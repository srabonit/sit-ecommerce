@extends('layouts.admin')

@section('title', 'Edit Staff: ' . $user->name . ' - ' . config('app.name'))

@section('admin_content')
<style>
    /* Global Strict 5px Radius for Buttons, Inputs & Controls */
    #editStaffRoot .btn,
    #editStaffRoot .btn-sm,
    #editStaffRoot button,
    #editStaffRoot .form-control,
    #editStaffRoot .form-select,
    #editStaffRoot .doc-preview-pill,
    #editStaffRoot .doc-input-row {
        border-radius: 5px !important;
    }

    /* Enterprise Card Surface */
    #editStaffRoot .staff-card {
        background: #ffffff;
        border-radius: 8px !important;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }

    .staff-section-title {
        font-size: 12.5px;
        font-weight: 700;
        color: #1e293b;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding-bottom: 10px;
        margin-bottom: 16px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Form Labels & Inputs */
    #editStaffRoot .form-label {
        font-size: 11.5px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 4px;
    }

    #editStaffRoot .form-control,
    #editStaffRoot .form-select {
        font-size: 12.5px;
        border-color: #cbd5e1;
        padding: 6px 10px;
        color: #0f172a;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    #editStaffRoot .form-control:focus,
    #editStaffRoot .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .doc-preview-pill {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 8px 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        transition: background-color 0.15s ease;
    }
    .doc-preview-pill:hover {
        background: #f1f5f9;
    }

    /* Safe Bengali Digits */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Mobile Responsive Optimizations */
    @media (max-width: 767.98px) {
        .edit-staff-header-actions {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .edit-staff-header-actions .btn {
            flex: 1 1 auto;
            justify-content: center;
            text-align: center;
            font-size: 12px;
            padding: 7px 10px;
        }
        .edit-staff-header-actions .btn-save-staff {
            flex: 1 1 100%;
        }
        .header-subtitle-text {
            margin-left: 0 !important;
            padding-left: 0 !important;
            margin-top: 4px;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="editStaffRoot">
    <!-- Header & Action Bar -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2.5 mb-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.roles.users') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center justify-content-center p-0 shadow-xs flex-shrink-0" style="width: 30px; height: 30px;" title="Back to Directory">
                    <i class="fas fa-arrow-left" style="font-size: 11px;"></i>
                </a>
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center flex-wrap" style="font-size: 17px;">
                    <i class="fas fa-user-pen text-primary me-2"></i>
                    <span>Edit Staff Profile:</span>
                    <span class="text-primary ms-1.5">{{ $user->name }}</span>
                </h5>
            </div>
            <small class="text-muted d-block ms-4 ps-2 header-subtitle-text" style="font-size: 11.5px;">Update employment details, branch assignment, credentials & attached files</small>
        </div>
        <div class="edit-staff-header-actions d-flex gap-2">
            <a href="{{ route('admin.roles.users.show', $user->id) }}" class="btn btn-outline-info btn-sm px-3 py-1.5 fw-semibold d-inline-flex align-items-center shadow-xs">
                <i class="fas fa-eye me-1.5"></i> <span>View Profile</span>
            </a>
            <a href="{{ route('admin.roles.users') }}" class="btn btn-light btn-sm px-3 py-1.5 border text-muted fw-semibold d-inline-flex align-items-center shadow-xs">
                <span>Cancel</span>
            </a>
            <button type="button" class="btn btn-primary btn-sm px-3.5 py-1.5 fw-semibold shadow-xs d-inline-flex align-items-center btn-save-staff" onclick="document.getElementById('editStaffForm').submit();">
                <i class="fas fa-floppy-disk me-1.5"></i> <span>Save Changes</span>
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3 small py-2 px-3 border-0 shadow-xs d-flex align-items-center" role="alert" style="border-radius: 6px;">
            <i class="fas fa-circle-exclamation me-2"></i>
            <span class="fw-semibold">{{ session('error') }}</span>
            <button type="button" class="btn-close btn-sm shadow-none py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3 small py-2.5 px-3 border-0 shadow-xs" role="alert" style="border-radius: 6px;">
            <strong class="d-block mb-1"><i class="fas fa-triangle-exclamation me-1.5"></i> Please fix the following errors:</strong>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.roles.users.update_staff', $user->id) }}" method="POST" enctype="multipart/form-data" id="editStaffForm">
        @csrf

        <div class="row g-3">
            <!-- Left Column: Core Profile & Employment -->
            <div class="col-12 col-lg-8">
                <!-- 1. Basic Information Card -->
                <div class="staff-card p-3 p-md-4 mb-3">
                    <div class="staff-section-title">
                        <i class="fas fa-id-card text-primary"></i>
                        <span>1. Core Profile & Role Assignment</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-sm fw-semibold" value="{{ old('name', $user->name) }}" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Official Designation</label>
                            <input type="text" name="designation" class="form-control form-control-sm" value="{{ old('designation', $user->designation) }}" placeholder=" Senior Order Manager, Cashier">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Assigned Role (Access Level) <span class="text-danger">*</span></label>
                            <select name="role" class="form-select form-select-sm fw-semibold" required>
                                @foreach($staffRoles as $r)
                                    <option value="{{ $r }}" {{ old('role', $user->role) ===$r ? 'selected' : '' }}>
                                        {{ ucwords(str_replace('_', ' ', $r)) }} {{$r === 'super_admin' ? '👑 (Root System Owner)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-1" style="font-size: 11px;">Permissions will adjust according to this role's RBAC matrix.</small>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Branch / Hub Assignment</label>
                            <select name="branch_id" class="form-select form-select-sm">
                                <option value="">-- Head Office / Central Global Hub --</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" {{ old('branch_id', $user->branch_id) ==$b->id ? 'selected' : '' }}>
                                        {{ $b->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-1" style="font-size: 11px;">Branch managers will be scoped to their assigned hub.</small>
                        </div>
                    </div>
                </div>

                <!-- 2. Contact & Identity Information -->
                <div class="staff-card p-3 p-md-4 mb-3">
                    <div class="staff-section-title">
                        <i class="fas fa-phone-volume text-success"></i>
                        <span>2. Contact & Identity Verification</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Primary Email (Login Username) <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control form-control-sm font-monospace" value="{{ old('email', $user->email) }}" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Secondary / Alternate Email</label>
                            <input type="email" name="email_alt" class="form-control form-control-sm font-monospace" value="{{ old('email_alt', $user->email_alt) }}" placeholder="Optional recovery email">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Primary Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control form-control-sm font-monospace fw-semibold bn-digit" value="{{ old('phone', $user->phone) }}" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Alternate / Emergency Phone</label>
                            <input type="text" name="phone_alt" class="form-control form-control-sm font-monospace bn-digit" value="{{ old('phone_alt', $user->phone_alt) }}">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">National ID (NID) / Passport No</label>
                            <input type="text" name="nid_number" class="form-control form-control-sm font-monospace bn-digit" value="{{ old('nid_number', $user->nid_number) }}" placeholder=" 1990123456789">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="dob" class="form-control form-control-sm bn-digit" value="{{ old('dob', $user->dob) }}">
                        </div>
                    </div>
                </div>

                <!-- 3. Employment & Payroll Configuration -->
                <div class="staff-card p-3 p-md-4 mb-3">
                    <div class="staff-section-title">
                        <i class="fas fa-hand-holding-dollar text-warning"></i>
                        <span>3. Employment Terms & Payroll</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Joining Date</label>
                            <input type="date" name="joining_date" class="form-control form-control-sm bn-digit" value="{{ old('joining_date', $user->joining_date) }}">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Resignation Date (If inactive)</label>
                            <input type="date" name="resignation_date" class="form-control form-control-sm bn-digit" value="{{ old('resignation_date', $user->resignation_date) }}">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Monthly Basic Salary (৳)</label>
                            <input type="number" step="0.01" min="0" name="monthly_salary" class="form-control form-control-sm font-monospace fw-bold bn-digit" value="{{ old('monthly_salary', $user->monthly_salary) }}">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Salary Pay Day of Month</label>
                            <input type="number" min="1" max="31" name="salary_pay_day" class="form-control form-control-sm bn-digit" value="{{ old('salary_pay_day', $user->salary_pay_day ?? 5) }}" placeholder=" 5 (5th of each month)">
                        </div>
                    </div>
                </div>

                <!-- 4. References & Notes -->
                <div class="staff-card p-3 p-md-4">
                    <div class="staff-section-title">
                        <i class="fas fa-user-tag text-info"></i>
                        <span>4. Reference & Internal Notes</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Reference Person Name</label>
                            <input type="text" name="reference_name" class="form-control form-control-sm" value="{{ old('reference_name', $user->reference_name) }}">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Reference Person Contact</label>
                            <input type="text" name="reference_phone" class="form-control form-control-sm font-monospace bn-digit" value="{{ old('reference_phone', $user->reference_phone) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Permanent / Present Address</label>
                            <textarea name="bio" class="form-control form-control-sm" rows="2" placeholder="Full residential address">{{ old('bio', $user->bio) }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Internal Admin Confidential Notes</label>
                            <textarea name="note" class="form-control form-control-sm" rows="2" placeholder="Only visible to Super Admins">{{ old('note', $user->note) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Avatar & Attached Documents -->
            <div class="col-12 col-lg-4">
                <!-- Avatar Upload Card -->
                <div class="staff-card p-3 p-md-4 mb-3 text-center">
                    <div class="staff-section-title justify-content-center">
                        <i class="fas fa-camera text-primary"></i>
                        <span>Profile Photo</span>
                    </div>
                    
                    <div class="mb-3">
                        @if($user->photo)
                            <img src="{{ asset($user->photo) }}" id="avatarPreview" alt="{{ $user->name }}" class="rounded-circle border shadow-xs" style="width: 96px; height: 96px; object-fit: cover;">
                        @else
                            <div id="avatarFallback" class="rounded-circle bg-light border text-secondary d-inline-flex align-items-center justify-content-center fw-bold fs-3 mx-auto shadow-xs" style="width: 96px; height: 96px;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <img id="avatarPreview" src="" alt="Preview" class="rounded-circle border shadow-xs d-none mx-auto" style="width: 96px; height: 96px; object-fit: cover;">
                        @endif
                    </div>

                    <div class="text-start">
                        <label class="form-label">Replace Photo</label>
                        <input type="file" name="photo" class="form-control form-control-sm" accept="image/*" onchange="previewUserPhoto(this)">
                        <small class="text-muted d-block mt-1" style="font-size: 11px;">Max file size: 2MB. Format: JPG, PNG, WEBP.</small>
                    </div>
                </div>

                <!-- Existing Documents Card -->
                <div class="staff-card p-3 p-md-4 mb-3">
                    <div class="staff-section-title">
                        <i class="fas fa-folder-open text-secondary"></i>
                        <span>Existing Documents</span>
                    </div>

                    @if($documents->count() > 0)
                        <div class="d-flex flex-column gap-2 mb-3">
                            @foreach($documents as $doc)
                                <div class="doc-preview-pill">
                                    <div class="d-flex align-items-center gap-2 text-truncate min-w-0">
                                        <i class="fas fa-file-pdf text-danger flex-shrink-0"></i>
                                        <span class="small fw-semibold text-dark text-truncate" style="max-width: 175px; font-size: 12px;">
                                            {{ $doc->doc_title }}
                                        </span>
                                    </div>
                                    <a href="{{ route('admin.roles.users.download_doc', $doc->id) }}" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center justify-content-center p-0 flex-shrink-0 shadow-xs" style="width: 28px; height: 28px;" title="Download">
                                        <i class="fas fa-download" style="font-size: 11px;"></i>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted small mb-3" style="font-size: 12px;">No documents previously uploaded for this staff member.</p>
                    @endif

                    <!-- Attach New Documents -->
                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2.5">
                            <span class="small fw-bold text-dark d-inline-flex align-items-center" style="font-size: 12px;">
                                <i class="fas fa-plus me-1.5 text-primary"></i> <span>Upload More Files</span>
                            </span>
                            <button type="button" class="btn btn-outline-primary btn-sm py-1 px-2.5 fw-semibold shadow-xs" style="font-size: 11px;" onclick="addDocumentRow()">
                                + Add Row
                            </button>
                        </div>

                        <div id="newDocumentsContainer" class="d-flex flex-column gap-2">
                            <div class="p-2.5 border bg-light bg-opacity-50 doc-input-row">
                                <input type="text" name="doc_titles[]" class="form-control form-control-sm mb-2" placeholder="Document Title ( NID Copy)">
                                <input type="file" name="doc_files[]" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Security Quick Note -->
                <div class="alert alert-info border-0 p-3 small bg-info-subtle text-info mb-0 shadow-xs" style="border-radius: 8px !important;">
                    <h6 class="fw-bold mb-1 d-flex align-items-center" style="font-size: 12.5px;">
                        <i class="fas fa-shield-halved me-1.5"></i> <span>Privilege Guard</span>
                    </h6>
                    <p class="mb-0" style="font-size: 11.5px; line-height: 1.45;">
                        Password changes and account suspension cannot be done from this form. Use the dedicated <strong>Status Control</strong> or <strong>Reset Password</strong> buttons in the staff list.
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function previewUserPhoto(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('avatarPreview');
                const fallback = document.getElementById('avatarFallback');
                img.src = e.target.result;
                img.classList.remove('d-none');
                if (fallback) fallback.classList.add('d-none');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function addDocumentRow() {
        const container = document.getElementById('newDocumentsContainer');
        const div = document.createElement('div');
        div.className = 'p-2.5 pt-4 border bg-light bg-opacity-50 doc-input-row position-relative';
        div.innerHTML = `
            <button type="button" class="btn-close position-absolute top-0 end-0 p-1 me-1.5 mt-1.5 shadow-none" style="font-size: 9px;" onclick="this.parentElement.remove()" title="Remove Row"></button>
            <input type="text" name="doc_titles[]" class="form-control form-control-sm mb-2" placeholder="Document Title">
            <input type="file" name="doc_files[]" class="form-control form-control-sm">
        `;
        container.appendChild(div);
    }
</script>
@endsection