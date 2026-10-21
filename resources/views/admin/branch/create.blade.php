@extends('layouts.admin')

@section('title', 'Add Branch - ' . config('app.name'))

@section('admin_content')
<div class="container-fluid py-3 px-2 px-md-3">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold text-dark mb-0">Add Branch</h5>
            <a href="{{ route('admin.branch.index') }}" class="btn btn-warning btn-sm fw-bold text-white px-3">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('admin.branch.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Branch Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control rounded-2" placeholder=" Dhanmondi Outlet" value="{{ old('name') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Branch Email</label>
                    <input type="email" name="email" class="form-control rounded-2" placeholder=" dhanmondi@domain.com" value="{{ old('email') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Branch Phone <span class="text-danger">*</span></label>
                    <input type="text" name="phone" class="form-control rounded-2" placeholder=" 01XXXXXXXXX" value="{{ old('phone') }}" required>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted">Branch Address <span class="text-danger">*</span></label>
                    <textarea name="address" rows="3" class="form-control rounded-2" placeholder="Detailed street address, floor, city..." required>{{ old('address') }}</textarea>
                </div>

                <button type="submit" class="btn btn-warning fw-bold text-white px-4">Add Branch</button>
            </form>
        </div>
    </div>
</div>
@endsection