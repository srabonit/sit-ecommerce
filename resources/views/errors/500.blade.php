@extends('layouts.app')

@php
    // সেন্ট্রাল গ্লোবাল সেটিংস ক্যাশ রেজলভার
    static $serverErrorSettingsCache = null;
    if ($serverErrorSettingsCache === null) {
        $serverErrorSettingsCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
            return \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $globalSettings = $serverErrorSettingsCache ?? [];

    $siteTitle = config('app.name', 'Ecommerce by Srabon IT');

    // 500 Error Page Dynamic Colors
    $errPageBg          = $globalSettings['color_site_bg'] ?? '#f8fafc';
    $errCardBg          = $globalSettings['color_auth_card_bg'] ?? '#ffffff';
    $errCardBorder      = $globalSettings['color_auth_card_border'] ?? '#edf2f7';
    $errIconColor       = '#ef4444';
    $errTitleColor      = $globalSettings['color_page_title'] ?? '#0f172a';
    $errSubtitleColor   = $globalSettings['color_auth_text'] ?? '#64748b';
    $errTextDesc        = $globalSettings['color_auth_text'] ?? '#64748b';

    $errBtnHomeBg       = $globalSettings['color_common_btn_bg'] ?? ($globalSettings['color_hdr_menu_hover_text'] ?? '#2563eb');
    $errBtnHomeText     = $globalSettings['color_common_btn_text'] ?? '#ffffff';
    $errBtnHomeHov      = $globalSettings['color_auth_btn_hover'] ?? '#1d4ed8';

    $errBtnReloadBorder = $globalSettings['color_cat_card_border'] ?? '#cbd5e1';
    $errBtnReloadText   = $globalSettings['color_page_sidebar_text'] ?? '#334155';
    $errBtnReloadHovBg  = $globalSettings['color_hdr_menu_hover_bg'] ?? '#f8fafc';
@endphp

@section('title', 'Server Error - ' . $siteTitle)

@section('content')
<style>
    :root {
        --err-page-bg: {{ $errPageBg }};
        --err-card-bg: {{ $errCardBg }};
        --err-card-border: {{ $errCardBorder }};
        --err-icon-color: {{ $errIconColor }};
        --err-title-color: {{ $errTitleColor }};
        --err-subtitle-color: {{ $errSubtitleColor }};
        --err-text-desc: {{ $errTextDesc }};

        --err-btn-home-bg: {{ $errBtnHomeBg }};
        --err-btn-home-text: {{ $errBtnHomeText }};
        --err-btn-home-hover: {{ $errBtnHomeHov }};

        --err-btn-reload-border: {{ $errBtnReloadBorder }};
        --err-btn-reload-text: {{ $errBtnReloadText }};
        --err-btn-reload-hover-bg: {{ $errBtnReloadHovBg }};
    }

    .server-error-page-wrapper {
        background-color: var(--err-page-bg) !important;
        min-height: 75vh;
    }

    .server-error-card {
        background-color: var(--err-card-bg) !important;
        border: 1px solid var(--err-card-border) !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04) !important;
    }

    /* Strict text-decoration none for all anchor tags */
    .server-error-card a,
    a.server-error-link-btn {
        text-decoration: none !important;
    }

    .server-error-icon-box {
        color: var(--err-icon-color) !important;
    }

    .server-error-code-title {
        color: var(--err-title-color) !important;
    }

    .server-error-subtitle {
        color: var(--err-subtitle-color) !important;
    }

    .server-error-desc {
        color: var(--err-text-desc) !important;
    }

    .btn-server-error-home {
        background-color: var(--err-btn-home-bg) !important;
        border-color: var(--err-btn-home-bg) !important;
        color: var(--err-btn-home-text) !important;
        transition: all 0.2s ease;
        text-decoration: none !important;
    }
    .btn-server-error-home:hover {
        background-color: var(--err-btn-home-hover) !important;
        border-color: var(--err-btn-home-hover) !important;
        color: var(--err-btn-home-text) !important;
        text-decoration: none !important;
    }

    .btn-server-error-reload {
        border: 1px solid var(--err-btn-reload-border) !important;
        color: var(--err-btn-reload-text) !important;
        background-color: transparent !important;
        transition: all 0.2s ease;
        text-decoration: none !important;
    }
    .btn-server-error-reload:hover {
        background-color: var(--err-btn-reload-hover-bg) !important;
        color: var(--err-title-color) !important;
        border-color: var(--err-title-color) !important;
        text-decoration: none !important;
    }
</style>

<div class="server-error-page-wrapper py-5 my-0 d-flex align-items-center">
    <div class="container py-4">
        <div class="row justify-content-center text-center">
            <div class="col-lg-6 col-md-8">
                <div class="card border-0 rounded-4 p-4 p-md-5 server-error-card">
                    
                    <!-- Icon -->
                    <div class="mb-4 server-error-icon-box">
                        <i class="fas fa-server fa-4x opacity-75"></i>
                    </div>

                    <h1 class="display-4 fw-bold server-error-code-title mb-2">500</h1>
                    <h3 class="fw-bold server-error-subtitle mb-3">Internal Server Error</h3>
                    
                    <p class="server-error-desc mb-4 small">
                        We are experiencing some technical issues on our server right now. Our engineering team has been notified. Please try again shortly.
                    </p>

                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <a href="{{ route('home') }}" class="btn btn-server-error-home server-error-link-btn rounded-pill px-4 py-2 fw-semibold shadow-sm small">
                            <i class="fas fa-home me-1"></i> Return Home
                        </a>
                        <a href="javascript:location.reload();" class="btn btn-server-error-reload server-error-link-btn rounded-pill px-4 py-2 fw-semibold small">
                            <i class="fas fa-redo me-1"></i> Try Again
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection