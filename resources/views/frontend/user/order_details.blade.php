@extends('layouts.dashboard')

@section('title', 'Order Details #' . $order->invoice_no)

@php
    // সেন্ট্রাল গ্লোবাল সেটিংস ক্যাশ রেজলভার
    static $orderDetailCache = null;
    if ($orderDetailCache === null) {
        $orderDetailCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
            return \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $ordSettings = $orderDetailCache ?? [];

    // ডায়নামিক থিম কালার ভ্যারিয়েবল (Existing Default Colors)
    $ordCardBg        = $ordSettings['color_auth_card_bg'] ?? '#ffffff';
    $ordCardBorder    = $ordSettings['color_auth_card_border'] ?? '#e2e8f0';
    $ordTitleColor    = $ordSettings['color_page_title'] ?? '#0f172a';
    $ordTextColor     = $ordSettings['color_auth_text'] ?? '#64748b';
    $ordBoxBg         = $ordSettings['color_site_bg'] ?? '#f8fafc';
    $ordPrimaryColor  = $ordSettings['color_common_btn_bg'] ?? ($ordSettings['color_hdr_menu_hover_text'] ?? '#2563eb');
    $ordPrimaryHover  = $ordSettings['color_auth_btn_hover'] ?? '#1d4ed8';
    $ordPriceColor    = $ordSettings['color_cpc_price'] ?? '#0f172a';

    // ক্যালকুলেশন ও স্ট্যাটাস লজিক (অক্ষত)
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
        $paymentBadgeClass = 'bg-success text-white';
    } else {
        $displayPaymentStatus = 'UNPAID';
        $paymentBadgeClass = 'bg-warning text-dark';
    }

    $orderBadgeClass = match($rawOrderStatus) {
        'delivered', 'completed', 'success' => 'bg-success text-white',
        'shipped' => 'bg-info text-dark',
        'processing' => 'bg-primary text-white',
        'cancelled' => 'bg-danger text-white',
        default => 'bg-warning text-dark',
    };

    // Universal Image Resolver for Order Items (অক্ষত)
    $resolveOrderItemImg = function($path, $fallback = 'https://placehold.co/80x80?text=Product') {
        if (empty($path)) return $fallback;
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        $clean = ltrim(str_replace(['storage/', 'public/'], '', $path), '/');
        if (file_exists(public_path($clean))) return asset($clean);
        if (file_exists(public_path('uploads/' . $clean))) return asset('uploads/' . $clean);
        return asset($clean);
    };
@endphp

@section('dashboard_content')
<style>
    :root {
        --ord-card-bg: {{ $ordCardBg }};
        --ord-card-border: {{ $ordCardBorder }};
        --ord-title: {{ $ordTitleColor }};
        --ord-text: {{ $ordTextColor }};
        --ord-box-bg: {{ $ordBoxBg }};
        --ord-primary: {{ $ordPrimaryColor }};
        --ord-primary-hover: {{ $ordPrimaryHover }};
        --ord-price: {{ $ordPriceColor }};
    }

    /* Universal Strict Link Text Decoration */
    .order-details-root a,
    .order-details-root a:hover {
        text-decoration: none !important;
    }

    /* Main Container Card */
    .order-details-card {
        background-color: var(--ord-card-bg) !important;
        border: 1px solid var(--ord-card-border) !important;
        border-radius: 16px !important;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04), 0 1px 3px rgba(15, 23, 42, 0.02) !important;
        padding: 24px;
        transition: all 0.25s ease;
    }

    .order-page-title {
        color: var(--ord-title) !important;
        font-weight: 700;
        font-size: 1.2rem;
    }

    .order-page-subtitle {
        color: var(--ord-text) !important;
        font-size: 12.5px;
    }

    /* Buttons */
    .btn-order-back {
        background: #ffffff !important;
        border: 1px solid var(--ord-card-border) !important;
        color: var(--ord-text) !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        padding: 6px 14px !important;
        border-radius: 50px !important;
        display: inline-flex;
        align-items: center;
        transition: all 0.2s ease;
    }
    .btn-order-back:hover {
        background: var(--ord-box-bg) !important;
        color: var(--ord-title) !important;
        border-color: #cbd5e1 !important;
    }

    .btn-order-action {
        font-size: 12.5px !important;
        font-weight: 600 !important;
        padding: 7px 16px !important;
        border-radius: 50px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: all 0.2s ease;
        min-height: 36px;
    }
    .btn-order-print {
        background: #ffffff !important;
        border: 1px solid var(--ord-card-border) !important;
        color: var(--ord-text) !important;
    }
    .btn-order-print:hover {
        background: var(--ord-box-bg) !important;
        color: var(--ord-title) !important;
        border-color: #cbd5e1 !important;
    }

    .btn-order-track {
        background: var(--ord-primary) !important;
        border: 1px solid var(--ord-primary) !important;
        color: #ffffff !important;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.2) !important;
    }
    .btn-order-track:hover {
        background: var(--ord-primary-hover) !important;
        border-color: var(--ord-primary-hover) !important;
        color: #ffffff !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3) !important;
    }

    /* Info Cards (Address, Payment, Status) */
    .order-info-tile {
        background-color: var(--ord-box-bg) !important;
        border: 1px solid var(--ord-card-border) !important;
        border-radius: 12px;
        padding: 16px;
        height: 100%;
        transition: all 0.2s ease;
    }
    .order-info-tile:hover {
        border-color: #cbd5e1 !important;
    }
    .order-info-heading {
        color: var(--ord-title) !important;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
    }
    .order-info-body {
        font-size: 12.5px;
        color: var(--ord-text);
        line-height: 1.6;
    }

    /* Badges */
    .status-badge-custom {
        font-size: 10px !important;
        font-weight: 700 !important;
        padding: 3px 8px !important;
        border-radius: 50px !important;
        letter-spacing: 0.03em;
    }

    /* Items Table */
    .order-items-table-wrap {
        border: 1px solid var(--ord-card-border);
        border-radius: 12px;
        overflow: hidden;
        background: #ffffff;
    }
    .order-items-table {
        margin-bottom: 0 !important;
        white-space: nowrap;
    }
    .order-items-table thead {
        background-color: var(--ord-box-bg) !important;
    }
    .order-items-table thead th {
        color: var(--ord-text) !important;
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        border-bottom: 1px solid var(--ord-card-border);
        padding: 11px 16px;
    }
    .order-items-table tbody td {
        padding: 12px 16px;
        border-bottom: 1px solid var(--ord-card-border);
        font-size: 13px;
        color: var(--ord-text);
    }
    .order-items-table tfoot td {
        padding: 10px 16px;
        border-bottom: 1px solid var(--ord-card-border);
    }
    .order-items-table tfoot tr:last-child td {
        border-bottom: none;
    }

    .order-product-thumb {
        width: 46px;
        height: 46px;
        object-fit: contain;
        background: var(--ord-box-bg);
        border: 1px solid var(--ord-card-border);
        border-radius: 8px;
        padding: 2px;
        flex-shrink: 0;
    }
    .order-product-name {
        color: var(--ord-title);
        font-weight: 600;
        font-size: 13px;
        line-height: 1.35;
    }

    /* Bengali digit protection */
    .bn-digit {
        font-weight: 700 !important;
    }

    /* Small Screen & Mobile Optimization (iPhone SE / 320px–575px) */
    @media (max-width: 767.98px) {
        .order-details-card {
            padding: 16px 14px !important;
            border-radius: 14px !important;
        }
        .order-header-action-group {
            width: 100%;
            display: flex;
            gap: 8px;
        }
        .order-header-action-group .btn-order-action {
            flex: 1;
            padding: 8px 10px !important;
            font-size: 12px !important;
        }
        .order-page-title {
            font-size: 1.1rem !important;
        }
        .order-info-tile {
            padding: 14px !important;
        }
    }
</style>

<div class="order-details-root">
    <div class="order-details-card mb-4">
        
        {{-- Header Bar --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 border-bottom pb-3 mb-4" style="border-color: var(--ord-card-border) !important;">
            <div>
                <a href="{{ route('user.orders') }}" class="btn-order-back mb-2">
                    <i class="fas fa-arrow-left me-1"></i> <span>Back to Orders</span>
                </a>
                <h5 class="order-page-title mb-0">Invoice #{{ $order->invoice_no }}</h5>
                <small class="order-page-subtitle d-block mt-0.5">
                    Placed on <span class="bn-digit">{{ date('d M, Y \a\t h:i A', strtotime($order->created_at)) }}</span>
                </small>
            </div>
            <div class="order-header-action-group d-flex gap-2">
                <a href="{{ route('order.confirmation', $order->invoice_no) }}" class="btn-order-action btn-order-print">
                    <i class="fas fa-print"></i> <span>Print Invoice</span>
                </a>
                <a href="{{ route('user.track.order', ['invoice_no' => $order->invoice_no]) }}" class="btn-order-action btn-order-track">
                    <i class="fas fa-truck"></i> <span>Track Order</span>
                </a>
            </div>
        </div>

        {{-- 3-Column Info Cards --}}
        <div class="row g-3 mb-4">
            {{-- 1. Shipping Address --}}
            <div class="col-12 col-md-4">
                <div class="order-info-tile">
                    <div class="order-info-heading">
                        <i class="fas fa-map-marker-alt me-2" style="color: var(--ord-primary);"></i>
                        <span>Shipping Address</span>
                    </div>
                    <div class="order-info-body">
                        <strong class="d-block" style="color: var(--ord-title);">
                            {{ $order->customer_name ?? $order->name ?? auth()->user()->name }}
                        </strong>
                        <div>Phone: <span class="bn-digit">{{ $order->customer_phone ?? $order->phone ?? auth()->user()->phone ?? 'N/A' }}</span></div>
                        <div class="text-break">Address: {{ $order->shipping_address ?? $order->customer_address ?? $order->address ?? 'N/A' }}</div>
                        <div>District: {{ $order->shipping_district ?? $order->district ?? $order->city ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>

            {{-- 2. Payment Details --}}
            <div class="col-12 col-md-4">
                <div class="order-info-tile">
                    <div class="order-info-heading">
                        <i class="fas fa-credit-card text-success me-2"></i>
                        <span>Payment Details</span>
                    </div>
                    <div class="order-info-body">
                        <div>Method: <strong class="text-capitalize" style="color: var(--ord-title);">{{ $order->payment_method ?? 'Cash on Delivery' }}</strong></div>
                        <div class="d-flex align-items-center gap-1.5 mt-1 mb-1">
                            <span>Status:</span>
                            <span class="badge {{ $paymentBadgeClass }} status-badge-custom text-uppercase">
                                {{ $displayPaymentStatus }}
                            </span>
                        </div>
                        @if(!empty($order->transaction_id))
                            <div class="text-truncate">TrxID: <span class="bn-digit">{{ $order->transaction_id }}</span></div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- 3. Order Status & Delivery Fee --}}
            <div class="col-12 col-md-4">
                <div class="order-info-tile">
                    <div class="order-info-heading">
                        <i class="fas fa-info-circle text-info me-2"></i>
                        <span>Order Status</span>
                    </div>
                    <div class="order-info-body">
                        <div class="d-flex align-items-center gap-1.5 mb-1">
                            <span>Status:</span>
                            <span class="badge {{ $orderBadgeClass }} status-badge-custom text-uppercase">
                                {{ $order->order_status ?? 'pending' }}
                            </span>
                        </div>
                        <div>
                            Delivery Charge: <span class="bn-digit fw-bold" style="color: var(--ord-title);">৳{{ number_format($orderDeliveryCharge, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Order Items Section --}}
        <h6 class="fw-bold mb-3" style="color: var(--ord-title); font-size: 14.5px;">Order Items</h6>
        <div class="order-items-table-wrap">
            <div class="table-responsive">
                <table class="table order-items-table align-middle">
                    <thead>
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
                                <td class="py-2.5">
                                    <div class="d-flex align-items-center gap-2.5">
                                        <img src="{{ $itemImg }}" alt="{{ $itemTitle }}" class="order-product-thumb" loading="lazy">
                                        <div class="min-w-0">
                                            <div class="order-product-name text-truncate" style="max-width: 280px;" title="{{ $itemTitle }}">
                                                {{ $itemTitle }}
                                            </div>
                                            @if(!empty($item->pv_color) || !empty($item->pv_size))
                                                <small class="d-block" style="color: var(--ord-text); font-size: 11px;">
                                                    {{ trim(($item->pv_color ?? '') . ' ' . ($item->pv_size ?? '')) }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center small bn-digit">৳{{ number_format($itemPrice, 2) }}</td>
                                <td class="text-center small bn-digit">{{ $itemQty }}</td>
                                <td class="text-end fw-bold small bn-digit" style="color: var(--ord-title);">৳{{ number_format($itemTotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end small py-2" style="color: var(--ord-text);">Subtotal:</td>
                            <td class="text-end fw-bold small py-2 bn-digit" style="color: var(--ord-title);">৳{{ number_format($orderSubtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end small py-2" style="color: var(--ord-text);">Delivery Charge:</td>
                            <td class="text-end fw-bold small py-2 bn-digit" style="color: var(--ord-title);">৳{{ number_format($orderDeliveryCharge, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end fw-bold fs-6 py-2.5" style="color: var(--ord-title);">Grand Total:</td>
                            <td class="text-end fw-bold fs-6 py-2.5 bn-digit" style="color: var(--ord-primary);">৳{{ number_format($orderGrandTotal, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection