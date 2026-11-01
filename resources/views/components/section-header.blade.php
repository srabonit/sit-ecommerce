@props(['title', 'link' => null])

@php
    $finalLink = null;
    if ($link) {
        $finalLink = filter_var($link, FILTER_VALIDATE_URL) ? $link : url($link);
    }

    // গ্লোবাল সেটিংস ক্যাশ রেজলভার
    static $secHeaderSettingsCache = null;
    if ($secHeaderSettingsCache === null) {
        $secHeaderSettingsCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
            return \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $secSettings = $secHeaderSettingsCache ?? [];

    // Section Header Dynamic Color Resolvers
    $secTitleColor = $secSettings['color_section_title'] ?? ($secSettings['color_page_title'] ?? '#0f172a');
    $secLinkColor  = $secSettings['color_section_link'] ?? ($secSettings['color_hdr_menu_hover_text'] ?? '#2563eb');
    $secLinkHover  = $secSettings['color_section_link_hover'] ?? '#1d4ed8';
@endphp

@once
<style>
    :root {
        --sec-head-title-color: {{ $secTitleColor }};
        --sec-head-link-color: {{ $secLinkColor }};
        --sec-head-link-hover: {{ $secLinkHover }};
    }

    .theme-section-heading-text {
        color: var(--sec-head-title-color) !important;
    }

    a.theme-section-view-all,
    .theme-section-view-all {
        color: var(--sec-head-link-color) !important;
        text-decoration: none !important;
        transition: color 0.2s ease, transform 0.2s ease;
        display: inline-flex;
        align-items: center;
    }

    a.theme-section-view-all:hover,
    .theme-section-view-all:hover {
        color: var(--sec-head-link-hover) !important;
        text-decoration: none !important;
        transform: translateX(2px);
    }
</style>
@endonce

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0 theme-section-heading-text theme-section-title">{{ $title }}</h4>
    @if($finalLink)
        <a href="{{ $finalLink }}" class="theme-section-view-all text-decoration-none fw-semibold small">
            View All <i class="fas fa-arrow-right ms-1"></i>
        </a>
    @endif
</div>