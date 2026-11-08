@extends('layouts.admin')

@section('title', 'Email Service Providers - Email Marketing')

@php
    $totalProvidersCount = $providers->count();
    $activeProvider      = $providers->firstWhere('is_active', true);
    $smtpEnginesCount    = $providers->where('driver', 'smtp')->count();
    $apiEnginesCount     = $totalProvidersCount - $smtpEnginesCount;
@endphp

@section('admin_content')

<style>
    /* =========================================================
       KAIADMIN LITE - CLASSIC WHITE THEME
       Scoped to Email Service Providers
    ========================================================= */

    #email-marketing-providers-root {
        --provider-primary: var(--bs-primary, #1572e8);
        --provider-secondary: #6861ce;
        --provider-success: #31ce36;
        --provider-info: #48abf7;
        --provider-warning: #ffad46;
        --provider-danger: #f25961;

        --provider-body-bg: #f8f9fa;
        --provider-card-bg: #ffffff;
        --provider-heading: #2a2f5b;
        --provider-text: #575962;
        --provider-muted: #8d9498;
        --provider-border: #ebedf2;

        --provider-radius: 5px;
        --provider-shadow: 0 1px 3px rgba(0, 0, 0, .04);

        color: var(--provider-text);
        min-width: 0;
        width: 100%;
    }

    #email-marketing-providers-root,
    #email-marketing-providers-root *,
    .provider-modal-scope,
    .provider-modal-scope * {
        box-sizing: border-box;
    }

    #email-marketing-providers-root .card,
    .provider-modal-scope .modal-content,
    #email-marketing-providers-root .btn,
    .provider-modal-scope .btn,
    #email-marketing-providers-root .badge,
    .provider-modal-scope .form-control,
    .provider-modal-scope .form-select,
    #email-marketing-providers-root .form-control,
    #email-marketing-providers-root .form-select,
    .admin-provider-toast {
        border-radius: var(--provider-radius) !important;
    }

    #email-marketing-providers-root .card {
        background: var(--provider-card-bg);
        border: 1px solid var(--provider-border) !important;
        box-shadow: var(--provider-shadow);
        margin-bottom: 0;
    }

    #email-marketing-providers-root .text-dark,
    .provider-modal-scope .text-dark {
        color: var(--provider-heading) !important;
    }

    #email-marketing-providers-root .text-muted,
    .provider-modal-scope .text-muted {
        color: var(--provider-muted) !important;
    }

    #email-marketing-providers-root .min-w-0 {
        min-width: 0;
    }

    /* =========================================================
       PAGE HEADER
    ========================================================= */

    #email-marketing-providers-root .providers-header-wrap {
        padding: 0 0 17px;
        margin-bottom: 20px !important;
        border-bottom: 1px solid var(--provider-border);
    }

    #email-marketing-providers-root .page-heading {
        color: var(--provider-heading);
        font-size: clamp(17px, 1.4vw, 21px);
        font-weight: 700;
        letter-spacing: -.35px;
        line-height: 1.35;
    }

    #email-marketing-providers-root .page-description {
        font-size: 12px;
        line-height: 1.6;
        color: var(--provider-muted);
    }

    #email-marketing-providers-root .page-count-badge {
        font-size: 10px;
        font-weight: 700;
        padding: 5px 9px !important;
    }

    /* =========================================================
       BUTTON SYSTEM
    ========================================================= */

    #email-marketing-providers-root .btn,
    .provider-modal-scope .btn {
        font-weight: 600;
        transition: all .18s ease;
    }

    #email-marketing-providers-root .btn-compact,
    .provider-modal-scope .btn-compact {
        min-height: 32px;
        padding: 6px 12px !important;
        font-size: 11.5px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        white-space: nowrap;
    }

    #email-marketing-providers-root .btn-primary,
    .provider-modal-scope .btn-primary {
        background-color: var(--provider-primary);
        border-color: var(--provider-primary);
    }

    #email-marketing-providers-root .btn-primary:hover,
    .provider-modal-scope .btn-primary:hover {
        filter: brightness(.94);
    }

    #email-marketing-providers-root .top-action-btns {
        flex-shrink: 0;
    }

    #email-marketing-providers-root .top-action-btns .btn {
        min-height: 35px;
    }

    #email-marketing-providers-root .icon-action-btn {
        width: 31px;
        height: 31px;
        padding: 0 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    /* =========================================================
       SECTION HEADER
    ========================================================= */

    #email-marketing-providers-root .section-header-wrap {
        min-height: 43px;
        padding: 10px 15px;
        background: #ffffff;
        border-bottom: 1px solid var(--provider-border);
    }

    #email-marketing-providers-root .section-heading {
        color: var(--provider-heading);
        font-size: 12px;
        font-weight: 700;
        line-height: 1.4;
    }

    #email-marketing-providers-root .collapse-toggle-btn {
        width: 27px;
        height: 27px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #ffffff;
        border: 1px solid var(--provider-border);
        color: var(--provider-muted);
        transition: all .2s ease;
        flex-shrink: 0;
    }

    #email-marketing-providers-root .collapse-toggle-btn:hover {
        color: var(--provider-primary);
        border-color: var(--provider-primary);
        background: #f4f8ff;
    }

    #email-marketing-providers-root .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    #email-marketing-providers-root .collapse-icon {
        font-size: 10px;
        transition: transform .25s ease;
    }

    /* =========================================================
       KPI METRIC CARDS
    ========================================================= */

    #email-marketing-providers-root .metric-card {
        position: relative;
        height: 100%;
        padding: 15px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        overflow: hidden;
        border: 1px solid var(--provider-border) !important;
        background: #ffffff;
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    #email-marketing-providers-root .metric-card:hover {
        border-color: #d9e2ef !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, .045);
    }

    #email-marketing-providers-root .metric-card-green {
        border-left: 3px solid var(--provider-success) !important;
    }

    #email-marketing-providers-root .metric-card-blue {
        border-left: 3px solid var(--provider-primary) !important;
    }

    #email-marketing-providers-root .metric-card-cyan {
        border-left: 3px solid var(--provider-info) !important;
    }

    #email-marketing-providers-root .metric-card-purple {
        border-left: 3px solid var(--provider-secondary) !important;
    }

    #email-marketing-providers-root .stat-label-title {
        font-size: 11px;
        font-weight: 600;
        color: var(--provider-muted);
        letter-spacing: .15px;
        line-height: 1.4;
    }

    #email-marketing-providers-root .stat-val-num {
        font-size: clamp(16px, 1.5vw, 21px);
        font-weight: 700;
        line-height: 1.3;
        letter-spacing: -.35px;
        color: var(--provider-heading);
    }

    #email-marketing-providers-root .stat-sub-text {
        font-size: 10px;
        line-height: 1.5;
        color: var(--provider-muted);
    }

    #email-marketing-providers-root .metric-icon-square {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        border-radius: 5px !important;
        flex-shrink: 0;
    }

    #email-marketing-providers-root .metric-card-purple .metric-icon-square {
        background: #f1edff;
        color: var(--provider-secondary);
    }

    #email-marketing-providers-root .bn-digit,
    #email-marketing-providers-root .font-monospace {
        font-weight: 600;
    }

    /* =========================================================
       PROVIDER ENGINE CARDS
    ========================================================= */

    #email-marketing-providers-root .provider-engine-card {
        overflow: hidden;
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    #email-marketing-providers-root .provider-engine-card:hover {
        border-color: #d7e0ec !important;
        box-shadow: 0 4px 16px rgba(0, 0, 0, .055);
    }

    #email-marketing-providers-root .provider-active-border {
        border-left: 3px solid var(--provider-success) !important;
    }

    #email-marketing-providers-root .provider-card-header {
        min-height: 48px;
        gap: 8px;
        padding: 10px 13px;
    }

    #email-marketing-providers-root .provider-name {
        min-width: 0;
        color: var(--provider-heading);
        font-size: 12.5px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    #email-marketing-providers-root .provider-driver-badge {
        flex-shrink: 0;
        font-size: 9px;
        font-weight: 700;
        padding: 4px 7px !important;
        letter-spacing: .3px;
    }

    #email-marketing-providers-root .provider-active-badge {
        flex-shrink: 0;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .25px;
        padding: 5px 7px !important;
    }

    #email-marketing-providers-root .provider-card-body {
        padding: 14px !important;
    }

    #email-marketing-providers-root .provider-details {
        font-size: 11px;
        line-height: 1.9;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    #email-marketing-providers-root .provider-details li {
        margin-bottom: 3px;
    }

    #email-marketing-providers-root .provider-details li:last-child {
        margin-bottom: 0;
    }

    #email-marketing-providers-root .provider-details strong {
        color: var(--provider-heading);
        font-family: inherit;
        font-weight: 700;
    }

    #email-marketing-providers-root .provider-card-footer {
        min-height: 53px;
        padding: 9px 12px;
        background: #fafbfc;
        border-top: 1px solid var(--provider-border);
    }

    #email-marketing-providers-root .provider-card-footer .badge {
        font-size: 9px;
    }

    /* =========================================================
       FORM CONTROLS
    ========================================================= */

    #email-marketing-providers-root .form-label,
    .provider-modal-scope .form-label {
        display: block;
        margin-bottom: 5px;
        color: var(--provider-heading);
        font-size: 11.5px;
        font-weight: 600;
        line-height: 1.45;
    }

    #email-marketing-providers-root .form-control,
    #email-marketing-providers-root .form-select,
    .provider-modal-scope .form-control,
    .provider-modal-scope .form-select {
        width: 100%;
        min-height: 36px;
        padding: 7px 10px;
        border: 1px solid #e0e3e8;
        background-color: #ffffff;
        color: var(--provider-heading);
        font-size: 12px;
        line-height: 1.4;
        box-shadow: none;
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    #email-marketing-providers-root .form-control:focus,
    #email-marketing-providers-root .form-select:focus,
    .provider-modal-scope .form-control:focus,
    .provider-modal-scope .form-select:focus {
        border-color: var(--provider-primary);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .10);
        outline: none;
    }

    #email-marketing-providers-root .form-control::placeholder,
    .provider-modal-scope .form-control::placeholder {
        color: #a1a7b3;
        opacity: 1;
    }

    .provider-modal-scope .input-group > .input-group-text {
        border-color: #e0e3e8;
        background: #f8f9fa;
    }

    .provider-modal-scope .input-group > .form-control:not(:last-child),
    .provider-modal-scope .input-group > .form-select:not(:last-child) {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    .provider-modal-scope .input-group > .btn {
        min-width: 38px;
        border-color: #e0e3e8;
    }

    /* =========================================================
       MODALS
    ========================================================= */

    .provider-modal-scope .modal-content {
        overflow: hidden;
        border: 1px solid var(--provider-border) !important;
        background: #ffffff;
        box-shadow: 0 12px 35px rgba(0, 0, 0, .12);
    }

    .provider-modal-scope .modal-header {
        min-height: 53px;
        padding: 12px 17px !important;
        background: #ffffff !important;
        border-bottom: 1px solid var(--provider-border) !important;
    }

    .provider-modal-scope .modal-title {
        font-size: 13px !important;
        font-weight: 700;
        color: var(--provider-heading);
        line-height: 1.5;
    }

    .provider-modal-scope .modal-body {
        padding: 18px !important;
    }

    .provider-modal-scope .modal-footer {
        min-height: 55px;
        padding: 11px 17px !important;
        background: #fafbfc !important;
        border-top: 1px solid var(--provider-border) !important;
    }

    .provider-modal-scope .modal-dialog {
        width: auto;
        max-width: 560px;
        margin-left: auto;
        margin-right: auto;
    }

    .provider-modal-scope .modal-dialog.modal-sm {
        max-width: 400px;
    }

    .provider-modal-scope .btn-close {
        font-size: 10px;
    }

    /* =========================================================
       EMPTY STATE
    ========================================================= */

    #email-marketing-providers-root .provider-empty-state {
        padding: 38px 20px !important;
    }

    #email-marketing-providers-root .empty-state-icon {
        width: 58px;
        height: 58px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 14px;
        border: 1px solid var(--provider-border);
        border-radius: 50%;
        background: #f5f7fa;
        color: #9ba2ae;
        font-size: 23px;
    }

    #email-marketing-providers-root .empty-state-title {
        color: var(--provider-heading);
        font-size: 14px;
        font-weight: 700;
    }

    #email-marketing-providers-root .empty-state-description {
        max-width: 460px;
        margin: 0 auto 17px;
        color: var(--provider-muted);
        font-size: 11.5px;
        line-height: 1.7;
    }

    /* =========================================================
       ALERTS
    ========================================================= */

    #email-marketing-providers-root .alert {
        border: 1px solid transparent !important;
        padding: 11px 14px !important;
        font-size: 12px;
        line-height: 1.5;
    }

    #email-marketing-providers-root .alert-success {
        background: #effaf1;
        border-color: #d8f1dc !important;
    }

    #email-marketing-providers-root .alert-danger {
        background: #fff1f1;
        border-color: #f7dada !important;
    }

    /* =========================================================
       TOAST NOTIFICATIONS
    ========================================================= */

    .admin-provider-toast {
        position: fixed;
        right: 20px;
        bottom: 20px;
        z-index: 108000;
        width: min(360px, calc(100vw - 32px));
        min-width: 0;
        box-shadow: 0 8px 28px rgba(0, 0, 0, .16);
        animation: providerSlideIn .25s ease;
        overflow-wrap: anywhere;
    }

    @keyframes providerSlideIn {
        from {
            transform: translateX(25px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    /* =========================================================
       TABLET RESPONSIVENESS
    ========================================================= */

    @media (max-width: 991.98px) {
        #email-marketing-providers-root .metric-card {
            padding: 13px;
        }

        #email-marketing-providers-root .provider-card-body {
            padding: 13px !important;
        }

        .provider-modal-scope .modal-dialog {
            max-width: min(560px, calc(100% - 32px));
        }
    }

    /* =========================================================
       MOBILE RESPONSIVENESS
    ========================================================= */

    @media (max-width: 767.98px) {
        #email-marketing-providers-root {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        #email-marketing-providers-root .providers-header-wrap {
            gap: 13px !important;
            padding-bottom: 14px;
            margin-bottom: 16px !important;
        }

        #email-marketing-providers-root .page-heading {
            font-size: 17px;
        }

        #email-marketing-providers-root .page-description {
            font-size: 11px;
        }

        #email-marketing-providers-root .top-action-btns {
            width: 100%;
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px !important;
        }

        #email-marketing-providers-root .top-action-btns .btn {
            width: 100%;
            min-height: 37px;
            padding: 7px 5px !important;
            font-size: 10.5px !important;
            white-space: normal;
        }

        #email-marketing-providers-root .section-header-wrap {
            padding: 10px 12px;
        }

        #email-marketing-providers-root .section-heading {
            font-size: 11px;
        }

        #email-marketing-providers-root .metric-card {
            min-height: 100px;
            padding: 11px !important;
        }

        #email-marketing-providers-root .stat-label-title {
            font-size: 10px;
        }

        #email-marketing-providers-root .stat-val-num {
            font-size: 15px;
        }

        #email-marketing-providers-root .stat-sub-text {
            font-size: 9px;
        }

        #email-marketing-providers-root .metric-icon-square {
            width: 29px;
            height: 29px;
            font-size: 11px;
        }

        #email-marketing-providers-root .provider-card-header {
            padding: 10px;
        }

        #email-marketing-providers-root .provider-card-body {
            padding: 12px !important;
        }

        #email-marketing-providers-root .provider-card-footer {
            padding: 9px 10px;
            flex-wrap: wrap;
        }

        #email-marketing-providers-root .provider-details {
            font-size: 10.5px;
        }

        #email-marketing-providers-root .provider-engine-card {
            margin-bottom: 0 !important;
        }

        #email-marketing-providers-root .provider-empty-state {
            padding: 28px 15px !important;
        }

        .provider-modal-scope .modal-dialog {
            max-width: calc(100% - 24px);
            margin: 12px auto;
        }

        .provider-modal-scope .modal-dialog.modal-sm {
            max-width: min(400px, calc(100% - 24px));
        }

        .provider-modal-scope .modal-body {
            padding: 15px !important;
        }

        .provider-modal-scope .modal-header {
            padding: 11px 13px !important;
        }

        .provider-modal-scope .modal-footer {
            padding: 10px 13px !important;
        }
    }

    @media (max-width: 380px) {
        #email-marketing-providers-root .top-action-btns {
            grid-template-columns: 1fr;
        }

        #email-marketing-providers-root .provider-card-footer .btn {
            font-size: 10px !important;
            padding: 5px 8px !important;
        }

        #email-marketing-providers-root .provider-name {
            font-size: 11.5px;
        }

        #email-marketing-providers-root .provider-driver-badge {
            font-size: 8px;
        }

        .provider-modal-scope .form-control,
        .provider-modal-scope .form-select {
            font-size: 11px;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-providers-root">

    {{-- 1. Page Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 providers-header-wrap">
        <div class="min-w-0">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h5 class="fw-bold mb-0 d-flex align-items-center gap-2 page-heading">
                    <i class="fas fa-server text-primary"></i>
                    <span>Email Delivery Providers</span>
                </h5>

                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 font-monospace page-count-badge">
                    {{ $totalProvidersCount }} Engines
                </span>
            </div>

            <small class="d-block mt-1 page-description">
                Configure SMTP servers and API-based mail engines (Brevo, Mailgun, SES, SendGrid, Postmark).
            </small>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 top-action-btns">
            <button type="button" class="btn btn-sm btn-outline-info btn-compact bg-white" data-bs-toggle="modal" data-bs-target="#testConnectionModal">
                <i class="fas fa-vial text-info me-1"></i>
                <span>Test SMTP Ping</span>
            </button>

            <button type="button" class="btn btn-sm btn-primary btn-compact fw-bold" data-bs-toggle="modal" data-bs-target="#addProviderModal">
                <i class="fas fa-plus-circle me-1"></i>
                <span>Add Mailer Provider</span>
            </button>
        </div>
    </div>

    {{-- 2. Feedback Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3 d-flex align-items-center justify-content-between gap-2" role="alert">
            <div class="d-flex align-items-center gap-2 min-w-0">
                <i class="fas fa-check-circle text-success fs-5 flex-shrink-0"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3 d-flex align-items-center justify-content-between gap-2" role="alert">
            <div class="d-flex align-items-center gap-2 min-w-0">
                <i class="fas fa-exclamation-circle text-danger fs-5 flex-shrink-0"></i>
                <span class="fw-semibold text-dark">{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- 3. KPI Overview --}}
    <div class="card mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center">
            <span class="section-heading">
                <i class="fas fa-chart-pie text-primary me-1"></i>
                Mailer Infrastructure Overview
            </span>

            <button type="button" class="collapse-toggle-btn no-print" data-bs-toggle="collapse" data-bs-target="#collapseProviderKpis" aria-expanded="true" aria-controls="collapseProviderKpis" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>

        <div class="collapse show" id="collapseProviderKpis">
            <div class="card-body p-3">
                <div class="row g-3">

                    {{-- Active Mailer Engine --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-green h-100">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <span class="stat-label-title text-truncate">Active Engine</span>
                                <div class="metric-icon-square bg-success-subtle text-success">
                                    <i class="fas fa-circle-check"></i>
                                </div>
                            </div>

                            <div class="min-w-0">
                                <h4 class="my-0 text-success text-truncate font-monospace stat-val-num" title="{{ $activeProvider->name ?? 'Default .ENV' }}">
                                    {{ $activeProvider->name ?? 'Default .ENV' }}
                                </h4>
                                <small class="d-block text-truncate font-monospace stat-sub-text">Primary dispatch pipeline</small>
                            </div>
                        </div>
                    </div>

                    {{-- Total Providers --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-blue h-100">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <span class="stat-label-title text-truncate">Total Providers</span>
                                <div class="metric-icon-square bg-primary-subtle text-primary">
                                    <i class="fas fa-server"></i>
                                </div>
                            </div>

                            <div class="min-w-0">
                                <h4 class="my-0 text-dark font-monospace stat-val-num">
                                    {{ $totalProvidersCount }}
                                </h4>
                                <small class="d-block text-truncate font-monospace stat-sub-text">Configured engines</small>
                            </div>
                        </div>
                    </div>

                    {{-- SMTP Engines --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-cyan h-100">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <span class="stat-label-title text-truncate">SMTP Drivers</span>
                                <div class="metric-icon-square bg-info-subtle text-info">
                                    <i class="fas fa-network-wired"></i>
                                </div>
                            </div>

                            <div class="min-w-0">
                                <h4 class="my-0 text-dark font-monospace stat-val-num">
                                    {{ $smtpEnginesCount }}
                                </h4>
                                <small class="d-block text-truncate font-monospace stat-sub-text">Host-based relays</small>
                            </div>
                        </div>
                    </div>

                    {{-- API Engines --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-purple h-100">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <span class="stat-label-title text-truncate">API Drivers</span>
                                <div class="metric-icon-square">
                                    <i class="fas fa-key"></i>
                                </div>
                            </div>

                            <div class="min-w-0">
                                <h4 class="my-0 text-dark font-monospace stat-val-num">
                                    {{ $apiEnginesCount }}
                                </h4>
                                <small class="d-block text-truncate font-monospace stat-sub-text">Brevo, SES, Mailgun, Postmark</small>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- 4. Provider Cards --}}
    <div class="row g-3">
        @forelse($providers as $provider)
            @php
                $driverIcon = match(strtolower($provider->driver ?? 'smtp')) {
                    'smtp'     => 'fa-server text-primary',
                    'brevo'    => 'fa-paper-plane text-info',
                    'mailgun'  => 'fa-bolt text-danger',
                    'ses'      => 'fa-cloud text-warning',
                    'sendgrid' => 'fa-cubes text-primary',
                    'postmark' => 'fa-stamp text-warning',
                    default    => 'fa-envelope text-secondary',
                };

                $makeActiveRoute = url('admin/email-marketing/providers/make-active/' . $provider->id);
                $destroyRoute    = url('admin/email-marketing/providers/delete/' . $provider->id);
                $updateRoute     = url('admin/email-marketing/providers/update/' . $provider->id);
            @endphp

            <div class="col-12 col-md-6 col-xl-4" id="provider-card-{{ $provider->id }}">
                <div class="card provider-engine-card h-100 d-flex flex-column justify-content-between {{ $provider->is_active ? 'provider-active-border' : '' }}">

                    <div>
                        {{-- Provider Card Header --}}
                        <div class="section-header-wrap provider-card-header d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2 min-w-0">
                                <i class="fas {{ $driverIcon }} flex-shrink-0" style="font-size: 14px;"></i>

                                <strong class="provider-name text-truncate" title="{{ $provider->name }}">
                                    {{ $provider->name }}
                                </strong>

                                <span class="badge bg-light text-secondary border font-monospace provider-driver-badge">
                                    {{ strtoupper($provider->driver) }}
                                </span>
                            </div>

                            @if($provider->is_active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace provider-active-badge">
                                    <i class="fas fa-circle-check me-1"></i> ACTIVE
                                </span>
                            @endif
                        </div>

                        {{-- Provider Details --}}
                        <div class="card-body provider-card-body">
                            <ul class="list-unstyled small text-muted mb-0 font-monospace provider-details">

                                <li class="text-truncate">
                                    <strong>Sender:</strong>
                                    <span class="text-dark" title="{{ $provider->sender_name ?? config('mail.from.name') }} &lt;{{ $provider->sender_email ?? config('mail.from.address') }}&gt;">
                                        {{ $provider->sender_name ?? config('mail.from.name') }} &lt;{{ $provider->sender_email ?? config('mail.from.address') }}&gt;
                                    </span>
                                </li>

                                @if($provider->driver === 'smtp')
                                    <li class="text-truncate">
                                        <strong>Host:</strong>
                                        <span>{{ $provider->host ?? '—' }}:{{ $provider->port ?? '587' }} ({{ strtoupper($provider->encryption ?? 'tls') }})</span>
                                    </li>

                                    <li class="text-truncate">
                                        <strong>Username:</strong>
                                        <span>{{ $provider->username ?? '—' }}</span>
                                    </li>
                                @else
                                    <li class="text-truncate">
                                        <strong>API Key:</strong>
                                        <span class="text-dark">••••••••{{ substr($provider->api_key ?? '', -4) }}</span>
                                    </li>

                                    <li class="text-truncate">
                                        <strong>Engine:</strong>
                                        <span>Transactional HTTP Rest API</span>
                                    </li>
                                @endif

                            </ul>
                        </div>
                    </div>

                    {{-- Provider Actions --}}
                    <div class="provider-card-footer d-flex align-items-center justify-content-between gap-2">

                        <div class="min-w-0">
                            @if(!$provider->is_active)
                                <form action="{{ $makeActiveRoute }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success btn-compact bg-white" title="Set as primary mail delivery engine">
                                        <i class="fas fa-check me-1"></i>
                                        <span>Make Active</span>
                                    </button>
                                </form>
                            @else
                                <span class="badge bg-success text-white font-monospace px-2 py-1">
                                    <i class="fas fa-shield-halved me-1"></i> DEFAULT ENGINE
                                </span>
                            @endif
                        </div>

                        <div class="d-flex align-items-center gap-1 flex-shrink-0">

                            {{-- Edit Provider --}}
                            <button type="button" class="btn btn-sm btn-light border text-primary icon-action-btn" data-bs-toggle="modal" data-bs-target="#editProviderModal{{ $provider->id }}" title="Edit Provider">
                                <i class="fas fa-pen-to-square" style="font-size: 11px;"></i>
                            </button>

                            {{-- Delete Provider --}}
                            <button type="button" class="btn btn-sm btn-light border text-danger icon-action-btn" onclick="triggerDeleteProvider('{{ addslashes($provider->name) }}', '{{$destroyRoute }}')" title="Delete Provider">
                                <i class="fas fa-trash-can" style="font-size: 11px;"></i>
                            </button>

                        </div>
                    </div>
                </div>
            </div>

            {{-- Edit Provider Modal --}}
            <div class="modal fade provider-modal-scope" id="editProviderModal{{ $provider->id }}" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">

                        <div class="modal-header d-flex justify-content-between align-items-center">
                            <h6 class="modal-title d-flex align-items-center gap-2 mb-0">
                                <i class="fas fa-pen-to-square text-primary"></i>
                                <span>Edit Provider: {{ $provider->name }}</span>
                            </h6>

                            <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <form action="{{ $updateRoute }}" method="POST">
                            @csrf

                            <div class="modal-body">
                                <div class="row g-3">

                                    <div class="col-12 col-md-7">
                                        <label class="form-label" for="edit_name_{{ $provider->id }}">
                                            Provider Nickname <span class="text-danger">*</span>
                                        </label>

                                        <input type="text" name="name" id="edit_name_{{ $provider->id }}" class="form-control" value="{{ $provider->name }}" required>
                                    </div>

                                    <div class="col-12 col-md-5">
                                        <label class="form-label" for="edit_driver_{{ $provider->id }}">
                                            Driver Type <span class="text-danger">*</span>
                                        </label>

                                        <select name="driver" id="edit_driver_{{ $provider->id }}" class="form-select font-monospace shadow-none" onchange="toggleEditDriverFields({{ $provider->id }})" required>
                                            <option value="smtp" {{ $provider->driver === 'smtp' ? 'selected' : '' }}>Standard SMTP</option>
                                            <option value="brevo" {{ $provider->driver === 'brevo' ? 'selected' : '' }}>Brevo (Sendinblue)</option>
                                            <option value="mailgun" {{ $provider->driver === 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                                            <option value="ses" {{ $provider->driver === 'ses' ? 'selected' : '' }}>Amazon SES</option>
                                            <option value="sendgrid" {{ $provider->driver === 'sendgrid' ? 'selected' : '' }}>SendGrid</option>
                                            <option value="postmark" {{ $provider->driver === 'postmark' ? 'selected' : '' }}>Postmark</option>
                                        </select>
                                    </div>

                                    {{-- SMTP Fields --}}
                                    <div class="col-12" id="edit_smtp_box_{{ $provider->id }}" style="{{ $provider->driver === 'smtp' ? '' : 'display: none;' }}">
                                        <div class="row g-3">

                                            <div class="col-12 col-md-8">
                                                <label class="form-label" for="edit_host_{{ $provider->id }}">SMTP Host</label>
                                                <input type="text" name="host" id="edit_host_{{ $provider->id }}" class="form-control font-monospace" value="{{ $provider->host }}">
                                            </div>

                                            <div class="col-12 col-md-4">
                                                <label class="form-label" for="edit_port_{{ $provider->id }}">Port</label>
                                                <input type="number" name="port" id="edit_port_{{ $provider->id }}" class="form-control font-monospace bn-digit" value="{{ $provider->port ?? 587 }}">
                                            </div>

                                            <div class="col-12 col-md-6">
                                                <label class="form-label" for="edit_user_{{ $provider->id }}">Username</label>
                                                <input type="text" name="username" id="edit_user_{{ $provider->id }}" class="form-control font-monospace" value="{{ $provider->username }}">
                                            </div>

                                            <div class="col-12 col-md-6">
                                                <label class="form-label" for="edit_pass_{{ $provider->id }}">Password / App Key</label>

                                                <div class="input-group">
                                                    <input type="password" name="password" id="edit_pass_{{ $provider->id }}" class="form-control" placeholder="Keep empty to leave intact">

                                                    <button class="btn btn-light border" type="button" onclick="togglePassVisibility('edit_pass_{{ $provider->id }}')">
                                                        <i class="fas fa-eye" style="font-size: 11px;"></i>
                                                    </button>
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                    {{-- API Key --}}
                                    <div class="col-12" id="edit_api_box_{{ $provider->id }}" style="{{ $provider->driver !== 'smtp' ? '' : 'display: none;' }}">
                                        <label class="form-label" for="edit_api_{{ $provider->id }}">REST API Key / Secret</label>

                                        <div class="input-group">
                                            <input type="password" name="api_key" id="edit_api_{{ $provider->id }}" class="form-control font-monospace" placeholder="Keep empty to leave current intact">

                                            <button class="btn btn-light border" type="button" onclick="togglePassVisibility('edit_api_{{ $provider->id }}')">
                                                <i class="fas fa-eye" style="font-size: 11px;"></i>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Sender Identity --}}
                                    <div class="col-12 col-md-6">
                                        <label class="form-label" for="edit_sname_{{ $provider->id }}">Sender Name</label>
                                        <input type="text" name="sender_name" id="edit_sname_{{ $provider->id }}" class="form-control" value="{{ $provider->sender_name }}">
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label class="form-label" for="edit_semail_{{ $provider->id }}">Sender Email</label>
                                        <input type="email" name="sender_email" id="edit_semail_{{ $provider->id }}" class="form-control font-monospace" value="{{ $provider->sender_email }}">
                                    </div>

                                </div>
                            </div>

                            <div class="modal-footer d-flex justify-content-between">
                                <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary btn-compact fw-bold">Update Provider</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        @empty
            <div class="col-12">
                <div class="card provider-empty-state text-center">

                    <div class="empty-state-icon">
                        <i class="fas fa-server"></i>
                    </div>

                    <h6 class="empty-state-title mb-2">No External Mail Providers Configured</h6>

                    <p class="empty-state-description">
                        The system is currently using your default Laravel <code class="font-monospace">.env</code> mail settings. Click "Add Mailer Provider" to manage multiple SMTP or transactional API services.
                    </p>

                    <div>
                        <button type="button" class="btn btn-sm btn-primary btn-compact fw-bold" data-bs-toggle="modal" data-bs-target="#addProviderModal">
                            <i class="fas fa-plus-circle me-1"></i> Add Mailer Provider
                        </button>
                    </div>

                </div>
            </div>
        @endforelse
    </div>
</div>

{{-- Modal 1: Add Provider --}}
<div class="modal fade provider-modal-scope" id="addProviderModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header d-flex justify-content-between align-items-center">
                <h6 class="modal-title d-flex align-items-center gap-2 mb-0">
                    <i class="fas fa-server text-primary"></i>
                    <span>Add Email Delivery Provider</span>
                </h6>

                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ url('admin/email-marketing/providers/store') }}" method="POST">
                @csrf

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-12 col-md-7">
                            <label class="form-label" for="add_provider_name">
                                Provider Nickname <span class="text-danger">*</span>
                            </label>

                            <input type="text" name="name" id="add_provider_name" class="form-control" placeholder="Brevo SMTP / Amazon SES" required>
                        </div>

                        <div class="col-12 col-md-5">
                            <label class="form-label" for="add_driver_type">
                                Driver Type <span class="text-danger">*</span>
                            </label>

                            <select name="driver" id="add_driver_type" class="form-select font-monospace shadow-none" onchange="toggleAddDriverFields()" required>
                                <option value="smtp">Standard SMTP</option>
                                <option value="brevo">Brevo (Sendinblue)</option>
                                <option value="mailgun">Mailgun</option>
                                <option value="ses">Amazon SES</option>
                                <option value="sendgrid">SendGrid</option>
                                <option value="postmark">Postmark</option>
                            </select>
                        </div>

                        {{-- SMTP Fields --}}
                        <div class="col-12" id="add_smtp_box">
                            <div class="row g-3">

                                <div class="col-12 col-md-8">
                                    <label class="form-label" for="add_smtp_host">SMTP Host</label>
                                    <input type="text" name="host" id="add_smtp_host" class="form-control font-monospace" placeholder="smtp-relay.brevo.com">
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label" for="add_smtp_port">Port</label>
                                    <input type="number" name="port" id="add_smtp_port" class="form-control font-monospace bn-digit" placeholder="587" value="587">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="add_username">Username</label>
                                    <input type="text" name="username" id="add_username" class="form-control font-monospace">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="add_password">Password / Secret</label>

                                    <div class="input-group">
                                        <input type="password" name="password" id="add_password" class="form-control">

                                        <button class="btn btn-light border" type="button" onclick="togglePassVisibility('add_password')">
                                            <i class="fas fa-eye" style="font-size: 11px;"></i>
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>

                        {{-- API Key --}}
                        <div class="col-12" id="add_api_box" style="display: none;">
                            <label class="form-label" for="add_api_key">REST API Key / Secret</label>

                            <div class="input-group">
                                <input type="password" name="api_key" id="add_api_key" class="form-control font-monospace" placeholder="xkeysib-...">

                                <button class="btn btn-light border" type="button" onclick="togglePassVisibility('add_api_key')">
                                    <i class="fas fa-eye" style="font-size: 11px;"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Sender Identity --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="add_sender_name">Sender Name</label>
                            <input type="text" name="sender_name" id="add_sender_name" class="form-control" placeholder="Shop Now BD">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="add_sender_email">Sender Email</label>
                            <input type="email" name="sender_email" id="add_sender_email" class="form-control font-monospace" placeholder="info@shopnowbd.online">
                        </div>

                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-compact fw-bold">Save Provider</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal 2: Test Connection --}}
<div class="modal fade provider-modal-scope" id="testConnectionModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center">

            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <h6 class="modal-title d-flex align-items-center gap-2 mb-0">
                    <i class="fas fa-vial text-info"></i>
                    <span>Send Verification Test</span>
                </h6>

                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body text-start">
                <label class="form-label" for="testTargetEmail">Target Inbox for SMTP Ping</label>

                <input type="email" id="testTargetEmail" class="form-control font-monospace shadow-none" value="{{ auth()->user()->email ?? '' }}" placeholder="your-email@example.com">

                <div id="testResultMsg" class="mt-3 font-monospace" style="font-size: 11px;"></div>
            </div>

            <div class="modal-footer border-0 pt-0 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light border btn-compact" data-bs-dismiss="modal">Close</button>

                <button type="button" class="btn btn-info text-white btn-compact fw-bold" id="btn-send-ping" onclick="runConnectionTest()">
                    <i class="fas fa-paper-plane me-1"></i> Send Ping
                </button>
            </div>

        </div>
    </div>
</div>

{{-- Modal 3: Delete Confirmation --}}
<div class="modal fade provider-modal-scope" id="deleteProviderModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center">

            <div class="modal-body p-4">

                <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 48px; height: 48px;">
                    <i class="fas fa-trash-can fs-4"></i>
                </div>

                <h6 class="fw-bold text-dark mb-2" style="font-size: 14px;">Delete Mail Provider?</h6>

                <p class="text-muted small mb-3" style="font-size: 11.5px; line-height: 1.6;">
                    Are you sure you want to delete <strong id="deleteProviderTitle" class="text-dark">this provider</strong>? This will remove the SMTP/API credentials from your system.
                </p>

                <form id="deleteProviderForm" method="POST" action="">
                    @csrf

                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger btn-compact fw-bold">Yes, Delete</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

{{-- Floating Toast Feedback --}}
<div id="custom-toast-holder"></div>

<script>
    function triggerDeleteProvider(name, formUrl) {
        document.getElementById('deleteProviderTitle').innerText = name;
        document.getElementById('deleteProviderForm').action = formUrl;
        const modalEl = document.getElementById('deleteProviderModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function toggleAddDriverFields() {
        const driver = document.getElementById('add_driver_type')?.value;
        const smtpBox = document.getElementById('add_smtp_box');
        const apiBox  = document.getElementById('add_api_box');

        if (driver === 'smtp') {
            smtpBox.style.display = '';
            apiBox.style.display = 'none';
        } else {
            smtpBox.style.display = 'none';
            apiBox.style.display = '';
        }
    }

    function toggleEditDriverFields(id) {
        const driver = document.getElementById(`edit_driver_${id}`)?.value;
        const smtpBox = document.getElementById(`edit_smtp_box_${id}`);
        const apiBox  = document.getElementById(`edit_api_box_${id}`);

        if (driver === 'smtp') {
            smtpBox.style.display = '';
            apiBox.style.display = 'none';
        } else {
            smtpBox.style.display = 'none';
            apiBox.style.display = '';
        }
    }

    function togglePassVisibility(inputId) {
        const input = document.getElementById(inputId);
        if (input) {
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    }

    function runConnectionTest() {
        const email = document.getElementById('testTargetEmail')?.value?.trim();
        const resultDiv = document.getElementById('testResultMsg');
        const pingBtn = document.getElementById('btn-send-ping');

        if (!email) {
            showToast('Warning', 'Please enter a target verification email.', 'warning');
            return;
        }

        if (pingBtn) {
            pingBtn.disabled = true;
            pingBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Testing...';
        }

        resultDiv.innerHTML = '<span class="text-muted"><i class="fas fa-spinner fa-spin me-1"></i> Testing SMTP handshake...</span>';

        const testUrl = "{{ url('admin/email-marketing/providers/test-connection') }}";

        fetch(testUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ test_email: email })
        })
        .then(res => res.json())
        .then(data => {
            if (pingBtn) {
                pingBtn.disabled = false;
                pingBtn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Send Ping';
            }
            if (data.success) {
                resultDiv.innerHTML = `<span class="text-success fw-bold"><i class="fas fa-check-circle me-1"></i> ${data.message}</span>`;
                showToast('Success', data.message, 'success');
            } else {
                resultDiv.innerHTML = `<span class="text-danger fw-bold"><i class="fas fa-exclamation-circle me-1"></i> ${data.message}</span>`;
                showToast('Failed', data.message, 'danger');
            }
        })
        .catch(err => {
            if (pingBtn) {
                pingBtn.disabled = false;
                pingBtn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Send Ping';
            }
            resultDiv.innerHTML = `<span class="text-danger fw-bold">Test failed: ${err.message}</span>`;
            showToast('Error', 'Connection test failed to complete.', 'danger');
        });
    }

    // Floating Toast Notifications
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
            <div id="${toastId}" class="admin-provider-toast ${config.color} p-3 d-flex align-items-center justify-content-between shadow-lg" style="border-radius: 5px !important;">
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