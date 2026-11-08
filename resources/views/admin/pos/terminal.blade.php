@extends('layouts.admin')

@section('title', 'POS Terminal - ' . $branch->name)

@section('admin_content')
@php
    $taxSettings = $taxSettings ?? [
        'is_vat_enabled' => true,
        'enable_pos_vat' => true,
        'default_vat_rate' => 0,
        'default_calculation_type' => 'exclusive',
        'vat_label' => 'Govt. VAT',
        'pos_vat_display_mode' => 'itemized'
    ];
    $brands = $brands ?? (\Illuminate\Support\Facades\Schema::hasTable('brands') ? \App\Models\Brand::where('is_active', true)->orderBy('name')->get() : collect());
    $categories = $categories ?? (\Illuminate\Support\Facades\Schema::hasTable('categories') ? \App\Models\Category::where('is_active', true)->orderBy('name')->get() : collect());
    $customers = $customers ?? \App\Models\User::where('role', 'customer')->orWhere('user_type', 'customer')->take(50)->get();
    $paymentMethods = $paymentMethods ?? ['Cash', 'bKash', 'Nagad', 'Rocket', 'Card', 'Bank Transfer'];
    $selectableBranches = $selectableBranches ?? collect([$branch]);
    $salesPersons = $salesPersons ?? collect();
@endphp

<!-- Html5-QRCode Library for Camera Scanning -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<style>
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

    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

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

    /* Clean Pagination inside Grid */
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

    @media (min-width: 992px) {
        .pos-product-scroll-container {
            height: calc(100vh - 245px);
            align-content: flex-start;
        }
        .pos-memo-card {
            height: calc(100vh - 120px);
        }
        .pos-cart-items-scroll {
            max-height: calc(100vh - 460px);
        }
        .pos-mobile-nav-bar,
        .pos-mobile-sticky-dock {
            display: none !important;
        }
    }

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
    }

    .pos-mobile-sticky-dock {
        position: fixed;
        bottom: 8px;
        left: 8px;
        right: 8px;
        z-index: 1040;
    }
</style>

<div class="container-fluid py-2 px-2 px-md-3" id="posTerminalRoot">
    <!-- Top Branch Header Bar -->
    <div class="mb-2 bg-white p-2 px-3 shadow-xs border rounded-3">
        <div class="d-flex justify-content-between align-items-center gap-2">
            <!-- Left Side: Branch Info -->
            <div class="d-flex align-items-center gap-2 min-w-0 flex-wrap">
                <span class="badge bg-light text-primary border border-primary-subtle fw-bold px-2 py-1 text-truncate" style="font-size: 11px;">
                    <i class="fas fa-store me-1 text-primary"></i> {{ $branch->name }}
                </span>

                @if(isset($registerStatus))
                    @if(($registerStatus['session_status'] ?? '') === 'open')
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5" style="font-size: 9.5px; font-weight: 600;">
                            <i class="fas fa-cash-register me-1"></i> Open
                        </span>
                    @else
                        <a href="{{ route('admin.daily_closing.session', $branch->id) }}" class="badge bg-danger-subtle text-danger border border-danger-subtle text-decoration-none px-2 py-0.5" style="font-size: 9.5px; font-weight: 600;">
                            <i class="fas fa-exclamation-circle me-1"></i> Closed
                        </a>
                    @endif
                @endif
            </div>

            <!-- Right Side: Action Buttons -->
            <div class="d-flex align-items-center gap-1 flex-shrink-0">
                @if(isset($selectableBranches) && $selectableBranches->count() > 1)
                    <div class="dropdown d-inline-block">
                        <button class="btn btn-outline-primary btn-compact py-1 px-2 dropdown-toggle text-nowrap" type="button" data-bs-toggle="dropdown" style="font-size: 10.5px !important; height: 28px;">
                            <i class="fas fa-store-alt me-1"></i> <span class="d-none d-sm-inline">Switch Hub</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="font-size: 11px;">
                            @foreach($selectableBranches as $sb)
                                <li>
                                    <a class="dropdown-item py-1.5 {{ $sb->id == $branch->id ? 'active fw-bold' : '' }}" href="{{ route('admin.pos.terminal', $sb->id) }}">
                                        {{ $sb->name }} ({{ $sb->code ?? $sb->branch_code }})
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <a href="{{ route('admin.pos.index') }}" class="btn btn-outline-secondary btn-compact py-1 px-2 text-nowrap" style="font-size: 10.5px !important; height: 28px; display: inline-flex; align-items: center;">
                    <i class="fas fa-exchange-alt me-1"></i> <span class="d-none d-sm-inline">Change</span> Branch
                </a>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation Bar -->
    <div class="pos-mobile-nav-bar d-lg-none">
        <div class="pos-mobile-nav-btn active" id="tabBtnProducts" onclick="switchMobilePosTab('products')">
            <i class="fas fa-boxes-stacked me-1"></i> Products &amp; Scanner
        </div>
        <div class="pos-mobile-nav-btn" id="tabBtnMemo" onclick="switchMobilePosTab('memo')">
            <i class="fas fa-receipt me-1"></i> Cart Memo (<span id="mobileTabCartCount" class="bn-digit">0</span>)
        </div>
    </div>

    <div class="row g-2 align-items-start">
        <!-- Left Column: Barcode Scanner, Filters & Product Grid -->
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

                    <button class="btn btn-info text-white fw-bold px-2" type="button" onclick="openCameraScannerModal()" title="Camera Scan">
                        <i class="fas fa-camera"></i>
                    </button>
                </div>
            </div>

            <!-- 2. Live AJAX Search & Filter Bar (Product Name, ID, SKU, Barcode, Brand & Category) -->
            <div class="card border-0 shadow-xs p-2 bg-white mb-2">
                <div class="row g-1.5">
                    <div class="col-12 col-md-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0 py-1" style="height: 31px;"><i class="fas fa-search text-muted" style="font-size: 10px;"></i></span>
                            <input type="text" id="posSearchProduct" class="form-control border-start-0 py-1" placeholder="Search title, SKU, barcode, ID..." style="height: 31px; font-size: 11.5px;" autocomplete="off">
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

        <!-- Right Column: POS Memo & Billing Control -->
        <div class="col-12 col-lg-4 pos-memo-panel pos-view-column" id="posMemoColumn">
            <div class="card border shadow-xs bg-white p-2 d-flex flex-column justify-content-between pos-memo-card">
                <div>
                    <!-- Customer & Sales Person Section -->
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

                        <select id="posCustomerSelect" class="form-select form-select-xs mb-1.5">
                            <option value="">-- Walk-in Customer --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" data-name="{{ $c->name }}" data-phone="{{ $c->phone }}">{{ $c->name }} ({{ $c->phone ?? 'N/A' }})</option>
                            @endforeach
                        </select>

                        <!-- Sales Person (Locked to Authenticated User) -->
                        <div class="d-flex align-items-center gap-1 mt-1">
                            <label class="text-muted text-nowrap" style="font-size: 9.5px; width: 68px;">Sales Person:</label>
                            <select id="posSalesPersonSelect" class="form-select form-select-xs" style="pointer-events: none; background-color: #f8fafc;">
                                <option value="{{ Auth::id() }}" selected>
                                    {{ Auth::user()->name }} ({{ Auth::user()->phone ?? 'Cashier' }})
                                </option>
                            </select>
                        </div>
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
                    <div class="d-flex justify-content-between align-items-center mb-1 text-muted" style="font-size: 10.5px;">
                        <span>Subtotal:</span>
                        <span class="fw-semibold text-dark font-monospace bn-digit" id="posSubtotalText">0.00 ৳</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-1 text-muted" id="posVatRow" style="font-size: 10.5px; display: none;">
                        <span>
                            <span id="posVatLabelText">{{ $taxSettings['vat_label'] ?? 'VAT' }}</span>:
                        </span>
                        <span class="fw-semibold text-primary font-monospace bn-digit" id="posVatText">0.00 ৳</span>
                    </div>

                    <div class="row g-1 mb-1">
                        <div class="col-6">
                            <span class="text-muted d-block" style="font-size: 10px;">Shipping (৳)</span>
                            <input type="number" id="posShipping" class="form-control form-control-xs font-monospace bn-digit" value="0" min="0" step="any" oninput="renderCartSummary()">
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block" style="font-size: 10px;">Discount (৳)</span>
                            <input type="number" id="posDiscount" class="form-control form-control-xs font-monospace bn-digit" value="0" min="0" step="any" oninput="renderCartSummary()">
                        </div>
                    </div>

                    <div class="row g-1 mb-2">
                        <div class="col-6">
                            <span class="text-muted d-block" style="font-size: 10px;">Payment Method</span>
                            <select id="posPayMethod" class="form-select form-select-xs">
                                @foreach($paymentMethods as $pm)
                                    <option value="{{ strtolower(str_replace(' ', '_', $pm)) }}">{{ $pm }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block" style="font-size: 10px;">Paid Amount</span>
                            <input type="number" id="posPaidAmount" class="form-control form-control-xs fw-bold text-success font-monospace bn-digit" value="0" min="0" step="any" oninput="handleManualPaidInput()">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                        <span class="fw-bold text-muted">Total Payable:</span>
                        <h6 class="fw-bold text-dark mb-0 fs-6 font-monospace bn-digit" id="grandTotalText">0.00 ৳</h6>
                    </div>

                    <!-- Action Control Row: Order (Save), Paper Size Selector, Print -->
                    <div class="row g-1">
                        <div class="col-4">
                            <button type="button" class="btn btn-outline-dark w-100 pos-action-btn" onclick="submitPosOrder('only_order')">
                                <i class="fas fa-save me-1"></i>Order
                            </button>
                        </div>
                        <div class="col-4">
                            <select id="posPaperSizeSelector" class="form-select form-select-xs w-100 pos-action-btn fw-bold text-dark border-primary" style="height: 31px !important; font-size: 10.5px !important; padding: 2px 2px !important; cursor: pointer;" onchange="handlePaperSizeChange(this.value)" title="Choose Default Paper Size for this Counter">
                                <option value="58mm">2" (58mm)</option>
                                <option value="80mm">3" (80mm)</option>
                                <option value="a5">A5 Memo</option>
                                <option value="a4">A4 Invoice</option>
                            </select>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-warning w-100 pos-action-btn text-white" onclick="submitPosOrder('print')">
                                <i class="fas fa-print me-1"></i>Print
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Sticky Quick Checkout Dock -->
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

<!-- Modal 2: Notice Alert Modal -->
<div class="modal fade pos-modal-scope" id="posAlertModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div id="posAlertIcon" class="mb-2"></div>
            <h6 class="fw-bold text-dark mb-1" id="posAlertTitle">Notice</h6>
            <p class="text-muted small mb-3" id="posAlertMessage" style="font-size: 11.5px; line-height: 1.4; word-break: break-word;"></p>
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
            <p class="small text-muted mb-2">This product has multiple variants. Select color and size:</p>
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
const TAX_SETTINGS = @json($taxSettings);

// Persistent Counter-Wise Paper Preference
const STORAGE_KEY_PAPER_SIZE = 'pos_counter_paper_size';

let posCart = [];
let isScanning = false;
let cameraScannerInstance = null;
let currentMobileTab = 'products';
let currentCalculatedGrandTotal = 0.00;
let currentCalculatedTax = 0.00;
let isPaidAmountManuallyEdited = false;
let productSearchAbortController = null;

// Paper Size Preference Storage
function getSavedPaperSize() {
    return localStorage.getItem(STORAGE_KEY_PAPER_SIZE) || '80mm';
}

function handlePaperSizeChange(val) {
    localStorage.setItem(STORAGE_KEY_PAPER_SIZE, val);
}

// Pure Web Native Hidden iFrame Printing (Zero External App Dependency, Exact Memo Length)
function executeSilentPrint(url) {
    let frame = document.getElementById('posHiddenPrintFrame');
    if (!frame) {
        frame = document.createElement('iframe');
        frame.id = 'posHiddenPrintFrame';
        frame.style.position = 'fixed';
        frame.style.right = '0';
        frame.style.bottom = '0';
        frame.style.width = '0';
        frame.style.height = '0';
        frame.style.border = '0';
        frame.style.visibility = 'hidden';
        document.body.appendChild(frame);
    }

    frame.src = url;
    frame.onload = function() {
        setTimeout(() => {
            try {
                frame.contentWindow.focus();
                frame.contentWindow.print();
            } catch(e) {
                window.open(url, '_blank');
            }
        }, 300);
    };
}

// Audio synth for cashier beep
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
    document.getElementById('posAlertMessage').innerHTML = message;
    
    const alertModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('posAlertModal'));
    alertModal.show();
}

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

        const ignoreIds = ['customerSearchInput', 'posSearchProduct', 'posShipping', 'posDiscount', 'posPaidAmount', 'custName', 'custPhone', 'custEmail', 'custAddress', 'posSalesPersonSelect', 'posPaperSizeSelector'];
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

// Barcode Lookup & Cart Addition Engine (With Variable Product Check)
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
        if (data.matched === 'multiple' || data.is_variable) {
            playBeep('error');
            if (data.variants && data.variants.length > 0) {
                handleMultipleBarcodeMatches(data.variants);
            } else {
                showPosAlert(data.message || 'Variable product found. Please select color and size from the card.', 'Select Variant', 'warning');
            }
            return;
        }

        if (data.success && (data.product || data.product_id)) {
            playBeep('success');
            const prodPayload = data.product || {
                id: data.product_id,
                variant_id: data.variant_id,
                title: data.title,
                price: data.unit_price || data.price,
                stock: data.available_stock ?? data.stock,
                product_code: data.sku || data.product_code,
                sku: data.sku || data.product_code
            };
            addScannedProductToCart(prodPayload);
            if (scannerInput) scannerInput.value = '';
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

    if (window.innerWidth < 992) {
        switchMobilePosTab('products');
    }

    // Initialize Paper Size Selector from localStorage
    const paperSelector = document.getElementById('posPaperSizeSelector');
    if (paperSelector) {
        paperSelector.value = getSavedPaperSize();
    }

    refocusBarcodeScanner();
});

function addScannedProductToCart(p) {
    const targetKeyId = p.variant_id ? `var-${p.variant_id}` : p.id;
    const existing = posCart.find(item => (item.variant_id ? `var-${item.variant_id}` : item.id) === targetKeyId);

    const currentQtyInCart = existing ? existing.qty : 0;
    const itemStock = parseInt(p.stock || p.available_stock) || 0;
    if (itemStock > 0 && (currentQtyInCart + 1) > itemStock) {
        playBeep('error');
        showPosAlert(`"${p.title}" stock limit exceeded! Current stock: ${itemStock}`, 'Stock Alert', 'warning');
        return;
    }

    let pTaxRate = 0;
    let pIsInclusive = (TAX_SETTINGS.default_calculation_type === 'inclusive');
    let pTaxType = p.tax_type || 'percent';
    let pTaxFixed = parseFloat(p.tax_fixed_amount) || 0;

    const isVatGloballyActive = Boolean(TAX_SETTINGS.is_vat_enabled && TAX_SETTINGS.enable_pos_vat);

    if (isVatGloballyActive) {
        if (TAX_SETTINGS.branch_tax_mode === 'exempt' || TAX_SETTINGS.branch_tax_mode === 'zero_rated') {
            pTaxRate = 0;
            pTaxFixed = 0;
        } else if (p.tax_rate !== undefined && p.tax_rate !== null && p.tax_rate !== '') {
            pTaxRate = parseFloat(p.tax_rate);
            pIsInclusive = p.is_tax_inclusive !== undefined ? Boolean(p.is_tax_inclusive) : (TAX_SETTINGS.default_calculation_type === 'inclusive');
        } else if (pTaxFixed > 0) {
            pTaxRate = 0;
        } else {
            pTaxRate = parseFloat(TAX_SETTINGS.default_vat_rate) || 0;
            pIsInclusive = (TAX_SETTINGS.default_calculation_type === 'inclusive');
        }
    } else {
        pTaxRate = 0;
        pTaxFixed = 0;
    }

    const skuVal = p.sku || p.product_code || p.barcode || ('PRD-' + p.id);

    if (existing) {
        existing.qty += 1;
    } else {
        posCart.push({
            id: p.id,
            variant_id: p.variant_id || null,
            title: p.title,
            price: parseFloat(p.price || p.unit_price) || 0,
            stock: itemStock,
            qty: 1,
            code: skuVal,
            sku: skuVal,
            tax_rate: pTaxRate,
            is_tax_inclusive: pIsInclusive,
            tax_type: pTaxType,
            tax_fixed_amount: pTaxFixed
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
        const price = it.variant_sale_price || it.sale_price || it.regular_price || it.price || 0;
        const stock = it.branch_stock ?? it.available_stock ?? it.stock ?? 0;
        const code = it.variant_sku || it.sku || it.product_code || '';

        const a = document.createElement('a');
        a.href = 'javascript:void(0)';
        a.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 px-3';
        a.innerHTML = `
            <div>
                <strong class="text-dark d-block">${title} ${details ? '('+details+')' : ''}</strong>
                <small class="text-muted font-monospace bn-digit">SKU: ${code} | Stock: ${stock}</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="fw-bold text-primary font-monospace bn-digit">${Number(price).toFixed(0)}৳</span>
                <button type="button" class="btn btn-primary btn-compact py-0 px-2 fw-semibold">Add</button>
            </div>
        `;

        a.onclick = function () {
            addScannedProductToCart({
                id: it.product_id || it.id,
                variant_id: it.variant_id || (it.id && it.color ? it.id : null),
                title: title + (details ? ' ('+details+')' : ''),
                price: price,
                stock: stock,
                sku: code,
                product_code: code,
                tax_rate: it.tax_rate !== undefined ? it.tax_rate : null,
                is_tax_inclusive: it.is_tax_inclusive || false,
                tax_type: it.tax_type || 'percent',
                tax_fixed_amount: parseFloat(it.tax_fixed_amount) || 0
            });
            bootstrap.Modal.getInstance(document.getElementById('multipleMatchesModal')).hide();
            refocusBarcodeScanner();
        };

        list.appendChild(a);
    });

    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('multipleMatchesModal'));
    modal.show();
}

// Camera Scanner Engine
function openCameraScannerModal() {
    const modalEl = document.getElementById('cameraScanModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();

    if (typeof Html5Qrcode !== 'undefined') {
        setTimeout(() => {
            startCameraScanner();
        }, 300);
    } else {
        showPosAlert('Camera scanner library failed to load.', 'Camera Unavailable', 'warning');
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
        showPosAlert("Could not start camera. Check permissions.", "Camera Error", "warning");
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

// Product Grid Fetch Engine (High Speed with Request Abort Controller)
function fetchPosProducts(targetUrl = null) {
    const grid = document.getElementById('posProductGrid');
    const loader = document.getElementById('posGridLoader');
    const searchVal = (document.getElementById('posSearchProduct')?.value || '').trim();
    const catVal = document.getElementById('posCategoryFilter')?.value || '';
    const brandVal = document.getElementById('posBrandFilter')?.value || '';

    loader.classList.remove('d-none');
    loader.classList.add('d-flex');

    if (productSearchAbortController) {
        productSearchAbortController.abort();
    }
    productSearchAbortController = new AbortController();

    const baseUrl = (typeof targetUrl === 'string' && targetUrl.startsWith('http')) 
        ? targetUrl 
        : "{{ route('admin.pos.filter.products') }}";

    const url = new URL(baseUrl, window.location.origin);
    url.searchParams.set('branch_id', BRANCH_ID);

    if (searchVal) {
        url.searchParams.set('search', searchVal);
        url.searchParams.set('q', searchVal);
    } else {
        url.searchParams.delete('search');
        url.searchParams.delete('q');
    }

    if (catVal) {
        url.searchParams.set('category_id', catVal);
    } else {
        url.searchParams.delete('category_id');
    }

    if (brandVal) {
        url.searchParams.set('brand_id', brandVal);
    } else {
        url.searchParams.delete('brand_id');
    }

    fetch(url, { 
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
        signal: productSearchAbortController.signal
    })
    .then(res => res.text())
    .then(html => {
        grid.innerHTML = html;
        grid.scrollTop = 0;
    })
    .catch(err => {
        if (err.name !== 'AbortError') {
            console.error('POS Grid Fetch Error:', err);
        }
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

// Add Item to Memo from Grid Card
function addToPosMemo(id, title, price, stock, thumb, explicitTaxRate = null, explicitIsInclusive = null, explicitTaxType = null, explicitTaxFixed = null) {
    const qtyInput = document.getElementById('gridQty' + id);
    const qtyToAdd = parseInt(qtyInput ? qtyInput.value : 1) || 1;

    let finalTitle = title;
    let finalPrice = parseFloat(price) || 0;
    let finalStock = parseInt(stock) || 0;
    let variantId = null;
    let itemCode = '';

    let itemTaxRate = (explicitTaxRate !== null && explicitTaxRate !== undefined && explicitTaxRate !== '') ? parseFloat(explicitTaxRate) : null;
    let itemIsTaxInclusive = explicitIsInclusive !== null ? Boolean(explicitIsInclusive) : (TAX_SETTINGS.default_calculation_type === 'inclusive');
    let itemTaxType = explicitTaxType || 'percent';
    let itemTaxFixed = explicitTaxFixed !== null ? parseFloat(explicitTaxFixed) : 0;

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

        if (opt.hasAttribute('data-tax-rate') && opt.getAttribute('data-tax-rate') !== '') {
            itemTaxRate = parseFloat(opt.getAttribute('data-tax-rate'));
        }
        if (opt.hasAttribute('data-is-inclusive')) {
            itemIsTaxInclusive = (opt.getAttribute('data-is-inclusive') === '1' || opt.getAttribute('data-is-inclusive') === 'true');
        }
        if (opt.hasAttribute('data-tax-type')) {
            itemTaxType = opt.getAttribute('data-tax-type');
        }
        if (opt.hasAttribute('data-tax-fixed')) {
            itemTaxFixed = parseFloat(opt.getAttribute('data-tax-fixed')) || 0;
        }
    } else {
        const cardEl = document.querySelector(`[data-product-id="${id}"]`) || (qtyInput ? qtyInput.closest('.card, [data-product-id]') : null);
        if (cardEl) {
            itemCode = cardEl.getAttribute('data-sku') || '';
            if (itemTaxRate === null && cardEl.hasAttribute('data-tax-rate') && cardEl.getAttribute('data-tax-rate') !== '') {
                itemTaxRate = parseFloat(cardEl.getAttribute('data-tax-rate'));
            }
            if (cardEl.hasAttribute('data-is-inclusive')) {
                itemIsTaxInclusive = (cardEl.getAttribute('data-is-inclusive') === '1' || cardEl.getAttribute('data-is-inclusive') === 'true');
            }
            if (cardEl.hasAttribute('data-tax-type')) {
                itemTaxType = cardEl.getAttribute('data-tax-type');
            }
            if (cardEl.hasAttribute('data-tax-fixed')) {
                itemTaxFixed = parseFloat(cardEl.getAttribute('data-tax-fixed')) || 0;
            }
        }
    }

    if (!itemCode) {
        itemCode = 'PRD-' + id;
    }

    const isVatGloballyActive = Boolean(TAX_SETTINGS.is_vat_enabled && TAX_SETTINGS.enable_pos_vat);

    if (isVatGloballyActive) {
        if (TAX_SETTINGS.branch_tax_mode === 'exempt' || TAX_SETTINGS.branch_tax_mode === 'zero_rated') {
            itemTaxRate = 0;
            itemTaxFixed = 0;
        } else if (itemTaxRate !== null && !isNaN(itemTaxRate)) {
            itemTaxRate = itemTaxRate;
        } else if (itemTaxFixed > 0) {
            itemTaxRate = 0;
        } else {
            itemTaxRate = parseFloat(TAX_SETTINGS.default_vat_rate) || 0;
            itemIsTaxInclusive = (TAX_SETTINGS.default_calculation_type === 'inclusive');
        }
    } else {
        itemTaxRate = 0;
        itemTaxFixed = 0;
    }

    const targetKeyId = variantId ? `var-${variantId}` : id;
    const existing = posCart.find(item => (item.variant_id ? `var-${item.variant_id}` : item.id) === targetKeyId);

    if (finalStock > 0 && ((existing ? existing.qty : 0) + qtyToAdd) > finalStock) {
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
            code: itemCode,
            sku: itemCode,
            tax_rate: itemTaxRate,
            is_tax_inclusive: itemIsTaxInclusive,
            tax_type: itemTaxType,
            tax_fixed_amount: itemTaxFixed
        });
    }

    playBeep('success');
    renderCart();
    refocusBarcodeScanner();
}

function handleManualPaidInput() {
    isPaidAmountManuallyEdited = true;
}

// Cart Items Render (Strictly Shows SKU, Eliminates ID & VAT)
function renderCart() {
    const container = document.getElementById('cartItemsList');
    const countBadge = document.getElementById('cartCountBadge');
    const mobileTabCount = document.getElementById('mobileTabCartCount');
    const stickyDock = document.getElementById('posMobileStickyDock');
    const stickyItemCount = document.getElementById('stickyCartItemCount');

    const totalQty = posCart.reduce((sum, item) => sum + item.qty, 0);
    if (countBadge) countBadge.textContent = totalQty;
    if (mobileTabCount) mobileTabCount.textContent = totalQty;
    if (stickyItemCount) stickyItemCount.textContent = totalQty + (totalQty === 1 ? ' Item' : ' Items');

    if (posCart.length === 0) {
        container.innerHTML = '<div class="text-center py-4 text-muted" id="cartEmptyPlaceholder" style="font-size: 11px;">Cart is empty. Scan barcode or click items.</div>';
        if (stickyDock) stickyDock.style.display = 'none';
        isPaidAmountManuallyEdited = false;
        renderCartSummary();
        return;
    }

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
        const skuDisplay = item.sku || item.code || ('PRD-' + item.id);

        html += `
            <div class="pos-item-row d-flex align-items-center justify-content-between">
                <div style="width: 54%; line-height: 1.2;">
                    <div class="fw-semibold text-dark text-truncate" title="${item.title}">${item.title}</div>
                    <small class="text-muted font-monospace bn-digit" style="font-size: 9.5px;">SKU: ${skuDisplay}</small><br>
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
    isPaidAmountManuallyEdited = false;
    renderCart();
    refocusBarcodeScanner();
}

function renderCartSummary() {
    const subtotal = posCart.reduce((sum, item) => sum + (item.price * item.qty), 0);
    const shipping = parseFloat(document.getElementById('posShipping').value) || 0;
    const discountInput = parseFloat(document.getElementById('posDiscount').value) || 0;
    const discount = Math.min(subtotal, Math.max(0, discountInput));

    const discountRatio = (subtotal > 0 && discount > 0) ? Math.min(1.0, discount / subtotal) : 0.0;

    let calculatedTax = 0;
    let exclusiveTax = 0;
    const isVatActive = Boolean(TAX_SETTINGS.is_vat_enabled && TAX_SETTINGS.enable_pos_vat);

    if (isVatActive) {
        posCart.forEach(item => {
            const lineTotal = (item.price * item.qty);
            const lineDiscount = lineTotal * discountRatio;
            const netLineBase = Math.max(0.0, lineTotal - lineDiscount);

            const rate = (item.tax_rate !== undefined && item.tax_rate !== null) ? parseFloat(item.tax_rate) : 0;
            const isInclusive = Boolean(item.is_tax_inclusive);

            if (item.tax_type === 'flat' && parseFloat(item.tax_fixed_amount) > 0) {
                const lineTax = parseFloat(item.tax_fixed_amount) * item.qty;
                calculatedTax += lineTax;
                exclusiveTax += lineTax;
            } else if (rate > 0) {
                if (isInclusive) {
                    const lineTax = (netLineBase - (netLineBase / (1 + (rate / 100))));
                    calculatedTax += lineTax;
                } else {
                    const lineTax = ((netLineBase * rate) / 100);
                    calculatedTax += lineTax;
                    exclusiveTax += lineTax;
                }
            }
        });
    }

    calculatedTax = Math.round(calculatedTax * 100) / 100;
    exclusiveTax  = Math.round(exclusiveTax * 100) / 100;
    currentCalculatedTax = calculatedTax;

    const grandTotal = Math.max(0, Math.round(((subtotal - discount) + exclusiveTax + shipping) * 100) / 100);
    currentCalculatedGrandTotal = grandTotal;

    const subtotalEl = document.getElementById('posSubtotalText');
    if (subtotalEl) {
        subtotalEl.textContent = subtotal.toFixed(2) + ' ৳';
    }

    const vatEl = document.getElementById('posVatText');
    const vatRow = document.getElementById('posVatRow');
    if (vatEl) {
        vatEl.textContent = calculatedTax.toFixed(2) + ' ৳';
    }

    if (vatRow) {
        if (isVatActive && calculatedTax > 0 && TAX_SETTINGS.pos_vat_display_mode !== 'hidden') {
            vatRow.style.display = 'flex';
        } else {
            vatRow.style.display = 'none';
        }
    }

    document.getElementById('grandTotalText').textContent = grandTotal.toFixed(2) + ' ৳';
    
    const paidInput = document.getElementById('posPaidAmount');
    if (paidInput && !isPaidAmountManuallyEdited) {
        paidInput.value = grandTotal.toFixed(2);
    }

    const stickyGrandTotal = document.getElementById('stickyCartGrandTotal');
    if (stickyGrandTotal) {
        stickyGrandTotal.textContent = grandTotal.toFixed(2) + ' ৳';
    }
}

// Customer Live Search
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
            const listData = Array.isArray(data) ? data : (data.customers || []);
            if (listData.length === 0) {
                custResults.innerHTML = '<div class="p-2 text-center text-muted" style="font-size: 10px;">No customer found</div>';
            } else {
                let html = '<div class="list-group list-group-flush">';
                listData.forEach(c => {
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
    .then(res => {
        return res.json().then(data => ({ ok: res.ok, data }));
    })
    .then(({ ok, data }) => {
        if (ok && data.success) {
            const select = document.getElementById('posCustomerSelect');
            const cust = data.customer || data;
            const opt = new Option(`${cust.name} (${cust.phone})`, cust.id, true, true);
            select.add(opt);
            custSearch.value = cust.name;
            bootstrap.Modal.getInstance(document.getElementById('newCustomerModal')).hide();
            showPosAlert('Customer registered successfully.', 'Success', 'success');
            refocusBarcodeScanner();
        } else {
            let errorMsg = data.message || 'Customer creation failed.';
            if (data.errors) {
                errorMsg = Object.values(data.errors).flat().join('<br>');
            }
            showPosAlert(errorMsg, 'Registration Error', 'danger');
        }
    })
    .catch(err => {
        showPosAlert('Server connection error. Please try again.', 'Error', 'danger');
    });
}

// Order Checkout Submission with Dynamic Paper Format Resolution
function submitPosOrder(actionType = 'only_order') {
    if (posCart.length === 0) {
        showPosAlert('Cart is empty! Please scan barcode or add products to memo first.', 'Empty Memo', 'warning');
        return;
    }

    const shippingCharge = parseFloat(document.getElementById('posShipping').value) || 0;
    const discountAmount = parseFloat(document.getElementById('posDiscount').value) || 0;
    const paidAmountInput = document.getElementById('posPaidAmount');
    const rawPaidVal = parseFloat(paidAmountInput.value);
    
    const finalPaid = isNaN(rawPaidVal) ? currentCalculatedGrandTotal : rawPaidVal;
    const salesPersonEl = document.getElementById('posSalesPersonSelect');

    const orderData = {
        branch_id: BRANCH_ID,
        customer_id: document.getElementById('posCustomerSelect').value || null,
        sales_person_id: salesPersonEl ? (salesPersonEl.value || null) : null,
        items: posCart.map(item => ({
            id: item.id,
            product_id: item.id,
            variant_id: item.variant_id,
            sku: item.sku || item.code,
            qty: item.qty,
            price: item.price,
            unit_price: item.price,
            tax_rate: item.tax_rate,
            is_tax_inclusive: item.is_tax_inclusive,
            tax_type: item.tax_type,
            tax_fixed_amount: item.tax_fixed_amount
        })),
        shipping_charge: shippingCharge,
        delivery_charge: shippingCharge,
        discount: discountAmount,
        discount_amount: discountAmount,
        tax_amount: currentCalculatedTax,
        payment_method: document.getElementById('posPayMethod').value,
        paid_amount: finalPaid
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
            playBeep('success');

            if (actionType === 'only_order') {
                const taxMsg = data.tax_amount ? ` (VAT: ৳${Number(data.tax_amount).toFixed(2)})` : '';
                showPosAlert(`Order placed successfully! Invoice: ${data.invoice_no}${taxMsg}`, 'Order Confirmed', 'success');
                fetchPosProducts();
            } else {
                // Determine user's selected paper format
                const selectedFormat = (document.getElementById('posPaperSizeSelector')?.value || getSavedPaperSize()).toLowerCase();
                let targetPrintUrl = '';

                if (selectedFormat === '58mm' || selectedFormat === '2inch') {
                    targetPrintUrl = data.print_url_58 || ("{{ url('admin/pos/print-pos-receipt-58mm') }}/" + data.order_id);
                } else if (selectedFormat === '80mm' || selectedFormat === '3inch') {
                    targetPrintUrl = data.print_url_80 || data.print_url || ("{{ url('admin/pos/print-pos-receipt') }}/" + data.order_id);
                } else if (selectedFormat === 'a5') {
                    targetPrintUrl = data.print_url_a5 || data.memo_url || ("{{ url('admin/pos/print-a5-memo') }}/" + data.order_id);
                } else if (selectedFormat === 'a4') {
                    targetPrintUrl = data.print_url_a4 || ("{{ url('admin/pos/print-a4-memo') }}/" + data.order_id);
                } else {
                    targetPrintUrl = data.print_url || ("{{ url('admin/pos/print-pos-receipt') }}/" + data.order_id);
                }

                // মোডাল ছাড়া সাথে সাথে ব্যাকগ্রাউন্ডে প্রিন্ট এক্সিকিউট
                executeSilentPrint(targetPrintUrl);
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

// Auto Live Search & Event Handlers
document.addEventListener('DOMContentLoaded', function () {
    fetchPosProducts();

    const searchInput = document.getElementById('posSearchProduct');
    const clearBtn = document.getElementById('clearProdSearchBtn');
    let debounceTimer;

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            if (clearBtn) clearBtn.classList.toggle('d-none', !this.value.trim());
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                fetchPosProducts();
            }, 160);
        });

        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(debounceTimer);
                fetchPosProducts();
            }
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            searchInput.value = '';
            clearBtn.classList.add('d-none');
            fetchPosProducts();
            refocusBarcodeScanner();
        });
    }

    const catFilter = document.getElementById('posCategoryFilter');
    if (catFilter) {
        catFilter.addEventListener('change', () => fetchPosProducts());
    }

    const brandFilter = document.getElementById('posBrandFilter');
    if (brandFilter) {
        brandFilter.addEventListener('change', () => fetchPosProducts());
    }
});
</script>
@endsection