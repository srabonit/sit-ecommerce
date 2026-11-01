@php
    $displayEngine  = app(\App\Services\DisplayRuleEngine::class);
    $activeWidgets  = $displayEngine->getActiveWidgets(request());
    $offsets        = $displayEngine->getPositionOffsets(request());

    // Hub Presentation Settings fetched from DisplayCampaignRule
    $hubRuleModel   = \App\Models\DisplayCampaignRule::where('rule_scope', 'floating_widget_hub')->first();
    $hubSettings    = is_array($hubRuleModel->settings ?? null) 
        ? $hubRuleModel->settings 
        : json_decode($hubRuleModel->settings ?? '[]', true);

    $displayStyle   = $hubSettings['display_style'] ?? 'speed_dial';
    $hubLabel       = $hubSettings['hub_label'] ?? 'Chat with Us';
    
    // Exact Boolean Evaluation
    $showLabel      = isset($hubSettings['show_label']) 
        ? filter_var($hubSettings['show_label'], FILTER_VALIDATE_BOOLEAN) 
        : true;

    $hubIcon        = $hubSettings['hub_icon'] ?? 'fas fa-comment-dots';
    $hubBgColor     = $hubSettings['hub_bg_color'] ?? '#1e3a8a';
    $hubIconColor   = $hubSettings['hub_icon_color'] ?? '#ffffff';
    $badgeBgColor   = $hubSettings['badge_bg_color'] ?? '#ffffff';
    $badgeTextColor = $hubSettings['badge_text_color'] ?? '#1e3a8a';

    $rightWidgets = $activeWidgets->where('position', 'bottom_right')->sortBy('order_index');
    $leftWidgets  = $activeWidgets->where('position', 'bottom_left')->sortBy('order_index');
@endphp

@if($activeWidgets->isNotEmpty())
<!-- Clean Plus Jakarta Sans for Badges & Buttons -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --app-safe-bottom-offset: 80px;
    }

    @keyframes enterprisePulseGlow {
        0% { box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.6); }
        70% { box-shadow: 0 0 0 12px rgba(37, 211, 102, 0); }
        100% { box-shadow: 0 0 0 0 rgba(37, 211, 102, 0); }
    }

    /* Attractive Pulse Effect for Master Main Icon */
    @keyframes hubPulseGlow {
        0% { box-shadow: 0 0 0 0 rgba(30, 58, 138, 0.55); }
        70% { box-shadow: 0 0 0 12px rgba(30, 58, 138, 0); }
        100% { box-shadow: 0 0 0 0 rgba(30, 58, 138, 0); }
    }

    @keyframes liveStatusBlink {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(0.85); }
    }

    .floating-widgets-stack {
        position: fixed;
        bottom: var(--app-safe-bottom-offset);
        z-index: 1045;
        display: flex;
        flex-direction: column;
        gap: 10px;
        pointer-events: none;
        transition: bottom 0.28s cubic-bezier(0.16, 1, 0.3, 1), transform 0.22s ease;
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif !important;
    }

    .floating-widgets-stack.stack-right {
        right: 18px;
        align-items: flex-end;
    }

    .floating-widgets-stack.stack-left {
        left: 18px;
        align-items: flex-start;
    }

    .floating-widget-item {
        pointer-events: auto !important;
        display: inline-flex;
        align-items: center;
        text-decoration: none !important;
        position: relative;
        cursor: pointer;
        background: transparent;
        border: none;
        padding: 0;
        outline: none !important;
    }

    /* Circular Enterprise Action Button */
    .floating-widget-btn {
        width: 44px;
        height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50% !important;
        box-shadow: 0 4px 15px rgba(15, 23, 42, 0.22);
        border: 2px solid #ffffff;
        font-size: 18px;
        flex-shrink: 0;
        position: relative;
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease, background-color 0.2s ease;
    }

    .floating-widget-btn.has-glow {
        animation: enterprisePulseGlow 2.2s infinite;
    }

    .floating-widget-item:hover .floating-widget-btn,
    .floating-widget-item:active .floating-widget-btn {
        transform: scale(1.1) translateY(-2px);
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.32);
    }

    .widget-live-dot {
        position: absolute;
        top: 1px;
        right: 1px;
        width: 10px;
        height: 10px;
        background-color: #22c55e;
        border: 2px solid #ffffff;
        border-radius: 50%;
        animation: liveStatusBlink 1.8s infinite;
    }

    /* Tooltip Chip for Child Floating Widgets */
    .floating-widget-chip {
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 5px !important;
        white-space: nowrap;
        background: #0f172a;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.18);
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.18s ease, transform 0.18s ease;
        position: absolute;
        pointer-events: none;
        letter-spacing: 0.2px;
    }

    .stack-right .floating-widget-chip {
        right: calc(100% + 10px);
        transform: translateX(6px);
    }

    .stack-left .floating-widget-chip {
        left: calc(100% + 10px);
        transform: translateX(-6px);
    }

    .floating-widget-item:hover .floating-widget-chip {
        opacity: 1;
        visibility: visible;
        transform: translateX(0);
    }

    .speed-dial-expanded-wrap {
        display: flex;
        flex-direction: column;
        gap: 10px;
        align-items: flex-end;
        padding: 0;
        margin: 0;
        transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1);
        transform-origin: bottom center;
    }

    .speed-dial-expanded-wrap.is-collapsed {
        display: none !important;
        opacity: 0;
        visibility: hidden;
        transform: translateY(18px) scale(0.85);
        height: 0;
        margin: 0;
        overflow: hidden;
        pointer-events: none;
    }

    /* Master Speed-Dial Button: Exact 44px with Pulse Glow */
    .speed-dial-master-btn {
        width: 44px;
        height: 44px;
        border-radius: 50% !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 15px rgba(15, 23, 42, 0.22);
        border: 2px solid #ffffff;
        font-size: 18px;
        flex-shrink: 0;
        position: relative;
        animation: hubPulseGlow 2.4s infinite;
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease, background-color 0.2s ease !important;
    }

    .speed-dial-hub-trigger:hover .speed-dial-master-btn,
    .speed-dial-hub-trigger:active .speed-dial-master-btn {
        transform: scale(1.1) translateY(-2px);
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.32);
    }

    .speed-dial-master-btn i {
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .speed-dial-master-btn.is-active {
        animation: none;
    }

    .speed-dial-master-btn.is-active i {
        transform: rotate(90deg);
    }

    /* ========================================================================= */
    /* MASTER HUB TRIGGER (CLICKABLE & HORIZONTAL BADGE ALIGNMENT)               */
    /* ========================================================================= */
    .speed-dial-hub-trigger {
        pointer-events: auto !important;
        display: inline-flex !important;
        align-items: center;
        gap: 9px;
        background: transparent;
        border: none;
        padding: 0;
        cursor: pointer;
        position: relative;
        outline: none !important;
        user-select: none;
    }

    .speed-dial-hub-trigger * {
        pointer-events: none;
    }

    /* Stack Right: Text is placed on the LEFT of the icon */
    .stack-right .speed-dial-hub-trigger {
        flex-direction: row;
        justify-content: flex-end;
    }

    /* Stack Left: Text is placed on the RIGHT of the icon */
    .stack-left .speed-dial-hub-trigger {
        flex-direction: row-reverse;
        justify-content: flex-start;
    }

    .speed-dial-pill-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 5px 13px;
        border-radius: 20px !important;
        font-size: 11.5px;
        font-weight: 700;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
        border: 1.5px solid rgba(226, 232, 240, 0.9);
        white-space: nowrap;
        margin: 0;
        line-height: 1.25;
        letter-spacing: -0.01em;
        transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.22s ease;
    }

    .speed-dial-hub-trigger:hover .speed-dial-pill-badge {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.18);
    }

    .dynamic-widget-modal .modal-content,
    .dynamic-widget-modal .btn,
    .dynamic-widget-modal .form-control {
        border-radius: 5px !important;
    }

    @media (max-width: 575.98px) {
        .floating-widgets-stack.stack-right { right: 14px; }
        .floating-widgets-stack.stack-left { left: 14px; }
        .floating-widget-btn,
        .speed-dial-master-btn { width: 40px; height: 40px; font-size: 16px; }
        .speed-dial-pill-badge { font-size: 10px; padding: 4px 10px; }
    }
</style>

{{-- ========================================================================= --}}
{{-- 1. BOTTOM-RIGHT STACK (SPEED-DIAL TOGGLE VS DIRECT STACK)                 --}}
{{-- ========================================================================= --}}
@if($rightWidgets->isNotEmpty())
    <div class="floating-widgets-stack stack-right" id="floatingWidgetsRight">

        @if($displayStyle === 'speed_dial')
            {{-- MODE A: SPEED-DIAL COLLAPSIBLE HUB --}}
            <div class="speed-dial-expanded-wrap is-collapsed" id="speedDialRightItems">
                @foreach($rightWidgets as $widget)
                    @php
                        $settings   = is_array($widget->settings) ? $widget->settings : json_decode($widget->settings ?? '[]', true);
                        $actionType = $settings['action_type'] ?? 'link';
                        $bgColor    = $widget->bg_color ?: '#2563eb';
                        $iconColor  = $widget->icon_color ?: '#ffffff';
                        $iconClass  = $widget->icon ?: 'fas fa-comment-dots';
                        $isWhatsApp = ($widget->widget_type === 'whatsapp');

                        $targetUrl = match($widget->widget_type) {
                            'whatsapp'  => 'https://wa.me/' . preg_replace('/[^0-9]/', '', $widget->value),
                            'phone'     => 'tel:' . preg_replace('/[^0-9+]/', '', $widget->value),
                            'messenger' => str_starts_with($widget->value, 'http') ? $widget->value : 'https://m.me/' . ltrim($widget->value, '@/'),
                            'email'     => 'mailto:' . $widget->value,
                            default     => $widget->value ?: '#',
                        };
                    @endphp

                    @if($actionType === 'modal')
                        <button type="button" 
                                class="floating-widget-item" 
                                data-bs-toggle="modal" 
                                data-bs-target="#dynamicWidgetModal_{{ $widget->id }}" 
                                aria-label="{{ $widget->label ?? 'Widget Modal' }}">
                            @if($widget->label)
                                <span class="floating-widget-chip">{{ $widget->label }}</span>
                            @endif
                            <span class="floating-widget-btn" style="background-color: {{ $bgColor }}; color: {{ $iconColor }};">
                                <i class="{{ $iconClass }}"></i>
                            </span>
                        </button>
                    @else
                        <a href="{{ $targetUrl }}" 
                           class="floating-widget-item" 
                           @if(!in_array($widget->widget_type, ['phone', 'email'])) target="_blank" rel="noopener noreferrer" @endif
                           aria-label="{{ $widget->label ?? $widget->widget_type }}">
                            @if($widget->label)
                                <span class="floating-widget-chip">{{ $widget->label }}</span>
                            @endif
                            <span class="floating-widget-btn {{ $isWhatsApp ? 'has-glow' : '' }}" style="background-color: {{ $bgColor }}; color: {{ $iconColor }};">
                                <i class="{{ $iconClass }}"></i>
                                @if($isWhatsApp)
                                    <span class="widget-live-dot" title="Online"></span>
                                @endif
                            </span>
                        </a>
                    @endif
                @endforeach
            </div>

            {{-- Speed-Dial Master Hub Button --}}
            <button type="button" 
                    class="floating-widget-item speed-dial-hub-trigger" 
                    id="btnMasterSpeedDial" 
                    onclick="toggleSpeedDialHub(event)" 
                    aria-label="Toggle Quick Support Hub">
                
                {{-- Pill Badge on Left --}}
                @if($showLabel && !empty($hubLabel))
                    <span class="speed-dial-pill-badge" 
                          id="speedDialPillBadge" 
                          style="background-color: {{ $badgeBgColor }}; color: {{ $badgeTextColor }}; border-color: rgba(0,0,0,0.12);">
                        {{ $hubLabel }}
                    </span>
                @endif

                {{-- Master Circular Hub Button on Right --}}
                <span class="speed-dial-master-btn" 
                      id="speedDialMasterIcon" 
                      style="background-color: {{ $hubBgColor }}; color: {{ $hubIconColor }};">
                    <i class="{{ $hubIcon }}"></i>
                </span>
            </button>

        @else
            {{-- MODE B: DIRECT FLOATING STACK --}}
            @foreach($rightWidgets as $widget)
                @php
                    $settings   = is_array($widget->settings) ? $widget->settings : json_decode($widget->settings ?? '[]', true);
                    $actionType = $settings['action_type'] ?? 'link';
                    $bgColor    = $widget->bg_color ?: '#2563eb';
                    $iconColor  = $widget->icon_color ?: '#ffffff';
                    $iconClass  = $widget->icon ?: 'fas fa-comment-dots';
                    $isWhatsApp = ($widget->widget_type === 'whatsapp');

                    $targetUrl = match($widget->widget_type) {
                        'whatsapp'  => 'https://wa.me/' . preg_replace('/[^0-9]/', '', $widget->value),
                        'phone'     => 'tel:' . preg_replace('/[^0-9+]/', '', $widget->value),
                        'messenger' => str_starts_with($widget->value, 'http') ? $widget->value : 'https://m.me/' . ltrim($widget->value, '@/'),
                        'email'     => 'mailto:' . $widget->value,
                        default     => $widget->value ?: '#',
                    };
                @endphp

                @if($actionType === 'modal')
                    <button type="button" 
                            class="floating-widget-item" 
                            data-bs-toggle="modal" 
                            data-bs-target="#dynamicWidgetModal_{{ $widget->id }}" 
                            aria-label="{{ $widget->label ?? 'Widget Modal' }}">
                        @if($widget->label)
                            <span class="floating-widget-chip">{{ $widget->label }}</span>
                        @endif
                        <span class="floating-widget-btn" style="background-color: {{ $bgColor }}; color: {{ $iconColor }};">
                            <i class="{{ $iconClass }}"></i>
                        </span>
                    </button>
                @else
                    <a href="{{ $targetUrl }}" 
                       class="floating-widget-item" 
                       @if(!in_array($widget->widget_type, ['phone', 'email'])) target="_blank" rel="noopener noreferrer" @endif
                       aria-label="{{ $widget->label ?? $widget->widget_type }}">
                        @if($widget->label)
                            <span class="floating-widget-chip">{{ $widget->label }}</span>
                        @endif
                        <span class="floating-widget-btn {{ $isWhatsApp ? 'has-glow' : '' }}" style="background-color: {{ $bgColor }}; color: {{ $iconColor }};">
                            <i class="{{ $iconClass }}"></i>
                            @if($isWhatsApp)
                                <span class="widget-live-dot" title="Online"></span>
                            @endif
                        </span>
                    </a>
                @endif
            @endforeach
        @endif

    </div>
@endif

{{-- ========================================================================= --}}
{{-- 2. BOTTOM-LEFT STACK (LEFT POSITIONED WIDGETS)                            --}}
{{-- ========================================================================= --}}
@if($leftWidgets->isNotEmpty())
    <div class="floating-widgets-stack stack-left" id="floatingWidgetsLeft">
        @foreach($leftWidgets as $widget)
            @php
                $settings   = is_array($widget->settings) ? $widget->settings : json_decode($widget->settings ?? '[]', true);
                $actionType = $settings['action_type'] ?? 'link';
                $bgColor    = $widget->bg_color ?: '#2563eb';
                $iconColor  = $widget->icon_color ?: '#ffffff';
                $iconClass  = $widget->icon ?: 'fas fa-comment-dots';

                $targetUrl = match($widget->widget_type) {
                    'whatsapp'  => 'https://wa.me/' . preg_replace('/[^0-9]/', '', $widget->value),
                    'phone'     => 'tel:' . preg_replace('/[^0-9+]/', '', $widget->value),
                    'messenger' => str_starts_with($widget->value, 'http') ? $widget->value : 'https://m.me/' . ltrim($widget->value, '@/'),
                    'email'     => 'mailto:' . $widget->value,
                    default     => $widget->value ?: '#',
                };
            @endphp

            @if($actionType === 'modal')
                <button type="button" 
                        class="floating-widget-item" 
                        data-bs-toggle="modal" 
                        data-bs-target="#dynamicWidgetModal_{{ $widget->id }}" 
                        aria-label="{{ $widget->label ?? 'Widget Modal' }}">
                    <span class="floating-widget-btn" style="background-color: {{ $bgColor }}; color: {{ $iconColor }};">
                        <i class="{{ $iconClass }}"></i>
                    </span>
                    @if($widget->label)
                        <span class="floating-widget-chip">{{ $widget->label }}</span>
                    @endif
                </button>
            @else
                <a href="{{ $targetUrl }}" 
                   class="floating-widget-item" 
                   @if(!in_array($widget->widget_type, ['phone', 'email'])) target="_blank" rel="noopener noreferrer" @endif
                   aria-label="{{ $widget->label ?? $widget->widget_type }}">
                    <span class="floating-widget-btn" style="background-color: {{ $bgColor }}; color: {{ $iconColor }};">
                        <i class="{{ $iconClass }}"></i>
                    </span>
                    @if($widget->label)
                        <span class="floating-widget-chip">{{ $widget->label }}</span>
                    @endif
                </a>
            @endif
        @endforeach
    </div>
@endif

{{-- ========================================================================= --}}
{{-- 3. DYNAMIC MODALS                                                         --}}
{{-- ========================================================================= --}}
@foreach($activeWidgets as $widget)
    @php
        $settings = is_array($widget->settings) ? $widget->settings : json_decode($widget->settings ?? '[]', true);
        $actionType = $settings['action_type'] ?? 'link';
    @endphp

    @if($actionType === 'modal')
        @php
            $mTitle = $settings['modal_title'] ?? '';
            $mContent = $settings['modal_content'] ?? '';

            if (str_contains($mContent, '[form') && class_exists(\App\Services\ShortcodeParser::class)) {
                $mContent = app(\App\Services\ShortcodeParser::class)->parse($mContent);
            }
        @endphp

        <div class="modal fade dynamic-widget-modal" id="dynamicWidgetModal_{{ $widget->id }}" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
                <div class="modal-content border-0 shadow-lg" style="overflow: hidden; border-radius: 5px !important;">
                    
                    @if(!empty($mTitle))
                        <div class="modal-header border-bottom py-2.5 px-3 bg-dark text-white d-flex justify-content-between align-items-center">
                            <h6 class="modal-title fw-bold mb-0 d-flex align-items-center gap-2" style="font-size: 13.5px;">
                                <i class="{{ $widget->icon ?: 'fas fa-info-circle' }} text-warning"></i> 
                                <span>{{ $mTitle }}</span>
                            </h6>
                            <button type="button" class="btn-close btn-close-white btn-sm shadow-none" data-bs-dismiss="modal"></button>
                        </div>
                    @else
                        <div class="d-flex justify-content-end p-2 pb-0">
                            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
                        </div>
                    @endif

                    <div class="modal-body p-3">
                        {!! $mContent !!}
                    </div>

                </div>
            </div>
        </div>
    @endif
@endforeach

{{-- ========================================================================= --}}
{{-- 4. COLLISION RESOLVER & SPEED-DIAL TOGGLE SCRIPT                          --}}
{{-- ========================================================================= --}}
<script>
    let isSpeedDialExpanded = false;

    function toggleSpeedDialHub(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        const wrap = document.getElementById('speedDialRightItems');
        const iconBtn = document.getElementById('speedDialMasterIcon');
        const iconEl = iconBtn ? iconBtn.querySelector('i') : null;
        if (!wrap || !iconBtn) return;

        isSpeedDialExpanded = !isSpeedDialExpanded;

        if (isSpeedDialExpanded) {
            wrap.classList.remove('is-collapsed');
            iconBtn.classList.add('is-active');
            if (iconEl) {
                iconEl.className = 'fas fa-times';
            }
        } else {
            wrap.classList.add('is-collapsed');
            iconBtn.classList.remove('is-active');
            if (iconEl) {
                iconEl.className = '{{ $hubIcon }}';
            }
        }
    }

    function updateWidgetStackPositions() {
        const stickyBar  = document.getElementById('siteStickyBottomBar');
        const bttBtn     = document.getElementById('btnScrollToTop');
        const spBar      = document.getElementById('stickySingleProductBar');
        const rightStack = document.getElementById('floatingWidgetsRight');
        const leftStack  = document.getElementById('floatingWidgetsLeft');

        let baseBottom = 20;

        if (stickyBar && window.getComputedStyle(stickyBar).display !== 'none') {
            const barH = stickyBar.offsetHeight || 62;
            baseBottom = Math.max(baseBottom, barH + 14);
        }

        if (spBar && spBar.classList.contains('bar-visible') && window.getComputedStyle(spBar).display !== 'none') {
            const spH = spBar.offsetHeight || 65;
            baseBottom = Math.max(baseBottom, spH + 16);
        }

        let rightBottom = baseBottom;
        let leftBottom  = baseBottom;

        if (bttBtn && window.getComputedStyle(bttBtn).display !== 'none' && bttBtn.classList.contains('active-visible')) {
            const bttStyles = window.getComputedStyle(bttBtn);
            const bttHeight = bttBtn.offsetHeight || 42;
            const bttBottomPos = parseInt(bttStyles.bottom) || 72;

            const isBttRight = (bttStyles.right !== 'auto' && parseInt(bttStyles.right) < 80);
            const isBttLeft  = (bttStyles.left !== 'auto' && parseInt(bttStyles.left) < 80);

            if (isBttRight) {
                rightBottom = Math.max(rightBottom, bttBottomPos + bttHeight + 10);
            }
            if (isBttLeft) {
                leftBottom = Math.max(leftBottom, bttBottomPos + bttHeight + 10);
            }
        }

        if (rightStack) rightStack.style.bottom = rightBottom + 'px';
        if (leftStack) leftStack.style.bottom = leftBottom + 'px';
    }

    window.addEventListener('scroll', updateWidgetStackPositions, { passive: true });
    window.addEventListener('resize', updateWidgetStackPositions, { passive: true });
    document.addEventListener('DOMContentLoaded', updateWidgetStackPositions);
</script>
@endif