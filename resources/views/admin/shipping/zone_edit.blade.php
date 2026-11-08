
@extends('layouts.admin')
@section('title', 'Edit Zone - ' . config('app.name'))

@section('admin_content')
<style>
    /* =========================================
       SHIPPING ZONE - KAIADMIN CLASSIC DESIGN
    ========================================= */

    #editZoneRoot {
        --zone-primary: var(--bs-primary, #1572e8);
        --zone-bg: #f8f9fa;
        --zone-card: #ffffff;
        --zone-text: #2a2f5b;
        --zone-muted: #8d9498;
        --zone-border: #ebedf2;
        --zone-radius: 5px;
        --zone-shadow: 0 1px 3px rgba(0, 0, 0, .04);
        color: var(--zone-text);
        min-width: 0;
    }

    #editZoneRoot *,
    .zone-modal-scope * {
        box-sizing: border-box;
    }

    /* Global Elements */

    #editZoneRoot .btn,
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
        border-radius: var(--zone-radius) !important;
    }

    #editZoneRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    #editZoneRoot .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    #editZoneRoot .btn:focus,
    .zone-modal-scope .btn:focus {
        box-shadow: none;
    }

    #editZoneRoot .btn-compact,
    .zone-modal-scope .btn-compact {
        min-height: 32px;
        padding: 6px 13px !important;
        font-size: 12px !important;
        line-height: 1.35;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        white-space: nowrap;
        transition: all .18s ease;
    }

    #editZoneRoot .btn-primary,
    .zone-modal-scope .btn-primary {
        background-color: var(--zone-primary);
        border-color: var(--zone-primary);
    }

    #editZoneRoot .btn-primary:hover,
    .zone-modal-scope .btn-primary:hover {
        filter: brightness(.94);
    }

    #editZoneRoot .btn-success,
    .zone-modal-scope .btn-success {
        background-color: #31ce77;
        border-color: #31ce77;
    }

    #editZoneRoot .btn-outline-primary:hover {
        color: #fff;
    }

    .action-sq-btn {
        width: 32px !important;
        height: 32px !important;
        min-width: 32px !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 12px !important;
        border-radius: 5px !important;
        transition: all .18s ease;
    }

    /* Page Header */

    #editZoneRoot h5 {
        color: var(--zone-text);
        letter-spacing: -.25px;
    }

    #editZoneRoot .page-subtitle {
        color: var(--zone-muted);
        font-size: 12px;
    }

    /* Navigation */

    .shipping-subnav-wrap {
        overflow-x: auto;
        white-space: nowrap;
        scrollbar-width: none;
        -ms-overflow-style: none;
        -webkit-overflow-scrolling: touch;
        border-color: var(--zone-border) !important;
    }

    .shipping-subnav-wrap::-webkit-scrollbar {
        display: none;
    }

    .shipping-nav-pills {
        background: #f5f7fd;
        padding: 4px;
        border-radius: 5px !important;
        display: inline-flex;
        gap: 3px;
    }

    .shipping-nav-pills .nav-link {
        color: #6c757d;
        font-weight: 600;
        font-size: 12px;
        padding: 8px 15px;
        transition: all .18s ease;
        background: transparent;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border: 1px solid transparent;
    }

    .shipping-nav-pills .nav-link:hover {
        color: var(--zone-primary);
        background: #fff;
    }

    .shipping-nav-pills .nav-link.active {
        background-color: #fff !important;
        color: var(--zone-primary) !important;
        border-color: var(--zone-border);
        box-shadow: 0 1px 3px rgba(0, 0, 0, .06);
    }

    /* Section Selector */

    #editZoneRoot > .section-toolbar {
        background: var(--zone-card);
        border: 1px solid var(--zone-border);
        border-radius: var(--zone-radius);
        box-shadow: var(--zone-shadow);
    }

    /* Collapsible Cards */

    .zone-collapsible-card {
        background: var(--zone-card);
        border: 1px solid var(--zone-border);
        border-radius: 5px !important;
        box-shadow: var(--zone-shadow);
        overflow: hidden;
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    .zone-collapsible-card.active-open {
        border-color: #dfe3eb;
    }

    .zone-card-header {
        min-height: 52px;
        padding: 12px 17px;
        background: #fff;
        cursor: pointer;
        user-select: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        border-bottom: 1px solid transparent;
        transition: background-color .18s ease;
    }

    .zone-card-header:hover {
        background: #fafbff;
    }

    .zone-collapsible-card.active-open .zone-card-header {
        background: #fff;
        border-bottom-color: var(--zone-border);
    }

    .zone-card-header h6 {
        color: var(--zone-text) !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        letter-spacing: -.1px;
    }

    .zone-card-header > .d-flex > i {
        font-size: 14px;
    }

    .sec-chevron {
        width: 26px;
        height: 26px;
        border-radius: 5px;
        background: #f5f7fd;
        border: 1px solid var(--zone-border);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        color: #8d9498;
        transition: transform .2s ease, background-color .18s ease, color .18s ease;
        flex-shrink: 0;
    }

    .zone-collapsible-card.active-open .sec-chevron {
        transform: rotate(180deg);
        background: var(--zone-primary);
        border-color: var(--zone-primary);
        color: #fff;
    }

    .zone-card-body {
        padding: 18px;
        background: #fff;
        min-width: 0;
    }

    /* Form Controls */

    #editZoneRoot .form-label,
    .zone-modal-scope .form-label {
        font-size: 12px;
        font-weight: 600;
        color: #495057;
        margin-bottom: 6px;
        display: block;
    }

    #editZoneRoot .form-control,
    #editZoneRoot .form-select,
    .zone-modal-scope .form-control,
    .zone-modal-scope .form-select {
        min-height: 36px;
        font-size: 12.5px;
        border: 1px solid #ebedf2;
        padding: 7px 11px;
        color: #495057;
        background-color: #fff;
        box-shadow: none;
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    #editZoneRoot textarea.form-control {
        min-height: auto;
        line-height: 1.6;
    }

    #editZoneRoot .form-control::placeholder,
    .zone-modal-scope .form-control::placeholder {
        color: #adb5bd;
        opacity: 1;
    }

    #editZoneRoot .form-control:focus,
    #editZoneRoot .form-select:focus,
    .zone-modal-scope .form-control:focus,
    .zone-modal-scope .form-select:focus {
        border-color: var(--zone-primary);
        box-shadow: 0 0 0 2px rgba(21, 114, 232, .10);
        outline: none;
    }

    #editZoneRoot .form-check-input,
    .zone-modal-scope .form-check-input {
        cursor: pointer;
    }

    #editZoneRoot .form-check-input:checked,
    .zone-modal-scope .form-check-input:checked {
        background-color: var(--zone-primary);
        border-color: var(--zone-primary);
    }

    /* Tables */

    .zone-table-wrap {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
        border: 1px solid var(--zone-border);
        border-radius: 5px !important;
        width: 100%;
        max-width: 100%;
        background: #fff;
    }

    .custom-zone-table {
        margin-bottom: 0;
        width: 100%;
    }

    .custom-zone-table th {
        background-color: #f8f9fd;
        color: #6c757d;
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .35px;
        border-bottom: 1px solid var(--zone-border);
        padding: 11px 12px;
        white-space: nowrap;
        vertical-align: middle;
    }

    .custom-zone-table td {
        padding: 9px 12px;
        vertical-align: middle;
        font-size: 12px;
        color: #495057;
        border-bottom: 1px solid #f1f2f6;
    }

    .custom-zone-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .custom-zone-table tbody tr:hover {
        background-color: #fafbff;
    }

    #weightSlabsTable {
        min-width: 560px;
    }

    #weightSlabsTable .form-control {
        height: 34px;
        min-height: 34px;
        font-size: 12px;
    }

    /* District Chips */

    .district-chips-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        max-height: 190px;
        overflow-y: auto;
        padding: 3px;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
    }

    .district-chip {
        font-size: 11.5px;
        font-weight: 600;
        padding: 6px 10px;
        border: 1px solid #ebedf2;
        background: #fff;
        color: #6c757d;
        cursor: pointer;
        user-select: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all .15s ease;
    }

    .district-chip:hover {
        border-color: var(--zone-primary);
        color: var(--zone-primary);
        background: #f5f8ff;
    }

    .district-chip.selected {
        background: var(--zone-primary) !important;
        border-color: var(--zone-primary) !important;
        color: #fff !important;
    }

    /* Product Search */

    #productSearchResults {
        border-color: var(--zone-border) !important;
        background: #fff;
    }

    #productSearchResults .list-group-item {
        font-size: 12px;
        border-color: #f1f2f6;
        transition: background .15s ease;
    }

    #productSearchResults .list-group-item:hover {
        background: #f5f8ff;
    }

    #selectedProductBox {
        border-color: var(--zone-border) !important;
    }

    /* Alerts and Badges */

    #editZoneRoot .alert {
        border: 1px solid transparent !important;
        box-shadow: none;
    }

    #editZoneRoot .badge {
        font-weight: 600;
        letter-spacing: .1px;
    }

    /* Modal */

    .zone-modal-scope .modal-content {
        border: 1px solid var(--zone-border) !important;
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(0, 0, 0, .12) !important;
    }

    .zone-modal-scope .modal-header,
    .zone-modal-scope .modal-footer {
        background: #f8f9fd !important;
        border-color: var(--zone-border) !important;
    }

    .zone-modal-scope .modal-header {
        min-height: 48px;
    }

    .zone-modal-scope .modal-body {
        padding: 18px !important;
    }

    .zone-modal-scope .modal-footer {
        padding: 11px 16px !important;
    }

    .zone-modal-scope .modal-dialog {
        width: calc(100% - 24px);
        max-width: 500px;
        margin-left: auto;
        margin-right: auto;
    }

    .zone-modal-scope .modal-dialog.modal-sm {
        max-width: 380px;
    }

    /* Typography */

    #editZoneRoot .bn-digit,
    #editZoneRoot .font-monospace {
        font-weight: 600 !important;
    }

    #editZoneRoot .text-muted {
        color: #8d9498 !important;
    }

    /* Responsive */

    @media (min-width: 1200px) {
        #editZoneRoot {
            padding-left: 20px;
            padding-right: 20px;
        }

        .zone-card-body {
            padding: 20px;
        }
    }

    @media (max-width: 991.98px) {
        .zone-card-body {
            padding: 15px;
        }

        .custom-zone-table th,
        .custom-zone-table td {
            padding: 9px 10px;
        }
    }

    @media (max-width: 767.98px) {
        #editZoneRoot {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        .shipping-nav-pills {
            display: flex;
            min-width: max-content;
        }

        .shipping-nav-pills .nav-link {
            padding: 8px 12px;
            font-size: 11.5px;
        }

        .zone-card-body {
            padding: 13px;
        }

        .zone-card-header {
            padding: 12px;
            min-height: 48px;
        }

        .top-back-btn-wrap {
            width: 100%;
        }

        .top-back-btn-wrap .btn-compact {
            width: 100%;
            min-height: 36px;
        }

        .zone-table-wrap {
            max-width: 100%;
        }

        .district-chips-grid {
            max-height: 230px;
        }

        #editZoneRoot .row {
            --bs-gutter-x: 12px;
        }

        .zone-modal-scope .modal-dialog {
            margin: 12px auto;
        }
    }

    @media (max-width: 420px) {
        .zone-card-header {
            gap: 7px;
        }

        .zone-card-header h6 {
            font-size: 12.5px !important;
        }

        #editZoneRoot .btn-compact,
        .zone-modal-scope .btn-compact {
            font-size: 11px !important;
        }

        .zone-card-body {
            padding: 11px;
        }

        .custom-zone-table th,
        .custom-zone-table td {
            padding: 8px 9px;
        }

        .district-chip {
            padding: 5px 8px;
            font-size: 11px;
        }

        .zone-modal-scope .modal-body {
            padding: 14px !important;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="editZoneRoot">

    <!-- Navigation -->
    <div class="shipping-subnav-wrap mb-3 pb-2 border-bottom">
        <ul class="nav nav-pills shipping-nav-pills flex-nowrap text-nowrap">
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('admin.shipping.index') }}">
                    <i class="fas fa-globe-asia" style="font-size: 11px;"></i>
                    <span>Zones</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.shipping.settings') }}">
                    <i class="fas fa-sliders-h" style="font-size: 11px;"></i>
                    <span>Settings</span>
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
                    <span>Pickup</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-map-location-dot text-primary"></i>
                <span>Edit: {{ $zone->name }}</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Edit locations, weight rates and shipping methods.
            </small>
        </div>

        <div class="top-back-btn-wrap">
            <a href="{{ route('admin.shipping.index') }}" class="btn btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-arrow-left me-1"></i>
                <span>Back</span>
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

    <!-- Section Controls -->
    <div class="bg-white border p-2 px-3 mb-3 d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center justify-content-between gap-2 shadow-xs" style="border-radius: 6px;">
        <div class="flex-grow-1">
            <select id="zoneSectionSelector" class="form-select form-select-sm fw-semibold" onchange="handleZoneSectionFilter(this.value)">
                <option value="all">-- All sections (4) --</option>
                <option value="info">1. Zone details</option>
                <option value="weight">2. Weight rates ({{ count($weightRates) }})</option>
                <option value="product">3. Product rates</option>
                <option value="methods">4. Shipping methods ({{ count($zone->methods) }})</option>
            </select>
        </div>

        <div class="d-flex gap-2">
            <button type="button" class="btn btn-light border btn-compact px-3 flex-grow-1" onclick="toggleAllZoneSections(true)">
                <i class="fas fa-expand-alt me-1"></i>
                <span>Expand</span>
            </button>

            <button type="button" class="btn btn-light border btn-compact px-3 flex-grow-1" onclick="toggleAllZoneSections(false)">
                <i class="fas fa-compress-alt me-1"></i>
                <span>Collapse</span>
            </button>
        </div>
    </div>

    <!-- 1. Zone Details -->
    <div class="zone-collapsible-card active-open mb-3" id="zoneSecCard_info" data-sec="info">
        <div class="zone-card-header" onclick="toggleZoneSection('info')">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-map-pin text-primary"></i>
                <h6 class="fw-bold text-dark mb-0" style="font-size: 14px;">Zone details</h6>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 bn-digit font-monospace" id="selectedCountBadge" style="font-size: 10px;">0 Locations</span>
            </div>
            <span class="sec-chevron"><i class="fas fa-chevron-down"></i></span>
        </div>

        <div class="zone-card-body" id="zoneSecBody_info">
            <form action="{{ route('admin.shipping.zone.update', $zone->id) }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Zone name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control fw-semibold" value="{{ $zone->name }}" required>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label mb-0">Districts / Locations <span class="text-danger">*</span></label>
                        <button type="button" class="btn btn-link text-primary text-decoration-none p-0 small fw-semibold" style="font-size: 11px;" onclick="document.getElementById('editDistrictPickerWrap').classList.toggle('d-none')">
                            <i class="fas fa-list-check me-1"></i>District picker
                        </button>
                    </div>

                    <textarea name="districts" id="zoneDistrictsInput" class="form-control" rows="3" required oninput="syncChipsFromTextarea()">{{ $districtsString }}</textarea>

                    <small class="text-muted d-block mt-1" style="font-size: 11px;">
                        Separate locations with commas.
                    </small>
                </div>

                <!-- District Picker -->
                <div id="editDistrictPickerWrap" class="p-3 bg-light border mb-3 d-none" style="border-radius: 5px;">
                    <div class="row g-2 align-items-center mb-2">
                        <div class="col-12 col-sm-7">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0" style="height: 30px;">
                                    <i class="fas fa-search text-muted" style="font-size: 10px;"></i>
                                </span>
                                <input type="text" class="form-control border-start-0" style="height: 30px; font-size: 11.5px;" placeholder="Search districts..." oninput="filterDistrictChips(this.value)">
                            </div>
                        </div>

                        <div class="col-12 col-sm-5 d-flex gap-1 justify-content-sm-end">
                            <button type="button" class="btn btn-outline-primary btn-compact py-1 px-2 flex-grow-1 flex-sm-grow-0" style="font-size: 10.5px !important;" onclick="selectAllDistricts()">Select all</button>
                            <button type="button" class="btn btn-outline-secondary btn-compact py-1 px-2 flex-grow-1 flex-sm-grow-0" style="font-size: 10.5px !important;" onclick="clearAllLocations()">Clear</button>
                        </div>
                    </div>

                    <div class="district-chips-grid" id="districtChipsContainer"></div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary btn-compact px-4 py-2 shadow-xs w-100 w-sm-auto">
                        <i class="fas fa-save me-1"></i>
                        <span>Update zone</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. Weight Rates -->
    <div class="zone-collapsible-card active-open mb-3" id="zoneSecCard_weight" data-sec="weight">
        <div class="zone-card-header" onclick="toggleZoneSection('weight')">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-weight-hanging text-primary"></i>
                <div>
                    <h6 class="fw-bold text-dark mb-0" style="font-size: 14px;">Weight rates</h6>
                </div>
            </div>
            <span class="sec-chevron"><i class="fas fa-chevron-down"></i></span>
        </div>

        <div class="zone-card-body" id="zoneSecBody_weight">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
                <small class="text-muted" style="font-size: 11.5px;">
                    Set weight ranges and delivery costs.
                </small>

                <button type="button" class="btn btn-outline-primary btn-compact shadow-xs w-100 w-sm-auto" onclick="addWeightRow()">
                    <i class="fas fa-plus me-1" style="font-size: 10px;"></i>
                    <span>Add slab</span>
                </button>
            </div>

            <form action="{{ route('admin.shipping.weight.rates.save', $zone->id) }}" method="POST">
                @csrf

                <div class="zone-table-wrap mb-3">
                    <table class="table custom-zone-table align-middle" id="weightSlabsTable">
                        <thead>
                            <tr>
                                <th style="min-width: 150px;">From (KG)</th>
                                <th style="min-width: 150px;">To (KG)</th>
                                <th style="min-width: 160px;">Cost (৳)</th>
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
                                        <button type="button" class="btn btn-outline-danger action-sq-btn" onclick="this.closest('tr').remove()" title="Remove slab">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td><input type="number" step="0.001" name="weight_from[]" class="form-control font-monospace bn-digit" value="0.000"></td>
                                    <td><input type="number" step="0.001" name="weight_to[]" class="form-control font-monospace bn-digit" value="0.500"></td>
                                    <td><input type="number" step="0.01" name="cost[]" class="form-control font-monospace bn-digit" value="120.00"></td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-outline-danger action-sq-btn" onclick="this.closest('tr').remove()" title="Remove slab">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="row align-items-end g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Extra per KG after 1 KG (৳)</label>
                        <input type="number" step="0.01" name="per_kg_extra" class="form-control font-monospace bn-digit" value="{{ $weightRates->first()->per_kg_extra ?? 20.00 }}">
                    </div>

                    <div class="col-12 col-md-6 text-md-end">
                        <button type="submit" class="btn btn-success btn-compact px-4 py-2 shadow-xs w-100 w-md-auto">
                            <i class="fas fa-check-circle me-1"></i>
                            <span>Save rates</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 3. Product Rates -->
    <div class="zone-collapsible-card active-open mb-3" id="zoneSecCard_product" data-sec="product">
        <div class="zone-card-header" onclick="toggleZoneSection('product')">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-box text-warning"></i>
                <h6 class="fw-bold text-dark mb-0" style="font-size: 14px;">Product rate override</h6>
            </div>
            <span class="sec-chevron"><i class="fas fa-chevron-down"></i></span>
        </div>

        <div class="zone-card-body" id="zoneSecBody_product">
            <p class="small text-muted mb-2" style="font-size: 11.5px;">
                Set a custom fee or weight for a product.
            </p>

            <div class="position-relative mb-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="fas fa-search text-muted" style="font-size: 11px;"></i>
                    </span>
                    <input type="text" id="productSearchInput" class="form-control border-start-0" placeholder="Search product or SKU..." autocomplete="off">
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
                        <label class="form-label">Custom fee (৳)</label>
                        <input type="number" step="0.01" id="override_cost" class="form-control font-monospace bn-digit" placeholder="Fee">
                    </div>

                    <div class="col-12 col-sm-4">
                        <div class="form-check form-switch p-2 bg-white border d-flex align-items-center justify-content-between m-0" style="border-radius: 5px; height: 33px;">
                            <label class="form-check-label small fw-semibold text-dark ms-1" for="override_qty_multiply" style="font-size: 11.5px; cursor: pointer;">Multiply by qty</label>
                            <input class="form-check-input m-0" type="checkbox" id="override_qty_multiply" style="cursor: pointer;">
                        </div>
                    </div>
                </div>

                <div class="mt-3 text-end">
                    <button type="button" class="btn btn-primary btn-compact px-3 py-2 shadow-xs w-100 w-sm-auto" id="btnSaveProdOverride" onclick="saveProductOverride()">
                        <i class="fas fa-save me-1"></i>
                        <span>Save rate</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Shipping Methods -->
    <div class="zone-collapsible-card active-open mb-4" id="zoneSecCard_methods" data-sec="methods">
        <div class="zone-card-header" onclick="toggleZoneSection('methods')">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-truck-fast text-success"></i>
                <h6 class="fw-bold text-dark mb-0" style="font-size: 14px;">Shipping methods</h6>
                <span class="badge bg-white text-dark border px-2 py-1 bn-digit font-monospace" style="font-size: 10px;">{{ count($zone->methods) }}</span>
            </div>
            <span class="sec-chevron"><i class="fas fa-chevron-down"></i></span>
        </div>

        <div class="zone-card-body" id="zoneSecBody_methods">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
                <small class="text-muted" style="font-size: 11.5px;">
                    Set flat rate or free shipping.
                </small>

                <button type="button" class="btn btn-primary btn-compact shadow-xs w-100 w-sm-auto" data-bs-toggle="modal" data-bs-target="#addMethodModal">
                    <i class="fas fa-plus me-1" style="font-size: 10px;"></i>
                    <span>Add method</span>
                </button>
            </div>

            <div class="zone-table-wrap">
                <table class="table custom-zone-table align-middle mb-0 text-nowrap">
                    <thead>
                        <tr>
                            <th style="min-width: 130px;">Title</th>
                            <th style="min-width: 100px;">Type</th>
                            <th style="min-width: 90px;">Status</th>
                            <th style="min-width: 180px;">Cost / Rules</th>
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
                                    <button type="button" class="btn btn-outline-danger action-sq-btn shadow-xs" title="Delete method" onclick="openDeleteMethodModal('{{ route('admin.shipping.method.destroy', $method->id) }}', '{{ addslashes($method->title) }}')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted small">
                                    <i class="fas fa-truck fa-2x mb-2 d-block opacity-25"></i>
                                    <span class="fw-semibold">No methods found.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Method Modal -->
<div class="modal fade zone-modal-scope" id="addMethodModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.shipping.method.store', $zone->id) }}" method="POST" class="modal-content border-0 shadow-lg overflow-hidden">
            @csrf

            <div class="modal-header bg-light border-bottom py-2 px-3">
                <h6 class="fw-bold text-dark mb-0" style="font-size: 14px;">
                    <i class="fas fa-plus-circle text-primary me-1"></i> Add method
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-3">
                <div class="mb-3">
                    <label class="form-label">Method type</label>
                    <select name="method_type" id="methodTypeSelect" class="form-select" onchange="handleMethodTypeChange()" required>
                        <option value="flat_rate">Flat rate</option>
                        <option value="free_shipping">Free shipping</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" id="methodTitleInput" class="form-control" value="Flat rate" required>
                </div>

                <!-- Flat Rate -->
                <div id="flatRateGroup">
                    <div class="mb-3">
                        <label class="form-label">Cost (৳)</label>
                        <input type="number" step="0.01" name="cost" id="methodCostInput" class="form-control font-monospace bn-digit" value="0.00">
                    </div>
                </div>

                <!-- Free Shipping -->
                <div id="freeShippingGroup" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">Requirement</label>
                        <select name="free_shipping_requires" id="freeReqSelect" class="form-select" onchange="handleFreeReqChange()">
                            <option value="min_amount">Minimum order amount</option>
                            <option value="no_requirement">No requirement</option>
                        </select>
                    </div>

                    <div class="mb-3" id="minAmountGroup">
                        <label class="form-label">Minimum amount (৳)</label>
                        <input type="number" step="0.01" name="min_amount" id="minAmountInput" class="form-control font-monospace bn-digit" value="4500.00" placeholder="4500">
                    </div>
                </div>

                <div class="form-check form-switch m-0">
                    <input class="form-check-input" type="checkbox" name="is_enabled" value="1" id="isMethodEnabled" checked style="cursor: pointer;">
                    <label class="form-check-label small fw-semibold text-dark" for="isMethodEnabled" style="cursor: pointer;">Enable method</label>
                </div>
            </div>

            <div class="modal-footer bg-light border-top py-2 px-3">
                <button type="button" class="btn btn-light border btn-compact px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary btn-compact px-3 shadow-xs">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Method Modal -->
<div class="modal fade zone-modal-scope" id="deleteMethodConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 46px; height: 46px;">
                    <i class="fas fa-trash-alt fs-5"></i>
                </div>

                <h6 class="fw-bold text-dark mb-1" style="font-size: 14.5px;">Delete method?</h6>

                <p class="text-muted small mb-3" style="font-size: 12px; line-height: 1.45;">
                    Delete <strong id="deleteMethodTitleText" class="text-dark"></strong>?
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

<!-- Notice Modal -->
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
