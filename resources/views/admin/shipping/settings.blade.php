
@extends('layouts.admin')
@section('title', 'Shipping Settings - ' . config('app.name'))

@section('admin_content')
<style>
    #shippingSettingsRoot {
        --ship-primary: var(--bs-primary, #1572e8);
        --ship-border: var(--bs-border-color, #e7eaf3);
        --ship-muted: var(--bs-secondary-color, #6c757d);
        --ship-surface: #f8f9fa;
        --ship-radius: 5px;
    }

    /* Consistent Radius */
    #shippingSettingsRoot .btn,
    #shippingSettingsRoot button,
    #shippingSettingsRoot .form-control,
    #shippingSettingsRoot .form-select,
    #shippingSettingsRoot .nav-link,
    #shippingSettingsRoot .alert,
    #shippingSettingsRoot .badge,
    #shippingSettingsRoot .shipping-option-card,
    #shippingSettingsRoot .shipping-collapsible-box {
        border-radius: var(--ship-radius);
    }

    /* Compact Buttons */
    #shippingSettingsRoot .btn-compact {
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

    #shippingSettingsRoot .btn:focus-visible,
    #shippingSettingsRoot .form-select:focus-visible,
    #shippingSettingsRoot .shipping-collapse-header:focus-visible {
        outline: 2px solid var(--ship-primary);
        outline-offset: 2px;
    }

    /* Main Card */
    #shippingSettingsRoot .shipping-main-card {
        background: #fff;
        border: 1px solid var(--ship-border);
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
    }

    /* Sub Navigation */
    #shippingSettingsRoot .shipping-subnav-wrap {
        overflow-x: auto;
        white-space: nowrap;
        scrollbar-width: none;
        -ms-overflow-style: none;
        -webkit-overflow-scrolling: touch;
    }

    #shippingSettingsRoot .shipping-subnav-wrap::-webkit-scrollbar {
        display: none;
    }

    #shippingSettingsRoot .shipping-nav-pills {
        display: inline-flex;
        flex-wrap: nowrap;
        gap: 4px;
        padding: 4px;
        background: #f1f3f5;
        border-radius: 6px;
        min-width: max-content;
    }

    #shippingSettingsRoot .shipping-nav-pills .nav-link {
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

    #shippingSettingsRoot .shipping-nav-pills .nav-link:hover {
        color: #212529;
        background: #fff;
    }

    #shippingSettingsRoot .shipping-nav-pills .nav-link.active {
        color: #fff;
        background: var(--ship-primary);
        box-shadow: 0 1px 3px rgba(0, 0, 0, .12);
    }

    /* Collapsible Sections */
    #shippingSettingsRoot .shipping-collapsible-box {
        overflow: hidden;
        background: #fff;
        border: 1px solid var(--ship-border);
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #shippingSettingsRoot .shipping-collapsible-box:hover {
        border-color: #cbd2da;
    }

    #shippingSettingsRoot .shipping-collapsible-box.active-open {
        border-color: var(--ship-primary);
        box-shadow: 0 2px 7px rgba(21, 114, 232, .06);
    }

    #shippingSettingsRoot .shipping-collapse-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 12px 14px;
        background: #f8f9fa;
        cursor: pointer;
        user-select: none;
        transition: background .15s ease;
    }

    #shippingSettingsRoot .shipping-collapse-header:hover {
        background: #f1f3f5;
    }

    #shippingSettingsRoot .shipping-collapsible-box.active-open .shipping-collapse-header {
        background: #f0f6ff;
        border-bottom: 1px solid #dce9fa;
    }

    #shippingSettingsRoot .shipping-collapse-header > div {
        min-width: 0;
        flex-wrap: wrap;
    }

    #shippingSettingsRoot .ship-chevron-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 26px;
        height: 26px;
        color: #495057;
        background: #fff;
        border: 1px solid var(--ship-border);
        border-radius: 5px;
        font-size: 10px;
        transition: transform .2s ease, background .15s ease, color .15s ease;
    }

    #shippingSettingsRoot .shipping-collapsible-box.active-open .ship-chevron-icon {
        color: #fff;
        background: var(--ship-primary);
        border-color: var(--ship-primary);
        transform: rotate(180deg);
    }

    #shippingSettingsRoot .shipping-collapse-body {
        padding: 14px;
        background: #fff;
    }

    /* Option Cards */
    #shippingSettingsRoot .shipping-option-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 11px 12px;
        margin: 0;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        cursor: pointer;
        transition: border-color .15s ease, background .15s ease;
    }

    #shippingSettingsRoot .shipping-option-card:hover {
        background: #fff;
        border-color: #9ec5fe;
    }

    #shippingSettingsRoot .shipping-option-card:has(input:checked) {
        background: #f0f6ff;
        border-color: var(--ship-primary);
        box-shadow: 0 1px 3px rgba(21, 114, 232, .08);
    }

    #shippingSettingsRoot .shipping-option-card:has(input:checked) .opt-title {
        color: var(--ship-primary) !important;
    }

    #shippingSettingsRoot .shipping-option-card > div:first-child {
        min-width: 0;
    }

    #shippingSettingsRoot .shipping-option-card .opt-title {
        font-size: 12.5px;
        line-height: 1.4;
    }

    #shippingSettingsRoot .shipping-option-card small {
        display: block;
        margin-top: 3px;
        font-size: 11px;
        line-height: 1.45;
    }

    #shippingSettingsRoot .shipping-option-card .form-check-input {
        flex-shrink: 0;
    }

    /* Select */
    #shippingSettingsRoot .form-select {
        min-height: 36px;
        padding: 6px 30px 6px 10px;
        color: #343a40;
        background-color: #fff;
        border: 1px solid #ced4da;
        box-shadow: none;
        font-size: 12px;
    }

    #shippingSettingsRoot .form-select:focus {
        border-color: var(--ship-primary);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .12);
    }

    /* Responsive */
    @media (max-width: 767.98px) {
        #shippingSettingsRoot {
            padding-right: 10px;
            padding-left: 10px;
        }

        #shippingSettingsRoot .shipping-nav-pills .nav-link {
            padding: 7px 11px;
            font-size: 11.5px;
        }

        #shippingSettingsRoot .top-header-actions {
            width: 100%;
        }

        #shippingSettingsRoot .top-header-actions .btn-compact {
            width: 100%;
            padding: 8px 12px;
        }

        #shippingSettingsRoot .shipping-main-card {
            padding: 12px !important;
        }

        #shippingSettingsRoot .shipping-collapse-header {
            padding: 10px;
        }

        #shippingSettingsRoot .shipping-collapse-body {
            padding: 10px;
        }

        #shippingSettingsRoot .shipping-option-card {
            padding: 10px;
            gap: 8px;
        }

        #shippingSettingsRoot .shipping-option-card .opt-title {
            font-size: 12px;
        }

        #shippingSettingsRoot .shipping-option-card small {
            font-size: 10.5px;
        }
    }

    @media (max-width: 400px) {
        #shippingSettingsRoot .shipping-main-card {
            padding: 10px !important;
        }

        #shippingSettingsRoot .shipping-collapse-header > div {
            gap: 6px !important;
        }

        #shippingSettingsRoot .shipping-collapse-header h6 {
            font-size: 12.5px !important;
        }

        #shippingSettingsRoot .shipping-option-card {
            align-items: flex-start;
        }

        #shippingSettingsRoot .shipping-option-card .form-check {
            padding-top: 2px;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="shippingSettingsRoot">
    <!-- Shipping Navigation -->
    <div class="shipping-subnav-wrap mb-3 pb-2 border-bottom">
        <ul class="nav nav-pills shipping-nav-pills flex-nowrap text-nowrap">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.shipping.index') }}">
                    <i class="fas fa-globe-asia" style="font-size: 11px;"></i>
                    <span>Zones</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('admin.shipping.settings') }}">
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

        <!-- Page Header -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
            <div>
                <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                    <i class="fas fa-truck-fast text-primary"></i>
                    <span>Shipping Settings</span>
                </h5>
                <small class="text-muted d-block" style="font-size: 12px; line-height: 1.4;">Manage rates, addresses and logs.</small>
            </div>
            <div class="top-header-actions">
                <button type="submit" class="btn btn-primary btn-compact px-3 py-2 shadow-xs gap-1">
                    <i class="fas fa-save"></i>
                    <span>Save</span>
                </button>
            </div>
        </div>

        <div class="shipping-main-card p-3 p-md-4 mb-4">
            <!-- Section Controls -->
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2 mb-3 pb-3 border-bottom">
                <div class="flex-grow-1">
                    <select id="shippingSectionDropdown" class="form-select fw-semibold" onchange="handleShippingSectionSelect(this.value)">
                        <option value="all">All Sections</option>
                        <option value="calc">1. Calculations</option>
                        <option value="dest">2. Destination</option>
                        <option value="debug">3. Debug Mode</option>
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
                <!-- Calculations -->
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
                                    <span class="fw-bold text-dark d-block opt-title">Cart shipping calculator</span>
                                    <small class="text-muted">Let customers check shipping costs before checkout.</small>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input m-0" type="checkbox" id="chk_calc_cart" name="shipping_calc_on_cart" value="1" {{ ($settings['shipping_calc_on_cart'] ?? '0') == '1' ? 'checked' : '' }} style="width: 36px; height: 18px; cursor: pointer;">
                                </div>
                            </label>

                            <label class="shipping-option-card" for="chk_hide_cost">
                                <div>
                                    <span class="fw-bold text-dark d-block opt-title">Hide rates before address</span>
                                    <small class="text-muted">Show shipping costs after a valid address is entered.</small>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input m-0" type="checkbox" id="chk_hide_cost" name="shipping_hide_cost_before_address" value="1" {{ ($settings['shipping_hide_cost_before_address'] ?? '1') == '1' ? 'checked' : '' }} style="width: 36px; height: 18px; cursor: pointer;">
                                </div>
                            </label>

                            <label class="shipping-option-card" for="chk_hide_free">
                                <div>
                                    <span class="fw-bold text-dark d-block opt-title">Hide other rates with free shipping</span>
                                    <small class="text-muted">Hide flat rates when an order qualifies for free shipping.</small>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input m-0" type="checkbox" id="chk_hide_free" name="shipping_hide_rates_when_free" value="1" {{ ($settings['shipping_hide_rates_when_free'] ?? '1') == '1' ? 'checked' : '' }} style="width: 36px; height: 18px; cursor: pointer;">
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Shipping Destination -->
                <div class="shipping-collapsible-box active-open" id="shipBox_dest" data-sec="dest">
                    <div class="shipping-collapse-header" onclick="toggleShippingPanel('dest')">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-map-marker-alt text-success"></i>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 13.5px;">Destination</h6>
                            <span class="badge bg-white text-secondary border px-2 py-1" style="font-size: 9.5px;">Address</span>
                        </div>
                        <span class="ship-chevron-icon">
                            <i class="fas fa-chevron-down"></i>
                        </span>
                    </div>

                    <div class="shipping-collapse-body" id="shipBody_dest">
                        <div class="d-flex flex-column gap-2">
                            <label class="shipping-option-card" for="dest1">
                                <div>
                                    <span class="fw-bold text-dark d-block opt-title">Shipping address by default</span>
                                    <small class="text-muted">Show separate shipping address fields at checkout.</small>
                                </div>
                                <input class="form-check-input m-0 flex-shrink-0" type="radio" name="shipping_destination_mode" id="dest1" value="shipping" {{ ($settings['shipping_destination_mode'] ?? 'force_billing') == 'shipping' ? 'checked' : '' }} style="cursor: pointer;">
                            </label>

                            <label class="shipping-option-card" for="dest2">
                                <div>
                                    <span class="fw-bold text-dark d-block opt-title">Billing address by default</span>
                                    <small class="text-muted">Use billing address, with an option for a different shipping address.</small>
                                </div>
                                <input class="form-check-input m-0 flex-shrink-0" type="radio" name="shipping_destination_mode" id="dest2" value="billing" {{ ($settings['shipping_destination_mode'] ?? 'force_billing') == 'billing' ? 'checked' : '' }} style="cursor: pointer;">
                            </label>

                            <label class="shipping-option-card" for="dest3">
                                <div>
                                    <span class="fw-bold text-dark d-block opt-title">Use billing address only</span>
                                    <small class="text-muted">Use one address form for checkout.</small>
                                </div>
                                <input class="form-check-input m-0 flex-shrink-0" type="radio" name="shipping_destination_mode" id="dest3" value="force_billing" {{ ($settings['shipping_destination_mode'] ?? 'force_billing') == 'force_billing' ? 'checked' : '' }} style="cursor: pointer;">
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Debug Mode -->
                <div class="shipping-collapsible-box active-open" id="shipBox_debug" data-sec="debug">
                    <div class="shipping-collapse-header" onclick="toggleShippingPanel('debug')">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-bug text-warning"></i>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 13.5px;">Debug Mode</h6>
                            <span class="badge bg-warning-subtle text-dark border border-warning px-2 py-1" style="font-size: 9.5px;">Logs</span>
                        </div>
                        <span class="ship-chevron-icon">
                            <i class="fas fa-chevron-down"></i>
                        </span>
                    </div>

                    <div class="shipping-collapse-body" id="shipBody_debug">
                        <label class="shipping-option-card" for="chk_ship_debug">
                            <div>
                                <span class="fw-bold text-dark d-block opt-title">Enable shipping debug</span>
                                <small class="text-muted">Bypass rate cache and show matched zones for troubleshooting.</small>
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
                <span>Save</span>
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
