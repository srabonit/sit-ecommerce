@extends('layouts.admin')

@section('title', 'Campaign Details - ' . $campaign->name)

@php
    $deliveryRate = $campaign->total_recipients > 0 ? round(($campaign->delivered_count / $campaign->total_recipients) * 100, 1) : 0;

    $badgeClass = match($campaign->status) {
        'completed' => 'bg-success-subtle text-success border border-success-subtle',
        'sending', 'queued' => 'bg-primary-subtle text-primary border border-primary-subtle',
        'scheduled' => 'bg-info-subtle text-info border border-info-subtle',
        'paused'    => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
        'cancelled' => 'bg-danger-subtle text-danger border border-danger-subtle',
        default     => 'bg-light text-dark border',
    };
@endphp

@section('admin_content')

<style>
    /* =========================================================
       EMAIL CAMPAIGN DETAILS - KAIADMIN CLASSIC / WHITE
    ========================================================= */

    #email-marketing-campaign-show-root {
        --campaign-primary: #1572e8;
        --campaign-primary-soft: #eaf2ff;
        --campaign-success: #31ce36;
        --campaign-info: #48abf7;
        --campaign-warning: #ffad46;
        --campaign-danger: #f25961;
        --campaign-text: #2a2f5b;
        --campaign-muted: #8d9498;
        --campaign-border: #ebedf2;
        --campaign-surface: #ffffff;
        --campaign-background: #f8f9fa;
        color: var(--campaign-text);
        font-size: 13px;
    }

    #email-marketing-campaign-show-root *,
    .campaign-modal-scope * {
        box-sizing: border-box;
    }

    /* General surfaces */
    #email-marketing-campaign-show-root .card {
        background: var(--campaign-surface);
        border: 1px solid var(--campaign-border) !important;
        border-radius: 5px !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .035);
        margin-bottom: 16px;
    }

    #email-marketing-campaign-show-root .section-header-wrap {
        min-height: 48px;
        padding: 11px 16px;
        background: #fff;
        border-bottom: 1px solid var(--campaign-border);
        border-radius: 5px 5px 0 0;
        gap: 12px;
    }

    #email-marketing-campaign-show-root .section-header-wrap > span,
    #email-marketing-campaign-show-root .section-header-wrap > div > span {
        font-size: 13px !important;
        font-weight: 700;
        color: #2a2f5b !important;
        line-height: 1.4;
    }

    /* Header */
    #email-marketing-campaign-show-root .campaign-show-header {
        padding: 4px 0 16px;
        border-bottom: 1px solid var(--campaign-border);
    }

    #email-marketing-campaign-show-root .campaign-show-header h5 {
        font-size: 19px !important;
        font-weight: 700;
        line-height: 1.4;
        color: #2a2f5b !important;
        overflow-wrap: anywhere;
    }

    #email-marketing-campaign-show-root .campaign-show-header > div:first-child > small {
        color: #8d9498 !important;
        font-size: 12px !important;
        line-height: 1.6 !important;
    }

    #email-marketing-campaign-show-root .header-actions-wrap {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
        gap: 7px;
    }

    /* Buttons */
    #email-marketing-campaign-show-root .btn,
    .campaign-modal-scope .btn {
        border-radius: 5px !important;
        font-weight: 600;
        transition: all .18s ease;
    }

    #email-marketing-campaign-show-root .btn-compact,
    .campaign-modal-scope .btn-compact {
        min-height: 33px;
        padding: 6px 12px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        font-size: 12px !important;
        font-weight: 600 !important;
        line-height: 1.35;
        white-space: nowrap;
        border-radius: 5px !important;
    }

    #email-marketing-campaign-show-root .btn-compact:hover,
    .campaign-modal-scope .btn-compact:hover {
        transform: translateY(-1px);
    }

    #email-marketing-campaign-show-root .btn-outline-secondary {
        border-color: #dce1e8;
        color: #6c757d;
    }

    #email-marketing-campaign-show-root .btn-outline-secondary:hover {
        background: #f1f3f5;
        border-color: #cbd2dc;
        color: #343a40;
    }

    /* Status badge */
    #email-marketing-campaign-show-root .badge {
        border-radius: 5px !important;
        font-weight: 700;
        letter-spacing: .15px;
    }

    #email-marketing-campaign-show-root .campaign-show-header .badge {
        font-size: 10px !important;
        padding: 5px 9px !important;
    }

    /* Collapse buttons */
    #email-marketing-campaign-show-root .collapse-toggle-btn {
        width: 29px;
        height: 29px;
        min-width: 29px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        color: #6c757d;
        border: 1px solid #e1e5eb;
        border-radius: 5px !important;
        transition: all .2s ease;
    }

    #email-marketing-campaign-show-root .collapse-toggle-btn:hover {
        background: var(--campaign-primary);
        color: #fff;
        border-color: var(--campaign-primary);
    }

    #email-marketing-campaign-show-root .collapse-icon {
        font-size: 11px;
        transition: transform .25s ease;
    }

    #email-marketing-campaign-show-root .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    /* KPI cards */
    #email-marketing-campaign-show-root .metric-card {
        min-height: 128px;
        height: 100%;
        padding: 15px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
        border: 1px solid var(--campaign-border) !important;
        border-left: 3px solid transparent !important;
        border-radius: 5px !important;
        transition: transform .18s ease, box-shadow .18s ease;
    }

    #email-marketing-campaign-show-root .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, .06);
    }

    #email-marketing-campaign-show-root .metric-card-blue {
        background: #f8fbff;
        border-left-color: #1572e8 !important;
    }

    #email-marketing-campaign-show-root .metric-card-green {
        background: #f8fdf9;
        border-left-color: #31ce36 !important;
    }

    #email-marketing-campaign-show-root .metric-card-cyan {
        background: #f6fcff;
        border-left-color: #48abf7 !important;
    }

    #email-marketing-campaign-show-root .metric-card-revenue {
        background: linear-gradient(135deg, #1572e8 0%, #0d5ec4 100%);
        border: 1px solid #1572e8 !important;
        color: #fff;
    }

    #email-marketing-campaign-show-root .stat-label-title {
        font-size: 12px;
        font-weight: 700;
        line-height: 1.4;
        letter-spacing: .1px;
    }

    #email-marketing-campaign-show-root .stat-val-num {
        font-size: 21px;
        font-weight: 700;
        line-height: 1.3;
        letter-spacing: -.4px;
        overflow-wrap: anywhere;
    }

    #email-marketing-campaign-show-root .stat-sub-text {
        margin-top: 4px;
        font-size: 10.5px;
        line-height: 1.4;
    }

    #email-marketing-campaign-show-root .metric-icon-square {
        width: 35px;
        height: 35px;
        min-width: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px !important;
        font-size: 14px;
    }

    #email-marketing-campaign-show-root .metric-card .progress {
        height: 5px !important;
        background: #e9edf3;
        border-radius: 10px !important;
        overflow: hidden;
    }

    #email-marketing-campaign-show-root .metric-card .progress-bar {
        border-radius: 10px !important;
    }

    /* General typography */
    #email-marketing-campaign-show-root .bn-digit,
    #email-marketing-campaign-show-root .font-monospace {
        font-weight: 600;
    }

    #email-marketing-campaign-show-root .text-muted {
        color: #8d9498 !important;
    }

    /* Specification details */
    #email-marketing-campaign-show-root .campaign-specs-grid {
        font-size: 13px;
    }

    #email-marketing-campaign-show-root .spec-label {
        display: block;
        margin-bottom: 6px;
        color: #8d9498;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .55px;
        line-height: 1.4;
    }

    #email-marketing-campaign-show-root .spec-value {
        min-width: 0;
        color: #2a2f5b;
        font-size: 12.5px;
        font-weight: 500;
        line-height: 1.6;
        overflow-wrap: anywhere;
    }

    #email-marketing-campaign-show-root .spec-value.text-truncate {
        display: block;
    }

    #email-marketing-campaign-show-root .spec-value .badge {
        max-width: 100%;
        white-space: normal;
        text-align: left;
        line-height: 1.5;
    }

    #email-marketing-campaign-show-root .mt-0\.5 {
        margin-top: 3px;
    }

    /* Audience health */
    #email-marketing-campaign-show-root .audience-health-list > div {
        min-height: 43px;
        padding: 10px 12px !important;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        background: #fafbfc !important;
        border: 1px solid #edf0f3 !important;
        border-radius: 5px !important;
        color: #515761;
        font-size: 12px;
        line-height: 1.4;
    }

    #email-marketing-campaign-show-root .audience-health-list > div > span:first-child {
        min-width: 0;
        overflow-wrap: anywhere;
    }

    #email-marketing-campaign-show-root .audience-health-list .badge {
        flex-shrink: 0;
        font-size: 11px !important;
    }

    /* Email preview */
    #email-marketing-campaign-show-root .email-preview-area {
        padding: 18px;
        background: #f7f8fa;
        border-radius: 0 0 5px 5px;
    }

    #email-marketing-campaign-show-root .email-render-preview-wrapper {
        width: 100%;
        max-width: 680px;
        min-height: 360px;
        margin: 0 auto;
        padding: 24px;
        overflow-x: auto;
        overflow-y: visible;
        background: #fff;
        border: 1px solid #e5e8ed;
        border-radius: 5px !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .035);
        -webkit-overflow-scrolling: touch;
    }

    #email-marketing-campaign-show-root .email-render-preview-wrapper img {
        max-width: 100%;
        height: auto;
    }

    /* Form controls */
    #email-marketing-campaign-show-root .form-label,
    .campaign-modal-scope .form-label {
        display: block;
        margin-bottom: 6px;
        color: #515761;
        font-size: 12px;
        font-weight: 700;
    }

    #email-marketing-campaign-show-root .form-control,
    #email-marketing-campaign-show-root .form-select,
    .campaign-modal-scope .form-control {
        min-height: 38px;
        padding: 8px 11px;
        color: #2a2f5b;
        background-color: #fff;
        border: 1px solid #e1e5eb;
        border-radius: 5px !important;
        box-shadow: none;
        font-size: 13px;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #email-marketing-campaign-show-root .form-control:focus,
    #email-marketing-campaign-show-root .form-select:focus,
    .campaign-modal-scope .form-control:focus {
        border-color: #1572e8;
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .10);
        outline: none;
    }

    /* Alerts */
    #email-marketing-campaign-show-root .alert {
        border-radius: 5px !important;
        border: 1px solid transparent !important;
        font-size: 13px;
    }

    #email-marketing-campaign-show-root .alert-success {
        border-color: #c8ead0 !important;
        background: #f2fbf4;
    }

    #email-marketing-campaign-show-root .alert-danger {
        border-color: #f3d0d2 !important;
        background: #fff6f6;
    }

    /* Modal styling */
    .campaign-modal-scope .modal-content {
        overflow: hidden;
        border: 1px solid #e9edf2 !important;
        border-radius: 6px !important;
        background: #fff;
        box-shadow: 0 12px 40px rgba(0, 0, 0, .15);
    }

    .campaign-modal-scope .modal-header,
    .campaign-modal-scope .modal-footer {
        background: #fafbfc !important;
        border-color: #edf0f3 !important;
    }

    .campaign-modal-scope .modal-title {
        font-size: 14px !important;
        color: #2a2f5b;
    }

    .campaign-modal-scope .modal-body {
        padding: 18px !important;
    }

    .campaign-modal-scope .modal-footer {
        padding: 12px 16px !important;
    }

    .campaign-modal-scope .modal-body p {
        font-size: 12px !important;
        line-height: 1.6 !important;
    }

    /* Utility */
    #email-marketing-campaign-show-root .shadow-xs {
        box-shadow: 0 1px 3px rgba(0, 0, 0, .05);
    }

    #email-marketing-campaign-show-root .min-w-0 {
        min-width: 0;
    }

    /* Tablet */
    @media (max-width: 991.98px) {
        #email-marketing-campaign-show-root .campaign-show-header {
            align-items: flex-start !important;
        }

        #email-marketing-campaign-show-root .header-actions-wrap {
            justify-content: flex-start;
        }

        #email-marketing-campaign-show-root .metric-card {
            min-height: 120px;
        }
    }

    /* Mobile */
    @media (max-width: 767.98px) {
        #email-marketing-campaign-show-root {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        #email-marketing-campaign-show-root .campaign-show-header {
            gap: 12px !important;
            padding-bottom: 13px;
        }

        #email-marketing-campaign-show-root .campaign-show-header h5 {
            font-size: 16px !important;
        }

        #email-marketing-campaign-show-root .header-actions-wrap {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 7px;
        }

        #email-marketing-campaign-show-root .header-actions-wrap > a,
        #email-marketing-campaign-show-root .header-actions-wrap > button,
        #email-marketing-campaign-show-root .header-actions-wrap > form {
            width: 100%;
            min-width: 0;
        }

        #email-marketing-campaign-show-root .header-actions-wrap > form .btn {
            width: 100%;
        }

        #email-marketing-campaign-show-root .header-actions-wrap .btn-back-main {
            grid-column: 1 / -1;
        }

        #email-marketing-campaign-show-root .btn-compact {
            min-height: 36px;
            padding: 7px 8px !important;
            font-size: 11px !important;
        }

        #email-marketing-campaign-show-root .section-header-wrap {
            min-height: 45px;
            padding: 10px 12px;
        }

        #email-marketing-campaign-show-root .section-header-wrap > span,
        #email-marketing-campaign-show-root .section-header-wrap > div > span {
            font-size: 12px !important;
        }

        #email-marketing-campaign-show-root .metric-card {
            min-height: 112px;
            padding: 11px !important;
        }

        #email-marketing-campaign-show-root .stat-label-title {
            font-size: 10.5px;
        }

        #email-marketing-campaign-show-root .stat-val-num {
            font-size: 16px;
        }

        #email-marketing-campaign-show-root .stat-sub-text {
            font-size: 9.5px;
        }

        #email-marketing-campaign-show-root .metric-icon-square {
            width: 29px;
            height: 29px;
            min-width: 29px;
            font-size: 12px;
        }

        #email-marketing-campaign-show-root .email-preview-area {
            padding: 10px;
        }

        #email-marketing-campaign-show-root .email-render-preview-wrapper {
            padding: 12px !important;
            min-height: 260px;
        }

        #email-marketing-campaign-show-root .audience-health-list > div {
            font-size: 11px;
            padding: 9px !important;
        }

        #email-marketing-campaign-show-root .card {
            margin-bottom: 12px;
        }
    }

    /* Small mobile */
    @media (max-width: 380px) {
        #email-marketing-campaign-show-root .metric-card {
            min-height: 105px;
            padding: 9px !important;
        }

        #email-marketing-campaign-show-root .stat-val-num {
            font-size: 14px;
        }

        #email-marketing-campaign-show-root .stat-label-title {
            font-size: 9.5px;
        }

        #email-marketing-campaign-show-root .header-actions-wrap {
            grid-template-columns: 1fr 1fr;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-campaign-show-root">

    {{-- 1. Header & Context Actions --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 campaign-show-header">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                    <i class="fas fa-paper-plane text-primary"></i>
                    <span>{{ $campaign->name }}</span>
                </h5>
                <span class="badge {{ $badgeClass }} font-monospace px-2 py-1" style="font-size: 10px;">
                    {{ strtoupper($campaign->status) }}
                </span>
            </div>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Created on {{ $campaign->created_at->format('M d, Y - h:i A') }} 
                @if($campaign->creator) by <strong class="text-dark">{{ $campaign->creator->name }}</strong> @endif
            </small>
        </div>

        <div class="d-flex gap-2 header-actions-wrap">
            <a href="{{ route('admin.email_marketing.campaigns.index') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs fw-semibold btn-back-main">
                <i class="fas fa-arrow-left me-1"></i> <span>Back to Campaigns</span>
            </a>

            {{-- Dispatch Now Action --}}
            @if(in_array($campaign->status, ['draft', 'scheduled']))
                <button type="button" class="btn btn-sm btn-success btn-compact shadow-xs fw-bold" onclick="triggerCampaignConfirm('Dispatch Campaign', 'Are you sure you want to queue this campaign for immediate broadcast to all recipients?', '{{ route('admin.email_marketing.campaigns.dispatch_now', $campaign->id) }}', 'dispatch')">
                    <i class="fas fa-paper-plane me-1"></i> <span>Dispatch Now</span>
                </button>
            @endif

            {{-- Pause / Resume / Cancel Actions --}}
            @if(in_array($campaign->status, ['queued', 'sending']))
                <form action="{{ route('admin.email_marketing.campaigns.pause', $campaign->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-warning btn-compact shadow-xs fw-semibold">
                        <i class="fas fa-pause me-1"></i> <span>Pause</span>
                    </button>
                </form>

                <button type="button" class="btn btn-sm btn-outline-danger btn-compact shadow-xs fw-semibold" onclick="triggerCampaignConfirm('Cancel Campaign', 'Are you sure you want to cancel all remaining queued emails for this campaign? This action cannot be reversed.', '{{ route('admin.email_marketing.campaigns.cancel', $campaign->id) }}', 'danger')">
                    <i class="fas fa-stop me-1"></i> <span>Cancel</span>
                </button>
            @elseif($campaign->status === 'paused')
                <form action="{{ route('admin.email_marketing.campaigns.resume', $campaign->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success btn-compact shadow-xs fw-bold">
                        <i class="fas fa-play me-1"></i> <span>Resume</span>
                    </button>
                </form>
            @endif

            {{-- Test Send Modal Trigger --}}
            <button type="button" class="btn btn-sm btn-outline-info btn-compact shadow-xs fw-semibold" data-bs-toggle="modal" data-bs-target="#testMailModal">
                <i class="fas fa-vial me-1"></i> <span>Send Test</span>
            </button>

            @if($campaign->isEditable())
                <a href="{{ route('admin.email_marketing.campaigns.edit', $campaign->id) }}" class="btn btn-sm btn-outline-dark btn-compact shadow-xs fw-semibold">
                    <i class="fas fa-pencil me-1"></i> <span>Edit</span>
                </a>
            @endif
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

    {{-- 3. Campaign Performance KPIs --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-chart-pie text-primary me-1"></i> Real-Time Campaign Performance &amp; Conversion KPIs
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseCampaignKpis" aria-expanded="true" aria-controls="collapseCampaignKpis" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseCampaignKpis">
            <div class="p-3">
                <div class="row g-2">
                    {{-- Delivered / Total Recipients --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-blue h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Delivered / Total</span>
                                <div class="metric-icon-square bg-primary-subtle text-primary shadow-xs">
                                    <i class="fas fa-truck-fast"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                                    {{ number_format($campaign->delivered_count) }}
                                    <span class="text-muted fw-normal" style="font-size: 11px;">/ {{ number_format($campaign->total_recipients) }}</span>
                                </h4>
                                <div class="progress mt-1 mb-1" style="height: 5px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ min($deliveryRate, 100) }}%"></div>
                                </div>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">
                                    Delivery Rate: <strong class="text-dark">{{ $deliveryRate }}%</strong>
                                </small>
                            </div>
                        </div>
                    </div>

                    {{-- Open Rate --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-green h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Open Rate</span>
                                <span class="badge bg-success-subtle text-success font-monospace px-1 py-0" style="font-size: 10px;">
                                    {{ $campaign->open_rate }}%
                                </span>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-success bn-digit font-monospace stat-val-num">
                                    {{ number_format($campaign->opened_count) }}
                                </h4>
                                <div class="progress mt-1 mb-1" style="height: 5px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ min($campaign->open_rate, 100) }}%"></div>
                                </div>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Verified tracking pixel opens</small>
                            </div>
                        </div>
                    </div>

                    {{-- Click Rate --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-cyan h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Click-Through (CTR)</span>
                                <span class="badge bg-info-subtle text-info font-monospace px-1 py-0" style="font-size: 10px;">
                                    {{ $campaign->click_rate }}%
                                </span>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-info bn-digit font-monospace stat-val-num">
                                    {{ number_format($campaign->clicked_count) }}
                                </h4>
                                <div class="progress mt-1 mb-1" style="height: 5px;">
                                    <div class="progress-bar bg-info" role="progressbar" style="width: {{ min($campaign->click_rate, 100) }}%"></div>
                                </div>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Direct store links clicked</small>
                            </div>
                        </div>
                    </div>

                    {{-- Attributed Revenue --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-revenue h-100 mb-0 shadow-xs">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-white text-opacity-75 stat-label-title text-truncate">Attributed Revenue</span>
                                <div class="metric-icon-square bg-white bg-opacity-25 text-white shadow-xs">
                                    <i class="fas fa-coins"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-white bn-digit font-monospace stat-val-num">
                                    ৳ {{ number_format($campaign->total_revenue, 2) }}
                                </h4>
                                <small class="text-white text-opacity-75 d-block text-truncate font-monospace stat-sub-text">
                                    Conversions from campaign
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Campaign Specifications & Audience Health --}}
    <div class="row g-3 mb-3">

        {{-- Metadata Info --}}
        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm h-100 mb-0">
                <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
                    <span class="fw-bold text-dark small" style="font-size: 12.5px;">
                        <i class="fas fa-circle-info text-primary me-1"></i> Campaign Specifications &amp; Targeting
                    </span>
                    <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseCampaignSpecs" aria-expanded="true" aria-controls="collapseCampaignSpecs" title="Toggle Specifications">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>
                </div>
                <div class="collapse show" id="collapseCampaignSpecs">
                    <div class="p-3">
                        <div class="row g-3 campaign-specs-grid" style="font-size: 12px;">

                            <div class="col-12 col-md-6">
                                <span class="spec-label">EMAIL SUBJECT</span>
                                <div class="spec-value fw-bold text-truncate" title="{{ $campaign->subject }}">
                                    {{ $campaign->subject }}
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <span class="spec-label">PREVIEW TEXT</span>
                                <div class="spec-value text-truncate" title="{{ $campaign->preview_text ?? 'None specified' }}">
                                    {{ $campaign->preview_text ?? 'None specified' }}
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <span class="spec-label">SENDER IDENTITY</span>
                                <div class="spec-value text-truncate">
                                    <strong>{{ $campaign->sender_name ?? config('mail.from.name') }}</strong>
                                    <span class="text-secondary">&lt;{{ $campaign->sender_email ?? config('mail.from.address') }}&gt;</span>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <span class="spec-label">TARGET AUDIENCE SEGMENT</span>
                                <div class="spec-value">
                                    @if($campaign->segment)
                                        <span class="badge bg-light text-primary border font-monospace px-2 py-1" style="font-size: 10px;">
                                            <i class="fas fa-filter me-1"></i> {{ $campaign->segment->name }}
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border font-monospace px-2 py-1" style="font-size: 10px;">
                                            <i class="fas fa-users me-1"></i> All Consented Subscribers
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <span class="spec-label">ATTACHED PROMOTION</span>
                                <div class="spec-value">
                                    @if($campaign->coupon)
                                        <span class="badge bg-warning-subtle text-dark border border-warning-subtle font-monospace px-2 py-1" style="font-size: 10px;">
                                            <i class="fas fa-ticket me-1 text-warning"></i> Coupon: {{ $campaign->coupon->code }}
                                        </span>
                                    @else
                                        <span class="text-muted font-monospace" style="font-size: 11px;">None attached</span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <span class="spec-label">SCHEDULE &amp; TIMESTAMPS</span>
                                <div class="spec-value font-monospace bn-digit" style="font-size: 11px; line-height: 1.6;">
                                    @if($campaign->scheduled_at)
                                        <span class="d-block">Scheduled: <strong>{{ $campaign->scheduled_at->format('M d, Y - h:i A') }}</strong></span>
                                    @endif
                                    @if($campaign->started_at)
                                        <span class="d-block text-primary">Started: {{ $campaign->started_at->format('M d, Y - h:i A') }}</span>
                                    @endif
                                    @if($campaign->completed_at)
                                        <span class="d-block text-success">Finished: {{ $campaign->completed_at->format('M d, Y - h:i A') }}</span>
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Negative Metrics & Audience Health --}}
        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm h-100 mb-0">
                <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
                    <span class="fw-bold text-dark small" style="font-size: 12px;">
                        <i class="fas fa-shield-halved text-warning me-1"></i> Audience Health Audit
                    </span>
                    <a href="{{ route('admin.email_marketing.campaigns.recipients', $campaign->id) }}" class="btn btn-sm btn-outline-primary btn-compact shadow-xs" style="font-size: 10px !important;">
                        <i class="fas fa-list me-1"></i> <span>Recipients</span>
                    </a>
                </div>
                <div class="p-3">
                    <div class="d-flex flex-column gap-2 audience-health-list" style="font-size: 12px;">
                        <div class="d-flex justify-content-between align-items-center p-2 bg-light border">
                            <span><i class="fas fa-ban text-danger me-2"></i> Bounced / Undelivered</span>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace bn-digit px-2 py-1" style="font-size: 10px;">
                                {{ number_format($campaign->bounced_count) }}
                            </span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center p-2 bg-light border">
                            <span><i class="fas fa-user-minus text-warning me-2"></i> Unsubscribed</span>
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle font-monospace bn-digit px-2 py-1" style="font-size: 10px;">
                                {{ number_format($campaign->unsubscribed_count) }}
                            </span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center p-2 bg-light border">
                            <span><i class="fas fa-clock text-secondary me-2"></i> Remaining in Queue</span>
                            <span class="badge bg-light text-dark border font-monospace bn-digit px-2 py-1" style="font-size: 10px;">
                                {{ number_format(max(0, $campaign->total_recipients - $campaign->sent_count)) }}
                            </span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center p-2 bg-light border">
                            <span><i class="fas fa-bolt text-info me-2"></i> Tracking Events Logged</span>
                            <span class="badge bg-info-subtle text-info border border-info-subtle font-monospace bn-digit px-2 py-1" style="font-size: 10px;">
                                {{ number_format($campaign->tracking_events_count ?? 0) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 5. Email Template Render Preview --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="fw-bold text-dark small" style="font-size: 12.5px;">
                    <i class="fas fa-palette text-primary me-1"></i> Email Template Visual Preview
                </span>
                @if($campaign->template)
                    <span class="badge bg-light text-secondary border font-monospace" style="font-size: 9.5px;">
                        {{ $campaign->template->name }}
                    </span>
                @endif
            </div>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseTemplatePreview" aria-expanded="true" aria-controls="collapseTemplatePreview" title="Toggle Preview">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseTemplatePreview">
            <div class="email-preview-area">
                <div class="email-render-preview-wrapper">
                    @if($campaign->template)
                        {!! $campaign->template->render([
                            'first_name'      => 'John',
                            'customer_name'   => 'John Doe',
                            'email'           => 'john.doe@example.com',
                            'coupon_code'     => $campaign->coupon->code ?? 'WELCOME10',
                            'unsubscribe_url' => '#',
                        ]) !!}
                    @else
                        <div class="text-center py-5 text-muted small">
                            <i class="fas fa-envelope-open fa-2x mb-2 d-block text-secondary opacity-25"></i>
                            <span>No design template attached to this campaign.</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Modal 1: Send Test Email Modal --}}
<div class="modal fade campaign-modal-scope" id="testMailModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom py-2 px-3 bg-light d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13.5px;">
                    <i class="fas fa-vial text-info"></i> <span>Send Test Email</span>
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.email_marketing.campaigns.send_test', $campaign->id) }}" method="POST">
                @csrf
                <div class="modal-body p-3">
                    <p class="small text-muted mb-2" style="font-size: 11px; line-height: 1.45;">
                        Send an instant live preview of <strong>{{ $campaign->name }}</strong> to verify template design, dynamic tags, and inbox rendering.
                    </p>
                    <label class="form-label">Recipient Test Email</label>
                    <input type="email" name="test_email" class="form-control font-monospace shadow-none" placeholder="your-email@example.com" value="{{ auth()->user()->email ?? '' }}" required>
                </div>
                <div class="modal-footer border-top py-2 px-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info btn-compact text-white shadow-xs fw-bold">Send Test</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal 2: Dynamic Action Confirmation Modal --}}
<div class="modal fade campaign-modal-scope" id="campaignConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mx-auto mb-2" id="campaignConfirmIconWrap" style="width: 44px; height: 44px; border-radius: 50% !important;">
                    <i class="fas fa-paper-plane fs-4" id="campaignConfirmIcon"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="campaignConfirmTitle" style="font-size: 14px;">Confirm Action</h6>
                <p class="text-muted small mb-3" id="campaignConfirmMsg" style="font-size: 11px; line-height: 1.45;"></p>
                <form id="campaignConfirmForm" method="POST" action="">
                    @csrf
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold" id="campaignConfirmSubmitBtn">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function triggerCampaignConfirm(title, message, actionUrl, type = 'dispatch') {
        const titleEl    = document.getElementById('campaignConfirmTitle');
        const msgEl      = document.getElementById('campaignConfirmMsg');
        const formEl     = document.getElementById('campaignConfirmForm');
        const btnEl      = document.getElementById('campaignConfirmSubmitBtn');
        const iconWrapEl = document.getElementById('campaignConfirmIconWrap');
        const iconEl     = document.getElementById('campaignConfirmIcon');

        if (titleEl) titleEl.innerText = title;
        if (msgEl) msgEl.innerText = message;
        if (formEl) formEl.action = actionUrl;

        if (type === 'danger') {
            iconWrapEl.className = 'rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-2';
            iconEl.className = 'fas fa-stop fs-4';
            btnEl.className = 'btn btn-danger btn-compact shadow-xs fw-bold';
            btnEl.innerText = 'Yes, Cancel';
        } else {
            iconWrapEl.className = 'rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mx-auto mb-2';
            iconEl.className = 'fas fa-paper-plane fs-4';
            btnEl.className = 'btn btn-success btn-compact shadow-xs fw-bold';
            btnEl.innerText = 'Yes, Dispatch';
        }

        const modalEl = document.getElementById('campaignConfirmModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }
</script>
@endsection