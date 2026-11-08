
@extends('layouts.admin')

@section('title', 'Record Supplier Payment Voucher')

@section('admin_content')
<style>
    /* ==========================================
       SUPPLIER PAYMENT - KAIADMIN CLASSIC STYLE
    ========================================== */

    .payment-create-root {
        --sp-primary: var(--bs-primary, #1572e8);
        --sp-success: var(--bs-success, #31ce36);
        --sp-danger: var(--bs-danger, #f25961);
        --sp-warning: var(--bs-warning, #ffad46);
        --sp-info: var(--bs-info, #48abf7);
        --sp-body: #f8f9fa;
        --sp-card: #ffffff;
        --sp-heading: #2a2f5b;
        --sp-text: #495057;
        --sp-muted: #8d9498;
        --sp-border: #ebecec;
        --sp-radius: 5px;
        color: var(--sp-text);
        font-size: 13px;
        min-width: 0;
    }

    .payment-create-root *,
    .payment-create-root *::before,
    .payment-create-root *::after {
        box-sizing: border-box;
    }

    /* Page Header */

    .payment-create-root .page-heading {
        color: var(--sp-heading);
        font-size: clamp(16px, 1.4vw, 19px);
        font-weight: 700;
        letter-spacing: -0.3px;
    }

    .payment-create-root .page-heading-icon {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(49, 206, 54, 0.10);
        color: var(--sp-success);
        border-radius: 5px;
        font-size: 16px;
        flex-shrink: 0;
    }

    /* Cards */

    .payment-create-root .card {
        background: var(--sp-card);
        border: 1px solid var(--sp-border) !important;
        border-radius: var(--sp-radius) !important;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.035) !important;
        min-width: 0;
        margin-bottom: 16px;
    }

    .payment-create-root .card-header {
        background: #fff;
        border-bottom: 1px solid var(--sp-border) !important;
        padding: 13px 17px;
    }

    .payment-create-root .card-body {
        padding: 17px;
    }

    .payment-create-root .card-footer {
        background: #fff;
        border-top: 1px solid var(--sp-border) !important;
        padding: 13px 17px;
    }

    .payment-create-root .section-heading {
        color: var(--sp-heading);
        font-size: 13.5px;
        font-weight: 700;
        margin: 0;
        line-height: 1.5;
    }

    .payment-create-root .section-icon {
        color: var(--sp-success);
        margin-right: 7px;
    }

    .payment-create-root .section-description {
        display: block;
        color: var(--sp-muted);
        font-size: 11.5px;
        margin-top: 3px;
        line-height: 1.5;
    }

    /* Form Controls */

    .payment-create-root .form-label {
        color: #495057;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 6px;
    }

    .payment-create-root .form-control,
    .payment-create-root .form-select {
        min-height: 36px;
        height: auto;
        padding: 7px 10px;
        border: 1px solid #e6e9ed;
        border-radius: var(--sp-radius) !important;
        color: #495057;
        background-color: #fff;
        font-size: 12.5px;
        box-shadow: none;
        max-width: 100%;
    }

    .payment-create-root .form-control::placeholder {
        color: #adb5bd;
        opacity: 1;
    }

    .payment-create-root .form-control:focus,
    .payment-create-root .form-select:focus {
        border-color: var(--sp-primary);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, 0.10);
    }

    .payment-create-root .form-control[type="file"] {
        padding: 5px 7px;
        overflow: hidden;
    }

    .payment-create-root .form-control[type="file"]::file-selector-button {
        margin: -5px 8px -5px -7px;
        padding: 7px 9px;
        border: 0;
        border-right: 1px solid #e6e9ed;
        background: #f8f9fa;
        color: #495057;
        font-size: 11px;
    }

    .payment-create-root .form-text,
    .payment-create-root .text-muted {
        color: var(--sp-muted) !important;
    }

    .payment-create-root .font-monospace {
        font-variant-numeric: tabular-nums;
    }

    .payment-create-root .form-label .text-danger {
        font-weight: 700;
    }

    /* Supplier Balance */

    .payment-create-root .supplier-balance-info {
        font-size: 11.5px;
        line-height: 1.6;
        margin-top: 6px;
        color: #737b84;
    }

    .payment-create-root #supplierPayableBalance {
        font-size: 12.5px;
    }

    /* Buttons */

    .payment-create-root .btn {
        min-height: 34px;
        padding: 6px 12px;
        border-radius: var(--sp-radius) !important;
        font-size: 12px;
        font-weight: 600;
        transition: all 0.2s ease;
        white-space: normal;
    }

    .payment-create-root .btn-sm {
        min-height: 32px;
        padding: 5px 10px;
    }

    .payment-create-root .btn-success {
        background-color: var(--sp-success);
        border-color: var(--sp-success);
        color: #fff;
    }

    .payment-create-root .btn-success:hover {
        background-color: #28b92e;
        border-color: #28b92e;
    }

    .payment-create-root .btn-outline-success {
        color: #28a745;
        border-color: rgba(49, 206, 54, 0.55);
    }

    .payment-create-root .btn-outline-success:hover {
        background: var(--sp-success);
        border-color: var(--sp-success);
        color: #fff;
    }

    .payment-create-root .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* Alerts */

    .payment-create-root .alert {
        border-radius: var(--sp-radius);
        padding: 12px 15px;
        font-size: 12.5px;
    }

    .payment-create-root .alert-danger {
        background: #fff5f5;
        border: 1px solid #f5d5d8 !important;
        color: #b42332;
    }

    /* Invoice Table */

    .payment-create-root .invoice-table-wrap {
        width: 100%;
        overflow-x: auto !important;
        overflow-y: hidden !important;
        -webkit-overflow-scrolling: touch;
    }

    .payment-create-root #invoicesTable {
        width: 100%;
        min-width: 1000px;
        margin-bottom: 0;
        border-color: var(--sp-border);
        font-size: 12px;
        vertical-align: middle;
    }

    .payment-create-root #invoicesTable thead th {
        background: #f8f9fa;
        color: #6c757d;
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: 0.35px;
        text-transform: uppercase;
        padding: 11px 9px;
        white-space: nowrap;
        vertical-align: middle;
        border-bottom: 1px solid #e6e9ed;
    }

    .payment-create-root #invoicesTable tbody td {
        padding: 9px;
        border-color: #edf0f2;
        white-space: nowrap;
    }

    .payment-create-root #invoicesTable tbody tr:hover {
        background-color: #fafbfd;
    }

    .payment-create-root #invoicesTable .allocation-input {
        min-width: 110px;
        max-width: 145px;
        margin-left: auto;
    }

    .payment-create-root #invoicesTable .pay-full-btn {
        min-height: 28px;
        font-size: 11px;
        border-radius: 4px !important;
    }

    .payment-create-root #invoicesTable .empty-invoice-message {
        white-space: normal;
        line-height: 1.6;
    }

    /* Allocation Information */

    .payment-create-root .allocation-info {
        height: 100%;
        padding: 12px;
        border: 1px solid var(--sp-border);
        border-radius: var(--sp-radius);
        background: #fff;
        color: #6c757d;
        font-size: 12px;
        line-height: 1.65;
    }

    .payment-create-root .allocation-info-title {
        color: var(--sp-heading);
        font-weight: 700;
        margin-bottom: 5px;
    }

    /* Financial Summary */

    .payment-create-root .financial-summary {
        padding: 15px;
        background: #fff;
        border: 1px solid var(--sp-border) !important;
        border-radius: var(--sp-radius);
    }

    .payment-create-root .summary-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        font-size: 12px;
        margin-bottom: 10px;
    }

    .payment-create-root .summary-row:last-child {
        margin-bottom: 0;
    }

    .payment-create-root .summary-label {
        color: #737b84;
    }

    .payment-create-root .summary-value {
        font-size: 14px;
        font-weight: 700;
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
    }

    .payment-create-root .summary-divider {
        border-color: var(--sp-border);
        opacity: 1;
        margin: 10px 0;
    }

    .payment-create-root .summary-total {
        color: var(--sp-primary);
    }

    /* Footer Actions */

    .payment-create-root .footer-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }

    .payment-create-root #submitBtn {
        min-width: 175px;
    }

    /* Loading Overlay */

    #payment-global-loader {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.78);
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

    #payment-global-loader .spinner-border {
        width: 3rem;
        height: 3rem;
    }

    #payment-global-loader h5 {
        font-size: 16px;
    }

    #payment-global-loader p {
        max-width: 400px;
        line-height: 1.6;
    }

    /* Tablet */

    @media (max-width: 991.98px) {
        .payment-create-root .card-body {
            padding: 15px;
        }

        .payment-create-root .card-header,
        .payment-create-root .card-footer {
            padding: 12px 15px;
        }

        .payment-create-root .page-heading {
            font-size: 16px;
        }
    }

    /* Mobile */

    @media (max-width: 575.98px) {
        .payment-create-root {
            padding-left: 10px !important;
            padding-right: 10px !important;
            font-size: 12px;
        }

        .payment-create-root .page-header {
            align-items: flex-start !important;
        }

        .payment-create-root .page-heading {
            font-size: 15px;
        }

        .payment-create-root .page-heading-icon {
            width: 30px;
            height: 30px;
            font-size: 14px;
        }

        .payment-create-root .page-header .btn {
            font-size: 11px;
            padding: 5px 8px;
        }

        .payment-create-root .card-body {
            padding: 12px;
        }

        .payment-create-root .card-header {
            padding: 12px;
        }

        .payment-create-root .card-footer {
            padding: 12px;
        }

        .payment-create-root .section-heading {
            font-size: 12.5px;
        }

        .payment-create-root .section-description {
            font-size: 11px;
        }

        .payment-create-root .form-label {
            font-size: 11.5px;
        }

        .payment-create-root .form-control,
        .payment-create-root .form-select {
            min-height: 36px;
            font-size: 12px;
        }

        .payment-create-root .allocation-actions {
            width: 100%;
        }

        .payment-create-root .allocation-actions .btn {
            flex: 1 1 auto;
            font-size: 10.5px;
            padding: 6px 7px;
        }

        .payment-create-root .financial-summary {
            padding: 12px;
        }

        .payment-create-root .summary-row {
            font-size: 11.5px;
        }

        .payment-create-root .summary-value {
            font-size: 13px;
        }

        .payment-create-root .footer-actions {
            width: 100%;
        }

        .payment-create-root .footer-actions .btn {
            flex: 1;
        }

        .payment-create-root #submitBtn {
            min-width: 0;
        }
    }

    @media (max-width: 359.98px) {
        .payment-create-root .page-header {
            flex-direction: column;
            align-items: flex-start !important;
        }

        .payment-create-root .page-header > div:last-child {
            width: 100%;
        }

        .payment-create-root .page-header .btn {
            width: 100%;
        }

        .payment-create-root .summary-row {
            gap: 5px;
        }
    }
</style>

{{-- Processing Overlay --}}
<div id="payment-global-loader">
    <div class="spinner-border text-success" role="status">
        <span class="visually-hidden">Processing...</span>
    </div>
    <h5 class="fw-bold text-white mt-3 mb-1">Processing Supplier Payment...</h5>
    <p class="text-white-50 small mb-0">
        Please do not reload the page or submit again. Updating the ledger and voucher.
    </p>
</div>

<div class="container-fluid px-3 py-3 payment-create-root">

    {{-- Page Header --}}
    <div class="page-header d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div class="d-flex align-items-center gap-2">
            <span class="page-heading-icon">
                <i class="fas fa-money-check-dollar"></i>
            </span>
            <h5 class="page-heading mb-0">
                Record Supplier Payment
            </h5>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.supplier-payments.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Payments
            </a>
        </div>
    </div>

    {{-- Error Banner --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-3">
            <div class="d-flex align-items-center gap-2 fw-semibold">
                <i class="fas fa-circle-exclamation fa-lg"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-3">
            <div class="d-flex align-items-center gap-2 fw-semibold mb-2">
                <i class="fas fa-circle-exclamation"></i>
                Payment recording failed. Please review the following:
            </div>
            <ul class="mb-0 ps-3 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('admin.supplier-payments.store') }}" method="POST" enctype="multipart/form-data" id="supplierPaymentForm">
        @csrf

        {{-- Payment Particulars --}}
        <div class="card shadow-sm mb-3">

            <div class="card-header">
                <h6 class="section-heading">
                    <i class="fas fa-receipt section-icon"></i>
                    Payment Details
                </h6>
            </div>

            <div class="card-body">
                <div class="row g-3">

                    {{-- Supplier --}}
                    <div class="col-12 col-md-4">
                        <label class="form-label">
                            Supplier / Beneficiary <span class="text-danger">*</span>
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

                        <div class="supplier-balance-info">
                            Outstanding Payable:
                            <span class="fw-bold text-danger font-monospace" id="supplierPayableBalance">৳0.00</span>
                        </div>
                    </div>

                    {{-- Payment Type --}}
                    <div class="col-6 col-md-2">
                        <label class="form-label">
                            Payment Type <span class="text-danger">*</span>
                        </label>
                        <select name="payment_type" id="payment_type" class="form-select form-select-sm" required>
                            <option value="invoice_payment" {{ old('payment_type', 'invoice_payment') === 'invoice_payment' ? 'selected' : '' }}>Bill Settlement</option>
                            <option value="advance" {{ old('payment_type') === 'advance' ? 'selected' : '' }}>Advance Payment</option>
                        </select>
                    </div>

                    {{-- Voucher Number --}}
                    <div class="col-6 col-md-2">
                        <label class="form-label">
                            Voucher Number <span class="text-danger">*</span>
                        </label>
                        @php
                            $defaultVoucher = 'SPV-' . date('Ymd') . '-' . strtoupper(Str::random(4));
                        @endphp
                        <input type="text" name="voucher_number" id="voucher_number" class="form-control form-control-sm font-monospace" value="{{ old('voucher_number', old('payment_number', $defaultVoucher)) }}" required>
                        <input type="hidden" name="payment_number" value="{{ old('voucher_number', old('payment_number', $defaultVoucher)) }}">
                    </div>

                    {{-- Payment Date --}}
                    <div class="col-6 col-md-2">
                        <label class="form-label">
                            Payment Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="payment_date" class="form-control form-control-sm" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                    </div>

                    {{-- Amount --}}
                    <div class="col-6 col-md-2">
                        <label class="form-label">
                            Paid Amount (৳) <span class="text-danger">*</span>
                        </label>
                        <input type="number" step="0.01" name="amount" id="payment_amount" class="form-control form-control-sm text-end fw-bold text-success font-monospace" placeholder="0.00" value="{{ old('amount', request('due_amount', '')) }}" min="0.01" required>
                    </div>

                    {{-- Payment Method --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label">
                            Payment Method <span class="text-danger">*</span>
                        </label>
                        <select name="payment_method" id="payment_method" class="form-select form-select-sm" required>
                            <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash in Hand</option>
                            <option value="bank_transfer" {{ old('payment_method', 'bank_transfer') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer / EFT / RTGS</option>
                            <option value="cheque" {{ old('payment_method') === 'cheque' ? 'selected' : '' }}>Cheque / Pay Order</option>
                            <option value="mobile_banking" {{ old('payment_method') === 'mobile_banking' ? 'selected' : '' }}>Mobile Banking (MFS)</option>
                        </select>
                    </div>

                    {{-- Bank Account --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label">
                            Disbursing Account <span class="text-danger">*</span>
                        </label>
                        <select name="bank_account_id" id="bank_account_id" class="form-select form-select-sm">
                            <option value="">Select Account</option>
                            @foreach($bankAccounts ?? [] as $acc)
                                <option value="{{ $acc->id }}" {{ old('bank_account_id') == $acc->id ? 'selected' : '' }}>
                                    {{ $acc->account_name }} &mdash; {{ $acc->bank_name ?? 'Cash Account' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Branch --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label">Operating Branch</label>
                        <select name="branch_id" class="form-select form-select-sm">
                            <option value="">HQ Branch</option>
                            @foreach($branches ?? [] as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Reference --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label" id="refLabel">Transaction / Reference #</label>
                        <input type="text" name="reference_no" id="reference_no" class="form-control form-control-sm font-monospace" placeholder="TXN-994021 / Cheque #" value="{{ old('reference_no') }}">
                    </div>

                    {{-- Cheque Date --}}
                    <div class="col-6 col-md-3" id="chequeDateCol" style="display: none;">
                        <label class="form-label">Cheque Clearance Date</label>
                        <input type="date" name="cheque_date" class="form-control form-control-sm" value="{{ old('cheque_date') }}">
                    </div>

                    {{-- Attachment --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label">Payment Attachment</label>
                        <input type="file" name="attachment_file" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                    </div>

                    {{-- Notes --}}
                    <div class="col-12">
                        <label class="form-label">Payment Notes</label>
                        <input type="text" name="notes" class="form-control form-control-sm" placeholder="Transaction details, settlement terms or remarks..." value="{{ old('notes') }}">
                    </div>

                </div>
            </div>
        </div>

        {{-- Invoice Allocation --}}
        <div class="card shadow-sm mb-3">

            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">

                <div>
                    <h6 class="section-heading">
                        <i class="fas fa-file-invoice section-icon"></i>
                        Invoice Allocation
                    </h6>
                    <small class="section-description">
                        Allocate payments to outstanding bills or record an advance payment.
                    </small>
                </div>

                <div class="d-flex flex-wrap gap-2 allocation-actions">
                    <button type="button" class="btn btn-sm btn-outline-success fw-semibold" id="autoAllocateFifoBtn">
                        <i class="fas fa-wand-magic-sparkles me-1"></i> Auto Allocate
                    </button>

                    <button type="button" class="btn btn-sm btn-outline-secondary" id="clearAllocationsBtn">
                        <i class="fas fa-rotate-left me-1"></i> Clear
                    </button>
                </div>

            </div>

            {{-- Invoice Table --}}
            <div class="invoice-table-wrap table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0" id="invoicesTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 150px;">Bill #</th>
                            <th style="width: 110px;">Bill Date</th>
                            <th style="width: 110px;">Due Date</th>
                            <th class="text-end" style="width: 130px;">Bill Total (৳)</th>
                            <th class="text-end" style="width: 130px;">Paid to Date (৳)</th>
                            <th class="text-end" style="width: 130px;">Remaining Due (৳)</th>
                            <th class="text-end" style="width: 160px;">Allocate Amount (৳) <span class="text-danger">*</span></th>
                            <th class="text-center" style="width: 80px;">Action</th>
                        </tr>
                    </thead>

                    <tbody id="invoicesContainer">
                        <tr id="emptyInvoicesRow">
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fas fa-circle-info fa-2x mb-2 text-secondary opacity-50"></i>
                                <div class="empty-invoice-message">
                                    Select a supplier to load outstanding invoices.
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Financial Summary --}}
            <div class="card-body bg-light border-top">
                <div class="row g-3 justify-content-between align-items-stretch">

                    <div class="col-12 col-md-6">
                        <div class="allocation-info">
                            <div class="allocation-info-title">
                                <i class="fas fa-scale-balanced text-success me-1"></i>
                                Advance Payment Rule
                            </div>
                            If the paid amount exceeds the allocated invoice total, the remaining amount will be recorded as an
                            <strong>Advance Supplier Payment</strong> in the supplier ledger.
                        </div>
                    </div>

                    <div class="col-12 col-md-5 col-lg-4">
                        <div class="financial-summary">

                            <div class="summary-row">
                                <span class="summary-label">Total Paid Amount</span>
                                <span class="summary-value text-success font-monospace" id="summaryTotalPaid">৳0.00</span>
                            </div>

                            <div class="summary-row">
                                <span class="summary-label">Allocated to Invoices</span>
                                <span class="summary-value text-dark font-monospace" id="summaryTotalAllocated">৳0.00</span>
                            </div>

                            <hr class="summary-divider">

                            <div class="summary-row">
                                <span class="summary-label" id="unallocatedLabel">
                                    Unallocated Surplus (Advance)
                                </span>
                                <span class="summary-value summary-total font-monospace" id="summaryUnallocated">৳0.00</span>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

            {{-- Form Actions --}}
            <div class="card-footer footer-actions">
                <a href="{{ route('admin.supplier-payments.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>

                <button type="submit" class="btn btn-sm btn-success px-4 fw-bold" id="submitBtn">
                    <i class="fas fa-check-circle me-1"></i> Disburse &amp; Post Voucher
                </button>
            </div>

        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let isSubmittingPayment = false;

    const supplierSelect = document.getElementById('supplier_id');
    const supplierPayableSpan = document.getElementById('supplierPayableBalance');
    const paymentAmountInput = document.getElementById('payment_amount');
    const paymentMethodSelect = document.getElementById('payment_method');
    const paymentTypeSelect = document.getElementById('payment_type');
    const chequeCol = document.getElementById('chequeDateCol');
    const refLabel = document.getElementById('refLabel');

    const container = document.getElementById('invoicesContainer');
    const emptyRow = document.getElementById('emptyInvoicesRow');
    const autoAllocateBtn = document.getElementById('autoAllocateFifoBtn');
    const clearAllocationsBtn = document.getElementById('clearAllocationsBtn');

    const summaryPaid = document.getElementById('summaryTotalPaid');
    const summaryAllocated = document.getElementById('summaryTotalAllocated');
    const summaryUnallocated = document.getElementById('summaryUnallocated');
    const unallocatedLabel = document.getElementById('unallocatedLabel');
    const submitBtn = document.getElementById('submitBtn');
    const paymentForm = document.getElementById('supplierPaymentForm');
    const loaderOverlay = document.getElementById('payment-global-loader');

    paymentMethodSelect.addEventListener('change', function () {
        if (this.value === 'cheque') {
            chequeCol.style.display = 'block';
            refLabel.innerText = 'Cheque Number *';
        } else {
            chequeCol.style.display = 'none';
            refLabel.innerText = this.value === 'bank_transfer' ? 'Bank Wire / RTGS Ref #' : 'Transaction / Reference #';
        }
    });

    supplierSelect.addEventListener('change', function () {
        const supplierId = this.value;
        const opt = this.options[this.selectedIndex];
        const balance = parseFloat(opt ? opt.getAttribute('data-balance') : 0) || 0;
        if (supplierPayableSpan) {
            supplierPayableSpan.innerText = '৳' + balance.toFixed(2);
        }

        if (!supplierId) {
            container.innerHTML = '';
            container.appendChild(emptyRow);
            calculateAllocations();
            return;
        }

        container.innerHTML = `<tr><td colspan="8" class="text-center py-3 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Loading unpaid bills...</td></tr>`;

        const fetchUrl = "{{ url('admin/procurement/supplier-payments/unpaid-purchases') }}/" + supplierId;

        fetch(fetchUrl, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(res => {
            container.innerHTML = '';
            if (res.success && res.data && res.data.length > 0) {
                res.data.forEach((bill, idx) => {
                    const due = parseFloat(bill.due_amount);
                    const grand = parseFloat(bill.grand_total);
                    const paid = parseFloat(bill.paid_amount);

                    const tr = document.createElement('tr');
                    tr.className = 'invoice-row';
                    tr.dataset.due = due;
                    tr.dataset.billId = bill.id;

                    tr.innerHTML = `
                        <td class="ps-3 fw-bold font-monospace">
                            #${bill.purchase_number}
                            <input type="hidden" name="allocations[${idx}][purchase_id]" value="${bill.id}">
                        </td>
                        <td class="text-muted">${bill.purchase_date}</td>
                        <td class="text-muted">${bill.due_date || '—'}</td>
                        <td class="text-end font-monospace">৳${grand.toFixed(2)}</td>
                        <td class="text-end text-muted font-monospace">৳${paid.toFixed(2)}</td>
                        <td class="text-end fw-bold text-danger font-monospace">৳${due.toFixed(2)}</td>
                        <td class="text-end">
                            <input type="number" step="0.01" name="allocations[${idx}][amount]" 
                                   class="form-control form-control-sm text-end allocation-input fw-semibold font-monospace" 
                                   placeholder="0.00" value="0.00" min="0" max="${due}">
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-xs btn-outline-success py-0 px-2 pay-full-btn" title="Pay Full Due">Full</button>
                        </td>
                    `;
                    container.appendChild(tr);

                    const allocInput = tr.querySelector('.allocation-input');
                    allocInput.addEventListener('input', function () {
                        const val = parseFloat(this.value) || 0;
                        if (val > due) this.value = due.toFixed(2);
                        calculateAllocations();
                    });

                    tr.querySelector('.pay-full-btn').addEventListener('click', function () {
                        allocInput.value = due.toFixed(2);
                        calculateAllocations();
                    });
                });

                @if(request('purchase_id'))
                    const preselectedBillRow = container.querySelector(`tr[data-bill-id="{{ request('purchase_id') }}"]`);
                    if (preselectedBillRow) {
                        const dueVal = parseFloat(preselectedBillRow.dataset.due) || 0;
                        const input = preselectedBillRow.querySelector('.allocation-input');
                        const paidTotal = parseFloat(paymentAmountInput.value) || dueVal;
                        input.value = Math.min(paidTotal, dueVal).toFixed(2);
                    }
                @endif
            } else {
                container.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-muted"><i class="fas fa-check-circle text-success me-1"></i> No outstanding unpaid invoices found for this supplier. You may still disburse advance payment.</td></tr>`;
            }
            calculateAllocations();
        })
        .catch(err => {
            console.error(err);
            container.innerHTML = `<tr><td colspan="8" class="text-center py-3 text-danger"><i class="fas fa-triangle-exclamation me-1"></i> Error loading invoices.</td></tr>`;
        });
    });

    autoAllocateBtn.addEventListener('click', function () {
        const totalPayment = parseFloat(paymentAmountInput.value) || 0;
        if (totalPayment <= 0) {
            alert('Please enter a valid payment amount first.');
            paymentAmountInput.focus();
            return;
        }

        let remaining = totalPayment;
        const rows = container.querySelectorAll('.invoice-row');

        rows.forEach(row => {
            const due = parseFloat(row.dataset.due) || 0;
            const input = row.querySelector('.allocation-input');

            if (remaining > 0) {
                const allocate = Math.min(remaining, due);
                input.value = allocate.toFixed(2);
                remaining -= allocate;
            } else {
                input.value = (0).toFixed(2);
            }
        });

        calculateAllocations();
    });

    clearAllocationsBtn.addEventListener('click', function () {
        const inputs = container.querySelectorAll('.allocation-input');
        inputs.forEach(input => input.value = (0).toFixed(2));
        calculateAllocations();
    });

    function calculateAllocations() {
        const totalPayment = parseFloat(paymentAmountInput.value) || 0;
        let totalAllocated = 0;

        const inputs = container.querySelectorAll('.allocation-input');
        inputs.forEach(input => {
            totalAllocated += parseFloat(input.value) || 0;
        });

        summaryPaid.innerText = '৳' + totalPayment.toFixed(2);
        summaryAllocated.innerText = '৳' + totalAllocated.toFixed(2);

        const diff = totalPayment - totalAllocated;

        if (diff >= 0) {
            unallocatedLabel.innerText = 'Unallocated Surplus (Advance):';
            summaryUnallocated.innerText = '৳' + diff.toFixed(2);
            summaryUnallocated.className = 'fw-bold text-primary fs-6 font-monospace';
            submitBtn.disabled = false;
        } else {
            unallocatedLabel.innerText = 'Over-allocated by:';
            summaryUnallocated.innerText = '-৳' + Math.abs(diff).toFixed(2);
            summaryUnallocated.className = 'fw-bold text-danger fs-6 font-monospace';
            submitBtn.disabled = true;
        }

        if (totalAllocated > 0) {
            paymentTypeSelect.value = 'invoice_payment';
        } else if (totalPayment > 0 && totalAllocated === 0) {
            paymentTypeSelect.value = 'advance';
        }
    }

    paymentAmountInput.addEventListener('input', calculateAllocations);

    paymentForm.addEventListener('submit', function (e) {
        if (isSubmittingPayment) {
            e.preventDefault();
            return false;
        }

        if (!supplierSelect.value) {
            e.preventDefault();
            alert('Please select a Supplier / Vendor.');
            supplierSelect.focus();
            return false;
        }

        const paid = parseFloat(paymentAmountInput.value) || 0;
        if (paid <= 0) {
            e.preventDefault();
            alert('Please enter a valid disbursement amount.');
            paymentAmountInput.focus();
            return false;
        }

        isSubmittingPayment = true;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Disbursing Voucher...';

        if (loaderOverlay) {
            loaderOverlay.style.display = 'flex';
        }
    });

    if (supplierSelect.value) {
        supplierSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endsection
