@extends('layouts.admin')

@section('title', 'Product Branch Availability - ' . ($product->name ?? $product->title))

@section('admin_content')
<style>
    /* Global Strict 5px Element Radius */
    #productAvailabilityRoot .btn,
    #productAvailabilityRoot .btn-sm,
    #productAvailabilityRoot button,
    #productAvailabilityRoot .badge,
    #productAvailabilityRoot .form-control,
    #productAvailabilityRoot .form-select,
    #productAvailabilityRoot .input-group-text,
    #productAvailabilityRoot .card,
    #productAvailabilityRoot .alert,
    #productAvailabilityRoot .table,
    #productAvailabilityRoot .table th,
    #productAvailabilityRoot .table td {
        border-radius: 5px !important;
    }

    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
    }

    .btn-compact {
        font-size: 11.5px !important;
        padding: 5px 12px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    .mode-selection-card {
        border: 2px solid #e2e8f0;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #ffffff;
    }
    .mode-selection-card:hover {
        border-color: #94a3b8;
        background: #f8fafc;
    }
    .mode-selection-card.active {
        border-color: #2563eb !important;
        background: #eff6ff !important;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.12);
    }
    .mode-selection-card input[type="radio"] {
        accent-color: #2563eb;
        transform: scale(1.15);
    }

    .table-input-xs {
        height: 28px !important;
        padding: 2px 6px !important;
        font-size: 11px !important;
    }
</style>

<div class="container-fluid py-2 px-2 px-md-3" id="productAvailabilityRoot">
    <!-- Top Breadcrumb & Action Header -->
    <div class="d-flex justify-content-between align-items-center mb-3 bg-white p-2.5 px-3 shadow-xs border">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge bg-primary text-white fw-bold px-2.5 py-1.5" style="font-size: 12px;">
                <i class="fas fa-shield-alt me-1"></i> Access Policy
            </span>
            <span class="fw-bold text-dark fs-6">{{ $product->name ?? $product->title }}</span>
            <span class="badge bg-light text-dark border font-monospace" style="font-size: 11px;">
                SKU: {{ $product->sku ?? $product->product_code }}
            </span>
            <span class="badge bg-success-subtle text-success border font-monospace" style="font-size: 11px;">
                Base Price: ৳{{ number_format($product->effective_price, 2) }}
            </span>
        </div>

        <div class="d-flex align-items-center gap-1.5">
            <a href="{{ route('admin.branch_product.index') }}" class="btn btn-outline-secondary btn-compact py-1 px-2.5">
                <i class="fas fa-arrow-left me-1"></i> Back to Catalog
            </a>
            <a href="{{ route('admin.product.edit', $product->id) }}" class="btn btn-light border btn-compact py-1 px-2.5">
                <i class="fas fa-edit me-1"></i> Edit Product
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 mb-3" style="font-size: 12px;">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 mb-3" style="font-size: 12px;">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.branch_product.availability.update', $product->id) }}" method="POST">
        @csrf

        @php
            $currentMode = strtolower($product->branch_availability_mode ?? 'all');
        @endphp

        <!-- 1. Five Availability Policy Modes Selection Cards -->
        <div class="card border shadow-xs bg-white p-3 mb-3">
            <h6 class="fw-bold text-dark border-bottom pb-2 mb-2.5" style="font-size: 13px;">
                <i class="fas fa-network-wired text-primary me-1"></i> Step 1: Select Branch Availability Policy Mode
            </h6>
            <p class="text-muted small mb-3" style="font-size: 11.5px;">
                Choose how this product is distributed across regional outlets, retail stores, head office and POS counters.
            </p>

            <div class="row g-2">
                <!-- Mode A: All Branches -->
                <div class="col-12 col-md-6 col-lg-4">
                    <label class="card p-2.5 mode-selection-card h-100 {{ $currentMode === 'all' ? 'active' : '' }}" for="mode_all">
                        <div class="d-flex align-items-start gap-2">
                            <input type="radio" name="branch_availability_mode" id="mode_all" value="all" class="mt-1" {{ $currentMode === 'all' ? 'checked' : '' }} onchange="onModeChanged('all')">
                            <div>
                                <strong class="text-dark d-block" style="font-size: 12px;">Mode A: All Branches</strong>
                                <small class="text-muted d-block" style="font-size: 10.5px; line-height: 1.35;">
                                    Product is automatically sellable and visible across all current and future operational branches.
                                </small>
                            </div>
                        </div>
                    </label>
                </div>

                <!-- Mode B: Selected Branches -->
                <div class="col-12 col-md-6 col-lg-4">
                    <label class="card p-2.5 mode-selection-card h-100 {{ $currentMode === 'selected' ? 'active' : '' }}" for="mode_selected">
                        <div class="d-flex align-items-start gap-2">
                            <input type="radio" name="branch_availability_mode" id="mode_selected" value="selected" class="mt-1" {{ $currentMode === 'selected' ? 'checked' : '' }} onchange="onModeChanged('selected')">
                            <div>
                                <strong class="text-dark d-block" style="font-size: 12px;">Mode B: Selected Branches Only</strong>
                                <small class="text-muted d-block" style="font-size: 10.5px; line-height: 1.35;">
                                    Strictly restricted to explicitly assigned outlets. You can toggle POS and sell permissions per branch below.
                                </small>
                            </div>
                        </div>
                    </label>
                </div>

                <!-- Mode C: Main Branch Only -->
                <div class="col-12 col-md-6 col-lg-4">
                    <label class="card p-2.5 mode-selection-card h-100 {{ $currentMode === 'main_only' ? 'active' : '' }}" for="mode_main">
                        <div class="d-flex align-items-start gap-2">
                            <input type="radio" name="branch_availability_mode" id="mode_main" value="main_only" class="mt-1" {{ $currentMode === 'main_only' ? 'checked' : '' }} onchange="onModeChanged('main_only')">
                            <div>
                                <strong class="text-dark d-block" style="font-size: 12px;">Mode C: Main Showroom Only</strong>
                                <small class="text-muted d-block" style="font-size: 10.5px; line-height: 1.35;">
                                    Exclusive to the designated flagship store / main branch. Sub-branches cannot sell this item.
                                </small>
                            </div>
                        </div>
                    </label>
                </div>

                <!-- Mode D: Head Office Only -->
                <div class="col-12 col-md-6 col-lg-4">
                    <label class="card p-2.5 mode-selection-card h-100 {{ $currentMode === 'head_office_only' ? 'active' : '' }}" for="mode_ho">
                        <div class="d-flex align-items-start gap-2">
                            <input type="radio" name="branch_availability_mode" id="mode_ho" value="head_office_only" class="mt-1" {{ $currentMode === 'head_office_only' ? 'checked' : '' }} onchange="onModeChanged('head_office_only')">
                            <div>
                                <strong class="text-dark d-block" style="font-size: 12px;">Mode D: Head Office / HQ Only</strong>
                                <small class="text-muted d-block" style="font-size: 10.5px; line-height: 1.35;">
                                    Central HQ warehouse & online orders only. In-store retail POS counters are blocked.
                                </small>
                            </div>
                        </div>
                    </label>
                </div>

                <!-- Mode E: Restricted -->
                <div class="col-12 col-md-6 col-lg-4">
                    <label class="card p-2.5 mode-selection-card h-100 {{ $currentMode === 'restricted' ? 'active' : '' }}" for="mode_restricted">
                        <div class="d-flex align-items-start gap-2">
                            <input type="radio" name="branch_availability_mode" id="mode_restricted" value="restricted" class="mt-1" {{ $currentMode === 'restricted' ? 'checked' : '' }} onchange="onModeChanged('restricted')">
                            <div>
                                <strong class="text-dark d-block" style="font-size: 12px;">Mode E: Restricted / Private Mode</strong>
                                <small class="text-muted d-block" style="font-size: 10.5px; line-height: 1.35;">
                                    Entirely hidden from standard POS searches unless explicitly whitelisted with customized parameters.
                                </small>
                            </div>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- 2. Granular Branch Assignment & Parameter Control Table -->
        <div class="card border shadow-xs bg-white p-3 mb-3" id="branchAssignmentContainer" style="{{ in_array($currentMode, ['selected', 'restricted']) ? '' : 'display: none;' }}">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2 flex-wrap gap-2">
                <div>
                    <h6 class="fw-bold text-dark mb-0" style="font-size: 13px;">
                        <i class="fas fa-store-alt text-success me-1"></i> Step 2: Assign Outlets &amp; Operational Permissions
                    </h6>
                    <small class="text-muted" style="font-size: 10.5px;">Check branches to assign this product and tune specific POS or stock alert flags.</small>
                </div>

                <div class="d-flex align-items-center gap-1.5">
                    <button type="button" class="btn btn-outline-primary btn-compact py-0.5 px-2" onclick="toggleSelectAllBranches(true)">
                        <i class="fas fa-check-double me-1"></i> Select All
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-compact py-0.5 px-2" onclick="toggleSelectAllBranches(false)">
                        <i class="fas fa-times me-1"></i> Deselect All
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0" style="font-size: 11.5px;">
                    <thead class="table-light">
                        <tr>
                            <th class="py-2 text-center" style="width: 40px;">Assign</th>
                            <th class="py-2">Branch / Outlet</th>
                            <th class="py-2">Type</th>
                            <th class="py-2 text-center" style="width: 110px;">Can Sell</th>
                            <th class="py-2 text-center" style="width: 110px;">POS Enabled</th>
                            <th class="py-2 text-center" style="width: 130px;">Alert Qty</th>
                            <th class="py-2" style="width: 150px;">Branch SKU</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($branches as $b)
                            @php
                                $isAssigned = in_array($b->id, $assignedBranchIds);
                                $pivot = $pivotSettings[$b->id] ?? null;
                                $canSell = $pivot ? (bool)$pivot['can_sell'] : true;
                                $posEnabled = $pivot ? (bool)$pivot['is_pos_enabled'] : true;
                                $lowAlert = $pivot['low_stock_alert'] ?? ($product->alert_quantity ?? 5);
                                $branchSku = $pivot['branch_sku'] ?? '';
                            @endphp
                            <tr id="row_branch_{{ $b->id }}" class="{{ $isAssigned ? 'table-primary-subtle' : '' }}">
                                <!-- Checkbox -->
                                <td class="py-2 text-center">
                                    <input type="checkbox" name="branches[]" value="{{ $b->id }}" id="chk_{{ $b->id }}" 
                                           class="form-check-input branch-checkbox" {{ $isAssigned ? 'checked' : '' }} 
                                           onchange="onBranchCheckChanged({{ $b->id }}, this.checked)">
                                </td>

                                <!-- Branch Info -->
                                <td class="py-2">
                                    <label class="fw-bold text-dark d-block mb-0 cursor-pointer" for="chk_{{ $b->id }}">
                                        {{ $b->name }}
                                    </label>
                                    <small class="text-muted font-monospace bn-digit">Code: {{ $b->code ?? $b->branch_code }}</small>
                                </td>

                                <!-- Branch Type -->
                                <td class="py-2">
                                    <span class="badge bg-light text-dark border font-monospace" style="font-size: 10px;">
                                        {{ strtoupper($b->type ?? ($b->branch_type ?? 'RETAIL')) }}
                                    </span>
                                </td>

                                <!-- Can Sell Toggle -->
                                <td class="py-2 text-center">
                                    <div class="form-check form-switch d-inline-block mb-0">
                                        <input class="form-check-input branch-toggle-{{ $b->id }}" type="checkbox" 
                                               name="pivot_data[{{ $b->id }}][can_sell]" value="1" 
                                               {{ $canSell ? 'checked' : '' }} {{ !$isAssigned ? 'disabled' : '' }}>
                                    </div>
                                </td>

                                <!-- POS Enabled Toggle -->
                                <td class="py-2 text-center">
                                    <div class="form-check form-switch d-inline-block mb-0">
                                        <input class="form-check-input branch-toggle-{{ $b->id }}" type="checkbox" 
                                               name="pivot_data[{{ $b->id }}][is_pos_enabled]" value="1" 
                                               {{ $posEnabled ? 'checked' : '' }} {{ !$isAssigned ? 'disabled' : '' }}>
                                    </div>
                                </td>

                                <!-- Low Stock Threshold -->
                                <td class="py-2 text-center">
                                    <input type="number" name="pivot_data[{{ $b->id }}][low_stock_alert]" 
                                           class="form-control form-control-sm table-input-xs font-monospace text-center mx-auto branch-input-{{ $b->id }}" 
                                           value="{{ $lowAlert }}" min="1" max="9999" {{ !$isAssigned ? 'disabled' : '' }} style="max-width: 80px;">
                                </td>

                                <!-- Branch-specific Custom SKU -->
                                <td class="py-2">
                                    <input type="text" name="pivot_data[{{ $b->id }}][branch_sku]" 
                                           class="form-control form-control-sm table-input-xs font-monospace branch-input-{{ $b->id }}" 
                                           placeholder="Optional SKU" value="{{ $branchSku }}" {{ !$isAssigned ? 'disabled' : '' }}>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sticky Submit Footer Bar -->
        <div class="d-flex justify-content-between align-items-center bg-white p-3 border shadow-xs" style="border-radius: 5px;">
            <div class="text-muted small" style="font-size: 11.5px;">
                <i class="fas fa-info-circle text-primary me-1"></i> Saving will immediately update catalog visibility across online shop and POS terminals.
            </div>

            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.branch_product.index') }}" class="btn btn-light border btn-compact py-2 px-3">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary fw-bold btn-compact py-2 px-4 fs-6">
                    <i class="fas fa-save me-1"></i> Save Access Policy
                </button>
            </div>
        </div>
    </form>
</div>

<script>
// Mode Change Handler
function onModeChanged(mode) {
    // 1. Highlight visual cards
    document.querySelectorAll('.mode-selection-card').forEach(card => card.classList.remove('active'));
    const selectedRadio = document.querySelector(`input[name="branch_availability_mode"][value="${mode}"]`);
    if (selectedRadio) {
        selectedRadio.closest('.mode-selection-card').classList.add('active');
    }

    // 2. Toggle Branch Assignment Table Container
    const tableContainer = document.getElementById('branchAssignmentContainer');
    if (mode === 'selected' || mode === 'restricted') {
        tableContainer.style.display = 'block';
        tableContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
        tableContainer.style.display = 'none';
    }
}

// Branch Checkbox Toggle Row Enable/Disable
function onBranchCheckChanged(branchId, isChecked) {
    const row = document.getElementById('row_branch_' + branchId);
    const toggles = document.querySelectorAll('.branch-toggle-' + branchId);
    const inputs  = document.querySelectorAll('.branch-input-' + branchId);

    if (isChecked) {
        row.classList.add('table-primary-subtle');
        toggles.forEach(el => el.disabled = false);
        inputs.forEach(el => el.disabled = false);
    } else {
        row.classList.remove('table-primary-subtle');
        toggles.forEach(el => el.disabled = true);
        inputs.forEach(el => el.disabled = true);
    }
}

// Select All / Deselect All
function toggleSelectAllBranches(select) {
    const checkboxes = document.querySelectorAll('.branch-checkbox');
    checkboxes.forEach(chk => {
        chk.checked = select;
        onBranchCheckChanged(chk.value, select);
    });
}
</script>
@endsection