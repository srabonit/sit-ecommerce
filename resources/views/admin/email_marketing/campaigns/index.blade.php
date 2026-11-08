
@extends('layouts.admin')

@section('title', 'Campaigns - Email Marketing')

@php
    $campaigns = $campaigns ?? collect();
    $totalCampaignsCount = method_exists($campaigns, 'total') ? $campaigns->total() : $campaigns->count();

    $hasCampaignModel = class_exists('\App\Models\EmailCampaign');
    $activeCount      = $hasCampaignModel ? \App\Models\EmailCampaign::whereIn('status', ['sending', 'queued', 'scheduled'])->count() : $campaigns->whereIn('status', ['sending', 'queued', 'scheduled'])->count();
    $completedCount   = $hasCampaignModel ? \App\Models\EmailCampaign::where('status', 'completed')->count() : $campaigns->where('status', 'completed')->count();
    $totalRevenueSum  = $hasCampaignModel ? (float) \App\Models\EmailCampaign::sum('total_revenue') : (float) $campaigns->sum('total_revenue');

    $dashboardRoute = Route::has('admin.email_marketing.dashboard')
        ? route('admin.email_marketing.dashboard')
        : url('admin/email-marketing/dashboard');

    $createCampaignRoute = Route::has('admin.email_marketing.campaigns.create')
        ? route('admin.email_marketing.campaigns.create')
        : url('admin/email-marketing/campaigns/create');
@endphp

@section('admin_content')

<style>
    /* =========================================================
       EMAIL MARKETING CAMPAIGNS
       Kaiadmin Lite v1.2.0 - Classic / White Classic
       Scoped styles only
    ========================================================= */

    #email-marketing-campaigns-root {
        --campaign-primary: #1572e8;
        --campaign-primary-soft: #eaf2ff;
        --campaign-success: #31ce36;
        --campaign-warning: #ffad46;
        --campaign-danger: #f25961;
        --campaign-info: #48abf7;
        --campaign-heading: #1a2035;
        --campaign-text: #495057;
        --campaign-muted: #8d9498;
        --campaign-border: #e6e9ed;
        --campaign-surface: #ffffff;
        --campaign-background: #f7f8fa;
        color: var(--campaign-text);
        font-size: 13px;
    }

    #email-marketing-campaigns-root *,
    .campaigns-modal-scope * {
        box-sizing: border-box;
    }

    #email-marketing-campaigns-root .card,
    #email-marketing-campaigns-root .btn,
    #email-marketing-campaigns-root .badge,
    #email-marketing-campaigns-root .alert,
    #email-marketing-campaigns-root .form-control,
    #email-marketing-campaigns-root .form-select,
    #email-marketing-campaigns-root .input-group-text,
    #email-marketing-campaigns-root .page-link,
    #email-marketing-campaigns-root .collapse-toggle-btn,
    #email-marketing-campaigns-root .progress,
    .campaigns-modal-scope .modal-content,
    .campaigns-modal-scope .btn,
    .admin-campaign-toast {
        border-radius: 5px !important;
    }

    #email-marketing-campaigns-root .card {
        border: 1px solid var(--campaign-border);
        background: var(--campaign-surface);
        box-shadow: 0 1px 3px rgba(0, 0, 0, .035);
        margin-bottom: 16px;
    }

    #email-marketing-campaigns-root .campaign-header-wrap {
        margin-bottom: 20px !important;
    }

    #email-marketing-campaigns-root .page-heading {
        color: var(--campaign-heading);
        font-size: 19px;
        font-weight: 700;
        letter-spacing: -.3px;
        line-height: 1.35;
    }

    #email-marketing-campaigns-root .page-description {
        color: var(--campaign-muted);
        font-size: 12px;
        line-height: 1.5;
    }

    #email-marketing-campaigns-root .btn-compact {
        min-height: 34px;
        padding: 7px 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        font-size: 11.5px;
        line-height: 1.2;
        font-weight: 600;
        white-space: nowrap;
        transition: background-color .15s ease, border-color .15s ease, box-shadow .15s ease;
    }

    #email-marketing-campaigns-root .btn-primary {
        background-color: var(--campaign-primary);
        border-color: var(--campaign-primary);
    }

    #email-marketing-campaigns-root .btn-primary:hover {
        background-color: #1265cf;
        border-color: #1265cf;
    }

    #email-marketing-campaigns-root .top-action-btns {
        gap: 8px !important;
    }

    #email-marketing-campaigns-root .section-header-wrap {
        min-height: 44px;
        padding: 10px 15px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        background: #fff;
        border-bottom: 1px solid var(--campaign-border);
    }

    #email-marketing-campaigns-root .section-heading {
        color: var(--campaign-heading);
        font-size: 12px;
        font-weight: 700;
        line-height: 1.4;
    }

    #email-marketing-campaigns-root .section-heading i {
        color: var(--campaign-primary);
    }

    #email-marketing-campaigns-root .collapse-toggle-btn {
        width: 27px;
        height: 27px;
        padding: 0;
        flex: 0 0 27px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid var(--campaign-border);
        color: #6c757d;
        transition: all .2s ease;
    }

    #email-marketing-campaigns-root .collapse-toggle-btn:hover {
        color: var(--campaign-primary);
        border-color: var(--campaign-primary);
        background: var(--campaign-primary-soft);
    }

    #email-marketing-campaigns-root .collapse-icon {
        font-size: 10px;
        transition: transform .2s ease;
    }

    #email-marketing-campaigns-root .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    /* KPI cards */
    #email-marketing-campaigns-root .metric-card {
        position: relative;
        min-height: 112px;
        height: 100%;
        padding: 14px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        overflow: hidden;
        border: 1px solid var(--campaign-border);
        border-left: 3px solid var(--campaign-primary);
        background: #fff;
        transition: box-shadow .18s ease, transform .18s ease;
    }

    #email-marketing-campaigns-root .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 14px rgba(0, 0, 0, .06);
    }

    #email-marketing-campaigns-root .metric-card-blue {
        border-left-color: #1572e8;
    }

    #email-marketing-campaigns-root .metric-card-amber {
        border-left-color: #ffad46;
    }

    #email-marketing-campaigns-root .metric-card-green {
        border-left-color: #31ce36;
    }

    #email-marketing-campaigns-root .metric-card-emerald {
        border-left-color: #48abf7;
    }

    #email-marketing-campaigns-root .stat-label-title {
        color: #7d8790;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .1px;
        line-height: 1.35;
    }

    #email-marketing-campaigns-root .stat-val-num {
        color: var(--campaign-heading);
        font-size: 21px;
        font-weight: 700;
        line-height: 1.35;
        letter-spacing: -.4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #email-marketing-campaigns-root .stat-sub-text {
        margin-top: 3px;
        color: #a1a8ae;
        font-size: 10px;
        line-height: 1.4;
    }

    #email-marketing-campaigns-root .metric-icon-square {
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px !important;
        font-size: 13px;
    }

    /* Filter area */
    #email-marketing-campaigns-root .filter-card {
        background: #fff;
    }

    #email-marketing-campaigns-root .form-label {
        display: block;
        margin-bottom: 6px;
        color: #495057;
        font-size: 11.5px;
        font-weight: 600;
    }

    #email-marketing-campaigns-root .form-control,
    #email-marketing-campaigns-root .form-select {
        min-height: 37px;
        border: 1px solid #e0e3e7;
        background-color: #fff;
        color: #495057;
        font-size: 12px;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #email-marketing-campaigns-root .form-control:focus,
    #email-marketing-campaigns-root .form-select:focus {
        border-color: #1572e8;
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .10);
    }

    #email-marketing-campaigns-root .campaign-filter-control {
        height: 37px;
        padding: 7px 10px;
        font-size: 12px;
    }

    #email-marketing-campaigns-root .campaign-addon-text {
        min-width: 36px;
        height: 37px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f7f8fa;
        border: 1px solid #e0e3e7;
        color: #8d9498;
        font-size: 12px;
    }

    #email-marketing-campaigns-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    #email-marketing-campaigns-root .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Table */
    #email-marketing-campaigns-root .table-card-header {
        min-height: 58px;
        padding: 11px 15px;
        background: #fff;
        border-bottom: 1px solid var(--campaign-border);
    }

    #email-marketing-campaigns-root .table-title {
        margin: 0;
        color: var(--campaign-heading);
        font-size: 13px;
        font-weight: 700;
        line-height: 1.4;
    }

    #email-marketing-campaigns-root .table-subtitle {
        display: block;
        margin-top: 3px;
        color: #9ba2a8;
        font-size: 10.5px;
        line-height: 1.4;
    }

    #email-marketing-campaigns-root .pc-fit-container {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    #email-marketing-campaigns-root .custom-campaigns-table {
        width: 100%;
        min-width: 1120px;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    #email-marketing-campaigns-root .custom-campaigns-table thead th {
        padding: 11px 9px;
        color: #7d8790;
        background: #f8f9fb;
        border-bottom: 1px solid #e6e9ed;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .35px;
        text-transform: uppercase;
        white-space: nowrap;
        vertical-align: middle;
    }

    #email-marketing-campaigns-root .custom-campaigns-table tbody td {
        padding: 11px 9px;
        color: #495057;
        background: #fff;
        border-bottom: 1px solid #edf0f2;
        font-size: 11.5px;
        vertical-align: middle;
    }

    #email-marketing-campaigns-root .custom-campaigns-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #email-marketing-campaigns-root .custom-campaigns-table tbody tr:hover td {
        background: #fbfcfe;
    }

    #email-marketing-campaigns-root .custom-campaigns-table .col-cp-id {
        width: 55px;
    }

    #email-marketing-campaigns-root .custom-campaigns-table .col-cp-name {
        min-width: 190px;
    }

    #email-marketing-campaigns-root .custom-campaigns-table .col-cp-target {
        min-width: 135px;
    }

    #email-marketing-campaigns-root .custom-campaigns-table .col-cp-status {
        min-width: 100px;
    }

    #email-marketing-campaigns-root .custom-campaigns-table .col-cp-recip {
        min-width: 155px;
    }

    #email-marketing-campaigns-root .custom-campaigns-table .col-cp-open,
    #email-marketing-campaigns-root .custom-campaigns-table .col-cp-click {
        min-width: 80px;
    }

    #email-marketing-campaigns-root .custom-campaigns-table .col-cp-rev {
        min-width: 125px;
    }

    #email-marketing-campaigns-root .custom-campaigns-table .col-cp-actions {
        min-width: 180px;
    }

    #email-marketing-campaigns-root .campaign-entry-row .badge {
        font-size: 9.5px;
        font-weight: 600;
        letter-spacing: .1px;
    }

    #email-marketing-campaigns-root .campaign-entry-row .progress {
        height: 5px;
        overflow: hidden;
        background: #edf0f3;
    }

    #email-marketing-campaigns-root .campaign-entry-row .progress-bar {
        border-radius: 5px;
    }

    #email-marketing-campaigns-root .campaign-entry-row .btn {
        width: 28px;
        height: 28px;
        padding: 0 !important;
        border: 1px solid #e6e9ed;
        background: #fff;
        border-radius: 5px !important;
        transition: all .15s ease;
    }

    #email-marketing-campaigns-root .campaign-entry-row .btn:hover {
        background: #f4f7fb;
        border-color: #cbd5e1;
        transform: translateY(-1px);
    }

    /* Pagination */
    #campaigns-pagination-links nav {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        width: auto !important;
        margin: 0 !important;
    }

    #campaigns-pagination-links nav > div.d-sm-none,
    #campaigns-pagination-links nav > .d-sm-none,
    #campaigns-pagination-links nav p,
    #campaigns-pagination-links nav .small.text-muted,
    #campaigns-pagination-links nav div:has(> p) {
        display: none !important;
    }

    #campaigns-pagination-links .pagination {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 4px !important;
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
    }

    #campaigns-pagination-links .page-item {
        display: inline-flex !important;
    }

    #campaigns-pagination-links .page-item .page-link,
    #campaigns-pagination-links .page-item span {
        min-width: 28px !important;
        height: 28px !important;
        padding: 0 7px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        color: #6c757d !important;
        background: #fff !important;
        border: 1px solid #e1e5ea !important;
        border-radius: 5px !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        line-height: 1 !important;
        text-decoration: none !important;
    }

    #campaigns-pagination-links .page-item .page-link:hover {
        color: var(--campaign-primary) !important;
        background: #f3f7ff !important;
        border-color: #b8d2f7 !important;
    }

    #campaigns-pagination-links .page-item.active .page-link,
    #campaigns-pagination-links .page-item.active span {
        color: #fff !important;
        background: var(--campaign-primary) !important;
        border-color: var(--campaign-primary) !important;
    }

    #campaigns-pagination-links .page-item.disabled .page-link,
    #campaigns-pagination-links .page-item.disabled span {
        opacity: .45;
        cursor: not-allowed;
    }

    /* Modal */
    .campaigns-modal-scope .modal-content {
        overflow: hidden;
        border: 1px solid #e6e9ed !important;
        box-shadow: 0 12px 35px rgba(0, 0, 0, .13) !important;
    }

    .campaigns-modal-scope .modal-header,
    .campaigns-modal-scope .modal-footer {
        background: #f8f9fb;
        border-color: #edf0f2;
    }

    .campaigns-modal-scope .modal-title {
        color: #1a2035;
        font-size: 13.5px;
        font-weight: 700;
    }

    .campaigns-modal-scope .modal-body {
        background: #fff;
    }

    .campaigns-modal-scope .form-control {
        min-height: 38px;
        border: 1px solid #e0e3e7;
        border-radius: 5px !important;
        font-size: 12px;
    }

    .campaigns-modal-scope .form-control:focus {
        border-color: #1572e8;
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .10);
    }

    .campaigns-modal-scope .btn-compact {
        min-height: 33px;
        padding: 7px 12px;
        font-size: 11.5px;
        font-weight: 600;
    }

    /* Toast */
    .admin-campaign-toast {
        position: fixed;
        right: 20px;
        bottom: 20px;
        z-index: 108000;
        min-width: 280px;
        max-width: min(420px, calc(100vw - 30px));
        box-shadow: 0 8px 25px rgba(0, 0, 0, .16);
        animation: campaignToastIn .25s ease;
    }

    @keyframes campaignToastIn {
        from { transform: translateX(25px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    .admin-campaign-toast button {
        flex: 0 0 auto;
    }

    /* Utility typography */
    #email-marketing-campaigns-root .bn-digit,
    #email-marketing-campaigns-root .font-monospace {
        font-variant-numeric: tabular-nums;
    }

    /* Tablet */
    @media (max-width: 991.98px) {
        #email-marketing-campaigns-root .metric-card {
            min-height: 105px;
        }

        #email-marketing-campaigns-root .stat-val-num {
            font-size: 19px;
        }
    }

    /* Mobile */
    @media (max-width: 767.98px) {
        #email-marketing-campaigns-root {
            padding-top: 14px !important;
        }

        #email-marketing-campaigns-root .campaign-header-wrap {
            align-items: flex-start !important;
            gap: 12px !important;
        }

        #email-marketing-campaigns-root .page-heading {
            font-size: 17px;
        }

        #email-marketing-campaigns-root .page-description {
            max-width: 100%;
            font-size: 11px;
        }

        #email-marketing-campaigns-root .top-action-btns {
            width: 100%;
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            gap: 7px !important;
        }

        #email-marketing-campaigns-root .top-action-btns .btn {
            width: 100%;
            min-height: 36px;
            padding: 7px 6px;
            font-size: 11px;
        }

        #email-marketing-campaigns-root .top-action-btns .btn-create-main {
            grid-column: 1 / -1;
        }

        #email-marketing-campaigns-root .section-header-wrap {
            padding: 10px 12px;
        }

        #email-marketing-campaigns-root .metric-card {
            min-height: 94px;
            padding: 10px;
        }

        #email-marketing-campaigns-root .stat-label-title {
            font-size: 10px;
        }

        #email-marketing-campaigns-root .stat-val-num {
            font-size: 16px;
        }

        #email-marketing-campaigns-root .stat-sub-text {
            font-size: 9px;
        }

        #email-marketing-campaigns-root .metric-icon-square {
            width: 28px;
            height: 28px;
            flex-basis: 28px;
            font-size: 11px;
        }

        #email-marketing-campaigns-root .filter-card .card-body,
        #email-marketing-campaigns-root .filter-card .p-3 {
            padding: 12px !important;
        }

        #email-marketing-campaigns-root .table-card-header {
            padding: 11px 12px;
        }

        #email-marketing-campaigns-root .custom-campaigns-table {
            min-width: 1120px;
        }

        #email-marketing-campaigns-root .card {
            margin-bottom: 12px;
        }

        #campaigns-pagination-footer {
            padding: 11px 12px !important;
        }

        #campaigns-pagination-links nav {
            justify-content: center !important;
        }

        .admin-campaign-toast {
            right: 12px;
            bottom: 12px;
            left: 12px;
            min-width: 0;
            max-width: none;
        }
    }

    @media (max-width: 380px) {
        #email-marketing-campaigns-root .stat-val-num {
            font-size: 14px;
        }

        #email-marketing-campaigns-root .metric-card {
            padding: 9px;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-campaigns-root">

    {{-- 1. Header & Actions --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 campaign-header-wrap">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2 page-heading">
                    <i class="fas fa-paper-plane text-primary"></i>
                    <span>Marketing Campaigns</span>
                </h5>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 font-monospace" id="campaigns-total-badge" style="font-size: 10px;">
                    {{ number_format($totalCampaignsCount) }} Campaigns
                </span>
            </div>
            <small class="d-block mt-1 page-description">
                Create, schedule, dispatch, and track promotional and segment-targeted email broadcasts.
            </small>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 top-action-btns">
            <a href="{{ $dashboardRoute }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-chart-pie me-1"></i> <span>Dashboard</span>
            </a>
            <a href="{{ $createCampaignRoute }}" class="btn btn-sm btn-primary btn-compact shadow-xs fw-bold btn-create-main">
                <i class="fas fa-plus-circle me-1"></i> <span>Create Campaign</span>
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

    {{-- 3. Overview KPI Summary Cards --}}
    <div class="card mb-3">
        <div class="section-header-wrap">
            <span class="section-heading">
                <i class="fas fa-chart-pie me-1"></i> Campaign Portfolio &amp; Dispatch Overview
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseCampaignsKpis" aria-expanded="true" aria-controls="collapseCampaignsKpis" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>

        <div class="collapse show" id="collapseCampaignsKpis">
            <div class="p-3">
                <div class="row g-3">

                    {{-- Total Broadcasts --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-blue h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="stat-label-title text-truncate">Total Campaigns</span>
                                <div class="metric-icon-square bg-primary-subtle text-primary">
                                    <i class="fas fa-paper-plane"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="my-0 stat-val-num" id="stat-total-campaigns">
                                    {{ number_format($totalCampaignsCount) }}
                                </h4>
                                <small class="d-block text-truncate stat-sub-text">Configured broadcasts</small>
                            </div>
                        </div>
                    </div>

                    {{-- Active / In-Queue --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-amber h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="stat-label-title text-truncate">Active Engine</span>
                                <div class="metric-icon-square bg-warning-subtle text-warning-emphasis">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="my-0 text-warning-emphasis stat-val-num" id="stat-active-engine">
                                    {{ number_format($activeCount) }}
                                </h4>
                                <small class="d-block text-truncate stat-sub-text">Sending or scheduled</small>
                            </div>
                        </div>
                    </div>

                    {{-- Completed Broadcasts --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-green h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="stat-label-title text-truncate">Completed</span>
                                <div class="metric-icon-square bg-success-subtle text-success">
                                    <i class="fas fa-circle-check"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="my-0 text-success stat-val-num" id="stat-completed-campaigns">
                                    {{ number_format($completedCount) }}
                                </h4>
                                <small class="d-block text-truncate stat-sub-text">Successfully dispatched</small>
                            </div>
                        </div>
                    </div>

                    {{-- Attributed Revenue --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-emerald h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="stat-label-title text-truncate">Attributed Sales</span>
                                <div class="metric-icon-square bg-info-subtle text-info">
                                    <i class="fas fa-coins"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="my-0 text-success stat-val-num" id="stat-attributed-sales">
                                    ৳ {{ number_format($totalRevenueSum, 2) }}
                                </h4>
                                <small class="d-block text-truncate stat-sub-text">Direct conversions</small>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- 4. Status Filter & Live Search Toolbar --}}
    <div class="card filter-card mb-3">
        <div class="section-header-wrap">
            <span class="section-heading">
                <i class="fas fa-filter me-1"></i> Filter &amp; Search Campaigns
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseCampaignFilter" aria-expanded="true" aria-controls="collapseCampaignFilter" title="Toggle Filters">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>

        <div class="collapse show" id="collapseCampaignFilter">
            <div class="p-3">
                <form id="campaignFilterForm" onsubmit="return false;">
                    <div class="row g-3 align-items-end">

                        {{-- Instant Live Search Input --}}
                        <div class="col-12 col-md-5">
                            <label class="form-label" for="campaignSearchInput">
                                <i class="fas fa-search me-1 text-primary"></i> Search Campaign Name, Subject or Audience
                            </label>
                            <div class="input-group">
                                <span class="input-group-text campaign-addon-text border-end-0">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" name="search" id="campaignSearchInput" class="form-control border-start-0 ps-0 campaign-filter-control shadow-none font-monospace" placeholder="Black Friday, Welcome Offer, VIP Special...">
                            </div>
                        </div>

                        {{-- Status Dropdown --}}
                        <div class="col-6 col-md-4">
                            <label class="form-label" for="campaignStatusSelect">
                                <i class="fas fa-toggle-on me-1 text-success"></i> Campaign Status
                            </label>
                            <select name="status" id="campaignStatusSelect" class="form-select campaign-filter-control shadow-none font-monospace filter-trigger">
                                <option value="">-- All Campaign Statuses --</option>
                                <option value="draft">Draft</option>
                                <option value="scheduled">Scheduled</option>
                                <option value="queued">Queued / In Queue</option>
                                <option value="sending">Sending Now</option>
                                <option value="completed">Completed</option>
                                <option value="paused">Paused</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>

                        {{-- Reset Button --}}
                        <div class="col-6 col-md-3">
                            <button type="button" class="btn btn-light border btn-compact w-100 shadow-xs fw-semibold text-secondary" onclick="resetCampaignFilters()">
                                <i class="fas fa-rotate-left me-1"></i> <span>Reset Filters</span>
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 5. Campaigns Table --}}
    <div class="card mb-3 overflow-hidden">
        <div class="table-card-header d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
            <div>
                <h6 class="table-title">
                    <i class="fas fa-envelopes-bulk text-primary me-1"></i>
                    <span>Broadcast Campaigns &amp; Delivery Matrix</span>
                </h6>
                <small class="table-subtitle font-monospace bn-digit" id="showing-entries-text">
                    Showing {{ $campaigns->firstItem() ?? 0 }} to {{ $campaigns->lastItem() ?? 0 }} of {{ number_format($campaigns->total()) }} campaigns
                </small>
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-secondary border font-monospace px-2 py-2" style="font-size: 10px;" id="visible-campaign-count">
                    {{ number_format($campaigns->total()) }} Total
                </span>
                <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseCampaignsTable" aria-expanded="true" aria-controls="collapseCampaignsTable" title="Toggle Table">
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </button>
            </div>
        </div>

        <div class="collapse show" id="collapseCampaignsTable">
            <div class="table-responsive pc-fit-container" style="min-height: 250px;">
                <table class="table align-middle mb-0 custom-campaigns-table" id="campaigns-table">
                    <thead>
                        <tr>
                            <th class="ps-3 col-cp-id text-center">#</th>
                            <th class="col-cp-name">Campaign &amp; Subject</th>
                            <th class="col-cp-target">Target Audience</th>
                            <th class="col-cp-status">Status</th>
                            <th class="col-cp-recip">Delivery Progress</th>
                            <th class="text-center col-cp-open">Open %</th>
                            <th class="text-center col-cp-click">Click %</th>
                            <th class="col-cp-rev">Attributed Revenue</th>
                            <th class="text-end pe-3 col-cp-actions">Actions</th>
                        </tr>
                    </thead>

                    <tbody id="campaigns-tbody">
                        @forelse($campaigns as $camp)
                            @php
                                $badgeClass = match($camp->status) {
                                    'completed'         => 'bg-success-subtle text-success border border-success-subtle',
                                    'sending', 'queued' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                    'scheduled'         => 'bg-info-subtle text-info border border-info-subtle',
                                    'paused'            => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                    'cancelled'         => 'bg-danger-subtle text-danger border border-danger-subtle',
                                    default             => 'bg-light text-dark border',
                                };

                                $rowSearchData = strtolower(
                                    ($camp->name ?? '') . ' ' .
                                    ($camp->subject ?? '') . ' ' .
                                    ($camp->segment->name ?? 'all consented users') . ' ' .
                                    ($camp->status ?? '')
                                );

                                $progress = (float)($camp->progress_percentage ?? 0.0);
                                if ($camp->status === 'completed') {
                                    $progress = 100.0;
                                }

                                $showCampaignUrl = Route::has('admin.email_marketing.campaigns.show')
                                    ? route('admin.email_marketing.campaigns.show', $camp->id)
                                    : url('admin/email-marketing/campaigns/view/' . $camp->id);

                                $editCampaignUrl = Route::has('admin.email_marketing.campaigns.edit')
                                    ? route('admin.email_marketing.campaigns.edit', $camp->id)
                                    : url('admin/email-marketing/campaigns/' . $camp->id . '/edit');

                                $dispatchNowUrl = Route::has('admin.email_marketing.campaigns.dispatch_now')
                                    ? route('admin.email_marketing.campaigns.dispatch_now', $camp->id)
                                    : url('admin/email-marketing/campaigns/dispatch-now/' . $camp->id);

                                $pauseCampaignUrl = Route::has('admin.email_marketing.campaigns.pause')
                                    ? route('admin.email_marketing.campaigns.pause', $camp->id)
                                    : url('admin/email-marketing/campaigns/pause/' . $camp->id);

                                $resumeCampaignUrl = Route::has('admin.email_marketing.campaigns.resume')
                                    ? route('admin.email_marketing.campaigns.resume', $camp->id)
                                    : url('admin/email-marketing/campaigns/resume/' . $camp->id);

                                $testSendUrl = Route::has('admin.email_marketing.campaigns.send_test')
                                    ? route('admin.email_marketing.campaigns.send_test', $camp->id)
                                    : url('admin/email-marketing/campaigns/send-test/' . $camp->id);

                                $deleteCampaignUrl = Route::has('admin.email_marketing.campaigns.destroy')
                                    ? route('admin.email_marketing.campaigns.destroy', $camp->id)
                                    : url('admin/email-marketing/campaigns/delete/' . $camp->id);
                            @endphp

                            <tr class="campaign-entry-row align-middle" id="campaign-row-{{ $camp->id }}" data-search="{{ $rowSearchData }}" data-status="{{ strtolower($camp->status) }}">

                                <td class="ps-3 col-cp-id text-center font-monospace bn-digit text-muted" style="font-size: 11px;">
                                    {{ $camp->id }}
                                </td>

                                <td class="col-cp-name">
                                    <div class="min-w-0" style="line-height: 1.4;">
                                        <a href="{{ $showCampaignUrl }}" class="fw-bold text-dark text-decoration-none d-block text-truncate" style="font-size: 12px;" title="{{ $camp->name }}">
                                            {{ $camp->name }}
                                        </a>
                                        <small class="text-muted d-block text-truncate font-monospace" style="font-size: 10px;" title="{{ $camp->subject }}">
                                            <i class="fas fa-envelope me-1 text-secondary"></i> {{ $camp->subject }}
                                        </small>
                                    </div>
                                </td>

                                <td class="col-cp-target">
                                    @if($camp->segment)
                                        <span class="badge bg-light text-dark border font-monospace text-truncate d-inline-block px-2 py-1" style="font-size: 10px; max-width: 100%;" title="{{ $camp->segment->name }}">
                                            <i class="fas fa-filter me-1 text-primary"></i> {{ $camp->segment->name }}
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border font-monospace text-truncate d-inline-block px-2 py-1" style="font-size: 10px; max-width: 100%;">
                                            <i class="fas fa-users me-1"></i> All Consented Users
                                        </span>
                                    @endif
                                </td>

                                <td class="col-cp-status">
                                    <span class="badge {{ $badgeClass }} font-monospace px-2 py-1" style="font-size: 9.5px;">
                                        @if(in_array($camp->status, ['sending', 'queued']))
                                            <i class="fas fa-spinner fa-spin me-1"></i>
                                        @endif
                                        {{ strtoupper($camp->status) }}
                                    </span>
                                </td>

                                <td class="col-cp-recip">
                                    <div class="d-flex justify-content-between align-items-center font-monospace bn-digit mb-1" style="font-size: 10.5px;">
                                        <span class="fw-bold text-dark">{{ number_format($camp->sent_count ?? 0) }}/{{ number_format($camp->total_recipients ?? 0) }}</span>
                                        <span class="text-muted">{{ round($progress, 1) }}%</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar {{ $camp->status === 'completed' ? 'bg-success' : 'bg-primary progress-bar-striped progress-bar-animated' }}"
                                             role="progressbar"
                                             style="width: {{ $progress }}%;"
                                             aria-valuenow="{{ $progress }}"
                                             aria-valuemin="0"
                                             aria-valuemax="100"></div>
                                    </div>
                                </td>

                                <td class="text-center col-cp-open font-monospace bn-digit fw-semibold text-success" style="font-size: 11.5px;">
                                    <div>{{ $camp->open_rate }}%</div>
                                    <small class="text-muted d-block" style="font-size: 9px; font-weight: normal;">{{ number_format($camp->opened_count ?? 0) }} opens</small>
                                </td>

                                <td class="text-center col-cp-click font-monospace bn-digit fw-semibold text-primary" style="font-size: 11.5px;">
                                    <div>{{ $camp->click_rate }}%</div>
                                    <small class="text-muted d-block" style="font-size: 9px; font-weight: normal;">{{ number_format($camp->clicked_count ?? 0) }} clicks</small>
                                </td>

                                <td class="col-cp-rev font-monospace bn-digit fw-bold text-dark" style="font-size: 11.5px;">
                                    ৳ {{ number_format($camp->total_revenue ?? 0, 2) }}
                                </td>

                                <td class="text-end pe-3 col-cp-actions">
                                    <div class="d-inline-flex align-items-center gap-1">

                                        {{-- 1. View Analytics / Overview --}}
                                        <a href="{{ $showCampaignUrl }}" class="btn btn-sm btn-light border p-1 text-primary shadow-xs d-inline-flex align-items-center justify-content-center" title="View Full Report">
                                            <i class="fas fa-chart-line" style="font-size: 10px;"></i>
                                        </a>

                                        {{-- 2. Dispatch Now --}}
                                        @if(in_array($camp->status, ['draft', 'scheduled']))
                                            <button type="button" class="btn btn-sm btn-light border p-1 text-success shadow-xs d-inline-flex align-items-center justify-content-center" onclick="triggerActionConfirm('Dispatch Campaign', 'Queue this campaign for immediate broadcast to all target recipients?', '{{ $dispatchNowUrl }}', 'dispatch')" title="Dispatch / Send Now">
                                                <i class="fas fa-paper-plane" style="font-size: 10px;"></i>
                                            </button>
                                        @endif

                                        {{-- 3. Pause / Resume --}}
                                        @if(in_array($camp->status, ['queued', 'sending']))
                                            <button type="button" class="btn btn-sm btn-light border p-1 text-warning shadow-xs d-inline-flex align-items-center justify-content-center" onclick="ajaxCampaignAction('{{ $pauseCampaignUrl }}', 'Pause Broadcast', 'POST')" title="Pause Dispatch">
                                                <i class="fas fa-pause" style="font-size: 10px;"></i>
                                            </button>
                                        @elseif($camp->status === 'paused')
                                            <button type="button" class="btn btn-sm btn-light border p-1 text-success shadow-xs d-inline-flex align-items-center justify-content-center" onclick="ajaxCampaignAction('{{ $resumeCampaignUrl }}', 'Resume Broadcast', 'POST')" title="Resume Dispatch">
                                                <i class="fas fa-play" style="font-size: 10px;"></i>
                                            </button>
                                        @endif

                                        {{-- 4. Centralized Test Send Trigger --}}
                                        <button type="button" class="btn btn-sm btn-light border p-1 text-info shadow-xs d-inline-flex align-items-center justify-content-center" onclick="openTestMailModal({{ $camp->id }}, '{{ addslashes($camp->name) }}', '{{$testSendUrl }}')" title="Send Test Email">
                                            <i class="fas fa-vial" style="font-size: 10px;"></i>
                                        </button>

                                        {{-- 5. Edit --}}
                                        @if(method_exists($camp, 'isEditable') ? $camp->isEditable() : in_array($camp->status, ['draft', 'scheduled']))
                                            <a href="{{ $editCampaignUrl }}" class="btn btn-sm btn-light border p-1 text-secondary shadow-xs d-inline-flex align-items-center justify-content-center" title="Edit Campaign Settings">
                                                <i class="fas fa-pencil" style="font-size: 10px;"></i>
                                            </a>
                                        @endif

                                        {{-- 6. Delete --}}
                                        <button type="button" class="btn btn-sm btn-light border p-1 text-danger shadow-xs d-inline-flex align-items-center justify-content-center" onclick="triggerActionConfirm('Delete Campaign', 'Are you sure you want to delete this campaign? All recipient delivery records will be removed.', '{{ $deleteCampaignUrl }}', 'delete')" title="Delete Campaign">
                                            <i class="fas fa-trash-can" style="font-size: 10px;"></i>
                                        </button>

                                    </div>
                                </td>
                            </tr>

                        @empty
                            <tr id="emptyCampaignsRow">
                                <td colspan="9" class="text-center py-5 text-muted small">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block text-secondary opacity-25"></i>
                                    <span class="fw-semibold">No marketing campaigns found. Click "Create Campaign" to launch your first one.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Responsive Pagination Footer --}}
            <div class="p-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-25 card-footer no-print" id="campaigns-pagination-footer" style="{{ method_exists($campaigns, 'hasPages') &&$campaigns->hasPages() ? '' : 'display: none;' }}">
                <small class="text-muted font-monospace bn-digit" id="pagination-info-text" style="font-size: 11px;">
                    Showing {{ method_exists($campaigns, 'firstItem') ? ($campaigns->firstItem() ?? 0) : 1 }} to {{ method_exists($campaigns, 'lastItem') ? ($campaigns->lastItem() ?? 0) : $campaigns->count() }} of {{ number_format($totalCampaignsCount) }} campaigns
                </small>
                <div id="campaigns-pagination-links">
                    @if(method_exists($campaigns, 'hasPages') &&$campaigns->hasPages())
                        {{ $campaigns->links('pagination::bootstrap-5') }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal 1: Centralized Reusable Test Send Email Modal --}}
<div class="modal fade campaigns-modal-scope" id="centralTestMailModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-1">
                    <i class="fas fa-vial text-info"></i> <span>Send Test Email</span>
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="centralTestMailForm" onsubmit="submitTestEmailAjax(event)">
                <div class="modal-body p-3">
                    <p class="small text-muted mb-3" style="font-size: 11px; line-height: 1.5;">
                        Send an instant test preview of <strong id="testModalCampName" class="text-dark">Campaign</strong> to verify design, dynamic tags, and deliverability.
                    </p>

                    <label class="form-label" for="test_email_input">
                        Recipient Test Email <span class="text-danger">*</span>
                    </label>
                    <input type="email" name="test_email" id="test_email_input" class="form-control font-monospace shadow-none" placeholder="your-email@example.com" value="{{ auth()->user()->email ?? '' }}" required>
                </div>

                <div class="modal-footer border-top py-2 px-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info btn-compact text-white shadow-xs fw-bold" id="btn-submit-test-mail">
                        <i class="fas fa-paper-plane me-1"></i> Send Test
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal 2: Reusable Action Confirmation Modal --}}
<div class="modal fade campaigns-modal-scope" id="campaignActionModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mx-auto mb-3" id="actionModalIconWrap" style="width: 46px; height: 46px; border-radius: 50% !important;">
                    <i class="fas fa-circle-question fs-4" id="actionModalIcon"></i>
                </div>

                <h6 class="fw-bold text-dark mb-1" id="actionModalTitle" style="font-size: 14px;">Confirm Action</h6>
                <p class="text-muted small mb-3" id="actionModalMsg" style="font-size: 11px; line-height: 1.5;"></p>

                <form id="actionModalForm" onsubmit="executeActionModalAjax(event)">
                    <div id="actionModalMethodWrap"></div>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold" id="actionModalSubmitBtn">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Floating Toast Feedback Notification Holder --}}
<div id="custom-toast-holder"></div>

<script>
    let searchTimeout = null;
    let pendingActionUrl = '';
    let pendingActionMethod = 'POST';

    // 1. Live Reload-Free Fetch
    function fetchFilteredCampaigns(page = 1) {
        const query = (document.getElementById('campaignSearchInput')?.value || '').trim();
        const status = (document.getElementById('campaignStatusSelect')?.value || '').trim();

        const params = new URLSearchParams();
        if (query) params.append('search', query);
        if (status) params.append('status', status);
        if (page > 1) params.append('page', page);

        const tbody = document.getElementById('campaigns-tbody');
        if (tbody) tbody.style.opacity = '0.35';

        fetch("{{ url('admin/email-marketing/campaigns') }}?" + params.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(res => res.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const newTbody = doc.getElementById('campaigns-tbody');
            if (tbody && newTbody) {
                tbody.innerHTML = newTbody.innerHTML;
                tbody.style.opacity = '1';
            }

            const newPagination = doc.getElementById('campaigns-pagination-links');
            const currentPagination = document.getElementById('campaigns-pagination-links');
            const paginationFooter = document.getElementById('campaigns-pagination-footer');

            if (newPagination && currentPagination) {
                currentPagination.innerHTML = newPagination.innerHTML;
                if (paginationFooter) {
                    paginationFooter.style.display = newPagination.innerHTML.trim() === '' ? 'none' : 'flex';
                }
            }

            const newEntries = doc.getElementById('showing-entries-text');
            const currentEntries = document.getElementById('showing-entries-text');
            if (newEntries && currentEntries) {
                currentEntries.innerHTML = newEntries.innerHTML;
            }

            const newTotalBadge = doc.getElementById('campaigns-total-badge');
            const currentTotalBadge = document.getElementById('campaigns-total-badge');
            if (newTotalBadge && currentTotalBadge) {
                currentTotalBadge.innerHTML = newTotalBadge.innerHTML;
            }

            filterCampaignsTableClient();
        })
        .catch(err => {
            console.error('Fetch error:', err);
            if (tbody) tbody.style.opacity = '1';
        });
    }

    // 2. Client-Side Realtime Search
    function filterCampaignsTableClient() {
        const query = (document.getElementById('campaignSearchInput')?.value || '').toLowerCase().trim();
        const status = (document.getElementById('campaignStatusSelect')?.value || '').toLowerCase().trim();

        const rows = document.querySelectorAll('.campaign-entry-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const searchData = row.getAttribute('data-search') || '';
            const rowStatus = row.getAttribute('data-status') || '';

            const matchesQuery = !query || searchData.includes(query);
            const matchesStatus = !status || rowStatus === status;

            if (matchesQuery && matchesStatus) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const countBadge = document.getElementById('visible-campaign-count');
        if (countBadge) {
            countBadge.innerText = `${visibleCount} Showing`;
        }
    }

    // 3. Search & Dropdown Listeners
    document.getElementById('campaignSearchInput')?.addEventListener('input', function() {
        filterCampaignsTableClient();
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => fetchFilteredCampaigns(1), 500);
    });

    document.getElementById('campaignStatusSelect')?.addEventListener('change', function() {
        fetchFilteredCampaigns(1);
    });

    function resetCampaignFilters() {
        const searchInput = document.getElementById('campaignSearchInput');
        const statusSelect = document.getElementById('campaignStatusSelect');
        if (searchInput) searchInput.value = '';
        if (statusSelect) statusSelect.value = '';
        fetchFilteredCampaigns(1);
    }

    // 4. Pagination Interceptor
    document.addEventListener('click', function(e) {
        const pageLink = e.target.closest('#campaigns-pagination-links .page-link');
        if (pageLink) {
            e.preventDefault();
            const href = pageLink.getAttribute('href');
            if (href && href !== '#' && !href.startsWith('javascript:')) {
                const url = new URL(href);
                const page = url.searchParams.get('page') || 1;
                fetchFilteredCampaigns(page);
                document.querySelector('.custom-campaigns-table')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
    });

    // 5. Test Mail Modal & AJAX Dispatch
    function openTestMailModal(campId, campName, submitUrl) {
        document.getElementById('testModalCampName').innerText = campName;
        pendingActionUrl = submitUrl;
        const modalEl = document.getElementById('centralTestMailModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function submitTestEmailAjax(e) {
        e.preventDefault();
        const email = document.getElementById('test_email_input').value.trim();
        const btn = document.getElementById('btn-submit-test-mail');

        if (!email) return;

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Sending...';

        fetch(pendingActionUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ test_email: email })
        })
        .then(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Send Test';
            const modalEl = document.getElementById('centralTestMailModal');
            if (modalEl && window.bootstrap) bootstrap.Modal.getInstance(modalEl)?.hide();
            showToast('Dispatched', `Test email sent successfully to ${email}.`, 'success');
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Send Test';
            showToast('Error', 'Failed to send test email. Please check SMTP configuration.', 'danger');
        });
    }

    // 6. Action Confirmation Modal & AJAX Handler
    function triggerActionConfirm(title, message, actionUrl, type = 'dispatch') {
        const titleEl    = document.getElementById('actionModalTitle');
        const msgEl      = document.getElementById('actionModalMsg');
        const btnEl      = document.getElementById('actionModalSubmitBtn');
        const iconWrapEl = document.getElementById('actionModalIconWrap');
        const iconEl     = document.getElementById('actionModalIcon');

        if (titleEl) titleEl.innerText = title;
        if (msgEl) msgEl.innerText = message;
        pendingActionUrl = actionUrl;

        if (type === 'delete') {
            pendingActionMethod = 'DELETE';
            iconWrapEl.className = 'rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-2';
            iconEl.className = 'fas fa-trash-can fs-4';
            btnEl.className = 'btn btn-danger btn-compact shadow-xs fw-bold';
            btnEl.innerText = 'Yes, Delete';
        } else {
            pendingActionMethod = 'POST';
            iconWrapEl.className = 'rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mx-auto mb-2';
            iconEl.className = 'fas fa-paper-plane fs-4';
            btnEl.className = 'btn btn-success btn-compact shadow-xs fw-bold';
            btnEl.innerText = 'Yes, Dispatch';
        }

        const modalEl = document.getElementById('campaignActionModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function executeActionModalAjax(e) {
        e.preventDefault();
        const btn = document.getElementById('actionModalSubmitBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';

        fetch(pendingActionUrl, {
            method: pendingActionMethod,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(() => {
            btn.disabled = false;
            const modalEl = document.getElementById('campaignActionModal');
            if (modalEl && window.bootstrap) bootstrap.Modal.getInstance(modalEl)?.hide();
            showToast('Success', 'Campaign operation executed successfully.', 'success');
            fetchFilteredCampaigns(1);
        })
        .catch(() => {
            btn.disabled = false;
            showToast('Error', 'Failed to execute campaign operation.', 'danger');
        });
    }

    // 7. Direct Pause / Resume AJAX
    function ajaxCampaignAction(actionUrl, actionName, method = 'POST') {
        fetch(actionUrl, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(() => {
            showToast('Updated', `${actionName} executed successfully.`, 'info');
            fetchFilteredCampaigns(1);
        })
        .catch(() => {
            showToast('Error', `Failed to execute ${actionName}.`, 'danger');
        });
    }

    // 8. Floating Toast Notifications
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
            <div id="${toastId}" class="admin-campaign-toast ${config.color} p-3 d-flex align-items-center justify-content-between shadow-lg" style="border-radius: 5px !important;">
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
