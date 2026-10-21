@extends('layouts.admin')

@section('title', 'Create Automation Workflow - Email Marketing')

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #email-marketing-automation-create-root .btn,
    #email-marketing-automation-create-root .btn-sm,
    #email-marketing-automation-create-root button,
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
    #email-marketing-automation-create-root .card {
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
    #email-marketing-automation-create-root .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #email-marketing-automation-create-root .form-control,
    #email-marketing-automation-create-root .form-select {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #email-marketing-automation-create-root .form-control:focus,
    #email-marketing-automation-create-root .form-select:focus {
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
    .delay-chip:hover,
    .delay-chip.active-chip {
        background-color: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
    }

    /* Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .workflow-create-header h5 {
            font-size: 16px !important;
        }
        .header-actions-wrap {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .header-actions-wrap .btn {
            width: 100%;
            padding: 8px 6px !important;
            font-size: 11px !important;
            justify-content: center;
        }
        #email-marketing-automation-create-root .card {
            margin-bottom: 12px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-automation-create-root">

    {{-- 1. Header & Navigation --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 workflow-create-header">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-diagram-project text-primary"></i>
                <span>Create Automation Workflow</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Set up event-triggered email drip sequences and automated lifecycle journeys.
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
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 small border-0 shadow-xs mb-3" role="alert">
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
            {{-- Left Column: General Configuration & Step Sequence --}}
            <div class="col-12 col-xl-8">

                {{-- 1. General Workflow Information (Collapsible) --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
                        <span class="fw-bold text-dark small" style="font-size: 12.5px;">
                            <i class="fas fa-sitemap text-primary me-1"></i> 1. General Workflow Details
                        </span>
                        <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseGeneralInfo" aria-expanded="true" aria-controls="collapseGeneralInfo" title="Toggle Section">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>
                    <div class="collapse show" id="collapseGeneralInfo">
                        <div class="p-3">
                            <div class="row g-2">
                                <div class="col-12 col-md-7">
                                    <label class="form-label">
                                        Workflow Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="name" class="form-control" placeholder="e.g. 3-Step Abandoned Cart Recovery Sequence" value="{{ old('name') }}" required>
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

                                <div class="col-12 mt-1">
                                    <label class="form-label">Internal Description / Strategy (Optional)</label>
                                    <textarea name="description" rows="2" class="form-control shadow-none" placeholder="Explain the objective and customer segment for this automation...">{{ old('description') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. Multi-Step Drip Sequence Builder (Collapsible) --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
                        <div>
                            <span class="fw-bold text-dark small" style="font-size: 12.5px;">
                                <i class="fas fa-bars-staggered text-success me-1"></i> 2. Automated Step Sequence
                            </span>
                            <span class="badge bg-light text-secondary border font-monospace ms-1 px-2 py-0" id="stepCounterBadge" style="font-size: 9.5px;">
                                2 Steps Active
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
                                {{-- Step items will be inserted here dynamically --}}
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Right Column: Information & Help Guidelines --}}
            <div class="col-12 col-xl-4">

                {{-- Action Panel Card --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="p-3">
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-xs btn-compact mb-2" style="font-size: 12px !important;">
                            <i class="fas fa-save me-1"></i> Activate Automation
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
                            <p class="mb-2">Enter step delays in <strong>minutes</strong> (e.g. 5, 10, 30) from the trigger event:</p>
                            <ul class="ps-3 mb-2 font-monospace bn-digit">
                                <li class="mb-1"><strong>5 minutes:</strong> <code>5</code> (Immediate / Ultra-fast cart recovery)</li>
                                <li class="mb-1"><strong>10 minutes:</strong> <code>10</code> (Quick attention reminder)</li>
                                <li class="mb-1"><strong>15 minutes:</strong> <code>15</code> (High conversion interval)</li>
                                <li class="mb-1"><strong>30 minutes:</strong> <code>30</code> (Standard recovery delay)</li>
                                <li class="mb-1"><strong>1 hour:</strong> <code>60</code></li>
                                <li class="mb-1"><strong>2 hours:</strong> <code>120</code></li>
                                <li class="mb-1"><strong>1 day (24 hrs):</strong> <code>1440</code></li>
                                <li class="mb-1"><strong>2 days (48 hrs):</strong> <code>2880</code></li>
                            </ul>
                            <div class="alert alert-info py-2 px-2 mb-0 border-0 shadow-xs" style="font-size: 10.5px; border-radius: 5px;">
                                <i class="fas fa-info-circle me-1 text-primary"></i> Step delay <code>0</code> executes immediately once the customer triggers the event.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Operational Guidance Card --}}
                <div class="card p-3">
                    <h6 class="fw-bold text-dark mb-1 small d-flex align-items-center gap-1">
                        <i class="fas fa-shield-halved text-success"></i> <span>Lifecycle Safety Note</span>
                    </h6>
                    <p class="text-muted small mb-1" style="font-size: 11px; line-height: 1.4;">
                        &bull; Subscribers who complete an order will automatically exit abandoned cart sequences to prevent irrelevant follow-ups.
                    </p>
                    <p class="text-muted small mb-0" style="font-size: 11px; line-height: 1.4;">
                        &bull; Customize subject lines per step to test different angles (e.g. curiosity, urgency, discounts).
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