@extends('layouts.admin')

@section('title', 'Featured Products Settings - ' . config('app.name'))

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #featured-settings-root .btn,
    #featured-settings-root .btn-sm,
    #featured-settings-root button,
    #featured-settings-root .form-control,
    #featured-settings-root .form-select,
    #featured-settings-root .badge,
    #featured-settings-root .card,
    #featured-settings-root .settings-main-card,
    #featured-settings-root .settings-panel-box,
    #featured-settings-root .status-kpi-pill,
    #featured-settings-root .collapse-toggle-btn,
    .featured-modal-scope .modal-content,
    .featured-modal-scope .btn,
    .featured-modal-scope .badge {
        border-radius: 5px !important;
    }

    /* Safe Monospace & Bengali Digit Typography */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Compact Button Styling */
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

    /* Enterprise Surface Cards */
    .settings-main-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 16px;
    }

    .settings-panel-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 14px 16px;
        margin-bottom: 14px;
        transition: all 0.2s ease;
    }

    /* Status Mini KPI Pill */
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

    /* Form Controls */
    .form-label {
        font-size: 11.5px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }

    #featured-settings-root .form-control,
    #featured-settings-root .form-select {
        min-height: 34px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #featured-settings-root .form-control:focus,
    #featured-settings-root .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Enhanced Toggle Switches */
    .form-switch .form-check-input {
        width: 38px;
        height: 20px;
        cursor: pointer;
        border-color: #cbd5e1;
        transition: background-color 0.15s ease, border-color 0.15s ease;
    }
    .form-switch .form-check-input:checked {
        background-color: #2563eb;
        border-color: #2563eb;
    }
    .form-check-label {
        font-size: 11.5px;
        font-weight: 600;
        color: #1e293b;
        cursor: pointer;
        user-select: none;
    }

    /* Collapsible Trigger Buttons & Rotating Chevron */
    .collapse-toggle-btn {
        width: 26px;
        height: 26px;
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

    /* Mobile Responsive Optimizations (< 768px) */
    @media (max-width: 767.98px) {
        .settings-panel-box {
            padding: 12px !important;
        }
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

<div class="container-fluid px-2 px-md-4 py-3" id="featured-settings-root">

    <!-- 1. Header & Quick Actions -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 header-top-wrap">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace text-uppercase" style="font-size: 9.5px;">
                    <i class="fas fa-fire me-1"></i> HOMEPAGE SHOWCASE
                </span>
                <span class="badge {{ $showFeatured ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' }} font-monospace text-uppercase" style="font-size: 9.5px;">
                    {{ $showFeatured ? 'Live on Store' : 'Hidden' }}
                </span>
            </div>
            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-fire text-danger"></i>
                <span>Featured Products Settings</span>
            </h5>
            <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">
                Configure homepage featured showcase visibility, pagination behavior and display quantities.
            </small>
        </div>

        <div class="top-action-btns d-flex gap-2">
            <a href="{{ route('admin.product.index') }}" class="btn btn-light border btn-compact shadow-xs text-secondary">
                <i class="fas fa-boxes me-1 text-primary"></i>
                <span>Manage Products</span>
            </a>
            <a href="{{ url('/') }}" target="_blank" class="btn btn-outline-primary btn-compact shadow-xs">
                <i class="fas fa-arrow-up-right-from-square me-1"></i>
                <span>View Storefront</span>
            </a>
        </div>
    </div>

    <!-- 2. Section Overview Symmetric KPI Cards -->
    <div class="row g-2 mb-3">
        <div class="col-12 col-md-4">
            <div class="status-kpi-pill">
                <div class="min-w-0">
                    <small class="text-muted d-block fw-semibold text-truncate" style="font-size: 10.5px;">Section Visibility</small>
                    <strong class="text-dark d-block" style="font-size: 13px;">{{ $showFeatured ? 'Enabled (Active)' : 'Disabled' }}</strong>
                </div>
                <div class="text-{{ $showFeatured ? 'success' : 'secondary' }} fs-5">
                    <i class="fas fa-toggle-{{ $showFeatured ? 'on' : 'off' }}"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="status-kpi-pill">
                <div class="min-w-0">
                    <small class="text-muted d-block fw-semibold text-truncate" style="font-size: 10.5px;">Pagination Status</small>
                    <strong class="text-dark d-block" style="font-size: 13px;">{{ $enablePagination ? 'Paginated' : 'Direct Limit' }}</strong>
                </div>
                <div class="text-primary fs-5">
                    <i class="fas fa-list-ol"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="status-kpi-pill">
                <div class="min-w-0">
                    <small class="text-muted d-block fw-semibold text-truncate" style="font-size: 10.5px;">Display Quantity</small>
                    <strong class="text-dark d-block font-monospace bn-digit" style="font-size: 13px;">{{ $featuredCount }} Products</strong>
                </div>
                <div class="text-warning fs-5">
                    <i class="fas fa-boxes-stacked"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Main Settings Form Card -->
    <div class="card settings-main-card">
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('admin.theme.setting.featured_products.update') }}" method="POST" id="featuredSettingsForm">
                @csrf
                <!-- Hidden inputs for backend controller parameter compatibility -->
                <input type="hidden" name="featured_grid_pc" value="{{ $gridPc ?? 4 }}">
                <input type="hidden" name="featured_section_banner" value="{{ $featuredBanner ?? '' }}">

                <!-- Panel 1: Visibility & Behavior Switches (COLLAPSIBLE) -->
                <div class="settings-panel-box">
                    <div class="d-flex justify-content-between align-items-center pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-sliders text-primary"></i>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 13px;">Section Visibility &amp; Behavior Rules</h6>
                        </div>
                        <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseVisibilityRules" aria-expanded="true" aria-controls="collapseVisibilityRules" title="Toggle Section View">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>

                    <div class="collapse show" id="collapseVisibilityRules">
                        <div class="row g-3 pt-3">
                            <!-- Switch 1: Enable Section -->
                            <div class="col-12 col-md-6">
                                <div class="p-3 bg-white border d-flex align-items-center justify-content-between h-100" style="border-radius: 5px;">
                                    <div class="pe-2">
                                        <label class="form-check-label d-block fw-bold" for="featSecSwitch">
                                            Homepage Featured Section
                                        </label>
                                        <small class="text-muted d-block" style="font-size: 11px; line-height: 1.35;">
                                            Showcase featured products spotlight block on storefront.
                                        </small>
                                    </div>
                                    <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                        <input class="form-check-input ms-0" type="checkbox" name="show_featured_products_section" value="1" id="featSecSwitch" {{ $showFeatured ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>

                            <!-- Switch 2: Enable Pagination -->
                            <div class="col-12 col-md-6">
                                <div class="p-3 bg-white border d-flex align-items-center justify-content-between h-100" style="border-radius: 5px;">
                                    <div class="pe-2">
                                        <label class="form-check-label d-block fw-bold" for="featPagSwitch">
                                            Section Pagination Controls
                                        </label>
                                        <small class="text-muted d-block" style="font-size: 11px; line-height: 1.35;">
                                            Render page navigation buttons below the product showcase.
                                        </small>
                                    </div>
                                    <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                        <input class="form-check-input ms-0" type="checkbox" name="featured_enable_pagination" value="1" id="featPagSwitch" {{ $enablePagination ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel 2: Product Limits & Display Density (COLLAPSIBLE) -->
                <div class="settings-panel-box mb-3">
                    <div class="d-flex justify-content-between align-items-center pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-boxes-stacked text-primary"></i>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 13px;">Product Display Limits &amp; Allocation</h6>
                        </div>
                        <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseLimitRules" aria-expanded="true" aria-controls="collapseLimitRules" title="Toggle Section View">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>

                    <div class="collapse show" id="collapseLimitRules">
                        <div class="pt-3">
                            <label class="form-label">
                                Products To Display Per Page (Limit) <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="featured_products_count" id="featured_products_count_input" class="form-control font-monospace bn-digit" value="{{ $featuredCount }}" min="1" max="100" required>
                            <small class="text-muted d-block mt-1 font-monospace" style="font-size: 10.5px;">
                                Enter any valid number (e.g. 8, 10, 12, 16 or 24).
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Submit Button Bar -->
                <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                    <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-4" style="min-height: 35px; font-size: 12px;">
                        <i class="fas fa-check-circle me-1"></i> Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Custom Enterprise Notice / Alert Modal -->
<div class="modal fade featured-modal-scope" id="featuredNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 108500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="mb-2" id="featuredNoticeIconWrap">
                    <i class="fas fa-circle-check text-success fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="featuredNoticeTitle" style="font-size: 14px;">Notification</h6>
                <p class="text-muted small mb-3" id="featuredNoticeMessage" style="font-size: 12px; line-height: 1.45;"></p>
                <button type="button" class="btn btn-primary btn-compact px-4" data-bs-dismiss="modal">Acknowledge</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Custom Enterprise Notice Modal Display
    function showFeaturedModalNotice(message, title = 'Notice', type = 'info') {
        const titleEl = document.getElementById('featuredNoticeTitle');
        const msgEl   = document.getElementById('featuredNoticeMessage');
        const iconEl  = document.getElementById('featuredNoticeIconWrap');

        if (titleEl) titleEl.innerText = title;
        if (msgEl) msgEl.innerText = message;

        if (iconEl) {
            if (type === 'danger' || type === 'error') {
                iconEl.innerHTML = '<i class="fas fa-circle-xmark text-danger fa-2x"></i>';
            } else if (type === 'success') {
                iconEl.innerHTML = '<i class="fas fa-circle-check text-success fa-2x"></i>';
            } else if (type === 'warning') {
                iconEl.innerHTML = '<i class="fas fa-triangle-exclamation text-warning fa-2x"></i>';
            } else {
                iconEl.innerHTML = '<i class="fas fa-circle-info text-primary fa-2x"></i>';
            }
        }

        const modalEl = document.getElementById('featuredNoticeModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    // Form Client-side Validation Guard
    document.getElementById('featuredSettingsForm').addEventListener('submit', function(e) {
        const countInput = document.getElementById('featured_products_count_input');
        const val = parseInt(countInput.value);

        if (isNaN(val) || val < 1 || val > 100) {
            e.preventDefault();
            showFeaturedModalNotice('Please enter a valid product count between 1 and 100.', 'Invalid Quantity', 'warning');
            return false;
        }
    });
</script>
@endsection