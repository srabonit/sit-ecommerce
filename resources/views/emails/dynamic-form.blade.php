@php
    // সেন্ট্রাল গ্লোবাল সেটিংস ক্যাশ রেজলভার (বারবার DB কুয়েরি হওয়া আটকাবে)
    static $formMailSettingsCache = null;
    if ($formMailSettingsCache === null) {
        $formMailSettingsCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
            return \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $settings = $formMailSettingsCache ?? [];

    // ডায়নামিক থিম কালার ভ্যারিয়েবল (Existing Default Colors)
    $siteTitle    = $settings['site_title'] ?? ($settings['footer_brand_title'] ?? config('app.name', 'Shop Now BD'));
    $headerBg     = $settings['color_hdr_bg'] ?? ($settings['color_header_bg'] ?? '#0f172a');
    $primaryColor = $settings['color_common_btn_bg'] ?? ($settings['color_primary'] ?? '#2563eb');
    $siteBg       = $settings['color_site_bg'] ?? '#f1f5f9';
    $cardBg       = $settings['color_auth_card_bg'] ?? '#ffffff';
    $cardBorder   = $settings['color_auth_card_border'] ?? '#e2e8f0';
    $titleColor   = $settings['color_page_title'] ?? '#0f172a';
    $textColor    = $settings['color_auth_text'] ?? '#475569';

    // সাবমিশন টাইমস্ট্যাম্প ও আইডি রেজলভার
    $rawDate = $submission?->submitted_at ?? $submission?->created_at ?? now();
    try {
        $formattedDate = \Carbon\Carbon::parse($rawDate)->format('d M Y, h:i A');
    } catch (\Throwable $e) {
        $formattedDate = (string) $rawDate;
    }

    $submissionId = $submission?->id ?? 'DIRECT';

    $adminViewUrl = route('admin.forms.submissions.index', array_filter([
        'form_id' => $form->id,
        'search'  => $submission?->id
    ]));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>New Form Submission - {{ $form->name }}</title>
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

        /* Responsive Mobile Layout (Gmail, Apple Mail, Outlook) */
        @media only screen and (max-width: 600px) {
            .mobile-outer-padding {
                padding: 14px 6px !important;
            }

            .mobile-card-container {
                width: 100% !important;
                border-radius: 8px !important;
            }

            .mobile-header-padding {
                padding: 20px 14px !important;
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

            .payload-table thead {
                display: none !important;
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

            .security-footprint-box {
                padding: 12px !important;
                font-size: 11.5px !important;
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

    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="mobile-outer-padding" style="background-color: {{ $siteBg }}; padding: 30px 15px;">
        <tr>
            <td align="center">
                <!-- Main Container -->
                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="mobile-card-container" style="max-width: 640px; background-color: {{ $cardBg }}; border-radius: 10px; border: 1px solid {{ $cardBorder }}; overflow: hidden; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);">
                    
                    <!-- Header Banner -->
                    <tr>
                        <td class="mobile-header-padding" style="background-color: {{ $headerBg }}; padding: 25px 30px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td>
                                        <span style="display: inline-block; background: rgba(255, 255, 255, 0.15); color: #ffffff; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; padding: 3px 10px; border-radius: 4px; margin-bottom: 8px;">
                                            New Lead / Submission
                                        </span>
                                        <h2 class="mobile-header-title" style="margin: 0; color: #ffffff; font-size: 20px; font-weight: 700; line-height: 1.3;">
                                            {{ $form->name }}
                                        </h2>
                                        <p style="margin: 5px 0 0; color: #cbd5e1; font-size: 13px;">
                                            Received on {{ $siteTitle }} • Entry #<span class="bn-digit">{{ $submissionId }}</span>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td class="mobile-body-padding" style="padding: 25px 30px;">
                            <p style="margin: 0 0 16px; font-size: 14px; color: {{ $textColor }}; line-height: 1.5;">
                                A new user response has been successfully captured. Below are the submitted details:
                            </p>

                            <!-- Submission Payload Table -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" class="payload-table" style="border-collapse: collapse; margin-bottom: 25px; border: 1px solid {{ $cardBorder }}; border-radius: 8px; overflow: hidden;">
                                <thead>
                                    <tr style="background-color: #f8fafc; border-bottom: 1.5px solid {{ $cardBorder }};">
                                        <th align="left" style="padding: 10px 14px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; width: 35%;">Field</th>
                                        <th align="left" style="padding: 10px 14px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Submitted Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($submission && $submission->values && $submission->values->isNotEmpty())
                                        @foreach($submission->values as $item)
                                            @php
                                                $fieldLabel = $item->field->label ?? ($item->field->name ?? 'Custom Field');
                                                $value = $item->value;

                                                if (is_string($value) && (str_starts_with($value, '[') || str_starts_with($value, '{'))) {
                                                    $decoded = json_decode($value, true);
                                                    if (is_array($decoded)) {
                                                        $value = implode(', ', $decoded);
                                                    }
                                                }

                                                $isFile = is_string($value) && (str_contains($value, 'uploads/forms/') || preg_match('/\.(jpg|jpeg|png|webp|pdf|docx?|zip)$/i', $value));
                                                $fileUrl = $isFile ? asset(ltrim($value, '/')) : null;
                                            @endphp
                                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                                <td class="payload-label-cell" style="padding: 11px 14px; font-size: 12.5px; font-weight: 700; color: #334155; vertical-align: top; background-color: #fafbfc;">
                                                    {{ $fieldLabel }}
                                                </td>
                                                <td class="payload-val-cell" style="padding: 11px 14px; font-size: 13px; color: {{ $titleColor }}; vertical-align: top;">
                                                    @if($isFile && $fileUrl)
                                                        <a href="{{ $fileUrl }}" target="_blank" class="btn-cta-responsive" style="display: inline-block; background-color: #eff6ff; color: {{ $primaryColor }}; border: 1px solid #bfdbfe; font-size: 11.5px; font-weight: 700; text-decoration: none !important; padding: 5px 12px; border-radius: 4px;">
                                                            📁 View Attached File
                                                        </a>
                                                        <small style="display: block; color: #94a3b8; font-size: 10px; margin-top: 4px;">{{ basename($value) }}</small>
                                                    @else
                                                        {!! !empty($value) ? nl2br(e($value)) : '<span style="color:#94a3b8; font-style:italic;">Empty</span>' !!}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @elseif(!empty($fieldData))
                                        @foreach($fieldData as $fLabel => $fVal)
                                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                                <td class="payload-label-cell" style="padding: 11px 14px; font-size: 12.5px; font-weight: 700; color: #334155; vertical-align: top; background-color: #fafbfc;">
                                                    {{ $fLabel }}
                                                </td>
                                                <td class="payload-val-cell" style="padding: 11px 14px; font-size: 13px; color: {{ $titleColor }}; vertical-align: top;">
                                                    {!! !empty($fVal) ? nl2br(e($fVal)) : '<span style="color:#94a3b8; font-style:italic;">Empty</span>' !!}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="2" style="padding: 18px; text-align: center; color: #94a3b8; font-size: 12px;">
                                                No field payload recorded.
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>

                            <!-- Origin & Security Footprint -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" class="security-footprint-box" style="background-color: #f8fafc; border: 1px solid {{ $cardBorder }}; border-radius: 8px; padding: 14px 18px; margin-bottom: 22px;">
                                <tr>
                                    <td style="font-size: 12px; color: #64748b; line-height: 1.6;">
                                        <strong style="color: #334155;">Client Real IP:</strong> 
                                        <span class="bn-digit" style="font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 12px; font-weight: 700; color: {{ $titleColor }}; background: #e2e8f0; padding: 2px 6px; border-radius: 4px;">
                                            {{ $submission?->ip_address ?? ($ip ?? '127.0.0.1') }}
                                        </span>
                                        <br>
                                        <strong style="color: #334155;">Timestamp:</strong> 
                                        <span class="bn-digit" style="color: {{ $titleColor }};">{{ $formattedDate }}</span>
                                        @if(!empty($submission?->user_agent))
                                            <br>
                                            <strong style="color: #334155;">Browser / Device:</strong> 
                                            <span style="font-size: 11px; color: #64748b; word-break: break-all;">{{ $submission->user_agent }}</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            <!-- Call to Action Button -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center" style="padding-top: 5px;">
                                        <a href="{{ $adminViewUrl }}" target="_blank" class="btn-cta-responsive" style="display: inline-block; background-color: {{ $primaryColor }}; color: #ffffff !important; font-size: 13px; font-weight: 700; text-decoration: none !important; padding: 10px 26px; border-radius: 50px; box-shadow: 0 3px 10px rgba(37,99,235,0.25);">
                                            Open in Submissions Ledger &rarr;
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; padding: 18px 30px; border-top: 1px solid {{ $cardBorder }}; text-align: center;">
                            <p style="margin: 0; font-size: 11.5px; color: #94a3b8; line-height: 1.5;">
                                This automated alert was sent by <strong>{{ $siteTitle }} Form Engine</strong>.
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