@extends('layouts.admin')

@section('title', 'Purchase Bill #' . ($purchase->purchase_number ?? 'Details'))

@section('admin_content')
<style>
    /* =========================================================
       Purchase Bill Details — Kaiadmin Classic / White Classic
    ========================================================= */
    .purchase-bill-details {
        --pbd-border: #e7eaf0;
        --pbd-muted: #778190;
        --pbd-radius: 8px;
        color: #343a40;
    }

    .purchase-bill-details .pbd-header {
        gap: 14px;
        margin-bottom: 20px;
    }

    .purchase-bill-details .pbd-title-wrap {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        min-width: 0;
    }

    .purchase-bill-details .pbd-page-title {
        display: flex;
        align-items: center;
        gap: 9px;
        margin: 0;
        color: #202938;
        font-size: 18px;
        font-weight: 700;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    .purchase-bill-details .pbd-title-icon {
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: rgba(var(--bs-primary-rgb), .09);
        color: var(--bs-primary);
        font-size: 16px;
    }

    .purchase-bill-details .pbd-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 9px;
        border-radius: 6px;
        font-size: 10.5px;
        font-weight: 700;
        line-height: 1.35;
        white-space: normal;
    }

    .purchase-bill-details .pbd-action-group {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 7px;
    }

    .purchase-bill-details .btn {
        border-radius: 7px;
        font-weight: 600;
        transition: all .2s ease;
    }

    .purchase-bill-details .pbd-action-group > .btn {
        min-height: 35px;
        padding: 7px 11px;
        font-size: 11.5px;
    }

    /* Common cards */
    .purchase-bill-details .pbd-card {
        height: 100%;
        border: 1px solid var(--pbd-border);
        border-radius: var(--pbd-radius);
        background: #fff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .035);
        overflow: hidden;
    }

    .purchase-bill-details .pbd-card-header {
        min-height: 44px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 11px 15px;
        border-bottom: 1px solid var(--pbd-border);
        background: #fff;
    }

    .purchase-bill-details .pbd-card-heading {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        color: #596474;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .45px;
        text-transform: uppercase;
    }

    .purchase-bill-details .pbd-card-heading i {
        color: var(--bs-primary);
        font-size: 13px;
    }

    .purchase-bill-details .pbd-card-body {
        padding: 16px;
    }

    /* Supplier */
    .purchase-bill-details .pbd-supplier-name {
        color: #202938;
        font-size: 15px;
        font-weight: 700;
        margin-bottom: 4px;
        overflow-wrap: anywhere;
    }

    .purchase-bill-details .pbd-company-name {
        color: var(--pbd-muted);
        font-size: 12px;
        margin-bottom: 15px;
    }

    .purchase-bill-details .pbd-contact-line {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        margin-bottom: 9px;
        color: #6f7a89;
        font-size: 12px;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    .purchase-bill-details .pbd-contact-line:last-child {
        margin-bottom: 0;
    }

    .purchase-bill-details .pbd-contact-line i {
        width: 14px;
        flex: 0 0 14px;
        margin-top: 2px;
        color: #9aa3b1;
        text-align: center;
    }

    .purchase-bill-details .pbd-statement-link {
        color: var(--bs-primary);
        font-size: 11px;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
    }

    .purchase-bill-details .pbd-statement-link:hover {
        text-decoration: underline;
    }

    /* Reference rows */
    .purchase-bill-details .pbd-info-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid #edf0f3;
        font-size: 11.5px;
        line-height: 1.5;
    }

    .purchase-bill-details .pbd-info-row:first-child {
        padding-top: 0;
    }

    .purchase-bill-details .pbd-info-row:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }

    .purchase-bill-details .pbd-info-label {
        flex: 0 0 auto;
        color: var(--pbd-muted);
    }

    .purchase-bill-details .pbd-info-value {
        min-width: 0;
        color: #303b4b;
        font-weight: 600;
        text-align: right;
        overflow-wrap: anywhere;
    }

    .purchase-bill-details .pbd-info-value a {
        text-decoration: none;
    }

    .purchase-bill-details .pbd-info-value a:hover {
        text-decoration: underline;
    }

    .purchase-bill-details .pbd-document-btn {
        padding: 4px 8px;
        border-radius: 5px;
        font-size: 10.5px;
    }

    /* Product table */
    .purchase-bill-details .pbd-section-card {
        border: 1px solid var(--pbd-border);
        border-radius: var(--pbd-radius);
        background: #fff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .035);
        overflow: hidden;
    }

    .purchase-bill-details .pbd-section-header {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 15px 17px;
        border-bottom: 1px solid var(--pbd-border);
        background: #fff;
    }

    .purchase-bill-details .pbd-section-title {
        margin: 0;
        color: #263142;
        font-size: 13px;
        font-weight: 700;
    }

    .purchase-bill-details .pbd-table-wrapper {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .purchase-bill-details .pbd-items-table {
        width: 100%;
        min-width: 1120px;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 12px;
    }

    .purchase-bill-details .pbd-items-table thead th {
        padding: 12px 10px;
        background: #f8f9fb;
        color: #697586;
        border-bottom: 1px solid var(--pbd-border);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .4px;
        text-transform: uppercase;
        white-space: nowrap;
        vertical-align: middle;
    }

    .purchase-bill-details .pbd-items-table tbody td {
        padding: 12px 10px;
        border-bottom: 1px solid #edf0f3;
        vertical-align: middle;
    }

    .purchase-bill-details .pbd-items-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .purchase-bill-details .pbd-items-table tbody tr:hover {
        background: #fafbfd;
    }

    .purchase-bill-details .pbd-product-name {
        color: #263142;
        font-weight: 700;
        line-height: 1.45;
    }

    .purchase-bill-details .pbd-variant-text {
        display: block;
        margin-top: 4px;
        color: #8791a0;
        font-size: 10.5px;
    }

    .purchase-bill-details .pbd-sku-badge {
        display: inline-block;
        max-width: 120px;
        padding: 5px 7px;
        overflow: hidden;
        text-overflow: ellipsis;
        border-radius: 5px;
        font-size: 10.5px;
        vertical-align: middle;
    }

    .purchase-bill-details .pbd-empty-row {
        padding: 35px 15px !important;
        color: #8992a0;
        text-align: center;
    }

    /* Financial area */
    .purchase-bill-details .pbd-financial-area {
        padding: 18px;
        background: #f8f9fb;
        border-top: 1px solid var(--pbd-border);
    }

    .purchase-bill-details .pbd-notes-heading {
        margin-bottom: 8px;
        color: #667181;
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .45px;
        text-transform: uppercase;
    }

    .purchase-bill-details .pbd-notes-box {
        padding: 12px;
        margin-bottom: 12px;
        border: 1px solid var(--pbd-border);
        border-radius: 7px;
        background: #fff;
        color: #687385;
        font-size: 11.5px;
        line-height: 1.65;
        overflow-wrap: anywhere;
    }

    .purchase-bill-details .pbd-system-notice {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        padding: 12px;
        border: 1px solid var(--pbd-border);
        border-radius: 7px;
        background: #fff;
        color: #788393;
        font-size: 11px;
        line-height: 1.6;
    }

    .purchase-bill-details .pbd-system-notice i {
        margin-top: 2px;
        color: var(--bs-primary);
    }

    .purchase-bill-details .pbd-summary-card {
        padding: 16px;
        border: 1px solid var(--pbd-border);
        border-radius: 8px;
        background: #fff;
    }

    .purchase-bill-details .pbd-summary-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 11px;
        font-size: 11.5px;
    }

    .purchase-bill-details .pbd-summary-row:last-child {
        margin-bottom: 0;
    }

    .purchase-bill-details .pbd-summary-label {
        color: #778190;
    }

    .purchase-bill-details .pbd-summary-value {
        color: #303b4b;
        font-weight: 600;
        text-align: right;
        overflow-wrap: anywhere;
    }

    .purchase-bill-details .pbd-summary-divider {
        margin: 13px 0;
        border-color: #e8ebf0;
        opacity: 1;
    }

    .purchase-bill-details .pbd-grand-total {
        margin-bottom: 13px;
        font-size: 13px;
    }

    .purchase-bill-details .pbd-grand-total .pbd-summary-value {
        color: var(--bs-primary);
        font-size: 19px;
        font-weight: 800;
    }

    .purchase-bill-details .pbd-due-row {
        padding-top: 11px;
        margin-top: 11px;
        border-top: 1px solid #edf0f3;
        font-size: 12px;
    }

    .purchase-bill-details .pbd-due-row .pbd-summary-value {
        font-size: 17px;
        font-weight: 800;
    }

    /* Dropdown */
    .purchase-bill-details .dropdown-menu {
        min-width: 210px;
        padding: 6px;
        border: 1px solid var(--pbd-border);
        border-radius: 8px;
        box-shadow: 0 7px 22px rgba(0, 0, 0, .11);
        font-size: 12px;
        z-index: 1055;
    }

    .purchase-bill-details .dropdown-item {
        padding: 8px 10px;
        border-radius: 5px;
        white-space: normal;
    }

    .purchase-bill-details .dropdown-item:hover {
        background: #f4f6f9;
    }

    /* Responsive */
    @media (max-width: 991.98px) {
        .purchase-bill-details .pbd-header {
            align-items: flex-start !important;
        }

        .purchase-bill-details .pbd-action-group {
            width: 100%;
        }
    }

    @media (max-width: 767.98px) {
        .purchase-bill-details {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        .purchase-bill-details .pbd-page-title {
            font-size: 16px;
        }

        .purchase-bill-details .pbd-header {
            gap: 12px;
        }

        .purchase-bill-details .pbd-action-group {
            gap: 6px;
        }

        .purchase-bill-details .pbd-action-group > .btn {
            flex: 1 1 auto;
            justify-content: center;
        }

        .purchase-bill-details .pbd-card-body {
            padding: 13px;
        }

        .purchase-bill-details .pbd-financial-area {
            padding: 13px;
        }

        .purchase-bill-details .pbd-summary-card {
            padding: 13px;
        }

        .purchase-bill-details .pbd-info-row {
            gap: 8px;
        }
    }

    @media (max-width: 420px) {
        .purchase-bill-details .pbd-title-wrap {
            align-items: flex-start;
        }

        .purchase-bill-details .pbd-title-icon {
            width: 33px;
            height: 33px;
            flex-basis: 33px;
        }

        .purchase-bill-details .pbd-status {
            font-size: 9.5px;
            padding: 5px 7px;
        }

        .purchase-bill-details .pbd-action-group > .btn {
            font-size: 10.5px;
            padding: 6px 8px;
        }

        .purchase-bill-details .pbd-info-row {
            font-size: 10.5px;
        }

        .purchase-bill-details .pbd-summary-row {
            font-size: 10.5px;
        }
    }

    /* Print */
    @media print {
        .admin-dark-sidebar,
        .admin-navbar,
        .purchase-bill-details .btn,
        .purchase-bill-details .dropdown {
            display: none !important;
        }

        .purchase-bill-details {
            padding: 0 !important;
        }

        .purchase-bill-details .pbd-card,
        .purchase-bill-details .pbd-section-card,
        .purchase-bill-details .pbd-summary-card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
            break-inside: avoid;
        }

        .purchase-bill-details .pbd-table-wrapper {
            overflow: visible !important;
        }

        .purchase-bill-details .pbd-items-table {
            width: 100% !important;
            min-width: 0 !important;
            font-size: 9px !important;
        }

        .purchase-bill-details .pbd-items-table th,
        .purchase-bill-details .pbd-items-table td {
            border: 1px solid #dee2e6 !important;
            padding: 5px !important;
        }

        .purchase-bill-details .pbd-financial-area {
            background: #fff !important;
        }
    }
</style>

<div class="container-fluid px-3 py-3 purchase-bill-details">

    {{-- Page Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between pbd-header">

        <div class="pbd-title-wrap">
            <h5 class="pbd-page-title">
                <span class="pbd-title-icon">
                    <i class="fas fa-file-invoice"></i>
                </span>
                Purchase Bill #{{ $purchase->purchase_number }}
            </h5>

            {{-- Payment Status Badge --}}
            @php
                $grandTotal = (float) ($purchase->grand_total ?? 0);
                $paidAmount = (float) ($purchase->paid_amount ?? 0);
                $dueAmount  = max(0, $grandTotal - $paidAmount);
            @endphp

            @if($dueAmount <= 0 && $grandTotal > 0)
                <span class="badge bg-success text-white pbd-status">
                    <i class="fas fa-check-circle"></i> Paid in Full
                </span>
            @elseif($paidAmount > 0)
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle pbd-status">
                    <i class="fas fa-clock-rotate-left"></i>
                    Partially Paid (Due: ৳{{ number_format($dueAmount, 2) }})
                </span>
            @else
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle pbd-status">
                    <i class="fas fa-circle-exclamation"></i> Unpaid Liability
                </span>
            @endif

            @if(!empty($purchase->status))
                <span class="badge bg-light text-secondary border pbd-status text-uppercase font-monospace">
                    {{ str_replace('_', ' ', $purchase->status) }}
                </span>
            @endif
        </div>

        <div class="pbd-action-group">
            <a href="{{ route('admin.purchases.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Bills
            </a>

            <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print Bill
            </button>

            @if($dueAmount > 0 && Route::has('admin.supplier-payments.create'))
                <a href="{{ route('admin.supplier-payments.create', ['purchase_id' => $purchase->id, 'supplier_id' => $purchase->supplier_id]) }}"
                   class="btn btn-sm btn-success fw-semibold">
                    <i class="fas fa-money-check-dollar me-1"></i> Disburse Payment
                </a>
            @endif

            <div class="dropdown">
                <button class="btn btn-sm btn-outline-dark dropdown-toggle" type="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                    Linked Workflows
                </button>

                <ul class="dropdown-menu dropdown-menu-end">

                    @if(Route::has('admin.purchase-returns.create'))
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.purchase-returns.create', ['purchase_id' => $purchase->id, 'supplier_id' => $purchase->supplier_id]) }}">
                                <i class="fas fa-arrow-rotate-left text-danger me-2"></i> Initiate Return (RTV)
                            </a>
                        </li>
                    @endif

                    @if(Route::has('admin.supplier-debit-credit-notes.create'))
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.supplier-debit-credit-notes.create', ['purchase_id' => $purchase->id, 'supplier_id' => $purchase->supplier_id, 'note_type' => 'debit_note']) }}">
                                <i class="fas fa-minus-circle text-warning me-2"></i> Issue Debit Note (Dr)
                            </a>
                        </li>
                    @endif

                    @php
                        $assetRoute = Route::has('admin.procurement-assets.create')
                            ? route('admin.procurement-assets.create', ['purchase_id' => $purchase->id, 'supplier_id' => $purchase->supplier_id])
                            : (Route::has('admin.asset-procurements.create') ? route('admin.asset-procurements.create', ['purchase_id' => $purchase->id, 'supplier_id' => $purchase->supplier_id]) : '#');
                    @endphp

                    @if($assetRoute !== '#')
                        <li>
                            <a class="dropdown-item" href="{{ $assetRoute }}">
                                <i class="fas fa-boxes-packing text-primary me-2"></i> Capitalize as Fixed Asset
                            </a>
                        </li>
                    @endif

                </ul>
            </div>
        </div>
    </div>

    {{-- Meta Overview Cards --}}
    <div class="row g-3 mb-4">

        {{-- Supplier Information Card --}}
        <div class="col-12 col-md-4">
            <div class="pbd-card">
                <div class="pbd-card-header">
                    <h6 class="pbd-card-heading">
                        <i class="fas fa-truck-field"></i> Vendor / Supplier
                    </h6>

                    @if(!empty($purchase->supplier->id) && Route::has('admin.suppliers.ledger'))
                        <a href="{{ route('admin.suppliers.ledger', $purchase->supplier->id) }}"
                           class="pbd-statement-link">
                            Statement <i class="fas fa-arrow-up-right-from-square fa-xs"></i>
                        </a>
                    @endif
                </div>

                <div class="pbd-card-body">
                    <h6 class="pbd-supplier-name">{{ $purchase->supplier->name ?? 'N/A' }}</h6>
                    <div class="pbd-company-name">{{ $purchase->supplier->company_name ?? 'General Supplier' }}</div>

                    <div class="pbd-contact-line">
                        <i class="fas fa-phone"></i>
                        <span>{{ $purchase->supplier->phone ?? '—' }}</span>
                    </div>

                    <div class="pbd-contact-line">
                        <i class="fas fa-envelope"></i>
                        <span>{{ $purchase->supplier->email ?? '—' }}</span>
                    </div>

                    @if(!empty($purchase->supplier->address))
                        <div class="pbd-contact-line">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>{{ $purchase->supplier->address }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Invoice & Logistics Context --}}
        <div class="col-12 col-md-4">
            <div class="pbd-card">
                <div class="pbd-card-header">
                    <h6 class="pbd-card-heading">
                        <i class="fas fa-file-invoice"></i> Commercial References
                    </h6>
                </div>

                <div class="pbd-card-body">
                    <div class="pbd-info-row">
                        <span class="pbd-info-label">Bill Date:</span>
                        <span class="pbd-info-value">{{ !empty($purchase->purchase_date) ? \Carbon\Carbon::parse($purchase->purchase_date)->format('d M, Y') : '—' }}</span>
                    </div>

                    <div class="pbd-info-row">
                        <span class="pbd-info-label">Supplier Invoice #:</span>
                        <span class="pbd-info-value font-monospace">{{ $purchase->supplier_invoice_no ?? '—' }}</span>
                    </div>

                    <div class="pbd-info-row">
                        <span class="pbd-info-label">Maturity / Due Date:</span>
                        <span class="pbd-info-value">{{ !empty($purchase->due_date) ? \Carbon\Carbon::parse($purchase->due_date)->format('d M, Y') : 'Immediate' }}</span>
                    </div>

                    <div class="pbd-info-row">
                        <span class="pbd-info-label">Target Warehouse:</span>
                        <span class="pbd-info-value">{{ $purchase->warehouse->name ?? 'Central Warehouse' }}</span>
                    </div>

                    <div class="pbd-info-row">
                        <span class="pbd-info-label">Attached Document:</span>
                        <span class="pbd-info-value">
                            @if(!empty($purchase->attachment_file))
                                <a href="{{ asset('storage/' . $purchase->attachment_file) }}" target="_blank"
                                   class="btn btn-sm btn-outline-primary pbd-document-btn">
                                    <i class="fas fa-file-pdf me-1"></i> View Scan
                                </a>
                            @else
                                <span class="text-muted">None</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sourcing & Cross-Links --}}
        <div class="col-12 col-md-4">
            <div class="pbd-card">
                <div class="pbd-card-header">
                    <h6 class="pbd-card-heading">
                        <i class="fas fa-network-wired"></i> Procurement Sourcing
                    </h6>
                </div>

                <div class="pbd-card-body">
                    <div class="pbd-info-row">
                        <span class="pbd-info-label">Purchase Order (PO):</span>
                        <span class="pbd-info-value">
                            @if(!empty($purchase->purchase_order_id) && Route::has('admin.purchase-orders.show'))
                                <a href="{{ route('admin.purchase-orders.show', $purchase->purchase_order_id) }}"
                                   class="fw-bold text-primary font-monospace">
                                    #{{ $purchase->purchaseOrder->po_number ?? $purchase->purchase_order_id }}
                                </a>
                            @else
                                <span class="text-muted">Direct Bill</span>
                            @endif
                        </span>
                    </div>

                    <div class="pbd-info-row">
                        <span class="pbd-info-label">Goods Received (GRN):</span>
                        <span class="pbd-info-value">
                            @if(!empty($purchase->purchase_receipt_id) && Route::has('admin.purchase-receipts.show'))
                                <a href="{{ route('admin.purchase-receipts.show', $purchase->purchase_receipt_id) }}"
                                   class="fw-bold text-success font-monospace">
                                    #{{ $purchase->purchaseReceipt->receipt_number ?? $purchase->purchase_receipt_id }}
                                </a>
                            @else
                                <span class="text-muted">Direct Inward</span>
                            @endif
                        </span>
                    </div>

                    <div class="pbd-info-row">
                        <span class="pbd-info-label">Operating Branch:</span>
                        <span class="pbd-info-value">{{ $purchase->branch->name ?? 'HQ Branch' }}</span>
                    </div>

                    <div class="pbd-info-row">
                        <span class="pbd-info-label">Payment Mode:</span>
                        <span class="pbd-info-value text-uppercase font-monospace">{{ str_replace('_', ' ', $purchase->payment_method ?? 'cash') }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Line Items Table --}}
    <div class="pbd-section-card mb-3">

        <div class="pbd-section-header">
            <i class="fas fa-boxes-stacked text-primary"></i>
            <h6 class="pbd-section-title">Billed Material &amp; Product Line Items</h6>
        </div>

        <div class="pbd-table-wrapper">
            <table class="table table-bordered table-hover align-middle pbd-items-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 45px;">#</th>
                        <th>Product Description</th>
                        <th style="width: 130px;">SKU / Code</th>
                        <th style="width: 110px;">Batch #</th>
                        <th style="width: 110px;">Expiry Date</th>
                        <th class="text-center" style="width: 90px;">Billed Qty</th>
                        <th class="text-end" style="width: 120px;">Unit Price (৳)</th>
                        <th class="text-end" style="width: 95px;">Discount (৳)</th>
                        <th class="text-end" style="width: 95px;">Tax (৳)</th>
                        <th class="text-end pe-3" style="width: 130px;">Line Total (৳)</th>
                    </tr>
                </thead>

                <tbody>
                    @php $calculatedItemsSubtotal = 0; @endphp

                    @forelse($items as $index => $item)
                        @php
                            $qty = (float) ($item->quantity ?? 1);
                            $unitCost = (float) ($item->unit_cost ?? $item->unit_price ?? $item->rate ?? 0);
                            $discount = (float) ($item->discount ?? 0);
                            $tax = (float) ($item->tax ?? 0);
                            $lineTotal = (float) ($item->total ?? (($qty * $unitCost) - $discount + $tax));
                            $calculatedItemsSubtotal += $lineTotal;
                        @endphp

                        <tr>
                            <td class="text-center text-muted font-monospace">{{ $index + 1 }}</td>

                            <td>
                                <div class="pbd-product-name">{{ $item->product_name ?? 'Product #' . $item->product_id }}</div>

                                @if(!empty($item->variant_color) || !empty($item->variant_size))
                                    <small class="pbd-variant-text">
                                        Variant: {{ $item->variant_color ?? '' }} {{ $item->variant_size ?? '' }}
                                    </small>
                                @endif
                            </td>

                            <td>
                                <span class="badge bg-light text-secondary border font-monospace pbd-sku-badge">
                                    {{ $item->product_sku ?? ($item->sku ?? '—') }}
                                </span>
                            </td>

                            <td class="font-monospace text-muted small">
                                {{ $item->batch_no ?? '—' }}
                            </td>

                            <td class="text-muted small">
                                {{ !empty($item->expiry_date) ? \Carbon\Carbon::parse($item->expiry_date)->format('d M, Y') : '—' }}
                            </td>

                            <td class="text-center fw-bold text-dark font-monospace">
                                {{ number_format($qty, 0) }}
                            </td>

                            <td class="text-end font-monospace">
                                ৳{{ number_format($unitCost, 2) }}
                            </td>

                            <td class="text-end text-muted font-monospace">
                                {{ $discount > 0 ? '-৳' . number_format($discount, 2) : '—' }}
                            </td>

                            <td class="text-end text-muted font-monospace">
                                {{ $tax > 0 ? '+৳' . number_format($tax, 2) : '—' }}
                            </td>

                            <td class="text-end pe-3 fw-bold text-dark font-monospace">
                                ৳{{ number_format($lineTotal, 2) }}
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="10" class="pbd-empty-row">
                                No product lines found on this commercial purchase bill.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Financial Calculation & Notes Breakdown --}}
        <div class="pbd-financial-area">
            <div class="row g-3 justify-content-between">

                <div class="col-12 col-md-6">
                    @if(!empty($purchase->notes))
                        <h6 class="pbd-notes-heading">Invoice Particulars &amp; Terms</h6>

                        <div class="pbd-notes-box">
                            {{ $purchase->notes }}
                        </div>
                    @endif

                    <div class="pbd-system-notice">
                        <i class="fas fa-shield-halved"></i>
                        <span>
                            Stock valuation journal entries and inventory ledger records have been synchronized for this transaction.
                        </span>
                    </div>
                </div>

                <div class="col-12 col-md-5 col-lg-4">
                    <div class="pbd-summary-card">

                        <div class="pbd-summary-row">
                            <span class="pbd-summary-label">Items Subtotal:</span>
                            <span class="pbd-summary-value font-monospace">৳{{ number_format($calculatedItemsSubtotal, 2) }}</span>
                        </div>

                        @if((float)($purchase->discount_amount ?? 0) > 0)
                            <div class="pbd-summary-row">
                                <span class="pbd-summary-label">Global Discount:</span>
                                <span class="pbd-summary-value text-danger font-monospace">-৳{{ number_format((float)$purchase->discount_amount, 2) }}</span>
                            </div>
                        @endif

                        @if((float)($purchase->tax_amount ?? 0) > 0)
                            <div class="pbd-summary-row">
                                <span class="pbd-summary-label">Global VAT / Tax:</span>
                                <span class="pbd-summary-value font-monospace">+৳{{ number_format((float)$purchase->tax_amount, 2) }}</span>
                            </div>
                        @endif

                        @if((float)($purchase->shipping_cost ?? 0) > 0)
                            <div class="pbd-summary-row">
                                <span class="pbd-summary-label">Shipping &amp; Freight:</span>
                                <span class="pbd-summary-value font-monospace">+৳{{ number_format((float)$purchase->shipping_cost, 2) }}</span>
                            </div>
                        @endif

                        @if((float)($purchase->round_off ?? 0) != 0)
                            <div class="pbd-summary-row">
                                <span class="pbd-summary-label">Round Off:</span>
                                <span class="pbd-summary-value text-muted font-monospace">৳{{ number_format((float)$purchase->round_off, 2) }}</span>
                            </div>
                        @endif

                        <hr class="pbd-summary-divider">

                        <div class="pbd-summary-row pbd-grand-total">
                            <span class="fw-bold text-dark">Grand Total:</span>
                            <span class="pbd-summary-value font-monospace">৳{{ number_format($grandTotal, 2) }}</span>
                        </div>

                        <div class="pbd-summary-row">
                            <span class="pbd-summary-label">Amount Paid:</span>
                            <span class="pbd-summary-value text-success font-monospace">৳{{ number_format($paidAmount, 2) }}</span>
                        </div>

                        <div class="pbd-summary-row pbd-due-row">
                            <span class="fw-bold text-dark">Remaining Due:</span>
                            <span class="pbd-summary-value font-monospace {{ $dueAmount > 0 ? 'text-danger' : 'text-muted' }}">
                                ৳{{ number_format($dueAmount, 2) }}
                            </span>
                        </div>

                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection