@extends('layouts.admin')

@section('title', 'Authentication & Security Settings')

@section('admin_content')
<style>
    /* =========================================================
       AUTHENTICATION SETTINGS — KAIADMIN CLASSIC UI
       Scoped styles: No global Bootstrap overrides
    ========================================================= */

    .auth-settings-page {
        --auth-primary: var(--bs-primary, #1572e8);
        --auth-body: var(--bs-body-color, #344054);
        --auth-muted: var(--bs-secondary-color, #667085);
        --auth-border: var(--bs-border-color, #e5e7eb);
        --auth-surface: #ffffff;
        --auth-soft: #f8fafc;
        --auth-radius: 8px;
        --auth-shadow: 0 1px 3px rgba(16, 24, 40, .05);
        color: var(--auth-body);
        min-width: 0;
    }

    .auth-settings-page,
    .auth-settings-page * {
        box-sizing: border-box;
    }

    /* Page Header */
    .auth-settings-page .auth-page-title {
        font-size: clamp(19px, 2vw, 23px);
        font-weight: 700;
        color: var(--bs-emphasis-color, #202939);
        letter-spacing: -.35px;
    }

    .auth-settings-page .auth-page-description {
        font-size: 13px;
        line-height: 1.6;
        color: var(--auth-muted);
        max-width: 680px;
    }

    /* Cards */
    .auth-settings-page .auth-card {
        background: var(--auth-surface);
        border: 1px solid var(--auth-border) !important;
        border-radius: var(--auth-radius) !important;
        box-shadow: var(--auth-shadow) !important;
        overflow: hidden;
        min-width: 0;
    }

    .auth-settings-page .auth-card .card-header {
        background: var(--auth-surface);
        border-bottom: 1px solid var(--auth-border);
        padding: 15px 20px;
    }

    .auth-settings-page .auth-card .card-body {
        padding: 20px;
    }

    .auth-settings-page .auth-section-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
        font-size: 14px;
        font-weight: 650;
        line-height: 1.5;
        color: var(--bs-emphasis-color, #202939);
    }

    .auth-settings-page .auth-section-icon {
        width: 30px;
        height: 30px;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        flex-shrink: 0;
        border-radius: 7px;
        background: var(--auth-soft);
        font-size: 14px;
    }

    /* Setting Rows */
    .auth-settings-page .auth-setting-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        padding: 13px 0;
        border-bottom: 1px solid var(--auth-border);
        min-width: 0;
    }

    .auth-settings-page .auth-setting-row:last-child {
        border-bottom: 0;
    }

    .auth-settings-page .auth-setting-content {
        flex: 1 1 auto;
        min-width: 0;
    }

    .auth-settings-page .auth-setting-label {
        display: block;
        margin-bottom: 3px;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.45;
        color: var(--bs-emphasis-color, #344054);
        cursor: pointer;
    }

    .auth-settings-page .auth-setting-description {
        display: block;
        font-size: 12px;
        line-height: 1.55;
        color: var(--auth-muted);
        overflow-wrap: anywhere;
    }

    /* Switches */
    .auth-settings-page .form-switch {
        flex-shrink: 0;
        padding-left: 2.7em;
        min-height: 24px;
    }

    .auth-settings-page .form-switch .form-check-input {
        width: 2.25em;
        height: 1.2em;
        margin-top: 2px;
        margin-left: -2.7em;
        cursor: pointer;
        box-shadow: none;
        border-color: #cbd5e1;
    }

    .auth-settings-page .form-switch .form-check-input:checked {
        background-color: var(--auth-primary);
        border-color: var(--auth-primary);
    }

    .auth-settings-page .form-switch .form-check-input:focus {
        border-color: var(--auth-primary);
        box-shadow: 0 0 0 .15rem rgba(var(--bs-primary-rgb, 21, 114, 232), .12);
    }

    /* Form Controls */
    .auth-settings-page .form-label {
        font-size: 12px;
        font-weight: 600;
        color: var(--bs-emphasis-color, #344054);
        margin-bottom: 6px;
    }

    .auth-settings-page .form-control,
    .auth-settings-page .form-select {
        min-height: 37px;
        border-radius: 6px !important;
        border-color: var(--auth-border);
        font-size: 13px;
        color: var(--auth-body);
        box-shadow: none;
    }

    .auth-settings-page .form-control:focus,
    .auth-settings-page .form-select:focus {
        border-color: var(--auth-primary);
        box-shadow: 0 0 0 .15rem rgba(var(--bs-primary-rgb, 21, 114, 232), .10);
    }

    .auth-settings-page .form-control::placeholder {
        color: #98a2b3;
        opacity: 1;
    }

    .auth-settings-page .form-text {
        display: block;
        font-size: 11px;
        line-height: 1.55;
        margin-top: 5px;
    }

    .auth-settings-page .input-group .form-control {
        min-width: 0;
    }

    .auth-settings-page .input-group .btn {
        min-width: 42px;
        border-radius: 0 6px 6px 0 !important;
    }

    .auth-settings-page .auth-code-value {
        display: block;
        padding: 8px 10px;
        background: var(--auth-soft);
        border: 1px solid var(--auth-border);
        border-radius: 6px;
        font-size: 11px;
        line-height: 1.6;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    /* Role Permissions */
    .auth-settings-page .auth-role-option {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        padding: 7px 0;
    }

    .auth-settings-page .auth-role-option .form-check-input {
        flex-shrink: 0;
        margin-top: 3px;
        cursor: pointer;
    }

    .auth-settings-page .auth-role-option .form-check-label {
        font-size: 12px;
        line-height: 1.5;
        cursor: pointer;
        overflow-wrap: anywhere;
    }

    /* Buttons */
    .auth-settings-page .btn {
        min-height: 36px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        transition: background-color .15s ease, border-color .15s ease;
    }

    .auth-settings-page .auth-save-button {
        min-height: 38px;
        padding-left: 17px;
        padding-right: 17px;
    }

    .auth-settings-page .auth-action-card .btn {
        min-height: 39px;
    }

    /* Alerts */
    .auth-settings-page .alert {
        border-radius: 7px;
        font-size: 12px;
        line-height: 1.6;
    }

    .auth-settings-page .auth-info-alert {
        background: var(--auth-soft);
        border: 1px solid var(--auth-border);
        color: var(--auth-muted);
    }

    /* Small Layout Improvements */
    .auth-settings-page .auth-card,
    .auth-settings-page .row > div {
        min-width: 0;
    }

    .auth-settings-page .auth-card .card-body,
    .auth-settings-page .auth-card .card-header {
        min-width: 0;
    }

    /* Tablet */
    @media (max-width: 991.98px) {
        .auth-settings-page .auth-card .card-body {
            padding: 18px;
        }

        .auth-settings-page .auth-card .card-header {
            padding: 14px 18px;
        }
    }

    /* Mobile */
    @media (max-width: 575.98px) {
        .auth-settings-page {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        .auth-settings-page .auth-page-header {
            align-items: stretch !important;
        }

        .auth-settings-page .auth-page-header .auth-header-actions,
        .auth-settings-page .auth-page-header .auth-header-actions .btn {
            width: 100%;
        }

        .auth-settings-page .auth-card .card-body {
            padding: 15px;
        }

        .auth-settings-page .auth-card .card-header {
            padding: 13px 15px;
        }

        .auth-settings-page .auth-setting-row {
            gap: 10px;
            padding: 12px 0;
        }

        .auth-settings-page .auth-setting-label {
            font-size: 12px;
        }

        .auth-settings-page .auth-setting-description {
            font-size: 11px;
        }

        .auth-settings-page .form-switch {
            padding-left: 2.5em;
        }

        .auth-settings-page .form-switch .form-check-input {
            margin-left: -2.5em;
        }

        .auth-settings-page .auth-section-title {
            font-size: 13px;
        }

        .auth-settings-page .auth-page-description {
            font-size: 12px;
        }

        .auth-settings-page .auth-code-value {
            font-size: 10px;
        }
    }

    @media (max-width: 360px) {
        .auth-settings-page .auth-setting-row {
            gap: 7px;
        }

        .auth-settings-page .auth-card .card-body {
            padding: 12px;
        }

        .auth-settings-page .auth-card .card-header {
            padding: 12px;
        }
    }
</style>

<div class="container-fluid px-3 px-md-4 py-3 auth-settings-page">

    {{-- Page Header --}}
    <div class="auth-page-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="auth-page-title mb-1">Login & Security Settings</h4>
            <p class="auth-page-description mb-0">Manage login, signup, OAuth & 2FA.</p>
        </div>

        <div class="auth-header-actions">
            <button type="submit" form="authSettingsForm" class="btn btn-primary auth-save-button shadow-sm d-inline-flex align-items-center justify-content-center">
                <i class="fas fa-save me-2"></i> Save Settings
            </button>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 mb-3 border-0 shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 mb-3 border-0 shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 mb-3 border-0 shadow-sm" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            Please check the form errors below.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @php
        $formAction = Route::has('admin.settings.auth.update') ? route('admin.settings.auth.update') : url('/admin/settings/auth');
        $rawGoogleSecret = $settings['google_client_secret'] ?? '';
        $isGoogleSecretConfigured = !empty($rawGoogleSecret) && !str_contains((string)$rawGoogleSecret, '•••');
    @endphp

    <form id="authSettingsForm" action="{{ $formAction }}" method="POST">
        @csrf

        <div class="row g-3 g-xl-4">

            {{-- Left Column: Authentication & Registration --}}
            <div class="col-12 col-lg-7">

                {{-- 1. Login Methods Management --}}
                <div class="card auth-card mb-4">

                    <div class="card-header">
                        <h6 class="auth-section-title">
                            <span class="auth-section-icon text-primary">
                                <i class="fas fa-sign-in-alt"></i>
                            </span>
                            Active Login Methods
                        </h6>
                    </div>

                    <div class="card-body">

                        <div class="alert auth-info-alert py-2 px-3 mb-3">
                            <i class="fas fa-info-circle text-info me-2"></i>
                            Keep at least one login method active. Disabled methods are hidden from the storefront.
                        </div>

                        {{-- Email + Password --}}
                        <div class="auth-setting-row">
                            <div class="auth-setting-content">
                                <label class="form-check-label auth-setting-label">Email + Password Login</label>
                                <span class="auth-setting-description">Sign in with a registered email and password.</span>
                            </div>

                            <div class="form-check form-switch m-0">
                                <input class="form-check-input login-method-toggle" type="checkbox" name="login_email_password" value="1" id="toggleEmailPassword" {{ !empty($settings['login_email_password']) ? 'checked' : '' }} style="cursor: pointer;">
                            </div>
                        </div>

                        {{-- Phone Number + Password --}}
                        <div class="auth-setting-row">
                            <div class="auth-setting-content">
                                <label class="form-check-label auth-setting-label">Phone + Password Login</label>
                                <span class="auth-setting-description">Sign in with a verified mobile number and password.</span>
                            </div>

                            <div class="form-check form-switch m-0">
                                <input class="form-check-input login-method-toggle" type="checkbox" name="login_phone_password" value="1" id="togglePhonePassword" {{ !empty($settings['login_phone_password']) ? 'checked' : '' }} style="cursor: pointer;">
                            </div>
                        </div>

                        {{-- Phone Number + OTP --}}
                        <div class="auth-setting-row">
                            <div class="auth-setting-content">
                                <label class="form-check-label auth-setting-label">Phone + OTP Login</label>
                                <span class="auth-setting-description">Passwordless sign-in using a 6-digit SMS code.</span>
                            </div>

                            <div class="form-check form-switch m-0">
                                <input class="form-check-input login-method-toggle" type="checkbox" name="login_phone_otp" value="1" id="togglePhoneOtp" {{ !empty($settings['login_phone_otp']) ? 'checked' : '' }} style="cursor: pointer;">
                            </div>
                        </div>

                        {{-- Email + OTP --}}
                        <div class="auth-setting-row">
                            <div class="auth-setting-content">
                                <label class="form-check-label auth-setting-label">Email + OTP Login</label>
                                <span class="auth-setting-description">Passwordless sign-in using a one-time email code.</span>
                            </div>

                            <div class="form-check form-switch m-0">
                                <input class="form-check-input login-method-toggle" type="checkbox" name="login_email_otp" value="1" id="toggleEmailOtp" {{ !empty($settings['login_email_otp']) ? 'checked' : '' }} style="cursor: pointer;">
                            </div>
                        </div>

                        {{-- Google Login --}}
                        <div class="auth-setting-row">
                            <div class="auth-setting-content">
                                <label class="form-check-label auth-setting-label">Google OAuth 2.0 Login</label>
                                <span class="auth-setting-description">Allow customers to sign in with Google.</span>
                            </div>

                            <div class="form-check form-switch m-0">
                                <input class="form-check-input login-method-toggle" type="checkbox" name="login_google" value="1" id="toggleGoogleLogin" {{ !empty($settings['login_google']) ? 'checked' : '' }} style="cursor: pointer;">
                            </div>
                        </div>

                        {{-- Default Login Method --}}
                        <div class="mt-4 pt-2">
                            <label class="form-label">
                                Default Login Method <span class="text-danger">*</span>
                            </label>

                            <select name="default_login_method" id="defaultLoginMethodSelect" class="form-select form-select-sm">
                                <option value="email_password" {{ $defaultLoginMethod === 'email_password' ? 'selected' : '' }}>Email + Password</option>
                                <option value="phone_password" {{ $defaultLoginMethod === 'phone_password' ? 'selected' : '' }}>Phone + Password</option>
                                <option value="phone_otp" {{ $defaultLoginMethod === 'phone_otp' ? 'selected' : '' }}>Phone + OTP</option>
                                <option value="email_otp" {{ $defaultLoginMethod === 'email_otp' ? 'selected' : '' }}>Email + OTP</option>
                                <option value="google" {{ $defaultLoginMethod === 'google' ? 'selected' : '' }}>Google OAuth</option>
                            </select>

                            <div class="form-text">
                                The default tab displayed on the customer sign-in page.
                            </div>
                        </div>

                    </div>
                </div>

                {{-- 2. Registration Rules & Verification --}}
                <div class="card auth-card mb-4">

                    <div class="card-header">
                        <h6 class="auth-section-title">
                            <span class="auth-section-icon text-success">
                                <i class="fas fa-user-plus"></i>
                            </span>
                            Registration &amp; Verification
                        </h6>
                    </div>

                    <div class="card-body">

                        {{-- Email Registration --}}
                        <div class="auth-setting-row">
                            <div class="auth-setting-content">
                                <label class="form-check-label auth-setting-label">Allow Email Registration</label>
                                <span class="auth-setting-description">Allow customers to register with an email address.</span>
                            </div>

                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" name="registration_email_enabled" value="1" {{ !empty($settings['registration_email_enabled']) ? 'checked' : '' }} style="cursor: pointer;">
                            </div>
                        </div>

                        {{-- Phone Registration --}}
                        <div class="auth-setting-row">
                            <div class="auth-setting-content">
                                <label class="form-check-label auth-setting-label">Allow Phone Registration</label>
                                <span class="auth-setting-description">Allow customers to register with a mobile number.</span>
                            </div>

                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" name="registration_phone_enabled" value="1" {{ !empty($settings['registration_phone_enabled']) ? 'checked' : '' }} style="cursor: pointer;">
                            </div>
                        </div>

                        {{-- Required Email --}}
                        <div class="auth-setting-row">
                            <div class="auth-setting-content">
                                <label class="form-check-label auth-setting-label">Require Email Address</label>
                                <span class="auth-setting-description">If disabled, customers can register using only a phone number.</span>
                            </div>

                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" name="registration_email_required" value="1" {{ !empty($settings['registration_email_required']) ? 'checked' : '' }} style="cursor: pointer;">
                            </div>
                        </div>

                        {{-- Required Phone --}}
                        <div class="auth-setting-row">
                            <div class="auth-setting-content">
                                <label class="form-check-label auth-setting-label">Require Phone Number</label>
                                <span class="auth-setting-description">Require every account to have a valid BD mobile number.</span>
                            </div>

                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" name="registration_phone_required" value="1" {{ !empty($settings['registration_phone_required']) ? 'checked' : '' }} style="cursor: pointer;">
                            </div>
                        </div>

                        {{-- Phone OTP Verification --}}
                        <div class="auth-setting-row">
                            <div class="auth-setting-content">
                                <label class="form-check-label auth-setting-label">SMS OTP Verification</label>
                                <span class="auth-setting-description">Verify phone numbers via OTP before account activation.</span>
                            </div>

                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" name="registration_otp_verification" value="1" {{ !empty($settings['registration_otp_verification']) ? 'checked' : '' }} style="cursor: pointer;">
                            </div>
                        </div>

                        {{-- Email Verification --}}
                        <div class="auth-setting-row">
                            <div class="auth-setting-content">
                                <label class="form-check-label auth-setting-label">Email Verification</label>
                                <span class="auth-setting-description">Send an OTP or confirmation link after registration.</span>
                            </div>

                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" name="registration_email_verification" value="1" {{ !empty($settings['registration_email_verification']) ? 'checked' : '' }} style="cursor: pointer;">
                            </div>
                        </div>

                        {{-- Google Auto Registration --}}
                        <div class="auth-setting-row">
                            <div class="auth-setting-content">
                                <label class="form-check-label auth-setting-label">Google Auto Registration</label>
                                <span class="auth-setting-description">Allow new users to register through Google.</span>
                            </div>

                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" name="registration_google_enabled" value="1" {{ !empty($settings['registration_google_enabled']) ? 'checked' : '' }} style="cursor: pointer;">
                            </div>
                        </div>

                        {{-- Existing Account Linking --}}
                        <div class="auth-setting-row">
                            <div class="auth-setting-content">
                                <label class="form-check-label auth-setting-label">Safe Account Linking</label>
                                <span class="auth-setting-description">Link Google sign-in to existing accounts with matching verified emails.</span>
                            </div>

                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" name="account_linking_enabled" value="1" {{ !empty($settings['account_linking_enabled']) ? 'checked' : '' }} style="cursor: pointer;">
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            {{-- Right Column: OAuth, OTP & 2FA --}}
            <div class="col-12 col-lg-5">

                {{-- 3. Google OAuth Credentials --}}
                <div class="card auth-card mb-4">

                    <div class="card-header">
                        <h6 class="auth-section-title">
                            <span class="auth-section-icon text-danger">
                                <i class="fab fa-google"></i>
                            </span>
                            Google OAuth Gateway
                        </h6>
                    </div>

                    <div class="card-body">

                        <div class="mb-3">
                            <label class="form-label">Google Client ID</label>

                            <input type="text" name="google_client_id" class="form-control form-control-sm font-monospace" value="{{ old('google_client_id', $settings['google_client_id'] ?? '') }}" placeholder="123456789-xxxx.apps.googleusercontent.com">
                        </div>

                        <div class="mb-3">

                            <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                                <label class="form-label mb-0">Google Client Secret</label>

                                @if($isGoogleSecretConfigured)
                                    <span class="text-success small fw-semibold text-nowrap">
                                        <i class="fas fa-check-circle me-1"></i> Configured
                                    </span>
                                @else
                                    <span class="text-danger small fw-semibold text-nowrap">
                                        <i class="fas fa-times-circle me-1"></i> Not Set
                                    </span>
                                @endif
                            </div>

                            <div class="input-group">
                                <input type="password"
                                       name="google_client_secret"
                                       id="googleClientSecretInput"
                                       class="form-control form-control-sm font-monospace"
                                       value="{{ old('google_client_secret', ($isGoogleSecretConfigured ? $rawGoogleSecret : '')) }}"
                                       placeholder="Paste Google Client Secret (GOCSPX-...)"
                                       autocomplete="new-password">

                                <button class="btn btn-outline-secondary btn-sm"
                                        type="button"
                                        id="btnToggleSecretVisibility"
                                        title="Show / Hide Secret">
                                    <i class="fas fa-eye text-muted" id="secretEyeIcon"></i>
                                </button>
                            </div>

                            <div class="form-text">
                                Use the eye icon to view or edit your secret before saving.
                            </div>

                        </div>

                        <div class="mb-0">
                            <label class="form-label">Authorized Redirect URI</label>

                            <input type="text" name="google_redirect_url" class="form-control form-control-sm font-monospace bg-light" value="{{ old('google_redirect_url', $settings['google_redirect_url'] ?? '/auth/google/callback') }}">

                            <div class="form-text">
                                Add this exact URL to your Google Cloud Console Authorized Redirect URIs:

                                <code class="auth-code-value text-primary mt-2">{{ url($settings['google_redirect_url'] ?? '/auth/google/callback') }}</code>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- 4. OTP Lifecycle & Security --}}
                <div class="card auth-card mb-4">

                    <div class="card-header">
                        <h6 class="auth-section-title">
                            <span class="auth-section-icon text-warning">
                                <i class="fas fa-shield-alt"></i>
                            </span>
                            OTP &amp; Security Limits
                        </h6>
                    </div>

                    <div class="card-body">

                        <div class="row g-3">

                            <div class="col-12 col-sm-6">
                                <label class="form-label">Expiry (Minutes)</label>

                                <input type="number" name="otp_expiry_minutes" min="1" max="60" class="form-control form-control-sm" value="{{ old('otp_expiry_minutes', $settings['otp_expiry_minutes'] ?? 5) }}" required>

                                <div class="form-text">Default: 5 minutes</div>
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label">Resend Cooldown (Sec)</label>

                                <input type="number" name="otp_resend_cooldown_seconds" min="10" max="300" class="form-control form-control-sm" value="{{ old('otp_resend_cooldown_seconds', $settings['otp_resend_cooldown_seconds'] ?? 60) }}" required>

                                <div class="form-text">Default: 60 seconds</div>
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label">Max Verification Attempts</label>

                                <input type="number" name="otp_max_attempts" min="1" max="10" class="form-control form-control-sm" value="{{ old('otp_max_attempts', $settings['otp_max_attempts'] ?? 3) }}" required>

                                <div class="form-text">Locks OTP after failed attempts</div>
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label">Daily Limit / Phone</label>

                                <input type="number" name="otp_daily_limit" min="1" max="100" class="form-control form-control-sm" value="{{ old('otp_daily_limit', $settings['otp_daily_limit'] ?? 10) }}" required>

                                <div class="form-text">Helps prevent SMS abuse</div>
                            </div>

                        </div>

                    </div>
                </div>

                {{-- 5. Two-Factor Authentication --}}
                <div class="card auth-card mb-4">

                    <div class="card-header">
                        <h6 class="auth-section-title">
                            <span class="auth-section-icon text-info">
                                <i class="fas fa-lock"></i>
                            </span>
                            Two-Factor Authentication (2FA)
                        </h6>
                    </div>

                    <div class="card-body">

                        {{-- Global 2FA --}}
                        <div class="auth-setting-row pt-0">
                            <div class="auth-setting-content">
                                <label class="form-check-label auth-setting-label">Global 2FA System</label>
                                <span class="auth-setting-description">Enable or disable the second verification step.</span>
                            </div>

                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" name="two_factor_global" value="1" id="twoFaGlobalToggle" {{ !empty($settings['two_factor_global']) ? 'checked' : '' }} style="cursor: pointer;">
                            </div>
                        </div>

                        {{-- 2FA Configuration --}}
                        <div class="row g-3 mt-2 mb-3">

                            <div class="col-12 col-sm-6">
                                <label class="form-label">Enforcement Mode</label>

                                <select name="two_factor_mode" class="form-select form-select-sm">
                                    <option value="optional" {{ ($settings['two_factor_mode'] ?? '') === 'optional' ? 'selected' : '' }}>Optional (User Opt-in)</option>
                                    <option value="mandatory" {{ ($settings['two_factor_mode'] ?? '') === 'mandatory' ? 'selected' : '' }}>Mandatory (Enforced)</option>
                                </select>
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label">Dispatch Channel</label>

                                <select name="two_factor_primary_type" class="form-select form-select-sm">
                                    <option value="sms" {{ ($settings['two_factor_primary_type'] ?? '') === 'sms' ? 'selected' : '' }}>SMS OTP</option>
                                    <option value="email" {{ ($settings['two_factor_primary_type'] ?? '') === 'email' ? 'selected' : '' }}>Email OTP</option>
                                    <option value="both" {{ ($settings['two_factor_primary_type'] ?? '') === 'both' ? 'selected' : '' }}>Both (SMS + Email)</option>
                                </select>
                            </div>

                        </div>

                        {{-- Role Level Enforcement --}}
                        <div class="pt-3 border-top">

                            <label class="form-label d-block mb-2">
                                Role Permissions &amp; Requirements
                            </label>

                            <div class="auth-role-option">
                                <input class="form-check-input" type="checkbox" name="two_factor_for_admin" value="1" id="twoFaAdminCheck" {{ !empty($settings['two_factor_for_admin']) ? 'checked' : '' }}>

                                <label class="form-check-label text-dark" for="twoFaAdminCheck">
                                    Enforce 2FA on Super Admin / Admin Accounts
                                </label>
                            </div>

                            <div class="auth-role-option">
                                <input class="form-check-input" type="checkbox" name="two_factor_for_staff" value="1" id="twoFaStaffCheck" {{ !empty($settings['two_factor_for_staff']) ? 'checked' : '' }}>

                                <label class="form-check-label text-dark" for="twoFaStaffCheck">
                                    Enforce 2FA on Managers &amp; Staff Users
                                </label>
                            </div>

                            <div class="auth-role-option">
                                <input class="form-check-input" type="checkbox" name="two_factor_for_customer" value="1" id="twoFaCustomerCheck" {{ !empty($settings['two_factor_for_customer']) ? 'checked' : '' }}>

                                <label class="form-check-label text-dark" for="twoFaCustomerCheck">
                                    Enforce / Allow 2FA on Registered Customer Accounts
                                </label>
                            </div>

                        </div>

                    </div>
                </div>

                {{-- Action Card --}}
                <div class="card auth-card auth-action-card mb-4">
                    <div class="card-body p-3">
                        <div class="d-grid gap-2">

                            <button type="submit" class="btn btn-primary shadow-sm">
                                <i class="fas fa-save me-2"></i> Save Settings
                            </button>

                            <a href="{{ route('admin.dashboard') }}" class="btn btn-light border text-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>

                        </div>
                    </div>
                </div>

            </div>

        </div>
    </form>
</div>

{{-- Interactive Dynamic Protection Script Engine --}}
<script>
(function() {
    function initAuthSettingsEngine() {
        const toggles = document.querySelectorAll('.login-method-toggle');
        const defaultSelect = document.getElementById('defaultLoginMethodSelect');
        const form = document.getElementById('authSettingsForm');

        // Toggle Secret Visibility
        const btnToggleSecret = document.getElementById('btnToggleSecretVisibility');
        const secretInput = document.getElementById('googleClientSecretInput');
        const secretIcon = document.getElementById('secretEyeIcon');

        if (btnToggleSecret && secretInput && secretIcon) {
            btnToggleSecret.addEventListener('click', function() {
                if (secretInput.type === 'password') {
                    secretInput.type = 'text';
                    secretIcon.className = 'fas fa-eye-slash text-primary';
                } else {
                    secretInput.type = 'password';
                    secretIcon.className = 'fas fa-eye text-muted';
                }
            });
        }

        const mapToggleToDefaultVal = {
            'toggleEmailPassword': 'email_password',
            'togglePhonePassword': 'phone_password',
            'togglePhoneOtp':      'phone_otp',
            'toggleEmailOtp':      'email_otp',
            'toggleGoogleLogin':   'google'
        };

        function syncDefaultDropdown() {
            if (!defaultSelect) return;

            let activeCount = 0;
            const currentSelected = defaultSelect.value;
            let currentSelectedStillValid = false;

            toggles.forEach(toggle => {
                const optVal = mapToggleToDefaultVal[toggle.id];
                if (!optVal) return;

                const opt = defaultSelect.querySelector(`option[value="${optVal}"]`);
                if (opt) {
                    if (toggle.checked) {
                        opt.disabled = false;
                        opt.hidden = false;
                        activeCount++;
                        if (optVal === currentSelected) {
                            currentSelectedStillValid = true;
                        }
                    } else {
                        opt.disabled = true;
                        opt.hidden = true;
                    }
                }
            });

            if (!currentSelectedStillValid) {
                const firstAvailable = defaultSelect.querySelector('option:not([disabled])');
                if (firstAvailable) {
                    defaultSelect.value = firstAvailable.value;
                }
            }
        }

        toggles.forEach(toggle => {
            toggle.addEventListener('change', function() {
                const checkedToggles = document.querySelectorAll('.login-method-toggle:checked');
                if (checkedToggles.length === 0) {
                    this.checked = true;
                    alert('Security Safeguard: At least one login method must remain active!');
                }
                syncDefaultDropdown();
            });
        });

        if (form) {
            form.addEventListener('submit', function(e) {
                const checkedToggles = document.querySelectorAll('.login-method-toggle:checked');
                if (checkedToggles.length === 0) {
                    e.preventDefault();
                    alert('Security Violation: You cannot disable all login methods.');
                    return false;
                }
            });
        }

        syncDefaultDropdown();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAuthSettingsEngine);
    } else {
        initAuthSettingsEngine();
    }
})();
</script>
@endsection

@section('content')
    @yield('admin_content')
@endsection