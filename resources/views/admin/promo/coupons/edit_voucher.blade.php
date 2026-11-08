@extends('layouts.admin')

@section('title', 'Edit Voucher - ' . $voucher->title)

@php
    $voucherIndexUrl  = Route::has('admin.promo.coupons.index') ? route('admin.promo.coupons.index', ['tab' => 'vouchers']) : url('/admin/promo/coupons?tab=vouchers');
    $voucherUpdateUrl = Route::has('admin.promo.coupons.vouchers.update') ? route('admin.promo.coupons.vouchers.update', $voucher->id) : url('/admin/promo/coupons/vouchers/update/' . $voucher->id);
    $targetType = old('target_user_type', $voucher->target_user_type ?? 'registered_only');
@endphp

@section('admin_content')
<style>
    /* Edit Voucher - Kaiadmin Classic */
    #editVoucherRoot {
        --voucher-radius: 5px;
        --voucher-border: var(--bs-border-color, #e5e7eb);
        --voucher-muted: #6b7280;
        color: var(--bs-body-color);
    }

    #editVoucherRoot .voucher-card {
        background: #fff;
        border: 1px solid var(--voucher-border);
        border-radius: var(--voucher-radius);
        box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
        padding: 20px;
        margin-bottom: 16px;
    }

    #editVoucherRoot .voucher-card-title {
        display: flex;
        align-items: center;
        gap: 9px;
        font-size: 15px;
        font-weight: 600;
        color: var(--bs-body-color);
        margin-bottom: 16px;
    }

    #editVoucherRoot .voucher-card-title i {
        font-size: 15px;
    }

    #editVoucherRoot .form-label {
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 6px;
        color: var(--bs-body-color);
    }

    #editVoucherRoot .form-control,
    #editVoucherRoot .form-select,
    #editVoucherRoot .input-group-text {
        min-height: 36px;
        border-radius: var(--voucher-radius);
        font-size: 13px;
    }

    #editVoucherRoot textarea.form-control {
        min-height: 82px;
        resize: vertical;
    }

    #editVoucherRoot .input-group > .form-control,
    #editVoucherRoot .input-group > .input-group-text {
        border-radius: 0;
    }

    #editVoucherRoot .input-group > :first-child {
        border-radius: var(--voucher-radius) 0 0 var(--voucher-radius);
    }

    #editVoucherRoot .input-group > :last-child {
        border-radius: 0 var(--voucher-radius) var(--voucher-radius) 0;
    }

    #editVoucherRoot .form-text,
    #editVoucherRoot .field-hint {
        display: block;
        margin-top: 5px;
        color: var(--voucher-muted);
        font-size: 11px;
        line-height: 1.45;
    }

    #editVoucherRoot .target-user-card {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        min-height: 62px;
        padding: 11px 12px;
        border: 1px solid var(--voucher-border);
        border-radius: var(--voucher-radius);
        background: #fff;
        cursor: pointer;
        transition: border-color .15s ease, background-color .15s ease;
    }

    #editVoucherRoot .target-user-card:hover {
        border-color: var(--bs-primary);
        background: var(--bs-tertiary-bg, #f8f9fa);
    }

    #editVoucherRoot .target-user-card.active {
        border-color: var(--bs-primary);
        background: rgba(var(--bs-primary-rgb, 13, 110, 253), .06);
    }

    #editVoucherRoot .target-user-card .form-check-input {
        flex-shrink: 0;
        margin: 0;
    }

    #editVoucherRoot .target-user-card strong {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: var(--bs-body-color);
        line-height: 1.4;
    }

    #editVoucherRoot .target-user-card small {
        display: block;
        margin-top: 3px;
        color: var(--voucher-muted);
        font-size: 10px;
        line-height: 1.35;
    }

    #editVoucherRoot .form-check-input {
        cursor: pointer;
    }

    #editVoucherRoot .form-switch .form-check-input {
        width: 2.2em;
    }

    #editVoucherRoot .btn {
        border-radius: var(--voucher-radius);
        font-size: 13px;
        font-weight: 600;
    }

    #editVoucherRoot .alert {
        border-radius: var(--voucher-radius);
    }

    #editVoucherRoot .voucher-header h4 {
        font-size: 19px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    #editVoucherRoot .voucher-header p {
        font-size: 12px;
        margin-bottom: 0;
    }

    #editVoucherRoot .voucher-section-badge {
        font-size: 10px;
        font-weight: 600;
        letter-spacing: .3px;
    }

    @media (max-width: 767.98px) {
        #editVoucherRoot {
            padding-left: 12px !important;
            padding-right: 12px !important;
            padding-top: 18px !important;
            padding-bottom: 18px !important;
        }

        #editVoucherRoot .voucher-card {
            padding: 15px;
            margin-bottom: 13px;
        }

        #editVoucherRoot .voucher-header {
            align-items: flex-start !important;
        }

        #editVoucherRoot .voucher-header h4 {
            font-size: 17px;
        }

        #editVoucherRoot .voucher-header .btn {
            width: 100%;
        }

        #editVoucherRoot .target-user-card {
            min-height: 56px;
        }
    }

    @media (min-width: 768px) and (max-width: 991.98px) {
        #editVoucherRoot .voucher-card {
            padding: 17px;
        }
    }
</style>

<div class="container-fluid px-3 py-4" id="editVoucherRoot">

    <!-- Page Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 voucher-header">
        <div>
            <h4 class="text-dark">
                <i class="fas fa-edit text-primary me-2"></i>Edit Voucher:
                <span class="text-primary">{{ $voucher->title }}</span>
            </h4>
            <p class="text-muted">Update voucher settings and limits.</p>
        </div>

        <a href="{{ $voucherIndexUrl }}" class="btn btn-outline-secondary btn-sm px-3">
            <i class="fas fa-arrow-left me-1"></i> Back to Vouchers
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger mb-4">
            <div class="d-flex align-items-center mb-1">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong class="small">Fix these errors:</strong>
            </div>
            <ul class="mb-0 ps-3 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ $voucherUpdateUrl }}" method="POST">
        @csrf

        <div class="row g-3">

            <!-- Main Campaign Details -->
            <div class="col-12 col-lg-8">

                <!-- Campaign Details -->
                <div class="voucher-card">
                    <h5 class="voucher-card-title">
                        <i class="fas fa-info-circle text-primary"></i>
                        Campaign Details
                    </h5>

                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <label class="form-label">Campaign Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control form-control-sm" placeholder="Eid Mega Voucher ৳200 OFF" value="{{ old('title', $voucher->title) }}" required>
                            <small class="field-hint">Shown in customer vouchers and cart.</small>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">Code Prefix</label>
                            <input type="text" name="code_prefix" class="form-control form-control-sm text-uppercase font-monospace fw-bold" placeholder="EID" value="{{ old('code_prefix', $voucher->code_prefix ?? 'VCH') }}">
                            <small class="field-hint">Format: {{ $voucher->code_prefix ?? 'VCH' }}-XXXXXX</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Terms &amp; Conditions</label>
                            <textarea name="terms_conditions" class="form-control form-control-sm" rows="3" placeholder="Applicable only on prepaid orders. Cannot be combined with other clearance items.">{{ old('terms_conditions', $voucher->terms_conditions) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Customer Eligibility -->
                <div class="voucher-card">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                        <h5 class="voucher-card-title mb-0">
                            <i class="fas fa-users text-primary"></i>
                            Customer Eligibility
                        </h5>
                        <span class="badge bg-light text-muted border voucher-section-badge">AUDIENCE</span>
                    </div>

                    <div class="row g-2">
                        <div class="col-12 col-md-4">
                            <label class="target-user-card {{ $targetType === 'registered_only' ? 'active' : '' }}" id="card_target_registered">
                                <input type="radio" name="target_user_type" value="registered_only" class="form-check-input" {{ $targetType === 'registered_only' ? 'checked' : '' }} onchange="selectTargetUserType('registered_only')">
                                <div>
                                    <strong>Registered Users</strong>
                                    <small>Login required</small>
                                </div>
                            </label>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="target-user-card {{ $targetType === 'all' ? 'active' : '' }}" id="card_target_all">
                                <input type="radio" name="target_user_type" value="all" class="form-check-input" {{ $targetType === 'all' ? 'checked' : '' }} onchange="selectTargetUserType('all')">
                                <div>
                                    <strong>All Customers</strong>
                                    <small>Any store visitor</small>
                                </div>
                            </label>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="target-user-card {{ $targetType === 'guest_only' ? 'active' : '' }}" id="card_target_guest">
                                <input type="radio" name="target_user_type" value="guest_only" class="form-check-input" {{ $targetType === 'guest_only' ? 'checked' : '' }} onchange="selectTargetUserType('guest_only')">
                                <div>
                                    <strong>Guest Users</strong>
                                    <small>Guest checkout only</small>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Discount Settings -->
                <div class="voucher-card">
                    <h5 class="voucher-card-title">
                        <i class="fas fa-percent text-primary"></i>
                        Discount Settings
                    </h5>

                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label">Voucher Type <span class="text-danger">*</span></label>
                            <select name="voucher_type" id="select_voucher_type" class="form-select form-select-sm" onchange="toggleVoucherDiscountInputs()" required>
                                <option value="fixed" {{ old('voucher_type', $voucher->voucher_type) === 'fixed' ? 'selected' : '' }}>Fixed Amount (৳)</option>
                                <option value="percentage" {{ old('voucher_type', $voucher->voucher_type) === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                <option value="free_shipping" {{ old('voucher_type', $voucher->voucher_type) === 'free_shipping' ? 'selected' : '' }}>Free Delivery</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-4" id="wrap_voucher_discount_amount">
                            <label class="form-label">Discount Value <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text" id="voucher_unit_symbol">৳</span>
                                <input type="number" step="0.01" min="0" name="discount_amount" id="input_voucher_discount_amount" class="form-control" placeholder="100" value="{{ old('discount_amount', $voucher->discount_amount) }}" required>
                            </div>
                        </div>

                        <div class="col-12 col-md-4" id="wrap_voucher_max_discount">
                            <label class="form-label">Max Discount</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" min="0" name="max_discount" class="form-control" placeholder="No limit" value="{{ old('max_discount', $voucher->max_discount) }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Min. Cart Spend</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" min="0" name="min_spend" class="form-control" placeholder="0" value="{{ old('min_spend', $voucher->min_spend ?? 0) }}">
                            </div>
                            <small class="field-hint">Minimum order subtotal.</small>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Validity (Days) <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <input type="number" min="1" name="validity_days" class="form-control" placeholder="7" value="{{ old('validity_days', $voucher->validity_days ?? 7) }}" required>
                                <span class="input-group-text">Days</span>
                            </div>
                            <small class="field-hint">Starts after claiming.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quotas, Schedule & Status -->
            <div class="col-12 col-lg-4">

                <!-- Quota & Claims -->
                <div class="voucher-card">
                    <h5 class="voucher-card-title">
                        <i class="fas fa-users-cog text-primary"></i>
                        Quota &amp; Claims
                    </h5>

                    <div class="mb-3">
                        <label class="form-label">Total Voucher Quota</label>
                        <input type="number" min="1" name="total_vouchers_count" class="form-control form-control-sm" placeholder="Unlimited" value="{{ old('total_vouchers_count', $voucher->total_vouchers_count) }}">
                        <small class="field-hint">
                            Claimed: <strong class="text-dark">{{ $voucher->claimed_count ?? 0 }}</strong>
                        </small>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Claims Per Customer <span class="text-danger">*</span></label>
                        <input type="number" min="1" name="max_claims_per_user" class="form-control form-control-sm" value="{{ old('max_claims_per_user', $voucher->max_claims_per_user ?? 1) }}" required>
                        <small class="field-hint">Maximum claims per user.</small>
                    </div>
                </div>

                <!-- Campaign Schedule -->
                <div class="voucher-card">
                    <h5 class="voucher-card-title">
                        <i class="far fa-calendar-alt text-primary"></i>
                        Campaign Schedule
                    </h5>

                    <div class="mb-3">
                        <label class="form-label">Claim Starts</label>
                        @php
                            $claimStartVal = $voucher->claim_start ? \Carbon\Carbon::parse($voucher->claim_start)->format('Y-m-d') : '';
                        @endphp
                        <input type="date" name="claim_start" class="form-control form-control-sm" value="{{ old('claim_start', $claimStartVal) }}">
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Claim Ends</label>
                        @php
                            $claimEndVal = $voucher->claim_end ? \Carbon\Carbon::parse($voucher->claim_end)->format('Y-m-d') : '';
                        @endphp
                        <input type="date" name="claim_end" class="form-control form-control-sm" value="{{ old('claim_end', $claimEndVal) }}">
                        <small class="field-hint">Leave blank for no end date.</small>
                    </div>
                </div>

                <!-- Activation -->
                <div class="voucher-card">
                    <h5 class="voucher-card-title">
                        <i class="fas fa-toggle-on text-primary"></i>
                        Status
                    </h5>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="is_active" id="check_voucher_is_active" value="1" {{ old('is_active', $voucher->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold small text-dark" for="check_voucher_is_active">Active</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="fas fa-save me-1"></i> Update Voucher
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