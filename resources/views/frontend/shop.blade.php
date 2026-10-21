@extends('layouts.app')

@php
    $globalSettings = DB::table('settings')->pluck('value', 'key');
    $colMobile = $globalSettings['grid_mobile'] ?? '2';
    $colTab    = $globalSettings['grid_tab'] ?? '2';
    $colPc     = $globalSettings['grid_pc'] ?? '3';

    // ১. মেনু বিল্ডার (site_menus) থেকে mobile_categories লোকেশনের মেনু রিড করা
    $catMenuRow = DB::table('site_menus')->whereJsonContains('locations', 'mobile_categories')->first();
    if (!$catMenuRow) {
        $catMenuRow = DB::table('site_menus')->whereJsonContains('locations', 'primary')->first();
    }
    $rawCatItems = !empty($catMenuRow->items) ? json_decode($catMenuRow->items, true) : [];

    // ২. মেনু বিল্ডারের ফ্ল্যাট আইটেমগুলোকে প্যারেন্ট ও চাইল্ড ট্রিতে রূপান্তর
    $categoryMenuTree = [];
    $pointers = [];
    foreach ($rawCatItems as $item) {
        $item['children'] = [];
        $depth = $item['depth'] ?? ($item['is_sub'] ?? false ? 1 : 0);

        if ($depth === 0) {
            $categoryMenuTree[] = $item;
            $pointers[0] = count($categoryMenuTree) - 1;
        } elseif ($depth === 1 && isset($pointers[0])) {
            $categoryMenuTree[$pointers[0]]['children'][] = $item;
            $pointers[1] = count($categoryMenuTree[$pointers[0]]['children']) - 1;
        } elseif ($depth === 2 && isset($pointers[0], $pointers[1])) {
            $categoryMenuTree[$pointers[0]]['children'][$pointers[1]]['children'][] = $item;
        }
    }

    // ৩. ব্যাকআপ: যদি মেনু বিল্ডারে কোনো আইটেম সেভ না থাকে তবে ক্যাটাগরি টেবিল থেকে রিড
    if (empty($categoryMenuTree)) {
        $dbCategories = \App\Models\Category::all();
        $hasParentCol = \Illuminate\Support\Facades\Schema::hasColumn('categories', 'parent_id');
        
        if ($hasParentCol) {
            $parents = $dbCategories->where(fn($c) => empty($c->parent_id) || $c->parent_id == 0);
            foreach ($parents as $p) {
                $children = $dbCategories->where('parent_id', $p->id);
                $cList = [];
                foreach ($children as $ch) {
                    $cList[] = [
                        'title' => $ch->name,
                        'url'   => route('category.products', $ch->slug ?? $ch->id),
                        'children' => []
                    ];
                }
                $categoryMenuTree[] = [
                    'title'    => $p->name,
                    'url'      => route('category.products', $p->slug ?? $p->id),
                    'children' => $cList
                ];
            }
        } else {
            foreach ($dbCategories as $cat) {
                $categoryMenuTree[] = [
                    'title'    => $cat->name,
                    'url'      => route('category.products', $cat->slug ?? $cat->id),
                    'children' => []
                ];
            }
        }
    }

    // ডাইনামিক URL ফরম্যাটার
    if (!function_exists('shopDynamicUrl')) {
        function shopDynamicUrl($url) {
            if (empty($url) || $url === '#') return '#';
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                $parsed = parse_url($url);
                return url(($parsed['path'] ?? '/') . (!empty($parsed['query']) ? '?' . $parsed['query'] : ''));
            }
            return url($url);
        }
    }

    // মেনু আইটেম অ্যাক্টিভ আছে কিনা চেক করা
    if (!function_exists('isCatNodeActive')) {
        function isCatNodeActive($node) {
            $currentUrl = url()->current();
            $targetUrl = shopDynamicUrl($node['url'] ?? '');
            if ($targetUrl !== '#' && $currentUrl === $targetUrl) return true;
            if (!empty($node['children'])) {
                foreach ($node['children'] as $child) {
                    if (isCatNodeActive($child)) return true;
                }
            }
            return false;
        }
    }

    $isAllActive = request()->is('shop') && !request()->has('category');
    $activeCategoryIdentifier = isset($category) ? ($category->slug ?? $category->id) : (request('category') ?? '');
@endphp

@section('title', (isset($category) ? $category->name . ' - ' : 'Shop - ') . config('app.name', 'Ecommerce by Srabon IT'))

@section('content')
<style>
    /* ================= SIDEBAR CATEGORY MENU CONTAINER ================= */
    .shop-sidebar-widget {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #edf2f7;
        box-shadow: 0 4px 15px -2px rgba(0, 0, 0, 0.03);
        position: sticky;
        top: 80px;
        z-index: 100;
        overflow: visible !important;
    }

    .shop-category-list {
        list-style: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    .shop-cat-row {
        position: relative;
        list-style: none !important;
    }

    .shop-cat-link {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 9.5px 16px;
        color: #334155;
        font-weight: 500;
        font-size: 13.5px;
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .shop-cat-link:hover,
    .shop-cat-row:hover > .shop-cat-link {
        background-color: #f1f5f9;
        color: #2563eb !important;
    }

    .shop-cat-link.active {
        background-color: #eff6ff;
        color: #2563eb !important;
        font-weight: 700;
    }

    /* Child Dropdown */
    .shop-cat-flyout-box {
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
        z-index: 9999 !important;
        margin-left: 6px;
        list-style: none !important;
    }

    .shop-cat-row:hover > .shop-cat-flyout-box {
        display: block !important;
        animation: catFlyoutAnim 0.2s ease-in-out;
    }

    .shop-cat-row::after {
        content: '';
        position: absolute;
        top: 0;
        right: -8px;
        width: 12px;
        height: 100%;
    }

    .shop-sub-link {
        display: block;
        padding: 8px 18px;
        color: #475569;
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.15s ease;
    }

    .shop-sub-link:hover {
        background-color: #f8fafc;
        color: #2563eb;
        padding-left: 22px;
    }

    .shop-sub-link.active {
        color: #2563eb;
        font-weight: 700;
        background-color: #eff6ff;
    }

    @keyframes catFlyoutAnim {
        from { opacity: 0; transform: translateX(-6px); }
        to { opacity: 1; transform: translateX(0); }
    }

    /* ================= DYNAMIC GRID ================= */
    .dynamic-product-grid {
        display: grid !important;
        gap: 16px !important;
        width: 100% !important;
        grid-template-columns: repeat(var(--col-pc, 3), minmax(0, 1fr)) !important;
        transition: opacity 0.2s ease;
    }

    @media (max-width: 991.98px) {
        .dynamic-product-grid {
            grid-template-columns: repeat(var(--col-tab, 2), minmax(0, 1fr)) !important;
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

    /* ================= AJAX SORT BAR ================= */
    .shop-top-controls {
        background: #ffffff;
        border: 1px solid #edf2f7;
        border-radius: 14px;
        padding: 12px 18px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }

    .shop-sort-select {
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        color: #334155;
        border-color: #cbd5e1;
        background-color: #f8fafc;
        padding: 6px 30px 6px 12px;
        cursor: pointer;
        min-width: 180px;
        transition: all 0.2s ease;
    }
    .shop-sort-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        background-color: #ffffff;
    }

    /* ================= 100% RESPONSIVE PAGINATION ================= */
    .shop-pagination-card {
        background: #ffffff;
        border: 1px solid #edf2f7;
        border-radius: 14px;
        padding: 18px 22px;
        margin-top: 40px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        width: 100%;
    }

    .pagination-counter-text {
        font-size: 13.5px;
        color: #64748b;
        font-weight: 500;
        margin: 0;
        white-space: nowrap;
    }

    .pagination-buttons-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        max-width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        padding: 4px 0;
    }
    .pagination-buttons-wrapper::-webkit-scrollbar {
        display: none;
    }

    .pagination-buttons-wrapper nav {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin: 0 auto !important;
    }
    .pagination-buttons-wrapper nav > div:first-child {
        display: none !important;
    }
    .pagination-buttons-wrapper nav > div:last-child {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    .pagination-buttons-wrapper nav > div:last-child > div:first-child {
        display: none !important;
    }

    .pagination-buttons-wrapper .pagination {
        margin: 0 auto !important;
        gap: 5px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-wrap: nowrap !important;
    }

    .pagination-buttons-wrapper .page-link {
        border-radius: 8px !important;
        border: 1px solid #e2e8f0;
        color: #334155;
        font-weight: 600;
        font-size: 13px;
        padding: 6px 12px;
        transition: all 0.2s ease;
        margin: 0 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        background: #ffffff;
    }

    .pagination-buttons-wrapper .page-item.active .page-link {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        box-shadow: 0 3px 8px rgba(37, 99, 235, 0.25);
    }

    .pagination-buttons-wrapper .page-link:hover {
        background-color: #eff6ff;
        color: #2563eb;
        border-color: #93c5fd;
    }

    /* Mobile Responsive (Always Centered) */
    @media (max-width: 767.98px) {
        .shop-pagination-card {
            flex-direction: column !important;
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
            gap: 12px !important;
            padding: 14px 10px !important;
        }

        .pagination-counter-text {
            font-size: 12.5px !important;
            text-align: center !important;
            width: 100% !important;
        }

        .pagination-buttons-wrapper {
            width: 100% !important;
            justify-content: center !important;
        }

        .pagination-buttons-wrapper nav {
            width: 100% !important;
            justify-content: center !important;
        }

        .pagination-buttons-wrapper .pagination {
            margin: 0 auto !important;
            justify-content: center !important;
        }

        .pagination-buttons-wrapper .page-link {
            font-size: 12px !important;
            padding: 4px 9px !important;
            min-width: 32px !important;
            height: 32px !important;
        }
    }

    /* Shimmer Skeleton Glow Wave Animation */
    .sc-skel-glow {
        background: #f1f5f9;
        background-image: linear-gradient(90deg, #f1f5f9 0px, #e2e8f0 50px, #f1f5f9 100px);
        background-size: 600px 100%;
        animation: scShopGlow 1.4s infinite linear;
    }
    @keyframes scShopGlow {
        0% { background-position: -300px 0; }
        100% { background-position: 300px 0; }
    }
</style>

<div id="shop-page-wrapper" class="container py-4 py-md-5">
    <!-- Breadcrumb -->
    <nav id="shop-breadcrumb-nav" aria-label="breadcrumb" class="mb-3">
        <ol id="shop-breadcrumb-list" class="breadcrumb small mb-0">
            <li id="breadcrumb-home" class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('shop') }}" class="text-decoration-none">Shop</a></li>
            @if(isset($category))
                <li id="breadcrumb-current" class="breadcrumb-item active" aria-current="page">{{ $category->name }}</li>
            @else
                <li id="breadcrumb-current" class="breadcrumb-item active" aria-current="page">All Products</li>
            @endif
        </ol>
    </nav>

    <!-- Header Title & AJAX Filter Bar -->
    <div class="shop-top-controls d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4">
        <div>
            <h4 id="shop-main-heading" class="fw-bold mb-0 fs-5 fs-md-4 text-dark">{{ isset($category) ? $category->name : 'All Products' }}</h4>
            <span id="shop-showing-text" class="text-muted small">
                Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} items
            </span>
        </div>

        <!-- AJAX Sort/Filter Dropdown Box -->
        <div class="d-flex align-items-center gap-2 align-self-stretch align-self-sm-auto">
            <label for="shopSortFilter" class="small fw-bold text-muted text-nowrap mb-0 d-none d-sm-inline">
                <i class="fas fa-sort-amount-down text-primary me-1"></i> Sort by:
            </label>
            <select id="shopSortFilter" class="form-select form-select-sm shop-sort-select flex-grow-1 flex-sm-grow-0" onchange="applyAjaxShopFilter()">
                <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest Items</option>
                <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                <option value="sale" {{ request('sort') == 'sale' ? 'selected' : '' }}>On Sale Deals</option>
                <option value="in_stock" {{ request('sort') == 'in_stock' ? 'selected' : '' }}>In Stock Only</option>
            </select>
        </div>
    </div>

    <div id="shop-grid-row" class="row g-4">
        <!-- Sidebar Column -->
        <div id="shop-sidebar-column" class="col-lg-3 col-md-4">
            <div id="shop-categories-card" class="shop-sidebar-widget p-2">
                <div class="px-3 py-2 border-bottom fw-bold text-dark small text-uppercase d-flex justify-content-between align-items-center mb-1">
                    <span><i class="fas fa-bars me-2 text-primary"></i> CATEGORIES</span>
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1" style="font-size: 11px;">Explore</span>
                </div>

                <ul id="sidebar-categories-list" class="shop-category-list">
                    @foreach($categoryMenuTree as $parentItem)
                        @php
                            $hasChildren = !empty($parentItem['children']);
                            $isActive = isCatNodeActive($parentItem);
                            $parentUrl = shopDynamicUrl($parentItem['url'] ?? '');
                        @endphp
                        <li class="shop-cat-row mb-1">
                            <a href="{{ $parentUrl }}" class="shop-cat-link {{ $isActive ? 'active' : '' }}">
                                <span class="text-truncate" style="max-width: 170px;">{{ $parentItem['title'] }}</span>
                                @if($hasChildren)
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 10px;"></i>
                                @endif
                            </a>

                            <!-- Child Flyout -->
                            @if($hasChildren)
                                <ul class="shop-cat-flyout-box">
                                    @foreach($parentItem['children'] as $childItem)
                                        @php
                                            $childUrl = shopDynamicUrl($childItem['url'] ?? '');
                                            $isChildActive = (url()->current() === $childUrl);
                                        @endphp
                                        <li>
                                            <a href="{{ $childUrl }}" class="shop-sub-link {{ $isChildActive ? 'active' : '' }}">
                                                {{ $childItem['title'] }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach

                    <li class="shop-cat-row mt-1 pt-1 border-top">
                        <a href="{{ route('shop') }}" class="shop-cat-link text-primary fw-bold {{ $isAllActive ? 'active' : '' }}">
                            <span><i class="fas fa-th-large me-2"></i> View All Categories</span>
                            <i class="fas fa-arrow-right small" style="font-size: 10px;"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Products Grid Area -->
        <div id="shop-products-column" class="col-lg-9 col-md-8 position-relative">
            
            <!-- AJAX Loading Overlay -->
            <div id="shop-ajax-loading-overlay" class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-flex align-items-center justify-content-center d-none" style="z-index: 20; border-radius: 12px; min-height: 250px;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>

            <div id="products-render-row" class="dynamic-product-grid" style="--col-mobile: {{ $colMobile }}; --col-tab: {{ $colTab }}; --col-pc: {{ $colPc }};">
                @forelse($products as $product)
                    <div id="product-col-{{ $product->id }}" class="product-card-wrap">
                        <x-product-card 
                            :product="$product" 
                            style="classic"
                            btnStyle="filled"
                            :showCategory="true"
                            :showReview="true"
                        />
                    </div>
                @empty
                    <div id="no-products-alert-box" class="col-12 text-center py-5 bg-white rounded-4 shadow-sm w-100" style="grid-column: 1 / -1;">
                        <i class="fas fa-box-open text-muted fs-1 mb-3"></i>
                        <p class="text-muted fs-5">No products found matching your selection.</p>
                        <a href="{{ route('shop') }}" class="btn btn-primary rounded-pill px-4">Browse All Products</a>
                    </div>
                @endforelse
            </div>

            <!-- Single Clean Line Pagination Block -->
            <div id="shop-pagination-container">
                @if($products->hasPages())
                    <div class="shop-pagination-card">
                        <p class="pagination-counter-text" id="pagination-bottom-summary">
                            Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} results
                        </p>
                        <div class="pagination-buttons-wrapper">
                            {{ $products->onEachSide(1)->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>

<script>
    // Universal Category & Shop AJAX Loader
    const currentCategoryContext = "{{ $activeCategoryIdentifier }}";
    const shopBaseRoute = "{{ route('shop') }}";

    // Shimmer Skeleton Card Generator
    function getShopSkeletonHTML(count) {
        let total = count || 9;
        let skel = '';
        for (let i = 0; i < total; i++) {
            skel += '<div class="product-card-wrap">' +
                '<div style="background:#ffffff; border-radius:12px; border:1px solid #edf2f7; padding:10px; display:flex; flex-direction:column; gap:8px;">' +
                    '<div class="sc-skel-glow" style="width:100%; aspect-ratio:1/1; border-radius:10px;"></div>' +
                    '<div class="sc-skel-glow" style="height:14px; width:80%; border-radius:4px;"></div>' +
                    '<div class="sc-skel-glow" style="height:16px; width:45%; border-radius:4px;"></div>' +
                    '<div class="sc-skel-glow" style="height:32px; width:100%; border-radius:20px; margin-top:4px;"></div>' +
                '</div>' +
            '</div>';
        }
        return skel;
    }

    function loadShopProductsAjax(url, browserDisplayUrl) {
        const grid = document.getElementById('products-render-row');
        const cardCount = grid ? (grid.children.length || 9) : 9;

        // Instantly display Shimmer Skeleton Cards without screen blanking
        if (grid) {
            grid.innerHTML = getShopSkeletonHTML(cardCount);
            grid.style.opacity = '1';
        }

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(function(res) {
            const contentType = res.headers.get('content-type') || '';
            if (contentType.includes('application/json')) {
                return res.json().then(function(data) {
                    return { isJson: true, data: data };
                });
            } else {
                return res.text().then(function(html) {
                    return { isJson: false, html: html };
                });
            }
        })
        .then(function(result) {
            if (result.isJson && result.data && result.data.status === 'success') {
                const data = result.data;
                if (grid) {
                    grid.innerHTML = data.grid_html;
                    grid.style.opacity = '1';
                }

                const topShowing = document.getElementById('shop-showing-text');
                if (topShowing) topShowing.innerText = data.showing_text;

                const pagContainer = document.getElementById('shop-pagination-container');
                if (pagContainer) {
                    if (data.total_count > 0 && data.pagination_html && data.pagination_html.trim() !== '') {
                        let cleanText = data.showing_text.replace('items', 'results');
                        cleanText = cleanText.split(' ').filter(function(word) { return word.length > 0; }).join(' ');

                        pagContainer.innerHTML = 
                            '<div class="shop-pagination-card">' +
                                '<p class="pagination-counter-text" id="pagination-bottom-summary">' + cleanText + '</p>' +
                                '<div class="pagination-buttons-wrapper">' + data.pagination_html + '</div>' +
                            '</div>';
                    } else {
                        pagContainer.innerHTML = '';
                    }
                }
            } else if (!result.isJson && result.html) {
                const parser = new DOMParser();
                const doc = parser.parseFromString(result.html, 'text/html');

                const newGrid = doc.getElementById('products-render-row');
                if (grid && newGrid) {
                    grid.innerHTML = newGrid.innerHTML;
                    grid.style.opacity = '1';
                }

                const newTopShowing = doc.getElementById('shop-showing-text');
                const topShowing = document.getElementById('shop-showing-text');
                if (topShowing && newTopShowing) {
                    topShowing.innerHTML = newTopShowing.innerHTML;
                }

                const newPag = doc.getElementById('shop-pagination-container');
                const pagContainer = document.getElementById('shop-pagination-container');
                if (pagContainer && newPag) {
                    pagContainer.innerHTML = newPag.innerHTML;
                }
            }

            const targetHistoryUrl = browserDisplayUrl || url;
            window.history.pushState({ path: targetHistoryUrl }, '', targetHistoryUrl);

            const prodColumn = document.getElementById('shop-products-column');
            if (prodColumn) {
                const rect = prodColumn.getBoundingClientRect();
                if (rect.top < 0) {
                    window.scrollTo({
                        top: window.scrollY + rect.top - 80,
                        behavior: 'smooth'
                    });
                }
            }
        })
        .catch(function(err) {
            console.error('AJAX shop error:', err);
            window.location.href = url;
        });
    }

    function applyAjaxShopFilter() {
        const sortFilterEl = document.getElementById('shopSortFilter');
        const sortVal = sortFilterEl ? sortFilterEl.value : 'latest';
        
        const currentBrowserUrl = new URL(window.location.href);
        currentBrowserUrl.searchParams.set('sort', sortVal);
        currentBrowserUrl.searchParams.set('page', '1');

        if (currentCategoryContext && currentCategoryContext !== '') {
            const apiRequestUrl = new URL(shopBaseRoute, window.location.origin);
            apiRequestUrl.searchParams.set('category', currentCategoryContext);
            apiRequestUrl.searchParams.set('sort', sortVal);
            apiRequestUrl.searchParams.set('page', '1');

            loadShopProductsAjax(apiRequestUrl.toString(), currentBrowserUrl.toString());
        } else {
            loadShopProductsAjax(currentBrowserUrl.toString(), currentBrowserUrl.toString());
        }
    }

    document.addEventListener('click', function(e) {
        const pageLink = e.target.closest('#shop-pagination-container .page-link');
        if (pageLink) {
            const href = pageLink.getAttribute('href');
            if (href && href !== '#' && !href.startsWith('javascript:')) {
                e.preventDefault();

                const clickedUrl = new URL(href, window.location.origin);
                const pageNum = clickedUrl.searchParams.get('page') || '1';
                const sortFilterEl = document.getElementById('shopSortFilter');
                const sortVal = sortFilterEl ? sortFilterEl.value : (clickedUrl.searchParams.get('sort') || 'latest');

                const currentBrowserUrl = new URL(window.location.href);
                currentBrowserUrl.searchParams.set('sort', sortVal);
                currentBrowserUrl.searchParams.set('page', pageNum);

                if (currentCategoryContext && currentCategoryContext !== '') {
                    const apiRequestUrl = new URL(shopBaseRoute, window.location.origin);
                    apiRequestUrl.searchParams.set('category', currentCategoryContext);
                    apiRequestUrl.searchParams.set('sort', sortVal);
                    apiRequestUrl.searchParams.set('page', pageNum);

                    loadShopProductsAjax(apiRequestUrl.toString(), currentBrowserUrl.toString());
                } else {
                    loadShopProductsAjax(currentBrowserUrl.toString(), currentBrowserUrl.toString());
                }
            }
        }
    });

    window.addEventListener('popstate', function() {
        applyAjaxShopFilter();
    });

    document.addEventListener('DOMContentLoaded', function() {
        if (currentCategoryContext && currentCategoryContext !== '') {
            const initialCount = document.querySelectorAll('#products-render-row .product-card-wrap').length;
            const topShowingText = document.getElementById('shop-showing-text')?.innerText || '';
            if (initialCount === 12 && topShowingText.includes('1 to 12')) {
                const currentBrowserUrl = new URL(window.location.href);
                const apiRequestUrl = new URL(shopBaseRoute, window.location.origin);
                apiRequestUrl.searchParams.set('category', currentCategoryContext);
                if (currentBrowserUrl.searchParams.get('sort')) {
                    apiRequestUrl.searchParams.set('sort', currentBrowserUrl.searchParams.get('sort'));
                }
                if (currentBrowserUrl.searchParams.get('page')) {
                    apiRequestUrl.searchParams.set('page', currentBrowserUrl.searchParams.get('page'));
                }
                loadShopProductsAjax(apiRequestUrl.toString(), currentBrowserUrl.toString());
            }
        }
    });
</script>
@endsection