@php
    $cart = $cart ?? session()->get('cart', []);
    $total = 0;

    static $drawerItemsSettingsCache = null;
    if ($drawerItemsSettingsCache === null) {
        $drawerItemsSettingsCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
            return DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $drawerSettings = $drawerItemsSettingsCache ?? [];

    $drawerItemBg        = $drawerSettings['color_cart_item_bg'] ?? '#ffffff';
    $drawerItemBorder    = $drawerSettings['color_cart_item_border'] ?? '#edf2f7';
    $drawerItemImgBg     = $drawerSettings['color_cart_item_img_bg'] ?? '#f8fafc';
    $drawerItemImgBorder = $drawerSettings['color_cart_item_img_border'] ?? '#e2e8f0';
    $drawerItemTitle     = $drawerSettings['color_cart_item_title'] ?? '#0f172a';
    $drawerItemTitleHov  = $drawerSettings['color_cart_item_title_hover'] ?? '#2563eb';
    $drawerVariantBg     = $drawerSettings['color_cart_variant_bg'] ?? '#f8fafc';
    $drawerVariantText   = $drawerSettings['color_cart_variant_text'] ?? '#64748b';
    $drawerVariantBorder = $drawerSettings['color_cart_variant_border'] ?? '#e2e8f0';
    $drawerSkuText       = $drawerSettings['color_cart_sku_text'] ?? '#94a3b8';
    $drawerItemPrice     = $drawerSettings['color_cart_item_price'] ?? '#0f172a';
    $drawerQtyBtnBg      = $drawerSettings['color_cart_qty_btn_bg'] ?? '#f8fafc';
    $drawerQtyBtnText    = $drawerSettings['color_cart_qty_btn_text'] ?? '#334155';
    $drawerQtyBtnBorder  = $drawerSettings['color_cart_qty_btn_border'] ?? '#e2e8f0';
    $drawerQtyBtnHovBg   = $drawerSettings['color_cart_qty_btn_hover_bg'] ?? '#e2e8f0';
    $drawerRemoveBtn     = $drawerSettings['color_cart_remove_btn'] ?? '#ef4444';
    $drawerRemoveBtnHov  = $drawerSettings['color_cart_remove_btn_hover'] ?? '#dc2626';
    $drawerEmptyIcon     = $drawerSettings['color_cart_empty_icon'] ?? '#94a3b8';
    $drawerEmptyBtnBg    = $drawerSettings['color_cart_empty_btn_bg'] ?? '#2563eb';
    $drawerEmptyBtnText  = $drawerSettings['color_cart_empty_btn_text'] ?? '#ffffff';
    $drawerEmptyBtnHovBg = $drawerSettings['color_cart_empty_btn_hover_bg'] ?? '#1d4ed8';

    $resolveDrawerImg = function($path, $fallback = 'https://placehold.co/100x100?text=No+Image') {
        if (empty($path) || $path === 'undefined' || $path === 'null') {
            return $fallback;
        }
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path, $fallback);
        }
        if (function_exists('show_image')) {
            return show_image($path, $fallback);
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        return asset(ltrim($path, '/'));
    };

    $cartProductIds = [];
    foreach($cart as $cItem) {
        $rawPrice = $cItem['price'] ?? 0;
        if (is_string($rawPrice)) $rawPrice = preg_replace('/[^0-9.]/', '', $rawPrice);
        $total += ((float)$rawPrice * (int)($cItem['quantity'] ?? 1));

        $pId = (int)($cItem['id'] ?? ($cItem['product_id'] ?? 0));
        if ($pId > 0) $cartProductIds[] = $pId;
    }

    $cartCategoryIds = [];
    if (!empty($cartProductIds) && \Illuminate\Support\Facades\Schema::hasTable('products')) {
        $cartCategoryIds = \Illuminate\Support\Facades\DB::table('products')
            ->whereIn('id', $cartProductIds)
            ->pluck('category_id')
            ->filter()
            ->map(fn($id) => (int)$id)
            ->toArray();
    }

    $appliedCoupon = session('applied_coupon');
    $appliedVoucher = session('applied_voucher');
    $couponCode = is_array($appliedCoupon) ? ($appliedCoupon['code'] ?? null) : $appliedCoupon;
    $voucherCode = is_array($appliedVoucher) ? ($appliedVoucher['code'] ?? ($appliedVoucher['voucher_code'] ?? null)) : $appliedVoucher;

    $calc = class_exists(\App\Services\DiscountEngineService::class)
        ? \App\Services\DiscountEngineService::calculateCartDiscounts($total, $cart, $couponCode, $voucherCode, auth()->id(), 0.0)
        : ['coupon_discount' => 0, 'voucher_discount' => 0, 'total_discount' => 0, 'final_payable' => $total];

    $couponDiscount = (float)($calc['coupon_discount'] ?? 0);
    $voucherDiscount = (float)($calc['voucher_discount'] ?? 0);
    $totalDiscount = (float)($calc['total_discount'] ?? 0);

    $drawerAvailableOffers = collect();
    $userId = auth()->id();
    $now = \Carbon\Carbon::now();

    // ১. কাস্টমারের নিজস্ব ওয়ালেট ভাউচার
    if ($userId && \Illuminate\Support\Facades\Schema::hasTable('customer_vouchers') && \Illuminate\Support\Facades\Schema::hasTable('vouchers')) {
        $walletVouchers = \Illuminate\Support\Facades\DB::table('customer_vouchers')
            ->join('vouchers', 'customer_vouchers.voucher_id', '=', 'vouchers.id')
            ->where('customer_vouchers.user_id', $userId)
            ->whereIn('customer_vouchers.status', ['claimed', 'restored'])
            ->where(function ($q) use ($now) {
                $q->whereNull('customer_vouchers.expires_at')->orWhere('customer_vouchers.expires_at', '>=', $now);
            })
            ->select(
                'vouchers.id',
                'vouchers.title',
                'vouchers.voucher_type',
                'vouchers.discount_amount',
                'vouchers.max_discount',
                'vouchers.min_spend',
                'customer_vouchers.voucher_code'
            )
            ->get()
            ->map(function ($item) use ($total) {
                $item->type = 'voucher';
                $item->is_wallet = true;
                $vMin = (float)($item->min_spend ?? 0);
                $item->is_eligible = ($vMin <= 0 || $total >= $vMin);
                $item->eligibility_reason = !$item->is_eligible ? 'Min ৳' . number_format($vMin, 0) : '';
                return $item;
            });
        $drawerAvailableOffers = $drawerAvailableOffers->merge($walletVouchers);
    }

    // ২. উন্মুক্ত ভাউচার ক্যাম্পেইন (টার্গেট ইউজার ফিল্টারসহ)
    if (\Illuminate\Support\Facades\Schema::hasTable('vouchers')) {
        $claimedIds = ($userId && \Illuminate\Support\Facades\Schema::hasTable('customer_vouchers'))
            ? \Illuminate\Support\Facades\DB::table('customer_vouchers')->where('user_id', $userId)->pluck('voucher_id')->toArray()
            : [];

        $vQuery = \Illuminate\Support\Facades\DB::table('vouchers')
            ->where('is_active', 1)
            ->whereNotIn('id', $claimedIds)
            ->where(function ($q) use ($now) {
                $q->whereNull('claim_start')->orWhere('claim_start', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('claim_end')->orWhere('claim_end', '>=', $now);
            });

        if (\Illuminate\Support\Facades\Schema::hasColumn('vouchers', 'target_user_type')) {
            if ($userId) {
                $vQuery->where(function($q) {
                    $q->whereNull('target_user_type')->orWhereIn('target_user_type', ['all', 'registered_only']);
                });
            }
        }

        $campaignVouchers = $vQuery->take(2)->get()->map(function ($item) use ($userId, $total) {
            $item->type = 'voucher_campaign';
            $item->is_wallet = false;
            $item->voucher_code = null;
            $target = $item->target_user_type ?? 'registered_only';
            $vMin = (float)($item->min_spend ?? 0);

            $isEligible = true;
            $reason = '';
            if ($target === 'registered_only' && !$userId) {
                $isEligible = false;
                $reason = 'Login to Claim';
            } elseif ($vMin > 0 && $total < $vMin) {
                $isEligible = false;
                $reason = 'Min ৳' . number_format($vMin, 0);
            }

            $item->is_eligible = $isEligible;
            $item->eligibility_reason = $reason;
            return $item;
        });
        $drawerAvailableOffers = $drawerAvailableOffers->merge($campaignVouchers);
    }

    // ৩. সক্রিয় পাবলিক কুপন কোড (টার্গেট ইউজার, প্রোডাক্ট ও ক্যাটাগরি ম্যাচিং যাচাইসহ)
    if (\Illuminate\Support\Facades\Schema::hasTable('coupons')) {
        $cQuery = \Illuminate\Support\Facades\DB::table('coupons')
            ->where('is_active', 1)
            ->where('is_public', 1)
            ->where(function ($q) use ($now) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('expire_date')->orWhere('expire_date', '>=', $now);
            });

        if (\Illuminate\Support\Facades\Schema::hasColumn('coupons', 'target_user_type')) {
            if ($userId) {
                $cQuery->where(function($q) {
                    $q->whereNull('target_user_type')->orWhereIn('target_user_type', ['all', 'registered_only']);
                });
            }
        }

        $storeCoupons = $cQuery->take(3)->get()->map(function ($c) use ($userId, $total, $cartProductIds, $cartCategoryIds) {
            $c->type = 'coupon';
            $c->is_wallet = false;
            $minReq = (float)($c->min_order_amount ?? ($c->min_spend ?? 0));
            $target = $c->target_user_type ?? 'all';
            $appTo  = $c->applicable_to ?? 'all';

            $isEligible = true;
            $reason = '';

            if ($target === 'registered_only' && !$userId) {
                $isEligible = false;
                $reason = 'Login Required';
            } elseif ($target === 'guest_only' && $userId) {
                $isEligible = false;
                $reason = 'Guest Only';
            } elseif ($minReq > 0 && $total < $minReq) {
                $isEligible = false;
                $reason = 'Min ৳' . number_format($minReq, 0);
            } elseif ($appTo === 'specific_products') {
                $includedP = \Illuminate\Support\Facades\Schema::hasTable('coupon_products')
                    ? \Illuminate\Support\Facades\DB::table('coupon_products')->where('coupon_id', $c->id)->where('is_excluded', 0)->pluck('product_id')->toArray()
                    : [];
                if (empty($includedP) && !empty($c->product_ids)) {
                    $includedP = is_array($c->product_ids) ? $c->product_ids : (json_decode($c->product_ids, true) ?? []);
                }
                if (!empty($includedP) && empty(array_intersect($cartProductIds, array_map('intval', $includedP)))) {
                    $isEligible = false;
                    $reason = 'Item Mismatch';
                }
            } elseif ($appTo === 'specific_categories') {
                $includedC = \Illuminate\Support\Facades\Schema::hasTable('coupon_categories')
                    ? \Illuminate\Support\Facades\DB::table('coupon_categories')->where('coupon_id', $c->id)->where('is_excluded', 0)->pluck('category_id')->toArray()
                    : [];
                if (empty($includedC) && !empty($c->category_ids)) {
                    $includedC = is_array($c->category_ids) ? $c->category_ids : (json_decode($c->category_ids, true) ?? []);
                }
                if (!empty($includedC) && empty(array_intersect($cartCategoryIds, array_map('intval', $includedC)))) {
                    $isEligible = false;
                    $reason = 'Category Mismatch';
                }
            }

            $c->is_eligible = $isEligible;
            $c->eligibility_reason = $reason;
            return $c;
        });

        $drawerAvailableOffers = $drawerAvailableOffers->merge($storeCoupons);
    }
@endphp

<style>
    :root {
        --cd-item-bg: {{ $drawerItemBg }};
        --cd-item-border: {{ $drawerItemBorder }};
        --cd-item-img-bg: {{ $drawerItemImgBg }};
        --cd-item-img-border: {{ $drawerItemImgBorder }};
        --cd-item-title: {{ $drawerItemTitle }};
        --cd-item-title-hover: {{ $drawerItemTitleHov }};
        --cd-variant-bg: {{ $drawerVariantBg }};
        --cd-variant-text: {{ $drawerVariantText }};
        --cd-variant-border: {{ $drawerVariantBorder }};
        --cd-sku-text: {{ $drawerSkuText }};
        --cd-item-price: {{ $drawerItemPrice }};
        --cd-qty-btn-bg: {{ $drawerQtyBtnBg }};
        --cd-qty-btn-text: {{ $drawerQtyBtnText }};
        --cd-qty-btn-border: {{ $drawerQtyBtnBorder }};
        --cd-qty-btn-hover-bg: {{ $drawerQtyBtnHovBg }};
        --cd-remove-btn: {{ $drawerRemoveBtn }};
        --cd-remove-btn-hover: {{ $drawerRemoveBtnHov }};
        --cd-empty-icon: {{ $drawerEmptyIcon }};
        --cd-empty-btn-bg: {{ $drawerEmptyBtnBg }};
        --cd-empty-btn-text: {{ $drawerEmptyBtnText }};
        --cd-empty-btn-hover-bg: {{ $drawerEmptyBtnHovBg }};
    }

    .cd-item-card {
        background-color: var(--cd-item-bg) !important;
        border: 1px solid var(--cd-item-border) !important;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .cd-item-card:hover {
        border-color: #cbd5e1 !important;
    }
    .cd-img-box {
        background-color: var(--cd-item-img-bg) !important;
        border: 1px solid var(--cd-item-img-border) !important;
    }
    .cd-item-title a {
        color: var(--cd-item-title) !important;
        transition: color 0.15s ease;
    }
    .cd-item-title a:hover {
        color: var(--cd-item-title-hover) !important;
    }
    .cd-variant-badge {
        background-color: var(--cd-variant-bg) !important;
        color: var(--cd-variant-text) !important;
        border: 1px solid var(--cd-variant-border) !important;
    }
    .cd-sku-text {
        color: var(--cd-sku-text) !important;
    }
    .cd-qty-group .btn {
        background-color: var(--cd-qty-btn-bg) !important;
        color: var(--cd-qty-btn-text) !important;
        border-color: var(--cd-qty-btn-border) !important;
        transition: background-color 0.15s ease;
    }
    .cd-qty-group .btn:hover {
        background-color: var(--cd-qty-btn-hover-bg) !important;
    }
    .cd-qty-group input {
        border-color: var(--cd-qty-btn-border) !important;
        color: var(--cd-qty-btn-text) !important;
        background-color: var(--cd-item-bg) !important;
    }
    .cd-price-text {
        color: var(--cd-item-price) !important;
    }
    .cd-remove-btn {
        color: var(--cd-remove-btn) !important;
        transition: color 0.15s ease, transform 0.15s ease;
    }
    .cd-remove-btn:hover {
        color: var(--cd-remove-btn-hover) !important;
        transform: scale(1.15);
    }
    .cd-empty-icon {
        color: var(--cd-empty-icon) !important;
    }
    .cd-empty-btn {
        background-color: var(--cd-empty-btn-bg) !important;
        border-color: var(--cd-empty-btn-bg) !important;
        color: var(--cd-empty-btn-text) !important;
        transition: all 0.2s ease;
    }
    .cd-empty-btn:hover {
        background-color: var(--cd-empty-btn-hover-bg) !important;
        border-color: var(--cd-empty-btn-hover-bg) !important;
        color: var(--cd-empty-btn-text) !important;
    }

    .drawer-offer-chip {
        border-radius: 6px;
        padding: 5px 8px;
        font-size: 10px;
        border: 1px dashed #cbd5e1;
        background: #ffffff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 6px;
    }
    .drawer-offer-chip.eligible {
        border-color: #93c5fd;
        background: #f8faff;
    }
    .drawer-offer-chip.ineligible {
        opacity: 0.65;
        background: #f8fafc;
    }
</style>

@if(!empty($cart) && count($cart) > 0)
    <div class="d-flex flex-column gap-2 px-2 py-1">
        @foreach($cart as $key => $item)
            @php
                $itemPrice = $item['price'] ?? 0;
                if (is_string($itemPrice)) {
                    $itemPrice = preg_replace('/[^0-9.]/', '', $itemPrice);
                }
                $itemTotal = (float)$itemPrice * (int)($item['quantity'] ?? 1);
                $itemImg = $resolveDrawerImg($item['image'] ?? null);
                $variantLabel = $item['variant_name'] ?? (!empty($item['color']) || !empty($item['size']) ? trim(($item['color'] ?? '') . ' / ' . ($item['size'] ?? '')) : null);
            @endphp
            <div class="d-flex gap-2 align-items-center p-2 rounded-3 position-relative shadow-xs cd-item-card" id="drawer-item-{{ $key }}">
                <div class="rounded-2 p-1 flex-shrink-0 d-flex align-items-center justify-content-center cd-img-box" style="width: 48px; height: 48px;">
                    <img src="{{ $itemImg }}" alt="{{ $item['title'] ?? 'Product' }}" class="img-fluid object-fit-contain rounded" style="max-height: 100%; max-width: 100%;" loading="lazy" onerror="this.onerror=null; this.src='https://placehold.co/100x100?text=No+Image';">
                </div>

                <div class="flex-grow-1 min-w-0 px-1" style="overflow: hidden;">
                    <h6 class="fw-bold text-truncate mb-1 cd-item-title" style="font-size: 11.5px; line-height: 1.25;" title="{{ $item['title'] ?? '' }}">
                        <a href="{{ route('product.details', $item['slug'] ?? ($item['id'] ?? $key)) }}" class="text-decoration-none">
                            {{ $item['title'] ?? 'Product' }}
                        </a>
                    </h6>
                    
                    @if(!empty($variantLabel))
                        <span class="badge small mb-1 cd-variant-badge" style="font-size: 8.5px; padding: 1px 4px;">{{ $variantLabel }}</span>
                    @endif

                    @if(!empty($item['sku']))
                        <small class="d-block font-monospace cd-sku-text" style="font-size: 8.5px; line-height: 1;">SKU: {{ $item['sku'] }}</small>
                    @endif

                    <div class="d-flex align-items-center justify-content-between mt-1 gap-1">
                        <div class="input-group input-group-sm flex-shrink-0 cd-qty-group" style="width: 68px; height: 22px;">
                            <button class="btn border p-0 d-flex align-items-center justify-content-center" type="button" onclick="updateCartDrawerQty('{{ $key }}', 'dec')" style="width: 20px; font-size: 10px;">-</button>
                            <input type="text" class="form-control text-center p-0 fw-bold border-top border-bottom bn-digit" value="{{ $item['quantity'] ?? 1 }}" readonly style="font-size: 11px; height: 22px;">
                            <button class="btn border p-0 d-flex align-items-center justify-content-center" type="button" onclick="updateCartDrawerQty('{{ $key }}', 'inc')" style="width: 20px; font-size: 10px;">+</button>
                        </div>

                        <div class="text-end flex-grow-1 ps-1">
                            <strong class="fw-bold bn-digit cd-price-text" style="font-size: 12px;">৳{{ number_format($itemTotal, 2) }}</strong>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-link p-1 flex-shrink-0 text-decoration-none ms-1 cd-remove-btn" onclick="removeCartDrawerItem('{{ $key }}')" title="Remove">
                    <i class="far fa-trash-alt" style="font-size: 11px;"></i>
                </button>
            </div>
        @endforeach

        <!-- =========================================================================
             SELF-CONTAINED COUPON & VOUCHER BOX (INLINE SHORT MESSAGE BELOW BOX)
             ========================================================================= -->
        <div class="drawer-coupon-box p-2.5 rounded-3 bg-light border mt-2">
            <div class="d-flex justify-content-between align-items-center mb-1.5">
                <span class="small fw-bold text-dark d-flex align-items-center gap-1" style="font-size: 11.5px;">
                    <i class="fas fa-ticket-alt text-primary"></i> Coupon &amp; Voucher
                </span>
                @auth
                    <a href="{{ route('user.coupons') }}" target="_blank" class="small text-decoration-none fw-semibold" style="font-size: 10px; color: var(--cd-item-title-hover, #2563eb);">
                        <i class="fas fa-wallet me-0.5"></i> Wallet
                    </a>
                @endauth
            </div>

            <!-- Coupon Input & Self-Contained Apply Button -->
            <div class="input-group input-group-sm shadow-2xs">
                <input type="text" id="drawer-coupon-input" class="form-control form-control-sm text-uppercase font-monospace bg-white border drawer-coupon-input" placeholder="Enter Coupon Code" value="{{ $couponCode ?? '' }}" style="font-size: 11px; height: 32px;" oninput="var m=this.closest('.drawer-coupon-box').querySelector('#drawer-coupon-inline-msg'); if(m) m.classList.add('d-none');" onkeydown="if(event.key==='Enter'){event.preventDefault(); this.nextElementSibling.click();}">
                <button type="button" id="btn-drawer-apply-coupon" class="btn btn-primary px-3 fw-bold" style="font-size: 11px; height: 32px;"
                    onclick="(function(btn){
                        var box = btn.closest('.drawer-coupon-box');
                        var input = box ? box.querySelector('input') : document.getElementById('drawer-coupon-input');
                        var code = input ? input.value.trim() : '';
                        var showInlineMsg = function(msg, ok) {
                            var msgEl = box ? box.querySelector('#drawer-coupon-inline-msg') : document.getElementById('drawer-coupon-inline-msg');
                            if (msgEl) {
                                msgEl.innerText = msg;
                                msgEl.style.color = ok ? '#10b981' : '#ef4444';
                                msgEl.classList.remove('d-none');
                            }
                        };
                        if (!code) { showInlineMsg('Please enter a valid coupon code.', false); return; }
                        var oldTxt = btn.innerHTML;
                        btn.disabled = true;
                        btn.innerHTML = '<span class=&quot;spinner-border spinner-border-sm&quot;></span>';
                        var csrf = document.querySelector('meta[name=&quot;csrf-token&quot;]') ? document.querySelector('meta[name=&quot;csrf-token&quot;]').getAttribute('content') : '{{ csrf_token() }}';
                        fetch('{{ route('cart.coupon.apply') }}', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
                            body: JSON.stringify({coupon_code: code})
                        }).then(function(r){
                            return r.json().then(function(d){ if(!r.ok) throw new Error(d.message || 'Invalid coupon code. Please check and try again.'); return d; });
                        }).then(function(d){
                            showInlineMsg(d.message || 'Coupon applied successfully!', true);
                            var subEl = document.getElementById('drawer-subtotal-calc'); if(subEl && d.subtotal) subEl.innerText = d.subtotal;
                            var disEl = document.getElementById('drawer-discount-calc'); if(disEl) disEl.innerText = parseFloat(d.total_discount || 0).toFixed(2);
                            var grdEl = document.getElementById('drawer-grandtotal-calc'); if(grdEl && d.grand_total) grdEl.innerText = d.grand_total;
                            var brk = document.getElementById('drawer-summary-breakdown'); if(brk) brk.classList.remove('d-none');
                            var bWrap = document.getElementById('drawer-applied-discounts-wrap');
                            if(bWrap && d.coupon_code) {
                                bWrap.classList.remove('d-none');
                                bWrap.innerHTML = '<span class=&quot;badge bg-success-subtle text-success border border-success-subtle d-inline-flex align-items-center gap-1 py-1 px-2&quot; style=&quot;font-size:10px;&quot;><i class=&quot;fas fa-tag&quot;></i> Coupon: <strong>' + d.coupon_code + '</strong> (-৳' + parseFloat(d.coupon_discount).toFixed(2) + ')</span>';
                            }
                            document.querySelectorAll('#cartDrawer *, .cart-drawer *, .offcanvas-end *').forEach(function(n){
                                if(n.children.length===0 && n.innerText.trim()==='Total' && n.parentElement){
                                    var amt = n.parentElement.querySelector('h4, span:last-child, strong, b');
                                    if(amt && amt!==n && d.grand_total) amt.innerText = '৳' + d.grand_total;
                                }
                            });
                            if(typeof window.syncDeliveryCharge === 'function') window.syncDeliveryCharge();
                        }).catch(function(e){
                            showInlineMsg(e.message || 'Invalid coupon code. Please check and try again.', false);
                        }).finally(function(){
                            btn.disabled = false;
                            btn.innerHTML = oldTxt;
                        });
                    })(this)">
                    Apply
                </button>
            </div>

            <!-- Short Inline Error / Success Message Directly Below Input Box -->
            <div id="drawer-coupon-inline-msg" class="d-none mt-1.5" style="font-size: 11px; font-weight: 500; line-height: 1.35;"></div>

            <!-- Applied Discount Badges -->
            <div id="drawer-applied-discounts-wrap" class="d-flex flex-wrap gap-1 mt-1.5 mb-1 {{ ($couponDiscount > 0 || $voucherDiscount > 0) ? '' : 'd-none' }}">
                @if(!empty($couponCode) && $couponDiscount > 0)
                    <span class="badge bg-success-subtle text-success border border-success-subtle d-inline-flex align-items-center gap-1 py-1 px-2" style="font-size: 10px;" id="drawer-coupon-tag">
                        <i class="fas fa-tag"></i> Coupon: <strong>{{ $couponCode }}</strong> (-৳{{ number_format($couponDiscount, 2) }})
                        <i class="fas fa-times ms-1 text-danger" style="cursor: pointer;" title="Remove"
                           onclick="(function(el){
                               var box = el.closest('.drawer-coupon-box');
                               var msgEl = box ? box.querySelector('#drawer-coupon-inline-msg') : document.getElementById('drawer-coupon-inline-msg');
                               var csrf = document.querySelector('meta[name=&quot;csrf-token&quot;]') ? document.querySelector('meta[name=&quot;csrf-token&quot;]').getAttribute('content') : '{{ csrf_token() }}';
                               fetch('{{ route('cart.coupon.remove') }}', {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'}}).then(function(r){return r.json();}).then(function(d){
                                   var inp = document.getElementById('drawer-coupon-input'); if(inp) inp.value = '';
                                   el.parentElement.remove();
                                   if(msgEl) { msgEl.innerText = d.message || 'Coupon removed.'; msgEl.style.color = '#64748b'; msgEl.classList.remove('d-none'); }
                                   var brk = document.getElementById('drawer-summary-breakdown'); if(brk && parseFloat(d.total_discount||0)<=0) brk.classList.add('d-none');
                                   document.querySelectorAll('#cartDrawer *, .cart-drawer *, .offcanvas-end *').forEach(function(n){
                                       if(n.children.length===0 && n.innerText.trim()==='Total' && n.parentElement){
                                           var amt = n.parentElement.querySelector('h4, span:last-child, strong, b');
                                           if(amt && amt!==n && d.grand_total) amt.innerText = '৳' + d.grand_total;
                                       }
                                   });
                                   if(typeof window.syncDeliveryCharge === 'function') window.syncDeliveryCharge();
                               });
                           })(this)"></i>
                    </span>
                @endif

                @if(!empty($voucherCode) && $voucherDiscount > 0)
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle d-inline-flex align-items-center gap-1 py-1 px-2" style="font-size: 10px;" id="drawer-voucher-tag">
                        <i class="fas fa-gift"></i> Voucher: <strong>{{ $voucherCode }}</strong> (-৳{{ number_format($voucherDiscount, 2) }})
                        <i class="fas fa-times ms-1 text-danger" style="cursor: pointer;" title="Remove"
                           onclick="(function(el){
                               var box = el.closest('.drawer-coupon-box');
                               var msgEl = box ? box.querySelector('#drawer-coupon-inline-msg') : document.getElementById('drawer-coupon-inline-msg');
                               var csrf = document.querySelector('meta[name=&quot;csrf-token&quot;]') ? document.querySelector('meta[name=&quot;csrf-token&quot;]').getAttribute('content') : '{{ csrf_token() }}';
                               fetch('{{ route('cart.voucher.remove') }}', {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'}}).then(function(r){return r.json();}).then(function(d){
                                   el.parentElement.remove();
                                   if(msgEl) { msgEl.innerText = d.message || 'Voucher removed.'; msgEl.style.color = '#64748b'; msgEl.classList.remove('d-none'); }
                                   var brk = document.getElementById('drawer-summary-breakdown'); if(brk && parseFloat(d.total_discount||0)<=0) brk.classList.add('d-none');
                                   document.querySelectorAll('#cartDrawer *, .cart-drawer *, .offcanvas-end *').forEach(function(n){
                                       if(n.children.length===0 && n.innerText.trim()==='Total' && n.parentElement){
                                           var amt = n.parentElement.querySelector('h4, span:last-child, strong, b');
                                           if(amt && amt!==n && d.grand_total) amt.innerText = '৳' + d.grand_total;
                                       }
                                   });
                                   if(typeof window.syncDeliveryCharge === 'function') window.syncDeliveryCharge();
                               });
                           })(this)"></i>
                    </span>
                @endif
            </div>

            <!-- 1-Click Available Offers (Coupons & Vouchers with Eligibility Protection) -->
            @if(count($drawerAvailableOffers) > 0)
                <div class="drawer-vouchers-collapsible mt-1">
                    <a class="d-flex justify-content-between align-items-center text-decoration-none py-1 px-2 bg-white rounded border" data-bs-toggle="collapse" href="#drawerVouchersList" role="button" aria-expanded="false" style="font-size: 10.5px; color: #475569;">
                        <span><i class="fas fa-tags text-warning me-1"></i> Available Offers ({{ count($drawerAvailableOffers) }})</span>
                        <i class="fas fa-chevron-down" style="font-size: 8.5px;"></i>
                    </a>
                    <div class="collapse mt-1" id="drawerVouchersList">
                        <div class="d-flex flex-column gap-1.5 p-1 bg-white rounded border" style="max-height: 160px; overflow-y: auto;">
                            @foreach($drawerAvailableOffers as $dv)
                                <div class="drawer-offer-chip {{ $dv->is_eligible ? 'eligible' : 'ineligible' }}">
                                    <div class="min-w-0 pe-1">
                                        <div class="d-flex align-items-center gap-1">
                                            @if($dv->type === 'coupon')
                                                <span class="badge bg-primary-subtle text-primary border py-0 px-1 font-monospace" style="font-size: 8.5px;">{{ $dv->code }}</span>
                                            @else
                                                <strong class="d-block text-dark text-truncate" style="max-width: 140px; font-size: 10px;">{{ $dv->title }}</strong>
                                            @endif
                                        </div>
                                        <small class="text-success font-monospace fw-bold d-block text-truncate" style="font-size: 9.5px;">
                                            @if(($dv->discount_type ?? '') === 'percentage')
                                                {{ (float)$dv->discount_amount }}% OFF
                                            @elseif(($dv->discount_type ?? '') === 'free_shipping')
                                                FREE SHIPPING
                                            @else
                                                ৳{{ number_format($dv->discount_amount ?? 0, 0) }} OFF
                                            @endif
                                            @if(!empty($dv->min_order_amount) || !empty($dv->min_spend))
                                                (Min ৳{{ number_format($dv->min_order_amount ?? $dv->min_spend, 0) }})
                                            @endif
                                        </small>
                                    </div>

                                    <div class="flex-shrink-0">
                                        @if($dv->is_eligible)
                                            @if($dv->type === 'coupon')
                                                <button type="button" class="btn btn-outline-primary btn-sm py-0 px-1.5 fw-bold font-monospace" style="font-size: 9px;"
                                                    onclick="var inp=document.getElementById('drawer-coupon-input'); if(inp){ inp.value='{{ $dv->code }}'; document.getElementById('btn-drawer-apply-coupon').click(); }">
                                                    Apply
                                                </button>
                                            @elseif($dv->is_wallet ?? false)
                                                <button type="button" class="btn btn-outline-primary btn-sm py-0 px-1.5 fw-bold font-monospace" style="font-size: 9px;"
                                                    onclick="if(window.applyDrawerVoucherDirect) window.applyDrawerVoucherDirect('{{ $dv->voucher_code }}', this);">
                                                    Apply
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-primary btn-sm py-0 px-1.5 fw-bold font-monospace" style="font-size: 9px;"
                                                    onclick="if(window.claimAndApplyDrawerVoucherDirect) window.claimAndApplyDrawerVoucherDirect({{ $dv->id }}, this);">
                                                    Claim
                                                </button>
                                            @endif
                                        @else
                                            <span class="badge bg-secondary-subtle text-muted border font-monospace py-0.5 px-1" style="font-size: 8.5px;" title="{{ $dv->eligibility_reason }}">
                                                <i class="fas fa-lock me-0.5"></i>{{ $dv->eligibility_reason }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Live Calculation Breakdown in Drawer -->
            <div id="drawer-summary-breakdown" class="mt-2 pt-1.5 border-top {{ ($totalDiscount > 0) ? '' : 'd-none' }}">
                <div class="d-flex justify-content-between text-muted small" style="font-size: 10.5px;">
                    <span>Subtotal:</span>
                    <span class="fw-semibold text-dark">৳<span id="drawer-subtotal-calc">{{ number_format($total, 2) }}</span></span>
                </div>
                <div class="d-flex justify-content-between text-success small" style="font-size: 10.5px;">
                    <span>Discount:</span>
                    <span class="fw-bold">-৳<span id="drawer-discount-calc">{{ number_format($totalDiscount, 2) }}</span></span>
                </div>
                <div class="d-flex justify-content-between text-dark fw-bold small mt-0.5" style="font-size: 11.5px;">
                    <span>Total Payable:</span>
                    <span class="text-danger">৳<span id="drawer-grandtotal-calc">{{ number_format(max(0, $total - $totalDiscount), 2) }}</span></span>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="text-center py-5 px-3 my-auto">
        <div class="mb-3">
            <i class="fas fa-shopping-basket opacity-25 cd-empty-icon" style="font-size: 3rem;"></i>
        </div>
        <h6 class="fw-bold mb-1 fs-6" style="color: var(--cd-item-title);">Your cart is empty</h6>
        <p class="text-muted small mb-3 px-2" style="font-size: 11.5px;">No products added yet.</p>
        <a href="{{ route('shop') }}" class="btn btn-sm rounded-pill px-4 cd-empty-btn">
            Start Shopping
        </a>
    </div>
@endif