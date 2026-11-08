@extends('layouts.app')

@php
    // সেন্ট্রাল গ্লোবাল সেটিংস ক্যাশ রেজলভার
    static $authSettingsCache = null;
    if ($authSettingsCache === null) {
        $authSettingsCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
            return \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $authSettings = $authSettingsCache ?? [];

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

    // Dynamic Multi-Method Authentication Resolver
    if (!isset($settings)) {
        $authSettingService = app(\App\Services\Auth\AuthenticationSettingsService::class);
        $settings = $authSettingService->all();
        $enabledMethods = $authSettingService->getEnabledLoginMethods();
        $defaultMethod = $authSettingService->getDefaultLoginMethod();
    }

    $isEmailPasswordActive = !empty($settings['login_email_password']);
    $isPhonePasswordActive = !empty($settings['login_phone_password']);
    $isPasswordTabActive   = $isEmailPasswordActive || $isPhonePasswordActive;
    $isPhoneOtpActive      = !empty($settings['login_phone_otp']);
    $isEmailOtpActive      = !empty($settings['login_email_otp']);
    $isGoogleActive        = !empty($settings['login_google']);

    // Determine initial active tab
    $initialTab = 'password';
    if ($defaultMethod === 'phone_otp' && $isPhoneOtpActive) {
        $initialTab = 'phone_otp';
    } elseif ($defaultMethod === 'email_otp' && $isEmailOtpActive) {
        $initialTab = 'email_otp';
    } elseif (!$isPasswordTabActive) {
        if ($isPhoneOtpActive) $initialTab = 'phone_otp';
        elseif ($isEmailOtpActive) $initialTab = 'email_otp';
    }

    // Determine password field label & placeholder
    $loginLabel = 'Email or Phone Number';
    $loginPlaceholder = 'Enter Email or Phone Number';
    if ($isEmailPasswordActive && !$isPhonePasswordActive) {
        $loginLabel = 'Email Address';
        $loginPlaceholder = 'Enter your email address';
    } elseif (!$isEmailPasswordActive && $isPhonePasswordActive) {
        $loginLabel = 'Mobile Phone Number';
        $loginPlaceholder = '017XXXXXXXX';
    }

    // Count distinct tabs
    $tabCount = ($isPasswordTabActive ? 1 : 0) + ($isPhoneOtpActive ? 1 : 0) + ($isEmailOtpActive ? 1 : 0);
@endphp

@section('title', 'Sign In - ' . $siteTitle)

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
        min-height: 75vh;
    }

    .auth-card-box {
        background-color: var(--auth-card-bg) !important;
        border: 1px solid var(--auth-card-border) !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04) !important;
    }

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

    /* Auth Navigation Tabs */
    .auth-nav-tabs {
        background: #f1f5f9;
        padding: 4px;
        border-radius: 8px;
        display: flex;
        gap: 4px;
        border: none;
    }

    .auth-nav-tabs .nav-link {
        flex: 1;
        border: none;
        border-radius: 6px;
        padding: 6px 10px;
        font-size: 12.5px;
        font-weight: 600;
        color: #64748b;
        background: transparent;
        transition: all 0.2s ease;
        text-align: center;
    }

    .auth-nav-tabs .nav-link.active {
        background: #ffffff;
        color: var(--auth-heading) !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    /* Google Button Styling */
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
            <div class="col-md-6 col-lg-5 col-xl-4">
                <div class="card border-0 rounded-4 p-4 auth-card-box">
                    
                    {{-- Logo & Header --}}
                    <div class="text-center mb-3">
                        @if(!empty($logoUrl))
                            <a href="{{ route('home') }}" class="d-inline-block mb-2 text-decoration-none">
                                <img src="{{ $logoUrl }}" alt="{{ $siteTitle }}" style="max-height: 48px; max-width: 160px; object-fit: contain;">
                            </a>
                        @else
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2 auth-icon-circle shadow-xs" style="width: 48px; height: 48px;">
                                <i class="fas fa-lock fs-5"></i>
                            </div>
                        @endif
                        <h4 class="fw-bold mb-1 auth-heading-text">Welcome Back</h4>
                        <p class="small mb-0 auth-subtext-color">Sign in to manage your orders &amp; account</p>
                    </div>

                    {{-- Session Flash Alerts --}}
                    @if(session('status'))
                        <div class="alert alert-success py-2 small rounded-3 mb-3 border-0 shadow-xs">
                            <i class="fas fa-check-circle me-1"></i> {{ session('status') }}
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success py-2 small rounded-3 mb-3 border-0 shadow-xs">
                            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger py-2 small rounded-3 mb-3 border-0 shadow-xs">
                            <i class="fas fa-exclamation-triangle me-1"></i> {{ session('error') }}
                        </div>
                    @endif

                    @if(session('info'))
                        <div class="alert alert-info py-2 small rounded-3 mb-3 border-0 shadow-xs">
                            <i class="fas fa-info-circle me-1"></i> {{ session('info') }}
                        </div>
                    @endif

                    {{-- Dynamic Tabs Selector (Shows only if multiple distinct options are active) --}}
                    @if($tabCount > 1)
                        <ul class="nav auth-nav-tabs mb-3" role="tablist">
                            @if($isPasswordTabActive)
                                <li class="nav-item flex-fill">
                                    <button class="nav-link w-100 {{ $initialTab === 'password' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#tabPassword" type="button" role="tab">
                                        <i class="fas fa-key me-1"></i> Password
                                    </button>
                                </li>
                            @endif

                            @if($isPhoneOtpActive)
                                <li class="nav-item flex-fill">
                                    <button class="nav-link w-100 {{ $initialTab === 'phone_otp' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#tabPhoneOtp" type="button" role="tab">
                                        <i class="fas fa-sms me-1"></i> Phone OTP
                                    </button>
                                </li>
                            @endif

                            @if($isEmailOtpActive)
                                <li class="nav-item flex-fill">
                                    <button class="nav-link w-100 {{ $initialTab === 'email_otp' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#tabEmailOtp" type="button" role="tab">
                                        <i class="fas fa-envelope me-1"></i> Email OTP
                                    </button>
                                </li>
                            @endif
                        </ul>
                    @endif

                    <div class="tab-content">

                        {{-- TAB 1: Password Credentials Login --}}
                        @if($isPasswordTabActive)
                            <div class="tab-pane fade {{ $initialTab === 'password' ? 'show active' : '' }}" id="tabPassword" role="tabpanel">
                                <form action="{{ route('login') }}" method="POST" novalidate>
                                    @csrf
                                    
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold auth-heading-text">{{ $loginLabel }}</label>
                                        <input type="text" name="login" class="form-control rounded-3 py-2 auth-form-input @error('login') is-invalid @enderror" value="{{ old('login') }}" placeholder="{{ $loginPlaceholder }}" required autofocus>
                                        @error('login')
                                            <div class="invalid-feedback small fw-semibold">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label small fw-bold auth-heading-text mb-0">Password</label>
                                            @if (Route::has('password.request'))
                                                <a href="{{ route('password.request') }}" class="small auth-theme-link text-decoration-none">Forgot Password?</a>
                                            @endif
                                        </div>
                                        <input type="password" name="password" class="form-control rounded-3 py-2 auth-form-input @error('password') is-invalid @enderror" placeholder="••••••••" required>
                                        @error('password')
                                            <div class="invalid-feedback small fw-semibold">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
                                        <label class="form-check-label small auth-subtext-color" for="rememberMe">
                                            Keep me signed in
                                        </label>
                                    </div>

                                    <button type="submit" class="btn auth-submit-btn w-100 py-2 rounded-3 fw-bold shadow-sm">
                                        Sign In with Password
                                    </button>
                                </form>
                            </div>
                        @endif

                        {{-- TAB 2: Phone Number + SMS OTP Login (Passwordless) --}}
                        @if($isPhoneOtpActive)
                            <div class="tab-pane fade {{ $initialTab === 'phone_otp' ? 'show active' : '' }}" id="tabPhoneOtp" role="tabpanel">
                                <div id="phoneOtpAlertBox" class="d-none alert py-2 small rounded-3 mb-3 border-0"></div>

                                {{-- Step A: Phone Input & Send OTP --}}
                                <div id="phoneOtpStep1">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold auth-heading-text">Mobile Phone Number</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light text-muted small border-end-0" style="border-radius: 8px 0 0 8px;">+880</span>
                                            <input type="tel" id="otpPhoneInput" class="form-control rounded-end-3 py-2 auth-form-input font-monospace" placeholder="17XXXXXXXX" maxlength="11">
                                        </div>
                                        <span class="form-text small text-muted" style="font-size: 11px;">We will send a 6-digit verification code to this number.</span>
                                    </div>

                                    <button type="button" id="btnSendPhoneOtp" class="btn auth-submit-btn w-100 py-2 rounded-3 fw-bold shadow-sm">
                                        <i class="fas fa-paper-plane me-1"></i> Send Verification Code
                                    </button>
                                </div>

                                {{-- Step B: OTP Input & Verify --}}
                                <div id="phoneOtpStep2" class="d-none">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="small text-muted">Sent to: <strong class="text-dark font-monospace" id="displayOtpPhone"></strong></span>
                                        <button type="button" id="btnChangePhone" class="btn btn-link p-0 small text-decoration-none auth-theme-link" style="font-size: 11.5px;">Change</button>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-bold auth-heading-text">6-Digit Code</label>
                                        <input type="text" id="otpCodeInput" class="form-control text-center py-2 auth-form-input font-monospace fw-bold" placeholder="000000" maxlength="6" style="letter-spacing: 6px; font-size: 20px;">
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-3 small">
                                        <span class="text-muted" id="otpTimerText">Expires in: <strong id="otpCountdown">05:00</strong></span>
                                        <button type="button" id="btnResendPhoneOtp" class="btn btn-link p-0 small text-decoration-none auth-theme-link disabled" disabled>Resend Code</button>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="rememberMeOtp">
                                        <label class="form-check-label small auth-subtext-color" for="rememberMeOtp">
                                            Keep me signed in
                                        </label>
                                    </div>

                                    <button type="button" id="btnVerifyPhoneOtp" class="btn auth-submit-btn w-100 py-2 rounded-3 fw-bold shadow-sm">
                                        Verify &amp; Sign In
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{-- TAB 3: Email + OTP Login (Passwordless) --}}
                        @if($isEmailOtpActive)
                            <div class="tab-pane fade {{ $initialTab === 'email_otp' ? 'show active' : '' }}" id="tabEmailOtp" role="tabpanel">
                                <div id="emailOtpAlertBox" class="d-none alert py-2 small rounded-3 mb-3 border-0"></div>

                                {{-- Step A: Email Input --}}
                                <div id="emailOtpStep1">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold auth-heading-text">Your Email Address</label>
                                        <input type="email" id="emailOtpInput" class="form-control rounded-3 py-2 auth-form-input" placeholder="name@example.com">
                                        <span class="form-text small text-muted" style="font-size: 11px;">We will send a temporary login passcode to your inbox.</span>
                                    </div>

                                    <button type="button" id="btnSendEmailOtp" class="btn auth-submit-btn w-100 py-2 rounded-3 fw-bold shadow-sm">
                                        <i class="fas fa-paper-plane me-1"></i> Send Passcode
                                    </button>
                                </div>

                                {{-- Step B: Passcode Input --}}
                                <div id="emailOtpStep2" class="d-none">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="small text-muted">Sent to: <strong class="text-dark" id="displayOtpEmail"></strong></span>
                                        <button type="button" id="btnChangeEmail" class="btn btn-link p-0 small text-decoration-none auth-theme-link" style="font-size: 11.5px;">Change</button>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-bold auth-heading-text">Passcode</label>
                                        <input type="text" id="emailOtpCodeInput" class="form-control text-center py-2 auth-form-input font-monospace fw-bold" placeholder="000000" maxlength="6" style="letter-spacing: 6px; font-size: 20px;">
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-3 small">
                                        <span class="text-muted" id="emailOtpTimerText">Expires in: <strong id="emailOtpCountdown">05:00</strong></span>
                                        <button type="button" id="btnResendEmailOtp" class="btn btn-link p-0 small text-decoration-none auth-theme-link disabled" disabled>Resend Code</button>
                                    </div>

                                    <button type="button" id="btnVerifyEmailOtp" class="btn auth-submit-btn w-100 py-2 rounded-3 fw-bold shadow-sm">
                                        Verify &amp; Sign In
                                    </button>
                                </div>
                            </div>
                        @endif

                    </div>

                    {{-- Google OAuth Section --}}
                    @if($isGoogleActive)
                        <div class="auth-divider">
                            <span>or sign in with</span>
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
                            Continue with Google
                        </a>
                    @endif

                    {{-- Footer: Registration Link --}}
                    <div class="text-center mt-4 pt-2 border-top" style="border-color: var(--auth-card-border) !important;">
                        <span class="small auth-subtext-color">Don't have an account?</span>
                        <a href="{{ route('register') }}" class="small auth-theme-link fw-bold text-decoration-none ms-1">Register Now</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- Interactive Phone & Email OTP Engine --}}
<script>
(function() {
    function initDynamicAuthClient() {
        const csrfToken = '{{ csrf_token() }}';

        // --- 1. Phone OTP Engine ---
        const btnSendPhoneOtp   = document.getElementById('btnSendPhoneOtp');
        const btnVerifyPhoneOtp = document.getElementById('btnVerifyPhoneOtp');
        const btnResendPhoneOtp = document.getElementById('btnResendPhoneOtp');
        const btnChangePhone    = document.getElementById('btnChangePhone');
        const otpPhoneInput     = document.getElementById('otpPhoneInput');
        const otpCodeInput      = document.getElementById('otpCodeInput');
        const phoneOtpStep1     = document.getElementById('phoneOtpStep1');
        const phoneOtpStep2     = document.getElementById('phoneOtpStep2');
        const displayOtpPhone   = document.getElementById('displayOtpPhone');
        const phoneOtpAlertBox  = document.getElementById('phoneOtpAlertBox');
        const otpCountdown      = document.getElementById('otpCountdown');

        let phoneTimerInterval = null;

        function showPhoneAlert(msg, isSuccess = false) {
            if (!phoneOtpAlertBox) return;
            phoneOtpAlertBox.textContent = msg;
            phoneOtpAlertBox.className = 'alert py-2 small rounded-3 mb-3 border-0 ' + (isSuccess ? 'alert-success' : 'alert-danger');
            phoneOtpAlertBox.classList.remove('d-none');
        }

        function startPhoneCountdown(seconds) {
            clearInterval(phoneTimerInterval);
            let rem = seconds;

            if (btnResendPhoneOtp) {
                btnResendPhoneOtp.disabled = true;
                btnResendPhoneOtp.classList.add('disabled');
            }

            phoneTimerInterval = setInterval(function() {
                if (rem <= 0) {
                    clearInterval(phoneTimerInterval);
                    if (otpCountdown) otpCountdown.textContent = '00:00';
                    if (btnResendPhoneOtp) {
                        btnResendPhoneOtp.disabled = false;
                        btnResendPhoneOtp.classList.remove('disabled');
                    }
                    return;
                }
                const m = Math.floor(rem / 60);
                const s = rem % 60;
                if (otpCountdown) {
                    otpCountdown.textContent = `${m < 10 ? '0' : ''}${m}:${s < 10 ? '0' : ''}${s}`;
                }
                rem--;
            }, 1000);
        }

        if (btnSendPhoneOtp) {
            btnSendPhoneOtp.addEventListener('click', function() {
                const phone = otpPhoneInput.value.trim();
                if (!phone) {
                    showPhoneAlert('Please enter your mobile phone number.');
                    otpPhoneInput.focus();
                    return;
                }

                btnSendPhoneOtp.disabled = true;
                btnSendPhoneOtp.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending...';

                fetch('{{ route("auth.otp.login.send") }}', {
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
                        displayOtpPhone.textContent = phone;
                        phoneOtpStep1.classList.add('d-none');
                        phoneOtpStep2.classList.remove('d-none');
                        showPhoneAlert(data.message, true);
                        startPhoneCountdown(data.expires_in_seconds || 300);
                        if (otpCodeInput) otpCodeInput.focus();
                    } else {
                        showPhoneAlert(data.message || 'Failed to send OTP.');
                    }
                })
                .catch(() => showPhoneAlert('Network error occurred. Please try again.'))
                .finally(() => {
                    btnSendPhoneOtp.disabled = false;
                    btnSendPhoneOtp.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Send Verification Code';
                });
            });
        }

        if (btnVerifyPhoneOtp) {
            btnVerifyPhoneOtp.addEventListener('click', function() {
                const phone = otpPhoneInput.value.trim();
                const code = otpCodeInput.value.trim();
                const remember = document.getElementById('rememberMeOtp')?.checked ? 1 : 0;

                if (!code || code.length < 4) {
                    showPhoneAlert('Please enter the 6-digit verification code.');
                    otpCodeInput.focus();
                    return;
                }

                btnVerifyPhoneOtp.disabled = true;
                btnVerifyPhoneOtp.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Verifying...';

                fetch('{{ route("auth.otp.login.verify") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ phone: phone, otp_code: code, remember: remember })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showPhoneAlert('Verification successful! Redirecting...', true);
                        window.location.href = data.redirect_url || '{{ route("user.dashboard") }}';
                    } else {
                        showPhoneAlert(data.message || 'Verification failed.');
                        btnVerifyPhoneOtp.disabled = false;
                        btnVerifyPhoneOtp.textContent = 'Verify & Sign In';
                    }
                })
                .catch(() => {
                    showPhoneAlert('Network error during verification.');
                    btnVerifyPhoneOtp.disabled = false;
                    btnVerifyPhoneOtp.textContent = 'Verify & Sign In';
                });
            });
        }

        if (btnResendPhoneOtp) {
            btnResendPhoneOtp.addEventListener('click', function() {
                btnSendPhoneOtp.click();
            });
        }

        if (btnChangePhone) {
            btnChangePhone.addEventListener('click', function() {
                clearInterval(phoneTimerInterval);
                phoneOtpStep2.classList.add('d-none');
                phoneOtpStep1.classList.remove('d-none');
                if (otpCodeInput) otpCodeInput.value = '';
                if (phoneOtpAlertBox) phoneOtpAlertBox.classList.add('d-none');
            });
        }

        // --- 2. Email OTP Engine ---
        const btnSendEmailOtp   = document.getElementById('btnSendEmailOtp');
        const btnVerifyEmailOtp = document.getElementById('btnVerifyEmailOtp');
        const btnResendEmailOtp = document.getElementById('btnResendEmailOtp');
        const btnChangeEmail    = document.getElementById('btnChangeEmail');
        const emailOtpInput     = document.getElementById('emailOtpInput');
        const emailOtpCodeInput = document.getElementById('emailOtpCodeInput');
        const emailOtpStep1     = document.getElementById('emailOtpStep1');
        const emailOtpStep2     = document.getElementById('emailOtpStep2');
        const displayOtpEmail   = document.getElementById('displayOtpEmail');
        const emailOtpAlertBox  = document.getElementById('emailOtpAlertBox');
        const emailOtpCountdown = document.getElementById('emailOtpCountdown');

        let emailTimerInterval = null;

        function showEmailAlert(msg, isSuccess = false) {
            if (!emailOtpAlertBox) return;
            emailOtpAlertBox.textContent = msg;
            emailOtpAlertBox.className = 'alert py-2 small rounded-3 mb-3 border-0 ' + (isSuccess ? 'alert-success' : 'alert-danger');
            emailOtpAlertBox.classList.remove('d-none');
        }

        function startEmailCountdown(seconds) {
            clearInterval(emailTimerInterval);
            let rem = seconds;

            if (btnResendEmailOtp) {
                btnResendEmailOtp.disabled = true;
                btnResendEmailOtp.classList.add('disabled');
            }

            emailTimerInterval = setInterval(function() {
                if (rem <= 0) {
                    clearInterval(emailTimerInterval);
                    if (emailOtpCountdown) emailOtpCountdown.textContent = '00:00';
                    if (btnResendEmailOtp) {
                        btnResendEmailOtp.disabled = false;
                        btnResendEmailOtp.classList.remove('disabled');
                    }
                    return;
                }
                const m = Math.floor(rem / 60);
                const s = rem % 60;
                if (emailOtpCountdown) {
                    emailOtpCountdown.textContent = `${m < 10 ? '0' : ''}${m}:${s < 10 ? '0' : ''}${s}`;
                }
                rem--;
            }, 1000);
        }

        if (btnSendEmailOtp) {
            btnSendEmailOtp.addEventListener('click', function() {
                const email = emailOtpInput.value.trim();
                if (!email) {
                    showEmailAlert('Please enter your email address.');
                    emailOtpInput.focus();
                    return;
                }

                btnSendEmailOtp.disabled = true;
                btnSendEmailOtp.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending...';

                fetch('{{ route("auth.email_otp.login.send") }}', {
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
                        displayOtpEmail.textContent = email;
                        emailOtpStep1.classList.add('d-none');
                        emailOtpStep2.classList.remove('d-none');
                        showEmailAlert(data.message, true);
                        startEmailCountdown(data.expires_in_seconds || 300);
                        if (emailOtpCodeInput) emailOtpCodeInput.focus();
                    } else {
                        showEmailAlert(data.message || 'Failed to send passcode.');
                    }
                })
                .catch(() => showEmailAlert('Network error occurred. Please try again.'))
                .finally(() => {
                    btnSendEmailOtp.disabled = false;
                    btnSendEmailOtp.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Send Passcode';
                });
            });
        }

        if (btnVerifyEmailOtp) {
            btnVerifyEmailOtp.addEventListener('click', function() {
                const email = emailOtpInput.value.trim();
                const code = emailOtpCodeInput.value.trim();

                if (!code || code.length < 4) {
                    showEmailAlert('Please enter the 6-digit passcode.');
                    emailOtpCodeInput.focus();
                    return;
                }

                btnVerifyEmailOtp.disabled = true;
                btnVerifyEmailOtp.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Verifying...';

                fetch('{{ route("auth.email_otp.login.verify") }}', {
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
                        showEmailAlert('Passcode verified! Redirecting...', true);
                        window.location.href = data.redirect_url || '{{ route("user.dashboard") }}';
                    } else {
                        showEmailAlert(data.message || 'Verification failed.');
                        btnVerifyEmailOtp.disabled = false;
                        btnVerifyEmailOtp.textContent = 'Verify & Sign In';
                    }
                })
                .catch(() => {
                    showEmailAlert('Network error during verification.');
                    btnVerifyEmailOtp.disabled = false;
                    btnVerifyEmailOtp.textContent = 'Verify & Sign In';
                });
            });
        }

        if (btnResendEmailOtp) {
            btnResendEmailOtp.addEventListener('click', function() {
                btnSendEmailOtp.click();
            });
        }

        if (btnChangeEmail) {
            btnChangeEmail.addEventListener('click', function() {
                clearInterval(emailTimerInterval);
                emailOtpStep2.classList.add('d-none');
                emailOtpStep1.classList.remove('d-none');
                if (emailOtpCodeInput) emailOtpCodeInput.value = '';
                if (emailOtpAlertBox) emailOtpAlertBox.classList.add('d-none');
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDynamicAuthClient);
    } else {
        initDynamicAuthClient();
    }
})();
</script>
@endsection