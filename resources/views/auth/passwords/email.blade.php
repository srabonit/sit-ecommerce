@extends('layouts.app')

@php
    $siteTitle = config('app.name', 'Shop Now BD');

    // Central Settings Resolver
    if (!isset($settings)) {
        $authSettingService = app(\App\Services\Auth\AuthenticationSettingsService::class);
        $settings = $authSettingService->all();
    }
    $isPhoneResetActive = $isPhoneResetActive ?? (!empty($settings['login_phone_password']) || !empty($settings['login_phone_otp']));
@endphp

@section('title', 'Forgot Password - ' . $siteTitle)

@section('content')
<style>
    .auth-tab-btn {
        flex: 1;
        border: none;
        border-radius: 6px;
        padding: 7px 10px;
        font-size: 12.5px;
        font-weight: 600;
        color: #64748b;
        background: transparent;
        transition: all 0.2s ease;
    }
    .auth-tab-btn.active {
        background: #ffffff;
        color: #0f172a !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }
</style>

<div class="py-5 bg-light d-flex align-items-center" style="min-height: 75vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5 col-xl-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <div class="text-center mb-3">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2 shadow-xs" style="width: 48px; height: 48px;">
                            <i class="fas fa-key fs-5"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-1">Forgot Password</h4>
                        <p class="text-muted small mb-0">Recover your account securely via Email or Mobile SMS Link</p>
                    </div>

                    {{-- Status Alerts --}}
                    @if(session('status'))
                        <div class="alert alert-success py-2 small rounded-3 mb-3 border-0">
                            <i class="fas fa-check-circle me-1"></i> {{ session('status') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger py-2 small rounded-3 mb-3 border-0">
                            <i class="fas fa-exclamation-triangle me-1"></i> {{ session('error') }}
                        </div>
                    @endif

                    {{-- Tab Switcher (Visible if Phone Reset is active) --}}
                    @if($isPhoneResetActive)
                        <ul class="nav p-1 bg-light rounded-3 mb-3 border" role="tablist">
                            <li class="nav-item flex-fill">
                                <button class="nav-link auth-tab-btn w-100 active" data-bs-toggle="pill" data-bs-target="#tabEmailReset" type="button" role="tab">
                                    <i class="fas fa-envelope me-1"></i> Reset via Email
                                </button>
                            </li>
                            <li class="nav-item flex-fill">
                                <button class="nav-link auth-tab-btn w-100" data-bs-toggle="pill" data-bs-target="#tabPhoneReset" type="button" role="tab">
                                    <i class="fas fa-sms me-1"></i> Reset via Phone
                                </button>
                            </li>
                        </ul>
                    @endif

                    <div class="tab-content">
                        {{-- 1. Email Reset Tab --}}
                        <div class="tab-pane fade show active" id="tabEmailReset" role="tabpanel">
                            <form action="{{ route('password.email') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-dark">Email Address</label>
                                    <input type="email" name="email" class="form-control rounded-3 py-2 @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="Enter Email Address" required autofocus>
                                    @error('email')
                                        <div class="invalid-feedback small fw-semibold">{{ $message }}</div>
                                    @enderror
                                    <span class="form-text small text-muted" style="font-size: 11px;">We will email you a secure password reset link.</span>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-2 rounded-3 fw-bold shadow-sm">
                                    <i class="fas fa-paper-plane me-1"></i> Send Reset Link
                                </button>
                            </form>
                        </div>

                        {{-- 2. Phone Number Reset Link Tab --}}
                        @if($isPhoneResetActive)
                            <div class="tab-pane fade" id="tabPhoneReset" role="tabpanel">
                                <div id="phoneResetAlertBox" class="d-none alert py-2 small rounded-3 mb-3 border-0"></div>

                                <form id="phoneResetForm" action="{{ route('password.phone.send_otp') }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-dark">Registered Phone Number</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light text-muted small border-end-0" style="border-radius: 8px 0 0 8px;">+880</span>
                                            <input type="tel" name="phone" id="resetPhoneInput" class="form-control rounded-end-3 py-2 font-monospace" placeholder="17XXXXXXXX" maxlength="11" required>
                                        </div>
                                        <span class="form-text small text-muted" id="phoneResetHelpText" style="font-size: 11px;">We will send a direct password reset link to your mobile via SMS.</span>
                                    </div>

                                    <button type="button" id="btnSendPhoneResetLink" class="btn btn-primary w-100 py-2 rounded-3 fw-bold shadow-sm">
                                        <i class="fas fa-paper-plane me-1"></i> Send Reset Link
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>

                    <div class="text-center mt-4 pt-2 border-top">
                        <a href="{{ route('login') }}" class="small text-secondary text-decoration-none">
                            <i class="fas fa-arrow-left me-1"></i> Back to Sign In
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Interactive Phone SMS Reset Link Client Engine --}}
@if($isPhoneResetActive)
<script>
(function() {
    function initPhoneResetClient() {
        const btnSendLink  = document.getElementById('btnSendPhoneResetLink');
        const phoneInput   = document.getElementById('resetPhoneInput');
        const alertBox     = document.getElementById('phoneResetAlertBox');
        let timerInterval  = null;

        function showAlert(msg, isSuccess = false) {
            if (!alertBox) return;
            alertBox.innerHTML = (isSuccess ? '<i class="fas fa-check-circle me-1"></i> ' : '<i class="fas fa-exclamation-circle me-1"></i> ') + msg;
            alertBox.className = 'alert py-2 small rounded-3 mb-3 border-0 ' + (isSuccess ? 'alert-success' : 'alert-danger');
            alertBox.classList.remove('d-none');
        }

        function startCountdown(seconds) {
            clearInterval(timerInterval);
            let rem = seconds;

            if (btnSendLink) {
                btnSendLink.disabled = true;
                btnSendLink.classList.add('disabled');
            }

            timerInterval = setInterval(function() {
                if (rem <= 0) {
                    clearInterval(timerInterval);
                    if (btnSendLink) {
                        btnSendLink.disabled = false;
                        btnSendLink.classList.remove('disabled');
                        btnSendLink.innerHTML = '<i class="fas fa-redo-alt me-1"></i> Resend Reset Link';
                    }
                    return;
                }
                const m = Math.floor(rem / 60);
                const s = rem % 60;
                if (btnSendLink) {
                    btnSendLink.innerHTML = `Resend in ${m < 10 ? '0' : ''}${m}:${s < 10 ? '0' : ''}${s}`;
                }
                rem--;
            }, 1000);
        }

        if (btnSendLink && phoneInput) {
            btnSendLink.addEventListener('click', function() {
                const phone = phoneInput.value.trim();
                if (!phone || phone.length < 10) {
                    showAlert('Please enter your 11-digit registered mobile number.');
                    phoneInput.focus();
                    return;
                }

                btnSendLink.disabled = true;
                btnSendLink.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending Link...';

                fetch('{{ route("password.phone.send_otp") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ phone: phone })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, true);
                        startCountdown(data.cooldown_seconds || 60);
                    } else {
                        showAlert(data.message || 'Failed to send reset link.');
                        btnSendLink.disabled = false;
                        btnSendLink.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Send Reset Link';
                    }
                })
                .catch(() => {
                    showAlert('Network error occurred. Please try again.');
                    btnSendLink.disabled = false;
                    btnSendLink.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Send Reset Link';
                });
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPhoneResetClient);
    } else {
        initPhoneResetClient();
    }
})();
</script>
@endif
@endsection