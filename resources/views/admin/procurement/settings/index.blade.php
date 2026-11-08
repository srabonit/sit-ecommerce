
@extends('layouts.admin')

@section('title', 'Procurement Settings')

@section('admin_content')

<style>
/* =========================================================
   PROCUREMENT SETTINGS — KAIADMIN CLASSIC DESIGN
   Scoped CSS | Bootstrap 5 Compatible
========================================================= */

.procurement-settings-page {
    --proc-primary: var(--bs-primary, #1572e8);
    --proc-secondary: #6c757d;
    --proc-body-bg: #f8f9fa;
    --proc-card-bg: #ffffff;
    --proc-heading: #2a2f5b;
    --proc-text: #495057;
    --proc-muted: #8d9498;
    --proc-border: #ebecec;
    --proc-radius: 8px;
    --proc-shadow: 0 2px 8px rgba(0, 0, 0, .035);

    color: var(--proc-text);
    font-size: 13px;
    min-width: 0;
}

.procurement-settings-page *,
.procurement-settings-page *::before,
.procurement-settings-page *::after {
    box-sizing: border-box;
}

.procurement-settings-page .page-heading {
    color: var(--proc-heading);
    font-size: clamp(17px, 1.5vw, 21px);
    font-weight: 700;
    letter-spacing: -.35px;
    line-height: 1.4;
}

.procurement-settings-page .breadcrumb {
    font-size: 12px !important;
    flex-wrap: wrap;
}

.procurement-settings-page .breadcrumb-item,
.procurement-settings-page .breadcrumb-item a {
    color: var(--proc-muted);
    text-decoration: none;
}

.procurement-settings-page .breadcrumb-item.active {
    color: var(--proc-heading);
    font-weight: 600;
}

.procurement-settings-page .card {
    background: var(--proc-card-bg);
    border: 1px solid var(--proc-border) !important;
    border-radius: var(--proc-radius) !important;
    box-shadow: var(--proc-shadow) !important;
    min-width: 0;
}

.procurement-settings-page .card-header,
.procurement-settings-page .card-footer {
    background: #fff !important;
    border-color: var(--proc-border) !important;
}

.procurement-settings-page .btn {
    min-height: 36px;
    border-radius: 6px !important;
    font-size: 12px;
    font-weight: 600;
    transition: all .2s ease;
}

.procurement-settings-page .btn-sm {
    min-height: 33px;
    padding: 6px 12px;
}

.procurement-settings-page .btn-primary {
    box-shadow: 0 2px 5px rgba(21, 114, 232, .12);
}

.procurement-settings-page .btn-primary:hover {
    box-shadow: 0 4px 10px rgba(21, 114, 232, .18);
}

.procurement-settings-page .form-label {
    color: var(--proc-heading);
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 7px;
}

.procurement-settings-page .form-control,
.procurement-settings-page .form-select {
    min-height: 38px;
    border: 1px solid #e1e5e9;
    border-radius: 6px;
    color: var(--proc-text);
    font-size: 12.5px;
    background-color: #fff;
    box-shadow: none;
}

.procurement-settings-page .form-control-sm,
.procurement-settings-page .form-select-sm {
    min-height: 35px;
    font-size: 12px;
    border-radius: 5px;
}

.procurement-settings-page .form-control:focus,
.procurement-settings-page .form-select:focus {
    border-color: var(--proc-primary);
    box-shadow: 0 0 0 .15rem rgba(21, 114, 232, .10);
}

.procurement-settings-page .form-control::placeholder {
    color: #adb5bd;
}

.procurement-settings-page textarea.form-control {
    min-height: 90px;
    line-height: 1.6;
    resize: vertical;
}

.procurement-settings-page .form-check-input {
    cursor: pointer;
    border-color: #cbd0d5;
}

.procurement-settings-page .form-check-input:checked {
    background-color: var(--proc-primary);
    border-color: var(--proc-primary);
}

.procurement-settings-page .form-check-input:focus {
    box-shadow: 0 0 0 .15rem rgba(21, 114, 232, .12);
}

.procurement-settings-page .form-switch .form-check-input {
    width: 2.25em;
    height: 1.15em;
    margin-top: .2em;
}

.procurement-settings-page .form-check-label {
    font-size: 12.5px;
    line-height: 1.6;
    cursor: pointer;
}

.procurement-settings-page .input-group-text {
    background: #f8f9fa;
    border-color: #e1e5e9;
    color: #697386;
    font-size: 12px;
    font-weight: 600;
}

.procurement-settings-page .text-muted {
    color: var(--proc-muted) !important;
}

.procurement-settings-page .section-heading {
    color: var(--proc-heading);
    font-size: 14px;
    font-weight: 700;
    margin-bottom: 5px;
}

.procurement-settings-page .section-description {
    color: var(--proc-muted);
    font-size: 12px;
    line-height: 1.7;
}

.procurement-settings-page .nav-tabs {
    display: flex;
    flex-wrap: nowrap;
    gap: 5px;
    overflow-x: auto;
    overflow-y: hidden;
    scrollbar-width: thin;
    scrollbar-color: #d5dbe1 transparent;
}

.procurement-settings-page .nav-tabs .nav-item {
    flex: 0 0 auto;
}

.procurement-settings-page .nav-tabs .nav-link {
    position: relative;
    min-height: 47px;
    padding: 13px 14px 12px !important;
    border: 0 !important;
    border-bottom: 2px solid transparent !important;
    border-radius: 0 !important;
    color: #7d8490 !important;
    background: transparent !important;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
    transition: color .2s ease;
}

.procurement-settings-page .nav-tabs .nav-link:hover {
    color: var(--proc-primary) !important;
}

.procurement-settings-page .nav-tabs .nav-link.active {
    color: var(--proc-primary) !important;
    border-bottom-color: var(--proc-primary) !important;
    background: transparent !important;
}

.procurement-settings-page .tab-content {
    min-width: 0;
}

.procurement-settings-page .tab-pane {
    min-width: 0;
}

.procurement-settings-page .tab-pane > .mb-3 {
    margin-bottom: 20px !important;
}

.procurement-settings-page .table-responsive {
    border-color: var(--proc-border) !important;
    border-radius: 7px !important;
    background: #fff !important;
    scrollbar-width: thin;
}

.procurement-settings-page .table {
    width: 100%;
    min-width: 880px;
    color: var(--proc-text);
    font-size: 12px !important;
    vertical-align: middle;
    border-color: var(--proc-border);
}

.procurement-settings-page .table thead th {
    padding: 12px 11px;
    background: #f8f9fc;
    color: #737b89 !important;
    font-size: 10.5px !important;
    font-weight: 700;
    letter-spacing: .35px;
    white-space: nowrap;
    border-color: var(--proc-border);
}

.procurement-settings-page .table tbody td {
    padding: 10px 11px;
    border-color: var(--proc-border);
    line-height: 1.5;
}

.procurement-settings-page .table tbody tr {
    transition: background .15s ease;
}

.procurement-settings-page .table tbody tr:hover {
    background: #f9fbff;
}

.procurement-settings-page .table tbody td:first-child {
    min-width: 180px;
}

.procurement-settings-page .table .form-control,
.procurement-settings-page .table .form-select {
    min-width: 105px;
}

.procurement-settings-page .preview-display {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 29px;
    max-width: 100%;
    padding: 5px 9px !important;
    font-size: 11px !important;
    font-weight: 600;
    white-space: nowrap;
    border-radius: 5px;
}

.procurement-settings-page .font-monospace {
    letter-spacing: .1px;
}

.procurement-settings-page .row > [class*="col-"] > .p-3.border {
    height: 100%;
    padding: 17px !important;
    background: #fff !important;
    border: 1px solid var(--proc-border) !important;
    border-radius: 8px !important;
    transition: border-color .2s ease, box-shadow .2s ease;
}

.procurement-settings-page .row > [class*="col-"] > .p-3.border:hover {
    border-color: #d7e5f7 !important;
    box-shadow: 0 3px 12px rgba(0, 0, 0, .035);
}

.procurement-settings-page .row > [class*="col-"] > .p-3.border p {
    font-size: 11.5px;
    line-height: 1.7;
}

.procurement-settings-page .alert {
    border-radius: 7px !important;
    font-size: 12.5px;
    line-height: 1.6;
}

.procurement-settings-page .alert ul {
    font-size: 12px;
}

.procurement-settings-page .card-footer {
    padding: 14px 20px !important;
}

.procurement-settings-page .card-footer .btn {
    min-width: 90px;
}

.procurement-settings-page .tab-pane .row {
    --bs-gutter-x: 1rem;
    --bs-gutter-y: 1rem;
}

.procurement-settings-page .small {
    line-height: 1.6;
}

.procurement-settings-page .form-check {
    min-height: 24px;
}

.procurement-settings-page .form-check-label {
    color: var(--proc-heading);
}

.procurement-settings-page .table-white {
    --bs-table-bg: #fff;
}

/* Tablet */
@media (max-width: 991.98px) {
    .procurement-settings-page .tab-pane {
        padding: 20px !important;
    }

    .procurement-settings-page .nav-tabs .nav-link {
        padding-left: 12px !important;
        padding-right: 12px !important;
    }
}

/* Mobile */
@media (max-width: 767.98px) {
    .procurement-settings-page {
        font-size: 12px;
    }

    .procurement-settings-page .page-heading {
        font-size: 17px;
    }

    .procurement-settings-page .page-topbar {
        align-items: flex-start !important;
    }

    .procurement-settings-page .page-actions {
        width: 100%;
        display: flex;
        flex-wrap: wrap;
    }

    .procurement-settings-page .page-actions .btn {
        flex: 1 1 auto;
        justify-content: center;
    }

    .procurement-settings-page .tab-pane {
        padding: 15px !important;
    }

    .procurement-settings-page .nav-tabs {
        padding-left: 10px !important;
        padding-right: 10px !important;
    }

    .procurement-settings-page .nav-tabs .nav-link {
        min-height: 44px;
        font-size: 11.5px;
        padding: 12px 10px !important;
    }

    .procurement-settings-page .section-heading {
        font-size: 13px;
    }

    .procurement-settings-page .row > [class*="col-"] > .p-3.border {
        padding: 14px !important;
    }

    .procurement-settings-page .card-footer {
        padding: 12px 14px !important;
    }

    .procurement-settings-page .card-footer .btn {
        flex: 1 1 0;
    }
}

/* Small mobile */
@media (max-width: 399.98px) {
    .procurement-settings-page .container-fluid {
        padding-left: 10px !important;
        padding-right: 10px !important;
    }

    .procurement-settings-page .page-heading {
        font-size: 15px;
    }

    .procurement-settings-page .page-actions .btn {
        font-size: 11px;
        padding-left: 8px;
        padding-right: 8px;
    }

    .procurement-settings-page .tab-pane {
        padding: 12px !important;
    }

    .procurement-settings-page .card-footer .btn {
        min-width: 0;
        font-size: 11px;
        padding-left: 8px;
        padding-right: 8px;
    }
}
</style>

<div class="container-fluid px-3 py-3 procurement-settings-page">

    {{-- Breadcrumb & Top Action Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 page-topbar">
        <div>
            <h5 class="page-heading mb-1">
                <i class="fas fa-gears text-primary me-2"></i>Procurement Settings
            </h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Procurement</li>
                    <li class="breadcrumb-item active" aria-current="page">Module Settings</li>
                </ol>
            </nav>
        </div>

        <div class="d-flex gap-2 page-actions">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.location.reload()">
                <i class="fas fa-rotate-left me-1"></i> Discard Changes
            </button>
            <button type="submit" form="procurementSettingsForm" class="btn btn-sm btn-primary px-3">
                <i class="fas fa-floppy-disk me-1"></i> Save Settings
            </button>
        </div>
    </div>

    {{-- Session Feedback Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3">
            <i class="fas fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3">
            <div class="d-flex align-items-center gap-2 fw-semibold mb-1">
                <i class="fas fa-circle-exclamation"></i> Settings update encountered errors:
            </div>
            <ul class="mb-0 ps-3 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('admin.procurement-settings.update') }}" method="POST" id="procurementSettingsForm">
        @csrf
        @method('PUT')

        {{-- Nav Tabs Navigation --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom p-0">
                <ul class="nav nav-tabs border-0 px-3 pt-2" id="settingsTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-semibold text-dark border-0 border-bottom border-primary border-3 pb-2"
                                id="numbering-tab" data-bs-toggle="tab" data-bs-target="#numbering-pane" type="button" role="tab">
                            <i class="fas fa-barcode text-primary me-2"></i>Document Numbering
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold text-secondary border-0 pb-2"
                                id="approval-tab" data-bs-toggle="tab" data-bs-target="#approval-pane" type="button" role="tab">
                            <i class="fas fa-sliders text-warning-emphasis me-2"></i>Approvals &amp; Tolerance
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold text-secondary border-0 pb-2"
                                id="gl-tab" data-bs-toggle="tab" data-bs-target="#gl-pane" type="button" role="tab">
                            <i class="fas fa-book-journal-whills text-success me-2"></i>Ledger Mapping
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold text-secondary border-0 pb-2"
                                id="terms-tab" data-bs-toggle="tab" data-bs-target="#terms-pane" type="button" role="tab">
                            <i class="fas fa-file-contract text-info me-2"></i>Terms &amp; Print Settings
                        </button>
                    </li>
                </ul>
            </div>

            <div class="tab-content" id="settingsTabContent">

                {{-- TAB 1: Document Numbering & Prefixes --}}
                <div class="tab-pane fade show active p-4" id="numbering-pane" role="tabpanel" tabindex="0">
                    <div class="mb-3">
                        <h6 class="section-heading">Automated Voucher Numbering</h6>
                        <p class="section-description mb-0">Configure document prefixes, date formats and serial number padding.</p>
                    </div>

                    <div class="table-responsive bg-light rounded border mb-3">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-white text-secondary text-uppercase">
                                <tr>
                                    <th style="min-width: 180px;">Document Type</th>
                                    <th style="width: 140px;">Prefix Code</th>
                                    <th style="width: 160px;">Date Format</th>
                                    <th style="width: 130px;">Zero Padding</th>
                                    <th style="width: 200px;">Generated Preview</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Purchase Order (PO) --}}
                                <tr>
                                    <td class="fw-semibold text-dark">
                                        <i class="fas fa-file-signature text-primary me-2"></i>Purchase Order (PO)
                                    </td>
                                    <td>
                                        <input type="text" name="po_prefix" class="form-control form-control-sm font-monospace prefix-input" data-target="po-preview" value="{{ old('po_prefix', $settings['po_prefix'] ?? 'PO-') }}" required>
                                    </td>
                                    <td>
                                        <select name="po_date_format" class="form-select form-select-sm format-input" data-target="po-preview">
                                            <option value="Ymd" {{ ($settings['po_date_format'] ?? 'Ymd') === 'Ymd' ? 'selected' : '' }}>YYYYMMDD ({{ date('Ymd') }})</option>
                                            <option value="Ym" {{ ($settings['po_date_format'] ?? '') === 'Ym' ? 'selected' : '' }}>YYYYMM ({{ date('Ym') }})</option>
                                            <option value="none" {{ ($settings['po_date_format'] ?? '') === 'none' ? 'selected' : '' }}>No Date Stamp</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="po_padding" class="form-control form-control-sm text-center padding-input" data-target="po-preview" value="{{ old('po_padding', $settings['po_padding'] ?? 4) }}" min="2" max="8">
                                    </td>
                                    <td>
                                        <span class="badge bg-white text-primary border font-monospace px-2.5 py-1 fs-6 preview-display" id="po-preview">PO-{{ date('Ymd') }}-0001</span>
                                    </td>
                                </tr>

                                {{-- Goods Received Note (GRN) --}}
                                <tr>
                                    <td class="fw-semibold text-dark">
                                        <i class="fas fa-truck-ramp-box text-success me-2"></i>Goods Received Note (GRN)
                                    </td>
                                    <td>
                                        <input type="text" name="grn_prefix" class="form-control form-control-sm font-monospace prefix-input" data-target="grn-preview" value="{{ old('grn_prefix', $settings['grn_prefix'] ?? 'GRN-') }}" required>
                                    </td>
                                    <td>
                                        <select name="grn_date_format" class="form-select form-select-sm format-input" data-target="grn-preview">
                                            <option value="Ymd" {{ ($settings['grn_date_format'] ?? 'Ymd') === 'Ymd' ? 'selected' : '' }}>YYYYMMDD ({{ date('Ymd') }})</option>
                                            <option value="Ym" {{ ($settings['grn_date_format'] ?? '') === 'Ym' ? 'selected' : '' }}>YYYYMM ({{ date('Ym') }})</option>
                                            <option value="none" {{ ($settings['grn_date_format'] ?? '') === 'none' ? 'selected' : '' }}>No Date Stamp</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="grn_padding" class="form-control form-control-sm text-center padding-input" data-target="grn-preview" value="{{ old('grn_padding', $settings['grn_padding'] ?? 4) }}" min="2" max="8">
                                    </td>
                                    <td>
                                        <span class="badge bg-white text-success border font-monospace px-2.5 py-1 fs-6 preview-display" id="grn-preview">GRN-{{ date('Ymd') }}-0001</span>
                                    </td>
                                </tr>

                                {{-- Purchase Invoice / Bill --}}
                                <tr>
                                    <td class="fw-semibold text-dark">
                                        <i class="fas fa-file-invoice text-dark me-2"></i>Purchase Bill / Invoice
                                    </td>
                                    <td>
                                        <input type="text" name="bill_prefix" class="form-control form-control-sm font-monospace prefix-input" data-target="bill-preview" value="{{ old('bill_prefix', $settings['bill_prefix'] ?? 'BILL-') }}" required>
                                    </td>
                                    <td>
                                        <select name="bill_date_format" class="form-select form-select-sm format-input" data-target="bill-preview">
                                            <option value="Ymd" {{ ($settings['bill_date_format'] ?? 'Ymd') === 'Ymd' ? 'selected' : '' }}>YYYYMMDD ({{ date('Ymd') }})</option>
                                            <option value="Ym" {{ ($settings['bill_date_format'] ?? '') === 'Ym' ? 'selected' : '' }}>YYYYMM ({{ date('Ym') }})</option>
                                            <option value="none" {{ ($settings['bill_date_format'] ?? '') === 'none' ? 'selected' : '' }}>No Date Stamp</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="bill_padding" class="form-control form-control-sm text-center padding-input" data-target="bill-preview" value="{{ old('bill_padding', $settings['bill_padding'] ?? 4) }}" min="2" max="8">
                                    </td>
                                    <td>
                                        <span class="badge bg-white text-dark border font-monospace px-2.5 py-1 fs-6 preview-display" id="bill-preview">BILL-{{ date('Ymd') }}-0001</span>
                                    </td>
                                </tr>

                                {{-- Purchase Return / RTV --}}
                                <tr>
                                    <td class="fw-semibold text-dark">
                                        <i class="fas fa-arrow-rotate-left text-danger me-2"></i>Purchase Return (RTV)
                                    </td>
                                    <td>
                                        <input type="text" name="rtv_prefix" class="form-control form-control-sm font-monospace prefix-input" data-target="rtv-preview" value="{{ old('rtv_prefix', $settings['rtv_prefix'] ?? 'RTV-') }}" required>
                                    </td>
                                    <td>
                                        <select name="rtv_date_format" class="form-select form-select-sm format-input" data-target="rtv-preview">
                                            <option value="Ymd" {{ ($settings['rtv_date_format'] ?? 'Ymd') === 'Ymd' ? 'selected' : '' }}>YYYYMMDD ({{ date('Ymd') }})</option>
                                            <option value="Ym" {{ ($settings['rtv_date_format'] ?? '') === 'Ym' ? 'selected' : '' }}>YYYYMM ({{ date('Ym') }})</option>
                                            <option value="none" {{ ($settings['rtv_date_format'] ?? '') === 'none' ? 'selected' : '' }}>No Date Stamp</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="rtv_padding" class="form-control form-control-sm text-center padding-input" data-target="rtv-preview" value="{{ old('rtv_padding', $settings['rtv_padding'] ?? 4) }}" min="2" max="8">
                                    </td>
                                    <td>
                                        <span class="badge bg-white text-danger border font-monospace px-2.5 py-1 fs-6 preview-display" id="rtv-preview">RTV-{{ date('Ymd') }}-0001</span>
                                    </td>
                                </tr>

                                {{-- Supplier Payment Receipt --}}
                                <tr>
                                    <td class="fw-semibold text-dark">
                                        <i class="fas fa-money-check-dollar text-success me-2"></i>Disbursement Payment Voucher
                                    </td>
                                    <td>
                                        <input type="text" name="payment_prefix" class="form-control form-control-sm font-monospace prefix-input" data-target="payment-preview" value="{{ old('payment_prefix', $settings['payment_prefix'] ?? 'SPV-') }}" required>
                                    </td>
                                    <td>
                                        <select name="payment_date_format" class="form-select form-select-sm format-input" data-target="payment-preview">
                                            <option value="Ymd" {{ ($settings['payment_date_format'] ?? 'Ymd') === 'Ymd' ? 'selected' : '' }}>YYYYMMDD ({{ date('Ymd') }})</option>
                                            <option value="Ym" {{ ($settings['payment_date_format'] ?? '') === 'Ym' ? 'selected' : '' }}>YYYYMM ({{ date('Ym') }})</option>
                                            <option value="none" {{ ($settings['payment_date_format'] ?? '') === 'none' ? 'selected' : '' }}>No Date Stamp</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="payment_padding" class="form-control form-control-sm text-center padding-input" data-target="payment-preview" value="{{ old('payment_padding', $settings['payment_padding'] ?? 4) }}" min="2" max="8">
                                    </td>
                                    <td>
                                        <span class="badge bg-white text-success border font-monospace px-2.5 py-1 fs-6 preview-display" id="payment-preview">SPV-{{ date('Ymd') }}-0001</span>
                                    </td>
                                </tr>

                                {{-- Supplier Exchange / Swap --}}
                                <tr>
                                    <td class="fw-semibold text-dark">
                                        <i class="fas fa-right-left text-info me-2"></i>Product Exchange / Swap Note
                                    </td>
                                    <td>
                                        <input type="text" name="exchange_prefix" class="form-control form-control-sm font-monospace prefix-input" data-target="exchange-preview" value="{{ old('exchange_prefix', $settings['exchange_prefix'] ?? 'EXC-') }}" required>
                                    </td>
                                    <td>
                                        <select name="exchange_date_format" class="form-select form-select-sm format-input" data-target="exchange-preview">
                                            <option value="Ymd" {{ ($settings['exchange_date_format'] ?? 'Ymd') === 'Ymd' ? 'selected' : '' }}>YYYYMMDD ({{ date('Ymd') }})</option>
                                            <option value="Ym" {{ ($settings['exchange_date_format'] ?? '') === 'Ym' ? 'selected' : '' }}>YYYYMM ({{ date('Ym') }})</option>
                                            <option value="none" {{ ($settings['exchange_date_format'] ?? '') === 'none' ? 'selected' : '' }}>No Date Stamp</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="exchange_padding" class="form-control form-control-sm text-center padding-input" data-target="exchange-preview" value="{{ old('exchange_padding', $settings['exchange_padding'] ?? 4) }}" min="2" max="8">
                                    </td>
                                    <td>
                                        <span class="badge bg-white text-info border font-monospace px-2.5 py-1 fs-6 preview-display" id="exchange-preview">EXC-{{ date('Ymd') }}-0001</span>
                                    </td>
                                </tr>

                                {{-- Debit Note (SDN) --}}
                                <tr>
                                    <td class="fw-semibold text-dark">
                                        <i class="fas fa-file-shield text-warning me-2"></i>Debit Note (SDN)
                                    </td>
                                    <td>
                                        <input type="text" name="debit_note_prefix" class="form-control form-control-sm font-monospace prefix-input" data-target="debit-preview" value="{{ old('debit_note_prefix', $settings['debit_note_prefix'] ?? 'SDN-') }}" required>
                                    </td>
                                    <td>
                                        <select name="debit_date_format" class="form-select form-select-sm format-input" data-target="debit-preview">
                                            <option value="Ymd" {{ ($settings['debit_date_format'] ?? 'Ymd') === 'Ymd' ? 'selected' : '' }}>YYYYMMDD ({{ date('Ymd') }})</option>
                                            <option value="Ym" {{ ($settings['debit_date_format'] ?? '') === 'Ym' ? 'selected' : '' }}>YYYYMM ({{ date('Ym') }})</option>
                                            <option value="none" {{ ($settings['debit_date_format'] ?? '') === 'none' ? 'selected' : '' }}>No Date Stamp</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="debit_padding" class="form-control form-control-sm text-center padding-input" data-target="debit-preview" value="{{ old('debit_padding', $settings['debit_padding'] ?? 4) }}" min="2" max="8">
                                    </td>
                                    <td>
                                        <span class="badge bg-white text-warning-emphasis border font-monospace px-2.5 py-1 fs-6 preview-display" id="debit-preview">SDN-{{ date('Ymd') }}-0001</span>
                                    </td>
                                </tr>

                                {{-- Credit Note (SCN) --}}
                                <tr>
                                    <td class="fw-semibold text-dark">
                                        <i class="fas fa-file-shield text-danger me-2"></i>Credit Note (SCN)
                                    </td>
                                    <td>
                                        <input type="text" name="credit_note_prefix" class="form-control form-control-sm font-monospace prefix-input" data-target="credit-preview" value="{{ old('credit_note_prefix', $settings['credit_note_prefix'] ?? 'SCN-') }}" required>
                                    </td>
                                    <td>
                                        <select name="credit_date_format" class="form-select form-select-sm format-input" data-target="credit-preview">
                                            <option value="Ymd" {{ ($settings['credit_date_format'] ?? 'Ymd') === 'Ymd' ? 'selected' : '' }}>YYYYMMDD ({{ date('Ymd') }})</option>
                                            <option value="Ym" {{ ($settings['credit_date_format'] ?? '') === 'Ym' ? 'selected' : '' }}>YYYYMM ({{ date('Ym') }})</option>
                                            <option value="none" {{ ($settings['credit_date_format'] ?? '') === 'none' ? 'selected' : '' }}>No Date Stamp</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="credit_padding" class="form-control form-control-sm text-center padding-input" data-target="credit-preview" value="{{ old('credit_padding', $settings['credit_padding'] ?? 4) }}" min="2" max="8">
                                    </td>
                                    <td>
                                        <span class="badge bg-white text-danger border font-monospace px-2.5 py-1 fs-6 preview-display" id="credit-preview">SCN-{{ date('Ymd') }}-0001</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- TAB 2: Approvals & 3-Way Matching Tolerance --}}
                <div class="tab-pane fade p-4" id="approval-pane" role="tabpanel" tabindex="0">
                    <div class="mb-3">
                        <h6 class="section-heading">Approval Controls &amp; 3-Way Matching</h6>
                        <p class="section-description mb-0">Configure procurement validation, variance thresholds and approval requirements.</p>
                    </div>

                    <div class="row g-3">
                        {{-- 3-Way Matching Requirement --}}
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded bg-white h-100">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" role="switch" name="enforce_3way_matching" id="enforce_3way_matching" value="1" {{ old('enforce_3way_matching', $settings['enforce_3way_matching'] ?? 1) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-dark" for="enforce_3way_matching">
                                        Enforce Mandatory 3-Way Matching (PO &rarr; GRN &rarr; Bill)
                                    </label>
                                </div>
                                <p class="small text-muted mb-0">
                                    When active, purchase bills cannot be confirmed without linking an approved Goods Received Note (GRN) and its parent Purchase Order (PO).
                                </p>
                            </div>
                        </div>

                        {{-- Quantity Variance Tolerance --}}
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded bg-white h-100">
                                <label class="form-label fw-bold text-dark small mb-1">Quantity Variance Limit (%)</label>
                                <div class="input-group input-group-sm mb-2" style="max-width: 200px;">
                                    <input type="number" step="0.1" name="qty_variance_tolerance_pct" class="form-control" value="{{ old('qty_variance_tolerance_pct', $settings['qty_variance_tolerance_pct'] ?? 5.0) }}" min="0" max="100">
                                    <span class="input-group-text">%</span>
                                </div>
                                <p class="small text-muted mb-0">
                                    Maximum surplus percentage allowed during inward GRN receipt over original order volume before manager approval is mandated.
                                </p>
                            </div>
                        </div>

                        {{-- Price Discrepancy Tolerance --}}
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded bg-white h-100">
                                <label class="form-label fw-bold text-dark small mb-1">Price Variance Limit (%)</label>
                                <div class="input-group input-group-sm mb-2" style="max-width: 200px;">
                                    <input type="number" step="0.1" name="price_variance_tolerance_pct" class="form-control" value="{{ old('price_variance_tolerance_pct', $settings['price_variance_tolerance_pct'] ?? 0.0) }}" min="0" max="100">
                                    <span class="input-group-text">%</span>
                                </div>
                                <p class="small text-muted mb-0">
                                    Maximum price rise allowed on vendor bills compared to initial agreed PO unit cost before triggering an alert.
                                </p>
                            </div>
                        </div>

                        {{-- Managerial Approval Ceiling --}}
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded bg-white h-100">
                                <label class="form-label fw-bold text-dark small mb-1">Director Approval Threshold (৳)</label>
                                <div class="input-group input-group-sm mb-2" style="max-width: 240px;">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" step="1000" name="director_approval_threshold" class="form-control text-end fw-semibold" value="{{ old('director_approval_threshold', $settings['director_approval_threshold'] ?? 100000) }}" min="0">
                                </div>
                                <p class="small text-muted mb-0">
                                    Purchase orders exceeding this gross commitment amount require dual-level executive sign-off before dispatch to vendors.
                                </p>
                            </div>
                        </div>

                        {{-- Auto-Hold on Overdue Credit Limit --}}
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded bg-white h-100">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" role="switch" name="block_po_on_credit_limit" id="block_po_on_credit_limit" value="1" {{ old('block_po_on_credit_limit', $settings['block_po_on_credit_limit'] ?? 1) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-dark" for="block_po_on_credit_limit">
                                        Block POs on Vendor Credit Limit Breaches
                                    </label>
                                </div>
                                <p class="small text-muted mb-0">
                                    Prevents creating new procurement commitments for suppliers who have exceeded agreed liability ceilings until dues are cleared.
                                </p>
                            </div>
                        </div>

                        {{-- Default QC Inspection Workflow --}}
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded bg-white h-100">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" role="switch" name="require_qc_quarantine" id="require_qc_quarantine" value="1" {{ old('require_qc_quarantine', $settings['require_qc_quarantine'] ?? 0) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-dark" for="require_qc_quarantine">
                                        Enable QC Quarantine for Inward Deliveries
                                    </label>
                                </div>
                                <p class="small text-muted mb-0">
                                    If enabled, GRNs are saved in <em>Pending QC</em> state by default, preventing immediate stock availability for sales until inspected.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TAB 3: Default General Ledger Mapping --}}
                <div class="tab-pane fade p-4" id="gl-pane" role="tabpanel" tabindex="0">
                    <div class="mb-3">
                        <h6 class="section-heading">Default General Ledger Mapping</h6>
                        <p class="section-description mb-0">Select default Chart of Accounts for automated procurement journal entries.</p>
                    </div>

                    <div class="row g-3">
                        {{-- Default Accounts Payable (Trade Creditors) --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small">Default Accounts Payable (Trade Creditors) <span class="text-danger">*</span></label>
                            <select name="gl_accounts_payable_id" class="form-select form-select-sm" required>
                                <option value="">Select General Ledger Head</option>
                                @foreach($accounts ?? [] as $acc)
                                    <option value="{{ $acc->id }}" {{ old('gl_accounts_payable_id', $settings['gl_accounts_payable_id'] ?? '') == $acc->id ? 'selected' : '' }}>
                                        {{ $acc->code ? $acc->code . ' - ' : '' }}{{ $acc->name }} (Current Liability)
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Primary liability head credited upon purchase bill confirmation.</small>
                        </div>

                        {{-- Default Inventory Inward Asset --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small">Default Inventory Control / Stock Asset <span class="text-danger">*</span></label>
                            <select name="gl_inventory_asset_id" class="form-select form-select-sm" required>
                                <option value="">Select General Ledger Head</option>
                                @foreach($accounts ?? [] as $acc)
                                    <option value="{{ $acc->id }}" {{ old('gl_inventory_asset_id', $settings['gl_inventory_asset_id'] ?? '') == $acc->id ? 'selected' : '' }}>
                                        {{ $acc->code ? $acc->code . ' - ' : '' }}{{ $acc->name }} (Current Asset)
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Inventory asset head debited when goods are accepted into the warehouse.</small>
                        </div>

                        {{-- Default Procurement Expense / COGS --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small">Direct Procurement / Consumables Expense <span class="text-danger">*</span></label>
                            <select name="gl_purchase_expense_id" class="form-select form-select-sm" required>
                                <option value="">Select General Ledger Head</option>
                                @foreach($accounts ?? [] as $acc)
                                    <option value="{{ $acc->id }}" {{ old('gl_purchase_expense_id', $settings['gl_purchase_expense_id'] ?? '') == $acc->id ? 'selected' : '' }}>
                                        {{ $acc->code ? $acc->code . ' - ' : '' }}{{ $acc->name }} (Cost of Goods Sold / Expense)
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Applied when billing non-inventory expenses or direct procurement services.</small>
                        </div>

                        {{-- Default Purchase Returns & Allowances --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small">Purchase Returns &amp; Rebates Account <span class="text-danger">*</span></label>
                            <select name="gl_purchase_return_id" class="form-select form-select-sm" required>
                                <option value="">Select General Ledger Head</option>
                                @foreach($accounts ?? [] as $acc)
                                    <option value="{{ $acc->id }}" {{ old('gl_purchase_return_id', $settings['gl_purchase_return_id'] ?? '') == $acc->id ? 'selected' : '' }}>
                                        {{ $acc->code ? $acc->code . ' - ' : '' }}{{ $acc->name }} (Contra-Expense / Cost Recovery)
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Credited when Return to Vendor (RTV) vouchers or Debit Notes are approved.</small>
                        </div>

                        {{-- Input VAT / Tax Claim Account --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small">Input VAT / Tax Receivable Account</label>
                            <select name="gl_input_tax_id" class="form-select form-select-sm">
                                <option value="">Select General Ledger Head</option>
                                @foreach($accounts ?? [] as $acc)
                                    <option value="{{ $acc->id }}" {{ old('gl_input_tax_id', $settings['gl_input_tax_id'] ?? '') == $acc->id ? 'selected' : '' }}>
                                        {{ $acc->code ? $acc->code . ' - ' : '' }}{{ $acc->name }} (Tax Asset / Current Asset)
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Debited for reimbursable purchase VAT and advance tax deductions.</small>
                        </div>

                        {{-- Freight & Carriage Inward Account --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small">Freight &amp; Carriage Inward Account</label>
                            <select name="gl_freight_inward_id" class="form-select form-select-sm">
                                <option value="">Select General Ledger Head</option>
                                @foreach($accounts ?? [] as $acc)
                                    <option value="{{ $acc->id }}" {{ old('gl_freight_inward_id', $settings['gl_freight_inward_id'] ?? '') == $acc->id ? 'selected' : '' }}>
                                        {{ $acc->code ? $acc->code . ' - ' : '' }}{{ $acc->name }} (Direct Expense)
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Captures inward transportation and port charges added to purchase bills.</small>
                        </div>
                    </div>
                </div>

                {{-- TAB 4: Default Terms & Print Boilerplate --}}
                <div class="tab-pane fade p-4" id="terms-pane" role="tabpanel" tabindex="0">
                    <div class="mb-3">
                        <h6 class="section-heading">Purchase Terms &amp; Print Settings</h6>
                        <p class="section-description mb-0">Configure standard terms, legal disclaimers and signatory titles for printed orders.</p>
                    </div>

                    <div class="row g-3">
                        {{-- Default Payment Terms Note --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small">Standard PO Payment Terms</label>
                            <textarea name="default_payment_terms_note" rows="3" class="form-control form-control-sm" placeholder="100% payment within 30 days following QC acceptance and original commercial invoice submission.">{{ old('default_payment_terms_note', $settings['default_payment_terms_note'] ?? "Payment will be disbursed via BEFTN/Account Payee Cheque within agreed credit terms upon successful delivery and warehouse QC approval.") }}</textarea>
                        </div>

                        {{-- Default Delivery & Inspection Terms --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small">Inspection, Rejection &amp; Return Terms</label>
                            <textarea name="default_inspection_terms_note" rows="3" class="form-control form-control-sm" placeholder="Defective or off-spec items are subject to immediate return at supplier freight expense.">{{ old('default_inspection_terms_note', $settings['default_inspection_terms_note'] ?? "All goods are subject to physical inspection at the receiving warehouse. Rejected lots must be replaced by the vendor within 7 business days.") }}</textarea>
                        </div>

                        {{-- Standard Disclaimer Footer --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Purchase Order Footer Disclaimer</label>
                            <input type="text" name="po_footer_disclaimer" class="form-control form-control-sm" value="{{ old('po_footer_disclaimer', $settings['po_footer_disclaimer'] ?? 'This is a computer-generated Purchase Order. Any alterations or price discrepancies must be confirmed in writing prior to shipment dispatch.') }}">
                        </div>

                        {{-- Signatory Labels --}}
                        <div class="col-6 col-md-4">
                            <label class="form-label fw-semibold small">Left Signatory Title</label>
                            <input type="text" name="signatory_left_title" class="form-control form-control-sm" value="{{ old('signatory_left_title', $settings['signatory_left_title'] ?? 'Prepared By (Procurement Officer)') }}">
                        </div>

                        <div class="col-6 col-md-4">
                            <label class="form-label fw-semibold small">Center Signatory Title</label>
                            <input type="text" name="signatory_center_title" class="form-control form-control-sm" value="{{ old('signatory_center_title', $settings['signatory_center_title'] ?? 'Verified By (Accounts / Audit)') }}">
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold small">Right Signatory Title</label>
                            <input type="text" name="signatory_right_title" class="form-control form-control-sm" value="{{ old('signatory_right_title', $settings['signatory_right_title'] ?? 'Authorized Signatory (Managing Director)') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer Submission Toolbar --}}
            <div class="card-footer bg-white border-top py-3 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-sm btn-secondary" onclick="window.location.reload()">Cancel</button>
                <button type="submit" class="btn btn-sm btn-primary px-4">
                    <i class="fas fa-check-circle me-1"></i> Save Configuration
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Dynamic Numbering Preview Updater
    const prefixInputs = document.querySelectorAll('.prefix-input');
    const formatInputs = document.querySelectorAll('.format-input');
    const paddingInputs = document.querySelectorAll('.padding-input');

    function updatePreview(targetId) {
        const previewElement = document.getElementById(targetId);
        if (!previewElement) return;

        const row = previewElement.closest('tr');
        const prefix = row.querySelector('.prefix-input').value || '';
        const format = row.querySelector('.format-input').value;
        const padding = parseInt(row.querySelector('.padding-input').value, 10) || 4;

        let dateStr = '';
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');

        if (format === 'Ymd') {
            dateStr = `${year}${month}${day}-`;
        } else if (format === 'Ym') {
            dateStr = `${year}${month}-`;
        }

        const serialNumber = '1'.padStart(padding, '0');
        previewElement.innerText = `${prefix}${dateStr}${serialNumber}`;
    }

    [...prefixInputs, ...formatInputs, ...paddingInputs].forEach(input => {
        input.addEventListener('input', function () {
            updatePreview(this.dataset.target);
        });
        input.addEventListener('change', function () {
            updatePreview(this.dataset.target);
        });
    });

    // Tab active border toggle styling
    const tabs = document.querySelectorAll('#settingsTab button');
    tabs.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function () {
            tabs.forEach(t => {
                t.classList.remove('border-bottom', 'border-primary', 'border-3', 'text-dark');
                t.classList.add('text-secondary');
            });
            this.classList.add('border-bottom', 'border-primary', 'border-3', 'text-dark');
            this.classList.remove('text-secondary');
        });
    });
});
</script>
@endsection
