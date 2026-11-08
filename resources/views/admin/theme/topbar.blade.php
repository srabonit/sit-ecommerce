
@extends('layouts.admin')

@section('title', 'Topbar Settings - ' . config('app.name'))

@section('admin_content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

<style>
    #topbarSettingsRoot,
    .topbar-modal-scope {
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif !important;
        color: #1e293b;
        font-size: 12px;
        letter-spacing: -0.01em;
    }

    #topbarSettingsRoot *,
    .topbar-modal-scope * {
        box-sizing: border-box;
    }

    /* Icon protection */
    #topbarSettingsRoot .fas,
    #topbarSettingsRoot .far,
    #topbarSettingsRoot .fab,
    #topbarSettingsRoot .fa,
    .topbar-modal-scope .fas,
    .topbar-modal-scope .far,
    .topbar-modal-scope .fa {
        font-family: "Font Awesome 6 Free", "Font Awesome 5 Free", FontAwesome !important;
        font-style: normal;
        line-height: 1;
        letter-spacing: normal;
        display: inline-block;
    }

    #topbarSettingsRoot .far,
    .topbar-modal-scope .far {
        font-weight: 400 !important;
    }

    /* Consistent radius */
    #topbarSettingsRoot .btn,
    #topbarSettingsRoot button,
    #topbarSettingsRoot .badge,
    #topbarSettingsRoot .alert,
    #topbarSettingsRoot .card,
    #topbarSettingsRoot .form-control,
    #topbarSettingsRoot .status-kpi-pill,
    #topbarSettingsRoot .toggle-item-box,
    #topbarSettingsRoot .color-sync-card,
    #topbarSettingsRoot .notice-item-card,
    #topbarSettingsRoot .collapse-toggle-btn,
    .topbar-modal-scope .btn,
    .topbar-modal-scope .form-control,
    .topbar-modal-scope .modal-content {
        border-radius: 5px !important;
    }

    #topbarSettingsRoot .input-group > .input-group-text {
        border-radius: 5px 0 0 5px !important;
    }

    #topbarSettingsRoot .input-group > .form-control {
        border-radius: 0 5px 5px 0 !important;
    }

    .bn-digit,
    .font-tabular {
        font-variant-numeric: tabular-nums;
        font-weight: 700 !important;
    }

    /* Buttons */
    #topbarSettingsRoot .btn-compact {
        min-height: 32px;
        padding: 6px 12px !important;
        font-size: 11px !important;
        line-height: 1.3;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        white-space: nowrap;
        transition: all .18s ease;
    }

    #topbarSettingsRoot .btn-primary {
        background: #2563eb !important;
        border-color: #2563eb !important;
        color: #fff !important;
    }

    #topbarSettingsRoot .btn-primary:hover {
        background: #1d4ed8 !important;
        border-color: #1d4ed8 !important;
    }

    #topbarSettingsRoot .btn-outline-secondary {
        background: #fff !important;
        border-color: #cbd5e1 !important;
        color: #334155 !important;
    }

    #topbarSettingsRoot .btn-outline-secondary:hover {
        background: #f1f5f9 !important;
        border-color: #94a3b8 !important;
    }

    /* Main header */
    #topbarSettingsRoot .topbar-header-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 5px !important;
        padding: 15px 18px;
        margin-bottom: 12px;
        box-shadow: 0 1px 3px rgba(15,23,42,.04);
    }

    #topbarSettingsRoot .topbar-page-title {
        font-size: 17px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.4;
        margin: 0;
    }

    #topbarSettingsRoot .topbar-page-subtitle {
        color: #64748b;
        font-size: 11px;
        line-height: 1.5;
        margin-top: 4px;
    }

    #topbarSettingsRoot .topbar-header-badge {
        font-size: 9px;
        font-weight: 700;
        padding: 5px 8px;
        letter-spacing: .04em;
    }

    /* KPI */
    #topbarSettingsRoot .status-kpi-pill {
        height: 100%;
        min-height: 68px;
        padding: 10px 13px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        background: #fff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 2px rgba(15,23,42,.02);
    }

    #topbarSettingsRoot .kpi-label {
        display: block;
        color: #64748b;
        font-size: 10.5px;
        font-weight: 600;
        margin-bottom: 3px;
    }

    #topbarSettingsRoot .kpi-value {
        display: block;
        font-size: 12.5px;
        font-weight: 700;
        line-height: 1.4;
    }

    #topbarSettingsRoot .kpi-icon {
        width: 34px;
        height: 34px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
        background: #eff6ff;
        color: #2563eb;
        font-size: 15px;
    }

    /* Section cards */
    #topbarSettingsRoot .topbar-section-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 5px !important;
        overflow: hidden;
        margin-bottom: 11px;
        box-shadow: 0 1px 3px rgba(15,23,42,.035);
    }

    #topbarSettingsRoot .topbar-section-header {
        min-height: 45px;
        padding: 9px 13px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        background: #fff;
        border-bottom: 1px solid #f1f5f9;
    }

    #topbarSettingsRoot .section-title-text {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
        color: #1e293b;
        font-size: 12px;
        font-weight: 700;
    }

    #topbarSettingsRoot .section-title-text > i {
        width: 23px;
        height: 23px;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
        background: #eff6ff;
        color: #2563eb;
        font-size: 11px;
    }

    #topbarSettingsRoot .section-tag {
        font-size: 8.5px;
        font-weight: 700;
        padding: 4px 7px;
        white-space: nowrap;
    }

    /* Collapse button */
    #topbarSettingsRoot .collapse-toggle-btn {
        width: 25px;
        height: 25px;
        padding: 0;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #64748b;
        cursor: pointer;
        transition: all .2s ease;
    }

    #topbarSettingsRoot .collapse-toggle-btn:hover {
        color: #fff;
        background: #2563eb;
        border-color: #2563eb;
    }

    #topbarSettingsRoot .collapse-icon {
        font-size: 10px;
        transition: transform .2s ease;
    }

    #topbarSettingsRoot .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    /* Form controls */
    #topbarSettingsRoot .form-label,
    .topbar-modal-scope .form-label {
        display: block;
        color: #334155;
        font-size: 10.5px;
        font-weight: 700;
        margin-bottom: 5px;
    }

    #topbarSettingsRoot .form-control,
    .topbar-modal-scope .form-control {
        min-height: 34px;
        padding: 6px 9px;
        border: 1px solid #cbd5e1;
        background-color: #fff;
        color: #0f172a;
        font-size: 11.5px;
        font-weight: 500;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #topbarSettingsRoot .form-control:focus,
    .topbar-modal-scope .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37,99,235,.11);
        outline: none;
    }

    #topbarSettingsRoot textarea.form-control {
        min-height: 78px;
        line-height: 1.5;
        resize: vertical;
    }

    #topbarSettingsRoot .form-control::placeholder,
    .topbar-modal-scope .form-control::placeholder {
        color: #94a3b8;
        font-weight: 400;
    }

    /* Toggle boxes */
    #topbarSettingsRoot .toggle-item-box {
        min-height: 70px;
        height: 100%;
        padding: 11px 13px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        background: #fff;
        border: 1px solid #e2e8f0;
        transition: all .15s ease;
    }

    #topbarSettingsRoot .toggle-item-box:hover {
        border-color: #bfdbfe;
        background: #f8fbff;
    }

    #topbarSettingsRoot .toggle-title {
        color: #1e293b;
        font-size: 11.5px;
        font-weight: 700;
        cursor: pointer;
        margin-bottom: 3px;
    }

    #topbarSettingsRoot .toggle-description {
        display: block;
        color: #64748b;
        font-size: 10px;
        line-height: 1.45;
    }

    #topbarSettingsRoot .form-switch .form-check-input {
        width: 34px;
        height: 18px;
        margin: 0;
        cursor: pointer;
        border-color: #cbd5e1;
        border-radius: 20px !important;
        box-shadow: none;
    }

    #topbarSettingsRoot .form-switch .form-check-input:checked {
        background-color: #2563eb;
        border-color: #2563eb;
    }

    /* Color controls */
    #topbarSettingsRoot .color-sync-card {
        min-height: 39px;
        padding: 4px 8px;
        display: flex;
        align-items: center;
        gap: 8px;
        background: #fff;
        border: 1px solid #cbd5e1;
        transition: all .15s ease;
    }

    #topbarSettingsRoot .color-sync-card:focus-within {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37,99,235,.1);
    }

    #topbarSettingsRoot .color-picker-input {
        width: 29px;
        height: 27px;
        padding: 0;
        border: 0;
        background: transparent;
        cursor: pointer;
        flex-shrink: 0;
    }

    #topbarSettingsRoot .color-code-input {
        width: 100%;
        min-width: 0;
        padding: 4px 8px;
        border: 0;
        border-left: 1px solid #e2e8f0;
        outline: none;
        background: transparent;
        color: #0f172a;
        font-size: 11px;
        font-weight: 700;
    }

    /* Notice list */
    #topbarSettingsRoot .notice-item-card {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 7px 9px;
        margin-bottom: 7px;
        background: #fff;
        border: 1px solid #e2e8f0;
        transition: border-color .15s ease;
    }

    #topbarSettingsRoot .notice-item-card:hover {
        border-color: #bfdbfe;
    }

    #topbarSettingsRoot .notice-icon-input {
        flex: 0 0 180px;
        max-width: 180px;
        font-size: 10.5px;
    }

    #topbarSettingsRoot .notice-text-input {
        flex: 1;
        min-width: 0;
        font-size: 11.5px;
    }

    #topbarSettingsRoot .btn-notice-delete-sq {
        width: 32px;
        height: 32px;
        min-width: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border: 1px solid #fecaca;
        background: #fff;
        color: #dc2626;
        font-size: 11px;
        cursor: pointer;
        transition: all .15s ease;
    }

    #topbarSettingsRoot .btn-notice-delete-sq:hover {
        background: #dc2626;
        border-color: #dc2626;
        color: #fff;
    }

    /* Bottom save */
    #topbarSettingsRoot .topbar-save-bar {
        position: sticky;
        bottom: 0;
        z-index: 10;
        padding: 10px 0;
        background: rgba(248,250,252,.94);
        backdrop-filter: blur(8px);
    }

    #topbarSettingsRoot .topbar-save-bar .btn {
        min-height: 37px;
    }

    /* Modal */
    .topbar-modal-scope .modal-content {
        overflow: hidden;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 18px 45px rgba(15,23,42,.16);
    }

    .topbar-modal-scope .modal-header,
    .topbar-modal-scope .modal-footer {
        padding: 11px 14px;
    }

    .topbar-modal-scope .modal-body {
        padding: 14px;
    }

    .topbar-modal-scope .modal-title {
        font-size: 13px;
        font-weight: 700;
    }

    .topbar-modal-scope .minimal-modal-card {
        padding: 22px !important;
        background: #fff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 5px !important;
        text-align: left;
    }

    .topbar-modal-scope .minimal-modal-title {
        margin-bottom: 7px;
        color: #0f172a;
        font-size: 15px;
        font-weight: 800;
    }

    .topbar-modal-scope .minimal-modal-subtext {
        margin-bottom: 18px;
        color: #64748b;
        font-size: 11.5px;
        line-height: 1.6;
    }

    .topbar-modal-scope .btn-minimal-delete,
    .topbar-modal-scope .btn-minimal-action {
        min-height: 34px;
        padding: 7px 17px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 5px !important;
        color: #fff;
        font-size: 11.5px;
        font-weight: 700;
        cursor: pointer;
        transition: all .15s ease;
    }

    .topbar-modal-scope .btn-minimal-delete {
        background: #dc2626;
    }

    .topbar-modal-scope .btn-minimal-delete:hover {
        background: #b91c1c;
    }

    .topbar-modal-scope .btn-minimal-action {
        background: #2563eb;
    }

    .topbar-modal-scope .btn-minimal-action:hover {
        background: #1d4ed8;
    }

    .topbar-modal-scope .btn-minimal-cancel {
        min-height: 34px;
        padding: 7px 12px;
        border: 0;
        background: transparent;
        color: #64748b;
        font-size: 11.5px;
        font-weight: 600;
        cursor: pointer;
    }

    .topbar-modal-scope .btn-minimal-cancel:hover {
        color: #0f172a;
    }

    /* Responsive */
    @media (max-width: 767.98px) {
        #topbarSettingsRoot {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        #topbarSettingsRoot .topbar-header-card {
            padding: 12px;
        }

        #topbarSettingsRoot .header-top-wrap {
            align-items: stretch !important;
        }

        #topbarSettingsRoot .topbar-page-title {
            font-size: 15px;
        }

        #topbarSettingsRoot .top-action-btns {
            width: 100%;
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            gap: 7px;
        }

        #topbarSettingsRoot .top-action-btns .btn {
            width: 100%;
            min-width: 0;
            white-space: normal;
            padding: 7px 6px !important;
        }

        #topbarSettingsRoot .topbar-section-header {
            padding: 9px 10px;
        }

        #topbarSettingsRoot .section-title-text {
            font-size: 11px;
        }

        #topbarSettingsRoot .section-tag {
            display: none;
        }

        #topbarSettingsRoot .notice-item-card {
            flex-wrap: wrap;
            gap: 6px;
            padding: 8px;
        }

        #topbarSettingsRoot .notice-icon-input {
            flex: 1 1 100%;
            max-width: 100%;
        }

        #topbarSettingsRoot .notice-text-input {
            flex: 1 1 0;
            width: 100%;
        }

        #topbarSettingsRoot .btn-notice-delete-sq {
            width: 34px;
            height: 34px;
        }

        #topbarSettingsRoot .topbar-save-bar .btn {
            width: 100%;
        }

        .topbar-modal-scope .minimal-modal-card {
            padding: 18px !important;
        }
    }

    @media (max-width: 380px) {
        #topbarSettingsRoot .topbar-page-title {
            font-size: 14px;
        }

        #topbarSettingsRoot .top-action-btns {
            grid-template-columns: 1fr;
        }

        #topbarSettingsRoot .status-kpi-pill {
            padding: 9px 10px;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-2" id="topbarSettingsRoot">

    <!-- Header -->
    <div class="topbar-header-card d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 header-top-wrap">
        <div class="min-w-0">
            <h5 class="topbar-page-title">
                <i class="fas fa-bullhorn text-primary me-1"></i>
                Topbar Settings
            </h5>

            <p class="topbar-page-subtitle mb-0">
                Manage visibility, colors and announcements.
            </p>
        </div>

        <div class="top-action-btns d-flex gap-2 flex-shrink-0">
            <button type="button" class="btn btn-outline-secondary btn-compact" onclick="toggleAllTopbarSections()">
                <i class="fas fa-arrows-up-down"></i>
                <span id="expandAllTopbarBtnText">Collapse All</span>
            </button>

            <button type="button" class="btn btn-primary btn-compact fw-bold px-3" onclick="document.getElementById('topbarSettingsForm').submit()">
                <i class="fas fa-save"></i>
                Save Settings
            </button>
        </div>
    </div>

    <!-- Summary -->
    @php
        $desktopOn = ($settings['show_topbar_desktop'] ?? '1') == '1';
        $mobileOn  = ($settings['show_topbar_mobile'] ?? '1') == '1';
        $noticesCount = count($notices ?? []);
    @endphp

    <div class="row g-2 mb-3">
        <div class="col-12 col-md-4">
            <div class="status-kpi-pill">
                <div class="min-w-0">
                    <small class="kpi-label">Desktop</small>
                    <strong class="kpi-value text-{{ $desktopOn ? 'success' : 'secondary' }}">
                        {{ $desktopOn ? 'Enabled' : 'Disabled' }}
                    </strong>
                </div>
                <div class="kpi-icon">
                    <i class="fas fa-desktop"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="status-kpi-pill">
                <div class="min-w-0">
                    <small class="kpi-label">Mobile &amp; Tablet</small>
                    <strong class="kpi-value text-{{ $mobileOn ? 'success' : 'secondary' }}">
                        {{ $mobileOn ? 'Enabled' : 'Disabled' }}
                    </strong>
                </div>
                <div class="kpi-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="status-kpi-pill">
                <div class="min-w-0">
                    <small class="kpi-label">Notices</small>
                    <strong class="kpi-value text-primary bn-digit font-tabular">
                        {{ $noticesCount }} {{ Str::plural('Notice', $noticesCount) }}
                    </strong>
                </div>
                <div class="kpi-icon">
                    <i class="fas fa-list-check"></i>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm py-2 px-3 mb-3 small d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle"></i>
                <span class="fw-semibold">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.theme.setting.topbar.update') }}" method="POST" id="topbarSettingsForm">
        @csrf

        <!-- Section 1: Visibility -->
        <div class="topbar-section-card">
            <div class="topbar-section-header">
                <div class="section-title-text">
                    <i class="fas fa-eye"></i>
                    <span>Display Visibility</span>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle section-tag">DEVICE ACCESS</span>
                    <button type="button" class="collapse-toggle-btn" data-bs-toggle="collapse" data-bs-target="#collapseVisibilitySection" aria-expanded="true" aria-controls="collapseVisibilitySection" title="Toggle Section">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>
                </div>
            </div>

            <div class="collapse show" id="collapseVisibilitySection">
                <div class="p-3 bg-light bg-opacity-25">
                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <div class="toggle-item-box">
                                <div class="pe-2 min-w-0">
                                    <label class="toggle-title d-block" for="sw_show_desktop">
                                        Desktop Display
                                    </label>
                                    <small class="toggle-description">
                                        Show topbar on desktop screens.
                                    </small>
                                </div>
                                <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                    <input class="form-check-input ms-0" type="checkbox" name="show_topbar_desktop" id="sw_show_desktop" value="1" {{ ($settings['show_topbar_desktop'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="toggle-item-box">
                                <div class="pe-2 min-w-0">
                                    <label class="toggle-title d-block" for="sw_show_mobile">
                                        Mobile &amp; Tablet Display
                                    </label>
                                    <small class="toggle-description">
                                        Show topbar on mobile devices.
                                    </small>
                                </div>
                                <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                    <input class="form-check-input ms-0" type="checkbox" name="show_topbar_mobile" id="sw_show_mobile" value="1" {{ ($settings['show_topbar_mobile'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Colors -->
        <div class="topbar-section-card">
            <div class="topbar-section-header">
                <div class="section-title-text">
                    <i class="fas fa-palette"></i>
                    <span>Color Settings</span>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle section-tag">COLOR PALETTE</span>
                    <button type="button" class="collapse-toggle-btn" data-bs-toggle="collapse" data-bs-target="#collapseColorsSection" aria-expanded="true" aria-controls="collapseColorsSection" title="Toggle Section">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>
                </div>
            </div>

            <div class="collapse show" id="collapseColorsSection">
                <div class="p-3 bg-light bg-opacity-25">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Background Color</label>
                            <div class="color-sync-card">
                                <input type="color" id="topbarBgPicker" class="color-picker-input" value="{{ $settings['topbar_bg'] ?? '#0066ff' }}" oninput="syncColorValues(this, 'topbarBgText')">
                                <input type="text" id="topbarBgText" name="topbar_bg" class="color-code-input" value="{{ $settings['topbar_bg'] ?? '#0066ff' }}" placeholder="#0066ff" oninput="syncTextValues(this, 'topbarBgPicker')">
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Text &amp; Icon Color</label>
                            <div class="color-sync-card">
                                <input type="color" id="topbarColorPicker" class="color-picker-input" value="{{ $settings['topbar_color'] ?? '#ffffff' }}" oninput="syncColorValues(this, 'topbarColorText')">
                                <input type="text" id="topbarColorText" name="topbar_color" class="color-code-input" value="{{ $settings['topbar_color'] ?? '#ffffff' }}" placeholder="#ffffff" oninput="syncTextValues(this, 'topbarColorPicker')">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Animation -->
        <div class="topbar-section-card">
            <div class="topbar-section-header">
                <div class="section-title-text">
                    <i class="fas fa-sliders-h"></i>
                    <span>Slider Animation</span>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle section-tag">TICKER MOTION</span>
                    <button type="button" class="collapse-toggle-btn" data-bs-toggle="collapse" data-bs-target="#collapseAnimationSection" aria-expanded="true" aria-controls="collapseAnimationSection" title="Toggle Section">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>
                </div>
            </div>

            <div class="collapse show" id="collapseAnimationSection">
                <div class="p-3 bg-light bg-opacity-25">
                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <div class="toggle-item-box">
                                <div class="pe-2 min-w-0">
                                    <label class="toggle-title d-block" for="sw_slider_desktop">
                                        Desktop Slider
                                    </label>
                                    <small class="toggle-description">
                                        Enable ticker animation on desktop.
                                    </small>
                                </div>
                                <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                    <input class="form-check-input ms-0" type="checkbox" name="topbar_slider_desktop" id="sw_slider_desktop" value="1" {{ ($settings['topbar_slider_desktop'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="toggle-item-box">
                                <div class="pe-2 min-w-0">
                                    <label class="toggle-title d-block" for="sw_slider_mobile">
                                        Mobile Slider
                                    </label>
                                    <small class="toggle-description">
                                        Enable ticker animation on mobile.
                                    </small>
                                </div>
                                <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                    <input class="form-check-input ms-0" type="checkbox" name="topbar_slider_mobile" id="sw_slider_mobile" value="1" {{ ($settings['topbar_slider_mobile'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 4: Notices -->
        <div class="topbar-section-card">
            <div class="topbar-section-header">
                <div class="section-title-text">
                    <i class="fas fa-list-check"></i>
                    <span>Announcements</span>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-primary btn-compact py-0 px-2" style="height: 27px;" data-bs-toggle="modal" data-bs-target="#addNoticeModal">
                        <i class="fas fa-plus"></i> Add Notice
                    </button>

                    <button type="button" class="collapse-toggle-btn" data-bs-toggle="collapse" data-bs-target="#collapseNoticesSection" aria-expanded="true" aria-controls="collapseNoticesSection" title="Toggle Section">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>
                </div>
            </div>

            <div class="collapse show" id="collapseNoticesSection">
                <div class="p-3">
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                        <small class="text-muted" style="font-size: 10.5px;">
                            Ticker announcement list
                        </small>

                        <span class="badge bg-light text-dark border bn-digit font-tabular px-2 py-1" style="font-size: 10px;" id="noticesCountBadge">
                            {{ count($notices) }} Items
                        </span>
                    </div>

                    <div id="noticesContainer">
                        @foreach($notices as $item)
                            <div class="notice-item-card notice-item-row">
                                <input type="text" name="notice_icons[]" class="form-control notice-icon-input" value="{{ $item['icon'] ?? 'fas fa-check-circle' }}" placeholder="fas fa-bullhorn" title="FontAwesome Icon Class">

                                <input type="text" name="notice_texts[]" class="form-control notice-text-input" value="{{ $item['text'] }}" placeholder="Announcement text..." required>

                                <button type="button" class="btn-notice-delete-sq" onclick="confirmDeleteNotice(this)" title="Remove notice">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 5: Fallback -->
        <div class="topbar-section-card">
            <div class="topbar-section-header">
                <div class="section-title-text">
                    <i class="fas fa-code"></i>
                    <span>Static Content</span>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-secondary-subtle text-secondary border section-tag">FALLBACK MODE</span>
                    <button type="button" class="collapse-toggle-btn" data-bs-toggle="collapse" data-bs-target="#collapseFallbackSection" aria-expanded="true" aria-controls="collapseFallbackSection" title="Toggle Section">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>
                </div>
            </div>

            <div class="collapse show" id="collapseFallbackSection">
                <div class="p-3 bg-light bg-opacity-25">
                    <small class="text-muted d-block mb-3" style="font-size: 10.5px;">
                        Shown when the slider is disabled.
                    </small>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Left Content (HTML Allowed)</label>
                            <textarea name="topbar_left_html" class="form-control" rows="3" placeholder="Contact details or HTML...">{{ $settings['topbar_left_html'] ?? '' }}</textarea>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Right Content (HTML Allowed)</label>
                            <textarea name="topbar_right_html" class="form-control" rows="3" placeholder="Links or social handles...">{{ $settings['topbar_right_html'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Save -->
        <div class="topbar-save-bar d-flex justify-content-end">
            <button type="submit" class="btn btn-primary btn-compact fw-bold px-4">
                <i class="fas fa-save me-1"></i> Save Settings
            </button>
        </div>
    </form>
</div>

<!-- Add Notice Modal -->
<div class="modal fade topbar-modal-scope" id="addNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light border-bottom">
                <h6 class="modal-title text-dark d-flex align-items-center gap-2 mb-0">
                    <i class="fas fa-plus-circle text-primary"></i>
                    Add Announcement
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Icon Class</label>
                    <input type="text" id="modalNoticeIcon" class="form-control" placeholder="fas fa-check-circle" value="fas fa-check-circle">
                    <small class="text-muted d-block mt-1" style="font-size: 10px;">
                        <code>fas fa-truck</code>, <code>fas fa-bolt</code>, <code>fas fa-headset</code>
                    </small>
                </div>

                <div>
                    <label class="form-label">Announcement Text *</label>
                    <textarea id="modalNoticeText" class="form-control" rows="3" placeholder="Enter announcement..." required></textarea>
                </div>
            </div>

            <div class="modal-footer bg-light border-top justify-content-between">
                <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="button" class="btn btn-primary btn-compact fw-bold px-3" onclick="addNoticeFromModal()">
                    <i class="fas fa-plus me-1"></i> Add Notice
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade topbar-modal-scope" id="deleteNoticeConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content minimal-modal-card">
            <h6 class="minimal-modal-title">Delete this notice?</h6>

            <p class="minimal-modal-subtext mb-2">
                <span id="deleteNoticePreviewText" class="fw-semibold text-dark">This notice</span>
                will be removed from the ticker.
            </p>

            <div id="deleteNoticeWarning" class="alert alert-warning border-0 py-2 px-2 mt-2 mb-3 small d-none" style="font-size: 10.5px;">
                <i class="fas fa-exclamation-triangle me-1"></i>
                Keep at least one notice.
            </div>

            <div class="d-flex align-items-center gap-2 pt-1">
                <button type="button" id="confirmDeleteNoticeBtn" class="btn-minimal-delete" onclick="executeNoticeDelete()">
                    <i class="fas fa-trash-alt me-1"></i> Delete
                </button>

                <button type="button" class="btn-minimal-cancel" data-bs-dismiss="modal">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Notification Modal -->
<div class="modal fade topbar-modal-scope" id="topbarNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 108500;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
        <div class="modal-content minimal-modal-card">
            <h6 class="minimal-modal-title" id="topbarNoticeTitle">Notification</h6>
            <p class="minimal-modal-subtext" id="topbarNoticeMessage" style="margin-bottom: 16px !important;"></p>

            <div class="d-flex align-items-center">
                <button type="button" class="btn-minimal-action" data-bs-dismiss="modal">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Universal Notice Modal
function showTopbarModalNotice(message, title = 'Notification') {
    const titleEl = document.getElementById('topbarNoticeTitle');
    const msgEl = document.getElementById('topbarNoticeMessage');

    if (titleEl) titleEl.innerText = title;
    if (msgEl) msgEl.innerText = message;

    const modalEl = document.getElementById('topbarNoticeModal');

    if (modalEl && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

// Two-way Color Sync
function syncColorValues(colorPicker, textInputId) {
    const textInput = document.getElementById(textInputId);

    if (textInput) {
        textInput.value = colorPicker.value.toLowerCase();
    }
}

function syncTextValues(textInput, colorPickerId) {
    const colorPicker = document.getElementById(colorPickerId);
    let val = textInput.value.trim();

    if (!val.startsWith('#') && val.length > 0) {
        val = '#' + val;
    }

    const hexPattern = /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/;

    if (hexPattern.test(val) && colorPicker) {
        colorPicker.value = val;
    }
}

// Add Notice
function addNoticeFromModal() {
    const iconInput = document.getElementById('modalNoticeIcon');
    const textInput = document.getElementById('modalNoticeText');

    const textVal = textInput.value.trim();
    const iconVal = iconInput.value.trim() || 'fas fa-check-circle';

    if (!textVal) {
        textInput.focus();
        showTopbarModalNotice('Please enter announcement text.', 'Required Field');
        return;
    }

    const container = document.getElementById('noticesContainer');
    const newRow = document.createElement('div');

    newRow.className = 'notice-item-card notice-item-row';

    newRow.innerHTML = `
        <input type="text" name="notice_icons[]" class="form-control notice-icon-input" value="${iconVal}" placeholder="fas fa-tag">
        <input type="text" name="notice_texts[]" class="form-control notice-text-input" value="${textVal}" placeholder="Announcement text..." required>
        <button type="button" class="btn-notice-delete-sq" onclick="confirmDeleteNotice(this)" title="Remove notice">
            <i class="fas fa-trash-alt"></i>
        </button>
    `;

    container.insertBefore(newRow, container.firstChild);
    updateNoticeItemCount();

    textInput.value = '';

    const modalElem = document.getElementById('addNoticeModal');

    if (modalElem && window.bootstrap) {
        bootstrap.Modal.getInstance(modalElem)?.hide();
    }

    showTopbarModalNotice('Announcement added to the ticker.', 'Item Added');
}

// Delete Confirmation
let targetNoticeRow = null;

function confirmDeleteNotice(btn) {
    targetNoticeRow = btn.closest('.notice-item-row');

    const textInput = targetNoticeRow
        ? targetNoticeRow.querySelector('.notice-text-input')
        : null;

    const previewText = textInput && textInput.value.trim()
        ? '"' + textInput.value.trim() + '"'
        : 'this notice';

    const previewElem = document.getElementById('deleteNoticePreviewText');

    if (previewElem) {
        previewElem.textContent = previewText.length > 32
            ? previewText.substring(0, 32) + '..."'
            : previewText;
    }

    const rows = document.querySelectorAll('.notice-item-row');
    const warningAlert = document.getElementById('deleteNoticeWarning');
    const confirmBtn = document.getElementById('confirmDeleteNoticeBtn');

    if (rows.length <= 1) {
        if (warningAlert) warningAlert.classList.remove('d-none');
        if (confirmBtn) confirmBtn.classList.add('d-none');
    } else {
        if (warningAlert) warningAlert.classList.add('d-none');
        if (confirmBtn) confirmBtn.classList.remove('d-none');
    }

    const deleteModal = new bootstrap.Modal(
        document.getElementById('deleteNoticeConfirmModal')
    );

    deleteModal.show();
}

function executeNoticeDelete() {
    const rows = document.querySelectorAll('.notice-item-row');

    if (rows.length > 1 && targetNoticeRow) {
        targetNoticeRow.remove();
        targetNoticeRow = null;

        updateNoticeItemCount();

        showTopbarModalNotice('Notice removed from the ticker.', 'Deleted');
    }

    const modalElem = document.getElementById('deleteNoticeConfirmModal');

    if (modalElem && window.bootstrap) {
        bootstrap.Modal.getInstance(modalElem)?.hide();
    }
}

// Notice Count
function updateNoticeItemCount() {
    const count = document.querySelectorAll('.notice-item-row').length;
    const badge = document.getElementById('noticesCountBadge');

    if (badge) {
        badge.innerText = count + ' Items';
    }
}

// Expand / Collapse All
let allTopbarSectionsExpanded = true;

function toggleAllTopbarSections() {
    const collapses = document.querySelectorAll(
        '#topbarSettingsRoot .topbar-section-card .collapse'
    );

    const toggleBtns = document.querySelectorAll(
        '#topbarSettingsRoot .topbar-section-card .collapse-toggle-btn'
    );

    const btnText = document.getElementById('expandAllTopbarBtnText');

    allTopbarSectionsExpanded = !allTopbarSectionsExpanded;

    collapses.forEach(col => {
        if (allTopbarSectionsExpanded) {
            bootstrap.Collapse.getOrCreateInstance(col, {
                toggle: false
            }).show();
        } else {
            bootstrap.Collapse.getOrCreateInstance(col, {
                toggle: false
            }).hide();
        }
    });

    toggleBtns.forEach(btn => {
        btn.setAttribute(
            'aria-expanded',
            allTopbarSectionsExpanded ? 'true' : 'false'
        );
    });

    if (btnText) {
        btnText.innerText = allTopbarSectionsExpanded
            ? 'Collapse All'
            : 'Expand All';
    }
}
</script>
@endsection
