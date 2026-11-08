@php
    $globalSettings = \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key');
    $siteTitle = $globalSettings['footer_brand_title'] ?? ($globalSettings['site_title'] ?? ($globalSettings['logo_title'] ?? config('app.name', 'Shop Now BD ONLINE')));
    $siteAddress = $globalSettings['company_address'] ?? ($globalSettings['site_address'] ?? 'Dhaka, Bangladesh');
    $sitePhone = $globalSettings['footer_phone'] ?? ($globalSettings['company_phone'] ?? ($globalSettings['site_phone'] ?? '+880 1305-937289'));
    $siteEmail = $globalSettings['footer_email'] ?? ($globalSettings['company_email'] ?? ($globalSettings['site_email'] ?? 'support@mayra.com.bd'));
    $companyBin = $globalSettings['company_bin'] ?? ($globalSettings['bin_number'] ?? '001293847-0101');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Statement - {{ ucwords(str_replace('_', ' ', $reportType)) }} ({{ $startDate }} to {{ $endDate }})</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap/bootstrap.min.css') }}">

    <style>
        :root {
            --statement-primary: #1572e8;
            --statement-primary-soft: #edf5ff;
            --statement-success: #31ce36;
            --statement-danger: #f25961;
            --statement-dark: #1a2035;
            --statement-muted: #6b7280;
            --statement-border: #e6e9f0;
            --statement-light: #f8f9fc;
            --statement-radius: 5px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: var(--statement-dark);
            background: #f4f6f9;
            font-size: 12px;
            line-height: 1.55;
            margin: 0;
            padding: 24px 16px;
            -webkit-font-smoothing: antialiased;
        }

        .statement-wrapper,
        .statement-wrapper .card,
        .statement-wrapper .btn,
        .statement-wrapper .badge,
        .statement-wrapper .summary-card,
        .statement-wrapper .meta-box,
        .statement-wrapper .sign-box,
        .no-print .btn {
            border-radius: var(--statement-radius) !important;
        }

        .statement-wrapper {
            width: 100%;
            max-width: 1050px;
            margin: 0 auto;
            padding: 30px;
            background: #fff;
            border: 1px solid var(--statement-border);
            box-shadow: 0 2px 12px rgba(0, 0, 0, .035);
        }

        /* Action Bar */
        .statement-action-bar {
            max-width: 1050px;
            margin: 0 auto 16px;
        }

        .statement-action-bar .btn {
            min-height: 34px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 5px !important;
            padding: 7px 13px;
        }

        /* Header */
        .statement-header {
            border-bottom: 2px solid var(--statement-dark);
            padding-bottom: 20px;
            margin-bottom: 22px;
        }

        .company-logo-text {
            margin: 0;
            color: var(--statement-dark);
            font-size: 23px;
            font-weight: 800;
            letter-spacing: -.7px;
            line-height: 1.3;
            text-transform: uppercase;
            overflow-wrap: anywhere;
        }

        .company-contact-details {
            color: var(--statement-muted);
            font-size: 11px;
            line-height: 1.9;
            margin-top: 7px;
        }

        .company-contact-details strong {
            color: var(--statement-dark);
            font-weight: 700;
        }

        .report-title-badge {
            display: inline-block;
            padding: 5px 9px;
            background: var(--statement-primary-soft);
            color: var(--statement-primary);
            border: 1px solid #d8e8ff;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .65px;
            text-transform: uppercase;
        }

        .statement-report-title {
            color: var(--statement-dark);
            font-size: 17px;
            font-weight: 800;
            margin: 7px 0 8px;
            overflow-wrap: anywhere;
        }

        .meta-box {
            min-width: 245px;
            padding: 12px 14px;
            background: var(--statement-light);
            border: 1px solid var(--statement-border);
        }

        .meta-box .meta-line {
            color: var(--statement-muted);
            font-size: 10.5px;
            line-height: 1.9;
        }

        .meta-box .meta-line strong {
            color: var(--statement-dark);
            font-weight: 700;
        }

        /* Summary Cards */
        .summary-card {
            height: 100%;
            padding: 13px 14px;
            background: #fff;
            border: 1px solid var(--statement-border);
            border-left: 3px solid var(--statement-primary);
            box-shadow: 0 1px 3px rgba(0, 0, 0, .025);
        }

        .summary-card.summary-success {
            border-left-color: var(--statement-success);
        }

        .summary-card.summary-info {
            border-left-color: #6861ce;
        }

        .summary-card.summary-dark {
            border-left-color: #1a2035;
        }

        .summary-label {
            display: block;
            color: var(--statement-muted);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .55px;
            text-transform: uppercase;
        }

        .metric-value {
            color: var(--statement-dark);
            font-size: clamp(15px, 1.8vw, 20px);
            font-weight: 800;
            line-height: 1.35;
            overflow-wrap: anywhere;
        }

        /* Table */
        .statement-table-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin: 24px 0 10px;
        }

        .statement-table-heading h6 {
            color: var(--statement-dark);
            font-size: 12px;
            font-weight: 800;
            margin: 0;
        }

        .statement-table-heading span {
            color: var(--statement-muted);
            font-size: 10px;
        }

        .table-responsive-wrapper {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-statement {
            width: 100%;
            min-width: 640px;
            margin-bottom: 20px;
            border-collapse: collapse;
            font-size: 10.5px;
            color: #3b4354;
        }

        .table-statement thead {
            display: table-header-group;
        }

        .table-statement th {
            padding: 10px 9px;
            color: #3b4354;
            background: #f4f5f9;
            border: 1px solid #e1e4eb;
            font-size: 9.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .35px;
            white-space: nowrap;
        }

        .table-statement td {
            padding: 9px;
            border: 1px solid #e6e9f0;
            vertical-align: middle;
            line-height: 1.5;
        }

        .table-statement tbody tr:nth-child(even) {
            background: #fafbfc;
        }

        .table-statement tbody tr {
            page-break-inside: avoid;
        }

        .table-statement .text-success {
            color: #159947 !important;
        }

        .table-statement .text-danger {
            color: #e04452 !important;
        }

        .table-statement .text-primary {
            color: var(--statement-primary) !important;
        }

        /* Signature */
        .sign-area {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 18px;
            margin-top: 65px;
            page-break-inside: avoid;
        }

        .sign-box {
            width: 210px;
            padding-top: 9px;
            border-top: 1px dashed #8991a3;
            color: #596174;
            font-size: 10px;
            font-weight: 700;
            text-align: center;
        }

        /* Disclaimer */
        .statement-disclaimer {
            color: #8991a3;
            font-size: 9.5px;
            line-height: 1.6;
        }

        /* Mobile */
        @media (max-width: 767.98px) {
            body {
                padding: 10px;
            }

            .statement-wrapper {
                padding: 16px;
            }

            .statement-header > .d-flex {
                flex-direction: column;
                gap: 15px;
            }

            .statement-header .meta-box {
                width: 100%;
                min-width: 0;
                text-align: left !important;
            }

            .company-logo-text {
                font-size: 20px;
            }

            .statement-report-title {
                font-size: 15px;
            }

            .statement-action-bar {
                gap: 8px;
            }

            .statement-action-bar .btn {
                flex: 1;
                justify-content: center;
            }

            .sign-area {
                flex-wrap: wrap;
                justify-content: center;
                gap: 35px 12px;
                margin-top: 45px;
            }

            .sign-box {
                width: calc(50% - 8px);
                font-size: 9px;
            }

            .table-statement {
                min-width: 640px;
            }
        }

        @media (max-width: 420px) {
            .statement-wrapper {
                padding: 12px;
            }

            .statement-action-bar .btn {
                font-size: 10px;
                padding: 7px 8px;
            }

            .metric-value {
                font-size: 14px;
            }
        }

        /* Print */
        @media print {
            @page {
                size: A4 landscape;
                margin: 12mm;
            }

            body {
                padding: 0 !important;
                margin: 0 !important;
                color: #000 !important;
                background: #fff !important;
                font-size: 10px;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .statement-wrapper {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                overflow: visible !important;
            }

            .no-print {
                display: none !important;
            }

            .statement-header {
                margin-bottom: 14px;
                padding-bottom: 12px;
            }

            .summary-card {
                box-shadow: none !important;
                break-inside: avoid;
            }

            .table-responsive-wrapper {
                overflow: visible !important;
            }

            .table-statement {
                width: 100% !important;
                min-width: 0 !important;
                font-size: 9px;
            }

            .table-statement th {
                background: #e9edf3 !important;
                color: #000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .table-statement td,
            .table-statement th {
                padding: 6px 7px;
            }

            .sign-area {
                margin-top: 45px;
                break-inside: avoid;
            }

            .statement-disclaimer {
                margin-top: 20px !important;
            }

            tr {
                page-break-inside: avoid !important;
            }
        }
    </style>
</head>

<body>

    <!-- Action Bar -->
    <div class="no-print statement-action-bar d-flex align-items-center justify-content-between">
        <button type="button"
                class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-2"
                onclick="window.close()">
            <i class="fas fa-arrow-left"></i>
            <span>Close Window</span>
        </button>

        <button type="button"
                class="btn btn-sm btn-primary px-3 shadow-sm d-inline-flex align-items-center gap-2"
                onclick="window.print()">
            <i class="fas fa-print"></i>
            <span>Print Statement</span>
        </button>
    </div>

    <!-- Main Statement -->
    <div class="statement-wrapper">

        <!-- Header -->
        <div class="statement-header">
            <div class="d-flex justify-content-between align-items-start gap-3">

                <div class="flex-grow-1">
                    <h1 class="company-logo-text">{{ $siteTitle }}</h1>

                    <div class="company-contact-details">
                        {{ $siteAddress }}<br>
                        Phone: {{ $sitePhone }}<br>
                        Email: {{ $siteEmail }}<br>
                        <strong>BIN:</strong>
                        <span class="font-monospace text-dark fw-semibold">{{ $companyBin }}</span>
                    </div>
                </div>

                <div class="text-md-end meta-box">
                    <span class="report-title-badge">NBR Audit Statement</span>

                    <h5 class="statement-report-title">
                        {{ ucwords(str_replace('_', ' ', $reportType)) }}
                    </h5>

                    <div class="meta-line">
                        <strong>Period:</strong>
                        {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}
                        -
                        {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                        <br>

                        <strong>Generated:</strong>
                        {{ date('d M Y, h:i A') }}
                        <br>

                        <strong>Prepared By:</strong>
                        {{ auth()->user()->name ?? 'System Administrator' }}
                    </div>
                </div>

            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-2 mb-3">

            <div class="col-6 col-md-3">
                <div class="summary-card summary-dark">
                    <span class="summary-label">Taxable Sales</span>
                    <div class="metric-value font-monospace mt-2">
                        ৳{{ number_format($summary['total_taxable_amount'] ?? 0, 2) }}
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="summary-card summary-success">
                    <span class="summary-label">Total Tax</span>
                    <div class="metric-value font-monospace text-success mt-2">
                        ৳{{ number_format($summary['total_tax_amount'] ?? 0, 2) }}
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="summary-card summary-info">
                    <span class="summary-label">Average Rate</span>
                    <div class="metric-value font-monospace text-primary mt-2">
                        @php
                            $taxable = $summary['total_taxable_amount'] ?? 0;
                            $tax = $summary['total_tax_amount'] ?? 0;
                            $eff = ($taxable > 0) ? ($tax / $taxable) * 100 : 0;
                        @endphp
                        {{ number_format($eff, 2) }}%
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="summary-card summary-dark">
                    <span class="summary-label">Total Records</span>
                    <div class="metric-value font-monospace mt-2">
                        {{ number_format($summary['total_records'] ?? 0) }} Rows
                    </div>
                </div>
            </div>

        </div>

        <!-- Table Heading -->
        <div class="statement-table-heading">
            <h6>
                <i class="fas fa-table me-1 text-primary"></i>
                Report Details
            </h6>
            <span>All amounts are in BDT (৳)</span>
        </div>

        <!-- Data Table -->
        <div class="table-responsive-wrapper">
            <table class="table-statement">

                <thead>
                    @if($reportType === 'product_wise')
                        <tr>
                            <th style="width: 70px;">ID</th>
                            <th>Product Name</th>
                            <th class="text-center">Qty Sold</th>
                            <th class="text-end">Taxable (BDT)</th>
                            <th class="text-center">Avg Rate</th>
                            <th class="text-end">Tax (BDT)</th>
                        </tr>
                    @elseif($reportType === 'variant_wise')
                        <tr>
                            <th style="width: 70px;">Variant ID</th>
                            <th>Product Name</th>
                            <th class="text-center">Qty Sold</th>
                            <th class="text-end">Taxable (BDT)</th>
                            <th class="text-center">Avg Rate</th>
                            <th class="text-end">Tax (BDT)</th>
                        </tr>
                    @elseif($reportType === 'category_wise')
                        <tr>
                            <th style="width: 80px;">Category ID</th>
                            <th>Category</th>
                            <th class="text-center">Items Sold</th>
                            <th class="text-end">Taxable (BDT)</th>
                            <th class="text-end">Tax (BDT)</th>
                        </tr>
                    @elseif($reportType === 'brand_wise')
                        <tr>
                            <th style="width: 80px;">Brand ID</th>
                            <th>Brand</th>
                            <th class="text-center">Items Sold</th>
                            <th class="text-end">Taxable (BDT)</th>
                            <th class="text-end">Tax (BDT)</th>
                        </tr>
                    @elseif($reportType === 'branch_wise')
                        <tr>
                            <th style="width: 70px;">Branch ID</th>
                            <th>Branch / Outlet</th>
                            <th>BIN</th>
                            <th class="text-center">Orders</th>
                            <th class="text-end">Taxable (BDT)</th>
                            <th class="text-end">Tax (BDT)</th>
                            <th class="text-end">Gross Sales (BDT)</th>
                        </tr>
                    @elseif($reportType === 'customer_wise')
                        <tr>
                            <th>Customer Phone</th>
                            <th>Customer Name</th>
                            <th class="text-center">Orders</th>
                            <th class="text-end">Taxable (BDT)</th>
                            <th class="text-end">Tax Paid (BDT)</th>
                            <th class="text-end">Total Spend (BDT)</th>
                        </tr>
                    @elseif($reportType === 'shipping_tax')
                        <tr>
                            <th>Invoice</th>
                            <th>Date</th>
                            <th>City / Courier</th>
                            <th class="text-end">Shipping Fee (BDT)</th>
                            <th class="text-end">Shipping VAT (BDT)</th>
                        </tr>
                    @elseif($reportType === 'payment_history')
                        <tr>
                            <th>Payment Date</th>
                            <th>Tax Period</th>
                            <th>Challan / Mushak</th>
                            <th>Method</th>
                            <th class="text-end">Paid (BDT)</th>
                        </tr>
                    @elseif($reportType === 'adjustment_history' || $reportType === 'refund_history')
                        <tr>
                            <th>Date</th>
                            <th>Invoice</th>
                            <th>Type</th>
                            <th class="text-end">Original Tax</th>
                            <th class="text-end">Reversal (BDT)</th>
                            <th>Reason / Reference</th>
                        </tr>
                    @else
                        <tr>
                            <th>Invoice</th>
                            <th>Date</th>
                            <th>Customer &amp; Phone</th>
                            <th class="text-end">Taxable (BDT)</th>
                            <th class="text-end">Product Tax</th>
                            <th class="text-end">Shipping Tax</th>
                            <th class="text-end">Total Tax (BDT)</th>
                            <th class="text-end">Grand Total</th>
                            <th class="text-center">Channel</th>
                        </tr>
                    @endif
                </thead>

                <tbody>
                    @forelse($records as $row)
                        @if($reportType === 'product_wise')
                            <tr>
                                <td class="font-monospace text-secondary">#{{ $row->product_id }}</td>
                                <td class="fw-semibold text-dark">{{ $row->title }}</td>
                                <td class="text-center font-monospace">{{ number_format($row->total_qty ?? 0) }}</td>
                                <td class="text-end font-monospace">৳{{ number_format($row->taxable_amount ?? 0, 2) }}</td>
                                <td class="text-center font-monospace">{{ number_format($row->avg_rate ?? 0, 2) }}%</td>
                                <td class="text-end fw-bold text-success font-monospace">৳{{ number_format($row->total_tax ?? 0, 2) }}</td>
                            </tr>
                        @elseif($reportType === 'variant_wise')
                            <tr>
                                <td class="font-monospace text-secondary">#{{ $row->variant_id }}</td>
                                <td class="fw-semibold text-dark">{{ $row->title }}</td>
                                <td class="text-center font-monospace">{{ number_format($row->total_qty ?? 0) }}</td>
                                <td class="text-end font-monospace">৳{{ number_format($row->taxable_amount ?? 0, 2) }}</td>
                                <td class="text-center font-monospace">{{ number_format($row->avg_rate ?? 0, 2) }}%</td>
                                <td class="text-end fw-bold text-success font-monospace">৳{{ number_format($row->total_tax ?? 0, 2) }}</td>
                            </tr>
                        @elseif($reportType === 'category_wise')
                            <tr>
                                <td class="font-monospace text-secondary">#{{ $row->category_id ?? 'N/A' }}</td>
                                <td class="fw-semibold text-dark">{{ $row->category_name }}</td>
                                <td class="text-center font-monospace">{{ number_format($row->total_qty ?? 0) }}</td>
                                <td class="text-end font-monospace">৳{{ number_format($row->taxable_amount ?? 0, 2) }}</td>
                                <td class="text-end fw-bold text-success font-monospace">৳{{ number_format($row->total_tax ?? 0, 2) }}</td>
                            </tr>
                        @elseif($reportType === 'brand_wise')
                            <tr>
                                <td class="font-monospace text-secondary">#{{ $row->brand_id ?? 'N/A' }}</td>
                                <td class="fw-semibold text-dark">{{ $row->brand_name }}</td>
                                <td class="text-center font-monospace">{{ number_format($row->total_qty ?? 0) }}</td>
                                <td class="text-end font-monospace">৳{{ number_format($row->taxable_amount ?? 0, 2) }}</td>
                                <td class="text-end fw-bold text-success font-monospace">৳{{ number_format($row->total_tax ?? 0, 2) }}</td>
                            </tr>
                        @elseif($reportType === 'branch_wise')
                            <tr>
                                <td class="font-monospace text-secondary">#{{ $row->branch_id }}</td>
                                <td class="fw-semibold text-dark">{{ $row->branch_name }}</td>
                                <td class="font-monospace">{{ $row->bin_number ?? 'N/A' }}</td>
                                <td class="text-center font-monospace">{{ number_format($row->total_orders ?? 0) }}</td>
                                <td class="text-end font-monospace">৳{{ number_format($row->taxable_amount ?? 0, 2) }}</td>
                                <td class="text-end fw-bold text-success font-monospace">৳{{ number_format($row->total_tax ?? 0, 2) }}</td>
                                <td class="text-end font-monospace">৳{{ number_format($row->total_sales ?? 0, 2) }}</td>
                            </tr>
                        @elseif($reportType === 'customer_wise')
                            <tr>
                                <td class="font-monospace text-primary">{{ $row->customer_phone }}</td>
                                <td class="fw-semibold text-dark">{{ $row->customer_name ?: 'Guest Customer' }}</td>
                                <td class="text-center font-monospace">{{ number_format($row->order_count ?? 0) }}</td>
                                <td class="text-end font-monospace">৳{{ number_format($row->taxable_amount ?? 0, 2) }}</td>
                                <td class="text-end fw-bold text-success font-monospace">৳{{ number_format($row->total_tax ?? 0, 2) }}</td>
                                <td class="text-end font-monospace">৳{{ number_format($row->total_spent ?? 0, 2) }}</td>
                            </tr>
                        @elseif($reportType === 'shipping_tax')
                            <tr>
                                <td class="font-monospace fw-semibold">{{ $row->invoice_no }}</td>
                                <td>{{ !empty($row->created_at) ? \Carbon\Carbon::parse($row->created_at)->format('d M Y') : 'N/A' }}</td>
                                <td>{{ $row->delivery_city ?? ($row->courier_name ?? 'Standard Courier') }}</td>
                                <td class="text-end font-monospace">৳{{ number_format($row->taxable_amount ?? 0, 2) }}</td>
                                <td class="text-end fw-bold text-primary font-monospace">৳{{ number_format($row->total_tax ?? 0, 2) }}</td>
                            </tr>
                        @elseif($reportType === 'payment_history')
                            <tr>
                                <td>{{ !empty($row->payment_date) ? \Carbon\Carbon::parse($row->payment_date)->format('d M Y') : 'N/A' }}</td>
                                <td class="font-monospace">{{ $row->tax_period }}</td>
                                <td class="font-monospace">{{ $row->challan_number ?: 'N/A' }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $row->payment_method ?? 'Cash')) }}</td>
                                <td class="text-end fw-bold text-success font-monospace">৳{{ number_format($row->amount ?? 0, 2) }}</td>
                            </tr>
                        @elseif($reportType === 'adjustment_history' || $reportType === 'refund_history')
                            <tr>
                                <td>{{ !empty($row->created_at) ? \Carbon\Carbon::parse($row->created_at)->format('d M Y') : 'N/A' }}</td>
                                <td class="font-monospace fw-semibold">
                                    {{ !empty($row->order) ? ($row->order->invoice_no ?? '#'.$row->order_id) : (!empty($row->invoice_no) ? $row->invoice_no : '#'.($row->order_id ?? '')) }}
                                </td>
                                <td>{{ ucfirst(str_replace('_', ' ', $row->adjustment_type ?? 'Adjustment')) }}</td>
                                <td class="text-end text-secondary font-monospace">৳{{ number_format($row->original_tax_amount ?? 0, 2) }}</td>
                                <td class="text-end fw-bold text-danger font-monospace">-৳{{ number_format($row->net_tax_change ?? 0, 2) }}</td>
                                <td class="text-secondary small">{{ $row->reason ?: ($row->reference_no ?: '-') }}</td>
                            </tr>
                        @else
                            <tr>
                                <td class="font-monospace fw-semibold">{{ $row->invoice_no }}</td>
                                <td>{{ !empty($row->created_at) ? \Carbon\Carbon::parse($row->created_at)->format('d M Y') : 'N/A' }}</td>
                                <td>{{ $row->customer_name ?: 'Guest' }} ({{ $row->customer_phone ?: 'N/A' }})</td>
                                <td class="text-end font-monospace">৳{{ number_format($row->taxable_amount ?? 0, 2) }}</td>
                                <td class="text-end text-secondary font-monospace">৳{{ number_format($row->product_tax_amount ?? 0, 2) }}</td>
                                <td class="text-end text-secondary font-monospace">৳{{ number_format($row->shipping_tax_amount ?? 0, 2) }}</td>
                                <td class="text-end fw-bold text-success font-monospace">৳{{ number_format($row->tax_amount ?? ($row->total_tax ?? 0), 2) }}</td>
                                <td class="text-end fw-bold text-dark font-monospace">৳{{ number_format($row->grand_total ?? 0, 2) }}</td>
                                <td class="text-center font-monospace">{{ !empty($row->is_pos) ? 'POS' : 'Online' }}</td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <div class="text-secondary">
                                    <i class="fas fa-file-invoice-dollar fs-2 opacity-50 mb-2 d-block"></i>
                                    <h6 class="fw-bold text-dark mb-1">No Records Found</h6>
                                    <p class="small mb-0">No records found for the selected period.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        <!-- Signature Section -->
        <div class="sign-area">
            <div class="sign-box">Prepared By (Accounts Officer)</div>
            <div class="sign-box">Checked &amp; Verified By</div>
            <div class="sign-box">Authorized Officer / Tax Consultant</div>
        </div>

        <!-- Legal Disclaimer -->
        <div class="statement-disclaimer text-center mt-4 pt-3 border-top">
            Computer-generated statement under NBR General Order No. 05/VAT/2026.
        </div>

    </div>

</body>
</html>