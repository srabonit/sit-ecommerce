@php
    $displayEngine = app(\App\Services\DisplayRuleEngine::class);
    $activePopup = $displayEngine->getActivePopup(request());
@endphp

@if($activePopup)
@php
    $popupSettings = $activePopup->settings ?? [];
    $bgColor     = $popupSettings['bg_color'] ?? '#ffffff';
    $textColor   = $popupSettings['text_color'] ?? '#0f172a';
    $btnBgColor  = $popupSettings['btn_bg_color'] ?? '#2563eb';
    $maxWidth    = $popupSettings['max_width'] ?? '500px';

    $showTitle   = $popupSettings['show_title'] ?? true;
    $showButton  = $popupSettings['show_button'] ?? true;
    $isOnlyImage = empty(trim(strip_tags($activePopup->content ?? ''))) && !$showTitle && !$showButton && !empty($activePopup->image);

    // Multi-page independent cookie resolution
    $freqScope = $popupSettings['frequency_scope'] ?? 'per_page';
    $pageSlugKey = ($freqScope === 'per_page') ? '_' . md5(request()->path()) : '';
    $cookieName  = 'popup_viewed_' . $activePopup->id . $pageSlugKey;

    // Shortcode & dynamic form parser
    $renderedContent = $activePopup->content ?? '';
    if (str_contains($renderedContent, '[form') && class_exists(\App\Services\ShortcodeParser::class)) {
        $renderedContent = app(\App\Services\ShortcodeParser::class)->parse($renderedContent);
    }
@endphp

<style>
    /* Safe Z-Index: Layer 6 (1080) */
    .dynamic-popup-overlay {
        position: fixed;
        inset: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(15, 23, 42, 0.68);
        backdrop-filter: blur(2.5px);
        z-index: 1080;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 16px;
        opacity: 0;
        transition: opacity 0.22s ease-in-out;
    }

    .dynamic-popup-overlay.show {
        display: flex !important;
        opacity: 1 !important;
    }

    .dynamic-popup-modal {
        background-color: {{ $bgColor }};
        color: {{ $textColor }};
        max-width: {{ $maxWidth }};
        width: 100%;
        position: relative;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
        border: 1px solid rgba(226, 232, 240, 0.85);
        border-radius: 5px !important;
        overflow: hidden;
        transform: scale(0.96);
        transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .dynamic-popup-overlay.show .dynamic-popup-modal {
        transform: scale(1);
    }

    .dynamic-popup-close {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 30px;
        height: 30px;
        background-color: #ffffff;
        color: #334155;
        border: 1px solid #e2e8f0;
        border-radius: 5px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        cursor: pointer;
        z-index: 25;
        transition: all 0.15s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.18);
    }

    .dynamic-popup-close:hover {
        background-color: #ef4444;
        color: #ffffff;
        border-color: #ef4444;
    }

    .dynamic-popup-banner {
        width: 100%;
        max-height: 280px;
        object-fit: cover;
        display: block;
    }

    .dynamic-popup-body {
        padding: 20px 22px;
    }

    .dynamic-popup-btn {
        background-color: {{ $btnBgColor }} !important;
        color: #ffffff !important;
        border: none !important;
        border-radius: 5px !important;
        font-weight: 700;
        font-size: 13px;
        padding: 9px 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none !important;
        transition: opacity 0.15s ease, transform 0.15s ease;
    }

    .dynamic-popup-btn:hover {
        opacity: 0.92;
        color: #ffffff !important;
        transform: translateY(-1px);
    }

    /* Clean Image Only Lightbox Mode */
    .image-only-popup {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
    }
    .image-only-popup .dynamic-popup-banner {
        border-radius: 5px !important;
        max-height: 82vh !important;
        box-shadow: 0 25px 50px rgba(0,0,0,0.45);
        cursor: pointer;
    }
</style>

<div id="dynamicPopupOverlay" class="dynamic-popup-overlay" role="dialog" aria-modal="true">
    <div class="dynamic-popup-modal {{ $isOnlyImage ? 'image-only-popup' : '' }}">
        <button type="button" class="dynamic-popup-close" id="dynamicPopupCloseBtn" aria-label="Close modal">
            <i class="fas fa-times"></i>
        </button>

        @if($isOnlyImage)
            {{-- Pure Image Only Clickable Banner --}}
            @if($activePopup->button_url)
                <a href="{{ $activePopup->button_url }}">
                    <img src="{{ $activePopup->image }}" alt="{{ $activePopup->title }}" class="dynamic-popup-banner">
                </a>
            @else
                <img src="{{ $activePopup->image }}" alt="{{ $activePopup->title }}" class="dynamic-popup-banner">
            @endif
        @else
            {{-- Standard Full Modal Layout --}}
            @if($activePopup->image)
                <img src="{{ $activePopup->image }}" alt="{{ $activePopup->title }}" class="dynamic-popup-banner">
            @endif

            <div class="dynamic-popup-body">
                @if($showTitle)
                    <h5 class="fw-bold mb-2" id="dynamicPopupTitle" style="font-size: 16px; color: {{ $textColor }};">
                        {{ $activePopup->title }}
                    </h5>
                @endif

                @if(!empty($renderedContent))
                    <div class="small mb-3" style="font-size: 12.5px; line-height: 1.5; color: {{ $textColor }}; opacity: 0.95;">
                        {!! $renderedContent !!}
                    </div>
                @endif

                @if($showButton && $activePopup->button_text && $activePopup->button_url)
                    <div class="mt-3 text-end">
                        <a href="{{ $activePopup->button_url }}" class="dynamic-popup-btn shadow-xs">
                            {{ $activePopup->button_text }}
                        </a>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>

<script>
    (function() {
        const overlay = document.getElementById('dynamicPopupOverlay');
        const closeBtn = document.getElementById('dynamicPopupCloseBtn');
        const cookieName = '{{ $cookieName }}';
        const frequencyDays = {{ (int) $activePopup->frequency_days }};
        const triggerType = '{{ $activePopup->trigger_type }}';
        const triggerValue = {{ (int) $activePopup->trigger_value }};

        function hasCookie(name) {
            return document.cookie.split(';').some(c => c.trim().startsWith(name + '='));
        }

        function setCookie(name, val, days) {
            let expires = '';
            if (days > 0) {
                const date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = '; expires=' + date.toUTCString();
            }
            document.cookie = name + '=' + (val || '') + expires + '; path=/; SameSite=Lax';
        }

        function showPopup() {
            if (hasCookie(cookieName)) return;
            overlay.classList.add('show');
        }

        function closePopup() {
            overlay.classList.remove('show');
            if (frequencyDays > 0) {
                setCookie(cookieName, '1', frequencyDays);
            }
        }

        if (closeBtn) closeBtn.addEventListener('click', closePopup);
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) closePopup();
        });

        if (!hasCookie(cookieName)) {
            if (triggerType === 'delay') {
                setTimeout(showPopup, Math.max(triggerValue, 0) * 1000);
            } else if (triggerType === 'scroll') {
                let triggered = false;
                window.addEventListener('scroll', function onScroll() {
                    if (triggered) return;
                    const scrollTop = window.scrollY || document.documentElement.scrollTop;
                    const docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                    if (docHeight <= 0) return;
                    if ((scrollTop / docHeight) * 100 >= triggerValue) {
                        triggered = true;
                        showPopup();
                        window.removeEventListener('scroll', onScroll);
                    }
                }, { passive: true });
            } else {
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', showPopup);
                } else {
                    showPopup();
                }
            }
        }
    })();
</script>
@endif