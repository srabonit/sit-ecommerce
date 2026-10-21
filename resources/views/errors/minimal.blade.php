@extends('layouts.app')

@section('title', ($exception->getStatusCode() ?? 'Error') . ' - ' . config('app.name', 'Ecommerce by Srabon IT'))

@section('content')
<div class="container py-5 my-5">
    <div class="row justify-content-center text-center">
        <div class="col-lg-6 col-md-8">
            <div class="card border-0 shadow-sm rounded-4 p-5 bg-white">
                
                <div class="mb-4 text-warning">
                    <i class="fas fa-ban fa-4x opacity-75"></i>
                </div>

                <h1 class="display-4 fw-bold text-dark mb-2">{{ $exception->getStatusCode() ?? 'Error' }}</h1>
                <h3 class="fw-bold text-secondary mb-3">{{ $exception->getMessage() ?? 'An unexpected error occurred.' }}</h3>
                
                <p class="text-muted mb-4 small">
                    Something went wrong while processing your request. Please go back to the homepage.
                </p>

                <div>
                    <a href="{{ route('home') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm small">
                        <i class="fas fa-home me-1"></i> Back to Home
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection