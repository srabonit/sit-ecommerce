
@extends('layouts.admin')

@section('title', 'Scheduled Emails - Email Marketing')

@section('admin_content')
@php
    $campaigns            = $campaigns ?? ($scheduledCampaigns ?? collect());
    $totalCampaignsCount  = method_exists($campaigns, 'total') ? $campaigns->total() : $campaigns->count();
    $totalRecipientsCount = (int) $campaigns->sum('total_recipients');

    $allCampaignsRoute = Route::has('admin.email_marketing.campaigns.index') 
        ? route('admin.email_marketing.campaigns.index') 
        : url('admin/email-marketing/campaigns');

    $createCampaignRoute = Route::has('admin.email_marketing.campaigns.create') 
        ? route('admin.email_marketing.campaigns.create') 
        : url('admin/email-marketing/campaigns/create');
@endphp

<style>
/* =========================================================
   KAIADMIN LITE — CLASSIC WHITE DESIGN
   Scheduled Email Marketing
========================================================= */

#email-marketing-scheduled-root {
    --sc-primary: var(--bs-primary, #1572e8);
    --sc-secondary: #686f7d;
    --sc-success: #31ce36;
    --sc-warning: #ffad46;
    --sc-danger: #f25961;
    --sc-info: #48abf7;

    --sc-body-bg: #f5f7fd;
    --sc-card-bg: #ffffff;
    --sc-heading: #2a2f5b;
    --sc-text: #495057;
    --sc-muted: #8d9498;
    --sc-border: #ebecec;
    --sc-soft-primary: #e8f1ff;
    --sc-soft-success: #e8f8ec;
    --sc-soft-warning: #fff4e5;
    --sc-soft-danger: #ffebed;
    --sc-soft-info: #e8f6ff;

    color: var(--sc-text);
    font-size: 13px;
    min-width: 0;
    width: 100%;
}

#email-marketing-scheduled-root *,
.scheduled-modal-scope * {
    box-sizing: border-box;
}

#email-marketing-scheduled-root .card,
#email-marketing-scheduled-root .btn,
#email-marketing-scheduled-root .badge,
#email-marketing-scheduled-root .alert,
#email-marketing-scheduled-root .form-control,
#email-marketing-scheduled-root .form-select,
#email-marketing-scheduled-root .input-group-text,
#email-marketing-scheduled-root .page-link,
#email-marketing-scheduled-root .collapse-toggle-btn,
.scheduled-modal-scope .modal-content,
.scheduled-modal-scope .btn {
    border-radius: 5px !important;
}

/* Page Header */

#email-marketing-scheduled-root .scheduled-header-wrap {
    padding: 2px 0 16px;
    border-bottom: 1px solid var(--sc-border);
}

#email-marketing-scheduled-root .scheduled-page-title {
    color: var(--sc-heading);
    font-size: clamp(17px, 1.5vw, 21px);
    font-weight: 700;
    letter-spacing: -.3px;
}

#email-marketing-scheduled-root .scheduled-page-description {
    color: var(--sc-muted);
    font-size: 12px;
    line-height: 1.5;
}

#email-marketing-scheduled-root .scheduled-total-badge {
    font-size: 10px;
    font-weight: 600;
    padding: 5px 9px;
    background: var(--sc-soft-primary);
    color: var(--sc-primary);
    border: 1px solid #d5e5ff;
}

/* Buttons */

#email-marketing-scheduled-root .btn-compact,
.scheduled-modal-scope .btn-compact {
    min-height: 34px;
    padding: 7px 12px !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    font-size: 11.5px !important;
    font-weight: 600 !important;
    line-height: 1.3;
    white-space: nowrap;
    transition: all .18s ease;
}

#email-marketing-scheduled-root .btn-primary {
    background-color: var(--sc-primary);
    border-color: var(--sc-primary);
}

#email-marketing-scheduled-root .btn-primary:hover {
    filter: brightness(.94);
}

#email-marketing-scheduled-root .btn-outline-secondary {
    color: var(--sc-secondary);
    border-color: #dce0e5;
    background: #fff;
}

#email-marketing-scheduled-root .btn-outline-secondary:hover {
    background: #f5f7fa;
    color: var(--sc-heading);
    border-color: #cbd0d8;
}

#email-marketing-scheduled-root .top-action-btns {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}

/* General Cards */

#email-marketing-scheduled-root .card {
    background: var(--sc-card-bg);
    border: 1px solid var(--sc-border) !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, .035);
    margin-bottom: 16px;
}

#email-marketing-scheduled-root .section-header-wrap {
    min-height: 43px;
    padding: 10px 15px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #fff;
    border-bottom: 1px solid var(--sc-border);
}

#email-marketing-scheduled-root .section-header-title {
    color: var(--sc-heading);
    font-size: 12px;
    font-weight: 700;
}

#email-marketing-scheduled-root .section-header-title i {
    color: var(--sc-primary);
}

/* Collapse Buttons */

#email-marketing-scheduled-root .collapse-toggle-btn {
    width: 27px;
    height: 27px;
    min-width: 27px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    color: var(--sc-secondary);
    border: 1px solid var(--sc-border);
    transition: all .2s ease;
}

#email-marketing-scheduled-root .collapse-toggle-btn:hover {
    background: var(--sc-soft-primary);
    color: var(--sc-primary);
    border-color: #cbdfff;
}

#email-marketing-scheduled-root .collapse-icon {
    font-size: 10px;
    transition: transform .25s ease;
}

#email-marketing-scheduled-root .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
    transform: rotate(180deg);
}

/* Metric Cards */

#email-marketing-scheduled-root .metric-card {
    min-height: 108px;
    height: 100%;
    padding: 14px 15px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    background: #fff;
    border: 1px solid var(--sc-border) !important;
    border-left: 3px solid var(--sc-primary) !important;
    box-shadow: 0 2px 6px rgba(0,0,0,.025);
    overflow: hidden;
    transition: box-shadow .2s ease, transform .2s ease;
}

#email-marketing-scheduled-root .metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 14px rgba(0,0,0,.06);
}

#email-marketing-scheduled-root .metric-card-cyan {
    border-left-color: var(--sc-info) !important;
}

#email-marketing-scheduled-root .metric-card-green {
    border-left-color: var(--sc-success) !important;
}

#email-marketing-scheduled-root .metric-card-amber {
    border-left-color: var(--sc-warning) !important;
}

#email-marketing-scheduled-root .stat-label-title {
    color: var(--sc-muted);
    font-size: 11px;
    font-weight: 700;
    line-height: 1.4;
    letter-spacing: .15px;
}

#email-marketing-scheduled-root .stat-val-num {
    color: var(--sc-heading);
    font-size: clamp(17px, 2vw, 21px);
    line-height: 1.3;
    font-weight: 700;
    letter-spacing: -.5px;
    overflow-wrap: anywhere;
}

#email-marketing-scheduled-root .stat-sub-text {
    margin-top: 4px;
    color: var(--sc-muted);
    font-size: 10px;
    line-height: 1.4;
}

#email-marketing-scheduled-root .metric-icon-square {
    width: 35px;
    height: 35px;
    min-width: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 5px !important;
    font-size: 14px;
}

/* Filter Panel */

#email-marketing-scheduled-root .scheduled-filter-card {
    background: #fff !important;
}

#email-marketing-scheduled-root .form-label {
    margin-bottom: 6px;
    color: var(--sc-heading);
    font-size: 11.5px;
    font-weight: 600;
}

#email-marketing-scheduled-root .scheduled-filter-control {
    width: 100%;
    height: 36px;
    min-height: 36px;
    padding: 7px 10px;
    color: var(--sc-text);
    background-color: #fff;
    border: 1px solid #e1e5eb;
    border-radius: 5px !important;
    box-shadow: none;
    font-size: 12px;
    transition: border-color .2s ease, box-shadow .2s ease;
}

#email-marketing-scheduled-root .scheduled-filter-control:focus {
    border-color: var(--sc-primary);
    box-shadow: 0 0 0 3px rgba(21,114,232,.10);
    outline: none;
}

#email-marketing-scheduled-root .scheduled-addon-text {
    min-width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--sc-muted);
    background: #f8f9fa;
    border-color: #e1e5eb;
    font-size: 12px;
}

#email-marketing-scheduled-root .input-group > .input-group-text {
    border-top-right-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
}

#email-marketing-scheduled-root .input-group > .form-control {
    border-top-left-radius: 0 !important;
    border-bottom-left-radius: 0 !important;
}

/* Table Header */

#email-marketing-scheduled-root .scheduled-table-heading {
    padding: 13px 15px;
    background: #fff;
    border-bottom: 1px solid var(--sc-border);
}

#email-marketing-scheduled-root .scheduled-table-title {
    color: var(--sc-heading);
    font-size: 13px;
    font-weight: 700;
}

#email-marketing-scheduled-root .scheduled-table-subtitle {
    display: block;
    margin-top: 4px;
    color: var(--sc-muted);
    font-size: 10.5px;
}

/* Table: All Columns Preserved */

#email-marketing-scheduled-root .pc-fit-container {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    overflow-y: visible;
    -webkit-overflow-scrolling: touch;
}

#email-marketing-scheduled-root .custom-scheduled-table {
    width: 100%;
    min-width: 1000px;
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
}

#email-marketing-scheduled-root .custom-scheduled-table thead th {
    padding: 11px 9px;
    color: #686f7d;
    background: #f8f9fc;
    border-bottom: 1px solid var(--sc-border);
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .35px;
    text-transform: uppercase;
    white-space: nowrap;
    vertical-align: middle;
}

#email-marketing-scheduled-root .custom-scheduled-table tbody td {
    padding: 11px 9px;
    color: var(--sc-text);
    background: #fff;
    border-bottom: 1px solid #f0f1f3;
    font-size: 11.5px;
    line-height: 1.5;
    vertical-align: middle;
}

#email-marketing-scheduled-root .custom-scheduled-table tbody tr:last-child td {
    border-bottom: 0;
}

#email-marketing-scheduled-root .custom-scheduled-table tbody tr:hover td {
    background: #fafbff;
}

#email-marketing-scheduled-root .custom-scheduled-table .col-sc-id {
    width: 65px;
}

#email-marketing-scheduled-root .custom-scheduled-table .col-sc-camp {
    width: 230px;
}

#email-marketing-scheduled-root .custom-scheduled-table .col-sc-aud {
    width: 155px;
}

#email-marketing-scheduled-root .custom-scheduled-table .col-sc-time {
    width: 160px;
}

#email-marketing-scheduled-root .custom-scheduled-table .col-sc-due {
    width: 170px;
}

#email-marketing-scheduled-root .custom-scheduled-table .col-sc-recip {
    width: 105px;
}

#email-marketing-scheduled-root .custom-scheduled-table .col-sc-action {
    width: 130px;
}

#email-marketing-scheduled-root .custom-scheduled-table .text-truncate {
    max-width: 100%;
}

#email-marketing-scheduled-root .custom-scheduled-table .font-monospace {
    font-variant-numeric: tabular-nums;
}

#email-marketing-scheduled-root .custom-scheduled-table .badge {
    font-size: 10px;
    font-weight: 600;
    line-height: 1.4;
    white-space: normal;
}

/* Action Buttons */

#email-marketing-scheduled-root .scheduled-action-btn {
    width: 30px;
    height: 30px;
    min-width: 30px;
    padding: 0 !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    border: 1px solid #e7e9ed;
    border-radius: 5px !important;
    font-size: 11px;
    transition: all .18s ease;
}

#email-marketing-scheduled-root .scheduled-action-btn:hover {
    background: #f5f7fa;
    border-color: #d1d7e0;
    transform: translateY(-1px);
}

/* Empty State */

#email-marketing-scheduled-root #emptyScheduledRow td,
#email-marketing-scheduled-root #noMatchScheduledRow td {
    padding: 45px 15px !important;
    color: var(--sc-muted);
    text-align: center;
    white-space: normal;
}

#email-marketing-scheduled-root #emptyScheduledRow i,
#email-marketing-scheduled-root #noMatchScheduledRow i {
    color: #b8c0cc;
}

/* Pagination */

#email-marketing-scheduled-root .card-footer {
    padding: 12px 15px;
    background: #fff;
    border-top: 1px solid var(--sc-border);
}

#email-marketing-scheduled-root .card-footer nav {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    margin: 0;
}

#email-marketing-scheduled-root .card-footer nav p,
#email-marketing-scheduled-root .card-footer nav .small.text-muted,
#email-marketing-scheduled-root .card-footer nav div:has(> p) {
    display: none;
}

#email-marketing-scheduled-root .card-footer .pagination {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 4px;
    margin: 0;
    padding: 0;
}

#email-marketing-scheduled-root .card-footer .page-link,
#email-marketing-scheduled-root .card-footer .page-item span {
    min-width: 30px;
    height: 30px;
    padding: 0 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--sc-secondary);
    background: #fff;
    border: 1px solid var(--sc-border);
    font-size: 11px;
    font-weight: 600;
    text-decoration: none;
}

#email-marketing-scheduled-root .card-footer .page-item.active .page-link,
#email-marketing-scheduled-root .card-footer .page-item.active span {
    color: #fff;
    background: var(--sc-primary);
    border-color: var(--sc-primary);
}

#email-marketing-scheduled-root .card-footer .page-link:hover {
    color: var(--sc-primary);
    background: var(--sc-soft-primary);
}

#email-marketing-scheduled-root .card-footer .page-item.disabled {
    opacity: .45;
}

/* Alerts */

#email-marketing-scheduled-root .alert {
    padding: 11px 14px;
    border: 1px solid transparent !important;
    font-size: 12px;
}

#email-marketing-scheduled-root .alert-success {
    background: #effaf1;
    border-color: #d5f0da !important;
}

#email-marketing-scheduled-root .alert-danger {
    background: #fff2f2;
    border-color: #f7dada !important;
}

/* Modal */

.scheduled-modal-scope .modal-content {
    overflow: hidden;
    background: #fff;
    border: 1px solid var(--sc-border) !important;
    box-shadow: 0 12px 40px rgba(0,0,0,.14);
}

.scheduled-modal-scope .modal-body {
    padding: 12px !important;
}

.scheduled-modal-scope .modal-dialog {
    max-width: 390px;
    width: calc(100% - 30px);
}

.scheduled-modal-scope h6 {
    color: #2a2f5b;
    font-size: 15px !important;
}

.scheduled-modal-scope p {
    color: #8d9498;
    font-size: 12px !important;
    line-height: 1.6 !important;
}

.scheduled-modal-scope .btn {
    min-height: 35px;
}

/* Typography */

#email-marketing-scheduled-root .bn-digit,
#email-marketing-scheduled-root .font-monospace {
    font-variant-numeric: tabular-nums;
}

#email-marketing-scheduled-root .text-muted {
    color: var(--sc-muted) !important;
}

#email-marketing-scheduled-root .text-dark {
    color: var(--sc-heading) !important;
}

/* Responsive: Large Desktop */

@media (min-width: 1400px) {
    #email-marketing-scheduled-root .metric-card {
        min-height: 112px;
        padding: 16px 18px;
    }

    #email-marketing-scheduled-root .custom-scheduled-table tbody td {
        padding: 12px 10px;
    }
}

/* Responsive: Tablet */

@media (max-width: 991.98px) {
    #email-marketing-scheduled-root .custom-scheduled-table {
        min-width: 1000px;
    }

    #email-marketing-scheduled-root .metric-card {
        min-height: 100px;
    }
}

/* Responsive: Mobile */

@media (max-width: 767.98px) {
    #email-marketing-scheduled-root {
        padding-left: 10px !important;
        padding-right: 10px !important;
    }

    #email-marketing-scheduled-root .scheduled-header-wrap {
        align-items: stretch !important;
        padding-bottom: 13px;
    }

    #email-marketing-scheduled-root .scheduled-page-title {
        font-size: 17px;
    }

    #email-marketing-scheduled-root .scheduled-page-description {
        font-size: 11px;
    }

    #email-marketing-scheduled-root .top-action-btns {
        display: grid;
        grid-template-columns: 1fr 1fr;
        width: 100%;
        gap: 7px;
    }

    #email-marketing-scheduled-root .top-action-btns .btn {
        width: 100%;
        min-height: 36px;
        padding: 7px 5px !important;
        font-size: 10.5px !important;
        white-space: normal;
        text-align: center;
    }

    #email-marketing-scheduled-root .section-header-wrap {
        padding: 10px 11px;
    }

    #email-marketing-scheduled-root .section-header-title {
        font-size: 11px;
    }

    #email-marketing-scheduled-root .metric-card {
        min-height: 95px;
        padding: 11px !important;
    }

    #email-marketing-scheduled-root .stat-label-title {
        font-size: 10px;
    }

    #email-marketing-scheduled-root .stat-val-num {
        font-size: 17px;
    }

    #email-marketing-scheduled-root .stat-sub-text {
        font-size: 9px;
    }

    #email-marketing-scheduled-root .metric-icon-square {
        width: 29px;
        height: 29px;
        min-width: 29px;
        font-size: 12px;
    }

    #email-marketing-scheduled-root .scheduled-filter-control {
        height: 38px;
        min-height: 38px;
        font-size: 11px;
    }

    #email-marketing-scheduled-root .scheduled-addon-text {
        height: 38px;
    }

    #email-marketing-scheduled-root .pc-fit-container {
        overflow-x: auto !important;
        max-width: 100%;
    }

    #email-marketing-scheduled-root .custom-scheduled-table {
        min-width: 1000px !important;
        table-layout: auto !important;
    }

    #email-marketing-scheduled-root .custom-scheduled-table thead th {
        padding: 10px 8px;
        font-size: 10px;
    }

    #email-marketing-scheduled-root .custom-scheduled-table tbody td {
        padding: 10px 8px;
        font-size: 11px;
    }

    #email-marketing-scheduled-root .scheduled-table-heading {
        padding: 11px;
    }

    #email-marketing-scheduled-root .scheduled-table-title {
        font-size: 12px;
    }

    #email-marketing-scheduled-root .card-footer {
        padding: 11px;
    }

    #email-marketing-scheduled-root .card-footer > div {
        width: 100%;
        text-align: center;
    }

    #email-marketing-scheduled-root .card-footer nav {
        justify-content: center;
    }

    #email-marketing-scheduled-root .card {
        margin-bottom: 12px;
    }

    #email-marketing-scheduled-root .alert {
        align-items: flex-start !important;
    }
}

/* Small Mobile */

@media (max-width: 400px) {
    #email-marketing-scheduled-root .top-action-btns {
        grid-template-columns: 1fr;
    }

    #email-marketing-scheduled-root .metric-card {
        padding: 9px !important;
    }

    #email-marketing-scheduled-root .stat-val-num {
        font-size: 15px;
    }

    #email-marketing-scheduled-root .stat-label-title {
        font-size: 9px;
    }

    #email-marketing-scheduled-root .section-header-title {
        font-size: 10px;
    }

    #email-marketing-scheduled-root .scheduled-total-badge {
        font-size: 9px;
    }
}

/* Reduced Motion */

@media (prefers-reduced-motion: reduce) {
    #email-marketing-scheduled-root *,
    .scheduled-modal-scope * {
        transition-duration: .01ms !important;
        animation-duration: .01ms !important;
    }
}
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-scheduled-root">

    {{-- 1. Page Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 scheduled-header-wrap">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h5 class="fw-bold mb-0 d-flex align-items-center gap-2 scheduled-page-title">
                    <i class="fas fa-calendar-check text-primary"></i>
                    <span>Scheduled Campaigns</span>
                </h5>

                <span class="badge scheduled-total-badge font-monospace" id="scheduled-total-badge">
                    {{ number_format($totalCampaignsCount) }} Queued Broadcasts
                </span>
            </div>

            <small class="d-block mt-1 scheduled-page-description">
                Monitor, reschedule, and dispatch scheduled email campaigns.
            </small>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 top-action-btns">
            <a href="{{ $allCampaignsRoute }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-paper-plane me-1"></i>
                <span>All Campaigns</span>
            </a>

            <a href="{{ $createCampaignRoute }}" class="btn btn-sm btn-primary btn-compact shadow-xs fw-bold">
                <i class="fas fa-plus-circle me-1"></i>
                <span>Schedule Campaign</span>
            </a>
        </div>
    </div>

    {{-- 2. Feedback Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3 d-flex align-items-center justify-content-between" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3 d-flex align-items-center justify-content-between" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-circle text-danger fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- 3. Overview Statistics --}}
    <div class="card mb-3">
        <div class="section-header-wrap">
            <span class="section-header-title">
                <i class="fas fa-chart-pie me-1"></i>
                Campaign Overview
            </span>

            <button type="button" class="collapse-toggle-btn shadow-xs no-print"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseScheduledKpis"
                    aria-expanded="true"
                    aria-controls="collapseScheduledKpis"
                    title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>

        <div class="collapse show" id="collapseScheduledKpis">
            <div class="p-3">
                <div class="row g-3">

                    {{-- Upcoming Campaigns --}}
                    <div class="col-6 col-xl-4">
                        <div class="card metric-card metric-card-cyan h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="stat-label-title text-truncate">Upcoming Campaigns</span>
                                <div class="metric-icon-square bg-info-subtle text-info">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>

                            <div class="min-w-0">
                                <h4 class="my-0 stat-val-num font-monospace">
                                    {{ number_format($totalCampaignsCount) }}
                                </h4>
                                <small class="d-block stat-sub-text">Awaiting scheduled dispatch</small>
                            </div>
                        </div>
                    </div>

                    {{-- Target Audience --}}
                    <div class="col-6 col-xl-4">
                        <div class="card metric-card metric-card-green h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="stat-label-title text-truncate">Target Recipients</span>
                                <div class="metric-icon-square bg-success-subtle text-success">
                                    <i class="fas fa-envelope-circle-check"></i>
                                </div>
                            </div>

                            <div class="min-w-0">
                                <h4 class="my-0 stat-val-num text-success font-monospace">
                                    {{ number_format($totalRecipientsCount) }}
                                </h4>
                                <small class="d-block stat-sub-text">
                                    <i class="fas fa-check-circle me-1 text-success"></i>
                                    Consented subscribers
                                </small>
                            </div>
                        </div>
                    </div>

                    {{-- Cron Worker --}}
                    <div class="col-12 col-xl-4">
                        <div class="card metric-card metric-card-amber h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="stat-label-title text-truncate">Automated Cron Worker</span>
                                <div class="metric-icon-square bg-warning-subtle text-warning-emphasis">
                                    <i class="fas fa-server"></i>
                                </div>
                            </div>

                            <div class="min-w-0">
                                <h4 class="my-0 stat-val-num text-warning-emphasis" style="font-size: 16px;">
                                    <i class="fas fa-arrows-spin me-1"></i>
                                    Active Pipeline
                                </h4>
                                <small class="d-block stat-sub-text">Queue checked every minute</small>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- 4. Search & Filters --}}
    <div class="card scheduled-filter-card mb-3">
        <div class="section-header-wrap">
            <span class="section-header-title">
                <i class="fas fa-filter me-1"></i>
                Search &amp; Filter Campaigns
            </span>

            <button type="button" class="collapse-toggle-btn shadow-xs no-print"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseScheduledFilter"
                    aria-expanded="true"
                    aria-controls="collapseScheduledFilter"
                    title="Toggle Filters">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>

        <div class="collapse show" id="collapseScheduledFilter">
            <div class="p-3">
                <form id="scheduledFilterForm" onsubmit="return false;">
                    <div class="row g-3 align-items-end">

                        {{-- Search --}}
                        <div class="col-12 col-md-5">
                            <label class="form-label" for="scheduledSearchInput">
                                <i class="fas fa-search me-1 text-primary"></i>
                                Search Campaign
                            </label>

                            <div class="input-group">
                                <span class="input-group-text scheduled-addon-text border-end-0">
                                    <i class="fas fa-search"></i>
                                </span>

                                <input type="text"
                                       id="scheduledSearchInput"
                                       class="form-control border-start-0 ps-0 scheduled-filter-control shadow-none"
                                       placeholder="Search name, subject, or segment..."
                                       onkeyup="filterScheduledTable()">
                            </div>
                        </div>

                        {{-- Readiness Filter --}}
                        <div class="col-6 col-md-4">
                            <label class="form-label" for="scheduledReadinessFilter">
                                <i class="fas fa-hourglass-half me-1 text-warning"></i>
                                Dispatch Readiness
                            </label>

                            <select id="scheduledReadinessFilter"
                                    class="form-select scheduled-filter-control shadow-none"
                                    onchange="filterScheduledTable()">
                                <option value="all" selected>All Scheduled Items</option>
                                <option value="upcoming">Upcoming</option>
                                <option value="due">Due for Dispatch</option>
                            </select>
                        </div>

                        {{-- Reset --}}
                        <div class="col-6 col-md-3">
                            <button type="button"
                                    class="btn btn-light border btn-compact w-100 shadow-xs fw-semibold text-secondary"
                                    onclick="resetScheduledFilters()">
                                <i class="fas fa-rotate-left me-1"></i>
                                Reset Filters
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 5. Scheduled Campaign Table --}}
    <div class="card mb-3 overflow-hidden">

        <div class="scheduled-table-heading d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">

            <div>
                <h6 class="scheduled-table-title mb-0 d-flex align-items-center gap-2">
                    <i class="fas fa-calendar-day text-primary"></i>
                    Scheduled Campaign Queue
                </h6>

                <small class="scheduled-table-subtitle font-monospace" id="showing-entries-text">
                    Showing {{ method_exists($campaigns, 'firstItem') ? ($campaigns->firstItem() ?? 0) : 1 }} to {{ method_exists($campaigns, 'lastItem') ? ($campaigns->lastItem() ?? 0) : $campaigns->count() }} of {{ number_format($totalCampaignsCount) }} campaigns
                </small>
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-secondary border font-monospace px-2 py-2"
                      style="font-size: 10px;"
                      id="visible-scheduled-count">
                    {{ number_format($totalCampaignsCount) }} Total
                </span>

                <button type="button"
                        class="collapse-toggle-btn shadow-xs no-print"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseScheduledTable"
                        aria-expanded="true"
                        aria-controls="collapseScheduledTable"
                        title="Toggle Table">
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </button>
            </div>
        </div>

        <div class="collapse show" id="collapseScheduledTable">

            <div class="table-responsive pc-fit-container" style="min-height: 250px;">
                <table class="table align-middle mb-0 custom-scheduled-table" id="scheduled-table">

                    <thead>
                        <tr>
                            <th class="ps-3 col-sc-id text-center">#ID</th>
                            <th class="col-sc-camp">Campaign &amp; Subject</th>
                            <th class="col-sc-aud">Target Audience</th>
                            <th class="col-sc-time">Scheduled Time</th>
                            <th class="col-sc-due">Countdown</th>
                            <th class="text-center col-sc-recip">Recipients</th>
                            <th class="text-end pe-3 col-sc-action">Actions</th>
                        </tr>
                    </thead>

                    <tbody id="scheduled-tbody">
                        @forelse($campaigns as $camp)
                            @php
                                $campViewUrl = Route::has('admin.email_marketing.campaigns.show') 
                                    ? route('admin.email_marketing.campaigns.show', $camp->id) 
                                    : url('admin/email-marketing/campaigns/view/' . $camp->id);

                                $campEditUrl = Route::has('admin.email_marketing.campaigns.edit') 
                                    ? route('admin.email_marketing.campaigns.edit', $camp->id) 
                                    : url('admin/email-marketing/campaigns/' . $camp->id . '/edit');

                                $campDestroyUrl = Route::has('admin.email_marketing.campaigns.destroy') 
                                    ? route('admin.email_marketing.campaigns.destroy', $camp->id) 
                                    : url('admin/email-marketing/campaigns/' . $camp->id);

                                $campDispatchNowUrl = Route::has('admin.email_marketing.campaigns.dispatch_now') 
                                    ? route('admin.email_marketing.campaigns.dispatch_now', $camp->id) 
                                    : url('admin/email-marketing/campaigns/' . $camp->id . '/dispatch-now');

                                $segmentTitle  =$camp->segment ? $camp->segment->name : 'All Subscribers';$isDue         = $camp->scheduled_at &&$camp->scheduled_at->isPast();
                                $readinessSlug =$isDue ? 'due' : 'upcoming';
                                $scheduleEpoch = $camp->scheduled_at ? $camp->scheduled_at->timestamp : 0;

                                $rowSearchData = strtolower(
                                    ($camp->name ?? '') . ' ' .
                                    ($camp->subject ?? '') . ' ' .
                                    $segmentTitle . ' ' .$readinessSlug
                                );
                            @endphp

                            <tr class="scheduled-entry-row align-middle"
                                data-search="{{ $rowSearchData }}"
                                data-readiness="{{ $readinessSlug }}"
                                data-epoch="{{ $scheduleEpoch }}">

                                {{-- Campaign ID --}}
                                <td class="ps-3 col-sc-id text-center font-monospace bn-digit text-muted" style="font-size: 11px;">
                                    {{ $camp->id }}
                                </td>

                                {{-- Campaign Information --}}
                                <td class="col-sc-camp">
                                    <div class="min-w-0" style="line-height: 1.45;">
                                        <a href="{{ $campViewUrl }}"
                                           class="fw-bold text-dark text-decoration-none d-block text-truncate"
                                           style="font-size: 12px;"
                                           title="{{ $camp->name }}">
                                            {{ $camp->name }}
                                        </a>

                                        <small class="text-muted d-block text-truncate font-monospace"
                                               style="font-size: 10px;"
                                               title="{{ $camp->subject }}">
                                            <i class="fas fa-envelope me-1 text-secondary"></i>
                                            {{ $camp->subject }}
                                        </small>
                                    </div>
                                </td>

                                {{-- Target Audience --}}
                                <td class="col-sc-aud">
                                    @if($camp->segment)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace text-truncate d-inline-block px-2 py-1"
                                              style="max-width: 100%; font-size: 9.5px;"
                                              title="{{ $camp->segment->name }}">
                                            <i class="fas fa-filter me-1"></i>
                                            {{ $camp->segment->name }}
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border font-monospace px-2 py-1"
                                              style="font-size: 9.5px;">
                                            <i class="fas fa-users me-1"></i>
                                            All Subscribers
                                        </span>
                                    @endif
                                </td>

                                {{-- Scheduled Date --}}
                                <td class="col-sc-time font-monospace bn-digit" style="line-height: 1.5;">
                                    @if($camp->scheduled_at)
                                        <span class="text-dark d-block" style="font-size: 11px;">
                                            {{ $camp->scheduled_at->format('M d, Y') }}
                                        </span>

                                        <small class="text-muted" style="font-size: 10px;">
                                            <i class="fas fa-clock me-1 text-secondary"></i>
                                            {{ $camp->scheduled_at->format('h:i A') }}
                                        </small>
                                    @else
                                        <span class="text-muted font-monospace" style="font-size: 10px;">
                                            Unset
                                        </span>
                                    @endif
                                </td>

                                {{-- Countdown --}}
                                <td class="col-sc-due font-monospace">
                                    @if($camp->scheduled_at)
                                        @if($isDue)
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace px-2 py-1 countdown-badge"
                                                  style="font-size: 9.5px;">
                                                <i class="fas fa-bolt me-1"></i>
                                                Due for dispatch
                                            </span>
                                        @else
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-1 countdown-badge"
                                                  style="font-size: 9.5px;">
                                                <i class="fas fa-hourglass-half me-1"></i>
                                                In {{ $camp->scheduled_at->diffForHumans(null, true) }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-muted font-monospace" style="font-size: 10px;">
                                            —
                                        </span>
                                    @endif
                                </td>

                                {{-- Recipients --}}
                                <td class="text-center col-sc-recip font-monospace bn-digit fw-bold text-dark"
                                    style="font-size: 11.5px;">
                                    {{ number_format($camp->total_recipients) }}
                                </td>

                                {{-- Actions --}}
                                <td class="text-end pe-3 col-sc-action">
                                    <div class="d-inline-flex align-items-center gap-1">

                                        {{-- Dispatch Immediately --}}
                                        <button type="button"
                                                class="btn btn-sm scheduled-action-btn text-success shadow-xs"
                                                onclick="triggerDispatchNowModal('{{ addslashes($camp->name) }}', '{{$campDispatchNowUrl }}')"
                                                title="Dispatch Immediately"
                                                aria-label="Dispatch Immediately">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>

                                        {{-- Edit Campaign --}}
                                        <a href="{{ $campEditUrl }}"
                                           class="btn btn-sm scheduled-action-btn text-primary shadow-xs"
                                           title="Edit Campaign"
                                           aria-label="Edit Campaign">
                                            <i class="fas fa-calendar-pen"></i>
                                        </a>

                                        {{-- Cancel Schedule --}}
                                        <button type="button"
                                                class="btn btn-sm scheduled-action-btn text-danger shadow-xs"
                                                onclick="triggerCancelScheduleModal('{{ addslashes($camp->name) }}', '{{$campDestroyUrl }}')"
                                                title="Cancel Schedule"
                                                aria-label="Cancel Schedule">
                                            <i class="fas fa-trash-can"></i>
                                        </button>

                                    </div>
                                </td>
                            </tr>

                        @empty
                            <tr id="emptyScheduledRow">
                                <td colspan="7" class="text-center py-5 text-muted small">
                                    <i class="fas fa-calendar-xmark fa-2x mb-2 d-block text-secondary opacity-25"></i>
                                    <span class="fw-semibold text-dark d-block mb-1">
                                        No Scheduled Campaigns
                                    </span>
                                    <span>
                                        Scheduled campaigns will appear here.
                                    </span>
                                </td>
                            </tr>
                        @endforelse

                        <tr id="noMatchScheduledRow" style="display: none;">
                            <td colspan="7" class="text-center py-5 text-muted small">
                                <i class="fas fa-filter-circle-xmark fa-2x mb-2 d-block text-secondary opacity-25"></i>
                                <span class="fw-semibold">
                                    No campaigns match your filters.
                                </span>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if(method_exists($campaigns, 'hasPages') &&$campaigns->hasPages())
                <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-white card-footer no-print">

                    <small class="text-muted font-monospace bn-digit" style="font-size: 11px;">
                        Showing {{ $campaigns->firstItem() }} to {{ $campaigns->lastItem() }} of {{$campaigns->total() }} campaigns
                    </small>

                    <div>
                        {{ $campaigns->links('pagination::bootstrap-5') }}
                    </div>

                </div>
            @endif

        </div>
    </div>
</div>

{{-- MODAL 1: Instant Dispatch Confirmation --}}
<div class="modal fade scheduled-modal-scope"
     id="instantDispatchModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index: 106500;">

    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">

            <div class="modal-body p-1">

                <div class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mx-auto mb-3"
                     style="width: 48px; height: 48px; border-radius: 50% !important;">
                    <i class="fas fa-paper-plane fs-4"></i>
                </div>

                <h6 class="fw-bold text-dark mb-2">
                    Dispatch Campaign Now?
                </h6>

                <p class="text-muted small mb-3">
                    Send <strong id="dispatchCampaignName" class="text-dark">this campaign</strong>
                    to all target recipients immediately?
                </p>

                <form id="instantDispatchForm"
                      method="POST"
                      action=""
                      onsubmit="lockSubmitBtn(this, 'Dispatching...')">

                    @csrf

                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <button type="button"
                                class="btn btn-light btn-compact border"
                                data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button type="submit"
                                class="btn btn-success btn-compact shadow-xs fw-bold text-white submit-btn">
                            <i class="fas fa-paper-plane me-1"></i>
                            Yes, Send Now
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

{{-- MODAL 2: Cancel / Delete Schedule Confirmation --}}
<div class="modal fade scheduled-modal-scope"
     id="cancelScheduleModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index: 106500;">

    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">

            <div class="modal-body p-1">

                <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-3"
                     style="width: 48px; height: 48px; border-radius: 50% !important;">
                    <i class="fas fa-trash-can fs-4"></i>
                </div>

                <h6 class="fw-bold text-dark mb-2">
                    Cancel Scheduled Campaign?
                </h6>

                <p class="text-muted small mb-3">
                    Are you sure you want to remove
                    <strong id="cancelCampaignName" class="text-dark">this campaign</strong>
                    from the schedule?
                </p>

                <form id="cancelScheduleForm"
                      method="POST"
                      action=""
                      onsubmit="lockSubmitBtn(this, 'Cancelling...')">

                    @csrf
                    @method('DELETE')

                    <div class="d-flex justify-content-center gap-2 flex-wrap">

                        <button type="button"
                                class="btn btn-light btn-compact border"
                                data-bs-dismiss="modal">
                            Keep Scheduled
                        </button>

                        <button type="submit"
                                class="btn btn-danger btn-compact shadow-xs fw-bold submit-btn">
                            <i class="fas fa-trash-can me-1"></i>
                            Yes, Cancel
                        </button>

                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function triggerDispatchNowModal(campaignName, actionUrl) {
        document.getElementById('dispatchCampaignName').innerText = campaignName;
        document.getElementById('instantDispatchForm').action = actionUrl;
        const modalEl = document.getElementById('instantDispatchModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function triggerCancelScheduleModal(campaignName, actionUrl) {
        document.getElementById('cancelCampaignName').innerText = campaignName;
        document.getElementById('cancelScheduleForm').action = actionUrl;
        const modalEl = document.getElementById('cancelScheduleModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function lockSubmitBtn(form, loadingText) {
        const btn = form.querySelector('.submit-btn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `<i class="fas fa-spinner fa-spin me-1"></i> ${loadingText}`;
        }
    }

    function filterScheduledTable() {
        const query     = (document.getElementById('scheduledSearchInput')?.value || '').toLowerCase().trim();
        const readiness = (document.getElementById('scheduledReadinessFilter')?.value || 'all').toLowerCase();

        const rows = document.querySelectorAll('.scheduled-entry-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const searchData    = row.getAttribute('data-search') || '';
            const itemReadiness = row.getAttribute('data-readiness') || '';

            const matchesQuery     = !query || searchData.includes(query);
            const matchesReadiness = readiness === 'all' || itemReadiness === readiness;

            if (matchesQuery && matchesReadiness) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const countBadge = document.getElementById('visible-scheduled-count');
        if (countBadge) {
            countBadge.innerText = `${visibleCount} Showing`;
        }

        const noMatchRow = document.getElementById('noMatchScheduledRow');
        if (noMatchRow) {
            noMatchRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
        }
    }

    function resetScheduledFilters() {
        const searchInput     = document.getElementById('scheduledSearchInput');
        const readinessFilter = document.getElementById('scheduledReadinessFilter');
        if (searchInput)     searchInput.value = '';
        if (readinessFilter) readinessFilter.value = 'all';
        filterScheduledTable();
    }

    // Live countdown refresh interval (updates diff every minute without page refresh)
    setInterval(() => {
        const now = Math.floor(Date.now() / 1000);
        document.querySelectorAll('.scheduled-entry-row').forEach(row => {
            const epoch = parseInt(row.getAttribute('data-epoch') || '0', 10);
            if (!epoch) return;

            const badge = row.querySelector('.countdown-badge');
            if (!badge) return;

            const diff = epoch - now;
            if (diff <= 0) {
                row.setAttribute('data-readiness', 'due');
                badge.className = 'badge bg-danger-subtle text-danger border border-danger-subtle font-monospace px-2 py-1 countdown-badge';
                badge.innerHTML = '<i class="fas fa-bolt me-1"></i> Due for dispatch';
            }
        });
    }, 60000);
</script>
@endsection
