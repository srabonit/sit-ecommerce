@extends('layouts.app')

@php
    $siteTitle = config('app.name', 'Shop Now BD');
    $displayTargetPhone = $maskedPhone ?? ($formattedPhone ?? ($phone ?? ''));
    $phoneValue = $formattedPhone ?? ($phone ?? '');
@endphp

@section('title', 'Set New Password - ' . $siteTitle)

@section('content')
<div class="py-5 bg-light d-flex align-items-center" style="min-height: 75vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <div class="text-center mb-4">
                        <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2 shadow-xs" style="width: 48px; height: 48px;">
                            <i class="fas fa-lock fs-5"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-1">Set New Password</h4>
                        <p class="text-muted small mb-0">For verified account: <strong class="text-dark font-monospace">{{ $displayTargetPhone }}</strong></p>
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger py-2 small rounded-3 mb-3 border-0">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form action="{{ route('password.phone.update') }}" method="POST" novalidate>
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <input type="hidden" name="phone" value="{{ $phoneValue }}">

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">New Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control rounded-3 py-2 @error('password') is-invalid @enderror" placeholder="Min 6 characters" required autofocus>
                            @error('password')
                                <div class="invalid-feedback small fw-semibold">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-dark">Confirm New Password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control rounded-3 py-2" placeholder="Repeat new password" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 rounded-3 fw-bold shadow-sm">
                            Update Password &amp; Sign In
                        </button>
                    </form>

                    <div class="text-center mt-4 pt-2 border-top">
                        <a href="{{ route('login') }}" class="small text-secondary text-decoration-none">
                            <i class="fas fa-arrow-left me-1"></i> Cancel &amp; Return to Sign In
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection