@extends('layouts.admin')

@section('title', 'Cost of Goods Sold (COGS) Analysis - ' . config('app.name'))

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #cogs-report-root .btn,
    #cogs-report-root .btn-sm,
    #cogs-report-root button,
    #cogs-report-root .badge,
    #cogs-report-root .alert,
    #cogs-report-root .form-control,
    #cogs-report-root .input-group-text,
    #cogs-report-root .page-link,
    #cogs-report-root .page-item span,
    #cogs-report-root .card,
    #cogs-report-root .cogs-card,
    #cogs-report-root .collapse-toggle-btn {
        border-radius: 5px !important;
    }

    #cogs-report-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #cogs-report-root .input-group > .form-control {
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
    .cogs-card {
        border: 1px solid #e2e8f0;
        background: #ffffff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 16px;
    }
    .cogs-card-header {
        padding: 10px 14px;
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
    }

    /* Collapsible Trigger Buttons & Rotating Chevron */
    .collapse-toggle-btn {
        width: 24px;
        height: 24px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        color: #475569;
        transition: all 0.2s ease;
    }
    .collapse-toggle-btn:hover {
        background-color: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
    }
    .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }
    .collapse-icon {
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        font-size: 10px;
    }

    /* Table Architecture: 100% Fit Screen on PC with Zero Horizontal Scroll */
    .cogs-table {
        width: 100%;
        margin-bottom: 0;
    }
    .cogs-table thead th {
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
    .cogs-table tbody td {
        padding: 8px 6px;
        vertical-align: middle;
        font-size: 11.5px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        white-space: nowrap;
    }
    .cogs-table tbody tr:hover {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .cogs-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .cogs-table th,
        .cogs-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 9 Columns Proportional Distribution Summing to Exactly 100% */
        .col-cg-id     { width: 4% !important; }
        .col-cg-desc   { width: 24% !important; }
        .col-cg-units  { width: 9% !important; }
        .col-cg-cost   { width: 10% !important; }
        .col-cg-price  { width: 10% !important; }
        .col-cg-sales  { width: 13% !important; }
        .col-cg-cogs   { width: 12% !important; }
        .col-cg-profit { width: 12% !important; }
        .col-cg-margin { width: 6% !important; }
    }

    /* Form Controls */
    #cogs-report-root .form-control {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        box-shadow: none;
        transition: border-color 0.15s ease;
    }
    #cogs-report-root .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
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
        background-color: #03172e !important;
        border-color: #03172e !important;
        color: #ffffff !important;
        font-weight: 700 !important;
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

    /* Print Styles */
    @media print {
        @page { size: A4 landscape; margin: 8mm; }
        body { background: #ffffff !important; color: #000000 !important; }
        #cogs-report-root .no-print, .admin-dark-sidebar, .admin-header, .main-footer { display: none !important; }
        #cogs-report-root { padding: 0 !important; margin: 0 !important; width: 100% !important; }
        .cogs-card { border: 1px solid #000000 !important; box-shadow: none !important; page-break-inside: avoid; margin-bottom: 10px !important; }
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
        .cogs-table {
            min-width: 760px !important;
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

<div class="container-fluid py-3 px-2 px-md-4" id="cogs-report-root">

    <!-- 1. Header (Original Clean Light Theme) -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3 header-top-wrap no-print">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-boxes-stacked text-warning"></i>
                <span>Cost of Goods Sold (COGS) Analysis</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Itemized SKU-level cost vs. sales revenue &amp; net gross margin breakdown
            </small>
        </div>
        <div class="top-action-btns d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-dark btn-compact shadow-xs fw-semibold" onclick="window.print()">
                <i class="fas fa-print me-1"></i> <span>Print A4 Statement</span>
            </button>
            <a href="{{ route('admin.report.profit_loss') }}" class="btn btn-sm btn-outline-primary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-file-invoice-dollar me-1"></i> <span>Profit &amp; Loss</span>
            </a>
            <a href="{{ route('admin.report.balance_sheet') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-scale-balanced me-1"></i> <span>Balance Sheet</span>
            </a>
            <a href="{{ route('admin.report.cash_flow') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-money-bill-trend-up me-1 text-primary"></i> <span>Cash Flow</span>
            </a>
        </div>
    </div>

    <!-- 2. Search & Filter Card (Collapsible) -->
    <div class="card border-0 shadow-sm cogs-card mb-3 no-print">
        <div class="cogs-card-header d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark small" style="font-size: 12.5px;">
                <i class="fas fa-filter text-primary me-1"></i> Search &amp; Filter Products
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseCogsSearch" aria-expanded="true" aria-controls="collapseCogsSearch" title="Toggle Search">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseCogsSearch">
            <div class="p-3">
                <form action="{{ route('admin.report.cogs') }}" method="GET" class="row g-2 align-items-center">
                    <div class="col-12 col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" placeholder="Search product name or SKU..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-12 col-md-auto d-flex gap-1">
                        <button type="submit" class="btn btn-dark btn-compact shadow-xs fw-bold px-3">
                            <i class="fas fa-filter me-1"></i> Search
                        </button>
                        @if(request('search'))
                            <a href="{{ route('admin.report.cogs') }}" class="btn btn-light border btn-compact shadow-xs">Reset</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 3. COGS Table Card (Collapsible) -->
    <div class="card border-0 shadow-sm cogs-card overflow-hidden">
        <div class="cogs-card-header d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13.5px;">
                <i class="fas fa-table-list text-primary"></i> <span>Delivered Products Profitability Breakdown</span>
            </h6>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-secondary border font-monospace px-2 py-0.5" style="font-size: 10.5px;">
                    Total Items: {{ $items->total() }}
                </span>
                <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseCogsTable" aria-expanded="true" aria-controls="collapseCogsTable" title="Toggle Table">
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </button>
            </div>
        </div>
        <div class="collapse show" id="collapseCogsTable">
            <div class="card-body p-0">
                <div class="table-responsive pc-fit-container">
                    <table class="table align-middle cogs-table mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3 col-cg-id text-center">#</th>
                                <th class="col-cg-desc">Product / SKU Description</th>
                                <th class="text-center col-cg-units">Units Sold</th>
                                <th class="text-end col-cg-cost">Avg Cost (৳)</th>
                                <th class="text-end col-cg-price">Avg Price (৳)</th>
                                <th class="text-end col-cg-sales">Total Sales (৳)</th>
                                <th class="text-end col-cg-cogs">Total COGS (৳)</th>
                                <th class="text-end col-cg-profit">Gross Profit (৳)</th>
                                <th class="text-end pe-3 col-cg-margin">Margin %</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $pageTotalUnits = 0;
                                $pageTotalSales = 0.00;
                                $pageTotalCogs  = 0.00;
                            @endphp
                            @forelse($items as $idx => $row)
                                @php
                                    $sales = (float) $row->total_sales;
                                    $cost = (float) $row->total_cogs;
                                    $profit = max(0.00, $sales - $cost);
                                    $margin = $sales > 0 ? round(($profit / $sales) * 100, 1) : 0;

                                    $pageTotalUnits += (int)$row->units_sold;
                                    $pageTotalSales += $sales;
                                    $pageTotalCogs  += $cost;
                                @endphp
                                <tr>
                                    <td class="ps-3 col-cg-id text-center font-monospace bn-digit text-muted" style="font-size: 10.5px;">
                                        {{ $items->firstItem() + $idx }}
                                    </td>
                                    <td class="col-cg-desc">
                                        <div class="min-w-0">
                                            <strong class="text-dark d-block text-truncate" style="font-size: 12px; line-height: 1.3;" title="{{ $row->product_title ?? 'Product #' . $row->product_id }}">
                                                {{ $row->product_title ?? 'Product #' . $row->product_id }}
                                            </strong>
                                            <small class="text-muted font-monospace d-block text-truncate" style="font-size: 9.5px;">
                                                SKU: {{ $row->sku ?? 'N/A' }}
                                            </small>
                                        </div>
                                    </td>
                                    <td class="text-center col-cg-units font-monospace bn-digit" style="font-size: 11px;">
                                        &times; {{ $row->units_sold }}
                                    </td>
                                    <td class="text-end col-cg-cost font-monospace bn-digit text-muted" style="font-size: 11.5px;">
                                        ৳ {{ number_format((float)$row->avg_cost, 2) }}
                                    </td>
                                    <td class="text-end col-cg-price font-monospace bn-digit text-dark" style="font-size: 11.5px;">
                                        ৳ {{ number_format((float)$row->avg_price, 2) }}
                                    </td>
                                    <td class="text-end col-cg-sales font-monospace bn-digit text-primary fw-bold" style="font-size: 11.5px;">
                                        ৳ {{ number_format($sales, 2) }}
                                    </td>
                                    <td class="text-end col-cg-cogs font-monospace bn-digit text-secondary" style="font-size: 11.5px;">
                                        ৳ {{ number_format($cost, 2) }}
                                    </td>
                                    <td class="text-end col-cg-profit font-monospace bn-digit text-success fw-bold" style="font-size: 11.5px;">
                                        ৳ {{ number_format($profit, 2) }}
                                    </td>
                                    <td class="text-end pe-3 col-cg-margin">
                                        <span class="badge {{ $margin >= 25 ? 'bg-success-subtle text-success border border-success-subtle' : ($margin >= 10 ? 'bg-warning-subtle text-dark border border-warning-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle') }} font-monospace px-1 py-0" style="font-size: 9.5px;">
                                            {{ $margin }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted small">No delivered order items found for COGS analysis.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($items->isNotEmpty())
                            @php
                                $pageTotalProfit = max(0.00, $pageTotalSales - $pageTotalCogs);
                                $pageOverallMargin = $pageTotalSales > 0 ? round(($pageTotalProfit / $pageTotalSales) * 100, 1) : 0;
                            @endphp
                            <tfoot>
                                <tr class="bg-light fw-bold" style="font-size: 11.5px;">
                                    <td colspan="2" class="ps-3 text-secondary">Page Total ({{ $items->count() }} Products):</td>
                                    <td class="text-center font-monospace bn-digit">&times; {{ $pageTotalUnits }}</td>
                                    <td colspan="2" class="text-end text-muted font-monospace">—</td>
                                    <td class="text-end font-monospace bn-digit text-primary">৳ {{ number_format($pageTotalSales, 2) }}</td>
                                    <td class="text-end font-monospace bn-digit text-secondary">৳ {{ number_format($pageTotalCogs, 2) }}</td>
                                    <td class="text-end font-monospace bn-digit text-success">৳ {{ number_format($pageTotalProfit, 2) }}</td>
                                    <td class="text-end pe-3 font-monospace bn-digit text-dark">{{ $pageOverallMargin }}%</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
            @if($items->hasPages())
                <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50 card-footer no-print">
                    <small class="text-muted bn-digit font-monospace" style="font-size: 11px;">
                        Showing {{ $items->firstItem() }} to {{ $items->lastItem() }} of {{ $items->total() }} entries
                    </small>
                    <div>
                        {{ $items->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection