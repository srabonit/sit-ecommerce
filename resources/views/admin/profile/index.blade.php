
@extends('layouts.admin')

@section('title', 'My Profile & Work Activity - ' . config('app.name'))

@php
    $resolvePhoto = function($path) {
        if (empty($path)) return null;
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        $clean = ltrim(str_replace(['storage/', 'public/'], '', $path), '/');
        if (file_exists(public_path($clean))) return asset($clean);
        if (file_exists(public_path('uploads/' . $clean))) return asset('uploads/' . $clean);
        return asset($clean);
    };

    $userPhoto = $resolvePhoto($user->photo ?? null);
@endphp

@section('admin_content')

<style>
/* =========================================================
   PROFILE PAGE — KAIADMIN LITE CLASSIC / WHITE DESIGN
   Scoped CSS: #adminProfileRoot
========================================================= */

#adminProfileRoot {
    --profile-primary: var(--bs-primary, #1572e8);
    --profile-secondary: #686c71;
    --profile-success: #31ce36;
    --profile-danger: #f25961;
    --profile-warning: #ffad46;
    --profile-info: #48abf7;

    --profile-bg: #f8f9fa;
    --profile-card: #ffffff;
    --profile-heading: #1a2035;
    --profile-text: #495057;
    --profile-muted: #8d9498;
    --profile-border: #ebedf2;

    --profile-radius: 5px;
    --profile-card-radius: 8px;
    --profile-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);

    color: var(--profile-text);
    font-size: 13px;
    min-width: 0;
    width: 100%;
}

#adminProfileRoot *,
#adminProfileRoot *::before,
#adminProfileRoot *::after {
    box-sizing: border-box;
}

#adminProfileRoot .profile-page-wrapper {
    min-width: 0;
}

/* =========================================================
   PAGE HEADER
========================================================= */

#adminProfileRoot .profile-page-header {
    margin-bottom: 20px;
}

#adminProfileRoot .profile-page-title {
    color: var(--profile-heading);
    font-size: clamp(17px, 1.5vw, 21px);
    font-weight: 700;
    line-height: 1.35;
    letter-spacing: -0.35px;
}

#adminProfileRoot .profile-page-subtitle {
    color: var(--profile-muted);
    font-size: 12px;
    line-height: 1.6;
    margin-top: 4px;
}

#adminProfileRoot .header-back-btn {
    min-height: 34px;
    white-space: nowrap;
}

/* =========================================================
   CARD DESIGN
========================================================= */

#adminProfileRoot .profile-card {
    background: var(--profile-card);
    border: 1px solid var(--profile-border);
    border-radius: var(--profile-card-radius) !important;
    box-shadow: var(--profile-shadow);
    min-width: 0;
    transition: box-shadow 0.2s ease, border-color 0.2s ease;
}

#adminProfileRoot .profile-card:hover {
    box-shadow: 0 3px 12px rgba(0, 0, 0, 0.045);
}

#adminProfileRoot .profile-section-title {
    color: var(--profile-heading);
    font-size: 14px;
    font-weight: 700;
    line-height: 1.5;
    margin-bottom: 15px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--profile-border);
}

#adminProfileRoot .profile-section-title i {
    font-size: 15px;
    width: 19px;
    text-align: center;
    flex-shrink: 0;
}

/* =========================================================
   PROFILE AVATAR
========================================================= */

#adminProfileRoot .profile-avatar-large,
#adminProfileRoot .profile-avatar-fallback {
    width: 104px;
    height: 104px;
    flex-shrink: 0;
    border-radius: 50%;
}

#adminProfileRoot .profile-avatar-large {
    object-fit: cover;
    background: #f1f3f5;
    border: 4px solid #ffffff;
    outline: 1px solid var(--profile-border);
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
}

#adminProfileRoot .profile-avatar-fallback {
    background: var(--profile-primary);
    color: #ffffff;
    font-size: 36px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 4px solid #ffffff;
    outline: 1px solid var(--profile-border);
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
}

#adminProfileRoot .profile-user-name {
    color: var(--profile-heading);
    font-size: 17px;
    font-weight: 700;
    line-height: 1.4;
    overflow-wrap: anywhere;
}

#adminProfileRoot .profile-user-designation {
    color: var(--profile-muted);
    font-size: 12px;
    margin-bottom: 13px;
}

/* =========================================================
   BADGES
========================================================= */

#adminProfileRoot .badge {
    border-radius: var(--profile-radius) !important;
    font-weight: 600;
    letter-spacing: 0.1px;
    line-height: 1.4;
    white-space: normal;
    overflow-wrap: anywhere;
}

#adminProfileRoot .profile-card .badge {
    font-size: 10.5px;
}

#adminProfileRoot .profile-status-badges {
    gap: 7px;
}

/* =========================================================
   PROFILE INFORMATION
========================================================= */

#adminProfileRoot .profile-info-list {
    border-top: 1px solid var(--profile-border);
}

#adminProfileRoot .profile-info-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    padding: 11px 0;
    border-bottom: 1px solid var(--profile-border);
    min-width: 0;
}

#adminProfileRoot .profile-info-row:last-child {
    border-bottom: 0;
    padding-bottom: 2px;
}

#adminProfileRoot .profile-info-label {
    color: var(--profile-muted);
    font-size: 12px;
    font-weight: 500;
    flex-shrink: 0;
}

#adminProfileRoot .profile-info-value {
    color: var(--profile-heading);
    font-size: 12px;
    font-weight: 600;
    text-align: right;
    overflow-wrap: anywhere;
    min-width: 0;
}

#adminProfileRoot .profile-info-value.text-primary {
    color: var(--profile-primary) !important;
}

/* =========================================================
   CONNECTED DEVICES
========================================================= */

#adminProfileRoot .device-session-row {
    background: #ffffff !important;
    border: 1px solid var(--profile-border) !important;
    border-radius: var(--profile-radius) !important;
    padding: 11px 12px !important;
    gap: 10px;
    transition: background 0.2s ease, border-color 0.2s ease;
}

#adminProfileRoot .device-session-row:hover {
    background: #f8f9fa !important;
    border-color: #dce1e8 !important;
}

#adminProfileRoot .device-session-row strong {
    color: var(--profile-heading);
    font-size: 12px;
    line-height: 1.5;
}

#adminProfileRoot .device-session-row small {
    font-size: 10.5px;
}

/* =========================================================
   FORM LABELS
========================================================= */

#adminProfileRoot .form-label {
    display: inline-block;
    color: #495057;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 7px;
    line-height: 1.5;
}

#adminProfileRoot .form-label .text-danger {
    font-weight: 700;
}

/* =========================================================
   FORM CONTROLS
========================================================= */

#adminProfileRoot .form-control,
#adminProfileRoot .form-select {
    width: 100%;
    min-width: 0;
    min-height: 38px;
    padding: 8px 12px;
    border: 1px solid #e0e3e8;
    border-radius: var(--profile-radius) !important;
    background-color: #ffffff;
    color: var(--profile-heading);
    font-family: inherit;
    font-size: 13px;
    font-weight: 400;
    line-height: 1.5;
    box-shadow: none;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

#adminProfileRoot .form-control::placeholder {
    color: #adb5bd;
    opacity: 1;
    font-size: 12px;
}

#adminProfileRoot .form-control:focus,
#adminProfileRoot .form-select:focus {
    background-color: #ffffff;
    border-color: var(--profile-primary);
    box-shadow: 0 0 0 3px rgba(21, 114, 232, 0.10);
    outline: 0;
}

#adminProfileRoot textarea.form-control {
    min-height: 76px;
    resize: vertical;
}

#adminProfileRoot input[type="file"].form-control {
    padding: 5px 8px;
    min-height: 38px;
    font-size: 11.5px;
}

#adminProfileRoot input[type="file"]::file-selector-button {
    padding: 5px 10px;
    margin: -1px 10px -1px -4px;
    border: 0;
    border-radius: 4px;
    background: #f1f3f5;
    color: #495057;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
}

#adminProfileRoot .form-control.is-invalid {
    border-color: var(--profile-danger);
    box-shadow: none;
}

#adminProfileRoot .invalid-feedback {
    font-size: 11px;
    margin-top: 5px;
}

/* =========================================================
   INPUT GROUP
========================================================= */

#adminProfileRoot .input-group {
    min-width: 0;
    flex-wrap: nowrap;
}

#adminProfileRoot .input-group .input-group-text {
    min-width: 39px;
    justify-content: center;
    padding: 7px 10px;
    color: var(--profile-muted);
    background: #f8f9fa !important;
    border: 1px solid #e0e3e8;
    border-radius: var(--profile-radius) 0 0 var(--profile-radius) !important;
    font-size: 12px;
}

#adminProfileRoot .input-group .form-control {
    min-width: 0;
    border-radius: 0 var(--profile-radius) var(--profile-radius) 0 !important;
}

/* =========================================================
   BUTTONS
========================================================= */

#adminProfileRoot .btn {
    min-height: 34px;
    border-radius: var(--profile-radius) !important;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.4;
    transition: background-color 0.2s ease, border-color 0.2s ease,
                box-shadow 0.2s ease, transform 0.2s ease;
}

#adminProfileRoot .btn-sm {
    min-height: 32px;
    padding: 6px 12px;
}

#adminProfileRoot .btn-profile-submit {
    min-height: 38px;
    padding: 8px 18px !important;
    font-size: 12px;
}

#adminProfileRoot .btn-primary {
    background-color: var(--profile-primary);
    border-color: var(--profile-primary);
}

#adminProfileRoot .btn-primary:hover {
    filter: brightness(0.94);
}

#adminProfileRoot .btn-dark {
    background-color: #1a2035;
    border-color: #1a2035;
}

#adminProfileRoot .btn-dark:hover {
    background-color: #30394e;
    border-color: #30394e;
}

#adminProfileRoot .btn:focus-visible {
    outline: 2px solid var(--profile-primary);
    outline-offset: 2px;
}

/* =========================================================
   ALERTS
========================================================= */

#adminProfileRoot .alert {
    border-radius: var(--profile-radius) !important;
    font-size: 12px;
    line-height: 1.6;
}

#adminProfileRoot .alert .btn-close {
    padding: 10px;
    font-size: 10px;
}

/* =========================================================
   ACTIVITY TIMELINE
========================================================= */

#adminProfileRoot .activity-timeline-wrapper {
    padding: 3px 2px 0 3px;
}

#adminProfileRoot .activity-timeline-item {
    position: relative;
    padding-left: 25px;
    padding-bottom: 21px;
    border-left: 2px solid #e9edf2;
    min-width: 0;
}

#adminProfileRoot .activity-timeline-item:last-child {
    border-left-color: transparent;
    padding-bottom: 0;
}

#adminProfileRoot .activity-timeline-dot {
    position: absolute;
    left: -7px;
    top: 3px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: var(--profile-primary);
    border: 2px solid #ffffff;
    box-shadow: 0 0 0 2px rgba(21, 114, 232, 0.17);
}

#adminProfileRoot .activity-timeline-item strong {
    color: var(--profile-heading);
    font-weight: 600;
    line-height: 1.6;
    overflow-wrap: anywhere;
}

#adminProfileRoot .activity-timeline-item > div {
    min-width: 0;
}

#adminProfileRoot .activity-timeline-item code {
    font-size: 11px;
    overflow-wrap: anywhere;
}

#adminProfileRoot .activity-timeline-item small {
    line-height: 1.7;
    overflow-wrap: anywhere;
}

#adminProfileRoot .activity-timeline-item .text-nowrap {
    white-space: nowrap;
}

/* =========================================================
   EMPTY STATE
========================================================= */

#adminProfileRoot .activity-timeline-wrapper .text-center {
    color: var(--profile-muted);
    padding: 28px 12px !important;
    line-height: 1.7;
}

#adminProfileRoot .activity-timeline-wrapper .fa-clipboard-list {
    color: #adb5bd;
}

/* =========================================================
   TYPOGRAPHY & MONOSPACE
========================================================= */

#adminProfileRoot .text-dark {
    color: var(--profile-heading) !important;
}

#adminProfileRoot .text-muted {
    color: var(--profile-muted) !important;
}

#adminProfileRoot .font-monospace,
#adminProfileRoot .bn-digit {
    font-weight: 600;
    letter-spacing: 0.05px;
}

#adminProfileRoot .text-truncate {
    min-width: 0;
}

#adminProfileRoot .border-top,
#adminProfileRoot .border-bottom {
    border-color: var(--profile-border) !important;
}

/* =========================================================
   FOCUS & ACCESSIBILITY
========================================================= */

#adminProfileRoot a,
#adminProfileRoot button,
#adminProfileRoot input,
#adminProfileRoot textarea {
    -webkit-tap-highlight-color: transparent;
}

#adminProfileRoot button:focus-visible,
#adminProfileRoot a:focus-visible {
    outline: 2px solid var(--profile-primary);
    outline-offset: 2px;
}

/* =========================================================
   TABLET RESPONSIVE
========================================================= */

@media (max-width: 991.98px) {

    #adminProfileRoot .profile-page-wrapper {
        padding-left: 12px !important;
        padding-right: 12px !important;
    }

    #adminProfileRoot .profile-card {
        height: auto;
    }

    #adminProfileRoot .profile-avatar-large,
    #adminProfileRoot .profile-avatar-fallback {
        width: 92px;
        height: 92px;
    }

}

/* =========================================================
   MOBILE RESPONSIVE
========================================================= */

@media (max-width: 767.98px) {

    #adminProfileRoot .profile-page-wrapper {
        padding: 12px 10px !important;
    }

    #adminProfileRoot .profile-page-header {
        margin-bottom: 16px;
    }

    #adminProfileRoot .profile-page-title {
        font-size: 17px;
    }

    #adminProfileRoot .profile-page-subtitle {
        font-size: 11.5px;
    }

    #adminProfileRoot .header-back-btn {
        width: 100%;
        justify-content: center;
        min-height: 38px;
    }

    #adminProfileRoot .profile-card {
        padding: 15px !important;
        margin-bottom: 12px !important;
    }

    #adminProfileRoot .profile-avatar-large,
    #adminProfileRoot .profile-avatar-fallback {
        width: 88px;
        height: 88px;
    }

    #adminProfileRoot .profile-user-name {
        font-size: 16px;
    }

    #adminProfileRoot .profile-info-row {
        gap: 8px;
    }

    #adminProfileRoot .profile-info-label,
    #adminProfileRoot .profile-info-value {
        font-size: 11.5px;
    }

    #adminProfileRoot .profile-section-title {
        font-size: 13px;
        margin-bottom: 13px;
    }

    #adminProfileRoot .form-label {
        font-size: 11.5px;
    }

    #adminProfileRoot .form-control,
    #adminProfileRoot .form-select {
        min-height: 40px;
        font-size: 13px;
    }

    #adminProfileRoot textarea.form-control {
        min-height: 80px;
    }

    #adminProfileRoot .btn-profile-submit {
        width: 100%;
        min-height: 40px;
        justify-content: center;
        text-align: center;
    }

    #adminProfileRoot .activity-timeline-item {
        padding-left: 21px;
        padding-bottom: 19px;
    }

    #adminProfileRoot .activity-timeline-item .text-nowrap {
        white-space: normal !important;
    }

}

/* =========================================================
   SMALL MOBILE
========================================================= */

@media (max-width: 374.98px) {

    #adminProfileRoot .profile-page-wrapper {
        padding-left: 7px !important;
        padding-right: 7px !important;
    }

    #adminProfileRoot .profile-card {
        padding: 12px !important;
    }

    #adminProfileRoot .profile-info-row {
        flex-wrap: wrap;
    }

    #adminProfileRoot .profile-info-value {
        max-width: 100%;
        text-align: left;
    }

    #adminProfileRoot .profile-status-badges .badge {
        padding-left: 8px !important;
        padding-right: 8px !important;
    }

    #adminProfileRoot .activity-timeline-item {
        padding-left: 19px;
    }

}

/* =========================================================
   REDUCED MOTION
========================================================= */

@media (prefers-reduced-motion: reduce) {

    #adminProfileRoot *,
    #adminProfileRoot *::before,
    #adminProfileRoot *::after {
        transition-duration: 0.01ms !important;
        animation-duration: 0.01ms !important;
    }

}
</style>

<div class="container-fluid profile-page-wrapper px-2 px-md-4 py-3" id="adminProfileRoot">

    <!-- Page Header -->
    <div class="profile-page-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">

        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center profile-page-title">
                <i class="fas fa-id-card-clip text-primary me-2"></i>
                <span>My Profile & Activity</span>
            </h5>

            <small class="text-muted d-block profile-page-subtitle">
                Manage your profile, security and work activity.
            </small>
        </div>

        <a href="{{ route('admin.dashboard') }}"
           class="btn btn-outline-secondary btn-sm px-3 py-1 fw-semibold d-inline-flex align-items-center shadow-xs header-back-btn">

            <i class="fas fa-arrow-left me-1"></i>
            <span>Dashboard</span>

        </a>

    </div>

    <!-- Success Notification -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3 small py-2 px-3 border-0 shadow-xs d-flex align-items-center gap-2" role="alert">

            <i class="fas fa-circle-check fs-6 text-success flex-shrink-0 me-1"></i>

            <div class="fw-semibold">{{ session('success') }}</div>

            <button type="button" class="btn-close btn-sm shadow-none ms-auto py-2" data-bs-dismiss="alert"></button>

        </div>
    @endif

    <!-- Error Notification -->
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3 small py-2 px-3 border-0 shadow-xs d-flex align-items-center gap-2" role="alert">

            <i class="fas fa-circle-exclamation fs-6 text-danger flex-shrink-0 me-1"></i>

            <div class="fw-semibold">{{ session('error') }}</div>

            <button type="button" class="btn-close btn-sm shadow-none ms-auto py-2" data-bs-dismiss="alert"></button>

        </div>
    @endif

    <div class="row g-3">

        <!-- =================================================
             LEFT COLUMN — PROFILE OVERVIEW
        ================================================== -->

        <div class="col-12 col-lg-4">

            <!-- Profile Overview Card -->
            <div class="profile-card p-4 text-center mb-3">

                <div class="d-flex justify-content-center mb-3">

                    @if(!empty($userPhoto))

                        <img src="{{ $userPhoto }}"
                             alt="{{ $user->name }}"
                             class="profile-avatar-large"
                             id="avatarPreviewImg"
                             onerror="this.style.display='none'; document.getElementById('avatarFallbackBox').style.display='flex';">

                        <div class="profile-avatar-fallback"
                             id="avatarFallbackBox"
                             style="display: none;">

                            {{ strtoupper(substr($user->name ?? 'A', 0, 1)) }}

                        </div>

                    @else

                        <div class="profile-avatar-fallback" id="avatarFallbackBox">

                            {{ strtoupper(substr($user->name ?? 'A', 0, 1)) }}

                        </div>

                        <img src=""
                             alt=""
                             class="profile-avatar-large d-none"
                             id="avatarPreviewImg">

                    @endif

                </div>

                <h5 class="profile-user-name mb-1">
                    {{ $user->name }}
                </h5>

                <p class="profile-user-designation">
                    {{ $user->designation ?: 'Authorized Staff' }}
                </p>

                <!-- Account Badges -->
                <div class="d-flex justify-content-center profile-status-badges flex-wrap mb-3">

                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 font-monospace small fw-semibold">

                        {{ ucwords(str_replace('_', ' ', $user->role)) }}

                    </span>

                    <span class="badge {{ $user->is_locked ? 'bg-danger-subtle text-danger border-danger-subtle' : 'bg-success-subtle text-success border-success-subtle' }} border px-3 py-1 small fw-semibold">

                        {{ $user->is_locked ? 'Locked' : 'Active Account' }}

                    </span>

                </div>

                <!-- Personal Information -->
                <div class="profile-info-list text-start small">

                    <div class="profile-info-row">

                        <span class="profile-info-label">Email</span>

                        <strong class="profile-info-value font-monospace text-truncate ms-2">
                            {{ $user->email }}
                        </strong>

                    </div>

                    <div class="profile-info-row">

                        <span class="profile-info-label">Phone</span>

                        <strong class="profile-info-value font-monospace bn-digit">
                            {{ $user->phone ?: 'Not Set' }}
                        </strong>

                    </div>

                    <div class="profile-info-row">

                        <span class="profile-info-label">Branch Hub</span>

                        <strong class="profile-info-value text-primary">
                            {{ $branchName }}
                        </strong>

                    </div>

                    <div class="profile-info-row">

                        <span class="profile-info-label">Member Since</span>

                        <span class="profile-info-value bn-digit">
                            {{ date('d M Y', strtotime($user->created_at)) }}
                        </span>

                    </div>

                </div>

            </div>

            <!-- Connected Devices -->
            <div class="profile-card p-3 mb-3 mb-lg-0">

                <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center profile-section-title">

                    <i class="fas fa-tower-broadcast text-success me-2"></i>

                    <span>Connected Devices</span>

                </h6>

                <div class="d-flex flex-column gap-2 small">

                    @forelse($mySessions as $s)

                        <div class="d-flex align-items-center justify-content-between p-2 bg-light border device-session-row">

                            <div class="overflow-hidden pe-2">

                                <strong class="text-dark d-block text-truncate">

                                    {{ $s->device_type ?: 'Computer' }} ({{ $s->browser ?: 'Browser' }})

                                </strong>

                                <small class="text-muted font-monospace bn-digit">
                                    IP: {{ $s->ip_address }}
                                </small>

                            </div>

                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fw-semibold flex-shrink-0">
                                Online
                            </span>

                        </div>

                    @empty

                        <div class="text-muted small py-2 text-center">
                            Current browser session active.
                        </div>

                    @endforelse

                </div>

            </div>

        </div>

        <!-- =================================================
             RIGHT COLUMN — FORMS & ACTIVITY
        ================================================== -->

        <div class="col-12 col-lg-8">

            <!-- =================================================
                 1. PROFILE UPDATE FORM
            ================================================== -->

            <div class="profile-card p-3 p-md-4 mb-3">

                <h6 class="fw-bold text-dark mb-3 border-bottom pb-2 d-flex align-items-center profile-section-title">

                    <i class="fas fa-user-pen text-primary me-2"></i>

                    <span>Personal Information</span>

                </h6>

                <form action="{{ route('admin.profile.update') }}"
                      method="POST"
                      enctype="multipart/form-data">

                    @csrf

                    <div class="row g-3">

                        <!-- Full Name -->
                        <div class="col-12 col-md-6">

                            <label class="form-label">
                                Full Name <span class="text-danger">*</span>
                            </label>

                            <input type="text"
                                   name="name"
                                   class="form-control fw-semibold @error('name') is-invalid @enderror"
                                   value="{{ old('name', $user->name) }}"
                                   required>

                            @error('name')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror

                        </div>

                        <!-- Email -->
                        <div class="col-12 col-md-6">

                            <label class="form-label">
                                Email Address <span class="text-danger">*</span>
                            </label>

                            <input type="email"
                                   name="email"
                                   class="form-control font-monospace @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email) }}"
                                   required>

                            @error('email')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror

                        </div>

                        <!-- Phone -->
                        <div class="col-12 col-md-6">

                            <label class="form-label">
                                Phone Number <span class="text-danger">*</span>
                            </label>

                            <input type="text"
                                   name="phone"
                                   class="form-control font-monospace bn-digit @error('phone') is-invalid @enderror"
                                   value="{{ old('phone', $user->phone) }}"
                                   required>

                            @error('phone')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror

                        </div>

                        <!-- Designation -->
                        <div class="col-12 col-md-6">

                            <label class="form-label">
                                Official Designation
                            </label>

                            <input type="text"
                                   name="designation"
                                   class="form-control"
                                   value="{{ old('designation', $user->designation) }}"
                                   placeholder="Senior Order Manager, Branch Manager">

                        </div>

                        <!-- Profile Photo -->
                        <div class="col-12">

                            <label class="form-label">
                                Profile Photo
                            </label>

                            <div class="row g-2">

                                <!-- File Upload -->
                                <div class="col-12 col-md-6">

                                    <div class="input-group input-group-sm">

                                        <span class="input-group-text bg-light text-muted">
                                            <i class="fas fa-upload"></i>
                                        </span>

                                        <input type="file"
                                               name="photo_file"
                                               id="photoFileInput"
                                               class="form-control"
                                               accept="image/*"
                                               onchange="previewSelectedPhoto(this)">

                                    </div>

                                    <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                        Max 3MB (JPG, PNG, WEBP)
                                    </small>

                                </div>

                                <!-- Image URL -->
                                <div class="col-12 col-md-6">

                                    <div class="input-group input-group-sm">

                                        <span class="input-group-text bg-light text-muted">
                                            <i class="fas fa-link"></i>
                                        </span>

                                        <input type="url"
                                               name="photo_url"
                                               id="photoUrlInput"
                                               class="form-control font-monospace"
                                               placeholder="https://example.com/avatar.jpg"
                                               value="{{ filter_var($user->photo, FILTER_VALIDATE_URL) ? $user->photo : '' }}"
                                               oninput="previewUrlPhoto(this.value)">

                                    </div>

                                    <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                        Or paste a direct image URL.
                                    </small>

                                </div>

                            </div>

                        </div>

                        <!-- Bio -->
                        <div class="col-12">

                            <label class="form-label">
                                Bio / Notes
                            </label>

                            <textarea name="bio"
                                      rows="2"
                                      class="form-control"
                                      placeholder="Brief note about your role or duties...">{{ old('bio', $user->bio) }}</textarea>

                        </div>

                        <!-- Submit -->
                        <div class="col-12 text-end pt-3 border-top">

                            <button type="submit"
                                    class="btn btn-primary btn-sm px-4 py-2 fw-semibold shadow-xs d-inline-flex align-items-center btn-profile-submit">

                                <i class="fas fa-floppy-disk me-2"></i>

                                <span>Save Profile Details</span>

                            </button>

                        </div>

                    </div>

                </form>

            </div>

            <!-- =================================================
                 2. SECURITY & PASSWORD
            ================================================== -->

            <div class="profile-card p-3 p-md-4 mb-3" id="security-section">

                <h6 class="fw-bold text-dark mb-3 border-bottom pb-2 d-flex align-items-center profile-section-title">

                    <i class="fas fa-shield-halved text-warning me-2"></i>

                    <span>Security & Password</span>

                </h6>

                <form action="{{ route('admin.profile.password') }}"
                      method="POST">

                    @csrf

                    <div class="row g-3">

                        <!-- Current Password -->
                        <div class="col-12 col-md-4">

                            <label class="form-label">
                                Current Password <span class="text-danger">*</span>
                            </label>

                            <input type="password"
                                   name="current_password"
                                   class="form-control @error('current_password') is-invalid @enderror"
                                   required>

                            @error('current_password')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror

                        </div>

                        <!-- New Password -->
                        <div class="col-12 col-md-4">

                            <label class="form-label">
                                New Password <span class="text-danger">*</span>
                            </label>

                            <input type="password"
                                   name="new_password"
                                   class="form-control @error('new_password') is-invalid @enderror"
                                   placeholder="Min 6 characters"
                                   required>

                            @error('new_password')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror

                        </div>

                        <!-- Confirm Password -->
                        <div class="col-12 col-md-4">

                            <label class="form-label">
                                Confirm New Password <span class="text-danger">*</span>
                            </label>

                            <input type="password"
                                   name="new_password_confirmation"
                                   class="form-control"
                                   required>

                        </div>

                        <!-- Update Password -->
                        <div class="col-12 text-end pt-3 border-top">

                            <button type="submit"
                                    class="btn btn-dark btn-sm px-4 py-2 fw-semibold shadow-xs d-inline-flex align-items-center btn-profile-submit">

                                <i class="fas fa-key me-2"></i>

                                <span>Update Password</span>

                            </button>

                        </div>

                    </div>

                </form>

            </div>

            <!-- =================================================
                 3. WORK ACTIVITY & AUDIT TRAIL
            ================================================== -->

            <div class="profile-card p-3 p-md-4">

                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3 flex-wrap gap-2">

                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center profile-section-title border-0 pb-0">

                        <i class="fas fa-clock-rotate-left text-info me-2"></i>

                        <span>Recent Work Activity</span>

                    </h6>

                    <span class="badge bg-light text-muted border px-2 py-1 bn-digit">
                        Last 30 Events
                    </span>

                </div>

                <div class="activity-timeline-wrapper">

                    @forelse($myActivities as $act)

                        <div class="activity-timeline-item">

                            <div class="activity-timeline-dot"></div>

                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-start gap-1">

                                <div>

                                    <span class="badge bg-secondary-subtle text-dark border font-monospace text-uppercase px-2 py-1">

                                        {{ $act->action }}

                                    </span>

                                    <strong class="text-dark small ms-1">
                                        {{ $act->description }}
                                    </strong>

                                    @if(!empty($act->record_ref))

                                        <code class="text-primary small font-monospace ms-1 bn-digit">
                                            ({{ $act->record_ref }})
                                        </code>

                                    @endif

                                </div>

                                <small class="text-muted text-nowrap font-monospace bn-digit">

                                    {{ date('d M Y, h:i A', strtotime($act->created_at)) }}

                                </small>

                            </div>

                            <small class="text-muted d-block mt-1">

                                Module:

                                <span class="fw-semibold text-secondary">
                                    {{ ucwords(str_replace('_', ' ', $act->module)) }}
                                </span>

                                &bull;

                                IP:

                                <span class="font-monospace bn-digit">
                                    {{ $act->ip_address }}
                                </span>

                            </small>

                        </div>

                    @empty

                        <div class="text-center py-4 text-muted small">

                            <i class="fas fa-clipboard-list fa-2x mb-2 d-block opacity-25"></i>

                            <span class="fw-semibold">
                                No recent work activities recorded for your account yet.
                            </span>

                        </div>

                    @endforelse

                </div>

            </div>

        </div>

    </div>

</div>

<script>
    // Live preview when selecting local photo file
    function previewSelectedPhoto(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('avatarPreviewImg');
                const fallback = document.getElementById('avatarFallbackBox');
                if (img) {
                    img.src = e.target.result;
                    img.classList.remove('d-none');
                    img.style.display = 'block';
                }
                if (fallback) fallback.style.display = 'none';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Live preview when pasting direct image URL
    function previewUrlPhoto(url) {
        url = url.trim();
        if (url.startsWith('http://') || url.startsWith('https://')) {
            const img = document.getElementById('avatarPreviewImg');
            const fallback = document.getElementById('avatarFallbackBox');
            if (img) {
                img.src = url;
                img.classList.remove('d-none');
                img.style.display = 'block';
                img.onerror = function() {
                    img.style.display = 'none';
                    if (fallback) fallback.style.display = 'flex';
                };
            }
            if (fallback) fallback.style.display = 'none';
        }
    }
</script>

@endsection
