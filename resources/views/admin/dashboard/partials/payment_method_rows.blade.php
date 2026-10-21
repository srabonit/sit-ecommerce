@php
    // Dynamic media resolver for payment gateway icons / logos
    $resolveGatewayLogo = function($path, $fallback = '') {
        if (empty($path)) return $fallback;
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path, $fallback);
        }
        if (function_exists('show_image')) return show_image($path, $fallback);
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        return asset(ltrim(str_replace(['public_html/', 'public/'], '', $path), '/'));
    };
@endphp

@forelse($paymentMethodStats as $pStat)
    @php
        $mName = strtolower($pStat->method_name ?? 'cod');
        $rawLogo = $pStat->logo ?? ($pStat->icon ?? ($pStat->image ?? null));
        $gatewayLogo = !empty($rawLogo) ? $resolveGatewayLogo($rawLogo) : null;
        $count = (int)($pStat->count ?? 0);
        $amount = (float)($pStat->total_amount ?? 0);

        // Brand color and icon mappings for popular Bangladeshi & global gateways
        $badgeTheme = match(true) {
            str_contains($mName, 'bkash')   => ['color' => '#d82a72', 'bg' => '#fdf2f6', 'border' => '#fbcfe8', 'icon' => 'fa-mobile-screen'],
            str_contains($mName, 'nagad')   => ['color' => '#f7941d', 'bg' => '#fff7ed', 'border' => '#ffedd5', 'icon' => 'fa-money-bill-wave'],
            str_contains($mName, 'rocket')  => ['color' => '#8c3494', 'bg' => '#faf5ff', 'border' => '#f3e8ff', 'icon' => 'fa-paper-plane'],
            str_contains($mName, 'upay')    => ['color' => '#005baa', 'bg' => '#eff6ff', 'border' => '#dbeafe', 'icon' => 'fa-wallet'],
            str_contains($mName, 'card') || str_contains($mName, 'visa') || str_contains($mName, 'master') => ['color' => '#2563eb', 'bg' => '#eff6ff', 'border' => '#bfdbfe', 'icon' => 'fa-credit-card'],
            str_contains($mName, 'pos')     => ['color' => '#059669', 'bg' => '#f0fdf4', 'border' => '#bbf7d0', 'icon' => 'fa-cash-register'],
            default                         => ['color' => '#0f172a', 'bg' => '#f8fafc', 'border' => '#e2e8f0', 'icon' => 'fa-hand-holding-dollar'],
        };
    @endphp
    <tr class="align-middle border-bottom">
        <!-- Payment Gateway Branding & Method Name -->
        <td class="py-2 ps-3" style="min-width: 170px;">
            <div class="d-flex align-items-center gap-2">
                <!-- Media Chooser Logo / Brand Icon Box -->
                <div class="position-relative flex-shrink-0" style="width: 32px; height: 32px; border-radius: 5px !important; overflow: hidden; border: 1px solid {{ $badgeTheme['border'] }}; background: {{ $badgeTheme['bg'] }}; display: flex; align-items: center; justify-content: center;">
                    @if(!empty($gatewayLogo))
                        <img src="{{ $gatewayLogo }}" alt="{{ $pStat->method_name }}" style="width: 100%; height: 100%; object-fit: contain; padding: 2px;" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
                        <i class="fas {{ $badgeTheme['icon'] }}" style="color: {{ $badgeTheme['color'] }}; font-size: 13px; display: none;"></i>
                    @else
                        <i class="fas {{ $badgeTheme['icon'] }}" style="color: {{ $badgeTheme['color'] }}; font-size: 13px;"></i>
                    @endif
                </div>

                <div class="min-width-0 flex-grow-1">
                    <strong class="text-dark d-block text-truncate text-capitalize" style="font-size: 12.5px; line-height: 1.3;" title="{{ $pStat->method_name }}">
                        {{ $pStat->method_name }}
                    </strong>
                    <small class="text-muted font-monospace" style="font-size: 10px;">Payment Channel</small>
                </div>
            </div>
        </td>

        <!-- Order / Transaction Volume Count -->
        <td class="text-center text-nowrap py-2" style="width: 120px;">
            <span class="badge bg-light text-dark border font-monospace bn-digit px-2 py-1" style="font-size: 11px; border-radius: 5px !important;">
                <i class="fas fa-receipt me-1 opacity-75"></i>{{ number_format($count) }} Orders
            </span>
        </td>

        <!-- Total Amount Collected -->
        <td class="text-end text-nowrap py-2 pe-3 font-monospace bn-digit" style="width: 140px; font-size: 12.5px;">
            <strong class="text-primary">৳ {{ number_format($amount, 2) }}</strong>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="3" class="text-center py-4 text-muted small">
            <i class="fas fa-wallet fa-2x mb-2 d-block opacity-25"></i>
            <span class="fw-semibold">No payment method statistics recorded in this selected period.</span>
        </td>
    </tr>
@endforelse