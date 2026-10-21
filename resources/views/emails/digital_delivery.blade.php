<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Product Access</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="table-layout: fixed;">
        <tr>
            <td align="center" style="padding: 24px 12px;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 620px; background-color: #ffffff; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;">
                    
                    <!-- Header -->
                    <tr>
                        <td align="center" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 28px 20px; color: #ffffff;">
                            <h1 style="margin: 0 0 6px 0; font-size: 20px; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase;">
                                {{ config('app.name', 'SHOP NOW BD ONLINE') }}
                            </h1>
                            <p style="margin: 0; font-size: 13px; color: #38bdf8; font-weight: 600;">
                                ⚡ Digital Product Access & License Confirmation
                            </p>
                        </td>
                    </tr>

                    <!-- Order Greeting -->
                    <tr>
                        <td style="padding: 24px 24px 16px 24px;">
                            <p style="margin: 0 0 10px 0; font-size: 14px;">
                                Dear <strong>{{ $order->shipping_name ?? ($order->customer_name ?? ($order->name ?? 'Valued Customer')) }}</strong>,
                            </p>
                            <p style="margin: 0 0 14px 0; font-size: 13px; line-height: 1.5; color: #475569;">
                                Your payment for invoice <strong>#{{ $order->invoice_no ?? $order->id }}</strong> has been verified successfully. Your instant digital download links and license keys are ready below:
                            </p>
                            
                            <table width="100%" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 14px; margin-bottom: 20px;">
                                <tr>
                                    <td style="font-size: 12px; color: #64748b;">Invoice No: <strong style="color: #0f172a;">#{{ $order->invoice_no ?? $order->id }}</strong></td>
                                    <td align="right" style="font-size: 12px; color: #64748b;">Payment Status: <span style="background-color: #dcfce7; color: #15803d; font-weight: bold; padding: 2px 8px; border-radius: 12px; font-size: 10px; text-transform: uppercase;">PAID</span></td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- 1. Downloadable Files Section (Grouped by Product) -->
                    @if(!empty($digitalDetails['downloads']) && count($digitalDetails['downloads']) > 0)
                        @php
                            $groupedDownloads = collect($digitalDetails['downloads'])->groupBy('product_id');
                        @endphp
                        <tr>
                            <td style="padding: 0 24px 20px 24px;">
                                <h3 style="margin: 0 0 12px 0; font-size: 14px; color: #0284c7; text-transform: uppercase; letter-spacing: 0.3px; border-bottom: 2px solid #e0f2fe; padding-bottom: 6px;">
                                    📁 Downloadable Products / Files
                                </h3>

                                @foreach($groupedDownloads as $pId => $dGroup)
                                    @php $firstFile = (object)$dGroup->first(); @endphp
                                    <table width="100%" style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; margin-bottom: 14px; padding: 14px; border-collapse: separate;">
                                        <tr>
                                            <td>
                                                <div style="font-size: 14px; font-weight: bold; color: #0f172a; margin-bottom: 8px;">
                                                    {{ $firstFile->product_title ?? 'Digital Product' }}
                                                </div>

                                                @foreach($dGroup as $idx => $dFile)
                                                    @php $fileObj = (object)$dFile; @endphp
                                                    <table width="100%" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; margin-bottom: 8px;">
                                                        <tr>
                                                            <td>
                                                                <div style="font-size: 12.5px; font-weight: bold; color: #334155;">
                                                                    📄 {{ $fileObj->file_name ?? 'Download File' }}
                                                                </div>
                                                                <div style="font-size: 11px; color: #64748b; margin-top: 3px;">
                                                                    • Limit: <strong>{{ !is_null($fileObj->download_limit ?? null) ? ($fileObj->download_limit - $fileObj->download_count) . ' downloads left' : 'Unlimited' }}</strong>
                                                                    &nbsp;|&nbsp;
                                                                    • Expiry: <strong>{{ !empty($fileObj->expires_at) ? date('d M, Y', strtotime($fileObj->expires_at)) : 'Lifetime Access' }}</strong>
                                                                </div>
                                                            </td>
                                                            <td align="right" valign="middle" style="width: 130px;">
                                                                <a href="{{ $fileObj->download_url }}" target="_blank" style="display: inline-block; background-color: #2563eb; color: #ffffff; text-decoration: none; padding: 7px 14px; border-radius: 6px; font-weight: bold; font-size: 11.5px; box-shadow: 0 2px 6px rgba(37,99,235,0.3);">
                                                                    Download
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                @endforeach
                                            </td>
                                        </tr>
                                    </table>
                                @endforeach
                            </td>
                        </tr>
                    @endif

                    <!-- 2. Software License Keys & Virtual Product Access Section -->
                    @if(!empty($digitalDetails['licenses']) && count($digitalDetails['licenses']) > 0)
                        @php
                            $groupedLicenses = collect($digitalDetails['licenses'])->groupBy('product_id');
                        @endphp
                        <tr>
                            <td style="padding: 0 24px 20px 24px;">
                                <h3 style="margin: 0 0 12px 0; font-size: 14px; color: #059669; text-transform: uppercase; letter-spacing: 0.3px; border-bottom: 2px solid #d1fae5; padding-bottom: 6px;">
                                    🔑 Software Licenses & Product Access
                                </h3>

                                @foreach($groupedLicenses as $prodId => $licGroup)
                                    @php
                                        $firstLic = (object)$licGroup->first();
                                        $totalKeys = $licGroup->count();
                                    @endphp
                                    <table width="100%" style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; margin-bottom: 14px; padding: 14px; border-collapse: separate;">
                                        <tr>
                                            <td>
                                                <div style="font-size: 14px; font-weight: bold; color: #0f172a; margin-bottom: 8px;">
                                                    {{ $firstLic->product_title ?? 'Virtual Product' }}
                                                    @if($totalKeys > 1)
                                                        <span style="background-color: #e0e7ff; color: #3730a3; font-size: 11px; padding: 2px 8px; border-radius: 10px; margin-left: 6px;">
                                                            Purchased Qty: {{ $totalKeys }} Keys
                                                        </span>
                                                    @endif
                                                </div>

                                                <!-- প্রতিটি Quantity-র জন্য আলাদা ইউনিক কী প্রদর্শন -->
                                                @foreach($licGroup as $index => $singleLic)
                                                    @php $singleObj = (object)$singleLic; @endphp
                                                    @if(!empty($singleObj->license_key))
                                                        <div style="margin-bottom: 8px;">
                                                            @if($totalKeys > 1)
                                                                <div style="font-size: 11px; font-weight: bold; color: #64748b; margin-bottom: 2px;">
                                                                    License Key #{{ $index + 1 }}:
                                                                </div>
                                                            @endif
                                                            <div style="background-color: #f8fafc; border: 1px dashed #059669; border-radius: 6px; padding: 8px 12px; font-family: 'Courier New', Courier, monospace; font-size: 14px; font-weight: bold; color: #059669; letter-spacing: 0.5px; word-break: break-all;">
                                                                @if(str_starts_with($singleObj->license_key, 'http://') || str_starts_with($singleObj->license_key, 'https://'))
                                                                    <a href="{{ $singleObj->license_key }}" target="_blank" style="color: #0284c7; text-decoration: underline;">
                                                                        {{ $singleObj->license_key }}
                                                                    </a>
                                                                @else
                                                                    {{ $singleObj->license_key }}
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach

                                                @if(!empty($firstLic->access_instructions))
                                                    <div style="font-size: 11.5px; color: #475569; background-color: #f1f5f9; padding: 8px 10px; border-radius: 4px; margin-top: 8px; line-height: 1.4;">
                                                        <strong>Instructions / Access Guide:</strong><br>
                                                        {!! nl2br(e($firstLic->access_instructions)) !!}
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                @endforeach
                            </td>
                        </tr>
                    @endif

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 16px 24px; text-align: center; font-size: 11px; color: #94a3b8;">
                            <p style="margin: 0 0 4px 0;">Need support with your digital purchase? Reply directly to this email.</p>
                            <p style="margin: 0;">&copy; {{ date('Y') }} {{ config('app.name', 'SHOP NOW BD ONLINE') }}. All rights reserved.</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>