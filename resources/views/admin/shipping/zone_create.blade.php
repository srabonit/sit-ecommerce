
@extends('layouts.admin')
@section('title', 'Add Zone - ' . config('app.name'))

@section('admin_content')
<style>
    #addShippingZoneRoot {
        --zone-primary: var(--bs-primary, #1572e8);
        --zone-border: var(--bs-border-color, #e7eaf3);
        --zone-muted: var(--bs-secondary-color, #6c757d);
        --zone-surface: #f8f9fa;
        --zone-radius: 5px;
    }

    /* Consistent Radius */
    #addShippingZoneRoot .btn,
    #addShippingZoneRoot button,
    #addShippingZoneRoot .form-control,
    #addShippingZoneRoot .form-select,
    #addShippingZoneRoot .input-group-text,
    #addShippingZoneRoot .nav-link,
    #addShippingZoneRoot .badge,
    #addShippingZoneRoot .district-chip,
    #addShippingZoneRoot .preset-collapsible-box {
        border-radius: var(--zone-radius);
    }

    #addShippingZoneRoot .input-group > .input-group-text {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    #addShippingZoneRoot .input-group > .form-control {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    /* Buttons */
    #addShippingZoneRoot .btn-compact {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.35;
        white-space: nowrap;
        transition: all .15s ease;
    }

    /* Main Card */
    #addShippingZoneRoot .zone-form-card {
        min-width: 0;
        overflow: hidden;
        background: #fff;
        border: 1px solid var(--zone-border);
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
    }

    #addShippingZoneRoot .zone-form-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 12px 16px;
        background: #f8f9fa;
        border-bottom: 1px solid var(--zone-border);
    }

    /* Navigation */
    #addShippingZoneRoot .shipping-subnav-wrap {
        overflow-x: auto;
        white-space: nowrap;
        scrollbar-width: none;
        -ms-overflow-style: none;
        -webkit-overflow-scrolling: touch;
    }

    #addShippingZoneRoot .shipping-subnav-wrap::-webkit-scrollbar {
        display: none;
    }

    #addShippingZoneRoot .shipping-nav-pills {
        display: inline-flex;
        flex-wrap: nowrap;
        gap: 4px;
        min-width: max-content;
        padding: 4px;
        background: #f1f3f5;
        border-radius: 6px;
    }

    #addShippingZoneRoot .shipping-nav-pills .nav-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 13px;
        color: #495057;
        background: transparent;
        font-size: 12px;
        font-weight: 600;
        transition: all .15s ease;
    }

    #addShippingZoneRoot .shipping-nav-pills .nav-link:hover {
        color: #212529;
        background: #fff;
    }

    #addShippingZoneRoot .shipping-nav-pills .nav-link.active {
        color: #fff;
        background: var(--zone-primary);
        box-shadow: 0 1px 3px rgba(0, 0, 0, .12);
    }

    /* Form Controls */
    #addShippingZoneRoot .form-label {
        display: block;
        margin-bottom: 5px;
        color: #343a40;
        font-size: 12px;
        font-weight: 600;
    }

    #addShippingZoneRoot .form-control,
    #addShippingZoneRoot .form-select {
        min-height: 36px;
        padding: 7px 10px;
        color: #343a40;
        background-color: #fff;
        border: 1px solid #ced4da;
        box-shadow: none;
        font-size: 12.5px;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #addShippingZoneRoot textarea.form-control {
        min-height: 76px;
        resize: vertical;
    }

    #addShippingZoneRoot .form-control:focus,
    #addShippingZoneRoot .form-select:focus {
        border-color: var(--zone-primary);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .12);
        outline: none;
    }

    #addShippingZoneRoot .form-control::placeholder {
        color: #98a1aa;
        opacity: 1;
    }

    /* District Picker */
    #addShippingZoneRoot .preset-collapsible-box {
        overflow: hidden;
        background: #fff;
        border: 1px solid #dee2e6;
    }

    #addShippingZoneRoot .preset-collapse-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 11px 14px;
        background: #f8f9fa;
        cursor: pointer;
        user-select: none;
        transition: background .15s ease;
    }

    #addShippingZoneRoot .preset-collapse-header:hover {
        background: #f0f3f6;
    }

    #addShippingZoneRoot .preset-collapse-body {
        padding: 12px;
        background: #fff;
        border-top: 1px solid var(--zone-border);
    }

    /* District Chips */
    #addShippingZoneRoot .district-chips-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        max-height: 220px;
        overflow-y: auto;
        padding: 3px;
        scrollbar-width: thin;
        scrollbar-color: #adb5bd transparent;
    }

    #addShippingZoneRoot .district-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 10px;
        color: #495057;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        font-size: 11.5px;
        font-weight: 600;
        cursor: pointer;
        user-select: none;
        transition: all .12s ease;
    }

    #addShippingZoneRoot .district-chip:hover {
        color: var(--zone-primary);
        background: #f0f6ff;
        border-color: #9ec5fe;
    }

    #addShippingZoneRoot .district-chip.selected {
        color: #fff;
        background: var(--zone-primary);
        border-color: var(--zone-primary);
    }

    #addShippingZoneRoot .district-chip:focus-visible {
        outline: 2px solid var(--zone-primary);
        outline-offset: 2px;
    }

    #addShippingZoneRoot .bn-digit,
    #addShippingZoneRoot .font-monospace {
        font-weight: 700;
    }

    /* Responsive */
    @media (max-width: 767.98px) {
        #addShippingZoneRoot {
            padding-right: 10px !important;
            padding-left: 10px !important;
        }

        #addShippingZoneRoot .shipping-nav-pills .nav-link {
            padding: 7px 11px;
            font-size: 11.5px;
        }

        #addShippingZoneRoot .top-header-actions {
            display: flex;
            width: 100%;
            gap: 8px;
        }

        #addShippingZoneRoot .top-header-actions .btn-compact {
            flex: 1 1 0;
            padding: 8px 10px;
        }

        #addShippingZoneRoot .zone-form-header {
            padding: 10px 12px;
        }

        #addShippingZoneRoot .preset-collapse-body {
            padding: 10px;
        }

        #addShippingZoneRoot .district-chips-grid {
            max-height: 240px;
            gap: 6px;
        }

        #addShippingZoneRoot .district-chip {
            padding: 6px 9px;
        }
    }

    @media (max-width: 420px) {
        #addShippingZoneRoot .zone-form-card .p-3 {
            padding: 12px !important;
        }

        #addShippingZoneRoot .form-control,
        #addShippingZoneRoot .form-select {
            font-size: 12px;
        }

        #addShippingZoneRoot .top-header-actions .btn-compact {
            font-size: 11px;
        }

        #addShippingZoneRoot .zone-form-header {
            align-items: flex-start;
        }

        #addShippingZoneRoot .district-chips-grid {
            max-height: 260px;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="addShippingZoneRoot">

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

    <form action="{{ route('admin.shipping.zone.store') }}" method="POST" id="zoneCreateForm">
        @csrf

        <!-- Header -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
            <div>
                <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                    <i class="fas fa-plus-circle text-primary"></i>
                    <span>Add Zone</span>
                </h5>
                <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                    Create a delivery zone.
                </small>
            </div>

            <div class="top-header-actions d-flex gap-2">
                <a href="{{ route('admin.shipping.index') }}" class="btn btn-outline-secondary btn-compact shadow-xs">
                    <i class="fas fa-arrow-left me-1"></i>
                    <span>Back</span>
                </a>

                <button type="submit" class="btn btn-primary btn-compact px-3 shadow-xs">
                    <i class="fas fa-save me-1"></i>
                    <span>Save</span>
                </button>
            </div>
        </div>

        <div class="row g-3 g-md-4">

            <!-- Main Form -->
            <div class="col-12 col-lg-8">
                <div class="zone-form-card mb-3">

                    <div class="zone-form-header">
                        <span class="fw-bold text-dark" style="font-size: 13.5px;">
                            <i class="fas fa-map-pin text-primary me-1"></i>
                            Zone Details
                        </span>

                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 bn-digit font-monospace" id="selectedCountBadge" style="font-size: 10.5px;">
                            0 Locations Selected
                        </span>
                    </div>

                    <div class="p-3 p-md-4">

                        <!-- Quick Preset -->
                        <div class="mb-3 p-2 px-3 bg-light border" style="border-radius: 5px;">
                            <label class="form-label mb-1 text-primary">
                                <i class="fas fa-bolt me-1"></i>
                                Quick Preset
                            </label>

                            <select id="zonePresetDropdown" class="form-select form-select-sm" onchange="applyZonePreset(this.value)">
                                <option value="">-- Choose preset --</option>
                                <option value="inside_dhaka">Inside Dhaka</option>
                                <option value="dhaka_sub">Dhaka Sub-Area</option>
                                <option value="dhaka_div">Dhaka Division (13 Districts)</option>
                                <option value="ctg_metro">Chattogram Metro</option>
                                <option value="outside_dhaka">Outside Dhaka (63 Districts)</option>
                                <option value="all_bd">All Bangladesh (64 Districts)</option>
                            </select>
                        </div>

                        <!-- Zone Name -->
                        <div class="mb-3">
                            <label class="form-label" for="zoneNameInput">
                                Zone Name <span class="text-danger">*</span>
                            </label>

                            <input type="text" name="name" id="zoneNameInput" class="form-control fw-semibold" placeholder="Inside Dhaka" required>

                            <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                Enter a name.
                            </small>
                        </div>

                        <!-- Districts -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label mb-0" for="zoneDistrictsInput">
                                    Districts / Locations <span class="text-danger">*</span>
                                </label>

                                <button type="button" class="btn btn-link text-danger text-decoration-none p-0 small fw-semibold" style="font-size: 11px;" onclick="clearAllLocations()">
                                    <i class="fas fa-eraser me-1"></i>Clear
                                </button>
                            </div>

                            <textarea name="districts" id="zoneDistrictsInput" class="form-control" rows="3" placeholder="Dhaka, Gazipur, Narayanganj" required oninput="syncChipsFromTextarea()"></textarea>

                            <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                Separate with commas.
                            </small>
                        </div>

                        <!-- District Picker -->
                        <div class="preset-collapsible-box">
                            <div class="preset-collapse-header" onclick="toggleDistrictPickerDrawer()">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-list-check text-primary"></i>
                                    <span class="fw-bold text-dark" style="font-size: 12px;">
                                        District Picker
                                    </span>
                                </div>

                                <i class="fas fa-chevron-down text-muted small" id="pickerChevronIcon"></i>
                            </div>

                            <div class="preset-collapse-body" id="districtPickerBody">
                                <div class="row g-2 align-items-center mb-2">

                                    <div class="col-12 col-sm-7">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light border-end-0" style="height: 30px;">
                                                <i class="fas fa-search text-muted" style="font-size: 10px;"></i>
                                            </span>

                                            <input type="text" id="chipSearchInput" class="form-control border-start-0" style="height: 30px; font-size: 11.5px;" placeholder="Search districts..." oninput="filterDistrictChips(this.value)">
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-5 d-flex gap-1 justify-content-sm-end">
                                        <button type="button" class="btn btn-outline-primary btn-compact py-1 px-2 flex-grow-1 flex-sm-grow-0" style="font-size: 10.5px !important;" onclick="selectAllDistricts()">
                                            Select All
                                        </button>

                                        <button type="button" class="btn btn-outline-secondary btn-compact py-1 px-2 flex-grow-1 flex-sm-grow-0" style="font-size: 10.5px !important;" onclick="clearAllLocations()">
                                            Clear
                                        </button>
                                    </div>
                                </div>

                                <div class="district-chips-grid" id="districtChipsContainer"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Submit -->
                <div class="text-end mb-2">
                    <button type="submit" class="btn btn-primary btn-compact px-4 py-2 shadow-sm w-100 w-sm-auto" style="font-size: 13px !important;">
                        <i class="fas fa-save me-2"></i>
                        <span>Save</span>
                    </button>
                </div>
            </div>

            <!-- Guide -->
            <div class="col-12 col-lg-4">
                <div class="zone-form-card">

                    <div class="zone-form-header">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 13px;">
                            <i class="fas fa-circle-info text-primary"></i>
                            <span>Zone Guide</span>
                        </h6>
                    </div>

                    <div class="p-3 text-secondary" style="font-size: 12px; line-height: 1.5;">
                        <p class="mb-2">
                            Group locations with the same shipping rates.
                        </p>

                        <ul class="ps-3 mb-0 d-flex flex-column gap-2">
                            <li>
                                <strong class="text-dark">Zone Name:</strong>
                                Shown in admin and checkout.
                            </li>

                            <li>
                                <strong class="text-dark">Locations:</strong>
                                Separate with commas.
                                <code>Dhaka, Gazipur</code>
                            </li>

                            <li>
                                <strong class="text-dark">Next:</strong>
                                Add shipping methods after saving.
                            </li>
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
