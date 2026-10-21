@extends('layouts.app')

@section('title', 'Page Not Found - ' . config('app.name', 'Ecommerce by Srabon IT'))

@section('content')
<div class="container py-5 my-5">
    <div class="row justify-content-center text-center">
        <div class="col-lg-6 col-md-8">
            <div class="card border-0 shadow-sm rounded-4 p-5 bg-white">
                
                <!-- আইকন বা ইলস্ট্রেশন -->
                <div class="mb-4 text-primary">
                    <i class="fas fa-exclamation-triangle fa-4x opacity-75"></i>
                </div>

                <h1 class="display-4 fw-bold text-dark mb-2">404</h1>
                <h3 class="fw-bold text-secondary mb-3">Oops! Page Not Found</h3>
                
                <p class="text-muted mb-4 small">
                    The page you are looking for might have been removed, had its name changed, or is temporarily unavailable. Let's get you back on track!
                </p>

                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <a href="{{ route('home') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm small">
                        <i class="fas fa-home me-1"></i> Back to Home
                    </a>
                    <a href="{{ route('shop') }}" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold small">
                        <i class="fas fa-shopping-bag me-1"></i> Browse Shop
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection