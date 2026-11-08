@extends('layouts.admin')

@section('title', 'Exchange Voucher #' . $supplierExchange->exchange_number)

@section('admin_content')
@php
    $outwardVal = (float) ($supplierExchange->outward_total ?? 0);
    $inwardVal  = (float) ($supplierExchange->inward_total ?? 0);
    $deltaVal   = $inwardVal - $outwardVal;
    $balancedSum = max($inwardVal, $outwardVal);
@endphp

<div class="container-fluid px-3 py-3 supplier-exchange-voucher">

    {{-- Page Header --}}
    <div class="voucher-header mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div class="flex-grow-1">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <div class="voucher-heading-icon">
                        <i class="fas fa-right-left"></i>
                    </div>

                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <h5 class="fw-bold text-dark mb-0">
                                Product Exchange #{{ $supplierExchange->exchange_number }}
                            </h5>

                            @switch($supplierExchange->status)
                                @case('pending')
                                    <span class="exchange-status status-pending">
                                        <i class="fas fa-clock me-1"></i> Pending Verification
                                    </span>
                                    @break
                                @case('approved')
                                    <span class="exchange-status status-approved">
                                        <i class="fas fa-truck-fast me-1"></i> Approved / Dispatched
                                    </span>
                                    @break
                                @case('completed')
                                    <span class="exchange-status status-completed">
                                        <i class="fas fa-check-double me-1"></i> Completed &amp; Settled
                                    </span>
                                    @break
                                @case('cancelled')
                                    <span class="exchange-status status-cancelled">
                                        <i class="fas fa-ban me-1"></i> Void / Cancelled
                                    </span>
                                    @break
                                @default
                                    <span class="exchange-status status-default">
                                        {{ ucfirst(str_replace('_', ' ', $supplierExchange->status)) }}
                                    </span>
                            @endswitch

                            <span class="exchange-status status-default">
                                Settlement: {{ ucfirst(str_replace('_', ' ', $supplierExchange->settlement_type ?? 'ledger_adjustment')) }}
                            </span>
                        </div>

                        <nav aria-label="breadcrumb" class="mt-2">
                            <ol class="breadcrumb mb-0 voucher-breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('admin.supplier-exchanges.index') }}">Supplier Exchanges</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    {{ $supplierExchange->exchange_number }}
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>

            {{-- Header Actions --}}
            <div class="d-flex flex-wrap align-items-center gap-2 voucher-actions">
                <a href="{{ route('admin.supplier-exchanges.index') }}" class="btn btn-sm btn-light border">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>

                <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Print Voucher
                </button>

                @if($supplierExchange->status === 'pending')
                    <form action="{{ route('admin.supplier-exchanges.approve', $supplierExchange->id) }}"
                          method="POST"
                          onsubmit="return confirm('Approve exchange #{{ $supplierExchange->exchange_number }}? This will deduct outward stock, admit inward stock, and update the supplier balance.');">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="fas fa-check-circle me-1"></i> Approve &amp; Post Stock
                        </button>
                    </form>
                @endif

                @if($supplierExchange->status !== 'cancelled')
                    <button type="button"
                            class="btn btn-sm btn-outline-danger"
                            data-bs-toggle="modal"
                            data-bs-target="#cancelExchangeModal">
                        <i class="fas fa-ban me-1"></i> Void Exchange
                    </button>
                @endif

                @if($supplierExchange->supplier)
                    <a href="{{ route('admin.suppliers.ledger', $supplierExchange->supplier_id) }}"
                       class="btn btn-sm btn-outline-dark">
                        <i class="fas fa-book-open me-1"></i> Supplier Ledger
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Overview Cards --}}
    <div class="row g-3 mb-4">

        {{-- Supplier Information --}}
        <div class="col-12 col-lg-4">
            <div class="card voucher-card h-100">
                <div class="card-header voucher-card-header">
                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon icon-blue">
                            <i class="fas fa-truck-field"></i>
                        </div>
                        <span>Vendor / Supplier</span>
                    </div>

                    @if($supplierExchange->supplier)
                        <a href="{{ route('admin.suppliers.ledger', $supplierExchange->supplier->id) }}"
                           class="section-link">
                            Statement <i class="fas fa-arrow-up-right-from-square ms-1"></i>
                        </a>
                    @endif
                </div>

                <div class="card-body p-3">
                    <h6 class="supplier-name mb-1">
                        {{ $supplierExchange->supplier->name ?? 'N/A' }}
                    </h6>

                    <div class="text-muted small mb-3">
                        {{ $supplierExchange->supplier->company_name ?? 'Individual Supplier' }}
                    </div>

                    <div class="supplier-contact">
                        <div>
                            <i class="fas fa-phone"></i>
                            <span>{{ $supplierExchange->supplier->phone ?? '—' }}</span>
                        </div>
                        <div>
                            <i class="fas fa-envelope"></i>
                            <span>{{ $supplierExchange->supplier->email ?? '—' }}</span>
                        </div>

                        @if($supplierExchange->supplier && $supplierExchange->supplier->address)
                            <div>
                                <i class="fas fa-map-marker-alt"></i>
                                <span>{{ $supplierExchange->supplier->address }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Logistics Information --}}
        <div class="col-12 col-lg-4">
            <div class="card voucher-card h-100">
                <div class="card-header voucher-card-header">
                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon icon-purple">
                            <i class="fas fa-boxes-stacked"></i>
                        </div>
                        <span>Logistics &amp; Warehouse</span>
                    </div>
                </div>

                <div class="card-body p-3">
                    <div class="detail-row">
                        <span>Exchange Date</span>
                        <strong>{{ \Carbon\Carbon::parse($supplierExchange->exchange_date)->format('d M, Y') }}</strong>
                    </div>

                    <div class="detail-row">
                        <span>Exchange Warehouse</span>
                        <strong>{{ $supplierExchange->warehouse->name ?? 'Central Warehouse' }}</strong>
                    </div>

                    <div class="detail-row">
                        <span>Operating Branch</span>
                        <strong>{{ $supplierExchange->branch->name ?? 'HQ Branch' }}</strong>
                    </div>

                    <div class="detail-row">
                        <span>Challan / Gate Pass #</span>
                        <strong class="font-monospace">{{ $supplierExchange->reference_no ?? '—' }}</strong>
                    </div>

                    <div class="detail-row detail-row-last">
                        <span>Challan / Scan Copy</span>
                        <div>
                            @if($supplierExchange->attachment_file)
                                <a href="{{ asset('storage/' . $supplierExchange->attachment_file) }}"
                                   target="_blank"
                                   class="btn btn-sm btn-outline-primary py-1">
                                    <i class="fas fa-file-pdf me-1"></i> View Scan
                                </a>
                            @else
                                <span class="text-muted">None</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Financial Summary --}}
        <div class="col-12 col-lg-4">
            <div class="card voucher-card h-100">
                <div class="card-header voucher-card-header">
                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon icon-green">
                            <i class="fas fa-scale-balanced"></i>
                        </div>
                        <span>Differential Settlement</span>
                    </div>
                </div>

                <div class="card-body p-3">
                    <div class="detail-row">
                        <span>Total Outward Sent</span>
                        <strong class="text-danger">৳{{ number_format($outwardVal, 2) }}</strong>
                    </div>

                    <div class="detail-row">
                        <span>Total Inward Received</span>
                        <strong class="text-success">৳{{ number_format($inwardVal, 2) }}</strong>
                    </div>

                    <div class="detail-row">
                        <span>Settlement Mode</span>
                        <strong>{{ ucfirst(str_replace('_', ' ', $supplierExchange->settlement_type ?? 'ledger_adjustment')) }}</strong>
                    </div>

                    @if($supplierExchange->bankAccount)
                        <div class="detail-row">
                            <span>Settlement Account</span>
                            <strong>{{ $supplierExchange->bankAccount->account_name }}</strong>
                        </div>
                    @endif

                    <div class="net-differential mt-3">
                        <div>
                            <span class="net-label">Net Differential</span>
                            <small class="d-block text-muted">Final settlement adjustment</small>
                        </div>

                        <div class="text-end">
                            @if($deltaVal > 0)
                                <div class="net-value text-danger">
                                    +৳{{ number_format($deltaVal, 2) }}
                                </div>
                                <small class="text-danger">Payable Due</small>
                            @elseif($deltaVal < 0)
                                <div class="net-value text-success">
                                    -৳{{ number_format(abs($deltaVal), 2) }}
                                </div>
                                <small class="text-success">Vendor Credit</small>
                            @else
                                <div class="net-value text-muted">৳0.00</div>
                                <small class="text-muted">Even Swap</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Outward Items --}}
    <div class="card voucher-card mb-4">
        <div class="card-header voucher-card-header">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="section-icon icon-red">
                    <i class="fas fa-arrow-up-from-bracket"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold text-dark">Outward Items</h6>
                    <small class="text-muted">Goods dispatched / deducted from inventory</small>
                </div>
            </div>

            <span class="total-pill total-outward">
                Total Outward: ৳{{ number_format($outwardVal, 2) }}
            </span>
        </div>

        <div class="table-responsive">
            <table class="table voucher-table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th>Product Description</th>
                        <th style="width: 130px;">SKU / Code</th>
                        <th style="width: 120px;">Batch #</th>
                        <th class="text-center" style="width: 115px;">Dispatched Qty</th>
                        <th class="text-end" style="width: 125px;">Unit Rate (৳)</th>
                        <th class="text-end" style="width: 145px;">Line Total (৳)</th>
                        <th>Swap / Return Reason</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($supplierExchange->outwardItems ?? [] as $index => $item)
                        @php
                            $qty = (float) $item->quantity;
                            $rate = (float) $item->unit_cost;
                            $lineTotal = (float) ($item->total ?? ($qty * $rate));
                        @endphp

                        <tr>
                            <td class="text-center text-muted">{{ $index + 1 }}</td>

                            <td>
                                <div class="product-name">
                                    {{ $item->product->name ?? ($item->product_name ?? 'Product #' . $item->product_id) }}
                                </div>

                                @if($item->variant)
                                    <small class="text-muted d-block mt-1">
                                        Variant: {{ $item->variant->name ?? $item->variant->variant_name }}
                                    </small>
                                @endif
                            </td>

                            <td>
                                <span class="code-label">
                                    {{ $item->sku ?? ($item->product->sku ?? '—') }}
                                </span>
                            </td>

                            <td class="font-monospace text-muted">
                                {{ $item->batch_no ?? '—' }}
                            </td>

                            <td class="text-center fw-semibold text-danger">
                                {{ number_format($qty, 2) }}
                            </td>

                            <td class="text-end">৳{{ number_format($rate, 2) }}</td>

                            <td class="text-end fw-bold text-danger">
                                ৳{{ number_format($lineTotal, 2) }}
                            </td>

                            <td>
                                <span class="reason-pill reason-outward">
                                    {{ ucfirst(str_replace('_', ' ', $item->reason ?? 'Defective')) }}
                                </span>

                                @if($item->remarks)
                                    <small class="text-muted d-block mt-1">{{ $item->remarks }}</small>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-table-cell">
                                <i class="fas fa-box-open"></i>
                                <span>No outward items recorded for this exchange voucher.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Inward Items --}}
    <div class="card voucher-card mb-4">
        <div class="card-header voucher-card-header">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="section-icon icon-green">
                    <i class="fas fa-arrow-down-to-bracket"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold text-dark">Inward Items</h6>
                    <small class="text-muted">Replacement goods received into inventory</small>
                </div>
            </div>

            <span class="total-pill total-inward">
                Total Inward: ৳{{ number_format($inwardVal, 2) }}
            </span>
        </div>

        <div class="table-responsive">
            <table class="table voucher-table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th>Product Description</th>
                        <th style="width: 130px;">SKU / Code</th>
                        <th style="width: 120px;">New Batch #</th>
                        <th style="width: 110px;">Expiry Date</th>
                        <th class="text-center" style="width: 115px;">Received Qty</th>
                        <th class="text-end" style="width: 125px;">Unit Rate (৳)</th>
                        <th class="text-end" style="width: 145px;">Line Total (৳)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($supplierExchange->inwardItems ?? [] as $index => $item)
                        @php
                            $qty = (float) $item->quantity;
                            $rate = (float) $item->unit_cost;
                            $lineTotal = (float) ($item->total ?? ($qty * $rate));
                        @endphp

                        <tr>
                            <td class="text-center text-muted">{{ $index + 1 }}</td>

                            <td>
                                <div class="product-name">
                                    {{ $item->product->name ?? ($item->product_name ?? 'Product #' . $item->product_id) }}
                                </div>

                                @if($item->variant)
                                    <small class="text-muted d-block mt-1">
                                        Variant: {{ $item->variant->name ?? $item->variant->variant_name }}
                                    </small>
                                @endif
                            </td>

                            <td>
                                <span class="code-label">
                                    {{ $item->sku ?? ($item->product->sku ?? '—') }}
                                </span>
                            </td>

                            <td class="font-monospace text-muted">
                                {{ $item->batch_no ?? '—' }}
                            </td>

                            <td class="text-muted">
                                {{ $item->expiry_date ? \Carbon\Carbon::parse($item->expiry_date)->format('d-m-Y') : '—' }}
                            </td>

                            <td class="text-center fw-semibold text-success">
                                {{ number_format($qty, 2) }}
                            </td>

                            <td class="text-end">৳{{ number_format($rate, 2) }}</td>

                            <td class="text-end fw-bold text-success">
                                ৳{{ number_format($lineTotal, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-table-cell">
                                <i class="fas fa-box-open"></i>
                                <span>No inward replacement items recorded for this exchange voucher.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($supplierExchange->notes)
            <div class="voucher-notes">
                <div class="notes-heading">
                    <i class="fas fa-clipboard-list me-2"></i>
                    Exchange Particulars &amp; Terms
                </div>
                <div class="notes-content">
                    {{ $supplierExchange->notes }}
                </div>
            </div>
        @endif
    </div>

    {{-- Accounting Ledger --}}
    <div class="card voucher-card mb-4">
        <div class="card-header voucher-card-header">
            <div class="d-flex align-items-center gap-2">
                <div class="section-icon icon-blue">
                    <i class="fas fa-receipt"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold text-dark">Automated Ledger Journal Entries</h6>
                    <small class="text-muted">Accounting impact of this exchange transaction</small>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table voucher-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Account Head</th>
                        <th>Ledger Impact</th>
                        <th class="text-end" style="width: 180px;">Debit (৳)</th>
                        <th class="text-end" style="width: 180px;">Credit (৳)</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td class="fw-semibold text-dark">
                            Inventory Asset (Inward Replacement Goods)
                        </td>
                        <td class="text-muted">Stock Asset Addition</td>
                        <td class="text-end fw-bold text-success">
                            ৳{{ number_format($inwardVal, 2) }}
                        </td>
                        <td class="text-end text-muted">—</td>
                    </tr>

                    <tr>
                        <td class="fw-semibold text-dark">
                            Inventory Asset (Outward Defective Goods)
                        </td>
                        <td class="text-muted">Stock Asset Deduction</td>
                        <td class="text-end text-muted">—</td>
                        <td class="text-end fw-bold text-danger">
                            ৳{{ number_format($outwardVal, 2) }}
                        </td>
                    </tr>

                    @if($deltaVal > 0)
                        <tr>
                            <td class="fw-semibold text-dark">
                                Accounts Payable &mdash; {{ $supplierExchange->supplier->name ?? 'Supplier' }}
                            </td>
                            <td class="text-muted">Net Surplus Payable to Vendor</td>
                            <td class="text-end text-muted">—</td>
                            <td class="text-end fw-bold text-danger">
                                ৳{{ number_format($deltaVal, 2) }}
                            </td>
                        </tr>
                    @elseif($deltaVal < 0)
                        <tr>
                            <td class="fw-semibold text-dark">
                                Accounts Payable &mdash; {{ $supplierExchange->supplier->name ?? 'Supplier' }}
                            </td>
                            <td class="text-muted">Net Surplus Claim / Due Deduction</td>
                            <td class="text-end fw-bold text-success">
                                ৳{{ number_format(abs($deltaVal), 2) }}
                            </td>
                            <td class="text-end text-muted">—</td>
                        </tr>
                    @endif
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="2" class="text-end text-uppercase fw-bold">
                            Balanced Posting:
                        </td>
                        <td class="text-end fw-bold text-dark">
                            ৳{{ number_format($balancedSum, 2) }}
                        </td>
                        <td class="text-end fw-bold text-dark">
                            ৳{{ number_format($balancedSum, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="ledger-footer">
            <div>
                <i class="fas fa-circle-info me-1"></i>
                Debit and credit entries are presented as the exchange's accounting representation.
            </div>
            <div class="ledger-balanced">
                <i class="fas fa-check-circle me-1"></i> Balanced Posting
            </div>
        </div>
    </div>
</div>

{{-- Void / Cancel Exchange Modal --}}
@if($supplierExchange->status !== 'cancelled')
    <div class="modal fade text-start" id="cancelExchangeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-header bg-light border-bottom">
                    <div>
                        <h6 class="modal-title fw-bold text-danger mb-1">
                            <i class="fas fa-triangle-exclamation me-1"></i>
                            Void Exchange Voucher
                        </h6>
                        <small class="text-muted">
                            Voucher #{{ $supplierExchange->exchange_number }}
                        </small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="{{ route('admin.supplier-exchanges.cancel', $supplierExchange->id) }}" method="POST">
                    @csrf

                    <div class="modal-body p-4">
                        <div class="alert alert-warning border-0 small mb-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Voiding this exchange voucher will reverse all outward/inward inventory stock adjustments and restore the vendor's ledger balance.
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-semibold small">
                                Cancellation Reason <span class="text-danger">*</span>
                            </label>
                            <textarea name="cancel_reason"
                                      rows="4"
                                      class="form-control form-control-sm"
                                      required
                                      placeholder="State reason (e.g., replacement rejected by QC, exchange aborted by vendor)..."></textarea>
                        </div>
                    </div>

                    <div class="modal-footer bg-light border-top">
                        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">
                            Close
                        </button>
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="fas fa-ban me-1"></i> Confirm Void Exchange
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

<style>
    .supplier-exchange-voucher {
        --voucher-border: #e8edf3;
        --voucher-muted: #718096;
        --voucher-heading: #243247;
        --voucher-bg: #f8fafc;
        color: #334155;
    }

    .supplier-exchange-voucher .voucher-header {
        background: #fff;
        border: 1px solid var(--voucher-border);
        border-radius: 12px;
        padding: 18px 20px;
    }

    .supplier-exchange-voucher .voucher-heading-icon {
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 11px;
        background: #eaf3ff;
        color: #2878d0;
        font-size: 17px;
        flex-shrink: 0;
    }

    .supplier-exchange-voucher .voucher-breadcrumb {
        font-size: 12px;
    }

    .supplier-exchange-voucher .voucher-breadcrumb a {
        color: #64748b;
        text-decoration: none;
    }

    .supplier-exchange-voucher .voucher-breadcrumb a:hover {
        color: #2878d0;
    }

    .supplier-exchange-voucher .voucher-breadcrumb .active {
        color: #334155;
        font-weight: 600;
    }

    .supplier-exchange-voucher .exchange-status {
        display: inline-flex;
        align-items: center;
        padding: 5px 9px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 700;
        line-height: 1.3;
        white-space: nowrap;
    }

    .supplier-exchange-voucher .status-pending {
        background: #fff7e6;
        color: #a66b00;
        border: 1px solid #f6dfaa;
    }

    .supplier-exchange-voucher .status-approved {
        background: #eaf5ff;
        color: #1768aa;
        border: 1px solid #cce5fa;
    }

    .supplier-exchange-voucher .status-completed {
        background: #eaf8ef;
        color: #21834b;
        border: 1px solid #ccebd7;
    }

    .supplier-exchange-voucher .status-cancelled {
        background: #fff0f0;
        color: #c33e3e;
        border: 1px solid #f3d0d0;
    }

    .supplier-exchange-voucher .status-default {
        background: #f1f5f9;
        color: #526174;
        border: 1px solid #e2e8f0;
    }

    .supplier-exchange-voucher .voucher-actions .btn {
        border-radius: 7px;
        font-weight: 600;
        padding: 7px 11px;
        white-space: nowrap;
    }

    .supplier-exchange-voucher .voucher-card {
        border: 1px solid var(--voucher-border);
        border-radius: 11px;
        box-shadow: 0 2px 8px rgba(30, 50, 80, .035);
        overflow: hidden;
    }

    .supplier-exchange-voucher .voucher-card-header {
        min-height: 58px;
        background: #fff;
        border-bottom: 1px solid var(--voucher-border);
        padding: 11px 15px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .supplier-exchange-voucher .voucher-card-header > div > span {
        color: #46566b;
        font-size: 12px;
        font-weight: 700;
    }

    .supplier-exchange-voucher .section-icon {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        font-size: 14px;
        flex-shrink: 0;
    }

    .supplier-exchange-voucher .icon-blue {
        background: #eaf3ff;
        color: #2878d0;
    }

    .supplier-exchange-voucher .icon-purple {
        background: #f2edff;
        color: #7656c9;
    }

    .supplier-exchange-voucher .icon-green {
        background: #eaf8ef;
        color: #26965a;
    }

    .supplier-exchange-voucher .icon-red {
        background: #fff0f0;
        color: #d34a4a;
    }

    .supplier-exchange-voucher .section-link {
        font-size: 11px;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
    }

    .supplier-exchange-voucher .supplier-name {
        font-size: 16px;
        font-weight: 750;
        color: var(--voucher-heading);
    }

    .supplier-exchange-voucher .supplier-contact {
        display: flex;
        flex-direction: column;
        gap: 10px;
        font-size: 12px;
        color: #64748b;
    }

    .supplier-exchange-voucher .supplier-contact > div {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        overflow-wrap: anywhere;
    }

    .supplier-exchange-voucher .supplier-contact i {
        width: 14px;
        margin-top: 2px;
        color: #94a3b8;
        text-align: center;
        flex-shrink: 0;
    }

    .supplier-exchange-voucher .detail-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        padding: 9px 0;
        border-bottom: 1px solid #f0f2f5;
        font-size: 12px;
    }

    .supplier-exchange-voucher .detail-row > span:first-child {
        color: #778599;
        flex-shrink: 0;
    }

    .supplier-exchange-voucher .detail-row strong {
        color: #344256;
        font-weight: 650;
        text-align: right;
        overflow-wrap: anywhere;
    }

    .supplier-exchange-voucher .detail-row-last {
        border-bottom: 0;
        padding-bottom: 0;
    }

    .supplier-exchange-voucher .net-differential {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        padding: 12px;
        background: #f8fafc;
        border: 1px solid #edf0f4;
        border-radius: 9px;
    }

    .supplier-exchange-voucher .net-label {
        display: block;
        font-size: 11px;
        font-weight: 750;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: .35px;
    }

    .supplier-exchange-voucher .net-value {
        font-size: 18px;
        font-weight: 800;
        line-height: 1.3;
        white-space: nowrap;
    }

    .supplier-exchange-voucher .total-pill {
        display: inline-flex;
        align-items: center;
        padding: 7px 11px;
        border-radius: 7px;
        font-size: 11px;
        font-weight: 750;
        white-space: nowrap;
    }

    .supplier-exchange-voucher .total-outward {
        background: #fff0f0;
        color: #c83e3e;
        border: 1px solid #f6dada;
    }

    .supplier-exchange-voucher .total-inward {
        background: #eaf8ef;
        color: #23864d;
        border: 1px solid #d1edda;
    }

    .supplier-exchange-voucher .voucher-table {
        min-width: 850px;
        font-size: 12px;
    }

    .supplier-exchange-voucher .voucher-table thead th {
        background: #f7f9fc;
        color: #68778b;
        border-bottom: 1px solid #e6ebf1;
        padding: 12px 11px;
        font-size: 10px;
        font-weight: 750;
        text-transform: uppercase;
        letter-spacing: .4px;
        white-space: nowrap;
    }

    .supplier-exchange-voucher .voucher-table tbody td {
        padding: 12px 11px;
        border-color: #edf0f4;
        color: #475569;
        vertical-align: middle;
    }

    .supplier-exchange-voucher .voucher-table tbody tr:hover {
        background: #fafcff;
    }

    .supplier-exchange-voucher .product-name {
        color: #27364a;
        font-weight: 700;
        font-size: 12px;
    }

    .supplier-exchange-voucher .code-label {
        display: inline-block;
        padding: 4px 7px;
        border-radius: 5px;
        border: 1px solid #e7ebf0;
        background: #f8fafc;
        color: #64748b;
        font-family: monospace;
        font-size: 10px;
    }

    .supplier-exchange-voucher .reason-pill {
        display: inline-flex;
        padding: 4px 8px;
        border-radius: 5px;
        font-size: 10px;
        font-weight: 700;
    }

    .supplier-exchange-voucher .reason-outward {
        background: #fff0f0;
        color: #c44848;
        border: 1px solid #f5dada;
    }

    .supplier-exchange-voucher .empty-table-cell {
        padding: 35px 15px !important;
        text-align: center;
        color: #94a3b8 !important;
    }

    .supplier-exchange-voucher .empty-table-cell i {
        display: block;
        margin-bottom: 9px;
        font-size: 22px;
        color: #cbd5e1;
    }

    .supplier-exchange-voucher .voucher-notes {
        padding: 15px;
        border-top: 1px solid var(--voucher-border);
        background: #fbfcfe;
    }

    .supplier-exchange-voucher .notes-heading {
        margin-bottom: 9px;
        color: #526174;
        font-size: 11px;
        font-weight: 750;
        text-transform: uppercase;
        letter-spacing: .35px;
    }

    .supplier-exchange-voucher .notes-content {
        padding: 12px;
        border: 1px solid #e9edf2;
        border-radius: 8px;
        background: #fff;
        color: #526174;
        font-size: 12px;
        line-height: 1.7;
        white-space: pre-line;
    }

    .supplier-exchange-voucher .voucher-table tfoot td {
        padding: 13px 11px;
        background: #f7f9fc;
        border-top: 1px solid #e6ebf1;
    }

    .supplier-exchange-voucher .ledger-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        padding: 12px 15px;
        background: #fbfcfe;
        border-top: 1px solid var(--voucher-border);
        color: #7b8797;
        font-size: 11px;
    }

    .supplier-exchange-voucher .ledger-balanced {
        color: #21834b;
        font-weight: 700;
    }

    @media (max-width: 991.98px) {
        .supplier-exchange-voucher .voucher-actions {
            width: 100%;
        }
    }

    @media (max-width: 575.98px) {
        .supplier-exchange-voucher {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        .supplier-exchange-voucher .voucher-header {
            padding: 14px;
        }

        .supplier-exchange-voucher .voucher-header h5 {
            font-size: 15px;
        }

        .supplier-exchange-voucher .voucher-actions {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 7px !important;
        }

        .supplier-exchange-voucher .voucher-actions > * {
            width: 100%;
        }

        .supplier-exchange-voucher .voucher-actions .btn {
            width: 100%;
            font-size: 11px;
            padding: 8px 5px;
        }

        .supplier-exchange-voucher .voucher-card-header {
            padding: 11px;
        }

        .supplier-exchange-voucher .voucher-card-header h6 {
            font-size: 12px;
        }

        .supplier-exchange-voucher .total-pill {
            font-size: 10px;
            padding: 6px 7px;
        }

        .supplier-exchange-voucher .net-value {
            font-size: 16px;
        }

        .supplier-exchange-voucher .ledger-footer {
            align-items: flex-start;
            flex-direction: column;
        }
    }

    @media print {
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        body {
            background: #fff !important;
            color: #111827 !important;
            font-size: 10px !important;
        }

        .admin-dark-sidebar,
        .admin-navbar,
        .voucher-breadcrumb,
        .voucher-actions,
        .btn,
        .modal,
        .section-link,
        .voucher-exchange-voucher .section-link {
            display: none !important;
        }

        .supplier-exchange-voucher {
            width: 100% !important;
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .supplier-exchange-voucher .voucher-header,
        .supplier-exchange-voucher .voucher-card {
            border: 1px solid #d1d5db !important;
            box-shadow: none !important;
            break-inside: avoid;
        }

        .supplier-exchange-voucher .voucher-header {
            padding: 10px 12px !important;
            margin-bottom: 10px !important;
        }

        .supplier-exchange-voucher .voucher-card {
            margin-bottom: 10px !important;
        }

        .supplier-exchange-voucher .voucher-card-header {
            background: #f3f4f6 !important;
            padding: 8px 10px !important;
        }

        .supplier-exchange-voucher .card-body {
            padding: 9px !important;
        }

        .supplier-exchange-voucher .voucher-table {
            min-width: 0 !important;
            font-size: 9px !important;
        }

        .supplier-exchange-voucher .voucher-table thead th,
        .supplier-exchange-voucher .voucher-table tbody td {
            padding: 6px 7px !important;
            border: 1px solid #d1d5db !important;
            color: #111827 !important;
        }

        .supplier-exchange-voucher .voucher-table thead th {
            background: #f3f4f6 !important;
        }

        .supplier-exchange-voucher .table-responsive {
            overflow: visible !important;
        }

        .supplier-exchange-voucher .row {
            break-inside: avoid;
        }

        .supplier-exchange-voucher .voucher-notes,
        .supplier-exchange-voucher .ledger-footer {
            background: #fff !important;
        }

        .supplier-exchange-voucher .exchange-status,
        .supplier-exchange-voucher .total-pill,
        .supplier-exchange-voucher .reason-pill {
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        a {
            color: inherit !important;
            text-decoration: none !important;
        }
    }
</style>
@endsection