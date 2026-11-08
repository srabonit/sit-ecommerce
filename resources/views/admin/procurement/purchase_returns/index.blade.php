@extends('layouts.admin')

@section('title', 'Purchase Returns (RTV) Register')

@section('admin_content')
@php
    $returnsList = $purchaseReturns ?? ($returns ?? collect([]));
    $totalReturnsCount = method_exists($returnsList, 'total') ? $returnsList->total() : $returnsList->count();
    $totalReturnValueAmount = (float) ($totalReturnValue ?? ($returnsList->sum('grand_total') ?? 0));
    $pendingReturnsCount = (int) ($pendingCount ?? 0);
    $completedReturnsCount = (int) ($completedCount ?? 0);
@endphp

<div class="container-fluid px-3 py-3 purchase-return-register-page">

    {{-- Breadcrumb & Action Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pr-header">
        <div>
            <h5 class="fw-bold text-dark mb-2">
                <i class="fas fa-arrow-rotate-left text-danger me-2"></i>Purchase Returns Register (Return to Vendor - RTV)
            </h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Procurement</li>
                    <li class="breadcrumb-item active" aria-current="page">Purchase Returns</li>
                </ol>
            </nav>
        </div>

        <div class="d-flex flex-wrap gap-2 pr-header-actions">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print Log
            </button>
            <a href="{{ route('admin.purchase-returns.create') }}" class="btn btn-sm btn-danger">
                <i class="fas fa-plus-circle me-1"></i> New Purchase Return (RTV)
            </a>
        </div>
    </div>

    {{-- Success Alert --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4 pr-alert">
            <div class="d-flex align-items-center gap-2 fw-semibold">
                <i class="fas fa-circle-check text-success"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Metric Stat Cards --}}
    <div class="row g-3 mb-4 pr-stat-row">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3 h-100 pr-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="min-w-0">
                        <div class="text-muted small fw-semibold">Total Returns</div>
                        <h4 class="fw-bold mb-0 mt-2 font-monospace">{{ number_format($totalReturnsCount) }}</h4>
                    </div>
                    <div class="pr-stat-icon bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-boxes-packing fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3 h-100 pr-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="min-w-0">
                        <div class="text-muted small fw-semibold">Total Claim Value</div>
                        <h4 class="fw-bold text-danger mb-0 mt-2 font-monospace">
                            ৳{{ number_format($totalReturnValueAmount, 2) }}
                        </h4>
                    </div>
                    <div class="pr-stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-hand-holding-dollar fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3 h-100 pr-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="min-w-0">
                        <div class="text-muted small fw-semibold">Pending Approval</div>
                        <h4 class="fw-bold text-warning mb-0 mt-2 font-monospace">
                            {{ number_format($pendingReturnsCount) }}
                        </h4>
                    </div>
                    <div class="pr-stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-clock fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3 h-100 pr-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="min-w-0">
                        <div class="text-muted small fw-semibold">Settled &amp; Closed</div>
                        <h4 class="fw-bold text-success mb-0 mt-2 font-monospace">
                            {{ number_format($completedReturnsCount) }}
                        </h4>
                    </div>
                    <div class="pr-stat-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-circle-check fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Toolbar & Search --}}
    <div class="card border-0 shadow-sm mb-3 pr-main-card">
        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="fas fa-sliders me-2 text-muted"></i>Search &amp; Filter Returns
                </h6>
            </div>

            <form action="{{ route('admin.purchase-returns.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-3">
                    <div class="input-group input-group-sm pr-search-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control bg-light border-start-0"
                               placeholder="Return #, Bill #, Reason, Vendor..."
                               value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-6 col-md-2">
                    <select name="supplier_id" class="form-select form-select-sm">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers ?? [] as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }} ({{ $supplier->company_name ?? 'Vendor' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <select name="settlement_type" class="form-select form-select-sm">
                        <option value="">All Settlement Modes</option>
                        <option value="credit_note" {{ request('settlement_type') === 'credit_note' ? 'selected' : '' }}>Credit Note (Due Deduction)</option>
                        <option value="cash_refund" {{ request('settlement_type') === 'cash_refund' ? 'selected' : '' }}>Cash Refund</option>
                        <option value="bank_refund" {{ request('settlement_type') === 'bank_refund' ? 'selected' : '' }}>Bank Refund</option>
                        <option value="replacement" {{ request('settlement_type') === 'replacement' ? 'selected' : '' }}>Goods Replacement</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Approval</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved / Dispatched</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Settled &amp; Closed</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div class="col-6 col-md-1">
                    <input type="date" name="return_date" class="form-control form-control-sm" value="{{ request('return_date') }}" title="Return Date">
                </div>

                <div class="col-12 col-md-2 d-flex gap-2 justify-content-end pr-filter-actions">
                    <button type="submit" class="btn btn-sm btn-dark flex-grow-1">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    @if(request()->anyFilled(['search', 'supplier_id', 'settlement_type', 'status', 'return_date']))
                        <a href="{{ route('admin.purchase-returns.index') }}" class="btn btn-sm btn-outline-danger" title="Clear Filters">
                            <i class="fas fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Purchase Returns Table --}}
        <div class="table-responsive pr-table-responsive">
            <table class="table table-hover align-middle mb-0 pr-returns-table">
                <thead class="table-light text-secondary text-uppercase">
                    <tr>
                        <th class="ps-3" style="width: 140px;">Return #</th>
                        <th style="width: 110px;">Date</th>
                        <th>Supplier / Vendor</th>
                        <th style="width: 140px;">Source Reference</th>
                        <th style="width: 130px;">Warehouse</th>
                        <th style="width: 130px;">Settlement Mode</th>
                        <th class="text-end" style="width: 130px;">Claim Value (৳)</th>
                        <th class="text-center" style="width: 130px;">Status</th>
                        <th class="text-end pe-3" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returnsList as $return)
                        @php
                            $claimTotal = (float) ($return->grand_total ?? ($return->total_amount ?? 0));
                        @endphp
                        <tr>
                            <td class="ps-3 fw-bold">
                                <a href="{{ route('admin.purchase-returns.show', $return->id) }}" class="text-danger text-decoration-none font-monospace">
                                    {{ $return->return_number }}
                                </a>
                            </td>
                            <td class="text-muted">
                                {{ !empty($return->return_date) ? \Carbon\Carbon::parse($return->return_date)->format('d M, Y') : '—' }}
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $return->supplier->name ?? ($return->supplier_name ?? '—') }}</div>
                                <small class="text-muted d-block">{{ $return->supplier->company_name ?? ($return->supplier_company ?? '') }}</small>
                            </td>
                            <td>
                                @if(!empty($return->purchase_id))
                                    <a href="{{ route('admin.purchases.show', $return->purchase_id) }}" class="fw-semibold text-primary text-decoration-none" style="font-size: 12px;">
                                        Bill #{{ $return->purchase->purchase_number ?? ($return->purchase_number ?? $return->purchase_id) }}
                                    </a>
                                @elseif(!empty($return->purchase_receipt_id))
                                    <a href="{{ route('admin.purchase-receipts.show', $return->purchase_receipt_id) }}" class="fw-semibold text-success text-decoration-none" style="font-size: 12px;">
                                        GRN #{{ $return->purchaseReceipt->receipt_number ?? ($return->grn_number ?? $return->purchase_receipt_id) }}
                                    </a>
                                @else
                                    <span class="text-muted small">Direct Return</span>
                                @endif
                            </td>
                            <td class="text-secondary small">
                                <i class="fas fa-warehouse me-1 text-muted"></i>{{ $return->warehouse->name ?? ($return->warehouse_name ?? 'Central Warehouse') }}
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border font-monospace pr-settlement-badge">
                                    {{ ucfirst(str_replace('_', ' ', $return->settlement_type ?? 'credit_note')) }}
                                </span>
                            </td>
                            <td class="text-end fw-bold text-danger font-monospace">
                                ৳{{ number_format($claimTotal, 2) }}
                            </td>
                            <td class="text-center">
                                @switch($return->status ?? 'approved')
                                    @case('pending')
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-0.5 pr-status-badge">
                                            Pending Approval
                                        </span>
                                        @break
                                    @case('approved')
                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-0.5 pr-status-badge">
                                            Approved (Dispatched)
                                        </span>
                                        @break
                                    @case('completed')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 pr-status-badge">
                                            <i class="fas fa-check-double me-1"></i> Settled &amp; Closed
                                        </span>
                                        @break
                                    @case('cancelled')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-0.5 pr-status-badge">
                                            Cancelled
                                        </span>
                                        @break
                                    @default
                                        <span class="badge bg-light text-dark border px-2 py-0.5 pr-status-badge">
                                            {{ ucfirst(str_replace('_', ' ', $return->status)) }}
                                        </span>
                                @endswitch
                            </td>
                            <td class="text-end pe-3">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border dropdown-toggle pr-manage-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Manage
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm pr-action-menu">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.purchase-returns.show', $return->id) }}">
                                                <i class="fas fa-eye text-primary me-2"></i> View Return Voucher
                                            </a>
                                        </li>

                                        @if(($return->status ?? '') === 'pending')
                                            <li>
                                                <form action="{{ route('admin.purchase-returns.approve', $return->id) }}" method="POST" onsubmit="return confirm('Approve Return Note #{{ $return->return_number }}? This will deduct stock and adjust vendor ledger.');">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item text-success">
                                                        <i class="fas fa-check-circle text-success me-2"></i> Approve &amp; Adjust Stock
                                                    </button>
                                                </form>
                                            </li>
                                        @endif

                                        @if(!empty($return->supplier_id) && Route::has('admin.suppliers.ledger'))
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-secondary" href="{{ route('admin.suppliers.ledger', $return->supplier_id) }}">
                                                    <i class="fas fa-book-open text-secondary me-2"></i> View Supplier Ledger
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fas fa-arrow-rotate-left fa-3x mb-3 text-secondary opacity-50"></i>
                                <div class="fw-semibold">No purchase returns recorded.</div>
                                <small>Initiate a Return to Vendor (RTV) for defective or excess stock items.</small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if(method_exists($returnsList, 'hasPages') && $returnsList->hasPages())
            <div class="card-footer bg-white border-top py-3 px-3 px-md-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <small class="text-muted">
                    Showing {{ $returnsList->firstItem() ?? 0 }} to {{ $returnsList->lastItem() ?? 0 }} of {{ $returnsList->total() }} return vouchers
                </small>
                <div class="pr-pagination">
                    {{ $returnsList->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<style>
    /* Purchase Return Register - scoped page styles */
    .purchase-return-register-page {
        --pr-border-color: #e8edf3;
        --pr-muted-color: #7d8793;
        --pr-radius: 8px;
        color: #344054;
    }

    .purchase-return-register-page .pr-header h5 {
        font-size: 1.05rem;
        line-height: 1.5;
        letter-spacing: -0.2px;
    }

    .purchase-return-register-page .breadcrumb {
        font-size: 12px;
    }

    .purchase-return-register-page .breadcrumb a {
        color: #6c757d;
        text-decoration: none;
    }

    .purchase-return-register-page .breadcrumb a:hover {
        color: var(--bs-primary);
    }

    .purchase-return-register-page .pr-header-actions .btn,
    .purchase-return-register-page .pr-filter-actions .btn {
        min-height: 34px;
        border-radius: 6px;
        font-weight: 600;
        padding-left: 12px;
        padding-right: 12px;
    }

    .purchase-return-register-page .pr-alert {
        border-radius: var(--pr-radius);
        padding: 13px 16px;
    }

    .purchase-return-register-page .pr-stat-card {
        border: 1px solid var(--pr-border-color) !important;
        border-radius: var(--pr-radius);
        transition: box-shadow 0.2s ease, transform 0.2s ease;
    }

    .purchase-return-register-page .pr-stat-card:hover {
        box-shadow: 0 5px 16px rgba(16, 24, 40, 0.07) !important;
        transform: translateY(-2px);
    }

    .purchase-return-register-page .pr-stat-card h4 {
        font-size: 1.35rem;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    .purchase-return-register-page .pr-stat-icon {
        width: 46px;
        height: 46px;
        min-width: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }

    .purchase-return-register-page .pr-main-card {
        border: 1px solid var(--pr-border-color) !important;
        border-radius: var(--pr-radius);
        overflow: visible;
    }

    .purchase-return-register-page .pr-main-card > .card-header {
        border-radius: var(--pr-radius) var(--pr-radius) 0 0;
    }

    .purchase-return-register-page .form-control,
    .purchase-return-register-page .form-select,
    .purchase-return-register-page .input-group-text {
        border-color: #dce2e9;
    }

    .purchase-return-register-page .form-control:focus,
    .purchase-return-register-page .form-select:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 0.15rem rgba(var(--bs-primary-rgb), 0.12);
    }

    .purchase-return-register-page .pr-search-group .input-group-text,
    .purchase-return-register-page .pr-search-group .form-control {
        min-height: 34px;
    }

    .purchase-return-register-page .pr-search-group .input-group-text {
        border-radius: 6px 0 0 6px;
    }

    .purchase-return-register-page .pr-search-group .form-control {
        border-radius: 0 6px 6px 0;
    }

    .purchase-return-register-page .pr-table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .purchase-return-register-page .pr-returns-table {
        min-width: 1120px;
        font-size: 13px;
        border-color: var(--pr-border-color);
    }

    .purchase-return-register-page .pr-returns-table thead th {
        padding: 13px 10px;
        background: #f8f9fb;
        color: #667085;
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: 0.45px;
        white-space: nowrap;
        border-bottom: 1px solid var(--pr-border-color);
        vertical-align: middle;
    }

    .purchase-return-register-page .pr-returns-table tbody td {
        padding: 13px 10px;
        border-bottom-color: #edf0f4;
        vertical-align: middle;
    }

    .purchase-return-register-page .pr-returns-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .purchase-return-register-page .pr-returns-table tbody tr:hover {
        background-color: #fafbfc;
    }

    .purchase-return-register-page .pr-returns-table a {
        transition: color 0.15s ease;
    }

    .purchase-return-register-page .pr-returns-table td {
        white-space: nowrap;
    }

    .purchase-return-register-page .pr-returns-table td:nth-child(3) {
        white-space: normal;
        min-width: 175px;
    }

    .purchase-return-register-page .pr-settlement-badge,
    .purchase-return-register-page .pr-status-badge {
        display: inline-block;
        padding: 5px 8px !important;
        border-radius: 5px;
        font-size: 10.5px !important;
        font-weight: 600;
        line-height: 1.4;
    }

    .purchase-return-register-page .pr-manage-btn {
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        padding: 5px 10px;
        white-space: nowrap;
    }

    .purchase-return-register-page .pr-action-menu {
        min-width: 205px;
        padding: 6px;
        border: 1px solid var(--pr-border-color);
        border-radius: 7px;
        font-size: 12px;
        z-index: 1050;
    }

    .purchase-return-register-page .pr-action-menu .dropdown-item {
        padding: 8px 10px;
        border-radius: 5px;
        white-space: normal;
    }

    .purchase-return-register-page .pr-action-menu .dropdown-item:hover {
        background: #f4f6f9;
    }

    .purchase-return-register-page .pr-action-menu form {
        margin: 0;
    }

    .purchase-return-register-page .pr-action-menu form button {
        width: 100%;
        text-align: left;
        background: transparent;
        border: 0;
    }

    .purchase-return-register-page .card-footer {
        border-radius: 0 0 var(--pr-radius) var(--pr-radius);
    }

    .purchase-return-register-page .pr-pagination nav {
        margin: 0;
    }

    .purchase-return-register-page .pr-pagination .pagination {
        margin-bottom: 0;
        flex-wrap: wrap;
        gap: 3px;
    }

    .purchase-return-register-page .pr-pagination .page-link {
        border-radius: 5px;
        font-size: 12px;
    }

    @media (max-width: 991.98px) {
        .purchase-return-register-page .pr-stat-card h4 {
            font-size: 1.2rem;
        }

        .purchase-return-register-page .pr-main-card > .card-header form > div {
            min-width: 0;
        }

        .purchase-return-register-page .pr-filter-actions {
            justify-content: flex-start !important;
        }
    }

    @media (max-width: 575.98px) {
        .purchase-return-register-page {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        .purchase-return-register-page .pr-header {
            align-items: flex-start !important;
        }

        .purchase-return-register-page .pr-header h5 {
            font-size: 0.98rem;
        }

        .purchase-return-register-page .pr-header-actions {
            width: 100%;
        }

        .purchase-return-register-page .pr-header-actions .btn {
            flex: 1 1 auto;
            justify-content: center;
        }

        .purchase-return-register-page .pr-stat-row {
            --bs-gutter-x: 10px;
            --bs-gutter-y: 10px;
        }

        .purchase-return-register-page .pr-stat-card {
            padding: 12px !important;
        }

        .purchase-return-register-page .pr-stat-card h4 {
            font-size: 1.05rem;
        }

        .purchase-return-register-page .pr-stat-icon {
            width: 38px;
            height: 38px;
            min-width: 38px;
        }

        .purchase-return-register-page .pr-stat-icon i {
            font-size: 15px;
        }

        .purchase-return-register-page .pr-main-card > .card-header {
            padding: 14px !important;
        }

        .purchase-return-register-page .pr-filter-actions {
            width: 100%;
        }

        .purchase-return-register-page .pr-pagination {
            width: 100%;
            overflow-x: auto;
        }
    }

    @media print {
        .purchase-return-register-page .pr-header-actions,
        .purchase-return-register-page .pr-main-card > .card-header,
        .purchase-return-register-page .pr-returns-table .dropdown,
        .purchase-return-register-page .pr-pagination {
            display: none !important;
        }

        .purchase-return-register-page .pr-main-card,
        .purchase-return-register-page .pr-stat-card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }

        .purchase-return-register-page .pr-table-responsive {
            overflow: visible !important;
        }

        .purchase-return-register-page .pr-returns-table {
            min-width: 0;
            width: 100%;
            font-size: 10px;
        }
    }
</style>
@endsection