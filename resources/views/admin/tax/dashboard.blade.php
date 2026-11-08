
@extends('layouts.admin')

@section('title', 'Tax & VAT Dashboard')

@section('admin_content')
<div class="container-fluid px-2 px-md-3 py-3 tax-dashboard">

    {{-- Dashboard Header & Filters --}}
<div class="tax-dashboard-header d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3 mb-3">
    <div class="d-flex align-items-center gap-3 min-w-0">
        <span class="d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background: #e0f2fe; color: #0284c7; border-radius: 8px; font-size: 16px;">
            <i class="fas fa-file-invoice-dollar"></i>
        </span>
        <div class="min-w-0 lh-sm">
            <h4 class="fw-bold mb-1 text-dark text-truncate" style="font-size: 16px;">
                Tax &amp; VAT Dashboard
            </h4>
            <div class="text-secondary text-truncate m-0 p-0" style="font-size: 11.5px;">
                Overview of tax &amp; VAT operations
            </div>
        </div>
    </div>

    <form action="{{ route('admin.tax.dashboard') }}" method="GET"
          class="tax-dashboard-filter d-flex flex-wrap align-items-center justify-content-lg-end gap-2"
          id="taxDashboardFilterForm">

        {{-- Period Selector --}}
        <div class="filter-field-col">
            <select name="period" class="form-select form-select-sm" id="taxPeriodSelect"
                    onchange="toggleCustomDates(this.value)" style="height: 34px;">
                <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>Today</option>
                <option value="yesterday" {{ request('period') == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                <option value="last_7_days" {{ request('period') == 'last_7_days' ? 'selected' : '' }}>Last 7 Days</option>
                <option value="this_week" {{ request('period') == 'this_week' ? 'selected' : '' }}>This Week</option>
                <option value="this_month" {{ (!request()->filled('period') || request('period') == 'this_month') ? 'selected' : '' }}>This Month</option>
                <option value="last_month" {{ request('period') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                <option value="this_year" {{ request('period') == 'this_year' ? 'selected' : '' }}>This Year</option>
                <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>Custom Dates</option>
            </select>
        </div>

        {{-- Custom Date Inputs --}}
        <div class="tax-custom-date-range d-flex align-items-center gap-1 {{ request('period') == 'custom' ? '' : 'd-none' }}"
             id="customDateRangeBox">
            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}" style="height: 34px;">
            <span class="text-secondary small px-1">-</span>
            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}" style="height: 34px;">
        </div>

        {{-- Branch Selector --}}
        @if(isset($branches) && $branches->count() > 0)
            <div class="filter-field-col">
                <select name="branch_id" class="form-select form-select-sm" style="height: 34px;">
                    <option value="">All Branches</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>
                            {{ $b->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        {{-- Filter Actions --}}
        <div class="filter-btn-group d-flex align-items-center gap-2">
            <button type="submit"
                    class="btn btn-primary btn-sm px-3 d-inline-flex align-items-center justify-content-center gap-2 text-nowrap" style="height: 34px;">
                <i class="fas fa-filter"></i>
                <span>Filter</span>
            </button>

            <a href="{{ route('admin.tax.dashboard') }}"
               class="btn btn-outline-secondary btn-sm tax-reset-btn d-inline-flex align-items-center justify-content-center text-nowrap"
               title="Reset Filters" style="height: 34px; width: 34px;">
                <i class="fas fa-redo"></i>
            </a>
        </div>
    </form>
</div>

    {{-- Primary Tax Metrics --}}
    <div class="row g-3 mb-3">

        {{-- Total Tax Collected --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card tax-metric-card h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="tax-metric-label">Tax Collected</span>
                        <div class="tax-metric-icon icon-primary">
                            <i class="fas fa-hand-holding-dollar"></i>
                        </div>
                    </div>

                    <div class="fw-bold text-dark metric-value font-monospace mb-3">
                        ৳{{ number_format($metrics['total_tax_collected'] ?? 0, 2) }}
                    </div>

                    <div class="tax-metric-footer d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <span>Product:
                            <strong class="text-dark font-monospace">
                                ৳{{ number_format($metrics['product_tax'] ?? 0, 2) }}
                            </strong>
                        </span>
                        <span>Delivery:
                            <strong class="text-dark font-monospace">
                                ৳{{ number_format($metrics['shipping_tax'] ?? 0, 2) }}
                            </strong>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Net Tax Liability --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card tax-metric-card h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="tax-metric-label">Net Tax Due</span>
                        <div class="tax-metric-icon icon-danger">
                            <i class="fas fa-scale-balanced"></i>
                        </div>
                    </div>

                    <div class="fw-bold text-danger metric-value font-monospace mb-3">
                        ৳{{ number_format($metrics['net_tax_liability'] ?? 0, 2) }}
                    </div>

                    <div class="tax-metric-footer">
                        After refunds:
                        <span class="text-danger fw-semibold font-monospace">
                            -৳{{ number_format($metrics['total_tax_refunded'] ?? 0, 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Government Tax Paid --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card tax-metric-card h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="tax-metric-label">Govt. Tax Paid</span>
                        <div class="tax-metric-icon icon-success">
                            <i class="fas fa-building-columns"></i>
                        </div>
                    </div>

                    <div class="fw-bold text-success metric-value font-monospace mb-3">
                        ৳{{ number_format($metrics['total_tax_paid'] ?? 0, 2) }}
                    </div>

                    <div class="tax-metric-footer">
                        <i class="fas fa-check-circle text-success me-1"></i>
                        Verified Treasury Challans
                    </div>
                </div>
            </div>
        </div>

        {{-- Outstanding Tax --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card tax-metric-card h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="tax-metric-label">Tax Outstanding</span>
                        <div class="tax-metric-icon icon-warning">
                            <i class="fas fa-triangle-exclamation"></i>
                        </div>
                    </div>

                    <div class="fw-bold metric-value font-monospace mb-3 {{ ($metrics['outstanding_tax'] ?? 0) > 0 ? 'text-warning' : 'text-success' }}">
                        ৳{{ number_format($metrics['outstanding_tax'] ?? 0, 2) }}
                    </div>

                    <div class="tax-metric-footer">
                        @if(($metrics['outstanding_tax'] ?? 0) > 0)
                            <i class="fas fa-clock text-warning me-1"></i> Payable to NBR
                        @else
                            <i class="fas fa-check-circle text-success me-1"></i> Fully Settled
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Secondary Sales Summary --}}
    <div class="row g-3 mb-3">

        <div class="col-6 col-lg-3">
            <div class="card tax-summary-card h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="tax-summary-icon icon-primary">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="overflow-hidden">
                        <div class="tax-summary-label">Taxable Sales</div>
                        <div class="tax-summary-value font-monospace">
                            ৳{{ number_format($metrics['taxable_sales'] ?? 0, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card tax-summary-card h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="tax-summary-icon icon-success">
                        <i class="fas fa-shield"></i>
                    </div>
                    <div class="overflow-hidden">
                        <div class="tax-summary-label">Tax-Free Sales</div>
                        <div class="tax-summary-value font-monospace">
                            ৳{{ number_format($metrics['exempt_sales'] ?? 0, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card tax-summary-card h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="tax-summary-icon icon-info">
                        <i class="fas fa-percent"></i>
                    </div>
                    <div class="overflow-hidden">
                        <div class="tax-summary-label">0% Tax Sales</div>
                        <div class="tax-summary-value font-monospace">
                            ৳{{ number_format($metrics['zero_rated_sales'] ?? 0, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card tax-summary-card h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="tax-summary-icon icon-danger">
                        <i class="fas fa-arrow-rotate-left"></i>
                    </div>
                    <div class="overflow-hidden">
                        <div class="tax-summary-label">Tax Refunded</div>
                        <div class="tax-summary-value text-danger font-monospace">
                            ৳{{ number_format($metrics['total_tax_refunded'] ?? 0, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tax Trend Chart & Sales Channel Breakdown --}}
    <div class="row g-3 mb-3">

        {{-- Daily Tax Trend --}}
        <div class="col-lg-8">
            <div class="card tax-panel-card h-100">
                <div class="card-header tax-panel-header">
                    <div>
                        <h6 class="tax-panel-title">
                            <i class="fas fa-chart-area text-primary me-2"></i>
                            Daily Tax Trend
                        </h6>
                        <p class="tax-panel-subtitle">Tax collection and liability over time</p>
                    </div>

                    <span class="tax-date-badge">
                        <i class="far fa-calendar-alt me-1"></i>
                        {{ Carbon\Carbon::parse($startDate)->format('d M') }} - {{ Carbon\Carbon::parse($endDate)->format('d M Y') }}
                    </span>
                </div>

                <div class="card-body p-3">
                    <div class="tax-chart-wrap">
                        <canvas id="taxTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Online vs POS Tax --}}
        <div class="col-lg-4">
            <div class="card tax-panel-card h-100">
                <div class="card-header tax-panel-header">
                    <div>
                        <h6 class="tax-panel-title">
                            <i class="fas fa-arrows-split-up-and-left text-primary me-2"></i>
                            Online vs POS Tax
                        </h6>
                        <p class="tax-panel-subtitle">Sales channel performance</p>
                    </div>
                </div>

                <div class="card-body p-3 d-flex flex-column justify-content-center gap-3">

                    {{-- Online Sales --}}
                    <div class="tax-channel-card">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                            <span class="fw-semibold text-dark small">
                                <i class="fas fa-globe text-primary me-1"></i>
                                Online Sales
                            </span>
                            <span class="badge tax-channel-badge badge-primary">
                                {{ $channelTax['online']['order_count'] ?? 0 }} Orders
                            </span>
                        </div>

                        <div class="tax-channel-data">
                            <span class="text-secondary">Tax Collected</span>
                            <strong class="text-dark font-monospace">
                                ৳{{ number_format($channelTax['online']['collected_tax'] ?? 0, 2) }}
                            </strong>
                        </div>

                        <div class="tax-channel-data">
                            <span class="text-secondary">Total Volume</span>
                            <strong class="text-dark font-monospace">
                                ৳{{ number_format($channelTax['online']['total_sales'] ?? 0, 2) }}
                            </strong>
                        </div>
                    </div>

                    {{-- POS Sales --}}
                    <div class="tax-channel-card">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                            <span class="fw-semibold text-dark small">
                                <i class="fas fa-cash-register text-success me-1"></i>
                                POS Outlets
                            </span>
                            <span class="badge tax-channel-badge badge-success">
                                {{ $channelTax['pos']['order_count'] ?? 0 }} Orders
                            </span>
                        </div>

                        <div class="tax-channel-data">
                            <span class="text-secondary">Tax Collected</span>
                            <strong class="text-dark font-monospace">
                                ৳{{ number_format($channelTax['pos']['collected_tax'] ?? 0, 2) }}
                            </strong>
                        </div>

                        <div class="tax-channel-data">
                            <span class="text-secondary">Total Volume</span>
                            <strong class="text-dark font-monospace">
                                ৳{{ number_format($channelTax['pos']['total_sales'] ?? 0, 2) }}
                            </strong>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Branch Summary & Top Tax Products --}}
    <div class="row g-3 mb-3">

        {{-- Branch-wise Summary --}}
        <div class="col-lg-7">
            <div class="card tax-panel-card h-100">
                <div class="card-header tax-panel-header">
                    <div>
                        <h6 class="tax-panel-title">
                            <i class="fas fa-building text-primary me-2"></i>
                            Branch VAT Summary
                        </h6>
                        <p class="tax-panel-subtitle">Tax performance by branch</p>
                    </div>

                    <a href="{{ route('admin.tax.reports.index', ['report_type' => 'branch_wise']) }}"
                       class="tax-panel-link">
                        View All <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>

                <div class="table-responsive tax-table-wrap">
                    <table class="table table-hover table-sm align-middle mb-0 tax-dashboard-table text-nowrap">
                        <thead>
                            <tr>
                                <th class="ps-3">Branch</th>
                                <th>BIN</th>
                                <th class="text-center">Orders</th>
                                <th class="text-end">Sales (BDT)</th>
                                <th class="text-end pe-3">Tax (BDT)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($branchSummary as $bs)
                                <tr>
                                    <td class="ps-3 fw-semibold text-dark">{{ $bs->branch_name }}</td>
                                    <td>
                                        <span class="tax-neutral-badge">{{ $bs->bin_number ?: 'N/A' }}</span>
                                    </td>
                                    <td class="text-center">{{ $bs->order_count }}</td>
                                    <td class="text-end font-monospace">
                                        ৳{{ number_format($bs->total_sales, 2) }}
                                    </td>
                                    <td class="text-end pe-3 fw-bold text-success font-monospace">
                                        ৳{{ number_format($bs->total_tax, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-secondary">
                                        <i class="fas fa-inbox d-block mb-2 fs-4"></i>
                                        No branch sales recorded for this period.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Top Tax-Generating Products --}}
        <div class="col-lg-5">
            <div class="card tax-panel-card h-100">
                <div class="card-header tax-panel-header">
                    <div>
                        <h6 class="tax-panel-title">
                            <i class="fas fa-boxes-stacked text-primary me-2"></i>
                            Top Tax Products
                        </h6>
                        <p class="tax-panel-subtitle">Highest tax-generating products</p>
                    </div>

                    <a href="{{ route('admin.tax.reports.index', ['report_type' => 'product_wise']) }}"
                       class="tax-panel-link">
                        Details <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>

                <div class="table-responsive tax-table-wrap">
                    <table class="table table-hover table-sm align-middle mb-0 tax-dashboard-table text-nowrap">
                        <thead>
                            <tr>
                                <th class="ps-3">Product</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end pe-3">Tax Collected</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topTaxProducts as $tp)
                                <tr>
                                    <td class="ps-3">
                                        <div class="text-truncate fw-semibold text-dark tax-product-name"
                                             title="{{ $tp->product_title }}">
                                            {{ $tp->product_title }}
                                        </div>
                                        <small class="text-secondary tax-product-rate">
                                            Rate: {{ number_format($tp->avg_tax_rate, 2) }}%
                                        </small>
                                    </td>
                                    <td class="text-center">{{ $tp->total_qty }}</td>
                                    <td class="text-end pe-3 fw-bold text-primary font-monospace">
                                        ৳{{ number_format($tp->total_tax_collected, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-secondary">
                                        <i class="fas fa-inbox d-block mb-2 fs-4"></i>
                                        No tax records available.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Treasury Payments & Tax Adjustments --}}
    <div class="row g-3">

        {{-- Government Payments --}}
        <div class="col-lg-6">
            <div class="card tax-panel-card h-100">
                <div class="card-header tax-panel-header">
                    <div>
                        <h6 class="tax-panel-title">
                            <i class="fas fa-receipt text-success me-2"></i>
                            Treasury Payments
                        </h6>
                        <p class="tax-panel-subtitle">Recent government tax payments</p>
                    </div>

                    <a href="{{ route('admin.tax.payments.index') }}" class="tax-panel-link">
                        View All <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>

                <div class="table-responsive tax-table-wrap">
                    <table class="table table-hover table-sm align-middle mb-0 tax-dashboard-table text-nowrap">
                        <thead>
                            <tr>
                                <th class="ps-3">Date</th>
                                <th>Period</th>
                                <th>Challan</th>
                                <th class="text-end">Amount</th>
                                <th class="text-center pe-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPayments as $payment)
                                <tr>
                                    <td class="ps-3">
                                        {{ Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}
                                    </td>
                                    <td>
                                        <span class="tax-neutral-badge">
                                            {{ $payment->tax_period }}
                                        </span>
                                    </td>
                                    <td>{{ $payment->challan_number ?: 'N/A' }}</td>
                                    <td class="text-end fw-bold text-success font-monospace">
                                        ৳{{ number_format($payment->amount, 2) }}
                                    </td>
                                    <td class="text-center pe-3">
                                        @if(!empty($payment->document_file))
                                            <a href="{{ route('admin.tax.payments.download', $payment->id) }}"
                                               class="btn btn-sm btn-light border tax-download-btn"
                                               title="Download Challan">
                                                <i class="fas fa-download text-primary"></i>
                                            </a>
                                        @else
                                            <span class="text-secondary">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-secondary">
                                        <i class="fas fa-inbox d-block mb-2 fs-4"></i>
                                        No treasury payments recorded.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Tax Adjustments --}}
        <div class="col-lg-6">
            <div class="card tax-panel-card h-100">
                <div class="card-header tax-panel-header">
                    <div>
                        <h6 class="tax-panel-title">
                            <i class="fas fa-sliders text-danger me-2"></i>
                            Tax Adjustments
                        </h6>
                        <p class="tax-panel-subtitle">Recent tax adjustments and refunds</p>
                    </div>

                    <a href="{{ route('admin.tax.reports.index', ['report_type' => 'adjustment_history']) }}"
                       class="tax-panel-link">
                        View All <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>

                <div class="table-responsive tax-table-wrap">
                    <table class="table table-hover table-sm align-middle mb-0 tax-dashboard-table text-nowrap">
                        <thead>
                            <tr>
                                <th class="ps-3">Date</th>
                                <th>Order Invoice</th>
                                <th>Type</th>
                                <th class="text-end pe-3">Tax Adjusted</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAdjustments as $adj)
                                <tr>
                                    <td class="ps-3">
                                        {{ Carbon\Carbon::parse($adj->created_at)->format('d M Y') }}
                                    </td>
                                    <td class="fw-semibold text-dark font-monospace">
                                        {{ !empty($adj->invoice_no) ? $adj->invoice_no : '#'.$adj->order_id }}
                                    </td>
                                    <td>
                                        <span class="tax-adjustment-badge">
                                            {{ ucfirst(str_replace('_', ' ', $adj->adjustment_type)) }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-3 fw-bold text-danger font-monospace">
                                        -৳{{ number_format($adj->net_tax_change, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-secondary">
                                        <i class="fas fa-inbox d-block mb-2 fs-4"></i>
                                        No tax adjustments found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    function toggleCustomDates(value) {
        const box = document.getElementById('customDateRangeBox');

        if (value === 'custom') {
            box.classList.remove('d-none');
        } else {
            box.classList.add('d-none');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const canvas = document.getElementById('taxTrendChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        let chartInstance = null;

        function loadChartData() {
            const period = "{{ request('period', 'this_month') }}";
            const startDate = "{{ $startDate }}";
            const endDate = "{{ $endDate }}";
            const branchId = "{{ request('branch_id', '') }}";

            fetch(`{{ route('admin.tax.dashboard.chart_ajax') }}?period=${period}&start_date=${startDate}&end_date=${endDate}&branch_id=${branchId}`)
                .then(res => res.json())
                .then(data => {
                    if (chartInstance) {
                        chartInstance.destroy();
                    }

                    chartInstance = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [
                                {
                                    label: 'Tax Collected (BDT)',
                                    data: data.collected,
                                    borderColor: '#2563eb',
                                    backgroundColor: 'rgba(37, 99, 235, 0.08)',
                                    fill: true,
                                    tension: 0.35,
                                    borderWidth: 2,
                                    pointRadius: 2.5,
                                },
                                {
                                    label: 'Net Tax Liability (BDT)',
                                    data: data.liability,
                                    borderColor: '#dc2626',
                                    backgroundColor: 'transparent',
                                    borderDash: [4, 4],
                                    tension: 0.35,
                                    borderWidth: 2,
                                    pointRadius: 2,
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        boxWidth: 10,
                                        font: { size: 11 }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(val) {
                                            return '৳' + Number(val).toLocaleString();
                                        },
                                        font: { size: 10 }
                                    },
                                    grid: { color: 'rgba(0,0,0,0.04)' }
                                },
                                x: {
                                    ticks: { font: { size: 10 } },
                                    grid: { display: false }
                                }
                            }
                        }
                    });
                })
                .catch(err => console.error('Failed to load tax chart:', err));
        }

        loadChartData();
    });
</script>

<style>
    /* =====================================================
       TAX DASHBOARD - KAIADMIN CLASSIC WHITE THEME
    ===================================================== */

    .tax-dashboard {
        --tax-primary: var(--bs-primary, #1572e8);
        --tax-success: #31ce36;
        --tax-danger: #f25961;
        --tax-warning: #ffad46;
        --tax-info: #48abf7;
        --tax-text: #2a2f5b;
        --tax-muted: #6c757d;
        --tax-border: #ebecec;
        --tax-surface: #ffffff;
        --tax-soft-bg: #f8f9fa;
        color: var(--tax-text);
    }

    /* Consistent Radius */
    .tax-dashboard .card,
    .tax-dashboard .btn,
    .tax-dashboard .form-select,
    .tax-dashboard .form-control,
    .tax-dashboard .badge,
    .tax-dashboard .tax-metric-icon,
    .tax-dashboard .tax-summary-icon,
    .tax-dashboard .tax-channel-card,
    .tax-dashboard .tax-neutral-badge,
    .tax-dashboard .tax-adjustment-badge {
        border-radius: 5px !important;
    }

    /* Header */
    .tax-dashboard-header {
        min-width: 0;
    }

    .tax-page-heading h4 {
        font-size: 1.25rem;
        letter-spacing: -0.25px;
    }

    .tax-page-heading h4 i {
        font-size: 1.1rem;
    }

    .tax-page-heading p {
        font-size: 12px;
        margin-left: 28px;
    }

    /* Filter Controls */
    .tax-dashboard-filter {
        width: auto;
        min-width: 0;
    }

    .tax-dashboard-filter .filter-field-col {
        min-width: 145px;
    }

    .tax-dashboard-filter .form-select,
    .tax-dashboard-filter .form-control {
        min-height: 34px;
        border: 1px solid #e3e6ed;
        background-color: #fff;
        color: #495057;
        font-size: 12px;
        box-shadow: none;
    }

    .tax-dashboard-filter .form-select:focus,
    .tax-dashboard-filter .form-control:focus {
        border-color: var(--tax-primary);
        box-shadow: 0 0 0 2px rgba(21, 114, 232, 0.10);
    }

    .tax-dashboard-filter .btn {
        min-height: 34px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .tax-dashboard-filter .btn-primary {
        background-color: var(--tax-primary);
        border-color: var(--tax-primary);
    }

    .tax-dashboard-filter .btn-primary:hover {
        filter: brightness(0.94);
    }

    .tax-dashboard .tax-reset-btn {
        width: 35px;
        height: 34px;
        padding: 0;
    }

    .tax-custom-date-range {
        min-width: 0;
    }

    .tax-custom-date-range input {
        width: 135px;
    }

    /* Base Cards */
    .tax-dashboard .card {
        border: 1px solid var(--tax-border);
        background: var(--tax-surface);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.035);
        overflow: hidden;
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .tax-dashboard .card:hover {
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.055);
    }

    /* Main Metric Cards */
    .tax-metric-card {
        border-top: 2px solid #e9edf3 !important;
    }

    .tax-metric-card .card-body {
        min-width: 0;
    }

    .tax-metric-label {
        color: #6c757d;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.55px;
        text-transform: uppercase;
    }

    .tax-metric-icon {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 16px;
    }

    .icon-primary {
        color: var(--tax-primary);
        background: rgba(21, 114, 232, 0.10);
    }

    .icon-success {
        color: var(--tax-success);
        background: rgba(49, 206, 54, 0.11);
    }

    .icon-danger {
        color: var(--tax-danger);
        background: rgba(242, 89, 97, 0.10);
    }

    .icon-warning {
        color: #d98b20;
        background: rgba(255, 173, 70, 0.15);
    }

    .icon-info {
        color: var(--tax-info);
        background: rgba(72, 171, 247, 0.12);
    }

    .tax-dashboard .metric-value {
        font-size: clamp(1.2rem, 2vw, 1.55rem);
        line-height: 1.25;
        letter-spacing: -0.45px;
        overflow-wrap: anywhere;
    }

    .tax-metric-footer {
        min-height: 29px;
        padding-top: 9px;
        border-top: 1px solid #eff0f2;
        color: var(--tax-muted);
        font-size: 11px;
        line-height: 1.5;
    }

    /* Secondary Summary Cards */
    .tax-summary-card .card-body {
        min-width: 0;
    }

    .tax-summary-icon {
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 17px;
    }

    .tax-summary-label {
        color: #7b8190;
        font-size: 11px;
        margin-bottom: 4px;
        white-space: nowrap;
    }

    .tax-summary-value {
        color: #2a2f5b;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    /* Section Panels */
    .tax-panel-card {
        min-width: 0;
    }

    .tax-panel-header {
        min-height: 65px;
        padding: 13px 15px 11px;
        background: #fff;
        border-bottom: 1px solid #eff0f2;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .tax-panel-title {
        margin: 0;
        color: #2a2f5b;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.4;
    }

    .tax-panel-subtitle {
        color: #9298a5;
        font-size: 11px;
        margin: 3px 0 0;
        line-height: 1.35;
    }

    .tax-panel-link {
        color: var(--tax-primary);
        font-size: 11px;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
    }

    .tax-panel-link:hover {
        color: #0d5ec4;
        text-decoration: underline;
    }

    .tax-date-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 9px;
        color: #6c757d;
        background: #f8f9fa;
        border: 1px solid #ebeef2;
        border-radius: 5px;
        font-size: 11px;
        white-space: nowrap;
    }

    /* Chart */
    .tax-chart-wrap {
        width: 100%;
        height: 280px;
        position: relative;
    }

    .tax-chart-wrap canvas {
        max-width: 100%;
    }

    /* Sales Channel Cards */
    .tax-channel-card {
        padding: 13px;
        background: #f8f9fb;
        border: 1px solid #e9edf2;
    }

    .tax-channel-badge {
        padding: 5px 8px;
        font-size: 10px;
        font-weight: 600;
    }

    .tax-channel-badge.badge-primary {
        color: #fff;
        background: var(--tax-primary);
    }

    .tax-channel-badge.badge-success {
        color: #fff;
        background: var(--tax-success);
    }

    .tax-channel-data {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-top: 8px;
        font-size: 11px;
    }

    .tax-channel-data strong {
        text-align: right;
        overflow-wrap: anywhere;
    }

    /* Tables */
    .tax-table-wrap {
        width: 100%;
        max-width: 100%;
        overflow-x: auto;
        scrollbar-width: thin;
        scrollbar-color: #d5d9e0 transparent;
    }

    .tax-table-wrap::-webkit-scrollbar {
        height: 5px;
    }

    .tax-table-wrap::-webkit-scrollbar-thumb {
        background: #d5d9e0;
        border-radius: 5px;
    }

    .tax-dashboard-table {
        width: 100%;
        min-width: 510px;
        color: #555b6d;
        font-size: 12px;
    }

    .tax-dashboard-table thead th {
        padding: 10px 9px;
        color: #737b8c;
        background: #f8f9fb;
        border-bottom: 1px solid #e9edf2;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.35px;
        text-transform: uppercase;
        vertical-align: middle;
    }

    .tax-dashboard-table tbody td {
        padding: 10px 9px;
        border-bottom: 1px solid #f0f1f3;
        vertical-align: middle;
    }

    .tax-dashboard-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .tax-dashboard-table tbody tr:hover {
        background: #fafbfd;
    }

    .tax-dashboard-table .font-monospace {
        font-size: 11.5px;
    }

    .tax-neutral-badge {
        display: inline-block;
        padding: 4px 7px;
        color: #626b7a;
        background: #f4f5f7;
        border: 1px solid #e7e9ed;
        font-size: 10px;
        font-weight: 600;
    }

    .tax-adjustment-badge {
        display: inline-block;
        padding: 4px 7px;
        color: #d94b54;
        background: rgba(242, 89, 97, 0.09);
        border: 1px solid rgba(242, 89, 97, 0.12);
        font-size: 10px;
        font-weight: 600;
    }

    .tax-product-name {
        max-width: 170px;
        font-size: 12px;
    }

    .tax-product-rate {
        font-size: 10px;
    }

    .tax-download-btn {
        width: 28px;
        height: 27px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border-color: #e5e8ed;
    }

    .tax-download-btn:hover {
        background: #f0f5ff;
        border-color: #cbdcf7;
    }

    /* Tablet */
    @media (max-width: 991.98px) {
        .tax-dashboard-filter {
            width: 100%;
        }

        .tax-dashboard-filter .filter-field-col {
            flex: 1 1 calc(50% - 8px);
            min-width: 130px;
        }

        .tax-dashboard-filter .filter-field-col select {
            width: 100%;
        }

        .tax-custom-date-range {
            flex: 1 1 100%;
        }

        .tax-custom-date-range input {
            flex: 1 1 0;
            width: 50%;
            min-width: 0;
        }

        .tax-dashboard-filter .filter-btn-group {
            flex: 1 1 100%;
            width: 100%;
        }

        .tax-dashboard-filter .filter-btn-group .btn-primary {
            flex: 1 1 auto;
        }

        .tax-chart-wrap {
            height: 240px;
        }
    }

    /* Mobile */
    @media (max-width: 575.98px) {
        .tax-dashboard {
            padding-right: 8px !important;
            padding-left: 8px !important;
        }

        .tax-page-heading h4 {
            font-size: 17px;
        }

        .tax-page-heading p {
            margin-left: 26px;
            font-size: 11px;
        }

        .tax-dashboard .metric-value {
            font-size: 1.18rem;
        }

        .tax-metric-card .card-body {
            padding: 12px !important;
        }

        .tax-metric-icon {
            width: 32px;
            height: 32px;
            font-size: 14px;
        }

        .tax-metric-label {
            font-size: 10px;
        }

        .tax-metric-footer {
            font-size: 10px;
        }

        .tax-summary-card .card-body {
            padding: 10px !important;
            gap: 8px !important;
        }

        .tax-summary-icon {
            width: 34px;
            height: 34px;
            font-size: 14px;
        }

        .tax-summary-label {
            font-size: 10px;
        }

        .tax-summary-value {
            font-size: 11px;
        }

        .tax-panel-header {
            padding: 11px 12px;
        }

        .tax-panel-title {
            font-size: 12px;
        }

        .tax-panel-subtitle {
            font-size: 10px;
        }

        .tax-date-badge {
            padding: 5px 6px;
            font-size: 9px;
        }

        .tax-chart-wrap {
            height: 195px;
        }

        .tax-channel-card {
            padding: 11px;
        }

        .tax-channel-data {
            font-size: 10px;
        }

        .tax-dashboard-table {
            font-size: 11px;
        }

        .tax-dashboard-table thead th {
            font-size: 9px;
        }

        .tax-dashboard-table tbody td {
            padding: 9px 8px;
        }
    }

    /* Extra Small Devices */
    @media (max-width: 360px) {
        .tax-dashboard-filter .filter-field-col {
            min-width: 100%;
        }

        .tax-dashboard .metric-value {
            font-size: 1.05rem;
        }

        .tax-summary-icon {
            width: 30px;
            height: 30px;
        }

        .tax-summary-value {
            font-size: 10px;
        }
    }
</style>
@endsection
