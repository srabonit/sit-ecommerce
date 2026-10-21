@extends('layouts.admin')

@section('title', 'A/B Testing - Email Marketing')

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #email-marketing-ab-testing-root .btn,
    #email-marketing-ab-testing-root .btn-sm,
    #email-marketing-ab-testing-root button,
    #email-marketing-ab-testing-root .badge,
    #email-marketing-ab-testing-root .alert,
    #email-marketing-ab-testing-root .card,
    #email-marketing-ab-testing-root .metric-card,
    #email-marketing-ab-testing-root .progress,
    #email-marketing-ab-testing-root .progress-bar,
    #email-marketing-ab-testing-root .collapse-toggle-btn {
        border-radius: 5px !important;
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
    #email-marketing-ab-testing-root .card {
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
    .metric-card-indigo { background: linear-gradient(180deg, #ffffff 0%, #eef2ff 100%); border-left: 3.5px solid #6366f1 !important; }
    .metric-card-amber  { background: linear-gradient(180deg, #ffffff 0%, #fffbeb 100%); border-left: 3.5px solid #f59e0b !important; }
    .metric-card-green  { background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%); border-left: 3.5px solid #10b981 !important; }

    /* Variant Simulation Box */
    .variant-sim-box {
        border: 1px solid #e2e8f0;
        background: #ffffff;
        padding: 12px;
        transition: all 0.2s ease;
    }
    .variant-sim-box:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
    }
    .variant-sim-box.winner-box {
        border-color: #86efac;
        background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%);
    }

    /* Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .ab-header-wrap h5 {
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
        #email-marketing-ab-testing-root .card {
            margin-bottom: 12px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-ab-testing-root">

    {{-- 1. Header & Quick Actions (Original Clean Light Theme) --}}
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

    {{-- 2. A/B Split Delivery Architecture KPI Cards (Collapsible Section) --}}
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

    {{-- 3. Live Demonstration Matrix: Variant A vs Variant B Simulation (Collapsible) --}}
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

    {{-- 4. Value Proposition & Launch CTA Card (Collapsible) --}}
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

    {{-- 5. Operational Guidance Card --}}
    <div class="card p-3 shadow-xs">
        <h6 class="fw-bold text-dark mb-1 small d-flex align-items-center gap-1">
            <i class="fas fa-circle-info text-primary"></i> <span>A/B Split Testing Best Practices</span>
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