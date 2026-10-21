@props(['category'])

@php
    $catId = $category->id;
    $catSlug = $category->slug ?? $catId;

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

<a id="category-card-link-{{ $catId }}" href="{{ route('category.products', $catSlug) }}" class="text-decoration-none">
    <div id="category-card-box-{{ $catId }}" class="card h-100 border-0 bg-light text-center p-3 rounded-3 hover-shadow">
        <img id="category-card-img-{{ $catId }}" 
             src="{{ $catImg }}" 
             class="img-fluid mb-2 mx-auto object-fit-contain" 
             style="height: 50px; width: 50px;" 
             alt="{{ $category->name }}">
        <h6 id="category-card-name-{{ $catId }}" class="fw-bold text-dark mb-1 small text-truncate">{{ $category->name }}</h6>
        <small id="category-card-count-{{ $catId }}" class="text-muted" style="font-size: 11px;">{{ $category->products_count ?? 0 }} Items</small>
    </div>
</a>