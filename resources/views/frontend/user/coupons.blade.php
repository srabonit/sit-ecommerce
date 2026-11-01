@extends('layouts.dashboard')

@php
    // সেন্ট্রাল গ্লোবাল সেটিংস ক্যাশ রেজলভার
    static $couponSettingsCache = null;
    if ($couponSettingsCache === null) {
        $couponSettingsCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
            return \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $cpnSettings = $couponSettingsCache ?? [];

    // ডায়নামিক থিম কালার ভ্যারিয়েবল (Existing Default Colors)
    $cpnCardBg        = $cpnSettings['color_auth_card_bg'] ?? '#ffffff';
    $cpnCardBorder    = $cpnSettings['color_auth_card_border'] ?? '#e2e8f0';
    $cpnTitleColor    = $cpnSettings['color_page_title'] ?? '#0f172a';
    $cpnTextColor     = $cpnSettings['color_auth_text'] ?? '#64748b';
    
    $cpnPrimaryColor  = $cpnSettings['color_common_btn_bg'] ?? ($cpnSettings['color_hdr_menu_hover_text'] ?? '#2563eb');
    $cpnPrimaryHover  = $cpnSettings['color_auth_btn_hover'] ?? '#1d4ed8';
    $cpnSuccessColor  = '#10b981';

    // ১. কাস্টমার পার্সোনাল ভাউচার ওয়ালেট ডেটা রেজল্যুশন
    $userId = auth()->id();
    $now = \Carbon\Carbon::now();

    if (!isset($myVouchers)) {
        $myVouchers = ($userId && \Illuminate\Support\Facades\Schema::hasTable('customer_vouchers'))
            ? \Illuminate\Support\Facades\DB::table('customer_vouchers')
                ->join('vouchers', 'customer_vouchers.voucher_id', '=', 'vouchers.id')
                ->where('customer_vouchers.user_id', $userId)
                ->select(
                    'customer_vouchers.*',
                    'vouchers.title as voucher_title',
                    'vouchers.voucher_type',
                    'vouchers.discount_amount as voucher_discount',
                    'vouchers.max_discount',
                    'vouchers.min_spend',
                    'vouchers.banner_image'
                )
                ->latest('customer_vouchers.id')
                ->get()
            : collect();
    }

    // ২. ক্লেইম করার জন্য উন্মুক্ত অ্যাক্টিভ ভাউচার ক্যাম্পেইন (Excluding guest_only for logged-in dashboard)
    if (!isset($claimableVouchers)) {
        $claimedVoucherIds = ($userId && \Illuminate\Support\Facades\Schema::hasTable('customer_vouchers'))
            ? \Illuminate\Support\Facades\DB::table('customer_vouchers')
                ->where('user_id', $userId)
                ->pluck('voucher_id')
                ->toArray()
            : [];

        if (\Illuminate\Support\Facades\Schema::hasTable('vouchers')) {
            $claimQuery = \Illuminate\Support\Facades\DB::table('vouchers')
                ->where('is_active', 1)
                ->where(function($q) use ($now) {
                    $q->whereNull('claim_start')->orWhere('claim_start', '<=', $now);
                })
                ->where(function($q) use ($now) {
                    $q->whereNull('claim_end')->orWhere('claim_end', '>=', $now);
                });

            if (\Illuminate\Support\Facades\Schema::hasColumn('vouchers', 'target_user_type')) {
                $claimQuery->where(function($q) {
                    $q->whereNull('target_user_type')
                      ->orWhereIn('target_user_type', ['all', 'registered_only']);
                });
            }

            $claimableVouchers = $claimQuery->get()->map(function($v) use ($claimedVoucherIds) {
                $v->already_claimed = in_array($v->id, $claimedVoucherIds);
                $v->is_stock_out = (!is_null($v->total_vouchers_count) && $v->claimed_count >= $v->total_vouchers_count);
                return $v;
            });
        } else {
            $claimableVouchers = collect();
        }
    }

    // ৩. অ্যাক্টিভ পাবলিক কুপন কোড তালিকা (Excluding guest_only for logged-in dashboard)
    if (!isset($publicCoupons)) {
        if (\Illuminate\Support\Facades\Schema::hasTable('coupons')) {
            $cpnQuery = \Illuminate\Support\Facades\DB::table('coupons')
                ->where('is_active', 1)
                ->where('is_public', 1)
                ->where(function($q) use ($now) {
                    $q->whereNull('start_date')->orWhere('start_date', '<=', $now);
                })
                ->where(function($q) use ($now) {
                    $q->whereNull('expire_date')->orWhere('expire_date', '>=', $now);
                });

            if (\Illuminate\Support\Facades\Schema::hasColumn('coupons', 'target_user_type')) {
                $cpnQuery->where(function($q) {
                    $q->whereNull('target_user_type')
                      ->orWhereIn('target_user_type', ['all', 'registered_only']);
                });
            }

            $publicCoupons = $cpnQuery->get();
        } else {
            $publicCoupons = collect();
        }
    }
@endphp

@section('title', 'Coupons & Vouchers - ' . config('app.name', 'Shop Now BD'))

@section('dashboard_content')
<style>
    :root {
        --cpn-card-bg: {{ $cpnCardBg }};
        --cpn-card-border: {{ $cpnCardBorder }};
        --cpn-title: {{ $cpnTitleColor }};
        --cpn-text: {{ $cpnTextColor }};
        --cpn-primary: {{ $cpnPrimaryColor }};
        --cpn-primary-hover: {{ $cpnPrimaryHover }};
        --cpn-success: {{ $cpnSuccessColor }};
    }

    /* Main Container Card */
    .coupon-main-card {
        background-color: var(--cpn-card-bg) !important;
        border: 1px solid var(--cpn-card-border) !important;
        border-radius: 16px !important;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04), 0 1px 3px rgba(15, 23, 42, 0.02) !important;
        transition: all 0.25s ease;
    }

    .coupon-card-title {
        color: var(--cpn-title) !important;
        font-size: 1.15rem;
        font-weight: 700;
        line-height: 1.35;
    }

    .coupon-card-subtitle {
        color: var(--cpn-text) !important;
        font-size: 12.5px;
        line-height: 1.45;
    }

    /* Tabs Styling */
    .voucher-tabs-nav {
        display: flex;
        gap: 8px;
        border-bottom: 1.5px solid var(--cpn-card-border);
        padding-bottom: 12px;
        margin-bottom: 20px;
        overflow-x: auto;
        white-space: nowrap;
        scrollbar-width: none;
    }
    .voucher-tabs-nav::-webkit-scrollbar {
        display: none;
    }

    .voucher-tab-btn {
        background: transparent;
        border: 1px solid transparent;
        color: var(--cpn-text);
        font-size: 13px;
        font-weight: 600;
        padding: 6px 14px;
        border-radius: 50px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .voucher-tab-btn:hover {
        color: var(--cpn-title);
        background: rgba(15, 23, 42, 0.04);
    }

    .voucher-tab-btn.active-tab {
        background: var(--cpn-primary) !important;
        color: #ffffff !important;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
    }

    .voucher-tab-btn .badge-count {
        background: rgba(255, 255, 255, 0.25);
        color: currentColor;
        font-size: 10.5px;
        padding: 2px 6px;
        border-radius: 20px;
    }

    .voucher-tab-btn:not(.active-tab) .badge-count {
        background: rgba(15, 23, 42, 0.08);
        color: var(--cpn-text);
    }

    /* Ticket Voucher Card Styling */
    .voucher-ticket-box {
        border-radius: 14px;
        position: relative;
        overflow: hidden;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        border: 1.5px dashed;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        background-color: #ffffff;
    }

    .voucher-ticket-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
    }

    /* Green Voucher Theme */
    .voucher-success-theme {
        background-color: rgba(16, 185, 129, 0.06) !important;
        border-color: rgba(16, 185, 129, 0.35) !important;
    }

    .voucher-success-theme .voucher-code-text {
        color: #059669 !important;
    }

    .voucher-success-theme .voucher-badge {
        background-color: #10b981 !important;
        color: #ffffff !important;
    }

    /* Primary Voucher Theme */
    .voucher-primary-theme {
        background-color: rgba(37, 99, 235, 0.06) !important;
        border-color: rgba(37, 99, 235, 0.35) !important;
    }

    .voucher-primary-theme .voucher-code-text {
        color: var(--cpn-primary) !important;
    }

    .voucher-primary-theme .voucher-badge {
        background-color: var(--cpn-primary) !important;
        color: #ffffff !important;
    }

    /* Muted Theme for Used/Expired */
    .voucher-muted-theme {
        background-color: rgba(148, 163, 184, 0.08) !important;
        border-color: rgba(148, 163, 184, 0.4) !important;
        opacity: 0.85;
    }

    .voucher-muted-theme .voucher-code-text {
        color: #64748b !important;
    }

    .voucher-muted-theme .voucher-badge {
        background-color: #64748b !important;
        color: #ffffff !important;
    }

    /* Code & Copy Button */
    .coupon-code-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #ffffff;
        border: 1px solid rgba(15, 23, 42, 0.1);
        padding: 5px 12px;
        border-radius: 8px;
        cursor: pointer;
        user-select: none;
        transition: all 0.2s ease;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        max-width: fit-content;
    }

    .coupon-code-pill:hover {
        border-color: var(--cpn-primary);
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.15);
    }

    .voucher-code-text {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 14px;
        font-weight: 800;
        letter-spacing: 0.04em;
    }

    .coupon-copy-icon {
        font-size: 12px;
        color: #64748b;
        transition: color 0.2s ease, transform 0.2s ease;
    }

    .coupon-code-pill:hover .coupon-copy-icon {
        color: var(--cpn-primary);
        transform: scale(1.15);
    }

    .voucher-badge {
        font-size: 10.5px !important;
        font-weight: 700 !important;
        padding: 4px 10px !important;
        border-radius: 50px !important;
        letter-spacing: 0.02em;
    }

    .voucher-desc-text {
        font-size: 13px;
        color: #475569;
        line-height: 1.4;
    }

    .voucher-validity-text {
        font-size: 11.5px;
        color: var(--cpn-text);
    }

    /* Claim Button */
    .btn-claim-voucher {
        background-color: var(--cpn-primary);
        color: #ffffff;
        font-size: 12px;
        font-weight: 600;
        padding: 6px 14px;
        border-radius: 8px;
        border: none;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-claim-voucher:hover {
        background-color: var(--cpn-primary-hover);
        color: #ffffff;
        transform: scale(1.02);
    }

    .btn-claim-voucher:disabled {
        background-color: #94a3b8 !important;
        cursor: not-allowed;
        transform: none;
    }

    /* Bengali digit protection */
    .bn-digit {
        font-weight: 700 !important;
    }

    /* Empty state */
    .voucher-empty-box {
        text-align: center;
        padding: 40px 20px;
        color: #64748b;
    }

    /* Mobile Responsive Tweaks (iPhone SE, 320px–375px) */
    @media (max-width: 575.98px) {
        .coupon-main-card {
            padding: 16px 12px !important;
            border-radius: 14px !important;
        }

        .coupon-card-title {
            font-size: 1.05rem !important;
        }

        .voucher-ticket-box {
            padding: 14px !important;
        }

        .voucher-code-text {
            font-size: 13px !important;
        }

        .voucher-desc-text {
            font-size: 12.5px !important;
        }

        .voucher-tab-btn {
            font-size: 12px;
            padding: 5px 12px;
        }
    }
</style>

<div class="coupon-main-card p-3 p-md-4">
    <!-- Header Title -->
    <div class="d-flex align-items-center gap-2 mb-1">
        <div class="fs-5" style="color: var(--cpn-success);">
            <i class="fas fa-ticket-alt"></i>
        </div>
        <h5 class="coupon-card-title mb-0">Coupons &amp; Vouchers</h5>
    </div>
    <p class="coupon-card-subtitle mb-3">Manage your personal voucher wallet and discover active discount codes</p>

    <!-- Navigation Tabs -->
    <div class="voucher-tabs-nav" id="voucherSectionTabs">
        <button type="button" class="voucher-tab-btn active-tab" onclick="switchVoucherTab('my_wallet', this)">
            <i class="fas fa-wallet"></i>
            <span>My Wallet</span>
            <span class="badge-count bn-digit">{{ count($myVouchers) }}</span>
        </button>

        <button type="button" class="voucher-tab-btn" onclick="switchVoucherTab('claimable', this)">
            <i class="fas fa-gift"></i>
            <span>Claim Vouchers</span>
            <span class="badge-count bn-digit">{{ count($claimableVouchers) }}</span>
        </button>

        <button type="button" class="voucher-tab-btn" onclick="switchVoucherTab('coupons', this)">
            <i class="fas fa-tags"></i>
            <span>Store Coupons</span>
            <span class="badge-count bn-digit">{{ count($publicCoupons) }}</span>
        </button>
    </div>

    <!-- TAB 1: My Personal Vouchers Wallet -->
    <div class="voucher-tab-content" id="tab-my_wallet">
        <div class="row g-3">
            @forelse($myVouchers as $mv)
                @php
                    $isExpired = (!empty($mv->expires_at) && \Carbon\Carbon::parse($mv->expires_at)->isPast());
                    $isRedeemed = ($mv->status === 'redeemed' || $mv->status === 'used');
                    $isReady = (!$isExpired && !$isRedeemed && in_array($mv->status, ['claimed', 'restored']));

                    $themeClass = $isReady 
                        ? ($mv->voucher_type === 'free_shipping' ? 'voucher-primary-theme' : 'voucher-success-theme') 
                        : 'voucher-muted-theme';
                @endphp
                <div class="col-12 col-md-6">
                    <div class="p-3 p-md-4 voucher-ticket-box {{ $themeClass }} position-relative">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                            <div class="coupon-code-pill" onclick="copyCouponCode('{{ $mv->voucher_code }}', this)" title="Click to copy">
                                <span class="voucher-code-text">{{ $mv->voucher_code }}</span>
                                <i class="fas fa-copy coupon-copy-icon"></i>
                            </div>

                            @if($isReady)
                                <span class="badge voucher-badge">Ready to Use</span>
                            @elseif($isRedeemed)
                                <span class="badge voucher-badge">Used</span>
                            @else
                                <span class="badge voucher-badge">Expired</span>
                            @endif
                        </div>

                        <p class="voucher-desc-text fw-semibold mb-2">
                            @if($mv->voucher_type === 'percentage')
                                Get <span class="bn-digit">{{ (float)$mv->voucher_discount }}%</span> off
                                @if(!empty($mv->max_discount))
                                    up to <span class="bn-digit">৳{{ number_format($mv->max_discount, 0) }}</span>
                                @endif
                            @elseif($mv->voucher_type === 'free_shipping')
                                Free Shipping voucher on eligible order
                            @else
                                Flat <span class="bn-digit">৳{{ number_format($mv->voucher_discount, 0) }}</span> discount on checkout
                            @endif

                            @if(!empty($mv->min_spend) && $mv->min_spend > 0)
                                (Min spend: <span class="bn-digit">৳{{ number_format($mv->min_spend, 0) }}</span>)
                            @endif
                        </p>

                        <div class="d-flex align-items-center justify-content-between pt-2 border-top border-secondary border-opacity-10 mt-2">
                            <small class="voucher-validity-text d-flex align-items-center gap-1">
                                <i class="far fa-clock"></i>
                                @if(!empty($mv->expires_at))
                                    <span>Expires: <strong class="bn-digit">{{ date('d M Y', strtotime($mv->expires_at)) }}</strong></span>
                                @else
                                    <span>Lifetime Validity</span>
                                @endif
                            </small>

                            @if($isReady)
                                <a href="{{ route('shop') }}" class="btn btn-sm btn-link text-decoration-none p-0 fw-bold" style="font-size: 11px; color: var(--cpn-primary);">Use Now &rarr;</a>
                            @else
                                <span class="badge bg-white text-muted border px-2 py-1" style="font-size: 10px;">{{ ucfirst($mv->status) }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="voucher-empty-box">
                        <i class="fas fa-wallet fa-3x mb-2 text-muted opacity-50"></i>
                        <h6 class="fw-bold text-dark mb-1">Your Voucher Wallet is Empty</h6>
                        <p class="small mb-3">You have not claimed any vouchers yet. Check available vouchers to claim discounts!</p>
                        <button type="button" class="btn btn-primary btn-sm px-3" onclick="document.querySelectorAll('.voucher-tab-btn')[1].click()">
                            Browse Claimable Vouchers
                        </button>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- TAB 2: Claimable Vouchers (Campaigns) -->
    <div class="voucher-tab-content d-none" id="tab-claimable">
        <div class="row g-3">
            @forelse($claimableVouchers as $cv)
                @php
                    $isPrimary = ($cv->voucher_type === 'free_shipping');
                    $themeClass = $isPrimary ? 'voucher-primary-theme' : 'voucher-success-theme';
                @endphp
                <div class="col-12 col-md-6" id="claimable-card-{{ $cv->id }}">
                    <div class="p-3 p-md-4 voucher-ticket-box {{ $themeClass }} position-relative">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                            <strong class="text-dark fs-6 text-truncate" title="{{ $cv->title }}">{{ $cv->title }}</strong>
                            
                            @if($cv->already_claimed)
                                <span class="badge bg-success text-white voucher-badge">Claimed</span>
                            @elseif($cv->is_stock_out)
                                <span class="badge bg-secondary text-white voucher-badge">Claim Limit Reached</span>
                            @else
                                <span class="badge voucher-badge">Available</span>
                            @endif
                        </div>

                        <p class="voucher-desc-text fw-semibold mb-2">
                            @if($cv->voucher_type === 'percentage')
                                Save <span class="bn-digit">{{ (float)$cv->discount_amount }}%</span>
                                @if(!empty($cv->max_discount))
                                    up to <span class="bn-digit">৳{{ number_format($cv->max_discount, 0) }}</span>
                                @endif
                            @elseif($cv->voucher_type === 'free_shipping')
                                Free Delivery on orders over <span class="bn-digit">৳{{ number_format($cv->min_spend, 0) }}</span>
                            @else
                                Flat <span class="bn-digit">৳{{ number_format($cv->discount_amount, 0) }}</span> discount on orders
                            @endif

                            @if(!empty($cv->min_spend) && $cv->min_spend > 0)
                                (Min order: <span class="bn-digit">৳{{ number_format($cv->min_spend, 0) }}</span>)
                            @endif
                        </p>

                        <div class="d-flex align-items-center justify-content-between pt-2 border-top border-secondary border-opacity-10 mt-2">
                            <small class="voucher-validity-text d-flex align-items-center gap-1">
                                <i class="far fa-clock"></i>
                                <span>Valid for: <strong class="bn-digit">{{ $cv->validity_days }} days</strong> from claim</span>
                            </small>

                            @if($cv->already_claimed)
                                <span class="text-success fw-bold small" style="font-size: 11px;">
                                    <i class="fas fa-check-circle me-0.5"></i> In Wallet
                                </span>
                            @elseif($cv->is_stock_out)
                                <span class="text-muted small" style="font-size: 11px;">Stock Out</span>
                            @else
                                <button type="button" class="btn-claim-voucher shadow-xs" onclick="claimVoucherAction({{ $cv->id }}, this)">
                                    <i class="fas fa-hand-holding-dollar"></i> Claim
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="voucher-empty-box">
                        <i class="fas fa-gift fa-3x mb-2 text-muted opacity-50"></i>
                        <h6 class="fw-bold text-dark mb-1">No Claimable Vouchers Right Now</h6>
                        <p class="small mb-0">Stay tuned for new campaigns and reward drops from {{ config('app.name', 'our store') }}!</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- TAB 3: Store Public Coupons -->
    <div class="voucher-tab-content d-none" id="tab-coupons">
        <div class="row g-3">
            @forelse($publicCoupons as $cpn)
                @php
                    $isPrimary = ($cpn->discount_type === 'free_shipping');
                    $themeClass = $isPrimary ? 'voucher-primary-theme' : 'voucher-success-theme';
                    $targetAudience = $cpn->target_user_type ?? 'all';
                    $appRule = $cpn->applicable_to ?? 'all';
                @endphp
                <div class="col-12 col-md-6">
                    <div class="p-3 p-md-4 voucher-ticket-box {{ $themeClass }} position-relative">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                            <div class="coupon-code-pill" onclick="copyCouponCode('{{ $cpn->code }}', this)" title="Click to copy">
                                <span class="voucher-code-text">{{ $cpn->code }}</span>
                                <i class="fas fa-copy coupon-copy-icon"></i>
                            </div>

                            <div class="d-flex align-items-center gap-1">
                                @if($targetAudience === 'registered_only')
                                    <span class="badge bg-info-subtle text-info border border-info-subtle" style="font-size: 9.5px;">Members Only</span>
                                @endif
                                <span class="badge voucher-badge">{{ $cpn->badge_text ?? 'Active' }}</span>
                            </div>
                        </div>

                        <p class="voucher-desc-text fw-semibold mb-2">
                            @if(!empty($cpn->customer_description))
                                {{ $cpn->customer_description }}
                            @elseif($cpn->discount_type === 'percentage')
                                Get <span class="bn-digit">{{ (float)$cpn->discount_amount }}%</span> off on orders
                                @if(!empty($cpn->min_order_amount) && $cpn->min_order_amount > 0)
                                    over <span class="bn-digit">৳{{ number_format($cpn->min_order_amount, 0) }}</span>
                                @endif
                            @elseif($cpn->discount_type === 'free_shipping')
                                Free Delivery nationwide on eligible cart
                            @else
                                Get <span class="bn-digit">৳{{ number_format($cpn->discount_amount, 0) }}</span> off on orders
                                @if(!empty($cpn->min_order_amount) && $cpn->min_order_amount > 0)
                                    over <span class="bn-digit">৳{{ number_format($cpn->min_order_amount, 0) }}</span>
                                @endif
                            @endif
                        </p>

                        <!-- Applicability Tag Hint -->
                        @if($appRule === 'specific_products')
                            <small class="text-primary d-block mb-2 font-monospace" style="font-size: 10px;">
                                <i class="fas fa-box-open me-0.5"></i> Applicable on selected items only
                            </small>
                        @elseif($appRule === 'specific_categories')
                            <small class="text-primary d-block mb-2 font-monospace" style="font-size: 10px;">
                                <i class="fas fa-layer-group me-0.5"></i> Applicable on selected categories only
                            </small>
                        @endif

                        <div class="d-flex align-items-center justify-content-between pt-2 border-top border-secondary border-opacity-10 mt-2">
                            <small class="voucher-validity-text d-flex align-items-center gap-1">
                                <i class="far fa-clock"></i>
                                @if(!empty($cpn->expire_date))
                                    <span>Valid until: <strong class="bn-digit">{{ date('d M Y', strtotime($cpn->expire_date)) }}</strong></span>
                                @else
                                    <span>Valid until: <strong>Ongoing</strong></span>
                                @endif
                            </small>
                            <span class="badge bg-white text-dark border border-secondary border-opacity-25 px-2 py-1 font-monospace" style="font-size: 10px;">
                                {{ ucfirst(str_replace('_', ' ', $cpn->discount_type)) }}
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="voucher-empty-box">
                        <i class="fas fa-tags fa-3x mb-2 text-muted opacity-50"></i>
                        <h6 class="fw-bold text-dark mb-1">No Active Public Coupons</h6>
                        <p class="small mb-0">Follow our social channels and newsletters for secret coupon drop announcements!</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Interactive Scripts: Tab Switching, 1-Click Copy & Voucher Claim --}}
<script>
function switchVoucherTab(tabName, btn) {
    document.querySelectorAll('.voucher-tab-btn').forEach(b => b.classList.remove('active-tab'));
    if (btn) btn.classList.add('active-tab');

    document.querySelectorAll('.voucher-tab-content').forEach(c => c.classList.add('d-none'));
    const target = document.getElementById('tab-' + tabName);
    if (target) target.classList.remove('d-none');
}

function copyCouponCode(code, el) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(code).then(() => showCopiedFeedback(el));
    } else {
        const textArea = document.createElement("textarea");
        textArea.value = code;
        textArea.style.position = "fixed";
        textArea.style.left = "-999999px";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            document.execCommand('copy');
            showCopiedFeedback(el);
        } catch (err) {}
        document.body.removeChild(textArea);
    }
}

function showCopiedFeedback(el) {
    const icon = el.querySelector('.coupon-copy-icon');
    if (!icon) return;
    
    const originalClass = icon.className;
    icon.className = 'fas fa-check text-success';
    
    setTimeout(() => {
        icon.className = originalClass;
    }, 1800);
}

function claimVoucherAction(voucherId, btn) {
    @guest
        window.location.href = "{{ route('login') }}";
        return;
    @endguest

    btn.disabled = true;
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Claiming...';

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    fetch("{{ url('/user/vouchers/claim') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ voucher_id: voucherId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            btn.className = 'btn btn-sm btn-success px-2 py-1 fw-bold';
            btn.innerHTML = '<i class="fas fa-check"></i> Claimed';
            setTimeout(() => {
                window.location.reload();
            }, 800);
        } else {
            alert(data.message || 'Could not claim this voucher.');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    })
    .catch(() => {
        alert('Network error while claiming voucher.');
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
}
</script>
@endsection