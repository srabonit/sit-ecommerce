@extends('layouts.admin')

@section('title', 'Adjustment Note #' . $note->note_number)

@section('admin_content')
@php
    $isDebit = ($note->note_type === 'debit_note');
    $baseAmt = (float) $note->amount;
    $taxAmt  = (float) ($note->tax_amount ?? 0);
    $totalAdj = (float) ($note->total_amount ?? ($baseAmt + $taxAmt));
@endphp

<div class="container-fluid px-3 py-3 adjustment-detail-page">

    {{-- Page Header --}}
    <div class="page-header mb-4">

        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">

            <div class="header-main">

                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">

                    <div class="page-icon {{ $isDebit ? 'icon-debit' : 'icon-credit' }}">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>

                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <h4 class="page-title mb-0">
                                {{ $isDebit ? 'Debit Note' : 'Credit Note' }}
                            </h4>

                            <span class="note-number-badge">
                                #{{ $note->note_number }}
                            </span>
                        </div>

                        <p class="page-subtitle mb-0">
                            Supplier ledger adjustment details and financial voucher
                        </p>
                    </div>

                </div>

                {{-- Note Type & Status --}}
                <div class="d-flex flex-wrap align-items-center gap-2 header-badges">

                    @if($isDebit)
                        <span class="note-type-badge badge-debit">
                            <i class="fas fa-minus-circle"></i>
                            Debit Note · Reduces Due
                        </span>
                    @else
                        <span class="note-type-badge badge-credit">
                            <i class="fas fa-plus-circle"></i>
                            Credit Note · Increases Due
                        </span>
                    @endif

                    @switch($note->status ?? 'posted')
                        @case('posted')
                        @case('approved')
                            <span class="status-badge status-posted">
                                <i class="fas fa-circle-check"></i>
                                Posted to Ledger
                            </span>
                            @break

                        @case('draft')
                        @case('pending')
                            <span class="status-badge status-draft">
                                <i class="fas fa-clock"></i>
                                Draft / Pending Approval
                            </span>
                            @break

                        @case('cancelled')
                            <span class="status-badge status-cancelled">
                                <i class="fas fa-ban"></i>
                                Void / Cancelled
                            </span>
                            @break

                        @default
                            <span class="status-badge status-default">
                                {{ ucfirst($note->status) }}
                            </span>
                    @endswitch

                </div>

                {{-- Breadcrumb --}}
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb custom-breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.supplier-debit-credit-notes.index') }}">
                                Adjustment Notes
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            {{ $note->note_number }}
                        </li>
                    </ol>
                </nav>

            </div>

            {{-- Header Actions --}}
            <div class="header-actions d-flex flex-wrap gap-2">

                <a href="{{ route('admin.supplier-debit-credit-notes.index') }}"
                   class="btn btn-outline-secondary action-btn">
                    <i class="fas fa-arrow-left"></i>
                    Back
                </a>

                <button type="button"
                        class="btn btn-outline-primary action-btn"
                        onclick="window.print()">
                    <i class="fas fa-print"></i>
                    Print Note
                </button>

                @if(($note->status ?? 'posted') === 'draft')
                    <form action="{{ route('admin.supplier-debit-credit-notes.approve', $note->id) }}"
                          method="POST"
                          onsubmit="return confirm('Post adjustment note #{{ $note->note_number }} to supplier ledger?');">
                        @csrf

                        <button type="submit" class="btn btn-success action-btn">
                            <i class="fas fa-check-circle"></i>
                            Approve &amp; Post
                        </button>
                    </form>
                @endif

                @if(($note->status ?? 'posted') !== 'cancelled')
                    <button type="button"
                            class="btn btn-outline-danger action-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#cancelNoteModal">
                        <i class="fas fa-ban"></i>
                        Void Note
                    </button>
                @endif

                @if($note->supplier)
                    <a href="{{ route('admin.suppliers.ledger', $note->supplier_id) }}"
                       class="btn btn-outline-dark action-btn">
                        <i class="fas fa-book-open"></i>
                        Ledger
                    </a>
                @endif

            </div>

        </div>
    </div>


    {{-- Financial Summary --}}
    <div class="row g-3 mb-4">

        {{-- Base Amount --}}
        <div class="col-6 col-xl-3">
            <div class="summary-card">
                <div class="summary-info">
                    <div class="summary-label">Base Amount</div>
                    <div class="summary-value">
                        ৳{{ number_format($baseAmt, 2) }}
                    </div>
                    <div class="summary-caption">Original adjustment value</div>
                </div>

                <div class="summary-icon summary-icon-blue">
                    <i class="fas fa-calculator"></i>
                </div>
            </div>
        </div>

        {{-- Tax --}}
        <div class="col-6 col-xl-3">
            <div class="summary-card">
                <div class="summary-info">
                    <div class="summary-label">VAT / Tax Adjustment</div>
                    <div class="summary-value">
                        ৳{{ number_format($taxAmt, 2) }}
                    </div>
                    <div class="summary-caption">Applicable tax amount</div>
                </div>

                <div class="summary-icon summary-icon-purple">
                    <i class="fas fa-percent"></i>
                </div>
            </div>
        </div>

        {{-- Total Adjustment --}}
        <div class="col-6 col-xl-3">
            <div class="summary-card">
                <div class="summary-info">
                    <div class="summary-label">Total Adjustment</div>
                    <div class="summary-value {{ $isDebit ? 'text-success' : 'text-danger' }}">
                        {{ $isDebit ? '-৳' : '+৳' }}{{ number_format($totalAdj, 2) }}
                    </div>
                    <div class="summary-caption">
                        {{ $isDebit ? 'Supplier liability reduction' : 'Additional supplier payable' }}
                    </div>
                </div>

                <div class="summary-icon {{ $isDebit ? 'summary-icon-green' : 'summary-icon-red' }}">
                    <i class="fas fa-scale-balanced"></i>
                </div>
            </div>
        </div>

        {{-- Note Date --}}
        <div class="col-6 col-xl-3">
            <div class="summary-card">
                <div class="summary-info">
                    <div class="summary-label">Adjustment Date</div>
                    <div class="summary-value summary-date">
                        {{ \Carbon\Carbon::parse($note->note_date)->format('d M, Y') }}
                    </div>
                    <div class="summary-caption">Recorded transaction date</div>
                </div>

                <div class="summary-icon summary-icon-orange">
                    <i class="fas fa-calendar-days"></i>
                </div>
            </div>
        </div>

    </div>


    {{-- Supplier & Reference Information --}}
    <div class="row g-3 mb-4">

        {{-- Supplier Information --}}
        <div class="col-12 col-xl-4">
            <div class="detail-card h-100">

                <div class="detail-card-header">
                    <div class="section-heading">
                        <div class="section-icon section-icon-blue">
                            <i class="fas fa-truck-field"></i>
                        </div>

                        <div>
                            <h6>Supplier Information</h6>
                            <p>Vendor contact and account details</p>
                        </div>
                    </div>

                    @if($note->supplier)
                        <a href="{{ route('admin.suppliers.ledger', $note->supplier->id) }}"
                           class="header-link">
                            Statement
                            <i class="fas fa-arrow-up-right-from-square"></i>
                        </a>
                    @endif
                </div>

                <div class="detail-card-body">

                    <div class="supplier-profile">

                        <div class="supplier-avatar">
                            <i class="fas fa-building"></i>
                        </div>

                        <div class="supplier-profile-info">
                            <h6>{{ $note->supplier->name ?? 'N/A' }}</h6>
                            <span>{{ $note->supplier->company_name ?? 'Individual Supplier' }}</span>
                        </div>

                    </div>

                    <div class="info-list">

                        <div class="info-item">
                            <span class="info-icon">
                                <i class="fas fa-phone"></i>
                            </span>

                            <div>
                                <small>Phone Number</small>
                                <strong>{{ $note->supplier->phone ?? '—' }}</strong>
                            </div>
                        </div>

                        <div class="info-item">
                            <span class="info-icon">
                                <i class="fas fa-envelope"></i>
                            </span>

                            <div>
                                <small>Email Address</small>
                                <strong>{{ $note->supplier->email ?? '—' }}</strong>
                            </div>
                        </div>

                        @if($note->supplier && $note->supplier->address)
                            <div class="info-item">
                                <span class="info-icon">
                                    <i class="fas fa-location-dot"></i>
                                </span>

                                <div>
                                    <small>Business Address</small>
                                    <strong>{{ $note->supplier->address }}</strong>
                                </div>
                            </div>
                        @endif

                    </div>

                </div>
            </div>
        </div>


        {{-- Commercial References --}}
        <div class="col-12 col-xl-4">
            <div class="detail-card h-100">

                <div class="detail-card-header">
                    <div class="section-heading">
                        <div class="section-icon section-icon-purple">
                            <i class="fas fa-file-contract"></i>
                        </div>

                        <div>
                            <h6>Commercial References</h6>
                            <p>Transaction references and documents</p>
                        </div>
                    </div>
                </div>

                <div class="detail-card-body">

                    <div class="reference-list">

                        <div class="reference-row">
                            <span>Adjustment Date</span>
                            <strong>
                                {{ \Carbon\Carbon::parse($note->note_date)->format('d M, Y') }}
                            </strong>
                        </div>

                        <div class="reference-row">
                            <span>Reason Category</span>
                            <strong>
                                {{ ucwords(str_replace('_', ' ', $note->reason_category ?? 'General Adjustment')) }}
                            </strong>
                        </div>

                        <div class="reference-row">
                            <span>Linked Purchase Bill</span>

                            <strong>
                                @if($note->purchase_id)
                                    <a href="{{ route('admin.purchases.show', $note->purchase_id) }}">
                                        #{{ $note->purchase->purchase_number ?? $note->purchase_id }}
                                    </a>
                                @else
                                    <span class="text-muted">Direct Ledger</span>
                                @endif
                            </strong>
                        </div>

                        <div class="reference-row">
                            <span>Vendor Reference</span>
                            <strong class="font-monospace">
                                {{ $note->reference_no ?? '—' }}
                            </strong>
                        </div>

                        <div class="reference-row reference-row-last">
                            <span>Approval / Memo Scan</span>

                            <strong>
                                @if($note->attachment_file)
                                    <a href="{{ asset('storage/' . $note->attachment_file) }}"
                                       target="_blank"
                                       class="attachment-link">
                                        <i class="fas fa-file-pdf"></i>
                                        View Scan
                                    </a>
                                @else
                                    <span class="text-muted">No Attachment</span>
                                @endif
                            </strong>
                        </div>

                    </div>

                </div>
            </div>
        </div>


        {{-- Financial Valuation --}}
        <div class="col-12 col-xl-4">
            <div class="detail-card h-100">

                <div class="detail-card-header">
                    <div class="section-heading">
                        <div class="section-icon section-icon-green">
                            <i class="fas fa-scale-balanced"></i>
                        </div>

                        <div>
                            <h6>Financial Valuation</h6>
                            <p>Accounting and ledger information</p>
                        </div>
                    </div>
                </div>

                <div class="detail-card-body">

                    <div class="reference-list">

                        <div class="reference-row">
                            <span>Base Amount</span>
                            <strong>৳{{ number_format($baseAmt, 2) }}</strong>
                        </div>

                        <div class="reference-row">
                            <span>VAT / Tax</span>
                            <strong>৳{{ number_format($taxAmt, 2) }}</strong>
                        </div>

                        <div class="reference-row">
                            <span>Balancing Ledger Head</span>
                            <strong>
                                {{ $note->chartOfAccount->name ?? ($note->account->name ?? 'Purchase Allowances') }}
                            </strong>
                        </div>

                        <div class="reference-row">
                            <span>Operating Branch</span>
                            <strong>
                                {{ $note->branch->name ?? 'HQ Branch' }}
                            </strong>
                        </div>

                    </div>

                    <div class="financial-total-box {{ $isDebit ? 'financial-debit' : 'financial-credit' }}">
                        <div>
                            <small>Net Adjustment</small>
                            <span>
                                {{ $isDebit ? 'Liability Reduction' : 'Additional Liability' }}
                            </span>
                        </div>

                        <strong>
                            {{ $isDebit ? '-৳' : '+৳' }}{{ number_format($totalAdj, 2) }}
                        </strong>
                    </div>

                </div>
            </div>
        </div>

    </div>


    {{-- Adjustment Rationale --}}
    <div class="detail-card mb-4">

        <div class="detail-card-header">
            <div class="section-heading">
                <div class="section-icon section-icon-orange">
                    <i class="fas fa-align-left"></i>
                </div>

                <div>
                    <h6>Adjustment Rationale &amp; Audit Log</h6>
                    <p>Business justification and supporting narration</p>
                </div>
            </div>
        </div>

        <div class="detail-card-body">

            <div class="rationale-block">
                <div class="rationale-label">
                    <i class="fas fa-file-lines"></i>
                    Commercial Reason / Justification
                </div>

                <div class="rationale-content">
                    {{ $note->reason }}
                </div>
            </div>

            @if($note->notes)
                <div class="rationale-block mt-3">
                    <div class="rationale-label">
                        <i class="fas fa-clipboard-list"></i>
                        Extended Audit Narration
                    </div>

                    <div class="rationale-content rationale-secondary">
                        {{ $note->notes }}
                    </div>
                </div>
            @endif

        </div>
    </div>


    {{-- Journal Entries --}}
    <div class="detail-card mb-4">

        <div class="detail-card-header journal-header">
            <div class="section-heading">
                <div class="section-icon section-icon-blue">
                    <i class="fas fa-book-open"></i>
                </div>

                <div>
                    <h6>Automated Ledger Journal Entries</h6>
                    <p>Double-entry accounting representation of this adjustment</p>
                </div>
            </div>

            <span class="balanced-indicator">
                <i class="fas fa-check-circle"></i>
                Balanced Entry
            </span>
        </div>

        <div class="journal-table-wrapper table-responsive">

            <table class="table journal-table align-middle mb-0">

                <thead>
                    <tr>
                        <th>Account Head</th>
                        <th>Classification</th>
                        <th>Ledger Impact</th>
                        <th class="text-end">Debit (৳)</th>
                        <th class="text-end">Credit (৳)</th>
                    </tr>
                </thead>

                <tbody>

                    @if($isDebit)

                        {{-- Debit Note: Dr Accounts Payable, Cr Purchase Allowances/Expense --}}

                        <tr>
                            <td>
                                <div class="journal-account">
                                    <span class="account-indicator indicator-debit"></span>
                                    <strong>
                                        Accounts Payable &mdash; {{ $note->supplier->name ?? 'Supplier' }}
                                    </strong>
                                </div>
                            </td>

                            <td>
                                <span class="classification-label">Current Liability</span>
                            </td>

                            <td>
                                <span class="ledger-impact impact-positive">
                                    <i class="fas fa-arrow-down"></i>
                                    Liability Reduction
                                </span>
                            </td>

                            <td class="text-end">
                                <strong class="journal-debit">
                                    ৳{{ number_format($totalAdj, 2) }}
                                </strong>
                            </td>

                            <td class="text-end">
                                <span class="journal-empty">—</span>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <div class="journal-account">
                                    <span class="account-indicator indicator-credit"></span>
                                    <strong>
                                        {{ $note->chartOfAccount->name ?? ($note->account->name ?? 'Purchase Allowances & Rebates') }}
                                    </strong>
                                </div>
                            </td>

                            <td>
                                <span class="classification-label">Expense / Cost Offset</span>
                            </td>

                            <td>
                                <span class="ledger-impact impact-neutral">
                                    Procurement Cost Recovery
                                </span>
                            </td>

                            <td class="text-end">
                                <span class="journal-empty">—</span>
                            </td>

                            <td class="text-end">
                                <strong class="journal-credit">
                                    ৳{{ number_format($totalAdj, 2) }}
                                </strong>
                            </td>
                        </tr>

                    @else

                        {{-- Credit Note: Dr Additional Cost/Expense, Cr Accounts Payable --}}

                        <tr>
                            <td>
                                <div class="journal-account">
                                    <span class="account-indicator indicator-debit"></span>
                                    <strong>
                                        {{ $note->chartOfAccount->name ?? ($note->account->name ?? 'Purchase Freight & Miscellaneous Surcharge') }}
                                    </strong>
                                </div>
                            </td>

                            <td>
                                <span class="classification-label">Cost / Expense Head</span>
                            </td>

                            <td>
                                <span class="ledger-impact impact-negative">
                                    <i class="fas fa-arrow-up"></i>
                                    Expense Addition
                                </span>
                            </td>

                            <td class="text-end">
                                <strong class="journal-debit">
                                    ৳{{ number_format($totalAdj, 2) }}
                                </strong>
                            </td>

                            <td class="text-end">
                                <span class="journal-empty">—</span>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <div class="journal-account">
                                    <span class="account-indicator indicator-credit"></span>
                                    <strong>
                                        Accounts Payable &mdash; {{ $note->supplier->name ?? 'Supplier' }}
                                    </strong>
                                </div>
                            </td>

                            <td>
                                <span class="classification-label">Current Liability</span>
                            </td>

                            <td>
                                <span class="ledger-impact impact-negative">
                                    <i class="fas fa-arrow-up"></i>
                                    Liability Addition
                                </span>
                            </td>

                            <td class="text-end">
                                <span class="journal-empty">—</span>
                            </td>

                            <td class="text-end">
                                <strong class="journal-credit">
                                    ৳{{ number_format($totalAdj, 2) }}
                                </strong>
                            </td>
                        </tr>

                    @endif

                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end">
                            <strong>Balanced Posting Total</strong>
                        </td>

                        <td class="text-end">
                            <strong>৳{{ number_format($totalAdj, 2) }}</strong>
                        </td>

                        <td class="text-end">
                            <strong>৳{{ number_format($totalAdj, 2) }}</strong>
                        </td>
                    </tr>
                </tfoot>

            </table>

        </div>

        <div class="journal-footer">
            <div class="journal-footer-info">
                <i class="fas fa-shield-halved"></i>
                Debit and credit totals are presented equally for this journal representation.
            </div>

            <div class="journal-footer-total">
                <span>Total Transaction Value</span>
                <strong>৳{{ number_format($totalAdj, 2) }}</strong>
            </div>
        </div>

    </div>

</div>


{{-- Void / Cancel Note Modal --}}
@if(($note->status ?? 'posted') !== 'cancelled')
    <div class="modal fade text-start adjustment-modal"
         id="cancelNoteModal"
         tabindex="-1"
         aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content">

                <div class="modal-header">
                    <div class="modal-heading">
                        <div class="modal-warning-icon">
                            <i class="fas fa-triangle-exclamation"></i>
                        </div>

                        <div>
                            <h6 class="modal-title">Void Adjustment Note</h6>
                            <p class="mb-0">
                                Note #{{ $note->note_number }}
                            </p>
                        </div>
                    </div>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close">
                    </button>
                </div>

                <form action="{{ route('admin.supplier-debit-credit-notes.cancel', $note->id) }}"
                      method="POST">

                    @csrf

                    <div class="modal-body">

                        <div class="void-warning">
                            <i class="fas fa-circle-info"></i>

                            <p>
                                Voiding this adjustment note will reverse the journal entry impact and restore the supplier's previous ledger balance.
                            </p>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">
                                Reason for Voiding
                                <span class="text-danger">*</span>
                            </label>

                            <textarea name="cancel_reason"
                                      rows="4"
                                      class="form-control"
                                      required
                                      placeholder="Specify reason (e.g., entered in error, duplicate note, revised agreement)..."></textarea>
                        </div>

                    </div>

                    <div class="modal-footer">

                        <button type="button"
                                class="btn btn-light"
                                data-bs-dismiss="modal">
                            Close
                        </button>

                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-ban me-1"></i>
                            Confirm Void Note
                        </button>

                    </div>

                </form>

            </div>
        </div>
    </div>
@endif


{{-- Page Styles --}}
<style>
    .adjustment-detail-page {
        --ad-primary: #3157d5;
        --ad-primary-soft: #eef2ff;
        --ad-text: #263247;
        --ad-muted: #8590a2;
        --ad-border: #e8ecf2;
        --ad-surface: #ffffff;
        color: var(--ad-text);
    }

    /* Header */
    .adjustment-detail-page .page-header {
        padding-top: 2px;
    }

    .adjustment-detail-page .header-main {
        min-width: 250px;
    }

    .adjustment-detail-page .page-icon {
        width: 46px;
        height: 46px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 12px;
        font-size: 19px;
    }

    .adjustment-detail-page .icon-debit {
        color: #a66c05;
        background: #fff5dc;
    }

    .adjustment-detail-page .icon-credit {
        color: #d13c4b;
        background: #fff0f1;
    }

    .adjustment-detail-page .page-title {
        color: #202938;
        font-size: 20px;
        font-weight: 750;
        letter-spacing: -.5px;
    }

    .adjustment-detail-page .note-number-badge {
        padding: 4px 9px;
        color: #475467;
        background: #f1f3f7;
        border: 1px solid #e7eaf0;
        border-radius: 6px;
        font-family: monospace;
        font-size: 11px;
        font-weight: 700;
    }

    .adjustment-detail-page .page-subtitle {
        margin-top: 4px;
        color: var(--ad-muted);
        font-size: 11px;
    }

    .adjustment-detail-page .custom-breadcrumb {
        margin-top: 13px;
        font-size: 11px;
    }

    .adjustment-detail-page .custom-breadcrumb a {
        color: #788497;
        text-decoration: none;
    }

    .adjustment-detail-page .custom-breadcrumb a:hover {
        color: var(--ad-primary);
    }

    .adjustment-detail-page .custom-breadcrumb .active {
        color: #344054;
        font-weight: 600;
    }

    .adjustment-detail-page .header-badges {
        margin-top: 11px;
    }

    .adjustment-detail-page .note-type-badge,
    .adjustment-detail-page .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 9px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .adjustment-detail-page .badge-debit {
        color: #986a0a;
        background: #fff7e2;
        border: 1px solid #f5e6bb;
    }

    .adjustment-detail-page .badge-credit {
        color: #c63b49;
        background: #fff0f1;
        border: 1px solid #f7dadd;
    }

    .adjustment-detail-page .status-posted {
        color: #198754;
        background: #eaf8f0;
        border: 1px solid #d6efdf;
    }

    .adjustment-detail-page .status-draft {
        color: #a0710c;
        background: #fff7e2;
        border: 1px solid #f5e6bb;
    }

    .adjustment-detail-page .status-cancelled {
        color: #c63b49;
        background: #fff0f1;
        border: 1px solid #f7dadd;
    }

    .adjustment-detail-page .status-default {
        color: #667085;
        background: #f2f4f7;
        border: 1px solid #e5e7eb;
    }

    /* Header Buttons */
    .adjustment-detail-page .header-actions {
        align-items: center;
    }

    .adjustment-detail-page .action-btn {
        min-height: 37px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 7px 12px;
        border-radius: 7px;
        font-size: 11px;
        font-weight: 650;
        white-space: nowrap;
    }

    .adjustment-detail-page .header-actions .btn-success {
        background: #198754;
        border-color: #198754;
    }

    /* Summary Cards */
    .adjustment-detail-page .summary-card {
        min-height: 112px;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 16px;
        background: #fff;
        border: 1px solid var(--ad-border);
        border-radius: 11px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, .035);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .adjustment-detail-page .summary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 7px 18px rgba(15, 23, 42, .07);
    }

    .adjustment-detail-page .summary-info {
        min-width: 0;
    }

    .adjustment-detail-page .summary-label {
        color: #7d8899;
        font-size: 11px;
        font-weight: 650;
        margin-bottom: 8px;
    }

    .adjustment-detail-page .summary-value {
        color: #263247;
        font-size: clamp(17px, 1.5vw, 22px);
        font-weight: 750;
        letter-spacing: -.5px;
        line-height: 1.25;
        white-space: nowrap;
    }

    .adjustment-detail-page .summary-date {
        font-size: 18px;
    }

    .adjustment-detail-page .summary-caption {
        margin-top: 5px;
        color: #9aa3b1;
        font-size: 10px;
    }

    .adjustment-detail-page .summary-icon {
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 10px;
        font-size: 16px;
    }

    .adjustment-detail-page .summary-icon-blue {
        color: #3157d5;
        background: #eef2ff;
    }

    .adjustment-detail-page .summary-icon-purple {
        color: #7954c7;
        background: #f3efff;
    }

    .adjustment-detail-page .summary-icon-green {
        color: #198754;
        background: #eaf8f0;
    }

    .adjustment-detail-page .summary-icon-red {
        color: #d13c4b;
        background: #fff0f1;
    }

    .adjustment-detail-page .summary-icon-orange {
        color: #c47c1c;
        background: #fff5e8;
    }

    /* Detail Cards */
    .adjustment-detail-page .detail-card {
        background: #fff;
        border: 1px solid var(--ad-border);
        border-radius: 11px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, .035);
        overflow: hidden;
    }

    .adjustment-detail-page .detail-card-header {
        min-height: 65px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 17px;
        background: #fff;
        border-bottom: 1px solid var(--ad-border);
    }

    .adjustment-detail-page .section-heading {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .adjustment-detail-page .section-icon {
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 9px;
        font-size: 14px;
    }

    .adjustment-detail-page .section-icon-blue {
        color: #3157d5;
        background: #eef2ff;
    }

    .adjustment-detail-page .section-icon-purple {
        color: #7954c7;
        background: #f3efff;
    }

    .adjustment-detail-page .section-icon-green {
        color: #198754;
        background: #eaf8f0;
    }

    .adjustment-detail-page .section-icon-orange {
        color: #c47c1c;
        background: #fff5e8;
    }

    .adjustment-detail-page .section-heading h6 {
        margin: 0;
        color: #293449;
        font-size: 12.5px;
        font-weight: 750;
    }

    .adjustment-detail-page .section-heading p {
        margin: 4px 0 0;
        color: #9aa3b1;
        font-size: 10px;
    }

    .adjustment-detail-page .header-link {
        color: var(--ad-primary);
        font-size: 10.5px;
        font-weight: 650;
        text-decoration: none;
        white-space: nowrap;
    }

    .adjustment-detail-page .header-link i {
        margin-left: 3px;
        font-size: 9px;
    }

    .adjustment-detail-page .detail-card-body {
        padding: 16px;
    }

    /* Supplier Profile */
    .adjustment-detail-page .supplier-profile {
        display: flex;
        align-items: center;
        gap: 12px;
        padding-bottom: 15px;
        margin-bottom: 12px;
        border-bottom: 1px solid #edf0f4;
    }

    .adjustment-detail-page .supplier-avatar {
        width: 43px;
        height: 43px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: #3157d5;
        background: #eef2ff;
        border-radius: 11px;
        font-size: 17px;
    }

    .adjustment-detail-page .supplier-profile-info h6 {
        margin: 0 0 4px;
        color: #263247;
        font-size: 13px;
        font-weight: 750;
    }

    .adjustment-detail-page .supplier-profile-info span {
        color: #8c96a7;
        font-size: 10.5px;
    }

    .adjustment-detail-page .info-list {
        display: flex;
        flex-direction: column;
        gap: 13px;
    }

    .adjustment-detail-page .info-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }

    .adjustment-detail-page .info-icon {
        width: 26px;
        height: 26px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: #8793a6;
        background: #f4f6f9;
        border-radius: 7px;
        font-size: 11px;
    }

    .adjustment-detail-page .info-item small {
        display: block;
        margin-bottom: 3px;
        color: #9aa3b1;
        font-size: 9.5px;
    }

    .adjustment-detail-page .info-item strong {
        display: block;
        color: #465267;
        font-size: 11px;
        font-weight: 600;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    /* References */
    .adjustment-detail-page .reference-list {
        display: flex;
        flex-direction: column;
    }

    .adjustment-detail-page .reference-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        padding: 11px 0;
        border-bottom: 1px solid #edf0f4;
    }

    .adjustment-detail-page .reference-row:first-child {
        padding-top: 0;
    }

    .adjustment-detail-page .reference-row span {
        color: #8993a4;
        font-size: 10.5px;
        flex-shrink: 0;
    }

    .adjustment-detail-page .reference-row strong {
        color: #344054;
        font-size: 10.5px;
        font-weight: 650;
        text-align: right;
        overflow-wrap: anywhere;
    }

    .adjustment-detail-page .reference-row strong a {
        color: #3157d5;
        text-decoration: none;
    }

    .adjustment-detail-page .reference-row strong a:hover {
        text-decoration: underline;
    }

    .adjustment-detail-page .reference-row-last {
        padding-bottom: 0;
        border-bottom: 0;
    }

    .adjustment-detail-page .attachment-link {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 8px;
        color: #3157d5 !important;
        background: #eef2ff;
        border-radius: 5px;
        font-size: 10px;
        white-space: nowrap;
    }

    /* Financial Total */
    .adjustment-detail-page .financial-total-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 12px;
        margin-top: 15px;
        border-radius: 8px;
    }

    .adjustment-detail-page .financial-debit {
        color: #198754;
        background: #eaf8f0;
        border: 1px solid #d8efdf;
    }

    .adjustment-detail-page .financial-credit {
        color: #d13c4b;
        background: #fff0f1;
        border: 1px solid #f7dadd;
    }

    .adjustment-detail-page .financial-total-box small {
        display: block;
        font-size: 10px;
        font-weight: 750;
    }

    .adjustment-detail-page .financial-total-box span {
        display: block;
        margin-top: 3px;
        font-size: 9px;
        opacity: .8;
    }

    .adjustment-detail-page .financial-total-box strong {
        font-size: 17px;
        font-weight: 800;
        white-space: nowrap;
    }

    /* Rationale */
    .adjustment-detail-page .rationale-block {
        min-width: 0;
    }

    .adjustment-detail-page .rationale-label {
        display: flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 8px;
        color: #647084;
        font-size: 10.5px;
        font-weight: 750;
    }

    .adjustment-detail-page .rationale-label i {
        color: #3157d5;
        font-size: 12px;
    }

    .adjustment-detail-page .rationale-content {
        padding: 13px 14px;
        color: #465267;
        background: #f8f9fc;
        border: 1px solid #e9edf3;
        border-radius: 8px;
        font-size: 11.5px;
        line-height: 1.8;
        overflow-wrap: anywhere;
        white-space: pre-line;
    }

    .adjustment-detail-page .rationale-secondary {
        color: #687386;
        background: #fbfcfe;
    }

    /* Journal */
    .adjustment-detail-page .balanced-indicator {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        color: #198754;
        background: #eaf8f0;
        border: 1px solid #d6efdf;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .adjustment-detail-page .journal-table-wrapper {
        width: 100%;
    }

    .adjustment-detail-page .journal-table {
        min-width: 720px;
        font-size: 11px;
    }

    .adjustment-detail-page .journal-table thead th {
        padding: 13px 14px;
        color: #788497;
        background: #f7f8fb;
        border-bottom: 1px solid #e7ebf1;
        font-size: 9.5px;
        font-weight: 750;
        letter-spacing: .4px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .adjustment-detail-page .journal-table tbody td {
        padding: 14px;
        color: #465267;
        border-color: #edf0f4;
    }

    .adjustment-detail-page .journal-account {
        display: flex;
        align-items: center;
        gap: 9px;
    }

    .adjustment-detail-page .journal-account strong {
        color: #344054;
        font-size: 11px;
        font-weight: 700;
    }

    .adjustment-detail-page .account-indicator {
        width: 7px;
        height: 7px;
        flex-shrink: 0;
        border-radius: 50%;
    }

    .adjustment-detail-page .indicator-debit {
        background: #3157d5;
    }

    .adjustment-detail-page .indicator-credit {
        background: #198754;
    }

    .adjustment-detail-page .classification-label {
        color: #7f8a9b;
        font-size: 10px;
    }

    .adjustment-detail-page .ledger-impact {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 10px;
        font-weight: 650;
    }

    .adjustment-detail-page .impact-positive {
        color: #198754;
    }

    .adjustment-detail-page .impact-negative {
        color: #d13c4b;
    }

    .adjustment-detail-page .impact-neutral {
        color: #718096;
    }

    .adjustment-detail-page .journal-debit {
        color: #198754;
        font-size: 11.5px;
        font-weight: 750;
        white-space: nowrap;
    }

    .adjustment-detail-page .journal-credit {
        color: #3157d5;
        font-size: 11.5px;
        font-weight: 750;
        white-space: nowrap;
    }

    .adjustment-detail-page .journal-empty {
        color: #b5bdc9;
    }

    .adjustment-detail-page .journal-table tfoot td {
        padding: 13px 14px;
        color: #344054;
        background: #f5f7fb;
        border-top: 1px solid #e5eaf1;
        font-size: 11px;
    }

    .adjustment-detail-page .journal-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        padding: 13px 16px;
        background: #fbfcfe;
        border-top: 1px solid var(--ad-border);
    }

    .adjustment-detail-page .journal-footer-info {
        color: #8b95a5;
        font-size: 10px;
    }

    .adjustment-detail-page .journal-footer-info i {
        margin-right: 5px;
        color: #198754;
    }

    .adjustment-detail-page .journal-footer-total {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .adjustment-detail-page .journal-footer-total span {
        color: #8791a1;
        font-size: 10px;
    }

    .adjustment-detail-page .journal-footer-total strong {
        color: #263247;
        font-size: 14px;
        font-weight: 800;
    }

    /* Void Modal */
    .adjustment-modal .modal-content {
        overflow: hidden;
        border: 1px solid #e8ecf2;
        border-radius: 12px;
        box-shadow: 0 15px 45px rgba(15, 23, 42, .16);
    }

    .adjustment-modal .modal-header {
        display: flex;
        align-items: center;
        padding: 16px 18px;
        background: #fffafa;
        border-bottom: 1px solid #f0e3e4;
    }

    .adjustment-modal .modal-heading {
        display: flex;
        align-items: center;
        gap: 11px;
    }

    .adjustment-modal .modal-warning-icon {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #dc3545;
        background: #fff0f1;
        border-radius: 10px;
        font-size: 16px;
    }

    .adjustment-modal .modal-title {
        color: #c03948;
        font-size: 13px;
        font-weight: 750;
    }

    .adjustment-modal .modal-heading p {
        margin-top: 3px;
        color: #8993a4;
        font-size: 10px;
    }

    .adjustment-modal .modal-body {
        padding: 18px;
    }

    .adjustment-modal .void-warning {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px;
        margin-bottom: 17px;
        color: #94620a;
        background: #fff8e8;
        border: 1px solid #f5e8c7;
        border-radius: 8px;
    }

    .adjustment-modal .void-warning i {
        margin-top: 2px;
        font-size: 13px;
    }

    .adjustment-modal .void-warning p {
        margin: 0;
        font-size: 10.5px;
        line-height: 1.7;
    }

    .adjustment-modal .form-label {
        margin-bottom: 7px;
        color: #465267;
        font-size: 11px;
        font-weight: 700;
    }

    .adjustment-modal .form-control {
        border-color: #e1e6ee;
        border-radius: 8px;
        font-size: 11px;
        box-shadow: none;
    }

    .adjustment-modal .form-control:focus {
        border-color: #91a8f3;
        box-shadow: 0 0 0 3px rgba(49, 87, 213, .08);
    }

    .adjustment-modal .modal-footer {
        padding: 12px 18px;
        background: #fafbfc;
        border-top: 1px solid #edf0f4;
    }

    .adjustment-modal .modal-footer .btn {
        min-height: 35px;
        padding: 7px 13px;
        border-radius: 7px;
        font-size: 11px;
        font-weight: 650;
    }

    /* Responsive */
    @media (max-width: 1199.98px) {
        .adjustment-detail-page .header-actions {
            width: 100%;
        }
    }

    @media (max-width: 767.98px) {
        .adjustment-detail-page .page-title {
            font-size: 17px;
        }

        .adjustment-detail-page .header-actions {
            gap: 7px !important;
        }

        .adjustment-detail-page .action-btn {
            min-height: 35px;
            padding: 6px 9px;
            font-size: 10px;
        }

        .adjustment-detail-page .summary-card {
            min-height: 100px;
            padding: 12px;
        }

        .adjustment-detail-page .summary-icon {
            width: 35px;
            height: 35px;
            font-size: 14px;
        }

        .adjustment-detail-page .detail-card-header {
            padding: 12px;
        }

        .adjustment-detail-page .detail-card-body {
            padding: 13px;
        }

        .adjustment-detail-page .journal-header {
            align-items: flex-start;
            flex-direction: column;
        }
    }

    @media (max-width: 575.98px) {
        .adjustment-detail-page {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        .adjustment-detail-page .page-icon {
            width: 39px;
            height: 39px;
            font-size: 16px;
        }

        .adjustment-detail-page .page-title {
            font-size: 15px;
        }

        .adjustment-detail-page .page-subtitle {
            font-size: 10px;
        }

        .adjustment-detail-page .header-actions {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            width: 100%;
        }

        .adjustment-detail-page .header-actions > * {
            min-width: 0;
        }

        .adjustment-detail-page .header-actions form {
            display: flex;
        }

        .adjustment-detail-page .header-actions form button,
        .adjustment-detail-page .header-actions > a,
        .adjustment-detail-page .header-actions > button {
            width: 100%;
        }

        .adjustment-detail-page .action-btn {
            width: 100%;
            padding: 7px 5px;
            font-size: 9.5px;
        }

        .adjustment-detail-page .summary-card {
            min-height: 94px;
            padding: 10px;
        }

        .adjustment-detail-page .summary-label {
            font-size: 9.5px;
            margin-bottom: 6px;
        }

        .adjustment-detail-page .summary-value {
            font-size: 15px;
        }

        .adjustment-detail-page .summary-date {
            font-size: 14px;
        }

        .adjustment-detail-page .summary-caption {
            font-size: 8.5px;
        }

        .adjustment-detail-page .summary-icon {
            width: 29px;
            height: 29px;
            font-size: 12px;
            border-radius: 8px;
        }

        .adjustment-detail-page .reference-row {
            gap: 8px;
        }

        .adjustment-detail-page .reference-row span,
        .adjustment-detail-page .reference-row strong {
            font-size: 9.5px;
        }

        .adjustment-detail-page .journal-footer {
            align-items: flex-start;
            flex-direction: column;
        }

        .adjustment-detail-page .balanced-indicator {
            font-size: 9px;
        }
    }

    /* Print */
    @media print {

        body {
            background: #fff !important;
        }

        .admin-dark-sidebar,
        .admin-navbar,
        .breadcrumb,
        .header-actions,
        .adjustment-modal,
        .btn,
        .dropdown,
        .header-link,
        .attachment-link {
            display: none !important;
        }

        .adjustment-detail-page {
            padding: 0 !important;
        }

        .adjustment-detail-page .page-header {
            margin-bottom: 15px !important;
        }

        .adjustment-detail-page .detail-card,
        .adjustment-detail-page .summary-card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
            break-inside: avoid;
        }

        .adjustment-detail-page .journal-table-wrapper {
            overflow: visible !important;
        }

        .adjustment-detail-page .journal-table {
            min-width: 0 !important;
            width: 100% !important;
        }

        .adjustment-detail-page .journal-table th,
        .adjustment-detail-page .journal-table td {
            border: 1px solid #dee2e6 !important;
        }

        .adjustment-detail-page .summary-icon,
        .adjustment-detail-page .page-icon,
        .adjustment-detail-page .section-icon,
        .adjustment-detail-page .financial-total-box,
        .adjustment-detail-page .rationale-content,
        .adjustment-detail-page .status-badge,
        .adjustment-detail-page .note-type-badge {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        @page {
            size: A4 landscape;
            margin: 10mm;
        }
    }
</style>
@endsection