@extends('layouts.admin')

@section('title', 'Add Customer - ' . config('app.name'))

@section('admin_content')
<style>
    /* Global Strict 5px Radius for Buttons, Inputs & Alerts */
    #createCustomerRoot .btn,
    #createCustomerRoot .btn-sm,
    #createCustomerRoot button,
    #createCustomerRoot .form-control,
    #createCustomerRoot .alert {
        border-radius: 5px !important;
    }

    /* Enterprise Card Surface */
    #createCustomerRoot .customer-main-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }

    #createCustomerRoot .card-header-custom {
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
        padding: 16px 22px;
    }

    /* Form Typography & Input Controls */
    #createCustomerRoot .form-label {
        font-size: 11.5px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 5px;
        letter-spacing: 0.01em;
    }

    #createCustomerRoot .form-control {
        font-size: 12.5px;
        border: 1px solid #cbd5e1;
        padding: 7px 12px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.02);
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    #createCustomerRoot .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    #createCustomerRoot .form-control::placeholder {
        color: #94a3b8;
    }

    /* Form Grouping Box */
    .customer-form-section {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px !important;
        padding: 18px 20px;
        margin-bottom: 18px;
    }

    /* Corporate Submit Button */
    .btn-submit-cust {
        background-color: #0f172a !important;
        border-color: #0f172a !important;
        color: #ffffff !important;
        font-size: 13px;
        padding: 9px 28px;
        transition: background-color 0.15s ease, box-shadow 0.15s ease;
    }
    .btn-submit-cust:hover {
        background-color: #1e293b !important;
        border-color: #1e293b !important;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
    }

    /* Safe Bengali Digits */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Mobile Responsive Optimizations */
    @media (max-width: 767.98px) {
        #createCustomerRoot .card-header-custom {
            padding: 14px 16px;
        }
        .customer-create-header h5 {
            font-size: 16px !important;
        }
        .customer-form-section {
            padding: 14px !important;
            margin-bottom: 14px !important;
        }
        .header-back-btn,
        .btn-submit-cust {
            width: 100% !important;
            justify-content: center;
            text-align: center;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="createCustomerRoot">
    <div class="card border-0 customer-main-card overflow-hidden">
        <!-- Header -->
        <div class="card-header-custom d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2.5 customer-create-header">
            <h5 class="fw-bold text-dark mb-2 d-flex align-items-center" style="font-size: 17px;">
                <i class="fas fa-user text-primary me-2"></i>
                <span>Add New Customer</span>
            </h5>
            <a href="{{ route('admin.roles.users') }}" class="btn btn-outline-secondary btn-sm px-3 py-1.5 fw-semibold d-inline-flex align-items-center shadow-xs header-back-btn">
                <i class="fas fa-arrow-left me-2"></i>
                <span>Back</span>
            </a>
        </div>

        <!-- Body -->
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('admin.roles.users.store_customer') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <!-- Info Notice -->
                <div class="alert alert-light border small text-muted mb-3 py-2 px-3 shadow-xs d-flex align-items-center" style="font-size: 12px; background-color: #f8fafc;">
                    <i class="fas fa-info-circle me-2 text-primary flex-shrink-0"></i>
                    <span><strong class="text-dark">Name, Password and Email or Phone</strong> are required.</span>
                </div>

                <!-- Primary Details Section -->
                <div class="customer-form-section">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control fw-semibold" placeholder="Customer Name" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Initial Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" placeholder="Initial access password" required>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Phone Number <span class="text-muted fw-normal">(Optional if Email provided)</span></label>
                            <input type="text" name="phone" class="form-control font-monospace bn-digit" placeholder="01XXXXXXXXX">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Email Address <span class="text-muted fw-normal">(Optional if Phone provided)</span></label>
                            <input type="email" name="email" class="form-control font-monospace" placeholder="customer@domain.com">
                        </div>
                    </div>
                </div>

                <!-- Optional Details Section -->
                <div class="customer-form-section mb-4">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label text-muted">Customer Address (Optional)</label>
                            <input type="text" name="address" class="form-control" placeholder="House, Road, City...">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-muted">Photo (Optional)</label>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                        </div>
                    </div>

                    <div>
                        <label class="form-label text-muted">Admin Note (Optional)</label>
                        <textarea name="note" rows="2" class="form-control" placeholder="Any special notes or tags for this customer..."></textarea>
                    </div>
                </div>

                <!-- Submit Action -->
                <div class="text-end pt-3 border-top">
                    <button type="submit" class="btn btn-dark fw-bold text-white shadow-xs btn-submit-cust">
                        Save Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection