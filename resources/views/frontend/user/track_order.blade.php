@extends('layouts.dashboard')

@section('title', 'Track Order - ' . config('app.name', 'Shop Now BD'))

@section('dashboard_content')
<style>
    .track-stepper-container {
        position: relative;
        padding: 25px 10px 15px;
    }
    .track-progress-line {
        position: absolute;
        top: 50px;
        left: 10%;
        right: 10%;
        height: 4px;
        background: #e2e8f0;
        z-index: 1;
    }
    .track-progress-fill {
        height: 100%;
        background: #2563eb;
        transition: width 0.4s ease;
    }
    .step-circle {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        margin: 0 auto 10px;
        position: relative;
        z-index: 2;
        transition: all 0.3s ease;
        border: 3px solid #ffffff;
    }
    .step-circle.active {
        background-color: #2563eb;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
    }
    .step-circle.completed {
        background-color: #10b981;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }
    .step-circle.inactive {
        background-color: #e2e8f0;
        color: #94a3b8;
    }
</style>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
    <h5 class="fw-bold text-dark mb-1"><i class="fas fa-shipping-fast text-primary me-2"></i> Track Your Package</h5>
    <p class="text-muted small mb-4">Enter your order Invoice Number to get live dispatch and courier updates</p>

    <form action="{{ route('user.track.order') }}" method="GET" class="mb-4">
        <div class="row g-2">
            <div class="col-md-9">
                <input type="text" name="invoice_no" class="form-control rounded-pill px-4 py-2" value="{{ request('invoice_no') }}" placeholder="   INV-20260904-QQFV" required>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold">
                    <i class="fas fa-search me-1"></i> Track
                </button>
            </div>
        </div>
    </form>

    @if(isset($trackedOrder) && $trackedOrder)
        @php
            $rawStatus = strtolower(trim($trackedOrder->order_status ?? 'pending'));

            // Map all 5 major milestone steps: 1=Received/Pending, 2=Confirmed, 3=Shipped, 4=Out for Delivery, 5=Delivered
            $currentStep = match($rawStatus) {
                'confirmed', 'processing' => 2,
                'shipped', 'dispatched', 'on the way' => 3,
                'attempt_to_deliver', 'out for delivery' => 4,
                'delivered', 'completed', 'success' => 5,
                default => 1, // 'pending', 'received'
            };

            $isCancelled = in_array($rawStatus, ['cancelled', 'canceled', 'failed', 'returned']);

            // Progress bar width calculations for 5 steps (0%, 25%, 50%, 75%, 100%)
            $fillPercentages = [
                1 => '0%',
                2 => '25%',
                3 => '50%',
                4 => '75%',
                5 => '100%'
            ];
            $fillWidth = $isCancelled ? '0%' : ($fillPercentages[$currentStep] ?? '0%');

            $statusBadgeClass = match($rawStatus) {
                'delivered', 'completed', 'success' => 'bg-success',
                'attempt_to_deliver', 'out for delivery' => 'bg-info text-dark',
                'shipped', 'dispatched' => 'bg-warning text-dark',
                'confirmed', 'processing' => 'bg-primary',
                'cancelled', 'canceled', 'failed', 'returned' => 'bg-danger',
                default => 'bg-secondary',
            };
        @endphp

        <div class="border rounded-4 p-4 mt-3 bg-light">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4 border-bottom pb-3">
                <div>
                    <h6 class="fw-bold text-dark mb-1">Invoice #{{ $trackedOrder->invoice_no }}</h6>
                    <small class="text-muted">
                        @if($currentStep >= 5)
                            <i class="fas fa-check-circle text-success me-1"></i> Package delivered successfully.
                        @elseif($isCancelled)
                            <i class="fas fa-times-circle text-danger me-1"></i> This order has been cancelled or returned.
                        @elseif($currentStep === 4)
                            <i class="fas fa-motorcycle text-primary me-1"></i> Rider is attempting to deliver your package today!
                        @else
                            Estimated Delivery: 2-4 business days
                        @endif
                    </small>
                </div>
                <div>
                    <span class="badge {{ $statusBadgeClass }} text-uppercase px-3 py-2 rounded-pill font-monospace" style="font-size: 11px; letter-spacing: 0.5px;">
                        CURRENT STATUS: {{ str_replace('_', ' ', $trackedOrder->order_status ?? 'Pending') }}
                    </span>
                </div>
            </div>

            @if($isCancelled)
                <div class="alert alert-danger rounded-4 py-3 mb-0 text-center">
                    <i class="fas fa-exclamation-triangle fa-lg me-2"></i> This order is currently <strong>{{ strtoupper($rawStatus) }}</strong>. Please reach out via <a href="{{ route('user.support') }}" class="alert-link text-decoration-underline">Customer Support</a> for questions or assistance.
                </div>
            @else
                <!-- 5-Step Visual Stepper -->
                <div class="track-stepper-container">
                    <div class="track-progress-line">
                        <div class="track-progress-fill" style="width: {{ $fillWidth }};"></div>
                    </div>

                    <div class="row text-center position-relative">
                        <!-- Step 1: Received -->
                        <div class="col" style="flex: 1 0 0%;">
                            <div class="step-circle {{ $currentStep > 1 ? 'completed' : 'active' }}">
                                <i class="fas {{ $currentStep > 1 ? 'fa-check' : 'fa-receipt' }}"></i>
                            </div>
                            <span class="small fw-bold {{ $currentStep >= 1 ? 'text-primary' : 'text-muted' }}">Received</span>
                            <span class="d-block text-muted" style="font-size: 11px;">{{ date('d M, Y', strtotime($trackedOrder->created_at)) }}</span>
                        </div>

                        <!-- Step 2: Confirmed -->
                        <div class="col" style="flex: 1 0 0%;">
                            <div class="step-circle {{ $currentStep > 2 ? 'completed' : ($currentStep === 2 ? 'active' : 'inactive') }}">
                                <i class="fas {{ $currentStep > 2 ? 'fa-check' : 'fa-clipboard-check' }}"></i>
                            </div>
                            <span class="small fw-bold {{ $currentStep >= 2 ? 'text-primary' : 'text-muted' }}">Confirmed</span>
                            <span class="d-block text-muted" style="font-size: 11px;">Verified</span>
                        </div>

                        <!-- Step 3: Shipped -->
                        <div class="col" style="flex: 1 0 0%;">
                            <div class="step-circle {{ $currentStep > 3 ? 'completed' : ($currentStep === 3 ? 'active' : 'inactive') }}">
                                <i class="fas {{ $currentStep > 3 ? 'fa-check' : 'fa-truck' }}"></i>
                            </div>
                            <span class="small fw-bold {{ $currentStep >= 3 ? 'text-primary' : 'text-muted' }}">Shipped</span>
                            <span class="d-block text-muted" style="font-size: 11px;">On The Way</span>
                        </div>

                        <!-- Step 4: Attempt to Deliver -->
                        <div class="col" style="flex: 1 0 0%;">
                            <div class="step-circle {{ $currentStep > 4 ? 'completed' : ($currentStep === 4 ? 'active' : 'inactive') }}">
                                <i class="fas {{ $currentStep > 4 ? 'fa-check' : 'fa-motorcycle' }}"></i>
                            </div>
                            <span class="small fw-bold {{ $currentStep >= 4 ? 'text-primary' : 'text-muted' }}">Out for Delivery</span>
                            <span class="d-block text-muted" style="font-size: 11px;">With Rider</span>
                        </div>

                        <!-- Step 5: Delivered -->
                        <div class="col" style="flex: 1 0 0%;">
                            <div class="step-circle {{ $currentStep >= 5 ? 'completed' : 'inactive' }}">
                                <i class="fas {{ $currentStep >= 5 ? 'fa-check-double' : 'fa-box-open' }}"></i>
                            </div>
                            <span class="small fw-bold {{ $currentStep >= 5 ? 'text-success' : 'text-muted' }}">Delivered</span>
                            <span class="d-block text-muted" style="font-size: 11px;">Completed</span>
                        </div>
                    </div>
                </div>

                <!-- Courier Information Box (If Assigned) -->
                @if(!empty($trackedOrder->courier_name) || !empty($trackedOrder->courier_tracking_id))
                    <div class="card border-0 bg-white shadow-sm rounded-3 p-3 mt-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-warning-subtle text-warning-emphasis d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                <i class="fas fa-dolly fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-0">Delivery Partner: {{ $trackedOrder->courier_name ?? 'Steadfast Courier' }}</h6>
                                <p class="text-muted small mb-0">Tracking ID: <strong class="text-primary font-monospace">{{ $trackedOrder->courier_tracking_id ?? 'N/A' }}</strong></p>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                <span class="text-muted small">
                    <i class="fas fa-map-marker-alt text-danger me-1"></i> Delivery To: <strong>{{ $trackedOrder->shipping_address ?? ($trackedOrder->shipping_district ?? 'Your Shipping Address') }}</strong>
                </span>
                <a href="{{ route('user.orders.details', $trackedOrder->invoice_no) }}" class="btn btn-outline-primary rounded-pill btn-sm px-3 fw-semibold">
                    <i class="fas fa-file-invoice me-1"></i> View Order Details
                </a>
            </div>
        </div>
    @elseif(request()->filled('invoice_no'))
        <div class="alert alert-warning rounded-4 border-0 py-3 text-center small">
            <i class="fas fa-exclamation-triangle me-1"></i> No matching order was found for invoice "<strong>{{ request('invoice_no') }}</strong>". Please verify your invoice number.
        </div>
    @endif
</div>
@endsection