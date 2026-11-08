@extends('layouts.admin')

@section('title', 'Purchase Orders')

@section('admin_content')
<div class="container-fluid px-3 py-4 purchase-orders-page">

    {{-- Page Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="fas fa-file-signature text-primary me-2"></i>Purchase Orders
            </h4>
            <p class="text-muted small mb-0">Manage, track and review your purchase orders.</p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm px-3" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print Orders Log
            </button>

            <a href="{{ route('admin.purchase-orders.create') }}" class="btn btn-primary btn-sm px-3">
                <i class="fas fa-plus me-1"></i> Issue New PO
            </a>
        </div>
    </div>

    {{-- Success & Error Notice Banners --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center gap-2 fw-semibold">
                <i class="fas fa-circle-check fa-lg"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center gap-2 fw-semibold">
                <i class="fas fa-circle-exclamation fa-lg"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Order Metric Cards --}}
    @php
        $totalOrdersCount = $purchaseOrders->total() ?? 0;
        $totalCommitmentVal = (float) ($totalOrderValue ?? ($purchaseOrders->sum('grand_total') ?? 0));
    @endphp

    <div class="row g-3 mb-4">

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card po-stat-card h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div>
                            <div class="text-muted small fw-semibold mb-2">Total Orders Placed</div>
                            <h4 class="fw-bold text-dark mb-0">{{ number_format($totalOrdersCount) }}</h4>
                        </div>
                        <div class="po-stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-file-contract"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card po-stat-card h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="min-w-0">
                            <div class="text-muted small fw-semibold mb-2">Committed Order Value</div>
                            <h4 class="fw-bold text-primary mb-0 font-monospace text-break">
                                ৳{{ number_format($totalCommitmentVal, 2) }}
                            </h4>
                        </div>
                        <div class="po-stat-icon bg-dark bg-opacity-10 text-dark">
                            <i class="fas fa-coins"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card po-stat-card h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div>
                            <div class="text-muted small fw-semibold mb-2">Pending Delivery</div>
                            <h4 class="fw-bold text-warning mb-0">{{ number_format($pendingDeliveryCount ?? 0) }}</h4>
                        </div>
                        <div class="po-stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-truck-ramp-box"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card po-stat-card h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div>
                            <div class="text-muted small fw-semibold mb-2">Completed &amp; Fulfilled</div>
                            <h4 class="fw-bold text-success mb-0">{{ number_format($completedOrdersCount ?? 0) }}</h4>
                        </div>
                        <div class="po-stat-icon bg-success bg-opacity-10 text-success">
                            <i class="fas fa-circle-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Filter Toolbar & Purchase Orders Table --}}
    <div class="card po-main-card">

        <div class="card-header bg-white border-bottom p-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <div>
                    <h6 class="fw-bold text-dark mb-1">Purchase Order Records</h6>
                    <p class="text-muted small mb-0">Search and filter your purchase order records.</p>
                </div>
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                    {{ number_format($purchaseOrders->total()) }} Records
                </span>
            </div>

            <form action="{{ route('admin.purchase-orders.index') }}" method="GET" class="row g-2 align-items-center">

                <div class="col-12 col-md-6 col-xl-3">
                    <div class="input-group input-group-sm po-search-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input
                            type="text"
                            name="search"
                            class="form-control bg-light border-start-0"
                            placeholder="PO #, Ref #, Notes..."
                            value="{{ request('search') }}"
                        >
                    </div>
                </div>

                <div class="col-6 col-md-3 col-xl-2">
                    <select name="supplier_id" class="form-select form-select-sm">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers ?? [] as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }} ({{ $supplier->company_name ?? 'Vendor' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-3 col-xl-2">
                    <select name="warehouse_id" class="form-select form-select-sm">
                        <option value="">All Warehouses</option>
                        @foreach($warehouses ?? [] as $wh)
                            <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                {{ $wh->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-3 col-xl-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div class="col-6 col-md-3 col-xl-1">
                    <input
                        type="date"
                        name="order_date"
                        class="form-control form-control-sm"
                        value="{{ request('order_date') }}"
                        title="Order Date"
                    >
                </div>

                <div class="col-12 col-md-6 col-xl-2 d-flex gap-2">
                    <button type="submit" class="btn btn-dark btn-sm flex-grow-1">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>

                    @if(request()->anyFilled(['search', 'supplier_id', 'warehouse_id', 'status', 'order_date']))
                        <a
                            href="{{ route('admin.purchase-orders.index') }}"
                            class="btn btn-outline-danger btn-sm px-3"
                            title="Clear Filters"
                        >
                            <i class="fas fa-rotate-left"></i>
                        </a>
                    @endif
                </div>

            </form>
        </div>

        {{-- Purchase Orders Table --}}
        <div class="table-responsive po-table-wrapper">
            <table class="table table-hover align-middle mb-0 po-table">
                <thead>
                    <tr>
                        <th class="ps-3">PO #</th>
                        <th>Order Date</th>
                        <th>Exp. Delivery</th>
                        <th>Supplier / Vendor</th>
                        <th>Destination</th>
                        <th class="text-end">Order Value (৳)</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($purchaseOrders as $po)
                        @php
                            $orderTotal = (float) $po->grand_total;
                            $isOverdue = in_array($po->status, ['pending', 'sent']) && $po->expected_delivery_date && \Carbon\Carbon::parse($po->expected_delivery_date)->isPast();
                        @endphp

                        <tr>
                            <td class="ps-3">
                                <a
                                    href="{{ route('admin.purchase-orders.show', $po->id) }}"
                                    class="text-primary text-decoration-none fw-bold font-monospace"
                                >
                                    {{ $po->po_number }}
                                </a>
                            </td>

                            <td class="text-muted text-nowrap">
                                {{ \Carbon\Carbon::parse($po->order_date)->format('d M, Y') }}
                            </td>

                            <td class="text-nowrap">
                                <div class="{{ $isOverdue ? 'text-danger fw-semibold' : 'text-dark' }}">
                                    {{ \Carbon\Carbon::parse($po->expected_delivery_date)->format('d M, Y') }}
                                </div>
                            </td>

                            <td>
                                <div class="fw-semibold text-dark">{{ $po->supplier->name ?? '—' }}</div>
                                <small class="text-muted d-block">{{ $po->supplier->company_name ?? '' }}</small>
                            </td>

                            <td class="text-secondary">
                                <span class="text-nowrap">
                                    <i class="fas fa-warehouse me-1 text-muted"></i>{{ $po->warehouse->name ?? 'Central Warehouse' }}
                                </span>
                            </td>

                            <td class="text-end fw-bold text-dark font-monospace text-nowrap">
                                ৳{{ number_format($orderTotal, 2) }}
                            </td>

                            <td class="text-center">
                                @switch($po->status)
                                    @case('draft')
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-2">
                                            Draft
                                        </span>
                                        @break

                                    @case('pending')
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-2">
                                            Pending
                                        </span>
                                        @break

                                    @case('approved')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-2">
                                            Approved
                                        </span>
                                        @break

                                    @case('completed')
                                        <span class="badge bg-success text-white px-2 py-2">
                                            <i class="fas fa-check-circle me-1"></i> Completed
                                        </span>
                                        @break

                                    @case('cancelled')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-2">
                                            Cancelled
                                        </span>
                                        @break

                                    @default
                                        <span class="badge bg-light text-dark border px-2 py-2">
                                            {{ ucfirst(str_replace('_', ' ', $po->status)) }}
                                        </span>
                                @endswitch
                            </td>

                            <td class="text-end pe-3">
                                <div class="dropdown">
                                    <button
                                        class="btn btn-sm btn-light border dropdown-toggle px-3"
                                        type="button"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false"
                                    >
                                        Manage
                                    </button>

                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.purchase-orders.show', $po->id) }}">
                                                <i class="fas fa-eye text-primary me-2"></i> View Order Details
                                            </a>
                                        </li>

                                        @if(!empty($po->supplier_id))
                                            <li>
                                                <a class="dropdown-item text-secondary" href="{{ route('admin.suppliers.ledger', $po->supplier_id) }}">
                                                    <i class="fas fa-book-open text-secondary me-2"></i> Supplier Ledger
                                                </a>
                                            </li>
                                        @endif

                                        <li><hr class="dropdown-divider"></li>

                                        <li>
                                            <form action="{{ route('admin.purchase-orders.destroy', $po->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete Purchase Order #{{ $po->po_number }} and its associated bill/ledger entries?');">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="fas fa-trash-alt text-danger me-2"></i> Delete Order
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <div class="po-empty-state">
                                    <i class="fas fa-file-signature fa-3x mb-3 text-secondary opacity-50"></i>
                                    <div class="fw-semibold text-dark">No purchase orders found.</div>
                                    <small class="text-muted">Try adjusting your filters or search keywords.</small>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($purchaseOrders->hasPages())
            <div class="card-footer bg-white border-top p-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <small class="text-muted">
                        Showing {{ $purchaseOrders->firstItem() ?? 0 }} to {{ $purchaseOrders->lastItem() ?? 0 }} of {{ $purchaseOrders->total() }} purchase orders
                    </small>

                    <div class="po-pagination">
                        {{ $purchaseOrders->links() }}
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>

<style>
    /* Purchase Orders - Kaiadmin White Classic */
    .purchase-orders-page {
        --po-border: #e8edf3;
        --po-muted: #6b7280;
        --po-surface: #ffffff;
        --po-soft-bg: #f7f9fc;
        color: #344054;
    }

    .purchase-orders-page .card {
        border: 1px solid var(--po-border);
        border-radius: 10px;
        background: var(--po-surface);
        box-shadow: 0 2px 10px rgba(16, 24, 40, 0.04);
    }

    .purchase-orders-page .po-stat-card {
        transition: box-shadow 0.2s ease, transform 0.2s ease;
    }

    .purchase-orders-page .po-stat-card:hover {
        box-shadow: 0 5px 18px rgba(16, 24, 40, 0.08);
        transform: translateY(-2px);
    }

    .purchase-orders-page .po-stat-icon {
        width: 48px;
        height: 48px;
        min-width: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 19px;
    }

    .purchase-orders-page .po-main-card {
        overflow: visible;
    }

    .purchase-orders-page .card-header,
    .purchase-orders-page .card-footer {
        border-color: var(--po-border) !important;
    }

    .purchase-orders-page .form-control,
    .purchase-orders-page .form-select,
    .purchase-orders-page .input-group-text {
        border-color: #dce2ea;
        border-radius: 6px;
    }

    .purchase-orders-page .input-group > .input-group-text {
        border-radius: 6px 0 0 6px;
    }

    .purchase-orders-page .input-group > .form-control {
        border-radius: 0 6px 6px 0;
    }

    .purchase-orders-page .form-control:focus,
    .purchase-orders-page .form-select:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 0.18rem rgba(var(--bs-primary-rgb), 0.12);
    }

    .purchase-orders-page .btn {
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .purchase-orders-page .btn-sm {
        min-height: 34px;
    }

    .purchase-orders-page .po-table-wrapper {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .purchase-orders-page .po-table {
        min-width: 1120px;
        font-size: 13px;
        border-collapse: separate;
        border-spacing: 0;
    }

    .purchase-orders-page .po-table thead th {
        background: #f7f9fc;
        color: #667085;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.45px;
        text-transform: uppercase;
        white-space: nowrap;
        padding: 13px 12px;
        border-bottom: 1px solid var(--po-border);
    }

    .purchase-orders-page .po-table tbody td {
        padding: 13px 12px;
        border-bottom: 1px solid #edf0f4;
        vertical-align: middle;
    }

    .purchase-orders-page .po-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .purchase-orders-page .po-table tbody tr:hover {
        background-color: #fafbfe;
    }

    .purchase-orders-page .po-table .badge {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.1px;
        border-radius: 5px;
        white-space: nowrap;
    }

    .purchase-orders-page .dropdown-menu {
        min-width: 190px;
        padding: 6px;
        border: 1px solid var(--po-border);
        border-radius: 8px;
        font-size: 12.5px;
        z-index: 1050;
    }

    .purchase-orders-page .dropdown-item {
        padding: 8px 10px;
        border-radius: 5px;
    }

    .purchase-orders-page .dropdown-item:hover {
        background: var(--po-soft-bg);
    }

    .purchase-orders-page .po-empty-state {
        padding: 12px 0;
    }

    .purchase-orders-page .po-pagination nav {
        margin: 0;
    }

    .purchase-orders-page .po-pagination .pagination {
        margin-bottom: 0;
        flex-wrap: wrap;
        gap: 3px;
    }

    .purchase-orders-page .po-pagination .page-link {
        border-radius: 5px;
        border-color: var(--po-border);
        font-size: 12px;
    }

    @media (max-width: 767.98px) {
        .purchase-orders-page {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        .purchase-orders-page .po-stat-icon {
            width: 42px;
            height: 42px;
            min-width: 42px;
            font-size: 16px;
        }

        .purchase-orders-page .po-stat-card .card-body {
            padding: 12px !important;
        }

        .purchase-orders-page .po-stat-card h4 {
            font-size: 18px;
        }

        .purchase-orders-page .card-header {
            padding: 13px !important;
        }

        .purchase-orders-page .po-pagination {
            width: 100%;
            overflow-x: auto;
        }
    }

    @media (max-width: 575.98px) {
        .purchase-orders-page .po-stat-card .text-muted {
            font-size: 11px;
        }

        .purchase-orders-page .po-stat-card h4 {
            font-size: 16px;
        }

        .purchase-orders-page .po-stat-icon {
            width: 36px;
            height: 36px;
            min-width: 36px;
            font-size: 14px;
        }

        .purchase-orders-page .po-pagination .pagination {
            justify-content: flex-start;
        }
    }

    /* Print Layout */
    @media print {
        .admin-dark-sidebar,
        .admin-navbar,
        .purchase-orders-page .btn,
        .purchase-orders-page .dropdown,
        .purchase-orders-page .card-header form,
        .purchase-orders-page .po-pagination {
            display: none !important;
        }

        .purchase-orders-page {
            padding: 0 !important;
        }

        .purchase-orders-page .card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
            margin-bottom: 12px !important;
        }

        .purchase-orders-page .po-table-wrapper {
            overflow: visible !important;
        }

        .purchase-orders-page .po-table {
            min-width: 0 !important;
            width: 100% !important;
            font-size: 10px !important;
        }

        .purchase-orders-page .po-table th,
        .purchase-orders-page .po-table td {
            border: 1px solid #dee2e6 !important;
            padding: 6px !important;
        }

        .purchase-orders-page .po-table thead th {
            background: #f1f1f1 !important;
            color: #000 !important;
        }
    }
</style>
@endsection