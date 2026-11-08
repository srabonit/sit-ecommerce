@extends('layouts.admin')

@section('title', 'Create Voucher Campaign')

@php
    $voucherIndexUrl = Route::has('admin.promo.coupons.index') ? route('admin.promo.coupons.index', ['tab' => 'vouchers']) : url('/admin/promo/coupons?tab=vouchers');
    $voucherStoreUrl = Route::has('admin.promo.coupons.vouchers.store') ? route('admin.promo.coupons.vouchers.store') : url('/admin/promo/coupons/vouchers/store');
    $targetType = old('target_user_type', 'registered_only');
@endphp

@section('admin_content')
<style>
    /* Voucher Campaign - Scoped UI */
    #voucherCampaignRoot {
        --vc-border: var(--bs-border-color, #e5e7eb);
        --vc-muted: #6b7280;
        --vc-radius: 6px;
    }

    #voucherCampaignRoot .vc-card {
        background: #fff;
        border: 1px solid var(--vc-border);
        border-radius: var(--vc-radius);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        padding: 20px;
        margin-bottom: 16px;
    }

    #voucherCampaignRoot .vc-card-title {
        display: flex;
        align-items: center;
        gap: 9px;
        font-size: 14px;
        font-weight: 700;
        color: #252f40;
        margin-bottom: 16px;
    }

    #voucherCampaignRoot .vc-card-title i {
        font-size: 15px;
    }

    #voucherCampaignRoot .vc-label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #344054;
        margin-bottom: 6px;
    }

    #voucherCampaignRoot .vc-help {
        display: block;
        color: var(--vc-muted);
        font-size: 11px;
        line-height: 1.45;
        margin-top: 5px;
    }

    #voucherCampaignRoot .form-control,
    #voucherCampaignRoot .form-select,
    #voucherCampaignRoot .input-group-text {
        min-height: 36px;
        border-radius: 5px;
        font-size: 12px;
    }

    #voucherCampaignRoot textarea.form-control {
        min-height: 78px;
        resize: vertical;
    }

    #voucherCampaignRoot .input-group > .form-control,
    #voucherCampaignRoot .input-group > .input-group-text {
        border-radius: 0;
    }

    #voucherCampaignRoot .input-group > :first-child {
        border-radius: 5px 0 0 5px;
    }

    #voucherCampaignRoot .input-group > :last-child {
        border-radius: 0 5px 5px 0;
    }

    #voucherCampaignRoot .vc-target-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 8px;
    }

    #voucherCampaignRoot .target-user-card {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        min-height: 64px;
        padding: 11px 12px;
        border: 1px solid var(--vc-border);
        border-radius: 6px;
        background: #fff;
        cursor: pointer;
        transition: border-color .15s ease, background .15s ease;
    }

    #voucherCampaignRoot .target-user-card:hover {
        border-color: var(--bs-primary, #1572e8);
        background: #f8fafc;
    }

    #voucherCampaignRoot .target-user-card.active {
        border-color: var(--bs-primary, #1572e8);
        background: rgba(21, 114, 232, .05);
    }

    #voucherCampaignRoot .target-user-card .form-check-input {
        flex-shrink: 0;
        margin: 0;
    }

    #voucherCampaignRoot .target-user-card strong {
        display: block;
        color: #252f40;
        font-size: 12px;
        font-weight: 600;
    }

    #voucherCampaignRoot .target-user-card small {
        display: block;
        color: var(--vc-muted);
        font-size: 10px;
        line-height: 1.4;
        margin-top: 3px;
    }

    #voucherCampaignRoot .vc-section-note {
        color: var(--vc-muted);
        font-size: 11px;
        margin-bottom: 14px;
    }

    #voucherCampaignRoot .vc-actions .btn {
        min-height: 38px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 600;
    }

    #voucherCampaignRoot .vc-back-btn {
        border-radius: 5px;
        font-size: 12px;
        font-weight: 600;
    }

    #voucherCampaignRoot .vc-error {
        border-radius: 6px;
        font-size: 12px;
    }

    #voucherCampaignRoot .form-check-input {
        cursor: pointer;
    }

    #voucherCampaignRoot .form-switch .form-check-input {
        width: 2.2em;
    }

    @media (max-width: 991.98px) {
        #voucherCampaignRoot .vc-card {
            padding: 16px;
        }
    }

    @media (max-width: 575.98px) {
        #voucherCampaignRoot {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        #voucherCampaignRoot .vc-card {
            padding: 14px;
            margin-bottom: 12px;
        }

        #voucherCampaignRoot .vc-header {
            align-items: flex-start !important;
        }

        #voucherCampaignRoot .vc-header h4 {
            font-size: 16px;
        }

        #voucherCampaignRoot .vc-header p {
            font-size: 11px;
        }

        #voucherCampaignRoot .vc-back-btn {
            padding: 6px 9px;
        }

        #voucherCampaignRoot .target-user-card {
            min-height: 56px;
        }
    }
</style>

<div class="container-fluid px-3 py-3" id="voucherCampaignRoot">

    <!-- Header -->
    <div class="vc-header d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <div>
            <h4 class="fw-bold text-dark mb-1" style="font-size: 18px;">
                <i class="fas fa-gift text-success me-2"></i>Create Voucher Campaign
            </h4>
            <p class="text-muted small mb-0">Create customer claimable vouchers.</p>
        </div>

        <a href="{{ $voucherIndexUrl }}" class="btn btn-outline-secondary btn-sm vc-back-btn px-3">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <!-- Validation Errors -->
    @if ($errors->any())
        <div class="alert alert-danger border-0 vc-error mb-3">
            <div class="d-flex align-items-center mb-1">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Fix these errors:</strong>
            </div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ $voucherStoreUrl }}" method="POST">
        @csrf

        <div class="row g-3">

            <!-- Main Information -->
            <div class="col-12 col-lg-8">

                <!-- Campaign Details -->
                <div class="vc-card">
                    <h5 class="vc-card-title">
                        <i class="fas fa-info-circle text-primary"></i>
                        Campaign Details
                    </h5>

                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <label class="vc-label">Campaign Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control form-control-sm" placeholder="Eid Voucher ৳200 OFF" value="{{ old('title') }}" required>
                            <small class="vc-help">Shown in customer wallets.</small>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="vc-label">Code Prefix</label>
                            <input type="text" name="code_prefix" class="form-control form-control-sm text-uppercase font-monospace fw-bold" placeholder="EID" value="{{ old('code_prefix', 'VCH') }}">
                            <small class="vc-help">Example: EID-8A3F9X</small>
                        </div>

                        <div class="col-12">
                            <label class="vc-label">Terms &amp; Conditions</label>
                            <textarea name="terms_conditions" class="form-control form-control-sm" rows="3" placeholder="Enter voucher rules...">{{ old('terms_conditions') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Customer Eligibility -->
                <div class="vc-card">
                    <div class="vc-target-heading">
                        <h5 class="vc-card-title mb-0">
                            <i class="fas fa-users text-success"></i>
                            Customer Eligibility
                        </h5>
                        <span class="badge bg-light text-muted border" style="font-size: 9px;">TARGET USERS</span>
                    </div>

                    <p class="vc-section-note mt-2">Choose eligible customers.</p>

                    <div class="row g-2">
                        <div class="col-12 col-md-4">
                            <label class="target-user-card {{ $targetType === 'registered_only' ? 'active' : '' }}" id="card_target_registered">
                                <input type="radio" name="target_user_type" value="registered_only" class="form-check-input mt-0" {{ $targetType === 'registered_only' ? 'checked' : '' }} onchange="selectTargetUserType('registered_only')">
                                <div>
                                    <strong>Registered Users</strong>
                                    <small>Login required</small>
                                </div>
                            </label>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="target-user-card {{ $targetType === 'all' ? 'active' : '' }}" id="card_target_all">
                                <input type="radio" name="target_user_type" value="all" class="form-check-input mt-0" {{ $targetType === 'all' ? 'checked' : '' }} onchange="selectTargetUserType('all')">
                                <div>
                                    <strong>All Customers</strong>
                                    <small>Registered and guests</small>
                                </div>
                            </label>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="target-user-card {{ $targetType === 'guest_only' ? 'active' : '' }}" id="card_target_guest">
                                <input type="radio" name="target_user_type" value="guest_only" class="form-check-input mt-0" {{ $targetType === 'guest_only' ? 'checked' : '' }} onchange="selectTargetUserType('guest_only')">
                                <div>
                                    <strong>Guest Users</strong>
                                    <small>Guest checkout only</small>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Discount Settings -->
                <div class="vc-card">
                    <h5 class="vc-card-title">
                        <i class="fas fa-percent text-success"></i>
                        Discount Settings
                    </h5>

                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="vc-label">Voucher Type <span class="text-danger">*</span></label>
                            <select name="voucher_type" id="select_voucher_type" class="form-select form-select-sm" onchange="toggleVoucherDiscountInputs()" required>
                                <option value="fixed" {{ old('voucher_type') === 'fixed' ? 'selected' : '' }}>Fixed Amount (৳)</option>
                                <option value="percentage" {{ old('voucher_type') === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                <option value="free_shipping" {{ old('voucher_type') === 'free_shipping' ? 'selected' : '' }}>Free Delivery</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-4" id="wrap_voucher_discount_amount">
                            <label class="vc-label">Discount Value <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text" id="voucher_unit_symbol">৳</span>
                                <input type="number" step="0.01" min="0" name="discount_amount" id="input_voucher_discount_amount" class="form-control" placeholder="100" value="{{ old('discount_amount', 0) }}" required>
                            </div>
                        </div>

                        <div class="col-12 col-md-4" id="wrap_voucher_max_discount">
                            <label class="vc-label">Max Discount</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" min="0" name="max_discount" class="form-control" placeholder="No limit" value="{{ old('max_discount') }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="vc-label">Minimum Spend</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" min="0" name="min_spend" class="form-control" placeholder="0" value="{{ old('min_spend', 0) }}">
                            </div>
                            <small class="vc-help">Minimum cart subtotal.</small>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="vc-label">Validity <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <input type="number" min="1" name="validity_days" class="form-control" placeholder="7" value="{{ old('validity_days', 7) }}" required>
                                <span class="input-group-text">Days</span>
                            </div>
                            <small class="vc-help">Days after claiming.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quotas, Schedule & Status -->
            <div class="col-12 col-lg-4">

                <!-- Quota -->
                <div class="vc-card">
                    <h5 class="vc-card-title">
                        <i class="fas fa-users-cog text-warning"></i>
                        Quota &amp; Claims
                    </h5>

                    <div class="mb-3">
                        <label class="vc-label">Total Voucher Limit</label>
                        <input type="number" min="1" name="total_vouchers_count" class="form-control form-control-sm" placeholder="Unlimited" value="{{ old('total_vouchers_count') }}">
                        <small class="vc-help">Total campaign claims.</small>
                    </div>

                    <div class="mb-1">
                        <label class="vc-label">Claims Per Customer <span class="text-danger">*</span></label>
                        <input type="number" min="1" name="max_claims_per_user" class="form-control form-control-sm" value="{{ old('max_claims_per_user', 1) }}" required>
                        <small class="vc-help">Maximum claims per user.</small>
                    </div>
                </div>

                <!-- Schedule -->
                <div class="vc-card">
                    <h5 class="vc-card-title">
                        <i class="far fa-calendar-alt text-primary"></i>
                        Campaign Schedule
                    </h5>

                    <div class="mb-3">
                        <label class="vc-label">Start Date</label>
                        <input type="date" name="claim_start" class="form-control form-control-sm" value="{{ old('claim_start', date('Y-m-d')) }}">
                    </div>

                    <div class="mb-1">
                        <label class="vc-label">End Date</label>
                        <input type="date" name="claim_end" class="form-control form-control-sm" value="{{ old('claim_end') }}">
                        <small class="vc-help">Leave blank for no deadline.</small>
                    </div>
                </div>

                <!-- Activation -->
                <div class="vc-card">
                    <h5 class="vc-card-title">
                        <i class="fas fa-toggle-on text-success"></i>
                        Activation
                    </h5>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="check_voucher_is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold small text-dark" for="check_voucher_is_active">Active Campaign</label>
                    </div>

                    <div class="vc-actions">
                        <button type="submit" class="btn btn-success w-100 py-2">
                            <i class="fas fa-save me-1"></i> Save Campaign
                        </button>
                    </div>
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