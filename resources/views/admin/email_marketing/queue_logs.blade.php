@extends('layouts.admin')

@section('title', 'Queue & Delivery Logs - Email Marketing')

@section('admin_content')
@php
    $logs = $queueItems ?? ($logs ?? ($queueLogs ?? ($deliveryLogs ?? collect())));
    $totalLogsCount = method_exists($logs, 'total') ? $logs->total() : $logs->count();

    // Graceful query resolution for recipients telemetry
    if (class_exists(\App\Models\EmailCampaignRecipient::class)) {
        $deliveredOpenedCount = \App\Models\EmailCampaignRecipient::whereIn('status', ['delivered', 'opened', 'clicked'])->count();
        $pendingQueueCount    = \App\Models\EmailCampaignRecipient::whereIn('status', ['queued', 'pending'])->count();
        $bouncedFailedCount   = \App\Models\EmailCampaignRecipient::whereIn('status', ['bounced', 'failed'])->count();
    } else {
        $deliveredOpenedCount = method_exists($logs, 'whereIn') ? $logs->whereIn('status', ['delivered', 'opened', 'clicked'])->count() : 0;
        $pendingQueueCount    = method_exists($logs, 'whereIn') ? $logs->whereIn('status', ['queued', 'pending'])->count() : 0;
        $bouncedFailedCount   = method_exists($logs, 'whereIn') ? $logs->whereIn('status', ['bounced', 'failed'])->count() : 0;
    }

    $dashboardRoute = Route::has('admin.email_marketing.dashboard') 
        ? route('admin.email_marketing.dashboard') 
        : url('admin/email-marketing/dashboard');

    $queueLogsRoute = Route::has('admin.email_marketing.queue_logs') 
        ? route('admin.email_marketing.queue_logs') 
        : url('admin/email-marketing/queue-logs');
@endphp

<style>
    /* =========================================================
       KAIADMIN CLASSIC — EMAIL QUEUE & DELIVERY LOGS
       Scoped styling: no global Bootstrap overrides
    ========================================================= */

    #email-marketing-queue-logs-root {
        --queue-primary: #1572e8;
        --queue-primary-soft: #eaf2ff;
        --queue-success: #31ce36;
        --queue-success-soft: #e8f8eb;
        --queue-warning: #ffad46;
        --queue-warning-soft: #fff4e5;
        --queue-danger: #f25961;
        --queue-danger-soft: #fff0f0;
        --queue-info: #48abf7;
        --queue-text: #2a2f5b;
        --queue-muted: #6b7280;
        --queue-border: #e5e7eb;
        --queue-surface: #ffffff;
        --queue-soft-bg: #f7f9fc;
        --queue-radius: 5px;
        --queue-shadow: 0 1px 3px rgba(0, 0, 0, .045);
        color: var(--queue-text);
        min-width: 0;
        width: 100%;
    }

    #email-marketing-queue-logs-root *,
    #email-marketing-queue-logs-root *::before,
    #email-marketing-queue-logs-root *::after {
        box-sizing: border-box;
    }

    #email-marketing-queue-logs-root .btn,
    #email-marketing-queue-logs-root button,
    #email-marketing-queue-logs-root .badge,
    #email-marketing-queue-logs-root .alert,
    #email-marketing-queue-logs-root .form-control,
    #email-marketing-queue-logs-root .form-select,
    #email-marketing-queue-logs-root .input-group-text,
    #email-marketing-queue-logs-root .card,
    #email-marketing-queue-logs-root .metric-card,
    #email-marketing-queue-logs-root .page-link,
    #email-marketing-queue-logs-root .page-item span,
    #email-marketing-queue-logs-root .collapse-toggle-btn,
    .queue-modal-scope .modal-content,
    .queue-modal-scope .btn {
        border-radius: var(--queue-radius) !important;
    }

    #email-marketing-queue-logs-root .card {
        background: var(--queue-surface);
        border: 1px solid var(--queue-border) !important;
        box-shadow: var(--queue-shadow);
        margin-bottom: 16px;
    }

    #email-marketing-queue-logs-root .shadow-sm {
        box-shadow: var(--queue-shadow) !important;
    }

    #email-marketing-queue-logs-root .queue-header-wrap {
        min-width: 0;
    }

    #email-marketing-queue-logs-root .queue-header-wrap h5 {
        color: var(--queue-text) !important;
        font-size: 18px;
        font-weight: 700;
        letter-spacing: -.25px;
    }

    #email-marketing-queue-logs-root .queue-header-wrap > div:first-child {
        min-width: 0;
    }

    #email-marketing-queue-logs-root .queue-header-wrap small {
        color: var(--queue-muted) !important;
        font-size: 12px;
        line-height: 1.5;
    }

    #email-marketing-queue-logs-root .top-action-btns {
        flex-shrink: 0;
    }

    #email-marketing-queue-logs-root .btn-compact {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        min-height: 32px;
        padding: 6px 12px !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        line-height: 1.3 !important;
        white-space: nowrap;
        transition: all .18s ease;
    }

    #email-marketing-queue-logs-root .btn-compact:hover {
        transform: translateY(-1px);
    }

    #email-marketing-queue-logs-root .btn-primary {
        background-color: var(--queue-primary);
        border-color: var(--queue-primary);
    }

    #email-marketing-queue-logs-root .btn-primary:hover {
        background-color: #1264ce;
        border-color: #1264ce;
    }

    #email-marketing-queue-logs-root .alert {
        border: 1px solid transparent !important;
        box-shadow: none !important;
        font-size: 12px;
    }

    #email-marketing-queue-logs-root .alert-success {
        background: var(--queue-success-soft);
        border-color: #c9eed0 !important;
    }

    #email-marketing-queue-logs-root .alert-danger {
        background: var(--queue-danger-soft);
        border-color: #f8d0d2 !important;
    }

    /* Section headers */
    #email-marketing-queue-logs-root .section-header-wrap {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        min-height: 45px;
        padding: 10px 14px;
        background: #fff;
        border-bottom: 1px solid var(--queue-border);
    }

    #email-marketing-queue-logs-root .section-header-wrap > span {
        color: var(--queue-text) !important;
        font-size: 12px !important;
        font-weight: 700;
        line-height: 1.4;
    }

    #email-marketing-queue-logs-root .collapse-toggle-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 27px;
        width: 27px;
        height: 27px;
        padding: 0;
        background: #fff;
        border: 1px solid var(--queue-border);
        color: #667085;
        cursor: pointer;
        transition: all .2s ease;
    }

    #email-marketing-queue-logs-root .collapse-toggle-btn:hover {
        color: var(--queue-primary);
        background: var(--queue-primary-soft);
        border-color: #b9d4fb;
    }

    #email-marketing-queue-logs-root .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    #email-marketing-queue-logs-root .collapse-icon {
        font-size: 10px;
        transition: transform .25s ease;
    }

    /* KPI cards */
    #email-marketing-queue-logs-root .metric-card {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-width: 0;
        min-height: 112px;
        height: 100%;
        padding: 14px;
        overflow: hidden;
        background: #fff;
        border: 1px solid var(--queue-border) !important;
        border-left: 3px solid transparent !important;
        box-shadow: none !important;
        transition: border-color .2s ease, transform .2s ease;
    }

    #email-marketing-queue-logs-root .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(42, 47, 91, .07) !important;
    }

    #email-marketing-queue-logs-root .metric-card-blue {
        border-left-color: var(--queue-primary) !important;
        background: linear-gradient(135deg, #fff 65%, #f2f7ff);
    }

    #email-marketing-queue-logs-root .metric-card-green {
        border-left-color: var(--queue-success) !important;
        background: linear-gradient(135deg, #fff 65%, #f1fbf2);
    }

    #email-marketing-queue-logs-root .metric-card-amber {
        border-left-color: var(--queue-warning) !important;
        background: linear-gradient(135deg, #fff 65%, #fff9ef);
    }

    #email-marketing-queue-logs-root .metric-card-red {
        border-left-color: var(--queue-danger) !important;
        background: linear-gradient(135deg, #fff 65%, #fff5f5);
    }

    #email-marketing-queue-logs-root .stat-label-title {
        min-width: 0;
        font-size: 11px;
        font-weight: 700;
        color: var(--queue-muted) !important;
        letter-spacing: .1px;
    }

    #email-marketing-queue-logs-root .stat-val-num {
        margin: 0;
        color: var(--queue-text);
        font-size: 21px;
        font-weight: 700;
        line-height: 1.3;
        letter-spacing: -.4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #email-marketing-queue-logs-root .stat-sub-text {
        display: block;
        margin-top: 4px;
        font-size: 10px;
        line-height: 1.4;
    }

    #email-marketing-queue-logs-root .metric-icon-square {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 34px;
        width: 34px;
        height: 34px;
        border-radius: 5px !important;
        font-size: 14px;
    }

    #email-marketing-queue-logs-root .bn-digit,
    #email-marketing-queue-logs-root .font-monospace {
        font-weight: 600;
    }

    /* Filter toolbar */
    #email-marketing-queue-logs-root .queue-filter-control {
        width: 100%;
        min-height: 36px;
        padding: 7px 10px !important;
        color: #344054 !important;
        background: #fff !important;
        border: 1px solid #dfe3eb !important;
        border-radius: 5px !important;
        box-shadow: none !important;
        font-size: 12px !important;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #email-marketing-queue-logs-root .queue-filter-control:focus {
        border-color: var(--queue-primary) !important;
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .10) !important;
        outline: none;
    }

    #email-marketing-queue-logs-root .queue-addon-text {
        min-height: 36px;
        padding: 7px 11px !important;
        color: #667085 !important;
        background: #f8f9fc !important;
        border-color: #dfe3eb !important;
        font-size: 12px !important;
    }

    #email-marketing-queue-logs-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    #email-marketing-queue-logs-root .input-group > .form-control,
    #email-marketing-queue-logs-root .input-group > .form-select {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    #email-marketing-queue-logs-root .form-label {
        display: block;
        margin-bottom: 6px;
        color: #49546a;
        font-size: 11px;
        font-weight: 600;
    }

    /* Table heading */
    #email-marketing-queue-logs-root .queue-table-heading {
        background: #fff;
    }

    #email-marketing-queue-logs-root .queue-table-heading h6 {
        color: var(--queue-text) !important;
        font-size: 13px !important;
        font-weight: 700;
    }

    #email-marketing-queue-logs-root .queue-table-heading small {
        font-size: 10px;
    }

    /* Queue table */
    #email-marketing-queue-logs-root .pc-fit-container {
        width: 100%;
        min-width: 0;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    #email-marketing-queue-logs-root .custom-queue-table {
        width: 100%;
        min-width: 960px;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    #email-marketing-queue-logs-root .custom-queue-table thead th {
        padding: 11px 9px;
        color: #596579;
        background: #f8f9fc;
        border-top: 0;
        border-bottom: 1px solid var(--queue-border);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .35px;
        text-transform: uppercase;
        white-space: nowrap;
        vertical-align: middle;
    }

    #email-marketing-queue-logs-root .custom-queue-table tbody td {
        padding: 11px 9px;
        color: #49546a;
        background: #fff;
        border-bottom: 1px solid #edf0f4;
        font-size: 11px;
        vertical-align: middle;
    }

    #email-marketing-queue-logs-root .custom-queue-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #email-marketing-queue-logs-root .custom-queue-table tbody tr:hover td {
        background: #fafbfe;
    }

    #email-marketing-queue-logs-root .custom-queue-table .col-qk-id {
        width: 60px;
    }

    #email-marketing-queue-logs-root .custom-queue-table .col-qk-email {
        min-width: 190px;
    }

    #email-marketing-queue-logs-root .custom-queue-table .col-qk-camp {
        min-width: 160px;
    }

    #email-marketing-queue-logs-root .custom-queue-table .col-qk-status {
        min-width: 115px;
    }

    #email-marketing-queue-logs-root .custom-queue-table .col-qk-date {
        min-width: 135px;
    }

    #email-marketing-queue-logs-root .custom-queue-table .col-qk-opens,
    #email-marketing-queue-logs-root .custom-queue-table .col-qk-clicks {
        min-width: 65px;
    }

    #email-marketing-queue-logs-root .custom-queue-table .col-qk-diag {
        min-width: 150px;
    }

    #email-marketing-queue-logs-root .custom-queue-table .badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        max-width: 100%;
        font-size: 9.5px;
        font-weight: 700;
        letter-spacing: .1px;
    }

    #email-marketing-queue-logs-root .custom-queue-table .text-truncate {
        min-width: 0;
    }

    #email-marketing-queue-logs-root .custom-queue-table .btn {
        max-width: 100%;
    }

    /* Pagination */
    #email-marketing-queue-logs-root .card-footer {
        background: #fafbfc !important;
        border-top: 1px solid var(--queue-border) !important;
    }

    #email-marketing-queue-logs-root .card-footer nav {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        width: auto !important;
        max-width: 100%;
        margin: 0 !important;
    }

    #email-marketing-queue-logs-root .card-footer nav > div.d-sm-none,
    #email-marketing-queue-logs-root .card-footer nav > .d-sm-none {
        display: none !important;
    }

    #email-marketing-queue-logs-root .card-footer nav > div.d-none,
    #email-marketing-queue-logs-root .card-footer nav > .d-none.flex-sm-fill {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    #email-marketing-queue-logs-root .card-footer nav p,
    #email-marketing-queue-logs-root .card-footer nav .small.text-muted,
    #email-marketing-queue-logs-root .card-footer nav div:has(> p) {
        display: none !important;
    }

    #email-marketing-queue-logs-root .card-footer .pagination {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 4px !important;
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
    }

    #email-marketing-queue-logs-root .card-footer .page-item {
        display: inline-flex !important;
    }

    #email-marketing-queue-logs-root .card-footer .page-item .page-link,
    #email-marketing-queue-logs-root .card-footer .page-item span {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 28px !important;
        height: 28px !important;
        padding: 0 7px !important;
        color: #596579 !important;
        background: #fff !important;
        border: 1px solid #e1e5eb !important;
        border-radius: 5px !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        line-height: 1 !important;
        text-decoration: none !important;
        transition: all .15s ease !important;
    }

    #email-marketing-queue-logs-root .card-footer .page-item .page-link:hover {
        color: var(--queue-primary) !important;
        background: var(--queue-primary-soft) !important;
        border-color: #bfd6fa !important;
    }

    #email-marketing-queue-logs-root .card-footer .page-item.active .page-link,
    #email-marketing-queue-logs-root .card-footer .page-item.active span {
        color: #fff !important;
        background: var(--queue-primary) !important;
        border-color: var(--queue-primary) !important;
    }

    #email-marketing-queue-logs-root .card-footer .page-item.disabled .page-link,
    #email-marketing-queue-logs-root .card-footer .page-item.disabled span {
        opacity: .45 !important;
        background: #f8f9fc !important;
        border-color: #e5e7eb !important;
        cursor: not-allowed !important;
    }

    /* Diagnostic modal */
    .queue-modal-scope .modal-dialog {
        width: 100%;
        max-width: min(560px, calc(100% - 1rem));
        margin: 1rem auto;
    }

    .queue-modal-scope .modal-content {
        overflow: hidden;
        background: #fff;
        border: 1px solid #e5e7eb !important;
        box-shadow: 0 12px 40px rgba(15, 23, 42, .15) !important;
    }

    .queue-modal-scope .modal-header {
        padding: 16px 18px 10px;
    }

    .queue-modal-scope .modal-header h6 {
        color: #2a2f5b !important;
        font-size: 14px;
        line-height: 1.4;
    }

    .queue-modal-scope .modal-body {
        padding: 12px 18px;
    }

    .queue-modal-scope .modal-footer {
        padding: 10px 18px 16px;
    }

    .queue-modal-scope .modal-body > div:first-child {
        border: 1px solid #e5e7eb !important;
        background: #f8f9fc !important;
    }

    .queue-modal-scope #modalQueueRecipient {
        overflow-wrap: anywhere;
    }

    .queue-modal-scope #modalQueueErrorMessage {
        max-height: 260px;
        overflow: auto;
        overflow-wrap: anywhere;
        word-break: break-word;
        line-height: 1.6;
    }

    /* Responsive adjustments */
    @media (min-width: 1200px) {
        #email-marketing-queue-logs-root .custom-queue-table {
            min-width: 0;
            table-layout: fixed;
        }

        #email-marketing-queue-logs-root .custom-queue-table .col-qk-id {
            width: 5%;
        }

        #email-marketing-queue-logs-root .custom-queue-table .col-qk-email {
            width: 22%;
        }

        #email-marketing-queue-logs-root .custom-queue-table .col-qk-camp {
            width: 19%;
        }

        #email-marketing-queue-logs-root .custom-queue-table .col-qk-status {
            width: 11%;
        }

        #email-marketing-queue-logs-root .custom-queue-table .col-qk-date {
            width: 15%;
        }

        #email-marketing-queue-logs-root .custom-queue-table .col-qk-opens {
            width: 6%;
        }

        #email-marketing-queue-logs-root .custom-queue-table .col-qk-clicks {
            width: 6%;
        }

        #email-marketing-queue-logs-root .custom-queue-table .col-qk-diag {
            width: 16%;
        }

        #email-marketing-queue-logs-root .custom-queue-table th,
        #email-marketing-queue-logs-root .custom-queue-table td {
            overflow: hidden;
            text-overflow: ellipsis;
        }
    }

    @media (max-width: 991.98px) {
        #email-marketing-queue-logs-root .metric-card {
            min-height: 104px;
        }

        #email-marketing-queue-logs-root .custom-queue-table {
            min-width: 960px;
            table-layout: auto;
        }
    }

    @media (max-width: 767.98px) {
        #email-marketing-queue-logs-root {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        #email-marketing-queue-logs-root .queue-header-wrap {
            gap: 12px !important;
        }

        #email-marketing-queue-logs-root .queue-header-wrap h5 {
            font-size: 16px !important;
            line-height: 1.4;
        }

        #email-marketing-queue-logs-root .queue-header-wrap small {
            font-size: 11px !important;
        }

        #email-marketing-queue-logs-root .top-action-btns {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            width: 100%;
            gap: 7px !important;
        }

        #email-marketing-queue-logs-root .top-action-btns .btn {
            width: 100%;
            min-width: 0;
            min-height: 36px;
            padding: 7px 5px !important;
            font-size: 11px !important;
        }

        #email-marketing-queue-logs-root .card {
            margin-bottom: 12px;
        }

        #email-marketing-queue-logs-root .section-header-wrap {
            padding: 10px 11px;
        }

        #email-marketing-queue-logs-root .section-header-wrap > span {
            font-size: 11px !important;
        }

        #email-marketing-queue-logs-root .metric-card {
            min-height: 92px;
            padding: 10px !important;
        }

        #email-marketing-queue-logs-root .stat-label-title {
            font-size: 10px;
        }

        #email-marketing-queue-logs-root .stat-val-num {
            font-size: 17px;
        }

        #email-marketing-queue-logs-root .stat-sub-text {
            font-size: 9px;
        }

        #email-marketing-queue-logs-root .metric-icon-square {
            flex-basis: 28px;
            width: 28px;
            height: 28px;
            font-size: 11px;
        }

        #email-marketing-queue-logs-root .queue-filter-control,
        #email-marketing-queue-logs-root .queue-addon-text {
            min-height: 36px;
            font-size: 11px !important;
        }

        #email-marketing-queue-logs-root .form-label {
            font-size: 10px;
        }

        #email-marketing-queue-logs-root .queue-table-heading {
            padding: 11px !important;
        }

        #email-marketing-queue-logs-root .queue-table-heading h6 {
            font-size: 12px !important;
        }

        #email-marketing-queue-logs-root .pc-fit-container {
            overflow-x: auto !important;
        }

        #email-marketing-queue-logs-root .custom-queue-table {
            min-width: 960px !important;
            table-layout: auto !important;
        }

        #email-marketing-queue-logs-root .custom-queue-table thead th,
        #email-marketing-queue-logs-root .custom-queue-table tbody td {
            padding: 9px 8px;
        }

        #email-marketing-queue-logs-root .card-footer {
            padding: 10px !important;
        }

        #email-marketing-queue-logs-root .card-footer > small {
            text-align: center;
            font-size: 10px !important;
        }

        #email-marketing-queue-logs-root .card-footer nav {
            justify-content: center !important;
            width: 100% !important;
        }

        .queue-modal-scope .modal-dialog {
            max-width: calc(100% - 20px);
            margin: 10px auto;
        }

        .queue-modal-scope .modal-content {
            padding: 10px !important;
        }

        .queue-modal-scope .modal-header {
            padding: 10px 8px 8px;
        }

        .queue-modal-scope .modal-body {
            padding: 10px 8px;
        }

        .queue-modal-scope .modal-footer {
            padding: 8px;
        }
    }

    @media (max-width: 380px) {
        #email-marketing-queue-logs-root .metric-card {
            padding: 8px !important;
        }

        #email-marketing-queue-logs-root .stat-label-title {
            font-size: 9px;
        }

        #email-marketing-queue-logs-root .stat-val-num {
            font-size: 15px;
        }

        #email-marketing-queue-logs-root .metric-icon-square {
            flex-basis: 25px;
            width: 25px;
            height: 25px;
            font-size: 10px;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-queue-logs-root">

    {{-- 1. Page Header & Navigation --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 queue-header-wrap">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i class="fas fa-server text-primary"></i>
                    <span>Email Queue &amp; Delivery Logs</span>
                </h5>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 font-monospace" id="queue-total-badge" style="font-size: 10px;">
                    {{ number_format($totalLogsCount) }} Recipient Items
                </span>
            </div>
            <small class="text-muted d-block mt-1">
                Monitor background queue dispatches, SMTP transmissions, and recipient activity.
            </small>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 top-action-btns">
            <a href="{{ $dashboardRoute }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-chart-pie me-1"></i> <span>Dashboard</span>
            </a>
            <a href="{{ $queueLogsRoute }}" class="btn btn-sm btn-dark btn-compact shadow-xs fw-bold" onclick="this.innerHTML='<i class=\'fas fa-spinner fa-spin me-1\'></i> Refreshing...'">
                <i class="fas fa-arrows-rotate me-1"></i> <span>Refresh Queue</span>
            </a>
        </div>
    </div>

    {{-- 2. System Status Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 small mb-3 d-flex align-items-center justify-content-between" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 small mb-3 d-flex align-items-center justify-content-between" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-circle text-danger fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- 3. Delivery Pipeline Overview --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap">
            <span>
                <i class="fas fa-chart-pie text-primary me-1"></i> Delivery Pipeline &amp; Transmission Overview
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseQueueKpis" aria-expanded="true" aria-controls="collapseQueueKpis" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>

        <div class="collapse show" id="collapseQueueKpis">
            <div class="p-3">
                <div class="row g-2">

                    {{-- Total Logged Items --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-blue h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1 gap-2">
                                <span class="stat-label-title text-truncate">Total Queue Items</span>
                                <div class="metric-icon-square bg-primary-subtle text-primary">
                                    <i class="fas fa-envelopes-bulk"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 bn-digit font-monospace stat-val-num">
                                    {{ number_format($totalLogsCount) }}
                                </h4>
                                <small class="text-muted stat-sub-text text-truncate">Total recipient queue items</small>
                            </div>
                        </div>
                    </div>

                    {{-- Delivered / Opened --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-green h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1 gap-2">
                                <span class="stat-label-title text-truncate">Delivered / Opened</span>
                                <div class="metric-icon-square bg-success-subtle text-success">
                                    <i class="fas fa-paper-plane"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-success bn-digit font-monospace stat-val-num">
                                    {{ number_format($deliveredOpenedCount) }}
                                </h4>
                                <small class="text-success stat-sub-text text-truncate">
                                    <i class="fas fa-circle-check me-1"></i>Verified inboxes
                                </small>
                            </div>
                        </div>
                    </div>

                    {{-- Pending in Queue --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-amber h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1 gap-2">
                                <span class="stat-label-title text-truncate">Pending in Queue</span>
                                <div class="metric-icon-square bg-warning-subtle text-warning-emphasis">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-warning-emphasis bn-digit font-monospace stat-val-num">
                                    {{ number_format($pendingQueueCount) }}
                                </h4>
                                <small class="text-muted stat-sub-text text-truncate">Awaiting worker pickup</small>
                            </div>
                        </div>
                    </div>

                    {{-- Bounced / Failed --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-red h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1 gap-2">
                                <span class="stat-label-title text-truncate">Bounced / Failed</span>
                                <div class="metric-icon-square bg-danger-subtle text-danger">
                                    <i class="fas fa-ban"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-danger bn-digit font-monospace stat-val-num">
                                    {{ number_format($bouncedFailedCount) }}
                                </h4>
                                <small class="text-danger stat-sub-text text-truncate">
                                    <i class="fas fa-triangle-exclamation me-1"></i>Suppression active
                                </small>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- 4. Search & Status Filters --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap">
            <span>
                <i class="fas fa-filter text-primary me-1"></i> Filter Queue Dispatches &amp; Search Transmissions
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseQueueFilter" aria-expanded="true" aria-controls="collapseQueueFilter" title="Toggle Filters">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>

        <div class="collapse show" id="collapseQueueFilter">
            <div class="p-3">
                <form id="queueFilterForm" onsubmit="return false;">
                    <div class="row g-2 align-items-end">

                        {{-- Instant Live Search Input --}}
                        <div class="col-12 col-md-5">
                            <label class="form-label" for="queueSearchInput">
                                <i class="fas fa-search me-1 text-primary"></i> Search Email, Broadcast Name, or Token
                            </label>
                            <div class="input-group">
                                <span class="input-group-text queue-addon-text border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text" id="queueSearchInput" class="form-control border-start-0 ps-0 queue-filter-control shadow-none font-monospace" placeholder="customer@example.com, Spring Promo..." onkeyup="filterQueueTable()">
                            </div>
                        </div>

                        {{-- Status Filter Dropdown --}}
                        <div class="col-6 col-md-4">
                            <label class="form-label" for="queueStatusFilter">
                                <i class="fas fa-toggle-on me-1 text-success"></i> Delivery Status
                            </label>
                            <select id="queueStatusFilter" class="form-select queue-filter-control shadow-none font-monospace" onchange="filterQueueTable()">
                                <option value="all" selected>-- All Delivery Statuses --</option>
                                <option value="queued">Queued / In Process</option>
                                <option value="sent">Sent to SMTP</option>
                                <option value="delivered">Delivered Successfully</option>
                                <option value="opened">Opened</option>
                                <option value="clicked">Clicked</option>
                                <option value="bounced">Bounced</option>
                                <option value="failed">Failed Dispatch</option>
                            </select>
                        </div>

                        {{-- Reset Filters --}}
                        <div class="col-6 col-md-3">
                            <button type="button" class="btn btn-light border btn-compact w-100 shadow-xs fw-semibold text-secondary" onclick="resetQueueFilters()">
                                <i class="fas fa-rotate-left me-1"></i> <span>Reset Filters</span>
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 5. Queue Delivery Log Table --}}
    <div class="card border-0 shadow-sm overflow-hidden mb-3">

        <div class="p-2 px-3 border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 queue-table-heading">
            <div>
                <h6 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                    <i class="fas fa-list-check text-primary"></i>
                    <span>Queue Audit Log &amp; Transmission Details</span>
                </h6>
                <small class="text-muted font-monospace bn-digit" id="showing-entries-text">
                    Showing {{ method_exists($logs, 'firstItem') ? ($logs->firstItem() ?? 0) : 1 }} to {{ method_exists($logs, 'lastItem') ? ($logs->lastItem() ?? 0) : $logs->count() }} of {{$totalLogsCount }} records
                </small>
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-secondary border font-monospace px-2 py-1" style="font-size: 10px;" id="visible-queue-count">
                    {{ $totalLogsCount }} Total
                </span>
                <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseQueueTable" aria-expanded="true" aria-controls="collapseQueueTable" title="Toggle Table">
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </button>
            </div>
        </div>

        <div class="collapse show" id="collapseQueueTable">
            <div class="table-responsive pc-fit-container" style="min-height: 250px;">
                <table class="table align-middle mb-0 custom-queue-table" id="queue-table">
                    <thead>
                        <tr>
                            <th class="ps-3 col-qk-id text-center">#ID</th>
                            <th class="col-qk-email">Recipient Email</th>
                            <th class="col-qk-camp">Associated Broadcast</th>
                            <th class="col-qk-status">Status</th>
                            <th class="col-qk-date">Queued / Sent At</th>
                            <th class="text-center col-qk-opens">Opens</th>
                            <th class="text-center col-qk-clicks">Clicks</th>
                            <th class="pe-3 col-qk-diag">Diagnostic</th>
                        </tr>
                    </thead>

                    <tbody id="queue-tbody">
                        @forelse($logs as $log)
                            @php
                                $statusSlug = strtolower($log->status ?? '');
                                $statusBadge = match($statusSlug) {
                                    'opened', 'clicked' => 'bg-success-subtle text-success border border-success-subtle',
                                    'delivered'         => 'bg-info-subtle text-info border border-info-subtle',
                                    'sent'              => 'bg-primary-subtle text-primary border border-primary-subtle',
                                    'queued', 'pending' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                    'bounced', 'failed' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                    default             => 'bg-light text-secondary border',
                                };

                                $campaignName =$log->campaign->name ?? 'Lifecycle / System Trigger';
                                $campUrl =$log->campaign ? (Route::has('admin.email_marketing.campaigns.show') ? route('admin.email_marketing.campaigns.show', $log->campaign->id) : url('admin/email-marketing/campaigns/view/' .$log->campaign->id)) : null;

                                $rowSearchData = strtolower(
                                    ($log->email ?? '') . ' ' .
                                    $campaignName . ' ' .
                                    ($log->tracking_token ?? '') . ' ' .
                                    $statusSlug . ' ' .
                                    ($log->error_message ?? '')
                                );
                            @endphp

                            <tr class="queue-entry-row align-middle" data-search="{{ $rowSearchData }}" data-status="{{ $statusSlug }}">
                                <td class="ps-3 col-qk-id text-center font-monospace bn-digit text-muted" style="font-size: 11px;">
                                    {{ $log->id }}
                                </td>

                                <td class="col-qk-email">
                                    <div class="min-w-0" style="line-height: 1.4;">
                                        <strong class="text-dark d-block text-truncate" style="font-size: 12px;" title="{{ $log->email }}">
                                            {{ $log->email }}
                                        </strong>

                                        <div class="d-flex align-items-center gap-1 mt-1">
                                            <small class="text-muted font-monospace text-truncate" style="font-size: 10px; max-width: 140px;" title="{{ $log->tracking_token ?? 'N/A' }}">
                                                Tk: {{ substr($log->tracking_token ?? 'N/A', 0, 14) }}...
                                            </small>

                                            @if(!empty($log->tracking_token))
                                                <button type="button" class="btn btn-sm btn-light border p-0 text-muted shadow-none d-inline-flex align-items-center justify-content-center" style="width: 20px; height: 20px;" onclick="copyLogToken('{{ addslashes($log->tracking_token) }}')" title="Copy Tracking Token">
                                                    <i class="fas fa-copy" style="font-size: 9px;"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="col-qk-camp">
                                    @if($log->campaign &&$campUrl)
                                        <a href="{{ $campUrl }}" class="text-decoration-none fw-semibold text-primary d-block text-truncate" style="font-size: 12px;" title="{{ $campaignName }}">
                                            <i class="fas fa-paper-plane me-1 text-primary"></i> {{ $campaignName }}
                                        </a>
                                    @else
                                        <span class="text-muted small font-monospace d-block text-truncate">{{ $campaignName }}</span>
                                    @endif
                                </td>

                                <td class="col-qk-status">
                                    <span class="badge {{ $statusBadge }} font-monospace px-2 py-1" style="border-radius: 5px !important; font-size: 9.5px;">
                                        @if(in_array($statusSlug, ['queued', 'pending']))
                                            <i class="fas fa-clock me-1"></i>
                                        @elseif(in_array($statusSlug, ['opened', 'clicked', 'delivered']))
                                            <i class="fas fa-circle-check me-1"></i>
                                        @elseif(in_array($statusSlug, ['bounced', 'failed']))
                                            <i class="fas fa-triangle-exclamation me-1"></i>
                                        @endif
                                        {{ strtoupper($log->status ?? 'UNKNOWN') }}
                                    </span>
                                </td>

                                <td class="col-qk-date font-monospace bn-digit" style="line-height: 1.4;">
                                    @if($log->sent_at)
                                        <span class="text-dark d-block" style="font-size: 11px;">{{ $log->sent_at->format('M d, Y') }}</span>
                                        <small class="text-muted" style="font-size: 10px;">{{ $log->sent_at->format('h:i:s A') }}</small>
                                    @elseif($log->created_at)
                                        <span class="text-muted d-block" style="font-size: 10.5px;">Queued: {{ $log->created_at->format('M d, Y') }}</span>
                                        <small class="text-muted" style="font-size: 10px;">{{ $log->created_at->format('h:i A') }}</small>
                                    @else
                                        <span class="text-muted font-monospace" style="font-size: 10px;">Pending Dispatch</span>
                                    @endif
                                </td>

                                <td class="text-center col-qk-opens font-monospace bn-digit">
                                    @php $openCount = (int)($log->opens_count ?? 0); @endphp
                                    @if($openCount > 0)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="border-radius: 4px; font-size: 11px;">
                                            {{ $openCount }}
                                        </span>
                                    @else
                                        <span class="text-muted font-monospace" style="font-size: 11px;">0</span>
                                    @endif
                                </td>

                                <td class="text-center col-qk-clicks font-monospace bn-digit">
                                    @php $clickCount = (int)($log->clicks_count ?? 0); @endphp
                                    @if($clickCount > 0)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" style="border-radius: 4px; font-size: 11px;">
                                            {{ $clickCount }}
                                        </span>
                                    @else
                                        <span class="text-muted font-monospace" style="font-size: 11px;">0</span>
                                    @endif
                                </td>

                                <td class="pe-3 col-qk-diag">
                                    @if(!empty($log->error_message))
                                        <button type="button" class="btn btn-sm btn-light border p-1 text-danger shadow-xs d-inline-flex align-items-center gap-1 text-truncate" style="max-width: 100%; font-size: 10px; border-radius: 5px !important;" onclick="showQueueDetailModal('{{ addslashes($log->email) }}', '{{ addslashes($campaignName) }}', '{{ addslashes($log->error_message) }}')" title="Click to view full error message">
                                            <i class="fas fa-triangle-exclamation flex-shrink-0"></i>
                                            <span class="text-truncate">{{ $log->error_message }}</span>
                                        </button>
                                    @else
                                        <span class="text-success small d-flex align-items-center gap-1 font-monospace" style="font-size: 10px;">
                                            <i class="fas fa-check-circle"></i> <span>OK</span>
                                        </span>
                                    @endif
                                </td>
                            </tr>

                        @empty
                            <tr id="emptyQueueRow">
                                <td colspan="8" class="text-center py-5 text-muted small">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block text-secondary opacity-25"></i>
                                    <span class="fw-semibold">No queue or delivery logs found matching the filter criteria.</span>
                                </td>
                            </tr>
                        @endforelse

                        <tr id="noMatchQueueRow" style="display: none;">
                            <td colspan="8" class="text-center py-5 text-muted small">
                                <i class="fas fa-filter-circle-xmark fa-2x mb-2 d-block text-secondary opacity-25"></i>
                                <span class="fw-semibold">No queue logs matched your search or status filter.</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Responsive Pagination Footer --}}
            @if(method_exists($logs, 'hasPages') &&$logs->hasPages())
                <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50 card-footer no-print">
                    <small class="text-muted font-monospace bn-digit" style="font-size: 11px;">
                        Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{$logs->total() }} records
                    </small>
                    <div>
                        {{ $logs->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>

{{-- Modal: Error / Transmission Diagnostic Detail --}}
<div class="modal fade queue-modal-scope" id="queueDetailModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg p-3">

            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i class="fas fa-circle-exclamation text-danger"></i>
                    <span>Transmission Diagnostic &amp; Error Note</span>
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-2 pt-3">
                <div class="mb-3 p-3 bg-light border">
                    <small class="text-muted d-block font-monospace mb-1" style="font-size: 10px;">RECIPIENT &amp; BROADCAST</small>
                    <strong class="text-dark d-block" id="modalQueueRecipient" style="font-size: 12.5px;">user@example.com</strong>
                    <span class="text-secondary small font-monospace" id="modalQueueCampaign">Campaign Name</span>
                </div>

                <div>
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                        <small class="text-muted font-monospace" style="font-size: 10px;">SMTP SERVER RESPONSE / ERROR</small>
                        <button type="button" class="btn btn-link btn-compact p-0 text-decoration-none text-primary font-monospace" style="font-size: 10.5px;" onclick="copyModalError()">
                            <i class="fas fa-copy me-1"></i> Copy Error
                        </button>
                    </div>

                    <div class="p-3 bg-danger-subtle text-danger border border-danger-subtle font-monospace small" style="border-radius: 5px; font-size: 11px; white-space: pre-wrap; word-break: break-all; max-height: 220px; overflow-y: auto;" id="modalQueueErrorMessage">
                        Diagnostic details will be rendered here.
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
    function showQueueDetailModal(email, campaign, errorMessage) {
        document.getElementById('modalQueueRecipient').innerText = email;
        document.getElementById('modalQueueCampaign').innerText = campaign;
        document.getElementById('modalQueueErrorMessage').innerText = errorMessage || 'No diagnostic error details recorded.';
        const modalEl = document.getElementById('queueDetailModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function copyModalError() {
        const text = document.getElementById('modalQueueErrorMessage')?.innerText || '';
        if (text && navigator.clipboard) {
            navigator.clipboard.writeText(text);
        }
    }

    function copyLogToken(token) {
        if (token && navigator.clipboard) {
            navigator.clipboard.writeText(token);
        }
    }

    function filterQueueTable() {
        const query  = (document.getElementById('queueSearchInput')?.value || '').toLowerCase().trim();
        const status = (document.getElementById('queueStatusFilter')?.value || 'all').toLowerCase();

        const rows = document.querySelectorAll('.queue-entry-row');
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

        const countBadge = document.getElementById('visible-queue-count');
        if (countBadge) {
            countBadge.innerText = `${visibleCount} Showing`;
        }

        const noMatchRow = document.getElementById('noMatchQueueRow');
        if (noMatchRow) {
            noMatchRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
        }
    }

    function resetQueueFilters() {
        const searchInput  = document.getElementById('queueSearchInput');
        const statusFilter = document.getElementById('queueStatusFilter');
        if (searchInput)  searchInput.value = '';
        if (statusFilter) statusFilter.value = 'all';
        filterQueueTable();
    }
</script>
@endsection