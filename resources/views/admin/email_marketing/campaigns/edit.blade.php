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
    /* Strict 5px Element Radius Across All Controls */
    #email-marketing-campaign-edit-root .btn,
    #email-marketing-campaign-edit-root .btn-sm,
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
    #email-marketing-campaign-edit-root .card {
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
    #email-marketing-campaign-edit-root .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #email-marketing-campaign-edit-root .form-control,
    #email-marketing-campaign-edit-root .form-select {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #email-marketing-campaign-edit-root .form-control:focus,
    #email-marketing-campaign-edit-root .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Quick Tag Insertion Chips */
    .tag-chip {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        padding: 2px 7px;
        font-size: 10px;
        font-weight: 600;
        color: #475569;
        background-color: #f1f5f9;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        user-select: none;
        transition: all 0.15s ease;
    }
    .tag-chip:hover {
        background-color: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
    }

    /* Live Customer Inbox Simulation Preview Box */
    .inbox-preview-box {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        padding: 12px;
    }
    .inbox-row-simulator {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        padding: 8px 12px;
        border-left: 3.5px solid #2563eb !important;
    }

    /* Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .campaign-edit-header h5 {
            font-size: 16px !important;
        }
        .header-actions-wrap {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .header-actions-wrap .btn,
        .header-actions-wrap form {
            width: 100%;
        }
        .header-actions-wrap .btn-save-main {
            grid-column: span 2;
        }
        #email-marketing-campaign-edit-root .card {
            margin-bottom: 12px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-campaign-edit-root">

    {{-- 1. Header & Navigation (Original Clean Light Theme) --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 campaign-edit-header">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                    <i class="fas fa-pencil text-primary"></i>
                    <span>Edit Campaign: <span class="text-secondary">{{ $campaign->name }}</span></span>
                </h5>
                <span class="badge {{ $badgeClass }} font-monospace px-2 py-1" style="font-size: 10px;">
                    {{ strtoupper($campaign->status) }}
                </span>
            </div>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
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

    {{-- 3. Non-Editable Notice --}}
    @if(!$campaign->isEditable())
        <div class="alert alert-warning border-0 shadow-xs mb-3 py-2 px-3 small d-flex align-items-center gap-2" role="alert" style="border-radius: 5px;">
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

                {{-- 1. Campaign Identity & Headers (Collapsible) --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
                        <span class="fw-bold text-dark small" style="font-size: 12.5px;">
                            <i class="fas fa-envelope-open-text text-primary me-1"></i> 1. Campaign &amp; Subject Details
                        </span>
                        <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseCampaignDetails" aria-expanded="true" aria-controls="collapseCampaignDetails" title="Toggle Section">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>
                    <div class="collapse show" id="collapseCampaignDetails">
                        <div class="p-3">
                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="form-label" for="campaign_name">
                                        Campaign Name (Internal Reference) <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="name" id="campaign_name" class="form-control" placeholder="e.g. Flash Sale Announcement - Weekend Special" value="{{ old('name', $campaign->name) }}" required {{ !$campaign->isEditable() ? 'disabled' : '' }}>
                                    <small class="text-muted d-block mt-1" style="font-size: 10px;">Internal name used to distinguish campaigns in performance analytics.</small>
                                </div>

                                <div class="col-12 mt-2">
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
                                        <div class="d-flex flex-wrap align-items-center gap-1 mt-1">
                                            <span class="text-muted font-monospace" style="font-size: 10px;">Quick Tags:</span>
                                            <span class="tag-chip" onclick="insertTagIntoSubject('{first_name}')">+{first_name}</span>
                                            <span class="tag-chip" onclick="insertTagIntoSubject('{customer_name}')">+{customer_name}</span>
                                            <span class="tag-chip" onclick="insertTagIntoSubject('{coupon_code}')">+{coupon_code}</span>
                                            <span class="tag-chip" onclick="insertTagIntoSubject('{store_name}')">+{store_name}</span>
                                        </div>
                                    @endif
                                    <small class="text-muted d-block mt-1" style="font-size: 10px;">Visible directly in recipient inboxes. Personalization tags supported.</small>
                                </div>

                                <div class="col-12 mt-2">
                                    <label class="form-label d-flex justify-content-between align-items-center" for="campaignPreviewTextInput">
                                        <span>Preview / Snippet Text</span>
                                        <span class="text-muted font-monospace" style="font-size: 10px;" id="previewCharCount">0 chars</span>
                                    </label>
                                    <input type="text" name="preview_text" id="campaignPreviewTextInput" class="form-control" value="{{ old('preview_text', $campaign->preview_text) }}" placeholder="e.g. Discover our exclusive weekly arrivals..." oninput="updateInboxPreview()" {{ !$campaign->isEditable() ? 'disabled' : '' }}>
                                    <small class="text-muted d-block mt-1" style="font-size: 10px;">Preheader snippet shown next to the subject line in modern mail clients.</small>
                                </div>

                                <div class="col-12 col-md-6 mt-2">
                                    <label class="form-label" for="campaign_sender_name">Sender Display Name</label>
                                    <input type="text" name="sender_name" id="campaign_sender_name" class="form-control" value="{{ old('sender_name', $campaign->sender_name) }}" placeholder="e.g. Shop Now BD" oninput="updateInboxPreview()" {{ !$campaign->isEditable() ? 'disabled' : '' }}>
                                    <small class="text-muted d-block mt-1" style="font-size: 10px;">Leave blank to fall back to global store sender name.</small>
                                </div>

                                <div class="col-12 col-md-6 mt-2">
                                    <label class="form-label" for="campaign_sender_email">Sender Email Address</label>
                                    <input type="email" name="sender_email" id="campaign_sender_email" class="form-control font-monospace" value="{{ old('sender_email', $campaign->sender_email) }}" placeholder="e.g. newsletter@shopnowbd.online" {{ !$campaign->isEditable() ? 'disabled' : '' }}>
                                    <small class="text-muted d-block mt-1" style="font-size: 10px;">Must match an authorized domain in your provider settings.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. Target Audience & Template Selection (Collapsible) --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
                        <span class="fw-bold text-dark small" style="font-size: 12.5px;">
                            <i class="fas fa-layer-group text-success me-1"></i> 2. Audience &amp; Content Design
                        </span>
                        <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseAudienceDesign" aria-expanded="true" aria-controls="collapseAudienceDesign" title="Toggle Section">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>
                    <div class="collapse show" id="collapseAudienceDesign">
                        <div class="p-3">
                            <div class="row g-2">
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
                                    <small class="text-muted d-block mt-1" style="font-size: 10px;">Restricts broadcast to specific customer behaviors.</small>
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
                                    <small class="text-muted d-block mt-1" style="font-size: 10px;">
                                        View designs in <a href="{{ route('admin.email_marketing.templates.index') }}" target="_blank" class="text-primary text-decoration-none fw-semibold">Template Builder</a>.
                                    </small>
                                </div>

                                <div class="col-12 mt-2">
                                    <label class="form-label" for="campaign_coupon_id">Attached Promotional Coupon (Optional)</label>
                                    <select name="coupon_id" id="campaign_coupon_id" class="form-select font-monospace shadow-none" {{ !$campaign->isEditable() ? 'disabled' : '' }}>
                                        <option value="">-- No Coupon Attached --</option>
                                        @foreach($coupons as $coupon)
                                            <option value="{{ $coupon->id }}" {{ old('coupon_id', $campaign->coupon_id) ==$coupon->id ? 'selected' : '' }}>
                                                Code: {{ $coupon->code }} &bull; Discount: ৳{{ number_format($coupon->discount_amount ?? 0) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted d-block mt-1" style="font-size: 10px;">Injected into the email body via the <code>{coupon_code}</code> dynamic token.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. Live Customer Inbox Simulation Card (200% Visual Upgrade) --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
                        <span class="fw-bold text-dark small" style="font-size: 12.5px;">
                            <i class="fas fa-eye text-info me-1"></i> Live Customer Inbox Simulation
                        </span>
                        <span class="badge bg-light text-secondary border font-monospace px-2 py-0" style="font-size: 9.5px;">
                            GMAIL / APPLE MAIL SIMULATOR
                        </span>
                    </div>
                    <div class="p-3 bg-light bg-opacity-50">
                        <div class="inbox-preview-box" style="border-radius: 5px;">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="small text-muted font-monospace" style="font-size: 10px;">
                                    <i class="fas fa-inbox me-1"></i> Real-time Inbox Display
                                </span>
                                <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace" style="font-size: 9px;">
                                    100% Responsive
                                </span>
                            </div>
                            <div class="inbox-row-simulator shadow-xs" style="border-radius: 5px;">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <strong class="text-dark d-block text-truncate" id="simSenderName" style="font-size: 12px; max-width: 240px;">
                                        {{ $campaign->sender_name ?? config('mail.from.name', 'Shop Now BD') }}
                                    </strong>
                                    <span class="text-muted font-monospace small" style="font-size: 10px;">Just now</span>
                                </div>
                                <div class="text-truncate" style="font-size: 11.5px; line-height: 1.35;">
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
                <div class="card border-0 shadow-sm mb-3">
                    <div class="p-3">
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-xs btn-compact mb-2" style="font-size: 12px !important;" {{ !$campaign->isEditable() ? 'disabled' : '' }}>
                            <i class="fas fa-save me-1"></i> Update Campaign
                        </button>
                        <a href="{{ route('admin.email_marketing.campaigns.show', $campaign->id) }}" class="btn btn-light border w-100 py-2 btn-compact shadow-xs text-secondary">
                            Cancel
                        </a>
                    </div>
                </div>

                {{-- Delivery Schedule Settings Card (Collapsible) --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
                        <span class="fw-bold text-dark small" style="font-size: 12px;">
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
                                <small class="text-muted d-block mt-1" style="font-size: 10px;">Clear value to keep campaign in <strong>Draft</strong> mode.</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Campaign Lifecycle Health & Metadata Card (Collapsible) --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
                        <span class="fw-bold text-dark small" style="font-size: 12px;">
                            <i class="fas fa-circle-info text-info me-1"></i> Campaign Metadata &amp; Health
                        </span>
                        <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseCampaignMeta" aria-expanded="true" aria-controls="collapseCampaignMeta" title="Toggle Health">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>
                    <div class="collapse show" id="collapseCampaignMeta">
                        <div class="p-3 small text-muted" style="font-size: 11px; line-height: 1.55;">
                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                <span>Current Status:</span>
                                <span class="badge {{ $badgeClass }} font-monospace" style="font-size: 9.5px;">
                                    {{ strtoupper($campaign->status) }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                <span>Recipients Target:</span>
                                <strong class="text-dark font-monospace bn-digit">{{ number_format($campaign->total_recipients) }} users</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                <span>Created Date:</span>
                                <span class="text-dark font-monospace bn-digit">{{ $campaign->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-1">
                                <span>Last Updated:</span>
                                <span class="text-dark font-monospace bn-digit">{{ $campaign->updated_at->format('M d, Y - h:i A') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Compliance Notice Card --}}
                <div class="card p-3 shadow-xs">
                    <h6 class="fw-bold text-dark mb-1 small d-flex align-items-center gap-1">
                        <i class="fas fa-shield-halved text-success"></i> <span>Anti-Spam Compliance</span>
                    </h6>
                    <p class="text-muted small mb-0" style="font-size: 11px; line-height: 1.4;">
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