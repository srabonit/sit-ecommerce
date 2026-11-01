@props(['category'])

@php
    $catId = $category->id;
    $catSlug = $category->slug ?? $catId;

    // সেন্ট্রাল গ্লোবাল সেটিংস ক্যাশ রেজলভার
    static $catCardSettingsCache = null;
    if ($catCardSettingsCache === null) {
        $catCardSettingsCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
            return \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $catSettings = $catCardSettingsCache ?? [];

    // Category Card Dynamic Colors
    $catCardBg       = $catSettings['color_cat_card_bg'] ?? '#f8fafc';
    $catCardBorder   = $catSettings['color_cat_card_border'] ?? '#edf2f7';
    $catCardBrdHov   = $catSettings['color_cat_card_border_hover'] ?? '#2563eb';
    $catCardTitle    = $catSettings['color_cat_card_title'] ?? '#0f172a';
    $catCardTitleHov = $catSettings['color_cat_card_title_hover'] ?? '#2563eb';
    $catCardCount    = $catSettings['color_cat_card_count'] ?? '#64748b';

    $catImg = 'https://placehold.co/100x100?text=' . urlencode($category->name);
    
    $rawImg = !empty($category->image) ? $category->image : (!empty($category->thumbnail) ? $category->thumbnail : null);

    if (!empty($rawImg)) {
        if (filter_var($rawImg, FILTER_VALIDATE_URL)) {
            $catImg = $rawImg;
        } else {
            $cleanCat = ltrim(str_replace('storage/', '', $rawImg), '/');
            if (file_exists(public_path($cleanCat))) {
                $catImg = asset($cleanCat);
            } elseif (file_exists(public_path('uploads/' . $cleanCat))) {
                $catImg = asset('uploads/' . $cleanCat);
            } else {
                $catImg = asset($cleanCat);
            }
        }
    }
@endphp

@once
<style>
    :root {
        --cat-card-bg: {{ $catCardBg }};
        --cat-card-border: {{ $catCardBorder }};
        --cat-card-border-hover: {{ $catCardBrdHov }};
        --cat-card-title: {{ $catCardTitle }};
        --cat-card-title-hover: {{ $catCardTitleHov }};
        --cat-card-count: {{ $catCardCount }};
    }

    .theme-category-card {
        background-color: var(--cat-card-bg) !important;
        border: 1px solid var(--cat-card-border) !important;
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease !important;
    }
    .theme-category-card:hover {
        border-color: var(--cat-card-border-hover) !important;
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.05);
    }
    .theme-category-title {
        color: var(--cat-card-title) !important;
        transition: color 0.15s ease;
    }
    .theme-category-card:hover .theme-category-title {
        color: var(--cat-card-title-hover) !important;
    }
    .theme-category-count {
        color: var(--cat-card-count) !important;
    }
</style>
@endonce

<a id="category-card-link-{{ $catId }}" href="{{ route('category.products', $catSlug) }}" class="text-decoration-none">
    <div id="category-card-box-{{ $catId }}" class="card h-100 text-center p-3 rounded-3 theme-category-card">
        <img id="category-card-img-{{ $catId }}" 
             src="{{ $catImg }}" 
             class="img-fluid mb-2 mx-auto object-fit-contain" 
             style="height: 50px; width: 50px;" 
             alt="{{ $category->name }}">
        <h6 id="category-card-name-{{ $catId }}" class="fw-bold mb-1 small text-truncate theme-category-title theme-product-title">{{ $category->name }}</h6>
        <small id="category-card-count-{{ $catId }}" class="theme-category-count" style="font-size: 11px;">{{ $category->products_count ?? 0 }} Items</small>
    </div>
</a>