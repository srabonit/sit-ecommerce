@props(['zone' => 'sidebar-right'])

@php
    // সেন্ট্রাল সেটিংস ক্যাশ রেজলভার
    static $widgetSettingsCache = null;
    if ($widgetSettingsCache === null) {
        $widgetSettingsCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
            return \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $wSettings = $widgetSettingsCache ?? [];

    // Dynamic Widget Scoped Colors
    $widgetCardBg       = $wSettings['color_page_sidebar_bg'] ?? '#ffffff';
    $widgetCardBorder   = $wSettings['color_page_sidebar_border'] ?? '#edf2f7';
    $widgetTitleColor   = $wSettings['color_page_sidebar_header_text'] ?? '#0f172a';
    $widgetTitleAccent  = $wSettings['color_hdr_menu_hover_text'] ?? ($wSettings['color_common_btn_bg'] ?? '#2563eb');
    
    $widgetSearchBtnBg  = $wSettings['color_blog_search_btn_bg'] ?? '#2563eb';
    $widgetSearchBtnTxt = $wSettings['color_blog_search_btn_text'] ?? '#ffffff';
    
    $widgetLinkColor    = $wSettings['color_page_sidebar_text'] ?? '#475569';
    $widgetLinkHover    = $wSettings['color_hdr_menu_hover_text'] ?? '#2563eb';
    $widgetLinkHoverBg  = $wSettings['color_hdr_menu_hover_bg'] ?? '#f8fafc';
    
    $widgetBadgeBg      = $wSettings['color_cat_card_bg'] ?? '#f1f5f9';
    $widgetBadgeText    = $wSettings['color_cat_card_count'] ?? '#64748b';

    $widgets = \Illuminate\Support\Facades\DB::table('site_widgets')
        ->where('widget_zone', $zone)
        ->where('is_active', 1)
        ->orderBy('order_index', 'asc')
        ->get();

    // Universal Dynamic Image Resolver for Widgets
    $resolveWidgetImg = function($path, $fallback = 'https://placehold.co/100x100?text=News') {
        if (empty($path)) return $fallback;
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        $clean = ltrim(str_replace(['storage/', 'public/'], '', $path), '/');
        if (file_exists(public_path($clean))) return asset($clean);
        if (file_exists(public_path('uploads/' . $clean))) return asset('uploads/' . $clean);
        return asset($clean);
    };
@endphp

@once
<style>
    :root {
        --widget-card-bg: {{ $widgetCardBg }};
        --widget-card-border: {{ $widgetCardBorder }};
        --widget-title-color: {{ $widgetTitleColor }};
        --widget-title-accent: {{ $widgetTitleAccent }};
        --widget-search-btn-bg: {{ $widgetSearchBtnBg }};
        --widget-search-btn-text: {{ $widgetSearchBtnTxt }};
        --widget-link-color: {{ $widgetLinkColor }};
        --widget-link-hover: {{ $widgetLinkHover }};
        --widget-link-hover-bg: {{ $widgetLinkHoverBg }};
        --widget-badge-bg: {{ $widgetBadgeBg }};
        --widget-badge-text: {{ $widgetBadgeText }};
    }

    .widget-item {
        background-color: var(--widget-card-bg) !important;
        border: 1px solid var(--widget-card-border) !important;
        transition: all 0.2s ease;
    }

    /* Strict Text Decoration None Across All Widget Links */
    .widget-item a,
    .widget-item a:hover,
    .widget-link-item,
    .widget-recent-title {
        text-decoration: none !important;
    }

    .widget-header-title {
        color: var(--widget-title-color) !important;
    }

    .widget-title-accent-border {
        border-bottom: 2px solid var(--widget-title-accent) !important;
    }

    .widget-search-btn {
        background-color: var(--widget-search-btn-bg) !important;
        border-color: var(--widget-search-btn-bg) !important;
        color: var(--widget-search-btn-text) !important;
        transition: all 0.2s ease;
    }
    .widget-search-btn:hover {
        filter: brightness(0.92);
        color: var(--widget-search-btn-text) !important;
    }

    .widget-link-item {
        color: var(--widget-link-color) !important;
        transition: all 0.2s ease;
    }
    .widget-link-item:hover {
        color: var(--widget-link-hover) !important;
        background-color: var(--widget-link-hover-bg) !important;
    }
    .widget-link-item i {
        color: var(--widget-title-accent) !important;
    }

    .widget-count-badge {
        background-color: var(--widget-badge-bg) !important;
        color: var(--widget-badge-text) !important;
        border: 1px solid var(--widget-card-border) !important;
    }

    .widget-recent-title {
        color: var(--widget-title-color) !important;
        transition: color 0.15s ease;
    }
    .widget-recent-title:hover {
        color: var(--widget-link-hover) !important;
    }

    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endonce

@if($widgets->count() > 0)
    <div class="widgets-stack d-flex flex-column gap-4">
        @foreach($widgets as $w)
            <div class="widget-item p-3 p-md-4 rounded-4 shadow-sm">
                @if(!empty($w->title) && $w->widget_type !== 'custom_html')
                    <h6 class="fw-bold mb-3 pb-2 border-bottom position-relative widget-header-title">
                        <span class="pb-2 position-absolute widget-title-accent-border" style="bottom:-1px; left:0;">
                            {{ $w->title }}
                        </span>
                    </h6>
                @endif

                {{-- 1. Search Box --}}
                @if($w->widget_type === 'search')
                    <form action="{{ route('blogs.index') }}" method="GET">
                        <div class="input-group">
                            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm rounded-start-pill ps-3" placeholder="Search news &amp; tips...">
                            <button class="btn widget-search-btn btn-sm rounded-end-pill px-3" type="submit"><i class="fas fa-search"></i></button>
                        </div>
                    </form>

                {{-- 2. Categories Widget --}}
                @elseif($w->widget_type === 'categories')
                    @php
                        $cats = \Illuminate\Support\Facades\DB::table('blog_posts')
                            ->select('category', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
                            ->where('status', 'published')
                            ->whereNotNull('category')
                            ->groupBy('category')
                            ->get();
                    @endphp
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                        @foreach($cats as $c)
                            <li>
                                <a href="{{ route('blogs.index', ['category' => $c->category]) }}" class="d-flex justify-content-between align-items-center text-decoration-none small py-1 px-2 rounded widget-link-item">
                                    <span><i class="fas fa-chevron-right me-2" style="font-size: 10px;"></i>{{ $c->category }}</span>
                                    <span class="badge rounded-pill widget-count-badge">{{ $c->count }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>

                {{-- 3. Recent Articles Widget --}}
                @elseif($w->widget_type === 'recent_posts')
                    @php
                        $recent = \Illuminate\Support\Facades\DB::table('blog_posts')
                            ->where('status', 'published')
                            ->latest('id')
                            ->take(4)
                            ->get();
                    @endphp
                    <div class="d-flex flex-column gap-3">
                        @foreach($recent as $r)
                            @php
                                $rImg = $resolveWidgetImg($r->featured_image);
                            @endphp
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $rImg }}" class="rounded-3 object-fit-cover flex-shrink-0" width="55" height="55" alt="{{ $r->title }}" loading="lazy">
                                <div>
                                    <small class="text-muted d-block" style="font-size: 11px;"><i class="far fa-clock me-1"></i>{{ \Carbon\Carbon::parse($r->created_at)->format('d M, Y') }}</small>
                                    <a href="{{ route('blogs.show', $r->slug) }}" class="text-decoration-none fw-semibold small lh-sm line-clamp-2 widget-recent-title">{{ Str::limit($r->title, 45) }}</a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                {{-- 4. Custom HTML / JS / Ad Banner Widget --}}
                @elseif($w->widget_type === 'custom_html')
                    <div class="custom-widget-raw">
                        {!! $w->content !!}
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endif