@extends('layouts.admin')

@section('title', 'Edit SMS Template: ' . $template->name)

@section('admin_content')
<div class="container-fluid px-3 px-md-4 py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="fw-bold mb-0">Edit SMS Template</h4>
                @if(!empty($template->is_system))
                    <span class="badge bg-secondary-subtle text-secondary border px-2 py-1" style="font-size: 11px;">
                        <i class="fas fa-lock me-1"></i> System Critical
                    </span>
                @endif
            </div>
            <p class="text-muted small mb-0">Modify template content, dynamic parameters, and delivery triggers.</p>
        </div>
        <a href="{{ route('admin.sms.templates.index') }}" class="btn btn-outline-secondary btn-sm px-3">
            <i class="fas fa-arrow-left me-1"></i> Back to Templates
        </a>
    </div>

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show small py-2 mb-4" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i> Please check form validation errors below.
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show small py-2 mb-4" role="alert">
            <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.sms.templates.update', $template->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">

            {{-- Left Column: Form Details --}}
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-semibold mb-0 text-dark">Template Parameters</h6>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-medium small text-dark">Template Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="templateName" class="form-control form-control-sm @error('name') is-invalid @enderror" value="{{ old('name', $template->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-medium small text-dark">Identifier Key <span class="text-danger">*</span></label>
                                <input type="text" name="key" id="templateKey" class="form-control form-control-sm @error('key') is-invalid @enderror" value="{{ old('key', $template->key) }}" {{ !empty($template->is_system) ? 'readonly' : 'required' }}>
                                @if(!empty($template->is_system))
                                    <span class="form-text text-warning small" style="font-size: 11px;">
                                        <i class="fas fa-info-circle me-1"></i> System template keys are locked to protect automated workflows.
                                    </span>
                                @else
                                    <span class="form-text small">Unique programmatic key (lowercase, letters, numbers, underscore).</span>
                                @endif
                                @error('key')
                                    <div class="invalid-feedback small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-medium small text-dark">Category / Event Type <span class="text-danger">*</span></label>
                                <select name="event_type" class="form-select form-select-sm @error('event_type') is-invalid @enderror" required>
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
                                <label class="form-label fw-medium small text-dark">Sender ID / Masking</label>
                                <select name="sender_id" class="form-select form-select-sm">
                                    <option value="">Default Gateway Sender ID</option>
                                    @foreach($senderIds as $sid)
                                        <option value="{{ $sid }}" {{ old('sender_id', $template->sender_id) === $sid ? 'selected' : '' }}>{{ $sid }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Message Body & Dynamic Tags --}}
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-medium small text-dark mb-0">Message Content <span class="text-danger">*</span></label>
                                <span class="badge bg-light text-secondary border fw-normal" id="encodingBadge" style="font-size: 11px;">
                                    {{ !empty($template->is_unicode) ? 'Unicode (Bangla)' : 'GSM-7' }}
                                </span>
                            </div>

                            {{-- Variable Buttons --}}
                            @php
                                $vars = is_array($template->variables) 
                                    ? $template->variables 
                                    : (is_string($template->variables) ? (json_decode($template->variables, true) ?? array_filter(array_map('trim', explode(',', $template->variables)))) : []);
                                
                                $standardVars = ['customer_name', 'order_number', 'order_amount', 'store_name', 'otp_code', 'track_url'];
                                $allPillVars = array_unique(array_merge($standardVars, (array) $vars));
                            @endphp

                            <div class="mb-2 p-2 bg-light rounded border">
                                <span class="text-muted d-block small mb-1" style="font-size: 11px;">Click tag to insert:</span>
                                <div class="d-flex flex-wrap gap-1" id="variablePillsContainer">
                                    @foreach($allPillVars as $vTag)
                                        <button type="button" class="btn btn-outline-secondary btn-xs py-0 px-2 variable-tag" data-tag="{{ '{' . $vTag . '}' }}">
                                            {{ '{' . $vTag . '}' }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <textarea name="body" id="messageBody" rows="5" class="form-control @error('body') is-invalid @enderror" placeholder="Write message template here..." required>{{ old('body', $template->body) }}</textarea>
                            @error('body')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror

                            {{-- Real-time Character & Parts Counter --}}
                            <div class="d-flex justify-content-between align-items-center mt-2 small text-muted">
                                <div>
                                    Characters: <span class="fw-bold text-dark" id="charCount">0</span>
                                </div>
                                <div>
                                    Parts: <span class="fw-bold text-dark" id="partsCount">{{ $template->estimated_parts ?? 1 }}</span>
                                    <span class="text-muted" style="font-size: 11px;" id="partsInfo">(160 chars/part)</span>
                                </div>
                            </div>
                        </div>

                        {{-- Optional Tags Definition & Description --}}
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-medium small text-dark">Custom Tag Definitions</label>
                                @php
                                    $variablesString = is_array($template->variables) ? implode(', ', $template->variables) : $template->variables;
                                @endphp
                                <input type="text" name="variables" class="form-control form-control-sm" value="{{ old('variables', $variablesString) }}" placeholder="customer_name, order_number, store_name" {{ !empty($template->is_system) ? 'readonly' : '' }}>
                                <span class="form-text small">Comma-separated list of placeholder keys.</span>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-medium small text-dark">Description</label>
                                <input type="text" name="description" class="form-control form-control-sm" value="{{ old('description', $template->description) }}" placeholder="Brief context about when this template sends.">
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
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-sm py-2">
                                <i class="fas fa-save me-1"></i> Update Template
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
                                <span class="fw-bold small text-dark" id="previewSender">{{ $template->sender_id ?: 'SHOP NOW BD' }}</span>
                                <span class="text-muted" style="font-size: 10px;">Preview</span>
                            </div>
                            <div class="small text-dark font-monospace" id="previewBody" style="white-space: pre-wrap; word-break: break-word; font-size: 12px;">
                                {{ $template->body }}
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
    const messageBody = document.getElementById('messageBody');
    const charCount = document.getElementById('charCount');
    const partsCount = document.getElementById('partsCount');
    const partsInfo = document.getElementById('partsInfo');
    const encodingBadge = document.getElementById('encodingBadge');
    const previewBody = document.getElementById('previewBody');

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

    // Initial calculation on load
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