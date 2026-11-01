@php
    // সেন্ট্রাল গ্লোবাল সেটিংস ক্যাশ রেজলভার (বারবার DB কুয়েরি হওয়া আটকাবে)
    static $unsubPrefSettingsCache = null;
    if ($unsubPrefSettingsCache === null) {
        $unsubPrefSettingsCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
            return \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $settings = $unsubPrefSettingsCache ?? [];

    // ডায়নামিক থিম কালার ভ্যারিয়েবল (Existing Default Colors)
    $siteBrandName   = $settings['site_title'] ?? ($settings['footer_brand_title'] ?? config('app.name', 'Shop Now BD'));
    $themeSiteBg     = $settings['color_site_bg'] ?? '#f1f5f9';
    $themeCardBg     = $settings['color_auth_card_bg'] ?? ($settings['color_card_bg'] ?? '#ffffff');
    $themeCardBorder = $settings['color_auth_card_border'] ?? '#e2e8f0';
    $themeTitleColor = $settings['color_page_title'] ?? ($settings['color_section_title'] ?? '#0f172a');
    $themeTextColor  = $settings['color_auth_text'] ?? '#64748b';
    $themePrimary    = $settings['color_common_btn_bg'] ?? '#2563eb';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe - Email Preferences - {{ $siteBrandName }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --unsub-site-bg: {{ $themeSiteBg }};
            --unsub-card-bg: {{ $themeCardBg }};
            --unsub-card-border: {{ $themeCardBorder }};
            --unsub-title: {{ $themeTitleColor }};
            --unsub-text: {{ $themeTextColor }};
            --unsub-primary: {{ $themePrimary }};
        }

        body {
            background-color: var(--unsub-site-bg) !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            margin: 0;
        }

        /* Universal Strict Link Text Decoration */
        a, a:hover {
            text-decoration: none !important;
        }

        .unsubscribe-card {
            background: var(--unsub-card-bg) !important;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08), 0 1px 3px rgba(15, 23, 42, 0.04);
            max-width: 520px;
            width: 100%;
            overflow: hidden;
            border: 1px solid var(--unsub-card-border) !important;
            transition: all 0.25s ease;
        }

        .theme-section-title {
            color: var(--unsub-title) !important;
            font-weight: 700;
        }

        .unsub-sub-desc {
            color: var(--unsub-text) !important;
            font-size: 13px;
            line-height: 1.5;
        }

        .target-email-alert {
            background-color: #f8fafc;
            border: 1px solid var(--unsub-card-border) !important;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 13px;
            color: var(--unsub-text);
            word-break: break-all;
        }

        .target-email-alert strong {
            color: var(--unsub-title);
        }

        .unsub-radio-option {
            background-color: #ffffff;
            border: 1px solid var(--unsub-card-border);
            border-radius: 8px;
            padding: 9px 12px;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .unsub-radio-option:hover {
            background-color: #f8fafc;
            border-color: #cbd5e1;
        }

        .block-all-box {
            background-color: #f8fafc !important;
            border: 1px solid var(--unsub-card-border) !important;
            border-radius: 10px;
            padding: 14px 16px;
        }

        /* Action Buttons */
        .btn-confirm-unsub {
            background-color: #dc2626 !important;
            border-color: #dc2626 !important;
            color: #ffffff !important;
            border-radius: 50px !important;
            padding: 10px 20px !important;
            font-size: 13.5px !important;
            font-weight: 700 !important;
            min-height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.2);
        }

        .btn-confirm-unsub:hover {
            background-color: #b91c1c !important;
            border-color: #b91c1c !important;
            box-shadow: 0 4px 14px rgba(220, 38, 38, 0.3);
            transform: translateY(-1px);
        }

        .btn-return-store {
            background-color: #ffffff !important;
            border: 1px solid var(--unsub-card-border) !important;
            color: var(--unsub-text) !important;
            border-radius: 50px !important;
            padding: 10px 20px !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            min-height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s ease;
        }

        .btn-return-store:hover {
            background-color: #f8fafc !important;
            color: var(--unsub-title) !important;
            border-color: #94a3b8 !important;
        }

        /* Small Screen Optimization (iPhone SE / 320px–375px) */
        @media (max-width: 575.98px) {
            body {
                padding: 12px;
            }

            .unsubscribe-card {
                border-radius: 14px;
            }

            .unsub-card-body {
                padding: 22px 16px !important;
            }

            .theme-section-title {
                font-size: 1.15rem;
            }

            .unsub-radio-option {
                padding: 8px 10px;
                font-size: 12px !important;
            }

            .block-all-box {
                padding: 12px 14px !important;
            }
        }
    </style>
</head>
<body>

<div class="unsubscribe-card">
    <div class="p-4 p-md-5 unsub-card-body">
        
        <!-- Header -->
        <div class="text-center mb-4">
            <div class="bg-danger-subtle text-danger p-3 rounded-circle d-inline-flex mb-3" style="width: 56px; height: 56px; align-items: center; justify-content: center;">
                <i class="fas fa-envelope-circle-xmark fa-2x"></i>
            </div>
            <h4 class="theme-section-title mb-1">Manage Email Preferences</h4>
            <p class="unsub-sub-desc mb-0">We are sorry to see you go. You can update your newsletter preferences below.</p>
        </div>

        <!-- Target Email Alert Box -->
        <div class="target-email-alert mb-4 d-flex align-items-center gap-2">
            <i class="fas fa-user-circle" style="color: var(--unsub-primary);"></i>
            <div>
                Target Email: <strong>{{ $subscriber->email }}</strong>
            </div>
        </div>

        <form action="{{ url('email/unsubscribe/' . $token) }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label small fw-bold mb-2" style="color: var(--unsub-title);">Please tell us why you are unsubscribing:</label>
                <div class="d-flex flex-column gap-2" style="font-size: 13px;">
                    <label class="unsub-radio-option form-check mb-0 d-flex align-items-center gap-2">
                        <input class="form-check-input mt-0" type="radio" name="reason" id="reason1" value="Too many emails" checked>
                        <span class="form-check-label text-secondary" for="reason1">I receive too many promotional emails</span>
                    </label>
                    <label class="unsub-radio-option form-check mb-0 d-flex align-items-center gap-2">
                        <input class="form-check-input mt-0" type="radio" name="reason" id="reason2" value="No longer interested">
                        <span class="form-check-label text-secondary" for="reason2">I am no longer interested in these products</span>
                    </label>
                    <label class="unsub-radio-option form-check mb-0 d-flex align-items-center gap-2">
                        <input class="form-check-input mt-0" type="radio" name="reason" id="reason3" value="Never signed up">
                        <span class="form-check-label text-secondary" for="reason3">I never consented or signed up for this newsletter</span>
                    </label>
                    <label class="unsub-radio-option form-check mb-0 d-flex align-items-center gap-2">
                        <input class="form-check-input mt-0" type="radio" name="reason" id="reason4" value="Content irrelevant">
                        <span class="form-check-label text-secondary" for="reason4">The content is not relevant to my interests</span>
                    </label>
                </div>
            </div>

            <div class="mb-4 form-check block-all-box">
                <input class="form-check-input ms-0 me-2" type="checkbox" name="block_all" id="blockAllInput" value="1" style="cursor: pointer;">
                <label class="form-check-label small fw-bold" for="blockAllInput" style="color: var(--unsub-title); cursor: pointer;">
                    Permanently block all future marketing communications
                </label>
                <div style="color: var(--unsub-text); font-size: 11px; margin-left: 24px; margin-top: 2px;">
                    Your email address will be placed on our strict suppression blacklist.
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-confirm-unsub">
                    <i class="fas fa-user-minus"></i> <span>Confirm Unsubscribe</span>
                </button>
                <a href="{{ url('/') }}" class="btn btn-return-store">
                    <i class="fas fa-store"></i> <span>Keep My Subscription &amp; Return To Store</span>
                </a>
            </div>
        </form>
    </div>
</div>

</body>
</html>