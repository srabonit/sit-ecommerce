@extends('layouts.admin')

@section('title', 'Goods Received Notes (GRN) Register')

@section('admin_content')
@php
    $receiptsList = $purchaseReceipts ?? ($receipts ?? collect([]));
    $totalReceiptsCount = method_exists($receiptsList, 'total') ? $receiptsList->total() : $receiptsList->count();
    $totalDeliveredUnits = (float) ($totalDeliveredQty ?? 0);
    $totalAcceptedUnits  = (float) ($totalAcceptedQty ?? 0);
    $totalRejectedUnits  = (float) ($totalRejectedQty ?? 0);
    $pendingCount        = (int) ($pendingQcCount ?? 0);
@endphp

<div class="container-fluid px-3 py-3 grn-register-page">

    {{-- Breadcrumb & Top Action Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 grn-page-header">
        <div>
            <h5 class="fw-bold text-dark mb-2 grn-page-title">
                <i class="fas fa-truck-ramp-box text-success me-2"></i>Goods Received Notes (GRN / Inward Inspection Register)
            </h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 grn-breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Procurement</li>
                    <li class="breadcrumb-item active" aria-current="page">Purchase Receipts (GRN)</li>
                </ol>
            </nav>
        </div>

        <div class="d-flex flex-wrap gap-2 grn-header-actions">
            <button type="button" class="btn btn-sm btn-outline-secondary grn-action-btn" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print Receipts Log
            </button>
            <a href="{{ route('admin.purchase-receipts.create') }}" class="btn btn-sm btn-success grn-action-btn">
                <i class="fas fa-plus-circle me-1"></i> Inward New GRN
            </a>
        </div>
    </div>

    {{-- Success Alert --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4 grn-alert" role="alert">
            <div class="d-flex align-items-center gap-2 fw-semibold">
                <i class="fas fa-circle-check text-success"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Inward & Inspection Stat Cards --}}
    <div class="row g-3 mb-4 grn-stats-row">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 grn-stat-card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="min-w-0">
                            <div class="text-muted small fw-semibold grn-stat-label">Total Consignments</div>
                            <h4 class="fw-bold text-dark mb-0 mt-2 font-monospace grn-stat-value">
                                {{ number_format($totalReceiptsCount) }}
                            </h4>
                        </div>
                        <div class="grn-stat-icon grn-stat-icon-success">
                            <i class="fas fa-boxes-packing"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 grn-stat-card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="min-w-0">
                            <div class="text-muted small fw-semibold grn-stat-label">Accepted to Warehouse</div>
                            <h4 class="fw-bold text-success mb-0 mt-2 font-monospace grn-stat-value">
                                {{ number_format($totalAcceptedUnits, 2) }}
                                <small class="fs-6 text-muted fw-normal">Units</small>
                            </h4>
                        </div>
                        <div class="grn-stat-icon grn-stat-icon-primary">
                            <i class="fas fa-circle-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 grn-stat-card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="min-w-0">
                            <div class="text-muted small fw-semibold grn-stat-label">Pending Inspection (QC)</div>
                            <h4 class="fw-bold text-warning mb-0 mt-2 font-monospace grn-stat-value">
                                {{ number_format($pendingCount) }}
                            </h4>
                        </div>
                        <div class="grn-stat-icon grn-stat-icon-warning">
                            <i class="fas fa-microscope"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 grn-stat-card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="min-w-0">
                            <div class="text-muted small fw-semibold grn-stat-label">Rejected / Quarantine</div>
                            <h4 class="fw-bold text-danger mb-0 mt-2 font-monospace grn-stat-value">
                                {{ number_format($totalRejectedUnits, 2) }}
                                <small class="fs-6 text-muted fw-normal">Units</small>
                            </h4>
                        </div>
                        <div class="grn-stat-icon grn-stat-icon-danger">
                            <i class="fas fa-triangle-exclamation"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Toolbar & Search --}}
    <div class="card border-0 shadow-sm mb-4 grn-register-card">

        <div class="card-header bg-white border-bottom p-3 grn-filter-header">
            <form action="{{ route('admin.purchase-receipts.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-3">
                    <div class="input-group input-group-sm grn-search-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control bg-light border-start-0"
                               placeholder="GRN #, Challan #, PO #, Vehicle #..."
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
                    <select name="warehouse_id" class="form-select form-select-sm">
                        <option value="">All Warehouses</option>
                        @foreach($warehouses ?? [] as $wh)
                            <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                {{ $wh->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All QC States</option>
                        <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Stock Accepted &amp; Posted</option>
                        <option value="pending_qc" {{ request('status') === 'pending_qc' ? 'selected' : '' }}>Quarantined / Pending QC</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected Consignment</option>
                    </select>
                </div>

                <div class="col-6 col-md-1">
                    <input type="date" name="receipt_date" class="form-control form-control-sm" value="{{ request('receipt_date') }}" title="Inward Date">
                </div>

                <div class="col-12 col-md-2 d-flex gap-2 justify-content-end grn-filter-actions">
                    <button type="submit" class="btn btn-sm btn-dark flex-grow-1 grn-action-btn">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    @if(request()->anyFilled(['search', 'supplier_id', 'warehouse_id', 'status', 'receipt_date']))
                        <a href="{{ route('admin.purchase-receipts.index') }}" class="btn btn-sm btn-outline-danger grn-clear-btn" title="Clear Filters">
                            <i class="fas fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Purchase Receipts Table --}}
        <div class="table-responsive grn-table-responsive">
            <table class="table table-hover align-middle mb-0 grn-register-table">
                <thead class="table-light text-secondary text-uppercase">
                    <tr>
                        <th class="ps-3" style="width: 140px;">GRN #</th>
                        <th style="width: 110px;">Inward Date</th>
                        <th>Supplier / Vendor</th>
                        <th style="width: 130px;">Challan #</th>
                        <th style="width: 130px;">Source PO #</th>
                        <th style="width: 120px;">Warehouse</th>
                        <th class="text-center" style="width: 110px;">Delivered</th>
                        <th class="text-center" style="width: 110px;">Accepted</th>
                        <th class="text-center" style="width: 100px;">Rejected</th>
                        <th class="text-center" style="width: 130px;">Inspection QC</th>
                        <th class="text-end pe-3" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receiptsList as $receipt)
                        @php
                            $deliveredQty = (float) ($receipt->items_sum_delivered_quantity ?? 0);
                            $acceptedQty  = (float) ($receipt->items_sum_accepted_quantity ?? 0);
                            $rejectedQty  = (float) ($receipt->items_sum_rejected_quantity ?? 0);
                        @endphp
                        <tr>
                            <td class="ps-3 fw-bold">
                                <a href="{{ route('admin.purchase-receipts.show', $receipt->id) }}" class="text-success text-decoration-none font-monospace grn-receipt-link">
                                    {{ $receipt->receipt_number }}
                                </a>
                                @if(!empty($receipt->vehicle_number))
                                    <small class="text-muted d-block font-monospace grn-vehicle-number">
                                        <i class="fas fa-truck fa-xs me-1"></i>{{ $receipt->vehicle_number }}
                                    </small>
                                @endif
                            </td>
                            <td class="text-muted">
                                {{ !empty($receipt->receipt_date) ? \Carbon\Carbon::parse($receipt->receipt_date)->format('d M, Y') : '—' }}
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $receipt->supplier->name ?? ($receipt->supplier_name ?? '—') }}</div>
                                <small class="text-muted d-block">{{ $receipt->supplier->company_name ?? ($receipt->supplier_company ?? '') }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border font-monospace grn-challan-badge">
                                    {{ $receipt->challan_no ?? ($receipt->supplier_challan_no ?? '—') }}
                                </span>
                            </td>
                            <td>
                                @if(!empty($receipt->purchase_order_id))
                                    <a href="{{ route('admin.purchase-orders.show', $receipt->purchase_order_id) }}" class="fw-semibold text-primary text-decoration-none grn-po-link">
                                        PO #{{ $receipt->purchaseOrder->po_number ?? $receipt->purchase_order_id }}
                                    </a>
                                @else
                                    <span class="text-muted small">Direct Delivery</span>
                                @endif
                            </td>
                            <td class="text-secondary small">
                                <i class="fas fa-warehouse me-1 text-muted"></i>{{ $receipt->warehouse->name ?? ($receipt->warehouse_name ?? 'Central') }}
                            </td>
                            <td class="text-center fw-semibold text-dark font-monospace">
                                {{ number_format($deliveredQty, 2) }}
                            </td>
                            <td class="text-center fw-bold text-success font-monospace">
                                {{ number_format($acceptedQty, 2) }}
                            </td>
                            <td class="text-center fw-bold font-monospace {{ $rejectedQty > 0 ? 'text-danger' : 'text-muted' }}">
                                {{ number_format($rejectedQty, 2) }}
                            </td>
                            <td class="text-center">
                                @switch($receipt->status ?? 'received')
                                    @case('received')
                                    @case('accepted')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 grn-status-badge">
                                            <i class="fas fa-circle-check me-1"></i> Stock Accepted
                                        </span>
                                        @break
                                    @case('pending_qc')
                                    @case('under_inspection')
                                    @case('pending')
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 grn-status-badge">
                                            <i class="fas fa-microscope me-1"></i> Pending QC
                                        </span>
                                        @break
                                    @case('rejected')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 grn-status-badge">
                                            <i class="fas fa-ban me-1"></i> Rejected
                                        </span>
                                        @break
                                    @default
                                        <span class="badge bg-light text-dark border px-2 py-1 grn-status-badge">
                                            {{ ucfirst(str_replace('_', ' ', $receipt->status)) }}
                                        </span>
                                @endswitch
                            </td>
                            <td class="text-end pe-3">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border dropdown-toggle grn-manage-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Manage
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm grn-action-menu">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.purchase-receipts.show', $receipt->id) }}">
                                                <i class="fas fa-eye text-primary me-2"></i> View GRN Voucher
                                            </a>
                                        </li>

                                        @if(($receipt->status ?? '') === 'pending_qc' || ($receipt->status ?? '') === 'under_inspection')
                                            <li>
                                                <form action="{{ route('admin.purchase-receipts.approve', $receipt->id) }}" method="POST" onsubmit="return confirm('Confirm QC approval for GRN #{{ $receipt->receipt_number }}?');">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item text-success">
                                                        <i class="fas fa-check-circle text-success me-2"></i> Approve QC &amp; Post Stock
                                                    </button>
                                                </form>
                                            </li>
                                        @endif

                                        <li>
                                            <a class="dropdown-item text-primary" href="{{ route('admin.purchases.create', ['grn_id' => $receipt->id, 'supplier_id' => $receipt->supplier_id]) }}">
                                                <i class="fas fa-file-invoice text-primary me-2"></i> Generate Bill from GRN
                                            </a>
                                        </li>

                                        @if($rejectedQty > 0)
                                            <li>
                                                <a class="dropdown-item text-danger" href="{{ route('admin.purchase-returns.create', ['grn_id' => $receipt->id, 'supplier_id' => $receipt->supplier_id]) }}">
                                                    <i class="fas fa-arrow-rotate-left text-danger me-2"></i> Return Rejected Goods (RTV)
                                                </a>
                                            </li>
                                        @endif

                                        @if(!empty($receipt->supplier_id) && Route::has('admin.suppliers.ledger'))
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-secondary" href="{{ route('admin.suppliers.ledger', $receipt->supplier_id) }}">
                                                    <i class="fas fa-book-open text-secondary me-2"></i> Supplier Ledger
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-5 text-muted grn-empty-state">
                                <i class="fas fa-truck-ramp-box fa-3x mb-3 text-secondary opacity-50"></i>
                                <div class="fw-semibold">No goods received notes found.</div>
                                <small>Receive inward shipments from vendors against open purchase orders or direct deliveries.</small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if(method_exists($receiptsList, 'hasPages') && $receiptsList->hasPages())
            <div class="card-footer bg-white border-top py-3 px-3 d-flex flex-wrap justify-content-between align-items-center gap-2 grn-pagination">
                <small class="text-muted">
                    Showing {{ $receiptsList->firstItem() ?? 0 }} to {{ $receiptsList->lastItem() ?? 0 }} of {{ $receiptsList->total() }} receipts
                </small>
                <div>
                    {{ $receiptsList->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

{{-- GRN Register Page Styles --}}
<style>
    .grn-register-page {
        --grn-border: #e9edf2;
        --grn-muted: #6c757d;
        --grn-radius: 8px;
        color: #344054;
    }

    .grn-register-page .grn-page-title {
        font-size: 18px;
        line-height: 1.45;
        letter-spacing: -0.2px;
    }

    .grn-register-page .grn-breadcrumb {
        font-size: 12px;
    }

    .grn-register-page .grn-breadcrumb a {
        color: #6c757d;
        text-decoration: none;
        transition: color .2s ease;
    }

    .grn-register-page .grn-breadcrumb a:hover {
        color: var(--bs-success);
    }

    .grn-register-page .grn-action-btn {
        min-height: 34px;
        padding: 7px 13px;
        border-radius: 6px;
        font-weight: 600;
        white-space: nowrap;
        transition: all .2s ease;
    }

    .grn-register-page .grn-action-btn:hover {
        transform: translateY(-1px);
    }

    .grn-register-page .grn-alert {
        border-radius: var(--grn-radius);
        padding: 13px 16px;
    }

    .grn-register-page .grn-stat-card {
        border: 1px solid var(--grn-border) !important;
        border-radius: var(--grn-radius);
        transition: box-shadow .2s ease, transform .2s ease;
    }

    .grn-register-page .grn-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 18px rgba(16, 24, 40, .07) !important;
    }

    .grn-register-page .grn-stat-label {
        font-size: 12px;
        line-height: 1.5;
    }

    .grn-register-page .grn-stat-value {
        font-size: 23px;
        line-height: 1.3;
        overflow-wrap: anywhere;
    }

    .grn-register-page .grn-stat-icon {
        width: 46px;
        height: 46px;
        flex: 0 0 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-size: 19px;
    }

    .grn-register-page .grn-stat-icon-success {
        color: #198754;
        background: rgba(25, 135, 84, .10);
    }

    .grn-register-page .grn-stat-icon-primary {
        color: #0d6efd;
        background: rgba(13, 110, 253, .10);
    }

    .grn-register-page .grn-stat-icon-warning {
        color: #d99a00;
        background: rgba(255, 193, 7, .15);
    }

    .grn-register-page .grn-stat-icon-danger {
        color: #dc3545;
        background: rgba(220, 53, 69, .10);
    }

    .grn-register-page .grn-register-card {
        border: 1px solid var(--grn-border) !important;
        border-radius: var(--grn-radius);
        overflow: visible;
    }

    .grn-register-page .grn-filter-header {
        border-radius: var(--grn-radius) var(--grn-radius) 0 0;
    }

    .grn-register-page .form-control,
    .grn-register-page .form-select,
    .grn-register-page .input-group-text {
        min-height: 34px;
        border-color: #dfe3e8;
        font-size: 12px;
    }

    .grn-register-page .form-control:focus,
    .grn-register-page .form-select:focus {
        border-color: rgba(25, 135, 84, .55);
        box-shadow: 0 0 0 .18rem rgba(25, 135, 84, .10);
    }

    .grn-register-page .grn-search-group .input-group-text {
        padding-left: 11px;
        padding-right: 10px;
    }

    .grn-register-page .grn-search-group .form-control {
        min-width: 0;
    }

    .grn-register-page .grn-clear-btn {
        min-width: 36px;
        min-height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
    }

    .grn-register-page .grn-table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .grn-register-page .grn-register-table {
        min-width: 1350px;
        width: 100%;
        font-size: 12.5px;
        border-collapse: separate;
        border-spacing: 0;
    }

    .grn-register-page .grn-register-table thead th {
        padding: 13px 10px;
        background: #f8f9fb;
        color: #667085;
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .45px;
        white-space: nowrap;
        border-bottom: 1px solid var(--grn-border);
        vertical-align: middle;
    }

    .grn-register-page .grn-register-table tbody td {
        padding: 12px 10px;
        border-bottom: 1px solid #edf0f3;
        vertical-align: middle;
        line-height: 1.5;
    }

    .grn-register-page .grn-register-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .grn-register-page .grn-register-table tbody tr:hover {
        background-color: #f9fbfd;
    }

    .grn-register-page .grn-receipt-link {
        font-size: 12.5px;
        font-weight: 700;
    }

    .grn-register-page .grn-receipt-link:hover,
    .grn-register-page .grn-po-link:hover {
        text-decoration: underline !important;
    }

    .grn-register-page .grn-vehicle-number {
        margin-top: 4px;
        font-size: 10.5px;
    }

    .grn-register-page .grn-challan-badge {
        padding: 5px 7px;
        font-size: 10.5px;
        font-weight: 500;
        white-space: nowrap;
    }

    .grn-register-page .grn-po-link {
        font-size: 12px;
        white-space: nowrap;
    }

    .grn-register-page .grn-status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 10.5px;
        font-weight: 600;
        line-height: 1.4;
        white-space: nowrap;
        border-radius: 5px;
    }

    .grn-register-page .grn-manage-btn {
        border-radius: 6px;
        padding: 5px 10px;
        font-size: 11.5px;
        font-weight: 600;
        white-space: nowrap;
    }

    .grn-register-page .grn-action-menu {
        min-width: 220px;
        padding: 6px;
        border: 1px solid var(--grn-border);
        border-radius: 8px;
        font-size: 12px;
        z-index: 1050;
    }

    .grn-register-page .grn-action-menu .dropdown-item {
        padding: 8px 10px;
        border-radius: 5px;
        white-space: normal;
    }

    .grn-register-page .grn-action-menu .dropdown-item:hover {
        background: #f4f6f9;
    }

    .grn-register-page .grn-empty-state {
        padding: 55px 20px !important;
    }

    .grn-register-page .grn-empty-state .fa-truck-ramp-box {
        display: block;
    }

    .grn-register-page .grn-pagination {
        border-radius: 0 0 var(--grn-radius) var(--grn-radius);
    }

    .grn-register-page .grn-pagination .pagination {
        margin-bottom: 0;
        flex-wrap: wrap;
        gap: 3px;
    }

    .grn-register-page .grn-pagination .page-link {
        border-radius: 5px;
        font-size: 12px;
    }

    @media (max-width: 991.98px) {
        .grn-register-page .grn-page-header {
            align-items: flex-start !important;
        }

        .grn-register-page .grn-stat-value {
            font-size: 21px;
        }

        .grn-register-page .grn-filter-actions {
            justify-content: flex-start !important;
        }
    }

    @media (max-width: 767.98px) {
        .grn-register-page {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        .grn-register-page .grn-page-title {
            font-size: 16px;
        }

        .grn-register-page .grn-header-actions {
            width: 100%;
        }

        .grn-register-page .grn-header-actions .grn-action-btn {
            flex: 1 1 auto;
        }

        .grn-register-page .grn-stat-card .card-body {
            padding: 12px !important;
        }

        .grn-register-page .grn-stat-label {
            font-size: 11px;
        }

        .grn-register-page .grn-stat-value {
            font-size: 19px;
        }

        .grn-register-page .grn-stat-icon {
            width: 38px;
            height: 38px;
            flex-basis: 38px;
            font-size: 16px;
        }

        .grn-register-page .grn-filter-header {
            padding: 12px !important;
        }

        .grn-register-page .grn-pagination {
            align-items: flex-start !important;
        }

        .grn-register-page .grn-pagination > div {
            max-width: 100%;
            overflow-x: auto;
        }
    }

    @media (max-width: 575.98px) {
        .grn-register-page .grn-page-header {
            gap: 12px !important;
        }

        .grn-register-page .grn-header-actions {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
        }

        .grn-register-page .grn-header-actions .grn-action-btn {
            width: 100%;
            padding-left: 7px;
            padding-right: 7px;
            font-size: 11px;
        }

        .grn-register-page .grn-stat-value {
            font-size: 17px;
        }

        .grn-register-page .grn-stat-value small {
            font-size: 10px !important;
        }

        .grn-register-page .grn-stat-icon {
            width: 32px;
            height: 32px;
            flex-basis: 32px;
            font-size: 14px;
        }

        .grn-register-page .grn-filter-actions {
            display: flex;
            width: 100%;
        }

        .grn-register-page .grn-filter-actions .btn {
            min-height: 36px;
        }

        .grn-register-page .grn-pagination {
            padding: 12px !important;
        }

        .grn-register-page .grn-pagination small {
            font-size: 11px;
        }
    }

    @media print {
        .grn-register-page .grn-header-actions,
        .grn-register-page .grn-filter-header,
        .grn-register-page .grn-manage-btn,
        .grn-register-page .dropdown,
        .grn-register-page .grn-pagination {
            display: none !important;
        }

        .grn-register-page {
            width: 100% !important;
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .grn-register-page .grn-page-header {
            margin-bottom: 15px !important;
        }

        .grn-register-page .grn-register-card,
        .grn-register-page .grn-stat-card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
            break-inside: avoid;
        }

        .grn-register-page .grn-table-responsive {
            overflow: visible !important;
        }

        .grn-register-page .grn-register-table {
            min-width: 0 !important;
            width: 100% !important;
            font-size: 9px !important;
        }

        .grn-register-page .grn-register-table thead th,
        .grn-register-page .grn-register-table tbody td {
            padding: 5px 4px !important;
            border: 1px solid #dee2e6 !important;
            color: #000 !important;
            font-size: 9px !important;
        }

        .grn-register-page .grn-register-table thead th {
            background: #f1f3f5 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .grn-register-page .grn-status-badge {
            white-space: normal;
        }

        .grn-register-page .grn-register-table tr {
            break-inside: avoid;
        }
    }
</style>
@endsection