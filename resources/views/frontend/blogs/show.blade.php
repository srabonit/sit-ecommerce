@extends('layouts.app')

@php
$siteBrandName = config('app.name', 'Shop Now BD');
$seoTitle = !empty($post->seo_title) ? $post->seo_title : ($post->title . ' - ' . $siteBrandName);
$seoDesc = !empty($post->seo_description) ? $post->seo_description : \Illuminate\Support\Str::limit(strip_tags($post->content ?? ''), 160);
$seoKeywords = !empty($post->seo_keywords) ? $post->seo_keywords : '';
$canonicalUrl = !empty($post->seo_canonical_url) ? $post->seo_canonical_url : url('/blog/' . $post->slug);

// সার্বজনীন ইমেজ রেজলভার
$resolveBlogImg = function ($path, $fallback = 'https://placehold.co/800x450?text=No+Image') {
    if (empty($path))
        return $fallback;
    if (filter_var($path, FILTER_VALIDATE_URL))
        return $path;
    $clean = ltrim(str_replace('storage/', '', $path), '/');
    if (file_exists(public_path($clean)))
        return asset($clean);
    if (file_exists(public_path('uploads/' . $clean)))
        return asset('uploads/' . $clean);
    return asset($clean);
};

$ogImage = !empty($post->featured_image) ? $resolveBlogImg($post->featured_image) : null;
$mainPostImg = !empty($post->featured_image) ? $resolveBlogImg($post->featured_image) : null;

// গ্লোবাল কালার সেটিংস ক্যাশ রেজলভার
static $blogSingleSettingsCache = null;
if ($blogSingleSettingsCache === null) {
    $blogSingleSettingsCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function () {
        return DB::table('settings')->pluck('value', 'key')->toArray();
    });
}
$globalSettings = $blogSingleSettingsCache ?? [];

// Single Blog Dynamic Color Resolvers
$blogPageBg = $globalSettings['color_blog_page_bg'] ?? ($globalSettings['color_site_bg'] ?? '#f8fafc');
$blogArticleCardBg = $globalSettings['color_blog_card_bg'] ?? '#ffffff';
$blogArticleCardBrd = $globalSettings['color_blog_card_border'] ?? '#e2e8f0';
$blogArticleTitle = $globalSettings['color_blog_title'] ?? '#0f172a';
$blogArticleBodyText = $globalSettings['color_blog_single_text'] ?? '#334155';

$blogBreadcrumbLink = $globalSettings['color_page_breadcrumb_link'] ?? '#64748b';
$blogBreadcrumbHover = $globalSettings['color_page_breadcrumb_hover'] ?? '#2563eb';
$blogBreadcrumbActive = $globalSettings['color_page_title'] ?? '#0f172a';

$blogMetaText = $globalSettings['color_blog_meta_text'] ?? '#64748b';
$blogMetaIcon = $globalSettings['color_blog_meta_icon'] ?? '#94a3b8';
$blogCatBadgeBg = $globalSettings['color_blog_hero_badge_bg'] ?? '#2563eb';
$blogCatBadgeText = $globalSettings['color_blog_hero_badge_text'] ?? '#ffffff';

// Sidebar Widgets Colors
$blogSidebarBg = $globalSettings['color_page_sidebar_bg'] ?? '#ffffff';
$blogSidebarBorder = $globalSettings['color_page_sidebar_border'] ?? '#e2e8f0';
$blogSidebarHeadText = $globalSettings['color_page_sidebar_header_text'] ?? '#0f172a';
$blogSidebarText = $globalSettings['color_page_sidebar_text'] ?? '#475569';

// Comments System Colors
$blogCommentBubbleBg = $globalSettings['color_blog_comment_bubble_bg'] ?? '#f1f5f9';
$blogCommentText = $globalSettings['color_blog_comment_text'] ?? '#1e293b';
$blogCommentBtnBg = $globalSettings['color_blog_comment_btn_bg'] ?? '#2563eb';
$blogCommentBtnText = $globalSettings['color_blog_comment_btn_text'] ?? '#ffffff';
$blogCommentActionTxt = $globalSettings['color_blog_meta_text'] ?? '#64748b';
$blogCommentActionHov = $globalSettings['color_blog_readmore'] ?? '#2563eb';

// Recommended Stories Colors
$blogRecCardBg = $globalSettings['color_blog_card_bg'] ?? '#ffffff';
$blogRecCardBorder = $globalSettings['color_blog_card_border'] ?? '#e2e8f0';
$blogRecTitle = $globalSettings['color_blog_title'] ?? '#0f172a';
$blogRecLink = $globalSettings['color_blog_readmore'] ?? '#2563eb';

$hasLeftSidebar = !empty($post->show_sidebar_left);
$hasRightSidebar = !empty($post->show_sidebar_right);

if ($hasLeftSidebar && $hasRightSidebar) {
    $layoutMode = 'both_sidebars';
} elseif ($hasLeftSidebar && !$hasRightSidebar) {
    $layoutMode = 'left_only';
} elseif (!$hasLeftSidebar && $hasRightSidebar) {
    $layoutMode = 'right_only';
} else {
    $layoutMode = 'no_sidebar';
}

$containerClass = 'container py-3 py-md-4 py-lg-5';
if (($post->page_width ?? '') === 'full-width') {
    $containerClass = 'container-fluid px-2 px-md-4 px-lg-5 py-3 py-md-4';
}

$leftWidgets = \Illuminate\Support\Facades\DB::table('site_widgets')
    ->where('widget_zone', 'sidebar-left')
    ->where('is_active', 1)
    ->orderBy('order_index', 'asc')
    ->get();

$rightWidgets = \Illuminate\Support\Facades\DB::table('site_widgets')
    ->where('widget_zone', 'sidebar-right')
    ->where('is_active', 1)
    ->orderBy('order_index', 'asc')
    ->get();

$sidebarCategories = \Illuminate\Support\Facades\DB::table('categories')->latest('id')->take(6)->get();
@endphp

@section('title', $seoTitle)
@section('meta_title', $seoTitle)
@section('meta_description', $seoDesc)
@section('meta_keywords', $seoKeywords)
@section('canonical', $canonicalUrl)
@section('og_image', $ogImage)

@section('content')
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --blog-single-page-bg: {{ $blogPageBg }};
            --blog-single-card-bg: {{ $blogArticleCardBg }};
            --blog-single-card-border: {{ $blogArticleCardBrd }};
            --blog-single-title: {{ $blogArticleTitle }};
            --blog-single-body-text: {{ $blogArticleBodyText }};

            --blog-single-breadcrumb-link: {{ $blogBreadcrumbLink }};
            --blog-single-breadcrumb-hover: {{ $blogBreadcrumbHover }};
            --blog-single-breadcrumb-active: {{ $blogBreadcrumbActive }};

            --blog-single-meta-text: {{ $blogMetaText }};
            --blog-single-meta-icon: {{ $blogMetaIcon }};
            --blog-single-badge-bg: {{ $blogCatBadgeBg }};
            --blog-single-badge-text: {{ $blogCatBadgeText }};

            --blog-single-sidebar-bg: {{ $blogSidebarBg }};
            --blog-single-sidebar-border: {{ $blogSidebarBorder }};
            --blog-single-sidebar-head: {{ $blogSidebarHeadText }};
            --blog-single-sidebar-text: {{ $blogSidebarText }};

            --blog-single-comment-bubble-bg: {{ $blogCommentBubbleBg }};
            --blog-single-comment-text: {{ $blogCommentText }};
            --blog-single-comment-btn-bg: {{ $blogCommentBtnBg }};
            --blog-single-comment-btn-text: {{ $blogCommentBtnText }};
            --blog-single-comment-act-text: {{ $blogCommentActionTxt }};
            --blog-single-comment-act-hover: {{ $blogCommentActionHov }};

            --blog-single-rec-card-bg: {{ $blogRecCardBg }};
            --blog-single-rec-card-border: {{ $blogRecCardBorder }};
            --blog-single-rec-title: {{ $blogRecTitle }};
            --blog-single-rec-link: {{ $blogRecLink }};
        }

        .single-blog-article-wrap { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: var(--blog-single-body-text) !important;
            background-color: var(--blog-single-page-bg) !important;
            width: 100%;
            overflow-x: hidden;
        }

        /* Responsive Breadcrumb */
        .sit-blog-breadcrumb {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            margin-bottom: 20px;
            line-height: 1.4;
        }
        .sit-blog-breadcrumb a {
            color: var(--blog-single-breadcrumb-link) !important;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .sit-blog-breadcrumb a:hover {
            color: var(--blog-single-breadcrumb-hover) !important;
        }
        .sit-blog-breadcrumb .sep {
            color: #cbd5e1;
            user-select: none;
        }
        .sit-blog-breadcrumb .active-title {
            color: var(--blog-single-breadcrumb-active) !important;
            font-weight: 600;
            max-width: 280px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        @media (min-width: 768px) { .sit-blog-breadcrumb .active-title { max-width: 480px; } }
        @media (min-width: 1200px) { .sit-blog-breadcrumb .active-title { max-width: 650px; } }

        /* Layout System (60% - 20% - 20%) */
        .blog-custom-layout-row { display: flex; flex-wrap: wrap; margin-left: -12px; margin-right: -12px; }
        .blog-layout-col { padding-left: 12px; padding-right: 12px; box-sizing: border-box; }

        @media (min-width: 992px) {
            .layout-both-left { flex: 0 0 20% !important; max-width: 20% !important; width: 20% !important; }
            .layout-both-center { flex: 0 0 60% !important; max-width: 60% !important; width: 60% !important; }
            .layout-both-right { flex: 0 0 20% !important; max-width: 20% !important; width: 20% !important; }

            .layout-leftonly-sidebar { flex: 0 0 20% !important; max-width: 20% !important; width: 20% !important; }
            .layout-leftonly-center { flex: 0 0 80% !important; max-width: 80% !important; width: 80% !important; }

            .layout-rightonly-center { flex: 0 0 80% !important; max-width: 80% !important; width: 80% !important; }
            .layout-rightonly-sidebar { flex: 0 0 20% !important; max-width: 20% !important; width: 20% !important; }

            .layout-full-center { flex: 0 0 100% !important; max-width: 100% !important; width: 100% !important; }
        }

        @media (max-width: 991.98px) {
            .layout-both-left, .layout-both-center, .layout-both-right,
            .layout-leftonly-sidebar, .layout-leftonly-center,
            .layout-rightonly-center, .layout-rightonly-sidebar,
            .layout-full-center {
                flex: 0 0 100% !important;
                max-width: 100% !important;
                width: 100% !important;
            }
            .layout-both-left, .layout-leftonly-sidebar { order: 2; margin-top: 24px; }
            .layout-both-right, .layout-rightonly-sidebar { order: 3; margin-top: 24px; }
            .layout-both-center, .layout-leftonly-center, .layout-rightonly-center, .layout-full-center { order: 1; }
        }

        .blog-article-card {
            background-color: var(--blog-single-card-bg) !important;
            border-radius: 16px;
            border: 1px solid var(--blog-single-card-border) !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            width: 100%;
            overflow: hidden;
        }
        .blog-rendered-title { 
            font-size: {{ !empty($post->title_font_size) ? $post->title_font_size : '30px' }} !important; 
            font-weight: 800; 
            color: var(--blog-single-title) !important; 
            line-height: 1.35; 
        }
        .single-blog-article { 
            font-size: 15.5px; 
            line-height: 1.8; 
            color: var(--blog-single-body-text) !important;
            word-wrap: break-word;
        }
        .single-blog-article img { max-width: 100% !important; height: auto !important; border-radius: 12px; margin: 1.2rem 0; }

        /* Sidebars */
        .blog-sidebar-widget {
            background-color: var(--blog-single-sidebar-bg) !important;
            border-radius: 12px;
            border: 1px solid var(--blog-single-sidebar-border) !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            margin-bottom: 16px;
            overflow: hidden;
        }
        .blog-widget-header {
            font-size: 13.5px;
            font-weight: 700;
            padding: 12px 14px;
            border-bottom: 1px solid var(--blog-single-sidebar-border) !important;
            color: var(--blog-single-sidebar-head) !important;
            background: #fcfdfe;
        }
        .blog-widget-body { 
            padding: 12px 14px; 
            font-size: 13px; 
            color: var(--blog-single-sidebar-text) !important; 
        }
        .blog-widget-body a {
            color: var(--blog-single-sidebar-text) !important;
            text-decoration: none;
            transition: color 0.15s ease;
        }
        .blog-widget-body a:hover {
            color: var(--blog-single-breadcrumb-hover) !important;
        }

        /* Fixed Gap Facebook Style Comments */
        .fb-comment-wrap {
            display: flex;
            gap: 14px !important;
            align-items: flex-start;
        }
        .fb-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background-color: var(--blog-single-comment-btn-bg) !important;
            color: var(--blog-single-comment-btn-text) !important;
            font-weight: 700;
            font-size: 13.5px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .fb-bubble {
            background-color: var(--blog-single-comment-bubble-bg) !important;
            color: var(--blog-single-comment-text) !important;
            border-radius: 18px;
            padding: 10px 16px;
            display: inline-block;
            max-width: 100%;
            word-break: break-word;
        }
        .fb-bubble strong {
            color: var(--blog-single-title) !important;
        }
        .fb-actions {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-left: 52px;
            margin-top: 4px;
            font-size: 11.5px;
            font-weight: 600;
            color: var(--blog-single-comment-act-text) !important;
        }
        .fb-actions a, .fb-actions button {
            color: var(--blog-single-comment-act-text) !important;
            text-decoration: none;
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
            font-weight: 600;
            font-size: 11.5px;
            transition: color 0.15s ease;
        }
        .fb-actions a:hover, .fb-actions button:hover {
            color: var(--blog-single-comment-act-hover) !important;
        }
        .reply-tree-indent {
            margin-left: 52px;
            border-left: 2px solid var(--blog-single-card-border);
            padding-left: 14px;
            margin-top: 10px;
        }

        /* Recommended Stories */
        .rec-post-card {
            border-radius: 12px;
            border: 1px solid var(--blog-single-rec-card-border) !important;
            background-color: var(--blog-single-rec-card-bg) !important;
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .rec-post-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        }
        .rec-thumb-box {
            width: 100%;
            height: 160px;
            overflow: hidden;
            background: #f1f5f9;
        }
        .rec-thumb-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .rec-post-card:hover .rec-thumb-box img {
            transform: scale(1.05);
        }
        .rec-post-title a {
            color: var(--blog-single-rec-title) !important;
            text-decoration: none;
            transition: color 0.15s ease;
        }
        .rec-post-title a:hover {
            color: var(--blog-single-rec-link) !important;
        }
        .rec-read-link {
            color: var(--blog-single-rec-link) !important;
            text-decoration: none;
            transition: color 0.15s ease;
        }

        /* Buttons */
        .btn-clean-primary {
            background-color: var(--blog-single-comment-btn-bg) !important;
            color: var(--blog-single-comment-btn-text) !important;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 12.5px;
            padding: 7px 20px;
            transition: all 0.2s ease;
        }
        .btn-clean-primary:hover {
            filter: brightness(0.92);
            color: var(--blog-single-comment-btn-text) !important;
            box-shadow: 0 3px 10px rgba(37, 99, 235, 0.25);
        }
        .btn-clean-outline {
            border: 1px solid var(--blog-single-card-border) !important;
            background: #ffffff;
            color: var(--blog-single-meta-text) !important;
            border-radius: 50px;
            font-weight: 600;
            font-size: 12px;
            padding: 5px 14px;
            transition: all 0.2s ease;
        }
        .btn-clean-outline:hover {
            border-color: var(--blog-single-comment-btn-bg) !important;
            color: var(--blog-single-comment-btn-bg) !important;
            background: #eff6ff;
        }

        @media (max-width: 767.98px) {
            .blog-article-card { padding: 14px !important; border-radius: 12px; }
            .blog-rendered-title { font-size: 21px !important; }
            .single-blog-article { font-size: 14px; line-height: 1.68; }
            .fb-avatar { width: 32px; height: 32px; font-size: 11.5px; }
            .fb-comment-wrap { gap: 10px !important; }
            .fb-actions { margin-left: 42px; gap: 10px; }
            .reply-tree-indent { margin-left: 22px; padding-left: 10px; }
        }

        #stickySingleProductBar, .sticky-single-product-bar {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }

        {!! $post->custom_css ?? '' !!}
    </style>

    <div class="{{ $containerClass }} single-blog-article-wrap">

        <!-- Responsive Breadcrumb -->
        <div class="sit-blog-breadcrumb">
            <a href="{{ route('home') }}"><i class="fas fa-home me-1"></i>Home</a>
            <span class="sep">/</span>
            <a href="{{ route('blogs.index') }}">Blog</a>
            <span class="sep">/</span>
            <span class="active-title" title="{{ $post->title }}">{{ $post->title }}</span>
        </div>

        <div class="blog-custom-layout-row">

            <!-- 1. LEFT SIDEBAR (20%) -->
            @if($hasLeftSidebar)
                <div class="blog-layout-col {{ $layoutMode === 'both_sidebars' ? 'layout-both-left' : 'layout-leftonly-sidebar' }}">
                    <div class="d-flex flex-column gap-3">
                        @forelse($leftWidgets as $wLeft)
                            <div class="blog-sidebar-widget">
                                <div class="blog-widget-header">{{ $wLeft->title }}</div>
                                <div class="blog-widget-body">
                                    {!! html_entity_decode($wLeft->content ?? '', ENT_QUOTES, 'UTF-8') !!}
                                </div>
                            </div>
                        @empty
                            <div class="blog-sidebar-widget">
                                <div class="blog-widget-header"><i class="fas fa-tags me-1" style="color: var(--blog-single-comment-btn-bg);"></i> Topics</div>
                                <div class="blog-widget-body p-0">
                                    <ul class="list-unstyled mb-0 small">
                                        @foreach($sidebarCategories as $catItem)
                                            <li class="py-2 px-3 border-bottom d-flex justify-content-between align-items-center" style="border-color: var(--blog-single-sidebar-border) !important;">
                                                <a href="{{ route('blogs.index', ['category' => $catItem->name]) }}" class="text-decoration-none">
                                                    {{ $catItem->name }}
                                                </a>
                                                <i class="fas fa-chevron-right text-muted" style="font-size: 9px;"></i>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif

            <!-- 2. MAIN CENTER CONTENT BODY -->
            @php
    $centerClass = match ($layoutMode) {
        'both_sidebars' => 'layout-both-center',
        'left_only' => 'layout-leftonly-center',
        'right_only' => 'layout-rightonly-center',
        default => 'layout-full-center',
    };
            @endphp

            <div class="blog-layout-col {{ $centerClass }}">
                <article class="blog-article-card p-3 p-md-4 p-lg-5 mb-4">
                    @if(!empty($post->category))
                        <span class="badge rounded-pill px-3 py-1 mb-2 fw-semibold" style="font-size: 11px; background-color: var(--blog-single-badge-bg); color: var(--blog-single-badge-text);">
                            {{ $post->category }}
                        </span>
                    @endif

                    @if(!isset($post->show_title) || $post->show_title == 1)
                        <h1 class="blog-rendered-title mb-3">{{ $post->title }}</h1>
                    @endif

                    <div class="d-flex align-items-center justify-content-between border-top border-bottom py-2 mb-3 small flex-wrap gap-2" style="border-color: var(--blog-single-card-border) !important;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 34px; height: 34px; font-size: 13px; background-color: var(--blog-single-comment-bubble-bg); color: var(--blog-single-comment-btn-bg);">
                                <i class="fas fa-feather-alt"></i>
                            </div>
                            <div>
                                <span class="d-block fw-bold" style="font-size: 12px; color: var(--blog-single-title);">{{ $post->author_name ?? 'Editorial Desk' }}</span>
                                <span style="font-size: 10.5px; color: var(--blog-single-meta-text);">Published: {{ \Carbon\Carbon::parse($post->created_at)->format('M d, Y') }}</span>
                                @if(!empty($post->updated_at) && $post->updated_at != $post->created_at)
                                    <span class="ms-1" style="font-size: 10px; color: var(--blog-single-comment-btn-bg);">(Updated: {{ \Carbon\Carbon::parse($post->updated_at)->format('M d, Y') }})</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <span class="badge bg-light border" style="font-size: 11px; color: var(--blog-single-meta-text); border-color: var(--blog-single-card-border) !important;">
                                <i class="far fa-eye me-1" style="color: var(--blog-single-meta-icon);"></i>{{ $post->views_count ?? 0 }} Reads
                            </span>
                        </div>
                    </div>

                    @if(!empty($mainPostImg) && (!isset($post->show_featured_image) || $post->show_featured_image == 1))
                        <div class="rounded-3 overflow-hidden mb-4 text-center">
                            <img src="{{ $mainPostImg }}" alt="{{ $post->title }}" class="w-100 object-fit-cover shadow-xs" style="max-height: 440px;" loading="lazy">
                        </div>
                    @endif

                    <div class="single-blog-article">
                        {!! \App\Services\ShortcodeParser::parse($post->content ?? '') !!}
                    </div>

                    <!-- Social Share Bar -->
                    <div class="d-flex align-items-center justify-content-between border-top pt-3 mt-4 flex-wrap gap-2" style="border-color: var(--blog-single-card-border) !important;">
                        <span class="fw-bold small" style="font-size: 12px; color: var(--blog-single-meta-text);">Share Story:</span>
                        <div class="d-flex gap-1.5">
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" class="btn btn-light btn-sm rounded-circle border text-primary" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;"><i class="fab fa-facebook-f small"></i></a>
                            <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}" target="_blank" class="btn btn-light btn-sm rounded-circle border text-info" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;"><i class="fab fa-twitter small"></i></a>
                            <a href="https://wa.me/?text={{ urlencode(url()->current()) }}" target="_blank" class="btn btn-light btn-sm rounded-circle border text-success" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;"><i class="fab fa-whatsapp small"></i></a>
                        </div>
                    </div>
                </article>

                <!-- ================= COLLAPSIBLE FACEBOOK STYLE COMMENTS ================= -->
                @if(!empty($post->enable_comments))
                    <div class="blog-article-card mb-4 overflow-hidden" id="blog-comments-container">
                        <div class="p-3 px-md-4 bg-light border-bottom d-flex justify-content-between align-items-center" style="cursor: pointer; border-color: var(--blog-single-card-border) !important;" data-bs-toggle="collapse" data-bs-target="#commentsCollapseBody" aria-expanded="true">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-comments" style="color: var(--blog-single-comment-btn-bg);"></i>
                                <h6 class="fw-bold mb-0 fs-6" style="color: var(--blog-single-title);">Discussion &amp; Feedback ({{ $comments->total() ?? 0 }})</h6>
                            </div>
                            <i class="fas fa-chevron-down text-muted small"></i>
                        </div>

                        <div class="collapse show" id="commentsCollapseBody">
                            <div class="p-3 p-md-4">
                                @auth
                                    <form id="commentSubmitForm" class="mb-4">
                                        @csrf
                                        <input type="hidden" name="parent_id" id="parentCommentId" value="">
                                        <div id="replyBadge" class="alert alert-info py-1 px-2.5 small d-none justify-content-between align-items-center mb-2" style="font-size: 11px;">
                                            <span id="replyBadgeText">Replying to comment...</span>
                                            <button type="button" class="btn-close py-0" style="font-size: 9px;" onclick="cancelReply()"></button>
                                        </div>
                                        <div class="fb-comment-wrap">
                                            <div class="fb-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                                            <div class="flex-grow-1">
                                                <textarea name="comment" id="commentText" class="form-control rounded-3" rows="2" placeholder="Write a comment..." required style="font-size: 13.5px;"></textarea>
                                                <div class="d-flex justify-content-end mt-2">
                                                    <button type="submit" id="btnSubmitComment" class="btn-clean-primary">
                                                        <i class="fas fa-paper-plane me-1"></i> Post
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                @else
                                    <div class="alert alert-light border rounded-3 p-2.5 text-center mb-4" style="border-color: var(--blog-single-card-border) !important;">
                                        <span class="small" style="color: var(--blog-single-meta-text);">Sign in to join the conversation.</span>
                                        <a href="{{ route('login') }}" class="btn-clean-outline ms-2">Sign In</a>
                                    </div>
                                @endauth

                                <div id="commentsTreeList" class="d-flex flex-column gap-3">
                                    @forelse($comments as $com)
                                        <div class="comment-item" id="comment-node-{{ $com->id }}">
                                            <div class="fb-comment-wrap">
                                                <div class="fb-avatar">{{ strtoupper(substr($com->user_name, 0, 1)) }}</div>
                                                <div>
                                                    <div class="fb-bubble">
                                                        <strong class="d-block" style="font-size: 12.5px;">{{ $com->user_name }}</strong>
                                                        <span id="comment-body-{{ $com->id }}" style="font-size: 13px;">{{ $com->comment }}</span>
                                                    </div>
                                                    <div class="fb-actions">
                                                        <span>{{ \Carbon\Carbon::parse($com->created_at)->diffForHumans() }}</span>
                                                        @if(!empty($com->is_edited))
                                                            <span style="font-size: 10px; opacity: 0.7;">(edited)</span>
                                                        @endif
                                                        @auth
                                                            <button type="button" onclick="prepareReply({{ $com->id }}, '{{ addslashes($com->user_name) }}')">Reply</button>
                                                            @if(auth()->id() == $com->user_id)
                                                                <button type="button" onclick="editMyComment({{ $com->id }})">Edit</button>
                                                                <button type="button" class="text-danger" onclick="confirmDeleteComment({{ $com->id }})">Delete</button>
                                                            @endif
                                                        @endauth
                                                        @if($com->status == 0)
                                                            <span class="badge bg-warning-subtle text-warning border px-1.5" style="font-size: 9px;">Pending Approval</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            @if(isset($replies[$com->id]))
                                                <div class="reply-tree-indent d-flex flex-column gap-2.5">
                                                    @foreach($replies[$com->id] as $rep)
                                                        <div class="comment-item" id="comment-node-{{ $rep->id }}">
                                                            <div class="fb-comment-wrap">
                                                                <div class="fb-avatar" style="width: 30px; height: 30px; font-size: 11px;">{{ strtoupper(substr($rep->user_name, 0, 1)) }}</div>
                                                                <div>
                                                                    <div class="fb-bubble bg-light">
                                                                        <strong class="d-block" style="font-size: 12px;">{{ $rep->user_name }}</strong>
                                                                        <span id="comment-body-{{ $rep->id }}" style="font-size: 12.5px;">{{ $rep->comment }}</span>
                                                                    </div>
                                                                    <div class="fb-actions" style="margin-left: 44px;">
                                                                        <span>{{ \Carbon\Carbon::parse($rep->created_at)->diffForHumans() }}</span>
                                                                        @if(!empty($rep->is_edited))
                                                                            <span style="font-size: 10px; opacity: 0.7;">(edited)</span>
                                                                        @endif
                                                                        @auth
                                                                            @if(auth()->id() == $rep->user_id)
                                                                                <button type="button" onclick="editMyComment({{ $rep->id }})">Edit</button>
                                                                                <button type="button" class="text-danger" onclick="confirmDeleteComment({{ $rep->id }})">Delete</button>
                                                                            @endif
                                                                        @endauth
                                                                        @if($rep->status == 0)
                                                                            <span class="badge bg-warning-subtle text-warning border px-1.5" style="font-size: 9px;">Pending Approval</span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                        <p class="small mb-0 text-center py-3" id="noCommentsMsg" style="color: var(--blog-single-meta-text);">No comments yet. Start the conversation!</p>
                                    @endforelse
                                </div>

                                @if(count($comments) > 0 && method_exists($comments, 'hasPages') && $comments->hasPages())
                                    <div class="mt-4 d-flex justify-content-center" id="ajaxCommentsPagination">
                                        {{ $comments->links('pagination::bootstrap-5') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- ================= RECOMMENDED STORIES ================= -->
                @if(isset($relatedPosts) && $relatedPosts->count() > 0)
                    <div class="mt-4 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0 fs-6 fs-md-5" style="color: var(--blog-single-title);">
                                <i class="fas fa-bookmark me-1" style="color: var(--blog-single-comment-btn-bg);"></i> Recommended Stories
                            </h5>
                            <a href="{{ route('blogs.index') }}" class="btn-clean-outline" style="font-size: 11px;">View All</a>
                        </div>

                        <div class="row row-cols-1 row-cols-sm-2 g-3">
                            @foreach($relatedPosts as $rel)
                                @php
            $relImg = $resolveBlogImg($rel->featured_image);
                                @endphp
                                <div class="col">
                                    <div class="rec-post-card h-100 d-flex flex-column">
                                        <a href="{{ route('blogs.show', $rel->slug) }}" class="rec-thumb-box d-block">
                                            <img src="{{ $relImg }}" alt="{{ $rel->title }}" loading="lazy">
                                        </a>
                                        <div class="p-3 d-flex flex-column flex-grow-1">
                                            <span class="small mb-1" style="font-size: 10.5px; color: var(--blog-single-meta-text);">
                                                <i class="far fa-calendar-alt me-1" style="color: var(--blog-single-meta-icon);"></i>{{ \Carbon\Carbon::parse($rel->created_at)->format('M d, Y') }}
                                            </span>
                                            <h6 class="fw-bold mb-2 rec-post-title" style="font-size: 13px; line-height: 1.4;">
                                                <a href="{{ route('blogs.show', $rel->slug) }}">
                                                    {{ Str::limit($rel->title, 55) }}
                                                </a>
                                            </h6>
                                            <a href="{{ route('blogs.show', $rel->slug) }}" class="rec-read-link small fw-semibold mt-auto" style="font-size: 11.5px;">
                                                Read Story &rarr;
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- 3. RIGHT SIDEBAR (20%) -->
            @if($hasRightSidebar)
                <div class="blog-layout-col {{ $layoutMode === 'both_sidebars' ? 'layout-both-right' : 'layout-rightonly-sidebar' }}">
                    <div class="d-flex flex-column gap-3">
                        @forelse($rightWidgets as $wRight)
                            <div class="blog-sidebar-widget">
                                <div class="blog-widget-header">{{ $wRight->title }}</div>
                                <div class="blog-widget-body">
                                    {!! html_entity_decode($wRight->content ?? '', ENT_QUOTES, 'UTF-8') !!}
                                </div>
                            </div>
                        @empty
                            <div class="blog-sidebar-widget">
                                <div class="blog-widget-header"><i class="fas fa-fire text-danger me-1"></i> Popular Categories</div>
                                <div class="blog-widget-body p-0">
                                    <ul class="list-unstyled mb-0 small">
                                        @foreach($sidebarCategories as $catRight)
                                            <li class="py-2 px-3 border-bottom d-flex justify-content-between align-items-center" style="border-color: var(--blog-single-sidebar-border) !important;">
                                                <a href="{{ route('category.products', $catRight->slug ?? $catRight->id) }}" class="text-decoration-none">
                                                    {{ $catRight->name }}
                                                </a>
                                                <span class="badge bg-light text-muted border px-1.5 py-0.5" style="font-size: 9.5px;">Explore</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif

        </div>
    </div>

    <div class="modal fade" id="editCommentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-bottom py-2.5 px-3 bg-light">
                    <h6 class="modal-title fw-bold text-dark mb-0 fs-6">Edit Your Comment</h6>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3">
                    <input type="hidden" id="editCommentId" value="">
                    <textarea id="editCommentText" class="form-control rounded-3" rows="3" style="font-size: 13.5px;" required></textarea>
                </div>
                <div class="modal-footer border-top py-2 px-3">
                    <button type="button" class="btn btn-light btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold" style="background-color: var(--blog-single-comment-btn-bg); border-color: var(--blog-single-comment-btn-bg);" onclick="submitCommentUpdate()">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteCommentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden text-center p-4">
                <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 56px; height: 56px;">
                    <i class="fas fa-trash-alt fs-4"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">Delete Comment?</h5>
                <p class="text-muted small mb-4">Are you sure you want to remove this comment and its replies?</p>
                <input type="hidden" id="targetDeleteCommentId" value="">
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-semibold border" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger rounded-pill px-4 fw-bold" onclick="executeCommentDelete()">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="clientAlertModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
            <div class="modal-content border-0 shadow-lg rounded-4 text-center p-4">
                <div id="alertModalIconWrap" class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 56px; height: 56px;">
                    <i id="alertModalIcon" class="fas fa-check-circle fs-3"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1" id="alertModalTitle">Notice</h5>
                <p class="text-muted small mb-3" id="alertModalMessage"></p>
                <div>
                    <button type="button" class="btn btn-dark btn-sm rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showModalNotice(title, message, isSuccess = true) {
            document.getElementById('alertModalTitle').innerText = title;
            document.getElementById('alertModalMessage').innerText = message;
            const iconWrap = document.getElementById('alertModalIconWrap');
            const icon = document.getElementById('alertModalIcon');
            if (isSuccess) {
                iconWrap.className = 'rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mx-auto mb-3';
                icon.className = 'fas fa-check-circle fs-3';
            } else {
                iconWrap.className = 'rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-3';
                icon.className = 'fas fa-exclamation-triangle fs-3';
            }
            new bootstrap.Modal(document.getElementById('clientAlertModal')).show();
        }

        document.addEventListener('DOMContentLoaded', function () {
            const comForm = document.getElementById('commentSubmitForm');
            if (comForm) {
                comForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const btn = document.getElementById('btnSubmitComment');
                    const commentInput = document.getElementById('commentText');

                    if (!commentInput.value.trim()) return;

                    btn.disabled = true;
                    const originalText = btn.innerHTML;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Submitting...';
                    commentInput.disabled = true;

                    fetch("{{ route('blogs.comment', $post->slug) }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            comment: commentInput.value,
                            parent_id: document.getElementById('parentCommentId').value
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        btn.disabled = false;
                        commentInput.disabled = false;
                        btn.innerHTML = originalText;

                        if (data.status === 'success') {
                            showModalNotice('Success', data.message, true);
                            commentInput.value = '';
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            showModalNotice('Error', data.message || 'Error submitting comment.', false);
                        }
                    })
                    .catch(() => {
                        btn.disabled = false;
                        commentInput.disabled = false;
                        btn.innerHTML = originalText;
                        showModalNotice('Error', 'Submission failed.', false);
                    });
                });
            }
        });

        function prepareReply(parentId, userName) {
            document.getElementById('parentCommentId').value = parentId;
            const badge = document.getElementById('replyBadge');
            document.getElementById('replyBadgeText').innerText = 'Replying to ' + userName;
            badge.classList.remove('d-none');
            badge.classList.add('d-flex');
            document.getElementById('commentText').focus();
        }

        function cancelReply() {
            document.getElementById('parentCommentId').value = '';
            const badge = document.getElementById('replyBadge');
            badge.classList.add('d-none');
            badge.classList.remove('d-flex');
        }

        function editMyComment(id) {
            const text = document.getElementById('comment-body-' + id).innerText;
            document.getElementById('editCommentId').value = id;
            document.getElementById('editCommentText').value = text;
            new bootstrap.Modal(document.getElementById('editCommentModal')).show();
        }

        function submitCommentUpdate() {
            const id = document.getElementById('editCommentId').value;
            const comment = document.getElementById('editCommentText').value;

            fetch("{{ url('/blog/comment/update') }}/" + id, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ comment: comment })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    bootstrap.Modal.getInstance(document.getElementById('editCommentModal'))?.hide();
                    showModalNotice('Success', data.message, true);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showModalNotice('Error', data.message || 'Update failed.', false);
                }
            });
        }

        function confirmDeleteComment(id) {
            document.getElementById('targetDeleteCommentId').value = id;
            new bootstrap.Modal(document.getElementById('deleteCommentModal')).show();
        }

        function executeCommentDelete() {
            const id = document.getElementById('targetDeleteCommentId').value;
            fetch("{{ url('/blog/comment/delete') }}/" + id, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                bootstrap.Modal.getInstance(document.getElementById('deleteCommentModal'))?.hide();
                if (data.status === 'success') {
                    showModalNotice('Deleted', data.message, true);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showModalNotice('Error', data.message || 'Deletion failed.', false);
                }
            });
        }

        {!! $post->custom_js ?? '' !!}
    </script>
@endsection