@extends('layouts.admin')

@section('title', 'Edit Campaign - ' . $campaign->name)

@php
    $badgeClass = match($campaign->status) {
        'completed'         => 'bg-success-subtle text-success border border-success-subtle',
        'sending', 'queued' => 'bg-primary-subtle text-primary border border-primary-subtle',
        'scheduled'         => 'bg-info-subtle text-info border border-info-subtle',
        'paused'            => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
        'cancelled'         => 'bg-danger-subtle text-danger border border-danger-subtle',
        default             => 'bg-light text-dark border',
    };

    $scheduledFormatted = $campaign->scheduled_at ? $campaign->scheduled_at->format('Y-m-d\TH:i') : '';
@endphp

@section('admin_content')
<style>
    /* =========================================================
       EMAIL CAMPAIGN EDIT - KAIADMIN WHITE CLASSIC
    ========================================================= */

    #email-marketing-campaign-edit-root {
        --campaign-primary: #1572e8;
        --campaign-primary-soft: #edf4ff;
        --campaign-success: #31ce36;
        --campaign-warning: #ffad46;
        --campaign-danger: #f25961;
        --campaign-info: #48abf7;
        --campaign-heading: #2a2f5b;
        --campaign-text: #495057;
        --campaign-muted: #8d9498;
        --campaign-border: #ebecec;
        --campaign-surface: #ffffff;
        --campaign-background: #f8f9fa;
        color: var(--campaign-text);
        font-size: 13px;
    }

    #email-marketing-campaign-edit-root,
    #email-marketing-campaign-edit-root * {
        box-sizing: border-box;
    }

    #email-marketing-campaign-edit-root .btn,
    #email-marketing-campaign-edit-root button,
    #email-marketing-campaign-edit-root .badge,
    #email-marketing-campaign-edit-root .alert,
    #email-marketing-campaign-edit-root .form-control,
    #email-marketing-campaign-edit-root .form-select,
    #email-marketing-campaign-edit-root .input-group-text,
    #email-marketing-campaign-edit-root .card,
    #email-marketing-campaign-edit-root .tag-chip,
    #email-marketing-campaign-edit-root .inbox-preview-box,
    #email-marketing-campaign-edit-root .collapse-toggle-btn {
        border-radius: 5px !important;
    }

    #email-marketing-campaign-edit-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    #email-marketing-campaign-edit-root .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    #email-marketing-campaign-edit-root .bn-digit,
    #email-marketing-campaign-edit-root .font-monospace {
        font-weight: 600;
    }

    /* Header */
    #email-marketing-campaign-edit-root .campaign-edit-header {
        padding: 2px 0 16px;
        border-bottom: 1px solid var(--campaign-border);
    }

    #email-marketing-campaign-edit-root .campaign-edit-header h5 {
        color: var(--campaign-heading) !important;
        font-size: 18px !important;
        font-weight: 700;
        line-height: 1.4;
    }

    #email-marketing-campaign-edit-root .campaign-edit-header small {
        color: var(--campaign-muted) !important;
        font-size: 12px !important;
        line-height: 1.5;
    }

    #email-marketing-campaign-edit-root .header-actions-wrap {
        align-items: center;
        flex-wrap: wrap;
    }

    /* Compact buttons */
    #email-marketing-campaign-edit-root .btn-compact {
        min-height: 34px;
        padding: 7px 12px !important;
        font-size: 11px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: background-color .18s ease, border-color .18s ease, color .18s ease, box-shadow .18s ease;
    }

    #email-marketing-campaign-edit-root .btn-primary {
        background-color: var(--campaign-primary);
        border-color: var(--campaign-primary);
    }

    #email-marketing-campaign-edit-root .btn-primary:hover {
        background-color: #0d62cf;
        border-color: #0d62cf;
    }

    #email-marketing-campaign-edit-root .btn-outline-primary {
        color: var(--campaign-primary);
        border-color: #c9dcfa;
    }

    #email-marketing-campaign-edit-root .btn-outline-primary:hover {
        color: #ffffff;
        background-color: var(--campaign-primary);
        border-color: var(--campaign-primary);
    }

    #email-marketing-campaign-edit-root .shadow-xs {
        box-shadow: 0 1px 3px rgba(0, 0, 0, .05) !important;
    }

    /* Cards */
    #email-marketing-campaign-edit-root .card {
        border: 1px solid var(--campaign-border) !important;
        background: var(--campaign-surface);
        box-shadow: 0 1px 3px rgba(0, 0, 0, .035) !important;
        margin-bottom: 16px;
        overflow: hidden;
    }

    #email-marketing-campaign-edit-root .section-header-wrap {
        min-height: 48px;
        padding: 11px 16px;
        background: #ffffff;
        border-bottom: 1px solid var(--campaign-border);
    }

    #email-marketing-campaign-edit-root .section-header-wrap > span {
        color: var(--campaign-heading) !important;
        font-size: 12.5px !important;
        font-weight: 700 !important;
        letter-spacing: .1px;
    }

    #email-marketing-campaign-edit-root .section-header-wrap > span i {
        font-size: 13px;
    }

    /* Collapse controls */
    #email-marketing-campaign-edit-root .collapse-toggle-btn {
        width: 27px;
        height: 27px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #ffffff;
        border: 1px solid #e3e6ea;
        color: #6c757d;
        transition: all .2s ease;
        flex-shrink: 0;
    }

    #email-marketing-campaign-edit-root .collapse-toggle-btn:hover {
        background: var(--campaign-primary);
        border-color: var(--campaign-primary);
        color: #ffffff;
    }

    #email-marketing-campaign-edit-root .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    #email-marketing-campaign-edit-root .collapse-icon {
        font-size: 10px;
        transition: transform .25s ease;
    }

    /* Form labels */
    #email-marketing-campaign-edit-root .form-label {
        display: block;
        margin-bottom: 6px;
        color: #495057;
        font-size: 11.5px;
        font-weight: 700;
    }

    #email-marketing-campaign-edit-root .form-label .text-danger {
        font-size: 12px;
    }

    /* Form controls */
    #email-marketing-campaign-edit-root .form-control,
    #email-marketing-campaign-edit-root .form-select {
        min-height: 38px;
        padding: 8px 11px;
        color: #495057;
        background-color: #ffffff;
        border: 1px solid #e3e6ea;
        box-shadow: none;
        font-size: 12px;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #email-marketing-campaign-edit-root .form-control::placeholder {
        color: #adb5bd;
        opacity: 1;
    }

    #email-marketing-campaign-edit-root .form-control:focus,
    #email-marketing-campaign-edit-root .form-select:focus {
        border-color: #8bb8f7;
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .09);
        outline: none;
    }

    #email-marketing-campaign-edit-root .form-control:disabled,
    #email-marketing-campaign-edit-root .form-select:disabled {
        background-color: #f5f6f8;
        color: #8d9498;
        cursor: not-allowed;
        opacity: 1;
    }

    #email-marketing-campaign-edit-root .form-text,
    #email-marketing-campaign-edit-root small.text-muted {
        color: var(--campaign-muted) !important;
        font-size: 10.5px !important;
        line-height: 1.5;
    }

    #email-marketing-campaign-edit-root .input-group .btn {
        min-height: 38px;
    }

    /* Quick personalization tags */
    #email-marketing-campaign-edit-root .tag-chip {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 8px;
        color: #526174;
        background: #f4f6f9;
        border: 1px solid #e5e9ef;
        font-size: 10px;
        font-weight: 600;
        cursor: pointer;
        user-select: none;
        transition: all .15s ease;
    }

    #email-marketing-campaign-edit-root .tag-chip:hover {
        color: #ffffff;
        background: var(--campaign-primary);
        border-color: var(--campaign-primary);
    }

    /* Inbox preview */
    #email-marketing-campaign-edit-root .inbox-preview-box {
        padding: 14px;
        background: #f8f9fb;
        border: 1px dashed #d9dfe7;
    }

    #email-marketing-campaign-edit-root .inbox-row-simulator {
        padding: 12px 14px;
        background: #ffffff;
        border: 1px solid #e8ebef;
        border-left: 3px solid var(--campaign-primary) !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .035);
    }

    #email-marketing-campaign-edit-root #simSenderName {
        color: #343a40 !important;
        font-size: 12px !important;
        font-weight: 700;
    }

    #email-marketing-campaign-edit-root #simSubjectText {
        color: #343a40 !important;
        font-size: 11.5px;
    }

    #email-marketing-campaign-edit-root #simSnippetText {
        color: #8d9498 !important;
        font-size: 11px;
    }

    /* Alerts */
    #email-marketing-campaign-edit-root .alert {
        padding: 12px 14px !important;
        border: 1px solid transparent !important;
        font-size: 12px;
        line-height: 1.55;
    }

    #email-marketing-campaign-edit-root .alert-danger {
        background: #fff5f5;
        border-color: #f8d7da !important;
    }

    #email-marketing-campaign-edit-root .alert-warning {
        background: #fff9ed;
        border-color: #ffe6b3 !important;
    }

    #email-marketing-campaign-edit-root .alert ul {
        margin-top: 5px;
    }

    /* Metadata rows */
    #email-marketing-campaign-edit-root .metadata-content {
        color: #7b838a;
        font-size: 11px;
        line-height: 1.55;
    }

    #email-marketing-campaign-edit-root .metadata-content strong,
    #email-marketing-campaign-edit-root .metadata-content .text-dark {
        color: #495057 !important;
    }

    #email-marketing-campaign-edit-root .metadata-content .border-bottom {
        border-color: #f0f1f2 !important;
    }

    /* Compliance card */
    #email-marketing-campaign-edit-root .compliance-card {
        background: #ffffff;
        border: 1px solid var(--campaign-border) !important;
    }

    #email-marketing-campaign-edit-root .compliance-card h6 {
        color: var(--campaign-heading) !important;
        font-size: 12px;
    }

    #email-marketing-campaign-edit-root .compliance-card p {
        color: var(--campaign-muted) !important;
        font-size: 11px !important;
        line-height: 1.6 !important;
    }

    /* Responsive layout */
    @media (min-width: 1200px) {
        #email-marketing-campaign-edit-root .campaign-action-card {
            position: sticky;
            top: 16px;
            z-index: 2;
        }
    }

    @media (max-width: 1199.98px) {
        #email-marketing-campaign-edit-root .campaign-action-card {
            position: static;
        }
    }

    @media (max-width: 767.98px) {
        #email-marketing-campaign-edit-root {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        #email-marketing-campaign-edit-root .campaign-edit-header {
            align-items: flex-start !important;
            gap: 12px !important;
            padding-bottom: 14px;
        }

        #email-marketing-campaign-edit-root .campaign-edit-header h5 {
            font-size: 16px !important;
        }

        #email-marketing-campaign-edit-root .header-actions-wrap {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 7px;
        }

        #email-marketing-campaign-edit-root .header-actions-wrap > a,
        #email-marketing-campaign-edit-root .header-actions-wrap > button,
        #email-marketing-campaign-edit-root .header-actions-wrap > form {
            width: 100%;
        }

        #email-marketing-campaign-edit-root .header-actions-wrap .btn-save-main {
            grid-column: 1 / -1;
        }

        #email-marketing-campaign-edit-root .card {
            margin-bottom: 12px !important;
        }

        #email-marketing-campaign-edit-root .section-header-wrap {
            min-height: 46px;
            padding: 10px 12px;
        }

        #email-marketing-campaign-edit-root .card > .p-3,
        #email-marketing-campaign-edit-root .collapse > .p-3 {
            padding: 13px !important;
        }

        #email-marketing-campaign-edit-root .inbox-preview-box {
            padding: 10px;
        }

        #email-marketing-campaign-edit-root .inbox-row-simulator {
            padding: 10px;
        }

        #email-marketing-campaign-edit-root .inbox-row-simulator #simSenderName {
            max-width: 65%;
        }
    }

    @media (max-width: 420px) {
        #email-marketing-campaign-edit-root .header-actions-wrap .btn {
            font-size: 10px !important;
            padding-left: 7px !important;
            padding-right: 7px !important;
        }

        #email-marketing-campaign-edit-root .section-header-wrap > span {
            font-size: 11.5px !important;
        }

        #email-marketing-campaign-edit-root .form-control,
        #email-marketing-campaign-edit-root .form-select {
            font-size: 11.5px;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-campaign-edit-root">

    {{-- 1. Header & Navigation --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 campaign-edit-header">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i class="fas fa-pencil text-primary"></i>
                    <span>Edit Campaign: <span class="text-secondary">{{ $campaign->name }}</span></span>
                </h5>
                <span class="badge {{ $badgeClass }} font-monospace px-2 py-1" style="font-size: 10px;">
                    {{ strtoupper($campaign->status) }}
                </span>
            </div>
            <small class="text-muted d-block">
                Modify campaign details, subject line, target audience, and broadcast scheduling.
            </small>
        </div>

        <div class="d-flex gap-2 header-actions-wrap">
            <a href="{{ route('admin.email_marketing.campaigns.show', $campaign->id) }}" class="btn btn-sm btn-outline-primary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-chart-line me-1"></i> <span>View Overview</span>
            </a>
            <a href="{{ route('admin.email_marketing.campaigns.index') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-arrow-left me-1"></i> <span>Back to Campaigns</span>
            </a>
            @if($campaign->isEditable())
                <button type="button" class="btn btn-sm btn-primary btn-compact shadow-xs fw-bold btn-save-main" onclick="document.getElementById('campaignEditForm').submit()">
                    <i class="fas fa-save me-1"></i> <span>Save Changes</span>
                </button>
            @endif
        </div>
    </div>

    {{-- 2. Validation Error Alerts --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show small shadow-xs mb-3" role="alert">
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

    {{-- 3. Non-Editable Notice --}}
    @if(!$campaign->isEditable())
        <div class="alert alert-warning shadow-xs mb-3 small d-flex align-items-center gap-2" role="alert">
            <i class="fas fa-lock text-warning-emphasis fs-5"></i>
            <div>
                <strong>Notice:</strong> This campaign is currently <strong>{{ strtoupper($campaign->status) }}</strong>. Active or completed broadcasts cannot have their message contents modified.
            </div>
        </div>
    @endif

    <form action="{{ route('admin.email_marketing.campaigns.update', $campaign->id) }}" method="POST" id="campaignEditForm">
        @csrf

        <div class="row g-3">
            {{-- Left Column: Core Campaign Settings & Messaging --}}
            <div class="col-12 col-xl-8">

                {{-- 1. Campaign Identity & Headers --}}
                <div class="card mb-3">
                    <div class="section-header-wrap d-flex justify-content-between align-items-center">
                        <span class="fw-bold small">
                            <i class="fas fa-envelope-open-text text-primary me-1"></i> 1. Campaign &amp; Subject Details
                        </span>
                        <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseCampaignDetails" aria-expanded="true" aria-controls="collapseCampaignDetails" title="Toggle Section">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>
                    <div class="collapse show" id="collapseCampaignDetails">
                        <div class="p-3">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label" for="campaign_name">
                                        Campaign Name (Internal Reference) <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="name" id="campaign_name" class="form-control" placeholder="Flash Sale Announcement - Weekend Special" value="{{ old('name', $campaign->name) }}" required {{ !$campaign->isEditable() ? 'disabled' : '' }}>
                                    <small class="text-muted d-block mt-1">Internal name used to distinguish campaigns in performance analytics.</small>
                                </div>

                                <div class="col-12">
                                    <label class="form-label d-flex justify-content-between align-items-center" for="campaignSubjectInput">
                                        <span>Email Subject Line <span class="text-danger">*</span></span>
                                        <span class="text-muted font-monospace" style="font-size: 10px;" id="subjectCharCount">0 chars</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="text" name="subject" id="campaignSubjectInput" class="form-control" value="{{ old('subject', $campaign->subject) }}" required oninput="updateInboxPreview()" {{ !$campaign->isEditable() ? 'disabled' : '' }}>
                                        @if($campaign->isEditable())
                                            <button type="button" class="btn btn-outline-secondary btn-compact shadow-none font-monospace" onclick="insertTagIntoSubject('{first_name}')" title="Insert Recipient's First Name">
                                                <i class="fas fa-user-tag me-1 text-primary"></i> +{first_name}
                                            </button>
                                        @endif
                                    </div>
                                    @if($campaign->isEditable())
                                        <div class="d-flex flex-wrap align-items-center gap-1 mt-2">
                                            <span class="text-muted font-monospace" style="font-size: 10px;">Quick Tags:</span>
                                            <span class="tag-chip" onclick="insertTagIntoSubject('{first_name}')">+{first_name}</span>
                                            <span class="tag-chip" onclick="insertTagIntoSubject('{customer_name}')">+{customer_name}</span>
                                            <span class="tag-chip" onclick="insertTagIntoSubject('{coupon_code}')">+{coupon_code}</span>
                                            <span class="tag-chip" onclick="insertTagIntoSubject('{store_name}')">+{store_name}</span>
                                        </div>
                                    @endif
                                    <small class="text-muted d-block mt-1">Visible directly in recipient inboxes. Personalization tags supported.</small>
                                </div>

                                <div class="col-12">
                                    <label class="form-label d-flex justify-content-between align-items-center" for="campaignPreviewTextInput">
                                        <span>Preview / Snippet Text</span>
                                        <span class="text-muted font-monospace" style="font-size: 10px;" id="previewCharCount">0 chars</span>
                                    </label>
                                    <input type="text" name="preview_text" id="campaignPreviewTextInput" class="form-control" value="{{ old('preview_text', $campaign->preview_text) }}" placeholder="Discover our exclusive weekly arrivals..." oninput="updateInboxPreview()" {{ !$campaign->isEditable() ? 'disabled' : '' }}>
                                    <small class="text-muted d-block mt-1">Preheader snippet shown next to the subject line in modern mail clients.</small>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="campaign_sender_name">Sender Display Name</label>
                                    <input type="text" name="sender_name" id="campaign_sender_name" class="form-control" value="{{ old('sender_name', $campaign->sender_name) }}" placeholder="Shop Now BD" oninput="updateInboxPreview()" {{ !$campaign->isEditable() ? 'disabled' : '' }}>
                                    <small class="text-muted d-block mt-1">Leave blank to fall back to global store sender name.</small>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="campaign_sender_email">Sender Email Address</label>
                                    <input type="email" name="sender_email" id="campaign_sender_email" class="form-control font-monospace" value="{{ old('sender_email', $campaign->sender_email) }}" placeholder="newsletter@shopnowbd.online" {{ !$campaign->isEditable() ? 'disabled' : '' }}>
                                    <small class="text-muted d-block mt-1">Must match an authorized domain in your provider settings.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. Target Audience & Template Selection --}}
                <div class="card mb-3">
                    <div class="section-header-wrap d-flex justify-content-between align-items-center">
                        <span class="fw-bold small">
                            <i class="fas fa-layer-group text-success me-1"></i> 2. Audience &amp; Content Design
                        </span>
                        <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseAudienceDesign" aria-expanded="true" aria-controls="collapseAudienceDesign" title="Toggle Section">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>
                    <div class="collapse show" id="collapseAudienceDesign">
                        <div class="p-3">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="campaign_segment_id">Target Audience Segment</label>
                                    <select name="segment_id" id="campaign_segment_id" class="form-select font-monospace shadow-none" {{ !$campaign->isEditable() ? 'disabled' : '' }}>
                                        <option value="">-- All Consented Subscribers (Broadcast) --</option>
                                        @foreach($segments as $segment)
                                            <option value="{{ $segment->id }}" {{ old('segment_id', $campaign->segment_id) ==$segment->id ? 'selected' : '' }}>
                                                {{ $segment->name }} ({{ number_format($segment->matching_subscribers_count) }} subscribers)
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted d-block mt-1">Restricts broadcast to specific customer behaviors.</small>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="campaign_template_id">
                                        Email Design Template <span class="text-danger">*</span>
                                    </label>
                                    <select name="template_id" id="campaign_template_id" class="form-select font-monospace shadow-none" required {{ !$campaign->isEditable() ? 'disabled' : '' }}>
                                        <option value="">-- Select Design Template --</option>
                                        @foreach($templates as $template)
                                            <option value="{{ $template->id }}" {{ old('template_id', $campaign->template_id) ==$template->id ? 'selected' : '' }}>
                                                {{ $template->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted d-block mt-1">
                                        View designs in <a href="{{ route('admin.email_marketing.templates.index') }}" target="_blank" class="text-primary text-decoration-none fw-semibold">Template Builder</a>.
                                    </small>
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="campaign_coupon_id">Attached Promotional Coupon (Optional)</label>
                                    <select name="coupon_id" id="campaign_coupon_id" class="form-select font-monospace shadow-none" {{ !$campaign->isEditable() ? 'disabled' : '' }}>
                                        <option value="">-- No Coupon Attached --</option>
                                        @foreach($coupons as $coupon)
                                            <option value="{{ $coupon->id }}" {{ old('coupon_id', $campaign->coupon_id) ==$coupon->id ? 'selected' : '' }}>
                                                Code: {{ $coupon->code }} &bull; Discount: ৳{{ number_format($coupon->discount_amount ?? 0) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted d-block mt-1">Injected into the email body via the <code>{coupon_code}</code> dynamic token.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. Live Customer Inbox Simulation --}}
                <div class="card mb-3">
                    <div class="section-header-wrap d-flex justify-content-between align-items-center">
                        <span class="fw-bold small">
                            <i class="fas fa-eye text-info me-1"></i> Live Customer Inbox Simulation
                        </span>
                        <span class="badge bg-light text-secondary border font-monospace px-2 py-1" style="font-size: 9.5px;">
                            GMAIL / APPLE MAIL SIMULATOR
                        </span>
                    </div>
                    <div class="p-3 bg-light bg-opacity-50">
                        <div class="inbox-preview-box">
                            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                                <span class="small text-muted font-monospace" style="font-size: 10px;">
                                    <i class="fas fa-inbox me-1"></i> Real-time Inbox Display
                                </span>
                                <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace" style="font-size: 9px;">
                                    100% Responsive
                                </span>
                            </div>
                            <div class="inbox-row-simulator">
                                <div class="d-flex align-items-center justify-content-between mb-2 gap-2">
                                    <strong class="text-dark d-block text-truncate" id="simSenderName" style="font-size: 12px; max-width: 240px;">
                                        {{ $campaign->sender_name ?? config('mail.from.name', 'Shop Now BD') }}
                                    </strong>
                                    <span class="text-muted font-monospace small flex-shrink-0" style="font-size: 10px;">Just now</span>
                                </div>
                                <div class="text-truncate" style="font-size: 11.5px; line-height: 1.5;">
                                    <strong class="text-dark" id="simSubjectText">{{ $campaign->subject }}</strong>
                                    <span class="text-muted ms-1" id="simSnippetText">— {{ $campaign->preview_text ?? 'Discover our exclusive weekly arrivals...' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Right Column: Scheduling & Submission --}}
            <div class="col-12 col-xl-4">

                {{-- Action Panel Card --}}
                <div class="card campaign-action-card mb-3">
                    <div class="p-3">
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-xs btn-compact mb-2" style="font-size: 12px !important;" {{ !$campaign->isEditable() ? 'disabled' : '' }}>
                            <i class="fas fa-save me-1"></i> Update Campaign
                        </button>
                        <a href="{{ route('admin.email_marketing.campaigns.show', $campaign->id) }}" class="btn btn-light border w-100 py-2 btn-compact shadow-xs text-secondary">
                            Cancel
                        </a>
                    </div>
                </div>

                {{-- Delivery Schedule Settings Card --}}
                <div class="card mb-3">
                    <div class="section-header-wrap d-flex justify-content-between align-items-center">
                        <span class="fw-bold small">
                            <i class="fas fa-clock text-warning me-1"></i> Schedule Settings
                        </span>
                        <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseScheduleSettings" aria-expanded="true" aria-controls="collapseScheduleSettings" title="Toggle Schedule">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>
                    <div class="collapse show" id="collapseScheduleSettings">
                        <div class="p-3">
                            <div class="mb-1">
                                <label class="form-label" for="scheduled_at_input">Broadcast Date &amp; Time</label>
                                <input type="datetime-local" name="scheduled_at" id="scheduled_at_input" class="form-control font-monospace bn-digit shadow-none" value="{{ old('scheduled_at', $scheduledFormatted) }}" {{ !$campaign->isEditable() ? 'disabled' : '' }}>
                                <small class="text-muted d-block mt-1">Clear value to keep campaign in <strong>Draft</strong> mode.</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Campaign Lifecycle Health & Metadata Card --}}
                <div class="card mb-3">
                    <div class="section-header-wrap d-flex justify-content-between align-items-center">
                        <span class="fw-bold small">
                            <i class="fas fa-circle-info text-info me-1"></i> Campaign Metadata &amp; Health
                        </span>
                        <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseCampaignMeta" aria-expanded="true" aria-controls="collapseCampaignMeta" title="Toggle Health">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>
                    <div class="collapse show" id="collapseCampaignMeta">
                        <div class="p-3 small text-muted metadata-content">
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom gap-2">
                                <span>Current Status:</span>
                                <span class="badge {{ $badgeClass }} font-monospace" style="font-size: 9.5px;">
                                    {{ strtoupper($campaign->status) }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom gap-2">
                                <span>Recipients Target:</span>
                                <strong class="text-dark font-monospace bn-digit">{{ number_format($campaign->total_recipients) }} users</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom gap-2">
                                <span>Created Date:</span>
                                <span class="text-dark font-monospace bn-digit">{{ $campaign->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-2 gap-2">
                                <span>Last Updated:</span>
                                <span class="text-dark font-monospace bn-digit">{{ $campaign->updated_at->format('M d, Y - h:i A') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Compliance Notice Card --}}
                <div class="card p-3 compliance-card">
                    <h6 class="fw-bold text-dark mb-2 small d-flex align-items-center gap-2">
                        <i class="fas fa-shield-halved text-success"></i> <span>Anti-Spam Compliance</span>
                    </h6>
                    <p class="text-muted small mb-0">
                        Mandatory 1-click unsubscribe links and open tracking pixels are automatically embedded into the footer of every outbound email.
                    </p>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
    function insertTagIntoSubject(tag) {
        const input = document.getElementById('campaignSubjectInput');
        if (input && !input.disabled) {
            const start = input.selectionStart || 0;
            const end   = input.selectionEnd || 0;
            const text  = input.value;
            input.value = text.substring(0, start) + ' ' + tag + ' ' + text.substring(end);
            input.focus();
            updateInboxPreview();
        }
    }

    function updateInboxPreview() {
        const subjectInput = document.getElementById('campaignSubjectInput');
        const previewInput = document.getElementById('campaignPreviewTextInput');
        const senderInput  = document.getElementById('campaign_sender_name');

        const simSender  = document.getElementById('simSenderName');
        const simSubject = document.getElementById('simSubjectText');
        const simSnippet = document.getElementById('simSnippetText');

        const subjLen = (subjectInput?.value || '').length;
        const prevLen = (previewInput?.value || '').length;

        const subjectCountEl = document.getElementById('subjectCharCount');
        const previewCountEl = document.getElementById('previewCharCount');

        if (subjectCountEl) subjectCountEl.innerText = `${subjLen} chars`;
        if (previewCountEl) previewCountEl.innerText = `${prevLen} chars`;

        if (simSender) {
            simSender.innerText = senderInput?.value.trim() || '{{ $campaign->sender_name ?? config('mail.from.name', 'Shop Now BD') }}';
        }
        if (simSubject) {
            simSubject.innerText = subjectInput?.value.trim() || '{{ addslashes($campaign->subject) }}';
        }
        if (simSnippet) {
            const snipVal = previewInput?.value.trim();
            simSnippet.innerText = snipVal ? `— ${snipVal}` : '— Discover our exclusive weekly arrivals...';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateInboxPreview();
    });
</script>
@endsection