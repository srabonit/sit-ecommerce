@php
    // Safe avatar resolver supporting Global Media Library paths
    $resolveAvatar = function($path, $fallback = '') {
        if (empty($path)) return $fallback;
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path, $fallback);
        }
        if (function_exists('show_image')) return show_image($path, $fallback);
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        return asset(ltrim(str_replace(['public_html/', 'public/'], '', $path), '/'));
    };
@endphp

@forelse($customers as $c)
    @php
        $spent = (float)($c->total_spent ?? 0);
        $paid  = (float)($c->total_paid ?? 0);
        $due   = max(0, $spent - $paid);

        // Resolve customer avatar from media chooser or generate stylish initials
        $rawAvatar = $c->avatar ?? ($c->photo ?? ($c->image ?? null));
        $avatarUrl = $resolveAvatar($rawAvatar);
        $nameStr   = trim($c->customer_name ?? 'Guest Customer');
        $nameParts = array_values(array_filter(explode(' ', $nameStr)));
        $initials  = strtoupper(substr($nameParts[0] ?? 'G', 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
    @endphp
    <tr class="align-middle border-bottom">
        <!-- Customer Profile & Contact Details -->
        <td class="py-2 ps-3" style="min-width: 200px; max-width: 320px;">
            <div class="d-flex align-items-center gap-2">
                <!-- Avatar / Media Chooser Thumbnail with Initials Fallback -->
                <div class="position-relative flex-shrink-0" style="width: 34px; height: 34px; border-radius: 5px !important; overflow: hidden; border: 1px solid #cbd5e1; background: #f8fafc; display: flex; align-items: center; justify-content: center;">
                    @if(!empty($avatarUrl))
                        <img src="{{ $avatarUrl }}" alt="{{ $nameStr }}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <span class="fw-bold font-monospace text-primary" style="font-size: 11px; display: none; width: 100%; height: 100%; align-items: center; justify-content: center; background: #e0f2fe;">
                            {{ $initials }}
                        </span>
                    @else
                        <span class="fw-bold font-monospace text-primary" style="font-size: 11px; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #e0f2fe;">
                            {{ $initials }}
                        </span>
                    @endif
                </div>

                <div class="min-width-0 flex-grow-1">
                    <div class="d-flex align-items-center gap-1 flex-wrap">
                        <strong class="text-dark d-block text-truncate" style="font-size: 12.5px; line-height: 1.3;" title="{{ $nameStr }}">
                            {{ $nameStr }}
                        </strong>
                        @if(!empty($c->user_id))
                            <span class="badge bg-light text-primary border font-monospace px-1 py-0" style="font-size: 9.5px; border-radius: 5px !important;">
                                #{{ $c->user_id }}
                            </span>
                        @else
                            <span class="badge bg-light text-muted border font-monospace px-1 py-0" style="font-size: 9.5px; border-radius: 5px !important;">
                                Guest
                            </span>
                        @endif
                    </div>
                    <small class="text-muted font-monospace bn-digit d-block text-truncate" style="font-size: 10.5px;">
                        <i class="fas fa-phone-volume me-1 opacity-75" style="font-size: 9px;"></i>{{ $c->customer_phone ?? 'N/A' }}
                    </small>
                </div>
            </div>
        </td>

        <!-- Total Orders Placed -->
        <td class="text-center text-nowrap py-2" style="width: 130px;">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace bn-digit px-2 py-1" style="font-size: 11px; border-radius: 5px !important;">
                <i class="fas fa-bag-shopping me-1"></i>{{ number_format((int)($c->total_orders_placed ?? 0)) }} Orders
            </span>
        </td>

        <!-- Total Lifetime Spent -->
        <td class="text-end text-nowrap font-monospace bn-digit py-2" style="width: 140px; font-size: 12px;">
            <strong class="text-dark">৳ {{ number_format($spent, 2) }}</strong>
        </td>

        <!-- Total Paid Amount & Balance Status -->
        <td class="text-end text-nowrap font-monospace bn-digit py-2" style="width: 140px; font-size: 12px;">
            <strong class="text-success d-block">৳ {{ number_format($paid, 2) }}</strong>
            @if($due > 0)
                <small class="text-danger font-monospace bn-digit d-block" style="font-size: 10px;">
                    Due: ৳ {{ number_format($due, 2) }}
                </small>
            @endif
        </td>

        <!-- Last Order Date & Time -->
        <td class="text-end text-nowrap py-2 pe-3" style="width: 150px; font-size: 11.5px; line-height: 1.3;">
            <span class="text-dark fw-semibold d-block bn-digit">
                {{ date('d M Y', strtotime($c->last_order_date)) }}
            </span>
            <small class="text-muted bn-digit font-monospace" style="font-size: 10px;">
                {{ date('h:i A', strtotime($c->last_order_date)) }}
            </small>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="text-center py-5 text-muted small">
            <i class="fas fa-user-slash fa-2x mb-2 d-block opacity-25"></i>
            <span class="fw-semibold">No customer purchases recorded in this selected period.</span>
        </td>
    </tr>
@endforelse