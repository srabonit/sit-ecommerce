@extends('layouts.admin')

@section('title', 'Email Marketing Settings')

@php
    $settings = $settings ?? ($generalSettings ?? []);

    // Dynamic fallbacks with DB/Config alignment
    $fromNameDefault  = data_get($settings, 'default_sender_name') ?? config('mail.from.name', 'SHOP NOW BD');
    $fromEmailDefault = data_get($settings, 'default_sender_email') ?? config('mail.from.address', 'newsletter@shopnowbd.online');
    $replyToDefault   = data_get($settings, 'reply_to_email') ?? '';
    $batchSizeDefault = (int) (data_get($settings, 'default_batch_size') ?? 50);
    $cartThresholdDef = (int) (data_get($settings, 'cart_abandon_threshold_minutes') ?? 60);

    $trackingSettingsRoute = Route::has('admin.email_marketing.tracking_settings') 
        ? route('admin.email_marketing.tracking_settings') 
        : url('admin/email-marketing/tracking-settings');

    $settingsUpdateRoute = Route::has('admin.email_marketing.settings.update') 
        ? route('admin.email_marketing.settings.update') 
        : url('admin/email-marketing/settings');

    $providersRoute = Route::has('admin.email_marketing.providers') 
        ? route('admin.email_marketing.providers') 
        : url('admin/email-marketing/providers');

    $suppressionRoute = Route::has('admin.email_marketing.suppression') 
        ? route('admin.email_marketing.suppression') 
        : url('admin/email-marketing/suppression');
@endphp

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #email-marketing-general-settings-root .btn,
    #email-marketing-general-settings-root .btn-sm,
    #email-marketing-general-settings-root button,
    #email-marketing-general-settings-root .badge,
    #email-marketing-general-settings-root .alert,
    #email-marketing-general-settings-root .form-control,
    #email-marketing-general-settings-root .form-select,
    #email-marketing-general-settings-root .input-group-text,
    #email-marketing-general-settings-root .card,
    #email-marketing-general-settings-root .metric-card,
    #email-marketing-general-settings-root .collapse-toggle-btn,
    .settings-modal-scope .modal-content,
    .settings-modal-scope .btn {
        border-radius: 5px !important;
    }

    #email-marketing-general-settings-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #email-marketing-general-settings-root .input-group > .form-control,
    #email-marketing-general-settings-root .input-group > .form-select {
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
        font-size: 11.5px !important;
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
    #email-marketing-general-settings-root .card {
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
    .metric-card-amber  { background: linear-gradient(180deg, #ffffff 0%, #fffbeb 100%); border-left: 3.5px solid #f59e0b !important; }
    .metric-card-cyan   { background: linear-gradient(180deg, #ffffff 0%, #ecfeff 100%); border-left: 3.5px solid #06b6d4 !important; }

    /* Form Controls */
    #email-marketing-general-settings-root .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #email-marketing-general-settings-root .form-control,
    #email-marketing-general-settings-root .form-select {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #email-marketing-general-settings-root .form-control:focus,
    #email-marketing-general-settings-root .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }
    .settings-addon-text {
        height: 32px !important;
        background-color: #f8fafc !important;
        border-color: #cbd5e1 !important;
        color: #64748b !important;
        font-size: 11px !important;
    }

    /* 100/100 Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .settings-header-wrap h5 {
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
        #email-marketing-general-settings-root .card {
            margin-bottom: 12px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-general-settings-root">

    {{-- 1. Header & Actions Toolbar --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 settings-header-wrap">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-sliders text-primary"></i>
                <span>Email Marketing Settings</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Global store identity, delivery rate limiting, and cart recovery timings.
            </small>
        </div>

        <div class="d-flex gap-2 top-action-btns">
            <a href="{{ $trackingSettingsRoute }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-chart-pie me-1"></i> <span>Tracking Settings</span>
            </a>
            <button type="button" class="btn btn-sm btn-primary btn-compact shadow-xs fw-bold" id="btn-save-general-settings" onclick="submitSettingsForm()">
                <i class="fas fa-save me-1"></i> <span>Save Configuration</span>
            </button>
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

    {{-- 3. Telemetry KPI Overview (Collapsible Section) --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-chart-pie text-primary me-1"></i> Engine Configuration &amp; Telemetry Status
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseSettingsKpis" aria-expanded="true" aria-controls="collapseSettingsKpis" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseSettingsKpis">
            <div class="p-3">
                <div class="row g-2">
                    {{-- Sender Identity --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-blue h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Default Sender</span>
                                <div class="metric-icon-square bg-primary-subtle text-primary shadow-xs">
                                    <i class="fas fa-id-card"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark text-truncate font-monospace stat-val-num" title="{{ $fromNameDefault }}">
                                    {{ $fromNameDefault }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text" title="{{ $fromEmailDefault }}">
                                    {{ $fromEmailDefault }}
                                </small>
                            </div>
                        </div>
                    </div>

                    {{-- Dispatch Chunk Size --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-green h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Queue Batch Rate</span>
                                <div class="metric-icon-square bg-success-subtle text-success shadow-xs">
                                    <i class="fas fa-gauge-high"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-success bn-digit font-monospace stat-val-num">
                                    {{ $batchSizeDefault }} <span style="font-size: 11px;" class="fw-normal text-muted">per cycle</span>
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Background worker chunk</small>
                            </div>
                        </div>
                    </div>

                    {{-- Abandoned Inactivity Trigger --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-amber h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Cart Inactivity</span>
                                <div class="metric-icon-square bg-warning-subtle text-warning-emphasis shadow-xs">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-warning-emphasis bn-digit font-monospace stat-val-num">
                                    {{ $cartThresholdDef }} <span style="font-size: 11px;" class="fw-normal text-muted">Minutes</span>
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Abandonment threshold</small>
                            </div>
                        </div>
                    </div>

                    {{-- Suppression Engine --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-cyan h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Compliance Filter</span>
                                <div class="metric-icon-square bg-info-subtle text-info shadow-xs">
                                    <i class="fas fa-shield-halved"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-info font-monospace stat-val-num" style="font-size: 15px;">
                                    <i class="fas fa-check-circle me-1"></i> Active
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Bounce &amp; opt-out shield</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Main Configuration Form --}}
    <form action="{{ $settingsUpdateRoute }}" method="POST" id="generalSettingsForm">
        @csrf

        <div class="row g-3">
            {{-- Left Column: Primary Settings Form --}}
            <div class="col-12 col-xl-8">
                {{-- Store Sender Identity (Collapsible) --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
                        <span class="fw-bold text-dark small" style="font-size: 12px;">
                            <i class="fas fa-id-card text-primary me-1"></i> 1. Default Store Sender Identity
                        </span>
                        <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseSenderIdentity" aria-expanded="true" aria-controls="collapseSenderIdentity" title="Toggle Section">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>
                    <div class="collapse show" id="collapseSenderIdentity">
                        <div class="p-3">
                            <div class="row g-2">
                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="default_sender_name">
                                        From Sender Name <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text settings-addon-text"><i class="fas fa-user-tag text-muted" style="font-size: 11px;"></i></span>
                                        <input type="text" name="default_sender_name" id="default_sender_name" class="form-control" value="{{ $fromNameDefault }}" placeholder="SHOP NOW BD" required>
                                    </div>
                                    <small class="text-muted font-monospace d-block mt-1" style="font-size: 10px;">The friendly display name customers see in their email inbox.</small>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="default_sender_email">
                                        From Email Address <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text settings-addon-text"><i class="fas fa-envelope text-muted" style="font-size: 11px;"></i></span>
                                        <input type="email" name="default_sender_email" id="default_sender_email" class="form-control font-monospace" value="{{ $fromEmailDefault }}" placeholder="newsletter@shopnowbd.online" required>
                                    </div>
                                    <small class="text-muted font-monospace d-block mt-1" style="font-size: 10px;">Must be a verified sender domain configured in your SMTP / mail server.</small>
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="reply_to_email">
                                        Reply-To Email Address (Optional)
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text settings-addon-text"><i class="fas fa-reply text-muted" style="font-size: 11px;"></i></span>
                                        <input type="email" name="reply_to_email" id="reply_to_email" class="form-control font-monospace" value="{{ $replyToDefault }}" placeholder="support@shopnowbd.online">
                                    </div>
                                    <small class="text-muted font-monospace d-block mt-1" style="font-size: 10px;">Where customer direct email replies will be routed (helpdesk or support inbox).</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Dispatch Engine Parameters (Collapsible) --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
                        <span class="fw-bold text-dark small" style="font-size: 12px;">
                            <i class="fas fa-gauge-high text-warning me-1"></i> 2. Delivery Speed &amp; Rate Limits
                        </span>
                        <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseDeliverySpeed" aria-expanded="true" aria-controls="collapseDeliverySpeed" title="Toggle Section">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>
                    <div class="collapse show" id="collapseDeliverySpeed">
                        <div class="p-3">
                            <div class="row g-2">
                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="default_batch_size">
                                        Default Batch / Chunk Size
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text settings-addon-text"><i class="fas fa-boxes-stacked text-muted" style="font-size: 11px;"></i></span>
                                        <input type="number" name="default_batch_size" id="default_batch_size" class="form-control font-monospace bn-digit" value="{{ $batchSizeDefault }}" min="10" max="500">
                                    </div>
                                    <small class="text-muted font-monospace d-block mt-1" style="font-size: 10px;">Number of emails dispatched per background queue cycle (Recommended: 50 - 100).</small>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="cart_abandon_threshold_minutes">
                                        Abandoned Cart Inactivity Threshold
                                    </label>
                                    <div class="input-group">
                                        <input type="number" name="cart_abandon_threshold_minutes" id="cart_abandon_threshold_minutes" class="form-control font-monospace bn-digit" value="{{ $cartThresholdDef }}" min="15">
                                        <span class="input-group-text settings-addon-text font-monospace" style="font-size: 11px;">Minutes</span>
                                    </div>
                                    <small class="text-muted font-monospace d-block mt-1" style="font-size: 10px;">Time elapsed without checkout activity before marking a cart session as abandoned.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Infrastructure Links & Operational Guidance --}}
            <div class="col-12 col-xl-4">
                {{-- Quick Infrastructure Navigation --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
                        <span class="fw-bold text-dark small" style="font-size: 12px;">
                            <i class="fas fa-link text-primary me-1"></i> Quick Management Links
                        </span>
                    </div>
                    <div class="p-3">
                        <div class="d-flex flex-column gap-2">
                            <a href="{{ $providersRoute }}" class="btn btn-light border btn-compact w-100 justify-content-start py-2 text-dark shadow-xs fw-semibold">
                                <i class="fas fa-server me-2 text-primary"></i> <span>Configure Mail Providers</span>
                            </a>
                            <a href="{{ $suppressionRoute }}" class="btn btn-light border btn-compact w-100 justify-content-start py-2 text-danger shadow-xs fw-semibold">
                                <i class="fas fa-ban me-2 text-danger"></i> <span>Suppression &amp; Blacklist</span>
                            </a>
                            <a href="{{ $trackingSettingsRoute }}" class="btn btn-light border btn-compact w-100 justify-content-start py-2 text-secondary shadow-xs fw-semibold">
                                <i class="fas fa-chart-line me-2 text-info"></i> <span>Open/Click Telemetry Config</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Best Practices Guidance Card --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
                        <span class="fw-bold text-dark small" style="font-size: 12px;">
                            <i class="fas fa-circle-info text-primary me-1"></i> Best Practices
                        </span>
                    </div>
                    <div class="p-3">
                        <p class="text-muted small mb-2" style="font-size: 11px; line-height: 1.45;">
                            &bull; <strong>Sender Domain Alignment:</strong> Ensure your From Email domain matches your SPF, DKIM, and DMARC DNS records to achieve 99%+ inbox placement.
                        </p>
                        <p class="text-muted small mb-0" style="font-size: 11px; line-height: 1.45;">
                            &bull; <strong>Warmup Strategy:</strong> When connecting a brand-new SMTP server, keep the batch chunk size at <strong>50 emails</strong> to prevent spam-filter throttling.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function submitSettingsForm() {
        const btn = document.getElementById('btn-save-general-settings');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
        }
        document.getElementById('generalSettingsForm').submit();
    }
</script>
@endsection