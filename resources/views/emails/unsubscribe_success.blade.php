@php
    // সেন্ট্রাল গ্লোবাল সেটিংস ক্যাশ রেজলভার (বারবার DB কুয়েরি হওয়া আটকাবে)
    static $unsubSuccessPrefCache = null;
    if ($unsubSuccessPrefCache === null) {
        $unsubSuccessPrefCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
            return \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $settings = $unsubSuccessPrefCache ?? [];

    // ডায়নামিক থিম কালার ভ্যারিয়েবল (Existing Default Colors)
    $siteBrandName       = $settings['site_title'] ?? ($settings['footer_brand_title'] ?? config('app.name', 'Shop Now BD'));
    $themeSiteBg         = $settings['color_site_bg'] ?? '#f8fafc';
    $themeCardBg         = $settings['color_auth_card_bg'] ?? ($settings['color_card_bg'] ?? '#ffffff');
    $themeCardBorder     = $settings['color_auth_card_border'] ?? '#e2e8f0';
    $themeTitleColor     = $settings['color_page_title'] ?? ($settings['color_section_title'] ?? '#0f172a');
    $themeTextColor      = $settings['color_auth_text'] ?? '#64748b';
    $themePrimaryBtnBg   = $settings['color_common_btn_bg'] ?? '#2563eb';
    $themePrimaryBtnHover= $settings['color_auth_btn_hover'] ?? '#1d4ed8';
    $themePrimaryBtnText = $settings['color_common_btn_text'] ?? '#ffffff';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribed Successfully - {{ $siteBrandName }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --unsub-succ-site-bg: {{ $themeSiteBg }};
            --unsub-succ-card-bg: {{ $themeCardBg }};
            --unsub-succ-card-border: {{ $themeCardBorder }};
            --unsub-succ-title: {{ $themeTitleColor }};
            --unsub-succ-text: {{ $themeTextColor }};
            --unsub-succ-btn-bg: {{ $themePrimaryBtnBg }};
            --unsub-succ-btn-hover: {{ $themePrimaryBtnHover }};
            --unsub-succ-btn-text: {{ $themePrimaryBtnText }};
        }

        body {
            background-color: var(--unsub-succ-site-bg) !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            margin: 0;
            color: #1e293b;
        }

        /* Universal Strict Link Text Decoration */
        a, a:hover {
            text-decoration: none !important;
        }

        .success-card {
            background: var(--unsub-succ-card-bg) !important;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08), 0 1px 3px rgba(15, 23, 42, 0.04);
            max-width: 480px;
            width: 100%;
            border: 1px solid var(--unsub-succ-card-border) !important;
            text-align: center;
            padding: 36px 28px;
            transition: all 0.25s ease;
        }

        .success-icon-badge {
            background-color: rgba(16, 185, 129, 0.12);
            color: #10b981;
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.2);
        }

        .theme-section-title {
            color: var(--unsub-succ-title) !important;
            font-weight: 700;
            font-size: 1.25rem;
            line-height: 1.35;
        }

        .success-desc {
            color: var(--unsub-succ-text) !important;
            font-size: 13.5px;
            line-height: 1.55;
        }

        .email-highlight {
            color: var(--unsub-succ-title);
            background-color: #f1f5f9;
            padding: 2px 8px;
            border-radius: 6px;
            display: inline-block;
            word-break: break-all;
        }

        .transactional-alert-box {
            background-color: #f8fafc;
            border: 1px solid var(--unsub-succ-card-border) !important;
            border-radius: 10px;
            font-size: 12px;
            color: var(--unsub-succ-text);
            line-height: 1.5;
            padding: 10px 14px;
            text-align: left;
        }

        .btn-return-store {
            background-color: var(--unsub-succ-btn-bg) !important;
            border-color: var(--unsub-succ-btn-bg) !important;
            color: var(--unsub-succ-btn-text) !important;
            border-radius: 50px !important;
            padding: 10px 28px !important;
            font-size: 13.5px !important;
            font-weight: 700 !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 42px;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.25) !important;
        }

        .btn-return-store:hover {
            background-color: var(--unsub-succ-btn-hover) !important;
            border-color: var(--unsub-succ-btn-hover) !important;
            color: var(--unsub-succ-btn-text) !important;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(37, 99, 235, 0.35) !important;
        }

        .btn-return-store:active {
            transform: translateY(0);
        }

        /* Small Screen & Mobile Optimization (iPhone SE / 320px–375px) */
        @media (max-width: 575.98px) {
            body {
                padding: 12px;
            }

            .success-card {
                padding: 26px 18px;
                border-radius: 14px;
            }

            .success-icon-badge {
                width: 54px;
                height: 54px;
                margin-bottom: 12px;
            }

            .success-icon-badge i {
                font-size: 1.75rem !important;
            }

            .theme-section-title {
                font-size: 1.15rem;
            }

            .success-desc {
                font-size: 13px;
            }

            .btn-return-store {
                width: 100% !important;
                min-height: 44px;
            }
        }
    </style>
</head>
<body>

<div class="success-card">
    <div class="success-icon-badge">
        <i class="fas fa-circle-check fa-2x"></i>
    </div>
    
    <h4 class="theme-section-title mb-2">Unsubscribed Successfully</h4>
    
    <p class="success-desc mb-4">
        Your email address <strong class="email-highlight">{{ $subscriber->email }}</strong> has been unsubscribed from our marketing broadcast list. You will no longer receive promotional emails from us.[cite: 21]
    </p>

    <div class="transactional-alert-box mb-4">
        <div class="d-flex align-items-start gap-2">
            <i class="fas fa-info-circle fs-6 mt-0.5" style="color: var(--unsub-succ-btn-bg);"></i>
            <div>
                Important transactional emails regarding active orders or password resets will still be delivered if you make a purchase.[cite: 21]
            </div>
        </div>
    </div>

    <div>
        <a href="{{ url('/') }}" class="btn btn-return-store">
            <i class="fas fa-store"></i> <span>Return to Store</span>[cite: 21]
        </a>
    </div>
</div>

</body>
</html>