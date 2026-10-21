@extends('layouts.app')

@php
    $siteTitle = config('app.name', 'Shop Now BD');
@endphp

@section('title', 'Reset Password - ' . $siteTitle)

@section('content')
<div class="py-5 bg-light d-flex align-items-center" style="min-height: 75vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <div class="text-center mb-4">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 48px; height: 48px;">
                            <i class="fas fa-key fs-5"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-1">Forgot Password</h4>
                        <p class="text-muted small">Enter your email and we will send a reset link</p>
                    </div>

                    @if(session('status'))
                        <div class="alert alert-success py-2 small rounded-3 mb-3">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form action="{{ route('password.email') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Email Address</label>
                            <input type="email" name="email" class="form-control rounded-3 py-2 @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="Enter Email Address" required autofocus>
                            @error('email')
                                <div class="invalid-feedback small fw-semibold">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 rounded-3 fw-bold shadow-sm">
                            Send Reset Link
                        </button>
                    </form>

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
@endsection