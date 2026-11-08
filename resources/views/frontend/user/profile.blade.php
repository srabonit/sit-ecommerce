@extends('layouts.dashboard')

@php
    // Central Global Settings Cache Resolver
    static $profSettingsCache = null;
    if ($profSettingsCache === null) {
        $profSettingsCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
            return \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $profSettings = $profSettingsCache ?? [];

    // Dynamic Theme Color Resolvers
    $profCardBg        = $profSettings['color_auth_card_bg'] ?? '#ffffff';
    $profCardBorder    = $profSettings['color_auth_card_border'] ?? '#edf2f7';
    $profTitleColor    = $profSettings['color_page_title'] ?? '#0f172a';
    $profTextColor     = $profSettings['color_auth_text'] ?? '#64748b';
    $profInputBg       = $profSettings['color_auth_input_bg'] ?? '#ffffff';
    $profInputBorder   = $profSettings['color_auth_input_border'] ?? '#cbd5e1';
    $profInputFocus    = $profSettings['color_auth_input_focus_border'] ?? ($profSettings['color_common_btn_bg'] ?? '#2563eb');
    
    $profPrimaryColor  = $profSettings['color_common_btn_bg'] ?? '#2563eb';
    $profPrimaryHover  = $profSettings['color_auth_btn_hover'] ?? '#1d4ed8';

    // 2FA Configuration Resolver
    $authSettingsService = app(\App\Services\Auth\AuthenticationSettingsService::class);
    $twoFaConfig = $authSettingsService->getTwoFactorConfig();

    // Check if 2FA is active on account (Personal opt-in OR Global policy enforcement)
    $isPersonal2FaActive = !empty($user->two_factor_enabled);
    $isMandatoryEnforced = $authSettingsService->isTwoFactorRequiredForUser($user) && !$isPersonal2FaActive;
    $is2FaActive         = $isPersonal2FaActive || $isMandatoryEnforced;

    $activeChannel = $user->two_factor_type ?: ($twoFaConfig['primary_type'] ?? 'sms');
    $twoFaToggleRoute = Route::has('user.two_factor.toggle') ? route('user.two_factor.toggle') : url('/user/two-factor/toggle');
@endphp

@section('title', 'Personal Info & Security - ' . config('app.name', 'Shop Now BD'))

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

    .profile-content-root a,
    .profile-content-root a:hover {
        text-decoration: none !important;
    }

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

    .btn-identity-action {
        font-size: 11.5px !important;
        font-weight: 600 !important;
        padding: 4px 10px !important;
        border-radius: 6px !important;
        border: 1px solid #cbd5e1 !important;
        background-color: #f8fafc !important;
        color: #334155 !important;
        transition: all 0.2s ease;
    }

    .btn-identity-action:hover {
        background-color: #e2e8f0 !important;
        color: #0f172a !important;
    }

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

    .bn-digit {
        font-weight: 700 !important;
    }

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

    {{-- Session Flash Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show small py-2.5 mb-3 border-0 shadow-sm" role="alert" style="border-radius: 10px;">
            <i class="fas fa-check-circle me-1.5 text-success"></i> {{ session('success') }}
            <button type="button" class="btn-close py-2.5" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show small py-2.5 mb-3 border-0 shadow-sm" role="alert" style="border-radius: 10px;">
            <i class="fas fa-exclamation-triangle me-1.5 text-danger"></i> {{ session('error') }}
            <button type="button" class="btn-close py-2.5" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3 g-md-4">
        
        {{-- Left Column: Personal Information & Verified Identities --}}
        <div class="col-12 col-lg-7">
            <div class="profile-page-card h-100">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <div class="d-flex align-items-center gap-2">
                        <div class="fs-5" style="color: var(--prof-primary);">
                            <i class="far fa-user"></i>
                        </div>
                        <h5 class="profile-card-title mb-0">Personal Information</h5>
                    </div>

                    @if(!empty($user->google_id))
                        <span class="badge bg-light text-dark border px-2 py-1 shadow-xs" style="font-size: 11px; border-radius: 6px;">
                            <i class="fab fa-google text-danger me-1"></i> Google Connected
                        </span>
                    @endif
                </div>
                <p class="profile-card-subtitle mb-4">Manage your basic profile details and verified identities</p>

                <form action="{{ route('user.profile.update') }}" method="POST">
                    @csrf
                    
                    {{-- Full Name --}}
                    <div class="mb-3">
                        <label class="profile-form-label">
                            Full Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               class="form-control profile-form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $user->name) }}" 
                               required>
                        @error('name')
                            <div class="invalid-feedback small fw-semibold">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Phone Number Display with Verification Badge & Change Modal Trigger --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1.5">
                            <label class="profile-form-label mb-0">
                                Phone Number
                                @if(!empty($user->phone_verified_at))
                                    <span class="badge bg-success-subtle text-success border border-success-subtle ms-1" style="font-size: 10px; font-weight: 600; padding: 2px 6px; border-radius: 4px;">
                                        <i class="fas fa-check-circle me-0.5"></i> Verified
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle ms-1" style="font-size: 10px; font-weight: 600; padding: 2px 6px; border-radius: 4px;">
                                        Unverified
                                    </span>
                                @endif
                            </label>
                            
                            <button type="button" class="btn btn-identity-action" data-bs-toggle="modal" data-bs-target="#phoneChangeModal">
                                <i class="fas fa-mobile-alt me-1 text-primary"></i> {{ empty($user->phone) ? 'Link Phone' : 'Change' }}
                            </button>
                        </div>
                        <input type="text" 
                               class="form-control profile-form-control bn-digit bg-light" 
                               id="displayUserPhoneInput"
                               value="{{ $user->phone ?: 'No phone number linked yet' }}" 
                               readonly>
                        <span class="form-text small text-muted" style="font-size: 11px;">
                            Used for SMS order notifications, passwordless login, and account recovery.
                        </span>
                    </div>

                    {{-- Email Address Display with Verification Badge & Change Modal Trigger --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1.5">
                            <label class="profile-form-label mb-0">
                                Email Address
                                @if(!empty($user->email_verified_at))
                                    <span class="badge bg-success-subtle text-success border border-success-subtle ms-1" style="font-size: 10px; font-weight: 600; padding: 2px 6px; border-radius: 4px;">
                                        <i class="fas fa-check-circle me-0.5"></i> Verified
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle ms-1" style="font-size: 10px; font-weight: 600; padding: 2px 6px; border-radius: 4px;">
                                        Unverified
                                    </span>
                                @endif
                            </label>
                            
                            <button type="button" class="btn btn-identity-action" data-bs-toggle="modal" data-bs-target="#emailChangeModal">
                                <i class="fas fa-envelope me-1 text-primary"></i> {{ empty($user->email) ? 'Link Email' : 'Change' }}
                            </button>
                        </div>
                        <input type="email" 
                               class="form-control profile-form-control bg-light" 
                               id="displayUserEmailInput"
                               value="{{ $user->email ?: 'No email address linked' }}" 
                               readonly>
                        <span class="form-text small text-muted" style="font-size: 11px;">
                            Used for invoices, digital receipts, and security updates.
                        </span>
                    </div>

                    {{-- Gender --}}
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

        {{-- Right Column: Account Security, 2FA, Active Sessions & Account Deletion --}}
        <div class="col-12 col-lg-5">
            
            {{-- 1. Password Change Card --}}
            <div class="profile-page-card mb-3 mb-md-4">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <div class="fs-5 text-danger">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h5 class="profile-card-title mb-0">Change Password</h5>
                </div>
                <p class="profile-card-subtitle mb-4">Update your password to keep your account secure</p>

                <form action="{{ route('user.password.update') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="profile-form-label">
                            Current Password <span class="text-danger">*</span>
                        </label>
                        <input type="password" 
                               name="current_password" 
                               class="form-control profile-form-control @error('current_password') is-invalid @enderror" 
                               placeholder="Enter current password" 
                               required>
                        @error('current_password')
                            <div class="invalid-feedback small fw-semibold">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="profile-form-label">
                            New Password <span class="text-danger">*</span>
                        </label>
                        <input type="password" 
                               name="new_password" 
                               class="form-control profile-form-control @error('new_password') is-invalid @enderror" 
                               placeholder="Min 6 characters" 
                               required>
                        @error('new_password')
                            <div class="invalid-feedback small fw-semibold">{{ $message }}</div>
                        @enderror
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

            {{-- 2. Two-Factor Authentication (2FA) Card (Personal Choice & Global Control) --}}
            <div class="profile-page-card mb-3 mb-md-4">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <div class="d-flex align-items-center gap-2">
                        <div class="fs-5 text-primary">
                            <i class="fas fa-shield-halved"></i>
                        </div>
                        <h5 class="profile-card-title mb-0">Two-Factor Authentication</h5>
                    </div>
                    @if($isMandatoryEnforced)
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 11px; border-radius: 6px;">
                            Mandatory (Enforced)
                        </span>
                    @else
                        <span class="badge {{ $is2FaActive ? 'bg-success-subtle text-success border-success-subtle' : 'bg-secondary-subtle text-secondary' }} border" style="font-size: 11px; border-radius: 6px;">
                            {{ $is2FaActive ? 'Active' : 'Disabled' }}
                        </span>
                    @endif
                </div>
                <p class="profile-card-subtitle mb-3">Add an extra layer of protection when signing in to your account</p>

                @if($is2FaActive)
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div>
                                <span class="text-muted" style="font-size: 11px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Current Verification Channel</span>
                                <div class="small fw-bold text-dark d-flex align-items-center gap-1.5 mt-0.5">
                                    @if($activeChannel === 'both')
                                        <i class="fas fa-shield-alt text-primary"></i> Both (SMS OTP &amp; Email Code)
                                    @elseif($activeChannel === 'email')
                                        <i class="fas fa-envelope text-info"></i> Email Verification Code
                                    @else
                                        <i class="fas fa-mobile-screen-button text-success"></i> SMS OTP (Mobile Phone)
                                    @endif
                                </div>
                            </div>
                            
                            @if(!$isMandatoryEnforced)
                                <button type="button" class="btn btn-outline-danger btn-sm px-2.5 py-1" style="font-size: 12px; border-radius: 6px;" data-bs-toggle="modal" data-bs-target="#disable2faModal">
                                    Turn Off
                                </button>
                            @endif
                        </div>

                        {{-- Change Channel Form --}}
                        <form action="{{ $twoFaToggleRoute }}" method="POST" class="mt-2 pt-2 border-top">
                            @csrf
                            <input type="hidden" name="enable" value="1">
                            <label class="form-label small text-muted mb-1" style="font-size: 11px; font-weight: 600;">Switch / Update Channel:</label>
                            <div class="input-group input-group-sm">
                                <select name="two_factor_type" class="form-select profile-form-control py-1" style="font-size: 12px;">
                                    <option value="sms" {{ $activeChannel === 'sms' ? 'selected' : '' }} {{ empty($user->phone) ? 'disabled' : '' }}>
                                        SMS OTP ({{ $user->phone ?: 'No Phone Linked' }})
                                    </option>
                                    <option value="email" {{ $activeChannel === 'email' ? 'selected' : '' }} {{ empty($user->email) ? 'disabled' : '' }}>
                                        Email Code ({{ $user->email ?: 'No Email Linked' }})
                                    </option>
                                    <option value="both" {{ $activeChannel === 'both' ? 'selected' : '' }} {{ (empty($user->phone) || empty($user->email)) ? 'disabled' : '' }}>
                                        Both (SMS OTP &amp; Email Code)
                                    </option>
                                </select>
                                <button type="submit" class="btn btn-outline-primary px-3 fw-bold" style="font-size: 11.5px;">Update</button>
                            </div>
                        </form>
                    </div>
                @else
                    {{-- Enable 2FA Form --}}
                    <form action="{{ $twoFaToggleRoute }}" method="POST">
                        @csrf
                        <input type="hidden" name="enable" value="1">
                        <div class="mb-3">
                            <label class="profile-form-label">Select Security Verification Channel</label>
                            <select name="two_factor_type" class="form-select profile-form-control">
                                <option value="sms" {{ empty($user->phone) ? 'disabled' : '' }}>
                                    SMS OTP (Mobile Phone: {{ $user->phone ?: 'Not Linked' }})
                                </option>
                                <option value="email" {{ empty($user->email) ? 'disabled' : '' }}>
                                    Email Code (Inbox: {{ $user->email ?: 'Not Linked' }})
                                </option>
                                <option value="both" {{ (empty($user->phone) || empty($user->email)) ? 'disabled' : '' }}>
                                    Both (SMS OTP &amp; Email Code)
                                </option>
                            </select>
                            <span class="form-text small text-muted" style="font-size: 11px;">
                                A 6-digit cryptographic code will be dispatched each time you sign in.
                            </span>
                        </div>
                        <button type="submit" class="btn btn-outline-primary btn-sm w-100 py-2 fw-bold" style="border-radius: 8px;">
                            <i class="fas fa-shield-alt me-1"></i> Activate Two-Factor Authentication
                        </button>
                    </form>
                @endif
            </div>

            {{-- 3. Device & Session Security Card --}}
            <div class="profile-page-card mb-3 mb-md-4">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <div class="fs-5 text-secondary">
                        <i class="fas fa-laptop-house"></i>
                    </div>
                    <h5 class="profile-card-title mb-0">Active Sessions</h5>
                </div>
                <p class="profile-card-subtitle mb-3">Terminate active sessions on other browsers or phones</p>

                <div class="p-3 bg-light rounded-3 mb-3 border">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small fw-bold text-dark d-flex align-items-center gap-1.5">
                                <span class="badge bg-success p-1 rounded-circle"></span> Current Device
                            </div>
                            <span class="text-muted font-monospace" style="font-size: 11px;">Active Session</span>
                        </div>
                        <span class="badge bg-white text-muted border px-2 py-1" style="font-size: 11px;">This Browser</span>
                    </div>
                </div>

                <button type="button" class="btn btn-outline-secondary btn-sm w-100 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#logoutOthersModal" style="border-radius: 8px;">
                    <i class="fas fa-sign-out-alt me-1 text-danger"></i> Log Out of Other Devices
                </button>
            </div>

            {{-- 4. Play Store Compliant Specific Delete Page Link --}}
            @if(!in_array(strtolower($user->role), ['admin', 'superadmin', 'manager', 'staff']))
            <div class="delete-account-callout">
                <h6 class="fw-bold text-danger mb-1 d-flex align-items-center gap-2">
                    <i class="fas fa-trash-alt"></i>
                    <span>Delete Account</span>
                </h6>
                <p class="small mb-3" style="color: var(--prof-text); font-size: 12px; line-height: 1.55;">
                    Request to delete your account. You can reactivate within <span class="bn-digit">30</span> days. After <span class="bn-digit">30</span> days, your data is permanently removed.[cite: 31]
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

{{-- =========================================================================
     MODAL 1: Phone Number Change with SMS OTP Verification
========================================================================== --}}
<div class="modal fade" id="phoneChangeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header border-bottom py-3 px-4">
                <h6 class="modal-title fw-bold text-dark mb-0">
                    <i class="fas fa-mobile-alt text-primary me-1.5"></i> Update Phone Number
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div id="mPhoneAlertBox" class="alert py-2 small rounded-3 mb-3 border-0 d-none"></div>

                <div id="mPhoneStep1">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Current Account Password <span class="text-danger">*</span></label>
                        <input type="password" id="mCurrentPasswordPhone" class="form-control profile-form-control" placeholder="Confirm your password to proceed" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">New Mobile Number <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted small border-end-0" style="border-radius: 8px 0 0 8px;">+880</span>
                            <input type="tel" id="mNewPhoneInput" class="form-control profile-form-control font-monospace" placeholder="17XXXXXXXX" maxlength="11" required>
                        </div>
                        <span class="form-text small text-muted" style="font-size: 11px;">A 6-digit verification code will be dispatched to this number.</span>
                    </div>

                    <button type="button" id="btnRequestPhoneOtp" class="btn btn-primary w-100 py-2 rounded-3 fw-bold shadow-sm">
                        <i class="fas fa-paper-plane me-1"></i> Send Verification Code
                    </button>
                </div>

                <div id="mPhoneStep2" class="d-none">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-muted">Sent to: <strong class="text-dark font-monospace" id="mDisplayTargetPhone"></strong></span>
                        <button type="button" id="btnBackToPhoneStep1" class="btn btn-link p-0 small text-decoration-none">Change Number</button>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">6-Digit Code</label>
                        <input type="text" id="mPhoneOtpCode" class="form-control text-center py-2 font-monospace fw-bold" placeholder="000000" maxlength="6" style="letter-spacing: 6px; font-size: 20px;">
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3 small">
                        <span class="text-muted">Expires in: <strong id="mPhoneCountdown">05:00</strong></span>
                        <button type="button" id="btnResendPhoneChangeOtp" class="btn btn-link p-0 small text-decoration-none disabled" disabled>Resend Code</button>
                    </div>

                    <button type="button" id="btnConfirmPhoneChange" class="btn btn-success w-100 py-2 rounded-3 fw-bold shadow-sm">
                        Verify &amp; Update Phone
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- =========================================================================
     MODAL 2: Email Address Change with Email OTP Verification
========================================================================== --}}
<div class="modal fade" id="emailChangeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header border-bottom py-3 px-4">
                <h6 class="modal-title fw-bold text-dark mb-0">
                    <i class="fas fa-envelope text-primary me-1.5"></i> Update Email Address
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div id="mEmailAlertBox" class="alert py-2 small rounded-3 mb-3 border-0 d-none"></div>

                <div id="mEmailStep1">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Current Account Password <span class="text-danger">*</span></label>
                        <input type="password" id="mCurrentPasswordEmail" class="form-control profile-form-control" placeholder="Confirm your password to proceed" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">New Email Address <span class="text-danger">*</span></label>
                        <input type="email" id="mNewEmailInput" class="form-control profile-form-control" placeholder="name@example.com" required>
                        <span class="form-text small text-muted" style="font-size: 11px;">A 6-digit confirmation passcode will be sent to your inbox.</span>
                    </div>

                    <button type="button" id="btnRequestEmailOtp" class="btn btn-primary w-100 py-2 rounded-3 fw-bold shadow-sm">
                        <i class="fas fa-paper-plane me-1"></i> Send Passcode
                    </button>
                </div>

                <div id="mEmailStep2" class="d-none">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-muted">Sent to: <strong class="text-dark" id="mDisplayTargetEmail"></strong></span>
                        <button type="button" id="btnBackToEmailStep1" class="btn btn-link p-0 small text-decoration-none">Change Email</button>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Passcode</label>
                        <input type="text" id="mEmailOtpCode" class="form-control text-center py-2 font-monospace fw-bold" placeholder="000000" maxlength="6" style="letter-spacing: 6px; font-size: 20px;">
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3 small">
                        <span class="text-muted">Expires in: <strong id="mEmailCountdown">05:00</strong></span>
                        <button type="button" id="btnResendEmailChangeOtp" class="btn btn-link p-0 small text-decoration-none disabled" disabled>Resend Code</button>
                    </div>

                    <button type="button" id="btnConfirmEmailChange" class="btn btn-success w-100 py-2 rounded-3 fw-bold shadow-sm">
                        Verify &amp; Update Email
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- =========================================================================
     MODAL 3: Terminate Other Device Sessions Confirmation
========================================================================== --}}
@php
    $logoutOthersRoute = Route::has('user.sessions.logout_others') ? route('user.sessions.logout_others') : url('/user/sessions/logout-others');
@endphp
<div class="modal fade" id="logoutOthersModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center" style="border-radius: 12px;">
            <div class="modal-body p-4">
                <div class="mb-3 text-warning">
                    <i class="fas fa-sign-out-alt fs-1"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">Terminate Other Sessions?</h6>
                <p class="text-muted small mb-3">Please enter your password to sign out of all other browsers and devices.</p>

                <form action="{{ $logoutOthersRoute }}" method="POST">
                    @csrf
                    <div class="mb-3 text-start">
                        <label class="form-label small fw-bold text-dark">Account Password</label>
                        <input type="password" name="password" class="form-control profile-form-control" placeholder="••••••••" required>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light btn-sm flex-fill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger btn-sm flex-fill">Log Out Others</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- =========================================================================
     MODAL 4: Disable 2FA Password Confirmation
========================================================================== --}}
<div class="modal fade" id="disable2faModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center" style="border-radius: 12px;">
            <div class="modal-body p-4">
                <div class="mb-3 text-danger">
                    <i class="fas fa-shield-virus fs-1"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">Disable 2FA?</h6>
                <p class="text-muted small mb-3">Confirm your password to turn off two-factor authentication.</p>

                <form action="{{ $twoFaToggleRoute }}" method="POST">
                    @csrf
                    <input type="hidden" name="enable" value="0">
                    <div class="mb-3 text-start">
                        <label class="form-label small fw-bold text-dark">Current Password</label>
                        <input type="password" name="current_password" class="form-control profile-form-control" placeholder="••••••••" required>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light btn-sm flex-fill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger btn-sm flex-fill">Disable 2FA</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    function initProfileSecurityClient() {
        const csrfToken = '{{ csrf_token() }}';

        // --- 1. Phone Update Pipeline ---
        const btnReqPhoneOtp   = document.getElementById('btnRequestPhoneOtp');
        const btnConfPhone     = document.getElementById('btnConfirmPhoneChange');
        const btnResendPhone   = document.getElementById('btnResendPhoneChangeOtp');
        const btnBackPhone     = document.getElementById('btnBackToPhoneStep1');
        const inputCurrPassP   = document.getElementById('mCurrentPasswordPhone');
        const inputNewPhone    = document.getElementById('mNewPhoneInput');
        const inputPhoneOtp    = document.getElementById('mPhoneOtpCode');
        const step1P           = document.getElementById('mPhoneStep1');
        const step2P           = document.getElementById('mPhoneStep2');
        const alertP           = document.getElementById('mPhoneAlertBox');
        const displayPhone     = document.getElementById('mDisplayTargetPhone');
        const countdownP       = document.getElementById('mPhoneCountdown');
        const displayUserPhone = document.getElementById('displayUserPhoneInput');
        let timerP             = null;

        function showAlertP(msg, isSuccess = false) {
            if (!alertP) return;
            alertP.textContent = msg;
            alertP.className = 'alert py-2 small rounded-3 mb-3 border-0 ' + (isSuccess ? 'alert-success' : 'alert-danger');
            alertP.classList.remove('d-none');
        }

        function startTimerP(seconds) {
            clearInterval(timerP);
            let rem = seconds;
            if (btnResendPhone) {
                btnResendPhone.disabled = true;
                btnResendPhone.classList.add('disabled');
            }
            timerP = setInterval(() => {
                if (rem <= 0) {
                    clearInterval(timerP);
                    if (countdownP) countdownP.textContent = '00:00';
                    if (btnResendPhone) {
                        btnResendPhone.disabled = false;
                        btnResendPhone.classList.remove('disabled');
                    }
                    return;
                }
                const m = Math.floor(rem / 60);
                const s = rem % 60;
                if (countdownP) countdownP.textContent = `${m < 10 ? '0' : ''}${m}:${s < 10 ? '0' : ''}${s}`;
                rem--;
            }, 1000);
        }

        if (btnReqPhoneOtp) {
            btnReqPhoneOtp.addEventListener('click', function() {
                const pass = inputCurrPassP.value.trim();
                const phone = inputNewPhone.value.trim();

                if (!pass || !phone) {
                    showAlertP('Please enter your current password and new mobile number.');
                    return;
                }

                btnReqPhoneOtp.disabled = true;
                btnReqPhoneOtp.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending...';

                @php
                    $reqPhoneOtpUrl = Route::has('user.phone.change.otp') ? route('user.phone.change.otp') : url('/user/phone/change-otp');
                @endphp
                fetch('{{ $reqPhoneOtpUrl }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ current_password: pass, new_phone: phone })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        displayPhone.textContent = phone;
                        step1P.classList.add('d-none');
                        step2P.classList.remove('d-none');
                        showAlertP(data.message, true);
                        startTimerP(data.cooldown_seconds || 60);
                        if (inputPhoneOtp) inputPhoneOtp.focus();
                    } else {
                        showAlertP(data.message || 'Failed to send OTP.');
                    }
                })
                .catch(() => showAlertP('Network error occurred.'))
                .finally(() => {
                    btnReqPhoneOtp.disabled = false;
                    btnReqPhoneOtp.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Send Verification Code';
                });
            });
        }

        if (btnConfPhone) {
            btnConfPhone.addEventListener('click', function() {
                const phone = inputNewPhone.value.trim();
                const code  = inputPhoneOtp.value.trim();

                if (!code || code.length < 4) {
                    showAlertP('Please enter the 6-digit OTP code.');
                    return;
                }

                btnConfPhone.disabled = true;
                btnConfPhone.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Verifying...';

                @php
                    $confPhoneUrl = Route::has('user.phone.change.confirm') ? route('user.phone.change.confirm') : url('/user/phone/change-confirm');
                @endphp
                fetch('{{ $confPhoneUrl }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ new_phone: phone, otp_code: code })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showAlertP(data.message, true);
                        if (displayUserPhone) displayUserPhone.value = data.new_phone || phone;
                        setTimeout(() => {
                            const modal = bootstrap.Modal.getInstance(document.getElementById('phoneChangeModal'));
                            if (modal) modal.hide();
                            window.location.reload();
                        }, 1200);
                    } else {
                        showAlertP(data.message || 'Verification failed.');
                        btnConfPhone.disabled = false;
                        btnConfPhone.textContent = 'Verify & Update Phone';
                    }
                })
                .catch(() => {
                    showAlertP('Network error occurred.');
                    btnConfPhone.disabled = false;
                    btnConfPhone.textContent = 'Verify & Update Phone';
                });
            });
        }

        if (btnResendPhone) {
            btnResendPhone.addEventListener('click', () => btnReqPhoneOtp.click());
        }

        if (btnBackPhone) {
            btnBackPhone.addEventListener('click', () => {
                clearInterval(timerP);
                step2P.classList.add('d-none');
                step1P.classList.remove('d-none');
                if (inputPhoneOtp) inputPhoneOtp.value = '';
                if (alertP) alertP.classList.add('d-none');
            });
        }

        // --- 2. Email Update Pipeline ---
        const btnReqEmailOtp   = document.getElementById('btnRequestEmailOtp');
        const btnConfEmail     = document.getElementById('btnConfirmEmailChange');
        const btnResendEmail   = document.getElementById('btnResendEmailChangeOtp');
        const btnBackEmail     = document.getElementById('btnBackToEmailStep1');
        const inputCurrPassE   = document.getElementById('mCurrentPasswordEmail');
        const inputNewEmail    = document.getElementById('mNewEmailInput');
        const inputEmailOtp    = document.getElementById('mEmailOtpCode');
        const step1E           = document.getElementById('mEmailStep1');
        const step2E           = document.getElementById('mEmailStep2');
        const alertE           = document.getElementById('mEmailAlertBox');
        const displayEmail     = document.getElementById('mDisplayTargetEmail');
        const countdownE       = document.getElementById('mEmailCountdown');
        const displayUserEmail = document.getElementById('displayUserEmailInput');
        let timerE             = null;

        function showAlertE(msg, isSuccess = false) {
            if (!alertE) return;
            alertE.textContent = msg;
            alertE.className = 'alert py-2 small rounded-3 mb-3 border-0 ' + (isSuccess ? 'alert-success' : 'alert-danger');
            alertE.classList.remove('d-none');
        }

        function startTimerE(seconds) {
            clearInterval(timerE);
            let rem = seconds;
            if (btnResendEmail) {
                btnResendEmail.disabled = true;
                btnResendEmail.classList.add('disabled');
            }
            timerE = setInterval(() => {
                if (rem <= 0) {
                    clearInterval(timerE);
                    if (countdownE) countdownE.textContent = '00:00';
                    if (btnResendEmail) {
                        btnResendEmail.disabled = false;
                        btnResendEmail.classList.remove('disabled');
                    }
                    return;
                }
                const m = Math.floor(rem / 60);
                const s = rem % 60;
                if (countdownE) countdownE.textContent = `${m < 10 ? '0' : ''}${m}:${s < 10 ? '0' : ''}${s}`;
                rem--;
            }, 1000);
        }

        if (btnReqEmailOtp) {
            btnReqEmailOtp.addEventListener('click', function() {
                const pass = inputCurrPassE.value.trim();
                const email = inputNewEmail.value.trim();

                if (!pass || !email) {
                    showAlertE('Please enter your current password and new email address.');
                    return;
                }

                btnReqEmailOtp.disabled = true;
                btnReqEmailOtp.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending...';

                @php
                    $reqEmailOtpUrl = Route::has('user.email.change.otp') ? route('user.email.change.otp') : url('/user/email/change-otp');
                @endphp
                fetch('{{ $reqEmailOtpUrl }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ current_password: pass, new_email: email })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        displayEmail.textContent = email;
                        step1E.classList.add('d-none');
                        step2E.classList.remove('d-none');
                        showAlertE(data.message, true);
                        startTimerE(data.cooldown_seconds || 60);
                        if (inputEmailOtp) inputEmailOtp.focus();
                    } else {
                        showAlertE(data.message || 'Failed to send passcode.');
                    }
                })
                .catch(() => showAlertE('Network error occurred.'))
                .finally(() => {
                    btnReqEmailOtp.disabled = false;
                    btnReqEmailOtp.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Send Passcode';
                });
            });
        }

        if (btnConfEmail) {
            btnConfEmail.addEventListener('click', function() {
                const email = inputNewEmail.value.trim();
                const code  = inputEmailOtp.value.trim();

                if (!code || code.length < 4) {
                    showAlertE('Please enter the 6-digit passcode.');
                    return;
                }

                btnConfEmail.disabled = true;
                btnConfEmail.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Verifying...';

                @php
                    $confEmailUrl = Route::has('user.email.change.confirm') ? route('user.email.change.confirm') : url('/user/email/change-confirm');
                @endphp
                fetch('{{ $confEmailUrl }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ new_email: email, otp_code: code })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showAlertE(data.message, true);
                        if (displayUserEmail) displayUserEmail.value = data.new_email || email;
                        setTimeout(() => {
                            const modal = bootstrap.Modal.getInstance(document.getElementById('emailChangeModal'));
                            if (modal) modal.hide();
                            window.location.reload();
                        }, 1200);
                    } else {
                        showAlertE(data.message || 'Verification failed.');
                        btnConfEmail.disabled = false;
                        btnConfEmail.textContent = 'Verify & Update Email';
                    }
                })
                .catch(() => {
                    showAlertE('Network error occurred.');
                    btnConfEmail.disabled = false;
                    btnConfEmail.textContent = 'Verify & Update Email';
                });
            });
        }

        if (btnResendEmail) {
            btnResendEmail.addEventListener('click', () => btnReqEmailOtp.click());
        }

        if (btnBackEmail) {
            btnBackEmail.addEventListener('click', () => {
                clearInterval(timerE);
                step2E.classList.add('d-none');
                step1E.classList.remove('d-none');
                if (inputEmailOtp) inputEmailOtp.value = '';
                if (alertE) alertE.classList.add('d-none');
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initProfileSecurityClient);
    } else {
        initProfileSecurityClient();
    }
})();
</script>
@endsection