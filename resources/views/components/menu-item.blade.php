@foreach($menuItems as $item)
    @php
        $itemUrl = filter_var($item['url'] ?? '', FILTER_VALIDATE_URL) ? $item['url'] : url($item['url'] ?? '/');
        $isActive = url()->current() === $itemUrl || request()->is(trim(parse_url($itemUrl, PHP_URL_PATH) ?? '', '/'));
    @endphp
    <li class="{{ !empty($item['children']) ? (isset($isSub) ? 'dropdown-submenu' : 'dropdown') : '' }}">
        <a href="{{ $itemUrl }}" class="{{ isset($isSub) ? 'dropdown-item' : 'nav-link' }} {{ $isActive ? 'active' : '' }}">
            {{ $item['title'] }}
            @if(!empty($item['children']))
                <i class="fas fa-angle-{{ isset($isSub) ? 'right' : 'down' }} ms-1 small"></i>
            @endif
        </a>
        
        @if(!empty($item['children']))
            <ul class="dropdown-menu">
                @include('components.menu-item', ['menuItems' => $item['children'], 'isSub' => true])
            </ul>
        @endif
    </li>
@endforeach