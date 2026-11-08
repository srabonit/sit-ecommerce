
@extends('layouts.admin')

@section('title', 'Edit Automation - ' . $automation->name)

@section('admin_content')

<style>
    /* =========================================
       AUTOMATION EDIT - DESIGN SYSTEM
    ========================================= */
    #email-marketing-automation-edit-root {
        --ea-primary: #2563eb;
        --ea-primary-soft: #eff6ff;
        --ea-success: #16a34a;
        --ea-border: #e2e8f0;
        --ea-muted: #64748b;
        --ea-dark: #0f172a;
        --ea-bg: #f8fafc;
        color: var(--ea-dark);
        font-size: 13px;
    }

    /* Strict 5px Radius */
    #email-marketing-automation-edit-root .btn,
    #email-marketing-automation-edit-root .badge,
    #email-marketing-automation-edit-root .alert,
    #email-marketing-automation-edit-root .form-control,
    #email-marketing-automation-edit-root .form-select,
    #email-marketing-automation-edit-root .input-group-text,
    #email-marketing-automation-edit-root .card,
    #email-marketing-automation-edit-root .step-card,
    #email-marketing-automation-edit-root .delay-chip,
    #email-marketing-automation-edit-root .collapse-toggle-btn,
    .workflow-modal-scope .modal-content,
    .workflow-modal-scope .btn {
        border-radius: 5px !important;
    }

    #email-marketing-automation-edit-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    #email-marketing-automation-edit-root .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    #email-marketing-automation-edit-root .font-monospace,
    #email-marketing-automation-edit-root .bn-digit {
        font-weight: 600;
        font-variant-numeric: tabular-nums;
    }

    /* Page Header */
    #email-marketing-automation-edit-root .workflow-page-header {
        padding: 2px 0 15px;
        border-bottom: 1px solid var(--ea-border);
    }

    #email-marketing-automation-edit-root .page-title {
        font-size: 18px;
        font-weight: 750;
        letter-spacing: -0.4px;
        color: var(--ea-dark);
    }

    #email-marketing-automation-edit-root .page-subtitle {
        font-size: 11.5px;
        color: var(--ea-muted);
        line-height: 1.5;
    }

    /* Compact Buttons */
    #email-marketing-automation-edit-root .btn-compact {
        min-height: 31px;
        padding: 5px 11px !important;
        font-size: 11px !important;
        line-height: 1.3;
        font-weight: 650 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        white-space: nowrap;
        transition: all .18s ease;
    }

    #email-marketing-automation-edit-root .btn-primary {
        background: var(--ea-primary);
        border-color: var(--ea-primary);
    }

    #email-marketing-automation-edit-root .btn-primary:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
        transform: translateY(-1px);
    }

    #email-marketing-automation-edit-root .btn-outline-primary:hover {
        transform: translateY(-1px);
    }

    /* Cards */
    #email-marketing-automation-edit-root .card {
        background: #fff;
        border: 1px solid var(--ea-border) !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .035);
        margin-bottom: 14px;
        overflow: hidden;
    }

    #email-marketing-automation-edit-root .section-header-wrap {
        min-height: 43px;
        padding: 9px 13px;
        background: #fff;
        border-bottom: 1px solid var(--ea-border);
    }

    #email-marketing-automation-edit-root .section-heading {
        font-size: 12px;
        font-weight: 750;
        color: #1e293b;
    }

    #email-marketing-automation-edit-root .section-icon {
        width: 25px;
        height: 25px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
        background: var(--ea-primary-soft);
        color: var(--ea-primary);
        font-size: 11px;
    }

    /* Collapse Button */
    #email-marketing-automation-edit-root .collapse-toggle-btn {
        width: 25px;
        height: 25px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid #cbd5e1;
        color: #64748b;
        transition: all .2s ease;
    }

    #email-marketing-automation-edit-root .collapse-toggle-btn:hover {
        background: var(--ea-primary);
        border-color: var(--ea-primary);
        color: #fff;
    }

    #email-marketing-automation-edit-root .collapse-icon {
        font-size: 10px;
        transition: transform .2s ease;
    }

    #email-marketing-automation-edit-root .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    /* Form Controls */
    #email-marketing-automation-edit-root .form-label {
        display: block;
        margin-bottom: 5px;
        color: #334155;
        font-size: 11px;
        font-weight: 700;
    }

    #email-marketing-automation-edit-root .form-control,
    #email-marketing-automation-edit-root .form-select {
        min-height: 34px;
        padding: 6px 10px;
        border: 1px solid #cbd5e1;
        background-color: #fff;
        color: #0f172a;
        font-size: 12px;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #email-marketing-automation-edit-root textarea.form-control {
        min-height: 65px;
        resize: vertical;
    }

    #email-marketing-automation-edit-root .form-control:focus,
    #email-marketing-automation-edit-root .form-select:focus {
        border-color: var(--ea-primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .10);
    }

    #email-marketing-automation-edit-root .form-control:disabled,
    #email-marketing-automation-edit-root .form-control[readonly] {
        background: #f8fafc;
        color: #475569;
        opacity: 1;
    }

    /* Workflow Step Cards */
    #email-marketing-automation-edit-root .step-card {
        border: 1px solid #dbe3ee !important;
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .035);
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    #email-marketing-automation-edit-root .step-card:hover {
        border-color: #93c5fd !important;
        box-shadow: 0 4px 12px rgba(37, 99, 235, .06);
    }

    #email-marketing-automation-edit-root .step-card-header {
        min-height: 42px;
        padding: 7px 11px;
        background: #fff;
        border-bottom: 1px solid #edf1f6;
    }

    #email-marketing-automation-edit-root .step-number-badge {
        background: var(--ea-primary-soft);
        border: 1px solid #dbeafe;
        color: #1d4ed8;
        padding: 3px 8px;
        font-size: 10px;
        font-weight: 700;
    }

    #email-marketing-automation-edit-root .step-card-body {
        padding: 13px;
        background: #fff;
    }

    #email-marketing-automation-edit-root .step-type-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
    }

    #email-marketing-automation-edit-root .step-delete-btn {
        width: 26px;
        height: 26px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #dc2626;
        background: #fff;
        border: 1px solid #fecaca;
        border-radius: 5px;
        transition: all .15s ease;
    }

    #email-marketing-automation-edit-root .step-delete-btn:hover {
        background: #fef2f2;
        border-color: #ef4444;
    }

    /* Delay Presets */
    #email-marketing-automation-edit-root .delay-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        padding: 3px 8px;
        color: #475569;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        font-size: 10px;
        font-weight: 700;
        cursor: pointer;
        user-select: none;
        transition: all .15s ease;
    }

    #email-marketing-automation-edit-root .delay-chip:hover {
        color: #fff;
        background: var(--ea-primary);
        border-color: var(--ea-primary);
    }

    /* Sidebar Information */
    #email-marketing-automation-edit-root .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        padding: 8px 0;
        border-bottom: 1px solid #edf1f5;
        font-size: 11px;
    }

    #email-marketing-automation-edit-root .info-row:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }

    #email-marketing-automation-edit-root .info-label {
        color: #64748b;
    }

    #email-marketing-automation-edit-root .info-value {
        color: #1e293b;
        font-weight: 650;
        text-align: right;
    }

    #email-marketing-automation-edit-root .guide-list {
        padding-left: 17px;
        margin-bottom: 10px;
    }

    #email-marketing-automation-edit-root .guide-list li {
        margin-bottom: 6px;
        font-size: 10.5px;
    }

    #email-marketing-automation-edit-root .empty-steps {
        padding: 30px 15px;
        text-align: center;
        color: #94a3b8;
        border: 1px dashed #cbd5e1;
        border-radius: 5px;
        background: #fff;
        font-size: 11px;
    }

    /* Mobile */
    @media (max-width: 767.98px) {
        #email-marketing-automation-edit-root {
            padding-top: 12px !important;
        }

        #email-marketing-automation-edit-root .page-title {
            font-size: 16px;
        }

        #email-marketing-automation-edit-root .workflow-page-header {
            padding-bottom: 12px;
        }

        #email-marketing-automation-edit-root .top-action-btns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            gap: 6px;
        }

        #email-marketing-automation-edit-root .top-action-btns > * {
            width: 100%;
        }

        #email-marketing-automation-edit-root .top-action-btns form .btn,
        #email-marketing-automation-edit-root .top-action-btns > a,
        #email-marketing-automation-edit-root .top-action-btns > button {
            width: 100%;
        }

        #email-marketing-automation-edit-root .top-action-btns .btn-save-main {
            grid-column: 1 / -1;
            grid-row: 1;
        }

        #email-marketing-automation-edit-root .card {
            margin-bottom: 11px;
        }

        #email-marketing-automation-edit-root .section-header-wrap {
            padding: 9px 10px;
        }

        #email-marketing-automation-edit-root .step-card-body {
            padding: 11px;
        }
    }

    @media (max-width: 380px) {
        #email-marketing-automation-edit-root .section-heading {
            font-size: 11px;
        }

        #email-marketing-automation-edit-root .btn-compact {
            padding: 5px 8px !important;
            font-size: 10px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="email-marketing-automation-edit-root">

    {{-- PAGE HEADER --}}
    <div class="workflow-page-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3">

        <div class="min-width-0">
            <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                <h5 class="page-title mb-0">
                    <i class="fas fa-pen-to-square text-primary me-1"></i>
                    Edit Workflow
                </h5>

                <span class="text-muted small">/</span>

                <span class="text-secondary fw-semibold small">
                    {{ $automation->name }}
                </span>

                @if($automation->is_active)
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size:9px;">
                        <i class="fas fa-circle-check me-1"></i> ACTIVE
                    </span>
                @else
                    <span class="badge bg-secondary-subtle text-secondary border px-2 py-1" style="font-size:9px;">
                        <i class="fas fa-pause me-1"></i> PAUSED
                    </span>
                @endif
            </div>

            <p class="page-subtitle mb-0">
                Manage your automation sequence, email templates and delivery timing.
            </p>
        </div>

        <div class="d-flex gap-2 top-action-btns">

            <button type="button"
                    class="btn btn-primary btn-compact btn-save-main shadow-sm"
                    onclick="document.getElementById('automationEditForm').requestSubmit()">
                <i class="fas fa-save"></i>
                Save Changes
            </button>

            <form action="{{ route('admin.email_marketing.automations.toggle_status', $automation->id) }}"
                  method="POST" class="d-inline">
                @csrf

                <button type="submit"
                        class="btn btn-sm {{ $automation->is_active ? 'btn-outline-warning' : 'btn-outline-success' }} btn-compact w-100">
                    <i class="fas {{ $automation->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                    {{ $automation->is_active ? 'Pause Engine' : 'Activate Engine' }}
                </button>
            </form>

            <a href="{{ route('admin.email_marketing.automations.index') }}"
               class="btn btn-outline-secondary btn-compact">
                <i class="fas fa-arrow-left"></i>
                Back
            </a>
        </div>
    </div>

    {{-- VALIDATION ERRORS --}}
    @if ($errors->any())
        <div class="alert alert-danger py-2 px-3 mb-3 shadow-sm" role="alert">
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="fas fa-triangle-exclamation"></i>
                <strong class="small">Please fix the following errors:</strong>
            </div>

            <ul class="mb-0 ps-4 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.email_marketing.automations.update', $automation->id) }}"
          method="POST"
          id="automationEditForm">

        @csrf

        <div class="row g-3">

            {{-- LEFT COLUMN --}}
            <div class="col-12 col-xl-8">

                {{-- WORKFLOW CONFIGURATION --}}
                <div class="card">

                    <div class="section-header-wrap d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="section-icon">
                                <i class="fas fa-sitemap"></i>
                            </span>
                            <span class="section-heading">1. Workflow Configuration</span>
                        </div>

                        <button type="button"
                                class="collapse-toggle-btn"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapseGeneralConfig"
                                aria-expanded="true"
                                aria-controls="collapseGeneralConfig">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>

                    <div class="collapse show" id="collapseGeneralConfig">
                        <div class="p-3">
                            <div class="row g-3">

                                <div class="col-12 col-md-7">
                                    <label class="form-label">
                                        Workflow Name <span class="text-danger">*</span>
                                    </label>

                                    <input type="text"
                                           name="name"
                                           class="form-control"
                                           value="{{ old('name', $automation->name) }}"
                                           placeholder="Enter workflow name"
                                           required>
                                </div>

                                <div class="col-12 col-md-5">
                                    <label class="form-label">
                                        Assigned Trigger Event
                                        <span class="badge bg-light text-secondary border ms-1" style="font-size:8px;">LOCKED</span>
                                    </label>

                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-warning">
                                            <i class="fas fa-bolt"></i>
                                        </span>

                                        <input type="text"
                                               class="form-control font-monospace"
                                               value="{{ ucfirst(str_replace('_', ' ', $automation->trigger_event)) }}"
                                               readonly
                                               disabled>
                                    </div>

                                    <small class="text-muted d-block mt-1" style="font-size:10px;">
                                        Trigger event cannot be changed after creation.
                                    </small>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Internal Description / Strategy</label>

                                    <textarea name="description"
                                              rows="3"
                                              class="form-control"
                                              placeholder="Describe the objective and customer journey for this automation...">{{ old('description', $automation->description) }}</textarea>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                {{-- SEQUENCE BUILDER --}}
                <div class="card">

                    <div class="section-header-wrap d-flex justify-content-between align-items-center gap-2">

                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="section-icon text-success" style="background:#f0fdf4;color:#16a34a;">
                                <i class="fas fa-bars-staggered"></i>
                            </span>

                            <span class="section-heading">2. Sequence Drip Steps</span>

                            <span class="badge bg-light text-secondary border px-2 py-1"
                                  id="stepCounterBadge"
                                  style="font-size:9px;">
                                {{ $automation->steps->count() }} Steps
                            </span>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <button type="button"
                                    class="btn btn-outline-primary btn-compact"
                                    onclick="addAutomationStep()">
                                <i class="fas fa-plus"></i>
                                Add Step
                            </button>

                            <button type="button"
                                    class="collapse-toggle-btn"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#collapseStepsBuilder"
                                    aria-expanded="true"
                                    aria-controls="collapseStepsBuilder">
                                <i class="fas fa-chevron-down collapse-icon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="collapse show" id="collapseStepsBuilder">
                        <div class="p-3" style="background:#f8fafc;">
                            <div id="stepsContainer" class="d-flex flex-column gap-2">
                                {{-- Steps generated by JavaScript --}}
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- RIGHT SIDEBAR --}}
            <div class="col-12 col-xl-4">

                {{-- SAVE ACTION --}}
                <div class="card">
                    <div class="p-3">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="section-icon">
                                <i class="fas fa-floppy-disk"></i>
                            </span>
                            <div>
                                <div class="fw-bold text-dark" style="font-size:12px;">Save Workflow</div>
                                <div class="text-muted" style="font-size:10px;">Apply your configuration changes</div>
                            </div>
                        </div>

                        <button type="submit"
                                class="btn btn-primary btn-compact w-100 py-2 mb-2">
                            <i class="fas fa-save"></i>
                            Save Workflow Changes
                        </button>

                        <a href="{{ route('admin.email_marketing.automations.index') }}"
                           class="btn btn-light border btn-compact w-100">
                            <i class="fas fa-xmark"></i>
                            Cancel Editing
                        </a>
                    </div>
                </div>

                {{-- DELAY REFERENCE --}}
                <div class="card">

                    <div class="section-header-wrap d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="section-icon" style="background:#fffbeb;color:#d97706;">
                                <i class="fas fa-clock"></i>
                            </span>
                            <span class="section-heading">Delay Timing Guide</span>
                        </div>

                        <button type="button"
                                class="collapse-toggle-btn"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapseTimingGuide"
                                aria-expanded="true"
                                aria-controls="collapseTimingGuide">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>

                    <div class="collapse show" id="collapseTimingGuide">
                        <div class="p-3">
                            <p class="text-muted mb-3" style="font-size:10.5px;line-height:1.5;">
                                Set each step delay in minutes, calculated from the original trigger event.
                            </p>

                            <ul class="guide-list font-monospace">
                                <li><strong>5 minutes</strong> — Immediate reminder</li>
                                <li><strong>10 minutes</strong> — Quick attention reminder</li>
                                <li><strong>15 minutes</strong> — High conversion interval</li>
                                <li><strong>30 minutes</strong> — Standard reminder</li>
                                <li><strong>1 hour</strong> — 60 minutes</li>
                                <li><strong>2 hours</strong> — 120 minutes</li>
                                <li><strong>1 day</strong> — 1440 minutes</li>
                            </ul>

                            <div class="alert alert-info border-0 mb-0 py-2 px-2"
                                 style="font-size:10px;background:#eff6ff;color:#1e40af;">
                                <i class="fas fa-circle-info me-1"></i>
                                Delay <strong>0</strong> means immediate execution after the trigger event.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- WORKFLOW HEALTH --}}
                <div class="card">

                    <div class="section-header-wrap d-flex align-items-center gap-2">
                        <span class="section-icon" style="background:#ecfeff;color:#0891b2;">
                            <i class="fas fa-heart-pulse"></i>
                        </span>
                        <span class="section-heading">Workflow Health</span>
                    </div>

                    <div class="p-3">

                        <div class="info-row">
                            <span class="info-label">Engine Status</span>

                            <span class="info-value {{ $automation->is_active ? 'text-success' : 'text-secondary' }}">
                                <i class="fas {{ $automation->is_active ? 'fa-circle-check' : 'fa-circle-pause' }} me-1"></i>
                                {{ $automation->is_active ? 'ACTIVE' : 'PAUSED' }}
                            </span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Trigger Condition</span>
                            <span class="info-value">
                                <span class="badge bg-light text-dark border" style="font-size:9px;">
                                    {{ ucfirst(str_replace('_', ' ', $automation->trigger_event)) }}
                                </span>
                            </span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Created Date</span>
                            <span class="info-value font-monospace">
                                {{ $automation->created_at->format('M d, Y') }}
                            </span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Last Updated</span>
                            <span class="info-value font-monospace">
                                {{ $automation->updated_at->format('M d, Y') }}
                            </span>
                        </div>

                    </div>
                </div>

                {{-- EXECUTION LOGS --}}
                <div class="card">
                    <div class="p-3">

                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="section-icon">
                                <i class="fas fa-list-check"></i>
                            </span>
                            <span class="section-heading">Audit & Dispatch History</span>
                        </div>

                        <p class="text-muted mb-3" style="font-size:10.5px;line-height:1.5;">
                            Review workflow execution queues, customer delivery logs and email bounce errors.
                        </p>

                        <a href="{{ route('admin.email_marketing.automations.logs') }}"
                           class="btn btn-outline-secondary btn-compact w-100">
                            <i class="fas fa-clock-rotate-left text-primary"></i>
                            View Execution Logs
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

{{-- NOTICE MODAL --}}
<div class="modal fade workflow-modal-scope"
     id="workflowNoticeModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index:106500;">

    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg p-3 text-center">

            <div class="modal-body p-1">

                <div class="d-inline-flex align-items-center justify-content-center mb-3"
                     style="width:45px;height:45px;background:#fffbeb;color:#d97706;border-radius:50%;">
                    <i class="fas fa-circle-exclamation fs-4"></i>
                </div>

                <h6 class="fw-bold text-dark mb-1"
                    id="workflowNoticeTitle"
                    style="font-size:14px;">
                    Attention Required
                </h6>

                <p class="text-muted small mb-3"
                   id="workflowNoticeMsg"
                   style="font-size:11px;line-height:1.5;"></p>

                <button type="button"
                        class="btn btn-dark btn-compact px-4"
                        data-bs-dismiss="modal">
                    Acknowledge
                </button>

            </div>
        </div>
    </div>
</div>

{{-- DYNAMIC STEPS JAVASCRIPT --}}
<script>
    const templatesList = @json($templates);
    const existingSteps = @json($automation->steps);

    let stepCount = 0;

    /**
     * Show notice modal
     */
    function showNoticeModal(message, title = 'Notice') {
        const titleElement = document.getElementById('workflowNoticeTitle');
        const messageElement = document.getElementById('workflowNoticeMsg');
        const modalElement = document.getElementById('workflowNoticeModal');

        if (titleElement) titleElement.textContent = title;
        if (messageElement) messageElement.textContent = message;

        if (modalElement && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalElement).show();
        } else {
            alert(message);
        }
    }

    /**
     * Format delay into readable text
     */
    function formatHumanDelay(minutes) {
        minutes = parseInt(minutes, 10) || 0;

        if (minutes === 0) {
            return 'Immediate (0 mins)';
        }

        if (minutes < 60) {
            return `${minutes} min${minutes > 1 ? 's' : ''}`;
        }

        if (minutes < 1440) {
            const hours = minutes / 60;
            const formatted = Number.isInteger(hours) ? hours : hours.toFixed(1);

            return `${formatted} hr${hours > 1 ? 's' : ''}`;
        }

        const days = minutes / 1440;
        const formatted = Number.isInteger(days) ? days : days.toFixed(1);

        return `${formatted} day${days > 1 ? 's' : ''}`;
    }

    /**
     * Update delay badge
     */
    function handleDelayInputChange(stepId, value) {
        const badge = document.getElementById(`delay-badge-${stepId}`);

        if (badge) {
            badge.textContent = formatHumanDelay(value);
        }
    }

    /**
     * Apply preset delay
     */
    function setStepDelayPreset(stepId, minutes) {
        const input = document.getElementById(`delay-input-${stepId}`);

        if (!input) return;

        input.value = minutes;
        input.dispatchEvent(new Event('input', { bubbles: true }));
    }

    /**
     * Create delay preset chip
     */
    function createDelayChip(label, minutes, stepId) {
        const chip = document.createElement('button');

        chip.type = 'button';
        chip.className = 'delay-chip';
        chip.textContent = label;
        chip.addEventListener('click', function() {
            setStepDelayPreset(stepId, minutes);
        });

        return chip;
    }

    /**
     * Reindex all steps after add/remove
     */
    function reindexSteps() {
        const container = document.getElementById('stepsContainer');
        const cards = container.querySelectorAll('.step-card');

        cards.forEach((card, index) => {
            const numberElement = card.querySelector('.step-number');

            if (numberElement) {
                numberElement.textContent = index + 1;
            }

            // Keep Laravel array indexes continuous after removing a step.
            card.querySelectorAll('[data-field]').forEach(function(field) {
                const fieldName = field.dataset.field;
                field.name = `steps[${index}][${fieldName}]`;
            });
        });

        const badge = document.getElementById('stepCounterBadge');

        if (badge) {
            badge.textContent = `${cards.length} ${cards.length === 1 ? 'Step' : 'Steps'}`;
        }

        const emptyState = container.querySelector('.empty-steps');

        if (cards.length === 0 && !emptyState) {
            const empty = document.createElement('div');
            empty.className = 'empty-steps';
            empty.innerHTML = '<i class="fas fa-envelope-open-text fs-4 mb-2 d-block"></i>No workflow steps added yet. Click Add Step to create one.';
            container.appendChild(empty);
        } else if (cards.length > 0 && emptyState) {
            emptyState.remove();
        }
    }

    /**
     * Add a new automation step
     */
    function addAutomationStep(delay = 10, subject = '', templateId = '') {
        const container = document.getElementById('stepsContainer');

        if (!container) return;

        const emptyState = container.querySelector('.empty-steps');
        if (emptyState) emptyState.remove();

        const index = stepCount++;
        const card = document.createElement('div');

        card.className = 'card step-card mb-0';
        card.id = `step-card-${index}`;

        // Build the card using static markup only.
        // Dynamic values are assigned through DOM properties.
        card.innerHTML = `
            <div class="step-card-header d-flex justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <span class="badge step-number-badge">
                        STEP <span class="step-number">${container.querySelectorAll('.step-card').length + 1}</span>
                    </span>

                    <span class="step-type-label">
                        <i class="fas fa-paper-plane text-primary me-1"></i>
                        Automated Email
                    </span>

                    <span class="badge bg-light text-secondary border d-none d-sm-inline-block"
                          id="delay-badge-${index}"
                          style="font-size:9px;">
                        ${formatHumanDelay(delay)}
                    </span>
                </div>

                <button type="button"
                        class="step-delete-btn"
                        title="Remove Step"
                        aria-label="Remove Step">
                    <i class="fas fa-trash-can" style="font-size:10px;"></i>
                </button>
            </div>

            <div class="step-card-body">
                <div class="row g-3">

                    <div class="col-12 col-md-5">
                        <label class="form-label d-flex justify-content-between align-items-center">
                            <span>Step Delay <span class="text-danger">*</span></span>
                            <small class="text-muted fw-normal" style="font-size:9px;">Minutes</small>
                        </label>

                        <div class="input-group">
                            <input type="number"
                                   id="delay-input-${index}"
                                   class="form-control font-monospace bn-digit"
                                   min="0"
                                   required
                                   data-field="delay">

                            <span class="input-group-text bg-light text-muted"
                                  style="font-size:10px;">
                                mins
                            </span>
                        </div>

                        <div class="d-flex flex-wrap gap-1 mt-2 delay-presets"></div>
                    </div>

                    <div class="col-12 col-md-7">
                        <label class="form-label">
                            Email Design Template <span class="text-danger">*</span>
                        </label>

                        <select class="form-select template-select"
                                data-field="template_id"
                                required>
                            <option value="">-- Select Design Template --</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">
                            Custom Subject Override
                            <span class="text-muted fw-normal">(Optional)</span>
                        </label>

                        <input type="text"
                               class="form-control subject-input"
                               data-field="subject"
                               placeholder="Leave empty to use the template default subject">
                    </div>

                </div>
            </div>
        `;

        // Assign dynamic values safely.
        const delayInput = card.querySelector(`#delay-input-${index}`);
        delayInput.value = delay ?? 10;

        const subjectInput = card.querySelector('.subject-input');
        subjectInput.value = subject ?? '';

        // Populate templates safely using textContent.
        const templateSelect = card.querySelector('.template-select');

        if (Array.isArray(templatesList)) {
            templatesList.forEach(function(template) {
                const option = document.createElement('option');

                option.value = template.id;
                option.textContent = template.name ?? `Template #${template.id}`;

                if (String(templateId) === String(template.id)) {
                    option.selected = true;
                }

                templateSelect.appendChild(option);
            });
        }

        // Delay presets
        const presetContainer = card.querySelector('.delay-presets');

        [
            ['5m', 5],
            ['10m', 10],
            ['15m', 15],
            ['30m', 30],
            ['1h', 60],
            ['2h', 120],
            ['1d', 1440]
        ].forEach(function(preset) {
            presetContainer.appendChild(
                createDelayChip(preset[0], preset[1], index)
            );
        });

        // Delay live update
        delayInput.addEventListener('input', function() {
            handleDelayInputChange(index, this.value);
        });

        // Remove step
        card.querySelector('.step-delete-btn').addEventListener('click', function() {
            removeAutomationStep(index);
        });

        container.appendChild(card);

        reindexSteps();
    }

    /**
     * Remove a workflow step
     */
    function removeAutomationStep(index) {
        const card = document.getElementById(`step-card-${index}`);

        if (!card) return;

        const container = document.getElementById('stepsContainer');
        const currentSteps = container.querySelectorAll('.step-card');

        if (currentSteps.length <= 1) {
            showNoticeModal(
                'A workflow sequence must contain at least one step before saving.',
                'Step Limit'
            );
            return;
        }

        card.remove();
        reindexSteps();
    }

    /**
     * Initialize existing workflow steps
     */
    document.addEventListener('DOMContentLoaded', function() {
        if (Array.isArray(existingSteps) && existingSteps.length > 0) {
            existingSteps.forEach(function(step) {
                addAutomationStep(
                    step.delay_minutes ?? 10,
                    step.subject ?? '',
                    step.template_id ?? ''
                );
            });
        } else {
            addAutomationStep(10, 'Did you forget items in your cart?', '');
            addAutomationStep(1440, 'Your exclusive 10% voucher is waiting!', '');
        }

        reindexSteps();
    });
</script>

@endsection
