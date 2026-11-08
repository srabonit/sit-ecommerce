@extends('layouts.admin')

@section('title', 'Supplier Ledger Statement — ' . $supplier->name)

@section('admin_content')
<div class="container-fluid px-3 py-3 supplier-ledger-page">

    {{-- Page Header --}}
    <div class="supplier-ledger-header d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div class="supplier-ledger-heading">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <div class="supplier-ledger-title-icon">
                    <i class="fas fa-book-open"></i>
                </div>

                <h5 class="fw-bold text-dark mb-0">Supplier Ledger Statement</h5>

                <span class="supplier-code-badge font-monospace">
                    Code: SUP-{{ str_pad($supplier->id, 4, '0', STR_PAD_LEFT) }}
                </span>
            </div>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 supplier-ledger-breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.suppliers.index') }}">Suppliers</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        {{ $supplier->name }} (Ledger)
                    </li>
                </ol>
            </nav>
        </div>

        <div class="supplier-ledger-actions d-flex flex-wrap gap-2">
            <a href="{{ route('admin.suppliers.index') }}" class="btn btn-sm btn-outline-secondary supplier-ledger-action">
                <i class="fas fa-arrow-left me-1"></i> Back to Suppliers
            </a>

            <button type="button" class="btn btn-sm btn-outline-primary supplier-ledger-action" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print Statement
            </button>

            <a href="{{ route('admin.supplier-payments.create', ['supplier_id' => $supplier->id]) }}" class="btn btn-sm btn-success supplier-ledger-action">
                <i class="fas fa-money-check-dollar me-1"></i> Record Payment
            </a>

            <a href="{{ route('admin.purchases.create', ['supplier_id' => $supplier->id]) }}" class="btn btn-sm btn-primary supplier-ledger-action">
                <i class="fas fa-plus me-1"></i> New Purchase Bill
            </a>
        </div>
    </div>

    {{-- Financial Calculations --}}
    @php
        $opening = (float) ($openingBalance ?? $supplier->opening_balance ?? 0);
        $totalDr = (float) ($totalDebit ?? 0);
        $totalCr = (float) ($totalCredit ?? 0);
        $closing = (float) ($closingBalance ?? ($opening + $totalCr - $totalDr));
    @endphp

    {{-- Supplier Profile & Financial Summary --}}
    <div class="row g-3 mb-4">

        {{-- Supplier Profile --}}
        <div class="col-12 col-lg-4">
            <div class="card supplier-profile-card h-100">

                <div class="card-header supplier-section-header">
                    <div class="d-flex align-items-center gap-2">
                        <div class="supplier-section-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold text-dark">Supplier Profile</h6>
                            <small class="text-muted">Vendor information &amp; KYC</small>
                        </div>
                    </div>
                </div>

                <div class="card-body p-3">
                    <div class="supplier-profile-name">{{ $supplier->name }}</div>

                    <div class="supplier-profile-company">
                        {{ $supplier->company_name ?? 'Individual Supplier' }}
                    </div>

                    <div class="supplier-profile-divider"></div>

                    <div class="supplier-profile-detail">
                        <span class="supplier-detail-icon">
                            <i class="fas fa-phone"></i>
                        </span>
                        <span>{{ $supplier->phone ?? '—' }}</span>
                    </div>

                    <div class="supplier-profile-detail">
                        <span class="supplier-detail-icon">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <span class="supplier-detail-value">{{ $supplier->email ?? '—' }}</span>
                    </div>

                    @if($supplier->tax_number ?? $supplier->bin_number)
                        <div class="supplier-profile-detail">
                            <span class="supplier-detail-icon">
                                <i class="fas fa-id-card"></i>
                            </span>
                            <span>BIN / Tax: {{ $supplier->tax_number ?? $supplier->bin_number }}</span>
                        </div>
                    @endif

                    <div class="supplier-profile-detail align-items-start">
                        <span class="supplier-detail-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </span>
                        <span>{{ $supplier->address ?? 'Address not recorded' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Financial Summary --}}
        <div class="col-12 col-lg-8">
            <div class="row g-3 h-100">

                <div class="col-6 col-md-3">
                    <div class="card supplier-summary-card h-100">
                        <div class="card-body">
                            <div class="supplier-summary-icon summary-icon-neutral">
                                <i class="fas fa-wallet"></i>
                            </div>

                            <div class="supplier-summary-label">Opening Balance</div>

                            <h5 class="supplier-summary-value text-dark">
                                ৳{{ number_format($opening, 2) }}
                            </h5>

                            <small class="supplier-summary-caption">
                                {{ $opening >= 0 ? '(Payable Due)' : '(Advance / Cr)' }}
                            </small>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="card supplier-summary-card h-100">
                        <div class="card-body">
                            <div class="supplier-summary-icon summary-icon-danger">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>

                            <div class="supplier-summary-label">Total Invoiced (Cr)</div>

                            <h5 class="supplier-summary-value text-danger">
                                +৳{{ number_format($totalCr, 2) }}
                            </h5>

                            <small class="supplier-summary-caption">Purchases &amp; Cr Notes</small>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="card supplier-summary-card h-100">
                        <div class="card-body">
                            <div class="supplier-summary-icon summary-icon-success">
                                <i class="fas fa-money-bill-transfer"></i>
                            </div>

                            <div class="supplier-summary-label">Total Settled (Dr)</div>

                            <h5 class="supplier-summary-value text-success">
                                -৳{{ number_format($totalDr, 2) }}
                            </h5>

                            <small class="supplier-summary-caption">Payments &amp; Returns</small>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="card supplier-summary-card supplier-closing-card h-100">
                        <div class="card-body">
                            <div class="supplier-summary-icon summary-icon-primary">
                                <i class="fas fa-scale-balanced"></i>
                            </div>

                            <div class="supplier-summary-label">Closing Balance</div>

                            <h5 class="supplier-summary-value {{ $closing > 0 ? 'text-danger' : ($closing < 0 ? 'text-success' : 'text-muted') }}">
                                ৳{{ number_format(abs($closing), 2) }}
                            </h5>

                            <small class="supplier-summary-caption fw-semibold {{ $closing > 0 ? 'text-danger' : ($closing < 0 ? 'text-success' : 'text-muted') }}">
                                @if($closing > 0)
                                    Net Payable Due
                                @elseif($closing < 0)
                                    Advance Paid (Surplus)
                                @else
                                    Settled / Zero Due
                                @endif
                            </small>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Ledger Statement Card --}}
    <div class="card supplier-ledger-card mb-3">

        {{-- Filter Header --}}
        <div class="card-header supplier-filter-header">

            <div class="d-flex align-items-center gap-2 mb-3">
                <div class="supplier-section-icon">
                    <i class="fas fa-filter"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0">Statement Filters</h6>
                    <small class="text-muted">Select a date range and transaction category</small>
                </div>
            </div>

            <form action="{{ route('admin.suppliers.ledger', $supplier->id) }}" method="GET" class="row g-3 align-items-end">

                <div class="col-12 col-md-3">
                    <label class="form-label supplier-filter-label">From Date</label>
                    <input
                        type="date"
                        name="from_date"
                        class="form-control form-control-sm supplier-filter-control"
                        value="{{ request('from_date', date('Y-m-01')) }}"
                    >
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label supplier-filter-label">To Date</label>
                    <input
                        type="date"
                        name="to_date"
                        class="form-control form-control-sm supplier-filter-control"
                        value="{{ request('to_date', date('Y-m-d')) }}"
                    >
                </div>

                <div class="col-6 col-md-3">
                    <label class="form-label supplier-filter-label">Transaction Category</label>
                    <select name="type" class="form-select form-select-sm supplier-filter-control">
                        <option value="">All Transactions</option>
                        <option value="purchase" {{ request('type') === 'purchase' ? 'selected' : '' }}>Purchase Bills (Credit)</option>
                        <option value="payment" {{ request('type') === 'payment' ? 'selected' : '' }}>Disbursement Payments (Debit)</option>
                        <option value="return" {{ request('type') === 'return' ? 'selected' : '' }}>Purchase Returns / RTV (Debit)</option>
                        <option value="debit_note" {{ request('type') === 'debit_note' ? 'selected' : '' }}>Debit Notes (Debit)</option>
                        <option value="credit_note" {{ request('type') === 'credit_note' ? 'selected' : '' }}>Credit Notes (Credit)</option>
                        <option value="exchange" {{ request('type') === 'exchange' ? 'selected' : '' }}>Product Exchanges (Delta)</option>
                    </select>
                </div>

                <div class="col-6 col-md-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-dark supplier-filter-btn flex-grow-1">
                            <i class="fas fa-filter me-1"></i> Apply Filter
                        </button>

                        @if(request()->anyFilled(['from_date', 'to_date', 'type']))
                            <a
                                href="{{ route('admin.suppliers.ledger', $supplier->id) }}"
                                class="btn btn-sm btn-outline-danger supplier-reset-btn"
                                title="Reset Filters"
                            >
                                <i class="fas fa-rotate-left"></i>
                            </a>
                        @endif
                    </div>
                </div>

            </form>
        </div>

        {{-- Ledger Table Heading --}}
        <div class="supplier-ledger-table-heading d-flex align-items-center justify-content-between flex-wrap gap-2 px-3 py-3">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-list-alt text-primary"></i>
                <span class="fw-bold text-dark">Transaction History</span>
            </div>
            <small class="text-muted">Running account balance and transaction details</small>
        </div>

        {{-- Running Ledger Statement Table --}}
        <div class="table-responsive supplier-ledger-table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0 supplier-ledger-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 40px;">#</th>
                        <th style="width: 105px;">Date</th>
                        <th style="width: 150px;">Voucher / Ref #</th>
                        <th style="width: 140px;">Transaction Type</th>
                        <th>Particulars / Narration</th>
                        <th class="text-end" style="width: 125px;">Debit (Dr) (৳)</th>
                        <th class="text-end" style="width: 125px;">Credit (Cr) (৳)</th>
                        <th class="text-end pe-3" style="width: 140px;">Running Balance (৳)</th>
                    </tr>
                </thead>

                <tbody>
                    {{-- Opening Balance Row --}}
                    <tr class="supplier-opening-row">
                        <td class="text-center text-muted">&bull;</td>

                        <td class="text-muted">
                            {{ request('from_date') ? \Carbon\Carbon::parse(request('from_date'))->format('d M, Y') : 'Prior' }}
                        </td>

                        <td class="font-monospace text-muted">—</td>

                        <td>
                            <span class="supplier-transaction-badge transaction-opening">
                                <i class="fas fa-flag me-1"></i> Opening Balance
                            </span>
                        </td>

                        <td class="text-muted">
                            Balance brought forward from prior transactions
                        </td>

                        <td class="text-end text-muted">—</td>
                        <td class="text-end text-muted">—</td>

                        <td class="text-end pe-3 text-dark fw-bold">
                            ৳{{ number_format(abs($opening), 2) }}
                            <small class="text-muted font-monospace d-block">
                                ({{ $opening >= 0 ? 'Dr Due' : 'Cr Adv' }})
                            </small>
                        </td>
                    </tr>

                    @php
                        $running = $opening;
                    @endphp

                    @forelse($ledgerEntries ?? [] as $index => $entry)
                        @php
                            $dr = (float) ($entry->debit ?? 0);
                            $cr = (float) ($entry->credit ?? 0);
                            $running = $running + $cr - $dr;
                        @endphp

                        <tr>
                            <td class="text-center text-muted">
                                {{ $index + 1 }}
                            </td>

                            <td class="text-muted text-nowrap">
                                {{ \Carbon\Carbon::parse($entry->date)->format('d M, Y') }}
                            </td>

                            <td class="fw-bold">
                                @if(!empty($entry->route))
                                    <a href="{{ $entry->route }}" class="supplier-reference-link">
                                        {{ $entry->reference_number }}
                                    </a>
                                @else
                                    <span class="font-monospace text-dark">
                                        {{ $entry->reference_number }}
                                    </span>
                                @endif

                                @if(!empty($entry->secondary_ref))
                                    <small class="supplier-secondary-reference font-monospace">
                                        Ref: {{ $entry->secondary_ref }}
                                    </small>
                                @endif
                            </td>

                            <td>
                                @switch($entry->type)
                                    @case('purchase')
                                        <span class="supplier-transaction-badge transaction-purchase">
                                            <i class="fas fa-file-invoice me-1"></i> Purchase Bill
                                        </span>
                                        @break

                                    @case('payment')
                                        <span class="supplier-transaction-badge transaction-payment">
                                            <i class="fas fa-money-check-dollar me-1"></i> Payment
                                        </span>
                                        @break

                                    @case('return')
                                        <span class="supplier-transaction-badge transaction-return">
                                            <i class="fas fa-arrow-rotate-left me-1"></i> Purchase Return
                                        </span>
                                        @break

                                    @case('debit_note')
                                        <span class="supplier-transaction-badge transaction-debit">
                                            <i class="fas fa-minus-circle me-1"></i> Debit Note (Dr)
                                        </span>
                                        @break

                                    @case('credit_note')
                                        <span class="supplier-transaction-badge transaction-credit">
                                            <i class="fas fa-plus-circle me-1"></i> Credit Note (Cr)
                                        </span>
                                        @break

                                    @case('exchange')
                                        <span class="supplier-transaction-badge transaction-exchange">
                                            <i class="fas fa-right-left me-1"></i> Product Swap
                                        </span>
                                        @break

                                    @default
                                        <span class="supplier-transaction-badge transaction-default">
                                            {{ ucfirst(str_replace('_', ' ', $entry->type)) }}
                                        </span>
                                @endswitch
                            </td>

                            <td>
                                <div class="supplier-description">
                                    {{ $entry->description ?? 'Supplier Transaction' }}
                                </div>

                                @if(!empty($entry->payment_method))
                                    <small class="supplier-payment-channel">
                                        <i class="fas fa-credit-card me-1"></i>
                                        Channel: {{ ucfirst(str_replace('_', ' ', $entry->payment_method)) }}
                                    </small>
                                @endif
                            </td>

                            <td class="text-end fw-semibold text-success text-nowrap">
                                {{ $dr > 0 ? '৳' . number_format($dr, 2) : '—' }}
                            </td>

                            <td class="text-end fw-semibold text-danger text-nowrap">
                                {{ $cr > 0 ? '৳' . number_format($cr, 2) : '—' }}
                            </td>

                            <td class="text-end pe-3 fw-bold text-nowrap {{ $running > 0 ? 'text-danger' : ($running < 0 ? 'text-success' : 'text-muted') }}">
                                ৳{{ number_format(abs($running), 2) }}

                                <small class="supplier-running-indicator font-monospace">
                                    {{ $running > 0 ? 'Dr' : ($running < 0 ? 'Cr' : 'Nil') }}
                                </small>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="supplier-ledger-empty">
                                    <div class="supplier-ledger-empty-icon">
                                        <i class="fas fa-receipt"></i>
                                    </div>

                                    <div class="fw-bold text-dark mb-1">
                                        No ledger entries found for the selected date range.
                                    </div>

                                    <small class="text-muted">
                                        Adjust the date filter or record purchase bills and payments against this supplier.
                                    </small>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="5" class="text-end text-uppercase supplier-ledger-total-label">
                            Total Periodic Movements:
                        </td>

                        <td class="text-end text-success">
                            ৳{{ number_format($totalDr, 2) }}
                        </td>

                        <td class="text-end text-danger">
                            ৳{{ number_format($totalCr, 2) }}
                        </td>

                        <td class="text-end pe-3 {{ $closing > 0 ? 'text-danger' : ($closing < 0 ? 'text-success' : 'text-dark') }}">
                            ৳{{ number_format(abs($closing), 2) }}

                            <small class="supplier-running-indicator font-monospace">
                                {{ $closing > 0 ? 'Dr Due' : ($closing < 0 ? 'Cr Adv' : 'Nil') }}
                            </small>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Pagination --}}
        @if(isset($ledgerEntries) && method_exists($ledgerEntries, 'hasPages') && $ledgerEntries->hasPages())
            <div class="card-footer supplier-ledger-pagination">
                <small class="text-muted">
                    Showing {{ $ledgerEntries->firstItem() ?? 0 }} to {{ $ledgerEntries->lastItem() ?? 0 }} of {{ $ledgerEntries->total() }} ledger entries
                </small>

                <div class="supplier-pagination-links">
                    {{ $ledgerEntries->links() }}
                </div>
            </div>
        @endif

    </div>

    {{-- Statement Audit Disclaimer --}}
    <div class="supplier-audit-disclaimer d-flex align-items-center justify-content-between gap-3 flex-wrap">
        <div class="supplier-audit-text">
            <i class="fas fa-shield-halved text-primary me-1"></i>
            ERP Automated Supplier Statement. Generated on {{ date('d M, Y h:i A') }}. All debit and credit balances reconcile directly with general ledger accounts payable journals.
        </div>

        <div class="supplier-audit-label">
            <i class="fas fa-circle-check me-1"></i>
            Authorized ERP Report
        </div>
    </div>

</div>

{{-- Supplier Ledger Styles --}}
<style>
    .supplier-ledger-page {
        --ledger-border: #e8edf3;
        --ledger-muted: #7b8493;
        --ledger-primary: var(--bs-primary, #1572e8);
        color: #344054;
        min-width: 0;
    }

    /* Header */
    .supplier-ledger-page .supplier-ledger-header {
        min-width: 0;
    }

    .supplier-ledger-page .supplier-ledger-title-icon {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: var(--ledger-primary);
        background: rgba(21, 114, 232, .09);
        border-radius: 8px;
        font-size: 16px;
    }

    .supplier-ledger-page .supplier-ledger-heading h5 {
        font-size: 19px;
        letter-spacing: -.3px;
    }

    .supplier-ledger-page .supplier-code-badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 8px;
        color: #667085;
        background: #f8fafc;
        border: 1px solid #e5eaf0;
        border-radius: 5px;
        font-size: 10px;
        font-weight: 600;
    }

    .supplier-ledger-page .supplier-ledger-breadcrumb {
        font-size: 12px;
    }

    .supplier-ledger-page .supplier-ledger-breadcrumb a {
        color: #6c7a91;
        text-decoration: none;
    }

    .supplier-ledger-page .supplier-ledger-breadcrumb a:hover {
        color: var(--ledger-primary);
    }

    .supplier-ledger-page .supplier-ledger-action {
        min-height: 36px;
        padding: 7px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
    }

    /* Profile Card */
    .supplier-ledger-page .supplier-profile-card,
    .supplier-ledger-page .supplier-summary-card,
    .supplier-ledger-page .supplier-ledger-card {
        border: 1px solid var(--ledger-border);
        border-radius: 9px;
        background: #fff;
        box-shadow: 0 2px 9px rgba(20, 35, 60, .04);
    }

    .supplier-ledger-page .supplier-section-header {
        padding: 13px 16px;
        background: #fff;
        border-bottom: 1px solid var(--ledger-border);
        border-radius: 9px 9px 0 0;
    }

    .supplier-ledger-page .supplier-section-header h6 {
        font-size: 13px;
    }

    .supplier-ledger-page .supplier-section-header small {
        font-size: 10px;
    }

    .supplier-ledger-page .supplier-section-icon {
        width: 33px;
        height: 33px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: var(--ledger-primary);
        background: rgba(21, 114, 232, .09);
        border-radius: 7px;
        font-size: 13px;
    }

    .supplier-ledger-page .supplier-profile-name {
        color: #263448;
        font-size: 15px;
        font-weight: 700;
    }

    .supplier-ledger-page .supplier-profile-company {
        margin-top: 3px;
        color: #8993a4;
        font-size: 11px;
    }

    .supplier-ledger-page .supplier-profile-divider {
        height: 1px;
        margin: 14px 0;
        background: #edf0f4;
    }

    .supplier-ledger-page .supplier-profile-detail {
        display: flex;
        align-items: center;
        gap: 9px;
        margin-bottom: 12px;
        color: #667085;
        font-size: 11px;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    .supplier-ledger-page .supplier-profile-detail:last-child {
        margin-bottom: 0;
    }

    .supplier-ledger-page .supplier-detail-icon {
        width: 18px;
        flex: 0 0 18px;
        color: #98a2b3;
        text-align: center;
    }

    .supplier-ledger-page .supplier-detail-value {
        min-width: 0;
        overflow-wrap: anywhere;
    }

    /* Financial Summary */
    .supplier-ledger-page .supplier-summary-card {
        transition: box-shadow .2s ease, transform .2s ease;
    }

    .supplier-ledger-page .supplier-summary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(20, 35, 60, .07);
    }

    .supplier-ledger-page .supplier-summary-card .card-body {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 15px 10px;
        text-align: center;
    }

    .supplier-ledger-page .supplier-summary-icon {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
        border-radius: 8px;
        font-size: 14px;
    }

    .supplier-ledger-page .summary-icon-neutral {
        color: #667085;
        background: #f2f4f7;
    }

    .supplier-ledger-page .summary-icon-danger {
        color: #f25961;
        background: rgba(242, 89, 97, .10);
    }

    .supplier-ledger-page .summary-icon-success {
        color: #31ce77;
        background: rgba(49, 206, 119, .11);
    }

    .supplier-ledger-page .summary-icon-primary {
        color: #1572e8;
        background: rgba(21, 114, 232, .10);
    }

    .supplier-ledger-page .supplier-summary-label {
        min-height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #7b8493;
        font-size: 10px;
        font-weight: 700;
        line-height: 1.4;
        text-transform: uppercase;
    }

    .supplier-ledger-page .supplier-summary-value {
        margin: 7px 0 0;
        font-size: clamp(14px, 1.2vw, 19px);
        font-weight: 700;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    .supplier-ledger-page .supplier-summary-caption {
        display: block;
        margin-top: 6px;
        color: #98a2b3;
        font-size: 9px;
        line-height: 1.4;
    }

    .supplier-ledger-page .supplier-closing-card {
        background: #f8fafc;
    }

    /* Filter Section */
    .supplier-ledger-page .supplier-filter-header {
        padding: 17px;
        background: #fff;
        border-bottom: 1px solid var(--ledger-border);
        border-radius: 9px 9px 0 0;
    }

    .supplier-ledger-page .supplier-filter-header h6 {
        font-size: 13px;
    }

    .supplier-ledger-page .supplier-filter-header small {
        font-size: 10px;
    }

    .supplier-ledger-page .supplier-filter-label {
        display: block;
        margin-bottom: 6px;
        color: #596579;
        font-size: 11px;
        font-weight: 600;
    }

    .supplier-ledger-page .supplier-filter-control {
        min-height: 36px;
        border-color: #e2e8f0;
        border-radius: 6px;
        background-color: #f9fafc;
        color: #475467;
        font-size: 11px;
        box-shadow: none;
    }

    .supplier-ledger-page .supplier-filter-control:focus {
        border-color: #b8d2f5;
        background-color: #fff;
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .08);
    }

    .supplier-ledger-page .supplier-filter-btn,
    .supplier-ledger-page .supplier-reset-btn {
        min-height: 36px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
    }

    .supplier-ledger-page .supplier-reset-btn {
        min-width: 38px;
    }

    /* Table Heading */
    .supplier-ledger-page .supplier-ledger-table-heading {
        border-bottom: 1px solid var(--ledger-border);
        background: #fff;
    }

    .supplier-ledger-page .supplier-ledger-table-heading span {
        font-size: 13px;
    }

    .supplier-ledger-page .supplier-ledger-table-heading small {
        font-size: 10px;
    }

    /* Ledger Table */
    .supplier-ledger-page .supplier-ledger-table {
        min-width: 1120px;
        color: #475467;
        font-size: 11px;
    }

    .supplier-ledger-page .supplier-ledger-table thead th {
        padding: 13px 9px;
        color: #667085;
        background: #f8fafc;
        border-color: #e6ebf2;
        border-bottom-width: 1px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .35px;
        text-transform: uppercase;
        white-space: nowrap;
        vertical-align: middle;
    }

    .supplier-ledger-page .supplier-ledger-table tbody td {
        padding: 11px 9px;
        border-color: #edf0f4;
        vertical-align: middle;
    }

    .supplier-ledger-page .supplier-ledger-table tbody tr:hover {
        background-color: #fafcff;
    }

    .supplier-ledger-page .supplier-opening-row {
        background: #f8fafc;
        font-weight: 600;
    }

    .supplier-ledger-page .supplier-opening-row td {
        border-bottom-color: #e1e7ef;
    }

    .supplier-ledger-page .supplier-reference-link {
        color: var(--ledger-primary);
        text-decoration: none;
        white-space: nowrap;
    }

    .supplier-ledger-page .supplier-reference-link:hover {
        text-decoration: underline;
    }

    .supplier-ledger-page .supplier-secondary-reference {
        display: block;
        margin-top: 4px;
        color: #98a2b3;
        font-size: 9px;
    }

    .supplier-ledger-page .supplier-description {
        color: #344054;
        line-height: 1.5;
        min-width: 150px;
    }

    .supplier-ledger-page .supplier-payment-channel {
        display: block;
        margin-top: 4px;
        color: #98a2b3;
        font-size: 9px;
    }

    .supplier-ledger-page .supplier-transaction-badge {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        padding: 5px 7px;
        border: 1px solid transparent;
        border-radius: 5px;
        font-size: 9px;
        font-weight: 600;
        line-height: 1.3;
        white-space: nowrap;
    }

    .supplier-ledger-page .transaction-opening {
        color: #667085;
        background: #f2f4f7;
        border-color: #e4e7ec;
    }

    .supplier-ledger-page .transaction-purchase {
        color: #1572e8;
        background: #eaf2fd;
        border-color: #d5e5fb;
    }

    .supplier-ledger-page .transaction-payment {
        color: #16864b;
        background: #eaf8f0;
        border-color: #d2f0df;
    }

    .supplier-ledger-page .transaction-return {
        color: #d92d20;
        background: #fef0ef;
        border-color: #fbd5d2;
    }

    .supplier-ledger-page .transaction-debit {
        color: #b54708;
        background: #fff5e8;
        border-color: #fce5c7;
    }

    .supplier-ledger-page .transaction-credit {
        color: #c43248;
        background: #fff0f2;
        border-color: #fbd9df;
    }

    .supplier-ledger-page .transaction-exchange {
        color: #087e8b;
        background: #e8f8fa;
        border-color: #cceef1;
    }

    .supplier-ledger-page .transaction-default {
        color: #475467;
        background: #f2f4f7;
        border-color: #e4e7ec;
    }

    .supplier-ledger-page .supplier-running-indicator {
        display: block;
        margin-top: 3px;
        font-size: 9px;
        font-weight: 500;
    }

    .supplier-ledger-page .supplier-ledger-table tfoot td {
        padding: 13px 9px;
        background: #f8fafc;
        border-color: #e1e7ef;
        font-size: 11px;
        font-weight: 700;
        vertical-align: middle;
    }

    .supplier-ledger-page .supplier-ledger-total-label {
        color: #475467;
        font-size: 10px !important;
        letter-spacing: .3px;
    }

    /* Empty State */
    .supplier-ledger-page .supplier-ledger-empty {
        padding: 18px 10px;
    }

    .supplier-ledger-page .supplier-ledger-empty-icon {
        width: 58px;
        height: 58px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 13px;
        color: #98a2b3;
        background: #f2f4f7;
        border-radius: 50%;
        font-size: 23px;
    }

    .supplier-ledger-page .supplier-ledger-empty .fw-bold {
        font-size: 12px;
    }

    .supplier-ledger-page .supplier-ledger-empty small {
        font-size: 10px;
    }

    /* Pagination */
    .supplier-ledger-page .supplier-ledger-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
        padding: 12px 16px;
        background: #fff;
        border-top: 1px solid var(--ledger-border);
        border-radius: 0 0 9px 9px;
    }

    .supplier-ledger-page .supplier-ledger-pagination > small {
        font-size: 10px;
    }

    .supplier-ledger-page .supplier-pagination-links .pagination {
        margin-bottom: 0;
    }

    .supplier-ledger-page .supplier-pagination-links .page-link {
        min-width: 30px;
        padding: 5px 9px;
        color: #596579;
        border-color: #e5eaf0;
        font-size: 11px;
        text-align: center;
    }

    .supplier-ledger-page .supplier-pagination-links .page-item.active .page-link {
        color: #fff;
        background-color: var(--ledger-primary);
        border-color: var(--ledger-primary);
    }

    /* Audit Disclaimer */
    .supplier-ledger-page .supplier-audit-disclaimer {
        padding: 12px 14px;
        color: #7b8493;
        background: #fff;
        border: 1px solid var(--ledger-border);
        border-radius: 7px;
        font-size: 10px;
        line-height: 1.6;
    }

    .supplier-ledger-page .supplier-audit-text {
        flex: 1 1 500px;
    }

    .supplier-ledger-page .supplier-audit-label {
        color: #667085;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    /* Responsive */
    @media (max-width: 991.98px) {
        .supplier-ledger-page .supplier-ledger-actions {
            width: 100%;
        }

        .supplier-ledger-page .supplier-summary-card .card-body {
            padding: 13px 8px;
        }

        .supplier-ledger-page .supplier-summary-value {
            font-size: 16px;
        }
    }

    @media (max-width: 767.98px) {
        .supplier-ledger-page {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        .supplier-ledger-page .supplier-ledger-heading h5 {
            font-size: 16px;
        }

        .supplier-ledger-page .supplier-ledger-actions {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .supplier-ledger-page .supplier-ledger-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 6px;
            font-size: 10px;
            white-space: nowrap;
        }

        .supplier-ledger-page .supplier-profile-card .card-body {
            padding: 14px !important;
        }

        .supplier-ledger-page .supplier-summary-label {
            font-size: 9px;
        }

        .supplier-ledger-page .supplier-summary-value {
            font-size: 14px;
        }

        .supplier-ledger-page .supplier-summary-caption {
            font-size: 8px;
        }

        .supplier-ledger-page .supplier-filter-header {
            padding: 14px;
        }

        .supplier-ledger-page .supplier-ledger-table-heading {
            padding: 12px !important;
        }

        .supplier-ledger-page .supplier-ledger-table-heading small {
            width: 100%;
        }

        .supplier-ledger-page .supplier-ledger-pagination {
            align-items: flex-start;
            flex-direction: column;
        }

        .supplier-ledger-page .supplier-pagination-links {
            max-width: 100%;
            overflow-x: auto;
        }

        .supplier-ledger-page .supplier-audit-disclaimer {
            align-items: flex-start !important;
            flex-direction: column;
            gap: 7px !important;
        }
    }

    @media (max-width: 420px) {
        .supplier-ledger-page .supplier-ledger-heading h5 {
            font-size: 14px;
        }

        .supplier-ledger-page .supplier-code-badge {
            font-size: 9px;
        }

        .supplier-ledger-page .supplier-summary-icon {
            width: 29px;
            height: 29px;
            font-size: 12px;
        }

        .supplier-ledger-page .supplier-summary-card .card-body {
            padding: 11px 6px;
        }

        .supplier-ledger-page .supplier-summary-value {
            font-size: 12px;
        }

        .supplier-ledger-page .supplier-summary-label {
            font-size: 8px;
        }

        .supplier-ledger-page .supplier-ledger-actions {
            gap: 6px !important;
        }
    }

    /* Print */
    @media print {
        .supplier-ledger-page {
            padding: 0 !important;
            color: #000 !important;
        }

        .admin-dark-sidebar,
        .admin-navbar,
        .supplier-ledger-page .supplier-ledger-breadcrumb,
        .supplier-ledger-page .supplier-ledger-actions,
        .supplier-ledger-page .supplier-filter-header,
        .supplier-ledger-page .supplier-ledger-pagination {
            display: none !important;
        }

        .supplier-ledger-page .supplier-profile-card,
        .supplier-ledger-page .supplier-summary-card,
        .supplier-ledger-page .supplier-ledger-card,
        .supplier-ledger-page .supplier-audit-disclaimer {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
            break-inside: avoid;
        }

        .supplier-ledger-page .supplier-ledger-table-responsive {
            overflow: visible !important;
        }

        .supplier-ledger-page .supplier-ledger-table {
            min-width: 0 !important;
            width: 100% !important;
            font-size: 8px !important;
        }

        .supplier-ledger-page .supplier-ledger-table th,
        .supplier-ledger-page .supplier-ledger-table td {
            padding: 5px 4px !important;
            color: #000 !important;
            border: 1px solid #dee2e6 !important;
        }

        .supplier-ledger-page .supplier-ledger-table thead th,
        .supplier-ledger-page .supplier-ledger-table tfoot td {
            background: #f1f3f5 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .supplier-ledger-page .supplier-ledger-table tbody tr {
            break-inside: avoid;
        }

        .supplier-ledger-page .supplier-transaction-badge {
            color: #000 !important;
            background: #f5f5f5 !important;
            border-color: #ccc !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .supplier-ledger-page .supplier-audit-disclaimer {
            margin-top: 12px;
        }
    }
</style>
@endsection