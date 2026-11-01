@php
$globalSettings = DB::table('settings')->pluck('value', 'key');

// Logo Resolver
$dbLogo = $globalSettings['header_logo'] ?? null;
$finalLogo = null;
if (!empty($dbLogo)) {
    if (filter_var($dbLogo, FILTER_VALIDATE_URL)) {
        $finalLogo = $dbLogo;
    } else {
        $cleanLogo = ltrim(str_replace('storage/', '', $dbLogo), '/');
        if (file_exists(public_path($cleanLogo))) {
            $finalLogo = asset($cleanLogo);
        } elseif (file_exists(public_path('uploads/' . $cleanLogo))) {
            $finalLogo = asset('uploads/' . $cleanLogo);
        } else {
            $finalLogo = asset($cleanLogo);
        }
    }
}

$siteTitle = !empty($globalSettings['site_title']) ? $globalSettings['site_title'] : config('app.name', 'Ecommerce');
$displayMode = $globalSettings['header_display_mode'] ?? 'logo_title';
$tagline = $globalSettings['site_tagline'] ?? '';

// Logo Dimensions
$rawWidth = $globalSettings['logo_width'] ?? '150';
$rawHeight = $globalSettings['logo_height'] ?? '40';
$logoWidth = preg_replace('/[^0-9]/', '', (string) $rawWidth) ?: '150';
$logoHeight = preg_replace('/[^0-9]/', '', (string) $rawHeight) ?: '40';

// 1. Header Background & Separators
$hdrBgColor = $globalSettings['color_hdr_bg'] ?? '#ffffff';
$hdrBorderColor = $globalSettings['color_hdr_border'] ?? '#e2e8f0';
$hdrSecMenuBg = $globalSettings['color_hdr_sec_menu_bg'] ?? '#ffffff';
$hdrSecMenuBorder = $globalSettings['color_hdr_sec_menu_border'] ?? '#f1f5f9';

// 2. Search Box Elements
$hdrSearchBorder = $globalSettings['color_hdr_search_border'] ?? '#2563eb';
$hdrSearchBg = $globalSettings['color_hdr_search_bg'] ?? '#ffffff';
$hdrSearchInputText = $globalSettings['color_hdr_search_input_text'] ?? '#0f172a';
$hdrSearchPlaceholder = $globalSettings['color_hdr_search_placeholder'] ?? '#94a3b8';
$hdrSearchCatBg = $globalSettings['color_hdr_search_cat_bg'] ?? '#f8fafc';
$hdrSearchCatText = $globalSettings['color_hdr_search_cat_text'] ?? '#475569';
$hdrSearchBtnBg = $globalSettings['color_hdr_search_btn_bg'] ?? '#2563eb';
$hdrSearchBtnText = $globalSettings['color_hdr_search_btn_text'] ?? '#ffffff';
$hdrSearchBtnHoverBg = $globalSettings['color_hdr_search_btn_hover_bg'] ?? '#1d4ed8';
$hdrSearchBtnHoverText = $globalSettings['color_hdr_search_btn_hover_text'] ?? '#ffffff';

// 3. Header Action Icons & Badges
$hdrIconText = $globalSettings['color_hdr_icon_text'] ?? '#334155';
$hdrIconHover = $globalSettings['color_hdr_icon_hover'] ?? '#2563eb';
$hdrIconActive = $globalSettings['color_hdr_icon_active'] ?? '#f59e0b';
$hdrBadgeBg = $globalSettings['color_hdr_badge_bg'] ?? '#ef4444';
$hdrBadgeText = $globalSettings['color_hdr_badge_text'] ?? '#ffffff';

// 4. Desktop Category Menu Button & Dropdown
$hdrCatBtnBg = $globalSettings['color_hdr_cat_btn_bg'] ?? '#f8fafc';
$hdrCatBtnText = $globalSettings['color_hdr_cat_btn_text'] ?? '#0f172a';
$hdrCatBtnHoverBg = $globalSettings['color_hdr_cat_btn_hover_bg'] ?? '#e2e8f0';
$hdrCatBtnHoverText = $globalSettings['color_hdr_cat_btn_hover_text'] ?? '#2563eb';
$hdrCatBtnActiveBg = $globalSettings['color_hdr_cat_btn_active_bg'] ?? '#2563eb';
$hdrCatBtnActiveText = $globalSettings['color_hdr_cat_btn_active_text'] ?? '#ffffff';
$hdrCatDropBg = $globalSettings['color_hdr_cat_drop_bg'] ?? '#ffffff';
$hdrCatDropText = $globalSettings['color_hdr_cat_drop_text'] ?? '#334155';
$hdrCatDropHoverBg = $globalSettings['color_hdr_cat_drop_hover_bg'] ?? '#f1f5f9';
$hdrCatDropHoverText = $globalSettings['color_hdr_cat_drop_hover_text'] ?? '#2563eb';

// 5. Desktop Primary Menu Links
$menuFontSize = $globalSettings['menu_font_size'] ?? '14px';
$menuFontWeight = $globalSettings['menu_font_weight'] ?? '600';
$menuPadding = $globalSettings['menu_padding'] ?? '10px 15px';
$menuMargin = $globalSettings['menu_margin'] ?? '0px';
$hdrMenuText = $globalSettings['color_hdr_menu_text'] ?? ($globalSettings['menu_color'] ?? '#334155');
$hdrMenuBg = $globalSettings['color_hdr_menu_bg'] ?? 'transparent';
$hdrMenuHoverText = $globalSettings['color_hdr_menu_hover_text'] ?? ($globalSettings['menu_hover_color'] ?? '#00a8ff');
$hdrMenuHoverBg = $globalSettings['color_hdr_menu_hover_bg'] ?? ($globalSettings['menu_hover_bg'] ?? '#e0f2fe');
$hdrMenuActiveText = $globalSettings['color_hdr_menu_active_text'] ?? '#2563eb';
$hdrMenuActiveBg = $globalSettings['color_hdr_menu_active_bg'] ?? '#e0f2fe';
$hdrMenuDropBg = $globalSettings['color_hdr_menu_drop_bg'] ?? '#ffffff';
$hdrMenuDropText = $globalSettings['color_hdr_menu_drop_text'] ?? '#475569';
$hdrMenuDropHoverBg = $globalSettings['color_hdr_menu_drop_hover_bg'] ?? '#f8fafc';
$hdrMenuDropHoverText = $globalSettings['color_hdr_menu_drop_hover_text'] ?? '#2563eb';

// 6. Header Support Contact Info
$hdrSupportIcon = $globalSettings['color_hdr_support_icon'] ?? '#2563eb';
$hdrSupportLabel = $globalSettings['color_hdr_support_label'] ?? '#64748b';
$hdrSupportPhone = $globalSettings['color_hdr_support_phone'] ?? '#0f172a';
$hdrSupportPhoneHover = $globalSettings['color_hdr_support_phone_hover'] ?? '#2563eb';

// 7. Mobile Menu Trigger Button
$hdrMobToggleBg = $globalSettings['color_hdr_mob_toggle_bg'] ?? '#f8fafc';
$hdrMobToggleIcon = $globalSettings['color_hdr_mob_toggle_icon'] ?? '#0f172a';
$hdrMobToggleHoverBg = $globalSettings['color_hdr_mob_toggle_hover_bg'] ?? '#e2e8f0';
$hdrMobToggleHoverIcon = $globalSettings['color_hdr_mob_toggle_hover_icon'] ?? '#2563eb';

// 8. Mobile Offcanvas Navigation Drawer
$hdrMobHeadBg = $globalSettings['color_hdr_mob_head_bg'] ?? '#f8fafc';
$hdrMobHeadText = $globalSettings['color_hdr_mob_head_text'] ?? '#0f172a';
$hdrMobHeadIcon = $globalSettings['color_hdr_mob_head_icon'] ?? '#2563eb';
$hdrMobTabBg = $globalSettings['color_hdr_mob_tab_bg'] ?? '#e2e8f0';
$hdrMobTabText = $globalSettings['color_hdr_mob_tab_text'] ?? '#64748b';
$hdrMobTabActiveBg = $globalSettings['color_hdr_mob_tab_active_bg'] ?? '#2563eb';
$hdrMobTabActiveText = $globalSettings['color_hdr_mob_tab_active_text'] ?? '#ffffff';
$hdrMobBodyBg = $globalSettings['color_hdr_mob_body_bg'] ?? '#ffffff';
$hdrMobItemText = $globalSettings['color_hdr_mob_item_text'] ?? '#0f172a';
$hdrMobItemBorder = $globalSettings['color_hdr_mob_item_border'] ?? '#f1f5f9';
$hdrMobItemHoverBg = $globalSettings['color_hdr_mob_item_hover_bg'] ?? '#f8fafc';
$hdrMobItemHoverText = $globalSettings['color_hdr_mob_item_hover_text'] ?? '#2563eb';
$hdrMobItemActiveBg = $globalSettings['color_hdr_mob_item_active_bg'] ?? '#eff6ff';
$hdrMobItemActiveText = $globalSettings['color_hdr_mob_item_active_text'] ?? '#2563eb';

$isStickyHeader = ($globalSettings['feature_sticky_header'] ?? '1') == '1';
$isGlassHeader = ($globalSettings['feature_glassmorphism_header'] ?? '0') == '1';
$enableFloatingCart = ($globalSettings['feature_floating_cart_modal'] ?? '1') == '1';

$showTopbarDesktop = !isset($globalSettings['show_topbar_desktop']) || $globalSettings['show_topbar_desktop'] == '1';
$showTopbarMobile = !isset($globalSettings['show_topbar_mobile']) || $globalSettings['show_topbar_mobile'] == '1';

$topbarBg = $globalSettings['topbar_bg'] ?? '#0066ff';
$topbarColor = $globalSettings['topbar_color'] ?? '#ffffff';

$sliderDesktop = !isset($globalSettings['topbar_slider_desktop']) || $globalSettings['topbar_slider_desktop'] == '1';
$sliderMobile = !isset($globalSettings['topbar_slider_mobile']) || $globalSettings['topbar_slider_mobile'] == '1';

$noticesJson = $globalSettings['topbar_notices_json'] ?? null;
$topbarNotices = !empty($noticesJson) ? json_decode($noticesJson, true) : [
    ['text' => 'Free Shipping on orders over ৳1500', 'icon' => 'fas fa-check-circle'],
    ['text' => '7-Day Easy Returns & Instant Refund', 'icon' => 'fas fa-undo'],
    ['text' => '100% Secure Checkout with Leading Gateways', 'icon' => 'fas fa-shield-alt'],
    ['text' => 'Hotline Support: +880 1774-937404 (10 AM - 10 PM)', 'icon' => 'fas fa-headset']
];

$topbarLeftHtml = $globalSettings['topbar_left_html'] ?? '';
$topbarRightHtml = $globalSettings['topbar_right_html'] ?? '';

$formatDynamicUrl = function ($url) {
    if (empty($url) || $url === '#')
        return '#';
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        $parsed = parse_url($url);
        $path = ($parsed['path'] ?? '/') . (!empty($parsed['query']) ? '?' . $parsed['query'] : '');
        return url($path);
    }
    return url($url);
};

$isMenuItemActive = function ($url) use ($formatDynamicUrl) {
    if (empty($url) || $url === '#')
        return false;
    $currentUrl = url()->current();
    $fullTarget = $formatDynamicUrl($url);
    if ($currentUrl === $fullTarget)
        return true;
    if ($fullTarget === url('/'))
        return request()->is('/');
    $parsedTarget = parse_url($fullTarget, PHP_URL_PATH);
    $trimmedTarget = trim($parsedTarget ?? '', '/');
    return !empty($trimmedTarget) && request()->is($trimmedTarget, $trimmedTarget . '/*');
};

$isTreeActive = function ($item) use ($isMenuItemActive, &$isTreeActive) {
    if ($isMenuItemActive($item['url'] ?? ''))
        return true;
    if (!empty($item['children'])) {
        foreach ($item['children'] as $child) {
            if ($isTreeActive($child))
                return true;
        }
    }
    return false;
};

$primaryMenuRow = DB::table('site_menus')->whereJsonContains('locations', 'primary')->first();
$rawPrimaryItems = !empty($primaryMenuRow->items) ? json_decode($primaryMenuRow->items, true) : [
    ['title' => 'Hot Offers', 'url' => route('shop', ['filter' => 'offers']), 'depth' => 0],
    ['title' => 'Bestsellers', 'url' => route('shop', ['filter' => 'bestsellers']), 'depth' => 0],
    ['title' => 'Gift Boxes', 'url' => route('shop', ['filter' => 'gifts']), 'depth' => 0],
    ['title' => 'About Us', 'url' => url('/about-us'), 'depth' => 0],
    ['title' => 'Contact', 'url' => url('/contact-us'), 'depth' => 0],
];

$primaryNavItems = [];
$lastItems = [];
foreach ($rawPrimaryItems as $item) {
    $item['url'] = $formatDynamicUrl($item['url'] ?? '');
    $item['children'] = [];
    $depth = $item['depth'] ?? ($item['is_sub'] ?? false ? 1 : 0);

    if ($depth === 0) {
        $primaryNavItems[] = $item;
        $lastItems[0] = count($primaryNavItems) - 1;
    } else {
        $parentDepth = $depth - 1;
        if (isset($lastItems[$parentDepth])) {
            if ($depth === 1) {
                $primaryNavItems[$lastItems[0]]['children'][] = $item;
                $lastItems[1] = count($primaryNavItems[$lastItems[0]]['children']) - 1;
            } elseif ($depth === 2) {
                $primaryNavItems[$lastItems[0]]['children'][$lastItems[1]]['children'][] = $item;
                $lastItems[2] = count($primaryNavItems[$lastItems[0]]['children'][$lastItems[1]]['children']) - 1;
            } else {
                $primaryNavItems[] = $item;
            }
        } else {
            $primaryNavItems[] = $item;
        }
    }
}

$mobileMenuRow = DB::table('site_menus')->whereJsonContains('locations', 'mobile')->first();
$rawMobileItems = !empty($mobileMenuRow->items) ? json_decode($mobileMenuRow->items, true) : $rawPrimaryItems;

$mobileNavItems = [];
$mLastItems = [];
foreach ($rawMobileItems as $item) {
    $item['url'] = $formatDynamicUrl($item['url'] ?? '');
    $item['children'] = [];
    $depth = $item['depth'] ?? ($item['is_sub'] ?? false ? 1 : 0);
    if ($depth === 0) {
        $mobileNavItems[] = $item;
        $mLastItems[0] = count($mobileNavItems) - 1;
    } else {
        if (isset($mLastItems[$depth - 1])) {
            if ($depth === 1) {
                $mobileNavItems[$mLastItems[0]]['children'][] = $item;
                $mLastItems[1] = count($mobileNavItems[$mLastItems[0]]['children']) - 1;
            } elseif ($depth === 2) {
                $mobileNavItems[$mLastItems[0]]['children'][$mLastItems[1]]['children'][] = $item;
            }
        }
    }
}

$catMenuRow = DB::table('site_menus')->whereJsonContains('locations', 'mobile_categories')->first();
$rawCatItems = !empty($catMenuRow->items) ? json_decode($catMenuRow->items, true) : null;

$drawerCategories = [];
$cLastItems = [];
if (!empty($rawCatItems)) {
    foreach ($rawCatItems as $item) {
        $item['url'] = $formatDynamicUrl($item['url'] ?? '');
        $item['children'] = [];
        $depth = $item['depth'] ?? ($item['is_sub'] ?? false ? 1 : 0);
        if ($depth === 0) {
            $drawerCategories[] = $item;
            $cLastItems[0] = count($drawerCategories) - 1;
        } else {
            if (isset($cLastItems[$depth - 1])) {
                if ($depth === 1) {
                    $drawerCategories[$cLastItems[0]]['children'][] = $item;
                    $cLastItems[1] = count($drawerCategories[$cLastItems[0]]['children']) - 1;
                } elseif ($depth === 2) {
                    $drawerCategories[$cLastItems[0]]['children'][$cLastItems[1]]['children'][] = $item;
                }
            }
        }
    }
}

$cartData = session()->get('cart', []);
$headerCartCount = array_sum(array_column($cartData, 'quantity'));
$headerCategories = \App\Models\Category::take(20)->get();

$headerWishlistCount = 0;
if (auth()->check()) {
    if (!\Illuminate\Support\Facades\Schema::hasTable('wishlists')) {
        \Illuminate\Support\Facades\Schema::create('wishlists', function ($table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('product_id');
            $table->timestamps();
            $table->index(['user_id', 'product_id']);
        });
    }
    $sessWish = session()->pull('wishlist', []);
    if (!empty($sessWish)) {
        foreach ($sessWish as $sId) {
            $currCount = DB::table('wishlists')->where('user_id', auth()->id())->count();
            if ($currCount < 100) {
                DB::table('wishlists')->updateOrInsert(
                    ['user_id' => auth()->id(), 'product_id' => (int) $sId],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }
    $headerWishlistCount = DB::table('wishlists')->where('user_id', auth()->id())->count();
} else {
    $headerWishlistCount = count(session()->get('wishlist', []));
}
$hasSavedProducts = $headerWishlistCount > 0;
@endphp

<style>
    :root {
        --hdr-bg: {{ $hdrBgColor }};
        --hdr-border: {{ $hdrBorderColor }};
        --hdr-sec-menu-bg: {{ $hdrSecMenuBg }};
        --hdr-sec-menu-border: {{ $hdrSecMenuBorder }};

        --hdr-search-border: {{ $hdrSearchBorder }};
        --hdr-search-bg: {{ $hdrSearchBg }};
        --hdr-search-input-text: {{ $hdrSearchInputText }};
        --hdr-search-placeholder: {{ $hdrSearchPlaceholder }};
        --hdr-search-cat-bg: {{ $hdrSearchCatBg }};
        --hdr-search-cat-text: {{ $hdrSearchCatText }};
        --hdr-search-btn-bg: {{ $hdrSearchBtnBg }};
        --hdr-search-btn-text: {{ $hdrSearchBtnText }};
        --hdr-search-btn-hover-bg: {{ $hdrSearchBtnHoverBg }};
        --hdr-search-btn-hover-text: {{ $hdrSearchBtnHoverText }};

        --hdr-icon-text: {{ $hdrIconText }};
        --hdr-icon-hover: {{ $hdrIconHover }};
        --hdr-icon-active: {{ $hdrIconActive }};
        --hdr-badge-bg: {{ $hdrBadgeBg }};
        --hdr-badge-text: {{ $hdrBadgeText }};

        --hdr-cat-btn-bg: {{ $hdrCatBtnBg }};
        --hdr-cat-btn-text: {{ $hdrCatBtnText }};
        --hdr-cat-btn-hover-bg: {{ $hdrCatBtnHoverBg }};
        --hdr-cat-btn-hover-text: {{ $hdrCatBtnHoverText }};
        --hdr-cat-btn-active-bg: {{ $hdrCatBtnActiveBg }};
        --hdr-cat-btn-active-text: {{ $hdrCatBtnActiveText }};
        --hdr-cat-drop-bg: {{ $hdrCatDropBg }};
        --hdr-cat-drop-text: {{ $hdrCatDropText }};
        --hdr-cat-drop-hover-bg: {{ $hdrCatDropHoverBg }};
        --hdr-cat-drop-hover-text: {{ $hdrCatDropHoverText }};

        --dyn-menu-font-size: {{ $menuFontSize }};
        --dyn-menu-font-weight: {{ $menuFontWeight }};
        --dyn-menu-padding: {{ $menuPadding }};
        --dyn-menu-margin: {{ $menuMargin }};
        --hdr-menu-text: {{ $hdrMenuText }};
        --hdr-menu-bg: {{ $hdrMenuBg }};
        --hdr-menu-hover-text: {{ $hdrMenuHoverText }};
        --hdr-menu-hover-bg: {{ $hdrMenuHoverBg }};
        --hdr-menu-active-text: {{ $hdrMenuActiveText }};
        --hdr-menu-active-bg: {{ $hdrMenuActiveBg }};
        --hdr-menu-drop-bg: {{ $hdrMenuDropBg }};
        --hdr-menu-drop-text: {{ $hdrMenuDropText }};
        --hdr-menu-drop-hover-bg: {{ $hdrMenuDropHoverBg }};
        --hdr-menu-drop-hover-text: {{ $hdrMenuDropHoverText }};

        --hdr-support-icon: {{ $hdrSupportIcon }};
        --hdr-support-label: {{ $hdrSupportLabel }};
        --hdr-support-phone: {{ $hdrSupportPhone }};
        --hdr-support-phone-hover: {{ $hdrSupportPhoneHover }};

        --hdr-mob-toggle-bg: {{ $hdrMobToggleBg }};
        --hdr-mob-toggle-icon: {{ $hdrMobToggleIcon }};
        --hdr-mob-toggle-hover-bg: {{ $hdrMobToggleHoverBg }};
        --hdr-mob-toggle-hover-icon: {{ $hdrMobToggleHoverIcon }};

        --hdr-mob-head-bg: {{ $hdrMobHeadBg }};
        --hdr-mob-head-text: {{ $hdrMobHeadText }};
        --hdr-mob-head-icon: {{ $hdrMobHeadIcon }};
        --hdr-mob-tab-bg: {{ $hdrMobTabBg }};
        --hdr-mob-tab-text: {{ $hdrMobTabText }};
        --hdr-mob-tab-active-bg: {{ $hdrMobTabActiveBg }};
        --hdr-mob-tab-active-text: {{ $hdrMobTabActiveText }};
        --hdr-mob-body-bg: {{ $hdrMobBodyBg }};
        --hdr-mob-item-text: {{ $hdrMobItemText }};
        --hdr-mob-item-border: {{ $hdrMobItemBorder }};
        --hdr-mob-item-hover-bg: {{ $hdrMobItemHoverBg }};
        --hdr-mob-item-hover-text: {{ $hdrMobItemHoverText }};
        --hdr-mob-item-active-bg: {{ $hdrMobItemActiveBg }};
        --hdr-mob-item-active-text: {{ $hdrMobItemActiveText }};
    }


    /* header responsive custom css */
select#mainHeaderSearchCategory {
    border-radius: 0px !important;
}
@media (min-width: 992px) and (max-width: 1140px) {
.header-action-btn {
    min-width:35px !important ;
    font-size:8px !important;}
}


@media (min-width: 992px) and (max-width: 1050px) {

.desktop-nav-menu li, .desktop-nav-menu ul {

    margin-left: -10px !important;
}

}

@media (max-width: 600px) {
    a#header-saved-btn {
    margin-right: -10px !important;}  
    a.header-action-btn.btn-open-cart-drawer {
    margin-right: -10px;}
}

 /* header responsive custom css end! */

    
    /* 1. Header Structure & Separators */
.container.py-2\.5.py-md-3 {
    margin-bottom: 5px !important;
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
            background-color: rgba(255, 255, 255, 0.88) !important;
            backdrop-filter: blur(16px) saturate(180%) !important;
            -webkit-backdrop-filter: blur(16px) saturate(180%) !important;
            border-bottom: 1px solid var(--hdr-border) !important;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04) !important;
        @else
            background-color: var(--hdr-bg) !important;
            border-bottom: 1px solid var(--hdr-border) !important;
        @endif
        color: var(--hdr-icon-text) !important;
        transition: background-color 0.2s ease, box-shadow 0.2s ease;
    }

    .secondary-menu-bar {
        @if($isGlassHeader)
            background-color: rgba(255, 255, 255, 0.65) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
        @else
            background-color: var(--hdr-sec-menu-bg) !important;
        @endif
        border-top: 1px solid var(--hdr-sec-menu-border) !important;
    }

    @if($isStickyHeader)
        .header-spacer {
            display: block;
            height: 154px;
        }
        @media (max-width: 991.98px) {
            .header-spacer {
                height: 124px;
            }
        }
    @else
        .header-spacer {
            display: none;
        }
    @endif
    
    .topbar-wrapper { 
        background: {{ $topbarBg }}; 
        color: {{ $topbarColor }}; 
        font-size: 12px; 
        padding: 7px 0; 
        font-weight: 500; 
        letter-spacing: 0.2px;
    }
    .topbar-wrapper a { color: inherit; text-decoration: none; }
    .topbar-scroller-wrap { overflow: hidden; white-space: nowrap; width: 100%; }
    .topbar-scroller { display: inline-flex; animation: scrollNotice 35s linear infinite; gap: 50px; }
    .topbar-scroller:hover { animation-play-state: paused; }
    @keyframes scrollNotice { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }

    @media (min-width: 992px) {
        .topbar-desktop-view { display: block !important; }
        .topbar-mobile-view { display: none !important; }
    }
    @media (max-width: 991.98px) {
        .topbar-desktop-view { display: none !important; }
        .topbar-mobile-view { display: block !important; }
    }

    /* 2. Search Bar Styles */
    .header-search-bar { 
        border: 2px solid var(--hdr-search-border) !important; 
        border-radius: 10px; 
        overflow: hidden; 
        background-color: var(--hdr-search-bg) !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .header-search-bar:focus-within {
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }
    .header-search-input { 
        border: none; 
        outline: none; 
        box-shadow: none; 
        background: transparent !important; 
        font-size: 13.5px;
        color: var(--hdr-search-input-text) !important;
    }
    .header-search-input::placeholder {
        color: var(--hdr-search-placeholder) !important;
    }
    .header-search-select { 
        border: none; 
        border-left: 1px solid var(--hdr-search-border) !important; 
        background-color: var(--hdr-search-cat-bg) !important; 
        max-width: 145px; 
        font-size: 13px; 
        font-weight: 600; 
        color: var(--hdr-search-cat-text) !important; 
        cursor: pointer;
    }
    .header-search-btn { 
        background-color: var(--hdr-search-btn-bg) !important; 
        color: var(--hdr-search-btn-text) !important; 
        border: none; 
        padding: 0 22px; 
        font-weight: 700; 
        font-size: 13.5px; 
        transition: background-color 0.2s ease, color 0.2s ease; 
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    .header-search-btn:hover { 
        background-color: var(--hdr-search-btn-hover-bg) !important; 
        color: var(--hdr-search-btn-hover-text) !important; 
    }

    /* 3. Action Buttons & Badges */
    .header-action-btn { 
        text-decoration: none; 
        color: var(--hdr-icon-text) !important; 
        display: flex; 
        flex-direction: column; 
        align-items: center; 
        justify-content: center; 
        font-size: 11px; 
        font-weight: 600; 
        transition: color 0.2s ease, transform 0.2s ease; 
        position: relative; 
        min-width: 44px;
        padding: 4px 6px;
    }
    .header-action-btn:hover { 
        color: var(--hdr-icon-hover) !important; 
        transform: translateY(-1px); 
    }
    .header-action-btn i { 
        font-size: 18px; 
        margin-bottom: 3px; 
    }
    .header-action-btn.is-active-icon,
    .header-action-btn.is-active-icon i,
    .header-action-btn.is-active-icon span {
        color: var(--hdr-icon-active) !important;
    }
    .header-badge-count { 
        position: absolute; 
        top: -2px; 
        right: 4px; 
        background-color: var(--hdr-badge-bg) !important; 
        color: var(--hdr-badge-text) !important; 
        border-radius: 9999px; 
        padding: 1px 5px; 
        font-size: 9.5px; 
        font-weight: 700 !important; 
        line-height: 1.2;
    }

    /* Mobile Protection: Keep header icons intact without text spilling */
    @media (max-width: 991.98px) {
        .header-action-btn > span:not(.header-badge-count) {
            display: none !important;
        }
        .header-action-btn {
            min-width: 36px !important;
            padding: 4px 4px !important;
        }
    }

    /* 4. Desktop Category Button & Dropdown */
    .secondary-menu-bar .dropdown > button.btn {
        background-color: var(--hdr-cat-btn-bg) !important;
        color: var(--hdr-cat-btn-text) !important;
        transition: all 0.2s ease;
    }
    .secondary-menu-bar .dropdown > button.btn:hover {
        background-color: var(--hdr-cat-btn-hover-bg) !important;
        color: var(--hdr-cat-btn-hover-text) !important;
    }
    .secondary-menu-bar .dropdown > button.btn.is-active-cat,
    .secondary-menu-bar .dropdown > button.btn.btn-primary {
        background-color: var(--hdr-cat-btn-active-bg) !important;
        color: var(--hdr-cat-btn-active-text) !important;
    }
    .secondary-menu-bar .dropdown-menu {
        background-color: var(--hdr-cat-drop-bg) !important;
        border-color: var(--hdr-border) !important;
    }
    .secondary-menu-bar .dropdown-item {
        color: var(--hdr-cat-drop-text) !important;
        transition: all 0.15s ease;
    }
    .secondary-menu-bar .dropdown-item:hover,
    .secondary-menu-bar .dropdown-item.active {
        background-color: var(--hdr-cat-drop-hover-bg) !important;
        color: var(--hdr-cat-drop-hover-text) !important;
    }

    /* 5. Desktop Navigation Menu */
    .desktop-nav-menu { 
        display: flex; 
        align-items: center; 
        gap: 4px; 
        list-style: none !important; 
        margin: 0 !important; 
        padding: 0 !important; 
    }
    .desktop-nav-menu li, .desktop-nav-menu ul { 
        list-style: none !important; 
        padding-left: 0 !important; 
    }
    .desktop-nav-item { position: relative; }
    
    .desktop-nav-link {
        text-decoration: none; 
        font-size: var(--dyn-menu-font-size); 
        font-weight: var(--dyn-menu-font-weight);
        color: var(--hdr-menu-text) !important; 
        background-color: var(--hdr-menu-bg) !important; 
        padding: var(--dyn-menu-padding); 
        margin: var(--dyn-menu-margin);
        border-radius: 8px; 
        transition: all 0.2s ease; 
        display: flex; 
        align-items: center; 
        gap: 6px; 
        position: relative;
    }
    .desktop-nav-link:hover { 
        color: var(--hdr-menu-hover-text) !important; 
        background-color: var(--hdr-menu-hover-bg) !important; 
    }
    .desktop-nav-link.active {
        color: var(--hdr-menu-active-text) !important; 
        background-color: var(--hdr-menu-active-bg) !important; 
        font-weight: 700 !important;
    }
    .desktop-nav-link.active::after {
        content: ""; 
        position: absolute; 
        bottom: 0px; 
        left: 18%; 
        right: 18%; 
        height: 2px;
        background-color: var(--hdr-menu-active-text) !important; 
        border-radius: 2px;
    }

    .desktop-nav-menu .dropdown { 
        position: relative; 
        padding-bottom: 12px; 
        margin-bottom: -12px; 
    }
    .desktop-nav-menu .shadow-dropdown {
        display: none; 
        position: absolute; 
        top: 100%; 
        left: 0; 
        z-index: 10000 !important; 
        min-width: 240px; 
        padding: 8px 0 !important;
        margin: 0 !important; 
        background-color: var(--hdr-menu-drop-bg) !important; 
        border: 1px solid var(--hdr-border) !important; 
        border-radius: 12px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08); 
        animation: fadeInMenu 0.2s ease-in-out; 
        list-style: none !important;
    }
    .desktop-nav-menu .dropdown:hover > .shadow-dropdown { display: block; }
    
    .desktop-nav-menu .dropdown-submenu { position: relative; width: 100%; list-style: none !important; }
    .desktop-nav-menu .dropdown-submenu > .submenu-box {
        display: none; 
        position: static; 
        background: transparent; 
        border: none; 
        box-shadow: none;
        margin: 0 0 0 14px !important; 
        padding: 0 !important; 
        width: auto; 
        border-left: 2px solid var(--hdr-border); 
        list-style: none !important;
    }
    .desktop-nav-menu .dropdown-submenu:hover > .submenu-box { display: block; }
    
    .desktop-nav-menu .dropdown-item {
        font-size: 13.5px; 
        font-weight: 500; 
        color: var(--hdr-menu-drop-text) !important; 
        padding: 8px 18px; 
        display: flex; 
        justify-content: space-between;
        align-items: center; 
        text-decoration: none; 
        transition: all 0.15s ease; 
        width: 100%; 
        box-sizing: border-box;
    }
    .desktop-nav-menu .dropdown-item:hover { 
        background-color: var(--hdr-menu-drop-hover-bg) !important; 
        color: var(--hdr-menu-drop-hover-text) !important; 
        padding-left: 22px; 
    }
    .desktop-nav-menu .dropdown-item.active {
        background-color: var(--hdr-menu-drop-hover-bg) !important; 
        color: var(--hdr-menu-drop-hover-text) !important; 
        font-weight: 700 !important;
        border-left: 3px solid var(--hdr-menu-drop-hover-text);
    }

    /* 6. Support Header Section */
    .hdr-support-icon {
        color: var(--hdr-support-icon) !important;
    }
    .hdr-support-label {
        color: var(--hdr-support-label) !important;
    }
    .hdr-support-phone {
        color: var(--hdr-support-phone) !important;
        transition: color 0.15s ease;
    }
    .hdr-support-phone:hover {
        color: var(--hdr-support-phone-hover) !important;
    }

    /* 7. Mobile Menu Trigger Button */
    .mobile-menu-toggle-btn { 
        border: 1px solid var(--hdr-border) !important; 
        background-color: var(--hdr-mob-toggle-bg) !important; 
        color: var(--hdr-mob-toggle-icon) !important; 
        width: 40px; 
        height: 40px; 
        border-radius: 10px; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        font-size: 17px; 
        cursor: pointer; 
        transition: all 0.2s ease; 
    }
    .mobile-menu-toggle-btn:hover { 
        background-color: var(--hdr-mob-toggle-hover-bg) !important; 
        color: var(--hdr-mob-toggle-hover-icon) !important; 
    }

    /* 8. Mobile Offcanvas Navigation Drawer */
    #mobileMenuOffcanvas {
        width: 325px !important;
        max-width: 88vw !important;
    }
    #mobileMenuOffcanvas .offcanvas-header {
        background-color: var(--hdr-mob-head-bg) !important;
        color: var(--hdr-mob-head-text) !important;
        border-bottom: 1px solid var(--hdr-mob-item-border) !important;
    }
    #mobileMenuOffcanvas .offcanvas-title {
        color: var(--hdr-mob-head-text) !important;
    }
    #mobileMenuOffcanvas .offcanvas-title i {
        color: var(--hdr-mob-head-icon) !important;
    }
    #mobileMenuOffcanvas .offcanvas-body {
        background-color: var(--hdr-mob-body-bg) !important;
    }

    /* Tabs Bar in Drawer */
    .mobile-offcanvas-tabs {
        background-color: var(--hdr-mob-head-bg) !important;
        border-bottom: 1px solid var(--hdr-mob-item-border) !important;
    }
    .mobile-offcanvas-tabs .nav-link { 
        border-radius: 8px; 
        font-weight: 600; 
        font-size: 13px; 
        background-color: var(--hdr-mob-tab-bg) !important;
        color: var(--hdr-mob-tab-text) !important; 
        padding: 8px 16px; 
        transition: all 0.2s ease;
    }
    .mobile-offcanvas-tabs .nav-link.active { 
        background-color: var(--hdr-mob-tab-active-bg) !important; 
        color: var(--hdr-mob-tab-active-text) !important; 
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);
    }

    /* Common Text, Separator Borders & HR across all Drawer Items */
    #mobileMenuOffcanvas .list-group-item,
    #mobileMenuOffcanvas .list-group-item a,
    #mobileMenuOffcanvas .mobile-tree-item,
    #mobileMenuOffcanvas .mob-accordion-btn {
        color: var(--hdr-mob-item-text) !important;
    }
    #mobileMenuOffcanvas .list-group-item {
        background-color: transparent !important;
        border-bottom: 1px solid var(--hdr-mob-item-border) !important;
    }
    #mobileMenuOffcanvas .border-bottom {
        border-bottom-color: var(--hdr-mob-item-border) !important;
    }
    #mobileMenuOffcanvas .mobile-tree-submenu { 
        margin-left: 20px; 
        padding-left: 0; 
        border-left: 1px solid var(--hdr-mob-item-border) !important; 
        margin-top: 4px; 
        margin-bottom: 4px; 
    }

    /* Single-Line Category Text Fix */
    #mobileMenuOffcanvas .list-group-item a,
    #mobileMenuOffcanvas .mobile-tree-item {
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        font-size: 13.5px !important;
    }
    #mobileMenuOffcanvas .mob-accordion-btn {
        width: 28px !important;
        height: 28px !important;
        flex-shrink: 0 !important;
        border: none; 
        background: transparent; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        font-size: 12px; 
        transition: all 0.25s ease; 
        opacity: 0.75; 
    }
    #mobileMenuOffcanvas .mob-accordion-btn:hover { opacity: 1; }
    #mobileMenuOffcanvas .mob-accordion-btn[aria-expanded="true"] { transform: rotate(180deg); opacity: 1; }

    /* Drawer Hover States */
    #mobileMenuOffcanvas .list-group-item:hover,
    #mobileMenuOffcanvas .list-group-item a:hover,
    #mobileMenuOffcanvas .mobile-tree-item:hover {
        background-color: var(--hdr-mob-item-hover-bg) !important;
        color: var(--hdr-mob-item-hover-text) !important;
    }
    #mobileMenuOffcanvas .list-group-item:hover a,
    #mobileMenuOffcanvas .list-group-item:hover button {
        color: var(--hdr-mob-item-hover-text) !important;
    }

    /* Drawer Active State */
    #mobileMenuOffcanvas .list-group-item.active-menu-item,
    #mobileMenuOffcanvas .active-menu-item a,
    #mobileMenuOffcanvas .mobile-tree-item.active {
        background-color: var(--hdr-mob-item-active-bg) !important;
        color: var(--hdr-mob-item-active-text) !important;
        font-weight: 700 !important;
        opacity: 1 !important;
    }
    #mobileMenuOffcanvas .active-menu-item button {
        color: var(--hdr-mob-item-active-text) !important;
    }

    .user-account-dropdown-menu {
        z-index: 1065 !important;
        position: absolute !important;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.12) !important;
        border: 1px solid #e2e8f0;
    }

    @keyframes fadeInMenu { 
        from { opacity: 0; transform: translateY(6px); } 
        to { opacity: 1; transform: translateY(0); } 
    }

    .live-search-container { 
        position: absolute; 
        top: calc(100% + 6px); 
        left: 0; 
        right: 0; 
        background: #ffffff; 
        border-radius: 12px; 
        box-shadow: 0 15px 35px rgba(15, 23, 42, 0.12); 
        border: 1px solid #e2e8f0; 
        z-index: 10005 !important; 
        max-height: 420px; 
        overflow-y: auto; 
    }
    .search-result-row { 
        padding: 10px 14px; 
        border-bottom: 1px solid #f1f5f9; 
        display: flex; 
        align-items: center; 
        justify-content: space-between; 
        transition: background-color 0.15s ease; 
    }
    .search-result-row:hover { 
        background-color: #f8fafc; 
    }
    .search-qty-pill { 
        display: inline-flex; 
        align-items: center; 
        background: #f1f5f9; 
        border-radius: 6px; 
        padding: 2px 4px; 
        gap: 4px; 
        border: 1px solid #e2e8f0;
    }
    .search-qty-btn { 
        border: none; 
        background: transparent; 
        width: 22px; 
        height: 22px; 
        font-weight: bold; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        border-radius: 4px; 
        cursor: pointer; 
        color: #475569;
    }
    .search-qty-btn:hover { 
        background: #e2e8f0; 
        color: #0f172a;
    }

    .bn-digit, [data-amount] {
        font-weight: 700 !important;
    }

    @media (max-width: 767.98px) {
        .search-result-row { padding: 8px 10px !important; gap: 6px !important; }
        .search-result-row > a { min-width: 0 !important; flex: 1 1 auto !important; overflow: hidden !important; }
        .search-result-row > a > div { min-width: 0 !important; flex: 1 1 auto !important; overflow: hidden !important; }
        .search-result-row > a img { width: 38px !important; height: 38px !important; flex-shrink: 0 !important; }
        .search-result-row > a .text-truncate { max-width: 100% !important; font-size: 12px !important; }
        .search-result-row > div:last-child { flex-shrink: 0 !important; gap: 4px !important; margin-left: auto !important; }
        .search-qty-pill { padding: 2px 3px !important; gap: 2px !important; }
        .search-qty-btn { width: 20px !important; height: 20px !important; font-size: 11px !important; }
        .search-result-row button.btn-primary { padding: 5px 10px !important; font-size: 12px !important; flex-shrink: 0 !important; }
    }
</style>

<header class="master-site-header">
    @if($showTopbarDesktop)
        <div class="topbar-wrapper topbar-desktop-view theme-topbar">
            <div class="container-fluid px-3 px-md-4">
                @if($sliderDesktop && count($topbarNotices) > 0)
                    <div class="topbar-scroller-wrap">
                        <div class="topbar-scroller">
                            @foreach(array_merge($topbarNotices, $topbarNotices) as $notice)
                                <span><i class="{{ $notice['icon'] ?? 'fas fa-check-circle' }} me-1"></i> <span class="bn-digit">{{ $notice['text'] }}</span></span>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="topbar-left-box small">
                            {!! $topbarLeftHtml !!}
                        </div>
                        <div class="topbar-right-box small">
                            {!! $topbarRightHtml !!}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if($showTopbarMobile)
        <div class="topbar-wrapper topbar-mobile-view theme-topbar">
            <div class="container-fluid px-2">
                @if($sliderMobile && count($topbarNotices) > 0)
                    <div class="topbar-scroller-wrap">
                        <div class="topbar-scroller">
                            @foreach(array_merge($topbarNotices, $topbarNotices) as $notice)
                                <span><i class="{{ $notice['icon'] ?? 'fas fa-check-circle' }} me-1"></i> <span class="bn-digit">{{ $notice['text'] }}</span></span>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-1">
                        <div class="topbar-left-box" style="font-size: 11px;">
                            {!! $topbarLeftHtml !!}
                        </div>
                        <div class="topbar-right-box" style="font-size: 11px;">
                            {!! $topbarRightHtml !!}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="container py-2.5 py-md-3">
        <div class="row align-items-center g-2 g-md-3">
            
            <div class="col-lg-3 col-md-3 col-5">
                <a href="{{ route('home') }}" class="text-decoration-none d-flex align-items-center gap-2">
                    @if(!empty($finalLogo))
                        <img src="{{ $finalLogo }}" 
                             alt="{{ $siteTitle }}" 
                             title="{{ $siteTitle }}" 
                             style="width: {{ $logoWidth }}px !important; height: {{ $logoHeight }}px !important; max-width: {{ $logoWidth }}px !important; max-height: {{ $logoHeight }}px !important; object-fit: contain !important;" 
                             onerror="this.style.display='none'; document.getElementById('brandTextFallback').style.display='flex';">
                    @endif
                    <div id="brandTextFallback" class="d-flex flex-column" style="{{ empty($finalLogo) || ($displayMode ?? '') !== 'logo_only' ? '' : 'display: none !important;' }}">
                        <span class="fs-4 fw-bold text-dark tracking-tight lh-1">{{ $siteTitle }}</span>
                        @if(($displayMode ?? '') === 'logo_title_tagline' && !empty($tagline))
                            <small class="text-muted fw-semibold mt-1" style="font-size: 10px; letter-spacing: 0.5px;">{{ $tagline }}</small>
                        @endif
                    </div>
                </a>
            </div>

            <div class="col-lg-6 col-12 order-3 order-lg-2 position-relative" style="z-index: 1030;">
                <form action="{{ route('shop') }}" method="GET" class="header-search-bar d-flex position-relative theme-header-search-bar">
                    <input type="text" id="mainHeaderSearchInput" name="q" value="{{ request('q') }}" class="form-control header-search-input px-3" placeholder="Find products by name, model or SKU..." autocomplete="off">
                    <select id="mainHeaderSearchCategory" name="category" class="form-select header-search-select d-none d-sm-block">
                        <option value="all">All Categories</option>
                        @foreach($headerCategories as $cat)
                            <option value="{{ $cat->slug ?? $cat->id }}" {{ request('category') == ($cat->slug ?? $cat->id) ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="header-search-btn theme-search-btn"><i class="fas fa-search d-sm-none"></i><span class="d-none d-sm-inline">Search</span></button>
                </form>
                <div id="liveSearchResultsBox" class="live-search-container d-none"><div id="liveSearchResultsList"></div></div>
            </div>

            <div class="col-lg-3 col-md-9 col-7 order-2 order-lg-3 position-relative" style="z-index: 1055;">
                <div class="d-flex justify-content-end align-items-center gap-2 gap-sm-3 gap-lg-4">
                    @auth
                        @php
    $uHeader = auth()->user();
    $isInternalStaff = !empty($uHeader->is_super_admin)
        || (method_exists($uHeader, 'isStaffOrAdmin') && $uHeader->isStaffOrAdmin())
        || in_array(str_replace(' ', '_', strtolower(trim((string) $uHeader->role))), ['admin', 'super_admin', 'branch_manager', 'staff'])
        || in_array(str_replace(' ', '_', strtolower(trim((string) ($uHeader->user_type ?? '')))), ['admin', 'staff']);
                        @endphp
                        <a href="{{ $isInternalStaff ? route('admin.order.online') : route('user.orders') }}" class="header-action-btn d-none d-md-flex"><i class="fas fa-receipt"></i><span>Orders</span></a>
                    @else
                        <a href="{{ route('checkout') }}" class="header-action-btn d-none d-md-flex"><i class="fas fa-receipt"></i><span>Orders</span></a>
                    @endauth

                    <a href="{{ auth()->check() ? route('user.wishlist') : (Route::has('login') ? route('login') : '#') }}" class="header-action-btn position-relative {{ $hasSavedProducts ? 'is-active-icon' : '' }}" id="header-saved-btn">
                        <i id="header-saved-icon" class="{{ $hasSavedProducts ? 'fas fa-heart text-warning' : 'far fa-heart' }}"></i>
                        <span id="wishlist-counter-badge" class="header-badge-count wishlist-badge-count bn-digit {{ $hasSavedProducts ? '' : 'd-none' }}">{{ $headerWishlistCount }}</span>
                        <span id="header-saved-text">Saved</span>
                    </a>

                    <a href="{{ route('cart.index') }}" class="header-action-btn {{ $enableFloatingCart ? 'btn-open-cart-drawer' : '' }}">
                        <i class="fas fa-shopping-cart"></i>
                        <span id="cart-counter-badge" class="header-badge-count cart-badge-count bn-digit {{ $headerCartCount > 0 ? '' : 'd-none' }}">{{ $headerCartCount }}</span>
                        <span>My Cart</span>
                    </a>

                    @auth
                        @php
    $uDropdown = auth()->user();
    $hasAdminAccess = !empty($uDropdown->is_super_admin)
        || (method_exists($uDropdown, 'isStaffOrAdmin') && $uDropdown->isStaffOrAdmin())
        || in_array(str_replace(' ', '_', strtolower(trim((string) $uDropdown->role))), ['admin', 'super_admin', 'branch_manager', 'staff', 'manager', 'cashier'])
        || in_array(str_replace(' ', '_', strtolower(trim((string) ($uDropdown->user_type ?? '')))), ['admin', 'staff']);
                        @endphp
                        <div class="dropdown position-relative" style="z-index: 1060;">
                            <a href="#" class="header-action-btn dropdown-toggle text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user-circle {{ $hasAdminAccess ? 'text-danger' : 'text-primary' }}"></i><span>{{ Str::limit($uDropdown->name, 7) }}</span></a>
                            <ul class="dropdown-menu dropdown-menu-end user-account-dropdown-menu rounded-3 mt-2 py-2" style="min-width: 200px;">
                                <li class="px-3 py-1.5 border-bottom mb-2">
                                    <div class="fw-bold text-dark text-truncate">{{ $uDropdown->name }}</div>
                                    <small class="text-muted text-truncate d-block">{{ $uDropdown->email }}</small>
                                    @if($hasAdminAccess)
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill mt-1" style="font-size: 10px;">{{ ucwords(str_replace('_', ' ', $uDropdown->role ?? 'Staff')) }}</span>
                                    @endif
                                </li>
                                @if($hasAdminAccess)
                                    <li><a class="dropdown-item small py-2 fw-bold text-danger" href="{{ route('admin.dashboard') }}"><i class="fas fa-shield-alt me-2 text-danger"></i> Admin Panel</a></li>
                                @endif
                                <li><a class="dropdown-item small py-2" href="{{ $hasAdminAccess ? route('admin.dashboard') : route('user.dashboard') }}"><i class="fas fa-th-large me-2 text-primary"></i> Dashboard</a></li>
                                <li><a class="dropdown-item small py-2" href="{{ route('user.orders') }}"><i class="fas fa-box me-2 text-secondary"></i> My Orders</a></li>
                                <li><a class="dropdown-item small py-2" href="{{ route('user.profile') }}"><i class="far fa-user me-2 text-secondary"></i> Profile Settings</a></li>
                                <li class="border-top my-1"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item small py-2 text-danger"><i class="fas fa-sign-out-alt me-2"></i> Sign Out</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <div class="dropdown position-relative" style="z-index: 1060;">
                            <a href="#" class="header-action-btn dropdown-toggle text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false"><i class="far fa-user"></i><span>Sign In</span></a>
                            <ul class="dropdown-menu dropdown-menu-end user-account-dropdown-menu rounded-3 mt-2 py-2" style="min-width: 180px;">
                                <li><a class="dropdown-item small py-2 fw-bold text-primary" href="{{ Route::has('login') ? route('login') : '#' }}"><i class="fas fa-sign-in-alt me-2"></i> Sign In</a></li>
                                @if(Route::has('register'))
                                    <li><a class="dropdown-item small py-2 text-secondary fw-semibold" href="{{ route('register') }}"><i class="fas fa-user-plus me-2"></i> Register</a></li>
                                @endif
                            </ul>
                        </div>
                    @endauth

                    <button class="mobile-menu-toggle-btn d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenuOffcanvas"><i class="fas fa-bars"></i></button>
                </div>
            </div>
        </div>
    </div>

    <div class="secondary-menu-bar py-2 d-none d-lg-block">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">
                @php 
                    $isAnyCategoryActive = request()->is('category*') || request()->has('category'); 
                @endphp
                <div class="dropdown">
                    <button class="btn btn-sm {{ $isAnyCategoryActive ? 'btn-primary text-white is-active-cat' : 'btn-light text-dark' }} border-0 fw-bold dropdown-toggle d-flex align-items-center gap-2 py-1.5 px-3 rounded-2 shadow-sm" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-bars"></i> All Categories
                    </button>
                    <ul class="dropdown-menu shadow-sm border rounded-3 mt-1 py-2" style="border-color: #e2e8f0;">
                        @foreach($headerCategories as $cat)
                            @php
    $catUrl = route('category.products', $cat->slug ?? $cat->id);
    $isCatActive = $isMenuItemActive($catUrl) || request('category') == ($cat->slug ?? $cat->id);
                            @endphp
                            <li>
                                <a class="dropdown-item small py-2 {{ $isCatActive ? 'active' : '' }}" href="{{ $catUrl }}">
                                    {{ $cat->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <ul class="desktop-nav-menu">
                    @foreach($primaryNavItems as $menu)
                        @php
    $parentActive = $isTreeActive($menu);
                        @endphp
                        @if(empty($menu['children']))
                            <li class="desktop-nav-item">
                                <a href="{{ $menu['url'] }}" class="desktop-nav-link {{ $parentActive ? 'active' : '' }}">
                                    {{ $menu['title'] }}
                                </a>
                            </li>
                        @else
                            <li class="desktop-nav-item dropdown">
                                <a href="{{ $menu['url'] }}" class="desktop-nav-link gap-1 {{ $parentActive ? 'active' : '' }}">
                                    {{ $menu['title'] }} <i class="fas fa-angle-down" style="font-size: 11px; margin-top: 2px;"></i>
                                </a>
                                <ul class="shadow-dropdown">
                                    @foreach($menu['children'] as $child)
                                        @php
            $childActive = $isTreeActive($child);
                                        @endphp
                                        @if(empty($child['children']))
                                            <li>
                                                <a class="dropdown-item {{ $childActive ? 'active' : '' }}" href="{{ $child['url'] }}">
                                                    {{ $child['title'] }}
                                                </a>
                                            </li>
                                        @else
                                            <li class="dropdown-submenu">
                                                <a class="dropdown-item {{ $childActive ? 'active' : '' }}" href="{{ $child['url'] }}">
                                                    <span>{{ $child['title'] }}</span> <i class="fas fa-angle-down small text-muted"></i>
                                                </a>
                                                <ul class="submenu-box">
                                                    @foreach($child['children'] as $grandChild)
                                                        @php
                    $grandActive = $isMenuItemActive($grandChild['url']);
                                                        @endphp
                                                        <li>
                                                            <a class="dropdown-item py-1.5 {{ $grandActive ? 'active' : '' }}" style="font-size:12.5px;" href="{{ $grandChild['url'] }}">
                                                                {{ $grandChild['title'] }}
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </li>
                        @endif
                    @endforeach
                </ul>

                <div class="small fw-semibold text-muted d-flex align-items-center">
                    <i class="fas fa-headset hdr-support-icon me-2"></i> <span class="hdr-support-label">Support:</span> <a href="tel:01774937404" class="hdr-support-phone text-dark text-decoration-none fw-bold ms-1 bn-digit">+880 1774-937404</a>
                </div>

            </div>
        </div>
    </div>
</header>

<div class="header-spacer"></div>

<!-- Mobile Navigation Drawer -->
<div class="offcanvas offcanvas-start border-0 shadow-lg" tabindex="-1" id="mobileMenuOffcanvas" aria-labelledby="mobileMenuOffcanvasLabel">
    <div class="offcanvas-header border-bottom py-3">
        <h6 class="offcanvas-title fw-bold mb-0 d-flex align-items-center gap-2" id="mobileMenuOffcanvasLabel">
            <i class="fas fa-compass"></i> Main Menu
        </h6>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body p-0">
        <ul class="nav nav-pills nav-fill mobile-offcanvas-tabs p-2 border-bottom gap-2" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-menu-categories" type="button"><i class="fas fa-th-large me-1"></i> Categories</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-menu-pages" type="button"><i class="fas fa-link me-1"></i> Pages</button></li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="tab-menu-categories">
                <div class="list-group list-group-flush">
                    @if(!empty($drawerCategories))
                        @foreach($drawerCategories as $cIdx => $dCat)
                            @php
        $dCatActive = $isTreeActive($dCat);
                            @endphp
                            @if(empty($dCat['children']))
                                <a href="{{ $dCat['url'] }}" class="list-group-item list-group-item-action py-3 px-3 fw-semibold border-bottom {{ $dCatActive ? 'active-menu-item' : '' }}">{{ $dCat['title'] }}</a>
                            @else
                                <div class="list-group-item list-group-item-action p-0 border-bottom border-0">
                                    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom {{ $dCatActive ? 'active-menu-item' : '' }}">
                                        <a href="{{ $dCat['url'] }}" class="text-decoration-none fw-semibold py-1 flex-grow-1" style="color: inherit;">{{ $dCat['title'] }}</a>
                                        <button class="mob-accordion-btn" type="button" data-bs-toggle="collapse" data-bs-target="#mobCat-{{ $cIdx }}"><i class="fas fa-chevron-down"></i></button>
                                    </div>
                                    <div class="collapse {{ $dCatActive ? 'show' : '' }}" id="mobCat-{{ $cIdx }}">
                                        <div class="mobile-tree-submenu">
                                            @foreach($dCat['children'] as $scIdx => $subCat)
                                                @php
                $subCatActive = $isTreeActive($subCat);
                                                @endphp
                                                @if(empty($subCat['children']))
                                                    <a href="{{ $subCat['url'] }}" class="mobile-tree-item fw-medium {{ $subCatActive ? 'active' : '' }}">{{ $subCat['title'] }}</a>
                                                @else
                                                    <div class="d-flex justify-content-between align-items-center pe-2">
                                                        <a href="{{ $subCat['url'] }}" class="mobile-tree-item fw-medium py-2 flex-grow-1 {{ $subCatActive ? 'active' : '' }}">{{ $subCat['title'] }}</a>
                                                        <button class="mob-accordion-btn" type="button" data-bs-toggle="collapse" data-bs-target="#mobSubCat-{{ $cIdx }}-{{ $scIdx }}"><i class="fas fa-chevron-down" style="font-size: 10px;"></i></button>
                                                    </div>
                                                    <div class="collapse {{ $subCatActive ? 'show' : '' }}" id="mobSubCat-{{ $cIdx }}-{{ $scIdx }}">
                                                        <div class="mobile-tree-submenu">
                                                            @foreach($subCat['children'] as $gCat)
                                                                @php
                        $gCatActive = $isMenuItemActive($gCat['url']);
                                                                @endphp
                                                                <a href="{{ $gCat['url'] }}" class="mobile-tree-item small {{ $gCatActive ? 'active' : '' }}">{{ $gCat['title'] }}</a>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @else
                        @foreach($headerCategories as $cat)
                            @php
        $cUrl = route('category.products', $cat->slug ?? $cat->id);
        $isThisCatActive = $isMenuItemActive($cUrl) || request('category') == ($cat->slug ?? $cat->id);
                            @endphp
                            <a href="{{ $cUrl }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 px-3 small fw-semibold {{ $isThisCatActive ? 'active-menu-item' : '' }}">
                                <span>{{ $cat->name }}</span>
                                <i class="fas fa-chevron-right text-muted" style="font-size: 10px;"></i>
                            </a>
                        @endforeach
                    @endif
                    <a href="{{ route('shop') }}" class="list-group-item list-group-item-action text-center fw-bold py-3 px-3 border-bottom"><i class="fas fa-eye me-2"></i> View All Categories</a>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-menu-pages">
                <div class="list-group list-group-flush">
                    @foreach($mobileNavItems as $mIdx => $mNav)
                        @php
    $mNavActive = $isTreeActive($mNav);
                        @endphp
                        @if(empty($mNav['children']))
                            <a href="{{ $mNav['url'] }}" class="list-group-item list-group-item-action py-3 px-3 fw-semibold border-bottom {{ $mNavActive ? 'active-menu-item' : '' }}">{{ $mNav['title'] }}</a>
                        @else
                            <div class="list-group-item list-group-item-action p-0 border-bottom border-0">
                                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom {{ $mNavActive ? 'active-menu-item' : '' }}">
                                    <a href="{{ $mNav['url'] }}" class="text-decoration-none fw-semibold py-1 flex-grow-1" style="color: inherit;">{{ $mNav['title'] }}</a>
                                    <button class="mob-accordion-btn" type="button" data-bs-toggle="collapse" data-bs-target="#mobNav-{{ $mIdx }}"><i class="fas fa-chevron-down"></i></button>
                                </div>
                                <div class="collapse {{ $mNavActive ? 'show' : '' }}" id="mobNav-{{ $mIdx }}">
                                    <div class="mobile-tree-submenu">
                                        @foreach($mNav['children'] as $smIdx => $sNav)
                                            @php
            $sNavActive = $isTreeActive($sNav);
                                            @endphp
                                            @if(empty($sNav['children']))
                                                <a href="{{ $sNav['url'] }}" class="mobile-tree-item fw-medium {{ $sNavActive ? 'active' : '' }}">{{ $sNav['title'] }}</a>
                                            @else
                                                <div class="d-flex justify-content-between align-items-center pe-2">
                                                    <a href="{{ $sNav['url'] }}" class="mobile-tree-item fw-medium py-2 flex-grow-1 {{ $sNavActive ? 'active' : '' }}">{{ $sNav['title'] }}</a>
                                                    <button class="mob-accordion-btn" type="button" data-bs-toggle="collapse" data-bs-target="#mobSubNav-{{ $mIdx }}-{{ $smIdx }}"><i class="fas fa-chevron-down" style="font-size: 10px;"></i></button>
                                                </div>
                                                <div class="collapse {{ $sNavActive ? 'show' : '' }}" id="mobSubNav-{{ $mIdx }}-{{ $smIdx }}">
                                                    <div class="mobile-tree-submenu">
                                                        @foreach($sNav['children'] as $gNav)
                                                            @php
                    $gNavActive = $isMenuItemActive($gNav['url']);
                                                            @endphp
                                                            <a href="{{ $gNav['url'] }}" class="mobile-tree-item small {{ $gNavActive ? 'active' : '' }}">{{ $gNav['title'] }}</a>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                    @auth
                        @php
    $uMob = auth()->user();
    $isMobStaff = !empty($uMob->is_super_admin)
        || (method_exists($uMob, 'isStaffOrAdmin') && $uMob->isStaffOrAdmin())
        || in_array(str_replace(' ', '_', strtolower(trim((string) $uMob->role))), ['admin', 'super_admin', 'branch_manager', 'staff']);
                        @endphp
                        @if($isMobStaff)
                            <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action py-3 px-3 fw-bold text-danger border-bottom"><i class="fas fa-shield-alt me-2"></i> Admin Panel</a>
                        @endif
                        <a href="{{ route('user.orders') }}" class="list-group-item list-group-item-action py-3 px-3 fw-semibold border-bottom"><i class="fas fa-box-open me-2"></i> Track My Orders</a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Wishlist Notification Modal -->
<div class="modal fade" id="wishlistLimitModal" tabindex="-1" aria-hidden="true" style="z-index: 100000;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-3 border-0 shadow-lg rounded-4">
            <div class="modal-body p-2">
                <div class="mb-2 text-danger">
                    <i class="fas fa-exclamation-circle fa-3x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">Wishlist Limit Reached</h6>
                <p class="text-muted small mb-3" style="font-size: 12px;" id="wishlistLimitModalMsg">
                    You can save a maximum of 100 items to your wishlist. Please remove some items to add new ones.
                </p>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="{{ route('user.wishlist') }}" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold">View Wishlist</a>
                    <button type="button" class="btn btn-light btn-sm rounded-pill px-3 fw-bold border" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-wishlist-toggle');
        if (!btn) return;

        e.preventDefault();
        e.stopPropagation();

        var productId = btn.getAttribute('data-id');
        if (!productId) return;

        var token = document.querySelector('meta[name="csrf-token"]') 
            ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
            : '{{ csrf_token() }}';

        btn.style.pointerEvents = 'none';

        fetch("{{ url('/wishlist/toggle') }}/" + productId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            btn.style.pointerEvents = 'auto';

            if (data.limit_reached) {
                var modalEl = document.getElementById('wishlistLimitModal');
                if (modalEl && window.bootstrap && bootstrap.Modal) {
                    var myModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    var msgEl = document.getElementById('wishlistLimitModalMsg');
                    if (msgEl && data.message) msgEl.innerText = data.message;
                    myModal.show();
                }
                return;
            }

            if (data.status === true || data.status === 'success') {
                var isAdded = data.is_added;
                var count = parseInt(data.count) || 0;

                document.querySelectorAll('.btn-wishlist-toggle[data-id="' + productId + '"]').forEach(function(el) {
                    var heartIcon = el.querySelector('i');
                    if (isAdded) {
                        el.classList.add('is-active');
                        if (el.classList.contains('header-icon-btn')) {
                            el.style.background = '#fee2e2';
                            el.style.color = '#ef4444';
                            el.style.borderColor = '#fca5a5';
                        }
                        if (heartIcon) {
                            heartIcon.className = 'fas fa-heart text-danger';
                        }
                    } else {
                        el.classList.remove('is-active');
                        if (el.classList.contains('header-icon-btn')) {
                            el.style.background = '';
                            el.style.color = '';
                            el.style.borderColor = '';
                        }
                        if (heartIcon) {
                            heartIcon.className = 'far fa-heart';
                        }
                    }
                });

                var badge = document.getElementById('wishlist-counter-badge');
                var savedIcon = document.getElementById('header-saved-icon');
                var savedBtn = document.getElementById('header-saved-btn');

                if (badge) {
                    badge.innerText = count;
                    if (count > 0) {
                        badge.classList.remove('d-none');
                        badge.style.display = 'inline-block';
                    } else {
                        badge.classList.add('d-none');
                        badge.style.display = 'none';
                    }
                }

                if (savedIcon) {
                    if (count > 0) {
                        savedIcon.className = 'fas fa-heart text-warning';
                        if (savedBtn) savedBtn.classList.add('is-active-icon');
                    } else {
                        savedIcon.className = 'far fa-heart';
                        if (savedBtn) savedBtn.classList.remove('is-active-icon');
                    }
                }

                if (!isAdded && window.location.pathname.indexOf('wishlist') !== -1) {
                    var col = document.getElementById('wishlist-col-' + productId) || btn.closest('.col');
                    if (col) {
                        col.style.transition = 'all 0.35s ease';
                        col.style.transform = 'scale(0.85)';
                        col.style.opacity = '0';
                        setTimeout(function() {
                            col.remove();
                            var remaining = document.querySelectorAll('#wishlist-grid-container .col');
                            if (remaining.length === 0) {
                                var grid = document.getElementById('wishlist-grid-container');
                                if (grid) grid.classList.add('d-none');
                                var emptyState = document.getElementById('wishlist-empty-state');
                                if (emptyState) emptyState.classList.remove('d-none');
                            }
                        }, 350);
                    }
                }
            }
        })
        .catch(function(err) {
            btn.style.pointerEvents = 'auto';
            console.error('Wishlist error:', err);
        });
    });

    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('mainHeaderSearchInput');
        const categorySelect = document.getElementById('mainHeaderSearchCategory');
        const resultsBox = document.getElementById('liveSearchResultsBox');
        const resultsList = document.getElementById('liveSearchResultsList');

        let debounceTimer;

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                const query = this.value.trim();
                const categoryId = categorySelect ? categorySelect.value : 'all';

                if (query.length < 2) {
                    resultsBox.classList.add('d-none');
                    return;
                }

                debounceTimer = setTimeout(() => {
                    fetch(`{{ route('api.liveSearch') }}?query=${encodeURIComponent(query)}&category_id=${encodeURIComponent(categoryId)}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success' && data.products && data.products.length > 0) {
                                resultsList.innerHTML = data.products.map(p => `
                                    <div class="search-result-row">
                                        <a href="${p.url}" class="d-flex align-items-center gap-2.5 text-decoration-none text-dark flex-grow-1">
                                            <img src="${p.image}" alt="${p.title}" class="rounded-2 border" style="width: 44px; height: 44px; object-fit: contain;">
                                            <div>
                                                <div class="fw-semibold text-truncate small" style="max-width: 240px;">${p.title}</div>
                                                <div class="d-flex align-items-center gap-2 mt-0.5">
                                                    <span class="fw-bold small bn-digit" style="color: var(--hdr-search-border) !important;">৳${p.price}</span>
                                                    ${p.sku ? `<span class="badge bg-light text-muted border px-1.5 py-0.5" style="font-size: 10px;">SKU: ${p.sku}</span>` : ''}
                                                </div>
                                            </div>
                                        </a>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="search-qty-pill">
                                                <button type="button" class="search-qty-btn" onclick="changeSearchQty(${p.id}, -1)">-</button>
                                                <span id="search-qty-${p.id}" class="small fw-bold px-1.5 bn-digit">1</span>
                                                <button type="button" class="search-qty-btn" onclick="changeSearchQty(${p.id}, 1)">+</button>
                                            </div>
                                            <button type="button" class="btn btn-sm rounded-2 px-2.5 py-1 small header-search-btn" onclick="addSearchItemToCart(${p.id})"><i class="fas fa-cart-plus"></i></button>
                                        </div>
                                    </div>
                                `).join('');
                                resultsBox.classList.remove('d-none');
                            } else {
                                resultsList.innerHTML = '<div class="p-3 text-center text-muted small">No products found matching "' + query + '"</div>';
                                resultsBox.classList.remove('d-none');
                            }
                        }).catch(err => {
                            console.error('Search Error:', err);
                            resultsBox.classList.add('d-none');
                        });
                }, 250);
            });

            document.addEventListener('click', (e) => {
                if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
                    resultsBox.classList.add('d-none');
                }
            });
        }
    });

    function changeSearchQty(id, step) {
        const qtySpan = document.getElementById(`search-qty-${id}`);
        let cur = parseInt(qtySpan.innerText) || 1;
        let next = cur + step;
        if (next >= 1) qtySpan.innerText = next;
    }

    function addSearchItemToCart(id) {
        const qty = parseInt(document.getElementById(`search-qty-${id}`).innerText) || 1;
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const cartAddBaseUrl = "{{ url('/cart/add') }}";

        fetch(`${cartAddBaseUrl}/${id}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ quantity: qty })
        }).then(res => res.json()).then(data => {
            if (data.status === 'success') {
                if (window.updateAllCartBadges) window.updateAllCartBadges(data.cart_count);
                document.getElementById('liveSearchResultsBox')?.classList.add('d-none');
                
                @if($enableFloatingCart)
                    if (window.openFloatingCartDrawer) window.openFloatingCartDrawer();
                @else
                    if (window.showGlobalToast) window.showGlobalToast(data.message, true);
                @endif
            }
        });
    }
</script>