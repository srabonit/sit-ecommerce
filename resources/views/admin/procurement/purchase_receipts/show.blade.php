@extends('layouts.admin')

@section('title', 'GRN #' . $purchaseReceipt->receipt_number)

@section('admin_content')
<div class="container-fluid px-3 py-3 grn-details-page">

    {{-- Breadcrumb & Action Header --}}
    <div class="grn-page-header d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div class="grn-page-heading">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <h5 class="fw-bold text-dark mb-0">
                    <i class="fas fa-truck-ramp-box text-success me-2"></i>Goods Received Note #{{ $purchaseReceipt->receipt_number }}
                </h5>

                {{-- Inspection / QC Status Badge --}}
                @switch($purchaseReceipt->status)
                    @case('received')
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 grn-status-badge">
                            <i class="fas fa-circle-check me-1"></i> Stock Accepted &amp; Posted
                        </span>
                        @break
                    @case('pending_qc')
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 grn-status-badge">
                            <i class="fas fa-microscope me-1"></i> Quarantined / Pending QC
                        </span>
                        @break
                    @case('rejected')
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 grn-status-badge">
                            <i class="fas fa-ban me-1"></i> Rejected Consignment
                        </span>
                        @break
                    @default
                        <span class="badge bg-light text-dark border px-2 py-1 grn-status-badge">
                            {{ ucfirst(str_replace('_', ' ', $purchaseReceipt->status)) }}
                        </span>
                @endswitch

                <span class="badge bg-light text-secondary border px-2 py-1 font-monospace grn-status-badge">
                    Challan: {{ $purchaseReceipt->challan_no ?? '—' }}
                </span>
            </div>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.purchase-receipts.index') }}">Purchase Receipts</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $purchaseReceipt->receipt_number }}</li>
                </ol>
            </nav>
        </div>

        @php
            $totalDelivered = (float) $purchaseReceipt->items->sum('delivered_quantity');
            $totalAccepted = (float) $purchaseReceipt->items->sum('accepted_quantity');
            $totalRejected = (float) $purchaseReceipt->items->sum('rejected_quantity');
        @endphp

        <div class="grn-header-actions d-flex flex-wrap gap-2">
            <a href="{{ route('admin.purchase-receipts.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Receipts
            </a>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print GRN Slip
            </button>

            {{-- Post to Stock / QC Approval --}}
            @if($purchaseReceipt->status === 'pending_qc')
                <form action="{{ route('admin.purchase-receipts.approve', $purchaseReceipt->id) }}" method="POST" onsubmit="return confirm('Confirm QC approval for GRN #{{ $purchaseReceipt->receipt_number }}? Accepted quantities will be added to warehouse stock.');">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success">
                        <i class="fas fa-circle-check me-1"></i> Approve &amp; Post Stock
                    </button>
                </form>
            @endif

            {{-- Downstream Billing Workflow --}}
            <a href="{{ route('admin.purchases.create', ['grn_id' => $purchaseReceipt->id, 'supplier_id' => $purchaseReceipt->supplier_id]) }}" class="btn btn-sm btn-primary">
                <i class="fas fa-file-invoice-dollar me-1"></i> Convert to Purchase Bill
            </a>

            {{-- Initiate Return (RTV) if items were rejected --}}
            @if($totalRejected > 0)
                <a href="{{ route('admin.purchase-returns.create', ['grn_id' => $purchaseReceipt->id, 'supplier_id' => $purchaseReceipt->supplier_id]) }}" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-arrow-rotate-left me-1"></i> Return Rejected Goods (RTV)
                </a>
            @endif
        </div>
    </div>

    {{-- Meta Overview Cards --}}
    <div class="row g-3 mb-4">

        {{-- Delivering Supplier Card --}}
        <div class="col-12 col-md-4">
            <div class="card grn-info-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold small text-secondary text-uppercase">
                        <i class="fas fa-truck-field me-1"></i> Delivering Vendor
                    </span>
                    @if($purchaseReceipt->supplier)
                        <a href="{{ route('admin.suppliers.ledger', $purchaseReceipt->supplier->id) }}" class="small text-primary text-decoration-none">
                            Statement <i class="fas fa-arrow-up-right-from-square fa-xs"></i>
                        </a>
                    @endif
                </div>
                <div class="card-body p-3">
                    <h6 class="fw-bold text-dark mb-1">{{ $purchaseReceipt->supplier->name ?? 'N/A' }}</h6>
                    <div class="text-secondary small mb-3">{{ $purchaseReceipt->supplier->company_name ?? 'Individual Supplier' }}</div>

                    <div class="grn-contact-detail small text-muted mb-2">
                        <i class="fas fa-phone fa-xs me-2"></i>{{ $purchaseReceipt->supplier->phone ?? '—' }}
                    </div>
                    <div class="grn-contact-detail small text-muted mb-2">
                        <i class="fas fa-envelope fa-xs me-2"></i>{{ $purchaseReceipt->supplier->email ?? '—' }}
                    </div>
                    @if($purchaseReceipt->supplier && $purchaseReceipt->supplier->address)
                        <div class="grn-contact-detail small text-muted">
                            <i class="fas fa-map-marker-alt fa-xs me-2"></i>{{ $purchaseReceipt->supplier->address }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Consignment & Sourcing Reference --}}
        <div class="col-12 col-md-4">
            <div class="card grn-info-card h-100">
                <div class="card-header">
                    <span class="fw-bold small text-secondary text-uppercase">
                        <i class="fas fa-file-contract me-1"></i> Gate Logistics &amp; Source PO
                    </span>
                </div>
                <div class="card-body p-3">
                    <div class="grn-meta-row d-flex justify-content-between small py-2 border-bottom">
                        <span class="text-muted">Inward Date:</span>
                        <span class="fw-semibold text-dark text-end">{{ \Carbon\Carbon::parse($purchaseReceipt->receipt_date)->format('d M, Y') }}</span>
                    </div>
                    <div class="grn-meta-row d-flex justify-content-between small py-2 border-bottom">
                        <span class="text-muted">Delivery Challan #:</span>
                        <span class="fw-bold text-dark font-monospace text-end">{{ $purchaseReceipt->challan_no ?? '—' }}</span>
                    </div>
                    <div class="grn-meta-row d-flex justify-content-between small py-2 border-bottom">
                        <span class="text-muted">Purchase Order (PO):</span>
                        <span class="text-end">
                            @if($purchaseReceipt->purchase_order_id)
                                <a href="{{ route('admin.purchase-orders.show', $purchaseReceipt->purchase_order_id) }}" class="fw-semibold text-primary text-decoration-none">
                                    #{{ $purchaseReceipt->purchaseOrder->po_number ?? $purchaseReceipt->purchase_order_id }}
                                </a>
                            @else
                                <span class="text-muted">Direct Consignment</span>
                            @endif
                        </span>
                    </div>
                    <div class="grn-meta-row d-flex justify-content-between small py-2 border-bottom">
                        <span class="text-muted">Receiving Warehouse:</span>
                        <span class="fw-semibold text-dark text-end">{{ $purchaseReceipt->warehouse->name ?? 'Central Warehouse' }}</span>
                    </div>
                    <div class="grn-meta-row d-flex justify-content-between small py-2 border-bottom">
                        <span class="text-muted">Vehicle / Carrier:</span>
                        <span class="fw-semibold text-dark text-end">{{ $purchaseReceipt->vehicle_number ?? 'Not Specified' }}</span>
                    </div>
                    <div class="grn-meta-row d-flex justify-content-between align-items-center small py-2">
                        <span class="text-muted">Challan Scan / QC File:</span>
                        <span class="text-end">
                            @if($purchaseReceipt->attachment_file)
                                <a href="{{ asset('storage/' . $purchaseReceipt->attachment_file) }}" target="_blank" class="btn btn-sm btn-outline-primary py-1 px-2">
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

        {{-- Inspection & Stock Admission Card --}}
        <div class="col-12 col-md-4">
            <div class="card grn-info-card h-100">
                <div class="card-header">
                    <span class="fw-bold small text-secondary text-uppercase">
                        <i class="fas fa-boxes-stacked me-1"></i> Stock Admission Summary
                    </span>
                </div>
                <div class="card-body p-3">
                    <div class="grn-meta-row d-flex justify-content-between align-items-center small py-2 border-bottom">
                        <span class="text-muted">Total Items Delivered:</span>
                        <span class="fw-bold text-dark fs-6">{{ number_format($totalDelivered, 2) }}</span>
                    </div>
                    <div class="grn-meta-row d-flex justify-content-between align-items-center small py-2 border-bottom">
                        <span class="text-muted">Accepted to Stock:</span>
                        <span class="fw-bold text-success fs-6">{{ number_format($totalAccepted, 2) }}</span>
                    </div>
                    <div class="grn-meta-row d-flex justify-content-between align-items-center small py-2 border-bottom">
                        <span class="text-muted">Rejected / Quarantined:</span>
                        <span class="fw-bold fs-6 {{ $totalRejected > 0 ? 'text-danger' : 'text-muted' }}">
                            {{ number_format($totalRejected, 2) }}
                        </span>
                    </div>
                    <div class="grn-meta-row d-flex justify-content-between align-items-center small py-2 border-bottom">
                        <span class="text-muted">Operating Branch:</span>
                        <span class="fw-semibold text-dark text-end">{{ $purchaseReceipt->branch->name ?? 'HQ Branch' }}</span>
                    </div>
                    <div class="grn-meta-row d-flex justify-content-between align-items-center small py-2">
                        <span class="text-muted">Received By:</span>
                        <span class="fw-semibold text-secondary text-end">{{ $purchaseReceipt->creator->name ?? 'System Staff' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Consignment Line Items Table --}}
    <div class="card grn-section-card mb-4">
        <div class="card-header">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="fas fa-boxes-packing text-success me-2"></i>Consignment Quality Inspection &amp; Stock Admission Breakdown
            </h6>
        </div>

        <div class="table-responsive grn-table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0 grn-items-table">
                <thead class="table-light text-secondary text-uppercase">
                    <tr>
                        <th class="text-center" style="width: 40px;">#</th>
                        <th>Product &amp; Specification</th>
                        <th style="width: 120px;">SKU / Code</th>
                        <th style="width: 120px;">Batch / Lot #</th>
                        <th style="width: 110px;">Expiry Date</th>
                        <th class="text-center" style="width: 110px;">Delivered Qty</th>
                        <th class="text-center" style="width: 110px;">Accepted Qty</th>
                        <th class="text-center" style="width: 110px;">Rejected Qty</th>
                        <th>QC Verdict / Discrepancy Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseReceipt->items as $index => $item)
                        @php
                            $delivered = (float) $item->delivered_quantity;
                            $accepted = (float) $item->accepted_quantity;
                            $rejected = (float) ($item->rejected_quantity ?? max(0, $delivered - $accepted));
                        @endphp
                        <tr>
                            <td class="text-center text-muted">{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $item->product->name ?? ($item->product_name ?? 'Product #' . $item->product_id) }}</div>
                                @if($item->variant)
                                    <small class="text-muted d-block">Variant: {{ $item->variant->name ?? $item->variant->variant_name }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border font-monospace">
                                    {{ $item->sku ?? ($item->product->sku ?? '—') }}
                                </span>
                            </td>
                            <td class="font-monospace text-muted small">
                                {{ $item->batch_no ?? '—' }}
                            </td>
                            <td class="text-muted small">
                                {{ $item->expiry_date ? \Carbon\Carbon::parse($item->expiry_date)->format('d-m-Y') : '—' }}
                            </td>
                            <td class="text-center fw-bold text-dark">
                                {{ number_format($delivered, 2) }}
                            </td>
                            <td class="text-center fw-bold text-success">
                                {{ number_format($accepted, 2) }}
                            </td>
                            <td class="text-center fw-bold {{ $rejected > 0 ? 'text-danger' : 'text-muted' }}">
                                {{ number_format($rejected, 2) }}
                            </td>
                            <td>
                                @if($rejected > 0)
                                    <span class="text-danger small">
                                        <i class="fas fa-triangle-exclamation me-1"></i>{{ $item->remarks ?? 'Quality Rejection' }}
                                    </span>
                                @elseif($item->remarks)
                                    <span class="text-secondary small">{{ $item->remarks }}</span>
                                @else
                                    <span class="text-success small"><i class="fas fa-check me-1"></i>QC Passed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                No items found on this goods received note.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="5" class="text-end text-uppercase">Consignment Totals:</td>
                        <td class="text-center text-dark">{{ number_format($totalDelivered, 2) }}</td>
                        <td class="text-center text-success">{{ number_format($totalAccepted, 2) }}</td>
                        <td class="text-center {{ $totalRejected > 0 ? 'text-danger' : 'text-muted' }}">{{ number_format($totalRejected, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if($purchaseReceipt->notes)
            <div class="card-body p-3 bg-light border-top">
                <h6 class="fw-bold text-secondary small text-uppercase mb-2">Gate Entry &amp; Receiving Particulars</h6>
                <div class="grn-notes-content p-3 bg-white border rounded small text-secondary">
                    {{ $purchaseReceipt->notes }}
                </div>
            </div>
        @endif
    </div>

    {{-- Linked Downstream Commercial Purchase Bills --}}
    @if(isset($purchaseReceipt->purchases) && $purchaseReceipt->purchases->count() > 0)
        <div class="card grn-section-card mb-3">
            <div class="card-header">
                <h6 class="fw-bold mb-0 text-dark small text-uppercase">
                    <i class="fas fa-file-invoice-dollar text-primary me-2"></i>Commercial Purchase Bills Generated from this GRN
                </h6>
            </div>
            <div class="table-responsive grn-table-responsive">
                <table class="table table-sm align-middle mb-0 grn-bills-table">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Bill #</th>
                            <th>Bill Date</th>
                            <th>Vendor Invoice #</th>
                            <th class="text-end">Grand Total (৳)</th>
                            <th class="text-end">Paid (৳)</th>
                            <th class="text-end">Due (৳)</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchaseReceipt->purchases as $bill)
                            @php
                                $billGrand = (float) $bill->grand_total;
                                $billPaid = (float) $bill->paid_amount;
                                $billDue = max(0, $billGrand - $billPaid);
                            @endphp
                            <tr>
                                <td class="ps-3 fw-bold">
                                    <a href="{{ route('admin.purchases.show', $bill->id) }}" class="text-primary text-decoration-none">
                                        #{{ $bill->purchase_number }}
                                    </a>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($bill->purchase_date)->format('d M, Y') }}</td>
                                <td><span class="badge bg-light text-secondary border">{{ $bill->supplier_invoice_no ?? '—' }}</span></td>
                                <td class="text-end fw-semibold">৳{{ number_format($billGrand, 2) }}</td>
                                <td class="text-end text-success">৳{{ number_format($billPaid, 2) }}</td>
                                <td class="text-end fw-bold {{ $billDue > 0 ? 'text-danger' : 'text-muted' }}">৳{{ number_format($billDue, 2) }}</td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('admin.purchases.show', $bill->id) }}" class="btn btn-sm btn-outline-primary py-1 px-2">
                                        View Bill
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

{{-- Page-specific Styles --}}
<style>
    .grn-details-page {
        color: #344054;
        min-width: 0;
    }

    .grn-details-page .grn-page-header {
        min-width: 0;
    }

    .grn-details-page .grn-page-heading {
        min-width: 0;
    }

    .grn-details-page .grn-page-heading h5 {
        font-size: 18px;
        line-height: 1.5;
    }

    .grn-details-page .grn-status-badge {
        font-size: 11px;
        font-weight: 600;
        border-radius: 5px;
        white-space: normal;
    }

    .grn-details-page .breadcrumb {
        font-size: 12px;
    }

    .grn-details-page .breadcrumb-item a {
        color: #6c757d;
        text-decoration: none;
    }

    .grn-details-page .breadcrumb-item a:hover {
        color: var(--bs-primary, #1572e8);
    }

    .grn-details-page .grn-header-actions {
        align-items: center;
    }

    .grn-details-page .grn-header-actions .btn,
    .grn-details-page .grn-header-actions form .btn {
        border-radius: 6px;
        font-weight: 600;
        white-space: nowrap;
        padding: 7px 11px;
    }

    .grn-details-page .grn-info-card,
    .grn-details-page .grn-section-card {
        border: 1px solid #e8edf3 !important;
        border-radius: 8px !important;
        background: #fff;
        box-shadow: 0 2px 8px rgba(20, 35, 60, 0.045) !important;
        overflow: hidden;
    }

    .grn-details-page .grn-info-card .card-header,
    .grn-details-page .grn-section-card .card-header {
        min-height: 45px;
        padding: 12px 16px;
        background: #fff;
        border-bottom: 1px solid #edf0f4;
    }

    .grn-details-page .grn-info-card .card-header {
        gap: 10px;
    }

    .grn-details-page .grn-info-card .card-header span {
        line-height: 1.5;
    }

    .grn-details-page .grn-info-card .card-body {
        padding: 16px;
    }

    .grn-details-page .grn-info-card h6 {
        font-size: 14px;
    }

    .grn-details-page .grn-contact-detail {
        line-height: 1.6;
        overflow-wrap: anywhere;
    }

    .grn-details-page .grn-contact-detail i {
        width: 14px;
        color: #98a2b3;
    }

    .grn-details-page .grn-meta-row {
        gap: 12px;
        line-height: 1.5;
    }

    .grn-details-page .grn-meta-row > span:first-child {
        flex: 1 1 auto;
        min-width: 0;
    }

    .grn-details-page .grn-meta-row > span:last-child {
        flex: 0 1 auto;
        max-width: 62%;
        overflow-wrap: anywhere;
    }

    .grn-details-page .grn-info-card .btn {
        border-radius: 5px;
        font-size: 11px;
        font-weight: 600;
    }

    .grn-details-page .grn-section-card .card-header h6 {
        font-size: 13px;
        line-height: 1.6;
    }

    .grn-details-page .grn-table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .grn-details-page .grn-items-table {
        min-width: 1120px;
        font-size: 12px;
    }

    .grn-details-page .grn-bills-table {
        min-width: 760px;
        font-size: 12px;
    }

    .grn-details-page .grn-items-table thead th,
    .grn-details-page .grn-bills-table thead th {
        padding: 11px 10px;
        color: #667085;
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: 0.35px;
        white-space: nowrap;
        vertical-align: middle;
    }

    .grn-details-page .grn-items-table tbody td,
    .grn-details-page .grn-bills-table tbody td {
        padding: 11px 10px;
        vertical-align: middle;
        border-color: #edf0f4;
    }

    .grn-details-page .grn-items-table tbody tr:hover,
    .grn-details-page .grn-bills-table tbody tr:hover {
        background-color: #f8fafc;
    }

    .grn-details-page .grn-items-table tfoot td {
        padding: 12px 10px;
        border-color: #e4e7ec;
        white-space: nowrap;
    }

    .grn-details-page .grn-items-table .badge,
    .grn-details-page .grn-bills-table .badge {
        font-size: 10.5px;
        font-weight: 600;
        padding: 5px 7px;
        border-radius: 4px;
    }

    .grn-details-page .grn-notes-content {
        line-height: 1.7;
        overflow-wrap: anywhere;
        border-radius: 6px !important;
    }

    .grn-details-page .grn-bills-table .btn {
        border-radius: 5px;
        font-size: 11px;
        font-weight: 600;
    }

    @media (max-width: 991.98px) {
        .grn-details-page .grn-page-header {
            align-items: flex-start !important;
        }

        .grn-details-page .grn-header-actions {
            width: 100%;
        }
    }

    @media (max-width: 767.98px) {
        .grn-details-page {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        .grn-details-page .grn-page-heading h5 {
            font-size: 16px;
        }

        .grn-details-page .grn-header-actions {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            width: 100%;
        }

        .grn-details-page .grn-header-actions > a,
        .grn-details-page .grn-header-actions > button,
        .grn-details-page .grn-header-actions > form {
            width: 100%;
        }

        .grn-details-page .grn-header-actions > form .btn {
            width: 100%;
        }

        .grn-details-page .grn-header-actions .btn {
            min-height: 36px;
            white-space: normal;
        }

        .grn-details-page .grn-info-card .card-header,
        .grn-details-page .grn-section-card .card-header {
            padding: 11px 13px;
        }

        .grn-details-page .grn-info-card .card-body {
            padding: 13px;
        }

        .grn-details-page .grn-meta-row > span:last-child {
            max-width: 58%;
        }
    }

    @media (max-width: 420px) {
        .grn-details-page .grn-page-heading h5 {
            font-size: 15px;
        }

        .grn-details-page .grn-status-badge {
            font-size: 10px;
        }

        .grn-details-page .grn-header-actions {
            grid-template-columns: 1fr;
        }

        .grn-details-page .grn-meta-row {
            font-size: 11px !important;
            gap: 8px;
        }

        .grn-details-page .grn-meta-row > span:last-child {
            max-width: 55%;
        }
    }

    @media print {
        .grn-details-page {
            width: 100% !important;
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
            color: #000 !important;
        }

        .admin-dark-sidebar,
        .admin-navbar,
        .grn-details-page .breadcrumb,
        .grn-details-page .btn,
        .grn-details-page .dropdown,
        .grn-details-page .card-header a,
        .grn-details-page .grn-header-actions {
            display: none !important;
        }

        .grn-details-page .grn-info-card,
        .grn-details-page .grn-section-card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
            margin-bottom: 12px !important;
            break-inside: avoid;
        }

        .grn-details-page .grn-table-responsive {
            overflow: visible !important;
        }

        .grn-details-page .grn-items-table,
        .grn-details-page .grn-bills-table {
            width: 100% !important;
            min-width: 0 !important;
            font-size: 9px !important;
        }

        .grn-details-page table {
            width: 100% !important;
            border-collapse: collapse !important;
        }

        .grn-details-page th,
        .grn-details-page td {
            border: 1px solid #dee2e6 !important;
            padding: 5px !important;
        }

        .grn-details-page .grn-info-card .card-header,
        .grn-details-page .grn-section-card .card-header {
            background: #f8f9fa !important;
        }

        .grn-details-page .grn-page-heading h5 {
            font-size: 16px !important;
        }

        .grn-details-page .row {
            display: flex !important;
        }

        .grn-details-page .col-md-4 {
            width: 33.333333% !important;
        }

        .grn-details-page a {
            color: #000 !important;
            text-decoration: none !important;
        }
    }
</style>
@endsection