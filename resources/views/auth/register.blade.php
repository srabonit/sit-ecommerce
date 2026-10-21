@extends('layouts.app')

@php
    $siteTitle = config('app.name', 'Shop Now BD');
    $storeLogo = DB::table('settings')->where('key', 'header_logo')->value('value') 
              ?? DB::table('settings')->where('key', 'site_icon')->value('value');

    $resolveAuthLogo = function($path) {
        if (empty($path)) return null;
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        $clean = ltrim(str_replace(['storage/', 'public/'], '', $path), '/');
        if (file_exists(public_path($clean))) return asset($clean);
        if (file_exists(public_path('uploads/' . $clean))) return asset('uploads/' . $clean);
        return asset($clean);
    };

    $logoUrl = $resolveAuthLogo($storeLogo);
@endphp

@section('title', 'Register - ' . $siteTitle)

@section('content')
<div class="py-5 bg-light d-flex align-items-center" style="min-height: 80vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    
                    <div class="text-center mb-4">
                        @if(!empty($logoUrl))
                            <a href="{{ route('home') }}" class="d-inline-block mb-3">
                                <img src="{{ $logoUrl }}" alt="{{ $siteTitle }}" style="max-height: 48px; max-width: 160px; object-fit: contain;">
                            </a>
                        @else
                            <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 48px; height: 48px;">
                                <i class="fas fa-user-plus fs-5"></i>
                            </div>
                        @endif
                        <h4 class="fw-bold text-dark mb-1">Create an Account</h4>
                        <p class="text-muted small">Register to track packages, save addresses & rewards</p>
                    </div>

                    <form action="{{ route('register') }}" method="POST" novalidate>
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control rounded-3 py-2 @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Enter full name" required>
                            @error('name')
                                <div class="invalid-feedback small fw-semibold">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control rounded-3 py-2 @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="017XXXXXXXX" required>
                            @error('phone')
                                <div class="invalid-feedback small fw-semibold">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Email Address (Optional)</label>
                            <input type="email" name="email" class="form-control rounded-3 py-2 @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="Enter your Email">
                            @error('email')
                                <div class="invalid-feedback small fw-semibold">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-2 mb-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control rounded-3 py-2 @error('password') is-invalid @enderror" placeholder="Min 6 characters" required>
                                @error('password')
                                    <div class="invalid-feedback small fw-semibold">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" class="form-control rounded-3 py-2" placeholder="Repeat password" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 rounded-3 fw-bold shadow-sm">
                            Create Account
                        </button>
                    </form>

                    <div class="text-center mt-4 pt-2 border-top">
                        <span class="small text-muted">Already have an account?</span>
                        <a href="{{ route('login') }}" class="small text-primary fw-bold text-decoration-none ms-1">Sign In</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection