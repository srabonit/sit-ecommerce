@props(['zone' => 'sidebar-right'])

@php
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

@if($widgets->count() > 0)
    <div class="widgets-stack d-flex flex-column gap-4">
        @foreach($widgets as $w)
            <div class="widget-item bg-white p-3 p-md-4 rounded-4 shadow-sm border border-light">
                @if(!empty($w->title) && $w->widget_type !== 'custom_html')
                    <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom position-relative">
                        <span class="border-bottom border-2 border-primary pb-2 position-absolute" style="bottom:-1px; left:0;">
                            {{ $w->title }}
                        </span>
                    </h6>
                @endif

                {{-- 1. Search Box --}}
                @if($w->widget_type === 'search')
                    <form action="{{ route('blogs.index') }}" method="GET">
                        <div class="input-group">
                            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm rounded-start-pill ps-3" placeholder="Search news &amp; tips...">
                            <button class="btn btn-primary btn-sm rounded-end-pill px-3" type="submit"><i class="fas fa-search"></i></button>
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
                                <a href="{{ route('blogs.index', ['category' => $c->category]) }}" class="d-flex justify-content-between align-items-center text-decoration-none text-secondary small py-1 px-2 rounded hover-bg-light">
                                    <span><i class="fas fa-chevron-right text-primary me-2" style="font-size: 10px;"></i>{{ $c->category }}</span>
                                    <span class="badge bg-light text-muted border rounded-pill">{{ $c->count }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>

                {{-- 3. Recent Articles Widget (Updated with Dynamic Image Resolver) --}}
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
                                    <a href="{{ route('blogs.show', $r->slug) }}" class="text-dark text-decoration-none fw-semibold small lh-sm line-clamp-2">{{ Str::limit($r->title, 45) }}</a>
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