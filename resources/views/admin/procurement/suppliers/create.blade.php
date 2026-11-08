
@extends('layouts.admin')

@section('title', 'Add New Supplier')

@section('admin_content')
<style>
    /* ==========================================
       SUPPLIER CREATE — KAIADMIN CLASSIC THEME
    ========================================== */

    .supplier-create-root {
        --supplier-primary: var(--bs-primary, #1572e8);
        --supplier-primary-soft: rgba(21, 114, 232, .08);
        --supplier-success: var(--bs-success, #31ce36);
        --supplier-danger: var(--bs-danger, #f25961);
        --supplier-warning: var(--bs-warning, #ffad46);
        --supplier-text: #2a2f5b;
        --supplier-muted: #8d9498;
        --supplier-border: #ebedf2;
        --supplier-surface: #ffffff;
        --supplier-background: #f9fbfd;
        color: var(--supplier-text);
        font-size: 13px;
        min-width: 0;
        width: 100%;
    }

    .supplier-create-root *,
    .supplier-create-root *::before,
    .supplier-create-root *::after {
        box-sizing: border-box;
    }

    /* Page Header */

    .supplier-create-root .supplier-page-header {
        gap: 12px;
        margin-bottom: 18px;
    }

    .supplier-create-root .supplier-page-title {
        font-size: clamp(17px, 2vw, 21px);
        font-weight: 700;
        color: var(--supplier-text);
        letter-spacing: -.35px;
        line-height: 1.35;
    }

    .supplier-create-root .supplier-page-title i {
        color: var(--supplier-primary);
        font-size: 19px;
    }

    .supplier-create-root .supplier-page-subtitle {
        color: var(--supplier-muted);
        font-size: 12px;
        margin-top: 4px;
    }

    /* Cards */

    .supplier-create-root .supplier-section-card {
        background: var(--supplier-surface);
        border: 1px solid var(--supplier-border) !important;
        border-radius: 5px !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .035) !important;
        overflow: visible !important;
        transition: box-shadow .2s ease;
    }

    .supplier-create-root .supplier-section-card .card-header {
        background: #fff !important;
        border-bottom: 1px solid var(--supplier-border) !important;
        padding: 13px 17px !important;
        border-radius: 5px 5px 0 0 !important;
    }

    .supplier-create-root .supplier-section-card .card-body {
        padding: 18px !important;
        overflow: visible !important;
    }

    .supplier-create-root .supplier-section-title {
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--supplier-text);
        font-size: 13px;
        font-weight: 700;
        line-height: 1.5;
        margin: 0;
    }

    .supplier-create-root .supplier-section-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        flex: 0 0 30px;
        border-radius: 5px;
        background: var(--supplier-primary-soft);
        color: var(--supplier-primary);
        font-size: 13px;
    }

    .supplier-create-root .supplier-section-description {
        color: var(--supplier-muted);
        font-size: 11.5px;
        font-weight: 400;
        margin: 2px 0 0 40px;
        line-height: 1.5;
    }

    /* Form Controls */

    .supplier-create-root .form-label {
        display: block;
        color: #495057;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.45;
        margin-bottom: 6px !important;
    }

    .supplier-create-root .form-control,
    .supplier-create-root .form-select,
    .supplier-create-root .input-group-text {
        min-height: 36px;
        border: 1px solid #e6e9ed;
        border-radius: 5px !important;
        background-color: #fff;
        color: #495057;
        font-size: 12.5px;
        padding: 7px 10px;
        transition: border-color .2s ease, box-shadow .2s ease;
        max-width: 100%;
    }

    .supplier-create-root .form-control::placeholder {
        color: #adb5bd;
        opacity: 1;
        font-size: 12px;
    }

    .supplier-create-root .form-control:focus,
    .supplier-create-root .form-select:focus {
        border-color: var(--supplier-primary) !important;
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .11) !important;
        outline: 0;
    }

    .supplier-create-root .form-control:disabled,
    .supplier-create-root .form-select:disabled {
        background-color: #f5f6f8;
    }

    .supplier-create-root textarea.form-control {
        min-height: 68px;
        resize: vertical;
        line-height: 1.5;
    }

    .supplier-create-root input[type="file"].form-control {
        padding: 5px;
        min-height: 36px;
        font-size: 11.5px;
    }

    .supplier-create-root input[type="file"]::file-selector-button {
        border: 0;
        border-radius: 4px;
        background: #edf3fc;
        color: var(--supplier-primary);
        font-weight: 600;
        font-size: 11px;
        padding: 6px 9px;
        margin-right: 8px;
    }

    .supplier-create-root .font-monospace {
        font-size: 12px !important;
        letter-spacing: .1px;
    }

    .supplier-create-root .form-control.text-end {
        font-weight: 600;
    }

    .supplier-create-root .form-label .text-danger {
        font-size: 12px;
    }

    /* Field Grid */

    .supplier-create-root .row {
        --bs-gutter-x: 16px;
        --bs-gutter-y: 16px;
    }

    .supplier-create-root .form-label,
    .supplier-create-root .form-control,
    .supplier-create-root .form-select {
        overflow-wrap: anywhere;
    }

    /* Buttons */

    .supplier-create-root .btn {
        min-height: 35px;
        border-radius: 5px !important;
        font-size: 12px;
        font-weight: 600;
        padding: 7px 13px;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        gap: 4px;
        transition: all .2s ease;
        white-space: normal;
    }

    .supplier-create-root .btn-primary {
        background-color: var(--supplier-primary);
        border-color: var(--supplier-primary);
    }

    .supplier-create-root .btn-primary:hover {
        filter: brightness(.94);
    }

    .supplier-create-root .btn-outline-secondary {
        border-color: #e1e5eb;
        color: #68717b;
        background: #fff;
    }

    .supplier-create-root .btn-outline-secondary:hover {
        background: #f5f7fa;
        border-color: #cbd2dc;
        color: var(--supplier-text);
    }

    .supplier-create-root .btn-secondary {
        background: #6c757d;
        border-color: #6c757d;
    }

    .supplier-create-root .btn:disabled {
        opacity: .7;
        cursor: not-allowed;
    }

    /* Alerts */

    .supplier-create-root .alert {
        border-radius: 5px !important;
        font-size: 12.5px;
        padding: 12px 15px;
        overflow-wrap: anywhere;
    }

    .supplier-create-root .alert ul {
        line-height: 1.7;
    }

    /* Opening Balance Preview */

    .supplier-create-root .balance-impact-panel {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 14px !important;
        background: #f7f9fc !important;
        border: 1px solid var(--supplier-border) !important;
        border-radius: 5px !important;
        min-height: 48px;
    }

    .supplier-create-root .balance-impact-main {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
        flex-wrap: wrap;
        color: #68717b;
        font-size: 12px;
    }

    .supplier-create-root .balance-impact-main > i {
        color: var(--supplier-primary);
    }

    .supplier-create-root #balanceTypeSummary {
        overflow-wrap: anywhere;
    }

    .supplier-create-root .balance-impact-note {
        color: var(--supplier-muted);
        font-size: 11px;
        text-align: right;
    }

    /* Footer */

    .supplier-create-root .supplier-card-footer {
        background: #fff !important;
        border-top: 1px solid var(--supplier-border) !important;
        padding: 13px 18px !important;
        border-radius: 0 0 5px 5px !important;
    }

    /* Loader */

    #supplier-global-loader {
        position: fixed;
        inset: 0;
        background: rgba(25, 35, 55, .78);
        z-index: 99999999;
        display: none;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        color: #fff;
        backdrop-filter: blur(3px);
        padding: 20px;
        text-align: center;
    }

    #supplier-global-loader .spinner-border {
        width: 3rem;
        height: 3rem;
    }

    #supplier-global-loader h5 {
        font-size: 16px;
    }

    #supplier-global-loader p {
        max-width: 360px;
        line-height: 1.6;
    }

    /* Responsive */

    @media (min-width: 1400px) {
        .supplier-create-root .supplier-section-card .card-body {
            padding: 20px !important;
        }

        .supplier-create-root .row {
            --bs-gutter-x: 18px;
            --bs-gutter-y: 17px;
        }
    }

    @media (max-width: 991.98px) {
        .supplier-create-root .supplier-section-card .card-body {
            padding: 16px !important;
        }

        .supplier-create-root .row {
            --bs-gutter-x: 14px;
            --bs-gutter-y: 14px;
        }
    }

    @media (max-width: 767.98px) {
        .supplier-create-root {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        .supplier-create-root .supplier-page-header {
            align-items: flex-start !important;
        }

        .supplier-create-root .supplier-page-header .btn {
            min-height: 36px;
        }

        .supplier-create-root .supplier-section-card .card-header {
            padding: 12px !important;
        }

        .supplier-create-root .supplier-section-card .card-body {
            padding: 14px !important;
        }

        .supplier-create-root .supplier-section-title {
            font-size: 12.5px;
        }

        .supplier-create-root .row {
            --bs-gutter-x: 12px;
            --bs-gutter-y: 13px;
        }

        .supplier-create-root .balance-impact-panel {
            align-items: flex-start;
            flex-direction: column;
        }

        .supplier-create-root .balance-impact-note {
            text-align: left;
            padding-left: 22px;
        }

        .supplier-create-root .supplier-card-footer {
            padding: 12px !important;
        }
    }

    @media (max-width: 575.98px) {
        .supplier-create-root .supplier-page-header {
            flex-direction: column;
            align-items: stretch !important;
        }

        .supplier-create-root .supplier-page-header > div:last-child {
            width: 100%;
        }

        .supplier-create-root .supplier-page-header > div:last-child .btn {
            width: 100%;
        }

        .supplier-create-root .supplier-section-card .card-body {
            padding: 12px !important;
        }

        .supplier-create-root .row {
            --bs-gutter-x: 10px;
            --bs-gutter-y: 12px;
        }

        .supplier-create-root .form-label {
            font-size: 11.5px;
        }

        .supplier-create-root .form-control,
        .supplier-create-root .form-select {
            font-size: 12px;
        }

        .supplier-create-root .supplier-card-footer {
            flex-direction: column-reverse;
        }

        .supplier-create-root .supplier-card-footer .btn,
        .supplier-create-root .supplier-card-footer a {
            width: 100%;
            min-height: 38px;
        }

        .supplier-create-root .supplier-section-icon {
            width: 28px;
            height: 28px;
            flex-basis: 28px;
        }

        .supplier-create-root .supplier-section-description {
            margin-left: 38px;
        }
    }

    @media (max-width: 359.98px) {
        .supplier-create-root {
            padding-left: 8px !important;
            padding-right: 8px !important;
        }

        .supplier-create-root .supplier-section-card .card-body {
            padding: 10px !important;
        }

        .supplier-create-root .supplier-section-card .card-header {
            padding: 10px !important;
        }

        .supplier-create-root .supplier-section-title {
            font-size: 11.5px;
        }

        .supplier-create-root .row {
            --bs-gutter-x: 8px;
        }
    }
</style>

{{-- Processing Overlay --}}
<div id="supplier-global-loader">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <h5 class="fw-bold text-white mt-3 mb-1">Registering Supplier...</h5>
    <p class="text-white-50 small mb-0">Please wait. Do not reload the page or submit again while the supplier profile and ledger are being created.</p>
</div>

<div class="container-fluid px-3 py-3 supplier-create-root">

    {{-- Page Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between supplier-page-header">
        <div>
            <h5 class="supplier-page-title mb-0">
                <i class="fas fa-truck-field me-2"></i>Add New Supplier
            </h5>
            <div class="supplier-page-subtitle">Create a supplier profile and configure business details.</div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.suppliers.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Supplier List
            </a>
        </div>
    </div>

    {{-- Error Banner --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3">
            <div class="d-flex align-items-center gap-2 fw-semibold">
                <i class="fas fa-circle-exclamation fa-lg"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3">
            <div class="d-flex align-items-center gap-2 fw-semibold mb-1">
                <i class="fas fa-circle-exclamation"></i>
                Please review the following errors:
            </div>
            <ul class="mb-0 ps-3 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('admin.suppliers.store') }}" method="POST" enctype="multipart/form-data" id="supplierForm">
        @csrf

        {{-- Section 1: Business Identity & Contact --}}
        <div class="card supplier-section-card mb-3">
            <div class="card-header">
                <h6 class="supplier-section-title">
                    <span class="supplier-section-icon">
                        <i class="fas fa-id-card"></i>
                    </span>
                    Business Identity &amp; Contact
                </h6>
                <p class="supplier-section-description">Supplier information and primary contact details.</p>
            </div>

            <div class="card-body">
                <div class="row">

                    {{-- Supplier Name --}}
                    <div class="col-12 col-md-4">
                        <label class="form-label">Supplier Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm" placeholder="Apex Spinning Mills Ltd." value="{{ old('name') }}" required>
                    </div>

                    {{-- Company Name --}}
                    <div class="col-12 col-md-4">
                        <label class="form-label">Registered Company Name</label>
                        <input type="text" name="company_name" class="form-control form-control-sm" placeholder="Official company name" value="{{ old('company_name') }}">
                    </div>

                    {{-- Contact Person --}}
                    <div class="col-6 col-md-4">
                        <label class="form-label">Contact Person</label>
                        <input type="text" name="contact_person" class="form-control form-control-sm" placeholder="Contact person's name" value="{{ old('contact_person') }}">
                    </div>

                    {{-- Primary Phone --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label">Primary Phone <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control form-control-sm font-monospace" placeholder="+8801700000000" value="{{ old('phone') }}" required>
                    </div>

                    {{-- Alternate Phone --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label">Alternate Phone / WhatsApp</label>
                        <input type="text" name="alt_phone" class="form-control form-control-sm font-monospace" placeholder="+8801800000000" value="{{ old('alt_phone') }}">
                    </div>

                    {{-- Email --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control form-control-sm" placeholder="billing@vendor.com" value="{{ old('email') }}">
                    </div>

                    {{-- Status --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label">Supplier Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select form-select-sm" required>
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive / Blocked</option>
                        </select>
                    </div>

                </div>
            </div>
        </div>

        {{-- Section 2: Compliance & Tax --}}
        <div class="card supplier-section-card mb-3">
            <div class="card-header">
                <h6 class="supplier-section-title">
                    <span class="supplier-section-icon">
                        <i class="fas fa-certificate"></i>
                    </span>
                    Compliance &amp; Tax Information
                </h6>
                <p class="supplier-section-description">Registration numbers and supplier classification.</p>
            </div>

            <div class="card-body">
                <div class="row">

                    {{-- BIN / VAT --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label">BIN / VAT Number</label>
                        <input type="text" name="bin_number" class="form-control form-control-sm font-monospace" placeholder="002394819-0101" value="{{ old('bin_number') }}">
                    </div>

                    {{-- e-TIN --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label">e-TIN Number</label>
                        <input type="text" name="tax_number" class="form-control form-control-sm font-monospace" placeholder="e-TIN number" value="{{ old('tax_number') }}">
                    </div>

                    {{-- Trade License --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label">Trade License Number</label>
                        <input type="text" name="trade_license_no" class="form-control form-control-sm font-monospace" placeholder="License number" value="{{ old('trade_license_no') }}">
                    </div>

                    {{-- Category --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label">Supplier Category</label>
                        <select name="category_id" class="form-select form-select-sm">
                            <option value="">General Supplier</option>
                            @foreach($categories ?? [] as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name ?? ($category->title ?? 'Category #' . $category->id) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>
            </div>
        </div>

        {{-- Section 3: Commercial Terms & Opening Balance --}}
        <div class="card supplier-section-card mb-3">
            <div class="card-header">
                <h6 class="supplier-section-title">
                    <span class="supplier-section-icon">
                        <i class="fas fa-scale-balanced"></i>
                    </span>
                    Payment Terms &amp; Opening Balance
                </h6>
                <p class="supplier-section-description">Configure credit terms and initial ledger position.</p>
            </div>

            <div class="card-body">
                <div class="row">

                    {{-- Payment Terms --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label">Payment Terms <span class="text-danger">*</span></label>
                        <select name="payment_terms" id="payment_terms" class="form-select form-select-sm" required>
                            <option value="net_30" {{ old('payment_terms', 'net_30') === 'net_30' ? 'selected' : '' }}>Net 30 Days</option>
                            <option value="net_15" {{ old('payment_terms') === 'net_15' ? 'selected' : '' }}>Net 15 Days</option>
                            <option value="net_60" {{ old('payment_terms') === 'net_60' ? 'selected' : '' }}>Net 60 Days</option>
                            <option value="cash_on_delivery" {{ old('payment_terms') === 'cash_on_delivery' ? 'selected' : '' }}>Cash on Delivery (COD)</option>
                            <option value="advance_payment" {{ old('payment_terms') === 'advance_payment' ? 'selected' : '' }}>100% Advance</option>
                        </select>
                    </div>

                    {{-- Credit Limit --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label">Credit Limit (৳)</label>
                        <input type="number" step="0.01" name="credit_limit" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" value="{{ old('credit_limit', 0) }}" min="0">
                        <small class="text-muted d-block mt-1" style="font-size: 11px;">Maximum permitted outstanding balance.</small>
                    </div>

                    {{-- Opening Balance --}}
                    <div class="col-6 col-md-2">
                        <label class="form-label">Opening Balance (৳)</label>
                        <input type="number" step="0.01" name="opening_balance" id="opening_balance" class="form-control form-control-sm text-end fw-bold font-monospace" placeholder="0.00" value="{{ old('opening_balance', 0) }}" min="0">
                    </div>

                    {{-- Balance Type --}}
                    <div class="col-6 col-md-2">
                        <label class="form-label">Balance Type</label>
                        <select name="opening_balance_type" id="opening_balance_type" class="form-select form-select-sm fw-semibold">
                            <option value="payable" {{ old('opening_balance_type', 'payable') === 'payable' ? 'selected' : '' }}>Payable</option>
                            <option value="advance" {{ old('opening_balance_type') === 'advance' ? 'selected' : '' }}>Advance</option>
                        </select>
                    </div>

                    {{-- Balance Date --}}
                    <div class="col-6 col-md-2">
                        <label class="form-label">Balance Date</label>
                        <input type="date" name="opening_balance_date" class="form-control form-control-sm" value="{{ old('opening_balance_date', date('Y-m-d')) }}">
                    </div>

                    {{-- Ledger Preview --}}
                    <div class="col-12">
                        <div class="balance-impact-panel" id="balanceImpactNotice">
                            <div class="balance-impact-main">
                                <i class="fas fa-circle-info"></i>
                                <span>Initial Ledger Position:</span>
                                <strong id="balanceTypeSummary" class="text-dark">Nil / Zero Balance</strong>
                            </div>
                            <span class="balance-impact-note">Automatically posted to the Accounts Payable ledger.</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- Section 4: Banking & Settlement --}}
        <div class="card supplier-section-card mb-3">
            <div class="card-header">
                <h6 class="supplier-section-title">
                    <span class="supplier-section-icon">
                        <i class="fas fa-building-columns"></i>
                    </span>
                    Banking &amp; Settlement Details
                </h6>
                <p class="supplier-section-description">Bank account, electronic transfers and mobile financial services.</p>
            </div>

            <div class="card-body">
                <div class="row">

                    {{-- Bank Name --}}
                    <div class="col-12 col-md-3">
                        <label class="form-label">Bank Name</label>
                        <input type="text" name="bank_name" class="form-control form-control-sm" placeholder="Dutch-Bangla Bank" value="{{ old('bank_name') }}">
                    </div>

                    {{-- Branch --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label">Branch Name</label>
                        <input type="text" name="bank_branch" class="form-control form-control-sm" placeholder="Branch name" value="{{ old('bank_branch') }}">
                    </div>

                    {{-- Account Name --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label">Account Holder Name</label>
                        <input type="text" name="bank_account_name" class="form-control form-control-sm" placeholder="Beneficiary name" value="{{ old('bank_account_name') }}">
                    </div>

                    {{-- Account Number --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label">Account Number</label>
                        <input type="text" name="bank_account_no" class="form-control form-control-sm font-monospace fw-semibold" placeholder="Bank account number" value="{{ old('bank_account_no') }}">
                    </div>

                    {{-- Routing Number --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label">Routing Number</label>
                        <input type="text" name="bank_routing_no" class="form-control form-control-sm font-monospace" placeholder="9-digit routing code" value="{{ old('bank_routing_no') }}">
                    </div>

                    {{-- SWIFT --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label">SWIFT / BIC Code</label>
                        <input type="text" name="swift_code" class="form-control form-control-sm font-monospace" placeholder="DBBLBDDH" value="{{ old('swift_code') }}">
                    </div>

                    {{-- MFS --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label">bKash / Nagad Number</label>
                        <input type="text" name="mfs_number" class="form-control form-control-sm font-monospace" placeholder="Merchant / Personal number" value="{{ old('mfs_number') }}">
                    </div>

                    {{-- Currency --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label">Settlement Currency</label>
                        <select name="currency" class="form-select form-select-sm">
                            <option value="BDT" {{ old('currency', 'BDT') === 'BDT' ? 'selected' : '' }}>BDT (৳ - Bangladeshi Taka)</option>
                            <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD ($ - US Dollar)</option>
                            <option value="EUR" {{ old('currency') === 'EUR' ? 'selected' : '' }}>EUR (€ - Euro)</option>
                            <option value="CNY" {{ old('currency') === 'CNY' ? 'selected' : '' }}>CNY (¥ - Chinese Yuan)</option>
                        </select>
                    </div>

                </div>
            </div>
        </div>

        {{-- Section 5: Address & Documents --}}
        <div class="card supplier-section-card mb-3">
            <div class="card-header">
                <h6 class="supplier-section-title">
                    <span class="supplier-section-icon">
                        <i class="fas fa-location-dot"></i>
                    </span>
                    Address, Documents &amp; Notes
                </h6>
                <p class="supplier-section-description">Business location, verification documents and internal remarks.</p>
            </div>

            <div class="card-body">
                <div class="row">

                    {{-- Address --}}
                    <div class="col-12 col-md-6">
                        <label class="form-label">Business Address <span class="text-danger">*</span></label>
                        <textarea name="address" rows="2" class="form-control form-control-sm" placeholder="Street, building, holding number or industrial area..." required>{{ old('address') }}</textarea>
                    </div>

                    {{-- City --}}
                    <div class="col-6 col-md-2">
                        <label class="form-label">City / District <span class="text-danger">*</span></label>
                        <input type="text" name="city" class="form-control form-control-sm" placeholder="Dhaka" value="{{ old('city', 'Dhaka') }}" required>
                    </div>

                    {{-- Postal Code --}}
                    <div class="col-6 col-md-2">
                        <label class="form-label">Postal Code</label>
                        <input type="text" name="postal_code" class="form-control form-control-sm font-monospace" placeholder="1212" value="{{ old('postal_code') }}">
                    </div>

                    {{-- Country --}}
                    <div class="col-12 col-md-2">
                        <label class="form-label">Country <span class="text-danger">*</span></label>
                        <input type="text" name="country" class="form-control form-control-sm" value="{{ old('country', 'Bangladesh') }}" required>
                    </div>

                    {{-- KYC Document --}}
                    <div class="col-12 col-md-4">
                        <label class="form-label">KYC Document</label>
                        <input type="file" name="kyc_document" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                    </div>

                    {{-- Notes --}}
                    <div class="col-12 col-md-8">
                        <label class="form-label">Internal Remarks / Delivery Notes</label>
                        <input type="text" name="notes" class="form-control form-control-sm" placeholder="Delivery capacity, lead time and supplier reliability..." value="{{ old('notes') }}">
                    </div>

                </div>
            </div>

            {{-- Form Actions --}}
            <div class="card-footer supplier-card-footer d-flex justify-content-end align-items-center gap-2">
                <a href="{{ route('admin.suppliers.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>

                <button type="submit" class="btn btn-sm btn-primary px-4 fw-bold" id="submitSupplierBtn">
                    <i class="fas fa-check-circle me-1"></i> Register Supplier
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let isSubmittingSupplier = false;

    const openingBalanceInput = document.getElementById('opening_balance');
    const balanceTypeSelect = document.getElementById('opening_balance_type');
    const balanceSummary = document.getElementById('balanceTypeSummary');
    const supplierForm = document.getElementById('supplierForm');
    const submitBtn = document.getElementById('submitSupplierBtn');
    const loaderOverlay = document.getElementById('supplier-global-loader');

    function updateBalancePreview() {
        const amount = parseFloat(openingBalanceInput.value) || 0;
        const type = balanceTypeSelect.value;

        if (amount <= 0) {
            balanceSummary.innerText = 'Nil / Zero Balance';
            balanceSummary.className = 'text-dark';
            return;
        }

        if (type === 'payable') {
            balanceSummary.innerText = `৳${amount.toFixed(2)} (Accounts Payable Due — We Owe Vendor)`;
            balanceSummary.className = 'text-danger fw-bold font-monospace';
        } else {
            balanceSummary.innerText = `৳${amount.toFixed(2)} (Advance Deposit Asset — Vendor Owes Us)`;
            balanceSummary.className = 'text-success fw-bold font-monospace';
        }
    }

    openingBalanceInput.addEventListener('input', updateBalancePreview);
    balanceTypeSelect.addEventListener('change', updateBalancePreview);

    // Single Submit & Feedback Blocker
    supplierForm.addEventListener('submit', function (e) {
        if (isSubmittingSupplier) {
            e.preventDefault();
            return false;
        }

        isSubmittingSupplier = true;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Onboarding Supplier...';

        if (loaderOverlay) {
            loaderOverlay.style.display = 'flex';
        }
    });

    updateBalancePreview();
});
</script>
@endsection
