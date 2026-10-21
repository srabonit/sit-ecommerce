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
@endphp

@section('content')
<style>
    .blog-card {
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .blog-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.08) !important;
    }
    .blog-img-box {
        height: 210px;
        overflow: hidden;
    }
    .blog-img-box img {
        transition: transform 0.4s ease;
    }
    .blog-card:hover .blog-img-box img {
        transform: scale(1.05);
    }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>

<div class="bg-light border-bottom py-4 py-md-5 mb-4 mb-md-5">
    <div class="container">
        <div class="row align-items-center g-3">
            <div class="col-md-7">
                <span class="badge bg-primary text-white rounded-pill px-3 py-1 small mb-2 text-uppercase tracking-wider">Our Official Blog</span>
                <h1 class="display-6 fw-bold text-dark mb-2">Trends, Guides &amp; Lifestyle Stories</h1>
                <p class="text-muted mb-0">Discover tips, style guides, and insider news straight from our team.</p>
            </div>
            <div class="col-md-5">
                <form action="{{ route('blogs.index') }}" method="GET" class="d-flex gap-2">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control rounded-pill shadow-sm" placeholder="Search by topic or keyword...">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm"><i class="fas fa-search"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <div class="row g-4">
        <!-- Main Content Area -->
        <div class="col-lg-8">
            <!-- Category Chips Carousel -->
            @if(isset($categories) && $categories->count() > 0)
                <div class="d-flex gap-2 flex-wrap mb-4 pb-2 border-bottom">
                    <a href="{{ route('blogs.index') }}" class="btn btn-sm rounded-pill px-3 {{ !request('category') ? 'btn-primary shadow-sm' : 'btn-white border text-dark' }}">All Stories</a>
                    @foreach($categories as $cat)
                        <a href="{{ route('blogs.index', ['category' => $cat]) }}" class="btn btn-sm rounded-pill px-3 {{ request('category') === $cat ? 'btn-primary shadow-sm' : 'btn-white border text-secondary' }}">{{ $cat }}</a>
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
                        <div class="card h-100 border-0 rounded-4 shadow-sm overflow-hidden blog-card bg-white d-flex flex-column">
                            <div class="blog-img-box position-relative">
                                <a href="{{ route('blogs.show', $post->slug) }}">
                                    <img src="{{ $postImg }}" alt="{{ $post->title }}" class="w-100 h-100 object-fit-cover" loading="lazy">
                                </a>
                                @if(!empty($post->category))
                                    <span class="position-absolute top-0 start-0 m-3 badge bg-dark bg-opacity-75 backdrop-blur text-white rounded-pill px-3 py-1 small">{{ $post->category }}</span>
                                @endif
                            </div>

                            <div class="card-body p-4 d-flex flex-column flex-grow-1">
                                <div class="d-flex align-items-center gap-3 text-muted small mb-2" style="font-size: 12px;">
                                    <span><i class="far fa-calendar-alt me-1"></i>{{ \Carbon\Carbon::parse($post->created_at)->format('M d, Y') }}</span>
                                    <span><i class="far fa-eye me-1"></i>{{ $post->views_count ?? 0 }} views</span>
                                </div>
                                <h5 class="fw-bold mb-2">
                                    <a href="{{ route('blogs.show', $post->slug) }}" class="text-dark text-decoration-none lh-base line-clamp-2">{{ $post->title }}</a>
                                </h5>
                                <p class="text-muted small line-clamp-2 mb-4 flex-grow-1">
                                    {{ $post->excerpt ?? Str::limit(strip_tags($post->content), 100) }}
                                </p>
                                <div class="border-top pt-3 d-flex justify-content-between align-items-center mt-auto">
                                    <span class="text-muted small"><i class="far fa-clock me-1"></i>3 min read</span>
                                    <a href="{{ route('blogs.show', $post->slug) }}" class="fw-bold text-primary text-decoration-none small">Read Story <i class="fas fa-arrow-right ms-1"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 py-5 text-center">
                        <div class="bg-white p-5 rounded-4 shadow-sm border">
                            <i class="far fa-newspaper fa-3x text-muted mb-3 opacity-50"></i>
                            <h4>No stories found</h4>
                            <p class="text-muted small">Try searching with other keywords or clear filter parameters.</p>
                            <a href="{{ route('blogs.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-4">Browse All Articles</a>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if(method_exists($posts, 'hasPages') && $posts->hasPages())
                <div class="mt-5 d-flex justify-content-center">
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
@endsection