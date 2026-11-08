@extends('layouts.admin')

@section('title', 'New SMS Template')

@section('admin_content')
<div class="container-fluid px-3 px-md-4 py-4 sms-create-page">

    {{-- Page Header --}}
    <div class="sms-create-header d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h4 class="sms-page-title mb-1">Create SMS Template</h4>
            <p class="sms-page-subtitle mb-0">
                Create SMS templates for orders, customers & alerts.
            </p>
        </div>

        <a href="{{ route('admin.sms.templates.index') }}"
           class="btn btn-outline-secondary sms-back-btn">
            <i class="fas fa-arrow-left me-2"></i>
            Back to Templates
        </a>
    </div>

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show sms-alert mb-3" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Please check the form errors below.</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show sms-alert mb-3" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.sms.templates.store') }}" method="POST">
        @csrf

        <div class="row g-3 g-xl-4">

            {{-- Left Column: Template Configuration --}}
            <div class="col-12 col-lg-8">

                <div class="card sms-create-card mb-3">

                    <div class="card-header sms-create-card-header">
                        <div class="d-flex align-items-center gap-2">
                            <div class="sms-card-icon">
                                <i class="fas fa-sliders-h"></i>
                            </div>
                            <div>
                                <h6 class="sms-card-title mb-0">Template Configuration</h6>
                                <p class="sms-card-subtitle mb-0">
                                    Set up SMS template details & content.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="card-body sms-create-card-body">

                        {{-- Trigger Rule Selector --}}
                        <div class="sms-form-section mb-4">

                            <label class="form-label sms-form-label">
                                Trigger Rule
                                <span class="text-danger">*</span>
                            </label>

                            <select id="triggerRuleSelector"
                                    class="form-select sms-form-control">

                                <option value="">-- Choose Trigger Rule --</option>

                                <optgroup label="Order Lifecycle Rules">
                                    <option value="order_pending" data-name="Order Pending Notification" data-type="order">Order Pending</option>
                                    <option value="order_placed_customer" data-name="Order Placed Confirmation" data-type="order">Order Placed</option>
                                    <option value="order_received" data-name="Order Received Notification" data-type="order">Order Received</option>
                                    <option value="order_confirmed" data-name="Order Confirmed Notification" data-type="order">Order Confirmed</option>
                                    <option value="order_processing" data-name="Order Processing Notification" data-type="order">Order Processing / Packed</option>
                                    <option value="order_shipped" data-name="Order Shipped Notification" data-type="order">Order Shipped</option>
                                    <option value="order_attempt_to_deliver" data-name="Out for Delivery Notification" data-type="order">Out for Delivery</option>
                                    <option value="order_delivered" data-name="Order Delivered Notification" data-type="order">Order Delivered</option>
                                    <option value="order_exchange" data-name="Order Exchange Notification" data-type="order">Order Exchange</option>
                                    <option value="order_returned" data-name="Order Returned Notification" data-type="order">Order Returned</option>
                                    <option value="order_cancelled" data-name="Order Cancelled Notification" data-type="order">Order Cancelled</option>
                                </optgroup>

                                <optgroup label="System &amp; Security Triggers">
                                    <option value="admin_new_order_alert" data-name="Admin New Order Alert" data-type="general">Admin New Order Alert</option>
                                    <option value="admin_low_balance_alert" data-name="Admin Low Balance Alert" data-type="general">Admin Low Balance Alert</option>
                                    <option value="password_reset_otp" data-name="Password Reset OTP" data-type="auth">Password Reset OTP</option>
                                    <option value="custom_login_otp" data-name="Login Verification OTP" data-type="auth">Login Verification OTP</option>
                                    <option value="welcome_sms" data-name="Customer Welcome Message" data-type="general">Customer Welcome</option>
                                </optgroup>

                                <optgroup label="Custom Configuration">
                                    <option value="custom" data-name="" data-type="marketing">Custom / Marketing Campaign</option>
                                </optgroup>

                            </select>

                            <div class="sms-form-hint">
                                <i class="fas fa-info-circle me-1"></i>
                                Select a trigger to auto-fill template details.
                            </div>

                        </div>

                        {{-- Template Name & Key --}}
                        <div class="row g-3 mb-4">

                            <div class="col-12 col-md-6">
                                <label class="form-label sms-form-label">
                                    Template Name
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="name"
                                       id="templateName"
                                       class="form-control sms-form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}"
                                       placeholder="Order Shipped Notification"
                                       required>

                                @error('name')
                                    <div class="invalid-feedback small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label sms-form-label">
                                    System Identifier Key
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="key"
                                       id="templateKey"
                                       class="form-control sms-form-control font-monospace @error('key') is-invalid @enderror"
                                       value="{{ old('key') }}"
                                       placeholder="order_shipped"
                                       required>

                                @error('key')
                                    <div class="invalid-feedback small">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>

                        {{-- Category & Sender --}}
                        <div class="row g-3 mb-4">

                            <div class="col-12 col-md-6">
                                <label class="form-label sms-form-label">
                                    Category / Event Type
                                    <span class="text-danger">*</span>
                                </label>

                                <select name="event_type"
                                        id="eventTypeSelect"
                                        class="form-select sms-form-control @error('event_type') is-invalid @enderror"
                                        required>

                                    <option value="order" {{ old('event_type') === 'order' ? 'selected' : '' }}>
                                        Order Notifications
                                    </option>

                                    <option value="auth" {{ old('event_type') === 'auth' ? 'selected' : '' }}>
                                        Auth &amp; OTP
                                    </option>

                                    <option value="marketing" {{ old('event_type') === 'marketing' ? 'selected' : '' }}>
                                        Marketing &amp; Campaigns
                                    </option>

                                    <option value="general" {{ old('event_type') === 'general' ? 'selected' : '' }}>
                                        General Alerts
                                    </option>

                                </select>

                                @error('event_type')
                                    <div class="invalid-feedback small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label sms-form-label">
                                    Sender ID / Masking
                                </label>

                                <select name="sender_id"
                                        id="senderIdSelect"
                                        class="form-select sms-form-control">

                                    <option value="">Default Gateway Sender ID</option>

                                    @foreach($senderIds as $sid)
                                        <option value="{{ $sid }}" {{ old('sender_id') === $sid ? 'selected' : '' }}>
                                            {{ $sid }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>

                        </div>

                        <div class="sms-section-divider"></div>

                        {{-- Message Content --}}
                        <div class="sms-message-section">

                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                <label class="form-label sms-form-label mb-0">
                                    Message Content
                                    <span class="text-danger">*</span>
                                </label>

                                <span class="badge sms-encoding-badge"
                                      id="encodingBadge">
                                    GSM-7
                                </span>
                            </div>

                            <p class="sms-message-hint mb-3">
                                Write your SMS & add dynamic variables.
                            </p>

                            {{-- Dynamic Variable Tags --}}
                            <div class="sms-variable-box mb-3">

                                <div class="sms-variable-heading">
                                    <i class="fas fa-code me-1"></i>
                                    Available Variables
                                </div>

                                <div class="d-flex flex-wrap gap-2"
                                     id="variablePillsContainer">

                                    <button type="button" class="btn variable-tag" data-tag="{customer_name}">{customer_name}</button>
                                    <button type="button" class="btn variable-tag" data-tag="{invoice_no}">{invoice_no}</button>
                                    <button type="button" class="btn variable-tag" data-tag="{order_total}">{order_total}</button>
                                    <button type="button" class="btn variable-tag" data-tag="{order_status}">{order_status}</button>
                                    <button type="button" class="btn variable-tag" data-tag="{courier_name}">{courier_name}</button>
                                    <button type="button" class="btn variable-tag" data-tag="{tracking_code}">{tracking_code}</button>
                                    <button type="button" class="btn variable-tag" data-tag="{store_name}">{store_name}</button>
                                    <button type="button" class="btn variable-tag" data-tag="{website_url}">{website_url}</button>
                                    <button type="button" class="btn variable-tag" data-tag="{otp_code}">{otp_code}</button>

                                </div>

                                <div class="sms-variable-footer">
                                    <i class="fas fa-mouse-pointer me-1"></i>
                                    Click a variable to insert it into your message.
                                </div>

                            </div>

                            {{-- Message Textarea --}}
                            <textarea name="body"
                                      id="messageBody"
                                      rows="6"
                                      class="form-control sms-message-textarea font-monospace @error('body') is-invalid @enderror"
                                      placeholder="Write message template here..."
                                      required>{{ old('body') }}</textarea>

                            @error('body')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror

                            {{-- Character & Parts Counter --}}
                            <div class="sms-message-metrics">

                                <div class="sms-metric-item">
                                    <span class="sms-metric-label">Characters</span>
                                    <span class="sms-metric-value" id="charCount">0</span>
                                </div>

                                <div class="sms-metric-divider"></div>

                                <div class="sms-metric-item">
                                    <span class="sms-metric-label">SMS Parts</span>
                                    <span class="sms-metric-value" id="partsCount">1</span>
                                    <span class="sms-metric-info" id="partsInfo">(160 chars/part)</span>
                                </div>

                            </div>

                        </div>

                    </div>
                </div>

            </div>

            {{-- Right Column --}}
            <div class="col-12 col-lg-4">

                {{-- Publish Settings --}}
                <div class="card sms-create-card mb-3">

                    <div class="card-header sms-create-card-header">
                        <div class="d-flex align-items-center gap-2">
                            <div class="sms-card-icon">
                                <i class="fas fa-paper-plane"></i>
                            </div>
                            <div>
                                <h6 class="sms-card-title mb-0">Publish Settings</h6>
                                <p class="sms-card-subtitle mb-0">
                                    Control template availability.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="card-body sms-publish-body">

                        <div class="sms-status-setting">
                            <div>
                                <div class="sms-setting-title">Active Status</div>
                                <div class="sms-setting-description">
                                    Enable this template for use.
                                </div>
                            </div>

                            <div class="form-check form-switch sms-publish-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="is_active"
                                       value="1"
                                       {{ old('is_active', '1') === '1' ? 'checked' : '' }}>
                            </div>
                        </div>

                        <div class="sms-publish-divider"></div>

                        <div class="d-grid gap-2">

                            <button type="submit" class="btn btn-primary sms-save-btn">
                                <i class="fas fa-save me-2"></i>
                                Save Template
                            </button>

                            <a href="{{ route('admin.sms.templates.index') }}"
                               class="btn btn-light sms-cancel-btn">
                                Cancel
                            </a>

                        </div>

                    </div>
                </div>

                {{-- Live SMS Preview --}}
                <div class="card sms-create-card sms-live-preview-card">

                    <div class="card-header sms-create-card-header">
                        <div class="d-flex align-items-center gap-2">
                            <div class="sms-card-icon sms-preview-card-icon">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <div>
                                <h6 class="sms-card-title mb-0">Live SMS Preview</h6>
                                <p class="sms-card-subtitle mb-0">
                                    Preview your message before saving.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="card-body sms-live-preview-body">

                        <div class="sms-phone-preview">

                            <div class="sms-phone-header">
                                <div class="sms-phone-avatar">
                                    <i class="fas fa-comment-alt"></i>
                                </div>

                                <div class="sms-phone-sender-info">
                                    <div class="sms-phone-sender" id="previewSender">
                                        SHOP NOW BD
                                    </div>
                                    <div class="sms-phone-time">
                                        Just now
                                    </div>
                                </div>

                                <div class="sms-phone-menu">
                                    <i class="fas fa-ellipsis-v"></i>
                                </div>
                            </div>

                            <div class="sms-phone-message-area">
                                <div class="sms-phone-message" id="previewBody">
                                    Type message content to preview...
                                </div>
                            </div>

                            <div class="sms-phone-footer">
                                <i class="fas fa-lock me-1"></i>
                                SMS Message Preview
                            </div>

                        </div>

                        <div class="sms-preview-note">
                            <i class="fas fa-info-circle me-1"></i>
                            This is a visual preview. Actual SMS appearance may vary by device.
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </form>
</div>

{{-- Direct In-Content JavaScript Engine --}}
<script>
(function() {
    function initTemplateCreateEngine() {
        const triggerRuleSelector = document.getElementById('triggerRuleSelector');
        const templateName        = document.getElementById('templateName');
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

        // 1. Sender ID update in live preview
        if (senderIdSelect && previewSender) {
            senderIdSelect.addEventListener('change', function () {
                const val = this.value.trim();
                previewSender.textContent = val || 'SHOP NOW BD';
            });
        }

        // 2. Trigger Rule selection auto-fills key, name, and event type
        if (triggerRuleSelector) {
            triggerRuleSelector.addEventListener('change', function () {
                const val = this.value;
                if (val && val !== 'custom') {
                    const opt = this.options[this.selectedIndex];
                    const ruleName = opt.getAttribute('data-name');
                    const ruleType = opt.getAttribute('data-type');

                    if (templateKey) templateKey.value = val;
                    if (templateName && (templateName.value === '' || templateName.dataset.autoSet === 'true')) {
                        templateName.value = ruleName;
                        templateName.dataset.autoSet = 'true';
                    }
                    if (ruleType && eventTypeSelect) {
                        eventTypeSelect.value = ruleType;
                    }
                }
            });
        }

        if (templateName) {
            templateName.addEventListener('input', function () {
                this.dataset.autoSet = 'false';
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
                previewBody.textContent = length > 0 ? text : 'Type message content to preview...';
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

        updateMetricsAndPreview();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTemplateCreateEngine);
    } else {
        initTemplateCreateEngine();
    }
})();
</script>

<style>
/* =========================================================
   SMS CREATE TEMPLATE
   Kaiadmin Lite v1.2.0 Classic / White Classic
   Page-scoped CSS
========================================================= */

.sms-create-page {
    --sms-primary: var(--bs-primary, #1572e8);
    --sms-heading: #2a2f5b;
    --sms-text: #59616a;
    --sms-muted: #8d9498;
    --sms-border: #ebedf2;
    --sms-surface: #ffffff;
    --sms-soft-bg: #f8f9fc;
    --sms-radius: 8px;
    color: var(--sms-text);
    min-width: 0;
}

/* Page Header */
.sms-create-page .sms-create-header {
    min-width: 0;
}

.sms-create-page .sms-page-title {
    color: var(--sms-heading);
    font-size: clamp(19px, 2vw, 22px);
    font-weight: 700;
    letter-spacing: -0.35px;
    line-height: 1.35;
}

.sms-create-page .sms-page-subtitle {
    color: var(--sms-muted);
    font-size: 13px;
    line-height: 1.6;
}

.sms-create-page .sms-back-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 37px;
    padding: 7px 14px;
    border-color: #e2e5eb;
    border-radius: 6px;
    color: #59616a;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
}

.sms-create-page .sms-back-btn:hover {
    border-color: #cbdcff;
    background: #edf4ff;
    color: var(--sms-primary);
}

/* Alerts */
.sms-create-page .sms-alert {
    padding: 12px 15px;
    border-radius: 6px;
    font-size: 12px;
    line-height: 1.6;
}

.sms-create-page .sms-alert .btn-close {
    font-size: 10px;
}

/* Cards */
.sms-create-page .sms-create-card {
    min-width: 0;
    overflow: hidden;
    border: 1px solid var(--sms-border);
    border-radius: var(--sms-radius);
    background: var(--sms-surface);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.035);
}

.sms-create-page .sms-create-card-header {
    display: flex;
    align-items: center;
    min-height: 67px;
    padding: 13px 18px;
    border-bottom: 1px solid var(--sms-border);
    background: #fff;
}

.sms-create-page .sms-card-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 34px;
    width: 34px;
    height: 34px;
    border-radius: 7px;
    background: #edf4ff;
    color: var(--sms-primary);
    font-size: 14px;
}

.sms-create-page .sms-card-title {
    color: var(--sms-heading);
    font-size: 14px;
    font-weight: 700;
    line-height: 1.45;
}

.sms-create-page .sms-card-subtitle {
    margin-top: 2px;
    color: var(--sms-muted);
    font-size: 11px;
    line-height: 1.5;
}

.sms-create-page .sms-create-card-body {
    padding: 22px;
}

/* Form Labels & Controls */
.sms-create-page .sms-form-label {
    display: block;
    margin-bottom: 7px;
    color: #424957;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.5;
}

.sms-create-page .sms-form-control {
    width: 100%;
    min-height: 39px;
    padding: 8px 11px;
    border: 1px solid #e5e8ed;
    border-radius: 6px;
    background-color: #fff;
    color: #424957;
    font-size: 12px;
    box-shadow: none;
    transition: border-color 0.18s ease, box-shadow 0.18s ease;
}

.sms-create-page .sms-form-control::placeholder {
    color: #a3a9b0;
    opacity: 1;
}

.sms-create-page .sms-form-control:focus {
    border-color: rgba(21, 114, 232, 0.6);
    box-shadow: 0 0 0 3px rgba(21, 114, 232, 0.08);
}

.sms-create-page .sms-form-control.is-invalid {
    border-color: #dc3545;
}

.sms-create-page .invalid-feedback {
    margin-top: 5px;
    font-size: 11px;
}

.sms-create-page .sms-form-hint {
    margin-top: 7px;
    color: #9299a2;
    font-size: 11px;
    line-height: 1.5;
}

/* Sections */
.sms-create-page .sms-form-section {
    min-width: 0;
}

.sms-create-page .sms-section-divider {
    height: 1px;
    margin: 5px 0 22px;
    background: #edf0f3;
}

/* Message Section */
.sms-create-page .sms-message-section {
    min-width: 0;
}

.sms-create-page .sms-message-hint {
    color: #9299a2;
    font-size: 11px;
    line-height: 1.6;
}

.sms-create-page .sms-encoding-badge {
    padding: 5px 9px;
    border: 1px solid #e3e7ed;
    border-radius: 5px;
    background: #f5f6f8;
    color: #68717a;
    font-size: 10px;
    font-weight: 600;
}

/* Variable Tags */
.sms-create-page .sms-variable-box {
    padding: 13px;
    border: 1px solid #e8ebf0;
    border-radius: 7px;
    background: #f8f9fc;
}

.sms-create-page .sms-variable-heading {
    margin-bottom: 10px;
    color: #59616a;
    font-size: 11px;
    font-weight: 700;
}

.sms-create-page .sms-variable-heading i {
    color: var(--sms-primary);
}

.sms-create-page .variable-tag {
    min-height: 27px;
    padding: 4px 8px;
    border: 1px solid #dfe5ee;
    border-radius: 5px;
    background: #fff;
    color: #526176;
    font-family: monospace;
    font-size: 10px;
    font-weight: 500;
    line-height: 1.3;
    transition: all 0.18s ease;
}

.sms-create-page .variable-tag:hover {
    border-color: #b9d2ff;
    background: #edf4ff;
    color: var(--sms-primary);
}

.sms-create-page .variable-tag:focus-visible {
    outline: 2px solid rgba(21, 114, 232, 0.35);
    outline-offset: 2px;
}

.sms-create-page .sms-variable-footer {
    margin-top: 11px;
    padding-top: 9px;
    border-top: 1px solid #e9edf3;
    color: #9aa1aa;
    font-size: 10px;
    line-height: 1.5;
}

/* Message Textarea */
.sms-create-page .sms-message-textarea {
    display: block;
    width: 100%;
    min-height: 145px;
    padding: 13px;
    border: 1px solid #e3e7ed;
    border-radius: 6px;
    background: #fff;
    color: #424957;
    font-size: 12px;
    line-height: 1.75;
    resize: vertical;
    box-shadow: none;
}

.sms-create-page .sms-message-textarea::placeholder {
    color: #a3a9b0;
    opacity: 1;
}

.sms-create-page .sms-message-textarea:focus {
    border-color: rgba(21, 114, 232, 0.6);
    box-shadow: 0 0 0 3px rgba(21, 114, 232, 0.08);
}

/* Character Metrics */
.sms-create-page .sms-message-metrics {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    margin-top: 13px;
    padding: 11px 13px;
    border: 1px solid #edf0f3;
    border-radius: 6px;
    background: #fafbfc;
}

.sms-create-page .sms-metric-item {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
}

.sms-create-page .sms-metric-label {
    color: #8d9498;
    font-size: 11px;
}

.sms-create-page .sms-metric-value {
    color: #424957;
    font-size: 12px;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
}

.sms-create-page .sms-metric-info {
    color: #9ba2aa;
    font-size: 10px;
}

.sms-create-page .sms-metric-divider {
    width: 1px;
    height: 17px;
    background: #e2e6eb;
}

/* Publish Settings */
.sms-create-page .sms-publish-body {
    padding: 18px;
}

.sms-create-page .sms-status-setting {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.sms-create-page .sms-setting-title {
    color: #424957;
    font-size: 12px;
    font-weight: 600;
}

.sms-create-page .sms-setting-description {
    margin-top: 3px;
    color: #9ba1a8;
    font-size: 10px;
    line-height: 1.5;
}

.sms-create-page .sms-publish-switch {
    min-height: 20px;
    padding-left: 0;
    margin: 0;
}

.sms-create-page .sms-publish-switch .form-check-input {
    float: none;
    width: 36px;
    height: 19px;
    margin: 0;
    border-color: #d5dbe2;
    background-color: #e9ecef;
    cursor: pointer;
    box-shadow: none;
}

.sms-create-page .sms-publish-switch .form-check-input:checked {
    border-color: var(--sms-primary);
    background-color: var(--sms-primary);
}

.sms-create-page .sms-publish-switch .form-check-input:focus {
    box-shadow: 0 0 0 3px rgba(21, 114, 232, 0.1);
}

.sms-create-page .sms-publish-divider {
    height: 1px;
    margin: 17px 0;
    background: #edf0f3;
}

.sms-create-page .sms-save-btn {
    min-height: 40px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    box-shadow: 0 2px 5px rgba(21, 114, 232, 0.12);
}

.sms-create-page .sms-save-btn:hover {
    box-shadow: 0 4px 10px rgba(21, 114, 232, 0.18);
}

.sms-create-page .sms-cancel-btn {
    min-height: 38px;
    border: 1px solid #e7e9ee;
    border-radius: 6px;
    background: #f8f9fc;
    color: #68717a;
    font-size: 12px;
    font-weight: 600;
}

.sms-create-page .sms-cancel-btn:hover {
    background: #eef0f4;
    color: #424957;
}

/* Live Preview */
.sms-create-page .sms-preview-card-icon {
    background: #f0efff;
    color: #6861ce;
}

.sms-create-page .sms-live-preview-body {
    padding: 20px 16px;
    background: #f8f9fc;
}

.sms-create-page .sms-phone-preview {
    width: 100%;
    max-width: 300px;
    min-height: 230px;
    margin: 0 auto;
    overflow: hidden;
    border: 1px solid #e6e9ef;
    border-radius: 11px;
    background: #fff;
    box-shadow: 0 3px 12px rgba(30, 40, 70, 0.055);
}

.sms-create-page .sms-phone-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 13px;
    border-bottom: 1px solid #edf0f3;
    background: #fff;
}

.sms-create-page .sms-phone-avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 32px;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #edf4ff;
    color: var(--sms-primary);
    font-size: 12px;
}

.sms-create-page .sms-phone-sender-info {
    flex: 1;
    min-width: 0;
}

.sms-create-page .sms-phone-sender {
    overflow: hidden;
    color: #2a2f5b;
    font-size: 11px;
    font-weight: 700;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.sms-create-page .sms-phone-time {
    margin-top: 2px;
    color: #a0a6ad;
    font-size: 9px;
}

.sms-create-page .sms-phone-menu {
    color: #a1a7af;
    font-size: 13px;
}

.sms-create-page .sms-phone-message-area {
    min-height: 135px;
    padding: 15px 12px;
    background: #fff;
}

.sms-create-page .sms-phone-message {
    width: fit-content;
    max-width: 100%;
    padding: 11px 13px;
    border: 1px solid #e9edf3;
    border-radius: 4px 12px 12px 12px;
    background: #f1f3f7;
    color: #424957;
    font-family: monospace;
    font-size: 11px;
    line-height: 1.7;
    white-space: pre-wrap;
    word-break: break-word;
}

.sms-create-page .sms-phone-footer {
    padding: 9px 12px;
    border-top: 1px solid #edf0f3;
    background: #fafbfc;
    color: #a0a6ad;
    font-size: 9px;
    text-align: center;
}

.sms-create-page .sms-phone-footer i {
    font-size: 9px;
}

.sms-create-page .sms-preview-note {
    max-width: 300px;
    margin: 13px auto 0;
    color: #9ba2aa;
    font-size: 10px;
    line-height: 1.6;
    text-align: center;
}

/* Responsive */
@media (max-width: 991.98px) {
    .sms-create-page .sms-create-card-body {
        padding: 19px;
    }

    .sms-create-page .sms-live-preview-body {
        padding: 18px;
    }

    .sms-create-page .sms-phone-preview {
        max-width: 100%;
    }
}

@media (max-width: 767.98px) {
    .sms-create-page {
        padding-top: 18px !important;
        padding-bottom: 22px !important;
    }

    .sms-create-page .sms-create-header {
        margin-bottom: 20px !important;
    }

    .sms-create-page .sms-page-title {
        font-size: 19px;
    }

    .sms-create-page .sms-page-subtitle {
        font-size: 12px;
    }

    .sms-create-page .sms-back-btn {
        min-height: 36px;
    }

    .sms-create-page .sms-create-card-header {
        min-height: 62px;
        padding: 12px 15px;
    }

    .sms-create-page .sms-create-card-body {
        padding: 16px;
    }

    .sms-create-page .sms-publish-body {
        padding: 16px;
    }

    .sms-create-page .sms-variable-box {
        padding: 11px;
    }

    .sms-create-page .sms-message-textarea {
        min-height: 130px;
    }
}

@media (max-width: 575.98px) {
    .sms-create-page .sms-create-header {
        align-items: stretch !important;
    }

    .sms-create-page .sms-back-btn {
        width: 100%;
    }

    .sms-create-page .sms-card-title {
        font-size: 13px;
    }

    .sms-create-page .sms-card-subtitle {
        font-size: 10px;
    }

    .sms-create-page .sms-create-card-body {
        padding: 14px;
    }

    .sms-create-page .sms-variable-box {
        padding: 10px;
    }

    .sms-create-page .variable-tag {
        font-size: 9px;
        padding: 4px 6px;
    }

    .sms-create-page .sms-message-metrics {
        gap: 10px;
        padding: 10px;
    }

    .sms-create-page .sms-metric-label {
        font-size: 10px;
    }

    .sms-create-page .sms-metric-info {
        font-size: 9px;
    }

    .sms-create-page .sms-phone-preview {
        max-width: 100%;
    }
}

@media (max-width: 360px) {
    .sms-create-page .sms-page-title {
        font-size: 18px;
    }

    .sms-create-page .sms-create-card-body {
        padding: 12px;
    }

    .sms-create-page .sms-variable-box {
        padding: 8px;
    }

    .sms-create-page .sms-message-metrics {
        gap: 8px;
    }

    .sms-create-page .sms-metric-divider {
        display: none;
    }
}
</style>
@endsection

@section('content')
    @yield('admin_content')
@endsection