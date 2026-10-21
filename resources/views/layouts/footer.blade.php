@php
    $footerKeys = [
        'color_footer_bg',
        'color_footer_text',
        'footer_bg',
        'footer_text_color',
        'footer_heading_color',
        'footer_bottom_bg',
        'footer_logo',
        'footer_show_logo',
        'footer_logo_width',
        'footer_logo_height',
        'footer_logo_mobile_width',
        'footer_logo_mobile_height',
        'footer_brand_title',
        'footer_show_brand_title',
        'footer_brand_tagline',
        'footer_show_brand_tagline',
        'footer_about_text',
        'footer_phone',
        'footer_email',
        'footer_track_btn_text',
        'footer_track_btn_url',
        'footer_show_accordion_mobile',
        'footer_mobile_align',
        'footer_col2_title',
        'footer_col3_title',
        'footer_col4_title',
        'footer_show_social',
        'footer_show_payment_badges',
        'footer_payment_only_logo',
        'footer_copyright_text',
        'footer_quick_links_json',
        'footer_support_links_json',
        'footer_social_links_json',
        'footer_payment_badges_json'
    ];

    $footerSettings = DB::table('settings')->whereIn('key', $footerKeys)->pluck('value', 'key')->toArray();

    $quickLinks = !empty($footerSettings['footer_quick_links_json'])
        ? json_decode($footerSettings['footer_quick_links_json'], true)
        : [
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'Shop', 'url' => '/shop'],
            ['label' => 'My Cart', 'url' => '/cart'],
            ['label' => 'Checkout', 'url' => '/checkout']
        ];

    $supportLinks = !empty($footerSettings['footer_support_links_json'])
        ? json_decode($footerSettings['footer_support_links_json'], true)
        : [
            ['label' => 'About Us', 'url' => '/about-us'],
            ['label' => 'Contact Us', 'url' => '/contact-us'],
            ['label' => 'Latest Blogs', 'url' => '/blogs']
        ];

    $socialLinks = !empty($footerSettings['footer_social_links_json'])
        ? json_decode($footerSettings['footer_social_links_json'], true)
        : [
            ['icon' => 'fab fa-facebook-f', 'url' => '#'],
            ['icon' => 'fab fa-instagram', 'url' => '#'],
            ['icon' => 'fab fa-youtube', 'url' => '#'],
            ['icon' => 'fab fa-tiktok', 'url' => '#']
        ];

    $paymentBadges = !empty($footerSettings['footer_payment_badges_json'])
        ? json_decode($footerSettings['footer_payment_badges_json'], true)
        : [
            ['name' => 'Cash on Delivery', 'image' => ''],
            ['name' => 'BanglaQR', 'image' => ''],
            ['name' => 'bKash', 'image' => ''],
            ['name' => 'Nagad', 'image' => '']
        ];

    $footerBg = $footerSettings['color_footer_bg'] ?? ($footerSettings['footer_bg'] ?? '#020b18');
    $footerTextColor = $footerSettings['color_footer_text'] ?? ($footerSettings['footer_text_color'] ?? '#94a3b8');
    $footerHeadingColor = $footerSettings['footer_heading_color'] ?? '#ffffff';
    $footerBottomBg = $footerSettings['footer_bottom_bg'] ?? '#010813';

    // Footer Logo Resolver
    $footerLogo = $footerSettings['footer_logo'] ?? null;
    $footerLogoUrl = null;
    if (!empty($footerLogo)) {
        if (filter_var($footerLogo, FILTER_VALIDATE_URL)) {
            $footerLogoUrl = $footerLogo;
        } else {
            $cleanLogo = ltrim(str_replace('storage/', '', $footerLogo), '/');
            if (file_exists(public_path($cleanLogo))) {
                $footerLogoUrl = asset($cleanLogo);
            } elseif (file_exists(public_path('uploads/' . $cleanLogo))) {
                $footerLogoUrl = asset('uploads/' . $cleanLogo);
            } else {
                $footerLogoUrl = asset($cleanLogo);
            }
        }
    }

    $logoWidth = !empty($footerSettings['footer_logo_width']) ? $footerSettings['footer_logo_width'] : '140';
    $logoHeight = !empty($footerSettings['footer_logo_height']) ? $footerSettings['footer_logo_height'] : '40';
    $logoMobileWidth = !empty($footerSettings['footer_logo_mobile_width']) ? $footerSettings['footer_logo_mobile_width'] : '110';
    $logoMobileHeight = !empty($footerSettings['footer_logo_mobile_height']) ? $footerSettings['footer_logo_mobile_height'] : '36';

    $brandTitle = !empty($footerSettings['footer_brand_title']) ? $footerSettings['footer_brand_title'] : config('app.name', 'Shop Now BD');
    $brandTagline = $footerSettings['footer_brand_tagline'] ?? '';
    $currentYear = date('Y');
    $mobileAlign = $footerSettings['footer_mobile_align'] ?? 'left';
@endphp

<style>
    :root {
        --footer-bg: {{ $footerBg }};
        --footer-text: {{ $footerTextColor }};
        --footer-heading: {{ $footerHeadingColor }};
        --footer-bottom-bg: {{ $footerBottomBg }};
    }

    .site-main-footer {
        background-color: var(--footer-bg) !important;
        color: var(--footer-text) !important;
        font-family: inherit;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        padding-top: 48px !important;
        padding-bottom: 20px !important;
    }
    
    .site-main-footer p,
    .site-main-footer small,
    .site-main-footer span,
    .site-main-footer div {
        color: var(--footer-text) !important;
    }

    .site-main-footer a {
        color: var(--footer-text) !important;
        text-decoration: none;
        transition: color 0.2s ease, transform 0.2s ease;
    }
    .site-main-footer a:hover {
        color: #ffffff !important;
        transform: translateX(3px);
    }

    .pc-footer-brand-title {
        font-size: 22px;
        font-weight: 800;
        color: var(--footer-heading) !important;
        letter-spacing: -0.4px;
        line-height: 1.2;
    }
    .pc-footer-heading {
        font-size: 14.5px;
        font-weight: 700;
        color: var(--footer-heading) !important;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 20px;
        letter-spacing: 0.2px;
    }
    .pc-footer-heading i {
        color: #f59e0b !important;
        font-size: 14px;
    }
    .pc-footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 11px;
    }
    .pc-footer-links li a {
        font-size: 13.5px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--footer-text) !important;
    }
    .pc-footer-links li a::before {
        content: '›';
        font-size: 16px;
        color: var(--footer-text);
        opacity: 0.55;
        line-height: 1;
        transition: color 0.2s, opacity 0.2s;
    }
    .pc-footer-links li a:hover::before {
        color: #f59e0b;
        opacity: 1;
    }
    .pc-contact-info-block {
        margin-top: 20px;
        padding-top: 16px;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        display: flex;
        flex-direction: column;
        gap: 12px;
        font-size: 13.5px;
    }
    .pc-contact-item {
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--footer-text) !important;
    }
    .pc-contact-item i {
        color: #f59e0b !important;
        font-size: 14px;
        width: 18px;
        text-align: center;
    }
    .pc-payment-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        max-width: 320px;
    }
    .pc-payment-card {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        padding: 8px 12px;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.2s ease;
        min-height: 44px;
    }
    .pc-payment-card:hover {
        background: rgba(255, 255, 255, 0.07);
        border-color: rgba(255, 255, 255, 0.22);
        transform: translateY(-1px);
    }
    .pc-payment-card img {
        max-height: 24px;
        max-width: 44px;
        object-fit: contain;
    }
    .pc-payment-card span {
        font-size: 12px;
        font-weight: 600;
        color: var(--footer-heading) !important;
        line-height: 1.2;
    }
    .pc-social-circle {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.08);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #ffffff !important;
        font-size: 14px;
        transition: all 0.2s ease;
    }
    .pc-social-circle:hover {
        background: #2563eb;
        border-color: #2563eb;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }

    /* Bengali Safe Typography Rule */
    .bn-digit {
        font-weight: 700 !important;
    }

    @media (max-width: 767.98px) {
        .site-main-footer {
            padding-top: 24px !important;
            padding-bottom: 16px !important;
            text-align: {{ $mobileAlign == 'center' ? 'center' : 'left' }};
        }
        .mob-footer-brand-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 14px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 12px;
            margin-bottom: 16px;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .mob-footer-brand-left {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }
        .mob-footer-brand-icon {
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.06);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
            padding: 4px;
        }
        .mob-footer-brand-icon img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .mob-accordion-group {
            display: flex;
            flex-direction: column;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.08);
            overflow: hidden;
            margin-bottom: 18px;
        }
        .mob-accordion-item {
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }
        .mob-accordion-item:last-child {
            border-bottom: none;
        }
        .mob-accordion-btn {
            width: 100%;
            background: transparent;
            border: none;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: var(--footer-heading) !important;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            text-align: left;
            transform: none !important;
            writing-mode: horizontal-tb !important;
            min-height: 46px;
        }
        .mob-accordion-btn .left-title {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-grow: 1;
            transform: none !important;
        }
        .mob-accordion-btn .left-title i {
            color: var(--footer-text) !important;
            font-size: 14px;
            width: 16px;
        }
        .mob-accordion-btn .chevron-icon {
            font-size: 11px;
            color: var(--footer-text) !important;
            transition: transform 0.25s ease;
            flex-shrink: 0;
        }
        .mob-accordion-btn[aria-expanded="true"] .chevron-icon {
            transform: rotate(180deg);
            color: #f59e0b !important;
        }
        .mob-accordion-body {
            padding: 0 16px 14px 42px;
            transform: none !important;
        }
        .mob-accordion-body ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .mob-accordion-body ul li a {
            font-size: 12.5px;
            color: var(--footer-text) !important;
            display: block;
            padding: 2px 0;
        }
        .mob-payments-row {
            display: flex;
            align-items: center;
            gap: 8px;
            overflow-x: auto;
            scrollbar-width: none;
            padding-bottom: 8px;
            margin-bottom: 16px;
            {{ $mobileAlign == 'center' ? 'justify-content: center;' : '' }}
        }
        .mob-payments-row::-webkit-scrollbar {
            display: none;
        }
        .mob-payment-pill {
            background: #ffffff;
            border-radius: 8px;
            padding: 6px 12px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
        }
        .mob-payment-pill img {
            max-height: 20px;
            max-width: 48px;
            object-fit: contain;
        }
        .mob-payment-pill span {
            font-size: 11px;
            font-weight: 700;
            color: #0f172a !important;
        }
        .mob-social-secure-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 0;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            margin-top: 6px;
        }
        .mob-social-secure-row .social-icons-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .mob-social-secure-row .secure-badge {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11.5px;
            color: var(--footer-text) !important;
            font-weight: 500;
            padding-left: 12px;
            border-left: 1px solid rgba(255, 255, 255, 0.15);
        }
        .mob-social-secure-row .secure-badge i {
            color: #ffffff !important;
        }
        .mob-copyright-wrapper {
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
        }
    }
</style>

<footer class="site-main-footer">
    
    <div class="container d-none d-md-block px-3 px-md-4">
        <div class="row g-4 pb-4">
            
            <div class="col-lg-3 col-md-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    @if(!empty($footerLogoUrl) && ($footerSettings['footer_show_logo'] ?? '1') == '1')
                        <img src="{{ $footerLogoUrl }}" alt="{{ $brandTitle }}" style="max-width: {{ $logoWidth }}px; max-height: {{ $logoHeight }}px; object-fit: contain;">
                    @elseif(($footerSettings['footer_show_brand_title'] ?? '1') == '1')
                        <h4 class="pc-footer-brand-title mb-0">
                            {{ $brandTitle }}
                        </h4>
                    @endif
                </div>

                @if(!empty($footerSettings['footer_about_text']))
                    <p class="small mb-3" style="line-height: 1.6; font-size: 13px;">
                        {{ $footerSettings['footer_about_text'] }}
                    </p>
                @endif

                @if(($footerSettings['footer_show_social'] ?? '1') == '1' && count($socialLinks) > 0)
                    <div class="d-flex gap-2">
                        @foreach($socialLinks as $soc)
                            @php
                                $sUrl = trim($soc['url'] ?? '#');
                                if ($sUrl !== '#' && !preg_match("~^(?:f|ht)tps?://~i", $sUrl)) {
                                    $sUrl = 'https://' . $sUrl;
                                }
                            @endphp
                            <a href="{{ $sUrl }}" class="pc-social-circle" target="_blank" rel="noopener noreferrer">
                                <i class="{{ $soc['icon'] ?? 'fab fa-facebook-f' }}"></i>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="col-lg-3 col-md-2 ps-lg-4">
                <div class="pc-footer-heading">
                    <i class="fas fa-link"></i>
                    <span>{{ $footerSettings['footer_col2_title'] ?? 'Quick Links' }}</span>
                </div>
                <ul class="pc-footer-links">
                    @foreach($quickLinks as $link)
                        <li><a href="{{ $link['url'] }}">{{ $link['label'] }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div class="col-lg-3 col-md-3">
                <div class="pc-footer-heading">
                    <i class="fas fa-headset"></i>
                    <span>{{ $footerSettings['footer_col3_title'] ?? 'Customer Support' }}</span>
                </div>
                <ul class="pc-footer-links">
                    @foreach($supportLinks as $link)
                        <li><a href="{{ $link['url'] }}">{{ $link['label'] }}</a></li>
                    @endforeach
                </ul>

                <div class="pc-contact-info-block">
                    @if(!empty($footerSettings['footer_phone']))
                        <div class="pc-contact-item">
                            <i class="fas fa-phone-alt"></i>
                            <a href="tel:{{ $footerSettings['footer_phone'] }}" class="bn-digit">{{ $footerSettings['footer_phone'] }}</a>
                        </div>
                    @endif
                    @if(!empty($footerSettings['footer_email']))
                        <div class="pc-contact-item">
                            <i class="fas fa-envelope"></i>
                            <a href="mailto:{{ $footerSettings['footer_email'] }}">{{ $footerSettings['footer_email'] }}</a>
                        </div>
                    @endif
                </div>
            </div>

            @if(($footerSettings['footer_show_payment_badges'] ?? '1') == '1')
                <div class="col-lg-3 col-md-3">
                    <div class="pc-footer-heading">
                        <i class="fas fa-credit-card"></i>
                        <span>{{ $footerSettings['footer_col4_title'] ?? 'Accepted Payment' }}</span>
                    </div>

                    <div class="pc-payment-grid">
                        @foreach($paymentBadges as $badge)
                            @php
                                $bImg = $badge['image'] ?? '';
                                $bSrc = '';
                                if (!empty($bImg)) {
                                    if (filter_var($bImg, FILTER_VALIDATE_URL)) {
                                        $bSrc = $bImg;
                                    } else {
                                        $cleanBImg = ltrim(str_replace('storage/', '', $bImg), '/');
                                        if (file_exists(public_path($cleanBImg))) {
                                            $bSrc = asset($cleanBImg);
                                        } elseif (file_exists(public_path('uploads/' . $cleanBImg))) {
                                            $bSrc = asset('uploads/' . $cleanBImg);
                                        } else {
                                            $bSrc = asset($cleanBImg);
                                        }
                                    }
                                }
                                $hideName = ($footerSettings['footer_payment_only_logo'] ?? '0') == '1';
                            @endphp
                            <div class="pc-payment-card {{ $hideName ? 'justify-content-center' : '' }}">
                                @if(!empty($bSrc))
                                    <img src="{{ $bSrc }}" alt="{{ $badge['name'] ?? 'Payment' }}">
                                @else
                                    <i class="fas fa-money-check-alt text-warning"></i>
                                @endif
                                @if(!$hideName)
                                    <span>{{ $badge['name'] ?? 'Cash on Delivery' }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        <div class="pt-3 pb-1 border-top border-secondary border-opacity-25 d-flex justify-content-between align-items-center" style="font-size: 12.5px;">
            <span class="bn-digit">
                {{ $footerSettings['footer_copyright_text'] ?? ('© ' . $currentYear . ' ' . $brandTitle . '. All rights reserved.') }}
            </span>
            <span>
                Developed by <strong class="text-light">Srabon IT</strong>
            </span>
        </div>
    </div>

    <div class="container d-md-none px-3">
        
        <a href="{{ route('home') }}" class="mob-footer-brand-box text-decoration-none">
            <div class="mob-footer-brand-left">
                @if(!empty($footerLogoUrl) && ($footerSettings['footer_show_logo'] ?? '1') == '1')
                    <div class="mob-footer-brand-icon" style="width: auto; height: auto;">
                        <img src="{{ $footerLogoUrl }}" alt="{{ $brandTitle }}" style="max-width: {{ $logoMobileWidth }}px; max-height: {{ $logoMobileHeight }}px;">
                    </div>
                @endif
                <div>
                    @if(($footerSettings['footer_show_brand_title'] ?? '1') == '1')
                        <h6 class="fw-bold mb-0" style="font-size: 15px; color: var(--footer-heading) !important;">{{ $brandTitle }}</h6>
                    @endif
                    @if(($footerSettings['footer_show_brand_tagline'] ?? '1') == '1' && !empty($brandTagline))
                        <small style="font-size: 10.5px;">{{ $brandTagline }}</small>
                    @endif
                </div>
            </div>
            <i class="fas fa-chevron-right small text-muted"></i>
        </a>

        <div class="mob-accordion-group">
            <div class="mob-accordion-item">
                <button type="button" class="mob-accordion-btn" data-bs-toggle="collapse" data-bs-target="#mobColLinks" aria-expanded="false">
                    <span class="left-title">
                        <i class="fas fa-home"></i>
                        <span>{{ $footerSettings['footer_col2_title'] ?? 'Quick Links' }}</span>
                    </span>
                    <i class="fas fa-chevron-down chevron-icon"></i>
                </button>
                <div class="collapse mob-accordion-body" id="mobColLinks">
                    <ul>
                        @foreach($quickLinks as $link)
                            <li><a href="{{ $link['url'] }}">{{ $link['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="mob-accordion-item">
                <button type="button" class="mob-accordion-btn" data-bs-toggle="collapse" data-bs-target="#mobColSupport" aria-expanded="false">
                    <span class="left-title">
                        <i class="fas fa-headset"></i>
                        <span>{{ $footerSettings['footer_col3_title'] ?? 'Customer Support' }}</span>
                    </span>
                    <i class="fas fa-chevron-down chevron-icon"></i>
                </button>
                <div class="collapse mob-accordion-body" id="mobColSupport">
                    <ul>
                        @foreach($supportLinks as $link)
                            <li><a href="{{ $link['url'] }}">{{ $link['label'] }}</a></li>
                        @endforeach
                        @if(!empty($footerSettings['footer_phone']))
                            <li class="pt-1"><a href="tel:{{ $footerSettings['footer_phone'] }}"><i class="fas fa-phone-alt me-2 text-primary"></i> <span class="bn-digit">{{ $footerSettings['footer_phone'] }}</span></a></li>
                        @endif
                        @if(!empty($footerSettings['footer_email']))
                            <li><a href="mailto:{{ $footerSettings['footer_email'] }}"><i class="fas fa-envelope me-2 text-primary"></i> {{ $footerSettings['footer_email'] }}</a></li>
                        @endif
                    </ul>
                </div>
            </div>

            <div class="mob-accordion-item">
                <button type="button" class="mob-accordion-btn" data-bs-toggle="collapse" data-bs-target="#mobColCompany" aria-expanded="false">
                    <span class="left-title">
                        <i class="fas fa-building"></i>
                        <span>Company</span>
                    </span>
                    <i class="fas fa-chevron-down chevron-icon"></i>
                </button>
                <div class="collapse mob-accordion-body" id="mobColCompany">
                    <ul>
                        <li><a href="{{ url('/about-us') }}">About Us</a></li>
                        <li><a href="{{ url('/contact-us') }}">Contact Us</a></li>
                        <li><a href="{{ url('/blogs') }}">Latest News</a></li>
                    </ul>
                </div>
            </div>
        </div>

        @if(($footerSettings['footer_show_payment_badges'] ?? '1') == '1')
            <div class="fw-bold mb-2 small" style="font-size: 13px; color: var(--footer-heading) !important;">
                {{ $footerSettings['footer_col4_title'] ?? 'Accepted Payment' }}
            </div>

            <div class="mob-payments-row">
                @foreach($paymentBadges as $badge)
                    @php
                        $bImg = $badge['image'] ?? '';
                        $bSrc = '';
                        if (!empty($bImg)) {
                            if (filter_var($bImg, FILTER_VALIDATE_URL)) {
                                $bSrc = $bImg;
                            } else {
                                $cleanBImg = ltrim(str_replace('storage/', '', $bImg), '/');
                                if (file_exists(public_path($cleanBImg))) {
                                    $bSrc = asset($cleanBImg);
                                } elseif (file_exists(public_path('uploads/' . $cleanBImg))) {
                                    $bSrc = asset('uploads/' . $cleanBImg);
                                } else {
                                    $bSrc = asset($cleanBImg);
                                }
                            }
                        }
                        $hideName = ($footerSettings['footer_payment_only_logo'] ?? '0') == '1';
                    @endphp
                    <div class="mob-payment-pill">
                        @if(!empty($bSrc))
                            <img src="{{ $bSrc }}" alt="{{ $badge['name'] ?? 'Payment' }}">
                        @else
                            <span>{{ $badge['name'] ?? 'COD' }}</span>
                        @endif
                        @if(!empty($bSrc) && !$hideName)
                            <span class="ms-1.5" style="font-size: 10.5px;">{{ $badge['name'] }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mob-social-secure-row">
            <div class="social-icons-wrap">
                @if(($footerSettings['footer_show_social'] ?? '1') == '1')
                    @foreach($socialLinks as $soc)
                        @php
                            $sUrl = trim($soc['url'] ?? '#');
                            if ($sUrl !== '#' && !preg_match("~^(?:f|ht)tps?://~i", $sUrl)) {
                                $sUrl = 'https://' . $sUrl;
                            }
                        @endphp
                        <a href="{{ $sUrl }}" class="pc-social-circle" style="width: 34px; height: 34px; font-size: 13px;" target="_blank" rel="noopener noreferrer">
                            <i class="{{ $soc['icon'] ?? 'fab fa-facebook-f' }}"></i>
                        </a>
                    @endforeach
                @endif
            </div>

            <div class="secure-badge">
                <i class="fas fa-lock"></i>
                <span>Secure Checkout</span>
            </div>
        </div>

        <div class="text-center mob-copyright-wrapper d-flex flex-column align-items-center gap-1" style="font-size: 11.5px;">
            <div class="bn-digit">
                {{ $footerSettings['footer_copyright_text'] ?? ('© ' . $currentYear . ' ' . $brandTitle . '. All rights reserved.') }}
            </div>
            <div>
                Developed by <strong style="color: var(--footer-heading) !important;">Srabon IT</strong>
            </div>
        </div>

    </div>

</footer>