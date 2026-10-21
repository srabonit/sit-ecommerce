@extends('layouts.admin')

@section('title', 'Edit Branch - ' . config('app.name'))

@section('admin_content')
<div class="container-fluid py-3 px-2 px-md-3">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <h5 class="fw-bold text-dark mb-0 text-truncate">Edit Branch: <span class="text-primary">{{ $branch->name }}</span></h5>
            <a href="{{ route('admin.branch.index') }}" class="btn btn-warning btn-sm fw-bold text-white px-3 align-self-start align-self-md-auto">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('admin.branch.update', $branch->id) }}" method="POST">
                @csrf

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">Branch Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control rounded-2" value="{{ old('name', $branch->name) }}" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">Branch Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control rounded-2" value="{{ old('code', $branch->code) }}" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">Branch Phone <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control rounded-2" value="{{ old('phone', $branch->phone) }}" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">Branch Email</label>
                        <input type="email" name="email" class="form-control rounded-2" value="{{ old('email', $branch->email) }}">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted">Branch Address <span class="text-danger">*</span></label>
                    <textarea name="address" rows="3" class="form-control rounded-2" required>{{ old('address', $branch->address) }}</textarea>
                </div>

                <button type="submit" class="btn btn-warning fw-bold text-white px-4">Update Branch</button>
            </form>
        </div>
    </div>
</div>
@endsection