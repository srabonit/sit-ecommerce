@extends('layouts.admin')

@section('title', 'Add Staff & Admin - ' . config('app.name'))

@section('admin_content')
<style>
    /* Global Strict 5px Radius for Buttons, Inputs & Controls */
    #createStaffRoot .btn,
    #createStaffRoot .btn-sm,
    #createStaffRoot button,
    #createStaffRoot .form-control,
    #createStaffRoot .form-select,
    #createStaffRoot .alert,
    #createStaffRoot .doc-row {
        border-radius: 5px !important;
    }

    /* Enterprise Card Surface */
    #createStaffRoot .staff-main-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }

    #createStaffRoot .card-header-custom {
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
        padding: 16px 22px;
    }

    /* Form Typography & Controls */
    #createStaffRoot .form-label {
        font-size: 11.5px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 5px;
        letter-spacing: 0.01em;
    }

    #createStaffRoot .form-control,
    #createStaffRoot .form-select {
        font-size: 12.5px;
        border: 1px solid #cbd5e1;
        padding: 7px 12px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.02);
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    #createStaffRoot .form-control:focus,
    #createStaffRoot .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    #createStaffRoot .form-control::placeholder {
        color: #94a3b8;
    }

    /* Form Section Blocks */
    .form-section-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px !important;
        padding: 18px 20px;
        margin-bottom: 20px;
    }

    .section-sub-heading {
        font-size: 12px;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding-bottom: 10px;
        margin-bottom: 16px;
        border-bottom: 1px solid #f1f5f9;
    }

    /* Dynamic Document Upload Box */
    .doc-upload-zone {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px !important;
        padding: 16px 18px;
    }

    .doc-row {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        padding: 10px;
        margin-bottom: 8px !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.02);
    }
    .doc-row:last-child {
        margin-bottom: 0 !important;
    }

    /* Corporate Primary Submit Button */
    .btn-submit-staff {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        font-size: 13px;
        padding: 9px 28px;
        transition: background-color 0.15s ease, box-shadow 0.15s ease;
    }
    .btn-submit-staff:hover {
        background-color: #1d4ed8 !important;
        border-color: #1d4ed8 !important;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
    }

    /* Safe Bengali Digits */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Mobile Responsive Optimizations */
    @media (max-width: 767.98px) {
        #createStaffRoot .card-header-custom {
            padding: 14px 16px;
        }
        .staff-create-header h5 {
            font-size: 16px !important;
        }
        .form-section-box,
        .doc-upload-zone {
            padding: 14px !important;
            margin-bottom: 16px !important;
        }
        .header-back-btn,
        .btn-add-doc,
        .btn-submit-staff {
            width: 100% !important;
            justify-content: center;
            text-align: center;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="createStaffRoot">
    <div class="card border-0 staff-main-card overflow-hidden">
        <!-- Header -->
        <div class="card-header-custom d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2.5 staff-create-header">
            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center" style="font-size: 17px;">
                <i class="fas fa-user-shield text-primary me-2"></i>
                <span>Add Staff / Administrator</span>
            </h5>
            <a href="{{ route('admin.roles.users') }}" class="btn btn-outline-secondary btn-sm px-3 py-1.5 fw-semibold d-inline-flex align-items-center shadow-xs header-back-btn">
                <i class="fas fa-arrow-left me-1.5"></i>
                <span>Back</span>
            </a>
        </div>

        <!-- Body -->
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('admin.roles.users.store_staff') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <!-- Mandatory Fields Notice -->
                <div class="alert alert-light border small text-muted mb-3 py-2 px-3 shadow-xs" style="font-size: 12px; background-color: #f8fafc;">
                    <span class="text-danger fw-bold">*</span> Marked fields (Name, Role, Phone, Email, Password) are strictly mandatory. All other fields are optional.
                </div>

                <!-- Mandatory Credentials & Access Section -->
                <div class="form-section-box">
                    <div class="section-sub-heading">Required Account Credentials & Role</div>

                    <!-- Row 1: Name & Role -->
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control fw-semibold" placeholder="Full Name" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Role Permission <span class="text-danger">*</span></label>
                            <select name="role" class="form-select fw-semibold" required>
                                <option value="" disabled selected>-- Select Staff / Admin Role --</option>
                                @foreach($staffRoles as $r)
                                    <option value="{{ $r }}">{{ ucwords(str_replace('_', ' ', $r)) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Row 2: Primary Phone & Primary Email -->
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Primary Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control font-monospace bn-digit" placeholder="01XXXXXXXXX" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Primary Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control font-monospace" placeholder="staff@domain.com" required>
                        </div>
                    </div>

                    <!-- Row 3: Password & Confirm Password -->
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" placeholder="Minimum 6 characters" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Re-type password" required>
                        </div>
                    </div>
                </div>

                <!-- Optional Details & Employment Info Section -->
                <div class="form-section-box">
                    <div class="section-sub-heading">Optional Details & Employment Info</div>

                    <!-- Row 4: Designation & Branch -->
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label text-muted">Designation</label>
                            <input type="text" name="designation" class="form-control" placeholder=" Sales Executive, Branch Manager">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-muted">Branch Assignment</label>
                            <select name="branch_id" class="form-select">
                                <option value="">Head Office / Online Store</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name ?? ('Branch #' . $b->id) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Row 5: Alternate Contacts & NID -->
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label text-muted">Alternate Phone</label>
                            <input type="text" name="phone_alt" class="form-control font-monospace bn-digit" placeholder="Emergency contact">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label text-muted">Alternate Email</label>
                            <input type="email" name="email_alt" class="form-control font-monospace" placeholder="Personal email">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label text-muted">NID Number</label>
                            <input type="text" name="nid_number" class="form-control font-monospace bn-digit" placeholder="National ID">
                        </div>
                    </div>

                    <!-- Row 6: Salary, Joining Date & Photo -->
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label text-muted">Monthly Basic Salary (BDT)</label>
                            <input type="number" step="0.01" name="monthly_salary" class="form-control font-monospace bn-digit" placeholder="25000">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label text-muted">Joining Date</label>
                            <input type="date" name="joining_date" class="form-control bn-digit" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label text-muted">Profile Photo</label>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                        </div>
                    </div>
                </div>

                <!-- Dynamic Multiple Document Section -->
                <div class="doc-upload-zone mb-4">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center" style="font-size: 13px;">
                            <i class="fas fa-folder-open text-primary me-2"></i>
                            <span>Employee Documents (NID, CV, Certificates)</span>
                        </h6>
                        <button type="button" id="addMoreDocBtn" class="btn btn-sm btn-outline-primary px-3 py-1.5 fw-semibold d-inline-flex align-items-center shadow-xs btn-add-doc" style="font-size: 11.5px;">
                            <i class="fas fa-plus me-1.5"></i>
                            <span>Add Another Document</span>
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
                                <button type="button" class="btn btn-sm btn-outline-danger remove-doc-btn d-none d-inline-flex align-items-center justify-content-center p-0" style="width: 30px; height: 30px;" title="Remove">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="text-end pt-3 border-top">
                    <button type="submit" class="btn btn-warning fw-bold text-white shadow-xs btn-submit-staff">
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