@extends(Auth::check() ? 'layouts.dashboard' : 'layouts.app')

@php
    // সেন্ট্রাল গ্লোবাল সেটিংস ক্যাশ রেজলভার
    static $delSettingsCache = null;
    if ($delSettingsCache === null) {
        $delSettingsCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
            return \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $delSettings = $delSettingsCache ?? [];

    // ডায়নামিক থিম কালার ভ্যারিয়েবল (Existing Default Colors)
    $delSiteBg       = $delSettings['color_site_bg'] ?? '#f8fafc';
    $delCardBg       = $delSettings['color_auth_card_bg'] ?? '#ffffff';
    $delCardBorder   = $delSettings['color_auth_card_border'] ?? '#edf2f7';
    $delTitleColor   = $delSettings['color_page_title'] ?? '#0f172a';
    $delTextColor    = $delSettings['color_auth_text'] ?? '#64748b';
    $delInputBg      = $delSettings['color_auth_input_bg'] ?? '#ffffff';
    $delInputBorder  = $delSettings['color_auth_input_border'] ?? '#cbd5e1';
    $delInputFocus   = $delSettings['color_auth_input_focus_border'] ?? ($delSettings['color_common_btn_bg'] ?? '#2563eb');
    $delPrimaryColor = $delSettings['color_common_btn_bg'] ?? '#2563eb';
    $delPrimaryHover = $delSettings['color_auth_btn_hover'] ?? '#1d4ed8';

    $activeSection = Auth::check() ? 'dashboard_content' : 'content';
@endphp

@section('title', 'Delete Account - ' . config('app.name', 'Shop Now BD'))

@section($activeSection)
<style>
    :root {
        --del-site-bg: {{ $delSiteBg }};
        --del-card-bg: {{ $delCardBg }};
        --del-card-border: {{ $delCardBorder }};
        --del-title: {{ $delTitleColor }};
        --del-text: {{ $delTextColor }};
        --del-input-bg: {{ $delInputBg }};
        --del-input-border: {{ $delInputBorder }};
        --del-input-focus: {{ $delInputFocus }};
        --del-primary: {{ $delPrimaryColor }};
        --del-primary-hover: {{ $delPrimaryHover }};
    }

    /* Universal Strict Link Reset */
    .delete-account-card a,
    .delete-account-card a:hover {
        text-decoration: none !important;
    }

    /* Card Styling */
    .delete-account-card {
        background-color: var(--del-card-bg) !important;
        border: 1px solid var(--del-card-border) !important;
        border-radius: 18px;
        padding: 20px 16px;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04), 0 1px 3px rgba(15, 23, 42, 0.02);
        transition: all 0.25s ease;
    }

    @media (min-width: 768px) {
        .delete-account-card {
            padding: 36px 40px;
        }
    }

    /* Header Avatar Circle */
    .delete-avatar-circle {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background-color: #fee2e2;
        color: #ef4444;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15);
    }

    .delete-card-title {
        color: var(--del-title) !important;
        font-weight: 700;
        line-height: 1.3;
    }

    .delete-card-subtitle {
        color: var(--del-text) !important;
        font-size: 13px;
        line-height: 1.45;
    }

    /* Play Store Policy & Notice Box */
    .delete-notice-box {
        background-color: #fffbeb !important;
        border: 1px solid #fef3c7 !important;
        border-left: 4px solid #f59e0b !important;
        border-radius: 12px;
        padding: 14px 16px;
    }

    .delete-notice-box h6 {
        color: #92400e !important;
        font-size: 13px;
    }

    .delete-notice-list {
        color: #78350f !important;
        font-size: 12px;
        line-height: 1.65;
    }

    /* Form Inputs */
    .delete-form-label {
        color: var(--del-title) !important;
        font-size: 12.5px;
        font-weight: 700;
        margin-bottom: 6px;
        display: block;
    }

    .delete-form-control {
        background-color: var(--del-input-bg) !important;
        border: 1px solid var(--del-input-border) !important;
        color: var(--del-title) !important;
        border-radius: 10px !important;
        padding: 9px 14px;
        font-size: 13.5px;
        transition: all 0.2s ease;
    }

    .delete-form-control:focus {
        border-color: var(--del-input-focus) !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
        background-color: #ffffff !important;
        outline: none;
    }

    .delete-form-control::placeholder {
        color: #94a3b8 !important;
        font-size: 13px;
        opacity: 0.9;
    }

    /* Action Buttons Wrapper */
    .delete-action-btn-wrap {
        display: flex;
        flex-direction: column-reverse;
        gap: 10px;
        width: 100%;
        padding-top: 14px;
        border-top: 1px solid var(--del-card-border);
    }

    @media (min-width: 576px) {
        .delete-action-btn-wrap {
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
        }
        .delete-action-btn-wrap .btn {
            width: auto !important;
        }
    }

    .btn-cancel-account {
        background: #ffffff !important;
        border: 1px solid var(--del-input-border) !important;
        color: var(--del-text) !important;
        border-radius: 9999px !important;
        padding: 9px 24px !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .btn-cancel-account:hover {
        background-color: #f8fafc !important;
        border-color: #94a3b8 !important;
        color: var(--del-title) !important;
    }

    .btn-delete-confirm {
        background-color: #dc2626 !important;
        border-color: #dc2626 !important;
        color: #ffffff !important;
        border-radius: 9999px !important;
        padding: 9px 26px !important;
        font-weight: 700 !important;
        font-size: 13px !important;
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2) !important;
    }

    .btn-delete-confirm:hover {
        background-color: #b91c1c !important;
        border-color: #b91c1c !important;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(220, 38, 38, 0.3) !important;
    }

    .btn-delete-confirm:active {
        transform: translateY(0);
    }

    /* Bengali digit protection */
    .bn-digit {
        font-weight: 700 !important;
    }

    /* Target User Preview Box */
    .target-user-strip {
        background-color: #f8fafc;
        border: 1px solid var(--del-card-border);
        border-radius: 12px;
        padding: 12px 16px;
    }

    /* Small Screen Optimization (iPhone SE, 320px–375px) */
    @media (max-width: 575.98px) {
        .delete-card-title {
            font-size: 1.15rem !important;
        }

        .delete-notice-box {
            padding: 12px 14px;
        }

        .delete-notice-list {
            font-size: 11.5px;
        }

        .delete-form-control {
            padding: 8px 12px;
            font-size: 13px;
        }

        .delete-action-btn-wrap .btn {
            width: 100% !important;
            min-height: 44px;
        }
    }
</style>

<div class="{{ Auth::check() ? '' : 'container py-4 py-md-5' }}">
    <div class="row justify-content-center">
        <div class="col-12 {{ Auth::check() ? 'col-xl-10' : 'col-md-9 col-lg-7' }}">
            <div class="delete-account-card">
                
                {{-- Header --}}
                <div class="text-center mb-3 mb-md-4">
                    <div class="delete-avatar-circle">
                        <i class="fas fa-user-slash fs-4"></i>
                    </div>
                    <h4 class="delete-card-title mb-1 fs-5 fs-md-4">Account Deletion</h4>
                    <p class="delete-card-subtitle mb-0">
                        <strong>{{ config('app.name', 'Shop Now BD') }}</strong> &mdash; Request to deactivate and permanently delete your account
                    </p>
                </div>

                {{-- Status Alerts --}}
                @if(session('success'))
                    <div class="alert alert-success border-0 shadow-sm rounded-3 mb-3 py-2.5 px-3 d-flex align-items-center">
                        <i class="fas fa-check-circle me-2 fs-5"></i> 
                        <span class="small fw-semibold">{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-3 py-2.5 px-3 d-flex align-items-center">
                        <i class="fas fa-exclamation-circle me-2 fs-5"></i> 
                        <span class="small fw-semibold">{{ session('error') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger border-0 shadow-sm rounded-3 small mb-3 py-2.5 px-3">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Play Store & Compliance Notice Box --}}
                <div class="delete-notice-box mb-3 mb-md-4">
                    <h6 class="fw-bold mb-1 d-flex align-items-center gap-2">
                        <i class="fas fa-exclamation-triangle text-warning"></i> 
                        <span>Account deletion notice:</span>
                    </h6>
                    <ul class="mb-0 ps-3 delete-notice-list mt-2">
                        <li>Your account will be immediately logged out and moved to the <strong>Trash</strong>.</li>
                        <li>You have a <strong class="bn-digit">30-day grace period</strong>. If you log in again within 30 days, your account will be instantly reactivated.</li>
                        <li>After 30 days of inactivity in trash, all your personal data (name, phone, saved addresses, wishlist) will be <strong>permanently deleted</strong>.</li>
                        <li>Once 30 days pass and the account is permanently deleted, you or anyone else can create a new fresh account using the same phone number or email.</li>
                    </ul>
                </div>

                @if(Auth::check())
                    {{-- ================= 1. SIGNED IN USER VIEW ================= --}}
                    <div class="target-user-strip mb-3 d-flex align-items-center justify-content-between gap-2">
                        <div class="min-w-0">
                            <span class="d-block" style="font-size: 11px; color: var(--del-text);">Target Account</span>
                            <strong class="d-block text-truncate" style="color: var(--del-title);">{{ Auth::user()->name }}</strong> 
                            <small class="bn-digit text-truncate d-block" style="color: var(--del-text); font-size: 12px;">{{ Auth::user()->phone ?? Auth::user()->email }}</small>
                        </div>
                        <span class="badge rounded-pill px-3 py-1 flex-shrink-0" style="background-color: rgba(37, 99, 235, 0.1); color: var(--del-primary); font-size: 11px; font-weight: 700;">Active</span>
                    </div>

                    <form action="{{ route('user.account.delete') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="delete-form-label">Confirm Your Account Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control delete-form-control" placeholder="Enter current password" required>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="confirm_checkbox" id="confirmCheckboxAuth" value="1" required style="cursor: pointer;">
                            <label class="form-check-label small" for="confirmCheckboxAuth" style="font-size: 12px; color: var(--del-text); cursor: pointer; user-select: none;">
                                I understand that my account will be deactivated and moved to trash for <span class="bn-digit">30</span> days before permanent deletion.
                            </label>
                        </div>

                        <div class="delete-action-btn-wrap">
                            <a href="{{ route('user.profile') }}" class="btn btn-cancel-account">
                                <i class="fas fa-arrow-left me-1"></i> <span>Cancel &amp; Keep Account</span>
                            </a>
                            <button type="submit" class="btn btn-delete-confirm">
                                <i class="fas fa-trash-alt me-1"></i> <span>Confirm &amp; Delete</span>
                            </button>
                        </div>
                    </form>

                @else
                    {{-- ================= 2. PUBLIC / UN-AUTHENTICATED VIEW ================= --}}
                    <form action="{{ route('user.account.delete') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="delete-form-label">Username or Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="username_or_phone" class="form-control delete-form-control bn-digit" value="{{ old('username_or_phone') }}" placeholder="Enter username or 017XXXXXXXX" required>
                        </div>

                        <div class="mb-3">
                            <label class="delete-form-label">Account Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control delete-form-control" value="{{ old('email') }}" placeholder="Enter registered email address" required>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="delete-form-label mb-0">Account Password <span class="text-danger">*</span></label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="small fw-semibold" style="font-size: 11.5px; color: var(--del-primary);">Forgot Password? Reset Here</a>
                                @endif
                            </div>
                            <input type="password" name="password" class="form-control delete-form-control" placeholder="Enter current password" required>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="confirm_checkbox" id="confirmCheckboxGuest" value="1" required style="cursor: pointer;">
                            <label class="form-check-label small" for="confirmCheckboxGuest" style="font-size: 12px; color: var(--del-text); cursor: pointer; user-select: none;">
                                I confirm that I want to deactivate my account and permanently delete my data after <span class="bn-digit">30</span> days.
                            </label>
                        </div>

                        <div class="delete-action-btn-wrap">
                            <a href="{{ route('login') }}" class="btn btn-cancel-account">
                                <i class="fas fa-sign-in-alt me-1"></i> <span>Sign In to Account</span>
                            </a>
                            <button type="submit" class="btn btn-delete-confirm">
                                <i class="fas fa-trash-alt me-1"></i> <span>Verify &amp; Delete Account</span>
                            </button>
                        </div>
                    </form>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection