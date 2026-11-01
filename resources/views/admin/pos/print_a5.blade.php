@php
    // 1. Settings theke dynamic brand title, phone, email, address, BIN ebong VAT settings read kora
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
        'enable_vat'
    ];

    $footerSettings = \Illuminate\Support\Facades\DB::table('settings')
        ->whereIn('key', $footerKeys)
        ->pluck('value', 'key')
        ->toArray();

    $siteName = !empty($footerSettings['footer_brand_title']) 
        ? $footerSettings['footer_brand_title'] 
        : ($siteTitle ?? config('app.name', 'Shop Now BD'));

    $siteTagline = $footerSettings['footer_brand_tagline'] ?? '';

    $sitePhone = !empty($footerSettings['footer_phone']) 
        ? $footerSettings['footer_phone'] 
        : ($order->branch_phone ?? '');

    $siteEmail = !empty($footerSettings['footer_email']) 
        ? $footerSettings['footer_email'] 
        : '';

    $branchAddress = !empty($order->branch_address) 
        ? $order->branch_address 
        : ($footerSettings['company_address'] ?? ($footerSettings['contact_address'] ?? ($footerSettings['address'] ?? '')));

    $siteWebsite = !empty($footerSettings['website_url']) 
        ? $footerSettings['website_url'] 
        : ($footerSettings['site_url'] ?? url('/'));

    // Central & Branch BIN Resolver
    $companyBin = !empty($companyBin)
        ? $companyBin
        : (!empty($order->branch_bin) 
            ? $order->branch_bin 
            : ($order->bin_number ?? ($footerSettings['company_bin'] ?? ($footerSettings['bin_number'] ?? '001293847-0101'))));

    $vatLabelText = !empty($vatLabel) 
        ? $vatLabel 
        : (!empty($footerSettings['vat_label']) ? $footerSettings['vat_label'] : 'Govt. VAT / Tax');

    $posVatDisplayMode = $footerSettings['pos_vat_display_mode'] ?? 'item_breakdown';

    // 2. Subtotal, Discount & Grand Total
    $subtotalAmt   = (float)($order->subtotal ?? ($order->sub_total ?? 0));
    $grandTotalAmt = (float)($order->grand_total ?? ($order->total_amount ?? ($order->total ?? 0)));
    $discountAmt   = (float)($order->discount_amount ?? ($order->discount ?? 0));
    $itemsList     = !empty($items) ? $items : (!empty($orderItems) ? $orderItems : []);

    // 3. DELIVERY CHARGE CALCULATION (STRICT: Explicit input thaklei delivery fee hobe, noyto 0)
    $deliveryFee = 0.0;
    if (isset($order->shipping_charge) && is_numeric($order->shipping_charge) && (float)$order->shipping_charge > 0) {
        $deliveryFee = (float)$order->shipping_charge;
    } elseif (isset($order->delivery_charge) && is_numeric($order->delivery_charge) && (float)$order->delivery_charge > 0) {
        $deliveryFee = (float)$order->delivery_charge;
    } elseif (isset($order->shipping_cost) && is_numeric($order->shipping_cost) && (float)$order->shipping_cost > 0) {
        $deliveryFee = (float)$order->shipping_cost;
    }
    $deliveryFee = round($deliveryFee, 2);

    // 4. STRICT VAT RESOLUTION (0% ba 0 hole kono fake VAT inject hobe na)
    $taxAmt = 0.0;
    if (isset($order->tax_amount) && !is_null($order->tax_amount)) {
        $taxAmt = (float)$order->tax_amount;
    } elseif (isset($order->product_tax_amount) && !is_null($order->product_tax_amount)) {
        $taxAmt = (float)$order->product_tax_amount;
    }
    $taxAmt = round(max(0.0, $taxAmt), 2);

    $isTaxInclusive = (bool)($order->is_tax_inclusive ?? false);

    // 5. Total & Paid Amount 100% Sync (Order Paid hole Due kokhono asbe na)
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

    // 6. Customer Details
    $customerName    = $order->customer_name ?? ($order->name ?? ($order->shipping_name ?? 'Walk-in Customer'));
    $customerPhone   = $order->customer_phone ?? ($order->phone ?? ($order->shipping_phone ?? 'N/A'));
    $customerEmail   = $order->customer_email ?? ($order->email ?? ($order->shipping_email ?? 'N/A'));
    $customerAddress = $order->customer_address ?? ($order->address ?? ($order->shipping_address ?? ($order->bio ?? 'N/A')));

    $cleanInvoiceNo = preg_replace('/[^A-Za-z0-9_-]/', '', $order->invoice_no ?? ('POS-' . $order->id));
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
            margin: 5mm 6mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.35;
            color: #000;
            background: #f1f5f9;
            margin: 0;
            padding: 15px 0;
        }

        .invoice-wrapper {
            width: 100%;
            max-width: 148mm;
            margin: 0 auto;
            padding: 10px 12px;
            background: #fff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border-radius: 4px;
        }

        .top-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 1.5px solid #000;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .brand-title {
            font-size: 16px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 2px 0;
            color: #000;
        }

        .barcode-container svg {
            max-height: 36px;
            max-width: 150px;
        }

        .info-grid {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 9.5px;
            flex-wrap: wrap;
            gap: 8px;
        }

        .info-box {
            width: 48%;
            min-width: 190px;
            flex-grow: 1;
            border: 1px solid #000;
            padding: 6px 8px;
            border-radius: 3px;
            background: #fafafa;
        }

        .info-box-title {
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1px dashed #666;
            padding-bottom: 3px;
            margin-bottom: 4px;
            font-size: 9.5px;
            color: #111;
        }

        table.items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 9.5px;
        }

        table.items-table th, 
        table.items-table td {
            border: 1px solid #000;
            padding: 4px 5px;
            text-align: left;
        }

        table.items-table th {
            background-color: #eee;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
        }

        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .font-monospace { font-family: monospace, Courier, monospace; }
        
        .totals-area {
            display: flex;
            justify-content: flex-end;
            align-items: flex-start;
            margin-top: 6px;
        }

        .totals-table {
            width: 52%;
            min-width: 180px;
            border-collapse: collapse;
            font-size: 9.5px;
            margin-left: auto;
        }

        .totals-table td {
            padding: 3px 6px;
            border: 1px solid #000;
        }

        .footer-terms {
            margin-top: 14px;
            border-top: 1px dashed #666;
            padding-top: 5px;
            text-align: center;
            font-size: 8.5px;
            color: #444;
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
            font-size: 11.5px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s ease;
        }

        .btn-print { background: #3b82f6; color: #fff; }
        .btn-print:hover { background: #2563eb; }

        .btn-download-pdf { background: #10b981; color: #fff; }
        .btn-download-pdf:hover { background: #059669; }

        @media screen and (max-width: 575.98px) {
            body { padding: 5px 0; }
            .invoice-wrapper { padding: 6px; }
            .top-row { flex-direction: column; gap: 6px; }
            .top-row .text-end { text-align: left !important; }
            .info-box { width: 100%; }
            .totals-table { width: 100%; }
            .print-btn-bar { flex-direction: column; gap: 6px; }
        }

        @media print {
            @page {
                size: a5 portrait;
                margin: 5mm 6mm;
            }
            body {
                background: #fff !important;
                padding: 0 !important;
            }
            .print-btn-bar {
                display: none !important;
            }
            .invoice-wrapper {
                width: 148mm !important;
                max-width: 148mm !important;
                margin: 0 auto !important;
                padding: 0 !important;
                box-shadow: none !important;
                page-break-inside: avoid !important;
                page-break-after: avoid !important;
            }
        }
    </style>
</head>
<body>

    <!-- Top Action Toolbar -->
    <div class="print-btn-bar">
        <span style="font-size: 11.5px; opacity: 0.9;">A5 Memo:</span>
        <button type="button" class="btn-action btn-print" onclick="executePrint()">
            🖨️ Print Memo
        </button>
        <button type="button" class="btn-action btn-download-pdf" id="btn-download-pdf" onclick="executeDownloadPDF()">
            💾 Save / Download PDF
        </button>
    </div>

    <!-- Main Printable Invoice Content -->
    <div class="invoice-wrapper" id="printable-invoice-content">
        
        <!-- Top Header with Dynamic Footer Brand & Contact Info -->
        <div class="top-row">
            <div>
                <h1 class="brand-title">{{ $siteName }}</h1>
                @if(!empty($siteTagline))
                    <div style="font-size: 8.5px; color: #64748b; font-weight: 500;">{{ $siteTagline }}</div>
                @endif

                <p style="margin: 3px 0 0 0; font-size: 9px; color: #111;">
                    {{ !empty($order->branch_name) ? $order->branch_name : 'Main Outlet' }}
                    @if(!empty($branchAddress)) | {{ $branchAddress }} @endif
                </p>

                @if(!empty($companyBin))
                    <p style="margin: 1px 0 0 0; font-size: 8.5px; color: #1e293b; font-family: monospace;">
                        BIN: <strong>{{ $companyBin }}</strong>
                    </p>
                @endif

                @if(!empty($sitePhone) || !empty($siteEmail))
                    <p style="margin: 2px 0 0 0; font-size: 8.5px; color: #475569;">
                        @if(!empty($sitePhone)) Hotline: <strong>{{ $sitePhone }}</strong> @endif
                        @if(!empty($sitePhone) && !empty($siteEmail)) &bull; @endif
                        @if(!empty($siteEmail)) Email: <strong>{{ $siteEmail }}</strong> @endif
                    </p>
                @endif

                <p style="margin: 2px 0 0 0; font-size: 9px;">
                    Date: <strong>{{ date('d M, Y h:i A', strtotime($order->created_at)) }}</strong>
                </p>
            </div>
            <div class="text-end">
                <div class="barcode-container">
                    <svg id="barcode"></svg>
                </div>
            </div>
        </div>

        <!-- Customer & Order Information Boxes -->
        <div class="info-grid">
            <div class="info-box">
                <div class="info-box-title">Customer Information:</div>
                <div style="font-size: 10.5px; font-weight: bold;">
                    {{ $customerName }}
                </div>
                <div>Phone: <strong>{{ $customerPhone }}</strong></div>
                @if(!empty($customerEmail) && $customerEmail !== 'N/A')
                    <div>Email: {{ $customerEmail }}</div>
                @endif
                @if(!empty($customerAddress) && $customerAddress !== 'N/A' && $customerAddress !== 'Digital Delivery')
                    <div>Address: {{ $customerAddress }}</div>
                @endif
            </div>

            <div class="info-box">
                <div class="info-box-title">Memo Particulars:</div>
                <div>Invoice No: <strong>#{{ $cleanInvoiceNo }}</strong></div>
                <div>Order Status: <strong>{{ strtoupper(str_replace('_', ' ', $order->order_status ?? 'DELIVERED')) }}</strong></div>
                <div>Payment Method: <strong>{{ strtoupper($order->payment_method ?? 'CASH') }}</strong></div>
                <div>Payment Status: <strong style="color: {{ in_array(strtolower($order->payment_status ?? ''), ['paid', 'completed']) ? '#16a34a' : '#ea580c' }};">{{ strtoupper($order->payment_status ?? 'PAID') }}</strong></div>
                @if(!empty($order->transaction_id))
                    <div>Trx ID: <strong>{{ $order->transaction_id }}</strong></div>
                @endif
                @if(!empty($order->sender_number))
                    <div>Sender No: <strong>{{ $order->sender_number }}</strong></div>
                @endif
            </div>
        </div>

        <!-- Products Table (Variant & VAT Support) -->
        <div style="width: 100%; overflow-x: auto;">
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 20px;" class="text-center">#</th>
                        <th>Product Description</th>
                        <th style="width: 65px;" class="text-center">SKU / Code</th>
                        <th style="width: 30px;" class="text-center">Qty</th>
                        <th style="width: 50px;" class="text-end">Rate</th>
                        @if($taxAmt > 0 && $posVatDisplayMode !== 'hidden')
                            <th style="width: 40px;" class="text-center">{{ $vatLabelText }}</th>
                        @endif
                        <th style="width: 60px;" class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($itemsList as $idx => $it)
                        @php
                            $title = $it->product_title ?? ($it->title ?? ($it->main_title ?? ($it->product_name ?? 'Product')));
                            $sku = $it->pv_sku ?? ($it->sku ?? ($it->product_code ?? ($it->main_sku ?? 'N/A')));
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
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td>
                                <strong style="font-size: 10px;">{{ $title }}</strong>
                                @if(!empty($varText))
                                    <span style="font-size: 8.5px; display: block; color: #334155; font-weight: 600;">
                                        Variant: {{ $varText }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-center font-monospace" style="font-size: 8.5px; color: #555;">{{ $sku }}</td>
                            <td class="text-center fw-bold">{{ $qty }}</td>
                            <td class="text-end">{{ number_format($unitPrice, 2) }}</td>
                            @if($taxAmt > 0 && $posVatDisplayMode !== 'hidden')
                                <td class="text-center font-monospace" style="font-size: 8.5px;">
                                    @if($posVatDisplayMode === 'item_breakdown')
                                        @if($lineTaxRate > 0)
                                            {{ number_format($lineTaxRate, ($lineTaxRate == (int)$lineTaxRate ? 0 : 1)) }}%
                                        @elseif($lineTaxAmt > 0)
                                            ৳{{ number_format($lineTaxAmt, 2) }}
                                        @else
                                            0%
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                            @endif
                            <td class="text-end fw-bold">{{ number_format($subtotal, 2) }} ৳</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ ($taxAmt > 0 && $posVatDisplayMode !== 'hidden') ? 7 : 6 }}" class="text-center py-2">No item records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Calculations & Totals (Strictly Separated VAT & Delivery Charge) -->
        <div class="totals-area">
            <table class="totals-table font-monospace">
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-end fw-bold">{{ number_format($subtotalAmt, 2) }} ৳</td>
                </tr>

                @if($discountAmt > 0)
                    <tr>
                        <td>Discount:</td>
                        <td class="text-end" style="color: #dc2626;">-{{ number_format($discountAmt, 2) }} ৳</td>
                    </tr>
                @endif

                {{-- VAT LINE: Shudhu tokhon-i ashbe jodi taxAmt > 0 ebong pos_vat_display_mode !== 'hidden' hoy --}}
                @if($taxAmt > 0 && $posVatDisplayMode !== 'hidden')
                    <tr>
                        <td>{{ $vatLabelText }} {{ $isTaxInclusive ? '(Inc.)' : '' }}:</td>
                        <td class="text-end fw-bold" style="color: #0284c7;">{{ number_format($taxAmt, 2) }} ৳</td>
                    </tr>
                @endif

                {{-- DELIVERY CHARGE: Shudhu tokhon-i ashbe jodi input > 0 thake --}}
                @if($deliveryFee > 0)
                    <tr>
                        <td>Delivery Charge:</td>
                        <td class="text-end fw-bold">{{ number_format($deliveryFee, 2) }} ৳</td>
                    </tr>
                @endif

                <tr style="font-weight: bold; background: #eee;">
                    <td>Grand Total:</td>
                    <td class="text-end">{{ number_format($grandTotalAmt, 2) }} ৳</td>
                </tr>

                <tr>
                    <td>Paid Amount:</td>
                    <td class="text-end fw-bold" style="color: #16a34a;">{{ number_format($paidAmt, 2) }} ৳</td>
                </tr>

                @if($dueAmt > 0)
                    <tr style="color: #dc2626; font-weight: bold;">
                        <td>Due Amount:</td>
                        <td class="text-end">{{ number_format($dueAmt, 2) }} ৳</td>
                    </tr>
                @endif
            </table>
        </div>

        <!-- Dynamic Footer Contact & Terms -->
        <div class="footer-terms">
            For support:
            @if(!empty($sitePhone)) Hotline: <strong>{{ $sitePhone }}</strong> @endif
            @if(!empty($sitePhone) && !empty($siteEmail)) &bull; @endif
            @if(!empty($siteEmail)) Email: <strong>{{ $siteEmail }}</strong> @endif
            @if(!empty($siteWebsite)) &bull; Web: <strong>{{ preg_replace('#^https?://#', '', rtrim($siteWebsite, '/')) }}</strong> @endif
            @if(!empty($companyBin)) &bull; BIN: <strong>{{ $companyBin }}</strong> @endif
            <br>
            Thank you for shopping with <strong>{{ $siteName }}</strong>
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
                height: 32,
                displayValue: true,
                fontSize: 9,
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
                filename:     cleanInvName + '.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2.5, useCORS: true },
                jsPDF:        { unit: 'mm', format: 'a5', orientation: 'portrait' }
            };

            html2pdf().set(opt).from(element).save().then(function() {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }).catch(function(err) {
                console.error(err);
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