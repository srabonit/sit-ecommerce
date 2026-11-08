
@extends('layouts.admin')

@section('title', 'Add Customer - ' . config('app.name'))

@section('admin_content')

<style>
/* =========================================================
   CREATE CUSTOMER — KAIADMIN LITE CLASSIC DESIGN
   Scoped CSS: #createCustomerRoot
========================================================= */

#createCustomerRoot {
    --customer-primary: var(--bs-primary, #1572e8);
    --customer-heading: #1a2035;
    --customer-text: #495057;
    --customer-muted: #8d9498;
    --customer-border: #ebedf2;
    --customer-bg: #f8f9fa;
    --customer-card: #ffffff;
    --customer-radius: 5px;
    --customer-card-radius: 8px;
    --customer-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);

    width: 100%;
    min-width: 0;
    color: var(--customer-text);
    font-size: 13px;
}

#createCustomerRoot *,
#createCustomerRoot *::before,
#createCustomerRoot *::after {
    box-sizing: border-box;
}

/* =========================================================
   MAIN CARD
========================================================= */

#createCustomerRoot .customer-main-card {
    background: var(--customer-card);
    border: 1px solid var(--customer-border);
    border-radius: var(--customer-card-radius) !important;
    box-shadow: var(--customer-shadow);
    min-width: 0;
}

#createCustomerRoot .card-header-custom {
    background: #ffffff;
    border-bottom: 1px solid var(--customer-border);
    padding: 17px 22px;
}

#createCustomerRoot .card-body {
    min-width: 0;
}

/* =========================================================
   PAGE HEADER
========================================================= */

#createCustomerRoot .customer-create-header h5 {
    color: var(--customer-heading);
    font-size: 18px;
    font-weight: 700;
    line-height: 1.4;
    letter-spacing: -0.3px;
}

#createCustomerRoot .customer-create-header h5 i {
    font-size: 17px;
}

#createCustomerRoot .header-back-btn {
    min-height: 35px;
    white-space: nowrap;
}

/* =========================================================
   INFORMATION NOTICE
========================================================= */

#createCustomerRoot .customer-info-alert {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 12px 14px !important;
    background: #f3f8ff !important;
    border: 1px solid #dceaff !important;
    border-left: 3px solid var(--customer-primary) !important;
    border-radius: var(--customer-radius) !important;
    color: var(--customer-text);
    font-size: 12px;
    line-height: 1.7;
}

#createCustomerRoot .customer-info-alert i {
    color: var(--customer-primary);
    font-size: 15px;
    margin-top: 2px;
    flex-shrink: 0;
}

#createCustomerRoot .customer-info-alert strong {
    color: var(--customer-heading);
    font-weight: 700;
}

/* =========================================================
   FORM SECTIONS
========================================================= */

#createCustomerRoot .customer-form-section {
    background: #ffffff;
    border: 1px solid var(--customer-border);
    border-radius: var(--customer-card-radius) !important;
    padding: 20px;
    margin-bottom: 17px;
    min-width: 0;
}

#createCustomerRoot .customer-section-heading {
    display: flex;
    align-items: center;
    gap: 9px;
    color: var(--customer-heading);
    font-size: 13px;
    font-weight: 700;
    line-height: 1.5;
    margin-bottom: 17px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--customer-border);
}

#createCustomerRoot .customer-section-heading i {
    width: 18px;
    text-align: center;
    color: var(--customer-primary);
    font-size: 14px;
}

/* =========================================================
   FORM LABELS
========================================================= */

#createCustomerRoot .form-label {
    display: inline-block;
    color: #495057;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.5;
    margin-bottom: 7px;
}

#createCustomerRoot .form-label .text-danger {
    font-weight: 700;
}

#createCustomerRoot .form-label .text-muted {
    font-size: 11px;
    font-weight: 400 !important;
}

/* =========================================================
   INPUT CONTROLS
========================================================= */

#createCustomerRoot .form-control,
#createCustomerRoot .form-select {
    display: block;
    width: 100%;
    min-width: 0;
    min-height: 39px;
    padding: 8px 12px;
    border: 1px solid #e0e3e8;
    border-radius: var(--customer-radius) !important;
    background-color: #ffffff;
    color: var(--customer-heading);
    font-family: inherit;
    font-size: 13px;
    font-weight: 400;
    line-height: 1.5;
    box-shadow: none;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

#createCustomerRoot .form-control::placeholder {
    color: #adb5bd;
    opacity: 1;
    font-size: 12px;
}

#createCustomerRoot .form-control:focus,
#createCustomerRoot .form-select:focus {
    border-color: var(--customer-primary);
    background-color: #ffffff;
    box-shadow: 0 0 0 3px rgba(21, 114, 232, 0.10);
    outline: none;
}

#createCustomerRoot textarea.form-control {
    min-height: 85px;
    resize: vertical;
}

#createCustomerRoot input[type="file"].form-control {
    min-height: 39px;
    padding: 5px 8px;
    font-size: 11.5px;
}

#createCustomerRoot input[type="file"]::file-selector-button {
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

#createCustomerRoot .font-monospace {
    font-size: 12px;
    font-weight: 600;
}

/* =========================================================
   SUBMIT AREA
========================================================= */

#createCustomerRoot .customer-submit-area {
    border-top: 1px solid var(--customer-border);
    padding-top: 18px;
    margin-top: 4px;
}

#createCustomerRoot .btn-submit-cust {
    min-height: 40px;
    padding: 9px 25px;
    background: var(--customer-primary) !important;
    border: 1px solid var(--customer-primary) !important;
    border-radius: var(--customer-radius) !important;
    color: #ffffff !important;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.1px;
    line-height: 1.5;
    transition: background-color 0.2s ease,
                box-shadow 0.2s ease,
                transform 0.2s ease;
}

#createCustomerRoot .btn-submit-cust:hover {
    background: #1263cb !important;
    border-color: #1263cb !important;
    box-shadow: 0 3px 9px rgba(21, 114, 232, 0.18);
}

#createCustomerRoot .btn-submit-cust:focus-visible,
#createCustomerRoot .header-back-btn:focus-visible {
    outline: 2px solid var(--customer-primary);
    outline-offset: 3px;
}

/* =========================================================
   GENERAL ELEMENTS
========================================================= */

#createCustomerRoot .btn,
#createCustomerRoot .btn-sm,
#createCustomerRoot button,
#createCustomerRoot .alert {
    border-radius: var(--customer-radius) !important;
}

#createCustomerRoot .text-dark {
    color: var(--customer-heading) !important;
}

#createCustomerRoot .border-top {
    border-color: var(--customer-border) !important;
}

#createCustomerRoot .text-muted {
    color: var(--customer-muted) !important;
}

/* =========================================================
   TABLET RESPONSIVE
========================================================= */

@media (max-width: 991.98px) {

    #createCustomerRoot .card-header-custom {
        padding: 16px 18px;
    }

    #createCustomerRoot .customer-form-section {
        padding: 17px;
    }

}

/* =========================================================
   MOBILE RESPONSIVE
========================================================= */

@media (max-width: 767.98px) {

    #createCustomerRoot {
        font-size: 12px;
    }

    #createCustomerRoot .card-header-custom {
        padding: 14px 15px;
    }

    #createCustomerRoot .customer-create-header h5 {
        font-size: 16px;
        margin-bottom: 0 !important;
    }

    #createCustomerRoot .header-back-btn {
        width: 100%;
        min-height: 39px;
        justify-content: center;
    }

    #createCustomerRoot .card-body {
        padding: 14px !important;
    }

    #createCustomerRoot .customer-info-alert {
        padding: 11px !important;
    }

    #createCustomerRoot .customer-form-section {
        padding: 14px;
        margin-bottom: 13px;
    }

    #createCustomerRoot .customer-section-heading {
        margin-bottom: 14px;
        padding-bottom: 10px;
    }

    #createCustomerRoot .form-label {
        font-size: 11.5px;
    }

    #createCustomerRoot .form-control,
    #createCustomerRoot .form-select {
        min-height: 41px;
        font-size: 13px;
    }

    #createCustomerRoot textarea.form-control {
        min-height: 85px;
    }

    #createCustomerRoot .customer-submit-area {
        padding-top: 14px;
    }

    #createCustomerRoot .btn-submit-cust {
        width: 100%;
        min-height: 42px;
        justify-content: center;
        text-align: center;
    }

}

/* =========================================================
   SMALL MOBILE
========================================================= */

@media (max-width: 374.98px) {

    #createCustomerRoot {
        padding-left: 7px !important;
        padding-right: 7px !important;
    }

    #createCustomerRoot .card-header-custom {
        padding: 12px;
    }

    #createCustomerRoot .card-body {
        padding: 11px !important;
    }

    #createCustomerRoot .customer-form-section {
        padding: 12px;
    }

    #createCustomerRoot .customer-create-header h5 {
        font-size: 15px;
    }

}

/* =========================================================
   REDUCED MOTION
========================================================= */

@media (prefers-reduced-motion: reduce) {

    #createCustomerRoot *,
    #createCustomerRoot *::before,
    #createCustomerRoot *::after {
        transition-duration: 0.01ms !important;
        animation-duration: 0.01ms !important;
    }

}
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="createCustomerRoot">

    <div class="card border-0 customer-main-card overflow-hidden">

        <!-- Page Header -->
        <div class="card-header-custom d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 customer-create-header">

            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center">

                <i class="fas fa-user text-primary me-2"></i>

                <span>Add New Customer</span>

            </h5>

            <a href="{{ route('admin.roles.users') }}"
               class="btn btn-outline-secondary btn-sm px-3 py-2 fw-semibold d-inline-flex align-items-center shadow-xs header-back-btn">

                <i class="fas fa-arrow-left me-2"></i>

                <span>Back</span>

            </a>

        </div>

        <!-- Form Body -->
        <div class="card-body p-3 p-md-4">

            <form action="{{ route('admin.roles.users.store_customer') }}" method="POST" enctype="multipart/form-data">

                @csrf

                <!-- Required Information Notice -->
                <div class="alert alert-light customer-info-alert mb-3 shadow-xs">

                    <i class="fas fa-circle-info"></i>

                    <span>
                        <strong>Name, Password and Email or Phone</strong> are required.
                    </span>

                </div>

                <!-- Primary Details Section -->
                <div class="customer-form-section">

                    <div class="customer-section-heading">
                        <i class="fas fa-user-circle"></i>
                        <span>Account & Contact Information</span>
                    </div>

                    <div class="row g-3 mb-3">

                        <!-- Customer Name -->
                        <div class="col-12 col-md-6">

                            <label class="form-label">
                                Customer Name <span class="text-danger">*</span>
                            </label>

                            <input type="text" name="name" class="form-control fw-semibold" placeholder="Customer Name" required>

                        </div>

                        <!-- Initial Password -->
                        <div class="col-12 col-md-6">

                            <label class="form-label">
                                Initial Password <span class="text-danger">*</span>
                            </label>

                            <input type="password" name="password" class="form-control" placeholder="Initial access password" required>

                        </div>

                    </div>

                    <div class="row g-3">

                        <!-- Phone Number -->
                        <div class="col-12 col-md-6">

                            <label class="form-label">
                                Phone Number
                                <span class="text-muted fw-normal">(Optional if Email provided)</span>
                            </label>

                            <input type="text" name="phone" class="form-control font-monospace bn-digit" placeholder="01XXXXXXXXX">

                        </div>

                        <!-- Email Address -->
                        <div class="col-12 col-md-6">

                            <label class="form-label">
                                Email Address
                                <span class="text-muted fw-normal">(Optional if Phone provided)</span>
                            </label>

                            <input type="email" name="email" class="form-control font-monospace" placeholder="customer@domain.com">

                        </div>

                    </div>

                </div>

                <!-- Optional Details Section -->
                <div class="customer-form-section mb-4">

                    <div class="customer-section-heading">
                        <i class="fas fa-address-card"></i>
                        <span>Additional Information</span>
                    </div>

                    <div class="row g-3 mb-3">

                        <!-- Customer Address -->
                        <div class="col-12 col-md-6">

                            <label class="form-label text-muted">
                                Customer Address (Optional)
                            </label>

                            <input type="text" name="address" class="form-control" placeholder="House, Road, City...">

                        </div>

                        <!-- Customer Photo -->
                        <div class="col-12 col-md-6">

                            <label class="form-label text-muted">
                                Photo (Optional)
                            </label>

                            <input type="file" name="photo" class="form-control" accept="image/*">

                        </div>

                    </div>

                    <!-- Admin Note -->
                    <div>

                        <label class="form-label text-muted">
                            Admin Note (Optional)
                        </label>

                        <textarea name="note" rows="2" class="form-control" placeholder="Any special notes or tags for this customer..."></textarea>

                    </div>

                </div>

                <!-- Submit Action -->
                <div class="customer-submit-area text-end">

                    <button type="submit" class="btn btn-dark fw-bold text-white shadow-xs btn-submit-cust">

                        <i class="fas fa-user-plus me-2"></i>

                        Save Customer

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection
