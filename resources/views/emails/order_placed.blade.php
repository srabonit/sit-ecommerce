<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 20px; color: #334155; }
        .email-container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; }
        .header { background: #2563eb; color: #ffffff; padding: 24px; text-align: center; }
        .content { padding: 24px; }
        .order-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .order-table th, .order-table td { padding: 10px; border-bottom: 1px solid #e2e8f0; text-align: left; font-size: 13px; }
        .order-table th { background-color: #f1f5f9; color: #475569; }
        .footer { background: #f8fafc; padding: 16px; text-align: center; font-size: 12px; color: #94a3b8; }
        .variant-badge { display: inline-block; padding: 2px 6px; font-size: 11px; background: #e2e8f0; border-radius: 4px; margin-right: 4px; font-weight: bold; color: #1e293b; }
        .sku-text { display: block; font-size: 11px; color: #2563eb; font-family: monospace; margin-top: 3px; font-weight: bold; }
        .digital-section { margin-top: 25px; border-radius: 8px; border: 1px solid #cbd5e1; padding: 16px; background-color: #f8fafc; }
        .digital-btn { display: inline-block; padding: 6px 14px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-size: 12px; font-weight: bold; }
        .license-box { background: #ffffff; border: 1px dashed #2563eb; border-radius: 6px; padding: 10px; margin-top: 8px; font-family: monospace; font-size: 13px; color: #1e293b; font-weight: bold; }
        .alert-pending { background-color: #fef3c7; border: 1px solid #fde68a; color: #92400e; padding: 12px; border-radius: 6px; font-size: 12.5px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h2 style="margin: 0;">Order Confirmed!</h2>
            <p style="margin: 5px 0 0 0; font-size: 14px;">Invoice #{{ $order->invoice_no ?? $order->id }}</p>
        </div>
        <div class="content">
            <p>Hello <strong>{{ $order->customer_name ?? ($order->name ?? 'Customer') }}</strong>,</p>
            <p>Thank you for shopping with us. Your order has been placed successfully.</p>
            
            <h4 style="margin-top: 20px; margin-bottom: 5px;">Order Summary:</h4>
            <table class="order-table">
                <thead>
                    <tr>
                        <th>Item Details</th>
                        <th>Qty</th>
                        <th style="text-align: right;">Total</th>
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
                                $title = $item->product_title ?? ($item->product_name ?? ($item->title ?? null));
                                $color = $item->color ?? null;
                                $size  = $item->size ?? null;
                                $sku   = $item->sku ?? null;
                                $vName = $item->variant_title ?? null;
                                $pId   = $item->product_id ?? null;
                            } elseif (is_array($item)) {
                                $qty = (int) ($item['quantity'] ?? ($item['qty'] ?? 1));
                                $price = (float) ($item['unit_price'] ?? ($item['price'] ?? 0));
                                $title = $item['product_title'] ?? ($item['product_name'] ?? ($item['title'] ?? null));
                                $color = $item['color'] ?? null;
                                $size  = $item['size'] ?? null;
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
                                <strong>{{ $title }}</strong>
                                @if(!empty($color) || !empty($size) || !empty($vName))
                                    <div style="margin-top: 4px;">
                                        @if(!empty($color))
                                            <span class="variant-badge">Color: {{ $color }}</span>
                                        @endif
                                        @if(!empty($size))
                                            <span class="variant-badge">Size: {{ $size }}</span>
                                        @endif
                                        @if(!empty($vName) && empty($color) && empty($size))
                                            <span class="variant-badge">{{ $vName }}</span>
                                        @endif
                                    </div>
                                @endif
                                @if(!empty($sku))
                                    <span class="sku-text">SKU: {{ $sku }}</span>
                                @endif
                            </td>
                            <td>{{ $qty }}</td>
                            <td style="text-align: right; font-weight: bold;">৳{{ number_format($price * $qty, 2) }}</td>
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
                        <td colspan="2" style="text-align: right; font-weight: bold;">Subtotal:</td>
                        <td style="text-align: right; font-weight: bold;">৳{{ number_format($sub, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align: right; font-weight: bold;">Delivery Charge:</td>
                        <td style="text-align: right; font-weight: bold;">৳{{ number_format($ship, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align: right; font-weight: bold;">Grand Total:</td>
                        <td style="text-align: right; font-weight: bold; color: #2563eb;">৳{{ number_format($grand, 2) }}</td>
                    </tr>
                </tfoot>
            </table>

            <!-- ==================== DIGITAL PRODUCTS SECTION ==================== -->
            @if(!empty($digitalDetails['has_digital']))
                @if(!empty($digitalDetails['is_paid']))
                    <!-- ১. পেইড ডাউনলোড লিংকসমূহ -->
                    @if(!empty($digitalDetails['downloads']) && count($digitalDetails['downloads']) > 0)
                        <div class="digital-section">
                            <h4 style="margin: 0 0 10px 0; color: #1e293b;">📥 Downloadable Files:</h4>
                            @foreach($digitalDetails['downloads'] as $dFile)
                                <div style="margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px dashed #e2e8f0;">
                                    <strong>{{ $dFile->product_title ?? 'Product' }}</strong>: {{ $dFile->file_name }}<br>
                                    <div style="margin-top: 6px;">
                                        @if($dFile->is_accessible)
                                            <a href="{{ $dFile->download_url }}" class="digital-btn" target="_blank">Download File</a>
                                        @else
                                            <span style="color: #dc2626; font-size: 11.5px; font-weight: bold;">(Download limit reached or expired)</span>
                                        @endif
                                    </div>
                                    @if(!empty($dFile->download_limit) || !empty($dFile->expires_at))
                                        <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                                            @if(!empty($dFile->download_limit)) Limit: {{ $dFile->download_count }}/{{ $dFile->download_limit }} | @endif
                                            @if(!empty($dFile->expires_at)) Expires on: {{ date('d M, Y', strtotime($dFile->expires_at)) }} @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- ২. পেইড লাইসেন্স কি ও নির্দেশনাবলী -->
                    @if(!empty($digitalDetails['licenses']) && count($digitalDetails['licenses']) > 0)
                        <div class="digital-section" style="margin-top: 15px;">
                            <h4 style="margin: 0 0 10px 0; color: #1e293b;">🔑 Software Licenses & Access Keys:</h4>
                            @foreach($digitalDetails['licenses'] as $lic)
                                <div style="margin-bottom: 12px;">
                                    <strong>{{ $lic->product_title ?? 'Product' }}</strong>
                                    <div class="license-box">{{ $lic->license_key }}</div>
                                    @if(!empty($lic->access_instructions))
                                        <div style="font-size: 12px; color: #475569; margin-top: 4px; background: #ffffff; padding: 6px; border-radius: 4px; border: 1px solid #e2e8f0;">
                                            <strong>Instructions:</strong> {{ $lic->access_instructions }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                @else
                    <!-- পেমেন্ট বাকি থাকলে নোটিশ -->
                    <div class="alert-pending">
                        <strong>Digital Delivery Notice:</strong> Your order contains digital items. Download links and license keys will be available immediately once your payment is confirmed.
                    </div>
                @endif
            @endif

            @if(!empty($order->customer_address) && $order->customer_address !== 'Digital Delivery')
                <p style="margin-top: 20px; font-size: 13px;">
                    <strong>Shipping Details:</strong><br>
                    {{ $order->customer_address ?? ($order->address ?? 'N/A') }}, {{ $order->district ?? 'N/A' }}<br>
                    Phone: {{ $order->customer_phone ?? ($order->phone ?? 'N/A') }}
                </p>
            @endif
        </div>
        <div class="footer">
            If you have any questions, contact us at info.srabonit@gmail.com
        </div>
    </div>
</body>
</html>