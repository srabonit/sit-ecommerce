@extends('layouts.dashboard')

@section('title', 'Manage Addresses - ' . config('app.name', 'Shop Now BD'))

@section('dashboard_content')
<div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <h5 class="fw-bold text-dark mb-1"><i class="fas fa-map-marker-alt text-primary me-2"></i> Manage Addresses</h5>
    <p class="text-muted small mb-4">Set your default shipping address for faster checkout</p>

    @php
        $sessionAddress = session()->get('user_shipping_address', []);
        $currentDistrict = $user->district ?? $sessionAddress['district'] ?? 'Dhaka';
        $currentAddress = $user->address ?? $sessionAddress['address'] ?? '';
    @endphp

    <form action="{{ route('user.addresses.update') }}" method="POST">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label small fw-bold text-dark">District / Area <span class="text-danger">*</span></label>
                <input type="text" name="district" class="form-control rounded-3 py-2" value="{{ old('district', $currentDistrict) }}" placeholder=" Dhaka" required>
            </div>

            <div class="col-md-12">
                <label class="form-label small fw-bold text-dark">Full Delivery Address <span class="text-danger">*</span></label>
                <textarea name="address" class="form-control rounded-3 py-2" rows="3" placeholder="House, Road, Thana/Area" required>{{ old('address', $currentAddress) }}</textarea>
            </div>

            <div class="col-12 mt-4">
                <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                    Save Address
                </button>
            </div>
        </div>
    </form>
</div>
@endsection