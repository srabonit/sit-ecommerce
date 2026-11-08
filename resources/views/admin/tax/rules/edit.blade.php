@extends('layouts.admin')

@section('title', 'Edit Tax Rule')

@section('admin_content')
@php
    $existingConditionsData = ($taxRule->conditions ?? collect())->map(function ($c) {
        $label = '';
        if ($c->condition_type === 'product' && !empty($c->condition_id)) {
            $label = \Illuminate\Support\Facades\DB::table('products')->where('id', $c->condition_id)->value('title') ?? ('Product #' . $c->condition_id);
        } elseif ($c->condition_type === 'category' && !empty($c->condition_id)) {
            $label = \Illuminate\Support\Facades\DB::table('categories')->where('id', $c->condition_id)->value('name') ?? ('Category #' . $c->condition_id);
        } elseif ($c->condition_type === 'brand' && !empty($c->condition_id)) {
            $label = \Illuminate\Support\Facades\DB::table('brands')->where('id', $c->condition_id)->value('name') ?? ('Brand #' . $c->condition_id);
        }

        return [
            'type'        => $c->condition_type,
            'id'          => $c->condition_id,
            'value'       => $c->condition_value,
            'is_excluded' => (bool) $c->is_excluded,
            'label'       => $label ?: ($c->target_label ?? ''),
        ];
    });
@endphp

<div class="container-fluid px-2 px-md-3 py-3 tax-rule-page">

    {{-- Page Header --}}
    <div class="tax-rule-header d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-3">
        <div class="min-w-0">
            <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i class="fas fa-edit text-primary"></i>
                    <span>Edit Tax Rule</span>
                </h4>
                <span class="badge bg-primary-subtle text-primary">
                    Rule #{{ $taxRule->id }}
                </span>
            </div>
            <p class="text-secondary small mb-0">
                Update tax rates, scope conditions, priority and validity settings.
            </p>
            <div class="text-secondary small mt-1">
                <i class="fas fa-tag me-1"></i>
                {{ $taxRule->name }}
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 w-100 w-md-auto page-actions">
            <a href="{{ route('admin.tax.rules.index') }}"
               class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center justify-content-center gap-2">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Rules</span>
            </a>

            <a href="{{ route('admin.tax.rules.create') }}"
               class="btn btn-sm btn-primary d-inline-flex align-items-center justify-content-center gap-2">
                <i class="fas fa-plus"></i>
                <span>Create Another</span>
            </a>
        </div>
    </div>

    {{-- Validation Feedback --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="fas fa-triangle-exclamation"></i>
                <strong class="small">Please resolve the following errors:</strong>
            </div>

            <ul class="mb-0 ps-4 small">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('admin.tax.rules.update', $taxRule->id) }}" method="POST" id="editTaxRuleForm">
        @csrf
        @method('PUT')

        <div class="row g-3">

            {{-- Left Column --}}
            <div class="col-12 col-lg-7">

                {{-- General Information --}}
                <div class="card tax-rule-card mb-3">
                    <div class="card-header">
                        <div class="d-flex align-items-center gap-2">
                            <span class="tax-rule-section-icon">
                                <i class="fas fa-sliders"></i>
                            </span>
                            <div>
                                <h6 class="mb-0 fw-bold">General Information</h6>
                                <small class="text-secondary">Define the basic identity of this tax rule.</small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-12 col-sm-6">
                                <label class="form-label">Rule Name <span class="text-danger">*</span></label>
                                <input type="text"
                                       name="name"
                                       class="form-control"
                                       id="ruleNameInput"
                                       placeholder="Standard 5% Apparel VAT"
                                       value="{{ old('name', $taxRule->name) }}"
                                       required>
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label">Rule Code <span class="text-danger">*</span></label>
                                <input type="text"
                                       name="code"
                                       class="form-control font-monospace text-uppercase"
                                       id="ruleCodeInput"
                                       placeholder="VAT_APPAREL_5"
                                       value="{{ old('code', $taxRule->code) }}"
                                       required>
                                <small class="form-text">Unique identifier for this tax rule.</small>
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label">Tax Class</label>
                                <select name="tax_class_id" class="form-select">
                                    <option value="">None (Standalone Rule)</option>
                                    @foreach($taxClasses as $tc)
                                        <option value="{{ $tc->id }}" {{ old('tax_class_id', $taxRule->tax_class_id) == $tc->id ? 'selected' : '' }}>
                                            {{ $tc->name }} ({{ $tc->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label">Tax Status <span class="text-danger">*</span></label>
                                <select name="status_type" class="form-select" id="statusTypeSelect" required>
                                    <option value="taxable" {{ old('status_type', $taxRule->status_type) === 'taxable' ? 'selected' : '' }}>Taxable</option>
                                    <option value="exempt" {{ old('status_type', $taxRule->status_type) === 'exempt' ? 'selected' : '' }}>Exempt</option>
                                    <option value="zero_rated" {{ old('status_type', $taxRule->status_type) === 'zero_rated' ? 'selected' : '' }}>Zero-Rated (0% Tax)</option>
                                </select>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Tax Calculation --}}
                <div class="card tax-rule-card mb-3">
                    <div class="card-header">
                        <div class="d-flex align-items-center gap-2">
                            <span class="tax-rule-section-icon">
                                <i class="fas fa-calculator"></i>
                            </span>
                            <div>
                                <h6 class="mb-0 fw-bold">Tax Calculation</h6>
                                <small class="text-secondary">Configure calculation method, rate and taxable limits.</small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-12 col-sm-6">
                                <label class="form-label">Calculation Mode <span class="text-danger">*</span></label>
                                <select name="calculation_type" class="form-select" required>
                                    <option value="exclusive" {{ old('calculation_type', $taxRule->calculation_type) === 'exclusive' ? 'selected' : '' }}>
                                        Exclusive (Added on top of price)
                                    </option>
                                    <option value="inclusive" {{ old('calculation_type', $taxRule->calculation_type) === 'inclusive' ? 'selected' : '' }}>
                                        Inclusive (Included in retail price)
                                    </option>
                                </select>
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label">Tax Method <span class="text-danger">*</span></label>
                                <select name="tax_type" class="form-select" id="taxTypeSelect" required>
                                    <option value="percentage" {{ old('tax_type', $taxRule->tax_type) === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                    <option value="fixed" {{ old('tax_type', $taxRule->tax_type) === 'fixed' ? 'selected' : '' }}>Fixed Amount (BDT)</option>
                                </select>
                            </div>

                            <div class="col-12 col-sm-6 {{ old('tax_type', $taxRule->tax_type) === 'fixed' ? 'd-none' : '' }}" id="percentageRateBox">
                                <label class="form-label">Tax Rate (%) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number"
                                           step="0.0001"
                                           min="0"
                                           max="100"
                                           name="rate"
                                           class="form-control font-monospace"
                                           id="rateInput"
                                           placeholder="5.0000"
                                           value="{{ old('rate', number_format($taxRule->rate, 2, '.', '')) }}">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6 {{ old('tax_type', $taxRule->tax_type) === 'fixed' ? '' : 'd-none' }}" id="fixedAmountBox">
                                <label class="form-label">Fixed Amount (BDT) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number"
                                           step="0.01"
                                           min="0"
                                           name="fixed_amount"
                                           class="form-control font-monospace"
                                           id="fixedAmountInput"
                                           placeholder="0.00"
                                           value="{{ old('fixed_amount', number_format($taxRule->fixed_amount, 2, '.', '')) }}">
                                </div>
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label">Priority Order <span class="text-danger">*</span></label>
                                <input type="number"
                                       min="1"
                                       max="999"
                                       name="priority"
                                       class="form-control font-monospace"
                                       value="{{ old('priority', $taxRule->priority) }}"
                                       required>
                                <small class="form-text">
                                    Higher numbers have higher priority. Use 50 or 100 for branch-specific overrides.
                                </small>
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label">Minimum Taxable Base (BDT)</label>
                                <input type="number"
                                       step="0.01"
                                       min="0"
                                       name="min_taxable_amount"
                                       class="form-control font-monospace"
                                       placeholder="0.00"
                                       value="{{ old('min_taxable_amount', $taxRule->min_taxable_amount) }}">
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label">Maximum Taxable Base (BDT)</label>
                                <input type="number"
                                       step="0.01"
                                       min="0"
                                       name="max_taxable_amount"
                                       class="form-control font-monospace"
                                       placeholder="Leave blank for no limit"
                                       value="{{ old('max_taxable_amount', $taxRule->max_taxable_amount) }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Legal Reference / Admin Note</label>
                                <textarea name="admin_note"
                                          class="form-control"
                                          rows="3"
                                          placeholder="Enter NBR SRO, official circular reference or internal notes...">{{ old('admin_note', $taxRule->admin_note) }}</textarea>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            {{-- Right Column --}}
            <div class="col-12 col-lg-5">

                {{-- Scope Conditions --}}
                <div class="card tax-rule-card mb-3">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="tax-rule-section-icon">
                                <i class="fas fa-diagram-project"></i>
                            </span>
                            <div>
                                <h6 class="mb-0 fw-bold">Scope Conditions</h6>
                                <small class="text-secondary">Choose where this rule applies.</small>
                            </div>
                        </div>

                        <button type="button"
                                class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-2"
                                id="addConditionBtn">
                            <i class="fas fa-plus"></i>
                            <span>Add Scope</span>
                        </button>
                    </div>

                    <div class="card-body">
                        <div class="tax-rule-info mb-3">
                            <i class="fas fa-circle-info me-1"></i>
                            If no scope is added, this rule applies globally to all products.
                        </div>

                        <div id="conditionsContainer" class="d-flex flex-column gap-2">
                            <!-- Dynamic Conditions Inserted via JS -->
                        </div>
                    </div>
                </div>

                {{-- Validity & Status --}}
                <div class="card tax-rule-card mb-3">
                    <div class="card-header">
                        <div class="d-flex align-items-center gap-2">
                            <span class="tax-rule-section-icon">
                                <i class="fas fa-calendar-check"></i>
                            </span>
                            <div>
                                <h6 class="mb-0 fw-bold">Validity &amp; Status</h6>
                                <small class="text-secondary">Set the active period and rule status.</small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-12 col-sm-6">
                                <label class="form-label">Valid From</label>
                                <input type="date"
                                       name="valid_from"
                                       class="form-control"
                                       value="{{ old('valid_from', $taxRule->valid_from ? \Carbon\Carbon::parse($taxRule->valid_from)->format('Y-m-d') : '') }}">
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label">Valid To</label>
                                <input type="date"
                                       name="valid_to"
                                       class="form-control"
                                       value="{{ old('valid_to', $taxRule->valid_to ? \Carbon\Carbon::parse($taxRule->valid_to)->format('Y-m-d') : '') }}">
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch tax-rule-status-switch">
                                    <input class="form-check-input ms-0 me-2"
                                           type="checkbox"
                                           name="is_active"
                                           value="1"
                                           id="isRuleActive"
                                           {{ old('is_active', $taxRule->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="isRuleActive">
                                        Keep Tax Rule Active
                                        <small class="d-block text-secondary fw-normal mt-1">
                                            Enable this rule immediately after saving.
                                        </small>
                                    </label>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Save Action --}}
                <div class="card tax-rule-card tax-rule-save-card">
                    <div class="card-body">
                        <button type="submit"
                                class="btn btn-primary w-100 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                            <i class="fas fa-check-circle"></i>
                            <span>Update Tax Rule</span>
                        </button>

                        <small class="text-secondary d-block text-center mt-2">
                            Review your changes before updating this rule.
                        </small>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<style>
    /* =========================================================
       Tax Rule Page - Kaiadmin Lite Classic / White Classic
       Scoped styles only
    ========================================================= */

    .tax-rule-page {
        --tax-primary: var(--bs-primary, #1572e8);
        --tax-primary-soft: rgba(21, 114, 232, 0.08);
        --tax-border: #e5e7eb;
        --tax-muted: #6b7280;
        --tax-surface: #ffffff;
        --tax-soft-bg: #f8fafc;
        --tax-radius: 5px;
        color: #344054;
    }

    .tax-rule-page .tax-rule-header {
        min-width: 0;
    }

    .tax-rule-page .tax-rule-header h4 {
        font-size: 1.2rem;
        letter-spacing: -0.02em;
    }

    .tax-rule-page .page-actions {
        flex-shrink: 0;
    }

    .tax-rule-page .card,
    .tax-rule-page .btn,
    .tax-rule-page .form-control,
    .tax-rule-page .form-select,
    .tax-rule-page .input-group-text,
    .tax-rule-page .badge,
    .tax-rule-page .alert,
    .tax-rule-page .condition-row,
    .tax-rule-page .list-group-item,
    .tax-rule-page .tax-rule-status-switch {
        border-radius: var(--tax-radius) !important;
    }

    .tax-rule-page .tax-rule-card {
        border: 1px solid var(--tax-border);
        background: var(--tax-surface);
        box-shadow: 0 1px 3px rgba(16, 24, 40, 0.045);
        overflow: visible;
    }

    .tax-rule-page .tax-rule-card > .card-header {
        padding: 15px 16px 12px;
        background: #fff;
        border-bottom: 1px solid #f0f1f3;
    }

    .tax-rule-page .tax-rule-card > .card-body {
        padding: 16px;
    }

    .tax-rule-page .tax-rule-section-icon {
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--tax-primary);
        background: var(--tax-primary-soft);
        border-radius: 5px;
        font-size: 14px;
    }

    .tax-rule-page .card-header h6 {
        color: #263445;
        font-size: 0.9rem;
        letter-spacing: -0.01em;
    }

    .tax-rule-page .card-header small {
        display: block;
        margin-top: 3px;
        font-size: 11px;
    }

    .tax-rule-page .form-label {
        display: block;
        margin-bottom: 6px;
        color: #344054;
        font-size: 0.82rem;
        font-weight: 600;
    }

    .tax-rule-page .form-control,
    .tax-rule-page .form-select {
        min-height: 38px;
        border: 1px solid #dce1e7;
        background-color: #fff;
        color: #344054;
        font-size: 0.84rem;
        box-shadow: none;
    }

    .tax-rule-page textarea.form-control {
        min-height: auto;
        resize: vertical;
    }

    .tax-rule-page .form-control::placeholder {
        color: #98a2b3;
        opacity: 1;
    }

    .tax-rule-page .form-control:focus,
    .tax-rule-page .form-select:focus {
        border-color: var(--tax-primary);
        box-shadow: 0 0 0 0.18rem rgba(21, 114, 232, 0.12);
    }

    .tax-rule-page .input-group-text {
        min-width: 40px;
        justify-content: center;
        border-color: #dce1e7;
        background: #f8fafc;
        color: #667085;
        font-size: 0.84rem;
    }

    .tax-rule-page .form-text {
        display: block;
        margin-top: 5px;
        color: var(--tax-muted);
        font-size: 11px;
    }

    .tax-rule-page .btn {
        min-height: 34px;
        font-size: 0.8rem;
        font-weight: 600;
        transition: all 0.18s ease;
    }

    .tax-rule-page .btn-primary {
        background-color: var(--tax-primary);
        border-color: var(--tax-primary);
    }

    .tax-rule-page .btn-primary:hover {
        filter: brightness(0.94);
    }

    .tax-rule-page .btn-outline-primary {
        border-color: rgba(21, 114, 232, 0.4);
        color: var(--tax-primary);
    }

    .tax-rule-page .btn-outline-primary:hover {
        background: var(--tax-primary);
        border-color: var(--tax-primary);
        color: #fff;
    }

    .tax-rule-page .tax-rule-info {
        padding: 10px 11px;
        border: 1px solid #dbeafe;
        border-radius: 5px;
        background: #f5f9ff;
        color: #526b8b;
        font-size: 11.5px;
        line-height: 1.6;
    }

    .tax-rule-page .tax-rule-status-switch {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        padding: 12px;
        border: 1px solid var(--tax-border);
        background: var(--tax-soft-bg);
    }

    .tax-rule-page .tax-rule-status-switch .form-check-input {
        flex: 0 0 auto;
        margin-top: 3px;
        cursor: pointer;
    }

    .tax-rule-page .tax-rule-status-switch .form-check-input:checked {
        background-color: #198754;
        border-color: #198754;
    }

    .tax-rule-page .tax-rule-status-switch .form-check-label {
        color: #344054;
        font-size: 0.82rem;
        cursor: pointer;
    }

    .tax-rule-page .tax-rule-save-card {
        border-color: rgba(21, 114, 232, 0.18);
    }

    .tax-rule-page .tax-rule-save-card .card-body {
        padding: 16px;
    }

    .tax-rule-page .condition-row {
        padding: 12px !important;
        border: 1px solid var(--tax-border) !important;
        background: #fbfcfe !important;
    }

    .tax-rule-page .condition-row .badge {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.02em;
    }

    .tax-rule-page .condition-row .form-control,
    .tax-rule-page .condition-row .form-select {
        min-height: 36px;
        font-size: 0.8rem;
    }

    .tax-rule-page .condition-row .form-check-label {
        font-size: 0.76rem;
    }

    .tax-rule-page .condition-row .btn-link {
        font-size: 0.75rem;
    }

    .tax-rule-page .list-group {
        border-radius: 5px;
        overflow: hidden;
        background: #fff;
    }

    .tax-rule-page .list-group-item {
        border-color: #edf0f3;
        color: #344054;
        font-size: 0.78rem;
    }

    .tax-rule-page .list-group-item-action:hover {
        background: #f3f7ff;
        color: var(--tax-primary);
    }

    .tax-rule-page .alert {
        border-width: 1px;
        box-shadow: 0 1px 3px rgba(16, 24, 40, 0.04);
    }

    .tax-rule-page .badge {
        font-weight: 600;
    }

    .tax-rule-page .min-w-0 {
        min-width: 0;
    }

    /* Tablet and Mobile */
    @media (max-width: 767.98px) {
        .tax-rule-page {
            padding-left: 8px !important;
            padding-right: 8px !important;
        }

        .tax-rule-page .page-actions {
            width: 100%;
        }

        .tax-rule-page .page-actions .btn {
            flex: 1 1 0;
        }

        .tax-rule-page .tax-rule-card > .card-header {
            padding: 13px 12px 11px;
        }

        .tax-rule-page .tax-rule-card > .card-body {
            padding: 13px;
        }
    }

    @media (max-width: 575.98px) {
        .tax-rule-page .tax-rule-header h4 {
            font-size: 1.05rem;
        }

        .tax-rule-page .form-label {
            font-size: 0.79rem;
        }

        .tax-rule-page .form-control,
        .tax-rule-page .form-select {
            font-size: 0.82rem;
        }

        .tax-rule-page .condition-row {
            padding: 10px !important;
        }

        .tax-rule-page .tax-rule-section-icon {
            width: 31px;
            height: 31px;
            flex-basis: 31px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Automatic Code Slug Generation
        const nameInput = document.getElementById('ruleNameInput');
        const codeInput = document.getElementById('ruleCodeInput');

        if (nameInput && codeInput) {
            nameInput.addEventListener('input', function () {
                if (!codeInput.dataset.touched) {
                    codeInput.value = this.value
                        .trim()
                        .toUpperCase()
                        .replace(/[^A-Z0-9]/g, '_')
                        .replace(/_+/g, '_');
                }
            });

            codeInput.addEventListener('input', function () {
                codeInput.dataset.touched = 'true';
            });
        }

        // 2. Status Type Change Handling
        const statusTypeSelect = document.getElementById('statusTypeSelect');
        const rateInput = document.getElementById('rateInput');
        const fixedAmountInput = document.getElementById('fixedAmountInput');

        function evaluateStatusType() {
            if (statusTypeSelect.value === 'exempt' || statusTypeSelect.value === 'zero_rated') {
                rateInput.value = '0.00';
                fixedAmountInput.value = '0.00';
                rateInput.readOnly = true;
                fixedAmountInput.readOnly = true;
            } else {
                rateInput.readOnly = false;
                fixedAmountInput.readOnly = false;
            }
        }

        statusTypeSelect.addEventListener('change', evaluateStatusType);
        evaluateStatusType();

        // 3. Percentage vs Fixed Amount Toggle
        const taxTypeSelect = document.getElementById('taxTypeSelect');
        const percentageRateBox = document.getElementById('percentageRateBox');
        const fixedAmountBox = document.getElementById('fixedAmountBox');

        function evaluateTaxType() {
            if (taxTypeSelect.value === 'fixed') {
                percentageRateBox.classList.add('d-none');
                fixedAmountBox.classList.remove('d-none');
            } else {
                percentageRateBox.classList.remove('d-none');
                fixedAmountBox.classList.add('d-none');
            }
        }

        taxTypeSelect.addEventListener('change', evaluateTaxType);
        evaluateTaxType();

        // 4. Dynamic Conditions Container
        const container = document.getElementById('conditionsContainer');
        const addBtn = document.getElementById('addConditionBtn');
        let conditionIndex = 0;

        const branchOptions = `@foreach($branches as $b)<option value="{{ $b->id }}">{{ addslashes($b->name) }}</option>@endforeach`;
        const zoneOptions = `@foreach($shippingZones as $z)<option value="{{ $z->id }}">{{ addslashes($z->name) }}</option>@endforeach`;

        const existingConditions = {!! json_encode($existingConditionsData) !!};

        function addConditionRow(type = 'category', id = '', value = '', isExcluded = false, label = '') {
            const rowId = `cond_row_${conditionIndex}`;
            const div = document.createElement('div');

            div.className = 'border border-light-subtle bg-light p-2 position-relative condition-row';
            div.id = rowId;

            div.innerHTML = `
                <div class="d-flex align-items-center justify-content-between mb-2 gap-2">
                    <span class="badge bg-secondary-subtle text-secondary small fw-bold px-2 py-1">
                        Scope #${conditionIndex + 1}
                    </span>
                    <button type="button"
                            class="btn btn-sm btn-link text-danger p-0 text-decoration-none"
                            onclick="document.getElementById('${rowId}').remove()">
                        <i class="fas fa-trash-alt me-1"></i> Remove
                    </button>
                </div>

                <div class="row g-2">
                    <div class="col-12 col-sm-5">
                        <select name="conditions[${conditionIndex}][type]"
                                class="form-select form-select-sm cond-type-select"
                                data-index="${conditionIndex}">
                            <option value="category" ${type === 'category' ? 'selected' : ''}>Category</option>
                            <option value="product" ${type === 'product' ? 'selected' : ''}>Product</option>
                            <option value="brand" ${type === 'brand' ? 'selected' : ''}>Brand</option>
                            <option value="branch" ${type === 'branch' ? 'selected' : ''}>Branch (POS)</option>
                            <option value="shipping_zone" ${type === 'shipping_zone' ? 'selected' : ''}>Shipping Zone</option>
                            <option value="delivery_charge" ${type === 'delivery_charge' ? 'selected' : ''}>Delivery Charge</option>
                        </select>
                    </div>

                    <div class="col-12 col-sm-7" id="cond_target_box_${conditionIndex}">
                        <!-- Dynamic Selector Target -->
                    </div>

                    <div class="col-12 mt-1">
                        <div class="form-check">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="conditions[${conditionIndex}][is_ex]"
                                   value="1"
                                   id="is_ex_${conditionIndex}"
                                   ${isExcluded ? 'checked' : ''}>
                            <label class="form-check-label small text-secondary" for="is_ex_${conditionIndex}">
                                Exclude this scope from the rule
                            </label>
                        </div>
                    </div>
                </div>
            `;

            container.appendChild(div);
            renderTargetInput(conditionIndex, type, id, value, label);
            conditionIndex++;
        }

        function renderTargetInput(idx, type, selectedId = '', customVal = '', labelText = '') {
            const targetBox = document.getElementById(`cond_target_box_${idx}`);
            if (!targetBox) return;

            if (type === 'branch') {
                targetBox.innerHTML = `
                    <select name="conditions[${idx}][id]" class="form-select form-select-sm font-monospace">
                        <option value="">Select Branch...</option>
                        ${branchOptions}
                    </select>
                `;

                if (selectedId) targetBox.querySelector('select').value = selectedId;

            } else if (type === 'shipping_zone') {
                targetBox.innerHTML = `
                    <select name="conditions[${idx}][id]" class="form-select form-select-sm font-monospace">
                        <option value="">Select Zone...</option>
                        ${zoneOptions}
                    </select>
                `;

                if (selectedId) targetBox.querySelector('select').value = selectedId;

            } else if (type === 'delivery_charge') {
                targetBox.innerHTML = `
                    <input type="text"
                           name="conditions[${idx}][value]"
                           class="form-control form-control-sm font-monospace"
                           value="All Delivery Charges"
                           readonly>
                `;

            } else {
                targetBox.innerHTML = `
                    <div class="position-relative">
                        <input type="text"
                               class="form-control form-control-sm entity-search-input"
                               data-index="${idx}"
                               data-type="${type}"
                               placeholder="Search ${type}..."
                               value="${labelText}"
                               autocomplete="off">

                        <input type="hidden"
                               name="conditions[${idx}][id]"
                               id="cond_hidden_id_${idx}"
                               value="${selectedId}">

                        <div class="list-group position-absolute w-100 shadow border-light-subtle d-none mt-1"
                             id="search_results_${idx}"
                             style="z-index: 1050; max-height: 160px; overflow-y: auto; font-size: 12px;">
                        </div>
                    </div>
                `;

                attachEntitySearch(idx, type);
            }
        }

        function attachEntitySearch(idx, type) {
            const input = document.querySelector(`.entity-search-input[data-index="${idx}"]`);
            const hiddenId = document.getElementById(`cond_hidden_id_${idx}`);
            const resultsBox = document.getElementById(`search_results_${idx}`);

            if (!input) return;

            let debounceTimer = null;

            input.addEventListener('input', function () {
                clearTimeout(debounceTimer);

                const query = this.value.trim();

                if (query.length < 1) {
                    resultsBox.classList.add('d-none');
                    return;
                }

                debounceTimer = setTimeout(() => {
                    fetch(`{{ route('admin.tax.rules.search_entities') }}?type=${type}&q=${encodeURIComponent(query)}`)
                        .then(res => res.json())
                        .then(data => {
                            resultsBox.innerHTML = '';

                            if (data.results && data.results.length > 0) {
                                data.results.forEach(item => {
                                    const a = document.createElement('a');

                                    a.className = 'list-group-item list-group-item-action py-2 px-2 d-flex justify-content-between align-items-center';
                                    a.href = 'javascript:void(0)';
                                    a.innerHTML = `<span>${item.text}</span> <small class="text-secondary">${item.extra || ''}</small>`;

                                    a.onclick = function () {
                                        input.value = item.text;
                                        hiddenId.value = item.id;
                                        resultsBox.classList.add('d-none');
                                    };

                                    resultsBox.appendChild(a);
                                });

                                resultsBox.classList.remove('d-none');

                            } else {
                                resultsBox.innerHTML = '<div class="list-group-item py-2 px-2 text-secondary small">No results found</div>';
                                resultsBox.classList.remove('d-none');
                            }
                        });
                }, 300);
            });

            document.addEventListener('click', function (e) {
                if (!input.contains(e.target) && !resultsBox.contains(e.target)) {
                    resultsBox.classList.add('d-none');
                }
            });
        }

        container.addEventListener('change', function (e) {
            if (e.target.classList.contains('cond-type-select')) {
                const idx = e.target.dataset.index;
                renderTargetInput(idx, e.target.value);
            }
        });

        addBtn.addEventListener('click', function () {
            addConditionRow();
        });

        // 5. Conditions Initial Load
        // Old input takes priority after validation errors; otherwise load database records.
        @if(old('conditions'))
            const oldConditions = @json(old('conditions'));

            if (Array.isArray(oldConditions) || typeof oldConditions === 'object') {
                Object.values(oldConditions).forEach(cond => {
                    if (cond && cond.type) {
                        addConditionRow(cond.type, cond.id || '', cond.value || '', Boolean(cond.is_ex));
                    }
                });
            }
        @else
            if (existingConditions && existingConditions.length > 0) {
                existingConditions.forEach(c => {
                    if (c.type !== 'global') {
                        addConditionRow(c.type, c.id, c.value, c.is_excluded, c.label);
                    }
                });
            }
        @endif
    });
</script>
@endsection