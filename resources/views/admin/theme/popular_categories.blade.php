@extends('layouts.admin')

@section('title', 'Popular Categories Slider - ' . config('app.name'))

@php
    // Safe dynamic route resolver for category directory (Prevents RouteNotFoundException)
    $categoryIndexRoute = '#';
    if (\Illuminate\Support\Facades\Route::has('admin.categories.index')) {
        $categoryIndexRoute = route('admin.categories.index');
    } elseif (\Illuminate\Support\Facades\Route::has('admin.category.index')) {
        $categoryIndexRoute = route('admin.category.index');
    } elseif (\Illuminate\Support\Facades\Route::has('admin.category')) {
        $categoryIndexRoute = route('admin.category');
    } elseif (\Illuminate\Support\Facades\Route::has('categories.index')) {
        $categoryIndexRoute = route('categories.index');
    } else {
        $categoryIndexRoute = url('admin/category');
    }

    $selectedCount = count($selectedCategoryIds ?? []);
    $totalCats = count($allCategories ?? []);
@endphp

@section('admin_content')
<!-- Font Awesome 6.5.1 CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<!-- Google Fonts: Plus Jakarta Sans & Inter -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

<style>
    /* Professional Enterprise Typography */
    #popCategoriesRoot,
    .popcat-modal-scope {
        font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        letter-spacing: -0.01em;
        color: #1e293b;
    }

    /* FontAwesome Protection */
    #popCategoriesRoot i,
    #popCategoriesRoot .fa,
    #popCategoriesRoot .fas,
    #popCategoriesRoot .far,
    #popCategoriesRoot .fab,
    #popCategoriesRoot .fa-solid,
    #popCategoriesRoot .fa-regular,
    .popcat-modal-scope i,
    .popcat-modal-scope .fa-solid {
        font-family: "Font Awesome 6 Free", "Font Awesome 5 Free", "FontAwesome" !important;
        font-style: normal;
        font-weight: 900;
        line-height: 1;
        letter-spacing: normal !important;
        display: inline-block;
    }
    #popCategoriesRoot .far,
    .popcat-modal-scope .far {
        font-weight: 400 !important;
    }

    /* Strict 5px Element Radius Across Controls */
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
    #popCategoriesRoot .collapse-toggle-btn {
        border-radius: 5px !important;
    }

    /* Clean Tabular Numbers */
    .bn-digit,
    .font-tabular {
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif !important;
        font-weight: 700 !important;
        font-variant-numeric: tabular-nums;
    }

    /* Sleek Compact Buttons */
    .btn-compact {
        font-size: 11px !important;
        padding: 6px 14px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    .btn-primary {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
    }
    .btn-primary:hover {
        background-color: #1d4ed8 !important;
        border-color: #1d4ed8 !important;
        color: #ffffff !important;
    }

    .btn-outline-secondary {
        background-color: #ffffff !important;
        border-color: #cbd5e1 !important;
        color: #334155 !important;
    }
    .btn-outline-secondary:hover {
        background-color: #f1f5f9 !important;
        border-color: #94a3b8 !important;
        color: #0f172a !important;
    }

    /* Clean White Theme Header Card */
    .header-banner-card {
        background: #ffffff;
        color: #0f172a;
        padding: 14px 18px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 14px;
    }

    /* Panel Section Cards */
    .panel-section-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 14px;
        overflow: hidden;
        border-radius: 6px !important;
    }
    .panel-section-header {
        padding: 12px 16px;
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
    }
    .section-title-text {
        font-size: 12.5px;
        font-weight: 700;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Symmetric Mini KPI Pill */
    .status-kpi-pill {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        padding: 12px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        height: 100%;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.02);
        border-radius: 6px !important;
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
        cursor: pointer;
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

    /* Category Multi-Select Box */
    .cat-multi-select-box {
        border: 1px solid #cbd5e1;
        background: #ffffff;
        overflow: hidden;
        border-radius: 6px !important;
    }
    .cat-select-search {
        border: none;
        border-bottom: 1px solid #e2e8f0;
        border-radius: 0 !important;
        padding: 9px 12px;
        font-size: 12px;
        outline: none;
        box-shadow: none !important;
        background: #ffffff;
        color: #0f172a;
    }
    .cat-select-search:focus {
        background: #f8fafc;
    }
    .cat-checklist-container {
        max-height: 240px;
        overflow-y: auto;
        padding: 6px 8px;
    }
    .cat-checklist-container::-webkit-scrollbar { width: 4px; }
    .cat-checklist-container::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    
    .cat-check-item {
        padding: 8px 10px;
        transition: background-color 0.12s ease;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 2px;
        border: 1px solid transparent;
        border-radius: 4px !important;
    }
    .cat-check-item:hover {
        background-color: #f1f5f9;
        border-color: #e2e8f0;
    }
    
    .selected-chips-box {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        padding: 8px 10px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        min-height: 42px;
        max-height: 95px;
        overflow-y: auto;
    }
    .cat-chip {
        background: #2563eb;
        color: #ffffff;
        padding: 3px 8px;
        font-size: 11px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        max-width: 100%;
        border-radius: 4px !important;
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
        border-radius: 20px !important;
        transition: background-color 0.15s ease, border-color 0.15s ease;
    }
    .form-switch .form-check-input:checked {
        background-color: #2563eb;
        border-color: #2563eb;
    }

    /* Minimal Modal System (Screenshot Reference) */
    .minimal-modal-card {
        background: #ffffff !important;
        border: 1px solid #f1f5f9 !important;
        border-radius: 12px !important;
        box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.14), 0 0 1px 1px rgba(15, 23, 42, 0.04) !important;
        padding: 24px 26px !important;
        text-align: left !important;
    }
    .minimal-modal-title {
        font-size: 16px !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        margin-bottom: 6px !important;
        letter-spacing: -0.02em !important;
    }
    .minimal-modal-subtext {
        font-size: 13px !important;
        color: #64748b !important;
        margin-bottom: 20px !important;
        line-height: 1.45 !important;
    }
    .btn-minimal-action {
        background-color: #2563eb !important;
        border: none !important;
        color: #ffffff !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        padding: 7px 22px !important;
        border-radius: 7px !important;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.28) !important;
        transition: all 0.15s ease !important;
        cursor: pointer !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    .btn-minimal-action:hover {
        background-color: #1d4ed8 !important;
        box-shadow: 0 6px 14px rgba(37, 99, 235, 0.38) !important;
        color: #ffffff !important;
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
        .minimal-modal-card {
            padding: 20px 20px !important;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-2" id="popCategoriesRoot">

    <!-- 1. Header & Quick Controls -->
    <div class="header-banner-card d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 header-top-wrap">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle text-uppercase" style="font-size: 9.5px; font-weight: 700;">
                    <i class="fas fa-shield-heart me-1"></i> Storefront Slider
                </span>
                <span class="badge {{ $sectionStatus ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border' }} text-uppercase" style="font-size: 9.5px; font-weight: 700;">
                    {{ $sectionStatus ? 'Slider Live' : 'Slider Hidden' }}
                </span>
            </div>
            <h5 class="fw-bold mb-0 d-flex align-items-center gap-2 text-dark" style="font-size: 18px;">
                <i class="fas fa-shield-heart text-primary"></i>
                <span>Popular Categories Slider Settings</span>
            </h5>
            <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">
                Control homepage circular category slider visibility, custom items allocation and product link routing.
            </small>
        </div>

        <div class="top-action-btns d-flex gap-2">
            <a href="{{ $categoryIndexRoute }}" class="btn btn-outline-secondary btn-compact shadow-xs bg-white text-dark fw-semibold">
                <i class="fas fa-list me-1 text-primary"></i>
                <span>Categories List</span>
            </a>
            <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold px-3" onclick="document.getElementById('popularCategoriesForm').submit()">
                <i class="fas fa-check-circle me-1"></i>
                <span>Save Settings</span>
            </button>
        </div>
    </div>

    <!-- 2. Status Summary KPI Row (Symmetric 3-Column Grid) -->
    <div class="row g-2 mb-3">
        <div class="col-12 col-md-4">
            <div class="status-kpi-pill">
                <div class="min-w-0">
                    <small class="text-muted d-block fw-semibold text-truncate" style="font-size: 11px;">Slider Visibility</small>
                    <strong class="text-dark d-block" style="font-size: 13.5px;">{{ $sectionStatus ? 'Enabled (Active)' : 'Disabled (Hidden)' }}</strong>
                </div>
                <div class="text-{{ $sectionStatus ? 'success' : 'secondary' }} fs-5">
                    <i class="fas fa-toggle-{{ $sectionStatus ? 'on' : 'off' }}"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="status-kpi-pill">
                <div class="min-w-0">
                    <small class="text-muted d-block fw-semibold text-truncate" style="font-size: 11px;">Selected Showcase</small>
                    <strong class="text-primary d-block bn-digit font-tabular" style="font-size: 13.5px;">
                        {{ $selectedCount > 0 ? $selectedCount . ' Categories' : 'All Categories (Auto)' }}
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
                    <small class="text-muted d-block fw-semibold text-truncate" style="font-size: 11px;">Total Catalog Categories</small>
                    <strong class="text-dark d-block bn-digit font-tabular" style="font-size: 13.5px;">{{ $totalCats }} Available</strong>
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
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle text-uppercase" style="font-size: 9px; font-weight: 700;">Slider Mode</span>
                    <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseVisibilityRules" aria-expanded="true" aria-controls="collapseVisibilityRules" title="Toggle Section">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>
                </div>
            </div>

            <div class="collapse show" id="collapseVisibilityRules">
                <div class="p-3 bg-light bg-opacity-25">
                    <div class="p-3 bg-white border d-flex align-items-center justify-content-between" style="border-radius: 6px;">
                        <div class="pe-2">
                            <label class="form-check-label d-block text-dark fw-bold" for="popCatSecSwitch" style="font-size: 12.5px; cursor: pointer;">
                                Homepage Popular Categories Slider
                            </label>
                            <small class="text-muted d-block" style="font-size: 11.5px; line-height: 1.35;">
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
                    <span class="badge bg-success-subtle text-success border border-success-subtle text-uppercase" style="font-size: 9px; font-weight: 700;">Custom Items</span>
                    <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseCategorySelection" aria-expanded="true" aria-controls="collapseCategorySelection" title="Toggle Section">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>
                </div>
            </div>

            <div class="collapse show" id="collapseCategorySelection">
                <div class="p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label mb-0">Select Featured Categories</label>
                        <small class="text-muted" style="font-size: 11px;">
                            Leave blank to show all active categories automatically.
                        </small>
                    </div>

                    <div class="cat-multi-select-box shadow-xs">
                        <!-- Selected Category Chips Wrapper -->
                        <div class="selected-chips-box" id="selectedCatChipsWrapper">
                            <span class="text-muted small my-auto placeholder-text" style="font-size: 11px;">No categories selected yet.</span>
                        </div>

                        <!-- Real-time Filter Input -->
                        <input type="text" id="catFilterInput" class="form-control cat-select-search" placeholder="Type category title to search instantly...">

                        <!-- Checklist Container with Counter Badges -->
                        <div class="cat-checklist-container" id="catChecklist">
                            @foreach($allCategories as $cat)
                                @php
                                    $isChecked = in_array($cat->id, $selectedCategoryIds ?? []);
                                @endphp
                                <label class="cat-check-item" data-title="{{ strtolower($cat->name) }}">
                                    <div class="d-flex align-items-center gap-2 min-w-0">
                                        <input class="form-check-input mt-0 cat-checkbox flex-shrink-0" type="checkbox" name="category_ids[]" value="{{ $cat->id }}" data-name="{{ $cat->name }}" {{ $isChecked ? 'checked' : '' }}>
                                        <span class="small fw-semibold text-dark text-truncate" style="font-size: 12px;">{{ $cat->name }}</span>
                                    </div>
                                    <span class="badge bg-light text-secondary border small bn-digit font-tabular flex-shrink-0 ms-2" style="font-size: 9.5px;">
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
            <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-4 w-100 w-md-auto" style="min-height: 36px; font-size: 12px;">
                <i class="fas fa-check-circle me-1"></i> Save Changes
            </button>
        </div>
    </form>
</div>

<!-- Modal: Minimal Notice Modal (Exact Match with Reference Screenshot) -->
<div class="modal fade popcat-modal-scope" id="popCatNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 108500;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
        <div class="modal-content minimal-modal-card">
            <h6 class="minimal-modal-title" id="popCatNoticeTitle">Notification</h6>
            <p class="minimal-modal-subtext" id="popCatNoticeMessage" style="margin-bottom: 16px !important;"></p>
            <div class="d-flex align-items-center">
                <button type="button" class="btn-minimal-action" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
// Universal Notice Modal
function showPopCatModalNotice(message, title = 'Notification') {
    const titleEl = document.getElementById('popCatNoticeTitle');
    const msgEl   = document.getElementById('popCatNoticeMessage');

    if (titleEl) titleEl.innerText = title;
    if (msgEl) msgEl.innerText = message;

    const modalEl = document.getElementById('popCatNoticeModal');
    if (modalEl && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

document.addEventListener('DOMContentLoaded', function () {
    @if(session('success'))
        showPopCatModalNotice('{{ session('success') }}', 'Success');
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