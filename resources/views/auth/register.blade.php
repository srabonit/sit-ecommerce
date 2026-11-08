@extends('layouts.app')

@php
    // Central Global Settings Cache Resolver
    static $registerSettingsCache = null;
    if ($registerSettingsCache === null) {
        $registerSettingsCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
            return \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $authSettings = $registerSettingsCache ?? [];

    $siteTitle = config('app.name', 'Shop Now BD');
    $storeLogo = $authSettings['header_logo'] ?? ($authSettings['site_icon'] ?? null);

    // Auth & Form Dynamic Color Resolvers
    $authPageBg       = $authSettings['color_auth_page_bg'] ?? ($authSettings['color_site_bg'] ?? '#f8fafc');
    $authCardBg       = $authSettings['color_auth_card_bg'] ?? '#ffffff';
    $authCardBorder   = $authSettings['color_auth_card_border'] ?? '#edf2f7';
    $authHeadingColor = $authSettings['color_auth_heading'] ?? '#0f172a';
    $authSubtextColor = $authSettings['color_auth_text'] ?? '#64748b';
    
    $authInputBg      = $authSettings['color_auth_input_bg'] ?? '#ffffff';
    $authInputBorder  = $authSettings['color_auth_input_border'] ?? '#cbd5e1';
    $authInputFocus   = $authSettings['color_auth_input_focus_border'] ?? ($authSettings['color_hdr_menu_hover_text'] ?? '#2563eb');
    
    $authBtnBg        = $authSettings['color_auth_btn_bg'] ?? ($authSettings['color_hdr_menu_hover_text'] ?? '#2563eb');
    $authBtnText      = $authSettings['color_auth_btn_text'] ?? '#ffffff';
    $authBtnHover     = $authSettings['color_auth_btn_hover'] ?? '#1d4ed8';

    $authLinkColor    = $authSettings['color_auth_link'] ?? ($authSettings['color_hdr_menu_hover_text'] ?? '#2563eb');
    $authLinkHover    = $authSettings['color_auth_link_hover'] ?? '#1d4ed8';

    $resolveAuthLogo = function($path) {
        if (empty($path)) return null;
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path, '');
        }
        if (function_exists('show_image')) {
            return show_image($path, '');
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        $clean = ltrim(str_replace(['storage/', 'public/'], '', $path), '/');
        if (file_exists(public_path($clean))) return asset($clean);
        if (file_exists(public_path('uploads/' . $clean))) return asset('uploads/' . $clean);
        return asset($clean);
    };

    $logoUrl = $resolveAuthLogo($storeLogo);

    // Dynamic Registration Rules Resolver
    if (!isset($settings)) {
        $authSettingService = app(\App\Services\Auth\AuthenticationSettingsService::class);
        $settings = $authSettingService->all();
    }

    $isPhoneEnabled      = !empty($settings['registration_phone_enabled']);
    $isEmailEnabled      = !empty($settings['registration_email_enabled']);
    $isPhoneRequired     = !empty($settings['registration_phone_required']);
    $isEmailRequired     = !empty($settings['registration_email_required']);
    $isPhoneOtpRequired  = !empty($settings['registration_otp_verification']);
    $isEmailOtpRequired  = !empty($settings['registration_email_verification']);
    $isGoogleActive      = !empty($settings['login_google']) && !empty($settings['registration_google_enabled']);

    $verifyPhoneOtpRoute = Route::has('auth.otp.register.verify') ? route('auth.otp.register.verify') : url('/auth/otp/register/verify');
    $verifyEmailOtpRoute = Route::has('auth.email_otp.register.verify') ? route('auth.email_otp.register.verify') : url('/auth/email-otp/register/verify');
@endphp

@section('title', 'Register - ' . $siteTitle)

@section('content')
<style>
    :root {
        --auth-page-bg: {{ $authPageBg }};
        --auth-card-bg: {{ $authCardBg }};
        --auth-card-border: {{ $authCardBorder }};
        --auth-heading: {{ $authHeadingColor }};
        --auth-subtext: {{ $authSubtextColor }};
        --auth-input-bg: {{ $authInputBg }};
        --auth-input-border: {{ $authInputBorder }};
        --auth-input-focus: {{ $authInputFocus }};
        --auth-btn-bg: {{ $authBtnBg }};
        --auth-btn-text: {{ $authBtnText }};
        --auth-btn-hover: {{ $authBtnHover }};
        --auth-link-color: {{ $authLinkColor }};
        --auth-link-hover: {{ $authLinkHover }};
    }

    .auth-page-wrapper {
        background-color: var(--auth-page-bg) !important;
        min-height: 80vh;
    }

    .auth-card-box {
        background-color: var(--auth-card-bg) !important;
        border: 1px solid var(--auth-card-border) !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04) !important;
    }

    /* Strict text-decoration none for all links */
    .auth-card-box a,
    a.auth-theme-link {
        text-decoration: none !important;
        color: var(--auth-link-color) !important;
        transition: color 0.15s ease;
    }

    .auth-card-box a:hover,
    a.auth-theme-link:hover {
        color: var(--auth-link-hover) !important;
        text-decoration: none !important;
    }

    .auth-heading-text {
        color: var(--auth-heading) !important;
    }

    .auth-subtext-color {
        color: var(--auth-subtext) !important;
    }

    .auth-form-input {
        background-color: var(--auth-input-bg) !important;
        border: 1px solid var(--auth-input-border) !important;
        color: var(--auth-heading) !important;
        transition: all 0.2s ease;
    }

    .auth-form-input:focus {
        border-color: var(--auth-input-focus) !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
        background-color: #ffffff !important;
    }

    .auth-form-input.is-verified {
        border-color: #10b981 !important;
        background-color: #f0fdf4 !important;
        color: #065f46 !important;
    }

    .auth-submit-btn {
        background-color: var(--auth-btn-bg) !important;
        border-color: var(--auth-btn-bg) !important;
        color: var(--auth-btn-text) !important;
        transition: all 0.2s ease;
    }

    .auth-submit-btn:hover {
        background-color: var(--auth-btn-hover) !important;
        border-color: var(--auth-btn-hover) !important;
        color: var(--auth-btn-text) !important;
    }

    .auth-icon-circle {
        background-color: var(--auth-btn-bg) !important;
        color: var(--auth-btn-text) !important;
    }

    .btn-google-auth {
        background: #ffffff;
        color: #334155;
        border: 1px solid #cbd5e1;
        font-weight: 600;
        font-size: 13.5px;
        transition: all 0.2s ease;
    }

    .btn-google-auth:hover {
        background: #f8fafc;
        border-color: #94a3b8;
        color: #0f172a;
    }

    .auth-divider {
        display: flex;
        align-items: center;
        text-align: center;
        color: #94a3b8;
        font-size: 11.5px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 1.25rem 0;
    }

    .auth-divider::before,
    .auth-divider::after {
        content: '';
        flex: 1;
        border-bottom: 1px solid #e2e8f0;
    }

    .auth-divider::before {
        margin-right: .75em;
    }

    .auth-divider::after {
        margin-left: .75em;
    }
</style>

<div class="py-5 auth-page-wrapper d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card border-0 rounded-4 p-4 auth-card-box">
                    
                    {{-- Logo & Heading --}}
                    <div class="text-center mb-4">
                        @if(!empty($logoUrl))
                            <a href="{{ route('home') }}" class="d-inline-block mb-3 text-decoration-none">
                                <img src="{{ $logoUrl }}" alt="{{ $siteTitle }}" style="max-height: 48px; max-width: 160px; object-fit: contain;">
                            </a>
                        @else
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2 auth-icon-circle shadow-xs" style="width: 48px; height: 48px;">
                                <i class="fas fa-user-plus fs-5"></i>
                            </div>
                        @endif
                        <h4 class="fw-bold mb-1 auth-heading-text">Create an Account</h4>
                        <p class="small mb-0 auth-subtext-color">Register to track packages, save addresses &amp; rewards</p>
                    </div>

                    {{-- Session Alerts --}}
                    @if(session('error'))
                        <div class="alert alert-danger py-2 small rounded-3 mb-3 border-0 shadow-xs">
                            <i class="fas fa-exclamation-triangle me-1"></i> {{ session('error') }}
                        </div>
                    @endif

                    <div id="registerOtpAlertBox" class="d-none alert py-2 small rounded-3 mb-3 border-0 shadow-xs"></div>

                    <form action="{{ route('register') }}" method="POST" id="registerForm" novalidate>
                        @csrf
                        
                        {{-- 1. Full Name --}}
                        <div class="mb-3">
                            <label class="form-label small fw-bold auth-heading-text">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control rounded-3 py-2 auth-form-input @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Enter full name" required autofocus>
                            @error('name')
                                <div class="invalid-feedback small fw-semibold">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- 2. Phone Number (Conditional) --}}
                        @if($isPhoneEnabled)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-bold auth-heading-text mb-0">
                                        Phone Number 
                                        @if($isPhoneRequired)<span class="text-danger">*</span>@else<span class="text-muted fw-normal">(Optional)</span>@endif
                                    </label>
                                    <span id="phoneVerifiedStatusBadge" class="badge bg-success-subtle text-success border border-success-subtle d-none" style="font-size: 11px; padding: 2px 7px; border-radius: 5px;">
                                        <i class="fas fa-check-circle me-1"></i> Verified
                                    </span>
                                </div>
                                
                                @if($isPhoneOtpRequired)
                                    <div class="input-group">
                                        <input type="tel" name="phone" id="registerPhoneInput" class="form-control rounded-start-3 py-2 auth-form-input font-monospace @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="017XXXXXXXX" maxlength="11" {{ $isPhoneRequired ? 'required' : '' }}>
                                        <button type="button" id="btnSendRegisterOtp" class="btn btn-outline-secondary px-3 small fw-bold" style="border-color: var(--auth-input-border); font-size: 12px; min-width: 90px;">
                                            Send OTP
                                        </button>
                                    </div>
                                    <span class="form-text small text-muted" id="phoneHelpText" style="font-size: 11px;">A registration verification code will be sent to this number.</span>
                                @else
                                    <input type="text" name="phone" class="form-control rounded-3 py-2 auth-form-input @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="017XXXXXXXX" {{ $isPhoneRequired ? 'required' : '' }}>
                                @endif

                                @error('phone')
                                    <div class="invalid-feedback d-block small fw-semibold">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Registration Phone OTP Input Field (Shown if required) --}}
                            @if($isPhoneOtpRequired)
                                <div class="mb-3" id="otpCodeFieldWrapper">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label small fw-bold auth-heading-text mb-0">
                                            SMS Verification Code <span class="text-danger">*</span>
                                        </label>
                                        <span class="small text-muted" id="otpTimerText" style="font-size: 11.5px;"></span>
                                    </div>
                                    <div class="input-group">
                                        <input type="text" name="otp_code" id="registerOtpCodeInput" class="form-control rounded-start-3 py-2 auth-form-input font-monospace fw-bold text-center @error('otp_code') is-invalid @enderror" placeholder="Enter SMS code" maxlength="6" style="letter-spacing: 4px; font-size: 16px;" required>
                                        <button type="button" id="btnVerifyRegisterOtp" class="btn btn-success px-3 small fw-bold" style="font-size: 12px;">
                                            <i class="fas fa-check me-1"></i> Verify Code
                                        </button>
                                    </div>
                                    <div id="otpSuccessFeedback" class="text-success small fw-semibold mt-1 d-none" style="font-size: 12px;">
                                        <i class="fas fa-check-circle me-1"></i> Phone verified successfully!
                                    </div>
                                    @error('otp_code')
                                        <div class="invalid-feedback d-block small fw-semibold">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif
                        @endif

                        {{-- 3. Email Address (Conditional) --}}
                        @if($isEmailEnabled)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-bold auth-heading-text mb-0">
                                        Email Address 
                                        @if($isEmailRequired)<span class="text-danger">*</span>@else<span class="text-muted fw-normal">(Optional)</span>@endif
                                    </label>
                                    <span id="emailVerifiedStatusBadge" class="badge bg-success-subtle text-success border border-success-subtle d-none" style="font-size: 11px; padding: 2px 7px; border-radius: 5px;">
                                        <i class="fas fa-check-circle me-1"></i> Verified
                                    </span>
                                </div>

                                @if($isEmailOtpRequired)
                                    <div class="input-group">
                                        <input type="email" name="email" id="registerEmailInput" class="form-control rounded-start-3 py-2 auth-form-input @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="Enter your Email" {{ $isEmailRequired ? 'required' : '' }}>
                                        <button type="button" id="btnSendRegisterEmailOtp" class="btn btn-outline-secondary px-3 small fw-bold" style="border-color: var(--auth-input-border); font-size: 12px; min-width: 90px;">
                                            Send Code
                                        </button>
                                    </div>
                                    <span class="form-text small text-muted" id="emailHelpText" style="font-size: 11px;">A 6-digit confirmation code will be dispatched to your email.</span>
                                @else
                                    <input type="email" name="email" class="form-control rounded-3 py-2 auth-form-input @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="Enter your Email" {{ $isEmailRequired ? 'required' : '' }}>
                                @endif

                                @error('email')
                                    <div class="invalid-feedback d-block small fw-semibold">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Registration Email OTP Input Field (Shown if required) --}}
                            @if($isEmailOtpRequired)
                                <div class="mb-3" id="emailOtpFieldWrapper">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label small fw-bold auth-heading-text mb-0">
                                            Email Verification Code <span class="text-danger">*</span>
                                        </label>
                                        <span class="small text-muted" id="emailOtpTimerText" style="font-size: 11.5px;"></span>
                                    </div>
                                    <div class="input-group">
                                        <input type="text" name="email_otp_code" id="registerEmailOtpCodeInput" class="form-control rounded-start-3 py-2 auth-form-input font-monospace fw-bold text-center @error('email_otp_code') is-invalid @enderror" placeholder="Enter Email code" maxlength="6" style="letter-spacing: 4px; font-size: 16px;" required>
                                        <button type="button" id="btnVerifyRegisterEmailOtp" class="btn btn-success px-3 small fw-bold" style="font-size: 12px;">
                                            <i class="fas fa-check me-1"></i> Verify Code
                                        </button>
                                    </div>
                                    <div id="emailOtpSuccessFeedback" class="text-success small fw-semibold mt-1 d-none" style="font-size: 12px;">
                                        <i class="fas fa-check-circle me-1"></i> Email address verified successfully!
                                    </div>
                                    @error('email_otp_code')
                                        <div class="invalid-feedback d-block small fw-semibold">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif
                        @endif

                        {{-- 4. Password & Confirmation --}}
                        <div class="row g-2 mb-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold auth-heading-text">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control rounded-3 py-2 auth-form-input @error('password') is-invalid @enderror" placeholder="Min 6 characters" required>
                                @error('password')
                                    <div class="invalid-feedback small fw-semibold">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold auth-heading-text">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" class="form-control rounded-3 py-2 auth-form-input" placeholder="Repeat password" required>
                            </div>
                        </div>

                        {{-- Submit Button --}}
                        <button type="submit" id="btnSubmitRegister" class="btn auth-submit-btn w-100 py-2 rounded-3 fw-bold shadow-sm">
                            Create Account
                        </button>
                    </form>

                    {{-- Google Signup Option --}}
                    @if($isGoogleActive)
                        <div class="auth-divider">
                            <span>or register with</span>
                        </div>

                        @php
                            $googleUrl = Route::has('auth.google') ? route('auth.google') : url('/auth/google');
                        @endphp
                        <a href="{{ $googleUrl }}" class="btn btn-google-auth w-100 py-2 rounded-3 shadow-xs d-flex align-items-center justify-content-center">
                            <svg class="me-2" width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
                                <path d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.874 2.684-6.616z" fill="#4285F4"/>
                                <path d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.258c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z" fill="#34A853"/>
                                <path d="M3.964 10.707c-.18-.54-.282-1.117-.282-1.707s.102-1.167.282-1.707V4.961H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.039l3.007-2.332z" fill="#FBBC05"/>
                                <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.961L3.964 7.293C4.672 5.166 6.656 3.58 9 3.58z" fill="#EA4335"/>
                            </svg>
                            Sign up with Google
                        </a>
                    @endif

                    {{-- Footer: Sign In Link --}}
                    <div class="text-center mt-4 pt-2 border-top" style="border-color: var(--auth-card-border) !important;">
                        <span class="small auth-subtext-color">Already have an account?</span>
                        <a href="{{ route('login') }}" class="small auth-theme-link fw-bold text-decoration-none ms-1">Sign In</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- Interactive Phone & Email Registration Client-Side Logic --}}
<script>
(function() {
    function initRegisterClient() {
        const csrfToken              = '{{ csrf_token() }}';
        const alertBox               = document.getElementById('registerOtpAlertBox');
        const registerForm           = document.getElementById('registerForm');

        // Phone OTP elements
        const btnSendPhoneOtp        = document.getElementById('btnSendRegisterOtp');
        const btnVerifyPhoneOtp      = document.getElementById('btnVerifyRegisterOtp');
        const phoneInput             = document.getElementById('registerPhoneInput');
        const phoneOtpCodeInput      = document.getElementById('registerOtpCodeInput');
        const phoneTimerText         = document.getElementById('otpTimerText');
        const phoneStatusBadge       = document.getElementById('phoneVerifiedStatusBadge');
        const phoneFeedbackSuccess   = document.getElementById('otpSuccessFeedback');
        const isPhoneOtpRequired     = {{ $isPhoneOtpRequired ? 'true' : 'false' }};

        // Email OTP elements
        const btnSendEmailOtp        = document.getElementById('btnSendRegisterEmailOtp');
        const btnVerifyEmailOtp      = document.getElementById('btnVerifyRegisterEmailOtp');
        const emailInput             = document.getElementById('registerEmailInput');
        const emailOtpCodeInput      = document.getElementById('registerEmailOtpCodeInput');
        const emailTimerText         = document.getElementById('emailOtpTimerText');
        const emailStatusBadge       = document.getElementById('emailVerifiedStatusBadge');
        const emailFeedbackSuccess   = document.getElementById('emailOtpSuccessFeedback');
        const isEmailOtpRequired     = {{ $isEmailOtpRequired ? 'true' : 'false' }};

        let isPhoneVerified          = false;
        let isEmailVerified          = false;
        let phoneTimerInterval       = null;
        let emailTimerInterval       = null;

        function showAlert(msg, isSuccess = false, htmlAppend = '') {
            if (!alertBox) return;
            alertBox.innerHTML = msg + htmlAppend;
            alertBox.className = 'alert py-2 small rounded-3 mb-3 border-0 shadow-xs ' + (isSuccess ? 'alert-success' : 'alert-danger');
            alertBox.classList.remove('d-none');
        }

        // ==========================
        // 1. PHONE OTP PIPELINE
        // ==========================
        function startPhoneTimer(seconds) {
            clearInterval(phoneTimerInterval);
            let rem = seconds;

            if (btnSendPhoneOtp) {
                btnSendPhoneOtp.disabled = true;
                btnSendPhoneOtp.innerHTML = '<i class="fas fa-check me-1"></i> Sent';
            }

            phoneTimerInterval = setInterval(function() {
                if (rem <= 0) {
                    clearInterval(phoneTimerInterval);
                    if (phoneTimerText) phoneTimerText.textContent = '';
                    if (btnSendPhoneOtp && !isPhoneVerified) {
                        btnSendPhoneOtp.disabled = false;
                        btnSendPhoneOtp.innerHTML = '<i class="fas fa-redo me-1"></i> Resend OTP';
                    }
                    return;
                }
                const m = Math.floor(rem / 60);
                const s = rem % 60;
                if (phoneTimerText) {
                    phoneTimerText.textContent = `Resend in ${m < 10 ? '0' : ''}${m}:${s < 10 ? '0' : ''}${s}`;
                }
                rem--;
            }, 1000);
        }

        if (btnSendPhoneOtp && phoneInput) {
            btnSendPhoneOtp.addEventListener('click', function() {
                const phone = phoneInput.value.trim();
                if (!phone || phone.length < 11) {
                    showAlert('Please enter a valid 11-digit mobile number first.');
                    phoneInput.focus();
                    return;
                }

                btnSendPhoneOtp.disabled = true;
                btnSendPhoneOtp.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending...';

                fetch('{{ route("auth.otp.register.send") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ phone: phone })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, true);
                        startPhoneTimer(data.cooldown_seconds || 60);
                        if (phoneOtpCodeInput) phoneOtpCodeInput.focus();
                    } else {
                        const loginLink = ' <a href="{{ route("login") }}" class="fw-bold text-danger text-decoration-underline ms-1">Sign In</a>';
                        showAlert(data.message || 'Failed to send OTP.', false, (data.message && data.message.includes('already registered')) ? loginLink : '');
                        btnSendPhoneOtp.disabled = false;
                        btnSendPhoneOtp.innerHTML = 'Send OTP';
                    }
                })
                .catch(() => {
                    showAlert('Network error sending verification code.');
                    btnSendPhoneOtp.disabled = false;
                    btnSendPhoneOtp.innerHTML = 'Send OTP';
                });
            });
        }

        if (btnVerifyPhoneOtp && phoneOtpCodeInput && phoneInput) {
            btnVerifyPhoneOtp.addEventListener('click', function() {
                const phone = phoneInput.value.trim();
                const code  = phoneOtpCodeInput.value.trim();

                if (!code || code.length < 4) {
                    showAlert('Please enter the SMS verification code.');
                    phoneOtpCodeInput.focus();
                    return;
                }

                btnVerifyPhoneOtp.disabled = true;
                btnVerifyPhoneOtp.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Verifying...';

                fetch('{{ $verifyPhoneOtpRoute }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ phone: phone, otp_code: code })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        isPhoneVerified = true;
                        clearInterval(phoneTimerInterval);
                        if (phoneTimerText) phoneTimerText.textContent = '';

                        showAlert(data.message || 'Phone number verified successfully!', true);
                        if (phoneStatusBadge) phoneStatusBadge.classList.remove('d-none');
                        if (phoneFeedbackSuccess) phoneFeedbackSuccess.classList.remove('d-none');

                        phoneInput.setAttribute('readonly', 'readonly');
                        phoneInput.classList.add('is-verified');
                        phoneOtpCodeInput.setAttribute('readonly', 'readonly');
                        phoneOtpCodeInput.classList.add('is-verified');

                        btnVerifyPhoneOtp.className = 'btn btn-outline-success px-3 small fw-bold disabled';
                        btnVerifyPhoneOtp.innerHTML = '<i class="fas fa-check-circle me-1"></i> Verified';
                        btnVerifyPhoneOtp.disabled = true;

                        if (btnSendPhoneOtp) btnSendPhoneOtp.classList.add('d-none');
                    } else {
                        showAlert(data.message || 'Invalid or expired SMS code.');
                        btnVerifyPhoneOtp.disabled = false;
                        btnVerifyPhoneOtp.innerHTML = '<i class="fas fa-check me-1"></i> Verify Code';
                    }
                })
                .catch(() => {
                    showAlert('Network error during SMS code verification.');
                    btnVerifyPhoneOtp.disabled = false;
                    btnVerifyPhoneOtp.innerHTML = '<i class="fas fa-check me-1"></i> Verify Code';
                });
            });
        }

        // ==========================
        // 2. EMAIL OTP PIPELINE
        // ==========================
        function startEmailTimer(seconds) {
            clearInterval(emailTimerInterval);
            let rem = seconds;

            if (btnSendEmailOtp) {
                btnSendEmailOtp.disabled = true;
                btnSendEmailOtp.innerHTML = '<i class="fas fa-check me-1"></i> Sent';
            }

            emailTimerInterval = setInterval(function() {
                if (rem <= 0) {
                    clearInterval(emailTimerInterval);
                    if (emailTimerText) emailTimerText.textContent = '';
                    if (btnSendEmailOtp && !isEmailVerified) {
                        btnSendEmailOtp.disabled = false;
                        btnSendEmailOtp.innerHTML = '<i class="fas fa-redo me-1"></i> Resend Code';
                    }
                    return;
                }
                const m = Math.floor(rem / 60);
                const s = rem % 60;
                if (emailTimerText) {
                    emailTimerText.textContent = `Resend in ${m < 10 ? '0' : ''}${m}:${s < 10 ? '0' : ''}${s}`;
                }
                rem--;
            }, 1000);
        }

        if (btnSendEmailOtp && emailInput) {
            btnSendEmailOtp.addEventListener('click', function() {
                const email = emailInput.value.trim();
                if (!email) {
                    showAlert('Please enter a valid email address first.');
                    emailInput.focus();
                    return;
                }

                btnSendEmailOtp.disabled = true;
                btnSendEmailOtp.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending...';

                fetch('{{ route("auth.email_otp.register.send") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email: email })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, true);
                        startEmailTimer(data.cooldown_seconds || 60);
                        if (emailOtpCodeInput) emailOtpCodeInput.focus();
                    } else {
                        const loginLink = ' <a href="{{ route("login") }}" class="fw-bold text-danger text-decoration-underline ms-1">Sign In</a>';
                        showAlert(data.message || 'Failed to dispatch email code.', false, (data.message && data.message.includes('already registered')) ? loginLink : '');
                        btnSendEmailOtp.disabled = false;
                        btnSendEmailOtp.innerHTML = 'Send Code';
                    }
                })
                .catch(() => {
                    showAlert('Network error sending email verification code.');
                    btnSendEmailOtp.disabled = false;
                    btnSendEmailOtp.innerHTML = 'Send Code';
                });
            });
        }

        if (btnVerifyEmailOtp && emailOtpCodeInput && emailInput) {
            btnVerifyEmailOtp.addEventListener('click', function() {
                const email = emailInput.value.trim();
                const code  = emailOtpCodeInput.value.trim();

                if (!code || code.length < 4) {
                    showAlert('Please enter the email verification passcode.');
                    emailOtpCodeInput.focus();
                    return;
                }

                btnVerifyEmailOtp.disabled = true;
                btnVerifyEmailOtp.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Verifying...';

                fetch('{{ $verifyEmailOtpRoute }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email: email, otp_code: code })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        isEmailVerified = true;
                        clearInterval(emailTimerInterval);
                        if (emailTimerText) emailTimerText.textContent = '';

                        showAlert(data.message || 'Email address verified successfully!', true);
                        if (emailStatusBadge) emailStatusBadge.classList.remove('d-none');
                        if (emailFeedbackSuccess) emailFeedbackSuccess.classList.remove('d-none');

                        emailInput.setAttribute('readonly', 'readonly');
                        emailInput.classList.add('is-verified');
                        emailOtpCodeInput.setAttribute('readonly', 'readonly');
                        emailOtpCodeInput.classList.add('is-verified');

                        btnVerifyEmailOtp.className = 'btn btn-outline-success px-3 small fw-bold disabled';
                        btnVerifyEmailOtp.innerHTML = '<i class="fas fa-check-circle me-1"></i> Verified';
                        btnVerifyEmailOtp.disabled = true;

                        if (btnSendEmailOtp) btnSendEmailOtp.classList.add('d-none');
                    } else {
                        showAlert(data.message || 'Invalid or expired email passcode.');
                        btnVerifyEmailOtp.disabled = false;
                        btnVerifyEmailOtp.innerHTML = '<i class="fas fa-check me-1"></i> Verify Code';
                    }
                })
                .catch(() => {
                    showAlert('Network error during email passcode verification.');
                    btnVerifyEmailOtp.disabled = false;
                    btnVerifyEmailOtp.innerHTML = '<i class="fas fa-check me-1"></i> Verify Code';
                });
            });
        }

        // ==========================
        // 3. FORM SUBMIT SAFEGUARD
        // ==========================
        if (registerForm) {
            registerForm.addEventListener('submit', function(e) {
                if (isPhoneOtpRequired && phoneInput && phoneInput.value.trim() && !isPhoneVerified) {
                    e.preventDefault();
                    showAlert('Please verify your mobile phone number before completing registration.');
                    if (phoneOtpCodeInput) phoneOtpCodeInput.focus();
                    return false;
                }

                if (isEmailOtpRequired && emailInput && emailInput.value.trim() && !isEmailVerified) {
                    e.preventDefault();
                    showAlert('Please verify your email address before completing registration.');
                    if (emailOtpCodeInput) emailOtpCodeInput.focus();
                    return false;
                }
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initRegisterClient);
    } else {
        initRegisterClient();
    }
})();
</script>
@endsection