@extends('layouts.dashboard')

@php
    // সেন্ট্রাল গ্লোবাল সেটিংস ক্যাশ রেজলভার
    static $profSettingsCache = null;
    if ($profSettingsCache === null) {
        $profSettingsCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
            return \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $profSettings = $profSettingsCache ?? [];

    // ডায়নামিক থিম কালার ভ্যারিয়েবল (Existing Default Colors)
    $profCardBg        = $profSettings['color_auth_card_bg'] ?? '#ffffff';
    $profCardBorder    = $profSettings['color_auth_card_border'] ?? '#edf2f7';
    $profTitleColor    = $profSettings['color_page_title'] ?? '#0f172a';
    $profTextColor     = $profSettings['color_auth_text'] ?? '#64748b';
    $profInputBg       = $profSettings['color_auth_input_bg'] ?? '#ffffff';
    $profInputBorder   = $profSettings['color_auth_input_border'] ?? '#cbd5e1';
    $profInputFocus    = $profSettings['color_auth_input_focus_border'] ?? ($profSettings['color_common_btn_bg'] ?? '#2563eb');
    
    $profPrimaryColor  = $profSettings['color_common_btn_bg'] ?? '#2563eb';
    $profPrimaryHover  = $profSettings['color_auth_btn_hover'] ?? '#1d4ed8';
@endphp

@section('title', 'Personal Info - ' . config('app.name', 'Shop Now BD'))

@section('dashboard_content')
<style>
    :root {
        --prof-card-bg: {{ $profCardBg }};
        --prof-card-border: {{ $profCardBorder }};
        --prof-title: {{ $profTitleColor }};
        --prof-text: {{ $profTextColor }};
        --prof-input-bg: {{ $profInputBg }};
        --prof-input-border: {{ $profInputBorder }};
        --prof-input-focus: {{ $profInputFocus }};
        --prof-primary: {{ $profPrimaryColor }};
        --prof-primary-hover: {{ $profPrimaryHover }};
    }

    /* Universal Strict Link Text Decoration */
    .profile-content-root a,
    .profile-content-root a:hover {
        text-decoration: none !important;
    }

    /* Profile Cards Base Styling */
    .profile-page-card {
        background-color: var(--prof-card-bg) !important;
        border: 1px solid var(--prof-card-border) !important;
        border-radius: 16px !important;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04), 0 1px 3px rgba(15, 23, 42, 0.02) !important;
        padding: 24px;
        transition: all 0.25s ease;
    }

    .profile-card-title {
        color: var(--prof-title) !important;
        font-weight: 700;
        font-size: 1.15rem;
        line-height: 1.35;
    }

    .profile-card-subtitle {
        color: var(--prof-text) !important;
        font-size: 12.5px;
        line-height: 1.45;
    }

    /* Form Labels & Controls */
    .profile-form-label {
        color: var(--prof-title) !important;
        font-size: 12.5px;
        font-weight: 700;
        margin-bottom: 6px;
        display: block;
    }

    .profile-form-control {
        background-color: var(--prof-input-bg) !important;
        border: 1px solid var(--prof-input-border) !important;
        color: var(--prof-title) !important;
        font-size: 13.5px;
        border-radius: 10px !important;
        padding: 9px 14px;
        transition: all 0.2s ease;
    }

    .profile-form-control:focus {
        border-color: var(--prof-input-focus) !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
        background-color: #ffffff !important;
        outline: none;
    }

    .profile-form-control::placeholder {
        color: #94a3b8 !important;
        font-size: 13px;
        opacity: 0.9;
    }

    /* Primary Save Button */
    .btn-profile-save {
        background-color: var(--prof-primary) !important;
        border-color: var(--prof-primary) !important;
        color: #ffffff !important;
        border-radius: 9999px !important;
        padding: 9px 28px !important;
        font-size: 13.5px !important;
        font-weight: 700 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 42px;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25) !important;
    }

    .btn-profile-save:hover {
        background-color: var(--prof-primary-hover) !important;
        border-color: var(--prof-primary-hover) !important;
        color: #ffffff !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35) !important;
    }

    .btn-profile-save:active {
        transform: translateY(0);
    }

    /* Password Update Button */
    .btn-password-update {
        border: 1px solid #ef4444 !important;
        color: #ef4444 !important;
        background: transparent !important;
        border-radius: 9999px !important;
        padding: 9px 24px !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 42px;
        transition: all 0.2s ease;
    }

    .btn-password-update:hover {
        background-color: #ef4444 !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25) !important;
        transform: translateY(-1px);
    }

    /* Delete Account Alert Box */
    .delete-account-callout {
        background-color: #ffffff !important;
        border: 1px solid var(--prof-card-border) !important;
        border-left: 4px solid #ef4444 !important;
        border-radius: 14px !important;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.03);
    }

    .btn-delete-link {
        border: 1px solid #fecaca !important;
        color: #dc2626 !important;
        background-color: #fff1f2 !important;
        border-radius: 50px !important;
        padding: 6px 16px !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
    }

    .btn-delete-link:hover {
        background-color: #ef4444 !important;
        border-color: #ef4444 !important;
        color: #ffffff !important;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.25);
    }

    /* Bengali digit protection */
    .bn-digit {
        font-weight: 700 !important;
    }

    /* Small Screen Optimization (iPhone SE, 320px–575px) */
    @media (max-width: 575.98px) {
        .profile-page-card {
            padding: 16px 14px !important;
            border-radius: 14px !important;
        }

        .profile-card-title {
            font-size: 1.05rem !important;
        }

        .profile-form-control {
            padding: 8px 12px;
            font-size: 13px;
        }

        .btn-profile-save,
        .btn-password-update {
            width: 100% !important;
            min-height: 44px;
        }

        .delete-account-callout {
            padding: 16px 14px !important;
        }

        .btn-delete-link {
            width: 100% !important;
            justify-content: center;
            min-height: 38px;
        }
    }
</style>

<div class="profile-content-root">
    <div class="row g-3 g-md-4">
        
        {{-- Left Column: Personal Information Form --}}
        <div class="col-12 col-lg-7">
            <div class="profile-page-card h-100">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <div class="fs-5" style="color: var(--prof-primary);">
                        <i class="far fa-user"></i>
                    </div>
                    <h5 class="profile-card-title mb-0">Personal Information</h5>
                </div>
                <p class="profile-card-subtitle mb-4">Manage your basic profile details</p>

                <form action="{{ route('user.profile.update') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="profile-form-label">
                            Full Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               class="form-control profile-form-control" 
                               value="{{ old('name', $user->name) }}" 
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="profile-form-label">
                            Phone Number <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               name="phone" 
                               class="form-control profile-form-control bn-digit" 
                               value="{{ old('phone', $user->phone) }}" 
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="profile-form-label">
                            Email Address <span class="text-danger">*</span>
                        </label>
                        <input type="email" 
                               name="email" 
                               class="form-control profile-form-control" 
                               value="{{ old('email', $user->email) }}" 
                               required>
                    </div>

                    <div class="mb-4">
                        <label class="profile-form-label">Gender (Optional)</label>
                        <select name="gender" class="form-select profile-form-control">
                            <option value="">Select Gender</option>
                            <option value="male" {{ (old('gender', $user->gender ?? '') === 'male') ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ (old('gender', $user->gender ?? '') === 'female') ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ (old('gender', $user->gender ?? '') === 'other') ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-profile-save">
                        <i class="fas fa-check-circle"></i>
                        <span>Save Changes</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- Right Column: Account Security & Account Deletion Link --}}
        <div class="col-12 col-lg-5">
            {{-- Password Change Card --}}
            <div class="profile-page-card mb-3 mb-md-4">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <div class="fs-5 text-danger">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h5 class="profile-card-title mb-0">Account Security</h5>
                </div>
                <p class="profile-card-subtitle mb-4">Update your password</p>

                <form action="{{ route('user.password.update') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="profile-form-label">
                            Current Password <span class="text-danger">*</span>
                        </label>
                        <input type="password" 
                               name="current_password" 
                               class="form-control profile-form-control" 
                               placeholder="Enter current password" 
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="profile-form-label">
                            New Password <span class="text-danger">*</span>
                        </label>
                        <input type="password" 
                               name="new_password" 
                               class="form-control profile-form-control" 
                               placeholder="Min 6 characters" 
                               required>
                    </div>

                    <div class="mb-4">
                        <label class="profile-form-label">
                            Confirm New Password <span class="text-danger">*</span>
                        </label>
                        <input type="password" 
                               name="new_password_confirmation" 
                               class="form-control profile-form-control" 
                               placeholder="Re-type new password" 
                               required>
                    </div>

                    <button type="submit" class="btn btn-password-update">
                        <i class="fas fa-key"></i>
                        <span>Update Password</span>
                    </button>
                </form>
            </div>

            {{-- Play Store Compliant Specific Delete Page Link --}}
            @if(!in_array(strtolower($user->role), ['admin', 'superadmin', 'manager', 'staff']))
            <div class="delete-account-callout">
                <h6 class="fw-bold text-danger mb-1 d-flex align-items-center gap-2">
                    <i class="fas fa-trash-alt"></i>
                    <span>Delete Account</span>
                </h6>
                <p class="small mb-3" style="color: var(--prof-text); font-size: 12px; line-height: 1.55;">
                    Request to delete your account. You can reactivate within <span class="bn-digit">30</span> days. After <span class="bn-digit">30</span> days, your data is permanently removed.
                </p>
                <a href="{{ route('user.account.delete.page') }}" class="btn btn-delete-link">
                    <span>Go to Delete Account Page</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            @endif
        </div>

    </div>
</div>
@endsection