@extends('layouts.admin')

@section('title', 'Tax & VAT Dashboard')

@section('admin_content')
<div class="container-fluid px-2 px-md-3 py-2 tax-dashboard">
    <!-- Top Header & Filter Controls -->
    <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-2 gap-lg-3 mb-3">
        <!-- Title & Subtitle (No wrapping on desktop/tablet) -->
        <div class="flex-shrink-0">
            <h4 class="fw-bold mb-0 text-dark fs-5 fs-md-4 d-flex align-items-center gap-2 text-nowrap">
                <i class="fas fa-file-invoice-dollar text-primary"></i> Tax &amp; VAT Dashboard
            </h4>
            <p class="text-secondary small mb-0">Overview of tax &amp; VAT operations</p>
        </div>

        <!-- Filter Form (Aligned right on desktop, symmetrical full-width on mobile) -->
        <form action="{{ route('admin.tax.dashboard') }}" method="GET" class="w-100 ms-lg-auto d-flex flex-wrap align-items-center justify-content-lg-end gap-2" id="taxDashboardFilterForm">
            <!-- Period Selector -->
            <div class="filter-field-col">
                <select name="period" class="form-select form-select-sm w-100" id="taxPeriodSelect" onchange="toggleCustomDates(this.value)">
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

            <!-- Custom Date Inputs -->
            <div class="d-flex align-items-center gap-1 {{ request('period') == 'custom' ? '' : 'd-none' }}" id="customDateRangeBox">
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
                <span class="text-secondary small">-</span>
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
            </div>

            <!-- Branch Selector -->
            @if(isset($branches) && $branches->count() > 0)
                <div class="filter-field-col">
                    <select name="branch_id" class="form-select form-select-sm w-100">
                        <option value="">All Branches</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Action Buttons (Fills remaining space on mobile so no blank gap is left) -->
            <div class="filter-btn-group d-flex align-items-center gap-1">
                <button type="submit" class="btn btn-sm btn-primary px-3 flex-grow-1 flex-lg-grow-0 d-inline-flex align-items-center justify-content-center gap-1">
                    <i class="fas fa-filter"></i> <span>Filter</span>
                </button>
                <a href="{{ route('admin.tax.dashboard') }}" class="btn btn-sm btn-outline-secondary flex-shrink-0 d-inline-flex align-items-center justify-content-center" title="Reset Filters" style="width: 32px; height: 31px;">
                    <i class="fas fa-redo"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Primary Metric Cards (4 Highlights) -->
    <div class="row g-2 mb-3">
        <!-- 1. Total Tax Collected -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border border-light-subtle shadow-sm h-100 bg-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase text-secondary fw-semibold small" style="font-size: 11px; letter-spacing: 0.5px;">Tax Collected</span>
                        <div class="badge bg-primary-subtle text-primary p-2">
                            <i class="fas fa-hand-holding-dollar fs-6"></i>
                        </div>
                    </div>
                    <div class="fw-bold mb-2 text-dark metric-value font-monospace">৳{{ number_format($metrics['total_tax_collected'] ?? 0, 2) }}</div>
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-1 text-secondary pt-1 border-top" style="font-size: 11px;">
                        <span>Product: <strong class="text-dark font-monospace">৳{{ number_format($metrics['product_tax'] ?? 0, 2) }}</strong></span>
                        <span>Delivery: <strong class="text-dark font-monospace">৳{{ number_format($metrics['shipping_tax'] ?? 0, 2) }}</strong></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Net Tax Liability -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border border-light-subtle shadow-sm h-100 bg-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase text-secondary fw-semibold small" style="font-size: 11px; letter-spacing: 0.5px;">Net Tax Due</span>
                        <div class="badge bg-danger-subtle text-danger p-2">
                            <i class="fas fa-scale-balanced fs-6"></i>
                        </div>
                    </div>
                    <div class="fw-bold mb-2 text-danger metric-value font-monospace">৳{{ number_format($metrics['net_tax_liability'] ?? 0, 2) }}</div>
                    <div class="text-secondary pt-1 border-top text-truncate" style="font-size: 11px;">
                        After refunds: <span class="text-danger fw-semibold font-monospace">-৳{{ number_format($metrics['total_tax_refunded'] ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Total Tax Paid -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border border-light-subtle shadow-sm h-100 bg-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase text-secondary fw-semibold small" style="font-size: 11px; letter-spacing: 0.5px;">Govt. Tax Paid</span>
                        <div class="badge bg-success-subtle text-success p-2">
                            <i class="fas fa-building-columns fs-6"></i>
                        </div>
                    </div>
                    <div class="fw-bold mb-2 text-success metric-value font-monospace">৳{{ number_format($metrics['total_tax_paid'] ?? 0, 2) }}</div>
                    <div class="text-secondary pt-1 border-top" style="font-size: 11px;">
                        <span>Verified Treasury Challans</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Outstanding Tax -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border border-light-subtle shadow-sm h-100 bg-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase text-secondary fw-semibold small" style="font-size: 11px; letter-spacing: 0.5px;">Tax Outstanding</span>
                        <div class="badge bg-warning-subtle text-warning p-2">
                            <i class="fas fa-triangle-exclamation fs-6"></i>
                        </div>
                    </div>
                    <div class="fw-bold mb-2 metric-value font-monospace {{ ($metrics['outstanding_tax'] ?? 0) > 0 ? 'text-warning' : 'text-success' }}">
                        ৳{{ number_format($metrics['outstanding_tax'] ?? 0, 2) }}
                    </div>
                    <div class="text-secondary pt-1 border-top" style="font-size: 11px;">
                        {{ ($metrics['outstanding_tax'] ?? 0) > 0 ? 'Payable to NBR' : 'Fully Settled' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Sales Strip -->
    <div class="row g-2 mb-3">
        <div class="col-6 col-lg-3">
            <div class="card border border-light-subtle shadow-sm bg-white h-100">
                <div class="card-body p-2 p-md-3 d-flex align-items-center gap-2">
                    <div class="p-2 rounded-1 bg-light text-primary flex-shrink-0"><i class="fas fa-chart-line fs-5"></i></div>
                    <div class="overflow-hidden">
                        <div class="text-secondary small text-truncate" style="font-size: 11px;">Taxable Sales</div>
                        <div class="fw-bold text-dark fs-6 font-monospace text-truncate">৳{{ number_format($metrics['taxable_sales'] ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border border-light-subtle shadow-sm bg-white h-100">
                <div class="card-body p-2 p-md-3 d-flex align-items-center gap-2">
                    <div class="p-2 rounded-1 bg-light text-success flex-shrink-0"><i class="fas fa-shield fs-5"></i></div>
                    <div class="overflow-hidden">
                        <div class="text-secondary small text-truncate" style="font-size: 11px;">Tax-Free Sales</div>
                        <div class="fw-bold text-dark fs-6 font-monospace text-truncate">৳{{ number_format($metrics['exempt_sales'] ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border border-light-subtle shadow-sm bg-white h-100">
                <div class="card-body p-2 p-md-3 d-flex align-items-center gap-2">
                    <div class="p-2 rounded-1 bg-light text-info flex-shrink-0"><i class="fas fa-percent fs-5"></i></div>
                    <div class="overflow-hidden">
                        <div class="text-secondary small text-truncate" style="font-size: 11px;">0% Tax Sales</div>
                        <div class="fw-bold text-dark fs-6 font-monospace text-truncate">৳{{ number_format($metrics['zero_rated_sales'] ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border border-light-subtle shadow-sm bg-white h-100">
                <div class="card-body p-2 p-md-3 d-flex align-items-center gap-2">
                    <div class="p-2 rounded-1 bg-light text-danger flex-shrink-0"><i class="fas fa-arrow-rotate-left fs-5"></i></div>
                    <div class="overflow-hidden">
                        <div class="text-secondary small text-truncate" style="font-size: 11px;">Tax Refunded</div>
                        <div class="fw-bold text-danger fs-6 font-monospace text-truncate">৳{{ number_format($metrics['total_tax_refunded'] ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Chart & Sales Channel Breakdown -->
    <div class="row g-2 mb-3">
        <!-- Live Tax Trend Chart -->
        <div class="col-lg-8">
            <div class="card border border-light-subtle shadow-sm h-100 bg-white">
                <div class="card-header bg-white border-0 pt-3 px-3 pb-1 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="fas fa-chart-area text-primary"></i> Daily Tax Trend
                    </h6>
                    <span class="badge bg-light text-secondary border px-2 py-1 fw-normal" style="font-size: 11px;">
                        {{ Carbon\Carbon::parse($startDate)->format('d M') }} - {{ Carbon\Carbon::parse($endDate)->format('d M Y') }}
                    </span>
                </div>
                <div class="card-body p-2 p-md-3">
                    <div class="tax-chart-wrap" style="height: 280px; position: relative;">
                        <canvas id="taxTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Online vs POS Channel Breakdown -->
        <div class="col-lg-4">
            <div class="card border border-light-subtle shadow-sm h-100 bg-white">
                <div class="card-header bg-white border-0 pt-3 px-3 pb-1">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="fas fa-arrows-split-up-and-left text-primary"></i> Online vs POS Tax
                    </h6>
                </div>
                <div class="card-body p-3 d-flex flex-column justify-content-center gap-2">
                    <!-- Online Card -->
                    <div class="p-3 border border-light-subtle bg-light rounded-1">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-semibold text-dark small d-flex align-items-center gap-1">
                                <i class="fas fa-globe text-primary"></i> Online Sales
                            </span>
                            <span class="badge bg-primary px-2 py-1 fw-medium" style="font-size: 11px;">{{ $channelTax['online']['order_count'] ?? 0 }} Orders</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="text-secondary small">Tax Collected:</span>
                            <span class="fw-bold text-dark font-monospace">৳{{ number_format($channelTax['online']['collected_tax'] ?? 0, 2) }}</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between text-secondary" style="font-size: 11px;">
                            <span>Total Volume:</span>
                            <span class="font-monospace">৳{{ number_format($channelTax['online']['total_sales'] ?? 0, 2) }}</span>
                        </div>
                    </div>

                    <!-- POS Card -->
                    <div class="p-3 border border-light-subtle bg-light rounded-1">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-semibold text-dark small d-flex align-items-center gap-1">
                                <i class="fas fa-cash-register text-success"></i> POS Outlets
                            </span>
                            <span class="badge bg-success px-2 py-1 fw-medium" style="font-size: 11px;">{{ $channelTax['pos']['order_count'] ?? 0 }} Orders</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="text-secondary small">Tax Collected:</span>
                            <span class="fw-bold text-dark font-monospace">৳{{ number_format($channelTax['pos']['collected_tax'] ?? 0, 2) }}</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between text-secondary" style="font-size: 11px;">
                            <span>Total Volume:</span>
                            <span class="font-monospace">৳{{ number_format($channelTax['pos']['total_sales'] ?? 0, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Tables Row (Branch Summary & Top Products) -->
    <div class="row g-2 mb-3">
        <!-- Branch-wise Summary -->
        <div class="col-lg-7">
            <div class="card border border-light-subtle shadow-sm bg-white h-100">
                <div class="card-header bg-white border-0 pt-3 px-3 pb-2 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="fas fa-building text-primary"></i> Branch VAT Summary
                    </h6>
                    <a href="{{ route('admin.tax.reports.index', ['report_type' => 'branch_wise']) }}" class="small text-decoration-none fw-semibold">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle mb-0 text-nowrap" style="font-size: 12.5px;">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th class="ps-3 py-2">Branch</th>
                                <th class="py-2">BIN</th>
                                <th class="text-center py-2">Orders</th>
                                <th class="text-end py-2">Sales (BDT)</th>
                                <th class="text-end pe-3 py-2">Tax (BDT)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($branchSummary as $bs)
                                <tr>
                                    <td class="ps-3 fw-semibold text-dark">{{ $bs->branch_name }}</td>
                                    <td><span class="badge bg-light text-dark border px-2 py-1">{{ $bs->bin_number ?: 'N/A' }}</span></td>
                                    <td class="text-center">{{ $bs->order_count }}</td>
                                    <td class="text-end font-monospace">৳{{ number_format($bs->total_sales, 2) }}</td>
                                    <td class="text-end pe-3 fw-bold text-success font-monospace">৳{{ number_format($bs->total_tax, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-secondary small">No branch sales recorded for this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top Tax-Generating Products -->
        <div class="col-lg-5">
            <div class="card border border-light-subtle shadow-sm bg-white h-100">
                <div class="card-header bg-white border-0 pt-3 px-3 pb-2 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="fas fa-boxes-stacked text-primary"></i> Top Tax Products
                    </h6>
                    <a href="{{ route('admin.tax.reports.index', ['report_type' => 'product_wise']) }}" class="small text-decoration-none fw-semibold">Details</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle mb-0 text-nowrap" style="font-size: 12.5px;">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th class="ps-3 py-2">Product</th>
                                <th class="text-center py-2">Qty</th>
                                <th class="text-end pe-3 py-2">Tax Collected</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topTaxProducts as $tp)
                                <tr>
                                    <td class="ps-3">
                                        <div class="text-truncate fw-semibold text-dark" style="max-width: 170px;" title="{{ $tp->product_title }}">
                                            {{ $tp->product_title }}
                                        </div>
                                        <small class="text-secondary" style="font-size: 11px;">Rate: {{ number_format($tp->avg_tax_rate, 2) }}%</small>
                                    </td>
                                    <td class="text-center">{{ $tp->total_qty }}</td>
                                    <td class="text-end pe-3 fw-bold text-primary font-monospace">৳{{ number_format($tp->total_tax_collected, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-secondary small">No tax records available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Government Payments & Adjustments -->
    <div class="row g-2">
        <!-- Govt. Payments -->
        <div class="col-lg-6">
            <div class="card border border-light-subtle shadow-sm bg-white h-100">
                <div class="card-header bg-white border-0 pt-3 px-3 pb-2 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="fas fa-receipt text-success"></i> Treasury Payments
                    </h6>
                    <a href="{{ route('admin.tax.payments.index') }}" class="small text-decoration-none fw-semibold">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle mb-0 text-nowrap" style="font-size: 12px;">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th class="ps-3 py-2">Date</th>
                                <th class="py-2">Period</th>
                                <th class="py-2">Challan</th>
                                <th class="text-end py-2">Amount</th>
                                <th class="text-center pe-3 py-2">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPayments as $payment)
                                <tr>
                                    <td class="ps-3">{{ Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
                                    <td><span class="badge bg-secondary-subtle text-secondary px-2 py-1">{{ $payment->tax_period }}</span></td>
                                    <td>{{ $payment->challan_number ?: 'N/A' }}</td>
                                    <td class="text-end fw-bold text-success font-monospace">৳{{ number_format($payment->amount, 2) }}</td>
                                    <td class="text-center pe-3">
                                        @if(!empty($payment->document_file))
                                            <a href="{{ route('admin.tax.payments.download', $payment->id) }}" class="btn btn-sm btn-light border p-1" title="Download Challan" style="line-height: 1;">
                                                <i class="fas fa-download text-primary" style="font-size: 11px;"></i>
                                            </a>
                                        @else
                                            <span class="text-secondary">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-secondary small">No treasury payments recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tax Adjustments -->
        <div class="col-lg-6">
            <div class="card border border-light-subtle shadow-sm bg-white h-100">
                <div class="card-header bg-white border-0 pt-3 px-3 pb-2 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="fas fa-sliders text-danger"></i> Tax Adjustments
                    </h6>
                    <a href="{{ route('admin.tax.reports.index', ['report_type' => 'adjustment_history']) }}" class="small text-decoration-none fw-semibold">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle mb-0 text-nowrap" style="font-size: 12px;">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th class="ps-3 py-2">Date</th>
                                <th class="py-2">Order Invoice</th>
                                <th class="py-2">Type</th>
                                <th class="text-end pe-3 py-2">Tax Adjusted</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAdjustments as $adj)
                                <tr>
                                    <td class="ps-3">{{ Carbon\Carbon::parse($adj->created_at)->format('d M Y') }}</td>
                                    <td class="fw-semibold text-dark font-monospace">{{ !empty($adj->invoice_no) ? $adj->invoice_no : '#'.$adj->order_id }}</td>
                                    <td>
                                        <span class="badge bg-danger-subtle text-danger px-2 py-1">
                                            {{ ucfirst(str_replace('_', ' ', $adj->adjustment_type)) }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-3 fw-bold text-danger font-monospace">-৳{{ number_format($adj->net_tax_change, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-secondary small">No tax adjustments found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
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
    /* Strict 5px Radius Rule */
    .tax-dashboard .card,
    .tax-dashboard .btn,
    .tax-dashboard .form-select,
    .tax-dashboard .form-control,
    .tax-dashboard .badge,
    .tax-dashboard .rounded,
    .tax-dashboard .rounded-1 {
        border-radius: 5px !important;
    }

    /* Fluid Typographic Metric Value */
    .tax-dashboard .metric-value {
        font-size: clamp(1.2rem, 2vw, 1.55rem);
        line-height: 1.2;
    }

    /* Desktop Form Layout (Right-aligned, no stretching, title stays intact) */
    @media (min-width: 992px) {
        .tax-dashboard #taxDashboardFilterForm {
            width: auto !important;
        }
        .tax-dashboard .filter-field-col select {
            min-width: 145px;
        }
    }

    /* Tablet & Mobile Layout Protection */
    @media (max-width: 991.98px) {
        .tax-dashboard #taxDashboardFilterForm {
            width: 100% !important;
        }

        /* Period & Branch dropdowns take equal 50% split */
        .tax-dashboard .filter-field-col {
            flex: 1 1 calc(50% - 0.25rem);
            min-width: 130px;
        }

        .tax-dashboard #customDateRangeBox {
            flex: 1 1 100% !important;
        }

        .tax-dashboard #customDateRangeBox input {
            flex: 1 1 0;
            width: 50%;
        }

        /* Buttons row takes 100% width; Filter button expands smoothly */
        .tax-dashboard .filter-btn-group {
            flex: 1 1 100% !important;
            width: 100%;
        }

        .tax-dashboard .filter-btn-group .btn-primary {
            flex: 1 1 auto;
        }

        .tax-dashboard .tax-chart-wrap {
            height: 220px !important;
        }
    }

    @media (max-width: 575.98px) {
        .tax-dashboard .metric-value {
            font-size: 1.25rem;
        }

        .tax-dashboard .tax-chart-wrap {
            height: 190px !important;
        }
    }
</style>
@endsection