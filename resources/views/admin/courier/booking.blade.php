
@extends('layouts.admin')

@section('title', 'New Courier Booking - ' . config('app.name'))

@section('admin_content')
<style>
    /* =========================================================
       COURIER BOOKING — KAIADMIN CLASSIC WHITE
       Scoped UI / Bootstrap 5 Compatible
    ========================================================= */

    #courier-booking-root {
        --cb-primary: var(--bs-primary, #1572e8);
        --cb-primary-soft: rgba(21, 114, 232, .08);
        --cb-body: #f5f7fd;
        --cb-card: #ffffff;
        --cb-heading: #2a2f5b;
        --cb-text: #495057;
        --cb-muted: #8a92a6;
        --cb-border: #e7eaf3;
        --cb-soft: #f8f9fc;
        --cb-success: #31ce36;
        --cb-warning: #ffad46;
        --cb-danger: #f25961;
        --cb-radius: 5px;
        color: var(--cb-text);
        font-size: 13px;
        min-width: 0;
    }

    #courier-booking-root *,
    .courier-modal-scope * {
        box-sizing: border-box;
    }

    /* Global Radius */

    #courier-booking-root .btn,
    #courier-booking-root button,
    #courier-booking-root .badge,
    #courier-booking-root .form-control,
    #courier-booking-root .form-select,
    #courier-booking-root .input-group-text,
    #courier-booking-root .booking-card,
    #courier-booking-root .financial-calc-box,
    #courier-booking-root .manual-courier-box,
    #courier-booking-root .section-badge,
    #courier-booking-root .proof-thumb-box,
    #courier-booking-root .proof-media-surface,
    .courier-modal-scope .modal-content,
    .courier-modal-scope .btn,
    .courier-modal-scope .form-control,
    .courier-modal-scope .form-select,
    .courier-modal-scope .badge,
    .courier-modal-scope .alert {
        border-radius: var(--cb-radius) !important;
    }

    #courier-booking-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    #courier-booking-root .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Page Header */

    #courier-booking-root .page-heading {
        color: var(--cb-heading);
        font-size: clamp(17px, 1.5vw, 21px);
        font-weight: 700;
        letter-spacing: -.35px;
        line-height: 1.35;
    }

    #courier-booking-root .page-description {
        color: var(--cb-muted);
        font-size: 12px;
        line-height: 1.6;
    }

    #courier-booking-root .page-heading-icon {
        width: 36px;
        height: 36px;
        min-width: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--cb-primary-soft);
        color: var(--cb-primary);
        border-radius: 5px;
        font-size: 16px;
    }

    /* Cards */

    #courier-booking-root .booking-card {
        background: var(--cb-card);
        border: 1px solid var(--cb-border);
        box-shadow: 0 2px 8px rgba(0, 0, 0, .025);
        padding: 18px;
        margin-bottom: 16px;
        min-width: 0;
    }

    #courier-booking-root .booking-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        padding-bottom: 12px;
        margin-bottom: 14px;
        border-bottom: 1px solid var(--cb-border);
        flex-wrap: wrap;
    }

    #courier-booking-root .section-title {
        color: var(--cb-heading);
        font-size: 13.5px;
        font-weight: 700;
        line-height: 1.4;
        margin: 0;
    }

    #courier-booking-root .section-description {
        display: block;
        color: var(--cb-muted);
        font-size: 11px;
        margin-top: 4px;
        line-height: 1.5;
    }

    #courier-booking-root .section-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .3px;
        padding: 5px 9px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    /* Buttons */

    #courier-booking-root .btn-compact {
        min-height: 32px;
        padding: 6px 12px !important;
        font-size: 11.5px !important;
        line-height: 1.35 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        white-space: nowrap;
        transition: all .18s ease;
    }

    #courier-booking-root .btn-primary,
    .courier-modal-scope .btn-primary {
        background-color: var(--cb-primary);
        border-color: var(--cb-primary);
    }

    #courier-booking-root .btn-primary:hover {
        filter: brightness(.94);
    }

    #courier-booking-root .btn:focus-visible,
    .courier-modal-scope .btn:focus-visible {
        outline: 2px solid var(--cb-primary);
        outline-offset: 2px;
    }

    /* Forms */

    #courier-booking-root .form-label {
        display: block;
        color: var(--cb-text);
        font-size: 11.5px;
        font-weight: 600;
        margin-bottom: 5px;
    }

    #courier-booking-root .form-control,
    #courier-booking-root .form-select {
        min-height: 36px;
        padding: 7px 10px;
        font-size: 12px;
        line-height: 1.5;
        color: var(--cb-heading);
        background-color: #fff;
        border: 1px solid #e1e5ef;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #courier-booking-root textarea.form-control {
        min-height: 65px;
        resize: vertical;
    }

    #courier-booking-root .form-control::placeholder {
        color: #adb4c5;
        opacity: 1;
    }

    #courier-booking-root .form-control:focus,
    #courier-booking-root .form-select:focus {
        border-color: var(--cb-primary);
        box-shadow: 0 0 0 3px var(--cb-primary-soft);
        outline: none;
    }

    #courier-booking-root .form-control:disabled,
    #courier-booking-root .form-select:disabled {
        background-color: #f1f3f8;
    }

    #courier-booking-root .input-group-text {
        min-height: 36px;
        background: var(--cb-soft);
        border-color: #e1e5ef;
        color: var(--cb-muted);
    }

    #courier-booking-root .bn-digit,
    #courier-booking-root .stat-amount,
    #courier-booking-root .font-monospace {
        font-weight: 600;
        font-variant-numeric: tabular-nums;
    }

    /* Order Search */

    #courier-booking-root .order-search-results-dropdown {
        position: absolute;
        top: calc(100% + 5px);
        left: 0;
        right: 0;
        z-index: 1060;
        max-height: 280px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid var(--cb-border);
        border-radius: 5px !important;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .10);
        display: none;
    }

    #courier-booking-root .order-search-item {
        padding: 10px 12px;
        cursor: pointer;
        border-bottom: 1px solid var(--cb-border);
        transition: background-color .15s ease;
    }

    #courier-booking-root .order-search-item:last-child {
        border-bottom: 0;
    }

    #courier-booking-root .order-search-item:hover {
        background: var(--cb-primary-soft);
    }

    /* Items Table */

    #courier-booking-root .pc-fit-container {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    #courier-booking-root .booking-items-table {
        width: 100%;
        min-width: 440px;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    #courier-booking-root .booking-items-table thead th {
        padding: 10px 8px;
        color: #6c7293;
        background: #f8f9fc;
        border-bottom: 1px solid var(--cb-border);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .35px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    #courier-booking-root .booking-items-table tbody td {
        padding: 10px 8px;
        color: var(--cb-text);
        font-size: 11.5px;
        border-bottom: 1px solid #f0f1f5;
        vertical-align: middle;
        overflow-wrap: anywhere;
    }

    #courier-booking-root .booking-items-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #courier-booking-root .booking-items-table tbody tr:hover {
        background: #fafbfe;
    }

    #courier-booking-root .col-prod-title {
        width: 48%;
    }

    #courier-booking-root .col-prod-qty {
        width: 12%;
    }

    #courier-booking-root .col-prod-weight {
        width: 17%;
    }

    #courier-booking-root .col-prod-price {
        width: 23%;
    }

    /* Financial Summary */

    #courier-booking-root .financial-calc-box {
        padding: 15px;
        background: #f8f9fc;
        border: 1px solid var(--cb-border);
        margin-bottom: 15px;
    }

    #courier-booking-root .financial-calc-box .summary-label {
        color: var(--cb-muted);
        font-size: 11.5px;
    }

    #courier-booking-root .financial-calc-box .summary-value {
        color: var(--cb-heading);
        font-size: 12px;
        font-weight: 700;
    }

    #courier-booking-root .financial-calc-box .cod-row {
        padding-top: 10px;
        margin-top: 8px;
        border-top: 1px solid var(--cb-border);
    }

    #courier-booking-root .financial-calc-box .cod-value {
        color: var(--cb-primary);
        font-size: 19px;
        font-weight: 700;
    }

    /* Manual Courier */

    #courier-booking-root .manual-courier-box {
        padding: 15px;
        background: #fffaf0;
        border: 1px solid #f2e5c7;
        margin-bottom: 15px;
    }

    #courier-booking-root .manual-courier-box .form-label {
        color: #756344;
    }

    /* Media Chooser */

    #courier-booking-root .proof-media-surface {
        padding: 12px;
        background: #f8f9fc;
        border: 1px dashed #d4d9e5;
        transition: border-color .2s ease, background .2s ease;
    }

    #courier-booking-root .proof-media-surface:hover {
        border-color: var(--cb-primary);
        background: #f5f8ff;
    }

    #courier-booking-root .proof-thumb-box {
        position: relative;
        width: 42px;
        height: 42px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        cursor: pointer;
        background: #fff;
        border: 1px solid var(--cb-border);
    }

    #courier-booking-root .proof-thumb-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    #courier-booking-root .proof-thumb-hover-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(42, 47, 91, .6);
        opacity: 0;
        transition: opacity .15s ease;
    }

    #courier-booking-root .proof-thumb-box:hover .proof-thumb-hover-overlay {
        opacity: 1;
    }

    /* Alerts */

    #courier-booking-root .alert {
        border: 1px solid transparent;
        border-radius: 5px;
    }

    /* Submit */

    #courier-booking-root #btnSubmitBooking {
        min-height: 42px;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: .1px;
        transition: all .2s ease;
    }

    #courier-booking-root #btnSubmitBooking:not(:disabled):hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(21, 114, 232, .16);
    }

    /* Modal */

    .courier-modal-scope .modal-content {
        border: 1px solid var(--cb-border);
        box-shadow: 0 10px 35px rgba(0, 0, 0, .12);
    }

    .courier-modal-scope .modal-title,
    .courier-modal-scope h6 {
        color: var(--cb-heading);
    }

    .courier-modal-scope .modal-body {
        overflow-wrap: anywhere;
    }

    #bookingProofZoomImgTag {
        max-width: 100%;
        max-height: min(75vh, 600px);
        object-fit: contain;
        border-radius: 5px;
    }

    /* Tablet */

    @media (max-width: 991.98px) {
        #courier-booking-root .booking-card {
            padding: 16px;
        }

        #courier-booking-root .financial-calc-box,
        #courier-booking-root .manual-courier-box {
            padding: 13px;
        }
    }

    /* Mobile */

    @media (max-width: 767.98px) {
        #courier-booking-root {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        #courier-booking-root .booking-card {
            padding: 13px;
            margin-bottom: 12px;
        }

        #courier-booking-root .booking-card-header {
            align-items: flex-start;
        }

        #courier-booking-root .header-action-btn-group {
            width: 100%;
        }

        #courier-booking-root .header-action-btn-group .btn {
            width: 100%;
            min-height: 36px;
            justify-content: center;
        }

        #courier-booking-root .page-heading {
            font-size: 17px;
        }

        #courier-booking-root .section-title {
            font-size: 13px;
        }

        #courier-booking-root .financial-calc-box,
        #courier-booking-root .manual-courier-box {
            padding: 12px;
            margin-bottom: 12px;
        }

        #courier-booking-root .proof-media-surface {
            padding: 10px;
        }

        #courier-booking-root .booking-items-table {
            min-width: 440px;
        }

        #courier-booking-root .btn-compact {
            min-height: 34px;
        }

        #courier-booking-root #btnSubmitBooking {
            min-height: 44px;
        }
    }

    @media (max-width: 400px) {
        #courier-booking-root .booking-card {
            padding: 11px;
        }

        #courier-booking-root .section-badge {
            font-size: 8px;
            padding: 4px 6px;
        }

        #courier-booking-root .booking-card-header {
            gap: 7px;
        }

        #courier-booking-root .proof-media-surface .btn {
            width: 100%;
        }

        #courier-booking-root .financial-calc-box .cod-value {
            font-size: 17px;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="courier-booking-root">

    <!-- Page Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div class="min-w-0">
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="page-heading-icon">
                    <i class="fas fa-truck-fast"></i>
                </span>
                <h5 class="page-heading mb-0">New Booking</h5>
            </div>
            <small class="page-description d-block">
                Select order → Verify → Book.
            </small>
        </div>

        <div class="header-action-btn-group">
            <a href="{{ route('admin.courier.index') }}"
               class="btn btn-outline-secondary btn-compact shadow-none">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Courier Orders</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 small shadow-sm d-flex align-items-center mb-3" role="alert">
            <i class="fas fa-circle-check me-2 fs-5 text-success"></i>
            <span class="fw-semibold">{{ session('success') }}</span>
            <button type="button" class="btn-close shadow-none py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 small shadow-sm d-flex align-items-center mb-3" role="alert">
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

            <!-- Left Column -->
            <div class="col-12 col-lg-7">

                <!-- Order Search -->
                <div class="booking-card">

                    <div class="booking-card-header">
                        <label class="form-label fw-bold mb-0">
                            <i class="fas fa-magnifying-glass text-primary me-1"></i>
                            Select Order
                        </label>

                        <span class="section-badge bg-primary-subtle text-primary border border-primary-subtle">
                            Step 1 · Order
                        </span>
                    </div>

                    <div class="position-relative">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text border-end-0">
                                <i class="fas fa-search"></i>
                            </span>

                            <input type="text"
                                   id="orderLiveSearchInput"
                                   class="form-control border-start-0 ps-0 shadow-none"
                                   placeholder="Search customer, phone or invoice..."
                                   value="{{ $order ? ('#' . ($order->invoice_no ?? $order->id) . ' - ' . ($order->shipping_name ?? 'Customer') . ' (' . ($order->shipping_phone ?? '') . ')') : '' }}"
                                   autocomplete="off">

                            <button class="btn btn-light border text-muted px-3"
                                    type="button"
                                    id="clearOrderSearchBtn"
                                    style="{{ $order ? '' : 'display:none;' }}"
                                    onclick="clearSelectedOrder()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div id="orderSearchResultsDropdown" class="order-search-results-dropdown shadow-lg"></div>
                    </div>

                    <small class="text-muted d-block mt-2" style="font-size: 10.5px;">
                        <i class="fas fa-info-circle me-1"></i>
                        Only active, unbooked orders are available.
                    </small>
                </div>

                <!-- Customer Information -->
                <div class="booking-card">

                    <div class="booking-card-header">
                        <div>
                            <h6 class="section-title">Customer &amp; Shipping</h6>
                            <small class="section-description">
                                Verify recipient details before dispatch.
                            </small>
                        </div>

                        <span class="section-badge bg-info-subtle text-info border border-info-subtle">
                            Recipient
                        </span>
                    </div>

                    <div class="row g-3 mb-3">

                        <div class="col-12 col-sm-6">
                            <label class="form-label">Recipient Name *</label>
                            <input type="text"
                                   name="recipient_name"
                                   id="cust_name"
                                   class="form-control form-control-sm shadow-none"
                                   required
                                   value="{{ $order->shipping_name ?? ($order->customer_name ?? '') }}">
                        </div>

                        <div class="col-12 col-sm-6">
                            <label class="form-label">Recipient Phone *</label>
                            <input type="text"
                                   name="recipient_phone"
                                   id="cust_phone"
                                   class="form-control form-control-sm font-monospace fw-bold bn-digit shadow-none"
                                   required
                                   value="{{ $order->shipping_phone ?? ($order->customer_phone ?? '') }}">
                        </div>

                    </div>

                    <div>
                        <label class="form-label">Delivery Address *</label>
                        <textarea name="recipient_address"
                                  id="cust_address"
                                  class="form-control form-control-sm shadow-none"
                                  rows="2"
                                  required>{{ $order->shipping_address ?? ($order->customer_address ?? '') }}</textarea>
                    </div>
                </div>

                <!-- Package Items -->
                <div class="booking-card mb-0">

                    <div class="booking-card-header">
                        <h6 class="section-title">Package Items</h6>

                        <span class="section-badge bg-secondary-subtle text-secondary border border-secondary-subtle bn-digit font-monospace"
                              id="itemsCountBadge">
                            {{ count($orderItems) }} Item(s)
                        </span>
                    </div>

                    <div class="table-responsive pc-fit-container">
                        <table class="table table-sm align-middle mb-0 booking-items-table">

                            <thead>
                                <tr>
                                    <th class="ps-2 col-prod-title">Product</th>
                                    <th class="text-center col-prod-qty">Qty</th>
                                    <th class="text-center col-prod-weight">Weight</th>
                                    <th class="text-end pe-2 col-prod-price">Price</th>
                                </tr>
                            </thead>

                            <tbody id="bookingItemsTbody">
                                @forelse($orderItems as $it)
                                    <tr>
                                        <td class="ps-2 col-prod-title">
                                            <strong class="text-dark d-block text-truncate"
                                                    style="font-size: 11.5px;"
                                                    title="{{ $it->title }}">
                                                {{ $it->title }}
                                            </strong>

                                            @if(!empty($it->product_code))
                                                <small class="text-muted font-monospace bn-digit d-block text-truncate"
                                                       style="font-size: 9.5px;">
                                                    Code: {{ $it->product_code }}
                                                </small>
                                            @endif
                                        </td>

                                        <td class="text-center fw-bold bn-digit font-monospace col-prod-qty">
                                            {{ $it->quantity }}
                                        </td>

                                        <td class="text-center font-monospace bn-digit col-prod-weight">
                                            {{ $it->weight ? ($it->weight . ' kg') : '0.5 kg' }}
                                        </td>

                                        <td class="text-end pe-2 font-monospace bn-digit col-prod-price">
                                            ৳{{ number_format((float)$it->price, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="noItemRow">
                                        <td colspan="4" class="text-center py-4 text-muted small">
                                            <i class="fas fa-box-open d-block mb-2 fs-4 opacity-50"></i>
                                            No order selected yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                        </table>
                    </div>
                </div>

            </div>

            <!-- Right Column -->
            <div class="col-12 col-lg-5">

                <div class="booking-card mb-0">

                    <!-- Courier Setup -->
                    <div class="booking-card-header">
                        <h6 class="section-title">Courier Setup</h6>

                        <span class="section-badge bg-success-subtle text-success border border-success-subtle">
                            Step 2 · Dispatch
                        </span>
                    </div>

                    <!-- Courier Provider -->
                    <div class="mb-3">
                        <label class="form-label">Courier Provider *</label>

                        <select name="courier_name"
                                id="courier_provider_select"
                                class="form-select form-select-sm fw-semibold shadow-none"
                                required
                                onchange="onCourierProviderChange()">

                            <option value="">-- Select Courier --</option>

                            <optgroup label="Connected Courier">
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
                                            {{ $cr->courier_name }} (API)
                                        </option>
                                    @endif
                                @endforeach
                            </optgroup>

                            <optgroup label="Saved Couriers" id="savedCouriersOptgroup">
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

                            <optgroup label="Custom Courier">
                                <option value="manual_new">+ Add New Manual Courier</option>
                            </optgroup>

                        </select>
                    </div>

                    <!-- Service & Weight -->
                    <div class="row g-3 mb-3">

                        <div class="col-6">
                            <label class="form-label">Service Type</label>

                            <select name="service_type" class="form-select form-select-sm shadow-none">
                                <option value="regular" selected>Regular (48h)</option>
                                <option value="express">Express (24h)</option>
                                <option value="same_day">Same Day</option>
                            </select>
                        </div>

                        <div class="col-6">
                            <label class="form-label">Parcel Weight (KG) *</label>

                            @php
                                $calcW = 0;
                                foreach($orderItems as $it) {$calcW += (((float)($it->weight ?: 0.5)) * ((int)$it->quantity));
                                }
                            @endphp

                            <input type="number"
                                   step="0.1"
                                   min="0.1"
                                   name="parcel_weight"
                                   id="parcel_weight_input"
                                   class="form-control form-control-sm font-monospace fw-bold bn-digit shadow-none"
                                   value="{{ max(0.5, round($calcW, 2)) }}"
                                   required>
                        </div>

                    </div>

                    <!-- Financial Summary -->
                    <div class="financial-calc-box">

                        <div class="d-flex justify-content-between align-items-center mb-2 gap-2">
                            <span class="summary-label">Order Total</span>

                            <strong class="summary-value font-monospace bn-digit">
                                ৳<span id="label_grand_total">{{ number_format($order->grand_total ?? 0, 2) }}</span>
                            </strong>
                        </div>

                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <span class="summary-label">Paid / Advance</span>

                            <strong class="summary-value text-success font-monospace bn-digit">
                                ৳<span id="label_paid_amount">{{ number_format($order->paid_amount ?? 0, 2) }}</span>
                            </strong>
                        </div>

                        <div class="d-flex justify-content-between align-items-center cod-row gap-2">
                            <span class="fw-bold text-dark" style="font-size: 12px;">
                                Collectible COD
                            </span>

                            @php
                                $cCod = max(0, ((float)($order->grand_total ?? 0)) - ((float)($order->paid_amount ?? 0)));
                            @endphp

                            <h5 class="cod-value mb-0 font-monospace bn-digit">
                                ৳<span id="label_cod_amount">{{ number_format($cCod, 2) }}</span>
                            </h5>
                        </div>

                    </div>

                    <!-- Manual Courier Details -->
                    <div id="manualCourierFieldsSection" class="manual-courier-box" style="display: none;">

                        <h6 class="fw-bold text-dark small mb-3 d-flex align-items-center gap-2">
                            <i class="fas fa-truck-moving text-warning"></i>
                            Manual Courier Details
                        </h6>

                        <!-- New Courier Name -->
                        <div class="mb-3" id="newCourierNameWrapper" style="display: none;">
                            <label class="form-label">Courier Name *</label>

                            <input type="text"
                                   name="new_manual_courier_name"
                                   id="new_manual_courier_name"
                                   class="form-control form-control-sm shadow-none"
                                   placeholder="Courier service name">
                        </div>

                        <div class="row g-3 mb-3">

                            <div class="col-6">
                                <label class="form-label">Hub / Branch</label>

                                <input type="text"
                                       name="manual_branch"
                                       id="manual_branch_input"
                                       class="form-control form-control-sm shadow-none"
                                       placeholder="Branch name">
                            </div>

                            <div class="col-6">
                                <label class="form-label">Contact Phone</label>

                                <input type="text"
                                       name="manual_phone"
                                       id="manual_phone_input"
                                       class="form-control form-control-sm font-monospace bn-digit shadow-none"
                                       placeholder="01XXXXXXXXX">
                            </div>

                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tracking ID / CN</label>

                            <input type="text"
                                   name="tracking_code"
                                   id="manual_tracking_code_input"
                                   class="form-control form-control-sm font-monospace bn-digit shadow-none"
                                   placeholder="CN-984321">
                        </div>

                        <div class="row g-3">

                            <div class="col-6">
                                <label class="form-label">Courier Cost (৳)</label>

                                <input type="number"
                                       step="1"
                                       min="0"
                                       name="courier_cost"
                                       id="manual_courier_cost_input"
                                       class="form-control form-control-sm font-monospace bn-digit fw-semibold shadow-none"
                                       value="0">
                            </div>

                            <div class="col-6">
                                <label class="form-label">Cost Paid (৳)</label>

                                <input type="number"
                                       step="1"
                                       min="0"
                                       name="courier_cost_paid"
                                       id="manual_courier_cost_paid_input"
                                       class="form-control form-control-sm font-monospace bn-digit shadow-none"
                                       value="0">
                            </div>

                        </div>
                    </div>

                    <!-- Consignment Proof -->
                    <div class="mb-3">

                        <label class="form-label d-flex justify-content-between align-items-center gap-2">
                            <span>
                                <i class="fas fa-paperclip text-primary me-1"></i>
                             Slip
                            </span>

                            <span class="badge bg-light text-muted border font-monospace"
                                  style="font-size: 8.5px;">
                                OPTIONAL
                            </span>
                        </label>

                        <div class="proof-media-surface">

                            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">

                                <button type="button"
                                        class="btn btn-outline-primary btn-compact shadow-none"
                                        onclick="chooseBookingProofMedia()">
                                    <i class="fas fa-photo-film"></i>
                                    <span>Media Library</span>
                                </button>

                                <span class="small text-muted">or upload a file</span>
                            </div>

                            <input type="file"
                                   name="consignment_slip_file"
                                   id="booking_screenshot_file"
                                   class="form-control form-control-sm shadow-none"
                                   accept="image/*"
                                   onchange="previewBookingLocalProof(this)">

                            <div id="booking_proof_preview_wrap"
                                 class="mt-3 d-none align-items-center gap-2 bg-white border p-2"
                                 style="border-radius: 5px;">

                                <div class="proof-thumb-box"
                                     onclick="zoomBookingProof()"
                                     title="Preview Slip">

                                    <img id="booking_proof_preview_img" src="" alt="Proof Preview">

                                    <div class="proof-thumb-hover-overlay">
                                        <i class="fas fa-search-plus text-white" style="font-size: 11px;"></i>
                                    </div>
                                </div>

                                <div class="min-w-0 flex-grow-1">

                                    <span class="badge bg-success-subtle text-success border border-success-subtle"
                                          style="font-size: 9px;">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Attached
                                    </span>

                                    <small class="text-muted d-block text-truncate font-monospace"
                                           id="booking_proof_preview_txt"
                                           style="font-size: 10px;"></small>
                                </div>

                                <button type="button"
                                        class="btn btn-sm btn-link text-danger p-1 ms-auto text-decoration-none fw-bold"
                                        onclick="clearBookingProofMedia()">
                                    &times; Clear
                                </button>

                            </div>
                        </div>
                    </div>

                    <!-- Handling Notes -->
                    <div class="mb-3">
                        <label class="form-label">Handling Notes</label>

                        <textarea name="note"
                                  id="booking_note_input"
                                  class="form-control form-control-sm shadow-none"
                                  rows="2"
                                  placeholder="Fragile item, call recipient before delivery...">{{ $order->admin_note ?? '' }}</textarea>
                    </div>

                    <!-- Submit -->
                    <button type="submit"
                            class="btn btn-primary w-100 py-2 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-none"
                            id="btnSubmitBooking"
                            style="min-height: 42px; font-size: 13px;">

                        <i class="fas fa-paper-plane"></i>
                        <span>Confirm &amp; Book Parcel</span>
                    </button>

                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal 1: Alert -->
<div class="modal fade courier-modal-scope"
     id="courierAlertModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index: 1060;">

    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-3">

            <div class="modal-body p-2">

                <div class="mb-3" id="alertModalIcon">
                    <i class="fas fa-triangle-exclamation text-warning fa-2x"></i>
                </div>

                <h6 class="fw-bold mb-2" id="alertModalTitle" style="font-size: 14px;">
                    Notice
                </h6>

                <p class="text-muted small mb-3"
                   id="alertModalMessage"
                   style="font-size: 12px; line-height: 1.5;">
                    Notification message.
                </p>

                <button type="button"
                        class="btn btn-primary btn-compact px-4"
                        data-bs-dismiss="modal">
                    OK
                </button>

            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Proof Preview -->
<div class="modal fade courier-modal-scope"
     id="bookingProofZoomModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index: 1080;">

    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content p-2">

            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">

                <span class="fw-bold text-dark small" style="font-size: 12.5px;">
                    <i class="fas fa-image text-primary me-1"></i>
                    Consignment Slip
                </span>

                <button type="button"
                        class="btn-close btn-sm shadow-none"
                        data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-2 text-center">
                <img id="bookingProofZoomImgTag"
                     src=""
                     alt="Proof Preview"
                     class="img-fluid border"
                     style="max-height: 480px; object-fit: contain; border-radius: 5px;">
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
