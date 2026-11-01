@php
    // সেন্ট্রাল গ্লোবাল সেটিংস ক্যাশ রেজলভার (বারবার DB কুয়েরি হওয়া আটকাবে)
    static $digitalMailCache = null;
    if ($digitalMailCache === null) {
        $digitalMailCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
            return \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $mailSettings = $digitalMailCache ?? [];

    // ডায়নামিক থিম কালার ভ্যারিয়েবল (Existing Default Colors)
    $siteBrandName     = $mailSettings['footer_brand_title'] ?? config('app.name', 'SHOP NOW BD ONLINE');
    $emailPrimaryColor = $mailSettings['color_common_btn_bg'] ?? '#2563eb';
    $emailPrimaryHover = $mailSettings['color_auth_btn_hover'] ?? '#1d4ed8';
    $emailSiteBg       = $mailSettings['color_site_bg'] ?? '#f1f5f9';
    $emailCardBg       = $mailSettings['color_auth_card_bg'] ?? '#ffffff';
    $emailCardBorder   = $mailSettings['color_auth_card_border'] ?? '#e2e8f0';
    $emailHeaderStart  = $mailSettings['color_hdr_bg'] ?? '#0f172a';
    $emailHeaderEnd    = '#1e293b';
    $emailTitleColor   = $mailSettings['color_page_title'] ?? '#0f172a';
    $emailTextColor    = $mailSettings['color_auth_text'] ?? '#475569';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Digital Product Access - {{ $siteBrandName }}</title>
    <style>
        /* Email Client CSS Reset */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }

        body {
            margin: 0 !important;
            padding: 0 !important;
            background-color: {{ $emailSiteBg }} !important;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
            width: 100% !important;
            height: 100% !important;
        }

        .theme-btn-primary { 
            background-color: {{ $emailPrimaryColor }} !important; 
            color: #ffffff !important;
            text-decoration: none !important;
        }
        
        .theme-text-primary { 
            color: {{ $emailPrimaryColor }} !important; 
        }

        .bn-digit {
            font-weight: bold !important;
        }

        /* Responsive Mobile Styles (Gmail, Apple Mail, Outlook Apps) */
        @media only screen and (max-width: 600px) {
            .mobile-outer-padding {
                padding: 12px 6px !important;
            }

            .mobile-card-container {
                width: 100% !important;
                border-radius: 10px !important;
            }

            .mobile-header-padding {
                padding: 22px 14px !important;
            }

            .mobile-header-title {
                font-size: 17px !important;
            }

            .mobile-body-padding {
                padding: 16px 14px 12px 14px !important;
            }

            .mobile-section-padding {
                padding: 0 14px 16px 14px !important;
            }

            .file-stack-table,
            .file-stack-table tbody,
            .file-stack-table tr {
                display: block !important;
                width: 100% !important;
            }

            .file-info-cell {
                display: block !important;
                width: 100% !important;
                margin-bottom: 10px !important;
            }

            .file-btn-cell {
                display: block !important;
                width: 100% !important;
                text-align: left !important;
            }

            .mobile-download-btn {
                display: block !important;
                width: 100% !important;
                text-align: center !important;
                box-sizing: border-box !important;
                padding: 9px 12px !important;
            }

            .license-key-box {
                font-size: 12.5px !important;
                padding: 8px 10px !important;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: {{ $emailSiteBg }}; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="table-layout: fixed; background-color: {{ $emailSiteBg }};">
        <tr>
            <td align="center" class="mobile-outer-padding" style="padding: 24px 12px;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="mobile-card-container" style="max-width: 620px; background-color: {{ $emailCardBg }}; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05); border: 1px solid {{ $emailCardBorder }};">
                    
                    <!-- Header Banner -->
                    <tr>
                        <td align="center" class="mobile-header-padding" style="background: linear-gradient(135deg, {{ $emailHeaderStart }} 0%, {{ $emailHeaderEnd }} 100%); padding: 28px 20px; color: #ffffff;">
                            <h1 class="mobile-header-title" style="margin: 0 0 6px 0; font-size: 20px; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase;">
                                {{ $siteBrandName }}
                            </h1>
                            <p style="margin: 0; font-size: 13px; color: #38bdf8; font-weight: 600;">
                                ⚡ Digital Product Access &amp; License Confirmation
                            </p>
                        </td>
                    </tr>

                    <!-- Order Greeting & Invoice Bar -->
                    <tr>
                        <td class="mobile-body-padding" style="padding: 24px 24px 16px 24px;">
                            <p style="margin: 0 0 10px 0; font-size: 14px; color: {{ $emailTitleColor }};">
                                Dear <strong>{{ $order->shipping_name ?? ($order->customer_name ?? ($order->name ?? 'Valued Customer')) }}</strong>,
                            </p>
                            <p style="margin: 0 0 14px 0; font-size: 13px; line-height: 1.5; color: {{ $emailTextColor }};">
                                Your payment for invoice <strong>#{{ $order->invoice_no ?? $order->id }}</strong> has been verified successfully. Your instant digital download links and license keys are ready below:
                            </p>
                            
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: {{ $emailSiteBg }}; border: 1px solid {{ $emailCardBorder }}; border-radius: 8px; padding: 10px 14px; margin-bottom: 20px;">
                                <tr>
                                    <td style="font-size: 12px; color: {{ $emailTextColor }};">
                                        Invoice No: <strong class="bn-digit" style="color: {{ $emailTitleColor }};">#{{ $order->invoice_no ?? $order->id }}</strong>
                                    </td>
                                    <td align="right" style="font-size: 12px; color: {{ $emailTextColor }};">
                                        Payment Status: <span style="background-color: #dcfce7; color: #15803d; font-weight: bold; padding: 3px 8px; border-radius: 12px; font-size: 10px; text-transform: uppercase; letter-spacing: 0.03em;">PAID</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- 1. Downloadable Files Section -->
                    @if(!empty($digitalDetails['downloads']) && count($digitalDetails['downloads']) > 0)
                        @php
                            $groupedDownloads = collect($digitalDetails['downloads'])->groupBy('product_id');
                        @endphp
                        <tr>
                            <td class="mobile-section-padding" style="padding: 0 24px 20px 24px;">
                                <h3 style="margin: 0 0 12px 0; font-size: 14px; color: #0284c7; text-transform: uppercase; letter-spacing: 0.3px; border-bottom: 2px solid #e0f2fe; padding-bottom: 6px;">
                                    📁 Downloadable Products / Files
                                </h3>

                                @foreach($groupedDownloads as $pId => $dGroup)
                                    @php $firstFile = (object)$dGroup->first(); @endphp
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; margin-bottom: 14px; padding: 14px; border-collapse: separate;">
                                        <tr>
                                            <td>
                                                <div style="font-size: 14px; font-weight: bold; color: {{ $emailTitleColor }}; margin-bottom: 10px;">
                                                    {{ $firstFile->product_title ?? 'Digital Product' }}
                                                </div>

                                                @foreach($dGroup as $idx => $dFile)
                                                    @php $fileObj = (object)$dFile; @endphp
                                                    <table width="100%" cellpadding="0" cellspacing="0" border="0" class="file-stack-table" style="background-color: {{ $emailSiteBg }}; border: 1px solid {{ $emailCardBorder }}; border-radius: 6px; padding: 10px 12px; margin-bottom: 8px;">
                                                        <tr>
                                                            <td class="file-info-cell">
                                                                <div style="font-size: 12.5px; font-weight: bold; color: #334155;">
                                                                    📄 {{ $fileObj->file_name ?? 'Download File' }}
                                                                </div>
                                                                <div class="bn-digit" style="font-size: 11px; color: {{ $emailTextColor }}; margin-top: 4px; line-height: 1.4;">
                                                                    • Limit: <strong>{{ !is_null($fileObj->download_limit ?? null) ? ($fileObj->download_limit - $fileObj->download_count) . ' downloads left' : 'Unlimited' }}</strong>
                                                                    &nbsp;|&nbsp;
                                                                    • Expiry: <strong>{{ !empty($fileObj->expires_at) ? date('d M, Y', strtotime($fileObj->expires_at)) : 'Lifetime Access' }}</strong>
                                                                </div>
                                                            </td>
                                                            <td align="right" valign="middle" class="file-btn-cell" style="width: 120px;">
                                                                <a href="{{ $fileObj->download_url }}" target="_blank" class="theme-btn-primary mobile-download-btn" style="display: inline-block; background-color: {{ $emailPrimaryColor }}; color: #ffffff !important; text-decoration: none !important; padding: 7px 16px; border-radius: 50px; font-weight: bold; font-size: 11.5px; box-shadow: 0 2px 6px rgba(37,99,235,0.25);">
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

                    <!-- 2. Software License Keys Section -->
                    @if(!empty($digitalDetails['licenses']) && count($digitalDetails['licenses']) > 0)
                        @php
                            $groupedLicenses = collect($digitalDetails['licenses'])->groupBy('product_id');
                        @endphp
                        <tr>
                            <td class="mobile-section-padding" style="padding: 0 24px 20px 24px;">
                                <h3 style="margin: 0 0 12px 0; font-size: 14px; color: #059669; text-transform: uppercase; letter-spacing: 0.3px; border-bottom: 2px solid #d1fae5; padding-bottom: 6px;">
                                    🔑 Software Licenses &amp; Product Access
                                </h3>

                                @foreach($groupedLicenses as $prodId => $licGroup)
                                    @php
                                        $firstLic = (object)$licGroup->first();
                                        $totalKeys = $licGroup->count();
                                    @endphp
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; margin-bottom: 14px; padding: 14px; border-collapse: separate;">
                                        <tr>
                                            <td>
                                                <div style="font-size: 14px; font-weight: bold; color: {{ $emailTitleColor }}; margin-bottom: 10px;">
                                                    {{ $firstLic->product_title ?? 'Virtual Product' }}
                                                    @if($totalKeys > 1)
                                                        <span class="bn-digit" style="background-color: #e0e7ff; color: #3730a3; font-size: 11px; padding: 2px 8px; border-radius: 10px; margin-left: 6px; display: inline-block;">
                                                            Purchased Qty: {{ $totalKeys }} Keys
                                                        </span>
                                                    @endif
                                                </div>

                                                @foreach($licGroup as $index => $singleLic)
                                                    @php $singleObj = (object)$singleLic; @endphp
                                                    @if(!empty($singleObj->license_key))
                                                        <div style="margin-bottom: 10px;">
                                                            @if($totalKeys > 1)
                                                                <div class="bn-digit" style="font-size: 11px; font-weight: bold; color: {{ $emailTextColor }}; margin-bottom: 3px;">
                                                                    License Key #{{ $index + 1 }}:
                                                                </div>
                                                            @endif
                                                            <div class="license-key-box" style="background-color: {{ $emailSiteBg }}; border: 1px dashed #059669; border-radius: 6px; padding: 8px 12px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 13.5px; font-weight: bold; color: #059669; letter-spacing: 0.5px; word-break: break-all;">
                                                                @if(str_starts_with($singleObj->license_key, 'http://') || str_starts_with($singleObj->license_key, 'https://'))
                                                                    <a href="{{ $singleObj->license_key }}" target="_blank" class="theme-text-primary" style="color: {{ $emailPrimaryColor }} !important; text-decoration: underline !important;">
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
                                                    <div style="font-size: 12px; color: {{ $emailTextColor }}; background-color: {{ $emailSiteBg }}; border: 1px solid {{ $emailCardBorder }}; padding: 8px 10px; border-radius: 6px; margin-top: 8px; line-height: 1.45;">
                                                        <strong style="color: {{ $emailTitleColor }};">Instructions / Access Guide:</strong><br>
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
                        <td style="background-color: {{ $emailSiteBg }}; border-top: 1px solid {{ $emailCardBorder }}; padding: 18px 20px; text-align: center; font-size: 11.5px; color: #94a3b8; line-height: 1.5;">
                            <p style="margin: 0 0 4px 0;">Need support with your digital purchase? Reply directly to this email.</p>
                            <p style="margin: 0;">&copy; {{ date('Y') }} {{ $siteBrandName }}. All rights reserved.</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>