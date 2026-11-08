@extends('layouts.admin')

@section('title', 'Supplier Product Exchanges Register')

@section('admin_content')
@php
    $exchangesList = $supplierExchanges ?? ($exchanges ?? collect([]));
    $totalExchangesCount = method_exists($exchangesList, 'total') ? $exchangesList->total() : $exchangesList->count();
    $totalOutwardVal = (float) ($totalOutwardValue ?? 0);
    $totalInwardVal  = (float) ($totalInwardValue ?? 0);
    $netDifferential = $totalInwardVal - $totalOutwardVal;
@endphp

<style>
    .exchange-register {
        --er-border: #e8edf3;
        --er-muted: #718096;
        color: #334155;
        font-size: 13px;
    }

    .exchange-register .page-heading {
        color: #172b4d;
        font-size: 19px;
        font-weight: 700;
        letter-spacing: -.35px;
    }

    .exchange-register .page-subtitle {
        color: #8794a5;
        font-size: 12px;
        margin-top: 5px;
    }

    .exchange-register .register-card {
        background: #fff;
        border: 1px solid var(--er-border) !important;
        border-radius: 12px !important;
        overflow: hidden;
        box-shadow: 0 3px 14px rgba(15, 23, 42, .045) !important;
    }

    .exchange-register .metric-card {
        height: 100%;
        padding: 17px;
        border: 1px solid var(--er-border);
        border-radius: 11px;
        background: #fff;
        box-shadow: 0 3px 12px rgba(15, 23, 42, .035);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .exchange-register .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(15, 23, 42, .07);
    }

    .exchange-register .metric-label {
        color: #77869a;
        font-size: 11.5px;
        font-weight: 600;
        margin-bottom: 7px;
    }

    .exchange-register .metric-value {
        color: #26364b;
        font-size: 19px;
        font-weight: 750;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    .exchange-register .metric-icon {
        display: inline-flex;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 17px;
    }

    .exchange-register .register-card .card-header {
        padding: 15px 18px;
        background: #fff;
        border-bottom: 1px solid var(--er-border);
    }

    .exchange-register .section-title {
        color: #26364b;
        font-size: 13.5px;
        font-weight: 700;
        margin: 0;
    }

    .exchange-register .section-subtitle {
        display: block;
        margin-top: 4px;
        color: #8794a5;
        font-size: 11.5px;
    }

    .exchange-register .filter-panel {
        padding: 17px 18px;
    }

    .exchange-register .form-control,
    .exchange-register .form-select,
    .exchange-register .input-group-text {
        min-height: 36px;
        border-color: #dfe5ed;
        border-radius: 7px;
        font-size: 12px;
        box-shadow: none;
    }

    .exchange-register .input-group .input-group-text {
        border-radius: 7px 0 0 7px;
    }

    .exchange-register .input-group .form-control {
        border-radius: 0 7px 7px 0;
    }

    .exchange-register .form-control:focus,
    .exchange-register .form-select:focus {
        border-color: #80b6e8;
        box-shadow: 0 0 0 3px rgba(40, 120, 199, .08);
    }

    .exchange-register .btn {
        border-radius: 7px;
        font-size: 12px;
        font-weight: 600;
        padding: 7px 13px;
    }

    .exchange-register .btn-info {
        background: #2388b8;
        border-color: #2388b8;
    }

    .exchange-register .btn-info:hover {
        background: #19769f;
        border-color: #19769f;
    }

    .exchange-register .exchange-table {
        min-width: 1120px;
        margin-bottom: 0;
        font-size: 12px;
    }

    .exchange-register .exchange-table thead th {
        padding: 13px 11px;
        background: #f7f9fc;
        color: #64748b;
        border-color: #e8edf3;
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .35px;
        text-transform: uppercase;
        white-space: nowrap;
        vertical-align: middle;
    }

    .exchange-register .exchange-table tbody td {
        padding: 13px 11px;
        border-color: #edf0f4;
        vertical-align: middle;
    }

    .exchange-register .exchange-table tbody tr {
        transition: background .15s ease;
    }

    .exchange-register .exchange-table tbody tr:hover {
        background: #f9fbfe;
    }

    .exchange-register .exchange-number {
        color: #2388b8;
        font-size: 12px;
        font-weight: 700;
    }

    .exchange-register .exchange-reference {
        color: #94a3b8;
        font-size: 10.5px;
        margin-top: 4px;
    }

    .exchange-register .supplier-name {
        color: #26364b;
        font-weight: 700;
        font-size: 12.5px;
    }

    .exchange-register .supplier-company {
        display: block;
        color: #94a3b8;
        font-size: 11px;
        margin-top: 3px;
    }

    .exchange-register .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 5px 9px;
        border-radius: 6px;
        font-size: 10.5px;
        font-weight: 650;
        line-height: 1.4;
        white-space: nowrap;
    }

    .exchange-register .dropdown-menu {
        min-width: 205px;
        padding: 6px;
        border: 1px solid var(--er-border);
        border-radius: 9px;
        font-size: 12px;
    }

    .exchange-register .dropdown-item {
        padding: 8px 10px;
        border-radius: 6px;
    }

    .exchange-register .dropdown-item:hover {
        background: #f3f6fa;
    }

    .exchange-register .dropdown-item form {
        margin: 0;
    }

    .exchange-register .dropdown-item button {
        width: 100%;
        padding: 0;
        border: 0;
        background: transparent;
        text-align: left;
        font-size: inherit;
    }

    .exchange-register .empty-state {
        padding: 55px 20px !important;
    }

    .exchange-register .empty-state-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 65px;
        height: 65px;
        margin-bottom: 15px;
        border-radius: 18px;
        background: #f1f5f9;
        color: #94a3b8;
        font-size: 26px;
    }

    .exchange-register .pagination {
        margin-bottom: 0;
        gap: 4px;
    }

    .exchange-register .pagination .page-link {
        border-radius: 6px;
        border-color: #e5eaf0;
        color: #64748b;
        font-size: 12px;
    }

    .exchange-register .pagination .page-item.active .page-link {
        background: #2388b8;
        border-color: #2388b8;
        color: #fff;
    }

    .exchange-register .pagination-footer {
        padding: 14px 18px;
        border-top: 1px solid var(--er-border);
        background: #fff;
    }

    .exchange-register .alert {
        border-radius: 9px;
        font-size: 12.5px;
    }

    @media (max-width: 767.98px) {
        .exchange-register .page-heading {
            font-size: 16px;
        }

        .exchange-register .header-actions {
            width: 100%;
        }

        .exchange-register .header-actions .btn {
            flex: 1;
        }

        .exchange-register .metric-card {
            padding: 13px;
        }

        .exchange-register .metric-value {
            font-size: 15px;
        }

        .exchange-register .metric-icon {
            width: 34px;
            height: 34px;
            flex-basis: 34px;
            font-size: 14px;
        }

        .exchange-register .filter-panel {
            padding: 14px;
        }

        .exchange-register .pagination-footer {
            align-items: flex-start !important;
            flex-direction: column;
            gap: 12px;
        }

        .exchange-register .pagination-footer > div {
            max-width: 100%;
            overflow-x: auto;
        }
    }

    @media print {
        body {
            background: #fff !important;
        }

        .admin-dark-sidebar,
        .admin-navbar,
        .exchange-register .no-print,
        .exchange-register .dropdown,
        .exchange-register .filter-panel,
        .exchange-register .pagination-footer,
        .exchange-register .card-header form,
        .exchange-register .alert .btn-close {
            display: none !important;
        }

        .exchange-register {
            padding: 0 !important;
        }

        .exchange-register .register-card,
        .exchange-register .metric-card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
            break-inside: avoid;
        }

        .exchange-register .metric-card {
            padding: 10px;
        }

        .exchange-register .exchange-table {
            min-width: 0 !important;
            width: 100% !important;
            font-size: 9px !important;
        }

        .exchange-register .exchange-table th,
        .exchange-register .exchange-table td {
            padding: 6px !important;
            border: 1px solid #dee2e6 !important;
        }

        .exchange-register .table-responsive {
            overflow: visible !important;
        }

        .exchange-register .exchange-number {
            color: #000 !important;
        }
    }
</style>

<div class="container-fluid px-3 py-3 exchange-register">

    {{-- Action Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <span class="d-inline-flex align-items-center justify-content-center rounded-3"
                      style="width:38px;height:38px;background:#eaf6fc;color:#2388b8;">
                    <i class="fas fa-right-left"></i>
                </span>
                <h5 class="page-heading mb-0">Supplier Product Exchanges &amp; Swaps Register</h5>
            </div>
            <div class="page-subtitle">
                Monitor supplier returns, replacement goods and exchange settlement records.
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 header-actions no-print">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print Register
            </button>

            <a href="{{ route('admin.supplier-exchanges.create') }}" class="btn btn-sm btn-info text-white">
                <i class="fas fa-plus-circle me-1"></i> New Product Exchange
            </a>
        </div>
    </div>

    {{-- Success Alert --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center gap-2 fw-semibold">
                <i class="fas fa-circle-check text-success"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Metric Stat Cards --}}
    <div class="row g-3 mb-4">

        {{-- Total Exchanges --}}
        <div class="col-6 col-xl-3">
            <div class="metric-card">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="min-w-0">
                        <div class="metric-label">Total Exchanges</div>
                        <div class="metric-value font-monospace">
                            {{ number_format($totalExchangesCount) }}
                        </div>
                    </div>
                    <div class="metric-icon bg-info bg-opacity-10 text-info">
                        <i class="fas fa-right-left"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Outward --}}
        <div class="col-6 col-xl-3">
            <div class="metric-card">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="min-w-0">
                        <div class="metric-label">Total Outward Sent</div>
                        <div class="metric-value text-danger font-monospace">
                            ৳{{ number_format($totalOutwardVal, 2) }}
                        </div>
                    </div>
                    <div class="metric-icon bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-arrow-up-from-bracket"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Inward --}}
        <div class="col-6 col-xl-3">
            <div class="metric-card">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="min-w-0">
                        <div class="metric-label">Total Inward Received</div>
                        <div class="metric-value text-success font-monospace">
                            ৳{{ number_format($totalInwardVal, 2) }}
                        </div>
                    </div>
                    <div class="metric-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-arrow-down-to-bracket"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Net Differential --}}
        <div class="col-6 col-xl-3">
            <div class="metric-card">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="min-w-0">
                        <div class="metric-label">Net Balance Adjustment</div>
                        <div class="metric-value font-monospace {{ $netDifferential >= 0 ? 'text-primary' : 'text-danger' }}">
                            {{ $netDifferential >= 0 ? '+' : '' }}৳{{ number_format($netDifferential, 2) }}
                        </div>
                        <small class="text-muted">
                            {{ $netDifferential >= 0 ? 'Payable' : 'Claim' }}
                        </small>
                    </div>
                    <div class="metric-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-scale-balanced"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Filter & Register Table --}}
    <div class="card register-card">

        {{-- Filter Toolbar --}}
        <div class="card-header">
            <div class="d-flex align-items-center gap-2 mb-3">
                <i class="fas fa-filter text-info"></i>
                <h6 class="section-title">Search &amp; Filter Exchanges</h6>
            </div>

            <form action="{{ route('admin.supplier-exchanges.index') }}" method="GET" class="filter-panel p-0">
                <div class="row g-2 align-items-center">

                    {{-- Search --}}
                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text"
                                   name="search"
                                   class="form-control bg-light border-start-0"
                                   placeholder="Exchange #, Challan #, Notes..."
                                   value="{{ request('search') }}">
                        </div>
                    </div>

                    {{-- Supplier --}}
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

                    {{-- Warehouse --}}
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

                    {{-- Status --}}
                    <div class="col-6 col-md-3 col-xl-2">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Verification</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved / In-Transit</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed &amp; Settled</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    {{-- Date --}}
                    <div class="col-6 col-md-3 col-xl-1">
                        <input type="date"
                               name="exchange_date"
                               class="form-control form-control-sm"
                               value="{{ request('exchange_date') }}"
                               title="Exchange Date">
                    </div>

                    {{-- Actions --}}
                    <div class="col-12 col-md-6 col-xl-2 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-dark flex-grow-1">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>

                        @if(request()->anyFilled(['search', 'supplier_id', 'warehouse_id', 'status', 'exchange_date']))
                            <a href="{{ route('admin.supplier-exchanges.index') }}"
                               class="btn btn-sm btn-outline-danger"
                               title="Clear Filters">
                                <i class="fas fa-rotate-left"></i>
                            </a>
                        @endif
                    </div>

                </div>
            </form>
        </div>

        {{-- Table Heading --}}
        <div class="px-3 py-3 d-flex align-items-center justify-content-between gap-2">
            <div>
                <h6 class="section-title">
                    <i class="fas fa-list-ul text-info me-1"></i>
                    Exchange Records
                </h6>
                <small class="section-subtitle">Supplier product returns and replacement transactions.</small>
            </div>

            <span class="badge bg-light text-secondary border px-2 py-2">
                {{ number_format($totalExchangesCount) }} Records
            </span>
        </div>

        {{-- Supplier Exchanges Table --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle exchange-table">
                <thead>
                    <tr>
                        <th class="ps-3" style="width:140px;">Exchange #</th>
                        <th style="width:110px;">Date</th>
                        <th>Supplier / Vendor</th>
                        <th style="width:130px;">Warehouse</th>
                        <th class="text-end" style="width:130px;">Outward (৳)</th>
                        <th class="text-end" style="width:130px;">Inward (৳)</th>
                        <th class="text-end" style="width:140px;">Balance Delta (৳)</th>
                        <th class="text-center" style="width:140px;">Status</th>
                        <th class="text-end pe-3" style="width:120px;">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($exchangesList as $exchange)
                        @php
                            $outward = (float) ($exchange->outward_total ?? ($exchange->returned_total_value ?? 0));
                            $inward  = (float) ($exchange->inward_total ?? ($exchange->replacement_total_value ?? 0));
                            $delta   = (float) ($exchange->differential_amount ?? ($exchange->price_difference ?? ($inward - $outward)));
                        @endphp

                        <tr>
                            {{-- Exchange Number --}}
                            <td class="ps-3">
                                <a href="{{ route('admin.supplier-exchanges.show', $exchange->id) }}"
                                   class="exchange-number text-decoration-none font-monospace">
                                    {{ $exchange->exchange_number }}
                                </a>

                                @if(!empty($exchange->reference_no))
                                    <small class="exchange-reference d-block font-monospace">
                                        Ref: {{ $exchange->reference_no }}
                                    </small>
                                @endif
                            </td>

                            {{-- Date --}}
                            <td class="text-muted text-nowrap">
                                <i class="far fa-calendar-alt me-1 text-secondary"></i>
                                {{ !empty($exchange->exchange_date) ? \Carbon\Carbon::parse($exchange->exchange_date)->format('d M, Y') : '—' }}
                            </td>

                            {{-- Supplier --}}
                            <td>
                                <div class="supplier-name">
                                    {{ $exchange->supplier->name ?? ($exchange->supplier_name ?? '—') }}
                                </div>
                                <small class="supplier-company">
                                    {{ $exchange->supplier->company_name ?? ($exchange->supplier_company ?? '') }}
                                </small>
                            </td>

                            {{-- Warehouse --}}
                            <td class="text-secondary text-nowrap">
                                <i class="fas fa-warehouse me-1 text-muted"></i>
                                {{ $exchange->warehouse->name ?? ($exchange->warehouse_name ?? 'Central') }}
                            </td>

                            {{-- Outward --}}
                            <td class="text-end fw-semibold text-danger font-monospace text-nowrap">
                                ৳{{ number_format($outward, 2) }}
                            </td>

                            {{-- Inward --}}
                            <td class="text-end fw-semibold text-success font-monospace text-nowrap">
                                ৳{{ number_format($inward, 2) }}
                            </td>

                            {{-- Balance Delta --}}
                            <td class="text-end fw-bold font-monospace text-nowrap">
                                @if($delta > 0)
                                    <span class="text-danger">
                                        +৳{{ number_format($delta, 2) }}
                                        <small class="fw-normal d-block" style="font-size:10px;">(Payable)</small>
                                    </span>
                                @elseif($delta < 0)
                                    <span class="text-success">
                                        -৳{{ number_format(abs($delta), 2) }}
                                        <small class="fw-normal d-block" style="font-size:10px;">(Claim)</small>
                                    </span>
                                @else
                                    <span class="text-muted">
                                        ৳0.00
                                        <small class="fw-normal d-block" style="font-size:10px;">(Even)</small>
                                    </span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="text-center">
                                @switch($exchange->status)
                                    @case('pending')
                                        <span class="status-badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                            <i class="fas fa-clock me-1"></i> Pending Verification
                                        </span>
                                        @break

                                    @case('approved')
                                        <span class="status-badge bg-info-subtle text-info border border-info-subtle">
                                            <i class="fas fa-truck me-1"></i> Approved / Dispatched
                                        </span>
                                        @break

                                    @case('completed')
                                        <span class="status-badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="fas fa-check-double me-1"></i> Completed
                                        </span>
                                        @break

                                    @case('cancelled')
                                        <span class="status-badge bg-danger-subtle text-danger border border-danger-subtle">
                                            <i class="fas fa-ban me-1"></i> Cancelled
                                        </span>
                                        @break

                                    @default
                                        <span class="status-badge bg-light text-dark border">
                                            {{ ucfirst(str_replace('_', ' ', $exchange->status)) }}
                                        </span>
                                @endswitch
                            </td>

                            {{-- Actions --}}
                            <td class="text-end pe-3">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border dropdown-toggle"
                                            type="button"
                                            data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                        Manage
                                    </button>

                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.supplier-exchanges.show', $exchange->id) }}">
                                                <i class="fas fa-eye text-primary me-2"></i>
                                                View Exchange Voucher
                                            </a>
                                        </li>

                                        @if($exchange->status === 'pending')
                                            <li>
                                                <form action="{{ route('admin.supplier-exchanges.approve', $exchange->id) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('Approve exchange #{{ $exchange->exchange_number }}? This will adjust inventory stock and post ledger delta.');">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item text-success">
                                                        <i class="fas fa-check-circle text-success me-2"></i>
                                                        Approve &amp; Post Stock
                                                    </button>
                                                </form>
                                            </li>
                                        @endif

                                        @if(!empty($exchange->supplier_id) && Route::has('admin.suppliers.ledger'))
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-secondary"
                                                   href="{{ route('admin.suppliers.ledger', $exchange->supplier_id) }}">
                                                    <i class="fas fa-book-open text-secondary me-2"></i>
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
                            <td colspan="9" class="text-center empty-state">
                                <div class="empty-state-icon">
                                    <i class="fas fa-right-left"></i>
                                </div>
                                <div class="fw-bold text-dark mb-1">
                                    No supplier product exchanges recorded.
                                </div>
                                <small class="text-muted">
                                    Manage product swaps, defective batch replacements, and variant exchanges with suppliers.
                                </small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if(method_exists($exchangesList, 'hasPages') && $exchangesList->hasPages())
            <div class="pagination-footer d-flex justify-content-between align-items-center flex-wrap gap-3">
                <small class="text-muted">
                    Showing
                    <strong>{{ $exchangesList->firstItem() ?? 0 }}</strong>
                    to
                    <strong>{{ $exchangesList->lastItem() ?? 0 }}</strong>
                    of
                    <strong>{{ $exchangesList->total() }}</strong>
                    exchanges
                </small>

                <div>
                    {{ $exchangesList->links() }}
                </div>
            </div>
        @endif

    </div>
</div>
@endsection