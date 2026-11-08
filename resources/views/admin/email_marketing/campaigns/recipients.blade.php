@extends('layouts.admin')

@section('title', 'Campaign Recipients - ' . $campaign->name)

@php
    $totalRecipientsCount = $recipients->total();
    $openedCount  = $recipients->where('opens_count', '>', 0)->count();
    $clickedCount =$recipients->where('clicks_count', '>', 0)->count();
    $bouncedCount =$recipients->whereIn('status', ['bounced', 'failed'])->count();
@endphp

@section('admin_content')
<style>
    /* =========================================================
       EMAIL MARKETING - RECIPIENTS
       Kaiadmin Lite v1.2.0 | Classic / White Classic
    ========================================================= */

    #email-marketing-recipients-root {
        --rc-primary: #1572e8;
        --rc-primary-soft: #eaf2ff;
        --rc-success: #31ce77;
        --rc-success-soft: #e9f9f0;
        --rc-info: #48abf7;
        --rc-info-soft: #eaf6ff;
        --rc-danger: #f25961;
        --rc-danger-soft: #fff0f0;
        --rc-text: #2a2f5b;
        --rc-muted: #6b7280;
        --rc-border: #e5e7eb;
        --rc-surface: #ffffff;
        --rc-background: #f8fafc;
        color: var(--rc-text);
    }

    #email-marketing-recipients-root *,
    .recipient-modal-scope * {
        box-sizing: border-box;
    }

    /* Main cards */
    #email-marketing-recipients-root .card {
        background: var(--rc-surface);
        border: 1px solid var(--rc-border);
        border-radius: 5px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .035);
        margin-bottom: 16px;
    }

    #email-marketing-recipients-root .section-header-wrap {
        padding: 13px 16px;
        background: #fff;
        border-bottom: 1px solid var(--rc-border);
        border-radius: 5px 5px 0 0;
    }

    #email-marketing-recipients-root .section-header-wrap > span {
        font-size: 13px !important;
        font-weight: 700;
        color: var(--rc-text);
    }

    /* Header */
    #email-marketing-recipients-root .recipients-header-wrap {
        padding: 2px 0 5px;
    }

    #email-marketing-recipients-root .recipients-header-wrap h5 {
        color: var(--rc-text) !important;
        font-size: 20px !important;
        font-weight: 700;
        letter-spacing: -.35px;
    }

    #email-marketing-recipients-root .recipients-header-wrap > div > small {
        color: var(--rc-muted) !important;
        font-size: 12px !important;
    }

    #email-marketing-recipients-root .recipients-header-wrap .badge {
        border-radius: 5px;
        font-size: 11px !important;
        font-weight: 600;
        background: #f1f5f9 !important;
        color: #475569 !important;
        border: 1px solid #e2e8f0 !important;
    }

    /* Buttons */
    #email-marketing-recipients-root .btn,
    .recipient-modal-scope .btn {
        border-radius: 5px;
        font-weight: 600;
        transition: all .18s ease;
    }

    #email-marketing-recipients-root .btn-compact {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        min-height: 34px;
        padding: 7px 12px;
        font-size: 12px;
        line-height: 1.35;
        white-space: nowrap;
    }

    #email-marketing-recipients-root .btn-outline-primary {
        color: var(--rc-primary);
        border-color: #cbdcf8;
        background: #fff;
    }

    #email-marketing-recipients-root .btn-outline-primary:hover {
        color: #fff;
        background: var(--rc-primary);
        border-color: var(--rc-primary);
    }

    #email-marketing-recipients-root .btn-outline-secondary:hover {
        color: #fff;
    }

    #email-marketing-recipients-root .btn-light {
        background: #f8fafc;
        border-color: #e2e8f0 !important;
    }

    #email-marketing-recipients-root .btn-light:hover {
        background: #eef2f7;
        border-color: #cbd5e1 !important;
    }

    /* Collapse controls */
    #email-marketing-recipients-root .collapse-toggle-btn {
        width: 30px;
        height: 30px;
        min-width: 30px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 5px;
        transition: all .2s ease;
    }

    #email-marketing-recipients-root .collapse-toggle-btn:hover {
        color: #fff;
        background: var(--rc-primary);
        border-color: var(--rc-primary);
    }

    #email-marketing-recipients-root .collapse-icon {
        font-size: 11px;
        transition: transform .2s ease;
    }

    #email-marketing-recipients-root .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    /* KPI cards */
    #email-marketing-recipients-root .metric-card {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 112px;
        height: 100%;
        padding: 15px;
        overflow: hidden;
        border: 1px solid var(--rc-border);
        border-left-width: 3px;
        border-radius: 5px;
        background: #fff;
        transition: transform .18s ease, box-shadow .18s ease;
    }

    #email-marketing-recipients-root .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(15, 23, 42, .07);
    }

    #email-marketing-recipients-root .metric-card-blue {
        border-left-color: var(--rc-primary);
        background: linear-gradient(135deg, #fff 55%, #f2f7ff);
    }

    #email-marketing-recipients-root .metric-card-green {
        border-left-color: var(--rc-success);
        background: linear-gradient(135deg, #fff 55%, #f1fcf5);
    }

    #email-marketing-recipients-root .metric-card-cyan {
        border-left-color: var(--rc-info);
        background: linear-gradient(135deg, #fff 55%, #f0f9ff);
    }

    #email-marketing-recipients-root .metric-card-red {
        border-left-color: var(--rc-danger);
        background: linear-gradient(135deg, #fff 55%, #fff5f5);
    }

    #email-marketing-recipients-root .stat-label-title {
        font-size: 12px;
        font-weight: 600;
        line-height: 1.35;
        color: #64748b !important;
    }

    #email-marketing-recipients-root .stat-val-num {
        margin: 0;
        font-size: 23px;
        font-weight: 700;
        line-height: 1.3;
        letter-spacing: -.5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #email-marketing-recipients-root .stat-sub-text {
        margin-top: 4px;
        font-size: 11px;
        color: #94a3b8 !important;
    }

    #email-marketing-recipients-root .metric-icon-square {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 5px;
        font-size: 15px;
    }

    /* Filter controls */
    #email-marketing-recipients-root .form-label {
        display: block;
        margin-bottom: 6px;
        color: #475569;
        font-size: 12px;
        font-weight: 600;
    }

    #email-marketing-recipients-root .form-control,
    #email-marketing-recipients-root .form-select,
    #email-marketing-recipients-root .input-group-text {
        min-height: 38px;
        border-color: #dce2ea;
        border-radius: 5px;
        color: #334155;
        background-color: #fff;
        font-size: 13px;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #email-marketing-recipients-root .input-group > .input-group-text {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        background: #f8fafc;
    }

    #email-marketing-recipients-root .input-group > .form-control {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    #email-marketing-recipients-root .form-control:focus,
    #email-marketing-recipients-root .form-select:focus {
        border-color: #8bb8f7;
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .10);
        outline: none;
    }

    #email-marketing-recipients-root .form-control::placeholder {
        color: #a0a9b7;
    }

    /* Table header and toolbar */
    #email-marketing-recipients-root .table-toolbar {
        background: #fff;
    }

    #email-marketing-recipients-root .custom-recipients-table {
        width: 100%;
        min-width: 1120px;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    #email-marketing-recipients-root .custom-recipients-table thead th {
        padding: 12px 10px;
        color: #64748b;
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .035em;
        text-transform: uppercase;
        white-space: nowrap;
        vertical-align: middle;
    }

    #email-marketing-recipients-root .custom-recipients-table tbody td {
        padding: 12px 10px;
        color: #334155;
        background: #fff;
        border-bottom: 1px solid #edf0f4;
        font-size: 12px;
        vertical-align: middle;
    }

    #email-marketing-recipients-root .custom-recipients-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #email-marketing-recipients-root .custom-recipients-table tbody tr:hover td {
        background: #f8fbff;
    }

    #email-marketing-recipients-root .custom-recipients-table .col-rc-id {
        min-width: 55px;
    }

    #email-marketing-recipients-root .custom-recipients-table .col-rc-user {
        min-width: 230px;
    }

    #email-marketing-recipients-root .custom-recipients-table .col-rc-status {
        min-width: 135px;
    }

    #email-marketing-recipients-root .custom-recipients-table .col-rc-date {
        min-width: 145px;
    }

    #email-marketing-recipients-root .custom-recipients-table .col-rc-opens,
    #email-marketing-recipients-root .custom-recipients-table .col-rc-clicks {
        min-width: 80px;
    }

    #email-marketing-recipients-root .custom-recipients-table .col-rc-activity {
        min-width: 150px;
    }

    #email-marketing-recipients-root .custom-recipients-table .col-rc-error {
        min-width: 240px;
    }

    #email-marketing-recipients-root .custom-recipients-table .badge {
        display: inline-flex;
        align-items: center;
        border-radius: 5px;
        font-size: 10px !important;
        font-weight: 700;
        letter-spacing: .015em;
    }

    #email-marketing-recipients-root .custom-recipients-table .text-truncate {
        max-width: 100%;
    }

    #email-marketing-recipients-root .pc-fit-container {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    #email-marketing-recipients-root .pc-fit-container::-webkit-scrollbar {
        height: 7px;
    }

    #email-marketing-recipients-root .pc-fit-container::-webkit-scrollbar-track {
        background: #f1f5f9;
    }

    #email-marketing-recipients-root .pc-fit-container::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    /* Error detail button */
    #email-marketing-recipients-root .custom-recipients-table .btn {
        min-height: 29px;
        padding: 5px 8px !important;
        font-size: 11px !important;
        border-radius: 5px;
    }

    /* Pagination */
    #email-marketing-recipients-root .card-footer {
        padding: 12px 16px;
        background: #fff !important;
        border-top: 1px solid var(--rc-border);
    }

    #email-marketing-recipients-root .card-footer nav {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        width: auto;
        margin: 0;
    }

    #email-marketing-recipients-root .card-footer nav > div.d-sm-none,
    #email-marketing-recipients-root .card-footer nav > .d-sm-none {
        display: none !important;
    }

    #email-marketing-recipients-root .card-footer nav > div.d-none,
    #email-marketing-recipients-root .card-footer nav > .d-none.flex-sm-fill {
        display: flex !important;
        align-items: center;
        justify-content: center;
    }

    #email-marketing-recipients-root .card-footer nav p,
    #email-marketing-recipients-root .card-footer nav .small.text-muted,
    #email-marketing-recipients-root .card-footer nav div:has(> p),
    #email-marketing-recipients-root .card-footer nav > div.d-none > div:first-child,
    #email-marketing-recipients-root .card-footer nav .d-none.flex-sm-fill > div:first-child {
        display: none !important;
    }

    #email-marketing-recipients-root .card-footer .pagination {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        gap: 4px;
        margin: 0;
        padding: 0;
    }

    #email-marketing-recipients-root .card-footer .page-item {
        display: inline-flex;
    }

    #email-marketing-recipients-root .card-footer .page-link,
    #email-marketing-recipients-root .card-footer .page-item span {
        min-width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 8px;
        color: #64748b;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        line-height: 1;
        transition: all .15s ease;
    }

    #email-marketing-recipients-root .card-footer .page-link:hover {
        color: var(--rc-primary);
        background: var(--rc-primary-soft);
        border-color: #bfd5fa;
    }

    #email-marketing-recipients-root .card-footer .page-item.active .page-link,
    #email-marketing-recipients-root .card-footer .page-item.active span {
        color: #fff;
        background: var(--rc-primary);
        border-color: var(--rc-primary);
    }

    #email-marketing-recipients-root .card-footer .page-item.disabled .page-link,
    #email-marketing-recipients-root .card-footer .page-item.disabled span {
        opacity: .45;
        background: #f8fafc;
        cursor: not-allowed;
    }

    #email-marketing-recipients-root .card-footer nav svg {
        width: 12px;
        height: 12px;
    }

    /* Modal */
    .recipient-modal-scope {
        z-index: 106500;
    }

    .recipient-modal-scope .modal-dialog {
        max-width: 560px;
        margin: 1.75rem auto;
        padding-left: 12px;
        padding-right: 12px;
    }

    .recipient-modal-scope .modal-content {
        overflow: hidden;
        border: 1px solid #e5e7eb !important;
        border-radius: 6px;
        background: #fff;
        box-shadow: 0 15px 45px rgba(15, 23, 42, .18);
    }

    .recipient-modal-scope .modal-header {
        padding: 18px 20px 10px;
    }

    .recipient-modal-scope .modal-header h6 {
        color: var(--rc-text) !important;
        font-size: 15px !important;
    }

    .recipient-modal-scope .modal-body {
        padding: 14px 20px;
    }

    .recipient-modal-scope .modal-footer {
        padding: 10px 20px 18px;
    }

    .recipient-modal-scope #modalRecipientEmail {
        max-width: 75%;
        overflow-wrap: anywhere;
    }

    .recipient-modal-scope #modalRecipientErrorMessage {
        border-radius: 5px;
        font-size: 12px !important;
        line-height: 1.6;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
        word-break: break-word;
        max-height: 260px;
        overflow-y: auto;
    }

    /* Typography helpers */
    #email-marketing-recipients-root .font-monospace,
    #email-marketing-recipients-root .bn-digit {
        font-weight: 600;
    }

    #email-marketing-recipients-root .shadow-xs {
        box-shadow: 0 1px 2px rgba(15, 23, 42, .05);
    }

    /* Tablet */
    @media (max-width: 991.98px) {
        #email-marketing-recipients-root .metric-card {
            min-height: 105px;
        }

        #email-marketing-recipients-root .custom-recipients-table {
            min-width: 1120px;
        }
    }

    /* Mobile */
    @media (max-width: 767.98px) {
        #email-marketing-recipients-root {
            padding-top: 16px !important;
        }

        #email-marketing-recipients-root .recipients-header-wrap {
            gap: 12px !important;
            margin-bottom: 16px !important;
        }

        #email-marketing-recipients-root .recipients-header-wrap h5 {
            font-size: 17px !important;
        }

        #email-marketing-recipients-root .recipients-header-wrap > div > small {
            font-size: 11px !important;
        }

        #email-marketing-recipients-root .top-action-btns {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            gap: 8px;
        }

        #email-marketing-recipients-root .top-action-btns .btn {
            width: 100%;
            min-height: 37px;
            padding: 7px 5px;
            font-size: 11px;
            white-space: normal;
        }

        #email-marketing-recipients-root .section-header-wrap {
            padding: 11px 12px;
        }

        #email-marketing-recipients-root .section-header-wrap > span {
            font-size: 11px !important;
            padding-right: 8px;
        }

        #email-marketing-recipients-root .collapse-toggle-btn {
            width: 28px;
            height: 28px;
            min-width: 28px;
        }

        #email-marketing-recipients-root .metric-card {
            min-height: 94px;
            padding: 11px;
        }

        #email-marketing-recipients-root .stat-label-title {
            font-size: 10px;
        }

        #email-marketing-recipients-root .stat-val-num {
            font-size: 18px;
        }

        #email-marketing-recipients-root .stat-sub-text {
            font-size: 9px;
        }

        #email-marketing-recipients-root .metric-icon-square {
            width: 29px;
            height: 29px;
            font-size: 12px;
        }

        #email-marketing-recipients-root .form-label {
            font-size: 11px;
        }

        #email-marketing-recipients-root .form-control,
        #email-marketing-recipients-root .form-select {
            min-height: 38px;
            font-size: 12px;
        }

        #email-marketing-recipients-root .card-footer {
            padding: 12px;
        }

        #email-marketing-recipients-root .card-footer > small {
            text-align: center;
            font-size: 10px !important;
        }

        #email-marketing-recipients-root .card-footer nav {
            justify-content: center;
            width: 100%;
        }

        .recipient-modal-scope .modal-dialog {
            margin: 10px auto;
            padding-left: 8px;
            padding-right: 8px;
        }

        .recipient-modal-scope .modal-content {
            padding: 8px !important;
        }

        .recipient-modal-scope .modal-header {
            padding: 12px 10px 8px;
        }

        .recipient-modal-scope .modal-body {
            padding: 10px;
        }

        .recipient-modal-scope .modal-footer {
            padding: 8px 10px 12px;
        }
    }

    @media (max-width: 380px) {
        #email-marketing-recipients-root .top-action-btns {
            grid-template-columns: 1fr;
        }

        #email-marketing-recipients-root .metric-card {
            padding: 9px;
        }

        #email-marketing-recipients-root .stat-val-num {
            font-size: 16px;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-recipients-root">

    {{-- 1. Header & Navigation --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 recipients-header-wrap">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                    <i class="fas fa-list-check text-primary"></i>
                    <span>Recipients Audit Log</span>
                </h5>
                <span class="badge bg-light text-secondary border font-monospace px-2 py-1" style="font-size: 10px;">
                    {{ $campaign->name }}
                </span>
            </div>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Detailed delivery timestamps, tracking events, opens, and clicks for this campaign.
            </small>
        </div>

        <div class="d-flex gap-2 top-action-btns">
            <a href="{{ route('admin.email_marketing.campaigns.show', $campaign->id) }}" class="btn btn-sm btn-outline-primary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-chart-line me-1"></i> <span>Campaign Overview</span>
            </a>
            <a href="{{ route('admin.email_marketing.campaigns.index') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-arrow-left me-1"></i> <span>All Campaigns</span>
            </a>
        </div>
    </div>

    {{-- 2. Delivery & Engagement KPI Summary Cards --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small">
                <i class="fas fa-chart-pie text-primary me-1"></i> Recipient Delivery &amp; Engagement Summary
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseRecipientsKpis" aria-expanded="true" aria-controls="collapseRecipientsKpis" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseRecipientsKpis">
            <div class="p-3">
                <div class="row g-2">
                    {{-- Total Audience Count --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-blue h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Total Audience</span>
                                <div class="metric-icon-square bg-primary-subtle text-primary shadow-xs">
                                    <i class="fas fa-users-viewfinder"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                                    {{ number_format($totalRecipientsCount) }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Total queued recipients</small>
                            </div>
                        </div>
                    </div>

                    {{-- Verified Opens --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-green h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Verified Opens</span>
                                <div class="metric-icon-square bg-success-subtle text-success shadow-xs">
                                    <i class="fas fa-envelope-open"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-success bn-digit font-monospace stat-val-num">
                                    {{ number_format($openedCount) }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Active readership</small>
                            </div>
                        </div>
                    </div>

                    {{-- Link Clicks --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-cyan h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Link Clicks</span>
                                <div class="metric-icon-square bg-info-subtle text-info shadow-xs">
                                    <i class="fas fa-mouse-pointer"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-info bn-digit font-monospace stat-val-num">
                                    {{ number_format($clickedCount) }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Direct store clicks</small>
                            </div>
                        </div>
                    </div>

                    {{-- Delivery Bounces / Failures --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-red h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Bounced / Failed</span>
                                <div class="metric-icon-square bg-danger-subtle text-danger shadow-xs">
                                    <i class="fas fa-triangle-exclamation"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-danger bn-digit font-monospace stat-val-num">
                                    {{ number_format($bouncedCount) }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Undelivered addresses</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Live Search & Status Filter Toolbar --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small">
                <i class="fas fa-filter text-primary me-1"></i> Filter Audience by Email &amp; Delivery Status
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseRecipientsFilter" aria-expanded="true" aria-controls="collapseRecipientsFilter" title="Toggle Filters">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseRecipientsFilter">
            <div class="p-3">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-5">
                        <label class="form-label" for="recipientSearchInput">
                            <i class="fas fa-search me-1 text-primary"></i> Search Email, Token, or Error Note
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-search" style="font-size: 11px;"></i></span>
                            <input type="text" id="recipientSearchInput" class="form-control border-start-0 ps-0 shadow-none" placeholder="user@example.com, token, timeout..." onkeyup="filterRecipientsTable()">
                        </div>
                    </div>

                    <div class="col-6 col-md-4">
                        <label class="form-label" for="recipientStatusFilter">
                            <i class="fas fa-toggle-on me-1 text-success"></i> Delivery Status
                        </label>
                        <select id="recipientStatusFilter" class="form-select shadow-none font-monospace" onchange="filterRecipientsTable()">
                            <option value="all" selected>All Statuses</option>
                            <option value="opened">Opened</option>
                            <option value="clicked">Clicked</option>
                            <option value="delivered">Delivered</option>
                            <option value="sent">Sent</option>
                            <option value="queued">In Queue</option>
                            <option value="bounced">Bounced / Failed</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-3">
                        <button type="button" class="btn btn-light border btn-compact w-100 shadow-xs fw-semibold text-secondary" onclick="resetRecipientFilters()">
                            <i class="fas fa-rotate-left me-1"></i> <span>Reset Filters</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Recipients Delivery Log Table Card --}}
    <div class="card border-0 shadow-sm overflow-hidden mb-3">
        <div class="p-2 px-3 border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 bg-light bg-opacity-25">
            <div>
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13px;">
                    <i class="fas fa-users-viewfinder text-primary"></i> <span>Audience Delivery Audit Records</span>
                </h6>
                <small class="text-muted font-monospace bn-digit" style="font-size: 10px;">
                    Showing {{ $recipients->firstItem() ?? 0 }} to {{ $recipients->lastItem() ?? 0 }} of {{$recipients->total() }} total recipients
                </small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-secondary border font-monospace px-2 py-1" style="font-size: 10px;" id="visible-recipient-count">
                    {{ $recipients->total() }} Logged
                </span>
                <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseRecipientsTable" aria-expanded="true" aria-controls="collapseRecipientsTable" title="Toggle Table">
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </button>
            </div>
        </div>

        <div class="collapse show" id="collapseRecipientsTable">
            <div class="table-responsive pc-fit-container">
                <table class="table align-middle mb-0 custom-recipients-table" id="recipients-table">
                    <thead>
                        <tr>
                            <th class="ps-3 col-rc-id text-center">#</th>
                            <th class="col-rc-user">Recipient Email</th>
                            <th class="col-rc-status">Delivery Status</th>
                            <th class="col-rc-date">Delivered At</th>
                            <th class="text-center col-rc-opens">Opens</th>
                            <th class="text-center col-rc-clicks">Clicks</th>
                            <th class="col-rc-activity">Last Activity</th>
                            <th class="pe-3 col-rc-error">Error / Bounce Details</th>
                        </tr>
                    </thead>
                    <tbody id="recipients-tbody">
                        @forelse($recipients as $recipient)
                            @php
                                $statusBadgeClass = match(strtolower($recipient->status ?? '')) {
                                    'opened', 'clicked' => 'bg-success-subtle text-success border border-success-subtle',
                                    'delivered'         => 'bg-info-subtle text-info border border-info-subtle',
                                    'sent', 'queued'    => 'bg-primary-subtle text-primary border border-primary-subtle',
                                    'bounced', 'failed' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                    default             => 'bg-light text-dark border',
                                };

                                $rowSearchString = strtolower(
                                    ($recipient->email ?? '') . ' ' .
                                    ($recipient->tracking_token ?? '') . ' ' .
                                    ($recipient->status ?? '') . ' ' .
                                    ($recipient->error_message ?? '')
                                );

                                $normalizedStatus = match(strtolower($recipient->status ?? '')) {
                                    'failed' => 'bounced',
                                    default  => strtolower($recipient->status ?? 'queued'),
                                };
                            @endphp
                            <tr class="recipient-row-item" data-search="{{ $rowSearchString }}" data-status="{{ $normalizedStatus }}">
                                <td class="ps-3 col-rc-id text-center font-monospace bn-digit text-muted" style="font-size: 11px;">
                                    {{ $recipient->id }}
                                </td>
                                <td class="col-rc-user">
                                    <div class="min-w-0" style="line-height: 1.3;">
                                        <strong class="text-dark d-block text-truncate" style="font-size: 12px;" title="{{ $recipient->email }}">
                                            {{ $recipient->email }}
                                        </strong>
                                        <small class="text-muted font-monospace d-block text-truncate" style="font-size: 10px;" title="{{ $recipient->tracking_token }}">
                                            Token: {{ substr($recipient->tracking_token, 0, 16) }}...
                                        </small>
                                    </div>
                                </td>
                                <td class="col-rc-status">
                                    <span class="badge {{ $statusBadgeClass }} font-monospace px-2 py-1" style="border-radius: 5px !important; font-size: 9.5px;">
                                        @if(in_array(strtolower($recipient->status ?? ''), ['opened', 'clicked']))
                                            <i class="fas fa-check-double me-1"></i>
                                        @elseif(strtolower($recipient->status ?? '') === 'delivered')
                                            <i class="fas fa-check me-1"></i>
                                        @elseif(in_array(strtolower($recipient->status ?? ''), ['bounced', 'failed']))
                                            <i class="fas fa-triangle-exclamation me-1"></i>
                                        @else
                                            <i class="fas fa-clock me-1"></i>
                                        @endif
                                        {{ strtoupper($recipient->status) }}
                                    </span>
                                </td>
                                <td class="col-rc-date font-monospace bn-digit" style="line-height: 1.3;">
                                    @if($recipient->delivered_at)
                                        <span class="text-dark d-block" style="font-size: 11px;">{{ $recipient->delivered_at->format('M d, Y') }}</span>
                                        <small class="text-muted" style="font-size: 9.5px;">{{ $recipient->delivered_at->format('h:i:s A') }}</small>
                                    @elseif($recipient->sent_at)
                                        <span class="text-muted d-block" style="font-size: 10.5px;">Sent: {{ $recipient->sent_at->format('h:i A') }}</span>
                                    @else
                                        <span class="badge bg-light text-muted border font-monospace px-2 py-0" style="font-size: 9.5px;">In Queue</span>
                                    @endif
                                </td>
                                <td class="text-center col-rc-opens font-monospace bn-digit">
                                    @if($recipient->opens_count > 0)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0" style="border-radius: 5px !important; font-size: 10px;">
                                            {{ $recipient->opens_count }}
                                        </span>
                                    @else
                                        <span class="text-muted" style="font-size: 11px;">0</span>
                                    @endif
                                </td>
                                <td class="text-center col-rc-clicks font-monospace bn-digit">
                                    @if($recipient->clicks_count > 0)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0" style="border-radius: 5px !important; font-size: 10px;">
                                            {{ $recipient->clicks_count }}
                                        </span>
                                    @else
                                        <span class="text-muted" style="font-size: 11px;">0</span>
                                    @endif
                                </td>
                                <td class="col-rc-activity">
                                    @if($recipient->clicked_at)
                                        <small class="text-primary d-block fw-semibold font-monospace" style="font-size: 10.5px;">
                                            <i class="fas fa-mouse-pointer me-1"></i> {{ $recipient->clicked_at->diffForHumans() }}
                                        </small>
                                    @elseif($recipient->opened_at)
                                        <small class="text-success d-block fw-semibold font-monospace" style="font-size: 10.5px;">
                                            <i class="fas fa-envelope-open me-1"></i> {{ $recipient->opened_at->diffForHumans() }}
                                        </small>
                                    @else
                                        <span class="text-muted small" style="font-size: 10px;">No engagement</span>
                                    @endif
                                </td>
                                <td class="col-rc-error pe-3">
                                    @if(!empty($recipient->error_message))
                                        <button type="button" class="btn btn-sm btn-light border p-1 text-danger shadow-xs d-inline-flex align-items-center gap-1 text-truncate" style="max-width: 100%; font-size: 10px; border-radius: 5px !important;" onclick="showRecipientErrorModal('{{ addslashes($recipient->email) }}', '{{ strtoupper($recipient->status) }}', '{{ addslashes($recipient->error_message) }}')" title="Click to view full error message">
                                            <i class="fas fa-triangle-exclamation flex-shrink-0"></i>
                                            <span class="text-truncate">{{ $recipient->error_message }}</span>
                                        </button>
                                    @else
                                        <span class="text-muted font-monospace" style="font-size: 11px;">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyRecipientsRow">
                                <td colspan="8" class="text-center py-5 text-muted small">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block text-secondary opacity-25"></i>
                                    <span class="fw-semibold">No recipients queued yet for this campaign.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Responsive Pagination Footer --}}
            @if($recipients->hasPages())
                <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50 card-footer no-print">
                    <small class="text-muted font-monospace bn-digit" style="font-size: 11px;">
                        Showing {{ $recipients->firstItem() }} to {{ $recipients->lastItem() }} of {{$recipients->total() }} recipients
                    </small>
                    <div>
                        {{ $recipients->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal: Recipient Error / Bounce Details Modal -->
<div class="modal fade recipient-modal-scope" id="recipientErrorModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg p-3">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 14px;">
                    <i class="fas fa-triangle-exclamation text-danger"></i> <span>Delivery Bounce / Error Details</span>
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2 pt-3">
                <div class="mb-2 p-2 bg-light border" style="border-radius: 5px;">
                    <small class="text-muted d-block font-monospace" style="font-size: 10px;">RECIPIENT &amp; STATUS</small>
                    <div class="d-flex justify-content-between align-items-center">
                        <strong class="text-dark" id="modalRecipientEmail" style="font-size: 12.5px;">user@example.com</strong>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace" id="modalRecipientStatus" style="font-size: 9.5px;">BOUNCED</span>
                    </div>
                </div>
                <div>
                    <small class="text-muted d-block font-monospace mb-1" style="font-size: 10px;">MAIL TRANSPORT SYSTEM RESPONSE</small>
                    <div class="p-2 bg-danger-subtle text-danger border border-danger-subtle font-monospace small" style="border-radius: 5px; font-size: 11px; white-space: pre-wrap; word-break: break-all; max-height: 220px; overflow-y: auto;" id="modalRecipientErrorMessage">
                        Error message will be rendered here.
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 d-flex justify-content-end">
                <button type="button" class="btn btn-dark btn-compact px-3" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    function showRecipientErrorModal(email, status, errorMessage) {
        document.getElementById('modalRecipientEmail').innerText = email;
        document.getElementById('modalRecipientStatus').innerText = status;
        document.getElementById('modalRecipientErrorMessage').innerText = errorMessage || 'No specific error details recorded.';
        const modalEl = document.getElementById('recipientErrorModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function filterRecipientsTable() {
        const query  = (document.getElementById('recipientSearchInput')?.value || '').toLowerCase().trim();
        const status = (document.getElementById('recipientStatusFilter')?.value || 'all').toLowerCase();

        const rows = document.querySelectorAll('.recipient-row-item');
        let visibleCount = 0;

        rows.forEach(row => {
            const searchData = row.getAttribute('data-search') || '';
            const rowStatus  = row.getAttribute('data-status') || '';

            const matchesQuery  = !query || searchData.includes(query);
            const matchesStatus = status === 'all' || rowStatus === status;

            if (matchesQuery && matchesStatus) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const countBadge = document.getElementById('visible-recipient-count');
        if (countBadge) {
            countBadge.innerText = `${visibleCount} Showing`;
        }
    }

    function resetRecipientFilters() {
        const searchInput  = document.getElementById('recipientSearchInput');
        const statusFilter = document.getElementById('recipientStatusFilter');
        if (searchInput)  searchInput.value = '';
        if (statusFilter) statusFilter.value = 'all';
        filterRecipientsTable();
    }
</script>
@endsection