@extends('layouts.admin')

@section('title', 'Purchase Bills')

@section('admin_content')
<style>
    /* =========================================================
       Purchase Bills Register — Kaiadmin Classic UI
    ========================================================= */
    .purchase-bills-page {
        --pb-border: #e7eaf0;
        --pb-muted: #6c757d;
        --pb-radius: 8px;
        color: #343a40;
    }

    .purchase-bills-page .pb-page-header {
        gap: 12px;
        margin-bottom: 22px;
    }

    .purchase-bills-page .pb-page-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 19px;
        font-weight: 700;
        color: #202938;
        margin: 0;
    }

    .purchase-bills-page .pb-page-title-icon {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: rgba(var(--bs-primary-rgb), .09);
        color: var(--bs-primary);
        font-size: 16px;
    }

    .purchase-bills-page .pb-header-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }

    .purchase-bills-page .btn {
        border-radius: 7px;
        font-weight: 600;
        transition: all .2s ease;
    }

    .purchase-bills-page .pb-header-actions .btn {
        min-height: 36px;
        padding: 7px 12px;
    }

    /* Summary cards */
    .purchase-bills-page .pb-metric-card {
        height: 100%;
        min-height: 112px;
        padding: 17px;
        border: 1px solid var(--pb-border);
        border-radius: var(--pb-radius);
        background: #fff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .035);
        transition: box-shadow .2s ease, transform .2s ease;
    }

    .purchase-bills-page .pb-metric-card:hover {
        box-shadow: 0 5px 16px rgba(0, 0, 0, .065);
        transform: translateY(-2px);
    }

    .purchase-bills-page .pb-metric-label {
        color: var(--pb-muted);
        font-size: 12px;
        font-weight: 600;
        line-height: 1.5;
        margin-bottom: 7px;
    }

    .purchase-bills-page .pb-metric-value {
        color: #202938;
        font-size: clamp(17px, 1.5vw, 22px);
        font-weight: 700;
        line-height: 1.3;
        margin: 0;
        overflow-wrap: anywhere;
    }

    .purchase-bills-page .pb-metric-icon {
        width: 43px;
        height: 43px;
        flex: 0 0 43px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        font-size: 17px;
    }

    /* Main register card */
    .purchase-bills-page .pb-register-card {
        border: 1px solid var(--pb-border);
        border-radius: var(--pb-radius);
        background: #fff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .035);
        overflow: visible;
    }

    .purchase-bills-page .pb-filter-header {
        padding: 16px;
        border-bottom: 1px solid var(--pb-border);
        border-radius: var(--pb-radius) var(--pb-radius) 0 0;
        background: #fff;
    }

    .purchase-bills-page .pb-filter-form {
        row-gap: 10px;
    }

    .purchase-bills-page .form-control,
    .purchase-bills-page .form-select,
    .purchase-bills-page .input-group-text {
        min-height: 36px;
        border-color: #dfe3e9;
        font-size: 12.5px;
    }

    .purchase-bills-page .form-control:focus,
    .purchase-bills-page .form-select:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 .15rem rgba(var(--bs-primary-rgb), .12);
    }

    .purchase-bills-page .input-group-text {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
    }

    .purchase-bills-page .pb-filter-actions {
        gap: 7px;
    }

    .purchase-bills-page .pb-filter-actions .btn {
        min-height: 36px;
    }

    /* Table */
    .purchase-bills-page .pb-table-wrapper {
        width: 100%;
        overflow-x: auto;
        overflow-y: visible;
        -webkit-overflow-scrolling: touch;
    }

    .purchase-bills-page .pb-bills-table {
        width: 100%;
        min-width: 1160px;
        margin: 0;
        font-size: 12.5px;
        border-collapse: separate;
        border-spacing: 0;
    }

    .purchase-bills-page .pb-bills-table thead th {
        padding: 13px 10px;
        background: #f8f9fb;
        color: #687385;
        border-bottom: 1px solid var(--pb-border);
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .45px;
        text-transform: uppercase;
        white-space: nowrap;
        vertical-align: middle;
    }

    .purchase-bills-page .pb-bills-table tbody td {
        padding: 12px 10px;
        border-bottom: 1px solid #edf0f3;
        vertical-align: middle;
    }

    .purchase-bills-page .pb-bills-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .purchase-bills-page .pb-bills-table tbody tr {
        transition: background-color .15s ease;
    }

    .purchase-bills-page .pb-bills-table tbody tr:hover {
        background: #fafbfd;
    }

    .purchase-bills-page .pb-bills-table .pb-bill-link {
        font-weight: 700;
        font-size: 12.5px;
    }

    .purchase-bills-page .pb-secondary-text {
        display: block;
        margin-top: 4px;
        color: #8a93a2;
        font-size: 10.5px;
        line-height: 1.45;
    }

    .purchase-bills-page .pb-invoice-badge {
        display: inline-block;
        max-width: 145px;
        overflow: hidden;
        text-overflow: ellipsis;
        vertical-align: middle;
        padding: 5px 7px;
        border-radius: 5px;
        font-size: 10.5px;
    }

    .purchase-bills-page .pb-status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        border-radius: 5px;
        padding: 5px 8px;
        font-size: 10.5px;
        font-weight: 700;
        line-height: 1.2;
    }

    .purchase-bills-page .pb-overdue-badge {
        display: block;
        width: fit-content;
        margin: 5px auto 0;
        padding: 4px 7px;
        border-radius: 4px;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .3px;
    }

    /* Manage dropdown */
    .purchase-bills-page .pb-manage-btn {
        min-width: 88px;
        min-height: 32px;
        padding: 5px 9px;
        border-radius: 6px;
        background: #fff;
        font-size: 11.5px;
        font-weight: 600;
    }

    .purchase-bills-page .dropdown-menu {
        min-width: 205px;
        padding: 6px;
        border: 1px solid var(--pb-border);
        border-radius: 8px;
        box-shadow: 0 7px 22px rgba(0, 0, 0, .11);
        font-size: 12px;
        z-index: 1055;
    }

    .purchase-bills-page .dropdown-item {
        display: flex;
        align-items: center;
        gap: 2px;
        padding: 8px 10px;
        border-radius: 5px;
        white-space: normal;
        line-height: 1.4;
    }

    .purchase-bills-page .dropdown-item:hover {
        background: #f4f6f9;
    }

    .purchase-bills-page .dropdown-divider {
        margin: 5px 0;
    }

    /* Empty state */
    .purchase-bills-page .pb-empty-state {
        padding: 55px 18px !important;
        text-align: center;
    }

    .purchase-bills-page .pb-empty-state-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 65px;
        height: 65px;
        margin-bottom: 15px;
        border-radius: 50%;
        background: #f1f3f6;
        color: #9aa3af;
        font-size: 25px;
    }

    .purchase-bills-page .pb-empty-state-title {
        color: #343d4b;
        font-size: 14px;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .purchase-bills-page .pb-empty-state-description {
        color: #8992a0;
        font-size: 12px;
        line-height: 1.6;
    }

    /* Pagination */
    .purchase-bills-page .pb-pagination-footer {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        border-top: 1px solid var(--pb-border);
        border-radius: 0 0 var(--pb-radius) var(--pb-radius);
        background: #fff;
    }

    .purchase-bills-page .pb-pagination-footer .pagination {
        margin-bottom: 0;
    }

    .purchase-bills-page .pb-pagination-footer .page-link {
        border-radius: 5px;
        margin: 0 2px;
        font-size: 12px;
    }

    /* Responsive */
    @media (max-width: 767.98px) {
        .purchase-bills-page {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        .purchase-bills-page .pb-page-header {
            align-items: flex-start !important;
        }

        .purchase-bills-page .pb-page-title {
            font-size: 17px;
        }

        .purchase-bills-page .pb-header-actions {
            width: 100%;
        }

        .purchase-bills-page .pb-header-actions .btn {
            flex: 1 1 auto;
            justify-content: center;
        }

        .purchase-bills-page .pb-metric-card {
            min-height: 100px;
            padding: 12px;
        }

        .purchase-bills-page .pb-metric-icon {
            width: 34px;
            height: 34px;
            flex-basis: 34px;
            font-size: 14px;
        }

        .purchase-bills-page .pb-metric-label {
            font-size: 10.5px;
        }

        .purchase-bills-page .pb-metric-value {
            font-size: 15px;
        }

        .purchase-bills-page .pb-filter-header {
            padding: 12px;
        }

        .purchase-bills-page .pb-filter-actions {
            width: 100%;
        }

        .purchase-bills-page .pb-pagination-footer {
            align-items: flex-start;
        }

        .purchase-bills-page .pb-pagination-footer > div {
            max-width: 100%;
            overflow-x: auto;
        }
    }

    @media (max-width: 380px) {
        .purchase-bills-page .pb-metric-card {
            padding: 10px;
        }

        .purchase-bills-page .pb-metric-value {
            font-size: 13px;
        }

        .purchase-bills-page .pb-metric-icon {
            display: none;
        }

        .purchase-bills-page .pb-header-actions .btn {
            font-size: 11px;
            padding-left: 8px;
            padding-right: 8px;
        }
    }

    /* Print */
    @media print {
        .admin-dark-sidebar,
        .admin-navbar,
        .purchase-bills-page .btn,
        .purchase-bills-page .dropdown,
        .purchase-bills-page .pb-filter-header {
            display: none !important;
        }

        .purchase-bills-page {
            padding: 0 !important;
        }

        .purchase-bills-page .pb-register-card,
        .purchase-bills-page .pb-metric-card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
            margin-bottom: 12px !important;
        }

        .purchase-bills-page .pb-table-wrapper {
            overflow: visible !important;
        }

        .purchase-bills-page .pb-bills-table {
            width: 100% !important;
            min-width: 0 !important;
            font-size: 9px !important;
        }

        .purchase-bills-page .pb-bills-table th,
        .purchase-bills-page .pb-bills-table td {
            border: 1px solid #dee2e6 !important;
            padding: 5px !important;
        }

        .purchase-bills-page .pb-pagination-footer {
            display: none !important;
        }
    }
</style>

<div class="container-fluid px-3 py-3 purchase-bills-page">

    {{-- Page Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between pb-page-header">
        <div>
            <h5 class="pb-page-title">
                <span class="pb-page-title-icon">
                    <i class="fas fa-file-invoice"></i>
                </span>
                Purchase Bills
            </h5>
        </div>

        <div class="pb-header-actions">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print Register
            </button>

            <a href="{{ route('admin.purchases.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus-circle me-1"></i> New Purchase Bill
            </a>
        </div>
    </div>

    {{-- Financial Metrics --}}
    @php
        $totalInvoicesCount = $purchases->total() ?? 0;
        $totalInvoicedVal = (float) ($totalBilledAmount ?? ($purchases->sum('grand_total') ?? 0));
        $totalSettledVal = (float) ($totalPaidAmount ?? ($purchases->sum('paid_amount') ?? 0));
        $totalOutstandingDue = (float) ($totalDueAmount ?? max(0, $totalInvoicedVal - $totalSettledVal));
    @endphp

    <div class="row g-3 mb-4">

        <div class="col-6 col-md-3">
            <div class="pb-metric-card">
                <div class="d-flex align-items-center justify-content-between gap-2 h-100">
                    <div class="min-w-0">
                        <div class="pb-metric-label">Total Invoices</div>
                        <h4 class="pb-metric-value">{{ number_format($totalInvoicesCount) }}</h4>
                    </div>
                    <div class="pb-metric-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-receipt"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="pb-metric-card">
                <div class="d-flex align-items-center justify-content-between gap-2 h-100">
                    <div class="min-w-0">
                        <div class="pb-metric-label">Total Invoiced Amount</div>
                        <h4 class="pb-metric-value font-monospace">৳{{ number_format($totalInvoicedVal, 2) }}</h4>
                    </div>
                    <div class="pb-metric-icon bg-dark bg-opacity-10 text-dark">
                        <i class="fas fa-coins"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="pb-metric-card">
                <div class="d-flex align-items-center justify-content-between gap-2 h-100">
                    <div class="min-w-0">
                        <div class="pb-metric-label">Disbursed / Paid to Date</div>
                        <h4 class="pb-metric-value text-success font-monospace">৳{{ number_format($totalSettledVal, 2) }}</h4>
                    </div>
                    <div class="pb-metric-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-money-check-dollar"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="pb-metric-card">
                <div class="d-flex align-items-center justify-content-between gap-2 h-100">
                    <div class="min-w-0">
                        <div class="pb-metric-label">Outstanding Payables Due</div>
                        <h4 class="pb-metric-value text-danger font-monospace">৳{{ number_format($totalOutstandingDue, 2) }}</h4>
                    </div>
                    <div class="pb-metric-icon bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-circle-exclamation"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Register & Filters --}}
    <div class="pb-register-card mb-3">

        {{-- Filter Toolbar --}}
        <div class="pb-filter-header">
            <form action="{{ route('admin.purchases.index') }}" method="GET" class="row g-2 align-items-center pb-filter-form">

                <div class="col-12 col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control bg-light border-start-0"
                               placeholder="Bill #, Supplier Inv #, PO #, Notes..."
                               value="{{ request('search') }}">
                    </div>
                </div>

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

                <div class="col-6 col-md-2">
                    <select name="warehouse_id" class="form-select form-select-sm">
                        <option value="">All Warehouses</option>
                        @foreach($warehouses ?? [] as $wh)
                            <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                {{ $wh->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <select name="payment_status" class="form-select form-select-sm">
                        <option value="">All Payment States</option>
                        <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid in Full</option>
                        <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>Partially Paid</option>
                        <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid Liability</option>
                        <option value="overdue" {{ request('payment_status') === 'overdue' ? 'selected' : '' }}>Overdue Bills</option>
                    </select>
                </div>

                <div class="col-6 col-md-1">
                    <input type="date" name="purchase_date" class="form-control form-control-sm"
                           value="{{ request('purchase_date') }}" title="Bill Date">
                </div>

                <div class="col-12 col-md-2 d-flex pb-filter-actions">
                    <button type="submit" class="btn btn-sm btn-dark flex-grow-1">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>

                    @if(request()->anyFilled(['search', 'supplier_id', 'warehouse_id', 'payment_status', 'purchase_date']))
                        <a href="{{ route('admin.purchases.index') }}" class="btn btn-sm btn-outline-danger"
                           title="Clear Filters">
                            <i class="fas fa-rotate-left"></i>
                        </a>
                    @endif
                </div>

            </form>
        </div>

        {{-- Purchase Bills Table --}}
        <div class="pb-table-wrapper">
            <table class="table table-hover align-middle pb-bills-table">
                <thead>
                    <tr>
                        <th class="ps-3" style="width: 140px;">Bill #</th>
                        <th style="width: 110px;">Date</th>
                        <th>Supplier / Vendor</th>
                        <th style="width: 130px;">Supplier Inv #</th>
                        <th style="width: 120px;">Warehouse</th>
                        <th class="text-end" style="width: 120px;">Total (৳)</th>
                        <th class="text-end" style="width: 120px;">Paid (৳)</th>
                        <th class="text-end" style="width: 120px;">Due (৳)</th>
                        <th class="text-center" style="width: 130px;">Payment Status</th>
                        <th class="text-end pe-3" style="width: 120px;">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($purchases as $purchase)
                        @php
                            $grandTotal = (float) $purchase->grand_total;
                            $paidAmount = (float) $purchase->paid_amount;
                            $dueAmount  = max(0, $grandTotal - $paidAmount);
                            $isOverdue  = $dueAmount > 0 && $purchase->due_date && \Carbon\Carbon::parse($purchase->due_date)->isPast();
                        @endphp

                        <tr>
                            <td class="ps-3 fw-bold">
                                <a href="{{ route('admin.purchases.show', $purchase->id) }}"
                                   class="text-primary text-decoration-none font-monospace pb-bill-link">
                                    {{ $purchase->purchase_number }}
                                </a>

                                @if($purchase->purchase_order_id)
                                    <small class="pb-secondary-text font-monospace">
                                        PO: #{{ $purchase->po_number ?? ($purchase->purchaseOrder->po_number ?? $purchase->purchase_order_id) }}
                                    </small>
                                @endif
                            </td>

                            <td class="text-muted">
                                <div>{{ !empty($purchase->purchase_date) ? \Carbon\Carbon::parse($purchase->purchase_date)->format('d M, Y') : '—' }}</div>

                                @if($purchase->due_date)
                                    <small class="{{ $isOverdue ? 'text-danger fw-semibold' : 'text-muted' }} pb-secondary-text">
                                        Due: {{ \Carbon\Carbon::parse($purchase->due_date)->format('d M, Y') }}
                                    </small>
                                @endif
                            </td>

                            <td>
                                <div class="fw-semibold text-dark">{{ $purchase->supplier->name ?? '—' }}</div>
                                <small class="pb-secondary-text">{{ $purchase->supplier->company_name ?? '' }}</small>
                            </td>

                            <td>
                                <span class="badge bg-light text-secondary border font-monospace pb-invoice-badge">
                                    {{ $purchase->supplier_invoice_no ?? '—' }}
                                </span>
                            </td>

                            <td class="text-secondary small">
                                <i class="fas fa-warehouse me-1 text-muted"></i>{{ $purchase->warehouse->name ?? 'Main' }}
                            </td>

                            <td class="text-end fw-bold text-dark font-monospace">
                                ৳{{ number_format($grandTotal, 2) }}
                            </td>

                            <td class="text-end text-success fw-semibold font-monospace">
                                ৳{{ number_format($paidAmount, 2) }}
                            </td>

                            <td class="text-end fw-bold font-monospace {{ $dueAmount > 0 ? 'text-danger' : 'text-muted' }}">
                                ৳{{ number_format($dueAmount, 2) }}
                            </td>

                            <td class="text-center">
                                @if($dueAmount <= 0 && $grandTotal > 0)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle pb-status-badge">
                                        <i class="fas fa-circle-check me-1"></i> Paid
                                    </span>
                                @elseif($paidAmount > 0)
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle pb-status-badge">
                                        <i class="fas fa-clock-rotate-left me-1"></i> Partial
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle pb-status-badge">
                                        <i class="fas fa-circle-exclamation me-1"></i> Unpaid
                                    </span>
                                @endif

                                @if($isOverdue)
                                    <span class="badge bg-danger text-white pb-overdue-badge">
                                        OVERDUE
                                    </span>
                                @endif
                            </td>

                            <td class="text-end pe-3">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border dropdown-toggle pb-manage-btn"
                                            type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Manage
                                    </button>

                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">

                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.purchases.show', $purchase->id) }}">
                                                <i class="fas fa-eye text-primary me-2"></i> View Bill Details
                                            </a>
                                        </li>

                                        @if($dueAmount > 0 && Route::has('admin.supplier-payments.create'))
                                            <li>
                                                <a class="dropdown-item text-success" href="{{ route('admin.supplier-payments.create', ['purchase_id' => $purchase->id, 'supplier_id' => $purchase->supplier_id, 'due_amount' => $dueAmount]) }}">
                                                    <i class="fas fa-money-bill-wave text-success me-2"></i> Disburse Payment
                                                </a>
                                            </li>
                                        @endif

                                        @if(Route::has('admin.purchase-returns.create'))
                                            <li>
                                                <a class="dropdown-item text-danger" href="{{ route('admin.purchase-returns.create', ['purchase_id' => $purchase->id, 'supplier_id' => $purchase->supplier_id]) }}">
                                                    <i class="fas fa-arrow-rotate-left text-danger me-2"></i> Return Items (RTV)
                                                </a>
                                            </li>
                                        @endif

                                        @if(Route::has('admin.supplier-debit-credit-notes.create'))
                                            <li>
                                                <a class="dropdown-item text-warning-emphasis" href="{{ route('admin.supplier-debit-credit-notes.create', ['purchase_id' => $purchase->id, 'supplier_id' => $purchase->supplier_id, 'note_type' => 'debit_note']) }}">
                                                    <i class="fas fa-note-sticky text-warning-emphasis me-2"></i> Issue Debit Note (Dr)
                                                </a>
                                            </li>
                                        @endif

                                        <li><hr class="dropdown-divider"></li>

                                        @php
                                            $assetUrl = Route::has('admin.procurement-assets.create')
                                                ? route('admin.procurement-assets.create', ['purchase_id' => $purchase->id, 'supplier_id' => $purchase->supplier_id])
                                                : (Route::has('admin.asset-procurements.create') ? route('admin.asset-procurements.create', ['purchase_id' => $purchase->id, 'supplier_id' => $purchase->supplier_id]) : null);
                                        @endphp

                                        @if($assetUrl)
                                            <li>
                                                <a class="dropdown-item text-secondary" href="{{ $assetUrl }}">
                                                    <i class="fas fa-boxes-packing text-secondary me-2"></i> Capitalize as Asset
                                                </a>
                                            </li>
                                        @endif

                                        @if(!empty($purchase->supplier_id) && Route::has('admin.suppliers.ledger'))
                                            <li>
                                                <a class="dropdown-item text-secondary" href="{{ route('admin.suppliers.ledger', $purchase->supplier_id) }}">
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
                            <td colspan="10" class="pb-empty-state text-muted">
                                <div class="pb-empty-state-icon">
                                    <i class="fas fa-file-invoice"></i>
                                </div>

                                <div class="pb-empty-state-title">
                                    No commercial purchase bills found.
                                </div>

                                <small class="pb-empty-state-description">
                                    Create a new purchase bill or convert approved Purchase Orders / Inward GRNs.
                                </small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Footer --}}
        @if($purchases->hasPages())
            <div class="pb-pagination-footer">
                <small class="text-muted">
                    Showing {{ $purchases->firstItem() ?? 0 }} to {{ $purchases->lastItem() ?? 0 }} of {{ $purchases->total() }} purchase invoices
                </small>

                <div>
                    {{ $purchases->links() }}
                </div>
            </div>
        @endif

    </div>
</div>
@endsection