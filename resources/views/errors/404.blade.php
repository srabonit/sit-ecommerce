@extends('layouts.app')

@php
    // সেন্ট্রাল গ্লোবাল সেটিংস ক্যাশ রেজলভার
    static $notFoundSettingsCache = null;
    if ($notFoundSettingsCache === null) {
        $notFoundSettingsCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
            return \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $globalSettings = $notFoundSettingsCache ?? [];

    $siteTitle = config('app.name', 'Ecommerce by Srabon IT');

    // Dynamic 404 Error Page Colors
    $nfPageBg       = $globalSettings['color_site_bg'] ?? '#f8fafc';
    $nfCardBg       = $globalSettings['color_auth_card_bg'] ?? '#ffffff';
    $nfCardBorder   = $globalSettings['color_auth_card_border'] ?? '#edf2f7';
    $nfIconColor    = $globalSettings['color_common_btn_bg'] ?? ($globalSettings['color_hdr_menu_hover_text'] ?? '#2563eb');
    $nfTitleColor   = $globalSettings['color_page_title'] ?? '#0f172a';
    $nfSubTitle     = $globalSettings['color_auth_text'] ?? '#64748b';
    $nfTextDesc     = $globalSettings['color_auth_text'] ?? '#64748b';

    $nfBtnHomeBg    = $globalSettings['color_common_btn_bg'] ?? '#2563eb';
    $nfBtnHomeText  = $globalSettings['color_common_btn_text'] ?? '#ffffff';
    $nfBtnHomeHov   = $globalSettings['color_auth_btn_hover'] ?? '#1d4ed8';

    $nfBtnShopBorder= $globalSettings['color_cat_card_border'] ?? '#cbd5e1';
    $nfBtnShopText  = $globalSettings['color_page_sidebar_text'] ?? '#475569';
    $nfBtnShopHovBg = $globalSettings['color_hdr_menu_hover_bg'] ?? '#f8fafc';
@endphp

@section('title', 'Page Not Found - ' . $siteTitle)

@section('content')
<style>
    :root {
        --nf-page-bg: {{ $nfPageBg }};
        --nf-card-bg: {{ $nfCardBg }};
        --nf-card-border: {{ $nfCardBorder }};
        --nf-icon-color: {{ $nfIconColor }};
        --nf-title-color: {{ $nfTitleColor }};
        --nf-subtitle-color: {{ $nfSubTitle }};
        --nf-text-desc: {{ $nfTextDesc }};

        --nf-btn-home-bg: {{ $nfBtnHomeBg }};
        --nf-btn-home-text: {{ $nfBtnHomeText }};
        --nf-btn-home-hover: {{ $nfBtnHomeHov }};

        --nf-btn-shop-border: {{ $nfBtnShopBorder }};
        --nf-btn-shop-text: {{ $nfBtnShopText }};
        --nf-btn-shop-hover-bg: {{ $nfBtnShopHovBg }};
    }

    .not-found-page-wrapper {
        background-color: var(--nf-page-bg) !important;
        min-height: 75vh;
    }

    .not-found-card {
        background-color: var(--nf-card-bg) !important;
        border: 1px solid var(--nf-card-border) !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04) !important;
    }

    /* Strict text-decoration none for all anchor tags */
    .not-found-card a,
    a.not-found-link-btn {
        text-decoration: none !important;
    }

    .not-found-icon-box {
        color: var(--nf-icon-color) !important;
    }

    .not-found-code-title {
        color: var(--nf-title-color) !important;
    }

    .not-found-subtitle {
        color: var(--nf-subtitle-color) !important;
    }

    .not-found-desc {
        color: var(--nf-text-desc) !important;
    }

    .btn-not-found-home {
        background-color: var(--nf-btn-home-bg) !important;
        border-color: var(--nf-btn-home-bg) !important;
        color: var(--nf-btn-home-text) !important;
        transition: all 0.2s ease;
        text-decoration: none !important;
    }
    .btn-not-found-home:hover {
        background-color: var(--nf-btn-home-hover) !important;
        border-color: var(--nf-btn-home-hover) !important;
        color: var(--nf-btn-home-text) !important;
        text-decoration: none !important;
    }

    .btn-not-found-shop {
        border: 1px solid var(--nf-btn-shop-border) !important;
        color: var(--nf-btn-shop-text) !important;
        background-color: transparent !important;
        transition: all 0.2s ease;
        text-decoration: none !important;
    }
    .btn-not-found-shop:hover {
        background-color: var(--nf-btn-shop-hover-bg) !important;
        color: var(--nf-icon-color) !important;
        border-color: var(--nf-icon-color) !important;
        text-decoration: none !important;
    }
</style>

<div class="not-found-page-wrapper py-5 my-0 d-flex align-items-center">
    <div class="container py-4">
        <div class="row justify-content-center text-center">
            <div class="col-lg-6 col-md-8">
                <div class="card border-0 rounded-4 p-4 p-md-5 not-found-card">
                    
                    <!-- Icon -->
                    <div class="mb-4 not-found-icon-box">
                        <i class="fas fa-exclamation-triangle fa-4x opacity-75"></i>
                    </div>

                    <h1 class="display-4 fw-bold not-found-code-title mb-2">404</h1>
                    <h3 class="fw-bold not-found-subtitle mb-3">Oops! Page Not Found</h3>
                    
                    <p class="not-found-desc mb-4 small">
                        The page you are looking for might have been removed, had its name changed, or is temporarily unavailable. Let's get you back on track!
                    </p>

                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <a href="{{ route('home') }}" class="btn btn-not-found-home not-found-link-btn rounded-pill px-4 py-2 fw-semibold shadow-sm small">
                            <i class="fas fa-home me-1"></i> Back to Home
                        </a>
                        <a href="{{ route('shop') }}" class="btn btn-not-found-shop not-found-link-btn rounded-pill px-4 py-2 fw-semibold small">
                            <i class="fas fa-shopping-bag me-1"></i> Browse Shop
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection