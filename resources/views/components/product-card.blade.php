@php
    // ১. স্ট্যাটিক ক্যাশিং (লুপে বারবার ডাটাবেজ কোয়েরি বন্ধ রাখার জন্য)
    static $cardSettingsCache = null;
    if ($cardSettingsCache === null) {
        $cardSettingsCache = \Illuminate\Support\Facades\Cache::remember('product_card_settings_cache', 86400, function() {
            return DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $dbSettings = $cardSettingsCache ?? [];

    $stockOutSetting     = $dbSettings['card_show_stockout_btn'] ?? '1';
    $showStockOutSetting = ($stockOutSetting === '1' || $stockOutSetting == 1);

    $showPriceDecimal   = ($dbSettings['card_show_price_decimal'] ?? '1') == '1';
    $showReviews        = ($dbSettings['card_show_review'] ?? '1') == '1';
    $showWishlist       = ($dbSettings['card_show_wishlist'] ?? '1') == '1';
    $showSaleBadge      = ($dbSettings['card_show_sale_badge'] ?? '1') == '1';
    $saleBadgeText      = !empty($dbSettings['card_sale_badge_text']) ? $dbSettings['card_sale_badge_text'] : 'For Sale!';
    $enableQuickView    = ($dbSettings['card_enable_quick_view'] ?? ($dbSettings['feature_quick_view'] ?? '1')) == '1';
    $cardHoverStyle     = $dbSettings['card_hover_style'] ?? 'zoom';
    $showBuyNowBtn      = ($dbSettings['feature_buy_now_btn'] ?? '1') == '1';
    $enableFloatingCart = ($dbSettings['feature_floating_cart_modal'] ?? '1') == '1';

    // কার্ড কালার ও ইন্টারেক্টিভ ভেরিয়েবল রিজলভার
    $cpcCardBg           = $dbSettings['color_cpc_card_bg'] ?? '#ffffff';
    $cpcCardBorder       = $dbSettings['color_cpc_card_border'] ?? '#edf2f7';
    $cpcCardBorderHover  = $dbSettings['color_cpc_card_border_hover'] ?? '#cbd5e1';
    $cpcCardShadowHover  = $dbSettings['color_cpc_card_shadow_hover'] ?? 'rgba(0,0,0,0.06)';
    $cpcImgWrapBg        = $dbSettings['color_cpc_img_wrap_bg'] ?? '#f8fafc';
    $cpcBodyBg           = $dbSettings['color_cpc_body_bg'] ?? 'transparent';

    $cpcTitle            = $dbSettings['color_cpc_title'] ?? '#1e293b';
    $cpcTitleHover       = $dbSettings['color_cpc_title_hover'] ?? '#2563eb';
    $cpcCategory         = $dbSettings['color_cpc_category'] ?? '#64748b';
    $cpcCategoryHover    = $dbSettings['color_cpc_category_hover'] ?? '#2563eb';
    $cpcPrice            = $dbSettings['color_cpc_price'] ?? '#0f172a';
    $cpcOldPrice         = $dbSettings['color_cpc_old_price'] ?? '#94a3b8';

    $cpcRatingStar       = $dbSettings['color_cpc_rating_star'] ?? '#f59e0b';
    $cpcRatingText       = $dbSettings['color_cpc_rating_text'] ?? '#64748b';

    $cpcSaleBadgeBg      = $dbSettings['color_cpc_sale_badge_bg'] ?? '#ef4444';
    $cpcSaleBadgeText    = $dbSettings['color_cpc_sale_badge_text'] ?? '#ffffff';
    $cpcSaleBadgeBorder  = $dbSettings['color_cpc_sale_badge_border'] ?? 'transparent';

    $cpcWishlistBg          = $dbSettings['color_cpc_wishlist_bg'] ?? '#f1592a';
    $cpcWishlistIcon        = $dbSettings['color_cpc_wishlist_icon'] ?? '#ffffff';
    $cpcWishlistBorder      = $dbSettings['color_cpc_wishlist_border'] ?? 'rgba(255, 255, 255, 0.4)';
    $cpcWishlistHoverBg     = $dbSettings['color_cpc_wishlist_hover_bg'] ?? '#1f2033';
    $cpcWishlistHoverIcon   = $dbSettings['color_cpc_wishlist_hover_icon'] ?? '#ffffff';
    $cpcWishlistHoverBorder = $dbSettings['color_cpc_wishlist_hover_border'] ?? 'transparent';
    $cpcWishlistActiveBg    = $dbSettings['color_cpc_wishlist_active_bg'] ?? '#dc2626';
    $cpcWishlistActiveIcon  = $dbSettings['color_cpc_wishlist_active_icon'] ?? '#ffffff';
    $cpcWishlistActiveBorder= $dbSettings['color_cpc_wishlist_active_border'] ?? 'transparent';

    $cpcQvBg            = $dbSettings['color_cpc_qv_bg'] ?? '#ef4444';
    $cpcQvIcon          = $dbSettings['color_cpc_qv_icon'] ?? '#ffffff';
    $cpcQvBorder        = $dbSettings['color_cpc_qv_border'] ?? 'rgba(255, 255, 255, 0.4)';
    $cpcQvHoverBg       = $dbSettings['color_cpc_qv_hover_bg'] ?? '#1f2033';
    $cpcQvHoverIcon     = $dbSettings['color_cpc_qv_hover_icon'] ?? '#ffffff';
    $cpcQvHoverBorder   = $dbSettings['color_cpc_qv_hover_border'] ?? 'transparent';

    $cpcCartBg          = $dbSettings['color_cpc_cart_bg'] ?? '#2563eb';
    $cpcCartText        = $dbSettings['color_cpc_cart_text'] ?? '#ffffff';
    $cpcCartBorder      = $dbSettings['color_cpc_cart_border'] ?? '#2563eb';
    $cpcCartIcon        = $dbSettings['color_cpc_cart_icon'] ?? '#ffffff';
    $cpcCartHoverBg     = $dbSettings['color_cpc_cart_hover_bg'] ?? '#1d4ed8';
    $cpcCartHoverText   = $dbSettings['color_cpc_cart_hover_text'] ?? '#ffffff';
    $cpcCartHoverBorder = $dbSettings['color_cpc_cart_hover_border'] ?? '#1d4ed8';
    $cpcCartHoverIcon   = $dbSettings['color_cpc_cart_hover_icon'] ?? '#ffffff';

    $cpcOrderBg          = $dbSettings['color_cpc_order_bg'] ?? '#0f172a';
    $cpcOrderText        = $dbSettings['color_cpc_order_text'] ?? '#ffffff';
    $cpcOrderBorder      = $dbSettings['color_cpc_order_border'] ?? '#0f172a';
    $cpcOrderIcon        = $dbSettings['color_cpc_order_icon'] ?? '#f59e0b';
    $cpcOrderHoverBg     = $dbSettings['color_cpc_order_hover_bg'] ?? '#1e293b';
    $cpcOrderHoverText   = $dbSettings['color_cpc_order_hover_text'] ?? '#ffffff';
    $cpcOrderHoverBorder = $dbSettings['color_cpc_order_hover_border'] ?? '#1e293b';
    $cpcOrderHoverIcon   = $dbSettings['color_cpc_order_hover_icon'] ?? '#f59e0b';

    $cpcOptBg          = $dbSettings['color_cpc_opt_bg'] ?? '#0f172a';
    $cpcOptText        = $dbSettings['color_cpc_opt_text'] ?? '#ffffff';
    $cpcOptBorder      = $dbSettings['color_cpc_opt_border'] ?? '#0f172a';
    $cpcOptIcon        = $dbSettings['color_cpc_opt_icon'] ?? '#ffffff';
    $cpcOptHoverBg     = $dbSettings['color_cpc_opt_hover_bg'] ?? '#1e293b';
    $cpcOptHoverText   = $dbSettings['color_cpc_opt_hover_text'] ?? '#ffffff';
    $cpcOptHoverBorder = $dbSettings['color_cpc_opt_hover_border'] ?? '#1e293b';
    $cpcOptHoverIcon   = $dbSettings['color_cpc_opt_hover_icon'] ?? '#ffffff';

    $cpcStockoutBg     = $dbSettings['color_cpc_stockout_bg'] ?? '#fee2e2';
    $cpcStockoutText   = $dbSettings['color_cpc_stockout_text'] ?? '#dc2626';
    $cpcStockoutBorder = $dbSettings['color_cpc_stockout_border'] ?? '#fca5a5';
    $cpcStockoutIcon   = $dbSettings['color_cpc_stockout_icon'] ?? '#dc2626';

    $finalPrice   = (float) ($product->sale_price ?? $product->price ?? $product->regular_price ?? 0);
    $regularPrice = (float) ($product->regular_price ?? 0);
    $hasDiscount  = !empty($product->sale_price) && ($product->sale_price < $regularPrice);

    // ২. এক্স্যাক্ট স্টক লজিক
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

    if ($actualStock <= 0 || (!empty($product->stock_status) && in_array(strtolower($product->stock_status), ['out_of_stock', 'outofstock', '0']))) {
        $isActuallyOut = true;
    }

    $displayStockOutBtn = ($showStockOutSetting && $isActuallyOut);

    // ৩. ভ্যারিয়েশন চেক
    $hasVariants = ($product->product_struct === 'variable') || 
                   (!empty($product->variants) && $product->variants->count() > 0) ||
                   (DB::table('product_variants')->where('product_id', $product->id)->exists());

    // Universal Safe Dynamic Image Resolver
    $resolveCardImg = function($path, $fallback = null) {
        if (empty($path)) {
            return $fallback;
        }
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path, $fallback ?? '');
        }
        if (function_exists('show_image')) {
            return show_image($path, $fallback ?? 'https://placehold.co/300x300?text=No+Image');
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        return asset(ltrim($path, '/'));
    };

    $thumb = 'https://placehold.co/300x300?text=No+Image';
    if (!empty($product->thumbnail)) {
        $thumb = $resolveCardImg($product->thumbnail, $thumb);
    }

    // ৪. সেকেন্ড ইমেজ লজিক (Gallery বা Variant Image)
    $secondaryImg = null;
    if ($cardHoverStyle === 'gallery_flip' || $cardHoverStyle === 'slide_up') {
        if (!empty($product->galleries) && $product->galleries->count() > 0) {
            $firstGallery = $product->galleries->first();
            $gPath = $firstGallery->image_path ?? ($firstGallery->image ?? null);
            if (!empty($gPath)) {
                $secondaryImg = $resolveCardImg($gPath);
            }
        } elseif (\Illuminate\Support\Facades\Schema::hasTable('product_galleries')) {
            $dbGallery = DB::table('product_galleries')->where('product_id', $product->id)->first();
            $gPath = $dbGallery->image_path ?? ($dbGallery->image ?? null);
            if (!empty($gPath)) {
                $secondaryImg = $resolveCardImg($gPath);
            }
        }

        if (empty($secondaryImg) && $hasVariants) {
            if (!empty($product->variants) && $product->variants->count() > 0) {
                $variantWithImg = $product->variants->first(function($v) {
                    return !empty($v->image);
                });
                if ($variantWithImg && !empty($variantWithImg->image)) {
                    $secondaryImg = $resolveCardImg($variantWithImg->image);
                }
            }
            
            if (empty($secondaryImg) && \Illuminate\Support\Facades\Schema::hasTable('product_variants')) {
                $dbVariantImg = DB::table('product_variants')
                    ->where('product_id', $product->id)
                    ->whereNotNull('image')
                    ->where('image', '!=', '')
                    ->value('image');

                if (!empty($dbVariantImg)) {
                    $secondaryImg = $resolveCardImg($dbVariantImg);
                }
            }
        }
    }

    if (!empty($secondaryImg) && $secondaryImg === $thumb) {
        $secondaryImg = null;
    }

    $hasSecondary = !empty($secondaryImg);

    // Wishlist Status
    $wishlist = session()->get('wishlist', []);
    $isWishlisted = in_array($product->id, $wishlist);
    if (!$isWishlisted && auth()->check() && \Illuminate\Support\Facades\Schema::hasTable('wishlists')) {
        $isWishlisted = DB::table('wishlists')->where('user_id', auth()->id())->where('product_id', $product->id)->exists();
    }
@endphp

@once
<style>
    :root {
        --cpc-card-bg: {{ $cpcCardBg }};
        --cpc-card-border: {{ $cpcCardBorder }};
        --cpc-card-border-hover: {{ $cpcCardBorderHover }};
        --cpc-card-shadow-hover: {{ $cpcCardShadowHover }};
        --cpc-img-wrap-bg: {{ $cpcImgWrapBg }};
        --cpc-body-bg: {{ $cpcBodyBg }};

        --cpc-title: {{ $cpcTitle }};
        --cpc-title-hover: {{ $cpcTitleHover }};
        --cpc-category: {{ $cpcCategory }};
        --cpc-category-hover: {{ $cpcCategoryHover }};
        --cpc-price: {{ $cpcPrice }};
        --cpc-old-price: {{ $cpcOldPrice }};

        --cpc-rating-star: {{ $cpcRatingStar }};
        --cpc-rating-text: {{ $cpcRatingText }};

        --cpc-sale-badge-bg: {{ $cpcSaleBadgeBg }};
        --cpc-sale-badge-text: {{ $cpcSaleBadgeText }};
        --cpc-sale-badge-border: {{ $cpcSaleBadgeBorder }};

        --cpc-wishlist-bg: {{ $cpcWishlistBg }};
        --cpc-wishlist-icon: {{ $cpcWishlistIcon }};
        --cpc-wishlist-border: {{ $cpcWishlistBorder }};
        --cpc-wishlist-hover-bg: {{ $cpcWishlistHoverBg }};
        --cpc-wishlist-hover-icon: {{ $cpcWishlistHoverIcon }};
        --cpc-wishlist-hover-border: {{ $cpcWishlistHoverBorder }};
        --cpc-wishlist-active-bg: {{ $cpcWishlistActiveBg }};
        --cpc-wishlist-active-icon: {{ $cpcWishlistActiveIcon }};
        --cpc-wishlist-active-border: {{ $cpcWishlistActiveBorder }};

        --cpc-qv-bg: {{ $cpcQvBg }};
        --cpc-qv-icon: {{ $cpcQvIcon }};
        --cpc-qv-border: {{ $cpcQvBorder }};
        --cpc-qv-hover-bg: {{ $cpcQvHoverBg }};
        --cpc-qv-hover-icon: {{ $cpcQvHoverIcon }};
        --cpc-qv-hover-border: {{ $cpcQvHoverBorder }};

        --cpc-cart-bg: {{ $cpcCartBg }};
        --cpc-cart-text: {{ $cpcCartText }};
        --cpc-cart-border: {{ $cpcCartBorder }};
        --cpc-cart-icon: {{ $cpcCartIcon }};
        --cpc-cart-hover-bg: {{ $cpcCartHoverBg }};
        --cpc-cart-hover-text: {{ $cpcCartHoverText }};
        --cpc-cart-hover-border: {{ $cpcCartHoverBorder }};
        --cpc-cart-hover-icon: {{ $cpcCartHoverIcon }};

        --cpc-order-bg: {{ $cpcOrderBg }};
        --cpc-order-text: {{ $cpcOrderText }};
        --cpc-order-border: {{ $cpcOrderBorder }};
        --cpc-order-icon: {{ $cpcOrderIcon }};
        --cpc-order-hover-bg: {{ $cpcOrderHoverBg }};
        --cpc-order-hover-text: {{ $cpcOrderHoverText }};
        --cpc-order-hover-border: {{ $cpcOrderHoverBorder }};
        --cpc-order-hover-icon: {{ $cpcOrderHoverIcon }};

        --cpc-opt-bg: {{ $cpcOptBg }};
        --cpc-opt-text: {{ $cpcOptText }};
        --cpc-opt-border: {{ $cpcOptBorder }};
        --cpc-opt-icon: {{ $cpcOptIcon }};
        --cpc-opt-hover-bg: {{ $cpcOptHoverBg }};
        --cpc-opt-hover-text: {{ $cpcOptHoverText }};
        --cpc-opt-hover-border: {{ $cpcOptHoverBorder }};
        --cpc-opt-hover-icon: {{ $cpcOptHoverIcon }};

        --cpc-stockout-bg: {{ $cpcStockoutBg }};
        --cpc-stockout-text: {{ $cpcStockoutText }};
        --cpc-stockout-border: {{ $cpcStockoutBorder }};
        --cpc-stockout-icon: {{ $cpcStockoutIcon }};
    }

    .custom-product-card {
        background-color: var(--cpc-card-bg) !important;
        border: 1px solid var(--cpc-card-border) !important;
        border-radius: 12px;
        transition: border-color 0.25s ease, box-shadow 0.25s ease, transform 0.25s ease;
        display: flex;
        flex-direction: column;
        height: 100%;
        position: relative;
        overflow: hidden;
    }
    .custom-product-card:hover {
        border-color: var(--cpc-card-border-hover) !important;
        box-shadow: 0 8px 20px var(--cpc-card-shadow-hover) !important;
        transform: translateY(-2px);
    }
    .cpc-img-wrap {
        position: relative;
        width: 100%;
        aspect-ratio: 1/1;
        background-color: var(--cpc-img-wrap-bg) !important;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .cpc-img-wrap img.primary-img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        padding: 6px;
        transition: transform 0.35s ease, opacity 0.35s ease;
        display: block;
        opacity: 1 !important;
    }

    .custom-product-card[data-has-secondary="false"]:hover .cpc-img-wrap img.primary-img,
    .custom-product-card[data-hover-style="zoom"]:hover .cpc-img-wrap img.primary-img {
        transform: scale(1.08) !important;
        opacity: 1 !important;
    }

    .custom-product-card[data-has-secondary="true"][data-hover-style="gallery_flip"] .cpc-img-wrap img.secondary-img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 6px;
        opacity: 0;
        transition: opacity 0.4s ease, transform 0.4s ease;
        transform: rotateY(180deg);
        backface-visibility: hidden;
    }
    .custom-product-card[data-has-secondary="true"][data-hover-style="gallery_flip"]:hover .cpc-img-wrap img.primary-img {
        opacity: 0 !important;
        transform: rotateY(-180deg);
    }
    .custom-product-card[data-has-secondary="true"][data-hover-style="gallery_flip"]:hover .cpc-img-wrap img.secondary-img {
        opacity: 1 !important;
        transform: rotateY(0deg);
    }

    .custom-product-card[data-has-secondary="true"][data-hover-style="slide_up"] .cpc-img-wrap img.secondary-img {
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 6px;
        transition: top 0.35s ease;
    }
    .custom-product-card[data-has-secondary="true"][data-hover-style="slide_up"]:hover .cpc-img-wrap img.primary-img {
        transform: translateY(-100%);
    }
    .custom-product-card[data-has-secondary="true"][data-hover-style="slide_up"]:hover .cpc-img-wrap img.secondary-img {
        top: 0;
    }

    .cpc-badge-sale {
        position: absolute;
        top: 10px;
        left: 10px;
        background-color: var(--cpc-sale-badge-bg) !important;
        color: var(--cpc-sale-badge-text) !important;
        border: 1px solid var(--cpc-sale-badge-border) !important;
        font-size: 9.5px;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 50px;
        z-index: 2;
        box-shadow: 0 2px 6px rgba(0,0,0,0.12);
    }

    .cpc-wishlist-btn {
        position: absolute;
        top: 6px;
        right: 6px;
        background-color: var(--cpc-wishlist-bg) !important;
        border: 1px solid var(--cpc-wishlist-border) !important;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--cpc-wishlist-icon) !important;
        font-size: 11px;
        cursor: pointer;
        z-index: 10;
        transition: transform 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275), background-color 0.2s ease, border-color 0.2s ease;
        margin-right: 5px !important;
        margin-top: 5px !important;
        box-shadow: 0 2px 5px rgba(0,0,0,0.15);
    }
    .cpc-wishlist-btn i {
        color: var(--cpc-wishlist-icon) !important;
        transition: color 0.2s ease;
    }
    .cpc-wishlist-btn:hover {
        background-color: var(--cpc-wishlist-hover-bg) !important;
        border-color: var(--cpc-wishlist-hover-border) !important;
        transform: scale(1.12);
    }
    .cpc-wishlist-btn:hover i {
        color: var(--cpc-wishlist-hover-icon) !important;
    }
    .cpc-wishlist-btn.is-active {
        background-color: var(--cpc-wishlist-active-bg) !important;
        border-color: var(--cpc-wishlist-active-border) !important;
        box-shadow: 0 2px 8px rgba(220, 38, 38, 0.35);
    }
    .cpc-wishlist-btn.is-active i {
        color: var(--cpc-wishlist-active-icon) !important;
        font-weight: 900 !important;
    }

    .cpc-quickview-btn {
        position: absolute;
        top: {{ $showWishlist ? '38px' : '6px' }};
        right: 6px;
        background-color: var(--cpc-qv-bg) !important;
        border: 1px solid var(--cpc-qv-border) !important;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--cpc-qv-icon) !important;
        font-size: 12px;
        cursor: pointer;
        z-index: 10;
        transition: transform 0.25s ease, background-color 0.2s ease, border-color 0.2s ease;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        margin-top: 5px !important;
        margin-right: 5px !important;
    }
    .cpc-quickview-btn i {
        color: var(--cpc-qv-icon) !important;
        transition: color 0.2s ease;
    }
    .cpc-quickview-btn:hover {
        background-color: var(--cpc-qv-hover-bg) !important;
        border-color: var(--cpc-qv-hover-border) !important;
        transform: scale(1.12);
    }
    .cpc-quickview-btn:hover i {
        color: var(--cpc-qv-hover-icon) !important;
    }

    .cpc-body {
        background-color: var(--cpc-body-bg) !important;
        padding: 8px 8px 10px 8px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }
    .cpc-title {
        font-size: 12.5px;
        font-weight: 600;
        color: var(--cpc-title) !important;
        margin-bottom: 2px;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
        transition: color 0.2s ease;
    }
    .cpc-title:hover {
        color: var(--cpc-title-hover) !important;
    }
    .cpc-category {
        font-size: 10.5px;
        color: var(--cpc-category) !important;
        margin-bottom: 3px;
        display: block;
        transition: color 0.2s ease;
        text-decoration: none;
    }
    .cpc-category:hover {
        color: var(--cpc-category-hover) !important;
    }
    .cpc-price-box {
        display: flex;
        align-items: baseline;
        gap: 5px;
        margin-bottom: 3px;
    }
    .cpc-current-price {
        font-size: 13.5px;
        font-weight: 800;
        color: var(--cpc-price) !important;
    }
    .cpc-old-price {
        font-size: 10.5px;
        color: var(--cpc-old-price) !important;
        text-decoration: line-through;
    }
    .cpc-rating {
        font-size: 9.5px;
        color: var(--cpc-rating-star) !important;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 1px;
    }
    .cpc-rating i {
        color: var(--cpc-rating-star) !important;
    }
    .cpc-rating-count {
        color: var(--cpc-rating-text) !important;
    }

    .cpc-btn-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
        margin-top: auto;
    }
    
    /* Cart Button */
    .cpc-btn-cart {
        background-color: var(--cpc-cart-bg) !important;
        color: var(--cpc-cart-text) !important;
        border: 1px solid var(--cpc-cart-border) !important;
        font-size: 11px;
        font-weight: 700;
        padding: 6px 4px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        width: 100%;
    }
    .cpc-btn-cart i {
        color: var(--cpc-cart-icon) !important;
        transition: color 0.2s ease;
    }
    .cpc-btn-cart:hover {
        background-color: var(--cpc-cart-hover-bg) !important;
        color: var(--cpc-cart-hover-text) !important;
        border-color: var(--cpc-cart-hover-border) !important;
    }
    .cpc-btn-cart:hover i {
        color: var(--cpc-cart-hover-icon) !important;
    }

    /* Order / Buy Now Button */
    .cpc-btn-order {
        background-color: var(--cpc-order-bg) !important;
        color: var(--cpc-order-text) !important;
        border: 1px solid var(--cpc-order-border) !important;
        font-size: 11px;
        font-weight: 700;
        padding: 6px 4px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        width: 100%;
    }
    .cpc-btn-order i {
        color: var(--cpc-order-icon) !important;
        transition: color 0.2s ease;
    }
    .cpc-btn-order:hover {
        background-color: var(--cpc-order-hover-bg) !important;
        color: var(--cpc-order-hover-text) !important;
        border-color: var(--cpc-order-hover-border) !important;
    }
    .cpc-btn-order:hover i {
        color: var(--cpc-order-hover-icon) !important;
    }

    /* Select Option Button */
    .cpc-btn-opt {
        background-color: var(--cpc-opt-bg) !important;
        color: var(--cpc-opt-text) !important;
        border: 1px solid var(--cpc-opt-border) !important;
        font-size: 11px;
        font-weight: 700;
        padding: 6px 4px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        width: 100%;
    }
    .cpc-btn-opt i {
        color: var(--cpc-opt-icon) !important;
        transition: color 0.2s ease;
    }
    .cpc-btn-opt:hover {
        background-color: var(--cpc-opt-hover-bg) !important;
        color: var(--cpc-opt-hover-text) !important;
        border-color: var(--cpc-opt-hover-border) !important;
    }
    .cpc-btn-opt:hover i {
        color: var(--cpc-opt-hover-icon) !important;
    }

    /* Stock Out Button */
    .cpc-btn-stockout {
        background-color: var(--cpc-stockout-bg) !important;
        color: var(--cpc-stockout-text) !important;
        border: 1px solid var(--cpc-stockout-border) !important;
        font-size: 11px !important;
        font-weight: 800 !important;
        padding: 7px 4px !important;
        border-radius: 6px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 5px !important;
        cursor: not-allowed !important;
        width: 100% !important;
        letter-spacing: 0.5px;
        box-shadow: 0 2px 6px rgba(220, 38, 38, 0.15);
    }
    .cpc-btn-stockout i {
        color: var(--cpc-stockout-icon) !important;
    }

    @media (max-width: 575.98px) {
        .cpc-body { padding: 6px 5px 8px 5px; }
        .cpc-title { font-size: 11.5px; }
        .cpc-current-price { font-size: 12.5px; }
        .cpc-btn-cart, .cpc-btn-order, .cpc-btn-opt, .cpc-btn-stockout {
            font-size: 10px !important;
            padding: 5px 2px !important;
            border-radius: 5px !important;
        }
        .cpc-btn-cart i, .cpc-btn-order i, .cpc-btn-opt i, .cpc-btn-stockout i { font-size: 9px !important; }
    }
</style>
@endonce

<div class="custom-product-card theme-product-card shadow-xs" data-has-secondary="{{ $hasSecondary ? 'true' : 'false' }}" data-hover-style="{{ $cardHoverStyle }}">
    <div class="cpc-img-wrap">
        @if($showSaleBadge && $hasDiscount)
            <span class="cpc-badge-sale">{{ $saleBadgeText }}</span>
        @endif
        
        @if($showWishlist)
            <button type="button" 
                    class="cpc-wishlist-btn btn-wishlist-toggle {{ $isWishlisted ? 'is-active' : '' }}" 
                    data-id="{{ $product->id }}" 
                    title="{{ $isWishlisted ? 'Remove from Wishlist' : 'Add to Wishlist' }}">
                <i class="{{ $isWishlisted ? 'fas fa-heart' : 'far fa-heart' }}"></i>
            </button>
        @endif

        @if($enableQuickView)
            <button type="button" class="cpc-quickview-btn" title="Quick View" onclick="event.preventDefault(); event.stopPropagation(); window.triggerQuickViewModal({{ $product->id }});">
                <i class="fas fa-eye"></i>
            </button>
        @endif

        <a href="{{ route('product.details', $product->slug ?? $product->id) }}" class="w-100 h-100 d-flex align-items-center justify-content-center position-relative overflow-hidden">
            <img src="{{ $thumb }}" alt="{{ $product->title ?? $product->name }}" class="primary-img" loading="lazy" onerror="this.src='https://placehold.co/300x300?text=No+Image'">
            @if($hasSecondary)
                <img src="{{ $secondaryImg }}" alt="{{ $product->title ?? $product->name }}" class="secondary-img" loading="lazy" onerror="this.style.display='none'">
            @endif
        </a>
    </div>

    <div class="cpc-body">
        <a href="{{ route('product.details', $product->slug ?? $product->id) }}" class="text-decoration-none">
            <h6 class="cpc-title theme-product-title" title="{{ $product->title ?? $product->name }}">
                {{ $product->title ?? $product->name }}
            </h6>
        </a>
        <span class="cpc-category text-truncate">{{ $product->category->name ?? 'Collection' }}</span>

        <div class="cpc-price-box">
            <span class="cpc-current-price theme-product-price bn-digit">
                ৳{{ $showPriceDecimal ? number_format($finalPrice, 2) : number_format($finalPrice, 0) }}
            </span>
            @if($hasDiscount)
                <span class="cpc-old-price bn-digit">
                    ৳{{ $showPriceDecimal ? number_format($regularPrice, 2) : number_format($regularPrice, 0) }}
                </span>
            @endif
        </div>

        @if($showReviews)
            <div class="cpc-rating">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <span class="cpc-rating-count ms-1" style="font-size: 8.5px;">(0)</span>
            </div>
        @endif

        <div class="cpc-btn-group">
            @if($displayStockOutBtn)
                <button type="button" class="cpc-btn-stockout" disabled title="Out of Stock">
                    <i class="fas fa-times-circle"></i> <span>STOCK OUT</span>
                </button>
            @elseif($hasVariants)
                <a href="{{ route('product.details', $product->slug ?? $product->id) }}" class="cpc-btn-opt text-center py-2 text-decoration-none">
                    <i class="fas fa-sliders-h me-1"></i> <span>SELECT OPTIONS</span>
                </a>
            @else
                <form action="{{ route('cart.add', $product->id) }}" method="POST" class="d-flex flex-column gap-1 product-cart-form" onsubmit="handleProductCardSubmit(event, this, {{ $product->id }})">
                    @csrf
                    <input type="hidden" name="quantity" value="1">
                    
                    <button type="submit" name="action" value="add_to_cart" class="cpc-btn-cart theme-btn-cart">
                        <i class="fas fa-shopping-bag"></i> <span>ADD TO CART</span>
                    </button>

                    @if($showBuyNowBtn)
                        <button type="submit" name="action" value="order_now" class="cpc-btn-order theme-btn-order">
                            <i class="fas fa-bolt"></i> <span>ORDER NOW</span>
                        </button>
                    @endif
                </form>
            @endif
        </div>
    </div>
</div>

<script>
if (typeof handleProductCardSubmit === 'undefined') {
    window.handleProductCardSubmit = function(e, form, productId) {
        var submitter = e.submitter;
        if (submitter && submitter.value === 'add_to_cart') {
            @if($enableFloatingCart)
                e.preventDefault();
                var csrf = form.querySelector('input[name="_token"]').value;
                fetch("{{ url('/cart/add') }}/" + productId, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ quantity: 1, action: 'add_to_cart' })
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (data.status === 'success') {
                        if (window.updateAllCartBadges) window.updateAllCartBadges(data.cart_count);
                        if (window.openFloatingCartDrawer) window.openFloatingCartDrawer();
                    }
                });
            @endif
        }
    };
}
</script>