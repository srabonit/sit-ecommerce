<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribed Successfully - {{ config('app.name', 'Shop Now BD') }}</title>
    
    <!-- Bootstrap 5 & FontAwesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @php
        // সেন্ট্রাল গ্লোবাল সেটিংস ক্যাশ রেজলভার
        static $unsubSuccessSettingsCache = null;
        if ($unsubSuccessSettingsCache === null) {
            $unsubSuccessSettingsCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
                return \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->toArray();
            });
        }
        $unsubSuccessSettings = $unsubSuccessSettingsCache ?? [];

        // ডায়নামিক থিম কালার ভ্যারিয়েবল (Existing Default Colors)
        $unsubSiteBg       = $unsubSuccessSettings['color_site_bg'] ?? '#f8fafc';
        $unsubCardBg       = $unsubSuccessSettings['color_auth_card_bg'] ?? '#ffffff';
        $unsubCardBorder   = $unsubSuccessSettings['color_auth_card_border'] ?? '#e2e8f0';
        $unsubTitleColor   = $unsubSuccessSettings['color_page_title'] ?? '#0f172a';
        $unsubTextColor    = $unsubSuccessSettings['color_auth_text'] ?? '#64748b';
        $unsubPrimary      = $unsubSuccessSettings['color_common_btn_bg'] ?? '#2563eb';
        $unsubPrimaryHover = $unsubSuccessSettings['color_auth_btn_hover'] ?? '#1d4ed8';
        $unsubSuccessColor = '#10b981';
    @endphp

    <style>
        :root {
            --unsub-site-bg: {{ $unsubSiteBg }};
            --unsub-card-bg: {{ $unsubCardBg }};
            --unsub-card-border: {{ $unsubCardBorder }};
            --unsub-title: {{ $unsubTitleColor }};
            --unsub-text: {{ $unsubTextColor }};
            --unsub-primary: {{ $unsubPrimary }};
            --unsub-primary-hover: {{ $unsubPrimaryHover }};
            --unsub-success: {{ $unsubSuccessColor }};
        }

        body {
            background-color: var(--unsub-site-bg) !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
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

        /* Success Card Box */
        .success-card {
            background: var(--unsub-card-bg) !important;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08), 0 1px 3px rgba(15, 23, 42, 0.04);
            border: 1px solid var(--unsub-card-border) !important;
            max-width: 460px;
            width: 100%;
            padding: 36px 28px;
            text-align: center;
            transition: all 0.25s ease;
        }

        .success-icon-circle {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background-color: rgba(16, 185, 129, 0.12);
            color: var(--unsub-success);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.2);
        }

        .success-card-title {
            color: var(--unsub-title) !important;
            font-weight: 700;
            font-size: 1.25rem;
            line-height: 1.35;
        }

        .success-card-desc {
            color: var(--unsub-text) !important;
            font-size: 13.5px;
            line-height: 1.55;
        }

        .subscriber-email-highlight {
            color: var(--unsub-title);
            background-color: #f1f5f9;
            padding: 2px 8px;
            border-radius: 6px;
            display: inline-block;
            word-break: break-all;
        }

        .success-card-note {
            color: var(--unsub-text) !important;
            font-size: 12px;
            line-height: 1.45;
            opacity: 0.85;
        }

        /* Return to Home Action Button */
        .btn-return-home {
            background-color: var(--unsub-primary) !important;
            border-color: var(--unsub-primary) !important;
            color: #ffffff !important;
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

        .btn-return-home:hover {
            background-color: var(--unsub-primary-hover) !important;
            border-color: var(--unsub-primary-hover) !important;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(37, 99, 235, 0.35) !important;
            color: #ffffff !important;
        }

        .btn-return-home:active {
            transform: translateY(0);
        }

        /* Bengali digit protection */
        .bn-digit {
            font-weight: 700 !important;
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

            .success-icon-circle {
                width: 54px;
                height: 54px;
                font-size: 18px;
                margin-bottom: 12px;
            }

            .success-card-title {
                font-size: 1.15rem;
            }

            .success-card-desc {
                font-size: 13px;
            }

            .btn-return-home {
                width: 100% !important;
                min-height: 44px;
            }
        }
    </style>
</head>
<body>

<div class="success-card">
    <div class="success-icon-circle">
        <i class="fas fa-check fs-3"></i>
    </div>
    
    <h5 class="success-card-title mb-2">Unsubscribed Successfully</h5>
    
    <p class="success-card-desc mb-3">
        Your email address <strong class="subscriber-email-highlight">{{ $subscriber->email }}</strong> has been removed from our marketing mailing list. You will no longer receive promotional broadcasts.[cite: 21]
    </p>
    
    <small class="success-card-note d-block mb-4">
        If this was a mistake, you can always subscribe again through our website anytime.
    </small>
    
    <a href="{{ url('/') }}" class="btn btn-return-home">
        <i class="fas fa-store"></i> <span>Return to Shop Home</span>[cite: 21]
    </a>
</div>

</body>
</html>