
@extends('layouts.admin')

@section('title', 'Campaigns & Promotional Slides - ' . config('app.name'))

@php
    // Safe media resolver supporting Global Media Library paths
    $resolveThemeImg = function($path, $fallback = 'https://placehold.co/80x80?text=No+Img') {
        if (empty($path) || $path === 'undefined' || $path === 'null') return $fallback;
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path, $fallback);
        }
        if (function_exists('show_image')) return show_image($path, $fallback);
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        return asset(ltrim(str_replace(['public_html/', 'public/'], '', $path), '/'));
    };
@endphp

@section('admin_content')
<style>
    /* =========================================================
       CAMPAIGN STUDIO — KAIADMIN CLASSIC / WHITE DESIGN SYSTEM
    ========================================================= */

    #campaignStudioRoot,
    .campaign-modal-scope {
        --campaign-primary: var(--bs-primary, #1572e8);
        --campaign-primary-rgb: 21, 114, 232;
        --campaign-secondary: #6c757d;
        --campaign-success: var(--bs-success, #31ce36);
        --campaign-warning: var(--bs-warning, #ffad46);
        --campaign-danger: var(--bs-danger, #f25961);
        --campaign-info: var(--bs-info, #48abf7);

        --campaign-body-bg: #f5f7fd;
        --campaign-card-bg: #ffffff;
        --campaign-header-bg: #ffffff;
        --campaign-heading: #2a2f5b;
        --campaign-text: #495057;
        --campaign-muted: #8d9498;
        --campaign-border: #ebedf2;
        --campaign-soft-bg: #f8f9fc;

        --campaign-radius: 5px;
        --campaign-shadow: 0 1px 3px rgba(0, 0, 0, 0.035);
        --campaign-transition: all .2s ease;
    }

    #campaignStudioRoot {
        width: 100%;
        max-width: 100%;
        color: var(--campaign-text);
        font-size: 13px;
        line-height: 1.5;
        min-width: 0;
    }

    #campaignStudioRoot *,
    .campaign-modal-scope * {
        box-sizing: border-box;
    }

    #campaignStudioRoot .text-dark,
    .campaign-modal-scope .text-dark {
        color: var(--campaign-heading) !important;
    }

    #campaignStudioRoot .text-muted,
    .campaign-modal-scope .text-muted {
        color: var(--campaign-muted) !important;
    }

    /* Consistent Border Radius */

    #campaignStudioRoot .btn,
    #campaignStudioRoot button,
    #campaignStudioRoot .form-control,
    #campaignStudioRoot .form-select,
    #campaignStudioRoot .badge,
    #campaignStudioRoot .card,
    #campaignStudioRoot .mgmt-card,
    #campaignStudioRoot .theme-mgmt-header,
    #campaignStudioRoot .campaign-stat-box,
    #campaignStudioRoot .product-multi-select-box,
    #campaignStudioRoot .product-chip,
    #campaignStudioRoot .proof-media-surface,
    #campaignStudioRoot .proof-thumb-box,
    #campaignStudioRoot .collapse-toggle-btn,
    #campaignStudioRoot .action-btn-circle,
    #campaignStudioRoot .slide-item-mobile,
    .campaign-modal-scope .modal-content,
    .campaign-modal-scope .btn,
    .campaign-modal-scope .form-control,
    .campaign-modal-scope .form-select,
    .campaign-modal-scope .badge {
        border-radius: var(--campaign-radius) !important;
    }

    #campaignStudioRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    #campaignStudioRoot .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    .bn-digit,
    .font-monospace {
        font-weight: 600 !important;
    }

    /* =========================================================
       PAGE HEADER
    ========================================================= */

    #campaignStudioRoot .theme-mgmt-header {
        position: relative;
        background: var(--campaign-header-bg);
        color: var(--campaign-text);
        padding: 18px 20px;
        border: 1px solid var(--campaign-border);
        border-left: 3px solid var(--campaign-primary);
        box-shadow: var(--campaign-shadow);
        margin-bottom: 18px;
    }

    #campaignStudioRoot .theme-mgmt-header h5 {
        color: var(--campaign-heading);
        font-size: clamp(16px, 1.5vw, 19px) !important;
        letter-spacing: -.25px;
        line-height: 1.35;
    }

    #campaignStudioRoot .theme-mgmt-header > div > small {
        color: var(--campaign-muted) !important;
        font-size: 12px !important;
    }

    #campaignStudioRoot .theme-mgmt-header .badge {
        font-size: 9px !important;
        font-weight: 700;
        letter-spacing: .35px;
        padding: 5px 8px;
    }

    #campaignStudioRoot .theme-mgmt-header .btn {
        min-height: 36px;
    }

    /* =========================================================
       BUTTON SYSTEM
    ========================================================= */

    #campaignStudioRoot .btn-compact,
    .campaign-modal-scope .btn-compact {
        min-height: 32px;
        padding: 6px 12px !important;
        font-size: 11.5px !important;
        line-height: 1.35 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        white-space: nowrap;
        transition: var(--campaign-transition);
    }

    #campaignStudioRoot .btn,
    .campaign-modal-scope .btn {
        font-weight: 600;
        transition: var(--campaign-transition);
    }

    #campaignStudioRoot .btn:focus-visible,
    .campaign-modal-scope .btn:focus-visible {
        outline: 2px solid var(--campaign-primary);
        outline-offset: 2px;
        box-shadow: none;
    }

    #campaignStudioRoot .btn-primary,
    .campaign-modal-scope .btn-primary {
        background-color: var(--campaign-primary);
        border-color: var(--campaign-primary);
    }

    #campaignStudioRoot .btn-outline-primary,
    .campaign-modal-scope .btn-outline-primary {
        color: var(--campaign-primary);
        border-color: rgba(var(--campaign-primary-rgb), .45);
    }

    #campaignStudioRoot .btn-outline-primary:hover,
    .campaign-modal-scope .btn-outline-primary:hover {
        background-color: var(--campaign-primary);
        border-color: var(--campaign-primary);
        color: #fff;
    }

    #campaignStudioRoot .btn-light,
    .campaign-modal-scope .btn-light {
        background: #fff;
        border-color: var(--campaign-border);
        color: var(--campaign-text);
    }

    #campaignStudioRoot .btn-light:hover,
    .campaign-modal-scope .btn-light:hover {
        background: var(--campaign-soft-bg);
        border-color: #d9dee8;
    }

    /* =========================================================
       MANAGEMENT CARDS
    ========================================================= */

    #campaignStudioRoot .mgmt-card {
        background: var(--campaign-card-bg);
        border: 1px solid var(--campaign-border);
        box-shadow: var(--campaign-shadow);
        display: flex;
        flex-direction: column;
        height: 100%;
        min-width: 0;
        overflow: hidden;
    }

    #campaignStudioRoot .mgmt-card-header {
        min-height: 48px;
        padding: 11px 15px;
        border-bottom: 1px solid var(--campaign-border);
        background: #fff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
    }

    #campaignStudioRoot .mgmt-card-header h6 {
        color: var(--campaign-heading);
        font-size: 13px !important;
        font-weight: 700;
        line-height: 1.4;
    }

    #campaignStudioRoot .mgmt-card-header > div {
        min-width: 0;
    }

    #campaignStudioRoot .mgmt-card-body {
        padding: 16px;
        flex-grow: 1;
        min-width: 0;
    }

    /* =========================================================
       CAMPAIGN OVERVIEW
    ========================================================= */

    #campaignStudioRoot .campaign-stat-box {
        background: #fff;
        border: 1px solid var(--campaign-border);
        padding: 12px 15px;
    }

    #campaignStudioRoot .campaign-stat-item {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding: 9px 0;
        border-bottom: 1px solid var(--campaign-border);
        font-size: 12px;
    }

    #campaignStudioRoot .campaign-stat-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    #campaignStudioRoot .campaign-stat-item > span:first-child {
        color: var(--campaign-muted);
        flex-shrink: 0;
    }

    #campaignStudioRoot .campaign-stat-item > strong,
    #campaignStudioRoot .campaign-stat-item > span:last-child {
        min-width: 0;
        text-align: right;
        overflow-wrap: anywhere;
    }

    #campaignStudioRoot .campaign-stat-item .badge {
        font-size: 10px !important;
    }

    /* =========================================================
       COLLAPSE BUTTON
    ========================================================= */

    #campaignStudioRoot .collapse-toggle-btn {
        width: 30px;
        height: 30px;
        min-width: 30px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid var(--campaign-border);
        color: var(--campaign-muted);
        transition: var(--campaign-transition);
    }

    #campaignStudioRoot .collapse-toggle-btn:hover {
        background: var(--campaign-primary);
        border-color: var(--campaign-primary);
        color: #fff;
    }

    #campaignStudioRoot .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    #campaignStudioRoot .collapse-icon {
        transition: transform .25s ease;
        font-size: 11px;
    }

    /* =========================================================
       PROMOTIONAL SLIDE TABLE
    ========================================================= */

    #campaignStudioRoot .pc-fit-container {
        width: 100%;
        max-width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        scrollbar-width: thin;
        scrollbar-color: #cbd2df transparent;
    }

    #campaignStudioRoot .custom-slide-table {
        width: 100%;
        min-width: 590px;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    #campaignStudioRoot .custom-slide-table th {
        background: #f8f9fc;
        color: #6c757d;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .45px;
        border-bottom: 1px solid var(--campaign-border);
        padding: 11px 9px;
        white-space: nowrap;
        vertical-align: middle;
    }

    #campaignStudioRoot .custom-slide-table td {
        padding: 10px 9px;
        vertical-align: middle;
        font-size: 11.5px;
        color: var(--campaign-text);
        border-bottom: 1px solid #f0f1f5;
        white-space: normal;
    }

    #campaignStudioRoot .custom-slide-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #campaignStudioRoot .custom-slide-table tbody tr {
        transition: background-color .15s ease;
    }

    #campaignStudioRoot .custom-slide-table tbody tr:hover {
        background: #fafbfe;
    }

    #campaignStudioRoot .custom-slide-table .col-slide-thumb {
        width: 76px;
    }

    #campaignStudioRoot .custom-slide-table .col-slide-info {
        min-width: 200px;
        width: auto;
    }

    #campaignStudioRoot .custom-slide-table .col-slide-status {
        width: 100px;
    }

    #campaignStudioRoot .custom-slide-table .col-slide-actions {
        width: 105px;
    }

    #campaignStudioRoot .custom-slide-table td .text-truncate {
        max-width: 100%;
    }

    #campaignStudioRoot .custom-slide-table td strong {
        font-size: 12px !important;
        line-height: 1.4 !important;
    }

    #campaignStudioRoot .custom-slide-table td small {
        font-size: 11px !important;
    }

    /* =========================================================
       IMAGE THUMBNAILS
    ========================================================= */

    #campaignStudioRoot .proof-thumb-box {
        width: 46px;
        height: 46px;
        background: #fff;
        border: 1px solid var(--campaign-border);
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
        position: relative;
    }

    #campaignStudioRoot .proof-thumb-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    #campaignStudioRoot .proof-thumb-hover-overlay {
        position: absolute;
        inset: 0;
        background: rgba(42, 47, 91, .55);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity .15s ease;
    }

    #campaignStudioRoot .proof-thumb-box:hover .proof-thumb-hover-overlay {
        opacity: 1;
    }

    /* =========================================================
       ACTION BUTTONS
    ========================================================= */

    #campaignStudioRoot .action-btn-circle {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        transition: var(--campaign-transition);
        border: 1px solid transparent;
        text-decoration: none;
        cursor: pointer;
    }

    #campaignStudioRoot .action-btn-circle.edit {
        background: #eaf2ff;
        color: #1572e8;
        border-color: #d6e5ff;
    }

    #campaignStudioRoot .action-btn-circle.edit:hover {
        background: #1572e8;
        color: #fff;
    }

    #campaignStudioRoot .action-btn-circle.delete {
        background: #fff0f1;
        color: #f25961;
        border-color: #ffdcdf;
    }

    #campaignStudioRoot .action-btn-circle.delete:hover {
        background: #f25961;
        color: #fff;
    }

    /* =========================================================
       PRODUCT MULTI-SELECT
    ========================================================= */

    #campaignStudioRoot .product-multi-select-box {
        border: 1px solid #e1e5ef;
        background: #fff;
        overflow: hidden;
    }

    #campaignStudioRoot .product-select-search {
        border: none;
        border-bottom: 1px solid var(--campaign-border);
        border-radius: 0 !important;
        padding: 10px 12px;
        font-size: 12px;
        outline: none;
        box-shadow: none !important;
        min-height: 40px;
    }

    #campaignStudioRoot .product-select-search:focus {
        background: #fbfcff;
    }

    #campaignStudioRoot .product-checklist-container {
        max-height: 210px;
        overflow-y: auto;
        padding: 7px;
        scrollbar-width: thin;
        scrollbar-color: #cbd2df transparent;
    }

    #campaignStudioRoot .product-checklist-container::-webkit-scrollbar {
        width: 5px;
    }

    #campaignStudioRoot .product-checklist-container::-webkit-scrollbar-thumb {
        background: #cbd2df;
        border-radius: 5px;
    }

    #campaignStudioRoot .product-check-item {
        padding: 8px 10px;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 2px;
        transition: background-color .12s ease;
    }

    #campaignStudioRoot .product-check-item:hover {
        background: #f5f7fc;
    }

    #campaignStudioRoot .product-check-item .form-check-input {
        margin-top: 0;
        flex-shrink: 0;
    }

    #campaignStudioRoot .selected-chips-box {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        padding: 8px 10px;
        background: #fafbfe;
        border-bottom: 1px solid var(--campaign-border);
        min-height: 42px;
        max-height: 100px;
        overflow-y: auto;
    }

    #campaignStudioRoot .product-chip {
        background: var(--campaign-primary);
        color: #fff;
        padding: 4px 9px;
        font-size: 10px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        max-width: 100%;
    }

    #campaignStudioRoot .product-chip span.chip-text {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 180px;
    }

    #campaignStudioRoot .product-chip .chip-remove {
        cursor: pointer;
        font-size: 14px;
        line-height: 1;
        opacity: .85;
    }

    #campaignStudioRoot .product-chip .chip-remove:hover {
        opacity: 1;
    }

    /* =========================================================
       GLOBAL MEDIA PICKER
    ========================================================= */

    #campaignStudioRoot .proof-media-surface {
        background: #fafbfe;
        border: 1px dashed #d5dbe8;
        padding: 14px;
        transition: var(--campaign-transition);
    }

    #campaignStudioRoot .proof-media-surface:hover {
        border-color: var(--campaign-primary);
        background: #f7f9ff;
    }

    #campaignStudioRoot .proof-media-surface .bg-white {
        border-color: var(--campaign-border) !important;
    }

    /* =========================================================
       FORM CONTROLS
    ========================================================= */

    #campaignStudioRoot .form-label,
    .campaign-modal-scope .form-label {
        font-size: 12px;
        font-weight: 600;
        color: var(--campaign-heading);
        margin-bottom: 6px;
        display: block;
    }

    #campaignStudioRoot .form-control,
    #campaignStudioRoot .form-select,
    .campaign-modal-scope .form-control,
    .campaign-modal-scope .form-select {
        border: 1px solid #e1e5ef;
        background-color: #fff;
        font-size: 12.5px;
        min-height: 38px;
        padding: 7px 11px;
        color: #495057;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #campaignStudioRoot textarea.form-control,
    .campaign-modal-scope textarea.form-control {
        min-height: 76px;
        resize: vertical;
    }

    #campaignStudioRoot .form-control::placeholder,
    .campaign-modal-scope .form-control::placeholder {
        color: #adb5bd;
        opacity: 1;
    }

    #campaignStudioRoot .form-control:focus,
    #campaignStudioRoot .form-select:focus,
    .campaign-modal-scope .form-control:focus,
    .campaign-modal-scope .form-select:focus {
        border-color: var(--campaign-primary);
        box-shadow: 0 0 0 3px rgba(var(--campaign-primary-rgb), .10);
        outline: none;
    }

    #campaignStudioRoot .form-control-color,
    .campaign-modal-scope .form-control-color {
        padding: 4px;
        min-height: 38px;
        cursor: pointer;
    }

    #campaignStudioRoot .form-check-input,
    .campaign-modal-scope .form-check-input {
        border-color: #d5dbe8;
        cursor: pointer;
    }

    #campaignStudioRoot .form-check-input:checked,
    .campaign-modal-scope .form-check-input:checked {
        background-color: var(--campaign-primary);
        border-color: var(--campaign-primary);
    }

    #campaignStudioRoot .form-check-input:focus,
    .campaign-modal-scope .form-check-input:focus {
        border-color: var(--campaign-primary);
        box-shadow: 0 0 0 3px rgba(var(--campaign-primary-rgb), .10);
    }

    /* Enhanced Toggle Switches */

    #campaignStudioRoot .form-switch .form-check-input,
    .campaign-modal-scope .form-switch .form-check-input {
        width: 36px;
        height: 19px;
        cursor: pointer;
        border-color: #d5dbe8;
    }

    #campaignStudioRoot .form-switch .form-check-input:checked,
    .campaign-modal-scope .form-switch .form-check-input:checked {
        background-color: var(--campaign-primary);
        border-color: var(--campaign-primary);
    }

    /* =========================================================
       MODAL DESIGN
    ========================================================= */

    .campaign-modal-scope .modal-dialog {
        width: calc(100% - 30px);
        max-width: 760px;
        margin-left: auto;
        margin-right: auto;
    }

    .campaign-modal-scope .modal-content {
        background: #fff;
        border: 1px solid var(--campaign-border) !important;
        box-shadow: 0 12px 40px rgba(42, 47, 91, .14) !important;
        overflow: hidden;
    }

    .campaign-modal-scope .modal-header {
        min-height: 52px;
        padding: 12px 18px !important;
        background: #fff !important;
        color: var(--campaign-heading) !important;
        border-bottom: 1px solid var(--campaign-border);
    }

    .campaign-modal-scope .modal-header .modal-title {
        color: var(--campaign-heading);
        font-size: 14px !important;
    }

    .campaign-modal-scope .modal-header .modal-title i {
        color: var(--campaign-primary) !important;
    }

    .campaign-modal-scope .modal-body {
        padding: 18px !important;
        max-height: calc(100vh - 160px);
        overflow-y: auto;
        scrollbar-width: thin;
    }

    .campaign-modal-scope .modal-footer {
        padding: 12px 18px !important;
        background: #fff !important;
        border-top: 1px solid var(--campaign-border);
        gap: 8px;
    }

    .campaign-modal-scope .modal-dialog.modal-sm {
        max-width: 380px;
    }

    .campaign-modal-scope .modal-dialog.modal-md {
        max-width: 600px;
    }

    .campaign-modal-scope .modal-dialog.modal-lg {
        max-width: 760px;
    }

    .campaign-modal-scope .modal-body .bg-light {
        background: #f8f9fc !important;
    }

    .campaign-modal-scope .modal-body .border,
    .campaign-modal-scope .modal-footer .border {
        border-color: var(--campaign-border) !important;
    }

    /* =========================================================
       MOBILE SLIDE CARDS
    ========================================================= */

    #campaignStudioRoot .slide-item-mobile {
        border: 1px solid var(--campaign-border);
        padding: 12px;
        margin-bottom: 10px;
        background: #fff;
        display: flex;
        flex-direction: column;
        gap: 10px;
        box-shadow: var(--campaign-shadow);
        min-width: 0;
    }

    #campaignStudioRoot .slide-item-mobile-top {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    #campaignStudioRoot .slide-item-mobile-img {
        width: 48px;
        height: 48px;
        border: 1px solid var(--campaign-border);
        overflow: hidden;
        background: #f8f9fc;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    #campaignStudioRoot .slide-item-mobile-img img {
        max-width: 100%;
        max-height: 100%;
        object-fit: cover;
    }

    #campaignStudioRoot .slide-item-mobile-content {
        flex: 1;
        min-width: 0;
    }

    #campaignStudioRoot .slide-title-mobile {
        font-size: 12px;
        font-weight: 700;
        color: var(--campaign-heading);
        margin-bottom: 3px;
        overflow-wrap: anywhere;
    }

    #campaignStudioRoot .slide-subtitle-mobile {
        font-size: 11px;
        color: var(--campaign-muted);
        margin-bottom: 0;
    }

    #campaignStudioRoot .slide-item-mobile-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        padding-top: 9px;
        border-top: 1px solid var(--campaign-border);
    }

    /* =========================================================
       BADGES
    ========================================================= */

    #campaignStudioRoot .badge,
    .campaign-modal-scope .badge {
        font-weight: 600;
        letter-spacing: .1px;
    }

    #campaignStudioRoot .badge.bg-success-subtle,
    .campaign-modal-scope .badge.bg-success-subtle {
        background: #e8f8ed !important;
        color: #31a354 !important;
        border-color: #ccefd6 !important;
    }

    #campaignStudioRoot .badge.bg-primary-subtle,
    .campaign-modal-scope .badge.bg-primary-subtle {
        background: #eaf2ff !important;
        color: var(--campaign-primary) !important;
        border-color: #d8e6ff !important;
    }

    #campaignStudioRoot .badge.bg-secondary-subtle,
    .campaign-modal-scope .badge.bg-secondary-subtle {
        background: #f0f1f4 !important;
        color: #737982 !important;
        border-color: #e2e4e9 !important;
    }

    /* =========================================================
       RESPONSIVE — TABLET
    ========================================================= */

    @media (max-width: 991.98px) {
        #campaignStudioRoot .theme-mgmt-header {
            padding: 15px;
        }

        #campaignStudioRoot .mgmt-card-header {
            padding: 11px 13px;
        }

        #campaignStudioRoot .mgmt-card-body {
            padding: 14px;
        }

        #campaignStudioRoot .campaign-stat-item {
            padding: 8px 0;
        }

        #campaignStudioRoot .custom-slide-table {
            min-width: 590px;
        }
    }

    /* =========================================================
       RESPONSIVE — MOBILE
    ========================================================= */

    @media (max-width: 767.98px) {
        #campaignStudioRoot {
            padding-left: 10px !important;
            padding-right: 10px !important;
            padding-top: 12px !important;
        }

        #campaignStudioRoot .theme-mgmt-header {
            padding: 13px;
            margin-bottom: 12px;
        }

        #campaignStudioRoot .theme-mgmt-header h5 {
            font-size: 16px !important;
        }

        #campaignStudioRoot .theme-mgmt-header > div > small {
            font-size: 11px !important;
        }

        #campaignStudioRoot .theme-mgmt-header > .btn {
            width: 100%;
        }

        #campaignStudioRoot .mgmt-card-header {
            padding: 10px 11px;
            min-height: 44px;
        }

        #campaignStudioRoot .mgmt-card-header h6 {
            font-size: 12px !important;
        }

        #campaignStudioRoot .mgmt-card-body {
            padding: 12px;
        }

        #campaignStudioRoot .campaign-stat-box {
            padding: 10px 11px;
        }

        #campaignStudioRoot .campaign-stat-item {
            font-size: 11.5px;
            gap: 8px;
        }

        #campaignStudioRoot .campaign-stat-item > span:first-child {
            max-width: 48%;
        }

        #campaignStudioRoot .campaign-stat-item > strong,
        #campaignStudioRoot .campaign-stat-item > span:last-child {
            max-width: 52%;
        }

        #campaignStudioRoot .slide-item-mobile {
            padding: 11px;
            gap: 9px;
        }

        #campaignStudioRoot .slide-item-mobile-actions .btn {
            flex: 1;
            min-height: 34px;
        }

        #campaignStudioRoot .product-chip span.chip-text {
            max-width: 120px;
        }

        #campaignStudioRoot .proof-media-surface {
            padding: 10px;
        }

        .campaign-modal-scope .modal-dialog {
            width: calc(100% - 20px);
            margin: 10px auto;
        }

        .campaign-modal-scope .modal-body {
            padding: 13px !important;
            max-height: calc(100dvh - 135px);
        }

        .campaign-modal-scope .modal-header {
            padding: 11px 13px !important;
        }

        .campaign-modal-scope .modal-footer {
            padding: 10px 13px !important;
        }

        .campaign-modal-scope .modal-dialog-centered {
            min-height: calc(100% - 20px);
        }
    }

    /* =========================================================
       RESPONSIVE — SMALL MOBILE
    ========================================================= */

    @media (max-width: 380px) {
        #campaignStudioRoot .theme-mgmt-header {
            padding: 11px;
        }

        #campaignStudioRoot .theme-mgmt-header h5 {
            font-size: 14px !important;
        }

        #campaignStudioRoot .mgmt-card-header {
            gap: 6px;
        }

        #campaignStudioRoot .campaign-stat-item {
            font-size: 10.5px;
        }

        #campaignStudioRoot .slide-item-mobile {
            padding: 9px;
        }

        #campaignStudioRoot .slide-item-mobile-actions {
            gap: 6px;
        }

        #campaignStudioRoot .slide-item-mobile-actions .btn {
            font-size: 10px !important;
            padding-left: 7px !important;
            padding-right: 7px !important;
        }

        .campaign-modal-scope .modal-dialog {
            width: calc(100% - 12px);
            margin: 6px auto;
        }
    }

    /* =========================================================
       ACCESSIBILITY & OVERFLOW PROTECTION
    ========================================================= */

    #campaignStudioRoot .min-w-0 {
        min-width: 0;
    }

    #campaignStudioRoot img,
    .campaign-modal-scope img {
        max-width: 100%;
    }

    #campaignStudioRoot button,
    .campaign-modal-scope button {
        touch-action: manipulation;
    }

    #campaignStudioRoot .text-truncate {
        min-width: 0;
    }

    .campaign-modal-scope .modal-content,
    .campaign-modal-scope .modal-body {
        overscroll-behavior: contain;
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="campaignStudioRoot">

    <!-- Page Header -->
    <div class="theme-mgmt-header d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3">
        <div class="min-w-0">
            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                <span class="badge {{ ($flashSale->status ?? 1) ? 'bg-success' : 'bg-secondary' }} font-monospace text-uppercase">
                    {{ ($flashSale->status ?? 1) ? 'Campaign Active' : 'Campaign Disabled' }}
                </span>
            </div>

            <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                <i class="fas fa-rectangle-ad text-primary"></i>
                <span>Flashsale Campaigns & Hero Sidebar</span>
            </h5>

            <small class="d-block mt-1">
                Manage flash sales, featured products & slides.
            </small>
        </div>

        <button type="button" class="btn btn-primary btn-compact fw-bold px-3" data-bs-toggle="modal" data-bs-target="#addOfferSlideModal" onclick="prepareAddSlideModal()">
            <i class="fas fa-plus me-1"></i> <span>Add New Slide</span>
        </button>
    </div>

    <!-- Main Content -->
    <div class="row g-3 mb-3">

        <!-- Campaign Overview -->
        <div class="col-12 col-lg-5">
            <div class="mgmt-card">

                <div class="mgmt-card-header">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-stopwatch text-primary"></i>
                        <h6 class="fw-bold mb-0">Active Flash Campaign</h6>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        @if($flashSale->status ?? 1)
                            <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace">Active</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border font-monospace">Disabled</span>
                        @endif

                        <button type="button" class="collapse-toggle-btn" data-bs-toggle="collapse" data-bs-target="#collapseCampaignOverview" aria-expanded="true" aria-controls="collapseCampaignOverview" title="Toggle Section">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>
                </div>

                <div class="collapse show" id="collapseCampaignOverview">
                    <div class="mgmt-card-body d-flex flex-column justify-content-between">

                        <div class="campaign-stat-box mb-3">

                            <div class="campaign-stat-item">
                                <span>Campaign Title:</span>
                                <strong class="text-dark text-truncate ms-2" style="max-width: 180px;">{{ $flashSale->title ?? 'Deals & Offers' }}</strong>
                            </div>

                            <div class="campaign-stat-item">
                                <span>Subtitle:</span>
                                <span class="text-secondary text-truncate ms-2" style="max-width: 180px;">{{ $flashSale->subtitle ?? 'No subtitle set' }}</span>
                            </div>

                            <div class="campaign-stat-item">
                                <span>Expires On:</span>
                                <span class="fw-bold font-monospace text-danger small bn-digit">
                                    {{ !empty($flashSale->end_time) ? date('d M Y, h:i A', strtotime($flashSale->end_time)) : 'Not scheduled' }}
                                </span>
                            </div>

                            <div class="campaign-stat-item">
                                <span>Included Products:</span>
                                <span class="badge bg-primary font-monospace bn-digit px-2 py-1">
                                    {{ count($selectedProductIds ?? []) }} Selected
                                </span>
                            </div>

                            <div class="campaign-stat-item">
                                <span>Category Navigation:</span>
                                <span class="fw-semibold {{ ($showCatMenu ?? true) ? 'text-success' : 'text-muted' }}">
                                    {{ ($showCatMenu ?? true) ? 'Visible' : 'Hidden' }}
                                </span>
                            </div>

                            <div class="campaign-stat-item">
                                <span>Right Slider:</span>
                                <span class="fw-semibold {{ ($flashSale->right_card_status ?? 1) ? 'text-success' : 'text-muted' }}">
                                    {{ ($flashSale->right_card_status ?? 1) ? 'Visible' : 'Hidden' }}
                                </span>
                            </div>

                        </div>

                        <button type="button" class="btn btn-outline-primary btn-compact fw-bold w-100 py-2 d-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#editCampaignModal">
                            <i class="fas fa-sliders-h"></i>
                            <span>Manage Campaign &amp; Products</span>
                        </button>

                    </div>
                </div>
            </div>
        </div>

        <!-- Promotional Slides -->
        <div class="col-12 col-lg-7">
            <div class="mgmt-card">

                <div class="mgmt-card-header">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-images text-success"></i>
                        <h6 class="fw-bold mb-0">Hero Promotional Slides</h6>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-dark border font-monospace bn-digit px-2 py-1">
                            {{ $offerCards->count() }} {{ Str::plural('Slide', $offerCards->count()) }}
                        </span>

                        <button type="button" class="collapse-toggle-btn" data-bs-toggle="collapse" data-bs-target="#collapsePromotionalSlides" aria-expanded="true" aria-controls="collapsePromotionalSlides" title="Toggle Section">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>
                </div>

                <div class="collapse show" id="collapsePromotionalSlides">
                    <div class="mgmt-card-body p-0">

                        <!-- Desktop & Tablet Table -->
                        <div class="table-responsive pc-fit-container d-none d-md-block">
                            <table class="table align-middle custom-slide-table mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-3 col-slide-thumb text-center">Thumb</th>
                                        <th class="col-slide-info">Slide Info &amp; Badge</th>
                                        <th class="col-slide-status text-center">Status</th>
                                        <th class="text-end pe-3 col-slide-actions">Actions</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($offerCards as $card)
                                        @php
                                            $cardImg = $resolveThemeImg($card->image);
                                        @endphp

                                        <tr>
                                            <td class="ps-3 col-slide-thumb text-center">
                                                <div class="proof-thumb-box" onclick="zoomThemeMedia('{{ $cardImg }}', '{{ addslashes($card->title) }}')" title="Click to Zoom Image">
                                                    <img src="{{ $cardImg }}" alt="Preview" onerror="this.src='https://placehold.co/80x80?text=No+Img';">
                                                    <div class="proof-thumb-hover-overlay">
                                                        <i class="fas fa-search-plus text-white"></i>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="col-slide-info">
                                                <div class="min-w-0">
                                                    <span class="badge px-2 py-1 font-monospace mb-1" style="background: {{ $card->badge_bg ?: '#2563eb' }}; color: #ffffff; font-size: 9px;">
                                                        {{ $card->badge_text }}
                                                    </span>

                                                    <strong class="text-dark d-block text-truncate" style="font-size: 12px; line-height: 1.4;" title="{{ $card->title }}">
                                                        {{ $card->title }}
                                                    </strong>

                                                    <small class="text-muted d-block text-truncate" title="{{ $card->subtitle }}">
                                                        {{ $card->subtitle }}
                                                    </small>
                                                </div>
                                            </td>

                                            <td class="col-slide-status text-center">
                                                @if($card->status)
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-2 py-1">Active</span>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary border font-monospace px-2 py-1">Disabled</span>
                                                @endif
                                            </td>

                                            <td class="text-end pe-3 col-slide-actions">
                                                <div class="d-inline-flex gap-1">
                                                    <button type="button" class="action-btn-circle edit"
                                                        data-id="{{ $card->id }}"
                                                        data-title="{{ $card->title }}"
                                                        data-subtitle="{{ $card->subtitle }}"
                                                        data-badge_text="{{ $card->badge_text }}"
                                                        data-badge_bg="{{ $card->badge_bg }}"
                                                        data-btn_text="{{ $card->btn_text }}"
                                                        data-btn_url="{{ $card->btn_url }}"
                                                        data-serial="{{ $card->serial }}"
                                                        data-status="{{ $card->status }}"
                                                        data-image="{{ $cardImg }}"
                                                        onclick="openEditOfferSlideModal(this)"
                                                        title="Edit Slide">
                                                        <i class="fas fa-pen"></i>
                                                    </button>

                                                    <button type="button" class="action-btn-circle delete"
                                                        onclick="openDeleteSlideModal('{{ route('admin.theme.setting.offer_card.destroy', $card->id) }}', '{{ addslashes($card->title) }}')"
                                                        title="Delete Slide">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>

                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-muted small">
                                                <i class="fas fa-images fa-2x opacity-25 mb-2 d-block"></i>
                                                <span class="fw-semibold">No promotional slides found. Click "Add New Slide" to get started.</span>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Slide Cards -->
                        <div class="d-block d-md-none p-2">
                            @forelse($offerCards as $card)
                                @php
                                    $cardImgMob = $resolveThemeImg($card->image);
                                @endphp

                                <div class="slide-item-mobile">
                                    <div class="slide-item-mobile-top">

                                        <div class="proof-thumb-box" onclick="zoomThemeMedia('{{ $cardImgMob }}', '{{ addslashes($card->title) }}')" title="Zoom">
                                            <img src="{{ $cardImgMob }}" alt="Preview" onerror="this.src='https://placehold.co/80x80?text=No+Img';">
                                        </div>

                                        <div class="slide-item-mobile-content">
                                            <div class="d-flex align-items-center gap-1 mb-1 flex-wrap">
                                                <span class="badge px-2 py-1 font-monospace" style="background: {{ $card->badge_bg ?: '#2563eb' }}; color: #ffffff; font-size: 8.5px;">
                                                    {{ $card->badge_text }}
                                                </span>

                                                @if($card->status)
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-2 py-1">Active</span>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary border font-monospace px-2 py-1">Off</span>
                                                @endif
                                            </div>

                                            <div class="slide-title-mobile text-truncate">{{ $card->title }}</div>
                                            <div class="slide-subtitle-mobile text-truncate">{{ $card->subtitle }}</div>
                                        </div>
                                    </div>

                                    <div class="slide-item-mobile-actions">
                                        <button type="button" class="btn btn-outline-primary btn-compact"
                                            data-id="{{ $card->id }}"
                                            data-title="{{ $card->title }}"
                                            data-subtitle="{{ $card->subtitle }}"
                                            data-badge_text="{{ $card->badge_text }}"
                                            data-badge_bg="{{ $card->badge_bg }}"
                                            data-btn_text="{{ $card->btn_text }}"
                                            data-btn_url="{{ $card->btn_url }}"
                                            data-serial="{{ $card->serial }}"
                                            data-status="{{ $card->status }}"
                                            data-image="{{ $cardImgMob }}"
                                            onclick="openEditOfferSlideModal(this)">
                                            <i class="fas fa-pen me-1"></i> <span>Edit</span>
                                        </button>

                                        <button type="button" class="btn btn-outline-danger btn-compact"
                                            onclick="openDeleteSlideModal('{{ route('admin.theme.setting.offer_card.destroy', $card->id) }}', '{{ addslashes($card->title) }}')">
                                            <i class="fas fa-trash-alt me-1"></i> <span>Delete</span>
                                        </button>
                                    </div>
                                </div>

                            @empty
                                <div class="text-center py-4 text-muted small">
                                    <i class="fas fa-images fa-2x opacity-25 mb-2 d-block"></i>
                                    No promotional slides found.
                                </div>
                            @endforelse
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ================= MODAL 1: EDIT FLASH SALE CAMPAIGN ================= -->

<div class="modal fade campaign-modal-scope" id="editCampaignModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.theme.setting.flash_sale.update') }}" method="POST">
                @csrf

                <div class="modal-header d-flex justify-content-between align-items-center">
                    <h6 class="modal-title fw-bold d-flex align-items-center gap-2 mb-0">
                        <i class="fas fa-stopwatch"></i>
                        <span>Edit Flash Sale Campaign</span>
                    </h6>
                    <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-12 col-sm-6">
                            <label class="form-label">Campaign Title *</label>
                            <input type="text" name="title" class="form-control" value="{{ $flashSale->title ?? 'Deals & Offers' }}" required>
                        </div>

                        <div class="col-12 col-sm-6">
                            <label class="form-label">Campaign Subtitle *</label>
                            <input type="text" name="subtitle" class="form-control" value="{{ $flashSale->subtitle ?? 'Limited time discounts' }}" required>
                        </div>

                        <div class="col-12 col-sm-6">
                            <label class="form-label">Countdown Expiry Time</label>
                            <input type="datetime-local" name="end_time" class="form-control font-monospace bn-digit" value="{{ (!empty($flashSale->end_time)) ? date('Y-m-d\TH:i', strtotime($flashSale->end_time)) : '' }}">
                        </div>

                        <div class="col-12 col-sm-6 d-flex align-items-end">
                            <div class="p-3 border bg-light w-100 d-flex align-items-center justify-content-between">
                                <label class="form-check-label small fw-bold text-dark m-0" for="modalCampaignStatus">Campaign Active</label>
                                <div class="form-check form-switch m-0 ps-0">
                                    <input class="form-check-input ms-0" type="checkbox" name="status" value="1" id="modalCampaignStatus" {{ ($flashSale->status ?? 1) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>

                        <!-- Product Checklist -->

                        <div class="col-12 mt-2">
                            <label class="form-label d-flex justify-content-between align-items-center mb-2 flex-wrap gap-1">
                                <span>Select Campaign Products</span>
                                <small class="text-muted fw-normal font-monospace">Select products to showcase</small>
                            </label>

                            <div class="product-multi-select-box">
                                <div class="selected-chips-box" id="selectedChipsWrapper">
                                    <span class="text-muted small my-auto placeholder-text">No products selected yet.</span>
                                </div>

                                <input type="text" id="productFilterInput" class="form-control product-select-search" placeholder="Search products...">

                                <div class="product-checklist-container" id="productChecklist">
                                    @foreach($allProducts as $p)
                                        @php
                                            $isChecked = in_array($p->id, $selectedProductIds ?? []);
                                            $price = $p->sale_price ?? $p->regular_price ?? 0;
                                        @endphp

                                        <label class="product-check-item" data-title="{{ strtolower($p->title) }}">
                                            <div class="d-flex align-items-center gap-2 min-w-0">
                                                <input class="form-check-input mt-0 product-checkbox flex-shrink-0" type="checkbox" name="product_ids[]" value="{{ $p->id }}" data-name="{{ $p->title }}" {{ $isChecked ? 'checked' : '' }}>
                                                <span class="small fw-medium text-dark text-truncate">{{ $p->title }}</span>
                                            </div>

                                            <span class="badge bg-light text-secondary border small font-monospace bn-digit flex-shrink-0 ms-2">৳{{ number_format($price, 2) }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Visibility Settings -->

                        <div class="col-12 pt-3 border-top mt-2">
                            <div class="row g-3">

                                <div class="col-12 col-sm-6">
                                    <div class="p-3 border bg-light d-flex align-items-center justify-content-between h-100">
                                        <label class="form-check-label small fw-bold text-dark m-0" for="modalShowCategoryMenu">
                                            Homepage Category Menu
                                        </label>

                                        <div class="form-check form-switch m-0 ps-0">
                                            <input class="form-check-input ms-0" type="checkbox" name="show_home_category_menu" id="modalShowCategoryMenu" value="1" {{ ($showCatMenu ?? true) ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6">
                                    <div class="p-3 border bg-light d-flex align-items-center justify-content-between h-100">
                                        <label class="form-check-label small fw-bold text-dark m-0" for="modalRightCardStatus">
                                            Promotional Right Banner
                                        </label>

                                        <div class="form-check form-switch m-0 ps-0">
                                            <input class="form-check-input ms-0" type="checkbox" name="right_card_status" id="modalRightCardStatus" value="1" {{ ($flashSale->right_card_status ?? 1) ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-compact fw-bold px-3">Update Campaign</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= MODAL 2: ADD NEW SLIDE ================= -->

<div class="modal fade campaign-modal-scope" id="addOfferSlideModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <form action="{{ route('admin.theme.setting.offer_card.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="modal-header d-flex justify-content-between align-items-center">
                    <h6 class="modal-title fw-bold d-flex align-items-center gap-2 mb-0">
                        <i class="fas fa-plus-circle"></i>
                        <span>Create Promotional Slide</span>
                    </h6>
                    <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-12 col-sm-8">
                            <label class="form-label">Badge Text *</label>
                            <input type="text" name="badge_text" class="form-control" placeholder="Special Discount" value="Special Voucher" required>
                        </div>

                        <div class="col-12 col-sm-4">
                            <label class="form-label">Badge Color</label>
                            <input type="color" name="badge_bg" class="form-control form-control-color w-100" value="#10b981" style="height: 38px;">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Card Title *</label>
                            <input type="text" name="title" class="form-control" placeholder="Promotional Campaign Title" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Card Subtitle *</label>
                            <textarea name="subtitle" class="form-control" rows="2" placeholder="Enter promotion details..." required></textarea>
                        </div>

                        <!-- Feature Image -->

                        <div class="col-12">
                            <label class="form-label d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                <span><i class="fas fa-paperclip text-primary me-1"></i> Feature Image *</span>
                                <span class="badge bg-light text-muted border font-monospace">GLOBAL MEDIA</span>
                            </label>

                            <input type="hidden" name="image_media" id="createSlideMediaInput" value="">

                            <div class="proof-media-surface">
                                <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                                    <button type="button" class="btn btn-primary btn-compact fw-bold" onclick="chooseOfferSlideCreateMedia()">
                                        <i class="fas fa-photo-film me-1"></i> Choose from Media Library
                                    </button>
                                    <span class="small text-muted">Or upload a local file:</span>
                                </div>

                                <input type="file" name="image" id="createSlideImageInput" class="form-control mb-3" accept="image/*" onchange="handleImagePreview(event, 'createSlidePreviewWrap', 'createSlideClearBtn', 'createSlidePlaceholder')">

                                <div class="bg-white border p-3 d-flex align-items-center gap-2" id="createSlidePreviewWrap">
                                    <div class="proof-thumb-box" id="createThumbBox" style="display: none;" onclick="zoomCreateSlidePreview()" title="Click to Zoom">
                                        <img id="createSlidePreviewImgTag" src="" alt="Preview">
                                        <div class="proof-thumb-hover-overlay">
                                            <i class="fas fa-search-plus text-white"></i>
                                        </div>
                                    </div>

                                    <span class="text-muted small my-auto" id="createSlidePlaceholder">
                                        <i class="fas fa-image me-1 text-primary"></i> No slide image selected yet
                                    </span>

                                    <button type="button" class="btn btn-outline-danger btn-compact py-1 px-2 ms-auto d-none" id="createSlideClearBtn" onclick="clearSelectedCreateImage()">
                                        <i class="fas fa-times me-1"></i> Clear
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <label class="form-label">Button Label *</label>
                            <input type="text" name="btn_text" class="form-control" value="Shop Now" required>
                        </div>

                        <div class="col-12 col-sm-6">
                            <label class="form-label">Button Target Link *</label>
                            <input type="text" name="btn_url" class="form-control font-monospace" value="/shop" required>
                        </div>

                        <div class="col-12 col-sm-6">
                            <label class="form-label">Sort Priority</label>
                            <input type="number" name="serial" class="form-control font-monospace bn-digit" value="1">
                        </div>

                        <div class="col-12 col-sm-6 d-flex align-items-end">
                            <div class="p-3 border bg-light w-100 d-flex align-items-center justify-content-between">
                                <label class="form-check-label fw-bold text-dark small m-0" for="createStatusSwitch">Active Slide Status</label>
                                <div class="form-check form-switch m-0 ps-0">
                                    <input class="form-check-input ms-0" type="checkbox" name="status" value="1" id="createStatusSwitch" checked>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-compact fw-bold px-3">Save &amp; Publish</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= MODAL 3: EDIT SLIDE ================= -->

<div class="modal fade campaign-modal-scope" id="editOfferSlideModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <form id="editSlideForm" action="" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="remove_image" id="editRemoveImageFlag" value="0">
                <input type="hidden" name="image_media" id="editSlideMediaInput" value="">

                <div class="modal-header d-flex justify-content-between align-items-center">
                    <h6 class="modal-title fw-bold d-flex align-items-center gap-2 mb-0">
                        <i class="fas fa-pen"></i>
                        <span>Edit Promotional Slide</span>
                    </h6>
                    <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-12 col-sm-8">
                            <label class="form-label">Badge Text *</label>
                            <input type="text" name="badge_text" id="editBadgeText" class="form-control" required>
                        </div>

                        <div class="col-12 col-sm-4">
                            <label class="form-label">Badge Color</label>
                            <input type="color" name="badge_bg" id="editBadgeBg" class="form-control form-control-color w-100" style="height: 38px;">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Card Title *</label>
                            <input type="text" name="title" id="editTitle" class="form-control" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Card Subtitle *</label>
                            <textarea name="subtitle" id="editSubtitle" class="form-control" rows="2" required></textarea>
                        </div>

                        <!-- Feature Image -->

                        <div class="col-12">
                            <label class="form-label d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                <span><i class="fas fa-paperclip text-primary me-1"></i> Feature Image</span>
                                <span class="badge bg-light text-muted border font-monospace">GLOBAL MEDIA</span>
                            </label>

                            <div class="proof-media-surface">
                                <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                                    <button type="button" class="btn btn-primary btn-compact fw-bold" onclick="chooseOfferSlideEditMedia()">
                                        <i class="fas fa-photo-film me-1"></i> Choose from Media Library
                                    </button>
                                    <span class="small text-muted">Or upload a local file:</span>
                                </div>

                                <input type="file" name="image" id="editSlideImageInput" class="form-control mb-3" accept="image/*" onchange="handleEditFileSelection(event)">

                                <div class="bg-white border p-3 d-flex align-items-center gap-2" id="editSlidePreviewWrap">
                                    <div class="proof-thumb-box" id="editThumbBox" onclick="zoomEditSlidePreview()" title="Click to Zoom">
                                        <img id="editCurrentImgTag" src="" alt="Slide Image" onerror="this.src='https://placehold.co/80x80?text=No+Img';">
                                        <div class="proof-thumb-hover-overlay">
                                            <i class="fas fa-search-plus text-white"></i>
                                        </div>
                                    </div>

                                    <span class="text-muted small my-auto d-none" id="editSlidePlaceholder">
                                        <i class="fas fa-image me-1 text-primary"></i> No image selected
                                    </span>

                                    <button type="button" class="btn btn-outline-danger btn-compact py-1 px-2 ms-auto" id="editSlideClearBtn" onclick="clearEditSlideImage()" title="Remove Image">
                                        <i class="fas fa-times me-1"></i> Clear
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <label class="form-label">Button Label *</label>
                            <input type="text" name="btn_text" id="editBtnText" class="form-control" required>
                        </div>

                        <div class="col-12 col-sm-6">
                            <label class="form-label">Button Target Link *</label>
                            <input type="text" name="btn_url" id="editBtnUrl" class="form-control font-monospace" required>
                        </div>

                        <div class="col-12 col-sm-6">
                            <label class="form-label">Sort Priority</label>
                            <input type="number" name="serial" id="editSerial" class="form-control font-monospace bn-digit">
                        </div>

                        <div class="col-12 col-sm-6 d-flex align-items-end">
                            <div class="p-3 border bg-light w-100 d-flex align-items-center justify-content-between">
                                <label class="form-check-label fw-bold text-dark small m-0" for="editStatusSwitch">Active Slide Status</label>
                                <div class="form-check form-switch m-0 ps-0">
                                    <input class="form-check-input ms-0" type="checkbox" name="status" value="1" id="editStatusSwitch">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-compact fw-bold px-3">Update Slide</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= MODAL 4: DELETE CONFIRMATION ================= -->

<div class="modal fade campaign-modal-scope" id="deleteSlideConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-3">
            <div class="modal-body p-2">

                <div class="mb-3 text-danger">
                    <i class="fas fa-trash-can fa-2x"></i>
                </div>

                <h6 class="fw-bold text-dark mb-2" style="font-size: 14px;">Delete Promotional Slide?</h6>

                <p class="text-muted small mb-3" style="line-height: 1.5;">
                    Are you sure you want to remove <strong id="deleteSlideTitle" class="text-dark">this slide</strong>?
                </p>

                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>

                    <form id="confirmedDeleteSlideForm" action="" method="POST" class="d-inline m-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-compact fw-bold">Yes, Delete</button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL 5: IMAGE LIGHTBOX ================= -->

<div class="modal fade campaign-modal-scope" id="slideMediaZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 108000;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content text-center p-2">

            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" id="slideMediaZoomTitle">
                    <i class="fas fa-image text-primary me-1"></i> Slide Preview
                </span>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-2">
                <img id="slideMediaZoomImgTag" src="" alt="Slide Asset" class="img-fluid border" style="max-height: 480px; object-fit: contain; border-radius: 5px;">
            </div>

        </div>
    </div>
</div>

<!-- ================= MODAL 6: NOTICE ================= -->

<div class="modal fade campaign-modal-scope" id="campaignNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 108500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-3">

            <div class="modal-body p-2">

                <div class="mb-3" id="campaignNoticeIconWrap">
                    <i class="fas fa-circle-check text-success fa-2x"></i>
                </div>

                <h6 class="fw-bold text-dark mb-2" id="campaignNoticeTitle" style="font-size: 14px;">Notification</h6>

                <p class="text-muted small mb-3" id="campaignNoticeMessage" style="line-height: 1.5;"></p>

                <button type="button" class="btn btn-primary btn-compact px-4" data-bs-dismiss="modal">Got It</button>

            </div>
        </div>
    </div>
</div>

<script>
let originalEditImageSrc = '';

// Universal Custom Notice Modal
function showCampaignModalNotice(message, title = 'Notice', type = 'info') {
    const titleEl = document.getElementById('campaignNoticeTitle');
    const msgEl   = document.getElementById('campaignNoticeMessage');
    const iconEl  = document.getElementById('campaignNoticeIconWrap');

    if (titleEl) titleEl.innerText = title;
    if (msgEl) msgEl.innerText = message;

    if (iconEl) {
        if (type === 'danger' || type === 'error') {
            iconEl.innerHTML = '<i class="fas fa-circle-xmark text-danger fa-2x"></i>';
        } else if (type === 'success') {
            iconEl.innerHTML = '<i class="fas fa-circle-check text-success fa-2x"></i>';
        } else if (type === 'warning') {
            iconEl.innerHTML = '<i class="fas fa-triangle-exclamation text-warning fa-2x"></i>';
        } else {
            iconEl.innerHTML = '<i class="fas fa-circle-info text-primary fa-2x"></i>';
        }
    }

    const modalEl = document.getElementById('campaignNoticeModal');
    if (modalEl && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

// Global Media Extractor
function extractMediaItem(mediaInput) {
    if (!mediaInput) return null;
    const item = Array.isArray(mediaInput) ? mediaInput[0] : mediaInput;
    if (!item || typeof item !== 'object') return null;
    const rawPath = item.file_path || item.path || item.url || '';
    const rawUrl  = item.url || item.file_path || item.path || '';
    if (!rawPath || rawPath === 'undefined') return null;
    return {
        path: String(rawPath).trim(),
        url:  String(rawUrl).trim()
    };
}

/* Add Modal: Reset & Prepare */
function prepareAddSlideModal() {
    clearSelectedCreateImage();
}

/* Add Modal: Media Library Picker */
function chooseOfferSlideCreateMedia() {
    if (typeof window.openMediaPicker === 'function') {
        window.openMediaPicker({
            multiple: false,
            onSelect: function(media) {
                const picked = extractMediaItem(media);
                if (picked && picked.path) {
                    document.getElementById('createSlideMediaInput').value = picked.path;
                    const fileInput = document.getElementById('createSlideImageInput');
                    if (fileInput) fileInput.value = '';

                    const thumbBox = document.getElementById('createThumbBox');
                    const imgTag = document.getElementById('createSlidePreviewImgTag');
                    if (imgTag) imgTag.src = picked.url;
                    if (thumbBox) thumbBox.style.display = 'inline-flex';
                    document.getElementById('createSlidePlaceholder')?.classList.add('d-none');
                    document.getElementById('createSlideClearBtn')?.classList.remove('d-none');

                    showCampaignModalNotice('Image attached from Global Media Library.', 'Image Selected', 'success');
                }
            }
        });
    } else {
        showCampaignModalNotice('Global Media Library is loading, please try again.', 'Notice', 'warning');
    }
}

function handleImagePreview(event, wrapId, clearBtnId, placeholderId) {
    const file = event.target.files[0];
    const clearBtn = document.getElementById(clearBtnId);
    const placeholder = document.getElementById(placeholderId);
    const thumbBox = document.getElementById('createThumbBox');
    const imgTag = document.getElementById('createSlidePreviewImgTag');

    if (file) {
        document.getElementById('createSlideMediaInput').value = '';
        const reader = new FileReader();
        reader.onload = function(e) {
            if (imgTag) imgTag.src = e.target.result;
            if (thumbBox) thumbBox.style.display = 'inline-flex';
            if (placeholder) placeholder.classList.add('d-none');
            if (clearBtn) clearBtn.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    }
}

function clearSelectedCreateImage() {
    const input = document.getElementById('createSlideImageInput');
    const mediaInput = document.getElementById('createSlideMediaInput');
    const thumbBox = document.getElementById('createThumbBox');
    const clearBtn = document.getElementById('createSlideClearBtn');
    const placeholder = document.getElementById('createSlidePlaceholder');

    if (input) input.value = '';
    if (mediaInput) mediaInput.value = '';
    if (thumbBox) thumbBox.style.display = 'none';
    if (placeholder) placeholder.classList.remove('d-none');
    if (clearBtn) clearBtn.classList.add('d-none');
}

/* Edit Modal: Media Library Picker */
function chooseOfferSlideEditMedia() {
    if (typeof window.openMediaPicker === 'function') {
        window.openMediaPicker({
            multiple: false,
            onSelect: function(media) {
                const picked = extractMediaItem(media);
                if (picked && picked.path) {
                    document.getElementById('editSlideMediaInput').value = picked.path;
                    const fileInput = document.getElementById('editSlideImageInput');
                    if (fileInput) fileInput.value = '';

                    const imgTag = document.getElementById('editCurrentImgTag');
                    const thumbBox = document.getElementById('editThumbBox');
                    if (imgTag) imgTag.src = picked.url;
                    if (thumbBox) thumbBox.style.display = 'inline-flex';
                    document.getElementById('editSlidePlaceholder')?.classList.add('d-none');
                    document.getElementById('editSlideClearBtn')?.classList.remove('d-none');
                    document.getElementById('editRemoveImageFlag').value = '0';

                    showCampaignModalNotice('Image updated from Global Media Library.', 'Image Selected', 'success');
                }
            }
        });
    } else {
        showCampaignModalNotice('Global Media Library is loading, please try again.', 'Notice', 'warning');
    }
}

function handleEditFileSelection(event) {
    const file = event.target.files[0];
    const imgTag = document.getElementById('editCurrentImgTag');
    const thumbBox = document.getElementById('editThumbBox');
    const clearBtn = document.getElementById('editSlideClearBtn');
    const placeholder = document.getElementById('editSlidePlaceholder');
    const removeFlag = document.getElementById('editRemoveImageFlag');

    if (file) {
        document.getElementById('editSlideMediaInput').value = '';
        const reader = new FileReader();
        reader.onload = function(e) {
            if (imgTag) imgTag.src = e.target.result;
            if (thumbBox) thumbBox.style.display = 'inline-flex';
            if (placeholder) placeholder.classList.add('d-none');
            if (clearBtn) clearBtn.classList.remove('d-none');
            if (removeFlag) removeFlag.value = '0';
        };
        reader.readAsDataURL(file);
    }
}

function clearEditSlideImage() {
    const input = document.getElementById('editSlideImageInput');
    const mediaInput = document.getElementById('editSlideMediaInput');
    const thumbBox = document.getElementById('editThumbBox');
    const clearBtn = document.getElementById('editSlideClearBtn');
    const placeholder = document.getElementById('editSlidePlaceholder');
    const removeFlag = document.getElementById('editRemoveImageFlag');

    if (input) input.value = '';
    if (mediaInput) mediaInput.value = '';
    if (thumbBox) thumbBox.style.display = 'none';
    if (placeholder) placeholder.classList.remove('d-none');
    if (clearBtn) clearBtn.classList.add('d-none');
    if (removeFlag) removeFlag.value = '1';
}

function openEditOfferSlideModal(btn) {
    const id = btn.getAttribute('data-id');
    const form = document.getElementById('editSlideForm');
    form.action = "{{ url('/admin/theme-setting/offer-card/update') }}/" + id;

    document.getElementById('editTitle').value = btn.getAttribute('data-title');
    document.getElementById('editSubtitle').value = btn.getAttribute('data-subtitle');
    document.getElementById('editBadgeText').value = btn.getAttribute('data-badge_text');
    document.getElementById('editBadgeBg').value = btn.getAttribute('data-badge_bg');
    document.getElementById('editBtnText').value = btn.getAttribute('data-btn_text');
    document.getElementById('editBtnUrl').value = btn.getAttribute('data-btn_url');
    document.getElementById('editSerial').value = btn.getAttribute('data-serial');
    document.getElementById('editStatusSwitch').checked = (btn.getAttribute('data-status') === '1');
    document.getElementById('editRemoveImageFlag').value = '0';
    document.getElementById('editSlideMediaInput').value = '';

    originalEditImageSrc = btn.getAttribute('data-image');
    document.getElementById('editSlideImageInput').value = '';

    const imgTag = document.getElementById('editCurrentImgTag');
    const thumbBox = document.getElementById('editThumbBox');
    const clearBtn = document.getElementById('editSlideClearBtn');
    const placeholder = document.getElementById('editSlidePlaceholder');

    if (originalEditImageSrc && !originalEditImageSrc.includes('placeholder')) {
        imgTag.src = originalEditImageSrc;
        thumbBox.style.display = 'inline-flex';
        placeholder.classList.add('d-none');
        clearBtn.classList.remove('d-none');
    } else {
        thumbBox.style.display = 'none';
        placeholder.classList.remove('d-none');
        clearBtn.classList.add('d-none');
    }

    const editModal = new bootstrap.Modal(document.getElementById('editOfferSlideModal'));
    editModal.show();
}

function openDeleteSlideModal(actionUrl, title) {
    const form = document.getElementById('confirmedDeleteSlideForm');
    const titleElem = document.getElementById('deleteSlideTitle');
    if (form && titleElem) {
        form.action = actionUrl;
        titleElem.textContent = title;
        const delModal = new bootstrap.Modal(document.getElementById('deleteSlideConfirmModal'));
        delModal.show();
    }
}

// Lightbox Zoom Helpers
function zoomThemeMedia(url, title = 'Slide Image') {
    const titleEl = document.getElementById('slideMediaZoomTitle');
    const imgTag  = document.getElementById('slideMediaZoomImgTag');
    if (url && imgTag) {
        if (titleEl) titleEl.innerText = title;
        imgTag.src = url;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('slideMediaZoomModal')).show();
    }
}

function zoomCreateSlidePreview() {
    const imgTag = document.getElementById('createSlidePreviewImgTag');
    if (imgTag && imgTag.src) {
        zoomThemeMedia(imgTag.src, 'New Slide Preview');
    }
}

function zoomEditSlidePreview() {
    const imgTag = document.getElementById('editCurrentImgTag');
    if (imgTag && imgTag.src) {
        zoomThemeMedia(imgTag.src, 'Slide Preview');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    @if(session('success'))
        showCampaignModalNotice('{{ session('success') }}', 'Success', 'success');
    @endif

    // Product Checklist Live Search & Chips
    const searchInput = document.getElementById('productFilterInput');
    const items = document.querySelectorAll('.product-check-item');
    const chipsWrapper = document.getElementById('selectedChipsWrapper');
    const checkboxes = document.querySelectorAll('.product-checkbox');

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            items.forEach(item => {
                const title = item.getAttribute('data-title');
                if (title && title.includes(query)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    function renderChips() {
        chipsWrapper.innerHTML = '';
        let hasSelected = false;

        checkboxes.forEach(cb => {
            if (cb.checked) {
                hasSelected = true;
                const id = cb.value;
                const name = cb.getAttribute('data-name');

                const chip = document.createElement('span');
                chip.className = 'product-chip';
                chip.innerHTML = `
                    <span class="chip-text">${name}</span>
                    <span class="chip-remove" data-id="${id}">&times;</span>
                `;
                chipsWrapper.appendChild(chip);
            }
        });

        if (!hasSelected) {
            chipsWrapper.innerHTML = '<span class="text-muted small my-auto placeholder-text" style="font-size: 11px;">No products selected yet.</span>';
        }
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', renderChips);
    });

    chipsWrapper.addEventListener('click', function (e) {
        const removeBtn = e.target.closest('.chip-remove');
        if (removeBtn) {
            const targetId = removeBtn.getAttribute('data-id');
            const targetCb = document.querySelector(`.product-checkbox[value="${targetId}"]`);
            if (targetCb) {
                targetCb.checked = false;
                renderChips();
            }
        }
    });

    renderChips();
});
</script>
@endsection
