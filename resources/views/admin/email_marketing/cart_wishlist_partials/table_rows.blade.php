@forelse($abandonedCarts as $cart)
    @php
        $items = is_array($cart->cart_items) ? $cart->cart_items : json_decode($cart->cart_items, true);
        $itemCount = is_array($items) ? count($items) : 0;
        $sentCount = (int)($cart->reminder_sent_count ?? ($cart->reminders_sent ?? ($cart->reminder_count ?? 0)));
        $lastSent  = $cart->last_reminder_at ?? ($cart->last_reminder_sent_at ?? null);
        $abandonTime = $cart->abandoned_at ? \Carbon\Carbon::parse($cart->abandoned_at) : $cart->created_at;
        $custName = $cart->customer_name ?: 'Guest Shopper';
    @endphp
    <tr id="cart-row-{{ $cart->id }}" class="align-middle">
        {{-- 1. Bulk Selection Checkbox --}}
        <td class="text-center">
            <input type="checkbox" 
                   class="form-check-input cart-select-cb mt-0 shadow-none" 
                   value="{{ $cart->id }}" 
                   data-email="{{ $cart->email }}" 
                   data-name="{{ $cart->customer_name ?: 'Valued Customer' }}"
                   data-value="{{ number_format((float)($cart->cart_value ?? 0), 2) }}"
                   data-recovered="{{ $cart->is_recovered ? '1' : '0' }}"
                   style="cursor: pointer; width: 15px; height: 15px; border-radius: 5px !important;">
        </td>

        {{-- 2. Session ID --}}
        <td class="ps-2 text-center font-monospace bn-digit text-muted" style="font-size: 11px;">
            #{{ $cart->id }}
        </td>

        {{-- 3. Customer Profile & Contact --}}
        <td>
            <div class="d-flex align-items-center gap-2">
                <div class="rounded bg-warning-subtle text-warning-emphasis d-flex align-items-center justify-content-center fw-bold text-uppercase flex-shrink-0 shadow-xs" style="width: 30px; height: 30px; font-size: 11px; border-radius: 5px !important;">
                    <i class="fas fa-cart-arrow-down" style="font-size: 11px;"></i>
                </div>
                <div class="min-w-0" style="line-height: 1.3;">
                    <strong class="text-dark d-block text-truncate" style="font-size: 12px; max-width: 210px;" title="{{ $custName }}">
                        {{ $custName }}
                    </strong>
                    <small class="text-muted font-monospace d-block text-truncate" style="font-size: 10px; max-width: 210px;" title="{{ $cart->email }}">
                        {{ $cart->email }}
                    </small>
                    @if(!empty($cart->phone))
                        <small class="text-secondary font-monospace d-block text-truncate" style="font-size: 10px;">
                            <i class="fas fa-phone-alt me-1 text-muted" style="font-size: 9px;"></i>{{ $cart->phone }}
                        </small>
                    @endif
                </div>
            </div>
        </td>

        {{-- 4. Cart Items Overview Dropdown (Mobile-Safe) --}}
        <td>
            @if($itemCount > 0)
                <div class="dropdown">
                    <button class="btn btn-sm btn-light border dropdown-toggle py-1 px-2 shadow-xs font-monospace text-truncate" type="button" data-bs-toggle="dropdown" data-bs-display="static" style="font-size: 11px; border-radius: 5px !important; max-width: 100%;">
                        <i class="fas fa-bag-shopping me-1 text-primary"></i> 
                        <span>{{ $itemCount }} {{ \Illuminate\Support\Str::plural('item', $itemCount) }}</span>
                    </button>
                    <ul class="dropdown-menu shadow-lg p-2 border" style="font-size: 11px; min-width: 260px; max-height: 280px; overflow-y: auto; border-radius: 5px !important;">
                        <li class="px-1 py-1 border-bottom mb-1 text-muted font-monospace small" style="font-size: 10px;">
                            <i class="fas fa-list me-1"></i> CART ITEMS BREAKDOWN:
                        </li>
                        @foreach($items as $item)
                            <li class="py-1 border-bottom">
                                <strong class="text-dark text-truncate d-block" style="max-width: 240px; font-size: 11.5px;" title="{{ $item['name'] ?? ($item['title'] ?? 'Product') }}">
                                    {{ $item['name'] ?? ($item['title'] ?? 'Product') }}
                                </strong>
                                @if(!empty($item['color']) || !empty($item['size']))
                                    <div class="text-muted font-monospace" style="font-size: 10px;">
                                        Variant: {{ trim(($item['color'] ?? '') . ' ' . ($item['size'] ?? '')) }}
                                    </div>
                                @endif
                                <div class="text-muted d-flex justify-content-between align-items-center mt-1 font-monospace" style="font-size: 10.5px;">
                                    <span>Qty: <strong class="text-dark">{{ $item['quantity'] ?? 1 }}</strong></span>
                                    <span class="fw-bold text-success">৳ {{ number_format((float)($item['price'] ?? 0), 2) }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @else
                <span class="badge bg-light text-muted border font-monospace px-2 py-0" style="font-size: 9.5px; border-radius: 5px !important;">
                    Empty Snapshot
                </span>
            @endif
        </td>

        {{-- 5. Cart Value --}}
        <td>
            <div class="fw-bold text-dark font-monospace bn-digit" style="font-size: 12.5px;">
                ৳ {{ number_format((float)($cart->cart_value ?? 0), 2) }}
            </div>
            <small class="text-muted font-monospace" style="font-size: 10px;">Checkout subtotal</small>
        </td>

        {{-- 6. Recovery Status --}}
        <td class="text-center">
            @if($cart->is_recovered)
                <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-2 py-1" style="border-radius: 5px !important; font-size: 9.5px;">
                    <i class="fas fa-circle-check me-1"></i> RECOVERED
                </span>
            @else
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle font-monospace px-2 py-1" style="border-radius: 5px !important; font-size: 9.5px;">
                    <i class="fas fa-clock me-1 text-warning"></i> PENDING
                </span>
            @endif
        </td>

        {{-- 7. Reminders Sent Counter & Interval --}}
        <td class="text-center font-monospace bn-digit" style="line-height: 1.3;">
            @if($sentCount > 0)
                <span class="badge bg-info-subtle text-info border border-info-subtle font-monospace px-2 py-0" style="border-radius: 5px !important; font-size: 10px;">
                    <i class="fas fa-paper-plane me-1"></i> {{ $sentCount }} sent
                </span>
                @if($lastSent)
                    <small class="text-muted d-block font-monospace" style="font-size: 9.5px;">
                        {{ \Carbon\Carbon::parse($lastSent)->diffForHumans() }}
                    </small>
                @endif
            @else
                <span class="badge bg-light text-muted border font-monospace px-2 py-0" style="border-radius: 5px !important; font-size: 10px;">
                    0 sent
                </span>
            @endif
        </td>

        {{-- 8. Abandoned Date & Time --}}
        <td class="font-monospace bn-digit" style="line-height: 1.3;">
            <span class="text-dark d-block" style="font-size: 11px;">
                {{ $abandonTime ? $abandonTime->format('M d, Y') : '—' }}
            </span>
            <small class="text-muted" style="font-size: 9.5px;">
                {{ $abandonTime ? $abandonTime->format('h:i A') : '' }}
            </small>
        </td>

        {{-- 9. Row Actions --}}
        <td class="text-end pe-3">
            <div class="d-inline-flex align-items-center gap-1">
                {{-- Quick Recovery Link in New Tab --}}
                <a href="{{ $cart->recovery_url ?: 'javascript:void(0)' }}" 
                   target="{{ $cart->recovery_url ? '_blank' : '_self' }}" 
                   class="btn btn-sm btn-light border p-1 text-secondary shadow-xs d-inline-flex align-items-center justify-content-center" 
                   style="width: 26px; height: 26px; border-radius: 5px !important;" 
                   title="Open Cart Recovery Link in New Tab">
                    <i class="fas fa-arrow-up-right-from-square" style="font-size: 10px;"></i>
                </a>

                {{-- Direct Send Reminder AJAX --}}
                @if(!$cart->is_recovered)
                    <button type="button" 
                            class="btn btn-sm btn-light border p-1 text-warning shadow-xs d-inline-flex align-items-center justify-content-center" 
                            style="width: 26px; height: 26px; border-radius: 5px !important;" 
                            onclick="sendCartRecoveryDirectAjax({{ $cart->id }}, '{{ addslashes($cart->email) }}')" 
                            title="Send Instant Recovery Reminder">
                        <i class="fas fa-paper-plane text-warning" style="font-size: 10px;"></i>
                    </button>
                @else
                    <button type="button" 
                            class="btn btn-sm btn-light border p-1 text-success shadow-xs d-inline-flex align-items-center justify-content-center" 
                            style="width: 26px; height: 26px; border-radius: 5px !important;" 
                            disabled 
                            title="Cart already converted">
                        <i class="fas fa-check text-success" style="font-size: 10px;"></i>
                    </button>
                @endif

                {{-- Delete Cart AJAX --}}
                <button type="button" 
                        class="btn btn-sm btn-light border p-1 text-danger shadow-xs d-inline-flex align-items-center justify-content-center" 
                        style="width: 26px; height: 26px; border-radius: 5px !important;" 
                        onclick="deleteCartAjax({{ $cart->id }}, '{{ addslashes($cart->email) }}')" 
                        title="Delete Session Record">
                    <i class="fas fa-trash-can" style="font-size: 10px;"></i>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="text-center py-5 text-muted small">
            <i class="fas fa-cart-shopping fa-2x mb-2 d-block text-secondary opacity-25"></i>
            <span class="fw-semibold text-dark d-block mb-1">No shopping sessions found</span>
            <span>No abandoned cart sessions match your selected filter criteria. Try adjusting the parameters.</span>
        </td>
    </tr>
@endforelse