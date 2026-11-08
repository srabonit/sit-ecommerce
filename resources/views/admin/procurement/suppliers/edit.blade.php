
@extends('layouts.admin')

@section('title', 'Edit Supplier — ' . $supplier->name)

@section('admin_content')
<style>
    /* ==========================================
       SUPPLIER EDIT — KAIADMIN WHITE CLASSIC
    ========================================== */

    .supplier-edit-root {
        --se-primary: var(--bs-primary, #1572e8);
        --se-primary-soft: rgba(21, 114, 232, .08);
        --se-success: var(--bs-success, #31ce36);
        --se-danger: var(--bs-danger, #f25961);
        --se-text: #2a2f5b;
        --se-muted: #8d9498;
        --se-border: #ebedf2;
        --se-surface: #ffffff;
        color: var(--se-text);
        font-size: 13px;
        min-width: 0;
        width: 100%;
    }

    .supplier-edit-root *,
    .supplier-edit-root *::before,
    .supplier-edit-root *::after {
        box-sizing: border-box;
    }

    /* Page Header */

    .supplier-edit-root .supplier-page-header {
        gap: 14px;
        margin-bottom: 18px;
    }

    .supplier-edit-root .supplier-heading-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        min-width: 0;
    }

    .supplier-edit-root .supplier-page-title {
        font-size: clamp(17px, 2vw, 21px);
        font-weight: 700;
        color: var(--se-text);
        letter-spacing: -.35px;
        line-height: 1.4;
        margin: 0;
        overflow-wrap: anywhere;
    }

    .supplier-edit-root .supplier-page-title i {
        color: var(--se-primary);
    }

    .supplier-edit-root .supplier-meta-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        min-height: 24px;
        border-radius: 5px !important;
        font-size: 11px !important;
        font-weight: 600;
        padding: 4px 8px !important;
        white-space: normal;
    }

    .supplier-edit-root .supplier-code-badge {
        background: #f5f7fa !important;
        color: #68717b !important;
        border: 1px solid var(--se-border) !important;
    }

    .supplier-edit-root .breadcrumb {
        font-size: 11.5px;
        flex-wrap: wrap;
        row-gap: 4px;
    }

    .supplier-edit-root .breadcrumb-item a {
        color: var(--se-primary);
        text-decoration: none;
    }

    .supplier-edit-root .breadcrumb-item.active {
        color: var(--se-muted);
    }

    .supplier-edit-root .supplier-header-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 7px;
    }

    /* Buttons */

    .supplier-edit-root .btn {
        min-height: 35px;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        gap: 4px;
        border-radius: 5px !important;
        font-size: 11.5px;
        font-weight: 600;
        padding: 7px 12px;
        white-space: normal;
        transition: all .2s ease;
    }

    .supplier-edit-root .btn-primary {
        background: var(--se-primary);
        border-color: var(--se-primary);
    }

    .supplier-edit-root .btn-primary:hover {
        filter: brightness(.94);
    }

    .supplier-edit-root .btn-outline-secondary,
    .supplier-edit-root .btn-outline-dark,
    .supplier-edit-root .btn-outline-success {
        background: #fff;
        border-color: #e1e5eb;
    }

    .supplier-edit-root .btn-outline-secondary {
        color: #68717b;
    }

    .supplier-edit-root .btn-outline-dark {
        color: #495057;
    }

    .supplier-edit-root .btn-outline-success {
        color: #269c52;
        border-color: #d5e9dc;
    }

    .supplier-edit-root .btn-outline-secondary:hover,
    .supplier-edit-root .btn-outline-dark:hover {
        background: #f5f7fa;
        border-color: #cbd2dc;
    }

    /* Financial Summary */

    .supplier-edit-root .supplier-summary-card {
        background: #fff;
        border: 1px solid var(--se-border) !important;
        border-radius: 5px !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .035) !important;
        height: 100%;
        min-width: 0;
        padding: 16px !important;
        position: relative;
        overflow: hidden;
    }

    .supplier-edit-root .supplier-summary-card::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: var(--se-primary);
        opacity: .75;
    }

    .supplier-edit-root .supplier-summary-label {
        display: flex;
        align-items: center;
        gap: 7px;
        color: var(--se-muted);
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .45px;
        text-transform: uppercase;
        line-height: 1.5;
    }

    .supplier-edit-root .supplier-summary-label i {
        color: var(--se-primary);
        font-size: 12px;
    }

    .supplier-edit-root .supplier-summary-value {
        color: var(--se-text);
        font-size: clamp(17px, 2vw, 21px);
        font-weight: 700;
        line-height: 1.4;
        margin: 7px 0 4px;
        overflow-wrap: anywhere;
        letter-spacing: -.3px;
    }

    .supplier-edit-root .supplier-summary-description {
        color: var(--se-muted);
        font-size: 11px;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    /* Section Cards */

    .supplier-edit-root .supplier-section-card {
        background: var(--se-surface);
        border: 1px solid var(--se-border) !important;
        border-radius: 5px !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .035) !important;
        overflow: visible !important;
    }

    .supplier-edit-root .supplier-section-card .card-header {
        background: #fff !important;
        border-bottom: 1px solid var(--se-border) !important;
        border-radius: 5px 5px 0 0 !important;
        padding: 13px 17px !important;
    }

    .supplier-edit-root .supplier-section-card .card-body {
        padding: 18px !important;
        overflow: visible !important;
    }

    .supplier-edit-root .supplier-section-title {
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--se-text);
        font-size: 13px;
        font-weight: 700;
        line-height: 1.5;
        margin: 0;
    }

    .supplier-edit-root .supplier-section-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        flex: 0 0 30px;
        border-radius: 5px;
        background: var(--se-primary-soft);
        color: var(--se-primary);
        font-size: 13px;
    }

    .supplier-edit-root .supplier-section-description {
        color: var(--se-muted);
        font-size: 11.5px;
        font-weight: 400;
        margin: 2px 0 0 40px;
        line-height: 1.5;
    }

    /* Form Controls */

    .supplier-edit-root .form-label {
        display: block;
        color: #495057;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.45;
        margin-bottom: 6px !important;
        overflow-wrap: anywhere;
    }

    .supplier-edit-root .form-control,
    .supplier-edit-root .form-select {
        min-height: 36px;
        border: 1px solid #e6e9ed;
        border-radius: 5px !important;
        background-color: #fff;
        color: #495057;
        font-size: 12.5px;
        padding: 7px 10px;
        max-width: 100%;
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    .supplier-edit-root .form-control:focus,
    .supplier-edit-root .form-select:focus {
        border-color: var(--se-primary) !important;
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .11) !important;
        outline: 0;
    }

    .supplier-edit-root .form-control::placeholder {
        color: #adb5bd;
        font-size: 12px;
    }

    .supplier-edit-root textarea.form-control {
        min-height: 68px;
        resize: vertical;
        line-height: 1.5;
    }

    .supplier-edit-root .form-control.font-monospace {
        font-size: 12px;
    }

    .supplier-edit-root input[type="file"].form-control {
        padding: 5px;
        min-height: 36px;
        font-size: 11.5px;
    }

    .supplier-edit-root input[type="file"]::file-selector-button {
        border: 0;
        border-radius: 4px;
        background: #edf3fc;
        color: var(--se-primary);
        font-size: 11px;
        font-weight: 600;
        padding: 6px 9px;
        margin-right: 8px;
    }

    .supplier-edit-root .row {
        --bs-gutter-x: 16px;
        --bs-gutter-y: 16px;
    }

    /* Ledger Integrity Notice */

    .supplier-edit-root .supplier-ledger-notice {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 13px 14px !important;
        background: #f7f9fc !important;
        border: 1px solid var(--se-border) !important;
        border-radius: 5px !important;
        color: #68717b;
        font-size: 11.5px;
        line-height: 1.6;
    }

    .supplier-edit-root .supplier-ledger-message {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        min-width: 0;
    }

    .supplier-edit-root .supplier-ledger-message > i {
        color: var(--se-primary);
        margin-top: 3px;
        flex: 0 0 auto;
    }

    .supplier-edit-root .supplier-ledger-action {
        flex: 0 0 auto;
        color: var(--se-primary);
        font-weight: 600;
        text-decoration: none;
        white-space: normal;
    }

    .supplier-edit-root .supplier-ledger-action:hover {
        text-decoration: underline;
    }

    /* Current KYC File */

    .supplier-edit-root .supplier-current-file {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: var(--se-primary);
        font-size: 11.5px;
        font-weight: 600;
        max-width: 100%;
        overflow-wrap: anywhere;
        text-decoration: none;
        line-height: 1.5;
    }

    .supplier-edit-root .supplier-current-file:hover {
        text-decoration: underline;
    }

    /* Validation */

    .supplier-edit-root .alert {
        border-radius: 5px !important;
        font-size: 12.5px;
        padding: 12px 15px;
        overflow-wrap: anywhere;
    }

    .supplier-edit-root .alert ul {
        line-height: 1.7;
    }

    /* Form Footer */

    .supplier-edit-root .supplier-card-footer {
        background: #fff !important;
        border-top: 1px solid var(--se-border) !important;
        border-radius: 0 0 5px 5px !important;
        padding: 13px 18px !important;
    }

    /* Responsive */

    @media (min-width: 1400px) {
        .supplier-edit-root .supplier-section-card .card-body {
            padding: 20px !important;
        }

        .supplier-edit-root .row {
            --bs-gutter-x: 18px;
            --bs-gutter-y: 17px;
        }
    }

    @media (max-width: 991.98px) {
        .supplier-edit-root .supplier-header-actions {
            width: 100%;
        }

        .supplier-edit-root .supplier-section-card .card-body {
            padding: 16px !important;
        }

        .supplier-edit-root .row {
            --bs-gutter-x: 14px;
            --bs-gutter-y: 14px;
        }
    }

    @media (max-width: 767.98px) {
        .supplier-edit-root {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        .supplier-edit-root .supplier-page-header {
            align-items: flex-start !important;
        }

        .supplier-edit-root .supplier-header-actions {
            gap: 7px;
        }

        .supplier-edit-root .supplier-section-card .card-header {
            padding: 12px !important;
        }

        .supplier-edit-root .supplier-section-card .card-body {
            padding: 14px !important;
        }

        .supplier-edit-root .row {
            --bs-gutter-x: 12px;
            --bs-gutter-y: 13px;
        }

        .supplier-edit-root .supplier-ledger-notice {
            align-items: flex-start;
            flex-direction: column;
            gap: 9px;
        }
    }

    @media (max-width: 575.98px) {
        .supplier-edit-root .supplier-page-header {
            flex-direction: column;
            align-items: stretch !important;
        }

        .supplier-edit-root .supplier-heading-row {
            align-items: flex-start;
        }

        .supplier-edit-root .supplier-header-actions {
            display: grid;
            grid-template-columns: 1fr;
            width: 100%;
        }

        .supplier-edit-root .supplier-header-actions .btn {
            width: 100%;
            min-height: 38px;
        }

        .supplier-edit-root .supplier-section-card .card-body {
            padding: 12px !important;
        }

        .supplier-edit-root .supplier-section-card .card-header {
            padding: 12px !important;
        }

        .supplier-edit-root .supplier-section-title {
            font-size: 12.5px;
        }

        .supplier-edit-root .row {
            --bs-gutter-x: 10px;
            --bs-gutter-y: 12px;
        }

        .supplier-edit-root .form-label {
            font-size: 11.5px;
        }

        .supplier-edit-root .form-control,
        .supplier-edit-root .form-select {
            font-size: 12px;
        }

        .supplier-edit-root .supplier-card-footer {
            flex-direction: column-reverse;
            padding: 12px !important;
        }

        .supplier-edit-root .supplier-card-footer .btn {
            width: 100%;
            min-height: 38px;
        }

        .supplier-edit-root .supplier-section-icon {
            width: 28px;
            height: 28px;
            flex-basis: 28px;
        }

        .supplier-edit-root .supplier-section-description {
            margin-left: 38px;
        }

        .supplier-edit-root .supplier-summary-card {
            padding: 14px !important;
        }
    }

    @media (max-width: 359.98px) {
        .supplier-edit-root {
            padding-left: 8px !important;
            padding-right: 8px !important;
        }

        .supplier-edit-root .supplier-section-card .card-body {
            padding: 10px !important;
        }

        .supplier-edit-root .supplier-section-card .card-header {
            padding: 10px !important;
        }

        .supplier-edit-root .supplier-section-title {
            font-size: 11.5px;
        }

        .supplier-edit-root .row {
            --bs-gutter-x: 8px;
        }

        .supplier-edit-root .supplier-meta-badge {
            font-size: 10px !important;
        }
    }
</style>

<div class="container-fluid px-3 py-3 supplier-edit-root">

    {{-- Page Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between supplier-page-header">

        <div class="min-w-0">
            <div class="supplier-heading-row">
                <h5 class="supplier-page-title">
                    <i class="fas fa-user-pen me-2"></i>Edit Supplier: {{ $supplier->name }}
                </h5>

                <span class="badge supplier-meta-badge supplier-code-badge font-monospace">
                    Code: SUP-{{ str_pad($supplier->id, 4, '0', STR_PAD_LEFT) }}
                </span>

                @if(($supplier->status ?? 'active') === 'active')
                    <span class="badge bg-success-subtle text-success border border-success-subtle supplier-meta-badge">
                        <i class="fas fa-circle-check"></i> Active
                    </span>
                @else
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle supplier-meta-badge">
                        <i class="fas fa-ban"></i> Inactive / Blocked
                    </span>
                @endif
            </div>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.suppliers.index') }}">Suppliers</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.suppliers.ledger', $supplier->id) }}">{{ $supplier->name }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Profile</li>
                </ol>
            </nav>
        </div>

        <div class="supplier-header-actions">
            <a href="{{ route('admin.suppliers.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Supplier List
            </a>

            <a href="{{ route('admin.suppliers.ledger', $supplier->id) }}" class="btn btn-sm btn-outline-dark">
                <i class="fas fa-book-open"></i> View Ledger
            </a>

            <a href="{{ route('admin.supplier-payments.create', ['supplier_id' => $supplier->id]) }}" class="btn btn-sm btn-outline-success">
                <i class="fas fa-money-check-dollar"></i> Record Payment
            </a>
        </div>
    </div>

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

    {{-- Financial Summary --}}
    @php
        $currentBalance = (float) ($supplier->current_balance ?? 0);
        $openingBalance = (float) ($supplier->opening_balance ?? 0);
    @endphp

    <div class="row g-3 mb-3">

        {{-- Opening Balance --}}
        <div class="col-12 col-md-4">
            <div class="supplier-summary-card">
                <div class="supplier-summary-label">
                    <i class="fas fa-wallet"></i>
                    Opening Balance
                </div>

                <h5 class="supplier-summary-value">
                    ৳{{ number_format(abs($openingBalance), 2) }}
                </h5>

                <div class="supplier-summary-description">
                    {{ $openingBalance >= 0 ? '(Opening Payable)' : '(Opening Advance)' }}
                </div>
            </div>
        </div>

        {{-- Current Outstanding --}}
        <div class="col-12 col-md-4">
            <div class="supplier-summary-card">
                <div class="supplier-summary-label">
                    <i class="fas fa-scale-balanced"></i>
                    Current Outstanding
                </div>

                <h5 class="supplier-summary-value {{ $currentBalance > 0 ? 'text-danger' : ($currentBalance < 0 ? 'text-success' : 'text-muted') }}">
                    ৳{{ number_format(abs($currentBalance), 2) }}
                </h5>

                <div class="supplier-summary-description fw-semibold {{ $currentBalance > 0 ? 'text-danger' : ($currentBalance < 0 ? 'text-success' : 'text-muted') }}">
                    @if($currentBalance > 0)
                        Net Accounts Payable (We Owe Vendor)
                    @elseif($currentBalance < 0)
                        Advance Deposit Surplus (Vendor Owes Us)
                    @else
                        Settled / Zero Balance
                    @endif
                </div>
            </div>
        </div>

        {{-- Credit Limit --}}
        <div class="col-12 col-md-4">
            <div class="supplier-summary-card">
                <div class="supplier-summary-label">
                    <i class="fas fa-credit-card"></i>
                    Credit Limit
                </div>

                <h5 class="supplier-summary-value text-primary">
                    {{ (float)$supplier->credit_limit > 0 ? '৳' . number_format((float)$supplier->credit_limit, 2) : 'No Limit Enforced' }}
                </h5>

                <div class="supplier-summary-description">
                    Terms: {{ ucfirst(str_replace('_', ' ', $supplier->payment_terms ?? 'Net 30 Days')) }}
                </div>
            </div>
        </div>

    </div>

    <form action="{{ route('admin.suppliers.update', $supplier->id) }}" method="POST" enctype="multipart/form-data" id="supplierEditForm">
        @csrf
        @method('PUT')

        {{-- Section 1: Business Identity & Contact --}}
        <div class="card supplier-section-card mb-3">
            <div class="card-header">
                <h6 class="supplier-section-title">
                    <span class="supplier-section-icon">
                        <i class="fas fa-id-card"></i>
                    </span>
                    Business Identity &amp; Contact
                </h6>
                <p class="supplier-section-description">Supplier profile and primary contact information.</p>
            </div>

            <div class="card-body">
                <div class="row">

                    {{-- Supplier Name --}}
                    <div class="col-12 col-md-4">
                        <label class="form-label">Supplier Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm" value="{{ old('name', $supplier->name) }}" required>
                    </div>

                    {{-- Company Name --}}
                    <div class="col-12 col-md-4">
                        <label class="form-label">Registered Company Name</label>
                        <input type="text" name="company_name" class="form-control form-control-sm" value="{{ old('company_name', $supplier->company_name) }}">
                    </div>

                    {{-- Contact Person --}}
                    <div class="col-6 col-md-4">
                        <label class="form-label">Contact Person</label>
                        <input type="text" name="contact_person" class="form-control form-control-sm" value="{{ old('contact_person', $supplier->contact_person) }}">
                    </div>

                    {{-- Primary Phone --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label">Primary Phone <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control form-control-sm" value="{{ old('phone', $supplier->phone) }}" required>
                    </div>

                    {{-- Alternate Phone --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label">Alternate Phone / WhatsApp</label>
                        <input type="text" name="alt_phone" class="form-control form-control-sm" value="{{ old('alt_phone', $supplier->alt_phone) }}">
                    </div>

                    {{-- Email --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control form-control-sm" value="{{ old('email', $supplier->email) }}">
                    </div>

                    {{-- Status --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label">Supplier Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select form-select-sm" required>
                            <option value="active" {{ old('status', $supplier->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $supplier->status) === 'inactive' ? 'selected' : '' }}>Inactive / Blocked</option>
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
                <p class="supplier-section-description">Registration details and supplier classification.</p>
            </div>

            <div class="card-body">
                <div class="row">

                    {{-- BIN / VAT --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label">BIN / VAT Number</label>
                        <input type="text" name="bin_number" class="form-control form-control-sm font-monospace" value="{{ old('bin_number', $supplier->bin_number) }}">
                    </div>

                    {{-- e-TIN --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label">e-TIN Number</label>
                        <input type="text" name="tax_number" class="form-control form-control-sm font-monospace" value="{{ old('tax_number', $supplier->tax_number) }}">
                    </div>

                    {{-- Trade License --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label">Trade License Number</label>
                        <input type="text" name="trade_license_no" class="form-control form-control-sm font-monospace" value="{{ old('trade_license_no', $supplier->trade_license_no) }}">
                    </div>

                    {{-- Category --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label">Supplier Category</label>
                        <select name="category_id" class="form-select form-select-sm">
                            <option value="">General Supplier</option>
                            @foreach($categories ?? [] as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $supplier->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>
            </div>
        </div>

        {{-- Section 3: Commercial Terms & Credit --}}
        <div class="card supplier-section-card mb-3">
            <div class="card-header">
                <h6 class="supplier-section-title">
                    <span class="supplier-section-icon">
                        <i class="fas fa-scale-balanced"></i>
                    </span>
                    Commercial Terms &amp; Credit
                </h6>
                <p class="supplier-section-description">Manage payment terms, credit limit and settlement currency.</p>
            </div>

            <div class="card-body">
                <div class="row">

                    {{-- Payment Terms --}}
                    <div class="col-6 col-md-4">
                        <label class="form-label">Payment Terms <span class="text-danger">*</span></label>
                        <select name="payment_terms" class="form-select form-select-sm" required>
                            <option value="net_30" {{ old('payment_terms', $supplier->payment_terms ?? 'net_30') === 'net_30' ? 'selected' : '' }}>Net 30 Days</option>
                            <option value="net_15" {{ old('payment_terms', $supplier->payment_terms) === 'net_15' ? 'selected' : '' }}>Net 15 Days</option>
                            <option value="net_60" {{ old('payment_terms', $supplier->payment_terms) === 'net_60' ? 'selected' : '' }}>Net 60 Days</option>
                            <option value="cash_on_delivery" {{ old('payment_terms', $supplier->payment_terms) === 'cash_on_delivery' ? 'selected' : '' }}>Cash on Delivery (COD)</option>
                            <option value="advance_payment" {{ old('payment_terms', $supplier->payment_terms) === 'advance_payment' ? 'selected' : '' }}>100% Advance</option>
                        </select>
                    </div>

                    {{-- Credit Limit --}}
                    <div class="col-6 col-md-4">
                        <label class="form-label">Credit Limit (৳)</label>
                        <input type="number" step="0.01" name="credit_limit" class="form-control form-control-sm text-end" value="{{ old('credit_limit', $supplier->credit_limit ?? 0) }}" min="0">
                    </div>

                    {{-- Currency --}}
                    <div class="col-6 col-md-4">
                        <label class="form-label">Settlement Currency <span class="text-danger">*</span></label>
                        <select name="currency" class="form-select form-select-sm" required>
                            <option value="BDT" {{ old('currency', $supplier->currency ?? 'BDT') === 'BDT' ? 'selected' : '' }}>BDT (৳ &mdash; Bangladeshi Taka)</option>
                            <option value="USD" {{ old('currency', $supplier->currency) === 'USD' ? 'selected' : '' }}>USD ($ &mdash; US Dollar)</option>
                            <option value="EUR" {{ old('currency', $supplier->currency) === 'EUR' ? 'selected' : '' }}>EUR (€ &mdash; Euro)</option>
                            <option value="CNY" {{ old('currency', $supplier->currency) === 'CNY' ? 'selected' : '' }}>CNY (¥ &mdash; Chinese Yuan)</option>
                        </select>
                    </div>

                    {{-- Ledger Integrity Notice --}}
                    <div class="col-12">
                        <div class="supplier-ledger-notice">
                            <div class="supplier-ledger-message">
                                <i class="fas fa-lock"></i>
                                <span>
                                    <strong class="text-dark">Ledger Protection:</strong>
                                    The opening balance is locked to preserve the financial audit trail. Use a debit or credit note for adjustments.
                                </span>
                            </div>

                            <a href="{{ route('admin.supplier-debit-credit-notes.create', ['supplier_id' => $supplier->id]) }}" class="supplier-ledger-action">
                                Create Adjustment <i class="fas fa-arrow-up-right-from-square fa-xs ms-1"></i>
                            </a>
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
                <p class="supplier-section-description">Bank accounts and mobile financial service details.</p>
            </div>

            <div class="card-body">
                <div class="row">

                    {{-- Bank Name --}}
                    <div class="col-12 col-md-3">
                        <label class="form-label">Bank Name</label>
                        <input type="text" name="bank_name" class="form-control form-control-sm" value="{{ old('bank_name', $supplier->bank_name) }}">
                    </div>

                    {{-- Branch --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label">Branch Name</label>
                        <input type="text" name="bank_branch" class="form-control form-control-sm" value="{{ old('bank_branch', $supplier->bank_branch) }}">
                    </div>

                    {{-- Account Name --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label">Account Holder Name</label>
                        <input type="text" name="bank_account_name" class="form-control form-control-sm" value="{{ old('bank_account_name', $supplier->bank_account_name) }}">
                    </div>

                    {{-- Account Number --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label">Bank Account Number</label>
                        <input type="text" name="bank_account_no" class="form-control form-control-sm font-monospace fw-semibold" value="{{ old('bank_account_no', $supplier->bank_account_no) }}">
                    </div>

                    {{-- Routing --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label">Routing Number</label>
                        <input type="text" name="bank_routing_no" class="form-control form-control-sm font-monospace" value="{{ old('bank_routing_no', $supplier->bank_routing_no) }}">
                    </div>

                    {{-- SWIFT --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label">SWIFT / BIC Code</label>
                        <input type="text" name="swift_code" class="form-control form-control-sm font-monospace" value="{{ old('swift_code', $supplier->swift_code) }}">
                    </div>

                    {{-- MFS --}}
                    <div class="col-12 col-md-6">
                        <label class="form-label">bKash / Nagad Wallet Number</label>
                        <input type="text" name="mfs_number" class="form-control form-control-sm font-monospace" value="{{ old('mfs_number', $supplier->mfs_number) }}">
                    </div>

                </div>
            </div>
        </div>

        {{-- Section 5: Address, Documents & Notes --}}
        <div class="card supplier-section-card mb-3">
            <div class="card-header">
                <h6 class="supplier-section-title">
                    <span class="supplier-section-icon">
                        <i class="fas fa-location-dot"></i>
                    </span>
                    Address, Documents &amp; Notes
                </h6>
                <p class="supplier-section-description">Business location, KYC documents and operational remarks.</p>
            </div>

            <div class="card-body">
                <div class="row">

                    {{-- Address --}}
                    <div class="col-12 col-md-6">
                        <label class="form-label">Business Address <span class="text-danger">*</span></label>
                        <textarea name="address" rows="2" class="form-control form-control-sm" required>{{ old('address', $supplier->address) }}</textarea>
                    </div>

                    {{-- City --}}
                    <div class="col-6 col-md-2">
                        <label class="form-label">City / District <span class="text-danger">*</span></label>
                        <input type="text" name="city" class="form-control form-control-sm" value="{{ old('city', $supplier->city ?? 'Dhaka') }}" required>
                    </div>

                    {{-- Postal Code --}}
                    <div class="col-6 col-md-2">
                        <label class="form-label">Postal Code</label>
                        <input type="text" name="postal_code" class="form-control form-control-sm font-monospace" value="{{ old('postal_code', $supplier->postal_code) }}">
                    </div>

                    {{-- Country --}}
                    <div class="col-12 col-md-2">
                        <label class="form-label">Country <span class="text-danger">*</span></label>
                        <input type="text" name="country" class="form-control form-control-sm" value="{{ old('country', $supplier->country ?? 'Bangladesh') }}" required>
                    </div>

                    {{-- KYC Document --}}
                    <div class="col-12 col-md-4">
                        <label class="form-label">KYC Document</label>
                        <input type="file" name="kyc_document" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">

                        @if($supplier->kyc_document)
                            <div class="mt-2">
                                <a href="{{ asset('storage/' . $supplier->kyc_document) }}" target="_blank" class="supplier-current-file">
                                    <i class="fas fa-file-pdf"></i>
                                    <span>Current File: {{ basename($supplier->kyc_document) }}</span>
                                </a>
                            </div>
                        @endif
                    </div>

                    {{-- Notes --}}
                    <div class="col-12 col-md-8">
                        <label class="form-label">Internal Remarks / Delivery Notes</label>
                        <input type="text" name="notes" class="form-control form-control-sm" value="{{ old('notes', $supplier->notes) }}">
                    </div>

                </div>
            </div>

            {{-- Form Actions --}}
            <div class="card-footer supplier-card-footer d-flex justify-content-end align-items-center gap-2">
                <a href="{{ route('admin.suppliers.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>

                <button type="submit" class="btn btn-sm btn-primary px-4">
                    <i class="fas fa-floppy-disk me-1"></i> Update Supplier
                </button>
            </div>
        </div>

    </form>
</div>
@endsection
