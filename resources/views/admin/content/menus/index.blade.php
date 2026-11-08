
@extends('layouts.admin')

@section('title', 'Navigation Menus - ' . config('app.name'))

@section('admin_content')
@php
    $currentLocations = !empty($currentMenu->locations) ? json_decode($currentMenu->locations, true) : [];
    $currentItems = !empty($currentMenu->items) ? json_decode($currentMenu->items, true) : [];
@endphp

<style>
    /* =========================================================
       KAIADMIN LITE v1.2.0 — CLASSIC WHITE
       Navigation Menu Management
       Scoped CSS | Bootstrap 5 Compatible
    ========================================================= */

    #navMenusRoot {
        --nav-primary: var(--bs-primary, #1572e8);
        --nav-secondary: #6c757d;
        --nav-success: #31ce36;
        --nav-danger: #f25961;
        --nav-warning: #ffad46;
        --nav-info: #48abf7;

        --nav-body: #f5f7fd;
        --nav-card: #ffffff;
        --nav-surface: #f8f9fc;
        --nav-heading: #2a2f5b;
        --nav-text: #495057;
        --nav-muted: #8d9498;
        --nav-border: #e6e9ef;

        --nav-radius: 7px;
        --nav-shadow: 0 1px 3px rgba(0, 0, 0, .035);

        color: var(--nav-text);
        font-size: 13px;
        min-width: 0;
        width: 100%;
    }

    #navMenusRoot *,
    #menuNoticeModal *,
    #deleteMenuConfirmModal * {
        box-sizing: border-box;
    }

    #navMenusRoot .btn,
    #navMenusRoot .form-control,
    #navMenusRoot .form-select,
    #menuNoticeModal .btn,
    #deleteMenuConfirmModal .btn {
        border-radius: 5px !important;
    }

    #navMenusRoot .btn {
        min-height: 33px;
        font-size: 12px;
        font-weight: 600;
        padding: 6px 12px;
        transition: all .18s ease;
    }

    #navMenusRoot .btn-compact,
    #menuNoticeModal .btn-compact,
    #deleteMenuConfirmModal .btn-compact {
        min-height: 30px;
        padding: 5px 11px !important;
        font-size: 11.5px !important;
        line-height: 1.35 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        white-space: nowrap;
        transition: all .18s ease;
    }

    #navMenusRoot .btn-primary {
        background: var(--nav-primary);
        border-color: var(--nav-primary);
    }

    #navMenusRoot .btn-primary:hover {
        filter: brightness(.93);
    }

    #navMenusRoot .btn:focus-visible,
    #navMenusRoot input:focus-visible,
    #navMenusRoot button:focus-visible {
        outline: 2px solid var(--nav-primary);
        outline-offset: 2px;
    }

    /* Page Navigation Tabs */

    #navMenusRoot .wp-nav-tab-wrapper {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 5px;
        border-bottom: 1px solid var(--nav-border);
        margin-bottom: 16px;
    }

    #navMenusRoot .wp-nav-tab {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 38px;
        padding: 8px 17px;
        margin-bottom: -1px;
        background: transparent;
        border: 1px solid transparent;
        border-bottom: 2px solid transparent;
        color: var(--nav-muted);
        font-size: 12.5px;
        font-weight: 600;
        text-decoration: none;
        transition: all .18s ease;
    }

    #navMenusRoot .wp-nav-tab:hover {
        color: var(--nav-primary);
        background: #f8faff;
    }

    #navMenusRoot .wp-nav-tab.active {
        color: var(--nav-primary);
        background: #fff;
        border-bottom-color: var(--nav-primary);
        font-weight: 700;
    }

    /* Menu Selector */

    #navMenusRoot .wp-menu-select-bar {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        padding: 14px 16px;
        margin-bottom: 18px;
        background: var(--nav-card);
        border: 1px solid var(--nav-border);
        border-radius: var(--nav-radius);
        box-shadow: var(--nav-shadow);
    }

    #navMenusRoot .wp-menu-select-bar > label {
        color: var(--nav-heading) !important;
        font-size: 12.5px !important;
    }

    #navMenusRoot .wp-menu-select-bar form {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }

    #navMenusRoot .wp-menu-select-bar form select {
        min-width: 220px;
    }

    #navMenusRoot .wp-menu-select-bar > span {
        font-size: 12px !important;
    }

    /* Form Controls */

    #navMenusRoot .form-control,
    #navMenusRoot .form-select {
        min-height: 36px;
        padding: 7px 11px;
        color: var(--nav-heading);
        background-color: #fff;
        border: 1px solid #e0e4eb;
        box-shadow: none;
        font-size: 12.5px;
        font-weight: 400;
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    #navMenusRoot .form-control::placeholder {
        color: #aab0b7;
        opacity: 1;
    }

    #navMenusRoot .form-control:focus,
    #navMenusRoot .form-select:focus {
        border-color: var(--nav-primary);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .10);
        outline: none;
    }

    #navMenusRoot .form-label {
        display: block;
        color: var(--nav-heading);
        font-size: 11.5px !important;
        font-weight: 600;
    }

    #navMenusRoot .form-control-sm,
    #navMenusRoot .form-select-sm {
        min-height: 34px;
        font-size: 12px;
    }

    /* Main Content Headings */

    #navMenusRoot .col-lg-4 > h6 {
        color: var(--nav-heading) !important;
        font-size: 14px !important;
        margin-bottom: 12px !important;
    }

    #navMenusRoot h6 {
        color: var(--nav-heading);
    }

    /* Left Accordion Cards */

    #navMenusRoot .wp-accordion-card {
        background: var(--nav-card);
        border: 1px solid var(--nav-border);
        border-radius: var(--nav-radius);
        margin-bottom: 10px;
        overflow: hidden;
        box-shadow: var(--nav-shadow);
        transition: border-color .18s ease;
    }

    #navMenusRoot .wp-accordion-card:hover {
        border-color: #d7dce5;
    }

    #navMenusRoot .wp-accordion-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        min-height: 43px;
        padding: 11px 14px;
        background: #fff;
        border-bottom: 1px solid transparent;
        color: var(--nav-heading);
        font-size: 12.5px;
        font-weight: 700;
        cursor: pointer;
        user-select: none;
        transition: background .18s ease;
    }

    #navMenusRoot .wp-accordion-header:hover {
        background: #f8faff;
    }

    #navMenusRoot .wp-accordion-header i {
        font-size: 11px;
    }

    #navMenusRoot .wp-accordion-body {
        padding: 13px 14px;
        border-top: 1px solid var(--nav-border);
        background: #fff;
    }

    /* Inner Tabs */

    #navMenusRoot .tab-content-nav {
        gap: 5px;
        border-bottom: 1px solid var(--nav-border) !important;
    }

    #navMenusRoot .tab-content-nav .nav-link {
        padding: 7px 11px;
        color: var(--nav-muted);
        font-size: 11.5px;
        font-weight: 600;
        border: none;
        border-bottom: 2px solid transparent;
        border-radius: 0;
        background: transparent;
    }

    #navMenusRoot .tab-content-nav .nav-link:hover {
        color: var(--nav-primary);
    }

    #navMenusRoot .tab-content-nav .nav-link.active {
        color: var(--nav-primary);
        font-weight: 700;
        border-bottom: 2px solid var(--nav-primary);
        background: transparent;
    }

    /* Scrollable Item Lists */

    #navMenusRoot .accordion-scroll-list {
        max-height: 190px;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 3px 5px 3px 0;
        scrollbar-width: thin;
        scrollbar-color: #cbd2dd transparent;
    }

    #navMenusRoot .accordion-scroll-list::-webkit-scrollbar {
        width: 5px;
    }

    #navMenusRoot .accordion-scroll-list::-webkit-scrollbar-track {
        background: transparent;
    }

    #navMenusRoot .accordion-scroll-list::-webkit-scrollbar-thumb {
        background: #cbd2dd;
        border-radius: 10px;
    }

    #navMenusRoot .accordion-scroll-list label {
        min-height: 29px;
        padding: 5px 6px !important;
        margin: 0;
        border-radius: 4px;
        font-size: 12px !important;
        line-height: 1.5;
        transition: background .15s ease;
    }

    #navMenusRoot .accordion-scroll-list label:hover {
        background: #f5f8ff;
    }

    #navMenusRoot .accordion-scroll-list label span {
        min-width: 0;
    }

    /* Checkboxes */

    #navMenusRoot .chk-item,
    #navMenusRoot .loc-check,
    #navMenusRoot #auto-add-pages {
        width: 16px;
        height: 16px;
        min-width: 16px;
        accent-color: var(--nav-primary);
        cursor: pointer;
        flex-shrink: 0;
    }

    #navMenusRoot .cursor-pointer {
        cursor: pointer;
    }

    /* Structure Builder */

    #navMenusRoot .wp-structure-box {
        background: var(--nav-card);
        border: 1px solid var(--nav-border);
        border-radius: var(--nav-radius);
        overflow: hidden;
        box-shadow: var(--nav-shadow);
        min-width: 0;
    }

    #navMenusRoot .wp-structure-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
        padding: 14px 16px;
        background: #fff;
        border-bottom: 1px solid var(--nav-border);
    }

    #navMenusRoot .wp-structure-header .menu-name-input-group {
        max-width: 65%;
        width: 100%;
        min-width: 0;
    }

    #navMenusRoot .wp-structure-header .menu-name-input-group label {
        font-size: 12px !important;
        color: var(--nav-heading) !important;
    }

    #navMenusRoot .wp-structure-header .menu-name-input-group input {
        min-width: 0;
    }

    #navMenusRoot .wp-structure-header .btn-save-top {
        flex-shrink: 0;
    }

    #navMenusRoot .wp-structure-box > .p-3.bg-light {
        background: #f9fafc !important;
        padding: 17px !important;
    }

    #navMenusRoot .wp-structure-box > .p-3.bg-light h6 {
        font-size: 14px !important;
        margin-bottom: 5px !important;
    }

    #navMenusRoot .wp-structure-box > .p-3.bg-light p {
        font-size: 12px !important;
        line-height: 1.65 !important;
        color: #7c8490 !important;
    }

    /* Menu Item Cards */

    #navMenusRoot .menu-item-bar {
        position: relative;
        min-width: 0;
        margin-bottom: 9px;
        background: #fff;
        border: 1px solid #e1e5ec;
        border-radius: 6px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, .025);
        overflow: hidden;
        transition: border-color .18s ease, margin-left .18s ease;
    }

    #navMenusRoot .menu-item-bar:hover {
        border-color: #b9c8dc;
    }

    #navMenusRoot .menu-item-bar.depth-0 {
        margin-left: 0;
        border-left: 3px solid #dce4ee;
    }

    #navMenusRoot .menu-item-bar.depth-1 {
        margin-left: 26px;
        border-left: 3px solid var(--nav-primary);
    }

    #navMenusRoot .menu-item-bar.depth-2 {
        margin-left: 52px;
        border-left: 3px solid var(--nav-success);
    }

    #navMenusRoot .menu-item-bar.depth-3 {
        margin-left: 78px;
        border-left: 3px solid var(--nav-warning);
    }

    #navMenusRoot .menu-item-handle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        min-height: 42px;
        padding: 9px 12px;
        background: #fff;
        color: var(--nav-heading);
        font-size: 12.5px;
        cursor: pointer;
        user-select: none;
        transition: background .18s ease;
    }

    #navMenusRoot .menu-item-handle:hover {
        background: #f8faff;
    }

    #navMenusRoot .item-title-display {
        min-width: 0;
        overflow-wrap: anywhere;
        line-height: 1.5;
    }

    #navMenusRoot .menu-item-handle > div {
        flex-shrink: 0;
    }

    #navMenusRoot .item-type-badge {
        display: inline-flex;
        align-items: center;
        color: #7c8490 !important;
        font-size: 10.5px !important;
        font-weight: 600;
        white-space: nowrap;
    }

    #navMenusRoot .menu-item-settings {
        display: none;
        padding: 13px;
        background: #fbfcfe;
        border-top: 1px solid var(--nav-border);
    }

    #navMenusRoot .menu-item-bar.is-open .menu-item-settings {
        display: block;
    }

    #navMenusRoot .menu-item-settings .form-control {
        background: #fff;
    }

    #navMenusRoot .menu-item-settings .btn {
        min-height: 30px;
    }

    /* Dragging State */

    #navMenusRoot .sortable-ghost {
        opacity: .45;
        background: #eaf2ff !important;
        border: 1px dashed var(--nav-primary) !important;
    }

    #navMenusRoot .sortable-drag {
        opacity: .95;
    }

    /* Empty State */

    #navMenusRoot #empty-canvas-notice {
        padding: 28px 16px !important;
        border: 1px dashed #cbd3df !important;
        border-radius: 6px !important;
        background: #fff !important;
        color: #89919c !important;
        line-height: 1.6;
    }

    /* Menu Settings */

    #navMenusRoot .wp-structure-box > .p-3.bg-white {
        padding: 17px !important;
    }

    #navMenusRoot .wp-structure-box > .p-3.bg-white h6 {
        font-size: 14px !important;
        color: var(--nav-heading) !important;
    }

    #navMenusRoot .wp-structure-box > .p-3.bg-white .row {
        row-gap: 8px;
    }

    #navMenusRoot .wp-structure-box > .p-3.bg-white .row > div:first-child {
        color: #737c88 !important;
        font-size: 12px !important;
    }

    #navMenusRoot .wp-structure-box > .p-3.bg-white label {
        min-height: 27px;
        font-size: 12px !important;
        line-height: 1.6;
    }

    #navMenusRoot .wp-structure-box > .p-3.bg-white label span {
        min-width: 0;
        overflow-wrap: anywhere;
    }

    /* Footer Actions */

    #navMenusRoot .wp-structure-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 13px 16px;
        background: #f9fafc;
        border-top: 1px solid var(--nav-border);
    }

    /* Notice and Confirmation Modals */

    #menuNoticeModal .modal-content,
    #deleteMenuConfirmModal .modal-content {
        border: 1px solid var(--nav-border) !important;
        border-radius: 9px !important;
        box-shadow: 0 12px 40px rgba(0, 0, 0, .12);
    }

    #menuNoticeModal .modal-body,
    #deleteMenuConfirmModal .modal-body {
        padding: 13px !important;
    }

    #menuNoticeModal h6,
    #deleteMenuConfirmModal h6 {
        color: #2a2f5b;
        font-size: 15px !important;
    }

    #menuNoticeModal p,
    #deleteMenuConfirmModal p {
        color: #818a96 !important;
        font-size: 12px !important;
        line-height: 1.65 !important;
    }

    #menuNoticeModal .modal-body > div:first-child,
    #deleteMenuConfirmModal .modal-body > div:first-child {
        margin-bottom: 12px !important;
    }

    /* General Text Optimization */

    #navMenusRoot .text-muted {
        color: #89919c !important;
    }

    #navMenusRoot .text-dark {
        color: #495057 !important;
    }

    #navMenusRoot .text-break {
        overflow-wrap: anywhere;
        word-break: normal;
    }

    #navMenusRoot .border-top,
    #navMenusRoot .border-bottom {
        border-color: var(--nav-border) !important;
    }

    #navMenusRoot .bn-digit,
    #navMenusRoot .font-monospace {
        font-weight: 600 !important;
    }

    /* =========================================================
       RESPONSIVE DESIGN
    ========================================================= */

    /* Large Desktop */

    @media (min-width: 1400px) {
        #navMenusRoot .wp-structure-box > .p-3.bg-light {
            padding: 20px !important;
        }

        #navMenusRoot .wp-structure-box > .p-3.bg-white {
            padding: 20px !important;
        }

        #navMenusRoot .wp-accordion-body {
            padding: 15px;
        }
    }

    /* Laptop and Tablet Landscape */

    @media (max-width: 1199.98px) {
        #navMenusRoot .menu-item-bar.depth-1 {
            margin-left: 20px;
        }

        #navMenusRoot .menu-item-bar.depth-2 {
            margin-left: 40px;
        }

        #navMenusRoot .menu-item-bar.depth-3 {
            margin-left: 60px;
        }
    }

    /* Tablet Portrait */

    @media (max-width: 991.98px) {
        #navMenusRoot .wp-structure-header .menu-name-input-group {
            max-width: 75%;
        }

        #navMenusRoot .wp-structure-box {
            margin-top: 2px;
        }

        #navMenusRoot .col-lg-4 > h6 {
            margin-top: 2px;
        }
    }

    /* Mobile Landscape and Portrait */

    @media (max-width: 767.98px) {
        #navMenusRoot {
            font-size: 12.5px;
        }

        #navMenusRoot .wp-nav-tab-wrapper {
            gap: 4px;
        }

        #navMenusRoot .wp-nav-tab {
            flex: 1 1 auto;
            min-height: 38px;
            padding: 8px 10px;
            font-size: 11.5px;
            text-align: center;
        }

        #navMenusRoot .wp-menu-select-bar {
            flex-direction: column;
            align-items: stretch !important;
            gap: 10px !important;
            padding: 13px;
        }

        #navMenusRoot .wp-menu-select-bar form {
            display: flex;
            flex-direction: row !important;
            align-items: center !important;
            gap: 7px !important;
            width: 100%;
        }

        #navMenusRoot .wp-menu-select-bar form select {
            flex: 1 1 auto;
            min-width: 0 !important;
            width: 100%;
        }

        #navMenusRoot .wp-menu-select-bar > span {
            line-height: 1.6;
        }

        #navMenusRoot .wp-structure-header {
            flex-direction: column;
            align-items: stretch !important;
            gap: 12px;
            padding: 13px;
        }

        #navMenusRoot .wp-structure-header .menu-name-input-group {
            max-width: 100% !important;
            width: 100%;
        }

        #navMenusRoot .wp-structure-header .btn-save-top {
            width: 100%;
            min-height: 36px;
        }

        #navMenusRoot .wp-structure-box > .p-3.bg-light {
            padding: 13px !important;
        }

        #navMenusRoot .wp-structure-box > .p-3.bg-white {
            padding: 14px !important;
        }

        #navMenusRoot .wp-structure-footer {
            padding: 12px;
        }

        #navMenusRoot .wp-structure-footer .btn {
            min-height: 34px;
        }

        /* Compact Nested Menu Indentation */

        #navMenusRoot .menu-item-bar.depth-1 {
            margin-left: 12px;
        }

        #navMenusRoot .menu-item-bar.depth-2 {
            margin-left: 24px;
        }

        #navMenusRoot .menu-item-bar.depth-3 {
            margin-left: 36px;
        }

        #navMenusRoot .menu-item-handle {
            padding: 9px 9px;
            gap: 6px;
        }

        #navMenusRoot .menu-item-settings {
            padding: 11px;
        }

        #navMenusRoot .item-type-badge {
            font-size: 9.5px !important;
        }

        #navMenusRoot .wp-accordion-body {
            padding: 12px;
        }

        #navMenusRoot .accordion-scroll-list {
            max-height: 220px;
        }

        #navMenusRoot .wp-structure-box > .p-3.bg-white .row > div:first-child {
            margin-bottom: 3px !important;
        }
    }

    /* Small Mobile */

    @media (max-width: 400px) {
        #navMenusRoot {
            font-size: 12px;
        }

        #navMenusRoot .wp-nav-tab {
            padding: 8px 6px;
            font-size: 10.5px;
        }

        #navMenusRoot .wp-menu-select-bar {
            padding: 10px;
        }

        #navMenusRoot .wp-menu-select-bar form {
            flex-wrap: nowrap;
        }

        #navMenusRoot .wp-structure-header .menu-name-input-group {
            gap: 7px !important;
        }

        #navMenusRoot .wp-structure-header .menu-name-input-group label {
            font-size: 11px !important;
        }

        #navMenusRoot .menu-item-bar.depth-1 {
            margin-left: 8px;
        }

        #navMenusRoot .menu-item-bar.depth-2 {
            margin-left: 16px;
        }

        #navMenusRoot .menu-item-bar.depth-3 {
            margin-left: 24px;
        }

        #navMenusRoot .menu-item-handle {
            padding: 8px 7px;
        }

        #navMenusRoot .menu-item-settings {
            padding: 9px;
        }

        #navMenusRoot .menu-item-settings .btn-compact {
            padding: 5px 8px !important;
            font-size: 10.5px !important;
        }

        #navMenusRoot .wp-structure-footer {
            padding: 10px;
        }

        #navMenusRoot .wp-structure-footer .btn {
            padding-left: 9px !important;
            padding-right: 9px !important;
            font-size: 10.5px !important;
        }
    }

    /* Prevent Unwanted Horizontal Page Overflow */

    #navMenusRoot .row,
    #navMenusRoot [class*="col-"],
    #navMenusRoot .wp-structure-box,
    #navMenusRoot .wp-accordion-card,
    #navMenusRoot .menu-item-bar {
        min-width: 0;
    }

    @media (prefers-reduced-motion: reduce) {
        #navMenusRoot *,
        #menuNoticeModal *,
        #deleteMenuConfirmModal * {
            transition-duration: .01ms !important;
            animation-duration: .01ms !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="navMenusRoot">

    <!-- Page Navigation -->
    <div class="wp-nav-tab-wrapper">
        <a href="{{ route('admin.content.menus.index') }}" class="wp-nav-tab active">Edit Menus</a>
        <a href="{{ route('admin.content.menus.create') }}" class="wp-nav-tab">Create New Menu</a>
    </div>

    <!-- Menu Selector -->
    <div class="wp-menu-select-bar d-flex align-items-center flex-wrap gap-2">
        <label class="small fw-semibold text-dark mb-0" style="font-size: 12px;">Select a menu to edit:</label>

        <form method="GET" action="{{ route('admin.content.menus.index') }}" class="d-flex align-items-center gap-2">
            <select name="menu" class="form-select form-select-sm fw-semibold" style="min-width: 220px;">
                @foreach($allMenus as $m)
                    @php
                        $locArray = json_decode($m->locations, true) ?: [];
                        $locString = !empty($locArray) ? ' (' . implode(', ', array_map('ucfirst', $locArray)) . ')' : '';
                    @endphp
                    <option value="{{ $m->id }}" {{ optional($currentMenu)->id == $m->id ? 'selected' : '' }}>
                        {{ $m->name }}{{ $locString }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-outline-primary btn-compact shadow-xs">Select</button>
        </form>

        <span class="small text-muted" style="font-size: 12px;">or <a href="{{ route('admin.content.menus.create') }}" class="text-primary text-decoration-none fw-semibold">create a new menu</a>.</span>
    </div>

    <div class="row g-3">

        <!-- Left Column: Add Menu Items -->
        <div class="col-12 col-lg-4">

            <h6 class="fw-bold mb-2 text-dark" style="font-size: 13.5px;">Add Menu Items</h6>

            <!-- Pages Accordion -->
            <div class="wp-accordion-card">
                <div class="wp-accordion-header" onclick="toggleWpAccordion(this)">
                    <span>Pages</span>
                    <i class="fas fa-chevron-up small text-muted"></i>
                </div>

                <div class="wp-accordion-body">
                    <ul class="nav nav-tabs tab-content-nav border-bottom mb-2" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#pages-recent" type="button">Most Recent</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pages-all" type="button">View All</button>
                        </li>
                    </ul>

                    <div class="tab-content accordion-scroll-list">
                        <div class="tab-pane fade show active" id="pages-recent">
                            @forelse($pages->take(8) as $p)
                                <label class="d-flex align-items-center gap-2 small py-1 cursor-pointer text-dark" style="font-size: 12px;">
                                    <input type="checkbox" class="chk-item" data-title="{{ $p->title }}" data-url="/{{ $p->slug }}" data-type="Page">
                                    <span class="text-break">{{ $p->title }}</span>
                                </label>
                            @empty
                                <div class="small text-muted py-2">No pages found.</div>
                            @endforelse
                        </div>

                        <div class="tab-pane fade" id="pages-all">
                            @forelse($pages as $p)
                                <label class="d-flex align-items-center gap-2 small py-1 cursor-pointer text-dark" style="font-size: 12px;">
                                    <input type="checkbox" class="chk-item" data-title="{{ $p->title }}" data-url="/{{ $p->slug }}" data-type="Page">
                                    <span class="text-break">{{ $p->title }}</span>
                                </label>
                            @empty
                                <div class="small text-muted py-2">No pages found.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-2">
                        <button type="button" class="btn btn-light border btn-compact text-secondary" onclick="selectAllInAccordion(this)">Select All</button>
                        <button type="button" class="btn btn-outline-primary btn-compact shadow-xs" onclick="addCheckedItemsToMenu(this)">Add to Menu</button>
                    </div>
                </div>
            </div>

            <!-- Store Endpoints -->
            <div class="wp-accordion-card">
                <div class="wp-accordion-header" onclick="toggleWpAccordion(this)">
                    <span>Store Endpoints</span>
                    <i class="fas fa-chevron-down small text-muted"></i>
                </div>

                <div class="wp-accordion-body" style="display: none;">
                    <div class="accordion-scroll-list">
                        <label class="d-flex align-items-center gap-2 small py-1 cursor-pointer text-dark" style="font-size: 12px;">
                            <input type="checkbox" class="chk-item" data-title="Shop" data-url="{{ route('shop') }}" data-type="Store Page">
                            <span class="text-break">Shop (Storefront)</span>
                        </label>

                        <label class="d-flex align-items-center gap-2 small py-1 cursor-pointer text-dark" style="font-size: 12px;">
                            <input type="checkbox" class="chk-item" data-title="Cart" data-url="{{ route('cart.index') }}" data-type="Store Page">
                            <span class="text-break">Cart</span>
                        </label>

                        <label class="d-flex align-items-center gap-2 small py-1 cursor-pointer text-dark" style="font-size: 12px;">
                            <input type="checkbox" class="chk-item" data-title="Checkout" data-url="{{ route('checkout') }}" data-type="Store Page">
                            <span class="text-break">Checkout</span>
                        </label>

                        <label class="d-flex align-items-center gap-2 small py-1 cursor-pointer text-dark" style="font-size: 12px;">
                            <input type="checkbox" class="chk-item" data-title="My Account" data-url="{{ route('user.dashboard') }}" data-type="Store Page">
                            <span class="text-break">My Account</span>
                        </label>

                        <label class="d-flex align-items-center gap-2 small py-1 cursor-pointer text-dark" style="font-size: 12px;">
                            <input type="checkbox" class="chk-item" data-title="Track Order" data-url="{{ route('guest.track.order') }}" data-type="Store Page">
                            <span class="text-break">Track Order</span>
                        </label>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-2">
                        <button type="button" class="btn btn-light border btn-compact text-secondary" onclick="selectAllInAccordion(this)">Select All</button>
                        <button type="button" class="btn btn-outline-primary btn-compact shadow-xs" onclick="addCheckedItemsToMenu(this)">Add to Menu</button>
                    </div>
                </div>
            </div>

            <!-- Product Categories -->
            <div class="wp-accordion-card">
                <div class="wp-accordion-header" onclick="toggleWpAccordion(this)">
                    <span>Product Categories</span>
                    <i class="fas fa-chevron-down small text-muted"></i>
                </div>

                <div class="wp-accordion-body" style="display: none;">
                    <div class="accordion-scroll-list">
                        @foreach($categories as $cat)
                            <label class="d-flex align-items-center gap-2 small py-1 cursor-pointer text-dark" style="font-size: 12px;">
                                <input type="checkbox" class="chk-item" data-title="{{ $cat->name }}" data-url="{{ route('category.products', $cat->slug ?? $cat->id) }}" data-type="Category">
                                <span class="text-break">{{ $cat->name }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-2">
                        <button type="button" class="btn btn-light border btn-compact text-secondary" onclick="selectAllInAccordion(this)">Select All</button>
                        <button type="button" class="btn btn-outline-primary btn-compact shadow-xs" onclick="addCheckedItemsToMenu(this)">Add to Menu</button>
                    </div>
                </div>
            </div>

            <!-- Posts -->
            <div class="wp-accordion-card">
                <div class="wp-accordion-header" onclick="toggleWpAccordion(this)">
                    <span>Posts</span>
                    <i class="fas fa-chevron-down small text-muted"></i>
                </div>

                <div class="wp-accordion-body" style="display: none;">
                    <div class="accordion-scroll-list">
                        @foreach($blogPosts as $bp)
                            <label class="d-flex align-items-center gap-2 small py-1 cursor-pointer text-dark" style="font-size: 12px;">
                                <input type="checkbox" class="chk-item" data-title="{{ $bp->title }}" data-url="/blog/{{ $bp->slug }}" data-type="Post">
                                <span class="text-break">{{ $bp->title }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-2">
                        <button type="button" class="btn btn-light border btn-compact text-secondary" onclick="selectAllInAccordion(this)">Select All</button>
                        <button type="button" class="btn btn-outline-primary btn-compact shadow-xs" onclick="addCheckedItemsToMenu(this)">Add to Menu</button>
                    </div>
                </div>
            </div>

            <!-- Custom Links -->
            <div class="wp-accordion-card mb-0">
                <div class="wp-accordion-header" onclick="toggleWpAccordion(this)">
                    <span>Custom Links</span>
                    <i class="fas fa-chevron-down small text-muted"></i>
                </div>

                <div class="wp-accordion-body" style="display: none;">
                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-secondary mb-1" style="font-size: 11px;">URL</label>
                        <input type="text" id="cl-url" class="form-control form-control-sm font-monospace" placeholder="https://">
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-secondary mb-1" style="font-size: 11px;">Link Text</label>
                        <input type="text" id="cl-text" class="form-control form-control-sm" placeholder="Menu Title">
                    </div>

                    <div class="text-end pt-2 border-top mt-2">
                        <button type="button" class="btn btn-outline-primary btn-compact shadow-xs" onclick="addCustomLink()">Add to Menu</button>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column: Menu Structure -->
        <div class="col-12 col-lg-8">

            <div class="wp-structure-box">

                <!-- Menu Header -->
                <div class="wp-structure-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2 menu-name-input-group" style="max-width: 65%; width: 100%;">
                        <label class="small fw-bold text-dark text-nowrap mb-0" style="font-size: 12px;">Menu Name</label>
                        <input type="text" id="menu-name" class="form-control form-control-sm fw-semibold" value="{{ optional($currentMenu)->name ?? 'Main Menu' }}" required>
                        <input type="hidden" id="menu-id" value="{{ optional($currentMenu)->id ?? '' }}">
                    </div>

                    <button type="button" class="btn btn-primary btn-compact px-3 shadow-xs btn-save-top" onclick="submitMenuForm()">Save Menu</button>
                </div>

                <!-- Menu Structure -->
                <div class="p-3 bg-light bg-opacity-50 border-bottom">
                    <h6 class="fw-bold text-dark mb-1" style="font-size: 13.5px;">Menu Structure</h6>

                    <p class="text-muted small mb-3" style="font-size: 12px; line-height: 1.4;">
                        Move Right for Sub Menu or Move Left for Parent menu
                    </p>

                    <div id="wp-menu-items-canvas">
                        @forelse($currentItems as $item)
                            @php $depth = $item['depth'] ?? ($item['is_sub'] ?? false ? 1 : 0); @endphp

                            <div class="menu-item-bar depth-{{ $depth }}"
                                 data-url="{{ $item['url'] }}"
                                 data-type="{{ $item['type'] ?? 'Custom' }}"
                                 data-original="{{ $item['original_title'] ?? $item['title'] }}"
                                 data-depth="{{ $depth }}">

                                <div class="menu-item-handle" onclick="toggleItemAccordion(this)">
                                    <span class="item-title-display fw-semibold text-break pe-2">{{ $item['title'] }}</span>

                                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                        <span class="text-muted small item-type-badge bn-digit" style="font-size: 11px;">{{ $depth > 0 ? 'Sub item (Depth ' . $depth . ')' : ($item['type'] ?? 'Custom') }}</span>
                                        <i class="fas fa-caret-down text-secondary"></i>
                                    </div>
                                </div>

                                <div class="menu-item-settings">
                                    <div class="mb-2">
                                        <label class="form-label small fw-semibold text-secondary mb-1" style="font-size: 11px;">Navigation Label</label>
                                        <input type="text" class="form-control form-control-sm item-nav-label" value="{{ $item['title'] }}" oninput="updateItemTitle(this)">
                                    </div>

                                    <div class="mb-2 d-flex flex-wrap gap-2">
                                        <button type="button" class="btn btn-outline-primary btn-compact" onclick="indentItem(this, 1)">
                                            <i class="fas fa-arrow-right me-1" style="font-size: 10px;"></i>
                                            <span>Move Right</span>
                                        </button>

                                        <button type="button" class="btn btn-outline-secondary btn-compact" onclick="indentItem(this, -1)">
                                            <i class="fas fa-arrow-left me-1" style="font-size: 10px;"></i>
                                            <span>Move Left</span>
                                        </button>
                                    </div>

                                    <div class="small text-muted mb-2 text-break" style="font-size: 11.5px;">
                                        Original: <span class="fw-semibold text-dark">{{ $item['original_title'] ?? $item['title'] }}</span>
                                    </div>

                                    <div class="d-flex align-items-center gap-2 pt-2 border-top">
                                        <button type="button" class="btn btn-outline-danger btn-compact" onclick="removeMenuItem(this)">Remove</button>
                                        <button type="button" class="btn btn-light border btn-compact text-secondary" onclick="toggleItemAccordion(this)">Cancel</button>
                                    </div>
                                </div>
                            </div>

                        @empty
                            <div class="p-4 text-center text-muted border rounded bg-white small" id="empty-canvas-notice" style="border-style: dashed !important;">
                                Add menu items from the column on the left.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Menu Locations and Settings -->
                <div class="p-3 bg-white">

                    <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom" style="font-size: 13.5px;">Menu Settings</h6>

                    <div class="row mb-3 align-items-center">
                        <div class="col-12 col-sm-3 text-muted small fw-semibold mb-1 mb-sm-0" style="font-size: 12px;">
                            Auto Add Pages
                        </div>

                        <div class="col-12 col-sm-9">
                            <label class="small cursor-pointer d-flex align-items-center gap-2 text-dark" style="font-size: 12px;">
                                <input type="checkbox" id="auto-add-pages" {{ optional($currentMenu)->auto_add_pages ? 'checked' : '' }}>
                                <span>Automatically add new top-level pages to this menu</span>
                            </label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-sm-3 text-muted small fw-semibold mb-1 mb-sm-0" style="font-size: 12px;">
                            Display Location
                        </div>

                        <div class="col-12 col-sm-9">
                            <div class="d-flex flex-column gap-2 small" style="font-size: 12px;">

                                <label class="cursor-pointer d-flex align-items-center gap-2 text-dark">
                                    <input type="checkbox" class="loc-check" value="primary" {{ in_array('primary', $currentLocations) ? 'checked' : '' }}>
                                    <span><strong>Primary Menu</strong></span>
                                </label>

                                <label class="cursor-pointer d-flex align-items-center gap-2 text-dark">
                                    <input type="checkbox" class="loc-check" value="mobile" {{ in_array('mobile', $currentLocations) ? 'checked' : '' }}>
                                    <span><strong>Mobile Menu</strong></span>
                                </label>

                                <label class="cursor-pointer d-flex align-items-center gap-2 text-dark">
                                    <input type="checkbox" class="loc-check" value="mobile_categories" {{ in_array('mobile_categories', $currentLocations) ? 'checked' : '' }}>
                                    <span><strong>Category Menu</strong></span>
                                </label>

                                <label class="cursor-pointer d-flex align-items-center gap-2 text-dark">
                                    <input type="checkbox" class="loc-check" value="footer" {{ in_array('footer', $currentLocations) ? 'checked' : '' }}>
                                    <span><strong>Footer Menu</strong></span>
                                </label>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="wp-structure-footer d-flex justify-content-between align-items-center">

                    @if(!empty($currentMenu))
                        <form action="{{ route('admin.content.menus.delete', $currentMenu->id) }}" method="POST" id="deleteMenuForm" class="m-0">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-outline-danger btn-compact" onclick="openDeleteMenuModal()">Delete Menu</button>
                        </form>
                    @else
                        <span></span>
                    @endif

                    <button type="button" class="btn btn-primary btn-compact px-3 shadow-xs" onclick="submitMenuForm()">Save Menu</button>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Notice Modal -->
<div class="modal fade" id="menuNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3" style="border-radius: 8px !important;">
            <div class="modal-body p-1">
                <div class="mb-2 text-warning">
                    <i class="fas fa-circle-exclamation fa-2x"></i>
                </div>

                <h6 class="fw-bold text-dark mb-1" id="menuNoticeTitle" style="font-size: 14px;">Notice</h6>

                <p class="text-muted small mb-3" id="menuNoticeMsg" style="font-size: 12px; line-height: 1.45;"></p>

                <button type="button" class="btn btn-dark btn-compact px-4" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteMenuConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3" style="border-radius: 8px !important;">
            <div class="modal-body p-1">
                <div class="mb-2 text-danger">
                    <i class="fas fa-trash-alt fa-2x"></i>
                </div>

                <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">Delete Menu?</h6>

                <p class="text-muted small mb-3" style="font-size: 12px; line-height: 1.45;">
                    Are you sure you want to permanently delete this navigation menu?
                </p>

                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light border btn-compact px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger btn-compact px-3 shadow-xs" onclick="document.getElementById('deleteMenuForm').submit()">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const canvas = document.getElementById('wp-menu-items-canvas');
        if (canvas) {
            new Sortable(canvas, { animation: 150, ghostClass: 'bg-primary-subtle' });
        }
    });

    function showMenuNotice(message, title = 'Notice') {
        document.getElementById('menuNoticeTitle').innerText = title;
        document.getElementById('menuNoticeMsg').innerText = message;
        const modalEl = document.getElementById('menuNoticeModal');
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    function openDeleteMenuModal() {
        const modalEl = document.getElementById('deleteMenuConfirmModal');
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    function toggleWpAccordion(header) {
        const body = header.nextElementSibling;
        const icon = header.querySelector('.fa-chevron-down, .fa-chevron-up');
        if (body.style.display === 'none') {
            body.style.display = 'block';
            if (icon) { icon.classList.remove('fa-chevron-down'); icon.classList.add('fa-chevron-up'); }
        } else {
            body.style.display = 'none';
            if (icon) { icon.classList.remove('fa-chevron-up'); icon.classList.add('fa-chevron-down'); }
        }
    }

    function toggleItemAccordion(el) { el.closest('.menu-item-bar').classList.toggle('is-open'); }
    function updateItemTitle(input) { input.closest('.menu-item-bar').querySelector('.item-title-display').innerText = input.value || 'Untitled'; }

    function indentItem(btn, direction) {
        const bar = btn.closest('.menu-item-bar');
        let currentDepth = parseInt(bar.dataset.depth) || 0;
        let newDepth = currentDepth + direction;

        if (newDepth < 0) newDepth = 0;
        if (newDepth > 3) newDepth = 3;

        bar.classList.remove('depth-0', 'depth-1', 'depth-2', 'depth-3');
        bar.classList.add('depth-' + newDepth);
        bar.dataset.depth = newDepth;

        const badge = bar.querySelector('.item-type-badge');
        badge.innerText = newDepth > 0 ? 'Sub item (Depth ' + newDepth + ')' : bar.dataset.type;
    }

    function removeMenuItem(link) { link.closest('.menu-item-bar').remove(); checkEmptyCanvas(); }

    function checkEmptyCanvas() {
        const canvas = document.getElementById('wp-menu-items-canvas');
        if (canvas.querySelectorAll('.menu-item-bar').length === 0) {
            canvas.innerHTML = '<div class="p-4 text-center text-muted border rounded bg-white small" id="empty-canvas-notice" style="border-style: dashed !important;">Add menu items from the column on the left.</div>';
        }
    }

    function selectAllInAccordion(btn) {
        const checkboxes = btn.closest('.wp-accordion-body').querySelectorAll('.chk-item');
        const allChecked = Array.from(checkboxes).every(c => c.checked);
        checkboxes.forEach(c => c.checked = !allChecked);
        btn.innerText = allChecked ? 'Select All' : 'Deselect All';
    }

    function addCheckedItemsToMenu(btn) {
        const container = btn.closest('.wp-accordion-body');
        const checkedBoxes = container.querySelectorAll('.chk-item:checked');
        if (!checkedBoxes.length) {
            showMenuNotice('Please select at least one item to add to the menu.');
            return;
        }

        const emptyNotice = document.getElementById('empty-canvas-notice');
        if (emptyNotice) emptyNotice.remove();

        const canvas = document.getElementById('wp-menu-items-canvas');
        checkedBoxes.forEach(chk => {
            const title = chk.dataset.title;
            const url = chk.dataset.url;
            const type = chk.dataset.type;

            const itemHtml = `
                <div class="menu-item-bar depth-0" data-url="${url}" data-type="${type}" data-original="${title}" data-depth="0">
                    <div class="menu-item-handle" onclick="toggleItemAccordion(this)">
                        <span class="item-title-display fw-semibold text-break pe-2">${title}</span>
                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            <span class="text-muted small item-type-badge bn-digit" style="font-size: 11px;">${type}</span>
                            <i class="fas fa-caret-down text-secondary"></i>
                        </div>
                    </div>
                    <div class="menu-item-settings">
                        <div class="mb-2">
                            <label class="form-label small fw-semibold text-secondary mb-1" style="font-size: 11px;">Navigation Label</label>
                            <input type="text" class="form-control form-control-sm item-nav-label" value="${title}" oninput="updateItemTitle(this)">
                        </div>
                        <div class="mb-2 d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-outline-primary btn-compact" onclick="indentItem(this, 1)">
                                <i class="fas fa-arrow-right me-1" style="font-size: 10px;"></i>
                                <span>Move Right</span>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-compact" onclick="indentItem(this, -1)">
                                <i class="fas fa-arrow-left me-1" style="font-size: 10px;"></i>
                                <span>Move Left</span>
                            </button>
                        </div>
                        <div class="small text-muted mb-2 text-break" style="font-size: 11.5px;">Original: <span class="fw-semibold text-dark">${title}</span></div>
                        <div class="d-flex align-items-center gap-2 pt-2 border-top">
                            <button type="button" class="btn btn-outline-danger btn-compact" onclick="removeMenuItem(this)">Remove</button>
                            <button type="button" class="btn btn-light border btn-compact text-secondary" onclick="toggleItemAccordion(this)">Cancel</button>
                        </div>
                    </div>
                </div>
            `;
            canvas.insertAdjacentHTML('beforeend', itemHtml);
            chk.checked = false;
        });
    }

    function addCustomLink() {
        const urlInput = document.getElementById('cl-url');
        const textInput = document.getElementById('cl-text');
        const url = urlInput.value.trim();
        const text = textInput.value.trim();

        if (!url || !text) {
            showMenuNotice('Please provide both URL and Link Text.');
            return;
        }

        const emptyNotice = document.getElementById('empty-canvas-notice');
        if (emptyNotice) emptyNotice.remove();

        const canvas = document.getElementById('wp-menu-items-canvas');
        const itemHtml = `
            <div class="menu-item-bar depth-0" data-url="${url}" data-type="Custom Link" data-original="${text}" data-depth="0">
                <div class="menu-item-handle" onclick="toggleItemAccordion(this)">
                    <span class="item-title-display fw-semibold text-break pe-2">${text}</span>
                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                        <span class="text-muted small item-type-badge bn-digit" style="font-size: 11px;">Custom Link</span>
                        <i class="fas fa-caret-down text-secondary"></i>
                    </div>
                </div>
                <div class="menu-item-settings">
                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-secondary mb-1" style="font-size: 11px;">Navigation Label</label>
                        <input type="text" class="form-control form-control-sm item-nav-label" value="${text}" oninput="updateItemTitle(this)">
                    </div>
                    <div class="mb-2 d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-primary btn-compact" onclick="indentItem(this, 1)">
                            <i class="fas fa-arrow-right me-1" style="font-size: 10px;"></i>
                            <span>Move Right</span>
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-compact" onclick="indentItem(this, -1)">
                            <i class="fas fa-arrow-left me-1" style="font-size: 10px;"></i>
                            <span>Move Left</span>
                        </button>
                    </div>
                    <div class="small text-muted mb-2 text-break" style="font-size: 11.5px;">Original: <span class="fw-semibold text-dark">${text}</span></div>
                    <div class="d-flex align-items-center gap-2 pt-2 border-top">
                        <button type="button" class="btn btn-outline-danger btn-compact" onclick="removeMenuItem(this)">Remove</button>
                        <button type="button" class="btn btn-light border btn-compact text-secondary" onclick="toggleItemAccordion(this)">Cancel</button>
                    </div>
                </div>
            </div>
        `;
        canvas.insertAdjacentHTML('beforeend', itemHtml);
        urlInput.value = '';
        textInput.value = '';
    }

    function submitMenuForm() {
        const menuName = document.getElementById('menu-name').value.trim();
        if (!menuName) {
            showMenuNotice('Please provide a menu name.');
            return;
        }

        const items = [];
        document.querySelectorAll('#wp-menu-items-canvas .menu-item-bar').forEach(bar => {
            items.push({
                title: bar.querySelector('.item-nav-label').value.trim(),
                url: bar.dataset.url,
                type: bar.dataset.type,
                original_title: bar.dataset.original,
                depth: parseInt(bar.dataset.depth) || 0,
                is_sub: (parseInt(bar.dataset.depth) || 0) > 0
            });
        });

        const locations = [];
        document.querySelectorAll('.loc-check:checked').forEach(c => locations.push(c.value));

        const payload = {
            _token: '{{ csrf_token() }}',
            menu_id: document.getElementById('menu-id').value,
            name: menuName,
            items: items,
            locations: locations,
            auto_add_pages: document.getElementById('auto-add-pages').checked
        };

        fetch('{{ route("admin.content.menus.save") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                window.location.href = data.redirect;
            } else {
                showMenuNotice('Failed to save menu.', 'Save Error');
            }
        })
        .catch(() => showMenuNotice('Failed to save menu.', 'Save Error'));
    }
</script>
@endsection
