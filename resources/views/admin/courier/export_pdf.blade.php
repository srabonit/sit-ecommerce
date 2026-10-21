<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courier_Challan_Ledger_{{ date('Y_m_d_His') }} - {{ config('app.name') }}</title>
    
    <!-- Multi-Language Typography: Plus Jakarta Sans + Hind Siliguri (300/700 Safe Bengali Digits) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;700&family=Noto+Sans+Devanagari:wght@400;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

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

        :root {
            --app-font: 'Plus Jakarta Sans', 'Hind Siliguri', 'Noto Sans Devanagari', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        body {
            font-family: var(--app-font);
            color: #0f172a;
            background: #f8fafc;
            margin: 0;
            padding: 12px 8px;
            font-size: 9.5px;
            line-height: 1.35;
            -webkit-font-smoothing: antialiased;
        }

        .print-container {
            width: 100%;
            max-width: 1120px;
            margin: 0 auto;
            background: #ffffff;
            padding: 16px 20px;
            border-radius: 8px;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.05);
            border: 1px solid #e2e8f0;
        }

        /* Non-Printable Action Toolbar */
        .no-print-toolbar {
            max-width: 1120px;
            margin: 0 auto 10px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 14px;
            background: #ffffff;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 6px 14px;
            font-size: 11.5px;
            font-weight: 600;
            border-radius: 5px !important;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .btn-primary { background-color: #2563eb; color: #ffffff; }
        .btn-primary:hover { background-color: #1d4ed8; }
        .btn-light { background-color: #f8fafc; color: #475569; border: 1px solid #cbd5e1; }
        .btn-light:hover { background-color: #f1f5f9; color: #0f172a; }

        /* Report Header */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding-bottom: 8px;
            border-bottom: 2px solid #0f172a;
            margin-bottom: 10px;
        }

        .store-brand h2 {
            margin: 0;
            font-size: 16px;
            color: #0f172a;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: -0.2px;
            line-height: 1.2;
        }
        .store-brand p {
            margin: 2px 0 0 0;
            color: #64748b;
            font-size: 9px;
            font-weight: 500;
        }

        .meta-info {
            text-align: right;
            font-size: 9px;
            color: #334155;
            font-weight: 500;
        }

        /* Compact Table Layout */
        .courier-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            table-layout: fixed;
        }

        .courier-table th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            padding: 6px 6px;
            border: 1px solid #cbd5e1;
            font-size: 8.5px;
            vertical-align: middle;
        }

        .courier-table td {
            padding: 5px 6px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .courier-table tbody tr:nth-child(even) {
            background-color: #fafbfc;
        }

        .courier-table tfoot td {
            background-color: #f8fafc;
            border: 1.5px solid #cbd5e1;
            padding: 7px 6px;
            font-weight: 700;
            font-size: 9px;
        }

        .font-mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }
        .text-end { text-align: right; }
        .text-center { text-align: center; }

        /* Safe Bengali Digits */
        .bn-digit, [data-amount] {
            font-weight: 700 !important;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }
        .badge-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .badge-warning { background: #fef9c3; color: #854d0e; border: 1px solid #fef08a; }
        .badge-danger  { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

        /* Modal Notification */
        .custom-modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.4);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        .custom-modal-card {
            background: #ffffff;
            border-radius: 8px;
            padding: 18px 22px;
            max-width: 320px;
            width: 90%;
            text-align: center;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.15);
        }

        @media print {
            body { background: #ffffff; padding: 0; }
            .no-print-toolbar, .custom-modal-backdrop { display: none !important; }
            .print-container { box-shadow: none; border: none; padding: 0; max-width: 100%; }
            .courier-table th { background-color: #f1f5f9 !important; }
        }

        @media (max-width: 767.98px) {
            .no-print-toolbar {
                flex-direction: column;
                align-items: stretch;
            }
            .no-print-toolbar .toolbar-btns {
                display: flex;
                gap: 6px;
                width: 100%;
            }
            .no-print-toolbar .toolbar-btns .btn {
                flex: 1 1 0;
                font-size: 11px;
                padding: 6px 8px;
            }
        }
    </style>
</head>
<body>

    <!-- Non-Printable Action Toolbar -->
    <div class="no-print-toolbar">
        <div>
            <strong style="color: #0f172a; font-size: 12px;">
                <i class="fas fa-file-pdf text-danger me-1"></i> Courier Delivery Challan & Consignment Sheet
            </strong>
            <span style="color: #64748b; margin-left: 8px; font-size: 11px;">
                Total: <strong class="bn-digit">{{ $bookings->count() }}</strong> Shipments
            </span>
        </div>
        <div class="toolbar-btns" style="display: flex; gap: 8px;">
            <button type="button" class="btn btn-primary" id="btn-download-pdf" onclick="downloadCourierChallanPdf()">
                <i class="fas fa-download"></i> <span id="btn-pdf-text">Download PDF (A4 Landscape)</span>
            </button>
            <button type="button" class="btn btn-light" onclick="window.close()">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
    </div>

    <!-- Main Printable Paper Container -->
    <div class="print-container" id="printable-courier-report">
        <!-- Minimal Clean Header -->
        <div class="report-header">
            <div class="store-brand">
                <h2>{{ config('app.name', 'Shop Now BD') }}</h2>
                <p>Courier Dispatch Manifest & COD Ledger &bull; Printed: <span class="bn-digit">{{ date('d M Y, h:i A') }}</span></p>
            </div>
            <div class="meta-info">
                <div>Total Parcels: <strong class="bn-digit">{{ $bookings->count() }}</strong> &bull; Currency: <strong>BDT (৳)</strong></div>
            </div>
        </div>

        <!-- Shipments Data Table -->
        <table class="courier-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 36px;">#</th>
                    <th style="width: 85px;">Invoice No</th>
                    <th style="width: 165px;">Recipient & Phone</th>
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
                            <strong style="color: #0f172a; display: block; font-size: 9.5px;">{{ $b->shipping_name }}</strong>
                            <small class="font-mono text-muted bn-digit" style="font-size: 8.5px;">{{ $b->shipping_phone }}</small>
                        </td>
                        <td>
                            <span style="font-size: 8.5px; color: #334155; line-height: 1.3; display: block;">{{ $b->shipping_address }}</span>
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

            <!-- Bottom Grand Totals Row -->
            <tfoot>
                <tr>
                    <td colspan="7" style="text-align: right; text-transform: uppercase; color: #475569; font-size: 8.5px;">
                        <i class="fas fa-calculator me-1 text-primary"></i> Total Reconciled (<span class="bn-digit">{{ $bookings->count() }}</span> Shipments):
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

        <!-- Minimal Footer -->
        <div style="margin-top: 10px; display: flex; justify-content: space-between; align-items: center; font-size: 8.5px; color: #94a3b8; border-top: 1px dashed #cbd5e1; padding-top: 5px;">
            <span>{{ config('app.name') }} &bull; Official Courier Dispatch Manifest</span>
            <span>Printed Document</span>
        </div>
    </div>

    <!-- Clean Modal (Replaces browser alert) -->
    <div class="custom-modal-backdrop" id="customAlertModal">
        <div class="custom-modal-card">
            <div style="color: #ef4444; font-size: 24px; margin-bottom: 6px;">
                <i class="fas fa-circle-exclamation"></i>
            </div>
            <h4 style="margin: 0 0 4px 0; font-size: 13px; font-weight: 700; color: #0f172a;">Notice</h4>
            <p id="customAlertMsg" style="margin: 0 0 12px 0; font-size: 11px; color: #64748b;">An error occurred.</p>
            <button type="button" class="btn btn-primary" style="width: 100%; font-size: 11px; padding: 6px 12px;" onclick="closeCustomAlert()">Acknowledge</button>
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