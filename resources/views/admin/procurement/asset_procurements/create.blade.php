@extends('layouts.admin')

@section('title', 'Capitalize Asset / Asset Procurement')

@section('admin_content')
<div class="container-fluid px-3 py-3 asset-capitalization-page">

    {{-- Breadcrumb & Action Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <h5 class="fw-bold text-dark mb-1">
                <i class="fas fa-boxes-packing text-primary me-2"></i>Capitalize Fixed Asset / Record Asset Procurement
            </h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="font-size: 12px;">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Procurement</li>
                    <li class="breadcrumb-item active" aria-current="page">Capitalize Asset</li>
                </ol>
            </nav>
        </div>

        <div class="d-flex gap-2">
            @php
                $indexUrl = Route::has('admin.procurement-assets.index') 
                    ? route('admin.procurement-assets.index') 
                    : (Route::has('admin.asset-procurements.index') ? route('admin.asset-procurements.index') : url('admin/procurement/procurement-assets'));
            @endphp
            <a href="{{ $indexUrl }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 5px;">
                <i class="fas fa-arrow-left me-1"></i> Back to Asset Register
            </a>
        </div>
    </div>

    {{-- Validation Error Alerts --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3" style="border-radius: 5px;">
            <div class="d-flex align-items-center gap-2 fw-semibold mb-1">
                <i class="fas fa-circle-exclamation"></i> Capitalization form encountered errors:
            </div>
            <ul class="mb-0 ps-3 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @php
        $formActionUrl = Route::has('admin.procurement-assets.store') 
            ? route('admin.procurement-assets.store') 
            : (Route::has('admin.asset-procurements.store') ? route('admin.asset-procurements.store') : url('admin/procurement/procurement-assets'));
    @endphp

    <form action="{{ $formActionUrl }}" method="POST" enctype="multipart/form-data" id="assetForm">
        @csrf

        {{-- Section 1: Identification & Procurement Sourcing --}}
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 5px;">
            <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h6 class="fw-bold mb-0 text-dark" style="font-size: 13.5px;">
                    <i class="fas fa-barcode text-primary me-2"></i>Asset Identification &amp; Purchase Sourcing
                </h6>

                {{-- Sourcing Purchase Bill Selector --}}
                @if(isset($purchases) && $purchases->count() > 0)
                    <div class="d-flex align-items-center gap-2">
                        <label for="purchase_id" class="small fw-semibold text-secondary mb-0">Link Purchase Bill:</label>
                        <select name="purchase_id" id="purchase_id" class="form-select form-select-sm" style="width: 260px; border-radius: 5px;">
                            <option value="">Direct Capitalization (No Bill Link)</option>
                            @foreach($purchases as $bill)
                                <option value="{{ $bill->id }}" 
                                        data-supplier="{{ $bill->supplier_id }}"
                                        data-amount="{{ (float)$bill->grand_total }}"
                                        data-date="{{ $bill->purchase_date }}"
                                        {{ old('purchase_id', request('purchase_id')) == $bill->id ? 'selected' : '' }}>
                                    #{{ $bill->purchase_number }} &mdash; ৳{{ number_format((float)$bill->grand_total, 2) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>

            <div class="card-body p-3">
                <div class="row g-3">
                    {{-- Asset Tag / Code --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold small">Asset Tag / Barcode <span class="text-danger">*</span></label>
                        <input type="text" name="asset_code" class="form-control form-control-sm font-monospace fw-bold" 
                               value="{{ old('asset_code', 'AST-' . date('Ymd') . '-' . strtoupper(Str::random(4))) }}" required>
                    </div>

                    {{-- Asset Name --}}
                    <div class="col-12 col-md-5">
                        <label class="form-label fw-semibold small">Asset Title &amp; Description <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm" placeholder="Industrial Sewing Machine Model J-400 / Core i7 Server" value="{{ old('name') }}" required>
                    </div>

                    {{-- Asset Category --}}
                    <div class="col-6 col-md-4">
                        <label class="form-label fw-semibold small">Asset Category <span class="text-danger">*</span></label>
                        <select name="asset_category_id" id="asset_category_id" class="form-select form-select-sm" required>
                            <option value="">Select Asset Category</option>
                            @foreach($assetCategories ?? [] as $category)
                                <option value="{{ $category->id }}" 
                                        data-rate="{{ (float) ($category->default_depreciation_rate ?? 10) }}"
                                        data-life="{{ (int) ($category->default_useful_life ?? 5) }}"
                                        {{ old('asset_category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Brand / Manufacturer --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold small">Brand / Make</label>
                        <input type="text" name="brand" class="form-control form-control-sm" placeholder="Brother / Dell / Toyota" value="{{ old('brand') }}">
                    </div>

                    {{-- Model Number --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold small">Model Number</label>
                        <input type="text" name="model_no" class="form-control form-control-sm" placeholder="Inspiron 5420 / DDL-8700" value="{{ old('model_no') }}">
                    </div>

                    {{-- Serial Number --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold small">Serial Number / Chassis #</label>
                        <input type="text" name="serial_number" class="form-control form-control-sm font-monospace" placeholder="SN-88402941-X" value="{{ old('serial_number') }}">
                    </div>

                    {{-- Sourcing Supplier --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold small">Vendor / Source Supplier <span class="text-danger">*</span></label>
                        <select name="supplier_id" id="supplier_id" class="form-select form-select-sm" required>
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id', request('supplier_id')) == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }} {{ $supplier->company_name ? "({$supplier->company_name})" : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Purchase Date --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold small">Acquisition Date <span class="text-danger">*</span></label>
                        <input type="date" name="purchase_date" id="purchase_date" class="form-control form-control-sm" value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                    </div>

                    {{-- In-Service / Commissioning Date --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold small">In-Service Date (Depreciation Starts) <span class="text-danger">*</span></label>
                        <input type="date" name="in_service_date" id="in_service_date" class="form-control form-control-sm" value="{{ old('in_service_date', date('Y-m-d')) }}" required>
                    </div>

                    {{-- Operating Branch --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold small">Assigned Branch <span class="text-danger">*</span></label>
                        <select name="branch_id" class="form-select form-select-sm" required>
                            <option value="">HQ Branch</option>
                            @foreach($branches ?? [] as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Physical Placement Location --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold small">Physical Location / Room / Floor</label>
                        <input type="text" name="location" class="form-control form-control-sm" placeholder="Floor 2, Sewing Line 4 / Server Rack 3" value="{{ old('location') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 2: Capitalization Valuation & Additional Costs --}}
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 5px;">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark" style="font-size: 13.5px;">
                    <i class="fas fa-coins text-primary me-2"></i>Financial Capitalization &amp; Acquisition Valuation
                </h6>
            </div>
            <div class="card-body p-3">
                <div class="row g-3">
                    {{-- Invoice Purchase Cost --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold small">Invoice Base Cost (৳) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="purchase_cost" id="purchase_cost" class="form-control form-control-sm text-end fw-semibold" placeholder="0.00" value="{{ old('purchase_cost', 0) }}" min="0.01" required>
                    </div>

                    {{-- Installation / Commissioning Expense --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold small">Installation &amp; Commissioning (৳)</label>
                        <input type="number" step="0.01" name="installation_cost" id="installation_cost" class="form-control form-control-sm text-end" placeholder="0.00" value="{{ old('installation_cost', 0) }}" min="0">
                    </div>

                    {{-- Freight / Carriage Inward --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold small">Freight / Carriage Inward (৳)</label>
                        <input type="number" step="0.01" name="shipping_cost" id="shipping_cost" class="form-control form-control-sm text-end" placeholder="0.00" value="{{ old('shipping_cost', 0) }}" min="0">
                    </div>

                    {{-- Estimated Salvage / Residual Value --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold small">Estimated Salvage / Scrap Value (৳)</label>
                        <input type="number" step="0.01" name="salvage_value" id="salvage_value" class="form-control form-control-sm text-end text-muted" placeholder="0.00" value="{{ old('salvage_value', 0) }}" min="0">
                    </div>

                    {{-- Total Capitalized Asset Value Display --}}
                    <div class="col-12 col-md-6">
                        <div class="p-3 bg-light border rounded d-flex justify-content-between align-items-center" style="border-radius: 5px;">
                            <div>
                                <div class="fw-bold text-dark fs-6">Total Capitalized Value:</div>
                                <small class="text-muted">Total acquisition and setup cost added to the balance sheet</small>
                            </div>
                            <div class="fw-bold text-primary fs-4" id="displayCapitalizedCost">৳0.00</div>
                            <input type="hidden" name="capitalized_cost" id="capitalized_cost" value="0.00">
                        </div>
                    </div>

                    {{-- Depreciable Base Display --}}
                    <div class="col-12 col-md-6">
                        <div class="p-3 bg-light border rounded d-flex justify-content-between align-items-center" style="border-radius: 5px;">
                            <div>
                                <div class="fw-bold text-dark fs-6">Depreciable Base:</div>
                                <small class="text-muted">Capitalized Value minus Estimated Salvage Value</small>
                            </div>
                            <div class="fw-bold text-dark fs-4" id="displayDepreciableBase">৳0.00</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 3: Depreciation Schedule & General Ledger Mapping --}}
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 5px;">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark" style="font-size: 13.5px;">
                    <i class="fas fa-chart-line text-primary me-2"></i>Depreciation Policy &amp; Ledger Account Mapping
                </h6>
            </div>
            <div class="card-body p-3">
                <div class="row g-3">
                    {{-- Depreciation Method --}}
                    <div class="col-6 col-md-4">
                        <label class="form-label fw-semibold small">Depreciation Method <span class="text-danger">*</span></label>
                        <select name="depreciation_method" id="depreciation_method" class="form-select form-select-sm" required>
                            <option value="straight_line" {{ old('depreciation_method', 'straight_line') === 'straight_line' ? 'selected' : '' }}>Straight-Line Method (SLM)</option>
                            <option value="reducing_balance" {{ old('depreciation_method') === 'reducing_balance' ? 'selected' : '' }}>Reducing / Diminishing Balance (WDV)</option>
                            <option value="none" {{ old('depreciation_method') === 'none' ? 'selected' : '' }}>Non-Depreciable (Freehold Land)</option>
                        </select>
                    </div>

                    {{-- Useful Life (Years) --}}
                    <div class="col-6 col-md-2" id="usefulLifeCol">
                        <label class="form-label fw-semibold small">Useful Life (Years) <span class="text-danger">*</span></label>
                        <input type="number" step="0.5" name="useful_life_years" id="useful_life_years" class="form-control form-control-sm text-center fw-bold" value="{{ old('useful_life_years', 5) }}" min="0.5" required>
                    </div>

                    {{-- Annual Depreciation Rate (%) --}}
                    <div class="col-6 col-md-2" id="deprRateCol">
                        <label class="form-label fw-semibold small">Annual Rate (%)</label>
                        <input type="number" step="0.01" name="depreciation_rate" id="depreciation_rate" class="form-control form-control-sm text-center" value="{{ old('depreciation_rate', 20) }}" min="0" max="100">
                    </div>

                    {{-- Estimated Annual Depreciation Preview --}}
                    <div class="col-6 col-md-4" id="annualDeprPreviewCol">
                        <label class="form-label fw-semibold small">Estimated Annual Depr.</label>
                        <div class="form-control form-control-sm text-end fw-bold text-danger bg-light" id="displayAnnualDepr">৳0.00 / year</div>
                    </div>

                    {{-- Fixed Asset Account (Debit) --}}
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold small">Fixed Asset Ledger Account <span class="text-danger">*</span></label>
                        <select name="asset_account_id" class="form-select form-select-sm" required>
                            <option value="">Select Asset Head</option>
                            @foreach($assetAccounts ?? [] as $acc)
                                <option value="{{ $acc->id }}" {{ old('asset_account_id') == $acc->id ? 'selected' : '' }}>
                                    {{ $acc->code ? $acc->code . ' - ' : '' }}{{ $acc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Accumulated Depreciation Account (Contra-Asset) --}}
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold small">Accumulated Depreciation Head <span class="text-danger">*</span></label>
                        <select name="accumulated_depreciation_account_id" class="form-select form-select-sm" required>
                            <option value="">Select Contra-Asset Head</option>
                            @foreach($accumulatedDeprAccounts ?? [] as $acc)
                                <option value="{{ $acc->id }}" {{ old('accumulated_depreciation_account_id') == $acc->id ? 'selected' : '' }}>
                                    {{ $acc->code ? $acc->code . ' - ' : '' }}{{ $acc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Depreciation Expense Account --}}
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold small">Depreciation Expense Head <span class="text-danger">*</span></label>
                        <select name="depreciation_expense_account_id" class="form-select form-select-sm" required>
                            <option value="">Select Expense Head</option>
                            @foreach($deprExpenseAccounts ?? [] as $acc)
                                <option value="{{ $acc->id }}" {{ old('depreciation_expense_account_id') == $acc->id ? 'selected' : '' }}>
                                    {{ $acc->code ? $acc->code . ' - ' : '' }}{{ $acc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 4: Physical Custody, Warranty & Attachments --}}
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 5px;">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark" style="font-size: 13.5px;">
                    <i class="fas fa-shield-halved text-primary me-2"></i>Physical Custody, Warranty &amp; Documents
                </h6>
            </div>
            <div class="card-body p-3">
                <div class="row g-3">
                    {{-- Assigned Department --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold small">Department</label>
                        <select name="department_id" class="form-select form-select-sm">
                            <option value="">Select Department</option>
                            @foreach($departments ?? [] as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Assigned Custodian / Employee --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold small">Custodian / Handed Over To</label>
                        <select name="custodian_id" class="form-select form-select-sm">
                            <option value="">Select Staff Custodian</option>
                            @foreach($employees ?? [] as $emp)
                                <option value="{{ $emp->id }}" {{ old('custodian_id') == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->name }} ({{ $emp->employee_id ?? 'Staff' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Warranty Expiry Date --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold small">Warranty Expiry Date</label>
                        <input type="date" name="warranty_expiry_date" class="form-control form-control-sm" value="{{ old('warranty_expiry_date') }}">
                    </div>

                    {{-- Insurance / AMC Policy # --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold small">Insurance / AMC Reference</label>
                        <input type="text" name="insurance_policy_no" class="form-control form-control-sm" placeholder="POL-99201-INS" value="{{ old('insurance_policy_no') }}">
                    </div>

                    {{-- Supporting Documents --}}
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold small">Warranty Card / Commissioning Certificate</label>
                        <input type="file" name="attachment_file" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                    </div>

                    {{-- Extended Notes --}}
                    <div class="col-12 col-md-8">
                        <label class="form-label fw-semibold small">Asset Remarks &amp; Specifications</label>
                        <input type="text" name="notes" class="form-control form-control-sm" placeholder="Power requirements, maintenance schedules, condition remarks..." value="{{ old('notes') }}">
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white border-top py-3 d-flex justify-content-end gap-2">
                <a href="{{ $indexUrl }}" class="btn btn-sm btn-secondary" style="border-radius: 5px;">Cancel</a>
                <button type="submit" class="btn btn-sm btn-primary px-4" style="border-radius: 5px;">
                    <i class="fas fa-check-circle me-1"></i> Capitalize &amp; Record Asset
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const purchaseCostInput = document.getElementById('purchase_cost');
    const installCostInput = document.getElementById('installation_cost');
    const shippingCostInput = document.getElementById('shipping_cost');
    const salvageValueInput = document.getElementById('salvage_value');

    const displayCapitalized = document.getElementById('displayCapitalizedCost');
    const hiddenCapitalized = document.getElementById('capitalized_cost');
    const displayDepreciable = document.getElementById('displayDepreciableBase');

    const deprMethodSelect = document.getElementById('depreciation_method');
    const usefulLifeInput = document.getElementById('useful_life_years');
    const deprRateInput = document.getElementById('depreciation_rate');
    const displayAnnualDepr = document.getElementById('displayAnnualDepr');

    const categorySelect = document.getElementById('asset_category_id');
    const purchaseBillSelect = document.getElementById('purchase_id');
    const supplierSelect = document.getElementById('supplier_id');

    function calculateValuations() {
        const purchaseCost = parseFloat(purchaseCostInput.value) || 0;
        const installCost = parseFloat(installCostInput.value) || 0;
        const shippingCost = parseFloat(shippingCostInput.value) || 0;
        const salvageValue = parseFloat(salvageValueInput.value) || 0;

        const totalCapitalized = Math.max(0, purchaseCost + installCost + shippingCost);
        const depreciableBase = Math.max(0, totalCapitalized - salvageValue);

        displayCapitalized.innerText = '৳' + totalCapitalized.toFixed(2);
        hiddenCapitalized.value = totalCapitalized.toFixed(2);
        displayDepreciable.innerText = '৳' + depreciableBase.toFixed(2);

        const method = deprMethodSelect.value;
        const usefulLife = parseFloat(usefulLifeInput.value) || 0;
        let annualDepr = 0;

        if (method === 'straight_line' && usefulLife > 0) {
            annualDepr = depreciableBase / usefulLife;
        } else if (method === 'reducing_balance') {
            const rate = parseFloat(deprRateInput.value) || 0;
            annualDepr = totalCapitalized * (rate / 100);
        }

        displayAnnualDepr.innerText = '৳' + annualDepr.toFixed(2) + ' / year';
    }

    deprMethodSelect.addEventListener('change', function () {
        const isNone = this.value === 'none';
        document.getElementById('usefulLifeCol').style.display = isNone ? 'none' : 'block';
        document.getElementById('deprRateCol').style.display = isNone ? 'none' : 'block';
        document.getElementById('annualDeprPreviewCol').style.display = isNone ? 'none' : 'block';
        calculateValuations();
    });

    usefulLifeInput.addEventListener('input', function () {
        const life = parseFloat(this.value) || 0;
        if (life > 0 && deprMethodSelect.value === 'straight_line') {
            deprRateInput.value = (100 / life).toFixed(2);
        }
        calculateValuations();
    });

    deprRateInput.addEventListener('input', function () {
        const rate = parseFloat(this.value) || 0;
        if (rate > 0 && deprMethodSelect.value === 'straight_line') {
            usefulLifeInput.value = (100 / rate).toFixed(1);
        }
        calculateValuations();
    });

    categorySelect.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        if (opt && opt.dataset.rate) {
            deprRateInput.value = opt.dataset.rate;
        }
        if (opt && opt.dataset.life) {
            usefulLifeInput.value = opt.dataset.life;
        }
        calculateValuations();
    });

    if (purchaseBillSelect) {
        purchaseBillSelect.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            if (opt && opt.value) {
                const supplierId = opt.getAttribute('data-supplier');
                const amount = parseFloat(opt.getAttribute('data-amount')) || 0;
                const billDate = opt.getAttribute('data-date');

                if (supplierId && supplierSelect) supplierSelect.value = supplierId;
                if (amount > 0) purchaseCostInput.value = amount.toFixed(2);
                if (billDate) document.getElementById('purchase_date').value = billDate;
                calculateValuations();
            }
        });
    }

    [purchaseCostInput, installCostInput, shippingCostInput, salvageValueInput].forEach(elem => {
        elem.addEventListener('input', calculateValuations);
    });

    calculateValuations();
    if (purchaseBillSelect && purchaseBillSelect.value) {
        purchaseBillSelect.dispatchEvent(new Event('change'));
    }
});
</script>

<style>
    .asset-capitalization-page {
        --asset-primary: var(--bs-primary, #1572e8);
        --asset-border: #e6eaf0;
        --asset-muted: #697586;
        color: #344054;
        font-size: 13px;
    }
    .asset-capitalization-page > .d-flex:first-child {
        padding: 2px 0 14px;
        border-bottom: 1px solid var(--asset-border);
    }
    .asset-capitalization-page h5 {
        font-size: clamp(1.05rem, 1.4vw, 1.3rem);
        letter-spacing: -.02em;
    }
    .asset-capitalization-page .breadcrumb {
        font-size: 12px !important;
    }
    .asset-capitalization-page .card {
        border: 1px solid var(--asset-border) !important;
        border-radius: 10px !important;
        box-shadow: 0 2px 8px rgba(16, 24, 40, .045) !important;
        overflow: visible;
    }
    .asset-capitalization-page .card-header {
        padding: 14px 18px !important;
        border-bottom-color: var(--asset-border) !important;
        border-radius: 10px 10px 0 0 !important;
    }
    .asset-capitalization-page .card-header h6 {
        font-size: 14px !important;
        letter-spacing: -.01em;
    }
    .asset-capitalization-page .card-body {
        padding: 18px !important;
    }
    .asset-capitalization-page .card-footer {
        border-radius: 0 0 10px 10px !important;
        border-top-color: var(--asset-border) !important;
        padding: 14px 18px !important;
    }
    .asset-capitalization-page .form-label {
        display: inline-block;
        margin-bottom: 6px;
        color: #475467;
        font-size: 12px !important;
        font-weight: 600 !important;
        line-height: 1.45;
    }
    .asset-capitalization-page .form-control,
    .asset-capitalization-page .form-select {
        min-height: 38px;
        border: 1px solid #d8dee8;
        border-radius: 7px;
        background-color: #fff;
        color: #344054;
        font-size: 13px;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .asset-capitalization-page .form-control:focus,
    .asset-capitalization-page .form-select:focus {
        border-color: var(--asset-primary);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .10);
    }
    .asset-capitalization-page .form-control::placeholder {
        color: #98a2b3;
        opacity: 1;
    }
    .asset-capitalization-page .btn {
        min-height: 36px;
        border-radius: 7px !important;
        padding: 7px 13px;
        font-size: 12px;
        font-weight: 600;
    }
    .asset-capitalization-page .alert {
        border-radius: 9px !important;
    }
    .asset-capitalization-page #displayCapitalizedCost,
    .asset-capitalization-page #displayDepreciableBase {
        white-space: nowrap;
        font-size: clamp(1.15rem, 2vw, 1.55rem) !important;
        letter-spacing: -.025em;
    }
    .asset-capitalization-page #purchase_id {
        width: 260px;
        max-width: 100%;
    }
    .asset-capitalization-page .bg-light.border.rounded {
        min-height: 84px;
        gap: 12px;
        border-color: var(--asset-border) !important;
        border-radius: 9px !important;
        background: #f8fafc !important;
    }
    .asset-capitalization-page .card,
    .asset-capitalization-page .card-header,
    .asset-capitalization-page .card-body,
    .asset-capitalization-page .card-footer {
        min-width: 0;
    }
    @media (max-width: 767.98px) {
        .asset-capitalization-page {
            padding-right: 10px !important;
            padding-left: 10px !important;
        }
        .asset-capitalization-page > .d-flex:first-child {
            align-items: flex-start !important;
        }
        .asset-capitalization-page > .d-flex:first-child > div:first-child {
            width: 100%;
        }
        .asset-capitalization-page > .d-flex:first-child > div:last-child {
            width: 100%;
        }
        .asset-capitalization-page > .d-flex:first-child .btn {
            width: 100%;
        }
        .asset-capitalization-page .card-header,
        .asset-capitalization-page .card-body {
            padding: 14px !important;
        }
        .asset-capitalization-page .card-header > .d-flex {
            width: 100%;
            align-items: stretch !important;
            flex-direction: column;
        }
        .asset-capitalization-page .card-header > .d-flex select {
            width: 100% !important;
        }
        .asset-capitalization-page .bg-light.border.rounded {
            align-items: flex-start !important;
            flex-direction: column;
        }
        .asset-capitalization-page #displayCapitalizedCost,
        .asset-capitalization-page #displayDepreciableBase {
            white-space: normal;
        }
        .asset-capitalization-page .card-footer {
            flex-wrap: wrap;
            padding: 12px 14px !important;
        }
        .asset-capitalization-page .card-footer .btn {
            flex: 1 1 auto;
        }
    }
    @media (max-width: 420px) {
        .asset-capitalization-page .row.g-3 {
            --bs-gutter-x: .65rem;
            --bs-gutter-y: .85rem;
        }
        .asset-capitalization-page .form-control,
        .asset-capitalization-page .form-select {
            font-size: 12px;
        }
        .asset-capitalization-page .card-header h6 {
            font-size: 13px !important;
        }
    }
    @media print {
        .asset-capitalization-page .card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
        }
    }
</style>

@endsection
