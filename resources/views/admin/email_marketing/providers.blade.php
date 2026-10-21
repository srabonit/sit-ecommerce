@extends('layouts.admin')

@section('title', 'Email Service Providers - Email Marketing')

@php
    $totalProvidersCount = $providers->count();
    $activeProvider      = $providers->firstWhere('is_active', true);
    $smtpEnginesCount    = $providers->where('driver', 'smtp')->count();
    $apiEnginesCount     = $totalProvidersCount - $smtpEnginesCount;
@endphp

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #email-marketing-providers-root .btn,
    #email-marketing-providers-root .btn-sm,
    #email-marketing-providers-root button,
    #email-marketing-providers-root .badge,
    #email-marketing-providers-root .alert,
    #email-marketing-providers-root .form-control,
    #email-marketing-providers-root .form-select,
    #email-marketing-providers-root .input-group-text,
    #email-marketing-providers-root .card,
    #email-marketing-providers-root .metric-card,
    #email-marketing-providers-root .collapse-toggle-btn,
    .provider-modal-scope .modal-content,
    .provider-modal-scope .btn,
    .admin-provider-toast {
        border-radius: 5px !important;
    }

    #email-marketing-providers-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #email-marketing-providers-root .input-group > .form-control,
    #email-marketing-providers-root .input-group > .form-select {
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
    #email-marketing-providers-root .card {
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

    .metric-card-green  { background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%); border-left: 3.5px solid #10b981 !important; }
    .metric-card-blue   { background: linear-gradient(180deg, #ffffff 0%, #f0f9ff 100%); border-left: 3.5px solid #2563eb !important; }
    .metric-card-cyan   { background: linear-gradient(180deg, #ffffff 0%, #ecfeff 100%); border-left: 3.5px solid #06b6d4 !important; }
    .metric-card-purple { background: linear-gradient(180deg, #ffffff 0%, #faf5ff 100%); border-left: 3.5px solid #a855f7 !important; }

    /* Provider Engine Cards */
    .provider-engine-card {
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .provider-engine-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.06);
    }
    .provider-active-border {
        border-left: 3.5px solid #10b981 !important;
    }

    /* Form Controls */
    #email-marketing-providers-root .form-label,
    .provider-modal-scope .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #email-marketing-providers-root .form-control,
    #email-marketing-providers-root .form-select,
    .provider-modal-scope .form-control,
    .provider-modal-scope .form-select {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #email-marketing-providers-root .form-control:focus,
    #email-marketing-providers-root .form-select:focus,
    .provider-modal-scope .form-control:focus,
    .provider-modal-scope .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Floating Toast Box */
    .admin-provider-toast {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 108000;
        min-width: 280px;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.2);
        animation: slideInRight 0.25s ease;
    }
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    /* 100/100 Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .providers-header-wrap h5 {
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
        #email-marketing-providers-root .card {
            margin-bottom: 12px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-providers-root">

    {{-- 1. Header & Navigation (Original Clean Light Theme) --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 providers-header-wrap">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                    <i class="fas fa-server text-primary"></i>
                    <span>Email Delivery Providers</span>
                </h5>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 font-monospace" style="font-size: 10px;">
                    {{ $totalProvidersCount }} Engines
                </span>
            </div>
            <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">
                Configure SMTP servers and API-based mail engines (Brevo, Mailgun, SES, SendGrid, Postmark).
            </small>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 top-action-btns">
            <button type="button" class="btn btn-sm btn-outline-info btn-compact bg-white shadow-xs fw-semibold text-dark" data-bs-toggle="modal" data-bs-target="#testConnectionModal">
                <i class="fas fa-vial text-info me-1"></i> <span>Test SMTP Ping</span>
            </button>
            <button type="button" class="btn btn-sm btn-primary btn-compact shadow-xs fw-bold" data-bs-toggle="modal" data-bs-target="#addProviderModal">
                <i class="fas fa-plus-circle me-1"></i> <span>Add Mailer Provider</span>
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
                <i class="fas fa-chart-pie text-primary me-1"></i> Mailer Delivery Infrastructure Overview
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseProviderKpis" aria-expanded="true" aria-controls="collapseProviderKpis" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseProviderKpis">
            <div class="p-3">
                <div class="row g-2">
                    {{-- Active Mailer Engine --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-green h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Active Engine</span>
                                <div class="metric-icon-square bg-success-subtle text-success shadow-xs">
                                    <i class="fas fa-circle-check"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-success text-truncate font-monospace stat-val-num" title="{{ $activeProvider->name ?? 'Default .ENV' }}">
                                    {{ $activeProvider->name ?? 'Default .ENV' }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Primary dispatch pipeline</small>
                            </div>
                        </div>
                    </div>

                    {{-- Total Configured Providers --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-blue h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Total Providers</span>
                                <div class="metric-icon-square bg-primary-subtle text-primary shadow-xs">
                                    <i class="fas fa-server"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                                    {{ $totalProvidersCount }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Configured engines</small>
                            </div>
                        </div>
                    </div>

                    {{-- SMTP Protocol Engines --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-cyan h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">SMTP Drivers</span>
                                <div class="metric-icon-square bg-info-subtle text-info shadow-xs">
                                    <i class="fas fa-network-wired"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                                    {{ $smtpEnginesCount }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Host-based relays</small>
                            </div>
                        </div>
                    </div>

                    {{-- API Mail Engines --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-purple h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">API Drivers</span>
                                <div class="metric-icon-square shadow-xs" style="background-color: #f3e8ff; color: #7e22ce;">
                                    <i class="fas fa-key"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                                    {{ $apiEnginesCount }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Brevo, SES, Mailgun, Postmark</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Providers List Cards Grid --}}
    <div class="row g-3">
        @forelse($providers as $provider)
            @php
                $driverIcon = match(strtolower($provider->driver ?? 'smtp')) {
                    'smtp'     => 'fa-server text-primary',
                    'brevo'    => 'fa-paper-plane text-info',
                    'mailgun'  => 'fa-bolt text-danger',
                    'ses'      => 'fa-cloud text-warning',
                    'sendgrid' => 'fa-cubes text-primary',
                    'postmark' => 'fa-stamp text-warning',
                    default    => 'fa-envelope text-secondary',
                };

                $makeActiveRoute = url('admin/email-marketing/providers/make-active/' . $provider->id);
                $destroyRoute    = url('admin/email-marketing/providers/delete/' . $provider->id);
                $updateRoute     = url('admin/email-marketing/providers/update/' . $provider->id);
            @endphp
            <div class="col-12 col-md-6 col-xl-4" id="provider-card-{{ $provider->id }}">
                <div class="card provider-engine-card border-0 shadow-sm h-100 d-flex flex-column justify-content-between mb-0 {{ $provider->is_active ? 'provider-active-border' : '' }}">
                    <div>
                        {{-- Card Header --}}
                        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
                            <div class="d-flex align-items-center gap-2 min-w-0">
                                <i class="fas {{ $driverIcon }} flex-shrink-0" style="font-size: 13px;"></i>
                                <strong class="text-dark text-truncate" style="font-size: 13px;" title="{{ $provider->name }}">
                                    {{ $provider->name }}
                                </strong>
                                <span class="badge bg-light text-secondary border font-monospace px-2 py-0" style="font-size: 9.5px;">
                                    {{ strtoupper($provider->driver) }}
                                </span>
                            </div>
                            @if($provider->is_active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-2 py-1 flex-shrink-0" style="font-size: 9.5px;">
                                    <i class="fas fa-circle-check me-1"></i> ACTIVE
                                </span>
                            @endif
                        </div>

                        {{-- Card Body Telemetry --}}
                        <div class="card-body p-3">
                            <ul class="list-unstyled small text-muted mb-0 font-monospace" style="font-size: 11px; line-height: 1.8;">
                                <li class="text-truncate">
                                    <strong class="text-dark font-sans-serif">Sender:</strong> 
                                    <span class="text-dark" title="{{ $provider->sender_name ?? config('mail.from.name') }} &lt;{{ $provider->sender_email ?? config('mail.from.address') }}&gt;">
                                        {{ $provider->sender_name ?? config('mail.from.name') }} &lt;{{ $provider->sender_email ?? config('mail.from.address') }}&gt;
                                    </span>
                                </li>
                                @if($provider->driver === 'smtp')
                                    <li class="text-truncate">
                                        <strong class="text-dark font-sans-serif">Host:</strong> 
                                        <span>{{ $provider->host ?? '—' }}:{{ $provider->port ?? '587' }} ({{ strtoupper($provider->encryption ?? 'tls') }})</span>
                                    </li>
                                    <li class="text-truncate">
                                        <strong class="text-dark font-sans-serif">Username:</strong> 
                                        <span>{{ $provider->username ?? '—' }}</span>
                                    </li>
                                @else
                                    <li class="text-truncate">
                                        <strong class="text-dark font-sans-serif">API Key:</strong> 
                                        <span class="text-dark">••••••••{{ substr($provider->api_key ?? '', -4) }}</span>
                                    </li>
                                    <li class="text-truncate">
                                        <strong class="text-dark font-sans-serif">Engine:</strong> 
                                        <span>Transactional HTTP Rest API</span>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    {{-- Card Actions Footer (Make Active, Edit, Delete) --}}
                    <div class="p-2 px-3 bg-light bg-opacity-50 border-top d-flex align-items-center justify-content-between gap-2">
                        <div>
                            @if(!$provider->is_active)
                                <form action="{{ $makeActiveRoute }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success btn-compact shadow-xs fw-semibold bg-white" title="Set as primary mail delivery engine">
                                        <i class="fas fa-check me-1"></i> <span>Make Active</span>
                                    </button>
                                </form>
                            @else
                                <span class="badge bg-success text-white font-monospace px-2 py-1 shadow-xs" style="font-size: 10px;">
                                    <i class="fas fa-shield-halved me-1"></i> DEFAULT ENGINE
                                </span>
                            @endif
                        </div>

                        <div class="d-flex align-items-center gap-1">
                            {{-- Edit Button --}}
                            <button type="button" class="btn btn-sm btn-light border p-1 text-primary shadow-xs d-inline-flex align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#editProviderModal{{ $provider->id }}" style="width: 26px; height: 26px; border-radius: 5px !important;" title="Edit Provider">
                                <i class="fas fa-pen-to-square" style="font-size: 10px;"></i>
                            </button>

                            {{-- Delete Button via Custom Reusable Modal --}}
                            <button type="button" class="btn btn-sm btn-light border p-1 text-danger shadow-xs d-inline-flex align-items-center justify-content-center" style="width: 26px; height: 26px; border-radius: 5px !important;" onclick="triggerDeleteProvider('{{ addslashes($provider->name) }}', '{{$destroyRoute }}')" title="Delete Provider">
                                <i class="fas fa-trash-can" style="font-size: 10px;"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Edit Provider Modal for Each Provider --}}
            <div class="modal fade provider-modal-scope" id="editProviderModal{{ $provider->id }}" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header border-bottom py-2 px-3 bg-light d-flex justify-content-between align-items-center">
                            <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 13.5px;">
                                <i class="fas fa-pen-to-square text-primary"></i> <span>Edit Provider: {{ $provider->name }}</span>
                            </h6>
                            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ $updateRoute }}" method="POST">
                            @csrf
                            <div class="modal-body p-3">
                                <div class="row g-2">
                                    <div class="col-12 col-md-7">
                                        <label class="form-label" for="edit_name_{{ $provider->id }}">Provider Nickname <span class="text-danger">*</span></label>
                                        <input type="text" name="name" id="edit_name_{{ $provider->id }}" class="form-control" value="{{ $provider->name }}" required>
                                    </div>
                                    <div class="col-12 col-md-5">
                                        <label class="form-label" for="edit_driver_{{ $provider->id }}">Driver Type <span class="text-danger">*</span></label>
                                        <select name="driver" id="edit_driver_{{ $provider->id }}" class="form-select font-monospace shadow-none" onchange="toggleEditDriverFields({{ $provider->id }})" required>
                                            <option value="smtp" {{ $provider->driver === 'smtp' ? 'selected' : '' }}>Standard SMTP</option>
                                            <option value="brevo" {{ $provider->driver === 'brevo' ? 'selected' : '' }}>Brevo (Sendinblue)</option>
                                            <option value="mailgun" {{ $provider->driver === 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                                            <option value="ses" {{ $provider->driver === 'ses' ? 'selected' : '' }}>Amazon SES</option>
                                            <option value="sendgrid" {{ $provider->driver === 'sendgrid' ? 'selected' : '' }}>SendGrid</option>
                                            <option value="postmark" {{ $provider->driver === 'postmark' ? 'selected' : '' }}>Postmark</option>
                                        </select>
                                    </div>

                                    {{-- SMTP Fields Box --}}
                                    <div class="col-12" id="edit_smtp_box_{{ $provider->id }}" style="{{ $provider->driver === 'smtp' ? '' : 'display: none;' }}">
                                        <div class="row g-2">
                                            <div class="col-12 col-md-8">
                                                <label class="form-label" for="edit_host_{{ $provider->id }}">SMTP Host</label>
                                                <input type="text" name="host" id="edit_host_{{ $provider->id }}" class="form-control font-monospace" value="{{ $provider->host }}">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label" for="edit_port_{{ $provider->id }}">Port</label>
                                                <input type="number" name="port" id="edit_port_{{ $provider->id }}" class="form-control font-monospace bn-digit" value="{{ $provider->port ?? 587 }}">
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label class="form-label" for="edit_user_{{ $provider->id }}">Username</label>
                                                <input type="text" name="username" id="edit_user_{{ $provider->id }}" class="form-control font-monospace" value="{{ $provider->username }}">
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label class="form-label" for="edit_pass_{{ $provider->id }}">Password / App Key</label>
                                                <div class="input-group">
                                                    <input type="password" name="password" id="edit_pass_{{ $provider->id }}" class="form-control" placeholder="Keep empty to leave intact">
                                                    <button class="btn btn-light border" type="button" onclick="togglePassVisibility('edit_pass_{{ $provider->id }}')">
                                                        <i class="fas fa-eye" style="font-size: 11px;"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- API Key Field Box --}}
                                    <div class="col-12" id="edit_api_box_{{ $provider->id }}" style="{{ $provider->driver !== 'smtp' ? '' : 'display: none;' }}">
                                        <label class="form-label" for="edit_api_{{ $provider->id }}">REST API Key / Secret</label>
                                        <div class="input-group">
                                            <input type="password" name="api_key" id="edit_api_{{ $provider->id }}" class="form-control font-monospace" placeholder="Keep empty to leave current intact">
                                            <button class="btn btn-light border" type="button" onclick="togglePassVisibility('edit_api_{{ $provider->id }}')">
                                                <i class="fas fa-eye" style="font-size: 11px;"></i>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Sender Identity Info --}}
                                    <div class="col-12 col-md-6">
                                        <label class="form-label" for="edit_sname_{{ $provider->id }}">Sender Name</label>
                                        <input type="text" name="sender_name" id="edit_sname_{{ $provider->id }}" class="form-control" value="{{ $provider->sender_name }}">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label" for="edit_semail_{{ $provider->id }}">Sender Email</label>
                                        <input type="email" name="sender_email" id="edit_semail_{{ $provider->id }}" class="form-control font-monospace" value="{{ $provider->sender_email }}">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-top py-2 px-3 bg-light d-flex justify-content-between">
                                <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold">Update Provider</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm p-4 text-center">
                    <div class="bg-light p-3 rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 56px; height: 56px; border-radius: 50% !important;">
                        <i class="fas fa-server fa-2x text-secondary opacity-50"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">No External Mail Providers Configured</h6>
                    <p class="text-muted small mx-auto mb-3" style="max-width: 460px; font-size: 11.5px; line-height: 1.5;">
                        The system is currently using your default Laravel <code class="font-monospace">.env</code> mail settings. Click "Add Mailer Provider" to manage multiple SMTP or transactional API services.
                    </p>
                    <div>
                        <button type="button" class="btn btn-sm btn-primary btn-compact shadow-xs fw-bold" data-bs-toggle="modal" data-bs-target="#addProviderModal">
                            <i class="fas fa-plus-circle me-1"></i> Add Mailer Provider
                        </button>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>

{{-- Modal 1: Add Provider Modal --}}
<div class="modal fade provider-modal-scope" id="addProviderModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom py-2 px-3 bg-light d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 13.5px;">
                    <i class="fas fa-server text-primary"></i> <span>Add Email Delivery Provider</span>
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ url('admin/email-marketing/providers/store') }}" method="POST">
                @csrf
                <div class="modal-body p-3">
                    <div class="row g-2">
                        <div class="col-12 col-md-7">
                            <label class="form-label" for="add_provider_name">Provider Nickname <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="add_provider_name" class="form-control" placeholder="e.g. Brevo SMTP / Amazon SES" required>
                        </div>
                        <div class="col-12 col-md-5">
                            <label class="form-label" for="add_driver_type">Driver Type <span class="text-danger">*</span></label>
                            <select name="driver" id="add_driver_type" class="form-select font-monospace shadow-none" onchange="toggleAddDriverFields()" required>
                                <option value="smtp">Standard SMTP</option>
                                <option value="brevo">Brevo (Sendinblue)</option>
                                <option value="mailgun">Mailgun</option>
                                <option value="ses">Amazon SES</option>
                                <option value="sendgrid">SendGrid</option>
                                <option value="postmark">Postmark</option>
                            </select>
                        </div>

                        {{-- SMTP Fields Box --}}
                        <div class="col-12" id="add_smtp_box">
                            <div class="row g-2">
                                <div class="col-12 col-md-8">
                                    <label class="form-label" for="add_smtp_host">SMTP Host</label>
                                    <input type="text" name="host" id="add_smtp_host" class="form-control font-monospace" placeholder="smtp-relay.brevo.com">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label" for="add_smtp_port">Port</label>
                                    <input type="number" name="port" id="add_smtp_port" class="form-control font-monospace bn-digit" placeholder="587" value="587">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="add_username">Username</label>
                                    <input type="text" name="username" id="add_username" class="form-control font-monospace">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="add_password">Password / Secret</label>
                                    <div class="input-group">
                                        <input type="password" name="password" id="add_password" class="form-control">
                                        <button class="btn btn-light border" type="button" onclick="togglePassVisibility('add_password')">
                                            <i class="fas fa-eye" style="font-size: 11px;"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- API Key Box --}}
                        <div class="col-12" id="add_api_box" style="display: none;">
                            <label class="form-label" for="add_api_key">REST API Key / Secret</label>
                            <div class="input-group">
                                <input type="password" name="api_key" id="add_api_key" class="form-control font-monospace" placeholder="xkeysib-...">
                                <button class="btn btn-light border" type="button" onclick="togglePassVisibility('add_api_key')">
                                    <i class="fas fa-eye" style="font-size: 11px;"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Sender Identity Info --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="add_sender_name">Sender Name</label>
                            <input type="text" name="sender_name" id="add_sender_name" class="form-control" placeholder="Shop Now BD">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="add_sender_email">Sender Email</label>
                            <input type="email" name="sender_email" id="add_sender_email" class="form-control font-monospace" placeholder="info@shopnowbd.online">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2 px-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold">Save Provider</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal 2: Test Connection Modal --}}
<div class="modal fade provider-modal-scope" id="testConnectionModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13.5px;">
                    <i class="fas fa-vial text-info"></i> <span>Send Verification Test</span>
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2 pt-3 text-start">
                <label class="form-label" for="testTargetEmail">Target Inbox for SMTP Ping</label>
                <input type="email" id="testTargetEmail" class="form-control font-monospace shadow-none" value="{{ auth()->user()->email ?? '' }}" placeholder="your-email@example.com">
                <div id="testResultMsg" class="mt-2 font-monospace" style="font-size: 11px;"></div>
            </div>
            <div class="modal-footer border-0 pt-0 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light border btn-compact" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-info text-white btn-compact shadow-xs fw-bold" id="btn-send-ping" onclick="runConnectionTest()">
                    <i class="fas fa-paper-plane me-1"></i> Send Ping
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal 3: Custom Delete Confirmation Modal --}}
<div class="modal fade provider-modal-scope" id="deleteProviderModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 44px; height: 44px; border-radius: 50% !important;">
                    <i class="fas fa-trash-can fs-4"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">Delete Mail Provider?</h6>
                <p class="text-muted small mb-3" style="font-size: 11px; line-height: 1.45;">
                    Are you sure you want to delete <strong id="deleteProviderTitle" class="text-dark">this provider</strong>? This will remove the SMTP/API credentials from your system.
                </p>
                <form id="deleteProviderForm" method="POST" action="">
                    @csrf
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger btn-compact shadow-xs fw-bold">Yes, Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Floating Toast Feedback Notification Holder --}}
<div id="custom-toast-holder"></div>

<script>
    function triggerDeleteProvider(name, formUrl) {
        document.getElementById('deleteProviderTitle').innerText = name;
        document.getElementById('deleteProviderForm').action = formUrl;
        const modalEl = document.getElementById('deleteProviderModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function toggleAddDriverFields() {
        const driver = document.getElementById('add_driver_type')?.value;
        const smtpBox = document.getElementById('add_smtp_box');
        const apiBox  = document.getElementById('add_api_box');

        if (driver === 'smtp') {
            smtpBox.style.display = '';
            apiBox.style.display = 'none';
        } else {
            smtpBox.style.display = 'none';
            apiBox.style.display = '';
        }
    }

    function toggleEditDriverFields(id) {
        const driver = document.getElementById(`edit_driver_${id}`)?.value;
        const smtpBox = document.getElementById(`edit_smtp_box_${id}`);
        const apiBox  = document.getElementById(`edit_api_box_${id}`);

        if (driver === 'smtp') {
            smtpBox.style.display = '';
            apiBox.style.display = 'none';
        } else {
            smtpBox.style.display = 'none';
            apiBox.style.display = '';
        }
    }

    function togglePassVisibility(inputId) {
        const input = document.getElementById(inputId);
        if (input) {
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    }

    function runConnectionTest() {
        const email = document.getElementById('testTargetEmail')?.value?.trim();
        const resultDiv = document.getElementById('testResultMsg');
        const pingBtn = document.getElementById('btn-send-ping');

        if (!email) {
            showToast('Warning', 'Please enter a target verification email.', 'warning');
            return;
        }

        if (pingBtn) {
            pingBtn.disabled = true;
            pingBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Testing...';
        }

        resultDiv.innerHTML = '<span class="text-muted"><i class="fas fa-spinner fa-spin me-1"></i> Testing SMTP handshake...</span>';

        const testUrl = "{{ url('admin/email-marketing/providers/test-connection') }}";

        fetch(testUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ test_email: email })
        })
        .then(res => res.json())
        .then(data => {
            if (pingBtn) {
                pingBtn.disabled = false;
                pingBtn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Send Ping';
            }
            if (data.success) {
                resultDiv.innerHTML = `<span class="text-success fw-bold"><i class="fas fa-check-circle me-1"></i> ${data.message}</span>`;
                showToast('Success', data.message, 'success');
            } else {
                resultDiv.innerHTML = `<span class="text-danger fw-bold"><i class="fas fa-exclamation-circle me-1"></i> ${data.message}</span>`;
                showToast('Failed', data.message, 'danger');
            }
        })
        .catch(err => {
            if (pingBtn) {
                pingBtn.disabled = false;
                pingBtn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Send Ping';
            }
            resultDiv.innerHTML = `<span class="text-danger fw-bold">Test failed: ${err.message}</span>`;
            showToast('Error', 'Connection test failed to complete.', 'danger');
        });
    }

    // Floating Toast Notifications
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
            <div id="${toastId}" class="admin-provider-toast ${config.color} p-3 d-flex align-items-center justify-content-between shadow-lg" style="border-radius: 5px !important;">
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