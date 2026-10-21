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
    /* Global Strict 5px Element Radius */
    #adminProfileRoot .btn,
    #adminProfileRoot .btn-sm,
    #adminProfileRoot button,
    #adminProfileRoot .form-control,
    #adminProfileRoot .badge,
    #adminProfileRoot .input-group,
    #adminProfileRoot .input-group-text,
    #adminProfileRoot .alert,
    #adminProfileRoot .device-session-row {
        border-radius: 5px !important;
    }

    /* Enterprise Card Surface */
    #adminProfileRoot .profile-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }

    /* Avatar Styling */
    .profile-avatar-large {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #e2e8f0;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    }
    .profile-avatar-fallback {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
        color: #ffffff;
        font-size: 36px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 3px solid #dbeafe;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
    }

    /* Form Controls */
    #adminProfileRoot .form-label {
        font-size: 11.5px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 5px;
    }

    #adminProfileRoot .form-control {
        font-size: 12.5px;
        border: 1px solid #cbd5e1;
        padding: 7px 12px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    #adminProfileRoot .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    #adminProfileRoot .input-group .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
        border-color: #cbd5e1;
    }
    #adminProfileRoot .input-group .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Activity Timeline */
    .activity-timeline-item {
        position: relative;
        padding-left: 26px;
        padding-bottom: 18px;
        border-left: 2px solid #e2e8f0;
    }
    .activity-timeline-item:last-child {
        border-left-color: transparent;
        padding-bottom: 0;
    }
    .activity-timeline-dot {
        position: absolute;
        left: -7px;
        top: 3px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #2563eb;
        border: 2px solid #ffffff;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.25);
    }

    /* Safe Bengali Digits */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Mobile Responsive Optimizations */
    @media (max-width: 767.98px) {
        .profile-page-wrapper {
            padding: 12px 10px !important;
        }
        #adminProfileRoot .profile-card {
            padding: 16px !important;
        }
        .header-back-btn,
        .btn-profile-submit {
            width: 100% !important;
            justify-content: center;
            text-align: center;
            padding: 8px 14px !important;
        }
    }
</style>

<div class="container-fluid profile-page-wrapper px-2 px-md-4 py-3" id="adminProfileRoot">

    <!-- Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center" style="font-size: 18px;">
                <i class="fas fa-id-card-clip text-primary me-2"></i>
                <span>My Profile & Activity</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">Manage your credentials, profile photo, and monitor audit trail activity</small>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm px-3 py-1 fw-semibold d-inline-flex align-items-center shadow-xs header-back-btn">
            <i class="fas fa-arrow-left me-1"></i>
            <span>Dashboard</span>
        </a>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3 small py-2 px-3 border-0 shadow-xs d-flex align-items-center gap-2" role="alert">
            <i class="fas fa-circle-check fs-6 text-success flex-shrink-0 me-1"></i>
            <div class="fw-semibold">{{ session('success') }}</div>
            <button type="button" class="btn-close btn-sm shadow-none ms-auto py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3 small py-2 px-3 border-0 shadow-xs d-flex align-items-center gap-2" role="alert">
            <i class="fas fa-circle-exclamation fs-6 text-danger flex-shrink-0 me-1"></i>
            <div class="fw-semibold">{{ session('error') }}</div>
            <button type="button" class="btn-close btn-sm shadow-none ms-auto py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3">
        <!-- Left Column: User Overview Card -->
        <div class="col-12 col-lg-4">
            <div class="profile-card p-4 text-center mb-3">
                <div class="d-flex justify-content-center mb-3">
                    @if(!empty($userPhoto))
                        <img src="{{ $userPhoto }}" alt="{{ $user->name }}" class="profile-avatar-large" id="avatarPreviewImg"
                             onerror="this.style.display='none'; document.getElementById('avatarFallbackBox').style.display='flex';">
                        <div class="profile-avatar-fallback" id="avatarFallbackBox" style="display: none;">
                            {{ strtoupper(substr($user->name ?? 'A', 0, 1)) }}
                        </div>
                    @else
                        <div class="profile-avatar-fallback" id="avatarFallbackBox">
                            {{ strtoupper(substr($user->name ?? 'A', 0, 1)) }}
                        </div>
                        <img src="" alt="" class="profile-avatar-large d-none" id="avatarPreviewImg">
                    @endif
                </div>

                <h5 class="fw-bold text-dark mb-1" style="font-size: 16px;">{{ $user->name }}</h5>
                <p class="text-muted small mb-3" style="font-size: 12px;">{{ $user->designation ?: 'Authorized Staff' }}</p>

                <div class="d-flex justify-content-center gap-2 flex-wrap mb-3">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 font-monospace small fw-semibold" style="font-size: 10.5px;">
                        {{ ucwords(str_replace('_', ' ', $user->role)) }}
                    </span>
                    <span class="badge {{ $user->is_locked ? 'bg-danger-subtle text-danger border-danger-subtle' : 'bg-success-subtle text-success border-success-subtle' }} border px-3 py-1 small fw-semibold" style="font-size: 10.5px;">
                        {{ $user->is_locked ? 'Locked' : 'Active Account' }}
                    </span>
                </div>

                <div class="text-start border-top pt-3 small">
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted" style="font-size: 12px;">Email</span>
                        <strong class="text-dark font-monospace text-truncate ms-2" style="font-size: 12px; max-width: 190px;">{{ $user->email }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted" style="font-size: 12px;">Phone</span>
                        <strong class="text-dark font-monospace bn-digit" style="font-size: 12px;">{{ $user->phone ?: 'Not Set' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted" style="font-size: 12px;">Branch Hub</span>
                        <strong class="text-primary" style="font-size: 12px;">{{ $branchName }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-2">
                        <span class="text-muted" style="font-size: 12px;">Member Since</span>
                        <span class="text-dark fw-semibold bn-digit" style="font-size: 12px;">{{ date('d M Y', strtotime($user->created_at)) }}</span>
                    </div>
                </div>
            </div>

            <!-- Connected Device Sessions Card -->
            <div class="profile-card p-3 mb-3 mb-lg-0">
                <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center" style="font-size: 13.5px;">
                    <i class="fas fa-tower-broadcast text-success me-2"></i>
                    <span>Connected Devices</span>
                </h6>
                <div class="d-flex flex-column gap-2 small">
                    @forelse($mySessions as $s)
                        <div class="d-flex align-items-center justify-content-between p-2 bg-light border device-session-row">
                            <div class="overflow-hidden pe-2">
                                <strong class="text-dark d-block text-truncate" style="font-size: 12px;">{{ $s->device_type ?: 'Computer' }} ({{ $s->browser ?: 'Browser' }})</strong>
                                <small class="text-muted font-monospace bn-digit" style="font-size: 10.5px;">IP: {{ $s->ip_address }}</small>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fw-semibold flex-shrink-0" style="font-size: 9.5px;">Online</span>
                        </div>
                    @empty
                        <div class="text-muted small py-2 text-center">Current browser session active.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Column: Edit Forms & Activity History -->
        <div class="col-12 col-lg-8">
            <!-- 1. Profile Update Form -->
            <div class="profile-card p-3 p-md-4 mb-3">
                <h6 class="fw-bold text-dark mb-3 border-bottom pb-2 d-flex align-items-center" style="font-size: 14px;">
                    <i class="fas fa-user-pen text-primary me-2"></i>
                    <span>Edit Personal Information & Photo</span>
                </h6>

                <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control fw-semibold @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $user->name) }}" required>
                            @error('name') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control font-monospace @error('email') is-invalid @enderror" 
                                   value="{{ old('email', $user->email) }}" required>
                            @error('email') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control font-monospace bn-digit @error('phone') is-invalid @enderror" 
                                   value="{{ old('phone', $user->phone) }}" required>
                            @error('phone') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Official Designation</label>
                            <input type="text" name="designation" class="form-control" 
                                   value="{{ old('designation', $user->designation) }}" placeholder=" Senior Order Manager, Branch Manager">
                        </div>

                        <!-- Profile Photo Update (File Upload or Direct URL) -->
                        <div class="col-12">
                            <label class="form-label">Profile Photo (File Upload OR Image URL)</label>
                            <div class="row g-2">
                                <div class="col-12 col-md-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light text-muted"><i class="fas fa-upload"></i></span>
                                        <input type="file" name="photo_file" id="photoFileInput" class="form-control" accept="image/*" onchange="previewSelectedPhoto(this)">
                                    </div>
                                    <small class="text-muted d-block mt-1" style="font-size: 11px;">Max 3MB (JPG, PNG, WEBP)</small>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light text-muted"><i class="fas fa-link"></i></span>
                                        <input type="url" name="photo_url" id="photoUrlInput" class="form-control font-monospace" placeholder="https://example.com/avatar.jpg" value="{{ filter_var($user->photo, FILTER_VALIDATE_URL) ? $user->photo : '' }}" oninput="previewUrlPhoto(this.value)">
                                    </div>
                                    <small class="text-muted d-block mt-1" style="font-size: 11px;">Or paste direct image URL</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Bio / Notes</label>
                            <textarea name="bio" rows="2" class="form-control" placeholder="Brief note about your role or duties...">{{ old('bio', $user->bio) }}</textarea>
                        </div>

                        <div class="col-12 text-end pt-2 border-top">
                            <button type="submit" class="btn btn-primary btn-sm px-4 py-2 fw-semibold shadow-xs d-inline-flex align-items-center btn-profile-submit">
                                <i class="fas fa-floppy-disk me-2"></i>
                                <span>Save Profile Details</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- 2. Security & Password Change Card -->
            <div class="profile-card p-3 p-md-4 mb-3" id="security-section">
                <h6 class="fw-bold text-dark mb-3 border-bottom pb-2 d-flex align-items-center" style="font-size: 14px;">
                    <i class="fas fa-shield-halved text-warning me-2"></i>
                    <span>Security & Password Change</span>
                </h6>

                <form action="{{ route('admin.profile.password') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label">Current Password <span class="text-danger">*</span></label>
                            <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                            @error('current_password') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">New Password <span class="text-danger">*</span></label>
                            <input type="password" name="new_password" class="form-control @error('new_password') is-invalid @enderror" placeholder="Min 6 characters" required>
                            @error('new_password') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                            <input type="password" name="new_password_confirmation" class="form-control" required>
                        </div>

                        <div class="col-12 text-end pt-2 border-top">
                            <button type="submit" class="btn btn-dark btn-sm px-4 py-2 fw-semibold shadow-xs d-inline-flex align-items-center btn-profile-submit">
                                <i class="fas fa-key me-2"></i>
                                <span>Update Password</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- 3. Work Activity & Audit Trail Card -->
            <div class="profile-card p-3 p-md-4">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center" style="font-size: 14px;">
                        <i class="fas fa-clock-rotate-left text-info me-2"></i>
                        <span>My Recent Work Activity</span>
                    </h6>
                    <span class="badge bg-light text-muted border px-2 py-1 bn-digit" style="font-size: 11px;">Last 30 Events</span>
                </div>

                <div class="activity-timeline-wrapper">
                    @forelse($myActivities as $act)
                        <div class="activity-timeline-item">
                            <div class="activity-timeline-dot"></div>
                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-start gap-1">
                                <div>
                                    <span class="badge bg-secondary-subtle text-dark border font-monospace text-uppercase px-2 py-1" style="font-size: 9.5px;">
                                        {{ $act->action }}
                                    </span>
                                    <strong class="text-dark small ms-1" style="font-size: 12.5px;">{{ $act->description }}</strong>
                                    @if(!empty($act->record_ref))
                                        <code class="text-primary small font-monospace ms-1 bn-digit">({{ $act->record_ref }})</code>
                                    @endif
                                </div>
                                <small class="text-muted text-nowrap font-monospace bn-digit" style="font-size: 11px;">
                                    {{ date('d M Y, h:i A', strtotime($act->created_at)) }}
                                </small>
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                Module: <span class="fw-semibold text-secondary">{{ ucwords(str_replace('_', ' ', $act->module)) }}</span> 
                                &bull; IP: <span class="font-monospace bn-digit">{{ $act->ip_address }}</span>
                            </small>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">
                            <i class="fas fa-clipboard-list fa-2x mb-2 d-block opacity-25"></i>
                            <span class="fw-semibold">No recent work activities recorded for your account yet.</span>
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