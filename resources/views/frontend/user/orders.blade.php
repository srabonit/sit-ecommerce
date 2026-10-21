@extends('layouts.dashboard')

@section('title', 'My Orders - ' . config('app.name', 'Shop Now BD'))

@section('dashboard_content')
<div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h5 class="fw-bold text-dark mb-1"><i class="fas fa-box text-primary me-2"></i> My Orders</h5>
            <p class="text-muted small mb-0">Check status, track packages, and review purchase history</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('user.orders') }}" class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-light border' }} rounded-pill px-3">All</a>
            <a href="{{ route('user.orders', ['status' => 'pending']) }}" class="btn btn-sm {{ request('status') === 'pending' ? 'btn-primary' : 'btn-light border' }} rounded-pill px-3">Pending</a>
            <a href="{{ route('user.orders', ['status' => 'processing']) }}" class="btn btn-sm {{ request('status') === 'processing' ? 'btn-primary' : 'btn-light border' }} rounded-pill px-3">Processing</a>
            <a href="{{ route('user.orders', ['status' => 'delivered']) }}" class="btn btn-sm {{ request('status') === 'delivered' ? 'btn-primary' : 'btn-light border' }} rounded-pill px-3">Delivered</a>
        </div>
    </div>

    @if(isset($orders) && $orders->count() > 0)
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th class="py-2">Invoice #</th>
                        <th class="py-2">Date</th>
                        <th class="py-2">Payment</th>
                        <th class="py-2">Total Amount</th>
                        <th class="py-2 text-center">Status</th>
                        <th class="py-2 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $ord)
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
                            <td class="small text-dark text-capitalize">{{ $ord->payment_method ?? 'Cash on Delivery' }}</td>
                            <td class="fw-bold text-dark small">৳{{ number_format($ord->grand_total, 2) }}</td>
                            <td class="text-center">
                                <span class="badge {{ $badgeClass }} rounded-pill text-uppercase px-2 py-1" style="font-size: 10px;">
                                    {{ $ord->order_status ?? 'pending' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('user.orders.details', $ord->invoice_no) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 small fw-semibold">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    @else
        <div class="text-center py-5 text-muted">
            <i class="fas fa-shopping-bag fa-3x mb-3 opacity-25"></i>
            <h6>No orders found</h6>
            <p class="small text-secondary mb-3">You have not placed any orders under this filter yet.</p>
            <a href="{{ route('shop') }}" class="btn btn-primary rounded-pill px-4 btn-sm fw-bold">Shop Now</a>
        </div>
    @endif
</div>
@endsection