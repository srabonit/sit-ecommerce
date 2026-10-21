@extends('layouts.admin')

@section('title', 'Print Barcode - ' . config('app.name'))

@section('admin_content')
<!-- JsBarcode Engine -->
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

<style>
    /* Strict 5px Element Radius Across All Controls */
    #barcode-print-root .btn,
    #barcode-print-root .btn-sm,
    #barcode-print-root button,
    #barcode-print-root .form-control,
    #barcode-print-root .form-select,
    #barcode-print-root .badge,
    #barcode-print-root .input-group-text,
    #barcode-print-root .card,
    #barcode-print-root .barcode-panel-card,
    #barcode-print-root .ajax-search-results,
    #barcode-print-root .barcode-sticker,
    #barcode-print-root .source-tab-btn,
    #barcode-print-root .step-pill,
    .barcode-modal-scope .modal-content,
    .barcode-modal-scope .btn,
    .barcode-modal-scope .form-control {
        border-radius: 5px !important;
    }

    #barcode-print-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #barcode-print-root .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Safe Monospace & Bengali Digit Typography */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Enterprise Surface Cards */
    .barcode-panel-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        padding: 16px;
        margin-bottom: 16px;
    }

    /* Compact Button Styling */
    .btn-compact {
        font-size: 11.5px !important;
        padding: 6px 12px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    /* Source Switcher Segmented Control */
    .source-tab-btn {
        padding: 6px 14px;
        font-size: 11.5px;
        font-weight: 700;
        cursor: pointer;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        color: #475569;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-check:checked + .source-tab-btn {
        background-color: #2563eb !important;
        color: #ffffff !important;
        border-color: #2563eb !important;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);
    }

    /* Step Heading Pill */
    .step-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        background: #2563eb;
        color: #ffffff;
        font-size: 11px;
        font-weight: 800;
        flex-shrink: 0;
    }

    /* Table Styling */
    .custom-table th {
        background-color: #f8fafc;
        color: #475569;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 10px 12px;
        white-space: nowrap;
    }
    .custom-table td {
        padding: 9px 12px;
        vertical-align: middle;
        font-size: 12px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }

    /* Live Search Dropdown */
    .ajax-search-wrapper {
        position: relative;
    }
    .ajax-search-results {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        z-index: 1055;
        max-height: 280px;
        overflow-y: auto;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.12);
        display: none;
    }
    .ajax-search-item {
        padding: 9px 12px;
        cursor: pointer;
        border-bottom: 1px solid #f1f5f9;
        transition: background-color 0.15s ease;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .ajax-search-item:last-child {
        border-bottom: none;
    }
    .ajax-search-item:hover {
        background-color: #f8fafc;
    }

    /* Thermal Barcode Sticker Canvas */
    .barcode-sticker {
        border: 1px dashed #cbd5e1;
        padding: 6px 4px;
        background: #ffffff;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        box-sizing: border-box;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        transition: transform 0.15s ease, border-color 0.15s ease;
        width: 100%;
        max-width: 100%;
    }
    .barcode-sticker:hover {
        border-color: #2563eb;
        transform: translateY(-2px);
    }
    .barcode-sticker .st-title {
        font-size: 10.5px;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.2;
        max-width: 95%;
        margin-bottom: 2px;
        white-space: normal;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .barcode-sticker .st-price {
        font-size: 11px;
        font-weight: 700;
        color: #0f172a;
        margin-top: 1px;
    }
    .barcode-sticker svg {
        width: 100%;
        max-height: 42px;
    }

    /* Fixed Height Controls for Presets */
    .size-44-34 { min-height: 32mm; max-height: 34mm; }
    .size-50-25 { min-height: 24mm; max-height: 26mm; }
    .size-38-25 { min-height: 24mm; max-height: 26mm; }

    /* Fluid Column Grid Layouts */
    .grid-1-col {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
        width: 100%;
    }
    .grid-1-col .barcode-sticker {
        max-width: 60mm;
    }
    .grid-4-col {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        width: 100%;
    }
    .grid-5-col {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 8px;
        width: 100%;
    }

    /* Form Controls */
    #barcode-print-root .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #475569;
        margin-bottom: 4px;
        display: block;
    }
    #barcode-print-root .form-control,
    #barcode-print-root .form-select {
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 6px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #barcode-print-root .form-control:focus,
    #barcode-print-root .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Mobile Screen Responsiveness */
    @media screen and (max-width: 767.98px) {
        .barcode-header h5 {
            font-size: 16px !important;
        }
        .source-tabs-wrap {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 4px;
        }
        .source-tab-btn {
            padding: 6px 4px !important;
            font-size: 10.5px !important;
            text-align: center;
            justify-content: center;
        }
        .barcode-panel-card {
            padding: 12px !important;
            margin-bottom: 12px !important;
        }
        .custom-table th, .custom-table td {
            padding: 8px 10px;
            font-size: 11.5px;
        }
        .grid-4-col, .grid-5-col {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 8px;
        }
        .preview-toolbar-wrap {
            flex-direction: column;
            align-items: stretch !important;
            gap: 8px;
        }
        .preview-toolbar-wrap select,
        .preview-toolbar-wrap button {
            width: 100% !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="barcode-print-root">
    <!-- Header & Source Switcher Tabs -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 pb-2 border-bottom barcode-header">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                    <i class="fas fa-barcode text-warning"></i>
                    <span>Universal Barcode Center</span>
                </h5>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-0.5" style="font-size: 9.5px;">
                    Code-128 Engine Active
                </span>
            </div>
            <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">
                Generate and batch print high-density thermal barcode stickers for products, orders, and support tokens.
            </small>
        </div>
        <div class="source-tabs-wrap d-flex gap-1">
            <input type="radio" class="btn-check" name="barcode_source_type" id="src-product" value="product" checked onchange="switchSourceType('product')">
            <label class="source-tab-btn" for="src-product">
                <i class="fas fa-boxes-stacked"></i> <span>Products</span>
            </label>

            <input type="radio" class="btn-check" name="barcode_source_type" id="src-order" value="order" onchange="switchSourceType('order')">
            <label class="source-tab-btn" for="src-order">
                <i class="fas fa-receipt"></i> <span>Orders</span>
            </label>

            <input type="radio" class="btn-check" name="barcode_source_type" id="src-ticket" value="ticket" onchange="switchSourceType('ticket')">
            <label class="source-tab-btn" for="src-ticket">
                <i class="fas fa-ticket-alt"></i> <span>Support Tokens</span>
            </label>
        </div>
    </div>

    <!-- 1. Search & Selection Command Section -->
    <div class="barcode-panel-card mb-3">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
            <div class="d-flex align-items-center gap-2">
                <span class="step-pill">1</span>
                <h6 class="fw-bold text-dark mb-0" style="font-size: 13.5px;" id="select-label">Select Product to Print</h6>
            </div>
            <button type="button" class="btn btn-outline-danger btn-compact" onclick="resetBarcodeScreen()">
                <i class="fas fa-rotate-left me-1"></i> Clear Screen
            </button>
        </div>

        <div class="row g-2 align-items-center">
            <!-- Search Input -->
            <div class="col-12 col-md-7">
                <!-- Product Search Container -->
                <div id="wrapper-select-product">
                    <div class="ajax-search-wrapper">
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0">
                                <i class="fas fa-search" style="font-size: 11px;"></i>
                            </span>
                            <input type="text" id="product-live-search" class="form-control border-start-0" 
                                   placeholder="Type product name, SKU, code, or scan barcode..." autocomplete="off">
                            <button class="btn btn-light border border-start-0 text-muted" type="button" id="clearProductSearchBtn" style="display:none;" onclick="clearProductInput()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div id="product-search-results" class="ajax-search-results"></div>
                    </div>
                </div>

                <!-- Order Search Container -->
                <div id="wrapper-select-order" style="display: none;">
                    <div class="ajax-search-wrapper">
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0">
                                <i class="fas fa-search" style="font-size: 11px;"></i>
                            </span>
                            <input type="text" id="order-live-search" class="form-control border-start-0" 
                                   placeholder="Search invoice no, order ID, or customer phone..." autocomplete="off">
                            <button class="btn btn-light border border-start-0 text-muted" type="button" id="clearOrderSearchBtn" style="display:none;" onclick="clearOrderInput()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div id="order-search-results" class="ajax-search-results"></div>
                    </div>
                </div>

                <!-- Ticket Search Container -->
                <div id="wrapper-select-ticket" style="display: none;">
                    <div class="ajax-search-wrapper">
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0">
                                <i class="fas fa-search" style="font-size: 11px;"></i>
                            </span>
                            <input type="text" id="ticket-live-search" class="form-control border-start-0" 
                                   placeholder="Search token number, ID, or subject..." autocomplete="off">
                            <button class="btn btn-light border border-start-0 text-muted" type="button" id="clearTicketSearchBtn" style="display:none;" onclick="clearTicketInput()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div id="ticket-search-results" class="ajax-search-results"></div>
                    </div>
                </div>
            </div>

            <!-- Direct Dropdown Picker -->
            <div class="col-12 col-md-5">
                <div id="dropdown-select-product">
                    <select id="barcode-product-select" class="form-select form-select-sm">
                        <option value="">-- Or Choose Product Directly --</option>
                        @if(isset($products) && count($products) > 0)
                            @foreach($products as $p)
                                <option value="{{ $p->id }}">{{ $p->title }} ({{ $p->product_code ?? 'PRD-'.$p->id }})</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div id="dropdown-select-order" style="display: none;">
                    <select id="barcode-order-select" class="form-select form-select-sm">
                        <option value="">-- Or Choose Order / Invoice --</option>
                        @if(isset($orders) && count($orders) > 0)
                            @foreach($orders as $o)
                                <option value="{{ $o->id }}">Invoice: {{ $o->invoice_no ?? '#'.$o->id }} \vert{} Bill: ৳{{ number_format($o->grand_total ?? 0) }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div id="dropdown-select-ticket" style="display: none;">
                    <select id="barcode-ticket-select" class="form-select form-select-sm">
                        <option value="">-- Or Choose Support Ticket --</option>
                        @if(isset($tickets) && count($tickets) > 0)
                            @foreach($tickets as $t)
                                <option value="{{ $t->id }}">Token: {{ $t->ticket_no ?? 'TK-'.$t->id }} \vert{} {{$t->subject }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
        </div>

        <!-- Staged Product Configuration Table -->
        <div class="mt-3 border-top pt-3" id="loaded-product-table-area" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small fw-bold text-dark" style="font-size: 11.5px;">
                    <i class="fas fa-barcode text-primary me-1"></i> Configure Barcode &amp; Print Quantity:
                </span>
                <span class="badge bg-light text-muted border font-monospace" style="font-size: 9.5px;">Staging Item</span>
            </div>
            <div class="table-responsive border" style="-webkit-overflow-scrolling: touch;">
                <table class="table custom-table align-middle mb-0 text-nowrap">
                    <thead>
                        <tr>
                            <th style="min-width: 220px;">Item / Subject</th>
                            <th style="min-width: 170px;">Barcode Data</th>
                            <th style="min-width: 120px; width: 140px;">Print Quantity</th>
                            <th class="text-end pe-3" style="min-width: 90px; width: 100px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="loaded-product-tbody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 2. Selected Items Queue Table -->
    <div class="barcode-panel-card mb-3" id="selected-products-panel" style="display: none;">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
            <div class="d-flex align-items-center gap-2">
                <span class="step-pill">2</span>
                <h6 class="fw-bold text-dark mb-0" style="font-size: 13.5px;">
                    Selected Queue For Print (<span id="queueCountBadge" class="bn-digit text-primary">0</span> Items)
                </h6>
            </div>
            <button type="button" class="btn btn-outline-danger btn-compact" onclick="clearEntireQueue()">
                <i class="fas fa-trash-alt me-1"></i> Empty Queue
            </button>
        </div>

        <div class="table-responsive border" style="-webkit-overflow-scrolling: touch;">
            <table class="table custom-table align-middle mb-0 text-nowrap">
                <thead>
                    <tr>
                        <th style="min-width: 220px;">Item Details</th>
                        <th style="min-width: 170px;">Barcode Code</th>
                        <th style="min-width: 120px; width: 140px;">Print Quantity</th>
                        <th class="text-end pe-3" style="min-width: 90px; width: 100px;">Action</th>
                    </tr>
                </thead>
                <tbody id="selected-products-tbody"></tbody>
            </table>
        </div>
    </div>

    <!-- 3. Live Preview & Sheet Settings Toolbar -->
    <div class="barcode-panel-card mb-3" id="barcode-preview-panel" style="display: none;">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 pb-2 border-bottom preview-toolbar-wrap">
            <div class="d-flex align-items-center gap-2">
                <span class="step-pill">3</span>
                <div>
                    <h6 class="fw-bold text-dark mb-0" style="font-size: 13.5px;">Thermal Print Sheet Preview</h6>
                    <small class="text-muted d-block" style="font-size: 10px;">Rendered directly using standard Code-128 protocol</small>
                </div>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2 w-100 w-md-auto">
                <select id="sticker-size-select" class="form-select form-select-sm" style="width: 140px;" onchange="renderStickersPreview()">
                    <option value="size-44-34" selected>44mm &times; 34mm (Standard)</option>
                    <option value="size-50-25">50mm &times; 25mm (Shelf)</option>
                    <option value="size-38-25">38mm &times; 25mm (Compact)</option>
                </select>

                <select id="sticker-layout-select" class="form-select form-select-sm" style="width: 150px;" onchange="renderStickersPreview()">
                    <option value="grid-1-col">Single Column (Roll)</option>
                    <option value="grid-4-col">A4 / A5 - 4 Columns</option>
                    <option value="grid-5-col" selected>A4 / A5 - 5 Columns</option>
                </select>

                <button type="button" class="btn btn-warning btn-compact text-dark fw-bold px-3 shadow-xs" onclick="printNow()">
                    <i class="fas fa-print me-1"></i> Print Now
                </button>
            </div>
        </div>

        <div id="print-area-wrapper" class="p-3 bg-light overflow-auto border" style="border-radius: 5px;">
            <div id="barcode-stickers-container" class="grid-5-col"></div>
        </div>
    </div>
</div>

<!-- Notice Modal (Replaces browser alert) -->
<div class="modal fade barcode-modal-scope" id="barcodeNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-warning-subtle text-warning-emphasis d-inline-flex align-items-center justify-content-center mx-auto mb-2" id="barcodeNoticeIconWrap" style="width: 44px; height: 44px;">
                    <i class="fas fa-circle-exclamation fs-4" id="barcodeNoticeIcon"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="barcodeNoticeTitle" style="font-size: 14px;">Notice</h6>
                <p class="text-muted small mb-3" id="barcodeNoticeMsg" style="font-size: 12px; line-height: 1.45;"></p>
                <button type="button" class="btn btn-dark btn-compact px-4" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentSourceType = 'product';
    let currentLoadedItem = null;
    let selectedProductsQueue = [];
    let searchDebounceTimer = null;

    function showBarcodeNotice(message, title = 'Notice', type = 'warning') {
        const titleEl  = document.getElementById('barcodeNoticeTitle');
        const msgEl    = document.getElementById('barcodeNoticeMsg');
        const iconWrap = document.getElementById('barcodeNoticeIconWrap');
        const iconEl   = document.getElementById('barcodeNoticeIcon');

        if (titleEl) titleEl.innerText = title;
        if (msgEl) msgEl.innerText = message;

        if (iconWrap && iconEl) {
            if (type === 'error' || type === 'danger') {
                iconWrap.className = 'rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-2';
                iconEl.className = 'fas fa-circle-xmark fs-4';
            } else if (type === 'success') {
                iconWrap.className = 'rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mx-auto mb-2';
                iconEl.className = 'fas fa-circle-check fs-4';
            } else {
                iconWrap.className = 'rounded-circle bg-warning-subtle text-warning-emphasis d-inline-flex align-items-center justify-content-center mx-auto mb-2';
                iconEl.className = 'fas fa-circle-exclamation fs-4';
            }
        }

        const modalEl = document.getElementById('barcodeNoticeModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function switchSourceType(type) {
        currentSourceType = type;
        
        // Inputs
        document.getElementById('wrapper-select-product').style.display = (type === 'product') ? 'block' : 'none';
        document.getElementById('wrapper-select-order').style.display = (type === 'order') ? 'block' : 'none';
        document.getElementById('wrapper-select-ticket').style.display = (type === 'ticket') ? 'block' : 'none';

        // Dropdowns
        document.getElementById('dropdown-select-product').style.display = (type === 'product') ? 'block' : 'none';
        document.getElementById('dropdown-select-order').style.display = (type === 'order') ? 'block' : 'none';
        document.getElementById('dropdown-select-ticket').style.display = (type === 'ticket') ? 'block' : 'none';

        const labels = { product: 'Select Product to Print', order: 'Select Order / Invoice to Print', ticket: 'Select Support Token to Print' };
        document.getElementById('select-label').innerText = labels[type];
        document.getElementById('loaded-product-table-area').style.display = 'none';
    }

    // Dropdown Change Listeners
    document.getElementById('barcode-product-select').addEventListener('change', function() {
        if (this.value) fetchItemDetails(this.value, 'product');
    });
    document.getElementById('barcode-order-select').addEventListener('change', function() {
        if (this.value) fetchItemDetails(this.value, 'order');
    });
    document.getElementById('barcode-ticket-select').addEventListener('change', function() {
        if (this.value) fetchItemDetails(this.value, 'ticket');
    });

    // 1. PRODUCT AJAX SEARCH
    const prodInput = document.getElementById('product-live-search');
    const prodResults = document.getElementById('product-search-results');
    const clearProdBtn = document.getElementById('clearProductSearchBtn');

    prodInput.addEventListener('input', function() {
        const query = this.value.trim();
        clearProdBtn.style.display = query.length > 0 ? 'inline-block' : 'none';

        clearTimeout(searchDebounceTimer);
        if (query.length < 1) {
            prodResults.style.display = 'none';
            prodResults.innerHTML = '';
            return;
        }

        searchDebounceTimer = setTimeout(() => {
            fetch(`{{ route('admin.product.barcode.search') }}?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(products => {
                    prodResults.innerHTML = '';
                    if (products && products.length > 0) {
                        products.forEach(item => {
                            const skuOrCode = item.sku || item.product_code || ('PRD-' + item.id);
                            const barcode = item.barcode || skuOrCode;

                            const itemElem = document.createElement('div');
                            itemElem.className = 'ajax-search-item';
                            itemElem.innerHTML = `
                                <div class="bg-light text-primary border p-1 text-center font-monospace bn-digit flex-shrink-0" style="width: 32px; height: 32px; border-radius: 5px; font-size: 10px; display: flex; align-items: center; justify-content: center;">
                                    #${item.id}
                                </div>
                                <div class="flex-grow-1 text-truncate">
                                    <strong class="text-dark d-block text-truncate" style="font-size: 12px;">${item.title}</strong>
                                    <small class="text-muted font-monospace bn-digit" style="font-size: 10px;">SKU: <strong class="text-dark">${skuOrCode}</strong> | Barcode: ${barcode}</small>
                                </div>
                            `;
                            itemElem.addEventListener('click', () => {
                                prodInput.value = `${item.title} (${skuOrCode})`;
                                prodResults.style.display = 'none';
                                document.getElementById('barcode-product-select').value = item.id;
                                fetchItemDetails(item.id, 'product');
                            });
                            prodResults.appendChild(itemElem);
                        });
                        prodResults.style.display = 'block';
                    } else {
                        prodResults.innerHTML = '<div class="p-3 text-muted text-center small">No matching products found.</div>';
                        prodResults.style.display = 'block';
                    }
                })
                .catch(err => console.error("Error searching product:", err));
        }, 250);
    });

    function clearProductInput() {
        prodInput.value = '';
        clearProdBtn.style.display = 'none';
        prodResults.style.display = 'none';
        prodResults.innerHTML = '';
    }

    // 2. ORDER AJAX SEARCH
    const orderInput = document.getElementById('order-live-search');
    const orderResults = document.getElementById('order-search-results');
    const clearOrderBtn = document.getElementById('clearOrderSearchBtn');

    orderInput.addEventListener('input', function() {
        const query = this.value.trim();
        clearOrderBtn.style.display = query.length > 0 ? 'inline-block' : 'none';

        clearTimeout(searchDebounceTimer);
        if (query.length < 1) {
            orderResults.style.display = 'none';
            orderResults.innerHTML = '';
            return;
        }

        searchDebounceTimer = setTimeout(() => {
            fetch(`{{ route('admin.product.barcode.search_order') }}?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(orders => {
                    orderResults.innerHTML = '';
                    if (orders && orders.length > 0) {
                        orders.forEach(o => {
                            const invNo = o.invoice_no || ('#' + o.id);
                            const itemElem = document.createElement('div');
                            itemElem.className = 'ajax-search-item';
                            itemElem.innerHTML = `
                                <div class="bg-primary-subtle text-primary border border-primary-subtle p-2 text-center" style="width: 32px; height: 32px; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-file-invoice" style="font-size: 11px;"></i>
                                </div>
                                <div class="flex-grow-1 text-truncate">
                                    <strong class="text-dark d-block text-truncate" style="font-size: 12px;">Invoice: ${invNo}</strong>
                                    <small class="text-muted font-monospace bn-digit" style="font-size: 10px;">Order ID: #${o.id} | Bill: ৳${Number(o.grand_total || 0).toLocaleString()}</small>
                                </div>
                            `;
                            itemElem.addEventListener('click', () => {
                                orderInput.value = `Invoice: ${invNo} | Bill: ৳${Number(o.grand_total || 0).toLocaleString()}`;
                                orderResults.style.display = 'none';
                                document.getElementById('barcode-order-select').value = o.id;
                                fetchItemDetails(o.id, 'order');
                            });
                            orderResults.appendChild(itemElem);
                        });
                        orderResults.style.display = 'block';
                    } else {
                        orderResults.innerHTML = '<div class="p-3 text-muted text-center small">No matching orders found.</div>';
                        orderResults.style.display = 'block';
                    }
                })
                .catch(err => console.error("Error searching order:", err));
        }, 250);
    });

    function clearOrderInput() {
        orderInput.value = '';
        clearOrderBtn.style.display = 'none';
        orderResults.style.display = 'none';
        orderResults.innerHTML = '';
    }

    // 3. SUPPORT TICKET AJAX SEARCH
    const ticketInput = document.getElementById('ticket-live-search');
    const ticketResults = document.getElementById('ticket-search-results');
    const clearTicketBtn = document.getElementById('clearTicketSearchBtn');

    ticketInput.addEventListener('input', function() {
        const query = this.value.trim();
        clearTicketBtn.style.display = query.length > 0 ? 'inline-block' : 'none';

        clearTimeout(searchDebounceTimer);
        if (query.length < 1) {
            ticketResults.style.display = 'none';
            ticketResults.innerHTML = '';
            return;
        }

        searchDebounceTimer = setTimeout(() => {
            fetch(`{{ route('admin.product.barcode.search_ticket') }}?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(tickets => {
                    ticketResults.innerHTML = '';
                    if (tickets && tickets.length > 0) {
                        tickets.forEach(t => {
                            const tkNo = t.ticket_no || ('TK-' + t.id);
                            const itemElem = document.createElement('div');
                            itemElem.className = 'ajax-search-item';
                            itemElem.innerHTML = `
                                <div class="bg-warning-subtle text-warning-emphasis border border-warning-subtle p-2 text-center" style="width: 32px; height: 32px; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-ticket-alt" style="font-size: 11px;"></i>
                                </div>
                                <div class="flex-grow-1 text-truncate">
                                    <strong class="text-dark d-block text-truncate" style="font-size: 12px;">${t.subject}</strong>
                                    <small class="text-muted font-monospace bn-digit" style="font-size: 10px;">Token: ${tkNo} | ID: #${t.id}</small>
                                </div>
                            `;
                            itemElem.addEventListener('click', () => {
                                ticketInput.value = `Token: ${tkNo} | ${t.subject}`;
                                ticketResults.style.display = 'none';
                                document.getElementById('barcode-ticket-select').value = t.id;
                                fetchItemDetails(t.id, 'ticket');
                            });
                            ticketResults.appendChild(itemElem);
                        });
                        ticketResults.style.display = 'block';
                    } else {
                        ticketResults.innerHTML = '<div class="p-3 text-muted text-center small">No matching tickets found.</div>';
                        ticketResults.style.display = 'block';
                    }
                })
                .catch(err => console.error("Error searching ticket:", err));
        }, 250);
    });

    function clearTicketInput() {
        ticketInput.value = '';
        clearTicketBtn.style.display = 'none';
        ticketResults.style.display = 'none';
        ticketResults.innerHTML = '';
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.ajax-search-wrapper')) {
            prodResults.style.display = 'none';
            orderResults.style.display = 'none';
            ticketResults.style.display = 'none';
        }
    });

    // FETCH ITEM DETAILS & RENDER
    function fetchItemDetails(id, type) {
        fetch(`{{ url('admin/product/barcode/get-details') }}/${id}?type=${type}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (type === 'product') {
                        currentLoadedItem = data.product;
                        displayProductRow(data.product, data.variations);
                    } else {
                        currentLoadedItem = data.data;
                        displayGenericRow(data.data);
                    }
                }
            })
            .catch(err => console.error("Error fetching barcode item:", err));
    }

    function displayProductRow(prod, variations) {
        const tbody = document.getElementById('loaded-product-tbody');
        tbody.innerHTML = '';

        const barcodeVal = prod.barcode || prod.product_code || ('PRD-' + prod.id);

        let html = `
            <tr id="row-prod-${prod.id}">
                <td>
                    <div class="min-width-0">
                        <strong class="text-dark d-block text-truncate" style="font-size: 12.5px;">${prod.title}</strong>
                        <small class="text-muted font-monospace bn-digit" style="font-size: 10px;">Code: ${prod.product_code || ('PRD-' + prod.id)}</small>
                    </div>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm font-monospace bn-digit" 
                           id="barcode-input-${prod.id}" value="${barcodeVal}" 
                           placeholder="Type barcode..." oninput="onBarcodeInputChange('${prod.id}', this.value)">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm font-monospace bn-digit" id="qty-input-${prod.id}" value="1" min="1">
                </td>
                <td class="text-end pe-3">
                    <button type="button" class="btn btn-warning btn-compact text-dark fw-bold px-3 shadow-xs" onclick="addToPrintQueue('${prod.id}')">
                        <i class="fas fa-plus me-1"></i> Add to Queue
                    </button>
                </td>
            </tr>
        `;

        if (variations && variations.length > 0) {
            variations.forEach(v => {
                const varSku = v.sku || v.product_code || '—';
                const varBarcode = v.barcode || prod.barcode || varSku;
                html += `
                    <tr class="bg-light-subtle">
                        <td class="ps-3">
                            <span class="text-muted small font-monospace"><i class="fas fa-level-up-alt fa-rotate-90 me-1"></i> Variant: <strong>${varSku}</strong></span>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm font-monospace bn-digit" 
                                   id="barcode-input-var-${v.id}" value="${varBarcode}" 
                                   oninput="onBarcodeInputChange('var-'+${v.id}, this.value)">
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm font-monospace bn-digit" id="qty-input-var-${v.id}" value="1" min="1">
                        </td>
                        <td class="text-end pe-3">
                            <button type="button" class="btn btn-outline-warning btn-compact text-dark fw-bold px-3 shadow-xs" onclick="addVariantToPrintQueue(${v.id}, '${varSku}')">
                                <i class="fas fa-plus me-1"></i> Add
                            </button>
                        </td>
                    </tr>
                `;
            });
        }

        tbody.innerHTML = html;
        document.getElementById('loaded-product-table-area').style.display = 'block';
    }

    function displayGenericRow(item) {
        const tbody = document.getElementById('loaded-product-tbody');
        tbody.innerHTML = `
            <tr id="row-${item.id}">
                <td>
                    <strong class="text-dark d-block" style="font-size: 12.5px;">${item.title}</strong>
                    <small class="text-muted font-monospace bn-digit">${item.subtitle}</small>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm font-monospace bn-digit" 
                           id="barcode-input-${item.id}" value="${item.barcode || ''}" 
                           oninput="onBarcodeInputChange('${item.id}', this.value)">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm font-monospace bn-digit" id="qty-input-${item.id}" value="1" min="1">
                </td>
                <td class="text-end pe-3">
                    <button type="button" class="btn btn-warning btn-compact text-dark fw-bold px-3 shadow-xs" onclick="addGenericToPrintQueue('${item.id}')">
                        <i class="fas fa-plus me-1"></i> Add to Queue
                    </button>
                </td>
            </tr>
        `;
        document.getElementById('loaded-product-table-area').style.display = 'block';
    }

    function onBarcodeInputChange(id, val) {
        if (currentLoadedItem && (currentLoadedItem.id == id || 'order-'+currentLoadedItem.id == id || 'token-'+currentLoadedItem.id == id)) {
            currentLoadedItem.barcode = val;
        }
        const item = selectedProductsQueue.find(x => x.id == id);
        if (item) {
            item.barcode = val;
            renderStickersPreview();
        }
    }

    function addToPrintQueue(prodId) {
        const barcodeVal = document.getElementById(`barcode-input-${prodId}`).value.trim();
        const qtyVal = parseInt(document.getElementById(`qty-input-${prodId}`).value) || 1;

        if (!barcodeVal) return showBarcodeNotice('Barcode value cannot be empty.', 'Validation Warning', 'warning');

        const price = currentLoadedItem.sale_price || currentLoadedItem.regular_price || 0;
        const itemData = {
            id: prodId,
            title: currentLoadedItem.title,
            barcode: barcodeVal,
            price: price,
            quantity: qtyVal,
            footer_label: `Price: ৳${Number(price).toLocaleString()}`
        };

        enqueueItem(itemData);
    }

    function addVariantToPrintQueue(varId, sku) {
        const barcodeVal = document.getElementById(`barcode-input-var-${varId}`).value.trim();
        const qtyVal = parseInt(document.getElementById(`qty-input-var-${varId}`).value) || 1;

        if (!barcodeVal) return showBarcodeNotice('Variant barcode value cannot be empty.', 'Validation Warning', 'warning');

        const price = currentLoadedItem.sale_price || currentLoadedItem.regular_price || 0;
        const itemData = {
            id: 'var-' + varId,
            title: `${currentLoadedItem.title} (${sku})`,
            barcode: barcodeVal,
            price: price,
            quantity: qtyVal,
            footer_label: `Price: ৳${Number(price).toLocaleString()}`
        };

        enqueueItem(itemData);
    }

    function addGenericToPrintQueue(itemId) {
        const barcodeVal = document.getElementById(`barcode-input-${itemId}`).value.trim();
        const qtyVal = parseInt(document.getElementById(`qty-input-${itemId}`).value) || 1;

        if (!barcodeVal) return showBarcodeNotice('Barcode value cannot be empty.', 'Validation Warning', 'warning');

        const itemData = {
            id: itemId,
            title: currentLoadedItem.title,
            barcode: barcodeVal,
            quantity: qtyVal,
            footer_label: currentLoadedItem.price ? `Bill: ৳${Number(currentLoadedItem.price).toLocaleString()}` : currentLoadedItem.subtitle
        };

        enqueueItem(itemData);
    }

    function enqueueItem(itemData) {
        const existingIndex = selectedProductsQueue.findIndex(x => x.id == itemData.id);
        if (existingIndex > -1) {
            selectedProductsQueue[existingIndex] = itemData;
        } else {
            selectedProductsQueue.push(itemData);
        }

        renderSelectedQueueTable();
        renderStickersPreview();
    }

    function renderSelectedQueueTable() {
        const tbody = document.getElementById('selected-products-tbody');
        const queueBadge = document.getElementById('queueCountBadge');
        tbody.innerHTML = '';

        if (selectedProductsQueue.length === 0) {
            document.getElementById('selected-products-panel').style.display = 'none';
            document.getElementById('barcode-preview-panel').style.display = 'none';
            if (queueBadge) queueBadge.innerText = '0';
            return;
        }

        if (queueBadge) queueBadge.innerText = selectedProductsQueue.length;

        selectedProductsQueue.forEach((item, index) => {
            tbody.innerHTML += `
                <tr>
                    <td class="fw-semibold text-dark">${item.title}</td>
                    <td class="font-monospace bn-digit">${item.barcode}</td>
                    <td>
                        <input type="number" class="form-control form-control-sm font-monospace bn-digit" style="max-width: 120px;" 
                               value="${item.quantity}" min="1" onchange="updateQueueQty(${index}, this.value)">
                    </td>
                    <td class="text-end pe-3">
                        <button type="button" class="btn btn-outline-danger btn-compact" onclick="removeFromQueue(${index})">
                            <i class="fas fa-times me-1"></i> Remove
                        </button>
                    </td>
                </tr>
            `;
        });

        document.getElementById('selected-products-panel').style.display = 'block';
        document.getElementById('barcode-preview-panel').style.display = 'block';
    }

    function clearEntireQueue() {
        selectedProductsQueue = [];
        renderSelectedQueueTable();
    }

    function updateQueueQty(index, newQty) {
        selectedProductsQueue[index].quantity = parseInt(newQty) || 1;
        renderStickersPreview();
    }

    function removeFromQueue(index) {
        selectedProductsQueue.splice(index, 1);
        renderSelectedQueueTable();
        renderStickersPreview();
    }

    function renderStickersPreview() {
        const container = document.getElementById('barcode-stickers-container');
        const layoutClass = document.getElementById('sticker-layout-select').value;
        const sizeClass = document.getElementById('sticker-size-select').value;

        container.className = layoutClass;
        container.innerHTML = '';

        selectedProductsQueue.forEach((item) => {
            for (let i = 0; i < item.quantity; i++) {
                const uniqueSvgId = `barcode-svg-${item.id}-${i}`;
                const stickerHtml = `
                    <div class="barcode-sticker ${sizeClass}">
                        <div class="st-title" title="${item.title}">${item.title}</div>
                        <svg id="${uniqueSvgId}"></svg>
                        <div class="st-price bn-digit font-monospace">${item.footer_label}</div>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', stickerHtml);

                JsBarcode(`#${uniqueSvgId}`, String(item.barcode), {
                    format: "CODE128",
                    lineColor: "#000000",
                    width: 1.3,
                    height: 36,
                    displayValue: true,
                    fontSize: 9.5,
                    margin: 1,
                    fontOptions: "bold"
                });
            }
        });
    }

    function resetBarcodeScreen() {
        document.getElementById('barcode-product-select').value = '';
        document.getElementById('barcode-order-select').value = '';
        document.getElementById('barcode-ticket-select').value = '';
        clearProductInput();
        clearOrderInput();
        clearTicketInput();
        document.getElementById('loaded-product-table-area').style.display = 'none';
        selectedProductsQueue = [];
        renderSelectedQueueTable();
    }

    // Isolated Print Engine (Guarantees zero blank pages & exact 4 or 5 columns on paper)
    function printNow() {
        const container = document.getElementById('barcode-stickers-container');
        if (!container || selectedProductsQueue.length === 0) {
            showBarcodeNotice('Please add at least one item to queue before printing.', 'Queue Empty', 'warning');
            return;
        }

        const layoutClass = document.getElementById('sticker-layout-select').value;
        const sizeClass   = document.getElementById('sticker-size-select').value;

        let iframe = document.getElementById('barcode-isolated-print-frame');
        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = 'barcode-isolated-print-frame';
            iframe.style.position = 'fixed';
            iframe.style.right = '0';
            iframe.style.bottom = '0';
            iframe.style.width = '0';
            iframe.style.height = '0';
            iframe.style.border = '0';
            document.body.appendChild(iframe);
        }

        const doc = iframe.contentWindow.document;
        doc.open();
        doc.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Barcode_Print_${Date.now()}</title>
                <style>
                    @page {
                        margin: 4mm;
                        size: auto;
                    }
                    * {
                        box-sizing: border-box !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                    html, body {
                        margin: 0 !important;
                        padding: 0 !important;
                        background: #ffffff !important;
                        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
                    }
                    .grid-1-col {
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        gap: 2.5mm;
                        width: 100%;
                    }
                    .grid-1-col .barcode-sticker {
                        max-width: 60mm;
                    }
                    .grid-4-col {
                        display: grid !important;
                        grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
                        gap: 2.5mm 1.5mm !important;
                        width: 100% !important;
                    }
                    .grid-5-col {
                        display: grid !important;
                        grid-template-columns: repeat(5, minmax(0, 1fr)) !important;
                        gap: 2mm 1.2mm !important;
                        width: 100% !important;
                    }
                    .barcode-sticker {
                        border: 1px dashed #94a3b8 !important;
                        background: #ffffff !important;
                        padding: 4px 2px !important;
                        display: flex !important;
                        flex-direction: column !important;
                        align-items: center !important;
                        justify-content: center !important;
                        text-align: center !important;
                        box-sizing: border-box !important;
                        page-break-inside: avoid !important;
                        break-inside: avoid !important;
                        width: 100% !important;
                        max-width: 100% !important;
                        border-radius: 4px !important;
                    }
                    .barcode-sticker .st-title {
                        font-size: 8.5px !important;
                        font-weight: 700 !important;
                        color: #000000 !important;
                        line-height: 1.15 !important;
                        max-width: 98% !important;
                        margin-bottom: 2px !important;
                        white-space: normal !important;
                        overflow: hidden !important;
                        text-overflow: ellipsis !important;
                        display: -webkit-box !important;
                        -webkit-line-clamp: 2 !important;
                        -webkit-box-orient: vertical !important;
                    }
                    .barcode-sticker svg {
                        width: 100% !important;
                        max-height: 38px !important;
                        display: block !important;
                    }
                    .barcode-sticker .st-price {
                        font-size: 9px !important;
                        font-weight: 700 !important;
                        color: #000000 !important;
                        margin-top: 1px !important;
                        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
                    }
                    .size-44-34 { min-height: 31mm; max-height: 33mm; }
                    .size-50-25 { min-height: 23mm; max-height: 25mm; }
                    .size-38-25 { min-height: 23mm; max-height: 25mm; }
                </style>
            </head>
            <body>
                <div class="${layoutClass}">
                    ${container.innerHTML}
                </div>
            </body>
            </html>
        `);
        doc.close();

        setTimeout(() => {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        }, 250);
    }
</script>
@endsection