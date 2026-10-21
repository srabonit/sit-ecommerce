@extends('layouts.dashboard')

@section('title', 'My Account - ' . config('app.name', 'Shop Now BD'))

@section('dashboard_content')
<style>
    .welcome-hero-card {
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        border-radius: 18px;
        color: #ffffff;
        padding: 30px;
        position: relative;
        overflow: hidden;
    }
    .stat-box-card {
        background: #ffffff;
        border: 1px solid #edf2f7;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 4px 15px -2px rgba(0, 0, 0, 0.03);
        display: flex;
        align-items: center;
        gap: 16px;
        transition: 0.2s;
    }
    .stat-box-card:hover {
        transform: translateY(-2px);
    }
    .stat-icon-circle {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    .recent-orders-card {
        background: #ffffff;
        border: 1px solid #edf2f7;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 15px -2px rgba(0, 0, 0, 0.03);
    }
    .quick-action-tile {
        background: #ffffff;
        border: 1px solid #edf2f7;
        border-radius: 14px;
        padding: 16px;
        text-decoration: none;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 600;
        font-size: 13.5px;
        transition: 0.2s;
    }
    .quick-action-tile:hover {
        border-color: #3b82f6;
        color: #2563eb;
        background: #f8fafc;
    }
</style>

<div class="welcome-hero-card shadow-sm mb-4">
    <div class="row align-items-center">
        <div class="col-md-8">
            <span class="badge bg-white text-primary rounded-pill px-3 py-1 mb-2 fw-bold small">Customer Account</span>
            <h3 class="fw-bold mb-1">Hello, {{ $user->name }}!</h3>
            <p class="mb-0 text-white-50 small">Track your packages, check order progress, and manage personal preferences all in one place.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="{{ route('shop') }}" class="btn btn-light text-primary rounded-pill px-4 py-2 fw-bold shadow-sm small">
                <i class="fas fa-shopping-bag me-1"></i> Start Shopping
            </a>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-box-card">
            <div class="stat-icon-circle bg-primary-subtle text-primary">
                <i class="fas fa-box-open"></i>
            </div>
            <div>
                <h4 class="fw-bold text-dark mb-0">{{ $activeOrders ?? 0 }}</h4>
                <span class="text-muted small">Active Orders</span>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-box-card">
            <div class="stat-icon-circle bg-warning-subtle text-warning">
                <i class="fas fa-coins"></i>
            </div>
            <div>
                <h4 class="fw-bold text-dark mb-0">{{ $loyaltyPoints ?? 0 }}</h4>
                <span class="text-muted small">Reward Points</span>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-box-card">
            <div class="stat-icon-circle bg-danger-subtle text-danger">
                <i class="fas fa-heart"></i>
            </div>
            <div>
                <h4 class="fw-bold text-dark mb-0">{{ $wishlistCount ?? 0 }}</h4>
                <span class="text-muted small">Wishlist Items</span>
            </div>
        </div>
    </div>
</div>

<div class="recent-orders-card mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-dark mb-0"><i class="fas fa-history text-primary me-2"></i> Recent Orders</h5>
        <a href="{{ route('user.orders') }}" class="text-primary text-decoration-none small fw-bold">View All Orders &rarr;</a>
    </div>

    @if(isset($recentOrders) && $recentOrders->count() > 0)
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th class="py-2">Invoice #</th>
                        <th class="py-2">Date</th>
                        <th class="py-2">Total Amount</th>
                        <th class="py-2 text-center">Status</th>
                        <th class="py-2 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentOrders as $ord)
                        @php
                            $badgeClass = match($ord->order_status ?? 'pending') {
                                'delivered' => 'bg-success',
                                'shipped' => 'bg-info text-dark',
                                'processing' => 'bg-primary',
                                'cancelled' => 'bg-danger',
                                default => 'bg-warning text-dark'
                            };
                        @endphp
                        <tr>
                            <td class="fw-bold text-primary small py-3">{{ $ord->invoice_no }}</td>
                            <td class="text-secondary small">{{ date('d M, Y', strtotime($ord->created_at)) }}</td>
                            <td class="fw-bold text-dark small">৳{{ number_format($ord->grand_total, 2) }}</td>
                            <td class="text-center">
                                <span class="badge {{ $badgeClass }} rounded-pill text-uppercase px-2 py-1" style="font-size: 10px;">
                                    {{ $ord->order_status ?? 'pending' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('user.orders.details', $ord->invoice_no) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 small fw-semibold">
                                    Details
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-4 text-muted small">
            <i class="fas fa-shopping-basket fa-2x mb-2 opacity-50"></i>
            <p class="mb-0">You have no recent orders placed yet.</p>
        </div>
    @endif
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="recent-orders-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0"><i class="fas fa-map-pin text-primary me-2"></i> Primary Shipping Address</h6>
                <a href="{{ route('user.addresses') }}" class="small text-primary text-decoration-none fw-semibold">Edit</a>
            </div>
            @php
                $sessionAddr = session()->get('user_shipping_address', []);
                $displayDistrict = $user->district ?? $sessionAddr['district'] ?? 'Dhaka';
                $displayAddress = $user->address ?? $sessionAddr['address'] ?? 'No delivery address saved yet.';
            @endphp
            <div class="p-3 bg-light rounded-3 small text-secondary">
                <strong class="text-dark d-block mb-1">{{ $user->name }}</strong>
                <div>{{ $user->phone ?? 'Phone number not provided' }}</div>
                <div>{{ $displayAddress }}</div>
                <div><strong>District:</strong> {{ $displayDistrict }}</div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="recent-orders-card h-100">
            <h6 class="fw-bold text-dark mb-3"><i class="fas fa-bolt text-primary me-2"></i> Quick Shortcuts</h6>
            <div class="row g-2">
                <div class="col-6">
                    <a href="{{ route('user.profile') }}" class="quick-action-tile">
                        <i class="far fa-user text-primary"></i> <span>Edit Profile</span>
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ route('user.coupons') }}" class="quick-action-tile">
                        <i class="fas fa-percent text-success"></i> <span>Vouchers</span>
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ route('user.track.order') }}" class="quick-action-tile">
                        <i class="fas fa-truck text-info"></i> <span>Track Order</span>
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ route('user.support') }}" class="quick-action-tile">
                        <i class="fas fa-life-ring text-warning"></i> <span>Help Desk</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection