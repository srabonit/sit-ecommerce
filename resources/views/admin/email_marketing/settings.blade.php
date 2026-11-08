
@extends('layouts.admin')

@section('title', 'Email Marketing Settings')

@php
    $settings = $settings ?? ($generalSettings ?? []);

    // Dynamic fallbacks with DB/Config alignment
    $fromNameDefault  = data_get($settings, 'default_sender_name') ?? config('mail.from.name', 'SHOP NOW BD');
    $fromEmailDefault = data_get($settings, 'default_sender_email') ?? config('mail.from.address', 'newsletter@shopnowbd.online');
    $replyToDefault   = data_get($settings, 'reply_to_email') ?? '';
    $batchSizeDefault = (int) (data_get($settings, 'default_batch_size') ?? 50);
    $cartThresholdDef = (int) (data_get($settings, 'cart_abandon_threshold_minutes') ?? 60);

    $trackingSettingsRoute = Route::has('admin.email_marketing.tracking_settings')
        ? route('admin.email_marketing.tracking_settings')
        : url('admin/email-marketing/tracking-settings');

    $settingsUpdateRoute = Route::has('admin.email_marketing.settings.update')
        ? route('admin.email_marketing.settings.update')
        : url('admin/email-marketing/settings');

    $providersRoute = Route::has('admin.email_marketing.providers')
        ? route('admin.email_marketing.providers')
        : url('admin/email-marketing/providers');

    $suppressionRoute = Route::has('admin.email_marketing.suppression')
        ? route('admin.email_marketing.suppression')
        : url('admin/email-marketing/suppression');
@endphp

@section('admin_content')

<style>
    /* =========================================================
       EMAIL MARKETING SETTINGS
       Kaiadmin Lite v1.2.0 - Classic / White Classic
    ========================================================= */

    #email-marketing-general-settings-root {
        --ems-primary: #1572e8;
        --ems-primary-rgb: 21, 114, 232;
        --ems-success: #18a979;
        --ems-warning: #e9a23b;
        --ems-danger: #e65353;
        --ems-info: #17a2b8;

        --ems-text: #344054;
        --ems-heading: #1f2937;
        --ems-muted: #7b8494;
        --ems-border: #e6eaf0;
        --ems-surface: #ffffff;
        --ems-soft: #f7f9fc;
        --ems-radius: 5px;

        min-width: 0;
        max-width: 100%;
        color: var(--ems-text);
        font-size: 13px;
        overflow-x: clip;
    }

    #email-marketing-general-settings-root *,
    #email-marketing-general-settings-root *::before,
    #email-marketing-general-settings-root *::after {
        box-sizing: border-box;
    }

    /* Page Header */
    #email-marketing-general-settings-root .settings-header-wrap {
        gap: 14px;
        margin-bottom: 18px;
    }

    #email-marketing-general-settings-root .settings-page-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0 0 5px;
        color: var(--ems-heading);
        font-size: 19px;
        font-weight: 700;
        line-height: 1.35;
    }

    #email-marketing-general-settings-root .settings-page-title > i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        flex-shrink: 0;
        border-radius: var(--ems-radius);
        background: rgba(var(--ems-primary-rgb), .09);
        color: var(--ems-primary);
        font-size: 15px;
    }

    #email-marketing-general-settings-root .settings-page-description {
        color: var(--ems-muted);
        font-size: 12px;
        line-height: 1.6;
    }

    /* Common surfaces */
    #email-marketing-general-settings-root .card {
        min-width: 0;
        margin-bottom: 16px;
        overflow: hidden;
        border: 1px solid var(--ems-border) !important;
        border-radius: var(--ems-radius) !important;
        background: var(--ems-surface);
        box-shadow: 0 2px 8px rgba(31, 41, 55, .035) !important;
    }

    #email-marketing-general-settings-root .section-header-wrap {
        min-height: 46px;
        padding: 10px 15px;
        border-bottom: 1px solid var(--ems-border);
        background: #fff;
    }

    #email-marketing-general-settings-root .section-title {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
        margin: 0;
        color: var(--ems-heading);
        font-size: 12px;
        font-weight: 700;
        line-height: 1.45;
    }

    #email-marketing-general-settings-root .section-title > i {
        color: var(--ems-primary);
        font-size: 13px;
    }

    #email-marketing-general-settings-root .section-body {
        padding: 17px;
    }

    /* Buttons */
    #email-marketing-general-settings-root .btn,
    #email-marketing-general-settings-root button,
    .settings-modal-scope .btn {
        border-radius: var(--ems-radius) !important;
        font-weight: 600;
        transition: background-color .18s ease, border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    #email-marketing-general-settings-root .btn-compact {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        min-height: 34px;
        padding: 7px 12px;
        font-size: 11.5px;
        line-height: 1.35;
        white-space: nowrap;
    }

    #email-marketing-general-settings-root .btn-primary {
        background-color: var(--ems-primary);
        border-color: var(--ems-primary);
    }

    #email-marketing-general-settings-root .btn-primary:hover {
        background-color: #0e62ce;
        border-color: #0e62ce;
        box-shadow: 0 3px 9px rgba(var(--ems-primary-rgb), .17);
    }

    #email-marketing-general-settings-root .top-action-btns {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }

    /* Collapse Controls */
    #email-marketing-general-settings-root .collapse-toggle-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 28px;
        width: 28px;
        height: 28px;
        padding: 0;
        border: 1px solid var(--ems-border);
        border-radius: var(--ems-radius);
        background: #fff;
        color: var(--ems-muted);
        cursor: pointer;
    }

    #email-marketing-general-settings-root .collapse-toggle-btn:hover {
        border-color: var(--ems-primary);
        background: rgba(var(--ems-primary-rgb), .06);
        color: var(--ems-primary);
    }

    #email-marketing-general-settings-root .collapse-icon {
        font-size: 10px;
        transition: transform .2s ease;
    }

    #email-marketing-general-settings-root .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    /* KPI Cards */
    #email-marketing-general-settings-root .metric-card {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        min-height: 105px;
        padding: 13px;
        border: 1px solid var(--ems-border) !important;
        border-left-width: 3px !important;
        border-radius: var(--ems-radius) !important;
        background: #fff;
        box-shadow: none !important;
        transition: box-shadow .18s ease, transform .18s ease;
    }

    #email-marketing-general-settings-root .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 14px rgba(31, 41, 55, .07) !important;
    }

    #email-marketing-general-settings-root .metric-card-blue {
        border-left-color: var(--ems-primary) !important;
    }

    #email-marketing-general-settings-root .metric-card-green {
        border-left-color: var(--ems-success) !important;
    }

    #email-marketing-general-settings-root .metric-card-amber {
        border-left-color: var(--ems-warning) !important;
    }

    #email-marketing-general-settings-root .metric-card-cyan {
        border-left-color: var(--ems-info) !important;
    }

    #email-marketing-general-settings-root .stat-label-title {
        color: var(--ems-muted);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .015em;
        line-height: 1.4;
    }

    #email-marketing-general-settings-root .stat-val-num {
        margin: 0;
        color: var(--ems-heading);
        font-size: 17px;
        font-weight: 700;
        line-height: 1.35;
        letter-spacing: -.02em;
        overflow-wrap: anywhere;
    }

    #email-marketing-general-settings-root .stat-sub-text {
        margin-top: 4px;
        color: var(--ems-muted);
        font-size: 10px;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    #email-marketing-general-settings-root .metric-icon-square {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 32px;
        width: 32px;
        height: 32px;
        border-radius: var(--ems-radius) !important;
        font-size: 13px;
    }

    #email-marketing-general-settings-root .metric-card-blue .metric-icon-square {
        background: rgba(var(--ems-primary-rgb), .09) !important;
        color: var(--ems-primary) !important;
    }

    #email-marketing-general-settings-root .metric-card-green .metric-icon-square {
        background: rgba(24, 169, 121, .09) !important;
        color: var(--ems-success) !important;
    }

    #email-marketing-general-settings-root .metric-card-amber .metric-icon-square {
        background: rgba(233, 162, 59, .12) !important;
        color: #bd7a12 !important;
    }

    #email-marketing-general-settings-root .metric-card-cyan .metric-icon-square {
        background: rgba(23, 162, 184, .09) !important;
        color: var(--ems-info) !important;
    }

    /* Form Controls */
    #email-marketing-general-settings-root .form-label {
        display: block;
        margin-bottom: 6px;
        color: #475467;
        font-size: 11.5px;
        font-weight: 700;
        line-height: 1.4;
    }

    #email-marketing-general-settings-root .form-control,
    #email-marketing-general-settings-root .form-select,
    #email-marketing-general-settings-root .input-group-text {
        min-height: 37px;
        border: 1px solid #dce2ea;
        border-radius: var(--ems-radius) !important;
        background-color: #fff;
        color: var(--ems-text);
        font-size: 12px;
        box-shadow: none;
    }

    #email-marketing-general-settings-root .form-control,
    #email-marketing-general-settings-root .form-select {
        padding: 8px 11px;
    }

    #email-marketing-general-settings-root .form-control::placeholder {
        color: #a0a8b5;
        opacity: 1;
    }

    #email-marketing-general-settings-root .form-control:focus,
    #email-marketing-general-settings-root .form-select:focus {
        border-color: var(--ems-primary);
        box-shadow: 0 0 0 3px rgba(var(--ems-primary-rgb), .10);
        outline: none;
    }

    #email-marketing-general-settings-root .input-group {
        flex-wrap: nowrap;
        min-width: 0;
    }

    #email-marketing-general-settings-root .input-group > .input-group-text {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 38px;
        padding: 7px 10px;
        border-color: #dce2ea;
        background: var(--ems-soft);
        color: var(--ems-muted);
    }

    #email-marketing-general-settings-root .input-group > .form-control,
    #email-marketing-general-settings-root .input-group > .form-select {
        min-width: 0;
    }

    #email-marketing-general-settings-root .settings-addon-text {
        font-size: 11px !important;
        font-weight: 600;
        white-space: nowrap;
    }

    #email-marketing-general-settings-root .field-help {
        display: block;
        margin-top: 6px;
        color: var(--ems-muted);
        font-size: 10.5px;
        line-height: 1.5;
    }

    #email-marketing-general-settings-root .field-help i {
        margin-right: 3px;
        color: #98a2b3;
    }

    #email-marketing-general-settings-root .font-monospace {
        overflow-wrap: anywhere;
    }

    /* Feedback */
    #email-marketing-general-settings-root .alert {
        border: 1px solid transparent !important;
        border-radius: var(--ems-radius) !important;
        font-size: 12px;
    }

    #email-marketing-general-settings-root .alert-success {
        border-color: #c9ecdf !important;
        background: #f0faf6;
    }

    #email-marketing-general-settings-root .alert-danger {
        border-color: #f4d3d3 !important;
        background: #fff5f5;
    }

    /* Infrastructure Links */
    #email-marketing-general-settings-root .management-link {
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 42px;
        padding: 9px 11px !important;
        border: 1px solid var(--ems-border) !important;
        border-radius: var(--ems-radius) !important;
        background: #fff !important;
        color: var(--ems-text) !important;
        font-size: 11.5px;
        text-align: left;
        white-space: normal;
    }

    #email-marketing-general-settings-root .management-link:hover {
        border-color: rgba(var(--ems-primary-rgb), .4) !important;
        background: rgba(var(--ems-primary-rgb), .035) !important;
        color: var(--ems-primary) !important;
    }

    #email-marketing-general-settings-root .management-link > i {
        width: 17px;
        flex: 0 0 17px;
        font-size: 13px;
        text-align: center;
    }

    /* Guidance */
    #email-marketing-general-settings-root .practice-item {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        margin-bottom: 12px;
        color: var(--ems-muted);
        font-size: 11px;
        line-height: 1.65;
    }

    #email-marketing-general-settings-root .practice-item:last-child {
        margin-bottom: 0;
    }

    #email-marketing-general-settings-root .practice-item > i {
        margin-top: 3px;
        color: var(--ems-primary);
        font-size: 12px;
    }

    #email-marketing-general-settings-root .practice-item strong {
        color: #475467;
    }

    /* Responsive */
    @media (max-width: 1199.98px) {
        #email-marketing-general-settings-root .metric-card {
            min-height: 100px;
        }
    }

    @media (max-width: 767.98px) {
        #email-marketing-general-settings-root {
            padding-right: 10px !important;
            padding-left: 10px !important;
        }

        #email-marketing-general-settings-root .settings-header-wrap {
            align-items: stretch !important;
            gap: 12px;
        }

        #email-marketing-general-settings-root .settings-page-title {
            font-size: 17px;
        }

        #email-marketing-general-settings-root .top-action-btns {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            width: 100%;
        }

        #email-marketing-general-settings-root .top-action-btns .btn {
            width: 100%;
            min-width: 0;
            min-height: 37px;
            padding: 7px 5px;
            font-size: 10.5px;
            white-space: normal;
        }

        #email-marketing-general-settings-root .section-header-wrap {
            padding: 10px 12px;
        }

        #email-marketing-general-settings-root .section-body {
            padding: 13px;
        }

        #email-marketing-general-settings-root .metric-card {
            min-height: 94px;
            padding: 10px;
        }

        #email-marketing-general-settings-root .stat-label-title {
            font-size: 10px;
        }

        #email-marketing-general-settings-root .stat-val-num {
            font-size: 14px;
        }

        #email-marketing-general-settings-root .stat-sub-text {
            font-size: 9px;
        }

        #email-marketing-general-settings-root .metric-icon-square {
            flex-basis: 27px;
            width: 27px;
            height: 27px;
            font-size: 11px;
        }

        #email-marketing-general-settings-root .card {
            margin-bottom: 12px;
        }
    }

    @media (max-width: 380px) {
        #email-marketing-general-settings-root .metric-card {
            padding: 8px;
        }

        #email-marketing-general-settings-root .stat-val-num {
            font-size: 12px;
        }

        #email-marketing-general-settings-root .top-action-btns .btn {
            font-size: 9.5px;
        }

        #email-marketing-general-settings-root .section-title {
            font-size: 11px;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-general-settings-root">

    {{-- 1. Page Header & Actions --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center settings-header-wrap">
        <div class="min-w-0">
            <h5 class="settings-page-title">
                <i class="fas fa-sliders"></i>
                <span>Email Marketing Settings</span>
            </h5>
            <small class="settings-page-description d-block">
                Manage sender identity, delivery limits, and cart recovery settings.
            </small>
        </div>

        <div class="top-action-btns">
            <a href="{{ $trackingSettingsRoute }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-chart-pie"></i>
                <span>Tracking Settings</span>
            </a>
            <button type="button" class="btn btn-sm btn-primary btn-compact shadow-xs fw-bold" id="btn-save-general-settings" onclick="submitSettingsForm()">
                <i class="fas fa-save"></i>
                <span>Save Configuration</span>
            </button>
        </div>
    </div>

    {{-- 2. Feedback Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 shadow-xs mb-3 d-flex align-items-center justify-content-between" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 shadow-xs mb-3 d-flex align-items-center justify-content-between" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-circle text-danger fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- 3. Configuration Overview --}}
    <div class="card mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center">
            <span class="section-title">
                <i class="fas fa-chart-pie"></i>
                Engine Configuration &amp; Telemetry Status
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseSettingsKpis" aria-expanded="true" aria-controls="collapseSettingsKpis" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>

        <div class="collapse show" id="collapseSettingsKpis">
            <div class="section-body">
                <div class="row g-2">

                    {{-- Sender Identity --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-blue h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <span class="stat-label-title text-truncate">Default Sender</span>
                                <div class="metric-icon-square">
                                    <i class="fas fa-id-card"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="stat-val-num font-monospace" title="{{ $fromNameDefault }}">
                                    {{ $fromNameDefault }}
                                </h4>
                                <small class="stat-sub-text d-block font-monospace" title="{{ $fromEmailDefault }}">
                                    {{ $fromEmailDefault }}
                                </small>
                            </div>
                        </div>
                    </div>

                    {{-- Dispatch Chunk Size --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-green h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <span class="stat-label-title text-truncate">Queue Batch Rate</span>
                                <div class="metric-icon-square">
                                    <i class="fas fa-gauge-high"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="stat-val-num text-success font-monospace">
                                    {{ $batchSizeDefault }} <span style="font-size: 11px;" class="fw-normal text-muted">per cycle</span>
                                </h4>
                                <small class="stat-sub-text d-block font-monospace">Background worker chunk</small>
                            </div>
                        </div>
                    </div>

                    {{-- Abandoned Inactivity Trigger --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-amber h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <span class="stat-label-title text-truncate">Cart Inactivity</span>
                                <div class="metric-icon-square">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="stat-val-num text-warning-emphasis font-monospace">
                                    {{ $cartThresholdDef }} <span style="font-size: 11px;" class="fw-normal text-muted">Minutes</span>
                                </h4>
                                <small class="stat-sub-text d-block font-monospace">Abandonment threshold</small>
                            </div>
                        </div>
                    </div>

                    {{-- Suppression Engine --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-cyan h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <span class="stat-label-title text-truncate">Compliance Filter</span>
                                <div class="metric-icon-square">
                                    <i class="fas fa-shield-halved"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="stat-val-num text-info" style="font-size: 15px;">
                                    <i class="fas fa-check-circle me-1"></i> Active
                                </h4>
                                <small class="stat-sub-text d-block font-monospace">Bounce &amp; opt-out shield</small>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- 4. Main Configuration Form --}}
    <form action="{{ $settingsUpdateRoute }}" method="POST" id="generalSettingsForm">
        @csrf

        <div class="row g-3">

            {{-- Left Column: Primary Settings --}}
            <div class="col-12 col-xl-8">

                {{-- Store Sender Identity --}}
                <div class="card mb-3">
                    <div class="section-header-wrap d-flex justify-content-between align-items-center">
                        <span class="section-title">
                            <i class="fas fa-id-card"></i>
                            1. Default Store Sender Identity
                        </span>
                        <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseSenderIdentity" aria-expanded="true" aria-controls="collapseSenderIdentity" title="Toggle Section">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>

                    <div class="collapse show" id="collapseSenderIdentity">
                        <div class="section-body">
                            <div class="row g-3">

                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="default_sender_name">
                                        From Sender Name <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text settings-addon-text">
                                            <i class="fas fa-user-tag"></i>
                                        </span>
                                        <input type="text" name="default_sender_name" id="default_sender_name" class="form-control" value="{{ $fromNameDefault }}" placeholder="SHOP NOW BD" required>
                                    </div>
                                    <small class="field-help">The display name customers see in their email inbox.</small>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="default_sender_email">
                                        From Email Address <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text settings-addon-text">
                                            <i class="fas fa-envelope"></i>
                                        </span>
                                        <input type="email" name="default_sender_email" id="default_sender_email" class="form-control font-monospace" value="{{ $fromEmailDefault }}" placeholder="newsletter@shopnowbd.online" required>
                                    </div>
                                    <small class="field-help">Use a verified sender address configured on your SMTP or mail server.</small>
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="reply_to_email">
                                        Reply-To Email Address (Optional)
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text settings-addon-text">
                                            <i class="fas fa-reply"></i>
                                        </span>
                                        <input type="email" name="reply_to_email" id="reply_to_email" class="form-control font-monospace" value="{{ $replyToDefault }}" placeholder="support@shopnowbd.online">
                                    </div>
                                    <small class="field-help">Customer replies will be directed to this support or helpdesk inbox.</small>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                {{-- Dispatch Engine Parameters --}}
                <div class="card mb-3">
                    <div class="section-header-wrap d-flex justify-content-between align-items-center">
                        <span class="section-title">
                            <i class="fas fa-gauge-high text-warning"></i>
                            2. Delivery Speed &amp; Rate Limits
                        </span>
                        <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseDeliverySpeed" aria-expanded="true" aria-controls="collapseDeliverySpeed" title="Toggle Section">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>

                    <div class="collapse show" id="collapseDeliverySpeed">
                        <div class="section-body">
                            <div class="row g-3">

                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="default_batch_size">
                                        Default Batch / Chunk Size
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text settings-addon-text">
                                            <i class="fas fa-boxes-stacked"></i>
                                        </span>
                                        <input type="number" name="default_batch_size" id="default_batch_size" class="form-control font-monospace bn-digit" value="{{ $batchSizeDefault }}" min="10" max="500">
                                    </div>
                                    <small class="field-help">Emails processed per background queue cycle. Recommended: 50–100.</small>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="cart_abandon_threshold_minutes">
                                        Abandoned Cart Inactivity Threshold
                                    </label>
                                    <div class="input-group">
                                        <input type="number" name="cart_abandon_threshold_minutes" id="cart_abandon_threshold_minutes" class="form-control font-monospace bn-digit" value="{{ $cartThresholdDef }}" min="15">
                                        <span class="input-group-text settings-addon-text font-monospace">Minutes</span>
                                    </div>
                                    <small class="field-help">Time without checkout activity before a cart is marked as abandoned.</small>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Right Column: Infrastructure & Guidance --}}
            <div class="col-12 col-xl-4">

                {{-- Quick Infrastructure Navigation --}}
                <div class="card mb-3">
                    <div class="section-header-wrap d-flex justify-content-between align-items-center">
                        <span class="section-title">
                            <i class="fas fa-link"></i>
                            Quick Management Links
                        </span>
                    </div>

                    <div class="section-body">
                        <div class="d-flex flex-column gap-2">
                            <a href="{{ $providersRoute }}" class="btn btn-light border btn-compact management-link shadow-xs">
                                <i class="fas fa-server text-primary"></i>
                                <span>Configure Mail Providers</span>
                            </a>

                            <a href="{{ $suppressionRoute }}" class="btn btn-light border btn-compact management-link shadow-xs">
                                <i class="fas fa-ban text-danger"></i>
                                <span>Suppression &amp; Blacklist</span>
                            </a>

                            <a href="{{ $trackingSettingsRoute }}" class="btn btn-light border btn-compact management-link shadow-xs">
                                <i class="fas fa-chart-line text-info"></i>
                                <span>Open/Click Telemetry Config</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Best Practices Guidance --}}
                <div class="card mb-3">
                    <div class="section-header-wrap d-flex justify-content-between align-items-center">
                        <span class="section-title">
                            <i class="fas fa-circle-info"></i>
                            Best Practices
                        </span>
                    </div>

                    <div class="section-body">
                        <div class="practice-item">
                            <i class="fas fa-shield-halved"></i>
                            <div>
                                <strong>Sender Domain Alignment:</strong>
                                Ensure your From Email domain matches your SPF, DKIM, and DMARC DNS records to improve inbox placement.
                            </div>
                        </div>

                        <div class="practice-item">
                            <i class="fas fa-paper-plane"></i>
                            <div>
                                <strong>Warmup Strategy:</strong>
                                When connecting a new SMTP server, start with a batch size of <strong>50 emails</strong> to reduce spam-filter throttling.
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
    function submitSettingsForm() {
        const btn = document.getElementById('btn-save-general-settings');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
        }
        document.getElementById('generalSettingsForm').submit();
    }
</script>
@endsection
