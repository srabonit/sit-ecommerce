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

@section('title', 'Sign In - ' . $siteTitle)

@section('content')
<div class="py-5 bg-light d-flex align-items-center" style="min-height: 75vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    
                    <div class="text-center mb-4">
                        @if(!empty($logoUrl))
                            <a href="{{ route('home') }}" class="d-inline-block mb-3">
                                <img src="{{ $logoUrl }}" alt="{{ $siteTitle }}" style="max-height: 48px; max-width: 160px; object-fit: contain;">
                            </a>
                        @else
                            <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 48px; height: 48px;">
                                <i class="fas fa-lock fs-5"></i>
                            </div>
                        @endif
                        <h4 class="fw-bold text-dark mb-1">Welcome Back</h4>
                        <p class="text-muted small">Sign in to manage your orders & account</p>
                    </div>

                    @if(session('status'))
                        <div class="alert alert-success py-2 small rounded-3 mb-3">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success py-2 small rounded-3 mb-3">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger py-2 small rounded-3 mb-3">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('login') }}" method="POST" novalidate>
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Email or Phone Number</label>
                            <input type="text" name="login" class="form-control rounded-3 py-2 @error('login') is-invalid @enderror" value="{{ old('login') }}" placeholder="Enter Email or Phone Number" required autofocus>
                            @error('login')
                                <div class="invalid-feedback small fw-semibold">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold text-dark mb-0">Password</label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="small text-primary text-decoration-none">Forgot Password?</a>
                                @endif
                            </div>
                            <input type="password" name="password" class="form-control rounded-3 py-2 @error('password') is-invalid @enderror" placeholder="••••••••" required>
                            @error('password')
                                <div class="invalid-feedback small fw-semibold">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
                            <label class="form-check-label small text-secondary" for="rememberMe">
                                Keep me signed in
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 rounded-3 fw-bold shadow-sm">
                            Sign In
                        </button>
                    </form>

                    <div class="text-center mt-4 pt-2 border-top">
                        <span class="small text-muted">Don't have an account?</span>
                        <a href="{{ route('register') }}" class="small text-primary fw-bold text-decoration-none ms-1">Register Now</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection