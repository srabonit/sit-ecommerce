@extends('layouts.app')

@php
    // সেন্ট্রাল গ্লোবাল সেটিংস ক্যাশ রেজলভার
    static $genericErrorSettingsCache = null;
    if ($genericErrorSettingsCache === null) {
        $genericErrorSettingsCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
            return \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $globalSettings = $genericErrorSettingsCache ?? [];

    $siteTitle = config('app.name', 'Ecommerce by Srabon IT');
    $statusCode = (isset($exception) && method_exists($exception, 'getStatusCode')) ? ($exception->getStatusCode() ?? 'Error') : 'Error';
    $errorMessage = (isset($exception) && method_exists($exception, 'getMessage') && !empty($exception->getMessage())) ? $exception->getMessage() : 'An unexpected error occurred.';

    // Generic Error Dynamic Colors
    $errPageBg        = $globalSettings['color_site_bg'] ?? '#f8fafc';
    $errCardBg        = $globalSettings['color_auth_card_bg'] ?? '#ffffff';
    $errCardBorder    = $globalSettings['color_auth_card_border'] ?? '#edf2f7';
    $errIconColor     = '#f59e0b';
    $errTitleColor    = $globalSettings['color_page_title'] ?? '#0f172a';
    $errSubtitleColor = $globalSettings['color_auth_text'] ?? '#64748b';
    $errTextDesc      = $globalSettings['color_auth_text'] ?? '#64748b';

    $errBtnHomeBg     = $globalSettings['color_common_btn_bg'] ?? ($globalSettings['color_hdr_menu_hover_text'] ?? '#2563eb');
    $errBtnHomeText   = $globalSettings['color_common_btn_text'] ?? '#ffffff';
    $errBtnHomeHov    = $globalSettings['color_auth_btn_hover'] ?? '#1d4ed8';
@endphp

@section('title', $statusCode . ' - ' . $siteTitle)

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
    }

    .generic-error-page-wrapper {
        background-color: var(--err-page-bg) !important;
        min-height: 75vh;
    }

    .generic-error-card {
        background-color: var(--err-card-bg) !important;
        border: 1px solid var(--err-card-border) !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04) !important;
    }

    /* Strict text-decoration none for all anchor tags */
    .generic-error-card a,
    a.generic-error-link-btn {
        text-decoration: none !important;
    }

    .generic-error-icon-box {
        color: var(--err-icon-color) !important;
    }

    .generic-error-code-title {
        color: var(--err-title-color) !important;
    }

    .generic-error-subtitle {
        color: var(--err-subtitle-color) !important;
    }

    .generic-error-desc {
        color: var(--err-text-desc) !important;
    }

    .btn-generic-error-home {
        background-color: var(--err-btn-home-bg) !important;
        border-color: var(--err-btn-home-bg) !important;
        color: var(--err-btn-home-text) !important;
        transition: all 0.2s ease;
        text-decoration: none !important;
    }
    .btn-generic-error-home:hover {
        background-color: var(--err-btn-home-hover) !important;
        border-color: var(--err-btn-home-hover) !important;
        color: var(--err-btn-home-text) !important;
        text-decoration: none !important;
    }
</style>

<div class="generic-error-page-wrapper py-5 my-0 d-flex align-items-center">
    <div class="container py-4">
        <div class="row justify-content-center text-center">
            <div class="col-lg-6 col-md-8">
                <div class="card border-0 rounded-4 p-4 p-md-5 generic-error-card">
                    
                    <div class="mb-4 generic-error-icon-box">
                        <i class="fas fa-ban fa-4x opacity-75"></i>
                    </div>

                    <h1 class="display-4 fw-bold generic-error-code-title mb-2">{{ $statusCode }}</h1>
                    <h3 class="fw-bold generic-error-subtitle mb-3">{{ $errorMessage }}</h3>
                    
                    <p class="generic-error-desc mb-4 small">
                        Something went wrong while processing your request. Please go back to the homepage.
                    </p>

                    <div>
                        <a href="{{ route('home') }}" class="btn btn-generic-error-home generic-error-link-btn rounded-pill px-4 py-2 fw-semibold shadow-sm small">
                            <i class="fas fa-home me-1"></i> Back to Home
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection