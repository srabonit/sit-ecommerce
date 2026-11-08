
@extends('layouts.admin')

@section('title', 'Create Tax Rule')

@section('admin_content')
<div class="container-fluid px-2 px-md-3 py-3 tax-rule-page">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                <i class="fas fa-plus-circle text-primary"></i> Create Tax Rule
            </h4>
            <p class="text-muted small mb-0">Configure tax rates, priority levels, and branch or category conditions.</p>
        </div>

        <div class="d-flex align-items-center gap-2 w-100 w-sm-auto justify-content-start justify-content-sm-end">
            <a href="{{ route('admin.tax.rules.index') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-2">
                <i class="fas fa-arrow-left"></i> <span>Back to Rules</span>
            </a>
        </div>
    </div>

    <!-- Validation Feedback -->
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4 py-3 px-3" role="alert">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="fas fa-triangle-exclamation text-danger"></i>
                <span class="fw-semibold small">Please resolve the following errors:</span>
            </div>
            <ul class="mb-0 ps-4 small">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('admin.tax.rules.store') }}" method="POST" id="taxRuleForm">
        @csrf

        <div class="row g-3">
            <!-- Left Column: Core Parameters & Rates -->
            <div class="col-12 col-lg-7">
                <!-- 1. General Information -->
                <div class="card tax-rule-card mb-3">
                    <div class="card-header">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="fas fa-sliders text-primary"></i> General Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold text-dark small">Rule Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control form-control-sm" id="ruleNameInput" placeholder="Lakshmipur Branch 0% VAT" value="{{ old('name') }}" required>
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold text-dark small">Rule Code <span class="text-danger">*</span></label>
                                <input type="text" name="code" class="form-control form-control-sm font-monospace text-uppercase" id="ruleCodeInput" placeholder="LAKSHMIPUR_ZERO_VAT" value="{{ old('code') }}" required>
                                <span class="text-muted d-block mt-1 tax-rule-hint">Unique identifier (auto-generated from the rule name).</span>
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold text-dark small">Tax Class</label>
                                <select name="tax_class_id" class="form-select form-select-sm">
                                    <option value="">None (Standalone Rule)</option>
                                    @foreach($taxClasses as $tc)
                                        <option value="{{ $tc->id }}" {{ old('tax_class_id') == $tc->id ? 'selected' : '' }}>
                                            {{ $tc->name }} ({{ $tc->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold text-dark small">Tax Status <span class="text-danger">*</span></label>
                                <select name="status_type" class="form-select form-select-sm" id="statusTypeSelect" required>
                                    <option value="taxable" {{ old('status_type', 'taxable') === 'taxable' ? 'selected' : '' }}>Taxable</option>
                                    <option value="exempt" {{ old('status_type') === 'exempt' ? 'selected' : '' }}>Exempt</option>
                                    <option value="zero_rated" {{ old('status_type') === 'zero_rated' ? 'selected' : '' }}>Zero-Rated (0% Tax)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Tax Calculation -->
                <div class="card tax-rule-card mb-3">
                    <div class="card-header">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="fas fa-calculator text-primary"></i> Tax Calculation
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold text-dark small">Calculation Mode <span class="text-danger">*</span></label>
                                <select name="calculation_type" class="form-select form-select-sm" required>
                                    <option value="exclusive" {{ old('calculation_type', 'exclusive') === 'exclusive' ? 'selected' : '' }}>
                                        Exclusive (Added on top of price)
                                    </option>
                                    <option value="inclusive" {{ old('calculation_type') === 'inclusive' ? 'selected' : '' }}>
                                        Inclusive (Included in retail price)
                                    </option>
                                </select>
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold text-dark small">Tax Method <span class="text-danger">*</span></label>
                                <select name="tax_type" class="form-select form-select-sm" id="taxTypeSelect" required>
                                    <option value="percentage" {{ old('tax_type', 'percentage') === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                    <option value="fixed" {{ old('tax_type') === 'fixed' ? 'selected' : '' }}>Fixed Amount (BDT)</option>
                                </select>
                            </div>

                            <div class="col-12 col-sm-6" id="percentageRateBox">
                                <label class="form-label fw-semibold text-dark small">Tax Rate (%) <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.0001" min="0" max="100" name="rate" class="form-control font-monospace" id="rateInput" placeholder="5.0000" value="{{ old('rate', '5.00') }}">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6 d-none" id="fixedAmountBox">
                                <label class="form-label fw-semibold text-dark small">Fixed Amount (BDT) <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" step="0.01" min="0" name="fixed_amount" class="form-control font-monospace" id="fixedAmountInput" placeholder="0.00" value="{{ old('fixed_amount', '0.00') }}">
                                </div>
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold text-dark small">Priority Order <span class="text-danger">*</span></label>
                                <input type="number" min="1" max="999" name="priority" class="form-control form-control-sm font-monospace" value="{{ old('priority', '10') }}" required>
                                <span class="text-muted d-block mt-1 tax-rule-hint">Higher numbers have higher priority. Use 50 or 100 to prioritize a branch-specific 0% rule.</span>
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold text-dark small">Minimum Taxable Base (BDT)</label>
                                <input type="number" step="0.01" min="0" name="min_taxable_amount" class="form-control form-control-sm font-monospace" placeholder="0.00" value="{{ old('min_taxable_amount', '0.00') }}">
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold text-dark small">Maximum Taxable Base (BDT)</label>
                                <input type="number" step="0.01" min="0" name="max_taxable_amount" class="form-control form-control-sm font-monospace" placeholder="Leave blank for no upper limit" value="{{ old('max_taxable_amount') }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold text-dark small">Legal Reference / Admin Note</label>
                                <textarea name="admin_note" class="form-control form-control-sm" rows="2" placeholder="NBR SRO, VAT regulations, or internal notes...">{{ old('admin_note') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Conditions, Scopes & Validity -->
            <div class="col-12 col-lg-5">
                <!-- 3. Scope Conditions -->
                <div class="card tax-rule-card mb-3">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="fas fa-diagram-project text-primary"></i> Scope Conditions
                        </h6>
                        <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-2" id="addConditionBtn">
                            <i class="fas fa-plus"></i> <span>Add Scope</span>
                        </button>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3 tax-rule-description">
                            If no scope is added, this rule applies globally to all products. Add a scope to target a specific branch or category.
                        </p>

                        <div id="conditionsContainer" class="d-flex flex-column gap-2">
                            <!-- Dynamic Conditions Inserted via JS -->
                        </div>
                    </div>
                </div>

                <!-- 4. Validity & Status -->
                <div class="card tax-rule-card mb-3">
                    <div class="card-header">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="fas fa-calendar-check text-primary"></i> Validity &amp; Status
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold text-dark small">Valid From</label>
                                <input type="date" name="valid_from" class="form-control form-control-sm" value="{{ old('valid_from') }}">
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold text-dark small">Valid To</label>
                                <input type="date" name="valid_to" class="form-control form-control-sm" value="{{ old('valid_to') }}">
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch tax-rule-status-switch">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" name="is_active" value="1" id="isRuleActive" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-dark small" for="isRuleActive">
                                        Activate Rule Immediately
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Save Action -->
                <div class="card tax-rule-card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                            <i class="fas fa-save"></i> <span>Save Tax Rule</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    /* Kaiadmin Classic / White Classic - page-scoped styling */
    .tax-rule-page {
        --tax-rule-primary: var(--bs-primary, #1572e8);
        --tax-rule-border: #e6e9ef;
        --tax-rule-muted: #7b8190;
        --tax-rule-surface: #ffffff;
        --tax-rule-soft: #f7f8fa;
        color: #2a2f5b;
    }

    .tax-rule-page .tax-rule-card,
    .tax-rule-page .btn,
    .tax-rule-page .form-control,
    .tax-rule-page .form-select,
    .tax-rule-page .input-group-text,
    .tax-rule-page .badge,
    .tax-rule-page .alert,
    .tax-rule-page .condition-row,
    .tax-rule-page .list-group-item,
    .tax-rule-page .rounded,
    .tax-rule-page .rounded-1 {
        border-radius: 5px !important;
    }

    .tax-rule-page .tax-rule-card {
        border: 1px solid var(--tax-rule-border);
        background: var(--tax-rule-surface);
        box-shadow: 0 1px 3px rgba(25, 35, 58, 0.045);
        overflow: visible;
    }

    .tax-rule-page .tax-rule-card > .card-header {
        padding: 1rem 1.125rem 0.25rem;
        background: var(--tax-rule-surface);
        border: 0;
    }

    .tax-rule-page .tax-rule-card > .card-body {
        padding: 1.125rem;
    }

    .tax-rule-page .tax-rule-card h6 {
        font-size: 0.9rem;
        letter-spacing: 0.01em;
    }

    .tax-rule-page .form-label {
        margin-bottom: 0.4rem;
    }

    .tax-rule-page .form-control,
    .tax-rule-page .form-select {
        min-height: 36px;
        border-color: #dfe3e8;
        color: #2a2f5b;
        background-color: #fff;
    }

    .tax-rule-page textarea.form-control {
        min-height: auto;
    }

    .tax-rule-page .input-group-text {
        border-color: #dfe3e8;
        background: var(--tax-rule-soft);
        color: #6c757d;
    }

    .tax-rule-page .form-control:focus,
    .tax-rule-page .form-select:focus {
        border-color: var(--tax-rule-primary);
        box-shadow: 0 0 0 0.15rem rgba(21, 114, 232, 0.13);
    }

    .tax-rule-page .btn {
        font-weight: 600;
        transition: background-color 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .tax-rule-page .btn-primary {
        background-color: var(--tax-rule-primary);
        border-color: var(--tax-rule-primary);
    }

    .tax-rule-page .btn-primary:hover {
        filter: brightness(0.95);
    }

    .tax-rule-page .tax-rule-hint {
        font-size: 11px;
        line-height: 1.45;
    }

    .tax-rule-page .tax-rule-description {
        font-size: 12px;
        line-height: 1.6;
    }

    .tax-rule-page .tax-rule-status-switch {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem;
        margin: 0;
        border: 1px solid var(--tax-rule-border);
        border-radius: 5px;
        background: var(--tax-rule-soft);
    }

    .tax-rule-page .form-check-input:checked {
        background-color: #198754;
        border-color: #198754;
    }

    .tax-rule-page .condition-row {
        padding: 0.75rem;
        border: 1px solid var(--tax-rule-border) !important;
        background: var(--tax-rule-soft) !important;
    }

    .tax-rule-page .condition-row .form-check {
        margin-bottom: 0;
    }

    .tax-rule-page .list-group {
        border-radius: 5px;
        overflow: hidden;
    }

    .tax-rule-page .alert {
        border: 1px solid rgba(220, 53, 69, 0.2);
    }

    @media (max-width: 575.98px) {
        .tax-rule-page {
            padding-left: 6px !important;
            padding-right: 6px !important;
        }

        .tax-rule-page .tax-rule-card > .card-header {
            padding: 0.875rem 0.875rem 0.25rem;
        }

        .tax-rule-page .tax-rule-card > .card-body {
            padding: 0.875rem;
        }

        .tax-rule-page .form-label {
            font-size: 0.8rem;
        }

        .tax-rule-page .condition-row {
            padding: 0.65rem;
        }

        .tax-rule-page .btn {
            white-space: normal;
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

        function addConditionRow(type = 'category', id = '', value = '', isExcluded = false, displayText = '') {
            const rowId = `cond_row_${conditionIndex}`;
            const div = document.createElement('div');
            div.className = 'border border-light-subtle bg-light p-2 position-relative condition-row';
            div.id = rowId;

            div.innerHTML = `
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="badge bg-secondary-subtle text-secondary small fw-bold px-2 py-1">Scope #${conditionIndex + 1}</span>
                    <button type="button" class="btn btn-sm btn-link text-danger p-0 text-decoration-none" onclick="document.getElementById('${rowId}').remove()">
                        <i class="fas fa-trash-alt me-1"></i> Remove
                    </button>
                </div>
                <div class="row g-2">
                    <div class="col-12 col-sm-5">
                        <select name="conditions[${conditionIndex}][type]" class="form-select form-select-sm cond-type-select" data-index="${conditionIndex}">
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
                            <input class="form-check-input" type="checkbox" name="conditions[${conditionIndex}][is_ex]" value="1" id="is_ex_${conditionIndex}" ${isExcluded ? 'checked' : ''}>
                            <label class="form-check-label small text-muted" for="is_ex_${conditionIndex}">Exclude this scope from the rule</label>
                        </div>
                    </div>
                </div>
            `;

            container.appendChild(div);
            renderTargetInput(conditionIndex, type, id, value, displayText);
            conditionIndex++;
        }

        function renderTargetInput(idx, type, selectedId = '', customVal = '', displayText = '') {
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
                    <input type="text" name="conditions[${idx}][value]" class="form-control form-control-sm font-monospace" value="All Delivery Charges" readonly>
                `;
            } else {
                targetBox.innerHTML = `
                    <div class="position-relative">
                        <input type="text" class="form-control form-control-sm entity-search-input" data-index="${idx}" data-type="${type}" placeholder="Search ${type}..." value="${displayText}" autocomplete="off">
                        <input type="hidden" name="conditions[${idx}][id]" id="cond_hidden_id_${idx}" value="${selectedId}">
                        <div class="list-group position-absolute w-100 shadow border-light-subtle d-none mt-1" id="search_results_${idx}" style="z-index: 1050; max-height: 160px; overflow-y: auto; font-size: 12px;"></div>
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
                                    a.className = 'list-group-item list-group-item-action py-1 px-2 d-flex justify-content-between align-items-center';
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
                                resultsBox.innerHTML = '<div class="list-group-item py-1 px-2 text-secondary small">No results found</div>';
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

        // 5. Restore Old Conditions on Validation Error
        @if(old('conditions'))
            const oldConditions = @json(old('conditions'));
            if (Array.isArray(oldConditions) || typeof oldConditions === 'object') {
                Object.values(oldConditions).forEach(cond => {
                    if (cond && cond.type) {
                        addConditionRow(cond.type, cond.id || '', cond.value || '', Boolean(cond.is_ex));
                    }
                });
            }
        @endif
    });
</script>
@endsection
