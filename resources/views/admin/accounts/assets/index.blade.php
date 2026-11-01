@extends('layouts.admin')

@section('title', 'Fixed Assets & Equipment Register - ' . config('app.name'))

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #assets-root .btn,
    #assets-root .btn-sm,
    #assets-root button,
    #assets-root .badge,
    #assets-root .alert,
    #assets-root .form-control,
    #assets-root .form-select,
    #assets-root .page-link,
    #assets-root .page-item span,
    #assets-root .card,
    #assets-root .asset-card,
    #assets-root .kpi-stat-card,
    .asset-modal-scope .modal-content,
    .asset-modal-scope .btn,
    .asset-modal-scope .form-control,
    .asset-modal-scope .form-select,
    .asset-modal-scope .badge {
        border-radius: 5px !important;
    }

    #assets-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #assets-root .input-group > .form-control,
    #assets-root .input-group > .form-select {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Safe Monospace & Bengali Digit Typography */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Compact Sleek Button Styling */
    .btn-compact {
        font-size: 11.5px !important;
        padding: 5px 12px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    /* Original Theme Card Styling */
    .asset-card {
        border: 1px solid #e2e8f0;
        background: #ffffff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 16px;
    }

    /* Original KPI Stat Cards */
    .kpi-stat-card {
        padding: 14px 16px;
        position: relative;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        height: 100%;
    }
    .kpi-stat-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3.5px;
    }
    .kpi-valuation::before    { background-color: #0d9488; }
    .kpi-total-count::before  { background-color: #0284c7; }
    .kpi-period-cost::before  { background-color: #f59e0b; }
    .kpi-period-count::before { background-color: #6366f1; }

    /* Table Architecture */
    .asset-table {
        width: 100%;
        margin-bottom: 0;
    }
    .asset-table thead th {
        background-color: #f8fafc;
        color: #475569;
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 8px 6px;
        white-space: nowrap;
    }
    .asset-table tbody td {
        padding: 8px 6px;
        vertical-align: middle;
        font-size: 11.5px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        white-space: nowrap;
    }
    .asset-table tbody tr:hover {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .asset-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .asset-table th,
        .asset-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 10 Columns Proportional Distribution Summing to Exactly 100% */
        .col-ast-id       { width: 4% !important; }
        .col-ast-code     { width: 10% !important; }
        .col-ast-name     { width: 22% !important; }
        .col-ast-type     { width: 11% !important; }
        .col-ast-date     { width: 9% !important; }
        .col-ast-warranty { width: 10% !important; }
        .col-ast-account  { width: 12% !important; }
        .col-ast-cost     { width: 10% !important; }
        .col-ast-receipt  { width: 6% !important; }
        .col-ast-action   { width: 6% !important; }
    }

    /* Form Controls */
    .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #assets-root .form-control,
    #assets-root .form-select,
    .asset-modal-scope .form-control,
    .asset-modal-scope .form-select {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        box-shadow: none;
        transition: border-color 0.15s ease;
    }
    #assets-root .form-control:focus,
    #assets-root .form-select:focus,
    .asset-modal-scope .form-control:focus,
    .asset-modal-scope .form-select:focus {
        border-color: #0d9488;
        box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.12);
        outline: none;
    }

    /* Bulletproof Responsive Pagination */
    .card-footer nav {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        width: auto !important;
        margin: 0 !important;
    }
    .card-footer nav > div.d-sm-none,
    .card-footer nav > .d-sm-none {
        display: none !important;
    }
    .card-footer nav > div.d-none,
    .card-footer nav > .d-none.flex-sm-fill {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    .card-footer nav p,
    .card-footer nav .small.text-muted,
    .card-footer nav div:has(> p),
    .card-footer nav > div.d-none > div:first-child,
    .card-footer nav .d-none.flex-sm-fill > div:first-child {
        display: none !important;
    }
    .card-footer .pagination {
        margin: 0 !important;
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 3px !important;
        padding: 0 !important;
        list-style: none !important;
    }
    .card-footer .page-item {
        display: inline-flex !important;
    }
    .card-footer .page-item .page-link,
    .card-footer .page-item span {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 25px !important;
        height: 25px !important;
        padding: 0 6px !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        color: #475569 !important;
        background-color: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 5px !important;
        text-decoration: none !important;
        line-height: 1 !important;
        transition: all 0.12s ease !important;
    }
    .card-footer .page-item .page-link:hover {
        background-color: #f1f5f9 !important;
        border-color: #94a3b8 !important;
        color: #0f172a !important;
    }
    .card-footer .page-item.active .page-link,
    .card-footer .page-item.active span {
        background-color: #0d9488 !important;
        border-color: #0d9488 !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        box-shadow: 0 1px 3px rgba(13, 148, 136, 0.25) !important;
    }
    .card-footer .page-item.disabled .page-link,
    .card-footer .page-item.disabled span {
        opacity: 0.45 !important;
        background-color: #f8fafc !important;
        border-color: #e2e8f0 !important;
        cursor: not-allowed !important;
    }
    .card-footer nav svg {
        width: 10px !important;
        height: 10px !important;
    }

    /* Print Optimization */
    @media print {
        @page { size: A4 landscape; margin: 8mm; }
        body { background: #ffffff !important; color: #000000 !important; }
        #assets-root .no-print, .admin-dark-sidebar, .admin-header, .main-footer { display: none !important; }
        #assets-root { padding: 0 !important; margin: 0 !important; width: 100% !important; }
        .asset-card { border: 1px solid #000000 !important; box-shadow: none !important; margin-bottom: 10px !important; }
    }

    /* Mobile Responsive Optimizations (< 768px) */
    @media (max-width: 767.98px) {
        .header-top-wrap {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 8px !important;
        }
        .top-action-btns {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .top-action-btns .btn {
            width: 100%;
            justify-content: center;
        }
        .pc-fit-container {
            width: 100%;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }
        .asset-table {
            min-width: 820px !important;
            table-layout: auto !important;
        }
        .card-footer {
            flex-direction: column !important;
            gap: 8px !important;
            justify-content: center !important;
        }
        .card-footer nav {
            width: 100% !important;
            justify-content: center !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="assets-root">

    <!-- 1. Header (Original Clean White/Light Theme) -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3 header-top-wrap no-print">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-boxes-stacked" style="color: #0d9488;"></i>
                <span>Fixed Assets &amp; Equipment Register</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Track company property, electronics, office furniture, machinery, and equipment valuation
            </small>
        </div>
        <div class="top-action-btns d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-dark btn-compact shadow-xs" onclick="window.print()">
                <i class="fas fa-print me-1"></i> <span>Print A4</span>
            </button>
            <button type="button" class="btn btn-sm btn-primary btn-compact shadow-xs fw-bold" data-bs-toggle="modal" data-bs-target="#addAssetModal" style="background-color: #0d9488; border-color: #0d9488;">
                <i class="fas fa-plus-circle me-1"></i> <span>Add New Asset</span>
            </button>
            <a href="{{ url('/admin/accounts/assets/export-csv') }}" class="btn btn-sm btn-outline-success btn-compact shadow-xs">
                <i class="fas fa-file-csv me-1"></i> <span>Export CSV</span>
            </a>
            <a href="{{ url('/admin/accounts/bank-balance') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-landmark me-1"></i> <span>Bank Balances</span>
            </a>
            <a href="{{ route('admin.report.balance_sheet') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-scale-balanced me-1"></i> <span>Balance Sheet</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-xs py-2 px-3 mb-3 small d-flex align-items-center justify-content-between no-print" style="border-radius: 5px;">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-xs py-2 px-3 mb-3 small d-flex align-items-center justify-content-between no-print" style="border-radius: 5px;">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-triangle-exclamation text-danger"></i>
                <span class="fw-semibold text-dark">{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- 2. KPI Metric Summary Cards (Original Clean Theme) -->
    <div class="row g-2 mb-3">
        <!-- Total Asset Valuation -->
        <div class="col-6 col-lg-3">
            <div class="kpi-stat-card kpi-valuation shadow-xs">
                <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Total Assets Valuation</span>
                <h5 class="fw-bold mb-0 font-monospace bn-digit" style="color: #0d9488;">৳ {{ number_format($totalValuation, 2) }}</h5>
                <small class="text-muted" style="font-size: 10px;">Cumulative company asset value</small>
            </div>
        </div>

        <!-- Total Assets Count -->
        <div class="col-6 col-lg-3">
            <div class="kpi-stat-card kpi-total-count shadow-xs">
                <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Total Fixed Assets</span>
                <h5 class="fw-bold text-primary mb-0 font-monospace bn-digit">{{ $totalAssetCount }} Items</h5>
                <small class="text-muted" style="font-size: 10px;">Registered physical items</small>
            </div>
        </div>

        <!-- Period Additions Cost -->
        <div class="col-6 col-lg-3">
            <div class="kpi-stat-card kpi-period-cost shadow-xs">
                <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Period Acquisitions</span>
                <h5 class="fw-bold text-warning mb-0 font-monospace bn-digit">৳ {{ number_format($periodAdditions, 2) }}</h5>
                <small class="text-muted font-monospace" style="font-size: 10px;">{{ $start->format('d M Y') }} — {{ $end->format('d M Y') }}</small>
            </div>
        </div>

        <!-- Period Items Count -->
        <div class="col-6 col-lg-3">
            <div class="kpi-stat-card kpi-period-count shadow-xs">
                <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Period Purchased</span>
                <h5 class="fw-bold mb-0 font-monospace bn-digit" style="color: #6366f1;">{{ $periodCount }} Assets</h5>
                <small class="text-muted" style="font-size: 10px;">Purchased in selected period</small>
            </div>
        </div>
    </div>

    <!-- 3. Filter Bar (Clean & Symmetric) -->
    <div class="card border-0 shadow-sm asset-card mb-3 p-3 no-print">
        <form action="{{ url('/admin/accounts/assets') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label">Reporting Period</label>
                <select name="date_filter" id="assetDateFilter" class="form-select font-monospace" onchange="toggleCustomAssetDates(this.value)">
                    <option value="all" {{ $dateFilter === 'all' ? 'selected' : '' }}>All Time</option>
                    <option value="today" {{ $dateFilter === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="yesterday" {{ $dateFilter === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                    <option value="this_week" {{ $dateFilter === 'this_week' ? 'selected' : '' }}>This Week</option>
                    <option value="this_month" {{ $dateFilter === 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="last_month" {{ $dateFilter === 'last_month' ? 'selected' : '' }}>Last Month</option>
                    <option value="this_year" {{ $dateFilter === 'this_year' ? 'selected' : '' }}>This Year</option>
                    <option value="custom" {{ $dateFilter === 'custom' ? 'selected' : '' }}>Custom Date Range</option>
                </select>
            </div>

            <div class="col-6 col-md-2 {{ $dateFilter === 'custom' ? '' : 'd-none' }}" id="assetStartCol">
                <label class="form-label">Start</label>
                <input type="date" name="start_date" class="form-control font-monospace bn-digit" value="{{ request('start_date', $start->format('Y-m-d')) }}">
            </div>

            <div class="col-6 col-md-2 {{ $dateFilter === 'custom' ? '' : 'd-none' }}" id="assetEndCol">
                <label class="form-label">End</label>
                <input type="date" name="end_date" class="form-control font-monospace bn-digit" value="{{ request('end_date', $end->format('Y-m-d')) }}">
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label">Asset Category</label>
                <select name="asset_type" class="form-select font-monospace">
                    <option value="all">All Categories</option>
                    <option value="electronics" {{ request('asset_type') === 'electronics' ? 'selected' : '' }}>Electronics &amp; IT</option>
                    <option value="furniture" {{ request('asset_type') === 'furniture' ? 'selected' : '' }}>Office Furniture</option>
                    <option value="vehicle" {{ request('asset_type') === 'vehicle' ? 'selected' : '' }}>Vehicles &amp; Bikes</option>
                    <option value="office_equipment" {{ request('asset_type') === 'office_equipment' ? 'selected' : '' }}>Office Equipment</option>
                    <option value="machinery" {{ request('asset_type') === 'machinery' ? 'selected' : '' }}>Machinery &amp; Tools</option>
                    <option value="others" {{ request('asset_type') === 'others' ? 'selected' : '' }}>Others</option>
                </select>
            </div>

            <div class="col-12 col-md-3">
                <label class="form-label">Search Asset / Tag / Note</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Asset name, code...">
            </div>

            <div class="col-12 col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-dark btn-compact flex-grow-1 shadow-xs fw-bold">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                @if(request()->anyFilled(['date_filter', 'asset_type', 'search', 'start_date', 'end_date']))
                    <a href="{{ url('/admin/accounts/assets') }}" class="btn btn-light border btn-compact shadow-xs">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- 4. Assets Ledger Table Card -->
    <div class="card border-0 shadow-sm asset-card overflow-hidden">
        <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13.5px;">
                <i class="fas fa-boxes-stacked" style="color: #0d9488;"></i> <span>Fixed Asset Inventory Ledger</span>
            </h6>
            <span class="badge bg-light text-secondary border font-monospace px-2 py-0.5" style="font-size: 10.5px;">
                Showing {{ $assets->firstItem() ?? 0 }} - {{ $assets->lastItem() ?? 0 }} of {{ $assets->total() }} entries
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive pc-fit-container">
                <table class="table align-middle asset-table mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3 col-ast-id text-center">#</th>
                            <th class="col-ast-code">Asset Tag / Code</th>
                            <th class="col-ast-name">Asset Name &amp; Particulars</th>
                            <th class="col-ast-type">Category / Type</th>
                            <th class="col-ast-date">Purchase Date</th>
                            <th class="col-ast-warranty">Warranty Expiry</th>
                            <th class="col-ast-account">Paid Account</th>
                            <th class="text-end col-ast-cost">Cost (৳)</th>
                            <th class="text-center col-ast-receipt no-print">Receipt</th>
                            <th class="text-end pe-3 col-ast-action no-print">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assets as $idx => $ast)
                            <tr id="asset-row-{{ $ast->id }}">
                                <td class="ps-3 col-ast-id text-center font-monospace bn-digit text-muted" style="font-size: 10.5px;">
                                    {{ $assets->firstItem() + $idx }}
                                </td>
                                <td class="col-ast-code">
                                    <span class="badge bg-light text-dark border font-monospace text-truncate d-inline-block px-1.5 py-0" style="font-size: 10px; max-width: 100%;">
                                        {{ $ast->asset_code ?? 'AST-' . $ast->id }}
                                    </span>
                                </td>
                                <td class="col-ast-name">
                                    <div class="min-w-0">
                                        <strong class="text-dark d-block text-truncate" style="font-size: 12px; line-height: 1.3;" title="{{ $ast->name }}">
                                            {{ $ast->name }}
                                        </strong>
                                        @if(!empty($ast->note))
                                            <small class="text-muted d-block text-truncate font-monospace" style="font-size: 9.5px;" title="{{ $ast->note }}">
                                                {{ $ast->note }}
                                            </small>
                                        @endif
                                    </div>
                                </td>
                                <td class="col-ast-type">
                                    <span class="badge bg-secondary-subtle text-secondary border font-monospace text-truncate d-inline-block px-1.5 py-0" style="font-size: 9px; max-width: 100%;">
                                        {{ strtoupper(str_replace('_', ' ', $ast->asset_type)) }}
                                    </span>
                                </td>
                                <td class="col-ast-date font-monospace bn-digit text-muted" style="font-size: 10.5px;">
                                    {{ date('d M Y', strtotime($ast->purchase_date)) }}
                                </td>
                                <td class="col-ast-warranty font-monospace bn-digit" style="font-size: 10.5px;">
                                    @if(!empty($ast->warranty_expiry))
                                        @php
                                            $isExpired = strtotime($ast->warranty_expiry) < time();
                                        @endphp
                                        <span class="{{ $isExpired ? 'text-danger' : 'text-success' }}" title="{{ $isExpired ? 'Expired' : 'Valid' }}">
                                            {{ date('d M Y', strtotime($ast->warranty_expiry)) }}
                                        </span>
                                    @else
                                        <span class="text-muted font-monospace">—</span>
                                    @endif
                                </td>
                                <td class="col-ast-account">
                                    <span class="badge bg-light text-primary border font-monospace px-1.5 py-0 text-truncate d-inline-block" style="font-size: 9.5px; max-width: 100%;" title="{{ $ast->bank_account_name ?? $ast->payment_method }}">
                                        {{ $ast->bank_account_name ?? $ast->payment_method }}
                                    </span>
                                </td>
                                <td class="text-end col-ast-cost font-monospace bn-digit fw-bold text-dark" style="font-size: 11.5px;">
                                    ৳ {{ number_format((float)$ast->purchase_cost, 2) }}
                                </td>
                                <td class="text-center col-ast-receipt no-print">
                                    @if(!empty($ast->voucher_receipt))
                                        <a href="{{ asset($ast->voucher_receipt) }}" target="_blank" class="btn btn-light border btn-compact py-0 px-1 text-secondary shadow-xs" style="height: 22px; font-size: 10px !important;" title="View Document / Memo">
                                            <i class="fas fa-file-invoice"></i>
                                        </a>
                                    @else
                                        <span class="text-muted font-monospace" style="font-size: 10px;">—</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3 col-ast-action no-print">
                                    <button type="button" class="btn btn-light border text-danger p-0 d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 25px; height: 25px; font-size: 10px;" onclick="deleteAsset({{ $ast->id }})" title="Delete Asset Record">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted small">
                                    <i class="fas fa-boxes-stacked fa-2x mb-2 d-block opacity-25"></i>
                                    <span class="fw-semibold">No fixed assets registered for this period.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($assets->hasPages())
            <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50 card-footer no-print">
                <small class="text-muted bn-digit font-monospace" style="font-size: 11px;">
                    Showing {{ $assets->firstItem() }} to {{ $assets->lastItem() }} of {{ $assets->total() }} entries
                </small>
                <div>
                    {{ $assets->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Modal: Add New Fixed Asset -->
<div class="modal fade asset-modal-scope" id="addAssetModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold d-flex align-items-center gap-1 mb-0" style="font-size: 13.5px;">
                    <i class="fas fa-plus-circle" style="color: #0d9488;"></i> <span>Register Fixed Asset / Equipment</span>
                </h6>
                <button type="button" class="btn-close btn-close-white btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ url('/admin/accounts/assets/store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-3">
                    <div class="row g-2">
                        <!-- Asset Name -->
                        <div class="col-12">
                            <label class="form-label">
                                Asset Name / Item Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" class="form-control" placeholder="Dell Core i5 Office PC, Epson Thermal Printer, Executive Chair" required>
                        </div>

                        <!-- Category / Type -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Asset Category <span class="text-danger">*</span>
                            </label>
                            <select name="asset_type" class="form-select font-monospace" required>
                                <option value="electronics" selected>Electronics &amp; Computers</option>
                                <option value="furniture">Furniture &amp; Fixtures</option>
                                <option value="vehicle">Vehicles &amp; Delivery Bikes</option>
                                <option value="office_equipment">Office Equipment / AC</option>
                                <option value="machinery">Machinery &amp; Tools</option>
                                <option value="others">Other Property</option>
                            </select>
                        </div>

                        <!-- Purchase Cost -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Purchase Cost (৳) <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="purchase_cost" class="form-control font-monospace bn-digit text-end fw-bold" placeholder="0.00" min="1" step="any" required>
                        </div>

                        <!-- Purchase Date -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Purchase Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="purchase_date" class="form-control font-monospace bn-digit" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <!-- Warranty Expiry -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">Warranty Expiry Date (Optional)</label>
                            <input type="date" name="warranty_expiry" class="form-control font-monospace bn-digit">
                        </div>

                        <!-- Deduct From Account -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Paid From Account <span class="text-danger">*</span>
                            </label>
                            <select name="bank_account_id" id="assetBankAccountId" class="form-select font-monospace" onchange="handleAssetAccountChange(this)" required>
                                @if(isset($bankAccounts) && $bankAccounts->isNotEmpty())
                                    @foreach($bankAccounts as $acc)
                                        <option value="{{ $acc->id }}" data-type="{{ $acc->account_type }}" {{ $acc->account_type === 'bank' ? 'selected' : '' }}>
                                            {{ $acc->account_name }} (৳{{ number_format((float)$acc->current_balance, 2) }})
                                        </option>
                                    @endforeach
                                @else
                                    <option value="">No Accounts Available</option>
                                @endif
                            </select>
                        </div>

                        <!-- Payment Method -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" id="assetPaymentMethod" class="form-select font-monospace">
                                <option value="Bank Transfer" selected>Bank Transfer</option>
                                <option value="Cash">Cash Handover</option>
                                <option value="Cheque">Bank Cheque</option>
                                <option value="bKash">bKash</option>
                                <option value="Nagad">Nagad</option>
                            </select>
                        </div>

                        <!-- Tag / Code -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">Asset Code / Serial #</label>
                            <input type="text" name="asset_code" class="form-control font-monospace" placeholder="PC-001, SN-987293">
                        </div>

                        <!-- Attachment -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">Attach Invoice / Warranty Slip</label>
                            <input type="file" name="receipt" class="form-control" accept="image/*,application/pdf">
                        </div>

                        <!-- Note -->
                        <div class="col-12">
                            <label class="form-label">Notes &amp; Location Specifications</label>
                            <textarea name="note" rows="2" class="form-control" placeholder="Location, custodian, configuration..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark btn-compact shadow-xs fw-bold px-3" style="background-color: #0d9488; border-color: #0d9488;">
                        <i class="fas fa-check-circle me-1"></i> Register Asset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleCustomAssetDates(val) {
    const s = document.getElementById('assetStartCol');
    const e = document.getElementById('assetEndCol');
    if (val === 'custom') {
        s.classList.remove('d-none');
        e.classList.remove('d-none');
    } else {
        s.classList.add('d-none');
        e.classList.add('d-none');
    }
}

function handleAssetAccountChange(select) {
    const selectedOption = select.options[select.selectedIndex];
    const type = selectedOption ? selectedOption.getAttribute('data-type') : null;
    const methodSelect = document.getElementById('assetPaymentMethod');
    if (methodSelect && type) {
        if (type === 'cash') methodSelect.value = 'Cash';
        else if (type === 'mobile_banking') methodSelect.value = 'bKash';
        else if (type === 'bank') methodSelect.value = 'Bank Transfer';
    }
}

function deleteAsset(id) {
    if (!confirm('Are you sure you want to permanently delete this fixed asset record? The deducted balance will be refunded to the account.')) {
        return;
    }

    const url = "{{ url('/admin/accounts/assets/delete') }}/" + id;

    fetch(url, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const row = document.getElementById('asset-row-' + id);
            if (row) row.remove();
        } else {
            alert('Failed to delete asset.');
        }
    })
    .catch(err => console.error(err));
}
</script>
@endsection