@extends('layouts.admin')

@section('title', 'Global Product Review Settings - ' . config('app.name'))

@php
    $isReviewsEnabled   = !empty($settings['reviews_enabled']);
    $approvalMode       = $settings['approval_mode'] ?? 'auto';
    $isVerifiedOnly     = !empty($settings['verified_buyer_only']);
    $isAutoReplyEnabled = !empty($settings['auto_reply_enabled']);
@endphp

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #reviewSettingsRoot .btn,
    #reviewSettingsRoot .btn-sm,
    #reviewSettingsRoot button,
    #reviewSettingsRoot .badge,
    #reviewSettingsRoot .form-control,
    #reviewSettingsRoot .form-select,
    #reviewSettingsRoot .card,
    #reviewSettingsRoot .metric-card,
    #reviewSettingsRoot .settings-header-card,
    #reviewSettingsRoot .settings-panel-card,
    #reviewSettingsRoot .alert {
        border-radius: 5px !important;
    }

    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
        font-variant-numeric: tabular-nums;
    }

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

    /* Clean White Enterprise Header Card */
    .settings-header-card {
        background: #ffffff;
        color: #0f172a;
        padding: 14px 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 14px;
    }

    /* Metric Cards - Zero Overflow Architecture */
    .metric-card {
        padding: 12px 14px;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    }
    .stat-label-title {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.02em;
        line-height: 1.2;
    }
    .stat-val-num {
        font-size: 16px;
        line-height: 1.25;
        letter-spacing: -0.01em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .stat-sub-text {
        font-size: 10px;
        margin-top: 2px;
    }
    .metric-icon-square {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
        border-radius: 5px !important;
    }

    .metric-card-blue   { background: linear-gradient(180deg, #ffffff 0%, #f0f9ff 100%); border-left: 3.5px solid #2563eb !important; }
    .metric-card-amber  { background: linear-gradient(180deg, #ffffff 0%, #fffbeb 100%); border-left: 3.5px solid #f59e0b !important; }
    .metric-card-green  { background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%); border-left: 3.5px solid #10b981 !important; }
    .metric-card-purple { background: linear-gradient(180deg, #ffffff 0%, #faf5ff 100%); border-left: 3.5px solid #a855f7 !important; }

    .settings-panel-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        padding: 14px 16px;
        margin-bottom: 14px;
        transition: opacity 0.2s ease, border-color 0.2s ease;
    }

    .settings-section-title {
        font-size: 12px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .form-check-label {
        font-size: 12px;
        font-weight: 600;
        color: #1e293b;
        cursor: pointer;
    }
    .form-check-input {
        cursor: pointer;
        width: 16px;
        height: 16px;
        border-radius: 4px !important;
    }
    .form-check-input:checked {
        background-color: #2563eb;
        border-color: #2563eb;
    }
    .settings-hint-text {
        font-size: 10.5px;
        color: #64748b;
        margin-top: 2px;
        line-height: 1.35;
    }

    #reviewSettingsRoot .form-control,
    #reviewSettingsRoot .form-select {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 4px 8px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #reviewSettingsRoot .form-control:focus,
    #reviewSettingsRoot .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* 100/100 Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .settings-header-card {
            padding: 12px 14px !important;
        }
        .header-actions-group {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .header-actions-group .btn {
            width: 100%;
            padding: 8px 6px !important;
            font-size: 11px !important;
            justify-content: center;
        }
        .metric-card {
            padding: 10px !important;
            min-height: 80px !important;
        }
        .stat-label-title {
            font-size: 10px !important;
        }
        .stat-val-num {
            font-size: 14px !important;
        }
        .stat-sub-text {
            font-size: 9px !important;
        }
        .metric-icon-square {
            width: 28px !important;
            height: 28px !important;
            font-size: 11px !important;
        }
        .bottom-save-bar {
            width: 100%;
        }
        .bottom-save-bar .btn {
            width: 100% !important;
            justify-content: center;
            padding: 10px 14px !important;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="reviewSettingsRoot">

    <!-- 1. Header Banner Card (Clean White Light Theme) -->
    <div class="settings-header-card d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace text-uppercase" style="font-size: 10px;">
                    <i class="fas fa-gear me-1"></i> REVIEW CONFIGURATION
                </span>
                <span class="badge bg-light text-secondary border font-monospace" style="font-size: 10px;">
                    GLOBAL POLICY ENGINE
                </span>
            </div>
            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-sliders text-primary"></i>
                <span>Global Product Review Settings</span>
            </h5>
            <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">
                Control storefront review display, approval policies, image uploads, spam protection and alert notifications.
            </small>
        </div>

        <div class="header-actions-group d-flex flex-wrap gap-1">
            <a href="{{ route('admin.product.reviews.index') }}" class="btn btn-outline-secondary btn-compact shadow-xs bg-white text-dark fw-semibold">
                <i class="fas fa-arrow-left me-1"></i> Back to Reviews
            </a>
            <a href="{{ route('admin.product.reviews.auto_rules') }}" class="btn btn-outline-success btn-compact shadow-xs bg-white fw-semibold">
                <i class="fas fa-robot me-1"></i> Auto-Reply Rules
            </a>
        </div>
    </div>

    <!-- 2. Reviews Status Telemetry KPI Overview -->
    <div class="row g-2 mb-3">
        <!-- KPI 1: Global Review System -->
        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-blue h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Review System</span>
                    <div class="metric-icon-square bg-primary-subtle text-primary shadow-xs">
                        <i class="fas fa-toggle-on"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-dark font-monospace stat-val-num">
                        {{ $isReviewsEnabled ? 'Active Live' : 'Suspended' }}
                    </h4>
                    <small class="text-muted d-block text-truncate font-monospace stat-sub-text">
                        {{ $isReviewsEnabled ? 'Storefront tabs enabled' : 'Completely hidden' }}
                    </small>
                </div>
            </div>
        </div>

        <!-- KPI 2: Default Moderation Mode -->
        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-amber h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Moderation Policy</span>
                    <div class="metric-icon-square bg-warning-subtle text-warning-emphasis shadow-xs">
                        <i class="fas fa-shield-check"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-warning-emphasis font-monospace stat-val-num" style="font-size: 14px;">
                        {{ $approvalMode === 'auto' ? 'Automatic Live' : 'Strict Manual' }}
                    </h4>
                    <small class="text-muted d-block text-truncate font-monospace stat-sub-text">
                        {{ $approvalMode === 'auto' ? 'Instant publication' : 'Queue approval' }}
                    </small>
                </div>
            </div>
        </div>

        <!-- KPI 3: Buyer Restrictions -->
        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-green h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Buyer Eligibility</span>
                    <div class="metric-icon-square bg-success-subtle text-success shadow-xs">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-success font-monospace stat-val-num" style="font-size: 14px;">
                        {{ $isVerifiedOnly ? 'Verified Orders' : 'Public Access' }}
                    </h4>
                    <small class="text-success d-block text-truncate font-monospace stat-sub-text">
                        <i class="fas fa-circle-check me-1"></i>{{ $isVerifiedOnly ? 'Delivered orders only' : 'All registered users' }}
                    </small>
                </div>
            </div>
        </div>

        <!-- KPI 4: Auto-Reply Engine -->
        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-purple h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Auto-Reply Relay</span>
                    <div class="metric-icon-square bg-purple-subtle text-primary shadow-xs" style="background-color: #f3e8ff; color: #7e22ce;">
                        <i class="fas fa-robot"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-dark font-monospace stat-val-num">
                        {{ $isAutoReplyEnabled ? 'Active Engine' : 'Disabled' }}
                    </h4>
                    <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Keyword &amp; rating bots</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 small border-0 shadow-xs d-flex align-items-center justify-content-between mb-3" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 small border-0 shadow-xs d-flex align-items-center justify-content-between mb-3" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-circle text-danger fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger py-2 px-3 small border-0 shadow-xs mb-3 font-monospace" style="font-size: 11px;">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- 3. Form Body -->
    <form action="{{ route('admin.product.reviews.settings.update') }}" method="POST" id="globalReviewSettingsForm">
        @csrf

        <div class="row g-2 g-md-3">
            <!-- Left Column: Visibility, Submission & Approval Policy -->
            <div class="col-12 col-lg-6">
                <!-- 1. Storefront Visibility & Submission -->
                <div class="settings-panel-card">
                    <div class="settings-section-title">
                        <i class="fas fa-toggle-on text-primary"></i>
                        <span>1. Storefront Visibility &amp; Submission</span>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="reviews_enabled" id="setReviewsEnabled" value="1" {{ !empty($settings['reviews_enabled']) ? 'checked' : '' }} onchange="toggleDependentCards()">
                        <label class="form-check-label font-monospace" for="setReviewsEnabled">Enable Review System Globally</label>
                        <div class="settings-hint-text font-monospace">If disabled, the review tab, rating summary, and public comments will be completely hidden from all product pages.</div>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="allow_new_submission" id="setAllowSubmission" value="1" {{ !empty($settings['allow_new_submission']) ? 'checked' : '' }}>
                        <label class="form-check-label font-monospace" for="setAllowSubmission">Allow New Review Submissions</label>
                        <div class="settings-hint-text font-monospace">When turned off, customers cannot write new reviews, but previously approved reviews will remain visible.</div>
                    </div>

                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="verified_buyer_only" id="setVerifiedOnly" value="1" {{ !empty($settings['verified_buyer_only']) ? 'checked' : '' }}>
                        <label class="form-check-label font-monospace" for="setVerifiedOnly">Verified Buyers Restriction (Strict)</label>
                        <div class="settings-hint-text font-monospace">Only logged-in customers who have an actual order with 'Delivered' or 'Completed' status can submit reviews.</div>
                    </div>
                </div>

                <!-- 2. Review Approval & Moderation Policy -->
                <div class="settings-panel-card">
                    <div class="settings-section-title">
                        <i class="fas fa-shield-check text-warning"></i>
                        <span>2. Approval &amp; Moderation Policy</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" style="font-size: 11px;">Default Approval Mode:</label>
                        <select name="approval_mode" class="form-select font-monospace">
                            <option value="auto" {{ ($settings['approval_mode'] ?? 'auto') === 'auto' ? 'selected' : '' }}>Mode A: Automatic Approval (Instant Publish)</option>
                            <option value="manual" {{ ($settings['approval_mode'] ?? 'auto') === 'manual' ? 'selected' : '' }}>Mode B: Manual Approval Required (Held as Pending)</option>
                        </select>
                        <div class="settings-hint-text font-monospace">Choose whether new eligible reviews go live instantly or wait for admin inspection.</div>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="require_approval_image" id="setReqApprovalImage" value="1" {{ !empty($settings['require_approval_image']) ? 'checked' : '' }}>
                        <label class="form-check-label font-monospace" for="setReqApprovalImage">Reviews with Photos Require Approval</label>
                        <div class="settings-hint-text font-monospace">Reviews containing uploaded customer photos will always be held in 'Pending' queue for quality inspection.</div>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="require_approval_first" id="setReqApprovalFirst" value="1" {{ !empty($settings['require_approval_first']) ? 'checked' : '' }}>
                        <label class="form-check-label font-monospace" for="setReqApprovalFirst">First-Time Reviewer Requires Approval</label>
                        <div class="settings-hint-text font-monospace">A customer's very first review on the store will require manual approval to prevent spam bots.</div>
                    </div>

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="auto_hide_reported" id="setAutoHideReported" value="1" {{ !empty($settings['auto_hide_reported']) ? 'checked' : '' }}>
                        <label class="form-check-label font-monospace" for="setAutoHideReported">Auto-Hide Reported Reviews</label>
                        <div class="settings-hint-text font-monospace">Automatically hide reviews from storefront when they receive multiple customer abuse flags.</div>
                    </div>

                    <div class="row g-2 align-items-center pt-1">
                        <div class="col-8">
                            <span class="small text-muted font-monospace" style="font-size: 10.5px;">Report Threshold to Auto-Hide:</span>
                        </div>
                        <div class="col-4">
                            <input type="number" name="report_threshold_hide" class="form-control text-center font-monospace bn-digit" min="1" max="20" value="{{ $settings['report_threshold_hide'] ?? 3 }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Media, Pagination, Notifications & Auto-Reply -->
            <div class="col-12 col-lg-6">
                <!-- 3. Customer Photo Uploads Configuration -->
                <div class="settings-panel-card">
                    <div class="settings-section-title">
                        <i class="fas fa-camera text-success"></i>
                        <span>3. Customer Photo Uploads Configuration</span>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="image_reviews_enabled" id="setImageReviewsEnabled" value="1" {{ !empty($settings['image_reviews_enabled']) ? 'checked' : '' }}>
                        <label class="form-check-label font-monospace" for="setImageReviewsEnabled">Allow Customers to Upload Product Photos</label>
                        <div class="settings-hint-text font-monospace">Enables photo attachment file input in the product details review form.</div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label" style="font-size: 11px;">Max Photos / Review:</label>
                            <input type="number" name="max_images_per_review" class="form-control text-center font-monospace bn-digit" min="1" max="10" value="{{ $settings['max_images_per_review'] ?? 4 }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label" style="font-size: 11px;">Max Size per File (KB):</label>
                            <input type="number" name="max_image_size_kb" class="form-control text-center font-monospace bn-digit" min="512" max="10240" step="512" value="{{ $settings['max_image_size_kb'] ?? 2048 }}">
                        </div>
                    </div>
                    <div class="settings-hint-text font-monospace" style="font-size: 10px;">Supported formats: JPG, JPEG, PNG, WEBP. SVG and executable formats are automatically blocked for security.</div>
                </div>

                <!-- 4. Storefront Pagination & Engagement -->
                <div class="settings-panel-card">
                    <div class="settings-section-title">
                        <i class="fas fa-sliders text-info"></i>
                        <span>4. Storefront Pagination &amp; Customer Interaction</span>
                    </div>

                    <div class="row g-2 align-items-center mb-3">
                        <div class="col-8">
                            <label class="form-label mb-0" style="font-size: 11px;">Reviews Per Page (Frontend PDP):</label>
                            <div class="settings-hint-text font-monospace">Controls how many reviews load initially before pagination.</div>
                        </div>
                        <div class="col-4">
                            <input type="number" name="frontend_per_page" class="form-control text-center font-monospace bn-digit" min="2" max="50" value="{{ $settings['frontend_per_page'] ?? 5 }}">
                        </div>
                    </div>

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="allow_voting" id="setAllowVoting" value="1" {{ !empty($settings['allow_voting']) ? 'checked' : '' }}>
                        <label class="form-check-label font-monospace" for="setAllowVoting">Enable 'Helpful' Voting Button</label>
                        <div class="settings-hint-text font-monospace">Allows store visitors to upvote useful reviews (1 vote per user/IP limit).</div>
                    </div>

                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="allow_reporting" id="setAllowReporting" value="1" {{ !empty($settings['allow_reporting']) ? 'checked' : '' }}>
                        <label class="form-check-label font-monospace" for="setAllowReporting">Enable 'Report Inappropriate' Button</label>
                        <div class="settings-hint-text font-monospace">Allows customers to flag offensive comments directly to admin moderation.</div>
                    </div>
                </div>

                <!-- 5. Notifications & Automation Engine -->
                <div class="settings-panel-card mb-0">
                    <div class="settings-section-title">
                        <i class="fas fa-robot text-danger"></i>
                        <span>5. Automation &amp; Admin Email Alerts</span>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="auto_reply_enabled" id="setAutoReplyEnabled" value="1" {{ !empty($settings['auto_reply_enabled']) ? 'checked' : '' }}>
                        <label class="form-check-label font-monospace" for="setAutoReplyEnabled">Enable Auto-Reply Engine Globally</label>
                        <div class="settings-hint-text font-monospace">Automatically posts replies to customer reviews based on configured rating/keyword rules.</div>
                    </div>

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="email_notification_admin" id="setEmailNotificationAdmin" value="1" {{ !empty($settings['email_notification_admin']) ? 'checked' : '' }}>
                        <label class="form-check-label font-monospace" for="setEmailNotificationAdmin">Send Email Alert on New Review</label>
                        <div class="settings-hint-text font-monospace">Sends real-time email notification to store owner whenever a customer leaves feedback.</div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label" style="font-size: 11px;">Admin Notification Email Recipient:</label>
                        <input type="email" name="admin_notification_email" class="form-control font-monospace" placeholder="admin@example.com" value="{{ $settings['admin_notification_email'] ?? '' }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Sticky Save Action Button Bar -->
        <div class="d-flex justify-content-end gap-2 mt-3 pt-2 border-top bottom-save-bar">
            <button type="submit" class="btn btn-primary btn-compact px-4 shadow-xs fw-bold" style="min-height: 34px;">
                <i class="fas fa-save me-1"></i> Save Review Configuration
            </button>
        </div>
    </form>
</div>

<script>
    function toggleDependentCards() {
        const isReviewsGloballyEnabled = document.getElementById('setReviewsEnabled').checked;
        const subInputs = [
            'setAllowSubmission', 
            'setVerifiedOnly', 
            'setImageReviewsEnabled',
            'setAutoReplyEnabled'
        ];
        
        subInputs.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                const parentCard = el.closest('.settings-panel-card');
                if (parentCard && id !== 'setReviewsEnabled') {
                    parentCard.style.opacity = isReviewsGloballyEnabled ? '1' : '0.85';
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        toggleDependentCards();
    });
</script>
@endsection