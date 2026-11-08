<style>
    /* =========================================
       ABANDONED CART — KAIADMIN CLASSIC UI
    ========================================= */

    .abandoned-cart-row {
        --ac-border: #ebeef2;
        --ac-text: #344054;
        --ac-muted: #8a94a6;
        --ac-primary: #1572e8;
        --ac-radius: 5px;

        vertical-align: middle;
        transition: background-color .18s ease;
    }

    .abandoned-cart-row:hover {
        background-color: #f9fbfd;
    }

    .abandoned-cart-row td {
        padding-top: 12px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--ac-border);
        vertical-align: middle;
        color: var(--ac-text);
        font-size: 12px;
    }

    .abandoned-cart-row .ac-session-id {
        font-size: 11px;
        white-space: nowrap;
        color: var(--ac-muted);
    }

    .abandoned-cart-row .ac-customer-avatar {
        width: 36px;
        height: 36px;
        min-width: 36px;
        border: 1px solid #f2dfb6;
        border-radius: 5px !important;
        font-size: 13px;
    }

    .abandoned-cart-row .ac-customer-name {
        max-width: 210px;
        font-size: 12.5px;
        font-weight: 600;
        color: #344054;
        line-height: 1.4;
    }

    .abandoned-cart-row .ac-customer-contact {
        max-width: 210px;
        font-size: 11px;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    .abandoned-cart-row .ac-item-dropdown {
        max-width: 100%;
    }

    .abandoned-cart-row .ac-item-trigger {
        min-height: 32px;
        max-width: 100%;
        padding: 5px 9px;
        border: 1px solid #e5e9f0;
        border-radius: 5px !important;
        background: #fff;
        color: #344054;
        font-size: 11.5px;
        font-weight: 500;
        box-shadow: none;
    }

    .abandoned-cart-row .ac-item-trigger:hover,
    .abandoned-cart-row .ac-item-trigger:focus {
        background: #f7f9fc;
        border-color: #cbd5e1;
        color: var(--ac-primary);
    }

    .abandoned-cart-row .ac-item-menu {
        min-width: 260px;
        max-width: min(320px, calc(100vw - 32px));
        max-height: 300px;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 8px;
        border: 1px solid var(--ac-border);
        border-radius: 5px !important;
        box-shadow: 0 8px 25px rgba(0, 0, 0, .10);
        font-size: 11.5px;
        z-index: 1080;
    }

    .abandoned-cart-row .ac-dropdown-heading {
        padding: 7px 5px;
        margin-bottom: 4px;
        border-bottom: 1px solid var(--ac-border);
        color: #7b8494;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .4px;
    }

    .abandoned-cart-row .ac-dropdown-item {
        padding: 9px 5px;
        border-bottom: 1px solid #f0f2f5;
    }

    .abandoned-cart-row .ac-dropdown-item:last-child {
        border-bottom: 0;
    }

    .abandoned-cart-row .ac-product-name {
        max-width: 250px;
        color: #344054;
        font-size: 12px;
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    .abandoned-cart-row .ac-variant {
        color: #8a94a6;
        font-size: 10.5px;
        overflow-wrap: anywhere;
    }

    .abandoned-cart-row .ac-item-meta {
        margin-top: 5px;
        color: #667085;
        font-size: 11px;
    }

    .abandoned-cart-row .ac-price {
        color: #16a36a;
        font-weight: 700;
        white-space: nowrap;
    }

    .abandoned-cart-row .ac-cart-value {
        color: #344054;
        font-size: 13px;
        font-weight: 700;
        white-space: nowrap;
    }

    .abandoned-cart-row .ac-subtitle {
        display: block;
        margin-top: 3px;
        color: #98a2b3;
        font-size: 10.5px;
    }

    .abandoned-cart-row .ac-status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        padding: 5px 8px;
        border-radius: 5px !important;
        font-family: inherit;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .2px;
        white-space: nowrap;
    }

    .abandoned-cart-row .ac-reminder-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 5px 8px;
        border-radius: 5px !important;
        font-size: 10.5px;
        font-weight: 600;
        white-space: nowrap;
    }

    .abandoned-cart-row .ac-date {
        color: #344054;
        font-size: 11.5px;
        font-weight: 600;
        white-space: nowrap;
    }

    .abandoned-cart-row .ac-time {
        display: block;
        margin-top: 3px;
        color: #98a2b3;
        font-size: 10.5px;
        white-space: nowrap;
    }

    .abandoned-cart-row .ac-actions {
        gap: 5px !important;
        white-space: nowrap;
    }

    .abandoned-cart-row .ac-action-btn {
        display: inline-flex;
        width: 32px;
        height: 32px;
        min-width: 32px;
        align-items: center;
        justify-content: center;
        padding: 0;
        border: 1px solid #e5e9f0;
        border-radius: 5px !important;
        background: #fff;
        box-shadow: none;
        transition: all .18s ease;
    }

    .abandoned-cart-row .ac-action-btn:hover:not(:disabled) {
        background: #f5f7fa;
        border-color: #cbd5e1;
    }

    .abandoned-cart-row .ac-action-btn i {
        font-size: 12px;
    }

    .abandoned-cart-row .ac-action-btn:disabled {
        cursor: not-allowed;
        opacity: .65;
    }

    .abandoned-cart-row .ac-checkbox {
        width: 16px;
        height: 16px;
        margin: 0;
        border-radius: 4px !important;
        cursor: pointer;
        vertical-align: middle;
    }

    .abandoned-cart-empty {
        padding: 42px 16px !important;
        text-align: center;
        background: #fff;
    }

    .abandoned-cart-empty .ac-empty-icon {
        display: flex;
        width: 54px;
        height: 54px;
        align-items: center;
        justify-content: center;
        margin: 0 auto 14px;
        border-radius: 50%;
        background: #f2f5f9;
        color: #98a2b3;
        font-size: 22px;
    }

    .abandoned-cart-empty .ac-empty-title {
        margin-bottom: 5px;
        color: #344054;
        font-size: 14px;
        font-weight: 700;
    }

    .abandoned-cart-empty .ac-empty-description {
        max-width: 420px;
        margin: 0 auto;
        color: #8a94a6;
        font-size: 12px;
        line-height: 1.6;
    }
</style>

@forelse($abandonedCarts as $cart)
    @php
        $items = is_array($cart->cart_items) ? $cart->cart_items : json_decode($cart->cart_items, true);
        $itemCount = is_array($items) ? count($items) : 0;
        $sentCount = (int)($cart->reminder_sent_count ?? ($cart->reminders_sent ?? ($cart->reminder_count ?? 0)));
        $lastSent  = $cart->last_reminder_at ?? ($cart->last_reminder_sent_at ?? null);
        $abandonTime = $cart->abandoned_at ? \Carbon\Carbon::parse($cart->abandoned_at) : $cart->created_at;
        $custName = $cart->customer_name ?: 'Guest Shopper';
    @endphp

    <tr id="cart-row-{{ $cart->id }}" class="align-middle abandoned-cart-row">

        {{-- 1. Bulk Selection Checkbox --}}
        <td class="text-center">
            <input type="checkbox"
                   class="form-check-input cart-select-cb ac-checkbox shadow-none"
                   value="{{ $cart->id }}"
                   data-email="{{ $cart->email }}"
                   data-name="{{ $cart->customer_name ?: 'Valued Customer' }}"
                   data-value="{{ number_format((float)($cart->cart_value ?? 0), 2) }}"
                   data-recovered="{{ $cart->is_recovered ? '1' : '0' }}">
        </td>

        {{-- 2. Session ID --}}
        <td class="ps-2 text-center font-monospace bn-digit ac-session-id">
            #{{ $cart->id }}
        </td>

        {{-- 3. Customer Profile & Contact --}}
        <td>
            <div class="d-flex align-items-center gap-2">

                <div class="ac-customer-avatar bg-warning-subtle text-warning-emphasis d-flex align-items-center justify-content-center fw-bold text-uppercase flex-shrink-0">
                    <i class="fas fa-cart-arrow-down"></i>
                </div>

                <div class="min-w-0" style="line-height: 1.4;">

                    <strong class="ac-customer-name d-block text-truncate"
                            title="{{ $custName }}">
                        {{ $custName }}
                    </strong>

                    <small class="ac-customer-contact text-muted font-monospace d-block text-truncate"
                           title="{{ $cart->email }}">
                        <i class="fas fa-envelope me-1"></i>{{ $cart->email }}
                    </small>

                    @if(!empty($cart->phone))
                        <small class="ac-customer-contact text-secondary font-monospace d-block text-truncate">
                            <i class="fas fa-phone-alt me-1 text-muted"></i>{{ $cart->phone }}
                        </small>
                    @endif

                </div>
            </div>
        </td>

        {{-- 4. Cart Items Overview Dropdown --}}
        <td>
            @if($itemCount > 0)
                <div class="dropdown ac-item-dropdown">

                    <button class="btn btn-sm ac-item-trigger dropdown-toggle font-monospace text-truncate"
                            type="button"
                            data-bs-toggle="dropdown"
                            data-bs-display="static">

                        <i class="fas fa-bag-shopping me-1 text-primary"></i>
                        <span>{{ $itemCount }} {{ \Illuminate\Support\Str::plural('item', $itemCount) }}</span>

                    </button>

                    <ul class="dropdown-menu ac-item-menu">

                        <li class="ac-dropdown-heading">
                            <i class="fas fa-list me-1"></i> CART ITEMS
                        </li>

                        @foreach($items as $item)
                            <li class="ac-dropdown-item">

                                <strong class="ac-product-name text-truncate d-block"
                                        title="{{ $item['name'] ?? ($item['title'] ?? 'Product') }}">
                                    {{ $item['name'] ?? ($item['title'] ?? 'Product') }}
                                </strong>

                                @if(!empty($item['color']) || !empty($item['size']))
                                    <div class="ac-variant font-monospace mt-1">
                                        Variant: {{ trim(($item['color'] ?? '') . ' ' . ($item['size'] ?? '')) }}
                                    </div>
                                @endif

                                <div class="ac-item-meta d-flex justify-content-between align-items-center font-monospace">

                                    <span>
                                        Qty: <strong class="text-dark">{{ $item['quantity'] ?? 1 }}</strong>
                                    </span>

                                    <span class="ac-price">
                                        ৳ {{ number_format((float)($item['price'] ?? 0), 2) }}
                                    </span>

                                </div>

                            </li>
                        @endforeach

                    </ul>
                </div>

            @else

                <span class="badge bg-light text-secondary border font-monospace px-2 py-1"
                      style="font-size: 10px; border-radius: 5px !important;">
                    Empty Snapshot
                </span>

            @endif
        </td>

        {{-- 5. Cart Value --}}
        <td>
            <div class="ac-cart-value font-monospace bn-digit">
                ৳ {{ number_format((float)($cart->cart_value ?? 0), 2) }}
            </div>

            <small class="ac-subtitle">
                Checkout Subtotal
            </small>
        </td>

        {{-- 6. Recovery Status --}}
        <td class="text-center">

            @if($cart->is_recovered)

                <span class="badge bg-success-subtle text-success border border-success-subtle ac-status-badge">
                    <i class="fas fa-circle-check"></i>
                    RECOVERED
                </span>

            @else

                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle ac-status-badge">
                    <i class="fas fa-clock text-warning"></i>
                    PENDING
                </span>

            @endif

        </td>

        {{-- 7. Reminders Sent Counter & Interval --}}
        <td class="text-center font-monospace bn-digit" style="line-height: 1.4;">

            @if($sentCount > 0)

                <span class="badge bg-info-subtle text-info border border-info-subtle ac-reminder-badge">
                    <i class="fas fa-paper-plane"></i>
                    {{ $sentCount }} sent
                </span>

                @if($lastSent)
                    <small class="ac-time">
                        {{ \Carbon\Carbon::parse($lastSent)->diffForHumans() }}
                    </small>
                @endif

            @else

                <span class="badge bg-light text-secondary border ac-reminder-badge">
                    0 sent
                </span>

            @endif

        </td>

        {{-- 8. Abandoned Date & Time --}}
        <td class="font-monospace bn-digit" style="line-height: 1.4;">

            <span class="ac-date d-block">
                {{ $abandonTime ? $abandonTime->format('M d, Y') : '—' }}
            </span>

            <small class="ac-time">
                {{ $abandonTime ? $abandonTime->format('h:i A') : '' }}
            </small>

        </td>

        {{-- 9. Row Actions --}}
        <td class="text-end pe-3">

            <div class="d-inline-flex align-items-center ac-actions">

                {{-- Quick Recovery Link in New Tab --}}
                <a href="{{ $cart->recovery_url ?: 'javascript:void(0)' }}"
                   target="{{ $cart->recovery_url ? '_blank' : '_self' }}"
                   class="btn btn-sm ac-action-btn text-secondary"
                   title="Open Recovery Link in New Tab">

                    <i class="fas fa-arrow-up-right-from-square"></i>

                </a>

                {{-- Direct Send Reminder AJAX --}}
                @if(!$cart->is_recovered)

                    <button type="button"
                            class="btn btn-sm ac-action-btn text-warning"
                            onclick="sendCartRecoveryDirectAjax({{ $cart->id }}, '{{ addslashes($cart->email) }}')"
                            title="Send Recovery Reminder">

                        <i class="fas fa-paper-plane text-warning"></i>

                    </button>

                @else

                    <button type="button"
                            class="btn btn-sm ac-action-btn text-success"
                            disabled
                            title="Cart Already Recovered">

                        <i class="fas fa-check text-success"></i>

                    </button>

                @endif

                {{-- Delete Cart AJAX --}}
                <button type="button"
                        class="btn btn-sm ac-action-btn text-danger"
                        onclick="deleteCartAjax({{ $cart->id }}, '{{ addslashes($cart->email) }}')"
                        title="Delete Session Record">

                    <i class="fas fa-trash-can"></i>

                </button>

            </div>

        </td>

    </tr>

@empty

    <tr class="abandoned-cart-empty">
        <td colspan="9">

            <div class="ac-empty-icon">
                <i class="fas fa-cart-shopping"></i>
            </div>

            <div class="ac-empty-title">
                No Shopping Sessions Found
            </div>

            <p class="ac-empty-description">
                No abandoned cart sessions match your current filters. Try adjusting your search criteria.
            </p>

        </td>
    </tr>

@endforelse