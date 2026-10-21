<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products_Catalog_{{ date('Y_m_d_His') }} - {{ config('app.name') }}</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- html2pdf Client-side Auto Downloader -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        @page {
            size: A4 landscape;
            margin: 6mm 8mm 6mm 8mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            color: #0f172a;
            background: #f8fafc;
            margin: 0;
            padding: 15px 0;
            font-size: 9.5px;
            line-height: 1.35;
        }

        .print-container {
            width: 100%;
            max-width: 1120px;
            margin: 0 auto;
            background: #ffffff;
            padding: 16px 20px;
            border-radius: 6px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
        }

        /* Top Action Bar (Download Button) */
        .no-print-toolbar {
            max-width: 1120px;
            margin: 0 auto 10px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 14px;
            background: #ffffff;
            border-radius: 6px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 14px;
            font-size: 11px;
            font-weight: 600;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-primary {
            background-color: #0284c7;
            color: #ffffff;
        }
        .btn-primary:hover {
            background-color: #0369a1;
        }
        .btn-light {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
        }

        /* Minimal Header */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding-bottom: 8px;
            border-bottom: 1.5px solid #0f172a;
            margin-bottom: 10px;
        }

        .store-brand h2 {
            margin: 0;
            font-size: 16px;
            color: #0f172a;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .store-brand p {
            margin: 2px 0 0 0;
            color: #64748b;
            font-size: 9px;
        }

        .meta-info {
            text-align: right;
            font-size: 9.5px;
            color: #334155;
        }

        /* Compact Filter Bar */
        .filter-badges-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            align-items: center;
            padding: 5px 8px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            margin-bottom: 10px;
            font-size: 9px;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: 600;
            font-size: 8.5px;
            text-transform: uppercase;
        }
        .badge-dark   { background: #1e293b; color: #fff; }
        .badge-info   { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
        .badge-success{ background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .badge-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        .badge-warn   { background: #fef9c3; color: #854d0e; border: 1px solid #fef08a; }

        /* Compact Table */
        .products-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            table-layout: fixed;
        }

        .products-table th {
            background-color: #f1f5f9;
            color: #1e293b;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            padding: 6px 5px;
            border: 1px solid #cbd5e1;
            font-size: 8.5px;
            vertical-align: middle;
        }

        .products-table td {
            padding: 5px 5px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .products-table tbody tr:nth-child(even) {
            background-color: #fafbfc;
        }

        /* Bottom Summary Tfoot */
        .products-table tfoot td {
            background-color: #f8fafc;
            border: 1.5px solid #cbd5e1;
            padding: 7px 6px;
            font-weight: 700;
            font-size: 9.5px;
        }

        .font-mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }
        .text-end { text-align: right; }
        .text-center { text-align: center; }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .no-print-toolbar {
                display: none !important;
            }
            .print-container {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            .products-table th {
                background-color: #e2e8f0 !important;
            }
        }
    </style>
</head>
<body>

    <!-- Non-Printable Action Toolbar -->
    <div class="no-print-toolbar">
        <div>
            <strong style="color: #0f172a; font-size: 12px;">
                <i class="fas fa-file-pdf text-danger me-1"></i> Products Catalog Export Preview
            </strong>
            <span style="color: #64748b; margin-left: 8px; font-size: 11px;">
                Matched: <strong>{{ $exportProducts->count() }}</strong> Products
            </span>
        </div>
        <div style="display: flex; gap: 8px;">
            <button type="button" class="btn btn-primary" id="btn-download-pdf" onclick="downloadPdfDirectly()">
                <i class="fas fa-download"></i> <span id="btn-pdf-text">Download PDF (A4 Landscape)</span>
            </button>
            <button type="button" class="btn btn-light" onclick="window.close()">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
    </div>

    <!-- Main Printable Paper Container -->
    <div class="print-container" id="printable-product-report">
        <!-- Minimal Clean Header -->
        <div class="report-header">
            <div class="store-brand">
                <h2>{{ config('app.name', 'Shop Now BD') }}</h2>
                <p>Products Inventory & Valuation Ledger &bull; Printed: {{ date('d M Y, h:i A') }}</p>
            </div>
            <div class="meta-info">
                <div>Total Products: <strong>{{ $exportProducts->count() }}</strong> &bull; Currency: <strong>BDT (৳)</strong></div>
            </div>
        </div>

        <!-- Filter Badges -->
        <div class="filter-badges-bar">
            <strong style="color: #475569;">Filter:</strong>
            
            <span class="badge badge-dark">
                Date: {{ strtoupper(str_replace('_', ' ', $request->get('date_filter', 'All Time'))) }}
            </span>

            @if($request->filled('single_date'))
                <span class="badge badge-info">
                    Single Date: {{ $request->get('single_date') }}
                </span>
            @endif

            @if($request->filled('start_date') || $request->filled('end_date'))
                <span class="badge badge-info">
                    Range: {{ $request->get('start_date', 'Start') }} &rarr; {{ $request->get('end_date', 'End') }}
                </span>
            @endif

            @if($request->filled('stock_status'))
                <span class="badge {{ $request->get('stock_status') === 'instock' ? 'badge-success' : ($request->get('stock_status') === 'low_stock' ? 'badge-warn' : 'badge-danger') }}">
                    Stock: {{ strtoupper(str_replace('_', ' ', $request->get('stock_status'))) }}
                </span>
            @endif

            @if($request->filled('min_price') || $request->filled('max_price'))
                <span class="badge badge-info">
                    Price: ৳{{ $request->get('min_price', '0') }} - ৳{{ $request->get('max_price', 'Any') }}
                </span>
            @endif

            @if($request->filled('is_featured') && $request->get('is_featured') !== 'all')
                <span class="badge badge-warn">
                    Featured: {{ $request->get('is_featured') == '1' ? 'YES' : 'NO' }}
                </span>
            @endif

            @if($request->filled('sort_by'))
                <span class="badge badge-dark">
                    Sorted: {{ strtoupper(str_replace('_', ' ', $request->get('sort_by'))) }}
                </span>
            @endif

            @if($request->filled('search'))
                <span class="badge badge-info">
                    Search: "{{ $request->get('search') }}"
                </span>
            @endif
        </div>

        <!-- Products Data Table -->
        <table class="products-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 45px;">ID</th>
                    <th style="width: 230px;">Product Name & SKU</th>
                    <th style="width: 120px;">Category</th>
                    <th style="width: 90px;">Brand</th>
                    <th class="text-center" style="width: 60px;">Type</th>
                    <th class="text-center" style="width: 90px;">Stock Status</th>
                    <th class="text-end" style="width: 80px;">MRP Price</th>
                    <th class="text-end" style="width: 80px;">Sale Price</th>
                    <th class="text-end" style="width: 80px;">Cost Price</th>
                    <th class="text-end" style="width: 95px;">Total Value</th>
                </tr>
            </thead>
            <tbody>
                @forelse($exportProducts as $prod)
                    @php
                        $stock = (int) ($prod->stock ?? 0);
                        $regPrice = (float) ($prod->regular_price ?? 0);
                        $salePrice = !empty($prod->sale_price) ? (float) $prod->sale_price : null;
                        $costPrice = !empty($prod->purchase_price) ? (float) $prod->purchase_price : null;
                        $totalVal = $stock > 0 ? ($stock * $regPrice) : 0;

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
                        <td class="text-center font-mono" style="font-weight: 700; color: #0284c7;">
                            #{{ $prod->id }}
                        </td>
                        <td>
                            <strong style="color: #0f172a; display: block; font-size: 9.5px;">
                                {{ $prod->title }}
                            </strong>
                            <small class="text-muted font-mono" style="font-size: 8px;">
                                SKU: {{ $prod->product_code ?? ($prod->sku ?? '—') }}
                            </small>
                        </td>
                        <td class="text-truncate">
                            {{ $prod->category_name ?? 'Uncategorized' }}
                        </td>
                        <td class="text-truncate">
                            {{ $prod->brand_name ?? '—' }}
                        </td>
                        <td class="text-center">
                            <span class="badge badge-info" style="font-size: 7.5px;">
                                {{ strtoupper($prod->product_type ?? 'PHYSICAL') }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $stockBadgeClass }}" style="font-size: 8px;">
                                {{ $stockLabel }}
                            </span>
                        </td>
                        <td class="text-end font-mono" style="font-weight: 700; color: #0f172a;">
                            ৳{{ number_format($regPrice, 2) }}
                        </td>
                        <td class="text-end font-mono" style="color: #0284c7;">
                            {{ $salePrice ? '৳' . number_format($salePrice, 2) : '—' }}
                        </td>
                        <td class="text-end font-mono" style="color: #64748b;">
                            {{ $costPrice ? '৳' . number_format($costPrice, 2) : '—' }}
                        </td>
                        <td class="text-end font-mono" style="font-weight: 700; color: #15803d;">
                            ৳{{ number_format($totalVal, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center" style="padding: 20px; color: #94a3b8;">
                            No products found matching the filter criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>

            <!-- Bottom Grand Totals Summary Row -->
            <tfoot>
                <tr>
                    <td colspan="5" style="text-align: right; text-transform: uppercase; letter-spacing: 0.3px; color: #334155; font-size: 9px;">
                        <i class="fas fa-calculator" style="color: #0284c7; margin-right: 3px;"></i> Total Matched ({{ $exportProducts->count() }} Products):
                    </td>
                    <td class="text-center font-mono" style="font-size: 10px; font-weight: 800; color: #0f172a;">
                        {{ number_format($totalStockQty ?? 0) }} units
                    </td>
                    <td colspan="2" class="text-end text-muted small" style="font-size: 8px;">
                        Total Cost: ৳{{ number_format($totalPurchaseCost ?? 0, 2) }}
                    </td>
                    <td colspan="2" class="text-end font-mono" style="font-size: 10.5px; font-weight: 800; color: #15803d;">
                        ৳{{ number_format($totalRetailValue ?? 0, 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Minimal Footer -->
        <div style="margin-top: 12px; display: flex; justify-content: space-between; align-items: center; font-size: 8.5px; color: #94a3b8; border-top: 1px dashed #cbd5e1; padding-top: 5px;">
            <span>{{ config('app.name') }} &bull; Inventory Stock Ledger</span>
            <span>Page 1 of 1</span>
        </div>
    </div>

    <script>
        function downloadPdfDirectly() {
            const btn = document.getElementById('btn-download-pdf');
            const btnText = document.getElementById('btn-pdf-text');
            const originalText = btnText.innerHTML;

            btn.disabled = true;
            btnText.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Generating PDF...';

            const element = document.getElementById('printable-product-report');

            const opt = {
                margin:       [4, 4, 4, 4],
                filename:     'Products_Catalog_{{ date("Y_m_d_His") }}.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, logging: false },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' },
                pagebreak:    { mode: ['avoid-all', 'css', 'legacy'] }
            };

            html2pdf().set(opt).from(element).save().then(() => {
                btn.disabled = false;
                btnText.innerHTML = originalText;
            }).catch(err => {
                console.error('PDF error:', err);
                btn.disabled = false;
                btnText.innerHTML = originalText;
                alert('Failed to generate PDF. Please try again.');
            });
        }
    </script>

</body>
</html>