@extends('layouts.app')

@php
    // সেন্ট্রাল গ্লোবাল সেটিংস ক্যাশ রেজলভার
    static $resetPassSettingsCache = null;
    if ($resetPassSettingsCache === null) {
        $resetPassSettingsCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
            return \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $authSettings = $resetPassSettingsCache ?? [];

    $siteTitle = config('app.name', 'Shop Now BD');

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
@endphp

@section('title', 'Set New Password - ' . $siteTitle)

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
    .auth-card-box a {
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

    .auth-form-input[readonly] {
        background-color: #f1f5f9 !important;
        opacity: 0.85;
        cursor: not-allowed;
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
</style>

<div class="py-5 auth-page-wrapper d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card border-0 rounded-4 p-4 auth-card-box">
                    <div class="text-center mb-4">
                        <h4 class="fw-bold mb-1 auth-heading-text">Create New Password</h4>
                        <p class="small mb-0 auth-subtext-color">Choose a secure password for your account</p>
                    </div>

                    <form action="{{ route('password.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="mb-3">
                            <label class="form-label small fw-bold auth-heading-text">Email Address</label>
                            <input type="email" name="email" class="form-control rounded-3 py-2 auth-form-input @error('email') is-invalid @enderror" value="{{ $email ?? old('email') }}" required readonly>
                            @error('email')
                                <div class="invalid-feedback small fw-semibold">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold auth-heading-text">New Password</label>
                            <input type="password" name="password" class="form-control rounded-3 py-2 auth-form-input @error('password') is-invalid @enderror" placeholder="Min 6 characters" required autofocus>
                            @error('password')
                                <div class="invalid-feedback small fw-semibold">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold auth-heading-text">Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="form-control rounded-3 py-2 auth-form-input" placeholder="Repeat password" required>
                        </div>

                        <button type="submit" class="btn auth-submit-btn w-100 py-2 rounded-3 fw-bold shadow-sm">
                            Reset Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection