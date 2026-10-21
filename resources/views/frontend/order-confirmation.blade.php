@extends('layouts.app')

@php
    // ১. Settings table theke dynamic brand title, phone ebong email read kora
    $siteSettings = \Illuminate\Support\Facades\DB::table('settings')
        ->whereIn('key', ['footer_brand_title', 'footer_phone', 'footer_email'])
        ->pluck('value', 'key')
        ->toArray();

    $siteName = !empty($siteSettings['footer_brand_title']) 
        ? $siteSettings['footer_brand_title'] 
        : config('app.name', 'SHOP NOW BD ONLINE');

    $sitePhone = $siteSettings['footer_phone'] ?? '';
    $siteEmail = $siteSettings['footer_email'] ?? '';

    // ২. Delivery fee & calculation fix
    $subtotalAmt   = (float)($order->subtotal ?? 0);
    $grandTotalAmt = (float)($order->grand_total ?? 0);
    $discountAmt   = (float)($order->discount_amount ?? ($order->discount ?? 0));

    if (!empty($order->delivery_charge) && (float)$order->delivery_charge > 0) {
        $deliveryFee = (float)$order->delivery_charge;
    } elseif (!empty($order->shipping_charge) && (float)$order->shipping_charge > 0) {
        $deliveryFee = (float)$order->shipping_charge;
    } else {
        $calcDiff = $grandTotalAmt - ($subtotalAmt - $discountAmt);
        $deliveryFee = max(0, $calcDiff);
    }

    $cleanInvoiceNo = preg_replace('/[^A-Za-z0-9_-]/', '', $order->invoice_no ?? ('INV-' . $order->id));
    $orderBarcode   = !empty($order->barcode) ? $order->barcode : ($order->invoice_no ?? ('88' . str_pad($order->id, 10, '0', STR_PAD_LEFT)));
    
    // ৩. Payment Success Verification (Only Paid/Completed holei true hobe)
    $isOrderPaid = in_array(strtolower($order->payment_status ?? ''), ['paid', 'completed']) || !empty($digitalDetails['is_paid']);
@endphp

@section('title', 'Order Token #' . $cleanInvoiceNo . ' - ' . $siteName)

@section('content')
<!-- JsBarcode Script -->
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<!-- Direct html2canvas & jsPDF Scripts for 100% Reliable Execution -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<style>
    /* ================= SCREEN VIEW STYLING ================= */
    .invoice-wrapper {
        background-color: #f1f5f9;
        min-height: 85vh;
    }

    /* Overall Token Column (80mm width standard) */
    .token-screen-container {
        width: 100%;
        max-width: 80mm;
        margin: 0 auto;
    }

    /* Top Elements Clean Spacing */
    .token-header-box {
        margin-bottom: 16px;
    }

    .token-total-card {
        margin-bottom: 14px;
        padding: 10px 14px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .token-buttons-group {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 18px;
    }

    /* Same width and height for both action buttons */
    .token-action-btn {
        width: 100%;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        border-radius: 7px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    /* Thermal POS Receipt / Token Card */
    .thermal-receipt-card {
        width: 100%;
        max-width: 80mm;
        margin: 0 auto;
        background: #ffffff;
        border: 1px dashed #94a3b8;
        border-radius: 8px;
        padding: 14px 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        color: #0f172a;
        font-size: 11px;
        line-height: 1.35;
    }

    .thermal-dashed-divider {
        border-top: 1px dashed #475569;
        margin: 8px 0;
    }

    .system-barcode-box {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
        margin: 4px auto 6px auto;
        overflow: hidden;
    }
    .system-barcode-box svg {
        width: 100% !important;
        max-width: 70mm !important;
        height: 34px !important;
        display: block;
    }

    .thermal-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10.5px;
        margin-top: 4px;
    }
    .thermal-table th {
        border-bottom: 1px dashed #000;
        padding: 4px 2px;
        text-align: left;
        font-weight: 700;
        font-size: 10px;
        text-transform: uppercase;
    }
    .thermal-table td {
        padding: 4px 2px;
        vertical-align: top;
        border-bottom: 1px dotted #e2e8f0;
    }

    .digital-box-compact {
        background: #f8fafc;
        border: 1px dashed #2563eb;
        border-radius: 6px;
        padding: 8px;
        margin: 8px 0;
        font-size: 10px;
    }

    /* Print rules */
    @media print {
        header, footer, nav, #site-header, #site-footer, 
        .print-hide, .btn, .alert, .stepper-track {
            display: none !important;
            visibility: hidden !important;
        }

        html, body {
            background: #ffffff !important;
            color: #000000 !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }

        @page {
            size: 80mm auto;
            margin: 2mm 3mm;
        }

        .thermal-receipt-card {
            border: none !important;
            box-shadow: none !important;
            padding: 4px !important;
            max-width: 100% !important;
            width: 100% !important;
        }
    }
</style>

<div class="invoice-wrapper py-3 py-md-4">
    <div class="container px-2">
        
        <!-- TOKEN BORABOR (80mm) WRAPPER -->
        <div class="token-screen-container">

            <!-- 1. Top Success Heading (With clear 16px bottom margin) -->
            <div class="text-center print-hide token-header-box">
                <div class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-circle shadow-sm mb-2" style="width: 40px; height: 40px;">
                    <i class="fas fa-check" style="font-size: 16px;"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1" style="font-size: 15px; line-height: 1.25;">Order Confirmed! 😊</h5>
                <p class="text-muted mb-0" style="font-size: 11.5px; line-height: 1.4;">Your order has been confirmed! We're getting your items ready for delivery.</p>
            </div>

            <!-- 2. Top Grand Total Highlight Card (With clear 14px bottom margin) -->
            <div class="token-total-card print-hide text-center">
                <div class="d-flex justify-content-between align-items-center px-1">
                    <span class="text-secondary fw-semibold" style="font-size: 11.5px;">Grand Total:</span>
                    <div>
                        <span class="fw-bold text-dark" style="font-size: 15.5px;">৳{{ number_format($grandTotalAmt, 2) }}</span>
                        <span class="badge bg-{{ $isOrderPaid ? 'success' : 'warning text-dark' }} text-uppercase ms-1" style="font-size: 8.5px; padding: 2px 6px;">
                            {{ $order->payment_status ?? 'UNPAID' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- 3. Action Buttons (With 10px gap between buttons & 18px margin before memo) -->
            <div class="token-buttons-group print-hide">
                <button type="button" id="btn-download-memo" onclick="downloadThermalMemoPDF()" class="btn btn-dark token-action-btn shadow-sm">
                    <i class="fas fa-download me-1"></i> Download Order Token
                </button>
                <a href="{{ route('shop') }}" class="btn btn-outline-secondary token-action-btn">
                    <i class="fas fa-shopping-bag me-1"></i> Continue Shopping
                </a>
            </div>

            <!-- 4. THERMAL TOKEN / RECEIPT MEMO (THIS CARD IS SAVED AS PDF) -->
            <div id="printable-memo-card" class="thermal-receipt-card">
                
                <!-- Store Header -->
                <div class="text-center">
                    <h5 class="fw-bold text-dark mb-0 text-uppercase" style="font-size: 14px; letter-spacing: 0.5px;">
                        {{ $siteName }}
                    </h5>
                    <div class="text-muted fw-semibold" style="font-size: 9.5px; text-transform: uppercase;">
                        Order Confirmation Slip
                    </div>
                    @if(!empty($sitePhone) || !empty($siteEmail))
                        <div class="text-secondary" style="font-size: 9px;">
                            @if(!empty($sitePhone)) Hotline: {{ $sitePhone }} @endif
                            @if(!empty($sitePhone) && !empty($siteEmail)) | @endif
                            @if(!empty($siteEmail)) {{ $siteEmail }} @endif
                        </div>
                    @endif
                </div>

                <!-- Barcode At The Top -->
                <div class="system-barcode-box">
                    <svg id="order-official-barcode"></svg>
                </div>

                <!-- Invoice & Date Info -->
                <div class="d-flex justify-content-between align-items-center" style="font-size: 10px;">
                    <span>Inv: <strong class="font-monospace">#{{ $order->invoice_no }}</strong></span>
                    <span>{{ date('d-M-Y h:i A', strtotime($order->created_at)) }}</span>
                </div>

                <div class="thermal-dashed-divider"></div>

                <!-- Customer & Payment Details -->
                <div style="font-size: 10px;">
                    <div class="d-flex justify-content-between">
                        <span>Customer:</span>
                        <strong class="text-truncate" style="max-width: 48mm;">{{ $order->customer_name ?? ($order->name ?? 'Guest') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Phone:</span>
                        <strong>{{ $order->customer_phone ?? ($order->phone ?? 'N/A') }}</strong>
                    </div>
                    @if(!empty($order->customer_address) && $order->customer_address !== 'Digital Delivery')
                        <div>
                            <span>Address:</span>
                            <span class="text-secondary">{{ $order->customer_address }}@if(!empty($order->district)), {{ $order->district }}@endif</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between mt-0.5">
                        <span>Payment:</span>
                        <strong class="text-uppercase">{{ $order->payment_method }} ({{ strtoupper($order->payment_status) }})</strong>
                    </div>
                    @if(!empty($order->transaction_id))
                        <div class="d-flex justify-content-between">
                            <span>Trx ID:</span>
                            <span class="font-monospace fw-bold">{{ $order->transaction_id }}</span>
                        </div>
                    @endif
                </div>

                <div class="thermal-dashed-divider"></div>

                <!-- Items Table -->
                <table class="thermal-table">
                    <thead>
                        <tr>
                            <th style="width: 50%;">Item</th>
                            <th class="text-center" style="width: 15%;">Qty</th>
                            <th class="text-end" style="width: 35%;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orderItems as $item)
                            @php
                                $pTitle = $item->product_title ?? ($item->product_table_title ?? ($item->product_name ?? ('Product #' . ($item->product_id ?? ''))));
                                if (empty($item->product_title) && !empty($item->product_id)) {
                                    $dbTitle = DB::table('products')->where('id', $item->product_id)->value('title');
                                    if (!empty($dbTitle)) $pTitle = $dbTitle;
                                }

                                $vColor = $item->color ?? null;
                                $vSize  = $item->size ?? null;
                                $vSku   = $item->sku ?? null;
                            @endphp
                            <tr>
                                <td>
                                    <strong class="d-block text-dark">{{ $pTitle }}</strong>
                                    @if(!empty($vColor) || !empty($vSize))
                                        <span style="font-size: 8.5px; color: #475569;">
                                            @if(!empty($vColor)) {{ $vColor }} @endif
                                            @if(!empty($vColor) && !empty($vSize)) / @endif
                                            @if(!empty($vSize)) {{ $vSize }} @endif
                                        </span>
                                    @endif
                                    @if(!empty($vSku))
                                        <span style="font-size: 8px; color: #dc2626; display: block; font-family: monospace;">SKU: {{ $vSku }}</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end fw-bold">৳{{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="thermal-dashed-divider"></div>

                <!-- Calculation Summary -->
                <div style="font-size: 10.5px;">
                    <div class="d-flex justify-content-between py-0.5">
                        <span>Subtotal:</span>
                        <strong>৳{{ number_format($subtotalAmt, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between py-0.5">
                        <span>Delivery Charge:</span>
                        <strong>৳{{ number_format($deliveryFee, 2) }}</strong>
                    </div>
                    @if(!empty($discountAmt) && $discountAmt > 0)
                        <div class="d-flex justify-content-between py-0.5 text-danger">
                            <span>Discount:</span>
                            <strong>-৳{{ number_format($discountAmt, 2) }}</strong>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between border-top border-dark pt-1 mt-1 fw-bold" style="font-size: 12.5px;">
                        <span>Grand Total:</span>
                        <span>৳{{ number_format($grandTotalAmt, 2) }}</span>
                    </div>
                </div>

                <!-- ================= DIGITAL DELIVERY ACCESS SECTION ================= -->
                @if(!empty($digitalDetails['has_digital']))
                    <div class="digital-box-compact">
                        <strong class="d-block mb-1 text-primary"><i class="fas fa-cloud-download-alt me-1"></i> Digital Delivery Access:</strong>
                        
                        @if($isOrderPaid)
                            {{-- ১. শুধুমাত্র Payment Success / Paid hole Download Files dekhabe --}}
                            @if(!empty($digitalDetails['downloads']) && count($digitalDetails['downloads']) > 0)
                                <div class="mb-2">
                                    <span class="fw-bold d-block text-dark" style="font-size: 10px;">Files:</span>
                                    @foreach($digitalDetails['downloads'] as $dFile)
                                        @php $fileObj = (object)$dFile; @endphp
                                        <div class="mb-1 pb-1 border-bottom border-light">
                                            <div class="text-truncate"><strong>{{ $fileObj->product_title ?? 'File' }}:</strong> {{ $fileObj->file_name ?? 'Download' }}</div>
                                            @if($fileObj->is_accessible ?? true)
                                                <a href="{{ $fileObj->download_url }}" target="_blank" class="fw-bold text-decoration-none text-primary" style="font-size: 9.5px;">
                                                    [Click Here to Download]
                                                </a>
                                            @else
                                                <span class="text-danger" style="font-size: 8.5px;">Link Expired / Limit Exceeded</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- ২. শুধুমাত্র Payment Success / Paid hole License Keys dekhabe (Quantity onujayi Key #1, Key #2) --}}
                            @if(!empty($digitalDetails['licenses']) && count($digitalDetails['licenses']) > 0)
                                <div>
                                    <span class="fw-bold d-block text-dark" style="font-size: 10px;">License Keys:</span>
                                    @foreach($digitalDetails['licenses'] as $licIdx => $lic)
                                        @php $licObj = (object)$lic; @endphp
                                        <div class="p-1 bg-white border rounded mt-1 font-monospace text-break" style="font-size: 9.5px;">
                                            <strong>{{ $licObj->product_title ?? 'Product' }} (Key #{{ $licIdx + 1 }}):</strong><br>
                                            @if(!empty($licObj->license_key))
                                                @if(str_starts_with($licObj->license_key, 'http://') || str_starts_with($licObj->license_key, 'https://'))
                                                    <a href="{{ $licObj->license_key }}" target="_blank" class="text-primary fw-bold text-break text-decoration-underline">
                                                        {{ $licObj->license_key }}
                                                    </a>
                                                @else
                                                    <span class="text-success fw-bold">{{ $licObj->license_key }}</span>
                                                @endif
                                            @endif

                                            @if(!empty($licObj->access_instructions))
                                                <div class="text-muted mt-0.5" style="font-size: 8.5px; font-family: sans-serif;">
                                                    Note: {{ $licObj->access_instructions }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @else
                            {{-- ৩. Payment Pending / Unpaid thakle kono Key ba Link dekhabe na --}}
                            <div class="p-1.5 bg-warning-subtle text-dark border border-warning rounded text-center mt-1" style="font-size: 9px; line-height: 1.35;">
                                <i class="fas fa-lock text-warning me-1"></i>
                                <strong>Payment Verification Pending:</strong><br>
                                Payment complete / verified holei apnar digital download links ebong license keys gulo ekhane shorashori unlock hobe.
                            </div>
                        @endif
                    </div>
                @endif

                <div class="thermal-dashed-divider"></div>

                <!-- Footer Note -->
                <div class="text-center text-muted" style="font-size: 9px;">
                    <div>Thank You for Shopping with<br>{{ $siteName }}!</div>
                </div>

            </div>

        </div>

    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const barcodeVal = "{{ $orderBarcode }}";
        if (barcodeVal) {
            try {
                JsBarcode("#order-official-barcode", String(barcodeVal), {
                    format: "CODE128",
                    lineColor: "#000000",
                    width: 1.15,
                    height: 34,
                    displayValue: true,
                    fontSize: 9,
                    margin: 0,
                    fontOptions: "bold",
                    font: "monospace"
                });
            } catch (e) {
                console.error("Barcode generation failed:", e);
            }
        }
    });

    // 1-Click Direct Dynamic Thermal PDF Download (Exact Proportional Height)
    function downloadThermalMemoPDF() {
        const btn = document.getElementById('btn-download-memo');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Memo downloading...';
        btn.disabled = true;

        const element = document.getElementById('printable-memo-card');
        const filename = '{{ $cleanInvoiceNo }}.pdf';

        if (typeof html2canvas === 'undefined' || !window.jspdf || !window.jspdf.jsPDF) {
            alert('PDF library is loading, please try again in 2 seconds.');
            btn.innerHTML = originalText;
            btn.disabled = false;
            return;
        }

        html2canvas(element, {
            scale: 2.5,
            useCORS: true,
            backgroundColor: '#ffffff',
            logging: false,
            windowWidth: element.scrollWidth
        }).then(function(canvas) {
            const pdfWidth = 80; // 80mm thermal receipt width
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
        }).catch(function(err) {
            console.error('PDF generation error:', err);
            alert('PDF download error: ' + err.message);
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }
</script>
@endsection