@extends('layouts.admin')

@section('title', 'Security Settings, Rules & Allowlist - ' . config('app.name'))

@section('admin_content')
<!-- Font Awesome 6.5.1 CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<!-- Google Fonts: Plus Jakarta Sans & Inter -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

<style>
    /* Professional Enterprise Typography */
    #security-settings-wrapper,
    .sec-settings-modal-scope {
        font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        letter-spacing: -0.01em;
        color: #1e293b;
    }

    /* FontAwesome Icon Protection */
    #security-settings-wrapper i,
    #security-settings-wrapper .fa,
    #security-settings-wrapper .fas,
    #security-settings-wrapper .far,
    #security-settings-wrapper .fab,
    #security-settings-wrapper .fa-solid,
    #security-settings-wrapper .fa-regular,
    .sec-settings-modal-scope i,
    .sec-settings-modal-scope .fa,
    .sec-settings-modal-scope .fa-solid {
        font-family: "Font Awesome 6 Free", "Font Awesome 5 Free", "FontAwesome" !important;
        font-style: normal;
        font-weight: 900;
        line-height: 1;
        letter-spacing: normal !important;
        display: inline-block;
    }
    #security-settings-wrapper .far,
    .sec-settings-modal-scope .far {
        font-weight: 400 !important;
    }

    #security-settings-wrapper {
        background-color: #f8fafc;
        min-height: 100vh;
    }

    /* Card Theme Matching Clean White Surface */
    .sec-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        transition: box-shadow 0.15s ease, border-color 0.15s ease;
    }
    .sec-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
    }

    /* Tabular Numeric Data Alignment */
    .bn-digit,
    .font-tabular {
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif !important;
        font-weight: 700 !important;
        font-variant-numeric: tabular-nums;
    }

    /* Clean Minimal Header Icon Box */
    .sec-header-icon-box {
        width: 38px !important;
        height: 38px !important;
        min-width: 38px !important;
        border-radius: 8px !important;
        background-color: #eff6ff !important;
        border: 1px solid #bfdbfe !important;
        color: #2563eb !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
    }
    .sec-header-icon-box i {
        font-size: 15px !important;
    }

    /* Section Header Soft Icon Box */
    .card-title-icon {
        width: 30px !important;
        height: 30px !important;
        min-width: 30px !important;
        border-radius: 6px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 12px !important;
        flex-shrink: 0 !important;
        margin-right: 8px !important;
    }
    .icon-soft-blue   { background-color: #eff6ff !important; border: 1px solid #bfdbfe !important; color: #2563eb !important; }
    .icon-soft-teal   { background-color: #f0fdfa !important; border: 1px solid #99f6e4 !important; color: #0d9488 !important; }
    .icon-soft-green  { background-color: #f0fdf4 !important; border: 1px solid #bbf7d0 !important; color: #16a34a !important; }
    .icon-soft-amber  { background-color: #fffbeb !important; border: 1px solid #fef3c7 !important; color: #d97706 !important; }
    .icon-soft-red    { background-color: #fef2f2 !important; border: 1px solid #fecaca !important; color: #dc2626 !important; }

    /* Page Titles & Subtitles */
    .sec-page-title {
        font-size: 17.5px !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        line-height: 1.25 !important;
        margin: 0 !important;
    }
    .sec-page-subtitle {
        font-size: 11.5px !important;
        color: #64748b !important;
        margin: 0 !important;
        line-height: 1.4 !important;
    }

    /* Status Pill Badge */
    .badge-status-live {
        background-color: #ecfdf5 !important;
        color: #059669 !important;
        border: 1px solid #a7f3d0 !important;
        font-size: 10px !important;
        font-weight: 700 !important;
        padding: 3px 9px !important;
        border-radius: 20px !important;
        display: inline-flex;
        align-items: center;
        letter-spacing: 0.01em;
    }

    /* Compact Sleek Button Styling */
    .btn-compact {
        font-size: 11.5px !important;
        padding: 6px 14px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        border-radius: 5px !important;
        transition: all 0.15s ease;
    }

    .btn-primary {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
    }
    .btn-primary:hover {
        background-color: #1d4ed8 !important;
        border-color: #1d4ed8 !important;
        color: #ffffff !important;
    }

    .btn-outline-primary {
        background-color: #ffffff !important;
        border-color: #bfdbfe !important;
        color: #2563eb !important;
    }
    .btn-outline-primary:hover {
        background-color: #eff6ff !important;
        border-color: #2563eb !important;
        color: #1d4ed8 !important;
    }

    .btn-outline-secondary {
        background-color: #ffffff !important;
        border-color: #cbd5e1 !important;
        color: #334155 !important;
    }
    .btn-outline-secondary:hover {
        background-color: #f1f5f9 !important;
        border-color: #94a3b8 !important;
        color: #0f172a !important;
    }

    /* Fixed Action Buttons */
    .sec-action-btn {
        width: 28px !important;
        height: 28px !important;
        min-width: 28px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        border-radius: 5px !important;
        padding: 0 !important;
        font-size: 11px !important;
        transition: all 0.15s ease !important;
        cursor: pointer;
    }

    /* Horizontal Nav Tabs Strip (Responsive Touch) */
    .sec-nav-tabs-wrapper {
        overflow-x: auto;
        white-space: nowrap;
        scrollbar-width: none;
        -webkit-overflow-scrolling: touch;
        border-bottom: 1px solid #e2e8f0;
        margin-bottom: 16px;
    }
    .sec-nav-tabs-wrapper::-webkit-scrollbar { display: none; }
    
    .sec-nav-tabs {
        display: inline-flex;
        gap: 6px;
        padding-bottom: 6px;
        margin-bottom: 0;
        border-bottom: 0;
    }
    .sec-nav-tabs .nav-link {
        font-size: 12px;
        font-weight: 600;
        color: #475569;
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        padding: 7px 14px;
        border-radius: 6px !important;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .sec-nav-tabs .nav-link:hover {
        background-color: #f8fafc;
        border-color: #94a3b8;
        color: #0f172a;
    }
    .sec-nav-tabs .nav-link.active {
        color: #ffffff !important;
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.25);
    }

    /* Form Controls */
    #security-settings-wrapper .form-control,
    #security-settings-wrapper .form-select,
    .sec-settings-modal-scope .form-control,
    .sec-settings-modal-scope .form-select {
        min-height: 34px;
        font-size: 12px;
        font-weight: 500;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        background-color: #ffffff;
        border-radius: 5px !important;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #security-settings-wrapper .form-control:focus,
    #security-settings-wrapper .form-select:focus,
    .sec-settings-modal-scope .form-control:focus,
    .sec-settings-modal-scope .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Toggle Item Box */
    .settings-toggle-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 6px !important;
        padding: 12px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        transition: all 0.15s ease;
    }
    .settings-toggle-card:hover {
        border-color: #cbd5e1;
        background-color: #f8fafc;
    }
    .settings-toggle-card .form-check-input {
        width: 36px;
        height: 18px;
        cursor: pointer;
        border-color: #cbd5e1;
        border-radius: 20px !important;
    }
    .settings-toggle-card .form-check-input:checked {
        background-color: #2563eb;
        border-color: #2563eb;
    }

    /* Unified Data Table */
    .sec-table-container {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .sec-table {
        margin-bottom: 0;
        width: 100%;
        border-collapse: collapse;
    }
    .sec-table thead th {
        font-size: 10.5px !important;
        text-transform: uppercase !important;
        letter-spacing: 0.04em !important;
        font-weight: 700 !important;
        color: #475569 !important;
        background-color: #f8fafc !important;
        border-bottom: 1.5px solid #e2e8f0 !important;
        padding: 10px 12px !important;
        white-space: nowrap !important;
    }
    .sec-table tbody td {
        padding: 10px 12px !important;
        border-bottom: 1px solid #f1f5f9 !important;
        vertical-align: middle !important;
        font-size: 12px !important;
        white-space: nowrap !important;
        color: #1e293b;
    }
    .sec-table tbody tr:hover td {
        background-color: #fafbfc;
    }

    /* Minimal Card Modal System (Screenshot Reference) */
    .minimal-modal-card {
        background: #ffffff !important;
        border: 1px solid #f1f5f9 !important;
        border-radius: 12px !important;
        box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.14), 0 0 1px 1px rgba(15, 23, 42, 0.04) !important;
        padding: 24px 26px !important;
        text-align: left !important;
    }
    .minimal-modal-title {
        font-size: 16px !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        margin-bottom: 6px !important;
        letter-spacing: -0.02em !important;
    }
    .minimal-modal-subtext {
        font-size: 13px !important;
        color: #64748b !important;
        margin-bottom: 22px !important;
        line-height: 1.45 !important;
    }
    .btn-minimal-action {
        background-color: #c93424 !important;
        border: none !important;
        color: #ffffff !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        padding: 7px 22px !important;
        border-radius: 7px !important;
        box-shadow: 0 4px 12px rgba(201, 52, 36, 0.28) !important;
        transition: all 0.15s ease !important;
        cursor: pointer !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    .btn-minimal-action:hover {
        background-color: #b02717 !important;
        box-shadow: 0 6px 14px rgba(201, 52, 36, 0.38) !important;
        color: #ffffff !important;
    }
    .btn-minimal-cancel {
        background: transparent !important;
        border: none !important;
        color: #64748b !important;
        font-weight: 500 !important;
        font-size: 13.5px !important;
        padding: 7px 14px !important;
        box-shadow: none !important;
        transition: color 0.15s ease !important;
        cursor: pointer !important;
    }
    .btn-minimal-cancel:hover {
        color: #0f172a !important;
        background: transparent !important;
    }

    /* Mobile Responsive Optimizations (< 768px) */
    @media (max-width: 767.98px) {
        .header-top-wrap {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 12px !important;
        }
        .header-action-btns-wrap {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        .header-action-btns-wrap .btn {
            width: 100%;
            justify-content: center;
        }
        .settings-toggle-card {
            padding: 10px 12px;
        }
        .sec-table thead th, .sec-table tbody td {
            padding: 8px 10px !important;
        }
        .minimal-modal-card {
            padding: 20px 20px !important;
        }
    }


    /* ================================================================
       KAIADMIN LITE v1.2.0 — CLASSIC / WHITE UI OVERRIDES
       Scoped to this page; backend, Blade bindings and JS are untouched.
       ================================================================ */
    #security-settings-wrapper {
        --sec-primary: var(--bs-primary, #2563eb);
        --sec-primary-soft: #eff6ff;
        --sec-border: #e5e7eb;
        --sec-surface: #ffffff;
        --sec-page: #f7f8fa;
        --sec-heading: #1f2937;
        --sec-muted: #6b7280;
        background: var(--sec-page) !important;
        color: var(--sec-heading);
        min-height: auto;
    }

    #security-settings-wrapper *,
    #security-settings-wrapper *::before,
    #security-settings-wrapper *::after,
    .sec-settings-modal-scope *,
    .sec-settings-modal-scope *::before,
    .sec-settings-modal-scope *::after {
        border-radius: 5px !important;
    }

    #security-settings-wrapper .sec-card {
        background: var(--sec-surface) !important;
        border: 1px solid var(--sec-border) !important;
        box-shadow: 0 1px 2px rgba(16, 24, 40, .035) !important;
        transition: border-color .18s ease, box-shadow .18s ease;
    }
    #security-settings-wrapper .sec-card:hover {
        border-color: #d5dbe5 !important;
        box-shadow: 0 3px 10px rgba(16, 24, 40, .055) !important;
    }

    #security-settings-wrapper .sec-header-icon-box {
        background: var(--sec-primary-soft) !important;
        border: 1px solid #dbeafe !important;
        color: var(--sec-primary) !important;
    }
    #security-settings-wrapper .sec-page-title {
        color: #1f2937 !important;
        font-size: 17px !important;
        letter-spacing: -.025em;
    }
    #security-settings-wrapper .sec-page-subtitle {
        color: var(--sec-muted) !important;
        font-size: 11.5px !important;
    }

    #security-settings-wrapper .sec-nav-tabs-wrapper {
        border-bottom: 1px solid var(--sec-border);
        margin-bottom: 14px;
        padding-bottom: 1px;
    }
    #security-settings-wrapper .sec-nav-tabs {
        gap: 5px;
        padding-bottom: 0;
    }
    #security-settings-wrapper .sec-nav-tabs .nav-link {
        min-height: 35px;
        padding: 7px 12px;
        color: #596579;
        background: #fff;
        border: 1px solid transparent;
        font-size: 11.5px;
        font-weight: 600;
        box-shadow: none;
    }
    #security-settings-wrapper .sec-nav-tabs .nav-link:hover {
        color: var(--sec-primary);
        background: #f8fafc;
        border-color: #e5eaf2;
    }
    #security-settings-wrapper .sec-nav-tabs .nav-link.active {
        color: var(--sec-primary) !important;
        background: var(--sec-primary-soft) !important;
        border-color: #dbeafe !important;
        box-shadow: none !important;
    }

    #security-settings-wrapper .card-title-icon {
        border-width: 1px !important;
    }
    #security-settings-wrapper .settings-toggle-card {
        background: #fff;
        border: 1px solid #e7eaf0;
        padding: 11px 13px;
        gap: 14px;
    }
    #security-settings-wrapper .settings-toggle-card:hover {
        background: #fbfcfe;
        border-color: #d8dee8;
    }
    #security-settings-wrapper .settings-toggle-card h6 {
        color: #273449 !important;
        line-height: 1.45;
    }
    #security-settings-wrapper .settings-toggle-card p {
        line-height: 1.55;
    }
    #security-settings-wrapper .settings-toggle-card .form-check-input {
        width: 34px;
        height: 18px;
        margin-top: 0;
        box-shadow: none;
    }

    #security-settings-wrapper .form-label {
        color: #475467;
        font-size: 11.5px;
        font-weight: 600;
    }
    #security-settings-wrapper .form-control,
    #security-settings-wrapper .form-select,
    .sec-settings-modal-scope .form-control,
    .sec-settings-modal-scope .form-select {
        min-height: 35px;
        border: 1px solid #d8dee8 !important;
        background-color: #fff !important;
        color: #344054 !important;
        font-size: 12px !important;
        box-shadow: none !important;
    }
    #security-settings-wrapper textarea.form-control,
    .sec-settings-modal-scope textarea.form-control {
        min-height: auto;
        line-height: 1.55;
    }
    #security-settings-wrapper .form-control:focus,
    #security-settings-wrapper .form-select:focus,
    .sec-settings-modal-scope .form-control:focus,
    .sec-settings-modal-scope .form-select:focus {
        border-color: var(--sec-primary, #2563eb) !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .10) !important;
    }

    #security-settings-wrapper .btn,
    #security-settings-wrapper .btn-compact,
    .sec-settings-modal-scope .btn {
        min-height: 32px;
        font-weight: 600;
        box-shadow: none;
    }
    #security-settings-wrapper .btn-primary {
        background: var(--sec-primary, #2563eb) !important;
        border-color: var(--sec-primary, #2563eb) !important;
    }
    #security-settings-wrapper .btn-primary:hover {
        filter: brightness(.94);
    }
    #security-settings-wrapper .sec-action-btn {
        min-height: 28px;
    }

    #security-settings-wrapper .sec-table-container {
        border-radius: 0 !important;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f8fafc;
    }
    #security-settings-wrapper .sec-table {
        min-width: 680px;
    }
    #security-settings-wrapper .sec-table thead th {
        background: #f8fafc !important;
        color: #667085 !important;
        border-bottom: 1px solid #e5e7eb !important;
        font-size: 10px !important;
        letter-spacing: .045em !important;
        padding: 10px 11px !important;
    }
    #security-settings-wrapper .sec-table tbody td {
        color: #344054;
        border-bottom: 1px solid #f0f2f5 !important;
        font-size: 11.5px !important;
        padding: 9px 11px !important;
    }
    #security-settings-wrapper .sec-table tbody tr:last-child td {
        border-bottom: 0 !important;
    }

    .sec-settings-modal-scope .modal-content,
    .sec-settings-modal-scope .minimal-modal-card {
        border: 1px solid #e5e7eb !important;
        border-radius: 5px !important;
        box-shadow: 0 12px 32px rgba(16, 24, 40, .14) !important;
    }
    .sec-settings-modal-scope .modal-header,
    .sec-settings-modal-scope .modal-footer {
        background: #f8fafc !important;
        border-color: #eaecf0 !important;
    }
    .sec-settings-modal-scope .minimal-modal-card {
        padding: 22px !important;
    }
    .sec-settings-modal-scope .minimal-modal-title {
        color: #1f2937 !important;
        font-size: 15px !important;
    }
    .sec-settings-modal-scope .minimal-modal-subtext {
        color: #667085 !important;
        font-size: 12px !important;
        line-height: 1.6 !important;
    }
    .sec-settings-modal-scope .btn-minimal-action,
    .sec-settings-modal-scope .btn-minimal-cancel {
        border-radius: 5px !important;
        box-shadow: none !important;
    }

    @media (min-width: 1400px) {
        #security-settings-wrapper > .mx-auto {
            max-width: 1600px;
        }
    }
    @media (max-width: 767.98px) {
        #security-settings-wrapper {
            padding-top: 10px !important;
        }
        #security-settings-wrapper .sec-card.p-3 {
            padding: 12px !important;
        }
        #security-settings-wrapper .header-top-wrap {
            align-items: stretch !important;
        }
        #security-settings-wrapper .header-action-btns-wrap {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            width: 100%;
        }
        #security-settings-wrapper .header-action-btns-wrap .btn {
            min-height: 36px;
            padding-inline: 8px !important;
            font-size: 11px !important;
        }
        #security-settings-wrapper .sec-nav-tabs-wrapper {
            margin-inline: -2px;
        }
        #security-settings-wrapper .sec-nav-tabs .nav-link {
            min-height: 36px;
            padding-inline: 10px;
        }
        #security-settings-wrapper .settings-toggle-card {
            align-items: flex-start;
            padding: 10px;
        }
        #security-settings-wrapper .settings-toggle-card .form-check {
            padding-top: 2px;
        }
        #security-settings-wrapper .sec-table {
            min-width: 680px;
        }
        .sec-settings-modal-scope .modal-dialog {
            width: calc(100% - 24px);
            max-width: 420px;
            margin: 12px auto;
        }
        .sec-settings-modal-scope .minimal-modal-card {
            padding: 18px !important;
        }
    }
    @media (max-width: 380px) {
        #security-settings-wrapper .header-action-btns-wrap {
            grid-template-columns: 1fr;
        }
        #security-settings-wrapper .sec-page-title {
            font-size: 15px !important;
        }
    }

</style>

<div class="container-fluid px-2 px-sm-3 px-md-4 py-3" id="security-settings-wrapper">
    <div class="mx-auto" style="max-width: 1600px;">

        <!-- ================= 1. HEADER & STATUS BAR ================= -->
        <header class="sec-card p-3 mb-3">
            <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 header-top-wrap">
                
                <!-- Left Title & Status Badge -->
                <div class="d-flex align-items-center gap-2.5">
                    <span class="sec-header-icon-box shadow-xs">
                        <i class="fas fa-sliders"></i>
                    </span>
                    <div class="ms-2">
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                            <h5 class="sec-page-title mb-0">Security Rules & Settings</h5>
                        </div>
                        <p class="sec-page-subtitle mb-0 d-none d-sm-block">
                           Configure firewall, order limits, login protection, allowlists & security alerts.
                        </p>
                    </div>
                </div>

                <!-- Right Action Buttons -->
                <div class="header-action-btns-wrap d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-primary btn-compact shadow-xs fw-semibold" data-bs-toggle="modal" data-bs-target="#modalAddAllowlist">
                        <i class="fas fa-plus me-1"></i> Add Allowlist
                    </button>
                    <button type="submit" form="mainSecuritySettingsForm" class="btn btn-primary btn-compact shadow-xs fw-bold px-3" id="btn-save-master-settings">
                        <i class="fas fa-check-circle me-1"></i> Save Changes
                    </button>
                </div>

            </div>
        </header>

        <!-- ================= 2. SETTINGS NAVIGATION TABS STRIP ================= -->
        <div class="sec-nav-tabs-wrapper">
            <ul class="sec-nav-tabs nav" id="securitySettingsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-btn-general" data-bs-toggle="tab" data-bs-target="#tab-general" type="button" role="tab">
                        Main Controls
                    </button>
                </li>
                <li class="nav-item " role="presentation">
                    <button class="nav-link" id="tab-btn-limits" data-bs-toggle="tab" data-bs-target="#tab-limits" type="button" role="tab">
                     Rate Limits
                    </button>
                </li>
                <li class="nav-item " role="presentation">
                    <button class="nav-link" id="tab-btn-allowlist" data-bs-toggle="tab" data-bs-target="#tab-allowlist" type="button" role="tab">
                    Trusted IPs
                    <span class="badge bg-light text-secondary border font-tabular ms-1">{{ $allowlists->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-btn-notifications" data-bs-toggle="tab" data-bs-target="#tab-notifications" type="button" role="tab">
                       Email Alerts
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-btn-proxy" data-bs-toggle="tab" data-bs-target="#tab-proxy" type="button" role="tab">
                    Cloudflare & Proxies
                    </button>
                </li>
            </ul>
        </div>

        <!-- ================= 3. MAIN SETTINGS FORM ================= -->
        <form id="mainSecuritySettingsForm" method="POST" action="javascript:void(0);">
            @csrf
            <div class="tab-content" id="securitySettingsTabContent">

                <!-- TAB 1: MASTER CONTROLS & GENERAL TOGGLES -->
                <div class="tab-pane fade show active" id="tab-general" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-12 col-lg-8">
                            <div class="sec-card p-3 p-md-4">
                                <div class="d-flex align-items-center mb-3">
                                    <span class="card-title-icon icon-soft-blue"><i class="fas fa-power-off"></i></span>
                                    <h6 class="fw-bold text-dark mb-0" style="font-size: 13.5px;">Core Security Settings</h6>
                                </div>
                                
                                <div class="d-flex flex-column gap-2.5">
                                    <!-- Master Active Toggle -->
                                    <div class="settings-toggle-card mt-1">
                                        <div class="pe-2">
                                            <h6 class="fw-bold mb-0.5 text-dark" style="font-size: 12.5px;">Master Security Status</h6>
                                            <p class="text-muted small mb-0" style="font-size: 11px;">Disables all background threat checks and rate limits.</p>
                                        </div>
                                        <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                            <input class="form-check-input ms-0" type="checkbox" name="security_master_active" value="1" {{ ($generalSettings['security_master_active'] ?? '1') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>

                                    <!-- Perimeter Firewall Toggle -->
                                    <div class="settings-toggle-card mt-1">
                                        <div class="pe-2">
                                            <h6 class="fw-bold mb-0.5 text-dark" style="font-size: 12.5px;">IP Firewall Enforcement</h6>
                                            <p class="text-muted small mb-0" style="font-size: 11px;">Blocks blacklisted IPs with an instant 403 before Laravel controllers load.</p>
                                        </div>
                                        <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                            <input class="form-check-input ms-0" type="checkbox" name="security_firewall_active" value="1" {{ ($generalSettings['security_firewall_active'] ?? '1') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>

                                    <!-- Real-Time Visitor Logging -->
                                    <div class="settings-toggle-card mt-1">
                                        <div class="pe-2">
                                            <h6 class="fw-bold mb-0.5 text-dark" style="font-size: 12.5px;">Live Visitor & IP Tracking</h6>
                                            <p class="text-muted small mb-0" style="font-size: 11px;">Logs visitor routes, browser details & country data for security audits.</p>
                                        </div>
                                        <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                            <input class="form-check-input ms-0" type="checkbox" name="security_visitor_tracking_active" value="1" {{ ($generalSettings['security_visitor_tracking_active'] ?? '1') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>

                                    <!-- CIDR Subnet Blocking -->
                                    <div class="settings-toggle-card mt-1">
                                        <div class="pe-2">
                                            <h6 class="fw-bold mb-0.5 text-dark" style="font-size: 12.5px;">CIDR Subnet Blocking</h6>
                                            <p class="text-muted small mb-0" style="font-size: 11px;">Blocks entire IP ranges (e.g., /24, /16) to stop bot networks and coordinated attacks.</p>
                                        </div>
                                        <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                            <input class="form-check-input ms-0" type="checkbox" name="security_cidr_blocking_active" value="1" {{ ($generalSettings['security_cidr_blocking_active'] ?? '1') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>

                                    <!-- Exclude Staff/Admin Traffic Toggle -->
                                    <div class="settings-toggle-card mt-1">
                                        <div class="pe-2">
                                            <h6 class="fw-bold mb-0.5 text-dark" style="font-size: 12.5px;">Exclude Staff & Admin Traffic</h6>
                                            <p class="text-muted small mb-0" style="font-size: 11px;">Keeps internal staff and admin activity out of visitor analytics and security alerts.</p>
                                        </div>
                                        <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                            <input class="form-check-input ms-0" type="checkbox" name="security_exclude_staff_traffic" value="1" {{ ($generalSettings['security_exclude_staff_traffic'] ?? '1') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Guidelines Card -->
                        <div class="col-12 col-lg-4">
                            <div class="sec-card p-3 p-md-4 h-100">
                                <div class="d-flex align-items-center mb-2.5">
                                    <span class="card-title-icon icon-soft-teal"><i class="fas fa-info-circle"></i></span>
                                    <h6 class="fw-bold text-dark mb-0" style="font-size: 13px;">Safety Guidelines</h6>
                                </div>
                                <p class="small text-muted mb-3" style="font-size: 11.5px; line-height: 1.5;">
                                  Security setting changes apply instantly through Redis and Laravel Cache. No server restart is required.
                                </p>
                                <div class="alert alert-light border small text-muted py-2.5 px-3 mb-0" style="font-size: 11px; border-radius: 6px;">
                                    <strong class="text-dark d-block mb-1"><i class="fas fa-shield-alt text-success me-1"></i>Fail-Safe Protection:</strong>
                                   If the database becomes temporarily unavailable, the firewall allows traffic to prevent checkout disruptions.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: VELOCITY & RATE LIMITS -->
                <div class="tab-pane fade" id="tab-limits" role="tabpanel">
                    <div class="row g-3">
                        <!-- Order Velocity Throttling -->
                        <div class="col-12 col-lg-6">
                            <div class="sec-card p-3 p-md-4 h-100">
                                <div class="d-flex align-items-center mb-3">
                                    <span class="card-title-icon icon-soft-amber"><i class="fas fa-cart-shopping"></i></span>
                                    <h6 class="fw-bold text-dark mb-0" style="font-size: 13.5px;">Order Rate Limits</h6>
                                </div>
                                <p class="text-muted small mb-3" style="font-size: 11.5px;">
                                    Limits how frequently an IP can place orders to detect suspicious activity.
                                </p>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-secondary mb-1">Max Orders per 5 Minutes</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control bn-digit font-tabular" name="security_order_limit_5min" min="1" max="50" value="{{ $orderSettings['security_order_limit_5min'] ?? 3 }}" required>
                                        <span class="input-group-text bg-light text-muted">Orders</span>
                                    </div>
                                    <small class="text-muted font-tabular" style="font-size: 10.5px;">Default: 3 orders</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-secondary mb-1">Max Orders per Hour</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control bn-digit font-tabular" name="security_order_limit_1hour" min="1" max="100" value="{{ $orderSettings['security_order_limit_1hour'] ?? 10 }}" required>
                                        <span class="input-group-text bg-light text-muted">Orders</span>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-secondary mb-1">Max Orders per 24 Hours</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control bn-digit font-tabular" name="security_order_limit_24hour" min="1" max="500" value="{{ $orderSettings['security_order_limit_24hour'] ?? 25 }}" required>
                                        <span class="input-group-text bg-light text-muted">Orders</span>
                                    </div>
                                </div>

                                <div class="mb-0">
                                    <label class="form-label small fw-bold text-secondary mb-1">Fraud Risk Score Threshold</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control bn-digit font-tabular" name="security_suspicious_order_threshold" min="10" max="100" value="{{ $orderSettings['security_suspicious_order_threshold'] ?? 40 }}" required>
                                        <span class="input-group-text bg-light text-muted">/100</span>
                                    </div>
                                    <small class="text-muted" style="font-size: 10.5px;">Orders exceeding the configured risk score are automatically flagged for review.</small>
                                </div>
                            </div>
                        </div>

                        <!-- Login Brute-Force & Form Spams -->
                        <div class="col-12 col-lg-6">
                            <div class="sec-card p-3 p-md-4 h-100">
                                <div class="d-flex align-items-center mb-3">
                                    <span class="card-title-icon icon-soft-red"><i class="fas fa-lock"></i></span>
                                    <h6 class="fw-bold text-dark mb-0" style="font-size: 13.5px;">Login & Form Protection</h6>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-secondary mb-1">Max Failed Login Attempts</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control bn-digit font-tabular" name="security_login_max_attempts" min="3" max="20" value="{{ $loginSettings['security_login_max_attempts'] ?? 5 }}" required>
                                        <span class="input-group-text bg-light text-muted">Attempts</span>
                                    </div>
                                    <small class="text-muted" style="font-size: 10.5px;">Allowed incorrect password attempts before lockout.</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-secondary mb-1">Brute-Force Lockout Duration</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control bn-digit font-tabular" name="security_login_lock_duration" min="1" max="1440" value="{{ $loginSettings['security_login_lock_duration'] ?? 15 }}" required>
                                        <span class="input-group-text bg-light text-muted">Minutes</span>
                                    </div>
                                    <small class="text-muted" style="font-size: 10.5px;">How long an IP remains blocked from login attempts.</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-secondary mb-1">Max Form Submissions per Minute</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control bn-digit font-tabular" name="security_form_limit_per_minute" min="1" max="30" value="{{ $formSettings['security_form_limit_per_minute'] ?? 5 }}" required>
                                        <span class="input-group-text bg-light text-muted">Submissions</span>
                                    </div>
                                </div>

                                <div class="settings-toggle-card mt-3">
                                    <div class="pe-2">
                                        <span class="fw-bold text-dark small d-block">Invisible Form Honeypot</span>
                                        <small class="text-muted" style="font-size: 11px;">Detects spam bots by trapping automated submissions through hidden fields.</small>
                                    </div>
                                    <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                        <input class="form-check-input ms-0" type="checkbox" name="security_honeypot_active" value="1" {{ ($formSettings['security_honeypot_active'] ?? '1') == '1' ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: TRUSTED ALLOWLIST -->
                <div class="tab-pane fade" id="tab-allowlist" role="tabpanel">
                    <div class="sec-card p-0 overflow-hidden">
                        <div class="py-2.5 px-3 bg-white border-bottom d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center py-1">
                                <span class="card-title-icon icon-soft-green"><i class="fas fa-shield"></i></span>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 13px;">Trusted IP & Endpoint Whitelist</h6>
                                    <small class="text-muted d-block" style="font-size: 11px;">Allows trusted IPs and endpoints to bypass rate limits and firewall restrictions.</small>
                                </div>
                            </div>
                            <button type="button" class="btn btn-success btn-compact shadow-xs fw-bold px-3" data-bs-toggle="modal" data-bs-target="#modalAddAllowlist" style="background-color: #16a34a !important; border-color: #16a34a !important;">
                                <i class="fas fa-plus me-1"></i> Add Entry
                            </button>
                        </div>

                        <div class="sec-table-container">
                            <table class="table sec-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-3" style="width: 100px;">Type</th>
                                        <th style="min-width: 160px;">Target</th>
                                        <th>Label Identifier</th>
                                        <th style="min-width: 140px;">Exemption Scope</th>
                                        <th>Created by</th>
                                        <th class="text-center" style="width: 80px;">Status</th>
                                        <th class="pe-3 text-end" style="width: 70px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="allowlist-table-body">
                                    @forelse($allowlists as $item)
                                        <tr id="allowlist-row-{{ $item->id }}">
                                            <td class="ps-3">
                                                <span class="badge bg-light text-secondary border px-2 py-0.5 text-uppercase font-tabular" style="font-size: 9px; font-weight: 700;">
                                                    {{ $item->type }}
                                                </span>
                                            </td>
                                            <td class="fw-bold text-dark bn-digit font-tabular" style="font-size: 12.5px;">{{ $item->value }}</td>
                                            <td class="fw-medium text-dark">{{ $item->label }}</td>
                                            <td>
                                                <span class="badge {{ $item->exemption_scope === 'all' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-primary-subtle text-primary border border-primary-subtle' }} px-2 py-0.5" style="font-size: 9.5px; font-weight: 600;">
                                                    {{ str_replace('_', ' ', ucfirst($item->exemption_scope)) }}
                                                </span>
                                            </td>
                                            <td class="text-muted small">{{ $item->creator ? $item->creator->name : 'System' }}</td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-inline-block m-0">
                                                    <input class="form-check-input btn-toggle-allowlist" type="checkbox" data-id="{{ $item->id }}" {{ $item->is_active ? 'checked' : '' }} style="cursor: pointer;">
                                                </div>
                                            </td>
                                            <td class="pe-3 text-end">
                                                <button type="button" class="btn btn-outline-danger sec-action-btn shadow-xs btn-delete-allowlist" data-id="{{ $item->id }}" title="Remove from Allowlist">
                                                    <i class="fas fa-trash-can"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5 text-muted small">
                                                <i class="fas fa-shield-halved fa-2x mb-2 text-secondary opacity-25 d-block"></i>
                                               No whitelist entries yet. Add your office IP or trusted payment gateway IP.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- TAB 4: EMAIL NOTIFICATIONS & ALERTS -->
                <div class="tab-pane fade" id="tab-notifications" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-12 col-lg-7">
                            <div class="sec-card p-3 p-md-4">
                                <div class="d-flex align-items-center mb-3">
                                    <span class="card-title-icon icon-soft-red"><i class="fas fa-envelope-open-text"></i></span>
                                    <h6 class="fw-bold text-dark mb-0" style="font-size: 13.5px;">Security Alerts & Notifications</h6>
                                </div>

                                <div class="settings-toggle-card mb-3">
                                    <div class="pe-2">
                                        <h6 class="fw-bold mb-0.5 text-dark" style="font-size: 12.5px;">Automated Email Alerts</h6>
                                        <p class="text-muted small mb-0" style="font-size: 11px;">Sends instant alerts for critical threats and coordinated brute-force attacks.</p>
                                    </div>
                                    <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                        <input class="form-check-input ms-0" type="checkbox" name="security_email_notifications" value="1" {{ ($notificationSettings['security_email_notifications'] ?? '0') == '1' ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-secondary mb-1">- Security Alert Recipient</label>
                                    <input type="email" class="form-control" name="security_alert_notification_email" value="{{ $notificationSettings['security_alert_notification_email'] ?? '' }}" placeholder="security@yourdomain.com">
                                    <small class="text-muted" style="font-size: 10.5px;">Leave blank to use the site admin email.</small>
                                </div>

                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label small fw-bold text-secondary mb-1">Minimum Alert Severity</label>
                                        <select class="form-select" name="security_email_min_risk_level">
                                            <option value="critical" {{ ($notificationSettings['security_email_min_risk_level'] ?? 'high') === 'critical' ? 'selected' : '' }}>Critical Only (Score &ge; 75)</option>
                                            <option value="high" {{ ($notificationSettings['security_email_min_risk_level'] ?? 'high') === 'high' ? 'selected' : '' }}>High &amp; Critical (Score &ge; 50)</option>
                                            <option value="medium" {{ ($notificationSettings['security_email_min_risk_level'] ?? 'high') === 'medium' ? 'selected' : '' }}>Medium and Above (Score &ge; 35)</option>
                                            <option value="low" {{ ($notificationSettings['security_email_min_risk_level'] ?? 'high') === 'low' ? 'selected' : '' }}>All Logged Events (Verbose)</option>
                                        </select>
                                    </div>

                                    <div class="col-12 col-sm-6">
                                        <label class="form-label small fw-bold text-secondary mb-1">Email Cooldown Period</label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" class="form-control bn-digit font-tabular" name="security_email_cooldown_minutes" min="5" max="1440" value="{{ $notificationSettings['security_email_cooldown_minutes'] ?? 30 }}" required>
                                            <span class="input-group-text bg-light text-muted">Minutes</span>
                                        </div>
                                        <small class="text-muted" style="font-size: 10.5px;">Prevents repeated alerts by adding a delay between emails.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-5">
                            <div class="sec-card p-3 p-md-4 h-100">
                                <div class="d-flex align-items-center mb-2.5">
                                    <span class="card-title-icon icon-soft-blue"><i class="fas fa-shield-cat"></i></span>
                                    <h6 class="fw-bold text-dark mb-0" style="font-size: 13px;">Email Anti-Flood Protection</h6>
                                </div>
                                <p class="small text-muted mb-2" style="font-size: 11.5px; line-height: 1.5;">
                                  During DDoS attacks or credential-stuffing attempts, repeated security events can generate hundreds of alerts.
                                </p>
                                <p class="small text-muted mb-0" style="font-size: 11.5px; line-height: 1.5;">
                                    The <strong>Email Cooldown Period</strong> intelligently groups alerts by source IP and limits repeated notifications, keeping your inbox manageable without missing critical threats.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 5: CLOUDFLARE & TRUSTED PROXY -->
                <div class="tab-pane fade" id="tab-proxy" role="tabpanel">
                    <div class="sec-card p-3 p-md-4">
                        <div class="d-flex align-items-center mb-3">
                            <span class="card-title-icon icon-soft-teal"><i class="fas fa-cloud"></i></span>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 13.5px;">Cloudflare & Reverse Proxy Settings</h6>
                        </div>

                        <div class="settings-toggle-card mb-3">
                            <div class="pe-2">
                                <h6 class="fw-bold mb-0.5 text-dark" style="font-size: 12.5px;">Real Client IP Detection (Cloudflare Headers)</h6>
                                <p class="text-muted small mb-0" style="font-size: 11px;">Detects the visitor's real IP using trusted proxy headers such as CF-Connecting-IP and X-Forwarded-For.</p>
                            </div>
                            <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                <input class="form-check-input ms-0" type="checkbox" name="security_cloudflare_support" value="1" {{ ($proxySettings['security_cloudflare_support'] ?? '1') == '1' ? 'checked' : '' }}>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-bold text-secondary mb-1">Trusted Proxy IPs / CIDRs (Comma-Separated)</label>
                            <textarea class="form-control font-monospace" name="security_trusted_proxies" rows="3" placeholder="127.0.0.1, 10.0.0.0/8, 172.16.0.0/12, 192.168.0.0/16" style="font-size: 11.5px;">{{ $proxySettings['security_trusted_proxies'] ?? '' }}</textarea>
                            <small class="text-muted" style="font-size: 10.5px;">Enter trusted load balancer or reverse proxy IP ranges. Default Cloudflare settings apply when no custom entries are provided.</small>
                        </div>
                    </div>
                </div>

            </div>
        </form>

    </div>
</div>

<!-- ================= 4. MODAL: ADD TRUSTED ALLOWLIST ENTRY ================= -->
<div class="modal fade sec-settings-modal-scope" id="modalAddAllowlist" tabindex="-1" aria-hidden="true" style="z-index: 108000;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 8px !important;">
            <div class="modal-header py-2.5 px-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold text-success d-flex align-items-center gap-1.5 mb-0" style="font-size: 13px;">
                    <i class="fas fa-shield-check"></i> Add Trusted Allowlist Entry
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formSubmitAllowlist" method="POST" action="javascript:void(0);">
                @csrf
                <div class="modal-body p-3">
                    <div class="row g-2 mb-2.5">
                        <div class="col-5">
                            <label class="form-label small fw-bold text-dark mb-1">Entry Type</label>
                            <select class="form-select form-select-sm" id="allowlist_type" name="type" required>
                                <option value="ip">Single IP</option>
                                <option value="cidr">CIDR (/24)</option>
                                <option value="endpoint">Route Path</option>
                            </select>
                        </div>
                        <div class="col-7">
                            <label class="form-label small fw-bold text-dark mb-1">Target Value <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm bn-digit font-tabular" id="allowlist_value" name="value" required placeholder="103.204.120.15">
                        </div>
                    </div>

                    <div class="mb-2.5">
                        <label class="form-label small fw-bold text-dark mb-1">Label Identifier <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" name="label" required placeholder="Head Office Static IP / POS Terminal">
                    </div>

                    <div class="mb-2.5">
                        <label class="form-label small fw-bold text-dark mb-1">Exemption Scope</label>
                        <select class="form-select form-select-sm" name="exemption_scope" required>
                            <option value="all" selected>All (Complete Bypass: Firewall &amp; Rate Limits)</option>
                            <option value="rate_limit_only">Rate Limits Only (Permit High Volume)</option>
                            <option value="firewall_only">Firewall Only (Bypass Blacklists)</option>
                        </select>
                    </div>

                    <div class="mb-1">
                        <label class="form-label small fw-bold text-dark mb-1">Internal Note (Optional)</label>
                        <textarea class="form-control form-control-sm" name="note" rows="2" placeholder="Audit note..."></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 bg-light border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-compact shadow-xs fw-bold px-3 text-white" id="btn-submit-allowlist" style="background-color: #16a34a !important; border-color: #16a34a !important;">
                        Save Entry
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= 5. MINIMAL CONFIRMATION MODAL (Screenshot Reference) ================= -->
<div class="modal fade sec-settings-modal-scope" id="settingsConfirmMinimalModal" tabindex="-1" aria-hidden="true" style="z-index: 108500;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content minimal-modal-card">
            <h6 class="minimal-modal-title" id="settingsConfirmModalTitle">Are you sure?</h6>
            <p class="minimal-modal-subtext" id="settingsConfirmModalMessage">
                This item will be removed from your trusted allowlist.
            </p>
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn-minimal-action" id="btnExecuteSettingsConfirm">Delete</button>
                <button type="button" class="btn-minimal-cancel" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- ================= 6. MINIMAL NOTICE MODAL ================= -->
<div class="modal fade sec-settings-modal-scope" id="settingsNoticeMinimalModal" tabindex="-1" aria-hidden="true" style="z-index: 108600;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
        <div class="modal-content minimal-modal-card">
            <h6 class="minimal-modal-title" id="settingsNoticeModalTitle">Notification</h6>
            <p class="minimal-modal-subtext" id="settingsNoticeModalMessage" style="margin-bottom: 16px !important;"></p>
            <div class="d-flex align-items-center">
                <button type="button" class="btn-minimal-action" style="background-color: #2563eb !important; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.28) !important;" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = '{{ csrf_token() }}';
    let pendingConfirmCallback = null;

    // Minimal Alert Function (Replaces native browser alert)
    function showAppNotice(msg, title = 'Notification') {
        const titleEl = document.getElementById('settingsNoticeModalTitle');
        const msgEl   = document.getElementById('settingsNoticeModalMessage');
        if (titleEl) titleEl.innerText = title;
        if (msgEl) msgEl.innerText = msg;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('settingsNoticeMinimalModal')).show();
    }

    // Minimal Confirm Function (Replaces native browser confirm)
    function showAppConfirm(title, msg, onConfirm, btnText = 'Delete', btnColor = '#c93424') {
        const titleEl = document.getElementById('settingsConfirmModalTitle');
        const msgEl   = document.getElementById('settingsConfirmModalMessage');
        const btn     = document.getElementById('btnExecuteSettingsConfirm');

        if (titleEl) titleEl.innerText = title;
        if (msgEl) msgEl.innerHTML = msg;
        if (btn) {
            btn.innerText = btnText;
            btn.style.backgroundColor = btnColor;
        }

        pendingConfirmCallback = onConfirm;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('settingsConfirmMinimalModal')).show();
    }

    document.getElementById('btnExecuteSettingsConfirm')?.addEventListener('click', function() {
        bootstrap.Modal.getInstance(document.getElementById('settingsConfirmMinimalModal'))?.hide();
        if (typeof pendingConfirmCallback === 'function') {
            pendingConfirmCallback();
            pendingConfirmCallback = null;
        }
    });

    // 1. URL Query Parameter Auto-Tab Switcher
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');

    if (tabParam) {
        let targetTabBtn = null;
        if (tabParam === 'ratelimit' || tabParam === 'rules') {
            targetTabBtn = document.getElementById('tab-btn-limits');
        } else if (tabParam === 'notifications') {
            targetTabBtn = document.getElementById('tab-btn-notifications');
        } else if (tabParam === 'allowlist') {
            targetTabBtn = document.getElementById('tab-btn-allowlist');
        } else if (tabParam === 'proxy') {
            targetTabBtn = document.getElementById('tab-btn-proxy');
        }

        if (targetTabBtn && typeof bootstrap !== 'undefined') {
            const tabInstance = new bootstrap.Tab(targetTabBtn);
            tabInstance.show();
        }
    }

    // 2. Master Settings Form Submit (AJAX)
    const settingsForm = document.getElementById('mainSecuritySettingsForm');
    if (settingsForm) {
        settingsForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-save-master-settings');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';

            const formData = new FormData(settingsForm);

            fetch(`{{ route('admin.security.settings.update') }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async (r) => {
                const data = await r.json();
                if (!r.ok) {
                    throw new Error(data.message || 'Validation error');
                }
                return data;
            })
            .then(res => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Save Changes';
                showAppNotice(res.message, 'Settings Saved');
                if (res.success) {
                    setTimeout(() => location.reload(), 900);
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Save Changes';
                showAppNotice(err.message || 'Failed to update security settings.', 'Error');
            });
        });
    }

    // 3. Create Allowlist Entry (AJAX)
    const allowlistForm = document.getElementById('formSubmitAllowlist');
    if (allowlistForm) {
        allowlistForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-submit-allowlist');
            btn.disabled = true;
            btn.innerText = 'Saving...';

            const formData = new FormData(allowlistForm);

            fetch(`{{ route('admin.security.settings.allowlist.store') }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async (r) => {
                const data = await r.json();
                if (!r.ok) {
                    throw new Error(data.message || 'Validation error');
                }
                return data;
            })
            .then(res => {
                btn.disabled = false;
                btn.innerText = 'Save Entry';
                
                const modalEl = document.getElementById('modalAddAllowlist');
                if (modalEl && typeof bootstrap !== 'undefined') {
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                }
                showAppNotice(res.message, 'Allowlist Added');
                if (res.success) {
                    setTimeout(() => location.reload(), 900);
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerText = 'Save Entry';
                showAppNotice(err.message || 'Failed to add allowlist entry.', 'Error');
            });
        });
    }

    // 4. Toggle Allowlist State
    document.querySelectorAll('.btn-toggle-allowlist').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const id = this.getAttribute('data-id');
            fetch(`{{ url('/admin/security/settings/allowlist/toggle') }}/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(res => {
                if (!res.success) {
                    showAppNotice('Failed to update status.', 'Error');
                    this.checked = !this.checked;
                }
            })
            .catch(() => {
                showAppNotice('Connection error while updating status.', 'Error');
                this.checked = !this.checked;
            });
        });
    });

    // 5. Delete Allowlist Entry (Minimal Modal Integration)
    document.querySelectorAll('.btn-delete-allowlist').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            showAppConfirm(
                'Remove from Allowlist?',
                'Are you sure you want to permanently remove this entry from the trusted allowlist?',
                function() {
                    fetch(`{{ url('/admin/security/settings/allowlist') }}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(res => {
                        showAppNotice(res.message, 'Deleted');
                        if (res.success) {
                            const row = document.getElementById(`allowlist-row-${id}`);
                            if (row) {
                                row.style.transition = 'all 0.25s ease';
                                row.style.opacity = '0';
                                setTimeout(() => row.remove(), 250);
                            }
                        }
                    })
                    .catch(() => {
                        showAppNotice('Failed to delete allowlist entry.', 'Error');
                    });
                },
                'Delete',
                '#c93424'
            );
        });
    });
});
</script>
@endsection