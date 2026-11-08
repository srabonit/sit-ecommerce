@extends('layouts.admin')

@section('title', 'Purchase Return #' . $purchaseReturn->return_number)

@section('admin_content')
<div class="container-fluid px-3 py-3 purchase-return-details-page">

    {{-- Breadcrumb & Action Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 prd-header">
        <div class="prd-heading-area">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <h5 class="fw-bold text-dark mb-0">
                    <i class="fas fa-arrow-rotate-left text-danger me-2"></i>Return to Vendor (RTV) #{{ $purchaseReturn->return_number }}
                </h5>

                {{-- Status Badges --}}
                @switch($purchaseReturn->status)
                    @case('pending')
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 prd-status-badge">
                            <i class="fas fa-clock me-1"></i> Pending Approval
                        </span>
                        @break
                    @case('approved')
                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 prd-status-badge">
                            <i class="fas fa-truck-fast me-1"></i> Approved / Dispatched
                        </span>
                        @break
                    @case('completed')
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 prd-status-badge">
                            <i class="fas fa-check-double me-1"></i> Settled &amp; Closed
                        </span>
                        @break
                    @case('cancelled')
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 prd-status-badge">
                            <i class="fas fa-ban me-1"></i> Void / Cancelled
                        </span>
                        @break
                    @default
                        <span class="badge bg-light text-dark border px-2 py-1 prd-status-badge">
                            {{ ucfirst(str_replace('_', ' ', $purchaseReturn->status)) }}
                        </span>
                @endswitch

                <span class="badge bg-light text-secondary border px-2 py-1 prd-status-badge">
                    Settlement: {{ ucfirst(str_replace('_', ' ', $purchaseReturn->settlement_type ?? 'credit_note')) }}
                </span>
            </div>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.purchase-returns.index') }}">Purchase Returns</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $purchaseReturn->return_number }}</li>
                </ol>
            </nav>
        </div>

        <div class="d-flex flex-wrap gap-2 prd-header-actions">
            <a href="{{ route('admin.purchase-returns.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Returns
            </a>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print RTV Note
            </button>

            @if($purchaseReturn->status === 'pending')
                <form action="{{ route('admin.purchase-returns.approve', $purchaseReturn->id) }}" method="POST" onsubmit="return confirm('Approve Return #{{ $purchaseReturn->return_number }}? This will deduct the returned quantities from inventory and adjust the vendor account balance.');">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success">
                        <i class="fas fa-check-circle me-1"></i> Approve &amp; Post Return
                    </button>
                </form>
            @endif

            @if($purchaseReturn->status !== 'cancelled')
                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelReturnModal">
                    <i class="fas fa-ban me-1"></i> Void Return
                </button>
            @endif

            @if($purchaseReturn->supplier)
                <a href="{{ route('admin.suppliers.ledger', $purchaseReturn->supplier_id) }}" class="btn btn-sm btn-outline-dark">
                    <i class="fas fa-book-open me-1"></i> Supplier Ledger
                </a>
            @endif
        </div>
    </div>

    {{-- Meta Overview Cards --}}
    @php
        $grandTotal = (float) ($purchaseReturn->grand_total ?? $purchaseReturn->total_amount ?? 0);
        $taxAmount  = (float) ($purchaseReturn->tax_amount ?? 0);
        $shipping   = (float) ($purchaseReturn->shipping_cost ?? 0);
        $totalItemsQty = (float) $purchaseReturn->items->sum('quantity');
    @endphp

    <div class="row g-3 mb-4 prd-overview-row">

        {{-- Vendor Details Card --}}
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100 prd-info-card">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center gap-2">
                    <span class="fw-bold small text-secondary text-uppercase">
                        <i class="fas fa-truck-field me-1"></i> Vendor / Supplier
                    </span>
                    @if($purchaseReturn->supplier)
                        <a href="{{ route('admin.suppliers.ledger', $purchaseReturn->supplier->id) }}" class="small text-primary text-decoration-none">
                            Statement <i class="fas fa-arrow-up-right-from-square fa-xs"></i>
                        </a>
                    @endif
                </div>
                <div class="card-body p-3">
                    <h6 class="fw-bold text-dark mb-1">{{ $purchaseReturn->supplier->name ?? 'N/A' }}</h6>
                    <div class="text-secondary small mb-3">{{ $purchaseReturn->supplier->company_name ?? 'Individual Supplier' }}</div>

                    <div class="small text-muted mb-2 prd-info-line">
                        <i class="fas fa-phone fa-xs me-2"></i>{{ $purchaseReturn->supplier->phone ?? '—' }}
                    </div>
                    <div class="small text-muted mb-2 prd-info-line">
                        <i class="fas fa-envelope fa-xs me-2"></i>{{ $purchaseReturn->supplier->email ?? '—' }}
                    </div>
                    @if($purchaseReturn->supplier && $purchaseReturn->supplier->address)
                        <div class="small text-muted prd-info-line">
                            <i class="fas fa-map-marker-alt fa-xs me-2"></i>{{ $purchaseReturn->supplier->address }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sourcing & Dispatch Logistics --}}
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100 prd-info-card">
                <div class="card-header bg-white border-bottom py-3">
                    <span class="fw-bold small text-secondary text-uppercase">
                        <i class="fas fa-dolly me-1"></i> Sourcing &amp; Dispatch
                    </span>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between gap-3 small py-2 border-bottom">
                        <span class="text-muted">Return Date:</span>
                        <span class="fw-semibold text-dark text-end">{{ \Carbon\Carbon::parse($purchaseReturn->return_date)->format('d M, Y') }}</span>
                    </div>
                    <div class="d-flex justify-content-between gap-3 small py-2 border-bottom">
                        <span class="text-muted">Source Purchase Bill:</span>
                        <span class="text-end">
                            @if($purchaseReturn->purchase_id)
                                <a href="{{ route('admin.purchases.show', $purchaseReturn->purchase_id) }}" class="fw-semibold text-primary text-decoration-none">
                                    #{{ $purchaseReturn->purchase->purchase_number ?? $purchaseReturn->purchase_id }}
                                </a>
                            @else
                                <span class="text-muted">Direct Return</span>
                            @endif
                        </span>
                    </div>
                    <div class="d-flex justify-content-between gap-3 small py-2 border-bottom">
                        <span class="text-muted">Source Inward GRN:</span>
                        <span class="text-end">
                            @if($purchaseReturn->purchase_receipt_id)
                                <a href="{{ route('admin.purchase-receipts.show', $purchaseReturn->purchase_receipt_id) }}" class="fw-semibold text-success text-decoration-none">
                                    #{{ $purchaseReturn->purchaseReceipt->receipt_number ?? $purchaseReturn->purchase_receipt_id }}
                                </a>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </span>
                    </div>
                    <div class="d-flex justify-content-between gap-3 small py-2 border-bottom">
                        <span class="text-muted">Dispatch Warehouse:</span>
                        <span class="fw-semibold text-dark text-end">{{ $purchaseReturn->warehouse->name ?? 'Central Warehouse' }}</span>
                    </div>
                    <div class="d-flex justify-content-between gap-3 small py-2 border-bottom">
                        <span class="text-muted">Gate Pass Reference:</span>
                        <span class="fw-bold text-dark font-monospace text-end">{{ $purchaseReturn->reference_no ?? '—' }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center gap-3 small py-2">
                        <span class="text-muted">Gate Pass / Proof Scan:</span>
                        <span class="text-end">
                            @if($purchaseReturn->attachment_file)
                                <a href="{{ asset('storage/' . $purchaseReturn->attachment_file) }}" target="_blank" class="btn btn-xs btn-outline-primary py-1 px-2 prd-scan-btn">
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

        {{-- Settlement & Financial Summary --}}
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100 prd-info-card">
                <div class="card-header bg-white border-bottom py-3">
                    <span class="fw-bold small text-secondary text-uppercase">
                        <i class="fas fa-scale-balanced me-1"></i> Claim Settlement
                    </span>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between gap-3 small py-2 border-bottom">
                        <span class="text-muted">Settlement Mode:</span>
                        <span class="fw-bold text-dark text-end">{{ ucfirst(str_replace('_', ' ', $purchaseReturn->settlement_type ?? 'credit_note')) }}</span>
                    </div>
                    @if($purchaseReturn->bankAccount)
                        <div class="d-flex justify-content-between gap-3 small py-2 border-bottom">
                            <span class="text-muted">Refund Received In:</span>
                            <span class="fw-semibold text-dark text-end">{{ $purchaseReturn->bankAccount->account_name }}</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between gap-3 small py-2 border-bottom">
                        <span class="text-muted">Total Returned Qty:</span>
                        <span class="fw-bold text-danger text-end">{{ number_format($totalItemsQty, 2) }} Units</span>
                    </div>
                    <div class="d-flex justify-content-between gap-3 small py-2 border-bottom">
                        <span class="text-muted">Created By:</span>
                        <span class="fw-semibold text-secondary text-end">{{ $purchaseReturn->creator->name ?? 'System Staff' }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center gap-2 pt-3">
                        <span class="fw-bold text-secondary small text-uppercase">Total Return Claim:</span>
                        <span class="fw-bold text-danger fs-5 text-nowrap">৳{{ number_format($grandTotal, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Returned Items Table --}}
    <div class="card border-0 shadow-sm mb-4 prd-section-card">
        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4">
            <h6 class="fw-bold mb-0 text-danger prd-section-title">
                <i class="fas fa-boxes-stacked me-2"></i>Returned Line Items &amp; Defect Classification
            </h6>
        </div>

        <div class="table-responsive prd-table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0 prd-items-table">
                <thead class="table-light text-secondary text-uppercase">
                    <tr>
                        <th class="text-center" style="width: 40px;">#</th>
                        <th>Product &amp; Specification</th>
                        <th style="width: 130px;">SKU / Code</th>
                        <th style="width: 120px;">Batch / Lot #</th>
                        <th class="text-center" style="width: 110px;">Return Qty</th>
                        <th class="text-end" style="width: 120px;">Unit Rate (৳)</th>
                        <th class="text-end" style="width: 100px;">Tax (৳)</th>
                        <th class="text-end" style="width: 140px;">Total Claim (৳)</th>
                        <th>Return Reason &amp; Particulars</th>
                    </tr>
                </thead>
                <tbody>
                    @php $itemsSubtotal = 0; @endphp
                    @forelse($purchaseReturn->items as $index => $item)
                        @php
                            $qty = (float) $item->quantity;
                            $rate = (float) $item->unit_cost;
                            $tax = (float) ($item->tax ?? 0);
                            $lineTotal = (float) ($item->total ?? (($qty * $rate) + $tax));
                            $itemsSubtotal += $lineTotal;
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
                                <span class="badge bg-light text-secondary border font-monospace prd-code-badge">
                                    {{ $item->sku ?? ($item->product->sku ?? '—') }}
                                </span>
                            </td>
                            <td class="font-monospace text-muted small">
                                {{ $item->batch_no ?? '—' }}
                            </td>
                            <td class="text-center fw-bold text-danger">
                                {{ number_format($qty, 2) }}
                            </td>
                            <td class="text-end">
                                ৳{{ number_format($rate, 2) }}
                            </td>
                            <td class="text-end text-muted">
                                {{ $tax > 0 ? '+৳' . number_format($tax, 2) : '—' }}
                            </td>
                            <td class="text-end fw-bold text-danger">
                                ৳{{ number_format($lineTotal, 2) }}
                            </td>
                            <td>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle me-1 prd-reason-badge">
                                    {{ ucfirst(str_replace('_', ' ', $item->reason ?? 'Defective')) }}
                                </span>
                                @if($item->remarks)
                                    <small class="text-secondary">{{ $item->remarks }}</small>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                No items found on this return voucher.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Financial Calculation & Notes Breakdown --}}
        <div class="card-body p-3 p-md-4 bg-light border-top">
            <div class="row g-3 justify-content-between">
                <div class="col-12 col-md-6">
                    @if($purchaseReturn->notes)
                        <h6 class="fw-bold text-secondary small text-uppercase mb-2">Return Particulars &amp; Audit Notes</h6>
                        <div class="p-3 bg-white border rounded small text-secondary mb-3 prd-note-box">
                            {{ $purchaseReturn->notes }}
                        </div>
                    @endif

                    <div class="p-3 bg-white border rounded small text-secondary prd-audit-note">
                        <i class="fas fa-shield-halved text-danger me-1"></i> Inventory deduction and journal entries for purchase returns have been posted in accordance with ERP periodic stock valuation.
                    </div>
                </div>

                <div class="col-12 col-md-5 col-lg-4">
                    <div class="card border p-3 shadow-none bg-white prd-total-card">
                        <div class="d-flex justify-content-between mb-2 small gap-2">
                            <span class="text-muted">Return Items Subtotal:</span>
                            <span class="fw-semibold text-dark text-nowrap">৳{{ number_format($itemsSubtotal, 2) }}</span>
                        </div>

                        @if($taxAmount > 0)
                            <div class="d-flex justify-content-between mb-2 small gap-2">
                                <span class="text-muted">Tax / VAT Reversal:</span>
                                <span class="text-dark fw-semibold text-nowrap">+৳{{ number_format($taxAmount, 2) }}</span>
                            </div>
                        @endif

                        @if($shipping > 0)
                            <div class="d-flex justify-content-between mb-2 small gap-2">
                                <span class="text-muted">Freight / Handling Deducted:</span>
                                <span class="text-danger fw-semibold text-nowrap">-৳{{ number_format($shipping, 2) }}</span>
                            </div>
                        @endif

                        <hr class="my-2">

                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <span class="fw-bold text-dark fs-6">Total Net Claim:</span>
                            <span class="fw-bold text-danger fs-5 text-nowrap">৳{{ number_format($grandTotal, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Automated Accounting Ledger Representation --}}
    <div class="card border-0 shadow-sm mb-3 prd-section-card">
        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4">
            <h6 class="fw-bold mb-0 text-dark small text-uppercase">
                <i class="fas fa-receipt text-primary me-2"></i>Automated Ledger Journal Entries
            </h6>
        </div>
        <div class="table-responsive prd-table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0 prd-ledger-table">
                <thead class="table-light text-secondary text-uppercase">
                    <tr>
                        <th>Account Head</th>
                        <th>Ledger Impact</th>
                        <th class="text-end" style="width: 160px;">Debit (৳)</th>
                        <th class="text-end" style="width: 160px;">Credit (৳)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-semibold text-dark">
                            Accounts Payable &mdash; {{ $purchaseReturn->supplier->name ?? 'Supplier' }}
                        </td>
                        <td class="text-muted">Supplier Liability Reduced (Due Deduction)</td>
                        <td class="text-end fw-bold text-success">৳{{ number_format($grandTotal, 2) }}</td>
                        <td class="text-end text-muted">—</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-dark">
                            Purchase Returns &amp; Allowances (Inventory Outflow)
                        </td>
                        <td class="text-muted">Inventory Asset / Procurement Cost Reversal</td>
                        <td class="text-end text-muted">—</td>
                        <td class="text-end fw-bold text-danger">৳{{ number_format($grandTotal, 2) }}</td>
                    </tr>
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="2" class="text-end text-uppercase prd-ledger-total-label">Balanced Posting:</td>
                        <td class="text-end text-dark">৳{{ number_format($grandTotal, 2) }}</td>
                        <td class="text-end text-dark">৳{{ number_format($grandTotal, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- Void / Cancel Return Modal --}}
@if($purchaseReturn->status !== 'cancelled')
    <div class="modal fade text-start" id="cancelReturnModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow prd-cancel-modal">
                <div class="modal-header bg-light">
                    <h6 class="modal-title fw-bold text-danger">
                        <i class="fas fa-triangle-exclamation me-1"></i> Void Return Voucher #{{ $purchaseReturn->return_number }}
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.purchase-returns.cancel', $purchaseReturn->id) }}" method="POST">
                    @csrf
                    <div class="modal-body p-3">
                        <p class="small text-muted mb-3">Voiding this return will reverse the stock deduction and restore the accounts payable liability on the vendor ledger.</p>
                        <div class="mb-2">
                            <label class="form-label fw-semibold small">Reason for Voiding <span class="text-danger">*</span></label>
                            <textarea name="cancel_reason" rows="3" class="form-control form-control-sm" required placeholder="State reason (e.g., vendor refused return, erroneous entry)..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-sm btn-danger">Confirm Void Return</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

{{-- Print Stylesheet & Page Styling --}}
<style>
    .purchase-return-details-page {
        --prd-border: #e8edf3;
        --prd-muted: #667085;
        --prd-radius: 8px;
        color: #344054;
    }

    .purchase-return-details-page .prd-header h5 {
        font-size: 1.05rem;
        line-height: 1.5;
        letter-spacing: -0.2px;
        overflow-wrap: anywhere;
    }

    .purchase-return-details-page .breadcrumb {
        font-size: 12px;
    }

    .purchase-return-details-page .breadcrumb a {
        color: #6c757d;
        text-decoration: none;
    }

    .purchase-return-details-page .breadcrumb a:hover {
        color: var(--bs-primary);
    }

    .purchase-return-details-page .prd-header-actions {
        align-items: center;
    }

    .purchase-return-details-page .prd-header-actions .btn,
    .purchase-return-details-page .prd-header-actions form .btn {
        min-height: 34px;
        border-radius: 6px;
        font-weight: 600;
        padding: 6px 11px;
    }

    .purchase-return-details-page .prd-header-actions form {
        margin: 0;
    }

    .purchase-return-details-page .prd-status-badge {
        font-size: 10.5px !important;
        font-weight: 600;
        line-height: 1.4;
        border-radius: 5px;
    }

    .purchase-return-details-page .prd-info-card,
    .purchase-return-details-page .prd-section-card {
        border: 1px solid var(--prd-border) !important;
        border-radius: var(--prd-radius);
        overflow: hidden;
    }

    .purchase-return-details-page .prd-info-card .card-header,
    .purchase-return-details-page .prd-section-card .card-header {
        border-bottom-color: var(--prd-border);
    }

    .purchase-return-details-page .prd-info-card .card-header {
        min-height: 46px;
    }

    .purchase-return-details-page .prd-info-card .card-body {
        line-height: 1.55;
    }

    .purchase-return-details-page .prd-info-line {
        overflow-wrap: anywhere;
    }

    .purchase-return-details-page .prd-info-line i {
        width: 14px;
        color: #98a2b3;
    }

    .purchase-return-details-page .prd-scan-btn {
        border-radius: 5px;
        font-size: 11px;
        font-weight: 600;
    }

    .purchase-return-details-page .prd-section-title {
        font-size: 13.5px;
    }

    .purchase-return-details-page .prd-table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .purchase-return-details-page .prd-items-table {
        min-width: 1050px;
        font-size: 12.5px;
        border-color: var(--prd-border);
    }

    .purchase-return-details-page .prd-items-table thead th,
    .purchase-return-details-page .prd-ledger-table thead th {
        background: #f8f9fb;
        color: #667085;
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: 0.4px;
        white-space: nowrap;
        padding: 12px 10px;
        border-color: var(--prd-border);
        vertical-align: middle;
    }

    .purchase-return-details-page .prd-items-table tbody td {
        padding: 12px 10px;
        border-color: var(--prd-border);
        vertical-align: middle;
    }

    .purchase-return-details-page .prd-items-table tbody tr:hover {
        background: #fafbfc;
    }

    .purchase-return-details-page .prd-items-table tbody td:nth-child(2),
    .purchase-return-details-page .prd-items-table tbody td:nth-child(9) {
        white-space: normal;
        min-width: 160px;
    }

    .purchase-return-details-page .prd-code-badge,
    .purchase-return-details-page .prd-reason-badge {
        font-size: 10.5px;
        font-weight: 600;
        padding: 5px 7px;
        border-radius: 5px;
    }

    .purchase-return-details-page .prd-note-box,
    .purchase-return-details-page .prd-audit-note {
        border-radius: 7px;
        line-height: 1.65;
        overflow-wrap: anywhere;
    }

    .purchase-return-details-page .prd-total-card {
        border-color: var(--prd-border) !important;
        border-radius: 8px;
    }

    .purchase-return-details-page .prd-ledger-table {
        min-width: 650px;
        font-size: 12px;
        border-color: var(--prd-border);
    }

    .purchase-return-details-page .prd-ledger-table tbody td,
    .purchase-return-details-page .prd-ledger-table tfoot td {
        padding: 11px 12px;
        border-color: var(--prd-border);
        vertical-align: middle;
    }

    .purchase-return-details-page .prd-ledger-total-label {
        font-size: 10.5px;
    }

    .purchase-return-details-page .prd-cancel-modal {
        border-radius: 8px;
        overflow: hidden;
    }

    .purchase-return-details-page .prd-cancel-modal .modal-header,
    .purchase-return-details-page .prd-cancel-modal .modal-footer {
        border-color: var(--prd-border);
    }

    .purchase-return-details-page .prd-cancel-modal .form-control {
        border-color: #dce2e9;
        border-radius: 6px;
    }

    .purchase-return-details-page .prd-cancel-modal .form-control:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 0.15rem rgba(var(--bs-primary-rgb), 0.12);
    }

    .purchase-return-details-page .prd-cancel-modal .btn {
        border-radius: 6px;
        font-weight: 600;
    }

    @media (max-width: 991.98px) {
        .purchase-return-details-page .prd-overview-row > div {
            width: 100%;
        }

        .purchase-return-details-page .prd-info-card .card-body {
            padding: 14px !important;
        }
    }

    @media (max-width: 767.98px) {
        .purchase-return-details-page .prd-header {
            align-items: flex-start !important;
        }

        .purchase-return-details-page .prd-header-actions {
            width: 100%;
        }

        .purchase-return-details-page .prd-header-actions .btn,
        .purchase-return-details-page .prd-header-actions form {
            flex: 1 1 auto;
        }

        .purchase-return-details-page .prd-header-actions form .btn {
            width: 100%;
        }

        .purchase-return-details-page .prd-info-card .card-body .d-flex {
            align-items: flex-start;
        }
    }

    @media (max-width: 575.98px) {
        .purchase-return-details-page {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        .purchase-return-details-page .prd-header h5 {
            font-size: 0.98rem;
        }

        .purchase-return-details-page .prd-header-actions {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px !important;
        }

        .purchase-return-details-page .prd-header-actions > * {
            min-width: 0;
        }

        .purchase-return-details-page .prd-header-actions .btn {
            width: 100%;
            white-space: normal;
            justify-content: center;
        }

        .purchase-return-details-page .prd-header-actions form {
            width: 100%;
        }

        .purchase-return-details-page .prd-header-actions form .btn {
            white-space: normal;
        }

        .purchase-return-details-page .prd-status-badge {
            white-space: normal;
        }

        .purchase-return-details-page .prd-info-card .card-header,
        .purchase-return-details-page .prd-section-card .card-header {
            padding-left: 13px !important;
            padding-right: 13px !important;
        }

        .purchase-return-details-page .prd-total-card {
            padding: 12px !important;
        }

        .purchase-return-details-page .prd-total-card .fs-5 {
            font-size: 1.1rem !important;
        }
    }

    @media print {
        @page {
            size: auto;
            margin: 12mm;
        }

        body {
            background: #fff !important;
        }

        .purchase-return-details-page {
            width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .admin-dark-sidebar,
        .admin-navbar,
        .breadcrumb,
        .prd-header-actions,
        .btn,
        .dropdown,
        .modal,
        .card-header a {
            display: none !important;
        }

        .purchase-return-details-page .prd-header {
            margin-bottom: 12px !important;
        }

        .purchase-return-details-page .prd-info-card,
        .purchase-return-details-page .prd-section-card,
        .purchase-return-details-page .prd-total-card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
            break-inside: avoid;
        }

        .purchase-return-details-page .prd-table-responsive {
            overflow: visible !important;
        }

        .purchase-return-details-page .prd-items-table,
        .purchase-return-details-page .prd-ledger-table {
            min-width: 0 !important;
            width: 100% !important;
            font-size: 9px !important;
        }

        .purchase-return-details-page .prd-items-table th,
        .purchase-return-details-page .prd-items-table td,
        .purchase-return-details-page .prd-ledger-table th,
        .purchase-return-details-page .prd-ledger-table td {
            padding: 5px !important;
            border: 1px solid #dee2e6 !important;
        }

        .purchase-return-details-page .prd-overview-row {
            break-inside: avoid;
        }

        .purchase-return-details-page .card {
            box-shadow: none !important;
        }
    }
</style>
@endsection