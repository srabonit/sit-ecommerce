@extends('layouts.admin')

@section('title', 'POS Terminal - ' . $branch->name)

@section('admin_content')
<!-- Html5-QRCode Library for Mobile / Tablet Camera Scanning -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<style>
    /* Global Strict 5px Element Radius */
    #posTerminalRoot .btn,
    #posTerminalRoot .btn-sm,
    #posTerminalRoot button,
    #posTerminalRoot .badge,
    #posTerminalRoot .form-control,
    #posTerminalRoot .form-select,
    #posTerminalRoot .input-group-text,
    #posTerminalRoot .card,
    #posTerminalRoot .alert,
    #posTerminalRoot .pos-scanner-strip,
    #posTerminalRoot .pos-item-row,
    #posTerminalRoot .pos-mobile-nav-btn,
    #posTerminalRoot .pos-mobile-sticky-bar,
    .pos-modal-scope .modal-content,
    .pos-modal-scope .btn,
    .pos-modal-scope .form-control {
        border-radius: 5px !important;
    }

    #posTerminalRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #posTerminalRoot .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Safe Bengali & Monospace Typography */
    .bn-digit,
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

    /* Top Super-Shop Scanner Bar */
    .pos-scanner-strip {
        background: #0f172a;
        color: #ffffff;
        padding: 6px 8px;
        display: flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.15);
    }
    .scanner-live-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 10.5px;
        font-weight: 700;
        color: #22c55e;
        white-space: nowrap;
    }
    .scanner-pulse {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background-color: #22c55e;
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
        animation: pulseGreen 1.8s infinite;
    }
    @keyframes pulseGreen {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 5px rgba(34, 197, 94, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }
    .pos-barcode-input {
        background: #ffffff !important;
        color: #0f172a !important;
        font-weight: 700;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        letter-spacing: 0.3px;
        border: 2px solid #3b82f6 !important;
        height: 32px;
        font-size: 12px;
    }
    .pos-barcode-input:focus {
        border-color: #60a5fa !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25) !important;
    }

    /* Memo Cart Panel Styling */
    .pos-memo-panel {
        font-size: 11px !important;
    }
    .pos-memo-panel .form-control-xs, 
    .pos-memo-panel .form-select-xs {
        height: 26px !important;
        padding: 2px 6px !important;
        font-size: 11px !important;
    }
    .pos-item-row {
        border-bottom: 1px dashed #e2e8f0;
        padding: 6px 4px;
        transition: background-color 0.15s ease;
    }
    .pos-item-row:hover {
        background-color: #f8fafc;
    }
    .qty-btn-mini {
        width: 22px !important;
        height: 22px !important;
        line-height: 16px !important;
        font-size: 11px !important;
        padding: 0 !important;
        font-weight: bold;
    }
    .qty-input-mini {
        width: 36px !important;
        height: 22px !important;
        font-size: 11px !important;
        padding: 0 !important;
        font-weight: bold;
        text-align: center;
    }
    .pos-action-btn {
        font-size: 11px !important;
        padding: 7px 4px !important;
        font-weight: 700;
        line-height: 1.2;
    }

    /* Product Grid Card Tuning */
    #posProductGrid .card {
        border-radius: 5px !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important;
    }
    #posProductGrid .btn {
        border-radius: 5px !important;
    }

    /* Bulletproof Clean Pagination inside Grid */
    #posProductGrid .card:has(.pagination),
    #posProductGrid div:has(> .pagination),
    #posProductGrid nav:has(.pagination) {
        border-radius: 5px !important;
        border: 1px solid #e2e8f0 !important;
        background: #ffffff !important;
        padding: 10px 8px !important;
        margin-top: 8px !important;
        margin-bottom: 16px !important;
    }
    #posProductGrid .pagination {
        margin: 6px 0 0 0 !important;
        gap: 3px !important;
        justify-content: center !important;
        flex-wrap: wrap !important;
        padding: 0 !important;
        list-style: none !important;
    }
    #posProductGrid .page-item .page-link {
        border-radius: 5px !important;
        font-size: 11px !important;
        padding: 3px 8px !important;
        color: #334155 !important;
        border: 1px solid #cbd5e1 !important;
        background: #ffffff !important;
        font-weight: 600 !important;
    }
    #posProductGrid .page-item.active .page-link {
        background: #2563eb !important;
        color: #ffffff !important;
        border-color: #2563eb !important;
    }

    /* Desktop Fixed Heights (>= 992px) */
    @media (min-width: 992px) {
        .pos-product-scroll-container {
            height: calc(100vh - 245px);
            align-content: flex-start;
        }
        .pos-memo-card {
            height: calc(100vh - 120px);
        }
        .pos-cart-items-scroll {
            max-height: calc(100vh - 400px);
        }
        .pos-mobile-nav-bar,
        .pos-mobile-sticky-dock {
            display: none !important;
        }
    }

    /* Mobile Responsive Architecture (< 992px) */
    @media (max-width: 991.98px) {
        .pos-mobile-nav-bar {
            display: flex !important;
            gap: 4px;
            margin-bottom: 8px;
        }
        .pos-mobile-nav-btn {
            flex: 1;
            padding: 8px 6px;
            font-size: 11.5px;
            font-weight: 700;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #475569;
            text-align: center;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .pos-mobile-nav-btn.active {
            background: #2563eb !important;
            color: #ffffff !important;
            border-color: #2563eb !important;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);
        }
        .pos-view-column.mobile-hidden {
            display: none !important;
        }
        .pos-product-scroll-container {
            height: auto;
            max-height: none;
            padding-bottom: 70px;
        }
        .pos-memo-card {
            height: auto;
            padding-bottom: 20px;
        }
        .pos-cart-items-scroll {
            max-height: 280px;
        }
        .pos-scanner-strip {
            padding: 5px 6px;
        }
        .scanner-live-badge {
            font-size: 10px;
        }
    }

    /* Floating Sticky Quick Checkout Dock on Mobile */
    .pos-mobile-sticky-dock {
        position: fixed;
        bottom: 8px;
        left: 8px;
        right: 8px;
        z-index: 1040;
    }
</style>

<div class="container-fluid py-2 px-2 px-md-3" id="posTerminalRoot">
    <!-- Top Branch Header Bar (Compact & Single Row on Mobile) -->
    <div class="d-flex justify-content-between align-items-center mb-2 bg-white p-2 px-3 shadow-xs border">
        <div class="d-flex align-items-center gap-1.5 min-width-0">
            <span class="badge bg-warning text-dark fw-bold px-2 py-1 text-truncate" style="font-size: 11px;">
                <i class="fas fa-store me-1"></i> {{ $branch->name }}
            </span>
            <small class="text-muted font-monospace bn-digit text-truncate d-none d-sm-inline" style="font-size: 10px;">{{ $branch->code }}</small>
        </div>
        <a href="{{ route('admin.pos.index') }}" class="btn btn-outline-secondary btn-compact py-1 px-2.5 flex-shrink-0" style="font-size: 10.5px !important;">
            <i class="fas fa-exchange-alt me-1"></i> Change Branch
        </a>
    </div>

    <!-- Mobile Segmented Navigation Bar -->
    <div class="pos-mobile-nav-bar d-lg-none">
        <div class="pos-mobile-nav-btn active" id="tabBtnProducts" onclick="switchMobilePosTab('products')">
            <i class="fas fa-boxes-stacked me-1"></i> Products &amp; Scanner
        </div>
        <div class="pos-mobile-nav-btn" id="tabBtnMemo" onclick="switchMobilePosTab('memo')">
            <i class="fas fa-receipt me-1"></i> Cart Memo (<span id="mobileTabCartCount" class="bn-digit">0</span>)
        </div>
    </div>

    <div class="row g-2 align-items-start">
        <!-- Left Column: Barcode Scanner, Filters & Product Grid (8 Cols on PC) -->
        <div class="col-12 col-lg-8 pos-view-column" id="posProductsColumn">
            <!-- 1. Super-Shop USB Barcode Scanner Strip -->
            <div class="pos-scanner-strip mb-2">
                <div class="scanner-live-badge">
                    <span class="scanner-pulse"></span>
                    <i class="fas fa-barcode"></i>
                    <span class="d-none d-sm-inline">Scanner:</span>
                </div>
                
                <div class="input-group input-group-sm flex-grow-1">
                    <input type="text" id="posBarcodeScannerInput" class="form-control form-control-sm pos-barcode-input font-monospace bn-digit" 
                           placeholder="Scan USB code or hit Enter..." autocomplete="off">
                    
                    <button class="btn btn-primary fw-bold px-2.5 text-nowrap" type="button" id="btnManualScanSubmit" onclick="triggerBarcodeScanFromInput()">
                        <i class="fas fa-plus"></i>
                        <span class="d-none d-sm-inline ms-1">Add</span>
                    </button>

                    <!-- Camera Scanner Trigger -->
                    <button class="btn btn-info text-white fw-bold px-2" type="button" onclick="openCameraScannerModal()" title="Camera Scan">
                        <i class="fas fa-camera"></i>
                    </button>
                </div>
            </div>

            <!-- 2. Manual Search & Filter Bar -->
            <div class="card border-0 shadow-xs p-2 bg-white mb-2">
                <div class="row g-1.5">
                    <div class="col-12 col-md-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0 py-1" style="height: 31px;"><i class="fas fa-search text-muted" style="font-size: 10px;"></i></span>
                            <input type="text" id="posSearchProduct" class="form-control border-start-0 py-1" placeholder="Search title, SKU, barcode..." style="height: 31px; font-size: 11.5px;">
                            <button class="btn btn-outline-light border border-start-0 text-muted d-none" type="button" id="clearProdSearchBtn"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <select id="posCategoryFilter" class="form-select form-select-sm" style="height: 31px; font-size: 11px;">
                            <option value="">Category (All)</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <select id="posBrandFilter" class="form-select form-select-sm" style="height: 31px; font-size: 11px;">
                            <option value="">Brand (All)</option>
                            @foreach($brands as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- 3. Dynamic Products Grid -->
            <div class="position-relative bg-white border p-2 mb-2 mb-lg-0">
                <div id="posGridLoader" class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-none justify-content-center align-items-center" style="z-index: 10;">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
                <div class="row g-2 overflow-y-auto pos-product-scroll-container" id="posProductGrid">
                    <!-- Products loaded via AJAX -->
                </div>
            </div>
        </div>

        <!-- Right Column: POS Memo & Billing Control (4 Cols on PC, Toggleable on Mobile) -->
        <div class="col-12 col-lg-4 pos-memo-panel pos-view-column" id="posMemoColumn">
            <div class="card border shadow-xs bg-white p-2 d-flex flex-column justify-content-between pos-memo-card">
                <div>
                    <!-- Customer Section -->
                    <div class="border p-2 bg-light mb-2 position-relative">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-dark" style="font-size: 11px;">Customer</span>
                            <button type="button" class="btn btn-warning text-white fw-bold py-0 px-2" style="font-size: 9.5px;" data-bs-toggle="modal" data-bs-target="#newCustomerModal">
                                + New
                            </button>
                        </div>

                        <div class="input-group input-group-sm mb-1">
                            <input type="text" id="customerSearchInput" class="form-control form-control-xs" placeholder="Search phone / name..." autocomplete="off">
                            <span id="custSpinner" class="input-group-text bg-white p-0 px-1 border-start-0 d-none"><span class="spinner-border spinner-border-sm text-primary" style="width: 10px; height: 10px;"></span></span>
                        </div>

                        <div id="custSearchResults" class="position-absolute start-0 end-0 bg-white border shadow-lg mt-1 d-none" style="top: 60px; z-index: 1050; max-height: 160px; overflow-y: auto;"></div>

                        <select id="posCustomerSelect" class="form-select form-select-xs">
                            <option value="">-- Walk-in Customer --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" data-name="{{ $c->name }}" data-phone="{{ $c->phone }}">{{ $c->name }} ({{ $c->phone ?? 'N/A' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Cart Header -->
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-1 mb-1">
                        <span class="fw-bold text-dark">Order Items (<span id="cartCountBadge" class="text-primary font-monospace bn-digit">0</span>)</span>
                        <a href="javascript:void(0)" class="text-danger text-decoration-none fw-semibold" style="font-size: 10.5px;" onclick="clearPosCart()">
                            <i class="fas fa-trash-alt me-1"></i>Clear
                        </a>
                    </div>

                    <!-- Cart Items Scroll List -->
                    <div id="cartItemsList" class="overflow-y-auto mb-2 pe-1 pos-cart-items-scroll">
                        <div class="text-center py-4 text-muted" id="cartEmptyPlaceholder" style="font-size: 11px;">
                            Cart is empty. Scan barcode or click items.
                        </div>
                    </div>
                </div>

                <!-- Billing Summary & Checkout Buttons -->
                <div class="border-top pt-2 mt-auto">
                    <div class="row g-1 mb-1">
                        <div class="col-6">
                            <span class="text-muted d-block" style="font-size: 10px;">Shipping (৳)</span>
                            <input type="number" id="posShipping" class="form-control form-control-xs font-monospace bn-digit" value="0" min="0" oninput="renderCartSummary()">
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block" style="font-size: 10px;">Discount (৳)</span>
                            <input type="number" id="posDiscount" class="form-control form-control-xs font-monospace bn-digit" value="0" min="0" oninput="renderCartSummary()">
                        </div>
                    </div>

                    <div class="row g-1 mb-2">
                        <div class="col-6">
                            <span class="text-muted d-block" style="font-size: 10px;">Payment Method</span>
                            <select id="posPayMethod" class="form-select form-select-xs">
                                @foreach($paymentMethods as $pm)
                                    <option value="{{ $pm }}">{{ $pm }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block" style="font-size: 10px;">Paid Amount</span>
                            <input type="number" id="posPaidAmount" class="form-control form-control-xs fw-bold text-success font-monospace bn-digit" value="0" min="0">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                        <span class="fw-bold text-muted">Total:</span>
                        <h6 class="fw-bold text-dark mb-0 fs-6 font-monospace bn-digit" id="grandTotalText">0.00 ৳</h6>
                    </div>

                    <!-- 3 Compact Action Buttons -->
                    <div class="row g-1">
                        <div class="col-4">
                            <button type="button" class="btn btn-outline-dark w-100 pos-action-btn" onclick="submitPosOrder('only_order')">
                                <i class="fas fa-save me-1"></i>Order
                            </button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-primary w-100 pos-action-btn" onclick="submitPosOrder('order_a5_print')">
                                <i class="fas fa-print me-1"></i>A5 Print
                            </button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-warning w-100 pos-action-btn text-white" onclick="submitPosOrder('pos_thermal_print')">
                                <i class="fas fa-receipt me-1"></i>POS Print
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Sticky Quick Checkout Dock (Appears when products are in cart) -->
    <div id="posMobileStickyDock" class="pos-mobile-sticky-dock d-lg-none" style="display: none;">
        <div class="d-flex align-items-center justify-content-between p-2 px-3 bg-dark text-white shadow-lg border border-secondary" style="border-radius: 5px;">
            <div>
                <span class="badge bg-primary text-white font-monospace bn-digit px-2 py-1" id="stickyCartItemCount">0 Items</span>
                <strong class="ms-1.5 font-monospace bn-digit fs-6" id="stickyCartGrandTotal">0.00 ৳</strong>
            </div>
            <button type="button" class="btn btn-warning text-dark fw-bold btn-compact py-1 px-3" onclick="switchMobilePosTab('memo')">
                <span>View Cart / Pay</span> <i class="fas fa-arrow-right ms-1"></i>
            </button>
        </div>
    </div>
</div>

<!-- Modal 1: New Customer Create -->
<div class="modal fade pos-modal-scope" id="newCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg p-3" style="font-size: 12px;">
            <h6 class="fw-bold text-dark mb-2"><i class="fas fa-user-plus text-warning me-1"></i> New Customer</h6>
            <div class="mb-2">
                <input type="text" id="custName" class="form-control form-control-sm" placeholder="Full Name *">
            </div>
            <div class="mb-2">
                <input type="text" id="custPhone" class="form-control form-control-sm font-monospace bn-digit" placeholder="Phone Number *">
            </div>
            <div class="mb-2">
                <input type="email" id="custEmail" class="form-control form-control-sm" placeholder="Email (Optional)">
            </div>
            <div class="mb-2">
                <textarea id="custAddress" class="form-control form-control-sm" rows="2" placeholder="Address (Optional)"></textarea>
            </div>
            <div class="d-flex justify-content-end gap-1">
                <button type="button" class="btn btn-light border btn-compact" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning fw-bold text-white btn-compact" onclick="saveCustomerAjax()">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Clean Custom Global Notice Modal -->
<div class="modal fade pos-modal-scope" id="posAlertModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div id="posAlertIcon" class="mb-2"></div>
            <h6 class="fw-bold text-dark mb-1" id="posAlertTitle">Notice</h6>
            <p class="text-muted small mb-3" id="posAlertMessage" style="font-size: 11.5px; line-height: 1.4;"></p>
            <div class="d-flex justify-content-center">
                <button type="button" class="btn btn-dark btn-compact px-4" data-bs-dismiss="modal" onclick="refocusBarcodeScanner()">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 3: Multiple Matches / Variant Selection Modal -->
<div class="modal fade pos-modal-scope" id="multipleMatchesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg p-3">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                <h6 class="fw-bold text-dark mb-0"><i class="fas fa-layer-group text-primary me-1"></i> Select Variant / Product</h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <p class="small text-muted mb-2">Multiple records matched this barcode. Select the correct item:</p>
            <div id="multipleMatchesList" class="list-group list-group-flush border" style="max-height: 250px; overflow-y: auto;"></div>
        </div>
    </div>
</div>

<!-- Modal 4: Mobile Camera Scanner Modal -->
<div class="modal fade pos-modal-scope" id="cameraScanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg p-3 text-center">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                <h6 class="fw-bold text-dark mb-0"><i class="fas fa-camera text-info me-1"></i> Camera Scanner</h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal" onclick="stopCameraScanner()"></button>
            </div>
            <div id="camera-reader-viewport" class="border bg-dark" style="width: 100%; min-height: 220px;"></div>
            <small class="text-muted d-block mt-2" style="font-size: 11px;">Hold barcode in front of camera. Auto-adds upon detection.</small>
        </div>
    </div>
</div>

<script>
const CSRF = "{{ csrf_token() }}";
const BRANCH_ID = {{ $branch->id }};
const BARCODE_SCAN_URL = "{{ route('admin.pos.scan.barcode') }}";
let posCart = [];
let isScanning = false;
let cameraScannerInstance = null;
let currentMobileTab = 'products';

// Audio synth for cashier beep without external assets
function playBeep(type = 'success') {
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        
        if (type === 'success') {
            osc.frequency.setValueAtTime(1760, audioCtx.currentTime);
            gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.1);
            osc.start();
            osc.stop(audioCtx.currentTime + 0.1);
        } else {
            osc.frequency.setValueAtTime(320, audioCtx.currentTime);
            gain.gain.setValueAtTime(0.25, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.22);
            osc.start();
            osc.stop(audioCtx.currentTime + 0.22);
        }
    } catch(e) {}
}

// Notice Modal Dialog Trigger
function showPosAlert(message, title = 'Notice', type = 'info') {
    playBeep(type === 'success' ? 'success' : 'error');

    const iconBox = document.getElementById('posAlertIcon');
    if (type === 'success') {
        iconBox.innerHTML = '<i class="fas fa-check-circle fa-3x text-success"></i>';
    } else if (type === 'warning') {
        iconBox.innerHTML = '<i class="fas fa-exclamation-triangle fa-3x text-warning"></i>';
    } else if (type === 'danger') {
        iconBox.innerHTML = '<i class="fas fa-times-circle fa-3x text-danger"></i>';
    } else {
        iconBox.innerHTML = '<i class="fas fa-info-circle fa-3x text-primary"></i>';
    }
    
    document.getElementById('posAlertTitle').textContent = title;
    document.getElementById('posAlertMessage').textContent = message;
    
    const alertModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('posAlertModal'));
    alertModal.show();
}

// Mobile Tab Switcher Handler (Products vs Cart Memo)
function switchMobilePosTab(tab) {
    currentMobileTab = tab;
    const prodCol = document.getElementById('posProductsColumn');
    const memoCol = document.getElementById('posMemoColumn');
    const tabProd = document.getElementById('tabBtnProducts');
    const tabMemo = document.getElementById('tabBtnMemo');
    const stickyDock = document.getElementById('posMobileStickyDock');

    if (tab === 'products') {
        prodCol.classList.remove('mobile-hidden');
        memoCol.classList.add('mobile-hidden');
        tabProd.classList.add('active');
        tabMemo.classList.remove('active');
        if (stickyDock && posCart.length > 0 && window.innerWidth < 992) {
            stickyDock.style.display = 'block';
        }
    } else {
        prodCol.classList.add('mobile-hidden');
        memoCol.classList.remove('mobile-hidden');
        tabProd.classList.remove('active');
        tabMemo.classList.add('active');
        if (stickyDock) {
            stickyDock.style.display = 'none';
        }
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

// Smart Focus Guard for USB Barcode Scanner
function refocusBarcodeScanner() {
    setTimeout(() => {
        const scannerInput = document.getElementById('posBarcodeScannerInput');
        const active = document.activeElement;

        const ignoreIds = ['customerSearchInput', 'posSearchProduct', 'posShipping', 'posDiscount', 'posPaidAmount', 'custName', 'custPhone', 'custEmail', 'custAddress'];
        if (active && (ignoreIds.includes(active.id) || active.tagName === 'SELECT' || active.tagName === 'TEXTAREA')) {
            return;
        }

        if (document.querySelector('.modal.show')) {
            return;
        }

        if (scannerInput) {
            scannerInput.focus();
        }
    }, 150);
}

// Global Keyboard Scanner Catcher
document.addEventListener('keydown', function (e) {
    const scannerInput = document.getElementById('posBarcodeScannerInput');
    const active = document.activeElement;

    const isTypingElsewhere = active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA' || active.tagName === 'SELECT');
    if (!isTypingElsewhere && e.key && e.key.length === 1 && !e.ctrlKey && !e.altKey && !e.metaKey) {
        if (scannerInput) {
            scannerInput.focus();
        }
    }
});

// Barcode Lookup & Auto Cart Addition Engine
function processBarcodeScan(rawBarcode) {
    const barcode = (rawBarcode || '').trim();
    if (!barcode) return;

    if (isScanning) return;
    isScanning = true;

    const scannerInput = document.getElementById('posBarcodeScannerInput');
    if (scannerInput) {
        scannerInput.disabled = true;
    }

    const url = new URL(BARCODE_SCAN_URL, window.location.origin);
    url.searchParams.set('barcode', barcode);
    url.searchParams.set('branch_id', BRANCH_ID);

    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.product) {
            playBeep('success');
            addScannedProductToCart(data.product);
            if (scannerInput) scannerInput.value = '';
        } else if (data.matched === 'multiple') {
            playBeep('error');
            handleMultipleBarcodeMatches(data.variants || data.products || []);
        } else {
            playBeep('error');
            showPosAlert(data.message || 'Item barcode not found in this branch.', 'Barcode Not Found', 'warning');
        }
    })
    .catch(err => {
        console.error('Scan error:', err);
        playBeep('error');
        showPosAlert('Server connection error during barcode lookup.', 'Server Error', 'danger');
    })
    .finally(() => {
        isScanning = false;
        if (scannerInput) {
            scannerInput.disabled = false;
            scannerInput.value = '';
            refocusBarcodeScanner();
        }
    });
}

function triggerBarcodeScanFromInput() {
    const scannerInput = document.getElementById('posBarcodeScannerInput');
    if (scannerInput && scannerInput.value.trim()) {
        processBarcodeScan(scannerInput.value.trim());
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const scannerInput = document.getElementById('posBarcodeScannerInput');
    if (scannerInput) {
        scannerInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                triggerBarcodeScanFromInput();
            }
        });
    }

    // Set initial view state for mobile
    if (window.innerWidth < 992) {
        switchMobilePosTab('products');
    }

    refocusBarcodeScanner();
});

function addScannedProductToCart(p) {
    const targetKeyId = p.variant_id ? `var-${p.variant_id}` : p.id;
    const existing = posCart.find(item => (item.variant_id ? `var-${item.variant_id}` : item.id) === targetKeyId);

    const currentQtyInCart = existing ? existing.qty : 0;
    if (p.stock > 0 && (currentQtyInCart + 1) > p.stock) {
        playBeep('error');
        showPosAlert(`"${p.title}" is out of stock! Current stock: ${p.stock}`, 'Stock Alert', 'warning');
        return;
    }

    if (existing) {
        existing.qty += 1;
    } else {
        posCart.push({
            id: p.id,
            variant_id: p.variant_id || null,
            title: p.title,
            price: parseFloat(p.price) || 0,
            stock: parseInt(p.stock) || 0,
            qty: 1,
            code: p.product_code || p.barcode || ''
        });
    }

    renderCart();
}

function handleMultipleBarcodeMatches(items) {
    const list = document.getElementById('multipleMatchesList');
    list.innerHTML = '';

    items.forEach(it => {
        const title = it.main_title || it.title;
        const details = [it.color, it.size].filter(Boolean).join(' / ');
        const price = it.variant_sale_price || it.sale_price || it.regular_price || 0;
        const stock = it.branch_stock ?? it.stock ?? 0;
        const code = it.variant_sku || it.product_code || '';

        const a = document.createElement('a');
        a.href = 'javascript:void(0)';
        a.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 px-3';
        a.innerHTML = `
            <div>
                <strong class="text-dark d-block">${title} ${details ? '('+details+')' : ''}</strong>
                <small class="text-muted font-monospace bn-digit">Code: ${code} | Stock: ${stock}</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="fw-bold text-primary font-monospace bn-digit">${Number(price).toFixed(0)}৳</span>
                <button type="button" class="btn btn-primary btn-compact py-0 px-2 fw-semibold">Add</button>
            </div>
        `;

        a.onclick = function () {
            addScannedProductToCart({
                id: it.product_id || it.id,
                variant_id: it.variant_id || null,
                title: title + (details ? ' ('+details+')' : ''),
                price: price,
                stock: stock,
                product_code: code
            });
            bootstrap.Modal.getInstance(document.getElementById('multipleMatchesModal')).hide();
            refocusBarcodeScanner();
        };

        list.appendChild(a);
    });

    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('multipleMatchesModal'));
    modal.show();
}

// Mobile Camera Scanner Engine
function openCameraScannerModal() {
    const modalEl = document.getElementById('cameraScanModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();

    if (typeof Html5Qrcode !== 'undefined') {
        setTimeout(() => {
            startCameraScanner();
        }, 300);
    } else {
        showPosAlert('Camera scanner library failed to load. Please use USB or manual entry.', 'Camera Unavailable', 'warning');
    }
}

function startCameraScanner() {
    if (cameraScannerInstance) {
        cameraScannerInstance.stop().catch(() => {});
    }

    cameraScannerInstance = new Html5Qrcode("camera-reader-viewport");
    cameraScannerInstance.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: { width: 250, height: 150 } },
        (decodedText) => {
            stopCameraScanner();
            bootstrap.Modal.getInstance(document.getElementById('cameraScanModal')).hide();
            processBarcodeScan(decodedText);
        },
        () => {}
    ).catch(err => {
        console.error("Camera access error:", err);
        showPosAlert("Could not start camera. Please verify camera permissions.", "Camera Error", "warning");
    });
}

function stopCameraScanner() {
    if (cameraScannerInstance) {
        cameraScannerInstance.stop().then(() => {
            cameraScannerInstance.clear();
            cameraScannerInstance = null;
        }).catch(() => {});
    }
}

// Product Grid Fetch Engine
function fetchPosProducts(targetUrl = null) {
    const grid = document.getElementById('posProductGrid');
    const loader = document.getElementById('posGridLoader');
    const search = document.getElementById('posSearchProduct').value;
    const cat = document.getElementById('posCategoryFilter').value;
    const brand = document.getElementById('posBrandFilter').value;

    loader.classList.remove('d-none');
    loader.classList.add('d-flex');

    const url = targetUrl ? new URL(targetUrl) : new URL("{{ route('admin.pos.filter.products') }}", window.location.origin);
    url.searchParams.set('branch_id', BRANCH_ID);
    if (search) url.searchParams.set('search', search);
    if (cat) url.searchParams.set('category_id', cat);
    if (brand) url.searchParams.set('brand_id', brand);

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
    .then(res => res.text())
    .then(html => {
        grid.innerHTML = html;
        grid.scrollTop = 0;
    })
    .finally(() => {
        loader.classList.add('d-none');
        loader.classList.remove('d-flex');
    });
}

window.loadPosPage = function(url) {
    if (url) fetchPosProducts(url);
};

function onGridVariantChanged(productId, selectEl) {
    const opt = selectEl.options[selectEl.selectedIndex];
    if (!opt) return;

    const price = parseFloat(opt.getAttribute('data-price')) || 0;
    const stock = parseInt(opt.getAttribute('data-stock')) || 0;

    const priceEl = document.getElementById('gridPrice' + productId);
    if (priceEl && price > 0) {
        priceEl.innerText = price.toFixed(2) + ' ৳';
    }

    const stockEl = document.getElementById('gridStock' + productId);
    if (stockEl) {
        stockEl.innerText = stock;
        stockEl.className = 'fw-bold ' + (stock > 0 ? 'text-success' : 'text-danger');
    }

    const qtyInput = document.getElementById('gridQty' + productId);
    if (qtyInput) {
        qtyInput.max = stock > 0 ? stock : 9999;
        if (parseInt(qtyInput.value) > stock && stock > 0) {
            qtyInput.value = stock;
        }
    }
}

function addToPosMemo(id, title, price, stock, thumb) {
    const qtyInput = document.getElementById('gridQty' + id);
    const qtyToAdd = parseInt(qtyInput ? qtyInput.value : 1) || 1;

    let finalTitle = title;
    let finalPrice = parseFloat(price) || 0;
    let finalStock = parseInt(stock) || 0;
    let variantId = null;
    let itemCode = 'ID: #' + id;

    const selectEl = document.getElementById('gridVariantSelect' + id);
    if (selectEl) {
        if (!selectEl.value) {
            playBeep('error');
            selectEl.classList.add('border-danger', 'bg-warning-subtle');
            setTimeout(() => selectEl.classList.remove('border-danger', 'bg-warning-subtle'), 1500);
            selectEl.focus();
            showPosAlert('Please select size or color variant first!', 'Variant Required', 'warning');
            return;
        }

        const opt = selectEl.options[selectEl.selectedIndex];
        variantId = parseInt(selectEl.value);
        finalPrice = parseFloat(opt.getAttribute('data-price')) || finalPrice;
        finalStock = parseInt(opt.getAttribute('data-stock')) || 0;
        finalTitle = opt.getAttribute('data-title') || finalTitle;
        itemCode = opt.getAttribute('data-code') || ('VAR-#' + variantId);
    }

    const targetKeyId = variantId ? `var-${variantId}` : id;
    const existing = posCart.find(item => (item.variant_id ? `var-${item.variant_id}` : item.id) === targetKeyId);

    if (finalStock <= 0 || ((existing ? existing.qty : 0) + qtyToAdd) > finalStock) {
        playBeep('error');
        showPosAlert(`"${finalTitle}" stock limit exceeded! Available: ${finalStock}`, 'Stock Alert', 'warning');
        return;
    }

    if (existing) {
        existing.qty += qtyToAdd;
    } else {
        posCart.push({
            id: id,
            variant_id: variantId,
            title: finalTitle,
            price: finalPrice,
            stock: finalStock,
            qty: qtyToAdd,
            code: itemCode
        });
    }

    playBeep('success');
    renderCart();
    refocusBarcodeScanner();
}

// Cart Items Render & Mobile Sticky Dock Update
function renderCart() {
    const container = document.getElementById('cartItemsList');
    const countBadge = document.getElementById('cartCountBadge');
    const mobileTabCount = document.getElementById('mobileTabCartCount');
    const stickyDock = document.getElementById('posMobileStickyDock');
    const stickyItemCount = document.getElementById('stickyCartItemCount');
    const stickyGrandTotal = document.getElementById('stickyCartGrandTotal');

    const totalQty = posCart.reduce((sum, item) => sum + item.qty, 0);
    if (countBadge) countBadge.textContent = totalQty;
    if (mobileTabCount) mobileTabCount.textContent = totalQty;
    if (stickyItemCount) stickyItemCount.textContent = totalQty + (totalQty === 1 ? ' Item' : ' Items');

    if (posCart.length === 0) {
        container.innerHTML = '<div class="text-center py-4 text-muted" id="cartEmptyPlaceholder" style="font-size: 11px;">Cart is empty. Scan barcode or click items.</div>';
        if (stickyDock) stickyDock.style.display = 'none';
        renderCartSummary();
        return;
    }

    // Toggle sticky footer on mobile when in product tab
    if (stickyDock) {
        if (window.innerWidth < 992 && currentMobileTab === 'products') {
            stickyDock.style.display = 'block';
        } else {
            stickyDock.style.display = 'none';
        }
    }

    let html = '';
    posCart.forEach((item, index) => {
        const itemTotal = (item.price * item.qty).toFixed(0);
        html += `
            <div class="pos-item-row d-flex align-items-center justify-content-between">
                <div style="width: 54%; line-height: 1.2;">
                    <div class="fw-semibold text-dark text-truncate" title="${item.title}">${item.title}</div>
                    ${item.code ? `<small class="text-muted font-monospace bn-digit" style="font-size: 9px;">${item.code}</small><br>` : ''}
                    <span class="text-muted font-monospace bn-digit" style="font-size: 10px;">${item.price.toFixed(0)} × ${item.qty} = <strong class="text-dark">${itemTotal}৳</strong></span>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <button type="button" class="btn btn-outline-secondary qty-btn-mini d-flex align-items-center justify-content-center" onclick="changeQty(${index}, -1)">-</button>
                    <input type="number" class="form-control qty-input-mini font-monospace bn-digit" value="${item.qty}" min="1" onchange="updateQtyInput(${index}, this.value)">
                    <button type="button" class="btn btn-outline-secondary qty-btn-mini d-flex align-items-center justify-content-center" onclick="changeQty(${index}, 1)">+</button>
                    <button type="button" class="btn btn-link text-danger p-0 ms-1" style="font-size: 11px; text-decoration: none;" onclick="removeFromCart(${index})">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
    renderCartSummary();
}

function changeQty(index, change) {
    const item = posCart[index];
    if (change > 0 && item.stock > 0 && (item.qty + change) > item.stock) {
        playBeep('error');
        showPosAlert(`Stock limit reached! Available stock: ${item.stock}`, 'Stock Limit', 'warning');
        return;
    }

    item.qty += change;
    if (item.qty <= 0) posCart.splice(index, 1);
    renderCart();
    refocusBarcodeScanner();
}

function updateQtyInput(index, value) {
    const val = parseInt(value);
    const item = posCart[index];
    if (val > 0) {
        if (item.stock > 0 && val > item.stock) {
            playBeep('error');
            showPosAlert(`Stock limit reached! Available stock: ${item.stock}`, 'Stock Limit', 'warning');
            item.qty = item.stock;
        } else {
            item.qty = val;
        }
    } else {
        posCart.splice(index, 1);
    }
    renderCart();
    refocusBarcodeScanner();
}

function removeFromCart(index) {
    posCart.splice(index, 1);
    renderCart();
    refocusBarcodeScanner();
}

function clearPosCart() {
    posCart = [];
    renderCart();
    refocusBarcodeScanner();
}

// Summary Calculation
function renderCartSummary() {
    const subtotal = posCart.reduce((sum, item) => sum + (item.price * item.qty), 0);
    const shipping = parseFloat(document.getElementById('posShipping').value) || 0;
    const discount = parseFloat(document.getElementById('posDiscount').value) || 0;
    const grandTotal = Math.max(0, (subtotal + shipping) - discount);

    document.getElementById('grandTotalText').textContent = grandTotal.toFixed(2) + ' ৳';
    document.getElementById('posPaidAmount').value = grandTotal.toFixed(2);

    const stickyGrandTotal = document.getElementById('stickyCartGrandTotal');
    if (stickyGrandTotal) {
        stickyGrandTotal.textContent = grandTotal.toFixed(2) + ' ৳';
    }
}

// Customer Search & Creation
const custSearch = document.getElementById('customerSearchInput');
const custResults = document.getElementById('custSearchResults');
const custSpinner = document.getElementById('custSpinner');
let custDebounce;

custSearch.addEventListener('input', function () {
    const q = this.value.trim();
    clearTimeout(custDebounce);
    if (!q) {
        custResults.classList.add('d-none');
        return;
    }

    custSpinner.classList.remove('d-none');
    custDebounce = setTimeout(() => {
        fetch("{{ route('admin.pos.search.customers') }}?q=" + encodeURIComponent(q), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.length === 0) {
                custResults.innerHTML = '<div class="p-2 text-center text-muted" style="font-size: 10px;">No customer found</div>';
            } else {
                let html = '<div class="list-group list-group-flush">';
                data.forEach(c => {
                    html += `
                        <a href="javascript:void(0)" class="list-group-item list-group-item-action py-1 px-2 select-cust-item" style="font-size: 10.5px;" data-id="${c.id}" data-name="${c.name}" data-phone="${c.phone ?? ''}">
                            <strong>${c.name}</strong> <small class="text-muted font-monospace bn-digit">(${c.phone ?? 'N/A'})</small>
                        </a>
                    `;
                });
                html += '</div>';
                custResults.innerHTML = html;
            }
            custResults.classList.remove('d-none');
        })
        .finally(() => custSpinner.classList.add('d-none'));
    }, 250);
});

custResults.addEventListener('click', function (e) {
    const item = e.target.closest('.select-cust-item');
    if (!item) return;

    const id = item.getAttribute('data-id');
    const name = item.getAttribute('data-name');
    const phone = item.getAttribute('data-phone');

    const select = document.getElementById('posCustomerSelect');
    let opt = select.querySelector(`option[value="${id}"]`);
    if (!opt) {
        opt = new Option(`${name} (${phone})`, id, true, true);
        select.add(opt);
    }
    select.value = id;

    custSearch.value = name;
    custResults.classList.add('d-none');
    refocusBarcodeScanner();
});

document.addEventListener('click', function (e) {
    if (!custSearch.contains(e.target) && !custResults.contains(e.target)) {
        custResults.classList.add('d-none');
    }
});

function saveCustomerAjax() {
    const name = document.getElementById('custName').value.trim();
    const phone = document.getElementById('custPhone').value.trim();
    const email = document.getElementById('custEmail').value.trim();
    const address = document.getElementById('custAddress').value.trim();

    if (!name || !phone) {
        showPosAlert('Customer name and phone number are mandatory.', 'Validation Error', 'warning');
        return;
    }

    fetch("{{ route('admin.pos.customer.create') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF
        },
        body: JSON.stringify({ name, phone, email, address })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const select = document.getElementById('posCustomerSelect');
            const opt = new Option(`${data.customer.name} (${data.customer.phone})`, data.customer.id, true, true);
            select.add(opt);
            custSearch.value = data.customer.name;
            bootstrap.Modal.getInstance(document.getElementById('newCustomerModal')).hide();
            showPosAlert('Customer registered successfully.', 'Success', 'success');
            refocusBarcodeScanner();
        } else {
            showPosAlert(data.message || 'Customer creation failed.', 'Error', 'danger');
        }
    });
}

// Order Placement Checkout Flow
function submitPosOrder(actionType = 'only_order') {
    if (posCart.length === 0) {
        showPosAlert('Cart is empty! Please scan barcode or add products to memo first.', 'Empty Memo', 'warning');
        return;
    }

    const orderData = {
        branch_id: BRANCH_ID,
        customer_id: document.getElementById('posCustomerSelect').value || null,
        items: posCart,
        shipping_charge: parseFloat(document.getElementById('posShipping').value) || 0,
        discount: parseFloat(document.getElementById('posDiscount').value) || 0,
        payment_method: document.getElementById('posPayMethod').value,
        paid_amount: parseFloat(document.getElementById('posPaidAmount').value) || 0
    };

    fetch("{{ route('admin.pos.order.place') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF
        },
        body: JSON.stringify(orderData)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            clearPosCart();

            if (actionType === 'only_order') {
                showPosAlert(`Order placed successfully! Invoice: ${data.invoice_no}`, 'Order Confirmed', 'success');
                fetchPosProducts();
            } else if (actionType === 'order_a5_print') {
                window.open("{{ url('admin/pos/print-a5-memo') }}/" + data.order_id, '_blank');
                fetchPosProducts();
            } else if (actionType === 'pos_thermal_print') {
                window.open("{{ url('admin/pos/print-pos-receipt') }}/" + data.order_id, '_blank');
                fetchPosProducts();
            }
        } else {
            showPosAlert(data.message || 'Failed to place order.', 'Order Error', 'danger');
        }
    })
    .catch(err => {
        console.error(err);
        showPosAlert('Server connection error. Please try again.', 'Connection Error', 'danger');
    })
    .finally(() => {
        refocusBarcodeScanner();
    });
}

// Page Initialization
document.addEventListener('DOMContentLoaded', function () {
    fetchPosProducts();

    const searchInput = document.getElementById('posSearchProduct');
    const clearBtn = document.getElementById('clearProdSearchBtn');
    let debounce;

    searchInput.addEventListener('input', () => {
        clearBtn.classList.toggle('d-none', !searchInput.value.trim());
        clearTimeout(debounce);
        debounce = setTimeout(fetchPosProducts, 250);
    });

    clearBtn.addEventListener('click', () => {
        searchInput.value = '';
        clearBtn.classList.add('d-none');
        fetchPosProducts();
        refocusBarcodeScanner();
    });

    document.getElementById('posCategoryFilter').addEventListener('change', () => fetchPosProducts());
    document.getElementById('posBrandFilter').addEventListener('change', () => fetchPosProducts());
});
</script>
@endsection