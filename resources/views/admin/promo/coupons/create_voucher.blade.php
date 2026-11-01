@extends('layouts.admin')

@section('title', 'Create Voucher Campaign')

@php
    $voucherIndexUrl = Route::has('admin.promo.coupons.index') ? route('admin.promo.coupons.index', ['tab' => 'vouchers']) : url('/admin/promo/coupons?tab=vouchers');
    $voucherStoreUrl = Route::has('admin.promo.coupons.vouchers.store') ? route('admin.promo.coupons.vouchers.store') : url('/admin/promo/coupons/vouchers/store');
    $targetType = old('target_user_type', 'registered_only');
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
        border-color: #16a34a;
        background: #f8fafc;
    }
    .target-user-card.active {
        border-color: #16a34a;
        background: #f0fdf4;
    }
</style>

<div class="container-fluid px-3 py-4">

    <!-- Header Section -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="fas fa-gift text-success me-2"></i>Create Voucher Campaign
            </h4>
            <p class="text-muted small mb-0">Set up 1-click claimable vouchers for customer wallets, cart drawers, and checkout pages.</p>
        </div>
        <a href="{{ $voucherIndexUrl }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3">
            <i class="fas fa-arrow-left me-1"></i> Back to Vouchers
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

    <form action="{{ $voucherStoreUrl }}" method="POST">
        @csrf

        <div class="row g-4">
            <!-- Left Side: Main Campaign Information -->
            <div class="col-12 col-lg-8">
                <!-- Basic Campaign Details -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h5 class="fw-bold text-dark mb-3 fs-6">
                        <i class="fas fa-info-circle text-primary me-2"></i>Campaign Details
                    </h5>

                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <label class="form-label small fw-bold text-dark">Campaign Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control form-control-sm" placeholder="Eid Mega Voucher ৳200 OFF" value="{{ old('title') }}" required>
                            <small class="text-muted" style="font-size: 11px;">This title will be displayed to customers in their voucher wallet.</small>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-dark">Code Prefix</label>
                            <input type="text" name="code_prefix" class="form-control form-control-sm text-uppercase font-monospace fw-bold" placeholder="EID" value="{{ old('code_prefix', 'VCH') }}">
                            <small class="text-muted" style="font-size: 11px;">Claimed vouchers will generate like: EID-8A3F9X</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Terms &amp; Conditions / Usage Rules</label>
                            <textarea name="terms_conditions" class="form-control form-control-sm" rows="3" placeholder="Applicable only on prepaid orders. Cannot be combined with clearance items.">{{ old('terms_conditions') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Customer Eligibility & Target Rule -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h5 class="fw-bold text-dark mb-0 fs-6">
                            <i class="fas fa-users text-success me-2"></i>Customer Eligibility Rule
                        </h5>
                        <span class="badge bg-light text-muted border" style="font-size: 9.5px;">TARGET AUDIENCE</span>
                    </div>
                    <p class="text-muted small mb-3">Define who is eligible to claim and redeem this voucher campaign into their wallet.</p>

                    <div class="row g-2">
                        <div class="col-12 col-md-4">
                            <label class="target-user-card w-100 d-flex align-items-center gap-2 {{ $targetType === 'registered_only' ? 'active' : '' }}" id="card_target_registered">
                                <input type="radio" name="target_user_type" value="registered_only" class="form-check-input mt-0" {{ $targetType === 'registered_only' ? 'checked' : '' }} onchange="selectTargetUserType('registered_only')">
                                <div>
                                    <strong class="d-block text-dark small" style="font-size: 12px;">Registered Users</strong>
                                    <small class="text-muted d-block" style="font-size: 10px;">Requires logged-in account (Wallet Claim)</small>
                                </div>
                            </label>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="target-user-card w-100 d-flex align-items-center gap-2 {{ $targetType === 'all' ? 'active' : '' }}" id="card_target_all">
                                <input type="radio" name="target_user_type" value="all" class="form-check-input mt-0" {{ $targetType === 'all' ? 'checked' : '' }} onchange="selectTargetUserType('all')">
                                <div>
                                    <strong class="d-block text-dark small" style="font-size: 12px;">All Customers</strong>
                                    <small class="text-muted d-block" style="font-size: 10px;">Claimable by any store visitor</small>
                                </div>
                            </label>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="target-user-card w-100 d-flex align-items-center gap-2 {{ $targetType === 'guest_only' ? 'active' : '' }}" id="card_target_guest">
                                <input type="radio" name="target_user_type" value="guest_only" class="form-check-input mt-0" {{ $targetType === 'guest_only' ? 'checked' : '' }} onchange="selectTargetUserType('guest_only')">
                                <div>
                                    <strong class="d-block text-dark small" style="font-size: 12px;">Guest Users Only</strong>
                                    <small class="text-muted d-block" style="font-size: 10px;">Applicable on guest checkouts</small>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Discount Structure -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h5 class="fw-bold text-dark mb-3 fs-6">
                        <i class="fas fa-percent text-success me-2"></i>Discount Settings
                    </h5>

                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-dark">Voucher Type <span class="text-danger">*</span></label>
                            <select name="voucher_type" id="select_voucher_type" class="form-select form-select-sm" onchange="toggleVoucherDiscountInputs()" required>
                                <option value="fixed" {{ old('voucher_type') === 'fixed' ? 'selected' : '' }}>Fixed Amount (৳)</option>
                                <option value="percentage" {{ old('voucher_type') === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                <option value="free_shipping" {{ old('voucher_type') === 'free_shipping' ? 'selected' : '' }}>Free Delivery</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-4" id="wrap_voucher_discount_amount">
                            <label class="form-label small fw-bold text-dark">Discount Value <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text" id="voucher_unit_symbol">৳</span>
                                <input type="number" step="0.01" min="0" name="discount_amount" id="input_voucher_discount_amount" class="form-control" placeholder="100" value="{{ old('discount_amount', 0) }}" required>
                            </div>
                        </div>

                        <div class="col-12 col-md-4" id="wrap_voucher_max_discount">
                            <label class="form-label small fw-bold text-dark">Max Discount Cap</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" min="0" name="max_discount" class="form-control" placeholder="No maximum" value="{{ old('max_discount') }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark">Minimum Cart Spend</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" min="0" name="min_spend" class="form-control" placeholder="0" value="{{ old('min_spend', 0) }}">
                            </div>
                            <small class="text-muted" style="font-size: 11px;">Minimum cart subtotal needed to claim/redeem.</small>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark">Customer Validity (Days) <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <input type="number" min="1" name="validity_days" class="form-control" placeholder="7" value="{{ old('validity_days', 7) }}" required>
                                <span class="input-group-text">Days</span>
                            </div>
                            <small class="text-muted" style="font-size: 11px;">Customer has this many days to use it after claiming.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Quotas, Dates & Status -->
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h5 class="fw-bold text-dark mb-3 fs-6">
                        <i class="fas fa-users-cog text-warning me-2"></i>Quota &amp; Claims
                    </h5>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Total Campaign Quota</label>
                        <input type="number" min="1" name="total_vouchers_count" class="form-control form-control-sm" placeholder="Unlimited" value="{{ old('total_vouchers_count') }}">
                        <small class="text-muted" style="font-size: 11px;">Maximum total claims across all store customers.</small>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-bold text-dark">Max Claims Per Customer <span class="text-danger">*</span></label>
                        <input type="number" min="1" name="max_claims_per_user" class="form-control form-control-sm" value="{{ old('max_claims_per_user', 1) }}" required>
                        <small class="text-muted" style="font-size: 11px;">How many times one logged-in user can claim this voucher.</small>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h5 class="fw-bold text-dark mb-3 fs-6">
                        <i class="far fa-calendar-alt text-primary me-2"></i>Campaign Schedule
                    </h5>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Claiming Starts</label>
                        <input type="date" name="claim_start" class="form-control form-control-sm" value="{{ old('claim_start', date('Y-m-d')) }}">
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-bold text-dark">Claiming Ends</label>
                        <input type="date" name="claim_end" class="form-control form-control-sm" value="{{ old('claim_end') }}">
                        <small class="text-muted" style="font-size: 11px;">Leave blank if the campaign has no deadline.</small>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h5 class="fw-bold text-dark mb-3 fs-6">
                        <i class="fas fa-toggle-on text-success me-2"></i>Activation
                    </h5>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="is_active" id="check_voucher_is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold small text-dark" for="check_voucher_is_active">Launch Campaign (Active)</label>
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-2.5 rounded-3 fw-bold shadow-sm">
                        <i class="fas fa-save me-1"></i> Save Voucher Campaign
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function selectTargetUserType(type) {
    document.querySelectorAll('.target-user-card').forEach(el => el.classList.remove('active'));
    if (type === 'all') {
        document.getElementById('card_target_all').classList.add('active');
    } else if (type === 'guest_only') {
        document.getElementById('card_target_guest').classList.add('active');
    } else {
        document.getElementById('card_target_registered').classList.add('active');
    }
}

function toggleVoucherDiscountInputs() {
    const type = document.getElementById('select_voucher_type').value;
    const unitSymbol = document.getElementById('voucher_unit_symbol');
    const wrapAmount = document.getElementById('wrap_voucher_discount_amount');
    const wrapMax = document.getElementById('wrap_voucher_max_discount');
    const inputAmount = document.getElementById('input_voucher_discount_amount');

    if (type === 'percentage') {
        unitSymbol.innerText = '%';
        wrapAmount.classList.remove('d-none');
        wrapMax.classList.remove('d-none');
        inputAmount.placeholder = '15';
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

document.addEventListener('DOMContentLoaded', function() {
    toggleVoucherDiscountInputs();
});
</script>
@endsection