@php
    // সেন্ট্রাল গ্লোবাল সেটিংস ক্যাশ রেজলভার
    static $tooManyRequestsCache = null;
    if ($tooManyRequestsCache === null) {
        $tooManyRequestsCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
            return \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $globalSettings = $tooManyRequestsCache ?? [];

    $siteTitle = config('app.name', 'Laravel Store');

    // 429 Error Page Dynamic Colors
    $themeSiteBg         = $globalSettings['color_site_bg'] ?? '#f8fafc';
    $themeCardBg         = $globalSettings['color_auth_card_bg'] ?? '#ffffff';
    $themeCardBorder     = $globalSettings['color_auth_card_border'] ?? '#edf2f7';
    $themeTitleColor     = $globalSettings['color_page_title'] ?? '#0f172a';
    $themeTextColor      = $globalSettings['color_auth_text'] ?? '#64748b';

    $themePrimaryBtnBg   = $globalSettings['color_common_btn_bg'] ?? ($globalSettings['color_hdr_menu_hover_text'] ?? '#2563eb');
    $themePrimaryBtnText = $globalSettings['color_common_btn_text'] ?? '#ffffff';
    $themePrimaryBtnHov  = $globalSettings['color_auth_btn_hover'] ?? '#1d4ed8';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Too Many Requests - {{ $siteTitle }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --tmr-page-bg: {{ $themeSiteBg }};
            --tmr-card-bg: {{ $themeCardBg }};
            --tmr-card-border: {{ $themeCardBorder }};
            --tmr-title-color: {{ $themeTitleColor }};
            --tmr-text-color: {{ $themeTextColor }};
            --tmr-btn-bg: {{ $themePrimaryBtnBg }};
            --tmr-btn-text: {{ $themePrimaryBtnText }};
            --tmr-btn-hover: {{ $themePrimaryBtnHov }};
        }

        body.tmr-body-wrap {
            background-color: var(--tmr-page-bg) !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .tmr-card-box {
            background-color: var(--tmr-card-bg) !important;
            border: 1px solid var(--tmr-card-border) !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04) !important;
            max-width: 450px;
            width: 100%;
        }

        /* Strict text-decoration none for all anchor tags */
        a,
        a:hover,
        .tmr-card-box a,
        .theme-btn-primary {
            text-decoration: none !important;
        }

        .tmr-heading-title {
            color: var(--tmr-title-color) !important;
        }

        .tmr-desc-text {
            color: var(--tmr-text-color) !important;
        }

        .theme-btn-primary {
            background-color: var(--tmr-btn-bg) !important;
            border-color: var(--tmr-btn-bg) !important;
            color: var(--tmr-btn-text) !important;
            transition: all 0.2s ease;
            text-decoration: none !important;
        }

        .theme-btn-primary:hover {
            background-color: var(--tmr-btn-hover) !important;
            border-color: var(--tmr-btn-hover) !important;
            color: var(--tmr-btn-text) !important;
            text-decoration: none !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }
    </style>
</head>
<body class="tmr-body-wrap d-flex align-items-center justify-content-center min-vh-100 p-3">
    <div class="text-center p-4 p-md-5 rounded-4 tmr-card-box">
        <div class="text-warning mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-shield-exclamation" viewBox="0 0 16 16">
                <path d="M5.338 1.59a61.44 61.44 0 0 0-2.837.856.481.481 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.725 10.725 0 0 0 2.287 2.233c.346.244.652.42.893.533.12.057.218.095.293.118a.55.55 0 0 0 .101.025.615.615 0 0 0 .1-.025c.076-.023.174-.061.294-.118.24-.113.547-.29.893-.533a10.726 10.726 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.775 11.775 0 0 1-2.517 2.453 7.159 7.159 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7.158 7.158 0 0 1-1.048-.625 11.777 11.777 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 62.456 62.456 0 0 1 5.072.56z"/>
                <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z"/>
            </svg>
        </div>
        <h4 class="fw-bold mb-2 tmr-heading-title">Too Many Requests</h4>
        <p class="small mb-4 tmr-desc-text">You have exceeded the allowed request limit (20 requests per minute). Please wait a few seconds and refresh.</p>
        <a href="{{ url('/') }}" class="btn theme-btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm">
            Back to Home
        </a>
    </div>
</body>
</html>