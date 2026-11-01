@php
    // সেন্ট্রাল গ্লোবাল সেটিংস ক্যাশ রেজলভার (বারবার DB কুয়েরি হওয়া আটকাবে)
    static $formReplyMailCache = null;
    if ($formReplyMailCache === null) {
        $formReplyMailCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
            return \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $settings = $formReplyMailCache ?? [];

    // ডায়নামিক থিম কালার ভ্যারিয়েবল (Existing Default Colors)
    $siteTitle    = $settings['site_title'] ?? ($settings['footer_brand_title'] ?? config('app.name', 'Shop Now BD'));
    $headerBg     = $settings['color_hdr_bg'] ?? ($settings['color_header_bg'] ?? '#0f172a');
    $primaryColor = $settings['color_common_btn_bg'] ?? ($settings['color_primary'] ?? '#2563eb');
    $siteBg       = $settings['color_site_bg'] ?? '#f1f5f9';
    $cardBg       = $settings['color_auth_card_bg'] ?? '#ffffff';
    $cardBorder   = $settings['color_auth_card_border'] ?? '#e2e8f0';
    $titleColor   = $settings['color_page_title'] ?? '#0f172a';
    $textColor    = $settings['color_auth_text'] ?? '#475569';

    // Logo Resolution (১০০% অক্ষত)
    $logoRaw = $settings['site_logo'] ?? null;
    $siteLogoUrl = null;
    if (!empty($logoRaw)) {
        if (filter_var($logoRaw, FILTER_VALIDATE_URL)) {
            $siteLogoUrl = $logoRaw;
        } else {
            $cleanLogo = ltrim(str_replace(['storage/', 'public/'], '', $logoRaw), '/');
            if (file_exists(public_path($cleanLogo))) {
                $siteLogoUrl = asset($cleanLogo);
            } elseif (file_exists(public_path('uploads/' . $cleanLogo))) {
                $siteLogoUrl = asset('uploads/' . $cleanLogo);
            } else {
                $siteLogoUrl = asset($cleanLogo);
            }
        }
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $replySubject }}</title>
    <style>
        /* Email Client CSS Reset */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }

        body { 
            margin: 0 !important; 
            padding: 0 !important; 
            background-color: {{ $siteBg }} !important; 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; 
            -webkit-font-smoothing: antialiased;
            color: #1e293b; 
            width: 100% !important;
            height: 100% !important;
        }

        a, a:hover {
            text-decoration: none !important;
        }

        .bn-digit {
            font-weight: bold !important;
        }

        /* Responsive Mobile Styles (Gmail, Apple Mail, Outlook) */
        @media only screen and (max-width: 600px) {
            .mobile-outer-padding {
                padding: 14px 6px !important;
            }

            .mobile-card-container {
                width: 100% !important;
                border-radius: 8px !important;
            }

            .mobile-header-padding {
                padding: 22px 16px !important;
            }

            .mobile-header-title {
                font-size: 18px !important;
            }

            .mobile-body-padding {
                padding: 20px 16px !important;
            }

            .btn-cta-responsive {
                display: block !important;
                width: 100% !important;
                text-align: center !important;
                box-sizing: border-box !important;
                padding: 12px 18px !important;
            }

            .inquiry-ref-box {
                padding: 12px 14px !important;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: {{ $siteBg }}; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;">

    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="mobile-outer-padding" style="background-color: {{ $siteBg }}; padding: 35px 15px;">
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="mobile-card-container" style="max-width: 620px; background-color: {{ $cardBg }}; border-radius: 10px; border: 1px solid {{ $cardBorder }}; overflow: hidden; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);">
                    
                    <!-- Header -->
                    <tr>
                        <td class="mobile-header-padding" style="background-color: {{ $headerBg }}; padding: 25px 30px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td>
                                        @if($siteLogoUrl)
                                            <img src="{{ $siteLogoUrl }}" alt="{{ $siteTitle }}" style="max-height: 40px; margin-bottom: 12px; display: block;">
                                        @else
                                            <h2 class="mobile-header-title" style="margin: 0 0 6px; color: #ffffff; font-size: 20px; font-weight: 800;">
                                                {{ $siteTitle }}
                                            </h2>
                                        @endif
                                        <p style="margin: 0; color: #94a3b8; font-size: 12.5px;">
                                            Response regarding your inquiry on <strong>{{ $form->name ?? 'Contact Form' }}</strong>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td class="mobile-body-padding" style="padding: 30px 30px;">
                            <p style="margin: 0 0 16px; font-size: 14.5px; color: {{ $titleColor }}; font-weight: 600;">
                                Hello {{ $customerName }},
                            </p>

                            <div style="font-size: 14px; color: #334155; line-height: 1.7; margin-bottom: 25px;">
                                {!! nl2br($replyMessage) !!}
                            </div>

                            @if(!empty($originalDetails))
                                <!-- Previous Inquiry Reference Box -->
                                <div class="inquiry-ref-box" style="background-color: #f8fafc; border-left: 3px solid {{ $primaryColor }}; border-radius: 0 6px 6px 0; padding: 14px 16px; margin-bottom: 25px; border-top: 1px solid {{ $cardBorder }}; border-right: 1px solid {{ $cardBorder }}; border-bottom: 1px solid {{ $cardBorder }};">
                                    <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">
                                        Reference Inquiry (#<span class="bn-digit">{{ $submission->id }}</span>)
                                    </span>
                                    <div style="font-size: 12.5px; color: {{ $textColor }}; line-height: 1.5;">
                                        {!! $originalDetails !!}
                                    </div>
                                </div>
                            @endif

                            <!-- CTA Button -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center" style="padding-top: 10px;">
                                        <a href="{{ url('/') }}" target="_blank" class="btn-cta-responsive" style="display: inline-block; background-color: {{ $primaryColor }}; color: #ffffff !important; font-size: 13px; font-weight: 700; text-decoration: none !important; padding: 11px 28px; border-radius: 50px; box-shadow: 0 3px 10px rgba(37,99,235,0.25);">
                                            Visit {{ $siteTitle }} Website &rarr;
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 28px 0 0; font-size: 12.5px; color: #64748b; line-height: 1.5; border-top: 1px solid #f1f5f9; padding-top: 18px;">
                                Best Regards,<br>
                                <strong style="color: {{ $titleColor }};">{{ $adminName }}</strong><br>
                                <span style="color: #94a3b8; font-size: 11.5px;">Customer Support Desk • {{ $siteTitle }}</span>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; padding: 16px 30px; border-top: 1px solid {{ $cardBorder }}; text-align: center;">
                            <p style="margin: 0; font-size: 11.5px; color: #94a3b8; line-height: 1.5;">
                                You received this email because you submitted a request on {{ $siteTitle }}.
                                <br>
                                &copy; {{ date('Y') }} {{ $siteTitle }}. All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>