@extends('layouts.admin')

@section('title', 'Create Marketing Campaign - Email Marketing')

@section('admin_content')
<div class="container-fluid px-3 py-2" id="email-marketing-campaign-create-root">
    {{-- Header & Navigation --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: #03172e;">
                <i class="fas fa-paper-plane text-primary me-2"></i>Create Email Campaign
            </h4>
            <p class="text-muted small mb-0">Design, target, and schedule high-converting promotional broadcasts.</p>
        </div>

        <div>
            <a href="{{ route('admin.email_marketing.campaigns.index') }}" class="btn btn-sm btn-outline-secondary shadow-sm" style="border-radius: 5px;">
                <i class="fas fa-arrow-left me-1"></i> Back to Campaigns
            </a>
        </div>
    </div>

    {{-- Error Alerts --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: 6px;">
            <div class="fw-bold mb-1"><i class="fas fa-exclamation-triangle me-2"></i>Please resolve the following errors:</div>
            <ul class="mb-0 ps-3 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('admin.email_marketing.campaigns.store') }}" method="POST" id="campaignCreateForm">
        @csrf

        <div class="row g-3">
            {{-- Left Column: Core Campaign Settings & Messaging --}}
            <div class="col-12 col-xl-8">
                {{-- 1. Campaign Identity & Headers --}}
                <div class="card border-0 shadow-sm mb-3" style="border-radius: 8px;">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="fas fa-envelope-open-text text-primary me-2"></i>Campaign &amp; Subject Details
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Campaign Name (Internal Reference) <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control form-control-sm" placeholder="Flash Sale Announcement - Weekend Special" value="{{ old('name') }}" required>
                                <small class="text-muted" style="font-size: 11px;">Only visible to admins in reports and dashboard.</small>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-semibold">Email Subject Line <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <input type="text" name="subject" id="campaignSubjectInput" class="form-control" placeholder="🔥 Flat 30% Off Everything - Limited Time Only!" value="{{ old('subject') }}" required>
                                    <button type="button" class="btn btn-outline-secondary" onclick="insertTagIntoSubject('{first_name}')" title="Insert Recipient's First Name">
                                        <i class="fas fa-user-tag me-1"></i> +{first_name}
                                    </button>
                                </div>
                                <small class="text-muted" style="font-size: 11px;">Make it catchy to improve open rates. Personalization tags supported.</small>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-semibold">Preview / Snippet Text</label>
                                <input type="text" name="preview_text" class="form-control form-control-sm" placeholder="Grab your favorite fashion items before stock runs out..." value="{{ old('preview_text') }}">
                                <small class="text-muted" style="font-size: 11px;">Snippet displayed right beside the subject line in customer inboxes (Gmail, Apple Mail, Outlook).</small>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold">Sender Display Name</label>
                                <input type="text" name="sender_name" class="form-control form-control-sm" placeholder="Shop Now BD" value="{{ old('sender_name') }}">
                                <small class="text-muted" style="font-size: 11px;">Leave blank to use default store sender name.</small>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold">Sender Email Address</label>
                                <input type="email" name="sender_email" class="form-control form-control-sm" placeholder="newsletter@shopnowbd.online" value="{{ old('sender_email') }}">
                                <small class="text-muted" style="font-size: 11px;">Must be a verified sender domain in your SMTP/Provider settings.</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. Target Audience & Template Selection --}}
                <div class="card border-0 shadow-sm" style="border-radius: 8px;">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="fas fa-layer-group text-success me-2"></i>Audience &amp; Content Design
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold">Target Audience Segment</label>
                                <select name="segment_id" class="form-select form-select-sm">
                                    <option value="">-- All Legally Consented Subscribers (Broadcast) --</option>
                                    @foreach($segments as $segment)
                                        <option value="{{ $segment->id }}" {{ old('segment_id') == $segment->id ? 'selected' : '' }}>
                                            {{ $segment->name }} ({{ number_format($segment->matching_subscribers_count) }} subscribers)
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted" style="font-size: 11px;">Selecting a segment isolates the broadcast to matching customer criteria.</small>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold">Email Template</label>
                                <select name="template_id" class="form-select form-select-sm" required>
                                    <option value="">-- Select Design Template --</option>
                                    @foreach($templates as $template)
                                        <option value="{{ $template->id }}" {{ old('template_id') == $template->id ? 'selected' : '' }}>
                                            {{ $template->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted" style="font-size: 11px;">
                                    Need a new design? <a href="{{ route('admin.email_marketing.templates.create') }}" target="_blank" class="text-primary text-decoration-none">Create a Template</a>.
                                </small>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-semibold">Attach Promotional Coupon (Optional)</label>
                                <select name="coupon_id" class="form-select form-select-sm">
                                    <option value="">-- No Coupon Attached --</option>
                                    @foreach($coupons as $coupon)
                                        <option value="{{ $coupon->id }}" {{ old('coupon_id') == $coupon->id ? 'selected' : '' }}>
                                            Code: {{ $coupon->code }} &bull; Discount: ৳{{ number_format($coupon->discount_amount ?? 0) }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted" style="font-size: 11px;">Attached coupons can be automatically rendered using dynamic placeholder tags.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Scheduling, Delivery Rules & Submission --}}
            <div class="col-12 col-xl-4">
                {{-- Delivery Schedule Settings Card --}}
                <div class="card border-0 shadow-sm mb-3" style="border-radius: 8px;">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="fas fa-clock text-warning me-2"></i>Delivery Schedule
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Schedule Broadcast Time</label>
                            <input type="datetime-local" name="scheduled_at" class="form-control form-control-sm" value="{{ old('scheduled_at') }}">
                            <small class="text-muted" style="font-size: 11px;">Leave empty to save as <strong>Draft</strong>. Set future date to auto-send on schedule.</small>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Sending Rate (Batch Size)</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="batch_size" class="form-control" value="{{ old('batch_size', 100) }}" min="10" max="1000" step="10">
                                <span class="input-group-text bg-light text-muted">mails / batch</span>
                            </div>
                            <small class="text-muted" style="font-size: 11px;">Regulates queue throttle to avoid mailbox rate-limiting.</small>
                        </div>
                    </div>
                </div>

                {{-- Anti-Spam Safeguard Card --}}
                <div class="card border-0 shadow-sm mb-3" style="border-radius: 8px; background: #f8fafc; border: 1px solid #e2e8f0;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fas fa-shield-halved text-success mt-1"></i>
                            <div class="small text-muted" style="font-size: 11.5px; line-height: 1.45;">
                                <strong>Compliance Safeguards:</strong>
                                <ul class="mb-0 ps-3 mt-1">
                                    <li>Unsubscribed &amp; bounced emails are automatically suppressed.</li>
                                    <li>Mandatory 1-click unsubscribe links are injected into the footer.</li>
                                    <li>Pixel and link redirection tracking are enabled.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Panel Card --}}
                <div class="card border-0 shadow-sm" style="border-radius: 8px;">
                    <div class="card-body p-3">
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold mb-2 shadow-sm" style="border-radius: 5px;">
                            <i class="fas fa-save me-1"></i> Save Campaign Configuration
                        </button>
                        <a href="{{ route('admin.email_marketing.campaigns.index') }}" class="btn btn-light border w-100 py-2 small" style="border-radius: 5px;">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function insertTagIntoSubject(tag) {
        const input = document.getElementById('campaignSubjectInput');
        if (input) {
            const start = input.selectionStart || 0;
            const end = input.selectionEnd || 0;
            const text = input.value;
            input.value = text.substring(0, start) + ' ' + tag + ' ' + text.substring(end);
            input.focus();
        }
    }
</script>
@endsection