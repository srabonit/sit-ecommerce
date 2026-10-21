@foreach($sliders as $slide)
    @php
        $mobileRatioClass = match($slide->mobile_view_ratio ?? 'landscape') {
            'square'   => 'ratio-mobile-square',
            'portrait' => 'ratio-mobile-portrait',
            default    => 'ratio-mobile-landscape',
        };
        $hasOverlay = !isset($slide->enable_overlay) || $slide->enable_overlay;
        $overlayColor = $slide->overlay_color ?? '#0f172a';
        $overlayOpacity = $slide->overlay_opacity ?? '0.45';
        $showContent = !isset($slide->show_content) || $slide->show_content;

        // Centralized Safe Slider Image Resolver (Safe for Localhost & cPanel)
        $slideImg = '';
        if (!empty($slide->image)) {
            if (class_exists(\App\Services\ImagePathService::class)) {
                $slideImg = \App\Services\ImagePathService::resolveUrl($slide->image, '');
            } elseif (function_exists('show_image')) {
                $slideImg = show_image($slide->image, '');
            } elseif (filter_var($slide->image, FILTER_VALIDATE_URL)) {
                $slideImg = $slide->image;
            } else {
                $slideImg = asset(ltrim($slide->image, '/'));
            }
        }
    @endphp

    <div class="hero-slide-item {{ $mobileRatioClass }}" style="background-image: url('{{ $slideImg }}');">
        {{-- Color Overlay Control --}}
        @if($hasOverlay)
            <div class="slide-overlay" style="background-color: {{ $overlayColor }}; opacity: {{ $overlayOpacity }};"></div>
        @endif

        {{-- Content On/Off Control --}}
        @if($showContent)
            <div class="slide-caption-wrap">
                @if(!empty($slide->badge_text))
                    <span class="slide-badge" style="background: {{ $slide->badge_bg }}; color: {{ $slide->badge_color }};">
                        {{ $slide->badge_text }}
                    </span>
                @endif

                @if(!empty($slide->title))
                    <h2 class="slide-title" style="color: {{ $slide->title_color }};">{{ $slide->title }}</h2>
                @endif

                @if(!empty($slide->subtitle))
                    <p class="slide-subtitle" style="color: {{ $slide->subtitle_color }};">{{ $slide->subtitle }}</p>
                @endif

                @if(!empty($slide->btn_text))
                    <a href="{{ $slide->btn_url }}" class="slide-btn" style="background: {{ $slide->btn_bg }}; color: {{ $slide->btn_color }};">
                        {{ $slide->btn_text }} &rarr;
                    </a>
                @endif
            </div>
        @endif
    </div>
@endforeach