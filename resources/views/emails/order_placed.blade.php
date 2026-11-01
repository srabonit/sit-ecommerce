@php
    // সেন্ট্রাল গ্লোবাল সেটিংস ক্যাশ রেজলভার (বারবার DB কুয়েরি হওয়া আটকাবে)
    static $custOrderMailCache = null;
    if ($custOrderMailCache === null) {
        $custOrderMailCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
            return \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $mailSettings = $custOrderMailCache ?? [];

    // ডায়নামিক থিম কালার ভ্যারিয়েবল (Existing Default Colors)
    $emailBrandName    = $mailSettings['footer_brand_title'] ?? config('app.name', 'Shop Now BD');
    $emailSupport      = $mailSettings['footer_email'] ?? 'info.srabonit@gmail.com';
    $emailPrimaryColor = $mailSettings['color_common_btn_bg'] ?? '#2563eb';
    $emailPriceColor   = $mailSettings['color_cpc_price'] ?? ($mailSettings['color_product_price'] ?? $emailPrimaryColor);
    $emailSiteBg       = $mailSettings['color_site_bg'] ?? '#f8fafc';
    $emailCardBg       = $mailSettings['color_auth_card_bg'] ?? '#ffffff';
    $emailCardBorder   = $mailSettings['color_auth_card_border'] ?? '#e2e8f0';
    $emailTitleColor   = $mailSettings['color_page_title'] ?? '#0f172a';
    $emailTextColor    = $mailSettings['color_auth_text'] ?? '#475569';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Order Confirmed - {{ $emailBrandName }}</title>
    <style>
        /* Base Email Client Reset */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }

        body { 
            font-family: Arial, Helvetica, sans-serif; 
            background-color: {{ $emailSiteBg }}; 
            margin: 0 !important; 
            padding: 16px 8px !important; 
            color: #334155; 
            width: 100% !important;
            height: 100% !important;
        }

        .email-wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: {{ $emailSiteBg }};
        }

        .email-container { 
            max-width: 600px; 
            margin: 0 auto; 
            background-color: {{ $emailCardBg }}; 
            border-radius: 12px; 
            overflow: hidden; 
            border: 1px solid {{ $emailCardBorder }}; 
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.04);
        }

        .email-header { 
            background-color: {{ $emailPrimaryColor }} !important; 
            color: #ffffff; 
            padding: 26px 20px; 
            text-align: center; 
        }

        .email-content { 
            padding: 24px 20px; 
        }

        /* Order Summary Table */
        .order-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 15px; 
        }

        .order-table th, 
        .order-table td { 
            padding: 12px 10px; 
            border-bottom: 1px solid {{ $emailCardBorder }}; 
            text-align: left; 
            font-size: 13px; 
            vertical-align: middle;
        }

        .order-table th { 
            background-color: #f1f5f9; 
            color: {{ $emailTextColor }}; 
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .variant-badge { 
            display: inline-block; 
            padding: 2px 7px; 
            font-size: 11px; 
            background-color: #f1f5f9; 
            border: 1px solid {{ $emailCardBorder }};
            border-radius: 4px; 
            margin-right: 4px; 
            margin-top: 3px;
            font-weight: bold; 
            color: #1e293b; 
        }

        .sku-text { 
            display: block; 
            font-size: 11px; 
            color: {{ $emailPrimaryColor }}; 
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; 
            margin-top: 4px; 
            font-weight: bold; 
        }

        .theme-product-price { 
            color: {{ $emailPriceColor }} !important; 
        }

        /* Digital Products Section */
        .digital-section { 
            margin-top: 25px; 
            border-radius: 10px; 
            border: 1px solid #cbd5e1; 
            padding: 18px 16px; 
            background-color: {{ $emailSiteBg }}; 
        }

        .digital-btn { 
            display: inline-block; 
            padding: 8px 18px; 
            background-color: {{ $emailPrimaryColor }} !important; 
            color: #ffffff !important; 
            text-decoration: none !important; 
            border-radius: 50px; 
            font-size: 12px; 
            font-weight: bold; 
            transition: opacity 0.2s ease;
        }

        .digital-btn:hover {
            opacity: 0.9;
            color: #ffffff !important;
        }

        .license-box { 
            background-color: #ffffff; 
            border: 1px dashed {{ $emailPrimaryColor }}; 
            border-radius: 8px; 
            padding: 10px 14px; 
            margin-top: 8px; 
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; 
            font-size: 13px; 
            color: #1e293b; 
            font-weight: bold; 
            word-break: break-all;
        }

        .alert-pending { 
            background-color: #fef3c7; 
            border: 1px solid #fde68a; 
            color: #92400e; 
            padding: 14px; 
            border-radius: 8px; 
            font-size: 12.5px; 
            margin-top: 20px; 
            line-height: 1.5;
        }

        .shipping-details-box {
            margin-top: 22px; 
            font-size: 13px;
            background-color: {{ $emailSiteBg }};
            border: 1px solid {{ $emailCardBorder }};
            border-radius: 10px;
            padding: 14px 16px;
            line-height: 1.6;
            color: {{ $emailTitleColor }};
        }

        .email-footer { 
            background-color: {{ $emailSiteBg }}; 
            padding: 18px 16px; 
            text-align: center; 
            font-size: 12px; 
            color: {{ $emailTextColor }}; 
            border-top: 1px solid {{ $emailCardBorder }};
            line-height: 1.5;
        }

        .email-footer a {
            color: {{ $emailPrimaryColor }};
            text-decoration: none !important;
            font-weight: bold;
        }

        .bn-digit {
            font-weight: bold !important;
        }

        /* Mobile Screens Optimization (320px–575px) */
        @media only screen and (max-width: 600px) {
            body { padding: 6px !important; }
            .email-container { width: 100% !important; border-radius: 8px !important; }
            .email-header { padding: 20px 14px !important; }
            .email-header h2 { font-size: 18px !important; }
            .email-content { padding: 16px 12px !important; }
            .order-table th, .order-table td { padding: 10px 6px !important; font-size: 12px !important; }
            .order-table th.col-qty, .order-table td.col-qty { text-align: center !important; }
            .digital-section { padding: 14px 12px !important; }
            .digital-btn { width: 100% !important; text-align: center !important; box-sizing: border-box; }
            .shipping-details-box { padding: 12px !important; font-size: 12.5px !important; }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            
            <!-- Email Header Banner -->
            <div class="email-header" style="background-color: {{ $emailPrimaryColor }};">
                <h2 style="margin: 0; font-size: 20px; font-weight: bold; letter-spacing: 0.02em;">Order Confirmed!</h2>
                <p style="margin: 6px 0 0 0; font-size: 13.5px; opacity: 0.9;" class="bn-digit">
                    Invoice #{{ $order->invoice_no ?? $order->id }}[cite: 19]
                </p>
            </div>

            <!-- Email Content Body -->
            <div class="email-content">
                <p style="margin-top: 0; font-size: 14px; color: {{ $emailTitleColor }};">
                    Hello <strong>{{ $order->customer_name ?? ($order->name ?? 'Customer') }}</strong>,[cite: 19]
                </p>
                <p style="font-size: 13.5px; color: {{ $emailTextColor }}; margin-bottom: 20px;">
                    Thank you for shopping with us. Your order has been placed successfully.[cite: 19]
                </p>
                
                <h4 style="margin: 20px 0 8px 0; font-size: 14px; font-weight: bold; color: {{ $emailTitleColor }};">
                    Order Summary:[cite: 19]
                </h4>
                <table class="order-table" cellpadding="0" cellspacing="0">
                    <thead>
                        <tr>
                            <th style="width: 60%;">Item Details</th>[cite: 19]
                            <th class="col-qty" style="width: 15%; text-align: center;">Qty</th>[cite: 19]
                            <th style="width: 25%; text-align: right;">Total</th>[cite: 19]
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orderItems as $item)
                            @php
                                $qty = 1;
                                $price = 0.00;
                                $title = null;
                                $color = null;
                                $size  = null;
                                $sku   = null;
                                $vName = null;
                                $pId   = null;

                                if (is_object($item)) {
                                    $qty = (int) ($item->quantity ?? ($item->qty ?? 1));
                                    $price = (float) ($item->unit_price ?? ($item->price ?? 0));
                                    $title = $item->product_table_title ?? ($item->product_title ?? ($item->product_name ?? ($item->title ?? null)));
                                    $color = $item->color ?? ($item->pv_color ?? null);
                                    $size  = $item->size ?? ($item->pv_size ?? null);
                                    $sku   = $item->sku ?? null;
                                    $vName = $item->variant_title ?? null;
                                    $pId   = $item->product_id ?? null;
                                } elseif (is_array($item)) {
                                    $qty = (int) ($item['quantity'] ?? ($item['qty'] ?? 1));
                                    $price = (float) ($item['unit_price'] ?? ($item['price'] ?? 0));
                                    $title = $item['product_table_title'] ?? ($item['product_title'] ?? ($item['product_name'] ?? ($item['title'] ?? null)));
                                    $color = $item['color'] ?? ($item['pv_color'] ?? null);
                                    $size  = $item['size'] ?? ($item['pv_size'] ?? null);
                                    $sku   = $item['sku'] ?? null;
                                    $vName = $item['variant_title'] ?? ($item['variant_name'] ?? null);
                                    $pId   = $item['product_id'] ?? ($item['id'] ?? null);
                                }

                                if ((empty($title) || $title === 'Product' || $title === 'Product Item') && !empty($pId)) {
                                    $dbTitle = \Illuminate\Support\Facades\DB::table('products')->where('id', $pId)->value('title');
                                    if (!empty($dbTitle)) {
                                        $title = $dbTitle;
                                    }
                                }

                                if (empty($title)) {
                                    $title = 'Product #' . ($pId ?? '');
                                }
                            @endphp
                            <tr>
                                <td>
                                    <strong style="color: {{ $emailTitleColor }}; font-size: 13px;">{{ $title }}</strong>[cite: 19]
                                    @if(!empty($color) || !empty($size) || !empty($vName))
                                        <div style="margin-top: 4px;">
                                            @if(!empty($color))
                                                <span class="variant-badge">Color: {{ $color }}</span>[cite: 19]
                                            @endif
                                            @if(!empty($size))
                                                <span class="variant-badge">Size: {{ $size }}</span>[cite: 19]
                                            @endif
                                            @if(!empty($vName) && empty($color) && empty($size))
                                                <span class="variant-badge">{{ $vName }}</span>[cite: 19]
                                            @endif
                                        </div>
                                    @endif
                                    @if(!empty($sku))
                                        <span class="sku-text bn-digit" style="color: {{ $emailPrimaryColor }};">SKU: {{ $sku }}</span>[cite: 19]
                                    @endif
                                </td>
                                <td class="col-qty bn-digit" style="text-align: center; color: {{ $emailTitleColor }};">{{ $qty }}</td>[cite: 19]
                                <td class="bn-digit" style="text-align: right; font-weight: bold; color: {{ $emailTitleColor }};">৳{{ number_format($price * $qty, 2) }}</td>[cite: 19]
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        @php
                            $sub = (float)($order->subtotal ?? 0);
                            $grand = (float)($order->grand_total ?? 0);
                            $ship = (float)($order->shipping_charge ?? ($order->delivery_charge ?? ($grand - $sub)));
                            if ($ship < 0) $ship = 0;
                        @endphp
                        <tr>
                            <td colspan="2" style="text-align: right; font-weight: bold; color: {{ $emailTextColor }}; padding-top: 14px;">Subtotal:</td>[cite: 19]
                            <td class="bn-digit" style="text-align: right; font-weight: bold; color: {{ $emailTitleColor }}; padding-top: 14px;">৳{{ number_format($sub, 2) }}</td>[cite: 19]
                        </tr>
                        <tr>
                            <td colspan="2" style="text-align: right; font-weight: bold; color: {{ $emailTextColor }};">Delivery Charge:</td>[cite: 19]
                            <td class="bn-digit" style="text-align: right; font-weight: bold; color: {{ $emailTitleColor }};">৳{{ number_format($ship, 2) }}</td>[cite: 19]
                        </tr>
                        <tr>
                            <td colspan="2" style="text-align: right; font-weight: bold; font-size: 14px; color: {{ $emailTitleColor }}; padding-top: 10px; border-top: 1px solid {{ $emailCardBorder }};">Grand Total:</td>[cite: 19]
                            <td class="theme-product-price bn-digit" style="text-align: right; font-weight: bold; font-size: 15px; color: {{ $emailPriceColor }}; padding-top: 10px; border-top: 1px solid {{ $emailCardBorder }};">৳{{ number_format($grand, 2) }}</td>[cite: 19]
                        </tr>
                    </tfoot>
                </table>

                <!-- ==================== DIGITAL PRODUCTS SECTION ==================== -->
                @if(!empty($digitalDetails['has_digital']))[cite: 19]
                    @if(!empty($digitalDetails['is_paid']))[cite: 19]
                        <!-- ১. পেইড ডাউনলোড লিংকসমূহ -->
                        @if(!empty($digitalDetails['downloads']) && count($digitalDetails['downloads']) > 0)[cite: 19]
                            <div class="digital-section">
                                <h4 style="margin: 0 0 12px 0; color: {{ $emailTitleColor }}; font-size: 14px;">📥 Downloadable Files:</h4>[cite: 19]
                                @foreach($digitalDetails['downloads'] as $dFile)
                                    <div style="margin-bottom: 12px; padding-bottom: 10px; border-bottom: 1px dashed {{ $emailCardBorder }};">
                                        <strong style="color: {{ $emailTitleColor }};">{{ $dFile->product_title ?? 'Product' }}</strong>: {{ $dFile->file_name }}<br>[cite: 19]
                                        <div style="margin-top: 8px;">
                                            @if($dFile->is_accessible)[cite: 19]
                                                <a href="{{ $dFile->download_url }}" class="digital-btn" target="_blank" style="background-color: {{ $emailPrimaryColor }};">Download File</a>[cite: 19]
                                            @else
                                                <span style="color: #dc2626; font-size: 11.5px; font-weight: bold;">(Download limit reached or expired)</span>[cite: 19]
                                            @endif
                                        </div>
                                        @if(!empty($dFile->download_limit) || !empty($dFile->expires_at))[cite: 19]
                                            <div style="font-size: 11px; color: {{ $emailTextColor }}; margin-top: 6px;">
                                                @if(!empty($dFile->download_limit)) Limit: <span class="bn-digit">{{ $dFile->download_count }}</span>/<span class="bn-digit">{{ $dFile->download_limit }}</span> | @endif[cite: 19]
                                                @if(!empty($dFile->expires_at)) Expires on: <span class="bn-digit">{{ date('d M, Y', strtotime($dFile->expires_at)) }}</span> @endif[cite: 19]
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- ২. পেইড লাইসেন্স কি ও নির্দেশনাবলী -->
                        @if(!empty($digitalDetails['licenses']) && count($digitalDetails['licenses']) > 0)[cite: 19]
                            <div class="digital-section" style="margin-top: 15px;">
                                <h4 style="margin: 0 0 12px 0; color: {{ $emailTitleColor }}; font-size: 14px;">🔑 Software Licenses &amp; Access Keys:</h4>[cite: 19]
                                @foreach($digitalDetails['licenses'] as $lic)
                                    <div style="margin-bottom: 12px;">
                                        <strong style="color: {{ $emailTitleColor }};">{{ $lic->product_title ?? 'Product' }}</strong>[cite: 19]
                                        <div class="license-box bn-digit" style="border-color: {{ $emailPrimaryColor }};">{{ $lic->license_key }}</div>[cite: 19]
                                        @if(!empty($lic->access_instructions))[cite: 19]
                                            <div style="font-size: 12px; color: {{ $emailTextColor }}; margin-top: 6px; background: #ffffff; padding: 8px 10px; border-radius: 6px; border: 1px solid {{ $emailCardBorder }};">
                                                <strong>Instructions:</strong> {{ $lic->access_instructions }}[cite: 19]
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <!-- পেমেন্ট বাকি থাকলে নোটিশ -->
                        <div class="alert-pending">
                            <strong>Digital Delivery Notice:</strong> Your order contains digital items. Download links and license keys will be available immediately once your payment is confirmed.[cite: 19]
                        </div>
                    @endif
                @endif

                <!-- Shipping Details (Physical delivery) -->
                @if(!empty($order->customer_address) && $order->customer_address !== 'Digital Delivery')[cite: 19]
                    <div class="shipping-details-box">
                        <strong style="display: block; margin-bottom: 4px; color: {{ $emailTitleColor }};">Shipping Details:</strong>[cite: 19]
                        <div>{{ $order->customer_address ?? ($order->address ?? 'N/A') }}, {{ $order->district ?? 'N/A' }}</div>[cite: 19]
                        <div>Phone: <span class="bn-digit">{{ $order->customer_phone ?? ($order->phone ?? 'N/A') }}</span></div>[cite: 19]
                    </div>
                @endif
            </div>

            <!-- Email Footer -->
            <div class="email-footer">
                If you have any questions, contact us at <a href="mailto:{{ $emailSupport }}">{{ $emailSupport }}</a>[cite: 19]
            </div>

        </div>
    </div>
</body>
</html>