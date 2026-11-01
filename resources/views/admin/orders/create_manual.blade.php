@extends('layouts.admin')

@section('title', 'Create Manual Order - ' . config('app.name'))

@section('admin_content')
<style>
    /* Global Strict 5px Element Radius */
    #manual-order-root .btn,
    #manual-order-root .btn-sm,
    #manual-order-root button,
    #manual-order-root .badge,
    #manual-order-root .form-control,
    #manual-order-root .form-select,
    #manual-order-root .input-group-text,
    #manual-order-root .alert,
    #manual-order-root .order-card,
    #manual-order-root .proof-media-surface,
    #manual-order-root .proof-thumb-box,
    .manual-order-modal-scope .modal-content,
    .manual-order-modal-scope .btn,
    .manual-order-modal-scope .form-control,
    .manual-order-modal-scope .form-select {
        border-radius: 5px !important;
    }

    #manual-order-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #manual-order-root .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Compact Button Sizing */
    #manual-order-root .btn-compact,
    .manual-order-modal-scope .btn-compact {
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
    #manual-order-root .order-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        padding: 18px;
        margin-bottom: 16px;
    }

    /* Bengali Safe Typography */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Form Controls */
    #manual-order-root .form-label,
    .manual-order-modal-scope .form-label {
        font-size: 11.5px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #manual-order-root .form-control,
    #manual-order-root .form-select,
    .manual-order-modal-scope .form-control,
    .manual-order-modal-scope .form-select {
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 6px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #manual-order-root .form-control:focus,
    #manual-order-root .form-select:focus,
    .manual-order-modal-scope .form-control:focus,
    .manual-order-modal-scope .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Search Dropdown Panels */
    .search-results-panel {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 1055;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 5px !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        max-height: 260px;
        overflow-y: auto;
    }

    .search-result-item {
        padding: 9px 12px;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        transition: background-color 0.15s ease;
    }
    .search-result-item:hover {
        background-color: #f8fafc;
    }
    .search-result-item:last-child {
        border-bottom: none;
    }

    /* Cart Items Table */
    .manual-items-table {
        margin-bottom: 0;
        width: 100%;
    }
    .manual-items-table th {
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
    .manual-items-table td {
        padding: 10px 12px;
        vertical-align: middle;
        font-size: 12.5px;
        border-bottom: 1px solid #f1f5f9;
    }

    /* Global Media Chooser Surface Box for Payment Proof */
    .proof-media-surface {
        background: #f8fafc;
        border: 1.5px dashed #cbd5e1;
        padding: 10px 12px;
        transition: border-color 0.15s ease;
    }
    .proof-media-surface:hover {
        border-color: #2563eb;
    }
    .proof-thumb-box {
        width: 44px;
        height: 44px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
    }
    .proof-thumb-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* 100/100 Mobile Responsive Tuning */
    @media (max-width: 767.98px) {
        #manual-order-root .order-card {
            padding: 12px !important;
            margin-bottom: 12px !important;
        }
        .manual-items-table th,
        .manual-items-table td {
            padding: 8px 10px !important;
            font-size: 12px;
        }
        .proof-actions-wrap {
            flex-direction: column;
            align-items: stretch !important;
        }
        .proof-actions-wrap .btn-compact {
            width: 100%;
        }
        .header-top-wrap {
            flex-direction: column;
            align-items: stretch !important;
        }
        .header-top-wrap .btn-compact {
            width: 100%;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="manual-order-root">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start align-items-md-center gap-2 mb-3 header-top-wrap">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-cart-plus text-primary"></i>
                <span>Create Manual Order</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">Book orders from Facebook Page, WhatsApp, Phone Calls, or Offline Showroom Walk-ins.</small>
        </div>
        <div>
            <a href="{{ route('admin.order.online') }}" class="btn btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-arrow-left me-1"></i>
                <span>Back to Orders</span>
            </a>
        </div>
    </div>

    <!-- Alert Message Container -->
    <div id="formAlertBox" class="alert alert-danger d-none py-2 px-3 small border-0 shadow-xs mb-3"></div>

    <form id="manualOrderForm" onsubmit="handleManualOrderSubmit(event)">
        @csrf
        <div class="row g-3">
            <!-- Left Column: Customer & Items Selection -->
            <div class="col-12 col-lg-8">
                <!-- 1. Customer & Delivery Info Card -->
                <div class="order-card">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3 d-flex align-items-center gap-2" style="font-size: 13.5px;">
                        <i class="fas fa-user-circle text-primary"></i>
                        <span>Customer &amp; Shipping Information</span>
                    </h6>

                    <div class="row g-2">
                        <!-- Customer Phone with Live Search & Spinner -->
                        <div class="col-12 col-md-6 position-relative">
                            <label class="form-label" for="custPhoneInput">
                                Customer Phone <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm position-relative">
                                <span class="input-group-text bg-light text-muted"><i class="fas fa-phone" style="font-size: 11px;"></i></span>
                                <input type="text" id="custPhoneInput" name="customer_phone" class="form-control font-monospace bn-digit" placeholder="Type phone (018...)" autocomplete="off" required oninput="triggerCustomerSearch(this.value, 'phone')">
                                <span id="phoneSearchSpinner" class="spinner-border spinner-border-sm text-primary position-absolute end-0 top-50 translate-middle-y me-2 d-none" style="z-index: 10;"></span>
                            </div>
                            <div id="customerPhoneSuggestionsPanel" class="search-results-panel d-none"></div>
                        </div>

                        <!-- Customer Name with Live Search & Spinner -->
                        <div class="col-12 col-md-6 position-relative">
                            <label class="form-label" for="custNameInput">
                                Customer Name <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm position-relative">
                                <span class="input-group-text bg-light text-muted"><i class="fas fa-user" style="font-size: 11px;"></i></span>
                                <input type="text" id="custNameInput" name="customer_name" class="form-control" placeholder="Full name or search..." autocomplete="off" required oninput="triggerCustomerSearch(this.value, 'name')">
                                <span id="nameSearchSpinner" class="spinner-border spinner-border-sm text-primary position-absolute end-0 top-50 translate-middle-y me-2 d-none" style="z-index: 10;"></span>
                            </div>
                            <div id="customerNameSuggestionsPanel" class="search-results-panel d-none"></div>
                        </div>

                        <!-- District Selection -->
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="custDistrictSelect">
                                Delivery District <span class="text-danger">*</span>
                            </label>
                            <select id="custDistrictSelect" name="district" class="form-select form-select-sm" required onchange="handleDistrictChange(this.value)">
                                @foreach($districts as $d)
                                    <option value="{{ $d }}" {{ strtolower($d) === 'dhaka' ? 'selected' : '' }}>{{ $d }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Customer Email (Optional) -->
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="custEmailInput">Email Address (Optional)</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-muted"><i class="fas fa-envelope" style="font-size: 11px;"></i></span>
                                <input type="email" id="custEmailInput" name="customer_email" class="form-control" placeholder="customer@example.com">
                            </div>
                        </div>

                        <!-- Full Delivery Address -->
                        <div class="col-12">
                            <label class="form-label" for="custAddressInput">
                                Full Shipping Address <span class="text-danger">*</span>
                            </label>
                            <textarea id="custAddressInput" name="customer_address" rows="2" class="form-control form-control-sm" placeholder="House, Road, Area, Thana / Post..." required></textarea>
                        </div>
                    </div>
                </div>

                <!-- 2. Product Search & Ordered Items Card -->
                <div class="order-card p-0 overflow-hidden mb-0">
                    <div class="p-3 border-bottom bg-white">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 13.5px;">
                                <i class="fas fa-boxes text-warning"></i>
                                <span>Order Items &amp; Products</span>
                            </h6>
                            <span class="badge bg-primary-subtle text-primary border font-monospace bn-digit px-2 py-1" style="font-size: 11px;">
                                <span id="cartTotalItemsCount">0</span> Items in Cart
                            </span>
                        </div>

                        <!-- Product Live Search Bar -->
                        <div class="position-relative mt-2">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search" style="font-size: 11px;"></i></span>
                                <input type="text" id="productSearchInput" class="form-control border-start-0" placeholder="Type product name, SKU, or barcode to add items..." autocomplete="off" oninput="debounceProductSearch(this.value)">
                                <button type="button" class="btn btn-light border btn-compact" onclick="clearProductSearch()">Clear</button>
                            </div>
                            <div id="productResultsPanel" class="search-results-panel d-none"></div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="table-responsive" style="-webkit-overflow-scrolling: touch;">
                        <table class="table align-middle mb-0 manual-items-table text-nowrap">
                            <thead>
                                <tr>
                                    <th style="min-width: 200px;">Product / Variant</th>
                                    <th style="width: 120px;">Unit Price (৳)</th>
                                    <th style="width: 90px;" class="text-center">Qty</th>
                                    <th style="width: 110px;" class="text-end">Total (৳)</th>
                                    <th style="width: 50px;" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="manualCartTableBody">
                                <tr id="emptyCartRow">
                                    <td colspan="5" class="text-center py-5 text-muted small">
                                        <i class="fas fa-cart-arrow-down fa-2x mb-2 d-block opacity-50"></i>
                                        Search and add products from above to create order items.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Column: Order Configuration, Financials & Submission -->
            <div class="col-12 col-lg-4">
                <!-- 1. Channel, Status & Logistics -->
                <div class="order-card">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3" style="font-size: 13.5px;">Order Setup &amp; Channel</h6>

                    <!-- Order Source -->
                    <div class="mb-2">
                        <label class="form-label">Order Booking Channel <span class="text-danger">*</span></label>
                        <select name="order_source" class="form-select form-select-sm" required>
                            @foreach($sources as $sKey => $sLabel)
                                <option value="{{ $sKey }}" {{ $sKey === 'facebook' ? 'selected' : '' }}>{{ $sLabel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Initial Order Status -->
                    <div class="mb-2">
                        <label class="form-label">Order Lifecycle Status <span class="text-danger">*</span></label>
                        <select name="order_status" class="form-select form-select-sm" required>
                            <option value="confirmed" selected>Confirmed (Reserve Stock)</option>
                            <option value="processing">Processing (Deduct Physical Stock Now)</option>
                            <option value="pending">Pending (Review Required)</option>
                        </select>
                        <small class="text-muted d-block mt-1" style="font-size: 10.5px;">Confirmed preserves stock as estimated order.</small>
                    </div>

                    <!-- Courier Partner -->
                    <div class="mb-0">
                        <label class="form-label">Courier Partner</label>
                        <select name="courier" class="form-select form-select-sm">
                            <option value="">Assign Courier Later</option>
                            @foreach($couriers as $cr)
                                <option value="{{ $cr->courier_code }}">{{ $cr->courier_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- 2. Pricing, Shipping & Payment Summary -->
                <div class="order-card">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3" style="font-size: 13.5px;">Pricing &amp; Settlement</h6>

                    <!-- Product Subtotal -->
                    <div class="d-flex justify-content-between align-items-center mb-2 small">
                        <span class="text-muted">Product Subtotal:</span>
                        <strong class="font-monospace bn-digit text-dark" id="displaySubtotal">৳ 0.00</strong>
                    </div>

                    <!-- Delivery Charge Input -->
                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label mb-0">Delivery Charge (৳):</label>
                            <a href="javascript:void(0)" class="text-primary small text-decoration-none fw-semibold" style="font-size: 10.5px;" onclick="applyDefaultDeliveryCharge()">Auto Suggest</a>
                        </div>
                        <input type="number" id="inputDeliveryCharge" name="delivery_charge" class="form-control form-control-sm font-monospace bn-digit text-end" value="60" min="0" step="any" oninput="recalculateTotals()">
                    </div>

                    <!-- Discount Input -->
                    <div class="mb-2">
                        <label class="form-label">Special Discount (৳):</label>
                        <input type="number" id="inputDiscount" name="discount" class="form-control form-control-sm font-monospace bn-digit text-end text-danger" value="0" min="0" step="any" oninput="recalculateTotals()">
                    </div>

                    <!-- Grand Total Line -->
                    <div class="border-top pt-2 mb-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-dark fs-6">Grand Total:</span>
                        <span class="fw-bold text-primary fs-5 font-monospace bn-digit" id="displayGrandTotal">৳ 0.00</span>
                    </div>

                    <!-- Payment Method -->
                    <div class="mb-2">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" id="paymentMethodSelect" class="form-select form-select-sm" onchange="handlePaymentMethodChange(this.value)">
                            <option value="Cash on Delivery" selected>Cash on Delivery (COD)</option>
                            <option value="bKash">bKash (Advance / Full)</option>
                            <option value="Nagad">Nagad (Advance / Full)</option>
                            <option value="Rocket">Rocket</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cash">Cash (Showroom)</option>
                        </select>
                    </div>

                    <!-- Paid Amount Input -->
                    <div class="mb-2">
                        <label class="form-label">Advance / Paid Amount (৳):</label>
                        <input type="number" id="inputPaidAmount" name="paid_amount" class="form-control form-control-sm font-monospace bn-digit text-end text-success" value="0" min="0" step="any" oninput="recalculateTotals()">
                    </div>

                    <!-- Transaction ID / Sender Details & Global Media Chooser for Payment Proof -->
                    <div id="advancePaymentDetailsGroup" class="mb-2 d-none">
                        <div class="mb-2">
                            <label class="form-label">Transaction Reference ID</label>
                            <input type="text" name="transaction_id" class="form-control form-control-sm font-monospace bn-digit" placeholder="Trx ID (9J2834KA)">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Sender Phone Number</label>
                            <input type="text" name="sender_number" class="form-control form-control-sm font-monospace bn-digit" placeholder="Sender phone number">
                        </div>

                        <!-- Integrated Global Media Chooser for Advance Slip -->
                        <div class="mb-2">
                            <label class="form-label">Advance Payment Slip (Global Media Chooser)</label>
                            <input type="hidden" name="payment_screenshot" id="manualPaymentScreenshotInput" value="">

                            <div class="proof-media-surface">
                                <div class="proof-actions-wrap d-flex align-items-center gap-2 mb-2 flex-wrap">
                                    <button type="button" class="btn btn-primary btn-compact shadow-xs" onclick="chooseManualPaymentProofMedia()">
                                        <i class="fas fa-photo-film me-1"></i>
                                        <span>Choose from Media Library</span>
                                    </button>
                                </div>

                                <div id="paymentProofPreviewContainer" class="d-none align-items-center justify-content-between gap-2 bg-white border p-2" style="border-radius: 5px;">
                                    <div class="d-flex align-items-center gap-2 min-width-0">
                                        <div class="proof-thumb-box" onclick="zoomManualProof()" title="Click to Zoom">
                                            <img id="paymentProofPreviewImg" src="https://placehold.co/100x100?text=Slip" alt="Payment Proof" onerror="this.src='https://placehold.co/100x100?text=Slip';">
                                        </div>
                                        <div class="text-truncate">
                                            <span class="small fw-bold text-success d-block text-truncate" style="font-size: 11px;">
                                                <i class="fas fa-check-circle me-1"></i>Slip Attached
                                            </span>
                                            <small class="text-muted d-block text-truncate font-monospace" id="paymentProofPathText" style="font-size: 9.5px; max-width: 180px;"></small>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-1 flex-shrink-0">
                                        <button type="button" class="btn btn-outline-secondary btn-compact py-1 px-2" style="font-size: 10px !important;" onclick="zoomManualProof()" title="Zoom">
                                            <i class="fas fa-expand"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-compact py-1 px-2" style="font-size: 10px !important;" onclick="clearManualPaymentProofMedia()" title="Remove">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </div>

                                <div id="noPaymentProofNotice" class="text-center py-1 text-muted" style="font-size: 11px;">
                                    <i class="fas fa-paperclip me-1 opacity-50"></i> Optional: Attach advance slip or bank deposit receipt.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Due Amount Line -->
                    <div class="bg-light p-2 border rounded d-flex justify-content-between align-items-center mb-3">
                        <span class="small fw-semibold text-secondary">Due / Collectible COD:</span>
                        <span class="fw-bold text-danger font-monospace bn-digit" id="displayDueAmount">৳ 0.00</span>
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                        <label class="form-label">Internal / Packaging Notes</label>
                        <textarea name="notes" rows="2" class="form-control form-control-sm" placeholder="Packaging instruction, delivery deadline..."></textarea>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" id="submitOrderBtn" class="btn btn-primary w-100 fw-bold py-2 shadow-xs d-flex align-items-center justify-content-center gap-2" style="min-height: 38px; font-size: 13px;">
                        <span id="btnIcon"><i class="fas fa-check-circle"></i></span>
                        <span id="btnText">Book &amp; Create Order</span>
                        <span id="btnSpinner" class="spinner-border spinner-border-sm d-none"></span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- ========================================================================= -->
<!-- Modal 1: Stock Warning Confirmation Modal (Replaces native confirm())     -->
<!-- ========================================================================= -->
<div class="modal fade manual-order-modal-scope" id="stockWarningConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-warning-subtle text-warning d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 46px; height: 46px;">
                    <i class="fas fa-triangle-exclamation fs-4"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 14.5px;">Out of Stock Warning</h6>
                <p class="text-muted small mb-3" id="stockWarningModalMsg" style="font-size: 12px; line-height: 1.45;">This item is currently out of stock. Proceed anyway?</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light border btn-compact px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning text-dark btn-compact px-3 shadow-xs fw-bold" id="confirmStockProceedBtn">Proceed Anyway</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- Modal 2: Notice Dialog Modal (Replaces native browser alert())            -->
<!-- ========================================================================= -->
<div class="modal fade manual-order-modal-scope" id="orderNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
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

<!-- ========================================================================= -->
<!-- Modal 3: Payment Slip Zoom Lightbox Modal                                 -->
<!-- ========================================================================= -->
<div class="modal fade manual-order-modal-scope" id="manualProofZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg text-center p-2">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" style="font-size: 12.5px;">
                    <i class="fas fa-image text-primary me-1"></i> Advance Payment Receipt
                </span>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2">
                <img id="manualZoomImgTag" src="" alt="Zoomed Receipt" class="img-fluid border" style="max-height: 480px; object-fit: contain; border-radius: 4px;">
            </div>
        </div>
    </div>
</div>

<script>
// Global State for Cart, Products & Customers
let cartItems = [];
window.lastSearchedProducts = [];
window.lastSearchedCustomers = [];
let searchDebounceTimer = null;
let custDebounceTimer = null;
let pendingStockCallback = null;

// Clean Notification Modal Helper (Replaces native alert())
function showManualOrderNotice(message, title = 'Notification', type = 'info') {
    const titleEl  = document.getElementById('orderNoticeTitle');
    const msgEl    = document.getElementById('orderNoticeMsg');
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

// 1. Debounced Product Search
function debounceProductSearch(query) {
    clearTimeout(searchDebounceTimer);
    const q = query.trim();
    const panel = document.getElementById('productResultsPanel');

    if (q.length < 1) {
        panel.classList.add('d-none');
        panel.innerHTML = '';
        return;
    }

    searchDebounceTimer = setTimeout(() => {
        fetch(`{{ route('admin.order.search_products') }}?q=${encodeURIComponent(q)}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            renderProductSearchResults(data);
        })
        .catch(err => console.error(err));
    }, 250);
}

function renderProductSearchResults(items) {
    window.lastSearchedProducts = items || [];
    const panel = document.getElementById('productResultsPanel');

    if (!items || items.length === 0) {
        panel.innerHTML = `<div class="p-3 text-muted text-center small"><i class="fas fa-box-open me-1"></i> No matching products found.</div>`;
        panel.classList.remove('d-none');
        return;
    }

    let html = '';
    items.forEach((p, pIdx) => {
        const thumb = p.thumbnail || 'https://placehold.co/40x40?text=Item';

        if (p.has_variants && p.variants.length > 0) {
            p.variants.forEach((v, vIdx) => {
                const stockColor = v.stock > 0 ? 'text-success' : 'text-danger';
                html += `
                    <div class="search-result-item d-flex justify-content-between align-items-center" onclick="selectProductToAdd(${pIdx}, ${vIdx})">
                        <div class="d-flex align-items-center gap-2">
                            <img src="${thumb}" class="border object-fit-cover" style="width: 34px; height: 34px; border-radius: 4px;" onerror="this.src='https://placehold.co/40x40?text=Item'">
                            <div>
                                <span class="fw-bold text-dark d-block" style="font-size: 12px;">${escapeHtml(p.title)}</span>
                                <small class="text-primary font-monospace fw-semibold" style="font-size: 10.5px;">Variant: ${escapeHtml(v.title)} &bull; SKU: ${escapeHtml(v.sku)}</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="fw-bold text-dark font-monospace d-block bn-digit" style="font-size: 12px;">৳ ${parseFloat(v.price).toFixed(2)}</span>
                            <small class="${stockColor} font-monospace bn-digit" style="font-size: 10px;">Stock: ${v.stock}</small>
                        </div>
                    </div>
                `;
            });
        } else {
            const stockColor = p.stock > 0 ? 'text-success' : 'text-danger';
            html += `
                <div class="search-result-item d-flex justify-content-between align-items-center" onclick="selectProductToAdd(${pIdx}, null)">
                    <div class="d-flex align-items-center gap-2">
                        <img src="${thumb}" class="border object-fit-cover" style="width: 34px; height: 34px; border-radius: 4px;" onerror="this.src='https://placehold.co/40x40?text=Item'">
                        <div>
                            <span class="fw-bold text-dark d-block" style="font-size: 12px;">${escapeHtml(p.title)}</span>
                            <small class="text-muted font-monospace" style="font-size: 10.5px;">SKU: ${escapeHtml(p.sku)}</small>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="fw-bold text-dark font-monospace d-block bn-digit" style="font-size: 12px;">৳ ${parseFloat(p.price).toFixed(2)}</span>
                        <small class="${stockColor} font-monospace bn-digit" style="font-size: 10px;">Stock: ${p.stock}</small>
                    </div>
                </div>
            `;
        }
    });

    panel.innerHTML = html;
    panel.classList.remove('d-none');
}

function selectProductToAdd(pIdx, vIdx) {
    const p = window.lastSearchedProducts[pIdx];
    if (!p) return;

    if (vIdx !== null && vIdx !== undefined && p.variants && p.variants[vIdx]) {
        const v = p.variants[vIdx];
        addProductToCart(p.product_id, p.title, v.variant_id, v.title, v.sku, v.price, v.stock, p.thumbnail || 'https://placehold.co/40x40?text=Item');
    } else {
        addProductToCart(p.product_id, p.title, null, null, p.sku, p.price, p.stock, p.thumbnail || 'https://placehold.co/40x40?text=Item');
    }
}

function clearProductSearch() {
    document.getElementById('productSearchInput').value = '';
    const panel = document.getElementById('productResultsPanel');
    panel.innerHTML = '';
    panel.classList.add('d-none');
}

// 2. Add Product to Cart with Modal Stock Warning
function addProductToCart(productId, title, variantId, variantTitle, sku, price, stock, thumb) {
    const existingIndex = cartItems.findIndex(it => it.product_id === productId && it.variant_id === variantId);

    if (existingIndex > -1) {
        const item = cartItems[existingIndex];
        if (item.quantity + 1 > stock) {
            showManualOrderNotice(`Cannot add more. Available stock is ${stock}.`, 'Stock Limit', 'error');
            return;
        }
        item.quantity += 1;
        finishCartAddition();
    } else {
        if (stock <= 0) {
            document.getElementById('stockWarningModalMsg').innerText = `"${title}" is currently Out of Stock (${stock}). Proceed anyway?`;
            pendingStockCallback = function () {
                executePushCartItem(productId, title, variantId, variantTitle, sku, price, stock, thumb);
            };
            bootstrap.Modal.getOrCreateInstance(document.getElementById('stockWarningConfirmModal')).show();
            return;
        }
        executePushCartItem(productId, title, variantId, variantTitle, sku, price, stock, thumb);
    }
}

function executePushCartItem(productId, title, variantId, variantTitle, sku, price, stock, thumb) {
    cartItems.push({
        product_id: productId,
        product_title: title,
        variant_id: variantId,
        variant_title: variantTitle,
        sku: sku,
        unit_price: parseFloat(price),
        quantity: 1,
        max_stock: stock,
        thumbnail: thumb
    });
    finishCartAddition();
}

function finishCartAddition() {
    clearProductSearch();
    renderCartTable();
    recalculateTotals();
}

document.getElementById('confirmStockProceedBtn').addEventListener('click', function () {
    const modalEl = document.getElementById('stockWarningConfirmModal');
    bootstrap.Modal.getInstance(modalEl).hide();
    if (typeof pendingStockCallback === 'function') {
        pendingStockCallback();
        pendingStockCallback = null;
    }
});

// 3. Render Cart Table
function renderCartTable() {
    const tbody = document.getElementById('manualCartTableBody');
    const countBadge = document.getElementById('cartTotalItemsCount');

    if (cartItems.length === 0) {
        tbody.innerHTML = `
            <tr id="emptyCartRow">
                <td colspan="5" class="text-center py-5 text-muted small">
                    <i class="fas fa-cart-arrow-down fa-2x mb-2 d-block opacity-40"></i>
                    Search and add products from above to create order items.
                </td>
            </tr>
        `;
        countBadge.innerText = '0';
        return;
    }

    countBadge.innerText = cartItems.length;
    let html = '';

    cartItems.forEach((it, index) => {
        const lineTotal = it.unit_price * it.quantity;
        html += `
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <img src="${it.thumbnail}" class="border object-fit-cover" style="width: 34px; height: 34px; border-radius: 4px;" onerror="this.src='https://placehold.co/40x40?text=Item'">
                        <div class="min-w-0">
                            <strong class="text-dark d-block text-truncate" style="max-width: 220px; font-size: 12px;">${escapeHtml(it.product_title)}</strong>
                            ${it.variant_title ? `<span class="badge bg-primary font-monospace px-1 py-0" style="font-size: 9px;">${escapeHtml(it.variant_title)}</span>` : ''}
                            <small class="text-muted font-monospace d-block" style="font-size: 9.5px;">SKU: ${escapeHtml(it.sku)} &bull; Avail: ${it.max_stock}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm font-monospace bn-digit text-end" value="${it.unit_price.toFixed(2)}" min="0" step="any" onchange="updateItemPrice(${index}, this.value)" style="height: 28px;">
                </td>
                <td class="text-center">
                    <input type="number" class="form-control form-control-sm font-monospace bn-digit text-center mx-auto" style="width: 60px; height: 28px;" value="${it.quantity}" min="1" onchange="updateItemQty(${index}, this.value)">
                </td>
                <td class="text-end font-monospace fw-bold text-dark bn-digit">
                    ৳ ${lineTotal.toFixed(2)}
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-compact py-0 px-1" onclick="removeCartItem(${index})" title="Remove item">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
}

function updateItemPrice(index, val) {
    const p = parseFloat(val);
    if (!isNaN(p) && p >= 0) {
        cartItems[index].unit_price = p;
        renderCartTable();
        recalculateTotals();
    }
}

function updateItemQty(index, val) {
    const q = parseInt(val, 10);
    if (!isNaN(q) && q > 0) {
        cartItems[index].quantity = q;
        renderCartTable();
        recalculateTotals();
    }
}

function removeCartItem(index) {
    cartItems.splice(index, 1);
    renderCartTable();
    recalculateTotals();
}

// 4. Recalculate Totals
function recalculateTotals() {
    let subtotal = 0;
    cartItems.forEach(it => {
        subtotal += (it.unit_price * it.quantity);
    });

    const delivery = parseFloat(document.getElementById('inputDeliveryCharge').value) || 0;
    const discount = parseFloat(document.getElementById('inputDiscount').value) || 0;
    const grandTotal = Math.max(0, (subtotal + delivery) - discount);

    const paidInput = document.getElementById('inputPaidAmount');
    let paid = parseFloat(paidInput.value) || 0;
    if (paid > grandTotal) {
        paid = grandTotal;
        paidInput.value = grandTotal;
    }

    const due = Math.max(0, grandTotal - paid);

    document.getElementById('displaySubtotal').innerText = '৳ ' + subtotal.toFixed(2);
    document.getElementById('displayGrandTotal').innerText = '৳ ' + grandTotal.toFixed(2);
    document.getElementById('displayDueAmount').innerText = '৳ ' + due.toFixed(2);

    const advanceGroup = document.getElementById('advancePaymentDetailsGroup');
    if (paid > 0) {
        advanceGroup.classList.remove('d-none');
    } else {
        advanceGroup.classList.add('d-none');
    }
}

// 5. Global Media Chooser Integration for Advance Payment Slip
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

function chooseManualPaymentProofMedia() {
    if (typeof window.openMediaPicker === 'function') {
        window.openMediaPicker({
            multiple: false,
            onSelect: function(media) {
                const picked = extractMediaItem(media);
                if (picked && picked.path) {
                    document.getElementById('manualPaymentScreenshotInput').value = picked.path;
                    const previewImg = document.getElementById('paymentProofPreviewImg');
                    const pathText   = document.getElementById('paymentProofPathText');
                    const container  = document.getElementById('paymentProofPreviewContainer');
                    const emptyNotice = document.getElementById('noPaymentProofNotice');

                    if (previewImg) previewImg.src = picked.url;
                    if (pathText) pathText.innerText = picked.path;
                    if (container) {
                        container.classList.remove('d-none');
                        container.classList.add('d-flex');
                    }
                    if (emptyNotice) emptyNotice.classList.add('d-none');

                    showManualOrderNotice('Advance receipt attached from Media Library.', 'Attached', 'success');
                }
            }
        });
    } else {
        showManualOrderNotice('Media Library is loading or not available on this layout.', 'Notice');
    }
}

function clearManualPaymentProofMedia() {
    document.getElementById('manualPaymentScreenshotInput').value = '';
    const container   = document.getElementById('paymentProofPreviewContainer');
    const emptyNotice = document.getElementById('noPaymentProofNotice');
    if (container) {
        container.classList.add('d-none');
        container.classList.remove('d-flex');
    }
    if (emptyNotice) emptyNotice.classList.remove('d-none');
}

function zoomManualProof() {
    const previewImg = document.getElementById('paymentProofPreviewImg');
    const zoomImgTag = document.getElementById('manualZoomImgTag');
    if (previewImg && zoomImgTag && previewImg.src) {
        zoomImgTag.src = previewImg.src;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('manualProofZoomModal')).show();
    }
}

// 6. Customer Live Autofill
function triggerCustomerSearch(val, source) {
    clearTimeout(custDebounceTimer);
    const q = val.trim();
    const panel = source === 'name' 
        ? document.getElementById('customerNameSuggestionsPanel')
        : document.getElementById('customerPhoneSuggestionsPanel');

    const spinner = source === 'name'
        ? document.getElementById('nameSearchSpinner')
        : document.getElementById('phoneSearchSpinner');

    const otherPanel = source === 'name'
        ? document.getElementById('customerPhoneSuggestionsPanel')
        : document.getElementById('customerNameSuggestionsPanel');
    if (otherPanel) { otherPanel.classList.add('d-none'); otherPanel.innerHTML = ''; }

    if (q.length < 2) {
        if (panel) { panel.classList.add('d-none'); panel.innerHTML = ''; }
        if (spinner) spinner.classList.add('d-none');
        return;
    }

    if (spinner) spinner.classList.remove('d-none');

    custDebounceTimer = setTimeout(() => {
        fetch(`{{ route('admin.order.search_customers') }}?q=${encodeURIComponent(q)}`, {
            headers: { 
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            window.lastSearchedCustomers = data || [];
            
            if (!data || data.length === 0) {
                panel.innerHTML = `
                    <div class="p-2 text-center text-muted small">
                        <i class="fas fa-user-plus text-primary me-1"></i> No past record found with "<strong>${escapeHtml(q)}</strong>".
                        <div class="mt-1 text-secondary" style="font-size: 10px;">Enter details manually to save as new customer.</div>
                    </div>
                `;
                panel.classList.remove('d-none');
                return;
            }

            let html = '';
            data.forEach((c, idx) => {
                const srcLabel = c.source ? `<span class="badge bg-light text-secondary border px-1" style="font-size: 9px;">${escapeHtml(c.source)}</span>` : '';
                html += `
                    <div class="search-result-item d-flex justify-content-between align-items-center" onclick="selectCustomerByIndex(${idx})">
                        <div class="min-w-0">
                            <strong class="d-block text-dark" style="font-size: 12px;">${escapeHtml(c.name || 'Customer')}</strong>
                            <span class="text-primary font-monospace fw-semibold bn-digit" style="font-size: 11px;">${escapeHtml(c.phone || 'No phone')}</span>
                            <small class="text-muted d-block text-truncate" style="max-width: 250px; font-size: 10px;">${escapeHtml(c.address || 'No address')} (${escapeHtml(c.district || 'Dhaka')})</small>
                        </div>
                        <div class="text-end flex-shrink-0 ms-2">
                            ${srcLabel}
                            <span class="btn btn-xs btn-outline-primary py-0 px-2 mt-1 d-block btn-compact" style="font-size: 9.5px !important;">Select</span>
                        </div>
                    </div>
                `;
            });

            panel.innerHTML = html;
            panel.classList.remove('d-none');
        })
        .catch(err => {
            console.error('Customer fetch error:', err);
            panel.innerHTML = `<div class="p-2 text-danger small text-center">Search error. Type details directly.</div>`;
            panel.classList.remove('d-none');
        })
        .finally(() => {
            if (spinner) spinner.classList.add('d-none');
        });
    }, 250);
}

function selectCustomerByIndex(idx) {
    const c = window.lastSearchedCustomers[idx];
    if (!c) return;

    if (c.name) document.getElementById('custNameInput').value = c.name;
    if (c.phone) document.getElementById('custPhoneInput').value = c.phone;
    if (c.address) document.getElementById('custAddressInput').value = c.address;
    if (c.email) document.getElementById('custEmailInput').value = c.email;

    if (c.district) {
        const select = document.getElementById('custDistrictSelect');
        let matched = false;
        for (let i = 0; i < select.options.length; i++) {
            if (select.options[i].value.toLowerCase().trim() === c.district.toLowerCase().trim()) {
                select.selectedIndex = i;
                matched = true;
                break;
            }
        }
        if (!matched && c.district) {
            const opt = new Option(c.district, c.district, true, true);
            select.add(opt);
        }
        handleDistrictChange(c.district);
    }

    closeCustomerPanels();
}

function closeCustomerPanels() {
    const p1 = document.getElementById('customerPhoneSuggestionsPanel');
    const p2 = document.getElementById('customerNameSuggestionsPanel');
    if (p1) { p1.classList.add('d-none'); p1.innerHTML = ''; }
    if (p2) { p2.classList.add('d-none'); p2.innerHTML = ''; }
}

function handleDistrictChange(district) {
    const chargeInput = document.getElementById('inputDeliveryCharge');
    if (district.toLowerCase().trim() === 'dhaka') {
        chargeInput.value = 60;
    } else {
        chargeInput.value = 120;
    }
    recalculateTotals();
}

function applyDefaultDeliveryCharge() {
    const district = document.getElementById('custDistrictSelect').value;
    handleDistrictChange(district);
}

function handlePaymentMethodChange(val) {
    const paidInput = document.getElementById('inputPaidAmount');
    if (val === 'Cash on Delivery') {
        paidInput.value = 0;
    }
    recalculateTotals();
}

// 7. Form Submission Handler with Payload Assembly
function handleManualOrderSubmit(e) {
    e.preventDefault();

    const alertBox = document.getElementById('formAlertBox');
    alertBox.classList.add('d-none');
    alertBox.innerHTML = '';

    if (cartItems.length === 0) {
        showManualOrderNotice('Please add at least one product item to create the order.', 'Items Required', 'error');
        return;
    }

    const form = document.getElementById('manualOrderForm');
    const formData = new FormData(form);

    const payload = {
        customer_name: formData.get('customer_name'),
        customer_phone: formData.get('customer_phone'),
        customer_email: formData.get('customer_email'),
        customer_address: formData.get('customer_address'),
        district: formData.get('district'),
        order_source: formData.get('order_source'),
        order_status: formData.get('order_status'),
        courier: formData.get('courier'),
        payment_method: formData.get('payment_method'),
        delivery_charge: parseFloat(formData.get('delivery_charge')) || 0,
        discount: parseFloat(formData.get('discount')) || 0,
        paid_amount: parseFloat(formData.get('paid_amount')) || 0,
        transaction_id: formData.get('transaction_id'),
        sender_number: formData.get('sender_number'),
        payment_screenshot: document.getElementById('manualPaymentScreenshotInput')?.value || '',
        notes: formData.get('notes'),
        items: cartItems.map(it => ({
            product_id: it.product_id,
            product_title: it.product_title,
            variant_id: it.variant_id,
            variant_title: it.variant_title,
            sku: it.sku,
            quantity: it.quantity,
            unit_price: it.unit_price
        }))
    };

    const submitBtn = document.getElementById('submitOrderBtn');
    const btnIcon   = document.getElementById('btnIcon');
    const btnText   = document.getElementById('btnText');
    const btnSpinner = document.getElementById('btnSpinner');

    submitBtn.disabled = true;
    btnIcon.classList.add('d-none');
    btnText.innerText = 'Creating Order...';
    btnSpinner.classList.remove('d-none');

    fetch("{{ route('admin.order.store_manual') }}", {
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
        if (!res.ok) {
            throw new Error(data.message || 'Failed to create order.');
        }
        return data;
    })
    .then(data => {
        showManualOrderNotice('Order created successfully!', 'Success', 'success');
        setTimeout(() => {
            if (data.success && data.redirect) {
                window.location.href = data.redirect;
            } else {
                window.location.href = "{{ route('admin.order.online') }}";
            }
        }, 1000);
    })
    .catch(err => {
        alertBox.innerHTML = `<i class="fas fa-exclamation-triangle me-1"></i> ${err.message}`;
        alertBox.classList.remove('d-none');
        alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    })
    .finally(() => {
        submitBtn.disabled = false;
        btnIcon.classList.remove('d-none');
        btnText.innerText = 'Book & Create Order';
        btnSpinner.classList.add('d-none');
    });
}

function escapeHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// Click outside to close suggestion dropdowns
document.addEventListener('click', function(e) {
    const prodPanel = document.getElementById('productResultsPanel');
    const prodInput = document.getElementById('productSearchInput');
    if (prodPanel && !prodPanel.contains(e.target) && e.target !== prodInput) {
        prodPanel.classList.add('d-none');
    }

    const p1 = document.getElementById('customerPhoneSuggestionsPanel');
    const p2 = document.getElementById('customerNameSuggestionsPanel');
    const phoneIn = document.getElementById('custPhoneInput');
    const nameIn  = document.getElementById('custNameInput');

    if (p1 && !p1.contains(e.target) && e.target !== phoneIn) {
        p1.classList.add('d-none');
    }
    if (p2 && !p2.contains(e.target) && e.target !== nameIn) {
        p2.classList.add('d-none');
    }
});
</script>
@endsection