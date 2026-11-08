@extends('layouts.admin')

@section('title', 'Record Supplier Product Exchange / Swap')

@section('admin_content')
<style>
    .exchange-page {
        --exchange-primary: #2878c7;
        --exchange-border: #e8edf3;
        --exchange-muted: #718096;
        color: #334155;
        font-size: 13px;
    }

    .exchange-page .page-heading {
        color: #172b4d;
        font-size: 19px;
        font-weight: 700;
        letter-spacing: -.35px;
    }

    .exchange-page .breadcrumb {
        font-size: 12px;
    }

    .exchange-page .breadcrumb-item a {
        color: #64748b;
        text-decoration: none;
    }

    .exchange-page .breadcrumb-item.active {
        color: #94a3b8;
    }

    .exchange-page .exchange-card {
        border: 1px solid var(--exchange-border) !important;
        border-radius: 12px !important;
        overflow: hidden;
        box-shadow: 0 3px 14px rgba(15, 23, 42, .045) !important;
        background: #fff;
    }

    .exchange-page .exchange-card > .card-header {
        min-height: 55px;
        padding: 14px 18px;
        background: #fff;
        border-bottom: 1px solid var(--exchange-border);
    }

    .exchange-page .exchange-card > .card-body {
        padding: 20px;
    }

    .exchange-page .section-title {
        font-size: 13.5px;
        font-weight: 700;
        color: #26364b;
        margin: 0;
    }

    .exchange-page .section-subtitle {
        display: block;
        margin-top: 4px;
        font-size: 11.5px;
        color: #8794a5;
        line-height: 1.5;
    }

    .exchange-page .form-label {
        color: #475569;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 6px;
    }

    .exchange-page .form-control,
    .exchange-page .form-select {
        min-height: 36px;
        border-color: #dfe5ed;
        border-radius: 7px;
        color: #334155;
        font-size: 12.5px;
        box-shadow: none;
    }

    .exchange-page .form-control:focus,
    .exchange-page .form-select:focus {
        border-color: #80b6e8;
        box-shadow: 0 0 0 3px rgba(40, 120, 199, .09);
    }

    .exchange-page .form-control::placeholder {
        color: #a0aab8;
    }

    .exchange-page .btn {
        border-radius: 7px;
        font-size: 12px;
        font-weight: 600;
        padding: 7px 13px;
    }

    .exchange-page .btn-info {
        background: #2388b8;
        border-color: #2388b8;
    }

    .exchange-page .btn-info:hover {
        background: #19769f;
        border-color: #19769f;
    }

    .exchange-page .balance-cue {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin-top: 7px;
        padding: 4px 8px;
        border-radius: 6px;
        background: #fff5f5;
        color: #dc5555;
        font-size: 11.5px;
    }

    .exchange-page .table-responsive {
        border-radius: 0 0 10px 10px;
    }

    .exchange-page .exchange-table {
        min-width: 1050px;
        margin-bottom: 0;
        font-size: 12px;
    }

    .exchange-page .exchange-table thead th {
        padding: 12px 10px;
        background: #f7f9fc;
        color: #64748b;
        border-color: #e8edf3;
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .35px;
        text-transform: uppercase;
        white-space: nowrap;
        vertical-align: middle;
    }

    .exchange-page .exchange-table tbody td {
        padding: 10px;
        border-color: #edf0f4;
        vertical-align: middle;
    }

    .exchange-page .exchange-table tbody tr:hover {
        background: #fbfdff;
    }

    .exchange-page .exchange-table .form-control,
    .exchange-page .exchange-table .form-select {
        min-width: 75px;
        min-height: 33px;
        font-size: 11.5px;
        padding: 5px 8px;
    }

    .exchange-page .exchange-table .product-search-input {
        min-width: 190px;
    }

    .exchange-page .exchange-table .product-name-display {
        font-size: 10.5px;
        color: #64748b !important;
        padding-left: 2px;
        overflow-wrap: anywhere;
    }

    .exchange-page .table-total {
        padding: 12px 18px;
        background: #f8fafc;
        border-top: 1px solid var(--exchange-border);
    }

    .exchange-page .table-total-label {
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .3px;
        text-transform: uppercase;
    }

    .exchange-page .table-total-value {
        font-size: 16px;
        font-weight: 750;
    }

    .exchange-page .logic-panel {
        height: 100%;
        padding: 17px;
        border: 1px solid #e3edf5;
        border-radius: 10px;
        background: #f7fbff;
    }

    .exchange-page .logic-panel-title {
        color: #294a69;
        font-size: 12.5px;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .exchange-page .logic-panel ul {
        padding-left: 18px;
        margin-bottom: 0;
        color: #64748b;
        font-size: 11.5px;
        line-height: 1.8;
    }

    .exchange-page .summary-card {
        height: 100%;
        padding: 17px;
        border: 1px solid var(--exchange-border);
        border-radius: 10px;
        background: #fff;
    }

    .exchange-page .summary-line {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 10px;
        font-size: 12px;
    }

    .exchange-page .summary-line:last-child {
        margin-bottom: 0;
    }

    .exchange-page .summary-label {
        color: #718096;
    }

    .exchange-page .summary-value {
        font-weight: 700;
        white-space: nowrap;
    }

    .exchange-page .differential-box {
        padding-top: 12px;
        margin-top: 5px;
        border-top: 1px dashed #dce3eb;
    }

    .exchange-page .differential-box #differentialLabel {
        color: #26364b;
        font-size: 12.5px;
        font-weight: 700;
    }

    .exchange-page .differential-box #displayNetDifferential {
        font-size: 19px;
    }

    .exchange-page .form-footer {
        padding: 15px 20px;
        border-top: 1px solid var(--exchange-border);
        background: #fff;
    }

    .exchange-page .required-mark {
        color: #e05252;
    }

    .exchange-page .remove-row-btn {
        width: 30px;
        height: 30px;
        border-radius: 6px;
        background: #fff5f5;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
    }

    .exchange-page .remove-row-btn:hover {
        background: #ffe5e5;
    }

    .exchange-page .alert {
        border-radius: 9px;
        font-size: 12.5px;
    }

    @media (max-width: 767.98px) {
        .exchange-page .page-heading {
            font-size: 16px;
        }

        .exchange-page .exchange-card > .card-header {
            padding: 13px 14px;
        }

        .exchange-page .exchange-card > .card-body {
            padding: 15px;
        }

        .exchange-page .section-header {
            align-items: flex-start !important;
            flex-direction: column;
            gap: 12px;
        }

        .exchange-page .section-header .btn {
            width: 100%;
        }

        .exchange-page .table-total {
            padding: 12px 14px;
            flex-wrap: wrap;
        }

        .exchange-page .form-footer {
            padding: 14px;
        }

        .exchange-page .form-footer .btn {
            flex: 1;
        }
    }

    @media print {
        .exchange-page .no-print,
        .exchange-page .breadcrumb {
            display: none !important;
        }

        .exchange-page .exchange-card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
            break-inside: avoid;
        }
    }
</style>

<div class="container-fluid px-3 py-3 exchange-page">

    {{-- Breadcrumb & Action Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="d-inline-flex align-items-center justify-content-center rounded-3"
                      style="width:36px;height:36px;background:#eaf6fc;color:#2388b8;">
                    <i class="fas fa-right-left"></i>
                </span>
                <h5 class="page-heading mb-0">Create Supplier Product Exchange / Swap</h5>
            </div>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.supplier-exchanges.index') }}">Supplier Exchanges</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">New Exchange</li>
                </ol>
            </nav>
        </div>

        <div class="no-print">
            <a href="{{ route('admin.supplier-exchanges.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Exchanges
            </a>
        </div>
    </div>

    {{-- Validation Error Alerts --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center gap-2 fw-semibold mb-2">
                <i class="fas fa-circle-exclamation"></i>
                Exchange submission encountered errors:
            </div>

            <ul class="mb-0 ps-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('admin.supplier-exchanges.store') }}" method="POST" enctype="multipart/form-data" id="exchangeForm">
        @csrf

        {{-- Sourcing & Commercial Context --}}
        <div class="card exchange-card mb-4">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-file-contract text-info"></i>
                    <h6 class="section-title">Exchange Information &amp; Vendor Context</h6>
                </div>
                <small class="section-subtitle">Select the supplier, warehouse and commercial details for this product exchange.</small>
            </div>

            <div class="card-body">
                <div class="row g-3">

                    {{-- Supplier Selector --}}
                    <div class="col-12 col-md-6 col-lg-4">
                        <label class="form-label">
                            Supplier / Swapping Vendor <span class="required-mark">*</span>
                        </label>

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

                        <div class="balance-cue">
                            <i class="fas fa-wallet"></i>
                            <span>Current Ledger Due:</span>
                            <strong id="supplierBalanceNotice">৳0.00</strong>
                        </div>
                    </div>

                    {{-- Exchange Number --}}
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label">
                            Exchange # <span class="required-mark">*</span>
                        </label>
                        <input type="text"
                               name="exchange_number"
                               class="form-control form-control-sm font-monospace"
                               value="{{ old('exchange_number', 'EXC-' . date('Ymd') . '-' . strtoupper(Str::random(4))) }}"
                               required>
                    </div>

                    {{-- Exchange Date --}}
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label">
                            Exchange Date <span class="required-mark">*</span>
                        </label>
                        <input type="date"
                               name="exchange_date"
                               class="form-control form-control-sm"
                               value="{{ old('exchange_date', date('Y-m-d')) }}"
                               required>
                    </div>

                    {{-- Target Warehouse --}}
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label">
                            Exchange Warehouse <span class="required-mark">*</span>
                        </label>
                        <select name="warehouse_id" id="warehouse_id" class="form-select form-select-sm" required>
                            <option value="">Select Warehouse</option>
                            @foreach($warehouses ?? [] as $wh)
                                <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                    {{ $wh->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Operating Branch --}}
                    <div class="col-6 col-md-3 col-lg-2">
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

                    {{-- Reference Number --}}
                    <div class="col-12 col-md-6 col-lg-3">
                        <label class="form-label">Vendor Challan / Outward Gate Pass #</label>
                        <input type="text"
                               name="reference_no"
                               class="form-control form-control-sm font-monospace"
                               placeholder="CHN-SWAP-9021"
                               value="{{ old('reference_no') }}">
                    </div>

                    {{-- Exchange Status --}}
                    <div class="col-12 col-md-6 col-lg-3">
                        <label class="form-label">
                            Status <span class="required-mark">*</span>
                        </label>
                        <select name="status" class="form-select form-select-sm" required>
                            <option value="approved" {{ old('status', 'approved') === 'approved' ? 'selected' : '' }}>
                                Approved &amp; Adjust Stock Immediately
                            </option>
                            <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>
                                Pending Verification / In Transit
                            </option>
                        </select>
                    </div>

                    {{-- Settlement Method --}}
                    <div class="col-12 col-md-6 col-lg-3">
                        <label class="form-label">
                            Differential Settlement <span class="required-mark">*</span>
                        </label>
                        <select name="settlement_type" id="settlement_type" class="form-select form-select-sm" required>
                            <option value="ledger_adjustment" {{ old('settlement_type', 'ledger_adjustment') === 'ledger_adjustment' ? 'selected' : '' }}>
                                Adjust Supplier Ledger Account
                            </option>
                            <option value="spot_cash" {{ old('settlement_type') === 'spot_cash' ? 'selected' : '' }}>
                                Spot Cash Settlement
                            </option>
                            <option value="bank_payment" {{ old('settlement_type') === 'bank_payment' ? 'selected' : '' }}>
                                Bank Wire Settlement
                            </option>
                        </select>
                    </div>

                    {{-- Bank / Drawer Account --}}
                    <div class="col-12 col-md-6 col-lg-3" id="bankAccountCol" style="display: none;">
                        <label class="form-label">Settlement Bank/Cash Account</label>
                        <select name="bank_account_id" id="bank_account_id" class="form-select form-select-sm">
                            <option value="">Select Account</option>
                            @foreach($bankAccounts ?? [] as $acc)
                                <option value="{{ $acc->id }}" {{ old('bank_account_id') == $acc->id ? 'selected' : '' }}>
                                    {{ $acc->account_name }} &mdash; {{ $acc->bank_name ?? 'Cash Account' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Attachment --}}
                    <div class="col-12 col-md-6 col-lg-4">
                        <label class="form-label">Swap Challan / Replacement Note Scan</label>
                        <input type="file"
                               name="attachment_file"
                               class="form-control form-control-sm"
                               accept=".pdf,.jpg,.jpeg,.png">
                        <small class="text-muted d-block mt-1">Accepted formats: PDF, JPG, JPEG, PNG</small>
                    </div>

                    {{-- Remarks --}}
                    <div class="col-12 col-md-6 col-lg-8">
                        <label class="form-label">Exchange Narration / Terms</label>
                        <input type="text"
                               name="notes"
                               class="form-control form-control-sm"
                               placeholder="Reason for exchange, defective batch replacement terms, RMA reference..."
                               value="{{ old('notes') }}">
                    </div>

                </div>
            </div>
        </div>

        {{-- Section 1: Outward Items --}}
        <div class="card exchange-card mb-4">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between gap-3 section-header">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-2"
                                  style="width:30px;height:30px;background:#fff0f0;color:#dc5555;">
                                <i class="fas fa-arrow-up-from-bracket"></i>
                            </span>
                            <h6 class="section-title text-danger">1. Outward Items</h6>
                        </div>
                        <small class="section-subtitle">
                            Goods dispatched or returned to the supplier. These quantities will be deducted from your selected warehouse.
                        </small>
                    </div>

                    <button type="button" class="btn btn-sm btn-outline-danger no-print" id="addOutwardRowBtn">
                        <i class="fas fa-plus me-1"></i> Add Outward Item
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle exchange-table" id="outwardTable">
                    <thead>
                        <tr>
                            <th style="min-width:220px;">Product Description <span class="required-mark">*</span></th>
                            <th style="width:120px;">SKU / Code</th>
                            <th style="width:110px;">Batch #</th>
                            <th class="text-center" style="width:95px;">Return Qty <span class="required-mark">*</span></th>
                            <th class="text-end" style="width:110px;">Unit Rate (৳) <span class="required-mark">*</span></th>
                            <th class="text-end" style="width:130px;">Line Total (৳)</th>
                            <th style="min-width:140px;">Return / Swap Reason <span class="required-mark">*</span></th>
                            <th class="text-center" style="width:45px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="outwardContainer">
                        {{-- Injected dynamically --}}
                    </tbody>
                </table>
            </div>

            <div class="table-total d-flex justify-content-end align-items-center gap-3">
                <span class="table-total-label">Total Outward Valuation</span>
                <span class="table-total-value text-danger" id="displayOutwardSubtotal">৳0.00</span>
                <input type="hidden" name="outward_total" id="outward_total" value="0.00">
            </div>
        </div>

        {{-- Section 2: Inward Items --}}
        <div class="card exchange-card mb-4">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between gap-3 section-header">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-2"
                                  style="width:30px;height:30px;background:#eaf8f0;color:#26975b;">
                                <i class="fas fa-arrow-down-to-bracket"></i>
                            </span>
                            <h6 class="section-title text-success">2. Inward Items</h6>
                        </div>
                        <small class="section-subtitle">
                            Replacement goods received from the supplier. These quantities will be added to your selected warehouse.
                        </small>
                    </div>

                    <button type="button" class="btn btn-sm btn-outline-success no-print" id="addInwardRowBtn">
                        <i class="fas fa-plus me-1"></i> Add Inward Item
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle exchange-table" id="inwardTable">
                    <thead>
                        <tr>
                            <th style="min-width:220px;">Product Description <span class="required-mark">*</span></th>
                            <th style="width:120px;">SKU / Code</th>
                            <th style="width:110px;">New Batch #</th>
                            <th style="width:120px;">Expiry Date</th>
                            <th class="text-center" style="width:95px;">Received Qty <span class="required-mark">*</span></th>
                            <th class="text-end" style="width:110px;">Unit Rate (৳) <span class="required-mark">*</span></th>
                            <th class="text-end" style="width:130px;">Line Total (৳)</th>
                            <th class="text-center" style="width:45px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="inwardContainer">
                        {{-- Injected dynamically --}}
                    </tbody>
                </table>
            </div>

            <div class="table-total d-flex justify-content-end align-items-center gap-3">
                <span class="table-total-label">Total Inward Valuation</span>
                <span class="table-total-value text-success" id="displayInwardSubtotal">৳0.00</span>
                <input type="hidden" name="inward_total" id="inward_total" value="0.00">
            </div>
        </div>

        {{-- Section 3: Financial Settlement --}}
        <div class="card exchange-card mb-4">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-scale-balanced text-info"></i>
                    <h6 class="section-title">3. Financial Settlement &amp; Differential Summary</h6>
                </div>
                <small class="section-subtitle">Review the valuation difference between returned and received goods before saving the exchange.</small>
            </div>

            <div class="card-body">
                <div class="row g-3 align-items-stretch">

                    {{-- Settlement Logic --}}
                    <div class="col-12 col-lg-7">
                        <div class="logic-panel">
                            <div class="logic-panel-title">
                                <i class="fas fa-circle-info text-info me-1"></i>
                                Differential Balance Settlement Logic
                            </div>

                            <ul>
                                <li>
                                    <strong>Inward &gt; Outward:</strong>
                                    You received items of higher value. The surplus is credited to the supplier (accounts payable increases).
                                </li>
                                <li>
                                    <strong>Outward &gt; Inward:</strong>
                                    You returned items of higher value. The differential reduces your accounts payable liability to the supplier.
                                </li>
                                <li>
                                    <strong>Inward == Outward:</strong>
                                    Pure 1:1 value exchange with zero financial debit/credit balance.
                                </li>
                            </ul>
                        </div>
                    </div>

                    {{-- Financial Summary --}}
                    <div class="col-12 col-lg-5">
                        <div class="summary-card">
                            <div class="summary-line">
                                <span class="summary-label">
                                    <i class="fas fa-arrow-up text-danger me-1"></i>
                                    Total Outward Returned
                                </span>
                                <span class="summary-value text-danger" id="summaryOutward">৳0.00</span>
                            </div>

                            <div class="summary-line">
                                <span class="summary-label">
                                    <i class="fas fa-arrow-down text-success me-1"></i>
                                    Total Inward Received
                                </span>
                                <span class="summary-value text-success" id="summaryInward">৳0.00</span>
                            </div>

                            <div class="differential-box">
                                <div class="d-flex justify-content-between align-items-center gap-2">
                                    <span id="differentialLabel">Net Differential:</span>
                                    <span class="fw-bold fs-5 text-dark" id="displayNetDifferential">৳0.00</span>
                                    <input type="hidden" name="differential_amount" id="differential_amount" value="0.00">
                                </div>
                                <small class="text-muted d-block mt-2">
                                    Automatically calculated from the total inward and outward valuations.
                                </small>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Form Actions --}}
            <div class="form-footer d-flex flex-wrap justify-content-end align-items-center gap-2 no-print">
                <a href="{{ route('admin.supplier-exchanges.index') }}" class="btn btn-sm btn-light border px-4">
                    Cancel
                </a>

                <button type="submit" class="btn btn-sm btn-info text-white px-4">
                    <i class="fas fa-check-circle me-1"></i> Save &amp; Post Exchange
                </button>
            </div>
        </div>
    </form>
</div>

{{-- Outward Row Template --}}
<template id="outwardRowTemplate">
    <tr class="outward-row">
        <td>
            <input type="text" class="form-control form-control-sm product-search-input mb-1" placeholder="Product name or SKU..." autocomplete="off">
            <input type="hidden" name="outward_items[INDEX][product_id]" class="product-id-input" required>
            <input type="hidden" name="outward_items[INDEX][variant_id]" class="variant-id-input">
            <small class="text-muted product-name-display d-block"></small>
        </td>
        <td>
            <input type="text" name="outward_items[INDEX][sku]" class="form-control form-control-sm sku-input" placeholder="SKU">
        </td>
        <td>
            <input type="text" name="outward_items[INDEX][batch_no]" class="form-control form-control-sm batch-input" placeholder="Batch #">
        </td>
        <td>
            <input type="number" step="any" name="outward_items[INDEX][quantity]" class="form-control form-control-sm text-center outward-qty-input fw-bold text-danger" value="1" min="0.01" required>
        </td>
        <td>
            <input type="number" step="0.01" name="outward_items[INDEX][unit_cost]" class="form-control form-control-sm text-end outward-cost-input" value="0.00" min="0" required>
        </td>
        <td class="text-end fw-semibold text-danger outward-line-total">
            ৳0.00
        </td>
        <td>
            <select name="outward_items[INDEX][reason]" class="form-select form-select-sm" required>
                <option value="defective">Defective / Damaged</option>
                <option value="size_swap">Size / Specification Swap</option>
                <option value="shade_mismatch">Color / Shade Mismatch</option>
                <option value="expired">Near Expiry Replacement</option>
                <option value="other">Other Reason</option>
            </select>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-row-btn" title="Remove row">
                <i class="fas fa-trash-can"></i>
            </button>
        </td>
    </tr>
</template>

{{-- Inward Row Template --}}
<template id="inwardRowTemplate">
    <tr class="inward-row">
        <td>
            <input type="text" class="form-control form-control-sm product-search-input mb-1" placeholder="Product name or SKU..." autocomplete="off">
            <input type="hidden" name="inward_items[INDEX][product_id]" class="product-id-input" required>
            <input type="hidden" name="inward_items[INDEX][variant_id]" class="variant-id-input">
            <small class="text-muted product-name-display d-block"></small>
        </td>
        <td>
            <input type="text" name="inward_items[INDEX][sku]" class="form-control form-control-sm sku-input" placeholder="SKU">
        </td>
        <td>
            <input type="text" name="inward_items[INDEX][batch_no]" class="form-control form-control-sm batch-input" placeholder="Batch #">
        </td>
        <td>
            <input type="date" name="inward_items[INDEX][expiry_date]" class="form-control form-control-sm expiry-input">
        </td>
        <td>
            <input type="number" step="any" name="inward_items[INDEX][quantity]" class="form-control form-control-sm text-center inward-qty-input fw-bold text-success" value="1" min="0.01" required>
        </td>
        <td>
            <input type="number" step="0.01" name="inward_items[INDEX][unit_cost]" class="form-control form-control-sm text-end inward-cost-input" value="0.00" min="0" required>
        </td>
        <td class="text-end fw-semibold text-success inward-line-total">
            ৳0.00
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-row-btn" title="Remove row">
                <i class="fas fa-trash-can"></i>
            </button>
        </td>
    </tr>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let outwardIndex = 0;
    let inwardIndex = 0;

    const outwardContainer = document.getElementById('outwardContainer');
    const inwardContainer = document.getElementById('inwardContainer');
    const outwardTemplate = document.getElementById('outwardRowTemplate').innerHTML;
    const inwardTemplate = document.getElementById('inwardRowTemplate').innerHTML;

    const addOutwardBtn = document.getElementById('addOutwardRowBtn');
    const addInwardBtn = document.getElementById('addInwardRowBtn');

    const supplierSelect = document.getElementById('supplier_id');
    const supplierBalanceSpan = document.getElementById('supplierBalanceNotice');
    const settlementType = document.getElementById('settlement_type');
    const bankAccountCol = document.getElementById('bankAccountCol');

    const displayOutwardSubtotal = document.getElementById('displayOutwardSubtotal');
    const displayInwardSubtotal = document.getElementById('displayInwardSubtotal');
    const summaryOutward = document.getElementById('summaryOutward');
    const summaryInward = document.getElementById('summaryInward');
    const displayNetDifferential = document.getElementById('displayNetDifferential');
    const differentialLabel = document.getElementById('differentialLabel');
    const hiddenDifferential = document.getElementById('differential_amount');
    const hiddenOutward = document.getElementById('outward_total');
    const hiddenInward = document.getElementById('inward_total');

    // Supplier balance cue
    function updateSupplierBalance() {
        const selected = supplierSelect.options[supplierSelect.selectedIndex];
        const balance = parseFloat(selected ? selected.getAttribute('data-balance') : 0) || 0;
        supplierBalanceSpan.innerText = '৳' + balance.toFixed(2);
    }
    supplierSelect.addEventListener('change', updateSupplierBalance);

    // Settlement mode conditional account field
    settlementType.addEventListener('change', function () {
        if (this.value === 'spot_cash' || this.value === 'bank_payment') {
            bankAccountCol.style.display = 'block';
        } else {
            bankAccountCol.style.display = 'none';
        }
    });

    // Add Outward Row
    function addOutwardRow(prefill = null) {
        const rowHtml = outwardTemplate.replace(/INDEX/g, outwardIndex);
        outwardContainer.insertAdjacentHTML('beforeend', rowHtml);
        const row = outwardContainer.lastElementChild;
        outwardIndex++;

        setupOutwardRowEvents(row);

        if (prefill) {
            row.querySelector('.product-id-input').value = prefill.product_id || '';
            row.querySelector('.variant-id-input').value = prefill.variant_id || '';
            row.querySelector('.sku-input').value = prefill.sku || '';
            row.querySelector('.batch-input').value = prefill.batch_no || '';
            row.querySelector('.outward-qty-input').value = prefill.quantity || 1;
            row.querySelector('.outward-cost-input').value = prefill.unit_cost || 0;
            row.querySelector('.product-name-display').innerText = prefill.product_name || prefill.name || '';
            row.querySelector('.product-search-input').value = prefill.product_name || prefill.name || '';
        }

        calculateCalculations();
    }

    // Add Inward Row
    function addInwardRow(prefill = null) {
        const rowHtml = inwardTemplate.replace(/INDEX/g, inwardIndex);
        inwardContainer.insertAdjacentHTML('beforeend', rowHtml);
        const row = inwardContainer.lastElementChild;
        inwardIndex++;

        setupInwardRowEvents(row);

        if (prefill) {
            row.querySelector('.product-id-input').value = prefill.product_id || '';
            row.querySelector('.variant-id-input').value = prefill.variant_id || '';
            row.querySelector('.sku-input').value = prefill.sku || '';
            row.querySelector('.batch-input').value = prefill.batch_no || '';
            row.querySelector('.expiry-input').value = prefill.expiry_date || '';
            row.querySelector('.inward-qty-input').value = prefill.quantity || 1;
            row.querySelector('.inward-cost-input').value = prefill.unit_cost || 0;
            row.querySelector('.product-name-display').innerText = prefill.product_name || prefill.name || '';
            row.querySelector('.product-search-input').value = prefill.product_name || prefill.name || '';
        }

        calculateCalculations();
    }

    function setupOutwardRowEvents(row) {
        const inputs = row.querySelectorAll('.outward-qty-input, .outward-cost-input');
        inputs.forEach(input => input.addEventListener('input', calculateCalculations));

        row.querySelector('.remove-row-btn').addEventListener('click', function () {
            if (outwardContainer.children.length > 1) {
                row.remove();
                calculateCalculations();
            } else {
                alert('An exchange must contain at least one outward item.');
            }
        });

        const searchInput = row.querySelector('.product-search-input');
        searchInput.addEventListener('change', function () {
            if (!row.querySelector('.product-id-input').value) {
                row.querySelector('.product-id-input').value = 1;
            }
            row.querySelector('.product-name-display').innerText = searchInput.value;
            calculateCalculations();
        });
    }

    function setupInwardRowEvents(row) {
        const inputs = row.querySelectorAll('.inward-qty-input, .inward-cost-input');
        inputs.forEach(input => input.addEventListener('input', calculateCalculations));

        row.querySelector('.remove-row-btn').addEventListener('click', function () {
            if (inwardContainer.children.length > 1) {
                row.remove();
                calculateCalculations();
            } else {
                alert('An exchange must contain at least one inward replacement item.');
            }
        });

        const searchInput = row.querySelector('.product-search-input');
        searchInput.addEventListener('change', function () {
            if (!row.querySelector('.product-id-input').value) {
                row.querySelector('.product-id-input').value = 1;
            }
            row.querySelector('.product-name-display').innerText = searchInput.value;
            calculateCalculations();
        });
    }

    function calculateCalculations() {
        let totalOutward = 0;
        let totalInward = 0;

        // Outward calculation
        outwardContainer.querySelectorAll('.outward-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.outward-qty-input').value) || 0;
            const cost = parseFloat(row.querySelector('.outward-cost-input').value) || 0;
            const line = Math.max(0, qty * cost);
            row.querySelector('.outward-line-total').innerText = '৳' + line.toFixed(2);
            totalOutward += line;
        });

        // Inward calculation
        inwardContainer.querySelectorAll('.inward-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.inward-qty-input').value) || 0;
            const cost = parseFloat(row.querySelector('.inward-cost-input').value) || 0;
            const line = Math.max(0, qty * cost);
            row.querySelector('.inward-line-total').innerText = '৳' + line.toFixed(2);
            totalInward += line;
        });

        const diff = totalInward - totalOutward;

        displayOutwardSubtotal.innerText = '৳' + totalOutward.toFixed(2);
        displayInwardSubtotal.innerText = '৳' + totalInward.toFixed(2);
        summaryOutward.innerText = '৳' + totalOutward.toFixed(2);
        summaryInward.innerText = '৳' + totalInward.toFixed(2);

        hiddenOutward.value = totalOutward.toFixed(2);
        hiddenInward.value = totalInward.toFixed(2);
        hiddenDifferential.value = diff.toFixed(2);

        if (diff > 0) {
            differentialLabel.innerText = 'Net Payable (Extra Due):';
            displayNetDifferential.innerText = '+৳' + Math.abs(diff).toFixed(2);
            displayNetDifferential.className = 'fw-bold fs-5 text-danger';
        } else if (diff < 0) {
            differentialLabel.innerText = 'Net Receivable (Vendor Credit):';
            displayNetDifferential.innerText = '-৳' + Math.abs(diff).toFixed(2);
            displayNetDifferential.className = 'fw-bold fs-5 text-success';
        } else {
            differentialLabel.innerText = 'Net Differential:';
            displayNetDifferential.innerText = '৳0.00 (Even Swap)';
            displayNetDifferential.className = 'fw-bold fs-5 text-muted';
        }
    }

    addOutwardBtn.addEventListener('click', () => addOutwardRow());
    addInwardBtn.addEventListener('click', () => addInwardRow());

    // Initialize defaults
    updateSupplierBalance();
    addOutwardRow();
    addInwardRow();
});
</script>
@endsection