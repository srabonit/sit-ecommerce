@extends('layouts.admin')

@section('title', 'Theme Modules & Features - ' . config('app.name'))

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #themeModulesRoot .btn,
    #themeModulesRoot .btn-sm,
    #themeModulesRoot button,
    #themeModulesRoot .badge,
    #themeModulesRoot .alert,
    #themeModulesRoot .card,
    #themeModulesRoot .modules-header-card,
    #themeModulesRoot .module-section-card,
    #themeModulesRoot .module-item-box,
    #themeModulesRoot .status-kpi-pill,
    #themeModulesRoot .collapse-toggle-btn,
    .modules-modal-scope .modal-content,
    .modules-modal-scope .btn,
    .modules-modal-scope .badge {
        border-radius: 5px !important;
    }

    /* Safe Monospace & Bengali Digit Typography */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Compact Sleek Button Styling */
    .btn-compact {
        font-size: 11.5px !important;
        padding: 5px 14px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    /* Enterprise Surface Header Card */
    .modules-header-card {
        background: #0f172a;
        color: #ffffff;
        padding: 14px 18px;
        border: 1px solid #1e293b;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
        margin-bottom: 14px;
    }

    /* Section Card */
    .module-section-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 14px;
        overflow: hidden;
    }
    .module-section-header {
        padding: 10px 14px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    .section-title-text {
        font-size: 12.5px;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Collapsible Trigger Buttons & Rotating Chevron */
    .collapse-toggle-btn {
        width: 24px;
        height: 24px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        color: #475569;
        transition: all 0.2s ease;
    }
    .collapse-toggle-btn:hover {
        background-color: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
    }
    .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }
    .collapse-icon {
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        font-size: 10px;
    }

    /* Symmetric Mini KPI Pill */
    .status-kpi-pill {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        padding: 10px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        height: 100%;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
    }

    /* Module Item Box */
    .module-item-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        padding: 12px 14px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        transition: all 0.15s ease;
    }
    .module-item-box:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
    }

    .module-icon-badge {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        border-radius: 5px !important;
        flex-shrink: 0;
    }

    /* Enhanced Toggle Switches */
    .form-switch .form-check-input {
        width: 36px;
        height: 18px;
        cursor: pointer;
        border-color: #cbd5e1;
        transition: background-color 0.15s ease, border-color 0.15s ease;
    }
    .form-switch .form-check-input:checked {
        background-color: #2563eb;
        border-color: #2563eb;
    }

    /* Mobile Responsive (< 768px) */
    @media (max-width: 767.98px) {
        .modules-header-card { padding: 12px 14px !important; }
        .header-top-wrap {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 8px !important;
        }
        .top-action-btns {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .top-action-btns .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-2" id="themeModulesRoot">

    <!-- 1. Header & Quick Controls -->
    <div class="modules-header-card d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 header-top-wrap">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace text-uppercase" style="font-size: 9px;">
                    <i class="fas fa-cubes me-1"></i> STOREFRONT FEATURES
                </span>
                <span class="badge bg-light text-white-50 border border-secondary font-monospace" style="font-size: 9px;">
                    6 CORE MODULES
                </span>
            </div>
            <h5 class="fw-bold mb-0 d-flex align-items-center gap-2" style="font-size: 17px;">
                <i class="fas fa-toggle-on text-primary"></i>
                <span>Theme Modules &amp; Feature Toggles</span>
            </h5>
            <small class="text-white-50 d-block mt-1" style="font-size: 11.5px; line-height: 1.35;">
                Enable or disable essential e-commerce modules, checkout options, live search and purchase policies.
            </small>
        </div>

        <div class="top-action-btns d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary btn-compact text-white border-white-50 shadow-xs" onclick="toggleAllModuleSections()">
                <i class="fas fa-arrows-up-down me-1"></i>
                <span id="expandAllModulesBtnText">Collapse All</span>
            </button>
            <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold px-3" onclick="document.getElementById('themeModulesForm').submit()">
                <i class="fas fa-check-circle me-1"></i>
                <span>Save Module Settings</span>
            </button>
        </div>
    </div>

    <!-- 2. Status Summary KPI Row -->
    @php
        $activeCount = 0;
        $modulesKeys = ['module_ajax_cart_drawer', 'module_buy_now_btn', 'module_quick_view', 'module_ajax_search', 'module_instant_checkout', 'module_allow_stockout_orders'];
        foreach($modulesKeys as $k) {
            if (($settings[$k] ?? '1') == '1') $activeCount++;
        }
        $disabledCount = count($modulesKeys) - $activeCount;
    @endphp

    <div class="row g-2 mb-3">
        <div class="col-12 col-md-4">
            <div class="status-kpi-pill">
                <div class="min-w-0">
                    <small class="text-muted d-block fw-semibold text-truncate" style="font-size: 10.5px;">Active Features</small>
                    <strong class="text-success d-block font-monospace bn-digit" style="font-size: 13px;">{{ $activeCount }} Modules Active</strong>
                </div>
                <div class="text-success fs-5"><i class="fas fa-circle-check"></i></div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="status-kpi-pill">
                <div class="min-w-0">
                    <small class="text-muted d-block fw-semibold text-truncate" style="font-size: 10.5px;">Disabled Features</small>
                    <strong class="text-secondary d-block font-monospace bn-digit" style="font-size: 13px;">{{ $disabledCount }} Modules Off</strong>
                </div>
                <div class="text-secondary fs-5"><i class="fas fa-circle-pause"></i></div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="status-kpi-pill">
                <div class="min-w-0">
                    <small class="text-muted d-block fw-semibold text-truncate" style="font-size: 10.5px;">System Sync</small>
                    <strong class="text-primary d-block" style="font-size: 13px;">Real-Time Storefront</strong>
                </div>
                <div class="text-primary fs-5"><i class="fas fa-arrows-rotate"></i></div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-xs py-2 px-3 mb-2 small d-flex align-items-center justify-content-between" style="border-radius: 5px;">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.theme.setting.modules.update') }}" method="POST" id="themeModulesForm">
        @csrf

        <!-- Section 1: Cart, Checkout & Conversion Modules (COLLAPSIBLE) -->
        <div class="module-section-card">
            <div class="module-section-header d-flex justify-content-between align-items-center">
                <div class="section-title-text">
                    <i class="fas fa-shopping-bag text-primary"></i>
                    <span>Cart, Checkout &amp; Conversion Modules</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace text-uppercase" style="font-size: 8.5px;">PURCHASE FLOW</span>
                    <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseCartModules" aria-expanded="true" aria-controls="collapseCartModules" title="Toggle Section">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>
                </div>
            </div>

            <div class="collapse show" id="collapseCartModules">
                <div class="p-3 bg-light bg-opacity-25">
                    <div class="row g-2 g-md-3">
                        <!-- 1. Ajax Floating Cart Drawer -->
                        <div class="col-12 col-md-4">
                            <div class="module-item-box shadow-xs">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="module-icon-badge bg-primary-subtle text-primary">
                                        <i class="fas fa-shopping-bag"></i>
                                    </div>
                                    <div class="form-check form-switch m-0 ps-0">
                                        <input class="form-check-input ms-0" type="checkbox" name="module_ajax_cart_drawer" value="1" {{ ($settings['module_ajax_cart_drawer'] ?? '1') == '1' ? 'checked' : '' }}>
                                    </div>
                                </div>
                                <div>
                                    <strong class="text-dark d-block mb-1" style="font-size: 12.5px;">Ajax Floating Cart Drawer</strong>
                                    <small class="text-muted d-block font-monospace" style="font-size: 10.5px; line-height: 1.35;">
                                        Opens slide-out cart drawer smoothly upon adding products without reloading.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Buy Now Button -->
                        <div class="col-12 col-md-4">
                            <div class="module-item-box shadow-xs">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="module-icon-badge bg-success-subtle text-success">
                                        <i class="fas fa-bolt"></i>
                                    </div>
                                    <div class="form-check form-switch m-0 ps-0">
                                        <input class="form-check-input ms-0" type="checkbox" name="module_buy_now_btn" value="1" {{ ($settings['module_buy_now_btn'] ?? '1') == '1' ? 'checked' : '' }}>
                                    </div>
                                </div>
                                <div>
                                    <strong class="text-dark d-block mb-1" style="font-size: 12.5px;">Buy Now / Instant Order</strong>
                                    <small class="text-muted d-block font-monospace" style="font-size: 10.5px; line-height: 1.35;">
                                        Enables direct 1-click purchase button on product cards bypassing cart page.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Instant Checkout Modal -->
                        <div class="col-12 col-md-4">
                            <div class="module-item-box shadow-xs">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="module-icon-badge bg-danger-subtle text-danger">
                                        <i class="fas fa-cash-register"></i>
                                    </div>
                                    <div class="form-check form-switch m-0 ps-0">
                                        <input class="form-check-input ms-0" type="checkbox" name="module_instant_checkout" value="1" {{ ($settings['module_instant_checkout'] ?? '1') == '1' ? 'checked' : '' }}>
                                    </div>
                                </div>
                                <div>
                                    <strong class="text-dark d-block mb-1" style="font-size: 12.5px;">Instant Checkout Option</strong>
                                    <small class="text-muted d-block font-monospace" style="font-size: 10.5px; line-height: 1.35;">
                                        Renders express checkout modal window directly on storefront.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Discovery, Preview & Inventory Policy Modules (COLLAPSIBLE) -->
        <div class="module-section-card">
            <div class="module-section-header d-flex justify-content-between align-items-center">
                <div class="section-title-text">
                    <i class="fas fa-search text-warning"></i>
                    <span>Discovery, Preview &amp; Inventory Policy</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle font-monospace text-uppercase" style="font-size: 8.5px;">CATALOG RULES</span>
                    <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseDiscoveryModules" aria-expanded="true" aria-controls="collapseDiscoveryModules" title="Toggle Section">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>
                </div>
            </div>

            <div class="collapse show" id="collapseDiscoveryModules">
                <div class="p-3 bg-light bg-opacity-25">
                    <div class="row g-2 g-md-3">
                        <!-- 4. Ajax Product Search -->
                        <div class="col-12 col-md-4">
                            <div class="module-item-box shadow-xs">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="module-icon-badge bg-warning-subtle text-warning-emphasis">
                                        <i class="fas fa-search"></i>
                                    </div>
                                    <div class="form-check form-switch m-0 ps-0">
                                        <input class="form-check-input ms-0" type="checkbox" name="module_ajax_search" value="1" {{ ($settings['module_ajax_search'] ?? '1') == '1' ? 'checked' : '' }}>
                                    </div>
                                </div>
                                <div>
                                    <strong class="text-dark d-block mb-1" style="font-size: 12.5px;">Ajax Product Live Search</strong>
                                    <small class="text-muted d-block font-monospace" style="font-size: 10.5px; line-height: 1.35;">
                                        Renders real-time live product suggestions with prices while typing in header.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- 5. Quick View Modal -->
                        <div class="col-12 col-md-4">
                            <div class="module-item-box shadow-xs">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="module-icon-badge bg-info-subtle text-info">
                                        <i class="fas fa-eye"></i>
                                    </div>
                                    <div class="form-check form-switch m-0 ps-0">
                                        <input class="form-check-input ms-0" type="checkbox" name="module_quick_view" value="1" {{ ($settings['module_quick_view'] ?? '1') == '1' ? 'checked' : '' }}>
                                    </div>
                                </div>
                                <div>
                                    <strong class="text-dark d-block mb-1" style="font-size: 12.5px;">Quick View Modal Preview</strong>
                                    <small class="text-muted d-block font-monospace" style="font-size: 10.5px; line-height: 1.35;">
                                        Enables interactive product details popup modal right from catalog cards.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- 6. Allow Out of Stock Orders (Pre-Order) -->
                        <div class="col-12 col-md-4">
                            <div class="module-item-box shadow-xs">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="module-icon-badge bg-secondary-subtle text-secondary">
                                        <i class="fas fa-box-open"></i>
                                    </div>
                                    <div class="form-check form-switch m-0 ps-0">
                                        <input class="form-check-input ms-0" type="checkbox" name="module_allow_stockout_orders" value="1" {{ ($settings['module_allow_stockout_orders'] ?? '1') == '1' ? 'checked' : '' }}>
                                    </div>
                                </div>
                                <div>
                                    <strong class="text-dark d-block mb-1" style="font-size: 12.5px;">Allow Stockout / Pre-Orders</strong>
                                    <small class="text-muted d-block font-monospace" style="font-size: 10.5px; line-height: 1.35;">
                                        Permits customers to place orders even when inventory balance reaches zero.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Action Bar -->
        <div class="d-flex justify-content-end pt-2 pb-3">
            <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-4 w-100 w-md-auto" style="min-height: 35px; font-size: 12px;">
                <i class="fas fa-check-circle me-1"></i> Save Module Settings
            </button>
        </div>
    </form>
</div>

<!-- Modal: Custom Enterprise Notice Modal -->
<div class="modal fade modules-modal-scope" id="modulesNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 108500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="mb-2" id="modulesNoticeIconWrap">
                    <i class="fas fa-circle-check text-success fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="modulesNoticeTitle" style="font-size: 14px;">Notification</h6>
                <p class="text-muted small mb-3" id="modulesNoticeMessage" style="font-size: 12px; line-height: 1.45;"></p>
                <button type="button" class="btn btn-primary btn-compact px-4" data-bs-dismiss="modal">Acknowledge</button>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle All Module Sections (Expand All / Collapse All)
let allModuleSectionsExpanded = true;
function toggleAllModuleSections() {
    const collapses = document.querySelectorAll('#themeModulesRoot .module-section-card .collapse');
    const toggleBtns = document.querySelectorAll('#themeModulesRoot .module-section-card .collapse-toggle-btn');
    const btnText = document.getElementById('expandAllModulesBtnText');

    allModuleSectionsExpanded = !allModuleSectionsExpanded;

    collapses.forEach(col => {
        if (allModuleSectionsExpanded) {
            bootstrap.Collapse.getOrCreateInstance(col, { toggle: false }).show();
        } else {
            bootstrap.Collapse.getOrCreateInstance(col, { toggle: false }).hide();
        }
    });

    toggleBtns.forEach(btn => {
        btn.setAttribute('aria-expanded', allModuleSectionsExpanded ? 'true' : 'false');
    });

    if (btnText) {
        btnText.innerText = allModuleSectionsExpanded ? 'Collapse All' : 'Expand All';
    }
}
</script>
@endsection