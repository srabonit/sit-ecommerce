<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe - {{ config('app.name', 'Shop Now BD') }}</title>
    
    <!-- Bootstrap 5 & FontAwesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @php
        // সেন্ট্রাল গ্লোবাল সেটিংস ক্যাশ রেজলভার
        static $unsubSettingsCache = null;
        if ($unsubSettingsCache === null) {
            $unsubSettingsCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
                return \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->toArray();
            });
        }
        $unsubSettings = $unsubSettingsCache ?? [];

        // ডায়নামিক থিম কালার ভ্যারিয়েবল (Existing Default Colors)
        $unsubSiteBg      = $unsubSettings['color_site_bg'] ?? '#f8fafc';
        $unsubCardBg      = $unsubSettings['color_auth_card_bg'] ?? '#ffffff';
        $unsubCardBorder  = $unsubSettings['color_auth_card_border'] ?? '#e2e8f0';
        $unsubTitleColor  = $unsubSettings['color_page_title'] ?? '#0f172a';
        $unsubTextColor   = $unsubSettings['color_auth_text'] ?? '#64748b';
        $unsubInputBg     = $unsubSettings['color_auth_input_bg'] ?? '#ffffff';
        $unsubInputBorder = $unsubSettings['color_auth_input_border'] ?? '#cbd5e1';
        $unsubInputFocus  = $unsubSettings['color_auth_input_focus_border'] ?? ($unsubSettings['color_common_btn_bg'] ?? '#2563eb');
        $unsubPrimary     = $unsubSettings['color_common_btn_bg'] ?? '#2563eb';
    @endphp

    <style>
        :root {
            --unsub-site-bg: {{ $unsubSiteBg }};
            --unsub-card-bg: {{ $unsubCardBg }};
            --unsub-card-border: {{ $unsubCardBorder }};
            --unsub-title: {{ $unsubTitleColor }};
            --unsub-text: {{ $unsubTextColor }};
            --unsub-input-bg: {{ $unsubInputBg }};
            --unsub-input-border: {{ $unsubInputBorder }};
            --unsub-input-focus: {{ $unsubInputFocus }};
            --unsub-primary: {{ $unsubPrimary }};
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

        /* Unsubscribe Card */
        .unsub-card {
            background: var(--unsub-card-bg) !important;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08), 0 1px 3px rgba(15, 23, 42, 0.04);
            border: 1px solid var(--unsub-card-border) !important;
            max-width: 480px;
            width: 100%;
            overflow: hidden;
            transition: all 0.25s ease;
        }

        .unsub-header {
            background: #f1f5f9;
            padding: 24px 20px;
            text-align: center;
            border-bottom: 1px solid var(--unsub-card-border) !important;
        }

        .unsub-header-title {
            color: var(--unsub-title) !important;
            font-weight: 700;
            font-size: 1.15rem;
            line-height: 1.35;
        }

        .unsub-header-brand {
            color: var(--unsub-text) !important;
            font-size: 12.5px;
        }

        .unsub-body {
            padding: 24px;
        }

        .unsub-desc {
            font-size: 13px;
            color: var(--unsub-text) !important;
            line-height: 1.5;
        }

        /* Target Email Alert Box */
        .email-target-box {
            background-color: #f8fafc;
            border: 1px solid var(--unsub-card-border);
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 13px;
            font-weight: 700;
            color: var(--unsub-title) !important;
            word-break: break-all;
        }

        /* Form Controls */
        .unsub-form-label {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--unsub-title) !important;
            margin-bottom: 6px;
            display: block;
        }

        .unsub-select-input {
            background-color: var(--unsub-input-bg) !important;
            border: 1px solid var(--unsub-input-border) !important;
            color: var(--unsub-title) !important;
            font-size: 13px;
            border-radius: 8px;
            padding: 8px 12px;
            transition: all 0.2s ease;
        }

        .unsub-select-input:focus {
            border-color: var(--unsub-input-focus) !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
            outline: none;
        }

        .unsub-checkbox-label {
            font-size: 12px;
            color: var(--unsub-text) !important;
            line-height: 1.45;
            cursor: pointer;
            user-select: none;
        }

        /* Action Buttons */
        .btn-unsub-danger {
            background-color: #dc2626 !important;
            border-color: #dc2626 !important;
            color: #ffffff !important;
            border-radius: 50px !important;
            padding: 9px 16px !important;
            font-weight: 600 !important;
            font-size: 13px !important;
            min-height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.2);
        }

        .btn-unsub-danger:hover {
            background-color: #b91c1c !important;
            border-color: #b91c1c !important;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
            transform: translateY(-1px);
        }

        .btn-unsub-cancel {
            background-color: #ffffff !important;
            border: 1px solid var(--unsub-input-border) !important;
            color: var(--unsub-text) !important;
            border-radius: 50px !important;
            padding: 9px 16px !important;
            font-weight: 600 !important;
            font-size: 13px !important;
            min-height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .btn-unsub-cancel:hover {
            background-color: #f8fafc !important;
            color: var(--unsub-title) !important;
            border-color: #94a3b8 !important;
        }

        /* Bengali digit protection */
        .bn-digit {
            font-weight: 700 !important;
        }

        /* Small Screen Optimization (iPhone SE / 320px–375px) */
        @media (max-width: 575.98px) {
            body {
                padding: 12px;
            }

            .unsub-header {
                padding: 20px 16px;
            }

            .unsub-header-title {
                font-size: 1.05rem;
            }

            .unsub-body {
                padding: 18px 16px;
            }

            .unsub-desc {
                font-size: 12.5px;
            }

            .email-target-box {
                font-size: 12px;
                padding: 8px 12px;
            }
        }
    </style>
</head>
<body>

<div class="unsub-card">
    {{-- Header Banner --}}
    <div class="unsub-header">
        <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px;">
            <i class="fas fa-envelope-open-text fs-4"></i>
        </div>
        <h5 class="unsub-header-title mb-1">Unsubscribe from Newsletter</h5>
        <p class="unsub-header-brand mb-0">{{ config('app.name', 'Shop Now BD') }}</p>
    </div>

    {{-- Main Body Form --}}
    <div class="unsub-body">
        <p class="unsub-desc mb-3">
            We are sorry to see you go! Are you sure you want to stop receiving promotional emails, discounts, and product updates for:
        </p>
        
        <div class="email-target-box mb-3 d-flex align-items-center gap-1.5">
            <i class="fas fa-user-circle" style="color: var(--unsub-primary);"></i>
            <span>{{ $subscriber->email }}</span>
        </div>

        <form action="{{ route('email.unsubscribe.process', $token) }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label class="unsub-form-label">Reason for unsubscribing (Optional):</label>
                <select name="reason" class="form-select unsub-select-input shadow-none">
                    <option value="I receive too many emails">I receive too many emails</option>
                    <option value="Content is no longer relevant">Content is no longer relevant to me</option>
                    <option value="I never signed up for this list">I never signed up for this mailing list</option>
                    <option value="Other">Other reasons</option>
                </select>
            </div>

            <div class="form-check mb-4">
                <input class="form-check-input shadow-none" type="checkbox" name="block_all" value="1" id="blockAll" style="cursor: pointer;">
                <label class="form-check-label unsub-checkbox-label" for="blockAll">
                    Permanently block this email address from all marketing broadcasts
                </label>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-unsub-danger">
                    <i class="fas fa-user-minus me-1.5"></i> <span>Unsubscribe Me</span>
                </button>
                <a href="{{ url('/') }}" class="btn btn-unsub-cancel">
                    <i class="fas fa-arrow-left me-1.5"></i> <span>Nevermind, take me back to store</span>
                </a>
            </div>
        </form>
    </div>
</div>

</body>
</html>