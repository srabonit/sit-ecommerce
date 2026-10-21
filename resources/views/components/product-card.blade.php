@php
    // ১. রিয়েল-টাইম সেটিং চেক
    $stockOutSetting = DB::table('settings')->where('key', 'card_show_stockout_btn')->value('value');
    $showStockOutSetting = ($stockOutSetting === '1' || $stockOutSetting == 1);

    $dbSettings = DB::table('settings')->whereIn('key', [
        'card_show_price_decimal',
        'card_show_review',
        'card_show_wishlist',
        'card_show_sale_badge',
        'card_sale_badge_text',
        'card_enable_quick_view',
        'card_hover_style',
        'feature_buy_now_btn',
        'feature_floating_cart_modal',
        'feature_quick_view'
    ])->pluck('value', 'key')->toArray();

    $showPriceDecimal   = ($dbSettings['card_show_price_decimal'] ?? '1') == '1';
    $showReviews        = ($dbSettings['card_show_review'] ?? '1') == '1';
    $showWishlist       = ($dbSettings['card_show_wishlist'] ?? '1') == '1';
    $showSaleBadge      = ($dbSettings['card_show_sale_badge'] ?? '1') == '1';
    $saleBadgeText      = !empty($dbSettings['card_sale_badge_text']) ? $dbSettings['card_sale_badge_text'] : 'For Sale!';
    $enableQuickView    = ($dbSettings['card_enable_quick_view'] ?? ($dbSettings['feature_quick_view'] ?? '1')) == '1';
    $cardHoverStyle     = $dbSettings['card_hover_style'] ?? 'zoom';
    $showBuyNowBtn      = ($dbSettings['feature_buy_now_btn'] ?? '1') == '1';
    $enableFloatingCart = ($dbSettings['feature_floating_cart_modal'] ?? '1') == '1';

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

    // Universal Safe Dynamic Image Resolver (Connected to ImagePathService)
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

<style>
    .custom-product-card {
        background: var(--card-bg-color, #ffffff);
        border: 1px solid #edf2f7;
        border-radius: 12px;
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
        height: 100%;
        position: relative;
        overflow: hidden;
    }
    .custom-product-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 6px 16px rgba(0,0,0,0.06);
    }
    .cpc-img-wrap {
        position: relative;
        width: 100%;
        aspect-ratio: 1/1;
        background: #f8fafc;
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
        background: #ef4444;
        color: #ffffff;
        font-size: 9.5px;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 50px;
        z-index: 2;
    }

    .cpc-wishlist-btn {
        position: absolute;
        top: 6px;
        right: 6px;
        background: #f1592a !important;
        border: 1px solid rgba(255, 255, 255, 0.4) !important;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff !important;
        font-size: 11px;
        cursor: pointer;
        z-index: 10;
        transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275), background 0.2s ease;
        margin-right: 5px !important;
        margin-top: 5px !important;
        box-shadow: 0 2px 5px rgba(0,0,0,0.15);
    }
    .cpc-wishlist-btn:hover {
        background: #1f2033 !important;
        color: #ffffff !important;
        transform: scale(1.1);
    }
    .cpc-wishlist-btn.is-active {
        background: #dc2626 !important;
        box-shadow: 0 2px 8px rgba(220, 38, 38, 0.4);
    }
    .cpc-wishlist-btn.is-active i {
        color: #ffffff !important;
        font-weight: 900 !important;
    }

    .cpc-quickview-btn {
        position: absolute;
        top: {{ $showWishlist ? '38px' : '6px' }};
        right: 6px;
        background: #ef4444 ;
        border: 1px solid rgba(255, 255, 255, 0.4) !important;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff !important;
        font-size: 12px;
        cursor: pointer;
        z-index: 10;
        transition: 0.2s;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        margin-top: 5px !important;
        margin-right: 5px !important;
    }
    .cpc-quickview-btn:hover {
        background: #1f2033 !important;
        color: #ffffff !important;
        transform: scale(1.1);
    }

    .cpc-body {
        padding: 8px 8px 10px 8px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }
    .cpc-title {
        font-size: 12.5px;
        font-weight: 600;
        color: var(--product-title-color, #1e293b);
        margin-bottom: 2px;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .cpc-category {
        font-size: 10.5px;
        color: #64748b;
        margin-bottom: 3px;
        display: block;
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
        color: var(--product-price-color, #0f172a);
    }
    .cpc-old-price {
        font-size: 10.5px;
        color: #94a3b8;
        text-decoration: line-through;
    }
    .cpc-rating {
        font-size: 9.5px;
        color: #f59e0b;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 1px;
    }

    .cpc-btn-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
        margin-top: auto;
    }
    .cpc-btn-cart {
        background-color: var(--card-btn-cart-bg, #2563eb);
        color: var(--card-btn-cart-text, #ffffff);
        border: none;
        font-size: 11px;
        font-weight: 700;
        padding: 6px 4px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        cursor: pointer;
        transition: 0.2s;
        text-decoration: none;
        width: 100%;
    }
    .cpc-btn-cart:hover { filter: brightness(0.9); color: #ffffff; }
    .cpc-btn-order {
        background-color: var(--card-btn-order-bg, #0f172a);
        color: var(--card-btn-order-text, #ffffff);
        border: none;
        font-size: 11px;
        font-weight: 700;
        padding: 6px 4px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        cursor: pointer;
        transition: 0.2s;
        text-decoration: none;
        width: 100%;
    }
    .cpc-btn-order:hover { filter: brightness(1.2); color: #ffffff; }

    .cpc-btn-stockout {
        background-color: #fee2e2 !important;
        color: #dc2626 !important;
        border: none !important;
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
        box-shadow: 0 2px 6px rgba(220, 38, 38, 0.2);
    }

    @media (max-width: 575.98px) {
        .cpc-body { padding: 6px 5px 8px 5px; }
        .cpc-title { font-size: 11.5px; }
        .cpc-current-price { font-size: 12.5px; }
        .cpc-btn-cart, .cpc-btn-order, .cpc-btn-stockout {
            font-size: 10px !important;
            padding: 5px 2px !important;
            border-radius: 5px !important;
        }
        .cpc-btn-cart i, .cpc-btn-order i, .cpc-btn-stockout i { font-size: 9px !important; }
    }
</style>

<div class="custom-product-card shadow-xs" data-has-secondary="{{ $hasSecondary ? 'true' : 'false' }}" data-hover-style="{{ $cardHoverStyle }}">
    <div class="cpc-img-wrap">
        @if($showSaleBadge && $hasDiscount)
            <span class="cpc-badge-sale">{{ $saleBadgeText }}</span>
        @endif
        
        @if($showWishlist)
            <button type="button" 
                    class="cpc-wishlist-btn btn-wishlist-toggle {{ $isWishlisted ? 'is-active' : '' }}" 
                    data-id="{{ $product->id }}" 
                    title="{{ $isWishlisted ? 'Add / remove to Wish' : 'Add / remove to Wish' }}">
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
            <h6 class="cpc-title" title="{{ $product->title ?? $product->name }}">
                {{ $product->title ?? $product->name }}
            </h6>
        </a>
        <span class="cpc-category text-truncate">{{ $product->category->name ?? 'Collection' }}</span>

        <div class="cpc-price-box">
            <span class="cpc-current-price">
                ৳{{ $showPriceDecimal ? number_format($finalPrice, 2) : number_format($finalPrice, 0) }}
            </span>
            @if($hasDiscount)
                <span class="cpc-old-price">
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
                <span class="text-muted ms-1" style="font-size: 8.5px;">(0)</span>
            </div>
        @endif

        <div class="cpc-btn-group">
            @if($displayStockOutBtn)
                <button type="button" class="cpc-btn-stockout" disabled title="Out of Stock">
                    <i class="fas fa-times-circle"></i> <span>STOCK OUT</span>
                </button>
            @elseif($hasVariants)
                <a href="{{ route('product.details', $product->slug ?? $product->id) }}" class="cpc-btn-cart text-center py-2 text-decoration-none" style="background-color: #0f172a; color: #fff;">
                    <i class="fas fa-sliders-h me-1"></i> SELECT OPTIONS
                </a>
            @else
                <form action="{{ route('cart.add', $product->id) }}" method="POST" class="d-flex flex-column gap-1 product-cart-form" onsubmit="handleProductCardSubmit(event, this, {{ $product->id }})">
                    @csrf
                    <input type="hidden" name="quantity" value="1">
                    
                    <button type="submit" name="action" value="add_to_cart" class="cpc-btn-cart">
                        <i class="fas fa-shopping-bag"></i> <span>ADD TO CART</span>
                    </button>

                    @if($showBuyNowBtn)
                        <button type="submit" name="action" value="order_now" class="cpc-btn-order">
                            <i class="fas fa-bolt text-warning"></i> <span>ORDER NOW</span>
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