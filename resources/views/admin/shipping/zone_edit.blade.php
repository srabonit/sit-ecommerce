@extends('layouts.admin')
@section('title', 'Edit Zone & Weight Rates - ' . config('app.name'))

@section('admin_content')
<style>
    /* Global Strict 5px Element Radius */
    #editZoneRoot .btn,
    #editZoneRoot .btn-sm,
    #editZoneRoot button,
    #editZoneRoot .form-control,
    #editZoneRoot .form-select,
    #editZoneRoot .input-group-text,
    #editZoneRoot .nav-link,
    #editZoneRoot .badge,
    #editZoneRoot .alert,
    #editZoneRoot .district-chip,
    #editZoneRoot .zone-collapsible-card,
    .zone-modal-scope .btn,
    .zone-modal-scope .form-control,
    .zone-modal-scope .form-select,
    .zone-modal-scope .modal-content {
        border-radius: 5px !important;
    }

    #editZoneRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #editZoneRoot .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Compact & Sleek Button Sizing */
    #editZoneRoot .btn-compact,
    .zone-modal-scope .btn-compact {
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

    .action-sq-btn {
        width: 28px !important;
        height: 28px !important;
        min-width: 28px !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 11px !important;
        border-radius: 5px !important;
        transition: all 0.15s ease;
    }

    /* Collapsible Enterprise Cards */
    .zone-collapsible-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        overflow: hidden;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .zone-collapsible-card.active-open {
        border-color: #cbd5e1;
    }
    .zone-card-header {
        padding: 12px 16px;
        background: #f8fafc;
        cursor: pointer;
        user-select: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        border-bottom: 1px solid transparent;
        transition: background-color 0.15s ease;
    }
    .zone-card-header:hover {
        background: #f1f5f9;
    }
    .zone-collapsible-card.active-open .zone-card-header {
        background: #f8fafc;
        border-bottom-color: #e2e8f0;
    }
    .sec-chevron {
        width: 24px;
        height: 24px;
        border-radius: 5px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        color: #475569;
        transition: transform 0.2s ease, background-color 0.15s ease, color 0.15s ease;
    }
    .zone-collapsible-card.active-open .sec-chevron {
        transform: rotate(180deg);
        background: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
    }
    .zone-card-body {
        padding: 16px;
        background: #ffffff;
    }

    /* Segmented Sub-Navigation Bar */
    .shipping-subnav-wrap {
        overflow-x: auto;
        white-space: nowrap;
        scrollbar-width: none;
        -ms-overflow-style: none;
        -webkit-overflow-scrolling: touch;
    }
    .shipping-subnav-wrap::-webkit-scrollbar {
        display: none;
    }
    .shipping-nav-pills {
        background: #f1f5f9;
        padding: 4px;
        border-radius: 6px !important;
        display: inline-flex;
        gap: 4px;
    }
    .shipping-nav-pills .nav-link {
        color: #475569;
        font-weight: 600;
        font-size: 12px;
        padding: 6px 14px;
        transition: all 0.15s ease;
        background: transparent;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .shipping-nav-pills .nav-link:hover {
        color: #0f172a;
        background-color: rgba(255, 255, 255, 0.6);
    }
    .shipping-nav-pills .nav-link.active {
        background-color: #2563eb !important;
        color: #ffffff !important;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.25);
    }

    /* Form Controls */
    #editZoneRoot .form-label,
    .zone-modal-scope .form-label {
        font-size: 11.5px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #editZoneRoot .form-control,
    #editZoneRoot .form-select,
    .zone-modal-scope .form-control,
    .zone-modal-scope .form-select {
        font-size: 12.5px;
        border: 1px solid #cbd5e1;
        padding: 6px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #editZoneRoot .form-control:focus,
    #editZoneRoot .form-select:focus,
    .zone-modal-scope .form-control:focus,
    .zone-modal-scope .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Tables (Weight Slabs & Shipping Methods) */
    .zone-table-wrap {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
        border: 1px solid #e2e8f0;
        border-radius: 6px !important;
    }
    .custom-zone-table {
        margin-bottom: 0;
        width: 100%;
    }
    .custom-zone-table th {
        background-color: #f8fafc;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 9px 12px;
        white-space: nowrap;
    }
    .custom-zone-table td {
        padding: 8px 12px;
        vertical-align: middle;
        font-size: 12.5px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }
    #weightSlabsTable {
        min-width: 560px;
    }
    #weightSlabsTable .form-control {
        height: 32px;
        font-size: 12px;
    }

    /* Interactive 64-District Chips */
    .district-chips-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        max-height: 175px;
        overflow-y: auto;
        padding: 2px;
        scrollbar-width: thin;
    }
    .district-chip {
        font-size: 11px;
        font-weight: 600;
        padding: 4px 10px;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        color: #334155;
        cursor: pointer;
        user-select: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.12s ease;
    }
    .district-chip:hover {
        border-color: #2563eb;
        color: #2563eb;
        background: #eff6ff;
    }
    .district-chip.selected {
        background: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
    }

    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    @media (max-width: 767.98px) {
        .shipping-nav-pills {
            display: flex;
            min-width: max-content;
        }
        .shipping-nav-pills .nav-link {
            padding: 6px 12px;
            font-size: 11.5px;
        }
        .zone-card-body {
            padding: 12px;
        }
        .top-back-btn-wrap {
            width: 100%;
        }
        .top-back-btn-wrap .btn-compact {
            width: 100%;
            padding: 7px 12px !important;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="editZoneRoot">
    <!-- Sub-Menu Navigation -->
    <div class="shipping-subnav-wrap mb-3 pb-2 border-bottom">
        <ul class="nav nav-pills shipping-nav-pills flex-nowrap text-nowrap">
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('admin.shipping.index') }}">
                    <i class="fas fa-globe-asia" style="font-size: 11px;"></i>
                    <span>Shipping zones</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.shipping.settings') }}">
                    <i class="fas fa-sliders-h" style="font-size: 11px;"></i>
                    <span>Shipping settings</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.shipping.classes') }}">
                    <i class="fas fa-boxes" style="font-size: 11px;"></i>
                    <span>Classes</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.shipping.pickup') }}">
                    <i class="fas fa-store" style="font-size: 11px;"></i>
                    <span>Local pickup</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Top Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-map-location-dot text-primary"></i>
                <span>Edit Zone: {{ $zone->name }}</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">Configure zone locations, weight-based delivery slabs, product overrides, and flat/free shipping methods.</small>
        </div>
        <div class="top-back-btn-wrap">
            <a href="{{ route('admin.shipping.index') }}" class="btn btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-arrow-left me-1"></i>
                <span>Back to Shipping Zones</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-xs py-2 px-3 mb-3 small d-flex align-items-center justify-content-between" role="alert">
            <div>
                <i class="fas fa-check-circle text-success me-2"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Quick Section Filter / Accordion Controller -->
    <div class="bg-white border p-2 px-3 mb-3 d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center justify-content-between gap-2 shadow-xs" style="border-radius: 6px;">
        <div class="flex-grow-1">
            <select id="zoneSectionSelector" class="form-select form-select-sm fw-semibold" onchange="handleZoneSectionFilter(this.value)">
                <option value="all">-- Quick Jump / Filter Section (Show All 4 Sections) --</option>
                <option value="info">1. Zone Name &amp; Districts Coverage</option>
                <option value="weight">2. Weight-Based Delivery Slabs ({{ count($weightRates) }} Slabs)</option>
                <option value="product">3. Specific Product Delivery Charge Override</option>
                <option value="methods">4. Shipping Methods ({{ count($zone->methods) }} Methods)</option>
            </select>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-light border btn-compact px-3 flex-grow-1" onclick="toggleAllZoneSections(true)">
                <i class="fas fa-expand-alt me-1"></i>
                <span>Expand All</span>
            </button>
            <button type="button" class="btn btn-light border btn-compact px-3 flex-grow-1" onclick="toggleAllZoneSections(false)">
                <i class="fas fa-compress-alt me-1"></i>
                <span>Collapse All</span>
            </button>
        </div>
    </div>

    <!-- 1. Zone Info Section -->
    <div class="zone-collapsible-card active-open mb-3" id="zoneSecCard_info" data-sec="info">
        <div class="zone-card-header" onclick="toggleZoneSection('info')">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-map-pin text-primary"></i>
                <h6 class="fw-bold text-dark mb-0" style="font-size: 14px;">Zone Details &amp; Locations</h6>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 bn-digit font-monospace" id="selectedCountBadge" style="font-size: 10px;">0 Locations</span>
            </div>
            <span class="sec-chevron"><i class="fas fa-chevron-down"></i></span>
        </div>

        <div class="zone-card-body" id="zoneSecBody_info">
            <form action="{{ route('admin.shipping.zone.update', $zone->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Zone Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control fw-semibold" value="{{ $zone->name }}" required>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label mb-0">Districts / Locations <span class="text-danger">*</span></label>
                        <button type="button" class="btn btn-link text-primary text-decoration-none p-0 small fw-semibold" style="font-size: 11px;" onclick="document.getElementById('editDistrictPickerWrap').classList.toggle('d-none')">
                            <i class="fas fa-list-check me-1"></i>Toggle 64-District Interactive Chooser
                        </button>
                    </div>
                    <textarea name="districts" id="zoneDistrictsInput" class="form-control" rows="3" required oninput="syncChipsFromTextarea()">{{ $districtsString }}</textarea>
                    <small class="text-muted d-block mt-1" style="font-size: 11px;">Enter districts separated by commas ( Dhaka, Gazipur).</small>
                </div>

                <!-- Interactive 64-District Picker Drawer -->
                <div id="editDistrictPickerWrap" class="p-3 bg-light border mb-3 d-none" style="border-radius: 5px;">
                    <div class="row g-2 align-items-center mb-2">
                        <div class="col-12 col-sm-7">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0" style="height: 30px;"><i class="fas fa-search text-muted" style="font-size: 10px;"></i></span>
                                <input type="text" class="form-control border-start-0" style="height: 30px; font-size: 11.5px;" placeholder="Filter district name..." oninput="filterDistrictChips(this.value)">
                            </div>
                        </div>
                        <div class="col-12 col-sm-5 d-flex gap-1 justify-content-sm-end">
                            <button type="button" class="btn btn-outline-primary btn-compact py-1 px-2 flex-grow-1 flex-sm-grow-0" style="font-size: 10.5px !important;" onclick="selectAllDistricts()">Select All 64</button>
                            <button type="button" class="btn btn-outline-secondary btn-compact py-1 px-2 flex-grow-1 flex-sm-grow-0" style="font-size: 10.5px !important;" onclick="clearAllLocations()">Clear All</button>
                        </div>
                    </div>
                    <div class="district-chips-grid" id="districtChipsContainer"></div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary btn-compact px-4 py-2 shadow-xs w-100 w-sm-auto">
                        <i class="fas fa-save me-1"></i>
                        <span>Update Zone Details</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. Weight-Based Slabs Section -->
    <div class="zone-collapsible-card active-open mb-3" id="zoneSecCard_weight" data-sec="weight">
        <div class="zone-card-header" onclick="toggleZoneSection('weight')">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-weight-hanging text-primary"></i>
                <div>
                    <h6 class="fw-bold text-dark mb-0" style="font-size: 14px;">Weight-Based Slabs ({{ $zone->name }})</h6>
                </div>
            </div>
            <span class="sec-chevron"><i class="fas fa-chevron-down"></i></span>
        </div>

        <div class="zone-card-body" id="zoneSecBody_weight">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
                <small class="text-muted" style="font-size: 11.5px;">Configure weight ranges (in KG) and corresponding delivery charges.</small>
                <button type="button" class="btn btn-outline-primary btn-compact shadow-xs w-100 w-sm-auto" onclick="addWeightRow()">
                    <i class="fas fa-plus me-1" style="font-size: 10px;"></i>
                    <span>Add Weight Slab</span>
                </button>
            </div>

            <form action="{{ route('admin.shipping.weight.rates.save', $zone->id) }}" method="POST">
                @csrf
                <div class="zone-table-wrap mb-3">
                    <table class="table custom-zone-table align-middle" id="weightSlabsTable">
                        <thead>
                            <tr>
                                <th style="min-width: 150px;">Weight From (KG)</th>
                                <th style="min-width: 150px;">Weight To (KG)</th>
                                <th style="min-width: 160px;">Delivery Cost (৳)</th>
                                <th class="text-end" style="width: 70px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($weightRates as $wr)
                                <tr>
                                    <td><input type="number" step="0.001" name="weight_from[]" class="form-control font-monospace bn-digit" value="{{ $wr->weight_from }}" required></td>
                                    <td><input type="number" step="0.001" name="weight_to[]" class="form-control font-monospace bn-digit" value="{{ $wr->weight_to }}" required></td>
                                    <td><input type="number" step="0.01" name="cost[]" class="form-control font-monospace bn-digit" value="{{ $wr->cost }}" required></td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-outline-danger action-sq-btn" onclick="this.closest('tr').remove()" title="Remove Slab"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td><input type="number" step="0.001" name="weight_from[]" class="form-control font-monospace bn-digit" value="0.000"></td>
                                    <td><input type="number" step="0.001" name="weight_to[]" class="form-control font-monospace bn-digit" value="0.500"></td>
                                    <td><input type="number" step="0.01" name="cost[]" class="form-control font-monospace bn-digit" value="120.00"></td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-outline-danger action-sq-btn" onclick="this.closest('tr').remove()" title="Remove Slab"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="row align-items-end g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Increase Per KG After 1 KG (৳)</label>
                        <input type="number" step="0.01" name="per_kg_extra" class="form-control font-monospace bn-digit" value="{{ $weightRates->first()->per_kg_extra ?? 20.00 }}">
                    </div>
                    <div class="col-12 col-md-6 text-md-end">
                        <button type="submit" class="btn btn-success btn-compact px-4 py-2 shadow-xs w-100 w-md-auto">
                            <i class="fas fa-check-circle me-1"></i>
                            <span>Save Weight Slabs</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 3. Specific Product Delivery Charge Section -->
    <div class="zone-collapsible-card active-open mb-3" id="zoneSecCard_product" data-sec="product">
        <div class="zone-card-header" onclick="toggleZoneSection('product')">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-box text-warning"></i>
                <h6 class="fw-bold text-dark mb-0" style="font-size: 14px;">Specific Product Delivery Charge</h6>
            </div>
            <span class="sec-chevron"><i class="fas fa-chevron-down"></i></span>
        </div>

        <div class="zone-card-body" id="zoneSecBody_product">
            <p class="small text-muted mb-2" style="font-size: 11.5px;">Search any product to assign an individual custom delivery fee or weight override.</p>

            <div class="position-relative mb-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted" style="font-size: 11px;"></i></span>
                    <input type="text" id="productSearchInput" class="form-control border-start-0" placeholder="Type product name or SKU to search..." autocomplete="off">
                </div>
                <div id="productSearchResults" class="list-group position-absolute w-100 shadow border mt-1" style="z-index: 1050; display: none; max-height: 240px; overflow-y: auto; border-radius: 5px;"></div>
            </div>

            <div id="selectedProductBox" class="p-3 bg-light border d-none" style="border-radius: 6px;">
                <input type="hidden" id="override_product_id">
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <strong id="override_product_title" class="text-primary small" style="font-size: 12.5px;"></strong>
                    <button type="button" class="btn-close btn-sm shadow-none" onclick="document.getElementById('selectedProductBox').classList.add('d-none')"></button>
                </div>
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-sm-4">
                        <label class="form-label">Weight (KG)</label>
                        <input type="number" step="0.001" id="override_weight" class="form-control font-monospace bn-digit">
                    </div>
                    <div class="col-12 col-sm-4">
                        <label class="form-label">Custom Fee (৳)</label>
                        <input type="number" step="0.01" id="override_cost" class="form-control font-monospace bn-digit" placeholder="Fee">
                    </div>
                    <div class="col-12 col-sm-4">
                        <div class="form-check form-switch p-2 bg-white border d-flex align-items-center justify-content-between m-0" style="border-radius: 5px; height: 33px;">
                            <label class="form-check-label small fw-semibold text-dark ms-1" for="override_qty_multiply" style="font-size: 11.5px; cursor: pointer;">Multiply by Qty</label>
                            <input class="form-check-input m-0" type="checkbox" id="override_qty_multiply" style="cursor: pointer;">
                        </div>
                    </div>
                </div>
                <div class="mt-3 text-end">
                    <button type="button" class="btn btn-primary btn-compact px-3 py-2 shadow-xs w-100 w-sm-auto" id="btnSaveProdOverride" onclick="saveProductOverride()">
                        <i class="fas fa-save me-1"></i>
                        <span>Save Product Rate</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Shipping Methods Section -->
    <div class="zone-collapsible-card active-open mb-4" id="zoneSecCard_methods" data-sec="methods">
        <div class="zone-card-header" onclick="toggleZoneSection('methods')">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-truck-fast text-success"></i>
                <h6 class="fw-bold text-dark mb-0" style="font-size: 14px;">Shipping Methods</h6>
                <span class="badge bg-white text-dark border px-2 py-1 bn-digit font-monospace" style="font-size: 10px;">{{ count($zone->methods) }} Methods</span>
            </div>
            <span class="sec-chevron"><i class="fas fa-chevron-down"></i></span>
        </div>

        <div class="zone-card-body" id="zoneSecBody_methods">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
                <small class="text-muted" style="font-size: 11.5px;">Assign flat rate or free shipping rules to this zone.</small>
                <button type="button" class="btn btn-primary btn-compact shadow-xs w-100 w-sm-auto" data-bs-toggle="modal" data-bs-target="#addMethodModal">
                    <i class="fas fa-plus me-1" style="font-size: 10px;"></i>
                    <span>Add Method</span>
                </button>
            </div>

            <div class="zone-table-wrap">
                <table class="table custom-zone-table align-middle mb-0 text-nowrap">
                    <thead>
                        <tr>
                            <th style="min-width: 130px;">Title</th>
                            <th style="min-width: 100px;">Type</th>
                            <th style="min-width: 90px;">Status</th>
                            <th style="min-width: 180px;">Conditions / Cost</th>
                            <th class="text-end" style="width: 80px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($zone->methods as $method)
                            <tr>
                                <td class="fw-bold text-dark">{{ $method->title }}</td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle text-uppercase px-2 py-1" style="font-size: 9.5px;">
                                        {{ str_replace('_', ' ', $method->method_type) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $method->is_enabled ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }} px-2 py-1" style="font-size: 9.5px;">
                                        {{ $method->is_enabled ? 'Enabled' : 'Disabled' }}
                                    </span>
                                </td>
                                <td class="small text-muted bn-digit font-monospace">
                                    @if($method->method_type === 'free_shipping')
                                        Free ({{ str_replace('_', ' ', $method->free_shipping_requires) }})
                                        @if($method->min_amount > 0)
                                            | Min: <strong class="text-dark">৳{{ number_format($method->min_amount, 2) }}</strong>
                                        @endif
                                    @else
                                        Cost: <strong class="text-dark">৳{{ number_format($method->cost, 2) }}</strong>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-outline-danger action-sq-btn shadow-xs" title="Delete Method" onclick="openDeleteMethodModal('{{ route('admin.shipping.method.destroy', $method->id) }}', '{{ addslashes($method->title) }}')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted small">
                                    <i class="fas fa-truck fa-2x mb-2 d-block opacity-25"></i>
                                    <span class="fw-semibold">No shipping methods added yet.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Add Shipping Method -->
<div class="modal fade zone-modal-scope" id="addMethodModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.shipping.method.store', $zone->id) }}" method="POST" class="modal-content border-0 shadow-lg overflow-hidden">
            @csrf
            <div class="modal-header bg-light border-bottom py-2 px-3">
                <h6 class="fw-bold text-dark mb-0" style="font-size: 14px;">
                    <i class="fas fa-plus-circle text-primary me-1"></i> Add Shipping Method
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div class="mb-3">
                    <label class="form-label">Method Type</label>
                    <select name="method_type" id="methodTypeSelect" class="form-select" onchange="handleMethodTypeChange()" required>
                        <option value="flat_rate">Flat rate</option>
                        <option value="free_shipping">Free shipping</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" id="methodTitleInput" class="form-control" value="Flat rate" required>
                </div>

                <!-- Flat Rate Fields -->
                <div id="flatRateGroup">
                    <div class="mb-3">
                        <label class="form-label">Cost (৳)</label>
                        <input type="number" step="0.01" name="cost" id="methodCostInput" class="form-control font-monospace bn-digit" value="0.00">
                    </div>
                </div>

                <!-- Free Shipping Fields -->
                <div id="freeShippingGroup" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">Free Shipping Requires</label>
                        <select name="free_shipping_requires" id="freeReqSelect" class="form-select" onchange="handleFreeReqChange()">
                            <option value="min_amount">A minimum order amount</option>
                            <option value="no_requirement">No requirement</option>
                        </select>
                    </div>
                    <div class="mb-3" id="minAmountGroup">
                        <label class="form-label">Minimum Order Amount (৳)</label>
                        <input type="number" step="0.01" name="min_amount" id="minAmountInput" class="form-control font-monospace bn-digit" value="4500.00" placeholder="4500">
                    </div>
                </div>

                <div class="form-check form-switch m-0">
                    <input class="form-check-input" type="checkbox" name="is_enabled" value="1" id="isMethodEnabled" checked style="cursor: pointer;">
                    <label class="form-check-label small fw-semibold text-dark" for="isMethodEnabled" style="cursor: pointer;">Enable this method</label>
                </div>
            </div>
            <div class="modal-footer bg-light border-top py-2 px-3">
                <button type="button" class="btn btn-light border btn-compact px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary btn-compact px-3 shadow-xs">Save Method</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Delete Shipping Method Confirmation (Replaces raw confirm()) -->
<div class="modal fade zone-modal-scope" id="deleteMethodConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 46px; height: 46px;">
                    <i class="fas fa-trash-alt fs-5"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 14.5px;">Delete Shipping Method?</h6>
                <p class="text-muted small mb-3" style="font-size: 12px; line-height: 1.45;">
                    Are you sure you want to delete <strong id="deleteMethodTitleText" class="text-dark"></strong>?
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light border btn-compact px-3" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteMethodConfirmForm" method="POST" action="" class="m-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-compact px-3 shadow-xs">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal 3: Notice Modal (Replaces raw alert()) -->
<div class="modal fade zone-modal-scope" id="zoneEditNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 44px; height: 44px;">
                    <i class="fas fa-check-circle fs-4"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">Saved</h6>
                <p class="text-muted small mb-3" id="zoneEditNoticeMsg" style="font-size: 12px; line-height: 1.45;"></p>
                <button type="button" class="btn btn-dark btn-compact px-4" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
const BD_DISTRICTS = [
    "Dhaka", "Gazipur", "Narayanganj", "Narsingdi", "Manikganj", "Munshiganj", "Tangail", "Kishoreganj", "Faridpur", "Gopalganj", "Madaripur", "Rajbari", "Shariatpur",
    "Chattogram", "Cox's Bazar", "Cumilla", "Brahmanbaria", "Chandpur", "Feni", "Lakshmipur", "Noakhali", "Khagrachhari", "Rangamati", "Bandarban",
    "Sylhet", "Moulvibazar", "Habiganj", "Sunamganj",
    "Rajshahi", "Bogura", "Pabna", "Sirajganj", "Natore", "Naogaon", "Chapainawabganj", "Joypurhat",
    "Khulna", "Jashore", "Satkhira", "Kushtia", "Jhenaidah", "Magura", "Narail", "Bagerhat", "Chuadanga", "Meherpur",
    "Barishal", "Patuakhali", "Bhola", "Pirojpur", "Barguna", "Jhalokati",
    "Rangpur", "Dinajpur", "Kurigram", "Gaibandha", "Nilphamari", "Panchagarh", "Thakurgaon", "Lalmonirhat",
    "Mymensingh", "Jamalpur", "Netrokona", "Sherpur"
];

function toggleZoneSection(secKey, forceOpen = null) {
    const card = document.getElementById('zoneSecCard_' + secKey);
    const body = document.getElementById('zoneSecBody_' + secKey);
    if (!card || !body) return;

    const isOpen = !body.classList.contains('d-none');
    const nextState = forceOpen !== null ? forceOpen : !isOpen;

    if (nextState) {
        body.classList.remove('d-none');
        card.classList.add('active-open');
    } else {
        body.classList.add('d-none');
        card.classList.remove('active-open');
    }
}

function handleZoneSectionFilter(val) {
    document.querySelectorAll('.zone-collapsible-card').forEach(card => {
        const key = card.getAttribute('data-sec');
        if (val === 'all' || key === val) {
            card.classList.remove('d-none');
            toggleZoneSection(key, true);
        } else {
            card.classList.add('d-none');
        }
    });
}

function toggleAllZoneSections(expand) {
    document.getElementById('zoneSectionSelector').value = 'all';
    document.querySelectorAll('.zone-collapsible-card').forEach(card => {
        card.classList.remove('d-none');
        const key = card.getAttribute('data-sec');
        toggleZoneSection(key, expand);
    });
}

function openDeleteMethodModal(actionUrl, methodTitle) {
    document.getElementById('deleteMethodConfirmForm').action = actionUrl;
    document.getElementById('deleteMethodTitleText').innerText = '"' + methodTitle + '"';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteMethodConfirmModal')).show();
}

function showZoneEditNotice(msg) {
    document.getElementById('zoneEditNoticeMsg').innerText = msg;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('zoneEditNoticeModal')).show();
}

function renderDistrictChips() {
    const container = document.getElementById('districtChipsContainer');
    if (!container) return;
    container.innerHTML = BD_DISTRICTS.map(d => `
        <span class="district-chip" data-name="${d}" onclick="toggleDistrictChip('${d.replace(/'/g, "\\'")}')">
            <i class="fas fa-plus" style="font-size: 8.5px;"></i>
            <span>${d}</span>
        </span>
    `).join('');
    syncChipsFromTextarea();
}

function getParsedTextareaList() {
    const raw = document.getElementById('zoneDistrictsInput').value || '';
    return raw.split(',').map(s => s.trim()).filter(Boolean);
}

function toggleDistrictChip(districtName) {
    let list = getParsedTextareaList();
    const existsIdx = list.findIndex(item => item.toLowerCase() === districtName.toLowerCase());
    if (existsIdx > -1) {
        list.splice(existsIdx, 1);
    } else {
        list.push(districtName);
    }
    document.getElementById('zoneDistrictsInput').value = list.join(', ');
    syncChipsFromTextarea();
}

function syncChipsFromTextarea() {
    const list = getParsedTextareaList();
    const lowerSet = new Set(list.map(i => i.toLowerCase()));

    document.querySelectorAll('.district-chip').forEach(chip => {
        const dName = (chip.getAttribute('data-name') || '').toLowerCase();
        const icon = chip.querySelector('i');
        if (lowerSet.has(dName)) {
            chip.classList.add('selected');
            if (icon) icon.className = 'fas fa-check';
        } else {
            chip.classList.remove('selected');
            if (icon) icon.className = 'fas fa-plus';
        }
    });

    const badge = document.getElementById('selectedCountBadge');
    if (badge) {
        badge.innerText = list.length + (list.length === 1 ? ' Location' : ' Locations');
    }
}

function selectAllDistricts() {
    document.getElementById('zoneDistrictsInput').value = BD_DISTRICTS.join(', ');
    syncChipsFromTextarea();
}

function clearAllLocations() {
    document.getElementById('zoneDistrictsInput').value = '';
    syncChipsFromTextarea();
}

function filterDistrictChips(query) {
    const q = (query || '').toLowerCase().trim();
    document.querySelectorAll('.district-chip').forEach(chip => {
        const name = (chip.getAttribute('data-name') || '').toLowerCase();
        chip.style.display = (!q || name.includes(q)) ? 'inline-flex' : 'none';
    });
}

function handleMethodTypeChange() {
    const type = document.getElementById('methodTypeSelect').value;
    const flatGroup = document.getElementById('flatRateGroup');
    const freeGroup = document.getElementById('freeShippingGroup');
    const titleInput = document.getElementById('methodTitleInput');

    if (type === 'free_shipping') {
        flatGroup.style.display = 'none';
        freeGroup.style.display = 'block';
        titleInput.value = 'Free shipping';
        document.getElementById('methodCostInput').value = '0.00';
    } else {
        flatGroup.style.display = 'block';
        freeGroup.style.display = 'none';
        titleInput.value = 'Flat rate';
    }
}

function handleFreeReqChange() {
    const req = document.getElementById('freeReqSelect').value;
    const minGrp = document.getElementById('minAmountGroup');
    minGrp.style.display = (req === 'min_amount') ? 'block' : 'none';
}

function addWeightRow() {
    const tbody = document.getElementById('weightSlabsTable').getElementsByTagName('tbody')[0];
    const tr = tbody.insertRow();
    tr.innerHTML = `
        <td><input type="number" step="0.001" name="weight_from[]" class="form-control font-monospace bn-digit" placeholder="0.000" required></td>
        <td><input type="number" step="0.001" name="weight_to[]" class="form-control font-monospace bn-digit" placeholder="1.000" required></td>
        <td><input type="number" step="0.01" name="cost[]" class="form-control font-monospace bn-digit" placeholder="60.00" required></td>
        <td class="text-end"><button type="button" class="btn btn-outline-danger action-sq-btn" onclick="this.closest('tr').remove()" title="Remove Slab"><i class="fas fa-trash"></i></button></td>
    `;
}

const searchInp = document.getElementById('productSearchInput');
const resBox = document.getElementById('productSearchResults');

searchInp.addEventListener('input', function() {
    const term = this.value.trim();
    if (term.length < 2) {
        resBox.style.display = 'none';
        return;
    }

    fetch(`{{ route('admin.shipping.product.search') }}?term=${encodeURIComponent(term)}`)
        .then(res => res.json())
        .then(data => {
            resBox.innerHTML = '';
            if (data && data.length > 0) {
                data.forEach(p => {
                    const item = document.createElement('a');
                    item.href = 'javascript:void(0)';
                    item.className = 'list-group-item list-group-item-action py-2 px-3 small d-flex justify-content-between align-items-center';
                    item.innerHTML = `<strong class="text-dark">${p.title}</strong> <span class="badge bg-light text-secondary border font-monospace bn-digit">${p.weight || 0} KG | ৳${p.custom_delivery_charge || 0}</span>`;
                    item.onclick = function() {
                        document.getElementById('override_product_id').value = p.id;
                        document.getElementById('override_product_title').innerText = p.title;
                        document.getElementById('override_weight').value = p.weight || 0;
                        document.getElementById('override_cost').value = p.custom_delivery_charge || '';
                        document.getElementById('override_qty_multiply').checked = (p.multiply_qty_delivery_charge == 1);
                        document.getElementById('selectedProductBox').classList.remove('d-none');
                        resBox.style.display = 'none';
                        searchInp.value = '';
                    };
                    resBox.appendChild(item);
                });
                resBox.style.display = 'block';
            } else {
                resBox.style.display = 'none';
            }
        })
        .catch(() => {
            resBox.style.display = 'none';
        });
});

function saveProductOverride() {
    const pId = document.getElementById('override_product_id').value;
    const cost = document.getElementById('override_cost').value;
    const weight = document.getElementById('override_weight').value;
    const multiply = document.getElementById('override_qty_multiply').checked ? 1 : 0;
    const btn = document.getElementById('btnSaveProdOverride');

    if (btn) btn.disabled = true;

    fetch(`{{ route('admin.shipping.product.shipping.update') }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            product_id: pId,
            custom_delivery_charge: cost,
            weight: weight,
            multiply_qty_delivery_charge: multiply
        })
    })
    .then(res => res.json())
    .then(data => {
        if (btn) btn.disabled = false;
        showZoneEditNotice(data.message || 'Product shipping rate updated successfully.');
    })
    .catch(() => {
        if (btn) btn.disabled = false;
        showZoneEditNotice('Failed to update product shipping rate.');
    });
}

document.addEventListener('DOMContentLoaded', renderDistrictChips);
</script>
@endsection