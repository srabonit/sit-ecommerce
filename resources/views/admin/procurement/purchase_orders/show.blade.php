@extends('layouts.admin')

@section('title', 'Purchase Order #' . ($purchaseOrder->po_number ?? 'PO Details'))

@section('admin_content')
<div class="container-fluid px-3 py-3 po-details-page">

    {{-- Flash Notice --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3" style="border-radius: 8px;">
            <div class="d-flex align-items-center gap-2 fw-semibold">
                <i class="fas fa-circle-check fa-lg text-success"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 po-page-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="fw-bold text-dark mb-0 po-page-title">
                <i class="fas fa-file-signature text-primary me-2"></i>Purchase Order #{{ $purchaseOrder->po_number }}
            </h5>

            @php
                $status = $purchaseOrder->status ?? 'pending';
            @endphp
            @switch($status)
                @case('draft')
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-2" style="font-size: 11px;">
                        <i class="fas fa-file-pen me-1"></i> Draft
                    </span>
                    @break
                @case('pending')
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-2" style="font-size: 11px;">
                        <i class="fas fa-clock me-1"></i> Pending Approval
                    </span>
                    @break
                @case('approved')
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-2" style="font-size: 11px;">
                        <i class="fas fa-circle-check me-1"></i> Approved
                    </span>
                    @break
                @case('completed')
                    <span class="badge bg-success text-white px-2 py-2" style="font-size: 11px;">
                        <i class="fas fa-check-double me-1"></i> Completed
                    </span>
                    @break
                @case('cancelled')
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-2" style="font-size: 11px;">
                        <i class="fas fa-ban me-1"></i> Cancelled
                    </span>
                    @break
                @default
                    <span class="badge bg-light text-dark border px-2 py-2" style="font-size: 11px;">
                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                    </span>
            @endswitch
        </div>

        <div class="d-flex flex-wrap gap-2 po-header-actions">
            <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-sm btn-outline-secondary po-action-btn">
                <i class="fas fa-arrow-left me-1"></i> Back to Orders
            </a>
            <button type="button" class="btn btn-sm btn-outline-primary po-action-btn" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print PO
            </button>

            @if(in_array($status, ['pending', 'draft']))
                <form action="{{ route('admin.purchase-orders.approve', $purchaseOrder->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success po-action-btn">
                        <i class="fas fa-check-circle me-1"></i> Approve Order
                    </button>
                </form>
            @endif

            @if(!in_array($status, ['completed', 'cancelled']))
                <button type="button" class="btn btn-sm btn-outline-danger po-action-btn" data-bs-toggle="modal" data-bs-target="#cancelPoModal">
                    <i class="fas fa-ban me-1"></i> Cancel PO
                </button>
            @endif
        </div>
    </div>

    @php
        $grandTotalVal = (float) ($purchaseOrder->grand_total ?? $purchaseOrder->total_amount ?? 0);
        $discountAmount = (float) ($purchaseOrder->discount_amount ?? 0);
        $totalOrderedQty = (float) (collect($items ?? [])->sum('quantity'));
    @endphp

    <div class="row g-3 mb-4 po-summary-row">
        <div class="col-12 col-md-4">
            <div class="card border shadow-sm h-100 po-info-card">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold small text-secondary text-uppercase">
                        <i class="fas fa-truck-field me-1 text-primary"></i> Supplier / Vendor
                    </span>
                    @if(!empty($purchaseOrder->supplier->id))
                        <a href="{{ route('admin.suppliers.ledger', $purchaseOrder->supplier->id) }}" class="small text-primary text-decoration-none fw-semibold">
                            Statement <i class="fas fa-arrow-up-right-from-square fa-xs"></i>
                        </a>
                    @endif
                </div>
                <div class="card-body p-3">
                    <h6 class="fw-bold text-dark mb-1">{{ $purchaseOrder->supplier->name ?? 'N/A' }}</h6>
                    <div class="text-secondary small mb-3">{{ $purchaseOrder->supplier->company_name ?? 'General Supplier' }}</div>

                    <div class="small text-muted mb-2 po-contact-item">
                        <i class="fas fa-phone fa-xs me-2 text-secondary"></i>{{ $purchaseOrder->supplier->phone ?? '—' }}
                    </div>
                    <div class="small text-muted mb-2 po-contact-item">
                        <i class="fas fa-envelope fa-xs me-2 text-secondary"></i>{{ $purchaseOrder->supplier->email ?? '—' }}
                    </div>
                    @if(!empty($purchaseOrder->supplier->address))
                        <div class="small text-muted po-contact-item">
                            <i class="fas fa-map-marker-alt fa-xs me-2 text-secondary"></i>{{ $purchaseOrder->supplier->address }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border shadow-sm h-100 po-info-card">
                <div class="card-header bg-white border-bottom py-3">
                    <span class="fw-bold small text-secondary text-uppercase">
                        <i class="fas fa-calendar-check me-1 text-primary"></i> Timeline &amp; Destination
                    </span>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start gap-2 small py-2 border-bottom">
                        <span class="text-muted">PO Date:</span>
                        <span class="fw-semibold text-dark text-end">{{ !empty($purchaseOrder->order_date) ? \Carbon\Carbon::parse($purchaseOrder->order_date)->format('d M, Y') : '—' }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-start gap-2 small py-2 border-bottom">
                        <span class="text-muted">Expected Delivery:</span>
                        <span class="fw-bold text-primary text-end">{{ !empty($purchaseOrder->expected_delivery_date) ? \Carbon\Carbon::parse($purchaseOrder->expected_delivery_date)->format('d M, Y') : 'Standard Delivery' }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-start gap-2 small py-2">
                        <span class="text-muted">Destination Warehouse:</span>
                        <span class="fw-semibold text-dark text-end">{{ $purchaseOrder->warehouse->name ?? 'Central Warehouse' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border shadow-sm h-100 po-info-card">
                <div class="card-header bg-white border-bottom py-3">
                    <span class="fw-bold small text-secondary text-uppercase">
                        <i class="fas fa-coins me-1 text-primary"></i> Commercial Valuation
                    </span>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start gap-2 small py-2 border-bottom">
                        <span class="text-muted">Total Ordered Items:</span>
                        <span class="fw-bold text-dark font-monospace text-end">{{ count($items ?? []) }} lines ({{ number_format($totalOrderedQty, 0) }} units)</span>
                    </div>
                    @if($discountAmount > 0)
                        <div class="d-flex justify-content-between align-items-start gap-2 small py-2 border-bottom">
                            <span class="text-muted">Discount Deducted:</span>
                            <span class="text-danger fw-semibold font-monospace text-end">-৳{{ number_format($discountAmount, 2) }}</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between align-items-start gap-2 small py-2">
                        <span class="text-muted">Total Payable:</span>
                        <span class="fw-bold text-success fs-6 font-monospace text-end">৳{{ number_format($grandTotalVal, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border shadow-sm mb-4 po-items-card">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold mb-0 text-dark po-section-title">
                <i class="fas fa-boxes-stacked text-primary me-2"></i>Ordered Line Items &amp; Pricing Breakdown
            </h6>
        </div>

        <div class="table-responsive po-table-wrap">
            <table class="table table-bordered table-hover align-middle mb-0 po-items-table" style="font-size: 12.5px;">
                <thead class="table-light text-secondary text-uppercase" style="font-size: 10.5px; letter-spacing: 0.5px;">
                    <tr>
                        <th class="text-center" style="width: 45px;">#</th>
                        <th>Product Description</th>
                        <th style="width: 150px;">SKU / Code</th>
                        <th class="text-center" style="width: 100px;">Ordered Qty</th>
                        <th class="text-end" style="width: 130px;">Purchase Rate (৳)</th>
                        <th class="text-end pe-3" style="width: 150px;">Line Total (৳)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $calculatedItemsSubtotal = 0; @endphp
                    @forelse($items as $index => $item)
                        @php
                            $qty = (float) ($item->quantity ?? 1);
                            $rate = (float) ($item->unit_cost ?? $item->unit_price ?? $item->rate ?? 0);
                            $lineTotal = (float) ($item->total ?? ($qty * $rate));
                            $calculatedItemsSubtotal += $lineTotal;
                        @endphp
                        <tr>
                            <td class="text-center text-muted font-monospace">{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $item->product_name ?? 'Product #' . $item->product_id }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border font-monospace" style="font-size: 11px;">
                                    {{ $item->product_sku ?? ($item->sku ?? '—') }}
                                </span>
                            </td>
                            <td class="text-center fw-bold text-dark font-monospace">
                                {{ number_format($qty, 0) }}
                            </td>
                            <td class="text-end font-monospace">
                                ৳{{ number_format($rate, 2) }}
                            </td>
                            <td class="text-end pe-3 fw-bold text-dark font-monospace">
                                ৳{{ number_format($lineTotal, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                No items found on this purchase order.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-body p-3 bg-light border-top">
            <div class="row g-3 justify-content-between">
                <div class="col-12 col-md-6">
                    @if(!empty($purchaseOrder->notes))
                        <h6 class="fw-bold text-secondary small text-uppercase mb-2">Notes / Instructions</h6>
                        <div class="p-3 bg-white border rounded small text-secondary po-notes">
                            {{ $purchaseOrder->notes }}
                        </div>
                    @endif
                </div>

                <div class="col-12 col-md-5 col-lg-4">
                    <div class="card border p-3 shadow-none bg-white po-total-card">
                        <div class="d-flex justify-content-between mb-2 small gap-2">
                            <span class="text-muted">Items Subtotal:</span>
                            <span class="fw-semibold text-dark font-monospace text-end">৳{{ number_format($calculatedItemsSubtotal, 2) }}</span>
                        </div>

                        @if($discountAmount > 0)
                            <div class="d-flex justify-content-between mb-2 small gap-2">
                                <span class="text-muted">Discount:</span>
                                <span class="text-danger fw-semibold font-monospace text-end">-৳{{ number_format($discountAmount, 2) }}</span>
                            </div>
                        @endif

                        <hr class="my-2">

                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <span class="fw-bold text-dark fs-6">Payable Grand Total:</span>
                            <span class="fw-bold text-primary fs-5 font-monospace text-end">৳{{ number_format($grandTotalVal, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(!in_array($status, ['completed', 'cancelled']))
    <div class="modal fade text-start" id="cancelPoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius: 8px;">
                <div class="modal-header bg-light">
                    <h6 class="modal-title fw-bold text-danger">
                        <i class="fas fa-triangle-exclamation me-1"></i> Cancel Purchase Order #{{ $purchaseOrder->po_number }}
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.purchase-orders.cancel', $purchaseOrder->id) }}" method="POST">
                    @csrf
                    <div class="modal-body p-3">
                        <p class="small text-muted mb-3">Cancelling this purchase order will prevent further operations on it.</p>
                        <div class="mb-2">
                            <label class="form-label fw-semibold small">Cancellation Reason <span class="text-danger">*</span></label>
                            <textarea name="cancel_reason" rows="3" class="form-control form-control-sm" required placeholder="State reason for cancelling..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-sm btn-secondary po-action-btn" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-sm btn-danger po-action-btn">Confirm Cancellation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

<style>
    .po-details-page {
        color: #344054;
    }

    .po-details-page .po-page-header {
        padding: 2px 0 4px;
    }

    .po-details-page .po-page-title {
        font-size: 1.15rem;
        line-height: 1.5;
    }

    .po-details-page .card {
        border-color: #e8ecf1 !important;
        border-radius: 8px;
        overflow: hidden;
    }

    .po-details-page .card-header {
        min-height: 48px;
    }

    .po-details-page .po-info-card,
    .po-details-page .po-items-card {
        background: #fff;
        transition: box-shadow .2s ease, border-color .2s ease;
    }

    .po-details-page .po-info-card:hover,
    .po-details-page .po-items-card:hover {
        border-color: #d9e1eb !important;
        box-shadow: 0 4px 14px rgba(16, 24, 40, .055) !important;
    }

    .po-details-page .po-contact-item {
        line-height: 1.6;
        overflow-wrap: anywhere;
    }

    .po-details-page .po-action-btn {
        border-radius: 6px;
        font-weight: 600;
        padding: 7px 12px;
        transition: all .2s ease;
    }

    .po-details-page .po-action-btn:focus-visible {
        outline: 3px solid rgba(13, 110, 253, .2);
        outline-offset: 2px;
    }

    .po-details-page .po-section-title {
        font-size: 14px;
    }

    .po-details-page .po-table-wrap {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .po-details-page .po-items-table {
        min-width: 760px;
    }

    .po-details-page .po-items-table thead th {
        padding: 12px 10px;
        white-space: nowrap;
        vertical-align: middle;
        font-weight: 700;
        border-bottom-width: 1px;
    }

    .po-details-page .po-items-table tbody td {
        padding: 12px 10px;
        vertical-align: middle;
    }

    .po-details-page .po-items-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .po-details-page .po-notes {
        border-radius: 6px;
        line-height: 1.7;
        overflow-wrap: anywhere;
    }

    .po-details-page .po-total-card {
        border-radius: 8px;
        border-color: #e8ecf1 !important;
    }

    #cancelPoModal .modal-content {
        overflow: hidden;
    }

    #cancelPoModal .form-control {
        border-radius: 6px;
    }

    #cancelPoModal .form-control:focus {
        border-color: var(--bs-primary, #0d6efd);
        box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .1);
    }

    @media (max-width: 991.98px) {
        .po-details-page .po-page-header {
            align-items: flex-start !important;
        }

        .po-details-page .po-header-actions {
            width: 100%;
        }
    }

    @media (max-width: 767.98px) {
        .po-details-page {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        .po-details-page .po-page-header {
            margin-bottom: 1rem !important;
        }

        .po-details-page .po-page-title {
            font-size: 1.05rem;
        }

        .po-details-page .po-header-actions {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px !important;
        }

        .po-details-page .po-header-actions > * {
            width: 100%;
        }

        .po-details-page .po-header-actions form {
            display: block !important;
        }

        .po-details-page .po-header-actions .po-action-btn {
            width: 100%;
            min-height: 36px;
            white-space: nowrap;
        }

        .po-details-page .po-items-table {
            min-width: 760px;
        }

        .po-details-page .po-total-card {
            width: 100%;
        }
    }

    @media (max-width: 420px) {
        .po-details-page .po-header-actions {
            grid-template-columns: 1fr;
        }

        .po-details-page .po-page-title {
            font-size: 1rem;
        }

        .po-details-page .card-header {
            padding-left: 12px;
            padding-right: 12px;
        }
    }

    @media print {
        .admin-dark-sidebar,
        .admin-navbar,
        .btn,
        .dropdown,
        .modal {
            display: none !important;
        }

        .po-details-page {
            padding: 0 !important;
        }

        .po-details-page .container-fluid {
            padding: 0 !important;
        }

        .po-details-page .card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
            margin-bottom: 12px !important;
            break-inside: avoid;
        }

        .po-details-page .po-items-table {
            min-width: 0 !important;
        }

        .po-details-page .po-table-wrap {
            overflow: visible !important;
        }

        .po-details-page table {
            width: 100% !important;
            border-collapse: collapse !important;
        }

        .po-details-page th,
        .po-details-page td {
            border: 1px solid #dee2e6 !important;
        }
    }
</style>
@endsection