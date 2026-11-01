@extends('layouts.admin')

@section('title', 'Edit Automation - ' . $automation->name)

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #email-marketing-automation-edit-root .btn,
    #email-marketing-automation-edit-root .btn-sm,
    #email-marketing-automation-edit-root button,
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

    /* Enterprise Surface Cards */
    #email-marketing-automation-edit-root .card {
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

    /* Form Controls */
    #email-marketing-automation-edit-root .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #email-marketing-automation-edit-root .form-control,
    #email-marketing-automation-edit-root .form-select {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #email-marketing-automation-edit-root .form-control:focus,
    #email-marketing-automation-edit-root .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Dynamic Step Cards */
    .step-card {
        border: 1px solid #cbd5e1 !important;
        background: #ffffff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .step-card:hover {
        border-color: #94a3b8 !important;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    }

    /* Quick Preset Delay Chips */
    .delay-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 2px 7px;
        font-size: 10px;
        font-weight: 600;
        color: #475569;
        background-color: #f1f5f9;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        user-select: none;
        transition: all 0.15s ease;
        border-radius: 4px !important;
    }
    .delay-chip:hover {
        background-color: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
    }

    /* Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .workflow-edit-header h5 {
            font-size: 16px !important;
        }
        .top-action-btns {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .top-action-btns .btn,
        .top-action-btns form {
            width: 100%;
        }
        .top-action-btns form .btn {
            width: 100%;
            justify-content: center;
        }
        .top-action-btns .btn-save-main {
            grid-column: span 2;
        }
        #email-marketing-automation-edit-root .card {
            margin-bottom: 12px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-automation-edit-root">

    {{-- 1. Header & Navigation --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 workflow-edit-header">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                    <i class="fas fa-pencil text-primary"></i>
                    <span>Edit Workflow: <span class="text-secondary">{{ $automation->name }}</span></span>
                </h5>
                @if($automation->is_active)
                    <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-2 py-1" style="font-size: 10px;">
                        <i class="fas fa-circle-check me-1"></i> ACTIVE ENGINE
                    </span>
                @else
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle font-monospace px-2 py-1" style="font-size: 10px;">
                        <i class="fas fa-pause me-1"></i> PAUSED
                    </span>
                @endif
            </div>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Modify workflow steps, adjust custom timing delays, and update message content.
            </small>
        </div>

        <div class="d-flex gap-2 top-action-btns">
            {{-- Toggle Status Button --}}
            <form action="{{ route('admin.email_marketing.automations.toggle_status', $automation->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm {{ $automation->is_active ? 'btn-outline-warning' : 'btn-outline-success' }} btn-compact shadow-xs fw-semibold w-100">
                    <i class="fas {{ $automation->is_active ? 'fa-pause' : 'fa-play' }} me-1"></i>
                    <span>{{ $automation->is_active ? 'Pause Engine' : 'Activate Engine' }}</span>
                </button>
            </form>

            <a href="{{ route('admin.email_marketing.automations.index') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-arrow-left me-1"></i> <span>Back</span>
            </a>

            <button type="button" class="btn btn-sm btn-primary btn-compact shadow-xs fw-bold btn-save-main" onclick="document.getElementById('automationEditForm').submit()">
                <i class="fas fa-save me-1"></i> <span>Save Changes</span>
            </button>
        </div>
    </div>

    {{-- 2. Validation Error Alerts --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 small border-0 shadow-xs mb-3" role="alert">
            <div class="d-flex align-items-center gap-1 mb-1">
                <i class="fas fa-triangle-exclamation text-danger fs-5"></i>
                <strong class="text-dark">Please fix the following validation errors:</strong>
            </div>
            <ul class="mb-0 ps-3 text-dark">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('admin.email_marketing.automations.update', $automation->id) }}" method="POST" id="automationEditForm">
        @csrf

        <div class="row g-3">
            {{-- Left Column: General Configuration & Steps Sequence --}}
            <div class="col-12 col-xl-8">

                {{-- 1. General Workflow Details (Collapsible) --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
                        <span class="fw-bold text-dark small" style="font-size: 12.5px;">
                            <i class="fas fa-sitemap text-primary me-1"></i> 1. Workflow Configuration
                        </span>
                        <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseGeneralConfig" aria-expanded="true" aria-controls="collapseGeneralConfig" title="Toggle Section">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>
                    <div class="collapse show" id="collapseGeneralConfig">
                        <div class="p-3">
                            <div class="row g-2">
                                <div class="col-12 col-md-7">
                                    <label class="form-label">
                                        Workflow Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $automation->name) }}" required>
                                </div>

                                <div class="col-12 col-md-5">
                                    <label class="form-label">Assigned Trigger Event (Locked)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-warning border-end-0"><i class="fas fa-bolt" style="font-size: 11px;"></i></span>
                                        <input type="text" class="form-control border-start-0 bg-light text-dark font-monospace fw-semibold" value="{{ ucfirst(str_replace('_', ' ', $automation->trigger_event)) }}" readonly disabled>
                                    </div>
                                    <small class="text-muted d-block mt-1" style="font-size: 10px;">Trigger conditions are permanently locked after creation.</small>
                                </div>

                                <div class="col-12 mt-1">
                                    <label class="form-label">Internal Description / Strategy</label>
                                    <textarea name="description" rows="2" class="form-control shadow-none" placeholder="Explain the objective and customer journey for this automation...">{{ old('description', $automation->description) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. Multi-Step Drip Sequence Builder (Collapsible & Fully Editable) --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
                        <div>
                            <span class="fw-bold text-dark small" style="font-size: 12.5px;">
                                <i class="fas fa-bars-staggered text-success me-1"></i> 2. Sequence Drip Steps
                            </span>
                            <span class="badge bg-light text-secondary border font-monospace ms-1 px-2 py-0" id="stepCounterBadge" style="font-size: 9.5px;">
                                {{ $automation->steps->count() }} Steps Active
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary btn-compact shadow-xs fw-bold" onclick="addAutomationStep()">
                                <i class="fas fa-plus me-1"></i> <span>Add Next Step</span>
                            </button>
                            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseStepsBuilder" aria-expanded="true" aria-controls="collapseStepsBuilder" title="Toggle Steps">
                                <i class="fas fa-chevron-down collapse-icon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="collapse show" id="collapseStepsBuilder">
                        <div class="p-3 bg-light bg-opacity-50">
                            <div id="stepsContainer" class="d-flex flex-column gap-2">
                                {{-- Step items populated dynamically via JavaScript below --}}
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Right Column: Information & Actions --}}
            <div class="col-12 col-xl-4">

                {{-- Action Panel Card --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="p-3">
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-xs btn-compact mb-2" style="font-size: 12px !important;">
                            <i class="fas fa-save me-1"></i> Save Workflow Changes
                        </button>
                        <a href="{{ route('admin.email_marketing.automations.index') }}" class="btn btn-light border w-100 py-2 btn-compact shadow-xs text-secondary">
                            Cancel
                        </a>
                    </div>
                </div>

                {{-- Recommended Delay Cheatsheet (Collapsible) --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
                        <span class="fw-bold text-dark small" style="font-size: 12px;">
                            <i class="fas fa-clock text-warning me-1"></i> Delay Timing Reference Guide
                        </span>
                        <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseTimingGuide" aria-expanded="true" aria-controls="collapseTimingGuide" title="Toggle Guide">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>
                    <div class="collapse show" id="collapseTimingGuide">
                        <div class="p-3 small text-muted" style="font-size: 11px; line-height: 1.5;">
                            <p class="mb-2">Enter step delays in <strong>minutes</strong> (5, 10, 30) from the trigger event:</p>
                            <ul class="ps-3 mb-2 font-monospace bn-digit">
                                <li class="mb-1"><strong>5 minutes:</strong> <code>5</code> (Immediate / Ultra-fast cart recovery)</li>
                                <li class="mb-1"><strong>10 minutes:</strong> <code>10</code> (Quick attention reminder)</li>
                                <li class="mb-1"><strong>15 minutes:</strong> <code>15</code> (High conversion interval)</li>
                                <li class="mb-1"><strong>30 minutes:</strong> <code>30</code> (Standard recovery delay)</li>
                                <li class="mb-1"><strong>1 hour:</strong> <code>60</code></li>
                                <li class="mb-1"><strong>2 hours:</strong> <code>120</code></li>
                                <li class="mb-1"><strong>1 day (24 hrs):</strong> <code>1440</code></li>
                            </ul>
                            <div class="alert alert-info py-2 px-2 mb-0 border-0 shadow-xs" style="font-size: 10.5px; border-radius: 5px;">
                                <i class="fas fa-info-circle me-1 text-primary"></i> Step delay <code>0</code> executes immediately once the customer triggers the event.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Automation Health Monitor Card --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
                        <span class="fw-bold text-dark small" style="font-size: 12px;">
                            <i class="fas fa-circle-info text-info me-1"></i> Workflow Health Info
                        </span>
                    </div>
                    <div class="p-3 small text-muted" style="font-size: 11px; line-height: 1.55;">
                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                            <span>Engine Status:</span>
                            <strong class="{{ $automation->is_active ? 'text-success' : 'text-secondary' }} font-monospace">
                                {{ $automation->is_active ? 'ACTIVE & LISTENING' : 'PAUSED' }}
                            </strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                            <span>Trigger Condition:</span>
                            <span class="badge bg-light text-dark border font-monospace" style="font-size: 9.5px;">
                                {{ ucfirst(str_replace('_', ' ', $automation->trigger_event)) }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                            <span>Created Date:</span>
                            <span class="text-dark font-monospace bn-digit">{{ $automation->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-1">
                            <span>Last Updated:</span>
                            <span class="text-dark font-monospace bn-digit">{{ $automation->updated_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Direct Execution Logs Shortcut Card --}}
                <div class="card p-3 shadow-xs">
                    <h6 class="fw-bold text-dark mb-1 small d-flex align-items-center gap-1">
                        <i class="fas fa-list-check text-primary"></i> <span>Audit &amp; Dispatch History</span>
                    </h6>
                    <p class="text-muted small mb-2" style="font-size: 11px; line-height: 1.4;">
                        Review realtime execution queues, customer delivery logs, and bounce errors for this workflow.
                    </p>
                    <a href="{{ route('admin.email_marketing.automations.logs') }}" class="btn btn-outline-secondary btn-compact w-100 shadow-xs fw-semibold">
                        <i class="fas fa-clock-rotate-left me-1 text-primary"></i> <span>View Execution Logs</span>
                    </a>
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
    const existingSteps = @json($automation->steps);
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
        // Populate existing steps from database
        if (existingSteps && existingSteps.length > 0) {
            existingSteps.forEach(st => {
                addAutomationStep(st.delay_minutes ?? 10, st.subject || '', st.template_id || '');
            });
        } else {
            addAutomationStep(10, 'Did you forget items in your cart?');
            addAutomationStep(1440, 'Your exclusive 10% voucher is waiting!');
        }
    });
</script>
@endsection