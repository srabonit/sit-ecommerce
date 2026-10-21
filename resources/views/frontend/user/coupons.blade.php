@extends('layouts.dashboard')

@section('title', 'Coupons & Vouchers - ' . config('app.name', 'Shop Now BD'))

@section('dashboard_content')
<div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <h5 class="fw-bold text-dark mb-1"><i class="fas fa-ticket-alt text-success me-2"></i> Coupons & Vouchers</h5>
    <p class="text-muted small mb-4">Available discount coupons for checkout savings</p>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="p-3 border border-success border-opacity-50 rounded-4 bg-success bg-opacity-10 position-relative">
                <span class="badge bg-success position-absolute top-0 end-0 m-3">Active</span>
                <h6 class="fw-bold text-success mb-1">WELCOME150</h6>
                <p class="small text-secondary mb-2">Get ৳150 off on orders over ৳1500</p>
                <small class="text-muted d-block">Valid until: 31 Dec 2026</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="p-3 border border-primary border-opacity-50 rounded-4 bg-primary bg-opacity-10 position-relative">
                <span class="badge bg-primary position-absolute top-0 end-0 m-3">Active</span>
                <h6 class="fw-bold text-primary mb-1">FREEDEL</h6>
                <p class="small text-secondary mb-2">Free Delivery nationwide on combo deals</p>
                <small class="text-muted d-block">Valid until: Ongoing</small>
            </div>
        </div>
    </div>
</div>
@endsection