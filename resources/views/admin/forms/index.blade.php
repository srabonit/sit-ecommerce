
@extends('layouts.admin')

@section('title', 'Form Builder - ' . config('app.name'))

@php
    // Safe thumbnail and media resolver supporting Global Media Library
    $resolveFormMedia = function($path, $fallback = 'https://placehold.co/60x60?text=Form') {
        if (empty($path)) return $fallback;
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path, $fallback);
        }
        if (function_exists('show_image')) return show_image($path, $fallback);
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        return asset(ltrim(str_replace(['public_html/', 'public/'], '', $path), '/'));
    };

    $totalFormsCount = method_exists($forms, 'total') ? $forms->total() : $forms->count();
    $activeFormsCount = $forms->where('is_active', true)->count();
    $totalSubmissionsCount = $forms->sum('submissions_count');
    $totalFieldsCount = $forms->sum('fields_count');
@endphp

@section('admin_content')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

<style>
    /* =====================================================
       KAIADMIN CLASSIC - FORM BUILDER
       Scoped Design System
    ===================================================== */

    #formBuilderIndexRoot,
    #formBuilderIndexRoot *,
    .fb-modal-scope,
    .fb-modal-scope * {
        box-sizing: border-box;
    }

    #formBuilderIndexRoot {
        --fb-primary: var(--bs-primary, #1572e8);
        --fb-primary-hover: #1264cc;
        --fb-success: #31ce77;
        --fb-danger: #f25961;
        --fb-warning: #ffad46;
        --fb-info: #48abf7;

        --fb-body: #f8f9fa;
        --fb-card: #ffffff;
        --fb-heading: #1a2035;
        --fb-text: #575962;
        --fb-muted: #8d9498;
        --fb-border: #ebedf2;
        --fb-soft: #f7f8fa;

        font-family: 'Inter', 'Plus Jakarta Sans', sans-serif;
        color: var(--fb-text);
        font-size: 13px;
        letter-spacing: normal;
        width: 100%;
        min-width: 0;
    }

    #formBuilderIndexRoot i,
    #formBuilderIndexRoot .fa,
    #formBuilderIndexRoot .fas,
    #formBuilderIndexRoot .far,
    #formBuilderIndexRoot .fab,
    #formBuilderIndexRoot .fa-solid,
    #formBuilderIndexRoot .fa-regular,
    .fb-modal-scope i,
    .fb-modal-scope .fa-solid {
        font-family: "Font Awesome 6 Free", "Font Awesome 5 Free", "FontAwesome" !important;
        font-style: normal;
        line-height: 1;
        letter-spacing: normal;
        display: inline-block;
    }

    #formBuilderIndexRoot .far,
    #formBuilderIndexRoot .fa-regular {
        font-weight: 400 !important;
    }

    #formBuilderIndexRoot .fa,
    #formBuilderIndexRoot .fas,
    #formBuilderIndexRoot .fa-solid,
    .fb-modal-scope .fa-solid {
        font-weight: 900;
    }

    #formBuilderIndexRoot .btn,
    #formBuilderIndexRoot button,
    #formBuilderIndexRoot .form-control,
    #formBuilderIndexRoot .form-select,
    #formBuilderIndexRoot .badge,
    #formBuilderIndexRoot .input-group-text,
    #formBuilderIndexRoot .card,
    #formBuilderIndexRoot .page-link,
    #formBuilderIndexRoot .shortcode-box,
    #formBuilderIndexRoot .proof-thumb-box,
    .fb-modal-scope .modal-content,
    .fb-modal-scope .btn {
        border-radius: 5px !important;
    }

    #formBuilderIndexRoot .bn-digit,
    #formBuilderIndexRoot .font-tabular {
        font-variant-numeric: tabular-nums;
        font-feature-settings: "tnum";
    }

    #formBuilderIndexRoot .container-fluid {
        max-width: 100%;
    }

    /* PAGE HEADER */

    #formBuilderIndexRoot .fb-header-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 16px 18px;
        margin-bottom: 18px;
        background: var(--fb-card);
        border: 1px solid var(--fb-border);
        border-radius: 5px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .035);
    }

    #formBuilderIndexRoot .fb-header-card h5 {
        color: var(--fb-heading) !important;
        font-size: 17px !important;
        font-weight: 700;
        line-height: 1.4;
    }

    #formBuilderIndexRoot .fb-header-card small {
        color: var(--fb-muted) !important;
        font-size: 11.5px !important;
    }

    #formBuilderIndexRoot .fb-header-card h5 i {
        font-size: 16px;
    }

    #formBuilderIndexRoot .fb-header-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    /* BUTTONS */

    #formBuilderIndexRoot .btn-compact {
        min-height: 34px;
        padding: 7px 13px !important;
        font-size: 11.5px !important;
        font-weight: 600 !important;
        line-height: 1.3 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        white-space: nowrap;
        transition: all .18s ease;
    }

    #formBuilderIndexRoot .btn-primary {
        background: var(--fb-primary) !important;
        border: 1px solid var(--fb-primary) !important;
        color: #fff !important;
    }

    #formBuilderIndexRoot .btn-primary:hover {
        background: var(--fb-primary-hover) !important;
        border-color: var(--fb-primary-hover) !important;
        color: #fff !important;
    }

    #formBuilderIndexRoot .btn-outline-secondary {
        background: #fff !important;
        border: 1px solid #e0e3e8 !important;
        color: #575962 !important;
    }

    #formBuilderIndexRoot .btn-outline-secondary:hover {
        background: var(--fb-soft) !important;
        border-color: #c9cdd4 !important;
        color: var(--fb-heading) !important;
    }

    #formBuilderIndexRoot .btn:focus-visible,
    #formBuilderIndexRoot button:focus-visible,
    #formBuilderIndexRoot a:focus-visible {
        outline: 2px solid var(--fb-primary);
        outline-offset: 2px;
    }

    /* STATISTICS CARDS */

    #formBuilderIndexRoot .metric-card {
        position: relative;
        height: 100%;
        min-height: 92px;
        padding: 14px 15px;
        background: #fff;
        border: 1px solid var(--fb-border);
        border-radius: 5px !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .025);
        transition: box-shadow .18s ease, transform .18s ease;
    }

    #formBuilderIndexRoot .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, .055);
    }

    #formBuilderIndexRoot .stat-label-title {
        color: var(--fb-muted) !important;
        font-size: 11px;
        font-weight: 600;
        line-height: 1.4;
    }

    #formBuilderIndexRoot .stat-val-num {
        color: var(--fb-heading);
        font-size: clamp(17px, 1.5vw, 21px);
        font-weight: 700;
        line-height: 1.35;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin: 0;
    }

    #formBuilderIndexRoot .metric-icon-square {
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px !important;
        font-size: 14px;
    }

    #formBuilderIndexRoot .metric-card-blue {
        border-left: 3px solid #1572e8 !important;
    }

    #formBuilderIndexRoot .metric-card-green {
        border-left: 3px solid #31ce77 !important;
    }

    #formBuilderIndexRoot .metric-card-purple {
        border-left: 3px solid #6861ce !important;
    }

    #formBuilderIndexRoot .metric-card-amber {
        border-left: 3px solid #ffad46 !important;
    }

    /* TABLE CARD */

    #formBuilderIndexRoot .fb-table-card {
        background: #fff;
        border: 1px solid var(--fb-border);
        border-radius: 5px !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .035);
        margin-bottom: 18px;
        min-width: 0;
    }

    #formBuilderIndexRoot .pc-fit-container {
        width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
    }

    #formBuilderIndexRoot .custom-fb-table {
        width: 100%;
        min-width: 850px;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
        table-layout: fixed;
    }

    #formBuilderIndexRoot .custom-fb-table th {
        padding: 11px 10px;
        background: #f8f9fa;
        color: #6c757d;
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .035em;
        line-height: 1.4;
        white-space: nowrap;
        vertical-align: middle;
        border-top: 1px solid var(--fb-border);
        border-bottom: 1px solid var(--fb-border);
    }

    #formBuilderIndexRoot .custom-fb-table td {
        padding: 11px 10px;
        color: var(--fb-text);
        font-size: 12px;
        font-weight: 500;
        line-height: 1.45;
        vertical-align: middle;
        border-bottom: 1px solid #f0f1f3;
        overflow-wrap: anywhere;
    }

    #formBuilderIndexRoot .custom-fb-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #formBuilderIndexRoot .custom-fb-table tbody tr {
        transition: background-color .15s ease;
    }

    #formBuilderIndexRoot .custom-fb-table tbody tr:hover {
        background: #fafbfc;
    }

    #formBuilderIndexRoot .custom-fb-table tbody tr:hover td {
        background: transparent;
    }

    #formBuilderIndexRoot .custom-fb-table th:nth-child(1),
    #formBuilderIndexRoot .custom-fb-table td:nth-child(1) {
        width: 27%;
    }

    #formBuilderIndexRoot .custom-fb-table th:nth-child(2),
    #formBuilderIndexRoot .custom-fb-table td:nth-child(2) {
        width: 8%;
    }

    #formBuilderIndexRoot .custom-fb-table th:nth-child(3),
    #formBuilderIndexRoot .custom-fb-table td:nth-child(3) {
        width: 14%;
    }

    #formBuilderIndexRoot .custom-fb-table th:nth-child(4),
    #formBuilderIndexRoot .custom-fb-table td:nth-child(4) {
        width: 23%;
    }

    #formBuilderIndexRoot .custom-fb-table th:nth-child(5),
    #formBuilderIndexRoot .custom-fb-table td:nth-child(5) {
        width: 10%;
    }

    #formBuilderIndexRoot .custom-fb-table th:nth-child(6),
    #formBuilderIndexRoot .custom-fb-table td:nth-child(6) {
        width: 18%;
    }

    /* FORM IDENTITY */

    #formBuilderIndexRoot .custom-fb-table td strong {
        color: var(--fb-heading) !important;
    }

    #formBuilderIndexRoot .custom-fb-table td a.text-dark {
        color: var(--fb-heading) !important;
    }

    #formBuilderIndexRoot .custom-fb-table td a.text-dark:hover {
        color: var(--fb-primary) !important;
    }

    #formBuilderIndexRoot .proof-thumb-box {
        width: 36px;
        height: 36px;
        flex: 0 0 36px;
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #fff;
        border: 1px solid #e6e8ec;
        cursor: pointer;
    }

    #formBuilderIndexRoot .proof-thumb-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    #formBuilderIndexRoot .proof-thumb-hover-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(0, 0, 0, .45);
        opacity: 0;
        transition: opacity .15s ease;
    }

    #formBuilderIndexRoot .proof-thumb-box:hover .proof-thumb-hover-overlay {
        opacity: 1;
    }

    /* SHORTCODE */

    #formBuilderIndexRoot .shortcode-box {
        max-width: 100%;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 5px 8px;
        background: #f7f9fc;
        border: 1px solid #e7eaf0;
        color: var(--fb-primary);
        font-size: 11px;
        font-weight: 600;
        line-height: 1.4;
    }

    #formBuilderIndexRoot .shortcode-box > span {
        min-width: 0;
        overflow-wrap: anywhere;
    }

    #formBuilderIndexRoot .btn-copy-shortcode {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 24px;
        min-height: 24px;
        padding: 2px;
        border: 0;
        background: transparent;
        color: #8d9498;
        cursor: pointer;
        transition: color .15s ease;
    }

    #formBuilderIndexRoot .btn-copy-shortcode:hover {
        color: var(--fb-primary);
    }

    /* BADGES */

    #formBuilderIndexRoot .badge-soft-success {
        color: #31a66a !important;
        background: #e8f8ef !important;
        border: 1px solid #d3f1df !important;
    }

    #formBuilderIndexRoot .badge-soft-danger {
        color: #e44d55 !important;
        background: #fff0f0 !important;
        border: 1px solid #f9dada !important;
    }

    #formBuilderIndexRoot .badge-soft-primary {
        color: #1572e8 !important;
        background: #eaf2ff !important;
        border: 1px solid #d9e7ff !important;
    }

    #formBuilderIndexRoot .badge-soft-secondary {
        color: #6c757d !important;
        background: #f1f2f4 !important;
        border: 1px solid #e5e7eb !important;
    }

    #formBuilderIndexRoot .subs-count-pill {
        display: inline-flex;
        align-items: center;
        text-decoration: none;
        transition: transform .15s ease;
    }

    #formBuilderIndexRoot .subs-count-pill:hover {
        transform: translateY(-1px);
    }

    #formBuilderIndexRoot .subs-count-pill:hover .badge-soft-secondary {
        color: var(--fb-primary) !important;
        background: #eaf2ff !important;
        border-color: #d9e7ff !important;
    }

    /* ACTION BUTTONS */

    #formBuilderIndexRoot .btn-action-square {
        width: 30px !important;
        height: 30px !important;
        min-width: 30px;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 11px !important;
        background: #fff !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 5px !important;
        cursor: pointer;
        transition: all .15s ease;
    }

    #formBuilderIndexRoot .btn-action-view {
        color: #31a66a !important;
    }

    #formBuilderIndexRoot .btn-action-view:hover {
        background: #31a66a !important;
        border-color: #31a66a !important;
        color: #fff !important;
    }

    #formBuilderIndexRoot .btn-action-edit {
        color: #1572e8 !important;
    }

    #formBuilderIndexRoot .btn-action-edit:hover {
        background: #1572e8 !important;
        border-color: #1572e8 !important;
        color: #fff !important;
    }

    #formBuilderIndexRoot .btn-action-delete {
        color: #f25961 !important;
    }

    #formBuilderIndexRoot .btn-action-delete:hover {
        background: #f25961 !important;
        border-color: #f25961 !important;
        color: #fff !important;
    }

    /* PAGINATION */

    #formBuilderIndexRoot #fbPaginationLinks {
        max-width: 100%;
    }

    #formBuilderIndexRoot #fbPaginationLinks nav {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        width: auto !important;
        margin: 0 !important;
    }

    #formBuilderIndexRoot #fbPaginationLinks nav > div.d-sm-none,
    #formBuilderIndexRoot #fbPaginationLinks nav > .d-sm-none,
    #formBuilderIndexRoot #fbPaginationLinks nav p,
    #formBuilderIndexRoot #fbPaginationLinks nav .small.text-muted {
        display: none !important;
    }

    #formBuilderIndexRoot #fbPaginationLinks .pagination {
        display: flex !important;
        flex-wrap: wrap;
        align-items: center !important;
        justify-content: flex-end;
        gap: 4px !important;
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
    }

    #formBuilderIndexRoot #fbPaginationLinks .page-item .page-link,
    #formBuilderIndexRoot #fbPaginationLinks .page-item span {
        min-width: 29px !important;
        height: 29px !important;
        padding: 0 7px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        color: #575962 !important;
        background: #fff !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 5px !important;
        text-decoration: none !important;
    }

    #formBuilderIndexRoot #fbPaginationLinks .page-item .page-link:hover {
        color: var(--fb-primary) !important;
        background: #f4f7fc !important;
        border-color: #cbdcf8 !important;
    }

    #formBuilderIndexRoot #fbPaginationLinks .page-item.active .page-link,
    #formBuilderIndexRoot #fbPaginationLinks .page-item.active span {
        color: #fff !important;
        background: var(--fb-primary) !important;
        border-color: var(--fb-primary) !important;
    }

    /* FOOTER */

    #formBuilderIndexRoot .fb-table-card > .border-top {
        background: #fff !important;
        border-color: var(--fb-border) !important;
        padding: 12px 15px !important;
    }

    /* MODALS */

    .fb-modal-scope .modal-content {
        background: #fff;
        border: 1px solid var(--fb-border) !important;
        border-radius: 5px !important;
        box-shadow: 0 12px 35px rgba(0, 0, 0, .13) !important;
    }

    .fb-modal-scope .minimal-modal-card {
        padding: 24px !important;
        text-align: left;
    }

    .fb-modal-scope .minimal-modal-title {
        margin-bottom: 8px !important;
        color: #1a2035 !important;
        font-size: 16px !important;
        font-weight: 700 !important;
        line-height: 1.4;
    }

    .fb-modal-scope .minimal-modal-subtext {
        margin-bottom: 20px !important;
        color: #8d9498 !important;
        font-size: 12.5px !important;
        font-weight: 400;
        line-height: 1.6 !important;
        overflow-wrap: anywhere;
    }

    .fb-modal-scope .btn-minimal-delete {
        min-height: 34px;
        padding: 8px 19px !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        background: #f25961 !important;
        border: 1px solid #f25961 !important;
        border-radius: 5px !important;
        color: #fff !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        box-shadow: none !important;
        cursor: pointer;
        transition: background .15s ease;
    }

    .fb-modal-scope .btn-minimal-delete:hover {
        background: #e0444c !important;
        border-color: #e0444c !important;
    }

    .fb-modal-scope .btn-minimal-cancel {
        min-height: 34px;
        padding: 7px 14px !important;
        background: #f1f2f4 !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 5px !important;
        color: #575962 !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        cursor: pointer;
    }

    .fb-modal-scope .btn-minimal-cancel:hover {
        background: #e9eaed !important;
        color: #1a2035 !important;
    }

    #formMediaZoomModal .modal-dialog {
        max-width: min(600px, calc(100% - 24px));
    }

    #formMediaZoomModal .modal-content {
        border-radius: 5px !important;
    }

    #formMediaZoomImgTag {
        max-width: 100%;
        max-height: min(70vh, 480px);
        object-fit: contain;
    }

    /* TABLET */

    @media (max-width: 991.98px) {
        #formBuilderIndexRoot .container-fluid {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        #formBuilderIndexRoot .custom-fb-table {
            min-width: 850px;
        }

        #formBuilderIndexRoot .metric-card {
            min-height: 86px;
        }
    }

    /* MOBILE */

    @media (max-width: 767.98px) {
        #formBuilderIndexRoot .fb-header-card {
            padding: 13px 14px;
            align-items: flex-start;
        }

        #formBuilderIndexRoot .fb-header-card h5 {
            font-size: 15px !important;
        }

        #formBuilderIndexRoot .fb-header-actions {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 7px;
        }

        #formBuilderIndexRoot .fb-header-actions .btn {
            width: 100%;
            min-height: 36px;
            padding: 7px 5px !important;
            font-size: 10.5px !important;
            white-space: normal;
        }

        #formBuilderIndexRoot .metric-card {
            min-height: 80px;
            padding: 11px !important;
        }

        #formBuilderIndexRoot .stat-label-title {
            font-size: 10px;
        }

        #formBuilderIndexRoot .stat-val-num {
            font-size: 16px;
        }

        #formBuilderIndexRoot .metric-icon-square {
            width: 29px;
            height: 29px;
            flex-basis: 29px;
            font-size: 12px;
        }

        #formBuilderIndexRoot .pc-fit-container {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }

        #formBuilderIndexRoot .custom-fb-table {
            width: 100%;
            min-width: 850px;
            table-layout: fixed;
        }

        #formBuilderIndexRoot .custom-fb-table th,
        #formBuilderIndexRoot .custom-fb-table td {
            padding: 9px 8px;
            font-size: 11px;
        }

        #formBuilderIndexRoot .fb-table-card > .border-top {
            align-items: flex-start !important;
        }

        #formBuilderIndexRoot #fbPaginationLinks {
            width: 100%;
        }

        #formBuilderIndexRoot #fbPaginationLinks nav {
            justify-content: flex-start !important;
        }

        #formBuilderIndexRoot #fbPaginationLinks .pagination {
            justify-content: flex-start;
        }

        .fb-modal-scope .minimal-modal-card {
            padding: 19px !important;
        }
    }

    /* SMALL MOBILE */

    @media (max-width: 575.98px) {
        #formBuilderIndexRoot {
            font-size: 12px;
        }

        #formBuilderIndexRoot .container-fluid {
            padding-left: 8px !important;
            padding-right: 8px !important;
        }

        #formBuilderIndexRoot .fb-header-card {
            padding: 11px !important;
            gap: 12px !important;
        }

        #formBuilderIndexRoot .fb-header-card h5 {
            font-size: 14px !important;
        }

        #formBuilderIndexRoot .fb-header-card small {
            font-size: 10.5px !important;
        }

        #formBuilderIndexRoot .row.g-2 {
            --bs-gutter-x: .5rem;
            --bs-gutter-y: .5rem;
        }

        #formBuilderIndexRoot .metric-card {
            min-height: 76px;
            padding: 9px !important;
        }

        #formBuilderIndexRoot .stat-label-title {
            font-size: 9.5px;
        }

        #formBuilderIndexRoot .stat-val-num {
            font-size: 15px;
        }

        #formBuilderIndexRoot .metric-icon-square {
            width: 26px;
            height: 26px;
            flex-basis: 26px;
            font-size: 11px;
        }

        #formBuilderIndexRoot .fb-table-card > .border-top {
            padding: 10px !important;
        }

        #formBuilderIndexRoot .fb-table-card > .border-top > small {
            font-size: 10px !important;
        }

        #formBuilderIndexRoot .shortcode-box {
            font-size: 10px;
        }

        .fb-modal-scope .modal-dialog {
            margin: 12px;
        }

        .fb-modal-scope .minimal-modal-card {
            padding: 16px !important;
        }

        .fb-modal-scope .minimal-modal-title {
            font-size: 14px !important;
        }

        .fb-modal-scope .minimal-modal-subtext {
            font-size: 11.5px !important;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        #formBuilderIndexRoot *,
        .fb-modal-scope * {
            transition-duration: .01ms !important;
            animation-duration: .01ms !important;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="formBuilderIndexRoot">

    <!-- 1. Page Header & Top Action Buttons -->
<div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3 mb-3">
    <div class="d-flex align-items-center gap-3 min-w-0">
        <span class="d-inline-flex align-items-center justify-content-center flex-shrink-0 shadow-xs" style="width: 40px; height: 40px; background: #e0f2fe; color: #0284c7; border-radius: 8px; font-size: 16px;">
            <i class="fa-solid fa-rectangle-list"></i>
        </span>
        <div class="min-w-0 lh-sm">
            <h5 class="fw-bold text-dark mb-1 text-truncate" style="font-size: 16px;">
                Form Builder
            </h5>
            <div class="text-secondary text-truncate m-0 p-0" style="font-size: 11.5px;">
                Forms, Fields &amp; Submissions
            </div>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2 flex-wrap flex-lg-nowrap justify-content-start justify-content-lg-end w-100 w-lg-auto">
        <a href="{{ route('admin.forms.submissions.index') }}" class="btn btn-sm btn-outline-secondary flex-fill flex-lg-grow-0 d-inline-flex align-items-center justify-content-center gap-2 px-3 text-nowrap bg-white" style="height: 34px; font-size: 11.5px; font-weight: 600;">
            <i class="fa-solid fa-address-book text-primary"></i>
            <span>Submissions</span>
        </a>

        <a href="{{ route('admin.forms.create') }}" class="btn btn-sm btn-primary flex-fill flex-lg-grow-0 d-inline-flex align-items-center justify-content-center gap-2 px-3 text-nowrap" style="height: 34px; font-size: 11.5px; font-weight: 600;">
            <i class="fa-solid fa-plus"></i>
            <span>New Form</span>
        </a>
    </div>
</div>

    <!-- STATISTICS -->
    <div class="row g-2 mb-3">

        <!-- Total Forms -->
        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-blue h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="stat-label-title text-truncate">Total Forms</span>
                    <div class="metric-icon-square bg-primary-subtle text-primary">
                        <i class="fa-solid fa-rectangle-list"></i>
                    </div>
                </div>
                <h4 class="stat-val-num bn-digit font-tabular">
                    {{ number_format($totalFormsCount) }}
                </h4>
            </div>
        </div>

        <!-- Active Forms -->
        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-green h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="stat-label-title text-truncate">Active Forms</span>
                    <div class="metric-icon-square bg-success-subtle text-success">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
                <h4 class="stat-val-num text-success bn-digit font-tabular">
                    {{ number_format($activeFormsCount) }}
                </h4>
            </div>
        </div>

        <!-- Submissions -->
        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-purple h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="stat-label-title text-truncate">Submissions</span>
                    <div class="metric-icon-square bg-purple-subtle text-primary" style="background-color: #f0edff; color: #6861ce;">
                        <i class="fa-solid fa-inbox"></i>
                    </div>
                </div>
                <h4 class="stat-val-num bn-digit font-tabular">
                    {{ number_format($totalSubmissionsCount) }}
                </h4>
            </div>
        </div>

        <!-- Fields -->
        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-amber h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="stat-label-title text-truncate">Total Fields</span>
                    <div class="metric-icon-square bg-warning-subtle text-warning-emphasis">
                        <i class="fa-solid fa-list-check"></i>
                    </div>
                </div>
                <h4 class="stat-val-num text-warning-emphasis bn-digit font-tabular">
                    {{ number_format($totalFieldsCount) }}
                </h4>
            </div>
        </div>

    </div>

    <!-- FORMS TABLE -->
    <div class="fb-table-card overflow-hidden">

        <div class="table-responsive pc-fit-container">
            <table class="table align-middle custom-fb-table mb-0" id="formsListTable">

                <thead>
                    <tr>
                        <th class="ps-3 col-fb-form">Form</th>
                        <th class="text-center col-fb-fields">Fields</th>
                        <th class="text-center col-fb-subs">Submissions</th>
                        <th class="col-fb-shortcode">Shortcode</th>
                        <th class="text-center col-fb-status">Status</th>
                        <th class="text-end pe-3 col-fb-action">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($forms as $form)

                        @php
                            $formLogoRaw = $form->logo ?? ($form->banner ?? null);
                            $formLogoUrl = !empty($formLogoRaw) ? $resolveFormMedia($formLogoRaw) : null;
                        @endphp

                        <tr id="form-row-{{ $form->id }}">

                            <!-- FORM IDENTITY -->
                            <td class="ps-3 col-fb-form">

                                <div class="d-flex align-items-center gap-2 min-w-0">

                                    @if($formLogoUrl)

                                        <div class="proof-thumb-box" onclick="zoomFormMedia('{{ $formLogoUrl }}', '{{ addslashes($form->name) }} Logo')" title="Preview Logo">

                                            <img src="{{ $formLogoUrl }}" alt="{{ $form->name }}" onerror="this.src='https://placehold.co/60x60?text=Form';">

                                            <div class="proof-thumb-hover-overlay">
                                                <i class="fas fa-search-plus text-white" style="font-size: 10px;"></i>
                                            </div>

                                        </div>

                                    @else

                                        <div class="proof-thumb-box bg-light text-secondary" style="cursor: default;" title="Default Form Icon">
                                            <i class="fa-solid fa-file-lines" style="font-size: 14px;"></i>
                                        </div>

                                    @endif

                                    <div class="min-w-0 flex-grow-1">

                                        <strong class="d-block text-truncate" style="font-size: 12px; line-height: 1.5;" title="{{ $form->name }}">

                                            <a href="{{ route('admin.forms.edit', $form) }}" class="text-dark text-decoration-none">
                                                {{ $form->name }}
                                            </a>

                                        </strong>

                                        <small class="text-muted d-block text-truncate" style="font-size: 10.5px; font-weight: 500;" title="/{{ $form->slug }}">
                                            /{{ $form->slug }}
                                        </small>

                                    </div>

                                </div>

                            </td>

                            <!-- FIELDS -->
                            <td class="text-center col-fb-fields">

                                <span class="badge badge-soft-primary bn-digit font-tabular px-2 py-1" style="font-size: 10px;">
                                    {{ $form->fields_count ?? ($form->fields ? $form->fields->count() : 0) }}
                                </span>

                            </td>

                            <!-- SUBMISSIONS -->
                            <td class="text-center col-fb-subs">

                                <a href="{{ route('admin.forms.submissions.index', ['form_id' => $form->id]) }}" class="subs-count-pill" title="View Submissions">

                                    <span class="badge badge-soft-secondary bn-digit font-tabular px-2 py-1" style="font-size: 10px; font-weight: 600;">

                                        <i class="fa-regular fa-folder-open me-1 text-primary"></i>{{ $form->submissions_count ?? 0 }}

                                    </span>

                                </a>

                            </td>

                            <!-- SHORTCODE -->
                            <td class="col-fb-shortcode">

                                <div class="shortcode-box">

                                    <span class="text-truncate" id="sc_text_{{ $form->id }}">[form id="{{ $form->id }}"]</span>

                                    <button type="button" class="btn-copy-shortcode" onclick="copyFormShortcode('[form id=&quot;{{ $form->id }}&quot;]')" title="Copy Shortcode">

                                        <i class="fa-regular fa-copy" style="font-size: 11px;"></i>

                                    </button>

                                </div>

                            </td>

                            <!-- STATUS -->
                            <td class="text-center col-fb-status">

                                @if($form->is_active)

                                    <span class="badge badge-soft-success px-2 py-1" style="font-size: 9.5px; font-weight: 700;">
                                        Active
                                    </span>

                                @else

                                    <span class="badge badge-soft-danger px-2 py-1" style="font-size: 9.5px; font-weight: 700;">
                                        Inactive
                                    </span>

                                @endif

                            </td>

                            <!-- ACTIONS -->
                            <td class="text-end pe-3 col-fb-action">

                                <div class="d-inline-flex align-items-center gap-1">

                                    <a href="{{ route('admin.forms.submissions.index', ['form_id' => $form->id]) }}" class="btn-action-square btn-action-view" title="Submissions">
                                        <i class="fa-solid fa-address-book"></i>
                                    </a>

                                    <a href="{{ route('admin.forms.edit', $form) }}" class="btn-action-square btn-action-edit" title="Edit Form">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>

                                    <button type="button" class="btn-action-square btn-action-delete" onclick="triggerFormDeleteModal('{{ $form->id }}', '{{ addslashes($form->name) }}')" title="Delete Form">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>

                                    <form id="delete-form-element-{{ $form->id }}" method="POST" action="{{ route('admin.forms.destroy', $form) }}" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted small">

                                <i class="fa-regular fa-rectangle-list fa-2x mb-2 d-block opacity-25"></i>

                                <span class="fw-semibold d-block mb-3">
                                    No forms found.
                                </span>

                                <a href="{{ route('admin.forms.create') }}" class="btn btn-primary btn-compact">
                                    <i class="fa-solid fa-plus"></i>
                                    Create Form
                                </a>

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

        <!-- TABLE FOOTER -->
        <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">

            <small class="text-muted bn-digit font-tabular" style="font-size: 11px;">
                Showing {{ $forms->firstItem() ?? 0 }} to {{ $forms->lastItem() ?? 0 }} of {{ $forms->total() }} records
            </small>

            <div id="fbPaginationLinks">
                {{ $forms->links('pagination::bootstrap-5') }}
            </div>

        </div>

    </div>

</div>

<!-- DELETE CONFIRMATION MODAL -->
<div class="modal fade fb-modal-scope" id="formConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 108600;">

    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">

        <div class="modal-content minimal-modal-card">

            <h6 class="minimal-modal-title">
                Delete this form?
            </h6>

            <p class="minimal-modal-subtext" id="formConfirmMessage">
                This form and all its submissions will be permanently deleted.
            </p>

            <div class="d-flex align-items-center gap-2">

                <button type="button" class="btn-minimal-delete" id="btnExecuteFormDelete">
                    <i class="fa-solid fa-trash-can me-1"></i> Delete
                </button>

                <button type="button" class="btn-minimal-cancel" data-bs-dismiss="modal">
                    Cancel
                </button>

            </div>

        </div>

    </div>

</div>

<!-- NOTIFICATION MODAL -->
<div class="modal fade fb-modal-scope" id="formNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 108500;">

    <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">

        <div class="modal-content minimal-modal-card">

            <h6 class="minimal-modal-title" id="formNoticeTitle">
                Notification
            </h6>

            <p class="minimal-modal-subtext" id="formNoticeMessage" style="margin-bottom: 16px !important;"></p>

            <div class="d-flex align-items-center">

                <button type="button" class="btn-minimal-delete" style="background-color: #1572e8 !important; border-color: #1572e8 !important;" data-bs-dismiss="modal">
                    OK
                </button>

            </div>

        </div>

    </div>

</div>

<!-- IMAGE PREVIEW MODAL -->
<div class="modal fade fb-modal-scope" id="formMediaZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 108000;">

    <div class="modal-dialog modal-dialog-centered modal-md">

        <div class="modal-content border-0 shadow-lg text-center p-2 bg-white">

            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">

                <span class="fw-bold text-dark small" style="font-size: 12px;" id="formMediaZoomTitle">
                    <i class="fa-solid fa-image text-primary me-1"></i>
                    Form Preview
                </span>

                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>

            </div>

            <div class="modal-body p-2">

                <img id="formMediaZoomImgTag" src="" alt="Form Media" class="img-fluid border">

            </div>

        </div>

    </div>

</div>

<script>
    let activeDeleteFormId = null;

    function showFormModalNotice(message, title = 'Notice') {
        const titleEl = document.getElementById('formNoticeTitle');
        const msgEl   = document.getElementById('formNoticeMessage');

        if (titleEl) titleEl.innerText = title;
        if (msgEl) msgEl.innerText = message;

        const modalEl = document.getElementById('formNoticeModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function triggerFormDeleteModal(formId, formName) {
        activeDeleteFormId = formId;
        const msgEl = document.getElementById('formConfirmMessage');
        if (msgEl) {
            msgEl.innerHTML = 'Form <strong>"' + formName + '"</strong> and all its submissions will be permanently removed.';
        }

        const modalEl = document.getElementById('formConfirmModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    document.getElementById('btnExecuteFormDelete')?.addEventListener('click', function() {
        if (activeDeleteFormId) {
            const formEl = document.getElementById('delete-form-element-' + activeDeleteFormId);
            if (formEl) {
                formEl.submit();
            }
        }
    });

    function copyFormShortcode(code) {
        navigator.clipboard.writeText(code).then(() => {
            showFormModalNotice('Shortcode copied successfully!', 'Copied');
        }).catch(() => {
            showFormModalNotice('Copy failed. Please copy manually.', 'Notice');
        });
    }

    function zoomFormMedia(url, title = 'Form Media') {
        const titleEl = document.getElementById('formMediaZoomTitle');
        const imgTag  = document.getElementById('formMediaZoomImgTag');
        if (url && imgTag) {
            if (titleEl) titleEl.innerText = title;
            imgTag.src = url;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('formMediaZoomModal')).show();
        }
    }
</script>

@endsection
