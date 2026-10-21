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
    /* Strict 5px Element Radius Across All Controls */
    #email-marketing-campaign-show-root .btn,
    #email-marketing-campaign-show-root .btn-sm,
    #email-marketing-campaign-show-root button,
    #email-marketing-campaign-show-root .badge,
    #email-marketing-campaign-show-root .alert,
    #email-marketing-campaign-show-root .form-control,
    #email-marketing-campaign-show-root .form-select,
    #email-marketing-campaign-show-root .input-group-text,
    #email-marketing-campaign-show-root .progress,
    #email-marketing-campaign-show-root .progress-bar,
    #email-marketing-campaign-show-root .card,
    #email-marketing-campaign-show-root .metric-card,
    #email-marketing-campaign-show-root .collapse-toggle-btn,
    .campaign-modal-scope .modal-content,
    .campaign-modal-scope .btn {
        border-radius: 5px !important;
    }

    #email-marketing-campaign-show-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #email-marketing-campaign-show-root .input-group > .form-control {
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
    #email-marketing-campaign-show-root .card {
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
    .metric-card-revenue{ background: linear-gradient(135deg, #10b981 0%, #047857 100%); color: #ffffff; border: 1px solid #059669; }

    /* Form Controls */
    #email-marketing-campaign-show-root .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #email-marketing-campaign-show-root .form-control {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #email-marketing-campaign-show-root .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Email Preview Screen Container */
    .email-render-preview-wrapper {
        max-width: 680px;
        width: 100%;
        min-height: 360px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin: 0 auto;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    /* Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .campaign-show-header h5 {
            font-size: 16px !important;
        }
        .header-actions-wrap {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .header-actions-wrap .btn,
        .header-actions-wrap form {
            flex: 1 1 calc(50% - 6px);
        }
        .header-actions-wrap form .btn {
            width: 100%;
            justify-content: center;
        }
        .header-actions-wrap .btn-back-main {
            flex: 1 1 100%;
        }
        .metric-card {
            padding: 10px !important;
            min-height: 86px !important;
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
        #email-marketing-campaign-show-root .card {
            margin-bottom: 12px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-campaign-show-root">

    {{-- 1. Header & Context Actions (Original Clean Light Theme)[cite: 20] --}}
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

            {{-- Dispatch Now Action[cite: 20] --}}
            @if(in_array($campaign->status, ['draft', 'scheduled']))
                <button type="button" class="btn btn-sm btn-success btn-compact shadow-xs fw-bold" onclick="triggerCampaignConfirm('Dispatch Campaign', 'Are you sure you want to queue this campaign for immediate broadcast to all recipients?', '{{ route('admin.email_marketing.campaigns.dispatch_now', $campaign->id) }}', 'dispatch')">
                    <i class="fas fa-paper-plane me-1"></i> <span>Dispatch Now</span>
                </button>
            @endif

            {{-- Pause / Resume / Cancel Actions[cite: 20] --}}
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

            {{-- Test Send Modal Trigger[cite: 20] --}}
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

    {{-- 2. Feedback Messages[cite: 20] --}}
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

    {{-- 3. ROW 1: Real-Time Performance KPI Cards (Collapsible Section)[cite: 20] --}}
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
                    {{-- Delivered / Total Recipients[cite: 20] --}}
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

                    {{-- Open Rate[cite: 20] --}}
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

                    {{-- Click Rate[cite: 20] --}}
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

                    {{-- Attributed Revenue[cite: 20] --}}
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

    {{-- 4. ROW 2: Campaign Configuration Details & Negative Metrics (Collapsible)[cite: 20] --}}
    <div class="row g-3 mb-3">
        {{-- Metadata Info[cite: 20] --}}
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
                        <div class="row g-2" style="font-size: 12px;">
                            <div class="col-12 col-md-6">
                                <span class="text-muted d-block font-monospace" style="font-size: 10px;">EMAIL SUBJECT</span>
                                <div class="fw-bold text-dark mt-0.5 text-truncate" title="{{ $campaign->subject }}">
                                    {{ $campaign->subject }}
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <span class="text-muted d-block font-monospace" style="font-size: 10px;">PREVIEW TEXT</span>
                                <div class="text-dark mt-0.5 text-truncate" title="{{ $campaign->preview_text ?? 'None specified' }}">
                                    {{ $campaign->preview_text ?? 'None specified' }}
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <span class="text-muted d-block font-monospace" style="font-size: 10px;">SENDER IDENTITY</span>
                                <div class="text-dark mt-0.5 text-truncate">
                                    <strong>{{ $campaign->sender_name ?? config('mail.from.name') }}</strong> 
                                    <span class="text-secondary">&lt;{{ $campaign->sender_email ?? config('mail.from.address') }}&gt;</span>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <span class="text-muted d-block font-monospace" style="font-size: 10px;">TARGET AUDIENCE SEGMENT</span>
                                <div class="text-dark mt-0.5">
                                    @if($campaign->segment)
                                        <span class="badge bg-light text-primary border font-monospace px-2 py-0" style="font-size: 10px;">
                                            <i class="fas fa-filter me-1"></i> {{ $campaign->segment->name }}
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border font-monospace px-2 py-0" style="font-size: 10px;">
                                            <i class="fas fa-users me-1"></i> All Consented Subscribers
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <span class="text-muted d-block font-monospace" style="font-size: 10px;">ATTACHED PROMOTION</span>
                                <div class="text-dark mt-0.5">
                                    @if($campaign->coupon)
                                        <span class="badge bg-warning-subtle text-dark border border-warning-subtle font-monospace px-2 py-0" style="font-size: 10px;">
                                            <i class="fas fa-ticket me-1 text-warning"></i> Coupon: {{ $campaign->coupon->code }}
                                        </span>
                                    @else
                                        <span class="text-muted font-monospace" style="font-size: 11px;">None attached</span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <span class="text-muted d-block font-monospace" style="font-size: 10px;">SCHEDULE &amp; TIMESTAMPS</span>
                                <div class="text-dark mt-0.5 font-monospace bn-digit" style="font-size: 11px; line-height: 1.4;">
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

        {{-- Negative Metrics & Audience Health[cite: 20] --}}
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
                    <div class="d-flex flex-column gap-2" style="font-size: 12px;">
                        <div class="d-flex justify-content-between align-items-center p-2 bg-light border" style="border-radius: 5px;">
                            <span><i class="fas fa-ban text-danger me-2"></i> Bounced / Undelivered</span>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace bn-digit px-2 py-1" style="font-size: 10px;">
                                {{ number_format($campaign->bounced_count) }}
                            </span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center p-2 bg-light border" style="border-radius: 5px;">
                            <span><i class="fas fa-user-minus text-warning me-2"></i> Unsubscribed</span>
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle font-monospace bn-digit px-2 py-1" style="font-size: 10px;">
                                {{ number_format($campaign->unsubscribed_count) }}
                            </span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center p-2 bg-light border" style="border-radius: 5px;">
                            <span><i class="fas fa-clock text-secondary me-2"></i> Remaining in Queue</span>
                            <span class="badge bg-light text-dark border font-monospace bn-digit px-2 py-1" style="font-size: 10px;">
                                {{ number_format(max(0, $campaign->total_recipients - $campaign->sent_count)) }}
                            </span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center p-2 bg-light border" style="border-radius: 5px;">
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

    {{-- 5. ROW 3: Email Template Render Preview (Collapsible)[cite: 20] --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <div class="d-flex align-items-center gap-2">
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
            <div class="p-3 bg-light bg-opacity-50">
                <div class="email-render-preview-wrapper p-3 p-md-4" style="border-radius: 5px;">
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

{{-- Modal 1: Send Test Email Modal[cite: 20] --}}
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

{{-- Modal 2: Dynamic Action Confirmation Modal (Replaces Browser Confirm)[cite: 20] --}}
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