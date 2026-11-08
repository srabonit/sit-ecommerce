
@extends('layouts.admin')

@section('title', 'Supplier Payments & Disbursements')

@section('admin_content')
@php
    $paymentsList = $supplierPayments ?? ($payments ?? collect([]));
    $totalVouchersCount = method_exists($paymentsList, 'total') ? $paymentsList->total() : $paymentsList->count();
    $totalDisbursedVal  = (float) ($totalDisbursedAmount ?? ($totalPaidAmount ?? ($paymentsList->sum('amount') ?? 0)));
    $totalSettledInvoicesVal = (float) ($totalAllocatedAmount ?? 0);
    $totalAdvanceSurplusVal  = (float) ($totalAdvanceAmount ?? max(0, $totalDisbursedVal - $totalSettledInvoicesVal));
@endphp

<style>
    /* ============================================
       SUPPLIER PAYMENTS - KAIADMIN CLASSIC
    ============================================ */

    .supplier-payments-root {
        --sp-primary: var(--bs-primary, #1572e8);
        --sp-success: var(--bs-success, #31ce36);
        --sp-danger: var(--bs-danger, #f25961);
        --sp-warning: var(--bs-warning, #ffad46);
        --sp-info: var(--bs-info, #48abf7);
        --sp-heading: #2a2f5b;
        --sp-text: #495057;
        --sp-muted: #8d9498;
        --sp-border: #ebecec;
        --sp-card: #ffffff;
        --sp-radius: 5px;

        color: var(--sp-text);
        font-size: 13px;
        min-width: 0;
    }

    .supplier-payments-root *,
    .supplier-payments-root *::before,
    .supplier-payments-root *::after {
        box-sizing: border-box;
    }

    /* Page Header */

    .supplier-payments-root .page-heading {
        color: var(--sp-heading);
        font-size: clamp(16px, 1.5vw, 19px);
        font-weight: 700;
        letter-spacing: -0.3px;
        line-height: 1.4;
    }

    .supplier-payments-root .heading-icon {
        width: 36px;
        height: 36px;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(49, 206, 54, 0.10);
        color: var(--sp-success);
        border-radius: var(--sp-radius);
        font-size: 16px;
    }

    /* Common Cards */

    .supplier-payments-root .card {
        background: var(--sp-card);
        border: 1px solid var(--sp-border) !important;
        border-radius: var(--sp-radius) !important;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.035) !important;
        min-width: 0;
    }

    .supplier-payments-root .card-header,
    .supplier-payments-root .card-footer {
        background: #fff;
        border-color: var(--sp-border) !important;
    }

    /* Buttons */

    .supplier-payments-root .btn {
        min-height: 34px;
        padding: 6px 11px;
        border-radius: var(--sp-radius) !important;
        font-size: 12px;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .supplier-payments-root .btn-sm {
        min-height: 32px;
        padding: 5px 10px;
    }

    .supplier-payments-root .btn-success {
        background-color: var(--sp-success);
        border-color: var(--sp-success);
    }

    .supplier-payments-root .btn-success:hover {
        background-color: #28b92e;
        border-color: #28b92e;
    }

    /* Metric Cards */

    .supplier-payments-root .metric-card {
        height: 100%;
        padding: 15px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .supplier-payments-root .metric-card:hover {
        border-color: #d9dfe5 !important;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.045) !important;
    }

    .supplier-payments-root .metric-label {
        color: var(--sp-muted);
        font-size: 11.5px;
        font-weight: 600;
        line-height: 1.5;
    }

    .supplier-payments-root .metric-value {
        color: var(--sp-heading);
        font-size: clamp(16px, 1.7vw, 21px);
        font-weight: 700;
        line-height: 1.4;
        margin-top: 5px;
        overflow-wrap: anywhere;
        font-variant-numeric: tabular-nums;
    }

    .supplier-payments-root .metric-icon {
        width: 42px;
        height: 42px;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--sp-radius);
        font-size: 17px;
    }

    .supplier-payments-root .metric-icon-success {
        background: rgba(49, 206, 54, 0.11);
        color: var(--sp-success);
    }

    .supplier-payments-root .metric-icon-dark {
        background: rgba(42, 47, 91, 0.08);
        color: var(--sp-heading);
    }

    .supplier-payments-root .metric-icon-primary {
        background: rgba(21, 114, 232, 0.10);
        color: var(--sp-primary);
    }

    .supplier-payments-root .metric-icon-info {
        background: rgba(72, 171, 247, 0.12);
        color: var(--sp-info);
    }

    /* Filter Panel */

    .supplier-payments-root .filter-card {
        overflow: visible;
    }

    .supplier-payments-root .filter-card .card-body {
        padding: 14px;
    }

    .supplier-payments-root .form-control,
    .supplier-payments-root .form-select,
    .supplier-payments-root .input-group-text {
        min-height: 36px;
        border-radius: var(--sp-radius) !important;
        border-color: #e6e9ed;
        font-size: 12px;
        box-shadow: none;
    }

    .supplier-payments-root .form-control,
    .supplier-payments-root .form-select {
        color: var(--sp-text);
    }

    .supplier-payments-root .form-control::placeholder {
        color: #adb5bd;
        opacity: 1;
    }

    .supplier-payments-root .form-control:focus,
    .supplier-payments-root .form-select:focus {
        border-color: var(--sp-primary);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, 0.10);
    }

    .supplier-payments-root .input-group-text {
        padding: 6px 11px;
        background: #f8f9fa;
        color: var(--sp-muted);
    }

    .supplier-payments-root .filter-search .form-control {
        min-width: 0;
    }

    .supplier-payments-root .filter-actions .btn {
        min-width: 70px;
    }

    /* Alerts */

    .supplier-payments-root .alert {
        border-radius: var(--sp-radius);
        padding: 12px 15px;
        font-size: 12.5px;
    }

    /* Table */

    .supplier-payments-root .payment-table-wrap {
        width: 100%;
        overflow-x: auto !important;
        overflow-y: visible !important;
        -webkit-overflow-scrolling: touch;
    }

    .supplier-payments-root .payment-table {
        width: 100%;
        min-width: 1160px;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 12px;
        vertical-align: middle;
    }

    .supplier-payments-root .payment-table thead th {
        background: #f8f9fa;
        color: #737b84;
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: 0.35px;
        text-transform: uppercase;
        padding: 12px 9px;
        white-space: nowrap;
        border-bottom: 1px solid var(--sp-border);
    }

    .supplier-payments-root .payment-table tbody td {
        padding: 10px 9px;
        border-color: #edf0f2;
        white-space: nowrap;
        color: var(--sp-text);
    }

    .supplier-payments-root .payment-table tbody tr:hover {
        background: #fafbfd;
    }

    .supplier-payments-root .payment-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .supplier-payments-root .payment-table .supplier-info {
        min-width: 150px;
        white-space: normal;
        line-height: 1.5;
    }

    .supplier-payments-root .payment-table .supplier-name {
        color: var(--sp-heading);
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    .supplier-payments-root .payment-table .supplier-company {
        color: var(--sp-muted);
        font-size: 11px;
        overflow-wrap: anywhere;
    }

    .supplier-payments-root .payment-table .voucher-link {
        color: #28a745;
        font-weight: 700;
        text-decoration: none;
    }

    .supplier-payments-root .payment-table .voucher-link:hover {
        text-decoration: underline;
    }

    .supplier-payments-root .payment-table .reference-text {
        display: block;
        color: var(--sp-muted);
        font-size: 10.5px;
        margin-top: 3px;
    }

    .supplier-payments-root .payment-table .money-cell {
        font-variant-numeric: tabular-nums;
    }

    /* Payment Method Badges */

    .supplier-payments-root .method-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 8px;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 4px;
        color: #59616a;
        font-size: 10.5px;
        font-weight: 600;
        white-space: nowrap;
    }

    /* Status Badges */

    .supplier-payments-root .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 5px 8px !important;
        border-radius: 4px;
        font-size: 10.5px !important;
        font-weight: 600;
        white-space: nowrap;
        line-height: 1.3;
    }

    /* Action Dropdown */

    .supplier-payments-root .action-dropdown .btn {
        min-height: 29px;
        padding: 4px 9px;
        background: #fff;
        border-color: #e5e8eb;
        color: #495057;
        font-size: 11px;
    }

    .supplier-payments-root .action-dropdown .btn:hover,
    .supplier-payments-root .action-dropdown .btn:focus {
        background: #f8f9fa;
        border-color: #cfd5da;
        color: var(--sp-heading);
    }

    .supplier-payments-root .action-dropdown .dropdown-menu {
        min-width: 175px;
        padding: 5px;
        border: 1px solid var(--sp-border);
        border-radius: var(--sp-radius);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.09);
        font-size: 12px;
        z-index: 1055;
    }

    .supplier-payments-root .action-dropdown .dropdown-item {
        padding: 8px 10px;
        border-radius: 4px;
    }

    .supplier-payments-root .action-dropdown .dropdown-item:hover {
        background: #f4f6f9;
    }

    /* Empty State */

    .supplier-payments-root .empty-state {
        padding: 45px 15px !important;
        white-space: normal !important;
    }

    .supplier-payments-root .empty-state-icon {
        width: 55px;
        height: 55px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f3f5f8;
        color: #9ba3af;
        border-radius: 50%;
        font-size: 22px;
    }

    .supplier-payments-root .empty-state-title {
        color: var(--sp-heading);
        font-size: 13px;
        font-weight: 700;
        margin-top: 12px;
    }

    .supplier-payments-root .empty-state-description {
        color: var(--sp-muted);
        font-size: 11.5px;
        line-height: 1.6;
        margin-top: 4px;
    }

    /* Pagination */

    .supplier-payments-root .pagination-footer {
        padding: 12px 15px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }

    .supplier-payments-root .pagination-footer .pagination {
        margin-bottom: 0;
    }

    .supplier-payments-root .pagination .page-link {
        color: var(--sp-primary);
        border-color: #e9ecef;
        font-size: 11.5px;
    }

    .supplier-payments-root .pagination .page-item.active .page-link {
        background: var(--sp-primary);
        border-color: var(--sp-primary);
        color: #fff;
    }

    .supplier-payments-root .pagination .page-link:focus {
        box-shadow: 0 0 0 3px rgba(21, 114, 232, 0.10);
    }

    /* Tablet */

    @media (max-width: 991.98px) {
        .supplier-payments-root .metric-card {
            padding: 13px;
        }

        .supplier-payments-root .metric-icon {
            width: 36px;
            height: 36px;
            font-size: 15px;
        }

        .supplier-payments-root .filter-card .card-body {
            padding: 12px;
        }
    }

    /* Mobile */

    @media (max-width: 575.98px) {
        .supplier-payments-root {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        .supplier-payments-root .page-heading {
            font-size: 15px;
        }

        .supplier-payments-root .heading-icon {
            width: 32px;
            height: 32px;
            font-size: 14px;
        }

        .supplier-payments-root .page-header-actions {
            width: 100%;
        }

        .supplier-payments-root .page-header-actions .btn {
            flex: 1;
            font-size: 11px;
            padding: 6px 8px;
        }

        .supplier-payments-root .metric-card {
            padding: 11px;
        }

        .supplier-payments-root .metric-label {
            font-size: 10.5px;
        }

        .supplier-payments-root .metric-value {
            font-size: 15px;
        }

        .supplier-payments-root .metric-icon {
            width: 31px;
            height: 31px;
            font-size: 13px;
        }

        .supplier-payments-root .filter-card .card-body {
            padding: 11px;
        }

        .supplier-payments-root .filter-actions {
            width: 100%;
        }

        .supplier-payments-root .filter-actions .btn {
            flex: 1;
        }

        .supplier-payments-root .pagination-footer {
            flex-direction: column;
            align-items: flex-start;
        }

        .supplier-payments-root .pagination-footer > div {
            max-width: 100%;
            overflow-x: auto;
        }
    }

    @media (max-width: 359.98px) {
        .supplier-payments-root .page-header {
            align-items: flex-start !important;
        }

        .supplier-payments-root .page-header-actions {
            flex-direction: column;
        }

        .supplier-payments-root .metric-card {
            padding: 9px;
        }

        .supplier-payments-root .metric-value {
            font-size: 13px;
        }

        .supplier-payments-root .metric-icon {
            width: 27px;
            height: 27px;
            font-size: 11px;
        }
    }

    /* Print */

    @media print {
        .supplier-payments-root .page-header-actions,
        .supplier-payments-root .filter-card,
        .supplier-payments-root .action-dropdown,
        .supplier-payments-root .pagination-footer {
            display: none !important;
        }

        .supplier-payments-root {
            padding: 0 !important;
        }

        .supplier-payments-root .card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
            margin-bottom: 12px !important;
            break-inside: avoid;
        }

        .supplier-payments-root .payment-table-wrap {
            overflow: visible !important;
        }

        .supplier-payments-root .payment-table {
            min-width: 0 !important;
            width: 100% !important;
            font-size: 9px !important;
        }

        .supplier-payments-root .payment-table thead th,
        .supplier-payments-root .payment-table tbody td {
            padding: 5px 4px !important;
            white-space: normal !important;
            overflow-wrap: anywhere;
            border: 1px solid #dee2e6 !important;
        }

        .supplier-payments-root .payment-table .method-badge,
        .supplier-payments-root .payment-table .status-badge {
            white-space: normal !important;
        }

        .supplier-payments-root .metric-card {
            break-inside: avoid;
        }

        .supplier-payments-root .card-header,
        .supplier-payments-root .card-body,
        .supplier-payments-root .card-footer {
            background: #fff !important;
        }
    }
</style>

<div class="container-fluid px-3 py-3 supplier-payments-root">

    {{-- Page Header --}}
    <div class="page-header d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">

        <div class="d-flex align-items-center gap-2">
            <span class="heading-icon">
                <i class="fas fa-money-check-dollar"></i>
            </span>

            <h5 class="page-heading mb-0">
                Supplier Payments &amp; Disbursements
            </h5>
        </div>

        <div class="page-header-actions d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print Register
            </button>

            <a href="{{ route('admin.supplier-payments.create') }}" class="btn btn-sm btn-success">
                <i class="fas fa-plus-circle me-1"></i> New Payment
            </a>
        </div>
    </div>

    {{-- Success Alert --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3">
            <div class="d-flex align-items-center gap-2 fw-semibold">
                <i class="fas fa-circle-check fa-lg text-success"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Error Alert --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3">
            <div class="d-flex align-items-center gap-2 fw-semibold">
                <i class="fas fa-circle-exclamation fa-lg text-danger"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Financial Metrics --}}
    <div class="row g-3 mb-3">

        {{-- Total Vouchers --}}
        <div class="col-6 col-md-3">
            <div class="card metric-card mb-0">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="min-w-0">
                        <div class="metric-label">Total Vouchers</div>
                        <h4 class="metric-value mb-0">
                            {{ number_format($totalVouchersCount) }}
                        </h4>
                    </div>

                    <div class="metric-icon metric-icon-success">
                        <i class="fas fa-receipt"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Disbursed --}}
        <div class="col-6 col-md-3">
            <div class="card metric-card mb-0">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="min-w-0">
                        <div class="metric-label">Total Disbursed</div>
                        <h4 class="metric-value text-success mb-0 font-monospace">
                            ৳{{ number_format($totalDisbursedVal, 2) }}
                        </h4>
                    </div>

                    <div class="metric-icon metric-icon-dark">
                        <i class="fas fa-coins"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Allocated --}}
        <div class="col-6 col-md-3">
            <div class="card metric-card mb-0">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="min-w-0">
                        <div class="metric-label">Allocated to Bills</div>
                        <h4 class="metric-value text-primary mb-0 font-monospace">
                            ৳{{ number_format($totalSettledInvoicesVal, 2) }}
                        </h4>
                    </div>

                    <div class="metric-icon metric-icon-primary">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Advance --}}
        <div class="col-6 col-md-3">
            <div class="card metric-card mb-0">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="min-w-0">
                        <div class="metric-label">Advance Surplus</div>
                        <h4 class="metric-value text-info mb-0 font-monospace">
                            ৳{{ number_format($totalAdvanceSurplusVal, 2) }}
                        </h4>
                    </div>

                    <div class="metric-icon metric-icon-info">
                        <i class="fas fa-hand-holding-dollar"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Filters & Payment Register --}}
    <div class="card filter-card mb-3">

        {{-- Filter Toolbar --}}
        <div class="card-body border-bottom">

            <form action="{{ route('admin.supplier-payments.index') }}" method="GET" class="row g-2 align-items-center">

                {{-- Search --}}
                <div class="col-12 col-md-3">
                    <div class="input-group input-group-sm filter-search">
                        <span class="input-group-text border-end-0">
                            <i class="fas fa-search"></i>
                        </span>

                        <input type="text" name="search" class="form-control border-start-0"
                               placeholder="Search voucher, cheque, reference..."
                               value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Supplier --}}
                <div class="col-6 col-md-2">
                    <select name="supplier_id" class="form-select form-select-sm">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers ?? [] as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }} ({{ $supplier->company_name ?? 'Vendor' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Payment Method --}}
                <div class="col-6 col-md-2">
                    <select name="payment_method" class="form-select form-select-sm">
                        <option value="">All Payment Modes</option>
                        <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash in Hand</option>
                        <option value="bank_transfer" {{ request('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer / EFT</option>
                        <option value="cheque" {{ request('payment_method') === 'cheque' ? 'selected' : '' }}>Cheque / Pay Order</option>
                        <option value="mobile_banking" {{ request('payment_method') === 'mobile_banking' ? 'selected' : '' }}>Mobile Banking (MFS)</option>
                    </select>
                </div>

                {{-- Status --}}
                <div class="col-6 col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed / Disbursed</option>
                        <option value="posted" {{ request('status') === 'posted' ? 'selected' : '' }}>Posted to Ledger</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Clearance</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Void / Cancelled</option>
                    </select>
                </div>

                {{-- Date --}}
                <div class="col-6 col-md-1">
                    <input type="date" name="payment_date" class="form-control form-control-sm"
                           value="{{ request('payment_date') }}" title="Payment Date">
                </div>

                {{-- Filter Actions --}}
                <div class="col-12 col-md-2 d-flex gap-2 justify-content-end filter-actions">

                    <button type="submit" class="btn btn-sm btn-dark flex-grow-1">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>

                    @if(request()->anyFilled(['search', 'supplier_id', 'payment_method', 'status', 'payment_date']))
                        <a href="{{ route('admin.supplier-payments.index') }}"
                           class="btn btn-sm btn-outline-danger"
                           title="Clear Filters">
                            <i class="fas fa-rotate-left"></i>
                        </a>
                    @endif

                </div>
            </form>
        </div>

        {{-- Supplier Payments Table --}}
        <div class="payment-table-wrap table-responsive">

            <table class="table table-hover align-middle payment-table">

                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 140px;">Voucher #</th>
                        <th style="width: 110px;">Date</th>
                        <th style="width: 180px;">Beneficiary Supplier</th>
                        <th style="width: 140px;">Payment Channel</th>
                        <th style="width: 150px;">Drawer Account</th>
                        <th class="text-end" style="width: 130px;">Disbursed (৳)</th>
                        <th class="text-end" style="width: 130px;">Allocated (৳)</th>
                        <th class="text-end" style="width: 120px;">Advance (৳)</th>
                        <th class="text-center" style="width: 130px;">Status</th>
                        <th class="text-end pe-3" style="width: 120px;">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($paymentsList as $payment)
                        @php
                            $paidAmt = (float) ($payment->amount ?? 0);
                            $allocatedAmt = (float) ($payment->allocated_amount ?? 0);
                            $advanceAmt = max(0, $paidAmt - $allocatedAmt);
                            $voucherNo = $payment->voucher_number ?? ($payment->payment_number ?? ('SPV-' . $payment->id));
                        @endphp

                        <tr>
                            {{-- Voucher --}}
                            <td class="ps-3 fw-bold">
                                <a href="{{ route('admin.supplier-payments.show', $payment->id) }}" class="voucher-link font-monospace">
                                    {{ $voucherNo }}
                                </a>

                                @if(!empty($payment->reference_no))
                                    <small class="reference-text font-monospace">
                                        Ref: {{ $payment->reference_no }}
                                    </small>
                                @endif
                            </td>

                            {{-- Date --}}
                            <td class="text-muted">
                                {{ !empty($payment->payment_date) ? \Carbon\Carbon::parse($payment->payment_date)->format('d M, Y') : '—' }}
                            </td>

                            {{-- Supplier --}}
                            <td class="supplier-info">
                                <div class="supplier-name">
                                    {{ $payment->supplier_name ?? ($payment->supplier->name ?? '—') }}
                                </div>

                                <small class="supplier-company d-block">
                                    {{ $payment->supplier_company ?? ($payment->supplier->company_name ?? '') }}
                                </small>
                            </td>

                            {{-- Payment Channel --}}
                            <td>
                                <span class="method-badge font-monospace">
                                    @switch($payment->payment_method)
                                        @case('cash')
                                            <i class="fas fa-money-bill-wave text-success"></i> Cash
                                            @break
                                        @case('bank_transfer')
                                            <i class="fas fa-building-columns text-primary"></i> Wire Transfer
                                            @break
                                        @case('cheque')
                                            <i class="fas fa-money-check text-warning-emphasis"></i> Cheque
                                            @break
                                        @case('mobile_banking')
                                            <i class="fas fa-mobile-screen-button text-info"></i> MFS
                                            @break
                                        @default
                                            {{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'cash')) }}
                                    @endswitch
                                </span>
                            </td>

                            {{-- Account --}}
                            <td class="text-secondary">
                                <i class="fas fa-vault me-1 text-muted"></i>{{ $payment->bank_account_name ?? ($payment->bankAccount->account_name ?? 'Cash in Hand') }}
                            </td>

                            {{-- Disbursed --}}
                            <td class="text-end fw-bold text-success money-cell font-monospace">
                                ৳{{ number_format($paidAmt, 2) }}
                            </td>

                            {{-- Allocated --}}
                            <td class="text-end fw-semibold text-dark money-cell font-monospace">
                                ৳{{ number_format($allocatedAmt, 2) }}
                            </td>

                            {{-- Advance --}}
                            <td class="text-end fw-bold money-cell font-monospace {{ $advanceAmt > 0 ? 'text-primary' : 'text-muted' }}">
                                {{ $advanceAmt > 0 ? '৳' . number_format($advanceAmt, 2) : '—' }}
                            </td>

                            {{-- Status --}}
                            <td class="text-center">
                                @switch($payment->status ?? 'completed')
                                    @case('completed')
                                    @case('posted')
                                    @case('approved')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle status-badge">
                                            <i class="fas fa-circle-check"></i> Disbursed
                                        </span>
                                        @break

                                    @case('pending')
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle status-badge">
                                            <i class="fas fa-clock"></i> Pending
                                        </span>
                                        @break

                                    @case('cancelled')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle status-badge">
                                            <i class="fas fa-ban"></i> Void
                                        </span>
                                        @break

                                    @default
                                        <span class="badge bg-light text-dark border status-badge">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                @endswitch
                            </td>

                            {{-- Actions --}}
                            <td class="text-end pe-3">
                                <div class="dropdown action-dropdown">
                                    <button class="btn btn-sm btn-light border dropdown-toggle"
                                            type="button"
                                            data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                        Manage
                                    </button>

                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.supplier-payments.show', $payment->id) }}">
                                                <i class="fas fa-eye text-primary me-2"></i> View Money Receipt
                                            </a>
                                        </li>

                                        @if(!empty($payment->supplier_id) && Route::has('admin.suppliers.ledger'))
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.suppliers.ledger', $payment->supplier_id) }}">
                                                    <i class="fas fa-book-open text-secondary me-2"></i> Supplier Ledger
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="10" class="text-center empty-state">

                                <div class="empty-state-icon">
                                    <i class="fas fa-money-check-dollar"></i>
                                </div>

                                <div class="empty-state-title">
                                    No Payment Records Found
                                </div>

                                <div class="empty-state-description">
                                    Supplier payments will appear here once recorded.
                                    Record a cash, cheque or bank payment against supplier bills.
                                </div>

                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if(method_exists($paymentsList, 'hasPages') && $paymentsList->hasPages())
            <div class="card-footer pagination-footer">

                <small class="text-muted">
                    Showing {{ $paymentsList->firstItem() ?? 0 }} to {{ $paymentsList->lastItem() ?? 0 }} of {{ $paymentsList->total() }} payment vouchers
                </small>

                <div>
                    {{ $paymentsList->links() }}
                </div>

            </div>
        @endif

    </div>
</div>

{{-- Print Stylesheet --}}
<style>
    @media print {
        .admin-dark-sidebar,
        .admin-navbar,
        .btn,
        .dropdown,
        .card-header form {
            display: none !important;
        }

        .container-fluid {
            padding: 0 !important;
        }

        .supplier-payments-root .card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
            margin-bottom: 12px !important;
        }

        .supplier-payments-root table {
            width: 100% !important;
            border-collapse: collapse !important;
        }

        .supplier-payments-root th,
        .supplier-payments-root td {
            border: 1px solid #dee2e6 !important;
        }
    }
</style>
@endsection
