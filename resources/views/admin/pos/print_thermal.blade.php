@php
    $siteSettings = \Illuminate\Support\Facades\DB::table('settings')
        ->whereIn('key', [
            'footer_brand_title', 'footer_phone', 'footer_email', 
            'site_title', 'logo_title', 'company_address', 'contact_address', 
            'company_bin', 'bin_number', 'vat_label', 'pos_vat_display_mode', 
            'enable_pos_vat', 'vat_enabled', 'enable_vat'
        ])
        ->pluck('value', 'key')
        ->toArray();

    $siteName = !empty($siteSettings['footer_brand_title']) 
        ? $siteSettings['footer_brand_title'] 
        : ($siteTitle ?? ($siteSettings['site_title'] ?? ($siteSettings['logo_title'] ?? config('app.name', 'SHOP NOW BD ONLINE'))));

    $sitePhone = !empty($order->branch_phone) ? $order->branch_phone : ($siteSettings['footer_phone'] ?? '');
    $siteEmail = !empty($siteSettings['footer_email']) ? $siteSettings['footer_email'] : '';
    $branchAddress = !empty($order->branch_address) ? $order->branch_address : ($siteSettings['company_address'] ?? ($siteSettings['contact_address'] ?? ''));

    // Central & Branch BIN Resolver
    $companyBin = !empty($companyBin) 
        ? $companyBin 
        : (!empty($order->branch_bin) 
            ? $order->branch_bin 
            : ($order->bin_number ?? ($siteSettings['company_bin'] ?? ($siteSettings['bin_number'] ?? '001293847-0101'))));

    $vatLabelText = !empty($vatLabel) ? $vatLabel : (!empty($siteSettings['vat_label']) ? $siteSettings['vat_label'] : 'Govt. VAT');
    $posVatDisplayMode = $siteSettings['pos_vat_display_mode'] ?? 'item_breakdown';

    $subtotalAmt   = (float)($order->subtotal ?? ($order->sub_total ?? 0));
    $grandTotalAmt = (float)($order->grand_total ?? ($order->total_amount ?? ($order->total ?? 0)));
    $discountAmt   = (float)($order->discount_amount ?? ($order->discount ?? 0));
    $itemsList     = !empty($items) ? $items : [];

    // ১. Delivery Charge (Strict: Input > 0 thaklei asbe, noyto 0)
    $deliveryFee = 0.0;
    if (isset($order->shipping_charge) && is_numeric($order->shipping_charge) && (float)$order->shipping_charge > 0) {
        $deliveryFee = (float)$order->shipping_charge;
    } elseif (isset($order->delivery_charge) && is_numeric($order->delivery_charge) && (float)$order->delivery_charge > 0) {
        $deliveryFee = (float)$order->delivery_charge;
    } elseif (isset($order->shipping_cost) && is_numeric($order->shipping_cost) && (float)$order->shipping_cost > 0) {
        $deliveryFee = (float)$order->shipping_cost;
    }
    $deliveryFee = round($deliveryFee, 2);

    // ২. Strict VAT Resolution (0% ba 0 hole kono bhabei tax asbe na)
    $taxAmt = 0.0;
    if (isset($order->tax_amount) && !is_null($order->tax_amount)) {
        $taxAmt = (float)$order->tax_amount;
    } elseif (isset($order->product_tax_amount) && !is_null($order->product_tax_amount)) {
        $taxAmt = (float)$order->product_tax_amount;
    }
    $taxAmt = round(max(0.0, $taxAmt), 2);

    $isTaxInclusive = (bool)($order->is_tax_inclusive ?? false);

    // ৩. Total & Paid Amount 100% Sync (Order Paid hole Due kokhono asbe na)
    $paymentStatus = strtolower($order->payment_status ?? '');
    if (in_array($paymentStatus, ['paid', 'completed'])) {
        $paidAmt = $grandTotalAmt;
        $dueAmt  = 0.0;
    } else {
        $paidAmt = (float)($order->paid_amount ?? 0);
        $dueAmt  = (float)($order->due_amount ?? max(0, $grandTotalAmt - $paidAmt));
        if ($paidAmt >= $grandTotalAmt) {
            $paidAmt = $grandTotalAmt;
            $dueAmt  = 0.0;
        }
    }

    $customerName    = $order->customer_name ?? ($order->name ?? ($order->shipping_name ?? 'Walk-in Customer'));
    $customerPhone   = $order->customer_phone ?? ($order->phone ?? ($order->shipping_phone ?? ''));
    $customerAddress = $order->customer_address ?? ($order->address ?? ($order->shipping_address ?? ($order->bio ?? '')));

    $cleanInvoiceNo = preg_replace('/[^A-Za-z0-9_-]/', '', $order->invoice_no ?? ('POS-' . $order->id));
    $verifyUrl      = \Illuminate\Support\Facades\Route::has('order.confirmation') 
        ? route('order.confirmation', $cleanInvoiceNo) 
        : url('/order/confirmation/' . $cleanInvoiceNo);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Receipt #{{ $cleanInvoiceNo }}</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body {
            background-color: #f1f5f9;
            margin: 0;
            padding: 24px 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Courier New", monospace;
            color: #000;
        }

        .no-print-bar {
            width: 100%;
            max-width: 80mm;
            margin: 0 auto 14px auto;
            display: flex;
            gap: 6px;
            justify-content: center;
        }
        .action-btn {
            background: #0f172a;
            color: #fff;
            border: none;
            padding: 7px 13px;
            font-size: 11px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: background 0.2s;
        }
        .action-btn:hover { background: #334155; }
        .action-btn.secondary { background: #475569; }

        .thermal-card {
            width: 100%;
            max-width: 80mm;
            margin: 0 auto;
            background: #ffffff;
            padding: 14px 14px;
            border: 1px dashed #94a3b8;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
            font-size: 11px;
            line-height: 1.35;
        }

        .thermal-card.paper-58mm {
            max-width: 58mm !important;
            font-size: 9.5px !important;
            padding: 10px 8px !important;
        }

        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .font-monospace { font-family: monospace, Courier, monospace; }
        
        .dashed-line {
            border-top: 1px dashed #000;
            margin: 7px 0;
        }

        table.receipt-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        table.receipt-table th {
            border-bottom: 1px dashed #000;
            padding: 4px 2px;
            font-size: 10px;
            text-transform: uppercase;
        }
        table.receipt-table td {
            padding: 4px 2px;
            vertical-align: top;
            font-size: 10px;
        }

        .qr-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin: 8px 0 4px 0;
        }
        .qr-wrapper img, .qr-wrapper canvas {
            display: block;
            margin: 0 auto;
        }

        @page {
            size: 80mm auto;
            margin: 0;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print-bar {
                display: none !important;
            }
            .thermal-card {
                border: none !important;
                box-shadow: none !important;
                max-width: 100% !important;
                width: 100% !important;
                padding: 6px 8px !important;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <button type="button" class="action-btn" onclick="window.print()">
            🖨️ Print
        </button>
        <button type="button" id="btn-save-pdf" class="action-btn" onclick="downloadReceiptPDF()">
            📥 Save
        </button>
        <button type="button" class="action-btn" onclick="togglePaperSize()">
            🔄 80mm/58mm
        </button>
    </div>

    <div id="thermal-receipt-element" class="thermal-card">
        
        <div class="text-center">
            <h3 style="margin: 0; font-size: 15px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">
                {{ $siteName }}
            </h3>

            <div style="font-size: 9.5px; color: #334155; margin-top: 2px;">
                {{ !empty($order->branch_name) ? $order->branch_name : 'Main Outlet' }}
                @if(!empty($branchAddress)) | {{ $branchAddress }} @endif
            </div>

            @if(!empty($companyBin))
                <div style="font-size: 9px; color: #1e293b; margin-top: 1px; font-family: monospace;">
                    BIN: <strong>{{ $companyBin }}</strong>
                </div>
            @endif

            @if(!empty($sitePhone) || !empty($siteEmail))
                <div style="font-size: 9px; color: #334155; margin-top: 1px;">
                    @if(!empty($sitePhone)) Hotline: {{ $sitePhone }} @endif
                    @if(!empty($sitePhone) && !empty($siteEmail)) | @endif
                    @if(!empty($siteEmail)) {{ $siteEmail }} @endif
                </div>
            @endif
        </div>

        <div class="dashed-line"></div>

        <div style="font-size: 10px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 4px;">
                <span>Invoice: <strong class="font-monospace">#{{ $cleanInvoiceNo }}</strong></span>
                <span style="font-size: 9.5px; color: #334155;">{{ function_exists('format_datetime') ? format_datetime($order->created_at, 'd-M-Y h:i A') : date('d-M-Y h:i A', strtotime($order->created_at)) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-top: 3px;">
                <span>Customer: <strong>{{ $customerName }}</strong></span>
                <span>{{ $customerPhone }}</span>
            </div>
            @if(!empty($customerAddress) && $customerAddress !== 'Digital Delivery' && $customerAddress !== 'N/A')
                <div style="font-size: 9px; color: #475569; margin-top: 2px;">
                    Address: {{ $customerAddress }}
                </div>
            @endif
        </div>

        <div class="dashed-line"></div>

        <table class="receipt-table">
            <thead>
                <tr>
                    <th style="text-align: left; width: 48%;">Item</th>
                    <th class="text-center" style="width: 14%;">Qty</th>
                    <th class="text-end" style="width: 18%;">Price</th>
                    <th class="text-end" style="width: 20%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($itemsList as $it)
                    @php
                        $title = $it->product_title ?? ($it->title ?? ($it->main_title ?? ($it->product_name ?? 'Product')));
                        $sku = $it->pv_sku ?? ($it->sku ?? ($it->product_code ?? ($it->main_sku ?? '')));
                        $qty = (int)($it->quantity ?? ($it->qty ?? 1));
                        $unitPrice = (float)($it->unit_price ?? ($it->price ?? 0));
                        $subtotal = (float)($it->subtotal ?? ($it->total ?? ($unitPrice * $qty)));

                        $varParts = array_filter([
                            $it->color ?? ($it->pv_color ?? null),
                            $it->size ?? ($it->pv_size ?? null)
                        ]);
                        $varText = !empty($it->variant_title) ? $it->variant_title : (count($varParts) > 0 ? implode(' / ', $varParts) : null);
                        $lineTaxRate = isset($it->tax_rate) ? (float)$it->tax_rate : 0.0;
                        $lineTaxAmt  = isset($it->tax_amount) ? (float)$it->tax_amount : 0.0;
                    @endphp
                    <tr>
                        <td style="text-align: left;">
                            <strong style="display: block; font-size: 10px; line-height: 1.2;">{{ $title }}</strong>
                            @if(!empty($varText))
                                <span style="font-size: 8.5px; color: #1e293b; display: block; font-weight: 600;">
                                    [{{ $varText }}]
                                </span>
                            @endif
                            <div style="font-size: 8px; color: #64748b; font-family: monospace;">
                                @if(!empty($sku)) Code: {{ $sku }} @endif
                                @if($taxAmt > 0 && $posVatDisplayMode === 'item_breakdown')
                                    @if($lineTaxRate > 0)
                                        <span style="color: #0f172a; font-weight: bold;">(VAT: {{ number_format($lineTaxRate, ($lineTaxRate == (int)$lineTaxRate ? 0 : 1)) }}%)</span>
                                    @elseif($lineTaxAmt > 0)
                                        <span style="color: #0f172a; font-weight: bold;">(VAT: ৳{{ number_format($lineTaxAmt, 2) }})</span>
                                    @endif
                                @endif
                            </div>
                        </td>
                        <td class="text-center">{{ $qty }}</td>
                        <td class="text-end">{{ number_format($unitPrice, 2) }}</td>
                        <td class="text-end fw-bold">{{ number_format($subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="dashed-line"></div>

        <div style="font-size: 10px;">
            <div style="display: flex; justify-content: space-between; padding: 1.5px 0;">
                <span>Subtotal:</span>
                <span class="fw-bold">{{ number_format($subtotalAmt, 2) }} ৳</span>
            </div>

            @if($discountAmt > 0)
                <div style="display: flex; justify-content: space-between; padding: 1.5px 0; color: #dc2626;">
                    <span>Discount:</span>
                    <span>-{{ number_format($discountAmt, 2) }} ৳</span>
                </div>
            @endif

            {{-- ভ্যাট থাকলে এবং হাইড করা না থাকলে তবেই ভ্যাট লাইন আসবে, ০ হলে কখনোই আসবে না --}}
            @if($taxAmt > 0 && $posVatDisplayMode !== 'hidden')
                <div style="display: flex; justify-content: space-between; padding: 1.5px 0;">
                    <span>{{ $vatLabelText }} {{ $isTaxInclusive ? '(Inc.)' : '' }}:</span>
                    <span class="fw-bold" style="color: #0284c7;">{{ number_format($taxAmt, 2) }} ৳</span>
                </div>
            @endif

            {{-- ডেলিভারি চার্জ ইনপুট থাকলে তবেই আসবে --}}
            @if($deliveryFee > 0)
                <div style="display: flex; justify-content: space-between; padding: 1.5px 0;">
                    <span>Delivery Charge:</span>
                    <span>{{ number_format($deliveryFee, 2) }} ৳</span>
                </div>
            @endif

            <div class="dashed-line" style="margin: 4px 0;"></div>

            <div style="display: flex; justify-content: space-between; padding: 2px 0; font-size: 12px; font-weight: bold;">
                <span>Total Amount:</span>
                <span>{{ number_format($grandTotalAmt, 2) }} ৳</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 1.5px 0;">
                <span>Paid ({{ $order->payment_method ?? 'Cash' }}):</span>
                <span class="fw-bold" style="color: #16a34a;">{{ number_format($paidAmt, 2) }} ৳</span>
            </div>
            @if($dueAmt > 0)
                <div style="display: flex; justify-content: space-between; padding: 1.5px 0; color: #dc2626; font-weight: bold;">
                    <span>Due Amount:</span>
                    <span>{{ number_format($dueAmt, 2) }} ৳</span>
                </div>
            @endif
        </div>

        <div class="dashed-line"></div>

        <div class="qr-wrapper text-center">
            <div id="pos-thermal-qrcode"></div>
            <span style="font-size: 8.5px; color: #64748b; margin-top: 4px; font-family: monospace;">
                Scan to Verify Slip
            </span>
        </div>

        <div class="text-center" style="font-size: 8.5px; color: #64748b; margin-top: 6px; line-height: 1.3;">
            Thank you for shopping with <strong>{{ $siteName }}</strong>!<br>
            Please keep receipt for warranty/exchange.
        </div>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const verifyPayload = "{{ $verifyUrl }}";
            new QRCode(document.getElementById("pos-thermal-qrcode"), {
                text: verifyPayload,
                width: 72,
                height: 72,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.M
            });
        });

        function togglePaperSize() {
            const card = document.getElementById('thermal-receipt-element');
            card.classList.toggle('paper-58mm');
        }

        function downloadReceiptPDF() {
            const btn = document.getElementById('btn-save-pdf');
            const originalText = btn.innerHTML;
            btn.innerHTML = '⏳ Generating...';
            btn.disabled = true;

            const element = document.getElementById('thermal-receipt-element');
            const filename = '{{ $cleanInvoiceNo }}.pdf';

            html2canvas(element, {
                scale: 3,
                useCORS: true,
                backgroundColor: '#ffffff',
                logging: false
            }).then(function (canvas) {
                const is58mm = element.classList.contains('paper-58mm');
                const pdfWidth = is58mm ? 58 : 80;
                const pdfHeight = (canvas.height * pdfWidth) / canvas.width;

                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF({
                    orientation: 'portrait',
                    unit: 'mm',
                    format: [pdfWidth, pdfHeight]
                });

                const imgData = canvas.toDataURL('image/jpeg', 0.98);
                pdf.addImage(imgData, 'JPEG', 0, 0, pdfWidth, pdfHeight);
                pdf.save(filename);

                btn.innerHTML = originalText;
                btn.disabled = false;
            }).catch(function (err) {
                console.error('PDF error:', err);
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }
    </script>
</body>
</html>