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

<div class="container-fluid px-2 px-md-3 py-2 tax-rule-page">
    <!-- Header & Navigation -->
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2 mb-3">
        <div class="d-flex align-items-center flex-wrap gap-2">
            <h4 class="fw-bold mb-0 text-dark fs-5 fs-md-4 d-flex align-items-center gap-2 text-nowrap">
                <i class="fas fa-edit text-primary"></i> Edit Rule: {{ $taxRule->name }}
            </h4>
            <span class="text-secondary small d-none d-md-inline">•</span>
            <span class="text-secondary small mb-0 text-nowrap">Update rates, scopes &amp; priority</span>
        </div>

        <div class="d-flex align-items-center gap-2 w-100 w-sm-auto justify-content-end page-actions">
            <a href="{{ route('admin.tax.rules.index') }}" class="btn btn-sm btn-outline-secondary flex-fill flex-sm-grow-0 d-inline-flex align-items-center justify-content-center gap-1">
                <i class="fas fa-arrow-left"></i> <span>Back to Rules</span>
            </a>
            <a href="{{ route('admin.tax.rules.create') }}" class="btn btn-sm btn-primary px-3 flex-fill flex-sm-grow-0 d-inline-flex align-items-center justify-content-center gap-1">
                <i class="fas fa-plus-circle"></i> <span>Create Another</span>
            </a>
        </div>
    </div>

    <!-- Error Validation Feedback -->
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border border-danger-subtle shadow-sm mb-3 py-2 px-3" role="alert">
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="fas fa-triangle-exclamation text-danger"></i>
                <span class="fw-semibold small">Please resolve errors:</span>
            </div>
            <ul class="mb-0 ps-3 small text-secondary">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('admin.tax.rules.update', $taxRule->id) }}" method="POST" id="editTaxRuleForm">
        @csrf
        @method('PUT')

        <div class="row g-2 g-md-3">
            <!-- Left Column: Core Parameters & Rates -->
            <div class="col-12 col-lg-7">
                <!-- 1. Basic Identification -->
                <div class="card border border-light-subtle shadow-sm bg-white mb-2 mb-md-3">
                    <div class="card-header bg-white border-0 pt-3 px-3 pb-1">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="fas fa-sliders text-primary"></i> General Information
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2 g-md-3">
                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold text-dark small mb-1">Rule Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control form-control-sm" id="ruleNameInput" placeholder="Standard 5% Apparel VAT" value="{{ old('name', $taxRule->name) }}" required>
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold text-dark small mb-1">Rule Code <span class="text-danger">*</span></label>
                                <input type="text" name="code" class="form-control form-control-sm font-monospace text-uppercase" id="ruleCodeInput" placeholder="VAT_APPAREL_5" value="{{ old('code', $taxRule->code) }}" required>
                                <span class="text-secondary d-block mt-1" style="font-size: 11px;">Unique code identifier.</span>
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold text-dark small mb-1">Tax Class</label>
                                <select name="tax_class_id" class="form-select form-select-sm">
                                    <option value="">None (Standalone Rule)</option>
                                    @foreach($taxClasses as $tc)
                                        <option value="{{ $tc->id }}" {{ old('tax_class_id', $taxRule->tax_class_id) == $tc->id ? 'selected' : '' }}>
                                            {{ $tc->name }} ({{ $tc->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold text-dark small mb-1">Tax Status <span class="text-danger">*</span></label>
                                <select name="status_type" class="form-select form-select-sm" id="statusTypeSelect" required>
                                    <option value="taxable" {{ old('status_type', $taxRule->status_type) === 'taxable' ? 'selected' : '' }}>Taxable (করযোগ্য)</option>
                                    <option value="exempt" {{ old('status_type', $taxRule->status_type) === 'exempt' ? 'selected' : '' }}>Exempt (করমুক্ত)</option>
                                    <option value="zero_rated" {{ old('status_type', $taxRule->status_type) === 'zero_rated' ? 'selected' : '' }}>Zero-Rated (০% কর)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Rate & Assessment Mode -->
                <div class="card border border-light-subtle shadow-sm bg-white mb-2 mb-md-3">
                    <div class="card-header bg-white border-0 pt-3 px-3 pb-1">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="fas fa-calculator text-primary"></i> Tax Calculation
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2 g-md-3">
                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold text-dark small mb-1">Calculation Mode <span class="text-danger">*</span></label>
                                <select name="calculation_type" class="form-select form-select-sm" required>
                                    <option value="exclusive" {{ old('calculation_type', $taxRule->calculation_type) === 'exclusive' ? 'selected' : '' }}>
                                        Exclusive (Added on top of price)
                                    </option>
                                    <option value="inclusive" {{ old('calculation_type', $taxRule->calculation_type) === 'inclusive' ? 'selected' : '' }}>
                                        Inclusive (Included in retail price)
                                    </option>
                                </select>
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold text-dark small mb-1">Tax Method <span class="text-danger">*</span></label>
                                <select name="tax_type" class="form-select form-select-sm" id="taxTypeSelect" required>
                                    <option value="percentage" {{ old('tax_type', $taxRule->tax_type) === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                    <option value="fixed" {{ old('tax_type', $taxRule->tax_type) === 'fixed' ? 'selected' : '' }}>Fixed Amount (BDT)</option>
                                </select>
                            </div>

                            <div class="col-12 col-sm-6 {{ old('tax_type', $taxRule->tax_type) === 'fixed' ? 'd-none' : '' }}" id="percentageRateBox">
                                <label class="form-label fw-semibold text-dark small mb-1">Tax Rate (%) <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.0001" min="0" max="100" name="rate" class="form-control font-monospace" id="rateInput" placeholder="5.0000" value="{{ old('rate', number_format($taxRule->rate, 2, '.', '')) }}">
                                    <span class="input-group-text bg-light text-secondary">%</span>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6 {{ old('tax_type', $taxRule->tax_type) === 'fixed' ? '' : 'd-none' }}" id="fixedAmountBox">
                                <label class="form-label fw-semibold text-dark small mb-1">Fixed Amount (BDT) <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light text-secondary">৳</span>
                                    <input type="number" step="0.01" min="0" name="fixed_amount" class="form-control font-monospace" id="fixedAmountInput" placeholder="0.00" value="{{ old('fixed_amount', number_format($taxRule->fixed_amount, 2, '.', '')) }}">
                                </div>
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold text-dark small mb-1">Priority Order <span class="text-danger">*</span></label>
                                <input type="number" min="1" max="999" name="priority" class="form-control form-control-sm font-monospace" value="{{ old('priority', $taxRule->priority) }}" required>
                                <span class="text-secondary d-block mt-1" style="font-size: 11px;">বড় নম্বর = বেশি অগ্রাধিকার (যেমন: নির্দিষ্ট ব্রাঞ্চে ০% কার্যকর করতে প্রায়োরিটি ৫০ বা ১০০ দিন)।</span>
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold text-dark small mb-1">Min. Taxable Base (BDT)</label>
                                <input type="number" step="0.01" min="0" name="min_taxable_amount" class="form-control form-control-sm font-monospace" placeholder="0.00" value="{{ old('min_taxable_amount', $taxRule->min_taxable_amount) }}">
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold text-dark small mb-1">Max. Taxable Base (BDT)</label>
                                <input type="number" step="0.01" min="0" name="max_taxable_amount" class="form-control form-control-sm font-monospace" placeholder="Leave blank for no limit" value="{{ old('max_taxable_amount', $taxRule->max_taxable_amount) }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold text-dark small mb-1">Legal Reference / Admin Note</label>
                                <textarea name="admin_note" class="form-control form-control-sm" rows="2" placeholder="NBR SRO or official circular reference...">{{ old('admin_note', $taxRule->admin_note) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Conditions, Scopes & Validity -->
            <div class="col-12 col-lg-5">
                <!-- 3. Target Scope & Cascading Conditions -->
                <div class="card border border-light-subtle shadow-sm bg-white mb-2 mb-md-3">
                    <div class="card-header bg-white border-0 pt-3 px-3 pb-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="fas fa-diagram-project text-primary"></i> Scope Conditions
                        </h6>
                        <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 d-inline-flex align-items-center gap-1" id="addConditionBtn">
                            <i class="fas fa-plus"></i> <span>Add Scope</span>
                        </button>
                    </div>
                    <div class="card-body p-3">
                        <span class="text-secondary small d-block mb-3" style="font-size: 11.5px;">
                            কোনো স্কোপ না দিলে রুলটি গ্লোবালি সব পণ্যের ওপর প্রযোজ্য হবে।
                        </span>

                        <div id="conditionsContainer" class="d-flex flex-column gap-2">
                            <!-- Dynamic Conditions Inserted via JS -->
                        </div>
                    </div>
                </div>

                <!-- 4. Rule Validity & Status Switch -->
                <div class="card border border-light-subtle shadow-sm bg-white mb-2 mb-md-3">
                    <div class="card-header bg-white border-0 pt-3 px-3 pb-1">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="fas fa-calendar-check text-primary"></i> Validity &amp; Status
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2 g-md-3">
                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold text-dark small mb-1">Valid From</label>
                                <input type="date" name="valid_from" class="form-control form-control-sm" value="{{ old('valid_from', $taxRule->valid_from ? \Carbon\Carbon::parse($taxRule->valid_from)->format('Y-m-d') : '') }}">
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold text-dark small mb-1">Valid To</label>
                                <input type="date" name="valid_to" class="form-control form-control-sm" value="{{ old('valid_to', $taxRule->valid_to ? \Carbon\Carbon::parse($taxRule->valid_to)->format('Y-m-d') : '') }}">
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch p-2 border rounded-1 bg-light mt-1">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" name="is_active" value="1" id="isRuleActive" {{ old('is_active', $taxRule->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-dark small" for="isRuleActive">
                                        Keep Tax Rule Active (রুলটি সক্রিয় রাখুন)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Save Action Card -->
                <div class="card border border-light-subtle shadow-sm bg-white">
                    <div class="card-body p-3">
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                            <i class="fas fa-check-circle"></i> <span>Update Tax Rule (হালনাগাদ সংরক্ষণ করুন)</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    /* Strict 5px Radius Rule */
    .tax-rule-page .card,
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

    /* Switch Styling */
    .tax-rule-page .form-check-input:checked {
        background-color: #198754;
        border-color: #198754;
    }

    /* Focus States */
    .tax-rule-page .form-control:focus,
    .tax-rule-page .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.15);
    }

    /* Responsive Mobile Optimization */
    @media (max-width: 575.98px) {
        .tax-rule-page {
            padding-left: 6px !important;
            padding-right: 6px !important;
        }

        .tax-rule-page .card-body {
            padding: 0.75rem !important;
        }

        .tax-rule-page .form-label {
            font-size: 0.8rem !important;
        }

        .tax-rule-page .condition-row {
            padding: 0.65rem !important;
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
                            <label class="form-check-label small text-secondary" for="is_ex_${conditionIndex}">Exclude this scope from rule</label>
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
                    <input type="text" name="conditions[${idx}][value]" class="form-control form-control-sm font-monospace" value="All Delivery Charges" readonly>
                `;
            } else {
                targetBox.innerHTML = `
                    <div class="position-relative">
                        <input type="text" class="form-control form-control-sm entity-search-input" data-index="${idx}" data-type="${type}" placeholder="Search ${type}..." value="${labelText}" autocomplete="off">
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

        // 5. Conditions Initial Load (Old input priority on validation error, else database records)
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