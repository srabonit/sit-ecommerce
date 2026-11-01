@extends('layouts.admin')

@section('title', 'Tax & VAT Reports')

@section('admin_content')
<div class="container-fluid px-2 px-md-3 py-2 tax-reports-page">
    <!-- Header & Action Controls -->
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2 mb-3">
        <div>
            <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                <h4 class="fw-bold mb-0 text-dark fs-5 fs-md-4 d-flex align-items-center gap-2">
                    <i class="fas fa-chart-line text-primary"></i> Tax &amp; VAT Reports
                </h4>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 fw-medium" style="font-size: 11px;">
                    {{ ucwords(str_replace('_', ' ', $reportType)) }}
                </span>
            </div>
            <p class="text-secondary small mb-0">Comprehensive tax return, audit logs, and sales-based VAT statements.</p>
        </div>

        <div class="d-flex align-items-center flex-wrap gap-2 w-100 w-md-auto justify-content-start justify-content-md-end">
            <a href="{{ route('admin.tax.dashboard') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
                <i class="fas fa-arrow-left"></i> <span>Dashboard</span>
            </a>
            <a href="{{ route('admin.tax.reports.print', request()->all()) }}" target="_blank" class="btn btn-sm btn-outline-dark d-inline-flex align-items-center gap-1">
                <i class="fas fa-print"></i> <span>Print</span>
            </a>
            <a href="{{ route('admin.tax.reports.export', request()->all()) }}" class="btn btn-sm btn-success d-inline-flex align-items-center gap-1">
                <i class="fas fa-file-csv"></i> <span>Export CSV</span>
            </a>
        </div>
    </div>

    <!-- Summary Metrics Strip -->
    <div class="row g-2 mb-3">
        <!-- 1. Taxable Sales -->
        <div class="col-6 col-xl-3">
            <div class="card border border-light-subtle shadow-sm bg-white h-100">
                <div class="card-body p-2 p-md-3 d-flex align-items-center justify-content-between gap-2">
                    <div class="overflow-hidden">
                        <span class="text-secondary small fw-semibold text-uppercase text-truncate d-block" style="font-size: 11px; letter-spacing: 0.5px;">Taxable Sales</span>
                        <div class="fw-bold text-dark metric-value font-monospace text-truncate mt-1">৳{{ number_format($summary['total_taxable_amount'] ?? 0, 2) }}</div>
                    </div>
                    <div class="p-2 bg-light text-primary flex-shrink-0 rounded-1">
                        <i class="fas fa-coins fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Total Tax Collected -->
        <div class="col-6 col-xl-3">
            <div class="card border border-light-subtle shadow-sm bg-white h-100">
                <div class="card-body p-2 p-md-3 d-flex align-items-center justify-content-between gap-2">
                    <div class="overflow-hidden">
                        <span class="text-secondary small fw-semibold text-uppercase text-truncate d-block" style="font-size: 11px; letter-spacing: 0.5px;">Total Tax</span>
                        <div class="fw-bold text-success metric-value font-monospace text-truncate mt-1">৳{{ number_format($summary['total_tax_amount'] ?? 0, 2) }}</div>
                    </div>
                    <div class="p-2 bg-success-subtle text-success flex-shrink-0 rounded-1">
                        <i class="fas fa-hand-holding-dollar fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Product / Shipping Tax or Rate -->
        @if(isset($summary['product_tax_amount']) && isset($summary['shipping_tax_amount']))
            <div class="col-6 col-xl-3">
                <div class="card border border-light-subtle shadow-sm bg-white h-100">
                    <div class="card-body p-2 p-md-3 d-flex align-items-center justify-content-between gap-2">
                        <div class="overflow-hidden">
                            <span class="text-secondary small fw-semibold text-uppercase text-truncate d-block" style="font-size: 11px; letter-spacing: 0.5px;">Product / Delivery Tax</span>
                            <div class="fw-bold text-dark text-truncate mt-1 font-monospace" style="font-size: 12px;">
                                Item: <span class="text-primary">৳{{ number_format($summary['product_tax_amount'], 2) }}</span>
                            </div>
                            <span class="text-secondary small text-truncate d-block font-monospace" style="font-size: 11px;">
                                Delivery: ৳{{ number_format($summary['shipping_tax_amount'], 2) }}
                            </span>
                        </div>
                        <div class="p-2 bg-info-subtle text-info flex-shrink-0 rounded-1">
                            <i class="fas fa-boxes-packing fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="col-6 col-xl-3">
                <div class="card border border-light-subtle shadow-sm bg-white h-100">
                    <div class="card-body p-2 p-md-3 d-flex align-items-center justify-content-between gap-2">
                        <div class="overflow-hidden">
                            <span class="text-secondary small fw-semibold text-uppercase text-truncate d-block" style="font-size: 11px; letter-spacing: 0.5px;">Average Tax Rate</span>
                            <div class="fw-bold text-primary metric-value font-monospace text-truncate mt-1">
                                @php
                                    $taxable = $summary['total_taxable_amount'] ?? 0;
                                    $tax = $summary['total_tax_amount'] ?? 0;
                                    $effectiveRate = ($taxable > 0) ? ($tax / $taxable) * 100 : 0;
                                @endphp
                                {{ number_format($effectiveRate, 2) }}%
                            </div>
                        </div>
                        <div class="p-2 bg-primary-subtle text-primary flex-shrink-0 rounded-1">
                            <i class="fas fa-percent fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- 4. Total Records -->
        <div class="col-6 col-xl-3">
            <div class="card border border-light-subtle shadow-sm bg-white h-100">
                <div class="card-body p-2 p-md-3 d-flex align-items-center justify-content-between gap-2">
                    <div class="overflow-hidden">
                        <span class="text-secondary small fw-semibold text-uppercase text-truncate d-block" style="font-size: 11px; letter-spacing: 0.5px;">Total Records</span>
                        <div class="fw-bold text-dark metric-value font-monospace text-truncate mt-1">{{ number_format($summary['total_records'] ?? 0) }} Rows</div>
                    </div>
                    <div class="p-2 bg-secondary-subtle text-secondary flex-shrink-0 rounded-1">
                        <i class="fas fa-list-check fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Panel -->
    <div class="card border border-light-subtle shadow-sm bg-white mb-3">
        <div class="card-header bg-white border-0 pt-3 px-3 pb-1 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="fas fa-filter text-primary"></i> Report Filters
            </h6>
            <span class="badge bg-light text-secondary border px-2 py-1 fw-normal" style="font-size: 11px;">
                {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
            </span>
        </div>
        <div class="card-body p-2 p-md-3">
            <form action="{{ route('admin.tax.reports.index') }}" method="GET" id="taxReportFilterForm">
                <div class="row g-2 align-items-end">
                    <!-- Report Type -->
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label small fw-semibold text-secondary mb-1">Report Type</label>
                        <select name="report_type" class="form-select form-select-sm" id="reportTypeSelect">
                            <option value="all_transactions" {{ $reportType === 'all_transactions' ? 'selected' : '' }}>All Transactions</option>
                            <option value="product_wise" {{ $reportType === 'product_wise' ? 'selected' : '' }}>Product-wise</option>
                            <option value="variant_wise" {{ $reportType === 'variant_wise' ? 'selected' : '' }}>Variant-wise</option>
                            <option value="category_wise" {{ $reportType === 'category_wise' ? 'selected' : '' }}>Category-wise</option>
                            <option value="brand_wise" {{ $reportType === 'brand_wise' ? 'selected' : '' }}>Brand-wise</option>
                            <option value="branch_wise" {{ $reportType === 'branch_wise' ? 'selected' : '' }}>Branch-wise</option>
                            <option value="pos_wise" {{ $reportType === 'pos_wise' ? 'selected' : '' }}>POS Orders</option>
                            <option value="order_wise" {{ $reportType === 'order_wise' ? 'selected' : '' }}>Order-wise</option>
                            <option value="customer_wise" {{ $reportType === 'customer_wise' ? 'selected' : '' }}>Customer-wise</option>
                            <option value="shipping_tax" {{ $reportType === 'shipping_tax' ? 'selected' : '' }}>Shipping Tax</option>
                            <option value="collection_history" {{ $reportType === 'collection_history' ? 'selected' : '' }}>Collection History</option>
                            <option value="payment_history" {{ $reportType === 'payment_history' ? 'selected' : '' }}>Govt. Payments</option>
                            <option value="refund_history" {{ $reportType === 'refund_history' ? 'selected' : '' }}>Refund History</option>
                            <option value="adjustment_history" {{ $reportType === 'adjustment_history' ? 'selected' : '' }}>Adjustment History</option>
                        </select>
                    </div>

                    <!-- Date Filter Range -->
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label small fw-semibold text-secondary mb-1">Time Period</label>
                        <select name="date_filter" class="form-select form-select-sm" id="dateFilterSelect" onchange="handleDateFilterChange(this.value)">
                            <option value="today" {{ $dateFilter === 'today' ? 'selected' : '' }}>Today</option>
                            <option value="yesterday" {{ $dateFilter === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                            <option value="last_7_days" {{ $dateFilter === 'last_7_days' ? 'selected' : '' }}>Last 7 Days</option>
                            <option value="this_week" {{ $dateFilter === 'this_week' ? 'selected' : '' }}>This Week</option>
                            <option value="previous_week" {{ $dateFilter === 'previous_week' ? 'selected' : '' }}>Previous Week</option>
                            <option value="last_30_days" {{ $dateFilter === 'last_30_days' ? 'selected' : '' }}>Last 30 Days</option>
                            <option value="this_month" {{ $dateFilter === 'this_month' ? 'selected' : '' }}>This Month</option>
                            <option value="previous_month" {{ $dateFilter === 'previous_month' ? 'selected' : '' }}>Previous Month</option>
                            <option value="all_time" {{ in_array($dateFilter, ['all', 'all_time']) ? 'selected' : '' }}>All Time</option>
                            <option value="specific_date" {{ $dateFilter === 'specific_date' ? 'selected' : '' }}>Specific Date</option>
                            <option value="custom_range" {{ $dateFilter === 'custom_range' ? 'selected' : '' }}>Custom Range</option>
                            <option value="specific_month" {{ $dateFilter === 'specific_month' ? 'selected' : '' }}>Specific Month</option>
                            <option value="specific_year" {{ $dateFilter === 'specific_year' ? 'selected' : '' }}>Specific Year</option>
                        </select>
                    </div>

                    <!-- Dynamic Date Controls -->
                    <div class="col-12 col-sm-6 col-lg-3" id="dateParamBox">
                        <div id="boxSpecificDate" class="{{ $dateFilter === 'specific_date' ? '' : 'd-none' }}">
                            <label class="form-label small fw-semibold text-secondary mb-1">Pick Date</label>
                            <input type="date" name="specific_date" class="form-control form-control-sm" value="{{ request('specific_date', date('Y-m-d')) }}">
                        </div>
                        <div id="boxCustomRange" class="{{ $dateFilter === 'custom_range' ? '' : 'd-none' }}">
                            <label class="form-label small fw-semibold text-secondary mb-1">Date Range</label>
                            <div class="input-group input-group-sm">
                                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                                <span class="input-group-text bg-light text-secondary">-</span>
                                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                            </div>
                        </div>
                        <div id="boxSpecificMonth" class="{{ $dateFilter === 'specific_month' ? '' : 'd-none' }}">
                            <label class="form-label small fw-semibold text-secondary mb-1">Pick Month</label>
                            <input type="month" name="month_year" class="form-control form-control-sm" value="{{ request('month_year', date('Y-m')) }}">
                        </div>
                        <div id="boxSpecificYear" class="{{ $dateFilter === 'specific_year' ? '' : 'd-none' }}">
                            <label class="form-label small fw-semibold text-secondary mb-1">Select Year</label>
                            <select name="year" class="form-select form-select-sm">
                                @for($y = date('Y'); $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <!-- Search Input -->
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label small fw-semibold text-secondary mb-1">Search Keywords</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0 text-secondary"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" placeholder="Invoice, name, phone..." value="{{ request('search') }}">
                        </div>
                    </div>

                    <!-- Conditional Filter: Branches -->
                    @if(isset($branches) && $branches->count() > 0)
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">Branch</label>
                            <select name="branch_id" class="form-select form-select-sm">
                                <option value="">All Branches</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- Conditional Filter: Categories -->
                    @if($reportType === 'category_wise' && isset($categories) && $categories->count() > 0)
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">Category</label>
                            <select name="category_id" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- Conditional Filter: Brands -->
                    @if($reportType === 'brand_wise' && isset($brands) && $brands->count() > 0)
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">Brand</label>
                            <select name="brand_id" class="form-select form-select-sm">
                                <option value="">All Brands</option>
                                @foreach($brands as $br)
                                    <option value="{{ $br->id }}" {{ request('brand_id') == $br->id ? 'selected' : '' }}>{{ $br->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- Per Page Option -->
                    <div class="col-6 col-sm-3 col-lg-2">
                        <label class="form-label small fw-semibold text-secondary mb-1">Page Limit</label>
                        <select name="per_page" class="form-select form-select-sm">
                            <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15 Rows</option>
                            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25 Rows</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 Rows</option>
                            <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100 Rows</option>
                        </select>
                    </div>

                    <!-- Actions Buttons -->
                    <div class="col-6 col-sm-3 col-lg-2 d-flex align-items-center gap-1">
                        <button type="submit" class="btn btn-sm btn-primary flex-grow-1 d-inline-flex align-items-center justify-content-center gap-1">
                            <i class="fas fa-filter"></i> <span>Apply</span>
                        </button>
                        <a href="{{ route('admin.tax.reports.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filters">
                            <i class="fas fa-redo"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Main Report Data Table -->
    <div class="card border border-light-subtle shadow-sm bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-nowrap" id="taxReportDataTable" style="font-size: 12.5px;">
                <thead class="table-light text-secondary">
                    @if($reportType === 'product_wise')
                        <tr>
                            <th class="ps-3 py-2" style="width: 80px;">ID</th>
                            <th class="py-2">Product Name</th>
                            <th class="text-center py-2">Qty Sold</th>
                            <th class="text-end py-2">Taxable Base</th>
                            <th class="text-center py-2">Avg. Rate</th>
                            <th class="text-end pe-3 py-2">Tax Collected</th>
                        </tr>
                    @elseif($reportType === 'variant_wise')
                        <tr>
                            <th class="ps-3 py-2" style="width: 80px;">Variant ID</th>
                            <th class="py-2">Item Title</th>
                            <th class="text-center py-2">Qty Sold</th>
                            <th class="text-end py-2">Taxable Base</th>
                            <th class="text-center py-2">Avg. Rate</th>
                            <th class="text-end pe-3 py-2">Tax Collected</th>
                        </tr>
                    @elseif($reportType === 'category_wise')
                        <tr>
                            <th class="ps-3 py-2" style="width: 90px;">Category ID</th>
                            <th class="py-2">Category Name</th>
                            <th class="text-center py-2">Qty Sold</th>
                            <th class="text-end py-2">Taxable Base</th>
                            <th class="text-end pe-3 py-2">Tax Collected</th>
                        </tr>
                    @elseif($reportType === 'brand_wise')
                        <tr>
                            <th class="ps-3 py-2" style="width: 80px;">Brand ID</th>
                            <th class="py-2">Brand Name</th>
                            <th class="text-center py-2">Qty Sold</th>
                            <th class="text-end py-2">Taxable Base</th>
                            <th class="text-end pe-3 py-2">Tax Collected</th>
                        </tr>
                    @elseif($reportType === 'branch_wise')
                        <tr>
                            <th class="ps-3 py-2" style="width: 80px;">Branch ID</th>
                            <th class="py-2">Branch Name</th>
                            <th class="py-2">BIN</th>
                            <th class="text-center py-2">Orders</th>
                            <th class="text-end py-2">Taxable Base</th>
                            <th class="text-end py-2">Tax Amount</th>
                            <th class="text-end pe-3 py-2">Gross Sales</th>
                        </tr>
                    @elseif($reportType === 'customer_wise')
                        <tr>
                            <th class="ps-3 py-2">Phone</th>
                            <th class="py-2">Customer Name</th>
                            <th class="text-center py-2">Orders</th>
                            <th class="text-end py-2">Taxable Base</th>
                            <th class="text-end py-2">Tax Paid</th>
                            <th class="text-end pe-3 py-2">Total Spend</th>
                        </tr>
                    @elseif($reportType === 'shipping_tax')
                        <tr>
                            <th class="ps-3 py-2">Invoice</th>
                            <th class="py-2">Date</th>
                            <th class="py-2">City / Courier</th>
                            <th class="text-end py-2">Delivery Fee</th>
                            <th class="text-end pe-3 py-2">Shipping VAT</th>
                        </tr>
                    @elseif($reportType === 'payment_history')
                        <tr>
                            <th class="ps-3 py-2">Payment Date</th>
                            <th class="py-2">Tax Period</th>
                            <th class="py-2">Challan / Mushak</th>
                            <th class="py-2">Method</th>
                            <th class="text-end pe-3 py-2">Paid Amount</th>
                        </tr>
                    @elseif($reportType === 'adjustment_history' || $reportType === 'refund_history')
                        <tr>
                            <th class="ps-3 py-2">Date</th>
                            <th class="py-2">Invoice</th>
                            <th class="py-2">Type</th>
                            <th class="text-end py-2">Original Tax</th>
                            <th class="text-end py-2">Tax Reversal</th>
                            <th class="pe-3 py-2">Reason / Ref.</th>
                        </tr>
                    @else
                        {{-- Default: all_transactions, order_wise, collection_history, pos_wise --}}
                        <tr>
                            <th class="ps-3 py-2">Invoice</th>
                            <th class="py-2">Date</th>
                            <th class="py-2">Customer</th>
                            <th class="text-end py-2">Taxable Base</th>
                            <th class="text-end py-2">Product Tax</th>
                            <th class="text-end py-2">Shipping Tax</th>
                            <th class="text-end py-2">Total Tax</th>
                            <th class="text-end py-2">Grand Total</th>
                            <th class="text-center pe-3 py-2">Channel</th>
                        </tr>
                    @endif
                </thead>
                <tbody id="reportTableBody">
                    @forelse($records as $row)
                        @if($reportType === 'product_wise')
                            <tr>
                                <td class="ps-3 font-monospace text-secondary">#{{ $row->product_id }}</td>
                                <td class="fw-semibold text-dark text-truncate" style="max-width: 220px;" title="{{ $row->title }}">{{ $row->title }}</td>
                                <td class="text-center font-monospace">{{ number_format($row->total_qty ?? 0) }}</td>
                                <td class="text-end font-monospace">৳{{ number_format($row->taxable_amount ?? 0, 2) }}</td>
                                <td class="text-center font-monospace">{{ number_format($row->avg_rate ?? 0, 2) }}%</td>
                                <td class="text-end pe-3 fw-bold text-success font-monospace">৳{{ number_format($row->total_tax ?? 0, 2) }}</td>
                            </tr>
                        @elseif($reportType === 'variant_wise')
                            <tr>
                                <td class="ps-3 font-monospace text-secondary">#{{ $row->variant_id }}</td>
                                <td class="fw-semibold text-dark text-truncate" style="max-width: 220px;" title="{{ $row->title }}">{{ $row->title }}</td>
                                <td class="text-center font-monospace">{{ number_format($row->total_qty ?? 0) }}</td>
                                <td class="text-end font-monospace">৳{{ number_format($row->taxable_amount ?? 0, 2) }}</td>
                                <td class="text-center font-monospace">{{ number_format($row->avg_rate ?? 0, 2) }}%</td>
                                <td class="text-end pe-3 fw-bold text-success font-monospace">৳{{ number_format($row->total_tax ?? 0, 2) }}</td>
                            </tr>
                        @elseif($reportType === 'category_wise')
                            <tr>
                                <td class="ps-3 font-monospace text-secondary">#{{ $row->category_id ?? 'N/A' }}</td>
                                <td class="fw-semibold text-dark">{{ $row->category_name }}</td>
                                <td class="text-center font-monospace">{{ number_format($row->total_qty ?? 0) }}</td>
                                <td class="text-end font-monospace">৳{{ number_format($row->taxable_amount ?? 0, 2) }}</td>
                                <td class="text-end pe-3 fw-bold text-success font-monospace">৳{{ number_format($row->total_tax ?? 0, 2) }}</td>
                            </tr>
                        @elseif($reportType === 'brand_wise')
                            <tr>
                                <td class="ps-3 font-monospace text-secondary">#{{ $row->brand_id ?? 'N/A' }}</td>
                                <td class="fw-semibold text-dark">{{ $row->brand_name }}</td>
                                <td class="text-center font-monospace">{{ number_format($row->total_qty ?? 0) }}</td>
                                <td class="text-end font-monospace">৳{{ number_format($row->taxable_amount ?? 0, 2) }}</td>
                                <td class="text-end pe-3 fw-bold text-success font-monospace">৳{{ number_format($row->total_tax ?? 0, 2) }}</td>
                            </tr>
                        @elseif($reportType === 'branch_wise')
                            <tr>
                                <td class="ps-3 font-monospace text-secondary">#{{ $row->branch_id }}</td>
                                <td class="fw-semibold text-dark">{{ $row->branch_name }}</td>
                                <td><span class="badge bg-light text-dark border px-2 py-1 font-monospace">{{ $row->bin_number ?? 'N/A' }}</span></td>
                                <td class="text-center font-monospace">{{ number_format($row->total_orders ?? 0) }}</td>
                                <td class="text-end font-monospace">৳{{ number_format($row->taxable_amount ?? 0, 2) }}</td>
                                <td class="text-end fw-bold text-success font-monospace">৳{{ number_format($row->total_tax ?? 0, 2) }}</td>
                                <td class="text-end pe-3 font-monospace">৳{{ number_format($row->total_sales ?? 0, 2) }}</td>
                            </tr>
                        @elseif($reportType === 'customer_wise')
                            <tr>
                                <td class="ps-3 font-monospace text-primary">{{ $row->customer_phone }}</td>
                                <td class="fw-semibold text-dark">{{ $row->customer_name ?: 'Guest Customer' }}</td>
                                <td class="text-center font-monospace">{{ number_format($row->order_count ?? 0) }}</td>
                                <td class="text-end font-monospace">৳{{ number_format($row->taxable_amount ?? 0, 2) }}</td>
                                <td class="text-end fw-bold text-success font-monospace">৳{{ number_format($row->total_tax ?? 0, 2) }}</td>
                                <td class="text-end pe-3 font-monospace">৳{{ number_format($row->total_spent ?? 0, 2) }}</td>
                            </tr>
                        @elseif($reportType === 'shipping_tax')
                            <tr>
                                <td class="ps-3 fw-semibold font-monospace">{{ $row->invoice_no }}</td>
                                <td>{{ !empty($row->created_at) ? \Carbon\Carbon::parse($row->created_at)->format('d M Y, h:i A') : 'N/A' }}</td>
                                <td>{{ $row->delivery_city ?? ($row->courier_name ?? 'Standard Courier') }}</td>
                                <td class="text-end font-monospace">৳{{ number_format($row->taxable_amount ?? 0, 2) }}</td>
                                <td class="text-end pe-3 fw-bold text-primary font-monospace">৳{{ number_format($row->total_tax ?? 0, 2) }}</td>
                            </tr>
                        @elseif($reportType === 'payment_history')
                            <tr>
                                <td class="ps-3">{{ !empty($row->payment_date) ? \Carbon\Carbon::parse($row->payment_date)->format('d M Y') : 'N/A' }}</td>
                                <td><span class="badge bg-secondary-subtle text-secondary px-2 py-1 font-monospace">{{ $row->tax_period }}</span></td>
                                <td class="font-monospace">
                                    {{ $row->challan_number ?: 'N/A' }}
                                    @if(!empty($row->mushak_reference)) <small class="text-secondary">({{ $row->mushak_reference }})</small> @endif
                                </td>
                                <td>{{ ucfirst(str_replace('_', ' ', $row->payment_method ?? 'Cash')) }}</td>
                                <td class="text-end pe-3 fw-bold text-success font-monospace">৳{{ number_format($row->amount ?? 0, 2) }}</td>
                            </tr>
                        @elseif($reportType === 'adjustment_history' || $reportType === 'refund_history')
                            <tr>
                                <td class="ps-3">{{ !empty($row->created_at) ? \Carbon\Carbon::parse($row->created_at)->format('d M Y') : 'N/A' }}</td>
                                <td class="fw-semibold font-monospace text-dark">
                                    {{ !empty($row->order) ? ($row->order->invoice_no ?? '#'.$row->order_id) : (!empty($row->invoice_no) ? $row->invoice_no : '#'.($row->order_id ?? '')) }}
                                </td>
                                <td><span class="badge bg-danger-subtle text-danger px-2 py-1">{{ ucfirst(str_replace('_', ' ', $row->adjustment_type ?? 'Adjustment')) }}</span></td>
                                <td class="text-end text-secondary font-monospace">৳{{ number_format($row->original_tax_amount ?? 0, 2) }}</td>
                                <td class="text-end fw-bold text-danger font-monospace">-৳{{ number_format($row->net_tax_change ?? 0, 2) }}</td>
                                <td class="pe-3 small text-secondary">{{ $row->reason ?: ($row->reference_no ?: '-') }}</td>
                            </tr>
                        @else
                            {{-- Default Rows: all_transactions, order_wise, collection_history, pos_wise --}}
                            <tr>
                                <td class="ps-3 fw-semibold font-monospace text-dark">{{ $row->invoice_no }}</td>
                                <td>{{ !empty($row->created_at) ? \Carbon\Carbon::parse($row->created_at)->format('d M Y, h:i A') : 'N/A' }}</td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $row->customer_name ?: 'Guest' }}</div>
                                    <small class="text-secondary font-monospace" style="font-size: 11px;">{{ $row->customer_phone ?: '' }}</small>
                                </td>
                                <td class="text-end font-monospace">৳{{ number_format($row->taxable_amount ?? 0, 2) }}</td>
                                <td class="text-end text-secondary font-monospace">৳{{ number_format($row->product_tax_amount ?? 0, 2) }}</td>
                                <td class="text-end text-secondary font-monospace">৳{{ number_format($row->shipping_tax_amount ?? 0, 2) }}</td>
                                <td class="text-end fw-bold text-success font-monospace">৳{{ number_format($row->tax_amount ?? ($row->total_tax ?? 0), 2) }}</td>
                                <td class="text-end fw-bold text-dark font-monospace">৳{{ number_format($row->grand_total ?? 0, 2) }}</td>
                                <td class="text-center pe-3">
                                    @if(!empty($row->is_pos))
                                        <span class="badge bg-success-subtle text-success px-2 py-1">POS</span>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary px-2 py-1">Online</span>
                                    @endif
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <div class="text-secondary">
                                    <i class="fas fa-file-invoice-dollar fs-2 opacity-50 mb-2 d-block"></i>
                                    <h6 class="fw-semibold text-dark">No Records Found</h6>
                                    <p class="small mb-0">Try adjusting your filter parameters or search keywords.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($records, 'hasPages') && $records->hasPages())
            <div class="card-footer bg-white border-0 py-2 px-3" id="paginationWrapper">
                <div class="d-flex justify-content-center justify-content-md-end">
                    {{ $records->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<style>
    /* Strict 5px Radius Rule */
    .tax-reports-page .card,
    .tax-reports-page .btn,
    .tax-reports-page .form-control,
    .tax-reports-page .form-select,
    .tax-reports-page .input-group-text,
    .tax-reports-page .badge,
    .tax-reports-page .rounded,
    .tax-reports-page .rounded-1 {
        border-radius: 5px !important;
    }

    /* Fluid Typography for Metric Numbers */
    .tax-reports-page .metric-value {
        font-size: clamp(1.1rem, 1.8vw, 1.45rem);
        line-height: 1.2;
    }

    /* Focus States */
    .tax-reports-page .form-control:focus,
    .tax-reports-page .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.15);
    }

    /* Mobile Protection */
    @media (max-width: 767.98px) {
        .tax-reports-page {
            padding-left: 6px !important;
            padding-right: 6px !important;
        }

        .tax-reports-page .card-body {
            padding: 0.75rem !important;
        }

        .tax-reports-page .table-responsive {
            -webkit-overflow-scrolling: touch;
        }

        .tax-reports-page .metric-value {
            font-size: 1.15rem;
        }
    }
</style>

<script>
    function handleDateFilterChange(val) {
        const boxDate = document.getElementById('boxSpecificDate');
        const boxRange = document.getElementById('boxCustomRange');
        const boxMonth = document.getElementById('boxSpecificMonth');
        const boxYear = document.getElementById('boxSpecificYear');

        if (boxDate) boxDate.classList.add('d-none');
        if (boxRange) boxRange.classList.add('d-none');
        if (boxMonth) boxMonth.classList.add('d-none');
        if (boxYear) boxYear.classList.add('d-none');

        if (val === 'specific_date' && boxDate) {
            boxDate.classList.remove('d-none');
        } else if (val === 'custom_range' && boxRange) {
            boxRange.classList.remove('d-none');
        } else if (val === 'specific_month' && boxMonth) {
            boxMonth.classList.remove('d-none');
        } else if (val === 'specific_year' && boxYear) {
            boxYear.classList.remove('d-none');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const reportTypeSelect = document.getElementById('reportTypeSelect');
        if (reportTypeSelect) {
            reportTypeSelect.addEventListener('change', function () {
                document.getElementById('taxReportFilterForm').submit();
            });
        }
    });
</script>
@endsection