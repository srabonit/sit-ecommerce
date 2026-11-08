@extends('layouts.admin')

@section('title', 'Media Library - ' . config('app.name'))

@section('admin_content')
@php
    $currentCategory = $activeCategory ?? request('category', 'site_content');
    $counts = $categoryCounts ?? [
        'site_content'   => 0,
        'user_submitted' => 0,
        'all'            => $files->total(),
    ];
@endphp

<style>
    /* =========================================================
       MEDIA LIBRARY — KAIADMIN CLASSIC DESIGN SYSTEM
       Scoped CSS | Bootstrap 5 Compatible
    ========================================================= */

    #mediaLibraryRoot {
        /* Theme Variables */
        --ml-primary: var(--bs-primary, #1572e8);
        --ml-secondary: #6c757d;
        --ml-success: var(--bs-success, #31ce36);
        --ml-danger: var(--bs-danger, #f25961);
        --ml-warning: var(--bs-warning, #ffad46);
        --ml-info: var(--bs-info, #48abf7);

        --ml-body-bg: #f8f9fa;
        --ml-card-bg: #ffffff;
        --ml-heading: #1a2035;
        --ml-text: #495057;
        --ml-muted: #8d9498;
        --ml-border: #ebedf2;
        --ml-input-border: #e8e8e8;

        --ml-radius: 5px;
        --ml-card-radius: 7px;

        --ml-shadow: 0 1px 3px rgba(0, 0, 0, .04);
        --ml-shadow-hover: 0 4px 12px rgba(0, 0, 0, .07);

        color: var(--ml-text);
        min-width: 0;
        width: 100%;
        font-size: 13px;
    }

    /* ==============================
       GLOBAL ELEMENTS
    ============================== */

    #mediaLibraryRoot *,
    #replaceMediaModal *,
    #usageTrackingModal *,
    #mediaNoticeModal *,
    #mediaConfirmModal * {
        box-sizing: border-box;
    }

    #mediaLibraryRoot .btn,
    #mediaLibraryRoot .form-control,
    #mediaLibraryRoot .form-select,
    #mediaLibraryRoot .badge,
    #mediaLibraryRoot .input-group-text,
    #replaceMediaModal .btn,
    #replaceMediaModal .form-control,
    #usageTrackingModal .btn,
    #mediaNoticeModal .btn,
    #mediaConfirmModal .btn {
        border-radius: var(--ml-radius);
    }

    #mediaLibraryRoot .btn:focus,
    #mediaLibraryRoot .form-control:focus,
    #mediaLibraryRoot .form-select:focus {
        outline: none;
    }

    #mediaLibraryRoot .btn {
        font-weight: 600;
        transition: background-color .18s ease,
                    border-color .18s ease,
                    box-shadow .18s ease;
    }

    #mediaLibraryRoot .btn-compact,
    #replaceMediaModal .btn-compact,
    #usageTrackingModal .btn-compact,
    #mediaNoticeModal .btn-compact,
    #mediaConfirmModal .btn-compact {
        min-height: 32px;
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.35;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        white-space: nowrap;
    }

    #mediaLibraryRoot .btn-compact i {
        font-size: 11px;
    }

    #mediaLibraryRoot .btn-primary,
    #mediaLibraryRoot .btn-success,
    #mediaLibraryRoot .btn-danger,
    #mediaLibraryRoot .btn-warning {
        box-shadow: none;
    }

    #mediaLibraryRoot .btn-primary:hover {
        box-shadow: 0 3px 8px rgba(21, 114, 232, .16);
    }

    #mediaLibraryRoot .btn:disabled {
        opacity: .65;
        cursor: not-allowed;
    }

    /* ==============================
       PAGE HEADER
    ============================== */

    #mediaLibraryRoot .media-page-header {
        margin-bottom: 20px;
    }

    #mediaLibraryRoot .media-page-title {
        color: var(--ml-heading);
        font-size: clamp(17px, 1.5vw, 20px);
        font-weight: 700;
        line-height: 1.3;
        letter-spacing: -.35px;
    }

    #mediaLibraryRoot .media-page-title i {
        font-size: 19px;
    }

    #mediaLibraryRoot .media-page-description {
        color: var(--ml-muted);
        font-size: 12px;
        line-height: 1.6;
        max-width: 680px;
    }

    #mediaLibraryRoot .header-top-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }

    /* ==============================
       PANEL / CARD
    ============================== */

    #mediaLibraryRoot .media-panel-card {
        background: var(--ml-card-bg);
        border: 1px solid var(--ml-border);
        border-radius: var(--ml-card-radius);
        box-shadow: var(--ml-shadow);
        min-width: 0;
    }

    /* ==============================
       CATEGORY NAVIGATION
    ============================== */

    #mediaLibraryRoot .media-section-tabs {
        display: flex;
        flex-wrap: wrap;
        align-items: stretch;
        gap: 8px;
        margin-bottom: 16px;
    }

    #mediaLibraryRoot .media-section-tab {
        min-height: 40px;
        padding: 9px 13px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;

        background: #ffffff;
        color: #5c6573;
        border: 1px solid var(--ml-border);
        border-radius: var(--ml-radius);

        text-decoration: none;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.4;

        transition: all .18s ease;
    }

    #mediaLibraryRoot .media-section-tab:hover {
        background: #f8f9fc;
        color: var(--ml-primary);
        border-color: #c9dafa;
    }

    #mediaLibraryRoot .media-section-tab.active-site {
        background: #eaf2ff;
        color: var(--ml-primary) !important;
        border-color: #c8dcff;
    }

    #mediaLibraryRoot .media-section-tab.active-user {
        background: #e8f7f2;
        color: #159d75 !important;
        border-color: #bde9d9;
    }

    #mediaLibraryRoot .media-section-tab > i {
        font-size: 14px;
    }

    #mediaLibraryRoot .media-section-tab .badge {
        font-size: 10px;
        font-weight: 700;
        min-width: 23px;
        padding: 4px 6px;
    }

    /* ==============================
       UPLOAD DROPZONE
    ============================== */

    #mediaLibraryRoot .wp-upload-collapsible {
        position: relative;
        padding: 32px 20px;
        margin-bottom: 16px;

        text-align: center;
        background: #ffffff;
        border: 2px dashed #d6dce5;
        border-radius: var(--ml-card-radius);

        transition: background .2s ease, border-color .2s ease;
    }

    #mediaLibraryRoot .wp-upload-collapsible:hover,
    #mediaLibraryRoot .wp-upload-collapsible.dragover {
        background: #f4f8ff;
        border-color: var(--ml-primary);
    }

    #mediaLibraryRoot .upload-icon-circle {
        width: 52px;
        height: 52px;
        display: inline-flex;
        align-items: center;
        justify-content: center;

        margin-bottom: 12px;
        border-radius: 50%;

        background: #eaf2ff;
        color: var(--ml-primary);
        font-size: 21px;
    }

    #mediaLibraryRoot .upload-heading {
        color: var(--ml-heading);
        font-size: 15px;
        font-weight: 700;
    }

    #mediaLibraryRoot .upload-description {
        color: var(--ml-muted);
        font-size: 12px;
        line-height: 1.6;
    }

    #mediaLibraryRoot #upload-target-category {
        min-height: 36px;
        width: 100%;
    }

    #mediaLibraryRoot #wp-collapsible-upload-box .btn-compact {
        min-height: 36px;
    }

    #mediaLibraryRoot #upload-progress-indicator {
        font-size: 12px;
    }

    /* ==============================
       FILTER TOOLBAR
    ============================== */

    #mediaLibraryRoot .media-filter-panel {
        padding: 16px;
        margin-bottom: 16px;
    }

    #mediaLibraryRoot .form-control,
    #mediaLibraryRoot .form-select {
        min-height: 36px;
        padding: 7px 10px;

        color: var(--ml-heading);
        background-color: #ffffff;
        border: 1px solid var(--ml-input-border);

        font-size: 12.5px;
        line-height: 1.4;

        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #mediaLibraryRoot .form-control::placeholder {
        color: #adb5bd;
        opacity: 1;
    }

    #mediaLibraryRoot .form-control:focus,
    #mediaLibraryRoot .form-select:focus {
        border-color: var(--ml-primary);
        box-shadow: 0 0 0 2px rgba(21, 114, 232, .10);
    }

    #mediaLibraryRoot .input-group > .form-control {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    #mediaLibraryRoot .input-group > .btn {
        min-width: 42px;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    #mediaLibraryRoot .form-select-sm,
    #mediaLibraryRoot .form-control-sm {
        min-height: 34px;
        font-size: 12px;
    }

    /* ==============================
       BULK ACTION TOOLBAR
    ============================== */

    #mediaLibraryRoot .bulk-toolbar-strip {
        padding: 11px 13px;
        background: #fff5f5;
        border: 1px solid #f8d7da;
        border-radius: var(--ml-radius);
    }

    #mediaLibraryRoot .bulk-toolbar-strip > span {
        font-size: 12px;
        line-height: 1.5;
    }

    #mediaLibraryRoot .bulk-checkbox {
        position: absolute;
        top: 7px;
        left: 7px;
        z-index: 6;

        width: 19px;
        height: 19px;
        margin: 0;

        display: none;
        cursor: pointer;

        border: 1px solid #aab2bd;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .15);
    }

    #mediaLibraryRoot .bulk-select-active .bulk-checkbox {
        display: block;
    }

    /* ==============================
       MEDIA GRID
    ============================== */

    #mediaLibraryRoot .media-card-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(125px, 1fr));
        gap: 12px;

        max-height: 72vh;
        overflow-y: auto;
        overflow-x: hidden;

        padding: 3px;
        scrollbar-width: thin;
        scrollbar-color: #d5dbe3 transparent;
    }

    #mediaLibraryRoot .media-card-grid::-webkit-scrollbar {
        width: 5px;
    }

    #mediaLibraryRoot .media-card-grid::-webkit-scrollbar-thumb {
        background: #d5dbe3;
        border-radius: 5px;
    }

    #mediaLibraryRoot .media-card-item {
        position: relative;
        aspect-ratio: 1 / 1;
        min-width: 0;

        overflow: hidden;
        cursor: pointer;

        background: #f7f8fa;
        border: 1px solid #e5e8ed;
        border-radius: 6px;

        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    #mediaLibraryRoot .media-card-item:hover {
        border-color: #aabbd5;
        transform: translateY(-2px);
        box-shadow: var(--ml-shadow-hover);
    }

    #mediaLibraryRoot .media-card-item.selected {
        border: 2px solid var(--ml-primary) !important;
        box-shadow: 0 0 0 2px rgba(21, 114, 232, .12);
    }

    #mediaLibraryRoot .media-card-item.selected::after {
        content: "\f00c";
        font-family: "Font Awesome 6 Free";
        font-weight: 900;

        position: absolute;
        top: 7px;
        right: 7px;
        z-index: 4;

        width: 21px;
        height: 21px;

        display: flex;
        align-items: center;
        justify-content: center;

        color: #ffffff;
        background: var(--ml-primary);
        border-radius: 4px;

        font-size: 10px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, .15);
    }

    #mediaLibraryRoot .bulk-select-active .media-card-item.selected::after {
        display: none;
    }

    #mediaLibraryRoot .media-card-item img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
    }

    #mediaLibraryRoot .media-card-source-badge {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;

        padding: 5px 7px;

        color: #ffffff;
        background: rgba(26, 32, 53, .78);

        font-size: 10px;
        font-weight: 500;
        line-height: 1.4;

        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        pointer-events: none;
    }

    #mediaLibraryRoot .media-card-item > div.bg-light {
        background: #f5f6fa !important;
    }

    #mediaLibraryRoot .media-card-item > div.bg-light i {
        font-size: 27px;
    }

    /* ==============================
       ATTACHMENT INSPECTOR
    ============================== */

    #mediaLibraryRoot .attachment-details-sidebar {
        position: sticky;
        top: 76px;

        padding: 16px;
        min-width: 0;

        background: #ffffff;
        border: 1px solid var(--ml-border);
        border-radius: var(--ml-card-radius);
        box-shadow: var(--ml-shadow);
    }

    #mediaLibraryRoot .attachment-details-sidebar h6 {
        color: var(--ml-heading);
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .035em;
    }

    #mediaLibraryRoot .sidebar-preview-stage {
        display: flex;
        align-items: center;
        justify-content: center;

        min-height: 155px;
        padding: 12px;

        background-color: #f8f9fc;
        background-image: radial-gradient(#dce1e9 1px, transparent 1px);
        background-size: 12px 12px;

        border: 1px solid var(--ml-border);
        border-radius: 5px;
    }

    #mediaLibraryRoot .sidebar-preview-stage img {
        max-width: 100%;
        max-height: 165px;
        object-fit: contain;
    }

    #mediaLibraryRoot .sidebar-meta-list {
        padding: 11px 12px;

        color: #78818d;
        background: #f8f9fc;

        border: 1px solid #edf0f4;
        border-radius: 5px;

        font-size: 11.5px;
        line-height: 1.8;

        overflow-wrap: anywhere;
    }

    #mediaLibraryRoot .sidebar-meta-list strong {
        color: var(--ml-heading);
        font-weight: 700;
    }

    #mediaLibraryRoot .sidebar-meta-list .text-primary {
        color: var(--ml-primary) !important;
    }

    /* Inspector Form */

    #mediaLibraryRoot .attachment-details-sidebar .form-label {
        display: block;
        margin-bottom: 5px;

        color: #596273;
        font-size: 11px;
        font-weight: 600;
    }

    #mediaLibraryRoot .attachment-details-sidebar .form-control,
    #mediaLibraryRoot .attachment-details-sidebar .form-select {
        width: 100%;
        min-height: 35px;
        font-size: 12px;
    }

    #mediaLibraryRoot .attachment-details-sidebar textarea.form-control {
        min-height: 58px;
        resize: vertical;
    }

    #mediaLibraryRoot .attachment-details-sidebar .input-group .form-control {
        min-width: 0;
    }

    #mediaLibraryRoot .attachment-details-sidebar .btn-compact {
        min-height: 32px;
    }

    #mediaLibraryRoot .attachment-details-sidebar .row {
        --bs-gutter-x: 6px;
        --bs-gutter-y: 6px;
    }

    /* Empty Inspector */

    #mediaLibraryRoot #sidebar-placeholder {
        padding: 35px 20px !important;
        border: 1px solid var(--ml-border);
        background: #ffffff;
    }

    #mediaLibraryRoot #sidebar-placeholder i {
        color: #b6beca;
    }

    #mediaLibraryRoot #sidebar-placeholder p {
        color: #89919d;
        font-size: 12px;
        line-height: 1.7;
    }

    /* ==============================
       PAGINATION
    ============================== */

    #mediaLibraryRoot .media-pagination-wrap {
        width: 100%;
        overflow: hidden;
    }

    #mediaLibraryRoot .media-pagination-wrap nav {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 10px;
        width: 100% !important;
    }

    #mediaLibraryRoot .media-pagination-wrap .pagination {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 4px;
        margin: 0;
    }

    #mediaLibraryRoot .media-pagination-wrap .page-link {
        min-width: 30px;
        padding: 5px 10px;

        color: #596273;
        background: #ffffff;
        border: 1px solid var(--ml-border);
        border-radius: 5px;

        font-size: 12px;
        text-align: center;
    }

    #mediaLibraryRoot .media-pagination-wrap .page-item.active .page-link {
        color: #ffffff !important;
        background: var(--ml-primary) !important;
        border-color: var(--ml-primary) !important;
        font-weight: 700;
    }

    #mediaLibraryRoot .media-pagination-wrap .page-item.disabled .page-link {
        color: #b6beca;
        background: #f8f9fa;
    }

    /* ==============================
       TYPOGRAPHY & DATA
    ============================== */

    #mediaLibraryRoot .bn-digit,
    #mediaLibraryRoot .font-monospace {
        font-weight: 600 !important;
    }

    #mediaLibraryRoot .text-muted {
        color: #8d9498 !important;
    }

    #mediaLibraryRoot .shadow-xs {
        box-shadow: var(--ml-shadow);
    }

    /* ==============================
       MODAL DESIGN
    ============================== */

    #replaceMediaModal .modal-content,
    #usageTrackingModal .modal-content,
    #mediaNoticeModal .modal-content,
    #mediaConfirmModal .modal-content {
        border: 1px solid var(--ml-border) !important;
        border-radius: 8px !important;
        box-shadow: 0 10px 35px rgba(0, 0, 0, .12) !important;
    }

    #replaceMediaModal .modal-content,
    #usageTrackingModal .modal-content {
        padding: 20px !important;
    }

    #replaceMediaModal .modal-content h6,
    #usageTrackingModal .modal-content h6 {
        color: var(--ml-heading);
        font-weight: 700;
    }

    #replaceMediaModal .modal-content p,
    #usageTrackingModal .modal-content p {
        font-size: 12px;
        line-height: 1.6;
    }

    #replaceMediaModal .form-control {
        min-height: 38px;
        font-size: 12px;
    }

    #usageTrackingModal #usage-list-container {
        max-height: 320px !important;
        overflow-y: auto;
        overflow-x: hidden;
    }

    #usageTrackingModal .list-group-item {
        padding-top: 10px !important;
        padding-bottom: 10px !important;
        border-color: var(--ml-border);
        gap: 8px;
    }

    #usageTrackingModal .list-group-item > div {
        min-width: 0;
    }

    #mediaNoticeModal .modal-content,
    #mediaConfirmModal .modal-content {
        padding: 22px !important;
    }

    #mediaNoticeModal .modal-body,
    #mediaConfirmModal .modal-body {
        padding: 5px !important;
    }

    #mediaNoticeTitle,
    #mediaConfirmTitle {
        color: var(--ml-heading);
        font-size: 15px;
        font-weight: 700;
    }

    #mediaNoticeMsg,
    #mediaConfirmMsg {
        font-size: 12px;
        line-height: 1.6;
        overflow-wrap: anywhere;
    }

    #mediaLibraryRoot .btn-close,
    #replaceMediaModal .btn-close,
    #usageTrackingModal .btn-close {
        flex-shrink: 0;
    }

    /* ==============================
       RESPONSIVE — LARGE DESKTOP
    ============================== */

    @media (min-width: 1400px) {
        #mediaLibraryRoot .media-card-grid {
            grid-template-columns: repeat(auto-fill, minmax(135px, 1fr));
            gap: 13px;
        }
    }

    /* ==============================
       RESPONSIVE — TABLET LANDSCAPE
    ============================== */

    @media (max-width: 1199.98px) {
        #mediaLibraryRoot .media-card-grid {
            grid-template-columns: repeat(auto-fill, minmax(115px, 1fr));
            gap: 10px;
        }

        #mediaLibraryRoot .attachment-details-sidebar {
            padding: 14px;
        }
    }

    /* ==============================
       RESPONSIVE — TABLET PORTRAIT
    ============================== */

    @media (max-width: 991.98px) {
        #mediaLibraryRoot .attachment-details-sidebar {
            position: static;
            top: auto;
        }

        #mediaLibraryRoot .media-card-grid {
            grid-template-columns: repeat(auto-fill, minmax(115px, 1fr));
            max-height: 65vh;
        }

        #mediaLibraryRoot .media-page-header {
            align-items: flex-start !important;
        }
    }

    /* ==============================
       RESPONSIVE — MOBILE
    ============================== */

    @media (max-width: 767.98px) {
        #mediaLibraryRoot {
            font-size: 12px;
        }

        #mediaLibraryRoot .media-page-header {
            margin-bottom: 16px;
        }

        #mediaLibraryRoot .media-page-title {
            font-size: 17px;
        }

        #mediaLibraryRoot .media-page-description {
            font-size: 11.5px;
        }

        #mediaLibraryRoot .header-top-actions {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
        }

        #mediaLibraryRoot .header-top-actions .btn {
            flex: 1 1 auto;
            min-height: 36px;
            padding: 7px 9px;
            font-size: 11px;
        }

        #mediaLibraryRoot .media-section-tabs {
            display: grid;
            grid-template-columns: 1fr;
            gap: 7px;
        }

        #mediaLibraryRoot .media-section-tab {
            width: 100%;
            justify-content: flex-start;
            min-height: 40px;
            padding: 9px 11px;
            font-size: 11.5px;
        }

        #mediaLibraryRoot .media-filter-panel {
            padding: 12px;
        }

        #mediaLibraryRoot .media-card-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
            max-height: 58vh;
        }

        #mediaLibraryRoot .media-card-item {
            border-radius: 5px;
        }

        #mediaLibraryRoot .media-card-source-badge {
            padding: 4px 5px;
            font-size: 9px;
        }

        #mediaLibraryRoot .attachment-details-sidebar {
            padding: 13px;
        }

        #mediaLibraryRoot .wp-upload-collapsible {
            padding: 22px 13px;
        }

        #mediaLibraryRoot #wp-collapsible-upload-box > .d-flex {
            flex-wrap: wrap;
        }

        #mediaLibraryRoot #wp-collapsible-upload-box > .d-flex > * {
            max-width: 100%;
        }

        #mediaLibraryRoot .bulk-toolbar-strip {
            padding: 10px;
        }

        #mediaLibraryRoot .bulk-toolbar-strip .btn {
            width: 100%;
        }

        #mediaLibraryRoot .media-panel-card {
            padding: 12px !important;
        }

        #mediaLibraryRoot .media-pagination-wrap nav {
            flex-direction: column !important;
        }
    }

    /* ==============================
       RESPONSIVE — SMALL MOBILE
    ============================== */

    @media (max-width: 420px) {
        #mediaLibraryRoot .container-fluid {
            padding-left: 9px !important;
            padding-right: 9px !important;
        }

        #mediaLibraryRoot .media-card-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 6px;
        }

        #mediaLibraryRoot .media-section-tab {
            gap: 6px;
            font-size: 10.5px;
        }

        #mediaLibraryRoot .media-section-tab .badge {
            margin-left: auto;
        }

        #mediaLibraryRoot .header-top-actions {
            gap: 6px;
        }

        #mediaLibraryRoot .header-top-actions .btn {
            font-size: 10.5px;
            padding: 7px 6px;
        }

        #mediaLibraryRoot .media-filter-panel {
            padding: 10px;
        }

        #mediaLibraryRoot .media-panel-card {
            padding: 10px !important;
        }

        #mediaLibraryRoot .sidebar-meta-list {
            font-size: 10.5px;
        }

        #replaceMediaModal .modal-dialog,
        #usageTrackingModal .modal-dialog,
        #mediaNoticeModal .modal-dialog,
        #mediaConfirmModal .modal-dialog {
            margin: 10px;
        }
    }

    /* ==============================
       MODAL VIEWPORT SAFETY
    ============================== */

    @media (max-width: 575.98px) {
        #replaceMediaModal .modal-dialog,
        #usageTrackingModal .modal-dialog,
        #mediaNoticeModal .modal-dialog,
        #mediaConfirmModal .modal-dialog {
            max-width: calc(100% - 20px);
            margin: 10px auto;
        }

        #replaceMediaModal .modal-content,
        #usageTrackingModal .modal-content,
        #mediaNoticeModal .modal-content,
        #mediaConfirmModal .modal-content {
            max-height: calc(100dvh - 30px);
            overflow-y: auto;
        }

        #mediaLibraryRoot .media-pagination-wrap .pagination {
            gap: 3px;
        }

        #mediaLibraryRoot .media-pagination-wrap .page-link {
            padding: 5px 8px;
            font-size: 11px;
        }
    }

    /* ==============================
       REDUCED MOTION
    ============================== */

    @media (prefers-reduced-motion: reduce) {
        #mediaLibraryRoot *,
        #replaceMediaModal *,
        #usageTrackingModal *,
        #mediaNoticeModal *,
        #mediaConfirmModal * {
            scroll-behavior: auto !important;
            transition-duration: .01ms !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="mediaLibraryRoot">

    <!-- Page Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-3 media-page-header">
        <div>
            <h5 class="fw-bold mb-1 text-dark d-flex align-items-center media-page-title">
                <i class="fas fa-photo-video text-primary me-2"></i>
                <span>Media & File Library</span>
            </h5>
            <small class="d-block media-page-description">
                Manage store images, variants, banners & uploads.
            </small>
        </div>

        <div class="header-top-actions">
            <a href="{{ route('admin.media.index', ['category' => $currentCategory, 'resync' => 1]) }}"
               class="btn btn-outline-primary btn-compact bg-white shadow-xs"
               title="Scan & Sync All Site Folders">
                <i class="fas fa-arrows-rotate me-1"></i>
                <span>Sync All Files</span>
            </a>

            <button type="button" class="btn btn-primary btn-compact shadow-xs" onclick="toggleUploadBox()">
                <i class="fas fa-plus me-1"></i>
                <span>Add New Media</span>
            </button>

            <button type="button" id="btn-bulk-toggle" class="btn btn-outline-secondary btn-compact bg-white shadow-xs" onclick="toggleBulkMode()">
                <span>Bulk Select</span>
            </button>
        </div>
    </div>

    <!-- Media Category Navigation -->
    <div class="media-section-tabs">

        <a href="{{ route('admin.media.index', ['category' => 'site_content']) }}"
           class="media-section-tab {{ $currentCategory === 'site_content' ? 'active-site' : '' }}">
            <i class="fas fa-images text-info"></i>
            <span>Site Content Media</span>
            <span class="badge bg-primary text-white px-2 py-0.5">{{ $counts['site_content'] ?? 0 }}</span>
        </a>

        <a href="{{ route('admin.media.index', ['category' => 'user_submitted']) }}"
           class="media-section-tab {{ $currentCategory === 'user_submitted' ? 'active-user' : '' }}">
            <i class="fas fa-file-shield text-warning"></i>
            <span>User Submitted Files </span>
            <span class="badge bg-warning text-dark px-2 py-0.5">{{ $counts['user_submitted'] ?? 0 }}</span>
        </a>

        <a href="{{ route('admin.media.index', ['category' => 'all']) }}"
           class="media-section-tab {{ $currentCategory === 'all' ? 'active-site' : '' }}">
            <i class="fas fa-layer-group"></i>
            <span>All Site Files</span>
            <span class="badge bg-secondary text-white px-2 py-0.5">{{ $counts['all'] ?? 0 }}</span>
        </a>

    </div>

    <!-- Upload Dropzone -->
    <div id="wp-collapsible-upload-box" class="wp-upload-collapsible shadow-xs" style="display: none;">

        <div class="upload-icon-circle">
            <i class="fas fa-cloud-upload-alt"></i>
        </div>

        <h6 class="fw-bold text-dark mb-1 upload-heading">Drop Files to Upload</h6>

        <p class="text-muted small mb-3 upload-description">
            Duplicate filenames are automatically renamed as <strong>(1), (2)</strong>. No file is ever rejected!
        </p>

        <div class="d-flex justify-content-center align-items-center gap-2 mb-2">
            <select id="upload-target-category" class="form-select form-select-sm" style="max-width: 220px;">
                <option value="site_content" {{ $currentCategory !== 'user_submitted' ? 'selected' : '' }}>Upload to: Site Content Media</option>
                <option value="user_submitted" {{ $currentCategory === 'user_submitted' ? 'selected' : '' }}>Upload to: User Submitted Files</option>
            </select>

            <label class="btn btn-primary btn-compact px-4 py-2 shadow-xs mb-0" style="cursor: pointer;">
                <i class="fas fa-folder-open me-1"></i>
                <span>Select Files</span>
                <input type="file" multiple class="d-none" accept="image/*,.pdf" onchange="uploadDashboardMedia(this.files)">
            </label>
        </div>

        <div class="text-muted bn-digit" style="font-size: 11px;">
            Maximum upload file size: 20 MB (JPG, PNG, WEBP, GIF, SVG, PDF).
        </div>

        <div id="upload-progress-indicator" class="d-none mt-2 text-primary small fw-semibold">
            <i class="fas fa-spinner fa-spin me-1"></i>
            Uploading media files, please wait...
        </div>

    </div>

    <!-- Filters & Search -->
    <div class="media-panel-card media-filter-panel">

        <form method="GET" action="{{ route('admin.media.index') }}" class="row g-2 align-items-center">

            <input type="hidden" name="category" value="{{ $currentCategory }}">

            <div class="col-6 col-md-2">
                <select name="type" class="form-select form-select-sm fw-semibold" onchange="this.form.submit()">
                    <option value="all" {{ request('type') == 'all' ? 'selected' : '' }}>All File Types</option>
                    <option value="image" {{ request('type') == 'image' ? 'selected' : '' }}>Images Only</option>
                    <option value="pdf" {{ request('type') == 'pdf' ? 'selected' : '' }}>PDF Documents</option>
                </select>
            </div>

            <div class="col-6 col-md-3">
                <select name="source_module" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="all">All Modules / Folders</option>

                    @if($currentCategory === 'user_submitted')
                        <option value="payment_proof" {{ request('source_module') == 'payment_proof' ? 'selected' : '' }}>Payment Proofs (Checkout / Manual)</option>
                        <option value="form_submission" {{ request('source_module') == 'form_submission' ? 'selected' : '' }}>Form Submissions</option>
                        <option value="support_ticket" {{ request('source_module') == 'support_ticket' ? 'selected' : '' }}>Support Ticket Attachments</option>
                        <option value="user_profile" {{ request('source_module') == 'user_profile' ? 'selected' : '' }}>User / Staff Profiles</option>
                    @else
                        <option value="media_library" {{ request('source_module') == 'media_library' ? 'selected' : '' }}>Media Library Uploads</option>
                        <option value="product" {{ request('source_module') == 'product' ? 'selected' : '' }}>Product Main / Thumbnails</option>
                        <option value="product_variant" {{ request('source_module') == 'product_variant' ? 'selected' : '' }}>Product Variant Images</option>
                        <option value="product_gallery" {{ request('source_module') == 'product_gallery' ? 'selected' : '' }}>Product Gallery Images</option>
                        <option value="category" {{ request('source_module') == 'category' ? 'selected' : '' }}>Categories</option>
                        <option value="brand" {{ request('source_module') == 'brand' ? 'selected' : '' }}>Brands</option>
                        <option value="slider" {{ request('source_module') == 'slider' ? 'selected' : '' }}>Hero Sliders</option>
                        <option value="theme_offer" {{ request('source_module') == 'theme_offer' ? 'selected' : '' }}>Theme / Offer Banners</option>
                        <option value="blog" {{ request('source_module') == 'blog' ? 'selected' : '' }}>Blog Posts</option>
                        <option value="page" {{ request('source_module') == 'page' ? 'selected' : '' }}>Pages</option>
                    @endif

                </select>
            </div>

            <div class="col-6 col-md-2">
                <select name="date" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="all">All Dates</option>

                    @foreach($dates as $d)
                        <option value="{{ $d->date_val }}" {{ request('date') == $d->date_val ? 'selected' : '' }}>{{ $d->date_label }}</option>
                    @endforeach

                </select>
            </div>

            <div class="col-12 col-md-5 ms-auto">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search by file name, title, order ref, or path...">

                    <button class="btn btn-primary px-3" type="submit" title="Search">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>

        </form>

        <!-- Bulk Action Bar -->
        <div id="bulk-action-bar" class="d-none mt-3 bulk-toolbar-strip d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">

            <span class="small fw-semibold text-danger">
                <span id="bulk-selected-count" class="bn-digit">0</span>
                media item(s) selected for bulk action
            </span>

            <button type="button" class="btn btn-danger btn-compact shadow-xs" onclick="executeBulkDelete()">
                <i class="fas fa-trash-alt me-1"></i>
                <span>Delete Selected</span>
            </button>

        </div>

    </div>

    <!-- Main Content: Media Grid & Inspector -->
    <div class="row g-3">

        <!-- Media Grid -->
        <div class="col-12 col-lg-8 col-xl-9">

            <div class="media-panel-card p-3">

                <div class="media-card-grid" id="main-media-grid">

                    @forelse($files as $file)

                        <div class="media-card-item" data-id="{{ $file->id }}" onclick="openDashboardDetails({{ json_encode($file) }}, this)">

                            <input type="checkbox" class="bulk-checkbox form-check-input mt-0" value="{{ $file->id }}" onclick="event.stopPropagation(); updateBulkCount();">

                            @if(str_starts_with($file->file_type ?? '', 'image/'))

                                <img src="{{ $file->url }}" alt="{{ $file->alt_text }}" loading="lazy" onerror="this.src='https://placehold.co/300x300?text=Missing'">

                            @else

                                <div class="h-100 d-flex flex-column align-items-center justify-content-center p-2 text-center bg-light">
                                    <i class="fas fa-file-pdf fa-2x text-danger mb-1"></i>
                                    <small class="text-truncate w-100 fw-semibold text-dark px-1" style="font-size: 10px;">{{ $file->file_name }}</small>
                                </div>

                            @endif

                            <div class="media-card-source-badge">
                                {{ $file->title ?: $file->file_name }}
                            </div>

                        </div>

                    @empty

                        <div class="col-12 text-center text-muted py-5" style="grid-column: 1 / -1;">
                            <i class="far fa-images fa-2x mb-2 d-block opacity-25"></i>
                            <span class="small fw-semibold">No media files found in this section.</span>
                        </div>

                    @endforelse

                </div>

                @if($files->hasPages())

                    <div class="mt-3 pt-3 border-top media-pagination-wrap">
                        {{ $files->links('pagination::bootstrap-5') }}
                    </div>

                @endif

            </div>

        </div>

        <!-- Attachment Details Inspector -->
        <div class="col-12 col-lg-4 col-xl-3" id="inspector-column-anchor">

            <div class="attachment-details-sidebar" id="dashboard-sidebar-panel" style="display: none;">

                <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom">

                    <h6 class="fw-bold mb-0 text-uppercase text-dark">
                        Attachment Details
                    </h6>

                    <button type="button" class="btn-close btn-sm d-lg-none shadow-none" onclick="closeSidebarPanel()" aria-label="Close"></button>

                </div>

                <!-- Preview -->
                <div class="sidebar-preview-stage mb-3">
                    <img id="sidebar-preview-img" src="" class="img-fluid rounded" style="max-height: 165px; object-fit: contain;">
                </div>

                <!-- File Information -->
                <div class="sidebar-meta-list text-muted lh-base mb-3">

                    <div class="text-truncate">
                        <strong class="text-dark" id="sidebar-meta-name"></strong>
                    </div>

                    <div id="sidebar-meta-module" class="text-primary fw-semibold"></div>
                    <div id="sidebar-meta-date" class="bn-digit"></div>
                    <div id="sidebar-meta-size" class="bn-digit"></div>
                    <div id="sidebar-meta-dims" class="bn-digit"></div>

                </div>

                <!-- File Actions -->
                <div class="row g-1 mb-3">

                    <div class="col-6">
                        <a href="#" id="sidebar-download-btn" class="btn btn-success btn-compact w-100 shadow-xs text-decoration-none">
                            <i class="fas fa-download me-1"></i>
                            <span>Download</span>
                        </a>
                    </div>

                    <div class="col-6">
                        <a href="#" target="_blank" id="sidebar-open-btn" class="btn btn-outline-dark btn-compact w-100 shadow-xs text-decoration-none">
                            <i class="fas fa-external-link-alt me-1"></i>
                            <span>View Full</span>
                        </a>
                    </div>

                    <div class="col-6 mt-1">
                        <button type="button" class="btn btn-outline-primary btn-compact w-100 shadow-xs" onclick="openReplaceModal()">
                            <i class="fas fa-sync-alt me-1"></i>
                            <span>Replace</span>
                        </button>
                    </div>

                    <div class="col-6 mt-1">
                        <button type="button" class="btn btn-outline-info btn-compact w-100 shadow-xs" onclick="checkMediaUsage()">
                            <i class="fas fa-link me-1"></i>
                            <span>Usage</span>
                        </button>
                    </div>

                </div>

                <!-- Metadata Form -->
                <form id="sidebar-metadata-form" onsubmit="saveDashboardMeta(event)">

                    <input type="hidden" id="sidebar-media-id">

                    <div class="mb-3">
                        <label class="form-label">Section / Category</label>
                        <select id="sidebar-category" class="form-select form-select-sm">
                            <option value="site_content">Site Content Media</option>
                            <option value="user_submitted">User Submitted File</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" id="sidebar-title" class="form-control form-control-sm" placeholder="Media title">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alternative Text (Alt)</label>
                        <textarea id="sidebar-alt" class="form-control form-control-sm" rows="2" placeholder="Describe image for SEO..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Caption</label>
                        <textarea id="sidebar-caption" class="form-control form-control-sm" rows="2" placeholder="Optional caption..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">File URL</label>

                        <div class="input-group input-group-sm">
                            <input type="text" id="sidebar-url" class="form-control font-monospace bg-light" style="font-size: 11px;" readonly>

                            <button type="button" class="btn btn-outline-secondary btn-compact" onclick="copySidebarUrl()">
                                Copy
                            </button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top gap-2">

                        <button type="submit" class="btn btn-primary btn-compact shadow-xs" id="btn-save-meta">
                            <span>Save Metadata</span>
                        </button>

                        <button type="button" class="btn btn-outline-danger btn-compact" onclick="deleteDashboardMedia()">
                            <span>Delete</span>
                        </button>

                    </div>

                </form>

            </div>

            <!-- Empty Inspector Placeholder -->
            <div class="media-panel-card p-4 text-center text-muted" id="sidebar-placeholder">

                <i class="far fa-image fa-2x mb-2 d-block opacity-25"></i>

                <p class="small mb-0">
                    Select a file to view, download, replace, or edit its metadata.
                </p>

            </div>

        </div>

    </div>

</div>

<!-- Modal 1: Replace Media -->
<div class="modal fade" id="replaceMediaModal" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered modal-sm">

        <div class="modal-content border-0 shadow-lg p-3">

            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">

                <h6 class="fw-bold mb-0 text-dark d-flex align-items-center" style="font-size: 13.5px;">
                    <i class="fas fa-sync-alt text-primary me-2"></i>
                    <span>Replace Media File</span>
                </h6>

                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>

            </div>

            <p class="small text-muted mb-3" style="font-size: 11.5px; line-height: 1.55;">
                Upload a new file to replace this item. All existing products and variants will display the new image immediately.
            </p>

            <input type="file" id="replace-file-input" class="form-control form-control-sm mb-3" accept="image/*,.pdf">

            <div class="d-flex justify-content-end gap-2">

                <button type="button" class="btn btn-light border btn-compact" data-bs-dismiss="modal">
                    Cancel
                </button>

                <button type="button" class="btn btn-primary btn-compact shadow-xs" id="btn-exec-replace" onclick="executeFileReplace()">
                    Replace
                </button>

            </div>

        </div>

    </div>

</div>

<!-- Modal 2: Usage Tracking -->
<div class="modal fade" id="usageTrackingModal" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 shadow-lg p-3">

            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">

                <h6 class="fw-bold mb-0 text-dark d-flex align-items-center" style="font-size: 14px;">
                    <i class="fas fa-link text-info me-2"></i>
                    <span>Media Usage Locations</span>
                </h6>

                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>

            </div>

            <div id="usage-list-container" class="my-2" style="max-height: 260px; overflow-y: auto;"></div>

            <div class="text-end pt-3 border-top">

                <button type="button" class="btn btn-dark btn-compact px-4" data-bs-dismiss="modal">
                    Close
                </button>

            </div>

        </div>

    </div>

</div>

<!-- Modal 3: Notice / Alert Dialog -->
<div class="modal fade" id="mediaNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">

    <div class="modal-dialog modal-dialog-centered modal-sm">

        <div class="modal-content border-0 shadow-lg text-center p-3">

            <div class="modal-body p-1">

                <div class="mb-2 text-success" id="mediaNoticeIcon">
                    <i class="fas fa-check-circle fa-2x"></i>
                </div>

                <h6 class="fw-bold text-dark mb-1" id="mediaNoticeTitle" style="font-size: 14px;">
                    Notice
                </h6>

                <p class="text-muted small mb-3" id="mediaNoticeMsg" style="font-size: 12px;"></p>

                <button type="button" class="btn btn-dark btn-compact px-4" data-bs-dismiss="modal">
                    OK
                </button>

            </div>

        </div>

    </div>

</div>

<!-- Modal 4: Confirmation Dialog -->
<div class="modal fade" id="mediaConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">

    <div class="modal-dialog modal-dialog-centered modal-sm">

        <div class="modal-content border-0 shadow-lg text-center p-3">

            <div class="modal-body p-1">

                <div class="mb-2 text-danger">
                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                </div>

                <h6 class="fw-bold text-dark mb-1" id="mediaConfirmTitle" style="font-size: 14px;">
                    Confirm Delete
                </h6>

                <p class="text-muted small mb-3" id="mediaConfirmMsg" style="font-size: 12px; line-height: 1.45;"></p>

                <div class="d-flex justify-content-center gap-2">

                    <button type="button" class="btn btn-light border btn-compact px-3" data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="button" class="btn btn-danger btn-compact px-3 shadow-xs" id="btnMediaConfirmProceed">
                        Delete
                    </button>

                </div>

            </div>

        </div>

    </div>

</div>

<script>
let currentDashboardMedia = null;
let isBulkMode = false;
let pendingMediaConfirmCallback = null;

function showMediaNotice(message, title = 'Success', isError = false, onHiddenCallback = null) {
    document.getElementById('mediaNoticeTitle').innerText = title;
    document.getElementById('mediaNoticeMsg').innerText = message;
    const iconWrap = document.getElementById('mediaNoticeIcon');
    iconWrap.className = isError ? 'mb-2 text-danger' : 'mb-2 text-success';
    iconWrap.innerHTML = isError ? '<i class="fas fa-exclamation-circle fa-2x"></i>' : '<i class="fas fa-check-circle fa-2x"></i>';

    const modalEl = document.getElementById('mediaNoticeModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    if (onHiddenCallback) {
        modalEl.addEventListener('hidden.bs.modal', function handler() {
            modalEl.removeEventListener('hidden.bs.modal', handler);
            onHiddenCallback();
        });
    }
    modal.show();
}

function showMediaConfirm(title, message, onConfirm) {
    document.getElementById('mediaConfirmTitle').innerText = title;
    document.getElementById('mediaConfirmMsg').innerText = message;
    pendingMediaConfirmCallback = onConfirm;

    const modalEl = document.getElementById('mediaConfirmModal');
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('btnMediaConfirmProceed').addEventListener('click', function() {
        const modalEl = document.getElementById('mediaConfirmModal');
        const instance = bootstrap.Modal.getInstance(modalEl);
        if (instance) instance.hide();

        if (typeof pendingMediaConfirmCallback === 'function') {
            const cb = pendingMediaConfirmCallback;
            pendingMediaConfirmCallback = null;
            cb();
        }
    });

    const dropBox = document.getElementById('wp-collapsible-upload-box');
    if (dropBox) {
        ['dragenter', 'dragover'].forEach(evt => {
            dropBox.addEventListener(evt, function(e) {
                e.preventDefault();
                e.stopPropagation();
                dropBox.classList.add('dragover');
            });
        });
        ['dragleave', 'drop'].forEach(evt => {
            dropBox.addEventListener(evt, function(e) {
                e.preventDefault();
                e.stopPropagation();
                dropBox.classList.remove('dragover');
            });
        });
        dropBox.addEventListener('drop', function(e) {
            if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                uploadDashboardMedia(e.dataTransfer.files);
            }
        });
    }
});

function toggleUploadBox() {
    const box = document.getElementById('wp-collapsible-upload-box');
    box.style.display = box.style.display === 'none' ? 'block' : 'none';
}

function closeSidebarPanel() {
    document.getElementById('dashboard-sidebar-panel').style.display = 'none';
    document.getElementById('sidebar-placeholder').style.display = 'block';
    document.querySelectorAll('.media-card-item').forEach(el => el.classList.remove('selected'));
}

function openDashboardDetails(media, element) {
    if (isBulkMode) {
        const cb = element.querySelector('.bulk-checkbox');
        if (cb) {
            cb.checked = !cb.checked;
            element.classList.toggle('selected', cb.checked);
            updateBulkCount();
        }
        return;
    }

    document.querySelectorAll('.media-card-item').forEach(el => el.classList.remove('selected'));
    element.classList.add('selected');

    currentDashboardMedia = media;
    document.getElementById('sidebar-placeholder').style.display = 'none';
    const panel = document.getElementById('dashboard-sidebar-panel');
    panel.style.display = 'block';

    document.getElementById('sidebar-media-id').value = media.id;
    document.getElementById('sidebar-preview-img').src = media.url;
    document.getElementById('sidebar-meta-name').innerText = media.file_name;
    document.getElementById('sidebar-meta-module').innerText = 'Module: ' + ((media.source_module || 'media_library').replace(/_/g, ' ').toUpperCase());
    document.getElementById('sidebar-meta-date').innerText = 'Uploaded: ' + (media.formatted_date || '');
    document.getElementById('sidebar-meta-size').innerText = 'File size: ' + (media.human_size || '');
    document.getElementById('sidebar-meta-dims').innerText = 'Dimensions: ' + (media.dimensions || 'N/A');

    document.getElementById('sidebar-download-btn').href = media.download_url || (`{{ url('admin/media') }}/${media.id}/download`);
    document.getElementById('sidebar-open-btn').href = media.url;

    document.getElementById('sidebar-category').value = media.media_category || 'site_content';
    document.getElementById('sidebar-alt').value = media.alt_text || '';
    document.getElementById('sidebar-title').value = media.title || '';
    document.getElementById('sidebar-caption').value = media.caption || '';
    document.getElementById('sidebar-url').value = media.url;

    if (window.innerWidth < 992) {
        document.getElementById('inspector-column-anchor').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

function copySidebarUrl() {
    const url = document.getElementById('sidebar-url');
    url.select();
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(url.value);
    } else {
        document.execCommand('copy');
    }
    showMediaNotice('File URL copied to clipboard!', 'Copied');
}

function saveDashboardMeta(e) {
    e.preventDefault();
    const id = document.getElementById('sidebar-media-id').value;
    const btn = document.getElementById('btn-save-meta');
    if (btn) btn.disabled = true;

    fetch(`{{ url('admin/media') }}/${id}/update`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            media_category: document.getElementById('sidebar-category').value,
            alt_text: document.getElementById('sidebar-alt').value,
            title: document.getElementById('sidebar-title').value,
            caption: document.getElementById('sidebar-caption').value
        })
    })
    .then(res => res.json())
    .then(data => {
        showMediaNotice(data.message || 'Metadata saved successfully.');
    })
    .finally(() => {
        if (btn) btn.disabled = false;
    });
}

function uploadDashboardMedia(files) {
    if (!files.length) return;
    const prog = document.getElementById('upload-progress-indicator');
    if (prog) prog.classList.remove('d-none');

    const targetCategory = document.getElementById('upload-target-category')?.value || '{{ $currentCategory }}';
    const formData = new FormData();
    for (let i = 0; i < files.length; i++) {
        formData.append('files[]', files[i]);
    }
    formData.append('media_category', targetCategory);
    formData.append('_token', '{{ csrf_token() }}');

    fetch("{{ route('admin.media.upload') }}", {
        method: 'POST',
        body: formData
    }).then(res => res.json()).then(data => {
        if (data.status === 'success') {
            location.reload();
        } else {
            if (prog) prog.classList.add('d-none');
            showMediaNotice('Failed to upload selected files.', 'Upload Error', true);
        }
    }).catch(() => {
        if (prog) prog.classList.add('d-none');
        showMediaNotice('Error occurred while uploading files.', 'Upload Error', true);
    });
}

function openReplaceModal() {
    bootstrap.Modal.getOrCreateInstance(document.getElementById('replaceMediaModal')).show();
}

function executeFileReplace() {
    const file = document.getElementById('replace-file-input').files[0];
    if (!file || !currentDashboardMedia) return;

    const btn = document.getElementById('btn-exec-replace');
    if (btn) btn.disabled = true;

    const formData = new FormData();
    formData.append('replacement_file', file);
    formData.append('_token', '{{ csrf_token() }}');

    fetch(`{{ url('admin/media') }}/${currentDashboardMedia.id}/replace`, {
        method: 'POST',
        body: formData
    }).then(res => res.json()).then(data => {
        const repModal = bootstrap.Modal.getInstance(document.getElementById('replaceMediaModal'));
        if (repModal) repModal.hide();
        showMediaNotice(data.message || 'File replaced successfully.', 'Replaced', false, () => {
            location.reload();
        });
    }).finally(() => {
        if (btn) btn.disabled = false;
    });
}

function checkMediaUsage() {
    if (!currentDashboardMedia) return;
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('usageTrackingModal'));
    const container = document.getElementById('usage-list-container');
    container.innerHTML = '<div class="text-center py-3 text-muted small"><i class="fas fa-spinner fa-spin me-1"></i> Checking usages...</div>';
    modal.show();

    fetch(`{{ url('admin/media') }}/${currentDashboardMedia.id}/usage`)
    .then(res => res.json())
    .then(data => {
        if (data.usages && data.usages.length > 0) {
            let html = '<ul class="list-group list-group-flush small">';
            data.usages.forEach(u => {
                html += `<li class="list-group-item d-flex justify-content-between align-items-center px-1 py-2">
                    <div class="text-truncate pe-2"><span class="badge bg-secondary me-1">${u.type}</span> <span class="fw-semibold text-dark">${u.title}</span></div>
                    <a href="${u.url}" target="_blank" class="btn btn-outline-primary btn-compact py-1 px-2">View</a>
                </li>`;
            });
            html += '</ul>';
            container.innerHTML = html;
        } else {
            container.innerHTML = '<div class="text-center py-3 text-success small fw-semibold"><i class="fas fa-check-circle me-1"></i> This media is not actively linked or is safe to delete.</div>';
        }
    });
}

function deleteDashboardMedia() {
    if (!currentDashboardMedia) return;

    showMediaConfirm(
        'Delete Media File?',
        'Are you sure you want to permanently delete this media file?',
        () => {
            fetch(`{{ url('admin/media') }}/${currentDashboardMedia.id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            })
            .then(async res => {
                const data = await res.json();
                if (data.status === 'in_use') {
                    setTimeout(() => {
                        showMediaConfirm(
                            'File Currently In Use!',
                            data.message + ' Do you still want to force delete it anyway?',
                            () => {
                                fetch(`{{ url('admin/media') }}/${currentDashboardMedia.id}?force_delete=1`, {
                                    method: 'DELETE',
                                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                                }).then(() => location.reload());
                            }
                        );
                    }, 250);
                } else {
                    location.reload();
                }
            });
        }
    );
}

// Bulk Mode
function toggleBulkMode() {
    isBulkMode = !isBulkMode;
    const grid = document.getElementById('main-media-grid');
    const bar = document.getElementById('bulk-action-bar');
    const btn = document.getElementById('btn-bulk-toggle');

    if (isBulkMode) {
        grid.classList.add('bulk-select-active');
        bar.classList.remove('d-none');
        btn.classList.replace('btn-outline-secondary', 'btn-secondary');
        btn.innerText = 'Cancel Selection';
        document.querySelectorAll('.media-card-item').forEach(el => el.classList.remove('selected'));
    } else {
        grid.classList.remove('bulk-select-active');
        bar.classList.add('d-none');
        btn.classList.replace('btn-secondary', 'btn-outline-secondary');
        btn.innerText = 'Bulk Select';
        document.querySelectorAll('.bulk-checkbox').forEach(cb => cb.checked = false);
        document.querySelectorAll('.media-card-item').forEach(el => el.classList.remove('selected'));
        updateBulkCount();
    }
}

function updateBulkCount() {
    const checkboxes = document.querySelectorAll('.bulk-checkbox');
    checkboxes.forEach(cb => {
        const card = cb.closest('.media-card-item');
        if (card) card.classList.toggle('selected', cb.checked);
    });
    const checked = document.querySelectorAll('.bulk-checkbox:checked').length;
    document.getElementById('bulk-selected-count').innerText = checked;
}

function executeBulkDelete() {
    const checked = Array.from(document.querySelectorAll('.bulk-checkbox:checked')).map(cb => cb.value);
    if (!checked.length) return;

    showMediaConfirm(
        'Delete Selected Files?',
        `Are you sure you want to permanently delete ${checked.length} selected media file(s)?`,
        () => {
            fetch("{{ route('admin.media.bulk.destroy') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({ ids: checked })
            }).then(res => res.json()).then(() => location.reload());
        }
    );
}
</script>
@endsection