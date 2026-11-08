
@extends('layouts.admin')

@section('title', 'Create Purchase Order')

@section('admin_content')
<style>
    .po-create-root {
        --po-border: #e6e9ef;
        --po-muted: #667085;
        --po-heading: #344054;
        --po-surface: #ffffff;
        --po-soft-bg: #f8f9fb;
        --po-radius: 8px;
        min-width: 0;
    }

    .po-create-root .card,
    .po-create-root .modal-content {
        border: 1px solid var(--po-border) !important;
        border-radius: var(--po-radius) !important;
        box-shadow: 0 2px 8px rgba(31, 41, 55, .045) !important;
    }

    .po-create-root .card-header,
    .po-create-root .card-footer {
        background: #fff;
        border-color: var(--po-border) !important;
    }

    .po-create-root .card-header {
        padding: 15px 18px;
    }

    .po-create-root .card-body {
        padding: 18px;
    }

    .po-create-root .card-footer {
        padding: 14px 18px;
    }

    .po-create-root .form-control,
    .po-create-root .form-select,
    .po-create-root .input-group-text {
        min-height: 35px;
        border-color: #dfe3e8;
        border-radius: 6px !important;
        font-size: 12.5px;
    }

    .po-create-root textarea.form-control {
        min-height: auto;
    }

    .po-create-root .form-control:focus,
    .po-create-root .form-select:focus {
        border-color: var(--bs-primary) !important;
        box-shadow: 0 0 0 .15rem rgba(var(--bs-primary-rgb), .12) !important;
    }

    .po-create-root .form-label {
        color: #475467;
        font-size: 12px;
    }

    .po-create-root .btn {
        border-radius: 6px !important;
        font-size: 12px;
        font-weight: 600;
    }

    .po-create-root .badge {
        border-radius: 5px !important;
    }

    .po-create-root .po-page-title {
        font-size: 17px;
        line-height: 1.5;
    }

    .po-create-root .po-page-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .po-create-root .po-page-actions .btn {
        min-height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 7px 12px;
    }

    .po-create-root .po-section-title {
        font-size: 13px;
        line-height: 1.5;
    }

    .po-create-root .po-section-subtitle {
        font-size: 11.5px;
        line-height: 1.5;
    }

    .po-create-root .po-supplier-notice {
        font-size: 11px;
    }

    .po-create-root .po-search-wrapper {
        position: relative;
        min-width: 0;
    }

    .po-create-root .po-search-results {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        width: max(100%, min(420px, calc(100vw - 40px)));
        max-width: min(480px, calc(100vw - 40px));
        z-index: 1055;
        background: #fff;
        border: 1px solid #dfe3e8;
        box-shadow: 0 10px 28px rgba(15, 23, 42, .14);
        border-radius: 7px !important;
        max-height: 280px;
        overflow-y: auto;
        display: none;
    }

    .po-create-root .po-search-item {
        padding: 10px 12px;
        cursor: pointer;
        border-bottom: 1px solid #eef0f3;
        background: #fff;
        transition: background .15s ease;
    }

    .po-create-root .po-search-item:last-child {
        border-bottom: 0;
    }

    .po-create-root .po-search-item:hover,
    .po-create-root .po-search-item.active {
        background-color: #f2f6fc;
    }

    .po-create-root .po-search-item > div:first-child {
        overflow-wrap: anywhere;
    }

    .po-create-root .table-responsive {
        width: 100%;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
    }

    .po-create-root .table-po-items {
        width: 100%;
        min-width: 1120px;
        margin-bottom: 0;
        border-color: var(--po-border);
    }

    .po-create-root .table-po-items th {
        padding: 11px 9px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .45px;
        background-color: #f8f9fb;
        border-bottom: 1px solid var(--po-border);
        color: #667085;
        white-space: normal;
        vertical-align: middle;
        line-height: 1.5;
    }

    .po-create-root .table-po-items td {
        padding: 9px 7px;
        vertical-align: middle;
        font-size: 12px;
        position: relative;
        background: #fff;
    }

    .po-create-root .table-po-items tbody tr:hover td {
        background-color: #fbfcfe;
    }

    .po-create-root .table-po-items tbody tr.active-search-row {
        position: relative;
        z-index: 10;
    }

    .po-create-root .table-po-items .form-control,
    .po-create-root .table-po-items .form-select {
        min-height: 33px;
        font-size: 11.5px;
    }

    .po-create-root .table-po-items .form-check {
        min-height: auto;
        padding-left: 1.35rem;
    }

    .po-create-root .table-po-items .form-check-input {
        margin-top: .15rem;
    }

    .po-create-root .table-po-items .form-check-label {
        line-height: 1.4;
    }

    .po-create-root .table-po-items .current-stock-badge,
    .po-create-root .table-po-items .last-cost-badge {
        font-size: 10px !important;
        font-weight: 600;
    }

    .po-create-root .table-po-items .line-total-display {
        white-space: nowrap;
    }

    .po-create-root .po-totals-card {
        border-radius: 7px !important;
        box-shadow: none !important;
    }

    .po-create-root .po-total-row {
        line-height: 1.6;
    }

    .po-create-root .po-grand-total {
        font-size: 19px;
    }

    .po-create-root .po-submit-btn {
        min-height: 38px;
        padding-left: 20px !important;
        padding-right: 20px !important;
    }

    .po-create-root .alert {
        border-radius: 7px;
        font-size: 12px;
    }

    .po-create-root .alert ul {
        line-height: 1.7;
    }

    .po-create-root .po-modal .modal-content {
        overflow: hidden;
    }

    .po-create-root .po-modal .modal-header,
    .po-create-root .po-modal .modal-footer {
        padding: 13px 17px;
        background: #f8f9fb;
        border-color: var(--po-border);
    }

    .po-create-root .po-modal .modal-body {
        padding: 18px;
    }

    .po-create-root .po-modal .modal-title {
        font-size: 14px;
    }

    .po-create-root .po-modal .form-label {
        margin-bottom: 6px;
        font-weight: 600;
    }

    .po-create-root .po-modal .btn-close {
        font-size: 11px;
    }

    /* Full-screen Loading Overlay */
    #po-global-loader {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, .78);
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

    #po-global-loader .spinner-border {
        width: 3.25rem;
        height: 3.25rem;
    }

    #po-global-loader h5 {
        font-size: 17px;
    }

    #po-global-loader p {
        font-size: 12px;
        max-width: 340px;
        line-height: 1.6;
    }

    @media (max-width: 767.98px) {
        .po-create-root {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        .po-create-root .po-page-header {
            align-items: flex-start !important;
        }

        .po-create-root .po-page-heading {
            width: 100%;
        }

        .po-create-root .po-page-actions {
            width: 100%;
        }

        .po-create-root .po-page-actions .btn {
            flex: 1 1 auto;
        }

        .po-create-root .card-header {
            padding: 13px 14px;
        }

        .po-create-root .card-body {
            padding: 14px;
        }

        .po-create-root .card-footer {
            padding: 13px 14px;
        }

        .po-create-root .po-section-title {
            font-size: 12px;
        }

        .po-create-root .po-section-subtitle {
            font-size: 10.5px;
        }

        .po-create-root .po-item-header {
            align-items: flex-start !important;
        }

        .po-create-root .po-totals-card {
            max-width: 100%;
        }
    }

    @media (max-width: 575.98px) {
        .po-create-root .po-page-title {
            font-size: 15px;
        }

        .po-create-root .po-page-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        .po-create-root .po-page-actions .btn {
            width: 100%;
            min-height: 38px;
            padding: 7px 8px;
            font-size: 11px;
        }

        .po-create-root .po-page-actions .btn:first-child {
            grid-column: 1 / -1;
        }

        .po-create-root .po-item-header {
            flex-direction: column;
        }

        .po-create-root .po-item-header > div:last-child {
            width: 100%;
        }

        .po-create-root .po-item-header > div:last-child .btn {
            width: 100%;
        }

        .po-create-root .po-submit-btn {
            flex: 1;
        }

        .po-create-root .po-footer-actions {
            width: 100%;
        }

        .po-create-root .po-footer-actions .btn {
            flex: 1;
        }

        .po-create-root .po-grand-total {
            font-size: 17px;
        }

        .po-create-root .po-modal .modal-dialog {
            margin: 10px;
        }
    }
</style>

{{-- Processing Overlay --}}
<div id="po-global-loader">
    <div class="spinner-border text-primary" role="status"></div>
    <h5 class="fw-bold text-white mt-3 mb-1">Creating Purchase Order...</h5>
    <p class="text-white-50 small mb-0">Please do not reload the page or click again. Your order is being processed.</p>
</div>

<div class="container-fluid px-3 py-3 po-create-root">

    {{-- Page Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4 po-page-header">
        <div class="po-page-heading">
            <h5 class="fw-bold text-dark mb-0 po-page-title">
                <i class="fas fa-file-signature text-primary me-2"></i>Create Purchase Order
            </h5>
            <small class="text-muted">Prepare a purchase order and manage supplier pricing.</small>
        </div>

        <div class="po-page-actions">
            <button type="button" class="btn btn-sm btn-outline-success fw-semibold" data-bs-toggle="modal" data-bs-target="#quickProductModal">
                <i class="fas fa-plus-circle me-1"></i> Add New Product in Website
            </button>
            <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Orders
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
                <i class="fas fa-circle-exclamation"></i> Please correct the following issues:
            </div>
            <ul class="mb-0 ps-3 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('admin.purchase-orders.store') }}" method="POST" id="purchaseOrderForm">
        @csrf

        {{-- Order Specifications --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0 text-dark po-section-title">
                    <i class="fas fa-file-contract text-primary me-2"></i>Order Specifications &amp; Vendor Selection
                </h6>
            </div>

            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold mb-1">Supplier / Vendor <span class="text-danger">*</span></label>
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
                        <div class="po-supplier-notice mt-2 text-muted" id="supplierInfoNotice">
                            Current Ledger Due:
                            <span class="fw-bold text-danger font-monospace" id="supplierBalanceNotice">৳0.00</span>
                        </div>
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label fw-semibold mb-1">PO Number <span class="text-danger">*</span></label>
                        <input type="text" name="po_number" id="po_number" class="form-control form-control-sm font-monospace" value="{{ old('po_number', 'PO-' . date('Ymd') . '-' . strtoupper(Str::random(4))) }}" required>
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label fw-semibold mb-1">Order Date <span class="text-danger">*</span></label>
                        <input type="date" name="order_date" id="order_date" class="form-control form-control-sm" value="{{ old('order_date', date('Y-m-d')) }}" required>
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label fw-semibold mb-1">Expected Delivery</label>
                        <input type="date" name="delivery_date" id="delivery_date" class="form-control form-control-sm" value="{{ old('delivery_date', date('Y-m-d', strtotime('+7 days'))) }}">
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label fw-semibold mb-1">Receiving Warehouse</label>
                        <select name="warehouse_id" id="warehouse_id" class="form-select form-select-sm">
                            <option value="">Default Warehouse</option>
                            @foreach($warehouses ?? [] as $wh)
                                <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                    {{ $wh->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Ordered Line Items --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3 po-item-header">
                <div>
                    <h6 class="fw-bold mb-1 text-dark po-section-title">
                        <i class="fas fa-boxes-stacked text-primary me-2"></i>Ordered Line Items &amp; Pricing
                    </h6>
                    <small class="text-muted po-section-subtitle">Search products by Name, ID or SKU. Rates and totals are calculated automatically.</small>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-primary" id="addItemRowBtn">
                        <i class="fas fa-plus me-1"></i> Add Item Line
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 table-po-items" id="itemsTable">
                    <thead>
                        <tr>
                            <th style="min-width: 290px;">Product (Search Name, ID or Barcode) <span class="text-danger">*</span></th>
                            <th style="width: 140px;">SKU / Code</th>
                            <th class="text-center" style="width: 85px;">Qty <span class="text-danger">*</span></th>
                            <th class="text-end" style="width: 130px;">Purchase Rate (৳) <span class="text-danger">*</span></th>
                            <th style="width: 175px;">Supplier Discount</th>
                            <th class="text-end" style="width: 135px;">Selling Price (৳)</th>
                            <th class="text-end" style="width: 130px;">Line Total (৳)</th>
                            <th class="text-center" style="width: 40px;"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsContainer">
                    </tbody>
                </table>
            </div>

            {{-- Order Notes & Totals --}}
            <div class="card-body p-3 bg-light border-top">
                <div class="row g-3 justify-content-between">
                    <div class="col-12 col-lg-7">
                        <label class="form-label small fw-semibold text-secondary text-uppercase mb-2">Notes / Instructions</label>
                        <textarea name="notes" rows="3" class="form-control form-control-sm" placeholder="Additional logistics remarks or instructions...">{{ old('notes') }}</textarea>
                    </div>

                    <div class="col-12 col-lg-5 col-xl-4">
                        <div class="card border p-3 shadow-none bg-white po-totals-card">
                            <div class="d-flex justify-content-between mb-2 small po-total-row">
                                <span class="text-muted">Items Subtotal:</span>
                                <span class="fw-bold text-dark font-monospace" id="displaySubtotal">৳0.00</span>
                                <input type="hidden" name="subtotal" id="hidden_subtotal" value="0.00">
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2 small po-total-row gap-2">
                                <span class="text-muted">Overall Order Discount (৳):</span>
                                <input type="number" step="0.01" name="discount_amount" id="global_discount" class="form-control form-control-sm text-end font-monospace" style="width: 110px;" value="{{ old('discount_amount', 0) }}" min="0">
                            </div>

                            <hr class="my-2">

                            <div class="d-flex justify-content-between align-items-center gap-2">
                                <span class="fw-bold text-dark">Payable Grand Total:</span>
                                <span class="fw-bold text-primary font-monospace po-grand-total" id="displayGrandTotal">৳0.00</span>
                                <input type="hidden" name="grand_total" id="hidden_grand_total" value="0.00">
                                <input type="hidden" name="total_amount" id="hidden_total_amount" value="0.00">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white border-top d-flex flex-wrap justify-content-end gap-2 po-footer-actions">
                <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-sm btn-light border">Cancel</a>
                <button type="submit" class="btn btn-sm btn-primary px-4 fw-bold po-submit-btn" id="submitPoBtn">
                    <i class="fas fa-check-circle me-1"></i> Save &amp; Generate PO
                </button>
            </div>
        </div>
    </form>
</div>

{{-- Quick Product Modal --}}
<div class="modal fade po-modal" id="quickProductModal" tabindex="-1" aria-hidden="true" style="z-index: 109000;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom">
                <h6 class="modal-title fw-bold text-dark">
                    <i class="fas fa-cube text-primary me-2"></i>Quick Add New Product to Website
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="quickProductForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Product Title <span class="text-danger">*</span></label>
                        <input type="text" id="modal_product_title" class="form-control form-control-sm" placeholder="Premium Cotton Shirt" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">SKU / Code</label>
                            <input type="text" id="modal_product_sku" class="form-control form-control-sm font-monospace" placeholder="Auto generated if empty">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Category</label>
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
                            <label class="form-label small fw-semibold">Regular Selling Price (৳) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" id="modal_regular_price" class="form-control form-control-sm font-monospace" placeholder="0.00" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Purchase / Cost Price (৳)</label>
                            <input type="number" step="0.01" id="modal_purchase_cost" class="form-control form-control-sm font-monospace" placeholder="0.00">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-sm btn-primary" id="saveQuickProductBtn">
                        <i class="fas fa-check me-1"></i> Save &amp; Select
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<template id="rowTemplate">
    <tr class="item-row">
        <td>
            <div class="po-search-wrapper">
                <input type="text" class="form-control form-control-sm product-search-input fw-semibold" placeholder="Search product name, ID or barcode..." autocomplete="off">
                <input type="hidden" name="items[INDEX][product_id]" class="product-id-input">
                <input type="hidden" name="items[INDEX][variant_id]" class="variant-id-input">
                <div class="po-search-results"></div>
            </div>
            <div class="d-flex align-items-center gap-1 mt-2 flex-wrap">
                <span class="badge bg-light text-muted border font-monospace current-stock-badge" style="font-size: 10px;">Stock: 0</span>
                <span class="badge bg-light text-primary border font-monospace last-cost-badge" style="font-size: 10px;">Prev Cost: ৳0.00</span>
            </div>
        </td>

        <td>
            <input type="text" name="items[INDEX][sku]" class="form-control form-control-sm sku-input font-monospace" placeholder="Enter SKU">
            <small class="sku-feedback d-block mt-1" style="font-size: 10px;"></small>
        </td>

        <td>
            <input type="number" step="any" name="items[INDEX][quantity]" class="form-control form-control-sm text-center qty-input fw-bold font-monospace" value="1" min="0.01" required>
        </td>

        <td>
            <input type="number" step="0.01" name="items[INDEX][unit_cost]" class="form-control form-control-sm text-end cost-input font-monospace fw-semibold" value="0.00" min="0" required>
        </td>

        <td>
            <div class="input-group input-group-sm">
                <input type="number" step="any" class="form-control form-control-sm text-end discount-val-input font-monospace" value="0.00" min="0">
                <select class="form-select form-select-sm discount-type-select" style="max-width: 60px;">
                    <option value="flat">৳</option>
                    <option value="percent">%</option>
                </select>
            </div>
            <input type="hidden" name="items[INDEX][discount]" class="discount-hidden-input" value="0.00">
        </td>

        <td>
            <input type="number" step="0.01" name="items[INDEX][regular_price]" class="form-control form-control-sm text-end regular-price-input font-monospace" value="0.00" min="0" title="Store regular price">
            <div class="form-check mt-2 mb-0" style="font-size: 10px;">
                <input class="form-check-input sync-price-check" type="checkbox" name="items[INDEX][update_product_price]" value="1" id="syncPrice_INDEX">
                <label class="form-check-label text-muted" for="syncPrice_INDEX">Sync to Store</label>
            </div>
        </td>

        <td class="text-end fw-bold text-dark font-monospace line-total-display">
            ৳0.00
        </td>

        <td class="text-center">
            <button type="button" class="btn btn-sm btn-link text-danger p-1 remove-row-btn" title="Remove row">
                <i class="fas fa-trash-can"></i>
            </button>
        </td>
    </tr>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let rowIndex = 0;
    let isSubmittingOrder = false;

    const container = document.getElementById('itemsContainer');
    const template = document.getElementById('rowTemplate').innerHTML;
    const addItemBtn = document.getElementById('addItemRowBtn');
    const supplierSelect = document.getElementById('supplier_id');
    const supplierBalanceSpan = document.getElementById('supplierBalanceNotice');
    const globalDiscountInput = document.getElementById('global_discount');
    const poForm = document.getElementById('purchaseOrderForm');
    const submitBtn = document.getElementById('submitPoBtn');
    const loaderOverlay = document.getElementById('po-global-loader');

    const searchUrl = "{{ url('admin/procurement/purchase-orders/search-products-ajax') }}";
    const skuUrl = "{{ url('admin/procurement/purchase-orders/find-product-by-sku-ajax') }}";
    const quickStoreUrl = "{{ url('admin/procurement/purchase-orders/quick-store-product-ajax') }}";

    function updateSupplierBalance() {
        const selected = supplierSelect.options[supplierSelect.selectedIndex];
        const balance = parseFloat(selected ? selected.getAttribute('data-balance') : 0) || 0;
        supplierBalanceSpan.innerText = '৳' + balance.toFixed(2);
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
        row.querySelector('.product-id-input').value = product.id || '';
        row.querySelector('.variant-id-input').value = product.variant_id || '';
        row.querySelector('.product-search-input').value = product.name || product.title || '';
        row.querySelector('.sku-input').value = product.sku || product.product_code || '';

        const cost = parseFloat(product.purchase_cost || 0);
        const regPrice = parseFloat(product.regular_price || 0);

        row.querySelector('.cost-input').value = cost.toFixed(2);
        row.querySelector('.regular-price-input').value = regPrice.toFixed(2);

        row.querySelector('.current-stock-badge').innerText = 'Stock: ' + (product.stock || 0);
        row.querySelector('.last-cost-badge').innerText = 'Prev Cost: ৳' + cost.toFixed(2);

        const skuFeedback = row.querySelector('.sku-feedback');
        skuFeedback.className = 'sku-feedback text-success';
        skuFeedback.innerText = '✓ Attached';

        row.querySelector('.discount-val-input').value = '0.00';
        row.querySelector('.discount-hidden-input').value = '0.00';

        calculateTotals();
    }

    function setupRowEvents(row) {
        const searchInput = row.querySelector('.product-search-input');
        const resultsBox = row.querySelector('.po-search-results');
        const skuInput = row.querySelector('.sku-input');
        const skuFeedback = row.querySelector('.sku-feedback');

        const qtyInput = row.querySelector('.qty-input');
        const costInput = row.querySelector('.cost-input');
        const discValInput = row.querySelector('.discount-val-input');
        const discTypeSelect = row.querySelector('.discount-type-select');
        const hiddenDiscount = row.querySelector('.discount-hidden-input');

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
                                el.className = 'po-search-item';
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
                        skuFeedback.innerText = '✕ No product found with this SKU';
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

        function calculateLineDiscount() {
            const qty = parseFloat(qtyInput.value) || 0;
            const cost = parseFloat(costInput.value) || 0;
            const lineSub = qty * cost;
            const val = parseFloat(discValInput.value) || 0;
            const type = discTypeSelect.value;

            let discountTk = 0;
            if (type === 'percent') {
                discountTk = (lineSub * val) / 100;
            } else {
                discountTk = val;
            }
            hiddenDiscount.value = Math.min(lineSub, discountTk).toFixed(2);
            calculateTotals();
        }

        qtyInput.addEventListener('input', calculateLineDiscount);
        costInput.addEventListener('input', calculateLineDiscount);
        discValInput.addEventListener('input', calculateLineDiscount);
        discTypeSelect.addEventListener('change', calculateLineDiscount);

        row.querySelector('.remove-row-btn').addEventListener('click', function () {
            if (container.children.length > 1) {
                row.remove();
                calculateTotals();
            } else {
                alert('A purchase order must include at least one item line.');
            }
        });
    }

    function calculateTotals() {
        let itemsSubtotal = 0;
        let totalItemsDiscount = 0;

        const rows = container.querySelectorAll('.item-row');
        rows.forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const cost = parseFloat(row.querySelector('.cost-input').value) || 0;
            const discount = parseFloat(row.querySelector('.discount-hidden-input').value) || 0;

            const baseTotal = qty * cost;
            const lineTotal = Math.max(0, baseTotal - discount);

            row.querySelector('.line-total-display').innerText = '৳' + lineTotal.toFixed(2);
            itemsSubtotal += baseTotal;
            totalItemsDiscount += discount;
        });

        const globalDiscount = parseFloat(globalDiscountInput.value) || 0;
        const netGrandTotal = Math.max(0, itemsSubtotal - totalItemsDiscount - globalDiscount);

        document.getElementById('displaySubtotal').innerText = '৳' + itemsSubtotal.toFixed(2);
        document.getElementById('displayGrandTotal').innerText = '৳' + netGrandTotal.toFixed(2);

        document.getElementById('hidden_subtotal').value = itemsSubtotal.toFixed(2);
        document.getElementById('hidden_grand_total').value = netGrandTotal.toFixed(2);
        document.getElementById('hidden_total_amount').value = netGrandTotal.toFixed(2);
    }

    // Single Submit & Feedback Blocker
    poForm.addEventListener('submit', function (e) {
        if (isSubmittingOrder) {
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
            alert('Please search for and select at least one product.');
            return false;
        }

        calculateTotals();

        // Lock form from duplicate clicks and show full screen processing overlay
        isSubmittingOrder = true;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Generating Order...';

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
            alert('Unable to connect to the server.');
        });
    });

    addItemBtn.addEventListener('click', () => addRow());
    supplierSelect.addEventListener('change', updateSupplierBalance);
    globalDiscountInput.addEventListener('input', calculateTotals);

    updateSupplierBalance();
    addRow();
});
</script>
@endsection
