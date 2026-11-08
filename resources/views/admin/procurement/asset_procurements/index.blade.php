@extends('layouts.admin')

@section('title', 'Fixed Assets & Capitalized Procurements Register')

@section('admin_content')
<div class="container-fluid px-3 py-3 asset-register-page">

    {{-- Breadcrumb & Action Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 asset-page-header">
        <div>
            <h5 class="fw-bold text-dark mb-1 asset-page-title">
                <i class="fas fa-boxes-packing text-primary me-2"></i>Fixed Assets &amp; Capitalized Procurements Register
            </h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 asset-breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Procurement</li>
                    <li class="breadcrumb-item active" aria-current="page">Asset Register</li>
                </ol>
            </nav>
        </div>

        <div class="d-flex flex-wrap gap-2 asset-header-actions">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print Register
            </button>
            @php
                $createAssetUrl = Route::has('admin.procurement-assets.create')
                    ? route('admin.procurement-assets.create')
                    : (Route::has('admin.asset-procurements.create') ? route('admin.asset-procurements.create') : url('admin/procurement/procurement-assets/create'));
            @endphp
            <a href="{{ $createAssetUrl }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus-circle me-1"></i> Capitalize New Asset
            </a>
        </div>
    </div>

    {{-- Asset Valuation & Depreciation Metric Cards --}}
    @php
        $totalAssetsCount = method_exists($assets, 'total') ? $assets->total() : $assets->count();
        $totalGrossCostVal = (float) ($totalGrossValue ?? ($assets->sum('capitalized_cost') ?? 0));
        $totalAccumDeprVal = (float) ($totalAccumulatedDepreciation ?? ($assets->sum('accumulated_depreciation') ?? 0));
        $totalNetBookVal   = (float) ($totalNetBookValue ?? max(0, $totalGrossCostVal - $totalAccumDeprVal));
    @endphp

    <div class="row g-3 mb-4 asset-metrics">
        <div class="col-6 col-md-3">
            <div class="card asset-metric-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between gap-2">
                    <div class="min-w-0">
                        <div class="text-muted small fw-semibold">Total Tracked Assets</div>
                        <h4 class="fw-bold text-dark mb-0 mt-2">{{ number_format($totalAssetsCount) }}</h4>
                    </div>
                    <div class="asset-metric-icon asset-icon-primary"><i class="fas fa-barcode"></i></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card asset-metric-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between gap-2">
                    <div class="min-w-0">
                        <div class="text-muted small fw-semibold">Gross Capitalized Cost</div>
                        <h4 class="fw-bold text-dark mb-0 mt-2">৳{{ number_format($totalGrossCostVal, 2) }}</h4>
                    </div>
                    <div class="asset-metric-icon asset-icon-dark"><i class="fas fa-coins"></i></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card asset-metric-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between gap-2">
                    <div class="min-w-0">
                        <div class="text-muted small fw-semibold">Accumulated Depreciation</div>
                        <h4 class="fw-bold text-danger mb-0 mt-2">৳{{ number_format($totalAccumDeprVal, 2) }}</h4>
                    </div>
                    <div class="asset-metric-icon asset-icon-danger"><i class="fas fa-arrow-trend-down"></i></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card asset-metric-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between gap-2">
                    <div class="min-w-0">
                        <div class="text-muted small fw-semibold">Written-Down Value (WDV)</div>
                        <h4 class="fw-bold text-success mb-0 mt-2">৳{{ number_format($totalNetBookVal, 2) }}</h4>
                    </div>
                    <div class="asset-metric-icon asset-icon-success"><i class="fas fa-chart-line"></i></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Toolbar & Search --}}
    <div class="card asset-content-card mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <div class="fw-semibold text-dark"><i class="fas fa-sliders me-2 text-primary"></i>Search &amp; Filter Assets</div>
            </div>
            @php
                $filterFormUrl = Route::has('admin.procurement-assets.index')
                    ? route('admin.procurement-assets.index')
                    : (Route::has('admin.asset-procurements.index') ? route('admin.asset-procurements.index') : url('admin/procurement/procurement-assets'));
            @endphp
            <form action="{{ $filterFormUrl }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="input-group input-group-sm asset-search-group">
                        <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control"
                               placeholder="Asset Tag, Title, Serial #, Brand..."
                               value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-6 col-md-3 col-xl-2">
                    <select name="asset_category_id" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        @foreach($assetCategories ?? [] as $category)
                            <option value="{{ $category->id }}" {{ request('asset_category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-3 col-xl-2">
                    <select name="branch_id" class="form-select form-select-sm">
                        <option value="">All Branches</option>
                        @foreach($branches ?? [] as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-4 col-xl-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Lifecycle States</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>In-Service / Active</option>
                        <option value="under_maintenance" {{ request('status') === 'under_maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                        <option value="disposed" {{ request('status') === 'disposed' ? 'selected' : '' }}>Disposed / Decommissioned</option>
                        <option value="written_off" {{ request('status') === 'written_off' ? 'selected' : '' }}>Written Off</option>
                    </select>
                </div>

                <div class="col-6 col-md-4 col-xl-1">
                    <input type="date" name="purchase_date" class="form-control form-control-sm" value="{{ request('purchase_date') }}" title="Acquisition Date">
                </div>

                <div class="col-12 col-md-4 col-xl-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-dark flex-grow-1">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    @if(request()->anyFilled(['search', 'asset_category_id', 'branch_id', 'status', 'purchase_date']))
                        <a href="{{ $filterFormUrl }}" class="btn btn-sm btn-outline-danger" title="Clear Filters">
                            <i class="fas fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Assets Table --}}
        <div class="table-responsive asset-table-wrap">
            <table class="table table-hover align-middle mb-0 asset-table">
                <thead>
                    <tr>
                        <th class="ps-3">Asset Tag</th>
                        <th>Asset Title &amp; Category</th>
                        <th>Serial / Brand</th>
                        <th>Location / Branch</th>
                        <th>Acquired Date</th>
                        <th class="text-end">Cost (৳)</th>
                        <th class="text-end">Depr. (৳)</th>
                        <th class="text-end">Book Value (৳)</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assets as $asset)
                        @php
                            $cost = (float) ($asset->capitalized_cost ?? $asset->purchase_cost);
                            $depr = (float) ($asset->accumulated_depreciation ?? 0);
                            $salvage = (float) ($asset->salvage_value ?? 0);
                            $wdv = max($salvage, $cost - $depr);
                            $showAssetUrl = Route::has('admin.procurement-assets.show')
                                ? route('admin.procurement-assets.show', $asset->id)
                                : (Route::has('admin.asset-procurements.show') ? route('admin.asset-procurements.show', $asset->id) : url('admin/procurement/procurement-assets/' . $asset->id));
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <a href="{{ $showAssetUrl }}" class="text-primary text-decoration-none fw-semibold font-monospace">
                                    {{ $asset->asset_code }}
                                </a>
                                @if(!empty($asset->purchase_id))
                                    <small class="text-muted d-block asset-subtext">
                                        Bill: #{{ $asset->purchase->purchase_number ?? $asset->purchase_id }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $asset->name }}</div>
                                <small class="text-muted d-block asset-subtext">
                                    <i class="fas fa-tag fa-xs me-1 text-secondary"></i>{{ $asset->category->name ?? 'General Asset' }}
                                </small>
                            </td>
                            <td>
                                <div class="text-dark">{{ $asset->brand ?? '—' }}</div>
                                @if(!empty($asset->serial_number))
                                    <small class="text-muted d-block font-monospace asset-subtext">SN: {{ $asset->serial_number }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="text-dark small fw-semibold">{{ $asset->branch->name ?? 'HQ Branch' }}</div>
                                <small class="text-muted d-block asset-subtext">{{ $asset->location ?? 'Unspecified' }}</small>
                            </td>
                            <td class="text-muted text-nowrap">
                                {{ !empty($asset->purchase_date) ? \Carbon\Carbon::parse($asset->purchase_date)->format('d M, Y') : '—' }}
                            </td>
                            <td class="text-end fw-semibold text-dark text-nowrap">৳{{ number_format($cost, 2) }}</td>
                            <td class="text-end fw-semibold text-danger text-nowrap">৳{{ number_format($depr, 2) }}</td>
                            <td class="text-end fw-bold text-success text-nowrap">৳{{ number_format($wdv, 2) }}</td>
                            <td class="text-center">
                                @switch($asset->status ?? 'active')
                                    @case('active')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle asset-status-badge">
                                            <i class="fas fa-circle-check me-1"></i> Active
                                        </span>
                                        @break
                                    @case('under_maintenance')
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle asset-status-badge">
                                            Maintenance
                                        </span>
                                        @break
                                    @case('disposed')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle asset-status-badge">
                                            Disposed
                                        </span>
                                        @break
                                    @case('written_off')
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle asset-status-badge">
                                            Written Off
                                        </span>
                                        @break
                                    @default
                                        <span class="badge bg-light text-dark border asset-status-badge">
                                            {{ ucfirst(str_replace('_', ' ', $asset->status)) }}
                                        </span>
                                @endswitch
                            </td>
                            <td class="text-end pe-3">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border dropdown-toggle asset-manage-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Manage
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm asset-action-menu">
                                        <li>
                                            <a class="dropdown-item" href="{{ $showAssetUrl }}">
                                                <i class="fas fa-eye text-primary me-2"></i> View Asset Card
                                            </a>
                                        </li>

                                        @if(!empty($asset->purchase_id) && Route::has('admin.purchases.show'))
                                            <li>
                                                <a class="dropdown-item text-secondary" href="{{ route('admin.purchases.show', $asset->purchase_id) }}">
                                                    <i class="fas fa-file-invoice text-secondary me-2"></i> Linked Purchase Bill
                                                </a>
                                            </li>
                                        @endif

                                        @if(!empty($asset->supplier_id) && Route::has('admin.suppliers.ledger'))
                                            <li>
                                                <a class="dropdown-item text-secondary" href="{{ route('admin.suppliers.ledger', $asset->supplier_id) }}">
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
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="fas fa-boxes-packing fa-3x mb-3 text-secondary opacity-50"></i>
                                <div class="fw-semibold">No fixed assets recorded in the registry.</div>
                                <small>Capitalize equipment, machinery, IT hardware, or vehicles from purchase invoices.</small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if(method_exists($assets, 'hasPages') && $assets->hasPages())
            <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center flex-wrap gap-2 asset-pagination">
                <small class="text-muted">
                    Showing {{ $assets->firstItem() ?? 0 }} to {{ $assets->lastItem() ?? 0 }} of {{ $assets->total() }} fixed assets
                </small>
                <div>
                    {{ $assets->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Page Styles --}}
<style>
    .asset-register-page {
        --asset-primary: var(--bs-primary, #1572e8);
        --asset-border: #e6eaf0;
        --asset-muted: #7b8493;
        color: #2a2f3a;
        min-width: 0;
    }

    .asset-register-page .asset-page-title {
        font-size: clamp(1.05rem, 1.5vw, 1.3rem);
        line-height: 1.4;
    }

    .asset-register-page .asset-breadcrumb {
        font-size: 12px;
    }

    .asset-register-page .asset-breadcrumb a {
        color: var(--asset-primary);
        text-decoration: none;
    }

    .asset-register-page .btn {
        min-height: 34px;
        border-radius: 6px;
        font-weight: 600;
        padding: .42rem .75rem;
    }

    .asset-register-page .asset-metric-card,
    .asset-register-page .asset-content-card {
        border: 1px solid var(--asset-border);
        border-radius: 9px;
        background: #fff;
        box-shadow: 0 2px 10px rgba(20, 35, 60, .035);
    }

    .asset-register-page .asset-metric-card .card-body {
        padding: 1.1rem 1.15rem;
    }

    .asset-register-page .asset-metric-card h4 {
        font-size: clamp(1.05rem, 1.6vw, 1.35rem);
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    .asset-register-page .asset-metric-icon {
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        font-size: 1.05rem;
    }

    .asset-register-page .asset-icon-primary { color: #1572e8; background: rgba(21, 114, 232, .10); }
    .asset-register-page .asset-icon-dark { color: #343a40; background: rgba(52, 58, 64, .08); }
    .asset-register-page .asset-icon-danger { color: #f25961; background: rgba(242, 89, 97, .10); }
    .asset-register-page .asset-icon-success { color: #31ce77; background: rgba(49, 206, 119, .11); }

    .asset-register-page .asset-content-card {
        overflow: visible;
    }

    .asset-register-page .asset-content-card > .card-header {
        border-radius: 9px 9px 0 0;
        padding: 1rem 1.15rem;
    }

    .asset-register-page .form-control,
    .asset-register-page .form-select,
    .asset-register-page .input-group-text {
        min-height: 36px;
        border-color: #e1e5eb;
        border-radius: 6px;
        font-size: 12.5px;
    }

    .asset-register-page .input-group .input-group-text {
        border-radius: 6px 0 0 6px;
        background: #f8f9fb;
    }

    .asset-register-page .input-group .form-control {
        border-radius: 0 6px 6px 0;
    }

    .asset-register-page .form-control:focus,
    .asset-register-page .form-select:focus {
        border-color: rgba(21, 114, 232, .55);
        box-shadow: 0 0 0 .18rem rgba(21, 114, 232, .10);
    }

    .asset-register-page .asset-table-wrap {
        width: 100%;
        border-radius: 0 0 9px 9px;
        scrollbar-width: thin;
    }

    .asset-register-page .asset-table {
        min-width: 1120px;
        font-size: 12.5px;
        color: #4b5563;
    }

    .asset-register-page .asset-table thead th {
        padding: .8rem .65rem;
        background: #f7f8fa;
        color: #697386;
        border-bottom: 1px solid var(--asset-border);
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .045em;
        text-transform: uppercase;
        white-space: nowrap;
        vertical-align: middle;
    }

    .asset-register-page .asset-table tbody td {
        padding: .78rem .65rem;
        border-color: #edf0f4;
        vertical-align: middle;
    }

    .asset-register-page .asset-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .asset-register-page .asset-subtext {
        margin-top: 3px;
        font-size: 10.5px;
        line-height: 1.45;
    }

    .asset-register-page .asset-status-badge {
        display: inline-block;
        padding: .34rem .55rem;
        border-radius: 5px;
        font-size: 10.5px;
        font-weight: 600;
        white-space: nowrap;
    }

    .asset-register-page .asset-manage-btn {
        min-height: 30px;
        padding: .3rem .55rem;
        font-size: 11.5px;
    }

    .asset-register-page .asset-action-menu {
        min-width: 190px;
        padding: .35rem;
        border: 1px solid var(--asset-border);
        border-radius: 7px;
        font-size: 12px;
        z-index: 1050;
    }

    .asset-register-page .asset-action-menu .dropdown-item {
        padding: .5rem .6rem;
        border-radius: 5px;
    }

    .asset-register-page .asset-pagination {
        border-radius: 0 0 9px 9px;
    }

    .asset-register-page .pagination {
        flex-wrap: wrap;
        gap: 3px;
        margin-bottom: 0;
    }

    .asset-register-page .page-link {
        border-radius: 5px;
        font-size: 12px;
    }

    @media (max-width: 767.98px) {
        .asset-register-page {
            padding-left: .65rem !important;
            padding-right: .65rem !important;
        }

        .asset-register-page .asset-page-header {
            align-items: flex-start !important;
        }

        .asset-register-page .asset-header-actions {
            width: 100%;
        }

        .asset-register-page .asset-header-actions .btn {
            flex: 1 1 auto;
        }

        .asset-register-page .asset-metric-card .card-body {
            padding: .85rem;
        }

        .asset-register-page .asset-metric-icon {
            width: 36px;
            height: 36px;
            flex-basis: 36px;
            font-size: .9rem;
        }

        .asset-register-page .asset-content-card > .card-header {
            padding: .85rem;
        }

        .asset-register-page .asset-pagination {
            align-items: flex-start !important;
        }
    }

    @media (max-width: 380px) {
        .asset-register-page .asset-metric-card .card-body {
            padding: .7rem;
        }

        .asset-register-page .asset-metric-card .small {
            font-size: 10.5px;
        }

        .asset-register-page .asset-metric-icon {
            display: none;
        }
    }

    @media print {
        .admin-dark-sidebar,
        .admin-navbar,
        .breadcrumb,
        .asset-register-page .btn,
        .asset-register-page .dropdown,
        .asset-register-page .card-header form {
            display: none !important;
        }

        .asset-register-page,
        .asset-register-page .container-fluid {
            padding: 0 !important;
        }

        .asset-register-page .card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
            margin-bottom: 12px !important;
        }

        .asset-register-page .asset-table-wrap {
            overflow: visible !important;
        }

        .asset-register-page .asset-table {
            min-width: 0 !important;
            width: 100% !important;
            font-size: 9px !important;
        }

        .asset-register-page .asset-table th,
        .asset-register-page .asset-table td {
            border: 1px solid #dee2e6 !important;
            padding: 4px !important;
        }
    }
</style>
@endsection
