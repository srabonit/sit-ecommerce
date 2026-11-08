@extends('layouts.admin')

@section('title', 'Create Purchase Bill')

@section('admin_content')
<style>
    /* =========================================================
       PURCHASE BILL — KAIADMIN CLASSIC / WHITE THEME
    ========================================================= */

    .bill-create-root {
        --bill-primary: var(--bs-primary, #1572e8);
        --bill-success: var(--bs-success, #31ce36);
        --bill-danger: var(--bs-danger, #f25961);
        --bill-warning: var(--bs-warning, #ffad46);
        --bill-info: var(--bs-info, #48abf7);
        --bill-heading: #2a2f5b;
        --bill-text: #495057;
        --bill-muted: #8d9498;
        --bill-border: #ebecec;
        --bill-surface: #ffffff;
        --bill-soft: #f8f9fa;
        color: var(--bill-text);
        min-width: 0;
    }

    .bill-create-root *,
    .bill-create-root *::before,
    .bill-create-root *::after {
        box-sizing: border-box;
    }

    .bill-create-root .card,
    .bill-create-root .modal-content,
    .bill-create-root .form-control,
    .bill-create-root .form-select,
    .bill-create-root .input-group-text,
    .bill-create-root .btn,
    .bill-create-root .badge {
        border-radius: 5px;
    }

    .bill-create-root .card {
        border: 1px solid var(--bill-border) !important;
        background: var(--bill-surface);
        box-shadow: 0 1px 4px rgba(0, 0, 0, .035) !important;
        min-width: 0;
    }

    .bill-create-root .card-header,
    .bill-create-root .card-footer {
        background: #fff !important;
        border-color: var(--bill-border) !important;
    }

    .bill-create-root .card-header {
        padding: 14px 18px;
    }

    .bill-create-root .card-body {
        min-width: 0;
    }

    .bill-create-root h5,
    .bill-create-root h6 {
        color: var(--bill-heading);
    }

    .bill-create-root .page-heading {
        font-size: clamp(17px, 1.5vw, 21px);
        font-weight: 700;
        letter-spacing: -.3px;
    }

    .bill-create-root .section-heading {
        font-size: 13px;
        font-weight: 700;
        margin: 0;
        color: var(--bill-heading);
    }

    .bill-create-root .section-description {
        display: block;
        font-size: 12px;
        line-height: 1.5;
        color: var(--bill-muted);
        margin-top: 4px;
    }

    /* Header */
    .bill-create-root .bill-page-header {
        gap: 12px;
    }

    .bill-create-root .bill-page-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }

    .bill-create-root .bill-page-actions .btn {
        min-height: 34px;
        font-size: 12px;
        font-weight: 600;
        padding: 6px 12px;
    }

    /* Form Controls */
    .bill-create-root .form-label {
        color: #495057;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 6px;
    }

    .bill-create-root .form-control,
    .bill-create-root .form-select {
        min-height: 36px;
        border-color: #e4e6e8;
        color: #495057;
        font-size: 12px;
        background-color: #fff;
    }

    .bill-create-root .form-control::placeholder {
        color: #aab0b5;
        opacity: 1;
    }

    .bill-create-root .form-control:focus,
    .bill-create-root .form-select:focus {
        border-color: var(--bill-primary);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .10);
    }

    .bill-create-root .input-group .btn {
        min-height: 36px;
    }

    .bill-create-root .form-text,
    .bill-create-root .text-muted {
        color: var(--bill-muted) !important;
    }

    .bill-create-root .btn {
        font-size: 12px;
        font-weight: 600;
        transition: background-color .15s ease, border-color .15s ease;
    }

    .bill-create-root .btn-sm {
        min-height: 32px;
        padding: 6px 11px;
    }

    .bill-create-root .btn-primary {
        box-shadow: none;
    }

    /* Cards and dropdown overflow */
    .bill-create-root .card,
    .bill-create-root .card-body {
        overflow: visible;
    }

    /* PO / GRN selectors */
    .bill-create-root .sourcing-selector {
        width: 250px;
        max-width: 100%;
    }

    /* AJAX Search */
    .bill-create-root .bill-search-wrapper {
        position: relative;
        width: 100%;
        min-width: 0;
    }

    .bill-create-root .bill-search-results {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        width: max-content;
        min-width: min(420px, 100%);
        max-width: min(480px, calc(100vw - 36px));
        z-index: 999999;
        background: #fff;
        border: 1px solid #e3e7eb;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .13);
        border-radius: 5px;
        max-height: 280px;
        overflow-y: auto;
        display: none;
    }

    .bill-create-root .bill-search-item {
        padding: 10px 12px;
        cursor: pointer;
        border-bottom: 1px solid #f1f2f3;
        background: #fff;
        transition: background .12s ease;
    }

    .bill-create-root .bill-search-item:last-child {
        border-bottom: 0;
    }

    .bill-create-root .bill-search-item:hover,
    .bill-create-root .bill-search-item.active {
        background-color: #f1f6ff;
    }

    .bill-create-root .active-search-row {
        position: relative;
        z-index: 1000;
    }

    /* Product Table */
    .bill-create-root .bill-items-scroll {
        width: 100%;
        overflow-x: auto;
        overflow-y: visible;
        -webkit-overflow-scrolling: touch;
    }

    .bill-create-root .table-bill-items {
        width: 100%;
        min-width: 1120px;
        margin-bottom: 0;
        border-color: #ebecec;
    }

    .bill-create-root .table-bill-items th {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .35px;
        background-color: #f8f9fa;
        border-bottom: 1px solid #e5e7eb;
        color: #686c71;
        white-space: nowrap;
        padding: 11px 8px;
        vertical-align: middle;
    }

    .bill-create-root .table-bill-items td {
        padding: 9px 7px;
        vertical-align: middle;
        font-size: 12px;
        position: relative;
        background: #fff;
    }

    .bill-create-root .table-bill-items tbody tr:hover td {
        background-color: #fcfcfd;
    }

    .bill-create-root .table-bill-items .form-control {
        min-width: 0;
        min-height: 34px;
        font-size: 11px;
        padding: 5px 7px;
    }

    .bill-create-root .table-bill-items .line-total-display {
        white-space: nowrap;
        font-size: 12px;
    }

    .bill-create-root .table-bill-items .remove-row-btn {
        min-width: 30px;
        min-height: 30px;
    }

    .bill-create-root .table-bill-items .badge {
        font-weight: 600;
    }

    /* Product line section */
    .bill-create-root .product-section-header {
        gap: 12px;
    }

    .bill-create-root .product-section-header .btn {
        white-space: nowrap;
    }

    /* Payment and totals */
    .bill-create-root .bill-settlement-area {
        background: #f8f9fa !important;
        border-top: 1px solid var(--bill-border) !important;
    }

    .bill-create-root .settlement-card {
        border: 1px solid var(--bill-border) !important;
        box-shadow: none !important;
    }

    .bill-create-root .bill-total-card {
        border: 1px solid var(--bill-border) !important;
        box-shadow: none !important;
    }

    .bill-create-root .bill-total-card .form-control {
        width: 110px;
        max-width: 45%;
        text-align: right;
    }

    .bill-create-root .bill-total-row {
        gap: 10px;
    }

    .bill-create-root .bill-grand-total {
        padding: 9px 0;
        border-top: 1px solid var(--bill-border);
        border-bottom: 1px solid var(--bill-border);
        margin-top: 8px;
    }

    .bill-create-root .bill-grand-total .total-value {
        font-size: clamp(17px, 2vw, 21px);
    }

    .bill-create-root .bill-due-row .total-value {
        font-size: 16px;
    }

    /* Alerts */
    .bill-create-root .alert {
        border-radius: 5px;
        font-size: 13px;
    }

    /* Modal */
    #quickProductModal .modal-content {
        border: 1px solid var(--bill-border);
        border-radius: 7px;
        overflow: hidden;
    }

    #quickProductModal .modal-header,
    #quickProductModal .modal-footer {
        background: #f8f9fa;
    }

    #quickProductModal .modal-title {
        font-size: 14px;
    }

    #quickProductModal .modal-body {
        max-height: calc(100vh - 190px);
        overflow-y: auto;
    }

    /* Processing Overlay */
    #bill-global-loader {
        position: fixed;
        inset: 0;
        background: rgba(24, 29, 47, .78);
        z-index: 99999999;
        display: none;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        color: #fff;
        backdrop-filter: blur(3px);
        padding: 20px;
        text-align: center;
    }

    #bill-global-loader .spinner-border {
        width: 3.25rem;
        height: 3.25rem;
    }

    #bill-global-loader h5 {
        color: #fff;
        font-size: 16px;
    }

    #bill-global-loader p {
        max-width: 480px;
        line-height: 1.6;
    }

    /* Footer */
    .bill-create-root .bill-form-footer {
        gap: 8px;
    }

    .bill-create-root .bill-form-footer .btn {
        min-height: 36px;
    }

    /* Responsive */
    @media (max-width: 991.98px) {
        .bill-create-root .card-header {
            padding: 12px 14px;
        }

        .bill-create-root .sourcing-selector {
            width: 100%;
        }

        .bill-create-root .bill-total-card .form-control {
            width: 105px;
        }
    }

    @media (max-width: 767.98px) {
        .bill-create-root {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        .bill-create-root .bill-page-header {
            align-items: flex-start !important;
        }

        .bill-create-root .bill-page-actions {
            width: 100%;
        }

        .bill-create-root .bill-page-actions .btn {
            flex: 1 1 auto;
            justify-content: center;
        }

        .bill-create-root .card-header {
            padding: 12px;
        }

        .bill-create-root .card-body {
            padding: 12px !important;
        }

        .bill-create-root .product-section-header {
            align-items: flex-start !important;
            flex-direction: column;
        }

        .bill-create-root .product-section-header .btn {
            width: 100%;
        }

        .bill-create-root .bill-search-results {
            width: 100%;
            min-width: 0;
            max-width: calc(100vw - 48px);
        }

        .bill-create-root .bill-form-footer {
            flex-wrap: wrap;
        }

        .bill-create-root .bill-form-footer .btn {
            flex: 1 1 120px;
        }
    }

    @media (max-width: 575.98px) {
        .bill-create-root .page-heading {
            font-size: 17px;
        }

        .bill-create-root .bill-page-actions {
            display: grid;
            grid-template-columns: 1fr;
        }

        .bill-create-root .bill-page-actions .btn {
            width: 100%;
        }

        .bill-create-root .card-header {
            align-items: flex-start !important;
        }

        .bill-create-root .card-header > .d-flex {
            width: 100%;
        }

        .bill-create-root .sourcing-selector {
            width: 100%;
        }

        .bill-create-root .bill-total-card .form-control {
            width: 105px;
            max-width: 48%;
        }

        .bill-create-root .bill-total-row span {
            font-size: 11px;
        }

        .bill-create-root .bill-grand-total .total-value {
            font-size: 17px;
        }

        #quickProductModal .modal-dialog {
            margin: 10px;
        }

        #quickProductModal .modal-body {
            padding: 12px !important;
        }

        .bill-create-root .bill-form-footer {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
        }

        .bill-create-root .bill-form-footer .btn {
            width: 100%;
            padding-left: 8px;
            padding-right: 8px;
        }
    }
</style>

{{-- Processing Overlay --}}
<div id="bill-global-loader">
    <div class="spinner-border text-primary" role="status"></div>
    <h5 class="fw-bold mt-3 mb-1">Processing Purchase Bill...</h5>
    <p class="text-white-50 small mb-0">Please do not reload the page or submit again. Ledger and stock are being updated.</p>
</div>

<div class="container-fluid px-3 py-3 bill-create-root">

    {{-- Page Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between bill-page-header mb-3">
        <div>
            <h5 class="page-heading mb-0">
                <i class="fas fa-file-invoice text-primary me-2"></i>Create Purchase Bill
            </h5>
        </div>

        <div class="bill-page-actions">
            <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#quickProductModal">
                <i class="fas fa-plus-circle me-1"></i> Quick Add Product
            </button>
            <a href="{{ route('admin.purchases.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Bills
            </a>
        </div>
    </div>

    {{-- Error Banner --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3">
            <div class="d-flex align-items-center gap-2 fw-semibold">
                <i class="fas fa-circle-exclamation fa-lg"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3">
            <div class="d-flex align-items-center gap-2 fw-semibold mb-1">
                <i class="fas fa-circle-exclamation"></i> Please review the following errors:
            </div>
            <ul class="mb-0 ps-3 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('admin.purchases.store') }}" method="POST" enctype="multipart/form-data" id="purchaseBillForm">
        @csrf

        {{-- Invoice Details & Sourcing --}}
        <div class="card mb-3">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                <h6 class="section-heading">
                    <i class="fas fa-receipt text-primary me-2"></i>Invoice Details &amp; Sourcing
                </h6>

                {{-- Quick Link PO or GRN --}}
                <div class="d-flex flex-wrap align-items-center gap-2 w-auto">
                    @if(isset($purchaseOrders) && $purchaseOrders->count() > 0)
                        <div class="input-group input-group-sm sourcing-selector">
                            <select id="linkPoSelector" class="form-select form-select-sm">
                                <option value="">Link Purchase Order (PO)</option>
                                @foreach($purchaseOrders as $po)
                                    <option value="{{ $po->id }}" data-supplier="{{ $po->supplier_id }}" {{ request('po_id') == $po->id ? 'selected' : '' }}>
                                        #{{ $po->po_number }} &mdash; {{ $po->supplier->name ?? 'Vendor' }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-primary" id="pullPoBtn" title="Pull Items from PO">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    @endif

                    @if(isset($purchaseReceipts) && $purchaseReceipts->count() > 0)
                        <div class="input-group input-group-sm sourcing-selector">
                            <select id="linkGrnSelector" class="form-select form-select-sm">
                                <option value="">Link Inward GRN</option>
                                @foreach($purchaseReceipts as $grn)
                                    <option value="{{ $grn->id }}" data-supplier="{{ $grn->supplier_id }}" {{ request('grn_id') == $grn->id ? 'selected' : '' }}>
                                        #{{ $grn->receipt_number }} &mdash; {{ $grn->supplier->name ?? 'Vendor' }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-success" id="pullGrnBtn" title="Pull Items from GRN">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card-body p-3">
                <div class="row g-3">
                    <input type="hidden" name="purchase_order_id" id="purchase_order_id" value="{{ old('purchase_order_id', request('po_id')) }}">
                    <input type="hidden" name="purchase_receipt_id" id="purchase_receipt_id" value="{{ old('purchase_receipt_id', request('grn_id')) }}">

                    <div class="col-12 col-md-4">
                        <label class="form-label">Supplier / Vendor <span class="text-danger">*</span></label>
                        <select name="supplier_id" id="supplier_id" class="form-select form-select-sm" required>
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}"
                                        data-balance="{{ (float) ($supplier->current_balance ?? 0) }}"
                                        {{ old('supplier_id', request('supplier_id')) == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }} {{ $supplier->company_name ? "({$supplier->company_name})" : '' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="small mt-2 text-muted" id="supplierInfoNotice">
                            Current Ledger Due: <span class="fw-bold text-danger font-monospace" id="supplierBalanceNotice">৳0.00</span>
                        </div>
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label">Bill Date <span class="text-danger">*</span></label>
                        <input type="date" name="purchase_date" class="form-control form-control-sm" value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label">Supplier Invoice / Memo No <span class="text-danger">*</span></label>
                        <input type="text" name="supplier_invoice_no" class="form-control form-control-sm font-monospace" placeholder="INV-9042" value="{{ old('supplier_invoice_no') }}" required>
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label">Receiving Warehouse <span class="text-danger">*</span></label>
                        <select name="warehouse_id" id="warehouse_id" class="form-select form-select-sm" required>
                            <option value="">Select Warehouse</option>
                            @foreach($warehouses ?? [] as $wh)
                                <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                    {{ $wh->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label">Branch</label>
                        <select name="branch_id" class="form-select form-select-sm">
                            <option value="">HQ Branch</option>
                            @foreach($branches ?? [] as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label">Payment Terms</label>
                        <select name="payment_terms" class="form-select form-select-sm">
                            <option value="immediate" {{ old('payment_terms') === 'immediate' ? 'selected' : '' }}>Immediate / Due on Receipt</option>
                            <option value="net_15" {{ old('payment_terms') === 'net_15' ? 'selected' : '' }}>Net 15 Days</option>
                            <option value="net_30" {{ old('payment_terms', 'net_30') === 'net_30' ? 'selected' : '' }}>Net 30 Days</option>
                            <option value="net_60" {{ old('payment_terms') === 'net_60' ? 'selected' : '' }}>Net 60 Days</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label">Bill Due Date</label>
                        <input type="date" name="due_date" class="form-control form-control-sm" value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}">
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label">Invoice Scan / Challan Copy</label>
                        <input type="file" name="attachment_file" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                </div>
            </div>
        </div>

        {{-- Product Line Items --}}
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center justify-content-between product-section-header">
                <div>
                    <h6 class="section-heading">
                        <i class="fas fa-boxes-stacked text-primary me-2"></i>Product Line Items &amp; Stock Valuation
                    </h6>
                    <small class="section-description">Search by product name, ID or SKU. Rates and totals update automatically.</small>
                </div>
                <button type="button" class="btn btn-sm btn-primary" id="addItemRowBtn">
                    <i class="fas fa-plus me-1"></i> Add Product Line
                </button>
            </div>

            <div class="bill-items-scroll">
                <table class="table table-bordered align-middle mb-0 table-bill-items" id="itemsTable">
                    <thead>
                        <tr>
                            <th style="min-width: 280px;">Product Description <span class="text-danger">*</span></th>
                            <th style="width: 130px;">SKU / Code</th>
                            <th style="width: 100px;">Batch #</th>
                            <th style="width: 120px;">Expiry Date</th>
                            <th class="text-center" style="width: 80px;">Qty <span class="text-danger">*</span></th>
                            <th class="text-end" style="width: 120px;">Unit Cost (৳) <span class="text-danger">*</span></th>
                            <th class="text-end" style="width: 100px;">Discount (৳)</th>
                            <th class="text-end" style="width: 100px;">Tax (৳)</th>
                            <th class="text-end" style="width: 130px;">Line Total (৳)</th>
                            <th class="text-center" style="width: 40px;"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsContainer">
                        {{-- Row entries cloned dynamically --}}
                    </tbody>
                </table>
            </div>

            {{-- Summary & Settlement --}}
            <div class="card-body p-3 bill-settlement-area">
                <div class="row g-3 justify-content-between">

                    {{-- Spot Payment --}}
                    <div class="col-12 col-lg-7">
                        <div class="card settlement-card p-3 mb-2">
                            <h6 class="fw-bold text-dark small text-uppercase mb-3">
                                <i class="fas fa-money-bill-wave text-success me-1"></i>Spot Payment (Optional)
                            </h6>

                            <div class="row g-2">
                                <div class="col-6 col-md-4">
                                    <label class="form-label">Paid Amount (৳)</label>
                                    <input type="number" step="0.01" name="paid_amount" id="paid_amount" class="form-control form-control-sm text-end fw-bold text-success font-monospace" value="{{ old('paid_amount', 0) }}" min="0">
                                </div>

                                <div class="col-6 col-md-4">
                                    <label class="form-label">Payment Method</label>
                                    <select name="payment_method" id="payment_method" class="form-select form-select-sm">
                                        <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                                        <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                        <option value="cheque" {{ old('payment_method') === 'cheque' ? 'selected' : '' }}>Cheque</option>
                                        <option value="mobile_banking" {{ old('payment_method') === 'mobile_banking' ? 'selected' : '' }}>Mobile Banking (MFS)</option>
                                    </select>
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label">Payment Account</label>
                                    <select name="bank_account_id" id="bank_account_id" class="form-select form-select-sm">
                                        <option value="">Default Cash Account</option>
                                        @foreach($bankAccounts ?? [] as $bank)
                                            <option value="{{ $bank->id }}" {{ old('bank_account_id') == $bank->id ? 'selected' : '' }}>
                                                {{ $bank->account_name }} &mdash; {{ $bank->bank_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Bill Notes / General Terms</label>
                                    <input type="text" name="notes" class="form-control form-control-sm" placeholder="Delivery remarks, discount agreements, payment details..." value="{{ old('notes') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Invoice Calculations --}}
                    <div class="col-12 col-lg-5 col-xl-4">
                        <div class="card bill-total-card p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2 small bill-total-row">
                                <span class="text-muted">Items Subtotal:</span>
                                <span class="fw-bold text-dark font-monospace" id="displaySubtotal">৳0.00</span>
                                <input type="hidden" name="subtotal" id="hidden_subtotal" value="0.00">
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2 small bill-total-row">
                                <span class="text-muted">Global Discount (৳):</span>
                                <input type="number" step="0.01" name="discount_amount" id="global_discount" class="form-control form-control-sm font-monospace" value="{{ old('discount_amount', 0) }}" min="0">
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2 small bill-total-row">
                                <span class="text-muted">Global Tax / VAT (৳):</span>
                                <input type="number" step="0.01" name="tax_amount" id="global_tax" class="form-control form-control-sm font-monospace" value="{{ old('tax_amount', 0) }}" min="0">
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2 small bill-total-row">
                                <span class="text-muted">Shipping &amp; Freight (৳):</span>
                                <input type="number" step="0.01" name="shipping_cost" id="shipping_cost" class="form-control form-control-sm font-monospace" value="{{ old('shipping_cost', 0) }}" min="0">
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2 small bill-total-row">
                                <span class="text-muted">Rounding Adjustment (৳):</span>
                                <input type="number" step="0.01" name="round_off" id="round_off" class="form-control form-control-sm font-monospace" value="{{ old('round_off', 0) }}">
                            </div>

                            <div class="d-flex justify-content-between align-items-center bill-grand-total bill-total-row">
                                <span class="fw-bold text-dark fs-6">Grand Total:</span>
                                <span class="fw-bold text-primary font-monospace total-value" id="displayGrandTotal">৳0.00</span>
                                <input type="hidden" name="grand_total" id="grand_total" value="0.00">
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-2 small bill-due-row bill-total-row">
                                <span class="text-muted">Due Liability:</span>
                                <span class="fw-bold text-danger font-monospace total-value" id="displayDueBalance">৳0.00</span>
                                <input type="hidden" name="due_amount" id="due_amount" value="0.00">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer py-3 d-flex justify-content-end bill-form-footer">
                <a href="{{ route('admin.purchases.index') }}" class="btn btn-sm btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-sm btn-primary px-4 fw-bold" id="submitBillBtn">
                    <i class="fas fa-check-circle me-1"></i> Save &amp; Post Bill
                </button>
            </div>
        </div>
    </form>
</div>

{{-- Quick Product Modal --}}
<div class="modal fade" id="quickProductModal" tabindex="-1" aria-hidden="true" style="z-index: 109000;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-2 bg-light border-bottom">
                <h6 class="modal-title fw-bold text-dark">
                    <i class="fas fa-cube text-primary me-1"></i>Quick Add Product
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickProductForm">
                <div class="modal-body p-3">
                    <div class="mb-3">
                        <label class="form-label">Product Title <span class="text-danger">*</span></label>
                        <input type="text" id="modal_product_title" class="form-control form-control-sm" placeholder="Premium Cotton Shirt" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">SKU / Code</label>
                            <input type="text" id="modal_product_sku" class="form-control form-control-sm font-monospace" placeholder="Auto-generated if empty">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Category</label>
                            <select id="modal_product_category" class="form-select form-select-sm">
                                <option value="">Select Category</option>
                                @foreach($categories ?? [] as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name ?? $cat->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Regular Selling Price (৳) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" id="modal_regular_price" class="form-control form-control-sm font-monospace" placeholder="0.00" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Purchase / Cost Price (৳)</label>
                            <input type="number" step="0.01" id="modal_purchase_cost" class="form-control form-control-sm font-monospace" placeholder="0.00">
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2 bg-light border-top">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-sm btn-primary" id="saveQuickProductBtn">
                        <i class="fas fa-check me-1"></i> Save &amp; Select
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Dynamic Row Template --}}
<template id="rowTemplate">
    <tr class="item-row">
        <!-- 1. Product Description with Live AJAX Search -->
        <td>
            <div class="bill-search-wrapper">
                <input type="text" class="form-control form-control-sm product-search-input fw-semibold" placeholder="Search product name, ID or barcode..." autocomplete="off">
                <input type="hidden" name="items[INDEX][product_id]" class="product-id-input">
                <input type="hidden" name="items[INDEX][variant_id]" class="variant-id-input">
                <input type="hidden" name="items[INDEX][po_item_id]" class="po-item-id-input">
                <input type="hidden" name="items[INDEX][grn_item_id]" class="grn-item-id-input">
                <div class="bill-search-results"></div>
            </div>
            <div class="d-flex align-items-center gap-1 mt-1">
                <span class="badge bg-light text-muted border font-monospace current-stock-badge" style="font-size: 10px;">Stock: 0</span>
                <span class="badge bg-light text-primary border font-monospace last-cost-badge" style="font-size: 10px;">Prev Cost: ৳0.00</span>
            </div>
        </td>

        <!-- 2. SKU / Code -->
        <td>
            <input type="text" name="items[INDEX][sku]" class="form-control form-control-sm sku-input font-monospace" placeholder="Enter SKU">
            <small class="sku-feedback d-block mt-0.5" style="font-size: 10px;"></small>
        </td>

        <!-- 3. Batch # -->
        <td>
            <input type="text" name="items[INDEX][batch_no]" class="form-control form-control-sm batch-input font-monospace" placeholder="Batch #">
        </td>

        <!-- 4. Expiry Date -->
        <td>
            <input type="date" name="items[INDEX][expiry_date]" class="form-control form-control-sm expiry-input">
        </td>

        <!-- 5. Qty -->
        <td>
            <input type="number" step="any" name="items[INDEX][quantity]" class="form-control form-control-sm text-center qty-input fw-bold font-monospace" value="1" min="0.01" required>
        </td>

        <!-- 6. Unit Cost -->
        <td>
            <input type="number" step="0.01" name="items[INDEX][unit_cost]" class="form-control form-control-sm text-end cost-input font-monospace fw-semibold" value="0.00" min="0" required>
        </td>

        <!-- 7. Discount -->
        <td>
            <input type="number" step="0.01" name="items[INDEX][discount]" class="form-control form-control-sm text-end discount-input font-monospace" value="0.00" min="0">
        </td>

        <!-- 8. Tax -->
        <td>
            <input type="number" step="0.01" name="items[INDEX][tax]" class="form-control form-control-sm text-end tax-input font-monospace" value="0.00" min="0">
        </td>

        <!-- 9. Line Total -->
        <td class="text-end fw-bold text-dark font-monospace line-total-display">
            ৳0.00
        </td>

        <!-- 10. Actions -->
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-row-btn" title="Remove row">
                <i class="fas fa-trash-can"></i>
            </button>
        </td>
    </tr>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let rowIndex = 0;
    let isSubmittingBill = false;

    const container = document.getElementById('itemsContainer');
    const template = document.getElementById('rowTemplate').innerHTML;
    const addItemBtn = document.getElementById('addItemRowBtn');
    const supplierSelect = document.getElementById('supplier_id');
    const supplierBalanceSpan = document.getElementById('supplierBalanceNotice');

    const globalDiscountInput = document.getElementById('global_discount');
    const globalTaxInput = document.getElementById('global_tax');
    const shippingInput = document.getElementById('shipping_cost');
    const roundOffInput = document.getElementById('round_off');
    const paidAmountInput = document.getElementById('paid_amount');
    const billForm = document.getElementById('purchaseBillForm');
    const submitBtn = document.getElementById('submitBillBtn');
    const loaderOverlay = document.getElementById('bill-global-loader');

    // Central AJAX Endpoints
    const searchUrl = "{{ url('admin/procurement/purchase-orders/search-products-ajax') }}";
    const skuUrl = "{{ url('admin/procurement/purchase-orders/find-product-by-sku-ajax') }}";
    const quickStoreUrl = "{{ url('admin/procurement/purchase-orders/quick-store-product-ajax') }}";

    function updateSupplierBalance() {
        const selected = supplierSelect.options[supplierSelect.selectedIndex];
        const balance = parseFloat(selected ? selected.getAttribute('data-balance') : 0) || 0;
        if (supplierBalanceSpan) {
            supplierBalanceSpan.innerText = '৳' + balance.toFixed(2);
        }
    }

    function addRow(prefill = null) {
        const rowHtml = template.replace(/INDEX/g, rowIndex);
        container.insertAdjacentHTML('beforeend', rowHtml);
        const currentRow = container.lastElementChild;
        rowIndex++;

        setupRowEvents(currentRow);

        if (prefill) {
            populateRowData(currentRow, prefill);
        }

        calculateTotals();
        return currentRow;
    }

    function populateRowData(row, product) {
        row.querySelector('.product-id-input').value = product.id || product.product_id || '';
        row.querySelector('.variant-id-input').value = product.variant_id || '';
        row.querySelector('.po-item-id-input').value = product.po_item_id || '';
        row.querySelector('.grn-item-id-input').value = product.grn_item_id || '';
        row.querySelector('.product-search-input').value = product.name || product.title || product.product_name || '';
        row.querySelector('.sku-input').value = product.sku || product.product_code || '';

        const cost = parseFloat(product.purchase_cost ?? product.unit_cost ?? 0);
        row.querySelector('.cost-input').value = cost.toFixed(2);
        row.querySelector('.qty-input').value = product.quantity || 1;
        row.querySelector('.batch-input').value = product.batch_no || '';
        row.querySelector('.expiry-input').value = product.expiry_date || '';
        row.querySelector('.discount-input').value = parseFloat(product.discount || 0).toFixed(2);
        row.querySelector('.tax-input').value = parseFloat(product.tax || 0).toFixed(2);

        row.querySelector('.current-stock-badge').innerText = 'Stock: ' + (product.stock || 0);
        row.querySelector('.last-cost-badge').innerText = 'Prev Cost: ৳' + cost.toFixed(2);

        const skuFeedback = row.querySelector('.sku-feedback');
        skuFeedback.className = 'sku-feedback text-success';
        skuFeedback.innerText = '✓ Attached';

        calculateTotals();
    }

    function setupRowEvents(row) {
        const searchInput = row.querySelector('.product-search-input');
        const resultsBox = row.querySelector('.bill-search-results');
        const skuInput = row.querySelector('.sku-input');
        const skuFeedback = row.querySelector('.sku-feedback');

        const inputs = row.querySelectorAll('.qty-input, .cost-input, .discount-input, .tax-input');
        inputs.forEach(input => {
            input.addEventListener('input', calculateTotals);
        });

        let debounceTimer = null;

        searchInput.addEventListener('input', function () {
            const query = this.value.trim();
            clearTimeout(debounceTimer);

            if (query.length < 1) {
                resultsBox.style.display = 'none';
                row.classList.remove('active-search-row');
                return;
            }

            debounceTimer = setTimeout(() => {
                fetch(searchUrl + "?term=" + encodeURIComponent(query))
                    .then(res => res.json())
                    .then(res => {
                        resultsBox.innerHTML = '';
                        if (res.results && res.results.length > 0) {
                            res.results.forEach(item => {
                                const el = document.createElement('div');
                                el.className = 'bill-search-item';
                                el.innerHTML = `
                                    <div class="fw-bold text-dark" style="font-size: 12.5px;">${item.name}</div>
                                    <div class="d-flex justify-content-between small text-muted font-monospace mt-1" style="font-size: 11px;">
                                        <span>SKU: <strong>${item.sku || '—'}</strong></span>
                                        <span>Stock: ${item.stock}</span>
                                        <span class="text-primary">Selling: ৳${parseFloat(item.regular_price || 0).toFixed(2)}</span>
                                        <span class="text-success">Cost: ৳${parseFloat(item.purchase_cost || 0).toFixed(2)}</span>
                                    </div>
                                `;
                                el.addEventListener('click', function () {
                                    populateRowData(row, item);
                                    resultsBox.style.display = 'none';
                                    row.classList.remove('active-search-row');
                                });
                                resultsBox.appendChild(el);
                            });
                            row.classList.add('active-search-row');
                            resultsBox.style.display = 'block';
                        } else {
                            resultsBox.innerHTML = '<div class="p-3 text-muted small text-center fw-semibold">No matching product found.</div>';
                            row.classList.add('active-search-row');
                            resultsBox.style.display = 'block';
                        }
                    })
                    .catch(() => {
                        resultsBox.style.display = 'none';
                        row.classList.remove('active-search-row');
                    });
            }, 250);
        });

        document.addEventListener('click', function (e) {
            if (!row.contains(e.target)) {
                resultsBox.style.display = 'none';
                row.classList.remove('active-search-row');
            }
        });

        function lookupSku() {
            const skuVal = skuInput.value.trim();
            if (!skuVal) return;

            skuFeedback.className = 'sku-feedback text-muted';
            skuFeedback.innerText = 'Searching SKU...';

            fetch(skuUrl + "?sku=" + encodeURIComponent(skuVal))
                .then(res => res.json())
                .then(res => {
                    if (res.success && res.data) {
                        populateRowData(row, res.data);
                    } else {
                        skuFeedback.className = 'sku-feedback text-danger fw-bold';
                        skuFeedback.innerText = '✕ Nothing found with this SKU';
                    }
                })
                .catch(() => {
                    skuFeedback.className = 'sku-feedback text-danger';
                    skuFeedback.innerText = '✕ Error searching SKU';
                });
        }

        skuInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                lookupSku();
            }
        });
        skuInput.addEventListener('blur', function () {
            if (this.value.trim() && !row.querySelector('.product-id-input').value) {
                lookupSku();
            }
        });

        row.querySelector('.remove-row-btn').addEventListener('click', function () {
            if (container.children.length > 1) {
                row.remove();
                calculateTotals();
            } else {
                alert('A purchase bill must have at least one product line.');
            }
        });
    }

    function calculateTotals() {
        let itemsSubtotal = 0;

        const rows = container.querySelectorAll('.item-row');
        rows.forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const cost = parseFloat(row.querySelector('.cost-input').value) || 0;
            const discount = parseFloat(row.querySelector('.discount-input').value) || 0;
            const tax = parseFloat(row.querySelector('.tax-input').value) || 0;

            const lineTotal = Math.max(0, (qty * cost) - discount + tax);
            row.querySelector('.line-total-display').innerText = '৳' + lineTotal.toFixed(2);
            itemsSubtotal += lineTotal;
        });

        const globalDiscount = parseFloat(globalDiscountInput.value) || 0;
        const globalTax = parseFloat(globalTaxInput.value) || 0;
        const shipping = parseFloat(shippingInput.value) || 0;
        const roundOff = parseFloat(roundOffInput.value) || 0;
        const paidAmount = parseFloat(paidAmountInput.value) || 0;

        const grandTotal = Math.max(0, itemsSubtotal - globalDiscount + globalTax + shipping + roundOff);
        const dueBalance = Math.max(0, grandTotal - paidAmount);

        document.getElementById('displaySubtotal').innerText = '৳' + itemsSubtotal.toFixed(2);
        document.getElementById('hidden_subtotal').value = itemsSubtotal.toFixed(2);

        document.getElementById('displayGrandTotal').innerText = '৳' + grandTotal.toFixed(2);
        document.getElementById('grand_total').value = grandTotal.toFixed(2);

        document.getElementById('displayDueBalance').innerText = '৳' + dueBalance.toFixed(2);
        document.getElementById('due_amount').value = dueBalance.toFixed(2);
    }

    billForm.addEventListener('submit', function (e) {
        if (isSubmittingBill) {
            e.preventDefault();
            return false;
        }

        if (!supplierSelect.value) {
            e.preventDefault();
            alert('Please select a Supplier / Vendor first.');
            supplierSelect.focus();
            return false;
        }

        const validProducts = container.querySelectorAll('.product-id-input');
        let hasValid = false;
        validProducts.forEach(inp => {
            if (inp.value && inp.value.trim() !== '') {
                hasValid = true;
            }
        });

        if (!hasValid) {
            e.preventDefault();
            alert('Please search and select at least one product.');
            return false;
        }

        calculateTotals();

        isSubmittingBill = true;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Posting Bill...';

        if (loaderOverlay) {
            loaderOverlay.style.display = 'flex';
        }
    });

    document.getElementById('quickProductForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const btn = document.getElementById('saveQuickProductBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';

        const payload = {
            title: document.getElementById('modal_product_title').value,
            sku: document.getElementById('modal_product_sku').value,
            category_id: document.getElementById('modal_product_category').value,
            regular_price: document.getElementById('modal_regular_price').value,
            purchase_cost: document.getElementById('modal_purchase_cost').value,
        };

        fetch(quickStoreUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(res => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check me-1"></i> Save &amp; Select';

            if (res.success && res.data) {
                let targetRow = null;
                const rows = container.querySelectorAll('.item-row');
                for (let r of rows) {
                    if (!r.querySelector('.product-id-input').value) {
                        targetRow = r;
                        break;
                    }
                }
                if (!targetRow) {
                    targetRow = addRow();
                }

                populateRowData(targetRow, res.data);

                const modalEl = document.getElementById('quickProductModal');
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) modalInstance.hide();
                } else {
                    modalEl.classList.remove('show');
                    modalEl.style.display = 'none';
                }
                document.getElementById('quickProductForm').reset();
            } else {
                alert(res.message || 'Error saving product');
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check me-1"></i> Save &amp; Select';
            alert('Server connection failed.');
        });
    });

    addItemBtn.addEventListener('click', () => addRow());
    supplierSelect.addEventListener('change', updateSupplierBalance);

    [globalDiscountInput, globalTaxInput, shippingInput, roundOffInput, paidAmountInput].forEach(elem => {
        if (elem) elem.addEventListener('input', calculateTotals);
    });

    // PO Pull Handler
    const pullPoBtn = document.getElementById('pullPoBtn');
    const linkPoSelector = document.getElementById('linkPoSelector');
    if (pullPoBtn && linkPoSelector) {
        pullPoBtn.addEventListener('click', function () {
            const poId = linkPoSelector.value;
            if (!poId) return alert('Select a Purchase Order first.');

            const opt = linkPoSelector.options[linkPoSelector.selectedIndex];
            const supplierId = opt.getAttribute('data-supplier');
            if (supplierId) {
                supplierSelect.value = supplierId;
                updateSupplierBalance();
            }
            document.getElementById('purchase_order_id').value = poId;

            fetch(`{{ url('admin/procurement/purchases/po-items') }}/${poId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(res => {
                if (res.success && res.data && res.data.length > 0) {
                    container.innerHTML = '';
                    rowIndex = 0;
                    res.data.forEach(item => {
                        addRow({
                            product_id: item.product_id,
                            variant_id: item.variant_id,
                            po_item_id: item.id,
                            name: item.product ? item.product.name : item.product_name,
                            sku: item.sku,
                            unit_cost: item.unit_cost,
                            quantity: item.quantity
                        });
                    });
                } else {
                    alert('No items found in selected Purchase Order.');
                }
            })
            .catch(() => alert('Failed to fetch Purchase Order items.'));
        });
    }

    // Default initialization
    updateSupplierBalance();
    addRow();
});
</script>
@endsection