
@extends('layouts.admin')

@section('title', 'Payment Voucher #' . $supplierPayment->payment_number)

@section('admin_content')
<div class="container-fluid px-3 py-3 supplier-payment-detail-page">

    {{-- Breadcrumb & Top Action Header --}}
    <div class="sp-page-header d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div class="sp-heading">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <div class="sp-title-icon">
                    <i class="fas fa-money-check-dollar"></i>
                </div>
                <h5 class="fw-bold text-dark mb-0">
                    Payment Voucher #{{ $supplierPayment->payment_number }}
                </h5>

                @switch($supplierPayment->status ?? 'posted')
                    @case('posted')
                    @case('approved')
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                            <i class="fas fa-circle-check me-1"></i> Disbursed &amp; Posted
                        </span>
                        @break
                    @case('pending')
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                            <i class="fas fa-clock me-1"></i> Pending Clearance
                        </span>
                        @break
                    @case('cancelled')
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                            <i class="fas fa-ban me-1"></i> Void / Cancelled
                        </span>
                        @break
                    @default
                        <span class="badge bg-light text-dark border px-2 py-1">
                            {{ ucfirst($supplierPayment->status) }}
                        </span>
                @endswitch

                <span class="badge bg-light text-secondary border px-2 py-1">
                    Method: {{ ucfirst(str_replace('_', ' ', $supplierPayment->payment_method ?? 'cash')) }}
                </span>
            </div>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.supplier-payments.index') }}">Supplier Payments</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $supplierPayment->payment_number }}</li>
                </ol>
            </nav>
        </div>

        <div class="sp-header-actions d-flex flex-wrap gap-2">
            <a href="{{ route('admin.supplier-payments.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Payments
            </a>

            <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print Money Receipt
            </button>

            @if(($supplierPayment->status ?? 'posted') !== 'cancelled')
                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelPaymentModal">
                    <i class="fas fa-ban me-1"></i> Void Voucher
                </button>
            @endif

            @if($supplierPayment->supplier)
                <a href="{{ route('admin.suppliers.ledger', $supplierPayment->supplier_id) }}" class="btn btn-sm btn-outline-dark">
                    <i class="fas fa-book-open me-1"></i> Supplier Ledger
                </a>
            @endif
        </div>
    </div>

    {{-- Meta Overview Cards --}}
    @php
        $totalPaid = (float) $supplierPayment->amount;
        $totalAllocated = (float) $supplierPayment->allocations->sum('amount');
        $advanceBalance = max(0, $totalPaid - $totalAllocated);
    @endphp

    <div class="row g-3 mb-4">

        {{-- Supplier / Beneficiary Card --}}
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card sp-info-card h-100">
                <div class="card-header">
                    <div class="sp-card-heading">
                        <span class="sp-card-icon sp-icon-primary">
                            <i class="fas fa-truck-field"></i>
                        </span>
                        <span>Beneficiary Supplier</span>
                    </div>

                    @if($supplierPayment->supplier)
                        <a href="{{ route('admin.suppliers.ledger', $supplierPayment->supplier->id) }}" class="sp-card-link">
                            Statement <i class="fas fa-arrow-up-right-from-square fa-xs"></i>
                        </a>
                    @endif
                </div>

                <div class="card-body">
                    <h6 class="sp-supplier-name">{{ $supplierPayment->supplier->name ?? 'N/A' }}</h6>
                    <div class="text-secondary small mb-3">{{ $supplierPayment->supplier->company_name ?? 'Individual Supplier' }}</div>

                    <div class="sp-detail-line">
                        <i class="fas fa-phone"></i>
                        <span>{{ $supplierPayment->supplier->phone ?? '—' }}</span>
                    </div>
                    <div class="sp-detail-line">
                        <i class="fas fa-envelope"></i>
                        <span>{{ $supplierPayment->supplier->email ?? '—' }}</span>
                    </div>

                    @if($supplierPayment->supplier && $supplierPayment->supplier->address)
                        <div class="sp-detail-line">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>{{ $supplierPayment->supplier->address }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Banking & Transaction Channel --}}
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card sp-info-card h-100">
                <div class="card-header">
                    <div class="sp-card-heading">
                        <span class="sp-card-icon sp-icon-info">
                            <i class="fas fa-building-columns"></i>
                        </span>
                        <span>Banking &amp; Drawer Channel</span>
                    </div>
                </div>

                <div class="card-body">
                    <div class="sp-info-row">
                        <span class="sp-info-label">Payment Date</span>
                        <span class="sp-info-value">{{ \Carbon\Carbon::parse($supplierPayment->payment_date)->format('d M, Y') }}</span>
                    </div>
                    <div class="sp-info-row">
                        <span class="sp-info-label">Payment Mode</span>
                        <span class="sp-info-value">{{ ucfirst(str_replace('_', ' ', $supplierPayment->payment_method)) }}</span>
                    </div>
                    <div class="sp-info-row">
                        <span class="sp-info-label">Disbursing Account</span>
                        <span class="sp-info-value">{{ $supplierPayment->bankAccount->account_name ?? 'Cash in Hand' }}</span>
                    </div>
                    <div class="sp-info-row">
                        <span class="sp-info-label">Transaction / Cheque #</span>
                        <span class="sp-info-value font-monospace">{{ $supplierPayment->reference_no ?? '—' }}</span>
                    </div>

                    @if($supplierPayment->cheque_date)
                        <div class="sp-info-row">
                            <span class="sp-info-label">Cheque Clearance Date</span>
                            <span class="sp-info-value text-primary">{{ \Carbon\Carbon::parse($supplierPayment->cheque_date)->format('d M, Y') }}</span>
                        </div>
                    @endif

                    <div class="sp-info-row sp-info-row-last">
                        <span class="sp-info-label">Receipt / Slip Scan</span>
                        <span class="sp-info-value">
                            @if($supplierPayment->attachment_file)
                                <a href="{{ asset('storage/' . $supplierPayment->attachment_file) }}" target="_blank" class="btn btn-sm btn-outline-primary sp-compact-btn">
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

        {{-- Allocation Summary & Authorization --}}
        <div class="col-12 col-xl-4">
            <div class="card sp-info-card h-100">
                <div class="card-header">
                    <div class="sp-card-heading">
                        <span class="sp-card-icon sp-icon-success">
                            <i class="fas fa-scale-balanced"></i>
                        </span>
                        <span>Allocation &amp; Audit</span>
                    </div>
                </div>

                <div class="card-body">
                    <div class="sp-info-row">
                        <span class="sp-info-label">Disbursed Amount</span>
                        <span class="sp-info-value sp-amount-success">৳{{ number_format($totalPaid, 2) }}</span>
                    </div>
                    <div class="sp-info-row">
                        <span class="sp-info-label">Allocated to Invoices</span>
                        <span class="sp-info-value">৳{{ number_format($totalAllocated, 2) }}</span>
                    </div>
                    <div class="sp-info-row">
                        <span class="sp-info-label">Unallocated (Advance)</span>
                        <span class="sp-info-value fw-bold {{ $advanceBalance > 0 ? 'text-primary' : 'text-muted' }}">
                            ৳{{ number_format($advanceBalance, 2) }}
                        </span>
                    </div>
                    <div class="sp-info-row">
                        <span class="sp-info-label">Operating Branch</span>
                        <span class="sp-info-value">{{ $supplierPayment->branch->name ?? 'HQ Branch' }}</span>
                    </div>
                    <div class="sp-info-row sp-info-row-last">
                        <span class="sp-info-label">Authorized By</span>
                        <span class="sp-info-value text-secondary">{{ $supplierPayment->creator->name ?? 'System Staff' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Invoice Allocations Table --}}
    <div class="card sp-section-card mb-4">
        <div class="card-header">
            <div class="sp-section-heading">
                <span class="sp-section-icon text-success">
                    <i class="fas fa-file-invoice"></i>
                </span>
                <div>
                    <h6 class="mb-0">Invoice Settlement Breakdown</h6>
                    <small class="text-muted">Purchase bills allocated against this payment voucher</small>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 sp-data-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 55px;">#</th>
                        <th style="min-width: 160px;">Purchase Bill #</th>
                        <th style="min-width: 115px;">Bill Date</th>
                        <th class="text-end" style="min-width: 135px;">Bill Total (৳)</th>
                        <th class="text-end" style="min-width: 170px;">Allocated from this Voucher (৳)</th>
                        <th class="text-center" style="min-width: 145px;">Settlement Status</th>
                        <th class="text-end pe-3" style="min-width: 100px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($supplierPayment->allocations as $index => $alloc)
                        @php
                            $bill = $alloc->purchase;
                            $billTotal = $bill ? (float) $bill->grand_total : 0;
                            $allocAmount = (float) $alloc->amount;
                            $billDue = $bill ? max(0, (float)$bill->grand_total - (float)$bill->paid_amount) : 0;
                        @endphp
                        <tr>
                            <td class="text-center text-muted">{{ $index + 1 }}</td>
                            <td>
                                @if($bill)
                                    <a href="{{ route('admin.purchases.show', $bill->id) }}" class="sp-table-link">
                                        #{{ $bill->purchase_number }}
                                    </a>
                                    @if($bill->supplier_invoice_no)
                                        <small class="text-muted d-block font-monospace mt-1">Inv: {{ $bill->supplier_invoice_no }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">Invoice #{{ $alloc->purchase_id }}</span>
                                @endif
                            </td>
                            <td class="text-muted">
                                {{ $bill ? \Carbon\Carbon::parse($bill->purchase_date)->format('d M, Y') : '—' }}
                            </td>
                            <td class="text-end text-dark fw-medium">
                                ৳{{ number_format($billTotal, 2) }}
                            </td>
                            <td class="text-end fw-bold text-success">
                                ৳{{ number_format($allocAmount, 2) }}
                            </td>
                            <td class="text-center">
                                @if($billDue <= 0)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="fas fa-check-circle me-1"></i> Paid in Full
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                                        Due: ৳{{ number_format($billDue, 2) }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                @if($bill)
                                    <a href="{{ route('admin.purchases.show', $bill->id) }}" class="btn btn-sm btn-outline-primary sp-compact-btn">
                                        View Bill
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <div class="sp-empty-state">
                                    <i class="fas fa-hand-holding-dollar"></i>
                                    <div class="fw-semibold text-dark mt-2">No invoice allocations found</div>
                                    <div class="small mt-1">Entire disbursement logged as an <strong>Advance Supplier Payment</strong>.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-end text-uppercase">Total Allocated to Invoices:</td>
                        <td class="text-end text-success">৳{{ number_format($totalAllocated, 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                    @if($advanceBalance > 0)
                        <tr class="sp-advance-row">
                            <td colspan="4" class="text-end text-uppercase">Advance Unallocated Credit Balance:</td>
                            <td class="text-end text-primary">৳{{ number_format($advanceBalance, 2) }}</td>
                            <td colspan="2"><span class="small text-muted font-italic">(Available for future bill allocations)</span></td>
                        </tr>
                    @endif
                </tfoot>
            </table>
        </div>

        @if($supplierPayment->notes)
            <div class="card-body sp-notes-area">
                <h6 class="fw-bold text-secondary small text-uppercase mb-2">Disbursement Particulars &amp; Narration</h6>
                <div class="sp-notes-content">
                    {{ $supplierPayment->notes }}
                </div>
            </div>
        @endif
    </div>

    {{-- Accounting Journal Voucher Entry Representation --}}
    <div class="card sp-section-card mb-3">
        <div class="card-header">
            <div class="sp-section-heading">
                <span class="sp-section-icon text-primary">
                    <i class="fas fa-receipt"></i>
                </span>
                <div>
                    <h6 class="mb-0">Automated Ledger Journal Entries</h6>
                    <small class="text-muted">Accounting impact of this supplier disbursement</small>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 sp-data-table sp-journal-table">
                <thead>
                    <tr>
                        <th style="min-width: 220px;">Account Head</th>
                        <th style="min-width: 190px;">Ledger Impact</th>
                        <th class="text-end" style="min-width: 145px;">Debit (৳)</th>
                        <th class="text-end" style="min-width: 145px;">Credit (৳)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-semibold text-dark">
                            Accounts Payable &mdash; {{ $supplierPayment->supplier->name ?? 'Supplier' }}
                        </td>
                        <td class="text-muted">Vendor Liability Settlement</td>
                        <td class="text-end fw-bold text-success">৳{{ number_format($totalPaid, 2) }}</td>
                        <td class="text-end text-muted">—</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-dark">
                            {{ $supplierPayment->bankAccount ? $supplierPayment->bankAccount->account_name : 'Cash in Hand Account' }}
                        </td>
                        <td class="text-muted">Asset Outflow (Disbursement)</td>
                        <td class="text-end text-muted">—</td>
                        <td class="text-end fw-bold text-danger">৳{{ number_format($totalPaid, 2) }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="text-end text-uppercase">Balanced Posting:</td>
                        <td class="text-end text-dark">৳{{ number_format($totalPaid, 2) }}</td>
                        <td class="text-end text-dark">৳{{ number_format($totalPaid, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- Void / Cancel Payment Modal --}}
@if(($supplierPayment->status ?? 'posted') !== 'cancelled')
    <div class="modal fade text-start supplier-payment-detail-page" id="cancelPaymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content sp-modal-content">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold text-danger">
                        <i class="fas fa-triangle-exclamation me-2"></i> Void Payment Voucher #{{ $supplierPayment->payment_number }}
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="{{ route('admin.supplier-payments.cancel', $supplierPayment->id) }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="sp-warning-note mb-3">
                            <i class="fas fa-circle-info"></i>
                            <p class="small mb-0">Voiding this disbursement will reverse allocated payments on linked purchase bills and restore the vendor's outstanding ledger balance.</p>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-semibold small">Reason for Voiding <span class="text-danger">*</span></label>
                            <textarea name="cancel_reason" rows="3" class="form-control form-control-sm" required placeholder="Specify reason (e.g., bounced cheque, erroneous transfer, duplicate disbursement)..."></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="fas fa-ban me-1"></i> Confirm Void Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

{{-- Page Styles --}}
<style>
    .supplier-payment-detail-page {
        --sp-primary: var(--bs-primary, #3699ff);
        --sp-success: var(--bs-success, #1bc5bd);
        --sp-border: var(--bs-border-color, #e4e6ef);
        --sp-muted: #7e8299;
        --sp-heading: #181c32;
        --sp-surface: #ffffff;
        color: var(--sp-heading);
    }

    .supplier-payment-detail-page .sp-page-header {
        min-width: 0;
    }

    .supplier-payment-detail-page .sp-title-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        flex: 0 0 36px;
        color: var(--sp-success);
        background: rgba(27, 197, 189, .10);
        border-radius: 8px;
        font-size: 16px;
    }

    .supplier-payment-detail-page .sp-heading h5 {
        font-size: 17px;
        letter-spacing: -.2px;
    }

    .supplier-payment-detail-page .breadcrumb {
        font-size: 12px;
        color: var(--sp-muted);
    }

    .supplier-payment-detail-page .breadcrumb-item a {
        color: var(--sp-primary);
        text-decoration: none;
    }

    .supplier-payment-detail-page .breadcrumb-item a:hover {
        text-decoration: underline;
    }

    .supplier-payment-detail-page .breadcrumb-item.active {
        color: var(--sp-muted);
    }

    .supplier-payment-detail-page .badge {
        font-size: 10.5px;
        font-weight: 600;
        letter-spacing: .1px;
        border-radius: 5px;
    }

    .supplier-payment-detail-page .sp-header-actions .btn {
        min-height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
        font-weight: 600;
        font-size: 12px;
        padding: 7px 11px;
    }

    .supplier-payment-detail-page .sp-info-card,
    .supplier-payment-detail-page .sp-section-card {
        border: 1px solid var(--sp-border);
        border-radius: 7px;
        background: var(--sp-surface);
        box-shadow: 0 2px 8px rgba(24, 28, 50, .035);
        overflow: hidden;
    }

    .supplier-payment-detail-page .sp-info-card .card-header,
    .supplier-payment-detail-page .sp-section-card .card-header {
        min-height: 52px;
        padding: 11px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        background: #fff;
        border-bottom: 1px solid var(--sp-border);
    }

    .supplier-payment-detail-page .sp-card-heading {
        display: flex;
        align-items: center;
        gap: 9px;
        color: #3f4254;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .45px;
    }

    .supplier-payment-detail-page .sp-card-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 29px;
        height: 29px;
        flex: 0 0 29px;
        border-radius: 6px;
        font-size: 13px;
    }

    .supplier-payment-detail-page .sp-icon-primary {
        color: #3699ff;
        background: rgba(54, 153, 255, .10);
    }

    .supplier-payment-detail-page .sp-icon-info {
        color: #8950fc;
        background: rgba(137, 80, 252, .10);
    }

    .supplier-payment-detail-page .sp-icon-success {
        color: #1bc5bd;
        background: rgba(27, 197, 189, .10);
    }

    .supplier-payment-detail-page .sp-card-link {
        color: var(--sp-primary);
        text-decoration: none;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }

    .supplier-payment-detail-page .sp-card-link:hover {
        text-decoration: underline;
    }

    .supplier-payment-detail-page .sp-info-card .card-body {
        padding: 15px 16px;
    }

    .supplier-payment-detail-page .sp-supplier-name {
        font-size: 15px;
        font-weight: 700;
        color: var(--sp-heading);
        margin-bottom: 4px;
    }

    .supplier-payment-detail-page .sp-detail-line {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        color: #7e8299;
        font-size: 12px;
        line-height: 1.6;
        margin-top: 8px;
        overflow-wrap: anywhere;
    }

    .supplier-payment-detail-page .sp-detail-line i {
        width: 14px;
        flex: 0 0 14px;
        margin-top: 3px;
        color: #a1a5b7;
        text-align: center;
    }

    .supplier-payment-detail-page .sp-info-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding: 9px 0;
        border-bottom: 1px dashed #edf0f5;
        font-size: 12px;
    }

    .supplier-payment-detail-page .sp-info-row:first-child {
        padding-top: 0;
    }

    .supplier-payment-detail-page .sp-info-row-last {
        padding-bottom: 0;
        border-bottom: 0;
    }

    .supplier-payment-detail-page .sp-info-label {
        color: var(--sp-muted);
        flex: 1 1 auto;
        min-width: 0;
    }

    .supplier-payment-detail-page .sp-info-value {
        color: #3f4254;
        font-weight: 600;
        text-align: right;
        overflow-wrap: anywhere;
        max-width: 62%;
    }

    .supplier-payment-detail-page .sp-amount-success {
        color: #1bc5bd;
        font-size: 15px;
        font-weight: 700;
    }

    .supplier-payment-detail-page .sp-compact-btn {
        border-radius: 4px;
        padding: 4px 8px;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }

    .supplier-payment-detail-page .sp-section-card .card-header {
        min-height: 60px;
        padding: 12px 16px;
    }

    .supplier-payment-detail-page .sp-section-heading {
        display: flex;
        align-items: center;
        gap: 11px;
    }

    .supplier-payment-detail-page .sp-section-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        border-radius: 7px;
        background: #f5f7fa;
        font-size: 15px;
    }

    .supplier-payment-detail-page .sp-section-heading h6 {
        color: var(--sp-heading);
        font-size: 13px;
        font-weight: 700;
    }

    .supplier-payment-detail-page .sp-section-heading small {
        display: block;
        margin-top: 3px;
        font-size: 11px;
    }

    .supplier-payment-detail-page .sp-data-table {
        font-size: 12px;
        white-space: normal;
    }

    .supplier-payment-detail-page .sp-data-table thead th {
        padding: 11px 12px;
        background: #f5f7fa;
        color: #7e8299;
        border-bottom: 1px solid var(--sp-border);
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .4px;
        vertical-align: middle;
        white-space: nowrap;
    }

    .supplier-payment-detail-page .sp-data-table tbody td {
        padding: 11px 12px;
        border-color: #edf0f5;
        color: #5e6278;
        vertical-align: middle;
    }

    .supplier-payment-detail-page .sp-data-table tbody tr:hover {
        background: #fafbfd;
    }

    .supplier-payment-detail-page .sp-data-table tfoot td {
        padding: 12px;
        background: #f8f9fb;
        border-top: 1px solid var(--sp-border);
        font-size: 11px;
        font-weight: 700;
        vertical-align: middle;
    }

    .supplier-payment-detail-page .sp-data-table tfoot td.text-uppercase {
        color: #7e8299;
        letter-spacing: .3px;
    }

    .supplier-payment-detail-page .sp-data-table .sp-advance-row td {
        background: rgba(54, 153, 255, .06);
    }

    .supplier-payment-detail-page .sp-table-link {
        color: var(--sp-primary);
        font-weight: 700;
        text-decoration: none;
    }

    .supplier-payment-detail-page .sp-table-link:hover {
        text-decoration: underline;
    }

    .supplier-payment-detail-page .sp-empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 8px 12px;
    }

    .supplier-payment-detail-page .sp-empty-state > i {
        color: #a1a5b7;
        font-size: 30px;
    }

    .supplier-payment-detail-page .sp-notes-area {
        background: #f8f9fb;
        border-top: 1px solid var(--sp-border);
    }

    .supplier-payment-detail-page .sp-notes-content {
        padding: 12px;
        color: #5e6278;
        background: #fff;
        border: 1px solid var(--sp-border);
        border-radius: 5px;
        font-size: 12px;
        line-height: 1.7;
        overflow-wrap: anywhere;
    }

    .supplier-payment-detail-page .sp-journal-table tbody td {
        padding-top: 13px;
        padding-bottom: 13px;
    }

    .supplier-payment-detail-page .sp-modal-content {
        border: 0;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 10px 35px rgba(24, 28, 50, .15);
    }

    .supplier-payment-detail-page .modal-header,
    .supplier-payment-detail-page .modal-footer {
        padding: 13px 18px;
        background: #f8f9fb;
        border-color: var(--sp-border);
    }

    .supplier-payment-detail-page .modal-title {
        font-size: 14px;
    }

    .supplier-payment-detail-page .modal-footer .btn {
        border-radius: 5px;
        font-weight: 600;
    }

    .supplier-payment-detail-page .sp-warning-note {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px;
        color: #7e8299;
        background: rgba(255, 184, 34, .10);
        border: 1px solid rgba(255, 184, 34, .22);
        border-radius: 6px;
    }

    .supplier-payment-detail-page .sp-warning-note i {
        color: #e5a000;
        margin-top: 2px;
    }

    .supplier-payment-detail-page .form-label {
        color: #5e6278;
    }

    .supplier-payment-detail-page textarea.form-control {
        min-height: 85px;
        border-color: var(--sp-border);
        border-radius: 5px;
        font-size: 12px;
        resize: vertical;
    }

    .supplier-payment-detail-page textarea.form-control:focus {
        border-color: var(--sp-primary);
        box-shadow: 0 0 0 .15rem rgba(54, 153, 255, .12);
    }

    @media (max-width: 767.98px) {
        .supplier-payment-detail-page {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        .supplier-payment-detail-page .sp-page-header {
            align-items: flex-start !important;
        }

        .supplier-payment-detail-page .sp-heading {
            width: 100%;
        }

        .supplier-payment-detail-page .sp-heading h5 {
            font-size: 15px;
        }

        .supplier-payment-detail-page .sp-header-actions {
            width: 100%;
        }

        .supplier-payment-detail-page .sp-header-actions .btn {
            flex: 1 1 auto;
        }

        .supplier-payment-detail-page .sp-info-card .card-body {
            padding: 13px;
        }

        .supplier-payment-detail-page .sp-section-card .card-header {
            padding: 11px 12px;
        }

        .supplier-payment-detail-page .sp-data-table thead th,
        .supplier-payment-detail-page .sp-data-table tbody td,
        .supplier-payment-detail-page .sp-data-table tfoot td {
            padding-left: 9px;
            padding-right: 9px;
        }
    }

    @media (max-width: 420px) {
        .supplier-payment-detail-page .sp-header-actions .btn {
            flex: 1 1 100%;
        }

        .supplier-payment-detail-page .sp-info-row {
            gap: 8px;
            font-size: 11px;
        }

        .supplier-payment-detail-page .sp-info-value {
            max-width: 58%;
        }

        .supplier-payment-detail-page .sp-card-heading {
            font-size: 10px;
        }
    }

    @media print {
        .admin-dark-sidebar,
        .admin-navbar,
        .supplier-payment-detail-page .breadcrumb,
        .supplier-payment-detail-page .btn,
        .supplier-payment-detail-page .dropdown,
        .supplier-payment-detail-page .modal,
        .supplier-payment-detail-page .sp-header-actions,
        .supplier-payment-detail-page .sp-card-link {
            display: none !important;
        }

        .supplier-payment-detail-page {
            width: 100% !important;
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .supplier-payment-detail-page .card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
            margin-bottom: 12px !important;
            break-inside: avoid;
        }

        .supplier-payment-detail-page .table-responsive {
            overflow: visible !important;
        }

        .supplier-payment-detail-page table {
            width: 100% !important;
            border-collapse: collapse !important;
        }

        .supplier-payment-detail-page th,
        .supplier-payment-detail-page td {
            border: 1px solid #dee2e6 !important;
        }

        .supplier-payment-detail-page .sp-info-card .card-header,
        .supplier-payment-detail-page .sp-section-card .card-header {
            background: #f8f9fa !important;
        }
    }
</style>
@endsection
