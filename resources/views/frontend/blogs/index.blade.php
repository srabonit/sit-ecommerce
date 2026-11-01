@extends('layouts.app')

@section('title', 'Stories & Articles - ' . config('app.name', 'Shop Now BD'))

@php
    // সার্বজনীন ব্লগ ইমেজ রেজলভার (লোকাল ও লাইভ উভয়ের জন্য)
    $resolveBlogImg = function($path, $fallback = 'https://placehold.co/600x400?text=No+Image') {
        if (empty($path)) return $fallback;
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        $clean = ltrim(str_replace('storage/', '', $path), '/');
        if (file_exists(public_path($clean))) return asset($clean);
        if (file_exists(public_path('uploads/' . $clean))) return asset('uploads/' . $clean);
        return asset($clean);
    };

    // গ্লোবাল সেটিংস ক্যাশ রেজলভার
    static $blogSettingsCache = null;
    if ($blogSettingsCache === null) {
        $blogSettingsCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
            return DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $globalSettings = $blogSettingsCache ?? [];

    // Blog Dynamic Colors Resolvers
    $blogPageBg       = $globalSettings['color_blog_page_bg'] ?? ($globalSettings['color_site_bg'] ?? '#f8fafc');
    $blogHeroBg       = $globalSettings['color_blog_hero_bg'] ?? '#f8fafc';
    $blogHeroBorder   = $globalSettings['color_blog_hero_border'] ?? '#e2e8f0';
    $blogHeroBadgeBg  = $globalSettings['color_blog_hero_badge_bg'] ?? '#2563eb';
    $blogHeroBadgeTxt = $globalSettings['color_blog_hero_badge_text'] ?? '#ffffff';
    $blogHeroTitle    = $globalSettings['color_blog_hero_title'] ?? '#0f172a';
    $blogHeroDesc     = $globalSettings['color_blog_hero_desc'] ?? '#64748b';

    $blogSearchBg     = $globalSettings['color_blog_search_bg'] ?? '#ffffff';
    $blogSearchText   = $globalSettings['color_blog_search_text'] ?? '#0f172a';
    $blogSearchBorder = $globalSettings['color_blog_search_border'] ?? '#cbd5e1';
    $blogSearchBtnBg  = $globalSettings['color_blog_search_btn_bg'] ?? '#2563eb';
    $blogSearchBtnTxt = $globalSettings['color_blog_search_btn_text'] ?? '#ffffff';

    $blogChipBg       = $globalSettings['color_blog_chip_bg'] ?? '#ffffff';
    $blogChipText     = $globalSettings['color_blog_chip_text'] ?? '#475569';
    $blogChipBorder   = $globalSettings['color_blog_chip_border'] ?? '#e2e8f0';
    $blogChipActBg    = $globalSettings['color_blog_chip_active_bg'] ?? '#2563eb';
    $blogChipActTxt   = $globalSettings['color_blog_chip_active_text'] ?? '#ffffff';
    $blogChipHovBg    = $globalSettings['color_blog_chip_hover_bg'] ?? '#eff6ff';
    $blogChipHovTxt   = $globalSettings['color_blog_chip_hover_text'] ?? '#2563eb';

    $blogCardBg       = $globalSettings['color_blog_card_bg'] ?? '#ffffff';
    $blogCardBorder   = $globalSettings['color_blog_card_border'] ?? '#edf2f7';
    $blogCardHoverBrd = $globalSettings['color_blog_card_hover_border'] ?? '#cbd5e1';
    $blogBadgeBg      = $globalSettings['color_blog_badge_bg'] ?? 'rgba(15, 23, 42, 0.75)';
    $blogBadgeText    = $globalSettings['color_blog_badge_text'] ?? '#ffffff';

    $blogMetaText     = $globalSettings['color_blog_meta_text'] ?? '#64748b';
    $blogMetaIcon     = $globalSettings['color_blog_meta_icon'] ?? '#94a3b8';
    $blogTitle        = $globalSettings['color_blog_title'] ?? '#0f172a';
    $blogTitleHover   = $globalSettings['color_blog_title_hover'] ?? '#2563eb';
    $blogExcerpt      = $globalSettings['color_blog_excerpt'] ?? '#64748b';
    $blogDivider      = $globalSettings['color_blog_divider'] ?? '#f1f5f9';
    $blogReadMore     = $globalSettings['color_blog_readmore'] ?? '#2563eb';
    $blogReadMoreHov  = $globalSettings['color_blog_readmore_hover'] ?? '#1d4ed8';

    $blogPagActiveBg  = $globalSettings['color_blog_pag_active_bg'] ?? '#2563eb';
    $blogPagActiveTxt = $globalSettings['color_blog_pag_active_text'] ?? '#ffffff';
    $blogPagBtnBg     = $globalSettings['color_blog_pag_btn_bg'] ?? '#ffffff';
    $blogPagBtnTxt    = $globalSettings['color_blog_pag_btn_text'] ?? '#334155';
    $blogPagBtnBorder = $globalSettings['color_blog_pag_btn_border'] ?? '#e2e8f0';
@endphp

@section('content')
<style>
    :root {
        --blog-page-bg: {{ $blogPageBg }};
        --blog-hero-bg: {{ $blogHeroBg }};
        --blog-hero-border: {{ $blogHeroBorder }};
        --blog-hero-badge-bg: {{ $blogHeroBadgeBg }};
        --blog-hero-badge-text: {{ $blogHeroBadgeTxt }};
        --blog-hero-title: {{ $blogHeroTitle }};
        --blog-hero-desc: {{ $blogHeroDesc }};

        --blog-search-bg: {{ $blogSearchBg }};
        --blog-search-text: {{ $blogSearchText }};
        --blog-search-border: {{ $blogSearchBorder }};
        --blog-search-btn-bg: {{ $blogSearchBtnBg }};
        --blog-search-btn-text: {{ $blogSearchBtnTxt }};

        --blog-chip-bg: {{ $blogChipBg }};
        --blog-chip-text: {{ $blogChipText }};
        --blog-chip-border: {{ $blogChipBorder }};
        --blog-chip-active-bg: {{ $blogChipActBg }};
        --blog-chip-active-text: {{ $blogChipActTxt }};
        --blog-chip-hover-bg: {{ $blogChipHovBg }};
        --blog-chip-hover-text: {{ $blogChipHovTxt }};

        --blog-card-bg: {{ $blogCardBg }};
        --blog-card-border: {{ $blogCardBorder }};
        --blog-card-hover-border: {{ $blogCardHoverBrd }};
        --blog-badge-bg: {{ $blogBadgeBg }};
        --blog-badge-text: {{ $blogBadgeText }};

        --blog-meta-text: {{ $blogMetaText }};
        --blog-meta-icon: {{ $blogMetaIcon }};
        --blog-title: {{ $blogTitle }};
        --blog-title-hover: {{ $blogTitleHover }};
        --blog-excerpt: {{ $blogExcerpt }};
        --blog-divider: {{ $blogDivider }};
        --blog-readmore: {{ $blogReadMore }};
        --blog-readmore-hover: {{ $blogReadMoreHov }};

        --blog-pag-active-bg: {{ $blogPagActiveBg }};
        --blog-pag-active-text: {{ $blogPagActiveTxt }};
        --blog-pag-btn-bg: {{ $blogPagBtnBg }};
        --blog-pag-btn-text: {{ $blogPagBtnTxt }};
        --blog-pag-btn-border: {{ $blogPagBtnBorder }};
    }

    #blog-page-root {
        background-color: var(--blog-page-bg) !important;
        min-height: 80vh;
    }

    .blog-hero-banner {
        background-color: var(--blog-hero-bg) !important;
        border-bottom: 1px solid var(--blog-hero-border) !important;
    }
    .blog-hero-badge {
        background-color: var(--blog-hero-badge-bg) !important;
        color: var(--blog-hero-badge-text) !important;
    }
    .blog-hero-title {
        color: var(--blog-hero-title) !important;
    }
    .blog-hero-desc {
        color: var(--blog-hero-desc) !important;
    }

    .blog-search-input {
        background-color: var(--blog-search-bg) !important;
        color: var(--blog-search-text) !important;
        border: 1px solid var(--blog-search-border) !important;
    }
    .blog-search-input:focus {
        border-color: var(--blog-search-btn-bg) !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
    }
    .blog-search-btn {
        background-color: var(--blog-search-btn-bg) !important;
        color: var(--blog-search-btn-text) !important;
        border: none !important;
        transition: all 0.2s ease;
    }
    .blog-search-btn:hover {
        filter: brightness(0.92);
        color: var(--blog-search-btn-text) !important;
    }

    /* Category Filter Chips */
    .blog-filter-chip {
        background-color: var(--blog-chip-bg) !important;
        color: var(--blog-chip-text) !important;
        border: 1px solid var(--blog-chip-border) !important;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .blog-filter-chip:hover {
        background-color: var(--blog-chip-hover-bg) !important;
        color: var(--blog-chip-hover-text) !important;
        border-color: var(--blog-chip-hover-text) !important;
    }
    .blog-filter-chip.active {
        background-color: var(--blog-chip-active-bg) !important;
        color: var(--blog-chip-active-text) !important;
        border-color: var(--blog-chip-active-bg) !important;
    }

    /* Blog Card Structure */
    .blog-card {
        background-color: var(--blog-card-bg) !important;
        border: 1px solid var(--blog-card-border) !important;
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    }
    .blog-card:hover {
        transform: translateY(-4px);
        border-color: var(--blog-card-hover-border) !important;
        box-shadow: 0 12px 24px rgba(0,0,0,0.08) !important;
    }
    .blog-img-box {
        height: 210px;
        overflow: hidden;
        background: #f1f5f9;
    }
    .blog-img-box img {
        transition: transform 0.4s ease;
    }
    .blog-card:hover .blog-img-box img {
        transform: scale(1.05);
    }
    .blog-card-badge {
        background-color: var(--blog-badge-bg) !important;
        color: var(--blog-badge-text) !important;
    }

    .blog-meta-item {
        color: var(--blog-meta-text) !important;
    }
    .blog-meta-item i {
        color: var(--blog-meta-icon) !important;
    }

    .blog-card-title a {
        color: var(--blog-title) !important;
        text-decoration: none;
        transition: color 0.2s ease;
    }
    .blog-card-title a:hover {
        color: var(--blog-title-hover) !important;
    }

    .blog-card-excerpt {
        color: var(--blog-excerpt) !important;
    }

    .blog-card-divider {
        border-top: 1px solid var(--blog-divider) !important;
    }

    .blog-readmore-btn {
        color: var(--blog-readmore) !important;
        text-decoration: none;
        font-weight: 700;
        transition: all 0.2s ease;
    }
    .blog-readmore-btn:hover {
        color: var(--blog-readmore-hover) !important;
        transform: translateX(2px);
    }

    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Blog Pagination Styles */
    .blog-pagination-wrapper .page-link {
        border-radius: 8px !important;
        border: 1px solid var(--blog-pag-btn-border) !important;
        color: var(--blog-pag-btn-text) !important;
        background-color: var(--blog-pag-btn-bg) !important;
        padding: 6px 12px;
        font-weight: 600;
        font-size: 13px;
        margin: 0 2px;
        transition: all 0.2s ease;
    }
    .blog-pagination-wrapper .page-item.active .page-link {
        background-color: var(--blog-pag-active-bg) !important;
        border-color: var(--blog-pag-active-bg) !important;
        color: var(--blog-pag-active-text) !important;
        box-shadow: 0 3px 8px rgba(37, 99, 235, 0.25);
    }
    .blog-pagination-wrapper .page-link:hover {
        background-color: var(--blog-chip-hover-bg) !important;
        color: var(--blog-chip-hover-text) !important;
    }
</style>

<div id="blog-page-root">

    <!-- Hero / Search Banner -->
    <div class="blog-hero-banner py-4 py-md-5 mb-4 mb-md-5">
        <div class="container">
            <div class="row align-items-center g-3">
                <div class="col-md-7">
                    <span class="badge blog-hero-badge rounded-pill px-3 py-1 small mb-2 text-uppercase tracking-wider">Our Official Blog</span>
                    <h1 class="display-6 fw-bold blog-hero-title mb-2">Trends, Guides &amp; Lifestyle Stories</h1>
                    <p class="blog-hero-desc mb-0">Discover tips, style guides, and insider news straight from our team.</p>
                </div>
                <div class="col-md-5">
                    <form action="{{ route('blogs.index') }}" method="GET" class="d-flex gap-2">
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control rounded-pill shadow-sm blog-search-input px-3" placeholder="Search by topic or keyword...">
                        <button type="submit" class="btn blog-search-btn rounded-pill px-4 shadow-sm"><i class="fas fa-search"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Container -->
    <div class="container pb-5">
        <div class="row g-4">
            
            <!-- Main Content Area -->
            <div class="col-lg-8">
                
                <!-- Category Chips Filter -->
                @if(isset($categories) && $categories->count() > 0)
                    <div class="d-flex gap-2 flex-wrap mb-4 pb-2 border-bottom" style="border-color: var(--blog-divider) !important;">
                        <a href="{{ route('blogs.index') }}" class="btn btn-sm rounded-pill px-3 blog-filter-chip {{ !request('category') ? 'active shadow-sm' : '' }}">All Stories</a>
                        @foreach($categories as $cat)
                            <a href="{{ route('blogs.index', ['category' => $cat]) }}" class="btn btn-sm rounded-pill px-3 blog-filter-chip {{ request('category') === $cat ? 'active shadow-sm' : '' }}">{{ $cat }}</a>
                        @endforeach
                    </div>
                @endif

                <!-- Blog Posts Grid -->
                <div class="row g-4">
                    @forelse($posts as $post)
                        @php
                            $postImg = $resolveBlogImg($post->featured_image);
                        @endphp
                        <div class="col-md-6">
                            <div class="card h-100 rounded-4 shadow-sm overflow-hidden blog-card d-flex flex-column">
                                <div class="blog-img-box position-relative">
                                    <a href="{{ route('blogs.show', $post->slug) }}">
                                        <img src="{{ $postImg }}" alt="{{ $post->title }}" class="w-100 h-100 object-fit-cover" loading="lazy">
                                    </a>
                                    @if(!empty($post->category))
                                        <span class="position-absolute top-0 start-0 m-3 badge blog-card-badge rounded-pill px-3 py-1 small">{{ $post->category }}</span>
                                    @endif
                                </div>

                                <div class="card-body p-4 d-flex flex-column flex-grow-1">
                                    <div class="d-flex align-items-center gap-3 small mb-2" style="font-size: 12px;">
                                        <span class="blog-meta-item"><i class="far fa-calendar-alt me-1"></i>{{ \Carbon\Carbon::parse($post->created_at)->format('M d, Y') }}</span>
                                        <span class="blog-meta-item"><i class="far fa-eye me-1"></i>{{ $post->views_count ?? 0 }} views</span>
                                    </div>

                                    <h5 class="fw-bold mb-2 blog-card-title">
                                        <a href="{{ route('blogs.show', $post->slug) }}" class="lh-base line-clamp-2">{{ $post->title }}</a>
                                    </h5>

                                    <p class="blog-card-excerpt small line-clamp-2 mb-4 flex-grow-1">
                                        {{ $post->excerpt ?? Str::limit(strip_tags($post->content), 100) }}
                                    </p>

                                    <div class="blog-card-divider pt-3 d-flex justify-content-between align-items-center mt-auto">
                                        <span class="blog-meta-item small"><i class="far fa-clock me-1"></i>3 min read</span>
                                        <a href="{{ route('blogs.show', $post->slug) }}" class="blog-readmore-btn small d-inline-flex align-items-center">
                                            Read Story <i class="fas fa-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 py-5 text-center">
                            <div class="bg-white p-5 rounded-4 shadow-sm border" style="border-color: var(--blog-card-border) !important;">
                                <i class="far fa-newspaper fa-3x mb-3 opacity-50" style="color: var(--blog-meta-icon);"></i>
                                <h4 style="color: var(--blog-title);">No stories found</h4>
                                <p class="small mb-3" style="color: var(--blog-excerpt);">Try searching with other keywords or clear filter parameters.</p>
                                <a href="{{ route('blogs.index') }}" class="btn btn-sm rounded-pill px-4 blog-filter-chip active">Browse All Articles</a>
                            </div>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if(method_exists($posts, 'hasPages') && $posts->hasPages())
                    <div class="mt-5 d-flex justify-content-center blog-pagination-wrapper">
                        {{ $posts->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>

            <!-- Dynamic Right Sidebar Widgets -->
            <div class="col-lg-4">
                <x-widget-area zone="sidebar-right" />
            </div>
        </div>
    </div>

</div>
@endsection