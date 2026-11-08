@extends('layouts.admin')

@section('title', 'Tax & VAT Settings')

@section('admin_content')
<div class="container-fluid px-2 px-md-3 py-2 tax-settings-wrapper">
<!-- Header Section -->
<div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3 mb-3">
    <div class="d-flex align-items-center gap-3 min-w-0">
        <span class="d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background: #e0f2fe; color: #0284c7; border-radius: 8px; font-size: 16px;">
            <i class="fas fa-sliders-h"></i>
        </span>
        <div class="min-w-0 lh-sm">
            <h4 class="fw-bold mb-1 text-dark text-truncate" style="font-size: 16px;">
                Tax &amp; VAT Settings
            </h4>
            <div class="text-secondary text-truncate m-0 p-0" style="font-size: 11.5px;">
                VAT Activation, Rates &amp; Priority Settings
            </div>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2 flex-wrap flex-lg-nowrap justify-content-start justify-content-lg-end page-actions w-100 w-lg-auto">
        <a href="{{ route('admin.tax.dashboard') }}"
           class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center justify-content-center gap-1 flex-fill flex-lg-grow-0 text-nowrap" style="height: 34px;">
            <i class="fas fa-arrow-left"></i>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('admin.tax.rules.index') }}"
           class="btn btn-sm btn-outline-primary d-inline-flex align-items-center justify-content-center gap-1 flex-fill flex-lg-grow-0 text-nowrap" style="height: 34px;">
            <i class="fas fa-balance-scale"></i>
            <span>Tax Rules</span>
        </a>
    </div>
</div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-3 py-2 px-3" role="alert">
            <div class="d-flex align-items-center small">
                <i class="fas fa-check-circle me-2 fs-6"></i>
                <div class="fw-medium">{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-3 py-2 px-3" role="alert">
            <div class="d-flex align-items-center small">
                <i class="fas fa-exclamation-circle me-2 fs-6"></i>
                <div class="fw-medium">{{ session('error') }}</div>
            </div>
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-3 py-2 px-3" role="alert">
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="fas fa-triangle-exclamation text-danger"></i>
                <span class="fw-semibold small">Please resolve the following errors:</span>
            </div>

            <ul class="mb-0 ps-3 small text-secondary">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>

            <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <!-- Main Settings Form -->
    <form action="{{ route('admin.tax.settings.update') }}" method="POST" id="taxSettingsForm">
        @csrf

        <div class="row g-2 g-md-3">

            <!-- Left Column: Primary VAT Configurations -->
            <div class="col-12 col-lg-7">

                <!-- VAT Activation & Rates Card -->
                <div class="card tax-settings-card mb-2 mb-md-3">
                    <div class="card-header bg-white border-0 pt-3 px-3 pb-1">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="fas fa-calculator text-primary"></i>
                            General VAT &amp; Rates
                        </h6>
                    </div>

                    <div class="card-body p-3">
                        <div class="row g-2 g-md-3">

                           <div class="col-12">
    <div class="vat-master-switch p-3 border rounded-3 shadow-sm"
         id="vatSwitchCard"
         style="background: {{ !empty($settings['vat_enabled']) ? '#f0fdf4' : '#fff1f2' }}; border-color: {{ !empty($settings['vat_enabled']) ? '#bbf7d0' : '#fecdd3' }} !important; transition: all 0.2s ease;">

        <div class="form-check form-switch m-0 d-flex align-items-center gap-3">
            <input class="form-check-input ms-0 mt-0 flex-shrink-0"
                   type="checkbox"
                   role="switch"
                   name="vat_enabled"
                   value="1"
                   id="vatEnabledSwitch"
                   style="width: 2.5em; height: 1.25em; cursor: pointer;"
                   {{ !empty($settings['vat_enabled']) ? 'checked' : '' }}
                   onchange="handleVatSwitchToggle(this)">

            <div class="flex-grow-1">
                <label class="form-check-label fw-bold text-dark mb-1 d-block"
                       for="vatEnabledSwitch"
                       id="vatSwitchLabel"
                       style="font-size: 14.5px; cursor: pointer;">
                    {{ !empty($settings['vat_enabled']) ? 'VAT System: Active' : 'VAT System: Disabled' }}
                </label>

                <span class="text-secondary d-block vat-switch-help"
                      id="vatSwitchHelp"
                      style="font-size: 12px; line-height: 1.4;">
                    {{ !empty($settings['vat_enabled']) ? 'VAT status is enabled.' : 'The VAT Status is currently disabled.' }}
                </span>
            </div>
        </div>
    </div>
</div>

                            <!-- VAT Label -->
                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold text-dark small mb-1">
                                    VAT Label <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="vat_label"
                                       class="form-control form-control-sm"
                                       value="{{ old('vat_label', $settings['vat_label'] ?? 'VAT') }}"
                                       placeholder="VAT or Govt. VAT"
                                       required>

                            </div>

                            <!-- Default VAT Rate -->
                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold text-dark small mb-1">
                                    Default Rate (%) <span class="text-danger">*</span>
                                </label>

                                <div class="input-group input-group-sm">
                                    <input type="number"
                                           step="0.01"
                                           min="0"
                                           max="100"
                                           name="default_vat_rate"
                                           class="form-control font-monospace"
                                           value="{{ old('default_vat_rate', $settings['default_vat_rate'] ?? 5.00) }}"
                                           required>

                                    <span class="input-group-text bg-light text-secondary">%</span>
                                </div>

                                <span class="text-secondary d-block mt-1 field-help">
                                    Used when no specific product rate is configured.
                                </span>
                            </div>

                            <!-- Calculation Mode -->
                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold text-dark small mb-1">
                                    Calculation Method <span class="text-danger">*</span>
                                </label>

                                <select name="default_tax_calculation_type"
                                        class="form-select form-select-sm"
                                        required>
                                    <option value="exclusive" {{ (old('default_tax_calculation_type', $settings['default_tax_calculation_type'] ?? '') === 'exclusive') ? 'selected' : '' }}>
                                        Exclusive (Added to product price)
                                    </option>

                                    <option value="inclusive" {{ (old('default_tax_calculation_type', $settings['default_tax_calculation_type'] ?? '') === 'inclusive') ? 'selected' : '' }}>
                                        Inclusive (Included in product price)
                                    </option>
                                </select>
                            </div>

                            <!-- Delivery VAT Switch -->
                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold text-dark small mb-1 d-block">
                                    Shipping Charges
                                </label>

                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="apply_vat_on_shipping"
                                           value="1"
                                           id="applyShippingVat"
                                           {{ !empty($settings['apply_vat_on_shipping']) ? 'checked' : '' }}>

                                    <label class="form-check-label text-dark small fw-medium"
                                           for="applyShippingVat">
                                        Apply VAT to delivery charges
                                    </label>
                                </div>

                                <span class="text-secondary d-block mt-1 field-help">
                                    When enabled, VAT will also be applied to courier charges.
                                </span>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Business Details & BIN Card -->
                <div class="card tax-settings-card mb-2 mb-md-3">
                    <div class="card-header bg-white border-0 pt-3 px-3 pb-1">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="fas fa-building text-primary"></i>
                            Business &amp; Registration Details
                        </h6>
                    </div>

                    <div class="card-body p-3">
                        <div class="row g-2 g-md-3">

                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold text-dark small mb-1">
                                    Company BIN
                                </label>

                                <input type="text"
                                       name="company_bin"
                                       class="form-control form-control-sm font-monospace text-uppercase"
                                       value="{{ old('company_bin', $settings['company_bin'] ?? '') }}"
                                       placeholder="001293847-0101">
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold text-dark small mb-1">
                                    Registered Entity Name
                                </label>

                                <input type="text"
                                       name="vat_registration_name"
                                       class="form-control form-control-sm"
                                       value="{{ old('vat_registration_name', $settings['vat_registration_name'] ?? '') }}"
                                       placeholder="Shop Now BD Online">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold text-dark small mb-1">
                                    Tax Area
                                </label>

                                <input type="text"
                                       name="vat_circle_or_division"
                                       class="form-control form-control-sm"
                                       value="{{ old('vat_circle_or_division', $settings['vat_circle_or_division'] ?? '') }}"
                                       placeholder="Circle-14, Taxes Zone-08, Dhaka">
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Display, Automation & POS Rules -->
            <div class="col-12 col-lg-5">

                <!-- Display & POS Card -->
                <div class="card tax-settings-card mb-2 mb-md-3">
                    <div class="card-header bg-white border-0 pt-3 px-3 pb-1">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="fas fa-desktop text-primary"></i>
                            POS &amp; Display Rules
                        </h6>
                    </div>

                    <div class="card-body p-3">
                        <div class="row g-2 g-md-3">

                            <div class="col-12">
                                <div class="form-check form-switch setting-switch-row">
                                    <input class="form-check-input ms-0 me-2"
                                           type="checkbox"
                                           name="display_vat_breakdown_on_checkout"
                                           value="1"
                                           id="checkoutBreakdownSwitch"
                                           {{ !empty($settings['display_vat_breakdown_on_checkout']) ? 'checked' : '' }}>

                                    <label class="form-check-label fw-semibold text-dark small"
                                           for="checkoutBreakdownSwitch">
                                        Show VAT breakdown at checkout
                                    </label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch setting-switch-row">
                                    <input class="form-check-input ms-0 me-2"
                                           type="checkbox"
                                           name="enable_pos_vat"
                                           value="1"
                                           id="posVatSwitch"
                                           {{ !empty($settings['enable_pos_vat']) ? 'checked' : '' }}>

                                    <label class="form-check-label fw-semibold text-dark small"
                                           for="posVatSwitch">
                                        Enable VAT on POS terminals
                                    </label>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold text-dark small mb-1">
                                    POS Receipt Format <span class="text-danger">*</span>
                                </label>

                                <select name="pos_vat_display_mode"
                                        class="form-select form-select-sm"
                                        required>
                                    <option value="item_breakdown" {{ (old('pos_vat_display_mode', $settings['pos_vat_display_mode'] ?? '') === 'item_breakdown') ? 'selected' : '' }}>
                                        Item-wise Details (VAT per item)
                                    </option>

                                    <option value="summary_only" {{ (old('pos_vat_display_mode', $settings['pos_vat_display_mode'] ?? '') === 'summary_only') ? 'selected' : '' }}>
                                        Summary Total Only
                                    </option>

                                    <option value="hidden" {{ (old('pos_vat_display_mode', $settings['pos_vat_display_mode'] ?? '') === 'hidden') ? 'selected' : '' }}>
                                        Hide VAT on Receipt
                                    </option>
                                </select>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Rounding & Ledger Locking Card -->
                <div class="card tax-settings-card mb-2 mb-md-3">
                    <div class="card-header bg-white border-0 pt-3 px-3 pb-1">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="fas fa-lock text-primary"></i>
                            Rounding &amp; Period Locking
                        </h6>
                    </div>

                    <div class="card-body p-3">
                        <div class="row g-2 g-md-3">

                            <div class="col-12">
                                <div class="form-check form-switch setting-switch-row">
                                    <input class="form-check-input ms-0 me-2"
                                           type="checkbox"
                                           name="enable_round_off_vat"
                                           value="1"
                                           id="roundOffVatSwitch"
                                           {{ !empty($settings['enable_round_off_vat']) ? 'checked' : '' }}>

                                    <label class="form-check-label fw-semibold text-dark small"
                                           for="roundOffVatSwitch">
                                        Round tax amounts to 2 decimal places
                                    </label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch setting-switch-row mb-1">
                                    <input class="form-check-input ms-0 me-2"
                                           type="checkbox"
                                           name="auto_lock_tax_periods"
                                           value="1"
                                           id="autoLockPeriodSwitch"
                                           {{ !empty($settings['auto_lock_tax_periods']) ? 'checked' : '' }}>

                                    <label class="form-check-label fw-semibold text-dark small"
                                           for="autoLockPeriodSwitch">
                                        Lock previous tax ledgers at month-end
                                    </label>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Action Button Card -->
                <div class="card tax-settings-action-card">
                    <div class="card-body p-3">
                        <button type="submit"
                                class="btn btn-primary w-100 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                            <i class="fas fa-save"></i>
                            <span>Save Settings</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<style>
    /* =========================================================
       TAX SETTINGS - KAIADMIN LITE CLASSIC / WHITE CLASSIC
    ========================================================= */

    .tax-settings-wrapper {
        --tax-primary: var(--bs-primary, #1572e8);
        --tax-border: #e8edf3;
        --tax-heading: #344054;
        --tax-muted: #6c757d;
        --tax-surface: #ffffff;

        color: var(--tax-heading);
        min-width: 0;
    }

    /* Card Design */
    .tax-settings-wrapper .card {
        background: var(--tax-surface);
        border: 1px solid var(--tax-border) !important;
        border-radius: 5px !important;
        box-shadow: 0 1px 3px rgba(16, 24, 40, 0.05) !important;
        overflow: hidden;
    }

    .tax-settings-wrapper .card-header {
        background: #fff !important;
    }

    .tax-settings-wrapper .card-header h6 {
        color: var(--tax-heading);
        letter-spacing: -0.01em;
    }

    /* Hierarchy Information Card */
    .tax-settings-wrapper .tax-hierarchy-card {
        background: #f5f9ff !important;
        border-color: #d9e7fb !important;
        box-shadow: none !important;
    }

    .tax-settings-wrapper .hierarchy-grid {
        font-size: 12px;
        line-height: 1.55;
    }

    .tax-settings-wrapper .hierarchy-item {
        position: relative;
        padding: 14px 12px 12px;
        background: #fff;
        border: 1px solid #e5edf8;
        border-radius: 5px;
    }

    .tax-settings-wrapper .hierarchy-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 27px;
        height: 24px;
        margin-bottom: 9px;
        border-radius: 4px;
        background: #edf4ff;
        color: var(--tax-primary);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.04em;
    }

    /* Master VAT Switch */
    .tax-settings-wrapper .vat-master-switch {
        border: 1px solid;
        border-radius: 5px;
        padding: 12px;
    }

    .tax-settings-wrapper .vat-master-switch .form-check-input {
        width: 2.4em;
        height: 1.25em;
        cursor: pointer;
    }

    .tax-settings-wrapper .vat-master-switch .form-check-label {
        cursor: pointer;
    }

    .tax-settings-wrapper .vat-switch-help {
        font-size: 11px;
        line-height: 1.5;
        margin-top: 3px;
    }

    /* Form Controls */
    .tax-settings-wrapper .form-control,
    .tax-settings-wrapper .form-select,
    .tax-settings-wrapper .input-group-text {
        min-height: 35px;
        border-radius: 5px !important;
        border-color: #dfe4ea;
        font-size: 12.5px;
    }

    .tax-settings-wrapper .form-control::placeholder {
        color: #98a2b3;
        opacity: 1;
    }

    .tax-settings-wrapper .form-control:focus,
    .tax-settings-wrapper .form-select:focus {
        border-color: var(--tax-primary);
        box-shadow: 0 0 0 0.15rem rgba(21, 114, 232, 0.12);
    }

    .tax-settings-wrapper .input-group-text {
        color: #667085;
    }

    .tax-settings-wrapper .form-label {
        color: var(--tax-heading);
        font-size: 12px;
    }

    .tax-settings-wrapper .field-help {
        font-size: 11px;
        line-height: 1.5;
    }

    /* Setting Switch Rows */
    .tax-settings-wrapper .setting-switch-row {
        display: flex;
        align-items: center;
        min-height: 42px;
        padding: 9px 10px;
        background: #f8fafc;
        border: 1px solid #e8edf3;
        border-radius: 5px;
        transition: background-color 0.18s ease, border-color 0.18s ease;
    }

    .tax-settings-wrapper .setting-switch-row:hover {
        background: #f5f8fc;
        border-color: #d9e2ec;
    }

    .tax-settings-wrapper .setting-switch-row .form-check-input {
        margin-top: 0;
        flex-shrink: 0;
    }

    .tax-settings-wrapper .setting-switch-row .form-check-label {
        line-height: 1.5;
        cursor: pointer;
    }

    /* Switch Styling */
    .tax-settings-wrapper .form-check-input {
        cursor: pointer;
        border-color: #cbd5e1;
        box-shadow: none;
    }

    .tax-settings-wrapper .form-check-input:focus {
        border-color: var(--tax-primary);
        box-shadow: 0 0 0 0.15rem rgba(21, 114, 232, 0.12);
    }

    .tax-settings-wrapper .form-check-input:checked {
        background-color: #198754;
        border-color: #198754;
    }

    /* Buttons */
    .tax-settings-wrapper .btn {
        border-radius: 5px !important;
        font-size: 12.5px;
        font-weight: 500;
        transition: background-color 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
    }

    .tax-settings-wrapper .btn-primary {
        box-shadow: 0 1px 2px rgba(21, 114, 232, 0.12);
    }

    .tax-settings-wrapper .btn-primary:hover {
        box-shadow: 0 2px 5px rgba(21, 114, 232, 0.18);
    }

    .tax-settings-wrapper .tax-settings-action-card .btn {
        min-height: 42px;
        font-size: 13px;
    }

    /* Alerts */
    .tax-settings-wrapper .alert {
        border-radius: 5px !important;
        font-size: 12.5px;
    }

    /* Responsive Layout */
    @media (max-width: 767.98px) {
        .tax-settings-wrapper .page-actions {
            width: 100%;
        }

        .tax-settings-wrapper .page-actions .btn {
            flex: 1;
            justify-content: center;
            min-height: 34px;
        }

        .tax-settings-wrapper .hierarchy-item {
            padding: 12px;
        }
    }

    @media (max-width: 575.98px) {
        .tax-settings-wrapper {
            padding-left: 6px !important;
            padding-right: 6px !important;
        }

        .tax-settings-wrapper .card-body {
            padding: 0.75rem !important;
        }

        .tax-settings-wrapper .tax-hierarchy-card .card-body {
            padding: 12px !important;
        }

        .tax-settings-wrapper .form-label {
            font-size: 0.8rem !important;
        }

        .tax-settings-wrapper .form-check-label {
            font-size: 0.8rem !important;
            word-break: break-word;
        }

        .tax-settings-wrapper .field-help {
            font-size: 10.5px;
        }

        .tax-settings-wrapper .hierarchy-grid {
            font-size: 11.5px;
        }

        .tax-settings-wrapper .vat-master-switch {
            padding: 10px;
        }
    }
</style>

<script>
    function handleVatSwitchToggle(el) {
        const card = document.getElementById('vatSwitchCard');
        const label = document.getElementById('vatSwitchLabel');
        const help = document.getElementById('vatSwitchHelp');

        if (el.checked) {
            card.style.background = '#f0fdf4';
            card.style.borderColor = '#bbf7d0';
            label.innerText = 'VAT System: Active';
            help.innerText = 'VAT status is enabled.';
        } else {
            card.style.background = '#fff1f2';
            card.style.borderColor = '#fecdd3';
            label.innerText = 'VAT System: Disabled';
            help.innerText = 'The VAT Status is currently disabled.';
        }
    }
</script>
@endsection