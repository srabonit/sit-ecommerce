@extends('layouts.admin')

@section('title', 'Order #' . ($order->invoice_no ?? $order->id) . ' Details')

@php
    $resolveOrderImg = function($path, $fallback = '') {
        if (empty($path) || $path === 'undefined' || $path === 'null') return $fallback;
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path, $fallback);
        }
        if (function_exists('show_image')) return show_image($path, $fallback);
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        return asset(ltrim($path, '/'));
    };

    $pSubtotal = (float)($order->product_subtotal ?? ($order->subtotal ?? 0));
    $dCharge   = (float)($order->delivery_charge ?? ($order->shipping_charge ?? 0));
    $discount  = (float)($order->discount ?? ($order->discount_amount ?? 0));
    $grand     = (float)($order->grand_total ?? 0);
    $paid      = (float)($order->paid_amount ?? (($order->payment_status ?? '') === 'paid' ? $grand : 0));
    $due       = (float)($order->due_amount ?? max(0, $grand - $paid));
@endphp

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across Controls */
    #order-details-root .btn,
    #order-details-root .btn-sm,
    #order-details-root button,
    #order-details-root .form-control,
    #order-details-root .form-select,
    #order-details-root .badge,
    #order-details-root .input-group-text,
    #order-details-root .alert,
    #order-details-root .order-card,
    #order-details-root .proof-media-surface,
    #order-details-root .proof-thumb-box,
    #order-details-root .proof-attached-card,
    .order-modal-scope .modal-content,
    .order-modal-scope .btn,
    .order-modal-scope .form-control,
    .order-modal-scope .form-select {
        border-radius: 5px !important;
    }

    #order-details-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #order-details-root .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Compact Button Styling */
    #order-details-root .btn-compact,
    .order-modal-scope .btn-compact {
        font-size: 11.5px !important;
        padding: 5px 12px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    /* Enterprise Surface Cards */
    #order-details-root .order-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        padding: 16px;
        margin-bottom: 16px;
    }

    /* Bengali Safe Typography */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Column Overflow Fix */
    .overflow-safe-col {
        min-width: 0 !important;
        word-break: break-word !important;
        overflow-wrap: anywhere !important;
    }

    .safe-text-break {
        overflow-wrap: anywhere !important;
        word-break: break-word !important;
        white-space: normal !important;
    }

    /* Table Styling */
    .order-items-table {
        margin-bottom: 0;
        width: 100%;
    }
    .order-items-table th {
        background-color: #f8fafc;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 10px 12px;
        white-space: nowrap;
    }
    .order-items-table td {
        padding: 10px 12px;
        vertical-align: middle;
        font-size: 12.5px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }

    /* Form Controls */
    #order-details-root .form-label,
    .order-modal-scope .form-label {
        font-size: 11.5px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #order-details-root .form-control,
    #order-details-root .form-select,
    .order-modal-scope .form-control,
    .order-modal-scope .form-select {
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 6px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #order-details-root .form-control:focus,
    #order-details-root .form-select:focus,
    .order-modal-scope .form-control:focus,
    .order-modal-scope .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Enhanced 200% Global Media Chooser Surface Box */
    .proof-media-surface {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        padding: 10px;
        transition: all 0.2s ease;
    }
    .proof-media-surface:hover {
        border-color: #3b82f6;
        background: #f1f5f9;
    }
    .proof-empty-icon-box {
        width: 38px;
        height: 38px;
        background: #e2e8f0;
        border-radius: 5px !important;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }
    .proof-attached-card {
        border-radius: 5px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
    }
    .proof-thumb-box {
        width: 48px;
        height: 48px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 5px !important;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
        position: relative;
    }
    .proof-thumb-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .proof-thumb-hover-overlay {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.15s ease;
    }
    .proof-thumb-box:hover .proof-thumb-hover-overlay {
        opacity: 1;
    }
    .proof-card-actions .btn-compact {
        font-size: 10.5px !important;
        padding: 5px 8px !important;
    }

    /* Live Search Dropdown for Exchange Items */
    .search-results-panel {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 1060;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 5px !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15);
        max-height: 240px;
        overflow-y: auto;
    }

    .search-result-item {
        padding: 8px 12px;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        transition: background-color 0.15s ease;
    }
    .search-result-item:hover {
        background-color: #f8fafc;
    }

    /* Mobile Responsive Overrides */
    @media (max-width: 767.98px) {
        .border-end-md {
            border-right: none !important;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 14px;
            margin-bottom: 14px;
        }
        .order-show-header h5 {
            font-size: 16px !important;
        }
        #order-details-root .order-card {
            padding: 12px !important;
            margin-bottom: 12px !important;
        }
        .header-action-btns {
            width: 100% !important;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .header-action-btns .btn-compact {
            width: 100%;
            padding: 7px 10px !important;
        }
        .header-action-btns a.btn-outline-secondary {
            grid-column: span 2;
        }
        .order-items-table th,
        .order-items-table td {
            padding: 8px 10px !important;
            font-size: 12px;
        }
    }
    @media (min-width: 768px) {
        .border-end-md {
            border-right: 1px solid #e2e8f0;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="order-details-root">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 order-show-header">
        <div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1">
                    <i class="fas fa-receipt text-primary"></i>
                    <span>Order</span> 
                    <span class="font-monospace text-primary bn-digit">#{{ $order->invoice_no ?? $order->id }}</span>
                </h5>

                {{-- Channel Badge --}}
                @php
                    $orderSource = strtolower($order->order_source ?? ($order->order_type ?? 'web'));
                @endphp
                <span class="badge {{ $orderSource === 'pos' ? 'bg-success text-white' : 'bg-primary text-white' }} px-2 py-1 text-uppercase" style="font-size: 9.5px;">
                    {{ $orderSource }}
                </span>

                {{-- Physical Stock Deduction Indicator Badge --}}
                @if(!empty($order->is_stock_restored))
                    <span class="badge bg-secondary text-white px-2 py-1" style="font-size: 9.5px;">
                        <i class="fas fa-undo me-1"></i> Stock Restored
                    </span>
                @elseif(!empty($order->is_stock_deducted))
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 9.5px;">
                        <i class="fas fa-check-double me-1"></i> Physical Stock Deducted
                    </span>
                @else
                    <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-2 py-1" style="font-size: 9.5px;">
                        <i class="fas fa-clock me-1"></i> Stock Reserved (Estimate)
                    </span>
                @endif
            </div>

            <small class="text-muted d-block mt-1" style="font-size: 11.5px;">
                Payment: <strong class="text-dark">{{ strtoupper($order->payment_method ?? 'COD') }}</strong> &bull; Placed: <span class="bn-digit">{{ date('d M Y, h:i A', strtotime($order->created_at)) }}</span>
            </small>
        </div>
        <div class="header-action-btns d-flex flex-wrap gap-1">
            <button type="button" class="btn btn-outline-danger btn-compact shadow-xs" onclick="openReturnExchangeModal()">
                <i class="fas fa-arrows-rotate me-1"></i>
                <span>Return / Exchange</span>
            </button>
            <a href="{{ route('admin.order.invoice.print', $order->id) }}" target="_blank" class="btn btn-dark btn-compact shadow-xs">
                <i class="fas fa-print me-1"></i>
                <span>Print Invoice</span>
            </a>
            <a href="{{ route('admin.order.online') }}" class="btn btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-arrow-left me-1"></i>
                <span>Back to Orders</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 small border-0 shadow-xs d-flex align-items-center justify-content-between mb-3" role="alert">
            <div>
                <i class="fas fa-check-circle me-2 text-success"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3">
        <!-- Left Column: Customer, Payment & Order Items -->
        <div class="col-12 col-lg-8">
            <!-- Customer & Payment Overview Card -->
            <div class="order-card">
                <div class="row g-3">
                    <!-- Billing & Customer Details -->
                    <div class="col-12 col-md-6 border-end-md pe-md-3 overflow-safe-col">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-2 d-flex align-items-center gap-1" style="font-size: 13.5px;">
                            <i class="fas fa-user-circle text-primary"></i> <span>Billing &amp; Customer Details</span>
                        </h6>
                        <p class="mb-1 small text-dark safe-text-break">
                            <strong class="text-muted">Name:</strong> {{ $order->shipping_name ?? ($order->customer_name ?? ($order->name ?? 'Guest Customer')) }}
                        </p>
                        <p class="mb-1 small text-dark">
                            <strong class="text-muted">Phone:</strong> <span class="font-monospace bn-digit">{{ $order->shipping_phone ?? ($order->customer_phone ?? ($order->phone ?? 'N/A')) }}</span>
                        </p>
                        <p class="mb-1 small text-dark safe-text-break">
                            <strong class="text-muted">Email:</strong> {{ $order->shipping_email ?? ($order->customer_email ?? ($order->email ?? 'N/A')) }}
                        </p>
                        <p class="mb-1 small text-dark safe-text-break">
                            <strong class="text-muted">District:</strong> {{ $order->shipping_district ?? ($order->district ?? 'Dhaka') }}
                        </p>
                        
                        <div class="mt-2 pt-1 border-top">
                            <strong class="text-muted small d-block mb-1" style="font-size: 11px;">
                                <i class="fas fa-map-marker-alt text-danger me-1"></i> Delivery Address:
                            </strong>
                            <div class="p-2 bg-light border text-dark small safe-text-break" style="font-size: 11.5px; line-height: 1.45; border-radius: 5px;">
                                {{ $order->shipping_address ?? ($order->customer_address ?? ($order->address ?? 'N/A')) }}
                            </div>
                        </div>
                    </div>

                    <!-- Payment & Courier Tracking Details -->
                    <div class="col-12 col-md-6 ps-md-3 overflow-safe-col">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-2 d-flex align-items-center gap-1" style="font-size: 13.5px;">
                            <i class="fas fa-receipt text-success"></i> <span>Payment &amp; Tracking Details</span>
                        </h6>
                        <p class="mb-1 small text-dark d-flex align-items-center justify-content-between">
                            <strong class="text-muted">Payment Method:</strong> 
                            <span class="badge bg-light text-dark border font-monospace px-2 py-0.5" style="font-size: 10px;">{{ strtoupper($order->payment_method ?? 'COD') }}</span>
                        </p>
                        <p class="mb-1 small text-dark d-flex align-items-center justify-content-between">
                            <strong class="text-muted">Payment Status:</strong> 
                            @php
                                $pStat = strtolower($order->payment_status ?? 'unpaid');
                                $pBadge = match($pStat) {
                                    'paid' => 'bg-success text-white',
                                    'partially_paid', 'partial' => 'bg-warning text-dark',
                                    'pending_verification' => 'bg-info text-dark',
                                    'refunded' => 'bg-secondary text-white',
                                    default => 'bg-danger text-white'
                                };
                            @endphp
                            <span class="badge {{ $pBadge }} text-uppercase px-2 py-0.5" style="font-size: 9.5px;">
                                {{ str_replace('_', ' ', $pStat) }}
                            </span>
                        </p>
                        <p class="mb-1 small text-dark safe-text-break">
                            <strong class="text-muted">Transaction ID:</strong> 
                            <span class="font-monospace text-primary fw-bold bn-digit">{{ $order->transaction_id ?? 'N/A' }}</span>
                        </p>
                        <p class="mb-1 small text-dark">
                            <strong class="text-muted">Sender Number:</strong> 
                            @if(!empty($order->sender_number))
                                <span class="font-monospace text-dark fw-bold bn-digit">{{ $order->sender_number }}</span>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </p>

                        <div class="border-top my-2 pt-2">
                            <p class="mb-1 small text-dark d-flex align-items-center justify-content-between">
                                <strong class="text-muted">Courier Provider:</strong> 
                                <span class="badge bg-primary-subtle text-primary border px-2 py-0.5 text-capitalize" style="font-size: 9.5px;">{{ $order->courier_name ?? 'Unassigned' }}</span>
                            </p>
                            <p class="mb-0 small text-dark safe-text-break">
                                <strong class="text-muted">Tracking Code:</strong> 
                                <span class="font-monospace text-success fw-bold bn-digit">{{ $order->courier_tracking_id ?? ($order->parcel_id ?? 'None') }}</span>
                            </p>
                        </div>

                        <!-- 200% Improved Global Media Chooser Component for Payment Proof -->
                        @php
                            $proofRaw = $order->payment_screenshot ?? '';
                            $proofImg = !empty($proofRaw) ? $resolveOrderImg($proofRaw) : '';
                        @endphp
                        <div class="mt-2 pt-2 border-top">
                            <label class="form-label d-flex align-items-center justify-content-between mb-2">
                                <span class="small text-muted fw-bold" style="font-size: 11px;">
                                    <i class="fas fa-paperclip text-primary me-1"></i> Payment Proof Image:
                                </span>
                                <span class="badge bg-light text-muted border font-monospace" style="font-size: 9px;">GLOBAL MEDIA</span>
                            </label>

                            <div class="proof-media-surface" id="proofMediaSurface">
                                <!-- Empty State -->
                                <div id="noProofAttachedNotice" class="{{ empty($proofImg) ? 'd-block' : 'd-none' }} text-center py-3">
                                    <div class="proof-empty-icon-box mx-auto mb-2">
                                        <i class="fas fa-cloud-arrow-up text-muted"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1" style="font-size: 12px;">No Payment Proof Attached</h6>
                                    <p class="text-muted small mb-2" style="font-size: 11px;">Attach customer deposit receipt, bKash/Nagad statement screenshot.</p>
                                    <button type="button" class="btn btn-primary btn-compact shadow-xs" onclick="choosePaymentProofMedia()">
                                        <i class="fas fa-photo-film me-1"></i>
                                        <span>Choose from Media Library</span>
                                    </button>
                                </div>

                                <!-- Attached Card Preview (Flawless on Mobile & PC) -->
                                <div id="proofPreviewContainer" class="{{ !empty($proofImg) ? 'd-block' : 'd-none' }} proof-attached-card bg-white border p-2">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <!-- Thumbnail with quick-zoom hover overlay -->
                                        <div class="proof-thumb-box position-relative" onclick="viewProofZoom()" title="Click to Zoom Image">
                                            <img id="paymentProofPreviewImg" src="{{ !empty($proofImg) ? $proofImg : 'https://placehold.co/120x120?text=Receipt' }}" alt="Payment Receipt" onerror="this.src='https://placehold.co/120x120?text=Receipt';">
                                            <div class="proof-thumb-hover-overlay">
                                                <i class="fas fa-search-plus text-white" style="font-size: 11px;"></i>
                                            </div>
                                        </div>

                                        <!-- Meta Details -->
                                        <div class="min-width-0 flex-grow-1">
                                            <div class="d-flex align-items-center gap-1 mb-1">
                                                <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 9.5px;" id="proofStatusText">
                                                    <i class="fas fa-check-circle me-1"></i>Receipt Attached
                                                </span>
                                            </div>
                                            <strong class="d-block text-dark text-truncate font-monospace" id="proofPathText" style="font-size: 10px;" title="{{ $proofRaw }}">
                                                {{ $proofRaw ? basename($proofRaw) : 'Receipt Image' }}
                                            </strong>
                                            <small class="text-muted d-block text-truncate" style="font-size: 9.5px;">Click Zoom or change anytime</small>
                                        </div>
                                    </div>

                                    <!-- Action Button Toolbar -->
                                    <div class="proof-card-actions d-flex align-items-center gap-1 border-top pt-2">
                                        <button type="button" class="btn btn-outline-secondary btn-compact flex-grow-1" onclick="viewProofZoom()">
                                            <i class="fas fa-expand me-1"></i><span>Zoom</span>
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-compact flex-grow-1" onclick="choosePaymentProofMedia()">
                                            <i class="fas fa-arrows-rotate me-1"></i><span>Change</span>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-compact flex-grow-1" onclick="clearPaymentProofMedia()">
                                            <i class="fas fa-trash-alt me-1"></i><span>Remove</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ordered Items Table Card -->
            <div class="order-card p-0 overflow-hidden mb-0">
                <div class="py-2 px-3 border-bottom bg-white d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13.5px;">
                        <i class="fas fa-boxes text-warning"></i> <span>Purchased Items</span>
                    </h6>
                    <button type="button" class="btn btn-outline-danger btn-compact py-1 px-2" style="font-size: 11px !important;" onclick="openReturnExchangeModal()">
                        <i class="fas fa-arrows-rotate me-1"></i> Return Items ?
                    </button>
                </div>
                <div class="table-responsive" style="-webkit-overflow-scrolling: touch;">
                    <table class="table align-middle mb-0 order-items-table text-nowrap">
                        <thead>
                            <tr>
                                <th style="min-width: 200px;">Item Description</th>
                                <th>Unit Price</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end pe-3">Line Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orderItems as $item)
                                @php
                                    $unitPrice = $item->price ?? ($item->unit_price ?? ($item->rate ?? 0));
                                    $qty = $item->quantity ?? ($item->qty ?? 1);
                                    $lineTotal = $item->total ?? ($item->total_price ?? ($unitPrice * $qty));

                                    $pName = $item->product_title ?? ($item->product_name ?? null);
                                    if (empty($pName) && !empty($item->product_id)) {
                                        $pName = DB::table('products')->where('id', $item->product_id)->value('title');
                                    }
                                    if (empty($pName)) {
                                        $pName = 'Product #' . ($item->product_id ?? '');
                                    }

                                    $vColor = $item->color ?? ($item->pv_color ?? null);
                                    $vSize  = $item->size ?? ($item->pv_size ?? null);
                                    $vTitle = $item->variant_title ?? null;
                                    $vSku   = $item->sku ?? ($item->pv_code ?? ($item->pv_sku ?? null));

                                    if (!empty($item->variant_id) && (empty($vColor) && empty($vSize))) {
                                        $vRecord = DB::table('product_variants')->where('id', $item->variant_id)->first();
                                        if ($vRecord) {
                                            $vColor = $vRecord->color ?? $vColor;
                                            $vSize  = $vRecord->size ?? $vSize;
                                            $vSku   = $vRecord->product_code ?? ($vRecord->sku ?? $vSku);
                                            $parts  = array_filter([$vColor, $vSize]);
                                            $vTitle = count($parts) > 0 ? implode(' / ', $parts) : ($vRecord->name ?? $vTitle);
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td class="text-wrap" style="min-width: 200px;">
                                        <strong class="text-dark d-block" style="font-size: 13px;">
                                            {{ $pName }}
                                        </strong>
                                        @if(!empty($vColor) || !empty($vSize) || !empty($vTitle))
                                            <div class="mt-1 d-flex flex-wrap gap-1">
                                                @if(!empty($vColor))
                                                    <span class="badge bg-dark text-white font-monospace px-1 py-0" style="font-size: 9px;">Color: {{ $vColor }}</span>
                                                @endif
                                                @if(!empty($vSize))
                                                    <span class="badge bg-primary text-white font-monospace px-1 py-0" style="font-size: 9px;">Size: {{ $vSize }}</span>
                                                @endif
                                                @if(!empty($vTitle) && empty($vColor) && empty($vSize))
                                                    <span class="badge bg-info-subtle text-dark border font-monospace px-1 py-0" style="font-size: 9px;">{{ $vTitle }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <small class="text-muted" style="font-size: 11px;">Standard</small>
                                        @endif
                                        @if(!empty($vSku))
                                            <small class="text-danger fw-bold d-block font-monospace bn-digit mt-1" style="font-size: 10px;">
                                                <i class="fas fa-barcode me-1"></i> SKU: {{ $vSku }}
                                            </small>
                                        @endif
                                    </td>
                                    <td class="font-monospace bn-digit">৳ {{ number_format($unitPrice, 2) }}</td>
                                    <td class="text-center font-monospace bn-digit">&times; {{ $qty }}</td>
                                    <td class="text-end pe-3 font-monospace fw-bold text-dark bn-digit">৳ {{ number_format($lineTotal, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted small">No items recorded in this order.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <td colspan="3" class="text-end fw-semibold small text-muted">Product Subtotal:</td>
                                <td class="text-end pe-3 font-monospace bn-digit">৳ {{ number_format($pSubtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end fw-semibold small text-muted">Delivery Charge:</td>
                                <td class="text-end pe-3 font-monospace bn-digit">৳ {{ number_format($dCharge, 2) }}</td>
                            </tr>
                            @if($discount > 0)
                                <tr>
                                    <td colspan="3" class="text-end fw-semibold small text-danger">Discount:</td>
                                    <td class="text-end pe-3 font-monospace bn-digit text-danger">- ৳ {{ number_format($discount, 2) }}</td>
                                </tr>
                            @endif
                            <tr class="border-top">
                                <td colspan="3" class="text-end fw-bold text-dark fs-6">Grand Total:</td>
                                <td class="text-end pe-3 font-monospace fw-bold text-primary fs-6 bn-digit">৳ {{ number_format($grand, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end small text-success fw-semibold">Paid Amount:</td>
                                <td class="text-end pe-3 font-monospace bn-digit text-success">৳ {{ number_format($paid, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end small text-danger fw-semibold">Due Amount:</td>
                                <td class="text-end pe-3 font-monospace bn-digit text-danger">৳ {{ number_format($due, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column: Status & Courier Controls -->
        <div class="col-12 col-lg-4">
            <!-- 1. Order Status & Payment Update -->
            <div class="order-card">
                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3" style="font-size: 13.5px;">Order Status &amp; Payment Control</h6>
                <form action="{{ route('admin.order.status.update', $order->id) }}" method="POST" id="orderStatusUpdateForm">
                    @csrf
                    <!-- Hidden Input for Proof Screenshot synced with Global Media Chooser -->
                    <input type="hidden" name="payment_screenshot" id="orderProofScreenshotInput" value="{{ $order->payment_screenshot ?? '' }}">

                    <div class="mb-2">
                        <label class="form-label">Order Lifecycle Status</label>
                        @php
                            $currOrdStatus = strtolower($order->order_status ?? 'pending');
                        @endphp
                        <select name="order_status" class="form-select">
                            <option value="pending" {{ $currOrdStatus == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="received" {{ $currOrdStatus == 'received' ? 'selected' : '' }}>Received</option>
                            <option value="confirmed" {{ $currOrdStatus == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="processing" {{ in_array($currOrdStatus, ['processing', 'packed']) ? 'selected' : '' }}>Processing (Packed)</option>
                            <option value="shipped" {{ $currOrdStatus == 'shipped' ? 'selected' : '' }}>Shipped</option>
                            <option value="attempt_to_deliver" {{ in_array($currOrdStatus, ['attempt_to_deliver', 'attempted']) ? 'selected' : '' }}>Attempt to Deliver</option>
                            <option value="delivered" {{ $currOrdStatus == 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="exchange" {{ in_array($currOrdStatus, ['exchange', 'exchanged']) ? 'selected' : '' }}>Exchange</option>
                            <option value="returned" {{ in_array($currOrdStatus, ['returned', 'return']) ? 'selected' : '' }}>Returned</option>
                            <option value="cancelled" {{ $currOrdStatus == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Payment Settlement Status</label>
                        @php
                            $currPayStatus = strtolower($order->payment_status ?? 'unpaid');
                        @endphp
                        <select name="payment_status" class="form-select">
                            <option value="unpaid" {{ $currPayStatus == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                            <option value="pending_verification" {{ $currPayStatus == 'pending_verification' ? 'selected' : '' }}>Pending Verification</option>
                            <option value="partially_paid" {{ in_array($currPayStatus, ['partially_paid', 'partial']) ? 'selected' : '' }}>Partially Paid</option>
                            <option value="paid" {{ $currPayStatus == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="refunded" {{ $currPayStatus == 'refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Transaction Reference ID</label>
                        <input type="text" name="transaction_id" class="form-control font-monospace bn-digit" value="{{ old('transaction_id', $order->transaction_id ?? '') }}" placeholder="TRX-98273462">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Internal Admin Note</label>
                        <textarea name="admin_note" rows="2" class="form-control" placeholder="Add processing instruction...">{{ old('admin_note', $order->admin_note ?? '') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2 shadow-xs d-flex align-items-center justify-content-center gap-1" style="min-height: 35px; font-size: 12px;">
                        <i class="fas fa-sync-alt me-1"></i>
                        <span>Update Status &amp; Save</span>
                    </button>
                </form>
            </div>

            <!-- 2. Courier Dispatch & Consignment Setup -->
            <div class="order-card">
                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3" style="font-size: 13.5px;">Courier Dispatch &amp; Consignment</h6>
                <form action="{{ route('admin.order.courier.send', $order->id) }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label">Select Active Courier</label>
                        <select name="courier" class="form-select">
                            @foreach($couriers as $cr)
                                <option value="{{ $cr->courier_code }}" {{ strtolower($order->courier_name ?? '') == strtolower($cr->courier_code) ? 'selected' : '' }}>
                                    {{ $cr->courier_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Custom Consignment / Tracking Code</label>
                        <input type="text" name="parcel_id" class="form-control font-monospace bn-digit" placeholder="Leave empty for auto-generation" value="{{ $order->courier_tracking_id ?? '' }}">
                        <small class="text-muted d-block mt-1" style="font-size: 10.5px;">Enter tracking code directly from courier portal if manually booked.</small>
                    </div>

                    <button type="submit" class="btn btn-warning text-dark w-100 fw-bold py-2 shadow-xs d-flex align-items-center justify-content-center gap-1" style="min-height: 35px; font-size: 12px;">
                        <i class="fas fa-truck-moving me-1"></i>
                        <span>Dispatch / Save Courier</span>
                    </button>
                </form>
            </div>

            <!-- 3. Customer Profile History -->
            <div class="order-card mb-0">
                <h6 class="fw-bold text-dark border-bottom pb-2 mb-2" style="font-size: 13.5px;">Customer Lifetime History</h6>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted small" style="font-size: 12px;">Completed Orders:</span>
                    <span class="fw-bold text-dark font-monospace bn-digit" style="font-size: 12px;">{{ $customerHistory['total_orders'] ?? 1 }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted small" style="font-size: 12px;">Total Lifetime Spend:</span>
                    <span class="fw-bold text-success font-monospace bn-digit" style="font-size: 12px;">৳ {{ number_format($customerHistory['total_revenue'] ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 4. Interactive Return & Exchange Inspection Modal                         -->
<!-- ========================================================================= -->
<div class="modal fade order-modal-scope" id="returnExchangeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-dark text-white py-2 px-3">
                <h6 class="modal-title fw-bold d-flex align-items-center gap-1" style="font-size: 13.5px;">
                    <i class="fas fa-arrows-rotate text-warning"></i>
                    <span>Return, Exchange &amp; Damage Inspection</span>
                </h6>
                <button type="button" class="btn-close btn-close-white shadow-none btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <form id="returnExchangeForm" onsubmit="handleReturnExchangeSubmit(event)">
                @csrf
                <div class="modal-body p-3">
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Process Action Type</label>
                            <select id="processTypeSelect" name="process_type" class="form-select" onchange="toggleExchangeSection(this.value)">
                                <option value="return">Pure Return &amp; Refund / Restock</option>
                                <option value="exchange">Product Exchange / Size Swap</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Inspection Note</label>
                            <input type="text" name="action_note" class="form-control" placeholder="Reason for return/exchange (e.g. wrong size)">
                        </div>
                    </div>

                    <!-- Step 1: Return Items Selection & Condition Inspection -->
                    <h6 class="fw-bold text-dark border-bottom pb-1 mb-2" style="font-size: 12px;">
                        <i class="fas fa-boxes-packing text-primary me-1"></i> Select Items Being Returned
                    </h6>
                    <div class="table-responsive mb-3 border">
                        <table class="table table-sm align-middle mb-0" style="font-size: 11.5px;">
                            <thead class="bg-light text-muted">
                                <tr>
                                    <th style="width: 30px;" class="text-center">Select</th>
                                    <th>Item</th>
                                    <th style="width: 80px;" class="text-center">Bought</th>
                                    <th style="width: 90px;" class="text-center">Return Qty</th>
                                    <th style="width: 150px;">Item Condition</th>
                                    <th style="width: 100px;" class="text-end pe-2">Return Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orderItems as $idx => $it)
                                    @php
                                        $itPrice = (float)($it->price ?? ($it->unit_price ?? 0));
                                        $itQty   = (int)($it->quantity ?? 1);
                                        $itTitle = $it->product_title ?? 'Product #' . $it->product_id;
                                    @endphp
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" class="form-check-input return-item-check m-0" data-index="{{ $idx }}" onchange="recalculateModalFigures()" style="cursor: pointer;">
                                            <input type="hidden" name="return_items[{{ $idx }}][item_id]" value="{{ $it->id }}">
                                        </td>
                                        <td>
                                            <strong class="d-block text-dark">{{ $itTitle }}</strong>
                                            <small class="text-muted bn-digit font-monospace">Unit: ৳{{ number_format($itPrice, 2) }}</small>
                                        </td>
                                        <td class="text-center font-monospace bn-digit">&times; {{ $itQty }}</td>
                                        <td class="text-center">
                                            <input type="number" name="return_items[{{ $idx }}][qty]" class="form-control form-control-sm font-monospace bn-digit text-center return-item-qty" value="1" min="1" max="{{ $itQty }}" data-price="{{ $itPrice }}" onchange="recalculateModalFigures()" disabled style="height: 28px; padding: 2px;">
                                        </td>
                                        <td>
                                            <select name="return_items[{{ $idx }}][condition]" class="form-select form-select-sm return-item-cond" disabled style="height: 28px; font-size: 11px;">
                                                <option value="restock" selected>Good (Restock to Sellable)</option>
                                                <option value="damaged">Damaged (Scrap / Write-off)</option>
                                            </select>
                                        </td>
                                        <td class="text-end pe-2 font-monospace fw-bold text-dark bn-digit line-return-val">৳ 0.00</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Step 2: Replacement Exchange Section -->
                    <div id="exchangeItemsSection" class="d-none">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-1 mb-2">
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 12px;">
                                <i class="fas fa-arrows-split-up-and-left text-success me-1"></i> Replacement Exchange Items
                            </h6>
                            <span class="badge bg-success-subtle text-success border px-2 py-0.5" style="font-size: 9.5px;">New Items to Send</span>
                        </div>

                        <div class="position-relative mb-2">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search" style="font-size: 10px;"></i></span>
                                <input type="text" id="exchangeSearchInput" class="form-control border-start-0" placeholder="Search replacement product or variant by title..." autocomplete="off" oninput="debounceExchangeSearch(this.value)">
                            </div>
                            <div id="exchangeSearchResultsPanel" class="search-results-panel d-none"></div>
                        </div>

                        <div class="table-responsive border mb-2">
                            <table class="table table-sm align-middle mb-0" style="font-size: 11.5px;">
                                <thead class="bg-light text-muted">
                                    <tr>
                                        <th>Replacement Product</th>
                                        <th style="width: 100px;">Unit Price (৳)</th>
                                        <th style="width: 80px;" class="text-center">Qty</th>
                                        <th style="width: 100px;" class="text-end">Total (৳)</th>
                                        <th style="width: 40px;" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="exchangeCartTableBody">
                                    <tr id="emptyExchangeCartRow">
                                        <td colspan="5" class="text-center py-3 text-muted small">No replacement items added yet. Search above to add.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-12 col-md-6">
                                <label class="form-label">Exchange Delivery Charge (৳):</label>
                                <input type="number" id="exchangeDeliveryInput" name="exchange_delivery_charge" class="form-control font-monospace bn-digit text-end" value="60" min="0" oninput="recalculateModalFigures()">
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Financial Rebalance Summary Box -->
                    <div class="bg-light p-2 border" style="border-radius: 5px;">
                        <div class="d-flex justify-content-between mb-1 small">
                            <span class="text-muted">Total Return Credit:</span>
                            <strong class="font-monospace bn-digit text-danger" id="summaryReturnCredit">৳ 0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1 small" id="summaryExchangeLine">
                            <span class="text-muted">Total Replacement Value (incl. delivery):</span>
                            <strong class="font-monospace bn-digit text-primary" id="summaryExchangeTotal">৳ 0.00</strong>
                        </div>
                        <div class="border-top pt-1 mt-1 d-flex justify-content-between align-items-center">
                            <strong class="text-dark small" id="balanceSummaryLabel">Net Customer Due Adjustment:</strong>
                            <span class="fw-bold fs-6 font-monospace bn-digit" id="summaryNetBalance">৳ 0.00</span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2 px-3">
                    <button type="button" class="btn btn-light border btn-compact px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="submitProcessBtn" class="btn btn-danger btn-compact px-4 shadow-xs">
                        <i class="fas fa-check-circle me-1"></i> Confirm &amp; Process
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 5. Payment Proof Zoom Lightbox Modal                                      -->
<!-- ========================================================================= -->
<div class="modal fade order-modal-scope" id="proofZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg text-center p-2">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" style="font-size: 12.5px;">
                    <i class="fas fa-image text-primary me-1"></i> Payment Receipt Slip
                </span>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2">
                <img id="modalZoomImgTag" src="" alt="Zoomed Receipt" class="img-fluid border" style="max-height: 480px; object-fit: contain; border-radius: 4px;">
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 6. Bootstrap Notice Dialog Modal                                          -->
<!-- ========================================================================= -->
<div class="modal fade order-modal-scope" id="orderNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mx-auto mb-2" id="orderNoticeIconWrap" style="width: 44px; height: 44px;">
                    <i class="fas fa-circle-info fs-4" id="orderNoticeIcon"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="orderNoticeTitle" style="font-size: 14px;">Notification</h6>
                <p class="text-muted small mb-3" id="orderNoticeMsg" style="font-size: 12px; line-height: 1.45;"></p>
                <button type="button" class="btn btn-dark btn-compact px-4" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
let exchangeCart = [];
let exchangeDebounceTimer = null;
window.lastExchangeProducts = [];

function showOrderNotice(message, title = 'Notification', type = 'info') {
    const titleEl = document.getElementById('orderNoticeTitle');
    const msgEl   = document.getElementById('orderNoticeMsg');
    const iconWrap = document.getElementById('orderNoticeIconWrap');
    const iconEl   = document.getElementById('orderNoticeIcon');

    if (titleEl) titleEl.innerText = title;
    if (msgEl) msgEl.innerText = message;

    if (iconWrap && iconEl) {
        if (type === 'error' || type === 'danger') {
            iconWrap.className = 'rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-2';
            iconEl.className = 'fas fa-circle-exclamation fs-4';
        } else if (type === 'success') {
            iconWrap.className = 'rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mx-auto mb-2';
            iconEl.className = 'fas fa-circle-check fs-4';
        } else {
            iconWrap.className = 'rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mx-auto mb-2';
            iconEl.className = 'fas fa-circle-info fs-4';
        }
    }

    const modalEl = document.getElementById('orderNoticeModal');
    if (modalEl && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

// Global Media Chooser Integration for Payment Proof Attachment
function extractMediaItem(mediaInput) {
    if (!mediaInput) return null;
    const item = Array.isArray(mediaInput) ? mediaInput[0] : mediaInput;
    if (!item || typeof item !== 'object') return null;
    const rawPath = item.file_path || item.path || item.url || '';
    const rawUrl  = item.url || item.file_path || item.path || '';
    if (!rawPath || rawPath === 'undefined') return null;
    return {
        path: String(rawPath).trim(),
        url:  String(rawUrl).trim()
    };
}

function choosePaymentProofMedia() {
    if (typeof window.openMediaPicker === 'function') {
        window.openMediaPicker({
            multiple: false,
            onSelect: function(media) {
                const picked = extractMediaItem(media);
                if (picked && picked.path) {
                    const inputHidden = document.getElementById('orderProofScreenshotInput');
                    const previewImg  = document.getElementById('paymentProofPreviewImg');
                    const statusText  = document.getElementById('proofStatusText');
                    const pathText    = document.getElementById('proofPathText');
                    const container   = document.getElementById('proofPreviewContainer');
                    const emptyNotice = document.getElementById('noProofAttachedNotice');

                    if (inputHidden) inputHidden.value = picked.path;
                    if (previewImg) previewImg.src = picked.url;
                    if (statusText) statusText.innerHTML = '<i class="fas fa-check-circle me-1"></i>Receipt Attached';
                    if (pathText) {
                        pathText.innerText = picked.path.split('/').pop() || picked.path;
                        pathText.title = picked.path;
                    }

                    if (container) {
                        container.classList.remove('d-none');
                        container.classList.add('d-block');
                    }
                    if (emptyNotice) {
                        emptyNotice.classList.add('d-none');
                        emptyNotice.classList.remove('d-block');
                    }

                    showOrderNotice('Payment screenshot attached from Media Library. Please click "Update Status & Save" to store changes.', 'Attached', 'success');
                }
            }
        });
    } else {
        showOrderNotice('Media Library is loading or not available on this layout.', 'Notice');
    }
}

function clearPaymentProofMedia() {
    const inputHidden = document.getElementById('orderProofScreenshotInput');
    const container   = document.getElementById('proofPreviewContainer');
    const emptyNotice = document.getElementById('noProofAttachedNotice');

    if (inputHidden) inputHidden.value = '';
    if (container) {
        container.classList.add('d-none');
        container.classList.remove('d-block');
    }
    if (emptyNotice) {
        emptyNotice.classList.remove('d-none');
        emptyNotice.classList.add('d-block');
    }

    showOrderNotice('Payment proof removed. Click "Update Status & Save" to confirm.', 'Notice');
}

function viewProofZoom() {
    const previewImg = document.getElementById('paymentProofPreviewImg');
    const zoomImgTag = document.getElementById('modalZoomImgTag');
    if (previewImg && zoomImgTag && previewImg.src) {
        zoomImgTag.src = previewImg.src;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('proofZoomModal')).show();
    }
}

function openReturnExchangeModal() {
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('returnExchangeModal'));
    modal.show();
}

function toggleExchangeSection(type) {
    const section = document.getElementById('exchangeItemsSection');
    const submitBtn = document.getElementById('submitProcessBtn');
    if (type === 'exchange') {
        section.classList.remove('d-none');
        submitBtn.className = 'btn btn-primary btn-compact px-4 shadow-xs';
        submitBtn.innerHTML = '<i class="fas fa-arrows-rotate me-1"></i> Execute Exchange';
    } else {
        section.classList.add('d-none');
        submitBtn.className = 'btn btn-danger btn-compact px-4 shadow-xs';
        submitBtn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Confirm Return &amp; Restock';
    }
    recalculateModalFigures();
}

document.querySelectorAll('.return-item-check').forEach(cb => {
    cb.addEventListener('change', function() {
        const row = this.closest('tr');
        const qtyIn = row.querySelector('.return-item-qty');
        const condIn = row.querySelector('.return-item-cond');

        if (this.checked) {
            qtyIn.disabled = false;
            condIn.disabled = false;
        } else {
            qtyIn.disabled = true;
            condIn.disabled = true;
        }
        recalculateModalFigures();
    });
});

function recalculateModalFigures() {
    let totalReturn = 0;
    document.querySelectorAll('.return-item-check').forEach(cb => {
        const row = cb.closest('tr');
        const lineValEl = row.querySelector('.line-return-val');
        if (cb.checked) {
            const qty = parseInt(row.querySelector('.return-item-qty').value, 10) || 1;
            const price = parseFloat(row.querySelector('.return-item-qty').dataset.price) || 0;
            const lineVal = qty * price;
            lineValEl.innerText = '৳ ' + lineVal.toFixed(2);
            totalReturn += lineVal;
        } else {
            lineValEl.innerText = '৳ 0.00';
        }
    });

    document.getElementById('summaryReturnCredit').innerText = '৳ ' + totalReturn.toFixed(2);

    const isExchange = document.getElementById('processTypeSelect').value === 'exchange';
    let exchangeTotal = 0;

    if (isExchange) {
        exchangeCart.forEach(it => {
            exchangeTotal += (it.unit_price * it.qty);
        });
        const deliv = parseFloat(document.getElementById('exchangeDeliveryInput').value) || 0;
        exchangeTotal += deliv;

        document.getElementById('summaryExchangeTotal').innerText = '৳ ' + exchangeTotal.toFixed(2);
        document.getElementById('summaryExchangeLine').classList.remove('d-none');

        const net = exchangeTotal - totalReturn;
        const balanceLabel = document.getElementById('balanceSummaryLabel');
        const balanceVal = document.getElementById('summaryNetBalance');

        if (net >= 0) {
            balanceLabel.innerText = 'Additional Due Payable by Customer:';
            balanceVal.innerText = '৳ ' + net.toFixed(2);
            balanceVal.className = 'fw-bold fs-6 font-monospace bn-digit text-danger';
        } else {
            balanceLabel.innerText = 'Refund / Credit to Customer:';
            balanceVal.innerText = '৳ ' + Math.abs(net).toFixed(2);
            balanceVal.className = 'fw-bold fs-6 font-monospace bn-digit text-success';
        }
    } else {
        document.getElementById('summaryExchangeLine').classList.add('d-none');
        document.getElementById('balanceSummaryLabel').innerText = 'Total Refund Value:';
        document.getElementById('summaryNetBalance').innerText = '৳ ' + totalReturn.toFixed(2);
        document.getElementById('summaryNetBalance').className = 'fw-bold fs-6 font-monospace bn-digit text-success';
    }
}

function debounceExchangeSearch(val) {
    clearTimeout(exchangeDebounceTimer);
    const q = val.trim();
    const panel = document.getElementById('exchangeSearchResultsPanel');

    if (q.length < 1) {
        panel.classList.add('d-none');
        panel.innerHTML = '';
        return;
    }

    exchangeDebounceTimer = setTimeout(() => {
        fetch(`{{ route('admin.order.search_products') }}?q=${encodeURIComponent(q)}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            window.lastExchangeProducts = data || [];
            if (!data || data.length === 0) {
                panel.innerHTML = `<div class="p-2 text-center text-muted small">No product found</div>`;
                panel.classList.remove('d-none');
                return;
            }

            let html = '';
            data.forEach((p, pIdx) => {
                if (p.has_variants && p.variants.length > 0) {
                    p.variants.forEach((v, vIdx) => {
                        html += `
                            <div class="search-result-item d-flex justify-content-between align-items-center" onclick="addReplacementItem(${pIdx}, ${vIdx})">
                                <div>
                                    <strong class="d-block text-dark" style="font-size: 11.5px;">${p.title}</strong>
                                    <small class="text-primary font-monospace bn-digit" style="font-size: 10px;">Variant: ${v.title} &bull; Stock: ${v.stock}</small>
                                </div>
                                <span class="fw-bold font-monospace bn-digit text-dark" style="font-size: 11.5px;">৳ ${parseFloat(v.price).toFixed(2)}</span>
                            </div>
                        `;
                    });
                } else {
                    html += `
                        <div class="search-result-item d-flex justify-content-between align-items-center" onclick="addReplacementItem(${pIdx}, null)">
                            <div>
                                <strong class="d-block text-dark" style="font-size: 11.5px;">${p.title}</strong>
                                <small class="text-muted font-monospace bn-digit" style="font-size: 10px;">Stock: ${p.stock}</small>
                            </div>
                            <span class="fw-bold font-monospace bn-digit text-dark" style="font-size: 11.5px;">৳ ${parseFloat(p.price).toFixed(2)}</span>
                        </div>
                    `;
                }
            });

            panel.innerHTML = html;
            panel.classList.remove('d-none');
        })
        .catch(err => console.error(err));
    }, 250);
}

function addReplacementItem(pIdx, vIdx) {
    const p = window.lastExchangeProducts[pIdx];
    if (!p) return;

    let vId = null;
    let label = p.title;
    let price = p.price;

    if (vIdx !== null && p.variants && p.variants[vIdx]) {
        const v = p.variants[vIdx];
        vId = v.variant_id;
        label = p.title + ' (' + v.title + ')';
        price = v.price;
    }

    exchangeCart.push({
        product_id: p.product_id,
        variant_id: vId,
        title: label,
        unit_price: parseFloat(price),
        qty: 1
    });

    document.getElementById('exchangeSearchInput').value = '';
    const panel = document.getElementById('exchangeSearchResultsPanel');
    panel.classList.add('d-none');
    panel.innerHTML = '';

    renderExchangeCart();
    recalculateModalFigures();
}

function renderExchangeCart() {
    const tbody = document.getElementById('exchangeCartTableBody');
    if (exchangeCart.length === 0) {
        tbody.innerHTML = `<tr id="emptyExchangeCartRow"><td colspan="5" class="text-center py-3 text-muted small">No replacement items added yet. Search above to add.</td></tr>`;
        return;
    }

    let html = '';
    exchangeCart.forEach((it, idx) => {
        const line = it.unit_price * it.qty;
        html += `
            <tr>
                <td><strong class="text-dark">${it.title}</strong></td>
                <td><input type="number" class="form-control form-control-sm font-monospace bn-digit text-end" value="${it.unit_price}" step="any" min="0" onchange="updateExchangePrice(${idx}, this.value)" style="height: 28px;"></td>
                <td class="text-center"><input type="number" class="form-control form-control-sm font-monospace bn-digit text-center" value="${it.qty}" min="1" onchange="updateExchangeQty(${idx}, this.value)" style="height: 28px;"></td>
                <td class="text-end font-monospace bn-digit fw-bold text-dark">৳ ${line.toFixed(2)}</td>
                <td class="text-center"><button type="button" class="btn btn-outline-danger btn-compact py-0 px-1" onclick="removeExchangeItem(${idx})"><i class="fas fa-trash-alt"></i></button></td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}

function updateExchangePrice(idx, val) {
    const p = parseFloat(val);
    if (!isNaN(p) && p >= 0) {
        exchangeCart[idx].unit_price = p;
        renderExchangeCart();
        recalculateModalFigures();
    }
}

function updateExchangeQty(idx, val) {
    const q = parseInt(val, 10);
    if (!isNaN(q) && q > 0) {
        exchangeCart[idx].qty = q;
        renderExchangeCart();
        recalculateModalFigures();
    }
}

function removeExchangeItem(idx) {
    exchangeCart.splice(idx, 1);
    renderExchangeCart();
    recalculateModalFigures();
}

function handleReturnExchangeSubmit(e) {
    e.preventDefault();

    const selectedChecks = document.querySelectorAll('.return-item-check:checked');
    if (selectedChecks.length === 0) {
        showOrderNotice('Please select at least one item to return.', 'Action Required', 'error');
        return;
    }

    const processType = document.getElementById('processTypeSelect').value;
    if (processType === 'exchange' && exchangeCart.length === 0) {
        showOrderNotice('Please search and add at least one replacement item for exchange.', 'Action Required', 'error');
        return;
    }

    const returnItems = [];
    selectedChecks.forEach(cb => {
        const row = cb.closest('tr');
        returnItems.push({
            item_id: parseInt(row.querySelector('input[type="hidden"]').value, 10),
            qty: parseInt(row.querySelector('.return-item-qty').value, 10) || 1,
            condition: row.querySelector('.return-item-cond').value
        });
    });

    const payload = {
        process_type: processType,
        return_items: returnItems,
        exchange_items: exchangeCart,
        exchange_delivery_charge: parseFloat(document.getElementById('exchangeDeliveryInput')?.value) || 0,
        action_note: document.querySelector('input[name="action_note"]').value
    };

    const submitBtn = document.getElementById('submitProcessBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';

    fetch("{{ route('admin.order.process_return_exchange', $order->id) }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(async res => {
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Action failed.');
        return data;
    })
    .then(data => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('returnExchangeModal'));
        if (modal) modal.hide();
        showOrderNotice(data.message || 'Action completed successfully.', 'Success', 'success');
        setTimeout(() => window.location.reload(), 1200);
    })
    .catch(err => {
        showOrderNotice('Error: ' + err.message, 'Failed', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Confirm &amp; Process';
    });
}

document.addEventListener('click', function(e) {
    const exPanel = document.getElementById('exchangeSearchResultsPanel');
    const exInput = document.getElementById('exchangeSearchInput');
    if (exPanel && !exPanel.contains(e.target) && e.target !== exInput) {
        exPanel.classList.add('d-none');
    }
});
</script>
@endsection