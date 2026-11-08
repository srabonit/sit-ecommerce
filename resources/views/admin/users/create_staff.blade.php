
@extends('layouts.admin')

@section('title', 'Add Staff & Admin - ' . config('app.name'))

@section('admin_content')
<style>
    /* =========================================================
       ADD STAFF - KAIADMIN LITE CLASSIC / WHITE THEME
    ========================================================= */

    #createStaffRoot {
        --staff-primary: #3b82f6;
        --staff-primary-hover: #2563eb;
        --staff-text: #343a40;
        --staff-muted: #6c757d;
        --staff-border: #e5e7eb;
        --staff-soft: #f8f9fa;
        --staff-radius: 5px;
        color: var(--staff-text);
        font-size: 13px;
    }

    #createStaffRoot .staff-main-card,
    #createStaffRoot .staff-section-card {
        background: #fff;
        border: 1px solid var(--staff-border);
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .035);
    }

    #createStaffRoot .staff-main-card {
        overflow: hidden;
    }

    #createStaffRoot .card-header-custom {
        background: #fff;
        border-bottom: 1px solid var(--staff-border);
        padding: 16px 20px;
    }

    #createStaffRoot .staff-page-title {
        font-size: 16px;
        font-weight: 700;
        color: #343a40;
        line-height: 1.4;
    }

    #createStaffRoot .staff-page-subtitle {
        font-size: 11.5px;
        color: var(--staff-muted);
        margin-top: 3px;
    }

    #createStaffRoot .staff-section-card {
        padding: 18px;
        margin-bottom: 18px;
    }

    #createStaffRoot .section-heading {
        display: flex;
        align-items: center;
        gap: 9px;
        padding-bottom: 12px;
        margin-bottom: 16px;
        border-bottom: 1px solid #edf0f2;
    }

    #createStaffRoot .section-icon {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
        background: #eff6ff;
        color: var(--staff-primary);
        font-size: 13px;
        flex-shrink: 0;
    }

    #createStaffRoot .section-heading h6 {
        font-size: 13px;
        font-weight: 700;
        color: #343a40;
        margin: 0;
    }

    #createStaffRoot .section-heading p {
        font-size: 11px;
        color: var(--staff-muted);
        margin: 2px 0 0;
    }

    #createStaffRoot .form-label {
        display: block;
        font-size: 11.5px;
        font-weight: 600;
        color: #495057;
        margin-bottom: 6px;
    }

    #createStaffRoot .form-control,
    #createStaffRoot .form-select {
        min-height: 36px;
        border: 1px solid #dce1e7;
        border-radius: var(--staff-radius);
        padding: 7px 10px;
        font-size: 12.5px;
        color: #343a40;
        background-color: #fff;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #createStaffRoot .form-control:focus,
    #createStaffRoot .form-select:focus {
        border-color: var(--staff-primary);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, .10);
    }

    #createStaffRoot .form-control::placeholder {
        color: #a0a7b1;
        opacity: 1;
    }

    #createStaffRoot input[type="file"].form-control {
        padding: 5px 8px;
        font-size: 11.5px;
    }

    #createStaffRoot .font-monospace,
    #createStaffRoot .bn-digit {
        font-weight: 600;
    }

    #createStaffRoot .required-mark {
        color: #dc3545;
        font-weight: 700;
    }

    #createStaffRoot .required-info {
        background: #f8faff;
        border: 1px solid #e4ecfb;
        border-left: 3px solid var(--staff-primary);
        border-radius: 5px;
        padding: 10px 13px;
        color: #596579;
        font-size: 11.5px;
        margin-bottom: 18px;
    }

    #createStaffRoot .required-info i {
        color: var(--staff-primary);
        margin-right: 6px;
    }

    #createStaffRoot .doc-upload-zone {
        background: #fafbfc;
        border: 1px solid var(--staff-border);
        border-radius: 6px;
        padding: 16px;
    }

    #createStaffRoot .doc-row {
        background: #fff;
        border: 1px solid var(--staff-border);
        border-radius: 5px;
        padding: 9px 7px;
        margin-bottom: 9px;
    }

    #createStaffRoot .doc-row:last-child {
        margin-bottom: 0;
    }

    #createStaffRoot .doc-row .form-control {
        min-width: 0;
    }

    #createStaffRoot .btn {
        border-radius: 5px;
        font-size: 12px;
        font-weight: 600;
        transition: all .15s ease;
    }

    #createStaffRoot .header-back-btn {
        padding: 7px 13px;
        border-color: #dce1e7;
        color: #596579;
        background: #fff;
    }

    #createStaffRoot .header-back-btn:hover {
        background: #f5f6f8;
        border-color: #cbd2da;
        color: #343a40;
    }

    #createStaffRoot .btn-add-doc {
        padding: 7px 12px;
        white-space: nowrap;
    }

    #createStaffRoot .btn-submit-staff {
        background: var(--staff-primary);
        border: 1px solid var(--staff-primary);
        color: #fff;
        padding: 9px 22px;
        min-height: 38px;
    }

    #createStaffRoot .btn-submit-staff:hover {
        background: var(--staff-primary-hover);
        border-color: var(--staff-primary-hover);
        color: #fff;
    }

    #createStaffRoot .remove-doc-btn {
        width: 30px;
        height: 30px;
        padding: 0;
    }

    #createStaffRoot .staff-form-footer {
        border-top: 1px solid var(--staff-border);
        padding-top: 16px;
        margin-top: 4px;
    }

    @media (max-width: 767.98px) {
        #createStaffRoot {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        #createStaffRoot .card-header-custom {
            padding: 13px 15px;
        }

        #createStaffRoot .staff-page-title {
            font-size: 15px;
        }

        #createStaffRoot .staff-section-card {
            padding: 13px;
            margin-bottom: 14px;
        }

        #createStaffRoot .doc-upload-zone {
            padding: 12px;
        }

        #createStaffRoot .header-back-btn,
        #createStaffRoot .btn-add-doc,
        #createStaffRoot .btn-submit-staff {
            width: 100%;
            justify-content: center;
        }

        #createStaffRoot .staff-form-footer {
            display: flex;
            flex-direction: column;
        }
    }

    @media (max-width: 420px) {
        #createStaffRoot .doc-row {
            padding: 8px 5px;
        }

        #createStaffRoot .doc-row .form-control {
            font-size: 11px;
        }

        #createStaffRoot .section-heading h6 {
            font-size: 12px;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="createStaffRoot">

    <div class="staff-main-card">

        <!-- Page Header -->
        <div class="card-header-custom d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 staff-create-header">

            <div>
                <h5 class="staff-page-title mb-0 d-flex align-items-center">
                    <i class="fas fa-user-shield text-primary me-2"></i>
                    Add Staff / Administrator
                </h5>
                <p class="staff-page-subtitle mb-0">
                    Create a new staff account and assign access permissions.
                </p>
            </div>

            <a href="{{ route('admin.roles.users') }}"
               class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center justify-content-center header-back-btn">
                <i class="fas fa-arrow-left me-2"></i>
                Back to Staff List
            </a>

        </div>

        <!-- Form Body -->
        <div class="card-body p-3 p-md-4">

            <form action="{{ route('admin.roles.users.store_staff') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Required Fields Information -->
                <div class="required-info">
                    <i class="fas fa-info-circle"></i>
                    Fields marked with <span class="required-mark">*</span> are mandatory:
                    Name, Role, Phone, Email and Password. All other fields are optional.
                </div>

                <!-- =========================================
                     SECTION 01: ACCOUNT INFORMATION
                ========================================== -->
                <div class="staff-section-card">

                    <div class="section-heading">
                        <span class="section-icon">
                            <i class="fas fa-user-lock"></i>
                        </span>
                        <div>
                            <h6>Account & Access Information</h6>
                            <p>Enter the staff member's basic account and login details.</p>
                        </div>
                    </div>

                    <div class="row g-3">

                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Full Name <span class="required-mark">*</span>
                            </label>
                            <input type="text" name="name" class="form-control fw-semibold" placeholder="Full Name" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Role Permission <span class="required-mark">*</span>
                            </label>
                            <select name="role" class="form-select fw-semibold" required>
                                <option value="" disabled selected>-- Select Staff / Admin Role --</option>
                                @foreach($staffRoles as $r)
                                    <option value="{{ $r }}">{{ ucwords(str_replace('_', ' ', $r)) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Primary Phone <span class="required-mark">*</span>
                            </label>
                            <input type="text" name="phone" class="form-control font-monospace bn-digit" placeholder="01XXXXXXXXX" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Primary Email <span class="required-mark">*</span>
                            </label>
                            <input type="email" name="email" class="form-control font-monospace" placeholder="staff@domain.com" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Password <span class="required-mark">*</span>
                            </label>
                            <input type="password" name="password" class="form-control" placeholder="Minimum 6 characters" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Confirm Password <span class="required-mark">*</span>
                            </label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Re-type password" required>
                        </div>

                    </div>
                </div>

                <!-- =========================================
                     SECTION 02: EMPLOYMENT INFORMATION
                ========================================== -->
                <div class="staff-section-card">

                    <div class="section-heading">
                        <span class="section-icon">
                            <i class="fas fa-briefcase"></i>
                        </span>
                        <div>
                            <h6>Employment & Personal Information</h6>
                            <p>Additional staff details, branch assignment and employment records.</p>
                        </div>
                    </div>

                    <div class="row g-3">

                        <div class="col-12 col-md-6">
                            <label class="form-label">Designation</label>
                            <input type="text" name="designation" class="form-control" placeholder="Sales Executive, Branch Manager">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Branch Assignment</label>
                            <select name="branch_id" class="form-select">
                                <option value="">Head Office / Online Store</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name ?? ('Branch #' . $b->id) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">Alternate Phone</label>
                            <input type="text" name="phone_alt" class="form-control font-monospace bn-digit" placeholder="Emergency contact">
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">Alternate Email</label>
                            <input type="email" name="email_alt" class="form-control font-monospace" placeholder="Personal email">
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">NID Number</label>
                            <input type="text" name="nid_number" class="form-control font-monospace bn-digit" placeholder="National ID">
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">Monthly Basic Salary (BDT)</label>
                            <input type="number" step="0.01" name="monthly_salary" class="form-control font-monospace bn-digit" placeholder="25000">
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">Joining Date</label>
                            <input type="date" name="joining_date" class="form-control bn-digit" value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">Profile Photo</label>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                        </div>

                    </div>
                </div>

                <!-- =========================================
                     SECTION 03: EMPLOYEE DOCUMENTS
                ========================================== -->
                <div class="staff-section-card">

                    <div class="section-heading">
                        <span class="section-icon">
                            <i class="fas fa-folder-open"></i>
                        </span>
                        <div>
                            <h6>Employee Documents</h6>
                            <p>Upload NID, CV, certificates and other supporting documents.</p>
                        </div>
                    </div>

                    <div class="doc-upload-zone">

                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">

                            <div>
                                <div class="fw-semibold text-dark" style="font-size:12px;">
                                    <i class="fas fa-file-upload text-primary me-1"></i>
                                    Document Attachments
                                </div>
                                <div class="text-muted mt-1" style="font-size:11px;">
                                    You can add multiple employee documents.
                                </div>
                            </div>

                            <button type="button"
                                    id="addMoreDocBtn"
                                    class="btn btn-sm btn-outline-primary d-inline-flex align-items-center justify-content-center btn-add-doc">
                                <i class="fas fa-plus me-2"></i>
                                Add Another Document
                            </button>

                        </div>

                        <div id="documentContainer">

                            <div class="row g-2 align-items-center doc-row mx-0">

                                <div class="col-12 col-md-5 px-1">
                                    <input type="text" name="doc_titles[]" class="form-control form-control-sm" placeholder="Document Name ( National ID Front/Back)">
                                </div>

                                <div class="col-10 col-md-6 px-1">
                                    <input type="file" name="doc_files[]" class="form-control form-control-sm">
                                </div>

                                <div class="col-2 col-md-1 px-1 text-end">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger remove-doc-btn d-none d-inline-flex align-items-center justify-content-center p-0"
                                            style="width: 30px; height: 30px;"
                                            title="Remove">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>

                            </div>

                        </div>

                    </div>
                </div>

                <!-- =========================================
                     FORM SUBMISSION
                ========================================== -->
                <div class="staff-form-footer d-flex flex-column flex-sm-row justify-content-end align-items-sm-center gap-2">

                    <button type="submit" class="btn btn-primary fw-bold btn-submit-staff">
                        <i class="fas fa-save me-2"></i>
                        Save Staff / Admin
                    </button>

                </div>

            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const docContainer = document.getElementById('documentContainer');
    const addBtn = document.getElementById('addMoreDocBtn');

    addBtn.addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'row g-2 align-items-center doc-row mx-0';
        row.innerHTML = `
            <div class="col-12 col-md-5 px-1">
                <input type="text" name="doc_titles[]" class="form-control form-control-sm" placeholder="Document Name ( Resume, Certificate)">
            </div>
            <div class="col-10 col-md-6 px-1">
                <input type="file" name="doc_files[]" class="form-control form-control-sm">
            </div>
            <div class="col-2 col-md-1 px-1 text-end">
                <button type="button" class="btn btn-sm btn-outline-danger remove-doc-btn d-inline-flex align-items-center justify-content-center p-0" style="width: 30px; height: 30px;" title="Remove">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        docContainer.appendChild(row);

        row.querySelector('.remove-doc-btn').addEventListener('click', function () {
            row.remove();
        });
    });
});
</script>
@endsection
