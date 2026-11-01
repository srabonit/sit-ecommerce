@extends('layouts.dashboard')

@php
    // সেন্ট্রাল গ্লোবাল সেটিংস ক্যাশ রেজলভার
    static $addrSettingsCache = null;
    if ($addrSettingsCache === null) {
        $addrSettingsCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
            return \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $addrSettings = $addrSettingsCache ?? [];

    // ডায়নামিক থিম কালার ভ্যারিয়েবল (Existing Default Colors)
    $addrCardBg        = $addrSettings['color_auth_card_bg'] ?? '#ffffff';
    $addrCardBorder    = $addrSettings['color_auth_card_border'] ?? '#e2e8f0';
    $addrTitleColor    = $addrSettings['color_page_title'] ?? '#0f172a';
    $addrTextColor     = $addrSettings['color_auth_text'] ?? '#64748b';
    $addrInputBg       = $addrSettings['color_auth_input_bg'] ?? '#ffffff';
    $addrInputBorder   = $addrSettings['color_auth_input_border'] ?? '#cbd5e1';
    $addrInputFocus    = $addrSettings['color_auth_input_focus_border'] ?? ($addrSettings['color_common_btn_bg'] ?? '#2563eb');
    
    $addrBtnBg         = $addrSettings['color_common_btn_bg'] ?? '#2563eb';
    $addrBtnText       = $addrSettings['color_common_btn_text'] ?? '#ffffff';
    $addrBtnHover      = $addrSettings['color_auth_btn_hover'] ?? '#1d4ed8';

    // সেশন ও ইউজার অ্যাড্রেস ডেটা রেজলভার
    $currentUser     = $user ?? auth()->user();
    $sessionAddress  = session()->get('user_shipping_address', []);
    $currentDistrict = $currentUser->district ?? $sessionAddress['district'] ?? 'Dhaka';
    $currentAddress  = $currentUser->address ?? $sessionAddress['address'] ?? '';
@endphp

@section('title', 'Manage Addresses - ' . config('app.name', 'Shop Now BD'))

@section('dashboard_content')
<style>
    :root {
        --addr-card-bg: {{ $addrCardBg }};
        --addr-card-border: {{ $addrCardBorder }};
        --addr-title: {{ $addrTitleColor }};
        --addr-text: {{ $addrTextColor }};
        --addr-input-bg: {{ $addrInputBg }};
        --addr-input-border: {{ $addrInputBorder }};
        --addr-input-focus: {{ $addrInputFocus }};
        --addr-btn-bg: {{ $addrBtnBg }};
        --addr-btn-text: {{ $addrBtnText }};
        --addr-btn-hover: {{ $addrBtnHover }};
    }

    /* Card Box & Responsive Styling */
    .address-manage-card {
        background-color: var(--addr-card-bg) !important;
        border: 1px solid var(--addr-card-border) !important;
        border-radius: 16px !important;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04), 0 1px 3px rgba(15, 23, 42, 0.02) !important;
        transition: all 0.25s ease;
    }

    .address-card-title {
        color: var(--addr-title) !important;
        font-size: 1.15rem;
        font-weight: 700;
        line-height: 1.35;
    }

    .address-card-subtitle {
        color: var(--addr-text) !important;
        font-size: 12.5px;
        line-height: 1.45;
    }

    .address-icon-box {
        color: var(--addr-btn-bg) !important;
    }

    .address-form-label {
        color: var(--addr-title) !important;
        font-size: 12.5px;
        font-weight: 700;
        margin-bottom: 6px;
        display: block;
    }

    .address-form-control {
        background-color: var(--addr-input-bg) !important;
        border: 1px solid var(--addr-input-border) !important;
        color: var(--addr-title) !important;
        font-size: 13.5px;
        border-radius: 10px !important;
        padding: 9px 14px;
        transition: all 0.2s ease;
    }

    .address-form-control:focus {
        border-color: var(--addr-input-focus) !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
        background-color: #ffffff !important;
        outline: none;
    }

    .address-form-control::placeholder {
        color: #94a3b8 !important;
        font-size: 13px;
        opacity: 0.9;
    }

    /* Action Save Button */
    .btn-save-address {
        background-color: var(--addr-btn-bg) !important;
        border-color: var(--addr-btn-bg) !important;
        color: var(--addr-btn-text) !important;
        border-radius: 9999px !important;
        padding: 9px 28px !important;
        font-size: 13.5px !important;
        font-weight: 700 !important;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        text-decoration: none !important;
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-save-address:hover {
        background-color: var(--addr-btn-hover) !important;
        border-color: var(--addr-btn-hover) !important;
        color: var(--addr-btn-text) !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.28) !important;
    }

    .btn-save-address:active {
        transform: translateY(0);
    }

    /* Bengali digit protection */
    .bn-digit {
        font-weight: 700 !important;
    }

    /* Mobile Responsive Optimizations */
    @media (max-width: 575.98px) {
        .address-manage-card {
            padding: 18px 15px !important;
            border-radius: 14px !important;
        }

        .address-card-title {
            font-size: 1.05rem !important;
        }

        .address-form-control {
            padding: 8px 12px;
            font-size: 13px;
        }

        .btn-save-address {
            width: 100% !important;
            min-height: 44px;
        }
    }
</style>

<div class="address-manage-card p-3 p-md-4">
    <div class="d-flex align-items-center gap-2 mb-1">
        <div class="address-icon-box fs-5">
            <i class="fas fa-map-marker-alt"></i>
        </div>
        <h5 class="address-card-title mb-0">Manage Addresses</h5>
    </div>
    <p class="address-card-subtitle mb-4">Set your default shipping address for faster checkout</p>

    <form action="{{ route('user.addresses.update') }}" method="POST">
        @csrf
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label class="address-form-label">
                    District / Area <span class="text-danger">*</span>
                </label>
                <input type="text" 
                       name="district" 
                       class="form-control address-form-control bn-digit" 
                       value="{{ old('district', $currentDistrict) }}" 
                       placeholder="Dhaka, Gazipur, Chattogram" 
                       required>
            </div>

            <div class="col-12">
                <label class="address-form-label">
                    Full Delivery Address <span class="text-danger">*</span>
                </label>
                <textarea name="address" 
                          class="form-control address-form-control bn-digit" 
                          rows="3" 
                          placeholder="House No, Road No, Area / Thana, Post Code" 
                          required>{{ old('address', $currentAddress) }}</textarea>
            </div>

            <div class="col-12 mt-4 pt-1">
                <button type="submit" class="btn btn-save-address shadow-sm">
                    <i class="fas fa-check-circle"></i>
                    <span>Save Address</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection