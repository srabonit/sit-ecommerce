@extends('layouts.app')

@php
    // ১. show_image() ফাংশন স্বয়ংক্রিয়ভাবে নিশ্চিতকরণ (Composer dump-autoload ছাড়াও যাতে কাজ করে)
    if (!function_exists('show_image')) {
        if (file_exists(app_path('Helpers/helpers.php'))) {
            require_once app_path('Helpers/helpers.php');
        }
    }

    if (!function_exists('show_image')) {
        function show_image($path, $fallback = 'https://placehold.co/300x300?text=No+Image') {
            if (empty($path)) return $fallback;
            if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
            $clean = ltrim(str_replace(['storage/', 'public/'], '', $path), '/');
            $publicRoot = is_dir(base_path('../public_html')) ? base_path('../public_html') : public_path();
            if (file_exists($publicRoot . '/' . $clean)) return asset($clean);
            if (file_exists($publicRoot . '/uploads/' . $clean)) return asset('uploads/' . $clean);
            if (file_exists(storage_path('app/public/' . $clean))) {
                $destDir = $publicRoot . '/uploads/' . dirname($clean);
                if (!file_exists($destDir)) @mkdir($destDir, 0777, true);
                @copy(storage_path('app/public/' . $clean), $publicRoot . '/uploads/' . $clean);
                return asset('uploads/' . $clean);
            }
            if (str_starts_with($clean, 'uploads/')) return asset($clean);
            return asset('uploads/' . $clean);
        }
    }

    $globalSettings = DB::table('settings')->pluck('value', 'key');

    $colMobile = $globalSettings['grid_mobile'] ?? '2';
    $colTab    = $globalSettings['grid_tab'] ?? '3';
    $colPc     = $colPc ?? ($globalSettings['grid_pc'] ?? '4');

    $showCategoryMenu = !isset($globalSettings['show_home_category_menu']) || $globalSettings['show_home_category_menu'] == '1';
    $showPopularCatSection = !isset($globalSettings['show_popular_categories_section']) || $globalSettings['show_popular_categories_section'] == '1';

    $catMenuRow = DB::table('site_menus')->whereJsonContains('locations', 'mobile_categories')->first();
    if (!$catMenuRow) {
        $catMenuRow = DB::table('site_menus')->whereJsonContains('locations', 'primary')->first();
    }
    $rawCatItems = !empty($catMenuRow->items) ? json_decode($catMenuRow->items, true) : [];

    $homeCategoryTree = [];
    $hPointers = [];
    foreach ($rawCatItems as $item) {
        $item['children'] = [];
        $depth = $item['depth'] ?? ($item['is_sub'] ?? false ? 1 : 0);

        if ($depth === 0) {
            $homeCategoryTree[] = $item;
            $hPointers[0] = count($homeCategoryTree) - 1;
        } elseif ($depth === 1 && isset($hPointers[0])) {
            $homeCategoryTree[$hPointers[0]]['children'][] = $item;
            $hPointers[1] = count($homeCategoryTree[$hPointers[0]]['children']) - 1;
        } elseif ($depth === 2 && isset($hPointers[0], $hPointers[1])) {
            $homeCategoryTree[$hPointers[0]]['children'][$hPointers[1]]['children'][] = $item;
        }
    }

    if (empty($homeCategoryTree)) {
        $dbCategories = \App\Models\Category::all();
        foreach ($dbCategories as $cat) {
            $homeCategoryTree[] = [
                'title'    => $cat->name,
                'url'      => route('category.products', $cat->slug ?? $cat->id),
                'children' => []
            ];
        }
    }

    $formatUrl = function($url) {
        if (empty($url) || $url === '#') return '#';
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $parsed = parse_url($url);
            return url(($parsed['path'] ?? '/') . (!empty($parsed['query']) ? '?' . $parsed['query'] : ''));
        }
        return url($url);
    };

    $isTreeActive = function($node) use ($formatUrl, &$isTreeActive) {
        $targetUrl = $formatUrl($node['url'] ?? '');
        if ($targetUrl !== '#' && url()->current() === $targetUrl) return true;
        if (!empty($node['children'])) {
            foreach ($node['children'] as $ch) {
                if ($isTreeActive($ch)) return true;
            }
        }
        return false;
    };
@endphp

@section('title', $globalSettings['site_title'] ?? ($globalSettings['seo_meta_title'] ?? config('app.name', 'Ecommerce')))

@section('content')
<style>
    .home-viewport {
        background-color: #f8fafc;
        position: relative;
        z-index: 1;
    }

    .widget-category-menu {
        border-radius: 16px;
        background: #ffffff;
        border: 1px solid #edf2f7;
        position: relative;
        overflow: visible !important;
    }

    .home-cat-list {
        list-style: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    .home-cat-row {
        position: relative;
        list-style: none !important;
    }

    .home-cat-link {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 9.5px 16px;
        font-size: 13.5px;
        font-weight: 500;
        color: #334155;
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .home-cat-link:hover,
    .home-cat-row:hover > .home-cat-link {
        background-color: #f1f5f9;
        color: #2563eb !important;
    }

    .home-cat-link.active {
        background-color: #eff6ff;
        color: #2563eb !important;
        font-weight: 700;
    }

    .home-cat-flyout-box {
        display: none !important;
        position: absolute;
        top: 0;
        left: 100%;
        min-width: 220px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.12), 0 8px 10px -6px rgba(0, 0, 0, 0.08);
        padding: 8px 0;
        z-index: 10 !important;
        margin-left: 6px;
        list-style: none !important;
    }

    .home-cat-row:hover > .home-cat-flyout-box {
        display: block !important;
        animation: homeCatFlyoutAnim 0.2s ease-in-out;
    }

    .home-cat-row::after {
        content: '';
        position: absolute;
        top: 0;
        right: -8px;
        width: 12px;
        height: 100%;
    }

    .home-sub-link {
        display: block;
        padding: 8px 18px;
        color: #475569;
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.15s ease;
    }

    .home-sub-link:hover {
        background-color: #f8fafc;
        color: #2563eb;
        padding-left: 22px;
    }

    .home-sub-link.active {
        color: #2563eb;
        font-weight: 700;
        background-color: #eff6ff;
    }

    @keyframes homeCatFlyoutAnim {
        from { opacity: 0; transform: translateX(-6px); }
        to { opacity: 1; transform: translateX(0); }
    }

    /* ================= DYNAMIC HERO SLIDER STYLES ================= */
    .hero-slider-wrap {
        border-radius: 14px;
        overflow: hidden;
        position: relative;
        min-height: 280px;
        height: 100%;
        background: #0f172a;
        z-index: 1;
        transition: all 0.25s ease-in-out;
    }
    @media (min-width: 768px) {
        .hero-slider-wrap { min-height: 380px; }
    }
    .hero-slide-item {
        position: absolute;
        inset: 0;
        opacity: 0;
        transition: opacity 1s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        padding: 24px;
        background-size: cover;
        background-position: center center;
        background-repeat: no-repeat;
    }
    @media (min-width: 768px) {
        .hero-slide-item { padding: 40px; }
    }
    .hero-slide-item.active {
        opacity: 1;
    }
    .hero-slide-overlay {
        position: absolute;
        inset: 0;
        pointer-events: none;
        z-index: 1;
    }
    .hero-slide-content {
        position: relative;
        z-index: 2;
        max-width: 480px;
    }

    /* ================= YOUTUBE / LINKEDIN BANNER STYLE FOR MOBILE ================= */
    @media (max-width: 767.98px) {
        .hero-slider-wrap:has(.hero-slide-item.active.ratio-mobile-landscape),
        .hero-slider-wrap.mobile-is-landscape {
            aspect-ratio: 21 / 9 !important;
            min-height: 145px !important;
            max-height: 175px !important;
            height: auto !important;
        }

        .hero-slider-wrap:has(.hero-slide-item.active.ratio-mobile-square),
        .hero-slider-wrap.mobile-is-square {
            aspect-ratio: 1 / 1 !important;
            max-height: 260px !important;
            min-height: 220px !important;
            height: auto !important;
        }

        .hero-slider-wrap:has(.hero-slide-item.active.ratio-mobile-portrait),
        .hero-slider-wrap.mobile-is-portrait {
            aspect-ratio: 4 / 5 !important;
            max-height: 320px !important;
            height: auto !important;
        }

        .hero-slide-item.ratio-mobile-landscape {
            aspect-ratio: 21 / 9 !important;
            max-height: 175px !important;
            height: 100% !important;
            padding: 14px 12px !important;
        }
        .hero-slide-item.ratio-mobile-square {
            aspect-ratio: 1 / 1 !important;
            max-height: 260px !important;
            height: 100% !important;
            padding: 16px 14px !important;
        }
        .hero-slide-item.ratio-mobile-portrait {
            aspect-ratio: 4 / 5 !important;
            max-height: 320px !important;
            height: 100% !important;
            padding: 16px 14px !important;
        }

        .hero-slide-content {
            padding: 0 !important;
            max-width: 100% !important;
        }
        .hero-slide-content h2 {
            font-size: 1.05rem !important;
            margin-bottom: 4px !important;
            line-height: 1.25 !important;
        }
        .hero-slide-content p {
            font-size: 11px !important;
            margin-bottom: 8px !important;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .hero-slide-content .badge {
            font-size: 9.5px !important;
            padding: 2px 8px !important;
            margin-bottom: 4px !important;
        }
        .hero-slide-content a.btn {
            font-size: 11px !important;
            padding: 4px 12px !important;
        }
    }

    .offer-slider-box {
        border-radius: 14px;
        overflow: hidden;
        position: relative;
        min-height: 280px;
        height: 100%;
        background: #ecfdf5;
        border: 1px solid #d1fae5;
        z-index: 1;
    }
    @media (min-width: 768px) {
        .offer-slider-box { min-height: 380px; }
    }
    .offer-slide-item {
        position: absolute;
        inset: 0;
        opacity: 0;
        transition: opacity 0.8s ease-in-out;
        padding: 16px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    @media (min-width: 768px) {
        .offer-slide-item { padding: 24px 22px; }
    }
    .offer-slide-item.active {
        opacity: 1;
    }

    .offer-feature-img-wrap {
        flex: 1 1 auto;
        min-height: 130px;
        max-height: 170px;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 8px 0;
    }
    .offer-feature-img-wrap img {
        max-height: 150px;
        max-width: 90%;
        object-fit: contain;
        filter: drop-shadow(0 6px 12px rgba(0,0,0,0.08));
    }

    .deals-panel-card {
        border-radius: 16px;
        border: 1.5px solid #fee2e2;
        background: linear-gradient(135deg, #ffffff 0%, #fff7f7 100%);
        box-shadow: 0 4px 20px rgba(239, 68, 68, 0.05);
        position: relative;
        z-index: 1;
    }
    
    .deals-timer-box {
        border-right: 1px solid #fed7d7;
        min-width: 100%;
        padding: 20px 16px;
        position: relative;
        overflow: hidden;
        background-color: #0f172a;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    @media (min-width: 992px) {
        .deals-timer-box {
            min-width: 300px;
            max-width: 350px;
            padding: 30px 24px;
            border-right: 1px solid #fed7d7;
            border-bottom: none;
        }
    }
    .deals-bg-slide-wrap {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
    }
    .deals-bg-slide-item {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center center;
        background-repeat: no-repeat;
        opacity: 0;
        transition: opacity 1.2s ease-in-out;
    }
    .deals-bg-slide-item.active {
        opacity: 1;
    }
    .deals-bg-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(15, 23, 42, 0.92) 0%, rgba(15, 23, 42, 0.78) 100%);
    }
    .deals-timer-content {
        position: relative;
    }

    .timer-tile {
        background: #ffffff;
        color: #0f172a;
        border-radius: 6px;
        padding: 4px 6px;
        font-weight: 800;
        font-size: 13px;
        min-width: 32px;
        display: inline-block;
        box-shadow: 0 2px 6px rgba(0,0,0,0.25);
    }
    @media (min-width: 768px) {
        .timer-tile { padding: 6px 8px; font-size: 15px; min-width: 40px; }
    }

    .flash-products-slider-row {
        display: flex;
        gap: 12px;
        overflow-x: auto;
        scroll-behavior: smooth;
        scrollbar-width: none;
        -ms-overflow-style: none;
        padding-bottom: 4px;
    }
    .flash-products-slider-row::-webkit-scrollbar {
        display: none;
    }
    .flash-product-card-item {
        flex: 0 0 165px;
        max-width: 165px;
        min-width: 165px;
    }
    @media (min-width: 768px) {
        .flash-product-card-item {
            flex: 0 0 210px;
            max-width: 210px;
            min-width: 210px;
        }
    }

    .featured-filter-scroll {
        display: flex;
        flex-wrap: nowrap;
        gap: 8px;
        overflow-x: auto;
        scrollbar-width: none;
        -ms-overflow-style: none;
        padding-bottom: 4px;
        max-width: 100%;
    }
    .featured-filter-scroll::-webkit-scrollbar {
        display: none;
    }
    .filter-pill-btn {
        white-space: nowrap;
        font-size: 12px;
        padding: 5px 14px;
        border-radius: 50px;
        font-weight: 600;
        flex-shrink: 0;
    }

    .cat-scroller-container {
        overflow: hidden;
        position: relative;
        width: 100%;
        padding: 10px 0;
        mask-image: linear-gradient(to right, transparent, black 5%, black 95%, transparent);
        -webkit-mask-image: linear-gradient(to right, transparent, black 5%, black 95%, transparent);
    }
    .cat-scroller-track {
        display: flex;
        gap: 12px;
        width: max-content;
        animation: scrollCategoriesRightToLeft 50s linear infinite;
    }
    .cat-scroller-track:hover {
        animation-play-state: paused;
    }
    @keyframes scrollCategoriesRightToLeft {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
    .cat-pill-card {
        width: 110px;
        background: #ffffff;
        border: 1px solid #edf2f7;
        border-radius: 14px;
        padding: 12px 8px;
        text-align: center;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        transition: all 0.25s ease;
        flex-shrink: 0;
    }
    @media (min-width: 768px) {
        .cat-pill-card { width: 135px; padding: 14px 10px; border-radius: 16px; }
    }
    .cat-pill-card:hover {
        border-color: #3b82f6;
        transform: translateY(-3px);
        box-shadow: 0 6px 16px rgba(59, 130, 246, 0.12);
    }
    .cat-icon-circle {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 8px;
        overflow: hidden;
    }
    @media (min-width: 768px) {
        .cat-icon-circle { width: 58px; height: 58px; margin-bottom: 10px; }
    }
    .cat-icon-circle img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .section-title-wrap {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        border-bottom: 1.5px solid #edf2f7;
        padding-bottom: 10px;
    }
    .section-title-wrap h4 {
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Dynamic Grid Layout */
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

    /* Shimmer Skeleton Card Effect */
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

    /* Centered Pagination */
    .featured-pagination-container,
    .sitcommerce-ajax-section-wrapper .sc-pagination-box {
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
    .sitcommerce-ajax-section-wrapper .sc-pagination-box::-webkit-scrollbar {
        display: none !important;
    }

    .featured-pagination-container nav,
    .sitcommerce-ajax-section-wrapper .sc-pagination-box nav {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin: 0 auto !important;
        width: auto !important;
    }

    .featured-pagination-container nav > div:first-child,
    .sitcommerce-ajax-section-wrapper .sc-pagination-box nav > div:first-child {
        display: none !important;
    }

    .featured-pagination-container nav > div:last-child,
    .sitcommerce-ajax-section-wrapper .sc-pagination-box nav > div:last-child {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin: 0 auto !important;
    }

    .featured-pagination-container nav > div:last-child > div:first-child,
    .sitcommerce-ajax-section-wrapper .sc-pagination-box nav > div:last-child > div:first-child {
        display: none !important;
    }

    .featured-pagination-container .pagination,
    .sitcommerce-ajax-section-wrapper .sc-pagination-box .pagination {
        margin: 0 auto !important;
        gap: 5px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-wrap: nowrap !important;
    }

    .featured-pagination-container .page-link,
    .sitcommerce-ajax-section-wrapper .sc-pagination-box .page-link {
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
    .sitcommerce-ajax-section-wrapper .sc-pagination-box .page-item.active .page-link {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        box-shadow: 0 3px 8px rgba(37, 99, 235, 0.25);
    }

    .featured-pagination-container .page-link:hover,
    .sitcommerce-ajax-section-wrapper .sc-pagination-box .page-link:hover {
        background-color: #eff6ff;
        color: #2563eb;
    }

    @media (max-width: 767.98px) {
        .cat-scroller-track {
            gap: 10px !important;
            animation: scrollCategoriesRightToLeft 40s linear infinite;
        }
        .featured-pagination-container,
        .sitcommerce-ajax-section-wrapper .sc-pagination-box {
            padding: 8px 4px !important;
            justify-content: center !important;
        }
        .featured-pagination-container .page-link,
        .sitcommerce-ajax-section-wrapper .sc-pagination-box .page-link {
            font-size: 11.5px !important;
            padding: 4px 8px !important;
            min-width: 30px !important;
            height: 30px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
    }
</style>

<div class="home-viewport py-3 py-md-4">
    <div class="container px-2 px-md-3">

        <!-- ================= ১. টপ হিরো গ্রিড ================= -->
        <div class="row g-3 mb-3 mb-md-4 {{ ($homeSettings['widget_position'] ?? 'left') === 'right' ? 'flex-row-reverse' : '' }} align-items-stretch">
            
            @if($showCategoryMenu && ($homeSettings['show_widget'] ?? true))
                <div class="col-lg-3 col-md-4 d-none d-md-block">
                    <div class="widget-category-menu shadow-sm p-2 h-100">
                        <div class="px-3 py-2 border-bottom fw-bold text-dark small text-uppercase d-flex justify-content-between align-items-center mb-1">
                            <span><i class="fas fa-bars me-2 text-primary"></i> CATEGORIES</span>
                            <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1" style="font-size: 11px;">Explore</span>
                        </div>

                        <ul class="home-cat-list mt-1">
                            @foreach($homeCategoryTree as $parentItem)
                                @php
                                    $hasKids = !empty($parentItem['children']);
                                    $isActive = $isTreeActive($parentItem);
                                    $parentUrl = $formatUrl($parentItem['url'] ?? '');
                                @endphp
                                <li class="home-cat-row mb-1">
                                    <a href="{{ $parentUrl }}" class="home-cat-link {{ $isActive ? 'active' : '' }}">
                                        <span class="text-truncate" style="max-width: 170px;">{{ $parentItem['title'] }}</span>
                                        @if($hasKids)
                                            <i class="fas fa-chevron-right text-muted" style="font-size: 10px;"></i>
                                        @endif
                                    </a>

                                    @if($hasKids)
                                        <ul class="home-cat-flyout-box">
                                            @foreach($parentItem['children'] as $childItem)
                                                @php
                                                    $childUrl = $formatUrl($childItem['url'] ?? '');
                                                    $isChildActive = (url()->current() === $childUrl);
                                                @endphp
                                                <li>
                                                    <a href="{{ $childUrl }}" class="home-sub-link {{ $isChildActive ? 'active' : '' }}">
                                                        {{ $childItem['title'] }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach

                            <li class="home-cat-row mt-1 pt-1 border-top">
                                <a href="{{ route('shop') }}" class="home-cat-link text-primary fw-bold">
                                    <span><i class="fas fa-th-large me-2"></i> All Categories</span>
                                    <i class="fas fa-arrow-right small" style="font-size: 10px;"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            @endif

            @php
                $showRightCard = !isset($flashSaleConfig->right_card_status) || (int)$flashSaleConfig->right_card_status === 1;
                $hasRightCardSlides = isset($offerCards) && $offerCards->count() > 0;
                $rightCardVisible = $showRightCard && $hasRightCardSlides;

                if ($showCategoryMenu && ($homeSettings['show_widget'] ?? true)) {
                    $sliderColClass = $rightCardVisible ? 'col-12 col-lg-6 col-md-8' : 'col-12 col-lg-9 col-md-8';
                    $rightCardColClass = 'col-lg-3 d-none d-lg-block';
                } else {
                    $sliderColClass = $rightCardVisible ? 'col-12 col-lg-8 col-xl-9 col-md-8' : 'col-12';
                    $rightCardColClass = 'col-lg-4 col-xl-3 d-none d-lg-block';
                }
            @endphp

            <div class="{{ $sliderColClass }}">
                <div class="hero-slider-wrap shadow-sm" id="heroAutoSlider">
                    @if(isset($heroSliders) && $heroSliders->count() > 0)
                        @foreach($heroSliders as $index => $slider)
                            @php
                                $mobileRatioClass = match($slider->mobile_view_ratio ?? 'landscape') {
                                    'square'   => 'ratio-mobile-square',
                                    'portrait' => 'ratio-mobile-portrait',
                                    default    => 'ratio-mobile-landscape',
                                };
                                $hasOverlay = !isset($slider->enable_overlay) || (int)$slider->enable_overlay === 1;
                                $overlayColor = $slider->overlay_color ?? '#0f172a';
                                $overlayOpacity = $slider->overlay_opacity ?? '0.45';
                                $showContent = !isset($slider->show_content) || (int)$slider->show_content === 1;
                            @endphp

                            <div class="hero-slide-item {{ $index === 0 ? 'active' : '' }} {{ $mobileRatioClass }}" 
                                 data-mobile-ratio="{{ $slider->mobile_view_ratio ?? 'landscape' }}"
                                 style="background-image: url('{{ show_image($slider->image) }}');">
                                
                                {{-- 1. Dynamic Color Overlay Control --}}
                                @if($hasOverlay)
                                    <div class="hero-slide-overlay" style="background-color: {{ $overlayColor }}; opacity: {{ $overlayOpacity }};"></div>
                                @endif

                                {{-- 2. Slide Content On/Off Control --}}
                                @if($showContent && (!empty($slider->title) || !empty($slider->badge_text) || !empty($slider->subtitle) || !empty($slider->btn_text)))
                                    <div class="hero-slide-content text-white">
                                        @if(!empty($slider->badge_text))
                                            <span class="badge rounded-pill px-3 py-1 mb-2 fw-semibold" style="background-color: {{ $slider->badge_bg ?? '#2563eb' }}; color: {{ $slider->badge_color ?? '#ffffff' }}; font-size: 11px;">
                                                {{ $slider->badge_text }}
                                            </span>
                                        @endif

                                        @if(!empty($slider->title))
                                            <h2 class="fs-4 fs-md-3 fw-bold mb-2" style="color: {{ $slider->title_color ?? '#ffffff' }};">
                                                {{ $slider->title }}
                                            </h2>
                                        @endif

                                        @if(!empty($slider->subtitle))
                                            <p class="small mb-3" style="color: {{ $slider->subtitle_color ?? 'rgba(255,255,255,0.7)' }}; font-size: 12.5px;">
                                                {{ $slider->subtitle }}
                                            </p>
                                        @endif

                                        @if(!empty($slider->btn_text))
                                            <a href="{{ $slider->btn_url ?? '/shop' }}" class="btn rounded-pill px-4 py-2 fw-bold shadow btn-sm" style="background-color: {{ $slider->btn_bg ?? '#2563eb' }}; color: {{ $slider->btn_color ?? '#ffffff' }};">
                                                {{ $slider->btn_text }} <i class="fas fa-arrow-right ms-1"></i>
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="hero-slide-item active ratio-mobile-landscape" data-mobile-ratio="landscape" style="background-image: url('https://images.unsplash.com/photo-1483985988355-763728e1935b?q=80&w=1200&auto=format&fit=crop');">
                            <div class="hero-slide-overlay" style="background-color: #0f172a; opacity: 0.45;"></div>
                            <div class="hero-slide-content text-white">
                                <span class="badge bg-primary text-white rounded-pill px-3 py-1 mb-2 fw-semibold" style="font-size: 11px;">New Arrivals</span>
                                <h2 class="fs-4 fs-md-3 fw-bold mb-2">Upgrade Your Lifestyle</h2>
                                <p class="small text-white-50 mb-3" style="font-size: 12.5px;">Explore verified trending fashion with fast delivery.</p>
                                <a href="{{ route('shop') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow btn-sm">
                                    Shop Now <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if($rightCardVisible)
                <div class="{{ $rightCardColClass }}">
                    <div class="offer-slider-box shadow-sm" id="offerAutoSlider">
                        @foreach($offerCards as $oIdx => $card)
                            <div class="offer-slide-item {{ $oIdx === 0 ? 'active' : '' }}">
                                <div>
                                    <span class="badge fw-bold px-3 py-1 rounded-pill small mb-2" style="background-color: {{ $card->badge_bg }}; color: {{ $card->badge_color }}; font-size: 10px;">
                                        {{ $card->badge_text }}
                                    </span>
                                    <h5 class="fw-bold text-dark mb-1 fs-6">{{ $card->title }}</h5>
                                    <p class="text-muted small mb-0" style="font-size: 11.5px;">{{ $card->subtitle }}</p>
                                </div>
                                <div class="offer-feature-img-wrap">
                                    <img src="{{ show_image($card->image) }}" alt="{{ $card->title }}">
                                </div>
                                <div>
                                    <a href="{{ $card->btn_url }}" class="btn btn-outline-success w-100 rounded-pill fw-bold small py-1.5" style="font-size: 12px;">
                                        {{ $card->btn_text }}
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        <!-- ================= ২. হট ডিলস অ্যান্ড অফারস ================= -->
        @php
            $isFlashSaleActive = !isset($flashSaleConfig->status) || (int)$flashSaleConfig->status === 1;
        @endphp

        @if($isFlashSaleActive && isset($dealProducts) && $dealProducts->count() > 0)
            <div class="deals-panel-card mb-3 mb-md-4 overflow-hidden shadow-sm">
                <div class="d-flex flex-column flex-lg-row">
                    
                    <div class="deals-timer-box">
                        @if(isset($flashSaleProductImages) && count($flashSaleProductImages) > 0)
                            <div class="deals-bg-slide-wrap" id="flashInfoBgSlider">
                                @foreach($flashSaleProductImages as $bIdx => $bProd)
                                    <div class="deals-bg-slide-item {{ $bIdx === 0 ? 'active' : '' }}" style="background-image: url('{{ show_image($bProd['image']) }}');"></div>
                                @endforeach
                            </div>
                        @endif

                        <div class="deals-bg-overlay"></div>

                        <div class="deals-timer-content text-white">
                            <span class="badge bg-danger text-white fw-bold rounded-pill px-2.5 py-1 mb-2" style="font-size: 10px; width: fit-content;">Hot Deals</span>
                            <h4 class="fw-bold text-white mb-1 fs-6 fs-md-5">{{ !empty($flashSaleConfig->title) ? $flashSaleConfig->title : 'Deals & Offers' }}</h4>
                            <p class="text-white-50 small mb-2" style="font-size: 11.5px;">{{ !empty($flashSaleConfig->subtitle) ? $flashSaleConfig->subtitle : 'Flash discounts expire soon' }}</p>
                            
                            <div class="d-flex gap-2 text-center mb-1">
                                <div><span class="timer-tile" id="deal-timer-days">00</span><div class="text-white-50 small mt-1" style="font-size: 9.5px;">Days</div></div>
                                <div><span class="timer-tile" id="deal-timer-hours">00</span><div class="text-white-50 small mt-1" style="font-size: 9.5px;">Hours</div></div>
                                <div><span class="timer-tile" id="deal-timer-mins">00</span><div class="text-white-50 small mt-1" style="font-size: 9.5px;">Mins</div></div>
                                <div><span class="timer-tile" id="deal-timer-secs">00</span><div class="text-white-50 small mt-1" style="font-size: 9.5px;">Secs</div></div>
                            </div>
                        </div>
                    </div>

                    <div class="p-2 p-md-3 overflow-hidden my-auto" style="flex-grow: 1;">
                        <div class="flash-products-slider-row" id="flashProductsSliderRow">
                            @foreach($dealProducts as $dealItem)
                                <div class="flash-product-card-item">
                                    <x-product-card :product="$dealItem" />
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>
        @endif

        <!-- ================= ৩. এক্সপ্লোর পপুলার ক্যাটাগরি ================= -->
        @if($showPopularCatSection && ($homeSettings['show_cat_slider'] ?? true))
            @php
                $displayCategories = (isset($popularCategories) && $popularCategories->count() > 0) ? $popularCategories : $categories;
            @endphp

            @if(count($displayCategories) > 0)
                <div class="card border-0 shadow-sm rounded-4 bg-white p-3 p-md-4 mb-3 mb-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="fw-bold text-dark mb-0 pop-cat-title fs-6 fs-md-5">
                            <i class="fas fa-shapes text-primary me-2"></i> Popular Categories
                        </h5>
                        <a href="{{ route('shop') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold pop-cat-view-btn" style="font-size: 11.5px;">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>

                    <div class="cat-scroller-container">
                        <div class="cat-scroller-track">
                            @foreach($displayCategories->concat($displayCategories) as $category)
                                @php
                                    $catUrl = route('category.products', $category->slug ?? $category->id);
                                    
                                    // image এবং thumbnail উভয় ফিল্ড থেকেই রেজল্ভ নিশ্চিতকরণ
                                    $rawCatImg = !empty($category->image) ? $category->image : ($category->thumbnail ?? null);
                                    $catImg = !empty($rawCatImg) ? show_image($rawCatImg) : null;

                                    $defaultIcon = match(strtolower($category->name)) {
                                        'fashion', 'clothing', 'gents fashion' => 'fa-tshirt',
                                        'ladies fashion', 'party dress', 'dress' => 'fa-female',
                                        'gadgets', 'speaker', 'power bank' => 'fa-headphones',
                                        'electronics', 'led', 'mobile' => 'fa-tv',
                                        'watch', 'watches' => 'fa-clock',
                                        'bags', 'purse bag' => 'fa-shopping-bag',
                                        'cosmetics', 'skincare' => 'fa-magic',
                                        default => 'fa-tag'
                                    };
                                @endphp
                                <a href="{{ $catUrl }}" class="cat-pill-card">
                                    <div class="cat-icon-circle">
                                        @if($catImg)
                                            <img src="{{ $catImg }}" alt="{{ $category->name }}">
                                        @elseif(!empty($category->icon))
                                            <i class="{{ $category->icon }} text-secondary fs-5"></i>
                                        @else
                                            <i class="fas {{ $defaultIcon }} text-secondary fs-5"></i>
                                        @endif
                                    </div>
                                    <span class="fw-bold text-dark small text-truncate w-100" style="font-size: 11.5px;" title="{{ $category->name }}">
                                        {{ $category->name }}
                                    </span>
                                    <small class="text-muted" style="font-size: 10px;">{{ $category->products_count ?? 0 }} Items</small>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        @endif

        <!-- ================= ৪. ফিচার্ড প্রোডাক্টস ================= -->
        @if($homeSettings['show_featured'] ?? true)
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 p-md-4 mb-3 mb-md-4" id="home-featured-wrapper">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                    <div>
                        <h4 class="fw-bold text-dark mb-1 fs-6 fs-md-5">
                            <i class="fas fa-fire text-danger me-2"></i> Featured Products
                        </h4>
                        <p class="text-muted small mb-0" style="font-size: 12px;">Handpicked collections updated every week</p>
                    </div>

                    <div class="featured-filter-scroll">
                        <button type="button" class="btn btn-sm btn-primary filter-pill-btn active" onclick="applyAjaxCategoryFilter('all', this)">
                            All Items
                        </button>
                        @if(isset($categories))
                            @foreach($categories->take(6) as $fCat)
                                <button type="button" class="btn btn-sm btn-outline-secondary filter-pill-btn" onclick="applyAjaxCategoryFilter('{{ $fCat->id }}', this)">
                                    {{ $fCat->name }}
                                </button>
                            @endforeach
                        @endif
                    </div>
                </div>

                <div id="featured-products-grid" class="dynamic-product-grid" style="--col-mobile: {{ $colMobile }}; --col-tab: {{ $colTab }}; --col-pc: {{ $colPc }};">
                    @if(isset($featuredProducts))
                        @php
                            $itemCollection = ($featuredProducts instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $featuredProducts->getCollection() : $featuredProducts;
                        @endphp
                        @forelse($itemCollection as $product)
                            <div class="product-card-wrap">
                                <x-product-card :product="$product" />
                            </div>
                        @empty
                            <div class="col-12 text-center text-muted py-5 small">No products available.</div>
                        @endforelse
                    @endif
                </div>

                <!-- Featured Products Centered Pagination -->
                <div id="featured-pagination-box" class="featured-pagination-container">
                    @if(isset($featuredProducts) && $featuredProducts instanceof \Illuminate\Pagination\LengthAwarePaginator && $featuredProducts->hasPages())
                        {{ $featuredProducts->onEachSide(1)->links('pagination::bootstrap-5') }}
                    @endif
                </div>
            </div>
        @endif

        <!-- ================= ৫. SITCOMMERCE DYNAMIC CUSTOM BLOCKS & SHORTCODES ================= -->
        @if(isset($homeDynamicSections) && $homeDynamicSections->count() > 0)
            @foreach($homeDynamicSections as $dynamicSec)
                @php
                    $isHeaderVisible = !isset($dynamicSec->show_header) || (int)$dynamicSec->show_header === 1;
                    $hasHeaderTitle = !empty(trim($dynamicSec->title ?? ''));
                    $hasHeaderButton = !empty($dynamicSec->btn_text) || !empty($dynamicSec->btn_url);
                    
                    $shouldShowHeader = $isHeaderVisible && ($hasHeaderTitle || $hasHeaderButton);
                    
                    $titleColor = !empty($dynamicSec->title_color) ? $dynamicSec->title_color : '#0f172a';
                    $iconClass = !empty($dynamicSec->icon) ? $dynamicSec->icon : 'fas fa-layer-group';
                    $btnBg = !empty($dynamicSec->btn_bg) ? $dynamicSec->btn_bg : 'transparent';
                    $btnColor = !empty($dynamicSec->btn_color) ? $dynamicSec->btn_color : '#2563eb';
                    $actionUrl = !empty($dynamicSec->btn_url) ? url($dynamicSec->btn_url) : route('shop');
                    $btnText = !empty($dynamicSec->btn_text) ? $dynamicSec->btn_text : 'View All';
                @endphp

                <div class="card border-0 shadow-sm rounded-4 bg-white p-3 p-md-4 mb-3 mb-md-4 home-custom-builder-section" id="section-block-{{ $dynamicSec->id }}">
                    
                    @if($shouldShowHeader)
                        <div class="section-title-wrap mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                            @if($hasHeaderTitle)
                                <h4 class="fs-6 fs-md-5 mb-0" style="color: {{ $titleColor }} !important;">
                                    @if(!empty($dynamicSec->icon))
                                        <i class="{{ $iconClass }} me-1" style="color: {{ $titleColor }};"></i>
                                    @endif
                                    {{ $dynamicSec->title }}
                                </h4>
                            @else
                                <div></div>
                            @endif

                            @if($hasHeaderButton)
                                <a href="{{ $actionUrl }}" class="btn btn-sm rounded-pill px-3 fw-bold small border" style="background-color: {{ $btnBg }}; color: {{ $btnColor }}; border-color: {{ $btnColor }} !important; font-size: 11.5px;">
                                    {{ $btnText }} <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            @endif
                        </div>
                    @endif

                    <div class="custom-builder-content">
                        {!! $dynamicSec->rendered_content ?? $dynamicSec->custom_content !!}
                    </div>
                </div>
            @endforeach
        @endif

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const heroWrap = document.getElementById('heroAutoSlider');
        const heroSlides = document.querySelectorAll('#heroAutoSlider .hero-slide-item');
        let currentHero = 0;

        function syncMobileSliderRatio() {
            if (!heroWrap || heroSlides.length === 0) return;
            const activeSlide = heroSlides[currentHero];
            const ratio = activeSlide ? activeSlide.getAttribute('data-mobile-ratio') : 'landscape';
            
            heroWrap.classList.remove('mobile-is-landscape', 'mobile-is-square', 'mobile-is-portrait');
            if (ratio === 'square') {
                heroWrap.classList.add('mobile-is-square');
            } else if (ratio === 'portrait') {
                heroWrap.classList.add('mobile-is-portrait');
            } else {
                heroWrap.classList.add('mobile-is-landscape');
            }
        }

        syncMobileSliderRatio();

        if (heroSlides.length > 1) {
            setInterval(() => {
                heroSlides[currentHero].classList.remove('active');
                currentHero = (currentHero + 1) % heroSlides.length;
                heroSlides[currentHero].classList.add('active');
                syncMobileSliderRatio();
            }, 4500);
        }

        const offerSlides = document.querySelectorAll('#offerAutoSlider .offer-slide-item');
        let currentOffer = 0;
        if (offerSlides.length > 1) {
            setInterval(() => {
                offerSlides[currentOffer].classList.remove('active');
                currentOffer = (currentOffer + 1) % offerSlides.length;
                offerSlides[currentOffer].classList.add('active');
            }, 3800);
        }

        const flashBgImgs = document.querySelectorAll('#flashInfoBgSlider .deals-bg-slide-item');
        let currentBgImg = 0;
        if (flashBgImgs.length > 1) {
            setInterval(() => {
                flashBgImgs[currentBgImg].classList.remove('active');
                currentBgImg = (currentBgImg + 1) % flashBgImgs.length;
                flashBgImgs[currentBgImg].classList.add('active');
            }, 3200);
        }

        const flashProdContainer = document.getElementById('flashProductsSliderRow');
        if (flashProdContainer) {
            let flashScrollInterval = setInterval(() => {
                const scrollStep = window.innerWidth < 768 ? (flashProdContainer.clientWidth / 2) : 220;
                if (flashProdContainer.scrollLeft + flashProdContainer.clientWidth >= flashProdContainer.scrollWidth - 10) {
                    flashProdContainer.scrollTo({ left: 0, behavior: 'smooth' });
                } else {
                    flashProdContainer.scrollBy({ left: scrollStep, behavior: 'smooth' });
                }
            }, 3000);

            flashProdContainer.addEventListener('mouseenter', () => clearInterval(flashScrollInterval));
            flashProdContainer.addEventListener('mouseleave', () => {
                flashScrollInterval = setInterval(() => {
                    const scrollStep = window.innerWidth < 768 ? (flashProdContainer.clientWidth / 2) : 220;
                    if (flashProdContainer.scrollLeft + flashProdContainer.clientWidth >= flashProdContainer.scrollWidth - 10) {
                        flashProdContainer.scrollTo({ left: 0, behavior: 'smooth' });
                    } else {
                        flashProdContainer.scrollBy({ left: scrollStep, behavior: 'smooth' });
                    }
                }, 3000);
            });
        }
    });

    @if(isset($flashSaleConfig->end_time) && $flashSaleConfig->end_time)
        const targetDate = new Date("{{ \Carbon\Carbon::parse($flashSaleConfig->end_time)->toIso8601String() }}").getTime();
        const timerInterval = setInterval(() => {
            const now = new Date().getTime();
            const distance = targetDate - now;

            if (distance < 0) {
                clearInterval(timerInterval);
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            if (document.getElementById('deal-timer-days')) document.getElementById('deal-timer-days').innerText = String(days).padStart(2, '0');
            if (document.getElementById('deal-timer-hours')) document.getElementById('deal-timer-hours').innerText = String(hours).padStart(2, '0');
            if (document.getElementById('deal-timer-mins')) document.getElementById('deal-timer-mins').innerText = String(minutes).padStart(2, '0');
            if (document.getElementById('deal-timer-secs')) document.getElementById('deal-timer-secs').innerText = String(seconds).padStart(2, '0');
        }, 1000);
    @endif

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

    let currentFeaturedCat = 'all';

    function applyAjaxCategoryFilter(catId, btnElement, targetPage = 1) {
        currentFeaturedCat = catId;

        if (btnElement) {
            document.querySelectorAll('.filter-pill-btn').forEach(b => {
                b.classList.remove('btn-primary', 'active');
                b.classList.add('btn-outline-secondary');
            });
            btnElement.classList.remove('btn-outline-secondary');
            btnElement.classList.add('btn-primary', 'active');
        }

        const grid = document.getElementById('featured-products-grid');
        const pagBox = document.getElementById('featured-pagination-box');
        const count = grid ? (grid.children.length || 8) : 8;

        if (grid) {
            grid.innerHTML = getCardSkeletonHTML(count);
        }

        const filterBaseUrl = "{{ route('home.filterProducts') }}";

        fetch(`${filterBaseUrl}?category_id=${catId}&featured_page=${targetPage}`)
            .then(res => res.json())
            .then(data => {
                if (grid) {
                    grid.innerHTML = data.html;
                }
                if (pagBox) {
                    pagBox.innerHTML = data.pagination_html || '';
                }
            })
            .catch(err => {
                console.error('Featured Filter error:', err);
            });
    }

    document.addEventListener('click', function(e) {
        const featPageLink = e.target.closest('#featured-pagination-box .page-link');
        if (featPageLink) {
            const href = featPageLink.getAttribute('href');
            if (href && href !== '#' && !href.startsWith('javascript:')) {
                e.preventDefault();
                const urlObj = new URL(href, window.location.origin);
                const pageNum = urlObj.searchParams.get('featured_page') || 1;
                applyAjaxCategoryFilter(currentFeaturedCat, null, pageNum);

                const featWrapper = document.getElementById('home-featured-wrapper');
                if (featWrapper) {
                    featWrapper.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            }
        }
    });

    document.addEventListener('click', function(e) {
        const pageLink = e.target.closest('.sitcommerce-ajax-section-wrapper .sc-pagination-box .page-link');
        if (pageLink) {
            const href = pageLink.getAttribute('href');
            if (href && href !== '#' && !href.startsWith('javascript:')) {
                e.preventDefault();

                const wrapper = pageLink.closest('.sitcommerce-ajax-section-wrapper');
                if (!wrapper) return;

                const grid = wrapper.querySelector('.dynamic-product-grid');
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
                    const newWrapper = doc.getElementById(targetWrapperId);

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
                    console.error('AJAX Shortcode Pagination Error:', err);
                    window.location.href = href;
                });
            }
        }
    });
</script>
@endsection