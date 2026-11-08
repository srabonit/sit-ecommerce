
@extends('layouts.admin')

@section('title', 'Create New Coupon')

@php
    $couponIndexUrl = Route::has('admin.promo.coupons.index') ? route('admin.promo.coupons.index', ['tab' => 'coupons']) : url('/admin/promo/coupons?tab=coupons');
    $couponStoreUrl = Route::has('admin.promo.coupons.store') ? route('admin.promo.coupons.store') : url('/admin/promo/coupons/store');

    $preloadedProducts = ($products ?? collect())->map(function($p) {
        return [
            'id'    => $p->id,
            'title' => $p->title ?? 'Product #' . $p->id,
            'sku'   => $p->sku ?? 'N/A',
            'price' => (float)($p->regular_price ?? 0),
            'image' => !empty($p->image) ? asset(ltrim($p->image, '/')) : null,
        ];
    });

    $preloadedCategories = ($categories ?? collect())->map(function($c) {
        return [
            'id'   => $c->id,
            'name' => $c->name ?? 'Category #' . $c->id,
            'slug' => $c->slug ?? '',
        ];
    });

    $oldProductIds = old('product_ids', []);
    $oldCategoryIds = old('category_ids', []);
@endphp

@section('admin_content')
<style>
    /* =========================================
       COUPON PAGE - KAIADMIN CLASSIC THEME
    ========================================= */

    #createCouponPage {
        --coupon-primary: var(--bs-primary, #1572e8);
        --coupon-border: var(--bs-border-color, #e5e7eb);
        --coupon-text: var(--bs-body-color, #2a2f5b);
        --coupon-muted: #8a92a6;
        --coupon-bg: #f8f9fa;
        --coupon-radius: 5px;
        color: var(--coupon-text);
        min-width: 0;
    }

    #createCouponPage,
    #createCouponPage * {
        box-sizing: border-box;
    }

    #createCouponPage .coupon-page-header {
        gap: 12px;
        margin-bottom: 22px;
    }

    #createCouponPage .coupon-page-title {
        font-size: clamp(18px, 2vw, 21px);
        font-weight: 600;
        line-height: 1.35;
        color: var(--coupon-text);
    }

    #createCouponPage .coupon-page-subtitle {
        font-size: 12px;
        color: var(--coupon-muted);
        line-height: 1.5;
    }

    /* Cards */

    #createCouponPage .card {
        background: #fff;
        border: 1px solid var(--coupon-border) !important;
        border-radius: 5px !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .035) !important;
        padding: 20px !important;
        min-width: 0;
    }

    #createCouponPage .coupon-section-title {
        display: flex;
        align-items: center;
        gap: 9px;
        font-size: 14px;
        font-weight: 600;
        color: var(--coupon-text);
        line-height: 1.4;
        margin-bottom: 17px;
    }

    #createCouponPage .coupon-section-title i {
        font-size: 15px;
        width: 17px;
        text-align: center;
    }

    #createCouponPage .coupon-section-description {
        color: var(--coupon-muted);
        font-size: 12px;
        line-height: 1.5;
        margin-bottom: 15px;
    }

    /* Form Controls */

    #createCouponPage .form-label {
        display: block;
        margin-bottom: 6px;
        color: var(--coupon-text);
        font-size: 12px;
        font-weight: 600;
    }

    #createCouponPage .form-control,
    #createCouponPage .form-select {
        min-height: 37px;
        border: 1px solid #e1e5eb;
        border-radius: 5px !important;
        color: var(--coupon-text);
        font-size: 12px;
        padding: 8px 11px;
        background-color: #fff;
        box-shadow: none;
        max-width: 100%;
    }

    #createCouponPage textarea.form-control {
        min-height: 70px;
        resize: vertical;
    }

    #createCouponPage .form-control:focus,
    #createCouponPage .form-select:focus {
        border-color: var(--coupon-primary);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .09);
    }

    #createCouponPage .form-control::placeholder {
        color: #a0a6b5;
        opacity: 1;
    }

    #createCouponPage .input-group {
        flex-wrap: nowrap;
        min-width: 0;
    }

    #createCouponPage .input-group .form-control {
        min-width: 0;
    }

    #createCouponPage .input-group-text {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 38px;
        padding: 7px 10px;
        background: #f8f9fa;
        border-color: #e1e5eb;
        color: #6c757d;
        font-size: 12px;
        border-radius: 5px;
    }

    #createCouponPage .input-group > :not(:first-child) {
        margin-left: -1px;
    }

    #createCouponPage .input-group > :first-child {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    #createCouponPage .input-group > :last-child {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    #createCouponPage .form-text,
    #createCouponPage .text-muted {
        color: var(--coupon-muted) !important;
    }

    #createCouponPage small.text-muted {
        display: inline-block;
        font-size: 11px !important;
        line-height: 1.5;
    }

    /* Buttons */

    #createCouponPage .btn {
        min-height: 35px;
        padding: 7px 13px;
        border-radius: 5px !important;
        font-size: 12px;
        font-weight: 500;
        box-shadow: none !important;
        transition: all .15s ease;
    }

    #createCouponPage .btn-primary {
        background: var(--coupon-primary);
        border-color: var(--coupon-primary);
    }

    #createCouponPage .btn:hover {
        filter: brightness(.96);
    }

    #createCouponPage .btn:focus-visible {
        outline: 2px solid var(--coupon-primary);
        outline-offset: 2px;
    }

    #createCouponPage .coupon-submit-btn {
        min-height: 41px;
        font-size: 13px;
        font-weight: 600;
    }

    /* Target User Selection */

    #createCouponPage .target-user-card {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        min-height: 66px;
        padding: 11px 12px;
        border: 1px solid var(--coupon-border);
        border-radius: 5px;
        background: #fff;
        cursor: pointer;
        transition: border-color .15s ease, background .15s ease;
    }

    #createCouponPage .target-user-card:hover {
        border-color: var(--coupon-primary);
        background: #f8faff;
    }

    #createCouponPage .target-user-card.active {
        border-color: var(--coupon-primary);
        background: rgba(21, 114, 232, .055);
    }

    #createCouponPage .target-user-card .form-check-input {
        flex-shrink: 0;
        margin: 0;
    }

    #createCouponPage .target-user-card strong {
        font-size: 12px !important;
        line-height: 1.4;
    }

    #createCouponPage .target-user-card small {
        font-size: 10.5px !important;
        line-height: 1.4;
    }

    /* Switches */

    #createCouponPage .form-check {
        min-height: 25px;
        padding-left: 2.6em;
    }

    #createCouponPage .form-check-input {
        width: 2em;
        height: 1.05em;
        margin-top: .2em;
        cursor: pointer;
        border-color: #cbd0d9;
        box-shadow: none;
    }

    #createCouponPage .form-check-input:checked {
        background-color: var(--coupon-primary);
        border-color: var(--coupon-primary);
    }

    #createCouponPage .form-check-label {
        cursor: pointer;
        font-size: 12px;
    }

    /* Search Picker */

    #createCouponPage .search-picker-box {
        position: relative;
        width: 100%;
        min-width: 0;
    }

    #createCouponPage .search-picker-dropdown {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        z-index: 1050;
        display: none;
        max-height: 260px;
        overflow-y: auto;
        overflow-x: hidden;
        background: #fff;
        border: 1px solid var(--coupon-border);
        border-radius: 5px;
        box-shadow: 0 5px 18px rgba(0, 0, 0, .10);
    }

    #createCouponPage .search-picker-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 10px 12px;
        border-bottom: 1px solid #f0f1f4;
        cursor: pointer;
        transition: background .15s ease;
        min-width: 0;
    }

    #createCouponPage .search-picker-item:last-child {
        border-bottom: 0;
    }

    #createCouponPage .search-picker-item:hover {
        background: #f5f8fc;
    }

    #createCouponPage .search-picker-item > div {
        min-width: 0;
    }

    #createCouponPage .search-picker-item strong {
        font-size: 12px !important;
        overflow-wrap: anywhere;
    }

    #createCouponPage .search-picker-item small {
        font-size: 10.5px !important;
    }

    #createCouponPage .search-picker-item .badge {
        flex-shrink: 0;
    }

    /* Selected Items */

    #createCouponPage .coupon-chips-container {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 5px;
        min-height: 54px;
        padding: 9px !important;
        border: 1px solid var(--coupon-border) !important;
        border-radius: 5px !important;
        background: #f8f9fb !important;
        overflow-wrap: anywhere;
    }

    #createCouponPage .selected-tag-chip {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        max-width: 100%;
        margin: 0;
        padding: 5px 8px;
        border: 1px solid #d7e5fa;
        border-radius: 5px;
        background: #edf4ff;
        color: #2457a7;
        font-size: 11px;
        font-weight: 600;
        overflow-wrap: anywhere;
        animation: couponFadeIn .15s ease-in;
    }

    #createCouponPage .selected-tag-chip .chip-remove-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 19px;
        height: 19px;
        padding: 0;
        border: 0;
        background: transparent;
        color: #dc3545;
        font-size: 15px;
        line-height: 1;
        cursor: pointer;
    }

    #createCouponPage .selected-tag-chip .chip-remove-btn:hover {
        color: #a71d2a;
    }

    @keyframes couponFadeIn {
        from { opacity: 0; transform: scale(.96); }
        to { opacity: 1; transform: scale(1); }
    }

    /* Badges */

    #createCouponPage .badge {
        border-radius: 4px;
        font-size: 10px;
        font-weight: 600;
        padding: 5px 7px;
    }

    /* Alerts */

    #createCouponPage .alert {
        border-radius: 5px !important;
        font-size: 12px;
        overflow-wrap: anywhere;
    }

    /* Responsive */

    @media (min-width: 1200px) {
        #createCouponPage .card {
            padding: 21px !important;
        }
    }

    @media (max-width: 991.98px) {
        #createCouponPage .coupon-page-header {
            margin-bottom: 18px;
        }

        #createCouponPage .card {
            padding: 17px !important;
        }

        #createCouponPage .target-user-card {
            min-height: 62px;
        }
    }

    @media (max-width: 767.98px) {
        #createCouponPage {
            padding-left: 12px !important;
            padding-right: 12px !important;
            padding-top: 18px !important;
        }

        #createCouponPage .coupon-page-header {
            align-items: flex-start !important;
        }

        #createCouponPage .coupon-page-header > div {
            min-width: 0;
            flex: 1 1 100%;
        }

        #createCouponPage .coupon-page-header > a {
            align-self: flex-start;
        }

        #createCouponPage .card {
            padding: 15px !important;
            margin-bottom: 15px !important;
        }

        #createCouponPage .coupon-section-title {
            margin-bottom: 14px;
        }

        #createCouponPage .row.g-3 {
            --bs-gutter-y: 12px;
        }

        #createCouponPage .target-user-card {
            min-height: 58px;
        }

        #createCouponPage .search-picker-dropdown {
            max-height: 220px;
        }
    }

    @media (max-width: 575.98px) {
        #createCouponPage .coupon-page-title {
            font-size: 18px;
        }

        #createCouponPage .coupon-page-subtitle {
            font-size: 11px;
        }

        #createCouponPage .card {
            padding: 13px !important;
        }

        #createCouponPage .coupon-section-title {
            font-size: 13px;
        }

        #createCouponPage .form-control,
        #createCouponPage .form-select {
            min-height: 39px;
            font-size: 12px;
        }

        #createCouponPage .input-group .btn {
            padding-left: 9px;
            padding-right: 9px;
            white-space: nowrap;
        }

        #createCouponPage .target-user-card {
            min-height: 55px;
        }

        #createCouponPage .coupon-submit-btn {
            min-height: 42px;
        }
    }

    @media (max-width: 360px) {
        #createCouponPage {
            padding-left: 9px !important;
            padding-right: 9px !important;
        }

        #createCouponPage .card {
            padding: 11px !important;
        }

        #createCouponPage .coupon-page-header > a {
            padding-left: 9px;
            padding-right: 9px;
        }
    }
</style>

<div class="container-fluid px-3 py-4" id="createCouponPage">

    <!-- Page Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between coupon-page-header">
        <div>
            <h4 class="coupon-page-title mb-1">
                <i class="fas fa-ticket-alt text-primary me-2"></i>Create New Coupon
            </h4>
            <p class="coupon-page-subtitle mb-0">Configure discounts and coupon eligibility.</p>
        </div>

        <a href="{{ $couponIndexUrl }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-4">
            <div class="d-flex align-items-center mb-1">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong class="small">Please fix these errors:</strong>
            </div>
            <ul class="mb-0 ps-3 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ $couponStoreUrl }}" method="POST" id="createCouponForm">
        @csrf

        <div class="row g-3">

            <!-- Left: Coupon Configuration -->
            <div class="col-12 col-lg-8">

                <!-- Basic Information -->
                <div class="card mb-3">
                    <h5 class="coupon-section-title">
                        <i class="fas fa-info-circle text-primary"></i>
                        Basic Information
                    </h5>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Coupon Code <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="code" id="input_coupon_code" class="form-control text-uppercase font-monospace fw-bold" placeholder="SUMMER50" value="{{ old('code') }}" required>
                                <button type="button" class="btn btn-outline-primary" onclick="generateRandomCouponCode()">
                                    <i class="fas fa-magic me-1"></i> Generate
                                </button>
                            </div>
                            <small class="text-muted">Customer enters this code at checkout.</small>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Coupon Name / Note</label>
                            <input type="text" name="name" class="form-control form-control-sm" placeholder="Summer Mega Discount" value="{{ old('name') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Badge Text</label>
                            <input type="text" name="badge_text" class="form-control form-control-sm" placeholder="20% OFF or LIMITED DEAL" value="{{ old('badge_text') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Customer Description</label>
                            <textarea name="customer_description" class="form-control form-control-sm" rows="2" placeholder="Get ৳150 off on minimum purchase of ৳1000">{{ old('customer_description') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Customer Eligibility -->
                <div class="card mb-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                        <h5 class="coupon-section-title mb-0">
                            <i class="fas fa-users text-primary"></i>
                            Customer Eligibility
                        </h5>
                        <span class="badge bg-light text-muted border">TARGET USERS</span>
                    </div>

                    <p class="coupon-section-description">Choose who can use this coupon.</p>

                    @php $targetType = old('target_user_type', 'all'); @endphp

                    <div class="row g-2">
                        <div class="col-12 col-md-4">
                            <label class="target-user-card {{ $targetType === 'all' ? 'active' : '' }}" id="card_target_all">
                                <input type="radio" name="target_user_type" value="all" class="form-check-input mt-0" {{ $targetType === 'all' ? 'checked' : '' }} onchange="selectTargetUserType('all')">
                                <div>
                                    <strong class="d-block text-dark">All Customers</strong>
                                    <small class="text-muted d-block">Registered &amp; Guests</small>
                                </div>
                            </label>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="target-user-card {{ $targetType === 'registered_only' ? 'active' : '' }}" id="card_target_registered">
                                <input type="radio" name="target_user_type" value="registered_only" class="form-check-input mt-0" {{ $targetType === 'registered_only' ? 'checked' : '' }} onchange="selectTargetUserType('registered_only')">
                                <div>
                                    <strong class="d-block text-dark">Registered Only</strong>
                                    <small class="text-muted d-block">Must sign in</small>
                                </div>
                            </label>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="target-user-card {{ $targetType === 'guest_only' ? 'active' : '' }}" id="card_target_guest">
                                <input type="radio" name="target_user_type" value="guest_only" class="form-check-input mt-0" {{ $targetType === 'guest_only' ? 'checked' : '' }} onchange="selectTargetUserType('guest_only')">
                                <div>
                                    <strong class="d-block text-dark">Guests Only</strong>
                                    <small class="text-muted d-block">Guest checkout</small>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Discount Calculation -->
                <div class="card mb-3">
                    <h5 class="coupon-section-title">
                        <i class="fas fa-percent text-success"></i>
                        Discount Settings
                    </h5>

                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label">Discount Type <span class="text-danger">*</span></label>
                            <select name="discount_type" id="select_discount_type" class="form-select form-select-sm" onchange="toggleDiscountInputs()" required>
                                <option value="fixed" {{ old('discount_type') === 'fixed' ? 'selected' : '' }}>Fixed Amount (৳)</option>
                                <option value="percentage" {{ old('discount_type') === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                <option value="free_shipping" {{ old('discount_type') === 'free_shipping' ? 'selected' : '' }}>Free Shipping</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-4" id="wrap_discount_amount">
                            <label class="form-label">Discount Amount <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text" id="discount_unit_symbol">৳</span>
                                <input type="number" step="0.01" min="0" name="discount_amount" id="input_discount_amount" class="form-control" placeholder="100" value="{{ old('discount_amount', 0) }}" required>
                            </div>
                        </div>

                        <div class="col-12 col-md-4" id="wrap_max_discount">
                            <label class="form-label">Max Discount Limit</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" min="0" name="max_discount_amount" class="form-control" placeholder="No limit" value="{{ old('max_discount_amount') }}">
                            </div>
                            <small class="text-muted">For percentage discounts.</small>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Minimum Cart Subtotal</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" min="0" name="min_order_amount" class="form-control" placeholder="0" value="{{ old('min_order_amount', 0) }}">
                            </div>
                            <small class="text-muted">Minimum order amount.</small>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Maximum Cart Subtotal</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" min="0" name="max_order_amount" class="form-control" placeholder="No maximum" value="{{ old('max_order_amount') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Applicability -->
                <div class="card mb-3">
                    <h5 class="coupon-section-title">
                        <i class="fas fa-boxes text-info"></i>
                        Product Applicability
                    </h5>

                    <div class="mb-3">
                        <label class="form-label">Applicable To</label>
                        <select name="applicable_to" id="select_applicable_to" class="form-select form-select-sm" onchange="toggleApplicableTargets()">
                            <option value="all" {{ old('applicable_to') === 'all' ? 'selected' : '' }}>All Store Products</option>
                            <option value="specific_products" {{ old('applicable_to') === 'specific_products' ? 'selected' : '' }}>Specific Products</option>
                            <option value="specific_categories" {{ old('applicable_to') === 'specific_categories' ? 'selected' : '' }}>Specific Categories</option>
                        </select>
                    </div>

                    <!-- Product Picker -->
                    <div id="wrap_specific_products" class="mb-3 d-none">
                        <label class="form-label d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <span>Select Products (Name, SKU or ID)</span>
                            <span class="badge bg-light text-muted border font-monospace" id="product_selected_counter">0 selected</span>
                        </label>

                        <div class="search-picker-box">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white text-muted"><i class="fas fa-search"></i></span>
                                <input type="text" id="product_search_input" class="form-control" placeholder="Search products..." autocomplete="off">
                            </div>
                            <div id="product_search_dropdown" class="search-picker-dropdown"></div>
                        </div>

                        <div class="coupon-chips-container mt-2" id="product_chips_container">
                            <span class="text-muted small ps-1" id="product_placeholder_text" style="font-size: 11px;">No products selected. Search to add.</span>
                        </div>

                        <div id="product_hidden_inputs"></div>
                        <small class="text-muted d-block mt-1">Coupon applies when the cart contains a selected product.</small>
                    </div>

                    <!-- Category Picker -->
                    <div id="wrap_specific_categories" class="mb-3 d-none">
                        <label class="form-label d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <span>Select Categories (Name or Slug)</span>
                            <span class="badge bg-light text-muted border font-monospace" id="category_selected_counter">0 selected</span>
                        </label>

                        <div class="search-picker-box">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white text-muted"><i class="fas fa-search"></i></span>
                                <input type="text" id="category_search_input" class="form-control" placeholder="Search categories..." autocomplete="off">
                            </div>
                            <div id="category_search_dropdown" class="search-picker-dropdown"></div>
                        </div>

                        <div class="coupon-chips-container mt-2" id="category_chips_container">
                            <span class="text-muted small ps-1" id="category_placeholder_text" style="font-size: 11px;">No categories selected. Search to add.</span>
                        </div>

                        <div id="category_hidden_inputs"></div>
                        <small class="text-muted d-block mt-1">Discount applies to selected categories.</small>
                    </div>
                </div>
            </div>

            <!-- Right: Limits, Validity & Status -->
            <div class="col-12 col-lg-4">

                <!-- Usage Limits -->
                <div class="card mb-3">
                    <h5 class="coupon-section-title">
                        <i class="fas fa-users-cog text-warning"></i>
                        Usage Limits
                    </h5>

                    <div class="mb-3">
                        <label class="form-label">Total Usage Limit</label>
                        <input type="number" min="1" name="total_usage_limit" class="form-control form-control-sm" placeholder="Unlimited" value="{{ old('total_usage_limit') }}">
                        <small class="text-muted">Store-wide redemption limit.</small>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Per Customer Limit <span class="text-danger">*</span></label>
                        <input type="number" min="1" name="per_user_limit" class="form-control form-control-sm" value="{{ old('per_user_limit', 1) }}" required>
                        <small class="text-muted">Uses allowed per customer.</small>
                    </div>
                </div>

                <!-- Validity -->
                <div class="card mb-3">
                    <h5 class="coupon-section-title">
                        <i class="far fa-calendar-alt text-primary"></i>
                        Validity Period
                    </h5>

                    <div class="mb-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control form-control-sm" value="{{ old('start_date', date('Y-m-d')) }}">
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Expiry Date</label>
                        <input type="date" name="expire_date" class="form-control form-control-sm" value="{{ old('expire_date') }}">
                        <small class="text-muted">Leave empty for no expiry.</small>
                    </div>
                </div>

                <!-- Status & Settings -->
                <div class="card mb-3">
                    <h5 class="coupon-section-title">
                        <i class="fas fa-toggle-on text-success"></i>
                        Status &amp; Settings
                    </h5>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="check_is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold small text-dark" for="check_is_active">Active</label>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_public" id="check_is_public" value="1" {{ old('is_public', 1) ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold small text-dark" for="check_is_public">Publicly Visible</label>
                        <small class="text-muted d-block">Show in cart and checkout.</small>
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="is_stackable" id="check_is_stackable" value="1" {{ old('is_stackable') ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold small text-dark" for="check_is_stackable">Allow Voucher Stacking</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 coupon-submit-btn">
                        <i class="fas fa-save me-1"></i> Save Coupon
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Preloaded Dataset & Selection Stores
const preloadedProducts = @json($preloadedProducts);
const preloadedCategories = @json($preloadedCategories);
const selectedProductsMap = new Map();
const selectedCategoriesMap = new Map();

function generateRandomCouponCode() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    let result = '';
    for (let i = 0; i < 8; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('input_coupon_code').value = result;
}

function selectTargetUserType(type) {
    document.querySelectorAll('.target-user-card').forEach(el => el.classList.remove('active'));
    if (type === 'registered_only') {
        document.getElementById('card_target_registered').classList.add('active');
    } else if (type === 'guest_only') {
        document.getElementById('card_target_guest').classList.add('active');
    } else {
        document.getElementById('card_target_all').classList.add('active');
    }
}

function toggleDiscountInputs() {
    const type = document.getElementById('select_discount_type').value;
    const unitSymbol = document.getElementById('discount_unit_symbol');
    const wrapAmount = document.getElementById('wrap_discount_amount');
    const wrapMax = document.getElementById('wrap_max_discount');
    const inputAmount = document.getElementById('input_discount_amount');

    if (type === 'percentage') {
        unitSymbol.innerText = '%';
        wrapAmount.classList.remove('d-none');
        wrapMax.classList.remove('d-none');
        inputAmount.placeholder = '10';
    } else if (type === 'free_shipping') {
        wrapAmount.classList.add('d-none');
        wrapMax.classList.add('d-none');
        inputAmount.value = '0';
    } else {
        unitSymbol.innerText = '৳';
        wrapAmount.classList.remove('d-none');
        wrapMax.classList.add('d-none');
        inputAmount.placeholder = '100';
    }
}

function toggleApplicableTargets() {
    const val = document.getElementById('select_applicable_to').value;
    const wrapProd = document.getElementById('wrap_specific_products');
    const wrapCat = document.getElementById('wrap_specific_categories');

    if (val === 'specific_products') {
        wrapProd.classList.remove('d-none');
        wrapCat.classList.add('d-none');
    } else if (val === 'specific_categories') {
        wrapProd.classList.add('d-none');
        wrapCat.classList.remove('d-none');
    } else {
        wrapProd.classList.add('d-none');
        wrapCat.classList.add('d-none');
    }
}

// ==========================================
// Product Search & Multi-Select Engine
// ==========================================
function renderProductChips() {
    const container = document.getElementById('product_chips_container');
    const hiddenInputs = document.getElementById('product_hidden_inputs');
    const placeholder = document.getElementById('product_placeholder_text');
    const counter = document.getElementById('product_selected_counter');

    container.innerHTML = '';
    hiddenInputs.innerHTML = '';

    if (selectedProductsMap.size === 0) {
        container.appendChild(placeholder);
        counter.innerText = '0 selected';
        return;
    }

    counter.innerText = selectedProductsMap.size + ' selected';

    selectedProductsMap.forEach((prod, id) => {
        const chip = document.createElement('div');
        chip.className = 'selected-tag-chip';
        chip.innerHTML = `
            <span>#${prod.id} - ${escapeHtml(prod.title)} (SKU: ${escapeHtml(prod.sku)}, ৳${prod.price})</span>
            <button type="button" class="chip-remove-btn" onclick="removeSelectedProduct(${prod.id})" title="Remove">&times;</button>
        `;
        container.appendChild(chip);

        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'product_ids[]';
        hidden.value = prod.id;
        hiddenInputs.appendChild(hidden);
    });
}

function addSelectedProduct(prod) {
    if (!selectedProductsMap.has(Number(prod.id))) {
        selectedProductsMap.set(Number(prod.id), prod);
        renderProductChips();
    }
    document.getElementById('product_search_input').value = '';
    document.getElementById('product_search_dropdown').style.display = 'none';
}

function removeSelectedProduct(id) {
    selectedProductsMap.delete(Number(id));
    renderProductChips();
}

function searchProducts(keyword) {
    const dropdown = document.getElementById('product_search_dropdown');
    keyword = keyword.toLowerCase().trim();

    if (!keyword) {
        dropdown.style.display = 'none';
        return;
    }

    let matches = preloadedProducts.filter(p => {
        return String(p.id).includes(keyword) ||
               String(p.title).toLowerCase().includes(keyword) ||
               String(p.sku).toLowerCase().includes(keyword);
    });

    if (matches.length === 0) {
        dropdown.innerHTML = `<div class="p-2.5 text-center text-muted small">No products match "${escapeHtml(keyword)}"</div>`;
        dropdown.style.display = 'block';
        return;
    }

    let html = '';
    matches.slice(0, 20).forEach(prod => {
        const isAdded = selectedProductsMap.has(Number(prod.id));
        html += `
            <div class="search-picker-item" onclick='addSelectedProduct(${JSON.stringify(prod)})'>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-secondary border font-monospace">#${prod.id}</span>
                    <div>
                        <strong class="d-block text-dark small" style="font-size: 12px;">${escapeHtml(prod.title)}</strong>
                        <small class="text-muted" style="font-size: 11px;">SKU: ${escapeHtml(prod.sku)} &bull; Price: ৳${prod.price}</small>
                    </div>
                </div>
                ${isAdded ? '<span class="badge bg-success-subtle text-success small">Added</span>' : '<button type="button" class="btn btn-outline-primary btn-xs py-0 px-2" style="font-size: 10px;">+ Add</button>'}
            </div>
        `;
    });

    dropdown.innerHTML = html;
    dropdown.style.display = 'block';
}

// ==========================================
// Category Search & Multi-Select Engine
// ==========================================
function renderCategoryChips() {
    const container = document.getElementById('category_chips_container');
    const hiddenInputs = document.getElementById('category_hidden_inputs');
    const placeholder = document.getElementById('category_placeholder_text');
    const counter = document.getElementById('category_selected_counter');

    container.innerHTML = '';
    hiddenInputs.innerHTML = '';

    if (selectedCategoriesMap.size === 0) {
        container.appendChild(placeholder);
        counter.innerText = '0 selected';
        return;
    }

    counter.innerText = selectedCategoriesMap.size + ' selected';

    selectedCategoriesMap.forEach((cat, id) => {
        const chip = document.createElement('div');
        chip.className = 'selected-tag-chip';
        chip.innerHTML = `
            <span>${escapeHtml(cat.name)} ${cat.slug ? '(' + escapeHtml(cat.slug) + ')' : ''}</span>
            <button type="button" class="chip-remove-btn" onclick="removeSelectedCategory(${cat.id})" title="Remove">&times;</button>
        `;
        container.appendChild(chip);

        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'category_ids[]';
        hidden.value = cat.id;
        hiddenInputs.appendChild(hidden);
    });
}

function addSelectedCategory(cat) {
    if (!selectedCategoriesMap.has(Number(cat.id))) {
        selectedCategoriesMap.set(Number(cat.id), cat);
        renderCategoryChips();
    }
    document.getElementById('category_search_input').value = '';
    document.getElementById('category_search_dropdown').style.display = 'none';
}

function removeSelectedCategory(id) {
    selectedCategoriesMap.delete(Number(id));
    renderCategoryChips();
}

function searchCategories(keyword) {
    const dropdown = document.getElementById('category_search_dropdown');
    keyword = keyword.toLowerCase().trim();

    if (!keyword) {
        dropdown.style.display = 'none';
        return;
    }

    let matches = preloadedCategories.filter(c => {
        return String(c.id).includes(keyword) ||
               String(c.name).toLowerCase().includes(keyword) ||
               String(c.slug).toLowerCase().includes(keyword);
    });

    if (matches.length === 0) {
        dropdown.innerHTML = `<div class="p-2.5 text-center text-muted small">No categories match "${escapeHtml(keyword)}"</div>`;
        dropdown.style.display = 'block';
        return;
    }

    let html = '';
    matches.slice(0, 15).forEach(cat => {
        const isAdded = selectedCategoriesMap.has(Number(cat.id));
        html += `
            <div class="search-picker-item" onclick='addSelectedCategory(${JSON.stringify(cat)})'>
                <div>
                    <strong class="d-block text-dark small" style="font-size: 12px;">${escapeHtml(cat.name)}</strong>
                    ${cat.slug ? `<small class="text-muted" style="font-size: 10.5px;">Slug: /${escapeHtml(cat.slug)}</small>` : ''}
                </div>
                ${isAdded ? '<span class="badge bg-success-subtle text-success small">Added</span>' : '<button type="button" class="btn btn-outline-primary btn-xs py-0 px-2" style="font-size: 10px;">+ Add</button>'}
            </div>
        `;
    });

    dropdown.innerHTML = html;
    dropdown.style.display = 'block';
}

function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', function() {
    toggleDiscountInputs();
    toggleApplicableTargets();

    // Setup Product Search Listeners
    const pInput = document.getElementById('product_search_input');
    pInput.addEventListener('input', function() { searchProducts(this.value); });
    pInput.addEventListener('focus', function() { if (this.value) searchProducts(this.value); });

    // Setup Category Search Listeners
    const cInput = document.getElementById('category_search_input');
    cInput.addEventListener('input', function() { searchCategories(this.value); });
    cInput.addEventListener('focus', function() { if (this.value) searchCategories(this.value); });

    // Close dropdowns on outside click
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-picker-box')) {
            document.getElementById('product_search_dropdown').style.display = 'none';
            document.getElementById('category_search_dropdown').style.display = 'none';
        }
    });

    // Populate Old Selected Values if validation failed
    const oldP = @json($oldProductIds);
    if (Array.isArray(oldP) && oldP.length) {
        oldP.forEach(id => {
            const found = preloadedProducts.find(p => Number(p.id) === Number(id));
            if (found) selectedProductsMap.set(Number(found.id), found);
        });
        renderProductChips();
    }

    const oldC = @json($oldCategoryIds);
    if (Array.isArray(oldC) && oldC.length) {
        oldC.forEach(id => {
            const found = preloadedCategories.find(c => Number(c.id) === Number(id));
            if (found) selectedCategoriesMap.set(Number(found.id), found);
        });
        renderCategoryChips();
    }
});
</script>
@endsection
