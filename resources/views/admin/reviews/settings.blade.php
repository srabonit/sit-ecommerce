@extends('layouts.admin')

@section('title', 'Global Product Review Settings - ' . config('app.name'))

@php
    $isReviewsEnabled   = !empty($settings['reviews_enabled']);
    $approvalMode       = $settings['approval_mode'] ?? 'auto';
    $isVerifiedOnly     = !empty($settings['verified_buyer_only']);
    $isAutoReplyEnabled = !empty($settings['auto_reply_enabled']);
@endphp

@section('admin_content')
<style>
    /* =========================================================
       GLOBAL REVIEW SETTINGS — MODERN ADMIN UI
    ========================================================= */

    #reviewSettingsRoot {
        --rs-primary: #2563eb;
        --rs-primary-soft: #eff6ff;
        --rs-success: #059669;
        --rs-warning: #d97706;
        --rs-purple: #9333ea;
        --rs-text: #172033;
        --rs-muted: #64748b;
        --rs-border: #e2e8f0;
        --rs-surface: #ffffff;
        --rs-background: #f8fafc;
        --rs-radius: 5px;
    }

    #reviewSettingsRoot,
    #reviewSettingsRoot * {
        box-sizing: border-box;
    }

    #reviewSettingsRoot .card,
    #reviewSettingsRoot .btn,
    #reviewSettingsRoot button,
    #reviewSettingsRoot .badge,
    #reviewSettingsRoot .form-control,
    #reviewSettingsRoot .form-select,
    #reviewSettingsRoot .alert,
    #reviewSettingsRoot .settings-header-card,
    #reviewSettingsRoot .metric-card,
    #reviewSettingsRoot .settings-panel-card,
    #reviewSettingsRoot .metric-icon-square {
        border-radius: var(--rs-radius) !important;
    }

    #reviewSettingsRoot .font-monospace,
    #reviewSettingsRoot .bn-digit {
        font-variant-numeric: tabular-nums;
    }

    #reviewSettingsRoot .btn-compact {
        min-height: 34px;
        padding: 7px 12px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        font-size: 11px !important;
        font-weight: 700 !important;
        line-height: 1.3;
        white-space: nowrap;
        transition: all .18s ease;
    }

    #reviewSettingsRoot .btn-compact:hover {
        transform: translateY(-1px);
    }

    /* Header */
    #reviewSettingsRoot .settings-header-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 17px 19px;
        margin-bottom: 15px;
        background: var(--rs-surface);
        border: 1px solid var(--rs-border);
        box-shadow: 0 2px 7px rgba(15, 23, 42, .035);
    }

    #reviewSettingsRoot .header-title {
        font-size: 18px;
        line-height: 1.35;
        font-weight: 800;
        letter-spacing: -.35px;
        color: var(--rs-text);
        margin: 0;
    }

    #reviewSettingsRoot .header-description {
        margin: 5px 0 0;
        font-size: 11.5px;
        line-height: 1.55;
        color: var(--rs-muted);
        max-width: 650px;
    }

    #reviewSettingsRoot .header-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 8px;
        border: 1px solid #dbeafe;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .65px;
        border-radius: 5px;
    }

    #reviewSettingsRoot .header-actions-group {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 7px;
        flex-shrink: 0;
    }

    /* Metrics */
    #reviewSettingsRoot .metric-card {
        position: relative;
        height: 100%;
        min-height: 91px;
        padding: 13px 14px;
        background: #fff;
        border: 1px solid var(--rs-border);
        box-shadow: 0 1px 3px rgba(15, 23, 42, .035);
        transition: transform .18s ease, box-shadow .18s ease;
        overflow: hidden;
    }

    #reviewSettingsRoot .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 14px rgba(15, 23, 42, .07);
    }

    #reviewSettingsRoot .metric-card::before {
        content: "";
        position: absolute;
        inset: 0 auto 0 0;
        width: 3px;
    }

    #reviewSettingsRoot .metric-card-blue::before {
        background: #2563eb;
    }

    #reviewSettingsRoot .metric-card-amber::before {
        background: #f59e0b;
    }

    #reviewSettingsRoot .metric-card-green::before {
        background: #10b981;
    }

    #reviewSettingsRoot .metric-card-purple::before {
        background: #9333ea;
    }

    #reviewSettingsRoot .stat-label-title {
        color: var(--rs-muted);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .25px;
        line-height: 1.4;
    }

    #reviewSettingsRoot .stat-val-num {
        margin: 0;
        font-size: 15px;
        line-height: 1.4;
        font-weight: 800;
        letter-spacing: -.25px;
        color: var(--rs-text);
        overflow-wrap: anywhere;
    }

    #reviewSettingsRoot .stat-sub-text {
        display: block;
        margin-top: 3px;
        color: var(--rs-muted);
        font-size: 9.5px;
        line-height: 1.4;
    }

    #reviewSettingsRoot .metric-icon-square {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 13px;
    }

    /* Feedback alerts */
    #reviewSettingsRoot .alert {
        border: 1px solid transparent !important;
        padding: 10px 13px;
        font-size: 11.5px;
    }

    #reviewSettingsRoot .alert-success {
        background: #f0fdf4;
        border-color: #bbf7d0 !important;
    }

    #reviewSettingsRoot .alert-danger {
        background: #fef2f2;
        border-color: #fecaca !important;
    }

    /* Settings panels */
    #reviewSettingsRoot .settings-panel-card {
        height: auto;
        padding: 16px;
        margin-bottom: 13px;
        background: var(--rs-surface);
        border: 1px solid var(--rs-border);
        box-shadow: 0 1px 3px rgba(15, 23, 42, .035);
        transition: opacity .2s ease, border-color .2s ease;
    }

    #reviewSettingsRoot .settings-section-title {
        display: flex;
        align-items: center;
        gap: 9px;
        margin-bottom: 15px;
        padding-bottom: 11px;
        border-bottom: 1px solid #edf1f6;
        color: var(--rs-text);
        font-size: 12px;
        font-weight: 800;
        line-height: 1.45;
    }

    #reviewSettingsRoot .section-icon {
        width: 29px;
        height: 29px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 5px;
        font-size: 12px;
    }

    #reviewSettingsRoot .section-icon-blue {
        background: #eff6ff;
        color: #2563eb;
    }

    #reviewSettingsRoot .section-icon-amber {
        background: #fffbeb;
        color: #d97706;
    }

    #reviewSettingsRoot .section-icon-green {
        background: #ecfdf5;
        color: #059669;
    }

    #reviewSettingsRoot .section-icon-info {
        background: #ecfeff;
        color: #0891b2;
    }

    #reviewSettingsRoot .section-icon-purple {
        background: #faf5ff;
        color: #9333ea;
    }

    /* Setting switch rows */
    #reviewSettingsRoot .setting-switch-row {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        padding: 10px 11px;
        margin-bottom: 8px;
        background: #fafbfd;
        border: 1px solid #edf1f6;
        border-radius: 5px;
        transition: background .15s ease, border-color .15s ease;
    }

    #reviewSettingsRoot .setting-switch-row:last-child {
        margin-bottom: 0;
    }

    #reviewSettingsRoot .setting-switch-row:hover {
        background: #f8fbff;
        border-color: #dbeafe;
    }

    #reviewSettingsRoot .setting-switch-control {
        flex-shrink: 0;
        padding-top: 1px;
    }

    #reviewSettingsRoot .setting-switch-content {
        flex: 1;
        min-width: 0;
    }

    #reviewSettingsRoot .form-check {
        min-height: 0;
        padding-left: 0;
        margin-bottom: 0;
    }

    #reviewSettingsRoot .form-check-input {
        width: 32px;
        height: 18px;
        margin: 0;
        cursor: pointer;
        border: 1px solid #cbd5e1;
        border-radius: 20px !important;
        background-color: #e2e8f0;
        box-shadow: none;
        transition: background-color .2s ease, border-color .2s ease;
    }

    #reviewSettingsRoot .form-check-input:checked {
        background-color: #2563eb;
        border-color: #2563eb;
    }

    #reviewSettingsRoot .form-check-input:focus {
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
    }

    #reviewSettingsRoot .setting-label {
        display: block;
        margin: 0;
        color: #1e293b;
        font-size: 11px;
        font-weight: 750;
        line-height: 1.5;
        cursor: pointer;
    }

    #reviewSettingsRoot .settings-hint-text {
        margin-top: 3px;
        color: #64748b;
        font-size: 10px;
        font-weight: 400;
        line-height: 1.5;
    }

    /* Form controls */
    #reviewSettingsRoot .form-label {
        display: block;
        margin-bottom: 5px;
        color: #334155;
        font-size: 10.5px !important;
        font-weight: 700;
        line-height: 1.45;
    }

    #reviewSettingsRoot .form-control,
    #reviewSettingsRoot .form-select {
        min-height: 35px;
        padding: 6px 10px;
        color: #172033;
        background-color: #fff;
        border: 1px solid #cbd5e1;
        border-radius: 5px !important;
        box-shadow: none;
        font-size: 11px;
        font-weight: 600;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #reviewSettingsRoot .form-control::placeholder {
        color: #94a3b8;
        font-weight: 400;
    }

    #reviewSettingsRoot .form-control:focus,
    #reviewSettingsRoot .form-select:focus {
        border-color: #60a5fa;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .10);
        outline: none;
    }

    #reviewSettingsRoot input[type="number"] {
        font-weight: 800;
    }

    #reviewSettingsRoot .field-hint {
        margin-top: 5px;
        color: #64748b;
        font-size: 9.5px;
        line-height: 1.45;
    }

    #reviewSettingsRoot .number-field-wrap {
        min-width: 0;
    }

    /* Save action */
    #reviewSettingsRoot .bottom-save-bar {
        position: sticky;
        bottom: 0;
        z-index: 10;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 14px;
        padding: 12px 0;
        background: rgba(248, 250, 252, .96);
        border-top: 1px solid var(--rs-border);
        backdrop-filter: blur(8px);
    }

    #reviewSettingsRoot .save-button {
        min-height: 39px !important;
        padding: 9px 19px !important;
        background: #2563eb;
        border: 1px solid #2563eb;
        font-size: 11px !important;
        font-weight: 800 !important;
        box-shadow: 0 3px 8px rgba(37, 99, 235, .15);
    }

    #reviewSettingsRoot .save-button:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
        box-shadow: 0 4px 12px rgba(37, 99, 235, .22);
    }

    /* Disabled / dependent visual state */
    #reviewSettingsRoot .settings-disabled {
        opacity: .72;
    }

    /* Tablet */
    @media (max-width: 991.98px) {
        #reviewSettingsRoot .settings-panel-card {
            padding: 14px;
        }

        #reviewSettingsRoot .settings-header-card {
            align-items: flex-start;
        }
    }

    /* Mobile */
    @media (max-width: 767.98px) {
        #reviewSettingsRoot {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        #reviewSettingsRoot .settings-header-card {
            flex-direction: column;
            align-items: stretch;
            padding: 14px;
            gap: 13px;
        }

        #reviewSettingsRoot .header-title {
            font-size: 16px;
        }

        #reviewSettingsRoot .header-description {
            font-size: 10.5px;
        }

        #reviewSettingsRoot .header-actions-group {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            width: 100%;
        }

        #reviewSettingsRoot .header-actions-group .btn {
            width: 100%;
            min-width: 0;
            min-height: 36px;
            padding: 7px 5px !important;
            font-size: 10px !important;
        }

        #reviewSettingsRoot .metric-card {
            min-height: 82px;
            padding: 10px;
        }

        #reviewSettingsRoot .stat-label-title {
            font-size: 9px;
        }

        #reviewSettingsRoot .stat-val-num {
            font-size: 13px;
        }

        #reviewSettingsRoot .stat-sub-text {
            font-size: 8.5px;
        }

        #reviewSettingsRoot .metric-icon-square {
            width: 27px;
            height: 27px;
            font-size: 11px;
        }

        #reviewSettingsRoot .settings-panel-card {
            padding: 12px;
            margin-bottom: 11px;
        }

        #reviewSettingsRoot .settings-section-title {
            font-size: 11px;
            margin-bottom: 12px;
        }

        #reviewSettingsRoot .setting-switch-row {
            padding: 9px;
            gap: 9px;
        }

        #reviewSettingsRoot .setting-label {
            font-size: 10.5px;
        }

        #reviewSettingsRoot .settings-hint-text {
            font-size: 9.5px;
        }

        #reviewSettingsRoot .bottom-save-bar {
            justify-content: stretch;
            padding: 10px 0;
        }

        #reviewSettingsRoot .save-button {
            width: 100%;
            min-height: 41px !important;
        }
    }

    @media (max-width: 380px) {
        #reviewSettingsRoot .header-actions-group {
            grid-template-columns: 1fr;
        }

        #reviewSettingsRoot .metric-card {
            padding: 9px;
        }

        #reviewSettingsRoot .stat-val-num {
            font-size: 12px;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="reviewSettingsRoot">

    {{-- Header --}}
    <div class="settings-header-card">

        <div class="min-w-0">
            <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                <span class="header-eyebrow">
                    <i class="fas fa-sliders"></i>
                    REVIEW CONFIGURATION
                </span>

                <span class="badge bg-light text-secondary border px-2 py-1"
                      style="font-size:9px; font-weight:700;">
                    GLOBAL POLICY ENGINE
                </span>
            </div>

            <h5 class="header-title">
                <i class="fas fa-star-half-alt text-primary me-1"></i>
                Global Product Review Settings
            </h5>

            <p class="header-description">
                Manage customer reviews, approval policies, photo uploads, storefront interactions and notification settings.
            </p>
        </div>

        <div class="header-actions-group">
            <a href="{{ route('admin.product.reviews.index') }}"
               class="btn btn-outline-secondary btn-compact bg-white">
                <i class="fas fa-arrow-left"></i>
                Back to Reviews
            </a>

            <a href="{{ route('admin.product.reviews.auto_rules') }}"
               class="btn btn-outline-success btn-compact bg-white">
                <i class="fas fa-robot"></i>
                Auto-Reply Rules
            </a>
        </div>
    </div>

    {{-- System Overview --}}
    <div class="row g-2 mb-3">

        <div class="col-6 col-xl-3">
            <div class="metric-card metric-card-blue">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                    <span class="stat-label-title">Review System</span>
                    <div class="metric-icon-square bg-primary-subtle text-primary">
                        <i class="fas fa-toggle-on"></i>
                    </div>
                </div>

                <h4 class="stat-val-num">
                    {{ $isReviewsEnabled ? 'Active Live' : 'Suspended' }}
                </h4>

                <small class="stat-sub-text">
                    {{ $isReviewsEnabled ? 'Storefront reviews enabled' : 'Reviews hidden from storefront' }}
                </small>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="metric-card metric-card-amber">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                    <span class="stat-label-title">Moderation Policy</span>
                    <div class="metric-icon-square bg-warning-subtle text-warning-emphasis">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                </div>

                <h4 class="stat-val-num" style="font-size:14px;">
                    {{ $approvalMode === 'auto' ? 'Automatic Live' : 'Strict Manual' }}
                </h4>

                <small class="stat-sub-text">
                    {{ $approvalMode === 'auto' ? 'Instant publication' : 'Admin approval required' }}
                </small>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="metric-card metric-card-green">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                    <span class="stat-label-title">Buyer Eligibility</span>
                    <div class="metric-icon-square bg-success-subtle text-success">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>

                <h4 class="stat-val-num" style="font-size:14px;">
                    {{ $isVerifiedOnly ? 'Verified Orders' : 'Public Access' }}
                </h4>

                <small class="stat-sub-text">
                    {{ $isVerifiedOnly ? 'Delivered orders only' : 'All eligible customers' }}
                </small>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="metric-card metric-card-purple">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                    <span class="stat-label-title">Auto-Reply Engine</span>
                    <div class="metric-icon-square"
                         style="background:#f3e8ff; color:#9333ea;">
                        <i class="fas fa-robot"></i>
                    </div>
                </div>

                <h4 class="stat-val-num">
                    {{ $isAutoReplyEnabled ? 'Active Engine' : 'Disabled' }}
                </h4>

                <small class="stat-sub-text">
                    Keyword &amp; rating automation
                </small>
            </div>
        </div>

    </div>

    {{-- Feedback Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center justify-content-between gap-2 mb-3"
             role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>

            <button type="button"
                    class="btn-close shadow-none"
                    data-bs-dismiss="alert"
                    aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center justify-content-between gap-2 mb-3"
             role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-circle text-danger fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('error') }}</span>
            </div>

            <button type="button"
                    class="btn-close shadow-none"
                    data-bs-dismiss="alert"
                    aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger mb-3">
            <div class="d-flex align-items-center gap-2 mb-2 fw-bold">
                <i class="fas fa-exclamation-triangle"></i>
                Please check the following errors:
            </div>

            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Settings Form --}}
    <form action="{{ route('admin.product.reviews.settings.update') }}"
          method="POST"
          id="globalReviewSettingsForm">

        @csrf

        <div class="row g-2 g-md-3">

            {{-- LEFT COLUMN --}}
            <div class="col-12 col-lg-6">

                {{-- 1. Storefront Visibility --}}
                <div class="settings-panel-card">

                    <div class="settings-section-title">
                        <span class="section-icon section-icon-blue">
                            <i class="fas fa-store"></i>
                        </span>
                        <span>1. Storefront Visibility &amp; Submission</span>
                    </div>

                    <div class="setting-switch-row">
                        <div class="setting-switch-control">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="reviews_enabled"
                                   id="setReviewsEnabled"
                                   value="1"
                                   {{ !empty($settings['reviews_enabled']) ? 'checked' : '' }}
                                   onchange="toggleDependentCards()">
                        </div>

                        <div class="setting-switch-content">
                            <label class="setting-label" for="setReviewsEnabled">
                                Enable Review System Globally
                            </label>

                            <div class="settings-hint-text">
                                If disabled, the review tab, rating summary and public comments will be hidden from all product pages.
                            </div>
                        </div>
                    </div>

                    <div class="setting-switch-row">
                        <div class="setting-switch-control">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="allow_new_submission"
                                   id="setAllowSubmission"
                                   value="1"
                                   {{ !empty($settings['allow_new_submission']) ? 'checked' : '' }}>
                        </div>

                        <div class="setting-switch-content">
                            <label class="setting-label" for="setAllowSubmission">
                                Allow New Review Submissions
                            </label>

                            <div class="settings-hint-text">
                                Customers can submit new reviews. When disabled, previously approved reviews remain visible.
                            </div>
                        </div>
                    </div>

                    <div class="setting-switch-row">
                        <div class="setting-switch-control">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="verified_buyer_only"
                                   id="setVerifiedOnly"
                                   value="1"
                                   {{ !empty($settings['verified_buyer_only']) ? 'checked' : '' }}>
                        </div>

                        <div class="setting-switch-content">
                            <label class="setting-label" for="setVerifiedOnly">
                                Verified Buyers Restriction
                            </label>

                            <div class="settings-hint-text">
                                Only logged-in customers with an actual Delivered or Completed order can submit reviews.
                            </div>
                        </div>
                    </div>

                </div>

                {{-- 2. Approval Policy --}}
                <div class="settings-panel-card">

                    <div class="settings-section-title">
                        <span class="section-icon section-icon-amber">
                            <i class="fas fa-shield-alt"></i>
                        </span>
                        <span>2. Approval &amp; Moderation Policy</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="approvalMode">
                            Default Approval Mode
                        </label>

                        <select name="approval_mode"
                                id="approvalMode"
                                class="form-select font-monospace">

                            <option value="auto"
                                {{ ($settings['approval_mode'] ?? 'auto') === 'auto' ? 'selected' : '' }}>
                                Mode A: Automatic Approval (Instant Publish)
                            </option>

                            <option value="manual"
                                {{ ($settings['approval_mode'] ?? 'auto') === 'manual' ? 'selected' : '' }}>
                                Mode B: Manual Approval Required (Held as Pending)
                            </option>
                        </select>

                        <div class="field-hint">
                            Choose whether new eligible reviews go live instantly or wait for admin inspection.
                        </div>
                    </div>

                    <div class="setting-switch-row">
                        <div class="setting-switch-control">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="require_approval_image"
                                   id="setReqApprovalImage"
                                   value="1"
                                   {{ !empty($settings['require_approval_image']) ? 'checked' : '' }}>
                        </div>

                        <div class="setting-switch-content">
                            <label class="setting-label" for="setReqApprovalImage">
                                Reviews with Photos Require Approval
                            </label>

                            <div class="settings-hint-text">
                                Reviews containing customer photos will always be held in the Pending queue for inspection.
                            </div>
                        </div>
                    </div>

                    <div class="setting-switch-row">
                        <div class="setting-switch-control">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="require_approval_first"
                                   id="setReqApprovalFirst"
                                   value="1"
                                   {{ !empty($settings['require_approval_first']) ? 'checked' : '' }}>
                        </div>

                        <div class="setting-switch-content">
                            <label class="setting-label" for="setReqApprovalFirst">
                                First-Time Reviewer Requires Approval
                            </label>

                            <div class="settings-hint-text">
                                A customer's first review on the store will require manual approval to reduce spam.
                            </div>
                        </div>
                    </div>

                    <div class="setting-switch-row">
                        <div class="setting-switch-control">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="auto_hide_reported"
                                   id="setAutoHideReported"
                                   value="1"
                                   {{ !empty($settings['auto_hide_reported']) ? 'checked' : '' }}>
                        </div>

                        <div class="setting-switch-content">
                            <label class="setting-label" for="setAutoHideReported">
                                Auto-Hide Reported Reviews
                            </label>

                            <div class="settings-hint-text">
                                Automatically hide reviews from the storefront when they receive multiple abuse reports.
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 align-items-center mt-3 pt-2 border-top">
                        <div class="col-8">
                            <label class="form-label mb-0">
                                Report Threshold to Auto-Hide
                            </label>
                            <div class="field-hint mt-1">
                                Minimum reports required to hide a review.
                            </div>
                        </div>

                        <div class="col-4 number-field-wrap">
                            <input type="number"
                                   name="report_threshold_hide"
                                   class="form-control text-center font-monospace bn-digit"
                                   min="1"
                                   max="20"
                                   value="{{ $settings['report_threshold_hide'] ?? 3 }}">
                        </div>
                    </div>

                </div>

            </div>

            {{-- RIGHT COLUMN --}}
            <div class="col-12 col-lg-6">

                {{-- 3. Photo Uploads --}}
                <div class="settings-panel-card">

                    <div class="settings-section-title">
                        <span class="section-icon section-icon-green">
                            <i class="fas fa-camera"></i>
                        </span>
                        <span>3. Customer Photo Uploads Configuration</span>
                    </div>

                    <div class="setting-switch-row mb-3">
                        <div class="setting-switch-control">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="image_reviews_enabled"
                                   id="setImageReviewsEnabled"
                                   value="1"
                                   {{ !empty($settings['image_reviews_enabled']) ? 'checked' : '' }}>
                        </div>

                        <div class="setting-switch-content">
                            <label class="setting-label" for="setImageReviewsEnabled">
                                Allow Customers to Upload Product Photos
                            </label>

                            <div class="settings-hint-text">
                                Enables photo attachments in the product review submission form.
                            </div>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label" for="maxImagesPerReview">
                                Max Photos / Review
                            </label>

                            <input type="number"
                                   id="maxImagesPerReview"
                                   name="max_images_per_review"
                                   class="form-control text-center font-monospace bn-digit"
                                   min="1"
                                   max="10"
                                   value="{{ $settings['max_images_per_review'] ?? 4 }}">
                        </div>

                        <div class="col-6">
                            <label class="form-label" for="maxImageSize">
                                Max Size per File (KB)
                            </label>

                            <input type="number"
                                   id="maxImageSize"
                                   name="max_image_size_kb"
                                   class="form-control text-center font-monospace bn-digit"
                                   min="512"
                                   max="10240"
                                   step="512"
                                   value="{{ $settings['max_image_size_kb'] ?? 2048 }}">
                        </div>
                    </div>

                    <div class="mt-3 p-2 rounded"
                         style="background:#f8fafc; border:1px solid #edf1f6;">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fas fa-info-circle text-primary mt-1"></i>
                            <div class="field-hint mt-0">
                                <strong class="text-dark">Supported formats:</strong>
                                JPG, JPEG, PNG and WEBP. SVG and executable formats are blocked for security.
                            </div>
                        </div>
                    </div>

                </div>

                {{-- 4. Pagination & Interaction --}}
                <div class="settings-panel-card">

                    <div class="settings-section-title">
                        <span class="section-icon section-icon-info">
                            <i class="fas fa-sliders-h"></i>
                        </span>
                        <span>4. Storefront Pagination &amp; Customer Interaction</span>
                    </div>

                    <div class="row g-2 align-items-center mb-3">
                        <div class="col-8">
                            <label class="form-label mb-0" for="frontendPerPage">
                                Reviews Per Page (Frontend PDP)
                            </label>

                            <div class="field-hint">
                                Controls how many reviews load initially before pagination.
                            </div>
                        </div>

                        <div class="col-4">
                            <input type="number"
                                   id="frontendPerPage"
                                   name="frontend_per_page"
                                   class="form-control text-center font-monospace bn-digit"
                                   min="2"
                                   max="50"
                                   value="{{ $settings['frontend_per_page'] ?? 5 }}">
                        </div>
                    </div>

                    <div class="setting-switch-row">
                        <div class="setting-switch-control">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="allow_voting"
                                   id="setAllowVoting"
                                   value="1"
                                   {{ !empty($settings['allow_voting']) ? 'checked' : '' }}>
                        </div>

                        <div class="setting-switch-content">
                            <label class="setting-label" for="setAllowVoting">
                                Enable Helpful Voting Button
                            </label>

                            <div class="settings-hint-text">
                                Allows visitors to upvote useful reviews (1 vote per user/IP limit).
                            </div>
                        </div>
                    </div>

                    <div class="setting-switch-row">
                        <div class="setting-switch-control">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="allow_reporting"
                                   id="setAllowReporting"
                                   value="1"
                                   {{ !empty($settings['allow_reporting']) ? 'checked' : '' }}>
                        </div>

                        <div class="setting-switch-content">
                            <label class="setting-label" for="setAllowReporting">
                                Enable Report Inappropriate Button
                            </label>

                            <div class="settings-hint-text">
                                Allows customers to flag offensive comments directly to admin moderation.
                            </div>
                        </div>
                    </div>

                </div>

                {{-- 5. Automation & Notifications --}}
                <div class="settings-panel-card mb-0">

                    <div class="settings-section-title">
                        <span class="section-icon section-icon-purple">
                            <i class="fas fa-robot"></i>
                        </span>
                        <span>5. Automation &amp; Admin Email Alerts</span>
                    </div>

                    <div class="setting-switch-row">
                        <div class="setting-switch-control">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="auto_reply_enabled"
                                   id="setAutoReplyEnabled"
                                   value="1"
                                   {{ !empty($settings['auto_reply_enabled']) ? 'checked' : '' }}>
                        </div>

                        <div class="setting-switch-content">
                            <label class="setting-label" for="setAutoReplyEnabled">
                                Enable Auto-Reply Engine Globally
                            </label>

                            <div class="settings-hint-text">
                                Automatically posts replies to customer reviews based on configured rating and keyword rules.
                            </div>
                        </div>
                    </div>

                    <div class="setting-switch-row mb-3">
                        <div class="setting-switch-control">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="email_notification_admin"
                                   id="setEmailNotificationAdmin"
                                   value="1"
                                   {{ !empty($settings['email_notification_admin']) ? 'checked' : '' }}>
                        </div>

                        <div class="setting-switch-content">
                            <label class="setting-label" for="setEmailNotificationAdmin">
                                Send Email Alert on New Review
                            </label>

                            <div class="settings-hint-text">
                                Sends an email notification to the store owner whenever a customer leaves feedback.
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="form-label" for="adminNotificationEmail">
                            Admin Notification Email Recipient
                        </label>

                        <input type="email"
                               id="adminNotificationEmail"
                               name="admin_notification_email"
                               class="form-control font-monospace"
                               placeholder="admin@example.com"
                               value="{{ $settings['admin_notification_email'] ?? '' }}">
                    </div>

                </div>

            </div>

        </div>

        {{-- Save Action --}}
        <div class="bottom-save-bar">
            <button type="submit"
                    class="btn btn-primary btn-compact save-button">
                <i class="fas fa-save"></i>
                Save Review Configuration
            </button>
        </div>

    </form>
</div>

<script>
    function toggleDependentCards() {
        const reviewsToggle = document.getElementById('setReviewsEnabled');

        if (!reviewsToggle) {
            return;
        }

        const isReviewsGloballyEnabled = reviewsToggle.checked;

        const dependentInputs = [
            'setAllowSubmission',
            'setVerifiedOnly',
            'setImageReviewsEnabled',
            'setAutoReplyEnabled'
        ];

        dependentInputs.forEach(id => {
            const el = document.getElementById(id);

            if (!el) {
                return;
            }

            const parentCard = el.closest('.settings-panel-card');

            if (parentCard) {
                parentCard.classList.toggle(
                    'settings-disabled',
                    !isReviewsGloballyEnabled
                );
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        toggleDependentCards();
    });
</script>
@endsection