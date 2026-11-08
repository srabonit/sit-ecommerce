@php
    $branch = $branch ?? ($order->branch ?? null);

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
        : ($siteTitle ?? ($siteSettings['site_title'] ?? ($siteSettings['logo_title'] ?? config('app.name', 'SHOP NOW BD'))));

    $sitePhone = !empty($order->branch_phone) 
        ? $order->branch_phone 
        : ($branch->phone ?? ($siteSettings['footer_phone'] ?? ''));

    $siteEmail = !empty($branch->email) 
        ? $branch->email 
        : (!empty($siteSettings['footer_email']) ? $siteSettings['footer_email'] : '');

    $branchAddress = !empty($order->branch_address) 
        ? $order->branch_address 
        : ($branch->address ?? ($siteSettings['company_address'] ?? ($siteSettings['contact_address'] ?? '')));

    // Central & Branch BIN Resolver
    $companyBin = !empty($companyBin) 
        ? $companyBin 
        : (!empty($order->branch_bin) 
            ? $order->branch_bin 
            : ($order->bin_number ?? ($branch->bin_number ?? ($siteSettings['company_bin'] ?? ($siteSettings['bin_number'] ?? '')))));

    $vatLabelText = !empty($vatLabel) ? $vatLabel : (!empty($siteSettings['vat_label']) ? $siteSettings['vat_label'] : 'Govt. VAT');
    $posVatDisplayMode = $siteSettings['pos_vat_display_mode'] ?? 'item_breakdown';

    $subtotalAmt   = (float)($order->subtotal ?? ($order->sub_total ?? 0));
    $grandTotalAmt = (float)($order->grand_total ?? ($order->total_amount ?? ($order->total ?? 0)));
    $discountAmt   = (float)($order->discount_amount ?? ($order->discount ?? 0));
    $itemsList     = !empty($items) ? $items : ($order->items ?? []);

    // Delivery Charge
    $deliveryFee = 0.0;
    if (isset($order->shipping_charge) && is_numeric($order->shipping_charge) && (float)$order->shipping_charge > 0) {
        $deliveryFee = (float)$order->shipping_charge;
    } elseif (isset($order->delivery_charge) && is_numeric($order->delivery_charge) && (float)$order->delivery_charge > 0) {
        $deliveryFee = (float)$order->delivery_charge;
    } elseif (isset($order->shipping_cost) && is_numeric($order->shipping_cost) && (float)$order->shipping_cost > 0) {
        $deliveryFee = (float)$order->shipping_cost;
    }
    $deliveryFee = round($deliveryFee, 2);

    // VAT Resolution
    $taxAmt = 0.0;
    if (isset($order->tax_amount) && !is_null($order->tax_amount)) {
        $taxAmt = (float)$order->tax_amount;
    } elseif (isset($order->product_tax_amount) && !is_null($order->product_tax_amount)) {
        $taxAmt = (float)$order->product_tax_amount;
    }
    $taxAmt = round(max(0.0, $taxAmt), 2);
    $isTaxInclusive = (bool)($order->is_tax_inclusive ?? false);

    // Paid & Due
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

    $customerName    = $order->customer_name ?? ($order->user?->name ?? ($order->name ?? ($order->shipping_name ?? 'Walk-in Customer')));
    $customerPhone   = $order->customer_phone ?? ($order->user?->phone ?? ($order->phone ?? ($order->shipping_phone ?? '')));
    $customerAddress = $order->customer_address ?? ($order->user?->address ?? ($order->address ?? ($order->shipping_address ?? '')));

    $salesPersonName = $order->salesPerson?->name ?? ($order->creator?->name ?? null);

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
    <style>
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color: #000000 !important; /* থার্মাল প্রিন্টারে শতভাগ গাঢ় ও কালো লেখা */
        }

        html, body {
            background-color: #ffffff !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 48mm !important; /* ৫৮ মিমি রোল পেপারের আসল হেড সাইজ */
            max-width: 48mm !important;
            height: auto !important;
            min-height: 0 !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Courier New", monospace;
            font-size: 8.5px;
            line-height: 1.25;
        }

        .no-print-bar {
            width: 100%;
            max-width: 48mm;
            margin: 0 auto 6px auto;
            display: flex;
            gap: 4px;
            justify-content: center;
        }
        .action-btn {
            background: #000;
            color: #fff !important;
            border: none;
            padding: 5px 8px;
            font-size: 9px;
            font-weight: 700;
            border-radius: 3px;
            cursor: pointer;
        }

        /* মূল রিসিট কন্টেইনার - ফুল উইডথ ফিক্স */
        .thermal-card-58 {
            width: 48mm !important;
            max-width: 48mm !important;
            margin: 0 auto !important;
            background: #ffffff !important;
            padding: 2px 0 4px 0 !important;
            font-size: 8.5px;
            line-height: 1.2;
            overflow: hidden;
            height: auto !important;
            min-height: 0 !important;
        }

        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: 700 !important; }
        .font-monospace { font-family: "Courier New", Courier, monospace !important; font-weight: 700; }
        
        .dashed-line {
            border-top: 1px dashed #000000;
            margin: 4px 0;
            width: 100%;
        }

        table.receipt-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2px;
            table-layout: fixed;
        }
        table.receipt-table th {
            border-bottom: 1px dashed #000000;
            padding: 2px 1px;
            font-size: 8px;
            text-transform: uppercase;
            font-weight: 800;
        }
        table.receipt-table td {
            padding: 2px 1px;
            vertical-align: top;
            font-size: 8px;
            word-break: break-word;
        }

        .qr-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin: 5px 0 2px 0;
        }
        .qr-wrapper img, .qr-wrapper canvas {
            display: block;
            margin: 0 auto;
        }

        /* স্ট্রিক্ট প্রিন্ট মিডিয়া রুলস */
        @page {
            size: 58mm auto;
            margin: 0mm !important;
        }

        @media print {
            html, body {
                width: 48mm !important;
                max-width: 48mm !important;
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
                height: auto !important;
                min-height: 0 !important;
            }
            .no-print-bar {
                display: none !important;
            }
            .thermal-card-58 {
                width: 48mm !important;
                max-width: 48mm !important;
                padding: 0 !important;
                margin: 0 !important;
                border: none !important;
                box-shadow: none !important;
                height: auto !important;
                min-height: 0 !important;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <button type="button" class="action-btn" onclick="window.print()">
            🖨️ Print
        </button>
    </div>

    <div id="thermal-receipt-element" class="thermal-card-58">
        
        <!-- ব্রাঞ্চ ও শপ হেডার -->
        <div class="text-center">
            <div style="font-size: 13px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.3px; line-height: 1.1;">
                {{ $siteName }}
            </div>

            <div style="font-size: 8px; margin-top: 2px; font-weight: 600;">
                {{ !empty($order->branch_name) ? $order->branch_name : ($branch->name ?? 'Main Outlet') }}
                @if(!empty($branchAddress)) | {{ $branchAddress }} @endif
            </div>

            {{-- @if(!empty($companyBin))
                <div style="font-size: 7.5px; font-family: monospace; font-weight: 700; margin-top: 1px;">
                    BIN: {{ $companyBin }}
                </div>
            @endif --}}

            @if(!empty($sitePhone) || !empty($siteEmail))
                <div style="font-size: 9px; margin-top: 1px; font-weight: 600;">
                    @if(!empty($sitePhone)) Hot: {{ $sitePhone }} @endif
                    @if(!empty($sitePhone) && !empty($siteEmail)) | @endif
                    @if(!empty($siteEmail)) {{ $siteEmail }} @endif
                </div>
            @endif
        </div>

        <div class="dashed-line"></div>

        <!-- ইনভয়েস ও কাস্টমার তথ্য (কোনো লেখা কাটা পড়বে না) -->
        <div style="font-size: 8px; line-height: 1.3;">
            <div style="font-weight: 700;">
                Inv: <span class="font-monospace">#{{ $cleanInvoiceNo }}</span>
            </div>
            <div style="font-size: 7.5px;">
                Date: {{ function_exists('format_datetime') ? format_datetime($order->created_at, 'd-M-y h:i A') : date('d-M-y h:i A', strtotime($order->created_at)) }}
            </div>
            
            <div class="dashed-line" style="margin: 2px 0;"></div>

            <div style="display: flex; font-size 10px; justify-content: space-between; gap: 2px;">
                <span class="fw-bold" style="max-width: 55%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    {{ $customerName }}
                </span>
                <span class="font-monospace fw-bold">{{ $customerPhone }}</span>
            </div>

            @if(!empty($customerAddress) && $customerAddress !== 'Digital Delivery' && $customerAddress !== 'N/A')
                <div style="font-size: 8px; margin-top: 1px;">
                    Addr: {{ $customerAddress }}
                </div>
            @endif
            @if(!empty($salesPersonName))
                <div style="font-size: 8px; margin-top: 1px;">
                    Served By: {{ $salesPersonName }}
                </div>
            @endif
        </div>

        <div class="dashed-line"></div>

        <!-- প্রোডাক্ট আইটেম টেবিল (৪টি কলাম ৪৮ মিমি-র ভেতরে পারফেক্ট ফিট) -->
        <table class="receipt-table">
            <thead>
                <tr>
                    <th style="text-align: left; width: 44%;">ITEM</th>
                    <th class="text-center" style="width: 12%;">QTY</th>
                    <th class="text-end" style="width: 22%;">RATE</th>
                    <th class="text-end" style="width: 22%;">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($itemsList as $it)
                    @php
                        $title = $it->product_title ?? ($it->title ?? ($it->main_title ?? ($it->product_name ?? ($it->product->name ?? ($it->product->title ?? 'Product')))));
                        $sku = $it->pv_sku ?? ($it->sku ?? ($it->product_code ?? ($it->main_sku ?? ($it->variant?->sku ?? ($it->product?->sku ?? '')))));
                        $qty = (int)($it->quantity ?? ($it->qty ?? 1));
                        $unitPrice = (float)($it->unit_price ?? ($it->price ?? 0));
                        $subtotal = (float)($it->subtotal ?? ($it->total ?? ($unitPrice * $qty)));

                        $varParts = array_filter([
                            $it->color ?? ($it->pv_color ?? ($it->variant?->color ?? null)),
                            $it->size ?? ($it->pv_size ?? ($it->variant?->size ?? null))
                        ]);
                        $varText = !empty($it->variant_title) ? $it->variant_title : (count($varParts) > 0 ? implode(' / ', $varParts) : null);
                        $lineTaxRate = isset($it->tax_rate) ? (float)$it->tax_rate : 0.0;
                    @endphp
                    <tr>
                        <td style="text-align: left;">
                            <strong style="display: block; font-size: 9px; line-height: 1.15;">{{ $title }}</strong>
                            @if(!empty($varText))
                                <span style="font-size: 9px; display: block; font-weight: 700;">
                                    [{{ $varText }}]
                                </span>
                            @endif
                            @if(!empty($sku))
                                <span style="font-size: 9px; font-family: monospace; display: block;">
                                    {{ $sku }}
                                </span>
                            @endif
                        </td>
                        <td  style="font-size: px;"  class="text-center fw-bold">{{ $qty }}</td>
                        <td  style="font-size: px;"  class="text-end font-monospace">{{ number_format($unitPrice, 1) }}</td>
                        <td  style="font-size: px;"  class="text-end font-monospace fw-bold">{{ number_format($subtotal, 1) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="dashed-line"></div>

        <!-- হিসাব বিবরণী (সম্পূর্ণ দৃশ্যমান, ডানপাশে কাটা পড়বে না) -->
        <div style="font-size: 10px; line-height: 1.35;">
            <div style="display: flex; justify-content: space-between;">
                <span>Subtotal:</span>
                <span class="font-monospace fw-bold">{{ number_format($subtotalAmt, 2) }}</span>
            </div>

            @if($discountAmt > 0)
                <div style="display: flex; justify-content: space-between;">
                    <span>Discount:</span>
                    <span class="font-monospace">-{{ number_format($discountAmt, 2) }}</span>
                </div>
            @endif

            @if($taxAmt > 0 && $posVatDisplayMode !== 'hidden')
                <div style="display: flex; justify-content: space-between;">
                    <span>{{ $vatLabelText }} {{ $isTaxInclusive ? '(Inc.)' : '' }}:</span>
                    <span class="font-monospace fw-bold">{{ number_format($taxAmt, 2) }}</span>
                </div>
            @endif

            @if($deliveryFee > 0)
                <div style="display: flex; justify-content: space-between;">
                    <span>Delivery Fee:</span>
                    <span class="font-monospace">{{ number_format($deliveryFee, 2) }}</span>
                </div>
            @endif

            <div class="dashed-line" style="margin: 2px 0;"></div>

            <div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: 900;">
                <span>Total Amount:</span>
                <span class="font-monospace">{{ number_format($grandTotalAmt, 2) }} ৳</span>
            </div>
            
            <div style="display: flex; font-size: 10px; justify-content: space-between; margin-top: 1px;">
                <span>Paid ({{ ucfirst($order->payment_method ?? 'Cash') }}):</span>
                <span class="font-monospace fw-bold">{{ number_format($paidAmt, 2) }}</span>
            </div>

            @if($dueAmt > 0)
                <div style="display: flex; justify-content: space-between; font-weight: 800;">
                    <span>Due Amount:</span>
                    <span class="font-monospace">{{ number_format($dueAmt, 2) }}</span>
                </div>
            @endif
        </div>

        <div class="dashed-line"></div>

        <!-- কিউআর কোড (একদম শার্প এবং পারফেক্ট সাইজ) -->
        <div class="qr-wrapper text-center">
            <div id="pos-thermal-qrcode"></div>
            <span style="font-size: 10px; margin-top: 2px; font-family: monospace; font-weight: 700;">
                Scan to Verify Slip
            </span>
        </div>

        <div class="text-center" style="font-size: 10px; margin-top: 3px; line-height: 1.2; font-weight: 600;">
            Thank you for shopping with 
        </div>
          <div class="text-center" style="font-size: 10px; margin-top: 3px; line-height: 1.2; font-weight: 600;">
            <strong>{{ $siteName }}</strong>!
        </div>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const verifyPayload = "{{ $verifyUrl }}";
            new QRCode(document.getElementById("pos-thermal-qrcode"), {
                text: verifyPayload,
                width: 52,
                height: 52,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.M
            });
        });
    </script>
</body>
</html>