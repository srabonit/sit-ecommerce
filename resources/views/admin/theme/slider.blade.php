@extends('layouts.admin')

@section('title', 'Hero Sliders - ' . config('app.name'))

@php
    // Safe media resolver supporting Global Media Library paths
    $resolveSliderImg = function($path, $fallback = 'https://placehold.co/120x60?text=No+Img') {
        if (empty($path) || $path === 'undefined' || $path === 'null') return $fallback;
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path, $fallback);
        }
        if (function_exists('show_image')) {
            return show_image($path, $fallback);
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        return asset(ltrim(str_replace(['public_html/', 'public/'], '', $path), '/'));
    };
@endphp

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #heroSliderMgmtRoot .btn,
    #heroSliderMgmtRoot .btn-sm,
    #heroSliderMgmtRoot button,
    #heroSliderMgmtRoot .form-control,
    #heroSliderMgmtRoot .form-select,
    #heroSliderMgmtRoot .badge,
    #heroSliderMgmtRoot .card,
    #heroSliderMgmtRoot .slider-mgmt-header,
    #heroSliderMgmtRoot .slider-card,
    #heroSliderMgmtRoot .status-kpi-pill,
    #heroSliderMgmtRoot .action-btn-sq,
    #heroSliderMgmtRoot .slide-item-mobile,
    #heroSliderMgmtRoot .collapse-toggle-btn,
    #heroSliderMgmtRoot .proof-thumb-box,
    .slider-modal-scope .modal-content,
    .slider-modal-scope .btn,
    .slider-modal-scope .form-control,
    .slider-modal-scope .form-select,
    .slider-modal-scope .badge,
    .slider-modal-scope .modal-section-card,
    .slider-modal-scope .proof-media-surface,
    .slider-modal-scope .proof-thumb-box,
    .slider-modal-scope .collapse-toggle-btn {
        border-radius: 5px !important;
    }

    #heroSliderMgmtRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #heroSliderMgmtRoot .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
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

    /* Enterprise Surface Header */
    .slider-mgmt-header {
        background: #0f172a;
        color: #ffffff;
        padding: 14px 18px;
        border: 1px solid #1e293b;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
        margin-bottom: 14px;
    }

    /* Enterprise Container Card */
    .slider-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 14px;
        overflow: hidden;
    }
    .slider-card-header {
        padding: 11px 16px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
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

    /* Table Architecture: 100% Fit Screen on PC with Zero Horizontal Scroll */
    .custom-slider-table {
        width: 100%;
        margin-bottom: 0;
    }
    .custom-slider-table thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 8px 6px;
        white-space: nowrap;
    }
    .custom-slider-table tbody td {
        padding: 7px 6px;
        vertical-align: middle;
        font-size: 11px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        white-space: nowrap;
    }
    .custom-slider-table tbody tr:hover {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .custom-slider-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .custom-slider-table th,
        .custom-slider-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 9 Proportional Columns Summing to Exactly 100% */
        .col-sl-thumb   { width: 8% !important; }
        .col-sl-badge   { width: 12% !important; }
        .col-sl-info    { width: 30% !important; }
        .col-sl-content { width: 10% !important; }
        .col-sl-overlay { width: 10% !important; }
        .col-sl-ratio   { width: 10% !important; }
        .col-sl-order   { width: 6% !important; }
        .col-sl-status  { width: 7% !important; }
        .col-sl-actions { width: 7% !important; }
    }

    /* Square Action Buttons */
    .action-btn-sq {
        width: 26px !important;
        height: 26px !important;
        min-width: 26px !important;
        border-radius: 5px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        transition: all 0.15s ease;
        border: 1px solid transparent;
        text-decoration: none;
        padding: 0;
    }
    .action-btn-sq.edit { background: #eff6ff; color: #2563eb; border-color: #bfdbfe; }
    .action-btn-sq.edit:hover { background: #2563eb; color: #ffffff; }
    .action-btn-sq.delete { background: #fef2f2; color: #dc2626; border-color: #fecaca; }
    .action-btn-sq.delete:hover { background: #dc2626; color: #ffffff; }

    /* Global Media Chooser Component Styles */
    .proof-media-surface {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        padding: 10px;
        transition: all 0.2s ease;
    }
    .proof-media-surface:hover {
        border-color: #2563eb;
        background: #f1f5f9;
    }
    .proof-thumb-box {
        width: 44px;
        height: 28px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
        position: relative;
    }
    .proof-thumb-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .proof-thumb-hover-overlay {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.15s ease;
    }
    .proof-thumb-box:hover .proof-thumb-hover-overlay {
        opacity: 1;
    }

    /* Modal Form Cards */
    .modal-section-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 12px;
        margin-bottom: 10px;
    }

    /* Form Controls */
    .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #heroSliderMgmtRoot .form-control,
    #heroSliderMgmtRoot .form-select,
    .slider-modal-scope .form-control,
    .slider-modal-scope .form-select {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #heroSliderMgmtRoot .form-control:focus,
    #heroSliderMgmtRoot .form-select:focus,
    .slider-modal-scope .form-control:focus,
    .slider-modal-scope .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }
    .slider-modal-scope .form-control-color {
        height: 32px !important;
        padding: 3px 6px !important;
        cursor: pointer;
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
    .form-check-label {
        font-size: 11.5px;
        font-weight: 600;
        color: #1e293b;
        cursor: pointer;
        user-select: none;
    }

    /* Mobile Responsive (< 768px) */
    @media (max-width: 767.98px) {
        .slider-mgmt-header { padding: 12px 14px; }
        .slider-card-header { padding: 10px 12px; }
        .slide-item-mobile {
            border: 1px solid #e2e8f0;
            padding: 10px 12px;
            margin-bottom: 8px;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .slide-item-mobile-top { display: flex; align-items: center; gap: 10px; }
        .slide-item-mobile-img {
            width: 70px;
            height: 44px;
            border: 1px solid #cbd5e1;
            overflow: hidden;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .slide-item-mobile-img img { width: 100%; height: 100%; object-fit: cover; }
        .slide-item-mobile-content { flex: 1; min-width: 0; }
        .slide-title-mobile { font-size: 12px; font-weight: 700; color: #0f172a; margin-bottom: 2px; }
        .slide-subtitle-mobile { font-size: 10.5px; color: #64748b; margin-bottom: 0; }
        .slide-item-mobile-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            padding-top: 8px;
            border-top: 1px dashed #e2e8f0;
        }
        .slide-item-mobile-actions button {
            flex: 1;
            padding: 6px 10px;
            font-size: 11.5px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="heroSliderMgmtRoot">

    <!-- 1. Top Header Banner -->
   <div class="slider-mgmt-header d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-3 bg-white p-3 border rounded-3 shadow-xs">
    <div class="min-w-0">
        <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2 text-nowrap" style="font-size: 16px;">
            <i class="fas fa-film text-primary"></i>
            <span>Hero Sliders Settings</span>
        </h5>
        <small class="text-secondary d-block mt-0 text-nowrap" style="font-size: 11.5px; line-height: 1.35;">
            Manage Slider banners, text, mobile views &amp; buttons.
        </small>
    </div>

    <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold px-3 d-inline-flex align-items-center justify-content-center gap-1.5 text-nowrap flex-shrink-0" style="height: 34px; font-size: 11.5px;" data-bs-toggle="modal" data-bs-target="#addSliderModal" onclick="prepareAddSlideModal()">
        <i class="fas fa-plus me-1"></i>
        <span>Add New Slide</span>
    </button>
</div>

    <!-- 2. Status Summary KPI Row (Symmetric 3-Column Grid) -->
    @php
        $activeSlidesCount = $sliders->where('status', 1)->count();
        $disabledSlidesCount = $sliders->where('status', 0)->count();
    @endphp

    <div class="row g-2 mb-3">
        <div class="col-12 col-md-4">
            <div class="status-kpi-pill">
                <div class="min-w-0">
                    <small class="text-muted d-block fw-semibold text-truncate" style="font-size: 10.5px;">Active Slides</small>
                    <strong class="text-success d-block font-monospace bn-digit" style="font-size: 13px;">{{ $activeSlidesCount }} Slides Live</strong>
                </div>
                <div class="text-success fs-5"><i class="fas fa-circle-check"></i></div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="status-kpi-pill">
                <div class="min-w-0">
                    <small class="text-muted d-block fw-semibold text-truncate" style="font-size: 10.5px;">Disabled Slides</small>
                    <strong class="text-secondary d-block font-monospace bn-digit" style="font-size: 13px;">{{ $disabledSlidesCount }} Slides Off</strong>
                </div>
                <div class="text-secondary fs-5"><i class="fas fa-circle-pause"></i></div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="status-kpi-pill">
                <div class="min-w-0">
                    <small class="text-muted d-block fw-semibold text-truncate" style="font-size: 10.5px;">Total Banners</small>
                    <strong class="text-dark d-block font-monospace bn-digit" style="font-size: 13px;">{{ $sliders->count() }} Configured</strong>
                </div>
                <div class="text-primary fs-5"><i class="fas fa-images"></i></div>
            </div>
        </div>
    </div>

    <!-- 3. Sliders Table Card (COLLAPSIBLE) -->
    <div class="slider-card">
        <div class="slider-card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-list text-primary"></i>
                <h6 class="fw-bold text-dark mb-0" style="font-size: 13px;">Active Hero Sliders</h6>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark border font-monospace bn-digit px-2 py-0.5" style="font-size: 9.5px;">
                    {{ $sliders->count() }} {{ Str::plural('Slide', $sliders->count()) }}
                </span>
                <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseSliderTable" aria-expanded="true" aria-controls="collapseSliderTable" title="Toggle Section">
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </button>
            </div>
        </div>

        <div class="collapse show" id="collapseSliderTable">
            <!-- Desktop Table View (100% Fit Screen with Zero Horizontal Scroll) -->
            <div class="table-responsive pc-fit-container d-none d-md-block">
                <table class="table custom-slider-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3 col-sl-thumb text-center">Banner</th>
                            <th class="col-sl-badge">Badge</th>
                            <th class="col-sl-info">Title &amp; Subtitle</th>
                            <th class="col-sl-content text-center">Content</th>
                            <th class="col-sl-overlay text-center">Overlay</th>
                            <th class="col-sl-ratio text-center">Mobile View</th>
                            <th class="col-sl-order text-center">Order</th>
                            <th class="col-sl-status text-center">Status</th>
                            <th class="text-end pe-3 col-sl-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sliders as $slide)
                            @php
                                $slideImg = $resolveSliderImg($slide->image);
                            @endphp
                            <tr>
                                <!-- Banner Preview with Lightbox Zoom -->
                                <td class="ps-3 col-sl-thumb text-center">
                                    <div class="proof-thumb-box shadow-xs" onclick="zoomThemeMedia('{{ $slideImg }}', '{{ addslashes($slide->title ?? 'Banner') }}')" title="Click to Zoom Banner">
                                        <img src="{{ $slideImg }}" alt="Slider Banner" onerror="this.src='https://placehold.co/120x60?text=No+Img';">
                                        <div class="proof-thumb-hover-overlay">
                                            <i class="fas fa-search-plus text-white" style="font-size: 8px;"></i>
                                        </div>
                                    </div>
                                </td>

                                <!-- Badge -->
                                <td class="col-sl-badge">
                                    @if($slide->badge_text)
                                        <span class="badge px-1.5 py-0 text-truncate d-inline-block font-monospace" style="background: {{ $slide->badge_bg ?: '#2563eb' }}; color: {{ $slide->badge_color ?: '#ffffff' }}; font-size: 9px; max-width: 100px;">
                                            {{ $slide->badge_text }}
                                        </span>
                                    @else
                                        <span class="text-muted font-monospace" style="font-size: 10px;">—</span>
                                    @endif
                                </td>

                                <!-- Title & Subtitle -->
                                <td class="col-sl-info">
                                    <div class="min-w-0">
                                        <strong class="text-dark d-block text-truncate" style="font-size: 11.5px; line-height: 1.3;" title="{{ $slide->title ?: 'Pure Image Banner' }}">
                                            {{ $slide->title ?: 'Pure Image Banner' }}
                                        </strong>
                                        <small class="text-muted d-block text-truncate" style="font-size: 10.5px;" title="{{ $slide->subtitle }}">
                                            {{ $slide->subtitle ?: 'No subtitle provided' }}
                                        </small>
                                    </div>
                                </td>

                                <!-- Content Visibility -->
                                <td class="col-sl-content text-center">
                                    @if(!isset($slide->show_content) || $slide->show_content)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-1.5 py-0" style="font-size: 9px;">Visible</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border font-monospace px-1.5 py-0" style="font-size: 9px;">Hidden</span>
                                    @endif
                                </td>

                                <!-- Overlay -->
                                <td class="col-sl-overlay text-center">
                                    @if(!isset($slide->enable_overlay) || $slide->enable_overlay)
                                        <span class="badge bg-dark-subtle text-dark border px-1.5 py-0 d-inline-flex align-items-center gap-1 bn-digit font-monospace" style="font-size: 9px;">
                                            <span style="display:inline-block; width:6px; height:6px; border-radius:1px; background: {{ $slide->overlay_color ?? '#0f172a' }};"></span>
                                            {{ round(($slide->overlay_opacity ?? 0.45) * 100) }}%
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border font-monospace px-1.5 py-0" style="font-size: 9px;">None</span>
                                    @endif
                                </td>

                                <!-- Mobile Aspect Ratio -->
                                <td class="col-sl-ratio text-center">
                                    @php
                                        $ratioBadgeText = match($slide->mobile_view_ratio ?? 'landscape') {
                                            'square'   => 'Square',
                                            'portrait' => 'Portrait',
                                            default    => 'Landscape'
                                        };
                                    @endphp
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle font-monospace px-1.5 py-0" style="font-size: 9px;">
                                        {{ $ratioBadgeText }}
                                    </span>
                                </td>

                                <!-- Order -->
                                <td class="col-sl-order text-center font-monospace bn-digit text-muted" style="font-size: 11px;">
                                    {{ $slide->serial }}
                                </td>

                                <!-- Status -->
                                <td class="col-sl-status text-center">
                                    @if($slide->status)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-2 py-0.5" style="font-size: 9px;">Active</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border font-monospace px-2 py-0.5" style="font-size: 9px;">Off</span>
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td class="text-end pe-3 col-sl-actions">
                                    <div class="d-inline-flex gap-1">
                                        <button type="button" class="action-btn-sq edit shadow-xs" data-bs-toggle="modal" data-bs-target="#editSliderModal{{ $slide->id }}" title="Edit Slide">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        <button type="button" class="action-btn-sq delete shadow-xs" onclick="openDeleteSliderModal('{{ route('admin.theme.setting.slider.destroy', $slide->id) }}', '{{ addslashes($slide->title ?? 'Slide') }}')" title="Delete Slide">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted small">
                                    <i class="fas fa-film fa-2x opacity-25 mb-2 d-block"></i>
                                    <span class="fw-semibold">No hero sliders added yet. Click "Add New Slide" to start.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile View (100/100 Touch-Comfort) -->
            <div class="d-block d-md-none p-2">
                @forelse($sliders as $slide)
                    @php
                        $slideImgMobile = $resolveSliderImg($slide->image);
                    @endphp
                    <div class="slide-item-mobile shadow-xs">
                        <div class="slide-item-mobile-top">
                            <div class="proof-thumb-box shadow-xs" onclick="zoomThemeMedia('{{ $slideImgMobile }}', '{{ addslashes($slide->title ?? 'Banner') }}')" title="Zoom">
                                <img src="{{ $slideImgMobile }}" alt="Banner" onerror="this.src='https://placehold.co/120x60?text=No+Img';">
                            </div>
                            <div class="slide-item-mobile-content">
                                <div class="d-flex align-items-center gap-1 mb-1">
                                    @if($slide->badge_text)
                                        <span class="badge px-1.5 py-0 font-monospace" style="background: {{ $slide->badge_bg ?: '#2563eb' }}; color: {{ $slide->badge_color ?: '#ffffff' }}; font-size: 8.5px;">
                                            {{ $slide->badge_text }}
                                        </span>
                                    @endif
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle font-monospace px-1 py-0" style="font-size: 8.5px;">
                                        {{ $slide->mobile_view_ratio === 'square' ? 'Square' : 'Banner' }}
                                    </span>
                                    @if($slide->status)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-1 py-0" style="font-size: 8.5px;">Active</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border font-monospace px-1 py-0" style="font-size: 8.5px;">Off</span>
                                    @endif
                                </div>
                                <div class="slide-title-mobile text-truncate">{{ $slide->title ?: 'Pure Image Banner' }}</div>
                                <div class="slide-subtitle-mobile text-truncate">{{ $slide->subtitle ?: 'No subtitle provided' }}</div>
                            </div>
                        </div>
                        
                        <div class="slide-item-mobile-actions">
                            <button type="button" class="btn btn-light border btn-compact" data-bs-toggle="modal" data-bs-target="#editSliderModal{{ $slide->id }}">
                                <i class="fas fa-pen text-primary"></i>
                                <span>Edit</span>
                            </button>
                            <button type="button" class="btn btn-light border btn-compact text-danger" onclick="openDeleteSliderModal('{{ route('admin.theme.setting.slider.destroy', $slide->id) }}', '{{ addslashes($slide->title ?? 'Slide') }}')">
                                <i class="fas fa-trash-alt"></i>
                                <span>Delete</span>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-muted small">
                        <i class="fas fa-film fa-2x opacity-25 mb-2 d-block"></i>
                        No hero sliders found.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL: ADD SLIDER (WITH GLOBAL MEDIA PICKER) ================= -->
<div class="modal fade slider-modal-scope" id="addSliderModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form action="{{ route('admin.theme.setting.slider.store') }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow-lg overflow-hidden">
            @csrf
            <div class="modal-header bg-dark text-white py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold d-flex align-items-center gap-1 mb-0" style="font-size: 13.5px;">
                    <i class="fas fa-plus-circle text-primary me-1"></i>
                    <span>Add New Hero Slide</span>
                </h6>
                <button type="button" class="btn-close btn-close-white btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-3">
                
                <!-- Section 1: Behavior & Overlay Rules (COLLAPSIBLE) -->
                <div class="modal-section-card">
                    <div class="d-flex justify-content-between align-items-center pb-2 border-bottom mb-2">
                        <div class="d-flex align-items-center gap-1.5">
                            <i class="fas fa-sliders text-primary"></i>
                            <strong class="text-dark small" style="font-size: 11.5px;">Behavior &amp; Overlay Rules</strong>
                        </div>
                        <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseAddRules" aria-expanded="true" aria-controls="collapseAddRules">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>

                    <div class="collapse show" id="collapseAddRules">
                        <div class="row g-2 align-items-center">
                            <!-- Show Content Switch -->
                            <div class="col-12 col-md-6">
                                <div class="p-2 border bg-white d-flex align-items-center justify-content-between" style="border-radius: 5px;">
                                    <div>
                                        <label class="form-check-label d-block fw-bold" for="createShowContentSwitch">Slide Text Content</label>
                                        <small class="text-muted d-block" style="font-size: 10px;">Turn OFF if your image banner already has text.</small>
                                    </div>
                                    <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                        <input class="form-check-input ms-0" type="checkbox" name="show_content" value="1" id="createShowContentSwitch" checked onchange="toggleContentInputs('create', this.checked)">
                                    </div>
                                </div>
                            </div>

                            <!-- Enable Overlay Switch -->
                            <div class="col-12 col-md-6">
                                <div class="p-2 border bg-white d-flex align-items-center justify-content-between" style="border-radius: 5px;">
                                    <div>
                                        <label class="form-check-label d-block fw-bold" for="createOverlaySwitch">Dark Color Overlay</label>
                                        <small class="text-muted d-block" style="font-size: 10px;">Increases contrast for overlaid text readability.</small>
                                    </div>
                                    <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                        <input class="form-check-input ms-0" type="checkbox" name="enable_overlay" value="1" id="createOverlaySwitch" checked onchange="toggleOverlayInputs('create', this.checked)">
                                    </div>
                                </div>
                            </div>

                            <!-- Overlay Palette & Opacity -->
                            <div class="col-6 col-md-4" id="createOverlayColorWrap">
                                <label class="form-label">Overlay Color</label>
                                <input type="color" name="overlay_color" class="form-control form-control-color w-100" value="#0f172a">
                            </div>
                            <div class="col-6 col-md-4" id="createOverlayOpacityWrap">
                                <label class="form-label">Overlay Opacity</label>
                                <select name="overlay_opacity" class="form-select font-monospace bn-digit">
                                    <option value="0.2">Light (20%)</option>
                                    <option value="0.35">Soft (35%)</option>
                                    <option value="0.45" selected>Standard (45%)</option>
                                    <option value="0.65">Deep (65%)</option>
                                    <option value="0.85">Heavy (85%)</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Mobile Aspect Ratio</label>
                                <select name="mobile_view_ratio" class="form-select font-monospace">
                                    <option value="landscape" selected>Landscape Banner (16:9)</option>
                                    <option value="square">Compact Square (1:1)</option>
                                    <option value="portrait">Tall Portrait (Poster)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Banner Image (GLOBAL MEDIA CHOOSER) -->
                <div class="modal-section-card">
                    <div class="d-flex justify-content-between align-items-center pb-2 border-bottom mb-2">
                        <div class="d-flex align-items-center gap-1.5">
                            <i class="fas fa-paperclip text-primary"></i>
                            <strong class="text-dark small" style="font-size: 11.5px;">Background Banner Image *</strong>
                        </div>
                        <span class="badge bg-light text-muted border font-monospace" style="font-size: 8.5px;">GLOBAL MEDIA</span>
                    </div>

                    <div class="proof-media-surface" style="border-radius: 5px;">
                        <input type="hidden" name="image_media" id="createSliderMediaInput" value="">

                        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                            <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold" onclick="chooseSliderCreateMedia()">
                                <i class="fas fa-photo-film me-1"></i> Choose from Media Library
                            </button>
                            <span class="small text-muted font-monospace" style="font-size: 10.5px;">or upload local file:</span>
                        </div>

                        <input type="file" name="image" id="createSliderFileInput" class="form-control mb-2" accept="image/*" onchange="handleSliderImagePreview(event, 'createSliderImgWrap', 'createSliderClearBtn', 'createSliderPlaceholder')">
                        
                        <div class="bg-white border p-2 d-flex align-items-center gap-2" id="createSliderImgWrap" style="border-radius: 5px;">
                            <div class="proof-thumb-box shadow-xs" id="createSliderThumbBox" style="display: none;" onclick="zoomThemeMedia(document.getElementById('createSlidePreviewImgTag').src, 'Banner Preview')" title="Click to Zoom">
                                <img id="createSlidePreviewImgTag" src="" alt="Banner">
                                <div class="proof-thumb-hover-overlay">
                                    <i class="fas fa-search-plus text-white" style="font-size: 8px;"></i>
                                </div>
                            </div>
                            <span class="text-muted small my-auto" id="createSliderPlaceholder" style="font-size: 11px;">
                                <i class="fas fa-image me-1 text-primary"></i> No banner image selected yet
                            </span>
                            <button type="button" class="btn btn-outline-danger btn-compact py-0 px-2 ms-auto d-none" id="createSliderClearBtn" onclick="clearSelectedSliderCreateImg()" style="height: 22px; font-size: 9.5px !important;">
                                <i class="fas fa-times me-1"></i> Clear
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Slide Text & Button Details (COLLAPSIBLE) -->
                <div class="modal-section-card" id="createContentFieldsContainer">
                    <div class="d-flex justify-content-between align-items-center pb-2 border-bottom mb-2">
                        <div class="d-flex align-items-center gap-1.5">
                            <i class="fas fa-font text-primary"></i>
                            <strong class="text-dark small" style="font-size: 11.5px;">Slide Typography &amp; Action Link</strong>
                        </div>
                        <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseAddText" aria-expanded="true" aria-controls="collapseAddText">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>

                    <div class="collapse show" id="collapseAddText">
                        <div class="row g-2">
                            <!-- Badge -->
                            <div class="col-12 col-md-4">
                                <label class="form-label">Badge Text</label>
                                <input type="text" name="badge_text" class="form-control" placeholder="Exclusive Offer">
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label">Badge Background</label>
                                <input type="color" name="badge_bg" class="form-control form-control-color w-100" value="#2563eb">
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label">Badge Text Color</label>
                                <input type="color" name="badge_color" class="form-control form-control-color w-100" value="#ffffff">
                            </div>

                            <!-- Title -->
                            <div class="col-12 col-md-8">
                                <label class="form-label">Main Title</label>
                                <input type="text" name="title" class="form-control" placeholder="Discover Latest Trends">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Title Color</label>
                                <input type="color" name="title_color" class="form-control form-control-color w-100" value="#ffffff">
                            </div>

                            <!-- Subtitle -->
                            <div class="col-12">
                                <label class="form-label">Subtitle Description</label>
                                <textarea name="subtitle" class="form-control" rows="2" placeholder="Experience authentic lifestyle products..."></textarea>
                            </div>

                            <!-- Button -->
                            <div class="col-12 col-md-6">
                                <label class="form-label">Button Label</label>
                                <input type="text" name="btn_text" class="form-control" value="Shop Now">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">Button Target Link</label>
                                <input type="text" name="btn_url" class="form-control font-monospace" value="/shop">
                            </div>
                            <div class="col-6 col-md-6">
                                <label class="form-label">Button Background</label>
                                <input type="color" name="btn_bg" class="form-control form-control-color w-100" value="#2563eb">
                            </div>
                            <div class="col-6 col-md-6">
                                <label class="form-label">Button Text Color</label>
                                <input type="color" name="btn_color" class="form-control form-control-color w-100" value="#ffffff">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Priority & Publication (COLLAPSIBLE) -->
                <div class="modal-section-card mb-0">
                    <div class="d-flex justify-content-between align-items-center pb-2 border-bottom mb-2">
                        <div class="d-flex align-items-center gap-1.5">
                            <i class="fas fa-layer-group text-primary"></i>
                            <strong class="text-dark small" style="font-size: 11.5px;">Priority &amp; Publication Status</strong>
                        </div>
                        <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseAddPub" aria-expanded="true" aria-controls="collapseAddPub">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>

                    <div class="collapse show" id="collapseAddPub">
                        <div class="row g-2 align-items-center">
                            <div class="col-12 col-md-6">
                                <label class="form-label">Display Order Index</label>
                                <input type="number" name="serial" class="form-control font-monospace bn-digit" value="1">
                            </div>
                            <div class="col-12 col-md-6 pt-md-3">
                                <div class="p-2 border bg-white d-flex align-items-center justify-content-between" style="border-radius: 5px;">
                                    <label class="form-check-label fw-bold small m-0" for="createStatusSwitch" style="font-size: 11px;">Active Slide on Storefront</label>
                                    <div class="form-check form-switch m-0 ps-0">
                                        <input class="form-check-input ms-0" type="checkbox" name="status" value="1" id="createStatusSwitch" checked>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="modal-footer py-2 px-3 bg-light d-flex justify-content-between">
                <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-3">Create Slide</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODALS: EDIT SLIDERS (WITH GLOBAL MEDIA PICKER) ================= -->
@foreach($sliders as $slide)
@php
    $editSlideImg = $resolveSliderImg($slide->image);
@endphp
<div class="modal fade slider-modal-scope" id="editSliderModal{{ $slide->id }}" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form action="{{ route('admin.theme.setting.slider.update', $slide->id) }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow-lg overflow-hidden">
            @csrf
            <input type="hidden" name="remove_image" id="editSliderRemoveImgFlag{{ $slide->id }}" value="0">
            <input type="hidden" name="image_media" id="editSliderMediaInput{{ $slide->id }}" value="">

            <div class="modal-header bg-dark text-white py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold d-flex align-items-center gap-1 mb-0" style="font-size: 13.5px;">
                    <i class="fas fa-pen text-primary"></i>
                    <span>Edit Slide: {{ $slide->title ?: 'Slide #' . $slide->id }}</span>
                </h6>
                <button type="button" class="btn-close btn-close-white btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-3">
                
                <!-- Section 1: Behavior & Overlay Rules (COLLAPSIBLE) -->
                <div class="modal-section-card">
                    <div class="d-flex justify-content-between align-items-center pb-2 border-bottom mb-2">
                        <div class="d-flex align-items-center gap-1.5">
                            <i class="fas fa-sliders text-primary"></i>
                            <strong class="text-dark small" style="font-size: 11.5px;">Behavior &amp; Overlay Rules</strong>
                        </div>
                        <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseEditRules{{ $slide->id }}" aria-expanded="true" aria-controls="collapseEditRules{{ $slide->id }}">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>

                    <div class="collapse show" id="collapseEditRules{{ $slide->id }}">
                        <div class="row g-2 align-items-center">
                            <!-- Show Content Switch -->
                            <div class="col-12 col-md-6">
                                <div class="p-2 border bg-white d-flex align-items-center justify-content-between" style="border-radius: 5px;">
                                    <div>
                                        <label class="form-check-label d-block fw-bold" for="editShowContentSwitch{{ $slide->id }}">Slide Text Content</label>
                                        <small class="text-muted d-block" style="font-size: 10px;">Turn OFF if your image banner already has text.</small>
                                    </div>
                                    <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                        <input class="form-check-input ms-0" type="checkbox" name="show_content" value="1" id="editShowContentSwitch{{ $slide->id }}" {{ (!isset($slide->show_content) || $slide->show_content) ? 'checked' : '' }} onchange="toggleContentInputs('edit{{ $slide->id }}', this.checked)">
                                    </div>
                                </div>
                            </div>

                            <!-- Enable Overlay Switch -->
                            <div class="col-12 col-md-6">
                                <div class="p-2 border bg-white d-flex align-items-center justify-content-between" style="border-radius: 5px;">
                                    <div>
                                        <label class="form-check-label d-block fw-bold" for="editOverlaySwitch{{ $slide->id }}">Dark Color Overlay</label>
                                        <small class="text-muted d-block" style="font-size: 10px;">Increases contrast for overlaid text readability.</small>
                                    </div>
                                    <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                        <input class="form-check-input ms-0" type="checkbox" name="enable_overlay" value="1" id="editOverlaySwitch{{ $slide->id }}" {{ (!isset($slide->enable_overlay) || $slide->enable_overlay) ? 'checked' : '' }} onchange="toggleOverlayInputs('edit{{ $slide->id }}', this.checked)">
                                    </div>
                                </div>
                            </div>

                            <!-- Overlay Palette & Opacity -->
                            <div class="col-6 col-md-4" id="createOverlayColorWrapedit{{ $slide->id }}" style="{{ (isset($slide->enable_overlay) && !$slide->enable_overlay) ? 'display:none;' : '' }}">
                                <label class="form-label">Overlay Color</label>
                                <input type="color" name="overlay_color" class="form-control form-control-color w-100" value="{{ $slide->overlay_color ?? '#0f172a' }}">
                            </div>
                            <div class="col-6 col-md-4" id="createOverlayOpacityWrapedit{{ $slide->id }}" style="{{ (isset($slide->enable_overlay) && !$slide->enable_overlay) ? 'display:none;' : '' }}">
                                <label class="form-label">Overlay Opacity</label>
                                <select name="overlay_opacity" class="form-select font-monospace bn-digit">
                                    <option value="0.2" {{ ($slide->overlay_opacity ?? '') == '0.2' ? 'selected' : '' }}>Light (20%)</option>
                                    <option value="0.35" {{ ($slide->overlay_opacity ?? '') == '0.35' ? 'selected' : '' }}>Soft (35%)</option>
                                    <option value="0.45" {{ (!isset($slide->overlay_opacity) || $slide->overlay_opacity == '0.45') ? 'selected' : '' }}>Standard (45%)</option>
                                    <option value="0.65" {{ ($slide->overlay_opacity ?? '') == '0.65' ? 'selected' : '' }}>Deep (65%)</option>
                                    <option value="0.85" {{ ($slide->overlay_opacity ?? '') == '0.85' ? 'selected' : '' }}>Heavy (85%)</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Mobile Aspect Ratio</label>
                                <select name="mobile_view_ratio" class="form-select font-monospace">
                                    <option value="landscape" {{ ($slide->mobile_view_ratio ?? 'landscape') == 'landscape' ? 'selected' : '' }}>Landscape Banner (16:9)</option>
                                    <option value="square" {{ ($slide->mobile_view_ratio ?? '') == 'square' ? 'selected' : '' }}>Compact Square (1:1)</option>
                                    <option value="portrait" {{ ($slide->mobile_view_ratio ?? '') == 'portrait' ? 'selected' : '' }}>Tall Portrait (Poster)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Banner Image (GLOBAL MEDIA CHOOSER) -->
                <div class="modal-section-card">
                    <div class="d-flex justify-content-between align-items-center pb-2 border-bottom mb-2">
                        <div class="d-flex align-items-center gap-1.5">
                            <i class="fas fa-paperclip text-primary"></i>
                            <strong class="text-dark small" style="font-size: 11.5px;">Update Background Banner</strong>
                        </div>
                        <span class="badge bg-light text-muted border font-monospace" style="font-size: 8.5px;">GLOBAL MEDIA</span>
                    </div>

                    <div class="proof-media-surface" style="border-radius: 5px;">
                        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                            <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold" onclick="chooseSliderEditMedia('{{ $slide->id }}')">
                                <i class="fas fa-photo-film me-1"></i> Choose from Media Library
                            </button>
                            <span class="small text-muted font-monospace" style="font-size: 10.5px;">or upload local file:</span>
                        </div>

                        <input type="file" name="image" id="editSliderFileInput{{ $slide->id }}" class="form-control mb-2" accept="image/*" onchange="handleSliderEditFilePreview(event, '{{ $slide->id }}')">
                        
                        <div class="bg-white border p-2 d-flex align-items-center gap-2" id="editSliderImgWrap{{ $slide->id }}" style="border-radius: 5px;">
                            <div class="proof-thumb-box shadow-xs" id="editSliderThumbBox{{ $slide->id }}" onclick="zoomThemeMedia(document.getElementById('editSliderImgTag{{ $slide->id }}').src, '{{ addslashes($slide->title ?? 'Banner') }}')" title="Click to Zoom">
                                <img id="editSliderImgTag{{ $slide->id }}" src="{{ $editSlideImg }}" alt="Banner" onerror="this.src='https://placehold.co/120x60?text=No+Img';">
                                <div class="proof-thumb-hover-overlay">
                                    <i class="fas fa-search-plus text-white" style="font-size: 8px;"></i>
                                </div>
                            </div>
                            <div class="min-w-0 flex-grow-1" id="editSliderMetaText{{ $slide->id }}">
                                <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace text-uppercase" style="font-size: 8.5px;">
                                    <i class="fas fa-check-circle me-1"></i>Current Banner
                                </span>
                                <small class="text-muted d-block text-truncate font-monospace" style="font-size: 9.5px;">{{ $slide->image ?: 'Active Banner' }}</small>
                            </div>
                            <span class="text-muted small my-auto d-none" id="editSliderPlaceholder{{ $slide->id }}" style="font-size: 11px;">
                                No banner image selected
                            </span>
                            <button type="button" class="btn btn-outline-danger btn-compact py-0 px-2 ms-auto" id="editSliderClearBtn{{ $slide->id }}" onclick="clearSliderEditImage('{{ $slide->id }}')" style="height: 22px; font-size: 9.5px !important;" title="Clear Image">
                                <i class="fas fa-times me-1"></i> Clear
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Slide Text & Button Details (COLLAPSIBLE) -->
                <div class="modal-section-card" id="editContentFieldsContaineredit{{ $slide->id }}" style="{{ (isset($slide->show_content) && !$slide->show_content) ? 'display:none !important;' : '' }}">
                    <div class="d-flex justify-content-between align-items-center pb-2 border-bottom mb-2">
                        <div class="d-flex align-items-center gap-1.5">
                            <i class="fas fa-font text-primary"></i>
                            <strong class="text-dark small" style="font-size: 11.5px;">Slide Typography &amp; Action Link</strong>
                        </div>
                        <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseEditText{{ $slide->id }}" aria-expanded="true" aria-controls="collapseEditText{{ $slide->id }}">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>

                    <div class="collapse show" id="collapseEditText{{ $slide->id }}">
                        <div class="row g-2">
                            <!-- Badge -->
                            <div class="col-12 col-md-4">
                                <label class="form-label">Badge Text</label>
                                <input type="text" name="badge_text" class="form-control" value="{{ $slide->badge_text }}">
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label">Badge Background</label>
                                <input type="color" name="badge_bg" class="form-control form-control-color w-100" value="{{ $slide->badge_bg ?? '#2563eb' }}">
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label">Badge Text Color</label>
                                <input type="color" name="badge_color" class="form-control form-control-color w-100" value="{{ $slide->badge_color ?? '#ffffff' }}">
                            </div>

                            <!-- Title -->
                            <div class="col-12 col-md-8">
                                <label class="form-label">Main Title</label>
                                <input type="text" name="title" class="form-control" value="{{ $slide->title }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Title Color</label>
                                <input type="color" name="title_color" class="form-control form-control-color w-100" value="{{ $slide->title_color ?? '#ffffff' }}">
                            </div>

                            <!-- Subtitle -->
                            <div class="col-12">
                                <label class="form-label">Subtitle Description</label>
                                <textarea name="subtitle" class="form-control" rows="2">{{ $slide->subtitle }}</textarea>
                            </div>

                            <!-- Button -->
                            <div class="col-12 col-md-6">
                                <label class="form-label">Button Label</label>
                                <input type="text" name="btn_text" class="form-control" value="{{ $slide->btn_text }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">Button Target Link</label>
                                <input type="text" name="btn_url" class="form-control font-monospace" value="{{ $slide->btn_url }}">
                            </div>
                            <div class="col-6 col-md-6">
                                <label class="form-label">Button Background</label>
                                <input type="color" name="btn_bg" class="form-control form-control-color w-100" value="{{ $slide->btn_bg ?? '#2563eb' }}">
                            </div>
                            <div class="col-6 col-md-6">
                                <label class="form-label">Button Text Color</label>
                                <input type="color" name="btn_color" class="form-control form-control-color w-100" value="{{ $slide->btn_color ?? '#ffffff' }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Priority & Publication (COLLAPSIBLE) -->
                <div class="modal-section-card mb-0">
                    <div class="d-flex justify-content-between align-items-center pb-2 border-bottom mb-2">
                        <div class="d-flex align-items-center gap-1.5">
                            <i class="fas fa-layer-group text-primary"></i>
                            <strong class="text-dark small" style="font-size: 11.5px;">Priority &amp; Publication Status</strong>
                        </div>
                        <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseEditPub{{ $slide->id }}" aria-expanded="true" aria-controls="collapseEditPub{{ $slide->id }}">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>

                    <div class="collapse show" id="collapseEditPub{{ $slide->id }}">
                        <div class="row g-2 align-items-center">
                            <div class="col-12 col-md-6">
                                <label class="form-label">Display Order Index</label>
                                <input type="number" name="serial" class="form-control font-monospace bn-digit" value="{{ $slide->serial }}">
                            </div>
                            <div class="col-12 col-md-6 pt-md-3">
                                <div class="p-2 border bg-white d-flex align-items-center justify-content-between" style="border-radius: 5px;">
                                    <label class="form-check-label fw-bold small m-0" for="editStatusSwitch{{ $slide->id }}" style="font-size: 11px;">Active Slide on Storefront</label>
                                    <div class="form-check form-switch m-0 ps-0">
                                        <input class="form-check-input ms-0" type="checkbox" name="status" value="1" id="editStatusSwitch{{ $slide->id }}" {{ $slide->status ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="modal-footer py-2 px-3 bg-light d-flex justify-content-between">
                <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-3">Update Slide</button>
            </div>
        </form>
    </div>
</div>
@endforeach

<!-- ================= MODAL: DELETE CONFIRMATION ================= -->
<div class="modal fade slider-modal-scope" id="deleteSliderConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="mb-2 text-danger">
                    <i class="fas fa-trash-can fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 14.5px;">Delete Hero Slide?</h6>
                <p class="text-muted small mb-3" style="font-size: 12px; line-height: 1.45;">
                    Are you sure you want to remove <strong id="deleteSliderTitle" class="text-dark">this slide</strong>?
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <form id="confirmedDeleteSliderForm" action="" method="POST" class="d-inline m-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-compact shadow-xs fw-bold">Yes, Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL: LIGHTBOX ZOOM PREVIEW ================= -->
<div class="modal fade slider-modal-scope" id="sliderMediaZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 108000;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg text-center p-2">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" style="font-size: 12.5px;" id="sliderMediaZoomTitle">
                    <i class="fas fa-image text-primary me-1"></i> Banner Preview
                </span>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2">
                <img id="sliderMediaZoomImgTag" src="" alt="Banner Preview" class="img-fluid border" style="max-height: 480px; object-fit: contain; border-radius: 5px;">
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL: CUSTOM NOTICE DIALOG ================= -->
<div class="modal fade slider-modal-scope" id="sliderNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 108500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="mb-2" id="sliderNoticeIconWrap">
                    <i class="fas fa-circle-check text-success fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="sliderNoticeTitle" style="font-size: 14px;">Notification</h6>
                <p class="text-muted small mb-3" id="sliderNoticeMsg" style="font-size: 12px; line-height: 1.45;"></p>
                <button type="button" class="btn btn-primary btn-compact px-4" data-bs-dismiss="modal">Acknowledge</button>
            </div>
        </div>
    </div>
</div>

<script>
// Universal Notice Modal (Replaces browser alert)
function showSliderNotice(message, title = 'Notice', type = 'info') {
    const titleEl = document.getElementById('sliderNoticeTitle');
    const msgEl   = document.getElementById('sliderNoticeMsg');
    const iconEl  = document.getElementById('sliderNoticeIconWrap');

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

    const modalEl = document.getElementById('sliderNoticeModal');
    if (modalEl && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

// Lightbox Zoom Helper
function zoomThemeMedia(url, title = 'Banner Image') {
    const titleEl = document.getElementById('sliderMediaZoomTitle');
    const imgTag  = document.getElementById('sliderMediaZoomImgTag');
    if (url && imgTag) {
        if (titleEl) titleEl.innerText = title;
        imgTag.src = url;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('sliderMediaZoomModal')).show();
    }
}

// Global Media Extractor
function extractMediaItem(mediaInput) {
    if (!mediaInput) return null;
    const item = Array.isArray(mediaInput) ? mediaInput[0] : mediaInput;
    if (!item || typeof item !== 'object') return null;
    const rawPath = item.file_path || item.path || item.url || '';
    const rawUrl  = item.url || item.file_path || item.path || '';
    if (!rawPath || rawPath === 'undefined') return null;
    return {
        path: String(rawPath).trim(),
        url:  String(rawUrl).trim()
    };
}

/* Toggle Slide Content Fields */
function toggleContentInputs(prefix, isChecked) {
    const container = document.getElementById(prefix + 'ContentFieldsContainer');
    if (container) {
        container.style.display = isChecked ? 'block' : 'none';
    }
}

/* Toggle Overlay Color & Opacity */
function toggleOverlayInputs(prefix, isChecked) {
    const colorWrap = document.getElementById('createOverlayColorWrap' + prefix);
    const opacityWrap = document.getElementById('createOverlayOpacityWrap' + prefix);
    const createColorWrap = document.getElementById('createOverlayColorWrap');
    const createOpacityWrap = document.getElementById('createOverlayOpacityWrap');

    if (prefix === 'create') {
        if (createColorWrap) createColorWrap.style.display = isChecked ? 'block' : 'none';
        if (createOpacityWrap) createOpacityWrap.style.display = isChecked ? 'block' : 'none';
    } else {
        if (colorWrap) colorWrap.style.display = isChecked ? 'block' : 'none';
        if (opacityWrap) opacityWrap.style.display = isChecked ? 'block' : 'none';
    }
}

/* Add Modal: Reset & Prepare */
function prepareAddSlideModal() {
    clearSelectedSliderCreateImg();
}

/* Add Modal: Media Library Picker Handler */
function chooseSliderCreateMedia() {
    if (typeof window.openMediaPicker === 'function') {
        window.openMediaPicker({
            multiple: false,
            onSelect: function(media) {
                const picked = extractMediaItem(media);
                if (picked && picked.path) {
                    document.getElementById('createSliderMediaInput').value = picked.path;
                    const fileInput = document.getElementById('createSliderFileInput');
                    if (fileInput) fileInput.value = '';

                    const thumbBox = document.getElementById('createSliderThumbBox');
                    const imgTag = document.getElementById('createSlidePreviewImgTag');
                    if (imgTag) imgTag.src = picked.url;
                    if (thumbBox) thumbBox.style.display = 'inline-flex';
                    document.getElementById('createSliderPlaceholder')?.classList.add('d-none');
                    document.getElementById('createSliderClearBtn')?.classList.remove('d-none');

                    showSliderNotice('Banner attached from Global Media Library.', 'Media Selected', 'success');
                }
            }
        });
    } else {
        showSliderNotice('Global Media Library is loading, please try again.', 'Notice', 'warning');
    }
}

function handleSliderImagePreview(event, wrapId, clearBtnId, placeholderId) {
    const file = event.target.files[0];
    const clearBtn = document.getElementById(clearBtnId);
    const placeholder = document.getElementById(placeholderId);
    const thumbBox = document.getElementById('createSliderThumbBox');
    const imgTag = document.getElementById('createSlidePreviewImgTag');

    if (file) {
        document.getElementById('createSliderMediaInput').value = '';
        const reader = new FileReader();
        reader.onload = function(e) {
            if (imgTag) imgTag.src = e.target.result;
            if (thumbBox) thumbBox.style.display = 'inline-flex';
            if (placeholder) placeholder.classList.add('d-none');
            if (clearBtn) clearBtn.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    }
}

function clearSelectedSliderCreateImg() {
    const input = document.getElementById('createSliderFileInput');
    const mediaInput = document.getElementById('createSliderMediaInput');
    const thumbBox = document.getElementById('createSliderThumbBox');
    const clearBtn = document.getElementById('createSliderClearBtn');
    const placeholder = document.getElementById('createSliderPlaceholder');

    if (input) input.value = '';
    if (mediaInput) mediaInput.value = '';
    if (thumbBox) thumbBox.style.display = 'none';
    if (placeholder) placeholder.classList.remove('d-none');
    if (clearBtn) clearBtn.classList.add('d-none');
}

/* Edit Modal: Media Library Picker Handler */
function chooseSliderEditMedia(id) {
    if (typeof window.openMediaPicker === 'function') {
        window.openMediaPicker({
            multiple: false,
            onSelect: function(media) {
                const picked = extractMediaItem(media);
                if (picked && picked.path) {
                    const mediaInput = document.getElementById('editSliderMediaInput' + id);
                    if (mediaInput) mediaInput.value = picked.path;

                    const fileInput = document.getElementById('editSliderFileInput' + id);
                    if (fileInput) fileInput.value = '';

                    const imgTag = document.getElementById('editSliderImgTag' + id);
                    const thumbBox = document.getElementById('editSliderThumbBox' + id);
                    const metaText = document.getElementById('editSliderMetaText' + id);

                    if (imgTag) imgTag.src = picked.url;
                    if (thumbBox) thumbBox.style.display = 'inline-flex';
                    if (metaText) {
                        metaText.querySelector('small').innerText = picked.path.split('/').pop() || picked.path;
                    }
                    document.getElementById('editSliderPlaceholder' + id)?.classList.add('d-none');
                    document.getElementById('editSliderClearBtn' + id)?.classList.remove('d-none');
                    document.getElementById('editSliderRemoveImgFlag' + id).value = '0';

                    showSliderNotice('Banner updated from Global Media Library.', 'Media Selected', 'success');
                }
            }
        });
    } else {
        showSliderNotice('Global Media Library is loading, please try again.', 'Notice', 'warning');
    }
}

function handleSliderEditFilePreview(event, id) {
    const file = event.target.files[0];
    const imgTag = document.getElementById('editSliderImgTag' + id);
    const thumbBox = document.getElementById('editSliderThumbBox' + id);
    const clearBtn = document.getElementById('editSliderClearBtn' + id);
    const placeholder = document.getElementById('editSliderPlaceholder' + id);
    const removeFlag = document.getElementById('editSliderRemoveImgFlag' + id);

    if (file) {
        const mediaInput = document.getElementById('editSliderMediaInput' + id);
        if (mediaInput) mediaInput.value = '';

        const reader = new FileReader();
        reader.onload = function(e) {
            if (imgTag) imgTag.src = e.target.result;
            if (thumbBox) thumbBox.style.display = 'inline-flex';
            if (placeholder) placeholder.classList.add('d-none');
            if (clearBtn) clearBtn.classList.remove('d-none');
            if (removeFlag) removeFlag.value = '0';
        };
        reader.readAsDataURL(file);
    }
}

function clearSliderEditImage(id) {
    const input = document.getElementById('editSliderFileInput' + id);
    const mediaInput = document.getElementById('editSliderMediaInput' + id);
    const thumbBox = document.getElementById('editSliderThumbBox' + id);
    const clearBtn = document.getElementById('editSliderClearBtn' + id);
    const placeholder = document.getElementById('editSliderPlaceholder' + id);
    const removeFlag = document.getElementById('editSliderRemoveImgFlag' + id);

    if (input) input.value = '';
    if (mediaInput) mediaInput.value = '';
    if (thumbBox) thumbBox.style.display = 'none';
    if (placeholder) placeholder.classList.remove('d-none');
    if (clearBtn) clearBtn.classList.add('d-none');
    if (removeFlag) removeFlag.value = '1';
}

function openDeleteSliderModal(actionUrl, title) {
    const form = document.getElementById('confirmedDeleteSliderForm');
    const titleElem = document.getElementById('deleteSliderTitle');
    if (form && titleElem) {
        form.action = actionUrl;
        titleElem.textContent = title;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteSliderConfirmModal')).show();
    }
}

document.addEventListener('DOMContentLoaded', function () {
    @if(session('success'))
        showSliderNotice('{{ session('success') }}', 'Success', 'success');
    @endif
});
</script>
@endsection