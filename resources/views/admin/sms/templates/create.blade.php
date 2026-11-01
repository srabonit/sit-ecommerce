@extends('layouts.admin')

@section('title', 'New SMS Template')

@section('admin_content')
<div class="container-fluid px-3 px-md-4 py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Create SMS Template</h4>
            <p class="text-muted small mb-0">Add a reusable message template with dynamic variables.</p>
        </div>
        <a href="{{ route('admin.sms.templates.index') }}" class="btn btn-outline-secondary btn-sm px-3">
            <i class="fas fa-arrow-left me-1"></i> Back to Templates
        </a>
    </div>

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show small py-2 mb-4" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i> Please check the form errors below.
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.sms.templates.store') }}" method="POST">
        @csrf
        <div class="row g-4">

            {{-- Left Column: Form Details --}}
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-semibold mb-0 text-dark">Template Information</h6>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-medium small text-dark">Template Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="templateName" class="form-control form-control-sm @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Order Placed Notification" required>
                                @error('name')
                                    <div class="invalid-feedback small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-medium small text-dark">Identifier Key <span class="text-danger">*</span></label>
                                <input type="text" name="key" id="templateKey" class="form-control form-control-sm @error('key') is-invalid @enderror" value="{{ old('key') }}" placeholder="e.g. order_placed_customer" required>
                                <span class="form-text small">Unique programmatic key (lowercase, letters, numbers, underscore).</span>
                                @error('key')
                                    <div class="invalid-feedback small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-medium small text-dark">Category / Event Type <span class="text-danger">*</span></label>
                                <select name="event_type" id="eventTypeSelect" class="form-select form-select-sm @error('event_type') is-invalid @enderror" required>
                                    <option value="order" {{ old('event_type') === 'order' ? 'selected' : '' }}>Order Notifications</option>
                                    <option value="auth" {{ old('event_type') === 'auth' ? 'selected' : '' }}>Auth &amp; OTP</option>
                                    <option value="marketing" {{ old('event_type') === 'marketing' ? 'selected' : '' }}>Marketing &amp; Campaigns</option>
                                    <option value="general" {{ old('event_type') === 'general' ? 'selected' : '' }}>General Alerts</option>
                                </select>
                                @error('event_type')
                                    <div class="invalid-feedback small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-medium small text-dark">Sender ID / Masking</label>
                                <select name="sender_id" class="form-select form-select-sm">
                                    <option value="">Default Gateway Sender ID</option>
                                    @foreach($senderIds as $sid)
                                        <option value="{{ $sid }}" {{ old('sender_id') === $sid ? 'selected' : '' }}>{{ $sid }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Message Body & Dynamic Tags --}}
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-medium small text-dark mb-0">Message Content <span class="text-danger">*</span></label>
                                <span class="badge bg-light text-secondary border fw-normal" id="encodingBadge" style="font-size: 11px;">GSM-7</span>
                            </div>

                            {{-- Variable Buttons --}}
                            <div class="mb-2 p-2 bg-light rounded border">
                                <span class="text-muted d-block small mb-1" style="font-size: 11px;">Click tag to insert:</span>
                                <div class="d-flex flex-wrap gap-1" id="variablePillsContainer">
                                    <button type="button" class="btn btn-outline-secondary btn-xs py-0 px-2 variable-tag" data-tag="{customer_name}">{customer_name}</button>
                                    <button type="button" class="btn btn-outline-secondary btn-xs py-0 px-2 variable-tag" data-tag="{order_number}">{order_number}</button>
                                    <button type="button" class="btn btn-outline-secondary btn-xs py-0 px-2 variable-tag" data-tag="{order_amount}">{order_amount}</button>
                                    <button type="button" class="btn btn-outline-secondary btn-xs py-0 px-2 variable-tag" data-tag="{store_name}">{store_name}</button>
                                    <button type="button" class="btn btn-outline-secondary btn-xs py-0 px-2 variable-tag" data-tag="{otp_code}">{otp_code}</button>
                                    <button type="button" class="btn btn-outline-secondary btn-xs py-0 px-2 variable-tag" data-tag="{track_url}">{track_url}</button>
                                </div>
                            </div>

                            <textarea name="body" id="messageBody" rows="5" class="form-control @error('body') is-invalid @enderror" placeholder="Write your message here... Use {tags} for dynamic values." required>{{ old('body') }}</textarea>
                            @error('body')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror

                            {{-- Real-time Character & Parts Counter --}}
                            <div class="d-flex justify-content-between align-items-center mt-2 small text-muted">
                                <div>
                                    Characters: <span class="fw-bold text-dark" id="charCount">0</span>
                                </div>
                                <div>
                                    Parts: <span class="fw-bold text-dark" id="partsCount">1</span>
                                    <span class="text-muted" style="font-size: 11px;" id="partsInfo">(160 chars/part)</span>
                                </div>
                            </div>
                        </div>

                        {{-- Optional Tags Definition & Description --}}
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-medium small text-dark">Custom Tag Definitions</label>
                                <input type="text" name="variables" class="form-control form-control-sm" value="{{ old('variables') }}" placeholder="customer_name, order_number, store_name">
                                <span class="form-text small">Comma-separated list of placeholder keys.</span>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-medium small text-dark">Description</label>
                                <input type="text" name="description" class="form-control form-control-sm" value="{{ old('description') }}" placeholder="Brief context about when this template sends.">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Preview & Controls --}}
            <div class="col-12 col-lg-4">
                
                {{-- Status & Save Card --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-semibold mb-0 text-dark">Publish Settings</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <label class="form-check-label fw-medium text-dark d-block">Active Status</label>
                                <span class="text-muted small">Enable immediate use.</span>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', '1') === '1' ? 'checked' : '' }}>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-sm py-2">
                                <i class="fas fa-save me-1"></i> Save Template
                            </button>
                            <a href="{{ route('admin.sms.templates.index') }}" class="btn btn-light btn-sm py-2 text-muted">Cancel</a>
                        </div>
                    </div>
                </div>

                {{-- Clean Live Preview Card --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-semibold mb-0 text-dark">Live SMS Preview</h6>
                    </div>
                    <div class="card-body p-3 text-center bg-light">
                        <div class="mx-auto bg-white border rounded-4 p-3 shadow-sm text-start" style="max-width: 280px; min-height: 180px;">
                            <div class="border-bottom pb-2 mb-2 d-flex justify-content-between align-items-center">
                                <span class="fw-bold small text-dark" id="previewSender">SHOP NOW BD</span>
                                <span class="text-muted" style="font-size: 10px;">Just now</span>
                            </div>
                            <div class="small text-dark font-monospace" id="previewBody" style="white-space: pre-wrap; word-break: break-word; font-size: 12px;">
                                Type message content to preview...
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </form>
</div>
@endsection

@section('content')
    @yield('admin_content')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const templateName = document.getElementById('templateName');
    const templateKey = document.getElementById('templateKey');
    const messageBody = document.getElementById('messageBody');
    const charCount = document.getElementById('charCount');
    const partsCount = document.getElementById('partsCount');
    const partsInfo = document.getElementById('partsInfo');
    const encodingBadge = document.getElementById('encodingBadge');
    const previewBody = document.getElementById('previewBody');

    let keyManuallyChanged = false;
    if (templateKey) {
        templateKey.addEventListener('input', function () {
            keyManuallyChanged = true;
        });
    }

    // Auto-generate key from name if untouched
    if (templateName && templateKey) {
        templateName.addEventListener('input', function () {
            if (!keyManuallyChanged && (templateKey.value === '' || templateKey.dataset.autoGenerated === 'true')) {
                templateKey.value = this.value
                    .toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9]+/g, '_')
                    .replace(/^_+|_+$/g, '');
                templateKey.dataset.autoGenerated = 'true';
            }
        });
    }

    // Live Metrics & Preview Engine
    function updateMetrics() {
        if (!messageBody) return;
        const text = messageBody.value;
        const length = text.length;
        if (charCount) charCount.textContent = length;

        // Check Unicode (Any non-ASCII / Bangla characters)
        const isUnicode = /[^\u0000-\u007F]/.test(text);

        let parts = 1;
        if (isUnicode) {
            if (encodingBadge) {
                encodingBadge.textContent = 'Unicode (Bangla)';
                encodingBadge.className = 'badge bg-warning-subtle text-warning-emphasis border border-warning-subtle fw-normal';
            }
            if (length <= 70) {
                parts = length > 0 ? 1 : 1;
                if (partsInfo) partsInfo.textContent = '(70 chars/part)';
            } else {
                parts = Math.ceil(length / 67);
                if (partsInfo) partsInfo.textContent = '(67 chars/part for multi-part)';
            }
        } else {
            if (encodingBadge) {
                encodingBadge.textContent = 'GSM-7 (Standard)';
                encodingBadge.className = 'badge bg-light text-secondary border fw-normal';
            }
            if (length <= 160) {
                parts = length > 0 ? 1 : 1;
                if (partsInfo) partsInfo.textContent = '(160 chars/part)';
            } else {
                parts = Math.ceil(length / 153);
                if (partsInfo) partsInfo.textContent = '(153 chars/part for multi-part)';
            }
        }

        if (partsCount) partsCount.textContent = parts;

        // Update mobile live preview
        if (previewBody) {
            previewBody.textContent = length > 0 ? text : 'Type message content to preview...';
        }
    }

    if (messageBody) {
        messageBody.addEventListener('input', updateMetrics);
    }

    // Variable Tag Inserter
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

            updateMetrics();
        });
    });

    updateMetrics();
});
</script>
<style>
.btn-xs {
    font-size: 11px;
    padding: 2px 6px;
    border-radius: 4px;
}
</style>
@endpush