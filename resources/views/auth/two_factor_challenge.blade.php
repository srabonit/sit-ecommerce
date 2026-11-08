@extends('layouts.app')

@php
    $siteTitle = config('app.name', 'Shop Now BD');

    $channelText = match($channel ?? 'sms') {
        'both'  => 'mobile phone and email inbox',
        'email' => 'email address',
        default => 'mobile phone number',
    };

    $channelIcon = match($channel ?? 'sms') {
        'both'  => 'fas fa-shield-halved',
        'email' => 'fas fa-envelope-open-text',
        default => 'fas fa-mobile-screen-button',
    };

    // Smart Back URL based on login origin (Admin dedicated login vs Storefront login)
    $backLoginUrl = (session()->get('auth_2fa_source') === 'admin' && Route::has('admin.login'))
        ? route('admin.login')
        : route('login');
@endphp

@section('title', 'Two-Factor Authentication - ' . $siteTitle)

@section('content')
<div class="py-5 bg-light d-flex align-items-center" style="min-height: 75vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-6 col-lg-5 col-xl-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    
                    {{-- Header & Security Icon --}}
                    <div class="text-center mb-3">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2 shadow-xs" style="width: 52px; height: 52px;">
                            <i class="{{ $channelIcon }} fs-4"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-1">Security Verification</h4>
                        <p class="text-muted small mb-0" style="font-size: 12.5px; line-height: 1.5;">
                            A 6-digit security code has been dispatched to your <strong>{{ $channelText }}</strong>:
                            <span class="text-dark font-monospace fw-bold d-block mt-1" style="font-size: 13px;">{{ $maskedTarget }}</span>
                        </p>
                    </div>

                    {{-- Flash Info / Success --}}
                    @if(session('info'))
                        <div class="alert alert-info py-2 small rounded-3 mb-3 border-0 shadow-xs" style="font-size: 12px;">
                            <i class="fas fa-info-circle me-1"></i> {{ session('info') }}
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success py-2 small rounded-3 mb-3 border-0 shadow-xs" style="font-size: 12px;">
                            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                        </div>
                    @endif

                    {{-- Backend Error Alert --}}
                    @if($errors->any())
                        <div class="alert alert-danger py-2 small rounded-3 mb-3 border-0 shadow-xs" style="font-size: 12px;">
                            @foreach ($errors->all() as $error)
                                <div><i class="fas fa-exclamation-circle me-1"></i> {{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Real-time AJAX Alert Box --}}
                    <div id="twoFaAlertBox" class="alert py-2 small rounded-3 mb-3 border-0 shadow-xs d-none" style="font-size: 12px;"></div>

                    <form action="{{ route('auth.2fa.verify') }}" method="POST" id="twoFaForm" novalidate>
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark text-center d-block mb-1.5">
                                Enter 6-Digit Verification Code
                            </label>
                            <input type="text" 
                                   name="code" 
                                   id="twoFaCodeInput" 
                                   class="form-control text-center py-2.5 font-monospace fw-bold @error('code') is-invalid @enderror" 
                                   placeholder="000000" 
                                   maxlength="6" 
                                   inputmode="numeric"
                                   pattern="[0-9]*"
                                   style="letter-spacing: 8px; font-size: 22px; border-radius: 10px;" 
                                   required 
                                   autofocus>
                        </div>

                        {{-- Timer & Resend Button --}}
                        <div class="d-flex justify-content-between align-items-center mb-4 small" style="font-size: 12px;">
                            <span class="text-muted">Resend in: <strong id="twoFaCountdown" class="text-dark">01:00</strong></span>
                            <button type="button" id="btnResendTwoFa" class="btn btn-link p-0 small text-decoration-none disabled" style="font-size: 12px; font-weight: 600;" disabled>
                                Resend Code
                            </button>
                        </div>

                        {{-- Submit Button --}}
                        <button type="submit" id="btnSubmitTwoFa" class="btn btn-primary w-100 py-2.5 rounded-3 fw-bold shadow-sm" style="font-size: 13.5px;">
                            <i class="fas fa-shield-alt me-1"></i> Verify &amp; Continue
                        </button>
                    </form>

                    {{-- Smart Cancel & Back to Login --}}
                    <div class="text-center mt-4 pt-2 border-top">
                        <a href="{{ $backLoginUrl }}" class="small text-secondary text-decoration-none" style="font-size: 12px;">
                            <i class="fas fa-arrow-left me-1"></i> Cancel &amp; Back to Login
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const btnResend    = document.getElementById('btnResendTwoFa');
    const countdownEl  = document.getElementById('twoFaCountdown');
    const alertBox     = document.getElementById('twoFaAlertBox');
    const codeInput    = document.getElementById('twoFaCodeInput');
    const submitBtn    = document.getElementById('btnSubmitTwoFa');
    const twoFaForm    = document.getElementById('twoFaForm');
    let timerInterval  = null;

    function showAlert(msg, isSuccess = false) {
        if (!alertBox) return;
        alertBox.innerHTML = (isSuccess ? '<i class="fas fa-check-circle me-1"></i> ' : '<i class="fas fa-exclamation-circle me-1"></i> ') + msg;
        alertBox.className = 'alert py-2 small rounded-3 mb-3 border-0 shadow-xs ' + (isSuccess ? 'alert-success' : 'alert-danger');
        alertBox.classList.remove('d-none');
    }

    // Only allow numeric input
    if (codeInput) {
        codeInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }

    function startCountdown(seconds) {
        clearInterval(timerInterval);
        let rem = seconds;

        if (btnResend) {
            btnResend.disabled = true;
            btnResend.classList.add('disabled');
            btnResend.innerHTML = 'Resend Code';
        }

        timerInterval = setInterval(() => {
            if (rem <= 0) {
                clearInterval(timerInterval);
                if (countdownEl) countdownEl.textContent = '00:00';
                if (btnResend) {
                    btnResend.disabled = false;
                    btnResend.classList.remove('disabled');
                    btnResend.innerHTML = '<i class="fas fa-redo-alt me-1"></i> Resend Code';
                }
                return;
            }
            const m = Math.floor(rem / 60);
            const s = rem % 60;
            if (countdownEl) {
                countdownEl.textContent = `${m < 10 ? '0' : ''}${m}:${s < 10 ? '0' : ''}${s}`;
            }
            rem--;
        }, 1000);
    }

    if (btnResend) {
        btnResend.addEventListener('click', function() {
            btnResend.disabled = true;
            btnResend.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending...';

            fetch('{{ route("auth.2fa.resend") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, true);
                    startCountdown(data.cooldown_seconds || 60);
                    if (codeInput) {
                        codeInput.value = '';
                        codeInput.focus();
                    }
                } else {
                    showAlert(data.message || 'Failed to resend code.');
                    btnResend.disabled = false;
                    btnResend.classList.remove('disabled');
                    btnResend.innerHTML = 'Resend Code';
                }
            })
            .catch(() => {
                showAlert('Network error occurred. Please try again.');
                btnResend.disabled = false;
                btnResend.classList.remove('disabled');
                btnResend.innerHTML = 'Resend Code';
            });
        });
    }

    if (twoFaForm && submitBtn) {
        twoFaForm.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Verifying...';
        });
    }

    startCountdown(60);
})();
</script>
@endsection