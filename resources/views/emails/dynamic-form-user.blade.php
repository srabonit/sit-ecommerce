@php
    // সেন্ট্রাল গ্লোবাল সেটিংস ক্যাশ রেজলভার (বারবার DB কুয়েরি হওয়া আটকাবে)
    static $userFormMailCache = null;
    if ($userFormMailCache === null) {
        $userFormMailCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
            return \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $settings = $userFormMailCache ?? [];

    // ডায়নামিক থিম কালার ভ্যারিয়েবল (Existing Default Colors)
    $siteTitle    = $settings['site_title'] ?? ($settings['footer_brand_title'] ?? config('app.name', 'Shop Now BD'));
    $headerBg     = $settings['color_hdr_bg'] ?? ($settings['color_header_bg'] ?? '#0f172a');
    $primaryColor = $settings['color_common_btn_bg'] ?? ($settings['color_primary'] ?? '#2563eb');
    $siteBg       = $settings['color_site_bg'] ?? '#f1f5f9';
    $cardBg       = $settings['color_auth_card_bg'] ?? '#ffffff';
    $cardBorder   = $settings['color_auth_card_border'] ?? '#e2e8f0';
    $titleColor   = $settings['color_page_title'] ?? '#0f172a';
    $textColor    = $settings['color_auth_text'] ?? '#475569';

    // Logo Resolution
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

    $rawDate = $submission?->submitted_at ?? $submission?->created_at ?? now();
    try {
        $formattedDate = \Carbon\Carbon::parse($rawDate)->format('d M Y, h:i A');
    } catch (\Throwable $e) {
        $formattedDate = (string) $rawDate;
    }

    $referenceId = $submission?->id ?? strtoupper(\Illuminate\Support\Str::random(8));

    // Check if user submission has an associated active coupon code
    $couponCode = null;
    if (class_exists(\App\Models\Coupon::class) && \Illuminate\Support\Facades\Schema::hasTable('coupons')) {
        $identifier = strtolower($form->slug . ' ' . $form->name);
        if (str_contains($identifier, 'coupon') || str_contains($identifier, 'discount') || str_contains($identifier, 'offer') || str_contains($identifier, 'voucher')) {
            $activeCoupon = \App\Models\Coupon::where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('start_date')->orWhere('start_date', '<=', now());
                })
                ->where(function ($q) {
                    $q->whereNull('expire_date')->orWhere('expire_date', '>=', now());
                })
                ->latest('id')
                ->first();

            if ($activeCoupon) {
                $couponCode = $activeCoupon->code;
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
    <title>Thank You - {{ $form->name }}</title>
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

        /* Responsive Mobile Styles (Gmail, Apple Mail, Outlook Apps) */
        @media only screen and (max-width: 600px) {
            .mobile-outer-padding {
                padding: 14px 6px !important;
            }

            .mobile-card-container {
                width: 100% !important;
                border-radius: 8px !important;
            }

            .mobile-header-padding {
                padding: 22px 14px !important;
            }

            .mobile-header-title {
                font-size: 17px !important;
            }

            .mobile-body-padding {
                padding: 18px 14px !important;
            }

            .payload-table, 
            .payload-table tbody, 
            .payload-table tr {
                display: block !important;
                width: 100% !important;
            }

            .payload-label-cell {
                display: block !important;
                width: 100% !important;
                box-sizing: border-box !important;
                padding: 9px 12px 3px 12px !important;
                border-bottom: none !important;
                background-color: #f8fafc !important;
                font-size: 11.5px !important;
            }

            .payload-val-cell {
                display: block !important;
                width: 100% !important;
                box-sizing: border-box !important;
                padding: 4px 12px 11px 12px !important;
                font-size: 12.5px !important;
            }

            .promo-coupon-box {
                padding: 14px 12px !important;
            }

            .promo-code-display {
                font-size: 18px !important;
                padding: 6px 12px !important;
            }

            .btn-cta-responsive {
                display: block !important;
                width: 100% !important;
                text-align: center !important;
                box-sizing: border-box !important;
                padding: 11px 16px !important;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: {{ $siteBg }}; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;">

    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="mobile-outer-padding" style="background-color: {{ $siteBg }}; padding: 35px 15px;">
        <tr>
            <td align="center">
                <!-- Main Container -->
                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="mobile-card-container" style="max-width: 600px; background-color: {{ $cardBg }}; border-radius: 10px; border: 1px solid {{ $cardBorder }}; overflow: hidden; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);">
                    
                    <!-- Header Banner -->
                    <tr>
                        <td class="mobile-header-padding" style="background-color: {{ $headerBg }}; padding: 30px 25px; text-align: center;">
                            @if($siteLogoUrl)
                                <img src="{{ $siteLogoUrl }}" alt="{{ $siteTitle }}" style="max-height: 48px; margin-bottom: 15px; display: inline-block;">
                            @else
                                <h1 style="margin: 0 0 10px; color: #ffffff; font-size: 22px; font-weight: 800; letter-spacing: -0.5px;">
                                    {{ $siteTitle }}
                                </h1>
                            @endif

                            <h2 class="mobile-header-title" style="margin: 0; color: #ffffff; font-size: 19px; font-weight: 700;">
                                Thank You for Reaching Out!
                            </h2>
                            <p style="margin: 6px 0 0; color: #cbd5e1; font-size: 13px;">
                                We have received your submission for <strong>{{ $form->name }}</strong>
                            </p>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td class="mobile-body-padding" style="padding: 28px 25px;">
                            
                            <!-- Success Message Card -->
                            <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 14px 18px; margin-bottom: 22px;">
                                <p style="margin: 0; font-size: 13.5px; color: #166534; line-height: 1.5;">
                                    ✓ {{ $form->success_message ?: 'Your request has been recorded. Our team will review your message and get in touch with you shortly.' }}
                                </p>
                            </div>

                            {{-- Coupon Voucher Card (Shown automatically if coupon form) --}}
                            @if($couponCode)
                                <div class="promo-coupon-box" style="background-color: #ffffff; border: 2px dashed #16a34a; border-radius: 8px; padding: 18px 20px; text-align: center; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(22, 163, 74, 0.08);">
                                    <span style="display: inline-block; background-color: #dcfce7; color: #15803d; font-size: 10.5px; font-weight: 700; text-transform: uppercase; padding: 3px 10px; border-radius: 20px; margin-bottom: 8px; letter-spacing: 0.5px;">
                                        🎁 Exclusive Discount Voucher
                                    </span>
                                    <h3 style="margin: 0 0 4px; font-size: 14px; color: {{ $titleColor }}; font-weight: 600;">Here is your promo code:</h3>
                                    <div class="promo-code-display bn-digit" style="font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 22px; font-weight: 800; color: {{ $titleColor }}; letter-spacing: 2px; background: #f1f5f9; padding: 8px 16px; border-radius: 6px; border: 1px solid {{ $cardBorder }}; display: inline-block; margin: 8px 0;">
                                        {{ $couponCode }}
                                    </div>
                                    <p style="margin: 4px 0 0; font-size: 11.5px; color: {{ $textColor }};">
                                        Use this discount code at checkout on your next purchase!
                                    </p>
                                </div>
                            @endif

                            <h4 style="margin: 0 0 12px; font-size: 13px; font-weight: 700; color: {{ $titleColor }}; text-transform: uppercase; letter-spacing: 0.5px;">
                                Submission Summary Receipt
                            </h4>

                            <!-- Summary Payload Table -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" class="payload-table" style="border-collapse: collapse; margin-bottom: 24px; border: 1px solid {{ $cardBorder }}; border-radius: 8px; overflow: hidden;">
                                <tbody>
                                    @if($submission && $submission->values && $submission->values->isNotEmpty())
                                        @foreach($submission->values as $item)
                                            @php
                                                $fieldType  = $item->field->type ?? 'text';
                                                $fieldLabel = $item->field->label ?? ($item->field->name ?? 'Field');
                                                $value      = $item->value;

                                                if ($fieldType === 'html' || $fieldType === 'hidden') {
                                                    continue;
                                                }

                                                if (is_string($value) && (str_starts_with($value, '[') || str_starts_with($value, '{'))) {
                                                    $decoded = json_decode($value, true);
                                                    if (is_array($decoded)) {
                                                        $value = implode(', ', $decoded);
                                                    }
                                                }

                                                $isFile = is_string($value) && (str_contains($value, 'uploads/forms/') || preg_match('/\.(jpg|jpeg|png|webp|pdf|docx?|zip)$/i', $value));
                                            @endphp
                                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                                <td class="payload-label-cell" style="padding: 10px 14px; font-size: 12px; font-weight: 700; color: #475569; width: 40%; background-color: #fafbfc; vertical-align: top;">
                                                    {{ $fieldLabel }}
                                                </td>
                                                <td class="payload-val-cell" style="padding: 10px 14px; font-size: 12.5px; color: {{ $titleColor }}; vertical-align: top;">
                                                    @if($isFile)
                                                        <span style="color: {{ $primaryColor }}; font-weight: 600;">📎 Attached File: {{ basename($value) }}</span>
                                                    @else
                                                        {!! !empty($value) ? nl2br(e($value)) : '<span style="color:#94a3b8; font-style:italic;">—</span>' !!}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @elseif(!empty($fieldData))
                                        @foreach($fieldData as $fLabel => $fVal)
                                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                                <td class="payload-label-cell" style="padding: 10px 14px; font-size: 12px; font-weight: 700; color: #475569; width: 40%; background-color: #fafbfc; vertical-align: top;">
                                                    {{ $fLabel }}
                                                </td>
                                                <td class="payload-val-cell" style="padding: 10px 14px; font-size: 12.5px; color: {{ $titleColor }}; vertical-align: top;">
                                                    {!! !empty($fVal) ? nl2br(e($fVal)) : '<span style="color:#94a3b8; font-style:italic;">—</span>' !!}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="2" style="padding: 14px; text-align: center; color: #94a3b8; font-size: 12px;">
                                                Submission recorded.
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>

                            <!-- Meta Info & Reference -->
                            <p style="margin: 0 0 20px; font-size: 11.5px; color: {{ $textColor }}; line-height: 1.6;">
                                Reference ID: <strong class="bn-digit" style="color: {{ $titleColor }};">#{{ $referenceId }}</strong> • Logged At: <span class="bn-digit">{{ $formattedDate }}</span>
                            </p>

                            <!-- CTA Button: Visit Store -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center" style="padding: 8px 0;">
                                        <a href="{{ url('/') }}" target="_blank" class="btn-cta-responsive" style="display: inline-block; background-color: {{ $primaryColor }}; color: #ffffff !important; font-size: 13px; font-weight: 700; text-decoration: none !important; padding: 11px 26px; border-radius: 50px; box-shadow: 0 3px 10px rgba(37,99,235,0.25);">
                                            Visit {{ $siteTitle }} Store &rarr;
                                        </a>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; padding: 18px 25px; border-top: 1px solid {{ $cardBorder }}; text-align: center;">
                            <p style="margin: 0; font-size: 11.5px; color: #94a3b8; line-height: 1.5;">
                                This is an automated confirmation receipt. Please do not reply directly to this email.
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