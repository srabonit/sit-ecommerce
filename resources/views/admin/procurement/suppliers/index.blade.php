@extends('layouts.admin')

@section('title', 'Suppliers & Vendor Payables Directory')

@section('admin_content')
<div class="container-fluid px-3 py-3 supplier-directory">

    {{-- Page Header --}}
    <div class="supplier-page-header d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div class="supplier-heading">
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="supplier-heading-icon">
                    <i class="fas fa-truck-field"></i>
                </div>
                <h5 class="fw-bold text-dark mb-0">Suppliers Directory</h5>
            </div>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 supplier-breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">Procurement</li>
                    <li class="breadcrumb-item active" aria-current="page">Suppliers</li>
                </ol>
            </nav>
        </div>

        <div class="supplier-header-actions d-flex flex-wrap align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary supplier-action-btn" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print Directory
            </button>

            <a href="{{ route('admin.suppliers.create') }}" class="btn btn-sm btn-primary supplier-action-btn">
                <i class="fas fa-plus me-1"></i> Add New Supplier
            </a>
        </div>
    </div>

    {{-- Financial Metrics --}}
    @php
        $totalSuppliersCount = $suppliers->total() ?? 0;
        $totalDuePayablesVal = (float) ($totalPayablesDue ?? 0);
        $totalAdvancesVal    = (float) ($totalAdvanceDeposits ?? 0);
        $activeVendorsCount  = $activeCount ?? $suppliers->where('status', 'active')->count();
    @endphp

    <div class="row g-3 mb-4">

        <div class="col-6 col-xl-3">
            <div class="card supplier-metric-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="supplier-metric-content">
                            <div class="supplier-metric-label">Total Suppliers</div>
                            <h4 class="supplier-metric-value text-dark">
                                {{ number_format($totalSuppliersCount) }}
                            </h4>
                            <div class="supplier-metric-caption">Registered vendors</div>
                        </div>
                        <div class="supplier-metric-icon metric-icon-primary">
                            <i class="fas fa-address-book"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card supplier-metric-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="supplier-metric-content">
                            <div class="supplier-metric-label">Accounts Payable Due</div>
                            <h4 class="supplier-metric-value text-danger">
                                ৳{{ number_format($totalDuePayablesVal, 2) }}
                            </h4>
                            <div class="supplier-metric-caption">Outstanding payments</div>
                        </div>
                        <div class="supplier-metric-icon metric-icon-danger">
                            <i class="fas fa-scale-unbalanced-flip"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card supplier-metric-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="supplier-metric-content">
                            <div class="supplier-metric-label">Advance Deposits</div>
                            <h4 class="supplier-metric-value text-success">
                                ৳{{ number_format($totalAdvancesVal, 2) }}
                            </h4>
                            <div class="supplier-metric-caption">Advance vendor payments</div>
                        </div>
                        <div class="supplier-metric-icon metric-icon-success">
                            <i class="fas fa-hand-holding-dollar"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card supplier-metric-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="supplier-metric-content">
                            <div class="supplier-metric-label">Active Vendors</div>
                            <h4 class="supplier-metric-value text-dark">
                                {{ number_format($activeVendorsCount) }}
                            </h4>
                            <div class="supplier-metric-caption">Currently active</div>
                        </div>
                        <div class="supplier-metric-icon metric-icon-info">
                            <i class="fas fa-circle-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Main Directory Card --}}
    <div class="card supplier-directory-card">

        {{-- Search & Filters --}}
        <div class="card-header supplier-filter-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <div>
                    <h6 class="fw-bold text-dark mb-1">
                        <i class="fas fa-filter text-primary me-2"></i>Supplier Directory
                    </h6>
                    <small class="text-muted">Search and manage your supplier accounts</small>
                </div>
            </div>

            <form action="{{ route('admin.suppliers.index') }}" method="GET" class="supplier-filter-form">
                <div class="row g-2 align-items-end">

                    <div class="col-12 col-lg-4">
                        <label class="form-label supplier-filter-label">Search Suppliers</label>
                        <div class="input-group supplier-search-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input
                                type="text"
                                name="search"
                                class="form-control"
                                placeholder="Name, company, phone, BIN or email..."
                                value="{{ request('search') }}"
                            >
                        </div>
                    </div>

                    <div class="col-6 col-lg-3">
                        <label class="form-label supplier-filter-label">Ledger Balance</label>
                        <select name="balance_filter" class="form-select">
                            <option value="">All Ledger Balances</option>
                            <option value="due" {{ request('balance_filter') === 'due' ? 'selected' : '' }}>
                                Outstanding Due (Payable &gt; 0)
                            </option>
                            <option value="advance" {{ request('balance_filter') === 'advance' ? 'selected' : '' }}>
                                Advance Paid (Credit &lt; 0)
                            </option>
                            <option value="zero" {{ request('balance_filter') === 'zero' ? 'selected' : '' }}>
                                Settled / Zero Balance
                            </option>
                        </select>
                    </div>

                    <div class="col-6 col-lg-2">
                        <label class="form-label supplier-filter-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>
                                Active
                            </option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>
                                Inactive / Blocked
                            </option>
                        </select>
                    </div>

                    <div class="col-12 col-lg-3">
                        <label class="form-label supplier-filter-label d-none d-lg-block">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-dark supplier-filter-btn flex-grow-1">
                                <i class="fas fa-filter me-1"></i> Apply Filters
                            </button>

                            @if(request()->anyFilled(['search', 'balance_filter', 'status']))
                                <a
                                    href="{{ route('admin.suppliers.index') }}"
                                    class="btn btn-outline-danger supplier-clear-btn"
                                    title="Clear Filters"
                                >
                                    <i class="fas fa-rotate-left"></i>
                                </a>
                            @endif
                        </div>
                    </div>

                </div>
            </form>
        </div>

        {{-- Table Section --}}
        <div class="supplier-table-section">

            <div class="supplier-table-heading d-flex align-items-center justify-content-between flex-wrap gap-2 px-3 py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-list-ul text-primary"></i>
                    <span class="fw-semibold text-dark">Supplier Records</span>
                </div>
                <small class="text-muted">Manage supplier profiles, balances and transactions</small>
            </div>

            <div class="table-responsive supplier-table-responsive">
                <table class="table table-hover align-middle mb-0 supplier-table">
                    <thead>
                        <tr>
                            <th class="ps-3" style="width: 120px;">Code</th>
                            <th>Vendor &amp; Company</th>
                            <th style="width: 180px;">Contact Details</th>
                            <th style="width: 150px;">Tax / Trade License</th>
                            <th style="width: 130px;">Payment Terms</th>
                            <th class="text-end" style="width: 150px;">Current Balance (৳)</th>
                            <th class="text-center" style="width: 110px;">Status</th>
                            <th class="text-end pe-3" style="width: 120px;">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($suppliers as $supplier)
                            @php
                                $balance = (float) ($supplier->current_balance ?? 0);
                            @endphp

                            <tr>
                                <td class="ps-3">
                                    <a
                                        href="{{ route('admin.suppliers.ledger', $supplier->id) }}"
                                        class="supplier-code-link font-monospace"
                                    >
                                        SUP-{{ str_pad($supplier->id, 4, '0', STR_PAD_LEFT) }}
                                    </a>
                                </td>

                                <td>
                                    <div class="supplier-name">{{ $supplier->name }}</div>
                                    <small class="supplier-company">
                                        {{ $supplier->company_name ?? 'Individual Supplier' }}
                                    </small>
                                </td>

                                <td>
                                    <div class="supplier-contact">
                                        <i class="fas fa-phone fa-xs"></i>
                                        <span>{{ $supplier->phone ?? '—' }}</span>
                                    </div>

                                    @if($supplier->email)
                                        <small class="supplier-email" title="{{ $supplier->email }}">
                                            <i class="fas fa-envelope fa-xs"></i>
                                            <span>{{ $supplier->email }}</span>
                                        </small>
                                    @endif
                                </td>

                                <td>
                                    @if($supplier->tax_number ?? $supplier->bin_number)
                                        <span class="supplier-tax-badge font-monospace">
                                            BIN: {{ $supplier->tax_number ?? $supplier->bin_number }}
                                        </span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>

                                <td class="supplier-payment-terms">
                                    {{ ucfirst(str_replace('_', ' ', $supplier->payment_terms ?? 'Net 30 Days')) }}
                                </td>

                                <td class="text-end">
                                    <div class="supplier-balance">
                                        @if($balance > 0)
                                            <span class="text-danger">৳{{ number_format($balance, 2) }}</span>
                                            <small class="text-danger">(Payable Due)</small>
                                        @elseif($balance < 0)
                                            <span class="text-success">৳{{ number_format(abs($balance), 2) }}</span>
                                            <small class="text-success">(Advance Deposit)</small>
                                        @else
                                            <span class="text-muted">৳0.00</span>
                                            <small class="text-muted">(Settled)</small>
                                        @endif
                                    </div>
                                </td>

                                <td class="text-center">
                                    @if(($supplier->status ?? 'active') === 'active')
                                        <span class="supplier-status-badge status-active">
                                            <i class="fas fa-circle-check"></i> Active
                                        </span>
                                    @else
                                        <span class="supplier-status-badge status-inactive">
                                            <i class="fas fa-circle-minus"></i> Inactive
                                        </span>
                                    @endif
                                </td>

                                <td class="text-end pe-3">
                                    <div class="dropdown supplier-actions-dropdown">
                                        <button
                                            class="btn btn-sm btn-light border supplier-manage-btn dropdown-toggle"
                                            type="button"
                                            data-bs-toggle="dropdown"
                                            aria-expanded="false"
                                        >
                                            Manage
                                        </button>

                                        <ul class="dropdown-menu dropdown-menu-end supplier-dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.suppliers.ledger', $supplier->id) }}">
                                                    <i class="fas fa-book-open text-primary me-2"></i>
                                                    View Ledger Statement
                                                </a>
                                            </li>

                                            <li>
                                                <a class="dropdown-item text-success" href="{{ route('admin.supplier-payments.create', ['supplier_id' => $supplier->id]) }}">
                                                    <i class="fas fa-money-check-dollar text-success me-2"></i>
                                                    Disburse Payment
                                                </a>
                                            </li>

                                            <li>
                                                <a class="dropdown-item text-dark" href="{{ route('admin.purchases.create', ['supplier_id' => $supplier->id]) }}">
                                                    <i class="fas fa-file-invoice text-dark me-2"></i>
                                                    New Purchase Bill
                                                </a>
                                            </li>

                                            <li><hr class="dropdown-divider"></li>

                                            <li>
                                                <a class="dropdown-item text-secondary" href="{{ route('admin.suppliers.edit', $supplier->id) }}">
                                                    <i class="fas fa-pen-to-square text-secondary me-2"></i>
                                                    Edit Profile
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="supplier-empty-state">
                                        <div class="supplier-empty-icon">
                                            <i class="fas fa-truck-field"></i>
                                        </div>
                                        <div class="fw-semibold text-dark mb-1">
                                            No suppliers found in the registry.
                                        </div>
                                        <small class="text-muted">
                                            Add vendor profiles to manage purchase orders, bills, and ledger statements.
                                        </small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if($suppliers->hasPages())
            <div class="card-footer supplier-pagination-footer">
                <small class="text-muted">
                    Showing {{ $suppliers->firstItem() ?? 0 }} to {{ $suppliers->lastItem() ?? 0 }} of {{ $suppliers->total() }} suppliers
                </small>

                <div class="supplier-pagination-links">
                    {{ $suppliers->links() }}
                </div>
            </div>
        @endif

    </div>
</div>

{{-- Supplier Directory Styles --}}
<style>
    .supplier-directory {
        --supplier-border: #e8edf3;
        --supplier-muted: #7b8493;
        --supplier-heading: #253247;
        --supplier-primary: var(--bs-primary, #1572e8);
        color: #344054;
        min-width: 0;
    }

    .supplier-directory .supplier-page-header {
        min-width: 0;
    }

    .supplier-directory .supplier-heading-icon {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: var(--supplier-primary);
        background: rgba(21, 114, 232, .09);
        border-radius: 8px;
        font-size: 16px;
    }

    .supplier-directory .supplier-heading h5 {
        font-size: 19px;
        letter-spacing: -.3px;
    }

    .supplier-directory .supplier-breadcrumb {
        font-size: 12px;
    }

    .supplier-directory .supplier-breadcrumb a {
        color: #6c7a91;
        text-decoration: none;
    }

    .supplier-directory .supplier-breadcrumb a:hover {
        color: var(--supplier-primary);
    }

    .supplier-directory .supplier-action-btn {
        min-height: 36px;
        padding: 7px 13px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 12px;
    }

    /* Metrics */
    .supplier-directory .supplier-metric-card {
        border: 1px solid var(--supplier-border);
        border-radius: 9px;
        background: #fff;
        box-shadow: 0 2px 8px rgba(20, 35, 60, .035);
        transition: box-shadow .2s ease, transform .2s ease;
    }

    .supplier-directory .supplier-metric-card:hover {
        box-shadow: 0 5px 16px rgba(20, 35, 60, .07);
        transform: translateY(-2px);
    }

    .supplier-directory .supplier-metric-card .card-body {
        padding: 18px;
    }

    .supplier-directory .supplier-metric-content {
        min-width: 0;
    }

    .supplier-directory .supplier-metric-label {
        color: #697586;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.4;
        margin-bottom: 8px;
    }

    .supplier-directory .supplier-metric-value {
        font-size: clamp(17px, 1.5vw, 23px);
        font-weight: 700;
        line-height: 1.25;
        margin: 0;
        overflow-wrap: anywhere;
    }

    .supplier-directory .supplier-metric-caption {
        color: #98a2b3;
        font-size: 11px;
        margin-top: 5px;
    }

    .supplier-directory .supplier-metric-icon {
        width: 43px;
        height: 43px;
        min-width: 43px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        font-size: 18px;
    }

    .supplier-directory .metric-icon-primary {
        color: #1572e8;
        background: rgba(21, 114, 232, .10);
    }

    .supplier-directory .metric-icon-danger {
        color: #f25961;
        background: rgba(242, 89, 97, .10);
    }

    .supplier-directory .metric-icon-success {
        color: #31ce77;
        background: rgba(49, 206, 119, .11);
    }

    .supplier-directory .metric-icon-info {
        color: #48abf7;
        background: rgba(72, 171, 247, .11);
    }

    /* Main Card & Filters */
    .supplier-directory .supplier-directory-card {
        border: 1px solid var(--supplier-border);
        border-radius: 9px;
        overflow: visible;
        background: #fff;
        box-shadow: 0 2px 10px rgba(20, 35, 60, .04);
    }

    .supplier-directory .supplier-filter-header {
        padding: 18px;
        background: #fff;
        border-bottom: 1px solid var(--supplier-border);
        border-radius: 9px 9px 0 0;
    }

    .supplier-directory .supplier-filter-header h6 {
        font-size: 14px;
    }

    .supplier-directory .supplier-filter-label {
        display: block;
        margin-bottom: 6px;
        color: #596579;
        font-size: 11px;
        font-weight: 600;
    }

    .supplier-directory .supplier-search-group .input-group-text,
    .supplier-directory .supplier-search-group .form-control,
    .supplier-directory .supplier-filter-form .form-select {
        min-height: 37px;
        border-color: #e2e8f0;
        background-color: #f9fafc;
        font-size: 12px;
        box-shadow: none;
    }

    .supplier-directory .supplier-search-group .input-group-text {
        color: #8993a4;
        border-right: 0;
        border-radius: 6px 0 0 6px;
    }

    .supplier-directory .supplier-search-group .form-control {
        border-left: 0;
        border-radius: 0 6px 6px 0;
    }

    .supplier-directory .supplier-search-group .form-control:focus {
        border-color: #b8d2f5;
        background: #fff;
    }

    .supplier-directory .supplier-filter-form .form-select {
        border-radius: 6px;
        color: #475467;
    }

    .supplier-directory .supplier-filter-form .form-select:focus {
        border-color: #b8d2f5;
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .08);
    }

    .supplier-directory .supplier-filter-btn,
    .supplier-directory .supplier-clear-btn {
        min-height: 37px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
    }

    .supplier-directory .supplier-clear-btn {
        min-width: 39px;
    }

    /* Table */
    .supplier-directory .supplier-table-heading {
        border-bottom: 1px solid var(--supplier-border);
        background: #fff;
    }

    .supplier-directory .supplier-table-heading span {
        font-size: 13px;
    }

    .supplier-directory .supplier-table-heading small {
        font-size: 11px;
    }

    .supplier-directory .supplier-table {
        min-width: 1120px;
        font-size: 12px;
        color: #475467;
    }

    .supplier-directory .supplier-table thead th {
        padding: 13px 10px;
        color: #667085;
        background: #f8fafc;
        border-bottom: 1px solid #e6ebf2;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .45px;
        text-transform: uppercase;
        white-space: nowrap;
        vertical-align: middle;
    }

    .supplier-directory .supplier-table tbody td {
        padding: 13px 10px;
        border-bottom: 1px solid #edf0f4;
        vertical-align: middle;
    }

    .supplier-directory .supplier-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .supplier-directory .supplier-table tbody tr:hover {
        background-color: #fafcff;
    }

    .supplier-directory .supplier-code-link {
        color: var(--supplier-primary);
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
    }

    .supplier-directory .supplier-code-link:hover {
        text-decoration: underline;
    }

    .supplier-directory .supplier-name {
        color: #263448;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.45;
    }

    .supplier-directory .supplier-company {
        display: block;
        margin-top: 3px;
        color: #8a94a6;
        font-size: 11px;
    }

    .supplier-directory .supplier-contact {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #475467;
        font-size: 11px;
        white-space: nowrap;
    }

    .supplier-directory .supplier-contact i,
    .supplier-directory .supplier-email i {
        color: #98a2b3;
    }

    .supplier-directory .supplier-email {
        display: flex;
        align-items: center;
        gap: 6px;
        max-width: 160px;
        margin-top: 5px;
        color: #8a94a6;
        font-size: 10px;
    }

    .supplier-directory .supplier-email span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .supplier-directory .supplier-tax-badge {
        display: inline-block;
        padding: 4px 6px;
        color: #667085;
        background: #f8fafc;
        border: 1px solid #e5eaf0;
        border-radius: 4px;
        font-size: 10px;
        white-space: nowrap;
    }

    .supplier-directory .supplier-payment-terms {
        color: #667085;
        font-size: 11px;
        white-space: nowrap;
    }

    .supplier-directory .supplier-balance {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 3px;
        white-space: nowrap;
    }

    .supplier-directory .supplier-balance > span {
        font-size: 12px;
        font-weight: 700;
    }

    .supplier-directory .supplier-balance small {
        font-size: 10px;
        font-weight: 400;
    }

    .supplier-directory .supplier-status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 5px 8px;
        border-radius: 5px;
        font-size: 10px;
        font-weight: 600;
        white-space: nowrap;
    }

    .supplier-directory .status-active {
        color: #16864b;
        background: #eaf8f0;
        border: 1px solid #d2f0df;
    }

    .supplier-directory .status-inactive {
        color: #667085;
        background: #f2f4f7;
        border: 1px solid #e4e7ec;
    }

    /* Actions Dropdown */
    .supplier-directory .supplier-manage-btn {
        padding: 5px 10px;
        border-radius: 5px;
        color: #475467;
        background: #fff;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }

    .supplier-directory .supplier-manage-btn:hover,
    .supplier-directory .supplier-manage-btn:focus {
        color: var(--supplier-primary);
        background: #f5f9ff;
        border-color: #cfe0f7;
    }

    .supplier-directory .supplier-dropdown-menu {
        min-width: 210px;
        padding: 5px;
        border: 1px solid #e8edf3;
        border-radius: 7px;
        box-shadow: 0 8px 24px rgba(20, 35, 60, .12);
        font-size: 11px;
        z-index: 1055;
    }

    .supplier-directory .supplier-dropdown-menu .dropdown-item {
        padding: 8px 9px;
        border-radius: 4px;
        white-space: nowrap;
    }

    .supplier-directory .supplier-dropdown-menu .dropdown-item:hover {
        background: #f4f7fb;
    }

    .supplier-directory .supplier-dropdown-menu .dropdown-divider {
        margin: 5px 0;
    }

    /* Empty State */
    .supplier-directory .supplier-empty-state {
        padding: 18px 10px;
    }

    .supplier-directory .supplier-empty-icon {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 14px;
        color: #98a2b3;
        background: #f2f4f7;
        border-radius: 50%;
        font-size: 24px;
    }

    .supplier-directory .supplier-empty-state .fw-semibold {
        font-size: 13px;
    }

    .supplier-directory .supplier-empty-state small {
        font-size: 11px;
    }

    /* Pagination */
    .supplier-directory .supplier-pagination-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
        padding: 12px 16px;
        background: #fff;
        border-top: 1px solid var(--supplier-border);
        border-radius: 0 0 9px 9px;
    }

    .supplier-directory .supplier-pagination-footer > small {
        font-size: 11px;
    }

    .supplier-directory .supplier-pagination-links .pagination {
        margin-bottom: 0;
    }

    .supplier-directory .supplier-pagination-links .page-link {
        min-width: 30px;
        padding: 5px 9px;
        color: #596579;
        border-color: #e5eaf0;
        font-size: 11px;
        text-align: center;
    }

    .supplier-directory .supplier-pagination-links .page-item.active .page-link {
        color: #fff;
        background-color: var(--supplier-primary);
        border-color: var(--supplier-primary);
    }

    /* Responsive */
    @media (max-width: 767.98px) {
        .supplier-directory {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        .supplier-directory .supplier-page-header {
            align-items: flex-start !important;
        }

        .supplier-directory .supplier-heading h5 {
            font-size: 16px;
        }

        .supplier-directory .supplier-header-actions {
            width: 100%;
        }

        .supplier-directory .supplier-header-actions .supplier-action-btn {
            flex: 1 1 auto;
            justify-content: center;
            text-align: center;
        }

        .supplier-directory .supplier-metric-card .card-body {
            padding: 12px;
        }

        .supplier-directory .supplier-metric-label {
            font-size: 10px;
            min-height: 28px;
        }

        .supplier-directory .supplier-metric-value {
            font-size: 16px;
        }

        .supplier-directory .supplier-metric-caption {
            font-size: 9px;
        }

        .supplier-directory .supplier-metric-icon {
            width: 34px;
            height: 34px;
            min-width: 34px;
            font-size: 14px;
        }

        .supplier-directory .supplier-filter-header {
            padding: 14px;
        }

        .supplier-directory .supplier-table-heading {
            padding: 12px !important;
        }

        .supplier-directory .supplier-table-heading small {
            width: 100%;
        }

        .supplier-directory .supplier-pagination-footer {
            align-items: flex-start;
            flex-direction: column;
        }

        .supplier-directory .supplier-pagination-links {
            max-width: 100%;
            overflow-x: auto;
        }
    }

    @media (max-width: 420px) {
        .supplier-directory .supplier-heading h5 {
            font-size: 14px;
        }

        .supplier-directory .supplier-header-actions .supplier-action-btn {
            font-size: 10px;
            padding: 7px 8px;
        }

        .supplier-directory .supplier-metric-card .card-body {
            padding: 10px;
        }

        .supplier-directory .supplier-metric-icon {
            width: 29px;
            height: 29px;
            min-width: 29px;
            font-size: 12px;
        }

        .supplier-directory .supplier-metric-value {
            font-size: 14px;
        }
    }

    /* Print */
    @media print {
        .supplier-directory {
            padding: 0 !important;
            color: #000 !important;
        }

        .admin-dark-sidebar,
        .admin-navbar,
        .supplier-directory .supplier-breadcrumb,
        .supplier-directory .supplier-header-actions,
        .supplier-directory .supplier-filter-header,
        .supplier-directory .supplier-actions-dropdown,
        .supplier-directory .supplier-pagination-footer {
            display: none !important;
        }

        .supplier-directory .supplier-directory-card,
        .supplier-directory .supplier-metric-card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
            break-inside: avoid;
        }

        .supplier-directory .supplier-table-responsive {
            overflow: visible !important;
        }

        .supplier-directory .supplier-table {
            min-width: 0 !important;
            width: 100% !important;
            font-size: 9px !important;
        }

        .supplier-directory .supplier-table th,
        .supplier-directory .supplier-table td {
            padding: 6px 5px !important;
            color: #000 !important;
            border: 1px solid #dee2e6 !important;
        }

        .supplier-directory .supplier-table thead th {
            background: #f1f3f5 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .supplier-directory .supplier-metric-value {
            font-size: 15px !important;
        }

        .supplier-directory .supplier-table tbody tr {
            break-inside: avoid;
        }
    }
</style>
@endsection