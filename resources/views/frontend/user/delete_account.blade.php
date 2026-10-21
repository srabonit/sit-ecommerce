@extends(Auth::check() ? 'layouts.dashboard' : 'layouts.app')

@section('title', 'Delete Account - ' . config('app.name', 'Shop Now BD'))

@php
    $activeSection = Auth::check() ? 'dashboard_content' : 'content';
@endphp

@section($activeSection)
<style>
    .delete-account-card {
        background: #ffffff;
        border: 1px solid #edf2f7;
        border-radius: 18px;
        padding: 24px;
        box-shadow: 0 4px 15px -2px rgba(0, 0, 0, 0.03);
    }
    @media (min-width: 768px) {
        .delete-account-card {
            padding: 36px 40px;
        }
    }
    .delete-avatar-circle {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background-color: #fee2e2;
        color: #ef4444;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 14px;
    }
    .delete-notice-box {
        background: #fffbeb;
        border: 1px solid #fef3c7;
        border-left: 4px solid #f59e0b;
        border-radius: 12px;
        padding: 14px 16px;
    }
    .delete-action-btn-wrap {
        display: flex;
        flex-direction: column-reverse;
        gap: 10px;
        width: 100%;
        padding-top: 14px;
        border-top: 1px solid #f1f5f9;
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
</style>

<div class="{{ Auth::check() ? '' : 'container py-5' }}">
    <div class="row justify-content-center">
        <div class="col-12 {{ Auth::check() ? 'col-xl-10' : 'col-md-8 col-lg-7' }}">
            <div class="delete-account-card">
                
                {{-- Header --}}
                <div class="text-center mb-3 mb-md-4">
                    <div class="delete-avatar-circle">
                        <i class="fas fa-user-slash fs-3"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-1 fs-5 fs-md-4">Account Deletion</h4>
                    <p class="text-muted small mb-0"><strong>Shop Now BD</strong> - Request to deactivate and permanently delete your account</p>
                </div>

                {{-- Status Alerts --}}
                @if(session('success'))
                    <div class="alert alert-success border-0 shadow-sm rounded-3 mb-3 py-2.5">
                        <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-3 py-2.5">
                        <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger border-0 rounded-3 small mb-3 py-2.5">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Play Store Policy Box --}}
                <div class="delete-notice-box mb-3 mb-md-4">
                    <h6 class="fw-bold text-dark mb-1 small d-flex align-items-center gap-1.5">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i> 
                        <span>Account deletion notice:</span>
                    </h6>
                    <ul class="mb-0 ps-3 text-secondary mt-2 small" style="font-size: 12px; line-height: 1.65;">
                        <li>Your account will be immediately logged out and moved to the <strong>Trash</strong>.</li>
                        <li>You have a <strong>30-day grace period</strong>. If you log in again within 30 days, your account will be instantly reactivated.</li>
                        <li>After 30 days of inactivity in trash, all your personal data (name, phone, saved addresses, wishlist) will be <strong>permanently deleted</strong>.</li>
                        <li>Once 30 days pass and the account is permanently deleted, you or anyone else can create a new fresh account using the same phone number or email.</li>
                    </ul>
                </div>

                @if(Auth::check())
                    {{-- ================= 1. SIGNED IN USER VIEW ================= --}}
                    <div class="p-3 bg-light rounded-3 mb-3 small d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted d-block" style="font-size: 11px;">Target Account</span>
                            <strong class="text-dark">{{ Auth::user()->name }}</strong> 
                            <span class="text-muted">({{ Auth::user()->phone ?? Auth::user()->email }})</span>
                        </div>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1">Active</span>
                    </div>

                    <form action="{{ route('user.account.delete') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Confirm Your Account Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control form-control-sm rounded-3 py-2 px-3" placeholder="Enter current password" required>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="confirm_checkbox" id="confirmCheckboxAuth" value="1" required style="cursor: pointer;">
                            <label class="form-check-label text-secondary small" for="confirmCheckboxAuth" style="font-size: 12px; cursor: pointer; user-select: none;">
                                I understand that my account will be deactivated and moved to trash for 30 days before permanent deletion.
                            </label>
                        </div>

                        <div class="delete-action-btn-wrap">
                            <a href="{{ route('user.profile') }}" class="btn btn-light border rounded-pill px-4 py-2 fw-semibold text-secondary w-100 text-center small">
                                <i class="fas fa-arrow-left me-1"></i> Cancel & Keep Account
                            </a>
                            <button type="submit" class="btn btn-danger rounded-pill px-4 py-2 fw-bold shadow-sm w-100 text-center small">
                                <i class="fas fa-trash-alt me-1"></i> Confirm & Delete
                            </button>
                        </div>
                    </form>

                @else
                    {{-- ================= 2. PUBLIC / UN-AUTHENTICATED VIEW ================= --}}
                    <form action="{{ route('user.account.delete') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Username or Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="username_or_phone" class="form-control form-control-sm rounded-3 py-2 px-3" value="{{ old('username_or_phone') }}" placeholder="Enter username or 017XXXXXXXX" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Account Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control form-control-sm rounded-3 py-2 px-3" value="{{ old('email') }}" placeholder="Enter registered email address" required>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold text-dark mb-0">Account Password <span class="text-danger">*</span></label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="small text-primary text-decoration-none" style="font-size: 11.5px;">Forgot Password? Reset Here</a>
                                @endif
                            </div>
                            <input type="password" name="password" class="form-control form-control-sm rounded-3 py-2 px-3" placeholder="Enter current password" required>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="confirm_checkbox" id="confirmCheckboxGuest" value="1" required style="cursor: pointer;">
                            <label class="form-check-label text-secondary small" for="confirmCheckboxGuest" style="font-size: 12px; cursor: pointer; user-select: none;">
                                I confirm that I want to deactivate my account and permanently delete my data after 30 days.
                            </label>
                        </div>

                        <div class="delete-action-btn-wrap">
                            <a href="{{ route('login') }}" class="btn btn-light border rounded-pill px-4 py-2 fw-semibold text-secondary w-100 text-center small">
                                <i class="fas fa-sign-in-alt me-1"></i> Sign In to Account
                            </a>
                            <button type="submit" class="btn btn-danger rounded-pill px-4 py-2 fw-bold shadow-sm w-100 text-center small">
                                <i class="fas fa-trash-alt me-1"></i> Verify & Delete Account
                            </button>
                        </div>
                    </form>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection