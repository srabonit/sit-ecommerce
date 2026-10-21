@extends('layouts.admin')

@section('title', 'Popular Categories Slider - ' . config('app.name'))

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #popCategoriesRoot .btn,
    #popCategoriesRoot .btn-sm,
    #popCategoriesRoot button,
    #popCategoriesRoot .form-control,
    #popCategoriesRoot .badge,
    #popCategoriesRoot .card,
    #popCategoriesRoot .header-banner-card,
    #popCategoriesRoot .panel-section-card,
    #popCategoriesRoot .status-kpi-pill,
    #popCategoriesRoot .cat-multi-select-box,
    #popCategoriesRoot .cat-chip,
    #popCategoriesRoot .cat-check-item,
    #popCategoriesRoot .collapse-toggle-btn,
    .popcat-modal-scope .modal-content,
    .popcat-modal-scope .btn,
    .popcat-modal-scope .badge {
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
    .header-banner-card {
        background: #0f172a;
        color: #ffffff;
        padding: 14px 18px;
        border: 1px solid #1e293b;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
        margin-bottom: 14px;
    }

    /* Panel Section Cards */
    .panel-section-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 14px;
        overflow: hidden;
    }
    .panel-section-header {
        padding: 11px 16px;
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

    /* Form Controls */
    .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }

    /* Enhanced Category Selection Box */
    .cat-multi-select-box {
        border: 1px solid #cbd5e1;
        background: #ffffff;
        overflow: hidden;
    }
    .cat-select-search {
        border: none;
        border-bottom: 1px solid #e2e8f0;
        border-radius: 0 !important;
        padding: 8px 12px;
        font-size: 12px;
        outline: none;
        box-shadow: none !important;
        background: #ffffff;
    }
    .cat-checklist-container {
        max-height: 240px;
        overflow-y: auto;
        padding: 6px 8px;
    }
    .cat-checklist-container::-webkit-scrollbar { width: 4px; }
    .cat-checklist-container::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .cat-check-item {
        padding: 6px 10px;
        transition: background-color 0.12s ease;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 2px;
        border: 1px solid transparent;
    }
    .cat-check-item:hover {
        background-color: #f1f5f9;
        border-color: #e2e8f0;
    }
    .selected-chips-box {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        padding: 6px 10px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        min-height: 38px;
        max-height: 90px;
        overflow-y: auto;
    }
    .cat-chip {
        background: #2563eb;
        color: #ffffff;
        padding: 3px 8px;
        font-size: 10.5px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        max-width: 100%;
    }
    .cat-chip .chip-remove {
        cursor: pointer;
        font-size: 13px;
        line-height: 1;
        opacity: 0.8;
    }
    .cat-chip .chip-remove:hover {
        opacity: 1;
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
        .header-banner-card { padding: 12px 14px !important; }
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
        .panel-section-header {
            padding: 10px 12px;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-2" id="popCategoriesRoot">

    <!-- 1. Header & Quick Controls -->
    <div class="header-banner-card d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 header-top-wrap">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace text-uppercase" style="font-size: 9px;">
                    <i class="fas fa-shield-heart me-1"></i> STOREFRONT SLIDER
                </span>
                <span class="badge {{ $sectionStatus ? 'bg-success' : 'bg-secondary' }} font-monospace text-uppercase" style="font-size: 9px;">
                    {{ $sectionStatus ? 'Slider Live' : 'Slider Hidden' }}
                </span>
            </div>
            <h5 class="fw-bold mb-0 d-flex align-items-center gap-2" style="font-size: 17px;">
                <i class="fas fa-shield-heart text-primary"></i>
                <span>Popular Categories Slider Settings</span>
            </h5>
            <small class="text-white-50 d-block mt-1" style="font-size: 11.5px; line-height: 1.35;">
                Control homepage circular category slider visibility, custom items allocation and product link routing.
            </small>
        </div>

        <div class="top-action-btns d-flex gap-2">
            <a href="{{ route('admin.category.index') }}" class="btn btn-outline-secondary btn-compact text-white border-white-50 shadow-xs">
                <i class="fas fa-list me-1"></i>
                <span>Categories List</span>
            </a>
            <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold px-3" onclick="document.getElementById('popularCategoriesForm').submit()">
                <i class="fas fa-check-circle me-1"></i>
                <span>Save Settings</span>
            </button>
        </div>
    </div>

    <!-- 2. Status Summary KPI Row (Symmetric 3-Column Grid) -->
    @php
        $selectedCount = count($selectedCategoryIds ?? []);
        $totalCats = count($allCategories);
    @endphp

    <div class="row g-2 mb-3">
        <div class="col-12 col-md-4">
            <div class="status-kpi-pill">
                <div class="min-w-0">
                    <small class="text-muted d-block fw-semibold text-truncate" style="font-size: 10.5px;">Slider Visibility</small>
                    <strong class="text-dark d-block" style="font-size: 13px;">{{ $sectionStatus ? 'Enabled (Active)' : 'Disabled (Hidden)' }}</strong>
                </div>
                <div class="text-{{ $sectionStatus ? 'success' : 'secondary' }} fs-5">
                    <i class="fas fa-toggle-{{ $sectionStatus ? 'on' : 'off' }}"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="status-kpi-pill">
                <div class="min-w-0">
                    <small class="text-muted d-block fw-semibold text-truncate" style="font-size: 10.5px;">Selected Showcase</small>
                    <strong class="text-primary d-block font-monospace bn-digit" style="font-size: 13px;">
                        {{ $selectedCount > 0 ?$selectedCount . ' Categories' : 'All Categories (Auto)' }}
                    </strong>
                </div>
                <div class="text-primary fs-5">
                    <i class="fas fa-check-double"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="status-kpi-pill">
                <div class="min-w-0">
                    <small class="text-muted d-block fw-semibold text-truncate" style="font-size: 10.5px;">Total Catalog Categories</small>
                    <strong class="text-dark d-block font-monospace bn-digit" style="font-size: 13px;">{{ $totalCats }} Available</strong>
                </div>
                <div class="text-warning fs-5">
                    <i class="fas fa-layer-group"></i>
                </div>
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

    <form action="{{ route('admin.theme.setting.popular_categories.update') }}" method="POST" id="popularCategoriesForm">
        @csrf

        <!-- Section 1: Visibility & Behavior Control (COLLAPSIBLE) -->
        <div class="panel-section-card">
            <div class="panel-section-header d-flex justify-content-between align-items-center">
                <div class="section-title-text">
                    <i class="fas fa-sliders text-primary"></i>
                    <span>Section Visibility &amp; Display Rules</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace text-uppercase" style="font-size: 8.5px;">SLIDER ON/OFF</span>
                    <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseVisibilityRules" aria-expanded="true" aria-controls="collapseVisibilityRules" title="Toggle Section">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>
                </div>
            </div>

            <div class="collapse show" id="collapseVisibilityRules">
                <div class="p-3 bg-light bg-opacity-25">
                    <div class="p-3 bg-white border d-flex align-items-center justify-content-between" style="border-radius: 5px;">
                        <div class="pe-2">
                            <label class="form-check-label d-block fw-bold text-dark" for="popCatSecSwitch" style="font-size: 12px; cursor: pointer;">
                                Homepage Popular Categories Slider
                            </label>
                            <small class="text-muted d-block" style="font-size: 11px; line-height: 1.35;">
                                Enable or disable circular icon category showcase carousel on storefront homepage.
                            </small>
                        </div>
                        <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                            <input class="form-check-input ms-0" type="checkbox" name="show_popular_categories_section" value="1" id="popCatSecSwitch" {{ $sectionStatus ? 'checked' : '' }}>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Category Selection & Checklist (COLLAPSIBLE) -->
        <div class="panel-section-card">
            <div class="panel-section-header d-flex justify-content-between align-items-center">
                <div class="section-title-text">
                    <i class="fas fa-list-check text-success"></i>
                    <span>Category Selection &amp; Showcase Allocations</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace text-uppercase" style="font-size: 8.5px;">CUSTOM ITEMS</span>
                    <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseCategorySelection" aria-expanded="true" aria-controls="collapseCategorySelection" title="Toggle Section">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>
                </div>
            </div>

            <div class="collapse show" id="collapseCategorySelection">
                <div class="p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label mb-0">Select Featured Categories</label>
                        <small class="text-muted font-monospace" style="font-size: 10.5px;">
                            Leave blank to show all active categories automatically.
                        </small>
                    </div>

                    <div class="cat-multi-select-box shadow-xs" style="border-radius: 5px;">
                        <!-- Selected Category Chips Wrapper -->
                        <div class="selected-chips-box" id="selectedCatChipsWrapper">
                            <span class="text-muted small my-auto placeholder-text" style="font-size: 11px;">No categories selected yet.</span>
                        </div>

                        <!-- Real-time Filter Input -->
                        <input type="text" id="catFilterInput" class="form-control cat-select-search" placeholder="Type category title to search instantly...">

                        <!-- Checklist Container with Counter Badges -->
                        <div class="cat-checklist-container" id="catChecklist">
                            @foreach($allCategories as$cat)
                                @php
                                    $isChecked = in_array($cat->id,$selectedCategoryIds ?? []);
                                @endphp
                                <label class="cat-check-item" data-title="{{ strtolower($cat->name) }}">
                                    <div class="d-flex align-items-center gap-2 min-w-0">
                                        <input class="form-check-input mt-0 cat-checkbox flex-shrink-0" type="checkbox" name="category_ids[]" value="{{ $cat->id }}" data-name="{{ $cat->name }}" {{ $isChecked ? 'checked' : '' }}>
                                        <span class="small fw-medium text-dark text-truncate" style="font-size: 11.5px;">{{ $cat->name }}</span>
                                    </div>
                                    <span class="badge bg-light text-secondary border small font-monospace bn-digit flex-shrink-0 ms-2" style="font-size: 9.5px;">
                                        {{ $cat->products_count ?? 0 }} Items
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button Bar -->
        <div class="d-flex justify-content-end pt-2 pb-3">
            <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-4 w-100 w-md-auto" style="min-height: 35px; font-size: 12px;">
                <i class="fas fa-check-circle me-1"></i> Save Changes
            </button>
        </div>
    </form>
</div>

<!-- Modal: Custom Enterprise Notice Modal -->
<div class="modal fade popcat-modal-scope" id="popCatNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 108500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="mb-2" id="popCatNoticeIconWrap">
                    <i class="fas fa-circle-check text-success fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="popCatNoticeTitle" style="font-size: 14px;">Notification</h6>
                <p class="text-muted small mb-3" id="popCatNoticeMessage" style="font-size: 12px; line-height: 1.45;"></p>
                <button type="button" class="btn btn-primary btn-compact px-4" data-bs-dismiss="modal">Acknowledge</button>
            </div>
        </div>
    </div>
</div>

<script>
// Universal Notice Modal
function showPopCatModalNotice(message, title = 'Notice', type = 'info') {
    const titleEl = document.getElementById('popCatNoticeTitle');
    const msgEl   = document.getElementById('popCatNoticeMessage');
    const iconEl  = document.getElementById('popCatNoticeIconWrap');

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

    const modalEl = document.getElementById('popCatNoticeModal');
    if (modalEl && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

document.addEventListener('DOMContentLoaded', function () {
    @if(session('success'))
        showPopCatModalNotice('{{ session('success') }}', 'Success', 'success');
    @endif

    // Real-time Category Search
    const searchInput = document.getElementById('catFilterInput');
    const items = document.querySelectorAll('.cat-check-item');
    const chipsWrapper = document.getElementById('selectedCatChipsWrapper');
    const checkboxes = document.querySelectorAll('.cat-checkbox');

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            items.forEach(item => {
                const title = item.getAttribute('data-title');
                if (title && title.includes(query)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    // Render Active Category Chips
    function renderCatChips() {
        chipsWrapper.innerHTML = '';
        let hasSelected = false;

        checkboxes.forEach(cb => {
            if (cb.checked) {
                hasSelected = true;
                const id = cb.value;
                const name = cb.getAttribute('data-name');

                const chip = document.createElement('span');
                chip.className = 'cat-chip shadow-xs';
                chip.innerHTML = `
                    <span class="text-truncate" style="max-width: 140px;">${name}</span>
                    <span class="chip-remove" data-id="${id}" title="Remove">&times;</span>
                `;
                chipsWrapper.appendChild(chip);
            }
        });

        if (!hasSelected) {
            chipsWrapper.innerHTML = '<span class="text-muted small my-auto placeholder-text" style="font-size: 11px;">No categories selected yet. All will display automatically.</span>';
        }
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', renderCatChips);
    });

    chipsWrapper.addEventListener('click', function (e) {
        if (e.target.classList.contains('chip-remove')) {
            const targetId = e.target.getAttribute('data-id');
            const targetCb = document.querySelector(`.cat-checkbox[value="${targetId}"]`);
            if (targetCb) {
                targetCb.checked = false;
                renderCatChips();
            }
        }
    });

    renderCatChips();
});
</script>
@endsection