@extends('layouts.app')

@section('title', 'Checkout - ' . config('app.name', 'Shop Now BD'))

@section('content')
@php
    $subtotal = 0;
    $hasDigitalInCart = false;
    $hasPhysicalInCart = false;

    foreach($cart as $id => $item) {
        $rawPrice = $item['price'] ?? 0;
        if (is_string($rawPrice)) {
            $rawPrice = preg_replace('/[^0-9.]/', '', $rawPrice);
        }
        $pId = $item['id'] ?? (is_numeric($id) ? $id : ($item['product_id'] ?? null));
        $prodModel = \App\Models\Product::find($pId);
        $isDownloadable = !empty($item['is_downloadable']) || ($prodModel && (!empty($prodModel->is_downloadable) || str_contains(strtolower($prodModel->product_type ?? ''), 'download')));
        $qty = $isDownloadable ? 1 : (int)($item['quantity'] ?? 1);

        $subtotal += ((float)$rawPrice * $qty);

        $isDig = ($item['is_digital'] ?? false) || ($prodModel && ($prodModel->is_virtual || $prodModel->is_downloadable || in_array(strtolower($prodModel->product_type ?? ''), ['virtual', 'downloadable', 'license'])));

        if ($isDig) {
            $hasDigitalInCart = true;
        } else {
            $hasPhysicalInCart = true;
        }
    }

    $isMixedOrder = ($hasDigitalInCart && $hasPhysicalInCart);
    $deliveryCharge = $initialShipping['cost'] ?? 0;

    // কুপন ও ভাউচার ক্যালকুলেশন ডেটা রেজলভার
    $appliedCoupon = session('applied_coupon');
    $appliedVoucher = session('applied_voucher');
    $couponDiscount = $calc['coupon_discount'] ?? (is_array($appliedCoupon) ? ($appliedCoupon['discount'] ?? 0) : 0);
    $voucherDiscount = $calc['voucher_discount'] ?? (is_array($appliedVoucher) ? ($appliedVoucher['discount'] ?? 0) : 0);
    $totalDiscount = $couponDiscount + $voucherDiscount;
    $effectiveDeliveryCharge = $calc['adjusted_delivery_charge'] ?? $deliveryCharge;

    // NBR & Dynamic Tax Data Resolvers
    $isVatEnabled     = (bool)($taxCalc['is_vat_enabled'] ?? false);
    $orderTaxAmount   = (float)($taxCalc['total_tax'] ?? ($taxCalc['total_tax_amount'] ?? 0.0));
    $productTaxAmount = (float)($taxCalc['product_tax'] ?? ($taxCalc['product_tax_amount'] ?? 0.0));
    $shippingTaxAmount= (float)($taxCalc['shipping_tax'] ?? ($taxCalc['shipping_tax_amount'] ?? 0.0));
    $exclusiveTax     = (float)($taxCalc['exclusive_tax'] ?? 0.0);
    $isTaxInclusive   = (bool)($taxCalc['is_inclusive'] ?? false);

    $grandTotal = $calc['final_payable'] ?? max(0, ($subtotal - $totalDiscount) + $effectiveDeliveryCharge + $exclusiveTax);

    $appliedCouponCode = is_array($appliedCoupon) ? ($appliedCoupon['code'] ?? null) : $appliedCoupon;
    $appliedVoucherCode = is_array($appliedVoucher) ? ($appliedVoucher['code'] ?? ($appliedVoucher['voucher_code'] ?? null)) : $appliedVoucher;

    $authUser = auth()->user();
    $defaultPayType = $hasDigitalInCart ? 'manual' : 'cod';

    // সক্রিয় স্টোর পাবলিক কুপন কোড সংগ্রহ (এলিজিবিলিটি চেকের জন্য)
    $availableStoreCoupons = collect();
    if (\Illuminate\Support\Facades\Schema::hasTable('coupons')) {
        $nowTime = \Carbon\Carbon::now();
        $cQuery = \Illuminate\Support\Facades\DB::table('coupons')
            ->where('is_active', 1)
            ->where('is_public', 1)
            ->where(function ($q) use ($nowTime) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $nowTime);
            })
            ->where(function ($q) use ($nowTime) {
                $q->whereNull('expire_date')->orWhere('expire_date', '>=', $nowTime);
            });
        $availableStoreCoupons = $cQuery->get();
    }

    // গ্লোবাল কালার সেটিংস ক্যাশ রেজলভার
    static $checkoutSettingsCache = null;
    if ($checkoutSettingsCache === null) {
        $checkoutSettingsCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
            return DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $checkoutSettings = $checkoutSettingsCache ?? [];

    $vatLabelText = $checkoutSettings['vat_label'] ?? 'Govt. VAT / Tax';

    // Checkout Scoped Dynamic Color Resolvers
    $checkoutPageBg          = $checkoutSettings['color_checkout_page_bg'] ?? ($checkoutSettings['color_site_bg'] ?? '#f8fafc');
    $checkoutCardBg          = $checkoutSettings['color_checkout_card_bg'] ?? '#ffffff';
    $checkoutCardBorder      = $checkoutSettings['color_checkout_card_border'] ?? '#e2e8f0';
    $checkoutHeading         = $checkoutSettings['color_checkout_heading'] ?? '#0f172a';
    $checkoutText            = $checkoutSettings['color_checkout_text'] ?? '#334155';
    $checkoutLabel           = $checkoutSettings['color_checkout_label'] ?? '#0f172a';
    $checkoutInputBg         = $checkoutSettings['color_checkout_input_bg'] ?? '#ffffff';
    $checkoutInputBorder     = $checkoutSettings['color_checkout_input_border'] ?? '#cbd5e1';
    $checkoutInputFocusBrd   = $checkoutSettings['color_checkout_input_focus_border'] ?? '#2563eb';

    $checkoutPayBoxBg        = $checkoutSettings['color_checkout_paybox_bg'] ?? '#ffffff';
    $checkoutPayBoxBorder    = $checkoutSettings['color_checkout_paybox_border'] ?? '#e2e8f0';
    $checkoutPayBoxActBg     = $checkoutSettings['color_checkout_paybox_active_bg'] ?? '#eff6ff';
    $checkoutPayBoxActBorder = $checkoutSettings['color_checkout_paybox_active_border'] ?? '#2563eb';

    $checkoutTotalText       = $checkoutSettings['color_checkout_total_price'] ?? '#2563eb';
    $checkoutBtnBg           = $checkoutSettings['color_checkout_btn_bg'] ?? '#2563eb';
    $checkoutBtnText         = $checkoutSettings['color_checkout_btn_text'] ?? '#ffffff';
    $checkoutBtnHover        = $checkoutSettings['color_checkout_btn_hover'] ?? '#1d4ed8';

    // সেন্ট্রাল কিউআর ইমেজ রেজলভার
    $resolveCheckoutQr = function($path) {
        if (empty($path)) return '';
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path, '');
        }
        if (function_exists('show_image')) {
            return show_image($path, '');
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        return asset(ltrim($path, '/'));
    };
@endphp

<!-- Font Awesome 6.5.1 CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<!-- Google Fonts: Plus Jakarta Sans & Inter -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --co-page-bg: {{ $checkoutPageBg }};
        --co-card-bg: {{ $checkoutCardBg }};
        --co-card-border: {{ $checkoutCardBorder }};
        --co-heading: {{ $checkoutHeading }};
        --co-text: {{ $checkoutText }};
        --co-label: {{ $checkoutLabel }};
        --co-input-bg: {{ $checkoutInputBg }};
        --co-input-border: {{ $checkoutInputBorder }};
        --co-input-focus-border: {{ $checkoutInputFocusBrd }};

        --co-paybox-bg: {{ $checkoutPayBoxBg }};
        --co-paybox-border: {{ $checkoutPayBoxBorder }};
        --co-paybox-act-bg: {{ $checkoutPayBoxActBg }};
        --co-paybox-act-border: {{ $checkoutPayBoxActBorder }};

        --co-total-price: {{ $checkoutTotalText }};
        --co-btn-bg: {{ $checkoutBtnBg }};
        --co-btn-text: {{ $checkoutBtnText }};
        --co-btn-hover: {{ $checkoutBtnHover }};
    }

    .checkout-wrapper {
        background-color: var(--co-page-bg) !important;
        min-height: 85vh;
        font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        letter-spacing: -0.01em;
    }

    /* FontAwesome Protection */
    .checkout-wrapper i,
    .checkout-wrapper .fa,
    .checkout-wrapper .fas,
    .checkout-wrapper .far,
    .checkout-wrapper .fab,
    .checkout-wrapper .fa-solid,
    .checkout-wrapper .fa-regular {
        font-family: "Font Awesome 6 Free", "Font Awesome 5 Free", "FontAwesome" !important;
        font-style: normal;
        font-weight: 900;
        line-height: 1;
        letter-spacing: normal !important;
        display: inline-block;
    }
    .checkout-wrapper .far,
    .checkout-wrapper .fa-regular {
        font-weight: 400 !important;
    }

    .bn-digit,
    .font-monospace {
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif !important;
        font-weight: 700 !important;
        font-variant-numeric: tabular-nums;
    }

    .checkout-card {
        background-color: var(--co-card-bg) !important;
        border: 1px solid var(--co-card-border) !important;
        border-radius: 8px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }

    .checkout-heading-text {
        color: var(--co-heading) !important;
        font-size: 14.5px;
        font-weight: 700;
    }

    .checkout-label-text {
        color: var(--co-label) !important;
        font-size: 11px;
        font-weight: 700;
        margin-bottom: 4px;
        display: block;
    }

    .checkout-form-input {
        background-color: var(--co-input-bg) !important;
        border: 1px solid var(--co-input-border) !important;
        color: var(--co-text) !important;
        border-radius: 5px !important;
        font-size: 12px;
        min-height: 34px;
        padding: 5px 10px;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .checkout-form-input:focus {
        border-color: var(--co-input-focus-border) !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
        outline: none;
    }
    textarea.checkout-form-input {
        min-height: 68px;
    }

    .payment-radio-box {
        background-color: var(--co-paybox-bg) !important;
        border: 1px solid var(--co-paybox-border) !important;
        border-radius: 6px !important;
        padding: 10px 12px;
        transition: all 0.15s ease;
    }
    .payment-radio-box:hover {
        border-color: #cbd5e1 !important;
    }
    .payment-radio-box.selected {
        background-color: var(--co-paybox-act-bg) !important;
        border-color: var(--co-paybox-act-border) !important;
    }

    .checkout-price-total {
        color: var(--co-total-price) !important;
        font-weight: 800;
    }

    .checkout-order-submit-btn {
        background-color: var(--co-btn-bg) !important;
        border-color: var(--co-btn-bg) !important;
        color: var(--co-btn-text) !important;
        font-weight: 700;
        border-radius: 6px !important;
        transition: all 0.15s ease;
        padding: 10px 16px;
    }
    .checkout-order-submit-btn:hover {
        background-color: var(--co-btn-hover) !important;
        border-color: var(--co-btn-hover) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
    }

    .brand-pay-card { 
        border-radius: 8px !important; 
        overflow: hidden; 
        box-shadow: 0 2px 10px rgba(0,0,0,0.04); 
        margin-top: 12px; 
        background: #ffffff; 
        border: 1px solid #e2e8f0; 
    }
    .brand-card-header { padding: 10px 14px; color: #ffffff; display: flex; justify-content: space-between; align-items: center; }
    .theme-bkash .brand-card-header { background: linear-gradient(135deg, #e2136e 0%, #b80d57 100%); }
    .theme-bkash .acc-box { border-color: #f48fb1; background: #fdf2f7; color: #b80d57; }
    .theme-nagad .brand-card-header { background: linear-gradient(135deg, #f57c00 0%, #e65100 100%); }
    .theme-nagad .acc-box { border-color: #ffcc80; background: #fff8f0; color: #e65100; }
    .theme-upay .brand-card-header { background: linear-gradient(135deg, #00838f 0%, #006064 100%); }
    .theme-upay .acc-box { border-color: #80deea; background: #f0fdfa; color: #006064; }
    .theme-default .brand-card-header { background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%); }
    .theme-default .acc-box { border-color: #bfdbfe; background: #eff6ff; color: #1e40af; }
    
    .acc-number-digit { 
        display: inline-flex; 
        align-items: center; 
        justify-content: center; 
        min-width: 22px; 
        height: 26px; 
        background: #ffffff; 
        border: 1px solid rgba(0,0,0,0.18); 
        font-family: 'Plus Jakarta Sans', 'Inter', monospace; 
        font-size: 13.5px; 
        font-weight: 800; 
        border-radius: 4px !important; 
        color: #0f172a; 
        box-shadow: 0 1px 2px rgba(0,0,0,0.06); 
    }
    .qr-standee-img { max-width: 120px; max-height: 150px; object-fit: contain; border-radius: 6px !important; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }

    /* Discount / Coupon Box Styles */
    .checkout-discount-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px !important;
        padding: 12px;
    }
    .applied-tag-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 5px !important;
    }
    .applied-tag-pill .btn-remove-tag {
        color: #ef4444;
        cursor: pointer;
        font-size: 10px;
        transition: transform 0.15s ease;
    }
    .applied-tag-pill .btn-remove-tag:hover {
        transform: scale(1.2);
    }

    /* Interactive Available Offer Chip */
    .promo-offer-item {
        background: #ffffff;
        border: 1px dashed #cbd5e1;
        border-radius: 6px;
        padding: 6px 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        transition: all 0.15s ease;
    }
    .promo-offer-item.eligible {
        border-color: #93c5fd;
        background: #f8faff;
    }
    .promo-offer-item.eligible:hover {
        border-color: #2563eb;
        background: #eff6ff;
    }
    .promo-offer-item.ineligible {
        opacity: 0.65;
        background: #f1f5f9;
        cursor: not-allowed;
    }

    /* Minimal Modal System */
    .minimal-modal-card {
        background: #ffffff !important;
        border: 1px solid #f1f5f9 !important;
        border-radius: 12px !important;
        box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.14), 0 0 1px 1px rgba(15, 23, 42, 0.04) !important;
        padding: 24px 26px !important;
        text-align: left !important;
    }

    .minimal-modal-title {
        font-size: 16px !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        margin-bottom: 6px !important;
        letter-spacing: -0.02em !important;
    }

    .minimal-modal-subtext {
        font-size: 13px !important;
        color: #64748b !important;
        margin-bottom: 22px !important;
        line-height: 1.45 !important;
    }

    .btn-minimal-delete {
        background-color: #c93424 !important;
        border: none !important;
        color: #ffffff !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        padding: 7px 22px !important;
        border-radius: 7px !important;
        box-shadow: 0 4px 12px rgba(201, 52, 36, 0.28) !important;
        transition: all 0.15s ease !important;
        cursor: pointer !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    .btn-minimal-delete:hover {
        background-color: #b02717 !important;
        box-shadow: 0 6px 14px rgba(201, 52, 36, 0.38) !important;
        color: #ffffff !important;
    }

    .btn-minimal-cancel {
        background: transparent !important;
        border: none !important;
        color: #64748b !important;
        font-weight: 500 !important;
        font-size: 13.5px !important;
        padding: 7px 14px !important;
        box-shadow: none !important;
        transition: color 0.15s ease !important;
        cursor: pointer !important;
    }
    .btn-minimal-cancel:hover {
        color: #0f172a !important;
        background: transparent !important;
    }

    @media (max-width: 575.98px) {
        .minimal-modal-card {
            padding: 20px 20px !important;
        }
    }
</style>

<div class="checkout-wrapper py-3 py-md-4">
    <div class="container px-2 px-md-3">
        <div class="text-center mb-3 mb-md-4">
            <span class="badge bg-primary-subtle text-primary px-3 py-1 font-monospace text-uppercase mb-1" style="font-size: 10px; border-radius: 5px;">
                <i class="fas fa-shield-alt me-1"></i> Fast &amp; Secure Checkout
            </span>
            <h3 class="fw-bold checkout-heading-text mb-0" style="font-size: 20px;">Complete Your Order</h3>
        </div>

        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-xs p-3 mb-3 d-flex align-items-center small" style="border-radius: 6px;">
                <i class="fas fa-exclamation-triangle fs-5 me-2 text-danger"></i>
                <span class="fw-bold">{{ session('error') }}</span>
            </div>
        @endif

        <div id="checkout-global-error-alert" class="alert alert-danger border-0 shadow-xs p-3 mb-3 d-none small" style="border-radius: 6px;">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle fs-5 me-2 text-danger"></i>
                <div>
                    <h6 class="fw-bold mb-1 small">Please fix the following:</h6>
                    <ul id="checkout-global-error-list" class="mb-0 ps-3"></ul>
                </div>
            </div>
        </div>

        <form id="checkout-main-form" novalidate enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="payment_type" id="input-payment_type" value="{{ $defaultPayType }}">

            <div class="row g-3 g-md-4">
                <div class="col-12 col-lg-7">
                    <!-- Shipping Card -->
                    <div class="card border-0 p-3 p-md-4 checkout-card mb-3">
                        <h5 class="fw-bold mb-3 checkout-heading-text">
                            <i class="fas fa-map-marker-alt text-primary me-1"></i> Shipping &amp; Contact Details
                        </h5>

                        <div class="row g-2 g-md-3">
                            <div class="col-12 col-md-6">
                                <label class="checkout-label-text">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="input-name" class="form-control checkout-form-input" value="{{ $authUser->name ?? '' }}" placeholder="Full name">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="checkout-label-text">Phone Number <span class="text-danger">*</span></label>
                                <input type="text" name="phone" id="input-phone" class="form-control checkout-form-input font-monospace" value="{{ $authUser->phone ?? '' }}" placeholder="017XXXXXXXX">
                            </div>

                            <div class="col-12">
                                <label class="checkout-label-text">
                                    Email Address @if($hasDigitalInCart)<span class="text-danger">* (Required for digital items access)</span>@else(Optional)@endif
                                </label>
                                <input type="email" name="email" id="input-email" class="form-control checkout-form-input font-monospace" value="{{ $authUser->email ?? '' }}" placeholder="example@gmail.com" {{ $hasDigitalInCart ? 'required' : '' }}>
                            </div>

                            @if($hasPhysicalInCart)
                                <div class="col-12 col-md-6">
                                    <label class="checkout-label-text">District / Zone <span class="text-danger">*</span></label>
                                    <select name="district" id="district-selector" class="form-select checkout-form-input font-monospace" onchange="syncDeliveryCharge()">
                                        @foreach($districts as $d)
                                            <option value="{{ $d }}" {{ (old('district') == $d || strtolower($d) == 'dhaka') ? 'selected' : '' }}>
                                                {{ $d }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="checkout-label-text">Delivery Address <span class="text-danger">*</span></label>
                                    <textarea name="address" id="input-address" class="form-control checkout-form-input" rows="3" placeholder="Road, House, Area...">{{ $authUser->address ?? '' }}</textarea>
                                </div>
                            @else
                                <input type="hidden" name="district" value="Digital">
                                <input type="hidden" name="address" value="Digital Delivery">
                            @endif

                            <div class="col-12">
                                <label class="checkout-label-text">Additional Notes (Optional)</label>
                                <textarea name="order_note" class="form-control checkout-form-input" rows="2" placeholder="Order instructions or delivery note..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Card -->
                    <div class="card border-0 p-3 p-md-4 checkout-card mb-3">
                        <h5 class="fw-bold mb-2 checkout-heading-text">
                            <i class="fas fa-wallet text-primary me-2"></i> Select Payment Method
                        </h5>

                        @if($isMixedOrder)
                            <div class="alert alert-info border-0 p-2.5 small mb-3 font-monospace" style="font-size: 11px; border-radius: 6px;">
                                <i class="fas fa-info-circle me-1 text-primary"></i> <strong>সম্মিলিত অর্ডার নোটিশ:</strong> আপনার কার্টে ফিজিক্যাল পণ্যের পাশাপাশি ডিজিটাল পণ্য রয়েছে। সম্পূর্ণ বিলটি একসাথে অনলাইন বা ম্যানুয়াল পেমেন্টে পরিশোধ করুন।
                            </div>
                        @elseif($hasDigitalInCart)
                            <div class="alert alert-info border-0 p-2.5 small mb-3 font-monospace" style="font-size: 11px; border-radius: 6px;">
                                <i class="fas fa-info-circle me-1 text-primary"></i> ডিজিটাল পণ্যের অ্যাক্সেস পেতে অনলাইন গেটওয়ে বা ম্যানুয়াল মেথডে সম্পূর্ণ পেমেন্ট পরিশোধ করুন।
                            </div>
                        @endif

                        <div class="d-flex flex-column gap-2">
                            @if(!$hasDigitalInCart)
                                <div class="payment-radio-box selected d-flex align-items-center" style="cursor: pointer;" onclick="setPaymentMode('cod', this, 'Cash on Delivery')">
                                    <input class="form-check-input me-3" type="radio" name="payment_method" id="pay_cod" value="Cash on Delivery" checked onchange="setPaymentMode('cod', this.closest('.payment-radio-box'), 'Cash on Delivery')">
                                    <label class="form-check-label fw-bold text-dark w-100 small" style="cursor: pointer;" for="pay_cod">
                                        <i class="fas fa-hand-holding-usd text-success fs-5 me-2 align-middle"></i> Cash on Delivery (COD)
                                    </label>
                                </div>
                            @endif

                            @if(isset($manualPayments) && count($manualPayments) > 0)
                                @foreach($manualPayments as $mIndex => $mMethod)
                                    @php
                                        $mName = strtolower($mMethod->name);
                                        $theme = 'default';
                                        if (str_contains($mName, 'bkash')) $theme = 'bkash';
                                        elseif (str_contains($mName, 'nagad')) $theme = 'nagad';
                                        elseif (str_contains($mName, 'upay')) $theme = 'upay';
                                        $qrUrl = !empty($mMethod->qr_code_image) ? $resolveCheckoutQr($mMethod->qr_code_image) : '';
                                        $isAutoSelected = $hasDigitalInCart && ($mIndex === 0);
                                    @endphp
                                    <div class="payment-radio-box {{ $isAutoSelected ? 'selected' : '' }} d-flex align-items-center" style="cursor: pointer;"
                                         onclick="setPaymentMode('manual', this, '{{ addslashes($mMethod->name) }}', '{{ addslashes($mMethod->account_number ?? '') }}', `{!! addslashes($mMethod->instruction ?? '') !!}`, '{{ $qrUrl }}', '{{ $theme }}')">
                                        <input class="form-check-input me-3" type="radio" name="payment_method" id="pay_m_{{ $mMethod->id }}" value="{{ $mMethod->name }}" {{ $isAutoSelected ? 'checked' : '' }}
                                               onchange="setPaymentMode('manual', this.closest('.payment-radio-box'), '{{ addslashes($mMethod->name) }}', '{{ addslashes($mMethod->account_number ?? '') }}', `{!! addslashes($mMethod->instruction ?? '') !!}`, '{{ $qrUrl }}', '{{ $theme }}')">
                                        <label class="form-check-label fw-bold text-dark w-100 d-flex justify-content-between align-items-center small" style="cursor: pointer;" for="pay_m_{{ $mMethod->id }}">
                                            <span>
                                                @if($theme === 'bkash')
                                                    <i class="fas fa-mobile-alt text-danger fs-5 me-2 align-middle"></i>
                                                @elseif($theme === 'nagad')
                                                    <i class="fas fa-mobile-alt text-warning fs-5 me-2 align-middle"></i>
                                                @elseif($theme === 'upay')
                                                    <i class="fas fa-mobile-alt text-info fs-5 me-2 align-middle"></i>
                                                @else
                                                    <i class="fas fa-qrcode text-primary fs-5 me-2 align-middle"></i>
                                                @endif
                                                {{ $mMethod->name }}
                                            </span>
                                            @if(str_contains($mName, 'qr'))
                                                <span class="badge bg-danger px-2 py-0.5 font-monospace" style="font-size: 9px; border-radius: 4px;">Bangla QR</span>
                                            @endif
                                        </label>
                                    </div>
                                @endforeach
                            @endif

                            @if(isset($gateways) && count($gateways) > 0)
                                @foreach($gateways as $gw)
                                    <div class="payment-radio-box d-flex align-items-center" style="cursor: pointer;" onclick="setPaymentMode('gateway', this, '{{ addslashes($gw->name) }}')">
                                        <input class="form-check-input me-3" type="radio" name="payment_method" id="pay_gw_{{ $gw->id }}" value="{{ $gw->name }}"
                                               onchange="setPaymentMode('gateway', this.closest('.payment-radio-box'), '{{ addslashes($gw->name) }}')">
                                        <label class="form-check-label fw-bold text-dark w-100 d-flex align-items-center justify-content-between small" style="cursor: pointer;" for="pay_gw_{{ $gw->id }}">
                                            <span><i class="fas fa-credit-card text-info fs-5 me-2 align-middle"></i> {{ $gw->name }}</span>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-0.5" style="font-size: 9px; border-radius: 4px;">Instant</span>
                                        </label>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <!-- Manual Payment Panel -->
                        <div id="manual-payment-panel" class="brand-pay-card theme-default d-none">
                            <div class="brand-card-header">
                                <div>
                                    <h6 class="fw-bold mb-0 text-white small" id="box-acc-title">Payment Instructions</h6>
                                    <small class="opacity-75 font-monospace" style="font-size: 10px;" id="box-acc-subtitle">Send money manually</small>
                                </div>
                                <span class="badge bg-white text-dark fw-bold px-2 py-0.5 font-monospace" style="font-size: 9px; border-radius: 4px;" id="box-acc-tag">Manual</span>
                            </div>

                            <div class="p-3">
                                <div class="row align-items-center g-3 mb-3">
                                    <div class="col-12 col-md-7">
                                        <label class="small text-muted fw-bold d-block mb-1 font-monospace" style="font-size: 10px;">ACCOUNT NUMBER:</label>
                                        <div id="box-acc-number-digits" class="mb-2 d-flex flex-wrap gap-1"></div>
                                        <div class="p-2 acc-box border mb-2" style="border-radius: 6px;">
                                            <div class="small fw-semibold" style="font-size: 11.5px; line-height: 1.4;" id="box-acc-instruction"></div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-5 text-center" id="box-qr-wrapper">
                                        <label class="small text-muted fw-bold d-block mb-1 font-monospace" style="font-size: 10px;">SCAN QR CODE</label>
                                        <img id="box-qr-img" src="" class="qr-standee-img border p-1 bg-white" alt="QR" onerror="this.onerror=null; this.src='https://placehold.co/130x130?text=QR';">
                                    </div>
                                </div>

                                <div class="alert alert-warning border-0 p-2 small mb-3 font-monospace" style="font-size: 10.5px; border-radius: 5px;">
                                    <i class="fas fa-info-circle me-1 text-warning"></i> Provide <strong>any one</strong> of the following details:
                                </div>

                                <div class="row g-2">
                                    <div class="col-12 col-md-6">
                                        <label class="checkout-label-text">Sender Number</label>
                                        <input type="text" name="sender_number" id="input-sender_number" class="form-control checkout-form-input font-monospace" placeholder="01XXXXXXXXX">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="checkout-label-text">Transaction ID (TrxID)</label>
                                        <input type="text" name="transaction_id" id="input-transaction_id" class="form-control checkout-form-input font-monospace" placeholder="TrxID">
                                    </div>
                                    <div class="col-12 mt-2">
                                        <label class="checkout-label-text">Payment Screenshot</label>
                                        <input type="file" name="payment_screenshot_file" id="input-screenshot" class="form-control checkout-form-input" accept="image/*">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side: Order Summary -->
                <div class="col-12 col-lg-5">
                    <div class="card border-0 p-3 p-md-4 checkout-card sticky-top" style="top: 20px; z-index: 10;">
                        <h5 class="fw-bold mb-3 checkout-heading-text pb-2 border-bottom">Order Summary</h5>

                        <div id="checkout-cart-items-wrapper" class="mb-3" style="max-height: 260px; overflow-y: auto;">
                            @foreach($cart as $id => $item)
                                @php
                                    $pIdCheck = $item['id'] ?? (is_numeric($id) ? $id : ($item['product_id'] ?? null));
                                    $pObj = \App\Models\Product::find($pIdCheck);

                                    $isDownloadableItem = !empty($item['is_downloadable']) 
                                        || ($pObj && (!empty($pObj->is_downloadable) || str_contains(strtolower($pObj->product_type ?? ''), 'download')));

                                    $isVirtualItem = !empty($item['is_virtual']) 
                                        || ($pObj && (!empty($pObj->is_virtual) || str_contains(strtolower($pObj->product_type ?? ''), 'virtual') || str_contains(strtolower($pObj->product_type ?? ''), 'license')));

                                    $effectiveQty = $isDownloadableItem ? 1 : (int)($item['quantity'] ?? 1);

                                    $rawPrice = $item['price'] ?? 0;
                                    if (is_string($rawPrice)) $rawPrice = preg_replace('/[^0-9.]/', '', $rawPrice);
                                    $itemPrice = (float)$rawPrice;
                                    $itemTotal = $itemPrice * $effectiveQty;

                                    $vLine = $item['variant_name'] ?? (!empty($item['color']) || !empty($item['size']) ? trim(($item['color'] ?? '') . ' / ' . ($item['size'] ?? '')) : null);
                                @endphp
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom gap-2" id="item-row-{{ $id }}">
                                    <div class="min-w-0" style="max-width: 55%;">
                                        <h6 class="mb-0 text-dark fw-bold small text-truncate" style="font-size: 12px;" title="{{ $item['title'] }}">{{ $item['title'] }}</h6>
                                        @if($isDownloadableItem)
                                            <span class="badge bg-primary-subtle text-primary border" style="font-size: 8px; padding: 1px 4px; border-radius: 4px;">Downloadable</span>
                                        @elseif($isVirtualItem)
                                            <span class="badge bg-success-subtle text-success border" style="font-size: 8px; padding: 1px 4px; border-radius: 4px;">License</span>
                                        @endif
                                        @if(!empty($vLine))
                                            <span class="badge bg-light text-primary border small" style="font-size: 8px; padding: 1px 4px; border-radius: 4px;">{{ $vLine }}</span>
                                        @endif
                                        <small class="text-muted d-block font-monospace" style="font-size: 10px;">৳{{ number_format($itemPrice, 2) }}</small>
                                    </div>

                                    <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                        @if($isDownloadableItem)
                                            <span class="badge bg-light text-dark border px-2 py-1 font-monospace" style="font-size: 10px; border-radius: 4px;">Qty: 1</span>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-secondary p-0 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 10px; border-radius: 4px;" onclick="updateQtyAjax('{{ $id }}', -1)">-</button>
                                            <span id="co-qty-text-{{ $id }}" class="small fw-bold px-1 font-monospace bn-digit" style="font-size: 11px;">{{ $effectiveQty }}</span>
                                            <button type="button" class="btn btn-sm btn-outline-secondary p-0 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 10px; border-radius: 4px;" onclick="updateQtyAjax('{{ $id }}', 1)">+</button>
                                        @endif

                                        <span class="small fw-bold text-dark ms-1 font-monospace bn-digit" style="font-size: 11.5px;">৳<span id="co-item-total-{{ $id }}">{{ number_format($itemTotal, 2) }}</span></span>
                                        <button type="button" class="btn btn-link text-danger p-0 ms-1" onclick="showConfirmModal('{{ $id }}', '{{ addslashes($item['title']) }}')" title="Remove Item">
                                            <i class="fas fa-trash-alt" style="font-size: 11px;"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Coupon & Voucher Section with Eligibility Protection -->
                        <div class="checkout-discount-box mb-3">
                            <label class="small fw-bold text-dark d-flex align-items-center justify-content-between mb-1">
                                <span><i class="fas fa-ticket-alt text-primary me-1"></i> Coupon &amp; Voucher</span>
                                @auth
                                    <a href="{{ route('user.coupons') }}" target="_blank" class="text-decoration-none small font-monospace" style="font-size: 10.5px; color: var(--co-total-price);">
                                        <i class="fas fa-wallet me-0.5"></i> Wallet
                                    </a>
                                @endauth
                            </label>

                            <!-- Coupon Input Box -->
                            <div class="input-group input-group-sm mb-2">
                                <input type="text" id="checkout-coupon-input" class="form-control form-control-sm text-uppercase font-monospace" placeholder="PROMO / COUPON CODE" value="{{ $appliedCouponCode ?? '' }}" style="font-size: 11px; height: 32px; border-radius: 5px 0 0 5px !important;">
                                <button type="button" id="btn-apply-coupon" class="btn btn-primary btn-sm px-3 fw-bold" style="height: 32px; border-radius: 0 5px 5px 0 !important;" onclick="applyCheckoutCoupon()">Apply</button>
                            </div>

                            <!-- Live Available Store Coupons Pill List (With Eligibility Indicators) -->
                            @if($availableStoreCoupons->count() > 0)
                                <div class="mb-2">
                                    <a class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-1.5" data-bs-toggle="collapse" href="#collapseStoreOffers" role="button" aria-expanded="false" style="font-size: 10.5px;">
                                        <i class="fas fa-tags text-primary"></i>
                                        <span class="fw-semibold">View Available Promo Codes ({{ $availableStoreCoupons->count() }})</span>
                                        <i class="fas fa-chevron-down" style="font-size: 9px;"></i>
                                    </a>

                                    <div class="collapse" id="collapseStoreOffers">
                                        <div class="d-flex flex-column gap-1.5 pt-1">
                                            @foreach($availableStoreCoupons as $storeCpn)
                                                @php
                                                    $cTarget = $storeCpn->target_user_type ?? 'all';
                                                    $minReq = (float)($storeCpn->min_order_amount ?? ($storeCpn->min_spend ?? 0));
                                                    
                                                    $isUserEligible = true;
                                                    $eligibilityReason = '';

                                                    if ($cTarget === 'registered_only' && !$authUser) {
                                                        $isUserEligible = false;
                                                        $eligibilityReason = 'Login Required';
                                                    } elseif ($cTarget === 'guest_only' && $authUser) {
                                                        $isUserEligible = false;
                                                        $eligibilityReason = 'Guest Only';
                                                    } elseif ($minReq > 0 && $subtotal < $minReq) {
                                                        $isUserEligible = false;
                                                        $eligibilityReason = 'Min ৳' . number_format($minReq, 0);
                                                    }
                                                @endphp

                                                <div class="promo-offer-item {{ $isUserEligible ? 'eligible' : 'ineligible' }}">
                                                    <div class="min-w-0">
                                                        <div class="d-flex align-items-center gap-1">
                                                            <strong class="font-monospace text-primary small" style="font-size: 11px;">{{ $storeCpn->code }}</strong>
                                                            @if(!empty($storeCpn->badge_text))
                                                                <span class="badge bg-warning text-dark py-0 px-1" style="font-size: 8px;">{{ $storeCpn->badge_text }}</span>
                                                            @endif
                                                        </div>
                                                        <small class="text-muted d-block text-truncate" style="font-size: 10px;">
                                                            @if($storeCpn->discount_type === 'percentage')
                                                                {{ (float)$storeCpn->discount_amount }}% OFF
                                                            @elseif($storeCpn->discount_type === 'free_shipping')
                                                                Free Shipping
                                                            @else
                                                                ৳{{ number_format($storeCpn->discount_amount ?? 0, 0) }} OFF
                                                            @endif
                                                            @if($minReq > 0) &bull; Min ৳{{ number_format($minReq, 0) }} @endif
                                                        </small>
                                                    </div>

                                                    <div class="flex-shrink-0 text-end">
                                                        @if($isUserEligible)
                                                            <button type="button" class="btn btn-outline-primary btn-sm py-0.5 px-2 font-monospace fw-bold" style="font-size: 10px; border-radius: 4px;" onclick="applyQuickPromoCode('{{ $storeCpn->code }}')">
                                                                Apply
                                                            </button>
                                                        @else
                                                            <span class="badge bg-secondary-subtle text-muted border font-monospace py-0.5 px-1.5" style="font-size: 9px;" title="{{ $eligibilityReason }}">
                                                                <i class="fas fa-lock me-0.5"></i>{{ $eligibilityReason }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Wallet Voucher Selector -->
                            @auth
                                @if(isset($userVouchers) && count($userVouchers) > 0)
                                    <div class="mb-2">
                                        <select id="checkout-voucher-select" class="form-select form-select-sm font-monospace" style="font-size: 11px; border-radius: 5px;" onchange="applySelectedVoucher(this.value)">
                                            <option value="">-- Apply from your Voucher Wallet --</option>
                                            @foreach($userVouchers as $uv)
                                                @php
                                                    $vMin = (float)($uv->min_spend ?? 0);
                                                    $isVEligible = ($vMin <= 0 || $subtotal >= $vMin);
                                                @endphp
                                                <option value="{{ $uv->voucher_code }}" {{ $appliedVoucherCode === $uv->voucher_code ? 'selected' : '' }} {{ !$isVEligible ? 'disabled' : '' }}>
                                                    {{ $uv->voucher_title }} (Code: {{ $uv->voucher_code }}) {{ !$isVEligible ? '[Min ৳' . number_format($vMin, 0) . ']' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                            @endauth

                            <!-- Applied Discount Badges -->
                            <div class="d-flex flex-wrap gap-1 pt-1" id="applied-discounts-container">
                                @if(!empty($appliedCouponCode) && $couponDiscount > 0)
                                    <div class="applied-tag-pill" id="tag-coupon-pill">
                                        <i class="fas fa-tag"></i>
                                        <span>Coupon: <strong>{{ $appliedCouponCode }}</strong> (-৳{{ number_format($couponDiscount, 2) }})</span>
                                        <i class="fas fa-times btn-remove-tag ms-1" onclick="removeCheckoutCoupon()" title="Remove coupon"></i>
                                    </div>
                                @endif

                                @if(!empty($appliedVoucherCode) && $voucherDiscount > 0)
                                    <div class="applied-tag-pill" id="tag-voucher-pill">
                                        <i class="fas fa-gift"></i>
                                        <span>Voucher: <strong>{{ $appliedVoucherCode }}</strong> (-৳{{ number_format($voucherDiscount, 2) }})</span>
                                        <i class="fas fa-times btn-remove-tag ms-1" onclick="removeCheckoutVoucher()" title="Remove voucher"></i>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Price Breakdown -->
                        <div class="d-flex justify-content-between text-muted mb-2 small font-monospace" style="font-size: 11.5px;">
                            <span>Subtotal:</span>
                            <strong class="text-dark">৳<span id="summary-subtotal-val">{{ number_format($subtotal, 2) }}</span></strong>
                        </div>

                        <div class="d-flex justify-content-between text-success mb-2 small font-monospace {{ $couponDiscount > 0 ? '' : 'd-none' }}" id="row-coupon-discount" style="font-size: 11.5px;">
                            <span>Coupon Discount:</span>
                            <strong>-৳<span id="summary-coupon-val">{{ number_format($couponDiscount, 2) }}</span></strong>
                        </div>

                        <div class="d-flex justify-content-between text-success mb-2 small font-monospace {{ $voucherDiscount > 0 ? '' : 'd-none' }}" id="row-voucher-discount" style="font-size: 11.5px;">
                            <span>Voucher Discount:</span>
                            <strong>-৳<span id="summary-voucher-val">{{ number_format($voucherDiscount, 2) }}</span></strong>
                        </div>

                        <div class="d-flex justify-content-between text-muted mb-2 small font-monospace" style="font-size: 11.5px;">
                            <span>Delivery Zone:</span>
                            <strong class="text-primary"><span id="summary-zone-name">{{ $initialShipping['zone_name'] ?? 'Standard Delivery' }}</span></strong>
                        </div>

                        <div class="d-flex justify-content-between text-muted mb-2 small font-monospace" style="font-size: 11.5px;">
                            <span>Delivery Charge:</span>
                            <strong class="text-dark">৳<span id="summary-delivery-val">{{ number_format($effectiveDeliveryCharge, 2) }}</span></strong>
                        </div>

                        <!-- NBR Tax & VAT Breakdown Section (Shows only when VAT is enabled and total tax > 0) -->
                        <div class="d-flex justify-content-between text-muted mb-2 small font-monospace {{ ($isVatEnabled && $orderTaxAmount > 0) ? '' : 'd-none' }}" id="row-tax-amount" style="font-size: 11.5px;">
                            <span>
                                {{ $vatLabelText }}:
                                <span class="badge bg-light text-muted border ms-1 font-monospace" id="tax-inclusion-badge" style="font-size: 8.5px; padding: 1px 4px;">
                                    {{ $isTaxInclusive ? 'Inclusive' : 'Exclusive' }}
                                </span>
                            </span>
                            <strong class="{{ $exclusiveTax > 0 ? 'text-primary' : 'text-dark' }}">
                                ৳<span id="summary-tax-val">{{ number_format($orderTaxAmount, 2) }}</span>
                            </strong>
                        </div>

                        <div class="d-flex justify-content-between text-muted mb-2 small font-monospace {{ ($isVatEnabled && $shippingTaxAmount > 0) ? '' : 'd-none' }}" id="row-shipping-tax-detail" style="font-size: 10.5px; opacity: 0.85;">
                            <span class="ps-2">&bull; Shipping VAT:</span>
                            <span>৳<span id="summary-shipping-tax-val">{{ number_format($shippingTaxAmount, 2) }}</span></span>
                        </div>

                        <hr class="my-2 opacity-25">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-bold text-dark small" style="font-size: 13px;">Grand Total (Payable):</span>
                            <h4 class="fw-bold checkout-price-total mb-0 font-monospace bn-digit text-danger" style="font-size: 18px;">
                                ৳<span id="summary-grandtotal-val">{{ number_format($grandTotal, 2) }}</span>
                            </h4>
                        </div>

                        <button type="button" id="confirm-order-submit-btn" onclick="dispatchCheckoutOrder()" class="btn checkout-order-submit-btn w-100 shadow-xs fw-bold">
                            <i class="fas fa-lock me-1.5"></i> Complete Order
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- =========================================================================
     MODAL: Minimalistic Clean Modal System
     ========================================================================= -->
<div class="modal fade" id="customAppAlertModal" tabindex="-1" aria-hidden="true" style="z-index: 108000;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 390px;">
        <div class="modal-content minimal-modal-card">
            <h6 class="minimal-modal-title" id="modal-alert-title">Are you sure you want to remove this item?</h6>
            <p class="minimal-modal-subtext" id="modal-alert-message">This item will be removed from your order.</p>
            <div class="d-flex align-items-center gap-3" id="modal-alert-btn-container">
                <!-- Injected via JavaScript -->
            </div>
        </div>
    </div>
</div>

<script>
let isUpdating = false;

function showAppModal(title, message, type = 'confirm', onConfirm = null) {
    const modalEl = document.getElementById('customAppAlertModal');
    if (!modalEl) return;

    document.getElementById('modal-alert-title').innerText = title;
    document.getElementById('modal-alert-message').innerText = message;

    const btnContainer = document.getElementById('modal-alert-btn-container');

    if (type === 'confirm') {
        btnContainer.innerHTML = `
            <button type="button" id="modal-alert-confirm-btn" class="btn-minimal-delete col-6">Delete</button>
            <button type="button" class="btn-minimal-cancel col-6" data-bs-dismiss="modal">Cancel</button>
        `;
        document.getElementById('modal-alert-confirm-btn').onclick = function() {
            bootstrap.Modal.getInstance(modalEl)?.hide();
            if (onConfirm) onConfirm();
        };
    } else {
        btnContainer.innerHTML = `
            <button type="button" class="btn-minimal-delete col-12" style="background-color: #2563eb !important; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.28) !important;" data-bs-dismiss="modal">OK</button>
        `;
    }

    bootstrap.Modal.getOrCreateInstance(modalEl).show();
}

function showConfirmModal(productId, productTitle = '') {
    showAppModal(
        'Are you sure you want to remove this item?',
        (productTitle ? `"${productTitle}"` : 'This product') + ' will be removed from your order.',
        'confirm',
        () => {
            executeRemoveCheckoutItem(productId);
        }
    );
}

function syncDeliveryCharge() {
    const selector = document.getElementById('district-selector');
    const district = selector ? selector.value : 'Dhaka';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch("{{ route('checkout.deliveryCharge') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ district: district })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('summary-subtotal-val').innerText = data.subtotal;
            document.getElementById('summary-delivery-val').innerText = data.delivery_charge;
            document.getElementById('summary-grandtotal-val').innerText = data.grand_total;
            if (document.getElementById('summary-zone-name')) {
                document.getElementById('summary-zone-name').innerText = data.zone;
            }

            // Real-time Dynamic Tax & VAT Display Control
            const isVatActive = (data.is_vat_enabled === true || data.is_vat_enabled === 'true' || data.is_vat_enabled === 1 || data.is_vat_enabled === '1');
            const taxAmountVal = parseFloat(data.tax_amount) || 0;
            const shippingTaxVal = parseFloat(data.shipping_tax) || 0;
            const taxRow = document.getElementById('row-tax-amount');
            const sRow = document.getElementById('row-shipping-tax-detail');

            if (taxRow) {
                if (isVatActive && taxAmountVal > 0) {
                    taxRow.classList.remove('d-none');
                } else {
                    taxRow.classList.add('d-none');
                }
            }

            if (data.tax_amount !== undefined && document.getElementById('summary-tax-val')) {
                document.getElementById('summary-tax-val').innerText = data.tax_amount;
            }
            if (data.is_tax_inclusive !== undefined && document.getElementById('tax-inclusion-badge')) {
                document.getElementById('tax-inclusion-badge').innerText = data.is_tax_inclusive ? 'Inclusive' : 'Exclusive';
            }
            if (document.getElementById('summary-shipping-tax-val')) {
                document.getElementById('summary-shipping-tax-val').innerText = data.shipping_tax !== undefined ? data.shipping_tax : '0.00';
            }
            if (sRow) {
                if (isVatActive && shippingTaxVal > 0) {
                    sRow.classList.remove('d-none');
                } else {
                    sRow.classList.add('d-none');
                }
            }

            const couponVal = parseFloat(data.coupon_discount) || 0;
            const cRow = document.getElementById('row-coupon-discount');
            if (cRow) {
                if (couponVal > 0) {
                    cRow.classList.remove('d-none');
                    document.getElementById('summary-coupon-val').innerText = data.coupon_discount;
                    const cPillSpan = document.querySelector('#tag-coupon-pill span');
                    if (cPillSpan) {
                        const codeMatch = cPillSpan.querySelector('strong')?.innerText;
                        if (codeMatch) {
                            cPillSpan.innerHTML = `Coupon: <strong>${codeMatch}</strong> (-৳${data.coupon_discount})`;
                        }
                    }
                } else {
                    cRow.classList.add('d-none');
                    document.getElementById('tag-coupon-pill')?.remove();
                }
            }

            const voucherVal = parseFloat(data.voucher_discount) || 0;
            const vRow = document.getElementById('row-voucher-discount');
            if (vRow) {
                if (voucherVal > 0) {
                    vRow.classList.remove('d-none');
                    document.getElementById('summary-voucher-val').innerText = data.voucher_discount;
                    const vPillSpan = document.querySelector('#tag-voucher-pill span');
                    if (vPillSpan) {
                        const codeMatch = vPillSpan.querySelector('strong')?.innerText;
                        if (codeMatch) {
                            vPillSpan.innerHTML = `Voucher: <strong>${codeMatch}</strong> (-৳${data.voucher_discount})`;
                        }
                    }
                } else {
                    vRow.classList.add('d-none');
                    document.getElementById('tag-voucher-pill')?.remove();
                }
            }
        }
    })
    .finally(() => {
        isUpdating = false;
    });
}

function setPaymentMode(mode, element, name = '', number = '', instruction = '', qrUrl = '', theme = 'default') {
    document.querySelectorAll('.payment-radio-box').forEach(el => {
        el.classList.remove('selected', 'border-primary', 'bg-light');
    });
    
    if (element) {
        element.classList.add('selected', 'border-primary', 'bg-light');
        const radio = element.querySelector('input[type="radio"]');
        if (radio && !radio.checked) {
            radio.checked = true;
        }
    }

    document.getElementById('input-payment_type').value = mode;

    const manualPanel = document.getElementById('manual-payment-panel');
    if (mode === 'manual') {
        manualPanel.className = 'brand-pay-card theme-' + theme;
        manualPanel.classList.remove('d-none');

        document.getElementById('box-acc-title').innerText = name + ' Payment';
        document.getElementById('box-acc-tag').innerText = name;
        document.getElementById('box-acc-instruction').innerHTML = instruction || 'Scan QR or send money manually.';

        const digitsBox = document.getElementById('box-acc-number-digits');
        digitsBox.innerHTML = '';
        if (number) {
            number.split('').forEach(d => {
                const span = document.createElement('span');
                span.className = 'acc-number-digit';
                span.innerText = d;
                digitsBox.appendChild(span);
            });
        }

        const qrWrapper = document.getElementById('box-qr-wrapper');
        const qrImg = document.getElementById('box-qr-img');
        if (qrUrl) {
            qrImg.src = qrUrl;
            qrWrapper.style.display = 'block';
        } else {
            qrWrapper.style.display = 'none';
        }
    } else {
        if (manualPanel) {
            manualPanel.classList.add('d-none');
        }
    }
}

function updateQtyAjax(productId, delta) {
    if (isUpdating) return;
    const qtyEl = document.getElementById(`co-qty-text-${productId}`);
    if (!qtyEl) return;

    let currentQty = parseInt(qtyEl.innerText) || 1;
    let newQty = currentQty + delta;
    if (newQty <= 0) {
        const itemRow = document.getElementById(`item-row-${productId}`);
        const title = itemRow ? itemRow.querySelector('h6')?.innerText : '';
        showConfirmModal(productId, title);
        return;
    }

    isUpdating = true;
    qtyEl.innerText = newQty;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const district = document.getElementById('district-selector')?.value || 'Dhaka';

    fetch("{{ route('cart.updateQty') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ id: productId, quantity: newQty, district: district })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById(`co-item-total-${productId}`).innerText = data.item_total;
            document.getElementById('summary-subtotal-val').innerText = data.subtotal;
            syncDeliveryCharge();
        } else {
            isUpdating = false;
        }
    })
    .catch(() => {
        isUpdating = false;
    });
}

function executeRemoveCheckoutItem(productId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch("{{ route('cart.remove') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ id: productId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            const row = document.getElementById(`item-row-${productId}`);
            if (row) {
                row.style.transition = 'all 0.25s ease';
                row.style.opacity = '0';
                row.style.transform = 'scale(0.95)';
                setTimeout(() => row.remove(), 250);
            }
            document.getElementById('summary-subtotal-val').innerText = data.subtotal;
            if (data.is_empty) {
                window.location.href = "{{ route('shop') }}";
            } else {
                syncDeliveryCharge();
            }
        }
    });
}

function applyQuickPromoCode(code) {
    const input = document.getElementById('checkout-coupon-input');
    if (input) {
        input.value = code;
        applyCheckoutCoupon();
    }
}

function applyCheckoutCoupon() {
    const input = document.getElementById('checkout-coupon-input');
    const code = input ? input.value.trim() : '';
    if (!code) {
        showAppModal('Coupon Required', 'Please enter a valid coupon code.', 'info');
        return;
    }

    const btn = document.getElementById('btn-apply-coupon');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const district = document.getElementById('district-selector')?.value || 'Dhaka';

    fetch("{{ route('checkout.coupon.apply') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ coupon_code: code, district: district })
    })
    .then(async res => {
        const data = await res.json();
        if (!res.ok) throw data;
        return data;
    })
    .then(data => {
        showAppModal('Success', data.message || 'Coupon applied successfully!', 'info');
        renderDiscountTags(data.coupon_code, data.coupon_discount, null, null);
        syncDeliveryCharge();
    })
    .catch(err => {
        showAppModal('Invalid Coupon', err.message || 'Could not apply coupon.', 'info');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = 'Apply';
    });
}

function removeCheckoutCoupon() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const district = document.getElementById('district-selector')?.value || 'Dhaka';

    fetch("{{ route('checkout.coupon.remove') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ district: district })
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('checkout-coupon-input').value = '';
        document.getElementById('tag-coupon-pill')?.remove();
        syncDeliveryCharge();
    });
}

function applySelectedVoucher(code) {
    if (!code) return;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const district = document.getElementById('district-selector')?.value || 'Dhaka';

    fetch("{{ route('checkout.voucher.apply') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ voucher_code: code, district: district })
    })
    .then(async res => {
        const data = await res.json();
        if (!res.ok) throw data;
        return data;
    })
    .then(data => {
        showAppModal('Success', data.message || 'Voucher applied successfully!', 'info');
        renderDiscountTags(null, null, data.voucher_code, data.voucher_discount);
        syncDeliveryCharge();
    })
    .catch(err => {
        showAppModal('Voucher Error', err.message || 'Could not apply voucher.', 'info');
        document.getElementById('checkout-voucher-select').value = '';
    });
}

function removeCheckoutVoucher() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const district = document.getElementById('district-selector')?.value || 'Dhaka';

    fetch("{{ route('checkout.voucher.remove') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ district: district })
    })
    .then(res => res.json())
    .then(data => {
        const sel = document.getElementById('checkout-voucher-select');
        if (sel) sel.value = '';
        document.getElementById('tag-voucher-pill')?.remove();
        syncDeliveryCharge();
    });
}

function renderDiscountTags(cCode, cDiscount, vCode, vDiscount) {
    const container = document.getElementById('applied-discounts-container');
    if (!container) return;

    if (cCode && parseFloat(cDiscount) > 0) {
        document.getElementById('tag-coupon-pill')?.remove();
        const cPill = document.createElement('div');
        cPill.className = 'applied-tag-pill';
        cPill.id = 'tag-coupon-pill';
        cPill.innerHTML = `<i class="fas fa-tag"></i> <span>Coupon: <strong>${cCode}</strong> (-৳${cDiscount})</span> <i class="fas fa-times btn-remove-tag ms-1" onclick="removeCheckoutCoupon()" title="Remove coupon"></i>`;
        container.appendChild(cPill);
    }

    if (vCode && parseFloat(vDiscount) > 0) {
        document.getElementById('tag-voucher-pill')?.remove();
        const vPill = document.createElement('div');
        vPill.className = 'applied-tag-pill';
        vPill.id = 'tag-voucher-pill';
        vPill.innerHTML = `<i class="fas fa-gift"></i> <span>Voucher: <strong>${vCode}</strong> (-৳${vDiscount})</span> <i class="fas fa-times btn-remove-tag ms-1" onclick="removeCheckoutVoucher()" title="Remove voucher"></i>`;
        container.appendChild(vPill);
    }
}

function dispatchCheckoutOrder() {
    const form = document.getElementById('checkout-main-form');
    const btn = document.getElementById('confirm-order-submit-btn');

    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    const alertBox = document.getElementById('checkout-global-error-alert');
    alertBox.classList.add('d-none');

    const payType = document.getElementById('input-payment_type')?.value || '';
    if (payType === 'manual') {
        const sender = document.getElementById('input-sender_number')?.value?.trim() || '';
        const trx = document.getElementById('input-transaction_id')?.value?.trim() || '';
        const ss = document.getElementById('input-screenshot')?.files?.length || 0;

        if (!sender && !trx && ss === 0) {
            showAppModal('Required Field Missing', 'দয়া করে Sender Number, Transaction ID অথবা Screenshot-এর যেকোনো একটি দিন।', 'info');
            return;
        }
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Placing Order...';

    const formData = new FormData(form);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch("{{ route('checkout.process') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(async res => {
        const data = await res.json();
        if (!res.ok) throw data;
        return data;
    })
    .then(data => {
        if (data.status === 'success') {
            window.location.href = data.redirect;
        } else {
            throw data;
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-lock me-1.5"></i> Complete Order';
        
        let errorMsg = '';
        if (err.errors) {
            errorMsg = Object.values(err.errors)[0][0];
        } else {
            errorMsg = err.message || 'Something went wrong. Please check your details.';
        }
        showAppModal('Validation Error', errorMsg, 'info');
    });
}

document.addEventListener('DOMContentLoaded', () => {
    syncDeliveryCharge();

    @if($hasDigitalInCart && isset($manualPayments) && count($manualPayments) > 0)
        const firstManualEl = document.querySelector('.payment-radio-box.selected');
        if (firstManualEl) {
            firstManualEl.click();
        }
    @endif
});

/* =========================================================================
   REAL-TIME ABANDONED CART RECOVERY TELEMETRY (Resilient Standalone Hook)
   ========================================================================= */
(function () {
    let syncTimer = null;

    function sendAbandonedCartTelemetry() {
        const emailEl = document.getElementById('input-email');
        const phoneEl = document.getElementById('input-phone');
        const nameEl  = document.getElementById('input-name');

        const email = emailEl ? emailEl.value.trim() : '';
        const phone = phoneEl ? phoneEl.value.trim() : '';
        const name  = nameEl  ? nameEl.value.trim()  : '';

        const isValidEmail = email.includes('@') && email.includes('.');
        const isValidPhone = phone.replace(/[^0-9]/g, '').length >= 10;

        if (isValidEmail || isValidPhone) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                || document.querySelector('input[name="_token"]')?.value 
                || '{{ csrf_token() }}';

            const syncUrl = "{{ route('cart.abandoned.sync') }}";

            fetch(syncUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    email: email,
                    phone: phone,
                    name: name,
                    _token: csrfToken
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && window.sessionStorage) {
                    sessionStorage.setItem('active_abandoned_cart_token', data.recovery_token);
                }
            })
            .catch(() => {
                // Background silent heartbeat
            });
        }
    }

    function triggerDebouncedSync() {
        clearTimeout(syncTimer);
        syncTimer = setTimeout(sendAbandonedCartTelemetry, 1000);
    }

    function initCartTracker() {
        const inputs = [
            document.getElementById('input-email'),
            document.getElementById('input-phone'),
            document.getElementById('input-name')
        ];

        inputs.forEach(input => {
            if (input) {
                input.addEventListener('input', triggerDebouncedSync);
                input.addEventListener('change', sendAbandonedCartTelemetry);
                input.addEventListener('blur', sendAbandonedCartTelemetry);
            }
        });

        const hasExistingContact = (inputs[0] && inputs[0].value.trim()) || (inputs[1] && inputs[1].value.trim());
        if (hasExistingContact) {
            triggerDebouncedSync();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCartTracker);
    } else {
        initCartTracker();
    }
})();
</script>
@endsection