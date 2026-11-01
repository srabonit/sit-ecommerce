@extends('layouts.admin')

@section('title', 'Extended Module Colors - ' . config('app.name'))

@section('admin_content')
    <style>
        /* Strict 5px Element Radius Across All Controls */
        #extendedColorsRoot .btn,
        #extendedColorsRoot .btn-sm,
        #extendedColorsRoot button,
        #extendedColorsRoot .form-control,
        #extendedColorsRoot .badge,
        #extendedColorsRoot .card,
        #extendedColorsRoot .theme-header-card,
        #extendedColorsRoot .theme-section-card,
        #extendedColorsRoot .color-control-wrapper,
        #extendedColorsRoot .color-input-container,
        #extendedColorsRoot .collapse-toggle-btn,
        #extendedColorsRoot .bottom-action-card,
        #extendedColorsRoot .top-action-bar,
        .colors-modal-scope .modal-content,
        .colors-modal-scope .btn,
        .colors-modal-scope .badge {
            border-radius: 5px !important;
        }

        .bn-digit,
        .font-monospace {
            font-weight: 700 !important;
        }

        .btn-compact {
            font-size: 11.5px !important;
            padding: 5px 14px !important;
            line-height: 1.3 !important;
            font-weight: 600 !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            transition: all 0.15s ease;
        }

        .theme-header-card {
            background: #03172e;
            color: #ffffff;
            padding: 14px 18px;
            border: 1px solid #1e293b;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
            margin-bottom: 12px;
        }

        .top-action-bar {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            padding: 10px 14px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
            margin-bottom: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }

        .theme-section-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
            margin-bottom: 14px;
            overflow: hidden;
            transition: box-shadow 0.2s ease, border-color 0.2s ease;
        }

        .theme-section-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
        }

        .theme-section-header {
            padding: 12px 16px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            cursor: pointer;
            user-select: none;
        }

        .section-title-text {
            font-size: 13.5px;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sub-group-header {
            background: #f1f5f9;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 700;
            color: #334155;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 10px;
            margin-bottom: 8px;
        }

        .collapse-toggle-btn {
            width: 26px;
            height: 26px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            color: #475569;
            transition: all 0.2s ease;
        }

        .collapse-toggle-btn:hover {
            background-color: #2563eb;
            border-color: #2563eb;
            color: #ffffff;
        }

        .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
            transform: rotate(180deg);
        }

        .collapse-icon {
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            font-size: 11px;
        }

        .color-control-wrapper {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 8px 10px;
            transition: all 0.15s ease;
            height: 100%;
            border-radius: 4px;
        }

        .color-control-wrapper:hover {
            background: #ffffff;
            border-color: #cbd5e1;
        }

        .color-control-wrapper label {
            font-size: 11px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 4px;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .color-input-container {
            display: flex;
            align-items: center;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            overflow: hidden;
            height: 30px;
            transition: all 0.15s ease;
            border-radius: 4px;
        }

        .color-input-container:focus-within {
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12);
        }

        .color-picker-widget {
            width: 32px;
            height: 30px;
            border: none;
            padding: 2px;
            background: transparent;
            cursor: pointer;
            flex-shrink: 0;
        }

        .color-picker-widget::-webkit-color-swatch-wrapper {
            padding: 0;
        }

        .color-picker-widget::-webkit-color-swatch {
            border: none;
            border-radius: 3px;
        }

        .color-hex-input {
            border: none;
            border-left: 1px solid #e2e8f0;
            outline: none;
            width: 100%;
            padding: 4px 8px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 11.5px;
            color: #0f172a;
            background: transparent;
            text-transform: lowercase;
            font-weight: 600;
        }

        .section-footer-box {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 10px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }

        .bottom-action-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            padding: 12px 16px;
            box-shadow: 0 -2px 10px rgba(15, 23, 42, 0.03);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }

        @media (max-width: 767.98px) {
            .theme-header-card {
                padding: 12px 14px !important;
            }

            .header-top-wrap,
            .top-action-bar,
            .bottom-action-card,
            .section-footer-box {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 8px !important;
            }

            .top-action-bar .btn-compact,
            .bottom-action-card .btn-compact,
            .section-footer-box .btn-compact {
                width: 100% !important;
                justify-content: center !important;
                padding: 8px 14px !important;
            }
        }
    </style>

    <div class="container-fluid px-2 px-md-4 py-2" id="extendedColorsRoot">

        <!-- Header Card -->
        <div class="theme-header-card d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 header-top-wrap">
            <div>
            
                <h5 class="fw-bold mb-0 d-flex align-items-center gap-2" style="font-size: 17px;">
                    <i class="fas fa-swatchbook text-warning"></i>
                    <span>Advance Colors</span>
                </h5>
                <small class="text-white-50 d-block mt-1" style="font-size: 11.5px; line-height: 1.35;">
                    Customize colors for dashboard, emails, cart, checkout, receipts & hovers.
                </small>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-xs py-2 px-3 mb-2 small d-flex align-items-center justify-content-between" style="border-radius: 5px;">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-check-circle text-success"></i>
                    <span class="fw-semibold text-dark">{{ session('success') }}</span>
                </div>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('admin.theme.setting.extended_colors.update') }}" method="POST" id="extendedColorsForm">
            @csrf

            <!-- TOP ACTION BAR -->
            <div class="top-action-bar">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-danger btn-compact shadow-xs fw-bold px-3" onclick="triggerExtModal('allExtendedResetModal')">
                        <i class="fas fa-trash-alt me-1"></i>
                        <span>Reset All Extended Colors</span>
                    </button>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-4" style="min-height: 33px;">
                        <i class="fas fa-check-circle me-1"></i>
                        <span>Save Extended Color Changes</span>
                    </button>
                </div>
            </div>

            <div class="row g-2 g-md-3 mb-2">

                <!-- ========================================================================= -->
                <!-- SECTION 1: USER DASHBOARD SUITE COLORS                                    -->
                <!-- ========================================================================= -->
                <div class="col-12">
                    <div class="theme-section-card">
                        <div class="theme-section-header d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#collapseExtUserDashboard" aria-expanded="true">
                            <div class="section-title-text">
                                <i class="fas fa-user-circle text-primary"></i>
                                <span>1. User Dashboard Suite Colors</span>
                                <span class="badge bg-secondary-subtle text-secondary small fw-normal ms-2" style="font-size: 10px;">Dashboard, Orders, Addresses, Wishlist, Coupons, Profile &amp; Support</span>
                            </div>
                            <button type="button" class="collapse-toggle-btn shadow-xs" aria-expanded="true">
                                <i class="fas fa-chevron-down collapse-icon"></i>
                            </button>
                        </div>

                        <div class="collapse show" id="collapseExtUserDashboard">
                            <div class="p-3">
                                <div class="sub-group-header">
                                    <i class="fas fa-columns text-primary"></i>
                                    <span>1. Page Canvas &amp; User Sidebar Navigation</span>
                                </div>
                                <div class="row g-2 mb-3">
                                    @php
                                        $userNavFields = [
                                            'color_user_page_bg'             => 'Dashboard Page BG',
                                            'color_user_sidebar_bg'          => 'Sidebar Box BG',
                                            'color_user_sidebar_border'      => 'Sidebar Border',
                                            'color_user_sidebar_text'        => 'Nav Link Text',
                                            'color_user_sidebar_hover_bg'    => 'Nav Link Hover BG',
                                            'color_user_sidebar_hover_text'  => 'Nav Link Hover Text',
                                            'color_user_sidebar_active_bg'   => 'Nav Link Active BG',
                                            'color_user_sidebar_active_text' => 'Nav Link Active Text',
                                        ];
                                    @endphp
                                    @foreach($userNavFields as $k => $lbl)
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>{{ $lbl }}</label>
                                                <div class="color-input-container">
                                                    @php $v = $colors[$k] ?? '#ffffff'; @endphp
                                                    <input type="color" class="color-picker-widget" value="{{ str_starts_with($v, '#') ? $v : '#ffffff' }}">
                                                    <input type="text" name="{{ $k }}" class="color-hex-input" value="{{ $v }}">
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="sub-group-header">
                                    <i class="fas fa-chart-pie text-success"></i>
                                    <span>2. Content Cards, Overview Counters &amp; Stats</span>
                                </div>
                                <div class="row g-2 mb-3">
                                    @php
                                        $userCardFields = [
                                            'color_user_card_bg'          => 'Card Container BG',
                                            'color_user_card_border'      => 'Card Border',
                                            'color_user_card_title'       => 'Card Heading Title',
                                            'color_user_card_text'        => 'Card Text / Subtitle',
                                            'color_user_stat_card_bg'     => 'Stat Card BG',
                                            'color_user_stat_card_border' => 'Stat Card Border',
                                            'color_user_stat_number'      => 'Stat Metric Value',
                                            'color_user_stat_label'       => 'Stat Label Text',
                                            'color_user_stat_icon'        => 'Stat Icon Color',
                                        ];
                                    @endphp
                                    @foreach($userCardFields as $k => $lbl)
                                        <div class="col-6 col-md-4">
                                            <div class="color-control-wrapper">
                                                <label>{{ $lbl }}</label>
                                                <div class="color-input-container">
                                                    @php $v = $colors[$k] ?? '#ffffff'; @endphp
                                                    <input type="color" class="color-picker-widget" value="{{ str_starts_with($v, '#') ? $v : '#ffffff' }}">
                                                    <input type="text" name="{{ $k }}" class="color-hex-input" value="{{ $v }}">
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="sub-group-header">
                                    <i class="fas fa-table text-warning"></i>
                                    <span>3. Data Tables, Order List &amp; Status Badges</span>
                                </div>
                                <div class="row g-2 mb-3">
                                    @php
                                        $userTableFields = [
                                            'color_user_table_header_bg'      => 'Table Header BG',
                                            'color_user_table_header_text'    => 'Table Header Text',
                                            'color_user_table_border'         => 'Table Row Border',
                                            'color_user_table_text'           => 'Table Cell Data Text',
                                            'color_user_badge_pending_bg'     => 'Pending Badge BG',
                                            'color_user_badge_pending_text'   => 'Pending Badge Text',
                                            'color_user_badge_completed_bg'   => 'Completed Badge BG',
                                            'color_user_badge_completed_text' => 'Completed Badge Text',
                                            'color_user_badge_cancelled_bg'   => 'Cancelled Badge BG',
                                            'color_user_badge_cancelled_text' => 'Cancelled Badge Text',
                                        ];
                                    @endphp
                                    @foreach($userTableFields as $k => $lbl)
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>{{ $lbl }}</label>
                                                <div class="color-input-container">
                                                    @php $v = $colors[$k] ?? '#ffffff'; @endphp
                                                    <input type="color" class="color-picker-widget" value="{{ str_starts_with($v, '#') ? $v : '#ffffff' }}">
                                                    <input type="text" name="{{ $k }}" class="color-hex-input" value="{{ $v }}">
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="sub-group-header">
                                    <i class="fas fa-edit text-info"></i>
                                    <span>4. Profile, Support &amp; Order Tracker Action Buttons &amp; Form Fields</span>
                                </div>
                                <div class="row g-2 mb-2">
                                    @php
                                        $userActionFields = [
                                            'color_user_btn_primary_bg'     => 'Primary Action Btn BG',
                                            'color_user_btn_primary_text'   => 'Primary Action Btn Text',
                                            'color_user_btn_primary_hover'  => 'Primary Action Hover',
                                            'color_user_input_bg'           => 'Form Input BG',
                                            'color_user_input_border'       => 'Form Input Border',
                                            'color_user_input_focus_border' => 'Input Focus Border',
                                        ];
                                    @endphp
                                    @foreach($userActionFields as $k => $lbl)
                                        <div class="col-6 col-md-4">
                                            <div class="color-control-wrapper">
                                                <label>{{ $lbl }}</label>
                                                <div class="color-input-container">
                                                    @php $v = $colors[$k] ?? '#ffffff'; @endphp
                                                    <input type="color" class="color-picker-widget" value="{{ str_starts_with($v, '#') ? $v : '#ffffff' }}">
                                                    <input type="text" name="{{ $k }}" class="color-hex-input" value="{{ $v }}">
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="section-footer-box">
                                <span class="small text-muted"><i class="fas fa-info-circle me-1"></i> Restore all User Dashboard suite colors to system defaults.</span>
                                <button type="button" class="btn btn-outline-warning btn-compact shadow-xs fw-bold px-3 text-dark border-warning" onclick="triggerExtModal('userDashboardResetModal')">
                                    <i class="fas fa-rotate-left me-1 text-warning"></i> Reset Dashboard Colors
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- SECTION 2: EMAIL TEMPLATES & UNSUBSCRIBE PREFERENCES                       -->
                <!-- ========================================================================= -->
                <div class="col-12">
                    <div class="theme-section-card">
                        <div class="theme-section-header d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#collapseExtEmails" aria-expanded="false">
                            <div class="section-title-text">
                                <i class="fas fa-envelope-open-text text-danger"></i>
                                <span>2. Email Templates, Notifications &amp; Unsubscribe Preferences</span>
                                <span class="badge bg-secondary-subtle text-secondary small fw-normal ms-2" style="font-size: 10px;">Order Placed, Digital Delivery, Dynamic Forms &amp; Unsubscribe Pages</span>
                            </div>
                            <button type="button" class="collapse-toggle-btn shadow-xs" aria-expanded="false">
                                <i class="fas fa-chevron-down collapse-icon"></i>
                            </button>
                        </div>

                        <div class="collapse" id="collapseExtEmails">
                            <div class="p-3">
                                <div class="sub-group-header">
                                    <i class="fas fa-envelope text-primary"></i>
                                    <span>1. Email Invoices &amp; Digital Product Access Notifications</span>
                                </div>
                                <div class="row g-2 mb-3">
                                    @php
                                        $emailCoreFields = [
                                            'color_email_site_bg'       => 'Email Canvas BG',
                                            'color_email_card_bg'       => 'Email Card Container BG',
                                            'color_email_card_border'   => 'Email Card Border',
                                            'color_email_header_bg'     => 'Email Header Banner BG',
                                            'color_email_header_text'   => 'Header Text Color',
                                            'color_email_title'         => 'Email Content Heading',
                                            'color_email_text'          => 'Email Body Text',
                                            'color_email_price'         => 'Order Grand Total Price',
                                            'color_email_btn_bg'        => 'Action Button BG',
                                            'color_email_btn_text'      => 'Action Button Text',
                                            'color_email_btn_hover'     => 'Action Button Hover BG',
                                            'color_email_badge_bg'      => 'Item Variant Badge BG',
                                            'color_email_badge_text'    => 'Variant Badge Text',
                                            'color_email_table_th_bg'   => 'Order Table Header BG',
                                            'color_email_table_th_text' => 'Table Header Text',
                                            'color_email_table_border'  => 'Order Table Border',
                                            'color_email_footer_bg'     => 'Email Footer BG',
                                            'color_email_footer_text'   => 'Email Footer Text',
                                        ];
                                    @endphp
                                    @foreach($emailCoreFields as $k => $lbl)
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>{{ $lbl }}</label>
                                                <div class="color-input-container">
                                                    @php $v = $colors[$k] ?? '#ffffff'; @endphp
                                                    <input type="color" class="color-picker-widget" value="{{ str_starts_with($v, '#') ? $v : '#ffffff' }}">
                                                    <input type="text" name="{{ $k }}" class="color-hex-input" value="{{ $v }}">
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="sub-group-header">
                                    <i class="fas fa-user-minus text-warning"></i>
                                    <span>2. Newsletter Unsubscribe &amp; Preferences Page</span>
                                </div>
                                <div class="row g-2 mb-3">
                                    @php
                                        $unsubFields = [
                                            'color_unsub_page_bg'            => 'Unsubscribe Page BG',
                                            'color_unsub_card_bg'            => 'Preferences Card BG',
                                            'color_unsub_card_border'        => 'Preferences Card Border',
                                            'color_unsub_title'              => 'Card Heading Title',
                                            'color_unsub_text'               => 'Muted Description Text',
                                            'color_unsub_input_bg'           => 'Form Select Option BG',
                                            'color_unsub_input_border'       => 'Form Option Border',
                                            'color_unsub_input_focus_border' => 'Option Focus Border',
                                            'color_unsub_btn_bg'             => 'Unsubscribe Confirm BG',
                                            'color_unsub_btn_hover'          => 'Confirm Button Hover',
                                            'color_unsub_btn_text'           => 'Confirm Button Text',
                                            'color_unsub_cancel_btn_bg'      => 'Keep Subscription BG',
                                            'color_unsub_cancel_btn_text'    => 'Keep Subscription Text',
                                        ];
                                    @endphp
                                    @foreach($unsubFields as $k => $lbl)
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>{{ $lbl }}</label>
                                                <div class="color-input-container">
                                                    @php $v = $colors[$k] ?? '#ffffff'; @endphp
                                                    <input type="color" class="color-picker-widget" value="{{ str_starts_with($v, '#') ? $v : '#ffffff' }}">
                                                    <input type="text" name="{{ $k }}" class="color-hex-input" value="{{ $v }}">
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="sub-group-header">
                                    <i class="fas fa-file-invoice text-success"></i>
                                    <span>3. Dynamic Form Submissions &amp; Lead Email Alerts</span>
                                </div>
                                <div class="row g-2 mb-2">
                                    @php
                                        $formEmailFields = [
                                            'color_form_mail_header_bg' => 'Form Email Header BG',
                                            'color_form_mail_primary'   => 'Form Primary Action Color',
                                            'color_form_mail_btn_hover' => 'Submissions Ledger Hover',
                                        ];
                                    @endphp
                                    @foreach($formEmailFields as $k => $lbl)
                                        <div class="col-6 col-md-4">
                                            <div class="color-control-wrapper">
                                                <label>{{ $lbl }}</label>
                                                <div class="color-input-container">
                                                    @php $v = $colors[$k] ?? '#ffffff'; @endphp
                                                    <input type="color" class="color-picker-widget" value="{{ str_starts_with($v, '#') ? $v : '#ffffff' }}">
                                                    <input type="text" name="{{ $k }}" class="color-hex-input" value="{{ $v }}">
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="section-footer-box">
                                <span class="small text-muted"><i class="fas fa-info-circle me-1"></i> Restore all email and unsubscribe page colors to system defaults.</span>
                                <button type="button" class="btn btn-outline-warning btn-compact shadow-xs fw-bold px-3 text-dark border-warning" onclick="triggerExtModal('emailsResetModal')">
                                    <i class="fas fa-rotate-left me-1 text-warning"></i> Reset Email Colors
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- SECTION 3: SHOPPING CART & CART DRAWER COLORS                             -->
                <!-- ========================================================================= -->
                <div class="col-12">
                    <div class="theme-section-card">
                        <div class="theme-section-header d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#collapseExtCart" aria-expanded="false">
                            <div class="section-title-text">
                                <i class="fas fa-shopping-basket text-primary"></i>
                                <span>3. Shopping Cart &amp; Cart Drawer Colors</span>
                                <span class="badge bg-secondary-subtle text-secondary small fw-normal ms-2" style="font-size: 10px;">cart.blade, cart_drawer_body &amp; cart_drawer_items</span>
                            </div>
                            <button type="button" class="collapse-toggle-btn shadow-xs" aria-expanded="false">
                                <i class="fas fa-chevron-down collapse-icon"></i>
                            </button>
                        </div>

                        <div class="collapse" id="collapseExtCart">
                            <div class="p-3">
                                <div class="sub-group-header">
                                    <i class="fas fa-layer-group text-primary"></i>
                                    <span>1. Cart Page Layout, Item Cards &amp; Product Thumbnails</span>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Cart Page BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_cart_page_bg'] ?? '#f8fafc'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_cart_page_bg" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Summary Panel BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_cart_panel_bg'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_cart_panel_bg" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Summary Panel Border</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_cart_panel_border'] ?? '#edf2f7'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_cart_panel_border" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Item Card Box BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_cart_item_bg'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_cart_item_bg" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Item Border (Normal)</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_cart_item_border'] ?? '#edf2f7'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_cart_item_border" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Item Border (Hover)</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_cart_item_border_hover'] ?? '#cbd5e1'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_cart_item_border_hover" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Product Image Box BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_cart_item_img_bg'] ?? '#f8fafc'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_cart_item_img_bg" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Product Image Border</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_cart_item_img_border'] ?? '#e2e8f0'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_cart_item_img_border" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="sub-group-header">
                                    <i class="fas fa-sliders-h text-success"></i>
                                    <span>2. Typography, Variants, Stepper Buttons &amp; Checkout Actions</span>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Product Title Color</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_cart_item_title'] ?? '#0f172a'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_cart_item_title" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Product Title Hover</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_cart_item_title_hover'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_cart_item_title_hover" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Item Price Text</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_cart_item_price'] ?? '#0f172a'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_cart_item_price" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>SKU Text Color</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_cart_sku_text'] ?? '#94a3b8'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_cart_sku_text" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Variant Badge BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_cart_variant_bg'] ?? '#f8fafc'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_cart_variant_bg" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Variant Badge Text</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_cart_variant_text'] ?? '#64748b'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_cart_variant_text" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Variant Badge Border</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_cart_variant_border'] ?? '#e2e8f0'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_cart_variant_border" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Qty Stepper BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_cart_qty_btn_bg'] ?? '#f8fafc'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_cart_qty_btn_bg" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Qty Stepper Text</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_cart_qty_btn_text'] ?? '#334155'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_cart_qty_btn_text" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Qty Stepper Hover BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_cart_qty_btn_hover_bg'] ?? '#e2e8f0'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_cart_qty_btn_hover_bg" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Qty Stepper Hover Text</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_cart_qty_btn_hover_text'] ?? '#0f172a'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_cart_qty_btn_hover_text" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Remove Item Icon</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_cart_remove_btn'] ?? '#ef4444'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_cart_remove_btn" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Remove Icon Hover</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_cart_remove_btn_hover'] ?? '#dc2626'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_cart_remove_btn_hover" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Checkout Button BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_cart_checkout_btn_bg'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_cart_checkout_btn_bg" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Checkout Button Hover BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_cart_checkout_btn_hover'] ?? '#1d4ed8'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_cart_checkout_btn_hover" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="section-footer-box">
                                <span class="small text-muted"><i class="fas fa-info-circle me-1"></i> Restore cart drawer and main cart page colors to default.</span>
                                <button type="button" class="btn btn-outline-warning btn-compact shadow-xs fw-bold px-3 text-dark border-warning" onclick="triggerExtModal('cartResetModal')">
                                    <i class="fas fa-rotate-left me-1 text-warning"></i> Reset Cart Colors
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- SECTION 4: CHECKOUT PAGE COLORS                                           -->
                <!-- ========================================================================= -->
                <div class="col-12">
                    <div class="theme-section-card">
                        <div class="theme-section-header d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#collapseExtCheckout" aria-expanded="false">
                            <div class="section-title-text">
                                <i class="fas fa-credit-card text-success"></i>
                                <span>4. Checkout Page Colors</span>
                                <span class="badge bg-secondary-subtle text-secondary small fw-normal ms-2" style="font-size: 10px;">checkout.blade.php</span>
                            </div>
                            <button type="button" class="collapse-toggle-btn shadow-xs" aria-expanded="false">
                                <i class="fas fa-chevron-down collapse-icon"></i>
                            </button>
                        </div>

                        <div class="collapse" id="collapseExtCheckout">
                            <div class="p-3">
                                <div class="row g-2 mb-2">
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Checkout Page BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_checkout_page_bg'] ?? '#f8fafc'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_checkout_page_bg" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Card Box BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_checkout_card_bg'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_checkout_card_bg" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Card Box Border</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_checkout_card_border'] ?? '#edf2f7'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_checkout_card_border" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Heading Text Color</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_checkout_heading'] ?? '#0f172a'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_checkout_heading" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Input Field BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_checkout_input_bg'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_checkout_input_bg" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Input Field Border</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_checkout_input_border'] ?? '#cbd5e1'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_checkout_input_border" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Input Focus Border</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_checkout_input_focus_border'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_checkout_input_focus_border" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Grand Total Price</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_checkout_total_price'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_checkout_total_price" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Payment Card Normal BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_checkout_paybox_bg'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_checkout_paybox_bg" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Payment Card Normal Border</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_checkout_paybox_border'] ?? '#e2e8f0'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_checkout_paybox_border" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Active Payment Card BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_checkout_paybox_active_bg'] ?? '#eff6ff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_checkout_paybox_active_bg" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Active Payment Card Border</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_checkout_paybox_active_border'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_checkout_paybox_active_border" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Place Order Button BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_checkout_btn_bg'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_checkout_btn_bg" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Place Order Button Text</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_checkout_btn_text'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_checkout_btn_text" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Place Order Hover BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_checkout_btn_hover'] ?? '#1d4ed8'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_checkout_btn_hover" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="section-footer-box">
                                <span class="small text-muted"><i class="fas fa-info-circle me-1"></i> Restore checkout page colors to default.</span>
                                <button type="button" class="btn btn-outline-warning btn-compact shadow-xs fw-bold px-3 text-dark border-warning" onclick="triggerExtModal('checkoutResetModal')">
                                    <i class="fas fa-rotate-left me-1 text-warning"></i> Reset Checkout Colors
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- SECTION 5: ORDER CONFIRMATION & THERMAL RECEIPT COLORS                    -->
                <!-- ========================================================================= -->
                <div class="col-12">
                    <div class="theme-section-card">
                        <div class="theme-section-header d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#collapseExtToken" aria-expanded="false">
                            <div class="section-title-text">
                                <i class="fas fa-receipt text-info"></i>
                                <span>5. Order Confirmation &amp; Thermal Receipt Colors</span>
                                <span class="badge bg-secondary-subtle text-secondary small fw-normal ms-2" style="font-size: 10px;">order-confirmation.blade.php</span>
                            </div>
                            <button type="button" class="collapse-toggle-btn shadow-xs" aria-expanded="false">
                                <i class="fas fa-chevron-down collapse-icon"></i>
                            </button>
                        </div>

                        <div class="collapse" id="collapseExtToken">
                            <div class="p-3">
                                <div class="row g-2 mb-2">
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Token Page BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_token_page_bg'] ?? '#f1f5f9'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_token_page_bg" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Success Icon BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_token_success_icon_bg'] ?? '#16a34a'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_token_success_icon_bg" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Heading Text Color</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_token_heading'] ?? '#0f172a'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_token_heading" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Total Price Text</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_token_total_price'] ?? '#0f172a'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_token_total_price" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Download Memo Btn BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_token_download_btn_bg'] ?? '#0f172a'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_token_download_btn_bg" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Download Memo Text</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_token_download_btn_text'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_token_download_btn_text" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Download Memo Hover BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_token_download_btn_hover'] ?? '#1e293b'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_token_download_btn_hover" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Continue Shopping Hover BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_token_continue_btn_hover'] ?? '#f8fafc'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_token_continue_btn_hover" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Thermal Slip BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_token_memo_bg'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_token_memo_bg" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Thermal Slip Border</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_token_memo_border'] ?? '#94a3b8'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_token_memo_border" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Digital Delivery Box BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_token_digital_box_bg'] ?? '#f8fafc'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_token_digital_box_bg" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Digital Box Border</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_token_digital_box_border'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_token_digital_box_border" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="section-footer-box">
                                <span class="small text-muted"><i class="fas fa-info-circle me-1"></i> Restore order token and thermal slip colors to default.</span>
                                <button type="button" class="btn btn-outline-warning btn-compact shadow-xs fw-bold px-3 text-dark border-warning" onclick="triggerExtModal('tokenResetModal')">
                                    <i class="fas fa-rotate-left me-1 text-warning"></i> Reset Token Colors
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- SECTION 6: UI COMPONENTS (CATEGORY CARD, SECTION HEADER, WIDGET AREA)     -->
                <!-- ========================================================================= -->
                <div class="col-12">
                    <div class="theme-section-card">
                        <div class="theme-section-header d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#collapseExtComponents" aria-expanded="false">
                            <div class="section-title-text">
                                <i class="fas fa-cubes text-secondary"></i>
                                <span>6. Category Card, Section Header &amp; Widget Area Components</span>
                                <span class="badge bg-secondary-subtle text-secondary small fw-normal ms-2" style="font-size: 10px;">category-card, section-header &amp; widget-area</span>
                            </div>
                            <button type="button" class="collapse-toggle-btn shadow-xs" aria-expanded="false">
                                <i class="fas fa-chevron-down collapse-icon"></i>
                            </button>
                        </div>

                        <div class="collapse" id="collapseExtComponents">
                            <div class="p-3">
                                <div class="row g-2 mb-2">
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Category Card BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_cat_card_bg'] ?? '#f8fafc'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_cat_card_bg" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Category Card Border</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_cat_card_border'] ?? '#edf2f7'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_cat_card_border" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Category Border Hover</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_cat_card_border_hover'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_cat_card_border_hover" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Category Title Color</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_cat_card_title'] ?? '#0f172a'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_cat_card_title" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Category Title Hover</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_cat_card_title_hover'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_cat_card_title_hover" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Section Heading Title</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_section_title'] ?? '#0f172a'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_section_title" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Section View All Link</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_section_link'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_section_link" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Section View All Hover</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_section_link_hover'] ?? '#1d4ed8'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_section_link_hover" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="section-footer-box">
                                <span class="small text-muted"><i class="fas fa-info-circle me-1"></i> Restore category card, section header &amp; widget colors to default.</span>
                                <button type="button" class="btn btn-outline-warning btn-compact shadow-xs fw-bold px-3 text-dark border-warning" onclick="triggerExtModal('componentsResetModal')">
                                    <i class="fas fa-rotate-left me-1 text-warning"></i> Reset Component Colors
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- SECTION 7: AUTHENTICATION & ERROR PAGES                                   -->
                <!-- ========================================================================= -->
                <div class="col-12">
                    <div class="theme-section-card">
                        <div class="theme-section-header d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#collapseExtAuth" aria-expanded="false">
                            <div class="section-title-text">
                                <i class="fas fa-user-lock text-danger"></i>
                                <span>7. Authentication &amp; Error Pages Colors</span>
                                <span class="badge bg-secondary-subtle text-secondary small fw-normal ms-2" style="font-size: 10px;">login, register, reset, 404, 429, 500 &amp; minimal</span>
                            </div>
                            <button type="button" class="collapse-toggle-btn shadow-xs" aria-expanded="false">
                                <i class="fas fa-chevron-down collapse-icon"></i>
                            </button>
                        </div>

                        <div class="collapse" id="collapseExtAuth">
                            <div class="p-3">
                                <div class="row g-2 mb-2">
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Auth Page BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_auth_page_bg'] ?? '#f8fafc'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_auth_page_bg" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Auth &amp; Error Card BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_auth_card_bg'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_auth_card_bg" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Card Border Color</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_auth_card_border'] ?? '#edf2f7'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_auth_card_border" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Heading Text Color</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_auth_heading'] ?? '#0f172a'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_auth_heading" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Subtitle / Muted Text</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_auth_text'] ?? '#64748b'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_auth_text" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Form Input Focus Border</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_auth_input_focus_border'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_auth_input_focus_border" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Submit / Home Button BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_auth_btn_bg'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_auth_btn_bg" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Submit / Home Btn Hover BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_auth_btn_hover'] ?? '#1d4ed8'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_auth_btn_hover" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Auth Link Color</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_auth_link'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_auth_link" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Auth Link Hover Color</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_auth_link_hover'] ?? '#1d4ed8'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_auth_link_hover" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Error Page Common Btn BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_common_btn_bg'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_common_btn_bg" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="section-footer-box">
                                <span class="small text-muted"><i class="fas fa-info-circle me-1"></i> Restore authentication and error page colors to default.</span>
                                <button type="button" class="btn btn-outline-warning btn-compact shadow-xs fw-bold px-3 text-dark border-warning" onclick="triggerExtModal('authResetModal')">
                                    <i class="fas fa-rotate-left me-1 text-warning"></i> Reset Auth &amp; Error Colors
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- SECTION 8: PRODUCT DETAILS, WHATSAPP & FOOTER SOCIAL HOVER FIXES          -->
                <!-- ========================================================================= -->
                <div class="col-12">
                    <div class="theme-section-card">
                        <div class="theme-section-header d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#collapseExtHovers" aria-expanded="false">
                            <div class="section-title-text">
                                <i class="fas fa-hand-pointer text-warning"></i>
                                <span>8. Product Details, Quick View, WhatsApp &amp; Footer Social Hover Controls</span>
                                <span class="badge bg-secondary-subtle text-secondary small fw-normal ms-2" style="font-size: 10px;">Buy Now, Add to Cart, WhatsApp Brand Green &amp; Social Icons</span>
                            </div>
                            <button type="button" class="collapse-toggle-btn shadow-xs" aria-expanded="false">
                                <i class="fas fa-chevron-down collapse-icon"></i>
                            </button>
                        </div>

                        <div class="collapse" id="collapseExtHovers">
                            <div class="p-3">
                                <div class="sub-group-header">
                                    <i class="fab fa-whatsapp text-success"></i>
                                    <span>1. Product Details &amp; Quick View - Buy Now, Cart &amp; WhatsApp Normal/Hover</span>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Buy Now Normal BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_pdp_buynow_bg'] ?? '#0dcaf0'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_pdp_buynow_bg" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Buy Now Normal Text</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_pdp_buynow_text'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_pdp_buynow_text" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Buy Now Hover BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_pdp_buynow_hover_bg'] ?? '#0bb5d8'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_pdp_buynow_hover_bg" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Buy Now Hover Text</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_pdp_buynow_hover_text'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_pdp_buynow_hover_text" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Add to Cart Normal BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_pdp_cart_bg'] ?? '#ffc107'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_pdp_cart_bg" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Add to Cart Normal Text</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_pdp_cart_text'] ?? '#212529'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_pdp_cart_text" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Add to Cart Hover BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_pdp_cart_hover_bg'] ?? '#e0a800'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_pdp_cart_hover_bg" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Add to Cart Hover Text</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_pdp_cart_hover_text'] ?? '#212529'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_pdp_cart_hover_text" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>WhatsApp Normal BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_pdp_wa_bg'] ?? '#25D366'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_pdp_wa_bg" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>WhatsApp Normal Text</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_pdp_wa_text'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_pdp_wa_text" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>WhatsApp Hover BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_pdp_wa_hover_bg'] ?? '#128C7E'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_pdp_wa_hover_bg" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>WhatsApp Hover Text</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_pdp_wa_hover_text'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_pdp_wa_hover_text" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="sub-group-header">
                                    <i class="fas fa-share-alt text-info"></i>
                                    <span>2. Footer Social Icons (Normal &amp; Hover Controls)</span>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Social Icon Normal Color</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_ftr_social_icon'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_ftr_social_icon" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Social Circle Normal BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_ftr_social_bg'] ?? '#1e293b'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ str_starts_with($v, '#') ? $v : '#1e293b' }}">
                                                <input type="text" name="color_ftr_social_bg" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Social Circle Normal Border</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_ftr_social_border'] ?? '#334155'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ str_starts_with($v, '#') ? $v : '#334155' }}">
                                                <input type="text" name="color_ftr_social_border" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Social Hover Icon Color</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_ftr_social_hover_icon'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_ftr_social_hover_icon" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Social Hover Circle BG</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_ftr_social_hover_bg'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_ftr_social_hover_bg" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Social Hover Circle Border</label>
                                            <div class="color-input-container">
                                                @php $v = $colors['color_ftr_social_hover_border'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $v }}">
                                                <input type="text" name="color_ftr_social_hover_border" class="color-hex-input" value="{{ $v }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="section-footer-box">
                                <span class="small text-muted"><i class="fas fa-info-circle me-1"></i> Restore product action button hovers, WhatsApp and social hover colors to default.</span>
                                <button type="button" class="btn btn-outline-warning btn-compact shadow-xs fw-bold px-3 text-dark border-warning" onclick="triggerExtModal('hoversResetModal')">
                                    <i class="fas fa-rotate-left me-1 text-warning"></i> Reset Button &amp; Social Hovers
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- BOTTOM ACTION BAR -->
            <div class="bottom-action-card mb-3">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-danger btn-compact shadow-xs fw-bold px-3" onclick="triggerExtModal('allExtendedResetModal')">
                        <i class="fas fa-trash-alt me-1"></i>
                        <span>Reset All Extended Colors</span>
                    </button>
                </div>
                <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-4" style="min-height: 35px; font-size: 12px;">
                    <i class="fas fa-check-circle me-1"></i>
                    <span>Save Extended Color Changes</span>
                </button>
            </div>
        </form>
    </div>

    <!-- CONFIRMATION MODALS -->
    @php
        $extModals = [
            ['id' => 'userDashboardResetModal', 'title' => 'Reset Dashboard Colors?', 'desc' => 'Restore user dashboard, sidebar, counters, badges and forms to system defaults.', 'input' => 'reset_user_dashboard', 'btn' => 'Yes, Reset Dashboard', 'class' => 'btn-warning text-dark'],
            ['id' => 'emailsResetModal', 'title' => 'Reset Email Colors?', 'desc' => 'Restore order invoices, digital delivery, unsubscribe and form emails to defaults.', 'input' => 'reset_emails', 'btn' => 'Yes, Reset Emails', 'class' => 'btn-warning text-dark'],
            ['id' => 'cartResetModal', 'title' => 'Reset Cart Colors?', 'desc' => 'Restore shopping cart page and drawer colors to system defaults.', 'input' => 'reset_cart', 'btn' => 'Yes, Reset Cart', 'class' => 'btn-warning text-dark'],
            ['id' => 'checkoutResetModal', 'title' => 'Reset Checkout Colors?', 'desc' => 'Restore checkout form, payment cards and button colors to defaults.', 'input' => 'reset_checkout', 'btn' => 'Yes, Reset Checkout', 'class' => 'btn-warning text-dark'],
            ['id' => 'tokenResetModal', 'title' => 'Reset Token Colors?', 'desc' => 'Restore order confirmation & 80mm thermal slip colors to defaults.', 'input' => 'reset_token', 'btn' => 'Yes, Reset Token', 'class' => 'btn-warning text-dark'],
            ['id' => 'componentsResetModal', 'title' => 'Reset Component Colors?', 'desc' => 'Restore category card, section header & widget colors to defaults.', 'input' => 'reset_components', 'btn' => 'Yes, Reset Components', 'class' => 'btn-warning text-dark'],
            ['id' => 'authResetModal', 'title' => 'Reset Auth & Error Colors?', 'desc' => 'Restore login, register, reset password & error page colors to defaults.', 'input' => 'reset_auth', 'btn' => 'Yes, Reset Auth', 'class' => 'btn-warning text-dark'],
            ['id' => 'hoversResetModal', 'title' => 'Reset Button & Social Hovers?', 'desc' => 'Restore Buy Now, Cart, WhatsApp & Footer Social hover colors to defaults.', 'input' => 'reset_hovers', 'btn' => 'Yes, Reset Hovers', 'class' => 'btn-warning text-dark'],
            ['id' => 'allExtendedResetModal', 'title' => 'Reset All Extended Colors?', 'desc' => 'Revert all extended module colors on this page to system defaults.', 'input' => 'reset_all_extended', 'btn' => 'Yes, Restore All', 'class' => 'btn-danger text-white'],
        ];
    @endphp

    @foreach($extModals as $m)
        <div class="modal fade colors-modal-scope" id="{{ $m['id'] }}" tabindex="-1" aria-hidden="true" style="z-index: 108500;">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow-lg text-center p-3">
                    <div class="modal-body p-1">
                        <div class="mb-2 {{ str_contains($m['class'], 'danger') ? 'text-danger' : 'text-warning' }}">
                            <i class="fas {{ str_contains($m['class'], 'danger') ? 'fa-triangle-exclamation' : 'fa-arrow-rotate-left' }} fa-2x"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1" style="font-size: 14.5px;">{{ $m['title'] }}</h6>
                        <p class="text-muted small mb-3" style="font-size: 12px; line-height: 1.45;">{{ $m['desc'] }}</p>
                        <div class="d-flex justify-content-center gap-2">
                            <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn {{ $m['class'] }} btn-compact shadow-xs fw-bold" onclick="submitExtReset('{{ $m['input'] }}')">{{ $m['btn'] }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('#extendedColorsRoot .color-input-container').forEach(container => {
                const picker = container.querySelector('.color-picker-widget');
                const textInput = container.querySelector('.color-hex-input');

                if (picker && textInput) {
                    picker.addEventListener('input', function () {
                        textInput.value = this.value.toLowerCase();
                    });

                    textInput.addEventListener('input', function () {
                        let val = this.value.trim();
                        if (!val.startsWith('#') && val.length > 0) val = '#' + val;
                        if (/^#([0-9A-F]{3}){1,2}$/i.test(val)) {
                            let finalHex = val.length === 4 ? '#' + val[1]+val[1] + val[2]+val[2] + val[3]+val[3] : val;
                            picker.value = finalHex.toLowerCase();
                        }
                    });

                    textInput.addEventListener('blur', function () {
                        let val = this.value.trim();
                        if (!val.startsWith('#') && val.length > 0) val = '#' + val;
                        if (/^#([0-9A-F]{3}){1,2}$/i.test(val)) {
                            if (val.length === 4) val = '#' + val[1]+val[1] + val[2]+val[2] + val[3]+val[3];
                            this.value = val.toLowerCase();
                        } else {
                            this.value = picker.value;
                        }
                    });
                }
            });

            document.querySelectorAll('#extendedColorsRoot .theme-section-card .collapse').forEach(collapseEl => {
                collapseEl.addEventListener('show.bs.collapse', function () {
                    const btn = this.closest('.theme-section-card').querySelector('.collapse-toggle-btn');
                    if (btn) btn.setAttribute('aria-expanded', 'true');
                });
                collapseEl.addEventListener('hide.bs.collapse', function () {
                    const btn = this.closest('.theme-section-card').querySelector('.collapse-toggle-btn');
                    if (btn) btn.setAttribute('aria-expanded', 'false');
                });
            });
        });

        let extAllExpanded = false;
        function toggleAllExtColorSections() {
            const collapses = document.querySelectorAll('#extendedColorsRoot .theme-section-card .collapse');
            const toggleBtns = document.querySelectorAll('#extendedColorsRoot .theme-section-card .collapse-toggle-btn');
            const btnText = document.getElementById('expandAllExtBtnText');

            extAllExpanded = !extAllExpanded;
            collapses.forEach(col => {
                const instance = bootstrap.Collapse.getOrCreateInstance(col, { toggle: false });
                extAllExpanded ? instance.show() : instance.hide();
            });
            toggleBtns.forEach(btn => btn.setAttribute('aria-expanded', extAllExpanded ? 'true' : 'false'));
            if (btnText) btnText.innerText = extAllExpanded ? 'Collapse All' : 'Expand All';
        }

        function triggerExtModal(modalId) {
            bootstrap.Modal.getOrCreateInstance(document.getElementById(modalId)).show();
        }

        function submitExtReset(inputName) {
            const form = document.getElementById('extendedColorsForm');
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = inputName;
            input.value = '1';
            form.appendChild(input);
            form.submit();
        }
    </script>
@endsection