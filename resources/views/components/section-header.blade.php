@props(['title', 'link' => null])

@php
    $finalLink = null;
    if ($link) {
        $finalLink = filter_var($link, FILTER_VALIDATE_URL) ? $link : url($link);
    }
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0 text-dark">{{ $title }}</h4>
    @if($finalLink)
        <a href="{{ $finalLink }}" class="text-primary text-decoration-none fw-semibold small">
            View All <i class="fas fa-arrow-right ms-1"></i>
        </a>
    @endif
</div>