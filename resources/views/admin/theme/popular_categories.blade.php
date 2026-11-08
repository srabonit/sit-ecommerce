
@extends('layouts.admin')

@section('title', 'Popular Categories Slider - ' . config('app.name'))

@php
    // Safe dynamic route resolver
    $categoryIndexRoute = '#';

    if (\Illuminate\Support\Facades\Route::has('admin.categories.index')) {
        $categoryIndexRoute = route('admin.categories.index');
    } elseif (\Illuminate\Support\Facades\Route::has('admin.category.index')) {
        $categoryIndexRoute = route('admin.category.index');
    } elseif (\Illuminate\Support\Facades\Route::has('admin.category')) {
        $categoryIndexRoute = route('admin.category');
    } elseif (\Illuminate\Support\Facades\Route::has('categories.index')) {
        $categoryIndexRoute = route('categories.index');
    } else {
        $categoryIndexRoute = url('admin/category');
    }

    $selectedCount = count($selectedCategoryIds ?? []);
    $totalCats = count($allCategories ?? []);
@endphp

@section('admin_content')

<style>
    /* =========================================================
       POPULAR CATEGORIES — ADMIN DESIGN SYSTEM
       Bootstrap compatible | Scoped | Responsive | 5px radius
    ========================================================= */

    #popCategoriesRoot,
    .popcat-modal-scope {
        --pc-primary: var(--bs-primary, #2563eb);
        --pc-primary-rgb: var(--bs-primary-rgb, 37, 99, 235);
        --pc-border: var(--bs-border-color, #e2e8f0);
        --pc-muted: var(--bs-secondary-color, #64748b);
        --pc-surface: var(--bs-body-bg, #ffffff);
        --pc-soft: var(--bs-tertiary-bg, #f8fafc);
        --pc-radius: 5px;
        --pc-shadow: 0 1px 3px rgba(15, 23, 42, .045);

        color: var(--bs-body-color, #334155);
        font-family: 'Plus Jakarta Sans', 'Inter', -apple-system,
                     BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    #popCategoriesRoot *,
    .popcat-modal-scope * {
        box-sizing: border-box;
    }

    /* Typography */
    #popCategoriesRoot .bn-digit,
    #popCategoriesRoot .font-tabular {
        font-variant-numeric: tabular-nums;
        font-weight: 700;
    }

    /* Consistent Radius */
    #popCategoriesRoot .btn,
    #popCategoriesRoot button,
    #popCategoriesRoot .badge,
    #popCategoriesRoot .alert,
    #popCategoriesRoot .card,
    #popCategoriesRoot .header-banner-card,
    #popCategoriesRoot .panel-section-card,
    #popCategoriesRoot .status-kpi-pill,
    #popCategoriesRoot .cat-multi-select-box,
    #popCategoriesRoot .cat-chip,
    #popCategoriesRoot .cat-check-item,
    #popCategoriesRoot .collapse-toggle-btn,
    .popcat-modal-scope .modal-content,
    .popcat-modal-scope .btn {
        border-radius: var(--pc-radius) !important;
    }

    /* Compact Buttons */
    #popCategoriesRoot .btn-compact {
        min-height: 33px;
        padding: 6px 13px !important;
        font-size: 11.5px !important;
        line-height: 1.35 !important;
        font-weight: 650 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        white-space: nowrap;
        transition: background-color .15s ease,
                    border-color .15s ease,
                    color .15s ease,
                    box-shadow .15s ease;
    }

    #popCategoriesRoot .btn-primary {
        background: var(--pc-primary);
        border-color: var(--pc-primary);
        color: #fff;
    }

    #popCategoriesRoot .btn-primary:hover {
        filter: brightness(.93);
        color: #fff;
    }

    #popCategoriesRoot .btn-outline-secondary {
        background: var(--pc-surface);
        border-color: var(--pc-border);
        color: var(--bs-body-color, #334155);
    }

    #popCategoriesRoot .btn-outline-secondary:hover {
        background: var(--pc-soft);
        border-color: #94a3b8;
    }

    #popCategoriesRoot .btn:focus-visible,
    #popCategoriesRoot button:focus-visible,
    #popCategoriesRoot input:focus-visible,
    .popcat-modal-scope button:focus-visible {
        outline: 2px solid rgba(var(--pc-primary-rgb), .4);
        outline-offset: 2px;
        box-shadow: none;
    }

    /* Header */
    #popCategoriesRoot .header-banner-card {
        background: var(--pc-surface);
        color: var(--bs-body-color, #0f172a);
        padding: 15px 18px;
        border: 1px solid var(--pc-border);
        box-shadow: var(--pc-shadow);
        margin-bottom: 14px;
    }

    #popCategoriesRoot .header-eyebrow {
        font-size: 9px;
        font-weight: 750;
        letter-spacing: .4px;
        padding: 5px 8px;
    }

    #popCategoriesRoot .header-title {
        font-size: 17px;
        font-weight: 750;
        line-height: 1.4;
        letter-spacing: -.3px;
        color: var(--bs-emphasis-color, #0f172a);
    }

    #popCategoriesRoot .header-description {
        max-width: 620px;
        font-size: 11.5px;
        line-height: 1.55;
        color: var(--pc-muted);
    }

    /* KPI Cards */
    #popCategoriesRoot .status-kpi-pill {
        min-height: 69px;
        height: 100%;
        padding: 11px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        background: var(--pc-surface);
        border: 1px solid var(--pc-border);
        box-shadow: var(--pc-shadow);
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #popCategoriesRoot .status-kpi-pill:hover {
        border-color: rgba(var(--pc-primary-rgb), .35);
        box-shadow: 0 3px 10px rgba(15, 23, 42, .045);
    }

    #popCategoriesRoot .kpi-label {
        display: block;
        margin-bottom: 3px;
        font-size: 10.5px;
        font-weight: 600;
        color: var(--pc-muted);
    }

    #popCategoriesRoot .kpi-value {
        display: block;
        font-size: 13px;
        font-weight: 750;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    #popCategoriesRoot .kpi-icon {
        width: 35px;
        height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 5px;
        font-size: 15px;
    }

    /* Section Cards */
    #popCategoriesRoot .panel-section-card {
        background: var(--pc-surface);
        border: 1px solid var(--pc-border);
        box-shadow: var(--pc-shadow);
        margin-bottom: 13px;
        overflow: hidden;
    }

    #popCategoriesRoot .panel-section-header {
        min-height: 48px;
        padding: 9px 14px;
        background: var(--pc-surface);
        border-bottom: 1px solid var(--pc-border);
        gap: 10px;
    }

    #popCategoriesRoot .section-title-text {
        min-width: 0;
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--bs-emphasis-color, #0f172a);
        font-size: 12.5px;
        font-weight: 750;
        line-height: 1.4;
    }

    #popCategoriesRoot .section-title-icon {
        width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 5px;
        background: rgba(var(--pc-primary-rgb), .09);
        font-size: 12px;
    }

    #popCategoriesRoot .section-category-badge {
        font-size: 8.5px;
        font-weight: 750;
        letter-spacing: .3px;
        padding: 5px 7px;
        white-space: nowrap;
    }

    /* Collapse Toggle */
    #popCategoriesRoot .collapse-toggle-btn {
        width: 27px;
        height: 27px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: var(--pc-surface);
        border: 1px solid var(--pc-border);
        color: var(--pc-muted);
        cursor: pointer;
        transition: background .15s ease, color .15s ease, border-color .15s ease;
    }

    #popCategoriesRoot .collapse-toggle-btn:hover {
        background: var(--pc-primary);
        border-color: var(--pc-primary);
        color: #fff;
    }

    #popCategoriesRoot .collapse-icon {
        font-size: 10px;
        transition: transform .2s ease;
    }

    #popCategoriesRoot .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    /* Form Controls */
    #popCategoriesRoot .form-label {
        display: block;
        margin-bottom: 5px;
        color: var(--bs-emphasis-color, #334155);
        font-size: 11.5px;
        font-weight: 700;
    }

    #popCategoriesRoot .form-check-input {
        cursor: pointer;
    }

    /* Visibility Setting */
    #popCategoriesRoot .visibility-setting-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 14px 15px;
        background: var(--pc-surface);
        border: 1px solid var(--pc-border);
        border-radius: 5px;
    }

    #popCategoriesRoot .visibility-setting-title {
        display: block;
        margin-bottom: 4px;
        font-size: 12.5px;
        font-weight: 750;
        color: var(--bs-emphasis-color, #1e293b);
        cursor: pointer;
    }

    #popCategoriesRoot .visibility-setting-description {
        display: block;
        font-size: 11px;
        line-height: 1.5;
        color: var(--pc-muted);
    }

    /* Category Selection */
    #popCategoriesRoot .cat-multi-select-box {
        background: var(--pc-surface);
        border: 1px solid var(--pc-border);
        overflow: hidden;
    }

    #popCategoriesRoot .selected-chips-box {
        min-height: 43px;
        max-height: 100px;
        overflow-y: auto;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 5px;
        padding: 8px 10px;
        background: var(--pc-soft);
        border-bottom: 1px solid var(--pc-border);
    }

    #popCategoriesRoot .cat-chip {
        max-width: 100%;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 4px 8px;
        background: var(--pc-primary);
        color: #fff;
        font-size: 10.5px;
        font-weight: 650;
        line-height: 1.4;
    }

    #popCategoriesRoot .cat-chip-name {
        max-width: 160px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    #popCategoriesRoot .chip-remove {
        width: 16px;
        height: 16px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 0;
        background: rgba(255, 255, 255, .15);
        color: #fff;
        border-radius: 3px !important;
        font-size: 13px;
        line-height: 1;
        cursor: pointer;
        transition: background .15s ease;
    }

    #popCategoriesRoot .chip-remove:hover {
        background: rgba(255, 255, 255, .3);
    }

    #popCategoriesRoot .cat-select-search {
        width: 100%;
        min-height: 39px;
        padding: 9px 12px;
        border: 0;
        border-bottom: 1px solid var(--pc-border);
        border-radius: 0 !important;
        background: var(--pc-surface);
        color: var(--bs-body-color, #334155);
        font-size: 11.5px;
        outline: none;
        box-shadow: none !important;
    }

    #popCategoriesRoot .cat-select-search:focus {
        background: var(--pc-soft);
        border-bottom-color: var(--pc-primary);
    }

    #popCategoriesRoot .cat-checklist-container {
        max-height: 265px;
        overflow-y: auto;
        padding: 7px 8px;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
    }

    #popCategoriesRoot .cat-checklist-container::-webkit-scrollbar {
        width: 5px;
    }

    #popCategoriesRoot .cat-checklist-container::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 5px;
    }

    #popCategoriesRoot .cat-check-item {
        min-height: 39px;
        margin-bottom: 3px;
        padding: 7px 9px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        border: 1px solid transparent;
        background: transparent;
        cursor: pointer;
        transition: background .12s ease, border-color .12s ease;
    }

    #popCategoriesRoot .cat-check-item:hover {
        background: var(--pc-soft);
        border-color: var(--pc-border);
    }

    #popCategoriesRoot .cat-check-item:has(.cat-checkbox:checked) {
        background: rgba(var(--pc-primary-rgb), .055);
        border-color: rgba(var(--pc-primary-rgb), .17);
    }

    #popCategoriesRoot .cat-checkbox {
        width: 15px;
        height: 15px;
        margin: 0;
        flex-shrink: 0;
        accent-color: var(--pc-primary);
    }

    #popCategoriesRoot .cat-item-name {
        min-width: 0;
        color: var(--bs-body-color, #334155);
        font-size: 11.5px;
        font-weight: 600;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    #popCategoriesRoot .cat-product-count {
        flex-shrink: 0;
        font-size: 9px;
        font-weight: 700;
    }

    #popCategoriesRoot .cat-empty-state {
        padding: 18px 10px;
        text-align: center;
        color: var(--pc-muted);
        font-size: 11px;
    }

    /* Enhanced Switch */
    #popCategoriesRoot .form-switch .form-check-input {
        width: 37px;
        height: 19px;
        margin: 0;
        cursor: pointer;
        border: 1px solid #cbd5e1;
        background-color: #e2e8f0;
        box-shadow: none;
        transition: background-color .15s ease, border-color .15s ease;
    }

    #popCategoriesRoot .form-switch .form-check-input:checked {
        background-color: var(--pc-primary);
        border-color: var(--pc-primary);
    }

    #popCategoriesRoot .form-switch .form-check-input:focus {
        border-color: var(--pc-primary);
        box-shadow: 0 0 0 3px rgba(var(--pc-primary-rgb), .13);
    }

    /* Bottom Action */
    #popCategoriesRoot .bottom-action-bar {
        padding: 2px 0 16px;
    }

    #popCategoriesRoot .bottom-action-bar .btn {
        min-height: 36px;
        padding: 7px 20px !important;
        font-size: 12px !important;
    }

    /* Success Alert */
    #popCategoriesRoot .alert {
        border: 1px solid rgba(25, 135, 84, .15) !important;
        background: rgba(25, 135, 84, .07);
    }

    /* Minimal Notice Modal */
    .popcat-modal-scope .modal-content {
        padding: 23px 24px !important;
        background: #fff;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 18px 45px rgba(15, 23, 42, .15);
        text-align: left;
    }

    .popcat-modal-scope .minimal-modal-title {
        margin-bottom: 6px;
        color: #0f172a;
        font-size: 15px;
        font-weight: 750;
    }

    .popcat-modal-scope .minimal-modal-subtext {
        margin-bottom: 17px;
        color: #64748b;
        font-size: 12px;
        line-height: 1.55;
        overflow-wrap: anywhere;
    }

    .popcat-modal-scope .btn-minimal-action {
        min-width: 65px;
        padding: 7px 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 5px;
        background: var(--pc-primary);
        color: #fff;
        font-size: 12px;
        font-weight: 650;
        cursor: pointer;
        transition: filter .15s ease;
    }

    .popcat-modal-scope .btn-minimal-action:hover {
        filter: brightness(.92);
    }

    /* Tablet */
    @media (max-width: 991.98px) {
        #popCategoriesRoot .status-kpi-pill {
            padding: 10px 12px;
        }
    }

    /* Mobile */
    @media (max-width: 767.98px) {
        #popCategoriesRoot {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        #popCategoriesRoot .header-banner-card {
            padding: 13px 14px !important;
        }

        #popCategoriesRoot .header-top-wrap {
            align-items: stretch !important;
            gap: 12px !important;
        }

        #popCategoriesRoot .header-title {
            font-size: 15px;
        }

        #popCategoriesRoot .header-description {
            font-size: 11px;
        }

        #popCategoriesRoot .top-action-btns {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            gap: 7px !important;
            width: 100%;
        }

        #popCategoriesRoot .top-action-btns .btn {
            width: 100%;
            min-width: 0;
            padding-left: 6px !important;
            padding-right: 6px !important;
            font-size: 10.5px !important;
        }

        #popCategoriesRoot .status-kpi-pill {
            min-height: 62px;
            padding: 9px 11px;
        }

        #popCategoriesRoot .kpi-value {
            font-size: 12px;
        }

        #popCategoriesRoot .panel-section-header {
            padding: 9px 10px;
        }

        #popCategoriesRoot .section-title-text {
            font-size: 11.5px;
            gap: 6px;
        }

        #popCategoriesRoot .section-category-badge {
            display: none;
        }

        #popCategoriesRoot .visibility-setting-box {
            padding: 12px;
            gap: 10px;
        }

        #popCategoriesRoot .visibility-setting-title {
            font-size: 11.5px;
        }

        #popCategoriesRoot .visibility-setting-description {
            font-size: 10.5px;
        }

        #popCategoriesRoot .cat-checklist-container {
            max-height: 235px;
        }

        #popCategoriesRoot .bottom-action-bar .btn {
            width: 100%;
        }

        .popcat-modal-scope .modal-content {
            padding: 19px 18px !important;
        }
    }

    @media (max-width: 359.98px) {
        #popCategoriesRoot .top-action-btns {
            grid-template-columns: 1fr;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        #popCategoriesRoot *,
        .popcat-modal-scope * {
            transition: none !important;
            animation: none !important;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-2" id="popCategoriesRoot">

    {{-- Header & Quick Actions --}}
    <div class="header-banner-card d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 header-top-wrap">

        <div class="header-content">
            <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                <span class="badge {{ $sectionStatus ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' }} header-eyebrow">
                    <i class="fas {{ $sectionStatus ? 'fa-circle-check' : 'fa-circle-pause' }} me-1"></i>
                    {{ $sectionStatus ? 'SLIDER LIVE' : 'SLIDER HIDDEN' }}
                </span>
            </div>

            <h5 class="header-title mb-0 d-flex align-items-center gap-2">
                <i class="fas fa-shield-heart text-primary"></i>
                <span>Popular Categories & Slider</span>
            </h5>

            <p class="header-description mb-0 mt-1">
                Control category slider visibility, items & links.
            </p>
        </div>

        <div class="top-action-btns d-flex gap-2 flex-shrink-0">
            <a href="{{ $categoryIndexRoute }}"
               class="btn btn-outline-secondary btn-compact shadow-none fw-semibold">
                <i class="fas fa-list text-primary"></i>
                <span>Categories List</span>
            </a>

            <button type="submit"
                    form="popularCategoriesForm"
                    class="btn btn-primary btn-compact fw-bold px-3">
                <i class="fas fa-check-circle"></i>
                <span>Save Settings</span>
            </button>
        </div>

    </div>

    {{-- Status Summary --}}
    <div class="row g-2 mb-3">

        <div class="col-12 col-md-4">
            <div class="status-kpi-pill">
                <div class="min-w-0">
                    <span class="kpi-label">Slider Visibility</span>
                    <strong class="kpi-value {{ $sectionStatus ? 'text-success' : 'text-secondary' }}">
                        {{ $sectionStatus ? 'Enabled' : 'Disabled' }}
                    </strong>
                </div>

                <div class="kpi-icon {{ $sectionStatus ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                    <i class="fas fa-toggle-{{ $sectionStatus ? 'on' : 'off' }}"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="status-kpi-pill">
                <div class="min-w-0">
                    <span class="kpi-label">Selected Showcase</span>
                    <strong class="kpi-value text-primary font-tabular">
                        {{ $selectedCount > 0 ? $selectedCount . ' Categories' : 'All Categories' }}
                    </strong>
                </div>

                <div class="kpi-icon bg-primary-subtle text-primary">
                    <i class="fas fa-check-double"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="status-kpi-pill">
                <div class="min-w-0">
                    <span class="kpi-label">Total Catalog Categories</span>
                    <strong class="kpi-value text-dark font-tabular">
                        {{ $totalCats }} Available
                    </strong>
                </div>

                <div class="kpi-icon bg-warning-subtle text-warning-emphasis">
                    <i class="fas fa-layer-group"></i>
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

    <form action="{{ route('admin.theme.setting.popular_categories.update') }}"
          method="POST"
          id="popularCategoriesForm">

        @csrf

        {{-- Section 1: Visibility & Display Rules --}}
        <div class="panel-section-card">

            <div class="panel-section-header d-flex justify-content-between align-items-center">

                <div class="section-title-text">
                    <span class="section-title-icon text-primary">
                        <i class="fas fa-sliders"></i>
                    </span>
                    <span>Section Display Settings</span>
                </div>

                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle section-category-badge">
                        SLIDER MODE
                    </span>

                    <button type="button"
                            class="collapse-toggle-btn"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapseVisibilityRules"
                            aria-expanded="true"
                            aria-controls="collapseVisibilityRules"
                            title="Toggle Section">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>
                </div>

            </div>

            <div class="collapse show" id="collapseVisibilityRules">
                <div class="p-3 bg-light bg-opacity-25">

                    <div class="visibility-setting-box">

                        <div class="pe-2 min-w-0">
                            <label class="visibility-setting-title"
                                   for="popCatSecSwitch">
                                Popular Categories Slider
                            </label>

                            <small class="visibility-setting-description">
                                Enable or disable the homepage category slider.
                            </small>
                        </div>

                        <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                            <input class="form-check-input ms-0"
                                   type="checkbox"
                                   name="show_popular_categories_section"
                                   value="1"
                                   id="popCatSecSwitch"
                                   {{ $sectionStatus ? 'checked' : '' }}>
                        </div>

                    </div>

                </div>
            </div>

        </div>

        {{-- Section 2: Category Selection --}}
        <div class="panel-section-card">

            <div class="panel-section-header d-flex justify-content-between align-items-center">

                <div class="section-title-text">
                    <span class="section-title-icon text-success">
                        <i class="fas fa-list-check"></i>
                    </span>
                    <span>Category Selection & Display</span>
                </div>

                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <span class="badge bg-success-subtle text-success border border-success-subtle section-category-badge">
                        CUSTOM ITEMS
                    </span>

                    <button type="button"
                            class="collapse-toggle-btn"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapseCategorySelection"
                            aria-expanded="true"
                            aria-controls="collapseCategorySelection"
                            title="Toggle Section">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>
                </div>

            </div>

            <div class="collapse show" id="collapseCategorySelection">
                <div class="p-3">

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                        <label class="form-label mb-0">
                            Select Featured Categories
                        </label>

                        <small class="text-muted" style="font-size: 10.5px;">
                            Leave blank to show all active categories automatically.
                        </small>
                    </div>

                    <div class="cat-multi-select-box">

                        {{-- Selected Category Chips --}}
                        <div class="selected-chips-box"
                             id="selectedCatChipsWrapper"
                             aria-live="polite">
                            <span class="text-muted small my-auto placeholder-text"
                                  style="font-size: 11px;">
                                No categories selected yet.
                            </span>
                        </div>

                        {{-- Category Search --}}
                        <div class="position-relative">
                            <i class="fas fa-search text-muted position-absolute top-50 translate-middle-y"
                               style="left: 12px; font-size: 11px; pointer-events: none;"></i>

                            <input type="text"
                                   id="catFilterInput"
                                   class="form-control cat-select-search"
                                   style="padding-left: 32px;"
                                   placeholder="Search categories by name..."
                                   autocomplete="off"
                                   aria-label="Search categories">
                        </div>

                        {{-- Category Checklist --}}
                        <div class="cat-checklist-container" id="catChecklist">

                            @forelse($allCategories as $cat)
                                @php
                                    $isChecked = in_array($cat->id, $selectedCategoryIds ?? []);
                                @endphp

                                <label class="cat-check-item"
                                       data-title="{{ strtolower($cat->name) }}">

                                    <div class="d-flex align-items-center gap-2 min-w-0">

                                        <input class="form-check-input cat-checkbox"
                                               type="checkbox"
                                               name="category_ids[]"
                                               value="{{ $cat->id }}"
                                               data-name="{{ $cat->name }}"
                                               {{ $isChecked ? 'checked' : '' }}>

                                        <span class="cat-item-name">
                                            {{ $cat->name }}
                                        </span>

                                    </div>

                                    <span class="badge bg-light text-secondary border cat-product-count">
                                        {{ $cat->products_count ?? 0 }} Items
                                    </span>

                                </label>

                            @empty
                                <div class="cat-empty-state">
                                    <i class="fas fa-folder-open d-block mb-2 fs-5"></i>
                                    No categories available.
                                </div>
                            @endforelse

                        </div>

                        {{-- Search Empty State --}}
                        <div class="cat-empty-state d-none"
                             id="catSearchEmptyState">
                            <i class="fas fa-search d-block mb-2"></i>
                            No matching categories found.
                        </div>

                    </div>

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-2">
                        <small class="text-muted" style="font-size: 10px;">
                            <i class="fas fa-circle-info me-1"></i>
                            Selected categories will appear in the showcase.
                        </small>

                        <small class="text-primary fw-semibold"
                               id="catSelectedCounter"
                               style="font-size: 10.5px;">
                            {{ $selectedCount }} Selected
                        </small>
                    </div>

                </div>
            </div>

        </div>

        {{-- Bottom Action --}}
        <div class="bottom-action-bar d-flex justify-content-end">
            <button type="submit" class="btn btn-primary btn-compact fw-bold">
                <i class="fas fa-check-circle"></i>
                Save Changes
            </button>
        </div>

    </form>
</div>

{{-- Minimal Notice Modal --}}
<div class="modal fade popcat-modal-scope"
     id="popCatNoticeModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index: 108500;">

    <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
        <div class="modal-content">

            <h6 class="minimal-modal-title" id="popCatNoticeTitle">
                Notification
            </h6>

            <p class="minimal-modal-subtext"
               id="popCatNoticeMessage"></p>

            <div class="d-flex align-items-center">
                <button type="button"
                        class="btn-minimal-action"
                        data-bs-dismiss="modal">
                    OK
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    // =========================================================
    // POPULAR CATEGORIES — JAVASCRIPT
    // =========================================================

    /**
     * Universal Notice Modal
     */
    function showPopCatModalNotice(message, title = 'Notification') {
        const titleEl = document.getElementById('popCatNoticeTitle');
        const msgEl = document.getElementById('popCatNoticeMessage');
        const modalEl = document.getElementById('popCatNoticeModal');

        if (titleEl) {
            titleEl.textContent = title;
        }

        if (msgEl) {
            msgEl.textContent = message;
        }

        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {

        const root = document.getElementById('popCategoriesRoot');

        if (!root) {
            return;
        }

        // Safely display Laravel success message
        const successMessage = @json(session('success'));

        if (successMessage) {
            showPopCatModalNotice(successMessage, 'Success');
        }

        // DOM References
        const searchInput = document.getElementById('catFilterInput');
        const checklist = document.getElementById('catChecklist');
        const chipsWrapper = document.getElementById('selectedCatChipsWrapper');
        const counter = document.getElementById('catSelectedCounter');
        const emptyState = document.getElementById('catSearchEmptyState');

        if (!checklist || !chipsWrapper) {
            return;
        }

        const items = Array.from(
            checklist.querySelectorAll('.cat-check-item')
        );

        const checkboxes = Array.from(
            checklist.querySelectorAll('.cat-checkbox')
        );

        /**
         * Update Category Search
         */
        function filterCategories() {
            const query = (searchInput?.value || '').trim().toLowerCase();

            let visibleCount = 0;

            items.forEach(function (item) {
                const title = (item.getAttribute('data-title') || '').toLowerCase();
                const matches = title.includes(query);

                item.style.display = matches ? 'flex' : 'none';

                if (matches) {
                    visibleCount++;
                }
            });

            if (emptyState) {
                emptyState.classList.toggle(
                    'd-none',
                    visibleCount > 0 || items.length === 0
                );
            }
        }

        /**
         * Render Selected Category Chips
         *
         * Uses textContent instead of innerHTML for category
         * names to prevent HTML injection.
         */
        function renderCatChips() {
            chipsWrapper.replaceChildren();

            const selectedCheckboxes = checkboxes.filter(function (cb) {
                return cb.checked;
            });

            selectedCheckboxes.forEach(function (cb) {

                const chip = document.createElement('span');
                chip.className = 'cat-chip';

                const name = document.createElement('span');
                name.className = 'cat-chip-name';
                name.textContent = cb.dataset.name || '';

                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'chip-remove';
                removeBtn.dataset.id = cb.value;
                removeBtn.title = 'Remove ' + (cb.dataset.name || 'category');
                removeBtn.setAttribute(
                    'aria-label',
                    'Remove ' + (cb.dataset.name || 'category')
                );
                removeBtn.textContent = '\u00D7';

                chip.appendChild(name);
                chip.appendChild(removeBtn);

                chipsWrapper.appendChild(chip);
            });

            // Empty state
            if (selectedCheckboxes.length === 0) {
                const placeholder = document.createElement('span');

                placeholder.className =
                    'text-muted small my-auto placeholder-text';

                placeholder.style.fontSize = '11px';

                placeholder.textContent =
                    'No categories selected yet. All will display automatically.';

                chipsWrapper.appendChild(placeholder);
            }

            // Selected counter
            if (counter) {
                counter.textContent =
                    selectedCheckboxes.length + ' Selected';
            }
        }

        /**
         * Search Event
         */
        if (searchInput) {
            searchInput.addEventListener('input', filterCategories);
        }

        /**
         * Checkbox Change Event
         */
        checkboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', renderCatChips);
        });

        /**
         * Remove Category from Selected Chips
         */
        chipsWrapper.addEventListener('click', function (event) {

            const removeBtn = event.target.closest('.chip-remove');

            if (!removeBtn || !chipsWrapper.contains(removeBtn)) {
                return;
            }

            const targetId = removeBtn.dataset.id;

            const targetCheckbox = checkboxes.find(function (cb) {
                return cb.value === targetId;
            });

            if (targetCheckbox) {
                targetCheckbox.checked = false;

                targetCheckbox.dispatchEvent(
                    new Event('change', { bubbles: true })
                );
            }
        });

        // Initial Render
        renderCatChips();
        filterCategories();

    });
</script>
@endsection
