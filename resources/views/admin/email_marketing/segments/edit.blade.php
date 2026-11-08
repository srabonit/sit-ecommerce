
@extends('layouts.admin')

@section('title', 'Audience Segments - Email Marketing')

@php
    $segments = $segments ?? collect();
    $totalSegmentsCount = $stats['total'] ?? (method_exists($segments, 'total') ? $segments->total() : $segments->count());

    $dynamicCount  = $stats['dynamic'] ?? $segments->where('type', 'dynamic')->count();
    $snapshotCount = $stats['snapshot'] ?? $segments->where('type', 'snapshot')->count();
    $totalMatchingAudience = $stats['total_reach'] ?? $segments->sum(fn($s) => (int)($s->matching_subscribers_count ?? 0));

    // Safe route fallbacks to prevent RouteNotFoundException
    $indexUrl = Route::has('admin.email_marketing.segments.index')
        ? route('admin.email_marketing.segments.index')
        : url('admin/email-marketing/segments');

    $createUrl = Route::has('admin.email_marketing.segments.create')
        ? route('admin.email_marketing.segments.create')
        : url('admin/email-marketing/segments/create');

    $exportCsvUrl = Route::has('admin.email_marketing.segments.export_csv')
        ? route('admin.email_marketing.segments.export_csv')
        : url('admin/email-marketing/segments/export-csv');

    $subscribersUrl = Route::has('admin.email_marketing.subscribers.index')
        ? route('admin.email_marketing.subscribers.index')
        : url('admin/email-marketing/subscribers');

    $recalculateBaseUrl = url('admin/email-marketing/segments/recalculate');
    $bulkRecalculateUrl = url('admin/email-marketing/segments/bulk-recalculate');
    $deleteBaseUrl       = url('admin/email-marketing/segments/delete');
    $bulkDeleteUrl       = url('admin/email-marketing/segments/bulk-delete');
@endphp

@section('admin_content')

<style>
    /* =========================================================
       AUDIENCE SEGMENTS - KAIADMIN LITE CLASSIC / WHITE
       All page styles are scoped to prevent admin-wide conflicts.
    ========================================================= */

    #email-marketing-segments-root {
        --segment-primary: var(--bs-primary, #1572e8);
        --segment-text: #1f2937;
        --segment-muted: #6b7280;
        --segment-border: #e5e7eb;
        --segment-surface: #ffffff;
        --segment-soft: #f8fafc;
        --segment-radius: 5px;
        color: var(--segment-text);
        min-width: 0;
    }

    #email-marketing-segments-root *,
    #email-marketing-segments-root *::before,
    #email-marketing-segments-root *::after {
        box-sizing: border-box;
    }

    /* Consistent 5px radius */
    #email-marketing-segments-root .btn,
    #email-marketing-segments-root button,
    #email-marketing-segments-root .badge,
    #email-marketing-segments-root .alert,
    #email-marketing-segments-root .form-control,
    #email-marketing-segments-root .form-select,
    #email-marketing-segments-root .input-group-text,
    #email-marketing-segments-root .card,
    #email-marketing-segments-root .page-link,
    #email-marketing-segments-root .collapse-toggle-btn,
    .segment-modal-scope .modal-content,
    .segment-modal-scope .btn,
    #custom-toast-holder .admin-segment-toast {
        border-radius: var(--segment-radius) !important;
    }

    #email-marketing-segments-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    #email-marketing-segments-root .input-group > .form-control,
    #email-marketing-segments-root .input-group > .form-select {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    #email-marketing-segments-root .font-monospace,
    #email-marketing-segments-root .bn-digit {
        font-variant-numeric: tabular-nums;
    }

    /* Page header */
    #email-marketing-segments-root .segments-header-wrap {
        min-width: 0;
    }

    #email-marketing-segments-root .segments-header-wrap h5 {
        color: var(--segment-text);
        letter-spacing: -0.35px;
    }

    #email-marketing-segments-root .segments-header-wrap > div:first-child {
        min-width: 0;
    }

    #email-marketing-segments-root .segments-header-wrap small {
        color: var(--segment-muted) !important;
        max-width: 680px;
    }

    /* Compact buttons */
    #email-marketing-segments-root .btn-compact {
        min-height: 32px;
        padding: 6px 11px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        font-size: 11.5px !important;
        line-height: 1.3;
        font-weight: 600 !important;
        white-space: nowrap;
        transition: background-color .15s ease, border-color .15s ease, color .15s ease, box-shadow .15s ease;
    }

    #email-marketing-segments-root .btn-compact:focus-visible,
    #email-marketing-segments-root .collapse-toggle-btn:focus-visible {
        outline: 2px solid rgba(21, 114, 232, .35);
        outline-offset: 2px;
    }

    #email-marketing-segments-root .btn-compact:hover {
        transform: translateY(-1px);
    }

    #email-marketing-segments-root .top-action-btns {
        flex-shrink: 0;
    }

    /* Cards */
    #email-marketing-segments-root .card {
        background: var(--segment-surface);
        border: 1px solid var(--segment-border) !important;
        box-shadow: 0 1px 3px rgba(31, 41, 55, .045) !important;
        margin-bottom: 15px;
        overflow: hidden;
    }

    #email-marketing-segments-root .section-header-wrap {
        min-height: 44px;
        padding: 10px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        background: #fff;
        border-bottom: 1px solid var(--segment-border);
    }

    #email-marketing-segments-root .section-header-wrap > span {
        color: var(--segment-text);
        font-size: 12px !important;
        font-weight: 700;
        min-width: 0;
    }

    /* Collapse toggle */
    #email-marketing-segments-root .collapse-toggle-btn {
        width: 27px;
        height: 27px;
        padding: 0;
        flex: 0 0 27px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid #dfe3e8;
        color: #64748b;
        transition: all .18s ease;
    }

    #email-marketing-segments-root .collapse-toggle-btn:hover {
        background: var(--segment-primary);
        border-color: var(--segment-primary);
        color: #fff;
    }

    #email-marketing-segments-root .collapse-icon {
        font-size: 10px;
        transition: transform .2s ease;
    }

    #email-marketing-segments-root .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    /* KPI cards */
    #email-marketing-segments-root .metric-card {
        height: 100%;
        min-width: 0;
        min-height: 112px;
        padding: 13px 14px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        border: 1px solid var(--segment-border) !important;
        border-left: 3px solid #1572e8 !important;
        background: #fff;
        box-shadow: 0 1px 3px rgba(31, 41, 55, .035) !important;
        transition: box-shadow .18s ease, transform .18s ease;
    }

    #email-marketing-segments-root .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 14px rgba(31, 41, 55, .075) !important;
    }

    #email-marketing-segments-root .metric-card-blue {
        border-left-color: #1572e8 !important;
    }

    #email-marketing-segments-root .metric-card-green {
        border-left-color: #31ce77 !important;
    }

    #email-marketing-segments-root .metric-card-amber {
        border-left-color: #ffad46 !important;
    }

    #email-marketing-segments-root .metric-card-cyan {
        border-left-color: #48abf7 !important;
    }

    #email-marketing-segments-root .stat-label-title {
        color: #6b7280;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .01em;
        line-height: 1.35;
    }

    #email-marketing-segments-root .stat-val-num {
        display: block;
        max-width: 100%;
        margin: 0;
        color: #1f2937;
        font-size: 21px;
        font-weight: 700;
        line-height: 1.25;
        letter-spacing: -.5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #email-marketing-segments-root .stat-sub-text {
        margin-top: 3px;
        color: #9ca3af !important;
        font-size: 10px;
        line-height: 1.35;
    }

    #email-marketing-segments-root .metric-icon-square {
        width: 33px;
        height: 33px;
        flex: 0 0 33px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px !important;
        font-size: 13px;
    }

    /* Filter toolbar */
    #email-marketing-segments-root .segment-filter-control {
        width: 100%;
        height: 36px !important;
        padding: 6px 9px !important;
        color: #374151 !important;
        background-color: #fff !important;
        border: 1px solid #dfe3e8 !important;
        border-radius: 5px !important;
        box-shadow: none !important;
        font-size: 11.5px !important;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #email-marketing-segments-root .segment-filter-control:focus {
        border-color: var(--segment-primary) !important;
        box-shadow: 0 0 0 2px rgba(21, 114, 232, .10) !important;
        outline: none;
    }

    #email-marketing-segments-root .segment-filter-control::placeholder {
        color: #9ca3af;
        opacity: 1;
    }

    #email-marketing-segments-root .segment-addon-text {
        min-height: 36px;
        padding: 6px 9px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f8fafc !important;
        border-color: #dfe3e8 !important;
        color: #6b7280 !important;
        font-size: 10.5px !important;
        white-space: nowrap;
    }

    #email-marketing-segments-root .input-group {
        min-width: 0;
    }

    #email-marketing-segments-root .input-group .form-control,
    #email-marketing-segments-root .input-group .form-select {
        min-width: 0;
    }

    #email-marketing-segments-root #segment-filter-form .border-top {
        border-color: #edf0f3 !important;
    }

    /* Alerts */
    #email-marketing-segments-root .alert {
        border: 1px solid transparent !important;
        box-shadow: 0 1px 3px rgba(31, 41, 55, .035) !important;
    }

    /* Bulk action area */
    #email-marketing-segments-root #selected-segments-badge {
        font-size: 10.5px;
        font-weight: 700;
    }

    #email-marketing-segments-root #showing-entries-text {
        color: #6b7280;
        font-size: 11px;
    }

    /* Table */
    #email-marketing-segments-root .pc-fit-container {
        width: 100%;
        min-height: 250px;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
    }

    #email-marketing-segments-root .custom-segments-table {
        width: 100%;
        min-width: 1050px;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
        table-layout: auto;
    }

    #email-marketing-segments-root .custom-segments-table thead th {
        padding: 11px 9px;
        color: #6b7280;
        background: #f8f9fa;
        border-bottom: 1px solid #e5e7eb;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .035em;
        line-height: 1.4;
        text-transform: uppercase;
        vertical-align: middle;
        white-space: nowrap;
    }

    #email-marketing-segments-root .custom-segments-table tbody td {
        padding: 10px 9px;
        color: #374151;
        background: #fff;
        border-bottom: 1px solid #f0f1f3;
        font-size: 11.5px;
        line-height: 1.45;
        vertical-align: middle;
    }

    #email-marketing-segments-root .custom-segments-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #email-marketing-segments-root .custom-segments-table tbody tr:hover td {
        background: #fafbfd;
    }

    #email-marketing-segments-root .custom-segments-table .form-check-input {
        width: 15px;
        height: 15px;
        margin: 0;
        border-color: #cbd5e1;
        cursor: pointer;
        vertical-align: middle;
    }

    #email-marketing-segments-root .custom-segments-table .form-check-input:checked {
        background-color: var(--segment-primary);
        border-color: var(--segment-primary);
    }

    #email-marketing-segments-root .custom-segments-table .col-sg-cb {
        min-width: 42px;
        width: 42px;
    }

    #email-marketing-segments-root .custom-segments-table .col-sg-id {
        min-width: 65px;
    }

    #email-marketing-segments-root .custom-segments-table .col-sg-name {
        min-width: 250px;
        white-space: normal;
    }

    #email-marketing-segments-root .custom-segments-table .col-sg-type {
        min-width: 120px;
    }

    #email-marketing-segments-root .custom-segments-table .col-sg-match {
        min-width: 145px;
    }

    #email-marketing-segments-root .custom-segments-table .col-sg-camps {
        min-width: 135px;
    }

    #email-marketing-segments-root .custom-segments-table .col-sg-date {
        min-width: 150px;
    }

    #email-marketing-segments-root .custom-segments-table .col-sg-action {
        min-width: 110px;
    }

    /* Pagination */
    #segments-pagination-links nav {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        width: auto !important;
        margin: 0 !important;
    }

    #segments-pagination-links nav > div.d-sm-none,
    #segments-pagination-links nav > .d-sm-none,
    #segments-pagination-links nav p,
    #segments-pagination-links nav .small.text-muted,
    #segments-pagination-links nav div:has(> p) {
        display: none !important;
    }

    #segments-pagination-links .pagination {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 4px !important;
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
    }

    #segments-pagination-links .page-item {
        display: inline-flex !important;
    }

    #segments-pagination-links .page-item .page-link,
    #segments-pagination-links .page-item span {
        min-width: 29px !important;
        height: 29px !important;
        padding: 0 8px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        color: #5f6875 !important;
        background: #fff !important;
        border: 1px solid #e1e5eb !important;
        border-radius: 5px !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        line-height: 1 !important;
        text-decoration: none !important;
        transition: all .15s ease;
    }

    #segments-pagination-links .page-item .page-link:hover {
        color: var(--segment-primary) !important;
        background: #f5f8fc !important;
        border-color: #b8cbe5 !important;
    }

    #segments-pagination-links .page-item.active .page-link,
    #segments-pagination-links .page-item.active span {
        color: #fff !important;
        background: var(--segment-primary) !important;
        border-color: var(--segment-primary) !important;
    }

    #segments-pagination-links .page-item.disabled .page-link,
    #segments-pagination-links .page-item.disabled span {
        opacity: .45 !important;
        background: #f8fafc !important;
        border-color: #e5e7eb !important;
        cursor: not-allowed !important;
    }

    /* Confirmation modal */
    .segment-modal-scope .modal-content {
        border: 1px solid #e5e7eb !important;
        box-shadow: 0 12px 35px rgba(15, 23, 42, .16) !important;
    }

    .segment-modal-scope .modal-body {
        padding: 5px !important;
    }

    .segment-modal-scope #confirm-modal-title {
        font-size: 14px;
        font-weight: 700;
    }

    .segment-modal-scope #confirm-modal-msg {
        font-size: 11.5px;
        line-height: 1.55;
    }

    .segment-modal-scope .btn-compact {
        min-height: 32px;
        padding: 6px 12px !important;
        font-size: 11px !important;
    }

    /* Toast notifications */
    #custom-toast-holder .admin-segment-toast {
        position: fixed;
        right: 20px;
        bottom: 20px;
        z-index: 108000;
        width: min(360px, calc(100vw - 32px));
        min-width: 260px;
        padding: 13px 14px;
        box-shadow: 0 8px 25px rgba(15, 23, 42, .18);
        animation: segmentToastIn .22s ease-out;
    }

    @keyframes segmentToastIn {
        from {
            transform: translateX(18px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    /* Tablet */
    @media (max-width: 991.98px) {
        #email-marketing-segments-root {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        #email-marketing-segments-root .metric-card {
            min-height: 105px;
        }

        #email-marketing-segments-root .top-action-btns {
            width: 100%;
        }

        #email-marketing-segments-root .top-action-btns .btn {
            flex: 1 1 auto;
        }

        #email-marketing-segments-root .custom-segments-table {
            min-width: 1050px;
        }
    }

    /* Mobile */
    @media (max-width: 767.98px) {
        #email-marketing-segments-root {
            padding-top: 12px !important;
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        #email-marketing-segments-root .segments-header-wrap {
            align-items: flex-start !important;
            gap: 12px !important;
        }

        #email-marketing-segments-root .segments-header-wrap h5 {
            font-size: 16px !important;
        }

        #email-marketing-segments-root .segments-header-wrap small {
            font-size: 11px !important;
        }

        #email-marketing-segments-root .top-action-btns {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 7px !important;
        }

        #email-marketing-segments-root .top-action-btns .btn-create-main {
            grid-column: 1 / -1;
            grid-row: 1;
        }

        #email-marketing-segments-root .top-action-btns .btn {
            width: 100%;
            min-height: 35px;
            padding: 7px 5px !important;
            font-size: 10.5px !important;
        }

        #email-marketing-segments-root .card {
            margin-bottom: 11px;
        }

        #email-marketing-segments-root .section-header-wrap {
            min-height: 42px;
            padding: 9px 11px;
        }

        #email-marketing-segments-root .section-header-wrap > span {
            font-size: 11px !important;
        }

        #email-marketing-segments-root .metric-card {
            min-height: 91px;
            padding: 10px !important;
        }

        #email-marketing-segments-root .stat-label-title {
            font-size: 9.5px;
        }

        #email-marketing-segments-root .stat-val-num {
            font-size: 17px;
        }

        #email-marketing-segments-root .stat-sub-text {
            font-size: 9px;
        }

        #email-marketing-segments-root .metric-icon-square {
            width: 27px;
            height: 27px;
            flex-basis: 27px;
            font-size: 11px;
        }

        #email-marketing-segments-root .segment-filter-control,
        #email-marketing-segments-root .segment-addon-text {
            min-height: 36px;
            font-size: 11px !important;
        }

        #email-marketing-segments-root #segment-filter-form .row {
            --bs-gutter-x: 8px;
            --bs-gutter-y: 8px;
        }

        #email-marketing-segments-root #segment-filter-form .p-3 {
            padding: 11px !important;
        }

        #email-marketing-segments-root .btn-compact {
            min-height: 34px;
            padding: 6px 9px !important;
            font-size: 10.5px !important;
        }

        #email-marketing-segments-root .pc-fit-container {
            width: 100%;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }

        #email-marketing-segments-root .custom-segments-table {
            min-width: 1050px !important;
            table-layout: auto !important;
        }

        #email-marketing-segments-root #showing-entries-text {
            font-size: 10px;
        }

        #email-marketing-segments-root .metric-card:hover {
            transform: none;
        }

        #segments-pagination-links nav {
            justify-content: center !important;
        }

        #custom-toast-holder .admin-segment-toast {
            right: 10px;
            bottom: 10px;
            width: calc(100vw - 20px);
            min-width: 0;
        }
    }

    /* Extra small mobile */
    @media (max-width: 380px) {
        #email-marketing-segments-root .metric-card {
            padding: 8px !important;
        }

        #email-marketing-segments-root .stat-label-title {
            font-size: 9px;
        }

        #email-marketing-segments-root .stat-val-num {
            font-size: 15px;
        }

        #email-marketing-segments-root .section-header-wrap > span {
            font-size: 10px !important;
        }

        #email-marketing-segments-root .top-action-btns .btn {
            font-size: 10px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-segments-root">

    {{-- 1. Header & Actions Toolbar --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 segments-header-wrap">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                    <i class="fas fa-filter-circle-dollar text-primary"></i>
                    <span>Audience Segments</span>
                </h5>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 font-monospace" id="segments-total-badge" style="font-size: 10px;">
                    {{ number_format($totalSegmentsCount) }} Segments
                </span>
            </div>
            <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">
                Target specific customer groups based on purchase history, cart abandonment, and customer engagement.
            </small>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 top-action-btns">
            <button type="button" class="btn btn-sm btn-outline-success btn-compact bg-white shadow-xs fw-semibold" onclick="exportSegmentsCsv()" title="Export filtered segments to CSV">
                <i class="fas fa-file-csv me-1 text-success"></i> <span>Export CSV</span>
            </button>
            <a href="{{ $subscribersUrl }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-users me-1"></i> <span>All Subscribers</span>
            </a>
            <a href="{{ $createUrl }}" class="btn btn-sm btn-primary btn-compact shadow-xs fw-bold btn-create-main">
                <i class="fas fa-plus-circle me-1"></i> <span>Create Segment</span>
            </a>
        </div>
    </div>

    {{-- 2. Feedback Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 small border-0 shadow-xs mb-3 d-flex align-items-center justify-content-between" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 small border-0 shadow-xs mb-3 d-flex align-items-center justify-content-between" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-circle text-danger fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- 3. Overview Stats Row (Collapsible Section) --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small">
                <i class="fas fa-chart-pie text-primary me-1"></i> Audience Segmentation &amp; Engine Overview
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseSegmentsKpis" aria-expanded="true" aria-controls="collapseSegmentsKpis" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>

        <div class="collapse show" id="collapseSegmentsKpis">
            <div class="p-3">
                <div class="row g-2">

                    {{-- Total Segments --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-blue h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Total Segments</span>
                                <div class="metric-icon-square bg-primary-subtle text-primary shadow-xs">
                                    <i class="fas fa-layer-group"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num" id="stat-total-segments">
                                    {{ number_format($totalSegmentsCount) }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Configured audience groups</small>
                            </div>
                        </div>
                    </div>

                    {{-- Dynamic Rules --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-green h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Dynamic Rules</span>
                                <div class="metric-icon-square bg-success-subtle text-success shadow-xs">
                                    <i class="fas fa-arrows-spin"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-success bn-digit font-monospace stat-val-num" id="stat-dynamic-rules">
                                    {{ number_format($dynamicCount) }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Auto-evaluating live criteria</small>
                            </div>
                        </div>
                    </div>

                    {{-- Static Snapshots --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-amber h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Static Snapshots</span>
                                <div class="metric-icon-square bg-warning-subtle text-warning-emphasis shadow-xs">
                                    <i class="fas fa-camera"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-warning-emphasis bn-digit font-monospace stat-val-num" id="stat-static-snapshots">
                                    {{ number_format($snapshotCount) }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Fixed audience records</small>
                            </div>
                        </div>
                    </div>

                    {{-- Cumulative Reach --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-cyan h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Target Reach</span>
                                <div class="metric-icon-square bg-info-subtle text-info shadow-xs">
                                    <i class="fas fa-users-viewfinder"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-info bn-digit font-monospace stat-val-num" id="stat-target-reach">
                                    {{ number_format($totalMatchingAudience) }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Matching subscriber reach</small>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- 4. Advanced Multi-Dimensional Filter Toolbar (Collapsible) --}}
    <div class="card border-0 shadow-sm mb-3" style="background: #f8fafc;">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small">
                <i class="fas fa-filter text-primary me-1"></i> Multi-Dimensional Filter &amp; Search Toolbar
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseSegmentsFilter" aria-expanded="true" aria-controls="collapseSegmentsFilter" title="Toggle Filters">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>

        <div class="collapse show" id="collapseSegmentsFilter">
            <div class="p-3">
                <form id="segment-filter-form" onsubmit="return false;">

                    {{-- Row 1: Search, Rule Type, Sorting, Per Page, Reset --}}
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-md-5">
                            <div class="input-group">
                                <span class="input-group-text segment-addon-text border-end-0">
                                    <i class="fas fa-search text-muted" style="font-size: 11px;"></i>
                                </span>
                                <input type="text" name="search" id="filter-search-input" class="form-control border-start-0 ps-0 segment-filter-control shadow-none font-monospace" placeholder="Search segment name or description...">
                            </div>
                        </div>

                        <div class="col-6 col-md-2">
                            <select name="type" id="filter-type" class="form-select segment-filter-control filter-trigger font-monospace shadow-none">
                                <option value="">Type: All Rules</option>
                                <option value="dynamic">Dynamic Criteria</option>
                                <option value="snapshot">Static Snapshot</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-2">
                            <select name="sort_by" id="filter-sort-by" class="form-select segment-filter-control filter-trigger font-monospace shadow-none">
                                <option value="latest">Newest Created</option>
                                <option value="oldest">Oldest Created</option>
                                <option value="name_asc">Name: A to Z</option>
                                <option value="name_desc">Name: Z to A</option>
                                <option value="audience_high">Audience: High to Low</option>
                                <option value="audience_low">Audience: Low to High</option>
                                <option value="most_campaigns">Most Campaigns Used</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-2">
                            <div class="input-group">
                                <span class="input-group-text segment-addon-text font-monospace">Per Page</span>
                                <select name="per_page" id="filter-per-page" class="form-select segment-filter-control filter-trigger fw-bold font-monospace shadow-none">
                                    <option value="10">10</option>
                                    <option value="15" selected>15</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="all">Show All</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-6 col-md-1 d-flex justify-content-end">
                            <button type="button" class="btn btn-light border btn-compact w-100 text-secondary shadow-xs fw-semibold" onclick="resetAllFilters()" title="Reset Filters">
                                <i class="fas fa-rotate-left"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Row 2: Date Presets & Conditional Panels --}}
                    <div class="row g-2 align-items-center mt-2 pt-2 border-top">
                        <div class="col-12 col-md-4">
                            <select name="date_filter" id="filter-date-select" class="form-select segment-filter-control font-monospace shadow-none">
                                <option value="">Created Date: All Time</option>
                                <option value="today">Created Today</option>
                                <option value="yesterday">Created Yesterday</option>
                                <option value="this_week">This Week</option>
                                <option value="last_week">Last Week</option>
                                <option value="this_month">This Month</option>
                                <option value="last_month">Last Month</option>
                                <option value="this_year">This Year</option>
                                <option value="specific_date">Specific Single Date...</option>
                                <option value="specific_month">Specific Month (YYYY-MM)...</option>
                                <option value="specific_year">Specific Year...</option>
                                <option value="custom_range">Custom Date Range (From - To)...</option>
                            </select>
                        </div>
                    </div>

                    {{-- Option A: Specific Single Date Panel --}}
                    <div id="singleDateWrapper" class="row g-2 align-items-center mt-2 pt-2 border-top d-none">
                        <div class="col-12 col-sm-auto small fw-bold text-muted font-monospace" style="font-size: 11px;">
                            <i class="fas fa-calendar-day text-primary me-1"></i> Specific Creation Date:
                        </div>
                        <div class="col-12 col-sm-auto">
                            <input type="date" name="specific_date" id="filter-specific-date" class="form-control segment-filter-control font-monospace">
                        </div>
                        <div class="col-12 col-sm-auto d-flex gap-2">
                            <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold" onclick="fetchFilteredSegments(1)">Apply Date</button>
                            <button type="button" class="btn btn-light border btn-compact shadow-xs" onclick="resetDatePanels()">Clear</button>
                        </div>
                    </div>

                    {{-- Option B: Specific Month Panel (YYYY-MM) --}}
                    <div id="specificMonthWrapper" class="row g-2 align-items-center mt-2 pt-2 border-top d-none">
                        <div class="col-12 col-sm-auto small fw-bold text-muted font-monospace" style="font-size: 11px;">
                            <i class="fas fa-calendar-week text-primary me-1"></i> Specific Creation Month:
                        </div>
                        <div class="col-12 col-sm-auto">
                            <input type="month" name="specific_month" id="filter-specific-month" class="form-control segment-filter-control font-monospace">
                        </div>
                        <div class="col-12 col-sm-auto d-flex gap-2">
                            <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold" onclick="fetchFilteredSegments(1)">Apply Month</button>
                            <button type="button" class="btn btn-light border btn-compact shadow-xs" onclick="resetDatePanels()">Clear</button>
                        </div>
                    </div>

                    {{-- Option C: Specific Year Panel --}}
                    <div id="specificYearWrapper" class="row g-2 align-items-center mt-2 pt-2 border-top d-none">
                        <div class="col-12 col-sm-auto small fw-bold text-muted font-monospace" style="font-size: 11px;">
                            <i class="fas fa-calendar-alt text-primary me-1"></i> Specific Creation Year:
                        </div>
                        <div class="col-12 col-sm-auto">
                            <input type="number" min="2020" max="2035" name="specific_year" id="filter-specific-year" class="form-control segment-filter-control font-monospace bn-digit" placeholder="{{ date('Y') }}">
                        </div>
                        <div class="col-12 col-sm-auto d-flex gap-2">
                            <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold" onclick="fetchFilteredSegments(1)">Apply Year</button>
                            <button type="button" class="btn btn-light border btn-compact shadow-xs" onclick="resetDatePanels()">Clear</button>
                        </div>
                    </div>

                    {{-- Option D: Custom Date Range Panel (From - To) --}}
                    <div id="customRangeWrapper" class="row g-2 align-items-center mt-2 pt-2 border-top d-none">
                        <div class="col-12 col-sm-auto small fw-bold text-muted font-monospace" style="font-size: 11px;">
                            <i class="fas fa-calendar-check text-primary me-1"></i> Custom Creation Range:
                        </div>
                        <div class="col-6 col-sm-auto">
                            <div class="input-group">
                                <span class="input-group-text segment-addon-text text-muted font-monospace">From</span>
                                <input type="date" name="start_date" id="filter-start-date" class="form-control segment-filter-control font-monospace">
                            </div>
                        </div>
                        <div class="col-6 col-sm-auto">
                            <div class="input-group">
                                <span class="input-group-text segment-addon-text text-muted font-monospace">To</span>
                                <input type="date" name="end_date" id="filter-end-date" class="form-control segment-filter-control font-monospace">
                            </div>
                        </div>
                        <div class="col-12 col-sm-auto d-flex gap-2">
                            <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold" onclick="fetchFilteredSegments(1)">Apply Range</button>
                            <button type="button" class="btn btn-light border btn-compact shadow-xs" onclick="resetDatePanels()">Clear</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>

    {{-- 5. Bulk Action Strip --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2 px-1">
        <div class="d-flex align-items-center flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-outline-primary btn-compact shadow-xs fw-bold bg-white" onclick="openBulkRecalculateModal()">
                <i class="fas fa-rotate me-1 text-primary"></i> <span>Bulk Recalculate Reach</span>
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger btn-compact shadow-xs fw-semibold bg-white" onclick="openBulkDeleteModal()">
                <i class="fas fa-trash-can me-1 text-danger"></i> <span>Bulk Delete</span>
            </button>
            <span class="badge bg-primary text-white font-monospace d-none px-2 py-1 shadow-xs" id="selected-segments-badge">
                0 selected
            </span>
        </div>

        <div class="text-muted font-monospace bn-digit small">
            <span id="showing-entries-text">Showing {{ method_exists($segments, 'firstItem') ? ($segments->firstItem() ?? 0) : 1 }} to {{ method_exists($segments, 'lastItem') ? ($segments->lastItem() ?? 0) : $segments->count() }} of {{ number_format($totalSegmentsCount) }} segments</span>
        </div>
    </div>

    {{-- 6. Segments Table Container --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="table-responsive pc-fit-container" style="min-height: 250px;">
            <table class="table custom-segments-table align-middle mb-0" id="segments-main-table">
                <thead>
                    <tr>
                        <th class="text-center col-sg-cb">
                            <input type="checkbox" class="form-check-input mt-0 shadow-none" id="check-all-segments" style="cursor: pointer; width: 14px; height: 14px; border-radius: 4px;">
                        </th>
                        <th class="ps-2 col-sg-id text-center">#ID</th>
                        <th class="col-sg-name">Segment Name &amp; Strategy Description</th>
                        <th class="col-sg-type">Rule Type</th>
                        <th class="text-center col-sg-match">Matching Subscribers</th>
                        <th class="text-center col-sg-camps">Used in Campaigns</th>
                        <th class="col-sg-date">Last Calculated</th>
                        <th class="text-end pe-3 col-sg-action">Action</th>
                    </tr>
                </thead>
                <tbody id="segments-table-tbody">
                    @include('admin.email_marketing.segments.partials.table_rows', ['segments' => $segments])
                </tbody>
            </table>
        </div>
    </div>

    {{-- 7. Responsive Pagination Footer --}}
    <div class="d-flex justify-content-center justify-content-md-end mb-4" id="segments-pagination-footer" style="{{ method_exists($segments, 'hasPages') &&$segments->hasPages() ? '' : 'display: none;' }}">
        <div id="segments-pagination-links">
            @if(method_exists($segments, 'hasPages') &&$segments->hasPages())
                {{ $segments->links('pagination::bootstrap-5') }}
            @endif
        </div>
    </div>

</div>

{{-- MODAL: Custom Reusable Delete & Bulk Confirm Modal --}}
<div class="modal fade segment-modal-scope" id="customConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 44px; height: 44px; border-radius: 50% !important;">
                    <i class="fas fa-trash-can fs-4"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="confirm-modal-title" style="font-size: 14px;">Delete Segment?</h6>
                <p class="text-muted small mb-3" id="confirm-modal-msg" style="font-size: 11px; line-height: 1.45;">
                    Are you sure you want to proceed? Existing campaigns linked to it will not be harmed.
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger btn-compact shadow-xs fw-bold" id="btn-confirm-action-execute">Yes, Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Floating Toast Feedback Notification Holder --}}
<div id="custom-toast-holder"></div>

{{-- JavaScript Engine --}}
<script>
    let searchTimeout = null;
    let pendingConfirmCallback = null;

    const indexUrl           = "{{ $indexUrl }}";
    const exportCsvUrl       = "{{ $exportCsvUrl }}";
    const recalculateBaseUrl = "{{ $recalculateBaseUrl }}";
    const bulkRecalculateUrl = "{{ $bulkRecalculateUrl }}";
    const deleteBaseUrl       = "{{ $deleteBaseUrl }}";
    const bulkDeleteUrl       = "{{ $bulkDeleteUrl }}";
    const csrfToken          = "{{ csrf_token() }}";

    // 1. Live Reload-Free Fetch
    function fetchFilteredSegments(page = 1) {
        const form = document.getElementById('segment-filter-form');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        params.append('page', page);

        const tbody = document.getElementById('segments-table-tbody');
        if (tbody) tbody.style.opacity = '0.35';

        fetch(indexUrl + '?' + params.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (tbody) {
                tbody.innerHTML = data.table_html;
                tbody.style.opacity = '1';
            }

            // Pagination Handling
            const paginationFooter = document.getElementById('segments-pagination-footer');
            const paginationLinks  = document.getElementById('segments-pagination-links');
            const perPageVal       = document.getElementById('filter-per-page')?.value || '15';

            if (paginationLinks && paginationFooter) {
                if (perPageVal === 'all' || !data.pagination_html || data.pagination_html.trim() === '') {
                    paginationLinks.innerHTML = '';
                    paginationFooter.style.display = 'none';
                } else {
                    paginationLinks.innerHTML = data.pagination_html;
                    paginationFooter.style.display = 'flex';
                }
            }

            // Stats Update
            if (data.stats) {
                const totalEl    = document.getElementById('stat-total-segments');
                const dynamicEl  = document.getElementById('stat-dynamic-rules');
                const snapshotEl = document.getElementById('stat-static-snapshots');
                const reachEl    = document.getElementById('stat-target-reach');
                const badgeEl    = document.getElementById('segments-total-badge');

                if (totalEl)    totalEl.innerText = Number(data.stats.total).toLocaleString();
                if (dynamicEl)  dynamicEl.innerText = Number(data.stats.dynamic).toLocaleString();
                if (snapshotEl) snapshotEl.innerText = Number(data.stats.snapshot).toLocaleString();
                if (reachEl)    reachEl.innerText = Number(data.stats.total_reach).toLocaleString();
                if (badgeEl)    badgeEl.innerText = Number(data.stats.total).toLocaleString() + ' Segments';
            }

            if (data.entries_text) {
                const entriesEl = document.getElementById('showing-entries-text');
                if (entriesEl) entriesEl.innerText = data.entries_text;
            }

            const checkAll = document.getElementById('check-all-segments');
            if (checkAll) checkAll.checked = false;
            updateSelectionBadge();
        })
        .catch(err => {
            console.error('Fetch error:', err);
            if (tbody) tbody.style.opacity = '1';
        });
    }

    // 2. Search Debounce & Change Triggers
    document.getElementById('filter-search-input')?.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => fetchFilteredSegments(1), 350);
    });

    document.querySelectorAll('.filter-trigger').forEach(el => {
        el.addEventListener('change', () => fetchFilteredSegments(1));
    });

    // 3. Date Presets Switcher
    document.getElementById('filter-date-select')?.addEventListener('change', function() {
        const val = this.value;
        const singleBox  = document.getElementById('singleDateWrapper');
        const monthBox   = document.getElementById('specificMonthWrapper');
        const yearBox    = document.getElementById('specificYearWrapper');
        const rangeBox   = document.getElementById('customRangeWrapper');

        singleBox?.classList.add('d-none');
        monthBox?.classList.add('d-none');
        yearBox?.classList.add('d-none');
        rangeBox?.classList.add('d-none');

        if (val === 'specific_date') {
            singleBox?.classList.remove('d-none');
        } else if (val === 'specific_month') {
            monthBox?.classList.remove('d-none');
        } else if (val === 'specific_year') {
            yearBox?.classList.remove('d-none');
        } else if (val === 'custom_range') {
            rangeBox?.classList.remove('d-none');
        } else {
            fetchFilteredSegments(1);
        }
    });

    function resetDatePanels() {
        const elDate  = document.getElementById('filter-specific-date');
        const elMonth = document.getElementById('filter-specific-month');
        const elYear  = document.getElementById('filter-specific-year');
        const elStart = document.getElementById('filter-start-date');
        const elEnd   = document.getElementById('filter-end-date');
        const elSel   = document.getElementById('filter-date-select');

        if (elDate)  elDate.value = '';
        if (elMonth) elMonth.value = '';
        if (elYear)  elYear.value = '';
        if (elStart) elStart.value = '';
        if (elEnd)   elEnd.value = '';
        if (elSel)   elSel.value = '';

        document.getElementById('singleDateWrapper')?.classList.add('d-none');
        document.getElementById('specificMonthWrapper')?.classList.add('d-none');
        document.getElementById('specificYearWrapper')?.classList.add('d-none');
        document.getElementById('customRangeWrapper')?.classList.add('d-none');

        fetchFilteredSegments(1);
    }

    function resetAllFilters() {
        document.getElementById('segment-filter-form')?.reset();
        resetDatePanels();
    }

    // 4. Pagination Link Click Interceptor
    document.addEventListener('click', function(e) {
        const pageLink = e.target.closest('#segments-pagination-links .page-link');
        if (pageLink) {
            e.preventDefault();
            const href = pageLink.getAttribute('href');
            if (href && href !== '#' && !href.startsWith('javascript:')) {
                const url = new URL(href);
                const page = url.searchParams.get('page') || 1;
                fetchFilteredSegments(page);
                document.querySelector('.custom-segments-table')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
    });

    // 5. Checkbox Selection & Badge Sync
    document.getElementById('check-all-segments')?.addEventListener('change', function() {
        document.querySelectorAll('.segment-select-cb').forEach(cb => cb.checked = this.checked);
        updateSelectionBadge();
    });

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('segment-select-cb')) {
            updateSelectionBadge();
        }
    });

    function getSelectedSegmentIds() {
        return Array.from(document.querySelectorAll('.segment-select-cb:checked')).map(cb => cb.value);
    }

    function updateSelectionBadge() {
        const selected = getSelectedSegmentIds();
        const badge = document.getElementById('selected-segments-badge');
        if (badge) {
            if (selected.length > 0) {
                badge.innerText = `${selected.length} selected`;
                badge.classList.remove('d-none');
            } else {
                badge.classList.add('d-none');
            }
        }
    }

    // 6. CSV Exporter (Exports with Active Filters)
    function exportSegmentsCsv() {
        const form = document.getElementById('segment-filter-form');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        const url = exportCsvUrl + '?' + params.toString();
        window.open(url, '_blank');
    }

    // 7. Single Segment Recalculation AJAX
    function recalculateSegmentAjax(id) {
        const icon = document.getElementById(`recalc-icon-${id}`);
        if (icon) icon.classList.add('fa-spin');

        fetch(`${recalculateBaseUrl}/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (icon) icon.classList.remove('fa-spin');
            if (data.success) {
                const countBadge = document.getElementById(`segment-count-${id}`);
                const dateBox = document.getElementById(`segment-last-calc-${id}`);
                if (countBadge) countBadge.innerText = Number(data.count).toLocaleString();
                if (dateBox) dateBox.innerHTML = `<span class="text-dark d-block font-monospace" style="font-size: 11px;">Just now</span><small class="text-muted font-monospace" style="font-size: 9.5px;">${data.last_calculated}</small>`;
                showToast('Recalculated', data.message, 'success');
            } else {
                showToast('Error', 'Failed to recalculate audience reach.', 'danger');
            }
        })
        .catch(() => {
            if (icon) icon.classList.remove('fa-spin');
            showToast('Error', 'Server error while recalculating.', 'danger');
        });
    }

    // 8. Bulk Recalculate
    function openBulkRecalculateModal() {
        const selected = getSelectedSegmentIds();
        if (selected.length === 0) {
            showToast('Warning', 'Please select at least one segment from the table checkbox.', 'warning');
            return;
        }

        fetch(bulkRecalculateUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ segment_ids: selected })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('Refreshed', data.message, 'success');
                fetchFilteredSegments(1);
            } else {
                showToast('Error', 'Failed to recalculate reach.', 'danger');
            }
        })
        .catch(() => {
            showToast('Error', 'Network or server error.', 'danger');
        });
    }

    // 9. Single Segment Deletion
    function deleteSegmentAjax(id, name) {
        const titleEl = document.getElementById('confirm-modal-title');
        const msgEl   = document.getElementById('confirm-modal-msg');
        if (titleEl) titleEl.innerText = 'Delete Segment?';
        if (msgEl) msgEl.innerHTML = `Are you sure you want to delete <strong>${name}</strong>? Existing campaigns linked to it will not be harmed.`;

        pendingConfirmCallback = function() {
            fetch(`${deleteBaseUrl}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                const modalEl = document.getElementById('customConfirmModal');
                if (modalEl && window.bootstrap) bootstrap.Modal.getInstance(modalEl)?.hide();
                showToast('Deleted', data.message, 'danger');
                fetchFilteredSegments(1);
            })
            .catch(() => {
                const modalEl = document.getElementById('customConfirmModal');
                if (modalEl && window.bootstrap) bootstrap.Modal.getInstance(modalEl)?.hide();
                fetchFilteredSegments(1);
            });
        };

        const modalEl = document.getElementById('customConfirmModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    // 10. Bulk Segment Deletion
    function openBulkDeleteModal() {
        const selected = getSelectedSegmentIds();
        if (selected.length === 0) {
            showToast('Warning', 'Please select at least one segment to delete.', 'warning');
            return;
        }

        const titleEl = document.getElementById('confirm-modal-title');
        const msgEl   = document.getElementById('confirm-modal-msg');
        if (titleEl) titleEl.innerText = 'Bulk Delete Segments?';
        if (msgEl) msgEl.innerHTML = `Are you sure you want to delete <strong>${selected.length}</strong> selected segment(s)?`;

        pendingConfirmCallback = function() {
            fetch(bulkDeleteUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ segment_ids: selected })
            })
            .then(res => res.json())
            .then(data => {
                const modalEl = document.getElementById('customConfirmModal');
                if (modalEl && window.bootstrap) bootstrap.Modal.getInstance(modalEl)?.hide();
                showToast('Deleted', data.message, 'danger');
                fetchFilteredSegments(1);
            })
            .catch(() => {
                const modalEl = document.getElementById('customConfirmModal');
                if (modalEl && window.bootstrap) bootstrap.Modal.getInstance(modalEl)?.hide();
                fetchFilteredSegments(1);
            });
        };

        const modalEl = document.getElementById('customConfirmModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    document.getElementById('btn-confirm-action-execute')?.addEventListener('click', function() {
        if (typeof pendingConfirmCallback === 'function') {
            pendingConfirmCallback();
        }
    });

    // 11. Floating Toast Notifications
    function showToast(title, message, type = 'success') {
        const holder = document.getElementById('custom-toast-holder');
        if (!holder) return;

        const toastId = 'toast-' + Date.now();
        const typeConfigs = {
            success: { icon: 'fa-check-circle', color: 'bg-success text-white' },
            danger:  { icon: 'fa-trash-can',    color: 'bg-danger text-white' },
            warning: { icon: 'fa-triangle-exclamation', color: 'bg-warning text-dark' },
            info:    { icon: 'fa-circle-info',  color: 'bg-primary text-white' }
        };
        const config = typeConfigs[type] || typeConfigs.success;

        const html = `
            <div id="${toastId}" class="admin-segment-toast ${config.color} p-3 d-flex align-items-center justify-content-between shadow-lg" style="border-radius: 5px !important;">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas ${config.icon} fs-5"></i>
                    <div>
                        <strong class="d-block" style="font-size: 13px;">${title}</strong>
                        <small style="opacity: 0.95; font-size: 11px;">${message}</small>
                    </div>
                </div>
                <button type="button" class="btn-close ${type === 'warning' ? '' : 'btn-close-white'} btn-sm ms-2 shadow-none" onclick="document.getElementById('${toastId}')?.remove()"></button>
            </div>
        `;

        holder.insertAdjacentHTML('beforeend', html);
        setTimeout(() => {
            const el = document.getElementById(toastId);
            if (el) el.remove();
        }, 3500);
    }
</script>
@endsection
