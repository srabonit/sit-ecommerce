
@extends('layouts.admin')

@section('title', 'Create Automation Workflow - Email Marketing')

@section('admin_content')
<style>
    /* =========================================================
       EMAIL AUTOMATION — KAIADMIN CLASSIC WHITE DESIGN
       Scoped CSS | Bootstrap 5 Compatible
    ========================================================= */

    #email-marketing-automation-create-root {
        --em-primary: var(--bs-primary, #1572e8);
        --em-secondary: var(--bs-secondary, #6c757d);
        --em-success: var(--bs-success, #31ce36);
        --em-warning: var(--bs-warning, #ffad46);
        --em-danger: var(--bs-danger, #f25961);
        --em-info: var(--bs-info, #48abf7);

        --em-body: var(--bs-body-bg, #f8f9fa);
        --em-card: #ffffff;
        --em-heading: var(--bs-heading-color, #2a2f5b);
        --em-text: var(--bs-body-color, #495057);
        --em-muted: #8d9498;
        --em-border: #ebecec;
        --em-soft: #f7f8fa;

        color: var(--em-text);
        font-size: 13px;
        min-width: 0;
        width: 100%;
    }

    /* ---------- Global Element Styling ---------- */

    #email-marketing-automation-create-root *,
    .workflow-modal-scope * {
        box-sizing: border-box;
    }

    #email-marketing-automation-create-root .btn,
    #email-marketing-automation-create-root .badge,
    #email-marketing-automation-create-root .alert,
    #email-marketing-automation-create-root .form-control,
    #email-marketing-automation-create-root .form-select,
    #email-marketing-automation-create-root .input-group-text,
    #email-marketing-automation-create-root .card,
    #email-marketing-automation-create-root .step-card,
    #email-marketing-automation-create-root .delay-chip,
    #email-marketing-automation-create-root .collapse-toggle-btn,
    .workflow-modal-scope .modal-content,
    .workflow-modal-scope .btn {
        border-radius: 5px !important;
    }

    #email-marketing-automation-create-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    #email-marketing-automation-create-root .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    #email-marketing-automation-create-root .font-monospace,
    #email-marketing-automation-create-root .bn-digit {
        font-weight: 600;
    }

    /* ---------- Page Header ---------- */

    #email-marketing-automation-create-root .workflow-create-header {
        padding: 0 0 16px;
        border-bottom: 1px solid var(--em-border);
    }

    #email-marketing-automation-create-root .workflow-create-header h5 {
        color: var(--em-heading) !important;
        font-size: clamp(17px, 1.5vw, 21px) !important;
        font-weight: 700;
        letter-spacing: -0.3px;
    }

    #email-marketing-automation-create-root .workflow-create-header > div:first-child > small {
        color: var(--em-muted) !important;
        font-size: 12px !important;
        line-height: 1.5 !important;
    }

    #email-marketing-automation-create-root .header-actions-wrap {
        flex-shrink: 0;
    }

    /* ---------- Buttons ---------- */

    #email-marketing-automation-create-root .btn-compact,
    .workflow-modal-scope .btn-compact {
        min-height: 33px;
        padding: 7px 13px !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        line-height: 1.3 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 3px;
        white-space: nowrap;
        transition: all .18s ease;
    }

    #email-marketing-automation-create-root .btn {
        font-weight: 600;
        transition: all .18s ease;
    }

    #email-marketing-automation-create-root .btn-primary {
        background-color: var(--em-primary);
        border-color: var(--em-primary);
    }

    #email-marketing-automation-create-root .btn-primary:hover {
        filter: brightness(.93);
        box-shadow: 0 3px 8px rgba(21, 114, 232, .15);
    }

    #email-marketing-automation-create-root .btn-outline-primary {
        color: var(--em-primary);
        border-color: var(--em-primary);
        background: #fff;
    }

    #email-marketing-automation-create-root .btn-outline-primary:hover {
        background: var(--em-primary);
        color: #fff;
    }

    #email-marketing-automation-create-root .btn-light {
        background: #f8f9fa;
        border-color: var(--em-border);
    }

    #email-marketing-automation-create-root .btn-light:hover {
        background: #eef0f2;
    }

    #email-marketing-automation-create-root .btn:focus-visible,
    #email-marketing-automation-create-root .delay-chip:focus-visible {
        outline: 2px solid var(--em-primary);
        outline-offset: 2px;
    }

    /* ---------- Cards ---------- */

    #email-marketing-automation-create-root .card {
        background: var(--em-card);
        border: 1px solid var(--em-border) !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .035) !important;
        margin-bottom: 16px;
        overflow: hidden;
    }

    #email-marketing-automation-create-root .card:last-child {
        margin-bottom: 0;
    }

    #email-marketing-automation-create-root .card-body {
        min-width: 0;
    }

    /* ---------- Section Headers ---------- */

    #email-marketing-automation-create-root .section-header-wrap {
        min-height: 47px;
        padding: 10px 15px;
        background: #fff;
        border-bottom: 1px solid var(--em-border);
        gap: 10px;
    }

    #email-marketing-automation-create-root .section-header-wrap > span,
    #email-marketing-automation-create-root .section-header-wrap > div > span {
        color: var(--em-heading);
        font-size: 13px !important;
        font-weight: 700;
        line-height: 1.4;
    }

    #email-marketing-automation-create-root .section-header-wrap i {
        vertical-align: middle;
    }

    /* ---------- Collapse Toggle ---------- */

    #email-marketing-automation-create-root .collapse-toggle-btn {
        width: 27px;
        height: 27px;
        min-width: 27px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid var(--em-border);
        color: #6c757d;
        cursor: pointer;
        transition: all .2s ease;
    }

    #email-marketing-automation-create-root .collapse-toggle-btn:hover {
        background: var(--em-primary);
        border-color: var(--em-primary);
        color: #fff;
    }

    #email-marketing-automation-create-root .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    #email-marketing-automation-create-root .collapse-icon {
        font-size: 10px;
        transition: transform .25s ease;
    }

    /* ---------- Form Controls ---------- */

    #email-marketing-automation-create-root .form-label {
        display: block;
        margin-bottom: 6px;
        color: #495057;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.4;
    }

    #email-marketing-automation-create-root .form-control,
    #email-marketing-automation-create-root .form-select {
        width: 100%;
        min-height: 38px;
        padding: 7px 11px;
        border: 1px solid #e0e3e6;
        background-color: #fff;
        color: var(--em-text);
        font-size: 12.5px;
        font-weight: 400;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #email-marketing-automation-create-root textarea.form-control {
        min-height: 75px;
        resize: vertical;
        line-height: 1.5;
    }

    #email-marketing-automation-create-root .form-control::placeholder {
        color: #adb5bd;
        opacity: 1;
    }

    #email-marketing-automation-create-root .form-control:focus,
    #email-marketing-automation-create-root .form-select:focus {
        border-color: var(--em-primary);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .10);
        outline: none;
    }

    #email-marketing-automation-create-root .input-group-text {
        padding: 7px 12px;
        border-color: #e0e3e6;
        background: #f8f9fa;
        color: #6c757d;
        font-size: 11px;
    }

    #email-marketing-automation-create-root .form-control.is-invalid,
    #email-marketing-automation-create-root .form-select.is-invalid {
        border-color: var(--em-danger);
    }

    /* ---------- General Information ---------- */

    #email-marketing-automation-create-root #collapseGeneralInfo > .p-3 {
        padding: 18px !important;
    }

    #email-marketing-automation-create-root .row {
        min-width: 0;
    }

    /* ---------- Step Builder ---------- */

    #email-marketing-automation-create-root #collapseStepsBuilder {
        background: #fafbfc;
    }

    #email-marketing-automation-create-root #collapseStepsBuilder > .p-3 {
        padding: 16px !important;
        background: #fafbfc !important;
    }

    #email-marketing-automation-create-root #stepsContainer {
        min-width: 0;
    }

    #email-marketing-automation-create-root .step-card {
        background: #fff;
        border: 1px solid #e5e7eb !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .035);
        transition: border-color .18s ease, box-shadow .18s ease;
        overflow: hidden;
    }

    #email-marketing-automation-create-root .step-card:hover {
        border-color: #cbd5e1 !important;
        box-shadow: 0 3px 10px rgba(0, 0, 0, .045);
    }

    #email-marketing-automation-create-root .step-card .card-header {
        min-height: 43px;
        padding: 8px 12px !important;
        background: #fff !important;
        border-bottom: 1px solid var(--em-border) !important;
        gap: 8px;
    }

    #email-marketing-automation-create-root .step-card .card-body {
        padding: 15px !important;
        background: #fff !important;
    }

    #email-marketing-automation-create-root .step-card .step-number {
        font-weight: 700;
    }

    #email-marketing-automation-create-root .step-card .card-header strong {
        color: var(--em-heading);
        font-weight: 600;
    }

    #email-marketing-automation-create-root .step-card .btn {
        min-width: 28px;
        min-height: 28px;
    }

    /* ---------- Delay Preset Chips ---------- */

    #email-marketing-automation-create-root .delay-chip {
        display: inline-flex;
        min-width: 37px;
        min-height: 27px;
        align-items: center;
        justify-content: center;
        padding: 4px 9px;
        border: 1px solid #e5e7eb;
        background: #f8f9fa;
        color: #59636e;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        user-select: none;
        transition: all .15s ease;
    }

    #email-marketing-automation-create-root .delay-chip:hover,
    #email-marketing-automation-create-root .delay-chip.active-chip {
        background: var(--em-primary);
        border-color: var(--em-primary);
        color: #fff;
    }

    /* ---------- Step Counter ---------- */

    #email-marketing-automation-create-root #stepCounterBadge {
        font-size: 10px !important;
        font-weight: 600;
        background: #f1f4f9 !important;
        border-color: #e5e7eb !important;
        color: #6c757d !important;
        padding: 4px 8px !important;
    }

    /* ---------- Right Action Panel ---------- */

    #email-marketing-automation-create-root .card > .p-3:has(> button[type="submit"]) {
        padding: 16px !important;
    }

    #email-marketing-automation-create-root .card button[type="submit"] {
        min-height: 40px;
        font-size: 12px !important;
    }

    #email-marketing-automation-create-root .card a.btn {
        min-height: 36px;
    }

    /* ---------- Timing Reference ---------- */

    #email-marketing-automation-create-root #collapseTimingGuide .p-3 {
        padding: 16px !important;
        font-size: 12px !important;
        line-height: 1.6 !important;
    }

    #email-marketing-automation-create-root #collapseTimingGuide ul {
        padding-left: 19px !important;
    }

    #email-marketing-automation-create-root #collapseTimingGuide li {
        padding: 2px 0;
        line-height: 1.55;
    }

    #email-marketing-automation-create-root #collapseTimingGuide code {
        padding: 2px 5px;
        border: 1px solid #e9ecef;
        border-radius: 3px;
        background: #f8f9fa;
        color: #495057;
        font-size: 11px;
    }

    #email-marketing-automation-create-root #collapseTimingGuide .alert {
        font-size: 11px !important;
        line-height: 1.5;
        border: 1px solid rgba(72, 171, 247, .15) !important;
        background: #eef7ff;
        color: #495057;
    }

    /* ---------- Lifecycle Guidance ---------- */

    #email-marketing-automation-create-root .card.p-3 {
        padding: 16px !important;
    }

    #email-marketing-automation-create-root .card.p-3 h6 {
        color: var(--em-heading) !important;
        font-size: 13px !important;
        margin-bottom: 9px !important;
    }

    #email-marketing-automation-create-root .card.p-3 p {
        font-size: 12px !important;
        line-height: 1.6 !important;
        color: #737b84 !important;
    }

    /* ---------- Validation Alerts ---------- */

    #email-marketing-automation-create-root .alert {
        border: 1px solid rgba(242, 89, 97, .18) !important;
        background: #fff5f5;
        box-shadow: none !important;
        color: #495057;
        font-size: 12px;
        line-height: 1.6;
    }

    #email-marketing-automation-create-root .alert strong {
        font-size: 12px;
    }

    #email-marketing-automation-create-root .alert ul {
        margin-top: 5px;
    }

    /* ---------- Modal ---------- */

    .workflow-modal-scope .modal-dialog {
        width: calc(100% - 24px);
        max-width: 360px;
        margin: 12px auto;
    }

    .workflow-modal-scope .modal-content {
        border: 1px solid var(--em-border, #ebecec) !important;
        background: #fff;
        box-shadow: 0 12px 35px rgba(0, 0, 0, .13) !important;
    }

    .workflow-modal-scope .modal-body {
        padding: 10px !important;
    }

    .workflow-modal-scope h6 {
        color: #2a2f5b;
        font-size: 15px !important;
    }

    .workflow-modal-scope p {
        font-size: 12px !important;
        line-height: 1.55 !important;
    }

    .workflow-modal-scope .btn-dark {
        min-height: 35px;
        font-size: 12px !important;
    }

    /* ---------- Utility ---------- */

    #email-marketing-automation-create-root .text-muted {
        color: #8d9498 !important;
    }

    #email-marketing-automation-create-root .shadow-xs {
        box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
    }

    #email-marketing-automation-create-root .no-print {
        flex-shrink: 0;
    }

    #email-marketing-automation-create-root .badge {
        font-weight: 600;
    }

    /* ---------- Tablet ---------- */

    @media (max-width: 991.98px) {
        #email-marketing-automation-create-root {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        #email-marketing-automation-create-root .card {
            margin-bottom: 13px;
        }
    }

    /* ---------- Mobile ---------- */

    @media (max-width: 767.98px) {
        #email-marketing-automation-create-root {
            padding-top: 12px !important;
            font-size: 12px;
        }

        #email-marketing-automation-create-root .workflow-create-header {
            gap: 12px !important;
            padding-bottom: 13px;
        }

        #email-marketing-automation-create-root .workflow-create-header h5 {
            font-size: 17px !important;
        }

        #email-marketing-automation-create-root .header-actions-wrap {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px !important;
        }

        #email-marketing-automation-create-root .header-actions-wrap .btn {
            width: 100%;
            min-height: 38px;
            padding: 7px 5px !important;
            font-size: 11px !important;
            justify-content: center;
        }

        #email-marketing-automation-create-root .card {
            margin-bottom: 12px !important;
        }

        #email-marketing-automation-create-root .section-header-wrap {
            padding: 10px 11px;
            min-height: 44px;
        }

        #email-marketing-automation-create-root .section-header-wrap > span,
        #email-marketing-automation-create-root .section-header-wrap > div > span {
            font-size: 11.5px !important;
        }

        #email-marketing-automation-create-root #collapseGeneralInfo > .p-3,
        #email-marketing-automation-create-root #collapseStepsBuilder > .p-3 {
            padding: 12px !important;
        }

        #email-marketing-automation-create-root .form-label {
            font-size: 12px;
        }

        #email-marketing-automation-create-root .form-control,
        #email-marketing-automation-create-root .form-select {
            min-height: 40px;
            font-size: 13px;
        }

        #email-marketing-automation-create-root .step-card .card-header {
            padding: 8px 9px !important;
        }

        #email-marketing-automation-create-root .step-card .card-body {
            padding: 12px !important;
        }

        #email-marketing-automation-create-root .step-card .card-header strong {
            font-size: 11px !important;
        }

        #email-marketing-automation-create-root .delay-chip {
            min-height: 30px;
            min-width: 40px;
            font-size: 11px;
        }

        #email-marketing-automation-create-root #collapseTimingGuide .p-3 {
            padding: 13px !important;
        }

        #email-marketing-automation-create-root .card.p-3 {
            padding: 13px !important;
        }
    }

    /* ---------- Small Mobile ---------- */

    @media (max-width: 380px) {
        #email-marketing-automation-create-root .workflow-create-header h5 {
            font-size: 15px !important;
        }

        #email-marketing-automation-create-root .header-actions-wrap {
            grid-template-columns: 1fr;
        }

        #email-marketing-automation-create-root .section-header-wrap {
            gap: 5px;
        }

        #email-marketing-automation-create-root .step-card .card-header {
            flex-wrap: wrap;
        }

        #email-marketing-automation-create-root .step-card .card-header > div {
            gap: 5px !important;
        }

        #email-marketing-automation-create-root .step-card .card-header strong {
            font-size: 10px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-automation-create-root">

    {{-- 1. Header & Navigation --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 workflow-create-header">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                <i class="fas fa-diagram-project text-primary"></i>
                <span>Create Automation Workflow</span>
            </h5>
            <small class="text-muted d-block">
                Build automated email journeys triggered by customer events.
            </small>
        </div>

        <div class="d-flex gap-2 header-actions-wrap">
            <a href="{{ route('admin.email_marketing.automations.index') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-arrow-left me-1"></i> <span>Back to Workflows</span>
            </a>
            <button type="button" class="btn btn-sm btn-primary btn-compact shadow-xs fw-bold" onclick="document.getElementById('automationCreateForm').submit()">
                <i class="fas fa-save me-1"></i> <span>Save Workflow</span>
            </button>
        </div>
    </div>

    {{-- 2. Validation Error Alerts --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 small mb-3" role="alert">
            <div class="d-flex align-items-center gap-1 mb-1">
                <i class="fas fa-triangle-exclamation text-danger fs-5"></i>
                <strong class="text-dark">Please resolve the following errors:</strong>
            </div>
            <ul class="mb-0 ps-3 text-dark">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('admin.email_marketing.automations.store') }}" method="POST" id="automationCreateForm">
        @csrf

        <div class="row g-3">

            {{-- Left Column: Workflow Configuration --}}
            <div class="col-12 col-xl-8">

                {{-- 1. General Workflow Information --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
                        <span class="fw-bold text-dark small">
                            <i class="fas fa-sitemap text-primary me-1"></i> 1. General Workflow Details
                        </span>
                        <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseGeneralInfo" aria-expanded="true" aria-controls="collapseGeneralInfo" title="Toggle Section">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>

                    <div class="collapse show" id="collapseGeneralInfo">
                        <div class="p-3">
                            <div class="row g-3">

                                <div class="col-12 col-md-7">
                                    <label class="form-label">
                                        Workflow Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="name" class="form-control" placeholder="3-Step Abandoned Cart Recovery Sequence" value="{{ old('name') }}" required>
                                </div>

                                <div class="col-12 col-md-5">
                                    <label class="form-label">
                                        Trigger Event <span class="text-danger">*</span>
                                    </label>
                                    <select name="trigger_event" class="form-select font-monospace" required>
                                        <option value="abandoned_cart" {{ old('trigger_event') === 'abandoned_cart' ? 'selected' : '' }}>🛒 Abandoned Cart Detected</option>
                                        <option value="welcome_subscriber" {{ old('trigger_event') === 'welcome_subscriber' ? 'selected' : '' }}>👋 New Subscriber Signed Up</option>
                                        <option value="first_purchase" {{ old('trigger_event') === 'first_purchase' ? 'selected' : '' }}>🛍️ First Successful Purchase</option>
                                        <option value="order_delivered" {{ old('trigger_event') === 'order_delivered' ? 'selected' : '' }}>📦 Order Delivered (Review Request)</option>
                                        <option value="win_back_inactivity" {{ old('trigger_event') === 'win_back_inactivity' ? 'selected' : '' }}>⏳ Customer Inactivity (Win-Back)</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Internal Description / Strategy (Optional)</label>
                                    <textarea name="description" rows="2" class="form-control shadow-none" placeholder="Describe the automation goal and target customer segment...">{{ old('description') }}</textarea>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. Multi-Step Drip Sequence Builder --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
                        <div class="d-flex align-items-center flex-wrap gap-2">
                            <span class="fw-bold text-dark small">
                                <i class="fas fa-bars-staggered text-success me-1"></i> 2. Automated Step Sequence
                            </span>
                            <span class="badge bg-light text-secondary border font-monospace ms-1 px-2 py-0" id="stepCounterBadge">
                                2 Steps Active
                            </span>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary btn-compact shadow-xs fw-bold" onclick="addAutomationStep()">
                                <i class="fas fa-plus me-1"></i> <span>Add Step</span>
                            </button>
                            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseStepsBuilder" aria-expanded="true" aria-controls="collapseStepsBuilder" title="Toggle Steps">
                                <i class="fas fa-chevron-down collapse-icon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="collapse show" id="collapseStepsBuilder">
                        <div class="p-3 bg-light bg-opacity-50">
                            <div id="stepsContainer" class="d-flex flex-column gap-2">
                                {{-- Step items will be inserted here dynamically --}}
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Right Column: Information & Guidance --}}
            <div class="col-12 col-xl-4">

                {{-- Action Panel Card --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="p-3">
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-xs btn-compact mb-2">
                            <i class="fas fa-save me-1"></i> Activate Automation
                        </button>
                        <a href="{{ route('admin.email_marketing.automations.index') }}" class="btn btn-light border w-100 py-2 btn-compact shadow-xs text-secondary">
                            Cancel
                        </a>
                    </div>
                </div>

                {{-- Recommended Delay Guide --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
                        <span class="fw-bold text-dark small">
                            <i class="fas fa-clock text-warning me-1"></i> Delay Timing Guide
                        </span>
                        <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseTimingGuide" aria-expanded="true" aria-controls="collapseTimingGuide" title="Toggle Guide">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>

                    <div class="collapse show" id="collapseTimingGuide">
                        <div class="p-3 small text-muted">
                            <p class="mb-2">Enter delays in <strong>minutes</strong> from the trigger event:</p>

                            <ul class="ps-3 mb-2 font-monospace bn-digit">
                                <li class="mb-1"><strong>5 minutes:</strong> <code>5</code> (Immediate cart recovery)</li>
                                <li class="mb-1"><strong>10 minutes:</strong> <code>10</code> (Quick reminder)</li>
                                <li class="mb-1"><strong>15 minutes:</strong> <code>15</code> (High-conversion interval)</li>
                                <li class="mb-1"><strong>30 minutes:</strong> <code>30</code> (Standard recovery)</li>
                                <li class="mb-1"><strong>1 hour:</strong> <code>60</code></li>
                                <li class="mb-1"><strong>2 hours:</strong> <code>120</code></li>
                                <li class="mb-1"><strong>1 day (24 hrs):</strong> <code>1440</code></li>
                                <li class="mb-1"><strong>2 days (48 hrs):</strong> <code>2880</code></li>
                            </ul>

                            <div class="alert alert-info py-2 px-2 mb-0">
                                <i class="fas fa-info-circle me-1 text-primary"></i>
                                Delay <code>0</code> sends the email immediately after the trigger event.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Operational Guidance Card --}}
                <div class="card p-3">
                    <h6 class="fw-bold text-dark mb-1 small d-flex align-items-center gap-1">
                        <i class="fas fa-shield-halved text-success"></i>
                        <span>Lifecycle Safety</span>
                    </h6>

                    <p class="text-muted small mb-2">
                        &bull; Customers who complete an order automatically exit abandoned cart sequences to prevent irrelevant follow-ups.
                    </p>

                    <p class="text-muted small mb-0">
                        &bull; Customize each subject line to test different approaches, such as curiosity, urgency and discounts.
                    </p>
                </div>

            </div>
        </div>
    </form>
</div>

<!-- Modal: Notice Dialog Modal -->
<div class="modal fade workflow-modal-scope" id="workflowNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-warning-subtle text-warning-emphasis d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 44px; height: 44px; border-radius: 50% !important;">
                    <i class="fas fa-circle-exclamation fs-4"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="workflowNoticeTitle" style="font-size: 14px;">Attention Required</h6>
                <p class="text-muted small mb-3" id="workflowNoticeMsg" style="font-size: 12px; line-height: 1.45;"></p>
                <button type="button" class="btn btn-dark btn-compact px-4" data-bs-dismiss="modal">Acknowledge</button>
            </div>
        </div>
    </div>
</div>

{{-- Dynamic Steps Javascript --}}
<script>
    const templatesList = @json($templates);
    let stepCount = 0;

    function showNoticeModal(message, title = 'Notice') {
        document.getElementById('workflowNoticeTitle').innerText = title;
        document.getElementById('workflowNoticeMsg').innerText = message;
        const modalEl = document.getElementById('workflowNoticeModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function formatHumanDelay(mins) {
        mins = parseInt(mins) || 0;
        if (mins === 0) return 'Immediate (0 mins)';
        if (mins < 60) return `${mins} mins`;
        if (mins < 1440) {
            const hrs = (mins / 60).toFixed(1).replace('.0', '');
            return `${hrs} hr${hrs > 1 ? 's' : ''}`;
        }
        const days = (mins / 1440).toFixed(1).replace('.0', '');
        return `${days} day${days > 1 ? 's' : ''}`;
    }

    function setStepDelayPreset(stepId, minutes) {
        const input = document.getElementById(`delay-input-${stepId}`);
        const badge = document.getElementById(`delay-badge-${stepId}`);
        if (input) {
            input.value = minutes;
            if (badge) badge.innerText = formatHumanDelay(minutes);
        }
    }

    function handleDelayInputChange(stepId, value) {
        const badge = document.getElementById(`delay-badge-${stepId}`);
        if (badge) {
            badge.innerText = formatHumanDelay(value);
        }
    }

    function addAutomationStep(delay = 10, subject = '', templateId = '') {
        const container = document.getElementById('stepsContainer');
        const index = stepCount;
        stepCount++;

        let templateOptions = '<option value="">-- Select Design Template --</option>';
        templatesList.forEach(tpl => {
            const selected = (templateId == tpl.id) ? 'selected' : '';
            templateOptions += `<option value="${tpl.id}" ${selected}>${tpl.name}</option>`;
        });

        const stepCard = document.createElement('div');
        stepCard.className = 'card step-card mb-0';
        stepCard.id = `step-card-${index}`;

        stepCard.innerHTML = `
            <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-0" style="font-size: 10px;">
                        Step <span class="step-number">${index + 1}</span>
                    </span>
                    <strong class="text-dark small" style="font-size: 12px;">Send Automated Email</strong>
                    <span class="badge bg-light text-muted border font-monospace d-none d-sm-inline-block px-1 py-0" id="delay-badge-${index}" style="font-size: 9.5px;">
                        ${formatHumanDelay(delay)}
                    </span>
                </div>
                <button type="button" class="btn btn-sm btn-light border text-danger p-0 d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 24px; height: 24px; border-radius: 5px !important;" onclick="removeAutomationStep(${index})" title="Remove Step">
                    <i class="fas fa-trash-can" style="font-size: 10px;"></i>
                </button>
            </div>
            <div class="card-body p-3 bg-white">
                <div class="row g-2">
                    <div class="col-12 col-md-5">
                        <label class="form-label d-flex justify-content-between align-items-center">
                            <span>Step Delay <span class="text-danger">*</span></span>
                            <span class="text-muted" style="font-weight: normal; font-size: 10px;">(Exact minutes)</span>
                        </label>
                        <div class="input-group">
                            <input type="number" name="steps[${index}][delay]" id="delay-input-${index}" class="form-control font-monospace bn-digit shadow-none" value="${delay}" min="0" required oninput="handleDelayInputChange(${index}, this.value)">
                            <span class="input-group-text bg-light text-muted font-monospace" style="font-size: 11px;">mins</span>
                        </div>
                        {{-- Quick Preset Delay Chips (Including 5m, 10m, 15m) --}}
                        <div class="d-flex flex-wrap gap-1 mt-1.5">
                            <span class="delay-chip" onclick="setStepDelayPreset(${index}, 5)">5m</span>
                            <span class="delay-chip" onclick="setStepDelayPreset(${index}, 10)">10m</span>
                            <span class="delay-chip" onclick="setStepDelayPreset(${index}, 15)">15m</span>
                            <span class="delay-chip" onclick="setStepDelayPreset(${index}, 30)">30m</span>
                            <span class="delay-chip" onclick="setStepDelayPreset(${index}, 60)">1h</span>
                            <span class="delay-chip" onclick="setStepDelayPreset(${index}, 120)">2h</span>
                            <span class="delay-chip" onclick="setStepDelayPreset(${index}, 1440)">1d</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-7">
                        <label class="form-label">Email Design Template <span class="text-danger">*</span></label>
                        <select name="steps[${index}][template_id]" class="form-select font-monospace shadow-none" required>
                            ${templateOptions}
                        </select>
                    </div>
                    <div class="col-12 mt-2">
                        <label class="form-label">Custom Subject Override (Optional)</label>
                        <input type="text" name="steps[${index}][subject]" class="form-control shadow-none" placeholder="Leave empty to inherit template default subject line" value="${subject}">
                    </div>
                </div>
            </div>
        `;

        container.appendChild(stepCard);
        reindexSteps();
    }

    function removeAutomationStep(index) {
        const card = document.getElementById(`step-card-${index}`);
        if (card) {
            const container = document.getElementById('stepsContainer');
            if (container.querySelectorAll('.step-card').length <= 1) {
                showNoticeModal('A workflow sequence must contain at least 1 step before saving.', 'Step Limit');
                return;
            }
            card.remove();
            reindexSteps();
        }
    }

    function reindexSteps() {
        const cards = document.querySelectorAll('.step-card');
        cards.forEach((card, idx) => {
            const numberEl = card.querySelector('.step-number');
            if (numberEl) numberEl.innerText = idx + 1;
        });

        const badge = document.getElementById('stepCounterBadge');
        if (badge) {
            badge.innerText = `${cards.length} ${cards.length === 1 ? 'Step' : 'Steps'} Active`;
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        // Default Step 1: 10 mins (Urgent reminder)
        // Default Step 2: 1440 mins (24 hours - 10% voucher offer)
        addAutomationStep(10, 'Did you forget items in your cart?');
        addAutomationStep(1440, 'Your exclusive 10% voucher is waiting!');
    });
</script>
@endsection
