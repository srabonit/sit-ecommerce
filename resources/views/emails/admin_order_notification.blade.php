<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 20px; color: #334155; }
        .email-container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; }
        .header { background: #0f172a; color: #ffffff; padding: 24px; text-align: center; }
        .content { padding: 24px; }
        .order-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .order-table th, .order-table td { padding: 10px; border-bottom: 1px solid #e2e8f0; text-align: left; font-size: 13px; }
        .order-table th { background-color: #f1f5f9; color: #475569; }
        .footer { background: #f8fafc; padding: 16px; text-align: center; font-size: 12px; color: #94a3b8; }
        .variant-badge { display: inline-block; padding: 2px 6px; font-size: 11px; background: #e2e8f0; border-radius: 4px; margin-right: 4px; font-weight: bold; color: #1e293b; }
        .sku-text { display: block; font-size: 11px; color: #dc2626; font-family: monospace; margin-top: 3px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h2 style="margin: 0;">New Order Placed!</h2>
            <p style="margin: 5px 0 0 0; font-size: 14px;">Invoice #{{ $order->invoice_no ?? $order->id }}</p>
        </div>
        <div class="content">
            <p>Hello Admin,</p>
            <p>A new order has been successfully placed on your e-commerce store.</p>
            
            <h4 style="margin-top: 20px; margin-bottom: 5px;">Customer & Shipping Details:</h4>
            <p style="font-size: 13px; background: #f8fafc; padding: 12px; border-radius: 8px;">
                <strong>Name:</strong> {{ $order->customer_name ?? ($order->name ?? 'N/A') }}<br>
                <strong>Phone:</strong> {{ $order->customer_phone ?? ($order->phone ?? 'N/A') }}<br>
                <strong>Email:</strong> {{ $order->customer_email ?? ($order->email ?? 'N/A') }}<br>
                <strong>Address:</strong> {{ $order->customer_address ?? ($order->shipping_address ?? ($order->address ?? 'N/A')) }}, {{ $order->district ?? 'N/A' }}<br>
                <strong>Payment Method:</strong> <span style="text-transform: uppercase;">{{ $order->payment_method ?? 'COD' }}</span>
            </p>

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
        </div>
        <div class="footer">
            Shop Now BD Automated Admin Notification System
        </div>
    </div>
</body>
</html>