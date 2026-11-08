
@extends('layouts.admin')

@section('title', 'Suppression & Blacklist - Email Marketing')

@php
    $totalCount = method_exists($suppressions, 'total') ? $suppressions->total() : $suppressions->count();

    if (class_exists(\App\Models\EmailSuppressionList::class)) {
        $hardBounceCount = \App\Models\EmailSuppressionList::whereIn('reason', ['hard_bounce', 'bounce'])->count();
        $spamCount       = \App\Models\EmailSuppressionList::where('reason', 'spam_complaint')->count();
        $unsubCount      = \App\Models\EmailSuppressionList::whereIn('reason', ['unsubscribe', 'compliance'])->count();
    } else {
        $hardBounceCount = method_exists($suppressions, 'whereIn') ? $suppressions->whereIn('reason', ['hard_bounce', 'bounce'])->count() : 0;
        $spamCount       = method_exists($suppressions, 'where') ? $suppressions->where('reason', 'spam_complaint')->count() : 0;
        $unsubCount      = method_exists($suppressions, 'whereIn') ? $suppressions->whereIn('reason', ['unsubscribe', 'compliance'])->count() : 0;
    }

    $subscribersRoute = Route::has('admin.email_marketing.subscribers.index')
        ? route('admin.email_marketing.subscribers.index')
        : url('admin/email-marketing/subscribers');

    $suppressionStoreRoute = Route::has('admin.email_marketing.suppression.store')
        ? route('admin.email_marketing.suppression.store')
        : url('admin/email-marketing/suppression/store');
@endphp

@section('admin_content')

<style>
    /* =========================================================
       SUPPRESSION & BLACKLIST
       Kaiadmin Lite v1.2.0 - Classic / White Classic
    ========================================================= */

    #email-marketing-suppression-root {
        --sp-primary: #1572e8;
        --sp-primary-rgb: 21, 114, 232;
        --sp-danger: #e65353;
        --sp-danger-rgb: 230, 83, 83;
        --sp-success: #18a979;
        --sp-warning: #e9a23b;
        --sp-info: #17a2b8;
        --sp-text: #344054;
        --sp-heading: #1f2937;
        --sp-muted: #7b8494;
        --sp-border: #e6eaf0;
        --sp-surface: #ffffff;
        --sp-soft: #f7f9fc;
        --sp-radius: 5px;

        min-width: 0;
        max-width: 100%;
        overflow-x: clip;
        color: var(--sp-text);
        font-size: 13px;
    }

    #email-marketing-suppression-root *,
    #email-marketing-suppression-root *::before,
    #email-marketing-suppression-root *::after {
        box-sizing: border-box;
    }

    /* Page Header */
    #email-marketing-suppression-root .suppression-header-wrap {
        gap: 14px;
        margin-bottom: 18px;
    }

    #email-marketing-suppression-root .page-title {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 9px;
        margin: 0;
        color: var(--sp-heading);
        font-size: 19px;
        font-weight: 700;
        line-height: 1.4;
    }

    #email-marketing-suppression-root .page-title-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        border-radius: var(--sp-radius);
        background: rgba(var(--sp-danger-rgb), .09);
        color: var(--sp-danger);
        font-size: 15px;
    }

    #email-marketing-suppression-root .page-description {
        display: block;
        margin-top: 5px;
        color: var(--sp-muted);
        font-size: 12px;
        line-height: 1.6;
    }

    #email-marketing-suppression-root .top-action-btns {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }

    /* Common Cards */
    #email-marketing-suppression-root .card {
        min-width: 0;
        margin-bottom: 16px;
        overflow: hidden;
        border: 1px solid var(--sp-border) !important;
        border-radius: var(--sp-radius) !important;
        background: var(--sp-surface);
        box-shadow: 0 2px 8px rgba(31, 41, 55, .035) !important;
    }

    #email-marketing-suppression-root .section-header-wrap {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        min-height: 46px;
        padding: 10px 15px;
        border-bottom: 1px solid var(--sp-border);
        background: #fff;
    }

    #email-marketing-suppression-root .section-title {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
        color: var(--sp-heading);
        font-size: 12px;
        font-weight: 700;
        line-height: 1.45;
    }

    #email-marketing-suppression-root .section-title > i {
        color: var(--sp-danger);
        font-size: 13px;
    }

    #email-marketing-suppression-root .section-body {
        padding: 16px;
    }

    /* Buttons */
    #email-marketing-suppression-root .btn,
    #email-marketing-suppression-root button,
    #email-marketing-suppression-root .badge,
    #email-marketing-suppression-root .alert,
    #email-marketing-suppression-root .form-control,
    #email-marketing-suppression-root .form-select,
    #email-marketing-suppression-root .input-group-text,
    #email-marketing-suppression-root .page-link,
    #email-marketing-suppression-root .collapse-toggle-btn,
    .suppression-modal-scope .modal-content,
    .suppression-modal-scope .btn,
    .suppression-modal-scope .form-control,
    .suppression-modal-scope .form-select {
        border-radius: var(--sp-radius) !important;
    }

    #email-marketing-suppression-root .btn-compact {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        min-height: 34px;
        padding: 7px 12px;
        font-size: 11.5px;
        font-weight: 600;
        line-height: 1.35;
        white-space: nowrap;
        transition: background-color .18s ease, border-color .18s ease, box-shadow .18s ease;
    }

    #email-marketing-suppression-root .btn-danger {
        background-color: var(--sp-danger);
        border-color: var(--sp-danger);
    }

    #email-marketing-suppression-root .btn-danger:hover {
        background-color: #d94343;
        border-color: #d94343;
        box-shadow: 0 3px 9px rgba(var(--sp-danger-rgb), .17);
    }

    /* Collapse Toggle */
    #email-marketing-suppression-root .collapse-toggle-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 28px;
        width: 28px;
        height: 28px;
        padding: 0;
        border: 1px solid var(--sp-border);
        background: #fff;
        color: var(--sp-muted);
        cursor: pointer;
        transition: all .18s ease;
    }

    #email-marketing-suppression-root .collapse-toggle-btn:hover {
        border-color: var(--sp-danger);
        background: rgba(var(--sp-danger-rgb), .06);
        color: var(--sp-danger);
    }

    #email-marketing-suppression-root .collapse-icon {
        font-size: 10px;
        transition: transform .2s ease;
    }

    #email-marketing-suppression-root .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    /* KPI Cards */
    #email-marketing-suppression-root .metric-card {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        min-height: 105px;
        padding: 13px;
        overflow: hidden;
        border: 1px solid var(--sp-border) !important;
        border-left-width: 3px !important;
        border-radius: var(--sp-radius) !important;
        background: #fff;
        box-shadow: none !important;
        transition: transform .18s ease, box-shadow .18s ease;
    }

    #email-marketing-suppression-root .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 14px rgba(31, 41, 55, .07) !important;
    }

    #email-marketing-suppression-root .metric-card-red {
        border-left-color: var(--sp-danger) !important;
    }

    #email-marketing-suppression-root .metric-card-dark {
        border-left-color: #475467 !important;
    }

    #email-marketing-suppression-root .metric-card-amber {
        border-left-color: var(--sp-warning) !important;
    }

    #email-marketing-suppression-root .metric-card-slate {
        border-left-color: #64748b !important;
    }

    #email-marketing-suppression-root .stat-label-title {
        color: var(--sp-muted);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .015em;
        line-height: 1.4;
    }

    #email-marketing-suppression-root .stat-val-num {
        margin: 0;
        color: var(--sp-heading);
        font-size: 18px;
        font-weight: 700;
        line-height: 1.35;
        letter-spacing: -.02em;
        overflow-wrap: anywhere;
    }

    #email-marketing-suppression-root .stat-sub-text {
        display: block;
        margin-top: 4px;
        color: var(--sp-muted);
        font-size: 10px;
        line-height: 1.4;
    }

    #email-marketing-suppression-root .metric-icon-square {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 32px;
        width: 32px;
        height: 32px;
        border-radius: var(--sp-radius) !important;
        font-size: 13px;
    }

    #email-marketing-suppression-root .metric-card-red .metric-icon-square {
        background: rgba(var(--sp-danger-rgb), .09) !important;
        color: var(--sp-danger) !important;
    }

    #email-marketing-suppression-root .metric-card-dark .metric-icon-square {
        background: #f0f2f5 !important;
        color: #475467 !important;
    }

    #email-marketing-suppression-root .metric-card-amber .metric-icon-square {
        background: rgba(233, 162, 59, .12) !important;
        color: #bd7a12 !important;
    }

    #email-marketing-suppression-root .metric-card-slate .metric-icon-square {
        background: #f1f5f9 !important;
        color: #64748b !important;
    }

    /* Filter Form */
    #email-marketing-suppression-root .form-label {
        display: block;
        margin-bottom: 6px;
        color: #475467;
        font-size: 11.5px;
        font-weight: 700;
        line-height: 1.4;
    }

    #email-marketing-suppression-root .form-control,
    #email-marketing-suppression-root .form-select,
    #email-marketing-suppression-root .input-group-text {
        min-height: 37px;
        border: 1px solid #dce2ea;
        background-color: #fff;
        color: var(--sp-text);
        font-size: 12px;
        box-shadow: none;
    }

    #email-marketing-suppression-root .form-control,
    #email-marketing-suppression-root .form-select {
        padding: 8px 11px;
    }

    #email-marketing-suppression-root .form-control::placeholder {
        color: #a0a8b5;
        opacity: 1;
    }

    #email-marketing-suppression-root .form-control:focus,
    #email-marketing-suppression-root .form-select:focus {
        border-color: var(--sp-danger) !important;
        box-shadow: 0 0 0 3px rgba(var(--sp-danger-rgb), .10) !important;
        outline: none;
    }

    #email-marketing-suppression-root .input-group {
        min-width: 0;
        flex-wrap: nowrap;
    }

    #email-marketing-suppression-root .input-group > .input-group-text {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 38px;
        padding: 7px 10px;
        border-color: #dce2ea;
        background: var(--sp-soft);
        color: var(--sp-muted);
    }

    #email-marketing-suppression-root .input-group > .form-control,
    #email-marketing-suppression-root .input-group > .form-select {
        min-width: 0;
    }

    #email-marketing-suppression-root .suppression-filter-control {
        min-height: 37px;
        font-size: 12px !important;
    }

    #email-marketing-suppression-root .suppression-addon-text {
        font-size: 11px !important;
    }

    /* Table Header */
    #email-marketing-suppression-root .table-card-heading {
        padding: 13px 15px;
        border-bottom: 1px solid var(--sp-border);
        background: #fff;
    }

    #email-marketing-suppression-root .table-card-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        color: var(--sp-heading);
        font-size: 13px;
        font-weight: 700;
    }

    #email-marketing-suppression-root .table-card-title i {
        color: var(--sp-danger);
    }

    #email-marketing-suppression-root .table-meta {
        display: block;
        margin-top: 4px;
        color: var(--sp-muted);
        font-size: 10.5px;
        line-height: 1.5;
    }

    /* Table: Keep Every Column Available */
    #email-marketing-suppression-root .pc-fit-container {
        width: 100%;
        max-width: 100%;
        overflow-x: auto !important;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
    }

    #email-marketing-suppression-root .custom-suppression-table {
        width: 100%;
        min-width: 950px;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
        table-layout: auto;
    }

    #email-marketing-suppression-root .custom-suppression-table thead th {
        padding: 11px 10px;
        border-bottom: 1px solid var(--sp-border);
        background: #f8f9fc;
        color: #667085;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .035em;
        text-transform: uppercase;
        white-space: nowrap;
        vertical-align: middle;
    }

    #email-marketing-suppression-root .custom-suppression-table tbody td {
        padding: 11px 10px;
        border-bottom: 1px solid #eef1f5;
        background: #fff;
        color: var(--sp-text);
        font-size: 11.5px;
        vertical-align: middle;
    }

    #email-marketing-suppression-root .custom-suppression-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #email-marketing-suppression-root .custom-suppression-table tbody tr.suppression-entry-row:hover td {
        background: #fafbfe;
    }

    #email-marketing-suppression-root .custom-suppression-table .col-sp-id {
        min-width: 65px;
        width: 65px;
    }

    #email-marketing-suppression-root .custom-suppression-table .col-sp-email {
        min-width: 230px;
    }

    #email-marketing-suppression-root .custom-suppression-table .col-sp-reason {
        min-width: 155px;
    }

    #email-marketing-suppression-root .custom-suppression-table .col-sp-details {
        min-width: 230px;
    }

    #email-marketing-suppression-root .custom-suppression-table .col-sp-date {
        min-width: 135px;
    }

    #email-marketing-suppression-root .custom-suppression-table .col-sp-actions {
        min-width: 120px;
    }

    #email-marketing-suppression-root .custom-suppression-table .email-value {
        display: block;
        max-width: 100%;
        overflow-wrap: anywhere;
        color: var(--sp-heading);
        font-size: 12px;
        font-weight: 700;
    }

    #email-marketing-suppression-root .custom-suppression-table .details-value {
        display: block;
        max-width: 260px;
        white-space: normal;
        overflow-wrap: anywhere;
        color: var(--sp-muted);
        font-size: 11px;
        line-height: 1.5;
    }

    #email-marketing-suppression-root .copy-email-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 25px;
        width: 25px;
        height: 25px;
        padding: 0;
        border: 1px solid var(--sp-border);
        border-radius: var(--sp-radius) !important;
        background: #fff;
        color: var(--sp-muted);
    }

    #email-marketing-suppression-root .copy-email-btn:hover {
        border-color: var(--sp-primary);
        color: var(--sp-primary);
    }

    #email-marketing-suppression-root .reason-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        max-width: 100%;
        padding: 5px 8px !important;
        border-radius: var(--sp-radius) !important;
        font-size: 9.5px;
        line-height: 1.4;
        white-space: normal;
        overflow-wrap: anywhere;
    }

    #email-marketing-suppression-root .unblock-btn {
        min-height: 30px;
        padding: 5px 9px !important;
        border: 1px solid #f0d2d2 !important;
        background: #fff7f7 !important;
        color: var(--sp-danger) !important;
        font-size: 10.5px;
    }

    #email-marketing-suppression-root .unblock-btn:hover {
        border-color: var(--sp-danger) !important;
        background: var(--sp-danger) !important;
        color: #fff !important;
    }

    /* Empty States */
    #email-marketing-suppression-root #emptySuppressionRow td,
    #email-marketing-suppression-root #noMatchSuppressionRow td {
        padding: 38px 15px !important;
        color: var(--sp-muted);
        white-space: normal;
        line-height: 1.6;
    }

    /* Pagination */
    #email-marketing-suppression-root .card-footer {
        border-top: 1px solid var(--sp-border) !important;
        background: #fafbfc !important;
    }

    #email-marketing-suppression-root .card-footer nav {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        width: auto !important;
        margin: 0 !important;
    }

    #email-marketing-suppression-root .card-footer nav > div.d-sm-none,
    #email-marketing-suppression-root .card-footer nav > .d-sm-none {
        display: none !important;
    }

    #email-marketing-suppression-root .card-footer nav > div.d-none,
    #email-marketing-suppression-root .card-footer nav > .d-none.flex-sm-fill {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    #email-marketing-suppression-root .card-footer nav p,
    #email-marketing-suppression-root .card-footer nav .small.text-muted,
    #email-marketing-suppression-root .card-footer nav div:has(> p) {
        display: none !important;
    }

    #email-marketing-suppression-root .card-footer .pagination {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 4px !important;
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
    }

    #email-marketing-suppression-root .card-footer .page-item {
        display: inline-flex !important;
    }

    #email-marketing-suppression-root .card-footer .page-item .page-link,
    #email-marketing-suppression-root .card-footer .page-item span {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 29px !important;
        height: 29px !important;
        padding: 0 7px !important;
        border: 1px solid #e0e5ec !important;
        border-radius: var(--sp-radius) !important;
        background: #fff !important;
        color: #475467 !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        line-height: 1 !important;
        text-decoration: none !important;
    }

    #email-marketing-suppression-root .card-footer .page-item .page-link:hover {
        border-color: var(--sp-primary) !important;
        background: rgba(var(--sp-primary-rgb), .06) !important;
        color: var(--sp-primary) !important;
    }

    #email-marketing-suppression-root .card-footer .page-item.active .page-link,
    #email-marketing-suppression-root .card-footer .page-item.active span {
        border-color: var(--sp-primary) !important;
        background: var(--sp-primary) !important;
        color: #fff !important;
    }

    #email-marketing-suppression-root .card-footer .page-item.disabled .page-link,
    #email-marketing-suppression-root .card-footer .page-item.disabled span {
        opacity: .45 !important;
        background: #f8fafc !important;
        cursor: not-allowed !important;
    }

    /* Feedback Alerts */
    #email-marketing-suppression-root .alert {
        border: 1px solid transparent !important;
        border-radius: var(--sp-radius) !important;
        font-size: 12px;
    }

    #email-marketing-suppression-root .alert-success {
        border-color: #c9ecdf !important;
        background: #f0faf6;
    }

    #email-marketing-suppression-root .alert-danger {
        border-color: #f4d3d3 !important;
        background: #fff5f5;
    }

    /* Modal Styling */
    .suppression-modal-scope .modal-dialog {
        width: auto;
        max-width: 420px;
        margin-right: auto;
        margin-left: auto;
    }

    .suppression-modal-scope .modal-content {
        overflow: hidden;
        border: 1px solid var(--sp-border, #e6eaf0) !important;
        border-radius: 6px !important;
        background: #fff;
        box-shadow: 0 14px 40px rgba(16, 24, 40, .15);
    }

    .suppression-modal-scope .modal-header,
    .suppression-modal-scope .modal-footer {
        padding: 12px 15px;
        border-color: #e9edf2 !important;
        background: #fafbfc;
    }

    .suppression-modal-scope .modal-title {
        color: #1f2937;
        font-size: 14px;
        font-weight: 700;
    }

    .suppression-modal-scope .modal-body {
        padding: 17px;
    }

    .suppression-modal-scope .form-label {
        display: block;
        margin-bottom: 6px;
        color: #475467;
        font-size: 11.5px;
        font-weight: 700;
    }

    .suppression-modal-scope .form-control,
    .suppression-modal-scope .form-select {
        min-height: 37px;
        padding: 8px 10px;
        border: 1px solid #dce2ea;
        background: #fff;
        color: #344054;
        font-size: 12px;
        box-shadow: none;
    }

    .suppression-modal-scope .form-control:focus,
    .suppression-modal-scope .form-select:focus {
        border-color: var(--sp-primary, #1572e8);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .10);
    }

    .suppression-modal-scope .modal-footer .btn {
        min-height: 34px;
        padding: 7px 12px;
        font-size: 11px;
    }

    /* Responsive */
    @media (max-width: 767.98px) {
        #email-marketing-suppression-root {
            padding-right: 10px !important;
            padding-left: 10px !important;
        }

        #email-marketing-suppression-root .suppression-header-wrap {
            align-items: stretch !important;
            gap: 12px;
        }

        #email-marketing-suppression-root .page-title {
            font-size: 17px;
        }

        #email-marketing-suppression-root .top-action-btns {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            width: 100%;
        }

        #email-marketing-suppression-root .top-action-btns .btn {
            width: 100%;
            min-width: 0;
            min-height: 37px;
            padding: 7px 5px;
            font-size: 10.5px;
            white-space: normal;
        }

        #email-marketing-suppression-root .section-header-wrap {
            padding: 10px 12px;
        }

        #email-marketing-suppression-root .section-body {
            padding: 13px;
        }

        #email-marketing-suppression-root .metric-card {
            min-height: 94px;
            padding: 10px;
        }

        #email-marketing-suppression-root .stat-label-title {
            font-size: 10px;
        }

        #email-marketing-suppression-root .stat-val-num {
            font-size: 15px;
        }

        #email-marketing-suppression-root .stat-sub-text {
            font-size: 9px;
        }

        #email-marketing-suppression-root .metric-icon-square {
            flex-basis: 27px;
            width: 27px;
            height: 27px;
            font-size: 11px;
        }

        #email-marketing-suppression-root .card {
            margin-bottom: 12px;
        }

        #email-marketing-suppression-root .table-card-heading {
            padding: 12px;
        }

        #email-marketing-suppression-root .card-footer {
            padding: 12px !important;
        }

        #email-marketing-suppression-root .card-footer > div {
            width: 100%;
            text-align: center;
        }

        #email-marketing-suppression-root .card-footer nav {
            justify-content: center !important;
        }

        .suppression-modal-scope .modal-dialog {
            max-width: calc(100vw - 24px);
            margin: 12px auto;
        }
    }

    @media (max-width: 380px) {
        #email-marketing-suppression-root .metric-card {
            padding: 8px;
        }

        #email-marketing-suppression-root .stat-val-num {
            font-size: 13px;
        }

        #email-marketing-suppression-root .top-action-btns .btn {
            font-size: 9.5px;
        }

        #email-marketing-suppression-root .section-title {
            font-size: 11px;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-suppression-root">

    {{-- 1. Page Header & Actions --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center suppression-header-wrap">
        <div class="min-w-0">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h5 class="page-title">
                    <span class="page-title-icon">
                        <i class="fas fa-ban"></i>
                    </span>
                    <span>Email Suppression &amp; Blacklist</span>
                </h5>

                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 font-monospace" id="suppression-total-badge" style="font-size: 10px;">
                    {{ number_format($totalCount) }} Suppressed
                </span>
            </div>

            <small class="page-description">
                Manage blocked email addresses and protect your sending reputation across promotional campaigns.
            </small>
        </div>

        <div class="top-action-btns">
            <a href="{{ $subscribersRoute }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-users"></i>
                <span>Subscribers</span>
            </a>

            <button type="button" class="btn btn-sm btn-danger btn-compact shadow-xs fw-bold" data-bs-toggle="modal" data-bs-target="#addSuppressionModal">
                <i class="fas fa-plus-circle"></i>
                <span>Add to Blacklist</span>
            </button>
        </div>
    </div>

    {{-- 2. Feedback Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 shadow-xs mb-3 d-flex align-items-center justify-content-between" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 shadow-xs mb-3 d-flex align-items-center justify-content-between" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-circle text-danger fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- 3. Suppression KPI Overview --}}
    <div class="card mb-3">
        <div class="section-header-wrap">
            <span class="section-title">
                <i class="fas fa-shield-halved"></i>
                Suppression &amp; Reputation Protection Overview
            </span>

            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseSuppressionKpis" aria-expanded="true" aria-controls="collapseSuppressionKpis" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>

        <div class="collapse show" id="collapseSuppressionKpis">
            <div class="section-body">
                <div class="row g-2">

                    {{-- Total Suppressed --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-red h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <span class="stat-label-title text-truncate">Total Suppressed</span>
                                <div class="metric-icon-square">
                                    <i class="fas fa-ban"></i>
                                </div>
                            </div>

                            <div class="min-w-0">
                                <h4 class="stat-val-num font-monospace">
                                    {{ number_format($totalCount) }}
                                </h4>
                                <small class="stat-sub-text font-monospace">Active blacklist entries</small>
                            </div>
                        </div>
                    </div>

                    {{-- Hard Bounces --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-red h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <span class="stat-label-title text-truncate">Hard Bounces</span>
                                <div class="metric-icon-square">
                                    <i class="fas fa-envelope-circle-check"></i>
                                </div>
                            </div>

                            <div class="min-w-0">
                                <h4 class="stat-val-num text-danger font-monospace">
                                    {{ number_format($hardBounceCount) }}
                                </h4>
                                <small class="stat-sub-text">Invalid or undeliverable addresses</small>
                            </div>
                        </div>
                    </div>

                    {{-- Spam Complaints --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-dark h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <span class="stat-label-title text-truncate">Spam Complaints</span>
                                <div class="metric-icon-square">
                                    <i class="fas fa-triangle-exclamation"></i>
                                </div>
                            </div>

                            <div class="min-w-0">
                                <h4 class="stat-val-num font-monospace">
                                    {{ number_format($spamCount) }}
                                </h4>
                                <small class="stat-sub-text">Feedback loop and direct reports</small>
                            </div>
                        </div>
                    </div>

                    {{-- Unsubscribed / Compliance --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-amber h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <span class="stat-label-title text-truncate">Opt-Out &amp; Legal</span>
                                <div class="metric-icon-square">
                                    <i class="fas fa-user-slash"></i>
                                </div>
                            </div>

                            <div class="min-w-0">
                                <h4 class="stat-val-num text-warning-emphasis font-monospace">
                                    {{ number_format($unsubCount) }}
                                </h4>
                                <small class="stat-sub-text">Unsubscribe and compliance requests</small>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- 4. Search & Reason Filter --}}
    <div class="card mb-3">
        <div class="section-header-wrap">
            <span class="section-title">
                <i class="fas fa-filter"></i>
                Search &amp; Filter Suppression Records
            </span>

            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseSuppressionFilter" aria-expanded="true" aria-controls="collapseSuppressionFilter" title="Toggle Filters">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>

        <div class="collapse show" id="collapseSuppressionFilter">
            <div class="section-body">
                <form id="suppressionFilterForm" onsubmit="return false;">
                    <div class="row g-3 align-items-end">

                        {{-- Instant Live Search --}}
                        <div class="col-12 col-md-5">
                            <label class="form-label" for="suppressionSearchInput">
                                <i class="fas fa-search me-1 text-danger"></i>
                                Search Email or Internal Notes
                            </label>

                            <div class="input-group">
                                <span class="input-group-text suppression-addon-text">
                                    <i class="fas fa-search"></i>
                                </span>

                                <input type="text" id="suppressionSearchInput" class="form-control suppression-filter-control font-monospace" placeholder="user@example.com, bounce, spam report..." onkeyup="filterSuppressionTable()">
                            </div>
                        </div>

                        {{-- Reason Filter --}}
                        <div class="col-12 col-md-4">
                            <label class="form-label" for="suppressionReasonFilter">
                                <i class="fas fa-shield-halved me-1 text-danger"></i>
                                Suppression Reason
                            </label>

                            <select id="suppressionReasonFilter" class="form-select suppression-filter-control font-monospace" onchange="filterSuppressionTable()">
                                <option value="all" selected>-- All Suppression Reasons --</option>
                                <option value="hard_bounce">Hard Bounce / Dead Address</option>
                                <option value="spam_complaint">Spam Complaint / Flagged</option>
                                <option value="unsubscribe">Manual Unsubscribe</option>
                                <option value="compliance">Compliance / Legal</option>
                            </select>
                        </div>

                        {{-- Reset Button --}}
                        <div class="col-12 col-md-3">
                            <button type="button" class="btn btn-light border btn-compact w-100 shadow-xs fw-semibold text-secondary" onclick="resetSuppressionFilters()">
                                <i class="fas fa-rotate-left"></i>
                                <span>Reset Filters</span>
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 5. Suppression Records Table --}}
    <div class="card mb-3">
        <div class="table-card-heading d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
            <div class="min-w-0">
                <h6 class="table-card-title">
                    <i class="fas fa-shield-halved"></i>
                    <span>Suppressed Inboxes &amp; Quarantine Audit</span>
                </h6>

                <small class="table-meta font-monospace" id="showing-entries-text">
                    Showing {{ method_exists($suppressions, 'firstItem') ? ($suppressions->firstItem() ?? 0) : 1 }} to {{ method_exists($suppressions, 'lastItem') ? ($suppressions->lastItem() ?? 0) : $suppressions->count() }} of {{$totalCount }} records
                </small>
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-secondary border font-monospace px-2 py-2" style="font-size: 10px;" id="visible-suppression-count">
                    {{ $totalCount }} Total
                </span>

                <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseSuppressionTable" aria-expanded="true" aria-controls="collapseSuppressionTable" title="Toggle Table">
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </button>
            </div>
        </div>

        <div class="collapse show" id="collapseSuppressionTable">
            <div class="table-responsive pc-fit-container" style="min-height: 250px;">
                <table class="table align-middle mb-0 custom-suppression-table" id="suppression-table">
                    <thead>
                        <tr>
                            <th class="ps-3 col-sp-id text-center">#ID</th>
                            <th class="col-sp-email">Blocked Recipient Email</th>
                            <th class="col-sp-reason">Suppression Reason</th>
                            <th class="col-sp-details">Audit Notes / Diagnostics</th>
                            <th class="col-sp-date">Blocked At</th>
                            <th class="text-end pe-3 col-sp-actions">Action</th>
                        </tr>
                    </thead>

                    <tbody id="suppression-tbody">
                        @forelse($suppressions as $item)
                            @php
                                $reasonBadge = match(strtolower($item->reason ?? '')) {
                                    'hard_bounce', 'bounce' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                    'spam_complaint'       => 'bg-dark text-white border border-dark',
                                    'unsubscribe'          => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                    'compliance'           => 'bg-info-subtle text-info border border-info-subtle',
                                    default                => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                };

                                $normalizedReason = match(strtolower($item->reason ?? '')) {
                                    'bounce' => 'hard_bounce',
                                    default  => strtolower($item->reason ?? ''),
                                };

                                $rowSearchData = strtolower(
                                    ($item->email ?? '') . ' ' .
                                    ($item->reason ?? '') . ' ' .
                                    ($item->details ?? '')
                                );

                                $deleteUrl = Route::has('admin.email_marketing.suppression.destroy')
                                    ? route('admin.email_marketing.suppression.destroy', $item->id)
                                    : url('admin/email-marketing/suppression/delete/' . $item->id);
                            @endphp

                            <tr class="suppression-entry-row align-middle" id="suppression-row-{{ $item->id }}" data-search="{{ $rowSearchData }}" data-reason="{{ $normalizedReason }}">

                                {{-- Record ID --}}
                                <td class="ps-3 col-sp-id text-center font-monospace bn-digit text-muted" style="font-size: 11px;">
                                    {{ $item->id }}
                                </td>

                                {{-- Blocked Email --}}
                                <td class="col-sp-email">
                                    <div class="min-w-0" style="line-height: 1.4;">
                                        <div class="d-flex align-items-center gap-2">
                                            <strong class="email-value font-monospace" title="{{ $item->email }}">
                                                {{ $item->email }}
                                            </strong>

                                            <button type="button" class="copy-email-btn shadow-none" onclick="copyBlockedEmail('{{ addslashes($item->email) }}')" title="Copy Email">
                                                <i class="fas fa-copy" style="font-size: 10px;"></i>
                                            </button>
                                        </div>

                                        <small class="text-muted font-monospace d-block mt-1" style="font-size: 10px;">
                                            Quarantined Address
                                        </small>
                                    </div>
                                </td>

                                {{-- Suppression Reason --}}
                                <td class="col-sp-reason">
                                    <span class="badge {{ $reasonBadge }} reason-badge font-monospace">
                                        @if(in_array(strtolower($item->reason ?? ''), ['hard_bounce', 'bounce']))
                                            <i class="fas fa-triangle-exclamation"></i>
                                        @elseif(strtolower($item->reason ?? '') === 'spam_complaint')
                                            <i class="fas fa-ban"></i>
                                        @elseif(strtolower($item->reason ?? '') === 'unsubscribe')
                                            <i class="fas fa-user-slash"></i>
                                        @endif

                                        {{ strtoupper(str_replace('_', ' ', $item->reason)) }}
                                    </span>
                                </td>

                                {{-- Audit Details --}}
                                <td class="col-sp-details">
                                    <small class="details-value font-monospace" title="{{ $item->details ?? 'Automated system suppression' }}">
                                        {{ $item->details ?? 'Automated system suppression' }}
                                    </small>
                                </td>

                                {{-- Blocked Date --}}
                                <td class="col-sp-date font-monospace" style="line-height: 1.4;">
                                    <span class="text-dark d-block" style="font-size: 11px;">
                                        {{ $item->created_at->format('M d, Y') }}
                                    </span>
                                    <small class="text-muted" style="font-size: 10px;">
                                        {{ $item->created_at->format('h:i A') }}
                                    </small>
                                </td>

                                {{-- Actions --}}
                                <td class="text-end pe-3 col-sp-actions">
                                    <button type="button" class="btn btn-sm btn-light border text-danger btn-compact unblock-btn shadow-xs fw-semibold" onclick="triggerUnblockModal({{ $item->id }}, '{{ addslashes($item->email) }}', '{{$deleteUrl }}')" title="Unblock / Remove from Blacklist">
                                        <i class="fas fa-unlock"></i>
                                        <span>Unblock</span>
                                    </button>
                                </td>

                            </tr>
                        @empty
                            <tr id="emptySuppressionRow">
                                <td colspan="6" class="text-center py-5 text-muted small">
                                    <i class="fas fa-shield-check fa-2x mb-2 d-block text-success opacity-50"></i>
                                    <span class="fw-semibold">No suppressed or blacklisted emails found. Domain reputation is clean.</span>
                                </td>
                            </tr>
                        @endforelse

                        <tr id="noMatchSuppressionRow" style="display: none;">
                            <td colspan="6" class="text-center py-5 text-muted small">
                                <i class="fas fa-filter-circle-xmark fa-2x mb-2 d-block text-secondary opacity-25"></i>
                                <span class="fw-semibold">No quarantined emails matched your search or reason filter.</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Responsive Pagination --}}
            @if(method_exists($suppressions, 'hasPages') &&$suppressions->hasPages())
                <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50 card-footer no-print">
                    <small class="text-muted font-monospace bn-digit" style="font-size: 11px;">
                        Showing {{ $suppressions->firstItem() }} to {{ $suppressions->lastItem() }} of {{$suppressions->total() }} records
                    </small>

                    <div>
                        {{ $suppressions->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal 1: Add Email to Suppression --}}
<div class="modal fade suppression-modal-scope" id="addSuppressionModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header border-bottom py-2 px-3 bg-light d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 13.5px;">
                    <i class="fas fa-ban text-danger"></i>
                    <span>Add Email to Blacklist</span>
                </h6>

                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ $suppressionStoreRoute }}" method="POST">
                @csrf

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="suppression_email_input">
                            Email Address <span class="text-danger">*</span>
                        </label>

                        <input type="email" name="email" id="suppression_email_input" class="form-control font-monospace shadow-none" placeholder="customer@example.com" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="suppression_reason_select">
                            Suppression Reason <span class="text-danger">*</span>
                        </label>

                        <select name="reason" id="suppression_reason_select" class="form-select font-monospace shadow-none" required>
                            <option value="hard_bounce">Hard Bounce (Invalid Address)</option>
                            <option value="spam_complaint">Spam Complaint / Flagged</option>
                            <option value="unsubscribe">Manual Unsubscribe Request</option>
                            <option value="compliance">Legal / Compliance Request</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label" for="suppression_details_input">
                            Internal Audit Notes
                        </label>

                        <textarea name="details" id="suppression_details_input" rows="2" class="form-control shadow-none font-monospace" placeholder="Reason details or ticket reference..."></textarea>
                    </div>
                </div>

                <div class="modal-footer border-top py-2 px-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-danger btn-compact shadow-xs fw-bold">
                        <i class="fas fa-ban"></i>
                        Confirm Blacklist
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal 2: Unblock Confirmation --}}
<div class="modal fade suppression-modal-scope" id="unblockConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">

            <div class="modal-body p-1">
                <div class="rounded-circle bg-warning-subtle text-warning-emphasis d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 46px; height: 46px; border-radius: 50% !important;">
                    <i class="fas fa-unlock fs-4"></i>
                </div>

                <h6 class="fw-bold text-dark mb-2" style="font-size: 14px;">
                    Unblock Recipient?
                </h6>

                <p class="text-muted small mb-3" style="font-size: 11px; line-height: 1.6;">
                    Are you sure you want to remove
                    <strong id="unblockModalEmail" class="text-dark font-monospace">email</strong>
                    from the suppression blacklist? Future campaigns may reach this inbox again.
                </p>

                <form id="unblockModalForm" method="POST" action="">
                    @csrf
                    @method('DELETE')

                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button type="submit" class="btn btn-danger btn-compact shadow-xs fw-bold">
                            Yes, Unblock
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
    function copyBlockedEmail(email) {
        if (email && navigator.clipboard) {
            navigator.clipboard.writeText(email);
        }
    }

    function triggerUnblockModal(id, email, deleteUrl) {
        document.getElementById('unblockModalEmail').innerText = email;
        document.getElementById('unblockModalForm').action = deleteUrl;
        const modalEl = document.getElementById('unblockConfirmModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function filterSuppressionTable() {
        const query  = (document.getElementById('suppressionSearchInput')?.value || '').toLowerCase().trim();
        const reason = (document.getElementById('suppressionReasonFilter')?.value || 'all').toLowerCase();

        const rows = document.querySelectorAll('.suppression-entry-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const searchData = row.getAttribute('data-search') || '';
            const rowReason  = row.getAttribute('data-reason') || '';

            const matchesQuery  = !query || searchData.includes(query);
            const matchesReason = reason === 'all' || rowReason === reason;

            if (matchesQuery && matchesReason) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const countBadge = document.getElementById('visible-suppression-count');
        if (countBadge) {
            countBadge.innerText = `${visibleCount} Showing`;
        }

        const noMatchRow = document.getElementById('noMatchSuppressionRow');
        if (noMatchRow) {
            noMatchRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
        }
    }

    function resetSuppressionFilters() {
        const searchInput  = document.getElementById('suppressionSearchInput');
        const reasonFilter = document.getElementById('suppressionReasonFilter');
        if (searchInput)  searchInput.value = '';
        if (reasonFilter) reasonFilter.value = 'all';
        filterSuppressionTable();
    }
</script>
@endsection
