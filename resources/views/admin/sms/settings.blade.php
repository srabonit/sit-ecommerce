@extends('layouts.admin')

@section('title', 'SMS Settings & Gateway')

@section('admin_content')

<style>
/* SMS Settings — Kaiadmin-inspired Classic White UI
   All rules are scoped to this page to avoid affecting other admin screens. */
.sms-settings-page {
    --sms-primary: var(--bs-primary, #1572e8);
    --sms-primary-rgb: var(--bs-primary-rgb, 21, 114, 232);
    --sms-page-bg: #f5f7fb;
    --sms-card-bg: #ffffff;
    --sms-border: #e7eaf0;
    --sms-heading: #1d2635;
    --sms-text: #596579;
    --sms-muted: #8993a4;
    --sms-radius: 10px;
    color: var(--sms-heading);
    min-width: 0;
}
.sms-settings-page,
.sms-settings-page * { box-sizing: border-box; }
.sms-settings-page .sms-page-heading h4 {
    color: var(--sms-heading);
    font-size: clamp(1.15rem, 1.6vw, 1.45rem);
    letter-spacing: -.025em;
}
.sms-settings-page .sms-page-heading p {
    color: var(--sms-muted) !important;
    font-size: .84rem;
}
.sms-settings-page .btn {
    min-height: 36px;
    border-radius: 7px;
    font-size: .82rem;
    font-weight: 600;
    letter-spacing: .005em;
    transition: background-color .18s ease, border-color .18s ease, box-shadow .18s ease, transform .18s ease;
}
.sms-settings-page .btn:focus-visible,
.sms-settings-page .form-control:focus,
.sms-settings-page .form-select:focus,
.sms-settings-page .form-check-input:focus {
    box-shadow: 0 0 0 .2rem rgba(var(--sms-primary-rgb), .14);
}
.sms-settings-page .btn-primary {
    background: var(--sms-primary);
    border-color: var(--sms-primary);
}
.sms-settings-page .btn-primary:hover { filter: brightness(.94); transform: translateY(-1px); }
.sms-settings-page .btn-outline-secondary {
    color: #596579;
    border-color: #dce1e9;
    background: #fff;
}
.sms-settings-page .btn-outline-secondary:hover { background: #f5f7fa; color: #263247; }
.sms-settings-page .card {
    border: 1px solid var(--sms-border) !important;
    border-radius: var(--sms-radius);
    background: var(--sms-card-bg);
    box-shadow: 0 2px 8px rgba(25, 42, 70, .035) !important;
    overflow: hidden;
}
.sms-settings-page .card-header {
    min-height: 49px;
    padding: 13px 18px !important;
    background: #fff !important;
    border-color: var(--sms-border) !important;
}
.sms-settings-page .card-header h6 {
    color: var(--sms-heading) !important;
    font-size: .9rem;
    font-weight: 650 !important;
    letter-spacing: -.01em;
}
.sms-settings-page .card-body { min-width: 0; }
.sms-settings-page .row { min-width: 0; }
.sms-settings-page .col-12,
.sms-settings-page [class*="col-"] { min-width: 0; }
.sms-settings-page .row.g-3 { --bs-gutter-y: 1rem; }
.sms-settings-page .row.g-4 { --bs-gutter-y: 1.25rem; }

/* Overview metric cards */
.sms-settings-page .sms-metric-card {
    position: relative;
    min-height: 112px;
    transition: border-color .18s ease, transform .18s ease, box-shadow .18s ease;
}
.sms-settings-page .sms-metric-card:hover {
    border-color: #d5deec !important;
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(25, 42, 70, .065) !important;
}
.sms-settings-page .sms-metric-card .card-body { padding: 16px !important; }
.sms-settings-page .sms-metric-card .sms-metric-label {
    color: var(--sms-muted);
    font-size: .76rem;
    font-weight: 600;
    letter-spacing: .015em;
}
.sms-settings-page .sms-metric-card h5 {
    font-size: clamp(1.08rem, 1.5vw, 1.32rem);
    line-height: 1.3;
    overflow-wrap: anywhere;
}
.sms-settings-page .sms-metric-card .sms-metric-meta {
    display: block;
    margin-top: 5px;
    color: var(--sms-muted);
    font-size: .7rem !important;
    line-height: 1.4;
}

/* Tabs */
.sms-settings-page .sms-settings-nav {
    display: flex;
    flex-wrap: nowrap;
    gap: 5px;
    width: 100%;
    padding: 5px;
    overflow-x: auto;
    border: 1px solid var(--sms-border);
    border-radius: 9px;
    background: #fff;
    scrollbar-width: thin;
}
.sms-settings-page .sms-settings-nav .nav-item { flex: 0 0 auto; }
.sms-settings-page .sms-settings-nav .nav-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    min-height: 38px;
    padding: 8px 14px !important;
    border: 0 !important;
    border-radius: 6px;
    color: #687487 !important;
    background: transparent;
    font-size: .82rem;
    font-weight: 600 !important;
    white-space: nowrap;
}
.sms-settings-page .sms-settings-nav .nav-link:hover { background: #f5f7fb; color: var(--sms-heading) !important; }
.sms-settings-page .sms-settings-nav .nav-link.active {
    color: var(--sms-primary) !important;
    background: rgba(var(--sms-primary-rgb), .09);
    box-shadow: inset 0 0 0 1px rgba(var(--sms-primary-rgb), .08);
}
.sms-settings-page .tab-content { min-width: 0; }
.sms-settings-page .tab-pane { min-width: 0; }

/* Forms and switches */
.sms-settings-page .form-label,
.sms-settings-page .form-check-label {
    color: #354155;
    font-size: .8rem;
}
.sms-settings-page .form-label { margin-bottom: 6px; font-weight: 600 !important; }
.sms-settings-page .form-control,
.sms-settings-page .form-select {
    min-height: 39px;
    padding: 8px 11px;
    border: 1px solid #dfe4ec;
    border-radius: 7px;
    color: #344054;
    background-color: #fff;
    font-size: .82rem;
}
.sms-settings-page textarea.form-control { min-height: 88px; }
.sms-settings-page .form-control::placeholder { color: #a2aaba; opacity: 1; }
.sms-settings-page .form-text {
    margin-top: 5px;
    color: var(--sms-muted) !important;
    font-size: .72rem !important;
    line-height: 1.45;
}
.sms-settings-page .form-check-input {
    width: 2.15em;
    height: 1.15em;
    margin-top: .15em;
    cursor: pointer;
    border-color: #cbd3df;
}
.sms-settings-page .form-check-input:checked {
    background-color: var(--sms-primary);
    border-color: var(--sms-primary);
}
.sms-settings-page .form-check-label { cursor: pointer; }
.sms-settings-page .form-switch { min-height: 22px; }
.sms-settings-page .border-bottom { border-color: var(--sms-border) !important; }
.sms-settings-page hr { border-color: var(--sms-border); opacity: 1; }

/* Setting rows */
.sms-settings-page .sms-setting-row {
    gap: 16px;
    min-width: 0;
}
.sms-settings-page .sms-setting-row > div:first-child { min-width: 0; }
.sms-settings-page .sms-setting-row .form-check { flex: 0 0 auto; }
.sms-settings-page .sms-setting-row label { line-height: 1.35; }
.sms-settings-page .sms-setting-row .text-muted { color: var(--sms-muted) !important; }

/* Order automation tiles */
.sms-settings-page .sms-trigger-card {
    height: 100%;
    padding: 13px;
    border: 1px solid var(--sms-border) !important;
    border-radius: 8px;
    background: #fff !important;
    transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
}
.sms-settings-page .sms-trigger-card:hover {
    border-color: #cfd9e8 !important;
    box-shadow: 0 4px 12px rgba(25, 42, 70, .05);
    transform: translateY(-1px);
}
.sms-settings-page .sms-trigger-card .form-check {
    display: flex;
    align-items: center;
    gap: 7px;
    min-width: 0;
}
.sms-settings-page .sms-trigger-card .form-check-input {
    flex: 0 0 auto;
    margin: 0;
}
.sms-settings-page .sms-trigger-card .form-check-label {
    flex: 0 1 auto;
    min-width: 0;
    line-height: 1.35;
    white-space: normal;
    overflow-wrap: normal;
    word-break: normal;
}
.sms-settings-page .sms-trigger-card > span { line-height: 1.45; color: var(--sms-muted) !important; }

/* Badges and alerts */
.sms-settings-page .badge {
    padding: 5px 8px;
    border-radius: 5px;
    font-size: .68rem;
    font-weight: 650;
    letter-spacing: .01em;
}
.sms-settings-page .alert {
    border-radius: 8px;
    border-width: 1px;
}
.sms-settings-page .bg-light-subtle { background: #f8f9fc !important; }

/* API reference panel */
.sms-settings-page .sms-reference-card {
    background: #f8faff !important;
    border-color: #e4eaf4 !important;
}
.sms-settings-page .sms-reference-card .card-body { padding: 20px !important; }
.sms-settings-page .sms-reference-card li { line-height: 1.5; }

/* Collapse header remains clickable */
.sms-settings-page [data-bs-toggle="collapse"] { cursor: pointer; }
.sms-settings-page #orderSettingsGroupCollapse .card-body { min-width: 0; }

/* Modals */
#testSmsModal .modal-content,
#appAlertModal .modal-content {
    border: 1px solid var(--sms-border, #e7eaf0) !important;
    border-radius: 12px;
    overflow: hidden;
}
#testSmsModal .modal-header,
#testSmsModal .modal-footer { background: #fff; border-color: #e7eaf0 !important; }
#testSmsModal .modal-title { color: #1d2635; font-size: .95rem; }
#testSmsModal .modal-body { padding: 20px !important; }
#testSmsModal .modal-footer { gap: 8px; }

/* The project uses this custom 5-column utility for ten lifecycle triggers. */
@media (min-width: 1200px) {
    .sms-settings-page .col-xl-2-4 {
        flex: 0 0 auto;
        width: 20%;
    }
}
@media (max-width: 1199.98px) {
    .sms-settings-page .col-xl-2-4 {
        flex: 0 0 auto;
        width: 33.33333333%;
    }
}
@media (max-width: 767.98px) {
    .sms-settings-page { padding-top: 1rem !important; }
    .sms-settings-page .sms-page-heading { width: 100%; }
    .sms-settings-page .sms-page-actions { width: 100%; }
    .sms-settings-page .sms-page-actions .btn { flex: 1 1 0; }
    .sms-settings-page .sms-metric-card { min-height: 104px; }
    .sms-settings-page .sms-metric-card .card-body { padding: 13px !important; }
    .sms-settings-page .card-header { padding: 12px 14px !important; }
    .sms-settings-page .card-body { padding: 14px !important; }
    .sms-settings-page .sms-settings-nav { margin-bottom: 1rem !important; }
    .sms-settings-page .sms-setting-row { align-items: flex-start !important; }
    .sms-settings-page .sms-trigger-card { padding: 11px; }
    .sms-settings-page .sms-trigger-card .form-check-label { font-size: .76rem; }
    .sms-settings-page .sms-trigger-card > span { font-size: .7rem !important; }
    .sms-settings-page .text-end .btn { width: 100%; }
}
@media (max-width: 575.98px) {
    .sms-settings-page .sms-page-actions { flex-direction: column; }
    .sms-settings-page .sms-page-actions .btn { width: 100%; }
    .sms-settings-page .col-xl-2-4 { width: 100%; }
    .sms-settings-page .sms-settings-nav .nav-link { padding: 8px 11px !important; font-size: .76rem; }
    .sms-settings-page .sms-metric-card .sms-metric-label { font-size: .7rem; }
    .sms-settings-page .sms-metric-card h5 { font-size: 1rem; }
    #testSmsModal .modal-dialog { margin: 12px; }
    #testSmsModal .modal-body { padding: 16px !important; }
}
@media (max-width: 359.98px) {
    .sms-settings-page .col-xl-2-4 { width: 100%; }
    .sms-settings-page .sms-page-actions .btn { font-size: .76rem; }
}
@media (prefers-reduced-motion: reduce) {
    .sms-settings-page *,
    #testSmsModal *,
    #appAlertModal * { transition-duration: .01ms !important; animation-duration: .01ms !important; }
}
</style>

<div class="container-fluid px-3 px-md-4 py-4 sms-settings-page">

    {{-- Top Header & Quick Actions --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4 sms-page-heading">
        <div>
            <h4 class="fw-bold mb-1">SMS Settings</h4>
            <p class="text-muted small mb-0">Configure Onecodesoft gateway, automated notifications, and OTP policies.</p>
        </div>
        <div class="d-flex align-items-center gap-2 sms-page-actions">
            <button type="button" class="btn btn-outline-secondary btn-sm px-3" id="btnSyncBalance" onclick="handleSyncBalance(this)">
                <i class="fas fa-sync-alt me-1" id="syncIcon"></i> <span id="syncBtnText">Sync Balance</span>
            </button>
            <button type="button" class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#testSmsModal">
                <i class="fas fa-paper-plane me-1"></i> Send Test SMS
            </button>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show small py-2 mb-3" role="alert">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show small py-2 mb-3" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show small py-2 mb-3" role="alert">
            <i class="fas fa-info-circle me-1"></i> {{ session('info') }}
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Live Overview Metrics --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 sms-metric-card">
                <div class="card-body p-3">
                    <span class="text-muted small d-block">Gateway Balance</span>
                    <h5 class="fw-bold text-dark mt-1 mb-0" id="cardBalanceText">
                        ৳ {{ number_format((float) ($provider->balance ?? 0), 2) }}
                    </h5>
                    <span class="text-muted" style="font-size: 11px;" id="cardLastSynced">
                        {{ $provider?->last_balance_synced_at ? $provider->last_balance_synced_at->diffForHumans() : 'Never synced' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 sms-metric-card">
                <div class="card-body p-3">
                    <span class="text-muted small d-block">Est. Remaining SMS</span>
                    <h5 class="fw-bold text-primary mt-1 mb-0" id="cardEstSms">
                        {{ $provider && $provider->price_per_sms > 0 ? number_format((int) ($provider->balance / $provider->price_per_sms)) : 0 }}
                    </h5>
                    <span class="text-muted" style="font-size: 11px;">Based on ৳ {{ number_format((float) ($provider->price_per_sms ?? 0.35), 4) }}/SMS</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 sms-metric-card">
                <div class="card-body p-3">
                    <span class="text-muted small d-block">Gateway Status</span>
                    <div class="mt-1">
                        @if($provider && $provider->is_active)
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Disabled</span>
                        @endif
                        @if($provider && $provider->sandbox_mode)
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle ms-1">Sandbox</span>
                        @endif
                    </div>
                    <span class="text-muted" style="font-size: 11px;">Provider: {{ $provider->name ?? 'Onecodesoft' }}</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 sms-metric-card">
                <div class="card-body p-3">
                    <span class="text-muted small d-block">Master Toggle</span>
                    <h5 class="fw-bold mt-1 mb-0">
                        @if((string) ($settings['global_sms_enabled'] ?? '1') === '1')
                            <span class="text-success small"><i class="fas fa-circle me-1" style="font-size: 8px;"></i> Online</span>
                        @else
                            <span class="text-secondary small"><i class="fas fa-circle me-1" style="font-size: 8px;"></i> Paused</span>
                        @endif
                    </h5>
                    <span class="text-muted" style="font-size: 11px;">Controls all outgoing traffic</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Settings Navigation --}}
    <ul class="nav nav-tabs nav-tabs-bordered mb-4 sms-settings-nav" id="smsSettingsTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active fw-medium px-3 py-2 text-dark" id="general-tab" data-bs-toggle="tab" data-bs-target="#tab-general" type="button">
                <i class="fas fa-sliders-h me-1"></i> General &amp; Triggers
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-medium px-3 py-2 text-dark" id="provider-tab" data-bs-toggle="tab" data-bs-target="#tab-provider" type="button">
                <i class="fas fa-server me-1"></i> Gateway API
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-medium px-3 py-2 text-dark" id="otp-tab" data-bs-toggle="tab" data-bs-target="#tab-otp" type="button">
                <i class="fas fa-shield-alt me-1"></i> OTP Policy
            </button>
        </li>
    </ul>

    <div class="tab-content" id="smsSettingsTabsContent">

        {{-- TAB 1: General & Automated Triggers --}}
        <div class="tab-pane fade show active" id="tab-general" role="tabpanel">
            <form action="{{ route('admin.sms.settings.update') }}" method="POST">
                @csrf
                <input type="hidden" name="setting_section" value="general">
                <input type="hidden" name="current_tab" value="tab-general">

                <div class="row g-4">
                    {{-- 1. System Master Channels --}}
                    <div class="col-12 col-lg-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-bottom py-3">
                                <h6 class="fw-semibold mb-0 text-dark">System Channels &amp; Master Toggles</h6>
                            </div>
                            <div class="card-body p-3 p-md-4">
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                    <div>
                                        <label class="form-check-label fw-medium text-dark d-block">Global SMS Service</label>
                                        <span class="text-muted small">Master switch to enable or pause all outgoing SMS traffic.</span>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" name="global_sms_enabled" value="1" {{ (string)($settings['global_sms_enabled'] ?? '1') === '1' ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                    <div>
                                        <label class="form-check-label fw-medium text-dark d-block">Global Email Service</label>
                                        <span class="text-muted small">Master switch to enable or pause all system emails.</span>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" name="global_email_enabled" value="1" {{ (string)($settings['global_email_enabled'] ?? '1') === '1' ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                    <div>
                                        <label class="form-check-label fw-medium text-dark d-block">Bulk SMS Campaigns</label>
                                        <span class="text-muted small">Allow background execution for bulk marketing campaigns.</span>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" name="bulk_sms_enabled" value="1" {{ (string)($settings['bulk_sms_enabled'] ?? '1') === '1' ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                    <div>
                                        <label class="form-check-label fw-medium text-dark d-block">Promotional SMS Module</label>
                                        <span class="text-muted small">Enable scheduled marketing and discount blasts.</span>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" name="sms_marketing_enabled" value="1" {{ (string)($settings['sms_marketing_enabled'] ?? '1') === '1' ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <label class="form-check-label fw-medium text-dark d-block">Customer Welcome SMS</label>
                                        <span class="text-muted small">Send welcome message when a customer registers.</span>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" name="auto_sms_customer_welcome" value="1" {{ (string)($settings['auto_sms_customer_welcome'] ?? '1') === '1' ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Admin Gateway & Low Balance Alerts --}}
                    <div class="col-12 col-lg-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-bottom py-3">
                                <h6 class="fw-semibold mb-0 text-dark">Admin Notification &amp; Balance Alerts</h6>
                            </div>
                            <div class="card-body p-3 p-md-4">
                                <div class="mb-3">
                                    <label class="form-label fw-medium small text-dark">Admin Notification Phone</label>
                                    <input type="text" class="form-control form-control-sm" name="admin_notification_phone" value="{{ $settings['admin_notification_phone'] ?? '' }}" placeholder="017XXXXXXXX or 88017XXXXXXXX">
                                    <div class="form-text small">Receives new order alerts and low gateway balance warnings.</div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                    <div>
                                        <label class="form-check-label small fw-medium text-dark d-block">Alert on Low Balance</label>
                                        <span class="text-muted small">Notify admin when SMS balance drops below threshold.</span>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" name="auto_sms_admin_low_balance" value="1" {{ (string)($settings['auto_sms_admin_low_balance'] ?? '1') === '1' ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label fw-medium small text-dark">Low Balance Threshold (৳)</label>
                                    <input type="number" step="0.5" class="form-control form-control-sm" name="low_balance_threshold" value="{{ $settings['low_balance_threshold'] ?? '50.00' }}">
                                    <div class="form-text small">Trigger warning alert once gateway balance drops below this amount.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. COLLAPSIBLE GROUP: ALL ORDER SETTINGS & LIFECYCLE STATUS TRIGGERS --}}
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center" 
                                 style="cursor: pointer;" 
                                 data-bs-toggle="collapse" 
                                 data-bs-target="#orderSettingsGroupCollapse" 
                                 aria-expanded="true">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-boxes-packing text-primary"></i>
                                    <h6 class="fw-bold mb-0 text-dark">Order Management &amp; Lifecycle SMS Automation (10 Status Triggers)</h6>
                                </div>
                                <span class="badge bg-light text-dark border px-2 py-1">
                                    <i class="fas fa-chevron-down" id="orderGroupChevron"></i>
                                </span>
                            </div>

                            <div class="collapse show" id="orderSettingsGroupCollapse">
                                <div class="card-body p-3 p-md-4">
                                    {{-- Section A: Order Master Notification & Trigger Controls --}}
                                    <h6 class="fw-semibold text-secondary small text-uppercase mb-3">
                                        <i class="fas fa-toggle-on me-1"></i> Order Automation &amp; Master Toggles
                                    </h6>
                                    
                                    <div class="row g-3 mb-4">
                                        <div class="col-12 col-md-6 col-lg-3">
                                            <div class="border rounded p-3 h-100 bg-light-subtle sms-trigger-card">
                                                <div class="form-check form-switch">
                                                    {{-- Checkout Confirmation Switch --}}
                                                    <input class="form-check-input" type="checkbox" name="auto_sms_order_placed_customer" value="1" {{ (string)($settings['auto_sms_order_placed_customer'] ?? '1') === '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-semibold small">Order Placed SMS</label>
                                                </div>
                                                <span class="text-muted d-block small mt-1">Instant confirmation SMS to customer on checkout.</span>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6 col-lg-3">
                                            <div class="border rounded p-3 h-100 bg-light-subtle sms-trigger-card">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="manual_order_sms_enabled" value="1" {{ (string)($settings['manual_order_sms_enabled'] ?? '1') === '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-semibold small">Instant Manual SMS</label>
                                                </div>
                                                <span class="text-muted d-block small mt-1">1-click status SMS trigger button in order management.</span>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6 col-lg-3">
                                            <div class="border rounded p-3 h-100 bg-light-subtle sms-trigger-card">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="auto_sms_admin_new_order" value="1" {{ (string)($settings['auto_sms_admin_new_order'] ?? '1') === '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-semibold small">Admin Order Alert</label>
                                                </div>
                                                <span class="text-muted d-block small mt-1">Send SMS alert to admin phone when a new order arrives.</span>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6 col-lg-3">
                                            <div class="border rounded p-3 h-100 bg-light-subtle sms-trigger-card">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="auto_email_order_status" value="1" {{ (string)($settings['auto_email_order_status'] ?? '1') === '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-semibold small">Order Status Email</label>
                                                </div>
                                                <span class="text-muted d-block small mt-1">Send automatic email update when order status changes.</span>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    {{-- Section B: Complete 10 Order Lifecycle Status Toggles --}}
                                    <h6 class="fw-semibold text-secondary small text-uppercase mb-3">
                                        <i class="fas fa-list-check me-1"></i> Individual Status Auto-SMS Triggers (10 Lifecycle Steps)
                                    </h6>

                                    <div class="row g-3">
                                        {{-- 1. Pending (Now independent: auto_sms_order_pending) --}}
                                        <div class="col-12 col-sm-6 col-md-4 col-xl-2-4">
                                            <div class="border rounded p-3 h-100 sms-trigger-card">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="auto_sms_order_pending" value="1" {{ (string)($settings['auto_sms_order_pending'] ?? ($settings['auto_sms_order_placed_customer'] ?? '1')) === '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-semibold small text-danger">1. Pending</label>
                                                </div>
                                                <span class="text-muted d-block small mt-1">When order is marked pending or awaiting review.</span>
                                            </div>
                                        </div>

                                        {{-- 2. Received --}}
                                        <div class="col-12 col-sm-6 col-md-4 col-xl-2-4">
                                            <div class="border rounded p-3 h-100 sms-trigger-card">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="auto_sms_order_received" value="1" {{ (string)($settings['auto_sms_order_received'] ?? '1') === '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-semibold small text-info-emphasis">2. Received</label>
                                                </div>
                                                <span class="text-muted d-block small mt-1">When order is accepted by admin desk.</span>
                                            </div>
                                        </div>

                                        {{-- 3. Confirmed --}}
                                        <div class="col-12 col-sm-6 col-md-4 col-xl-2-4">
                                            <div class="border rounded p-3 h-100 sms-trigger-card">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="auto_sms_order_confirmed" value="1" {{ (string)($settings['auto_sms_order_confirmed'] ?? '1') === '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-semibold small text-primary">3. Confirmed</label>
                                                </div>
                                                <span class="text-muted d-block small mt-1">Customer verified &amp; ready to process.</span>
                                            </div>
                                        </div>

                                        {{-- 4. Processing (Packed) --}}
                                        <div class="col-12 col-sm-6 col-md-4 col-xl-2-4">
                                            <div class="border rounded p-3 h-100 sms-trigger-card">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="auto_sms_order_processing" value="1" {{ (string)($settings['auto_sms_order_processing'] ?? '0') === '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-semibold small text-dark">4. Processing</label>
                                                </div>
                                                <span class="text-muted d-block small mt-1">Warehouse packaging &amp; stock deduction.</span>
                                            </div>
                                        </div>

                                        {{-- 5. Shipped --}}
                                        <div class="col-12 col-sm-6 col-md-4 col-xl-2-4">
                                            <div class="border rounded p-3 h-100 sms-trigger-card">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="auto_sms_order_shipped" value="1" {{ (string)($settings['auto_sms_order_shipped'] ?? '1') === '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-semibold small text-warning-emphasis">5. Shipped</label>
                                                </div>
                                                <span class="text-muted d-block small mt-1">Handed over to courier with tracking code.</span>
                                            </div>
                                        </div>

                                        {{-- 6. Attempt to Deliver --}}
                                        <div class="col-12 col-sm-6 col-md-4 col-xl-2-4">
                                            <div class="border rounded p-3 h-100 sms-trigger-card">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="auto_sms_order_attempt_to_deliver" value="1" {{ (string)($settings['auto_sms_order_attempt_to_deliver'] ?? '1') === '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-semibold small text-secondary">6. Attempt to Deliver</label>
                                                </div>
                                                <span class="text-muted d-block small mt-1">Rider out for delivery at customer address.</span>
                                            </div>
                                        </div>

                                        {{-- 7. Delivered --}}
                                        <div class="col-12 col-sm-6 col-md-4 col-xl-2-4">
                                            <div class="border rounded p-3 h-100 sms-trigger-card">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="auto_sms_order_delivered" value="1" {{ (string)($settings['auto_sms_order_delivered'] ?? '1') === '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-semibold small text-success">7. Delivered</label>
                                                </div>
                                                <span class="text-muted d-block small mt-1">Parcel successfully handed over to customer.</span>
                                            </div>
                                        </div>

                                        {{-- 8. Exchange --}}
                                        <div class="col-12 col-sm-6 col-md-4 col-xl-2-4">
                                            <div class="border rounded p-3 h-100 sms-trigger-card">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="auto_sms_order_exchange" value="1" {{ (string)($settings['auto_sms_order_exchange'] ?? '1') === '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-semibold small text-info">8. Exchange</label>
                                                </div>
                                                <span class="text-muted d-block small mt-1">When replacement item is approved &amp; swapped.</span>
                                            </div>
                                        </div>

                                        {{-- 9. Returned --}}
                                        <div class="col-12 col-sm-6 col-md-4 col-xl-2-4">
                                            <div class="border rounded p-3 h-100 sms-trigger-card">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="auto_sms_order_returned" value="1" {{ (string)($settings['auto_sms_order_returned'] ?? '1') === '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-semibold small text-muted">9. Returned</label>
                                                </div>
                                                <span class="text-muted d-block small mt-1">Delivery failed or returned to warehouse.</span>
                                            </div>
                                        </div>

                                        {{-- 10. Cancelled --}}
                                        <div class="col-12 col-sm-6 col-md-4 col-xl-2-4">
                                            <div class="border rounded p-3 h-100 sms-trigger-card">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="auto_sms_order_cancelled" value="1" {{ (string)($settings['auto_sms_order_cancelled'] ?? '1') === '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-semibold small text-danger">10. Cancelled</label>
                                                </div>
                                                <span class="text-muted d-block small mt-1">Order cancelled by admin or customer.</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Save Button --}}
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-1"></i> Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- TAB 2: Provider API Gateway --}}
        <div class="tab-pane fade" id="tab-provider" role="tabpanel">
            <div class="row g-4">
                <div class="col-12 col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="fw-semibold mb-0 text-dark">Onecodesoft Credentials</h6>
                        </div>
                        <div class="card-body p-3 p-md-4">
                            <form action="{{ route('admin.sms.settings.provider.update') }}" method="POST">
                                @csrf
                                <input type="hidden" name="provider_id" value="{{ $provider->id ?? 1 }}">
                                <input type="hidden" name="current_tab" value="tab-provider">

                                <div class="mb-3">
                                    <label class="form-label fw-medium small text-dark">API Key / Token <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm font-monospace" name="api_token" value="{{ old('api_token', $provider->decrypted_token ?? '') }}" placeholder="Enter Onecodesoft API Key / Token" required>
                                    <div class="form-text small">Your secret API token from the Onecodesoft dashboard.</div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-sm-6">
                                        <label class="form-label fw-medium small text-dark">Sender ID / Masking</label>
                                        <input type="text" class="form-control form-control-sm" name="sender_id" value="{{ old('sender_id', $provider->sender_id ?? '') }}" placeholder="Leave blank for auto non-masking">
                                        <div class="form-text small" style="font-size: 11px;">Leave blank if not registered for branding.</div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label fw-medium small text-dark">Business / Brand Name</label>
                                        <input type="text" class="form-control form-control-sm" name="business_name" value="{{ old('business_name', $provider->business_name ?? '') }}" placeholder="SHOP NOW BD">
                                    </div>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-sm-6">
                                        <label class="form-label fw-medium small text-dark">Price per SMS (৳)</label>
                                        <input type="number" step="0.0001" class="form-control form-control-sm" name="price_per_sms" value="{{ old('price_per_sms', $provider->price_per_sms ?? 0.3500) }}" required>
                                    </div>
                                    <div class="col-sm-6 d-flex align-items-center gap-4 mt-sm-4 pt-sm-2">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ ($provider->is_active ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label small fw-medium">Active</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="sandbox_mode" value="1" {{ ($provider->sandbox_mode ?? false) ? 'checked' : '' }}>
                                            <label class="form-check-label small fw-medium">Sandbox Mode</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="fas fa-check me-1"></i> Update Credentials
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm bg-light">
                        <div class="card-body p-3 p-md-4 sms-reference-card">
                            <h6 class="fw-semibold text-dark mb-2">Driver Reference</h6>
                            <p class="text-muted small mb-3">Onecodesoft is pre-configured with auto-routing for Bangladeshi numbers (GP, Robi, BL, Teletalk).</p>
                            <ul class="list-unstyled small mb-0 text-muted">
                                <li class="mb-2"><i class="fas fa-check text-success me-1"></i> Non-Masking &amp; Masking support</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-1"></i> Multi-part Unicode concatenation</li>
                                <li><i class="fas fa-check text-success me-1"></i> Instant Delivery Status (DLR)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 3: OTP Policies --}}
        <div class="tab-pane fade" id="tab-otp" role="tabpanel">
            <form action="{{ route('admin.sms.settings.update') }}" method="POST">
                @csrf
                <input type="hidden" name="setting_section" value="otp">
                <input type="hidden" name="current_tab" value="tab-otp">

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-semibold mb-0 text-dark">OTP Security &amp; Rate Limiting</h6>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-medium small text-dark">OTP Engine Mode</label>
                                <select class="form-select form-select-sm" name="otp_mode">
                                    <option value="identity" {{ ($settings['otp_mode'] ?? 'identity') === 'identity' ? 'selected' : '' }}>Identity Mode (Internal DB Engine)</option>
                                    <option value="custom" {{ ($settings['otp_mode'] ?? '') === 'custom' ? 'selected' : '' }}>Custom Gateway OTP</option>
                                </select>
                                <div class="form-text small">Recommended: Identity Mode for unified rate-limiting and audit logging.</div>
                            </div>

                            <div class="col-6 col-md-3">
                                <label class="form-label fw-medium small text-dark">Expiry (Minutes)</label>
                                <input type="number" min="1" max="60" class="form-control form-control-sm" name="otp_expiry_minutes" value="{{ $settings['otp_expiry_minutes'] ?? 5 }}">
                            </div>

                            <div class="col-6 col-md-3">
                                <label class="form-label fw-medium small text-dark">Resend Cooldown (Seconds)</label>
                                <input type="number" min="10" max="300" class="form-control form-control-sm" name="otp_resend_cooldown_seconds" value="{{ $settings['otp_resend_cooldown_seconds'] ?? 60 }}">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-medium small text-dark">Max Verification Attempts</label>
                                <input type="number" min="1" max="10" class="form-control form-control-sm" name="max_otp_attempts" value="{{ $settings['max_otp_attempts'] ?? 3 }}">
                            </div>

                            <div class="col-6 col-md-4">
                                <label class="form-label fw-medium small text-dark">Daily Limit / Phone</label>
                                <input type="number" min="1" max="50" class="form-control form-control-sm" name="max_daily_otp_per_number" value="{{ $settings['max_daily_otp_per_number'] ?? 5 }}">
                            </div>

                            <div class="col-6 col-md-4">
                                <label class="form-label fw-medium small text-dark">Daily Limit / IP</label>
                                <input type="number" min="1" max="100" class="form-control form-control-sm" name="max_daily_otp_per_ip" value="{{ $settings['max_daily_otp_per_ip'] ?? 15 }}">
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i> Save OTP Settings
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

    </div>
</div>

{{-- Modal 1: Send Test SMS Modal --}}
<div class="modal fade" id="testSmsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3">
                <h6 class="modal-title fw-bold">Send Test SMS</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                <div id="testSmsAlert" class="d-none alert small py-2 mb-3"></div>

                <div class="mb-3">
                    <label class="form-label fw-medium small text-dark">Mobile Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm" id="testRecipient" placeholder="017XXXXXXXX or 88017XXXXXXXX" required>
                    <div class="form-text small">Supports 11-digit local format or with 88 country code.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium small text-dark">Message Text</label>
                    <textarea class="form-control form-control-sm" id="testMessage" rows="3" placeholder="Leave empty for standard test message..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-top py-2">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm px-3" id="btnSubmitTestSms" onclick="handleSendTestSms(this)">
                    <span id="testSmsSpinner" class="spinner-border spinner-border-sm me-1 d-none"></span>
                    <i class="fas fa-paper-plane me-1" id="testSmsBtnIcon"></i> <span id="testSmsBtnText">Send Now</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal 2: General Notification & Alert Modal (Replaces JS Alerts) --}}
<div class="modal fade" id="appAlertModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div id="appModalIcon" class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 46px; height: 46px;">
                    <i class="fas fa-check-circle fs-3"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="appModalTitle">Notification</h6>
                <p class="text-muted small mb-3" id="appModalBody" style="font-size: 12px; line-height: 1.45;"></p>
                <div>
                    <button type="button" class="btn btn-dark btn-sm px-4" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Direct In-Content JavaScript (Zero dependency on layout stacks) --}}
<script>
// Generic Modal Alert Function (Replaces browser alert)
function showAppModalAlert(title, message, type = 'success') {
    const modalEl = document.getElementById('appAlertModal');
    if (!modalEl) return;
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

    document.getElementById('appModalTitle').textContent = title;
    document.getElementById('appModalBody').textContent = message;

    const iconBox = document.getElementById('appModalIcon');
    if (type === 'success') {
        iconBox.className = 'rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mx-auto mb-2';
        iconBox.innerHTML = '<i class="fas fa-check-circle fs-3"></i>';
    } else if (type === 'error' || type === 'danger') {
        iconBox.className = 'rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-2';
        iconBox.innerHTML = '<i class="fas fa-times-circle fs-3"></i>';
    } else {
        iconBox.className = 'rounded-circle bg-info-subtle text-info d-inline-flex align-items-center justify-content-center mx-auto mb-2';
        iconBox.innerHTML = '<i class="fas fa-info-circle fs-3"></i>';
    }

    modal.show();
}

// 1. Direct Sync Balance Function with Modal Alerts
function handleSyncBalance(button) {
    const syncIcon = document.getElementById('syncIcon');
    const syncText = document.getElementById('syncBtnText');

    button.disabled = true;
    if (syncIcon) syncIcon.classList.add('fa-spin');
    if (syncText) syncText.innerText = 'Syncing...';

    fetch("{{ route('admin.sms.settings.sync_balance') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Accept": "application/json"
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const balanceEl = document.getElementById('cardBalanceText');
            const estEl = document.getElementById('cardEstSms');
            const syncedEl = document.getElementById('cardLastSynced');

            if (balanceEl) balanceEl.innerText = `৳ ${data.balance}`;
            if (estEl) estEl.innerText = data.estimated_sms;
            if (syncedEl) syncedEl.innerText = 'Just now';
            showAppModalAlert('Success', data.message || 'Gateway balance synced successfully!', 'success');
        } else {
            showAppModalAlert('Gateway Error', data.message || 'Sync failed. Please check credentials.', 'error');
        }
    })
    .catch(err => {
        console.error(err);
        showAppModalAlert('Connection Error', 'Network error occurred while syncing balance.', 'error');
    })
    .finally(() => {
        button.disabled = false;
        if (syncIcon) syncIcon.classList.remove('fa-spin');
        if (syncText) syncText.innerText = 'Sync Balance';
    });
}

// 2. Direct Send Test SMS Function
function handleSendTestSms(button) {
    const phoneInput = document.getElementById('testRecipient');
    const messageInput = document.getElementById('testMessage');
    const alertBox = document.getElementById('testSmsAlert');
    const spinner = document.getElementById('testSmsSpinner');
    const btnIcon = document.getElementById('testSmsBtnIcon');
    const btnText = document.getElementById('testSmsBtnText');

    const phone = phoneInput ? phoneInput.value.trim() : '';
    const message = messageInput ? messageInput.value.trim() : '';

    if (!phone) {
        showModalAlert('danger', 'Please enter a valid mobile number.');
        return;
    }

    button.disabled = true;
    if (spinner) spinner.classList.remove('d-none');
    if (btnIcon) btnIcon.classList.add('d-none');
    if (btnText) btnText.innerText = 'Sending...';
    if (alertBox) alertBox.classList.add('d-none');

    fetch("{{ route('admin.sms.settings.test') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Accept": "application/json"
        },
        body: JSON.stringify({ phone_number: phone, message: message })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showModalAlert('success', data.message || 'Test SMS sent successfully.');
            // Auto sync balance card after successful test send
            const btnSync = document.getElementById('btnSyncBalance');
            if (btnSync) handleSyncBalance(btnSync);
        } else {
            showModalAlert('danger', data.message || 'Failed to send test SMS.');
        }
    })
    .catch(err => {
        console.error(err);
        showModalAlert('danger', 'Gateway connectivity error.');
    })
    .finally(() => {
        button.disabled = false;
        if (spinner) spinner.classList.add('d-none');
        if (btnIcon) btnIcon.classList.remove('d-none');
        if (btnText) btnText.innerText = 'Send Now';
    });
}

function showModalAlert(type, msg) {
    const alertBox = document.getElementById('testSmsAlert');
    if (alertBox) {
        alertBox.className = `alert alert-${type} small py-2 mb-3`;
        alertBox.innerText = msg;
        alertBox.classList.remove('d-none');
    }
}

// 3. Tab Persistence & Selection
document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);
    const activeTabTarget = urlParams.get('tab') || window.location.hash.replace('#', '') || 'tab-general';

    const initialTabTrigger = document.querySelector(`[data-bs-target="#${activeTabTarget}"]`);
    if (initialTabTrigger && typeof bootstrap !== 'undefined') {
        const tabInstance = new bootstrap.Tab(initialTabTrigger);
        tabInstance.show();
    }

    document.querySelectorAll('#smsSettingsTabs button[data-bs-toggle="tab"]').forEach(btn => {
        btn.addEventListener('shown.bs.tab', function (e) {
            const targetId = e.target.getAttribute('data-bs-target').replace('#', '');
            const newUrl = new URL(window.location.href);
            newUrl.searchParams.set('tab', targetId);
            history.replaceState(null, null, newUrl.toString());
        });
    });

    // Toggle icon rotation on accordion collapse
    const orderGroupEl = document.getElementById('orderSettingsGroupCollapse');
    const orderGroupChevron = document.getElementById('orderGroupChevron');
    if (orderGroupEl && orderGroupChevron) {
        orderGroupEl.addEventListener('show.bs.collapse', function () {
            orderGroupChevron.className = 'fas fa-chevron-down';
        });
        orderGroupEl.addEventListener('hide.bs.collapse', function () {
            orderGroupChevron.className = 'fas fa-chevron-right';
        });
    }
});
</script>
@endsection