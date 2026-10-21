@extends('layouts.admin')
@section('title', 'Add Zone - ' . config('app.name'))

@section('admin_content')
<style>
    /* Global Strict 5px Element Radius */
    #addShippingZoneRoot .btn,
    #addShippingZoneRoot .btn-sm,
    #addShippingZoneRoot button,
    #addShippingZoneRoot .form-control,
    #addShippingZoneRoot .form-select,
    #addShippingZoneRoot .input-group-text,
    #addShippingZoneRoot .nav-link,
    #addShippingZoneRoot .badge,
    #addShippingZoneRoot .district-chip,
    #addShippingZoneRoot .preset-collapsible-box {
        border-radius: 5px !important;
    }

    #addShippingZoneRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #addShippingZoneRoot .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Compact & Sleek Button Sizing */
    #addShippingZoneRoot .btn-compact {
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

    /* Enterprise Main Card */
    .zone-form-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }
    .zone-form-header {
        padding: 12px 16px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        border-radius: 7px 7px 0 0;
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
    #addShippingZoneRoot .form-label {
        font-size: 11.5px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #addShippingZoneRoot .form-control,
    #addShippingZoneRoot .form-select {
        font-size: 12.5px;
        border: 1px solid #cbd5e1;
        padding: 6px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #addShippingZoneRoot .form-control:focus,
    #addShippingZoneRoot .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Collapsible District Picker Box */
    .preset-collapsible-box {
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        overflow: hidden;
    }
    .preset-collapse-header {
        padding: 10px 14px;
        background: #f1f5f9;
        cursor: pointer;
        user-select: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }
    .preset-collapse-body {
        padding: 12px;
        background: #ffffff;
        border-top: 1px solid #e2e8f0;
    }

    /* Interactive District Chips */
    .district-chips-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        max-height: 190px;
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
        .top-header-actions {
            width: 100%;
            display: flex;
            gap: 8px;
        }
        .top-header-actions .btn-compact {
            flex: 1 1 0;
            padding: 7px 12px !important;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="addShippingZoneRoot">
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

    <form action="{{ route('admin.shipping.zone.store') }}" method="POST" id="zoneCreateForm">
        @csrf

        <!-- Top Header & Actions -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
            <div>
                <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                    <i class="fas fa-plus-circle text-primary"></i>
                    <span>Add Zone</span>
                </h5>
                <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">Create a new delivery zone and assign districts or locations.</small>
            </div>
            <div class="top-header-actions d-flex gap-2">
                <a href="{{ route('admin.shipping.index') }}" class="btn btn-outline-secondary btn-compact shadow-xs">
                    <i class="fas fa-arrow-left me-1"></i>
                    <span>Back to Shipping Zones</span>
                </a>
                <button type="submit" class="btn btn-primary btn-compact px-3 shadow-xs">
                    <i class="fas fa-save me-1"></i>
                    <span>Save changes</span>
                </button>
            </div>
        </div>

        <div class="row g-3 g-md-4">
            <!-- Left Main Form Column -->
            <div class="col-12 col-lg-8">
                <div class="zone-form-card mb-3">
                    <div class="zone-form-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <span class="fw-bold text-dark" style="font-size: 13.5px;">
                            <i class="fas fa-map-pin text-primary me-1"></i> Zone Details &amp; Coverage
                        </span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 bn-digit font-monospace" id="selectedCountBadge" style="font-size: 10.5px;">
                            0 Locations Selected
                        </span>
                    </div>

                    <div class="p-3 p-md-4">
                        <!-- Quick Template Preset Dropdown -->
                        <div class="mb-3 p-2 px-3 bg-light border" style="border-radius: 5px;">
                            <label class="form-label mb-1 text-primary">
                                <i class="fas fa-bolt me-1"></i> Quick Zone Preset (Optional Auto-Fill)
                            </label>
                            <select id="zonePresetDropdown" class="form-select form-select-sm" onchange="applyZonePreset(this.value)">
                                <option value="">-- Choose a preset or type manually below --</option>
                                <option value="inside_dhaka">Inside Dhaka (Dhaka City)</option>
                                <option value="dhaka_sub">Dhaka Sub-Area (Gazipur, Narayanganj, Savar, Keraniganj)</option>
                                <option value="dhaka_div">Dhaka Division (All 13 Districts)</option>
                                <option value="ctg_metro">Chattogram Metro (Chattogram)</option>
                                <option value="outside_dhaka">Outside Dhaka (All 63 Districts except Dhaka)</option>
                                <option value="all_bd">All Bangladesh (All 64 Districts)</option>
                            </select>
                        </div>

                        <!-- 1. Zone Name -->
                        <div class="mb-3">
                            <label class="form-label" for="zoneNameInput">Zone Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="zoneNameInput" class="form-control fw-semibold" placeholder=" Inside Dhaka, Dhaka Sub-Area, Outside Dhaka" required>
                            <small class="text-muted d-block mt-1" style="font-size: 11px;">Give your zone a descriptive name ( Inside Dhaka).</small>
                        </div>

                        <!-- 2. Districts / Locations Textarea -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label mb-0" for="zoneDistrictsInput">Districts / Locations <span class="text-danger">*</span></label>
                                <button type="button" class="btn btn-link text-danger text-decoration-none p-0 small fw-semibold" style="font-size: 11px;" onclick="clearAllLocations()">
                                    <i class="fas fa-eraser me-1"></i>Clear All
                                </button>
                            </div>
                            <textarea name="districts" id="zoneDistrictsInput" class="form-control" rows="3" placeholder="Dhaka, Gazipur, Narayanganj" required oninput="syncChipsFromTextarea()"></textarea>
                            <small class="text-muted d-block mt-1" style="font-size: 11px;">Enter districts or areas separated by commas, or click districts from the selector below.</small>
                        </div>

                        <!-- Interactive Collapsible 64-District Selector -->
                        <div class="preset-collapsible-box">
                            <div class="preset-collapse-header" onclick="toggleDistrictPickerDrawer()">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-list-check text-primary"></i>
                                    <span class="fw-bold text-dark" style="font-size: 12px;">Interactive Bangladesh District Picker (Click to Toggle)</span>
                                </div>
                                <i class="fas fa-chevron-down text-muted small" id="pickerChevronIcon"></i>
                            </div>

                            <div class="preset-collapse-body" id="districtPickerBody">
                                <div class="row g-2 align-items-center mb-2">
                                    <div class="col-12 col-sm-7">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light border-end-0" style="height: 30px;"><i class="fas fa-search text-muted" style="font-size: 10px;"></i></span>
                                            <input type="text" id="chipSearchInput" class="form-control border-start-0" style="height: 30px; font-size: 11.5px;" placeholder="Filter district name..." oninput="filterDistrictChips(this.value)">
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-5 d-flex gap-1 justify-content-sm-end">
                                        <button type="button" class="btn btn-outline-primary btn-compact py-1 px-2 flex-grow-1 flex-sm-grow-0" style="font-size: 10.5px !important;" onclick="selectAllDistricts()">Select All 64</button>
                                        <button type="button" class="btn btn-outline-secondary btn-compact py-1 px-2 flex-grow-1 flex-sm-grow-0" style="font-size: 10.5px !important;" onclick="clearAllLocations()">Unselect All</button>
                                    </div>
                                </div>

                                <div class="district-chips-grid" id="districtChipsContainer"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Submit Button -->
                <div class="text-end mb-2">
                    <button type="submit" class="btn btn-primary btn-compact px-4 py-2 shadow-sm w-100 w-sm-auto" style="font-size: 13px !important;">
                        <i class="fas fa-save me-2"></i>
                        <span>Save changes</span>
                    </button>
                </div>
            </div>

            <!-- Right Guide Column -->
            <div class="col-12 col-lg-4">
                <div class="zone-form-card">
                    <div class="zone-form-header">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 13px;">
                            <i class="fas fa-circle-info text-primary"></i>
                            <span>How Shipping Zones Work</span>
                        </h6>
                    </div>
                    <div class="p-3 text-secondary" style="font-size: 12px; line-height: 1.5;">
                        <p class="mb-2">A shipping zone is a geographic region where a certain set of shipping methods and delivery rates apply.</p>
                        <ul class="ps-3 mb-0 d-flex flex-column gap-2">
                            <li><strong class="text-dark">Zone Name:</strong> Visible to admins and during checkout rate matching (, <em>Inside Dhaka</em>).</li>
                            <li><strong class="text-dark">Comma Separated:</strong> Multiple districts or sub-areas must be separated by commas (, <code>Dhaka, Gazipur, Narayanganj</code>).</li>
                            <li><strong class="text-dark">Next Step:</strong> After saving this zone, you can assign Flat Rate, Free Shipping, or Local Pickup methods to it.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    const BD_DISTRICTS = [
        "Dhaka", "Gazipur", "Narayanganj", "Narsingdi", "Manikganj", "Munshiganj", "Tangail", "Kishoreganj", "Faridpur", "Gopalganj", "Madaripur", "Rajbari", "Shariatpur",
        "Chattogram", "Cox's Bazar", "Cumilla", "Brahmanbaria", "Chandpur", "Feni", "Lakshmipur", "Noakhali", "Khagrachhari", "Rangamati", "Bandarban",
        "Sylhet", "Moulvibazar", "Habiganj", "Sunamganj",
        "Rajshahi", "Bogura", "Pabna", "Sirajganj", "Natore", "Naogaon", "Chapainawabganj", "Joypurhat",
        "Khulna", "Jashore", "Satkhira", "Kushtia", "Jhenaidah", "Magura", "Narail", "Bagerhat", "Chuadanga", "Meherpur",
        "Barishal", "Patuakhali", "Bhola", "Pirojpur", "Barguna", "Jhalokati",
        "Rangpur", "Dinajpur", "Kurigram", "Gaibandha", "Nilphamari", "Panchagarh", "Thakurgaon", "Lalমনিরহাট".replace("মনিরহাট", "monirhat"),
        "Mymensingh", "Jamalpur", "Netrokona", "Sherpur"
    ];

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
            badge.innerText = list.length + (list.length === 1 ? ' Location Selected' : ' Locations Selected');
        }
    }

    function applyZonePreset(presetKey) {
        const nameInput = document.getElementById('zoneNameInput');
        const distInput = document.getElementById('zoneDistrictsInput');
        if (!presetKey) return;

        if (presetKey === 'inside_dhaka') {
            nameInput.value = 'Inside Dhaka';
            distInput.value = 'Dhaka';
        } else if (presetKey === 'dhaka_sub') {
            nameInput.value = 'Dhaka Sub-Area';
            distInput.value = 'Gazipur, Narayanganj, Savar, Keraniganj, Dhamrai, Tongi';
        } else if (presetKey === 'dhaka_div') {
            nameInput.value = 'Dhaka Division';
            distInput.value = 'Dhaka, Gazipur, Narayanganj, Narsingdi, Manikganj, Munshiganj, Tangail, Kishoreganj, Faridpur, Gopalganj, Madaripur, Rajbari, Shariatpur';
        } else if (presetKey === 'ctg_metro') {
            nameInput.value = 'Chattogram Metro';
            distInput.value = 'Chattogram';
        } else if (presetKey === 'outside_dhaka') {
            nameInput.value = 'Outside Dhaka';
            distInput.value = BD_DISTRICTS.filter(d => d !== 'Dhaka').join(', ');
        } else if (presetKey === 'all_bd') {
            nameInput.value = 'All Bangladesh';
            distInput.value = BD_DISTRICTS.join(', ');
        }
        syncChipsFromTextarea();
    }

    function selectAllDistricts() {
        document.getElementById('zoneDistrictsInput').value = BD_DISTRICTS.join(', ');
        syncChipsFromTextarea();
    }

    function clearAllLocations() {
        document.getElementById('zoneDistrictsInput').value = '';
        document.getElementById('zonePresetDropdown').value = '';
        syncChipsFromTextarea();
    }

    function filterDistrictChips(query) {
        const q = (query || '').toLowerCase().trim();
        document.querySelectorAll('.district-chip').forEach(chip => {
            const name = (chip.getAttribute('data-name') || '').toLowerCase();
            chip.style.display = (!q || name.includes(q)) ? 'inline-flex' : 'none';
        });
    }

    function toggleDistrictPickerDrawer() {
        const body = document.getElementById('districtPickerBody');
        const icon = document.getElementById('pickerChevronIcon');
        if (!body) return;
        body.classList.toggle('d-none');
        if (icon) {
            icon.className = body.classList.contains('d-none')
                ? 'fas fa-chevron-right text-muted small'
                : 'fas fa-chevron-down text-muted small';
        }
    }

    document.addEventListener('DOMContentLoaded', renderDistrictChips);
</script>
@endsection