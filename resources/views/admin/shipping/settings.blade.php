@extends('layouts.admin')
@section('title', 'Shipping Settings - ' . config('app.name'))

@section('admin_content')
<style>
    /* Global Strict 5px Element Radius */
    #shippingSettingsRoot .btn,
    #shippingSettingsRoot .btn-sm,
    #shippingSettingsRoot button,
    #shippingSettingsRoot .form-control,
    #shippingSettingsRoot .form-select,
    #shippingSettingsRoot .nav-link,
    #shippingSettingsRoot .alert,
    #shippingSettingsRoot .badge,
    #shippingSettingsRoot .shipping-option-card,
    #shippingSettingsRoot .shipping-collapsible-box {
        border-radius: 5px !important;
    }

    /* Compact & Sleek Button Sizing */
    #shippingSettingsRoot .btn-compact {
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
    .shipping-main-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
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

    /* Collapsible Section Cards */
    .shipping-collapsible-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .shipping-collapsible-box:hover {
        border-color: #cbd5e1;
    }
    .shipping-collapsible-box.active-open {
        border-color: #2563eb !important;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.06);
    }
    .shipping-collapse-header {
        padding: 11px 14px;
        background: #f8fafc;
        cursor: pointer;
        user-select: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        transition: background-color 0.15s ease;
    }
    .shipping-collapse-header:hover {
        background: #f1f5f9;
    }
    .shipping-collapsible-box.active-open .shipping-collapse-header {
        background: #eff6ff;
        border-bottom: 1px solid #dbeafe;
    }
    .ship-chevron-icon {
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
    .shipping-collapsible-box.active-open .ship-chevron-icon {
        transform: rotate(180deg);
        background: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
    }
    .shipping-collapse-body {
        padding: 14px;
        background: #ffffff;
    }

    /* Interactive Option Rows (Checkboxes & Radios) */
    .shipping-option-card {
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        padding: 10px 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        transition: all 0.15s ease;
        margin: 0;
    }
    .shipping-option-card:hover {
        border-color: #93c5fd;
        background: #ffffff;
    }
    .shipping-option-card:has(input:checked) {
        border-color: #2563eb;
        background-color: #eff6ff;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.08);
    }
    .shipping-option-card:has(input:checked) .opt-title {
        color: #2563eb !important;
    }

    #shippingSettingsRoot .form-select {
        font-size: 12px;
        height: 33px;
        padding: 4px 10px;
        border: 1px solid #cbd5e1;
        color: #0f172a;
        box-shadow: none;
    }
    #shippingSettingsRoot .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    /* 100/100 Mobile Responsive Tuning */
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
        }
        .top-header-actions .btn-compact {
            width: 100%;
            padding: 7px 14px !important;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="shippingSettingsRoot">
    <!-- Sub-Menu Navigation (Mobile Scrollable) -->
    <div class="shipping-subnav-wrap mb-3 pb-2 border-bottom">
        <ul class="nav nav-pills shipping-nav-pills flex-nowrap text-nowrap">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.shipping.index') }}">
                    <i class="fas fa-globe-asia" style="font-size: 11px;"></i>
                    <span>Shipping zones</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('admin.shipping.settings') }}">
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

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-xs py-2 px-3 mb-3 small d-flex align-items-center justify-content-between" role="alert">
            <div>
                <i class="fas fa-check-circle text-success me-2"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.shipping.settings.update') }}" method="POST">
        @csrf

        <!-- Top Page Header & Quick Save -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
            <div>
                <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                    <i class="fas fa-truck-fast text-primary"></i>
                    <span>Shipping Configuration</span>
                </h5>
                <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">Manage cart shipping calculators, default destination rules, and debug logs.</small>
            </div>
            <div class="top-header-actions">
                <button type="submit" class="btn btn-primary btn-compact px-3 py-2 shadow-xs gap-1">
                    <i class="fas fa-save"></i>
                    <span>Save changes</span>
                </button>
            </div>
        </div>

        <div class="shipping-main-card p-3 p-md-4 mb-4">
            <!-- Quick Dropdown Selector Bar -->
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2 mb-3 pb-3 border-bottom">
                <div class="flex-grow-1">
                    <select id="shippingSectionDropdown" class="form-select fw-semibold" onchange="handleShippingSectionSelect(this.value)">
                        <option value="all">-- Select Setting Section (Show All Expanded) --</option>
                        <option value="calc">1. Calculations (Cart Calculator &amp; Cost Visibility)</option>
                        <option value="dest">2. Shipping Destination (Default Address Rules)</option>
                        <option value="debug">3. Debug Mode (Troubleshooting &amp; Rate Logs)</option>
                    </select>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light border btn-compact px-3 flex-grow-1" onclick="toggleAllShippingSections(true)">
                        <i class="fas fa-expand-alt me-1"></i>
                        <span>Expand All</span>
                    </button>
                    <button type="button" class="btn btn-light border btn-compact px-3 flex-grow-1" onclick="toggleAllShippingSections(false)">
                        <i class="fas fa-compress-alt me-1"></i>
                        <span>Collapse All</span>
                    </button>
                </div>
            </div>

            <div class="d-flex flex-column gap-3">
                <!-- 1. Calculations (Collapsible) -->
                <div class="shipping-collapsible-box active-open" id="shipBox_calc" data-sec="calc">
                    <div class="shipping-collapse-header" onclick="toggleShippingPanel('calc')">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-calculator text-primary"></i>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 13.5px;">Calculations</h6>
                            <span class="badge bg-white text-secondary border px-2 py-1" style="font-size: 9.5px;">Cart &amp; Rates</span>
                        </div>
                        <span class="ship-chevron-icon">
                            <i class="fas fa-chevron-down"></i>
                        </span>
                    </div>

                    <div class="shipping-collapse-body" id="shipBody_calc">
                        <div class="d-flex flex-column gap-2">
                            <label class="shipping-option-card" for="chk_calc_cart">
                                <div>
                                    <span class="fw-bold text-dark d-block opt-title" style="font-size: 12.5px;">Enable shipping calculator on cart page</span>
                                    <small class="text-muted" style="font-size: 11px;">Allows customers to estimate shipping cost before proceeding to checkout.</small>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input m-0" type="checkbox" id="chk_calc_cart" name="shipping_calc_on_cart" value="1" {{ ($settings['shipping_calc_on_cart'] ?? '0') == '1' ? 'checked' : '' }} style="width: 36px; height: 18px; cursor: pointer;">
                                </div>
                            </label>

                            <label class="shipping-option-card" for="chk_hide_cost">
                                <div>
                                    <span class="fw-bold text-dark d-block opt-title" style="font-size: 12.5px;">Hide shipping costs until address entered</span>
                                    <small class="text-muted" style="font-size: 11px;">Prevents showing shipping charges until a valid delivery address is provided.</small>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input m-0" type="checkbox" id="chk_hide_cost" name="shipping_hide_cost_before_address" value="1" {{ ($settings['shipping_hide_cost_before_address'] ?? '1') == '1' ? 'checked' : '' }} style="width: 36px; height: 18px; cursor: pointer;">
                                </div>
                            </label>

                            <label class="shipping-option-card" for="chk_hide_free">
                                <div>
                                    <span class="fw-bold text-dark d-block opt-title" style="font-size: 12.5px;">Hide shipping rates when free shipping available</span>
                                    <small class="text-muted" style="font-size: 11px;">Automatically hides flat rate options when an order qualifies for Free Shipping.</small>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input m-0" type="checkbox" id="chk_hide_free" name="shipping_hide_rates_when_free" value="1" {{ ($settings['shipping_hide_rates_when_free'] ?? '1') == '1' ? 'checked' : '' }} style="width: 36px; height: 18px; cursor: pointer;">
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 2. Shipping Destination (Collapsible) -->
                <div class="shipping-collapsible-box active-open" id="shipBox_dest" data-sec="dest">
                    <div class="shipping-collapse-header" onclick="toggleShippingPanel('dest')">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-map-marker-alt text-success"></i>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 13.5px;">Shipping destination</h6>
                            <span class="badge bg-white text-secondary border px-2 py-1" style="font-size: 9.5px;">Address Mode</span>
                        </div>
                        <span class="ship-chevron-icon">
                            <i class="fas fa-chevron-down"></i>
                        </span>
                    </div>

                    <div class="shipping-collapse-body" id="shipBody_dest">
                        <div class="d-flex flex-column gap-2">
                            <label class="shipping-option-card" for="dest1">
                                <div>
                                    <span class="fw-bold text-dark d-block opt-title" style="font-size: 12.5px;">Default to customer shipping address</span>
                                    <small class="text-muted" style="font-size: 11px;">Shows separate shipping address fields by default during checkout.</small>
                                </div>
                                <input class="form-check-input m-0 flex-shrink-0" type="radio" name="shipping_destination_mode" id="dest1" value="shipping" {{ ($settings['shipping_destination_mode'] ?? 'force_billing') == 'shipping' ? 'checked' : '' }} style="cursor: pointer;">
                            </label>

                            <label class="shipping-option-card" for="dest2">
                                <div>
                                    <span class="fw-bold text-dark d-block opt-title" style="font-size: 12.5px;">Default to customer billing address</span>
                                    <small class="text-muted" style="font-size: 11px;">Uses billing address first, with an optional toggle to ship to a different address.</small>
                                </div>
                                <input class="form-check-input m-0 flex-shrink-0" type="radio" name="shipping_destination_mode" id="dest2" value="billing" {{ ($settings['shipping_destination_mode'] ?? 'force_billing') == 'billing' ? 'checked' : '' }} style="cursor: pointer;">
                            </label>

                            <label class="shipping-option-card" for="dest3">
                                <div>
                                    <span class="fw-bold text-dark d-block opt-title" style="font-size: 12.5px;">Force shipping to customer billing address</span>
                                    <small class="text-muted" style="font-size: 11px;">Streamlines checkout into a single unified address form.</small>
                                </div>
                                <input class="form-check-input m-0 flex-shrink-0" type="radio" name="shipping_destination_mode" id="dest3" value="force_billing" {{ ($settings['shipping_destination_mode'] ?? 'force_billing') == 'force_billing' ? 'checked' : '' }} style="cursor: pointer;">
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 3. Debug Mode (Collapsible) -->
                <div class="shipping-collapsible-box active-open" id="shipBox_debug" data-sec="debug">
                    <div class="shipping-collapse-header" onclick="toggleShippingPanel('debug')">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-bug text-warning"></i>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 13.5px;">Debug mode</h6>
                            <span class="badge bg-warning-subtle text-dark border border-warning px-2 py-1" style="font-size: 9.5px;">Diagnostics</span>
                        </div>
                        <span class="ship-chevron-icon">
                            <i class="fas fa-chevron-down"></i>
                        </span>
                    </div>

                    <div class="shipping-collapse-body" id="shipBody_debug">
                        <label class="shipping-option-card" for="chk_ship_debug">
                            <div>
                                <span class="fw-bold text-dark d-block opt-title" style="font-size: 12.5px;">Enable shipping debug mode</span>
                                <small class="text-muted" style="font-size: 11px;">Bypasses shipping rate cache and displays matched shipping zones for troubleshooting.</small>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input m-0" type="checkbox" id="chk_ship_debug" name="shipping_debug_mode" value="1" {{ ($settings['shipping_debug_mode'] ?? '0') == '1' ? 'checked' : '' }} style="width: 36px; height: 18px; cursor: pointer;">
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-primary btn-compact px-4 py-2 shadow-sm w-100 w-sm-auto" style="font-size: 13px !important;">
                <i class="fas fa-save me-2"></i>
                <span>Save changes</span>
            </button>
        </div>
    </form>
</div>

<script>
    function toggleShippingPanel(key, forceOpen = null) {
        const box  = document.getElementById('shipBox_' + key);
        const body = document.getElementById('shipBody_' + key);
        if (!box || !body) return;

        const isOpen = !body.classList.contains('d-none');
        const nextState = forceOpen !== null ? forceOpen : !isOpen;

        if (nextState) {
            body.classList.remove('d-none');
            box.classList.add('active-open');
        } else {
            body.classList.add('d-none');
            box.classList.remove('active-open');
        }
    }

    function handleShippingSectionSelect(val) {
        const allBoxes = document.querySelectorAll('.shipping-collapsible-box');
        if (val === 'all') {
            allBoxes.forEach(b => {
                b.classList.remove('d-none');
                const sec = b.getAttribute('data-sec');
                toggleShippingPanel(sec, true);
            });
        } else {
            allBoxes.forEach(b => {
                const sec = b.getAttribute('data-sec');
                if (sec === val) {
                    b.classList.remove('d-none');
                    toggleShippingPanel(sec, true);
                } else {
                    b.classList.add('d-none');
                }
            });
        }
    }

    function toggleAllShippingSections(expand) {
        document.getElementById('shippingSectionDropdown').value = 'all';
        document.querySelectorAll('.shipping-collapsible-box').forEach(b => {
            b.classList.remove('d-none');
            const sec = b.getAttribute('data-sec');
            toggleShippingPanel(sec, expand);
        });
    }
</script>
@endsection