<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courier_Challan_Ledger_{{ date('Y_m_d_His') }} - {{ config('app.name') }}</title>

    <!-- Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Noto+Sans+Devanagari:wght@400;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- PDF Downloader -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        @page {
            size: A4 landscape;
            margin: 6mm 8mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        :root {
            --app-font: 'Plus Jakarta Sans', 'Hind Siliguri', 'Noto Sans Devanagari', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            --primary: #1572e8;
            --primary-dark: #1264cc;
            --text: #1d2939;
            --muted: #667085;
            --border: #e5e7eb;
            --surface: #ffffff;
            --soft: #f8fafc;
            --radius: 5px;
            --shadow: 0 1px 3px rgba(16, 24, 40, 0.05);
        }

        html {
            min-width: 320px;
        }

        body {
            margin: 0;
            padding: 16px 12px;
            background: #f3f5f9;
            color: var(--text);
            font-family: var(--app-font);
            font-size: 10px;
            line-height: 1.4;
            -webkit-font-smoothing: antialiased;
        }

        /* Utility classes */
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .text-muted { color: var(--muted); }
        .text-primary { color: var(--primary); }
        .text-danger { color: #dc3545; }
        .text-capitalize { text-transform: capitalize; }
        .font-mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }

        .bn-digit,
        [data-amount] {
            font-weight: 700 !important;
        }

        .me-1 { margin-right: 4px; }
        .ms-1 { margin-left: 4px; }
        .py-4 { padding-top: 16px; padding-bottom: 16px; }

        /* =====================================================
           SCREEN TOOLBAR
        ===================================================== */

        .no-print-toolbar {
            width: 100%;
            max-width: 1120px;
            margin: 0 auto 12px;
            padding: 10px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .toolbar-info {
            min-width: 0;
        }

        .toolbar-title {
            display: block;
            color: var(--text);
            font-size: 12px;
            font-weight: 700;
            line-height: 1.5;
        }

        .toolbar-count {
            display: inline-block;
            margin-top: 3px;
            color: var(--muted);
            font-size: 10.5px;
        }

        .toolbar-btns {
            display: flex;
            align-items: center;
            gap: 7px;
            flex-shrink: 0;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 34px;
            padding: 7px 13px;
            border: 1px solid transparent;
            border-radius: var(--radius);
            font-family: inherit;
            font-size: 11px;
            font-weight: 600;
            line-height: 1.3;
            text-decoration: none;
            white-space: nowrap;
            cursor: pointer;
            transition: background-color 0.15s ease, border-color 0.15s ease, transform 0.15s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            transform: none;
        }

        .btn-primary {
            color: #fff;
            background: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            color: #fff;
            background: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .btn-light {
            color: #475467;
            background: #fff;
            border-color: #d0d5dd;
        }

        .btn-light:hover {
            color: var(--text);
            background: #f8fafc;
            border-color: #98a2b3;
        }

        /* =====================================================
           PRINTABLE REPORT
        ===================================================== */

        .print-container {
            width: 100%;
            max-width: 1120px;
            margin: 0 auto;
            padding: 18px 20px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        /* Report Header */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 16px;
            padding-bottom: 9px;
            margin-bottom: 12px;
            border-bottom: 2px solid #1d2939;
        }

        .store-brand {
            min-width: 0;
        }

        .store-brand h2 {
            margin: 0;
            color: #1d2939;
            font-size: 17px;
            font-weight: 800;
            letter-spacing: -0.35px;
            line-height: 1.25;
            text-transform: uppercase;
            overflow-wrap: anywhere;
        }

        .store-brand p {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: 9px;
            font-weight: 500;
            line-height: 1.5;
        }

        .meta-info {
            flex-shrink: 0;
            color: #475467;
            font-size: 9px;
            font-weight: 500;
            text-align: right;
            line-height: 1.5;
        }

        /* =====================================================
           TABLE
        ===================================================== */

        .table-scroll {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f8fafc;
        }

        .table-scroll::-webkit-scrollbar {
            height: 6px;
        }

        .table-scroll::-webkit-scrollbar-track {
            background: #f8fafc;
        }

        .table-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .courier-table {
            width: 100%;
            min-width: 1050px;
            margin: 0;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 9px;
        }

        .courier-table th {
            padding: 7px 6px;
            color: #475467;
            background: #f2f4f7;
            border: 1px solid #d0d5dd;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 0.025em;
            line-height: 1.35;
            text-align: left;
            text-transform: uppercase;
            vertical-align: middle;
            overflow-wrap: break-word;
        }

        .courier-table td {
            padding: 6px;
            color: #344054;
            background: #fff;
            border: 1px solid #e4e7ec;
            font-size: 8.5px;
            line-height: 1.4;
            vertical-align: middle;
            overflow-wrap: anywhere;
            word-break: normal;
        }

        .courier-table tbody tr:nth-child(even) td {
            background: #fafbfc;
        }

        .courier-table tbody tr:hover td {
            background: #f5f9ff;
        }

        .courier-table tfoot td {
            padding: 8px 6px;
            color: #1d2939;
            background: #f2f4f7;
            border: 1px solid #cbd5e1;
            font-size: 8.5px;
            font-weight: 700;
            line-height: 1.4;
        }

        .courier-table strong {
            font-weight: 700;
        }

        /* Status Badges */
        .badge {
            display: inline-block;
            max-width: 100%;
            padding: 3px 6px;
            border: 1px solid transparent;
            border-radius: 4px;
            font-size: 7.5px;
            font-weight: 700;
            letter-spacing: 0.1px;
            line-height: 1.3;
            text-align: center;
            text-transform: uppercase;
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .badge-success {
            color: #15803d;
            background: #dcfce7;
            border-color: #bbf7d0;
        }

        .badge-warning {
            color: #854d0e;
            background: #fef9c3;
            border-color: #fef08a;
        }

        .badge-danger {
            color: #b91c1c;
            background: #fee2e2;
            border-color: #fecaca;
        }

        /* =====================================================
           REPORT FOOTER
        ===================================================== */

        .report-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-top: 12px;
            padding-top: 7px;
            color: #98a2b3;
            border-top: 1px dashed #d0d5dd;
            font-size: 8px;
            line-height: 1.4;
        }

        /* =====================================================
           CUSTOM ALERT
        ===================================================== */

        .custom-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
            background: rgba(16, 24, 40, 0.48);
        }

        .custom-modal-card {
            width: 100%;
            max-width: 340px;
            padding: 22px;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: 0 16px 40px rgba(16, 24, 40, 0.2);
            text-align: center;
        }

        .custom-modal-icon {
            margin-bottom: 9px;
            color: #dc3545;
            font-size: 26px;
        }

        .custom-modal-card h4 {
            margin: 0 0 5px;
            color: var(--text);
            font-size: 14px;
            font-weight: 700;
        }

        .custom-modal-card p {
            margin: 0 0 15px;
            color: var(--muted);
            font-size: 11px;
            line-height: 1.6;
        }

        .custom-modal-card .btn {
            width: 100%;
        }

        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 767.98px) {
            body {
                padding: 10px 8px;
            }

            .no-print-toolbar {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
                padding: 11px;
            }

            .toolbar-info {
                width: 100%;
            }

            .toolbar-title {
                font-size: 11.5px;
            }

            .toolbar-count {
                font-size: 10px;
            }

            .toolbar-btns {
                display: flex;
                width: 100%;
                gap: 7px;
            }

            .toolbar-btns .btn {
                flex: 1 1 0;
                min-width: 0;
                padding: 7px 6px;
                font-size: 10px;
            }

            .print-container {
                padding: 12px;
            }

            .report-header {
                align-items: flex-start;
                gap: 10px;
            }

            .store-brand h2 {
                font-size: 14px;
            }

            .store-brand p {
                font-size: 8px;
            }

            .meta-info {
                font-size: 8px;
            }

            .report-footer {
                align-items: flex-start;
                font-size: 7.5px;
            }
        }

        @media (max-width: 420px) {
            body {
                padding: 8px 5px;
            }

            .no-print-toolbar {
                padding: 9px;
            }

            .toolbar-title {
                font-size: 10.5px;
            }

            .toolbar-btns .btn {
                font-size: 9.5px;
            }

            .print-container {
                padding: 9px;
            }

            .report-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .meta-info {
                text-align: left;
            }
        }

        /* =====================================================
           PRINT
        ===================================================== */

        @media print {
            html,
            body {
                width: 100%;
                min-width: 0;
                margin: 0;
                padding: 0;
                background: #fff !important;
            }

            .no-print-toolbar,
            .custom-modal-backdrop {
                display: none !important;
            }

            .print-container {
                width: 100%;
                max-width: none;
                margin: 0;
                padding: 0;
                overflow: visible;
                border: 0;
                border-radius: 0;
                box-shadow: none;
            }

            .report-header {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .table-scroll {
                width: 100%;
                overflow: visible !important;
            }

            .courier-table {
                width: 100%;
                min-width: 0;
                table-layout: fixed;
                font-size: 8px;
            }

            .courier-table th {
                padding: 5px 4px;
                color: #344054 !important;
                background: #f1f5f9 !important;
                font-size: 7.5px;
            }

            .courier-table td {
                padding: 4px;
                font-size: 7.5px;
            }

            .courier-table tfoot td {
                padding: 5px 4px;
                background: #f1f5f9 !important;
                font-size: 7.5px;
            }

            .badge {
                font-size: 6.5px;
                padding: 2px 4px;
            }

            .report-footer {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body>

    <!-- Non-Printable Action Toolbar -->
    <div class="no-print-toolbar">

        <div class="toolbar-info">
            <strong class="toolbar-title">
                <i class="fas fa-file-pdf text-danger me-1"></i>
                Courier Delivery Challan &amp; Consignment Sheet
            </strong>

            <span class="toolbar-count">
                Total:
                <strong class="bn-digit">{{ $bookings->count() }}</strong>
                Shipments
            </span>
        </div>

        <div class="toolbar-btns">
            <button type="button" class="btn btn-primary" id="btn-download-pdf" onclick="downloadCourierChallanPdf()">
                <i class="fas fa-download"></i>
                <span id="btn-pdf-text">Download PDF (A4 Landscape)</span>
            </button>

            <button type="button" class="btn btn-light" onclick="window.close()">
                <i class="fas fa-times"></i>
                Close
            </button>
        </div>

    </div>

    <!-- Main Printable Paper -->
    <div class="print-container" id="printable-courier-report">

        <!-- Report Header -->
        <div class="report-header">

            <div class="store-brand">
                <h2>{{ config('app.name', 'Shop Now BD') }}</h2>

                <p>
                    Courier Dispatch Manifest &amp; COD Ledger
                    &bull;
                    Printed:
                    <span class="bn-digit">{{ date('d M Y, h:i A') }}</span>
                </p>
            </div>

            <div class="meta-info">
                <div>
                    Total Parcels:
                    <strong class="bn-digit">{{ $bookings->count() }}</strong>
                    &bull;
                    Currency:
                    <strong>BDT (৳)</strong>
                </div>
            </div>

        </div>

        <!-- Shipments Data Table -->
        <div class="table-scroll">
            <table class="courier-table">

                <thead>
                    <tr>
                        <th class="text-center" style="width: 36px;">#</th>
                        <th style="width: 85px;">Invoice No</th>
                        <th style="width: 165px;">Recipient &amp; Phone</th>
                        <th style="width: 185px;">Delivery Address</th>
                        <th style="width: 95px;">Courier</th>
                        <th style="width: 105px;">Tracking / CN</th>
                        <th class="text-center" style="width: 55px;">Weight</th>
                        <th class="text-end" style="width: 85px;">Order Total</th>
                        <th class="text-end" style="width: 85px;">COD Amount</th>
                        <th class="text-center" style="width: 85px;">Status</th>
                        <th class="text-center" style="width: 85px;">Settlement</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($bookings as $idx => $b)
                        @php
                            $st = strtolower($b->delivery_status ?? 'in_review');
                            $stBadge = match($st) {
                                'delivered', 'completed' => 'badge-success',
                                'returned', 'return', 'cancelled' => 'badge-danger',
                                default => 'badge-warning'
                            };
                            $codSt = strtolower($b->cod_settlement_status ?? 'unsettled');
                            $codBadge = match($codSt) {
                                'settled' => 'badge-success',
                                'partial' => 'badge-warning',
                                default => 'badge-danger'
                            };
                        @endphp

                        <tr>
                            <td class="text-center font-mono bn-digit" style="font-weight: 700; color: #2563eb;">
                                {{ $idx + 1 }}
                            </td>

                            <td class="font-mono bn-digit" style="font-weight: 700;">
                                #{{ $b->invoice_no ?? $b->order_id }}
                            </td>

                            <td>
                                <strong style="color: #0f172a; display: block; font-size: 9.5px;">
                                    {{ $b->shipping_name }}
                                </strong>

                                <small class="font-mono text-muted bn-digit" style="font-size: 8.5px;">
                                    {{ $b->shipping_phone }}
                                </small>
                            </td>

                            <td>
                                <span style="font-size: 8.5px; color: #334155; line-height: 1.3; display: block;">
                                    {{ $b->shipping_address }}
                                </span>
                            </td>

                            <td class="text-capitalize" style="font-weight: 600;">
                                {{ $b->courier_name }}
                            </td>

                            <td class="font-mono bn-digit" style="font-size: 8.5px; font-weight: 700;">
                                {{ $b->tracking_code }}
                            </td>

                            <td class="text-center font-mono bn-digit">
                                {{ number_format((float)($b->parcel_weight ?? 0.5), 1) }} kg
                            </td>

                            <td class="text-end font-mono bn-digit">
                                ৳{{ number_format((float)($b->order_amount ?? 0), 2) }}
                            </td>

                            <td class="text-end font-mono bn-digit" style="color: #2563eb; font-weight: 700;">
                                ৳{{ number_format((float)($b->cod_amount ?? 0), 2) }}
                            </td>

                            <td class="text-center">
                                <span class="badge {{ $stBadge }}">
                                    {{ str_replace('_', ' ', $b->delivery_status ?? 'IN REVIEW') }}
                                </span>
                            </td>

                            <td class="text-center">
                                <span class="badge {{ $codBadge }}">
                                    {{ str_replace('_', ' ', $b->cod_settlement_status ?? 'UNSETTLED') }}
                                </span>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-4 text-muted" style="font-size: 10px;">
                                No shipments found matching the criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                <!-- Grand Totals -->
                <tfoot>
                    <tr>
                        <td colspan="7" style="text-align: right; text-transform: uppercase; color: #475569; font-size: 8.5px;">
                            <i class="fas fa-calculator me-1 text-primary"></i>
                            Total Reconciled
                            (<span class="bn-digit">{{ $bookings->count() }}</span> Shipments):
                        </td>

                        <td class="text-end font-mono bn-digit" style="font-size: 9px; font-weight: 700; color: #0f172a;">
                            Cost: ৳{{ number_format($totalCostSum ?? 0, 2) }}
                        </td>

                        <td class="text-end font-mono bn-digit" style="font-size: 9.5px; font-weight: 700; color: #2563eb;">
                            ৳{{ number_format($totalCodSum ?? 0, 2) }}
                        </td>

                        <td colspan="2" class="text-end font-mono bn-digit" style="font-size: 9.5px; font-weight: 700; color: #15803d;">
                            Settled: ৳{{ number_format($totalSettledSum ?? 0, 2) }}
                        </td>
                    </tr>
                </tfoot>

            </table>
        </div>

        <!-- Report Footer -->
        <div class="report-footer">
            <span>
                {{ config('app.name') }}
                &bull;
                Official Courier Dispatch Manifest
            </span>

            <span>Printed Document</span>
        </div>

    </div>

    <!-- Custom Alert Modal -->
    <div class="custom-modal-backdrop" id="customAlertModal">
        <div class="custom-modal-card">

            <div class="custom-modal-icon">
                <i class="fas fa-circle-exclamation"></i>
            </div>

            <h4>Notice</h4>

            <p id="customAlertMsg">An error occurred.</p>

            <button type="button" class="btn btn-primary" onclick="closeCustomAlert()">
                Acknowledge
            </button>

        </div>
    </div>

    <script>
        function showCustomAlert(msg) {
            document.getElementById('customAlertMsg').innerText = msg;
            document.getElementById('customAlertModal').style.display = 'flex';
        }

        function closeCustomAlert() {
            document.getElementById('customAlertModal').style.display = 'none';
        }

        function downloadCourierChallanPdf() {
            const btn = document.getElementById('btn-download-pdf');
            const btnText = document.getElementById('btn-pdf-text');
            const originalText = btnText.innerHTML;

            btn.disabled = true;
            btnText.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Generating PDF...';

            const element = document.getElementById('printable-courier-report');

            const opt = {
                margin:       [4, 4, 4, 4],
                filename:     'Courier_Challan_{{ date("Y_m_d_His") }}.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, logging: false },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' },
                pagebreak:    { mode: ['avoid-all', 'css', 'legacy'] }
            };

            html2pdf().set(opt).from(element).save().then(() => {
                btn.disabled = false;
                btnText.innerHTML = originalText;
            }).catch(err => {
                console.error('PDF generation error:', err);
                btn.disabled = false;
                btnText.innerHTML = originalText;
                showCustomAlert('Failed to generate PDF. Please try again.');
            });
        }
    </script>

</body>
</html>