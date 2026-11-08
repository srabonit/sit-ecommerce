@php
    $branch = $branch ?? ($order->branch ?? null);

    // ১. সেটিংস থেকে ডাইনামিক ব্র্যান্ড, ফোন, ইমেইল, ঠিকানা ও মূসক/ট্যাক্স সেটিংস রিড
    $footerKeys = [
        'footer_brand_title',
        'footer_brand_tagline',
        'footer_phone',
        'footer_email',
        'footer_about_text',
        'company_address',
        'contact_address',
        'address',
        'website_url',
        'site_url',
        'company_bin',
        'bin_number',
        'vat_label',
        'pos_vat_display_mode',
        'enable_pos_vat',
        'vat_enabled',
        'enable_vat',
        'header_logo',
        'site_icon'
    ];

    $footerSettings = \Illuminate\Support\Facades\DB::table('settings')
        ->whereIn('key', $footerKeys)
        ->pluck('value', 'key')
        ->toArray();

    $siteName = !empty($footerSettings['footer_brand_title']) 
        ? $footerSettings['footer_brand_title'] 
        : ($siteTitle ?? config('app.name', 'Shop Now BD'));

    $siteLogo = !empty($footerSettings['header_logo']) 
        ? asset(ltrim($footerSettings['header_logo'], '/')) 
        : null;

    $sitePhone = !empty($order->branch_phone) 
        ? $order->branch_phone 
        : (!empty($footerSettings['footer_phone']) ? $footerSettings['footer_phone'] : ($branch->phone ?? ''));

    $siteEmail = !empty($branch->email) 
        ? $branch->email 
        : (!empty($footerSettings['footer_email']) ? $footerSettings['footer_email'] : '');

    $branchAddress = !empty($order->branch_address) 
        ? $order->branch_address 
        : ($branch->address ?? ($footerSettings['company_address'] ?? ($footerSettings['contact_address'] ?? ($footerSettings['address'] ?? ''))));

    $siteWebsite = !empty($footerSettings['website_url']) 
        ? $footerSettings['website_url'] 
        : ($footerSettings['site_url'] ?? url('/'));

    // Central & Branch BIN Resolver
    $companyBin = !empty($companyBin)
        ? $companyBin
        : (!empty($order->branch_bin) 
            ? $order->branch_bin 
            : ($order->bin_number ?? ($branch->bin_number ?? ($footerSettings['company_bin'] ?? ($footerSettings['bin_number'] ?? '001293847-0101')))));

    $vatLabelText = !empty($vatLabel) 
        ? $vatLabel 
        : (!empty($footerSettings['vat_label']) ? $footerSettings['vat_label'] : 'Govt. VAT / Tax');

    $posVatDisplayMode = $footerSettings['pos_vat_display_mode'] ?? 'item_breakdown';

    // ২. সাবটোটাল, ডিসকাউন্ট এবং মোট এমাউন্ট
    $subtotalAmt   = (float)($order->subtotal ?? ($order->sub_total ?? 0));
    $grandTotalAmt = (float)($order->grand_total ?? ($order->total_amount ?? ($order->total ?? 0)));
    $discountAmt   = (float)($order->discount_amount ?? ($order->discount ?? 0));
    $itemsList     = !empty($items) ? $items : (!empty($orderItems) ? $orderItems : ($order->items ?? []));

    // ৩. ডেলিভারি চার্জ
    $deliveryFee = 0.0;
    if (isset($order->shipping_charge) && is_numeric($order->shipping_charge) && (float)$order->shipping_charge > 0) {
        $deliveryFee = (float)$order->shipping_charge;
    } elseif (isset($order->delivery_charge) && is_numeric($order->delivery_charge) && (float)$order->delivery_charge > 0) {
        $deliveryFee = (float)$order->delivery_charge;
    } elseif (isset($order->shipping_cost) && is_numeric($order->shipping_cost) && (float)$order->shipping_cost > 0) {
        $deliveryFee = (float)$order->shipping_cost;
    }
    $deliveryFee = round($deliveryFee, 2);

    // ৪. ভ্যাট রেজল্যুশন
    $taxAmt = 0.0;
    if (isset($order->tax_amount) && !is_null($order->tax_amount)) {
        $taxAmt = (float)$order->tax_amount;
    } elseif (isset($order->product_tax_amount) && !is_null($order->product_tax_amount)) {
        $taxAmt = (float)$order->product_tax_amount;
    }
    $taxAmt = round(max(0.0, $taxAmt), 2);

    $isTaxInclusive = (bool)($order->is_tax_inclusive ?? false);

    // ৫. পেইড ও বকেয়া হিসাব (Sync)
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

    // ৬. কাস্টমার তথ্য
    $customerName    = $order->customer_name ?? ($order->user?->name ?? ($order->name ?? ($order->shipping_name ?? 'Guest Customer')));
    $customerPhone   = $order->customer_phone ?? ($order->user?->phone ?? ($order->phone ?? ($order->shipping_phone ?? 'N/A')));
    $customerEmail   = $order->customer_email ?? ($order->user?->email ?? ($order->email ?? ($order->shipping_email ?? 'N/A')));
    $customerAddress = $order->customer_address ?? ($order->user?->address ?? ($order->address ?? ($order->shipping_address ?? ($order->bio ?? 'N/A'))));

    $salesPersonName = $order->salesPerson?->name ?? ($order->creator?->name ?? null);
    $cleanInvoiceNo  = preg_replace('/[^A-Za-z0-9_-]/', '', $order->invoice_no ?? ('POS-' . $order->id));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $cleanInvoiceNo }}</title>
    
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    
    <style>
        @page {
            size: a5 portrait;
            margin: 6mm 8mm 6mm 8mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5px;
            line-height: 1.35;
            color: #000;
            background: #f1f5f9;
            margin: 0;
            padding: 12px 0;
        }

        .invoice-wrapper {
            width: 100%;
            max-width: 148mm;
            min-height: 204mm;
            margin: 0 auto;
            padding: 8mm 9mm 6mm 9mm;
            background: #fff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .top-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
        }

        .brand-logo-area {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .brand-logo-img {
            max-height: 36px;
            max-width: 140px;
            object-fit: contain;
        }

        .brand-title {
            font-size: 17px;
            font-weight: 900;
            margin: 0;
            color: #000;
            letter-spacing: -0.3px;
        }

        .barcode-container svg {
            max-height: 32px;
            max-width: 155px;
        }

        .mushak-header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 6px;
        }

        .invoice-badge-box {
            display: inline-block;
            border: 1.3px solid #000;
            padding: 2px 6px;
            font-weight: 800;
            font-size: 10px;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .mushak-meta-text {
            font-size: 9.5px;
            font-weight: 700;
            color: #000;
            margin: 1.5px 0;
            font-family: monospace;
        }

        .nbr-ref-text {
            font-size: 9px;
            font-family: monospace;
            color: #334155;
            font-weight: 600;
        }

        .divider-solid {
            border-bottom: 1.3px solid #000;
            margin: 5px 0 8px 0;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 9px;
        }

        .info-box {
            border: 1.1px solid #000;
            padding: 6px 8px;
            background: #fff;
            min-height: 78px;
        }

        .info-box-header {
            font-weight: 800;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 3px;
        }

        .info-box-divider {
            border-bottom: 1px dashed #777;
            margin-bottom: 4px;
        }

        .info-box-body {
            font-size: 9px;
            line-height: 1.45;
        }

        .info-customer-name {
            font-size: 11.5px;
            font-weight: 800;
            color: #000;
            margin-bottom: 2px;
        }

        table.items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        table.items-table th, 
        table.items-table td {
            border: 1.1px solid #000;
            padding: 4px 6px;
            text-align: left;
        }

        table.items-table th {
            background-color: #ffffff;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.2px;
        }

        table.items-table td {
            font-size: 9px;
            vertical-align: middle;
        }

        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .font-monospace { font-family: monospace, Courier, monospace; }

        .item-sku-tag {
            color: #dc2626;
            font-size: 8px;
            font-weight: 700;
            font-family: monospace;
            display: block;
            margin-top: 1px;
        }

        .bottom-calc-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 10px;
        }

        .notes-box {
            border: 1.1px solid #000;
            padding: 6px 8px;
            min-height: 68px;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table td {
            border: 1.1px solid #000;
            padding: 3.5px 6px;
            font-size: 9px;
        }

        .signatures-area {
            margin-top: 24px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding: 0 8px;
        }

        .sign-box {
            text-align: center;
            width: 135px;
        }

        .sign-line {
            border-top: 1px dashed #000;
            margin-bottom: 4px;
        }

        .sign-title {
            font-size: 8.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .footer-note-line {
            margin-top: 14px;
            border-top: 1px dashed #666;
            padding-top: 4px;
            text-align: center;
            font-size: 8px;
            color: #000;
            font-family: monospace;
        }

        .print-btn-bar {
            width: 100%;
            max-width: 148mm;
            margin: 0 auto 10px auto;
            background: #1e293b;
            color: #fff;
            padding: 8px 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border-radius: 6px;
        }

        .btn-action {
            border: none;
            padding: 6px 14px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 4px;
            font-size: 11px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s ease;
        }

        .btn-print { background: #2563eb; color: #fff; }
        .btn-print:hover { background: #1d4ed8; }

        .btn-download-pdf { background: #10b981; color: #fff; }
        .btn-download-pdf:hover { background: #059669; }

        @media print {
            @page {
                size: a5 portrait;
                margin: 5mm 6mm;
            }
            body {
                background: #fff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .print-btn-bar {
                display: none !important;
            }
            .invoice-wrapper {
                width: 100% !important;
                max-width: 148mm !important;
                min-height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                page-break-inside: avoid !important;
                page-break-after: avoid !important;
            }
        }
    </style>
</head>
<body>

    <!-- প্রিন্ট ও ডাউনলোড টুলবার (প্রিন্টের সময় হাইড থাকবে) -->
    <div class="print-btn-bar">
        <span style="font-size: 11px; font-weight: 600;">A5 Mushak 6.3 Invoice:</span>
        <button type="button" class="btn-action btn-print" onclick="executePrint()">
            🖨️ Print Memo
        </button>
        <button type="button" class="btn-action btn-download-pdf" id="btn-download-pdf" onclick="executeDownloadPDF()">
            💾 Save as PDF
        </button>
    </div>

    <!-- মূল A5 ইনভয়েস কন্টেইনার -->
    <div class="invoice-wrapper" id="printable-invoice-content">
        <div>
            <!-- টপ লোগো এবং বারকোড সেকশন -->
            <div class="top-row">
                <div class="brand-logo-area">
                    @if(!empty($siteLogo))
                        <img src="{{ $siteLogo }}" alt="{{ $siteName }}" class="brand-logo-img">
                    @else
                        <h1 class="brand-title">{{ $siteName }}</h1>
                    @endif
                </div>

                <div class="text-end">
                    <div class="barcode-container">
                        <svg id="barcode"></svg>
                    </div>
                </div>
            </div>

            <!-- মূসক ৬.৩ হেডার এবং বিআইএন -->
            <div class="mushak-header-row">
                <div>
                    <div class="invoice-badge-box">
                        MUSHAK 6.3 / TAX INVOICE
                    </div>
                    <div class="mushak-meta-text">
                        BIN: <strong>{{ $companyBin }}</strong>
                    </div>
                    <div class="mushak-meta-text" style="font-family: inherit; font-size: 9px;">
                        Date: <strong>{{ date('d M, Y h:i A', strtotime($order->created_at)) }}</strong>
                    </div>
                </div>

                <div class="text-end">
                    <div class="nbr-ref-text">
                        NBR GO-05/VAT/2026
                    </div>
                </div>
            </div>

            <div class="divider-solid"></div>

            <!-- বিলিং ও অর্ডার পার্টিকুলার্স বক্স -->
            <div class="info-grid">
                <div class="info-box">
                    <div class="info-box-header">BILL &amp; SHIP TO:</div>
                    <div class="info-box-divider"></div>
                    <div class="info-box-body">
                        <div class="info-customer-name">{{ $customerName }}</div>
                        <div>Phone: <strong>{{ $customerPhone }}</strong></div>
                        <div>Address: {{ $customerAddress }}</div>
                    </div>
                </div>

                <div class="info-box">
                    <div class="info-box-header">ORDER PARTICULARS:</div>
                    <div class="info-box-divider"></div>
                    <div class="info-box-body">
                        <div>Invoice No: <strong>#{{ $cleanInvoiceNo }}</strong></div>
                        <div>Order Status: <strong>{{ strtoupper(str_replace('_', ' ', $order->order_status ?? 'DELIVERED')) }}</strong></div>
                        <div>Payment Method: <strong>{{ strtoupper($order->payment_method ?? 'CASH') }}</strong></div>
                        <div>
                            Payment Status: 
                            <strong style="color: {{ in_array(strtolower($order->payment_status ?? ''), ['paid', 'completed']) ? '#16a34a' : '#ea580c' }};">
                                {{ strtoupper($order->payment_status ?? 'PAID') }}
                            </strong>
                        </div>
                        @if(!empty($salesPersonName))
                            <div>Served By: <strong>{{ $salesPersonName }}</strong></div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- প্রোডাক্ট আইটেম টেবিল -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 25px;" class="text-center">#</th>
                        <th>ITEM DESCRIPTION / SPECIFICATION</th>
                        <th style="width: 75px;" class="text-end">RATE</th>
                        <th style="width: 35px;" class="text-center">QTY</th>
                        <th style="width: 45px;" class="text-center">VAT</th>
                        <th style="width: 85px;" class="text-end">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($itemsList as $idx => $it)
                        @php
                            $title = $it->product_title ?? ($it->title ?? ($it->main_title ?? ($it->product_name ?? ($it->product->name ?? ($it->product->title ?? 'Product')))));
                            $sku = $it->pv_sku ?? ($it->sku ?? ($it->product_code ?? ($it->main_sku ?? ($it->variant?->sku ?? ($it->product?->sku ?? 'N/A')))));
                            $qty = (int)($it->quantity ?? ($it->qty ?? 1));
                            $unitPrice = (float)($it->unit_price ?? ($it->price ?? 0));
                            $subtotal = (float)($it->subtotal ?? ($it->total ?? ($unitPrice * $qty)));

                            $varParts = array_filter([
                                $it->color ?? ($it->pv_color ?? ($it->variant?->color ?? null)),
                                $it->size ?? ($it->pv_size ?? ($it->variant?->size ?? null))
                            ]);
                            $varText = !empty($it->variant_title) ? $it->variant_title : (count($varParts) > 0 ? implode(' / ', $varParts) : null);
                            $lineTaxRate = isset($it->tax_rate) ? (float)$it->tax_rate : 0.0;
                            $lineTaxAmt  = isset($it->tax_amount) ? (float)$it->tax_amount : 0.0;
                        @endphp
                        <tr>
                            <td class="text-center font-monospace">{{ $idx + 1 }}</td>
                            <td>
                                <strong style="font-size: 9.5px; color: #000;">{{ $title }}</strong>
                                <span class="item-sku-tag">SKU: {{ $sku }}</span>
                                @if(!empty($varText))
                                    <span style="font-size: 8.5px; color: #334155; display: block;">
                                        Variant: {{ $varText }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-end font-monospace">৳{{ number_format($unitPrice, 2) }}</td>
                            <td class="text-center font-monospace fw-bold">{{ $qty }}</td>
                            <td class="text-center font-monospace">
                                @if($lineTaxRate > 0)
                                    {{ number_format($lineTaxRate, ($lineTaxRate == (int)$lineTaxRate ? 0 : 1)) }}%
                                @elseif($lineTaxAmt > 0)
                                    ৳{{ number_format($lineTaxAmt, 2) }}
                                @else
                                    0%
                                @endif
                            </td>
                            <td class="text-end font-monospace fw-bold">৳{{ number_format($subtotal, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-2">No items found in this order.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- নোটস ও ক্যালকুলেশন সামারি -->
            <div class="bottom-calc-grid">
                <div class="notes-box">
                    <strong style="font-size: 9.5px; display: block; margin-bottom: 3px;">Order Instructions / Notes:</strong>
                    <span style="font-size: 8.5px; color: #334155;">
                        {{ $order->notes ?? ($order->note ?? 'POS In-Store Sale') }}
                    </span>
                </div>

                <div>
                    <table class="totals-table font-monospace">
                        <tr>
                            <td style="width: 50%;">Subtotal:</td>
                            <td class="text-end fw-bold">৳{{ number_format($subtotalAmt, 2) }}</td>
                        </tr>

                        @if($discountAmt > 0)
                            <tr>
                                <td>Discount:</td>
                                <td class="text-end" style="color: #dc2626;">-৳{{ number_format($discountAmt, 2) }}</td>
                            </tr>
                        @endif

                        @if($taxAmt > 0 && $posVatDisplayMode !== 'hidden')
                            <tr>
                                <td>{{ $vatLabelText }} {{ $isTaxInclusive ? '(Inc.)' : '' }}:</td>
                                <td class="text-end fw-bold" style="color: #0284c7;">৳{{ number_format($taxAmt, 2) }}</td>
                            </tr>
                        @endif

                        @if($deliveryFee > 0)
                            <tr>
                                <td>Delivery Charge:</td>
                                <td class="text-end fw-bold">৳{{ number_format($deliveryFee, 2) }}</td>
                            </tr>
                        @endif

                        <tr style="font-weight: 800; background: #fafafa;">
                            <td>Grand Total:</td>
                            <td class="text-end">৳{{ number_format($grandTotalAmt, 2) }}</td>
                        </tr>

                        <tr>
                            <td>Paid Amount:</td>
                            <td class="text-end fw-bold" style="color: #16a34a;">৳{{ number_format($paidAmt, 2) }}</td>
                        </tr>

                        @if($dueAmt > 0)
                            <tr style="color: #dc2626; font-weight: 800;">
                                <td>Due Amount:</td>
                                <td class="text-end">৳{{ number_format($dueAmt, 2) }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- সিগনেচার এরিয়া এবং ফুটার নোট -->
        <div>
            <div class="signatures-area">
                <div class="sign-box">
                    <div class="sign-line"></div>
                    <div class="sign-title">CUSTOMER COPY</div>
                </div>

                <div class="sign-box">
                    <div class="sign-line"></div>
                    <div class="sign-title">AUTHORIZED SEAL</div>
                </div>
            </div>

            <div class="footer-note-line">
                For support: @if(!empty($sitePhone)) • Hotline: <strong>{{ $sitePhone }}</strong> @endif • BIN: <strong>{{ $companyBin }}</strong> • Computer Generated Tax Invoice (Mushak 6.3)
            </div>
        </div>
    </div>

    <script>
        const cleanInvName = "{{ $cleanInvoiceNo }}";
        document.title = cleanInvName;

        const invBarcodeVal = "{{ $order->barcode ?? ($order->invoice_no ?? $order->id) }}";
        if (typeof JsBarcode !== 'undefined') {
            JsBarcode("#barcode", String(invBarcodeVal), {
                format: "CODE128",
                width: 1.15,
                height: 28,
                displayValue: true,
                fontSize: 8.5,
                margin: 0
            });
        }

        function executeDownloadPDF() {
            const btn = document.getElementById('btn-download-pdf');
            const originalText = btn.innerHTML;
            btn.innerHTML = '⏳ Generating PDF...';
            btn.disabled = true;

            const element = document.getElementById('printable-invoice-content');
            const opt = {
                margin:       [4, 4, 4, 4],
                filename:     cleanInvName + '-A5.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2.5, useCORS: true },
                jsPDF:        { unit: 'mm', format: 'a5', orientation: 'portrait' }
            };

            html2pdf().set(opt).from(element).save().then(function() {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }).catch(function(err) {
                console.error('PDF error:', err);
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }

        function executePrint() {
            document.title = cleanInvName;
            window.print();
        }
    </script>
</body>
</html>