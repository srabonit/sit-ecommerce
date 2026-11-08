
@extends('layouts.admin')

@section('title', 'Branch Inventory Valuation & Stock Audit - ' . $branch->name)

@section('admin_content')
<style>
    /* =========================================================
       Branch Inventory - Kaiadmin Lite v1.2.0 Classic
    ========================================================= */

    #branchInventoryReportRoot {
        --inventory-primary: #1572e8;
        --inventory-border: #e5e7eb;
        --inventory-text: #2a2f36;
        --inventory-muted: #6b7280;
        --inventory-surface: #ffffff;
        color: var(--inventory-text);
        font-size: 13px;
    }

    #branchInventoryReportRoot .btn,
    #branchInventoryReportRoot .btn-sm,
    #branchInventoryReportRoot button,
    #branchInventoryReportRoot .badge,
    #branchInventoryReportRoot .form-control,
    #branchInventoryReportRoot .form-select,
    #branchInventoryReportRoot .input-group-text,
    #branchInventoryReportRoot .card,
    #branchInventoryReportRoot .alert,
    #branchInventoryReportRoot .table,
    #branchInventoryReportRoot .table th,
    #branchInventoryReportRoot .table td {
        border-radius: 5px !important;
    }

    #branchInventoryReportRoot .card {
        border-color: var(--inventory-border) !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .035);
    }

    #branchInventoryReportRoot .font-monospace,
    #branchInventoryReportRoot .bn-digit {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
        font-weight: 600 !important;
    }

    #branchInventoryReportRoot .btn-compact {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 32px;
        padding: 6px 11px !important;
        font-size: 11.5px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        white-space: nowrap;
        transition: background-color .15s ease, border-color .15s ease, color .15s ease;
    }

    /* Header */
    #branchInventoryReportRoot .inventory-header {
        gap: 12px;
        min-width: 0;
        border-color: var(--inventory-border) !important;
        border-radius: 5px;
    }

    #branchInventoryReportRoot .inventory-header-info,
    #branchInventoryReportRoot .inventory-header-actions {
        min-width: 0;
    }

    #branchInventoryReportRoot .inventory-header-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 6px;
    }

    /* KPI Cards */
    #branchInventoryReportRoot .inventory-kpi-card {
        height: 100%;
        min-height: 105px;
        padding: 13px 14px;
        overflow: hidden;
        background: var(--inventory-surface);
        border: 1px solid var(--inventory-border);
        border-radius: 5px;
        transition: transform .15s ease, box-shadow .15s ease;
    }

    #branchInventoryReportRoot .inventory-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, .055);
    }

    #branchInventoryReportRoot .inventory-kpi-card h5 {
        margin-top: 5px;
        font-size: clamp(14px, 1.3vw, 18px);
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    #branchInventoryReportRoot .inventory-kpi-card > div {
        gap: 5px;
    }

    #branchInventoryReportRoot .inventory-kpi-card small {
        line-height: 1.45;
    }

    /* Filter Form */
    #branchInventoryReportRoot .inventory-filter-card {
        padding: 14px !important;
    }

    #branchInventoryReportRoot .inventory-filter-form {
        row-gap: 10px;
    }

    #branchInventoryReportRoot .inventory-search-group {
        min-width: 0;
    }

    #branchInventoryReportRoot .inventory-search-group .input-group-text {
        min-width: 36px;
        justify-content: center;
    }

    #branchInventoryReportRoot .inventory-search-group .form-control {
        min-height: 34px;
        font-size: 11.5px;
    }

    #branchInventoryReportRoot .inventory-filter-form .form-check {
        display: flex;
        align-items: center;
        gap: 7px;
        min-height: 32px;
        padding-left: 1.8em;
    }

    #branchInventoryReportRoot .inventory-filter-form .form-check-input {
        flex-shrink: 0;
        margin-top: 0;
        cursor: pointer;
    }

    #branchInventoryReportRoot .inventory-filter-form .form-check-label {
        line-height: 1.4;
        cursor: pointer;
    }

    #branchInventoryReportRoot .inventory-filter-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
    }

    #branchInventoryReportRoot .form-control:focus,
    #branchInventoryReportRoot .form-select:focus {
        border-color: #80b5ff;
        box-shadow: 0 0 0 .15rem rgba(21, 114, 232, .12);
    }

    /* Inventory Table */
    #branchInventoryReportRoot .inventory-table-card {
        overflow: hidden;
    }

    #branchInventoryReportRoot .table-responsive {
        width: 100%;
        max-width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    #branchInventoryReportRoot .inventory-table {
        min-width: 1180px;
        margin-bottom: 0;
        border-color: #edf0f3;
        vertical-align: middle;
    }

    #branchInventoryReportRoot .inventory-table thead th {
        padding: 10px 9px;
        color: #5b6472;
        background: #f8fafc;
        border-bottom: 1px solid var(--inventory-border);
        font-size: 10.5px;
        font-weight: 700;
        white-space: nowrap;
        vertical-align: middle;
    }

    #branchInventoryReportRoot .inventory-table tbody td {
        padding: 9px;
        color: #4b5563;
        border-bottom-color: #edf0f3;
        vertical-align: middle;
    }

    #branchInventoryReportRoot .inventory-table.table-hover tbody tr:hover {
        background-color: #f8fafc;
    }

    #branchInventoryReportRoot .thumb-mini {
        width: 36px;
        height: 36px;
        flex: 0 0 36px;
        object-fit: cover;
        border: 1px solid #e2e8f0;
        border-radius: 5px;
        background: #f8fafc;
    }

    #branchInventoryReportRoot .inventory-product-info {
        min-width: 0;
    }

    #branchInventoryReportRoot .inventory-product-name {
        max-width: 220px;
        overflow-wrap: anywhere;
        line-height: 1.4;
    }

    #branchInventoryReportRoot .inventory-status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 22px;
        padding: 4px 8px;
        font-size: 9.5px;
        font-weight: 600;
        line-height: 1.2;
        white-space: nowrap;
    }

    #branchInventoryReportRoot .inventory-empty-state {
        padding: 28px 12px !important;
        color: var(--inventory-muted);
        text-align: center;
        line-height: 1.6;
    }

    /* Pagination */
    #branchInventoryReportRoot .inventory-pagination {
        gap: 10px;
        padding: 12px 8px 4px;
    }

    #branchInventoryReportRoot .inventory-pagination > small {
        line-height: 1.5;
    }

    #branchInventoryReportRoot .inventory-pagination nav {
        max-width: 100%;
    }

    #branchInventoryReportRoot .inventory-pagination .pagination {
        flex-wrap: wrap;
        gap: 3px;
        margin-bottom: 0;
    }

    #branchInventoryReportRoot .inventory-pagination .page-link {
        border-radius: 5px !important;
        font-size: 11px;
    }

    @media (max-width: 991.98px) {
        #branchInventoryReportRoot .inventory-header {
            align-items: flex-start !important;
        }

        #branchInventoryReportRoot .inventory-header-actions {
            justify-content: flex-start;
        }

        #branchInventoryReportRoot .inventory-kpi-card {
            min-height: 108px;
        }
    }

    @media (max-width: 767.98px) {
        #branchInventoryReportRoot {
            padding-right: 10px !important;
            padding-left: 10px !important;
        }

        #branchInventoryReportRoot .inventory-header {
            flex-direction: column;
            align-items: stretch !important;
            padding: 12px !important;
        }

        #branchInventoryReportRoot .inventory-header-info {
            gap: 7px !important;
        }

        #branchInventoryReportRoot .inventory-header-actions {
            width: 100%;
            justify-content: flex-start;
        }

        #branchInventoryReportRoot .inventory-filter-form > div {
            width: 100%;
        }

        #branchInventoryReportRoot .inventory-filter-actions {
            justify-content: flex-start;
        }

        #branchInventoryReportRoot .inventory-kpi-card {
            padding: 11px;
        }

        #branchInventoryReportRoot .inventory-kpi-card h5 {
            font-size: 14px;
        }

        #branchInventoryReportRoot .inventory-pagination {
            align-items: flex-start !important;
            flex-direction: column;
        }
    }

    @media (max-width: 420px) {
        #branchInventoryReportRoot .inventory-kpi-card {
            min-height: 108px;
            padding: 10px;
        }

        #branchInventoryReportRoot .inventory-kpi-card h5 {
            font-size: 13px;
        }

        #branchInventoryReportRoot .inventory-kpi-card > div {
            align-items: flex-start !important;
            flex-direction: column;
        }

        #branchInventoryReportRoot .inventory-header-actions .btn-compact {
            padding-right: 8px !important;
            padding-left: 8px !important;
            font-size: 10.5px !important;
        }

        #branchInventoryReportRoot .inventory-filter-card {
            padding: 11px !important;
        }
    }
</style>

<div class="container-fluid py-2 px-2 px-md-3" id="branchInventoryReportRoot">

    <!-- Branch Header -->
    <div class="inventory-header d-flex justify-content-between align-items-center mb-3 bg-white p-3 shadow-xs border">

        <div class="inventory-header-info d-flex align-items-center gap-2 flex-wrap">
            <span class="badge bg-warning text-dark fw-bold px-2 py-2" style="font-size: 11px;">
                <i class="fas fa-boxes-stacked me-1"></i> Stock Valuation
            </span>

            <span class="fw-bold text-dark fs-6">{{ $branch->name }}</span>

            <span class="badge bg-light text-dark border font-monospace" style="font-size: 10.5px;">
                Code: {{ $branch->code ?? $branch->branch_code }}
            </span>

            <span class="badge bg-light text-secondary border font-monospace" style="font-size: 10px;">
                Total SKUs: {{ number_format($summary->total_sku_count ?? 0) }}
            </span>
        </div>

        <div class="inventory-header-actions">

            @if(isset($selectableBranches) && $selectableBranches->count() > 1)
                <div class="dropdown d-inline-block">
                    <button class="btn btn-outline-primary btn-compact dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-store-alt me-1"></i> Switch Branch
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border" style="font-size: 11px;">
                        @foreach($selectableBranches as $sb)
                            <li>
                                <a class="dropdown-item py-2 {{ $sb->id == $branch->id ? 'active fw-bold' : '' }}"
                                   href="{{ route('admin.branch_report.inventory', array_merge(request()->except('branch_id'), ['branch_id' => $sb->id])) }}">
                                    {{ $sb->name }} ({{ $sb->code ?? $sb->branch_code }})
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <a href="{{ route('admin.branch_product.index', ['branch_id' => $branch->id]) }}"
               class="btn btn-outline-secondary btn-compact">
                <i class="fas fa-boxes me-1"></i> Product Catalog
            </a>

            <a href="{{ route('admin.branch_report.dashboard', ['branch_id' => $branch->id]) }}"
               class="btn btn-light border btn-compact">
                <i class="fas fa-chart-pie me-1"></i> Analytics
            </a>

            <a href="{{ route('admin.pos.terminal', $branch->id) }}"
               class="btn btn-warning text-white btn-compact">
                <i class="fas fa-desktop me-1"></i> POS Terminal
            </a>
        </div>
    </div>

    <!-- Inventory Valuation KPI Summary -->
    <div class="row g-2 mb-3">

        <!-- Physical Units -->
        <div class="col-6 col-md-3">
            <div class="inventory-kpi-card border-start border-primary border-3">
                <span class="text-muted d-block small" style="font-size: 10.5px;">
                    Physical Units on Hand
                </span>

                <h5 class="fw-bold text-dark mb-0 font-monospace bn-digit">
                    {{ number_format($summary->total_physical_units ?? 0) }} pcs
                </h5>

                <div class="d-flex justify-content-between align-items-center mt-2">
                    <small class="text-muted" style="font-size: 10px;">
                        Available: {{ number_format(max(0, ($summary->total_physical_units ?? 0) - ($summary->total_reserved_units ?? 0))) }} pcs
                    </small>

                    <small class="text-warning fw-semibold" style="font-size: 10px;">
                        Reserved: {{ number_format($summary->total_reserved_units ?? 0) }} pcs
                    </small>
                </div>
            </div>
        </div>

        <!-- Inventory Asset Value -->
        <div class="col-6 col-md-3">
            <div class="inventory-kpi-card border-start border-success border-3">
                <span class="text-muted d-block small" style="font-size: 10.5px;">
                    Asset Valuation (at Cost)
                </span>

                <h5 class="fw-bold text-success mb-0 font-monospace bn-digit">
                    ৳{{ number_format($summary->total_cost_value ?? 0, 2) }}
                </h5>

                <small class="text-muted d-block mt-2" style="font-size: 10px;">
                    Total procurement cost invested
                </small>
            </div>
        </div>

        <!-- Potential Retail Value -->
        <div class="col-6 col-md-3">
            <div class="inventory-kpi-card border-start border-info border-3">
                <span class="text-muted d-block small" style="font-size: 10.5px;">
                    Potential Retail Value (MRP)
                </span>

                <h5 class="fw-bold text-dark mb-0 font-monospace bn-digit">
                    ৳{{ number_format($summary->total_retail_value ?? 0, 2) }}
                </h5>

                <small class="text-info fw-semibold d-block mt-2" style="font-size: 10px;">
                    Expected sales turnover if sold
                </small>
            </div>
        </div>

        <!-- Potential Gross Margin -->
        <div class="col-6 col-md-3">
            <div class="inventory-kpi-card border-start border-warning border-3">
                @php
                    $costVal = (float) ($summary->total_cost_value ?? 0);
                    $retailVal = (float) ($summary->total_retail_value ?? 0);
                    $potentialMargin = max(0, $retailVal - $costVal);
                    $marginPercent = $retailVal > 0 ? round(($potentialMargin / $retailVal) * 100, 1) : 0;
                @endphp

                <span class="text-muted d-block small" style="font-size: 10.5px;">
                    Projected Gross Margin
                </span>

                <h5 class="fw-bold text-dark mb-0 font-monospace bn-digit">
                    ৳{{ number_format($potentialMargin, 2) }}
                </h5>

                <small class="text-warning fw-semibold d-block mt-2" style="font-size: 10px;">
                    Estimated Margin: {{ $marginPercent }}%
                </small>
            </div>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="card inventory-filter-card border shadow-xs bg-white mb-3">

        <form method="GET" action="{{ route('admin.branch_report.inventory') }}" class="row g-2 align-items-center inventory-filter-form">

            <input type="hidden" name="branch_id" value="{{ $branch->id }}">

            <!-- Keyword Search -->
            <div class="col-12 col-md-6">
                <div class="input-group input-group-sm inventory-search-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>

                    <input type="text" name="search" class="form-control border-start-0"
                           placeholder="Search product title, SKU, variant code..."
                           value="{{ request('search') }}" style="font-size: 11.5px;">

                    @if(request('search'))
                        <a href="{{ route('admin.branch_report.inventory', ['branch_id' => $branch->id]) }}"
                           class="btn btn-outline-light border text-muted"
                           title="Clear Search">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Low Stock Filter -->
            <div class="col-6 col-md-3">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" name="low_stock_only" value="1" id="chkLowStock"
                           {{ request('low_stock_only') ? 'checked' : '' }} onchange="this.form.submit()">

                    <label class="form-check-label fw-semibold text-dark" for="chkLowStock" style="font-size: 11.5px;">
                        <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                        Low Stock Items Only
                    </label>
                </div>
            </div>

            <!-- Filter Actions -->
            <div class="col-6 col-md-3">
                <div class="inventory-filter-actions">
                    <button type="submit" class="btn btn-primary btn-sm btn-compact px-3">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>

                    <a href="{{ route('admin.branch_report.inventory', ['branch_id' => $branch->id]) }}"
                       class="btn btn-light border btn-sm btn-compact px-3"
                       title="Reset Filters">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Inventory Stock Audit Table -->
    <div class="card inventory-table-card border shadow-xs bg-white p-2">

        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle inventory-table" style="font-size: 11.5px;">

                <thead class="table-light">
                    <tr>
                        <th class="py-2">Item / Product Name</th>
                        <th class="py-2">SKU / Code</th>
                        <th class="py-2">Category</th>
                        <th class="py-2 text-end">Cost (৳)</th>
                        <th class="py-2 text-end">MRP (৳)</th>
                        <th class="py-2 text-center">Physical</th>
                        <th class="py-2 text-center">Reserved</th>
                        <th class="py-2 text-center">Available</th>
                        <th class="py-2 text-center">Alert Limit</th>
                        <th class="py-2 text-center">Status</th>
                        <th class="py-2 text-end">Cost Valuation</th>
                        <th class="py-2 text-end">Retail Valuation</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($stocks as $stock)
                        @php
                            $prod = $stock->product;
                            $variant = $stock->variant;
                            $qty = (int) $stock->quantity;
                            $reserved = (int) ($stock->reserved_quantity ?? 0);
                            $avail = max(0, $qty - $reserved);
                            $alertQty = (int) ($stock->alert_quantity ?? 5);

                            $unitCost = $variant ? (float)($variant->purchase_cost ?? ($variant->purchase_price ?? 0)) : (float)($prod?->purchase_cost ?? ($prod?->purchase_price ?? 0.00));
                            $unitRetail = $variant ? (float)($variant->regular_price ?? ($variant->sale_price ?? 0)) : (float)($prod?->regular_price ?? ($prod?->sale_price ?? 0.00));

                            $lineCostVal = $qty * $unitCost;
                            $lineRetailVal = $qty * $unitRetail;

                            $isOutOfStock = $avail <= 0;
                            $isLowStock = !$isOutOfStock && $avail <= $alertQty;

                            $imgSrc = $variant && !empty($variant->image)
                                ? asset($variant->image)
                                : ($prod && !empty($prod->thumbnail) ? asset($prod->thumbnail) : 'https://placehold.co/40x40?text=No+Img');
                        @endphp

                        <tr>
                            <!-- Product Name & Variant -->
                            <td class="py-2">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $imgSrc }}"
                                         alt="{{ $prod?->name ?? ($prod?->title ?? 'Product') }}"
                                         class="thumb-mini flex-shrink-0"
                                         onerror="this.src='https://placehold.co/40x40?text=No+Img'">

                                    <div class="inventory-product-info">
                                        <div class="inventory-product-name fw-semibold text-dark">
                                            {{ $prod?->title ?? ($prod?->name ?? 'Product') }}
                                        </div>

                                        @if($variant)
                                            <span class="badge bg-light text-primary border font-monospace mt-1" style="font-size: 9px; padding: 2px 5px;">
                                                {{ $variant->color ?? '' }} {{ $variant->size ?? '' }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- SKU / Barcode -->
                            <td class="py-2 font-monospace bn-digit text-muted text-nowrap" style="font-size: 10.5px;">
                                {{ $stock->sku ?? ($variant?->sku ?? ($prod?->sku ?? ($prod?->product_code ?? '—'))) }}
                            </td>

                            <!-- Category -->
                            <td class="py-2">
                                <span class="badge bg-light text-dark border font-monospace" style="font-size: 9.5px;">
                                    {{ $prod?->category?->name ?? 'General' }}
                                </span>
                            </td>

                            <!-- Unit Procurement Cost -->
                            <td class="py-2 text-end font-monospace bn-digit text-muted text-nowrap">
                                ৳{{ number_format($unitCost, 2) }}
                            </td>

                            <!-- Unit Retail Selling Price -->
                            <td class="py-2 text-end font-monospace bn-digit fw-semibold text-dark text-nowrap">
                                ৳{{ number_format($unitRetail, 2) }}
                            </td>

                            <!-- Physical Stock -->
                            <td class="py-2 text-center font-monospace bn-digit fw-bold text-dark">
                                {{ $qty }}
                            </td>

                            <!-- Reserved Quantity -->
                            <td class="py-2 text-center font-monospace bn-digit {{ $reserved > 0 ? 'text-warning fw-bold' : 'text-muted' }}">
                                {{ $reserved }}
                            </td>

                            <!-- Available Quantity -->
                            <td class="py-2 text-center font-monospace bn-digit fw-bold fs-6 {{ $avail > 0 ? 'text-success' : 'text-danger' }}">
                                {{ $avail }}
                            </td>

                            <!-- Alert Threshold -->
                            <td class="py-2 text-center font-monospace bn-digit text-muted">
                                {{ $alertQty }}
                            </td>

                            <!-- Stock Status -->
                            <td class="py-2 text-center">
                                @if($isOutOfStock)
                                    <span class="badge bg-danger inventory-status-badge">
                                        Out of Stock
                                    </span>
                                @elseif($isLowStock)
                                    <span class="badge bg-warning text-dark inventory-status-badge">
                                        <i class="fas fa-exclamation-triangle me-1"></i> Low Stock
                                    </span>
                                @else
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 inventory-status-badge">
                                        Optimal
                                    </span>
                                @endif
                            </td>

                            <!-- Cost Valuation -->
                            <td class="py-2 text-end font-monospace bn-digit fw-bold text-dark text-nowrap">
                                ৳{{ number_format($lineCostVal, 2) }}
                            </td>

                            <!-- Retail Valuation -->
                            <td class="py-2 text-end font-monospace bn-digit text-primary fw-bold text-nowrap">
                                ৳{{ number_format($lineRetailVal, 2) }}
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="12" class="inventory-empty-state">
                                <i class="fas fa-boxes-stacked fa-2x d-block mb-2 text-secondary opacity-50"></i>
                                <span class="fw-semibold d-block text-dark">No Inventory Records Found</span>
                                <small class="text-muted">
                                    No stock records match the selected branch and filters.
                                </small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="inventory-pagination d-flex justify-content-between align-items-center flex-wrap">

            <small class="text-muted" style="font-size: 11px;">
                Showing {{ $stocks->firstItem() ?? 0 }} to {{ $stocks->lastItem() ?? 0 }} of {{ $stocks->total() }} stock records
            </small>

            <div>
                {{ $stocks->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
