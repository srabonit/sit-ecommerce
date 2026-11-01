@php
    static $menuComponentSettings = null;
    if ($menuComponentSettings === null) {
        $menuComponentSettings = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
            return \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $mSettings = $menuComponentSettings ?? [];

    // Header & Navigation Dynamic Color Resolvers
    $navMenuText       = $mSettings['color_hdr_menu_text'] ?? '#334155';
    $navMenuHoverText  = $mSettings['color_hdr_menu_hover_text'] ?? '#2563eb';
    $navMenuHoverBg    = $mSettings['color_hdr_menu_hover_bg'] ?? '#eff6ff';
    $navMenuActiveText = $mSettings['color_hdr_menu_active_text'] ?? '#2563eb';
    $navMenuActiveBg   = $mSettings['color_hdr_menu_active_bg'] ?? '#eff6ff';

    $dropMenuBg        = $mSettings['color_hdr_menu_drop_bg'] ?? '#ffffff';
    $dropMenuText      = $mSettings['color_hdr_menu_drop_text'] ?? '#475569';
    $dropMenuHoverBg   = $mSettings['color_hdr_menu_drop_hover_bg'] ?? '#f8fafc';
    $dropMenuHoverText = $mSettings['color_hdr_menu_drop_hover_text'] ?? '#2563eb';
@endphp

@once
<style>
    :root {
        --nav-dyn-menu-text: {{ $navMenuText }};
        --nav-dyn-menu-hover-text: {{ $navMenuHoverText }};
        --nav-dyn-menu-hover-bg: {{ $navMenuHoverBg }};
        --nav-dyn-menu-active-text: {{ $navMenuActiveText }};
        --nav-dyn-menu-active-bg: {{ $navMenuActiveBg }};

        --drop-dyn-menu-bg: {{ $dropMenuBg }};
        --drop-dyn-menu-text: {{ $dropMenuText }};
        --drop-dyn-menu-hover-bg: {{ $dropMenuHoverBg }};
        --drop-dyn-menu-hover-text: {{ $dropMenuHoverText }};
    }

    /* Strict Text Decoration None Across All Menu Anchors */
    .theme-menu-link,
    a.theme-menu-link,
    .dropdown-menu a,
    .dropdown-submenu a {
        text-decoration: none !important;
    }

    /* Top Level Primary Nav Links */
    a.theme-menu-link.nav-link {
        color: var(--nav-dyn-menu-text) !important;
        text-decoration: none !important;
        transition: color 0.2s ease, background-color 0.2s ease;
        border-radius: 6px;
    }
    a.theme-menu-link.nav-link:hover {
        color: var(--nav-dyn-menu-hover-text) !important;
        background-color: var(--nav-dyn-menu-hover-bg) !important;
        text-decoration: none !important;
    }
    a.theme-menu-link.nav-link.active {
        color: var(--nav-dyn-menu-active-text) !important;
        background-color: var(--nav-dyn-menu-active-bg) !important;
        font-weight: 700 !important;
        text-decoration: none !important;
    }

    /* Dropdown Menu Container */
    .dropdown-menu {
        background-color: var(--drop-dyn-menu-bg) !important;
        border-color: #e2e8f0 !important;
        border-radius: 8px;
    }

    /* Dropdown Items */
    a.theme-menu-link.dropdown-item {
        color: var(--drop-dyn-menu-text) !important;
        text-decoration: none !important;
        transition: all 0.15s ease;
        font-size: 13px;
        padding: 7px 16px;
    }
    a.theme-menu-link.dropdown-item:hover {
        background-color: var(--drop-dyn-menu-hover-bg) !important;
        color: var(--drop-dyn-menu-hover-text) !important;
        text-decoration: none !important;
    }
    a.theme-menu-link.dropdown-item.active {
        background-color: var(--drop-dyn-menu-hover-bg) !important;
        color: var(--drop-dyn-menu-hover-text) !important;
        font-weight: 700 !important;
        text-decoration: none !important;
    }

    /* Dropdown Submenu Positioning */
    .dropdown-submenu {
        position: relative;
    }
    .dropdown-submenu > .dropdown-menu {
        top: 0;
        left: 100%;
        margin-top: -6px;
        margin-left: 2px;
    }
    .dropdown-submenu:hover > .dropdown-menu {
        display: block;
    }
</style>
@endonce

@foreach($menuItems as $item)
    @php
        $itemUrl = filter_var($item['url'] ?? '', FILTER_VALIDATE_URL) ? $item['url'] : url($item['url'] ?? '/');
        $isActive = url()->current() === $itemUrl || request()->is(trim(parse_url($itemUrl, PHP_URL_PATH) ?? '', '/'));
    @endphp
    <li class="{{ !empty($item['children']) ? (isset($isSub) ? 'dropdown-submenu' : 'dropdown') : '' }}">
        <a href="{{ $itemUrl }}" class="{{ isset($isSub) ? 'dropdown-item' : 'nav-link' }} {{ $isActive ? 'active' : '' }} theme-menu-link text-decoration-none d-flex align-items-center justify-content-between gap-1">
            <span>{{ $item['title'] }}</span>
            @if(!empty($item['children']))
                <i class="fas fa-angle-{{ isset($isSub) ? 'right' : 'down' }} ms-1 small"></i>
            @endif
        </a>
        
        @if(!empty($item['children']))
            <ul class="dropdown-menu shadow-sm">
                @include('components.menu-item', ['menuItems' => $item['children'], 'isSub' => true])
            </ul>
        @endif
    </li>
@endforeach