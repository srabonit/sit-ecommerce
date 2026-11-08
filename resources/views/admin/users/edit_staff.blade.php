
@extends('layouts.admin')

@section('title', 'Edit Staff: ' . $user->name . ' - ' . config('app.name'))

@section('admin_content')

<style>
    /* =========================================================
       EDIT STAFF - KAIADMIN CLASSIC / WHITE CLASSIC
       All styles are scoped to #editStaffRoot
    ========================================================= */

    #editStaffRoot {
        --staff-primary: #1572e8;
        --staff-primary-soft: #eef5ff;
        --staff-success: #31ce77;
        --staff-warning: #ffad46;
        --staff-info: #48abf7;
        --staff-text: #2a2f5b;
        --staff-muted: #8d9498;
        --staff-border: #ebedf2;
        --staff-surface: #ffffff;
        --staff-bg: #f8f9fa;
        --staff-radius: 7px;
    }

    #editStaffRoot,
    #editStaffRoot * {
        box-sizing: border-box;
    }

    #editStaffRoot .staff-card {
        background: var(--staff-surface);
        border: 1px solid var(--staff-border);
        border-radius: var(--staff-radius);
        box-shadow: 0 1px 3px rgba(0, 0, 0, .035);
        min-width: 0;
    }

    #editStaffRoot .staff-section-title {
        display: flex;
        align-items: center;
        gap: 9px;
        padding-bottom: 13px;
        margin-bottom: 18px;
        border-bottom: 1px solid #f0f1f5;
        color: var(--staff-text);
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .045em;
        text-transform: uppercase;
        line-height: 1.5;
    }

    #editStaffRoot .staff-section-title i {
        font-size: 15px;
    }

    /* Header */
    #editStaffRoot .staff-page-title {
        color: var(--staff-text);
        font-size: 18px;
        font-weight: 700;
        line-height: 1.4;
    }

    #editStaffRoot .header-subtitle-text {
        color: var(--staff-muted);
        font-size: 11.5px;
        line-height: 1.5;
    }

    #editStaffRoot .edit-staff-header-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    /* Buttons */
    #editStaffRoot .btn,
    #editStaffRoot button {
        border-radius: 5px;
        font-weight: 600;
        transition: background-color .15s ease, border-color .15s ease, box-shadow .15s ease;
    }

    #editStaffRoot .edit-staff-header-actions .btn {
        min-height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        font-size: 11.5px;
        white-space: nowrap;
    }

    #editStaffRoot .btn-save-staff {
        background: var(--staff-primary);
        border-color: var(--staff-primary);
        color: #fff;
    }

    #editStaffRoot .btn-save-staff:hover {
        background: #1263ca;
        border-color: #1263ca;
        color: #fff;
    }

    /* Form Controls */
    #editStaffRoot .form-label {
        display: block;
        margin-bottom: 6px;
        color: #495057;
        font-size: 11.5px;
        font-weight: 600;
        line-height: 1.5;
    }

    #editStaffRoot .form-control,
    #editStaffRoot .form-select {
        min-height: 34px;
        border: 1px solid #e6e8eb;
        border-radius: 5px;
        background-color: #fff;
        color: #343a40;
        font-size: 12px;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #editStaffRoot .form-control {
        padding: 7px 10px;
    }

    #editStaffRoot .form-select {
        padding: 6px 30px 6px 10px;
    }

    #editStaffRoot textarea.form-control {
        min-height: 65px;
        resize: vertical;
    }

    #editStaffRoot .form-control:focus,
    #editStaffRoot .form-select:focus {
        border-color: var(--staff-primary);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .09);
    }

    #editStaffRoot .form-control::placeholder {
        color: #adb5bd;
        opacity: 1;
    }

    #editStaffRoot .form-text,
    #editStaffRoot .field-hint {
        display: block;
        margin-top: 5px;
        color: #9ba1a6;
        font-size: 10.5px;
        line-height: 1.5;
    }

    #editStaffRoot .font-monospace,
    #editStaffRoot .bn-digit {
        font-weight: 600;
    }

    /* Profile Photo */
    #editStaffRoot .avatar-preview {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border: 3px solid #fff;
        outline: 1px solid var(--staff-border);
        box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
    }

    #editStaffRoot .avatar-fallback {
        width: 100px;
        height: 100px;
        font-size: 30px;
    }

    /* Existing Document */
    #editStaffRoot .doc-preview-pill {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        min-width: 0;
        padding: 9px 10px;
        border: 1px solid var(--staff-border);
        border-radius: 5px;
        background: #fbfcfe;
        transition: background-color .15s ease, border-color .15s ease;
    }

    #editStaffRoot .doc-preview-pill:hover {
        background: var(--staff-primary-soft);
        border-color: #d8e7fb;
    }

    #editStaffRoot .doc-title-text {
        min-width: 0;
        overflow: hidden;
        color: #495057;
        font-size: 11.5px;
        font-weight: 600;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* New Document Upload */
    #editStaffRoot .doc-input-row {
        position: relative;
        padding: 10px;
        border: 1px dashed #d8dee8;
        border-radius: 6px;
        background: #fafbfd;
    }

    #editStaffRoot .doc-input-row .form-control {
        background-color: #fff;
    }

    #editStaffRoot .doc-input-row .btn-close {
        position: absolute;
        top: 7px;
        right: 7px;
        z-index: 2;
        font-size: 9px;
    }

    /* Alerts */
    #editStaffRoot .alert {
        border-radius: 6px;
        font-size: 12px;
    }

    #editStaffRoot .alert ul {
        line-height: 1.7;
    }

    /* Security Note */
    #editStaffRoot .security-note {
        border: 1px solid #dceefb;
        border-radius: 7px;
        background: #f0f8ff;
        color: #2878a8;
    }

    #editStaffRoot .security-note p {
        font-size: 11.5px;
        line-height: 1.6;
    }

    /* Responsive */
    @media (max-width: 991.98px) {
        #editStaffRoot .staff-card {
            padding: 18px !important;
        }
    }

    @media (max-width: 767.98px) {
        #editStaffRoot {
            padding-right: 10px;
            padding-left: 10px;
        }

        #editStaffRoot .staff-page-header {
            align-items: flex-start !important;
        }

        #editStaffRoot .staff-page-title {
            font-size: 16px;
        }

        #editStaffRoot .header-subtitle-text {
            margin-left: 0 !important;
            padding-left: 0 !important;
            margin-top: 6px;
        }

        #editStaffRoot .edit-staff-header-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            gap: 7px;
        }

        #editStaffRoot .edit-staff-header-actions .btn {
            width: 100%;
            min-height: 36px;
            padding: 7px 9px;
            font-size: 11px;
        }

        #editStaffRoot .edit-staff-header-actions .btn-save-staff {
            grid-column: 1 / -1;
        }

        #editStaffRoot .staff-card {
            padding: 15px !important;
            margin-bottom: 12px !important;
        }

        #editStaffRoot .staff-section-title {
            margin-bottom: 15px;
            padding-bottom: 11px;
            font-size: 11.5px;
        }
    }

    @media (max-width: 380px) {
        #editStaffRoot .staff-page-title {
            font-size: 14px;
        }

        #editStaffRoot .staff-section-title {
            font-size: 10.5px;
        }

        #editStaffRoot .staff-card {
            padding: 12px !important;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="editStaffRoot">

    <!-- =====================================================
         PAGE HEADER
    ====================================================== -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 staff-page-header">

        <div class="min-w-0">
            <div class="d-flex align-items-center gap-2">

                <a href="{{ route('admin.roles.users') }}"
                   class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center justify-content-center flex-shrink-0"
                   style="width: 32px; height: 32px;"
                   title="Back to Directory">
                    <i class="fas fa-arrow-left"></i>
                </a>

                <h5 class="staff-page-title mb-0 d-flex align-items-center flex-wrap">
                    <i class="fas fa-user-pen text-primary me-2"></i>
                    <span>Edit Staff Profile:</span>
                    <span class="text-primary ms-1">{{ $user->name }}</span>
                </h5>

            </div>

            <small class="header-subtitle-text d-block ms-4 ps-2">
                Update employment details, branch assignment, credentials & attached files
            </small>
        </div>

        <div class="edit-staff-header-actions">

            <a href="{{ route('admin.roles.users.show', $user->id) }}"
               class="btn btn-outline-info btn-sm px-3 shadow-xs">
                <i class="fas fa-eye"></i>
                <span>View Profile</span>
            </a>

            <a href="{{ route('admin.roles.users') }}"
               class="btn btn-light btn-sm px-3 border text-muted shadow-xs">
                <span>Cancel</span>
            </a>

            <button type="button"
                    class="btn btn-primary btn-sm px-3 shadow-xs btn-save-staff"
                    onclick="document.getElementById('editStaffForm').submit();">
                <i class="fas fa-floppy-disk"></i>
                <span>Save Changes</span>
            </button>

        </div>
    </div>


    <!-- =====================================================
         ALERT MESSAGES
    ====================================================== -->

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3 py-2 px-3 border-0 shadow-xs d-flex align-items-center"
             role="alert">

            <i class="fas fa-circle-exclamation me-2"></i>

            <span class="fw-semibold">{{ session('error') }}</span>

            <button type="button"
                    class="btn-close btn-sm shadow-none py-2"
                    data-bs-dismiss="alert"></button>
        </div>
    @endif


    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3 py-3 px-3 border-0 shadow-xs"
             role="alert">

            <strong class="d-block mb-2">
                <i class="fas fa-triangle-exclamation me-1"></i>
                Please fix the following errors:
            </strong>

            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>

            <button type="button"
                    class="btn-close btn-sm shadow-none"
                    data-bs-dismiss="alert"></button>
        </div>
    @endif


    <!-- =====================================================
         EDIT STAFF FORM
    ====================================================== -->

    <form action="{{ route('admin.roles.users.update_staff', $user->id) }}"
          method="POST"
          enctype="multipart/form-data"
          id="editStaffForm">

        @csrf

        <div class="row g-3">

            <!-- =============================================
                 LEFT COLUMN
            ============================================== -->

            <div class="col-12 col-lg-8">

                <!-- 1. CORE PROFILE & ROLE ASSIGNMENT -->

                <div class="staff-card p-3 p-md-4 mb-3">

                    <div class="staff-section-title">
                        <i class="fas fa-id-card text-primary"></i>
                        <span>1. Core Profile & Role Assignment</span>
                    </div>

                    <div class="row g-3">

                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Full Name <span class="text-danger">*</span>
                            </label>

                            <input type="text"
                                   name="name"
                                   class="form-control form-control-sm fw-semibold"
                                   value="{{ old('name', $user->name) }}"
                                   required>
                        </div>


                        <div class="col-12 col-md-6">
                            <label class="form-label">Official Designation</label>

                            <input type="text"
                                   name="designation"
                                   class="form-control form-control-sm"
                                   value="{{ old('designation', $user->designation) }}"
                                   placeholder=" Senior Order Manager, Cashier">
                        </div>


                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Assigned Role (Access Level) <span class="text-danger">*</span>
                            </label>

                            <select name="role"
                                    class="form-select form-select-sm fw-semibold"
                                    required>

                                @foreach($staffRoles as $r)
                                    <option value="{{ $r }}" {{ old('role', $user->role) ===$r ? 'selected' : '' }}>
                                        {{ ucwords(str_replace('_', ' ', $r)) }} {{$r === 'super_admin' ? '👑 (Root System Owner)' : '' }}
                                    </option>
                                @endforeach

                            </select>

                            <small class="field-hint">
                                Permissions will adjust according to this role's RBAC matrix.
                            </small>
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

                            <small class="field-hint">
                                Branch managers will be scoped to their assigned hub.
                            </small>
                        </div>

                    </div>
                </div>


                <!-- 2. CONTACT & IDENTITY INFORMATION -->

                <div class="staff-card p-3 p-md-4 mb-3">

                    <div class="staff-section-title">
                        <i class="fas fa-phone-volume text-success"></i>
                        <span>2. Contact & Identity Verification</span>
                    </div>

                    <div class="row g-3">

                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Primary Email (Login Username) <span class="text-danger">*</span>
                            </label>

                            <input type="email"
                                   name="email"
                                   class="form-control form-control-sm font-monospace"
                                   value="{{ old('email', $user->email) }}"
                                   required>
                        </div>


                        <div class="col-12 col-md-6">
                            <label class="form-label">Secondary / Alternate Email</label>

                            <input type="email"
                                   name="email_alt"
                                   class="form-control form-control-sm font-monospace"
                                   value="{{ old('email_alt', $user->email_alt) }}"
                                   placeholder="Optional recovery email">
                        </div>


                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Primary Phone Number <span class="text-danger">*</span>
                            </label>

                            <input type="text"
                                   name="phone"
                                   class="form-control form-control-sm font-monospace fw-semibold bn-digit"
                                   value="{{ old('phone', $user->phone) }}"
                                   required>
                        </div>


                        <div class="col-12 col-md-6">
                            <label class="form-label">Alternate / Emergency Phone</label>

                            <input type="text"
                                   name="phone_alt"
                                   class="form-control form-control-sm font-monospace bn-digit"
                                   value="{{ old('phone_alt', $user->phone_alt) }}">
                        </div>


                        <div class="col-12 col-md-6">
                            <label class="form-label">National ID (NID) / Passport No</label>

                            <input type="text"
                                   name="nid_number"
                                   class="form-control form-control-sm font-monospace bn-digit"
                                   value="{{ old('nid_number', $user->nid_number) }}"
                                   placeholder=" 1990123456789">
                        </div>


                        <div class="col-12 col-md-6">
                            <label class="form-label">Date of Birth</label>

                            <input type="date"
                                   name="dob"
                                   class="form-control form-control-sm bn-digit"
                                   value="{{ old('dob', $user->dob) }}">
                        </div>

                    </div>
                </div>


                <!-- 3. EMPLOYMENT & PAYROLL -->

                <div class="staff-card p-3 p-md-4 mb-3">

                    <div class="staff-section-title">
                        <i class="fas fa-hand-holding-dollar text-warning"></i>
                        <span>3. Employment Terms & Payroll</span>
                    </div>

                    <div class="row g-3">

                        <div class="col-12 col-md-6">
                            <label class="form-label">Joining Date</label>

                            <input type="date"
                                   name="joining_date"
                                   class="form-control form-control-sm bn-digit"
                                   value="{{ old('joining_date', $user->joining_date) }}">
                        </div>


                        <div class="col-12 col-md-6">
                            <label class="form-label">Resignation Date (If inactive)</label>

                            <input type="date"
                                   name="resignation_date"
                                   class="form-control form-control-sm bn-digit"
                                   value="{{ old('resignation_date', $user->resignation_date) }}">
                        </div>


                        <div class="col-12 col-md-6">
                            <label class="form-label">Monthly Basic Salary (৳)</label>

                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   name="monthly_salary"
                                   class="form-control form-control-sm font-monospace fw-bold bn-digit"
                                   value="{{ old('monthly_salary', $user->monthly_salary) }}">
                        </div>


                        <div class="col-12 col-md-6">
                            <label class="form-label">Salary Pay Day of Month</label>

                            <input type="number"
                                   min="1"
                                   max="31"
                                   name="salary_pay_day"
                                   class="form-control form-control-sm bn-digit"
                                   value="{{ old('salary_pay_day', $user->salary_pay_day ?? 5) }}"
                                   placeholder=" 5 (5th of each month)">
                        </div>

                    </div>
                </div>


                <!-- 4. REFERENCES & NOTES -->

                <div class="staff-card p-3 p-md-4">

                    <div class="staff-section-title">
                        <i class="fas fa-user-tag text-info"></i>
                        <span>4. Reference & Internal Notes</span>
                    </div>

                    <div class="row g-3">

                        <div class="col-12 col-md-6">
                            <label class="form-label">Reference Person Name</label>

                            <input type="text"
                                   name="reference_name"
                                   class="form-control form-control-sm"
                                   value="{{ old('reference_name', $user->reference_name) }}">
                        </div>


                        <div class="col-12 col-md-6">
                            <label class="form-label">Reference Person Contact</label>

                            <input type="text"
                                   name="reference_phone"
                                   class="form-control form-control-sm font-monospace bn-digit"
                                   value="{{ old('reference_phone', $user->reference_phone) }}">
                        </div>


                        <div class="col-12">
                            <label class="form-label">Permanent / Present Address</label>

                            <textarea name="bio"
                                      class="form-control form-control-sm"
                                      rows="2"
                                      placeholder="Full residential address">{{ old('bio', $user->bio) }}</textarea>
                        </div>


                        <div class="col-12">
                            <label class="form-label">Internal Admin Confidential Notes</label>

                            <textarea name="note"
                                      class="form-control form-control-sm"
                                      rows="2"
                                      placeholder="Only visible to Super Admins">{{ old('note', $user->note) }}</textarea>
                        </div>

                    </div>
                </div>

            </div>


            <!-- =============================================
                 RIGHT COLUMN
            ============================================== -->

            <div class="col-12 col-lg-4">

                <!-- PROFILE PHOTO -->

                <div class="staff-card p-3 p-md-4 mb-3">

                    <div class="staff-section-title justify-content-center">
                        <i class="fas fa-camera text-primary"></i>
                        <span>Profile Photo</span>
                    </div>

                    <div class="mb-3">

                        @if($user->photo)

                            <img src="{{ asset($user->photo) }}"
                                 id="avatarPreview"
                                 alt="{{ $user->name }}"
                                 class="rounded-circle avatar-preview">

                        @else

                            <div id="avatarFallback"
                                 class="rounded-circle bg-light border text-secondary d-inline-flex align-items-center justify-content-center fw-bold mx-auto avatar-fallback shadow-xs">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>

                            <img id="avatarPreview"
                                 src=""
                                 alt="Preview"
                                 class="rounded-circle avatar-preview d-none mx-auto">

                        @endif

                    </div>

                    <div class="text-start">

                        <label class="form-label">Replace Photo</label>

                        <input type="file"
                               name="photo"
                               class="form-control form-control-sm"
                               accept="image/*"
                               onchange="previewUserPhoto(this)">

                        <small class="field-hint">
                            Max file size: 2MB. Format: JPG, PNG, WEBP.
                        </small>

                    </div>
                </div>


                <!-- EXISTING DOCUMENTS -->

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

                                        <span class="doc-title-text">
                                            {{ $doc->doc_title }}
                                        </span>

                                    </div>

                                    <a href="{{ route('admin.roles.users.download_doc', $doc->id) }}"
                                       class="btn btn-outline-primary btn-sm d-inline-flex align-items-center justify-content-center p-0 flex-shrink-0 shadow-xs"
                                       style="width: 29px; height: 29px;"
                                       title="Download">

                                        <i class="fas fa-download" style="font-size: 11px;"></i>

                                    </a>

                                </div>

                            @endforeach

                        </div>

                    @else

                        <div class="text-center py-3 mb-3">

                            <i class="fas fa-folder-open text-muted mb-2"
                               style="font-size: 25px; opacity: .55;"></i>

                            <p class="text-muted small mb-0" style="font-size: 11.5px;">
                                No documents previously uploaded for this staff member.
                            </p>

                        </div>

                    @endif


                    <!-- UPLOAD MORE DOCUMENTS -->

                    <div class="border-top pt-3">

                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">

                            <span class="small fw-bold text-dark d-inline-flex align-items-center"
                                  style="font-size: 12px;">

                                <i class="fas fa-plus me-2 text-primary"></i>
                                <span>Upload More Files</span>

                            </span>

                            <button type="button"
                                    class="btn btn-outline-primary btn-sm py-1 px-2 fw-semibold shadow-xs"
                                    style="font-size: 11px;"
                                    onclick="addDocumentRow()">

                                <i class="fas fa-plus me-1"></i>
                                Add Row

                            </button>

                        </div>


                        <div id="newDocumentsContainer" class="d-flex flex-column gap-2">

                            <div class="doc-input-row">

                                <input type="text"
                                       name="doc_titles[]"
                                       class="form-control form-control-sm mb-2"
                                       placeholder="Document Title ( NID Copy)">

                                <input type="file"
                                       name="doc_files[]"
                                       class="form-control form-control-sm">

                            </div>

                        </div>

                    </div>

                </div>


                <!-- SECURITY INFORMATION -->

                <div class="alert security-note p-3 mb-0 shadow-xs">

                    <h6 class="fw-bold mb-2 d-flex align-items-center"
                        style="font-size: 12.5px;">

                        <i class="fas fa-shield-halved me-2"></i>
                        <span>Privilege Guard</span>

                    </h6>

                    <p class="mb-0">
                        Password changes and account suspension cannot be done from this form.
                        Use the dedicated <strong>Status Control</strong> or
                        <strong>Reset Password</strong> buttons in the staff list.
                    </p>

                </div>

            </div>

        </div>

    </form>

</div>


<!-- =========================================================
     JAVASCRIPT - EXISTING FUNCTIONALITY PRESERVED
========================================================= -->

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

        div.className = 'doc-input-row pt-4';

        div.innerHTML = `
            <button type="button"
                    class="btn-close shadow-none"
                    onclick="this.parentElement.remove()"
                    title="Remove Row"></button>

            <input type="text"
                   name="doc_titles[]"
                   class="form-control form-control-sm mb-2"
                   placeholder="Document Title">

            <input type="file"
                   name="doc_files[]"
                   class="form-control form-control-sm">
        `;

        container.appendChild(div);
    }
</script>

@endsection
