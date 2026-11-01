@extends('layouts.admin')

@section('title', 'Edit Coupon - ' . ($coupon->code ?? ''))

@php
    $couponIndexUrl  = Route::has('admin.promo.coupons.index') ? route('admin.promo.coupons.index', ['tab' => 'coupons']) : url('/admin/promo/coupons?tab=coupons');
    $couponUpdateUrl = Route::has('admin.promo.coupons.update') ? route('admin.promo.coupons.update', $coupon->id) : url('/admin/promo/coupons/update/' . $coupon->id);

    // Preloaded Products Mapping
    $preloadedProducts = ($products ?? collect())->map(function($p) {
        return [
            'id'    => $p->id,
            'title' => $p->title ?? 'Product #' . $p->id,
            'sku'   => $p->sku ?? 'N/A',
            'price' => (float)($p->regular_price ?? 0),
            'image' => !empty($p->image) ? asset(ltrim($p->image, '/')) : null,
        ];
    });

    // Preloaded Categories Mapping
    $preloadedCategories = ($categories ?? collect())->map(function($c) {
        return [
            'id'   => $c->id,
            'name' => $c->name ?? 'Category #' . $c->id,
            'slug' => $c->slug ?? '',
        ];
    });

    // Initial Selected Products & Categories (Old Input priority over DB stored IDs)
    $initialProductIds = old('product_ids', $selectedProducts ?? []);
    $initialCategoryIds = old('category_ids', $selectedCategories ?? []);

    $targetType = old('target_user_type', data_get($coupon, 'target_user_type', 'all'));
@endphp

@section('admin_content')
<style>
    .target-user-card {
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 14px;
        cursor: pointer;
        transition: all 0.15s ease;
        background: #ffffff;
    }
    .target-user-card:hover {
        border-color: #2563eb;
        background: #f8fafc;
    }
    .target-user-card.active {
        border-color: #2563eb;
        background: #eff6ff;
    }
    .search-picker-box {
        position: relative;
    }
    .search-picker-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 1050;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        max-height: 240px;
        overflow-y: auto;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.15);
        display: none;
        margin-top: 4px;
    }
    .search-picker-item {
        padding: 8px 12px;
        cursor: pointer;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: background 0.12s ease;
    }
    .search-picker-item:hover {
        background: #f8fafc;
    }
    .selected-tag-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1e40af;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11.5px;
        font-weight: 600;
        margin: 3px;
        animation: fadeIn 0.15s ease-in;
    }
    .selected-tag-chip .chip-remove-btn {
        background: transparent;
        border: none;
        color: #ef4444;
        cursor: pointer;
        padding: 0;
        font-size: 13px;
        line-height: 1;
        display: inline-flex;
        align-items: center;
    }
    .selected-tag-chip .chip-remove-btn:hover {
        color: #b91c1c;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.96); }
        to { opacity: 1; transform: scale(1); }
    }
</style>

<div class="container-fluid px-3 py-4">

    <!-- Header Section -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="fas fa-edit text-primary me-2"></i>Edit Coupon: <span class="font-monospace text-primary">{{ $coupon->code ?? '' }}</span>
            </h4>
            <p class="text-muted small mb-0">Update promotional rules, customer eligibility, limits, validity, and targeted products.</p>
        </div>
        <a href="{{ $couponIndexUrl }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3">
            <i class="fas fa-arrow-left me-1"></i> Back to Coupons
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4">
            <div class="d-flex align-items-center mb-1">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong class="small">Please fix the following errors:</strong>
            </div>
            <ul class="mb-0 ps-3 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ $couponUpdateUrl }}" method="POST" id="editCouponForm">
        @csrf

        <div class="row g-4">
            <!-- Left Side: Main Coupon Configuration -->
            <div class="col-12 col-lg-8">
                <!-- Basic Info Card -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h5 class="fw-bold text-dark mb-3 fs-6">
                        <i class="fas fa-info-circle text-primary me-2"></i>Basic Coupon Information
                    </h5>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark">Coupon Code <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="code" id="input_coupon_code" class="form-control text-uppercase font-monospace fw-bold" placeholder="SUMMER50" value="{{ old('code', data_get($coupon, 'code', '')) }}" required>
                                <button type="button" class="btn btn-outline-primary" onclick="generateRandomCouponCode()">
                                    <i class="fas fa-magic me-1"></i> Generate
                                </button>
                            </div>
                            <small class="text-muted" style="font-size: 11px;">Customers use this code to claim discounts.</small>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark">Coupon Name / Internal Note</label>
                            <input type="text" name="name" class="form-control form-control-sm" placeholder="Summer Mega Discount" value="{{ old('name', data_get($coupon, 'name', '')) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Badge / Promotional Ribbon Text</label>
                            <input type="text" name="badge_text" class="form-control form-control-sm" placeholder="20% OFF or LIMITED DEAL" value="{{ old('badge_text', data_get($coupon, 'badge_text', '')) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Customer Facing Description</label>
                            <textarea name="customer_description" class="form-control form-control-sm" rows="2" placeholder="Get ৳150 off on minimum purchase of ৳1000">{{ old('customer_description', data_get($coupon, 'customer_description', '')) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Customer Target & Eligibility Rule -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h5 class="fw-bold text-dark mb-0 fs-6">
                            <i class="fas fa-users text-primary me-2"></i>Customer Eligibility Rule
                        </h5>
                        <span class="badge bg-light text-muted border" style="font-size: 9.5px;">TARGET USER</span>
                    </div>
                    <p class="text-muted small mb-3">Define who is allowed to apply this coupon code at checkout.</p>

                    <div class="row g-2">
                        <div class="col-12 col-md-4">
                            <label class="target-user-card w-100 d-flex align-items-center gap-2 {{ $targetType === 'all' ? 'active' : '' }}" id="card_target_all">
                                <input type="radio" name="target_user_type" value="all" class="form-check-input mt-0" {{ $targetType === 'all' ? 'checked' : '' }} onchange="selectTargetUserType('all')">
                                <div>
                                    <strong class="d-block text-dark small" style="font-size: 12px;">All Customers</strong>
                                    <small class="text-muted d-block" style="font-size: 10px;">Registered &amp; Guest users</small>
                                </div>
                            </label>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="target-user-card w-100 d-flex align-items-center gap-2 {{ $targetType === 'registered_only' ? 'active' : '' }}" id="card_target_registered">
                                <input type="radio" name="target_user_type" value="registered_only" class="form-check-input mt-0" {{ $targetType === 'registered_only' ? 'checked' : '' }} onchange="selectTargetUserType('registered_only')">
                                <div>
                                    <strong class="d-block text-dark small" style="font-size: 12px;">Registered Users Only</strong>
                                    <small class="text-muted d-block" style="font-size: 10px;">Must be signed in</small>
                                </div>
                            </label>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="target-user-card w-100 d-flex align-items-center gap-2 {{ $targetType === 'guest_only' ? 'active' : '' }}" id="card_target_guest">
                                <input type="radio" name="target_user_type" value="guest_only" class="form-check-input mt-0" {{ $targetType === 'guest_only' ? 'checked' : '' }} onchange="selectTargetUserType('guest_only')">
                                <div>
                                    <strong class="d-block text-dark small" style="font-size: 12px;">Guest Users Only</strong>
                                    <small class="text-muted d-block" style="font-size: 10px;">First-time guest orders</small>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Discount Mechanics -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h5 class="fw-bold text-dark mb-3 fs-6">
                        <i class="fas fa-percent text-success me-2"></i>Discount Calculation
                    </h5>

                    @php
                        $currDiscountType = old('discount_type', data_get($coupon, 'discount_type', 'fixed'));
                        $currDiscountAmt = old('discount_amount', data_get($coupon, 'discount_amount', data_get($coupon, 'value', 0)));
                        $currMaxDiscount = old('max_discount_amount', data_get($coupon, 'max_discount_amount', data_get($coupon, 'max_discount', '')));
                        $currMinSpend = old('min_order_amount', data_get($coupon, 'min_order_amount', data_get($coupon, 'min_spend', 0)));
                        $currMaxSpend = old('max_order_amount', data_get($coupon, 'max_order_amount', ''));
                    @endphp

                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-dark">Discount Type <span class="text-danger">*</span></label>
                            <select name="discount_type" id="select_discount_type" class="form-select form-select-sm" onchange="toggleDiscountInputs()" required>
                                <option value="fixed" {{ $currDiscountType === 'fixed' ? 'selected' : '' }}>Fixed Amount (৳)</option>
                                <option value="percentage" {{ $currDiscountType === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                <option value="free_shipping" {{ $currDiscountType === 'free_shipping' ? 'selected' : '' }}>Free Shipping</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-4" id="wrap_discount_amount">
                            <label class="form-label small fw-bold text-dark">Discount Amount <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text" id="discount_unit_symbol">৳</span>
                                <input type="number" step="0.01" min="0" name="discount_amount" id="input_discount_amount" class="form-control" placeholder="100" value="{{ $currDiscountAmt }}" required>
                            </div>
                        </div>

                        <div class="col-12 col-md-4" id="wrap_max_discount">
                            <label class="form-label small fw-bold text-dark">Max Discount Limit (Cap)</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" min="0" name="max_discount_amount" class="form-control" placeholder="No limit" value="{{ $currMaxDiscount }}">
                            </div>
                            <small class="text-muted" style="font-size: 11px;">Limits percentage-based discounts.</small>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark">Minimum Cart Subtotal</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" min="0" name="min_order_amount" class="form-control" placeholder="0" value="{{ $currMinSpend }}">
                            </div>
                            <small class="text-muted" style="font-size: 11px;">Minimum order amount required to apply.</small>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark">Maximum Cart Subtotal (Optional)</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" min="0" name="max_order_amount" class="form-control" placeholder="No maximum" value="{{ $currMaxSpend }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product & Category Applicability -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h5 class="fw-bold text-dark mb-3 fs-6">
                        <i class="fas fa-boxes text-info me-2"></i>Product Applicability Rules
                    </h5>

                    @php
                        $currAppTo = old('applicable_to', data_get($coupon, 'applicable_to', 'all'));
                    @endphp

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Applicable To</label>
                        <select name="applicable_to" id="select_applicable_to" class="form-select form-select-sm" onchange="toggleApplicableTargets()">
                            <option value="all" {{ $currAppTo === 'all' ? 'selected' : '' }}>All Store Products</option>
                            <option value="specific_products" {{ $currAppTo === 'specific_products' ? 'selected' : '' }}>Specific Products</option>
                            <option value="specific_categories" {{ $currAppTo === 'specific_categories' ? 'selected' : '' }}>Specific Categories</option>
                        </select>
                    </div>

                    <!-- Searchable Product Multi-Picker Component -->
                    <div id="wrap_specific_products" class="mb-3 d-none">
                        <label class="form-label small fw-bold text-dark d-flex justify-content-between align-items-center">
                            <span>Select Eligible Products (Search by Name, SKU or ID):</span>
                            <span class="badge bg-light text-muted border font-monospace" id="product_selected_counter">0 selected</span>
                        </label>
                        
                        <div class="search-picker-box">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white text-muted"><i class="fas fa-search"></i></span>
                                <input type="text" id="product_search_input" class="form-control" placeholder="Type product title, SKU, or ID to add..." autocomplete="off">
                            </div>
                            <div id="product_search_dropdown" class="search-picker-dropdown"></div>
                        </div>

                        <!-- Selected Products Badges/Chips Container -->
                        <div class="p-2.5 border rounded-3 bg-light mt-2 min-h-50" id="product_chips_container" style="min-height: 54px;">
                            <span class="text-muted small ps-1" id="product_placeholder_text" style="font-size: 11px;">No specific products selected yet. Search above to add.</span>
                        </div>
                        <div id="product_hidden_inputs"></div>
                        <small class="text-muted d-block mt-1" style="font-size: 11px;">Customers can only apply this coupon if their cart contains any of these selected products.</small>
                    </div>

                    <!-- Searchable Category Multi-Picker Component -->
                    <div id="wrap_specific_categories" class="mb-3 d-none">
                        <label class="form-label small fw-bold text-dark d-flex justify-content-between align-items-center">
                            <span>Select Eligible Categories (Search by Name or Slug):</span>
                            <span class="badge bg-light text-muted border font-monospace" id="category_selected_counter">0 selected</span>
                        </label>

                        <div class="search-picker-box">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white text-muted"><i class="fas fa-search"></i></span>
                                <input type="text" id="category_search_input" class="form-control" placeholder="Type category name or slug to add..." autocomplete="off">
                            </div>
                            <div id="category_search_dropdown" class="search-picker-dropdown"></div>
                        </div>

                        <!-- Selected Categories Badges/Chips Container -->
                        <div class="p-2.5 border rounded-3 bg-light mt-2 min-h-50" id="category_chips_container" style="min-height: 54px;">
                            <span class="text-muted small ps-1" id="category_placeholder_text" style="font-size: 11px;">No specific categories selected yet. Search above to add.</span>
                        </div>
                        <div id="category_hidden_inputs"></div>
                        <small class="text-muted d-block mt-1" style="font-size: 11px;">Discount will only apply to items belonging to these selected categories.</small>
                    </div>
                </div>
            </div>

            <!-- Right Side: Limits, Validity & Status -->
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h5 class="fw-bold text-dark mb-3 fs-6">
                        <i class="fas fa-users-cog text-warning me-2"></i>Usage Limits
                    </h5>

                    @php
                        $currUsageLimit = old('total_usage_limit', data_get($coupon, 'total_usage_limit', data_get($coupon, 'usage_limit', '')));
                        $currUsedCount = data_get($coupon, 'used_count', data_get($coupon, 'current_usage_count', 0));
                        $currPerUser = old('per_user_limit', data_get($coupon, 'per_user_limit', data_get($coupon, 'usage_limit_per_user', 1)));
                    @endphp

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Total Usage Limit</label>
                        <input type="number" min="1" name="total_usage_limit" class="form-control form-control-sm" placeholder="Unlimited" value="{{ $currUsageLimit }}">
                        <small class="text-muted" style="font-size: 11px;">Used so far: <strong class="text-dark">{{ $currUsedCount }}</strong> times</small>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-bold text-dark">Per Customer Limit <span class="text-danger">*</span></label>
                        <input type="number" min="1" name="per_user_limit" class="form-control form-control-sm" value="{{ $currPerUser }}" required>
                        <small class="text-muted" style="font-size: 11px;">Maximum usages permitted per user account.</small>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h5 class="fw-bold text-dark mb-3 fs-6">
                        <i class="far fa-calendar-alt text-primary me-2"></i>Validity Period
                    </h5>

                    @php
                        $startDateVal = !empty($coupon->start_date) ? \Carbon\Carbon::parse($coupon->start_date)->format('Y-m-d') : '';
                        $rawExpire = data_get($coupon, 'expire_date', data_get($coupon, 'expires_at'));
                        $expireDateVal = !empty($rawExpire) ? \Carbon\Carbon::parse($rawExpire)->format('Y-m-d') : '';
                    @endphp

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Start Date</label>
                        <input type="date" name="start_date" class="form-control form-control-sm" value="{{ old('start_date', $startDateVal) }}">
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-bold text-dark">Expiry Date</label>
                        <input type="date" name="expire_date" class="form-control form-control-sm" value="{{ old('expire_date', $expireDateVal) }}">
                        <small class="text-muted" style="font-size: 11px;">Leave empty for permanent validity.</small>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h5 class="fw-bold text-dark mb-3 fs-6">
                        <i class="fas fa-toggle-on text-success me-2"></i>Status &amp; Settings
                    </h5>

                    @php
                        $currIsActive = old('is_active', data_get($coupon, 'is_active', data_get($coupon, 'status', 1)));
                        $currIsPublic = old('is_public', data_get($coupon, 'is_public', 1));
                        $currIsStackable = old('is_stackable', data_get($coupon, 'is_stackable', 0));
                    @endphp

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="check_is_active" value="1" {{ $currIsActive ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold small text-dark" for="check_is_active">Active / Enabled</label>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_public" id="check_is_public" value="1" {{ $currIsPublic ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold small text-dark" for="check_is_public">Publicly Visible</label>
                        <small class="text-muted d-block" style="font-size: 10.5px;">Shown on checkout &amp; drawer offers.</small>
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="is_stackable" id="check_is_stackable" value="1" {{ $currIsStackable ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold small text-dark" for="check_is_stackable">Allow with Vouchers (Stackable)</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2.5 rounded-3 fw-bold shadow-sm">
                        <i class="fas fa-sync-alt me-1"></i> Update Coupon
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

    // Populate Initial / Stored Product Chips
    const initP = @json($initialProductIds);
    if (Array.isArray(initP) && initP.length) {
        initP.forEach(id => {
            const found = preloadedProducts.find(p => Number(p.id) === Number(id));
            if (found) {
                selectedProductsMap.set(Number(found.id), found);
            } else {
                selectedProductsMap.set(Number(id), {
                    id: Number(id),
                    title: 'Product #' + id,
                    sku: 'N/A',
                    price: 0
                });
            }
        });
        renderProductChips();
    }

    // Populate Initial / Stored Category Chips
    const initC = @json($initialCategoryIds);
    if (Array.isArray(initC) && initC.length) {
        initC.forEach(id => {
            const found = preloadedCategories.find(c => Number(c.id) === Number(id));
            if (found) {
                selectedCategoriesMap.set(Number(found.id), found);
            } else {
                selectedCategoriesMap.set(Number(id), {
                    id: Number(id),
                    name: 'Category #' + id,
                    slug: ''
                });
            }
        });
        renderCategoryChips();
    }
});
</script>
@endsection