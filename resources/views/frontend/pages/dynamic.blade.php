@extends('layouts.app')

@php

    /*
    |--------------------------------------------------------------------------
    | 1. DYNAMIC IMAGE RESOLVER (Local & Live Universal Support)
    |--------------------------------------------------------------------------
    */
    $resolveDynamicImg = function($path, $fallback = '') {
        if (empty($path)) return $fallback;
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        $clean = ltrim(str_replace(['storage/', 'public/'], '', $path), '/');
        if (file_exists(public_path($clean))) return asset($clean);
        if (file_exists(public_path('uploads/' . $clean))) return asset('uploads/' . $clean);
        return asset($clean);
    };

    /*
    |--------------------------------------------------------------------------
    | 2. DYNAMIC SEO & CANONICAL OVERRIDES
    |--------------------------------------------------------------------------
    */

    $siteBrandName = config('app.name', 'SHOP NOW BD');

    $seoTitle = !empty($page->seo_title)
        ? $page->seo_title
        : ($page->title . ' - ' .$siteBrandName);

    $seoDesc = !empty($page->seo_description)
        ? $page->seo_description
        : \Illuminate\Support\Str::limit(
            strip_tags($page->content ?? ''),
            160
        );

    $seoKeywords = !empty($page->seo_keywords)
        ? $page->seo_keywords
        : '';

    $canonicalUrl = !empty($page->seo_canonical_url)
        ? $page->seo_canonical_url
        : url('/' . $page->slug);

    $ogImage = !empty($page->featured_image)
        ? $resolveDynamicImg($page->featured_image)
        : null;

    $pageFeaturedImg = !empty($page->featured_image)
        ? $resolveDynamicImg($page->featured_image)
        : null;

    /*
    |--------------------------------------------------------------------------
    | 3. DYNAMIC COLOR SETTINGS RESOLVER
    |--------------------------------------------------------------------------
    */
    static $pageThemeSettingsCache = null;
    if ($pageThemeSettingsCache === null) {
        $pageThemeSettingsCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
            return DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $pageColors = $pageThemeSettingsCache ?? [];

    $pageBg           = $pageColors['color_page_bg'] ?? ($pageColors['color_site_bg'] ?? '#f8fafc');
    $pageText         = $pageColors['color_page_text'] ?? '#1e293b';
    $pageTitleColor   = $pageColors['color_page_title'] ?? '#0f172a';
    $pageCardBg       = $pageColors['color_page_card_bg'] ?? '#ffffff';
    $pageCardBorder   = $pageColors['color_page_card_border'] ?? '#e2e8f0';
    $pageSidebarBg    = $pageColors['color_page_sidebar_bg'] ?? '#ffffff';
    $pageSidebarBorder= $pageColors['color_page_sidebar_border'] ?? '#e2e8f0';
    $pageSidebarHead  = $pageColors['color_page_sidebar_header_text'] ?? '#0f172a';
    $pageSidebarText  = $pageColors['color_page_sidebar_text'] ?? '#475569';
    $pageBreadcrumb   = $pageColors['color_page_breadcrumb_link'] ?? '#64748b';
    $pageBreadcrumbHov= $pageColors['color_page_breadcrumb_hover'] ?? '#2563eb';

    /*
    |--------------------------------------------------------------------------
    | 4. PAGE WIDTH LAYOUT HANDLER
    |--------------------------------------------------------------------------
    */

    $isFullWidth = ($page->page_width ?? 'contained-wide') === 'full-width';

    $containerClass = 'container py-4';

    if ($isFullWidth) {
        $containerClass = 'container-fluid px-0 py-0';
    } elseif (($page->page_width ?? '') === 'boxed') {
        $containerClass = 'container py-4';
    } elseif (($page->page_width ?? '') === 'contained-wide') {
        $containerClass = 'container py-4';
    }

@endphp


{{-- =========================================================
     SEO META
========================================================= --}}

@section('title', $seoTitle)

@section('meta_title', $seoTitle)

@section('meta_description', $seoDesc)

@section('meta_keywords', $seoKeywords)

@section('canonical', $canonicalUrl)

@section('og_image', $ogImage)


@section('content')


{{-- =========================================================
     EXTERNAL FONTS / ICONS
========================================================= --}}

<link
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    rel="stylesheet"
>

<link
    href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet"
>


<style>
    :root {
        --page-bg: {{ $pageBg }};
        --page-text: {{ $pageText }};
        --page-title: {{ $pageTitleColor }};
        --page-card-bg: {{ $pageCardBg }};
        --page-card-border: {{ $pageCardBorder }};
        --page-sidebar-bg: {{ $pageSidebarBg }};
        --page-sidebar-border: {{ $pageSidebarBorder }};
        --page-sidebar-head: {{ $pageSidebarHead }};
        --page-sidebar-text: {{ $pageSidebarText }};
        --page-breadcrumb-link: {{ $pageBreadcrumb }};
        --page-breadcrumb-hover: {{ $pageBreadcrumbHov }};
    }

    /*
    |--------------------------------------------------------------------------
    | MAIN PAGE WRAPPER
    |--------------------------------------------------------------------------
    */

    .sit-dynamic-page-wrap {
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: var(--page-text) !important;
        background-color: var(--page-bg) !important;
        width: 100%;
        overflow-x: hidden;
    }


    /*
    |--------------------------------------------------------------------------
    | PAGE TITLE
    |--------------------------------------------------------------------------
    */

    .sit-dynamic-page-title {
        font-size:
            {{ !empty($page->title_font_size)
                ? $page->title_font_size
                : '30px' }} !important;
        font-weight: 800;
        color: var(--page-title) !important;
        line-height: 1.3;
    }


    /*
    |--------------------------------------------------------------------------
    | RAW CANVAS
    |--------------------------------------------------------------------------
    */

    .sit-raw-canvas {
        width: 100%;
        box-sizing: border-box;
        color: var(--page-text) !important;
    }


    /*
    |--------------------------------------------------------------------------
    | MAIN CONTENT CARD
    |--------------------------------------------------------------------------
    */

    .sit-main-content-card {
        background-color: var(--page-card-bg) !important;
        border-radius: 14px;
        border: 1px solid var(--page-card-border) !important;
        color: var(--page-text) !important;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
        width: 100%;
        box-sizing: border-box;
        overflow: hidden;
        position: relative;
    }


    /*
    |--------------------------------------------------------------------------
    | CONTENT IMAGES
    |--------------------------------------------------------------------------
    */

    .sit-raw-canvas img,
    .sit-main-content-card img {
        max-width: 100% !important;
        height: auto;
    }


    /*
    |--------------------------------------------------------------------------
    | IFRAMES
    |--------------------------------------------------------------------------
    */

    .sit-raw-canvas iframe,
    .sit-main-content-card iframe {
        max-width: 100% !important;
    }


    /*
    |--------------------------------------------------------------------------
    | TABLES
    |--------------------------------------------------------------------------
    */

    .sit-raw-canvas table,
    .sit-main-content-card table {
        width: 100% !important;
        max-width: 100% !important;
        overflow-x: auto;
        display: block;
    }


    /*
    |--------------------------------------------------------------------------
    | SIDEBAR CARD
    |--------------------------------------------------------------------------
    */

    .sit-sidebar-card {
        background-color: var(--page-sidebar-bg) !important;
        border-radius: 12px;
        border: 1px solid var(--page-sidebar-border) !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        margin-bottom: 20px;
        overflow: hidden;
    }

    .sit-sidebar-card:last-child {
        margin-bottom: 0;
    }


    /*
    |--------------------------------------------------------------------------
    | SIDEBAR HEADER
    |--------------------------------------------------------------------------
    */

    .sit-sidebar-header {
        font-size: 14px;
        font-weight: 700;
        padding: 12px 16px;
        border-bottom: 1px solid var(--page-sidebar-border) !important;
        color: var(--page-sidebar-head) !important;
    }


    /*
    |--------------------------------------------------------------------------
    | SIDEBAR BODY
    |--------------------------------------------------------------------------
    */

    .sit-sidebar-body {
        padding: 14px 16px;
        color: var(--page-sidebar-text) !important;
    }


    /*
    |--------------------------------------------------------------------------
    | BREADCRUMB STYLING
    |--------------------------------------------------------------------------
    */
    .sit-page-breadcrumb a {
        color: var(--page-breadcrumb-link) !important;
        text-decoration: none;
        transition: color 0.15s ease;
    }
    .sit-page-breadcrumb a:hover {
        color: var(--page-breadcrumb-hover) !important;
    }
    .sit-page-breadcrumb .breadcrumb-item.active {
        color: var(--page-title) !important;
    }


    /*
    |--------------------------------------------------------------------------
    | DYNAMIC GRID LAYOUT
    |--------------------------------------------------------------------------
    */

    .dynamic-product-grid {
        display: grid !important;
        gap: 16px !important;
        width: 100% !important;
        grid-template-columns: repeat(var(--col-pc, 4), minmax(0, 1fr)) !important;
        transition: opacity 0.2s ease;
    }
    @media (max-width: 991.98px) {
        .dynamic-product-grid {
            grid-template-columns: repeat(var(--col-tab, 3), minmax(0, 1fr)) !important;
        }
    }
    @media (max-width: 575.98px) {
        .dynamic-product-grid {
            grid-template-columns: repeat(var(--col-mobile, 2), minmax(0, 1fr)) !important;
            gap: 10px !important;
        }
    }
    .product-card-wrap {
        width: 100% !important;
        min-width: 0 !important;
    }


    /*
    |--------------------------------------------------------------------------
    | PURE SHIMMER SKELETON CARD EFFECT
    |--------------------------------------------------------------------------
    */

    .sc-skeleton-card {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #edf2f7;
        padding: 10px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
    }
    .sc-shimmer {
        background: #f1f5f9;
        background-image: linear-gradient(90deg, #f1f5f9 0px, #e2e8f0 50px, #f1f5f9 100px);
        background-size: 600px 100%;
        animation: scShimmerWave 1.4s infinite linear;
        border-radius: 6px;
    }
    .sc-skel-img {
        width: 100%;
        aspect-ratio: 1 / 1;
        border-radius: 10px;
    }
    .sc-skel-title {
        height: 14px;
        width: 80%;
    }
    .sc-skel-price {
        height: 16px;
        width: 45%;
    }
    .sc-skel-btn {
        height: 32px;
        width: 100%;
        border-radius: 20px;
        margin-top: 4px;
    }
    @keyframes scShimmerWave {
        0% { background-position: -300px 0; }
        100% { background-position: 300px 0; }
    }


    /*
    |--------------------------------------------------------------------------
    | 100% RESPONSIVE & ALWAYS CENTERED PAGINATION
    |--------------------------------------------------------------------------
    */

    .featured-pagination-container,
    .sitcommerce-ajax-section-wrapper .sc-pagination-box,
    .sit-dynamic-page-wrap .sc-pagination-box {
        width: 100% !important;
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        margin-top: 24px !important;
        padding: 10px 0 !important;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch !important;
        scrollbar-width: none !important;
    }
    .featured-pagination-container::-webkit-scrollbar,
    .sitcommerce-ajax-section-wrapper .sc-pagination-box::-webkit-scrollbar,
    .sit-dynamic-page-wrap .sc-pagination-box::-webkit-scrollbar {
        display: none !important;
    }

    .featured-pagination-container nav,
    .sitcommerce-ajax-section-wrapper .sc-pagination-box nav,
    .sit-dynamic-page-wrap .sc-pagination-box nav {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin: 0 auto !important;
        width: auto !important;
    }

    .featured-pagination-container nav > div:first-child,
    .sitcommerce-ajax-section-wrapper .sc-pagination-box nav > div:first-child,
    .sit-dynamic-page-wrap .sc-pagination-box nav > div:first-child {
        display: none !important;
    }

    .featured-pagination-container nav > div:last-child,
    .sitcommerce-ajax-section-wrapper .sc-pagination-box nav > div:last-child,
    .sit-dynamic-page-wrap .sc-pagination-box nav > div:last-child {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin: 0 auto !important;
    }

    .featured-pagination-container nav > div:last-child > div:first-child,
    .sitcommerce-ajax-section-wrapper .sc-pagination-box nav > div:last-child > div:first-child,
    .sit-dynamic-page-wrap .sc-pagination-box nav > div:last-child > div:first-child {
        display: none !important;
    }

    .featured-pagination-container .pagination,
    .sitcommerce-ajax-section-wrapper .sc-pagination-box .pagination,
    .sit-dynamic-page-wrap .sc-pagination-box .pagination {
        margin: 0 auto !important;
        gap: 5px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-wrap: nowrap !important;
    }

    .featured-pagination-container .page-link,
    .sitcommerce-ajax-section-wrapper .sc-pagination-box .page-link,
    .sit-dynamic-page-wrap .sc-pagination-box .page-link {
        border-radius: 8px !important;
        border: 1px solid #e2e8f0;
        color: #334155;
        font-weight: 600;
        font-size: 13px;
        padding: 6px 12px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        background: #ffffff;
    }

    .featured-pagination-container .page-item.active .page-link,
    .sitcommerce-ajax-section-wrapper .sc-pagination-box .page-item.active .page-link,
    .sit-dynamic-page-wrap .sc-pagination-box .page-item.active .page-link {
        background-color: var(--common-btn-bg, #2563eb) !important;
        border-color: var(--common-btn-bg, #2563eb) !important;
        color: var(--common-btn-text, #ffffff) !important;
        box-shadow: 0 3px 8px rgba(37, 99, 235, 0.25);
    }

    .featured-pagination-container .page-link:hover,
    .sitcommerce-ajax-section-wrapper .sc-pagination-box .page-link:hover,
    .sit-dynamic-page-wrap .sc-pagination-box .page-link:hover {
        background-color: var(--menu-active-bg, #eff6ff);
        color: var(--common-btn-bg, #2563eb);
    }

    @media (max-width: 767.98px) {
        .featured-pagination-container,
        .sitcommerce-ajax-section-wrapper .sc-pagination-box,
        .sit-dynamic-page-wrap .sc-pagination-box {
            padding: 8px 4px !important;
            justify-content: center !important;
        }
        .featured-pagination-container .page-link,
        .sitcommerce-ajax-section-wrapper .sc-pagination-box .page-link,
        .sit-dynamic-page-wrap .sc-pagination-box .page-link {
            font-size: 11.5px !important;
            padding: 4px 8px !important;
            min-width: 30px !important;
            height: 30px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FORCE KILL STICKY PRODUCT BAR ON CUSTOM DYNAMIC PAGES
    |--------------------------------------------------------------------------
    */
    #stickySingleProductBar,
    .sticky-single-product-bar {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
        pointer-events: none !important;
        transform: translateY(200%) !important;
    }

    /*
    |--------------------------------------------------------------------------
    | CUSTOM PAGE CSS
    |--------------------------------------------------------------------------
    */

    {!! $page->custom_css ?? '' !!}

</style>


<div class="{{ $containerClass }} sit-dynamic-page-wrap">


    {{-- =====================================================
         BREADCRUMB
    ====================================================== --}}

    @if(!$isFullWidth)

        <nav aria-label="breadcrumb" class="mb-3 sit-page-breadcrumb">

            <ol class="breadcrumb small">

                <li class="breadcrumb-item">

                    <a href="{{ route('home') }}">
                        Home
                    </a>

                </li>


                <li class="breadcrumb-item active fw-semibold" aria-current="page">

                    {{ $page->title }}

                </li>

            </ol>

        </nav>

    @else

        <div class="container pt-3">

            <nav aria-label="breadcrumb" class="mb-3 sit-page-breadcrumb">

                <ol class="breadcrumb small">

                    <li class="breadcrumb-item">

                        <a href="{{ route('home') }}">
                            Home
                        </a>

                    </li>


                    <li class="breadcrumb-item active fw-semibold" aria-current="page">

                        {{ $page->title }}

                    </li>

                </ol>

            </nav>

        </div>

    @endif


    {{-- =====================================================
         PAGE TITLE
    ====================================================== --}}

    @if(!isset($page->show_title) || $page->show_title == 1)

        <div class="{{ $isFullWidth ? 'container py-2' : '' }}">

            <h2 class="sit-dynamic-page-title mb-4">

                {{ $page->title }}

            </h2>

        </div>

    @endif


    {{-- =====================================================
         FEATURED IMAGE (DYNAMIC RESOLVER)
    ====================================================== --}}

    @if(!empty($pageFeaturedImg) && (!isset($page->show_featured_image) || $page->show_featured_image == 1))

        <div class="mb-4 text-center {{ $isFullWidth ? 'w-100' : '' }}">

            <img
                src="{{ $pageFeaturedImg }}"
                alt="{{ $page->title }}"
                class="img-fluid {{ $isFullWidth ? 'w-100 rounded-0' : 'rounded-4 shadow-xs w-100' }}"
                style="max-height: 480px; object-fit: cover;"
                loading="lazy"
            >

        </div>

    @endif


    {{-- =====================================================
         SIDEBAR ENABLED
    ====================================================== --}}

    @if(!empty($page->show_sidebar_widget))


        <div class="{{ $isFullWidth ? 'container py-4' : '' }}">

            <div class="row g-4">


                {{-- =================================================
                     MAIN CONTENT
                ================================================== --}}

                <div class="col-12 col-lg-8 col-xl-9">


                    <div class="sit-main-content-card p-3 p-md-4">

                        {!! $renderedContent !!}

                    </div>

                </div>



                {{-- =================================================
                     RIGHT SIDEBAR
                ================================================== --}}

                <div class="col-12 col-lg-4 col-xl-3">


                    <div class="d-flex flex-column gap-3">


                        {{-- =================================================
                             CUSTOM SIDEBAR WIDGETS
                        ================================================== --}}

                        @forelse($sidebarWidgets ?? [] as $widget)


                            <div class="sit-sidebar-card">


                                <div class="sit-sidebar-header">

                                    {{ $widget->title }}

                                </div>


                                <div class="sit-sidebar-body small">

                                    {!! html_entity_decode(
                                        $widget->content ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) !!}

                                </div>


                            </div>


                        @empty


                            {{-- =================================================
                                 POPULAR CATEGORIES
                            ================================================== --}}

                            <div class="sit-sidebar-card">


                                <div class="sit-sidebar-header">

                                    Popular Categories

                                </div>


                                <div class="sit-sidebar-body p-0">


                                    <ul class="list-unstyled mb-0 small">


                                        @forelse($popularCategories ?? [] as $cat)


                                            <li class="py-2.5 px-3 border-bottom d-flex justify-content-between align-items-center">


                                                <a href="{{ route('category.products', $cat->slug ?? $cat->id) }}" class="text-decoration-none fw-medium" style="color: var(--page-sidebar-text);">

                                                    {{ $cat->name }}

                                                </a>


                                                <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 10px;">

                                                    Explore

                                                </span>


                                            </li>


                                        @empty


                                            <li class="p-3 text-muted">

                                                No categories found.

                                            </li>


                                        @endforelse


                                    </ul>


                                </div>


                            </div>


                            {{-- =================================================
                                 SPECIAL OFFER
                            ================================================== --}}

                            <div
                                class="card border-0 shadow-xs rounded-4 p-4 text-center text-white"
                                style="background: linear-gradient(135deg, var(--common-btn-bg, #2563eb) 0%, #1d4ed8 100%);"
                            >


                                <span class="badge bg-warning text-dark align-self-center mb-2 px-3 py-1 font-monospace fw-bold">

                                    SPECIAL OFFER

                                </span>


                                <h5 class="fw-bold mb-1">

                                    Get 20% Discount

                                </h5>


                                <p class="small opacity-75 mb-3">

                                    On all new collection items

                                </p>


                                <a href="{{ route('shop') }}" class="btn btn-light btn-sm fw-bold rounded-pill px-4 py-2 text-primary theme-text-primary shadow-xs">

                                    Shop Now

                                </a>


                            </div>


                        @endforelse


                    </div>


                </div>


            </div>

        </div>


    @else


        {{-- =====================================================
             FULL CANVAS / NO SIDEBAR
        ====================================================== --}}

        <div class="sit-raw-canvas {{ $isFullWidth ? '' : 'container py-4 p-3 p-md-4 bg-white rounded-4 border shadow-xs' }}">

            {!! $renderedContent !!}

        </div>


    @endif


</div>


{{-- =========================================================
     CUSTOM PAGE JAVASCRIPT
========================================================= --}}

<script>
    // ================= REAL PRODUCT CARD SKELETON SHIMMER GENERATOR =================
    function getCardSkeletonHTML(count = 8) {
        let skelHtml = '';
        for (let i = 0; i < count; i++) {
            skelHtml += `
                <div class="product-card-wrap">
                    <div class="sc-skeleton-card">
                        <div class="sc-shimmer sc-skel-img"></div>
                        <div class="sc-shimmer sc-skel-title"></div>
                        <div class="sc-shimmer sc-skel-price"></div>
                        <div class="sc-shimmer sc-skel-btn"></div>
                    </div>
                </div>`;
        }
        return skelHtml;
    }

    // ================= INSTANT AJAX PAGINATION & FILTER WITH SHIMMER SKELETON =================
    document.addEventListener('click', function(e) {
        const pageLink = e.target.closest('.sitcommerce-ajax-section-wrapper .sc-pagination-box .page-link, .featured-pagination-container .page-link, .sc-pagination-box .page-link');
        if (pageLink) {
            const href = pageLink.getAttribute('href');
            if (href && href !== '#' && !href.startsWith('javascript:')) {
                e.preventDefault();

                const wrapper = pageLink.closest('.sitcommerce-ajax-section-wrapper') || pageLink.closest('.home-custom-builder-section') || pageLink.closest('.sit-main-content-card') || pageLink.closest('.sit-raw-canvas');
                if (!wrapper) return;

                const grid = wrapper.querySelector('.dynamic-product-grid') || wrapper.querySelector('.row');
                const cardCount = grid ? (grid.children.length || 8) : 8;

                if (grid) {
                    grid.innerHTML = getCardSkeletonHTML(cardCount);
                }

                fetch(href, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const targetWrapperId = wrapper.getAttribute('id');
                    
                    let newWrapper = null;
                    if (targetWrapperId) {
                        newWrapper = doc.getElementById(targetWrapperId);
                    } else {
                        newWrapper = doc.querySelector('.sit-main-content-card') || doc.querySelector('.sit-raw-canvas');
                    }

                    if (newWrapper) {
                        wrapper.innerHTML = newWrapper.innerHTML;
                    }

                    const rect = wrapper.getBoundingClientRect();
                    if (rect.top < 0) {
                        window.scrollTo({
                            top: window.scrollY + rect.top - 80,
                            behavior: 'smooth'
                        });
                    }
                })
                .catch(err => {
                    console.error('AJAX Dynamic Page Pagination Error:', err);
                    window.location.href = href;
                });
            }
        }
    });

    // Explicitly destroy layout sticky bar on custom dynamic page load
    (function killStickySingleBar() {
        const bar = document.getElementById('stickySingleProductBar');
        if (bar) {
            bar.remove();
        }
    })();

    document.addEventListener('DOMContentLoaded', function() {
        const bar = document.getElementById('stickySingleProductBar');
        if (bar) {
            bar.remove();
        }
    });

    {!! $page->custom_js ?? '' !!}

</script>

@endsection