@php
$globalSettings = cache()->remember('global_settings_cache', 3600, function () {
    return DB::table('settings')->pluck('value', 'key');
});
$wishlistCount = count(session()->get('wishlist', []));

// Universal Image URL Formatter
if (!function_exists('cleanGlobalImageUrl')) {
    function cleanGlobalImageUrl($path) {
        if (empty($path)) return '';
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        $clean = ltrim(str_replace('storage/', '', $path), '/');
        if (file_exists(public_path($clean))) return asset($clean);
        if (file_exists(public_path('uploads/' . $clean))) return asset('uploads/' . $clean);
        return asset($clean);
    }
}

// Favicon path processing
$dbSiteIcon = $globalSettings['site_icon'] ?? null;
$finalFavicon = cleanGlobalImageUrl($dbSiteIcon);

// 1. App Master Layout & Global Background
$themeSiteBg = $globalSettings['color_site_bg'] ?? '#f8fafc';

// 2. App Sticky Single Product Bar Scoped Colors
$appSpbBg             = $globalSettings['color_app_spb_bg'] ?? '#ffffff';
$appSpbBorder         = $globalSettings['color_app_spb_border'] ?? 'rgba(226, 232, 240, 0.9)';
$appSpbTitle          = $globalSettings['color_app_spb_title'] ?? '#0f172a';
$appSpbTitleHover     = $globalSettings['color_app_spb_title_hover'] ?? '#2563eb';
$appSpbPrice          = $globalSettings['color_app_spb_price'] ?? '#2563eb';
$appSpbCartBtnBg      = $globalSettings['color_app_spb_cart_btn_bg'] ?? '#f8fafc';
$appSpbCartBtnText    = $globalSettings['color_app_spb_cart_btn_text'] ?? '#0f172a';
$appSpbCartBtnHoverBg = $globalSettings['color_app_spb_cart_btn_hover_bg'] ?? '#e2e8f0';
$appSpbBuyNowBg       = $globalSettings['color_app_spb_buynow_bg'] ?? '#0dcaf0';
$appSpbBuyNowText     = $globalSettings['color_app_spb_buynow_text'] ?? '#ffffff';
$appSpbBuyNowHoverBg  = $globalSettings['color_app_spb_buynow_hover_bg'] ?? '#0bb5d8';
$appSpbBuyNowHoverText= $globalSettings['color_app_spb_buynow_hover_text'] ?? '#ffffff';

// 3. App Bottom Search Modal Scoped Colors
$appSearchModalBg     = $globalSettings['color_app_search_modal_bg'] ?? '#ffffff';
$appSearchModalBorder = $globalSettings['color_app_search_modal_border'] ?? '#e2e8f0';
$appSearchPrice       = $globalSettings['color_app_search_price'] ?? '#2563eb';
$appSearchCartBtnBg   = $globalSettings['color_app_search_cart_btn_bg'] ?? '#2563eb';
$appSearchCartBtnText = $globalSettings['color_app_search_cart_btn_text'] ?? '#ffffff';
$appSearchCartBtnHoverBg = $globalSettings['color_app_search_cart_btn_hover_bg'] ?? '#1d4ed8';
$appSearchCartBtnHoverText = $globalSettings['color_app_search_cart_btn_hover_text'] ?? '#ffffff';

// 4. App Drawer Cart & Quick View Scoped Colors
$appDrawerBg          = $globalSettings['color_app_drawer_bg'] ?? '#ffffff';
$appDrawerBorder      = $globalSettings['color_app_drawer_border'] ?? '#e2e8f0';
$appDrawerIcon        = $globalSettings['color_app_drawer_icon'] ?? '#2563eb';
$appDrawerTotalPrice  = $globalSettings['color_app_drawer_total_price'] ?? '#dc2626';
$appDrawerCheckoutBg  = $globalSettings['color_app_drawer_checkout_bg'] ?? '#0f172a';
$appDrawerCheckoutText= $globalSettings['color_app_drawer_checkout_text'] ?? '#ffffff';
$appDrawerCheckoutHoverBg = $globalSettings['color_app_drawer_checkout_hover_bg'] ?? '#1e293b';
$appDrawerCheckoutHoverText = $globalSettings['color_app_drawer_checkout_hover_text'] ?? '#ffffff';
$appQuickViewSpinner  = $globalSettings['color_app_quickview_spinner'] ?? '#2563eb';

// 5. Scroll to Top
$appScrollTopBg       = $globalSettings['color_app_scroll_top_bg'] ?? '#ea580c';
$appScrollTopColor    = $globalSettings['color_app_scroll_top_color'] ?? '#ffffff';

$enableFloatingCart = ($globalSettings['feature_floating_cart_modal'] ?? '1') == '1';
$enableInstantCheckout = ($globalSettings['feature_instant_checkout_modal'] ?? '0') == '1';
$enableQuickView = ($globalSettings['feature_quick_view'] ?? '1') == '1';
$isStickyHeader = ($globalSettings['feature_sticky_header'] ?? '1') == '1';
$isGlassHeader = ($globalSettings['feature_glassmorphism_header'] ?? '0') == '1';

// Sticky Footer Config
$enableStickyFooter = ($globalSettings['feature_sticky_footer'] ?? '1') == '1';
$showOnMobile = !isset($globalSettings['sticky_footer_show_mobile']) || $globalSettings['sticky_footer_show_mobile'] == '1';
$showOnTablet = !isset($globalSettings['sticky_footer_show_tablet']) || $globalSettings['sticky_footer_show_tablet'] == '1';
$showOnDesktop = !isset($globalSettings['sticky_footer_show_desktop']) || $globalSettings['sticky_footer_show_desktop'] == '1';
$desktopStyle = $globalSettings['sticky_footer_desktop_style'] ?? 'boxed_floating';
$footerGlass = ($globalSettings['sticky_footer_glassmorphism'] ?? '1') == '1';
$footerHideOnScroll = ($globalSettings['sticky_footer_hide_on_scroll'] ?? '0') == '1';

// Dynamic Footer Row Background & Nav Colors
$footerRowBg = $globalSettings['sticky_footer_row_bg'] ?? 'transparent';
$footerBg = $globalSettings['sticky_footer_bg'] ?? '#ffffff';
$footerTextColor = $globalSettings['sticky_footer_text_color'] ?? '#64748b';
$footerActiveColor = $globalSettings['sticky_footer_active_color'] ?? '#2563eb';

// Bottom To Top Config
$enableScrollTop = ($globalSettings['feature_scroll_to_top'] ?? '1') == '1';
$stShowMobile = !isset($globalSettings['scroll_top_show_mobile']) || $globalSettings['scroll_top_show_mobile'] == '1';
$stShowTablet = !isset($globalSettings['scroll_top_show_tablet']) || $globalSettings['scroll_top_show_tablet'] == '1';
$stShowDesktop = !isset($globalSettings['scroll_top_show_desktop']) || $globalSettings['scroll_top_show_desktop'] == '1';
$stStyle = $globalSettings['scroll_top_style'] ?? 'classic_rounded';
$stPosition = $globalSettings['scroll_top_position'] ?? 'right';

// Sticky Single Product Bar Config
$enableStickyProductBar = ($globalSettings['feature_sticky_product_bar'] ?? '1') == '1';
$spbShowMobile = !isset($globalSettings['sticky_product_bar_show_mobile']) || $globalSettings['sticky_product_bar_show_mobile'] == '1';
$spbShowTablet = !isset($globalSettings['sticky_product_bar_show_tablet']) || $globalSettings['sticky_product_bar_show_tablet'] == '1';
$spbShowDesktop = !isset($globalSettings['sticky_product_bar_show_desktop']) || $globalSettings['sticky_product_bar_show_desktop'] == '1';
$spbPosition = $globalSettings['sticky_product_bar_position'] ?? 'bottom';
$spbShowBuyNow = ($globalSettings['sticky_product_bar_show_buynow'] ?? '1') == '1';

// Collision Resolver
$displayEngineInstance = app(\App\Services\DisplayRuleEngine::class);
$allActiveWidgetsList = $displayEngineInstance->getActiveWidgets(request());
$hasRightSideWidgets = $allActiveWidgetsList->where('position', 'bottom_right')->isNotEmpty() || ($enableScrollTop && $stPosition !== 'left');
$hasLeftSideWidgets  = $allActiveWidgetsList->where('position', 'bottom_left')->isNotEmpty() || ($enableScrollTop && $stPosition === 'left');

if ($hasRightSideWidgets) {
    $spbAvoidClass = 'spb-avoid-right';
} elseif ($hasLeftSideWidgets) {
    $spbAvoidClass = 'spb-avoid-left';
} else {
    $spbAvoidClass = 'spb-full-width';
}

// Account Destination Resolver
$accountTargetUrl = route('login');
if (auth()->check()) {
    $currUser = auth()->user();
    if (method_exists($currUser, 'isStaffOrAdmin') && $currUser->isStaffOrAdmin()) {
        $accountTargetUrl = route('admin.dashboard');
    } elseif (!empty($currUser->is_super_admin) || in_array(strtolower((string)$currUser->role), ['admin', 'super_admin', 'branch_manager', 'staff'])) {
        $accountTargetUrl = route('admin.dashboard');
    } else {
        $accountTargetUrl = route('user.dashboard');
    }
}

$rawFooterItems = $globalSettings['sticky_footer_items_json'] ?? null;
$stickyFooterNavItems = !empty($rawFooterItems) ? json_decode($rawFooterItems, true) : [
    ['title' => 'Shop', 'icon' => 'fas fa-store', 'url' => '/shop', 'action_type' => 'link'],
    ['title' => 'Search', 'icon' => 'fas fa-search', 'url' => '#', 'action_type' => 'search'],
    ['title' => 'Wishlist', 'icon' => 'far fa-heart', 'url' => '/user/wishlist', 'action_type' => 'wishlist'],
    ['title' => 'Cart', 'icon' => 'fas fa-shopping-bag', 'url' => '/cart', 'action_type' => 'cart'],
    ['title' => 'Account', 'icon' => 'fas fa-user', 'url' => $accountTargetUrl, 'action_type' => 'link']
];

$cartData = session()->get('cart', []);
$totalCartCount = array_sum(array_column($cartData, 'quantity'));

// Free Shipping Resolver
$freeShippingList = [];
if (\Illuminate\Support\Facades\Schema::hasTable('shipping_methods')) {
    $methods = DB::table('shipping_methods')
        ->leftJoin('shipping_zones', 'shipping_methods.zone_id', '=', 'shipping_zones.id')
        ->where('shipping_methods.method_type', 'free_shipping')
        ->where('shipping_methods.is_enabled', 1)
        ->select(
            'shipping_methods.min_amount',
            'shipping_methods.cost',
            'shipping_methods.free_shipping_requires',
            'shipping_zones.name as zone_name'
        )
        ->get();

    foreach ($methods as $m) {
        $amount = (float) ($m->min_amount ?? $m->cost ?? 0);
        $zName = !empty($m->zone_name) ? trim($m->zone_name) : 'All Areas';
        if ($m->free_shipping_requires === 'no_requirement' || $amount <= 0) {
            $freeShippingList[] = "Free delivery available in {$zName}";
        } else {
            $freeShippingList[] = "Free delivery over ৳" . number_format($amount, 0) . " in {$zName}";
        }
    }
}

// SEO Resolver
$siteBrandName = $globalSettings['site_title'] ?? config('app.name', 'My Store');

$seoFinalTitle = !empty($globalSettings['seo_meta_title']) ? $globalSettings['seo_meta_title'] : $siteBrandName;
$seoFinalDesc = !empty($globalSettings['seo_meta_description']) ? $globalSettings['seo_meta_description'] : ($globalSettings['site_tagline'] ?? '');
$seoFinalKeywords = $globalSettings['seo_meta_keywords'] ?? '';
$seoFinalImage = cleanGlobalImageUrl($globalSettings['header_logo'] ?? '');
$seoFinalUrl = url()->current();
$seoOgType = 'website';
$seoRobots = 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1';

if (request()->routeIs('home') || request()->is('/')) {
    $seoFinalTitle = !empty($globalSettings['seo_meta_title']) ? $globalSettings['seo_meta_title'] : $siteBrandName;
    $seoFinalDesc = !empty($globalSettings['seo_meta_description']) ? $globalSettings['seo_meta_description'] : ($globalSettings['site_tagline'] ?? '');
    $seoFinalKeywords = $globalSettings['seo_meta_keywords'] ?? '';
    $seoOgType = 'website';
} elseif (request()->routeIs('shop') || request()->is('shop')) {
    $seoFinalTitle = 'Shop | ' . $siteBrandName;
    $seoFinalDesc = !empty($globalSettings['seo_meta_description']) ? $globalSettings['seo_meta_description'] : "Explore our latest products and exclusive deals at {$siteBrandName}.";
    $seoFinalKeywords = $globalSettings['seo_meta_keywords'] ?? '';
    $seoOgType = 'website';
} elseif (request()->routeIs('product.details') && isset($product) && is_object($product)) {
    $seoOgType = 'product';
    $customTitle = $product->seo_title ?? null;
    $seoFinalTitle = !empty($customTitle) ? $customTitle : ($product->title . ' | ' . $siteBrandName);

    if (!empty($product->meta_description)) {
        $seoFinalDesc = $product->meta_description;
    } elseif (!empty($product->short_description)) {
        $seoFinalDesc = \Illuminate\Support\Str::limit(strip_tags($product->short_description), 160);
    } elseif (!empty($product->description)) {
        $seoFinalDesc = \Illuminate\Support\Str::limit(strip_tags($product->description), 160);
    }

    if (!empty($product->focus_keyphrase)) {
        $seoFinalKeywords = $product->focus_keyphrase;
    }
    if (!empty($product->canonical_url)) {
        $seoFinalUrl = $product->canonical_url;
    }
    if (!empty($product->thumbnail)) {
        $seoFinalImage = cleanGlobalImageUrl($product->thumbnail);
    }
} elseif (request()->routeIs('blogs.show') && isset($post) && is_object($post)) {
    $seoOgType = 'article';
    $customPostTitle = $post->seo_title ?? ($post->meta_title ?? null);
    $seoFinalTitle = !empty($customPostTitle) ? $customPostTitle : ($post->title . ' | ' . $siteBrandName);

    $customPostDesc = $post->seo_description ?? ($post->meta_description ?? null);
    if (!empty($customPostDesc)) {
        $seoFinalDesc = $customPostDesc;
    } elseif (!empty($post->excerpt)) {
        $seoFinalDesc = \Illuminate\Support\Str::limit(strip_tags($post->excerpt), 160);
    } elseif (!empty($post->content)) {
        $seoFinalDesc = \Illuminate\Support\Str::limit(strip_tags($post->content), 160);
    }

    if (!empty($post->seo_keywords)) {
        $seoFinalKeywords = $post->seo_keywords;
    }
    if (!empty($post->featured_image)) {
        $seoFinalImage = cleanGlobalImageUrl($post->featured_image);
    }
} elseif (isset($page) && is_object($page) && !request()->routeIs('home') && !request()->routeIs('shop')) {
    $seoOgType = 'article';
    $customPageTitle = !empty($page->seo_title) ? $page->seo_title : ($page->title . ' | ' . $siteBrandName);
    $seoFinalTitle = $customPageTitle;

    $customPageDesc = !empty($page->seo_description) ? $page->seo_description : ($page->meta_description ?? null);
    if (!empty($customPageDesc)) {
        $seoFinalDesc = $customPageDesc;
    } elseif (!empty($page->content)) {
        $seoFinalDesc = \Illuminate\Support\Str::limit(strip_tags($page->content), 160);
    }

    if (!empty($page->seo_keywords)) {
        $seoFinalKeywords = $page->seo_keywords;
    }
    
    if (!empty($page->seo_canonical_url)) {
        $seoFinalUrl = $page->seo_canonical_url;
    } else {
        $seoFinalUrl = url('/' . $page->slug);
    }

    if (isset($page->allow_indexing) && $page->allow_indexing == 0) {
        $seoRobots = 'noindex, nofollow';
    }

    if (!empty($page->seo_og_image)) {
        $seoFinalImage = cleanGlobalImageUrl($page->seo_og_image);
    } elseif (!empty($page->featured_image)) {
        $seoFinalImage = cleanGlobalImageUrl($page->featured_image);
    }
}

$seoFinalDesc = trim(preg_replace('/\s+/', ' ', strip_tags(html_entity_decode($seoFinalDesc, ENT_QUOTES, 'UTF-8'))));
$seoFinalTitle = trim(strip_tags(html_entity_decode($seoFinalTitle, ENT_QUOTES, 'UTF-8')));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', $seoFinalTitle)</title>
    <meta name="title" content="@yield('meta_title', $seoFinalTitle)">
    <meta name="description" content="@yield('meta_description', $seoFinalDesc)">
    @if(!empty($seoFinalKeywords))
    <meta name="keywords" content="@yield('meta_keywords', $seoFinalKeywords)">
    @endif
    <meta name="robots" content="@yield('robots', $seoRobots)">
    <link rel="canonical" href="@yield('canonical', $seoFinalUrl)">

    <meta property="og:type" content="{{ $seoOgType }}">
    <meta property="og:url" content="@yield('canonical', $seoFinalUrl)">
    <meta property="og:title" content="@yield('meta_title', $seoFinalTitle)">
    <meta property="og:description" content="@yield('meta_description', $seoFinalDesc)">
    @if(!empty($seoFinalImage))
    <meta property="og:image" content="@yield('og_image', $seoFinalImage)">
    @endif
    <meta property="og:site_name" content="{{ $siteBrandName }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="@yield('canonical', $seoFinalUrl)">
    <meta name="twitter:title" content="@yield('meta_title', $seoFinalTitle)">
    <meta name="twitter:description" content="@yield('meta_description', $seoFinalDesc)">
    @if(!empty($seoFinalImage))
    <meta name="twitter:image" content="@yield('og_image', $seoFinalImage)">
    @endif

    @if(!empty($globalSettings['google_site_verification']))
    <meta name="google-site-verification" content="{{ $globalSettings['google_site_verification'] }}">
    @endif
    @if(!empty($globalSettings['facebook_site_verification']))
    <meta name="facebook-domain-verification" content="{{ $globalSettings['facebook_site_verification'] }}">
    @endif

    @if(!empty($globalSettings['facebook_pixel_script']))
    {!! $globalSettings['facebook_pixel_script'] !!}
    @endif

    @if(!empty($finalFavicon))
        <link rel="icon" type="image/x-icon" href="{{ $finalFavicon }}">
        <link rel="shortcut icon" type="image/x-icon" href="{{ $finalFavicon }}">
    @endif
    
    <!-- Multi-Language Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;700&family=Noto+Sans+Devanagari:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive.css') }}">

    <style>
        :root {
            --app-font-family: 'Plus Jakarta Sans', 'Hind Siliguri', 'Noto Sans Devanagari', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --site-bg-color: {{ !empty($themeSiteBg) ? $themeSiteBg : '#f8fafc' }};

            /* Dynamic Footer Row Background & Nav Colors */
            --sf-row-bg: {{ !empty($footerRowBg) ? $footerRowBg : 'transparent' }};
            --sf-bar-bg: {{ !empty($footerBg) ? $footerBg : '#ffffff' }};
            --sf-text-color: {{ !empty($footerTextColor) ? $footerTextColor : '#64748b' }};
            --sf-active-color: {{ !empty($footerActiveColor) ? $footerActiveColor : '#2563eb' }};

            /* App Sticky Single Product Bar Variables */
            --app-spb-bg: {{ $appSpbBg }};
            --app-spb-border: {{ $appSpbBorder }};
            --app-spb-title: {{ $appSpbTitle }};
            --app-spb-title-hover: {{ $appSpbTitleHover }};
            --app-spb-price: {{ $appSpbPrice }};
            --app-spb-cart-btn-bg: {{ $appSpbCartBtnBg }};
            --app-spb-cart-btn-text: {{ $appSpbCartBtnText }};
            --app-spb-cart-btn-hover-bg: {{ $appSpbCartBtnHoverBg }};
            --app-spb-buynow-bg: {{ $appSpbBuyNowBg }};
            --app-spb-buynow-text: {{ $appSpbBuyNowText }};
            --app-spb-buynow-hover-bg: {{ $appSpbBuyNowHoverBg }};
            --app-spb-buynow-hover-text: {{ $appSpbBuyNowHoverText }};

            /* App Bottom Search Modal Variables */
            --app-search-modal-bg: {{ $appSearchModalBg }};
            --app-search-modal-border: {{ $appSearchModalBorder }};
            --app-search-price: {{ $appSearchPrice }};
            --app-search-cart-btn-bg: {{ $appSearchCartBtnBg }};
            --app-search-cart-btn-text: {{ $appSearchCartBtnText }};
            --app-search-cart-btn-hover-bg: {{ $appSearchCartBtnHoverBg }};
            --app-search-cart-btn-hover-text: {{ $appSearchCartBtnHoverText }};

            /* App Drawer Cart & Quick View Variables */
            --app-drawer-bg: {{ $appDrawerBg }};
            --app-drawer-border: {{ $appDrawerBorder }};
            --app-drawer-icon: {{ $appDrawerIcon }};
            --app-drawer-total-price: {{ $appDrawerTotalPrice }};
            --app-drawer-checkout-bg: {{ $appDrawerCheckoutBg }};
            --app-drawer-checkout-text: {{ $appDrawerCheckoutText }};
            --app-drawer-checkout-hover-bg: {{ $appDrawerCheckoutHoverBg }};
            --app-drawer-checkout-hover-text: {{ $appDrawerCheckoutHoverText }};
            --app-quickview-spinner: {{ $appQuickViewSpinner }};

            /* Scroll to Top Variables */
            --app-scroll-top-bg: {{ $appScrollTopBg }};
            --app-scroll-top-color: {{ $appScrollTopColor }};
        }

        /* 1. Global / Body Background */
        body, #site-body {
            background-color: var(--site-bg-color) !important;
            font-family: var(--app-font-family);
            color: #0f172a;
            line-height: 1.55;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
        }

        /* 2. Sticky Product Bar Scoped Elements */
        .sticky-single-product-bar {
            position: fixed !important;
            z-index: 1030 !important;
            background: var(--app-spb-bg) !important;
            backdrop-filter: blur(16px) saturate(180%) !important;
            -webkit-backdrop-filter: blur(16px) saturate(180%) !important;
            display: none;
            align-items: center !important;
            justify-content: space-between !important;
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.25s ease !important;
            opacity: 0;
            visibility: hidden;
            border: 1px solid var(--app-spb-border) !important;
            border-radius: 12px !important;
            box-shadow: 0 6px 24px rgba(15, 23, 42, 0.1) !important;
            box-sizing: border-box !important;
        }

        .sticky-single-product-bar.bar-visible {
            opacity: 1 !important;
            visibility: visible !important;
            transform: translateY(0) !important;
        }

        .app-spb-title {
            color: var(--app-spb-title) !important;
            transition: color 0.15s ease;
        }
        .app-spb-title:hover {
            color: var(--app-spb-title-hover) !important;
        }
        .app-spb-price {
            color: var(--app-spb-price) !important;
        }
        .app-spb-cart-icon {
            color: var(--app-spb-cart-btn-text) !important;
        }
        #spb-add-cart-btn.btn-spb-cart {
            background-color: var(--app-spb-cart-btn-bg) !important;
            color: var(--app-spb-cart-btn-text) !important;
            border: 1px solid var(--app-spb-border) !important;
            transition: all 0.2s ease;
        }
        #spb-add-cart-btn.btn-spb-cart:hover {
            background-color: var(--app-spb-cart-btn-hover-bg) !important;
        }
        .app-spb-buynow-btn {
            background-color: var(--app-spb-buynow-bg) !important;
            border-color: var(--app-spb-buynow-bg) !important;
            color: var(--app-spb-buynow-text) !important;
            transition: all 0.2s ease;
        }
        .app-spb-buynow-btn:hover {
            background-color: var(--app-spb-buynow-hover-bg) !important;
            border-color: var(--app-spb-buynow-hover-bg) !important;
            color: var(--app-spb-buynow-hover-text) !important;
        }

        /* 3. Bottom Search Modal Scoped Elements */
        .bottom-nav-search-modal .modal-content {
            background-color: var(--app-search-modal-bg) !important;
            border: 1px solid var(--app-search-modal-border) !important;
        }
        .app-search-item-price {
            color: var(--app-search-price) !important;
        }
        .app-search-item-cart-btn {
            background-color: var(--app-search-cart-btn-bg) !important;
            border-color: var(--app-search-cart-btn-bg) !important;
            color: var(--app-search-cart-btn-text) !important;
            transition: all 0.2s ease;
        }
        .app-search-item-cart-btn:hover {
            background-color: var(--app-search-cart-btn-hover-bg) !important;
            border-color: var(--app-search-cart-btn-hover-bg) !important;
            color: var(--app-search-cart-btn-hover-text) !important;
        }

        /* 4. Drawer Cart & Quick View Scoped Elements */
        #sideDrawerCart {
            width: 380px;
            max-width: 100vw;
            background-color: var(--app-drawer-bg) !important;
            border-left: 1px solid var(--app-drawer-border) !important;
            box-shadow: -10px 0 35px rgba(15, 23, 42, 0.08);
        }

        .app-quickview-spinner {
            color: var(--app-quickview-spinner) !important;
        }
        .app-drawer-cart-icon {
            color: var(--app-drawer-icon) !important;
        }
        .app-drawer-spinner {
            color: var(--app-drawer-icon) !important;
        }
        .app-drawer-checkout-btn {
            background-color: var(--app-drawer-checkout-bg) !important;
            border-color: var(--app-drawer-checkout-bg) !important;
            color: var(--app-drawer-checkout-text) !important;
            transition: all 0.2s ease;
        }
        .app-drawer-checkout-btn:hover {
            background-color: var(--app-drawer-checkout-hover-bg) !important;
            border-color: var(--app-drawer-checkout-hover-bg) !important;
            color: var(--app-drawer-checkout-hover-text) !important;
        }
        #sideDrawerCartFooter #sideDrawerCartTotal {
            font-size: 18px !important;
            font-weight: 700 !important;
            color: var(--app-drawer-total-price) !important;
            white-space: nowrap;
        }

        .bn-digit, 
        .product-price-val, 
        .spb-price, 
        #sideDrawerCartSubtotal, 
        #sideDrawerCartTotal,
        .cart-drawer-count,
        .wishlist-badge-count {
            font-weight: 700 !important;
        }

        @media (max-width: 575.98px) {
            #sideDrawerCart {
                width: 86vw !important;
                max-width: 86vw !important;
            }
        }

        #sideDrawerCart .offcanvas-footer,
        #sideDrawerCartFooter {
            padding: 16px !important;
            background-color: var(--app-drawer-bg) !important;
            border-top: 1px solid var(--app-drawer-border);
            box-sizing: border-box !important;
            width: 100% !important;
        }

        #sideDrawerCartFooter .drawer-price-row {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            padding: 3px 0 !important;
            width: 100% !important;
            margin-bottom: 8px;
        }

        #sideDrawerCartFooter .drawer-price-row span.text-secondary {
            font-size: 13.5px !important;
            font-weight: 500;
            color: #64748b !important;
        }

        #sideDrawerCartFooter #sideDrawerCartSubtotal {
            font-size: 14.5px !important;
            font-weight: 700 !important;
            color: #0f172a !important;
            white-space: nowrap;
        }

        #sideDrawerCartFooter .btn-dark {
            font-size: 13.5px !important;
            padding: 11px 0 !important;
            border-radius: 10px !important;
            font-weight: 700 !important;
            letter-spacing: 0.3px;
            transition: all 0.2s ease;
        }

        .master-site-header {
            @if($isStickyHeader)
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                right: 0 !important;
                width: 100% !important;
                z-index: 1040 !important;
            @else
                position: relative !important;
                z-index: 1040 !important;
            @endif

            @if($isGlassHeader)
                background-color: rgba(255, 255, 255, 0.85) !important;
                backdrop-filter: blur(16px) saturate(180%) !important;
                -webkit-backdrop-filter: blur(16px) saturate(180%) !important;
                border-bottom: 1px solid rgba(226, 232, 240, 0.8) !important;
                box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04) !important;
            @else
                background-color: #ffffff !important;
            @endif
        }

        @if($isStickyHeader)
            .header-spacer {
                display: block;
                height: 148px;
            }
            @media (max-width: 991.98px) {
                .header-spacer { height: 118px; }
            }
        @else
            .header-spacer { display: none; }
        @endif

        /* ========================================================================= */
        /* STICKY FOOTER DYNAMIC ROW & INNER BAR STYLING                             */
        /* ========================================================================= */
        .site-sticky-footer-wrapper {
            position: fixed !important;
            bottom: 0 !important;
            left: 0 !important;
            right: 0 !important;
            width: 100% !important;
            z-index: 1035 !important;
            pointer-events: none !important;
            background-color: var(--sf-row-bg) !important;
            display: none;
            justify-content: center !important;
            align-items: center !important;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s ease !important;
        }

        .site-sticky-footer-wrapper.sf-hidden {
            transform: translateY(100%) !important;
            opacity: 0 !important;
        }

        .site-sticky-bottom-bar {
            pointer-events: auto !important;
            display: flex !important;
            justify-content: space-around !important;
            align-items: center !important;
            padding: 8px 12px !important;
            box-sizing: border-box !important;
            width: 100% !important;

            @if($footerGlass)
                background-color: {{ $footerBg }}e6 !important;
                backdrop-filter: blur(16px) !important;
                -webkit-backdrop-filter: blur(16px) !important;
                border-top: 1px solid rgba(226, 232, 240, 0.6) !important;
                box-shadow: 0 -4px 20px rgba(15, 23, 42, 0.06) !important;
            @else
                background-color: var(--sf-bar-bg) !important;
                border-top: 1px solid #e2e8f0 !important;
                box-shadow: 0 -4px 16px rgba(15, 23, 42, 0.06) !important;
            @endif
        }

        @if($enableStickyFooter)
            @if($showOnMobile)
                @media (max-width: 767.98px) {
                    .site-sticky-footer-wrapper {
                        display: flex !important;
                    }
                    .site-sticky-bottom-bar {
                        border-radius: 0 !important;
                    }
                    body { padding-bottom: 64px !important; }
                }
            @endif

            @if($showOnTablet)
                @media (min-width: 768px) and (max-width: 991.98px) {
                    .site-sticky-footer-wrapper {
                        display: flex !important;
                    }
                    .site-sticky-bottom-bar {
                        border-radius: 0 !important;
                    }
                    body { padding-bottom: 68px !important; }
                }
            @endif

            @if($showOnDesktop)
                @media (min-width: 992px) {
                    .site-sticky-footer-wrapper {
                        display: flex !important;
                        padding: 6px 0 !important;
                    }
                    .site-sticky-bottom-bar {
                        @if($desktopStyle === 'full_width')
                            border-radius: 0 !important;
                            max-width: 100% !important;
                        @else
                            max-width: 620px !important;
                            border-radius: 14px !important;
                            border: 1px solid #e2e8f0 !important;
                            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.08) !important;
                        @endif
                    }
                    body { padding-bottom: 74px !important; }
                }
            @endif
        @endif

        .sticky-footer-btn {
            text-decoration: none !important;
            color: var(--sf-text-color) !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            font-size: 11px !important;
            font-weight: 600 !important;
            position: relative !important;
            cursor: pointer !important;
            background: transparent !important;
            border: none !important;
            padding: 4px 8px !important;
            transition: color 0.2s ease, transform 0.2s ease;
        }
        .sticky-footer-btn:hover, .sticky-footer-btn.active {
            color: var(--sf-active-color) !important;
            transform: translateY(-1px);
        }
        .sticky-footer-btn i {
            font-size: 17px !important;
            margin-bottom: 3px !important;
        }
        .sticky-footer-badge {
            position: absolute !important;
            top: -2px !important;
            right: 6px !important;
            background: #ef4444 !important;
            color: #ffffff !important;
            font-size: 9.5px !important;
            padding: 1px 5px !important;
            border-radius: 9999px !important;
            font-weight: 700 !important;
            line-height: 1.2;
        }

        @if($spbPosition === 'top')
            .sticky-single-product-bar {
                top: {{ $isStickyHeader ? '110px' : '0' }} !important;
                transform: translateY(-10px);
            }
            @media (min-width: 992px) {
                .sticky-single-product-bar { top: {{ $isStickyHeader ? '135px' : '0' }} !important; }
            }
        @else
            .sticky-single-product-bar {
                bottom: {{ $enableStickyFooter ? '72px' : '14px' }} !important;
                transform: translateY(10px);
            }
            @media (min-width: 992px) {
                .sticky-single-product-bar {
                    bottom: {{ $enableStickyFooter ? '78px' : '16px' }} !important;
                }
            }
        @endif

        /* Avoid Floating Widget Collision */
        .sticky-single-product-bar.spb-avoid-right {
            left: 10px !important;
            right: 66px !important;
            width: auto !important;
        }
        @media (min-width: 768px) {
            .sticky-single-product-bar.spb-avoid-right {
                left: 20px !important;
                right: 76px !important;
                max-width: 1200px !important;
                margin-left: auto !important;
            }
        }

        .sticky-single-product-bar.spb-avoid-left {
            left: 66px !important;
            right: 10px !important;
            width: auto !important;
        }
        @media (min-width: 768px) {
            .sticky-single-product-bar.spb-avoid-left {
                left: 76px !important;
                right: 20px !important;
                max-width: 1200px !important;
                margin-right: auto !important;
            }
        }

        .sticky-single-product-bar.spb-full-width {
            left: 8px !important;
            right: 8px !important;
            width: auto !important;
        }
        @media (min-width: 992px) {
            .sticky-single-product-bar.spb-full-width {
                left: 0 !important;
                right: 0 !important;
                width: 100% !important;
                border-radius: 0 !important;
            }
        }

        @media (max-width: 767.98px) {
            .sticky-single-product-bar { 
                bottom: {{ $enableStickyFooter ? '72px' : '14px' }} !important;
                padding: 7px 10px !important; 
                min-height: 50px !important;
            }
            .spb-container { 
                width: 100%; 
                display: flex; 
                align-items: center; 
                justify-content: space-between; 
                gap: 8px; 
            }
            .spb-thumb { 
                width: 36px !important; 
                height: 36px !important; 
                border-radius: 7px !important; 
            }
            .spb-title { 
                font-size: 11px !important; 
                max-width: 115px !important; 
                line-height: 1.25 !important; 
            }
            .spb-price { 
                font-size: 11.5px !important; 
            }
            .spb-btn-group {
                gap: 8px !important;
            }
            .btn-spb { 
                font-size: 10px !important; 
                padding: 6px 9px !important; 
                border-radius: 6px !important; 
                letter-spacing: -0.01em;
            }
            .btn-spb i {
                font-size: 11px !important;
            }
        }

        @media (min-width: 768px) {
            .sticky-single-product-bar { padding: 10px 20px !important; }
            .spb-container { max-width: 1200px; margin: 0 auto; width: 100%; display: flex; align-items: center; justify-content: space-between; }
            .spb-thumb { width: 44px !important; height: 44px !important; border-radius: 8px !important; }
            .spb-title { font-size: 13.5px !important; max-width: 360px !important; }
            .spb-price { font-size: 14.5px !important; }
            .spb-btn-group { gap: 10px !important; }
            .btn-spb { font-size: 12.5px !important; padding: 7px 16px !important; border-radius: 8px !important; }
        }

        @if($enableStickyProductBar)
            @if($spbShowMobile) @media (max-width: 767.98px) { .sticky-single-product-bar { display: flex !important; } } @endif
            @if($spbShowTablet) @media (min-width: 768px) and (max-width: 991.98px) { .sticky-single-product-bar { display: flex !important; } } @endif
            @if($spbShowDesktop) @media (min-width: 992px) { .sticky-single-product-bar { display: flex !important; } } @endif
        @endif

        /* Back to Top Button */
        .btn-scroll-to-top {
            position: fixed !important;
            z-index: 1045 !important;
            width: 44px !important;
            height: 44px !important;
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border-radius: 50% !important;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.2) !important;
            border: 2px solid #ffffff !important;
            font-size: 16px !important;
            outline: none !important;
            opacity: 0;
            visibility: hidden;
            bottom: {{ $enableStickyFooter ? '83px' : '21px' }} !important;
            background-color: var(--app-scroll-top-bg) !important;
            color: var(--app-scroll-top-color) !important;
            transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1),
                        box-shadow 0.22s ease,
                        opacity 0.25s ease,
                        bottom 0.28s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }

        .btn-scroll-to-top.active-visible {
            opacity: 1 !important;
            visibility: visible !important;
        }

        .btn-scroll-to-top:hover,
        .btn-scroll-to-top:active {
            transform: scale(1.1) translateY(-2px) !important;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.32) !important;
            filter: brightness(1.1);
        }

        @if($stPosition === 'left')
            .btn-scroll-to-top { left: 18px !important; right: auto !important; }
        @else
            .btn-scroll-to-top { right: 18px !important; left: auto !important; }
        @endif

        @media (max-width: 767.98px) {
            .btn-scroll-to-top {
                bottom: {{ $enableStickyFooter ? '76px' : '18px' }} !important;
                width: 42px !important;
                height: 42px !important;
                font-size: 15px !important;
            }
            @if($stPosition === 'left')
                .btn-scroll-to-top { left: 14px !important; }
            @else
                .btn-scroll-to-top { right: 14px !important; }
            @endif
        }

        @if($enableScrollTop)
            @if($stShowMobile) @media (max-width: 767.98px) { .btn-scroll-to-top { display: inline-flex !important; } } @endif
            @if($stShowTablet) @media (min-width: 768px) and (max-width: 991.98px) { .btn-scroll-to-top { display: inline-flex !important; } } @endif
            @if($stShowDesktop) @media (min-width: 992px) { .btn-scroll-to-top { display: inline-flex !important; } } @endif
        @endif
    </style>
</head>
<body id="site-body" class="d-flex flex-column min-vh-100">

    @include('layouts.header')

    <main id="main-content-wrapper" style="flex: 1 0 auto;">
        @yield('content')
    </main>

    @include('layouts.footer')

    {{-- Sticky Footer Nav with Outer Row Background Wrapper --}}
    @if($enableStickyFooter)
        <div class="site-sticky-footer-wrapper" id="siteStickyFooterWrapper">
            <div class="site-sticky-bottom-bar" id="siteStickyBottomBar">
                @foreach($stickyFooterNavItems as $navItem)
                    @php
                        $actType = $navItem['action_type'] ?? 'link';
                        $targetUrl = $navItem['url'] ?? '#';
                        $btnClass = '';
                        $clickAttr = '';

                        if ($actType === 'link' && strtolower($navItem['title'] ?? '') === 'account') {
                            $targetUrl = $accountTargetUrl;
                        }

                        if ($actType === 'cart') {
                            $btnClass = $enableFloatingCart ? 'btn-open-cart-drawer' : '';
                            if (!$enableFloatingCart) {
                                $targetUrl = route('cart.index');
                            }
                        } elseif ($actType === 'search') {
                            $clickAttr = "onclick=\"event.preventDefault(); window.openBottomSearchModal();\"";
                        }
                    @endphp
                    <a href="{{ $targetUrl }}" class="sticky-footer-btn {{ $btnClass }}" {!! $clickAttr !!}>
                        <i class="{{ $navItem['icon'] ?? 'fas fa-circle' }}"></i>
                        <span>{{ $navItem['title'] ?? '' }}</span>
                        @if($actType === 'cart')
                            <span class="sticky-footer-badge cart-drawer-count {{ $totalCartCount > 0 ? '' : 'd-none' }}">{{ $totalCartCount }}</span>
                        @elseif($actType === 'wishlist')
                            <span class="sticky-footer-badge wishlist-badge-count {{ $wishlistCount > 0 ? '' : 'd-none' }}">{{ $wishlistCount }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Sticky Product Bar with Gap and Collision-Free Class --}}
    @if($enableStickyProductBar)
        <div id="stickySingleProductBar" class="sticky-single-product-bar {{ $spbAvoidClass }}">
            <div class="spb-container">
                <div class="d-flex align-items-center gap-2 gap-sm-3 min-w-0">
                    <img id="spb-img" src="https://placehold.co/80x80?text=Product" alt="Product" class="border flex-shrink-0 bg-light spb-thumb" style="object-fit: cover;">
                    <div class="min-w-0">
                        <h6 id="spb-title" class="fw-bold text-dark text-truncate mb-0 spb-title app-spb-title">Product Title</h6>
                        <div class="d-flex align-items-center gap-2 mt-0">
                            <span id="spb-price" class="fw-bold spb-price app-spb-price">৳0.00</span>
                            <del id="spb-old-price" class="text-muted small d-none" style="font-size: 11px;"></del>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center flex-shrink-0 spb-btn-group">
                    <button type="button" id="spb-add-cart-btn" class="btn btn-spb btn-spb-cart d-flex align-items-center justify-content-center shadow-sm">
                        <i class="fas fa-shopping-cart app-spb-cart-icon"></i>
                        <span class="d-none d-sm-inline ms-1">Add to Cart</span>
                    </button>
                    @if($spbShowBuyNow)
                        <button type="button" id="spb-buy-now-btn" class="btn fw-bold btn-spb app-spb-buynow-btn d-flex align-items-center justify-content-center shadow-sm">
                            <i class="fas fa-bolt text-warning me-1"></i>
                            <span>Buy Now</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if($enableScrollTop)
        <button type="button" id="btnScrollToTop" class="btn-scroll-to-top" title="Back to top">
            <i class="fas fa-chevron-up"></i>
        </button>
    @endif

    <!-- Search Modal -->
    <div class="modal fade bottom-nav-search-modal" id="bottomNavSearchModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
            <div class="modal-content overflow-hidden border-0 shadow-lg rounded-4">
                <div class="modal-header border-bottom py-2.5 px-3 bg-light d-flex justify-content-between align-items-center">
                    <span class="text-uppercase small fw-bold text-muted" style="letter-spacing: 0.6px; font-size: 11px;">Search Catalog</span>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="d-flex align-items-center border rounded-3 px-3 py-2 bg-white mb-2 shadow-sm">
                        <input type="text" id="modalAjaxSearchInput" class="form-control p-0 border-0 shadow-none" placeholder="Type product name, model or SKU..." autocomplete="off">
                        <i class="fas fa-search text-muted ms-2"></i>
                    </div>
                    <div id="modalSearchResultsList" class="mt-2 overflow-y-auto border rounded-3 bg-white d-none shadow-sm" style="max-height: 380px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick View Modal -->
    <div class="modal fade" id="globalQuickViewModal" tabindex="-1" aria-hidden="true" style="z-index: 99999;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4 border-0 shadow-lg" id="globalQuickViewContent" style="background: #ffffff; min-height: 250px;">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary app-quickview-spinner"></div>
                    <div class="small text-muted mt-2 fw-semibold">Loading product preview...</div>
                </div>
            </div>
        </div>
    </div>

    @if($enableFloatingCart)
        <!-- Drawer Cart -->
        <div class="offcanvas offcanvas-end border-0" tabindex="-1" id="sideDrawerCart" aria-labelledby="sideDrawerCartLabel" style="z-index: 105000;">
            <div class="offcanvas-header bg-white border-bottom py-3 px-3">
                <h6 class="offcanvas-title fw-bold text-dark mb-0 d-flex align-items-center gap-2" id="sideDrawerCartLabel" style="font-size: 14px;">
                    <i class="fas fa-shopping-bag app-drawer-cart-icon"></i> Shopping Cart (<span class="cart-drawer-count">{{ $totalCartCount }}</span>)
                </h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas"></button>
            </div>

            @if(!empty($freeShippingList) && count($freeShippingList) > 0)
                <div class="p-2.5 px-3 bg-light border-bottom" id="drawer-shipping-notice-wrap">
                    <div id="drawer-shipping-notice" class="drawer-delivery-badge p-2 rounded-2 text-center fw-semibold text-dark d-flex flex-column gap-1" style="font-size: 11.5px; background: #e0f2fe; color: #0369a1 !important;">
                        @foreach($freeShippingList as $fNotice)
                            <div>
                                <i class="fas fa-truck-moving me-1"></i> {{ $fNotice }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="offcanvas-body p-2 p-sm-3 overflow-y-auto" id="sideDrawerCartBody" style="max-height: calc(100vh - 240px);">
                <div class="text-center py-5 my-auto">
                    <div class="spinner-border app-drawer-spinner"></div>
                </div>
            </div>

            <div class="offcanvas-footer" id="sideDrawerCartFooter" style="{{ $totalCartCount > 0 ? 'display: block;' : 'display: none !important;' }}">
                <div class="drawer-price-row">
                    <span class="text-secondary">Subtotal</span>
                    <strong id="sideDrawerCartSubtotal">৳0.00</strong>
                </div>

                <div class="drawer-price-row mb-3">
                    <span class="text-secondary fw-bold">Total</span>
                    <strong id="sideDrawerCartTotal">৳0.00</strong>
                </div>

                <div class="d-flex flex-column gap-2">
                    <a href="{{ route('checkout') }}" class="btn fw-bold py-2.5 rounded-3 text-center shadow-sm w-100 app-drawer-checkout-btn">
                        Checkout Now <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                    <button type="button" class="btn btn-link text-muted small text-decoration-none text-center p-0" data-bs-dismiss="offcanvas">
                        Continue Shopping
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Global Floating Toast Notification -->
    <div id="global-cart-toast" class="toast align-items-center text-bg-dark border-0 position-fixed bottom-0 end-0 m-3 shadow-lg rounded-3" style="z-index: 999999; display: none;" role="alert">
        <div class="d-flex p-1">
            <div class="toast-body d-flex align-items-center gap-2">
                <i id="global-toast-icon" class="fas fa-check-circle text-success fs-5"></i>
                <span id="global-toast-message">Product added to cart!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="document.getElementById('global-cart-toast').style.setProperty('display', 'none', 'important')"></button>
        </div>
    </div>

    {{-- Dynamic Popups & Floating Contact Action Widgets Integration --}}
    @include('frontend.partials.dynamic_popups')
    @include('frontend.partials.floating_widgets')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>

<script>
window.openBottomSearchModal = function() {
    const sModalEl = document.getElementById('bottomNavSearchModal');
    if (!sModalEl) return;
    bootstrap.Modal.getOrCreateInstance(sModalEl).show();
    setTimeout(() => {
        document.getElementById('modalAjaxSearchInput')?.focus();
    }, 200);
};

function changeModalSearchQty(id, step) {
    const qtySpan = document.getElementById(`modal-search-qty-${id}`);
    if (!qtySpan) return;
    let cur = parseInt(qtySpan.innerText) || 1;
    let next = cur + step;
    if (next >= 1) qtySpan.innerText = next;
}

function addModalSearchItemToCart(id) {
    const qty = parseInt(document.getElementById(`modal-search-qty-${id}`)?.innerText) || 1;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    fetch(`{{ url('/cart/add') }}/${id}`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ quantity: qty })
    }).then(res => res.json()).then(data => {
        if (data.status === 'success') {
            if (window.updateAllCartBadges) window.updateAllCartBadges(data.cart_count);
            bootstrap.Modal.getInstance(document.getElementById('bottomNavSearchModal'))?.hide();
            @if($enableFloatingCart) window.openFloatingCartDrawer(); @else window.showGlobalToast(data.message, true); @endif
        }
    });
}

document.addEventListener('click', function(e) {
    const trigger = e.target.closest('.btn-open-cart-drawer');
    if (trigger) {
        @if($enableFloatingCart)
            e.preventDefault();
            window.openFloatingCartDrawer();
        @endif
    }
});

window.triggerQuickViewModal = function(productId) {
    const modalEl = document.getElementById('globalQuickViewModal');
    const content = document.getElementById('globalQuickViewContent');

    if (!modalEl || !content) return;

    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();

    content.innerHTML = `
        <div class="modal-body p-5 text-center">
            <div class="spinner-border text-primary app-quickview-spinner mb-2" role="status"></div>
            <div class="small text-muted fw-bold">Loading product preview...</div>
        </div>
    `;

    fetch("{{ url('/ajax/quick-view') }}/" + productId, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html, application/xhtml+xml'
        }
    })
    .then(res => {
        if (!res.ok) throw new Error('HTTP Status: ' + res.status);
        return res.text();
    })
    .then(html => {
        content.innerHTML = html;
        const scripts = content.querySelectorAll('script');
        scripts.forEach(oldScript => {
            const newScript = document.createElement('script');
            Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
            newScript.appendChild(document.createTextNode(oldScript.innerHTML));
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });
    })
    .catch(err => {
        console.error('Quick view error:', err);
        content.innerHTML = `
            <div class="modal-body p-4 text-center">
                <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                <div class="fw-bold text-dark mb-1">Failed to load product preview</div>
                <small class="text-muted">Please refresh the page and try again.</small>
                <div class="mt-3">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        `;
    });
};

window.openProductQuickView = function(productId) {
    window.triggerQuickViewModal(productId);
};

window.openFloatingCartDrawer = function() {
    @if($enableFloatingCart)
        const drawerEl = document.getElementById('sideDrawerCart');
        if (!drawerEl) return;
        bootstrap.Offcanvas.getOrCreateInstance(drawerEl).show();
        window.refreshDrawerCartContent();
    @else
        window.location.href = "{{ route('cart.index') }}";
    @endif
};

window.refreshDrawerCartContent = function() {
    const body = document.getElementById('sideDrawerCartBody');
    const footer = document.getElementById('sideDrawerCartFooter');
    if (!body) return;

    fetch("{{ route('cart.index') }}", {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            body.innerHTML = data.html;
            window.updateAllCartBadges(data.cart_count);
            
            const subtotalEl = document.getElementById('sideDrawerCartSubtotal');
            const totalEl = document.getElementById('sideDrawerCartTotal');
            if (subtotalEl) subtotalEl.innerText = '৳' + data.subtotal;
            if (totalEl) totalEl.innerText = '৳' + data.grand_total;

            if (footer) {
                if (parseInt(data.cart_count) > 0) {
                    footer.style.setProperty('display', 'block', 'important');
                } else {
                    footer.style.setProperty('display', 'none', 'important');
                }
            }
        }
    });
};

window.updateCartDrawerQty = function(cartKey, type) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    fetch("{{ route('cart.updateQty') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ cart_key: cartKey, type: type })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('sideDrawerCartBody').innerHTML = data.html;
            window.updateAllCartBadges(data.cart_count);
            const subtotalEl = document.getElementById('sideDrawerCartSubtotal');
            const totalEl = document.getElementById('sideDrawerCartTotal');
            if (subtotalEl) subtotalEl.innerText = '৳' + data.subtotal;
            if (totalEl) totalEl.innerText = '৳' + data.grand_total;

            const footer = document.getElementById('sideDrawerCartFooter');
            if (footer) {
                if (parseInt(data.cart_count) > 0) {
                    footer.style.setProperty('display', 'block', 'important');
                } else {
                    footer.style.setProperty('display', 'none', 'important');
                }
            }
        }
    });
};

window.removeCartDrawerItem = function(cartKey) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    fetch("{{ route('cart.remove') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ cart_key: cartKey })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('sideDrawerCartBody').innerHTML = data.html;
            window.updateAllCartBadges(data.cart_count);
            const subtotalEl = document.getElementById('sideDrawerCartSubtotal');
            const totalEl = document.getElementById('sideDrawerCartTotal');
            if (subtotalEl) subtotalEl.innerText = '৳' + data.subtotal;
            if (totalEl) totalEl.innerText = '৳' + data.grand_total;

            const footer = document.getElementById('sideDrawerCartFooter');
            if (footer) {
                if (parseInt(data.cart_count) > 0) {
                    footer.style.setProperty('display', 'block', 'important');
                } else {
                    footer.style.setProperty('display', 'none', 'important');
                }
            }
        }
    });
};

window.updateAllCartBadges = function(count) {
    const countNum = parseInt(count) || 0;
    const badges = document.querySelectorAll('#cart-counter-badge, .cart-badge-count, .cart-drawer-count, .sticky-footer-badge.cart-drawer-count');
    badges.forEach(badge => {
        badge.innerText = countNum;
        if (countNum > 0) {
            badge.style.setProperty('display', 'inline-block', 'important');
            badge.classList.remove('d-none');
        } else {
            badge.style.setProperty('display', 'none', 'important');
            badge.classList.add('d-none');
        }
    });
};

window.updateAllWishlistBadges = function(count) {
    const countNum = parseInt(count) || 0;
    const badges = document.querySelectorAll('#wishlist-counter-badge, .wishlist-badge-count, #wishlist-count, .sticky-footer-badge.wishlist-badge-count');
    badges.forEach(badge => {
        badge.innerText = countNum;
        if (countNum > 0) {
            badge.style.setProperty('display', 'inline-block', 'important');
            badge.classList.remove('d-none');
        } else {
            badge.style.setProperty('display', 'none', 'important');
            badge.classList.add('d-none');
        }
    });
};

document.addEventListener('DOMContentLoaded', () => {
    @if($enableStickyProductBar)
        const spBar = document.getElementById('stickySingleProductBar');
        const mainCartForm = document.getElementById('single-product-order-form') || document.querySelector('form.form-ajax-cart') || document.querySelector('form[action*="cart/add"]');

        if (spBar && mainCartForm) {
            const titleEl = document.getElementById('product-title-heading') || document.querySelector('h1');
            
            function extractRealPrice() {
                const activePriceEl = document.getElementById('product-active-price');
                if (activePriceEl) {
                    let pText = activePriceEl.innerText.trim();
                    if (pText) return pText;
                }

                const selectors = ['.fs-4.fw-bold.text-danger', '.current-price', '.product-price-current'];
                for (let sel of selectors) {
                    const el = document.querySelector(sel);
                    if (el && el.innerText.trim()) {
                        let txt = el.innerText.trim();
                        if (txt.includes('৳') || txt.includes('Tk') || /\d/.test(txt)) {
                            return txt;
                        }
                    }
                }
                return '';
            }

            function extractRealImage() {
                const mainImgEl = document.getElementById('product-main-display-image');
                if (mainImgEl && mainImgEl.src) return mainImgEl.src;

                const imgSelectors = ['#product-main-preview-box img', '.product-main-image img', 'main img'];
                for (let sel of imgSelectors) {
                    const img = document.querySelector(sel);
                    if (img && img.src && img.src.startsWith('http')) return img.src;
                }
                return '';
            }

            const oldPriceEl = document.querySelector('.text-muted.text-decoration-line-through');
            const pTitle = titleEl ? titleEl.innerText.trim() : '';
            const pPrice = extractRealPrice();
            const pOldPrice = oldPriceEl ? oldPriceEl.innerText.trim() : '';
            const pImg = extractRealImage();

            if (pTitle) {
                document.getElementById('spb-title').innerText = pTitle;
                if (pPrice) {
                    document.getElementById('spb-price').innerText = pPrice;
                }
                if (pImg) {
                    const spbImg = document.getElementById('spb-img');
                    spbImg.src = pImg;
                    spbImg.onerror = () => { spbImg.src = 'https://placehold.co/80x80?text=Product'; };
                }
                if (pOldPrice && pOldPrice !== pPrice) {
                    const delEl = document.getElementById('spb-old-price');
                    delEl.innerText = pOldPrice;
                    delEl.classList.remove('d-none');
                }

                document.getElementById('spb-add-cart-btn')?.addEventListener('click', () => {
                    const submitBtn = mainCartForm.querySelector('button[value="add_to_cart"]') || mainCartForm.querySelector('button[type="submit"]:not([value="order_now"])');
                    if (submitBtn) submitBtn.click();
                    else mainCartForm.requestSubmit();
                });

                document.getElementById('spb-buy-now-btn')?.addEventListener('click', () => {
                    const buyBtn = mainCartForm.querySelector('button[value="order_now"]');
                    if (buyBtn) buyBtn.click();
                    else {
                        const hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = 'action';
                        hidden.value = 'order_now';
                        mainCartForm.appendChild(hidden);
                        mainCartForm.requestSubmit();
                    }
                });

                let ticking = false;
                window.addEventListener('scroll', () => {
                    if (!ticking) {
                        window.requestAnimationFrame(() => {
                            const rect = mainCartForm.getBoundingClientRect();
                            if (rect.top < 0) {
                                spBar.classList.add('bar-visible');
                            } else {
                                spBar.classList.remove('bar-visible');
                            }
                            ticking = false;
                        });
                        ticking = true;
                    }
                }, { passive: true });
            }
        }
    @endif

    @if($enableScrollTop)
        const btnScroll = document.getElementById('btnScrollToTop');
        if (btnScroll) {
            window.addEventListener('scroll', () => {
                if (window.scrollY > 250) btnScroll.classList.add('active-visible');
                else btnScroll.classList.remove('active-visible');
            }, { passive: true });

            btnScroll.addEventListener('click', () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }
    @endif

    const modalSearchInput = document.getElementById('modalAjaxSearchInput');
    const modalResultsList = document.getElementById('modalSearchResultsList');
    let mSearchTimer;

    if (modalSearchInput) {
        modalSearchInput.addEventListener('input', function() {
            clearTimeout(mSearchTimer);
            const q = this.value.trim();
            if (q.length < 2) {
                modalResultsList.innerHTML = '';
                modalResultsList.classList.add('d-none');
                return;
            }

            mSearchTimer = setTimeout(() => {
                fetch(`{{ route('api.liveSearch') }}?query=${encodeURIComponent(q)}&category_id=all`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success' && data.products && data.products.length > 0) {
                            modalResultsList.innerHTML = data.products.map(p => `
                                <div class="d-flex align-items-center justify-content-between p-2.5 border-bottom">
                                    <a href="${p.url}" class="d-flex align-items-center gap-2.5 text-decoration-none text-dark flex-grow-1 min-w-0 pe-2">
                                        <img src="${p.image}" alt="${p.title}" class="rounded-2 border flex-shrink-0" style="width: 44px; height: 44px; object-fit: contain;">
                                        <div class="text-truncate">
                                            <div class="fw-semibold text-truncate small">${p.title}</div>
                                            <div class="d-flex align-items-center gap-2 mt-1">
                                                <span class="fw-bold text-primary small bn-digit app-search-item-price">৳${p.price}</span>
                                                ${p.sku ? `<span class="badge bg-light text-muted border px-2 py-0.5" style="font-size: 10px;">SKU: ${p.sku}</span>` : ''}
                                            </div>
                                        </div>
                                    </a>
                                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                        <div class="d-inline-flex align-items-center bg-light border rounded-2 px-1">
                                            <button type="button" class="btn btn-sm p-0 px-1 border-0" onclick="changeModalSearchQty(${p.id}, -1)">-</button>
                                            <span id="modal-search-qty-${p.id}" class="small fw-bold px-1.5 bn-digit">1</span>
                                            <button type="button" class="btn btn-sm p-0 px-1 border-0" onclick="changeModalSearchQty(${p.id}, 1)">+</button>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-primary rounded-2 px-2.5 py-1 app-search-item-cart-btn" onclick="addModalSearchItemToCart(${p.id})">
                                            <i class="fas fa-shopping-cart"></i>
                                        </button>
                                    </div>
                                </div>
                            `).join('');
                            modalResultsList.classList.remove('d-none');
                        } else {
                            modalResultsList.innerHTML = `<div class="p-3 text-center text-muted small">No products found matching "${q}"</div>`;
                            modalResultsList.classList.remove('d-none');
                        }
                    });
            }, 250);
        });
    }

    @if($enableStickyFooter && $footerHideOnScroll)
        let lastScrollY = window.scrollY;
        const stickyNavWrapper = document.getElementById('siteStickyFooterWrapper') || document.getElementById('siteStickyBottomBar');
        window.addEventListener('scroll', () => {
            if (!stickyNavWrapper) return;
            if (window.scrollY > lastScrollY && window.scrollY > 80) {
                stickyNavWrapper.classList.add('sf-hidden');
            } else {
                stickyNavWrapper.classList.remove('sf-hidden');
            }
            lastScrollY = window.scrollY;
        }, { passive: true });
    @endif
});

window.showGlobalToast = function(message, isSuccess = true) {
    const toastEl = document.getElementById('global-cart-toast');
    const toastMsg = document.getElementById('global-toast-message');
    const toastIcon = document.getElementById('global-toast-icon');

    if (toastEl && toastMsg) {
        toastMsg.innerText = message;
        if (toastIcon) {
            toastIcon.className = isSuccess ? 'fas fa-check-circle text-success fs-5' : 'fas fa-info-circle text-warning fs-5';
        }
        toastEl.style.setProperty('display', 'block', 'important');
        setTimeout(() => toastEl.style.setProperty('display', 'none', 'important'), 3000);
    }
};
</script>
</body>
</html>