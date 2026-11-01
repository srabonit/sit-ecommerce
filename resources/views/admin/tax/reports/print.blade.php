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
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #0f172a;
            background: #f8fafc;
            font-size: 12px;
            line-height: 1.45;
            margin: 0;
            padding: 16px;
        }

        /* Strict 5px Radius Rule */
        .statement-wrapper,
        .statement-wrapper .card,
        .statement-wrapper .btn,
        .statement-wrapper .badge,
        .statement-wrapper .summary-card,
        .statement-wrapper .meta-box,
        .statement-wrapper .sign-box,
        .no-print .btn {
            border-radius: 5px !important;
        }

        .statement-wrapper {
            max-width: 1050px;
            margin: 0 auto;
            padding: 24px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 18px rgba(15, 23, 42, 0.04);
        }

        .statement-header {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 16px;
            margin-bottom: 18px;
        }

        .company-logo-text {
            margin: 0;
            color: #0f172a;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.5px;
            text-transform: uppercase;
        }

        .report-title-badge {
            display: inline-block;
            padding: 3px 8px;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .summary-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 10px 14px;
        }

        .metric-value {
            font-size: clamp(1rem, 1.8vw, 1.25rem);
            line-height: 1.2;
        }

        .table-statement {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
            font-size: 11.5px;
        }

        .table-statement th {
            padding: 7px 10px;
            color: #0f172a;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .table-statement td {
            padding: 7px 10px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .sign-area {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            margin-top: 50px;
            page-break-inside: avoid;
        }

        .sign-box {
            width: 210px;
            padding-top: 6px;
            border-top: 1px dashed #64748b;
            color: #475569;
            font-size: 11px;
            font-weight: 600;
            text-align: center;
        }

        /* Mobile Responsive View */
        @media (max-width: 767.98px) {
            body {
                padding: 8px;
            }

            .statement-wrapper {
                padding: 14px;
            }

            .statement-header > .d-flex {
                flex-direction: column;
                gap: 12px;
            }

            .statement-header .meta-box {
                width: 100%;
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                padding: 10px;
            }

            .sign-area {
                flex-wrap: wrap;
                justify-content: center;
                gap: 24px 12px;
                margin-top: 36px;
            }

            .sign-box {
                width: 46%;
                font-size: 10.5px;
            }

            .table-responsive-wrapper {
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .table-statement {
                min-width: 640px;
            }
        }

        /* Clean Print View */
        @media print {
            body {
                padding: 0 !important;
                color: #000 !important;
                background: #fff !important;
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

            .table-statement th {
                background: #e2e8f0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            tr {
                page-break-inside: avoid !important;
            }
        }
    </style>
</head>
<body>
    <!-- Top Action Bar (Screen Only) -->
    <div class="no-print d-flex align-items-center justify-content-between mb-3 mx-auto" style="max-width: 1050px;">
        <button type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1" onclick="window.close()">
            <i class="fas fa-arrow-left"></i> <span>Close Window</span>
        </button>
        <button type="button" class="btn btn-sm btn-primary px-3 shadow-sm d-inline-flex align-items-center gap-1" onclick="window.print()">
            <i class="fas fa-print"></i> <span>Print Statement</span>
        </button>
    </div>

    <!-- Main Statement Sheet -->
    <div class="statement-wrapper">
        <!-- Statement Header -->
        <div class="statement-header">
            <div class="d-flex justify-content-between align-items-start gap-3">
                <div>
                    <h1 class="company-logo-text">{{ $siteTitle }}</h1>
                    <div class="text-secondary small mt-1">
                        {{ $siteAddress }}<br>
                        Phone: {{ $sitePhone }} | Email: {{ $siteEmail }}<br>
                        <strong>BIN:</strong> <span class="font-monospace text-dark fw-semibold">{{ $companyBin }}</span>
                    </div>
                </div>

                <div class="text-md-end meta-box">
                    <span class="report-title-badge mb-1">NBR Audit Statement</span>
                    <h5 class="fw-bold mb-1 text-dark fs-6">{{ ucwords(str_replace('_', ' ', $reportType)) }}</h5>
                    <div class="text-secondary small">
                        <strong>Period:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}<br>
                        <strong>Generated:</strong> {{ date('d M Y, h:i A') }}<br>
                        <strong>Prepared By:</strong> {{ auth()->user()->name ?? 'System Administrator' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric Summary Strip -->
        <div class="row g-2 mb-3">
            <div class="col-6 col-md-3">
                <div class="summary-card h-100">
                    <span class="text-secondary text-uppercase fw-semibold d-block text-truncate" style="font-size: 10.5px; letter-spacing: 0.5px;">Taxable Sales</span>
                    <div class="fw-bold text-dark metric-value font-monospace text-truncate mt-1">৳{{ number_format($summary['total_taxable_amount'] ?? 0, 2) }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="summary-card h-100">
                    <span class="text-secondary text-uppercase fw-semibold d-block text-truncate" style="font-size: 10.5px; letter-spacing: 0.5px;">Total Tax</span>
                    <div class="fw-bold text-success metric-value font-monospace text-truncate mt-1">৳{{ number_format($summary['total_tax_amount'] ?? 0, 2) }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="summary-card h-100">
                    <span class="text-secondary text-uppercase fw-semibold d-block text-truncate" style="font-size: 10.5px; letter-spacing: 0.5px;">Average Rate</span>
                    <div class="fw-bold text-primary metric-value font-monospace text-truncate mt-1">
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
                <div class="summary-card h-100">
                    <span class="text-secondary text-uppercase fw-semibold d-block text-truncate" style="font-size: 10.5px; letter-spacing: 0.5px;">Total Records</span>
                    <div class="fw-bold text-dark metric-value font-monospace text-truncate mt-1">{{ number_format($summary['total_records'] ?? 0) }} Rows</div>
                </div>
            </div>
        </div>

        <!-- Data Table Container -->
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
                            <td colspan="10" class="text-center py-4 text-secondary">No records found for the selected period.</td>
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

        <!-- Legal Reference Disclaimer -->
        <div class="text-center text-secondary small mt-4 pt-3 border-top" style="font-size: 10px;">
            Computer-generated statement under NBR General Order No. 05/VAT/2026.
        </div>
    </div>
</body>
</html>