@extends('layouts.dashboard')

@section('title', 'Personal Info - ' . config('app.name', 'Shop Now BD'))

@section('dashboard_content')
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <h5 class="fw-bold text-dark mb-1"><i class="far fa-user text-primary me-2"></i> Personal Information</h5>
            <p class="text-muted small mb-4">Manage your basic profile details</p>

            <form action="{{ route('user.profile.update') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Full Name</label>
                    <input type="text" name="name" class="form-control rounded-3 py-2" value="{{ old('name', $user->name) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Phone Number</label>
                    <input type="text" name="phone" class="form-control rounded-3 py-2" value="{{ old('phone', $user->phone) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Email Address</label>
                    <input type="email" name="email" class="form-control rounded-3 py-2" value="{{ old('email', $user->email) }}" required>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-dark">Gender (Optional)</label>
                    <select name="gender" class="form-select rounded-3 py-2">
                        <option value="">Select Gender</option>
                        <option value="male" {{ (old('gender', $user->gender ?? '') === 'male') ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ (old('gender', $user->gender ?? '') === 'female') ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ (old('gender', $user->gender ?? '') === 'other') ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                    Save Changes
                </button>
            </form>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
            <h5 class="fw-bold text-dark mb-1"><i class="fas fa-shield-alt text-danger me-2"></i> Account Security</h5>
            <p class="text-muted small mb-4">Update your password</p>

            <form action="{{ route('user.password.update') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Current Password</label>
                    <input type="password" name="current_password" class="form-control rounded-3 py-2" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">New Password</label>
                    <input type="password" name="new_password" class="form-control rounded-3 py-2" placeholder="Min 6 characters" required>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-dark">Confirm New Password</label>
                    <input type="password" name="new_password_confirmation" class="form-control rounded-3 py-2" required>
                </div>

                <button type="submit" class="btn btn-outline-danger rounded-pill px-4 py-2 fw-bold">
                    Update Password
                </button>
            </form>
        </div>

        {{-- Play Store Compliant Specific Delete Page Link --}}
        @if(!in_array(strtolower($user->role), ['admin', 'superadmin', 'manager', 'staff']))
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white border-start border-danger border-4">
            <h6 class="fw-bold text-danger mb-1"><i class="fas fa-trash-alt me-2"></i> Delete Account</h6>
            <p class="text-muted small mb-3">
                Request to delete your account. You can reactivate within 30 days. After 30 days, your data is permanently removed.
            </p>
            <a href="{{ route('user.account.delete.page') }}" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1.5 fw-bold text-decoration-none d-inline-block">
                Go to Delete Account Page &rarr;
            </a>
        </div>
        @endif
    </div>
</div>
@endsection