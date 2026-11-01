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

    /* All links text-decoration none */
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
</style>

<div class="py-5 auth-page-wrapper d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card border-0 rounded-4 p-4 auth-card-box">
                    
                    <div class="text-center mb-4">
                        @if(!empty($logoUrl))
                            <a href="{{ route('home') }}" class="d-inline-block mb-3 text-decoration-none">
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

                    @if(session('status'))
                        <div class="alert alert-success py-2 small rounded-3 mb-3">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success py-2 small rounded-3 mb-3">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger py-2 small rounded-3 mb-3">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('login') }}" method="POST" novalidate>
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold auth-heading-text">Email or Phone Number</label>
                            <input type="text" name="login" class="form-control rounded-3 py-2 auth-form-input @error('login') is-invalid @enderror" value="{{ old('login') }}" placeholder="Enter Email or Phone Number" required autofocus>
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

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
                            <label class="form-check-label small auth-subtext-color" for="rememberMe">
                                Keep me signed in
                            </label>
                        </div>

                        <button type="submit" class="btn auth-submit-btn w-100 py-2 rounded-3 fw-bold shadow-sm">
                            Sign In
                        </button>
                    </form>

                    <div class="text-center mt-4 pt-2 border-top" style="border-color: var(--auth-card-border) !important;">
                        <span class="small auth-subtext-color">Don't have an account?</span>
                        <a href="{{ route('register') }}" class="small auth-theme-link fw-bold text-decoration-none ms-1">Register Now</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection