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
    /* Strict 5px Element Radius Across All Controls */
    #email-marketing-recipients-root .btn,
    #email-marketing-recipients-root .btn-sm,
    #email-marketing-recipients-root button,
    #email-marketing-recipients-root .badge,
    #email-marketing-recipients-root .alert,
    #email-marketing-recipients-root .form-control,
    #email-marketing-recipients-root .form-select,
    #email-marketing-recipients-root .input-group-text,
    #email-marketing-recipients-root .card,
    #email-marketing-recipients-root .metric-card,
    #email-marketing-recipients-root .page-link,
    #email-marketing-recipients-root .page-item span,
    #email-marketing-recipients-root .collapse-toggle-btn,
    .recipient-modal-scope .modal-content,
    .recipient-modal-scope .btn {
        border-radius: 5px !important;
    }

    #email-marketing-recipients-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #email-marketing-recipients-root .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Safe Monospace & Bengali Digit Typography */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Compact Sleek Button Styling */
    .btn-compact {
        font-size: 11px !important;
        padding: 5px 12px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    /* Collapsible Trigger Buttons & Rotating Chevron */
    .collapse-toggle-btn {
        width: 24px;
        height: 24px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        color: #475569;
        transition: all 0.2s ease;
    }
    .collapse-toggle-btn:hover {
        background-color: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
    }
    .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }
    .collapse-icon {
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        font-size: 10px;
    }

    /* Enterprise Surface Cards */
    #email-marketing-recipients-root .card {
        border: 1px solid #e2e8f0;
        background-color: #ffffff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 16px;
    }
    .section-header-wrap {
        padding: 10px 14px;
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
    }

    /* Metric Cards - Zero Overflow Architecture */
    .metric-card {
        padding: 12px 14px;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    }
    .stat-label-title {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.02em;
        line-height: 1.2;
    }
    .stat-val-num {
        font-size: 18px;
        line-height: 1.25;
        letter-spacing: -0.02em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .stat-sub-text {
        font-size: 10px;
        margin-top: 2px;
    }
    .metric-icon-square {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
        border-radius: 5px !important;
    }

    .metric-card-blue   { background: linear-gradient(180deg, #ffffff 0%, #f0f9ff 100%); border-left: 3.5px solid #2563eb !important; }
    .metric-card-green  { background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%); border-left: 3.5px solid #10b981 !important; }
    .metric-card-cyan   { background: linear-gradient(180deg, #ffffff 0%, #ecfeff 100%); border-left: 3.5px solid #06b6d4 !important; }
    .metric-card-red    { background: linear-gradient(180deg, #ffffff 0%, #fef2f2 100%); border-left: 3.5px solid #ef4444 !important; }

    /* Table Architecture: 100% Fit Screen on PC with Zero Horizontal Scroll */
    .custom-recipients-table {
        width: 100%;
        margin-bottom: 0;
    }
    .custom-recipients-table thead th {
        font-size: 10px;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #475569;
        background-color: #f8fafc;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 8px 8px;
        white-space: nowrap;
    }
    .custom-recipients-table tbody td {
        padding: 8px 8px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        font-size: 11px;
        vertical-align: middle;
        white-space: nowrap;
    }
    .custom-recipients-table tbody tr:hover td {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .custom-recipients-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .custom-recipients-table th,
        .custom-recipients-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 8 Columns Proportional Distribution Summing to Exactly 100% */
        .col-rc-id        { width: 4% !important; }
        .col-rc-user      { width: 22% !important; }
        .col-rc-status    { width: 11% !important; }
        .col-rc-date      { width: 13% !important; }
        .col-rc-opens     { width: 8% !important; }
        .col-rc-clicks    { width: 8% !important; }
        .col-rc-activity  { width: 14% !important; }
        .col-rc-error     { width: 20% !important; }
    }

    /* Form Controls */
    #email-marketing-recipients-root .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #email-marketing-recipients-root .form-control,
    #email-marketing-recipients-root .form-select {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #email-marketing-recipients-root .form-control:focus,
    #email-marketing-recipients-root .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Bulletproof Responsive Pagination */
    .card-footer nav {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        width: auto !important;
        margin: 0 !important;
    }
    .card-footer nav > div.d-sm-none,
    .card-footer nav > .d-sm-none {
        display: none !important;
    }
    .card-footer nav > div.d-none,
    .card-footer nav > .d-none.flex-sm-fill {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    .card-footer nav p,
    .card-footer nav .small.text-muted,
    .card-footer nav div:has(> p),
    .card-footer nav > div.d-none > div:first-child,
    .card-footer nav .d-none.flex-sm-fill > div:first-child {
        display: none !important;
    }
    .card-footer .pagination {
        margin: 0 !important;
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 3px !important;
        padding: 0 !important;
        list-style: none !important;
    }
    .card-footer .page-item {
        display: inline-flex !important;
    }
    .card-footer .page-item .page-link,
    .card-footer .page-item span {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 25px !important;
        height: 25px !important;
        padding: 0 6px !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        color: #475569 !important;
        background-color: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 5px !important;
        text-decoration: none !important;
        line-height: 1 !important;
        transition: all 0.12s ease !important;
    }
    .card-footer .page-item .page-link:hover {
        background-color: #f1f5f9 !important;
        border-color: #94a3b8 !important;
        color: #0f172a !important;
    }
    .card-footer .page-item.active .page-link,
    .card-footer .page-item.active span {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.25) !important;
    }
    .card-footer .page-item.disabled .page-link,
    .card-footer .page-item.disabled span {
        opacity: 0.45 !important;
        background-color: #f8fafc !important;
        border-color: #e2e8f0 !important;
        cursor: not-allowed !important;
    }
    .card-footer nav svg {
        width: 10px !important;
        height: 10px !important;
    }

    /* 100/100 Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .recipients-header-wrap h5 {
            font-size: 16px !important;
        }
        .top-action-btns {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .top-action-btns .btn {
            width: 100%;
            padding: 8px 6px !important;
            font-size: 11px !important;
            justify-content: center;
        }
        .metric-card {
            padding: 10px !important;
            min-height: 84px !important;
        }
        .stat-label-title {
            font-size: 10px !important;
        }
        .stat-val-num {
            font-size: 14px !important;
        }
        .stat-sub-text {
            font-size: 9px !important;
        }
        .metric-icon-square {
            width: 28px !important;
            height: 28px !important;
            font-size: 11px !important;
        }
        .pc-fit-container {
            width: 100%;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }
        .custom-recipients-table {
            min-width: 820px !important;
            table-layout: auto !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-recipients-root">

    {{-- 1. Header & Navigation (Original Clean Light Theme)[cite: 20] --}}
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
                Detailed delivery timestamps, tracking events, opens, and clicks for this campaign[cite: 20].
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

    {{-- 2. Delivery & Engagement KPI Summary Cards (Collapsible Section) --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
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

    {{-- 3. Live Search & Status Filter Toolbar (Collapsible) --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
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
                            <input type="text" id="recipientSearchInput" class="form-control border-start-0 ps-0 shadow-none" placeholder="e.g. user@example.com, token, timeout..." onkeyup="filterRecipientsTable()">
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

    {{-- 4. Recipients Delivery Log Table Card (Desktop 100% Fit Screen with Zero Horizontal Scroll)[cite: 20] --}}
    <div class="card border-0 shadow-sm overflow-hidden mb-3">
        <div class="p-2 px-3 border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 bg-light bg-opacity-25">
            <div>
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13px;">
                    <i class="fas fa-users-viewfinder text-primary"></i> <span>Audience Delivery Audit Records</span>
                </h6>
                <small class="text-muted font-monospace bn-digit" style="font-size: 10px;">
                    Showing {{ $recipients->firstItem() ?? 0 }} to {{ $recipients->lastItem() ?? 0 }} of {{$recipients->total() }} total recipients[cite: 20]
                </small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-secondary border font-monospace px-2 py-1" style="font-size: 10px;" id="visible-recipient-count">
                    {{ $recipients->total() }} Logged[cite: 20]
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
                                    {{ $recipient->id }}[cite: 20]
                                </td>
                                <td class="col-rc-user">
                                    <div class="min-w-0" style="line-height: 1.3;">
                                        <strong class="text-dark d-block text-truncate" style="font-size: 12px;" title="{{ $recipient->email }}">
                                            {{ $recipient->email }}[cite: 20]
                                        </strong>
                                        <small class="text-muted font-monospace d-block text-truncate" style="font-size: 10px;" title="{{ $recipient->tracking_token }}">
                                            Token: {{ substr($recipient->tracking_token, 0, 16) }}...[cite: 20]
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
                                        {{ strtoupper($recipient->status) }}[cite: 20]
                                    </span>
                                </td>
                                <td class="col-rc-date font-monospace bn-digit" style="line-height: 1.3;">
                                    @if($recipient->delivered_at)
                                        <span class="text-dark d-block" style="font-size: 11px;">{{ $recipient->delivered_at->format('M d, Y') }}</span>[cite: 20]
                                        <small class="text-muted" style="font-size: 9.5px;">{{ $recipient->delivered_at->format('h:i:s A') }}</small>[cite: 20]
                                    @elseif($recipient->sent_at)
                                        <span class="text-muted d-block" style="font-size: 10.5px;">Sent: {{ $recipient->sent_at->format('h:i A') }}</span>[cite: 20]
                                    @else
                                        <span class="badge bg-light text-muted border font-monospace px-2 py-0" style="font-size: 9.5px;">In Queue</span>[cite: 20]
                                    @endif
                                </td>
                                <td class="text-center col-rc-opens font-monospace bn-digit">
                                    @if($recipient->opens_count > 0)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0" style="border-radius: 5px !important; font-size: 10px;">
                                            {{ $recipient->opens_count }}[cite: 20]
                                        </span>
                                    @else
                                        <span class="text-muted" style="font-size: 11px;">0</span>[cite: 20]
                                    @endif
                                </td>
                                <td class="text-center col-rc-clicks font-monospace bn-digit">
                                    @if($recipient->clicks_count > 0)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0" style="border-radius: 5px !important; font-size: 10px;">
                                            {{ $recipient->clicks_count }}[cite: 20]
                                        </span>
                                    @else
                                        <span class="text-muted" style="font-size: 11px;">0</span>[cite: 20]
                                    @endif
                                </td>
                                <td class="col-rc-activity">
                                    @if($recipient->clicked_at)
                                        <small class="text-primary d-block fw-semibold font-monospace" style="font-size: 10.5px;">
                                            <i class="fas fa-mouse-pointer me-1"></i> {{ $recipient->clicked_at->diffForHumans() }}[cite: 20]
                                        </small>
                                    @elseif($recipient->opened_at)
                                        <small class="text-success d-block fw-semibold font-monospace" style="font-size: 10.5px;">
                                            <i class="fas fa-envelope-open me-1"></i> {{ $recipient->opened_at->diffForHumans() }}[cite: 20]
                                        </small>
                                    @else
                                        <span class="text-muted small" style="font-size: 10px;">No engagement</span>[cite: 20]
                                    @endif
                                </td>
                                <td class="col-rc-error pe-3">
                                    @if(!empty($recipient->error_message))
                                        <button type="button" class="btn btn-sm btn-light border p-1 text-danger shadow-xs d-inline-flex align-items-center gap-1 text-truncate" style="max-width: 100%; font-size: 10px; border-radius: 5px !important;" onclick="showRecipientErrorModal('{{ addslashes($recipient->email) }}', '{{ strtoupper($recipient->status) }}', '{{ addslashes($recipient->error_message) }}')" title="Click to view full error message">
                                            <i class="fas fa-triangle-exclamation flex-shrink-0"></i>
                                            <span class="text-truncate">{{ $recipient->error_message }}</span>[cite: 20]
                                        </button>
                                    @else
                                        <span class="text-muted font-monospace" style="font-size: 11px;">—</span>[cite: 20]
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyRecipientsRow">
                                <td colspan="8" class="text-center py-5 text-muted small">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block text-secondary opacity-25"></i>[cite: 20]
                                    <span class="fw-semibold">No recipients queued yet for this campaign.</span>[cite: 20]
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Bulletproof Responsive Pagination Footer[cite: 20] --}}
            @if($recipients->hasPages())
                <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50 card-footer no-print">
                    <small class="text-muted font-monospace bn-digit" style="font-size: 11px;">
                        Showing {{ $recipients->firstItem() }} to {{ $recipients->lastItem() }} of {{$recipients->total() }} recipients[cite: 20]
                    </small>
                    <div>
                        {{ $recipients->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal: Recipient Error / Bounce Details Modal (Zero Cut-off on Mobile) -->
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