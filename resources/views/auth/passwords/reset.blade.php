@extends('layouts.app')

@section('title', 'Set New Password - ' . config('app.name', 'Shop Now BD'))

@section('content')
<div class="py-5 bg-light d-flex align-items-center" style="min-height: 75vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <div class="text-center mb-4">
                        <h4 class="fw-bold text-dark mb-1">Create New Password</h4>
                        <p class="text-muted small">Choose a secure password for your account</p>
                    </div>

                    <form action="{{ route('password.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Email Address</label>
                            <input type="email" name="email" class="form-control rounded-3 py-2 @error('email') is-invalid @enderror" value="{{ $email ?? old('email') }}" required readonly>
                            @error('email')
                                <div class="invalid-feedback small fw-semibold">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">New Password</label>
                            <input type="password" name="password" class="form-control rounded-3 py-2 @error('password') is-invalid @enderror" placeholder="Min 6 characters" required autofocus>
                            @error('password')
                                <div class="invalid-feedback small fw-semibold">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-dark">Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="form-control rounded-3 py-2" placeholder="Repeat password" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 rounded-3 fw-bold shadow-sm">
                            Reset Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection