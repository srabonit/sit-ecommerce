@extends('layouts.admin')
@section('title', 'Shipping Zones - ' . config('app.name'))

@section('admin_content')
<style>
    /* Global Strict 5px Element Radius */
    #shippingZonesRoot .btn,
    #shippingZonesRoot .btn-sm,
    #shippingZonesRoot button,
    #shippingZonesRoot .form-control,
    #shippingZonesRoot .form-select,
    #shippingZonesRoot .input-group-text,
    #shippingZonesRoot .nav-link,
    #shippingZonesRoot .alert,
    #shippingZonesRoot .badge,
    #shippingZonesRoot .zone-mobile-card,
    #deleteShippingZoneModal .btn,
    #deleteShippingZoneModal .modal-content {
        border-radius: 5px !important;
    }

    #shippingZonesRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #shippingZonesRoot .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Compact & Sleek Button Sizing */
    #shippingZonesRoot .btn-compact,
    #deleteShippingZoneModal .btn-compact {
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

    .zone-action-sq-btn {
        width: 27px !important;
        height: 27px !important;
        min-width: 27px !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 11px !important;
        border-radius: 5px !important;
        transition: all 0.15s ease;
    }

    /* Enterprise Main Card */
    .shipping-panel-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        overflow: hidden;
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

    /* Filter Controls */
    .zone-filter-bar {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 12px 16px;
    }
    .zone-filter-control {
        font-size: 12px !important;
        height: 33px !important;
        padding: 4px 10px !important;
        border: 1px solid #cbd5e1 !important;
        background-color: #ffffff !important;
        color: #0f172a !important;
        box-shadow: none !important;
    }
    .zone-filter-control:focus {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
        outline: none;
    }

    /* Desktop Table Styling */
    .custom-zone-table th {
        background-color: #f8fafc;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 10px 16px;
        white-space: nowrap;
    }
    .custom-zone-table td {
        padding: 11px 16px;
        vertical-align: middle;
        font-size: 12.5px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }
    .custom-zone-table tbody tr:hover {
        background-color: #fafbfc;
    }

    /* Collapsible Mobile Zone Cards */
    .zone-mobile-card {
        border: 1px solid #e2e8f0;
        background: #ffffff;
        overflow: hidden;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .zone-mobile-card.active-open {
        border-color: #2563eb !important;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.08);
    }
    .zone-mobile-header {
        padding: 10px 12px;
        background: #f8fafc;
        cursor: pointer;
        user-select: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }
    .zone-mobile-card.active-open .zone-mobile-header {
        background: #eff6ff;
        border-bottom: 1px solid #dbeafe;
    }
    .zone-chevron {
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
    .zone-mobile-card.active-open .zone-chevron {
        transform: rotate(180deg);
        background: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
    }
    .zone-mobile-body {
        padding: 12px;
        background: #ffffff;
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
        .top-add-zone-wrap {
            width: 100%;
        }
        .top-add-zone-wrap .btn-compact {
            width: 100%;
            padding: 7px 14px !important;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="shippingZonesRoot">
    <!-- Sub-Menu Navigation (Mobile Scrollable) -->
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

    <!-- Top Header & Add Zone Button -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                    <i class="fas fa-map-location-dot text-primary"></i>
                    <span>Shipping zones</span>
                </h5>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 bn-digit font-monospace" style="font-size: 11px;">
                    {{ count($zones) }} Zones
                </span>
            </div>
            <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">Manage geographic delivery zones and their assigned shipping methods.</small>
        </div>
        <div class="top-add-zone-wrap">
            <a href="{{ route('admin.shipping.zone.create') }}" class="btn btn-primary btn-compact px-3 py-2 shadow-xs gap-1">
                <i class="fas fa-plus" style="font-size: 10px;"></i>
                <span>Add zone</span>
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

    <div class="shipping-panel-card">
        <!-- Quick Zone Dropdown Selector & Search Toolbar -->
        <div class="zone-filter-bar">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <select id="zoneQuickDropdown" class="form-select zone-filter-control fw-semibold" onchange="filterShippingZones()">
                        <option value="all">-- Select Shipping Zone (Show All {{ count($zones) }} Zones) --</option>
                        @foreach($zones as $zone)
                            <option value="{{ $zone->id }}">{{ $zone->name }} ({{ count($zone->methods) }} methods)</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0" style="height: 33px; border-color: #cbd5e1;"><i class="fas fa-search text-muted" style="font-size: 11px;"></i></span>
                        <input type="text" id="zoneSearchInput" class="form-control border-start-0 zone-filter-control" placeholder="Search by zone name, region or method..." oninput="filterShippingZones()">
                    </div>
                </div>
                <div class="col-12 col-md-2 text-md-end">
                    <button type="button" class="btn btn-light border btn-compact w-100" style="height: 33px;" onclick="resetZoneFilters()">
                        <i class="fas fa-rotate-left me-1"></i>
                        <span>Reset</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 1. Desktop Table View -->
        <div class="table-responsive d-none d-md-block">
            <table class="table custom-zone-table align-middle mb-0">
                <thead>
                    <tr>
                        <th style="min-width: 160px;">Zone name</th>
                        <th style="min-width: 200px;">Region(s)</th>
                        <th style="min-width: 240px;">Shipping method(s)</th>
                        <th class="text-end" style="min-width: 110px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="desktopZoneTbody">
                    @forelse($zones as $zone)
                        @php
                            $regions = $zone->locations->pluck('code')->toArray();
                            $regionText = empty($regions) ? 'Everywhere' : implode(', ', $regions);
                        @endphp
                        <tr class="zone-data-item" data-zone-id="{{ $zone->id }}" data-search="{{ strtolower($zone->name . ' ' . $regionText . ' ' . $zone->methods->pluck('title')->implode(' ')) }}">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-location-dot text-primary"></i>
                                    <strong class="text-dark" style="font-size: 13px;">{{ $zone->name }}</strong>
                                </div>
                            </td>
                            <td class="small text-muted" style="max-width: 280px;">
                                @if(empty($regions))
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-1" style="font-size: 10px;">
                                        <i class="fas fa-earth-asia me-1"></i>Everywhere
                                    </span>
                                @else
                                    <span class="text-dark" style="font-size: 12px; line-height: 1.4;">{{ $regionText }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @forelse($zone->methods as $method)
                                        <span class="badge bg-light text-dark border px-2 py-1 fw-semibold" style="font-size: 10.5px;">
                                            <i class="fas fa-truck-fast text-primary me-1" style="font-size: 9.5px;"></i>{{ $method->title }} ({{ ucfirst(str_replace('_', ' ', $method->method_type)) }})
                                        </span>
                                    @empty
                                        <span class="text-muted small" style="font-size: 11.5px;">No shipping methods added</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="text-end text-nowrap">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <a href="{{ route('admin.shipping.zone.edit', $zone->id) }}" class="btn btn-light border text-dark btn-compact shadow-xs" title="Edit Zone">
                                        <i class="fas fa-edit text-primary me-1" style="font-size: 10px;"></i>
                                        <span>Edit</span>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger zone-action-sq-btn shadow-xs" title="Delete Zone" onclick="openDeleteZoneModal('{{ route('admin.shipping.zone.destroy', $zone->id) }}', '{{ addslashes($zone->name) }}')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted small">
                                <i class="fas fa-map-location-dot fa-2x mb-2 d-block opacity-25"></i>
                                <span class="fw-semibold">No shipping zones found.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- 2. Mobile Collapsible Accordion Stack View -->
        <div class="d-block d-md-none p-2">
            <div class="d-flex flex-column gap-2" id="mobileZoneStack">
                @forelse($zones as $index => $zone)
                    @php
                        $regions = $zone->locations->pluck('code')->toArray();
                        $regionText = empty($regions) ? 'Everywhere' : implode(', ', $regions);
                    @endphp
                    <div class="zone-mobile-card zone-data-item {{ $index === 0 ? 'active-open' : '' }}" id="mobZoneCard_{{ $zone->id }}" data-zone-id="{{ $zone->id }}" data-search="{{ strtolower($zone->name . ' ' . $regionText . ' ' . $zone->methods->pluck('title')->implode(' ')) }}">
                        <div class="zone-mobile-header" onclick="toggleMobileZoneCard({{ $zone->id }})">
                            <div class="d-flex align-items-center gap-2 min-width-0">
                                <i class="fas fa-location-dot text-primary"></i>
                                <strong class="text-dark text-truncate" style="font-size: 13px;">{{ $zone->name }}</strong>
                                <span class="badge bg-white text-secondary border px-2 py-0 bn-digit" style="font-size: 9.5px;">{{ count($zone->methods) }} methods</span>
                            </div>
                            <span class="zone-chevron flex-shrink-0">
                                <i class="fas fa-chevron-down"></i>
                            </span>
                        </div>

                        <div class="zone-mobile-body {{ $index === 0 ? '' : 'd-none' }}" id="mobZoneBody_{{ $zone->id }}">
                            <div class="mb-2">
                                <small class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 10px;">Region(s)</small>
                                <div class="text-dark small" style="font-size: 12px; line-height: 1.4;">{{ $regionText }}</div>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 10px;">Shipping Method(s)</small>
                                <div class="d-flex flex-wrap gap-1">
                                    @forelse($zone->methods as $method)
                                        <span class="badge bg-light text-dark border px-2 py-1 fw-semibold" style="font-size: 10.5px;">
                                            <i class="fas fa-truck-fast text-primary me-1" style="font-size: 9.5px;"></i>{{ $method->title }} ({{ ucfirst(str_replace('_', ' ', $method->method_type)) }})
                                        </span>
                                    @empty
                                        <span class="text-muted small" style="font-size: 11px;">No methods configured</span>
                                    @endforelse
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2 pt-2 border-top">
                                <a href="{{ route('admin.shipping.zone.edit', $zone->id) }}" class="btn btn-outline-primary btn-compact flex-grow-1">
                                    <i class="fas fa-edit me-1"></i>
                                    <span>Edit Zone</span>
                                </a>
                                <button type="button" class="btn btn-outline-danger btn-compact flex-grow-1" onclick="openDeleteZoneModal('{{ route('admin.shipping.zone.destroy', $zone->id) }}', '{{ addslashes($zone->name) }}')">
                                    <i class="fas fa-trash-alt me-1"></i>
                                    <span>Delete</span>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-muted small">
                        <i class="fas fa-map-location-dot fa-2x mb-2 d-block opacity-25"></i>
                        <span class="fw-semibold">No shipping zones found.</span>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal (Replaces native confirm()) -->
<div class="modal fade" id="deleteShippingZoneModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 46px; height: 46px;">
                    <i class="fas fa-trash-alt fs-5"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 14.5px;">Delete Shipping Zone?</h6>
                <p class="text-muted small mb-3" style="font-size: 12px; line-height: 1.45;">
                    Are you sure you want to permanently delete <strong id="deleteZoneNameText" class="text-dark"></strong> and all its methods?
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light border btn-compact px-3" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteShippingZoneForm" method="POST" action="" class="m-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-compact px-3 shadow-xs">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openDeleteZoneModal(actionUrl, zoneName) {
        document.getElementById('deleteShippingZoneForm').action = actionUrl;
        document.getElementById('deleteZoneNameText').innerText = '"' + zoneName + '"';
        bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteShippingZoneModal')).show();
    }

    function toggleMobileZoneCard(zoneId, forceOpen = null) {
        const card = document.getElementById('mobZoneCard_' + zoneId);
        const body = document.getElementById('mobZoneBody_' + zoneId);
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

    function filterShippingZones() {
        const selectedId = document.getElementById('zoneQuickDropdown').value;
        const query = (document.getElementById('zoneSearchInput').value || '').toLowerCase().trim();

        document.querySelectorAll('.zone-data-item').forEach(el => {
            const zId = el.getAttribute('data-zone-id');
            const searchStr = el.getAttribute('data-search') || '';

            const matchDropdown = (selectedId === 'all' || zId === selectedId);
            const matchSearch = (!query || searchStr.includes(query));

            if (matchDropdown && matchSearch) {
                el.classList.remove('d-none');
                if (selectedId !== 'all' && el.classList.contains('zone-mobile-card')) {
                    toggleMobileZoneCard(zId, true);
                }
            } else {
                el.classList.add('d-none');
            }
        });
    }

    function resetZoneFilters() {
        document.getElementById('zoneQuickDropdown').value = 'all';
        document.getElementById('zoneSearchInput').value = '';
        filterShippingZones();
    }
</script>
@endsection