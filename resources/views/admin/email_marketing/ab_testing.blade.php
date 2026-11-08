
@extends('layouts.admin')

@section('title', 'A/B Testing - Email Marketing')

@section('admin_content')

<style>
    /* =========================================================
       KAIADMIN LITE v1.2.0 — CLASSIC / WHITE CLASSIC
       Page: Email Marketing A/B Testing
       Scoped CSS — Existing Functionality Preserved
    ========================================================= */

    #email-marketing-ab-testing-root {
        --ab-primary: var(--bs-primary, #1572e8);
        --ab-primary-soft: #eaf2ff;
        --ab-success: #31ce77;
        --ab-success-soft: #eaf9f0;
        --ab-warning: #ffad46;
        --ab-warning-soft: #fff6e8;
        --ab-indigo: #6861ce;
        --ab-indigo-soft: #f0efff;
        --ab-danger: #f25961;
        --ab-info: #48abf7;

        --ab-body-bg: #f5f7fd;
        --ab-card-bg: #ffffff;
        --ab-heading: #2a2f5b;
        --ab-text: #575962;
        --ab-muted: #8d9498;
        --ab-border: #ebedf2;

        --ab-radius: 5px;
        --ab-shadow: 0 1px 3px rgba(0, 0, 0, 0.035);

        color: var(--ab-text);
        font-size: 13px;
        min-width: 0;
        width: 100%;
    }

    #email-marketing-ab-testing-root *,
    #email-marketing-ab-testing-root *::before,
    #email-marketing-ab-testing-root *::after {
        box-sizing: border-box;
    }

    /* =========================
       GLOBAL ELEMENTS
    ========================= */

    #email-marketing-ab-testing-root .card,
    #email-marketing-ab-testing-root .btn,
    #email-marketing-ab-testing-root .badge,
    #email-marketing-ab-testing-root .alert,
    #email-marketing-ab-testing-root .progress,
    #email-marketing-ab-testing-root .progress-bar,
    #email-marketing-ab-testing-root .collapse-toggle-btn,
    #email-marketing-ab-testing-root .variant-sim-box {
        border-radius: var(--ab-radius) !important;
    }

    #email-marketing-ab-testing-root .card {
        background: var(--ab-card-bg);
        border: 1px solid var(--ab-border) !important;
        box-shadow: var(--ab-shadow) !important;
        margin-bottom: 16px;
        overflow: hidden;
    }

    #email-marketing-ab-testing-root .text-dark {
        color: var(--ab-heading) !important;
    }

    #email-marketing-ab-testing-root .text-muted {
        color: var(--ab-muted) !important;
    }

    #email-marketing-ab-testing-root .font-monospace,
    #email-marketing-ab-testing-root .bn-digit {
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }

    /* =========================
       PAGE HEADER
    ========================= */

    #email-marketing-ab-testing-root .ab-header-wrap {
        margin-bottom: 20px !important;
    }

    #email-marketing-ab-testing-root .ab-header-wrap h5 {
        color: var(--ab-heading);
        font-size: clamp(17px, 1.5vw, 20px) !important;
        font-weight: 700;
        letter-spacing: -0.35px;
    }

    #email-marketing-ab-testing-root .ab-header-wrap > div:first-child > small {
        color: var(--ab-muted) !important;
        font-size: 12px !important;
        line-height: 1.6 !important;
    }

    /* =========================
       BUTTONS
    ========================= */

    #email-marketing-ab-testing-root .btn-compact {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        min-height: 34px;
        padding: 7px 13px !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        line-height: 1.35 !important;
        white-space: nowrap;
        border-radius: var(--ab-radius) !important;
        transition: all 0.18s ease;
    }

    #email-marketing-ab-testing-root .btn-primary {
        background-color: var(--ab-primary);
        border-color: var(--ab-primary);
    }

    #email-marketing-ab-testing-root .btn-primary:hover {
        filter: brightness(0.94);
        box-shadow: 0 3px 8px rgba(21, 114, 232, 0.16);
    }

    #email-marketing-ab-testing-root .btn-outline-secondary {
        border-color: #dce0e8;
        color: #575962;
        background: #ffffff;
    }

    #email-marketing-ab-testing-root .btn-outline-secondary:hover {
        background: #f5f7fd;
        border-color: #cbd1dc;
        color: var(--ab-heading);
    }

    #email-marketing-ab-testing-root .btn-light {
        background: #ffffff;
        border-color: var(--ab-border) !important;
        color: var(--ab-text) !important;
    }

    #email-marketing-ab-testing-root .btn-light:hover {
        background: #f5f7fd;
    }

    #email-marketing-ab-testing-root .top-action-btns {
        flex-shrink: 0;
    }

    /* =========================
       SECTION HEADER
    ========================= */

    #email-marketing-ab-testing-root .section-header-wrap {
        min-height: 47px;
        padding: 11px 16px;
        background: #ffffff;
        border-bottom: 1px solid var(--ab-border);
        gap: 10px;
    }

    #email-marketing-ab-testing-root .section-header-wrap > span {
        color: var(--ab-heading) !important;
        font-size: 12.5px !important;
        font-weight: 700 !important;
        line-height: 1.5;
    }

    #email-marketing-ab-testing-root .section-header-wrap > span i {
        font-size: 13px;
    }

    /* =========================
       COLLAPSE BUTTON
    ========================= */

    #email-marketing-ab-testing-root .collapse-toggle-btn {
        width: 27px;
        height: 27px;
        min-width: 27px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #ffffff;
        border: 1px solid #e1e5ec;
        color: #7b8190;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    #email-marketing-ab-testing-root .collapse-toggle-btn:hover {
        background: var(--ab-primary-soft);
        border-color: var(--ab-primary);
        color: var(--ab-primary);
    }

    #email-marketing-ab-testing-root .collapse-icon {
        font-size: 10px;
        transition: transform 0.25s ease;
    }

    #email-marketing-ab-testing-root
    .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    /* =========================
       KPI / METRIC CARDS
    ========================= */

    #email-marketing-ab-testing-root .metric-card {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        min-width: 0;
        padding: 14px !important;
        background: #ffffff;
        border: 1px solid var(--ab-border) !important;
        border-left: 3px solid var(--ab-primary) !important;
        box-shadow: none !important;
        overflow: hidden;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    #email-marketing-ab-testing-root .metric-card:hover {
        border-color: #d5dce8 !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.045) !important;
    }

    #email-marketing-ab-testing-root .metric-card-blue {
        border-left-color: var(--ab-primary) !important;
    }

    #email-marketing-ab-testing-root .metric-card-indigo {
        border-left-color: var(--ab-indigo) !important;
    }

    #email-marketing-ab-testing-root .metric-card-amber {
        border-left-color: var(--ab-warning) !important;
    }

    #email-marketing-ab-testing-root .metric-card-green {
        border-left-color: var(--ab-success) !important;
    }

    #email-marketing-ab-testing-root .stat-label-title {
        font-size: 11px;
        font-weight: 600;
        line-height: 1.4;
        color: var(--ab-muted) !important;
        letter-spacing: 0.1px;
    }

    #email-marketing-ab-testing-root .stat-val-num {
        font-size: clamp(17px, 1.5vw, 21px);
        line-height: 1.4;
        letter-spacing: -0.5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: var(--ab-heading) !important;
    }

    #email-marketing-ab-testing-root .stat-val-num > span {
        font-size: 11px !important;
        letter-spacing: 0;
    }

    #email-marketing-ab-testing-root .stat-sub-text {
        display: block;
        margin-top: 4px;
        font-size: 10px;
        line-height: 1.5;
        color: var(--ab-muted) !important;
    }

    #email-marketing-ab-testing-root .metric-icon-square {
        width: 34px;
        height: 34px;
        min-width: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--ab-radius) !important;
        font-size: 13px;
        flex-shrink: 0;
    }

    #email-marketing-ab-testing-root .metric-card-blue .metric-icon-square {
        background: var(--ab-primary-soft) !important;
        color: var(--ab-primary) !important;
    }

    #email-marketing-ab-testing-root .metric-card-indigo .metric-icon-square {
        background: var(--ab-indigo-soft) !important;
        color: var(--ab-indigo) !important;
    }

    #email-marketing-ab-testing-root .metric-card-amber .metric-icon-square {
        background: var(--ab-warning-soft) !important;
        color: #d58b20 !important;
    }

    #email-marketing-ab-testing-root .metric-card-green .metric-icon-square {
        background: var(--ab-success-soft) !important;
        color: #20a65b !important;
    }

    #email-marketing-ab-testing-root .metric-card-amber .stat-val-num {
        color: #c58a27 !important;
    }

    #email-marketing-ab-testing-root .metric-card-green .stat-val-num {
        color: #20a65b !important;
    }

    #email-marketing-ab-testing-root .progress {
        height: 5px !important;
        background: #edf0f5;
        overflow: hidden;
    }

    #email-marketing-ab-testing-root .progress-bar {
        border-radius: 0 !important;
    }

    #email-marketing-ab-testing-root .metric-card-indigo .progress-bar {
        background-color: var(--ab-indigo) !important;
    }

    /* =========================
       VARIANT COMPARISON
    ========================= */

    #email-marketing-ab-testing-root .variant-sim-box {
        height: 100%;
        min-width: 0;
        padding: 14px !important;
        background: #ffffff;
        border: 1px solid var(--ab-border) !important;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    #email-marketing-ab-testing-root .variant-sim-box:hover {
        border-color: #d4dce8 !important;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.035);
    }

    #email-marketing-ab-testing-root .variant-sim-box.winner-box {
        background: #ffffff;
        border-color: #bce8ce !important;
    }

    #email-marketing-ab-testing-root .variant-sim-box .badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 22px;
        padding: 4px 8px !important;
        font-size: 9.5px !important;
        font-weight: 700;
        letter-spacing: 0.2px;
    }

    #email-marketing-ab-testing-root .variant-sim-box strong {
        line-height: 1.5;
    }

    #email-marketing-ab-testing-root .variant-sim-box .p-2.bg-light,
    #email-marketing-ab-testing-root .variant-sim-box .p-2.bg-white {
        padding: 11px !important;
        border-radius: var(--ab-radius) !important;
        overflow-wrap: anywhere;
    }

    #email-marketing-ab-testing-root .variant-sim-box .p-2 small {
        font-size: 10px !important;
        font-weight: 600;
        letter-spacing: 0.3px;
        margin-bottom: 4px;
    }

    #email-marketing-ab-testing-root .variant-sim-box .p-2 span {
        display: block;
        font-size: 12px !important;
        line-height: 1.6;
        overflow-wrap: anywhere;
    }

    #email-marketing-ab-testing-root .variant-sim-box .border-top {
        border-color: var(--ab-border) !important;
    }

    #email-marketing-ab-testing-root .variant-sim-box .border-end {
        border-color: var(--ab-border) !important;
    }

    #email-marketing-ab-testing-root .variant-sim-box .row {
        margin-top: 8px;
    }

    #email-marketing-ab-testing-root .variant-sim-box .row small {
        font-size: 10px !important;
        letter-spacing: 0.2px;
        margin-bottom: 3px;
    }

    #email-marketing-ab-testing-root .variant-sim-box .row span {
        font-size: 15px !important;
    }

    /* =========================
       SUCCESS ALERT
    ========================= */

    #email-marketing-ab-testing-root .alert-success {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px 14px !important;
        background: var(--ab-success-soft);
        border: 1px solid #d5f0df !important;
        color: #267c4b;
        font-size: 11px !important;
        line-height: 1.65;
    }

    #email-marketing-ab-testing-root .alert-success strong {
        font-weight: 700;
    }

    #email-marketing-ab-testing-root .alert-success i {
        margin-top: 1px;
    }

    /* =========================
       LAUNCH CTA
    ========================= */

    #email-marketing-ab-testing-root #collapseLaunchCta .p-4 {
        padding: clamp(20px, 4vw, 34px) 16px !important;
    }

    #email-marketing-ab-testing-root #collapseLaunchCta .bg-primary-subtle {
        background: var(--ab-primary-soft) !important;
        color: var(--ab-primary) !important;
    }

    #email-marketing-ab-testing-root #collapseLaunchCta h6 {
        color: var(--ab-heading) !important;
        font-size: 15px !important;
        line-height: 1.5;
    }

    #email-marketing-ab-testing-root #collapseLaunchCta p {
        color: var(--ab-muted) !important;
        font-size: 12px !important;
        line-height: 1.7 !important;
    }

    /* =========================
       BEST PRACTICES
    ========================= */

    #email-marketing-ab-testing-root > .card:last-child {
        padding: 17px !important;
        background: #ffffff;
        border: 1px solid var(--ab-border) !important;
    }

    #email-marketing-ab-testing-root > .card:last-child h6 {
        color: var(--ab-heading) !important;
        font-size: 12.5px !important;
        margin-bottom: 12px !important;
    }

    #email-marketing-ab-testing-root > .card:last-child p {
        color: var(--ab-text) !important;
        font-size: 11px !important;
        line-height: 1.7 !important;
        padding-left: 2px;
    }

    #email-marketing-ab-testing-root > .card:last-child p strong {
        color: var(--ab-heading);
        font-weight: 700;
    }

    /* =========================
       TABLET RESPONSIVENESS
    ========================= */

    @media (max-width: 991.98px) {
        #email-marketing-ab-testing-root .metric-card {
            padding: 12px !important;
        }

        #email-marketing-ab-testing-root .section-header-wrap {
            padding: 10px 13px;
        }
    }

    /* =========================
       MOBILE RESPONSIVENESS
    ========================= */

    @media (max-width: 767.98px) {
        #email-marketing-ab-testing-root {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        #email-marketing-ab-testing-root .ab-header-wrap {
            align-items: flex-start !important;
            gap: 12px !important;
            margin-bottom: 15px !important;
        }

        #email-marketing-ab-testing-root .ab-header-wrap h5 {
            font-size: 17px !important;
        }

        #email-marketing-ab-testing-root .top-action-btns {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            width: 100%;
            gap: 8px !important;
        }

        #email-marketing-ab-testing-root .top-action-btns .btn {
            width: 100%;
            min-width: 0;
            min-height: 38px;
            padding: 7px 5px !important;
            font-size: 10.5px !important;
            white-space: normal;
            text-align: center;
        }

        #email-marketing-ab-testing-root .card {
            margin-bottom: 12px !important;
        }

        #email-marketing-ab-testing-root .section-header-wrap {
            min-height: 45px;
            padding: 9px 11px;
        }

        #email-marketing-ab-testing-root .section-header-wrap > span {
            font-size: 11.5px !important;
        }

        #email-marketing-ab-testing-root .section-header-wrap > span i {
            font-size: 12px;
        }

        #email-marketing-ab-testing-root .metric-card {
            min-height: 105px;
            padding: 11px !important;
        }

        #email-marketing-ab-testing-root .stat-label-title {
            font-size: 10px;
        }

        #email-marketing-ab-testing-root .stat-val-num {
            font-size: 17px;
        }

        #email-marketing-ab-testing-root .stat-sub-text {
            font-size: 9.5px;
        }

        #email-marketing-ab-testing-root .metric-icon-square {
            width: 29px;
            height: 29px;
            min-width: 29px;
            font-size: 11px;
        }

        #email-marketing-ab-testing-root .variant-sim-box {
            padding: 12px !important;
        }

        #email-marketing-ab-testing-root .variant-sim-box .d-flex.justify-content-between {
            gap: 7px;
            flex-wrap: wrap;
        }

        #email-marketing-ab-testing-root .variant-sim-box .d-flex.align-items-center.gap-2 {
            min-width: 0;
            flex-wrap: wrap;
        }

        #email-marketing-ab-testing-root .variant-sim-box .d-flex.align-items-center strong {
            font-size: 11px !important;
        }

        #email-marketing-ab-testing-root .variant-sim-box .badge {
            font-size: 9px !important;
        }

        #email-marketing-ab-testing-root .variant-sim-box .p-2 span {
            font-size: 11.5px !important;
        }

        #email-marketing-ab-testing-root .alert-success {
            padding: 10px !important;
            font-size: 10.5px !important;
        }

        #email-marketing-ab-testing-root > .card:last-child {
            padding: 13px !important;
        }
    }

    /* =========================
       SMALL MOBILE
    ========================= */

    @media (max-width: 374.98px) {
        #email-marketing-ab-testing-root .container-fluid {
            padding-left: 7px !important;
            padding-right: 7px !important;
        }

        #email-marketing-ab-testing-root .metric-card {
            padding: 9px !important;
            min-height: 100px;
        }

        #email-marketing-ab-testing-root .stat-val-num {
            font-size: 15px;
        }

        #email-marketing-ab-testing-root .stat-val-num > span {
            font-size: 9px !important;
        }

        #email-marketing-ab-testing-root .metric-icon-square {
            width: 25px;
            height: 25px;
            min-width: 25px;
        }

        #email-marketing-ab-testing-root .top-action-btns .btn {
            font-size: 9.5px !important;
        }
    }

    /* =========================
       LARGE DESKTOP
    ========================= */

    @media (min-width: 1400px) {
        #email-marketing-ab-testing-root .metric-card {
            padding: 16px !important;
        }

        #email-marketing-ab-testing-root .variant-sim-box {
            padding: 16px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-ab-testing-root">

    {{-- 1. Header & Quick Actions --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 ab-header-wrap">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-flask-vial text-primary"></i>
                <span>A/B Split Testing</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Test multiple subject lines and email design variations to maximize opens and conversions.
            </small>
        </div>

        <div class="d-flex gap-2 top-action-btns">
            <a href="{{ route('admin.email_marketing.campaigns.index') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-paper-plane me-1"></i> <span>All Campaigns</span>
            </a>
            <a href="{{ route('admin.email_marketing.campaigns.create') }}" class="btn btn-sm btn-primary btn-compact shadow-xs fw-bold">
                <i class="fas fa-plus-circle me-1"></i> <span>New A/B Campaign</span>
            </a>
        </div>
    </div>

    {{-- 2. A/B Split Delivery Architecture KPI Cards --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-chart-pie text-primary me-1"></i> Automated 20/20/60 Split Testing Architecture
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseSplitArchitecture" aria-expanded="true" aria-controls="collapseSplitArchitecture" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>

        <div class="collapse show" id="collapseSplitArchitecture">
            <div class="p-3">
                <div class="row g-2">

                    {{-- Variant A Slice --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-blue h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Sample Slice A</span>
                                <div class="metric-icon-square bg-primary-subtle text-primary shadow-xs">
                                    <span class="fw-bold font-monospace" style="font-size: 12px;">A</span>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                                    20% <span style="font-size: 11px;" class="fw-normal text-muted">Audience</span>
                                </h4>
                                <div class="progress mt-1 mb-1" style="height: 4px;">
                                    <div class="progress-bar bg-primary" style="width: 20%;"></div>
                                </div>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Variant Subject #1</small>
                            </div>
                        </div>
                    </div>

                    {{-- Variant B Slice --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-indigo h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Sample Slice B</span>
                                <div class="metric-icon-square bg-indigo-subtle text-primary shadow-xs" style="background-color: #e0e7ff; color: #4338ca;">
                                    <span class="fw-bold font-monospace" style="font-size: 12px;">B</span>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                                    20% <span style="font-size: 11px;" class="fw-normal text-muted">Audience</span>
                                </h4>
                                <div class="progress mt-1 mb-1" style="height: 4px;">
                                    <div class="progress-bar bg-indigo" style="width: 20%; background-color: #6366f1;"></div>
                                </div>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Variant Subject #2</small>
                            </div>
                        </div>
                    </div>

                    {{-- Evaluation Window --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-amber h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Testing Window</span>
                                <div class="metric-icon-square bg-warning-subtle text-warning-emphasis shadow-xs">
                                    <i class="fas fa-stopwatch"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-warning-emphasis bn-digit font-monospace stat-val-num">
                                    2 to 4 <span style="font-size: 11px;" class="fw-normal text-muted">Hours</span>
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text mt-1">Real-time open &amp; click tracking</small>
                            </div>
                        </div>
                    </div>

                    {{-- Winner Automated Rollout --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-green h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Winning Rollout</span>
                                <div class="metric-icon-square bg-success-subtle text-success shadow-xs">
                                    <i class="fas fa-crown"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-success bn-digit font-monospace stat-val-num">
                                    60% <span style="font-size: 11px;" class="fw-normal text-muted">Remaining</span>
                                </h4>
                                <div class="progress mt-1 mb-1" style="height: 4px;">
                                    <div class="progress-bar bg-success" style="width: 60%;"></div>
                                </div>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Auto-sent to remaining users</small>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- 3. Variant A vs Variant B Simulation --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12.5px;">
                <i class="fas fa-vial-circle-check text-success me-1"></i> Live Variant Simulation &amp; Decision Matrix
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseVariantDemo" aria-expanded="true" aria-controls="collapseVariantDemo" title="Toggle Demo">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>

        <div class="collapse show" id="collapseVariantDemo">
            <div class="p-3">
                <div class="row g-2">

                    {{-- Variant A Card --}}
                    <div class="col-12 col-md-6">
                        <div class="variant-sim-box h-100" style="border-radius: 5px;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary text-white font-monospace px-2 py-1" style="font-size: 10px;">
                                        VARIANT A
                                    </span>
                                    <strong class="text-dark small" style="font-size: 12px;">Curiosity Subject Angle</strong>
                                </div>
                                <span class="badge bg-light text-muted border font-monospace" style="font-size: 9.5px;">20% Senders</span>
                            </div>

                            <div class="p-2 bg-light border mb-2" style="border-radius: 5px;">
                                <small class="text-muted d-block font-monospace" style="font-size: 10px;">SUBJECT LINE:</small>
                                <span class="text-dark fw-semibold" style="font-size: 12px;">"Did you see our latest collection drop?"</span>
                            </div>

                            <div class="row g-2 text-center pt-1 border-top">
                                <div class="col-6 border-end">
                                    <small class="text-muted d-block font-monospace" style="font-size: 10px;">OPEN RATE</small>
                                    <span class="fw-bold text-dark font-monospace bn-digit" style="font-size: 14px;">21.4%</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block font-monospace" style="font-size: 10px;">CLICK RATE</small>
                                    <span class="fw-bold text-dark font-monospace bn-digit" style="font-size: 14px;">3.8%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Variant B Card (Winner) --}}
                    <div class="col-12 col-md-6">
                        <div class="variant-sim-box winner-box h-100" style="border-radius: 5px;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-success text-white font-monospace px-2 py-1" style="font-size: 10px;">
                                        VARIANT B
                                    </span>
                                    <strong class="text-success small" style="font-size: 12px;">Urgency &amp; Discount Angle</strong>
                                </div>
                                <span class="badge bg-success text-white font-monospace" style="font-size: 9.5px;">
                                    <i class="fas fa-crown me-1"></i> WINNER
                                </span>
                            </div>

                            <div class="p-2 bg-white border border-success border-opacity-50 mb-2" style="border-radius: 5px;">
                                <small class="text-muted d-block font-monospace" style="font-size: 10px;">SUBJECT LINE:</small>
                                <span class="text-dark fw-bold" style="font-size: 12px;">"🔥 Flat 30% Off Everything - Today Only!"</span>
                            </div>

                            <div class="row g-2 text-center pt-1 border-top">
                                <div class="col-6 border-end">
                                    <small class="text-muted d-block font-monospace" style="font-size: 10px;">OPEN RATE</small>
                                    <span class="fw-bold text-success font-monospace bn-digit" style="font-size: 14px;">38.6%</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block font-monospace" style="font-size: 10px;">CLICK RATE</small>
                                    <span class="fw-bold text-success font-monospace bn-digit" style="font-size: 14px;">8.2%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="alert alert-success border-0 shadow-xs mt-3 mb-0 py-2 px-3 small d-flex align-items-center gap-2" style="border-radius: 5px; font-size: 11px;">
                    <i class="fas fa-circle-check text-success fs-5 flex-shrink-0"></i>
                    <div>
                        <strong>Engine Result:</strong> Variant B outperformed Variant A by <strong>+80.3%</strong> higher opens. The system automatically dispatched Variant B to the remaining 60% audience without manual intervention.
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Launch Campaign CTA --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12.5px;">
                <i class="fas fa-rocket text-primary me-1"></i> Launch Your First A/B Test Campaign
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseLaunchCta" aria-expanded="true" aria-controls="collapseLaunchCta" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>

        <div class="collapse show" id="collapseLaunchCta">
            <div class="p-4 text-center">
                <div class="bg-primary-subtle text-primary p-3 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 56px; height: 56px; border-radius: 50% !important;">
                    <i class="fas fa-flask-vial fs-3"></i>
                </div>

                <h6 class="fw-bold text-dark mb-1" style="font-size: 15px;">Automated A/B Variant Optimization</h6>

                <p class="text-muted mx-auto mb-3" style="max-width: 540px; font-size: 12px; line-height: 1.55;">
                    Eliminate guesswork from your marketing strategy. Test multiple subject lines, sender names, or email designs to discover what resonates best with your verified shoppers.
                </p>

                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <a href="{{ route('admin.email_marketing.campaigns.create') }}" class="btn btn-primary btn-compact shadow-xs fw-bold px-4 py-2" style="font-size: 12px !important;">
                        <i class="fas fa-plus-circle me-1"></i> Launch A/B Test Campaign
                    </a>

                    <a href="{{ route('admin.email_marketing.campaigns.index') }}" class="btn btn-light border btn-compact shadow-xs text-secondary px-3 py-2">
                        View Active Broadcasts
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- 5. Operational Guidance --}}
    <div class="card p-3 shadow-xs">
        <h6 class="fw-bold text-dark mb-1 small d-flex align-items-center gap-1">
            <i class="fas fa-circle-info text-primary"></i>
            <span>A/B Split Testing Best Practices</span>
        </h6>

        <p class="text-muted small mb-1" style="font-size: 11px; line-height: 1.4;">
            &bull; <strong>Test One Variable at a Time:</strong> Test either two different subject lines OR two different template layouts, not both simultaneously.
        </p>

        <p class="text-muted small mb-0" style="font-size: 11px; line-height: 1.4;">
            &bull; <strong>Sufficient Sample Size:</strong> A/B split testing is most effective with audience segments containing at least <strong>500+ subscribers</strong> for statistically significant results.
        </p>
    </div>

</div>
@endsection
