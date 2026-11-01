@php
    // সেন্ট্রাল গ্লোবাল সেটিংস ক্যাশ রেজলভার (বারবার DB কুয়েরি হওয়া আটকাবে)
    static $adminMailSettingsCache = null;
    if ($adminMailSettingsCache === null) {
        $adminMailSettingsCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
            return \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $mailSettings = $adminMailSettingsCache ?? [];

    // ডায়নামিক থিম কালার ভ্যারিয়েবল (Existing Default Colors)
    $emailBrandName    = $mailSettings['footer_brand_title'] ?? config('app.name', 'Shop Now BD');
    $emailHeaderBg     = $mailSettings['color_hdr_bg'] ?? ($mailSettings['color_header_bg'] ?? '#0f172a');
    $emailPrimaryColor = $mailSettings['color_cpc_price'] ?? ($mailSettings['color_product_price'] ?? ($mailSettings['color_common_btn_bg'] ?? '#2563eb'));
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
    <title>New Order Placed - {{ $emailBrandName }}</title>
    <style>
        /* Email Client CSS Reset */
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
            background-color: {{ $emailHeaderBg }} !important; 
            color: #ffffff; 
            padding: 24px 20px; 
            text-align: center; 
        }

        .email-content { 
            padding: 24px 20px; 
        }

        .details-box {
            background-color: {{ $emailSiteBg }}; 
            padding: 14px 16px; 
            border-radius: 10px; 
            border: 1px solid {{ $emailCardBorder }};
            font-size: 13px;
            line-height: 1.65;
            color: {{ $emailTitleColor }};
        }

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
            color: #dc2626; 
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; 
            margin-top: 4px; 
            font-weight: bold; 
        }

        .theme-product-price { 
            color: {{ $emailPrimaryColor }} !important; 
        }

        .email-footer { 
            background-color: {{ $emailSiteBg }}; 
            padding: 16px; 
            text-align: center; 
            font-size: 12px; 
            color: {{ $emailTextColor }}; 
            border-top: 1px solid {{ $emailCardBorder }};
            line-height: 1.5;
        }

        .bn-digit {
            font-weight: bold !important;
        }

        /* Mobile Screens (320px–575px) */
        @media only screen and (max-width: 600px) {
            body { padding: 6px !important; }
            .email-container { width: 100% !important; border-radius: 8px !important; }
            .email-header { padding: 20px 14px !important; }
            .email-header h2 { font-size: 18px !important; }
            .email-content { padding: 16px 12px !important; }
            .details-box { padding: 12px !important; font-size: 12.5px !important; }
            .order-table th, .order-table td { padding: 10px 6px !important; font-size: 12px !important; }
            .order-table th.col-qty, .order-table td.col-qty { text-align: center !important; }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            
            <!-- Email Header -->
            <div class="email-header" style="background-color: {{ $emailHeaderBg }};">
                <h2 style="margin: 0; font-size: 20px; font-weight: bold;">New Order Placed!</h2>
                <p style="margin: 6px 0 0 0; font-size: 13.5px; opacity: 0.9;" class="bn-digit">
                    Invoice #{{ $order->invoice_no ?? $order->id }}[cite: 21]
                </p>
            </div>

            <!-- Email Content -->
            <div class="email-content">
                <p style="margin-top: 0; font-size: 14px; color: {{ $emailTitleColor }};">Hello Admin,</p>[cite: 21]
                <p style="font-size: 13.5px; color: {{ $emailTextColor }}; margin-bottom: 20px;">
                    A new order has been successfully placed on your e-commerce store.[cite: 21]
                </p>
                
                <h4 style="margin: 18px 0 8px 0; font-size: 14px; font-weight: bold; color: {{ $emailTitleColor }};">
                    Customer &amp; Shipping Details:[cite: 21]
                </h4>
                <div class="details-box">
                    <strong>Name:</strong> {{ $order->customer_name ?? ($order->name ?? 'N/A') }}<br>[cite: 21]
                    <strong>Phone:</strong> <span class="bn-digit">{{ $order->customer_phone ?? ($order->phone ?? 'N/A') }}</span><br>[cite: 21]
                    <strong>Email:</strong> {{ $order->customer_email ?? ($order->email ?? 'N/A') }}<br>[cite: 21]
                    <strong>Address:</strong> {{ $order->customer_address ?? ($order->shipping_address ?? ($order->address ?? 'N/A')) }}, {{ $order->district ?? 'N/A' }}<br>[cite: 21]
                    <strong>Payment Method:</strong> <span style="text-transform: uppercase; font-weight: bold; color: {{ $emailPrimaryColor }};">{{ $order->payment_method ?? 'COD' }}</span>[cite: 21]
                </div>

                <h4 style="margin: 24px 0 8px 0; font-size: 14px; font-weight: bold; color: {{ $emailTitleColor }};">
                    Order Summary:[cite: 21]
                </h4>
                <table class="order-table" cellpadding="0" cellspacing="0">
                    <thead>
                        <tr>
                            <th style="width: 60%;">Item Details</th>[cite: 21]
                            <th class="col-qty" style="width: 15%; text-align: center;">Qty</th>[cite: 21]
                            <th style="width: 25%; text-align: right;">Total</th>[cite: 21]
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
                                    <strong style="color: {{ $emailTitleColor }}; font-size: 13px;">{{ $title }}</strong>[cite: 21]
                                    @if(!empty($color) || !empty($size) || !empty($vName))
                                        <div style="margin-top: 4px;">
                                            @if(!empty($color))
                                                <span class="variant-badge">Color: {{ $color }}</span>[cite: 21]
                                            @endif
                                            @if(!empty($size))
                                                <span class="variant-badge">Size: {{ $size }}</span>[cite: 21]
                                            @endif
                                            @if(!empty($vName) && empty($color) && empty($size))
                                                <span class="variant-badge">{{ $vName }}</span>[cite: 21]
                                            @endif
                                        </div>
                                    @endif
                                    @if(!empty($sku))
                                        <span class="sku-text bn-digit">SKU: {{ $sku }}</span>[cite: 21]
                                    @endif
                                </td>
                                <td class="col-qty bn-digit" style="text-align: center; color: {{ $emailTitleColor }};">{{ $qty }}</td>[cite: 21]
                                <td class="bn-digit" style="text-align: right; font-weight: bold; color: {{ $emailTitleColor }};">৳{{ number_format($price * $qty, 2) }}</td>[cite: 21]
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
                            <td colspan="2" style="text-align: right; font-weight: bold; color: {{ $emailTextColor }}; padding-top: 14px;">Subtotal:</td>[cite: 21]
                            <td class="bn-digit" style="text-align: right; font-weight: bold; color: {{ $emailTitleColor }}; padding-top: 14px;">৳{{ number_format($sub, 2) }}</td>[cite: 21]
                        </tr>
                        <tr>
                            <td colspan="2" style="text-align: right; font-weight: bold; color: {{ $emailTextColor }};">Delivery Charge:</td>[cite: 21]
                            <td class="bn-digit" style="text-align: right; font-weight: bold; color: {{ $emailTitleColor }};">৳{{ number_format($ship, 2) }}</td>[cite: 21]
                        </tr>
                        <tr>
                            <td colspan="2" style="text-align: right; font-weight: bold; font-size: 14px; color: {{ $emailTitleColor }}; padding-top: 10px; border-top: 1px solid {{ $emailCardBorder }};">Grand Total:</td>[cite: 21]
                            <td class="theme-product-price bn-digit" style="text-align: right; font-weight: bold; font-size: 15px; color: {{ $emailPrimaryColor }}; padding-top: 10px; border-top: 1px solid {{ $emailCardBorder }};">৳{{ number_format($grand, 2) }}</td>[cite: 21]
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Email Footer -->
            <div class="email-footer">
                {{ $emailBrandName }} Automated Admin Notification System[cite: 21]
            </div>

        </div>
    </div>
</body>
</html>