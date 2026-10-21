@extends('layouts.dashboard')

@section('title', 'Order Details #' . $order->invoice_no)

@section('dashboard_content')
@php
    $orderSubtotal = (float) ($order->subtotal ?? 0);
    $orderGrandTotal = (float) ($order->grand_total ?? $order->total ?? 0);
    $orderDeliveryCharge = (float) ($order->delivery_charge ?? $order->shipping_cost ?? $order->shipping_charge ?? $order->delivery_fee ?? 0);

    if ($orderDeliveryCharge <= 0 && $orderGrandTotal > 0 && $orderSubtotal > 0 && $orderGrandTotal > $orderSubtotal) {
        $orderDeliveryCharge = $orderGrandTotal - $orderSubtotal;
    }

    if ($orderSubtotal <= 0 && $orderGrandTotal > 0) {
        $orderSubtotal = max(0, $orderGrandTotal - $orderDeliveryCharge);
    }

    $rawOrderStatus = strtolower(trim($order->order_status ?? 'pending'));
    $rawPaymentStatus = strtolower(trim($order->payment_status ?? 'unpaid'));

    $isOrderDone = in_array($rawOrderStatus, ['completed', 'delivered', 'success']);

    if ($isOrderDone || $rawPaymentStatus === 'paid') {
        $displayPaymentStatus = 'PAID';
        $paymentBadgeClass = 'bg-success';
    } else {
        $displayPaymentStatus = 'UNPAID';
        $paymentBadgeClass = 'bg-warning text-dark';
    }

    $orderBadgeClass = match($rawOrderStatus) {
        'delivered', 'completed', 'success' => 'bg-success',
        'shipped' => 'bg-info text-dark',
        'processing' => 'bg-primary',
        'cancelled' => 'bg-danger',
        default => 'bg-warning text-dark',
    };

    // Universal Image Resolver for Order Items
    $resolveOrderItemImg = function($path, $fallback = 'https://placehold.co/80x80?text=Product') {
        if (empty($path)) return $fallback;
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        $clean = ltrim(str_replace(['storage/', 'public/'], '', $path), '/');
        if (file_exists(public_path($clean))) return asset($clean);
        if (file_exists(public_path('uploads/' . $clean))) return asset('uploads/' . $clean);
        return asset($clean);
    };
@endphp

<div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 border-bottom pb-3 mb-4">
        <div>
            <a href="{{ route('user.orders') }}" class="btn btn-sm btn-light border rounded-pill px-3 mb-2 small">
                <i class="fas fa-arrow-left me-1"></i> Back to Orders
            </a>
            <h5 class="fw-bold text-dark mb-0">Invoice #{{ $order->invoice_no }}</h5>
            <small class="text-muted">Placed on {{ date('d M, Y \a\t h:i A', strtotime($order->created_at)) }}</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('order.confirmation', $order->invoice_no) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fas fa-print me-1"></i> Print Invoice
            </a>
            <a href="{{ route('user.track.order', ['invoice_no' => $order->invoice_no]) }}" class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="fas fa-truck me-1"></i> Track Order
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="p-3 bg-light rounded-3 h-100">
                <h6 class="fw-bold text-dark small mb-2"><i class="fas fa-map-marker-alt text-primary me-2"></i> Shipping Address</h6>
                <div class="small text-secondary">
                    <strong>{{ $order->customer_name ?? $order->name ?? auth()->user()->name }}</strong><br>
                    Phone: {{ $order->customer_phone ?? $order->phone ?? auth()->user()->phone ?? 'N/A' }}<br>
                    Address: {{ $order->shipping_address ?? $order->customer_address ?? $order->address ?? 'N/A' }}<br>
                    District: {{ $order->shipping_district ?? $order->district ?? $order->city ?? 'N/A' }}
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 bg-light rounded-3 h-100">
                <h6 class="fw-bold text-dark small mb-2"><i class="fas fa-credit-card text-success me-2"></i> Payment Details</h6>
                <div class="small text-secondary">
                    Method: <strong class="text-capitalize">{{ $order->payment_method ?? 'Cash on Delivery' }}</strong><br>
                    Status: <span class="badge {{ $paymentBadgeClass }} text-uppercase" style="font-size: 10px;">{{ $displayPaymentStatus }}</span><br>
                    @if(!empty($order->transaction_id))
                        TrxID: {{ $order->transaction_id }}
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 bg-light rounded-3 h-100">
                <h6 class="fw-bold text-dark small mb-2"><i class="fas fa-info-circle text-info me-2"></i> Order Status</h6>
                <div class="small text-secondary">
                    Status: <span class="badge {{ $orderBadgeClass }} text-uppercase" style="font-size: 10px;">{{ $order->order_status ?? 'pending' }}</span><br>
                    Delivery Charge: ৳{{ number_format($orderDeliveryCharge, 2) }}
                </div>
            </div>
        </div>
    </div>

    <h6 class="fw-bold text-dark mb-3">Order Items</h6>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Item</th>
                    <th class="text-center">Price</th>
                    <th class="text-center">Quantity</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orderItems as $item)
                    @php
                        $itemTitle = $item->product_table_title ?? $item->product_title ?? $item->product_name ?? $item->title ?? $item->name ?? 'Product Item';
                        $itemQty = (int) ($item->quantity ?? $item->qty ?? 1);
                        $itemPrice = (float) ($item->unit_price ?? $item->product_price ?? $item->price ?? $item->rate ?? 0);
                        
                        if ($itemPrice === 0.0 && isset($item->total)) {
                            $itemTotal = (float) $item->total;
                            $itemPrice = $itemQty > 0 ? ($itemTotal / $itemQty) : $itemTotal;
                        } else {
                            $itemTotal = (float) ($item->total ?? $item->subtotal ?? ($itemPrice * $itemQty));
                        }

                        $itemImageRaw = $item->image ?? $item->product_image ?? $item->thumbnail ?? null;
                        $itemImg = $resolveOrderItemImg($itemImageRaw);
                    @endphp
                    <tr>
                        <td class="py-3">
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $itemImg }}" alt="{{ $itemTitle }}" class="rounded-2 border flex-shrink-0" style="width: 44px; height: 44px; object-fit: contain;" loading="lazy">
                                <div>
                                    <div class="fw-semibold text-dark small">{{ $itemTitle }}</div>
                                    @if(!empty($item->pv_color) || !empty($item->pv_size))
                                        <small class="text-muted" style="font-size: 11px;">
                                            {{ trim(($item->pv_color ?? '') . ' ' . ($item->pv_size ?? '')) }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="text-center small">৳{{ number_format($itemPrice, 2) }}</td>
                        <td class="text-center small">{{ $itemQty }}</td>
                        <td class="text-end fw-bold text-dark small">৳{{ number_format($itemTotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="border-top">
                <tr>
                    <td colspan="3" class="text-end text-secondary small py-2">Subtotal:</td>
                    <td class="text-end fw-bold text-dark small py-2">৳{{ number_format($orderSubtotal, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-end text-secondary small py-2">Delivery Charge:</td>
                    <td class="text-end fw-bold text-dark small py-2">৳{{ number_format($orderDeliveryCharge, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-end fw-bold text-dark fs-6 py-2">Grand Total:</td>
                    <td class="text-end fw-bold text-primary fs-6 py-2">৳{{ number_format($orderGrandTotal, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection