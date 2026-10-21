@extends('layouts.admin')

@section('title', 'New Courier Booking - ' . config('app.name'))

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #courier-booking-root .btn,
    #courier-booking-root .btn-sm,
    #courier-booking-root button,
    #courier-booking-root .form-control,
    #courier-booking-root .form-select,
    #courier-booking-root .badge,
    #courier-booking-root .section-badge,
    #courier-booking-root .booking-card,
    #courier-booking-root .financial-calc-box,
    #courier-booking-root .manual-courier-box,
    #courier-booking-root .input-group-text,
    #courier-booking-root .proof-thumb-box,
    #courier-booking-root .proof-media-surface,
    .courier-modal-scope .modal-content,
    .courier-modal-scope .btn,
    .courier-modal-scope .form-control,
    .courier-modal-scope .form-select,
    .courier-modal-scope .badge,
    .courier-modal-scope .alert {
        border-radius: 5px !important;
    }

    #courier-booking-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #courier-booking-root .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Safe Bengali Digit & Monospace Rendering */
    .bn-digit, 
    .stat-amount, 
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Compact Button Styling */
    .btn-compact {
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

    /* Enterprise Card Styling */
    .booking-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        padding: 16px;
        margin-bottom: 16px;
    }

    .section-badge {
        font-size: 10px;
        font-weight: 700;
        padding: 3px 8px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    /* Form Controls */
    #courier-booking-root .form-control,
    #courier-booking-root .form-select {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        color: #0f172a;
        box-shadow: none;
        padding: 5px 10px;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #courier-booking-root .form-control:focus,
    #courier-booking-root .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Order Live Search Dropdown */
    .order-search-results-dropdown {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        z-index: 1060;
        max-height: 260px;
        overflow-y: auto;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 5px !important;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.12);
        display: none;
    }
    .order-search-item {
        padding: 8px 12px;
        cursor: pointer;
        border-bottom: 1px solid #f1f5f9;
        transition: background-color 0.15s ease;
    }
    .order-search-item:hover {
        background-color: #f8fafc;
    }

    /* Table Architecture: 100% Fit Screen on PC with Zero Horizontal Scroll */
    .booking-items-table {
        width: 100%;
        margin-bottom: 0;
    }
    .booking-items-table thead th {
        font-size: 10px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #475569;
        background-color: #f8fafc;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 8px 6px;
        white-space: nowrap;
    }
    .booking-items-table tbody td {
        padding: 7px 6px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        font-size: 11.5px;
        vertical-align: middle;
    }

    @media (min-width: 992px) {
        .booking-items-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .booking-items-table th,
        .booking-items-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }
        .col-prod-title  { width: 52%; }
        .col-prod-qty    { width: 12%; }
        .col-prod-weight { width: 16%; }
        .col-prod-price  { width: 20%; }
    }

    /* Financial Calculation Box */
    .financial-calc-box {
        padding: 12px 14px;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        margin-bottom: 16px;
    }

    .manual-courier-box {
        padding: 12px 14px;
        background-color: #fffbeb;
        border: 1px solid #fef3c7;
        margin-bottom: 16px;
    }

    /* Global Media Chooser Component Styles */
    .proof-media-surface {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        padding: 10px;
        transition: all 0.2s ease;
    }
    .proof-media-surface:hover {
        border-color: #2563eb;
        background: #f1f5f9;
    }
    .proof-thumb-box {
        width: 32px;
        height: 32px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
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
        background: rgba(15, 23, 42, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.15s ease;
    }
    .proof-thumb-box:hover .proof-thumb-hover-overlay {
        opacity: 1;
    }

    /* Mobile Responsive Optimizations (< 768px) */
    @media (max-width: 767.98px) {
        .booking-card {
            padding: 12px !important;
            margin-bottom: 12px !important;
        }
        .financial-calc-box,
        .manual-courier-box {
            padding: 12px !important;
            margin-bottom: 12px !important;
        }
        .header-action-btn-group {
            width: 100% !important;
        }
        .header-action-btn-group .btn {
            width: 100% !important;
            justify-content: center !important;
            padding: 7px 10px !important;
        }
        .pc-fit-container {
            width: 100%;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="courier-booking-root">
    <!-- Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-truck-fast text-primary"></i> <span>Courier Parcel Booking</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Select an existing order to auto-populate customer details, calculate weight, attach vouchers &amp; dispatch directly.
            </small>
        </div>
        <div class="header-action-btn-group">
            <a href="{{ route('admin.courier.index') }}" class="btn btn-outline-secondary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-arrow-left me-1"></i> <span>Back to Courier Orders</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 small border-0 shadow-sm d-flex align-items-center mb-3" role="alert">
            <i class="fas fa-circle-check me-2 fs-5 text-success"></i> 
            <span class="fw-semibold">{{ session('success') }}</span>
            <button type="button" class="btn-close shadow-none py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 small border-0 shadow-sm d-flex align-items-center mb-3" role="alert">
            <i class="fas fa-triangle-exclamation me-2 fs-5 text-danger"></i> 
            <span class="fw-semibold">{{ session('error') }}</span>
            <button type="button" class="btn-close shadow-none py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.courier.booking.store') }}" method="POST" id="courierBookingForm" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="order_id" id="booking_order_id" value="{{ $order->id ?? '' }}">
        <input type="hidden" name="consignment_slip_media" id="consignment_slip_media" value="">

        <div class="row g-3">
            <!-- Left Column: Order Selection & Details -->
            <div class="col-12 col-lg-7">
                <!-- 1. Live Order Lookup Box -->
                <div class="booking-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-bold text-dark small mb-0" style="font-size: 12px;">
                            <i class="fas fa-magnifying-glass text-primary me-1"></i> Select Existing Order
                        </label>
                        <span class="section-badge bg-primary-subtle text-primary border border-primary-subtle">Step 1: Order Lookup</span>
                    </div>

                    <div class="position-relative">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-search" style="font-size: 11px;"></i></span>
                            <input type="text" id="orderLiveSearchInput" class="form-control border-start-0 ps-0 shadow-none" 
                                   placeholder="Click or type to search by Customer, Phone, or Invoice (#)..." 
                                   value="{{ $order ? ('#' . ($order->invoice_no ?? $order->id) . ' - ' . ($order->shipping_name ?? 'Customer') . ' (' . ($order->shipping_phone ?? '') . ')') : '' }}"
                                   autocomplete="off">
                            <button class="btn btn-light border text-muted px-2" type="button" id="clearOrderSearchBtn" style="{{ $order ? '' : 'display:none;' }}" onclick="clearSelectedOrder()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div id="orderSearchResultsDropdown" class="order-search-results-dropdown shadow-lg"></div>
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size: 10.5px;">
                        * Only active unbooked orders are shown. Delivered, cancelled or booked orders are excluded.
                    </small>
                </div>

                <!-- 2. Customer & Delivery Information -->
                <div class="booking-card">
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <div>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 13.5px;">Customer &amp; Shipping Information</h6>
                            <small class="text-muted" style="font-size: 10.5px;">Auto-synced from checkout; editable before dispatch</small>
                        </div>
                        <span class="section-badge bg-info-subtle text-info border border-info-subtle">Recipient</span>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-semibold text-muted mb-1" style="font-size: 11px;">Recipient Name *</label>
                            <input type="text" name="recipient_name" id="cust_name" class="form-control form-control-sm shadow-none" required 
                                   value="{{ $order->shipping_name ?? ($order->customer_name ?? '') }}">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-semibold text-muted mb-1" style="font-size: 11px;">Recipient Phone *</label>
                            <input type="text" name="recipient_phone" id="cust_phone" class="form-control form-control-sm font-monospace fw-bold bn-digit shadow-none" required 
                                   value="{{ $order->shipping_phone ?? ($order->customer_phone ?? '') }}">
                        </div>
                    </div>

                    <div>
                        <label class="form-label small fw-semibold text-muted mb-1" style="font-size: 11px;">Delivery Address *</label>
                        <textarea name="recipient_address" id="cust_address" class="form-control form-control-sm shadow-none" rows="2" style="font-size: 12px;" required>{{ $order->shipping_address ?? ($order->customer_address ?? '') }}</textarea>
                    </div>
                </div>

                <!-- 3. Product Details in this Order (Desktop 100% Fit Screen with Zero Horizontal Scroll) -->
                <div class="booking-card mb-0">
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <h6 class="fw-bold text-dark mb-0" style="font-size: 13.5px;">Package Content &amp; Items</h6>
                        <span class="section-badge bg-secondary-subtle text-secondary border border-secondary-subtle bn-digit font-monospace" id="itemsCountBadge">
                            {{ count($orderItems) }} Item(s)
                        </span>
                    </div>

                    <div class="table-responsive pc-fit-container">
                        <table class="table table-sm align-middle mb-0 booking-items-table">
                            <thead>
                                <tr>
                                    <th class="ps-2 col-prod-title">Product Title</th>
                                    <th class="text-center col-prod-qty">Qty</th>
                                    <th class="text-center col-prod-weight">Weight</th>
                                    <th class="text-end pe-2 col-prod-price">Price</th>
                                </tr>
                            </thead>
                            <tbody id="bookingItemsTbody">
                                @forelse($orderItems as $it)
                                    <tr>
                                        <td class="ps-2 col-prod-title">
                                            <strong class="text-dark d-block text-truncate" style="font-size: 11.5px;" title="{{ $it->title }}">{{ $it->title }}</strong>
                                            @if(!empty($it->product_code))
                                                <small class="text-muted font-monospace bn-digit d-block text-truncate" style="font-size: 9.5px;">Code: {{ $it->product_code }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center fw-bold bn-digit font-monospace col-prod-qty">{{ $it->quantity }}</td>
                                        <td class="text-center font-monospace bn-digit col-prod-weight">{{ $it->weight ? ($it->weight . ' kg') : '0.5 kg' }}</td>
                                        <td class="text-end pe-2 font-monospace bn-digit col-prod-price">৳{{ number_format((float)$it->price, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr id="noItemRow">
                                        <td colspan="4" class="text-center py-3 text-muted small">No order selected yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Column: Courier Selection, Financials & Dispatch -->
            <div class="col-12 col-lg-5">
                <div class="booking-card mb-0">
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <h6 class="fw-bold text-dark mb-0" style="font-size: 13.5px;">Courier Dispatch Setup</h6>
                        <span class="section-badge bg-success-subtle text-success border border-success-subtle">Step 2: Dispatch</span>
                    </div>

                    <!-- Courier Provider Select -->
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-dark mb-1" style="font-size: 11.5px;">Courier Provider *</label>
                        <select name="courier_name" id="courier_provider_select" class="form-select form-select-sm fw-semibold shadow-none" required onchange="onCourierProviderChange()">
                            <option value="">-- Choose Courier Provider --</option>
                            <optgroup label="Automated API Providers">
                                @foreach($couriers as $cr)
                                    @php
                                        $cNameLower = strtolower($cr->courier_name);$isApiGateway = false;
                                        foreach (['steadfast', 'pathao', 'redx'] as $gateway) {
                                            if (str_contains($cNameLower, $gateway)) {$isApiGateway = true;
                                                break;
                                            }
                                        }
                                    @endphp
                                    @if($isApiGateway)
                                        <option value="{{ $cr->courier_name }}" {{ str_contains($cNameLower, 'steadfast') ? 'selected' : '' }}>
                                            {{ $cr->courier_name }} (Automated API)
                                        </option>
                                    @endif
                                @endforeach
                            </optgroup>
                            <optgroup label="Saved Couriers &amp; Local Riders" id="savedCouriersOptgroup">
                                @foreach($couriers as $cr)
                                    @php
                                        $cNameLower = strtolower($cr->courier_name);$isApiGateway = false;
                                        foreach (['steadfast', 'pathao', 'redx'] as $gateway) {
                                            if (str_contains($cNameLower, $gateway)) {$isApiGateway = true;
                                                break;
                                            }
                                        }
                                    @endphp
                                    @if(!$isApiGateway)
                                        <option value="{{ $cr->courier_name }}">
                                            {{ $cr->courier_name }} (Manual)
                                        </option>
                                    @endif
                                @endforeach
                            </optgroup>
                            <optgroup label="Custom / New Courier Service">
                                <option value="manual_new">+ Add New Manual Courier...</option>
                            </optgroup>
                        </select>
                    </div>

                    <!-- Service Type & Calculated Weight -->
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-muted mb-1" style="font-size: 11px;">Service Type</label>
                            <select name="service_type" class="form-select form-select-sm shadow-none">
                                <option value="regular" selected>Regular (48h)</option>
                                <option value="express">Express (24h)</option>
                                <option value="same_day">Same Day</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-muted mb-1" style="font-size: 11px;">Total Weight (KG) *</label>
                            @php
                                $calcW = 0;
                                foreach($orderItems as $it) {$calcW += (((float)($it->weight ?: 0.5)) * ((int)$it->quantity));
                                }
                            @endphp
                            <input type="number" step="0.1" min="0.1" name="parcel_weight" id="parcel_weight_input" 
                                   class="form-control form-control-sm font-monospace fw-bold bn-digit shadow-none" 
                                   value="{{ max(0.5, round($calcW, 2)) }}" required>
                        </div>
                    </div>

                    <!-- Financial & COD Calculation Box -->
                    <div class="financial-calc-box">
                        <div class="d-flex justify-content-between mb-1 small">
                            <span class="text-muted" style="font-size: 11px;">Order Total:</span>
                            <strong class="text-dark font-monospace bn-digit">৳<span id="label_grand_total">{{ number_format($order->grand_total ?? 0, 2) }}</span></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1 small">
                            <span class="text-muted" style="font-size: 11px;">Paid / Advance:</span>
                            <strong class="text-success font-monospace bn-digit">৳<span id="label_paid_amount">{{ number_format($order->paid_amount ?? 0, 2) }}</span></strong>
                        </div>
                        <div class="border-top my-1"></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark small" style="font-size: 11.5px;">Collectible COD:</span>
                            @php
                                $cCod = max(0, ((float)($order->grand_total ?? 0)) - ((float)($order->paid_amount ?? 0)));
                            @endphp
                            <h5 class="fw-bold text-primary mb-0 font-monospace bn-digit" style="font-size: 15px;">৳<span id="label_cod_amount">{{ number_format($cCod, 2) }}</span></h5>
                        </div>
                    </div>

                    <!-- Manual Courier Details Section -->
                    <div id="manualCourierFieldsSection" class="manual-courier-box" style="display: none;">
                        <h6 class="fw-bold text-dark small mb-2 d-flex align-items-center gap-1" style="font-size: 12px;">
                            <i class="fas fa-truck-moving text-warning me-1"></i> <span>Manual Courier Configuration</span>
                        </h6>
                        
                        <!-- New Courier Name Input -->
                        <div class="mb-2" id="newCourierNameWrapper" style="display: none;">
                            <label class="form-label small text-muted mb-1" style="font-size: 10.5px;">Courier Service Name *</label>
                            <input type="text" name="new_manual_courier_name" id="new_manual_courier_name" class="form-control form-control-sm shadow-none" placeholder="Sundarban / SA Paribahan / Rider">
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small text-muted mb-1" style="font-size: 10.5px;">Hub / Branch</label>
                                <input type="text" name="manual_branch" id="manual_branch_input" class="form-control form-control-sm shadow-none" placeholder="Motijheel Hub">
                            </div>
                            <div class="col-6">
                                <label class="form-label small text-muted mb-1" style="font-size: 10.5px;">Contact Phone</label>
                                <input type="text" name="manual_phone" id="manual_phone_input" class="form-control form-control-sm font-monospace bn-digit shadow-none" placeholder="01XXXXXXXXX">
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small text-muted mb-1" style="font-size: 10.5px;">CN Number / Tracking ID</label>
                            <input type="text" name="tracking_code" id="manual_tracking_code_input" class="form-control form-control-sm font-monospace bn-digit shadow-none" placeholder="CN-984321">
                        </div>

                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small text-muted mb-1" style="font-size: 10.5px;">Courier Cost (৳)</label>
                                <input type="number" step="1" min="0" name="courier_cost" id="manual_courier_cost_input" class="form-control form-control-sm font-monospace bn-digit fw-semibold shadow-none" value="0">
                            </div>
                            <div class="col-6">
                                <label class="form-label small text-muted mb-1" style="font-size: 10.5px;">Cost Paid (৳)</label>
                                <input type="number" step="1" min="0" name="courier_cost_paid" id="manual_courier_cost_paid_input" class="form-control form-control-sm font-monospace bn-digit shadow-none" value="0">
                            </div>
                        </div>
                    </div>

                    <!-- 200% Modern Global Media Chooser Component: Waybill / Parcel Slip / Consignment Proof -->
                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-muted mb-1 d-flex justify-content-between align-items-center" style="font-size: 11px;">
                            <span><i class="fas fa-paperclip text-primary me-1"></i> Consignment Slip / Parcel Proof (Optional)</span>
                            <span class="badge bg-light text-muted border font-monospace" style="font-size: 8.5px;">GLOBAL MEDIA</span>
                        </label>
                        <div class="proof-media-surface">
                            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                <button type="button" class="btn btn-outline-primary btn-compact shadow-xs" onclick="chooseBookingProofMedia()">
                                    <i class="fas fa-photo-film me-1"></i>
                                    <span>Choose from Media Library</span>
                                </button>
                                <span class="small text-muted" style="font-size: 10.5px;">or upload directly:</span>
                            </div>
                            <input type="file" name="consignment_slip_file" id="booking_screenshot_file" class="form-control form-control-sm shadow-none" accept="image/*" onchange="previewBookingLocalProof(this)">

                            <div id="booking_proof_preview_wrap" class="mt-2 d-none align-items-center gap-2 bg-white border p-1" style="border-radius: 5px;">
                                <div class="proof-thumb-box" onclick="zoomBookingProof()" title="Click to Zoom Slip">
                                    <img id="booking_proof_preview_img" src="" alt="Proof Preview">
                                    <div class="proof-thumb-hover-overlay">
                                        <i class="fas fa-search-plus text-white" style="font-size: 9px;"></i>
                                    </div>
                                </div>
                                <div class="min-w-0 flex-grow-1">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 8.5px;">
                                        <i class="fas fa-check-circle me-1"></i>Slip Attached
                                    </span>
                                    <small class="text-muted d-block text-truncate font-monospace" id="booking_proof_preview_txt" style="font-size: 9px;"></small>
                                </div>
                                <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-auto text-decoration-none fw-bold" style="font-size: 11px;" onclick="clearBookingProofMedia()">&times; Clear</button>
                            </div>
                        </div>
                    </div>

                    <!-- Special Instruction / Notes -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted mb-1" style="font-size: 11px;">Handling Notes &amp; Instructions</label>
                        <textarea name="note" id="booking_note_input" class="form-control form-control-sm shadow-none" rows="2" style="font-size: 12px;" placeholder="Fragile item, call recipient before delivery...">{{ $order->admin_note ?? '' }}</textarea>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-xs" id="btnSubmitBooking" style="min-height: 36px; font-size: 13px;">
                        <i class="fas fa-paper-plane"></i> <span>Confirm &amp; Book Parcel</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal 1: Responsive Alert Modal -->
<div class="modal fade courier-modal-scope" id="courierAlertModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-2">
                <div class="mb-2" id="alertModalIcon">
                    <i class="fas fa-triangle-exclamation text-warning fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="alertModalTitle" style="font-size: 14px;">Notice</h6>
                <p class="text-muted small mb-3" id="alertModalMessage" style="font-size: 12px; line-height: 1.45;">Notification message.</p>
                <button type="button" class="btn btn-primary btn-compact px-4" data-bs-dismiss="modal">Acknowledge</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Lightbox Zoom Modal for Consignment Slips & Media Proof -->
<div class="modal fade courier-modal-scope" id="bookingProofZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 1080;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg text-center p-2">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" style="font-size: 12.5px;">
                    <i class="fas fa-image text-primary me-1"></i> Consignment Slip Preview
                </span>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2">
                <img id="bookingProofZoomImgTag" src="" alt="Proof Preview" class="img-fluid border" style="max-height: 480px; object-fit: contain; border-radius: 5px;">
            </div>
        </div>
    </div>
</div>

<!-- Live Order Search & Auto-Fill Script -->
<script>
    let searchDebounce = null;
    const searchInput = document.getElementById('orderLiveSearchInput');
    const dropdown = document.getElementById('orderSearchResultsDropdown');
    const clearBtn = document.getElementById('clearOrderSearchBtn');

    function showCourierModalAlert(message, type = 'warning', title = 'Notice') {
        const titleEl = document.getElementById('alertModalTitle');
        const msgEl   = document.getElementById('alertModalMessage');
        const iconEl  = document.getElementById('alertModalIcon');

        titleEl.innerText = title;
        msgEl.innerText   = message;

        if (type === 'danger') {
            iconEl.innerHTML = '<i class="fas fa-circle-xmark text-danger fa-2x"></i>';
        } else if (type === 'success') {
            iconEl.innerHTML = '<i class="fas fa-circle-check text-success fa-2x"></i>';
        } else {
            iconEl.innerHTML = '<i class="fas fa-triangle-exclamation text-warning fa-2x"></i>';
        }

        const modal = new bootstrap.Modal(document.getElementById('courierAlertModal'));
        modal.show();
    }

    // Global Media Chooser Integration
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

    function chooseBookingProofMedia() {
        if (typeof window.openMediaPicker === 'function') {
            window.openMediaPicker({
                multiple: false,
                onSelect: function(media) {
                    const picked = extractMediaItem(media);
                    if (picked && picked.path) {
                        document.getElementById('consignment_slip_media').value = picked.path;
                        const fileEl = document.getElementById('booking_screenshot_file');
                        if (fileEl) fileEl.value = '';
                        const wrap = document.getElementById('booking_proof_preview_wrap');
                        const img  = document.getElementById('booking_proof_preview_img');
                        const txt  = document.getElementById('booking_proof_preview_txt');
                        if (img) img.src = picked.url;
                        if (txt) txt.innerText = picked.path.split('/').pop() || picked.path;
                        if (wrap) {
                            wrap.classList.remove('d-none');
                            wrap.classList.add('d-flex');
                        }
                    }
                }
            });
        } else {
            showCourierModalAlert('Global Media Library is loading, please try again.', 'warning', 'Notice');
        }
    }

    function previewBookingLocalProof(input) {
        if (input.files && input.files[0]) {
            document.getElementById('consignment_slip_media').value = '';
            const reader = new FileReader();
            reader.onload = function(e) {
                const wrap = document.getElementById('booking_proof_preview_wrap');
                const img  = document.getElementById('booking_proof_preview_img');
                const txt  = document.getElementById('booking_proof_preview_txt');
                if (img) img.src = e.target.result;
                if (txt) txt.innerText = input.files[0].name;
                if (wrap) {
                    wrap.classList.remove('d-none');
                    wrap.classList.add('d-flex');
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function clearBookingProofMedia() {
        document.getElementById('consignment_slip_media').value = '';
        const fileEl = document.getElementById('booking_screenshot_file');
        if (fileEl) fileEl.value = '';
        const wrap = document.getElementById('booking_proof_preview_wrap');
        if (wrap) {
            wrap.classList.add('d-none');
            wrap.classList.remove('d-flex');
        }
    }

    function zoomBookingProof() {
        const img = document.getElementById('booking_proof_preview_img');
        const zoomImgTag = document.getElementById('bookingProofZoomImgTag');
        if (img && img.src && zoomImgTag) {
            zoomImgTag.src = img.src;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('bookingProofZoomModal')).show();
        }
    }

    function performOrderSearch(query = '') {
        dropdown.innerHTML = '<div class="p-2 text-center text-muted small"><i class="fas fa-spinner fa-spin me-1"></i> Searching available orders...</div>';
        dropdown.style.display = 'block';

        fetch(`{{ route('admin.courier.search.order') }}?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(orders => {
                dropdown.innerHTML = '';
                if (orders && orders.length > 0) {
                    orders.forEach(o => {
                        const inv = o.invoice_no || ('#' + o.id);
                        const name = o.customer_name || 'Customer';
                        const phone = o.customer_phone || 'N/A';
                        const grand = Number(o.grand_total || 0).toLocaleString();

                        const item = document.createElement('div');
                        item.className = 'order-search-item';
                        item.innerHTML = `
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong class="text-primary font-monospace bn-digit">#${inv}</strong>
                                    <span class="text-dark fw-semibold ms-1" style="font-size: 12.5px;">${name}</span>
                                    <small class="text-muted d-block bn-digit font-monospace" style="font-size: 10.5px;">Phone: ${phone}</small>
                                </div>
                                <span class="badge bg-light text-dark border fw-bold bn-digit font-monospace">৳${grand}</span>
                            </div>
                        `;

                        item.addEventListener('click', () => {
                            selectOrderForBooking(o.id);
                        });

                        dropdown.appendChild(item);
                    });
                    dropdown.style.display = 'block';
                } else {
                    dropdown.innerHTML = '<div class="p-3 text-muted text-center small">No unbooked or active orders found. (Delivered/Cancelled orders are excluded)</div>';
                    dropdown.style.display = 'block';
                }
            })
            .catch(err => {
                console.error(err);
                dropdown.innerHTML = '<div class="p-2 text-danger text-center small">Failed to load orders.</div>';
            });
    }

    searchInput.addEventListener('focus', function() {
        if (!this.value.trim() || this.value.includes('#')) {
            performOrderSearch('');
        }
    });

    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        clearBtn.style.display = query ? 'inline-block' : 'none';

        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(() => {
            performOrderSearch(query);
        }, 200);
    });

    function selectOrderForBooking(orderId) {
        dropdown.style.display = 'none';
        
        fetch(`{{ url('admin/courier-management/order-details-ajax') }}/${orderId}`)
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    showCourierModalAlert(data.message || 'Cannot select this order for courier booking.', 'danger', 'Order Unavailable');
                    clearSelectedOrder();
                    return;
                }

                const o = data.order;
                document.getElementById('booking_order_id').value = o.id;

                const inv   = o.invoice_no || o.id;
                const name  = o.customer_name || 'Customer';
                const phone = o.customer_phone || '';
                
                searchInput.value = `#${inv} - ${name} (${phone})`;
                clearBtn.style.display = 'inline-block';

                document.getElementById('cust_name').value    = name;
                document.getElementById('cust_phone').value   = phone;
                document.getElementById('cust_address').value = o.customer_address || '';

                // Financials
                document.getElementById('label_grand_total').innerText = Number(o.grand_total || 0).toLocaleString();
                document.getElementById('label_paid_amount').innerText = Number(o.paid_amount || 0).toLocaleString();
                document.getElementById('label_cod_amount').innerText  = Number(data.cod_amount || 0).toLocaleString();

                // Weight
                document.getElementById('parcel_weight_input').value = data.total_weight || 0.5;

                // Auto-populate delivery cost from order's shipping charge
                if (data.delivery_charge !== undefined && data.delivery_charge > 0) {
                    const costInput = document.getElementById('manual_courier_cost_input');
                    if (costInput) {
                        costInput.value = data.delivery_charge;
                    }
                }

                // Items table render
                const tbody = document.getElementById('bookingItemsTbody');
                tbody.innerHTML = '';
                if (data.items && data.items.length > 0) {
                    data.items.forEach(it => {
                        const tr = document.createElement('tr');
                        let titleHtml = `<strong class="text-dark d-block text-truncate" style="font-size: 11.5px;" title="${it.title}">${it.title}</strong>`;
                        if (it.pv_color || it.pv_size) {
                            titleHtml += `<small class="text-primary d-block text-truncate" style="font-size: 10px;">Variant: ${[it.pv_color, it.pv_size].filter(Boolean).join(' / ')}</small>`;
                        }
                        if (it.product_code || it.pv_sku) {
                            titleHtml += `<small class="text-muted font-monospace bn-digit d-block text-truncate" style="font-size: 9.5px;">Code: ${it.pv_sku || it.product_code}</small>`;
                        }

                        tr.innerHTML = `
                            <td class="ps-2 col-prod-title">${titleHtml}</td>
                            <td class="text-center fw-bold bn-digit font-monospace col-prod-qty">${it.quantity}</td>
                            <td class="text-center font-monospace bn-digit col-prod-weight">${it.weight ? (it.weight + ' kg') : '0.5 kg'}</td>
                            <td class="text-end pe-2 font-monospace bn-digit col-prod-price">৳${Number(it.price || 0).toFixed(2)}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                    document.getElementById('itemsCountBadge').innerText = `${data.items.length} Item(s)`;
                } else {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center py-2 text-muted small">No items recorded in this order.</td></tr>';
                }
            })
            .catch(err => {
                console.error(err);
                showCourierModalAlert('Failed to load order details.', 'danger', 'Network Error');
            });
    }

    function clearSelectedOrder() {
        document.getElementById('booking_order_id').value = '';
        searchInput.value = '';
        clearBtn.style.display = 'none';
        document.getElementById('cust_name').value = '';
        document.getElementById('cust_phone').value = '';
        document.getElementById('cust_address').value = '';
        document.getElementById('label_grand_total').innerText = '0.00';
        document.getElementById('label_paid_amount').innerText = '0.00';
        document.getElementById('label_cod_amount').innerText = '0.00';
        document.getElementById('parcel_weight_input').value = '0.5';
        document.getElementById('manual_courier_cost_input').value = '0';
        document.getElementById('bookingItemsTbody').innerHTML = '<tr><td colspan="4" class="text-center py-3 text-muted small">No order selected yet.</td></tr>';
        document.getElementById('itemsCountBadge').innerText = '0 Item(s)';
        clearBookingProofMedia();
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('#orderLiveSearchInput') && !e.target.closest('#orderSearchResultsDropdown')) {
            dropdown.style.display = 'none';
        }
    });

    // Courier selection handling
    function onCourierProviderChange() {
        const select = document.getElementById('courier_provider_select');
        const val = select.value.toLowerCase();
        const selectedCourier = select.value;
        const manualSection = document.getElementById('manualCourierFieldsSection');
        const newCourierWrapper = document.getElementById('newCourierNameWrapper');
        
        if (select.value === 'manual_new') {
            manualSection.style.display = 'block';
            newCourierWrapper.style.display = 'block';
            document.getElementById('new_manual_courier_name').value = '';
            document.getElementById('manual_branch_input').value = '';
            document.getElementById('manual_phone_input').value = '';
        } else if (val.includes('steadfast') || val.includes('pathao') || val.includes('redx')) {
            manualSection.style.display = 'none';
            newCourierWrapper.style.display = 'none';
        } else if (val !== '') {
            manualSection.style.display = 'block';
            newCourierWrapper.style.display = 'none';

            fetch(`{{ url('admin/courier-management/courier-info-ajax') }}/${encodeURIComponent(selectedCourier)}`)
                .then(res => res.json())
                .then(info => {
                    if (info.success) {
                        if (info.default_branch) {
                            document.getElementById('manual_branch_input').value = info.default_branch;
                        }
                        if (info.default_phone) {
                            document.getElementById('manual_phone_input').value = info.default_phone;
                        }
                    }
                })
                .catch(err => console.error(err));
        } else {
            manualSection.style.display = 'none';
            newCourierWrapper.style.display = 'none';
        }
    }

    // Form Submit Guard with Spinner
    document.getElementById('courierBookingForm').addEventListener('submit', function(e) {
        const orderId = document.getElementById('booking_order_id').value;
        const courierSelect = document.getElementById('courier_provider_select').value;
        const btn = document.getElementById('btnSubmitBooking');

        if (!orderId) {
            e.preventDefault();
            showCourierModalAlert('Please search and select an order first from Step 1.', 'warning', 'Order Selection Required');
            return false;
        }

        if (!courierSelect) {
            e.preventDefault();
            showCourierModalAlert('Please choose a courier provider for dispatch.', 'warning', 'Courier Required');
            return false;
        }

        if (courierSelect === 'manual_new') {
            const newName = document.getElementById('new_manual_courier_name').value.trim();
            if (!newName) {
                e.preventDefault();
                showCourierModalAlert('Please enter the name of the new manual courier.', 'warning', 'Courier Name Required');
                return false;
            }
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> <span>Processing Booking...</span>';
    });

    document.addEventListener('DOMContentLoaded', onCourierProviderChange);
</script>
@endsection