@extends('layouts.app')

@section('title', 'Shopping Cart - ' . config('app.name', 'SIT ECOMMERCE'))

@section('content')
@php
    $subtotal = 0;
    $cartProductIds = [];

    foreach($cart as $id => $item) {
        $rawPrice = $item['price'] ?? 0;
        if (is_string($rawPrice)) {
            $rawPrice = preg_replace('/[^0-9.]/', '', $rawPrice);
        }
        $subtotal += ((float)$rawPrice * (int)($item['quantity'] ?? 1));

        $pId = (int)($item['id'] ?? (is_numeric($id) ? $id : ($item['product_id'] ?? 0)));
        if ($pId > 0) {
            $cartProductIds[] = $pId;
        }
    }

    $cartCategoryIds = [];
    if (!empty($cartProductIds) && \Illuminate\Support\Facades\Schema::hasTable('products')) {
        $cartCategoryIds = \Illuminate\Support\Facades\DB::table('products')
            ->whereIn('id', $cartProductIds)
            ->pluck('category_id')
            ->filter()
            ->map(fn($id) => (int)$id)
            ->toArray();
    }

    // কুপন ও ভাউচার ডিসকাউন্ট রেজলভার
    $appliedCoupon = session('applied_coupon');
    $appliedVoucher = session('applied_voucher');
    $couponCode = is_array($appliedCoupon) ? ($appliedCoupon['code'] ?? null) : $appliedCoupon;
    $voucherCode = is_array($appliedVoucher) ? ($appliedVoucher['code'] ?? ($appliedVoucher['voucher_code'] ?? null)) : $appliedVoucher;

    $couponDiscount = $calc['coupon_discount'] ?? (is_array($appliedCoupon) ? (float)($appliedCoupon['discount'] ?? 0) : 0);
    $voucherDiscount = $calc['voucher_discount'] ?? (is_array($appliedVoucher) ? (float)($appliedVoucher['discount'] ?? 0) : 0);
    $totalDiscount = $couponDiscount + $voucherDiscount;

    $deliveryCharge = 70;
    $grandTotal = $subtotal > 0 ? max(0, ($subtotal - $totalDiscount) + $deliveryCharge) : 0;
    $isEmpty = count($cart) === 0;

    $userId = auth()->id();
    $now = \Carbon\Carbon::now();

    // সক্রিয় পাবলিক কুপন কোড সংগ্রহ (টার্গেট ইউজার ও প্রোডাক্ট/ক্যাটাগরি ম্যাচিং ভ্যালিডেশনসহ)
    $availableStoreCoupons = collect();
    if (\Illuminate\Support\Facades\Schema::hasTable('coupons')) {
        $cQuery = \Illuminate\Support\Facades\DB::table('coupons')
            ->where('is_active', 1)
            ->where('is_public', 1)
            ->where(function ($q) use ($now) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('expire_date')->orWhere('expire_date', '>=', $now);
            });

        if (\Illuminate\Support\Facades\Schema::hasColumn('coupons', 'target_user_type')) {
            if ($userId) {
                $cQuery->where(function($q) {
                    $q->whereNull('target_user_type')->orWhereIn('target_user_type', ['all', 'registered_only']);
                });
            }
        }

        $availableStoreCoupons = $cQuery->get()->map(function ($c) use ($userId, $subtotal, $cartProductIds, $cartCategoryIds) {
            $minReq = (float)($c->min_order_amount ?? ($c->min_spend ?? 0));
            $target = $c->target_user_type ?? 'all';
            $appTo  = $c->applicable_to ?? 'all';

            $isEligible = true;
            $reason = '';

            if ($target === 'registered_only' && !$userId) {
                $isEligible = false;
                $reason = 'Login Required';
            } elseif ($target === 'guest_only' && $userId) {
                $isEligible = false;
                $reason = 'Guest Only';
            } elseif ($minReq > 0 && $subtotal < $minReq) {
                $isEligible = false;
                $reason = 'Min ৳' . number_format($minReq, 0);
            } elseif ($appTo === 'specific_products') {
                $includedP = \Illuminate\Support\Facades\Schema::hasTable('coupon_products')
                    ? \Illuminate\Support\Facades\DB::table('coupon_products')->where('coupon_id', $c->id)->where('is_excluded', 0)->pluck('product_id')->toArray()
                    : [];
                if (empty($includedP) && !empty($c->product_ids)) {
                    $includedP = is_array($c->product_ids) ? $c->product_ids : (json_decode($c->product_ids, true) ?? []);
                }
                if (!empty($includedP) && empty(array_intersect($cartProductIds, array_map('intval', $includedP)))) {
                    $isEligible = false;
                    $reason = 'Item Mismatch';
                }
            } elseif ($appTo === 'specific_categories') {
                $includedC = \Illuminate\Support\Facades\Schema::hasTable('coupon_categories')
                    ? \Illuminate\Support\Facades\DB::table('coupon_categories')->where('coupon_id', $c->id)->where('is_excluded', 0)->pluck('category_id')->toArray()
                    : [];
                if (empty($includedC) && !empty($c->category_ids)) {
                    $includedC = is_array($c->category_ids) ? $c->category_ids : (json_decode($c->category_ids, true) ?? []);
                }
                if (!empty($includedC) && empty(array_intersect($cartCategoryIds, array_map('intval', $includedC)))) {
                    $isEligible = false;
                    $reason = 'Category Mismatch';
                }
            }

            $c->is_eligible = $isEligible;
            $c->eligibility_reason = $reason;
            return $c;
        });
    }

    // কাস্টমারের নিজস্ব সক্রিয় ভাউচার ওয়ালেট
    $userVouchers = collect();
    if ($userId && \Illuminate\Support\Facades\Schema::hasTable('customer_vouchers') && \Illuminate\Support\Facades\Schema::hasTable('vouchers')) {
        $userVouchers = \Illuminate\Support\Facades\DB::table('customer_vouchers')
            ->join('vouchers', 'customer_vouchers.voucher_id', '=', 'vouchers.id')
            ->where('customer_vouchers.user_id', $userId)
            ->whereIn('customer_vouchers.status', ['claimed', 'restored'])
            ->where(function ($q) use ($now) {
                $q->whereNull('customer_vouchers.expires_at')->orWhere('customer_vouchers.expires_at', '>=', $now);
            })
            ->select(
                'vouchers.id',
                'vouchers.title',
                'vouchers.voucher_type',
                'vouchers.discount_amount',
                'vouchers.max_discount',
                'vouchers.min_spend',
                'customer_vouchers.voucher_code'
            )
            ->get();
    }

    // গ্লোবাল কালার সেটিংস ক্যাশ রেজলভার
    static $cartPageSettingsCache = null;
    if ($cartPageSettingsCache === null) {
        $cartPageSettingsCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
            return DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $cartSettings = $cartPageSettingsCache ?? [];

    // Cart Page Scoped Dynamic Colors
    $cartPageBg          = $cartSettings['color_cart_page_bg'] ?? ($cartSettings['color_site_bg'] ?? '#f8fafc');
    $cartItemBg          = $cartSettings['color_cart_item_bg'] ?? '#ffffff';
    $cartItemBorder      = $cartSettings['color_cart_item_border'] ?? '#e2e8f0';
    $cartItemBorderHover = $cartSettings['color_cart_item_border_hover'] ?? '#cbd5e1';
    $cartItemImgBg       = $cartSettings['color_cart_item_img_bg'] ?? '#f8fafc';
    $cartItemTitle       = $cartSettings['color_cart_item_title'] ?? '#0f172a';
    $cartItemTitleHover  = $cartSettings['color_cart_item_title_hover'] ?? '#2563eb';
    $cartItemPrice       = $cartSettings['color_cart_item_price'] ?? '#0f172a';
    $cartQtyPillBg       = $cartSettings['color_cart_qty_btn_bg'] ?? '#f1f5f9';
    $cartQtyBtnText      = $cartSettings['color_cart_qty_btn_text'] ?? '#475569';
    $cartQtyBtnHovBg     = $cartSettings['color_cart_qty_btn_hover_bg'] ?? '#e2e8f0';
    $cartTrashBtnColor   = $cartSettings['color_cart_remove_btn'] ?? '#94a3b8';
    $cartTrashBtnHover   = $cartSettings['color_cart_remove_btn_hover'] ?? '#ef4444';

    $cartPanelBg         = $cartSettings['color_cart_panel_bg'] ?? '#ffffff';
    $cartPanelBorder     = $cartSettings['color_cart_panel_border'] ?? '#e2e8f0';
    $cartCheckoutBtnBg   = $cartSettings['color_cart_checkout_btn_bg'] ?? '#2563eb';
    $cartCheckoutBtnText = $cartSettings['color_cart_checkout_btn_text'] ?? '#ffffff';
    $cartCheckoutBtnHov  = $cartSettings['color_cart_checkout_btn_hover'] ?? '#1d4ed8';

    // সার্বজনীন কার্ট ইমেজ রেজলভার
    $resolveCartImg = function($path, $fallback = 'https://placehold.co/120x120?text=Product') {
        if (empty($path) || $path === 'undefined' || $path === 'null') {
            return $fallback;
        }
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path, $fallback);
        }
        if (function_exists('show_image')) {
            return show_image($path, $fallback);
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        return asset(ltrim($path, '/'));
    };
@endphp

<!-- Font Awesome 6.5.1 CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<!-- Google Fonts: Plus Jakarta Sans & Inter -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --cart-pg-bg: {{ $cartPageBg }};
        --cart-it-bg: {{ $cartItemBg }};
        --cart-it-border: {{ $cartItemBorder }};
        --cart-it-border-hover: {{ $cartItemBorderHover }};
        --cart-it-img-bg: {{ $cartItemImgBg }};
        --cart-it-title: {{ $cartItemTitle }};
        --cart-it-title-hover: {{ $cartItemTitleHover }};
        --cart-it-price: {{ $cartItemPrice }};
        --cart-it-qty-bg: {{ $cartQtyPillBg }};
        --cart-it-qty-text: {{ $cartQtyBtnText }};
        --cart-it-qty-hov: {{ $cartQtyBtnHovBg }};
        --cart-it-trash: {{ $cartTrashBtnColor }};
        --cart-it-trash-hov: {{ $cartTrashBtnHover }};

        --cart-pnl-bg: {{ $cartPanelBg }};
        --cart-pnl-border: {{ $cartPanelBorder }};
        --cart-chk-btn-bg: {{ $cartCheckoutBtnBg }};
        --cart-chk-btn-text: {{ $cartCheckoutBtnText }};
        --cart-chk-btn-hov: {{ $cartCheckoutBtnHov }};
    }

    #cart-main-page-root {
        font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        background-color: var(--cart-pg-bg) !important;
        min-height: 80vh;
        letter-spacing: -0.01em;
    }

    /* FontAwesome Protection */
    #cart-main-page-root i,
    #cart-main-page-root .fa,
    #cart-main-page-root .fas,
    #cart-main-page-root .far,
    #cart-main-page-root .fab,
    #cart-main-page-root .fa-solid,
    #cart-main-page-root .fa-regular {
        font-family: "Font Awesome 6 Free", "Font Awesome 5 Free", "FontAwesome" !important;
        font-style: normal;
        font-weight: 900;
        line-height: 1;
        letter-spacing: normal !important;
        display: inline-block;
    }
    #cart-main-page-root .far,
    #cart-main-page-root .fa-regular {
        font-weight: 400 !important;
    }

    .bn-digit,
    .font-monospace {
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif !important;
        font-weight: 700 !important;
        font-variant-numeric: tabular-nums;
    }

    /* Minimalist Cards with Strict Radius */
    .cart-item-card {
        border: 1px solid var(--cart-it-border) !important;
        border-radius: 6px !important;
        background-color: var(--cart-it-bg) !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .cart-item-card:hover {
        border-color: var(--cart-it-border-hover) !important;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    }

    .cart-img-wrapper {
        width: 58px;
        height: 58px;
        background-color: var(--cart-it-img-bg) !important;
        border: 1px solid var(--cart-it-border) !important;
        border-radius: 5px !important;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 4px;
        flex-shrink: 0;
    }
    .cart-item-title a {
        color: var(--cart-it-title) !important;
        transition: color 0.15s ease;
        text-decoration: none;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.35;
    }
    .cart-item-title a:hover {
        color: var(--cart-it-title-hover) !important;
    }

    .cart-price-highlight {
        color: var(--cart-it-price) !important;
        font-weight: 700;
    }

    /* Quantity Stepper Pill */
    .qty-pill {
        background-color: var(--cart-it-qty-bg) !important;
        border-radius: 5px !important;
        padding: 2px 4px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        border: 1px solid var(--cart-it-border);
    }
    .qty-action-btn {
        border: none;
        background: transparent;
        color: var(--cart-it-qty-text) !important;
        font-weight: 700;
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        transition: all 0.15s ease;
        font-size: 12px;
        cursor: pointer;
    }
    .qty-action-btn:hover {
        background-color: var(--cart-it-qty-hov) !important;
        color: #0f172a;
    }
    .qty-value {
        font-size: 12px;
        font-weight: 700;
        min-width: 20px;
        text-align: center;
        color: var(--cart-it-title) !important;
    }

    /* Trash Button */
    .trash-action-btn {
        background: none;
        border: none;
        color: var(--cart-it-trash) !important;
        font-size: 14px;
        transition: all 0.15s ease;
        padding: 6px;
        border-radius: 5px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .trash-action-btn:hover {
        color: var(--cart-it-trash-hov) !important;
        background-color: #fee2e2;
    }

    /* Side Panels */
    .panel-card {
        border: 1px solid var(--cart-pnl-border) !important;
        border-radius: 6px !important;
        background-color: var(--cart-pnl-bg) !important;
        padding: 16px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
    }

    @media (min-width: 992px) {
        .sticky-summary-panel {
            position: sticky;
            top: 20px;
            z-index: 10;
        }
    }

    /* Checkout & Action Buttons */
    .cart-checkout-btn {
        background-color: var(--cart-chk-btn-bg) !important;
        border-color: var(--cart-chk-btn-bg) !important;
        color: var(--cart-chk-btn-text) !important;
        font-weight: 700;
        border-radius: 5px !important;
        transition: all 0.15s ease;
    }
    .cart-checkout-btn:hover {
        background-color: var(--cart-chk-btn-hov) !important;
        border-color: var(--cart-chk-btn-hov) !important;
        color: var(--cart-chk-btn-text) !important;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
    }

    .btn-compact {
        font-size: 11px !important;
        padding: 6px 14px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px !important;
        transition: all 0.15s ease;
    }

    /* Interactive Available Offer Chip */
    .promo-offer-item {
        background: #ffffff;
        border: 1px dashed #cbd5e1;
        border-radius: 6px;
        padding: 6px 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        transition: all 0.15s ease;
    }
    .promo-offer-item.eligible {
        border-color: #93c5fd;
        background: #f8faff;
    }
    .promo-offer-item.eligible:hover {
        border-color: #2563eb;
        background: #eff6ff;
    }
    .promo-offer-item.ineligible {
        opacity: 0.65;
        background: #f1f5f9;
        cursor: not-allowed;
    }

    /* =========================================================================
       MINIMAL MODAL DESIGN SYSTEM (Exact Match with Image Reference)
       ========================================================================= */
    .minimal-modal-card {
        background: #ffffff !important;
        border: 1px solid #f1f5f9 !important;
        border-radius: 12px !important;
        box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.12), 0 0 1px 1px rgba(15, 23, 42, 0.04) !important;
        padding: 24px 26px !important;
        text-align: left !important;
    }

    .minimal-modal-title {
        font-size: 16px !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        margin-bottom: 6px !important;
        letter-spacing: -0.02em !important;
    }

    .minimal-modal-subtext {
        font-size: 13px !important;
        color: #64748b !important;
        margin-bottom: 22px !important;
        line-height: 1.45 !important;
    }

    .btn-minimal-delete {
        background-color: #c93424 !important;
        border: none !important;
        color: #ffffff !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        padding: 7px 22px !important;
        border-radius: 7px !important;
        box-shadow: 0 4px 12px rgba(201, 52, 36, 0.28) !important;
        transition: all 0.15s ease !important;
        cursor: pointer !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    .btn-minimal-delete:hover {
        background-color: #b02717 !important;
        box-shadow: 0 6px 14px rgba(201, 52, 36, 0.38) !important;
        color: #ffffff !important;
    }

    .btn-minimal-cancel {
        background: transparent !important;
        border: none !important;
        color: #64748b !important;
        font-weight: 500 !important;
        font-size: 13.5px !important;
        padding: 7px 14px !important;
        box-shadow: none !important;
        transition: color 0.15s ease !important;
        cursor: pointer !important;
    }
    .btn-minimal-cancel:hover {
        color: #0f172a !important;
        background: transparent !important;
    }

    /* Mobile Responsive Layout (< 576px) */
    @media (max-width: 575.98px) {
        .cart-item-card {
            padding: 10px !important;
            gap: 10px !important;
        }
        .cart-img-wrapper {
            width: 52px !important;
            height: 52px !important;
        }
        .cart-mobile-bottom-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 8px;
            width: 100%;
        }
        .minimal-modal-card {
            padding: 20px 20px !important;
        }
    }
</style>

<div class="py-3 py-md-4" id="cart-main-page-root">
    <div class="container px-2 px-md-3">

        <!-- 1. Empty State (When Cart is Empty) -->
        <div id="cart-empty-state" class="{{ $isEmpty ? '' : 'd-none' }} text-center py-5 panel-card my-4">
            <div class="mb-3 text-secondary">
                <i class="fas fa-shopping-bag fa-3x opacity-25" style="color: var(--cart-it-trash);"></i>
            </div>
            <h4 class="fw-bold mb-1 fs-5" style="color: var(--cart-it-title);">Your cart is empty</h4>
            <p class="text-muted small mb-3">Explore our store to add products to your cart.</p>
            <a href="{{ route('shop') }}" class="btn cart-checkout-btn px-4 py-2 fw-bold small">
                <i class="fas fa-arrow-left me-1"></i> Start Shopping
            </a>
        </div>

        <!-- 2. Active Cart Section -->
        <div id="cart-main-content" class="row g-3 g-lg-4 {{ $isEmpty ? 'd-none' : '' }}">
            
            <!-- Left Column: Product Items -->
            <div class="col-12 col-lg-8">
                <!-- Cart Header with Single Clear Cart Button -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h4 class="fw-bold mb-0 text-dark" style="font-size: 18px;">Shopping Cart</h4>
                        <small class="text-muted font-monospace" style="font-size: 11px;">Manage your cart items and review order pricing</small>
                    </div>
                    <button type="button" class="btn btn-outline-danger btn-compact" onclick="openCancelOrderModal()" title="Clear all items from cart">
                        <i class="fas fa-trash-alt me-1"></i> Clear Cart
                    </button>
                </div>

                <!-- Desktop Table Column Header -->
                <div class="d-none d-md-flex justify-content-between text-muted small px-3 mb-2 fw-bold text-uppercase font-monospace" style="font-size: 10px; letter-spacing: 0.04em;">
                    <span>Product Details</span>
                    <div class="d-flex gap-5" style="margin-right: 52px;">
                        <span>Quantity</span>
                        <span>Total Price</span>
                    </div>
                </div>

                <!-- Items List Container -->
                <div id="cart-items-wrapper" class="d-flex flex-column gap-2 mb-3">
                    @foreach($cart as $id => $item)
                        @php
                            $itemImg = $resolveCartImg($item['image'] ?? null);
                            $rawPrice = $item['price'] ?? 0;
                            if (is_string($rawPrice)) {
                                $rawPrice = preg_replace('/[^0-9.]/', '', $rawPrice);
                            }
                            $itemTotal = (float)$rawPrice * (int)($item['quantity'] ?? 1);
                            $variantLabel = $item['variant_name'] ?? (!empty($item['color']) || !empty($item['size']) ? trim(($item['color'] ?? '') . ' / ' . ($item['size'] ?? '')) : null);
                        @endphp
                        <div id="cart-item-row-{{ $id }}" class="cart-item-card p-2 p-md-3 d-flex align-items-center justify-content-between gap-2">
                            
                            <!-- Thumbnail -->
                            <div class="cart-img-wrapper flex-shrink-0">
                                <img src="{{ $itemImg }}" alt="{{ $item['title'] }}" class="img-fluid rounded object-fit-contain" style="max-height: 48px;" onerror="this.onerror=null; this.src='https://placehold.co/120x120?text=Product';">
                            </div>

                            <!-- Mobile Layout Block (< 576px) -->
                            <div class="d-block d-sm-none flex-grow-1 min-w-0">
                                <div class="d-flex justify-content-between align-items-start gap-1">
                                    <h6 class="mb-0 fw-bold cart-item-title text-truncate" style="max-width: 210px;" title="{{ $item['title'] }}">
                                        <a href="{{ route('product.details', $item['slug'] ?? ($item['id'] ?? $id)) }}">
                                            {{ $item['title'] }}
                                        </a>
                                    </h6>
                                    <button type="button" class="trash-action-btn p-0" onclick="openDeleteModal('{{ $id }}', '{{ addslashes($item['title']) }}')" title="Remove item">
                                        <i class="fas fa-trash-alt" style="font-size: 13px;"></i>
                                    </button>
                                </div>

                                @if(!empty($variantLabel))
                                    <span class="badge bg-light text-secondary border small my-1 font-monospace" style="font-size: 9px; padding: 1px 4px;">{{ $variantLabel }}</span>
                                @endif

                                <small class="text-muted d-block font-monospace" style="font-size: 10.5px;">Unit: ৳{{ number_format((float)$rawPrice, 2) }}</small>
                                
                                <div class="cart-mobile-bottom-row">
                                    <div class="qty-pill">
                                        <button type="button" class="qty-action-btn" onclick="updateQuantityAJAX('{{ $id }}', -1)">-</button>
                                        <span id="qty-val-mob-{{ $id }}" class="qty-value bn-digit">{{ $item['quantity'] }}</span>
                                        <button type="button" class="qty-action-btn" onclick="updateQuantityAJAX('{{ $id }}', 1)">+</button>
                                    </div>
                                    <div class="fw-bold cart-price-highlight font-monospace bn-digit" style="font-size: 13.5px;">
                                        ৳<span id="item-total-mob-{{ $id }}">{{ number_format($itemTotal, 2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Desktop Layout Block (>= 576px) -->
                            <div class="d-none d-sm-flex align-items-center justify-content-between flex-grow-1">
                                <div class="min-w-0 ps-2" style="max-width: 58%;">
                                    <h6 class="mb-1 fw-bold cart-item-title text-truncate" title="{{ $item['title'] }}">
                                        <a href="{{ route('product.details', $item['slug'] ?? ($item['id'] ?? $id)) }}">
                                            {{ $item['title'] }}
                                        </a>
                                    </h6>
                                    @if(!empty($variantLabel))
                                        <span class="badge bg-light text-secondary border small mb-1 font-monospace" style="font-size: 9px; padding: 1px 5px;">{{ $variantLabel }}</span>
                                    @endif
                                    <small class="text-muted d-block font-monospace" style="font-size: 10.5px;">Unit: ৳{{ number_format((float)$rawPrice, 2) }}</small>
                                </div>

                                <div class="d-flex align-items-center gap-3 gap-md-4 flex-shrink-0">
                                    <div class="qty-pill">
                                        <button type="button" class="qty-action-btn" onclick="updateQuantityAJAX('{{ $id }}', -1)">-</button>
                                        <span id="qty-val-{{ $id }}" class="qty-value bn-digit">{{ $item['quantity'] }}</span>
                                        <button type="button" class="qty-action-btn" onclick="updateQuantityAJAX('{{ $id }}', 1)">+</button>
                                    </div>

                                    <div class="fw-bold cart-price-highlight font-monospace bn-digit" style="min-width: 85px; text-align: right; font-size: 13px;">
                                        ৳<span id="item-total-{{ $id }}">{{ number_format($itemTotal, 2) }}</span>
                                    </div>

                                    <button type="button" class="trash-action-btn" onclick="openDeleteModal('{{ $id }}', '{{ addslashes($item['title']) }}')" title="Remove item">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>

                <!-- Bottom Single Clean Navigation Link -->
                <div class="d-flex justify-content-start align-items-center pt-2">
                    <a href="{{ route('shop') }}" class="btn btn-outline-secondary btn-compact">
                        <i class="fas fa-arrow-left me-1"></i> Continue Shopping
                    </a>
                </div>
            </div>

            <!-- Right Column: Coupon & Order Summary -->
            <div class="col-12 col-lg-4">
                <div class="d-flex flex-column gap-3 sticky-summary-panel">
                    
                    <!-- Coupon Card with Eligibility Protection -->
                    <div class="panel-card">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-dark small" style="font-size: 12px;">
                                <i class="fas fa-ticket-alt text-primary me-1"></i> Coupon &amp; Voucher
                            </span>
                            @auth
                                <a href="{{ route('user.coupons') }}" target="_blank" class="small text-decoration-none fw-semibold" style="font-size: 10.5px; color: var(--cart-it-title-hover);">
                                    <i class="fas fa-wallet me-0.5"></i> Wallet
                                </a>
                            @endauth
                        </div>
                        
                        <div class="input-group input-group-sm mb-1.5">
                            <input type="text" id="cart-coupon-input" class="form-control form-control-sm text-uppercase font-monospace bg-light border-0" placeholder="ENTER COUPON CODE" value="{{ $couponCode ?? '' }}" style="font-size: 11px; height: 32px; border-radius: 5px 0 0 5px !important;">
                            <button type="button" id="btn-apply-coupon" class="btn btn-primary px-3 fw-bold small cart-checkout-btn" style="height: 32px; border-radius: 0 5px 5px 0 !important;" onclick="applyCartCoupon()">
                                Apply
                            </button>
                        </div>

                        <!-- Real-time Coupon Message Feedback Area -->
                        <div id="cart-coupon-feedback-msg" class="small mt-1 d-none font-monospace" style="font-size: 11px;"></div>

                        <!-- 1-Click Available Store Coupons Dropdown -->
                        @if($availableStoreCoupons->count() > 0)
                            <div class="mt-2 mb-1">
                                <a class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-1.5" data-bs-toggle="collapse" href="#collapseCartStoreOffers" role="button" aria-expanded="false" style="font-size: 10.5px;">
                                    <i class="fas fa-tags text-primary"></i>
                                    <span class="fw-semibold">Available Promo Codes ({{ $availableStoreCoupons->count() }})</span>
                                    <i class="fas fa-chevron-down" style="font-size: 8.5px;"></i>
                                </a>

                                <div class="collapse" id="collapseCartStoreOffers">
                                    <div class="d-flex flex-column gap-1.5 pt-1" style="max-height: 180px; overflow-y: auto;">
                                        @foreach($availableStoreCoupons as $storeCpn)
                                            <div class="promo-offer-item {{ $storeCpn->is_eligible ? 'eligible' : 'ineligible' }}">
                                                <div class="min-w-0">
                                                    <div class="d-flex align-items-center gap-1">
                                                        <strong class="font-monospace text-primary small" style="font-size: 11px;">{{ $storeCpn->code }}</strong>
                                                        @if(!empty($storeCpn->badge_text))
                                                            <span class="badge bg-warning text-dark py-0 px-1" style="font-size: 8px;">{{ $storeCpn->badge_text }}</span>
                                                        @endif
                                                    </div>
                                                    <small class="text-muted d-block text-truncate" style="font-size: 9.5px;">
                                                        @if($storeCpn->discount_type === 'percentage')
                                                            {{ (float)$storeCpn->discount_amount }}% OFF
                                                        @elseif($storeCpn->discount_type === 'free_shipping')
                                                            Free Shipping
                                                        @else
                                                            ৳{{ number_format($storeCpn->discount_amount ?? 0, 0) }} OFF
                                                        @endif
                                                        @if(!empty($storeCpn->min_order_amount) || !empty($storeCpn->min_spend))
                                                            &bull; Min ৳{{ number_format($storeCpn->min_order_amount ?? $storeCpn->min_spend, 0) }}
                                                        @endif
                                                    </small>
                                                </div>

                                                <div class="flex-shrink-0 text-end">
                                                    @if($storeCpn->is_eligible)
                                                        <button type="button" class="btn btn-outline-primary btn-sm py-0.5 px-2 font-monospace fw-bold" style="font-size: 9.5px; border-radius: 4px;" onclick="applyQuickPromoCode('{{ $storeCpn->code }}')">
                                                            Apply
                                                        </button>
                                                    @else
                                                        <span class="badge bg-secondary-subtle text-muted border font-monospace py-0.5 px-1.5" style="font-size: 8.5px;" title="{{ $storeCpn->eligibility_reason }}">
                                                            <i class="fas fa-lock me-0.5"></i>{{ $storeCpn->eligibility_reason }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Wallet Voucher Selector -->
                        @auth
                            @if(isset($userVouchers) && count($userVouchers) > 0)
                                <div class="mt-2 mb-1">
                                    <select id="cart-voucher-select" class="form-select form-select-sm font-monospace bg-light border-0" style="font-size: 11px; border-radius: 5px;" onchange="applySelectedVoucher(this.value)">
                                        <option value="">-- Apply from your Voucher Wallet --</option>
                                        @foreach($userVouchers as $uv)
                                            @php
                                                $vMin = (float)($uv->min_spend ?? 0);
                                                $isVEligible = ($vMin <= 0 || $subtotal >= $vMin);
                                            @endphp
                                            <option value="{{ $uv->voucher_code }}" {{ $voucherCode === $uv->voucher_code ? 'selected' : '' }} {{ !$isVEligible ? 'disabled' : '' }}>
                                                {{ $uv->title }} (Code: {{ $uv->voucher_code }}) {{ !$isVEligible ? '[Min ৳' . number_format($vMin, 0) . ']' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        @endauth

                        <!-- Applied Discount Badges (Both Coupon and Voucher Supported) -->
                        <div class="d-flex flex-wrap gap-1 mt-2" id="cart-applied-discounts-container">
                            <div id="cart-applied-coupon-pill" class="{{ (!empty($couponCode) && $couponDiscount > 0) ? '' : 'd-none' }}">
                                <div class="d-inline-flex align-items-center gap-1.5 bg-success-subtle text-success border border-success-subtle rounded px-2 py-1 small font-monospace" style="font-size: 10.5px; border-radius: 5px !important;">
                                    <i class="fas fa-tag"></i>
                                    <span>Coupon: <strong id="applied-coupon-code-label">{{ $couponCode ?? '' }}</strong> (-৳<span id="applied-coupon-discount-label">{{ number_format($couponDiscount, 2) }}</span>)</span>
                                    <i class="fas fa-times ms-1 text-danger cursor-pointer" onclick="removeCartCoupon()" title="Remove coupon"></i>
                                </div>
                            </div>

                            <div id="cart-applied-voucher-pill" class="{{ (!empty($voucherCode) && $voucherDiscount > 0) ? '' : 'd-none' }}">
                                <div class="d-inline-flex align-items-center gap-1.5 bg-primary-subtle text-primary border border-primary-subtle rounded px-2 py-1 small font-monospace" style="font-size: 10.5px; border-radius: 5px !important;">
                                    <i class="fas fa-gift"></i>
                                    <span>Voucher: <strong id="applied-voucher-code-label">{{ $voucherCode ?? '' }}</strong> (-৳<span id="applied-voucher-discount-label">{{ number_format($voucherDiscount, 2) }}</span>)</span>
                                    <i class="fas fa-times ms-1 text-danger cursor-pointer" onclick="removeCartVoucher()" title="Remove voucher"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary Panel Card -->
                    <div class="panel-card">
                        <h5 class="fw-bold mb-3 text-dark pb-2 border-bottom" style="font-size: 14px;">Order Summary</h5>

                        <div class="d-flex justify-content-between text-muted mb-2 small font-monospace" style="font-size: 11.5px;">
                            <span>Subtotal</span>
                            <strong class="text-dark">৳<span id="summary-subtotal">{{ number_format($subtotal, 2) }}</span></strong>
                        </div>

                        <!-- Coupon Discount Row -->
                        <div class="d-flex justify-content-between text-success mb-2 small font-monospace {{ $couponDiscount > 0 ? '' : 'd-none' }}" id="row-coupon-discount" style="font-size: 11.5px;">
                            <span>Coupon Discount</span>
                            <strong class="fw-bold">-৳<span id="summary-coupon-discount">{{ number_format($couponDiscount, 2) }}</span></strong>
                        </div>

                        <!-- Voucher Discount Row -->
                        <div class="d-flex justify-content-between text-success mb-2 small font-monospace {{ $voucherDiscount > 0 ? '' : 'd-none' }}" id="row-voucher-discount" style="font-size: 11.5px;">
                            <span>Voucher Discount</span>
                            <strong class="fw-bold">-৳<span id="summary-voucher-discount">{{ number_format($voucherDiscount, 2) }}</span></strong>
                        </div>

                        <!-- Delivery Area Selector -->
                        <div class="mb-2">
                            <label class="small text-muted fw-bold d-block mb-1 font-monospace" style="font-size: 10.5px;">DELIVERY AREA</label>
                            <select id="cart-delivery-selector" class="form-select form-select-sm py-1.5 bg-light border-0 font-monospace" onchange="recalculateTotals()" style="font-size: 11.5px; border-radius: 5px !important;">
                                <option value="inside" data-cost="70" selected>Inside Dhaka (৳70)</option>
                                <option value="outside" data-cost="130">Outside Dhaka (৳130)</option>
                            </select>
                        </div>

                        <div class="d-flex justify-content-between text-muted mb-2 small font-monospace" style="font-size: 11.5px;">
                            <span>Delivery Charge</span>
                            <strong class="text-dark">৳<span id="summary-delivery">70.00</span></strong>
                        </div>

                        <hr class="my-2 opacity-25">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-bold text-dark small" style="font-size: 13px;">Total Payable</span>
                            <h4 class="fw-bold cart-price-highlight mb-0 font-monospace text-danger bn-digit" style="font-size: 18px;">
                                ৳<span id="summary-grandtotal">{{ number_format($grandTotal, 2) }}</span>
                            </h4>
                        </div>

                        <a href="{{ route('checkout') }}" class="btn cart-checkout-btn w-100 py-2.5 shadow-xs text-center fw-bold" style="font-size: 13px;">
                            <span>Proceed to Checkout</span> <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<!-- =========================================================================
     MODAL 1: Single Item Delete Confirmation (Exact Minimal Style of Screenshot)
     ========================================================================= -->
<div class="modal fade" id="customDeleteModal" tabindex="-1" aria-hidden="true" style="z-index: 108000;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content minimal-modal-card">
            <h6 class="minimal-modal-title">Are you sure you want to delete this?</h6>
            <p class="minimal-modal-subtext">
                <span id="modal-product-name" class="fw-semibold text-dark"></span> will be removed from your shopping cart.
            </p>
            <div class="d-flex align-items-center gap-3">
                <button type="button" id="confirm-delete-action-btn" class="btn-minimal-delete col-6">Delete</button>
                <button type="button" class="btn-minimal-cancel col-6" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- =========================================================================
     MODAL 2: Clear Cart Confirmation (Exact Minimal Style of Screenshot)
     ========================================================================= -->
<div class="modal fade" id="customCancelOrderModal" tabindex="-1" aria-hidden="true" style="z-index: 108100;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content minimal-modal-card">
            <h6 class="minimal-modal-title">Are you sure you wanna clear this?</h6>
            <p class="minimal-modal-subtext">
                All products currently in your cart will be permanently removed.
            </p>
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn-minimal-delete" onclick="executeClearCart()">Clear</button>
                <button type="button" class="btn-minimal-cancel" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
    let itemToDeleteId = null;
    let deleteModalObj = null;
    let cancelOrderModalObj = null;

    document.addEventListener('DOMContentLoaded', () => {
        deleteModalObj = new bootstrap.Modal(document.getElementById('customDeleteModal'));
        cancelOrderModalObj = new bootstrap.Modal(document.getElementById('customCancelOrderModal'));

        document.getElementById('confirm-delete-action-btn').addEventListener('click', () => {
            if (itemToDeleteId) {
                performItemRemoval(itemToDeleteId);
            }
        });
    });

    // Inline feedback for coupon response
    function showCouponFeedback(msg, type = 'success') {
        const fb = document.getElementById('cart-coupon-feedback-msg');
        if (!fb) return;
        fb.innerText = msg;
        fb.className = 'small mt-1 fw-bold font-monospace ' + (type === 'success' ? 'text-success' : 'text-danger');
        fb.classList.remove('d-none');
        setTimeout(() => fb.classList.add('d-none'), 5000);
    }

    function openDeleteModal(productId, productTitle) {
        itemToDeleteId = productId;
        document.getElementById('modal-product-name').innerText = `"${productTitle}"`;
        deleteModalObj.show();
    }

    function openCancelOrderModal() {
        cancelOrderModalObj.show();
    }

    function recalculateTotals() {
        const subtotal = parseFloat(document.getElementById('summary-subtotal').innerText.replace(/,/g, '')) || 0;
        const selector = document.getElementById('cart-delivery-selector');
        const deliveryCost = parseFloat(selector.options[selector.selectedIndex].getAttribute('data-cost')) || 70;
        const couponDiscount = parseFloat(document.getElementById('summary-coupon-discount')?.innerText.replace(/,/g, '') || 0);
        const voucherDiscount = parseFloat(document.getElementById('summary-voucher-discount')?.innerText.replace(/,/g, '') || 0);

        document.getElementById('summary-delivery').innerText = deliveryCost.toFixed(2);
        const grandTotal = Math.max(0, (subtotal - (couponDiscount + voucherDiscount)) + deliveryCost);
        document.getElementById('summary-grandtotal').innerText = grandTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function applyQuickPromoCode(code) {
        const input = document.getElementById('cart-coupon-input');
        if (input) {
            input.value = code;
            applyCartCoupon();
        }
    }

    function applyCartCoupon() {
        const input = document.getElementById('cart-coupon-input');
        const code = input ? input.value.trim() : '';
        if (!code) {
            showCouponFeedback('Please enter a coupon code.', 'error');
            return;
        }

        const btn = document.getElementById('btn-apply-coupon');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch("{{ route('cart.coupon.apply') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ coupon_code: code })
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) throw data;
            return data;
        })
        .then(data => {
            showCouponFeedback(data.message || 'Coupon applied successfully!', 'success');

            const couponVal = parseFloat(data.coupon_discount) || 0;
            document.getElementById('applied-coupon-code-label').innerText = data.coupon_code;
            document.getElementById('applied-coupon-discount-label').innerText = couponVal.toFixed(2);
            document.getElementById('summary-coupon-discount').innerText = couponVal.toFixed(2);
            
            document.getElementById('cart-applied-coupon-pill')?.classList.remove('d-none');
            document.getElementById('row-coupon-discount')?.classList.remove('d-none');

            document.getElementById('summary-subtotal').innerText = data.subtotal;
            recalculateTotals();
        })
        .catch(err => {
            showCouponFeedback(err.message || 'Invalid or expired coupon code.', 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = 'Apply';
        });
    }

    function removeCartCoupon() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch("{{ route('cart.coupon.remove') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            showCouponFeedback(data.message || 'Coupon removed.', 'success');
            document.getElementById('cart-coupon-input').value = '';
            document.getElementById('cart-applied-coupon-pill')?.classList.add('d-none');
            document.getElementById('row-coupon-discount')?.classList.add('d-none');
            document.getElementById('summary-coupon-discount').innerText = '0.00';
            
            recalculateTotals();
        })
        .catch(() => {
            showCouponFeedback('Failed to remove coupon.', 'error');
        });
    }

    function applySelectedVoucher(code) {
        if (!code) return;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch("{{ route('cart.voucher.apply') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ voucher_code: code })
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) throw data;
            return data;
        })
        .then(data => {
            showCouponFeedback(data.message || 'Voucher applied successfully!', 'success');

            const voucherVal = parseFloat(data.voucher_discount) || 0;
            document.getElementById('applied-voucher-code-label').innerText = data.voucher_code;
            document.getElementById('applied-voucher-discount-label').innerText = voucherVal.toFixed(2);
            document.getElementById('summary-voucher-discount').innerText = voucherVal.toFixed(2);

            document.getElementById('cart-applied-voucher-pill')?.classList.remove('d-none');
            document.getElementById('row-voucher-discount')?.classList.remove('d-none');

            document.getElementById('summary-subtotal').innerText = data.subtotal;
            recalculateTotals();
        })
        .catch(err => {
            showCouponFeedback(err.message || 'Could not apply voucher.', 'error');
            const sel = document.getElementById('cart-voucher-select');
            if (sel) sel.value = '';
        });
    }

    function removeCartVoucher() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch("{{ route('cart.voucher.remove') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            showCouponFeedback(data.message || 'Voucher removed.', 'success');
            const sel = document.getElementById('cart-voucher-select');
            if (sel) sel.value = '';
            document.getElementById('cart-applied-voucher-pill')?.classList.add('d-none');
            document.getElementById('row-voucher-discount')?.classList.add('d-none');
            document.getElementById('summary-voucher-discount').innerText = '0.00';

            recalculateTotals();
        })
        .catch(() => {
            showCouponFeedback('Failed to remove voucher.', 'error');
        });
    }

    function updateQuantityAJAX(productId, change) {
        const qtySpan = document.getElementById(`qty-val-${productId}`) || document.getElementById(`qty-val-mob-${productId}`);
        let currentQty = parseInt(qtySpan ? qtySpan.innerText : 1) || 1;
        let newQty = currentQty + change;

        if (newQty <= 0) {
            openDeleteModal(productId, 'this product');
            return;
        }

        const deliveryType = document.getElementById('cart-delivery-selector').value;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch("{{ route('cart.updateQty') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ id: productId, quantity: newQty, delivery_type: deliveryType })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                if (document.getElementById(`qty-val-${productId}`)) document.getElementById(`qty-val-${productId}`).innerText = newQty;
                if (document.getElementById(`qty-val-mob-${productId}`)) document.getElementById(`qty-val-mob-${productId}`).innerText = newQty;
                if (document.getElementById(`item-total-${productId}`)) document.getElementById(`item-total-${productId}`).innerText = data.item_total;
                if (document.getElementById(`item-total-mob-${productId}`)) document.getElementById(`item-total-mob-${productId}`).innerText = data.item_total;
                
                document.getElementById('summary-subtotal').innerText = data.cart_subtotal;
                
                if (data.coupon_discount !== undefined) {
                    const cDisc = parseFloat(data.coupon_discount) || 0;
                    document.getElementById('summary-coupon-discount').innerText = cDisc.toFixed(2);
                    if (cDisc > 0) {
                        document.getElementById('row-coupon-discount')?.classList.remove('d-none');
                    } else {
                        document.getElementById('row-coupon-discount')?.classList.add('d-none');
                        document.getElementById('cart-applied-coupon-pill')?.classList.add('d-none');
                    }
                }

                if (data.voucher_discount !== undefined) {
                    const vDisc = parseFloat(data.voucher_discount) || 0;
                    document.getElementById('summary-voucher-discount').innerText = vDisc.toFixed(2);
                    if (vDisc > 0) {
                        document.getElementById('row-voucher-discount')?.classList.remove('d-none');
                    } else {
                        document.getElementById('row-voucher-discount')?.classList.add('d-none');
                        document.getElementById('cart-applied-voucher-pill')?.classList.add('d-none');
                    }
                }

                recalculateTotals();

                if (window.updateAllCartBadges) {
                    window.updateAllCartBadges(data.cart_count);
                }
            }
        })
        .catch(err => console.error('Qty update error:', err));
    }

    function performItemRemoval(productId) {
        const confirmBtn = document.getElementById('confirm-delete-action-btn');
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = 'Deleting...';

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch("{{ route('cart.remove') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ id: productId })
        })
        .then(res => res.json())
        .then(data => {
            confirmBtn.disabled = false;
            confirmBtn.innerText = 'Delete';
            deleteModalObj.hide();

            if (data.status === 'success') {
                const row = document.getElementById(`cart-item-row-${productId}`);
                if (row) {
                    row.style.transition = 'all 0.25s ease';
                    row.style.opacity = '0';
                    row.style.transform = 'scale(0.95)';
                    setTimeout(() => row.remove(), 250);
                }

                document.getElementById('summary-subtotal').innerText = data.cart_subtotal;
                
                if (data.coupon_discount !== undefined) {
                    const cDisc = parseFloat(data.coupon_discount) || 0;
                    document.getElementById('summary-coupon-discount').innerText = cDisc.toFixed(2);
                    if (cDisc > 0) {
                        document.getElementById('row-coupon-discount')?.classList.remove('d-none');
                    } else {
                        document.getElementById('row-coupon-discount')?.classList.add('d-none');
                        document.getElementById('cart-applied-coupon-pill')?.classList.add('d-none');
                    }
                }

                if (data.voucher_discount !== undefined) {
                    const vDisc = parseFloat(data.voucher_discount) || 0;
                    document.getElementById('summary-voucher-discount').innerText = vDisc.toFixed(2);
                    if (vDisc > 0) {
                        document.getElementById('row-voucher-discount')?.classList.remove('d-none');
                    } else {
                        document.getElementById('row-voucher-discount')?.classList.add('d-none');
                        document.getElementById('cart-applied-voucher-pill')?.classList.add('d-none');
                    }
                }

                recalculateTotals();

                if (window.updateAllCartBadges) {
                    window.updateAllCartBadges(data.cart_count);
                }

                if (data.is_empty) {
                    document.getElementById('cart-main-content').classList.add('d-none');
                    document.getElementById('cart-empty-state').classList.remove('d-none');
                }
            }
        })
        .catch(err => {
            console.error('Remove error:', err);
            confirmBtn.disabled = false;
            confirmBtn.innerText = 'Delete';
        });
    }

    function executeClearCart() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch("{{ route('cart.clear') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(() => {
            cancelOrderModalObj.hide();
            if (window.updateAllCartBadges) {
                window.updateAllCartBadges(0);
            }
            document.getElementById('cart-main-content').classList.add('d-none');
            document.getElementById('cart-empty-state').classList.remove('d-none');
        });
    }
</script>
@endsection