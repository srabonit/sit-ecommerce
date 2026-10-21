@php
    $siteSettings = \Illuminate\Support\Facades\DB::table('settings')
        ->whereIn('key', ['footer_brand_title', 'footer_phone', 'footer_email', 'header_logo', 'footer_logo', 'site_icon', 'favicon'])
        ->pluck('value', 'key')
        ->toArray();

    $siteName = !empty($siteSettings['footer_brand_title']) 
        ? $siteSettings['footer_brand_title'] 
        : config('app.name', 'SHOP NOW BD ONLINE');

    $sitePhone =$siteSettings['footer_phone'] ?? '';
    $siteEmail =$siteSettings['footer_email'] ?? '';

    $siteLogo = null;
    $rawLogo = $siteSettings['header_logo'] ?? ($siteSettings['footer_logo'] ?? null);
    if (!empty($rawLogo)) {
        if (class_exists(\App\Services\ImagePathService::class)) {
            $resolved = \App\Services\ImagePathService::resolveUrl($rawLogo, '');$siteLogo = !empty($resolved) ?$resolved : null;
        } elseif (function_exists('show_image')) {
            $siteLogo = show_image($rawLogo, '');
        } elseif (filter_var($rawLogo, FILTER_VALIDATE_URL)) {
            $siteLogo =$rawLogo;
        } else {
            $siteLogo = asset(ltrim($rawLogo, '/'));
        }
    }

    $dbSiteIcon = $siteSettings['site_icon'] ?? ($siteSettings['favicon'] ?? null);
    $finalFavicon = null;
    if (!empty($dbSiteIcon)) {
        if (filter_var($dbSiteIcon, FILTER_VALIDATE_URL)) {
            $finalFavicon =$dbSiteIcon;
        } else {
            $cleanIcon = ltrim(str_replace(['storage/', 'public/'], '',$dbSiteIcon), '/');
            if (file_exists(public_path($cleanIcon))) {
                $finalFavicon = asset($cleanIcon);
            } elseif (file_exists(public_path('uploads/' . $cleanIcon))) {
                $finalFavicon = asset('uploads/' .$cleanIcon);
            } else {
                $finalFavicon = asset($cleanIcon);
            }
        }
    }
    if (empty($finalFavicon) && file_exists(public_path('favicon.ico'))) {$finalFavicon = asset('favicon.ico');
    }

    $subtotalAmt   = (float)($order->subtotal ?? 0);
    $grandTotalAmt = (float)($order->grand_total ?? 0);
    $discountAmt   = (float)($order->discount_amount ?? ($order->discount ?? 0));

    if (isset($order->delivery_charge) && is_numeric($order->delivery_charge)) {
        $deliveryFee = (float)$order->delivery_charge;
    } elseif (isset($order->shipping_charge) && is_numeric($order->shipping_charge)) {
        $deliveryFee = (float)$order->shipping_charge;
    } else {
        $calcDiff =$grandTotalAmt - ($subtotalAmt -$discountAmt);
        $deliveryFee = max(0,$calcDiff);
    }

    $paidAmt = (float)($order->paid_amount ?? (($order->payment_status ?? '') === 'paid' ? $grandTotalAmt : 0));
    $dueAmt  = (float)($order->due_amount ?? max(0, $grandTotalAmt -$paidAmt));

    $rawInv =$order->invoice_no ?? ('INV-' . $order->id);$cleanInvoiceNo = preg_replace('/[^A-Za-z0-9_-]/', '', $rawInv);$trackingCode = $order->courier_tracking_id ?? ($order->parcel_id ?? null);
    $orderNote =$order->admin_note ?? ($order->order_note ?? ($order->notes ?? 'Thank you for shopping with us. Please check your package in front of the delivery person.'));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $cleanInvoiceNo }} - Invoice Memo</title>
    
    @if(!empty($finalFavicon))
        <link rel="icon" type="image/x-icon" href="{{ $finalFavicon }}">
        <link rel="shortcut icon" href="{{ $finalFavicon }}">
        <link rel="apple-touch-icon" href="{{ $finalFavicon }}">
    @endif

    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .btn-action,
        .custom-badge,
        .custom-modal-content {
            border-radius: 5px !important;
        }

        @page {
            size: a5 portrait;
            margin: 4mm 5mm;
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
            background: #0f172a;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .print-btn-bar {
            background: #020617;
            border-bottom: 1px solid #1e293b;
            padding: 10px 16px;
            position: sticky;
            top: 0;
            z-index: 1050;
            width: 100%;
        }

        .toolbar-inner {
            width: 140mm;
            max-width: 100%;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .toolbar-left,
        .toolbar-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-action {
            border: 1px solid transparent;
            padding: 6px 14px;
            font-weight: 600;
            cursor: pointer;
            font-size: 11.5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
            white-space: nowrap;
            transition: all 0.15s ease;
        }

        .btn-back { background: #1e293b; color: #f8fafc; border-color: #334155; }
        .btn-back:hover { background: #334155; color: #ffffff; }

        .btn-print { background: #2563eb; color: #ffffff; }
        .btn-print:hover { background: #1d4ed8; }

        .btn-download-pdf { background: #16a34a; color: #ffffff; }
        .btn-download-pdf:hover { background: #15803d; }

        @media (max-width: 600px) {
            .print-btn-bar {
                padding: 8px 10px;
            }
            .toolbar-inner {
                width: 100% !important;
                flex-direction: column;
                align-items: stretch;
                gap: 8px;
            }
            .toolbar-left,
            .toolbar-right {
                width: 100%;
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 6px;
            }
            .toolbar-left .btn-back {
                grid-column: span 2;
                width: 100%;
            }
            .toolbar-right .btn-action {
                width: 100%;
                padding: 7px 6px;
                font-size: 11px;
            }
        }

        .memo-viewport-stage {
            width: 100%;
            padding: 16px 8px 30px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            overflow-x: hidden;
        }

        .memo-scale-wrapper {
            transform-origin: top center;
            transition: transform 0.15s ease;
            flex-shrink: 0;
            margin: 0 auto;
        }

        .invoice-wrapper {
            width: 140mm !important;
            max-width: 140mm !important;
            min-width: 140mm !important;
            min-height: 198mm;
            margin: 0 auto;
            padding: 6mm 6mm 5mm 6mm;
            background: #ffffff;
            color: #000000;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.45);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .top-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 1.5px solid #000;
            padding-bottom: 6px;
            margin-bottom: 6px;
        }

        .brand-logo-img {
            max-height: 38px;
            max-width: 135px;
            object-fit: contain;
            display: block;
            margin-bottom: 3px;
        }

        .brand-title {
            font-size: 15px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin: 0 0 2px 0;
            line-height: 1.2;
            color: #000;
        }

        .barcode-container {
            text-align: right;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }
        .barcode-container svg {
            max-height: 34px;
            max-width: 150px;
            display: block;
        }

        .info-grid {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            gap: 6px;
            width: 100%;
        }

        .info-box {
            width: 50%;
            border: 1px solid #000;
            padding: 5px 7px;
            font-size: 9.5px;
            line-height: 1.35;
            min-width: 0;
        }

        .info-box-title {
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1px dashed #666;
            padding-bottom: 2px;
            margin-bottom: 4px;
            font-size: 9px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .safe-text-break {
            word-break: break-word !important;
            overflow-wrap: anywhere !important;
            white-space: normal !important;
        }

        table.items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            font-size: 9.5px;
        }

        table.items-table th, 
        table.items-table td {
            border: 1px solid #000;
            padding: 3.5px 5px;
            text-align: left;
            vertical-align: middle;
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
            justify-content: space-between;
            align-items: flex-start;
            gap: 6px;
            width: 100%;
        }

        .meta-box {
            width: 50%;
            font-size: 9px;
            border: 1px solid #000;
            padding: 5px 6px;
            line-height: 1.35;
            min-width: 0;
        }

        .totals-table {
            width: 48%;
            border-collapse: collapse;
            font-size: 9.5px;
        }

        .totals-table td {
            padding: 3px 6px;
            border: 1px solid #000;
        }

        .memo-seal-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 8px;
            padding-top: 4px;
        }

        .seal-box {
            width: 85px;
            text-align: center;
            border-top: 1px dashed #000;
            padding-top: 2px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .footer-terms {
            margin-top: 6px;
            border-top: 1px dashed #666;
            padding-top: 3px;
            text-align: center;
            font-size: 8px;
            color: #333;
        }

        @media print {
            @page {
                size: a5 portrait;
                margin: 4mm 5mm;
            }
            body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .print-btn-bar {
                display: none !important;
            }
            .memo-viewport-stage {
                padding: 0 !important;
                background: transparent !important;
                overflow: visible !important;
                display: block !important;
                height: auto !important;
            }
            .memo-scale-wrapper {
                transform: none !important;
                margin: 0 !important;
            }
            .invoice-wrapper {
                width: 100% !important;
                max-width: 100% !important;
                min-width: 100% !important;
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

    <div class="print-btn-bar">
        <div class="toolbar-inner">
            <div class="toolbar-left">
                <a href="{{ route('admin.order.online') }}" class="btn-action btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Orders
                </a>
            </div>

            <div class="toolbar-right">
                <button type="button" class="btn-action btn-print" onclick="executePrint()">
                    <i class="fas fa-print"></i> Print Memo
                </button>
                <button type="button" class="btn-action btn-download-pdf" id="btn-download-pdf" onclick="executeDownloadPDF()">
                    <i class="fas fa-download"></i> <span id="btnPdfText">Save PDF</span>
                </button>
            </div>
        </div>
    </div>

    <div class="memo-viewport-stage" id="memoViewportStage">
        <div class="memo-scale-wrapper" id="memoScaleWrapper">
            <div class="invoice-wrapper" id="printable-invoice-content">
                <div>
                    <!-- Top Header Row -->
                    <div class="top-row">
                        <div>
                            @if(!empty($siteLogo))
                                <img src="{{ $siteLogo }}" alt="{{ $siteName }}" class="brand-logo-img" id="invoiceBrandLogo" onerror="this.style.display='none'; document.getElementById('alt-site-title').style.display='block';">
                                <h1 class="brand-title" id="alt-site-title" style="display: none;">{{ $siteName }}</h1>
                            @else
                                <img src="" alt="{{ $siteName }}" class="brand-logo-img" id="invoiceBrandLogo" style="display: none;">
                                <h1 class="brand-title" id="alt-site-title">{{ $siteName }}</h1>
                            @endif
                            <p style="margin: 0; font-size: 9px; font-weight: bold; text-transform: uppercase;">Delivery &amp; Packing Slip Invoice</p>
                            <!-- Timezone-Safe Dynamic Date -->
                            <p style="margin: 2px 0 0 0; font-size: 8.5px; color: #333;">Date: <strong>{{ function_exists('format_datetime') ? format_datetime($order->created_at, 'd M, Y h:i A') : date('d M, Y h:i A', strtotime($order->created_at)) }}</strong></p>
                        </div>

                        <div class="barcode-container">
                            <svg id="barcode"></svg>
                        </div>
                    </div>

                    <!-- Customer & Order Meta Grid -->
                    <div class="info-grid">
                        <div class="info-box">
                            <div class="info-box-title">Bill &amp; Ship To:</div>
                            <div style="font-size: 10.5px; font-weight: bold;" class="safe-text-break">
                                {{ $order->shipping_name ?? ($order->customer_name ?? ($order->name ?? 'Guest Customer')) }}
                            </div>
                            <div>Phone: <strong>{{ $order->shipping_phone ?? ($order->customer_phone ?? ($order->phone ?? 'N/A')) }}</strong></div>
                            @if(!empty($order->shipping_email) || !empty($order->customer_email) || !empty($order->email))
                                <div class="safe-text-break">Email: {{ $order->shipping_email ?? ($order->customer_email ?? ($order->email ?? 'N/A')) }}</div>
                            @endif
                            <div class="safe-text-break">Address: {{ $order->shipping_address ?? ($order->customer_address ?? ($order->address ?? 'N/A')) }}</div>
                            @if(!empty($order->shipping_district) || !empty($order->district))
                                <div>District: <strong>{{ $order->shipping_district ?? $order->district }}</strong></div>
                            @endif
                        </div>

                        <div class="info-box">
                            <div class="info-box-title">Order Particulars:</div>
                            <div>Invoice No: <strong>#{{ $order->invoice_no ?? $order->id }}</strong></div>
                            <div>Order Status: <strong>{{ strtoupper(str_replace('_', ' ', $order->order_status ?? 'PENDING')) }}</strong></div>
                            <div>Payment Method: <strong>{{ strtoupper($order->payment_method ?? 'CASH ON DELIVERY') }}</strong></div>
                            <div>Payment Status: <strong>{{ strtoupper($order->payment_status ?? 'UNPAID') }}</strong></div>
                            @if(!empty($order->transaction_id))
                                <div>Trx ID: <strong>{{ $order->transaction_id }}</strong></div>
                            @endif
                            @if(!empty($order->courier_name))
                                <div>Courier: <strong>{{ $order->courier_name }} @if(!empty($trackingCode))({{$trackingCode }})@endif</strong></div>
                            @endif
                        </div>
                    </div>

                    <!-- Items Table -->
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th style="width: 20px;" class="text-center">#</th>
                                <th>Item Description / Specification</th>
                                <th style="width: 55px;" class="text-center">Rate</th>
                                <th style="width: 30px;" class="text-center">Qty</th>
                                <th style="width: 60px;" class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orderItems as $idx =>$item)
                                @php
                                    $unitPrice = (float)($item->price ?? ($item->unit_price ?? ($item->rate ?? 0)));
                                    $qty = (int)($item->quantity ?? ($item->qty ?? 1));
                                    $lineTotal = (float)($item->total ?? ($item->total_price ?? ($unitPrice * $qty)));

                                    $pTitle =$item->product_title ?? ($item->product_name ?? ($item->title ?? null));
                                    if (empty($pTitle) && !empty($item->product_id)) {
                                        $pTitle = \Illuminate\Support\Facades\DB::table('products')->where('id',$item->product_id)->value('title');
                                    }
                                    $pTitle =$pTitle ?: ('Product #' . ($item->product_id ?? ($idx + 1)));

                                    $vColor = $item->color ?? ($item->pv_color ?? null);
                                    $vSize  = $item->size ?? ($item->pv_size ?? null);
                                    $vTitle =$item->variant_title ?? null;
                                    $vSku   =$item->sku ?? ($item->pv_code ?? ($item->pv_sku ?? null));

                                    if (!empty($item->variant_id) && (empty($vColor) && empty($vSize))) {
                                        $vRec = \Illuminate\Support\Facades\DB::table('product_variants')->where('id',$item->variant_id)->first();
                                        if ($vRec) {$vColor = $vRec->color ?? $vColor;
                                            $vSize  = $vRec->size ?? $vSize;
                                            $vSku   =$vRec->product_code ?? ($vRec->sku ?? $vSku);
                                            $parts  = array_filter([$vColor, $vSize]);$vTitle = count($parts) > 0 ? implode(' / ', $parts) : ($vRec->name ?? $vTitle);
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $idx + 1 }}</td>
                                    <td class="safe-text-break">
                                        <strong>{{ $pTitle }}</strong>
                                        @if(!empty($vColor) || !empty($vSize) || !empty($vTitle))
                                            <span style="font-size: 8px; display: block; color: #333;">
                                                Variant: {{ trim(($vColor ? 'Color: ' . $vColor : '') . ($vColor && $vSize ? ' \vert{} ' : '') . ($vSize ? 'Size: ' . $vSize : '')) ?:$vTitle }}
                                            </span>
                                        @endif
                                        @if(!empty($vSku))
                                            <span style="font-size: 8px; font-family: monospace; display: block; color: #b91c1c; font-weight: bold;">
                                                SKU: {{ $vSku }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">৳{{ number_format($unitPrice, 2) }}</td>
                                    <td class="text-center">{{ $qty }}</td>
                                    <td class="text-end">৳{{ number_format($lineTotal, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-2">No item records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <!-- Totals & Notes Section -->
                    <div class="totals-area">
                        <div class="meta-box safe-text-break">
                            <strong>Order Instructions / Notes:</strong><br>
                            <span>{{ $orderNote }}</span>
                        </div>

                        <table class="totals-table">
                            <tr>
                                <td>Subtotal:</td>
                                <td class="text-end">৳{{ number_format($subtotalAmt, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Delivery Fee:</td>
                                <td class="text-end">৳{{ number_format($deliveryFee, 2) }}</td>
                            </tr>
                            @if(!empty($discountAmt) &&$discountAmt > 0)
                            <tr>
                                <td>Discount:</td>
                                <td class="text-end">-৳{{ number_format($discountAmt, 2) }}</td>
                            </tr>
                            @endif
                            <tr style="font-weight: bold; background: #eee;">
                                <td>Grand Total:</td>
                                <td class="text-end">৳{{ number_format($grandTotalAmt, 2) }}</td>
                            </tr>
                            @if($paidAmt > 0)
                            <tr>
                                <td>Paid Amount:</td>
                                <td class="text-end">৳{{ number_format($paidAmt, 2) }}</td>
                            </tr>
                            <tr style="font-weight: bold;">
                                <td>Collectible Due:</td>
                                <td class="text-end">৳{{ number_format($dueAmt, 2) }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>

                <!-- Footer Section -->
                <div>
                    <div class="memo-seal-row">
                        <div class="seal-box">Customer Copy</div>
                        <div class="seal-box">Authorized Seal</div>
                    </div>

                    <div class="footer-terms">
                        For support or queries:
                        @if(!empty($sitePhone))
                            contact <strong>{{ $sitePhone }}</strong>
                        @endif
                        @if(!empty($sitePhone) && !empty($siteEmail))
                            &bull;
                        @endif
                        @if(!empty($siteEmail))
                            Email: <strong>{{ $siteEmail }}</strong>
                        @endif
                        @if(empty($sitePhone) && empty($siteEmail))
                            contact our support team
                        @endif
                        &bull; This is Computer Generated Invoice
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const cleanInvName = "{{ $cleanInvoiceNo }}";
        document.title = cleanInvName;

        try {
            JsBarcode("#barcode", cleanInvName, {
                format: "CODE128",
                width: 1.15,
                height: 28,
                displayValue: true,
                fontSize: 8.5,
                margin: 0
            });
        } catch (e) {
            console.warn("Barcode rendering fallback:", e);
        }

        function autoFitMemoOnMobile() {
            const stage   = document.getElementById('memoViewportStage');
            const wrapper = document.getElementById('memoScaleWrapper');
            const content = document.getElementById('printable-invoice-content');

            if (!stage || !wrapper || !content) return;

            wrapper.style.transform = 'none';

            const contentWidth = content.offsetWidth;
            const availableWidth = window.innerWidth - 16;

            if (availableWidth < contentWidth) {
                const scaleRatio = availableWidth / contentWidth;
                wrapper.style.transform = `scale(${scaleRatio})`;
                wrapper.style.transformOrigin = 'top center';
                
                const scaledHeight = content.offsetHeight * scaleRatio;
                stage.style.height = (scaledHeight + 24) + 'px';
            } else {
                wrapper.style.transform = 'none';
                stage.style.height = 'auto';
            }
        }

        window.addEventListener('resize', autoFitMemoOnMobile);
        window.addEventListener('DOMContentLoaded', autoFitMemoOnMobile);

        function executeDownloadPDF() {
            const btn = document.getElementById('btn-download-pdf');
            const btnText = document.getElementById('btnPdfText');
            const wrapper = document.getElementById('memoScaleWrapper');

            const prevTransform = wrapper.style.transform;
            wrapper.style.transform = 'none';

            btnText.innerText = 'Creating PDF...';
            btn.disabled = true;

            const element = document.getElementById('printable-invoice-content');
            const opt = {
                margin:       [4, 4, 4, 4],
                filename:     cleanInvName + '.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, logging: false },
                jsPDF:        { unit: 'mm', format: 'a5', orientation: 'portrait' }
            };

            html2pdf().set(opt).from(element).save().then(function() {
                btnText.innerText = 'Save PDF';
                btn.disabled = false;
                wrapper.style.transform = prevTransform;
            }).catch(function(err) {
                console.error(err);
                btnText.innerText = 'Save PDF';
                btn.disabled = false;
                wrapper.style.transform = prevTransform;
                window.print();
            });
        }

        function executePrint() {
            document.title = cleanInvName;
            window.print();
        }
    </script>
</body>
</html>