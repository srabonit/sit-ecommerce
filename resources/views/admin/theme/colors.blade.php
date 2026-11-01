@extends('layouts.admin')

@section('title', 'Theme Colors & Styles - ' . config('app.name'))

@section('admin_content')
    <style>
        /* Strict 5px Element Radius Across All Controls */
        #themeColorsRoot .btn,
        #themeColorsRoot .btn-sm,
        #themeColorsRoot button,
        #themeColorsRoot .form-control,
        #themeColorsRoot .badge,
        #themeColorsRoot .card,
        #themeColorsRoot .theme-header-card,
        #themeColorsRoot .theme-section-card,
        #themeColorsRoot .color-control-wrapper,
        #themeColorsRoot .color-input-container,
        #themeColorsRoot .collapse-toggle-btn,
        #themeColorsRoot .bottom-action-card,
        #themeColorsRoot .top-action-bar,
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

            .header-top-wrap {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 8px !important;
            }

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

    <div class="container-fluid px-2 px-md-4 py-2" id="themeColorsRoot">

        <!-- Header & Quick Action Card -->
        <div
            class="theme-header-card d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 header-top-wrap">
            <div>
                <h5 class="fw-bold mb-0 d-flex align-items-center gap-2" style="font-size: 17px;">
                    <i class="fas fa-palette text-warning"></i>
                    <span>Theme Colors</span>
                </h5>
                <small class="text-white-50 d-block mt-1" style="font-size: 11.5px; line-height: 1.35;">
                   Customize colors for each storefront section.
                </small>
            </div>

            <div class="top-action-btns d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary btn-compact text-white border-white-50 shadow-xs col-6"
                    onclick="toggleAllColorSections()">
                    <i class="fas fa-arrows-up-down me-1"></i>
                    <span id="expandAllBtnText">Expand All</span>
                </button>
                <a href="{{ route('admin.theme.setting.extended_colors') }}" class="btn btn-warning btn-compact shadow-xs fw-bold text-dark col-6">
    <i class="fas fa-swatchbook me-1"></i>
    <span>Advance Color</span>
</a>
            
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-xs py-2 px-3 mb-2 small d-flex align-items-center justify-content-between"
                style="border-radius: 5px;">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-check-circle text-success"></i>
                    <span class="fw-semibold text-dark">{{ session('success') }}</span>
                </div>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('admin.theme.setting.colors.update') }}" method="POST" id="themeColorsForm">
            @csrf

            <!-- ========================================================================= -->
            <!-- TOP ACTION BAR: USER SHORTCUT SAVE & GLOBAL RESET                         -->
            <!-- ========================================================================= -->
            <div class="top-action-bar">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-danger btn-compact shadow-xs fw-bold px-3"
                        onclick="triggerResetColorsModal()">
                        <i class="fas fa-trash-alt me-1"></i>
                        <span>Reset All Colors</span>
                    </button>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-4"
                        style="min-height: 33px;">
                        <i class="fas fa-check-circle me-1"></i>
                        <span>Save Color Changes</span>
                    </button>
                </div>
            </div>

            <div class="row g-2 g-md-3 mb-2">

                <!-- ========================================================================= -->
                <!-- SECTION 1: HEADER & NAVIGATION (SINGLE MAIN ACCORDION / COLLAPSE)         -->
                <!-- ========================================================================= -->
                <div class="col-12">
                    <div class="theme-section-card">
                        <div class="theme-section-header d-flex justify-content-between align-items-center"
                            data-bs-toggle="collapse" data-bs-target="#collapseHeaderSection" aria-expanded="false"
                            aria-controls="collapseHeaderSection">
                            <div class="section-title-text">
                                <i class="fas fa-heading text-primary"></i>
                                <span>Header & Navigation</span>
                            
                            </div>
                            <button type="button" class="collapse-toggle-btn shadow-xs" aria-expanded="false">
                                <i class="fas fa-chevron-down collapse-icon"></i>
                            </button>
                        </div>

                        <div class="collapse" id="collapseHeaderSection">
                            <div class="p-3">
                                <div class="sub-group-header">
                                    <i class="fas fa-layer-group text-primary"></i>
                                    <span>1. Header Background &amp; Row Dividers (HR / Borders)</span>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Header Main BG</label>
                                            <div class="color-input-container">
                                                @php $vHdrBg = $colors['color_hdr_bg'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vHdrBg }}">
                                                <input type="text" name="color_hdr_bg" class="color-hex-input"
                                                    value="{{ $vHdrBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Header Bottom Border / HR</label>
                                            <div class="color-input-container">
                                                @php $vHdrBrd = $colors['color_hdr_border'] ?? '#e2e8f0'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vHdrBrd }}">
                                                <input type="text" name="color_hdr_border" class="color-hex-input"
                                                    value="{{ $vHdrBrd }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Secondary Menu Bar BG</label>
                                            <div class="color-input-container">
                                                @php $vHdrSecBg = $colors['color_hdr_sec_menu_bg'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vHdrSecBg }}">
                                                <input type="text" name="color_hdr_sec_menu_bg" class="color-hex-input"
                                                    value="{{ $vHdrSecBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Secondary Row Top Border / HR</label>
                                            <div class="color-input-container">
                                                @php $vHdrSecBrd = $colors['color_hdr_sec_menu_border'] ?? '#f1f5f9'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vHdrSecBrd }}">
                                                <input type="text" name="color_hdr_sec_menu_border" class="color-hex-input"
                                                    value="{{ $vHdrSecBrd }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="sub-group-header">
                                    <i class="fas fa-search text-success"></i>
                                    <span>2. Header Search Bar &amp; Elements</span>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Search Border</label>
                                            <div class="color-input-container">
                                                @php $vSrchBrd = $colors['color_hdr_search_border'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vSrchBrd }}">
                                                <input type="text" name="color_hdr_search_border" class="color-hex-input"
                                                    value="{{ $vSrchBrd }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Search Box BG</label>
                                            <div class="color-input-container">
                                                @php $vSrchBg = $colors['color_hdr_search_bg'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vSrchBg }}">
                                                <input type="text" name="color_hdr_search_bg" class="color-hex-input"
                                                    value="{{ $vSrchBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Input Field Text</label>
                                            <div class="color-input-container">
                                                @php $vSrchTxt = $colors['color_hdr_search_input_text'] ?? '#0f172a'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vSrchTxt }}">
                                                <input type="text" name="color_hdr_search_input_text"
                                                    class="color-hex-input" value="{{ $vSrchTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Placeholder Text</label>
                                            <div class="color-input-container">
                                                @php $vSrchPlh = $colors['color_hdr_search_placeholder'] ?? '#94a3b8'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vSrchPlh }}">
                                                <input type="text" name="color_hdr_search_placeholder"
                                                    class="color-hex-input" value="{{ $vSrchPlh }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Category Select BG</label>
                                            <div class="color-input-container">
                                                @php $vSrchCatBg = $colors['color_hdr_search_cat_bg'] ?? '#f8fafc'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vSrchCatBg }}">
                                                <input type="text" name="color_hdr_search_cat_bg" class="color-hex-input"
                                                    value="{{ $vSrchCatBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Category Select Text</label>
                                            <div class="color-input-container">
                                                @php $vSrchCatTxt = $colors['color_hdr_search_cat_text'] ?? '#475569'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vSrchCatTxt }}">
                                                <input type="text" name="color_hdr_search_cat_text" class="color-hex-input"
                                                    value="{{ $vSrchCatTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Search Button BG</label>
                                            <div class="color-input-container">
                                                @php $vSrchBtnBg = $colors['color_hdr_search_btn_bg'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vSrchBtnBg }}">
                                                <input type="text" name="color_hdr_search_btn_bg" class="color-hex-input"
                                                    value="{{ $vSrchBtnBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Search Button Text</label>
                                            <div class="color-input-container">
                                                @php $vSrchBtnTxt = $colors['color_hdr_search_btn_text'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vSrchBtnTxt }}">
                                                <input type="text" name="color_hdr_search_btn_text" class="color-hex-input"
                                                    value="{{ $vSrchBtnTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="color-control-wrapper">
                                            <label>Search Button Hover BG</label>
                                            <div class="color-input-container">
                                                @php $vSrchBtnHovBg = $colors['color_hdr_search_btn_hover_bg'] ?? '#1d4ed8'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vSrchBtnHovBg }}">
                                                <input type="text" name="color_hdr_search_btn_hover_bg"
                                                    class="color-hex-input" value="{{ $vSrchBtnHovBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="color-control-wrapper">
                                            <label>Search Button Hover Text</label>
                                            <div class="color-input-container">
                                                @php $vSrchBtnHovTxt = $colors['color_hdr_search_btn_hover_text'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vSrchBtnHovTxt }}">
                                                <input type="text" name="color_hdr_search_btn_hover_text"
                                                    class="color-hex-input" value="{{ $vSrchBtnHovTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="sub-group-header">
                                    <i class="fas fa-shopping-bag text-warning"></i>
                                    <span>3. Header Action Icons &amp; Badges</span>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Icon &amp; Text Color</label>
                                            <div class="color-input-container">
                                                @php $vIconTxt = $colors['color_hdr_icon_text'] ?? '#334155'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vIconTxt }}">
                                                <input type="text" name="color_hdr_icon_text" class="color-hex-input"
                                                    value="{{ $vIconTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Icon Hover Color</label>
                                            <div class="color-input-container">
                                                @php $vIconHov = $colors['color_hdr_icon_hover'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vIconHov }}">
                                                <input type="text" name="color_hdr_icon_hover" class="color-hex-input"
                                                    value="{{ $vIconHov }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Active Icon State</label>
                                            <div class="color-input-container">
                                                @php $vIconAct = $colors['color_hdr_icon_active'] ?? '#f59e0b'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vIconAct }}">
                                                <input type="text" name="color_hdr_icon_active" class="color-hex-input"
                                                    value="{{ $vIconAct }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="color-control-wrapper">
                                            <label>Badge Background</label>
                                            <div class="color-input-container">
                                                @php $vBadgeBg = $colors['color_hdr_badge_bg'] ?? '#ef4444'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vBadgeBg }}">
                                                <input type="text" name="color_hdr_badge_bg" class="color-hex-input"
                                                    value="{{ $vBadgeBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="color-control-wrapper">
                                            <label>Badge Text Color</label>
                                            <div class="color-input-container">
                                                @php $vBadgeTxt = $colors['color_hdr_badge_text'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vBadgeTxt }}">
                                                <input type="text" name="color_hdr_badge_text" class="color-hex-input"
                                                    value="{{ $vBadgeTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="sub-group-header">
                                    <i class="fas fa-th text-info"></i>
                                    <span>4. Category Button &amp; Dropdown Menu</span>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Button BG</label>
                                            <div class="color-input-container">
                                                @php $vCatBtnBg = $colors['color_hdr_cat_btn_bg'] ?? '#f8fafc'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vCatBtnBg }}">
                                                <input type="text" name="color_hdr_cat_btn_bg" class="color-hex-input"
                                                    value="{{ $vCatBtnBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Button Text</label>
                                            <div class="color-input-container">
                                                @php $vCatBtnTxt = $colors['color_hdr_cat_btn_text'] ?? '#0f172a'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vCatBtnTxt }}">
                                                <input type="text" name="color_hdr_cat_btn_text" class="color-hex-input"
                                                    value="{{ $vCatBtnTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Button Hover BG</label>
                                            <div class="color-input-container">
                                                @php $vCatBtnHovBg = $colors['color_hdr_cat_btn_hover_bg'] ?? '#e2e8f0'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vCatBtnHovBg }}">
                                                <input type="text" name="color_hdr_cat_btn_hover_bg" class="color-hex-input"
                                                    value="{{ $vCatBtnHovBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="color-control-wrapper">
                                            <label>Active Button BG</label>
                                            <div class="color-input-container">
                                                @php $vCatBtnActBg = $colors['color_hdr_cat_btn_active_bg'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vCatBtnActBg }}">
                                                <input type="text" name="color_hdr_cat_btn_active_bg"
                                                    class="color-hex-input" value="{{ $vCatBtnActBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="color-control-wrapper">
                                            <label>Active Button Text</label>
                                            <div class="color-input-container">
                                                @php $vCatBtnActTxt = $colors['color_hdr_cat_btn_active_text'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vCatBtnActTxt }}">
                                                <input type="text" name="color_hdr_cat_btn_active_text"
                                                    class="color-hex-input" value="{{ $vCatBtnActTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Dropdown Menu BG</label>
                                            <div class="color-input-container">
                                                @php $vCatDropBg = $colors['color_hdr_cat_drop_bg'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vCatDropBg }}">
                                                <input type="text" name="color_hdr_cat_drop_bg" class="color-hex-input"
                                                    value="{{ $vCatDropBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Dropdown Item Text</label>
                                            <div class="color-input-container">
                                                @php $vCatDropTxt = $colors['color_hdr_cat_drop_text'] ?? '#334155'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vCatDropTxt }}">
                                                <input type="text" name="color_hdr_cat_drop_text" class="color-hex-input"
                                                    value="{{ $vCatDropTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Drop Item Hover BG</label>
                                            <div class="color-input-container">
                                                @php $vCatDropHovBg = $colors['color_hdr_cat_drop_hover_bg'] ?? '#f1f5f9'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vCatDropHovBg }}">
                                                <input type="text" name="color_hdr_cat_drop_hover_bg"
                                                    class="color-hex-input" value="{{ $vCatDropHovBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Drop Item Hover Text</label>
                                            <div class="color-input-container">
                                                @php $vCatDropHovTxt = $colors['color_hdr_cat_drop_hover_text'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vCatDropHovTxt }}">
                                                <input type="text" name="color_hdr_cat_drop_hover_text"
                                                    class="color-hex-input" value="{{ $vCatDropHovTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="sub-group-header">
                                    <i class="fas fa-bars text-secondary"></i>
                                    <span>5. Desktop Primary Menu Links &amp; Dropdowns</span>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Menu Text</label>
                                            <div class="color-input-container">
                                                @php $vMenuTxt = $colors['color_hdr_menu_text'] ?? '#334155'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vMenuTxt }}">
                                                <input type="text" name="color_hdr_menu_text" class="color-hex-input"
                                                    value="{{ $vMenuTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Hover Text</label>
                                            <div class="color-input-container">
                                                @php $vMenuHovTxt = $colors['color_hdr_menu_hover_text'] ?? '#00a8ff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vMenuHovTxt }}">
                                                <input type="text" name="color_hdr_menu_hover_text" class="color-hex-input"
                                                    value="{{ $vMenuHovTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Hover BG</label>
                                            <div class="color-input-container">
                                                @php $vMenuHovBg = $colors['color_hdr_menu_hover_bg'] ?? '#e0f2fe'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vMenuHovBg }}">
                                                <input type="text" name="color_hdr_menu_hover_bg" class="color-hex-input"
                                                    value="{{ $vMenuHovBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="color-control-wrapper">
                                            <label>Active Text Color</label>
                                            <div class="color-input-container">
                                                @php $vMenuActTxt = $colors['color_hdr_menu_active_text'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vMenuActTxt }}">
                                                <input type="text" name="color_hdr_menu_active_text" class="color-hex-input"
                                                    value="{{ $vMenuActTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="color-control-wrapper">
                                            <label>Active Pill BG</label>
                                            <div class="color-input-container">
                                                @php $vMenuActBg = $colors['color_hdr_menu_active_bg'] ?? '#e0f2fe'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vMenuActBg }}">
                                                <input type="text" name="color_hdr_menu_active_bg" class="color-hex-input"
                                                    value="{{ $vMenuActBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Dropdown Menu BG</label>
                                            <div class="color-input-container">
                                                @php $vMenuDropBg = $colors['color_hdr_menu_drop_bg'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vMenuDropBg }}">
                                                <input type="text" name="color_hdr_menu_drop_bg" class="color-hex-input"
                                                    value="{{ $vMenuDropBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Dropdown Item Text</label>
                                            <div class="color-input-container">
                                                @php $vMenuDropTxt = $colors['color_hdr_menu_drop_text'] ?? '#475569'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vMenuDropTxt }}">
                                                <input type="text" name="color_hdr_menu_drop_text" class="color-hex-input"
                                                    value="{{ $vMenuDropTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Drop Item Hover BG</label>
                                            <div class="color-input-container">
                                                @php $vMenuDropHovBg = $colors['color_hdr_menu_drop_hover_bg'] ?? '#f8fafc'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vMenuDropHovBg }}">
                                                <input type="text" name="color_hdr_menu_drop_hover_bg"
                                                    class="color-hex-input" value="{{ $vMenuDropHovBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Drop Item Hover Text</label>
                                            <div class="color-input-container">
                                                @php $vMenuDropHovTxt = $colors['color_hdr_menu_drop_hover_text'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vMenuDropHovTxt }}">
                                                <input type="text" name="color_hdr_menu_drop_hover_text"
                                                    class="color-hex-input" value="{{ $vMenuDropHovTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="sub-group-header">
                                    <i class="fas fa-headset text-danger"></i>
                                    <span>6. Header Support Contact Info</span>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Headset Icon</label>
                                            <div class="color-input-container">
                                                @php $vSuppIco = $colors['color_hdr_support_icon'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vSuppIco }}">
                                                <input type="text" name="color_hdr_support_icon" class="color-hex-input"
                                                    value="{{ $vSuppIco }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Support: Label</label>
                                            <div class="color-input-container">
                                                @php $vSuppLbl = $colors['color_hdr_support_label'] ?? '#64748b'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vSuppLbl }}">
                                                <input type="text" name="color_hdr_support_label" class="color-hex-input"
                                                    value="{{ $vSuppLbl }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Phone Number</label>
                                            <div class="color-input-container">
                                                @php $vSuppPhn = $colors['color_hdr_support_phone'] ?? '#0f172a'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vSuppPhn }}">
                                                <input type="text" name="color_hdr_support_phone" class="color-hex-input"
                                                    value="{{ $vSuppPhn }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Phone Hover</label>
                                            <div class="color-input-container">
                                                @php $vSuppPhnHov = $colors['color_hdr_support_phone_hover'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vSuppPhnHov }}">
                                                <input type="text" name="color_hdr_support_phone_hover"
                                                    class="color-hex-input" value="{{ $vSuppPhnHov }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="sub-group-header">
                                    <i class="fas fa-mobile-alt text-primary"></i>
                                    <span>7. Mobile Header Trigger &amp; Offcanvas Navigation Drawer</span>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Mobile Trigger BG</label>
                                            <div class="color-input-container">
                                                @php $vMobTogBg = $colors['color_hdr_mob_toggle_bg'] ?? '#f8fafc'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vMobTogBg }}">
                                                <input type="text" name="color_hdr_mob_toggle_bg" class="color-hex-input"
                                                    value="{{ $vMobTogBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Mobile Trigger Icon</label>
                                            <div class="color-input-container">
                                                @php $vMobTogIco = $colors['color_hdr_mob_toggle_icon'] ?? '#0f172a'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vMobTogIco }}">
                                                <input type="text" name="color_hdr_mob_toggle_icon" class="color-hex-input"
                                                    value="{{ $vMobTogIco }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Trigger Hover BG</label>
                                            <div class="color-input-container">
                                                @php $vMobTogHovBg = $colors['color_hdr_mob_toggle_hover_bg'] ?? '#e2e8f0'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vMobTogHovBg }}">
                                                <input type="text" name="color_hdr_mob_toggle_hover_bg"
                                                    class="color-hex-input" value="{{ $vMobTogHovBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Trigger Hover Icon</label>
                                            <div class="color-input-container">
                                                @php $vMobTogHovIco = $colors['color_hdr_mob_toggle_hover_icon'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vMobTogHovIco }}">
                                                <input type="text" name="color_hdr_mob_toggle_hover_icon"
                                                    class="color-hex-input" value="{{ $vMobTogHovIco }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Drawer Header BG</label>
                                            <div class="color-input-container">
                                                @php $vMobHeadBg = $colors['color_hdr_mob_head_bg'] ?? '#f8fafc'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vMobHeadBg }}">
                                                <input type="text" name="color_hdr_mob_head_bg" class="color-hex-input"
                                                    value="{{ $vMobHeadBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Drawer Header Text</label>
                                            <div class="color-input-container">
                                                @php $vMobHeadTxt = $colors['color_hdr_mob_head_text'] ?? '#0f172a'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vMobHeadTxt }}">
                                                <input type="text" name="color_hdr_mob_head_text" class="color-hex-input"
                                                    value="{{ $vMobHeadTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Drawer Header Icon</label>
                                            <div class="color-input-container">
                                                @php $vMobHeadIco = $colors['color_hdr_mob_head_icon'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vMobHeadIco }}">
                                                <input type="text" name="color_hdr_mob_head_icon" class="color-hex-input"
                                                    value="{{ $vMobHeadIco }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Tabs Wrapper BG</label>
                                            <div class="color-input-container">
                                                @php $vMobTabBg = $colors['color_hdr_mob_tab_bg'] ?? '#e2e8f0'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vMobTabBg }}">
                                                <input type="text" name="color_hdr_mob_tab_bg" class="color-hex-input"
                                                    value="{{ $vMobTabBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Tab Inactive Text</label>
                                            <div class="color-input-container">
                                                @php $vMobTabTxt = $colors['color_hdr_mob_tab_text'] ?? '#64748b'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vMobTabTxt }}">
                                                <input type="text" name="color_hdr_mob_tab_text" class="color-hex-input"
                                                    value="{{ $vMobTabTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Tab Active BG</label>
                                            <div class="color-input-container">
                                                @php $vMobTabActBg = $colors['color_hdr_mob_tab_active_bg'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vMobTabActBg }}">
                                                <input type="text" name="color_hdr_mob_tab_active_bg"
                                                    class="color-hex-input" value="{{ $vMobTabActBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Tab Active Text</label>
                                            <div class="color-input-container">
                                                @php $vMobTabActTxt = $colors['color_hdr_mob_tab_active_text'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vMobTabActTxt }}">
                                                <input type="text" name="color_hdr_mob_tab_active_text"
                                                    class="color-hex-input" value="{{ $vMobTabActTxt }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Drawer Item Text (Common)</label>
                                            <div class="color-input-container">
                                                @php $vMobItmTxt = $colors['color_hdr_mob_item_text'] ?? '#0f172a'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vMobItmTxt }}">
                                                <input type="text" name="color_hdr_mob_item_text" class="color-hex-input"
                                                    value="{{ $vMobItmTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Item Separator Border / HR</label>
                                            <div class="color-input-container">
                                                @php $vMobItmBrd = $colors['color_hdr_mob_item_border'] ?? '#f1f5f9'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vMobItmBrd }}">
                                                <input type="text" name="color_hdr_mob_item_border" class="color-hex-input"
                                                    value="{{ $vMobItmBrd }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Drawer Body Background</label>
                                            <div class="color-input-container">
                                                @php $vMobBodyBg = $colors['color_hdr_mob_body_bg'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vMobBodyBg }}">
                                                <input type="text" name="color_hdr_mob_body_bg" class="color-hex-input"
                                                    value="{{ $vMobBodyBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Item Hover BG</label>
                                            <div class="color-input-container">
                                                @php $vMobItmHovBg = $colors['color_hdr_mob_item_hover_bg'] ?? '#f8fafc'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vMobItmHovBg }}">
                                                <input type="text" name="color_hdr_mob_item_hover_bg"
                                                    class="color-hex-input" value="{{ $vMobItmHovBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Item Hover Text</label>
                                            <div class="color-input-container">
                                                @php $vMobItmHovTxt = $colors['color_hdr_mob_item_hover_text'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vMobItmHovTxt }}">
                                                <input type="text" name="color_hdr_mob_item_hover_text"
                                                    class="color-hex-input" value="{{ $vMobItmHovTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Active Item BG</label>
                                            <div class="color-input-container">
                                                @php $vMobItmActBg = $colors['color_hdr_mob_item_active_bg'] ?? '#eff6ff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vMobItmActBg }}">
                                                <input type="text" name="color_hdr_mob_item_active_bg"
                                                    class="color-hex-input" value="{{ $vMobItmActBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Active Item Text</label>
                                            <div class="color-input-container">
                                                @php $vMobItmActTxt = $colors['color_hdr_mob_item_active_text'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vMobItmActTxt }}">
                                                <input type="text" name="color_hdr_mob_item_active_text"
                                                    class="color-hex-input" value="{{ $vMobItmActTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <!-- SPECIFIC RESET BUTTON FOR HEADER -->
                            <div class="section-footer-box">
                                <span class="small text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Restore only header, navigation, search and mobile drawer colors to default.
                                </span>
                                <button type="button"
                                    class="btn btn-outline-warning btn-compact shadow-xs fw-bold px-3 text-dark border-warning"
                                    onclick="triggerResetHeaderModal()">
                                    <i class="fas fa-rotate-left me-1 text-warning"></i>
                                    <span>Reset Header Colors</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- SECTION 2: FOOTER & BOTTOM BAR (SINGLE MAIN ACCORDION / COLLAPSE)         -->
                <!-- ========================================================================= -->
                <div class="col-12">
                    <div class="theme-section-card">
                        <div class="theme-section-header d-flex justify-content-between align-items-center"
                            data-bs-toggle="collapse" data-bs-target="#collapseFooterSection" aria-expanded="false"
                            aria-controls="collapseFooterSection">
                            <div class="section-title-text">
                                <i class="fas fa-shoe-prints text-success"></i>
                                <span>Footer &amp; Bottom Bar Colors</span>
                                <span class="badge bg-secondary-subtle text-secondary small fw-normal ms-2"
                                    style="font-size: 10px;">PC &amp; Mobile Footer, Payments, Social &amp; Copyright</span>
                            </div>
                            <button type="button" class="collapse-toggle-btn shadow-xs" aria-expanded="false">
                                <i class="fas fa-chevron-down collapse-icon"></i>
                            </button>
                        </div>

                        <div class="collapse" id="collapseFooterSection">
                            <div class="p-3">
                                <div class="sub-group-header">
                                    <i class="fas fa-layer-group text-primary"></i>
                                    <span>1. PC Footer Background, Borders &amp; Brand Typography</span>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Footer Main BG</label>
                                            <div class="color-input-container">
                                                @php $vFtrBg = $colors['color_ftr_bg'] ?? '#020b18'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vFtrBg }}">
                                                <input type="text" name="color_ftr_bg" class="color-hex-input"
                                                    value="{{ $vFtrBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Footer Top Border</label>
                                            <div class="color-input-container">
                                                @php $vFtrBrd = $colors['color_ftr_border'] ?? 'rgba(255, 255, 255, 0.08)'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ str_starts_with($vFtrBrd, '#') ? $vFtrBrd : '#1e293b' }}">
                                                <input type="text" name="color_ftr_border" class="color-hex-input"
                                                    value="{{ $vFtrBrd }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Logo Border</label>
                                            <div class="color-input-container">
                                                @php $vFtrLogoBrd = $colors['color_ftr_logo_border'] ?? '#1e293b'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ str_starts_with($vFtrLogoBrd, '#') ? $vFtrLogoBrd : '#1e293b' }}">
                                                <input type="text" name="color_ftr_logo_border" class="color-hex-input"
                                                    value="{{ $vFtrLogoBrd }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Common Body Text</label>
                                            <div class="color-input-container">
                                                @php $vFtrTxt = $colors['color_ftr_text'] ?? '#94a3b8'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vFtrTxt }}">
                                                <input type="text" name="color_ftr_text" class="color-hex-input"
                                                    value="{{ $vFtrTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Brand Title Color</label>
                                            <div class="color-input-container">
                                                @php $vFtrTitle = $colors['color_ftr_brand_title'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vFtrTitle }}">
                                                <input type="text" name="color_ftr_brand_title" class="color-hex-input"
                                                    value="{{ $vFtrTitle }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Brand Tagline Color</label>
                                            <div class="color-input-container">
                                                @php $vFtrTag = $colors['color_ftr_brand_tagline'] ?? '#94a3b8'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vFtrTag }}">
                                                <input type="text" name="color_ftr_brand_tagline" class="color-hex-input"
                                                    value="{{ $vFtrTag }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>About / Description Text</label>
                                            <div class="color-input-container">
                                                @php $vFtrAbout = $colors['color_ftr_about_text'] ?? '#94a3b8'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vFtrAbout }}">
                                                <input type="text" name="color_ftr_about_text" class="color-hex-input"
                                                    value="{{ $vFtrAbout }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="sub-group-header">
                                    <i class="fas fa-link text-info"></i>
                                    <span>2. Column Headings, Links &amp; Caret Icons</span>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Column Heading / Title</label>
                                            <div class="color-input-container">
                                                @php $vFtrHeading = $colors['color_ftr_heading'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vFtrHeading }}">
                                                <input type="text" name="color_ftr_heading" class="color-hex-input"
                                                    value="{{ $vFtrHeading }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Heading Icon Color</label>
                                            <div class="color-input-container">
                                                @php $vFtrHeadIco = $colors['color_ftr_heading_icon'] ?? '#f59e0b'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vFtrHeadIco }}">
                                                <input type="text" name="color_ftr_heading_icon" class="color-hex-input"
                                                    value="{{ $vFtrHeadIco }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Link Text Color</label>
                                            <div class="color-input-container">
                                                @php $vFtrLinkTxt = $colors['color_ftr_link_text'] ?? '#94a3b8'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vFtrLinkTxt }}">
                                                <input type="text" name="color_ftr_link_text" class="color-hex-input"
                                                    value="{{ $vFtrLinkTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Link Hover Color</label>
                                            <div class="color-input-container">
                                                @php $vFtrLinkHov = $colors['color_ftr_link_hover_text'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vFtrLinkHov }}">
                                                <input type="text" name="color_ftr_link_hover_text" class="color-hex-input"
                                                    value="{{ $vFtrLinkHov }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="color-control-wrapper">
                                            <label>Link Caret / Bullet Icon</label>
                                            <div class="color-input-container">
                                                @php $vFtrLinkIco = $colors['color_ftr_link_icon'] ?? '#94a3b8'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ str_starts_with($vFtrLinkIco, '#') ? $vFtrLinkIco : '#94a3b8' }}">
                                                <input type="text" name="color_ftr_link_icon" class="color-hex-input"
                                                    value="{{ $vFtrLinkIco }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="color-control-wrapper">
                                            <label>Link Caret Hover Icon</label>
                                            <div class="color-input-container">
                                                @php $vFtrLinkHovIco = $colors['color_ftr_link_hover_icon'] ?? '#f59e0b'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vFtrLinkHovIco }}">
                                                <input type="text" name="color_ftr_link_hover_icon" class="color-hex-input"
                                                    value="{{ $vFtrLinkHovIco }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="sub-group-header">
                                    <i class="fas fa-headset text-warning"></i>
                                    <span>3. Customer Contact Info &amp; Social Media Icons</span>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Contact Icon Color</label>
                                            <div class="color-input-container">
                                                @php $vFtrCntIco = $colors['color_ftr_contact_icon'] ?? '#f59e0b'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vFtrCntIco }}">
                                                <input type="text" name="color_ftr_contact_icon" class="color-hex-input"
                                                    value="{{ $vFtrCntIco }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Contact Text / Phone</label>
                                            <div class="color-input-container">
                                                @php $vFtrCntTxt = $colors['color_ftr_contact_text'] ?? '#94a3b8'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vFtrCntTxt }}">
                                                <input type="text" name="color_ftr_contact_text" class="color-hex-input"
                                                    value="{{ $vFtrCntTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Contact Hover Color</label>
                                            <div class="color-input-container">
                                                @php $vFtrCntHov = $colors['color_ftr_contact_hover'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vFtrCntHov }}">
                                                <input type="text" name="color_ftr_contact_hover" class="color-hex-input"
                                                    value="{{ $vFtrCntHov }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Social Icon Color</label>
                                            <div class="color-input-container">
                                                @php $vFtrSocIco = $colors['color_ftr_social_icon'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vFtrSocIco }}">
                                                <input type="text" name="color_ftr_social_icon" class="color-hex-input"
                                                    value="{{ $vFtrSocIco }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Social Circle BG</label>
                                            <div class="color-input-container">
                                                @php $vFtrSocBg = $colors['color_ftr_social_bg'] ?? '#1e293b'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ str_starts_with($vFtrSocBg, '#') ? $vFtrSocBg : '#1e293b' }}">
                                                <input type="text" name="color_ftr_social_bg" class="color-hex-input"
                                                    value="{{ $vFtrSocBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Social Circle Border</label>
                                            <div class="color-input-container">
                                                @php $vFtrSocBrd = $colors['color_ftr_social_border'] ?? '#334155'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ str_starts_with($vFtrSocBrd, '#') ? $vFtrSocBrd : '#334155' }}">
                                                <input type="text" name="color_ftr_social_border" class="color-hex-input"
                                                    value="{{ $vFtrSocBrd }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Social Hover Circle BG</label>
                                            <div class="color-input-container">
                                                @php $vFtrSocHovBg = $colors['color_ftr_social_hover_bg'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vFtrSocHovBg }}">
                                                <input type="text" name="color_ftr_social_hover_bg" class="color-hex-input"
                                                    value="{{ $vFtrSocHovBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Social Hover Border</label>
                                            <div class="color-input-container">
                                                @php $vFtrSocHovBrd = $colors['color_ftr_social_hover_border'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vFtrSocHovBrd }}">
                                                <input type="text" name="color_ftr_social_hover_border"
                                                    class="color-hex-input" value="{{ $vFtrSocHovBrd }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="sub-group-header">
                                    <i class="fas fa-credit-card text-success"></i>
                                    <span>4. Payment Badges, Cards &amp; Logo Borders</span>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Payment Card BG</label>
                                            <div class="color-input-container">
                                                @php $vFtrPayBg = $colors['color_ftr_payment_bg'] ?? '#0f172a'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ str_starts_with($vFtrPayBg, '#') ? $vFtrPayBg : '#0f172a' }}">
                                                <input type="text" name="color_ftr_payment_bg" class="color-hex-input"
                                                    value="{{ $vFtrPayBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Payment Card Border</label>
                                            <div class="color-input-container">
                                                @php $vFtrPayBrd = $colors['color_ftr_payment_border'] ?? '#1e293b'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ str_starts_with($vFtrPayBrd, '#') ? $vFtrPayBrd : '#1e293b' }}">
                                                <input type="text" name="color_ftr_payment_border" class="color-hex-input"
                                                    value="{{ $vFtrPayBrd }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Payment Name Text</label>
                                            <div class="color-input-container">
                                                @php $vFtrPayTxt = $colors['color_ftr_payment_text'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vFtrPayTxt }}">
                                                <input type="text" name="color_ftr_payment_text" class="color-hex-input"
                                                    value="{{ $vFtrPayTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Payment Logo Border</label>
                                            <div class="color-input-container">
                                                @php $vFtrPayLogoBrd = $colors['color_ftr_payment_logo_border'] ?? '#334155'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ str_starts_with($vFtrPayLogoBrd, '#') ? $vFtrPayLogoBrd : '#334155' }}">
                                                <input type="text" name="color_ftr_payment_logo_border"
                                                    class="color-hex-input" value="{{ $vFtrPayLogoBrd }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Payment Hover BG</label>
                                            <div class="color-input-container">
                                                @php $vFtrPayHovBg = $colors['color_ftr_payment_hover_bg'] ?? '#1e293b'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ str_starts_with($vFtrPayHovBg, '#') ? $vFtrPayHovBg : '#1e293b' }}">
                                                <input type="text" name="color_ftr_payment_hover_bg" class="color-hex-input"
                                                    value="{{ $vFtrPayHovBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Payment Hover Border</label>
                                            <div class="color-input-container">
                                                @php $vFtrPayHovBrd = $colors['color_ftr_payment_hover_border'] ?? '#334155'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ str_starts_with($vFtrPayHovBrd, '#') ? $vFtrPayHovBrd : '#334155' }}">
                                                <input type="text" name="color_ftr_payment_hover_border"
                                                    class="color-hex-input" value="{{ $vFtrPayHovBrd }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="sub-group-header">
                                    <i class="fas fa-copyright text-secondary"></i>
                                    <span>5. Bottom Copyright Bar &amp; Developer Credit</span>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Bottom Bar BG</label>
                                            <div class="color-input-container">
                                                @php $vFtrBotBg = $colors['color_ftr_bottom_bg'] ?? '#010813'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vFtrBotBg }}">
                                                <input type="text" name="color_ftr_bottom_bg" class="color-hex-input"
                                                    value="{{ $vFtrBotBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Bottom Bar Top Border</label>
                                            <div class="color-input-container">
                                                @php $vFtrBotBrd = $colors['color_ftr_bottom_border'] ?? '#1e293b'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ str_starts_with($vFtrBotBrd, '#') ? $vFtrBotBrd : '#1e293b' }}">
                                                <input type="text" name="color_ftr_bottom_border" class="color-hex-input"
                                                    value="{{ $vFtrBotBrd }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Copyright Text Color</label>
                                            <div class="color-input-container">
                                                @php $vFtrCopyTxt = $colors['color_ftr_copyright_text'] ?? '#94a3b8'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vFtrCopyTxt }}">
                                                <input type="text" name="color_ftr_copyright_text" class="color-hex-input"
                                                    value="{{ $vFtrCopyTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Developer Credit Color</label>
                                            <div class="color-input-container">
                                                @php $vFtrDevTxt = $colors['color_ftr_developer_text'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vFtrDevTxt }}">
                                                <input type="text" name="color_ftr_developer_text" class="color-hex-input"
                                                    value="{{ $vFtrDevTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="sub-group-header">
                                    <i class="fas fa-mobile-alt text-danger"></i>
                                    <span>6. Mobile Footer Accordion, Brand Box &amp; Secure Checkout</span>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Mobile Brand Box BG</label>
                                            <div class="color-input-container">
                                                @php $vFtrMobBrdBg = $colors['color_ftr_mob_brand_bg'] ?? '#0f172a'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ str_starts_with($vFtrMobBrdBg, '#') ? $vFtrMobBrdBg : '#0f172a' }}">
                                                <input type="text" name="color_ftr_mob_brand_bg" class="color-hex-input"
                                                    value="{{ $vFtrMobBrdBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Mobile Brand Border</label>
                                            <div class="color-input-container">
                                                @php $vFtrMobBrdBorder = $colors['color_ftr_mob_brand_border'] ?? '#1e293b'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ str_starts_with($vFtrMobBrdBorder, '#') ? $vFtrMobBrdBorder : '#1e293b' }}">
                                                <input type="text" name="color_ftr_mob_brand_border" class="color-hex-input"
                                                    value="{{ $vFtrMobBrdBorder }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Mobile Logo Box BG</label>
                                            <div class="color-input-container">
                                                @php $vFtrMobLogoBg = $colors['color_ftr_mob_logo_box_bg'] ?? '#1e293b'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ str_starts_with($vFtrMobLogoBg, '#') ? $vFtrMobLogoBg : '#1e293b' }}">
                                                <input type="text" name="color_ftr_mob_logo_box_bg" class="color-hex-input"
                                                    value="{{ $vFtrMobLogoBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Mobile Brand Arrow</label>
                                            <div class="color-input-container">
                                                @php $vFtrMobArr = $colors['color_ftr_mob_brand_arrow'] ?? '#94a3b8'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vFtrMobArr }}">
                                                <input type="text" name="color_ftr_mob_brand_arrow" class="color-hex-input"
                                                    value="{{ $vFtrMobArr }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Accordion Group BG</label>
                                            <div class="color-input-container">
                                                @php $vFtrMobAccBg = $colors['color_ftr_mob_acc_bg'] ?? '#0f172a'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ str_starts_with($vFtrMobAccBg, '#') ? $vFtrMobAccBg : '#0f172a' }}">
                                                <input type="text" name="color_ftr_mob_acc_bg" class="color-hex-input"
                                                    value="{{ $vFtrMobAccBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Accordion Outer Border</label>
                                            <div class="color-input-container">
                                                @php $vFtrMobAccBrd = $colors['color_ftr_mob_acc_border'] ?? '#1e293b'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ str_starts_with($vFtrMobAccBrd, '#') ? $vFtrMobAccBrd : '#1e293b' }}">
                                                <input type="text" name="color_ftr_mob_acc_border" class="color-hex-input"
                                                    value="{{ $vFtrMobAccBrd }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Accordion Item Divider</label>
                                            <div class="color-input-container">
                                                @php $vFtrMobAccItemBrd = $colors['color_ftr_mob_acc_item_border'] ?? '#1e293b'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ str_starts_with($vFtrMobAccItemBrd, '#') ? $vFtrMobAccItemBrd : '#1e293b' }}">
                                                <input type="text" name="color_ftr_mob_acc_item_border"
                                                    class="color-hex-input" value="{{ $vFtrMobAccItemBrd }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Accordion Title Text</label>
                                            <div class="color-input-container">
                                                @php $vFtrMobAccBtnTxt = $colors['color_ftr_mob_acc_btn_text'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vFtrMobAccBtnTxt }}">
                                                <input type="text" name="color_ftr_mob_acc_btn_text" class="color-hex-input"
                                                    value="{{ $vFtrMobAccBtnTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Accordion Title Icon</label>
                                            <div class="color-input-container">
                                                @php $vFtrMobAccBtnIco = $colors['color_ftr_mob_acc_btn_icon'] ?? '#94a3b8'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vFtrMobAccBtnIco }}">
                                                <input type="text" name="color_ftr_mob_acc_btn_icon" class="color-hex-input"
                                                    value="{{ $vFtrMobAccBtnIco }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Accordion Chevron</label>
                                            <div class="color-input-container">
                                                @php $vFtrMobChev = $colors['color_ftr_mob_acc_chevron'] ?? '#94a3b8'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vFtrMobChev }}">
                                                <input type="text" name="color_ftr_mob_acc_chevron" class="color-hex-input"
                                                    value="{{ $vFtrMobChev }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Active Chevron Color</label>
                                            <div class="color-input-container">
                                                @php $vFtrMobChevAct = $colors['color_ftr_mob_acc_chevron_active'] ?? '#f59e0b'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vFtrMobChevAct }}">
                                                <input type="text" name="color_ftr_mob_acc_chevron_active"
                                                    class="color-hex-input" value="{{ $vFtrMobChevAct }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Accordion Body BG</label>
                                            <div class="color-input-container">
                                                @php $vFtrMobBodyBg = $colors['color_ftr_mob_acc_body_bg'] ?? '#020b18'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ str_starts_with($vFtrMobBodyBg, '#') ? $vFtrMobBodyBg : '#020b18' }}">
                                                <input type="text" name="color_ftr_mob_acc_body_bg" class="color-hex-input"
                                                    value="{{ $vFtrMobBodyBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Accordion Link Text</label>
                                            <div class="color-input-container">
                                                @php $vFtrMobAccLink = $colors['color_ftr_mob_acc_link_text'] ?? '#94a3b8'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vFtrMobAccLink }}">
                                                <input type="text" name="color_ftr_mob_acc_link_text"
                                                    class="color-hex-input" value="{{ $vFtrMobAccLink }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Accordion Link Hover</label>
                                            <div class="color-input-container">
                                                @php $vFtrMobAccHov = $colors['color_ftr_mob_acc_link_hover'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vFtrMobAccHov }}">
                                                <input type="text" name="color_ftr_mob_acc_link_hover"
                                                    class="color-hex-input" value="{{ $vFtrMobAccHov }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Mobile Payment Pill BG</label>
                                            <div class="color-input-container">
                                                @php $vFtrMobPayPillBg = $colors['color_ftr_mob_pay_pill_bg'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vFtrMobPayPillBg }}">
                                                <input type="text" name="color_ftr_mob_pay_pill_bg" class="color-hex-input"
                                                    value="{{ $vFtrMobPayPillBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Mobile Payment Pill Border</label>
                                            <div class="color-input-container">
                                                @php $vFtrMobPayPillBrd = $colors['color_ftr_mob_pay_pill_border'] ?? '#e2e8f0'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ str_starts_with($vFtrMobPayPillBrd, '#') ? $vFtrMobPayPillBrd : '#e2e8f0' }}">
                                                <input type="text" name="color_ftr_mob_pay_pill_border"
                                                    class="color-hex-input" value="{{ $vFtrMobPayPillBrd }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Mobile Payment Pill Text</label>
                                            <div class="color-input-container">
                                                @php $vFtrMobPayPillTxt = $colors['color_ftr_mob_pay_pill_text'] ?? '#0f172a'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vFtrMobPayPillTxt }}">
                                                <input type="text" name="color_ftr_mob_pay_pill_text"
                                                    class="color-hex-input" value="{{ $vFtrMobPayPillTxt }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Secure Row Border</label>
                                            <div class="color-input-container">
                                                @php $vFtrMobSecBrd = $colors['color_ftr_mob_secure_border'] ?? '#1e293b'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ str_starts_with($vFtrMobSecBrd, '#') ? $vFtrMobSecBrd : '#1e293b' }}">
                                                <input type="text" name="color_ftr_mob_secure_border"
                                                    class="color-hex-input" value="{{ $vFtrMobSecBrd }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Secure Lock Icon</label>
                                            <div class="color-input-container">
                                                @php $vFtrMobSecIco = $colors['color_ftr_mob_secure_icon'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vFtrMobSecIco }}">
                                                <input type="text" name="color_ftr_mob_secure_icon" class="color-hex-input"
                                                    value="{{ $vFtrMobSecIco }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Secure Checkout Text</label>
                                            <div class="color-input-container">
                                                @php $vFtrMobSecTxt = $colors['color_ftr_mob_secure_text'] ?? '#94a3b8'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vFtrMobSecTxt }}">
                                                <input type="text" name="color_ftr_mob_secure_text" class="color-hex-input"
                                                    value="{{ $vFtrMobSecTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <!-- SPECIFIC RESET BUTTON FOR FOOTER -->
                            <div class="section-footer-box">
                                <span class="small text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Restore only PC &amp; mobile footer, payments, and copyright colors to default.
                                </span>
                                <button type="button"
                                    class="btn btn-outline-warning btn-compact shadow-xs fw-bold px-3 text-dark border-warning"
                                    onclick="triggerResetFooterModal()">
                                    <i class="fas fa-rotate-left me-1 text-warning"></i>
                                    <span>Reset Footer Colors</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- SECTION 3: PRODUCT CARD, DETAILS & QUICK VIEW (SINGLE MAIN ACCORDION)     -->
                <!-- ========================================================================= -->
                <div class="col-12">
                    <div class="theme-section-card">
                        <div class="theme-section-header d-flex justify-content-between align-items-center"
                            data-bs-toggle="collapse" data-bs-target="#collapseProductSection" aria-expanded="false"
                            aria-controls="collapseProductSection">
                            <div class="section-title-text">
                                <i class="fas fa-box-open text-warning"></i>
                                <span>Product Card, Details &amp; Quick View Colors</span>
                                <span class="badge bg-secondary-subtle text-secondary small fw-normal ms-2"
                                    style="font-size: 10px;">Grid Cards, Details Page, Swatches, Buttons &amp; Quick
                                    View</span>
                            </div>
                            <button type="button" class="collapse-toggle-btn shadow-xs" aria-expanded="false">
                                <i class="fas fa-chevron-down collapse-icon"></i>
                            </button>
                        </div>

                        <div class="collapse" id="collapseProductSection">
                            <div class="p-3">
                                <div class="sub-group-header">
                                    <i class="fas fa-th-large text-primary"></i>
                                    <span>1. Product Grid Card - Card Layout, Sale Badge &amp; Floating Actions</span>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Card Background</label>
                                            <div class="color-input-container">
                                                @php $vCpcCardBg = $colors['color_cpc_card_bg'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vCpcCardBg }}">
                                                <input type="text" name="color_cpc_card_bg" class="color-hex-input"
                                                    value="{{ $vCpcCardBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Card Border (Normal)</label>
                                            <div class="color-input-container">
                                                @php $vCpcCardBrd = $colors['color_cpc_card_border'] ?? '#edf2f7'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vCpcCardBrd }}">
                                                <input type="text" name="color_cpc_card_border" class="color-hex-input"
                                                    value="{{ $vCpcCardBrd }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Card Border (Hover)</label>
                                            <div class="color-input-container">
                                                @php $vCpcCardBrdHov = $colors['color_cpc_card_border_hover'] ?? '#cbd5e1'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vCpcCardBrdHov }}">
                                                <input type="text" name="color_cpc_card_border_hover"
                                                    class="color-hex-input" value="{{ $vCpcCardBrdHov }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Image Wrap Background</label>
                                            <div class="color-input-container">
                                                @php $vCpcImgBg = $colors['color_cpc_img_wrap_bg'] ?? '#f8fafc'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vCpcImgBg }}">
                                                <input type="text" name="color_cpc_img_wrap_bg" class="color-hex-input"
                                                    value="{{ $vCpcImgBg }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Sale Badge BG</label>
                                            <div class="color-input-container">
                                                @php $vCpcSaleBg = $colors['color_cpc_sale_badge_bg'] ?? '#ef4444'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vCpcSaleBg }}">
                                                <input type="text" name="color_cpc_sale_badge_bg" class="color-hex-input"
                                                    value="{{ $vCpcSaleBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Sale Badge Text</label>
                                            <div class="color-input-container">
                                                @php $vCpcSaleTxt = $colors['color_cpc_sale_badge_text'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vCpcSaleTxt }}">
                                                <input type="text" name="color_cpc_sale_badge_text" class="color-hex-input"
                                                    value="{{ $vCpcSaleTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Sale Badge Border</label>
                                            <div class="color-input-container">
                                                @php $vCpcSaleBrd = $colors['color_cpc_sale_badge_border'] ?? '#ef4444'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ str_starts_with($vCpcSaleBrd, '#') ? $vCpcSaleBrd : '#ef4444' }}">
                                                <input type="text" name="color_cpc_sale_badge_border"
                                                    class="color-hex-input" value="{{ $vCpcSaleBrd }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Wishlist Btn BG</label>
                                            <div class="color-input-container">
                                                @php $vCpcWishBg = $colors['color_cpc_wishlist_bg'] ?? '#f1592a'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vCpcWishBg }}">
                                                <input type="text" name="color_cpc_wishlist_bg" class="color-hex-input"
                                                    value="{{ $vCpcWishBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Wishlist Icon Color</label>
                                            <div class="color-input-container">
                                                @php $vCpcWishIco = $colors['color_cpc_wishlist_icon'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vCpcWishIco }}">
                                                <input type="text" name="color_cpc_wishlist_icon" class="color-hex-input"
                                                    value="{{ $vCpcWishIco }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Wishlist Hover BG</label>
                                            <div class="color-input-container">
                                                @php $vCpcWishHovBg = $colors['color_cpc_wishlist_hover_bg'] ?? '#1f2033'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vCpcWishHovBg }}">
                                                <input type="text" name="color_cpc_wishlist_hover_bg"
                                                    class="color-hex-input" value="{{ $vCpcWishHovBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Wishlist Active BG</label>
                                            <div class="color-input-container">
                                                @php $vCpcWishActBg = $colors['color_cpc_wishlist_active_bg'] ?? '#dc2626'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vCpcWishActBg }}">
                                                <input type="text" name="color_cpc_wishlist_active_bg"
                                                    class="color-hex-input" value="{{ $vCpcWishActBg }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Quick View Btn BG</label>
                                            <div class="color-input-container">
                                                @php $vCpcQvBg = $colors['color_cpc_qv_bg'] ?? '#ef4444'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vCpcQvBg }}">
                                                <input type="text" name="color_cpc_qv_bg" class="color-hex-input"
                                                    value="{{ $vCpcQvBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Quick View Icon</label>
                                            <div class="color-input-container">
                                                @php $vCpcQvIco = $colors['color_cpc_qv_icon'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vCpcQvIco }}">
                                                <input type="text" name="color_cpc_qv_icon" class="color-hex-input"
                                                    value="{{ $vCpcQvIco }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Quick View Hover BG</label>
                                            <div class="color-input-container">
                                                @php $vCpcQvHovBg = $colors['color_cpc_qv_hover_bg'] ?? '#1f2033'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vCpcQvHovBg }}">
                                                <input type="text" name="color_cpc_qv_hover_bg" class="color-hex-input"
                                                    value="{{ $vCpcQvHovBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Quick View Hover Icon</label>
                                            <div class="color-input-container">
                                                @php $vCpcQvHovIco = $colors['color_cpc_qv_hover_icon'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vCpcQvHovIco }}">
                                                <input type="text" name="color_cpc_qv_hover_icon" class="color-hex-input"
                                                    value="{{ $vCpcQvHovIco }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="sub-group-header">
                                    <i class="fas fa-tags text-success"></i>
                                    <span>2. Product Grid Card - Typography, Prices, Ratings &amp; Action Buttons</span>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Card Title Color</label>
                                            <div class="color-input-container">
                                                @php $vCpcTitle = $colors['color_cpc_title'] ?? '#1e293b'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vCpcTitle }}">
                                                <input type="text" name="color_cpc_title" class="color-hex-input"
                                                    value="{{ $vCpcTitle }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Card Title Hover</label>
                                            <div class="color-input-container">
                                                @php $vCpcTitleHov = $colors['color_cpc_title_hover'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vCpcTitleHov }}">
                                                <input type="text" name="color_cpc_title_hover" class="color-hex-input"
                                                    value="{{ $vCpcTitleHov }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Category Text</label>
                                            <div class="color-input-container">
                                                @php $vCpcCat = $colors['color_cpc_category'] ?? '#64748b'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vCpcCat }}">
                                                <input type="text" name="color_cpc_category" class="color-hex-input"
                                                    value="{{ $vCpcCat }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Category Hover</label>
                                            <div class="color-input-container">
                                                @php $vCpcCatHov = $colors['color_cpc_category_hover'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vCpcCatHov }}">
                                                <input type="text" name="color_cpc_category_hover" class="color-hex-input"
                                                    value="{{ $vCpcCatHov }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Current Sale Price</label>
                                            <div class="color-input-container">
                                                @php $vCpcPrice = $colors['color_cpc_price'] ?? '#0f172a'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vCpcPrice }}">
                                                <input type="text" name="color_cpc_price" class="color-hex-input"
                                                    value="{{ $vCpcPrice }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Strikethrough Old Price</label>
                                            <div class="color-input-container">
                                                @php $vCpcOldPrice = $colors['color_cpc_old_price'] ?? '#94a3b8'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vCpcOldPrice }}">
                                                <input type="text" name="color_cpc_old_price" class="color-hex-input"
                                                    value="{{ $vCpcOldPrice }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Review Stars</label>
                                            <div class="color-input-container">
                                                @php $vCpcRatingStar = $colors['color_cpc_rating_star'] ?? '#f59e0b'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vCpcRatingStar }}">
                                                <input type="text" name="color_cpc_rating_star" class="color-hex-input"
                                                    value="{{ $vCpcRatingStar }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Review Count Text</label>
                                            <div class="color-input-container">
                                                @php $vCpcRatingTxt = $colors['color_cpc_rating_text'] ?? '#64748b'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vCpcRatingTxt }}">
                                                <input type="text" name="color_cpc_rating_text" class="color-hex-input"
                                                    value="{{ $vCpcRatingTxt }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Add to Cart BG</label>
                                            <div class="color-input-container">
                                                @php $vCpcCartBg = $colors['color_cpc_cart_bg'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vCpcCartBg }}">
                                                <input type="text" name="color_cpc_cart_bg" class="color-hex-input"
                                                    value="{{ $vCpcCartBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Add to Cart Text</label>
                                            <div class="color-input-container">
                                                @php $vCpcCartTxt = $colors['color_cpc_cart_text'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vCpcCartTxt }}">
                                                <input type="text" name="color_cpc_cart_text" class="color-hex-input"
                                                    value="{{ $vCpcCartTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Cart Hover BG</label>
                                            <div class="color-input-container">
                                                @php $vCpcCartHovBg = $colors['color_cpc_cart_hover_bg'] ?? '#1d4ed8'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vCpcCartHovBg }}">
                                                <input type="text" name="color_cpc_cart_hover_bg" class="color-hex-input"
                                                    value="{{ $vCpcCartHovBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Cart Hover Text</label>
                                            <div class="color-input-container">
                                                @php $vCpcCartHovTxt = $colors['color_cpc_cart_hover_text'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vCpcCartHovTxt }}">
                                                <input type="text" name="color_cpc_cart_hover_text" class="color-hex-input"
                                                    value="{{ $vCpcCartHovTxt }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Order Now BG</label>
                                            <div class="color-input-container">
                                                @php $vCpcOrderBg = $colors['color_cpc_order_bg'] ?? '#0f172a'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vCpcOrderBg }}">
                                                <input type="text" name="color_cpc_order_bg" class="color-hex-input"
                                                    value="{{ $vCpcOrderBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Order Now Text</label>
                                            <div class="color-input-container">
                                                @php $vCpcOrderTxt = $colors['color_cpc_order_text'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vCpcOrderTxt }}">
                                                <input type="text" name="color_cpc_order_text" class="color-hex-input"
                                                    value="{{ $vCpcOrderTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Order Hover BG</label>
                                            <div class="color-input-container">
                                                @php $vCpcOrderHovBg = $colors['color_cpc_order_hover_bg'] ?? '#1e293b'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vCpcOrderHovBg }}">
                                                <input type="text" name="color_cpc_order_hover_bg" class="color-hex-input"
                                                    value="{{ $vCpcOrderHovBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Order Hover Text</label>
                                            <div class="color-input-container">
                                                @php $vCpcOrderHovTxt = $colors['color_cpc_order_hover_text'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vCpcOrderHovTxt }}">
                                                <input type="text" name="color_cpc_order_hover_text" class="color-hex-input"
                                                    value="{{ $vCpcOrderHovTxt }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Select Option BG</label>
                                            <div class="color-input-container">
                                                @php $vCpcOptBg = $colors['color_cpc_opt_bg'] ?? '#0f172a'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vCpcOptBg }}">
                                                <input type="text" name="color_cpc_opt_bg" class="color-hex-input"
                                                    value="{{ $vCpcOptBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Select Option Text</label>
                                            <div class="color-input-container">
                                                @php $vCpcOptTxt = $colors['color_cpc_opt_text'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vCpcOptTxt }}">
                                                <input type="text" name="color_cpc_opt_text" class="color-hex-input"
                                                    value="{{ $vCpcOptTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Stock Out Badge BG</label>
                                            <div class="color-input-container">
                                                @php $vCpcStockBg = $colors['color_cpc_stockout_bg'] ?? '#fee2e2'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vCpcStockBg }}">
                                                <input type="text" name="color_cpc_stockout_bg" class="color-hex-input"
                                                    value="{{ $vCpcStockBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Stock Out Text Color</label>
                                            <div class="color-input-container">
                                                @php $vCpcStockTxt = $colors['color_cpc_stockout_text'] ?? '#dc2626'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vCpcStockTxt }}">
                                                <input type="text" name="color_cpc_stockout_text" class="color-hex-input"
                                                    value="{{ $vCpcStockTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="sub-group-header">
                                    <i class="fas fa-eye text-info"></i>
                                    <span>3. Product Details &amp; Quick View - Gallery, Price Box &amp; Badges</span>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Details Wrapper BG</label>
                                            <div class="color-input-container">
                                                @php $vPdpWrapBg = $colors['color_pdp_wrapper_bg'] ?? '#f8fafc'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vPdpWrapBg }}">
                                                <input type="text" name="color_pdp_wrapper_bg" class="color-hex-input"
                                                    value="{{ $vPdpWrapBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Main Card BG</label>
                                            <div class="color-input-container">
                                                @php $vPdpCardBg = $colors['color_pdp_card_bg'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vPdpCardBg }}">
                                                <input type="text" name="color_pdp_card_bg" class="color-hex-input"
                                                    value="{{ $vPdpCardBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Main Card Border</label>
                                            <div class="color-input-container">
                                                @php $vPdpCardBrd = $colors['color_pdp_card_border'] ?? '#edf2f7'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vPdpCardBrd }}">
                                                <input type="text" name="color_pdp_card_border" class="color-hex-input"
                                                    value="{{ $vPdpCardBrd }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Zoom Lens Border</label>
                                            <div class="color-input-container">
                                                @php $vPdpLensBrd = $colors['color_pdp_lens_border'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vPdpLensBrd }}">
                                                <input type="text" name="color_pdp_lens_border" class="color-hex-input"
                                                    value="{{ $vPdpLensBrd }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Thumbnail Border (Normal)</label>
                                            <div class="color-input-container">
                                                @php $vPdpThumbBrd = $colors['color_pdp_thumb_border'] ?? '#e2e8f0'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vPdpThumbBrd }}">
                                                <input type="text" name="color_pdp_thumb_border" class="color-hex-input"
                                                    value="{{ $vPdpThumbBrd }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Thumbnail Border (Active)</label>
                                            <div class="color-input-container">
                                                @php $vPdpThumbActBrd = $colors['color_pdp_thumb_active_border'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vPdpThumbActBrd }}">
                                                <input type="text" name="color_pdp_thumb_active_border"
                                                    class="color-hex-input" value="{{ $vPdpThumbActBrd }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Thumb Nav Arrow BG</label>
                                            <div class="color-input-container">
                                                @php $vPdpArrowBg = $colors['color_pdp_thumb_arrow_bg'] ?? '#f8fafc'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vPdpArrowBg }}">
                                                <input type="text" name="color_pdp_thumb_arrow_bg" class="color-hex-input"
                                                    value="{{ $vPdpArrowBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Thumb Arrow Hover BG</label>
                                            <div class="color-input-container">
                                                @php $vPdpArrowHovBg = $colors['color_pdp_thumb_arrow_hover_bg'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vPdpArrowHovBg }}">
                                                <input type="text" name="color_pdp_thumb_arrow_hover_bg"
                                                    class="color-hex-input" value="{{ $vPdpArrowHovBg }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Price Box BG</label>
                                            <div class="color-input-container">
                                                @php $vPdpPriceBoxBg = $colors['color_pdp_price_box_bg'] ?? '#f8fafc'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vPdpPriceBoxBg }}">
                                                <input type="text" name="color_pdp_price_box_bg" class="color-hex-input"
                                                    value="{{ $vPdpPriceBoxBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Price Box Border</label>
                                            <div class="color-input-container">
                                                @php $vPdpPriceBoxBrd = $colors['color_pdp_price_box_border'] ?? '#edf2f7'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vPdpPriceBoxBrd }}">
                                                <input type="text" name="color_pdp_price_box_border" class="color-hex-input"
                                                    value="{{ $vPdpPriceBoxBrd }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="color-control-wrapper">
                                            <label>Detail Active Price</label>
                                            <div class="color-input-container">
                                                @php $vPdpPrice = $colors['color_pdp_price'] ?? '#dc2626'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vPdpPrice }}">
                                                <input type="text" name="color_pdp_price" class="color-hex-input"
                                                    value="{{ $vPdpPrice }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="sub-group-header">
                                    <i class="fas fa-sliders-h text-warning"></i>
                                    <span>4. Swatches, Stepper Quantity &amp; Purchase Action Buttons</span>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Swatch Chip BG</label>
                                            <div class="color-input-container">
                                                @php $vPdpSwBg = $colors['color_pdp_swatch_bg'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vPdpSwBg }}">
                                                <input type="text" name="color_pdp_swatch_bg" class="color-hex-input"
                                                    value="{{ $vPdpSwBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Swatch Chip Text</label>
                                            <div class="color-input-container">
                                                @php $vPdpSwTxt = $colors['color_pdp_swatch_text'] ?? '#1e293b'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vPdpSwTxt }}">
                                                <input type="text" name="color_pdp_swatch_text" class="color-hex-input"
                                                    value="{{ $vPdpSwTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Active Swatch BG</label>
                                            <div class="color-input-container">
                                                @php $vPdpSwActBg = $colors['color_pdp_swatch_active_bg'] ?? '#eff6ff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vPdpSwActBg }}">
                                                <input type="text" name="color_pdp_swatch_active_bg" class="color-hex-input"
                                                    value="{{ $vPdpSwActBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Active Swatch Border</label>
                                            <div class="color-input-container">
                                                @php $vPdpSwActBrd = $colors['color_pdp_swatch_active_border'] ?? '#2563eb'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vPdpSwActBrd }}">
                                                <input type="text" name="color_pdp_swatch_active_border"
                                                    class="color-hex-input" value="{{ $vPdpSwActBrd }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Qty Stepper BG</label>
                                            <div class="color-input-container">
                                                @php $vPdpQtyBtnBg = $colors['color_pdp_qty_btn_bg'] ?? '#f8fafc'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vPdpQtyBtnBg }}">
                                                <input type="text" name="color_pdp_qty_btn_bg" class="color-hex-input"
                                                    value="{{ $vPdpQtyBtnBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Qty Stepper Border</label>
                                            <div class="color-input-container">
                                                @php $vPdpQtyBrd = $colors['color_pdp_qty_border'] ?? '#cbd5e1'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vPdpQtyBrd }}">
                                                <input type="text" name="color_pdp_qty_border" class="color-hex-input"
                                                    value="{{ $vPdpQtyBrd }}">
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Buy Now Button (Normal & Hover Controls) -->
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Buy Now Button BG</label>
                                            <div class="color-input-container">
                                                @php $vPdpBuyBg = $colors['color_pdp_buynow_bg'] ?? '#0dcaf0'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vPdpBuyBg }}">
                                                <input type="text" name="color_pdp_buynow_bg" class="color-hex-input"
                                                    value="{{ $vPdpBuyBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Buy Now Button Text</label>
                                            <div class="color-input-container">
                                                @php $vPdpBuyTxt = $colors['color_pdp_buynow_text'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vPdpBuyTxt }}">
                                                <input type="text" name="color_pdp_buynow_text" class="color-hex-input"
                                                    value="{{ $vPdpBuyTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Buy Now Hover BG</label>
                                            <div class="color-input-container">
                                                @php $vPdpBuyHovBg = $colors['color_pdp_buynow_hover_bg'] ?? '#0bb5d8'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vPdpBuyHovBg }}">
                                                <input type="text" name="color_pdp_buynow_hover_bg" class="color-hex-input"
                                                    value="{{ $vPdpBuyHovBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Buy Now Hover Text</label>
                                            <div class="color-input-container">
                                                @php $vPdpBuyHovTxt = $colors['color_pdp_buynow_hover_text'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vPdpBuyHovTxt }}">
                                                <input type="text" name="color_pdp_buynow_hover_text"
                                                    class="color-hex-input" value="{{ $vPdpBuyHovTxt }}">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Add to Cart Button (Normal & Hover Controls) -->
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Add to Cart Button BG</label>
                                            <div class="color-input-container">
                                                @php $vPdpCartBg = $colors['color_pdp_cart_bg'] ?? '#ffc107'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vPdpCartBg }}">
                                                <input type="text" name="color_pdp_cart_bg" class="color-hex-input"
                                                    value="{{ $vPdpCartBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Add to Cart Text</label>
                                            <div class="color-input-container">
                                                @php $vPdpCartTxt = $colors['color_pdp_cart_text'] ?? '#212529'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vPdpCartTxt }}">
                                                <input type="text" name="color_pdp_cart_text" class="color-hex-input"
                                                    value="{{ $vPdpCartTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Add to Cart Hover BG</label>
                                            <div class="color-input-container">
                                                @php $vPdpCartHovBg = $colors['color_pdp_cart_hover_bg'] ?? '#e0a800'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vPdpCartHovBg }}">
                                                <input type="text" name="color_pdp_cart_hover_bg" class="color-hex-input"
                                                    value="{{ $vPdpCartHovBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>Add to Cart Hover Text</label>
                                            <div class="color-input-container">
                                                @php $vPdpCartHovTxt = $colors['color_pdp_cart_hover_text'] ?? '#212529'; @endphp
                                                <input type="color" class="color-picker-widget"
                                                    value="{{ $vPdpCartHovTxt }}">
                                                <input type="text" name="color_pdp_cart_hover_text" class="color-hex-input"
                                                    value="{{ $vPdpCartHovTxt }}">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- WhatsApp Button (Brand Color & Hover Controls Fixed) -->
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>WhatsApp Button BG</label>
                                            <div class="color-input-container">
                                                @php $vPdpWaBg = $colors['color_pdp_wa_bg'] ?? '#25D366'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vPdpWaBg }}">
                                                <input type="text" name="color_pdp_wa_bg" class="color-hex-input"
                                                    value="{{ $vPdpWaBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>WhatsApp Button Text</label>
                                            <div class="color-input-container">
                                                @php $vPdpWaTxt = $colors['color_pdp_wa_text'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vPdpWaTxt }}">
                                                <input type="text" name="color_pdp_wa_text" class="color-hex-input"
                                                    value="{{ $vPdpWaTxt }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>WhatsApp Hover BG</label>
                                            <div class="color-input-container">
                                                @php $vPdpWaHovBg = $colors['color_pdp_wa_hover_bg'] ?? '#128C7E'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vPdpWaHovBg }}">
                                                <input type="text" name="color_pdp_wa_hover_bg" class="color-hex-input"
                                                    value="{{ $vPdpWaHovBg }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="color-control-wrapper">
                                            <label>WhatsApp Hover Text</label>
                                            <div class="color-input-container">
                                                @php $vPdpWaHovTxt = $colors['color_pdp_wa_hover_text'] ?? '#ffffff'; @endphp
                                                <input type="color" class="color-picker-widget" value="{{ $vPdpWaHovTxt }}">
                                                <input type="text" name="color_pdp_wa_hover_text" class="color-hex-input"
                                                    value="{{ $vPdpWaHovTxt }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="sub-group-header">
                                        <i class="fas fa-shield-alt text-danger"></i>
                                        <span>5. Product Details - Delivery &amp; Trust Store Sidebar</span>
                                    </div>
                                    <div class="row g-2 mb-3">
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Sidebar Card BG</label>
                                                <div class="color-input-container">
                                                    @php $vPdpSideBg = $colors['color_pdp_sidebar_bg'] ?? '#ffffff'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vPdpSideBg }}">
                                                    <input type="text" name="color_pdp_sidebar_bg" class="color-hex-input"
                                                        value="{{ $vPdpSideBg }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Sidebar Border</label>
                                                <div class="color-input-container">
                                                    @php $vPdpSideBrd = $colors['color_pdp_sidebar_border'] ?? '#edf2f7'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vPdpSideBrd }}">
                                                    <input type="text" name="color_pdp_sidebar_border"
                                                        class="color-hex-input" value="{{ $vPdpSideBrd }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Verified Badge Color</label>
                                                <div class="color-input-container">
                                                    @php $vPdpSideVer = $colors['color_pdp_sidebar_verified_badge'] ?? '#16a34a'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vPdpSideVer }}">
                                                    <input type="text" name="color_pdp_sidebar_verified_badge"
                                                        class="color-hex-input" value="{{ $vPdpSideVer }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Feature Icon BG</label>
                                                <div class="color-input-container">
                                                    @php $vPdpFeatIcoBg = $colors['color_pdp_sidebar_feature_icon_bg'] ?? '#eff6ff'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vPdpFeatIcoBg }}">
                                                    <input type="text" name="color_pdp_sidebar_feature_icon_bg"
                                                        class="color-hex-input" value="{{ $vPdpFeatIcoBg }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="sub-group-header">
                                        <i class="fas fa-file-alt text-secondary"></i>
                                        <span>6. Description, Specifications Table &amp; Review Cards</span>
                                    </div>
                                    <div class="row g-2 mb-2">
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Tabs Active Border</label>
                                                <div class="color-input-container">
                                                    @php $vPdpTabActBrd = $colors['color_pdp_tab_link_active_border'] ?? '#2563eb'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vPdpTabActBrd }}">
                                                    <input type="text" name="color_pdp_tab_link_active_border"
                                                        class="color-hex-input" value="{{ $vPdpTabActBrd }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Tabs Active Text</label>
                                                <div class="color-input-container">
                                                    @php $vPdpTabActTxt = $colors['color_pdp_tab_link_active_text'] ?? '#2563eb'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vPdpTabActTxt }}">
                                                    <input type="text" name="color_pdp_tab_link_active_text"
                                                        class="color-hex-input" value="{{ $vPdpTabActTxt }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Specs Table TH BG</label>
                                                <div class="color-input-container">
                                                    @php $vPdpSpecThBg = $colors['color_pdp_spec_th_bg'] ?? '#f8fafc'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vPdpSpecThBg }}">
                                                    <input type="text" name="color_pdp_spec_th_bg" class="color-hex-input"
                                                        value="{{ $vPdpSpecThBg }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Review Box BG</label>
                                                <div class="color-input-container">
                                                    @php $vPdpRevBoxBg = $colors['color_pdp_review_box_bg'] ?? '#f8fafc'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vPdpRevBoxBg }}">
                                                    <input type="text" name="color_pdp_review_box_bg"
                                                        class="color-hex-input" value="{{ $vPdpRevBoxBg }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- SPECIFIC RESET BUTTON FOR PRODUCT -->
                                <div class="section-footer-box">
                                    <span class="small text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Restore only product card, details page and quick view modal colors to default.
                                    </span>
                                    <button type="button"
                                        class="btn btn-outline-warning btn-compact shadow-xs fw-bold px-3 text-dark border-warning"
                                        onclick="triggerResetProductModal()">
                                        <i class="fas fa-rotate-left me-1 text-warning"></i>
                                        <span>Reset Product Colors</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ========================================================================= -->
                    <!-- SECTION 4: APP MASTER LAYOUT & FLOATING WIDGETS (COLLAPSE)                -->
                    <!-- ========================================================================= -->
                    <div class="col-12">
                        <div class="theme-section-card">
                            <div class="theme-section-header d-flex justify-content-between align-items-center"
                                data-bs-toggle="collapse" data-bs-target="#collapseMasterSection" aria-expanded="false"
                                aria-controls="collapseMasterSection">
                                <div class="section-title-text">
                                    <i class="fas fa-desktop text-primary"></i>
                                    <span>App Master Layout &amp; Floating Widgets</span>
                                    <span class="badge bg-secondary-subtle text-secondary small fw-normal ms-2"
                                        style="font-size: 10px;">Global BG, Sticky Bar, Bottom Search &amp; Drawer
                                        Cart</span>
                                </div>
                                <button type="button" class="collapse-toggle-btn shadow-xs" aria-expanded="false">
                                    <i class="fas fa-chevron-down collapse-icon"></i>
                                </button>
                            </div>

                            <div class="collapse" id="collapseMasterSection">
                                <div class="p-3">
                                    <div class="sub-group-header">
                                        <i class="fas fa-globe text-primary"></i>
                                        <span>1. Global Body Background &amp; Scroll to Top</span>
                                    </div>
                                    <div class="row g-2 mb-3">
                                        <div class="col-6 col-md-4">
                                            <div class="color-control-wrapper">
                                                <label>Global Site Body BG</label>
                                                <div class="color-input-container">
                                                    @php $vSiteBg = $colors['color_site_bg'] ?? '#f8fafc'; @endphp
                                                    <input type="color" class="color-picker-widget" value="{{ $vSiteBg }}">
                                                    <input type="text" name="color_site_bg" class="color-hex-input"
                                                        value="{{ $vSiteBg }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-4">
                                            <div class="color-control-wrapper">
                                                <label>Scroll-to-Top Button BG</label>
                                                <div class="color-input-container">
                                                    @php $vStBg = $colors['color_app_scroll_top_bg'] ?? '#ea580c'; @endphp
                                                    <input type="color" class="color-picker-widget" value="{{ $vStBg }}">
                                                    <input type="text" name="color_app_scroll_top_bg"
                                                        class="color-hex-input" value="{{ $vStBg }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="color-control-wrapper">
                                                <label>Scroll-to-Top Icon Color</label>
                                                <div class="color-input-container">
                                                    @php $vStColor = $colors['color_app_scroll_top_color'] ?? '#ffffff'; @endphp
                                                    <input type="color" class="color-picker-widget" value="{{ $vStColor }}">
                                                    <input type="text" name="color_app_scroll_top_color"
                                                        class="color-hex-input" value="{{ $vStColor }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="sub-group-header">
                                        <i class="fas fa-bolt text-warning"></i>
                                        <span>2. Sticky Single Product Bar (Floating Bottom/Top)</span>
                                    </div>
                                    <div class="row g-2 mb-3">
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Sticky Bar BG</label>
                                                <div class="color-input-container">
                                                    @php $vSpbBg = $colors['color_app_spb_bg'] ?? '#ffffff'; @endphp
                                                    <input type="color" class="color-picker-widget" value="{{ $vSpbBg }}">
                                                    <input type="text" name="color_app_spb_bg" class="color-hex-input"
                                                        value="{{ $vSpbBg }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Sticky Bar Border</label>
                                                <div class="color-input-container">
                                                    @php $vSpbBrd = $colors['color_app_spb_border'] ?? 'rgba(226, 232, 240, 0.9)'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ str_starts_with($vSpbBrd, '#') ? $vSpbBrd : '#e2e8f0' }}">
                                                    <input type="text" name="color_app_spb_border" class="color-hex-input"
                                                        value="{{ $vSpbBrd }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Bar Title Text</label>
                                                <div class="color-input-container">
                                                    @php $vSpbTitle = $colors['color_app_spb_title'] ?? '#0f172a'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vSpbTitle }}">
                                                    <input type="text" name="color_app_spb_title" class="color-hex-input"
                                                        value="{{ $vSpbTitle }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Bar Active Price</label>
                                                <div class="color-input-container">
                                                    @php $vSpbPrice = $colors['color_app_spb_price'] ?? '#2563eb'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vSpbPrice }}">
                                                    <input type="text" name="color_app_spb_price" class="color-hex-input"
                                                        value="{{ $vSpbPrice }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Add to Cart BG</label>
                                                <div class="color-input-container">
                                                    @php $vSpbCartBg = $colors['color_app_spb_cart_btn_bg'] ?? '#f8fafc'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vSpbCartBg }}">
                                                    <input type="text" name="color_app_spb_cart_btn_bg"
                                                        class="color-hex-input" value="{{ $vSpbCartBg }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Add to Cart Text</label>
                                                <div class="color-input-container">
                                                    @php $vSpbCartTxt = $colors['color_app_spb_cart_btn_text'] ?? '#0f172a'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vSpbCartTxt }}">
                                                    <input type="text" name="color_app_spb_cart_btn_text"
                                                        class="color-hex-input" value="{{ $vSpbCartTxt }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Buy Now Button BG</label>
                                                <div class="color-input-container">
                                                    @php $vSpbBuyBg = $colors['color_app_spb_buynow_bg'] ?? '#0dcaf0'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vSpbBuyBg }}">
                                                    <input type="text" name="color_app_spb_buynow_bg"
                                                        class="color-hex-input" value="{{ $vSpbBuyBg }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Buy Now Button Text</label>
                                                <div class="color-input-container">
                                                    @php $vSpbBuyTxt = $colors['color_app_spb_buynow_text'] ?? '#ffffff'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vSpbBuyTxt }}">
                                                    <input type="text" name="color_app_spb_buynow_text"
                                                        class="color-hex-input" value="{{ $vSpbBuyTxt }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="sub-group-header">
                                        <i class="fas fa-search text-success"></i>
                                        <span>3. Bottom Navigation Search Modal &amp; Drawer Cart</span>
                                    </div>
                                    <div class="row g-2 mb-2">
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Search Modal BG</label>
                                                <div class="color-input-container">
                                                    @php $vAppSrchBg = $colors['color_app_search_modal_bg'] ?? '#ffffff'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vAppSrchBg }}">
                                                    <input type="text" name="color_app_search_modal_bg"
                                                        class="color-hex-input" value="{{ $vAppSrchBg }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Search Result Price</label>
                                                <div class="color-input-container">
                                                    @php $vAppSrchPrice = $colors['color_app_search_price'] ?? '#2563eb'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vAppSrchPrice }}">
                                                    <input type="text" name="color_app_search_price" class="color-hex-input"
                                                        value="{{ $vAppSrchPrice }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Search Item Cart BG</label>
                                                <div class="color-input-container">
                                                    @php $vAppSrchCartBg = $colors['color_app_search_cart_btn_bg'] ?? '#2563eb'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vAppSrchCartBg }}">
                                                    <input type="text" name="color_app_search_cart_btn_bg"
                                                        class="color-hex-input" value="{{ $vAppSrchCartBg }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Drawer Cart Icon</label>
                                                <div class="color-input-container">
                                                    @php $vAppDrwIco = $colors['color_app_drawer_icon'] ?? '#2563eb'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vAppDrwIco }}">
                                                    <input type="text" name="color_app_drawer_icon" class="color-hex-input"
                                                        value="{{ $vAppDrwIco }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Drawer Total Price</label>
                                                <div class="color-input-container">
                                                    @php $vAppDrwTotal = $colors['color_app_drawer_total_price'] ?? '#dc2626'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vAppDrwTotal }}">
                                                    <input type="text" name="color_app_drawer_total_price"
                                                        class="color-hex-input" value="{{ $vAppDrwTotal }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Drawer Checkout BG</label>
                                                <div class="color-input-container">
                                                    @php $vAppDrwChkBg = $colors['color_app_drawer_checkout_bg'] ?? '#0f172a'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vAppDrwChkBg }}">
                                                    <input type="text" name="color_app_drawer_checkout_bg"
                                                        class="color-hex-input" value="{{ $vAppDrwChkBg }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Drawer Checkout Text</label>
                                                <div class="color-input-container">
                                                    @php $vAppDrwChkTxt = $colors['color_app_drawer_checkout_text'] ?? '#ffffff'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vAppDrwChkTxt }}">
                                                    <input type="text" name="color_app_drawer_checkout_text"
                                                        class="color-hex-input" value="{{ $vAppDrwChkTxt }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>QuickView Spinner</label>
                                                <div class="color-input-container">
                                                    @php $vAppQvSpin = $colors['color_app_quickview_spinner'] ?? '#2563eb'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vAppQvSpin }}">
                                                    <input type="text" name="color_app_quickview_spinner"
                                                        class="color-hex-input" value="{{ $vAppQvSpin }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- SPECIFIC RESET BUTTON FOR MASTER -->
                                <div class="section-footer-box">
                                    <span class="small text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Restore global body, sticky bar, search modal and cart drawer colors to default.
                                    </span>
                                    <button type="button"
                                        class="btn btn-outline-warning btn-compact shadow-xs fw-bold px-3 text-dark border-warning"
                                        onclick="triggerResetMasterModal()">
                                        <i class="fas fa-rotate-left me-1 text-warning"></i>
                                        <span>Reset Master Colors</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ========================================================================= -->
                    <!-- SECTION 5: HOME PAGE SECTIONS & WIDGETS (COLLAPSE)                        -->
                    <!-- ========================================================================= -->
                    <div class="col-12">
                        <div class="theme-section-card">
                            <div class="theme-section-header d-flex justify-content-between align-items-center"
                                data-bs-toggle="collapse" data-bs-target="#collapseHomeSection" aria-expanded="false"
                                aria-controls="collapseHomeSection">
                                <div class="section-title-text">
                                    <i class="fas fa-home text-info"></i>
                                    <span>Home Page Sections &amp; Widgets</span>
                                    <span class="badge bg-secondary-subtle text-secondary small fw-normal ms-2"
                                        style="font-size: 10px;">Category Sidebar, Deals Timer, Popular Slider &amp;
                                        Featured</span>
                                </div>
                                <button type="button" class="collapse-toggle-btn shadow-xs" aria-expanded="false">
                                    <i class="fas fa-chevron-down collapse-icon"></i>
                                </button>
                            </div>

                            <div class="collapse" id="collapseHomeSection">
                                <div class="p-3">
                                    <div class="sub-group-header">
                                        <i class="fas fa-bars text-primary"></i>
                                        <span>1. Home Category Sidebar Widget &amp; Flyout Menu</span>
                                    </div>
                                    <div class="row g-2 mb-3">
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Widget Background</label>
                                                <div class="color-input-container">
                                                    @php $vHmCatBg = $colors['color_home_cat_menu_bg'] ?? '#ffffff'; @endphp
                                                    <input type="color" class="color-picker-widget" value="{{ $vHmCatBg }}">
                                                    <input type="text" name="color_home_cat_menu_bg" class="color-hex-input"
                                                        value="{{ $vHmCatBg }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Item Text Color</label>
                                                <div class="color-input-container">
                                                    @php $vHmCatTxt = $colors['color_home_cat_item_text'] ?? '#334155'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vHmCatTxt }}">
                                                    <input type="text" name="color_home_cat_item_text"
                                                        class="color-hex-input" value="{{ $vHmCatTxt }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Item Hover BG</label>
                                                <div class="color-input-container">
                                                    @php $vHmCatHovBg = $colors['color_home_cat_item_hover_bg'] ?? '#f1f5f9'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vHmCatHovBg }}">
                                                    <input type="text" name="color_home_cat_item_hover_bg"
                                                        class="color-hex-input" value="{{ $vHmCatHovBg }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Item Hover Text</label>
                                                <div class="color-input-container">
                                                    @php $vHmCatHovTxt = $colors['color_home_cat_item_hover_text'] ?? '#2563eb'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vHmCatHovTxt }}">
                                                    <input type="text" name="color_home_cat_item_hover_text"
                                                        class="color-hex-input" value="{{ $vHmCatHovTxt }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="sub-group-header">
                                        <i class="fas fa-clock text-danger"></i>
                                        <span>2. Hot Deals &amp; Countdown Timer Box</span>
                                    </div>
                                    <div class="row g-2 mb-3">
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Deals Card BG</label>
                                                <div class="color-input-container">
                                                    @php $vHmDealsCardBg = $colors['color_home_deals_card_bg'] ?? '#ffffff'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vHmDealsCardBg }}">
                                                    <input type="text" name="color_home_deals_card_bg"
                                                        class="color-hex-input" value="{{ $vHmDealsCardBg }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Deals Card Border</label>
                                                <div class="color-input-container">
                                                    @php $vHmDealsCardBrd = $colors['color_home_deals_card_border'] ?? '#fee2e2'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vHmDealsCardBrd }}">
                                                    <input type="text" name="color_home_deals_card_border"
                                                        class="color-hex-input" value="{{ $vHmDealsCardBrd }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Timer Box BG</label>
                                                <div class="color-input-container">
                                                    @php $vHmTimerBg = $colors['color_home_deals_timer_box_bg'] ?? '#0f172a'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vHmTimerBg }}">
                                                    <input type="text" name="color_home_deals_timer_box_bg"
                                                        class="color-hex-input" value="{{ $vHmTimerBg }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Timer Number Tile BG</label>
                                                <div class="color-input-container">
                                                    @php $vHmTileBg = $colors['color_home_deals_tile_bg'] ?? '#ffffff'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vHmTileBg }}">
                                                    <input type="text" name="color_home_deals_tile_bg"
                                                        class="color-hex-input" value="{{ $vHmTileBg }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="sub-group-header">
                                        <i class="fas fa-shapes text-warning"></i>
                                        <span>3. Popular Categories Slider &amp; Featured Header</span>
                                    </div>
                                    <div class="row g-2 mb-2">
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Pop Category Card BG</label>
                                                <div class="color-input-container">
                                                    @php $vHmPopCardBg = $colors['color_home_pop_card_bg'] ?? '#ffffff'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vHmPopCardBg }}">
                                                    <input type="text" name="color_home_pop_card_bg" class="color-hex-input"
                                                        value="{{ $vHmPopCardBg }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Category Title Color</label>
                                                <div class="color-input-container">
                                                    @php $vHmPopTitle = $colors['color_home_pop_title'] ?? '#0f172a'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vHmPopTitle }}">
                                                    <input type="text" name="color_home_pop_title" class="color-hex-input"
                                                        value="{{ $vHmPopTitle }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Featured Filter Active BG</label>
                                                <div class="color-input-container">
                                                    @php $vHmFeatActBg = $colors['color_home_feat_filter_active_bg'] ?? '#2563eb'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vHmFeatActBg }}">
                                                    <input type="text" name="color_home_feat_filter_active_bg"
                                                        class="color-hex-input" value="{{ $vHmFeatActBg }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Featured Filter Active Text</label>
                                                <div class="color-input-container">
                                                    @php $vHmFeatActTxt = $colors['color_home_feat_filter_active_text'] ?? '#ffffff'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vHmFeatActTxt }}">
                                                    <input type="text" name="color_home_feat_filter_active_text"
                                                        class="color-hex-input" value="{{ $vHmFeatActTxt }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- SPECIFIC RESET BUTTON FOR HOME -->
                                <div class="section-footer-box">
                                    <span class="small text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Restore home page categories, deals and featured filters to default.
                                    </span>
                                    <button type="button"
                                        class="btn btn-outline-warning btn-compact shadow-xs fw-bold px-3 text-dark border-warning"
                                        onclick="triggerResetHomeModal()">
                                        <i class="fas fa-rotate-left me-1 text-warning"></i>
                                        <span>Reset Home Colors</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ========================================================================= -->
                    <!-- SECTION 6: SHOP PAGE & CATALOG STYLES (COLLAPSE)                          -->
                    <!-- ========================================================================= -->
                    <div class="col-12">
                        <div class="theme-section-card">
                            <div class="theme-section-header d-flex justify-content-between align-items-center"
                                data-bs-toggle="collapse" data-bs-target="#collapseShopSection" aria-expanded="false"
                                aria-controls="collapseShopSection">
                                <div class="section-title-text">
                                    <i class="fas fa-store text-success"></i>
                                    <span>Shop Page &amp; Catalog Styles</span>
                                    <span class="badge bg-secondary-subtle text-secondary small fw-normal ms-2"
                                        style="font-size: 10px;">Top Filter Bar, Sort Dropdown &amp; Pagination</span>
                                </div>
                                <button type="button" class="collapse-toggle-btn shadow-xs" aria-expanded="false">
                                    <i class="fas fa-chevron-down collapse-icon"></i>
                                </button>
                            </div>

                            <div class="collapse" id="collapseShopSection">
                                <div class="p-3">
                                    <div class="sub-group-header">
                                        <i class="fas fa-sliders-h text-primary"></i>
                                        <span>1. Shop Header &amp; Sort Control Bar</span>
                                    </div>
                                    <div class="row g-2 mb-3">
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Shop Page BG</label>
                                                <div class="color-input-container">
                                                    @php $vShpPageBg = $colors['color_shop_page_bg'] ?? '#f8fafc'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vShpPageBg }}">
                                                    <input type="text" name="color_shop_page_bg" class="color-hex-input"
                                                        value="{{ $vShpPageBg }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Top Control Bar BG</label>
                                                <div class="color-input-container">
                                                    @php $vShpTopBg = $colors['color_shop_top_bg'] ?? '#ffffff'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vShpTopBg }}">
                                                    <input type="text" name="color_shop_top_bg" class="color-hex-input"
                                                        value="{{ $vShpTopBg }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Shop Heading Text</label>
                                                <div class="color-input-container">
                                                    @php $vShpHeading = $colors['color_shop_heading'] ?? '#0f172a'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vShpHeading }}">
                                                    <input type="text" name="color_shop_heading" class="color-hex-input"
                                                        value="{{ $vShpHeading }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Showing Counter Text</label>
                                                <div class="color-input-container">
                                                    @php $vShpShowTxt = $colors['color_shop_showing_text'] ?? '#64748b'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vShpShowTxt }}">
                                                    <input type="text" name="color_shop_showing_text"
                                                        class="color-hex-input" value="{{ $vShpShowTxt }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="sub-group-header">
                                        <i class="fas fa-list-ol text-info"></i>
                                        <span>2. Shop Pagination Card &amp; Navigation Buttons</span>
                                    </div>
                                    <div class="row g-2 mb-2">
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Pagination Card BG</label>
                                                <div class="color-input-container">
                                                    @php $vShpPagCardBg = $colors['color_shop_pag_card_bg'] ?? '#ffffff'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vShpPagCardBg }}">
                                                    <input type="text" name="color_shop_pag_card_bg" class="color-hex-input"
                                                        value="{{ $vShpPagCardBg }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Pagination Button BG</label>
                                                <div class="color-input-container">
                                                    @php $vShpPagBtnBg = $colors['color_shop_pag_btn_bg'] ?? '#ffffff'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vShpPagBtnBg }}">
                                                    <input type="text" name="color_shop_pag_btn_bg" class="color-hex-input"
                                                        value="{{ $vShpPagBtnBg }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Active Page Link BG</label>
                                                <div class="color-input-container">
                                                    @php $vShpPagActBg = $colors['color_shop_pag_btn_active_bg'] ?? '#2563eb'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vShpPagActBg }}">
                                                    <input type="text" name="color_shop_pag_btn_active_bg"
                                                        class="color-hex-input" value="{{ $vShpPagActBg }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Active Page Link Text</label>
                                                <div class="color-input-container">
                                                    @php $vShpPagActTxt = $colors['color_shop_pag_btn_active_text'] ?? '#ffffff'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vShpPagActTxt }}">
                                                    <input type="text" name="color_shop_pag_btn_active_text"
                                                        class="color-hex-input" value="{{ $vShpPagActTxt }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- SPECIFIC RESET BUTTON FOR SHOP -->
                                <div class="section-footer-box">
                                    <span class="small text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Restore shop controls, catalog header and pagination to default.
                                    </span>
                                    <button type="button"
                                        class="btn btn-outline-warning btn-compact shadow-xs fw-bold px-3 text-dark border-warning"
                                        onclick="triggerResetShopModal()">
                                        <i class="fas fa-rotate-left me-1 text-warning"></i>
                                        <span>Reset Shop Colors</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ========================================================================= -->
                    <!-- SECTION 7: CUSTOM DYNAMIC PAGES (COLLAPSE)                                -->
                    <!-- ========================================================================= -->
                    <div class="col-12">
                        <div class="theme-section-card">
                            <div class="theme-section-header d-flex justify-content-between align-items-center"
                                data-bs-toggle="collapse" data-bs-target="#collapsePagesSection" aria-expanded="false"
                                aria-controls="collapsePagesSection">
                                <div class="section-title-text">
                                    <i class="fas fa-file-alt text-secondary"></i>
                                    <span>Custom Dynamic Pages</span>
                                    <span class="badge bg-secondary-subtle text-secondary small fw-normal ms-2"
                                        style="font-size: 10px;">Page Content Card, Title, Sidebars &amp; Breadcrumb</span>
                                </div>
                                <button type="button" class="collapse-toggle-btn shadow-xs" aria-expanded="false">
                                    <i class="fas fa-chevron-down collapse-icon"></i>
                                </button>
                            </div>

                            <div class="collapse" id="collapsePagesSection">
                                <div class="p-3">
                                    <div class="sub-group-header">
                                        <i class="fas fa-columns text-primary"></i>
                                        <span>1. Custom Page Structure &amp; Typography</span>
                                    </div>
                                    <div class="row g-2 mb-2">
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Page Background</label>
                                                <div class="color-input-container">
                                                    @php $vPgBg = $colors['color_page_bg'] ?? '#f8fafc'; @endphp
                                                    <input type="color" class="color-picker-widget" value="{{ $vPgBg }}">
                                                    <input type="text" name="color_page_bg" class="color-hex-input"
                                                        value="{{ $vPgBg }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Page Title Color</label>
                                                <div class="color-input-container">
                                                    @php $vPgTitle = $colors['color_page_title'] ?? '#0f172a'; @endphp
                                                    <input type="color" class="color-picker-widget" value="{{ $vPgTitle }}">
                                                    <input type="text" name="color_page_title" class="color-hex-input"
                                                        value="{{ $vPgTitle }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Content Card BG</label>
                                                <div class="color-input-container">
                                                    @php $vPgCardBg = $colors['color_page_card_bg'] ?? '#ffffff'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vPgCardBg }}">
                                                    <input type="text" name="color_page_card_bg" class="color-hex-input"
                                                        value="{{ $vPgCardBg }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Sidebar Header Text</label>
                                                <div class="color-input-container">
                                                    @php $vPgSideHead = $colors['color_page_sidebar_header_text'] ?? '#0f172a'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vPgSideHead }}">
                                                    <input type="text" name="color_page_sidebar_header_text"
                                                        class="color-hex-input" value="{{ $vPgSideHead }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- SPECIFIC RESET BUTTON FOR PAGES -->
                                <div class="section-footer-box">
                                    <span class="small text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Restore custom dynamic page layout and sidebar colors to default.
                                    </span>
                                    <button type="button"
                                        class="btn btn-outline-warning btn-compact shadow-xs fw-bold px-3 text-dark border-warning"
                                        onclick="triggerResetPagesModal()">
                                        <i class="fas fa-rotate-left me-1 text-warning"></i>
                                        <span>Reset Page Colors</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ========================================================================= -->
                    <!-- SECTION 8: BLOG PAGES & DISCUSSION (COLLAPSE)                             -->
                    <!-- ========================================================================= -->
                    <div class="col-12">
                        <div class="theme-section-card">
                            <div class="theme-section-header d-flex justify-content-between align-items-center"
                                data-bs-toggle="collapse" data-bs-target="#collapseBlogsSection" aria-expanded="false"
                                aria-controls="collapseBlogsSection">
                                <div class="section-title-text">
                                    <i class="fas fa-newspaper text-danger"></i>
                                    <span>Blog Pages &amp; Discussion</span>
                                    <span class="badge bg-secondary-subtle text-secondary small fw-normal ms-2"
                                        style="font-size: 10px;">Hero Banner, Category Chips, Cards &amp; Facebook
                                        Comments</span>
                                </div>
                                <button type="button" class="collapse-toggle-btn shadow-xs" aria-expanded="false">
                                    <i class="fas fa-chevron-down collapse-icon"></i>
                                </button>
                            </div>

                            <div class="collapse" id="collapseBlogsSection">
                                <div class="p-3">
                                    <div class="sub-group-header">
                                        <i class="fas fa-rss text-primary"></i>
                                        <span>1. Blog Hero Banner &amp; Category Chips</span>
                                    </div>
                                    <div class="row g-2 mb-3">
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Blog Page BG</label>
                                                <div class="color-input-container">
                                                    @php $vBlgPageBg = $colors['color_blog_page_bg'] ?? '#f8fafc'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vBlgPageBg }}">
                                                    <input type="text" name="color_blog_page_bg" class="color-hex-input"
                                                        value="{{ $vBlgPageBg }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Hero Banner BG</label>
                                                <div class="color-input-container">
                                                    @php $vBlgHeroBg = $colors['color_blog_hero_bg'] ?? '#f8fafc'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vBlgHeroBg }}">
                                                    <input type="text" name="color_blog_hero_bg" class="color-hex-input"
                                                        value="{{ $vBlgHeroBg }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Category Chip Active BG</label>
                                                <div class="color-input-container">
                                                    @php $vBlgChipActBg = $colors['color_blog_chip_active_bg'] ?? '#2563eb'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vBlgChipActBg }}">
                                                    <input type="text" name="color_blog_chip_active_bg"
                                                        class="color-hex-input" value="{{ $vBlgChipActBg }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Category Chip Active Text</label>
                                                <div class="color-input-container">
                                                    @php $vBlgChipActTxt = $colors['color_blog_chip_active_text'] ?? '#ffffff'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vBlgChipActTxt }}">
                                                    <input type="text" name="color_blog_chip_active_text"
                                                        class="color-hex-input" value="{{ $vBlgChipActTxt }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="sub-group-header">
                                        <i class="fas fa-comments text-success"></i>
                                        <span>2. Blog Cards &amp; Facebook-Style Comments</span>
                                    </div>
                                    <div class="row g-2 mb-2">
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Card Title Color</label>
                                                <div class="color-input-container">
                                                    @php $vBlgTitle = $colors['color_blog_title'] ?? '#0f172a'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vBlgTitle }}">
                                                    <input type="text" name="color_blog_title" class="color-hex-input"
                                                        value="{{ $vBlgTitle }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Read More Link Color</label>
                                                <div class="color-input-container">
                                                    @php $vBlgRead = $colors['color_blog_readmore'] ?? '#2563eb'; @endphp
                                                    <input type="color" class="color-picker-widget" value="{{ $vBlgRead }}">
                                                    <input type="text" name="color_blog_readmore" class="color-hex-input"
                                                        value="{{ $vBlgRead }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Comment Bubble BG</label>
                                                <div class="color-input-container">
                                                    @php $vBlgBubbleBg = $colors['color_blog_comment_bubble_bg'] ?? '#f1f5f9'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vBlgBubbleBg }}">
                                                    <input type="text" name="color_blog_comment_bubble_bg"
                                                        class="color-hex-input" value="{{ $vBlgBubbleBg }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="color-control-wrapper">
                                                <label>Comment Post Button BG</label>
                                                <div class="color-input-container">
                                                    @php $vBlgComBtnBg = $colors['color_blog_comment_btn_bg'] ?? '#2563eb'; @endphp
                                                    <input type="color" class="color-picker-widget"
                                                        value="{{ $vBlgComBtnBg }}">
                                                    <input type="text" name="color_blog_comment_btn_bg"
                                                        class="color-hex-input" value="{{ $vBlgComBtnBg }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- SPECIFIC RESET BUTTON FOR BLOGS -->
                                <div class="section-footer-box">
                                    <span class="small text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Restore blog articles, chips and comments colors to default.
                                    </span>
                                    <button type="button"
                                        class="btn btn-outline-warning btn-compact shadow-xs fw-bold px-3 text-dark border-warning"
                                        onclick="triggerResetBlogsModal()">
                                        <i class="fas fa-rotate-left me-1 text-warning"></i>
                                        <span>Reset Blog Colors</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ========================================================================= -->
                <!-- BOTTOM ACTION BUTTONS: SAVE & GLOBAL RESET                                -->
                <!-- ========================================================================= -->
                <div class="bottom-action-card mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-danger btn-compact shadow-xs fw-bold px-3"
                            onclick="triggerResetColorsModal()">
                            <i class="fas fa-trash-alt me-1"></i>
                            <span>Reset All Colors</span>
                        </button>
                    </div>
                    <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-4"
                        style="min-height: 35px; font-size: 12px;">
                        <i class="fas fa-check-circle me-1"></i>
                        <span>Save Color Changes</span>
                    </button>
                </div>
        </form>
    </div>

    <!-- ========================================================================= -->
    <!-- CONFIRMATION MODALS                                                       -->
    <!-- ========================================================================= -->

    <!-- MODAL 1: RESET HEADER -->
    <div class="modal fade colors-modal-scope" id="headerResetConfirmModal" tabindex="-1" aria-hidden="true"
        style="z-index: 108500;">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg text-center p-3">
                <div class="modal-body p-1">
                    <div class="mb-2 text-warning"><i class="fas fa-arrow-rotate-left fa-2x"></i></div>
                    <h6 class="fw-bold text-dark mb-1" style="font-size: 14.5px;">Reset Header Colors?</h6>
                    <p class="text-muted small mb-3" style="font-size: 12px; line-height: 1.45;">Revert all header
                        background, navigation, search and mobile drawer colors to default.</p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light btn-compact border"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-warning btn-compact shadow-xs fw-bold text-dark"
                            id="btnExecuteResetHeader">Yes, Reset Header</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 2: RESET FOOTER -->
    <div class="modal fade colors-modal-scope" id="footerResetConfirmModal" tabindex="-1" aria-hidden="true"
        style="z-index: 108500;">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg text-center p-3">
                <div class="modal-body p-1">
                    <div class="mb-2 text-warning"><i class="fas fa-arrow-rotate-left fa-2x"></i></div>
                    <h6 class="fw-bold text-dark mb-1" style="font-size: 14.5px;">Reset Footer Colors?</h6>
                    <p class="text-muted small mb-3" style="font-size: 12px; line-height: 1.45;">Revert all footer
                        background, links, payments, and copyright colors to default.</p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light btn-compact border"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-warning btn-compact shadow-xs fw-bold text-dark"
                            id="btnExecuteResetFooter">Yes, Reset Footer</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 3: RESET PRODUCT MODULE -->
    <div class="modal fade colors-modal-scope" id="productResetConfirmModal" tabindex="-1" aria-hidden="true"
        style="z-index: 108500;">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg text-center p-3">
                <div class="modal-body p-1">
                    <div class="mb-2 text-warning"><i class="fas fa-arrow-rotate-left fa-2x"></i></div>
                    <h6 class="fw-bold text-dark mb-1" style="font-size: 14.5px;">Reset Product Colors?</h6>
                    <p class="text-muted small mb-3" style="font-size: 12px; line-height: 1.45;">Revert all product card,
                        details page and quick view modal colors to default.</p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light btn-compact border"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-warning btn-compact shadow-xs fw-bold text-dark"
                            id="btnExecuteResetProduct">Yes, Reset Product</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 4: RESET APP MASTER -->
    <div class="modal fade colors-modal-scope" id="masterResetConfirmModal" tabindex="-1" aria-hidden="true"
        style="z-index: 108500;">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg text-center p-3">
                <div class="modal-body p-1">
                    <div class="mb-2 text-warning"><i class="fas fa-arrow-rotate-left fa-2x"></i></div>
                    <h6 class="fw-bold text-dark mb-1" style="font-size: 14.5px;">Reset Master Layout?</h6>
                    <p class="text-muted small mb-3" style="font-size: 12px; line-height: 1.45;">Revert global site
                        background, sticky product bar and cart drawer colors to default.</p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light btn-compact border"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-warning btn-compact shadow-xs fw-bold text-dark"
                            id="btnExecuteResetMaster">Yes, Reset Master</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 5: RESET HOME -->
    <div class="modal fade colors-modal-scope" id="homeResetConfirmModal" tabindex="-1" aria-hidden="true"
        style="z-index: 108500;">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg text-center p-3">
                <div class="modal-body p-1">
                    <div class="mb-2 text-warning"><i class="fas fa-arrow-rotate-left fa-2x"></i></div>
                    <h6 class="fw-bold text-dark mb-1" style="font-size: 14.5px;">Reset Home Colors?</h6>
                    <p class="text-muted small mb-3" style="font-size: 12px; line-height: 1.45;">Revert home categories,
                        deals timer and popular category slider colors to default.</p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light btn-compact border"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-warning btn-compact shadow-xs fw-bold text-dark"
                            id="btnExecuteResetHome">Yes, Reset Home</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 6: RESET SHOP -->
    <div class="modal fade colors-modal-scope" id="shopResetConfirmModal" tabindex="-1" aria-hidden="true"
        style="z-index: 108500;">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg text-center p-3">
                <div class="modal-body p-1">
                    <div class="mb-2 text-warning"><i class="fas fa-arrow-rotate-left fa-2x"></i></div>
                    <h6 class="fw-bold text-dark mb-1" style="font-size: 14.5px;">Reset Shop Colors?</h6>
                    <p class="text-muted small mb-3" style="font-size: 12px; line-height: 1.45;">Revert shop top controls,
                        sort bar and pagination colors to default.</p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light btn-compact border"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-warning btn-compact shadow-xs fw-bold text-dark"
                            id="btnExecuteResetShop">Yes, Reset Shop</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 7: RESET PAGES -->
    <div class="modal fade colors-modal-scope" id="pagesResetConfirmModal" tabindex="-1" aria-hidden="true"
        style="z-index: 108500;">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg text-center p-3">
                <div class="modal-body p-1">
                    <div class="mb-2 text-warning"><i class="fas fa-arrow-rotate-left fa-2x"></i></div>
                    <h6 class="fw-bold text-dark mb-1" style="font-size: 14.5px;">Reset Page Colors?</h6>
                    <p class="text-muted small mb-3" style="font-size: 12px; line-height: 1.45;">Revert custom dynamic page
                        body, card and sidebar colors to default.</p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light btn-compact border"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-warning btn-compact shadow-xs fw-bold text-dark"
                            id="btnExecuteResetPages">Yes, Reset Pages</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 8: RESET BLOGS -->
    <div class="modal fade colors-modal-scope" id="blogsResetConfirmModal" tabindex="-1" aria-hidden="true"
        style="z-index: 108500;">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg text-center p-3">
                <div class="modal-body p-1">
                    <div class="mb-2 text-warning"><i class="fas fa-arrow-rotate-left fa-2x"></i></div>
                    <h6 class="fw-bold text-dark mb-1" style="font-size: 14.5px;">Reset Blog Colors?</h6>
                    <p class="text-muted small mb-3" style="font-size: 12px; line-height: 1.45;">Revert blog hero banner,
                        category chips, cards and comment colors to default.</p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light btn-compact border"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-warning btn-compact shadow-xs fw-bold text-dark"
                            id="btnExecuteResetBlogs">Yes, Reset Blogs</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 9: RESTORE ALL COLORS -->
    <div class="modal fade colors-modal-scope" id="colorsResetConfirmModal" tabindex="-1" aria-hidden="true"
        style="z-index: 108500;">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg text-center p-3">
                <div class="modal-body p-1">
                    <div class="mb-2 text-danger"><i class="fas fa-triangle-exclamation fa-2x"></i></div>
                    <h6 class="fw-bold text-dark mb-1" style="font-size: 14.5px;">Restore All Colors?</h6>
                    <p class="text-muted small mb-3" style="font-size: 12px; line-height: 1.45;">Revert all custom colors
                        across the entire storefront to defaults.</p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light btn-compact border"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger btn-compact shadow-xs fw-bold"
                            id="btnExecuteResetColors">Yes, Restore All</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Two-way sync between color picker & hex input
            document.querySelectorAll('.color-input-container').forEach(container => {
                const picker = container.querySelector('.color-picker-widget');
                const textInput = container.querySelector('.color-hex-input');

                if (picker && textInput) {
                    picker.addEventListener('input', function () {
                        textInput.value = this.value.toLowerCase();
                    });

                    textInput.addEventListener('input', function () {
                        let val = this.value.trim();
                        if (!val.startsWith('#') && val.length > 0) {
                            val = '#' + val;
                        }
                        const isValidHex = /^#([0-9A-F]{3}){1,2}$/i.test(val);
                        if (isValidHex) {
                            let finalHex = val;
                            if (val.length === 4) {
                                finalHex = '#' + val[1] + val[1] + val[2] + val[2] + val[3] + val[3];
                            }
                            picker.value = finalHex.toLowerCase();
                        }
                    });

                    textInput.addEventListener('blur', function () {
                        let val = this.value.trim();
                        if (!val.startsWith('#') && val.length > 0) {
                            val = '#' + val;
                        }
                        const isValidHex = /^#([0-9A-F]{3}){1,2}$/i.test(val);
                        if (isValidHex) {
                            if (val.length === 4) {
                                val = '#' + val[1] + val[1] + val[2] + val[2] + val[3] + val[3];
                            }
                            this.value = val.toLowerCase();
                        } else {
                            this.value = picker.value;
                        }
                    });
                }
            });

            // 2. Synchronize toggle button arrow icon on collapse show/hide
            document.querySelectorAll('#themeColorsRoot .theme-section-card .collapse').forEach(collapseEl => {
                collapseEl.addEventListener('show.bs.collapse', function () {
                    const card = this.closest('.theme-section-card');
                    const btn = card.querySelector('.collapse-toggle-btn');
                    if (btn) btn.setAttribute('aria-expanded', 'true');
                });
                collapseEl.addEventListener('hide.bs.collapse', function () {
                    const card = this.closest('.theme-section-card');
                    const btn = card.querySelector('.collapse-toggle-btn');
                    if (btn) btn.setAttribute('aria-expanded', 'false');
                });
            });
        });

        // All sections expand/collapse toggle
        let allSectionsExpanded = false;
        function toggleAllColorSections() {
            const collapses = document.querySelectorAll('#themeColorsRoot .theme-section-card .collapse');
            const toggleBtns = document.querySelectorAll('#themeColorsRoot .theme-section-card .collapse-toggle-btn');
            const btnText = document.getElementById('expandAllBtnText');

            allSectionsExpanded = !allSectionsExpanded;

            collapses.forEach(col => {
                if (allSectionsExpanded) {
                    bootstrap.Collapse.getOrCreateInstance(col, { toggle: false }).show();
                } else {
                    bootstrap.Collapse.getOrCreateInstance(col, { toggle: false }).hide();
                }
            });

            toggleBtns.forEach(btn => {
                btn.setAttribute('aria-expanded', allSectionsExpanded ? 'true' : 'false');
            });

            if (btnText) {
                btnText.innerText = allSectionsExpanded ? 'Collapse All' : 'Expand All';
            }
        }

        // Modal Triggers
        function triggerResetHeaderModal() { bootstrap.Modal.getOrCreateInstance(document.getElementById('headerResetConfirmModal')).show(); }
        function triggerResetFooterModal() { bootstrap.Modal.getOrCreateInstance(document.getElementById('footerResetConfirmModal')).show(); }
        function triggerResetProductModal() { bootstrap.Modal.getOrCreateInstance(document.getElementById('productResetConfirmModal')).show(); }
        function triggerResetMasterModal() { bootstrap.Modal.getOrCreateInstance(document.getElementById('masterResetConfirmModal')).show(); }
        function triggerResetHomeModal() { bootstrap.Modal.getOrCreateInstance(document.getElementById('homeResetConfirmModal')).show(); }
        function triggerResetShopModal() { bootstrap.Modal.getOrCreateInstance(document.getElementById('shopResetConfirmModal')).show(); }
        function triggerResetPagesModal() { bootstrap.Modal.getOrCreateInstance(document.getElementById('pagesResetConfirmModal')).show(); }
        function triggerResetBlogsModal() { bootstrap.Modal.getOrCreateInstance(document.getElementById('blogsResetConfirmModal')).show(); }
        function triggerResetColorsModal() { bootstrap.Modal.getOrCreateInstance(document.getElementById('colorsResetConfirmModal')).show(); }

        // Submit Action Helper
        function submitScopedReset(name) {
            const form = document.getElementById('themeColorsForm');
            const resetInput = document.createElement('input');
            resetInput.type = 'hidden';
            resetInput.name = name;
            resetInput.value = '1';
            form.appendChild(resetInput);
            form.submit();
        }

        document.getElementById('btnExecuteResetHeader').addEventListener('click', () => submitScopedReset('reset_header'));
        document.getElementById('btnExecuteResetFooter').addEventListener('click', () => submitScopedReset('reset_footer'));
        document.getElementById('btnExecuteResetProduct').addEventListener('click', () => submitScopedReset('reset_product'));
        document.getElementById('btnExecuteResetMaster').addEventListener('click', () => submitScopedReset('reset_master'));
        document.getElementById('btnExecuteResetHome').addEventListener('click', () => submitScopedReset('reset_home'));
        document.getElementById('btnExecuteResetShop').addEventListener('click', () => submitScopedReset('reset_shop'));
        document.getElementById('btnExecuteResetPages').addEventListener('click', () => submitScopedReset('reset_pages'));
        document.getElementById('btnExecuteResetBlogs').addEventListener('click', () => submitScopedReset('reset_blogs'));
        document.getElementById('btnExecuteResetColors').addEventListener('click', () => submitScopedReset('reset_defaults'));
    </script>
@endsection