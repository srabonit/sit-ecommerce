
@extends('layouts.admin')

@section('title', 'Edit SMS Template: ' . $template->name)

@section('admin_content')
<div class="container-fluid px-3 px-md-4 py-4 sms-template-edit">

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="d-flex align-items-center flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1 text-dark page-title">Edit SMS Template</h4>
                <p class="text-muted small mb-0">Manage your SMS content, triggers and delivery settings.</p>
            </div>

            @if(!empty($template->is_system))
                <span class="badge system-badge">
                    <i class="fas fa-lock me-1"></i> System
                </span>
            @endif
        </div>

        <a href="{{ route('admin.sms.templates.index') }}" class="btn btn-outline-secondary btn-sm px-3 action-btn">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show small mb-4" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i>
            Please check form validation errors below.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show small mb-4" role="alert">
            <i class="fas fa-exclamation-circle me-1"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.sms.templates.update', $template->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">

            {{-- Left Column: Form Details --}}
            <div class="col-12 col-lg-8">

                {{-- Template Configuration --}}
                <div class="card template-card mb-4">
                    <div class="card-header template-card-header">
                        <div class="d-flex align-items-center gap-2">
                            <span class="section-icon">
                                <i class="fas fa-sliders-h"></i>
                            </span>
                            <div>
                                <h6 class="fw-bold mb-0">Template Configuration</h6>
                                <small class="text-muted">Configure the template and its trigger rules.</small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-3 p-md-4">

                        {{-- Trigger Rule Selector --}}
                        <div class="mb-4">
                            <label class="form-label">
                                Trigger Rule <span class="text-danger">*</span>
                            </label>

                            <select id="triggerRuleSelector" class="form-select form-select-sm">
                                <optgroup label="Order Lifecycle Rules">
                                    <option value="order_pending" data-type="order" {{ in_array($template->key, ['order_pending', 'pending']) ? 'selected' : '' }}>Order Pending</option>
                                    <option value="order_placed_customer" data-type="order" {{ in_array($template->key, ['order_placed_customer', 'order_placed', 'order_confirmation']) ? 'selected' : '' }}>Order Placed</option>
                                    <option value="order_received" data-type="order" {{ in_array($template->key, ['order_received', 'received']) ? 'selected' : '' }}>Order Received</option>
                                    <option value="order_confirmed" data-type="order" {{ in_array($template->key, ['order_confirmed', 'confirmed']) ? 'selected' : '' }}>Order Confirmed</option>
                                    <option value="order_processing" data-type="order" {{ in_array($template->key, ['order_processing', 'order_packed', 'processing']) ? 'selected' : '' }}>Order Processing / Packed</option>
                                    <option value="order_shipped" data-type="order" {{ in_array($template->key, ['order_shipped', 'order_dispatch', 'shipped']) ? 'selected' : '' }}>Order Shipped</option>
                                    <option value="order_attempt_to_deliver" data-type="order" {{ in_array($template->key, ['order_attempt_to_deliver', 'attempt_to_deliver']) ? 'selected' : '' }}>Out for Delivery</option>
                                    <option value="order_delivered" data-type="order" {{ in_array($template->key, ['order_delivered', 'delivered', 'completed']) ? 'selected' : '' }}>Order Delivered</option>
                                    <option value="order_exchange" data-type="order" {{ in_array($template->key, ['order_exchange', 'exchange']) ? 'selected' : '' }}>Order Exchange</option>
                                    <option value="order_returned" data-type="order" {{ in_array($template->key, ['order_returned', 'returned', 'return']) ? 'selected' : '' }}>Order Returned</option>
                                    <option value="order_cancelled" data-type="order" {{ in_array($template->key, ['order_cancelled', 'cancelled', 'order_cancel']) ? 'selected' : '' }}>Order Cancelled</option>
                                </optgroup>

                                <optgroup label="System &amp; Security Triggers">
                                    <option value="admin_new_order_alert" data-type="general" {{ in_array($template->key, ['admin_new_order_alert', 'admin_new_order']) ? 'selected' : '' }}>Admin New Order Alert</option>
                                    <option value="admin_low_balance_alert" data-type="general" {{ in_array($template->key, ['admin_low_balance_alert', 'low_balance']) ? 'selected' : '' }}>Admin Low Balance Alert</option>
                                    <option value="password_reset_otp" data-type="auth" {{ in_array($template->key, ['password_reset_otp', 'password_reset']) ? 'selected' : '' }}>Password Reset OTP</option>
                                    <option value="custom_login_otp" data-type="auth" {{ in_array($template->key, ['custom_login_otp', 'login_otp']) ? 'selected' : '' }}>Login Verification OTP</option>
                                    <option value="welcome_sms" data-type="general" {{ in_array($template->key, ['welcome_sms', 'customer_welcome']) ? 'selected' : '' }}>Customer Welcome</option>
                                </optgroup>

                                <optgroup label="Custom Configuration">
                                    <option value="custom" data-type="marketing" {{ !in_array($template->key, ['order_pending', 'order_placed_customer', 'order_placed', 'order_received', 'order_confirmed', 'order_processing', 'order_shipped', 'order_attempt_to_deliver', 'order_delivered', 'order_exchange', 'order_returned', 'order_cancelled', 'admin_new_order_alert', 'admin_low_balance_alert', 'password_reset_otp', 'custom_login_otp', 'welcome_sms', 'customer_welcome']) ? 'selected' : '' }}>Custom / Marketing Campaign</option>
                                </optgroup>
                            </select>
                        </div>

                        {{-- Template Name & Key --}}
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <label class="form-label">
                                    Template Name <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    name="name"
                                    id="templateName"
                                    class="form-control form-control-sm @error('name') is-invalid @enderror"
                                    value="{{ old('name', $template->name) }}"
                                    required
                                >

                                @error('name')
                                    <div class="invalid-feedback small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">
                                    System Identifier Key <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    name="key"
                                    id="templateKey"
                                    class="form-control form-control-sm font-monospace @error('key') is-invalid @enderror"
                                    value="{{ old('key', $template->key) }}"
                                    {{ !empty($template->is_system) ? 'readonly' : 'required' }}
                                >

                                @error('key')
                                    <div class="invalid-feedback small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Event Type & Sender ID --}}
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <label class="form-label">
                                    Category / Event Type <span class="text-danger">*</span>
                                </label>

                                <select
                                    name="event_type"
                                    id="eventTypeSelect"
                                    class="form-select form-select-sm @error('event_type') is-invalid @enderror"
                                    required
                                >
                                    <option value="order" {{ old('event_type', $template->event_type) === 'order' ? 'selected' : '' }}>Order Notifications</option>
                                    <option value="auth" {{ old('event_type', $template->event_type) === 'auth' ? 'selected' : '' }}>Auth &amp; OTP</option>
                                    <option value="marketing" {{ old('event_type', $template->event_type) === 'marketing' ? 'selected' : '' }}>Marketing &amp; Campaigns</option>
                                    <option value="general" {{ old('event_type', $template->event_type) === 'general' ? 'selected' : '' }}>General Alerts</option>
                                </select>

                                @error('event_type')
                                    <div class="invalid-feedback small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Sender ID / Masking</label>

                                <select name="sender_id" id="senderIdSelect" class="form-select form-select-sm">
                                    <option value="">Default Gateway Sender ID</option>

                                    @foreach($senderIds as $sid)
                                        <option value="{{ $sid }}" {{ old('sender_id', $template->sender_id) === $sid ? 'selected' : '' }}>{{ $sid }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="section-divider mb-4"></div>

                        {{-- Message Body & Dynamic Tags --}}
                        <div class="mb-0">

                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                <label class="form-label mb-0">
                                    Message Content <span class="text-danger">*</span>
                                </label>

                                <span class="badge encoding-badge" id="encodingBadge">
                                    {{ !empty($template->is_unicode) ? 'Unicode' : 'GSM-7' }}
                                </span>
                            </div>

                            @php
                                $vars = is_array($template->variables)
                                    ? $template->variables
                                    : (is_string($template->variables) ? (json_decode($template->variables, true) ?? array_filter(array_map('trim', explode(',', $template->variables)))) : []);

                                $standardVars = ['customer_name', 'invoice_no', 'order_total', 'order_status', 'courier_name', 'tracking_code', 'store_name', 'website_url', 'otp_code'];
                                $allPillVars = array_unique(array_merge($standardVars, (array) $vars));
                            @endphp

                            {{-- Dynamic Variable Pills --}}
                            <div class="variable-panel mb-3">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                    <span class="variable-panel-title">
                                        <i class="fas fa-code me-1"></i> Available Variables
                                    </span>
                                    <span class="text-muted variable-hint">Click to insert into message</span>
                                </div>

                                <div class="d-flex flex-wrap gap-2" id="variablePillsContainer">
                                    @foreach($allPillVars as $vTag)
                                        <button
                                            type="button"
                                            class="btn btn-outline-secondary btn-xs variable-tag shadow-none"
                                            data-tag="{{ '{' . $vTag . '}' }}"
                                        >
                                            {{ '{' . $vTag . '}' }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <textarea
                                name="body"
                                id="messageBody"
                                rows="6"
                                class="form-control font-monospace small @error('body') is-invalid @enderror"
                                placeholder="Write message template here..."
                                required
                            >{{ old('body', $template->body) }}</textarea>

                            @error('body')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror

                            {{-- Character & Parts Counter --}}
                            <div class="message-metrics d-flex justify-content-between align-items-center flex-wrap gap-3 mt-3">
                                <div class="metric-item">
                                    <span class="metric-label">Characters</span>
                                    <span class="metric-value" id="charCount">0</span>
                                </div>

                                <div class="metric-item">
                                    <span class="metric-label">SMS Parts</span>
                                    <span class="metric-value" id="partsCount">{{ $template->estimated_parts ?? 1 }}</span>
                                    <span class="metric-info" id="partsInfo">(160 chars/part)</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Right Column: Preview & Controls --}}
            <div class="col-12 col-lg-4">

                {{-- Status & Save Card --}}
                <div class="card template-card mb-4">
                    <div class="card-header template-card-header">
                        <div class="d-flex align-items-center gap-2">
                            <span class="section-icon">
                                <i class="fas fa-paper-plane"></i>
                            </span>
                            <div>
                                <h6 class="fw-bold mb-0">Publish Settings</h6>
                                <small class="text-muted">Control template availability.</small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <label class="form-check-label fw-semibold text-dark mb-0">Active Status</label>
                                <p class="small text-muted mb-0 mt-1">Enable or disable this template.</p>
                            </div>

                            <div class="form-check form-switch m-0">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="is_active"
                                    value="1"
                                    {{ old('is_active', $template->is_active) ? 'checked' : '' }}
                                    style="cursor: pointer;"
                                >
                            </div>
                        </div>

                        <div class="section-divider mb-3"></div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-sm py-2 primary-action-btn">
                                <i class="fas fa-save me-1"></i> Update Template
                            </button>

                            <a href="{{ route('admin.sms.templates.index') }}" class="btn btn-light btn-sm py-2 cancel-action-btn">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Live Preview Card --}}
                <div class="card template-card preview-card">
                    <div class="card-header template-card-header">
                        <div class="d-flex align-items-center gap-2">
                            <span class="section-icon preview-icon">
                                <i class="fas fa-mobile-alt"></i>
                            </span>
                            <div>
                                <h6 class="fw-bold mb-0">Live SMS Preview</h6>
                                <small class="text-muted">Preview how your message will appear.</small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body preview-area">
                        <div class="phone-preview">
                            <div class="phone-preview-header">
                                <span class="preview-sender" id="previewSender">{{ $template->sender_id ?: 'SHOP NOW BD' }}</span>
                                <span class="preview-time">Just now</span>
                            </div>

                            <div class="phone-preview-body font-monospace" id="previewBody">{{ $template->body }}</div>
                        </div>

                        <div class="preview-caption mt-3">
                            <i class="fas fa-info-circle me-1"></i>
                            This is a live preview of your SMS content.
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

{{-- Direct In-Content JavaScript Engine (Runs immediately without layout stack dependency) --}}
<script>
(function() {
    function initTemplateEditEngine() {
        const triggerRuleSelector = document.getElementById('triggerRuleSelector');
        const templateKey         = document.getElementById('templateKey');
        const eventTypeSelect     = document.getElementById('eventTypeSelect');
        const senderIdSelect      = document.getElementById('senderIdSelect');
        const messageBody         = document.getElementById('messageBody');
        const charCount           = document.getElementById('charCount');
        const partsCount          = document.getElementById('partsCount');
        const partsInfo           = document.getElementById('partsInfo');
        const encodingBadge       = document.getElementById('encodingBadge');
        const previewBody         = document.getElementById('previewBody');
        const previewSender       = document.getElementById('previewSender');

        // 1. Live Sender ID updates
        if (senderIdSelect && previewSender) {
            senderIdSelect.addEventListener('change', function () {
                const val = this.value.trim();
                previewSender.textContent = val || 'SHOP NOW BD';
            });
        }

        // 2. Sync Key & Event Type when Trigger Rule changes
        if (triggerRuleSelector && templateKey) {
            triggerRuleSelector.addEventListener('change', function () {
                const val = this.value;
                if (val !== 'custom') {
                    templateKey.value = val;
                    const opt = this.options[this.selectedIndex];
                    const cat = opt.getAttribute('data-type');
                    if (cat && eventTypeSelect) {
                        eventTypeSelect.value = cat;
                    }
                }
            });
        }

        // 3. Live Metrics and Instant Mobile Preview Engine
        function updateMetricsAndPreview() {
            if (!messageBody) return;
            const text = messageBody.value;
            const length = text.length;

            if (charCount) charCount.textContent = length;

            const isUnicode = /[^\u0000-\u007F]/.test(text);

            let parts = 1;
            if (isUnicode) {
                if (encodingBadge) {
                    encodingBadge.textContent = 'Unicode';
                    encodingBadge.className = 'badge bg-warning-subtle text-warning-emphasis border border-warning-subtle fw-normal';
                }
                if (length <= 70) {
                    parts = 1;
                    if (partsInfo) partsInfo.textContent = '(70 chars/part)';
                } else {
                    parts = Math.ceil(length / 67);
                    if (partsInfo) partsInfo.textContent = '(67 chars/part)';
                }
            } else {
                if (encodingBadge) {
                    encodingBadge.textContent = 'GSM-7';
                    encodingBadge.className = 'badge bg-light text-secondary border fw-normal';
                }
                if (length <= 160) {
                    parts = 1;
                    if (partsInfo) partsInfo.textContent = '(160 chars/part)';
                } else {
                    parts = Math.ceil(length / 153);
                    if (partsInfo) partsInfo.textContent = '(153 chars/part)';
                }
            }

            if (partsCount) partsCount.textContent = parts;

            if (previewBody) {
                if (length > 0) {
                    previewBody.textContent = text;
                } else {
                    previewBody.textContent = 'Type message content to preview...';
                }
            }
        }

        if (messageBody) {
            messageBody.addEventListener('input', updateMetricsAndPreview);
            messageBody.addEventListener('keyup', updateMetricsAndPreview);
            messageBody.addEventListener('change', updateMetricsAndPreview);
        }

        // 4. Dynamic Variable Tag Inserter
        document.querySelectorAll('.variable-tag').forEach(btn => {
            btn.addEventListener('click', function () {
                if (!messageBody) return;
                const tag = this.getAttribute('data-tag');
                const startPos = messageBody.selectionStart;
                const endPos = messageBody.selectionEnd;
                const currentVal = messageBody.value;

                messageBody.value = currentVal.substring(0, startPos) + tag + currentVal.substring(endPos);
                messageBody.focus();
                messageBody.selectionStart = messageBody.selectionEnd = startPos + tag.length;

                updateMetricsAndPreview();
            });
        });

        // Initial setup
        updateMetricsAndPreview();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTemplateEditEngine);
    } else {
        initTemplateEditEngine();
    }
})();
</script>

<style>
.sms-template-edit {
    --sms-primary: #1572e8;
    --sms-primary-soft: #edf4ff;
    --sms-ink: #1f2937;
    --sms-muted: #6b7280;
    --sms-border: #e5e7eb;
    --sms-surface: #ffffff;
    --sms-soft: #f7f9fc;
    --sms-radius: 10px;
    color: var(--sms-ink);
    min-width: 0;
}

.sms-template-edit .page-title {
    font-size: clamp(1.15rem, 2vw, 1.4rem);
    letter-spacing: -0.3px;
}

.sms-template-edit .system-badge {
    background: #f1f3f5;
    color: #596273;
    border: 1px solid #e1e5eb;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    padding: 6px 9px;
}

.sms-template-edit .action-btn,
.sms-template-edit .primary-action-btn,
.sms-template-edit .cancel-action-btn {
    border-radius: 7px;
    font-weight: 600;
    transition: all .2s ease;
}

.sms-template-edit .template-card {
    background: var(--sms-surface);
    border: 1px solid var(--sms-border) !important;
    border-radius: var(--sms-radius);
    box-shadow: 0 2px 10px rgba(31, 41, 55, .035);
    overflow: hidden;
}

.sms-template-edit .template-card-header {
    background: #fff;
    border-bottom: 1px solid var(--sms-border);
    padding: 16px 20px;
}

.sms-template-edit .template-card-header h6 {
    color: var(--sms-ink);
    font-size: 14px;
}

.sms-template-edit .template-card-header small {
    font-size: 11px;
}

.sms-template-edit .section-icon {
    width: 36px;
    height: 36px;
    flex: 0 0 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--sms-primary);
    background: var(--sms-primary-soft);
    border-radius: 8px;
    font-size: 14px;
}

.sms-template-edit .preview-icon {
    color: #16866b;
    background: #eaf8f3;
}

.sms-template-edit .form-label {
    display: block;
    margin-bottom: 7px;
    color: #374151;
    font-size: 12px;
    font-weight: 600;
}

.sms-template-edit .form-control,
.sms-template-edit .form-select {
    min-height: 38px;
    border: 1px solid #dfe3e9;
    border-radius: 7px;
    color: var(--sms-ink);
    font-size: 13px;
    background-color: #fff;
    box-shadow: none;
    transition: border-color .2s ease, box-shadow .2s ease;
}

.sms-template-edit .form-control:focus,
.sms-template-edit .form-select:focus {
    border-color: var(--sms-primary);
    box-shadow: 0 0 0 3px rgba(21, 114, 232, .10);
}

.sms-template-edit .form-control[readonly] {
    background: #f3f5f8;
    color: #697386;
    cursor: not-allowed;
}

.sms-template-edit textarea.form-control {
    min-height: 150px;
    resize: vertical;
    line-height: 1.7;
    font-size: 13px;
    padding: 12px;
}

.sms-template-edit .section-divider {
    height: 1px;
    background: var(--sms-border);
}

.sms-template-edit .encoding-badge {
    display: inline-flex;
    align-items: center;
    border: 1px solid #dce2e9;
    background: #f3f5f8;
    color: #596273;
    border-radius: 5px;
    padding: 5px 9px;
    font-size: 10px;
    font-weight: 600;
}

.sms-template-edit .variable-panel {
    padding: 13px;
    border: 1px solid #e4e9f0;
    background: var(--sms-soft);
    border-radius: 8px;
}

.sms-template-edit .variable-panel-title {
    font-size: 11px;
    font-weight: 700;
    color: #475467;
}

.sms-template-edit .variable-hint {
    font-size: 10px;
}

.sms-template-edit .btn-xs {
    display: inline-flex;
    align-items: center;
    max-width: 100%;
    padding: 5px 8px;
    border-radius: 5px;
    font-size: 10px;
    line-height: 1.3;
    overflow-wrap: anywhere;
    transition: all .15s ease;
}

.sms-template-edit .variable-tag {
    border-color: #d7dfe9;
    background: #fff;
    color: #526174;
}

.sms-template-edit .variable-tag:hover {
    color: var(--sms-primary);
    border-color: #9ec5fe;
    background: var(--sms-primary-soft);
}

.sms-template-edit .message-metrics {
    padding: 11px 13px;
    border: 1px solid var(--sms-border);
    background: #fafbfc;
    border-radius: 7px;
}

.sms-template-edit .metric-item {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
}

.sms-template-edit .metric-label {
    color: var(--sms-muted);
    font-size: 11px;
}

.sms-template-edit .metric-value {
    color: var(--sms-ink);
    font-size: 12px;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
}

.sms-template-edit .metric-info {
    color: #8a94a3;
    font-size: 10px;
}

.sms-template-edit .form-check-input {
    width: 2.2em;
    height: 1.2em;
    cursor: pointer;
}

.sms-template-edit .form-check-input:checked {
    background-color: var(--sms-primary);
    border-color: var(--sms-primary);
}

.sms-template-edit .primary-action-btn {
    min-height: 40px;
    background: var(--sms-primary);
    border-color: var(--sms-primary);
}

.sms-template-edit .primary-action-btn:hover {
    background: #0e63ce;
    border-color: #0e63ce;
    transform: translateY(-1px);
}

.sms-template-edit .cancel-action-btn {
    color: #667085;
    background: #f3f5f8;
    border: 1px solid #e8ebf0;
}

.sms-template-edit .cancel-action-btn:hover {
    color: #344054;
    background: #e9edf3;
}

.sms-template-edit .preview-area {
    background: #f5f7fa;
    padding: 22px 15px;
    text-align: center;
}

.sms-template-edit .phone-preview {
    width: 100%;
    max-width: 300px;
    min-height: 190px;
    margin: 0 auto;
    padding: 15px;
    background: #fff;
    border: 1px solid #e0e5ec;
    border-radius: 13px;
    box-shadow: 0 4px 14px rgba(31, 41, 55, .06);
    text-align: left;
}

.sms-template-edit .phone-preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
    padding-bottom: 11px;
    margin-bottom: 13px;
    border-bottom: 1px solid #edf0f4;
}

.sms-template-edit .preview-sender {
    color: #263244;
    font-size: 12px;
    font-weight: 700;
    overflow-wrap: anywhere;
}

.sms-template-edit .preview-time {
    color: #98a2b3;
    font-size: 10px;
    white-space: nowrap;
}

.sms-template-edit .phone-preview-body {
    min-height: 100px;
    color: #344054;
    font-size: 12px;
    line-height: 1.7;
    white-space: pre-wrap;
    overflow-wrap: anywhere;
    word-break: break-word;
}

.sms-template-edit .preview-caption {
    color: #8a94a3;
    font-size: 10px;
}

.sms-template-edit .alert {
    border-radius: 8px;
}

@media (max-width: 767.98px) {
    .sms-template-edit {
        padding-top: 18px !important;
    }

    .sms-template-edit .template-card-header {
        padding: 13px 15px;
    }

    .sms-template-edit .card-body {
        padding: 15px !important;
    }

    .sms-template-edit .preview-area {
        padding: 18px 12px;
    }

    .sms-template-edit .phone-preview {
        max-width: 100%;
    }
}

@media (max-width: 420px) {
    .sms-template-edit .page-title {
        font-size: 18px;
    }

    .sms-template-edit .metric-item {
        gap: 4px;
    }

    .sms-template-edit .variable-panel {
        padding: 10px;
    }

    .sms-template-edit .btn-xs {
        font-size: 9px;
        padding: 5px 6px;
    }
}
</style>
@endsection

@section('content')
    @yield('admin_content')
@endsection
