@extends('layouts.app')

@section('title', 'Server Error - ' . config('app.name', 'Ecommerce by Srabon IT'))

@section('content')
<div class="container py-5 my-5">
    <div class="row justify-content-center text-center">
        <div class="col-lg-6 col-md-8">
            <div class="card border-0 shadow-sm rounded-4 p-5 bg-white">
                
                <!-- আইকন -->
                <div class="mb-4 text-danger">
                    <i class="fas fa-server fa-4x opacity-75"></i>
                </div>

                <h1 class="display-4 fw-bold text-dark mb-2">500</h1>
                <h3 class="fw-bold text-secondary mb-3">Internal Server Error</h3>
                
                <p class="text-muted mb-4 small">
                    We are experiencing some technical issues on our server right now. Our engineering team has been notified. Please try again shortly.
                </p>

                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <a href="{{ route('home') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm small">
                        <i class="fas fa-home me-1"></i> Return Home
                    </a>
                    <a href="javascript:location.reload();" class="btn btn-outline-dark rounded-pill px-4 py-2 fw-semibold small">
                        <i class="fas fa-redo me-1"></i> Try Again
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection