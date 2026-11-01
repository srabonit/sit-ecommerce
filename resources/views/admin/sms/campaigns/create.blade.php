@extends('layouts.admin')

@section('title', 'New SMS Campaign')

@section('admin_content')
<div class="container-fluid px-3 px-md-4 py-4">

    {{-- Top Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Create SMS Campaign</h4>
            <p class="text-muted small mb-0">Broadcast instant or scheduled SMS to targeted audience.</p>
        </div>
        <a href="{{ route('admin.sms.campaigns.index') }}" class="btn btn-outline-secondary btn-sm px-3">
            <i class="fas fa-arrow-left me-1"></i> Back to Campaigns
        </a>
    </div>

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show small py-2 mb-4" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i> Please check form errors below.
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show small py-2 mb-4" role="alert">
            <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.sms.campaigns.store') }}" method="POST" id="campaignForm">
        @csrf

        <div class="row g-4">

            {{-- Left Column: Campaign Details & Audience --}}
            <div class="col-12 col-lg-8">

                {{-- Basic Details --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-semibold mb-0 text-dark">Campaign Info</h6>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-medium small text-dark">Campaign Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control form-control-sm @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Flash Sale Weekend Blast" required>
                                @error('name')
                                    <div class="invalid-feedback small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-medium small text-dark">SMS Provider &amp; Masking <span class="text-danger">*</span></label>
                                <div class="row g-2">
                                    <div class="col-7">
                                        <select name="provider_id" id="providerSelect" class="form-select form-select-sm" required>
                                            @foreach($providers as $provider)
                                                <option value="{{ $provider->id }}" data-price="{{ $provider->price_per_sms ?? 0.35 }}" data-sender="{{ $provider->sender_id }}" {{ old('provider_id') == $provider->id ? 'selected' : '' }}>
                                                    {{ $provider->name }} (৳{{ number_format((float) ($provider->price_per_sms ?? 0.35), 4) }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-5">
                                        <input type="text" name="sender_id" id="senderIdInput" class="form-control form-control-sm" value="{{ old('sender_id', $providers->first()?->sender_id) }}" placeholder="Sender ID">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Target Audience Selection --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-semibold mb-0 text-dark">Target Audience</h6>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-sm-6 col-md-3">
                                <label class="border rounded p-3 d-block cursor-pointer audience-card h-100 {{ old('target_audience', 'all_users') === 'all_users' ? 'border-primary bg-primary-subtle' : '' }}">
                                    <input type="radio" name="target_audience" value="all_users" class="d-none audience-radio" data-count="{{ $audienceCounts['all_users'] ?? 0 }}" {{ old('target_audience', 'all_users') === 'all_users' ? 'checked' : '' }}>
                                    <span class="fw-semibold small d-block text-dark">All Users</span>
                                    <span class="text-muted d-block small" style="font-size: 11px;">Registered accounts</span>
                                    <span class="fw-bold text-primary mt-2 d-block">{{ number_format($audienceCounts['all_users'] ?? 0) }}</span>
                                </label>
                            </div>

                            <div class="col-12 col-sm-6 col-md-3">
                                <label class="border rounded p-3 d-block cursor-pointer audience-card h-100 {{ old('target_audience') === 'subscribed_only' ? 'border-primary bg-primary-subtle' : '' }}">
                                    <input type="radio" name="target_audience" value="subscribed_only" class="d-none audience-radio" data-count="{{ $audienceCounts['subscribed_users'] ?? 0 }}" {{ old('target_audience') === 'subscribed_only' ? 'checked' : '' }}>
                                    <span class="fw-semibold small d-block text-dark">Subscribers</span>
                                    <span class="text-muted d-block small" style="font-size: 11px;">Marketing opted-in</span>
                                    <span class="fw-bold text-primary mt-2 d-block">{{ number_format($audienceCounts['subscribed_users'] ?? 0) }}</span>
                                </label>
                            </div>

                            <div class="col-12 col-sm-6 col-md-3">
                                <label class="border rounded p-3 d-block cursor-pointer audience-card h-100 {{ old('target_audience') === 'recent_buyers' ? 'border-primary bg-primary-subtle' : '' }}">
                                    <input type="radio" name="target_audience" value="recent_buyers" class="d-none audience-radio" data-count="{{ $audienceCounts['recent_buyers'] ?? 0 }}" {{ old('target_audience') === 'recent_buyers' ? 'checked' : '' }}>
                                    <span class="fw-semibold small d-block text-dark">Recent Buyers</span>
                                    <span class="text-muted d-block small" style="font-size: 11px;">Last 60 days orders</span>
                                    <span class="fw-bold text-primary mt-2 d-block">{{ number_format($audienceCounts['recent_buyers'] ?? 0) }}</span>
                                </label>
                            </div>

                            <div class="col-12 col-sm-6 col-md-3">
                                <label class="border rounded p-3 d-block cursor-pointer audience-card h-100 {{ old('target_audience') === 'custom_numbers' ? 'border-primary bg-primary-subtle' : '' }}">
                                    <input type="radio" name="target_audience" value="custom_numbers" class="d-none audience-radio" data-count="0" {{ old('target_audience') === 'custom_numbers' ? 'checked' : '' }}>
                                    <span class="fw-semibold small d-block text-dark">Custom List</span>
                                    <span class="text-muted d-block small" style="font-size: 11px;">Manual numbers</span>
                                    <span class="fw-bold text-primary mt-2 d-block" id="customNumbersCount">0</span>
                                </label>
                            </div>
                        </div>

                        {{-- Custom Phone Numbers Input --}}
                        <div id="customNumbersContainer" class="{{ old('target_audience') === 'custom_numbers' ? '' : 'd-none' }}">
                            <label class="form-label fw-medium small text-dark">Enter Recipient Numbers</label>
                            <textarea name="custom_numbers" id="customNumbersTextarea" rows="4" class="form-control form-control-sm @error('custom_numbers') is-invalid @enderror" placeholder="Paste numbers separated by comma, space or newline (e.g. 01711000000, 01811000000)...">{{ old('custom_numbers') }}</textarea>
                            <span class="form-text small">Bangladeshi 11-digit numbers. Duplicates and blacklisted numbers are filtered out.</span>
                            @error('custom_numbers')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Message Content --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h6 class="fw-semibold mb-0 text-dark">Message Body</h6>
                        @if($templates->count() > 0)
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted small">Insert Template:</span>
                                <select id="templatePicker" class="form-select form-select-sm" style="width: 180px;">
                                    <option value="">Select template...</option>
                                    @foreach($templates as $tpl)
                                        <option value="{{ $tpl->id }}" data-body="{{ $tpl->body }}">{{ $tpl->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small">SMS Content <span class="text-danger">*</span></span>
                            <span class="badge bg-light text-secondary border fw-normal" id="encodingBadge" style="font-size: 11px;">GSM-7</span>
                        </div>

                        <textarea name="sms_message" id="campaignMessage" rows="5" class="form-control @error('sms_message') is-invalid @enderror" placeholder="Type promotional SMS text here..." required>{{ old('sms_message') }}</textarea>
                        @error('sms_message')
                            <div class="invalid-feedback small">{{ $message }}</div>
                        @enderror

                        {{-- Counter Bar --}}
                        <div class="d-flex justify-content-between align-items-center mt-2 small text-muted">
                            <div>Characters: <strong class="text-dark" id="charCount">0</strong></div>
                            <div>
                                Parts: <strong class="text-dark" id="partsCount">1</strong>
                                <span class="text-muted" style="font-size: 11px;" id="partsInfo">(160 chars/part)</span>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label fw-medium small text-dark">Internal Note / Description</label>
                            <input type="text" name="description" class="form-control form-control-sm" value="{{ old('description') }}" placeholder="Campaign objective or marketing reference (optional)">
                        </div>
                    </div>
                </div>

            </div>

            {{-- Right Column: Scheduling & Cost Summary --}}
            <div class="col-12 col-lg-4">

                {{-- Schedule Timing Card --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-semibold mb-0 text-dark">Execution Timing</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="schedule_type" id="scheduleImmediate" value="immediate" {{ old('schedule_type', 'immediate') === 'immediate' ? 'checked' : '' }}>
                            <label class="form-check-label small fw-medium" for="scheduleImmediate">
                                Send Immediately
                            </label>
                            <span class="text-muted d-block small" style="font-size: 11px;">Queues and starts dispatch immediately</span>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="schedule_type" id="scheduleLater" value="scheduled" {{ old('schedule_type') === 'scheduled' ? 'checked' : '' }}>
                            <label class="form-check-label small fw-medium" for="scheduleLater">
                                Schedule for Later
                            </label>
                            <span class="text-muted d-block small" style="font-size: 11px;">Dispatches at specified date &amp; time</span>
                        </div>

                        <div id="scheduleTimeContainer" class="{{ old('schedule_type') === 'scheduled' ? '' : 'd-none' }}">
                            <label class="form-label small fw-medium text-dark">Scheduled Date &amp; Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="scheduled_at" id="scheduledAtInput" class="form-control form-control-sm @error('scheduled_at') is-invalid @enderror" value="{{ old('scheduled_at') }}" min="{{ now()->addMinutes(2)->format('Y-m-d\TH:i') }}">
                            @error('scheduled_at')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Live Summary & Cost Card --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-semibold mb-0 text-dark">Campaign Summary</h6>
                    </div>
                    <div class="card-body p-3">
                        <ul class="list-unstyled small mb-3">
                            <li class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Target Recipients:</span>
                                <strong class="text-dark" id="summaryRecipients">0</strong>
                            </li>
                            <li class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">SMS Parts / Recipient:</span>
                                <strong class="text-dark" id="summaryParts">1</strong>
                            </li>
                            <li class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Total Message Credits:</span>
                                <strong class="text-dark" id="summaryTotalCredits">0</strong>
                            </li>
                            <li class="d-flex justify-content-between py-2">
                                <span class="text-muted fw-semibold">Estimated Cost:</span>
                                <h6 class="fw-bold text-primary mb-0" id="summaryEstCost">৳ 0.00</h6>
                            </li>
                        </ul>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-sm py-2" id="btnSubmitCampaign">
                                <i class="fas fa-paper-plane me-1"></i> Launch Campaign
                            </button>
                            <a href="{{ route('admin.sms.campaigns.index') }}" class="btn btn-light btn-sm py-2 text-muted">Cancel</a>
                        </div>
                    </div>
                </div>

                {{-- Live Mobile Preview --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-semibold mb-0 text-dark">Live Preview</h6>
                    </div>
                    <div class="card-body p-3 text-center bg-light">
                        <div class="mx-auto bg-white border rounded-4 p-3 shadow-sm text-start" style="max-width: 270px; min-height: 160px;">
                            <div class="border-bottom pb-2 mb-2 d-flex justify-content-between align-items-center">
                                <span class="fw-bold small text-dark" id="previewSender">SHOP NOW BD</span>
                                <span class="text-muted" style="font-size: 10px;">Preview</span>
                            </div>
                            <div class="small text-dark font-monospace" id="previewBody" style="white-space: pre-wrap; word-break: break-word; font-size: 12px;">
                                Type message to preview...
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
    const providerSelect = document.getElementById('providerSelect');
    const senderIdInput = document.getElementById('senderIdInput');
    const campaignMessage = document.getElementById('campaignMessage');
    const templatePicker = document.getElementById('templatePicker');
    const charCount = document.getElementById('charCount');
    const partsCount = document.getElementById('partsCount');
    const partsInfo = document.getElementById('partsInfo');
    const encodingBadge = document.getElementById('encodingBadge');

    const audienceRadios = document.querySelectorAll('.audience-radio');
    const customNumbersContainer = document.getElementById('customNumbersContainer');
    const customNumbersTextarea = document.getElementById('customNumbersTextarea');
    const customNumbersCount = document.getElementById('customNumbersCount');

    const scheduleImmediate = document.getElementById('scheduleImmediate');
    const scheduleLater = document.getElementById('scheduleLater');
    const scheduleTimeContainer = document.getElementById('scheduleTimeContainer');

    const summaryRecipients = document.getElementById('summaryRecipients');
    const summaryParts = document.getElementById('summaryParts');
    const summaryTotalCredits = document.getElementById('summaryTotalCredits');
    const summaryEstCost = document.getElementById('summaryEstCost');
    const previewBody = document.getElementById('previewBody');
    const previewSender = document.getElementById('previewSender');

    // 1. Provider & Sender update
    if (providerSelect) {
        providerSelect.addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];
            const defaultSender = selected.getAttribute('data-sender') || '';
            if (senderIdInput) senderIdInput.value = defaultSender;
            if (previewSender) previewSender.textContent = defaultSender || 'SHOP NOW BD';
            calculateEverything();
        });
    }

    if (senderIdInput) {
        senderIdInput.addEventListener('input', function () {
            if (previewSender) previewSender.textContent = this.value.trim() || 'SHOP NOW BD';
        });
    }

    // 2. Audience Selection
    audienceRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            document.querySelectorAll('.audience-card').forEach(c => {
                c.classList.remove('border-primary', 'bg-primary-subtle');
            });
            this.closest('.audience-card').classList.add('border-primary', 'bg-primary-subtle');

            if (this.value === 'custom_numbers') {
                if (customNumbersContainer) customNumbersContainer.classList.remove('d-none');
            } else {
                if (customNumbersContainer) customNumbersContainer.classList.add('d-none');
            }
            calculateEverything();
        });
    });

    // Custom numbers count watcher
    if (customNumbersTextarea) {
        customNumbersTextarea.addEventListener('input', function () {
            const text = this.value.trim();
            const count = text ? text.split(/[\r\n,]+/).filter(Boolean).length : 0;
            if (customNumbersCount) customNumbersCount.textContent = count;
            calculateEverything();
        });
    }

    // 3. Template Insert
    if (templatePicker) {
        templatePicker.addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];
            const body = selected.getAttribute('data-body') || '';
            if (body && campaignMessage) {
                campaignMessage.value = body;
                calculateEverything();
            }
        });
    }

    // 4. Scheduling Type Toggle
    if (scheduleImmediate) {
        scheduleImmediate.addEventListener('change', function () {
            if (this.checked && scheduleTimeContainer) scheduleTimeContainer.classList.add('d-none');
        });
    }
    if (scheduleLater) {
        scheduleLater.addEventListener('change', function () {
            if (this.checked && scheduleTimeContainer) scheduleTimeContainer.classList.remove('d-none');
        });
    }

    // 5. Message & Cost Calculation Engine
    function calculateEverything() {
        if (!campaignMessage) return;

        const text = campaignMessage.value;
        const length = text.length;
        if (charCount) charCount.textContent = length;

        // Detect Unicode
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
                if (partsInfo) partsInfo.textContent = '(67 chars/part)';
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
                if (partsInfo) partsInfo.textContent = '(153 chars/part)';
            }
        }

        if (partsCount) partsCount.textContent = parts;
        if (summaryParts) summaryParts.textContent = parts;
        if (previewBody) previewBody.textContent = length > 0 ? text : 'Type message to preview...';

        // Recipients calculation
        let recipients = 0;
        const activeAudience = document.querySelector('.audience-radio:checked');
        if (activeAudience) {
            if (activeAudience.value === 'custom_numbers') {
                const raw = customNumbersTextarea ? customNumbersTextarea.value.trim() : '';
                recipients = raw ? raw.split(/[\r\n,]+/).filter(Boolean).length : 0;
            } else {
                recipients = parseInt(activeAudience.getAttribute('data-count') || 0, 10);
            }
        }

        if (summaryRecipients) summaryRecipients.textContent = recipients.toLocaleString();

        const totalCredits = recipients * parts;
        if (summaryTotalCredits) summaryTotalCredits.textContent = totalCredits.toLocaleString();

        const price = parseFloat(providerSelect ? (providerSelect.options[providerSelect.selectedIndex]?.getAttribute('data-price') || 0.35) : 0.35);
        const estimatedCost = (totalCredits * price).toFixed(2);
        if (summaryEstCost) summaryEstCost.textContent = `৳ ${parseFloat(estimatedCost).toLocaleString()}`;
    }

    if (campaignMessage) {
        campaignMessage.addEventListener('input', calculateEverything);
    }

    // Initial Trigger
    calculateEverything();
});
</script>
<style>
.cursor-pointer {
    cursor: pointer;
}
.audience-card {
    transition: all 0.15s ease-in-out;
}
</style>
@endpush