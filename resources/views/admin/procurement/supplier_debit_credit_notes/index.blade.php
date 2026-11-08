@extends('layouts.admin')

@section('title', 'Supplier Debit & Credit Notes Register')

@section('admin_content')
<div class="container-fluid px-3 py-3 supplier-notes-page">

    {{-- Page Header --}}
    <div class="page-header mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="page-icon">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div>
                        <h4 class="page-title mb-0">Supplier Debit &amp; Credit Notes</h4>
                        <p class="page-subtitle mb-0">Manage supplier ledger adjustment notes</p>
                    </div>
                </div>

                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb custom-breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">Procurement</li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Debit &amp; Credit Notes
                        </li>
                    </ol>
                </nav>
            </div>

            <div class="header-actions d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-outline-secondary btn-action" onclick="window.print()">
                    <i class="fas fa-print me-2"></i>Print Register
                </button>

                <div class="dropdown">
                    <button class="btn btn-primary btn-action dropdown-toggle" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-plus me-2"></i>Issue New Note
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end custom-dropdown">
                        <li>
                            <a class="dropdown-item"
                               href="{{ route('admin.supplier-debit-credit-notes.create', ['note_type' => 'debit_note']) }}">
                                <span class="dropdown-icon debit-icon">
                                    <i class="fas fa-minus-circle"></i>
                                </span>
                                <span>
                                    <strong>Debit Note</strong>
                                    <small>Reduces supplier due</small>
                                </span>
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item"
                               href="{{ route('admin.supplier-debit-credit-notes.create', ['note_type' => 'credit_note']) }}">
                                <span class="dropdown-icon credit-icon">
                                    <i class="fas fa-plus-circle"></i>
                                </span>
                                <span>
                                    <strong>Credit Note</strong>
                                    <small>Increases supplier due</small>
                                </span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistics --}}
    @php
        $totalNotesCount = $notes->total() ?? 0;
        $totalDebitVal  = (float) ($totalDebitAmount ?? 0);
        $totalCreditVal = (float) ($totalCreditAmount ?? 0);
        $netLedgerAdjustment = $totalCreditVal - $totalDebitVal;
    @endphp

    <div class="row g-3 mb-4">

        {{-- Total Notes --}}
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-content">
                    <div class="stat-label">Total Notes Issued</div>
                    <div class="stat-value text-dark">{{ number_format($totalNotesCount) }}</div>
                    <div class="stat-description">Adjustment records</div>
                </div>
                <div class="stat-icon icon-dark">
                    <i class="fas fa-file-invoice"></i>
                </div>
            </div>
        </div>

        {{-- Debit Notes --}}
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-content">
                    <div class="stat-label">Debit Notes</div>
                    <div class="stat-value text-success">৳{{ number_format($totalDebitVal, 2) }}</div>
                    <div class="stat-description">
                        <i class="fas fa-arrow-down me-1"></i>Reduces supplier due
                    </div>
                </div>
                <div class="stat-icon icon-success">
                    <i class="fas fa-arrow-trend-down"></i>
                </div>
            </div>
        </div>

        {{-- Credit Notes --}}
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-content">
                    <div class="stat-label">Credit Notes</div>
                    <div class="stat-value text-danger">৳{{ number_format($totalCreditVal, 2) }}</div>
                    <div class="stat-description">
                        <i class="fas fa-arrow-up me-1"></i>Increases supplier due
                    </div>
                </div>
                <div class="stat-icon icon-danger">
                    <i class="fas fa-arrow-trend-up"></i>
                </div>
            </div>
        </div>

        {{-- Net Adjustment --}}
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-content">
                    <div class="stat-label">Net Liability Adjustment</div>
                    <div class="stat-value {{ $netLedgerAdjustment > 0 ? 'text-danger' : ($netLedgerAdjustment < 0 ? 'text-success' : 'text-muted') }}">
                        {{ $netLedgerAdjustment > 0 ? '+' : '' }}৳{{ number_format($netLedgerAdjustment, 2) }}
                    </div>
                    <div class="stat-description">
                        {{ $netLedgerAdjustment >= 0 ? 'Payable adjustment' : 'Liability relief' }}
                    </div>
                </div>
                <div class="stat-icon icon-primary">
                    <i class="fas fa-scale-balanced"></i>
                </div>
            </div>
        </div>

    </div>

    {{-- Main Register Card --}}
    <div class="register-card">

        {{-- Filter Header --}}
        <div class="register-header">
            <div class="d-flex align-items-center gap-2 mb-3">
                <div class="section-icon">
                    <i class="fas fa-list-ul"></i>
                </div>
                <div>
                    <h6 class="section-title mb-0">Adjustment Notes Register</h6>
                    <p class="section-subtitle mb-0">Search, filter and manage supplier adjustments</p>
                </div>
            </div>

            <form action="{{ route('admin.supplier-debit-credit-notes.index') }}"
                  method="GET"
                  class="filter-form">

                <div class="row g-2 align-items-end">

                    {{-- Search --}}
                    <div class="col-12 col-lg-3">
                        <label class="filter-label">Search Records</label>
                        <div class="input-group custom-input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text"
                                   name="search"
                                   class="form-control"
                                   placeholder="Note #, Bill #, Reason..."
                                   value="{{ request('search') }}">
                        </div>
                    </div>

                    {{-- Supplier --}}
                    <div class="col-6 col-lg-2">
                        <label class="filter-label">Supplier</label>
                        <select name="supplier_id" class="form-select">
                            <option value="">All Suppliers</option>
                            @foreach($suppliers ?? [] as $supplier)
                                <option value="{{ $supplier->id }}"
                                    {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }} ({{ $supplier->company_name ?? 'Vendor' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Note Type --}}
                    <div class="col-6 col-lg-2">
                        <label class="filter-label">Note Type</label>
                        <select name="note_type" class="form-select">
                            <option value="">All Note Types</option>
                            <option value="debit_note"
                                {{ request('note_type') === 'debit_note' ? 'selected' : '' }}>
                                Debit Note
                            </option>
                            <option value="credit_note"
                                {{ request('note_type') === 'credit_note' ? 'selected' : '' }}>
                                Credit Note
                            </option>
                        </select>
                    </div>

                    {{-- Status --}}
                    <div class="col-6 col-lg-2">
                        <label class="filter-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="posted"
                                {{ request('status') === 'posted' ? 'selected' : '' }}>
                                Posted to Ledger
                            </option>
                            <option value="draft"
                                {{ request('status') === 'draft' ? 'selected' : '' }}>
                                Draft / Pending
                            </option>
                            <option value="cancelled"
                                {{ request('status') === 'cancelled' ? 'selected' : '' }}>
                                Void / Cancelled
                            </option>
                        </select>
                    </div>

                    {{-- Date --}}
                    <div class="col-6 col-lg-1">
                        <label class="filter-label">Date</label>
                        <input type="date"
                               name="note_date"
                               class="form-control"
                               value="{{ request('note_date') }}"
                               title="Note Date">
                    </div>

                    {{-- Actions --}}
                    <div class="col-12 col-lg-2">
                        <label class="filter-label d-none d-lg-block">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary filter-btn flex-grow-1">
                                <i class="fas fa-filter me-1"></i>Apply
                            </button>

                            @if(request()->anyFilled(['search', 'supplier_id', 'note_type', 'status', 'note_date']))
                                <a href="{{ route('admin.supplier-debit-credit-notes.index') }}"
                                   class="btn btn-outline-danger filter-clear-btn"
                                   title="Clear Filters">
                                    <i class="fas fa-rotate-left"></i>
                                </a>
                            @endif
                        </div>
                    </div>

                </div>
            </form>
        </div>

        {{-- Table Toolbar --}}
        <div class="table-toolbar">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="table-toolbar-icon">
                        <i class="fas fa-table-list"></i>
                    </span>
                    <span class="fw-semibold text-dark">Notes Overview</span>
                    <span class="record-count">{{ $totalNotesCount }} Records</span>
                </div>

                <span class="table-info-text">
                    <i class="fas fa-circle-info me-1"></i>
                    Select a note number to view details
                </span>
            </div>
        </div>

        {{-- Notes Table --}}
        <div class="table-responsive register-table-wrapper">
            <table class="table register-table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" style="min-width: 145px;">Note Number</th>
                        <th style="min-width: 105px;">Date</th>
                        <th style="min-width: 180px;">Supplier / Vendor</th>
                        <th style="min-width: 145px;">Voucher Type</th>
                        <th style="min-width: 130px;">Linked Bill</th>
                        <th style="min-width: 190px;">Reason / Particulars</th>
                        <th class="text-end" style="min-width: 135px;">Amount (৳)</th>
                        <th class="text-center" style="min-width: 120px;">Status</th>
                        <th class="text-end pe-4" style="min-width: 110px;">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($notes as $note)
                        @php
                            $isDebit = ($note->note_type === 'debit_note');
                            $amount  = (float) $note->amount;
                        @endphp

                        <tr>
                            {{-- Note Number --}}
                            <td class="ps-4">
                                <a href="{{ route('admin.supplier-debit-credit-notes.show', $note->id) }}"
                                   class="note-number {{ $isDebit ? 'note-debit' : 'note-credit' }}">
                                    {{ $note->note_number }}
                                </a>

                                @if($note->reference_no)
                                    <small class="reference-number">
                                        Ref: {{ $note->reference_no }}
                                    </small>
                                @endif
                            </td>

                            {{-- Date --}}
                            <td>
                                <span class="date-value">
                                    {{ \Carbon\Carbon::parse($note->note_date)->format('d M, Y') }}
                                </span>
                            </td>

                            {{-- Supplier --}}
                            <td>
                                <div class="supplier-name">
                                    {{ $note->supplier->name ?? '—' }}
                                </div>
                                @if($note->supplier->company_name ?? false)
                                    <small class="supplier-company">
                                        {{ $note->supplier->company_name }}
                                    </small>
                                @endif
                            </td>

                            {{-- Voucher Type --}}
                            <td>
                                @if($isDebit)
                                    <span class="type-badge type-debit">
                                        <i class="fas fa-minus-circle"></i>
                                        Debit Note
                                    </span>
                                @else
                                    <span class="type-badge type-credit">
                                        <i class="fas fa-plus-circle"></i>
                                        Credit Note
                                    </span>
                                @endif
                            </td>

                            {{-- Purchase Bill --}}
                            <td>
                                @if($note->purchase_id)
                                    <a href="{{ route('admin.purchases.show', $note->purchase_id) }}"
                                       class="bill-link">
                                        <i class="fas fa-file-invoice me-1"></i>
                                        Bill #{{ $note->purchase->purchase_number ?? $note->purchase_id }}
                                    </a>
                                @else
                                    <span class="direct-adjustment">
                                        <i class="fas fa-book me-1"></i>Direct Ledger
                                    </span>
                                @endif
                            </td>

                            {{-- Reason --}}
                            <td>
                                <div class="reason-text">
                                    {{ $note->reason ?? 'Ledger Adjustment' }}
                                </div>

                                @if($note->notes)
                                    <small class="reason-notes"
                                           title="{{ $note->notes }}">
                                        {{ $note->notes }}
                                    </small>
                                @endif
                            </td>

                            {{-- Amount --}}
                            <td class="text-end">
                                <span class="amount-value {{ $isDebit ? 'amount-debit' : 'amount-credit' }}">
                                    {{ $isDebit ? '-৳' : '+৳' }}{{ number_format($amount, 2) }}
                                </span>
                            </td>

                            {{-- Status --}}
                            <td class="text-center">
                                @switch($note->status ?? 'posted')
                                    @case('posted')
                                    @case('approved')
                                        <span class="status-badge status-posted">
                                            <i class="fas fa-circle-check"></i> Posted
                                        </span>
                                        @break

                                    @case('draft')
                                    @case('pending')
                                        <span class="status-badge status-draft">
                                            <i class="fas fa-clock"></i> Draft
                                        </span>
                                        @break

                                    @case('cancelled')
                                        <span class="status-badge status-cancelled">
                                            <i class="fas fa-ban"></i> Void
                                        </span>
                                        @break

                                    @default
                                        <span class="status-badge status-default">
                                            {{ ucfirst($note->status) }}
                                        </span>
                                @endswitch
                            </td>

                            {{-- Actions --}}
                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-manage dropdown-toggle"
                                            type="button"
                                            data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                        <i class="fas fa-ellipsis me-1"></i>Manage
                                    </button>

                                    <ul class="dropdown-menu dropdown-menu-end action-dropdown">

                                        <li>
                                            <a class="dropdown-item"
                                               href="{{ route('admin.supplier-debit-credit-notes.show', $note->id) }}">
                                                <i class="fas fa-eye text-primary"></i>
                                                View Note Details
                                            </a>
                                        </li>

                                        @if(($note->status ?? 'posted') === 'draft')
                                            <li>
                                                <form action="{{ route('admin.supplier-debit-credit-notes.approve', $note->id) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('Post Note #{{ $note->note_number }} to supplier ledger?');">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item text-success">
                                                        <i class="fas fa-check-circle"></i>
                                                        Post to Ledger
                                                    </button>
                                                </form>
                                            </li>
                                        @endif

                                        @if($note->supplier)
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item"
                                                   href="{{ route('admin.suppliers.ledger', $note->supplier_id) }}">
                                                    <i class="fas fa-book-open text-secondary"></i>
                                                    Supplier Ledger
                                                </a>
                                            </li>
                                        @endif

                                    </ul>
                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="9" class="empty-state-cell">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-file-circle-exclamation"></i>
                                    </div>
                                    <h6>No Adjustment Notes Found</h6>
                                    <p>
                                        No debit or credit notes have been recorded yet.
                                        Issue Debit Notes for vendor penalties/rebates, or Credit Notes for additional logistics/bill charges.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($notes->hasPages())
            <div class="register-footer">
                <div class="pagination-info">
                    Showing
                    <strong>{{ $notes->firstItem() ?? 0 }}</strong>
                    to
                    <strong>{{ $notes->lastItem() ?? 0 }}</strong>
                    of
                    <strong>{{ $notes->total() }}</strong>
                    adjustment notes
                </div>

                <div class="pagination-wrapper">
                    {{ $notes->links() }}
                </div>
            </div>
        @endif

    </div>
</div>

{{-- Page Styles --}}
<style>
    .supplier-notes-page {
        --sn-primary: #3157d5;
        --sn-primary-light: #eef2ff;
        --sn-text: #202938;
        --sn-muted: #7b8494;
        --sn-border: #e8ecf2;
        --sn-surface: #ffffff;
        color: var(--sn-text);
    }

    /* Header */
    .supplier-notes-page .page-header {
        padding: 2px 0 0;
    }

    .supplier-notes-page .page-icon {
        width: 43px;
        height: 43px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: var(--sn-primary-light);
        color: var(--sn-primary);
        border-radius: 12px;
        font-size: 19px;
    }

    .supplier-notes-page .page-title {
        font-size: 20px;
        font-weight: 750;
        letter-spacing: -0.45px;
        color: #202938;
    }

    .supplier-notes-page .page-subtitle {
        color: var(--sn-muted);
        font-size: 12px;
        margin-top: 3px;
    }

    .supplier-notes-page .custom-breadcrumb {
        margin-top: 12px;
        font-size: 11.5px;
    }

    .supplier-notes-page .custom-breadcrumb a {
        color: #64748b;
        text-decoration: none;
    }

    .supplier-notes-page .custom-breadcrumb a:hover {
        color: var(--sn-primary);
    }

    .supplier-notes-page .custom-breadcrumb .active {
        color: #334155;
        font-weight: 600;
    }

    .supplier-notes-page .btn-action {
        min-height: 39px;
        padding: 8px 15px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 650;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .supplier-notes-page .btn-primary {
        background: var(--sn-primary);
        border-color: var(--sn-primary);
    }

    .supplier-notes-page .btn-primary:hover {
        background: #2446b8;
        border-color: #2446b8;
    }

    /* Dropdowns */
    .supplier-notes-page .custom-dropdown,
    .supplier-notes-page .action-dropdown {
        padding: 6px;
        border: 1px solid var(--sn-border);
        border-radius: 10px;
        box-shadow: 0 8px 25px rgba(30, 41, 59, 0.10);
        min-width: 210px;
    }

    .supplier-notes-page .custom-dropdown .dropdown-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px;
        border-radius: 7px;
        white-space: normal;
    }

    .supplier-notes-page .custom-dropdown .dropdown-item:hover,
    .supplier-notes-page .action-dropdown .dropdown-item:hover {
        background: #f5f7fb;
    }

    .supplier-notes-page .custom-dropdown strong {
        display: block;
        color: #253047;
        font-size: 12px;
    }

    .supplier-notes-page .custom-dropdown small {
        display: block;
        margin-top: 2px;
        color: var(--sn-muted);
        font-size: 10.5px;
    }

    .supplier-notes-page .dropdown-icon {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 8px;
    }

    .supplier-notes-page .debit-icon {
        background: #fff6df;
        color: #b77908;
    }

    .supplier-notes-page .credit-icon {
        background: #fff0f0;
        color: #dc3545;
    }

    /* Statistics */
    .supplier-notes-page .stat-card {
        height: 100%;
        min-height: 116px;
        padding: 17px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        background: var(--sn-surface);
        border: 1px solid var(--sn-border);
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.035);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .supplier-notes-page .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 7px 20px rgba(15, 23, 42, 0.07);
    }

    .supplier-notes-page .stat-content {
        min-width: 0;
    }

    .supplier-notes-page .stat-label {
        color: #778196;
        font-size: 11.5px;
        font-weight: 650;
        margin-bottom: 8px;
    }

    .supplier-notes-page .stat-value {
        font-size: clamp(17px, 1.5vw, 22px);
        font-weight: 750;
        letter-spacing: -0.6px;
        line-height: 1.25;
        white-space: nowrap;
    }

    .supplier-notes-page .stat-description {
        margin-top: 5px;
        color: #98a1b0;
        font-size: 10.5px;
    }

    .supplier-notes-page .stat-icon {
        width: 43px;
        height: 43px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 11px;
        font-size: 17px;
    }

    .supplier-notes-page .icon-dark {
        background: #f0f2f5;
        color: #485467;
    }

    .supplier-notes-page .icon-success {
        background: #eaf8f0;
        color: #198754;
    }

    .supplier-notes-page .icon-danger {
        background: #fff0f0;
        color: #dc3545;
    }

    .supplier-notes-page .icon-primary {
        background: #eef2ff;
        color: var(--sn-primary);
    }

    /* Register Card */
    .supplier-notes-page .register-card {
        overflow: visible;
        background: #fff;
        border: 1px solid var(--sn-border);
        border-radius: 12px;
        box-shadow: 0 3px 12px rgba(15, 23, 42, 0.035);
    }

    .supplier-notes-page .register-header {
        padding: 19px 20px 18px;
        border-bottom: 1px solid var(--sn-border);
    }

    .supplier-notes-page .section-icon {
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--sn-primary-light);
        color: var(--sn-primary);
        border-radius: 9px;
        font-size: 14px;
    }

    .supplier-notes-page .section-title {
        color: #263247;
        font-size: 14px;
        font-weight: 750;
    }

    .supplier-notes-page .section-subtitle {
        color: #929bad;
        font-size: 11px;
        margin-top: 3px;
    }

    /* Filter Form */
    .supplier-notes-page .filter-label {
        display: block;
        color: #657084;
        font-size: 10.5px;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .supplier-notes-page .filter-form .form-control,
    .supplier-notes-page .filter-form .form-select {
        min-height: 37px;
        border-color: #e3e8ef;
        border-radius: 7px;
        background-color: #fff;
        color: #354155;
        font-size: 11.5px;
        box-shadow: none;
    }

    .supplier-notes-page .filter-form .form-control:focus,
    .supplier-notes-page .filter-form .form-select:focus {
        border-color: #91a8f3;
        box-shadow: 0 0 0 3px rgba(49, 87, 213, 0.08);
    }

    .supplier-notes-page .custom-input-group .input-group-text {
        min-height: 37px;
        padding: 0 11px;
        color: #929bad;
        background: #f8f9fc;
        border-color: #e3e8ef;
        border-right: 0;
        border-radius: 7px 0 0 7px;
        font-size: 12px;
    }

    .supplier-notes-page .custom-input-group .form-control {
        border-left: 0;
        border-radius: 0 7px 7px 0;
    }

    .supplier-notes-page .filter-btn,
    .supplier-notes-page .filter-clear-btn {
        min-height: 37px;
        border-radius: 7px;
        font-size: 11.5px;
        font-weight: 650;
    }

    .supplier-notes-page .filter-clear-btn {
        min-width: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* Table Toolbar */
    .supplier-notes-page .table-toolbar {
        padding: 13px 20px;
        background: #fbfcfe;
        border-bottom: 1px solid var(--sn-border);
    }

    .supplier-notes-page .table-toolbar-icon {
        color: #64748b;
        font-size: 13px;
    }

    .supplier-notes-page .table-toolbar .fw-semibold {
        font-size: 12px;
    }

    .supplier-notes-page .record-count {
        padding: 3px 8px;
        background: #eef2ff;
        color: #425fc3;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
    }

    .supplier-notes-page .table-info-text {
        color: #929bad;
        font-size: 10.5px;
    }

    /* Register Table */
    .supplier-notes-page .register-table {
        font-size: 12px;
        white-space: normal;
    }

    .supplier-notes-page .register-table thead th {
        padding: 13px 10px;
        background: #f7f8fb;
        color: #748095;
        border-bottom: 1px solid #e7ebf1;
        font-size: 10px;
        font-weight: 750;
        letter-spacing: .45px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .supplier-notes-page .register-table tbody td {
        padding: 14px 10px;
        border-bottom: 1px solid #edf0f4;
        color: #485467;
        vertical-align: middle;
    }

    .supplier-notes-page .register-table tbody tr {
        transition: background .15s ease;
    }

    .supplier-notes-page .register-table tbody tr:hover {
        background: #fafbfe;
    }

    .supplier-notes-page .register-table tbody tr:last-child td {
        border-bottom: 0;
    }

    /* Note Details */
    .supplier-notes-page .note-number {
        display: inline-block;
        font-size: 12px;
        font-weight: 750;
        text-decoration: none;
    }

    .supplier-notes-page .note-number:hover {
        text-decoration: underline;
    }

    .supplier-notes-page .note-debit {
        color: #a66b00;
    }

    .supplier-notes-page .note-credit {
        color: #d13c4b;
    }

    .supplier-notes-page .reference-number {
        display: block;
        margin-top: 4px;
        color: #98a1b0;
        font-family: monospace;
        font-size: 10px;
    }

    .supplier-notes-page .date-value {
        color: #667085;
        font-size: 11.5px;
        white-space: nowrap;
    }

    .supplier-notes-page .supplier-name {
        color: #263247;
        font-size: 12px;
        font-weight: 700;
    }

    .supplier-notes-page .supplier-company {
        display: block;
        margin-top: 4px;
        color: #98a1b0;
        font-size: 10.5px;
    }

    /* Type Badges */
    .supplier-notes-page .type-badge,
    .supplier-notes-page .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 5px 8px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .supplier-notes-page .type-debit {
        color: #9a6909;
        background: #fff7df;
        border: 1px solid #f8e8b7;
    }

    .supplier-notes-page .type-credit {
        color: #ca3948;
        background: #fff0f1;
        border: 1px solid #f8d9dc;
    }

    /* Bill & Reason */
    .supplier-notes-page .bill-link {
        color: #3157d5;
        font-size: 11px;
        font-weight: 650;
        text-decoration: none;
        white-space: nowrap;
    }

    .supplier-notes-page .bill-link:hover {
        text-decoration: underline;
    }

    .supplier-notes-page .direct-adjustment {
        color: #8791a1;
        font-size: 10.5px;
        white-space: nowrap;
    }

    .supplier-notes-page .reason-text {
        max-width: 220px;
        color: #465267;
        font-size: 11.5px;
        font-weight: 600;
    }

    .supplier-notes-page .reason-notes {
        display: block;
        max-width: 220px;
        margin-top: 4px;
        overflow: hidden;
        color: #9aa3b1;
        font-size: 10px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Amount */
    .supplier-notes-page .amount-value {
        font-size: 12px;
        font-weight: 750;
        white-space: nowrap;
    }

    .supplier-notes-page .amount-debit {
        color: #16834d;
    }

    .supplier-notes-page .amount-credit {
        color: #d13c4b;
    }

    /* Status */
    .supplier-notes-page .status-posted {
        color: #16834d;
        background: #eaf8f0;
        border: 1px solid #d5efdf;
    }

    .supplier-notes-page .status-draft {
        color: #9a6909;
        background: #fff7df;
        border: 1px solid #f8e8b7;
    }

    .supplier-notes-page .status-cancelled {
        color: #c03948;
        background: #fff0f1;
        border: 1px solid #f8d9dc;
    }

    .supplier-notes-page .status-default {
        color: #667085;
        background: #f2f4f7;
        border: 1px solid #e5e7eb;
    }

    /* Action Dropdown */
    .supplier-notes-page .btn-manage {
        padding: 6px 9px;
        color: #586477;
        background: #fff;
        border: 1px solid #e2e7ee;
        border-radius: 7px;
        font-size: 10.5px;
        font-weight: 650;
        white-space: nowrap;
    }

    .supplier-notes-page .btn-manage:hover,
    .supplier-notes-page .btn-manage:focus {
        color: var(--sn-primary);
        background: #f5f7ff;
        border-color: #cbd5f7;
    }

    .supplier-notes-page .action-dropdown {
        min-width: 190px;
    }

    .supplier-notes-page .action-dropdown .dropdown-item {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 9px 10px;
        color: #475467;
        border-radius: 6px;
        font-size: 11px;
    }

    .supplier-notes-page .action-dropdown .dropdown-item i {
        width: 15px;
        text-align: center;
    }

    .supplier-notes-page .action-dropdown .dropdown-divider {
        margin: 5px 0;
    }

    /* Empty State */
    .supplier-notes-page .empty-state-cell {
        padding: 55px 20px !important;
        text-align: center;
    }

    .supplier-notes-page .empty-state-icon {
        width: 65px;
        height: 65px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        color: #a3adbd;
        background: #f3f5f8;
        border-radius: 18px;
        font-size: 25px;
    }

    .supplier-notes-page .empty-state h6 {
        color: #344054;
        font-size: 14px;
        font-weight: 750;
    }

    .supplier-notes-page .empty-state p {
        max-width: 470px;
        margin: 7px auto 0;
        color: #98a1b0;
        font-size: 11px;
        line-height: 1.7;
    }

    /* Pagination */
    .supplier-notes-page .register-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        padding: 13px 20px;
        background: #fff;
        border-top: 1px solid var(--sn-border);
        border-radius: 0 0 12px 12px;
    }

    .supplier-notes-page .pagination-info {
        color: #8a94a5;
        font-size: 10.5px;
    }

    .supplier-notes-page .pagination-info strong {
        color: #465267;
    }

    .supplier-notes-page .pagination-wrapper nav {
        margin: 0;
    }

    .supplier-notes-page .pagination-wrapper .pagination {
        margin: 0;
        gap: 4px;
    }

    .supplier-notes-page .pagination-wrapper .page-link {
        min-width: 30px;
        padding: 6px 9px;
        color: #586477;
        border: 1px solid #e7ebf1;
        border-radius: 6px;
        font-size: 10.5px;
        text-align: center;
    }

    .supplier-notes-page .pagination-wrapper .page-item.active .page-link {
        color: #fff;
        background: var(--sn-primary);
        border-color: var(--sn-primary);
    }

    .supplier-notes-page .pagination-wrapper .page-item.disabled .page-link {
        color: #b7bfca;
        background: #f8f9fb;
    }

    /* Responsive */
    @media (max-width: 991.98px) {
        .supplier-notes-page .register-header {
            padding: 16px;
        }

        .supplier-notes-page .stat-card {
            padding: 14px;
        }

        .supplier-notes-page .table-toolbar {
            padding: 12px 15px;
        }
    }

    @media (max-width: 575.98px) {
        .supplier-notes-page {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        .supplier-notes-page .page-title {
            font-size: 16px;
        }

        .supplier-notes-page .page-icon {
            width: 38px;
            height: 38px;
            font-size: 16px;
        }

        .supplier-notes-page .header-actions {
            width: 100%;
        }

        .supplier-notes-page .header-actions > button,
        .supplier-notes-page .header-actions > .dropdown {
            flex: 1;
        }

        .supplier-notes-page .header-actions .btn-action {
            width: 100%;
            padding-left: 9px;
            padding-right: 9px;
            font-size: 11px;
        }

        .supplier-notes-page .stat-card {
            min-height: 105px;
            padding: 12px;
        }

        .supplier-notes-page .stat-label {
            font-size: 10px;
        }

        .supplier-notes-page .stat-value {
            font-size: 16px;
        }

        .supplier-notes-page .stat-description {
            font-size: 9px;
        }

        .supplier-notes-page .stat-icon {
            width: 34px;
            height: 34px;
            font-size: 14px;
        }

        .supplier-notes-page .register-header {
            padding: 14px;
        }

        .supplier-notes-page .table-info-text {
            display: none;
        }

        .supplier-notes-page .register-footer {
            padding: 12px;
        }

        .supplier-notes-page .pagination-info {
            width: 100%;
        }

        .supplier-notes-page .pagination-wrapper {
            width: 100%;
            overflow-x: auto;
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
        .filter-form,
        .table-toolbar,
        .dropdown,
        .register-footer,
        .btn {
            display: none !important;
        }

        .supplier-notes-page {
            padding: 0 !important;
        }

        .supplier-notes-page .page-header {
            margin-bottom: 15px !important;
        }

        .supplier-notes-page .register-card,
        .supplier-notes-page .stat-card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
        }

        .supplier-notes-page .register-table-wrapper {
            overflow: visible !important;
        }

        .supplier-notes-page .register-table {
            width: 100% !important;
            font-size: 9px !important;
        }

        .supplier-notes-page .register-table th,
        .supplier-notes-page .register-table td {
            padding: 7px 5px !important;
            border: 1px solid #dee2e6 !important;
        }

        .supplier-notes-page .register-table thead th {
            background: #f1f3f5 !important;
            color: #333 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .supplier-notes-page .type-badge,
        .supplier-notes-page .status-badge {
            border: 1px solid #ccc !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        @page {
            size: landscape;
            margin: 10mm;
        }
    }
</style>
@endsection