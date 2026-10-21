@extends('layouts.app')

@php
    /** @var \App\Models\Product $product */
    $finalPrice = (float) ($product->sale_price ?? $product->regular_price ?? 0);
    $regularPrice = (float) ($product->regular_price ?? 0);
    $hasDiscount = !empty($product->sale_price) && ($finalPrice < $regularPrice);
    $discountPercentage = ($hasDiscount && $regularPrice > 0) ? (int) round((($regularPrice - $finalPrice) / $regularPrice) * 100) : 0;

    // ইউনিভার্সাল ইমেজ রেজলভার (Connected to Central ImagePathService)
    $resolveProductImg = function($path, $fallback = 'https://placehold.co/600x600?text=No+Image') {
        if (empty($path)) {
            return $fallback;
        }
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path, $fallback ?? '');
        }
        if (function_exists('show_image')) {
            return show_image($path, $fallback);
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        return asset(ltrim($path, '/'));
    };

    $mainImg = $resolveProductImg($product->thumbnail);

    // Setting check
    $stockOutSetting = DB::table('settings')->where('key', 'card_show_stockout_btn')->value('value');
    $showStockOutSetting = ($stockOutSetting === '1' || $stockOutSetting == 1);

    // Exact stock check
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

    // Database & Session Wishlist Status
    $wishlist = session()->get('wishlist', []);
    $isWishlisted = in_array($product->id, $wishlist);
    if (!$isWishlisted && auth()->check() && \Illuminate\Support\Facades\Schema::hasTable('wishlists')) {
        $isWishlisted = DB::table('wishlists')->where('user_id', auth()->id())->where('product_id', $product->id)->exists();
    }

    $showReviewsSetting = DB::table('settings')->where('key', 'detail_show_reviews')->value('value') ?? '1';
    $allowReviewSetting = DB::table('settings')->where('key', 'detail_allow_review')->value('value') ?? '1';

    $reviews = DB::table('product_reviews')
        ->join('users', 'product_reviews.user_id', '=', 'users.id')
        ->where('product_reviews.product_id', $product->id)
        ->where('product_reviews.status', 'approved')
        ->select('product_reviews.*', 'users.name as user_name')
        ->latest()
        ->get();

    $totalReviewCount = $reviews->count();
    $avgScore = $totalReviewCount > 0 ? (float) $reviews->avg('rating') : 5.0;

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

    $showSocial = DB::table('settings')->where('key', 'footer_show_social')->value('value') ?? '1';
    $showSocial = ($showSocial == '1');
    $socialLinksJson = DB::table('settings')->where('key', 'footer_social_links_json')->value('value');
    $socialLinks = !empty($socialLinksJson) ? json_decode($socialLinksJson, true) : [
        ['icon' => 'fab fa-facebook-f', 'url' => '#'],
        ['icon' => 'fab fa-youtube', 'url' => '#'],
        ['icon' => 'fab fa-instagram', 'url' => '#']
    ];

    $storeLogo = DB::table('settings')->where('key', 'header_logo')->value('value') 
              ?? DB::table('settings')->where('key', 'site_icon')->value('value');
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

    // Load complete variant data with Universal Image Resolution
    $variants = ($product->variants && $product->variants->count() > 0)
        ? $product->variants
        : DB::table('product_variants')->where('product_id', $product->id)->get();

    $uniqueColors = $variants->pluck('color')->map(fn($c) => trim((string)$c))->filter()->unique()->values();
    $uniqueSizes  = $variants->pluck('size')->map(fn($s) => trim((string)$s))->filter()->unique()->values();

    // Map each color to its first available variant image for swatch preview & fallback
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

    // Thumbnails Gallery (Product Gallery + Variant Images)
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
@endphp

@section('title', ($product->title ?? $product->name) . ' - ' . config('app.name', 'Shop Now BD'))

@section('content')
    <style>
        .product-details-container {
            max-width: 1260px;
            margin: 0 auto;
        }
        .product-main-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #edf2f7;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.02);
            padding: 16px 20px;
        }
        @media (max-width: 575.98px) {
            .product-main-card {
                padding: 12px 14px;
            }
        }

        .zoom-container {
            position: relative;
            overflow: hidden;
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #f1f5f9;
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
            border: 2px solid #2563eb;
            width: 110px;
            height: 110px;
            background-repeat: no-repeat;
            display: none;
            pointer-events: none;
            border-radius: 50%;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);
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
            height: 50px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #475569;
            font-size: 11px;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }
        .thumb-nav-arrow:hover {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
        }
        .gallery-thumbs-scroll {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            scroll-behavior: smooth;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f8fafc;
            padding-bottom: 4px;
            flex-grow: 1;
        }
        .gallery-thumbs-scroll::-webkit-scrollbar {
            height: 4px;
        }
        .gallery-thumbs-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .gallery-thumb-item {
            width: 52px;
            height: 52px;
            border-radius: 8px;
            border: 1.5px solid #e2e8f0;
            padding: 2px;
            object-fit: contain;
            background: #ffffff;
            cursor: pointer;
            transition: all 0.15s ease;
            flex-shrink: 0;
        }
        .gallery-thumb-item:hover, .gallery-thumb-item.active-thumb {
            border-color: #2563eb !important;
            transform: translateY(-1px);
        }

        .badge-discount-tag {
            position: absolute;
            top: 8px;
            left: 8px;
            background: #ef4444;
            color: #ffffff;
            font-size: 10.5px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 4px;
            z-index: 3;
            letter-spacing: 0.3px;
        }

        .product-breadcrumb {
            font-size: 11.5px;
            font-weight: 500;
        }
        .product-breadcrumb a {
            color: #64748b;
            text-decoration: none;
        }
        .product-breadcrumb a:hover {
            color: #2563eb;
        }
        .product-title-text {
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.3;
            letter-spacing: -0.01em;
        }
        @media (min-width: 768px) {
            .product-title-text {
                font-size: 1.45rem;
            }
        }

        .product-rating-action-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 8px;
            min-height: 36px;
        }
        .rating-stars {
            color: #f59e0b;
            font-size: 11.5px;
            display: inline-flex;
            align-items: center;
            gap: 2px;
        }
        .product-header-actions {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-left: auto;
        }
        .header-icon-btn {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .header-icon-btn:hover {
            border-color: #cbd5e1;
            color: #2563eb;
        }
        .header-icon-btn.is-active {
            background: #fee2e2 !important;
            color: #ef4444 !important;
            border-color: #fca5a5 !important;
        }

        .price-banner-box {
            background: #f8fafc;
            border: 1px solid #edf2f7;
            border-radius: 10px;
            padding: 8px 12px;
        }
        .current-active-price {
            font-size: 1.45rem;
            font-weight: 800;
            color: #dc2626;
            letter-spacing: -0.02em;
            line-height: 1;
        }
        .old-strike-price {
            font-size: 0.9rem;
            color: #94a3b8;
            text-decoration: line-through;
            font-weight: 500;
        }
        .saving-chip {
            background: #fee2e2;
            color: #dc2626;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .product-short-desc-card {
            background: #fdfdfd;
            border-left: 3px solid #2563eb;
            border-radius: 0 8px 8px 0;
            padding: 8px 12px;
            font-size: 12.5px;
            line-height: 1.55;
            color: #475569;
        }
        .product-short-desc-card ul, .product-short-desc-card ol {
            padding-left: 1.1rem;
            margin-bottom: 0;
        }
        .product-short-desc-card li {
            margin-bottom: 2px;
        }

        .swatch-group-wrap {
            margin-top: 10px;
            margin-bottom: 12px;
        }
        .swatch-group-label {
            font-size: 11.5px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 7px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .swatches-flex-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .swatch-chip {
            cursor: pointer;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            padding: 5px 12px;
            font-size: 12px;
            font-weight: 600;
            color: #1e293b;
            background: #ffffff;
            transition: all 0.15s ease;
            user-select: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .swatch-chip:hover {
            border-color: #94a3b8;
            transform: translateY(-1px);
        }
        .swatch-chip.active {
            border-color: #2563eb !important;
            background: #eff6ff !important;
            color: #2563eb !important;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.12);
        }
        .swatch-mini-thumb {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            object-fit: cover;
            border: 1px solid #e2e8f0;
        }

        .product-qty-section {
            margin-top: 14px;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .qty-stepper-wrap {
            display: inline-flex;
            align-items: center;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
            overflow: hidden;
            height: 34px;
        }
        .qty-btn {
            border: none;
            background: #f8fafc;
            color: #334155;
            width: 32px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
        }
        .qty-btn:hover {
            background: #e2e8f0;
        }
        .qty-input-field {
            width: 40px;
            height: 34px;
            border: none;
            text-align: center;
            font-weight: 700;
            font-size: 13px;
            color: #0f172a;
            background: #ffffff;
        }
        .qty-input-field:focus {
            outline: none;
        }

        .action-btns-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            width: 100%;
            margin-bottom: 8px;
        }

        .btn-buy-now-vibrant {
            background-color: var(--details-btn-order-bg, #0dcaf0) !important;
            border: none !important;
            color: var(--details-btn-order-text, #ffffff) !important;
            font-weight: 700 !important;
            font-size: 13px !important;
            height: 38px !important;
            padding: 0 10px !important;
            border-radius: 8px !important;
            transition: all 0.2s ease !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            width: 100%;
        }
        .btn-buy-now-vibrant:hover {
            filter: brightness(0.92);
            transform: translateY(-1px);
        }
        .btn-add-cart-vibrant {
            background-color: var(--details-btn-cart-bg, #ffc107) !important;
            border: none !important;
            color: var(--details-btn-cart-text, #212529) !important;
            font-weight: 700 !important;
            font-size: 13px !important;
            height: 38px !important;
            padding: 0 10px !important;
            border-radius: 8px !important;
            transition: all 0.2s ease !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            width: 100%;
        }
        .btn-add-cart-vibrant:hover {
            filter: brightness(0.92);
            transform: translateY(-1px);
        }
        .btn-whatsapp-vibrant {
            background: #ffffff;
            color: #16a34a;
            border: 1.5px solid #22c55e;
            font-weight: 600;
            font-size: 12.5px;
            height: 36px;
            padding: 0 12px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
            transition: all 0.15s ease;
            width: 100%;
        }
        .btn-whatsapp-vibrant:hover {
            background: #22c55e;
            color: #ffffff;
        }
        .btn-stockout-vibrant {
            background-color: #fee2e2 !important;
            color: #dc2626 !important;
            border: 1px dashed #fca5a5 !important;
            font-weight: 700 !important;
            font-size: 13px !important;
            height: 38px;
            width: 100%;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            cursor: not-allowed !important;
        }

        .store-sidebar-card {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #edf2f7;
            padding: 16px;
        }
        .sidebar-brand-box {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #f1f5f9;
            margin-bottom: 12px;
        }
        .sidebar-brand-meta {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }
        .sidebar-verified-badge {
            display: inline-flex;
            align-items: center;
            color: #16a34a;
            font-weight: 600;
            font-size: 11px;
            line-height: 1.2;
            margin-top: 1px;
        }
        .sidebar-feature-row {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            font-size: 12px;
            color: #475569;
            margin-bottom: 8px;
        }
        .sidebar-feature-icon {
            width: 24px;
            height: 24px;
            border-radius: 6px;
            background: #eff6ff;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 11px;
        }
        .sidebar-social-circles {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
        .sidebar-social-circle {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            border: 1px solid #cbd5e1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            background: #ffffff;
            text-decoration: none;
            font-size: 11px;
        }
        .sidebar-social-circle:hover {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
        }

        .product-nav-tabs {
            border-bottom: 1px solid #e2e8f0;
            padding: 0 4px;
        }
        .product-nav-tabs .nav-link {
            border: none;
            color: #64748b;
            font-weight: 600;
            font-size: 13px;
            padding: 10px 18px;
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
        }
        .product-nav-tabs .nav-link.active {
            color: #2563eb;
            border-bottom-color: #2563eb;
            background: transparent;
        }
        .tab-content-area {
            padding: 16px 8px 8px 8px;
        }
        .product-desc-content {
            font-size: 0.92rem;
            line-height: 1.7;
            color: #334155;
        }

        .mini-slider-container {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            scroll-behavior: smooth;
            scrollbar-width: none;
            -ms-overflow-style: none;
            padding-bottom: 6px;
        }
        .mini-slider-container::-webkit-scrollbar { display: none; }
        .mini-slider-item {
            flex: 0 0 calc((100% - 30px) / 4);
            min-width: 140px;
        }
        @media (max-width: 991.98px) {
            .mini-slider-item { flex: 0 0 calc((100% - 20px) / 3); min-width: 130px; }
        }
        @media (max-width: 575.98px) {
            .mini-slider-item { flex: 0 0 calc((100% - 10px) / 2); min-width: 125px; }
            .btn-buy-now-vibrant, .btn-add-cart-vibrant {
                height: 36px !important;
                font-size: 12px !important;
            }
        }
        .mini-product-card {
            border-radius: 10px;
            background: #ffffff;
            border: 1px solid #edf2f7;
            transition: all 0.2s ease;
        }
        .mini-product-card:hover {
            transform: translateY(-2px);
            border-color: #cbd5e1;
        }
        .mini-product-img-box {
            position: relative;
            aspect-ratio: 1/1;
            background: #f8fafc;
            border-radius: 9px 9px 0 0;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .mini-product-img-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 6px;
        }

        .cart-response-alert {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 10px;
            margin-bottom: 12px;
            font-size: 12.5px;
        }
    </style>

    <div id="single-product-page-wrapper" class="bg-light py-2 py-md-3">
        <div id="single-product-container" class="container product-details-container px-2 px-md-3">

            @if(session('success'))
                <div class="cart-response-alert alert alert-success alert-dismissible fade show bg-success-subtle text-success-emphasis border-success-subtle py-2" role="alert" id="productDetailSuccessAlert">
                    <div class="d-flex align-items-center gap-2 fw-semibold small">
                        <i class="fas fa-check-circle"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="cart-response-alert alert alert-danger alert-dismissible fade show bg-danger-subtle text-danger-emphasis border-danger-subtle py-2" role="alert">
                    <div class="d-flex align-items-center gap-2 fw-semibold small">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                        <li class="breadcrumb-item active text-truncate text-dark fw-semibold" style="max-width: 220px;" aria-current="page">
                            {{ $product->title ?? $product->name }}
                        </li>
                    </ol>
                </nav>
            @endif

            <!-- Main Primary Product Card -->
            <div id="product-primary-card" class="product-main-card mb-3 bg-white">
                <div id="product-primary-grid" class="row g-3 g-md-4 align-items-start">
                    
                    <!-- 1. Image Gallery Column with Slider Controls -->
                    <div id="product-gallery-column" class="col-12 col-lg-4 col-md-5">
                        <div id="product-main-preview-box" class="zoom-container {{ $zoomMode === 'inner' ? 'zoom-mode-inner' : '' }} mb-2">
                            @if($hasDiscount)
                                <span class="badge-discount-tag" id="badge-discount-percent">
                                    -{{ $discountPercentage }}%
                                </span>
                            @endif
                            <img id="product-main-display-image" src="{{ $mainImg }}" alt="{{ $product->title ?? $product->name }}" onerror="this.src='https://placehold.co/600x600?text=No+Image'">
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
                                <img id="thumb-main-img" src="{{ $mainImg }}" class="gallery-thumb-item active-thumb" onclick="switchProductMainImage('{{ $mainImg }}', this)" alt="Thumb" onerror="this.style.display='none'">
                                @if(!empty($allThumbnails) && $allThumbnails->count() > 0)
                                    @foreach($allThumbnails as $galImg)
                                        <img src="{{ $galImg }}" class="gallery-thumb-item" onclick="switchProductMainImage('{{ $galImg }}', this)" alt="Gallery" onerror="this.style.display='none'">
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
                        <h1 id="product-title-heading" class="product-title-text mb-1.5">
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
                                    <span class="text-dark fw-bold ms-1 small" style="font-size: 11px;">{{ number_format($avgScore, 1) }}</span>
                                    <span class="text-muted" style="font-size: 11px;">({{ $totalReviewCount }})</span>
                                </div>
                            @endif

                            <div class="product-header-actions">
                                @if($uiSettings['show_wishlist'] ?? true)
                                    <button type="button" 
                                            class="header-icon-btn btn-wishlist-toggle {{ $isWishlisted ? 'is-active' : '' }}" 
                                            data-id="{{ $product->id }}" 
                                            title="{{ $isWishlisted ? 'Remove from Wishlist' : 'Add to Wishlist' }}" 
                                            style="{{ $isWishlisted ? 'background: #fee2e2; color: #ef4444; border-color: #fca5a5;' : '' }}">
                                        <i class="{{ $isWishlisted ? 'fas fa-heart text-danger' : 'far fa-heart' }}"></i>
                                    </button>
                                @endif

                                @if($uiSettings['show_social_share'] ?? true)
                                    <div class="dropdown">
                                        <button class="header-icon-btn" type="button" data-bs-toggle="dropdown" title="Share">
                                            <i class="fas fa-share-alt"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 small">
                                            <li><a class="dropdown-item py-1.5" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}" target="_blank"><i class="fab fa-facebook text-primary me-2"></i> Facebook</a></li>
                                            <li><a class="dropdown-item py-1.5" href="https://api.whatsapp.com/send?text={{ urlencode(request()->fullUrl()) }}" target="_blank"><i class="fab fa-whatsapp text-success me-2"></i> WhatsApp</a></li>
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Brand, SKU & Stock Badges -->
                        <div class="d-flex flex-wrap align-items-center gap-2 small text-muted mb-2">
                            @if(($uiSettings['show_brand'] ?? true) && !empty($product->brand))
                                <span class="badge bg-light text-dark border fw-semibold px-2 py-1" style="font-size: 10px;">
                                    Brand: <strong>{{ $product->brand->name }}</strong>
                                </span>
                            @endif
                            @if($uiSettings['show_sku'] ?? true)
                                <span class="badge bg-light text-dark border fw-semibold px-2 py-1 font-monospace" style="font-size: 10px;">
                                    SKU: <strong id="display-sku" class="text-primary">{{ $product->sku ?? ($product->product_code ?? 'PRD-' . $product->id) }}</strong>
                                </span>
                            @endif
                            <div id="display-stock-status">
                                @if($isActuallyOut)
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fw-bold" style="font-size: 10px;">Out of Stock</span>
                                @elseif($actualStock > 0)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fw-bold" style="font-size: 10px;"><i class="fas fa-check-circle me-1"></i>In Stock ({{ $actualStock }})</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 fw-bold" style="font-size: 10px;">Pre-Order</span>
                                @endif
                            </div>
                        </div>

                        <!-- Price Banner -->
                        <div class="price-banner-box mb-2.5">
                            <div class="d-flex align-items-baseline flex-wrap gap-2 mb-0.5">
                                <span id="product-active-price" class="current-active-price" data-unit-price="{{ $finalPrice }}">
                                    ৳{{ number_format($finalPrice, 2) }}
                                </span>
                                <span class="old-strike-price {{ $hasDiscount ? '' : 'd-none' }}" id="product-active-old-price">
                                    ৳{{ number_format($regularPrice, 2) }}
                                </span>
                                <span class="saving-chip {{ $hasDiscount ? '' : 'd-none' }}" id="product-discount-tag">
                                    {{ $discountPercentage }}% SAVINGS
                                </span>
                            </div>
                            <div class="text-secondary fw-semibold" style="font-size: 11px;">
                                Estimated Total: <strong id="calculated-total-display" class="text-dark fw-bold">৳{{ number_format($finalPrice, 2) }}</strong>
                            </div>
                        </div>

                        <!-- Short Description Box -->
                        @if(!empty($sanitizedShortDesc))
                            <div class="product-short-desc-card mb-2.5">
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

                                <!-- Color Swatches with Optional Mini Variant Image Thumbnail -->
                                @if($uniqueColors->count() > 0)
                                    <div class="swatch-group-wrap">
                                        <div class="swatch-group-label">
                                            <span>Color: <span id="selected-color-name" class="text-primary font-monospace fw-bold"></span></span>
                                        </div>
                                        <div class="swatches-flex-container">
                                            @foreach($uniqueColors as $color)
                                                @php
                                                    $cLookup = strtolower(trim($color));
                                                    $swatchImg = $colorImageMap[$cLookup] ?? null;
                                                @endphp
                                                <div class="swatch-chip swatch-color-btn" data-color="{{ $color }}" data-image="{{ $swatchImg ?? '' }}" onclick="selectColorSwatch('{{ addslashes($color) }}', this)">
                                                    @if(!empty($swatchImg))
                                                        <img src="{{ $swatchImg }}" alt="{{ $color }}" class="swatch-mini-thumb" onerror="this.style.display='none'">
                                                    @else
                                                        <i class="fas fa-circle text-primary opacity-75" style="font-size: 9px;"></i>
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
                                            <span>Size / Spec: <span id="selected-size-name" class="text-primary font-monospace fw-bold"></span></span>
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
                                    <label class="fw-bold text-dark mb-0" style="font-size: 11.5px;">Qty:</label>
                                    <div class="qty-stepper-wrap">
                                        <button class="qty-btn" type="button" onclick="changeQuantity(-1)">-</button>
                                        <input id="product-qty-input" type="number" name="quantity" class="qty-input-field" value="1" min="1" max="99" onchange="updateSubtotalPrice()">
                                        <button class="qty-btn" type="button" onclick="changeQuantity(1)">+</button>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="action-btns-grid">
                                    <button type="submit" name="action" value="order_now" class="btn btn-buy-now-vibrant shadow-xs">
                                        <i class="fas fa-bolt text-warning" style="font-size: 11px;"></i> Buy Now
                                    </button>
                                    <button type="submit" name="action" value="add_to_cart" class="btn btn-add-cart-vibrant shadow-xs">
                                        <i class="fas fa-shopping-cart" style="font-size: 11px;"></i> Add Cart
                                    </button>
                                </div>

                                @if($uiSettings['show_whatsapp'] ?? true)
                                    <div class="mb-1">
                                        <a href="https://api.whatsapp.com/send?phone=8801774937404&text={{ urlencode('Hello, I want to order: ' . ($product->title ?? $product->name) . ' - ' . request()->fullUrl()) }}" target="_blank" class="btn-whatsapp-vibrant">
                                            <i class="fab fa-whatsapp" style="font-size: 14px;"></i> Order via WhatsApp
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
                                    <div class="bg-light border rounded-2 p-1 d-flex align-items-center justify-content-center shadow-xs flex-shrink-0" style="width: 38px; height: 38px; overflow: hidden;">
                                        @if($storeLogoUrl)
                                            <img src="{{ $storeLogoUrl }}" alt="Logo" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                        @else
                                            <i class="fas fa-store text-primary" style="font-size: 15px;"></i>
                                        @endif
                                    </div>
                                    <div class="min-w-0 sidebar-brand-meta">
                                        <h6 class="fw-bold mb-0 text-dark text-truncate" style="font-size: 13px; line-height: 1.2;">{{ config('app.name', 'ECOM SIT') }}</h6>
                                        @if($uiSettings['store_verified_badge'] ?? true)
                                            <span class="sidebar-verified-badge"><i class="fas fa-check-circle me-1 text-success"></i>Verified Store</span>
                                        @endif
                                    </div>
                                </div>

                                @if(!empty($videoEmbedUrl))
                                    <div class="mb-2.5 border-bottom pb-2">
                                        <h6 class="fw-bold text-secondary text-uppercase mb-1.5" style="font-size: 10.5px;">
                                            <i class="fab fa-youtube text-danger me-1"></i> Product Video
                                        </h6>
                                        <div class="ratio ratio-16x9 rounded overflow-hidden shadow-xs">
                                            <iframe src="{{ $videoEmbedUrl }}" title="Video" allowfullscreen></iframe>
                                        </div>
                                    </div>
                                @endif

                                @if($uiSettings['show_delivery_options'] ?? true)
                                    <div class="mb-2.5 border-bottom pb-2">
                                        <h6 class="fw-bold text-secondary text-uppercase mb-1.5" style="font-size: 10.5px; letter-spacing: 0.3px;">Delivery Options</h6>
                                        <select id="delivery-city-selector" class="form-select form-select-sm rounded-2 mb-2" style="font-size: 11.5px; padding: 5px 8px;" onchange="updateDeliveryRate()">
                                            <option value="dhaka" data-cost="{{ $uiSettings['delivery_inside_dhaka'] ?? 70 }}" data-time="{{ $uiSettings['delivery_dhaka_days'] ?? '2-3 days' }}" selected>Inside Dhaka</option>
                                            <option value="outside" data-cost="{{ $uiSettings['delivery_outside_dhaka'] ?? 130 }}" data-time="{{ $uiSettings['delivery_outside_days'] ?? '3-5 days' }}">Outside Dhaka</option>
                                        </select>
                                        
                                        <div class="sidebar-feature-row">
                                            <div class="sidebar-feature-icon"><i class="fas fa-truck"></i></div>
                                            <div class="w-100">
                                                <div class="d-flex justify-content-between fw-semibold" style="font-size: 11.5px;">
                                                    <span>Standard Delivery</span>
                                                    <strong id="delivery-cost-amount" class="text-primary">৳{{ number_format($uiSettings['delivery_inside_dhaka'] ?? 70, 2) }}</strong>
                                                </div>
                                                <span id="delivery-timeline" class="text-muted" style="font-size: 10.5px;">
                                                    Guaranteed by {{ $uiSettings['delivery_dhaka_days'] ?? '2-3 days' }}
                                                </span>
                                            </div>
                                        </div>

                                        @if($uiSettings['show_cod_option'] ?? true)
                                            <div class="sidebar-feature-row mb-0">
                                                <div class="sidebar-feature-icon"><i class="fas fa-hand-holding-usd"></i></div>
                                                <div class="fw-semibold" style="font-size: 11.5px;">
                                                    <span>Cash on Delivery</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                @if($uiSettings['show_return_warranty'] ?? true)
                                    <div class="mb-2 border-bottom pb-2">
                                        <h6 class="fw-bold text-secondary text-uppercase mb-1.5" style="font-size: 10.5px; letter-spacing: 0.3px;">Return & Warranty</h6>
                                        <div class="sidebar-feature-row">
                                            <div class="sidebar-feature-icon"><i class="fas fa-undo-alt"></i></div>
                                            <div style="font-size: 11.5px;">7 Days Easy Return Policy</div>
                                        </div>
                                        <div class="sidebar-feature-row mb-0">
                                            <div class="sidebar-feature-icon"><i class="fas fa-shield-alt"></i></div>
                                            <div style="font-size: 11.5px;">100% Genuine Guaranteed</div>
                                        </div>
                                    </div>
                                @endif

                                <div class="mb-2">
                                    <h6 class="fw-bold text-secondary text-uppercase mb-1.5" style="font-size: 10.5px; letter-spacing: 0.3px;">Accepted Payment</h6>
                                    <div class="d-flex flex-wrap gap-1">
                                        @forelse($activePaymentList as $methodName)
                                            <span class="badge bg-light text-dark border px-1.5 py-0.5" style="font-size: 9.5px;">
                                                {{ $methodName }}
                                            </span>
                                        @empty
                                            <span class="badge bg-light text-dark border px-1.5 py-0.5" style="font-size: 9.5px;">COD</span>
                                        @endforelse
                                    </div>
                                </div>

                                @if($showSocial && count($socialLinks) > 0)
                                    <div class="border-top pt-2">
                                        <small class="text-muted d-block mb-1 fw-semibold" style="font-size: 10.5px;">Follow Store:</small>
                                        <div class="sidebar-social-circles">
                                            @foreach($socialLinks as $soc)
                                                @php
                                                    $linkUrl = trim($soc['url'] ?? '#');
                                                    if ($linkUrl !== '#' && !preg_match("~^(?:f|ht)tps?://~i", $linkUrl)) {
                                                        $linkUrl = 'https://' . $linkUrl;
                                                    }
                                                @endphp
                                                <a href="{{ $linkUrl }}" class="sidebar-social-circle" target="_blank" rel="noopener noreferrer">
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
            <div class="product-main-card mb-3 bg-white">
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
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-pane-reviews" type="button" role="tab">
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
                            <div class="table-responsive rounded-2 border overflow-hidden p-1">
                                <table class="table table-bordered table-striped align-middle small mb-0" style="font-size: 12px;">
                                    <tbody>
                                        @if(!empty($product->weight) && $product->weight > 0)
                                            <tr>
                                                <th class="bg-light text-dark fw-bold" style="width: 35%;">Weight</th>
                                                <td>{{ $product->weight }} kg</td>
                                            </tr>
                                        @endif
                                        @if(!empty($product->height) && $product->height > 0)
                                            <tr>
                                                <th class="bg-light text-dark fw-bold">Height</th>
                                                <td>{{ $product->height }} cm</td>
                                            </tr>
                                        @endif
                                        @if(!empty($product->width) && $product->width > 0)
                                            <tr>
                                                <th class="bg-light text-dark fw-bold">Width</th>
                                                <td>{{ $product->width }} cm</td>
                                            </tr>
                                        @endif
                                        @if(!empty($product->length) && $product->length > 0)
                                            <tr>
                                                <th class="bg-light text-dark fw-bold">Length</th>
                                                <td>{{ $product->length }} cm</td>
                                            </tr>
                                        @endif
                                        @if(!empty($product->sku))
                                            <tr>
                                                <th class="bg-light text-dark fw-bold">SKU</th>
                                                <td class="font-monospace text-primary">{{ $product->sku }}</td>
                                            </tr>
                                        @endif
                                        @if(!empty($product->product_code))
                                            <tr>
                                                <th class="bg-light text-dark fw-bold">Product Code</th>
                                                <td class="font-monospace">{{ $product->product_code }}</td>
                                            </tr>
                                        @endif
                                        @if(!empty($product->barcode))
                                            <tr>
                                                <th class="bg-light text-dark fw-bold">Barcode</th>
                                                <td class="font-monospace">{{ $product->barcode }}</td>
                                            </tr>
                                        @endif
                                        @if(isset($product->stock))
                                            <tr>
                                                <th class="bg-light text-dark fw-bold">Stock Quantity</th>
                                                <td>
                                                    @if($product->stock > 0)
                                                        <strong class="text-success">{{ $product->stock }} units</strong>
                                                    @else
                                                        <strong class="text-warning">Pre-Order</strong>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endif
                                        @if(!empty($product->product_type))
                                            <tr>
                                                <th class="bg-light text-dark fw-bold">Product Type</th>
                                                <td class="text-capitalize">{{ $product->product_type }}</td>
                                            </tr>
                                        @endif
                                        @if(!empty($product->product_struct))
                                            <tr>
                                                <th class="bg-light text-dark fw-bold">Structure</th>
                                                <td class="text-capitalize">{{ $product->product_struct }}</td>
                                            </tr>
                                        @endif
                                        @if(!empty($product->variants) && $product->variants->count() > 0)
                                            <tr>
                                                <th class="bg-light text-dark fw-bold">Available Variants</th>
                                                <td>
                                                    @foreach($product->variants as $v)
                                                        <span class="badge bg-light text-dark border me-1 mb-1" style="font-size: 10px;">{{ $v->color ? $v->color . ' - ' : '' }}{{ $v->size ?? $v->title }}</span>
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
                            <div class="p-3 bg-light rounded-3 mb-2.5 border">
                                @auth
                                    @if($isBuyer)
                                        <h6 class="fw-bold text-dark mb-2" style="font-size: 13px;">{{ $myReview ? 'Update Your Review' : 'Write Review' }}</h6>
                                        <form action="{{ $myReview ? route('user.product.review.update', $myReview->id) : route('user.product.review.store', $product->id) }}" method="POST">
                                            @csrf
                                            @if($myReview) @method('PUT') @endif
                                            <div class="row g-2 align-items-center mb-2">
                                                <div class="col-auto"><label class="small fw-bold text-muted" style="font-size: 11.5px;">Rating:</label></div>
                                                <div class="col-auto">
                                                    <select name="rating" class="form-select form-select-sm rounded-2" style="width: 100px; font-size: 11px;">
                                                        @for($s = 5; $s >= 1; $s--)
                                                            <option value="{{ $s }}" {{ ($myReview->rating ?? 5) == $s ? 'selected' : '' }}>{{ $s }} Stars</option>
                                                        @endfor
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="mb-2">
                                                <textarea name="comment" rows="2" class="form-control form-control-sm rounded-2" style="font-size: 12px;" placeholder="Share your experience..." required>{{ $myReview->comment ?? '' }}</textarea>
                                            </div>
                                            <div class="d-flex gap-1.5">
                                                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold" style="font-size: 11.5px;">
                                                    {{ $myReview ? 'Update' : 'Submit' }}
                                                </button>
                                                @if($myReview)
                                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5" style="font-size: 11.5px;" onclick="document.getElementById('delete-rev-form').submit();">Delete</button>
                                                @endif
                                            </div>
                                        </form>
                                        @if($myReview)
                                            <form id="delete-rev-form" action="{{ route('user.product.review.destroy', $myReview->id) }}" method="POST" class="d-none">
                                                @csrf @method('DELETE')
                                            </form>
                                        @endif
                                    @else
                                        <p class="small text-muted mb-0" style="font-size: 11.5px;"><i class="fas fa-info-circle me-1 text-primary"></i> Only verified buyers can write a review.</p>
                                    @endif
                                @else
                                    <p class="small text-muted mb-0" style="font-size: 11.5px;">Please <a href="{{ route('login') }}" class="text-primary fw-bold">Login</a> to review.</p>
                                @endauth
                            </div>

                            <div class="d-flex flex-column gap-2 px-1">
                                @forelse($reviews as $rev)
                                    <div class="border-bottom pb-2">
                                        <div class="d-flex justify-content-between align-items-center mb-0.5">
                                            <strong class="text-dark small" style="font-size: 12px;">{{ $rev->user_name }}</strong>
                                            <small class="text-muted" style="font-size: 10px;">{{ date('d M Y', strtotime($rev->created_at)) }}</small>
                                        </div>
                                        <div class="text-warning small mb-1" style="font-size: 10px;">
                                            @for($r = 1; $r <= 5; $r++)
                                                <i class="fas fa-star {{ $r <= $rev->rating ? 'text-warning' : 'text-muted opacity-25' }}"></i>
                                            @endfor
                                            <span class="badge bg-success-subtle text-success ms-1 font-monospace" style="font-size: 8.5px;">Verified</span>
                                        </div>
                                        <p class="text-secondary mb-0" style="font-size: 12px; line-height: 1.4;">{{ $rev->comment }}</p>
                                    </div>
                                @empty
                                    <p class="text-muted small mb-0 py-1" style="font-size: 11.5px;">No reviews yet.</p>
                                @endforelse
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Related Products -->
            @if(($uiSettings['show_related'] ?? true) && !empty($relatedProducts) && $relatedProducts->count() > 0)
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="fw-bold text-dark mb-0 fs-6">Related Products</h5>
                        <div class="d-flex gap-1">
                            <button type="button" class="header-icon-btn" style="width: 26px; height: 26px;" onclick="scrollSlider('related-slider-box', -1)"><i class="fas fa-chevron-left" style="font-size: 10px;"></i></button>
                            <button type="button" class="header-icon-btn" style="width: 26px; height: 26px;" onclick="scrollSlider('related-slider-box', 1)"><i class="fas fa-chevron-right" style="font-size: 10px;"></i></button>
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
                                            <img src="{{ $rImg }}" alt="{{ $rel->title ?? $rel->name }}" onerror="this.src='https://placehold.co/300x300?text=Item'">
                                        </a>
                                    </div>
                                    <div class="p-2 d-flex flex-column flex-grow-1">
                                        <h6 class="small fw-semibold text-truncate mb-1" style="font-size: 11.5px;" title="{{ $rel->title ?? $rel->name }}">
                                            <a href="{{ route('product.details', $rel->slug ?? $rel->id) }}" class="text-dark text-decoration-none">
                                                {{ $rel->title ?? $rel->name }}
                                            </a>
                                        </h6>
                                        <div class="mt-auto d-flex align-items-baseline gap-1">
                                            <strong class="text-primary small fw-bold" style="font-size: 11px;">৳{{ number_format($rPrice, 0) }}</strong>
                                            @if($rOld > $rPrice)
                                                <del class="text-muted" style="font-size: 9.5px;">৳{{ number_format($rOld, 0) }}</del>
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
                        <h5 class="fw-bold text-dark mb-0 fs-6">Recently Viewed</h5>
                        <div class="d-flex gap-1">
                            <button type="button" class="header-icon-btn" style="width: 26px; height: 26px;" onclick="scrollSlider('recent-slider-box', -1)"><i class="fas fa-chevron-left" style="font-size: 10px;"></i></button>
                            <button type="button" class="header-icon-btn" style="width: 26px; height: 26px;" onclick="scrollSlider('recent-slider-box', 1)"><i class="fas fa-chevron-right" style="font-size: 10px;"></i></button>
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
                                            <img src="{{ $recImg }}" alt="{{ $rec->title ?? $rec->name }}" onerror="this.src='https://placehold.co/300x300?text=Item'">
                                        </a>
                                    </div>
                                    <div class="p-2 d-flex flex-column flex-grow-1">
                                        <h6 class="small fw-semibold text-truncate mb-1" style="font-size: 11.5px;" title="{{ $rec->title ?? $rec->name }}">
                                            <a href="{{ route('product.details', $rec->slug ?? $rec->id) }}" class="text-dark text-decoration-none">
                                                {{ $rec->title ?? $rec->name }}
                                            </a>
                                        </h6>
                                        <div class="mt-auto d-flex align-items-baseline gap-1">
                                            <strong class="text-primary small fw-bold" style="font-size: 11px;">৳{{ number_format($recPrice, 0) }}</strong>
                                            @if($recOld > $recPrice)
                                                <del class="text-muted" style="font-size: 9.5px;">৳{{ number_format($recOld, 0) }}</del>
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

        // Immediately switch image if the color swatch itself has an associated image
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

        // 1. Try exact Color + Size match
        let matched = variantsData.find(v => {
            const cMatch = !currentColor || (v.color && v.color.trim().toLowerCase() === currentColor.trim().toLowerCase());
            const sMatch = !currentSize || (v.size && v.size.trim().toLowerCase() === currentSize.trim().toLowerCase());
            return cMatch && sMatch;
        });

        // 2. Fallback: if exact combo doesn't exist, match by selected Color or Size
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
            
            // Live Price Update
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

            // Live SKU Update
            const skuEl = document.getElementById('display-sku');
            if (skuEl && matched.sku) {
                skuEl.innerText = matched.sku;
            }

            // Live Stock Badge Update
            const stockBadgeWrap = document.getElementById('display-stock-status');
            if (stockBadgeWrap && typeof matched.stock !== 'undefined') {
                if (matched.stock > 0) {
                    stockBadgeWrap.innerHTML = `<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fw-bold" style="font-size: 10px;"><i class="fas fa-check-circle me-1"></i>In Stock (${matched.stock})</span>`;
                } else {
                    stockBadgeWrap.innerHTML = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fw-bold" style="font-size: 10px;">Out of Stock</span>`;
                }
            }

            // Live Variant Image Switch (or fallback to any variant with same color)
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

    document.addEventListener('DOMContentLoaded', () => {
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

        // Auto select first variant swatch on load
        const firstColor = document.querySelector('.swatch-color-btn');
        if (firstColor) firstColor.click();
        const firstSize = document.querySelector('.swatch-size-btn');
        if (firstSize) firstSize.click();
    });
</script>
@endsection