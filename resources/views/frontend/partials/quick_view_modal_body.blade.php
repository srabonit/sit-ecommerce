@php
    $finalPrice = (float) ($product->sale_price ?? $product->price ?? $product->regular_price ?? 0);
    $regularPrice = (float) ($product->regular_price ?? 0);
    $hasDiscount = !empty($product->sale_price) && ($product->sale_price < $regularPrice);
    $discountPercentage = ($hasDiscount && $regularPrice > 0) ? (int) round((($regularPrice - $finalPrice) / $regularPrice) * 100) : 0;

    // Universal Image Resolver (Connected to Central ImagePathService)
    $resolveQvImg = function($path, $fallback = 'https://placehold.co/600x600?text=No+Image') {
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

    $mainImg = $resolveQvImg($product->thumbnail);

    $avgRating = 5.0;
    $reviewCount = 0;
    if (\Illuminate\Support\Facades\Schema::hasTable('product_reviews')) {
        $avgRating = DB::table('product_reviews')->where('product_id', $product->id)->where('status', 'approved')->avg('rating') ?: 5.0;
        $reviewCount = DB::table('product_reviews')->where('product_id', $product->id)->where('status', 'approved')->count();
    }

    $rawShortDesc = $product->short_description ?? '';
    $cleanShortDesc = html_entity_decode(stripslashes(str_replace(['\r\n', '\n', '\r'], "\n", (string)$rawShortDesc)));
    $sanitizedShortDesc = strip_tags($cleanShortDesc, '<p><br><b><strong><i><ul><ol><li>');

    $shareUrl = urlencode(route('product.details', $product->slug ?? $product->id));
    $shareTitle = urlencode($product->title ?? $product->name);

    $globalCardSettings = DB::table('settings')->pluck('value', 'key')->toArray();
    $showBuyNowBtn = ($globalCardSettings['feature_buy_now_btn'] ?? '1') == '1';

    $variants = ($product->variants && $product->variants->count() > 0)
        ? $product->variants
        : (\Illuminate\Support\Facades\Schema::hasTable('product_variants')
            ? DB::table('product_variants')->where('product_id', $product->id)->get()
            : collect());

    $uniqueColors = $variants->pluck('color')->map(fn($c) => trim((string)$c))->filter()->unique()->values();
    $uniqueSizes  = $variants->pluck('size')->map(fn($s) => trim((string)$s))->filter()->unique()->values();

    // Map each color to its first available variant image for swatch preview & fallback
    $qvColorImageMap = [];
    foreach ($variants as $vRow) {
        $cKey = strtolower(trim((string)($vRow->color ?? '')));
        if ($cKey !== '' && !empty($vRow->image) && !isset($qvColorImageMap[$cKey])) {
            $qvColorImageMap[$cKey] = $resolveQvImg($vRow->image, '');
        }
    }

    $variantsJson = $variants->map(function($v) use ($resolveQvImg, $qvColorImageMap, $regularPrice, $finalPrice) {
        $cKey = strtolower(trim((string)($v->color ?? '')));
        $vImg = !empty($v->image)
            ? $resolveQvImg($v->image, '')
            : ($qvColorImageMap[$cKey] ?? null);

        $vRegPrice  = (float)(!empty($v->regular_price) && $v->regular_price > 0 ? $v->regular_price : $regularPrice);
        $vSalePrice = (float)(!empty($v->sale_price) && $v->sale_price > 0 ? $v->sale_price : $vRegPrice);

        return [
            'id'            => $v->id,
            'color'         => $v->color ? trim((string)$v->color) : '',
            'size'          => $v->size ? trim((string)$v->size) : '',
            'price'         => $vSalePrice > 0 ? $vSalePrice : $finalPrice,
            'regular_price' => $vRegPrice > 0 ? $vRegPrice : $regularPrice,
            'stock'         => (int)($v->stock ?? 0),
            'sku'           => $v->product_code ?? ($v->sku ?? ''),
            'image'         => $vImg ?: null,
        ];
    })->values();

    $allThumbnails = collect();
    if (!empty($product->galleries) && $product->galleries->count() > 0) {
        foreach($product->galleries as $gallery) {
            $gPath = $gallery->image_path ?? ($gallery->image ?? '');
            if (!empty($gPath)) {
                $gResolved = $resolveQvImg($gPath, '');
                if (!empty($gResolved) && !$allThumbnails->contains($gResolved) && $gResolved !== $mainImg) {
                    $allThumbnails->push($gResolved);
                }
            }
        }
    }
    foreach($variants as $varItem) {
        if (!empty($varItem->image)) {
            $vPath = $resolveQvImg($varItem->image, '');
            if (!empty($vPath) && !$allThumbnails->contains($vPath) && $vPath !== $mainImg) {
                $allThumbnails->push($vPath);
            }
        }
    }
@endphp

<style>
    .qv-modal-body-wrap {
        position: relative;
        background: #ffffff;
        border-radius: 20px;
        padding: 24px;
        box-sizing: border-box;
        overflow: hidden;
    }

    .qv-skeleton-loader {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: #ffffff;
        z-index: 1055;
        padding: 24px;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        gap: 16px;
        transition: opacity 0.35s ease, visibility 0.35s ease;
    }
    @media (min-width: 768px) {
        .qv-skeleton-loader {
            flex-direction: row;
            gap: 24px;
        }
    }

    .qv-shimmer-box {
        background: #f1f5f9;
        background-image: linear-gradient(90deg, #f1f5f9 0px, #e2e8f0 50%, #f1f5f9 100%);
        background-size: 200% 100%;
        animation: qvShimmerWave 1.4s infinite linear;
        border-radius: 10px;
    }
    @keyframes qvShimmerWave {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }

    .qv-content-fade {
        opacity: 0;
        transform: scale(0.985);
        transition: opacity 0.35s cubic-bezier(0.16, 1, 0.3, 1), transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .qv-content-fade.qv-loaded {
        opacity: 1 !important;
        transform: scale(1) !important;
    }

    @media (max-width: 767.98px) {
        .qv-modal-body-wrap {
            padding: 14px 12px 18px 12px !important;
            max-height: 86vh;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: 16px;
        }
        .qv-skeleton-loader {
            padding: 14px 12px !important;
        }
    }

    .qv-modal-header-btn {
        position: absolute;
        top: 10px;
        right: 12px;
        z-index: 1060;
        background: #f1f5f9;
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #475569;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    }
    .qv-modal-header-btn:hover {
        background: #fee2e2;
        color: #ef4444;
        transform: rotate(90deg);
    }

    .qv-zoom-box {
        position: relative;
        overflow: hidden;
        background: #f8fafc;
        border-radius: 14px;
        border: 1px solid #edf2f7;
        aspect-ratio: 1 / 1;
        width: 100%;
        max-width: 340px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    @media (min-width: 768px) {
        .qv-zoom-box {
            max-width: 100%;
            border-radius: 16px;
        }
    }
    .qv-zoom-box img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        display: block;
        transition: transform 0.2s ease;
    }

    .qv-thumb-scroll {
        display: flex;
        gap: 6px;
        overflow-x: auto;
        scroll-behavior: smooth;
        padding: 8px 2px 4px 2px;
        margin-top: 4px;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
        justify-content: flex-start;
    }
    @media (min-width: 768px) {
        .qv-thumb-scroll {
            gap: 8px;
            margin-top: 8px;
        }
    }
    .qv-thumb-scroll::-webkit-scrollbar { height: 4px; }
    .qv-thumb-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

    .qv-thumb-item {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        border: 2px solid #e2e8f0;
        padding: 2px;
        object-fit: contain;
        background: #ffffff;
        cursor: pointer;
        flex-shrink: 0;
        transition: all 0.15s ease;
    }
    @media (min-width: 768px) {
        .qv-thumb-item {
            width: 52px;
            height: 52px;
        }
    }
    .qv-thumb-item:hover, .qv-thumb-item.active-thumb {
        border-color: #2563eb !important;
        transform: scale(1.04);
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.2);
    }

    .qv-product-title {
        font-weight: 800;
        color: #0f172a;
        line-height: 1.25;
        font-size: 1.1rem;
        margin-bottom: 4px;
        padding-right: 28px;
    }
    @media (min-width: 768px) {
        .qv-product-title {
            font-size: 1.35rem;
            padding-right: 0;
        }
    }

    .qv-price-banner {
        background: #f8fafc;
        border: 1px solid #edf2f7;
        border-radius: 12px;
        padding: 8px 12px;
        margin-bottom: 10px;
    }
    @media (min-width: 768px) {
        .qv-price-banner {
            padding: 10px 14px;
            margin-bottom: 12px;
        }
    }
    .qv-current-price {
        font-size: 1.35rem;
        font-weight: 800;
        color: #dc2626;
        letter-spacing: -0.02em;
        line-height: 1;
    }
    @media (min-width: 768px) {
        .qv-current-price {
            font-size: 1.55rem;
        }
    }
    .qv-old-price {
        font-size: 0.9rem;
        color: #94a3b8;
        text-decoration: line-through;
        font-weight: 500;
    }
    .qv-saving-tag {
        background: #fee2e2;
        color: #dc2626;
        font-size: 10px;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 4px;
    }

    .qv-swatch-group {
        margin-bottom: 10px;
    }
    .qv-swatch-title {
        font-size: 11px;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-bottom: 5px;
    }
    .qv-swatch-container {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    @media (min-width: 768px) {
        .qv-swatch-container { gap: 8px; }
    }
    .qv-swatch-chip {
        cursor: pointer;
        border: 1.5px solid #cbd5e1;
        border-radius: 8px;
        padding: 4px 10px;
        font-size: 11.5px;
        font-weight: 600;
        color: #1e293b;
        background: #ffffff;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        user-select: none;
    }
    @media (min-width: 768px) {
        .qv-swatch-chip {
            padding: 5px 12px;
            font-size: 12px;
        }
    }
    .qv-swatch-chip:hover {
        border-color: #94a3b8;
    }
    .qv-swatch-chip.active {
        border-color: #2563eb !important;
        background: #eff6ff !important;
        color: #2563eb !important;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
    }
    .qv-swatch-mini-thumb {
        width: 18px;
        height: 18px;
        border-radius: 4px;
        object-fit: cover;
        border: 1px solid #cbd5e1;
    }

    .qv-qty-wrap {
        display: inline-flex;
        align-items: center;
        border: 1.5px solid #cbd5e1;
        border-radius: 8px;
        background: #ffffff;
        overflow: hidden;
        height: 34px;
    }
    .qv-qty-btn {
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
    .qv-qty-btn:hover { background: #e2e8f0; }
    .qv-qty-input {
        width: 40px;
        height: 34px;
        border: none;
        text-align: center;
        font-weight: 700;
        font-size: 13px;
        color: #0f172a;
        background: #ffffff;
    }
    .qv-qty-input:focus { outline: none; }

    .qv-action-grid {
        display: flex;
        flex-direction: column;
        gap: 8px;
        width: 100%;
        margin-top: 10px;
        margin-bottom: 6px;
    }
    @media (min-width: 576px) {
        .qv-action-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
    }

    .btn-solid-qv-buy {
        background-color: var(--details-btn-order-bg, #0dcaf0) !important;
        border: none !important;
        color: var(--details-btn-order-text, #ffffff) !important;
        font-weight: 700 !important;
        font-size: 13px !important;
        height: 40px !important;
        border-radius: 8px !important;
        cursor: pointer !important;
        width: 100% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        text-decoration: none !important;
        transition: all 0.2s ease !important;
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08) !important;
    }
    .btn-solid-qv-buy:hover {
        filter: brightness(0.92);
        color: var(--details-btn-order-text, #ffffff) !important;
    }
    .btn-solid-qv-buy i, .btn-solid-qv-buy span {
        color: var(--details-btn-order-text, #ffffff) !important;
    }

    .btn-solid-qv-cart {
        background-color: var(--details-btn-cart-bg, #ffc107) !important;
        border: none !important;
        color: var(--details-btn-cart-text, #212529) !important;
        font-weight: 700 !important;
        font-size: 13px !important;
        height: 40px !important;
        border-radius: 8px !important;
        cursor: pointer !important;
        width: 100% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        text-decoration: none !important;
        transition: all 0.2s ease !important;
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08) !important;
    }
    .btn-solid-qv-cart:hover {
        filter: brightness(0.92);
        color: var(--details-btn-cart-text, #212529) !important;
    }
    .btn-solid-qv-cart i, .btn-solid-qv-cart span {
        color: var(--details-btn-cart-text, #212529) !important;
        font-weight: 700 !important;
    }

    .social-share-circle {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #ffffff !important;
        font-size: 10px;
        text-decoration: none;
    }
</style>

<div class="qv-modal-body-wrap" id="qv-modal-wrapper">
    <button type="button" class="qv-modal-header-btn" data-bs-dismiss="modal" aria-label="Close">
        <i class="fas fa-times"></i>
    </button>

    <div class="qv-skeleton-loader" id="qvSkeletonPlaceholder">
        <div style="flex: 1; max-width: 320px; margin: 0 auto; width: 100%;">
            <div class="qv-shimmer-box" style="width: 100%; aspect-ratio: 1/1; border-radius: 14px; margin-bottom: 8px;"></div>
            <div style="display: flex; gap: 8px;">
                <div class="qv-shimmer-box" style="width: 48px; height: 48px; border-radius: 8px;"></div>
                <div class="qv-shimmer-box" style="width: 48px; height: 48px; border-radius: 8px;"></div>
                <div class="qv-shimmer-box" style="width: 48px; height: 48px; border-radius: 8px;"></div>
            </div>
        </div>
        <div style="flex: 1.4; display: flex; flex-direction: column; gap: 10px;">
            <div class="qv-shimmer-box" style="width: 80%; height: 24px;"></div>
            <div class="qv-shimmer-box" style="width: 45%; height: 16px;"></div>
            <div class="qv-shimmer-box" style="width: 100%; height: 60px; border-radius: 12px;"></div>
            <div class="qv-shimmer-box" style="width: 60%; height: 18px;"></div>
            <div class="qv-shimmer-box" style="width: 100%; height: 40px; border-radius: 8px; margin-top: 10px;"></div>
        </div>
    </div>

    <div class="qv-content-fade" id="qvRealContent">
        <div class="row g-3 g-md-4 align-items-start">
            <div class="col-12 col-md-5 text-center">
                <div class="qv-zoom-box">
                    @if($hasDiscount)
                        <span class="badge bg-danger position-absolute top-0 start-0 m-2 px-2 py-1 z-2 small" id="qv-badge-discount">-{{ $discountPercentage }}%</span>
                    @endif
                    <img id="qv-main-display-image" src="{{ $mainImg }}" class="img-fluid object-fit-contain w-100 h-100 rounded" alt="{{ $product->title }}" onerror="this.src='https://placehold.co/600x600?text=No+Image'">
                </div>

                <div class="qv-thumb-scroll" id="qv-thumb-container">
                    <img src="{{ $mainImg }}" class="qv-thumb-item active-thumb" onclick="qvSetImage('{{ $mainImg }}', this)" alt="Thumb" onerror="this.style.display='none'">
                    @if(!empty($allThumbnails) && $allThumbnails->count() > 0)
                        @foreach($allThumbnails as $galImg)
                            <img src="{{ $galImg }}" class="qv-thumb-item" onclick="qvSetImage('{{ $galImg }}', this)" alt="Gallery" onerror="this.style.display='none'">
                        @endforeach
                    @endif
                </div>
            </div>

            <div class="col-12 col-md-7">
                <h4 class="qv-product-title">{{ $product->title ?? $product->name }}</h4>
                <div class="d-flex flex-wrap align-items-center gap-2 small text-muted mb-2" style="font-size: 11px;">
                    <span>Category: <strong>{{ $product->category->name ?? 'Collection' }}</strong></span>
                    <span>•</span>
                    <span class="font-monospace">SKU: <strong id="qv-display-sku" class="text-primary">{{ $product->sku ?? ($product->product_code ?? 'PRD-' . $product->id) }}</strong></span>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2 pb-1 border-bottom">
                    <div class="text-warning small" style="font-size: 10.5px;">
                        @for($s = 1; $s <= 5; $s++)
                            <i class="fas fa-star {{ $s <= round($avgRating) ? 'text-warning' : 'text-muted opacity-25' }}"></i>
                        @endfor
                        <span class="text-muted ms-1">({{ $reviewCount }})</span>
                    </div>

                    <div class="d-flex align-items-center gap-1">
                        <span class="text-muted small me-1" style="font-size: 10.5px;">Share:</span>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" class="social-share-circle" style="background: #1877f2;"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://api.whatsapp.com/send?text={{ $shareTitle }}%20-%20{{ $shareUrl }}" target="_blank" class="social-share-circle" style="background: #25d366;"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>

                <div class="qv-price-banner">
                    <div class="d-flex align-items-baseline gap-2 mb-1">
                        <span id="qv-active-price" class="qv-current-price" data-unit-price="{{ $finalPrice }}">৳{{ number_format($finalPrice, 2) }}</span>
                        <span id="qv-active-old-price" class="qv-old-price {{ $hasDiscount ? '' : 'd-none' }}">৳{{ number_format($regularPrice, 2) }}</span>
                        <span id="qv-discount-tag" class="qv-saving-tag {{ $hasDiscount ? '' : 'd-none' }}">{{ $discountPercentage }}% OFF</span>
                    </div>
                    <div class="small text-secondary" style="font-size: 11px;">
                        Estimated Total: <strong id="qv-calculated-total-display" class="text-dark fw-bold">৳{{ number_format($finalPrice, 2) }}</strong>
                    </div>
                </div>

                @if(!empty($sanitizedShortDesc))
                    <div class="small text-secondary mb-2" style="max-height: 48px; overflow-y: auto; font-size: 11px; line-height: 1.45;">
                        {!! nl2br($sanitizedShortDesc) !!}
                    </div>
                @endif

                <form id="qv-main-form" action="{{ route('cart.add', $product->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="variant_id" id="qv-selected-variant-id" value="">

                    @if($uniqueColors->count() > 0)
                        <div class="qv-swatch-group">
                            <div class="qv-swatch-title">
                                Color: <span id="qv-selected-color-name" class="text-primary font-monospace fw-bold"></span>
                            </div>
                            <div class="qv-swatch-container" id="qv-color-container">
                                @foreach($uniqueColors as $color)
                                    @php
                                        $cLookup = strtolower(trim($color));
                                        $swatchImg = $qvColorImageMap[$cLookup] ?? null;
                                    @endphp
                                    <div class="qv-swatch-chip qv-color-btn" data-image="{{ $swatchImg ?? '' }}" onclick="qvSetColor('{{ addslashes($color) }}', this)">
                                        @if(!empty($swatchImg))
                                            <img src="{{ $swatchImg }}" alt="{{ $color }}" class="qv-swatch-mini-thumb" onerror="this.style.display='none'">
                                        @else
                                            <i class="fas fa-circle text-primary opacity-75" style="font-size: 8px;"></i>
                                        @endif
                                        <span>{{ $color }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($uniqueSizes->count() > 0)
                        <div class="qv-swatch-group">
                            <div class="qv-swatch-title">
                                Size / Spec: <span id="qv-selected-size-name" class="text-primary font-monospace fw-bold"></span>
                            </div>
                            <div class="qv-swatch-container" id="qv-size-container">
                                @foreach($uniqueSizes as $size)
                                    <div class="qv-swatch-chip qv-size-btn" onclick="qvSetSize('{{ addslashes($size) }}', this)">
                                        {{ $size }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mb-2 d-flex align-items-center gap-2">
                        <label class="small fw-bold text-dark mb-0" style="font-size: 11.5px;">Quantity:</label>
                        <div class="qv-qty-wrap">
                            <button class="qv-qty-btn" type="button" onclick="qvChangeQty(-1)">-</button>
                            <input id="qv-product-qty-input" type="number" name="quantity" class="qv-qty-input" value="1" min="1" max="99" onchange="qvCalculatePrice()" oninput="qvCalculatePrice()">
                            <button class="qv-qty-btn" type="button" onclick="qvChangeQty(1)">+</button>
                        </div>
                    </div>

                    <div class="qv-action-grid">
                        @if($showBuyNowBtn)
                            <button type="submit" name="action" value="order_now" class="btn-solid-qv-buy">
                                <i class="fas fa-bolt"></i> <span>Buy Now</span>
                            </button>
                        @endif
                        <button type="submit" name="action" value="add_to_cart" class="btn-solid-qv-cart">
                            <i class="fas fa-shopping-cart"></i> <span>Add Cart</span>
                        </button>
                    </div>
                </form>

                <div class="pt-2 border-top text-center mt-1">
                    <a href="{{ route('product.details', $product->slug ?? $product->id) }}" class="text-decoration-none small text-primary fw-bold" style="font-size: 11.5px;">
                        View Full Product Details <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.qvData = {!! json_encode($variantsJson) !!};
    window.qvColor = null;
    window.qvSize = null;

    (function() {
        var placeholder = document.getElementById('qvSkeletonPlaceholder');
        var content = document.getElementById('qvRealContent');
        setTimeout(function() {
            if (placeholder) {
                placeholder.style.opacity = '0';
                placeholder.style.visibility = 'hidden';
            }
            if (content) {
                content.classList.add('qv-loaded');
            }
        }, 180);
    })();

    window.qvSetImage = function(src, el) {
        if (!src) return;
        var img = document.getElementById('qv-main-display-image');
        if (img) img.src = src;
        document.querySelectorAll('#qv-thumb-container .qv-thumb-item').forEach(function(t) {
            if (el ? t === el : (t.src === src || t.getAttribute('src') === src)) {
                t.classList.add('active-thumb');
                t.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
            } else {
                t.classList.remove('active-thumb');
            }
        });
    };

    window.qvSetColor = function(c, el) {
        document.querySelectorAll('#qv-color-container .qv-color-btn').forEach(function(b) {
            b.classList.remove('active');
        });
        if (el) {
            el.classList.add('active');
            var swatchImg = el.getAttribute('data-image');
            if (swatchImg) {
                window.qvSetImage(swatchImg, null);
            }
        }
        window.qvColor = c;
        var lbl = document.getElementById('qv-selected-color-name');
        if (lbl) lbl.innerText = c;
        window.qvFindMatch();
    };

    window.qvSetSize = function(s, el) {
        document.querySelectorAll('#qv-size-container .qv-size-btn').forEach(function(b) {
            b.classList.remove('active');
        });
        if (el) el.classList.add('active');
        window.qvSize = s;
        var lbl = document.getElementById('qv-selected-size-name');
        if (lbl) lbl.innerText = s;
        window.qvFindMatch();
    };

    window.qvFindMatch = function() {
        if (!window.qvData || window.qvData.length === 0) return;

        var match = window.qvData.find(function(v) {
            var cOk = !window.qvColor || (v.color && v.color.trim().toLowerCase() === window.qvColor.trim().toLowerCase());
            var sOk = !window.qvSize || (v.size && v.size.trim().toLowerCase() === window.qvSize.trim().toLowerCase());
            return cOk && sOk;
        });

        if (!match && window.qvColor) {
            match = window.qvData.find(function(v) {
                return v.color && v.color.trim().toLowerCase() === window.qvColor.trim().toLowerCase();
            });
        }
        if (!match && window.qvSize) {
            match = window.qvData.find(function(v) {
                return v.size && v.size.trim().toLowerCase() === window.qvSize.trim().toLowerCase();
            });
        }

        if (match) {
            var vInput = document.getElementById('qv-selected-variant-id');
            if (vInput) vInput.value = match.id;

            var priceEl = document.getElementById('qv-active-price');
            if (priceEl) {
                priceEl.innerText = '৳' + parseFloat(match.price).toFixed(2);
                priceEl.setAttribute('data-unit-price', match.price);
            }

            var oldPriceEl = document.getElementById('qv-active-old-price');
            var discTag = document.getElementById('qv-discount-tag');
            if (oldPriceEl && match.regular_price > match.price) {
                oldPriceEl.innerText = '৳' + parseFloat(match.regular_price).toFixed(2);
                oldPriceEl.classList.remove('d-none');
                if (discTag) {
                    var pct = Math.round(((match.regular_price - match.price) / match.regular_price) * 100);
                    discTag.innerText = pct + '% OFF';
                    discTag.classList.remove('d-none');
                }
            } else {
                if (oldPriceEl) oldPriceEl.classList.add('d-none');
                if (discTag) discTag.classList.add('d-none');
            }

            var skuEl = document.getElementById('qv-display-sku');
            if (skuEl && match.sku) skuEl.innerText = match.sku;

            var targetImg = match.image;
            if (!targetImg && window.qvColor) {
                var colorWithImg = window.qvData.find(function(v) {
                    return v.color && v.color.trim().toLowerCase() === window.qvColor.trim().toLowerCase() && v.image;
                });
                if (colorWithImg) targetImg = colorWithImg.image;
            }
            if (targetImg) {
                window.qvSetImage(targetImg, null);
            }

            window.qvCalculatePrice();
        }
    };

    window.qvChangeQty = function(step) {
        var q = document.getElementById('qv-product-qty-input');
        if (!q) return;
        var val = parseInt(q.value) || 1;
        val += step;
        if (val < 1) val = 1;
        q.value = val;
        window.qvCalculatePrice();
    };

    window.qvCalculatePrice = function() {
        var q = document.getElementById('qv-product-qty-input');
        var p = document.getElementById('qv-active-price');
        var tot = document.getElementById('qv-calculated-total-display');
        if (!q || !p || !tot) return;
        var qty = parseInt(q.value) || 1;
        var price = parseFloat(p.getAttribute('data-unit-price')) || 0;
        tot.innerText = '৳' + (qty * price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    setTimeout(function() {
        var firstCol = document.querySelector('#qv-color-container .qv-color-btn');
        if (firstCol) firstCol.click();
        var firstSz = document.querySelector('#qv-size-container .qv-size-btn');
        if (firstSz) firstSz.click();
        window.qvCalculatePrice();
    }, 10);
</script>