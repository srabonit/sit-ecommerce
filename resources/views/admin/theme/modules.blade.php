
@extends('layouts.admin')

@section('title', 'Theme Modules & Features - ' . config('app.name'))

@section('admin_content')
<style>
    /* =========================================================
       THEME MODULES — MODERN ADMIN DESIGN SYSTEM
       Scoped styles | Bootstrap compatible | 5px radius
    ========================================================= */
    #themeModulesRoot,
    .modules-modal-scope {
        --tm-primary: var(--bs-primary, #2563eb);
        --tm-primary-rgb: var(--bs-primary-rgb, 37, 99, 235);
        --tm-success: var(--bs-success, #16a34a);
        --tm-border: var(--bs-border-color, #e2e8f0);
        --tm-muted: var(--bs-secondary-color, #64748b);
        --tm-surface: var(--bs-body-bg, #ffffff);
        --tm-soft: var(--bs-tertiary-bg, #f8fafc);
        --tm-radius: 5px;
        --tm-shadow: 0 1px 3px rgba(15, 23, 42, .045);
        color: var(--bs-body-color, #334155);
    }

    #themeModulesRoot *,
    .modules-modal-scope * {
        box-sizing: border-box;
    }

    #themeModulesRoot .btn,
    #themeModulesRoot button,
    #themeModulesRoot .badge,
    #themeModulesRoot .alert,
    #themeModulesRoot .card,
    #themeModulesRoot .modules-header-card,
    #themeModulesRoot .module-section-card,
    #themeModulesRoot .module-item-box,
    #themeModulesRoot .status-kpi-pill,
    #themeModulesRoot .collapse-toggle-btn,
    .modules-modal-scope .modal-content,
    .modules-modal-scope .btn,
    .modules-modal-scope .badge {
        border-radius: var(--tm-radius) !important;
    }

    #themeModulesRoot .bn-digit,
    #themeModulesRoot .font-monospace {
        font-weight: 700 !important;
    }

    #themeModulesRoot .btn-compact {
        min-height: 32px;
        padding: 6px 13px !important;
        font-size: 11.5px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        white-space: nowrap;
        transition: background-color .15s ease, border-color .15s ease,
                    color .15s ease, box-shadow .15s ease;
    }

    #themeModulesRoot .btn:focus-visible,
    #themeModulesRoot button:focus-visible,
    #themeModulesRoot input:focus-visible,
    .modules-modal-scope .btn:focus-visible {
        outline: 2px solid rgba(var(--tm-primary-rgb), .4);
        outline-offset: 2px;
        box-shadow: none;
    }

    /* Header */
    #themeModulesRoot .modules-header-card {
        position: relative;
        overflow: hidden;
        background: #111c32;
        color: #fff;
        padding: 16px 18px;
        border: 1px solid #26354e;
        box-shadow: 0 2px 5px rgba(15, 23, 42, .08);
        margin-bottom: 14px;
    }

    #themeModulesRoot .modules-header-card::after {
        content: "";
        position: absolute;
        width: 190px;
        height: 190px;
        right: 9%;
        top: -125px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .035);
        pointer-events: none;
    }

    #themeModulesRoot .header-content,
    #themeModulesRoot .header-actions {
        position: relative;
        z-index: 1;
    }

    #themeModulesRoot .header-eyebrow {
        font-size: 9px;
        letter-spacing: .55px;
        font-weight: 700;
    }

    #themeModulesRoot .header-title {
        font-size: 17px;
        line-height: 1.4;
        font-weight: 750;
        letter-spacing: -.25px;
    }

    #themeModulesRoot .header-description {
        max-width: 590px;
        font-size: 11.5px;
        line-height: 1.55;
        color: rgba(255, 255, 255, .65);
    }

    #themeModulesRoot .header-actions .btn-outline-light {
        border-color: rgba(255, 255, 255, .3);
        color: #fff;
    }

    #themeModulesRoot .header-actions .btn-outline-light:hover {
        background: rgba(255, 255, 255, .1);
        border-color: rgba(255, 255, 255, .65);
    }

    /* KPI Cards */
    #themeModulesRoot .status-kpi-pill {
        min-height: 68px;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 11px 14px;
        background: var(--tm-surface);
        border: 1px solid var(--tm-border);
        box-shadow: var(--tm-shadow);
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #themeModulesRoot .status-kpi-pill:hover {
        border-color: rgba(var(--tm-primary-rgb), .35);
        box-shadow: 0 3px 10px rgba(15, 23, 42, .045);
    }

    #themeModulesRoot .kpi-label {
        font-size: 10.5px;
        font-weight: 600;
        color: var(--tm-muted);
        margin-bottom: 3px;
    }

    #themeModulesRoot .kpi-value {
        font-size: 13px;
        font-weight: 750;
        line-height: 1.4;
    }

    #themeModulesRoot .kpi-icon {
        width: 35px;
        height: 35px;
        border-radius: 5px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 15px;
    }

    /* Section Cards */
    #themeModulesRoot .module-section-card {
        background: var(--tm-surface);
        border: 1px solid var(--tm-border);
        box-shadow: var(--tm-shadow);
        margin-bottom: 13px;
        overflow: hidden;
    }

    #themeModulesRoot .module-section-header {
        min-height: 47px;
        padding: 9px 13px;
        background: var(--tm-soft);
        border-bottom: 1px solid var(--tm-border);
        gap: 10px;
    }

    #themeModulesRoot .section-title-text {
        min-width: 0;
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--bs-emphasis-color, #1e293b);
        font-size: 12.5px;
        font-weight: 750;
        line-height: 1.4;
    }

    #themeModulesRoot .section-title-icon {
        width: 27px;
        height: 27px;
        border-radius: 5px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(var(--tm-primary-rgb), .09);
        flex-shrink: 0;
        font-size: 12px;
    }

    #themeModulesRoot .section-category-badge {
        font-size: 8.5px;
        font-weight: 750;
        letter-spacing: .3px;
        padding: 5px 7px;
        white-space: nowrap;
    }

    /* Collapse Button */
    #themeModulesRoot .collapse-toggle-btn {
        width: 26px;
        height: 26px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: var(--tm-surface);
        border: 1px solid var(--tm-border);
        color: var(--tm-muted);
        transition: background .15s ease, color .15s ease, border-color .15s ease;
    }

    #themeModulesRoot .collapse-toggle-btn:hover {
        background: var(--tm-primary);
        border-color: var(--tm-primary);
        color: #fff;
    }

    #themeModulesRoot .collapse-icon {
        font-size: 10px;
        transition: transform .2s ease;
    }

    #themeModulesRoot .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    /* Module Grid Area */
    #themeModulesRoot .module-section-body {
        padding: 14px;
        background: var(--bs-tertiary-bg, #f8fafc);
        background: color-mix(in srgb, var(--tm-soft) 65%, transparent);
    }

    #themeModulesRoot .module-item-box {
        min-height: 132px;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 13px 14px;
        background: var(--tm-surface);
        border: 1px solid var(--tm-border);
        box-shadow: 0 1px 2px rgba(15, 23, 42, .025);
        transition: border-color .16s ease, box-shadow .16s ease, transform .16s ease;
    }

    #themeModulesRoot .module-item-box:hover {
        border-color: rgba(var(--tm-primary-rgb), .4);
        box-shadow: 0 4px 13px rgba(15, 23, 42, .055);
        transform: translateY(-1px);
    }

    #themeModulesRoot .module-item-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 13px;
    }

    #themeModulesRoot .module-icon-badge {
        width: 35px;
        height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 5px !important;
        font-size: 14px;
    }

    #themeModulesRoot .module-name {
        display: block;
        margin-bottom: 5px;
        color: var(--bs-emphasis-color, #1e293b);
        font-size: 12.5px;
        font-weight: 750;
        line-height: 1.4;
    }

    #themeModulesRoot .module-description {
        display: block;
        color: var(--tm-muted);
        font-size: 10.5px;
        font-weight: 400;
        line-height: 1.55;
    }

    /* Toggle Switch */
    #themeModulesRoot .module-switch {
        margin: 0;
        padding: 0;
        min-height: auto;
    }

    #themeModulesRoot .module-switch .form-check-input {
        float: none;
        width: 36px;
        height: 19px;
        margin: 0;
        cursor: pointer;
        border: 1px solid #cbd5e1;
        background-color: #e2e8f0;
        box-shadow: none;
        transition: background-color .15s ease, border-color .15s ease;
    }

    #themeModulesRoot .module-switch .form-check-input:checked {
        background-color: var(--tm-primary);
        border-color: var(--tm-primary);
    }

    #themeModulesRoot .module-switch .form-check-input:focus {
        border-color: var(--tm-primary);
        box-shadow: 0 0 0 3px rgba(var(--tm-primary-rgb), .13);
    }

    /* Bottom Action */
    #themeModulesRoot .modules-bottom-actions {
        padding: 2px 0 16px;
    }

    #themeModulesRoot .modules-bottom-actions .btn {
        min-height: 36px;
        font-size: 12px !important;
        padding: 7px 20px !important;
    }

    /* Success Alert */
    #themeModulesRoot .alert {
        border: 1px solid rgba(25, 135, 84, .15) !important;
        background: rgba(25, 135, 84, .075);
    }

    /* Notice Modal */
    .modules-modal-scope .modal-content {
        border: 1px solid var(--tm-border);
        box-shadow: 0 15px 45px rgba(15, 23, 42, .17);
    }

    .modules-modal-scope .modal-body {
        padding: 10px 8px !important;
    }

    /* Tablet */
    @media (max-width: 991.98px) {
        #themeModulesRoot .module-item-box {
            min-height: 138px;
        }
    }

    /* Mobile */
    @media (max-width: 767.98px) {
        #themeModulesRoot {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        #themeModulesRoot .modules-header-card {
            padding: 13px 14px !important;
        }

        #themeModulesRoot .header-top-wrap {
            align-items: stretch !important;
            gap: 12px !important;
        }

        #themeModulesRoot .header-title {
            font-size: 15px;
        }

        #themeModulesRoot .header-description {
            font-size: 11px;
        }

        #themeModulesRoot .header-actions {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            gap: 7px !important;
            width: 100%;
        }

        #themeModulesRoot .header-actions .btn {
            width: 100%;
            min-width: 0;
            padding-left: 7px !important;
            padding-right: 7px !important;
            font-size: 10.5px !important;
        }

        #themeModulesRoot .status-kpi-pill {
            min-height: 62px;
            padding: 9px 11px;
        }

        #themeModulesRoot .kpi-value {
            font-size: 12px;
        }

        #themeModulesRoot .module-section-header {
            padding: 9px 10px;
        }

        #themeModulesRoot .section-title-text {
            font-size: 11.5px;
            gap: 6px;
        }

        #themeModulesRoot .section-category-badge {
            display: none;
        }

        #themeModulesRoot .module-section-body {
            padding: 10px;
        }

        #themeModulesRoot .module-item-box {
            min-height: 122px;
            padding: 12px;
        }

        #themeModulesRoot .module-item-top {
            margin-bottom: 10px;
        }

        #themeModulesRoot .modules-bottom-actions .btn {
            width: 100%;
        }
    }

    @media (max-width: 359.98px) {
        #themeModulesRoot .header-actions {
            grid-template-columns: 1fr;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        #themeModulesRoot *,
        .modules-modal-scope * {
            transition: none !important;
            animation: none !important;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-2" id="themeModulesRoot">

    {{-- Header & Quick Actions --}}
    <div class="modules-header-card d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 header-top-wrap">

        <div class="header-content">
            <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle header-eyebrow">
                    <i class="fas fa-cubes me-1"></i> STOREFRONT FEATURES
                </span>
                <span class="badge bg-light bg-opacity-10 text-white-50 border border-secondary header-eyebrow">
                    6 CORE MODULES
                </span>
            </div>

            <h5 class="header-title mb-0 d-flex align-items-center gap-2">
                <i class="fas fa-toggle-on text-primary"></i>
                <span>Theme Modules &amp; Feature Toggles</span>
            </h5>

            <p class="header-description mb-0 mt-1">
                Enable or disable essential e-commerce modules, checkout options, live search and purchase policies.
            </p>
        </div>

        <div class="header-actions d-flex align-items-center gap-2">
            <button type="button"
                    class="btn btn-outline-light btn-compact"
                    onclick="toggleAllModuleSections()">
                <i class="fas fa-arrows-up-down"></i>
                <span id="expandAllModulesBtnText">Collapse All</span>
            </button>

            <button type="submit"
                    form="themeModulesForm"
                    class="btn btn-primary btn-compact fw-bold px-3">
                <i class="fas fa-check-circle"></i>
                <span>Save Settings</span>
            </button>
        </div>
    </div>

    {{-- Calculate Module Status --}}
    @php
        $activeCount = 0;
        $modulesKeys = [
            'module_ajax_cart_drawer',
            'module_buy_now_btn',
            'module_quick_view',
            'module_ajax_search',
            'module_instant_checkout',
            'module_allow_stockout_orders'
        ];

        foreach ($modulesKeys as $k) {
            if (($settings[$k] ?? '1') == '1') {
                $activeCount++;
            }
        }

        $disabledCount = count($modulesKeys) - $activeCount;
    @endphp

    {{-- Status Summary --}}
    <div class="row g-2 mb-3">

        <div class="col-12 col-md-4">
            <div class="status-kpi-pill">
                <div class="min-w-0">
                    <div class="kpi-label">Active Features</div>
                    <strong class="kpi-value text-success d-block font-monospace bn-digit">
                        {{ $activeCount }} Modules Active
                    </strong>
                </div>
                <div class="kpi-icon bg-success-subtle text-success">
                    <i class="fas fa-circle-check"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="status-kpi-pill">
                <div class="min-w-0">
                    <div class="kpi-label">Disabled Features</div>
                    <strong class="kpi-value text-secondary d-block font-monospace bn-digit">
                        {{ $disabledCount }} Modules Off
                    </strong>
                </div>
                <div class="kpi-icon bg-secondary-subtle text-secondary">
                    <i class="fas fa-circle-pause"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="status-kpi-pill">
                <div class="min-w-0">
                    <div class="kpi-label">System Sync</div>
                    <strong class="kpi-value text-primary d-block">
                        Real-Time Storefront
                    </strong>
                </div>
                <div class="kpi-icon bg-primary-subtle text-primary">
                    <i class="fas fa-arrows-rotate"></i>
                </div>
            </div>
        </div>

    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success shadow-none py-2 px-3 mb-3 d-flex align-items-center justify-content-between gap-2"
             role="alert">
            <div class="d-flex align-items-center gap-2 min-w-0">
                <i class="fas fa-circle-check text-success"></i>
                <span class="fw-semibold text-dark small">
                    {{ session('success') }}
                </span>
            </div>
            <button type="button"
                    class="btn-close shadow-none flex-shrink-0"
                    data-bs-dismiss="alert"
                    aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('admin.theme.setting.modules.update') }}"
          method="POST"
          id="themeModulesForm">
        @csrf

        {{-- Section 1: Cart & Checkout --}}
        <div class="module-section-card">

            <div class="module-section-header d-flex justify-content-between align-items-center">

                <div class="section-title-text">
                    <span class="section-title-icon text-primary">
                        <i class="fas fa-shopping-bag"></i>
                    </span>
                    <span>Cart, Checkout &amp; Conversion Modules</span>
                </div>

                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle section-category-badge">
                        PURCHASE FLOW
                    </span>

                    <button type="button"
                            class="collapse-toggle-btn"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapseCartModules"
                            aria-expanded="true"
                            aria-controls="collapseCartModules"
                            title="Toggle Section">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>
                </div>

            </div>

            <div class="collapse show" id="collapseCartModules">
                <div class="module-section-body">
                    <div class="row g-2 g-md-3">

                        {{-- Ajax Floating Cart Drawer --}}
                        <div class="col-12 col-md-4">
                            <div class="module-item-box">
                                <div class="module-item-top">
                                    <div class="module-icon-badge bg-primary-subtle text-primary">
                                        <i class="fas fa-shopping-bag"></i>
                                    </div>

                                    <div class="form-check form-switch module-switch">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               name="module_ajax_cart_drawer"
                                               value="1"
                                               aria-label="Enable Ajax Floating Cart Drawer"
                                               {{ ($settings['module_ajax_cart_drawer'] ?? '1') == '1' ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div>
                                    <strong class="module-name">Ajax Floating Cart Drawer</strong>
                                    <small class="module-description">
                                        Opens slide-out cart drawer smoothly upon adding products without reloading.
                                    </small>
                                </div>
                            </div>
                        </div>

                        {{-- Buy Now Button --}}
                        <div class="col-12 col-md-4">
                            <div class="module-item-box">
                                <div class="module-item-top">
                                    <div class="module-icon-badge bg-success-subtle text-success">
                                        <i class="fas fa-bolt"></i>
                                    </div>

                                    <div class="form-check form-switch module-switch">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               name="module_buy_now_btn"
                                               value="1"
                                               aria-label="Enable Buy Now Button"
                                               {{ ($settings['module_buy_now_btn'] ?? '1') == '1' ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div>
                                    <strong class="module-name">Buy Now / Instant Order</strong>
                                    <small class="module-description">
                                        Enables direct 1-click purchase button on product cards bypassing cart page.
                                    </small>
                                </div>
                            </div>
                        </div>

                        {{-- Instant Checkout --}}
                        <div class="col-12 col-md-4">
                            <div class="module-item-box">
                                <div class="module-item-top">
                                    <div class="module-icon-badge bg-danger-subtle text-danger">
                                        <i class="fas fa-cash-register"></i>
                                    </div>

                                    <div class="form-check form-switch module-switch">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               name="module_instant_checkout"
                                               value="1"
                                               aria-label="Enable Instant Checkout"
                                               {{ ($settings['module_instant_checkout'] ?? '1') == '1' ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div>
                                    <strong class="module-name">Instant Checkout Option</strong>
                                    <small class="module-description">
                                        Renders express checkout modal window directly on storefront.
                                    </small>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- Section 2: Discovery & Inventory --}}
        <div class="module-section-card">

            <div class="module-section-header d-flex justify-content-between align-items-center">

                <div class="section-title-text">
                    <span class="section-title-icon text-warning">
                        <i class="fas fa-search"></i>
                    </span>
                    <span>Discovery, Preview &amp; Inventory Policy</span>
                </div>

                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle section-category-badge">
                        CATALOG RULES
                    </span>

                    <button type="button"
                            class="collapse-toggle-btn"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapseDiscoveryModules"
                            aria-expanded="true"
                            aria-controls="collapseDiscoveryModules"
                            title="Toggle Section">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>
                </div>

            </div>

            <div class="collapse show" id="collapseDiscoveryModules">
                <div class="module-section-body">
                    <div class="row g-2 g-md-3">

                        {{-- Ajax Product Search --}}
                        <div class="col-12 col-md-4">
                            <div class="module-item-box">
                                <div class="module-item-top">
                                    <div class="module-icon-badge bg-warning-subtle text-warning-emphasis">
                                        <i class="fas fa-search"></i>
                                    </div>

                                    <div class="form-check form-switch module-switch">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               name="module_ajax_search"
                                               value="1"
                                               aria-label="Enable Ajax Product Live Search"
                                               {{ ($settings['module_ajax_search'] ?? '1') == '1' ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div>
                                    <strong class="module-name">Ajax Product Live Search</strong>
                                    <small class="module-description">
                                        Renders real-time live product suggestions with prices while typing in header.
                                    </small>
                                </div>
                            </div>
                        </div>

                        {{-- Quick View --}}
                        <div class="col-12 col-md-4">
                            <div class="module-item-box">
                                <div class="module-item-top">
                                    <div class="module-icon-badge bg-info-subtle text-info">
                                        <i class="fas fa-eye"></i>
                                    </div>

                                    <div class="form-check form-switch module-switch">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               name="module_quick_view"
                                               value="1"
                                               aria-label="Enable Quick View Modal"
                                               {{ ($settings['module_quick_view'] ?? '1') == '1' ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div>
                                    <strong class="module-name">Quick View Modal Preview</strong>
                                    <small class="module-description">
                                        Enables interactive product details popup modal right from catalog cards.
                                    </small>
                                </div>
                            </div>
                        </div>

                        {{-- Stockout Orders --}}
                        <div class="col-12 col-md-4">
                            <div class="module-item-box">
                                <div class="module-item-top">
                                    <div class="module-icon-badge bg-secondary-subtle text-secondary">
                                        <i class="fas fa-box-open"></i>
                                    </div>

                                    <div class="form-check form-switch module-switch">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               name="module_allow_stockout_orders"
                                               value="1"
                                               aria-label="Enable Stockout Orders"
                                               {{ ($settings['module_allow_stockout_orders'] ?? '1') == '1' ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div>
                                    <strong class="module-name">Allow Stockout / Pre-Orders</strong>
                                    <small class="module-description">
                                        Permits customers to place orders even when inventory balance reaches zero.
                                    </small>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom Save Action --}}
        <div class="modules-bottom-actions d-flex justify-content-end">
            <button type="submit" class="btn btn-primary btn-compact fw-bold">
                <i class="fas fa-check-circle"></i>
                Save Module Settings
            </button>
        </div>

    </form>
</div>

{{-- Custom Enterprise Notice Modal --}}
<div class="modal fade modules-modal-scope"
     id="modulesNoticeModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index: 108500;">

    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">

            <div class="modal-body">
                <div class="mb-2" id="modulesNoticeIconWrap">
                    <i class="fas fa-circle-check text-success fa-2x"></i>
                </div>

                <h6 class="fw-bold text-dark mb-1"
                    id="modulesNoticeTitle"
                    style="font-size: 14px;">
                    Notification
                </h6>

                <p class="text-muted small mb-3"
                   id="modulesNoticeMessage"
                   style="font-size: 12px; line-height: 1.5;"></p>

                <button type="button"
                        class="btn btn-primary btn-compact px-4"
                        data-bs-dismiss="modal">
                    Acknowledge
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    // =========================================================
    // MODULE SECTION CONTROLS
    // Expand All / Collapse All
    // =========================================================

    let allModuleSectionsExpanded = true;

    function toggleAllModuleSections() {
        const root = document.getElementById('themeModulesRoot');

        if (!root || typeof bootstrap === 'undefined') {
            return;
        }

        const collapses = root.querySelectorAll(
            '.module-section-card .collapse'
        );

        const toggleBtns = root.querySelectorAll(
            '.module-section-card .collapse-toggle-btn'
        );

        const btnText = document.getElementById(
            'expandAllModulesBtnText'
        );

        allModuleSectionsExpanded = !allModuleSectionsExpanded;

        collapses.forEach(function (col) {
            const instance = bootstrap.Collapse.getOrCreateInstance(
                col,
                { toggle: false }
            );

            if (allModuleSectionsExpanded) {
                instance.show();
            } else {
                instance.hide();
            }
        });

        toggleBtns.forEach(function (btn) {
            btn.setAttribute(
                'aria-expanded',
                allModuleSectionsExpanded ? 'true' : 'false'
            );
        });

        if (btnText) {
            btnText.innerText = allModuleSectionsExpanded
                ? 'Collapse All'
                : 'Expand All';
        }
    }

    // Keep the global button label synchronized when users
    // individually expand or collapse a section.
    document.addEventListener('DOMContentLoaded', function () {
        const root = document.getElementById('themeModulesRoot');

        if (!root) {
            return;
        }

        root.querySelectorAll('.module-section-card .collapse').forEach(function (collapseEl) {

            collapseEl.addEventListener('shown.bs.collapse', function () {
                const section = collapseEl.closest('.module-section-card');
                const toggleBtn = section?.querySelector('.collapse-toggle-btn');

                if (toggleBtn) {
                    toggleBtn.setAttribute('aria-expanded', 'true');
                }

                syncModuleExpandButton();
            });

            collapseEl.addEventListener('hidden.bs.collapse', function () {
                const section = collapseEl.closest('.module-section-card');
                const toggleBtn = section?.querySelector('.collapse-toggle-btn');

                if (toggleBtn) {
                    toggleBtn.setAttribute('aria-expanded', 'false');
                }

                syncModuleExpandButton();
            });
        });
    });

    function syncModuleExpandButton() {
        const root = document.getElementById('themeModulesRoot');

        if (!root) {
            return;
        }

        const collapses = Array.from(
            root.querySelectorAll('.module-section-card .collapse')
        );

        const allExpanded = collapses.every(function (el) {
            return el.classList.contains('show');
        });

        const btnText = document.getElementById('expandAllModulesBtnText');

        allModuleSectionsExpanded = allExpanded;

        if (btnText) {
            btnText.innerText = allExpanded ? 'Collapse All' : 'Expand All';
        }
    }
</script>
@endsection
