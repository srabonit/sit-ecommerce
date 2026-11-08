
@extends('layouts.admin')

@section('title', 'Create Marketing Campaign - Email Marketing')

@section('admin_content')

<style>
    /* =========================================================
       KAIADMIN LITE v1.2.0 - CLASSIC WHITE DESIGN
       Email Marketing / Create Campaign
    ========================================================= */

    #email-marketing-campaign-create-root {
        --campaign-primary: var(--bs-primary, #1572e8);
        --campaign-success: #31ce36;
        --campaign-warning: #ffad46;
        --campaign-danger: #f25961;
        --campaign-info: #48abf7;

        --campaign-heading: #2a2f5b;
        --campaign-text: #495057;
        --campaign-muted: #8d9498;
        --campaign-border: #ebedf2;
        --campaign-surface: #ffffff;
        --campaign-background: #f5f7fd;

        --campaign-radius: 5px;
        --campaign-shadow: 0 1px 3px rgba(0, 0, 0, .035);

        width: 100%;
        min-width: 0;
        color: var(--campaign-text);
        font-size: 13px;
    }

    #email-marketing-campaign-create-root *,
    #email-marketing-campaign-create-root *::before,
    #email-marketing-campaign-create-root *::after {
        box-sizing: border-box;
    }

    /* Page Header */

    #email-marketing-campaign-create-root .campaign-page-header {
        margin-bottom: 22px;
    }

    #email-marketing-campaign-create-root .campaign-page-title {
        color: var(--campaign-heading);
        font-size: clamp(18px, 1.6vw, 22px);
        font-weight: 700;
        letter-spacing: -.4px;
        line-height: 1.4;
    }

    #email-marketing-campaign-create-root .campaign-page-description {
        color: var(--campaign-muted);
        font-size: 12px;
        line-height: 1.6;
        margin-bottom: 0;
    }

    /* Common Radius */

    #email-marketing-campaign-create-root .btn,
    #email-marketing-campaign-create-root .badge,
    #email-marketing-campaign-create-root .alert,
    #email-marketing-campaign-create-root .card,
    #email-marketing-campaign-create-root .form-control,
    #email-marketing-campaign-create-root .form-select,
    #email-marketing-campaign-create-root .input-group-text,
    #email-marketing-campaign-create-root .input-group > .btn {
        border-radius: var(--campaign-radius) !important;
    }

    /* Cards */

    #email-marketing-campaign-create-root .card {
        background: var(--campaign-surface);
        border: 1px solid var(--campaign-border) !important;
        box-shadow: var(--campaign-shadow);
        overflow: hidden;
        margin-bottom: 16px;
    }

    #email-marketing-campaign-create-root .card-header {
        min-height: 52px;
        display: flex;
        align-items: center;
        padding: 13px 17px !important;
        background: #fff !important;
        border-bottom: 1px solid var(--campaign-border) !important;
    }

    #email-marketing-campaign-create-root .card-header h6 {
        color: var(--campaign-heading) !important;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.5;
        margin: 0;
    }

    #email-marketing-campaign-create-root .card-header h6 i {
        font-size: 14px;
    }

    #email-marketing-campaign-create-root .card-body {
        padding: 18px !important;
        min-width: 0;
    }

    /* Forms */

    #email-marketing-campaign-create-root .form-label {
        display: block;
        margin-bottom: 7px;
        color: var(--campaign-heading);
        font-size: 12px;
        font-weight: 600;
        line-height: 1.5;
    }

    #email-marketing-campaign-create-root .form-control,
    #email-marketing-campaign-create-root .form-select {
        min-height: 39px;
        padding: 8px 11px;
        background-color: #fff;
        border: 1px solid #e3e6ed;
        color: var(--campaign-text);
        font-size: 12px;
        line-height: 1.5;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #email-marketing-campaign-create-root .form-control::placeholder {
        color: #adb5bd;
        opacity: 1;
    }

    #email-marketing-campaign-create-root .form-control:focus,
    #email-marketing-campaign-create-root .form-select:focus {
        border-color: var(--campaign-primary);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .09);
        outline: none;
    }

    #email-marketing-campaign-create-root .form-control:disabled,
    #email-marketing-campaign-create-root .form-select:disabled {
        background-color: #f5f6f9;
        cursor: not-allowed;
    }

    #email-marketing-campaign-create-root .form-control-sm,
    #email-marketing-campaign-create-root .form-select-sm {
        min-height: 39px;
        font-size: 12px;
    }

    #email-marketing-campaign-create-root small.text-muted {
        display: block;
        margin-top: 6px;
        color: var(--campaign-muted) !important;
        font-size: 10.5px !important;
        line-height: 1.55;
    }

    /* Input Groups */

    #email-marketing-campaign-create-root .input-group {
        flex-wrap: nowrap;
        min-width: 0;
    }

    #email-marketing-campaign-create-root .input-group > .form-control {
        min-width: 0;
    }

    #email-marketing-campaign-create-root .input-group-text {
        padding: 8px 11px;
        background: #f8f9fc;
        border-color: #e3e6ed;
        color: #6c757d;
        font-size: 11px;
        white-space: nowrap;
    }

    #email-marketing-campaign-create-root .input-group > .btn {
        min-height: 39px;
        padding: 7px 12px;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }

    /* Buttons */

    #email-marketing-campaign-create-root .btn {
        font-size: 12px;
        font-weight: 600;
        transition: all .18s ease;
    }

    #email-marketing-campaign-create-root .btn-primary {
        background: var(--campaign-primary);
        border-color: var(--campaign-primary);
    }

    #email-marketing-campaign-create-root .btn-primary:hover {
        filter: brightness(.94);
        transform: translateY(-1px);
    }

    #email-marketing-campaign-create-root .btn-outline-secondary {
        border-color: #dce0e5;
        color: #59636e;
    }

    #email-marketing-campaign-create-root .btn-outline-secondary:hover {
        background: #f5f7fa;
        color: var(--campaign-heading);
        border-color: #cbd2dc;
    }

    #email-marketing-campaign-create-root .btn-light {
        background: #fff;
        border-color: #e3e6ed !important;
        color: #59636e;
    }

    #email-marketing-campaign-create-root .btn-light:hover {
        background: #f5f7fa;
    }

    #email-marketing-campaign-create-root .btn:focus-visible {
        outline: 2px solid var(--campaign-primary);
        outline-offset: 2px;
    }

    /* Personalization Button */

    #email-marketing-campaign-create-root .personalization-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
    }

    /* Compliance Panel */

    #email-marketing-campaign-create-root .compliance-card {
        background: #f8fafc;
        border: 1px solid #e5eaf1 !important;
        box-shadow: none;
    }

    #email-marketing-campaign-create-root .compliance-icon {
        width: 34px;
        height: 34px;
        min-width: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #e9f9eb;
        color: var(--campaign-success);
        border-radius: 5px;
        font-size: 14px;
    }

    #email-marketing-campaign-create-root .compliance-title {
        color: var(--campaign-heading);
        font-size: 12px;
        font-weight: 700;
    }

    #email-marketing-campaign-create-root .compliance-content {
        color: #737b85;
        font-size: 11px;
        line-height: 1.65;
    }

    #email-marketing-campaign-create-root .compliance-content ul {
        padding-left: 17px;
    }

    #email-marketing-campaign-create-root .compliance-content li {
        padding-left: 2px;
        margin-bottom: 5px;
    }

    #email-marketing-campaign-create-root .compliance-content li:last-child {
        margin-bottom: 0;
    }

    /* Error Alerts */

    #email-marketing-campaign-create-root .alert {
        padding: 14px 16px;
        border: 1px solid rgba(242, 89, 97, .18) !important;
        background: #fff5f5;
        color: #a94442;
    }

    #email-marketing-campaign-create-root .alert ul {
        line-height: 1.7;
    }

    /* Action Panel */

    #email-marketing-campaign-create-root .campaign-action-card {
        position: sticky;
        top: 15px;
    }

    #email-marketing-campaign-create-root .campaign-action-card .btn {
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    #email-marketing-campaign-create-root .campaign-action-card .btn-primary {
        box-shadow: 0 2px 5px rgba(21, 114, 232, .13);
    }

    /* Links */

    #email-marketing-campaign-create-root a {
        transition: color .15s ease;
    }

    #email-marketing-campaign-create-root .template-create-link {
        font-weight: 600;
    }

    /* Responsive: Large Screens */

    @media (min-width: 1400px) {
        #email-marketing-campaign-create-root .card-body {
            padding: 20px !important;
        }
    }

    /* Responsive: Tablet */

    @media (max-width: 1199.98px) {
        #email-marketing-campaign-create-root .campaign-action-card {
            position: static;
        }
    }

    @media (max-width: 991.98px) {
        #email-marketing-campaign-create-root {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        #email-marketing-campaign-create-root .card-body {
            padding: 15px !important;
        }
    }

    /* Responsive: Mobile */

    @media (max-width: 767.98px) {
        #email-marketing-campaign-create-root .campaign-page-header {
            margin-bottom: 17px;
        }

        #email-marketing-campaign-create-root .campaign-page-title {
            font-size: 18px;
        }

        #email-marketing-campaign-create-root .campaign-page-description {
            font-size: 11.5px;
        }

        #email-marketing-campaign-create-root .card-header {
            min-height: 47px;
            padding: 11px 13px !important;
        }

        #email-marketing-campaign-create-root .card-header h6 {
            font-size: 12px;
        }

        #email-marketing-campaign-create-root .card-body {
            padding: 14px !important;
        }

        #email-marketing-campaign-create-root .form-label {
            font-size: 11.5px;
        }

        #email-marketing-campaign-create-root .form-control,
        #email-marketing-campaign-create-root .form-select {
            min-height: 40px;
        }

        #email-marketing-campaign-create-root .input-group > .btn {
            padding-left: 9px;
            padding-right: 9px;
            font-size: 10px;
        }

        #email-marketing-campaign-create-root .campaign-action-card .btn {
            min-height: 43px;
        }
    }

    /* Responsive: Small Mobile */

    @media (max-width: 380px) {
        #email-marketing-campaign-create-root {
            padding-left: 8px !important;
            padding-right: 8px !important;
        }

        #email-marketing-campaign-create-root .campaign-page-title {
            font-size: 16px;
        }

        #email-marketing-campaign-create-root .card-body {
            padding: 12px !important;
        }

        #email-marketing-campaign-create-root .input-group > .btn {
            padding-left: 6px;
            padding-right: 6px;
            font-size: 9px;
        }

        #email-marketing-campaign-create-root .input-group > .btn i {
            margin-right: 2px !important;
        }
    }

    /* Reduced Motion */

    @media (prefers-reduced-motion: reduce) {
        #email-marketing-campaign-create-root *,
        #email-marketing-campaign-create-root *::before,
        #email-marketing-campaign-create-root *::after {
            transition-duration: .01ms !important;
            animation-duration: .01ms !important;
        }
    }
</style>

<div class="container-fluid px-3 py-3" id="email-marketing-campaign-create-root">

    {{-- Header & Navigation --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 campaign-page-header">

        <div class="min-w-0">
            <h4 class="campaign-page-title mb-1">
                <i class="fas fa-paper-plane text-primary me-2"></i>
                Create Email Campaign
            </h4>

            <p class="campaign-page-description">
                Design, target, and schedule promotional email campaigns.
            </p>
        </div>

        <div>
            <a href="{{ route('admin.email_marketing.campaigns.index') }}" class="btn btn-sm btn-outline-secondary shadow-sm px-3 py-2">
                <i class="fas fa-arrow-left me-1"></i>
                Back to Campaigns
            </a>
        </div>
    </div>

    {{-- Error Alerts --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-3" role="alert">

            <div class="fw-bold mb-2">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Please resolve the following errors:
            </div>

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

            {{-- Left Column: Campaign Details --}}
            <div class="col-12 col-xl-8">

                {{-- 1. Campaign Identity & Headers --}}
                <div class="card shadow-sm mb-3">

                    <div class="card-header">
                        <h6>
                            <i class="fas fa-envelope-open-text text-primary me-2"></i>
                            Campaign &amp; Subject Details
                        </h6>
                    </div>

                    <div class="card-body">

                        <div class="row g-3">

                            <div class="col-12">
                                <label class="form-label">
                                    Campaign Name (Internal Reference)
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="text" name="name" class="form-control form-control-sm" placeholder="Flash Sale Announcement - Weekend Special" value="{{ old('name') }}" required>

                                <small class="text-muted">
                                    Visible only to admins in reports and dashboards.
                                </small>
                            </div>

                            <div class="col-12">
                                <label class="form-label">
                                    Email Subject Line
                                    <span class="text-danger">*</span>
                                </label>

                                <div class="input-group input-group-sm">

                                    <input type="text" name="subject" id="campaignSubjectInput" class="form-control" placeholder="🔥 Flat 30% Off Everything - Limited Time Only!" value="{{ old('subject') }}" required>

                                    <button type="button" class="btn btn-outline-secondary personalization-btn" onclick="insertTagIntoSubject('{first_name}')" title="Insert Recipient's First Name">
                                        <i class="fas fa-user-tag"></i>
                                        +{first_name}
                                    </button>
                                </div>

                                <small class="text-muted">
                                    Write an engaging subject line. Personalization tags are supported.
                                </small>
                            </div>

                            <div class="col-12">
                                <label class="form-label">
                                    Preview / Snippet Text
                                </label>

                                <input type="text" name="preview_text" class="form-control form-control-sm" placeholder="Grab your favorite fashion items before stock runs out..." value="{{ old('preview_text') }}">

                                <small class="text-muted">
                                    Displayed beside the subject line in Gmail, Apple Mail, and Outlook.
                                </small>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">
                                    Sender Display Name
                                </label>

                                <input type="text" name="sender_name" class="form-control form-control-sm" placeholder="Shop Now BD" value="{{ old('sender_name') }}">

                                <small class="text-muted">
                                    Leave blank to use the default store sender name.
                                </small>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">
                                    Sender Email Address
                                </label>

                                <input type="email" name="sender_email" class="form-control form-control-sm" placeholder="newsletter@shopnowbd.online" value="{{ old('sender_email') }}">

                                <small class="text-muted">
                                    Use a verified sender domain from your SMTP/provider settings.
                                </small>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- 2. Target Audience & Template Selection --}}
                <div class="card shadow-sm">

                    <div class="card-header">
                        <h6>
                            <i class="fas fa-layer-group text-success me-2"></i>
                            Audience &amp; Content Design
                        </h6>
                    </div>

                    <div class="card-body">

                        <div class="row g-3">

                            <div class="col-12 col-md-6">
                                <label class="form-label">
                                    Target Audience Segment
                                </label>

                                <select name="segment_id" class="form-select form-select-sm">

                                    <option value="">-- All Legally Consented Subscribers (Broadcast) --</option>

                                    @foreach($segments as $segment)
                                        <option value="{{ $segment->id }}" {{ old('segment_id') == $segment->id ? 'selected' : '' }}>
                                            {{ $segment->name }} ({{ number_format($segment->matching_subscribers_count) }} subscribers)
                                        </option>
                                    @endforeach

                                </select>

                                <small class="text-muted">
                                    Select a segment to target matching customers only.
                                </small>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">
                                    Email Template
                                    <span class="text-danger">*</span>
                                </label>

                                <select name="template_id" class="form-select form-select-sm" required>

                                    <option value="">-- Select Design Template --</option>

                                    @foreach($templates as $template)
                                        <option value="{{ $template->id }}" {{ old('template_id') == $template->id ? 'selected' : '' }}>
                                            {{ $template->name }}
                                        </option>
                                    @endforeach

                                </select>

                                <small class="text-muted">
                                    Need a new design?
                                    <a href="{{ route('admin.email_marketing.templates.create') }}" target="_blank" class="text-primary text-decoration-none template-create-link">
                                        Create a Template
                                    </a>.
                                </small>
                            </div>

                            <div class="col-12">
                                <label class="form-label">
                                    Attach Promotional Coupon (Optional)
                                </label>

                                <select name="coupon_id" class="form-select form-select-sm">

                                    <option value="">-- No Coupon Attached --</option>

                                    @foreach($coupons as $coupon)
                                        <option value="{{ $coupon->id }}" {{ old('coupon_id') == $coupon->id ? 'selected' : '' }}>
                                            Code: {{ $coupon->code }} &bull; Discount: ৳{{ number_format($coupon->discount_amount ?? 0) }}
                                        </option>
                                    @endforeach

                                </select>

                                <small class="text-muted">
                                    Coupon details can be rendered using dynamic placeholder tags.
                                </small>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Scheduling & Delivery --}}
            <div class="col-12 col-xl-4">

                {{-- Delivery Schedule Settings --}}
                <div class="card shadow-sm mb-3">

                    <div class="card-header">
                        <h6>
                            <i class="fas fa-clock text-warning me-2"></i>
                            Delivery Schedule
                        </h6>
                    </div>

                    <div class="card-body">

                        <div class="mb-3">
                            <label class="form-label">
                                Schedule Broadcast Time
                            </label>

                            <input type="datetime-local" name="scheduled_at" class="form-control form-control-sm" value="{{ old('scheduled_at') }}">

                            <small class="text-muted">
                                Leave empty to save as <strong>Draft</strong>. Choose a future date to schedule delivery.
                            </small>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">
                                Sending Rate (Batch Size)
                            </label>

                            <div class="input-group input-group-sm">

                                <input type="number" name="batch_size" class="form-control" value="{{ old('batch_size', 100) }}" min="10" max="1000" step="10">

                                <span class="input-group-text bg-light text-muted">
                                    mails / batch
                                </span>
                            </div>

                            <small class="text-muted">
                                Controls queue throttling to reduce mailbox rate limits.
                            </small>
                        </div>

                    </div>
                </div>

                {{-- Anti-Spam Safeguards --}}
                <div class="card compliance-card mb-3">

                    <div class="card-body">

                        <div class="d-flex align-items-start gap-3">

                            <div class="compliance-icon">
                                <i class="fas fa-shield-halved"></i>
                            </div>

                            <div class="min-w-0 flex-grow-1">

                                <div class="compliance-title mb-2">
                                    Compliance Safeguards
                                </div>

                                <div class="compliance-content">

                                    <ul class="mb-0 ps-3 mt-1">
                                        <li>Unsubscribed and bounced emails are automatically suppressed.</li>
                                        <li>Mandatory one-click unsubscribe links are added to the footer.</li>
                                        <li>Pixel and link redirection tracking are enabled.</li>
                                    </ul>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Campaign Action Panel --}}
                <div class="card campaign-action-card">

                    <div class="card-body">

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold mb-2 shadow-sm">
                            <i class="fas fa-save me-2"></i>
                            Save Campaign Configuration
                        </button>

                        <a href="{{ route('admin.email_marketing.campaigns.index') }}" class="btn btn-light border w-100 py-2">
                            <i class="fas fa-times me-1"></i>
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
