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
                    <span class="slide-badge theme-slider-badge" style="background: {{ !empty($slide->badge_bg) ? $slide->badge_bg : 'var(--common-btn-bg, #2563eb)' }}; color: {{ !empty($slide->badge_color) ? $slide->badge_color : '#ffffff' }};">
                        {{ $slide->badge_text }}
                    </span>
                @endif

                @if(!empty($slide->title))
                    <h2 class="slide-title theme-slider-title" style="color: {{ $slide->title_color ?? '#ffffff' }};">{{ $slide->title }}</h2>
                @endif

                @if(!empty($slide->subtitle))
                    <p class="slide-subtitle theme-slider-subtitle" style="color: {{ $slide->subtitle_color ?? 'rgba(255,255,255,0.85)' }};">{{ $slide->subtitle }}</p>
                @endif

                @if(!empty($slide->btn_text))
                    <a href="{{ $slide->btn_url }}" class="slide-btn theme-slider-btn theme-btn-primary" style="background: {{ !empty($slide->btn_bg) ? $slide->btn_bg : 'var(--common-btn-bg, #2563eb)' }}; color: {{ !empty($slide->btn_color) ? $slide->btn_color : 'var(--common-btn-text, #ffffff)' }};">
                        {{ $slide->btn_text }} &rarr;
                    </a>
                @endif
            </div>
        @endif
    </div>
@endforeach