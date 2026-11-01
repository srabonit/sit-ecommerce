@extends('layouts.app')

@php
    /** @var \App\Models\Product $product */
    $finalPrice = (float) ($product->sale_price ?? $product->regular_price ?? 0);
    $regularPrice = (float) ($product->regular_price ?? 0);
    $hasDiscount = !empty($product->sale_price) && ($finalPrice < $regularPrice);
    $discountPercentage = ($hasDiscount && $regularPrice > 0) ? (int) round((($regularPrice - $finalPrice) / $regularPrice) * 100) : 0;

    // Local Safe SVG Fallback
    $svgFallback = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="600" height="600" viewBox="0 0 600 600"><rect fill="%23f8fafc" width="600" height="600"/><text fill="%2394a3b8" font-family="sans-serif" font-size="28" dy="10" font-weight="bold" x="50%" y="50%" text-anchor="middle">No Image</text></svg>';

    // Universal Image Resolver
    $resolveProductImg = function($path, $fallback = null) use ($svgFallback) {
        $defaultFallback = $fallback ?: $svgFallback;
        if (empty($path)) {
            return $defaultFallback;
        }
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path, $defaultFallback);
        }
        if (function_exists('show_image')) {
            return show_image($path, $defaultFallback);
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        return asset(ltrim($path, '/'));
    };

    $mainImg = $resolveProductImg($product->thumbnail);

    // Settings Cache
    static $pdpSettingsCache = null;
    if ($pdpSettingsCache === null) {
        $pdpSettingsCache = \Illuminate\Support\Facades\Cache::remember('product_detail_theme_settings_cache', 86400, function() {
            return DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $pdpSettings = $pdpSettingsCache ?? [];

    $stockOutSetting = $pdpSettings['card_show_stockout_btn'] ?? '1';
    $showStockOutSetting = ($stockOutSetting === '1' || $stockOutSetting == 1);

    // Exact Stock Check
    $actualStock = 0;
    $isActuallyOut = false;

    if (\Illuminate\Support\Facades\Schema::hasTable('product_stocks')) {
        $hasEntry = DB::table('product_stocks')->where('product_id', $product->id)->exists();
        if ($hasEntry) {
            $actualStock = (float) DB::table('product_stocks')->where('product_id', $product->id)->sum('quantity');
        } else {
            $actualStock = (float) ($product->stock ?? ($product->stock_quantity ?? ($product->quantity ?? 0)));
        }
    } else {
        $actualStock = (float) ($product->stock ?? ($product->stock_quantity ?? ($product->quantity ?? 0)));
    }

    $stockStatusStr = !empty($product->stock_status) ? strtolower((string)$product->stock_status) : '';
    if ($actualStock <= 0 || in_array($stockStatusStr, ['out_of_stock', 'outofstock', '0'], true)) {
        $isActuallyOut = true;
    }

    $displayStockOutBtn = ($showStockOutSetting && $isActuallyOut);
    $zoomMode = $uiSettings['image_zoom_mode'] ?? 'lens';

    // Wishlist Status
    $wishlist = session()->get('wishlist', []);
    $isWishlisted = in_array($product->id, $wishlist);
    if (!$isWishlisted && auth()->check() && \Illuminate\Support\Facades\Schema::hasTable('wishlists')) {
        $isWishlisted = DB::table('wishlists')->where('user_id', auth()->id())->where('product_id', $product->id)->exists();
    }

    // Advanced Product Reviews Engine
    $reviewSettings = class_exists(\App\Services\ProductReviewService::class) 
        ? \App\Services\ProductReviewService::resolveProductSettings($product->id)
        : [];

    $showReviewsSetting = $reviewSettings['reviews_enabled'] ?? (($pdpSettings['detail_show_reviews'] ?? '1') == '1');
    $allowReviewSetting = $reviewSettings['allow_new_submission'] ?? (($pdpSettings['detail_allow_review'] ?? '1') == '1');
    $imageReviewsEnabled = $reviewSettings['image_reviews_enabled'] ?? true;
    $maxReviewImages = (int) ($reviewSettings['max_images_per_review'] ?? 4);

    $ratingStats = class_exists(\App\Services\ProductReviewService::class)
        ? \App\Services\ProductReviewService::getProductRatingSummary($product->id)
        : [
            'total_reviews'     => 0,
            'average_rating'    => 5.0,
            'distribution'      => [5=>0, 4=>0, 3=>0, 2=>0, 1=>0],
            'percentages'       => [5=>0, 4=>0, 3=>0, 2=>0, 1=>0],
            'with_images_count' => 0,
            'verified_count'    => 0,
        ];

    $totalReviewCount = $ratingStats['total_reviews'];
    $avgScore = $totalReviewCount > 0 ? (float) $ratingStats['average_rating'] : 5.0;

    $reviewsQuery = DB::table('product_reviews')
        ->join('users', 'product_reviews.user_id', '=', 'users.id')
        ->where('product_reviews.product_id', $product->id)
        ->where('product_reviews.status', 'approved');

    if (\Illuminate\Support\Facades\Schema::hasColumn('product_reviews', 'is_visible')) {
        $reviewsQuery->where('product_reviews.is_visible', 1);
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('product_reviews', 'is_pinned')) {
        $reviewsQuery->orderBy('product_reviews.is_pinned', 'desc')
                     ->orderBy('product_reviews.pin_order', 'asc');
    }

    $reviews = $reviewsQuery->select('product_reviews.*', 'users.name as user_name')
        ->latest('product_reviews.id')
        ->get();

    $myReview = auth()->check() ? DB::table('product_reviews')->where('product_id', $product->id)->where('user_id', auth()->id())->first() : null;

    $isBuyer = false;
    if (auth()->check()) {
        $isBuyer = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.user_id', auth()->id())
            ->where('order_items.product_id', $product->id)
            ->whereIn('orders.order_status', ['delivered', 'completed'])
            ->exists();
    }

    $activePaymentList = ['Cash on Delivery'];
    if (Schema::hasTable('manual_payment_methods')) {
        $mMethods = DB::table('manual_payment_methods')->where('is_active', true)->pluck('name')->toArray();
        $activePaymentList = array_merge($activePaymentList, $mMethods);
    }
    if (Schema::hasTable('payment_gateways')) {
        $gwMethods = DB::table('payment_gateways')->where('is_active', true)->pluck('name')->toArray();
        $activePaymentList = array_merge($activePaymentList, $gwMethods);
    }
    $activePaymentList = array_unique($activePaymentList);

    $showSocial = ($pdpSettings['footer_show_social'] ?? '1') == '1';
    $socialLinksJson = $pdpSettings['footer_social_links_json'] ?? null;
    $socialLinks = !empty($socialLinksJson) ? json_decode($socialLinksJson, true) : [
        ['icon' => 'fab fa-facebook-f', 'url' => '#'],
        ['icon' => 'fab fa-youtube', 'url' => '#'],
        ['icon' => 'fab fa-instagram', 'url' => '#']
    ];

    $storeLogo = $pdpSettings['header_logo'] ?? ($pdpSettings['site_icon'] ?? null);
    $storeLogoUrl = !empty($storeLogo) ? $resolveProductImg($storeLogo, '') : null;

    $rawShortDesc = $product->short_description ?? '';
    $cleanShortDesc = html_entity_decode(stripslashes(str_replace(['\r\n', '\n', '\r'], "\n", (string)$rawShortDesc)));
    $sanitizedShortDesc = strip_tags($cleanShortDesc, ['p', 'br', 'b', 'strong', 'i', 'ul', 'ol', 'li']);

    $rawDesc = $product->description ?? 'Authentic and verified high quality product.';
    $cleanDesc = html_entity_decode(stripslashes(str_replace(['\r\n', '\n', '\r'], "\n", (string)$rawDesc)));

    $hasSpecifications = (!empty($product->weight) && $product->weight > 0) ||
                           (!empty($product->height) && $product->height > 0) ||
                           (!empty($product->width) && $product->width > 0) ||
                           (!empty($product->length) && $product->length > 0) ||
                           !empty($product->sku) ||
                           !empty($product->product_code) ||
                           !empty($product->barcode) ||
                           (isset($product->stock) && $product->stock !== null);

    $variants = ($product->variants && $product->variants->count() > 0)
        ? $product->variants
        : DB::table('product_variants')->where('product_id', $product->id)->get();

    $uniqueColors = $variants->pluck('color')->map(fn($c) => trim((string)$c))->filter()->unique()->values();
    $uniqueSizes  = $variants->pluck('size')->map(fn($s) => trim((string)$s))->filter()->unique()->values();

    $colorImageMap = [];
    foreach ($variants as $vRow) {
        $cKey = strtolower(trim((string)($vRow->color ?? '')));
        if ($cKey !== '' && !empty($vRow->image) && !isset($colorImageMap[$cKey])) {
            $colorImageMap[$cKey] = $resolveProductImg($vRow->image, '');
        }
    }

    $variantsJson = $variants->map(function($v) use ($resolveProductImg, $colorImageMap, $regularPrice, $finalPrice) {
        $cKey = strtolower(trim((string)($v->color ?? '')));
        $vImg = !empty($v->image)
            ? $resolveProductImg($v->image, '')
            : ($colorImageMap[$cKey] ?? null);

        $vRegPrice = (float)(!empty($v->regular_price) && $v->regular_price > 0 ? $v->regular_price : $regularPrice);
        $vSalePrice = (float)(!empty($v->sale_price) && $v->sale_price > 0 ? $v->sale_price : $vRegPrice);

        return [
            'id'            => $v->id,
            'color'         => trim((string)($v->color ?? '')),
            'size'          => trim((string)($v->size ?? '')),
            'price'         => $vSalePrice > 0 ? $vSalePrice : $finalPrice,
            'regular_price' => $vRegPrice > 0 ? $vRegPrice : $regularPrice,
            'stock'         => (int)($v->stock ?? 0),
            'sku'           => $v->product_code ?? ($v->sku ?? ''),
            'barcode'       => $v->barcode ?? '',
            'image'         => $vImg ?: null,
        ];
    })->values();

    $allThumbnails = collect();
    if (!empty($product->galleries) && $product->galleries->count() > 0) {
        foreach($product->galleries as $gallery) {
            $gRaw = $gallery->image_path ?? ($gallery->image ?? null);
            if (!empty($gRaw)) {
                $gResolved = $resolveProductImg($gRaw, '');
                if (!empty($gResolved) && !$allThumbnails->contains($gResolved) && $gResolved !== $mainImg) {
                    $allThumbnails->push($gResolved);
                }
            }
        }
    }
    foreach($variants as $varItem) {
        if (!empty($varItem->image)) {
            $vPath = $resolveProductImg($varItem->image, '');
            if (!empty($vPath) && !$allThumbnails->contains($vPath) && $vPath !== $mainImg) {
                $allThumbnails->push($vPath);
            }
        }
    }

    $pdpPageWrapperBg   = $pdpSettings['color_pdp_wrapper_bg'] ?? '#f8fafc';
    $pdpCardBg          = $pdpSettings['color_pdp_card_bg'] ?? '#ffffff';
    $pdpCardBorder      = $pdpSettings['color_pdp_card_border'] ?? '#edf2f7';
    $pdpZoomBg          = $pdpSettings['color_pdp_zoom_bg'] ?? '#ffffff';
    $pdpZoomBorder      = $pdpSettings['color_pdp_zoom_border'] ?? '#f1f5f9';
    $pdpLensBorder      = $pdpSettings['color_pdp_lens_border'] ?? '#2563eb';

    $pdpThumbBorder     = $pdpSettings['color_pdp_thumb_border'] ?? '#e2e8f0';
    $pdpThumbActiveBrd  = $pdpSettings['color_pdp_thumb_active_border'] ?? '#2563eb';
    $pdpThumbArrowBg    = $pdpSettings['color_pdp_thumb_arrow_bg'] ?? '#f8fafc';
    $pdpThumbArrowText  = $pdpSettings['color_pdp_thumb_arrow_text'] ?? '#475569';
    $pdpThumbArrowHovBg = $pdpSettings['color_pdp_thumb_arrow_hover_bg'] ?? '#2563eb';
    $pdpThumbArrowHovTxt= $pdpSettings['color_pdp_thumb_arrow_hover_text'] ?? '#ffffff';

    $pdpDiscountBg      = $pdpSettings['color_pdp_discount_bg'] ?? '#ef4444';
    $pdpDiscountText    = $pdpSettings['color_pdp_discount_text'] ?? '#ffffff';

    $pdpBreadcrumbLink  = $pdpSettings['color_pdp_breadcrumb_link'] ?? '#64748b';
    $pdpBreadcrumbHover = $pdpSettings['color_pdp_breadcrumb_hover'] ?? '#2563eb';
    $pdpBreadcrumbActive= $pdpSettings['color_pdp_breadcrumb_active'] ?? '#0f172a';

    $pdpTitle           = $pdpSettings['color_pdp_title'] ?? '#0f172a';
    $pdpRatingStar      = $pdpSettings['color_pdp_rating_star'] ?? '#f59e0b';
    $pdpRatingText      = $pdpSettings['color_pdp_rating_text'] ?? '#64748b';

    $pdpActionBtnBg     = $pdpSettings['color_pdp_action_btn_bg'] ?? '#ffffff';
    $pdpActionBtnBorder = $pdpSettings['color_pdp_action_btn_border'] ?? '#e2e8f0';
    $pdpActionBtnIcon   = $pdpSettings['color_pdp_action_btn_icon'] ?? '#64748b';
    $pdpActionBtnHovBg  = $pdpSettings['color_pdp_action_btn_hover_bg'] ?? '#f8fafc';
    $pdpActionBtnHovBrd = $pdpSettings['color_pdp_action_btn_hover_border'] ?? '#cbd5e1';
    $pdpActionBtnHovIco = $pdpSettings['color_pdp_action_btn_hover_icon'] ?? '#2563eb';
    $pdpActionBtnActBg  = $pdpSettings['color_pdp_action_btn_active_bg'] ?? '#fee2e2';
    $pdpActionBtnActBrd = $pdpSettings['color_pdp_action_btn_active_border'] ?? '#fca5a5';
    $pdpActionBtnActIco = $pdpSettings['color_pdp_action_btn_active_icon'] ?? '#ef4444';

    $pdpSkuBadgeBg      = $pdpSettings['color_pdp_sku_badge_bg'] ?? '#f8fafc';
    $pdpSkuBadgeText    = $pdpSettings['color_pdp_sku_badge_text'] ?? '#2563eb';
    $pdpSkuBadgeBorder  = $pdpSettings['color_pdp_sku_badge_border'] ?? '#e2e8f0';

    $pdpStockInBg       = $pdpSettings['color_pdp_stock_in_bg'] ?? '#ecfdf5';
    $pdpStockInText     = $pdpSettings['color_pdp_stock_in_text'] ?? '#059669';
    $pdpStockInBorder   = $pdpSettings['color_pdp_stock_in_border'] ?? '#a7f3d0';

    $pdpStockOutBg      = $pdpSettings['color_pdp_stock_out_bg'] ?? '#fef2f2';
    $pdpStockOutText    = $pdpSettings['color_pdp_stock_out_text'] ?? '#dc2626';
    $pdpStockOutBorder  = $pdpSettings['color_pdp_stock_out_border'] ?? '#fecaca';

    $pdpStockPreBg      = $pdpSettings['color_pdp_stock_pre_bg'] ?? '#fffbeb';
    $pdpStockPreText    = $pdpSettings['color_pdp_stock_pre_text'] ?? '#d97706';
    $pdpStockPreBorder  = $pdpSettings['color_pdp_stock_pre_border'] ?? '#fde68a';

    $pdpPriceBoxBg      = $pdpSettings['color_pdp_price_box_bg'] ?? '#f8fafc';
    $pdpPriceBoxBorder  = $pdpSettings['color_pdp_price_box_border'] ?? '#edf2f7';
    $pdpPrice           = $pdpSettings['color_pdp_price'] ?? '#dc2626';
    $pdpOldPrice        = $pdpSettings['color_pdp_old_price'] ?? '#94a3b8';
    $pdpSavingBg        = $pdpSettings['color_pdp_saving_bg'] ?? '#fee2e2';
    $pdpSavingText      = $pdpSettings['color_pdp_saving_text'] ?? '#dc2626';
    $pdpEstimatedText   = $pdpSettings['color_pdp_estimated_text'] ?? '#475569';

    $pdpShortDescBg     = $pdpSettings['color_pdp_short_desc_bg'] ?? '#fdfdfd';
    $pdpShortDescBorder = $pdpSettings['color_pdp_short_desc_border'] ?? '#2563eb';
    $pdpShortDescText   = $pdpSettings['color_pdp_short_desc_text'] ?? '#475569';

    $pdpSwatchLabel     = $pdpSettings['color_pdp_swatch_label'] ?? '#475569';
    $pdpSwatchActiveLbl = $pdpSettings['color_pdp_swatch_active_label'] ?? '#2563eb';
    $pdpSwatchBg        = $pdpSettings['color_pdp_swatch_bg'] ?? '#ffffff';
    $pdpSwatchText      = $pdpSettings['color_pdp_swatch_text'] ?? '#1e293b';
    $pdpSwatchBorder    = $pdpSettings['color_pdp_swatch_border'] ?? '#cbd5e1';
    $pdpSwatchHovBg     = $pdpSettings['color_pdp_swatch_hover_bg'] ?? '#f8fafc';
    $pdpSwatchHovBrd    = $pdpSettings['color_pdp_swatch_hover_border'] ?? '#94a3b8';
    $pdpSwatchActBg     = $pdpSettings['color_pdp_swatch_active_bg'] ?? '#eff6ff';
    $pdpSwatchActText   = $pdpSettings['color_pdp_swatch_active_text'] ?? '#2563eb';
    $pdpSwatchActBorder = $pdpSettings['color_pdp_swatch_active_border'] ?? '#2563eb';

    $pdpQtyLabel        = $pdpSettings['color_pdp_qty_label'] ?? '#0f172a';
    $pdpQtyBorder       = $pdpSettings['color_pdp_qty_border'] ?? '#cbd5e1';
    $pdpQtyBtnBg        = $pdpSettings['color_pdp_qty_btn_bg'] ?? '#f8fafc';
    $pdpQtyBtnText      = $pdpSettings['color_pdp_qty_btn_text'] ?? '#334155';
    $pdpQtyBtnHovBg     = $pdpSettings['color_pdp_qty_btn_hover_bg'] ?? '#e2e8f0';
    $pdpQtyInputBg      = $pdpSettings['color_pdp_qty_input_bg'] ?? '#ffffff';
    $pdpQtyInputText    = $pdpSettings['color_pdp_qty_input_text'] ?? '#0f172a';

    $pdpBuyNowBg        = $pdpSettings['color_pdp_buynow_bg'] ?? '#0dcaf0';
    $pdpBuyNowText      = $pdpSettings['color_pdp_buynow_text'] ?? '#ffffff';
    $pdpBuyNowBorder    = $pdpSettings['color_pdp_buynow_border'] ?? 'transparent';
    $pdpBuyNowIcon      = $pdpSettings['color_pdp_buynow_icon'] ?? '#ffffff';
    $pdpBuyNowHovBg     = $pdpSettings['color_pdp_buynow_hover_bg'] ?? '#0bb5d8';
    $pdpBuyNowHovText   = $pdpSettings['color_pdp_buynow_hover_text'] ?? '#ffffff';

    $pdpCartBg          = $pdpSettings['color_pdp_cart_bg'] ?? '#ffc107';
    $pdpCartText        = $pdpSettings['color_pdp_cart_text'] ?? '#212529';
    $pdpCartBorder      = $pdpSettings['color_pdp_cart_border'] ?? 'transparent';
    $pdpCartIcon        = $pdpSettings['color_pdp_cart_icon'] ?? '#212529';
    $pdpCartHovBg       = $pdpSettings['color_pdp_cart_hover_bg'] ?? '#e0a800';
    $pdpCartHovText     = $pdpSettings['color_pdp_cart_hover_text'] ?? '#212529';

    $pdpWaBg            = $pdpSettings['color_pdp_wa_bg'] ?? '#ffffff';
    $pdpWaText          = $pdpSettings['color_pdp_wa_text'] ?? '#16a34a';
    $pdpWaBorder        = $pdpSettings['color_pdp_wa_border'] ?? '#22c55e';
    $pdpWaIcon          = $pdpSettings['color_pdp_wa_icon'] ?? '#22c55e';
    $pdpWaHovBg         = $pdpSettings['color_pdp_wa_hover_bg'] ?? '#22c55e';
    $pdpWaHovText       = $pdpSettings['color_pdp_wa_hover_text'] ?? '#ffffff';

    $pdpStockoutBg      = $pdpSettings['color_pdp_stockout_bg'] ?? '#fee2e2';
    $pdpStockoutText    = $pdpSettings['color_pdp_stockout_text'] ?? '#dc2626';
    $pdpStockoutBorder  = $pdpSettings['color_pdp_stockout_border'] ?? '#fca5a5';
    $pdpStockoutIcon    = $pdpSettings['color_pdp_stockout_icon'] ?? '#dc2626';

    $pdpSidebarBg       = $pdpSettings['color_pdp_sidebar_bg'] ?? '#ffffff';
    $pdpSidebarBorder   = $pdpSettings['color_pdp_sidebar_border'] ?? '#edf2f7';
    $pdpSidebarBrand    = $pdpSettings['color_pdp_sidebar_brand_title'] ?? '#0f172a';
    $pdpSidebarVerified = $pdpSettings['color_pdp_sidebar_verified_badge'] ?? '#16a34a';
    $pdpSidebarHeading  = $pdpSettings['color_pdp_sidebar_heading'] ?? '#475569';
    $pdpSidebarFeatIcoBg= $pdpSettings['color_pdp_sidebar_feature_icon_bg'] ?? '#eff6ff';
    $pdpSidebarFeatIco  = $pdpSettings['color_pdp_sidebar_feature_icon'] ?? '#2563eb';
    $pdpSidebarFeatTxt  = $pdpSettings['color_pdp_sidebar_feature_text'] ?? '#475569';

    $pdpSidebarSocBg    = $pdpSettings['color_pdp_sidebar_social_bg'] ?? '#ffffff';
    $pdpSidebarSocBrd   = $pdpSettings['color_pdp_sidebar_social_border'] ?? '#cbd5e1';
    $pdpSidebarSocIco   = $pdpSettings['color_pdp_sidebar_social_icon'] ?? '#64748b';
    $pdpSidebarSocHovBg = $pdpSettings['color_pdp_sidebar_social_hover_bg'] ?? '#2563eb';
    $pdpSidebarSocHovBrd= $pdpSettings['color_pdp_sidebar_social_hover_border'] ?? '#2563eb';
    $pdpSidebarSocHovIco= $pdpSettings['color_pdp_sidebar_social_hover_icon'] ?? '#ffffff';

    $pdpTabBorder       = $pdpSettings['color_pdp_tab_border'] ?? '#e2e8f0';
    $pdpTabLinkText     = $pdpSettings['color_pdp_tab_link_text'] ?? '#64748b';
    $pdpTabLinkActText  = $pdpSettings['color_pdp_tab_link_active_text'] ?? '#2563eb';
    $pdpTabLinkActBrd   = $pdpSettings['color_pdp_tab_link_active_border'] ?? '#2563eb';
    $pdpDescContentText = $pdpSettings['color_pdp_desc_text'] ?? '#334155';

    $pdpSpecThBg        = $pdpSettings['color_pdp_spec_th_bg'] ?? '#f8fafc';
    $pdpSpecThText      = $pdpSettings['color_pdp_spec_th_text'] ?? '#0f172a';
    $pdpSpecTdBg        = $pdpSettings['color_pdp_spec_td_bg'] ?? '#ffffff';
    $pdpSpecTdText      = $pdpSettings['color_pdp_spec_td_text'] ?? '#334155';
    $pdpSpecBorder      = $pdpSettings['color_pdp_spec_border'] ?? '#e2e8f0';
@endphp

@section('title', ($product->title ?? $product->name) . ' - ' . config('app.name', 'Shop Now BD'))

@section('content')
    <style>
        :root {
            --pdp-wrapper-bg: {{ $pdpPageWrapperBg }};
            --pdp-card-bg: {{ $pdpCardBg }};
            --pdp-card-border: {{ $pdpCardBorder }};
            --pdp-zoom-bg: {{ $pdpZoomBg }};
            --pdp-zoom-border: {{ $pdpZoomBorder }};
            --pdp-lens-border: {{ $pdpLensBorder }};
            --pdp-thumb-border: {{ $pdpThumbBorder }};
            --pdp-thumb-active-border: {{ $pdpThumbActiveBrd }};
            --pdp-thumb-arrow-bg: {{ $pdpThumbArrowBg }};
            --pdp-thumb-arrow-text: {{ $pdpThumbArrowText }};
            --pdp-thumb-arrow-hover-bg: {{ $pdpThumbArrowHovBg }};
            --pdp-thumb-arrow-hover-text: {{ $pdpThumbArrowHovTxt }};
            --pdp-discount-bg: {{ $pdpDiscountBg }};
            --pdp-discount-text: {{ $pdpDiscountText }};
            --pdp-breadcrumb-link: {{ $pdpBreadcrumbLink }};
            --pdp-breadcrumb-hover: {{ $pdpBreadcrumbHover }};
            --pdp-breadcrumb-active: {{ $pdpBreadcrumbActive }};
            --pdp-title: {{ $pdpTitle }};
            --pdp-rating-star: {{ $pdpRatingStar }};
            --pdp-rating-text: {{ $pdpRatingText }};
            --pdp-action-btn-bg: {{ $pdpActionBtnBg }};
            --pdp-action-btn-border: {{ $pdpActionBtnBorder }};
            --pdp-action-btn-icon: {{ $pdpActionBtnIcon }};
            --pdp-action-btn-hover-bg: {{ $pdpActionBtnHovBg }};
            --pdp-action-btn-hover-border: {{ $pdpActionBtnHovBrd }};
            --pdp-action-btn-hover-icon: {{ $pdpActionBtnHovIco }};
            --pdp-action-btn-active-bg: {{ $pdpActionBtnActBg }};
            --pdp-action-btn-active-border: {{ $pdpActionBtnActBrd }};
            --pdp-action-btn-active-icon: {{ $pdpActionBtnActIco }};
            --pdp-sku-badge-bg: {{ $pdpSkuBadgeBg }};
            --pdp-sku-badge-text: {{ $pdpSkuBadgeText }};
            --pdp-sku-badge-border: {{ $pdpSkuBadgeBorder }};
            --pdp-stock-in-bg: {{ $pdpStockInBg }};
            --pdp-stock-in-text: {{ $pdpStockInText }};
            --pdp-stock-in-border: {{ $pdpStockInBorder }};
            --pdp-stock-out-bg: {{ $pdpStockOutBg }};
            --pdp-stock-out-text: {{ $pdpStockOutText }};
            --pdp-stock-out-border: {{ $pdpStockOutBorder }};
            --pdp-stock-pre-bg: {{ $pdpStockPreBg }};
            --pdp-stock-pre-text: {{ $pdpStockPreText }};
            --pdp-stock-pre-border: {{ $pdpStockPreBorder }};
            --pdp-price-box-bg: {{ $pdpPriceBoxBg }};
            --pdp-price-box-border: {{ $pdpPriceBoxBorder }};
            --pdp-price: {{ $pdpPrice }};
            --pdp-old-price: {{ $pdpOldPrice }};
            --pdp-saving-bg: {{ $pdpSavingBg }};
            --pdp-saving-text: {{ $pdpSavingText }};
            --pdp-estimated-text: {{ $pdpEstimatedText }};
            --pdp-short-desc-bg: {{ $pdpShortDescBg }};
            --pdp-short-desc-border: {{ $pdpShortDescBorder }};
            --pdp-short-desc-text: {{ $pdpShortDescText }};
            --pdp-swatch-label: {{ $pdpSwatchLabel }};
            --pdp-swatch-active-label: {{ $pdpSwatchActiveLbl }};
            --pdp-swatch-bg: {{ $pdpSwatchBg }};
            --pdp-swatch-text: {{ $pdpSwatchText }};
            --pdp-swatch-border: {{ $pdpSwatchBorder }};
            --pdp-swatch-hover-bg: {{ $pdpSwatchHovBg }};
            --pdp-swatch-hover-border: {{ $pdpSwatchHovBrd }};
            --pdp-swatch-active-bg: {{ $pdpSwatchActBg }};
            --pdp-swatch-active-text: {{ $pdpSwatchActText }};
            --pdp-swatch-active-border: {{ $pdpSwatchActBorder }};
            --pdp-qty-label: {{ $pdpQtyLabel }};
            --pdp-qty-border: {{ $pdpQtyBorder }};
            --pdp-qty-btn-bg: {{ $pdpQtyBtnBg }};
            --pdp-qty-btn-text: {{ $pdpQtyBtnText }};
            --pdp-qty-btn-hover-bg: {{ $pdpQtyBtnHovBg }};
            --pdp-qty-input-bg: {{ $pdpQtyInputBg }};
            --pdp-qty-input-text: {{ $pdpQtyInputText }};
            --pdp-buynow-bg: {{ $pdpBuyNowBg }};
            --pdp-buynow-text: {{ $pdpBuyNowText }};
            --pdp-buynow-border: {{ $pdpBuyNowBorder }};
            --pdp-buynow-icon: {{ $pdpBuyNowIcon }};
            --pdp-buynow-hover-bg: {{ $pdpBuyNowHovBg }};
            --pdp-buynow-hover-text: {{ $pdpBuyNowHovText }};
            --pdp-cart-bg: {{ $pdpCartBg }};
            --pdp-cart-text: {{ $pdpCartText }};
            --pdp-cart-border: {{ $pdpCartBorder }};
            --pdp-cart-icon: {{ $pdpCartIcon }};
            --pdp-cart-hover-bg: {{ $pdpCartHovBg }};
            --pdp-cart-hover-text: {{ $pdpCartHovText }};
            --pdp-wa-bg: {{ $pdpWaBg }};
            --pdp-wa-text: {{ $pdpWaText }};
            --pdp-wa-border: {{ $pdpWaBorder }};
            --pdp-wa-icon: {{ $pdpWaIcon }};
            --pdp-wa-hover-bg: {{ $pdpWaHovBg }};
            --pdp-wa-hover-text: {{ $pdpWaHovText }};
            --pdp-stockout-bg: {{ $pdpStockoutBg }};
            --pdp-stockout-text: {{ $pdpStockoutText }};
            --pdp-stockout-border: {{ $pdpStockoutBorder }};
            --pdp-stockout-icon: {{ $pdpStockoutIcon }};
            --pdp-sidebar-bg: {{ $pdpSidebarBg }};
            --pdp-sidebar-border: {{ $pdpSidebarBorder }};
            --pdp-sidebar-brand: {{ $pdpSidebarBrand }};
            --pdp-sidebar-verified: {{ $pdpSidebarVerified }};
            --pdp-sidebar-heading: {{ $pdpSidebarHeading }};
            --pdp-sidebar-feature-icon-bg: {{ $pdpSidebarFeatIcoBg }};
            --pdp-sidebar-feature-icon: {{ $pdpSidebarFeatIco }};
            --pdp-sidebar-feature-text: {{ $pdpSidebarFeatTxt }};
            --pdp-sidebar-social-bg: {{ $pdpSidebarSocBg }};
            --pdp-sidebar-social-border: {{ $pdpSidebarSocBrd }};
            --pdp-sidebar-social-icon: {{ $pdpSidebarSocIco }};
            --pdp-sidebar-social-hover-bg: {{ $pdpSidebarSocHovBg }};
            --pdp-sidebar-social-hover-border: {{ $pdpSidebarSocHovBrd }};
            --pdp-sidebar-social-hover-icon: {{ $pdpSidebarSocHovIco }};
            --pdp-tab-border: {{ $pdpTabBorder }};
            --pdp-tab-link-text: {{ $pdpTabLinkText }};
            --pdp-tab-link-active-text: {{ $pdpTabLinkActText }};
            --pdp-tab-link-active-border: {{ $pdpTabLinkActBrd }};
            --pdp-desc-text: {{ $pdpDescContentText }};
            --pdp-spec-th-bg: {{ $pdpSpecThBg }};
            --pdp-spec-th-text: {{ $pdpSpecThText }};
            --pdp-spec-td-bg: {{ $pdpSpecTdBg }};
            --pdp-spec-td-text: {{ $pdpSpecTdText }};
            --pdp-spec-border: {{ $pdpSpecBorder }};
        }

        /* Strict 5px Element Radius Across All Controls */
        #single-product-page-wrapper .btn,
        #single-product-page-wrapper .btn-sm,
        #single-product-page-wrapper button,
        #single-product-page-wrapper .badge,
        #single-product-page-wrapper .alert,
        #single-product-page-wrapper .form-control,
        #single-product-page-wrapper .form-select,
        #single-product-page-wrapper .input-group-text,
        #single-product-page-wrapper .product-main-card,
        #single-product-page-wrapper .store-sidebar-card,
        #single-product-page-wrapper .zoom-container,
        #single-product-page-wrapper .gallery-thumb-item,
        #single-product-page-wrapper .price-banner-box,
        #single-product-page-wrapper .swatch-chip,
        #single-product-page-wrapper .qty-stepper-wrap,
        #single-product-page-wrapper .btn-buy-now-vibrant,
        #single-product-page-wrapper .btn-add-cart-vibrant,
        #single-product-page-wrapper .btn-whatsapp-vibrant,
        #single-product-page-wrapper .btn-stockout-vibrant,
        #single-product-page-wrapper .rating-overview-card,
        #single-product-page-wrapper .mini-product-card,
        .pdp-spec-table,
        .modal-content,
        .modal .btn {
            border-radius: 5px !important;
        }

        .bn-digit,
        .font-monospace {
            font-weight: 700 !important;
            font-variant-numeric: tabular-nums;
        }

        #single-product-page-wrapper {
            background-color: var(--pdp-wrapper-bg) !important;
        }
        .product-details-container {
            max-width: 1260px;
            margin: 0 auto;
        }
        .product-main-card {
            background-color: var(--pdp-card-bg) !important;
            border: 1px solid var(--pdp-card-border) !important;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
            padding: 16px;
        }

        .zoom-container {
            position: relative;
            overflow: hidden;
            background-color: var(--pdp-zoom-bg) !important;
            border: 1px solid var(--pdp-zoom-border) !important;
            cursor: crosshair;
            aspect-ratio: 1 / 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .zoom-container img {
            transition: transform 0.2s ease-out;
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            display: block;
        }
        .zoom-mode-inner:hover img {
            transform: scale(1.5);
        }
        .zoom-lens {
            position: absolute;
            border: 2px solid var(--pdp-lens-border) !important;
            width: 110px;
            height: 110px;
            background-repeat: no-repeat;
            display: none;
            pointer-events: none;
            border-radius: 50% !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            z-index: 10;
        }

        .thumb-slider-outer {
            position: relative;
            display: flex;
            align-items: center;
            gap: 4px;
            width: 100%;
        }
        .thumb-nav-arrow {
            width: 24px;
            height: 52px;
            background-color: var(--pdp-thumb-arrow-bg) !important;
            border: 1px solid var(--pdp-thumb-border) !important;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--pdp-thumb-arrow-text) !important;
            font-size: 11px;
            flex-shrink: 0;
            transition: all 0.15s ease;
        }
        .thumb-nav-arrow:hover {
            background-color: var(--pdp-thumb-arrow-hover-bg) !important;
            color: var(--pdp-thumb-arrow-hover-text) !important;
            border-color: var(--pdp-thumb-arrow-hover-bg) !important;
        }
        .gallery-thumbs-scroll {
            display: flex;
            gap: 6px;
            overflow-x: auto;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f8fafc;
            padding-bottom: 3px;
            flex-grow: 1;
        }
        .gallery-thumbs-scroll::-webkit-scrollbar {
            height: 3px;
        }
        .gallery-thumbs-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        .gallery-thumb-item {
            width: 52px;
            height: 52px;
            border: 1.5px solid var(--pdp-thumb-border) !important;
            padding: 2px;
            object-fit: contain;
            background: #ffffff;
            cursor: pointer;
            transition: all 0.15s ease;
            flex-shrink: 0;
        }
        .gallery-thumb-item:hover, .gallery-thumb-item.active-thumb {
            border-color: var(--pdp-thumb-active-border) !important;
            transform: translateY(-1px);
        }

        .badge-discount-tag {
            position: absolute;
            top: 8px;
            left: 8px;
            background-color: var(--pdp-discount-bg) !important;
            color: var(--pdp-discount-text) !important;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 7px;
            z-index: 3;
            letter-spacing: 0.3px;
        }

        .product-breadcrumb {
            font-size: 11px;
            font-weight: 600;
        }
        .product-breadcrumb a {
            color: var(--pdp-breadcrumb-link) !important;
            text-decoration: none;
            transition: color 0.15s ease;
        }
        .product-breadcrumb a:hover {
            color: var(--pdp-breadcrumb-hover) !important;
        }
        .product-breadcrumb .breadcrumb-item.active {
            color: var(--pdp-breadcrumb-active) !important;
        }

        .product-title-text {
            font-size: 18px;
            font-weight: 700;
            color: var(--pdp-title) !important;
            line-height: 1.35;
            letter-spacing: -0.01em;
        }
        @media (min-width: 768px) {
            .product-title-text {
                font-size: 22px;
            }
        }

        .product-rating-action-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
            border-bottom: 1px solid var(--pdp-card-border);
            padding-bottom: 6px;
            min-height: 34px;
        }
        .rating-stars {
            color: var(--pdp-rating-star) !important;
            font-size: 11px;
            display: inline-flex;
            align-items: center;
            gap: 2px;
        }
        .rating-stars i {
            color: var(--pdp-rating-star) !important;
        }
        .rating-count-text {
            color: var(--pdp-rating-text) !important;
            font-size: 11px;
            font-weight: 600;
        }

        .product-header-actions {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-left: auto;
        }
        .header-icon-btn {
            width: 30px;
            height: 30px;
            border: 1px solid var(--pdp-action-btn-border) !important;
            background-color: var(--pdp-action-btn-bg) !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--pdp-action-btn-icon) !important;
            font-size: 11px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .header-icon-btn i {
            color: var(--pdp-action-btn-icon) !important;
            transition: color 0.15s ease;
        }
        .header-icon-btn:hover {
            background-color: var(--pdp-action-btn-hover-bg) !important;
            border-color: var(--pdp-action-btn-hover-border) !important;
            color: var(--pdp-action-btn-hover-icon) !important;
        }
        .header-icon-btn:hover i {
            color: var(--pdp-action-btn-hover-icon) !important;
        }
        .header-icon-btn.is-active {
            background-color: var(--pdp-action-btn-active-bg) !important;
            color: var(--pdp-action-btn-active-icon) !important;
            border-color: var(--pdp-action-btn-active-border) !important;
        }
        .header-icon-btn.is-active i {
            color: var(--pdp-action-btn-active-icon) !important;
        }

        .pdp-sku-badge {
            background-color: var(--pdp-sku-badge-bg) !important;
            color: var(--pdp-sku-badge-text) !important;
            border: 1px solid var(--pdp-sku-badge-border) !important;
            font-size: 10px;
        }
        .pdp-stock-in-badge {
            background-color: var(--pdp-stock-in-bg) !important;
            color: var(--pdp-stock-in-text) !important;
            border: 1px solid var(--pdp-stock-in-border) !important;
            font-size: 10px;
        }
        .pdp-stock-out-badge {
            background-color: var(--pdp-stock-out-bg) !important;
            color: var(--pdp-stock-out-text) !important;
            border: 1px solid var(--pdp-stock-out-border) !important;
            font-size: 10px;
        }
        .pdp-stock-pre-badge {
            background-color: var(--pdp-stock-pre-bg) !important;
            color: var(--pdp-stock-pre-text) !important;
            border: 1px solid var(--pdp-stock-pre-border) !important;
            font-size: 10px;
        }

        .price-banner-box {
            background-color: var(--pdp-price-box-bg) !important;
            border: 1px solid var(--pdp-price-box-border) !important;
            padding: 8px 12px;
        }
        .current-active-price {
            font-size: 22px;
            font-weight: 800;
            color: var(--pdp-price) !important;
            letter-spacing: -0.02em;
            line-height: 1;
        }
        .old-strike-price {
            font-size: 13px;
            color: var(--pdp-old-price) !important;
            text-decoration: line-through;
            font-weight: 600;
        }
        .saving-chip {
            background-color: var(--pdp-saving-bg) !important;
            color: var(--pdp-saving-text) !important;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 6px;
        }
        .calculated-total-line {
            color: var(--pdp-estimated-text) !important;
            font-size: 11px;
        }
        .calculated-total-line strong {
            color: var(--pdp-title) !important;
        }

        .product-short-desc-card {
            background-color: var(--pdp-short-desc-bg) !important;
            border-left: 3px solid var(--pdp-short-desc-border) !important;
            padding: 8px 12px;
            font-size: 12px;
            line-height: 1.5;
            color: var(--pdp-short-desc-text) !important;
        }
        .product-short-desc-card ul, .product-short-desc-card ol {
            padding-left: 1rem;
            margin-bottom: 0;
        }
        .product-short-desc-card li {
            margin-bottom: 2px;
            color: var(--pdp-short-desc-text) !important;
        }

        .swatch-group-wrap {
            margin-top: 10px;
            margin-bottom: 10px;
        }
        .swatch-group-label {
            font-size: 11px;
            font-weight: 700;
            color: var(--pdp-swatch-label) !important;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .swatch-active-name {
            color: var(--pdp-swatch-active-label) !important;
        }
        .swatches-flex-container {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .swatch-chip {
            cursor: pointer;
            border: 1.5px solid var(--pdp-swatch-border) !important;
            padding: 5px 12px;
            font-size: 12px;
            font-weight: 600;
            color: var(--pdp-swatch-text) !important;
            background-color: var(--pdp-swatch-bg) !important;
            transition: all 0.15s ease;
            user-select: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            min-height: 32px;
        }
        .swatch-chip:hover {
            background-color: var(--pdp-swatch-hover-bg) !important;
            border-color: var(--pdp-swatch-hover-border) !important;
            transform: translateY(-1px);
        }
        .swatch-chip.active {
            border-color: var(--pdp-swatch-active-border) !important;
            background-color: var(--pdp-swatch-active-bg) !important;
            color: var(--pdp-swatch-active-text) !important;
            box-shadow: 0 1px 3px rgba(37, 99, 235, 0.2);
        }
        .swatch-mini-thumb {
            width: 18px;
            height: 18px;
            object-fit: cover;
            border: 1px solid var(--pdp-card-border);
            border-radius: 3px !important;
        }

        .product-qty-section {
            margin-top: 12px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .product-qty-section label {
            color: var(--pdp-qty-label) !important;
            font-size: 11px;
            font-weight: 700;
        }
        .qty-stepper-wrap {
            display: inline-flex;
            align-items: center;
            border: 1px solid var(--pdp-qty-border) !important;
            background-color: var(--pdp-qty-input-bg) !important;
            overflow: hidden;
            height: 32px;
        }
        .qty-btn {
            border: none;
            background-color: var(--pdp-qty-btn-bg) !important;
            color: var(--pdp-qty-btn-text) !important;
            width: 30px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            transition: background-color 0.15s ease;
        }
        .qty-btn:hover {
            background-color: var(--pdp-qty-btn-hover-bg) !important;
        }
        .qty-input-field {
            width: 38px;
            height: 32px;
            border: none;
            text-align: center;
            font-weight: 700;
            font-size: 12px;
            color: var(--pdp-qty-input-text) !important;
            background-color: var(--pdp-qty-input-bg) !important;
        }
        .qty-input-field:focus {
            outline: none;
        }

        .action-btns-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
            width: 100%;
            margin-bottom: 6px;
        }

        .btn-buy-now-vibrant {
            background-color: var(--pdp-buynow-bg) !important;
            border: 1px solid var(--pdp-buynow-border) !important;
            color: var(--pdp-buynow-text) !important;
            font-weight: 700 !important;
            font-size: 12px !important;
            height: 36px !important;
            padding: 0 10px !important;
            transition: all 0.15s ease !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            width: 100%;
        }
        .btn-buy-now-vibrant i {
            color: var(--pdp-buynow-icon) !important;
            transition: color 0.15s ease;
        }
        .btn-buy-now-vibrant:hover {
            background-color: var(--pdp-buynow-hover-bg) !important;
            color: var(--pdp-buynow-hover-text) !important;
            transform: translateY(-1px);
        }

        .btn-add-cart-vibrant {
            background-color: var(--pdp-cart-bg) !important;
            border: 1px solid var(--pdp-cart-border) !important;
            color: var(--pdp-cart-text) !important;
            font-weight: 700 !important;
            font-size: 12px !important;
            height: 36px !important;
            padding: 0 10px !important;
            transition: all 0.15s ease !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            width: 100%;
        }
        .btn-add-cart-vibrant i {
            color: var(--pdp-cart-icon) !important;
            transition: color 0.15s ease;
        }
        .btn-add-cart-vibrant:hover {
            background-color: var(--pdp-cart-hover-bg) !important;
            color: var(--pdp-cart-hover-text) !important;
            transform: translateY(-1px);
        }

        .btn-whatsapp-vibrant {
            background-color: var(--pdp-wa-bg) !important;
            color: var(--pdp-wa-text) !important;
            border: 1px solid var(--pdp-wa-border) !important;
            font-weight: 600;
            font-size: 12px;
            height: 34px;
            padding: 0 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
            transition: all 0.15s ease;
            width: 100%;
        }
        .btn-whatsapp-vibrant i {
            color: var(--pdp-wa-icon) !important;
            transition: color 0.15s ease;
        }
        .btn-whatsapp-vibrant:hover {
            background-color: var(--pdp-wa-hover-bg) !important;
            color: var(--pdp-wa-hover-text) !important;
            border-color: var(--pdp-wa-hover-bg) !important;
        }
        .btn-whatsapp-vibrant:hover i {
            color: var(--pdp-wa-hover-text) !important;
        }

.price-banner-box .align-items-baseline #product-active-price {
   font-weight: 700 !important;
    font-family: 'Hind Siliguri' !important;
                                              }
 .btn-stockout-vibrant {
            background-color: var(--pdp-stockout-bg) !important;
            color: var(--pdp-stockout-text) !important;
            border: 1px dashed var(--pdp-stockout-border) !important;
            font-weight: 700 !important;
            font-size: 12px !important;
            height: 36px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            cursor: not-allowed !important;
        }
        .btn-stockout-vibrant i {
            color: var(--pdp-stockout-icon) !important;
        }


        .store-sidebar-card {
            background-color: var(--pdp-sidebar-bg) !important;
            border: 1px solid var(--pdp-sidebar-border) !important;
            padding: 14px;
        }
        .sidebar-brand-box {
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--pdp-sidebar-border);
            margin-bottom: 10px;
        }
        .sidebar-brand-meta h6 {
            color: var(--pdp-sidebar-brand) !important;
        }
        .sidebar-verified-badge {
            display: inline-flex;
            align-items: center;
            color: var(--pdp-sidebar-verified) !important;
            font-weight: 600;
            font-size: 10.5px;
            line-height: 1.2;
            margin-top: 1px;
        }
        .sidebar-heading-text {
            color: var(--pdp-sidebar-heading) !important;
        }
        .sidebar-feature-row {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            font-size: 11.5px;
            color: var(--pdp-sidebar-feature-text) !important;
            margin-bottom: 7px;
        }
        .sidebar-feature-icon {
            width: 22px;
            height: 22px;
            background-color: var(--pdp-sidebar-feature-icon-bg) !important;
            color: var(--pdp-sidebar-feature-icon) !important;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 10px;
        }
        .sidebar-feature-icon i {
            color: var(--pdp-sidebar-feature-icon) !important;
        }

        .sidebar-social-circles {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        .sidebar-social-circle {
            width: 26px;
            height: 26px;
            border: 1px solid var(--pdp-sidebar-social-border) !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--pdp-sidebar-social-icon) !important;
            background-color: var(--pdp-sidebar-social-bg) !important;
            text-decoration: none;
            font-size: 11px;
            transition: all 0.15s ease;
        }
        .sidebar-social-circle i {
            color: var(--pdp-sidebar-social-icon) !important;
            transition: color 0.15s ease;
        }
        .sidebar-social-circle:hover {
            background-color: var(--pdp-sidebar-social-hover-bg) !important;
            color: var(--pdp-sidebar-social-hover-icon) !important;
            border-color: var(--pdp-sidebar-social-hover-border) !important;
        }
        .sidebar-social-circle:hover i {
            color: var(--pdp-sidebar-social-hover-icon) !important;
        }

        .product-nav-tabs {
            border-bottom: 1px solid var(--pdp-tab-border) !important;
            padding: 0 4px;
        }
        .product-nav-tabs .nav-link {
            border: none;
            color: var(--pdp-tab-link-text) !important;
            font-weight: 700;
            font-size: 12.5px;
            padding: 9px 16px;
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
            transition: all 0.15s ease;
        }
        .product-nav-tabs .nav-link.active {
            color: var(--pdp-tab-link-active-text) !important;
            border-bottom-color: var(--pdp-tab-link-active-border) !important;
            background: transparent;
        }
        .tab-content-area {
            padding: 14px 6px 6px 6px;
        }
        .product-desc-content {
            font-size: 12.5px;
            line-height: 1.65;
            color: var(--pdp-desc-text) !important;
        }

        /* Specifications Table */
        .pdp-spec-table th {
            background-color: var(--pdp-spec-th-bg) !important;
            color: var(--pdp-spec-th-text) !important;
            border-color: var(--pdp-spec-border) !important;
            font-size: 11.5px;
        }
        .pdp-spec-table td {
            background-color: var(--pdp-spec-td-bg) !important;
            color: var(--pdp-spec-td-text) !important;
            border-color: var(--pdp-spec-border) !important;
            font-size: 11.5px;
        }

        .rating-overview-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            padding: 16px 20px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        }
        .rating-bar-track {
            height: 6px;
            background: #f1f5f9;
            overflow: hidden;
            flex-grow: 1;
        }
        .rating-bar-fill {
            height: 100%;
            background: #2563eb;
            transition: width 0.3s ease;
        }

        .screen-review-card {
            background: #ffffff;
            border-bottom: 1px solid #f1f5f9;
            padding: 14px 2px;
            transition: background-color 0.15s ease;
        }
        .screen-avatar-img {
            width: 38px;
            height: 38px;
            object-fit: cover;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: #94a3b8;
        }
        .screen-reviewer-name {
            font-size: 13.5px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
        }
        .screen-time-text {
            font-size: 11px;
            color: #64748b;
        }
        .screen-star-row {
            display: flex;
            align-items: center;
            gap: 2px;
            color: #f59e0b;
            font-size: 12px;
        }
        .screen-numeric-rating {
            font-size: 11px;
            font-weight: 700;
            color: #0f172a;
            margin-left: 4px;
        }
        .screen-verified-pill {
            background: #ecfdf5;
            color: #059669;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 6px;
            margin-left: 4px;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }
        .screen-comment-body {
            font-size: 12.5px;
            line-height: 1.6;
            color: #334155;
            margin-top: 6px;
            margin-bottom: 6px;
        }
        .review-attached-img {
            width: 52px;
            height: 52px;
            border: 1px solid #e2e8f0;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.15s ease, border-color 0.15s ease;
        }
        .review-attached-img:hover {
            transform: scale(1.05);
            border-color: #2563eb;
        }

        .btn-write-review-outline {
            border: 1.5px solid #2563eb !important;
            color: #2563eb !important;
            background: #ffffff !important;
            font-weight: 700 !important;
            font-size: 13px !important;
            padding: 8px 16px !important;
            width: 100%;
            display: block;
            text-align: center;
            transition: all 0.15s ease;
        }
        .btn-write-review-outline:hover {
            background: #eff6ff !important;
            color: #1d4ed8 !important;
        }

        .btn-edit-pill-badge {
            border: 1px solid #2563eb !important;
            color: #2563eb !important;
            background: #ffffff !important;
            font-size: 10.5px !important;
            font-weight: 600 !important;
            padding: 2px 8px !important;
            display: inline-flex;
            align-items: center;
            gap: 3px;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .btn-edit-pill-badge:hover {
            background: #eff6ff !important;
        }

        .star-picker-wrap {
            display: inline-flex;
            gap: 4px;
            cursor: pointer;
        }
        .star-picker-wrap i {
            font-size: 20px;
            color: #cbd5e1;
            transition: color 0.15s ease;
        }
        .star-picker-wrap i.hovered,
        .star-picker-wrap i.selected {
            color: #f59e0b;
        }

        .mini-slider-container {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            -ms-overflow-style: none;
            padding-bottom: 4px;
        }
        .mini-slider-container::-webkit-scrollbar { display: none; }
        .mini-slider-item {
            flex: 0 0 calc((100% - 24px) / 4);
            min-width: 140px;
        }
        @media (max-width: 991.98px) {
            .mini-slider-item { flex: 0 0 calc((100% - 16px) / 3); min-width: 130px; }
        }
        @media (max-width: 575.98px) {
            .mini-slider-item { flex: 0 0 calc((100% - 8px) / 2); min-width: 120px; }
            .product-main-card {
                padding: 10px 12px;
            }
            .btn-buy-now-vibrant, .btn-add-cart-vibrant {
                height: 38px !important;
                font-size: 11.5px !important;
            }
            .btn-whatsapp-vibrant {
                height: 36px !important;
            }
        }
        .mini-product-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            transition: all 0.15s ease;
        }
        .mini-product-card:hover {
            transform: translateY(-2px);
            border-color: #cbd5e1;
        }
        .mini-product-img-box {
            position: relative;
            aspect-ratio: 1/1;
            background: #f8fafc;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .mini-product-img-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 4px;
        }

        .cart-response-alert {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 8px 12px;
            margin-bottom: 10px;
            font-size: 12px;
        }
    </style>

    <div id="single-product-page-wrapper" class="py-2 py-md-3">
        <div id="single-product-container" class="container product-details-container px-2 px-md-3">

            @if(session('success'))
                <div class="cart-response-alert alert alert-success alert-dismissible fade show bg-success-subtle text-success-emphasis border-success-subtle py-2" role="alert" id="productDetailSuccessAlert">
                    <div class="d-flex align-items-center gap-2 fw-semibold small">
                        <i class="fas fa-check-circle"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="cart-response-alert alert alert-danger alert-dismissible fade show bg-danger-subtle text-danger-emphasis border-danger-subtle py-2" role="alert">
                    <div class="d-flex align-items-center gap-2 fw-semibold small">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($uiSettings['show_breadcrumb'] ?? true)
                <nav id="single-product-breadcrumb-nav" aria-label="breadcrumb" class="mb-2">
                    <ol id="single-product-breadcrumb-list" class="breadcrumb product-breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="fas fa-home me-1"></i>Home</a></li>
                        @if(!empty($product->category))
                            <li class="breadcrumb-item">
                                <a href="{{ route('shop', ['category' => $product->category->slug ?? $product->category->id]) }}">{{ $product->category->name }}</a>
                            </li>
                        @endif
                        <li class="breadcrumb-item active text-truncate fw-semibold" style="max-width: 220px;" aria-current="page">
                            {{ $product->title ?? $product->name }}
                        </li>
                    </ol>
                </nav>
            @endif

            <!-- Main Primary Product Card -->
            <div id="product-primary-card" class="product-main-card mb-3">
                <div id="product-primary-grid" class="row g-3 g-md-4 align-items-start">
                    
                    <!-- 1. Image Gallery Column with Slider Controls -->
                    <div id="product-gallery-column" class="col-12 col-lg-4 col-md-5">
                        <div id="product-main-preview-box" class="zoom-container {{ $zoomMode === 'inner' ? 'zoom-mode-inner' : '' }} mb-2">
                            @if($hasDiscount)
                                <span class="badge-discount-tag font-monospace" id="badge-discount-percent">
                                    -{{ $discountPercentage }}%
                                </span>
                            @endif
                            <img id="product-main-display-image" src="{{ $mainImg }}" alt="{{ $product->title ?? $product->name }}" onerror="this.onerror=null; this.src='{{ $svgFallback }}'">
                            @if($zoomMode === 'lens')
                                <div id="zoom-lens-element" class="zoom-lens"></div>
                            @endif
                        </div>

                        <!-- Thumbnails Slider Outer -->
                        <div class="thumb-slider-outer">
                            <button type="button" class="thumb-nav-arrow" onclick="scrollThumbs(-1)" title="Scroll Left">
                                <i class="fas fa-chevron-left"></i>
                            </button>

                            <div id="product-thumbnails-wrapper" class="gallery-thumbs-scroll">
                                <img id="thumb-main-img" src="{{ $mainImg }}" class="gallery-thumb-item active-thumb" onclick="switchProductMainImage('{{ $mainImg }}', this)" alt="Thumb" onerror="this.onerror=null; this.style.display='none'">
                                @if(!empty($allThumbnails) && $allThumbnails->count() > 0)
                                    @foreach($allThumbnails as $galImg)
                                        <img src="{{ $galImg }}" class="gallery-thumb-item" onclick="switchProductMainImage('{{ $galImg }}', this)" alt="Gallery" onerror="this.onerror=null; this.style.display='none'">
                                    @endforeach
                                @endif
                            </div>

                            <button type="button" class="thumb-nav-arrow" onclick="scrollThumbs(1)" title="Scroll Right">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- 2. Product Information Column -->
                    <div id="product-info-column" class="col-12 {{ ($uiSettings['show_delivery_sidebar'] ?? true) ? 'col-lg-5 col-md-7' : 'col-lg-8 col-md-7' }}">
                        <h1 id="product-title-heading" class="product-title-text mb-1">
                            {{ $product->title ?? $product->name }}
                        </h1>

                        <!-- Ratings & Header Action Buttons -->
                        <div class="product-rating-action-row">
                            @if(($uiSettings['show_review_header'] ?? true) && $showReviewsSetting == '1')
                                <div class="rating-stars">
                                    @for($s = 1; $s <= 5; $s++)
                                        @if($avgScore >= $s)
                                            <i class="fas fa-star"></i>
                                        @elseif($avgScore >= ($s - 0.7))
                                            <i class="fas fa-star-half-alt"></i>
                                        @else
                                            <i class="far fa-star text-muted opacity-50"></i>
                                        @endif
                                    @endfor
                                    <span class="text-dark fw-bold ms-1 font-monospace" style="font-size: 11px;">{{ number_format($avgScore, 1) }}</span>
                                    <span class="rating-count-text font-monospace">({{ $totalReviewCount }})</span>
                                </div>
                            @endif

                            <div class="product-header-actions">
                                @if($uiSettings['show_wishlist'] ?? true)
                                    <button type="button" 
                                            class="header-icon-btn btn-wishlist-toggle {{ $isWishlisted ? 'is-active' : '' }}" 
                                            data-id="{{ $product->id }}" 
                                            title="{{ $isWishlisted ? 'Remove from Wishlist' : 'Add to Wishlist' }}">
                                        <i class="{{ $isWishlisted ? 'fas fa-heart text-danger' : 'far fa-heart' }}"></i>
                                    </button>
                                @endif

                                @if($uiSettings['show_social_share'] ?? true)
                                    <div class="dropdown">
                                        <button class="header-icon-btn" type="button" data-bs-toggle="dropdown" title="Share">
                                            <i class="fas fa-share-alt"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border small" style="border-radius: 5px !important;">
                                            <li><a class="dropdown-item py-1" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}" target="_blank"><i class="fab fa-facebook text-primary me-2"></i> Facebook</a></li>
                                            <li><a class="dropdown-item py-1" href="https://api.whatsapp.com/send?text={{ urlencode(request()->fullUrl()) }}" target="_blank"><i class="fab fa-whatsapp text-success me-2"></i> WhatsApp</a></li>
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Brand, SKU & Stock Badges -->
                        <div class="d-flex flex-wrap align-items-center gap-2 small text-muted mb-2">
                            @if(($uiSettings['show_brand'] ?? true) && !empty($product->brand))
                                <span class="badge pdp-sku-badge fw-semibold px-2 py-1">
                                    Brand: <strong>{{ $product->brand->name }}</strong>
                                </span>
                            @endif
                            @if($uiSettings['show_sku'] ?? true)
                                <span class="badge pdp-sku-badge fw-semibold px-2 py-1 font-monospace">
                                    SKU: <strong id="display-sku">{{ $product->sku ?? ($product->product_code ?? 'PRD-' . $product->id) }}</strong>
                                </span>
                            @endif
                            <div id="display-stock-status">
                                @if($isActuallyOut)
                                    <span class="badge pdp-stock-out-badge px-2 py-1 fw-bold font-monospace">Out of Stock</span>
                                @elseif($actualStock > 0)
                                    <span class="badge pdp-stock-in-badge px-2 py-1 fw-bold font-monospace"><i class="fas fa-check-circle me-1"></i>In Stock ({{ $actualStock }})</span>
                                @else
                                    <span class="badge pdp-stock-pre-badge px-2 py-1 fw-bold font-monospace">Pre-Order</span>
                                @endif
                            </div>
                        </div>

                        <!-- Price Banner -->
                        <div class="price-banner-box mb-2">
                            <div class="d-flex align-items-baseline flex-wrap gap-2 mb-1">
                                <span id="product-active-price" class="current-active-price font-monospace bn-digit" data-unit-price="{{ $finalPrice }}">
                                    ৳{{ number_format($finalPrice, 2) }}
                                </span>
                                <span class="old-strike-price font-monospace bn-digit {{ $hasDiscount ? '' : 'd-none' }}" id="product-active-old-price">
                                    ৳{{ number_format($regularPrice, 2) }}
                                </span>
                                <span class="saving-chip font-monospace {{ $hasDiscount ? '' : 'd-none' }}" id="product-discount-tag">
                                    {{ $discountPercentage }}% SAVINGS
                                </span>
                            </div>
                            <div class="calculated-total-line fw-semibold font-monospace">
                                Estimated Total: <strong id="calculated-total-display" class="bn-digit">৳{{ number_format($finalPrice, 2) }}</strong>
                            </div>
                        </div>

                        <!-- Short Description Box -->
                        @if(!empty($sanitizedShortDesc))
                            <div class="product-short-desc-card mb-2">
                                {!! nl2br($sanitizedShortDesc) !!}
                            </div>
                        @endif

                        <!-- Product Form (Variations & Cart actions) -->
                        @if($displayStockOutBtn)
                            <div class="mt-2 mb-2">
                                <button type="button" class="btn-stockout-vibrant" disabled title="Out of Stock">
                                    <i class="fas fa-ban"></i> PRODUCT OUT OF STOCK
                                </button>
                            </div>
                        @else
                            <form id="single-product-order-form" action="{{ route('cart.add', $product->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="variant_id" id="selected-variant-id" value="">

                                <!-- Color Swatches with Mini Image Thumbnail -->
                                @if($uniqueColors->count() > 0)
                                    <div class="swatch-group-wrap">
                                        <div class="swatch-group-label">
                                            <span>Color: <span id="selected-color-name" class="swatch-active-name font-monospace fw-bold"></span></span>
                                        </div>
                                        <div class="swatches-flex-container">
                                            @foreach($uniqueColors as $color)
                                                @php
                                                    $cLookup = strtolower(trim($color));
                                                    $swatchImg = $colorImageMap[$cLookup] ?? null;
                                                @endphp
                                                <div class="swatch-chip swatch-color-btn" data-color="{{ $color }}" data-image="{{ $swatchImg ?? '' }}" onclick="selectColorSwatch('{{ addslashes($color) }}', this)">
                                                    @if(!empty($swatchImg))
                                                        <img src="{{ $swatchImg }}" alt="{{ $color }}" class="swatch-mini-thumb" onerror="this.onerror=null; this.style.display='none'">
                                                    @else
                                                        <i class="fas fa-circle opacity-75" style="font-size: 8px; color: var(--pdp-swatch-active-border);"></i>
                                                    @endif
                                                    <span>{{ $color }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Size / Spec Swatches -->
                                @if($uniqueSizes->count() > 0)
                                    <div class="swatch-group-wrap">
                                        <div class="swatch-group-label">
                                            <span>Size / Spec: <span id="selected-size-name" class="swatch-active-name font-monospace fw-bold"></span></span>
                                        </div>
                                        <div class="swatches-flex-container">
                                            @foreach($uniqueSizes as $size)
                                                <div class="swatch-chip swatch-size-btn" data-size="{{ $size }}" onclick="selectSizeSwatch('{{ addslashes($size) }}', this)">
                                                    {{ $size }}
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Quantity Stepper -->
                                <div class="product-qty-section">
                                    <label class="mb-0">Qty:</label>
                                    <div class="qty-stepper-wrap">
                                        <button class="qty-btn" type="button" onclick="changeQuantity(-1)">-</button>
                                        <input id="product-qty-input" type="number" name="quantity" class="qty-input-field font-monospace bn-digit" value="1" min="1" max="99" onchange="updateSubtotalPrice()">
                                        <button class="qty-btn" type="button" onclick="changeQuantity(1)">+</button>
                                    </div>
                                </div>

                                <!-- Action Buttons Grid -->
                                <div class="action-btns-grid">
                                    <button type="submit" name="action" value="order_now" class="btn btn-buy-now-vibrant shadow-xs">
                                        <i class="fas fa-bolt" style="font-size: 11px;"></i> Buy Now
                                    </button>
                                    <button type="submit" name="action" value="add_to_cart" class="btn btn-add-cart-vibrant shadow-xs">
                                        <i class="fas fa-shopping-cart" style="font-size: 11px;"></i> Add Cart
                                    </button>
                                </div>

                                @if($uiSettings['show_whatsapp'] ?? true)
                                    <div class="mb-1">
                                        <a href="https://api.whatsapp.com/send?phone=8801774937404&text={{ urlencode('Hello, I want to order: ' . ($product->title ?? $product->name) . ' - ' . request()->fullUrl()) }}" target="_blank" class="btn-whatsapp-vibrant shadow-xs">
                                            <i class="fab fa-whatsapp" style="font-size: 13px;"></i> Order via WhatsApp
                                        </a>
                                    </div>
                                @endif
                            </form>
                        @endif
                    </div>

                    <!-- 3. Right Column: Compact Delivery & Trust Sidebar -->
                    @if($uiSettings['show_delivery_sidebar'] ?? true)
                        <div class="col-12 col-lg-3">
                            <div class="store-sidebar-card h-100">
                                
                                <div class="sidebar-brand-box">
                                    <div class="bg-light border p-1 d-flex align-items-center justify-content-center shadow-xs flex-shrink-0" style="width: 36px; height: 36px; overflow: hidden; border-radius: 5px !important;">
                                        @if($storeLogoUrl)
                                            <img src="{{ $storeLogoUrl }}" alt="Logo" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                        @else
                                            <i class="fas fa-store" style="font-size: 14px; color: var(--pdp-sidebar-feature-icon);"></i>
                                        @endif
                                    </div>
                                    <div class="min-w-0 sidebar-brand-meta">
                                        <h6 class="fw-bold mb-0 text-truncate" style="font-size: 12.5px; line-height: 1.2;">{{ config('app.name', 'ECOM STORE') }}</h6>
                                        @if($uiSettings['store_verified_badge'] ?? true)
                                            <span class="sidebar-verified-badge"><i class="fas fa-check-circle me-1"></i>Verified Store</span>
                                        @endif
                                    </div>
                                </div>

                                @if(!empty($videoEmbedUrl))
                                    <div class="mb-2 border-bottom pb-2">
                                        <h6 class="fw-bold sidebar-heading-text text-uppercase mb-1" style="font-size: 10px;">
                                            <i class="fab fa-youtube text-danger me-1"></i> Product Video
                                        </h6>
                                        <div class="ratio ratio-16x9 overflow-hidden shadow-xs" style="border-radius: 5px !important;">
                                            <iframe src="{{ $videoEmbedUrl }}" title="Video" allowfullscreen></iframe>
                                        </div>
                                    </div>
                                @endif

                                @if($uiSettings['show_delivery_options'] ?? true)
                                    <div class="mb-2 border-bottom pb-2">
                                        <h6 class="fw-bold sidebar-heading-text text-uppercase mb-1" style="font-size: 10px; letter-spacing: 0.3px;">Delivery Options</h6>
                                        <select id="delivery-city-selector" class="form-select form-select-sm mb-2 font-monospace" style="font-size: 11px; padding: 4px 8px;" onchange="updateDeliveryRate()">
                                            <option value="dhaka" data-cost="{{ $uiSettings['delivery_inside_dhaka'] ?? 70 }}" data-time="{{ $uiSettings['delivery_dhaka_days'] ?? '2-3 days' }}" selected>Inside Dhaka</option>
                                            <option value="outside" data-cost="{{ $uiSettings['delivery_outside_dhaka'] ?? 130 }}" data-time="{{ $uiSettings['delivery_outside_days'] ?? '3-5 days' }}">Outside Dhaka</option>
                                        </select>
                                        
                                        <div class="sidebar-feature-row">
                                            <div class="sidebar-feature-icon" style="border-radius: 4px !important;"><i class="fas fa-truck"></i></div>
                                            <div class="w-100">
                                                <div class="d-flex justify-content-between fw-semibold" style="font-size: 11px;">
                                                    <span>Standard Delivery</span>
                                                    <strong id="delivery-cost-amount" class="font-monospace bn-digit" style="color: var(--pdp-price);">৳{{ number_format($uiSettings['delivery_inside_dhaka'] ?? 70, 2) }}</strong>
                                                </div>
                                                <span id="delivery-timeline" class="text-muted font-monospace" style="font-size: 10px;">
                                                    Guaranteed by {{ $uiSettings['delivery_dhaka_days'] ?? '2-3 days' }}
                                                </span>
                                            </div>
                                        </div>

                                        @if($uiSettings['show_cod_option'] ?? true)
                                            <div class="sidebar-feature-row mb-0">
                                                <div class="sidebar-feature-icon" style="border-radius: 4px !important;"><i class="fas fa-hand-holding-usd"></i></div>
                                                <div class="fw-semibold" style="font-size: 11px;">
                                                    <span>Cash on Delivery</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                @if($uiSettings['show_return_warranty'] ?? true)
                                    <div class="mb-2 border-bottom pb-2">
                                        <h6 class="fw-bold sidebar-heading-text text-uppercase mb-1" style="font-size: 10px; letter-spacing: 0.3px;">Return & Warranty</h6>
                                        <div class="sidebar-feature-row">
                                            <div class="sidebar-feature-icon" style="border-radius: 4px !important;"><i class="fas fa-undo-alt"></i></div>
                                            <div style="font-size: 11px;">7 Days Easy Return Policy</div>
                                        </div>
                                        <div class="sidebar-feature-row mb-0">
                                            <div class="sidebar-feature-icon" style="border-radius: 4px !important;"><i class="fas fa-shield-alt"></i></div>
                                            <div style="font-size: 11px;">100% Genuine Guaranteed</div>
                                        </div>
                                    </div>
                                @endif

                                <div class="mb-2">
                                    <h6 class="fw-bold sidebar-heading-text text-uppercase mb-1" style="font-size: 10px; letter-spacing: 0.3px;">Accepted Payment</h6>
                                    <div class="d-flex flex-wrap gap-1">
                                        @forelse($activePaymentList as $methodName)
                                            <span class="badge pdp-sku-badge px-1 py-0 font-monospace" style="font-size: 9px;">
                                                {{ $methodName }}
                                            </span>
                                        @empty
                                            <span class="badge pdp-sku-badge px-1 py-0 font-monospace" style="font-size: 9px;">COD</span>
                                        @endforelse
                                    </div>
                                </div>

                                @if($showSocial && count($socialLinks) > 0)
                                    <div class="border-top pt-2">
                                        <small class="text-muted d-block mb-1 fw-semibold font-monospace" style="font-size: 10px;">Follow Store:</small>
                                        <div class="sidebar-social-circles">
                                            @foreach($socialLinks as $soc)
                                                @php
                                                    $linkUrl = trim($soc['url'] ?? '#');
                                                    if ($linkUrl !== '#' && !preg_match("~^(?:f|ht)tps?://~i", $linkUrl)) {
                                                        $linkUrl = 'https://' . $linkUrl;
                                                    }
                                                @endphp
                                                <a href="{{ $linkUrl }}" class="sidebar-social-circle shadow-xs" target="_blank" rel="noopener noreferrer">
                                                    <i class="{{ $soc['icon'] ?? 'fab fa-facebook-f' }}"></i>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                            </div>
                        </div>
                    @endif

                </div>
            </div>

            <!-- Description, Specifications & Reviews Tabs -->
            <div class="product-main-card mb-3">
                <ul class="nav nav-tabs product-nav-tabs flex-nowrap overflow-x-auto text-nowrap" role="tablist" style="scrollbar-width: none;">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-pane-desc" type="button" role="tab">Description</button>
                    </li>
                    @if($hasSpecifications)
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-pane-spec" type="button" role="tab">Specifications</button>
                        </li>
                    @endif
                    @if($showReviewsSetting == '1')
                        <li class="nav-item">
                            <button class="nav-link" id="reviews-tab-btn" data-bs-toggle="tab" data-bs-target="#tab-pane-reviews" type="button" role="tab">
                                Reviews ({{ $totalReviewCount }})
                            </button>
                        </li>
                    @endif
                </ul>

                <div class="tab-content tab-content-area">
                    <div id="tab-pane-desc" class="tab-pane fade show active" role="tabpanel">
                        <div class="product-desc-content px-2 py-1">
                            {!! nl2br($cleanDesc) !!}
                        </div>
                    </div>

                    @if($hasSpecifications)
                        <div id="tab-pane-spec" class="tab-pane fade" role="tabpanel">
                            <div class="table-responsive border overflow-hidden p-1" style="border-radius: 5px !important;">
                                <table class="table table-bordered table-striped align-middle mb-0 pdp-spec-table font-monospace">
                                    <tbody>
                                        @if(!empty($product->weight) && $product->weight > 0)
                                            <tr>
                                                <th style="width: 35%;">Weight</th>
                                                <td>{{ $product->weight }} kg</td>
                                            </tr>
                                        @endif
                                        @if(!empty($product->height) && $product->height > 0)
                                            <tr>
                                                <th>Height</th>
                                                <td>{{ $product->height }} cm</td>
                                            </tr>
                                        @endif
                                        @if(!empty($product->width) && $product->width > 0)
                                            <tr>
                                                <th>Width</th>
                                                <td>{{ $product->width }} cm</td>
                                            </tr>
                                        @endif
                                        @if(!empty($product->length) && $product->length > 0)
                                            <tr>
                                                <th>Length</th>
                                                <td>{{ $product->length }} cm</td>
                                            </tr>
                                        @endif
                                        @if(!empty($product->sku))
                                            <tr>
                                                <th>SKU</th>
                                                <td style="color: var(--pdp-price);">{{ $product->sku }}</td>
                                            </tr>
                                        @endif
                                        @if(!empty($product->product_code))
                                            <tr>
                                                <th>Product Code</th>
                                                <td>{{ $product->product_code }}</td>
                                            </tr>
                                        @endif
                                        @if(!empty($product->barcode))
                                            <tr>
                                                <th>Barcode</th>
                                                <td>{{ $product->barcode }}</td>
                                            </tr>
                                        @endif
                                        @if(isset($product->stock))
                                            <tr>
                                                <th>Stock Quantity</th>
                                                <td>
                                                    @if($product->stock > 0)
                                                        <strong style="color: var(--pdp-stock-in-text);">{{ $product->stock }} units</strong>
                                                    @else
                                                        <strong style="color: var(--pdp-stock-pre-text);">Pre-Order</strong>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endif
                                        @if(!empty($product->product_type))
                                            <tr>
                                                <th>Product Type</th>
                                                <td class="text-capitalize">{{ $product->product_type }}</td>
                                            </tr>
                                        @endif
                                        @if(!empty($product->product_struct))
                                            <tr>
                                                <th>Structure</th>
                                                <td class="text-capitalize">{{ $product->product_struct }}</td>
                                            </tr>
                                        @endif
                                        @if(!empty($product->variants) && $product->variants->count() > 0)
                                            <tr>
                                                <th>Available Variants</th>
                                                <td>
                                                    @foreach($product->variants as $v)
                                                        <span class="badge pdp-sku-badge me-1 mb-1">{{ $v->color ? $v->color . ' - ' : '' }}{{ $v->size ?? $v->title }}</span>
                                                    @endforeach
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    @if($showReviewsSetting == '1')
                        <div id="tab-pane-reviews" class="tab-pane fade" role="tabpanel">

                            <!-- 1. Ratings & Reviews Breakdown Card -->
                            <div class="rating-overview-card mb-3">
                                <div class="row g-3 align-items-center">
                                    <div class="col-12 col-md-4 text-center border-end-md">
                                        <div class="d-flex align-items-center justify-content-center gap-1 mb-1 text-muted small fw-bold font-monospace">
                                            <i class="far fa-star"></i> Ratings & Reviews
                                        </div>
                                        <div class="h2 fw-bold text-dark font-monospace mb-0" style="line-height: 1;">
                                            {{ number_format($avgScore, 1) }}
                                        </div>
                                        <div class="my-1 text-warning" style="font-size: 13px;">
                                            @for($s = 1; $s <= 5; $s++)
                                                @if($avgScore >= $s)
                                                    <i class="fas fa-star"></i>
                                                @elseif($avgScore >= ($s - 0.7))
                                                    <i class="fas fa-star-half-alt"></i>
                                                @else
                                                    <i class="far fa-star text-muted opacity-50"></i>
                                                @endif
                                            @endfor
                                        </div>
                                        <small class="text-muted fw-semibold font-monospace">{{ $totalReviewCount }} reviews</small>
                                    </div>

                                    <div class="col-12 col-md-8 ps-md-3">
                                        <div class="d-flex flex-column gap-1" style="max-width: 440px;">
                                            @for($star = 5; $star >= 1; $star--)
                                                @php
                                                    $pct = $ratingStats['percentages'][$star] ?? 0;
                                                    $count = $ratingStats['distribution'][$star] ?? 0;
                                                @endphp
                                                <div class="d-flex align-items-center gap-2 small">
                                                    <span class="text-muted font-monospace text-nowrap" style="width: 40px; font-size: 11px;">{{ $star }} <i class="fas fa-star text-warning" style="font-size: 9px;"></i></span>
                                                    <div class="rating-bar-track" style="border-radius: 4px !important;">
                                                        <div class="rating-bar-fill" style="width: {{ $pct }}%; border-radius: 4px !important;"></div>
                                                    </div>
                                                    <span class="text-muted font-monospace text-end" style="width: 36px; font-size: 10px;">{{ $pct }}%</span>
                                                </div>
                                            @endfor
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. Write / Update Review Form Box -->
                            <div class="mb-3">
                                @auth
                                    @if($isBuyer)
                                        <div id="user-review-form-container" class="p-3 mb-3 bg-light border {{ $myReview ? 'd-none' : '' }}" style="border-radius: 5px !important;">
                                            <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                                                <h6 class="fw-bold text-dark mb-0 small">
                                                    <i class="fas fa-pencil text-primary me-1"></i>
                                                    {{ $myReview ? 'Update Your Review' : 'Write a Review' }}
                                                </h6>
                                                @if($myReview)
                                                    <button type="button" class="btn btn-sm btn-link text-muted p-0 text-decoration-none small" onclick="toggleReviewForm(false)">
                                                        <i class="fas fa-times me-1"></i> Cancel
                                                    </button>
                                                @endif
                                            </div>

                                            <form action="{{ $myReview ? route('user.product.review.update', $myReview->id) : route('user.product.review.store', $product->id) }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                @if($myReview) @method('PUT') @endif
                                                
                                                <div class="mb-2">
                                                    <label class="small fw-bold text-muted d-block mb-1 font-monospace">Your Rating *</label>
                                                    <div class="star-picker-wrap" id="starPicker">
                                                        @for($s = 1; $s <= 5; $s++)
                                                            <i class="fas fa-star {{ ($myReview->rating ?? 5) >= $s ? 'selected' : '' }}" data-val="{{ $s }}"></i>
                                                        @endfor
                                                    </div>
                                                    <input type="hidden" name="rating" id="reviewRatingInput" value="{{ $myReview->rating ?? 5 }}">
                                                </div>

                                                <div class="mb-2">
                                                    <label class="small fw-bold text-muted d-block mb-1 font-monospace">Your Review *</label>
                                                    <textarea name="comment" rows="3" class="form-control form-control-sm" placeholder="Share your experience about product quality, fabric, fitting or delivery..." required>{{ $myReview->comment ?? '' }}</textarea>
                                                </div>

                                                @if($imageReviewsEnabled)
                                                    <div class="mb-3">
                                                        <label class="small text-muted fw-bold d-block mb-1 font-monospace">
                                                            <i class="fas fa-camera text-primary me-1"></i> Attach Product Photos (Optional - Max {{ $maxReviewImages }} images):
                                                        </label>
                                                        <input type="file" name="review_images[]" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp" multiple>
                                                        <small class="text-muted d-block mt-0 font-monospace" style="font-size: 10px;">Supported formats: JPG, JPEG, PNG, WEBP</small>
                                                    </div>
                                                @endif

                                                <div class="d-flex align-items-center gap-2">
                                                    <button type="submit" class="btn btn-sm btn-primary px-3 py-1 fw-bold shadow-xs" style="font-size: 12px;">
                                                        {{ $myReview ? 'Update Review' : 'Submit Review' }}
                                                    </button>
                                                    @if($myReview)
                                                        <button type="button" class="btn btn-sm btn-outline-danger px-3 py-1 shadow-xs" style="font-size: 12px;" onclick="document.getElementById('delete-rev-form').submit();">
                                                            Delete
                                                        </button>
                                                    @endif
                                                </div>
                                            </form>

                                            @if($myReview)
                                                <form id="delete-rev-form" action="{{ route('user.product.review.destroy', $myReview->id) }}" method="POST" class="d-none">
                                                    @csrf @method('DELETE')
                                                </form>
                                            @endif
                                        </div>
                                    @else
                                        <div class="p-2.5 bg-light border text-muted small mb-2" style="border-radius: 5px !important; font-size: 11px;">
                                            <i class="fas fa-shield-alt text-success me-1"></i> Only verified buyers who ordered and received this product can write a review.
                                        </div>
                                    @endif
                                @else
                                    <div class="p-2.5 bg-light border text-muted small mb-2" style="border-radius: 5px !important; font-size: 11px;">
                                        <i class="fas fa-lock text-secondary me-1"></i> Please <a href="{{ route('login') }}" class="fw-bold text-primary">Login</a> to write a customer review.
                                    </div>
                                @endauth
                            </div>

                            <!-- 3. Customer Reviews List -->
                            <div class="d-flex flex-column mb-3" id="pdpReviewsContainer">
                                @forelse($reviews as $rev)
                                    @php
                                        $isMyReview = auth()->check() && ($rev->user_id == auth()->id());
                                        $revImages = !empty($rev->images_json) ? (json_decode($rev->images_json, true) ?: []) : [];
                                    @endphp
                                    <div class="screen-review-card">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="screen-avatar-img" style="border-radius: 5px !important;">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <div>
                                                    <div class="d-flex align-items-center flex-wrap gap-1">
                                                        <span class="screen-reviewer-name">{{ $rev->user_name }}</span>
                                                        @if($isMyReview)
                                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace ms-1" style="font-size: 8.5px; padding: 1px 4px;">You</span>
                                                        @endif
                                                        @if(!empty($rev->is_verified_purchase))
                                                            <span class="screen-verified-pill font-monospace" style="border-radius: 3px !important;">
                                                                <i class="fas fa-check"></i> Verified
                                                            </span>
                                                        @endif
                                                        @if(!empty($rev->is_pinned))
                                                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle font-monospace ms-1" style="font-size: 8.5px;">
                                                                <i class="fas fa-thumbtack me-0.5"></i> Featured
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="screen-star-row mt-1">
                                                        @for($r = 1; $r <= 5; $r++)
                                                            <i class="fas fa-star {{ $r <= $rev->rating ? '' : 'text-muted opacity-25' }}"></i>
                                                        @endfor
                                                        <span class="screen-numeric-rating font-monospace">{{ number_format($rev->rating, 1) }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-center gap-2">
                                                <span class="screen-time-text font-monospace" style="font-size: 10px;">
                                                    {{ \Carbon\Carbon::parse($rev->created_at)->diffForHumans() }}
                                                </span>
                                                @if($isMyReview)
                                                    <button type="button" class="btn-edit-pill-badge" onclick="toggleReviewForm(true)">
                                                        <i class="fas fa-pen-to-square"></i> Edit
                                                    </button>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="screen-comment-body">
                                            {{ $rev->comment }}
                                        </div>

                                        @if(count($revImages) > 0 && (!isset($rev->images_visible) || $rev->images_visible))
                                            <div class="d-flex gap-1.5 flex-wrap mt-1.5 mb-1.5">
                                                @foreach($revImages as $img)
                                                    <img src="{{ asset($img) }}" alt="Customer Review Photo" class="review-attached-img" onclick="openReviewImageLightbox('{{ asset($img) }}')" onerror="this.onerror=null; this.style.display='none'">
                                                @endforeach
                                            </div>
                                        @endif

                                        @if(!empty($rev->admin_reply) && (!isset($rev->is_reply_visible) || $rev->is_reply_visible))
                                            <div class="p-2 bg-light border-start border-3 border-primary mb-2 mt-2" style="font-size: 11px; line-height: 1.45; border-radius: 0 4px 4px 0 !important;">
                                                <div class="d-flex align-items-center gap-1 fw-bold text-primary mb-0 font-monospace" style="font-size: 10.5px;">
                                                    <i class="fas fa-reply"></i>
                                                    <span>Official Store Response:</span>
                                                </div>
                                                <div class="text-dark">{{ $rev->admin_reply }}</div>
                                            </div>
                                        @endif

                                        <div class="d-flex align-items-center justify-content-between pt-1 text-muted font-monospace" style="font-size: 10.5px;">
                                            <div class="d-flex align-items-center gap-2">
                                                <button type="button" class="btn btn-sm btn-light border py-0 px-2 text-muted fw-semibold" onclick="markReviewHelpful({{ $rev->id }}, this)" style="font-size: 10px;">
                                                    <i class="far fa-thumbs-up me-1"></i> Helpful (<span class="helpful-num">{{ $rev->helpful_count ?? 0 }}</span>)
                                                </button>
                                                <button type="button" class="btn btn-sm btn-link text-muted p-0 text-decoration-none" onclick="openReportModal({{ $rev->id }})" style="font-size: 10px;">
                                                    <i class="far fa-flag me-0.5"></i> Report
                                                </button>
                                            </div>
                                            <span style="font-size: 9.5px;">Posted on {{ config('app.name', 'Store') }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-4 text-muted small font-monospace" style="font-size: 11px;">
                                        <i class="far fa-comment-dots fa-2x mb-2 d-block opacity-50"></i>
                                        No reviews yet for this product.
                                    </div>
                                @endforelse
                            </div>

                            @if(!$myReview && $isBuyer)
                                <div class="mt-2 mb-2">
                                    <button type="button" class="btn-write-review-outline shadow-xs" onclick="toggleReviewForm(true)">
                                        Write a Review
                                    </button>
                                </div>
                            @endif

                        </div>
                    @endif
                </div>
            </div>

            <!-- Related Products -->
            @if(($uiSettings['show_related'] ?? true) && !empty($relatedProducts) && $relatedProducts->count() > 0)
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="fw-bold mb-0 small" style="color: var(--pdp-title); font-size: 13px;">Related Products</h5>
                        <div class="d-flex gap-1">
                            <button type="button" class="header-icon-btn" style="width: 24px; height: 24px;" onclick="scrollSlider('related-slider-box', -1)"><i class="fas fa-chevron-left" style="font-size: 9px;"></i></button>
                            <button type="button" class="header-icon-btn" style="width: 24px; height: 24px;" onclick="scrollSlider('related-slider-box', 1)"><i class="fas fa-chevron-right" style="font-size: 9px;"></i></button>
                        </div>
                    </div>

                    <div id="related-slider-box" class="mini-slider-container">
                        @foreach($relatedProducts as $rel)
                            @php
                                $rPrice = (float)($rel->sale_price ?? $rel->regular_price ?? 0);
                                $rOld = (float)($rel->regular_price ?? 0);
                                $rImg = $resolveProductImg($rel->thumbnail, 'https://placehold.co/300x300?text=Item');
                            @endphp
                            <div class="mini-slider-item">
                                <div class="mini-product-card h-100 d-flex flex-column">
                                    <div class="mini-product-img-box">
                                        <a href="{{ route('product.details', $rel->slug ?? $rel->id) }}">
                                            <img src="{{ $rImg }}" alt="{{ $rel->title ?? $rel->name }}" onerror="this.onerror=null; this.src='{{ $svgFallback }}'">
                                        </a>
                                    </div>
                                    <div class="p-2 d-flex flex-column flex-grow-1">
                                        <h6 class="small fw-semibold text-truncate mb-1" style="font-size: 11px;" title="{{ $rel->title ?? $rel->name }}">
                                            <a href="{{ route('product.details', $rel->slug ?? $rel->id) }}" class="text-dark text-decoration-none">
                                                {{ $rel->title ?? $rel->name }}
                                            </a>
                                        </h6>
                                        <div class="mt-auto d-flex align-items-baseline gap-1 font-monospace">
                                            <strong class="small fw-bold bn-digit" style="font-size: 11px; color: var(--pdp-price);">৳{{ number_format($rPrice, 0) }}</strong>
                                            @if($rOld > $rPrice)
                                                <del class="text-muted bn-digit" style="font-size: 9px;">৳{{ number_format($rOld, 0) }}</del>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Recently Viewed -->
            @if(($uiSettings['show_recent'] ?? true) && !empty($recentProducts) && $recentProducts->count() > 0)
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="fw-bold mb-0 small" style="color: var(--pdp-title); font-size: 13px;">Recently Viewed</h5>
                        <div class="d-flex gap-1">
                            <button type="button" class="header-icon-btn" style="width: 24px; height: 24px;" onclick="scrollSlider('recent-slider-box', -1)"><i class="fas fa-chevron-left" style="font-size: 9px;"></i></button>
                            <button type="button" class="header-icon-btn" style="width: 24px; height: 24px;" onclick="scrollSlider('recent-slider-box', 1)"><i class="fas fa-chevron-right" style="font-size: 9px;"></i></button>
                        </div>
                    </div>

                    <div id="recent-slider-box" class="mini-slider-container">
                        @foreach($recentProducts as $rec)
                            @php
                                $recPrice = (float)($rec->sale_price ?? $rec->regular_price ?? 0);
                                $recOld = (float)($rec->regular_price ?? 0);
                                $recImg = $resolveProductImg($rec->thumbnail, 'https://placehold.co/300x300?text=Item');
                            @endphp
                            <div class="mini-slider-item">
                                <div class="mini-product-card h-100 d-flex flex-column">
                                    <div class="mini-product-img-box">
                                        <a href="{{ route('product.details', $rec->slug ?? $rec->id) }}">
                                            <img src="{{ $recImg }}" alt="{{ $rec->title ?? $rec->name }}" onerror="this.onerror=null; this.src='{{ $svgFallback }}'">
                                        </a>
                                    </div>
                                    <div class="p-2 d-flex flex-column flex-grow-1">
                                        <h6 class="small fw-semibold text-truncate mb-1" style="font-size: 11px;" title="{{ $rec->title ?? $rec->name }}">
                                            <a href="{{ route('product.details', $rec->slug ?? $rec->id) }}" class="text-dark text-decoration-none">
                                                {{ $rec->title ?? $rec->name }}
                                            </a>
                                        </h6>
                                        <div class="mt-auto d-flex align-items-baseline gap-1 font-monospace">
                                            <strong class="small fw-bold bn-digit" style="font-size: 11px; color: var(--pdp-price);">৳{{ number_format($recPrice, 0) }}</strong>
                                            @if($recOld > $recPrice)
                                                <del class="text-muted bn-digit" style="font-size: 9px;">৳{{ number_format($recOld, 0) }}</del>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>

    <!-- 1. Review Photo Lightbox Modal -->
    <div class="modal fade" id="reviewPhotoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content border-0 shadow-lg text-center p-2 bg-white">
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn-close shadow-none btn-sm" data-bs-dismiss="modal"></button>
                </div>
                <div class="p-2">
                    <img id="reviewPhotoModalImg" src="" alt="Customer Photo" class="img-fluid" style="max-height: 480px; object-fit: contain; border-radius: 4px;">
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Universal Custom Alert & Notification Modal -->
    <div class="modal fade" id="customAlertModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg text-center p-3">
                <div class="modal-body p-2">
                    <div id="customAlertIconBox" class="mb-2" style="font-size: 32px;"></div>
                    <h6 id="customAlertTitle" class="fw-bold mb-1 small"></h6>
                    <p id="customAlertMessage" class="text-muted small mb-3" style="font-size: 11px;"></p>
                    <button type="button" class="btn btn-primary btn-sm px-4 fw-bold" data-bs-dismiss="modal" style="font-size: 11px;">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Report Review Modal -->
    <div class="modal fade" id="reportReviewModalDialog" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg p-2">
                <div class="modal-header border-0 pb-0 pt-2 px-3">
                    <h6 class="modal-title fw-bold text-dark small"><i class="far fa-flag text-danger me-1"></i> Report Review</h6>
                    <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3">
                    <p class="text-muted small mb-2" style="font-size: 11px;">Please specify why you are reporting this review:</p>
                    <input type="hidden" id="reportTargetReviewId" value="">
                    <textarea id="reportReasonInput" rows="3" class="form-control form-control-sm font-monospace shadow-none" placeholder="Offensive language, spam, inappropriate image..." style="font-size: 11px;"></textarea>
                </div>
                <div class="modal-footer border-0 pt-0 px-3 pb-2 d-flex justify-content-end gap-1">
                    <button type="button" class="btn btn-light btn-sm px-3" data-bs-dismiss="modal" style="font-size: 11px;">Cancel</button>
                    <button type="button" class="btn btn-danger btn-sm px-3 fw-bold" onclick="submitReviewReport()" style="font-size: 11px;">Submit Report</button>
                </div>
            </div>
        </div>
    </div>

<script>
    const variantsData = {!! json_encode($variantsJson) !!};
    let currentColor = null;
    let currentSize = null;

    function selectColorSwatch(color, element) {
        document.querySelectorAll('.swatch-color-btn').forEach(el => el.classList.remove('active'));
        element.classList.add('active');
        currentColor = color;
        const colorLabel = document.getElementById('selected-color-name');
        if (colorLabel) colorLabel.innerText = color;

        const swatchImgUrl = element.getAttribute('data-image');
        if (swatchImgUrl) {
            updateMainDisplayImageAndSyncThumb(swatchImgUrl);
        }

        matchVariant();
    }

    function selectSizeSwatch(size, element) {
        document.querySelectorAll('.swatch-size-btn').forEach(el => el.classList.remove('active'));
        element.classList.add('active');
        currentSize = size;
        const sizeLabel = document.getElementById('selected-size-name');
        if (sizeLabel) sizeLabel.innerText = size;
        matchVariant();
    }

    function updateMainDisplayImageAndSyncThumb(imgUrl) {
        if (!imgUrl) return;
        const mainImg = document.getElementById('product-main-display-image');
        const zoomLens = document.getElementById('zoom-lens-element');

        if (mainImg) {
            mainImg.src = imgUrl;
        }
        if (zoomLens) {
            zoomLens.style.backgroundImage = `url('${imgUrl}')`;
        }

        document.querySelectorAll('.gallery-thumb-item').forEach(el => {
            if (el.src === imgUrl || el.getAttribute('src') === imgUrl) {
                el.classList.add('active-thumb');
                el.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
            } else {
                el.classList.remove('active-thumb');
            }
        });
    }

    function matchVariant() {
        if (!variantsData || variantsData.length === 0) return;

        let matched = variantsData.find(v => {
            const cMatch = !currentColor || (v.color && v.color.trim().toLowerCase() === currentColor.trim().toLowerCase());
            const sMatch = !currentSize || (v.size && v.size.trim().toLowerCase() === currentSize.trim().toLowerCase());
            return cMatch && sMatch;
        });

        if (!matched && currentColor) {
            matched = variantsData.find(v => v.color && v.color.trim().toLowerCase() === currentColor.trim().toLowerCase());
        }
        if (!matched && currentSize) {
            matched = variantsData.find(v => v.size && v.size.trim().toLowerCase() === currentSize.trim().toLowerCase());
        }

        if (matched) {
            const variantInput = document.getElementById('selected-variant-id');
            if (variantInput) {
                variantInput.value = matched.id;
            }
            
            const priceEl = document.getElementById('product-active-price');
            if (priceEl) {
                priceEl.innerText = '৳' + parseFloat(matched.price).toFixed(2);
                priceEl.setAttribute('data-unit-price', matched.price);
            }

            const oldPriceEl = document.getElementById('product-active-old-price');
            const discTagEl  = document.getElementById('product-discount-tag');
            if (oldPriceEl && matched.regular_price > matched.price) {
                oldPriceEl.innerText = '৳' + parseFloat(matched.regular_price).toFixed(2);
                oldPriceEl.classList.remove('d-none');
                if (discTagEl) {
                    let pct = Math.round(((matched.regular_price - matched.price) / matched.regular_price) * 100);
                    discTagEl.innerText = pct + '% SAVINGS';
                    discTagEl.classList.remove('d-none');
                }
            } else {
                if (oldPriceEl) oldPriceEl.classList.add('d-none');
                if (discTagEl) discTagEl.classList.add('d-none');
            }

            const skuEl = document.getElementById('display-sku');
            if (skuEl && matched.sku) {
                skuEl.innerText = matched.sku;
            }

            const stockBadgeWrap = document.getElementById('display-stock-status');
            if (stockBadgeWrap && typeof matched.stock !== 'undefined') {
                if (matched.stock > 0) {
                    stockBadgeWrap.innerHTML = `<span class="badge pdp-stock-in-badge px-2 py-1 fw-bold font-monospace" style="font-size: 10px;"><i class="fas fa-check-circle me-1"></i>In Stock (${matched.stock})</span>`;
                } else {
                    stockBadgeWrap.innerHTML = `<span class="badge pdp-stock-out-badge px-2 py-1 fw-bold font-monospace" style="font-size: 10px;">Out of Stock</span>`;
                }
            }

            let targetImg = matched.image;
            if (!targetImg && currentColor) {
                const colorWithImg = variantsData.find(v => v.color && v.color.trim().toLowerCase() === currentColor.trim().toLowerCase() && v.image);
                if (colorWithImg) targetImg = colorWithImg.image;
            }
            if (targetImg) {
                updateMainDisplayImageAndSyncThumb(targetImg);
            }

            updateSubtotalPrice();
        }
    }

    function switchProductMainImage(imgUrl, element) {
        updateMainDisplayImageAndSyncThumb(imgUrl);
    }

    function scrollThumbs(dir) {
        const container = document.getElementById('product-thumbnails-wrapper');
        if (!container) return;
        container.scrollBy({ left: dir * 160, behavior: 'smooth' });
    }

    function changeQuantity(step) {
        const qtyInput = document.getElementById('product-qty-input');
        if (!qtyInput) return;
        let currentVal = parseInt(qtyInput.value) || 1;
        currentVal += step;
        if (currentVal < 1) currentVal = 1;
        qtyInput.value = currentVal;
        updateSubtotalPrice();
    }

    function updateSubtotalPrice() {
        const qtyInput = document.getElementById('product-qty-input');
        const priceElement = document.getElementById('product-active-price');
        const totalDisplay = document.getElementById('calculated-total-display');
        if (!qtyInput || !priceElement || !totalDisplay) return;
        let qty = parseInt(qtyInput.value) || 1;
        let unitPrice = parseFloat(priceElement.getAttribute('data-unit-price')) || 0;
        let total = qty * unitPrice;
        totalDisplay.innerText = '৳' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function updateDeliveryRate() {
        const selector = document.getElementById('delivery-city-selector');
        if (!selector) return;
        const selectedOption = selector.options[selector.selectedIndex];
        const cost = parseFloat(selectedOption.getAttribute('data-cost')) || 0;
        const time = selectedOption.getAttribute('data-time') || '2-3 days';
        const costAmountEl = document.getElementById('delivery-cost-amount');
        if (costAmountEl) costAmountEl.innerText = '৳' + cost.toFixed(2);
        const timelineEl = document.getElementById('delivery-timeline');
        if (timelineEl) {
            timelineEl.innerText = 'Guaranteed by ' + time;
        }
    }

    const zoomMode = "{{ $zoomMode }}";
    if (zoomMode === 'lens') {
        const zoomBox = document.getElementById('product-main-preview-box');
        const zoomImg = document.getElementById('product-main-display-image');
        const zoomLens = document.getElementById('zoom-lens-element');

        if (zoomBox && zoomLens && zoomImg) {
            zoomBox.addEventListener('mouseenter', () => {
                zoomLens.style.display = 'block';
                zoomLens.style.backgroundImage = `url('${zoomImg.src}')`;
                zoomLens.style.backgroundSize = `${zoomImg.width * 2}px ${zoomImg.height * 2}px`;
            });

            zoomBox.addEventListener('mouseleave', () => {
                zoomLens.style.display = 'none';
            });

            zoomBox.addEventListener('mousemove', (e) => {
                const rect = zoomBox.getBoundingClientRect();
                let x = e.clientX - rect.left - (zoomLens.offsetWidth / 2);
                let y = e.clientY - rect.top - (zoomLens.offsetHeight / 2);

                const maxX = zoomBox.offsetWidth - zoomLens.offsetWidth;
                const maxY = zoomBox.offsetHeight - zoomLens.offsetHeight;

                if (x > maxX) x = maxX;
                if (x < 0) x = 0;
                if (y > maxY) y = maxY;
                if (y < 0) y = 0;

                zoomLens.style.left = x + 'px';
                zoomLens.style.top = y + 'px';
                zoomLens.style.backgroundPosition = `-${x * 2}px -${y * 2}px`;
            });
        }
    }

    function scrollSlider(boxId, dir) {
        const el = document.getElementById(boxId);
        if (!el) return;
        const scrollAmount = el.clientWidth * 0.75;
        el.scrollBy({ left: dir * scrollAmount, behavior: 'smooth' });
    }

    function setupMiniSlider(containerId, intervalSpeed) {
        const container = document.getElementById(containerId);
        if (!container) return;

        let autoScrollInterval = setInterval(() => {
            if (container.scrollLeft + container.clientWidth >= container.scrollWidth - 10) {
                container.scrollTo({ left: 0, behavior: 'smooth' });
            } else {
                container.scrollBy({ left: 180, behavior: 'smooth' });
            }
        }, intervalSpeed);

        container.addEventListener('mouseenter', () => clearInterval(autoScrollInterval));
        container.addEventListener('mouseleave', () => {
            autoScrollInterval = setInterval(() => {
                if (container.scrollLeft + container.clientWidth >= container.scrollWidth - 10) {
                    container.scrollTo({ left: 0, behavior: 'smooth' });
                } else {
                    container.scrollBy({ left: 180, behavior: 'smooth' });
                }
            }, intervalSpeed);
        });
    }

    // Modal Alert Function (Replaces native JS alerts)
    function showAppModalAlert(title, message, type = 'info') {
        const modalEl = document.getElementById('customAlertModal');
        const iconBox = document.getElementById('customAlertIconBox');
        const titleEl = document.getElementById('customAlertTitle');
        const msgEl   = document.getElementById('customAlertMessage');

        if (!modalEl || !iconBox || !titleEl || !msgEl) return;

        if (type === 'success') {
            iconBox.innerHTML = '<i class="fas fa-check-circle text-success"></i>';
        } else if (type === 'error') {
            iconBox.innerHTML = '<i class="fas fa-times-circle text-danger"></i>';
        } else {
            iconBox.innerHTML = '<i class="fas fa-info-circle text-primary"></i>';
        }

        titleEl.innerText = title;
        msgEl.innerText = message;

        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }

    // Lightbox modal for customer review photos
    function openReviewImageLightbox(imgUrl) {
        const modalImg = document.getElementById('reviewPhotoModalImg');
        if (modalImg && imgUrl) {
            modalImg.src = imgUrl;
            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('reviewPhotoModal'));
            modal.show();
        }
    }

    // Helpful Vote Action (AJAX)
    function markReviewHelpful(reviewId, btnEl) {
        fetch(`/user/product/review/${reviewId}/helpful`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                const countEl = btnEl.querySelector('.helpful-num');
                if (countEl && data.helpful_count !== undefined) {
                    countEl.innerText = data.helpful_count;
                }
                btnEl.classList.remove('btn-light', 'text-muted');
                btnEl.classList.add('btn-primary', 'text-white');
                showAppModalAlert('Thank You!', data.message || 'Helpful vote recorded.', 'success');
            } else {
                showAppModalAlert('Notice', data.message || 'You have already voted on this review.', 'info');
            }
        })
        .catch(() => showAppModalAlert('Access Required', 'Please login to mark reviews as helpful.', 'error'));
    }

    // Report Review Action
    function openReportModal(reviewId) {
        const hiddenId = document.getElementById('reportTargetReviewId');
        const reasonInput = document.getElementById('reportReasonInput');
        if (hiddenId) hiddenId.value = reviewId;
        if (reasonInput) reasonInput.value = '';

        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('reportReviewModalDialog'));
        modal.show();
    }

    function submitReviewReport() {
        const reviewId = document.getElementById('reportTargetReviewId').value;
        const reason = document.getElementById('reportReasonInput').value.trim();

        if (!reason) {
            showAppModalAlert('Required', 'Please write a brief reason for reporting this review.', 'error');
            return;
        }

        const reportModal = bootstrap.Modal.getInstance(document.getElementById('reportReviewModalDialog'));
        if (reportModal) reportModal.hide();

        fetch(`/user/product/review/${reviewId}/report`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(res => res.json())
        .then(data => {
            showAppModalAlert('Report Submitted', data.message || 'Review has been submitted for moderation.', 'success');
        })
        .catch(() => showAppModalAlert('Access Required', 'Please login to report reviews.', 'error'));
    }

    // Toggle Review Edit Form
    function toggleReviewForm(show) {
        const formContainer = document.getElementById('user-review-form-container');
        if (!formContainer) return;

        if (show) {
            formContainer.classList.remove('d-none');
            formContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
            const textarea = formContainer.querySelector('textarea[name="comment"]');
            if (textarea) textarea.focus();
        } else {
            formContainer.classList.add('d-none');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Interactive 5-Star Rating Picker
        const starPicker = document.getElementById('starPicker');
        if (starPicker) {
            const stars = starPicker.querySelectorAll('i');
            const ratingInput = document.getElementById('reviewRatingInput');

            stars.forEach(star => {
                star.addEventListener('mouseenter', () => {
                    const val = parseInt(star.getAttribute('data-val'));
                    stars.forEach(s => {
                        s.classList.toggle('hovered', parseInt(s.getAttribute('data-val')) <= val);
                    });
                });

                star.addEventListener('mouseleave', () => {
                    stars.forEach(s => s.classList.remove('hovered'));
                });

                star.addEventListener('click', () => {
                    const val = parseInt(star.getAttribute('data-val'));
                    if (ratingInput) ratingInput.value = val;
                    stars.forEach(s => {
                        s.classList.toggle('selected', parseInt(s.getAttribute('data-val')) <= val);
                    });
                });
            });
        }

        const sliderSpeed = {{ $uiSettings['slider_auto_speed'] ?? 3500 }};
        setupMiniSlider('related-slider-box', sliderSpeed);
        setupMiniSlider('recent-slider-box', sliderSpeed + 500);

        const alertSuccess = document.getElementById('productDetailSuccessAlert');
        if (alertSuccess) {
            setTimeout(() => {
                if (window.bootstrap && window.bootstrap.Alert) {
                    let bsAlert = window.bootstrap.Alert.getOrCreateInstance(alertSuccess);
                    if (bsAlert) bsAlert.close();
                } else {
                    alertSuccess.remove();
                }
            }, 3500);
        }

        const firstColor = document.querySelector('.swatch-color-btn');
        if (firstColor) firstColor.click();
        const firstSize = document.querySelector('.swatch-size-btn');
        if (firstSize) firstSize.click();
    });
</script>
@endsection