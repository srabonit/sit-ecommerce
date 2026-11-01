@php
    $footerKeys = [
        // Legacy fallbacks
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
        'footer_payment_badges_json',

        // 1. PC Footer Dynamic Color Controls
        'color_ftr_bg',
        'color_ftr_border',
        'color_ftr_text',
        'color_ftr_logo_border',
        'color_ftr_brand_title',
        'color_ftr_brand_tagline',
        'color_ftr_about_text',
        'color_ftr_heading',
        'color_ftr_heading_icon',
        'color_ftr_link_text',
        'color_ftr_link_hover_text',
        'color_ftr_link_icon',
        'color_ftr_link_hover_icon',
        'color_ftr_contact_icon',
        'color_ftr_contact_text',
        'color_ftr_contact_hover',
        'color_ftr_social_icon',
        'color_ftr_social_bg',
        'color_ftr_social_border',
        'color_ftr_social_hover_icon',
        'color_ftr_social_hover_bg',
        'color_ftr_social_hover_border',
        'color_ftr_payment_bg',
        'color_ftr_payment_border',
        'color_ftr_payment_text',
        'color_ftr_payment_logo_border',
        'color_ftr_payment_hover_bg',
        'color_ftr_payment_hover_border',
        'color_ftr_bottom_bg',
        'color_ftr_bottom_border',
        'color_ftr_copyright_text',
        'color_ftr_developer_text',

        // 2. Mobile Footer Dynamic Color Controls
        'color_ftr_mob_brand_bg',
        'color_ftr_mob_brand_border',
        'color_ftr_mob_logo_box_bg',
        'color_ftr_mob_logo_box_border',
        'color_ftr_mob_brand_arrow',
        'color_ftr_mob_acc_bg',
        'color_ftr_mob_acc_border',
        'color_ftr_mob_acc_item_border',
        'color_ftr_mob_acc_btn_text',
        'color_ftr_mob_acc_btn_icon',
        'color_ftr_mob_acc_chevron',
        'color_ftr_mob_acc_chevron_active',
        'color_ftr_mob_acc_body_bg',
        'color_ftr_mob_acc_link_text',
        'color_ftr_mob_acc_link_hover',
        'color_ftr_mob_pay_pill_bg',
        'color_ftr_mob_pay_pill_border',
        'color_ftr_mob_pay_pill_text',
        'color_ftr_mob_secure_border',
        'color_ftr_mob_secure_icon',
        'color_ftr_mob_secure_text'
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

    // PC Colors Resolvers
    $ftrBg                 = $footerSettings['color_ftr_bg'] ?? ($footerSettings['color_footer_bg'] ?? ($footerSettings['footer_bg'] ?? '#020b18'));
    $ftrBorder             = $footerSettings['color_ftr_border'] ?? 'rgba(255, 255, 255, 0.08)';
    $ftrText               = $footerSettings['color_ftr_text'] ?? ($footerSettings['color_footer_text'] ?? ($footerSettings['footer_text_color'] ?? '#94a3b8'));
    $ftrLogoBorder         = $footerSettings['color_ftr_logo_border'] ?? 'transparent';
    $ftrBrandTitle         = $footerSettings['color_ftr_brand_title'] ?? ($footerSettings['footer_heading_color'] ?? '#ffffff');
    $ftrBrandTagline       = $footerSettings['color_ftr_brand_tagline'] ?? '#94a3b8';
    $ftrAboutText          = $footerSettings['color_ftr_about_text'] ?? '#94a3b8';
    $ftrHeading            = $footerSettings['color_ftr_heading'] ?? ($footerSettings['footer_heading_color'] ?? '#ffffff');
    $ftrHeadingIcon        = $footerSettings['color_ftr_heading_icon'] ?? '#f59e0b';
    $ftrLinkText           = $footerSettings['color_ftr_link_text'] ?? '#94a3b8';
    $ftrLinkHoverText      = $footerSettings['color_ftr_link_hover_text'] ?? '#ffffff';
    $ftrLinkIcon           = $footerSettings['color_ftr_link_icon'] ?? 'rgba(148, 163, 184, 0.6)';
    $ftrLinkHoverIcon      = $footerSettings['color_ftr_link_hover_icon'] ?? '#f59e0b';
    $ftrContactIcon        = $footerSettings['color_ftr_contact_icon'] ?? '#f59e0b';
    $ftrContactText        = $footerSettings['color_ftr_contact_text'] ?? '#94a3b8';
    $ftrContactHover       = $footerSettings['color_ftr_contact_hover'] ?? '#ffffff';
    $ftrSocialIcon         = $footerSettings['color_ftr_social_icon'] ?? '#ffffff';
    $ftrSocialBg           = $footerSettings['color_ftr_social_bg'] ?? 'rgba(255, 255, 255, 0.06)';
    $ftrSocialBorder       = $footerSettings['color_ftr_social_border'] ?? 'rgba(255, 255, 255, 0.08)';
    $ftrSocialHoverIcon    = $footerSettings['color_ftr_social_hover_icon'] ?? '#ffffff';
    $ftrSocialHoverBg      = $footerSettings['color_ftr_social_hover_bg'] ?? '#2563eb';
    $ftrSocialHoverBorder  = $footerSettings['color_ftr_social_hover_border'] ?? '#2563eb';
    $ftrPaymentBg          = $footerSettings['color_ftr_payment_bg'] ?? 'rgba(255, 255, 255, 0.03)';
    $ftrPaymentBorder      = $footerSettings['color_ftr_payment_border'] ?? 'rgba(255, 255, 255, 0.1)';
    $ftrPaymentText        = $footerSettings['color_ftr_payment_text'] ?? '#ffffff';
    $ftrPaymentLogoBorder  = $footerSettings['color_ftr_payment_logo_border'] ?? 'transparent';
    $ftrPaymentHoverBg     = $footerSettings['color_ftr_payment_hover_bg'] ?? 'rgba(255, 255, 255, 0.07)';
    $ftrPaymentHoverBorder = $footerSettings['color_ftr_payment_hover_border'] ?? 'rgba(255, 255, 255, 0.22)';
    $ftrBottomBg           = $footerSettings['color_ftr_bottom_bg'] ?? ($footerSettings['footer_bottom_bg'] ?? '#010813');
    $ftrBottomBorder       = $footerSettings['color_ftr_bottom_border'] ?? 'rgba(255, 255, 255, 0.08)';
    $ftrCopyrightText      = $footerSettings['color_ftr_copyright_text'] ?? '#94a3b8';
    $ftrDeveloperText      = $footerSettings['color_ftr_developer_text'] ?? '#ffffff';

    // Mobile Colors Resolvers
    $ftrMobBrandBg         = $footerSettings['color_ftr_mob_brand_bg'] ?? 'rgba(255, 255, 255, 0.03)';
    $ftrMobBrandBorder     = $footerSettings['color_ftr_mob_brand_border'] ?? 'rgba(255, 255, 255, 0.08)';
    $ftrMobLogoBoxBg       = $footerSettings['color_ftr_mob_logo_box_bg'] ?? 'rgba(255, 255, 255, 0.06)';
    $ftrMobLogoBoxBorder   = $footerSettings['color_ftr_mob_logo_box_border'] ?? 'rgba(255, 255, 255, 0.08)';
    $ftrMobBrandArrow      = $footerSettings['color_ftr_mob_brand_arrow'] ?? '#94a3b8';
    $ftrMobAccBg           = $footerSettings['color_ftr_mob_acc_bg'] ?? 'rgba(255, 255, 255, 0.02)';
    $ftrMobAccBorder       = $footerSettings['color_ftr_mob_acc_border'] ?? 'rgba(255, 255, 255, 0.08)';
    $ftrMobAccItemBorder   = $footerSettings['color_ftr_mob_acc_item_border'] ?? 'rgba(255, 255, 255, 0.06)';
    $ftrMobAccBtnText      = $footerSettings['color_ftr_mob_acc_btn_text'] ?? '#ffffff';
    $ftrMobAccBtnIcon      = $footerSettings['color_ftr_mob_acc_btn_icon'] ?? '#94a3b8';
    $ftrMobAccChevron      = $footerSettings['color_ftr_mob_acc_chevron'] ?? '#94a3b8';
    $ftrMobAccChevronActive= $footerSettings['color_ftr_mob_acc_chevron_active'] ?? '#f59e0b';
    $ftrMobAccBodyBg       = $footerSettings['color_ftr_mob_acc_body_bg'] ?? 'transparent';
    $ftrMobAccLinkText     = $footerSettings['color_ftr_mob_acc_link_text'] ?? '#94a3b8';
    $ftrMobAccLinkHover    = $footerSettings['color_ftr_mob_acc_link_hover'] ?? '#ffffff';
    $ftrMobPayPillBg       = $footerSettings['color_ftr_mob_pay_pill_bg'] ?? '#ffffff';
    $ftrMobPayPillBorder   = $footerSettings['color_ftr_mob_pay_pill_border'] ?? 'transparent';
    $ftrMobPayPillText     = $footerSettings['color_ftr_mob_pay_pill_text'] ?? '#0f172a';
    $ftrMobSecureBorder    = $footerSettings['color_ftr_mob_secure_border'] ?? 'rgba(255, 255, 255, 0.08)';
    $ftrMobSecureIcon      = $footerSettings['color_ftr_mob_secure_icon'] ?? '#ffffff';
    $ftrMobSecureText      = $footerSettings['color_ftr_mob_secure_text'] ?? '#94a3b8';

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
        /* PC Footer Variables */
        --ftr-bg: {{ $ftrBg }};
        --ftr-border: {{ $ftrBorder }};
        --ftr-text: {{ $ftrText }};
        --ftr-logo-border: {{ $ftrLogoBorder }};
        --ftr-brand-title: {{ $ftrBrandTitle }};
        --ftr-brand-tagline: {{ $ftrBrandTagline }};
        --ftr-about-text: {{ $ftrAboutText }};
        --ftr-heading: {{ $ftrHeading }};
        --ftr-heading-icon: {{ $ftrHeadingIcon }};
        --ftr-link-text: {{ $ftrLinkText }};
        --ftr-link-hover-text: {{ $ftrLinkHoverText }};
        --ftr-link-icon: {{ $ftrLinkIcon }};
        --ftr-link-hover-icon: {{ $ftrLinkHoverIcon }};
        --ftr-contact-icon: {{ $ftrContactIcon }};
        --ftr-contact-text: {{ $ftrContactText }};
        --ftr-contact-hover: {{ $ftrContactHover }};
        --ftr-social-icon: {{ $ftrSocialIcon }};
        --ftr-social-bg: {{ $ftrSocialBg }};
        --ftr-social-border: {{ $ftrSocialBorder }};
        --ftr-social-hover-icon: {{ $ftrSocialHoverIcon }};
        --ftr-social-hover-bg: {{ $ftrSocialHoverBg }};
        --ftr-social-hover-border: {{ $ftrSocialHoverBorder }};
        --ftr-payment-bg: {{ $ftrPaymentBg }};
        --ftr-payment-border: {{ $ftrPaymentBorder }};
        --ftr-payment-text: {{ $ftrPaymentText }};
        --ftr-payment-logo-border: {{ $ftrPaymentLogoBorder }};
        --ftr-payment-hover-bg: {{ $ftrPaymentHoverBg }};
        --ftr-payment-hover-border: {{ $ftrPaymentHoverBorder }};
        --ftr-bottom-bg: {{ $ftrBottomBg }};
        --ftr-bottom-border: {{ $ftrBottomBorder }};
        --ftr-copyright-text: {{ $ftrCopyrightText }};
        --ftr-developer-text: {{ $ftrDeveloperText }};

        /* Mobile Footer Variables */
        --ftr-mob-brand-bg: {{ $ftrMobBrandBg }};
        --ftr-mob-brand-border: {{ $ftrMobBrandBorder }};
        --ftr-mob-logo-box-bg: {{ $ftrMobLogoBoxBg }};
        --ftr-mob-logo-box-border: {{ $ftrMobLogoBoxBorder }};
        --ftr-mob-brand-arrow: {{ $ftrMobBrandArrow }};
        --ftr-mob-acc-bg: {{ $ftrMobAccBg }};
        --ftr-mob-acc-border: {{ $ftrMobAccBorder }};
        --ftr-mob-acc-item-border: {{ $ftrMobAccItemBorder }};
        --ftr-mob-acc-btn-text: {{ $ftrMobAccBtnText }};
        --ftr-mob-acc-btn-icon: {{ $ftrMobAccBtnIcon }};
        --ftr-mob-acc-chevron: {{ $ftrMobAccChevron }};
        --ftr-mob-acc-chevron-active: {{ $ftrMobAccChevronActive }};
        --ftr-mob-acc-body-bg: {{ $ftrMobAccBodyBg }};
        --ftr-mob-acc-link-text: {{ $ftrMobAccLinkText }};
        --ftr-mob-acc-link-hover: {{ $ftrMobAccLinkHover }};
        --ftr-mob-pay-pill-bg: {{ $ftrMobPayPillBg }};
        --ftr-mob-pay-pill-border: {{ $ftrMobPayPillBorder }};
        --ftr-mob-pay-pill-text: {{ $ftrMobPayPillText }};
        --ftr-mob-secure-border: {{ $ftrMobSecureBorder }};
        --ftr-mob-secure-icon: {{ $ftrMobSecureIcon }};
        --ftr-mob-secure-text: {{ $ftrMobSecureText }};
    }

    .site-main-footer {
        background-color: var(--ftr-bg) !important;
        color: var(--ftr-text) !important;
        font-family: inherit;
        border-top: 1px solid var(--ftr-border) !important;
        padding-top: 48px !important;
        padding-bottom: 20px !important;
    }
    
    .site-main-footer p,
    .site-main-footer small,
    .site-main-footer span,
    .site-main-footer div {
        color: var(--ftr-text);
    }

    .site-main-footer a {
        color: var(--ftr-link-text) !important;
        text-decoration: none;
        transition: color 0.2s ease, transform 0.2s ease;
    }
    .site-main-footer a:hover {
        color: var(--ftr-link-hover-text) !important;
        transform: translateX(3px);
    }

    .pc-footer-logo-img {
        border: 1px solid var(--ftr-logo-border) !important;
        border-radius: 8px;
    }

    .pc-footer-brand-title {
        font-size: 22px;
        font-weight: 800;
        color: var(--ftr-brand-title) !important;
        letter-spacing: -0.4px;
        line-height: 1.2;
    }

    .pc-footer-brand-tagline {
        color: var(--ftr-brand-tagline) !important;
        font-size: 12px;
    }

    .pc-footer-about-text {
        color: var(--ftr-about-text) !important;
        line-height: 1.6;
        font-size: 13px;
    }

    .pc-footer-heading {
        font-size: 14.5px;
        font-weight: 700;
        color: var(--ftr-heading) !important;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 20px;
        letter-spacing: 0.2px;
    }
    .pc-footer-heading i,
    .theme-footer-icon {
        color: var(--ftr-heading-icon) !important;
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
        color: var(--ftr-link-text) !important;
    }
    .pc-footer-links li a::before {
        content: '›';
        font-size: 16px;
        color: var(--ftr-link-icon) !important;
        line-height: 1;
        transition: color 0.2s ease;
    }
    .pc-footer-links li a:hover {
        color: var(--ftr-link-hover-text) !important;
    }
    .pc-footer-links li a:hover::before {
        color: var(--ftr-link-hover-icon) !important;
    }

    .pc-contact-info-block {
        margin-top: 20px;
        padding-top: 16px;
        border-top: 1px solid var(--ftr-border) !important;
        display: flex;
        flex-direction: column;
        gap: 12px;
        font-size: 13.5px;
    }
    .pc-contact-item {
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--ftr-contact-text) !important;
    }
    .pc-contact-item i {
        color: var(--ftr-contact-icon) !important;
        font-size: 14px;
        width: 18px;
        text-align: center;
    }
    .pc-contact-item a {
        color: var(--ftr-contact-text) !important;
    }
    .pc-contact-item a:hover {
        color: var(--ftr-contact-hover) !important;
    }

    .pc-payment-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        max-width: 320px;
    }
    .pc-payment-card {
        background: var(--ftr-payment-bg) !important;
        border: 1px solid var(--ftr-payment-border) !important;
        border-radius: 10px;
        padding: 8px 12px;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.2s ease;
        min-height: 44px;
    }
    .pc-payment-card:hover {
        background: var(--ftr-payment-hover-bg) !important;
        border-color: var(--ftr-payment-hover-border) !important;
        transform: translateY(-1px);
    }
    .pc-payment-card img {
        max-height: 24px;
        max-width: 44px;
        object-fit: contain;
        border: 1px solid var(--ftr-payment-logo-border) !important;
        border-radius: 4px;
    }
    .pc-payment-card span {
        font-size: 12px;
        font-weight: 600;
        color: var(--ftr-payment-text) !important;
        line-height: 1.2;
    }

    .pc-social-circle {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: var(--ftr-social-bg) !important;
        border: 1px solid var(--ftr-social-border) !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--ftr-social-icon) !important;
        font-size: 14px;
        transition: all 0.2s ease;
    }
    .pc-social-circle:hover {
        background: var(--ftr-social-hover-bg) !important;
        border-color: var(--ftr-social-hover-border) !important;
        color: var(--ftr-social-hover-icon) !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }

    .pc-footer-bottom-bar {
        background-color: var(--ftr-bottom-bg) !important;
        border-top: 1px solid var(--ftr-bottom-border) !important;
    }
    .pc-footer-copyright-text {
        color: var(--ftr-copyright-text) !important;
    }
    .pc-footer-developer-text {
        color: var(--ftr-developer-text) !important;
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
            background: var(--ftr-mob-brand-bg) !important;
            border: 1px solid var(--ftr-mob-brand-border) !important;
            border-radius: 12px;
            margin-bottom: 16px;
        }
        .mob-footer-brand-left {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }
        .mob-footer-brand-icon {
            border-radius: 10px;
            background: var(--ftr-mob-logo-box-bg) !important;
            border: 1px solid var(--ftr-mob-logo-box-border) !important;
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
        .mob-brand-arrow-icon {
            color: var(--ftr-mob-brand-arrow) !important;
        }

        .mob-accordion-group {
            display: flex;
            flex-direction: column;
            border-radius: 12px;
            background: var(--ftr-mob-acc-bg) !important;
            border: 1px solid var(--ftr-mob-acc-border) !important;
            overflow: hidden;
            margin-bottom: 18px;
        }
        .mob-accordion-item {
            border-bottom: 1px solid var(--ftr-mob-acc-item-border) !important;
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
            color: var(--ftr-mob-acc-btn-text) !important;
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
            color: var(--ftr-mob-acc-btn-text) !important;
        }
        .mob-accordion-btn .left-title i {
            color: var(--ftr-mob-acc-btn-icon) !important;
            font-size: 14px;
            width: 16px;
        }
        .mob-accordion-btn .chevron-icon {
            font-size: 11px;
            color: var(--ftr-mob-acc-chevron) !important;
            transition: transform 0.25s ease, color 0.2s ease;
            flex-shrink: 0;
        }
        .mob-accordion-btn[aria-expanded="true"] .chevron-icon {
            transform: rotate(180deg);
            color: var(--ftr-mob-acc-chevron-active) !important;
        }
        .mob-accordion-body {
            background-color: var(--ftr-mob-acc-body-bg) !important;
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
            color: var(--ftr-mob-acc-link-text) !important;
            display: block;
            padding: 2px 0;
        }
        .mob-accordion-body ul li a:hover {
            color: var(--ftr-mob-acc-link-hover) !important;
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
            background: var(--ftr-mob-pay-pill-bg) !important;
            border: 1px solid var(--ftr-mob-pay-pill-border) !important;
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
            color: var(--ftr-mob-pay-pill-text) !important;
        }

        .mob-social-secure-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 0;
            border-top: 1px solid var(--ftr-mob-secure-border) !important;
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
            color: var(--ftr-mob-secure-text) !important;
            font-weight: 500;
            padding-left: 12px;
            border-left: 1px solid var(--ftr-mob-secure-border) !important;
        }
        .mob-social-secure-row .secure-badge i {
            color: var(--ftr-mob-secure-icon) !important;
        }
        .mob-copyright-wrapper {
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid var(--ftr-bottom-border) !important;
        }
    }
</style>

<footer class="site-main-footer theme-footer">
    
    <!-- PC / Tablet Footer Layout -->
    <div class="container d-none d-md-block px-3 px-md-4">
        <div class="row g-4 pb-4">
            
            <!-- Col 1: Brand Info & Social -->
            <div class="col-lg-3 col-md-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    @if(!empty($footerLogoUrl) && ($footerSettings['footer_show_logo'] ?? '1') == '1')
                        <img src="{{ $footerLogoUrl }}" alt="{{ $brandTitle }}" class="pc-footer-logo-img" style="max-width: {{ $logoWidth }}px; max-height: {{ $logoHeight }}px; object-fit: contain;">
                    @elseif(($footerSettings['footer_show_brand_title'] ?? '1') == '1')
                        <h4 class="pc-footer-brand-title mb-0 theme-footer-brand-title">
                            {{ $brandTitle }}
                        </h4>
                    @endif
                </div>

                @if(($footerSettings['footer_show_brand_tagline'] ?? '1') == '1' && !empty($brandTagline))
                    <div class="pc-footer-brand-tagline mb-2">{{ $brandTagline }}</div>
                @endif

                @if(!empty($footerSettings['footer_about_text']))
                    <p class="pc-footer-about-text small mb-3">
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
                            <a href="{{ $sUrl }}" class="pc-social-circle theme-social-circle" target="_blank" rel="noopener noreferrer">
                                <i class="{{ $soc['icon'] ?? 'fab fa-facebook-f' }}"></i>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Col 2: Quick Links -->
            <div class="col-lg-3 col-md-2 ps-lg-4">
                <div class="pc-footer-heading theme-footer-heading">
                    <i class="fas fa-link theme-footer-icon"></i>
                    <span>{{ $footerSettings['footer_col2_title'] ?? 'Quick Links' }}</span>
                </div>
                <ul class="pc-footer-links">
                    @foreach($quickLinks as $link)
                        <li><a href="{{ $link['url'] }}" class="theme-footer-link">{{ $link['label'] }}</a></li>
                    @endforeach
                </ul>
            </div>

            <!-- Col 3: Customer Support & Contact -->
            <div class="col-lg-3 col-md-3">
                <div class="pc-footer-heading theme-footer-heading">
                    <i class="fas fa-headset theme-footer-icon"></i>
                    <span>{{ $footerSettings['footer_col3_title'] ?? 'Customer Support' }}</span>
                </div>
                <ul class="pc-footer-links">
                    @foreach($supportLinks as $link)
                        <li><a href="{{ $link['url'] }}" class="theme-footer-link">{{ $link['label'] }}</a></li>
                    @endforeach
                </ul>

                <div class="pc-contact-info-block">
                    @if(!empty($footerSettings['footer_phone']))
                        <div class="pc-contact-item">
                            <i class="fas fa-phone-alt theme-footer-icon"></i>
                            <a href="tel:{{ $footerSettings['footer_phone'] }}" class="bn-digit theme-footer-link">{{ $footerSettings['footer_phone'] }}</a>
                        </div>
                    @endif
                    @if(!empty($footerSettings['footer_email']))
                        <div class="pc-contact-item">
                            <i class="fas fa-envelope theme-footer-icon"></i>
                            <a href="mailto:{{ $footerSettings['footer_email'] }}" class="theme-footer-link">{{ $footerSettings['footer_email'] }}</a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Col 4: Accepted Payment -->
            @if(($footerSettings['footer_show_payment_badges'] ?? '1') == '1')
                <div class="col-lg-3 col-md-3">
                    <div class="pc-footer-heading theme-footer-heading">
                        <i class="fas fa-credit-card theme-footer-icon"></i>
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

        <!-- PC Footer Bottom Copyright & Developer Row -->
        <div class="pc-footer-bottom-bar pt-3 pb-2 px-3 mt-3 d-flex justify-content-between align-items-center rounded-2" style="font-size: 12.5px;">
            <span class="pc-footer-copyright-text bn-digit">
                {{ $footerSettings['footer_copyright_text'] ?? ('© ' . $currentYear . ' ' . $brandTitle . '. All rights reserved.') }}
            </span>
            <span class="pc-footer-developer-text">
                Developed by <a href="https://srabonit.com"><strong class="pc-footer-developer-text">Srabon IT</strong></a>
            </span>
        </div>
    </div>

    <!-- Mobile Footer Layout -->
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
                        <h6 class="fw-bold mb-0" style="font-size: 15px; color: var(--ftr-brand-title) !important;">{{ $brandTitle }}</h6>
                    @endif
                    @if(($footerSettings['footer_show_brand_tagline'] ?? '1') == '1' && !empty($brandTagline))
                        <small style="font-size: 10.5px; color: var(--ftr-brand-tagline) !important;">{{ $brandTagline }}</small>
                    @endif
                </div>
            </div>
            <i class="fas fa-chevron-right small mob-brand-arrow-icon"></i>
        </a>

        <!-- Mobile Accordions -->
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
                            <li><a href="{{ $link['url'] }}" class="theme-footer-link">{{ $link['label'] }}</a></li>
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
                            <li><a href="{{ $link['url'] }}" class="theme-footer-link">{{ $link['label'] }}</a></li>
                        @endforeach
                        @if(!empty($footerSettings['footer_phone']))
                            <li class="pt-1"><a href="tel:{{ $footerSettings['footer_phone'] }}" class="theme-footer-link"><i class="fas fa-phone-alt me-2"></i> <span class="bn-digit">{{ $footerSettings['footer_phone'] }}</span></a></li>
                        @endif
                        @if(!empty($footerSettings['footer_email']))
                            <li><a href="mailto:{{ $footerSettings['footer_email'] }}" class="theme-footer-link"><i class="fas fa-envelope me-2"></i> {{ $footerSettings['footer_email'] }}</a></li>
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
                        <li><a href="{{ url('/about-us') }}" class="theme-footer-link">About Us</a></li>
                        <li><a href="{{ url('/contact-us') }}" class="theme-footer-link">Contact Us</a></li>
                        <li><a href="{{ url('/blogs') }}" class="theme-footer-link">Latest News</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Mobile Payment Badges -->
        @if(($footerSettings['footer_show_payment_badges'] ?? '1') == '1')
            <div class="fw-bold mb-2 small" style="font-size: 13px; color: var(--ftr-heading) !important;">
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

        <!-- Mobile Social & Secure Checkout Row -->
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
                        <a href="{{ $sUrl }}" class="pc-social-circle theme-social-circle" style="width: 34px; height: 34px; font-size: 13px;" target="_blank" rel="noopener noreferrer">
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

        <!-- Mobile Bottom Copyright & Developer -->
        <div class="text-center mob-copyright-wrapper d-flex flex-column align-items-center gap-1" style="font-size: 11.5px;">
            <div class="pc-footer-copyright-text bn-digit">
                {{ $footerSettings['footer_copyright_text'] ?? ('© ' . $currentYear . ' ' . $brandTitle . '. All rights reserved.') }}
            </div>
            <div class="pc-footer-developer-text">
                Developed by <strong>Srabon IT</strong>
            </div>
        </div>

    </div>

</footer>