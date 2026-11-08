
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products_Catalog_{{ date('Y_m_d_His') }} - {{ config('app.name') }}</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --dark: #0f172a;
            --slate: #475569;
            --muted: #64748b;
            --border: #e2e8f0;
            --surface: #ffffff;
            --background: #f1f5f9;
            --success: #15803d;
            --danger: #dc2626;
            --warning: #a16207;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        @page {
            size: A4 landscape;
            margin: 6mm 8mm;
        }

        body {
            margin: 0;
            padding: 24px 15px;
            background: var(--background);
            color: var(--dark);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            font-size: 10px;
            line-height: 1.45;
        }

        /* Main Layout */

        .report-wrapper {
            max-width: 1200px;
            margin: 0 auto;
        }

        .print-container {
            width: 100%;
            background: var(--surface);
            padding: 24px 26px;
            border: 1px solid var(--border);
            border-radius: 6px;
            box-shadow: 0 8px 30px rgba(15, 23, 42, 0.045);
        }

        /* Toolbar */

        .no-print-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 14px;
            padding: 12px 16px;
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.025);
        }

        .toolbar-title {
            display: flex;
            align-items: center;
            gap: 11px;
            min-width: 0;
        }

        .toolbar-icon {
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: #eff6ff;
            color: var(--primary);
            border-radius: 5px;
            font-size: 14px;
        }

        .toolbar-title strong {
            display: block;
            font-size: 12px;
            color: var(--dark);
            font-weight: 700;
        }

        .toolbar-title small {
            display: block;
            color: var(--muted);
            font-size: 10px;
            margin-top: 2px;
        }

        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 8px 13px;
            min-height: 34px;
            border: 1px solid transparent;
            border-radius: 5px;
            font-size: 10.5px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all .2s ease;
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-primary:disabled {
            opacity: .7;
            cursor: not-allowed;
        }

        .btn-light {
            background: #fff;
            color: var(--slate);
            border-color: var(--border);
        }

        .btn-light:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        /* Report Header */

        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            padding-bottom: 17px;
            border-bottom: 2px solid var(--dark);
            margin-bottom: 16px;
        }

        .store-brand h2 {
            margin: 0;
            color: var(--dark);
            font-size: 19px;
            font-weight: 800;
            letter-spacing: .35px;
            text-transform: uppercase;
        }

        .store-brand p {
            margin: 5px 0 0;
            color: var(--muted);
            font-size: 9.5px;
        }

        .meta-info {
            text-align: right;
            color: var(--slate);
            font-size: 10px;
            line-height: 1.8;
        }

        .meta-info strong {
            color: var(--dark);
            font-weight: 700;
        }

        /* Report Overview */

        .report-overview {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 13px;
        }

        .overview-item {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 10px;
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 5px;
            color: var(--slate);
            font-size: 9px;
        }

        .overview-item i {
            color: var(--primary);
            font-size: 10px;
        }

        .overview-item strong {
            color: var(--dark);
            font-weight: 700;
        }

        /* Filter Section */

        .filter-section {
            padding: 10px 12px;
            margin-bottom: 15px;
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 5px;
        }

        .filter-heading {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 8px;
            color: var(--dark);
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        .filter-heading i {
            color: var(--primary);
        }

        .filter-badges-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            align-items: center;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 7px;
            border-radius: 5px;
            font-weight: 700;
            font-size: 8px;
            line-height: 1.4;
            white-space: nowrap;
        }

        .badge-dark {
            background: #e2e8f0;
            color: #334155;
            border: 1px solid #cbd5e1;
        }

        .badge-info {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }

        .badge-success {
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .badge-danger {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .badge-warn {
            background: #fffbeb;
            color: #a16207;
            border: 1px solid #fde68a;
        }

        /* Table */

        .table-wrapper {
            width: 100%;
            overflow: visible;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 9px;
        }

        .products-table th {
            padding: 9px 6px;
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .25px;
            vertical-align: middle;
            line-height: 1.4;
        }

        .products-table td {
            padding: 7px 6px;
            border: 1px solid var(--border);
            color: #334155;
            vertical-align: middle;
            overflow-wrap: anywhere;
            line-height: 1.45;
        }

        .products-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .products-table tbody tr:hover {
            background: #eff6ff;
        }

        .products-table tfoot td {
            padding: 10px 7px;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            font-weight: 700;
            font-size: 9px;
        }

        .products-table tfoot tr {
            border-top: 2px solid #94a3b8;
        }

        .product-name {
            display: block;
            color: var(--dark);
            font-size: 9.5px;
            font-weight: 700;
            line-height: 1.4;
        }

        .product-sku {
            display: block;
            margin-top: 3px;
            color: var(--muted);
            font-size: 8px;
        }

        .font-mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-muted {
            color: var(--muted);
        }

        /* Footer */

        .report-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-top: 17px;
            padding-top: 9px;
            border-top: 1px dashed #cbd5e1;
            color: #94a3b8;
            font-size: 8.5px;
        }

        /* Responsive */

        @media (max-width: 767px) {
            body {
                padding: 10px;
            }

            .no-print-toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .toolbar-actions {
                width: 100%;
            }

            .toolbar-actions .btn {
                flex: 1;
            }

            .print-container {
                padding: 15px 12px;
                overflow: hidden;
            }

            .report-header {
                align-items: flex-start;
                flex-direction: column;
                gap: 10px;
            }

            .meta-info {
                text-align: left;
            }

            .store-brand h2 {
                font-size: 16px;
            }

            .table-wrapper {
                overflow-x: auto;
            }

            .products-table {
                min-width: 950px;
            }
        }

        /* Print */

        @media print {
            html, body {
                background: #fff !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .no-print-toolbar {
                display: none !important;
            }

            .report-wrapper {
                max-width: 100% !important;
                margin: 0 !important;
            }

            .print-container {
                width: 100% !important;
                max-width: 100% !important;
                padding: 0 !important;
                border: none !important;
                border-radius: 0 !important;
                box-shadow: none !important;
            }

            .products-table th {
                background: #e2e8f0 !important;
            }

            .products-table tbody tr {
                break-inside: avoid;
            }

            .report-header {
                break-after: avoid;
            }

            .filter-section {
                break-inside: avoid;
            }
        }
    </style>
</head>

<body>

<div class="report-wrapper">

    {{-- Toolbar --}}
    <div class="no-print-toolbar">

        <div class="toolbar-title">

            <div class="toolbar-icon">
                <i class="fas fa-file-pdf"></i>
            </div>

            <div>
                <strong>Products Catalog Export Preview</strong>
                <small>
                    Matched:
                    <strong>{{ $exportProducts->count() }}</strong>
                    Products
                </small>
            </div>

        </div>

        <div class="toolbar-actions">

            <button
                type="button"
                class="btn btn-primary"
                id="btn-download-pdf"
                onclick="downloadPdfDirectly()"
            >
                <i class="fas fa-download"></i>
                <span id="btn-pdf-text">Download PDF</span>
            </button>

            <button
                type="button"
                class="btn btn-light"
                onclick="window.close()"
            >
                <i class="fas fa-times"></i>
                Close
            </button>

        </div>

    </div>


    {{-- Printable Report --}}
    <div class="print-container" id="printable-product-report">

        {{-- Header --}}
        <div class="report-header">

            <div class="store-brand">

                <h2>{{ config('app.name', 'Shop Now BD') }}</h2>

                <p>
                    Products Inventory &amp; Valuation Ledger
                    &bull;
                    Printed: {{ date('d M Y, h:i A') }}
                </p>

            </div>

            <div class="meta-info">

                <div>
                    Total Products:
                    <strong>{{ $exportProducts->count() }}</strong>
                </div>

                <div>
                    Currency:
                    <strong>BDT (৳)</strong>
                </div>

            </div>

        </div>


        {{-- Overview --}}
        <div class="report-overview">

            <div class="overview-item">
                <i class="fas fa-boxes-stacked"></i>
                Products:
                <strong>{{ $exportProducts->count() }}</strong>
            </div>

            <div class="overview-item">
                <i class="fas fa-cubes"></i>
                Stock Units:
                <strong>{{ number_format($totalStockQty ?? 0) }}</strong>
            </div>

            <div class="overview-item">
                <i class="fas fa-wallet"></i>
                Total Cost:
                <strong>৳{{ number_format($totalPurchaseCost ?? 0, 2) }}</strong>
            </div>

            <div class="overview-item">
                <i class="fas fa-chart-line"></i>
                Retail Value:
                <strong>৳{{ number_format($totalRetailValue ?? 0, 2) }}</strong>
            </div>

        </div>


        {{-- Filters --}}
        <div class="filter-section">

            <div class="filter-heading">
                <i class="fas fa-filter"></i>
                Applied Filters
            </div>

            <div class="filter-badges-bar">

                <span class="badge badge-dark">
                    Date:
                    {{ strtoupper(str_replace('_', ' ', $request->get('date_filter', 'All Time'))) }}
                </span>

                @if($request->filled('single_date'))
                    <span class="badge badge-info">
                        Single Date: {{ $request->get('single_date') }}
                    </span>
                @endif

                @if($request->filled('start_date') || $request->filled('end_date'))
                    <span class="badge badge-info">
                        Range:
                        {{ $request->get('start_date', 'Start') }}
                        &rarr;
                        {{ $request->get('end_date', 'End') }}
                    </span>
                @endif

                @if($request->filled('stock_status'))
                    <span class="badge {{ $request->get('stock_status') === 'instock' ? 'badge-success' : ($request->get('stock_status') === 'low_stock' ? 'badge-warn' : 'badge-danger') }}">
                        Stock:
                        {{ strtoupper(str_replace('_', ' ', $request->get('stock_status'))) }}
                    </span>
                @endif

                @if($request->filled('min_price') || $request->filled('max_price'))
                    <span class="badge badge-info">
                        Price:
                        ৳{{ $request->get('min_price', '0') }}
                        -
                        ৳{{ $request->get('max_price', 'Any') }}
                    </span>
                @endif

                @if($request->filled('is_featured') && $request->get('is_featured') !== 'all')
                    <span class="badge badge-warn">
                        Featured:
                        {{ $request->get('is_featured') == '1' ? 'YES' : 'NO' }}
                    </span>
                @endif

                @if($request->filled('sort_by'))
                    <span class="badge badge-dark">
                        Sorted:
                        {{ strtoupper(str_replace('_', ' ', $request->get('sort_by'))) }}
                    </span>
                @endif

                @if($request->filled('search'))
                    <span class="badge badge-info">
                        Search: "{{ $request->get('search') }}"
                    </span>
                @endif

            </div>

        </div>


        {{-- Products Table --}}
        <div class="table-wrapper">

            <table class="products-table">

                <thead>
                    <tr>
                        <th class="text-center" style="width:45px;">ID</th>
                        <th style="width:230px;">Product Name &amp; SKU</th>
                        <th style="width:120px;">Category</th>
                        <th style="width:90px;">Brand</th>
                        <th class="text-center" style="width:60px;">Type</th>
                        <th class="text-center" style="width:90px;">Stock Status</th>
                        <th class="text-end" style="width:80px;">MRP Price</th>
                        <th class="text-end" style="width:80px;">Sale Price</th>
                        <th class="text-end" style="width:80px;">Cost Price</th>
                        <th class="text-end" style="width:95px;">Total Value</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($exportProducts as $prod)

                        @php
                            $stock = (int) ($prod->stock ?? 0);
                            $regPrice = (float) ($prod->regular_price ?? 0);

                            $salePrice = !empty($prod->sale_price)
                                ? (float) $prod->sale_price
                                : null;

                            $costPrice = !empty($prod->purchase_price)
                                ? (float) $prod->purchase_price
                                : null;

                            $totalVal = $stock > 0
                                ? ($stock * $regPrice)
                                : 0;

                            if ($stock <= 0) {
                                $stockBadgeClass = 'badge-danger';
                                $stockLabel = 'Out of Stock';
                            } elseif ($stock < 6) {
                                $stockBadgeClass = 'badge-warn';
                                $stockLabel = 'Low (' . $stock . ')';
                            } else {
                                $stockBadgeClass = 'badge-success';
                                $stockLabel = 'In Stock (' . $stock . ')';
                            }
                        @endphp

                        <tr>

                            <td
                                class="text-center font-mono"
                                style="font-weight:700;color:#2563eb;"
                            >
                                #{{ $prod->id }}
                            </td>

                            <td>
                                <strong class="product-name">
                                    {{ $prod->title }}
                                </strong>

                                <small class="product-sku font-mono">
                                    SKU:
                                    {{ $prod->product_code ?? ($prod->sku ?? '—') }}
                                </small>
                            </td>

                            <td>
                                {{ $prod->category_name ?? 'Uncategorized' }}
                            </td>

                            <td>
                                {{ $prod->brand_name ?? '—' }}
                            </td>

                            <td class="text-center">
                                <span class="badge badge-info" style="font-size:7.5px;">
                                    {{ strtoupper($prod->product_type ?? 'PHYSICAL') }}
                                </span>
                            </td>

                            <td class="text-center">
                                <span class="badge {{ $stockBadgeClass }}" style="font-size:8px;">
                                    {{ $stockLabel }}
                                </span>
                            </td>

                            <td class="text-end font-mono" style="font-weight:700;color:#0f172a;">
                                ৳{{ number_format($regPrice, 2) }}
                            </td>

                            <td class="text-end font-mono" style="color:#2563eb;">
                                {{ $salePrice ? '৳' . number_format($salePrice, 2) : '—' }}
                            </td>

                            <td class="text-end font-mono" style="color:#64748b;">
                                {{ $costPrice ? '৳' . number_format($costPrice, 2) : '—' }}
                            </td>

                            <td class="text-end font-mono" style="font-weight:700;color:#15803d;">
                                ৳{{ number_format($totalVal, 2) }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="10" class="text-center" style="padding:25px;color:#94a3b8;">
                                <i class="fas fa-box-open" style="font-size:22px;display:block;margin-bottom:8px;"></i>
                                No products found matching the filter criteria.
                            </td>
                        </tr>

                    @endforelse

                </tbody>


                {{-- Grand Totals --}}
                <tfoot>

                    <tr>

                        <td
                            colspan="5"
                            style="text-align:right;text-transform:uppercase;letter-spacing:.3px;color:#334155;font-size:9px;"
                        >
                            <i class="fas fa-calculator" style="color:#2563eb;margin-right:3px;"></i>
                            Total Matched ({{ $exportProducts->count() }} Products):
                        </td>

                        <td class="text-center font-mono" style="font-size:10px;font-weight:800;color:#0f172a;">
                            {{ number_format($totalStockQty ?? 0) }} units
                        </td>

                        <td colspan="2" class="text-end text-muted" style="font-size:8px;">
                            Total Cost:
                            ৳{{ number_format($totalPurchaseCost ?? 0, 2) }}
                        </td>

                        <td colspan="2" class="text-end font-mono" style="font-size:10.5px;font-weight:800;color:#15803d;">
                            ৳{{ number_format($totalRetailValue ?? 0, 2) }}
                        </td>

                    </tr>

                </tfoot>

            </table>

        </div>


        {{-- Footer --}}
        <div class="report-footer">

            <span>
                {{ config('app.name') }}
                &bull;
                Inventory Stock Ledger
            </span>

            <span>
                Generated: {{ date('d M Y, h:i A') }}
            </span>

        </div>

    </div>

</div>


{{-- PDF Generator --}}
<script>
    function downloadPdfDirectly() {

        const btn = document.getElementById('btn-download-pdf');
        const btnText = document.getElementById('btn-pdf-text');

        const originalText = btnText.innerHTML;

        btn.disabled = true;

        btnText.innerHTML =
            '<i class="fas fa-spinner fa-spin me-1"></i> Generating PDF...';

        const element = document.getElementById('printable-product-report');

        const opt = {
            margin: [4, 4, 4, 4],

            filename: 'Products_Catalog_{{ date("Y_m_d_His") }}.pdf',

            image: {
                type: 'jpeg',
                quality: 0.98
            },

            html2canvas: {
                scale: 2,
                useCORS: true,
                logging: false,
                backgroundColor: '#ffffff',
                scrollX: 0,
                scrollY: 0
            },

            jsPDF: {
                unit: 'mm',
                format: 'a4',
                orientation: 'landscape'
            },

            pagebreak: {
                mode: ['css', 'legacy'],
                avoid: ['tr', '.report-header', '.filter-section']
            }
        };

        html2pdf()
            .set(opt)
            .from(element)
            .save()
            .then(() => {

                btn.disabled = false;
                btnText.innerHTML = originalText;

            })
            .catch(err => {

                console.error('PDF error:', err);

                btn.disabled = false;
                btnText.innerHTML = originalText;

                alert('Failed to generate PDF. Please try again.');

            });
    }
</script>

</body>
</html>
