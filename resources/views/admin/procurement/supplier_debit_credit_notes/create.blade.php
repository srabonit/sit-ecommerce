
@extends('layouts.admin')

@section('title', 'Issue Supplier Debit / Credit Note')

@section('admin_content')
<div class="container-fluid px-3 py-3 procurement-note-page">

    {{-- Breadcrumb & Action Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4 procurement-page-header">
        <div>
            <h5 class="fw-bold text-dark mb-1 procurement-page-title">
                <i class="fas fa-file-shield text-primary me-2"></i>Issue Supplier Adjustment Note
            </h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.supplier-debit-credit-notes.index') }}">Adjustment Notes</a></li>
                    <li class="breadcrumb-item active" aria-current="page">New Note</li>
                </ol>
            </nav>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.supplier-debit-credit-notes.index') }}" class="btn btn-sm btn-outline-secondary procurement-btn">
                <i class="fas fa-arrow-left me-1"></i> Back to Notes
            </a>
        </div>
    </div>

    {{-- Validation Error Alerts --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4 procurement-alert">
            <div class="d-flex align-items-center gap-2 fw-semibold mb-2">
                <i class="fas fa-circle-exclamation"></i> Please review the following errors:
            </div>
            <ul class="mb-0 ps-3 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('admin.supplier-debit-credit-notes.store') }}" method="POST" enctype="multipart/form-data" id="adjustmentNoteForm">
        @csrf

        {{-- Note Type & Classification Card --}}
        <div class="card procurement-card mb-4">
            <div class="card-header procurement-card-header">
                <h6 class="fw-bold mb-0">
                    <i class="fas fa-sliders text-primary me-2"></i>Note Classification
                </h6>
            </div>
            <div class="card-body p-3 p-md-4">
                <div class="row g-3 align-items-center">
                    {{-- Note Type Switcher --}}
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">Note Type <span class="text-danger">*</span></label>
                        <select name="note_type" id="note_type" class="form-select fw-semibold" required>
                            <option value="debit_note" {{ old('note_type', request('note_type', 'debit_note')) === 'debit_note' ? 'selected' : '' }}>
                                Debit Note (Dr — Reduces Supplier Due)
                            </option>
                            <option value="credit_note" {{ old('note_type', request('note_type')) === 'credit_note' ? 'selected' : '' }}>
                                Credit Note (Cr — Increases Supplier Due)
                            </option>
                        </select>
                    </div>

                    {{-- Dynamic Impact Explanation --}}
                    <div class="col-12 col-md-8">
                        <div class="p-3 rounded procurement-impact-banner" id="accountingImpactBanner">
                            <div class="fw-bold mb-1" id="impactTitle"></div>
                            <div class="small" id="impactDescription"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sourcing & Commercial Context --}}
        <div class="card procurement-card mb-4">
            <div class="card-header procurement-card-header">
                <h6 class="fw-bold mb-0">
                    <i class="fas fa-file-contract text-primary me-2"></i>Adjustment Details
                </h6>
            </div>
            <div class="card-body p-3 p-md-4">
                <div class="row g-3">

                    {{-- Supplier Selector --}}
                    <div class="col-12 col-md-6 col-lg-4">
                        <label class="form-label fw-semibold">Supplier <span class="text-danger">*</span></label>
                        <select name="supplier_id" id="supplier_id" class="form-select" required>
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}"
                                        data-balance="{{ (float) ($supplier->current_balance ?? 0) }}"
                                        {{ old('supplier_id', request('supplier_id')) == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }} {{ $supplier->company_name ? "({$supplier->company_name})" : '' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="small mt-2 text-muted">
                            Current Ledger Due:
                            <span class="fw-bold text-danger" id="supplierBalanceNotice">৳0.00</span>
                        </div>
                    </div>

                    {{-- Note Number --}}
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label fw-semibold">Note Number <span class="text-danger">*</span></label>
                        <input type="text" name="note_number" id="note_number" class="form-control font-monospace"
                               value="{{ old('note_number', (request('note_type') === 'credit_note' ? 'SCN-' : 'SDN-') . date('Ymd') . '-' . strtoupper(Str::random(4))) }}" required>
                    </div>

                    {{-- Note Date --}}
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label fw-semibold">Adjustment Date <span class="text-danger">*</span></label>
                        <input type="date" name="note_date" class="form-control" value="{{ old('note_date', date('Y-m-d')) }}" required>
                    </div>

                    {{-- Link Purchase Bill --}}
                    <div class="col-12 col-md-6 col-lg-4">
                        <label class="form-label fw-semibold">Linked Purchase Bill <span class="text-muted fw-normal">(Optional)</span></label>
                        <select name="purchase_id" id="purchase_id" class="form-select">
                            <option value="">Direct Supplier Ledger Adjustment (No Bill Link)</option>
                            @if(isset($purchases))
                                @foreach($purchases as $bill)
                                    <option value="{{ $bill->id }}"
                                            data-supplier="{{ $bill->supplier_id }}"
                                            data-due="{{ max(0, (float)$bill->grand_total - (float)$bill->paid_amount) }}"
                                            {{ old('purchase_id', request('purchase_id')) == $bill->id ? 'selected' : '' }}>
                                        #{{ $bill->purchase_number }} — Total: ৳{{ number_format((float)$bill->grand_total, 2) }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <small class="text-muted" id="billDueNotice"></small>
                    </div>

                    {{-- Adjustment Reason --}}
                    <div class="col-12 col-md-6 col-lg-4">
                        <label class="form-label fw-semibold">Adjustment Reason <span class="text-danger">*</span></label>
                        <select name="reason_category" id="reason_category" class="form-select" required>
                            <option value="price_discrepancy" {{ old('reason_category') === 'price_discrepancy' ? 'selected' : '' }}>Rate / Price Discrepancy</option>
                            <option value="post_bill_rebate" {{ old('reason_category') === 'post_bill_rebate' ? 'selected' : '' }}>Post-Purchase Rebate / Cash Discount</option>
                            <option value="shortage_penalty" {{ old('reason_category') === 'shortage_penalty' ? 'selected' : '' }}>Delivery Shortage / SLA Penalty</option>
                            <option value="damaged_goods_allowance" {{ old('reason_category') === 'damaged_goods_allowance' ? 'selected' : '' }}>Damaged Goods Allowance</option>
                            <option value="freight_adjustment" {{ old('reason_category') === 'freight_adjustment' ? 'selected' : '' }}>Carriage / Freight Difference</option>
                            <option value="tax_vat_correction" {{ old('reason_category') === 'tax_vat_correction' ? 'selected' : '' }}>Tax / VAT Correction</option>
                            <option value="other" {{ old('reason_category') === 'other' ? 'selected' : '' }}>Other Commercial Reason</option>
                        </select>
                    </div>

                    {{-- Operating Branch --}}
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label fw-semibold">Branch</label>
                        <select name="branch_id" class="form-select">
                            <option value="">HQ Branch</option>
                            @foreach($branches ?? [] as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status --}}
                    <div class="col-6 col-md-3 col-lg-3">
                        <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="posted" {{ old('status', 'posted') === 'posted' ? 'selected' : '' }}>Approved &amp; Post to Ledger</option>
                            <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft / Pending Verification</option>
                        </select>
                    </div>

                    {{-- Vendor Ref --}}
                    <div class="col-6 col-md-3 col-lg-3">
                        <label class="form-label fw-semibold">Vendor Reference</label>
                        <input type="text" name="reference_no" class="form-control" placeholder="V-MEMO-2026-88" value="{{ old('reference_no') }}">
                    </div>

                    {{-- Attachment --}}
                    <div class="col-12 col-md-6 col-lg-4">
                        <label class="form-label fw-semibold">Supporting Document</label>
                        <input type="file" name="attachment_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    </div>

                    {{-- Reason / Description --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold">Adjustment Description <span class="text-danger">*</span></label>
                        <input type="text" name="reason" class="form-control" placeholder="Explain the reason for this adjustment..." value="{{ old('reason') }}" required>
                    </div>
                </div>
            </div>
        </div>

        {{-- Financial Valuation & Ledger Offset --}}
        <div class="card procurement-card mb-4">
            <div class="card-header procurement-card-header">
                <h6 class="fw-bold mb-0">
                    <i class="fas fa-coins text-primary me-2"></i>Financial Adjustment
                </h6>
            </div>

            <div class="card-body p-3 p-md-4">
                <div class="row g-3">

                    {{-- Base Amount --}}
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">Base Amount (৳) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" id="base_amount" class="form-control text-end fw-bold"
                               placeholder="0.00" value="{{ old('amount', 0) }}" min="0.01" required>
                    </div>

                    {{-- Tax / VAT --}}
                    <div class="col-6 col-md-4">
                        <label class="form-label fw-semibold">VAT / Tax (৳)</label>
                        <input type="number" step="0.01" name="tax_amount" id="tax_amount" class="form-control text-end"
                               placeholder="0.00" value="{{ old('tax_amount', 0) }}" min="0">
                    </div>

                    {{-- Net Total --}}
                    <div class="col-6 col-md-4">
                        <label class="form-label fw-semibold">Total Adjustment (৳)</label>
                        <input type="text" id="displayTotalAdjustment" class="form-control text-end fw-bold bg-light text-dark procurement-total-field" value="৳0.00" readonly>
                        <input type="hidden" name="total_amount" id="total_amount" value="0.00">
                    </div>

                    {{-- Ledger Offset --}}
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Ledger Offset Account</label>
                        <select name="chart_of_account_id" id="chart_of_account_id" class="form-select">
                            <option value="">Default Adjustment Account (Purchase Allowances / Expense)</option>
                            @foreach($accounts ?? [] as $acc)
                                <option value="{{ $acc->id }}" {{ old('chart_of_account_id') == $acc->id ? 'selected' : '' }}>
                                    {{ $acc->code ?? '' }} — {{ $acc->name }} ({{ ucfirst($acc->type ?? 'Expense') }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Select the account used to balance this supplier adjustment.</small>
                    </div>

                    {{-- Extended Narration --}}
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Audit Log Narration</label>
                        <input type="text" name="notes" class="form-control" placeholder="Additional audit remarks..." value="{{ old('notes') }}">
                    </div>
                </div>
            </div>

            {{-- Double-Entry Preview --}}
            <div class="card-body p-3 p-md-4 procurement-journal-section">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <div>
                        <h6 class="fw-bold text-dark mb-1">Journal Posting Preview</h6>
                        <p class="small text-muted mb-0">Automated double-entry representation</p>
                    </div>
                    <span class="badge rounded-pill bg-primary-subtle text-primary px-3 py-2">
                        <i class="fas fa-calculator me-1"></i> Preview
                    </span>
                </div>

                <div class="table-responsive procurement-table-wrapper">
                    <table class="table table-bordered table-hover align-middle mb-0 procurement-journal-table">
                        <thead>
                            <tr>
                                <th>Account Head</th>
                                <th>Classification</th>
                                <th class="text-end" style="width: 140px;">Debit (৳)</th>
                                <th class="text-end" style="width: 140px;">Credit (৳)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-semibold text-dark" id="journalHead1">Accounts Payable — Vendor</td>
                                <td class="text-muted" id="journalClass1">Current Liability</td>
                                <td class="text-end fw-bold text-success" id="journalDebit1">৳0.00</td>
                                <td class="text-end text-muted" id="journalCredit1">—</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold text-dark" id="journalHead2">Purchase Allowances / Adjustments</td>
                                <td class="text-muted" id="journalClass2">Cost / Expense Head</td>
                                <td class="text-end text-muted" id="journalDebit2">—</td>
                                <td class="text-end fw-bold text-danger" id="journalCredit2">৳0.00</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="text-end text-uppercase">Balanced Entry:</td>
                                <td class="text-end text-dark" id="journalTotalDebit">৳0.00</td>
                                <td class="text-end text-dark" id="journalTotalCredit">৳0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Footer Actions --}}
            <div class="card-footer procurement-card-footer">
                <a href="{{ route('admin.supplier-debit-credit-notes.index') }}" class="btn btn-sm btn-outline-secondary procurement-btn">Cancel</a>
                <button type="submit" class="btn btn-sm btn-primary px-4 procurement-btn">
                    <i class="fas fa-check-circle me-1"></i> Save &amp; Post Note
                </button>
            </div>
        </div>
    </form>
</div>

{{-- Page Scoped Styles --}}
<style>
/* ==========================================================
   Procurement Adjustment Note
   Kaiadmin Lite v1.2.0 - Classic / White Classic
   Scoped to .procurement-note-page
========================================================== */

.procurement-note-page {
    --pn-primary: var(--bs-primary, #1572e8);
    --pn-body: #f8f9fa;
    --pn-card: #ffffff;
    --pn-heading: #2a2f5b;
    --pn-text: #575962;
    --pn-muted: #8d9498;
    --pn-border: #e5e7eb;
    --pn-radius: 8px;
    --pn-shadow: 0 2px 10px rgba(0, 0, 0, .035);
    color: var(--pn-text);
    min-width: 0;
}

.procurement-note-page *,
.procurement-note-page *::before,
.procurement-note-page *::after {
    box-sizing: border-box;
}

.procurement-note-page .procurement-page-header {
    gap: 12px;
}

.procurement-note-page .procurement-page-title {
    color: var(--pn-heading) !important;
    font-size: clamp(17px, 1.5vw, 21px);
    line-height: 1.4;
    letter-spacing: -.3px;
}

.procurement-note-page .breadcrumb {
    font-size: 12px;
    margin-top: 5px;
}

.procurement-note-page .breadcrumb-item a {
    color: var(--pn-primary);
    text-decoration: none;
}

.procurement-note-page .breadcrumb-item.active {
    color: var(--pn-muted);
}

.procurement-note-page .procurement-card {
    background: var(--pn-card);
    border: 1px solid var(--pn-border) !important;
    border-radius: var(--pn-radius) !important;
    box-shadow: var(--pn-shadow);
    overflow: visible;
    min-width: 0;
}

.procurement-note-page .procurement-card-header {
    background: #fff;
    border-bottom: 1px solid var(--pn-border);
    padding: 15px 20px;
    border-radius: var(--pn-radius) var(--pn-radius) 0 0 !important;
}

.procurement-note-page .procurement-card-header h6 {
    color: var(--pn-heading);
    font-size: 14px;
    letter-spacing: -.15px;
}

.procurement-note-page .procurement-card-header i {
    font-size: 14px;
}

.procurement-note-page .card-body {
    min-width: 0;
}

.procurement-note-page .form-label {
    display: block;
    color: #41465b;
    font-size: 12.5px;
    font-weight: 600;
    margin-bottom: 7px;
}

.procurement-note-page .form-control,
.procurement-note-page .form-select {
    min-height: 39px;
    width: 100%;
    border: 1px solid #e2e5ec;
    border-radius: 6px;
    background-color: #fff;
    color: #343a40;
    font-size: 13px;
    padding: 8px 11px;
    box-shadow: none;
    transition: border-color .2s ease, box-shadow .2s ease;
}

.procurement-note-page .form-control:focus,
.procurement-note-page .form-select:focus {
    border-color: var(--pn-primary);
    box-shadow: 0 0 0 3px rgba(21, 114, 232, .10);
}

.procurement-note-page .form-control::placeholder {
    color: #adb3bd;
    font-size: 12px;
}

.procurement-note-page input[type="file"].form-control {
    padding: 5px 8px;
    min-height: 39px;
}

.procurement-note-page input[type="file"]::file-selector-button {
    border: 0;
    border-radius: 4px;
    background: #eef3fb;
    color: var(--pn-primary);
    padding: 6px 10px;
    margin-right: 10px;
    font-size: 12px;
    font-weight: 600;
}

.procurement-note-page .text-muted {
    color: var(--pn-muted) !important;
}

.procurement-note-page small.text-muted {
    display: inline-block;
    margin-top: 6px;
    font-size: 11.5px;
    line-height: 1.5;
}

.procurement-note-page .procurement-impact-banner {
    min-height: 68px;
    background: #f8f9fc;
    border: 1px solid var(--pn-border);
    border-radius: 7px !important;
    line-height: 1.55;
}

.procurement-note-page #impactTitle {
    font-size: 13px;
}

.procurement-note-page #impactDescription {
    color: var(--pn-text);
    font-size: 12px;
}

.procurement-note-page #supplierBalanceNotice {
    font-size: 13px;
}

.procurement-note-page #billDueNotice {
    font-size: 11px;
    margin-top: 5px;
}

.procurement-note-page .procurement-total-field {
    background: #f0f5ff !important;
    border-color: #dce7fa;
    color: var(--pn-primary) !important;
    font-size: 17px;
    min-height: 42px;
}

.procurement-note-page .procurement-journal-section {
    background: #f8f9fc;
    border-top: 1px solid var(--pn-border);
}

.procurement-note-page .procurement-table-wrapper {
    width: 100%;
    border: 1px solid var(--pn-border);
    border-radius: 7px;
    background: #fff;
}

.procurement-note-page .procurement-journal-table {
    min-width: 580px;
    font-size: 12px;
    border-color: var(--pn-border);
}

.procurement-note-page .procurement-journal-table thead th {
    background: #f3f5f9;
    color: #646b7a;
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .4px;
    padding: 12px 14px;
    white-space: nowrap;
    border-color: var(--pn-border);
}

.procurement-note-page .procurement-journal-table tbody td {
    padding: 12px 14px;
    vertical-align: middle;
    border-color: var(--pn-border);
    line-height: 1.5;
}

.procurement-note-page .procurement-journal-table tfoot td {
    padding: 12px 14px;
    background: #f3f5f9;
    border-color: var(--pn-border);
    font-size: 11px;
}

.procurement-note-page .procurement-card-footer {
    display: flex;
    justify-content: flex-end;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
    background: #fff;
    border-top: 1px solid var(--pn-border);
    padding: 14px 20px;
    border-radius: 0 0 var(--pn-radius) var(--pn-radius);
}

.procurement-note-page .procurement-btn {
    min-height: 36px;
    padding: 8px 15px;
    border-radius: 6px !important;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    justify-content: center;
    align-items: center;
    gap: 4px;
    transition: all .2s ease;
    text-decoration: none;
}

.procurement-note-page .btn-primary {
    box-shadow: 0 2px 5px rgba(21, 114, 232, .13);
}

.procurement-note-page .btn-primary:hover {
    box-shadow: 0 4px 10px rgba(21, 114, 232, .2);
    transform: translateY(-1px);
}

.procurement-note-page .btn-outline-secondary {
    border-color: #d9dde5;
    color: #606775;
}

.procurement-note-page .btn-outline-secondary:hover {
    background: #f3f5f9;
    border-color: #c8ced9;
    color: #343a40;
}

.procurement-note-page .procurement-alert {
    border: 1px solid rgba(220, 53, 69, .16);
    border-radius: 7px;
    padding: 15px 18px;
}

.procurement-note-page .procurement-alert ul {
    line-height: 1.8;
}

.procurement-note-page .badge {
    font-weight: 600;
    letter-spacing: .1px;
}

/* Tablet */
@media (max-width: 991.98px) {
    .procurement-note-page .procurement-card-header {
        padding: 14px 16px;
    }

    .procurement-note-page .card-body {
        padding: 16px !important;
    }

    .procurement-note-page .procurement-card-footer {
        padding: 13px 16px;
    }
}

/* Mobile Landscape & Portrait */
@media (max-width: 767.98px) {
    .procurement-note-page {
        padding-left: 10px !important;
        padding-right: 10px !important;
    }

    .procurement-note-page .procurement-page-header {
        align-items: flex-start !important;
    }

    .procurement-note-page .procurement-page-title {
        font-size: 17px;
    }

    .procurement-note-page .procurement-card {
        margin-bottom: 16px !important;
    }

    .procurement-note-page .card-body {
        padding: 14px !important;
    }

    .procurement-note-page .procurement-card-header {
        padding: 13px 14px;
    }

    .procurement-note-page .form-control,
    .procurement-note-page .form-select {
        min-height: 41px;
    }

    .procurement-note-page .procurement-impact-banner {
        min-height: auto;
    }

    .procurement-note-page .procurement-card-footer {
        padding: 12px 14px;
    }

    .procurement-note-page .procurement-btn {
        min-height: 39px;
    }
}

/* Small Mobile */
@media (max-width: 575.98px) {
    .procurement-note-page .procurement-page-header {
        flex-direction: column;
        align-items: stretch !important;
    }

    .procurement-note-page .procurement-page-header > div:last-child {
        width: 100%;
    }

    .procurement-note-page .procurement-page-header .procurement-btn {
        width: 100%;
    }

    .procurement-note-page .breadcrumb {
        font-size: 11px;
        flex-wrap: wrap;
    }

    .procurement-note-page .procurement-card-header h6 {
        font-size: 13px;
    }

    .procurement-note-page .procurement-card-footer {
        flex-direction: column-reverse;
        align-items: stretch;
    }

    .procurement-note-page .procurement-card-footer .btn,
    .procurement-note-page .procurement-card-footer a {
        width: 100%;
    }

    .procurement-note-page .procurement-journal-table {
        min-width: 550px;
    }

    .procurement-note-page .procurement-journal-table thead th,
    .procurement-note-page .procurement-journal-table tbody td,
    .procurement-note-page .procurement-journal-table tfoot td {
        padding: 10px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const noteTypeSelect = document.getElementById('note_type');
    const noteNumberInput = document.getElementById('note_number');
    const supplierSelect = document.getElementById('supplier_id');
    const supplierBalanceSpan = document.getElementById('supplierBalanceNotice');
    const purchaseSelect = document.getElementById('purchase_id');
    const billDueNotice = document.getElementById('billDueNotice');

    const baseAmountInput = document.getElementById('base_amount');
    const taxAmountInput = document.getElementById('tax_amount');
    const displayTotalAdj = document.getElementById('displayTotalAdjustment');
    const hiddenTotal = document.getElementById('total_amount');

    const impactBanner = document.getElementById('accountingImpactBanner');
    const impactTitle = document.getElementById('impactTitle');
    const impactDescription = document.getElementById('impactDescription');

    // Journal preview elements
    const journalHead1 = document.getElementById('journalHead1');
    const journalClass1 = document.getElementById('journalClass1');
    const journalDebit1 = document.getElementById('journalDebit1');
    const journalCredit1 = document.getElementById('journalCredit1');

    const journalHead2 = document.getElementById('journalHead2');
    const journalClass2 = document.getElementById('journalClass2');
    const journalDebit2 = document.getElementById('journalDebit2');
    const journalCredit2 = document.getElementById('journalCredit2');

    const journalTotalDebit = document.getElementById('journalTotalDebit');
    const journalTotalCredit = document.getElementById('journalTotalCredit');

    function updateNoteTypeState() {
        const isDebit = (noteTypeSelect.value === 'debit_note');

        if (isDebit) {
            impactBanner.className = 'p-2.5 rounded border small bg-warning-subtle text-warning-emphasis border-warning-subtle';
            impactTitle.innerText = 'Debit Note (Dr Impact)';
            impactDescription.innerText = 'Reduces your Accounts Payable liability to the supplier. Acts like a rebate, claim deduction, or defective allowance.';

            journalHead1.innerText = 'Accounts Payable &mdash; Supplier';
            journalClass1.innerText = 'Current Liability (Decreases due)';
            journalHead2.innerText = 'Purchase Allowances & Rebates Head';
            journalClass2.innerText = 'Direct Expense / Cost Offset';
        } else {
            impactBanner.className = 'p-2.5 rounded border small bg-danger-subtle text-danger-emphasis border-danger-subtle';
            impactTitle.innerText = 'Credit Note (Cr Impact)';
            impactDescription.innerText = 'Increases your Accounts Payable liability to the supplier. Acts like an additional carriage charge, price hike adjustment, or post-bill expense.';

            journalHead1.innerText = 'Procurement Cost / Additional Expense';
            journalClass1.innerText = 'Expense / Cost Head';
            journalHead2.innerText = 'Accounts Payable &mdash; Supplier';
            journalClass2.innerText = 'Current Liability (Increases due)';
        }

        calculateTotals();
    }

    function updateSupplierDetails() {
        const selected = supplierSelect.options[supplierSelect.selectedIndex];
        const balance = parseFloat(selected ? selected.getAttribute('data-balance') : 0) || 0;
        supplierBalanceSpan.innerText = '৳' + balance.toFixed(2);

        const supplierId = supplierSelect.value;
        if (!supplierId) return;

        // Fetch supplier bills dynamically if not already populated
        fetch(`{{ url('admin/procurement/suppliers') }}/${supplierId}/unpaid-bills`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(res => {
            if (res.success && res.data) {
                const currentVal = purchaseSelect.value;
                purchaseSelect.innerHTML = '<option value="">Direct Supplier Ledger Adjustment (No Bill Link)</option>';
                res.data.forEach(bill => {
                    const opt = document.createElement('option');
                    opt.value = bill.id;
                    opt.dataset.due = bill.due_amount;
                    opt.innerText = `#${bill.purchase_number} (Total: ৳${parseFloat(bill.grand_total).toFixed(2)}, Due: ৳${parseFloat(bill.due_amount).toFixed(2)})`;
                    if (bill.id == currentVal) opt.selected = true;
                    purchaseSelect.appendChild(opt);
                });
                updateBillDue();
            }
        })
        .catch(err => console.error(err));
    }

    function updateBillDue() {
        const selected = purchaseSelect.options[purchaseSelect.selectedIndex];
        if (selected && selected.dataset.due) {
            billDueNotice.innerText = 'Invoice Remaining Due: ৳' + parseFloat(selected.dataset.due).toFixed(2);
        } else {
            billDueNotice.innerText = '';
        }
    }

    function calculateTotals() {
        const base = parseFloat(baseAmountInput.value) || 0;
        const tax = parseFloat(taxAmountInput.value) || 0;
        const total = Math.max(0, base + tax);

        displayTotalAdj.value = '৳' + total.toFixed(2);
        hiddenTotal.value = total.toFixed(2);

        const isDebit = (noteTypeSelect.value === 'debit_note');

        if (isDebit) {
            journalDebit1.innerText = '৳' + total.toFixed(2);
            journalCredit1.innerText = '—';
            journalDebit2.innerText = '—';
            journalCredit2.innerText = '৳' + total.toFixed(2);
        } else {
            journalDebit1.innerText = '৳' + total.toFixed(2);
            journalCredit1.innerText = '—';
            journalDebit2.innerText = '—';
            journalCredit2.innerText = '৳' + total.toFixed(2);
        }

        journalTotalDebit.innerText = '৳' + total.toFixed(2);
        journalTotalCredit.innerText = '৳' + total.toFixed(2);
    }

    noteTypeSelect.addEventListener('change', updateNoteTypeState);
    supplierSelect.addEventListener('change', updateSupplierDetails);
    purchaseSelect.addEventListener('change', updateBillDue);
    baseAmountInput.addEventListener('input', calculateTotals);
    taxAmountInput.addEventListener('input', calculateTotals);

    // Initial setup
    updateNoteTypeState();
    if (supplierSelect.value) {
        updateSupplierDetails();
    }
    updateBillDue();
});
</script>
@endsection
