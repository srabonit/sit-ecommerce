@extends('layouts.admin')

@section('title', 'FraudBD API Configuration')

@section('admin_content')
<div class="container-fluid px-2 px-md-4 py-2">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
        <div>
            <h5 class="fw-bold text-dark mb-0 fs-6 fs-md-5">Fraud Checker Configuration</h5>
            <span class="text-muted small">Verification Gateway</span>
        </div>
        <a href="{{ route('admin.order.fraud') }}" class="btn btn-sm btn-outline-secondary px-3 rounded-pill w-100 w-sm-auto text-center">
            <i class="fas fa-arrow-left me-1"></i> Back to Fraud Checker
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 small py-2" role="alert">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3 small py-2" role="alert">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-7 p-0">
            <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white">
                <form action="{{ route('admin.system.setting.fraud.update') }}" method="POST">
                    @csrf
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                        <span class="fw-bold text-dark small">FraudBD Gateway</span>
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" name="is_active" id="isActiveSwitch" value="1" {{ old('is_active', $setting->is_active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label small fw-semibold ms-1" for="isActiveSwitch">Enabled</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Registered Email</label>
                        <input type="email" name="api_email" class="form-control form-control-sm rounded-3 @error('api_email') is-invalid @enderror" value="{{ old('api_email', $setting->api_email ?? '') }}" placeholder="Email address" required>
                        @error('api_email')
                            <div class="invalid-feedback small fw-semibold">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Active API Key</label>
                        <input type="text" name="api_key" class="form-control form-control-sm rounded-3 font-monospace @error('api_key') is-invalid @enderror" value="{{ old('api_key', $setting->api_key ?? '') }}" placeholder="API key" required>
                        @error('api_key')
                            <div class="invalid-feedback small fw-semibold">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">API Endpoint URL</label>
                        <input type="text" name="api_url" class="form-control form-control-sm rounded-3 font-monospace @error('api_url') is-invalid @enderror" value="{{ old('api_url', $setting->api_url ?? 'https://fraudbd.com/api/check-courier-info') }}" placeholder="Endpoint URL" required>
                        @error('api_url')
                            <div class="invalid-feedback small fw-semibold">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary px-4 fw-bold rounded-pill w-100 w-md-auto py-2">Save Configuration</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection