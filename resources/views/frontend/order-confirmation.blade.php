@extends('layouts.app')

@php
    // গ্লোবাল সেটিংস ক্যাশ রেজলভার
    static $tokenSettingsCache = null;
    if ($tokenSettingsCache === null) {
        $tokenSettingsCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
            return \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $globalSettings = $tokenSettingsCache ?? [];

    $siteName = !empty($globalSettings['footer_brand_title']) 
        ? $globalSettings['footer_brand_title'] 
        : config('app.name', 'SHOP NOW BD ONLINE');

    $sitePhone = $globalSettings['footer_phone'] ?? '';
    $siteEmail = $globalSettings['footer_email'] ?? '';

    // Central & Branch BIN Resolver for NBR Mushak 6.3 Compliance
    $companyBin = !empty($order->branch_bin) 
        ? $order->branch_bin 
        : ($order->bin_number ?? ($globalSettings['company_bin'] ?? ($globalSettings['bin_number'] ?? '001293847-0101')));

    // Order Confirmation / Token Dynamic Color Resolvers
    $tokenPageBg          = $globalSettings['color_token_page_bg'] ?? ($globalSettings['color_site_bg'] ?? '#f1f5f9');
    $tokenSuccessIconBg   = $globalSettings['color_token_success_icon_bg'] ?? '#16a34a';
    $tokenSuccessIconText = $globalSettings['color_token_success_icon_text'] ?? '#ffffff';
    $tokenHeadingColor    = $globalSettings['color_token_heading'] ?? '#0f172a';
    $tokenSubtextColor    = $globalSettings['color_token_subtext'] ?? '#64748b';

    $tokenTotalCardBg     = $globalSettings['color_token_total_card_bg'] ?? '#ffffff';
    $tokenTotalCardBorder = $globalSettings['color_token_total_card_border'] ?? '#cbd5e1';
    $tokenTotalCardLabel  = $globalSettings['color_token_total_card_label'] ?? '#64748b';
    $tokenTotalPrice      = $globalSettings['color_token_total_price'] ?? '#0f172a';

    $tokenDownloadBtnBg   = $globalSettings['color_token_download_btn_bg'] ?? '#0f172a';
    $tokenDownloadBtnText = $globalSettings['color_token_download_btn_text'] ?? '#ffffff';
    $tokenDownloadBtnHov  = $globalSettings['color_token_download_btn_hover'] ?? '#1e293b';

    $tokenContinueBtnBg   = $globalSettings['color_token_continue_btn_bg'] ?? '#ffffff';
    $tokenContinueBtnText = $globalSettings['color_token_continue_btn_text'] ?? '#475569';
    $tokenContinueBtnBrd  = $globalSettings['color_token_continue_btn_border'] ?? '#cbd5e1';
    $tokenContinueBtnHov  = $globalSettings['color_token_continue_btn_hover'] ?? '#f8fafc';

    $tokenReceiptBg       = $globalSettings['color_token_memo_bg'] ?? '#ffffff';
    $tokenReceiptBorder   = $globalSettings['color_token_memo_border'] ?? '#94a3b8';
    $tokenReceiptText     = $globalSettings['color_token_memo_text'] ?? '#0f172a';
    $tokenReceiptDivider  = $globalSettings['color_token_memo_divider'] ?? '#475569';
    $tokenDigitalBoxBg    = $globalSettings['color_token_digital_box_bg'] ?? '#f8fafc';
    $tokenDigitalBoxBrd   = $globalSettings['color_token_digital_box_border'] ?? '#2563eb';

    // ডেলিভারি ফি এবং গ্র্যান্ড টোটাল ক্যালকুলেশন
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

    // NBR Tax & VAT Ledger Snapshot Data
    $taxAmt          = (float)($order->tax_amount ?? 0);
    $productTaxAmt   = (float)($order->product_tax_amount ?? 0);
    $shippingTaxAmt  = (float)($order->shipping_tax_amount ?? 0);
    $taxableBaseAmt  = (float)($order->taxable_base_amount ?? ($subtotalAmt - $discountAmt));
    $isTaxInclusive  = (bool)($order->is_tax_inclusive ?? false);

    $cleanInvoiceNo = preg_replace('/[^A-Za-z0-9_-]/', '', $order->invoice_no ?? ('INV-' . $order->id));
    $orderBarcode   = !empty($order->barcode) ? $order->barcode : ($order->invoice_no ?? ('88' . str_pad($order->id, 10, '0', STR_PAD_LEFT)));
    
    // পেমেন্ট স্ট্যাটাস ভেরিফিকেশন
    $isOrderPaid = in_array(strtolower($order->payment_status ?? ''), ['paid', 'completed']) || !empty($digitalDetails['is_paid']);
@endphp

@section('title', 'Order Token #' . $cleanInvoiceNo . ' - ' . $siteName)

@section('content')
<!-- JsBarcode Script -->
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<!-- Direct html2canvas Script for Image Generation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<style>
    :root {
        --token-pg-bg: {{ $tokenPageBg }};
        --token-succ-ico-bg: {{ $tokenSuccessIconBg }};
        --token-succ-ico-text: {{ $tokenSuccessIconText }};
        --token-heading: {{ $tokenHeadingColor }};
        --token-subtext: {{ $tokenSubtextColor }};

        --token-total-bg: {{ $tokenTotalCardBg }};
        --token-total-border: {{ $tokenTotalCardBorder }};
        --token-total-label: {{ $tokenTotalCardLabel }};
        --token-total-price: {{ $tokenTotalPrice }};

        --token-down-bg: {{ $tokenDownloadBtnBg }};
        --token-down-text: {{ $tokenDownloadBtnText }};
        --token-down-hov: {{ $tokenDownloadBtnHov }};

        --token-cont-bg: {{ $tokenContinueBtnBg }};
        --token-cont-text: {{ $tokenContinueBtnText }};
        --token-cont-border: {{ $tokenContinueBtnBrd }};
        --token-cont-hov: {{ $tokenContinueBtnHov }};

        --token-memo-bg: {{ $tokenReceiptBg }};
        --token-memo-border: {{ $tokenReceiptBorder }};
        --token-memo-text: {{ $tokenReceiptText }};
        --token-memo-divider: {{ $tokenReceiptDivider }};
        --token-dig-box-bg: {{ $tokenDigitalBoxBg }};
        --token-dig-box-border: {{ $tokenDigitalBoxBrd }};
    }

    /* ================= SCREEN VIEW STYLING ================= */
    .invoice-wrapper {
        background-color: var(--token-pg-bg) !important;
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

    .token-success-icon-badge {
        background-color: var(--token-succ-ico-bg) !important;
        color: var(--token-succ-ico-text) !important;
        width: 40px;
        height: 40px;
    }

    .token-title-text {
        color: var(--token-heading) !important;
    }

    .token-subtitle-text {
        color: var(--token-subtext) !important;
    }

    .token-total-card {
        margin-bottom: 14px;
        padding: 10px 14px;
        background-color: var(--token-total-bg) !important;
        border: 1px solid var(--token-total-border) !important;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .token-total-label-text {
        color: var(--token-total-label) !important;
    }

    .token-total-price-val {
        color: var(--token-total-price) !important;
    }

    .token-buttons-group {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 18px;
    }

    .token-btn-download {
        background-color: var(--token-down-bg) !important;
        color: var(--token-down-text) !important;
        border: none !important;
    }
    .token-btn-download:hover {
        background-color: var(--token-down-hov) !important;
        color: var(--token-down-text) !important;
    }

    .token-btn-continue {
        background-color: var(--token-cont-bg) !important;
        color: var(--token-cont-text) !important;
        border: 1px solid var(--token-cont-border) !important;
    }
    .token-btn-continue:hover {
        background-color: var(--token-cont-hov) !important;
        color: #0f172a !important;
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
        background-color: var(--token-memo-bg) !important;
        border: 1px dashed var(--token-memo-border) !important;
        border-radius: 8px;
        padding: 14px 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        color: var(--token-memo-text) !important;
        font-size: 11px;
        line-height: 1.35;
    }

    .thermal-dashed-divider {
        border-top: 1px dashed var(--token-memo-divider) !important;
        margin: 8px 0;
    }

    .mushak-badge-pill {
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #000;
        border: 1px solid #000;
        padding: 1px 4px;
        display: inline-block;
        margin-top: 2px;
        background: #f1f5f9;
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
        color: var(--token-memo-text) !important;
    }
    .thermal-table td {
        padding: 4px 2px;
        vertical-align: top;
        border-bottom: 1px dotted #e2e8f0;
        color: var(--token-memo-text) !important;
    }

    .digital-box-compact {
        background-color: var(--token-dig-box-bg) !important;
        border: 1px dashed var(--token-dig-box-border) !important;
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
            background: #ffffff !important;
            color: #000000 !important;
        }
    }
</style>

<div class="invoice-wrapper py-3 py-md-4">
    <div class="container px-2">
        
        <!-- TOKEN WRAPPER (80mm) -->
        <div class="token-screen-container">

            <!-- 1. Top Success Heading -->
            <div class="text-center print-hide token-header-box">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle shadow-sm mb-2 token-success-icon-badge">
                    <i class="fas fa-check" style="font-size: 16px;"></i>
                </div>
                <h5 class="fw-bold mb-1 token-title-text" style="font-size: 15px; line-height: 1.25;">Order Confirmed! 😊</h5>
                <p class="mb-0 token-subtitle-text" style="font-size: 11.5px; line-height: 1.4;">Your order has been confirmed! We're getting your items ready for delivery.</p>
            </div>

            <!-- 2. Top Grand Total Highlight Card -->
            <div class="token-total-card print-hide text-center">
                <div class="d-flex justify-content-between align-items-center px-1">
                    <span class="fw-semibold token-total-label-text" style="font-size: 11.5px;">Grand Total:</span>
                    <div>
                        <span class="fw-bold token-total-price-val" style="font-size: 15.5px;">৳{{ number_format($grandTotalAmt, 2) }}</span>
                        <span class="badge bg-{{ $isOrderPaid ? 'success' : 'warning text-dark' }} text-uppercase ms-1" style="font-size: 8.5px; padding: 2px 6px;">
                            {{ $order->payment_status ?? 'UNPAID' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- 3. Action Buttons -->
            <div class="token-buttons-group print-hide">
                <button type="button" id="btn-download-memo" onclick="downloadThermalMemoJPG()" class="btn token-action-btn token-btn-download shadow-sm">
                    <i class="fas fa-download me-1"></i> Save Order Token
                </button>
                <a href="{{ route('shop') }}" class="btn token-action-btn token-btn-continue">
                    <i class="fas fa-shopping-bag me-1"></i> Continue Shopping
                </a>
            </div>

            <!-- 4. THERMAL TOKEN / RECEIPT MEMO (Mushak 6.3 & NBR Compliant) -->
            <div id="printable-memo-card" class="thermal-receipt-card">
                
                <!-- Store Header -->
                <div class="text-center">
                    <h5 class="fw-bold mb-0 text-uppercase" style="font-size: 14px; letter-spacing: 0.5px;">
                        {{ $siteName }}
                    </h5>
                    <div class="text-muted fw-semibold" style="font-size: 9.5px; text-transform: uppercase;">
                        Order Confirmation Slip
                    </div>
                    @if(!empty($sitePhone) || !empty($siteEmail))
                        <div class="text-secondary" style="font-size: 9px; margin-top: 1px;">
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
                                $lineTaxRate = isset($item->tax_rate) ? (float)$item->tax_rate : 0.0;
                            @endphp
                            <tr>
                                <td>
                                    <strong class="d-block">{{ $pTitle }}</strong>
                                    @if(!empty($vColor) || !empty($vSize))
                                        <span style="font-size: 8.5px; opacity: 0.8;">
                                            @if(!empty($vColor)) {{ $vColor }} @endif
                                            @if(!empty($vColor) && !empty($vSize)) / @endif
                                            @if(!empty($vSize)) {{ $vSize }} @endif
                                        </span>
                                    @endif
                                    <div style="font-size: 8px; font-family: monospace;">
                                        @if(!empty($vSku)) <span style="color: #dc2626;">SKU: {{ $vSku }}</span> @endif
                                        @if($lineTaxRate > 0)
                                            <span style="color: #0f172a; font-weight: bold;">(VAT: {{ number_format($lineTaxRate, 1) }}%)</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end fw-bold">৳{{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="thermal-dashed-divider"></div>

                <!-- Calculation Summary (With Reconciled NBR VAT Breakdown) -->
                <div style="font-size: 10.5px;">
                    <div class="d-flex justify-content-between py-0.5">
                        <span>Subtotal:</span>
                        <strong>৳{{ number_format($subtotalAmt, 2) }}</strong>
                    </div>
                    @if(!empty($discountAmt) && $discountAmt > 0)
                        <div class="d-flex justify-content-between py-0.5 text-danger">
                            <span>Discount:</span>
                            <strong>-৳{{ number_format($discountAmt, 2) }}</strong>
                        </div>
                    @endif
                    @if($taxAmt > 0)
                        <div class="d-flex justify-content-between py-0.5">
                            <span>Govt. VAT {{ $isTaxInclusive ? '(Inc.)' : '' }}:</span>
                            <strong>৳{{ number_format($taxAmt, 2) }}</strong>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between py-0.5">
                        <span>Delivery Charge:</span>
                        <strong>৳{{ number_format($deliveryFee, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between border-top border-dark pt-1 mt-1 fw-bold" style="font-size: 12.5px;">
                        <span>Grand Total:</span>
                        <span>৳{{ number_format($grandTotalAmt, 2) }}</span>
                    </div>
                    @if($taxAmt > 0)
                        <div style="font-size: 8px; color: #475569; margin-top: 3px; text-align: right;">
                            Taxable Base: ৳{{ number_format($taxableBaseAmt, 2) }} | Mode: {{ $isTaxInclusive ? 'Inclusive' : 'Exclusive' }}
                        </div>
                    @endif
                </div>

                <!-- DIGITAL DELIVERY ACCESS SECTION -->
                @if(!empty($digitalDetails['has_digital']))
                    <div class="digital-box-compact">
                        <strong class="d-block mb-1" style="color: var(--token-dig-box-border);"><i class="fas fa-cloud-download-alt me-1"></i> Digital Delivery Access:</strong>
                        
                        @if($isOrderPaid)
                            @if(!empty($digitalDetails['downloads']) && count($digitalDetails['downloads']) > 0)
                                <div class="mb-2">
                                    <span class="fw-bold d-block" style="font-size: 10px;">Files:</span>
                                    @foreach($digitalDetails['downloads'] as $dFile)
                                        @php $fileObj = (object)$dFile; @endphp
                                        <div class="mb-1 pb-1 border-bottom border-light">
                                            <div class="text-truncate"><strong>{{ $fileObj->product_title ?? 'File' }}:</strong> {{ $fileObj->file_name ?? 'Download' }}</div>
                                            @if($fileObj->is_accessible ?? true)
                                                <a href="{{ $fileObj->download_url }}" target="_blank" class="fw-bold text-decoration-none" style="font-size: 9.5px; color: var(--token-dig-box-border);">
                                                    [Click Here to Download]
                                                </a>
                                            @else
                                                <span class="text-danger" style="font-size: 8.5px;">Link Expired / Limit Exceeded</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if(!empty($digitalDetails['licenses']) && count($digitalDetails['licenses']) > 0)
                                <div>
                                    <span class="fw-bold d-block" style="font-size: 10px;">License Keys:</span>
                                    @foreach($digitalDetails['licenses'] as $licIdx => $lic)
                                        @php $licObj = (object)$lic; @endphp
                                        <div class="p-1 bg-white border rounded mt-1 font-monospace text-break" style="font-size: 9.5px;">
                                            <strong>{{ $licObj->product_title ?? 'Product' }} (Key #{{ $licIdx + 1 }}):</strong><br>
                                            @if(!empty($licObj->license_key))
                                                @if(str_starts_with($licObj->license_key, 'http://') || str_starts_with($licObj->license_key, 'https://'))
                                                    <a href="{{ $licObj->license_key }}" target="_blank" class="fw-bold text-break text-decoration-underline" style="color: var(--token-dig-box-border);">
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
                <div class="text-center text-muted" style="font-size: 8.5px; line-height: 1.3;">
                    <div>Thank You for Shopping with <strong>{{ $siteName }}</strong>!</div>
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

    // 1-Click Direct Crisp JPG Image Download
    function downloadThermalMemoJPG() {
        const btn = document.getElementById('btn-download-memo');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Token downloading...';
        btn.disabled = true;

        const element = document.getElementById('printable-memo-card');
        const filename = '{{ $cleanInvoiceNo }}.jpg';

        if (typeof html2canvas === 'undefined') {
            alert('Image library is loading, please try again in 2 seconds.');
            btn.innerHTML = originalText;
            btn.disabled = false;
            return;
        }

        html2canvas(element, {
            scale: 3, // High quality crisp resolution
            useCORS: true,
            backgroundColor: '#ffffff',
            logging: false,
            windowWidth: element.scrollWidth
        }).then(function(canvas) {
            const imgData = canvas.toDataURL('image/jpeg', 0.95);
            const downloadLink = document.createElement('a');
            downloadLink.href = imgData;
            downloadLink.download = filename;
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);

            btn.innerHTML = originalText;
            btn.disabled = false;
        }).catch(function(err) {
            console.error('JPG generation error:', err);
            alert('JPG download error: ' + err.message);
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }
</script>
@endsection