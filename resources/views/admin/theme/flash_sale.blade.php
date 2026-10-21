@extends('layouts.admin')

@section('title', 'Campaigns & Promotional Slides - ' . config('app.name'))

@php
    // Safe media resolver supporting Global Media Library paths
    $resolveThemeImg = function($path, $fallback = 'https://placehold.co/80x80?text=No+Img') {
        if (empty($path) || $path === 'undefined' || $path === 'null') return $fallback;
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path, $fallback);
        }
        if (function_exists('show_image')) return show_image($path, $fallback);
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        return asset(ltrim(str_replace(['public_html/', 'public/'], '', $path), '/'));
    };
@endphp

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #campaignStudioRoot .btn,
    #campaignStudioRoot .btn-sm,
    #campaignStudioRoot button,
    #campaignStudioRoot .form-control,
    #campaignStudioRoot .form-select,
    #campaignStudioRoot .badge,
    #campaignStudioRoot .card,
    #campaignStudioRoot .mgmt-card,
    #campaignStudioRoot .theme-mgmt-header,
    #campaignStudioRoot .campaign-stat-box,
    #campaignStudioRoot .product-multi-select-box,
    #campaignStudioRoot .product-chip,
    #campaignStudioRoot .proof-media-surface,
    #campaignStudioRoot .proof-thumb-box,
    #campaignStudioRoot .collapse-toggle-btn,
    #campaignStudioRoot .action-btn-circle,
    #campaignStudioRoot .slide-item-mobile,
    .campaign-modal-scope .modal-content,
    .campaign-modal-scope .btn,
    .campaign-modal-scope .form-control,
    .campaign-modal-scope .form-select,
    .campaign-modal-scope .badge {
        border-radius: 5px !important;
    }

    #campaignStudioRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #campaignStudioRoot .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
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

    /* Enterprise Surface Header */
    .theme-mgmt-header {
        background: #0f172a;
        color: #ffffff;
        padding: 14px 18px;
        border: 1px solid #1e293b;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
        margin-bottom: 16px;
    }

    /* Enterprise Cards */
    .mgmt-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: hidden;
    }
    .mgmt-card-header {
        padding: 12px 16px;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .mgmt-card-body {
        padding: 16px;
        flex-grow: 1;
    }

    /* Campaign Stat Box */
    .campaign-stat-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 12px 14px;
    }
    .campaign-stat-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 7px 0;
        border-bottom: 1px dashed #e2e8f0;
        font-size: 12px;
    }
    .campaign-stat-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
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

    /* Table Architecture: 100% Fit Screen on PC with Zero Horizontal Scroll */
    .custom-slide-table {
        width: 100%;
        margin-bottom: 0;
    }
    .custom-slide-table th {
        background-color: #f8fafc;
        color: #475569;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 8px 6px;
        white-space: nowrap;
    }
    .custom-slide-table td {
        padding: 7px 6px;
        vertical-align: middle;
        font-size: 11px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        white-space: nowrap;
    }
    .custom-slide-table tbody tr:hover {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .custom-slide-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .custom-slide-table th,
        .custom-slide-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 4 Proportional Columns Summing to Exactly 100% */
        .col-slide-thumb   { width: 10% !important; }
        .col-slide-info    { width: 48% !important; }
        .col-slide-status  { width: 18% !important; }
        .col-slide-actions { width: 24% !important; }
    }

    /* Product Multi-select Box */
    .product-multi-select-box {
        border: 1px solid #cbd5e1;
        background: #ffffff;
        overflow: hidden;
    }
    .product-select-search {
        border: none;
        border-bottom: 1px solid #e2e8f0;
        border-radius: 0 !important;
        padding: 7px 10px;
        font-size: 12px;
        outline: none;
        box-shadow: none !important;
    }
    .product-checklist-container {
        max-height: 180px;
        overflow-y: auto;
        padding: 6px;
    }
    .product-checklist-container::-webkit-scrollbar { width: 4px; }
    .product-checklist-container::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .product-check-item {
        padding: 5px 8px;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 2px;
        transition: background-color 0.12s ease;
    }
    .product-check-item:hover { background-color: #f1f5f9; }
    .selected-chips-box {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        padding: 6px 8px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        min-height: 38px;
        max-height: 90px;
        overflow-y: auto;
    }
    .product-chip {
        background: #2563eb;
        color: #ffffff;
        padding: 3px 8px;
        font-size: 10px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        max-width: 100%;
    }
    .product-chip span.chip-text {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 180px;
    }
    .product-chip .chip-remove {
        cursor: pointer;
        font-size: 12px;
        line-height: 1;
        opacity: 0.8;
    }
    .product-chip .chip-remove:hover { opacity: 1; }

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
        width: 38px;
        height: 38px;
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

    /* Action Buttons */
    .action-btn-circle {
        width: 26px;
        height: 26px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 10.5px;
        transition: all 0.12s ease;
        border: 1px solid transparent;
        text-decoration: none;
    }
    .action-btn-circle.edit { background: #eff6ff; color: #2563eb; border-color: #bfdbfe; }
    .action-btn-circle.edit:hover { background: #2563eb; color: #ffffff; }
    .action-btn-circle.delete { background: #fef2f2; color: #dc2626; border-color: #fecaca; }
    .action-btn-circle.delete:hover { background: #dc2626; color: #ffffff; }

    /* Form Controls */
    .form-label { font-size: 11.5px; font-weight: 700; color: #334155; margin-bottom: 4px; display: block; }
    #campaignStudioRoot .form-control, #campaignStudioRoot .form-select,
    .campaign-modal-scope .form-control, .campaign-modal-scope .form-select {
        border-color: #cbd5e1;
        font-size: 12px;
        min-height: 33px;
        padding: 5px 10px;
        color: #0f172a;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #campaignStudioRoot .form-control:focus, #campaignStudioRoot .form-select:focus,
    .campaign-modal-scope .form-control:focus, .campaign-modal-scope .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Enhanced Toggle Switches */
    .form-switch .form-check-input {
        width: 36px;
        height: 18px;
        cursor: pointer;
        border-color: #cbd5e1;
    }
    .form-switch .form-check-input:checked {
        background-color: #2563eb;
        border-color: #2563eb;
    }

    /* Mobile Responsive (< 768px) */
    @media (max-width: 767.98px) {
        .theme-mgmt-header { padding: 12px 14px; }
        .theme-mgmt-header h4 { font-size: 15px !important; }
        .mgmt-card-header { padding: 10px 12px; }
        .mgmt-card-body { padding: 12px; }
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
            width: 44px; height: 44px; border: 1px solid #e2e8f0;
            overflow: hidden; background: #f8fafc; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .slide-item-mobile-img img { max-width: 100%; max-height: 100%; object-fit: cover; }
        .slide-item-mobile-content { flex: 1; min-width: 0; }
        .slide-title-mobile { font-size: 12px; font-weight: 700; color: #1e293b; margin-bottom: 2px; }
        .slide-subtitle-mobile { font-size: 10.5px; color: #64748b; margin-bottom: 0; }
        .slide-item-mobile-actions { display: flex; align-items: center; justify-content: flex-end; gap: 8px; padding-top: 6px; border-top: 1px dashed #e2e8f0; }
        .product-chip span.chip-text { max-width: 120px; }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="campaignStudioRoot">

    <!-- Top Header Banner -->
    <div class="theme-mgmt-header d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace text-uppercase" style="font-size: 9px;">
                    <i class="fas fa-rectangle-ad me-1"></i> STOREFRONT HERO
                </span>
                <span class="badge {{ ($flashSale->status ?? 1) ? 'bg-success' : 'bg-secondary' }} font-monospace text-uppercase" style="font-size: 9px;">
                    {{ ($flashSale->status ?? 1) ? 'Campaign Active' : 'Campaign Disabled' }}
                </span>
            </div>
            <h5 class="fw-bold mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-rectangle-ad text-primary"></i>
                <span>Campaigns &amp; Promotional Slides</span>
            </h5>
            <small class="text-white-50 d-block mt-1" style="font-size: 11.5px; line-height: 1.35;">
                Configure active store flash promotions, featured products showcase, countdown timer &amp; hero slides.
            </small>
        </div>

        <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold px-3" data-bs-toggle="modal" data-bs-target="#addOfferSlideModal" onclick="prepareAddSlideModal()">
            <i class="fas fa-plus me-1"></i> <span>Add New Slide</span>
        </button>
    </div>

    <!-- Main Content Row (PC 50%/50% Balanced Symmetrical Grid) -->
    <div class="row g-3 mb-3">
        
        <!-- 1. Left Column: Active Campaign Overview (COLLAPSIBLE) -->
        <div class="col-12 col-lg-5">
            <div class="mgmt-card">
                <div class="mgmt-card-header">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-stopwatch text-primary"></i>
                        <h6 class="fw-bold text-dark mb-0" style="font-size: 13px;">Active Campaign Overview</h6>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @if($flashSale->status ?? 1)
                            <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace" style="font-size: 9px;">Active</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border font-monospace" style="font-size: 9px;">Disabled</span>
                        @endif
                        <!-- Collapse Trigger Button -->
                        <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseCampaignOverview" aria-expanded="true" aria-controls="collapseCampaignOverview" title="Toggle Section">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>
                </div>

                <div class="collapse show" id="collapseCampaignOverview">
                    <div class="mgmt-card-body d-flex flex-column justify-content-between">
                        <div class="campaign-stat-box mb-3">
                            <div class="campaign-stat-item">
                                <span class="text-muted">Campaign Title:</span>
                                <strong class="text-dark text-truncate ms-2" style="max-width: 180px;">{{ $flashSale->title ?? 'Deals & Offers' }}</strong>
                            </div>
                            <div class="campaign-stat-item">
                                <span class="text-muted">Subtitle:</span>
                                <span class="text-secondary text-truncate ms-2" style="max-width: 180px;">{{ $flashSale->subtitle ?? 'No subtitle set' }}</span>
                            </div>
                            <div class="campaign-stat-item">
                                <span class="text-muted">Expires On:</span>
                                <span class="fw-bold font-monospace text-danger small bn-digit">
                                    {{ !empty($flashSale->end_time) ? date('d M Y, h:i A', strtotime($flashSale->end_time)) : 'Not scheduled' }}
                                </span>
                            </div>
                            <div class="campaign-stat-item">
                                <span class="text-muted">Included Products:</span>
                                <span class="badge bg-primary font-monospace bn-digit px-2 py-0.5" style="font-size: 9.5px;">
                                    {{ count($selectedProductIds ?? []) }} Selected
                                </span>
                            </div>
                            <div class="campaign-stat-item">
                                <span class="text-muted">Category Nav Menu:</span>
                                <span class="fw-semibold {{ ($showCatMenu ?? true) ? 'text-success' : 'text-muted' }}">
                                    {{ ($showCatMenu ?? true) ? 'Visible' : 'Hidden' }}
                                </span>
                            </div>
                            <div class="campaign-stat-item">
                                <span class="text-muted">Hero Right Banner:</span>
                                <span class="fw-semibold {{ ($flashSale->right_card_status ?? 1) ? 'text-success' : 'text-muted' }}">
                                    {{ ($flashSale->right_card_status ?? 1) ? 'Visible' : 'Hidden' }}
                                </span>
                            </div>
                        </div>

                        <button type="button" class="btn btn-outline-primary btn-compact shadow-xs fw-bold w-100 py-2 d-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#editCampaignModal">
                            <i class="fas fa-sliders-h"></i> <span>Manage Campaign &amp; Products</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Right Column: Promotional Slides List (COLLAPSIBLE) -->
        <div class="col-12 col-lg-7">
            <div class="mgmt-card">
                <div class="mgmt-card-header">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-images text-success"></i>
                        <h6 class="fw-bold text-dark mb-0" style="font-size: 13px;">Hero Promotional Slides</h6>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-dark border font-monospace bn-digit px-2 py-0.5" style="font-size: 9.5px;">
                            {{ $offerCards->count() }} {{ Str::plural('Slide', $offerCards->count()) }}
                        </span>
                        <!-- Collapse Trigger Button -->
                        <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapsePromotionalSlides" aria-expanded="true" aria-controls="collapsePromotionalSlides" title="Toggle Section">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>
                </div>

                <div class="collapse show" id="collapsePromotionalSlides">
                    <div class="mgmt-card-body p-0">
                        <!-- Desktop Table View (100% Fit Screen with Zero Horizontal Scroll) -->
                        <div class="table-responsive pc-fit-container d-none d-md-block">
                            <table class="table align-middle custom-slide-table mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-3 col-slide-thumb text-center">Thumb</th>
                                        <th class="col-slide-info">Slide Info &amp; Badge</th>
                                        <th class="col-slide-status text-center">Status</th>
                                        <th class="text-end pe-3 col-slide-actions">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($offerCards as $card)
                                        @php
                                            $cardImg = $resolveThemeImg($card->image);
                                        @endphp
                                        <tr>
                                            <!-- Interactive Media Preview Thumbnail -->
                                            <td class="ps-3 col-slide-thumb text-center">
                                                <div class="proof-thumb-box shadow-xs" onclick="zoomThemeMedia('{{ $cardImg }}', '{{ addslashes($card->title) }}')" title="Click to Zoom Image">
                                                    <img src="{{ $cardImg }}" alt="Preview" onerror="this.src='https://placehold.co/80x80?text=No+Img';">
                                                    <div class="proof-thumb-hover-overlay">
                                                        <i class="fas fa-search-plus text-white" style="font-size: 8.5px;"></i>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Slide Info -->
                                            <td class="col-slide-info">
                                                <div class="min-w-0">
                                                    <span class="badge px-1.5 py-0 font-monospace mb-1" style="background: {{ $card->badge_bg ?: '#2563eb' }}; color: #ffffff; font-size: 8.5px;">
                                                        {{ $card->badge_text }}
                                                    </span>
                                                    <strong class="text-dark d-block text-truncate" style="font-size: 12px; line-height: 1.3;" title="{{ $card->title }}">
                                                        {{ $card->title }}
                                                    </strong>
                                                    <small class="text-muted d-block text-truncate" style="font-size: 10.5px;" title="{{ $card->subtitle }}">
                                                        {{ $card->subtitle }}
                                                    </small>
                                                </div>
                                            </td>

                                            <!-- Status -->
                                            <td class="col-slide-status text-center">
                                                @if($card->status)
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-2 py-0.5" style="font-size: 9.5px;">Active</span>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary border font-monospace px-2 py-0.5" style="font-size: 9.5px;">Disabled</span>
                                                @endif
                                            </td>

                                            <!-- Actions -->
                                            <td class="text-end pe-3 col-slide-actions">
                                                <div class="d-inline-flex gap-1">
                                                    <button type="button" class="action-btn-circle edit shadow-xs" 
                                                        data-id="{{ $card->id }}"
                                                        data-title="{{ $card->title }}"
                                                        data-subtitle="{{ $card->subtitle }}"
                                                        data-badge_text="{{ $card->badge_text }}"
                                                        data-badge_bg="{{ $card->badge_bg }}"
                                                        data-btn_text="{{ $card->btn_text }}"
                                                        data-btn_url="{{ $card->btn_url }}"
                                                        data-serial="{{ $card->serial }}"
                                                        data-status="{{ $card->status }}"
                                                        data-image="{{ $cardImg }}"
                                                        onclick="openEditOfferSlideModal(this)" 
                                                        title="Edit Slide">
                                                        <i class="fas fa-pen"></i>
                                                    </button>
                                                    <button type="button" class="action-btn-circle delete shadow-xs" 
                                                        onclick="openDeleteSlideModal('{{ route('admin.theme.setting.offer_card.destroy', $card->id) }}', '{{ addslashes($card->title) }}')" 
                                                        title="Delete Slide">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-muted small">
                                                <i class="fas fa-images fa-2x opacity-25 mb-2 d-block"></i>
                                                <span class="fw-semibold">No promotional slides found. Click "Add New Slide" to start.</span>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Stack View (100/100 Mobile Comfort) -->
                        <div class="d-block d-md-none p-2">
                            @forelse($offerCards as $card)
                                @php
                                    $cardImgMob = $resolveThemeImg($card->image);
                                @endphp
                                <div class="slide-item-mobile shadow-xs">
                                    <div class="slide-item-mobile-top">
                                        <div class="proof-thumb-box shadow-xs" onclick="zoomThemeMedia('{{ $cardImgMob }}', '{{ addslashes($card->title) }}')" title="Zoom">
                                            <img src="{{ $cardImgMob }}" alt="Preview" onerror="this.src='https://placehold.co/80x80?text=No+Img';">
                                        </div>
                                        <div class="slide-item-mobile-content">
                                            <div class="d-flex align-items-center gap-1 mb-1">
                                                <span class="badge px-1.5 py-0 font-monospace" style="background: {{ $card->badge_bg ?: '#2563eb' }}; color: #ffffff; font-size: 8.5px;">
                                                    {{ $card->badge_text }}
                                                </span>
                                                @if($card->status)
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-1 py-0" style="font-size: 8.5px;">Active</span>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary border font-monospace px-1 py-0" style="font-size: 8.5px;">Off</span>
                                                @endif
                                            </div>
                                            <div class="slide-title-mobile text-truncate">{{ $card->title }}</div>
                                            <div class="slide-subtitle-mobile text-truncate">{{ $card->subtitle }}</div>
                                        </div>
                                    </div>
                                    
                                    <div class="slide-item-mobile-actions">
                                        <button type="button" class="btn btn-outline-primary btn-compact"
                                            data-id="{{ $card->id }}"
                                            data-title="{{ $card->title }}"
                                            data-subtitle="{{ $card->subtitle }}"
                                            data-badge_text="{{ $card->badge_text }}"
                                            data-badge_bg="{{ $card->badge_bg }}"
                                            data-btn_text="{{ $card->btn_text }}"
                                            data-btn_url="{{ $card->btn_url }}"
                                            data-serial="{{ $card->serial }}"
                                            data-status="{{ $card->status }}"
                                            data-image="{{ $cardImgMob }}"
                                            onclick="openEditOfferSlideModal(this)">
                                            <i class="fas fa-pen me-1"></i> <span>Edit</span>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-compact"
                                            onclick="openDeleteSlideModal('{{ route('admin.theme.setting.offer_card.destroy', $card->id) }}', '{{ addslashes($card->title) }}')">
                                            <i class="fas fa-trash-alt me-1"></i> <span>Delete</span>
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-muted small">
                                    <i class="fas fa-images fa-2x opacity-25 mb-2 d-block"></i>
                                    No promotional slides found.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ================= MODAL 1: EDIT FLASH SALE CAMPAIGN ================= -->
<div class="modal fade campaign-modal-scope" id="editCampaignModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('admin.theme.setting.flash_sale.update') }}" method="POST">
                @csrf
                <div class="modal-header bg-dark text-white py-2 px-3 d-flex justify-content-between align-items-center">
                    <h6 class="modal-title fw-bold d-flex align-items-center gap-1 mb-0" style="font-size: 13.5px;">
                        <i class="fas fa-stopwatch text-primary"></i> <span>Edit Flash Sale Campaign</span>
                    </h6>
                    <button type="button" class="btn-close btn-close-white btn-sm shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-3">
                    <div class="row g-2">
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Campaign Title *</label>
                            <input type="text" name="title" class="form-control" value="{{ $flashSale->title ?? 'Deals & Offers' }}" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Campaign Subtitle *</label>
                            <input type="text" name="subtitle" class="form-control" value="{{ $flashSale->subtitle ?? 'Limited time discounts' }}" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Countdown Expiry Time</label>
                            <input type="datetime-local" name="end_time" class="form-control font-monospace bn-digit" value="{{ (!empty($flashSale->end_time)) ? date('Y-m-d\TH:i', strtotime($flashSale->end_time)) : '' }}">
                        </div>
                        <div class="col-12 col-sm-6 d-flex align-items-center pt-sm-3">
                            <div class="p-2 border bg-light w-100 d-flex align-items-center justify-content-between" style="border-radius: 5px;">
                                <label class="form-check-label small fw-bold text-dark m-0" for="modalCampaignStatus">Campaign Active</label>
                                <div class="form-check form-switch m-0 ps-0">
                                    <input class="form-check-input ms-0" type="checkbox" name="status" value="1" id="modalCampaignStatus" {{ ($flashSale->status ?? 1) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>

                        <!-- Product Checklist -->
                        <div class="col-12 mt-2">
                            <label class="form-label d-flex justify-content-between align-items-center mb-1">
                                <span>Select Campaign Products</span>
                                <small class="text-muted fw-normal font-monospace">Check products to showcase</small>
                            </label>

                            <div class="product-multi-select-box" style="border-radius: 5px;">
                                <div class="selected-chips-box" id="selectedChipsWrapper">
                                    <span class="text-muted small my-auto placeholder-text" style="font-size: 11px;">No products selected yet.</span>
                                </div>

                                <input type="text" id="productFilterInput" class="form-control product-select-search" placeholder="Search product by title...">

                                <div class="product-checklist-container" id="productChecklist">
                                    @foreach($allProducts as $p)
                                        @php
                                            $isChecked = in_array($p->id, $selectedProductIds ?? []);
                                            $price = $p->sale_price ?? $p->regular_price ?? 0;
                                        @endphp
                                        <label class="product-check-item" data-title="{{ strtolower($p->title) }}">
                                            <div class="d-flex align-items-center gap-2 min-w-0">
                                                <input class="form-check-input mt-0 product-checkbox flex-shrink-0" type="checkbox" name="product_ids[]" value="{{ $p->id }}" data-name="{{ $p->title }}" {{ $isChecked ? 'checked' : '' }}>
                                                <span class="small fw-medium text-dark text-truncate" style="font-size: 11.5px;">{{ $p->title }}</span>
                                            </div>
                                            <span class="badge bg-light text-secondary border small font-monospace bn-digit flex-shrink-0 ms-2">৳{{ number_format($price, 2) }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Visibility Switches -->
                        <div class="col-12 pt-2 border-top mt-2">
                            <div class="row g-2">
                                <div class="col-12 col-sm-6">
                                    <div class="p-2 border bg-light d-flex align-items-center justify-content-between" style="border-radius: 5px;">
                                        <label class="form-check-label small fw-bold text-dark m-0" for="modalShowCategoryMenu">
                                            Homepage Category Menu
                                        </label>
                                        <div class="form-check form-switch m-0 ps-0">
                                            <input class="form-check-input ms-0" type="checkbox" name="show_home_category_menu" id="modalShowCategoryMenu" value="1" {{ ($showCatMenu ?? true) ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="p-2 border bg-light d-flex align-items-center justify-content-between" style="border-radius: 5px;">
                                        <label class="form-check-label small fw-bold text-dark m-0" for="modalRightCardStatus">
                                            Promotional Right Banner
                                        </label>
                                        <div class="form-check form-switch m-0 ps-0">
                                            <input class="form-check-input ms-0" type="checkbox" name="right_card_status" id="modalRightCardStatus" value="1" {{ ($flashSale->right_card_status ?? 1) ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer py-2 px-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-3">Update Campaign</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= MODAL 2: ADD NEW SLIDE (WITH GLOBAL MEDIA PICKER) ================= -->
<div class="modal fade campaign-modal-scope" id="addOfferSlideModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('admin.theme.setting.offer_card.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-dark text-white py-2 px-3 d-flex justify-content-between align-items-center">
                    <h6 class="modal-title fw-bold d-flex align-items-center gap-1 mb-0" style="font-size: 13.5px;">
                        <i class="fas fa-plus-circle text-primary"></i> <span>Create Promotional Slide</span>
                    </h6>
                    <button type="button" class="btn-close btn-close-white btn-sm shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-3">
                    <div class="row g-2">
                        <div class="col-12 col-sm-8">
                            <label class="form-label">Badge Text *</label>
                            <input type="text" name="badge_text" class="form-control" placeholder="Special Discount" value="Special Voucher" required>
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label">Badge Color</label>
                            <input type="color" name="badge_bg" class="form-control form-control-color w-100" value="#10b981" style="height: 33px;">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Card Title *</label>
                            <input type="text" name="title" class="form-control" placeholder="Promotional Campaign Title" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Card Subtitle *</label>
                            <textarea name="subtitle" class="form-control" rows="2" placeholder="Brief details of promotion..." required></textarea>
                        </div>

                        <!-- 200% Global Media Chooser Component for Feature Image -->
                        <div class="col-12">
                            <label class="form-label d-flex justify-content-between align-items-center mb-1">
                                <span><i class="fas fa-paperclip text-primary me-1"></i> Feature Image *</span>
                                <span class="badge bg-light text-muted border font-monospace" style="font-size: 8.5px;">GLOBAL MEDIA</span>
                            </label>
                            
                            <input type="hidden" name="image_media" id="createSlideMediaInput" value="">

                            <div class="proof-media-surface" style="border-radius: 5px;">
                                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                    <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold" onclick="chooseOfferSlideCreateMedia()">
                                        <i class="fas fa-photo-film me-1"></i> Choose from Media Library
                                    </button>
                                    <span class="small text-muted" style="font-size: 11px;">or upload local file:</span>
                                </div>

                                <input type="file" name="image" id="createSlideImageInput" class="form-control mb-2" accept="image/*" onchange="handleImagePreview(event, 'createSlidePreviewWrap', 'createSlideClearBtn', 'createSlidePlaceholder')">
                                
                                <div class="bg-white border p-2 d-flex align-items-center gap-2" id="createSlidePreviewWrap" style="border-radius: 5px;">
                                    <div class="proof-thumb-box shadow-xs" id="createThumbBox" style="display: none;" onclick="zoomCreateSlidePreview()" title="Click to Zoom">
                                        <img id="createSlidePreviewImgTag" src="" alt="Preview">
                                        <div class="proof-thumb-hover-overlay">
                                            <i class="fas fa-search-plus text-white" style="font-size: 8px;"></i>
                                        </div>
                                    </div>
                                    <span class="text-muted small my-auto" id="createSlidePlaceholder" style="font-size: 11px;">
                                        <i class="fas fa-image me-1 text-primary"></i> No slide image selected yet
                                    </span>
                                    <button type="button" class="btn btn-outline-danger btn-compact py-0 px-2 ms-auto d-none" id="createSlideClearBtn" onclick="clearSelectedCreateImage()" style="height: 22px; font-size: 9.5px !important;">
                                        <i class="fas fa-times me-1"></i> Clear
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <label class="form-label">Button Label *</label>
                            <input type="text" name="btn_text" class="form-control" value="Shop Now" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Button Target Link *</label>
                            <input type="text" name="btn_url" class="form-control font-monospace" value="/shop" required>
                        </div>

                        <div class="col-12 col-sm-6">
                            <label class="form-label">Sort Priority</label>
                            <input type="number" name="serial" class="form-control font-monospace bn-digit" value="1">
                        </div>
                        <div class="col-12 col-sm-6 d-flex align-items-center pt-sm-3">
                            <div class="p-2 border bg-light w-100 d-flex align-items-center justify-content-between" style="border-radius: 5px;">
                                <label class="form-check-label fw-bold text-dark small m-0" for="createStatusSwitch">Active Slide Status</label>
                                <div class="form-check form-switch m-0 ps-0">
                                    <input class="form-check-input ms-0" type="checkbox" name="status" value="1" id="createStatusSwitch" checked>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer py-2 px-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-3">Save &amp; Publish</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= MODAL 3: EDIT SLIDE (WITH GLOBAL MEDIA PICKER) ================= -->
<div class="modal fade campaign-modal-scope" id="editOfferSlideModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form id="editSlideForm" action="" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="remove_image" id="editRemoveImageFlag" value="0">
                <input type="hidden" name="image_media" id="editSlideMediaInput" value="">

                <div class="modal-header bg-dark text-white py-2 px-3 d-flex justify-content-between align-items-center">
                    <h6 class="modal-title fw-bold d-flex align-items-center gap-1 mb-0" style="font-size: 13.5px;">
                        <i class="fas fa-pen text-primary"></i> <span>Edit Promotional Slide</span>
                    </h6>
                    <button type="button" class="btn-close btn-close-white btn-sm shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-3">
                    <div class="row g-2">
                        <div class="col-12 col-sm-8">
                            <label class="form-label">Badge Text *</label>
                            <input type="text" name="badge_text" id="editBadgeText" class="form-control" required>
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label">Badge Color</label>
                            <input type="color" name="badge_bg" id="editBadgeBg" class="form-control form-control-color w-100" style="height: 33px;">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Card Title *</label>
                            <input type="text" name="title" id="editTitle" class="form-control" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Card Subtitle *</label>
                            <textarea name="subtitle" id="editSubtitle" class="form-control" rows="2" required></textarea>
                        </div>

                        <!-- 200% Global Media Chooser Component for Feature Image -->
                        <div class="col-12">
                            <label class="form-label d-flex justify-content-between align-items-center mb-1">
                                <span><i class="fas fa-paperclip text-primary me-1"></i> Feature Image</span>
                                <span class="badge bg-light text-muted border font-monospace" style="font-size: 8.5px;">GLOBAL MEDIA</span>
                            </label>

                            <div class="proof-media-surface" style="border-radius: 5px;">
                                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                    <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold" onclick="chooseOfferSlideEditMedia()">
                                        <i class="fas fa-photo-film me-1"></i> Choose from Media Library
                                    </button>
                                    <span class="small text-muted" style="font-size: 11px;">or upload local file:</span>
                                </div>

                                <input type="file" name="image" id="editSlideImageInput" class="form-control mb-2" accept="image/*" onchange="handleEditFileSelection(event)">
                                
                                <div class="bg-white border p-2 d-flex align-items-center gap-2" id="editSlidePreviewWrap" style="border-radius: 5px;">
                                    <div class="proof-thumb-box shadow-xs" id="editThumbBox" onclick="zoomEditSlidePreview()" title="Click to Zoom">
                                        <img id="editCurrentImgTag" src="" alt="Slide Image" onerror="this.src='https://placehold.co/80x80?text=No+Img';">
                                        <div class="proof-thumb-hover-overlay">
                                            <i class="fas fa-search-plus text-white" style="font-size: 8px;"></i>
                                        </div>
                                    </div>
                                    <span class="text-muted small my-auto d-none" id="editSlidePlaceholder" style="font-size: 11px;">
                                        <i class="fas fa-image me-1 text-primary"></i> No image selected
                                    </span>
                                    <button type="button" class="btn btn-outline-danger btn-compact py-0 px-2 ms-auto" id="editSlideClearBtn" onclick="clearEditSlideImage()" style="height: 22px; font-size: 9.5px !important;" title="Remove Image">
                                        <i class="fas fa-times me-1"></i> Clear
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <label class="form-label">Button Label *</label>
                            <input type="text" name="btn_text" id="editBtnText" class="form-control" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Button Target Link *</label>
                            <input type="text" name="btn_url" id="editBtnUrl" class="form-control font-monospace" required>
                        </div>

                        <div class="col-12 col-sm-6">
                            <label class="form-label">Sort Priority</label>
                            <input type="number" name="serial" id="editSerial" class="form-control font-monospace bn-digit">
                        </div>
                        <div class="col-12 col-sm-6 d-flex align-items-center pt-sm-3">
                            <div class="p-2 border bg-light w-100 d-flex align-items-center justify-content-between" style="border-radius: 5px;">
                                <label class="form-check-label fw-bold text-dark small m-0" for="editStatusSwitch">Active Slide Status</label>
                                <div class="form-check form-switch m-0 ps-0">
                                    <input class="form-check-input ms-0" type="checkbox" name="status" value="1" id="editStatusSwitch">
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
</div>

<!-- ================= MODAL 4: DELETE CONFIRMATION ================= -->
<div class="modal fade campaign-modal-scope" id="deleteSlideConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="mb-2 text-danger">
                    <i class="fas fa-trash-can fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 14.5px;">Delete Promotional Slide?</h6>
                <p class="text-muted small mb-3" style="font-size: 12px; line-height: 1.45;">
                    Are you sure you want to remove <strong id="deleteSlideTitle" class="text-dark">this slide</strong>?
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <form id="confirmedDeleteSlideForm" action="" method="POST" class="d-inline m-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-compact shadow-xs fw-bold">Yes, Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL 5: LIGHTBOX ZOOM MODAL ================= -->
<div class="modal fade campaign-modal-scope" id="slideMediaZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 108000;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg text-center p-2">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" style="font-size: 12.5px;" id="slideMediaZoomTitle">
                    <i class="fas fa-image text-primary me-1"></i> Slide Preview
                </span>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2">
                <img id="slideMediaZoomImgTag" src="" alt="Slide Asset" class="img-fluid border" style="max-height: 480px; object-fit: contain; border-radius: 5px;">
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL 6: CUSTOM ENTERPRISE NOTICE MODAL ================= -->
<div class="modal fade campaign-modal-scope" id="campaignNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 108500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="mb-2" id="campaignNoticeIconWrap">
                    <i class="fas fa-circle-check text-success fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="campaignNoticeTitle" style="font-size: 14px;">Notification</h6>
                <p class="text-muted small mb-3" id="campaignNoticeMessage" style="font-size: 12px; line-height: 1.45;"></p>
                <button type="button" class="btn btn-primary btn-compact px-4" data-bs-dismiss="modal">Acknowledge</button>
            </div>
        </div>
    </div>
</div>

<script>
let originalEditImageSrc = '';

// Universal Custom Notice Modal
function showCampaignModalNotice(message, title = 'Notice', type = 'info') {
    const titleEl = document.getElementById('campaignNoticeTitle');
    const msgEl   = document.getElementById('campaignNoticeMessage');
    const iconEl  = document.getElementById('campaignNoticeIconWrap');

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

    const modalEl = document.getElementById('campaignNoticeModal');
    if (modalEl && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
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

/* Add Modal: Reset & Prepare */
function prepareAddSlideModal() {
    clearSelectedCreateImage();
}

/* Add Modal: Media Library Picker */
function chooseOfferSlideCreateMedia() {
    if (typeof window.openMediaPicker === 'function') {
        window.openMediaPicker({
            multiple: false,
            onSelect: function(media) {
                const picked = extractMediaItem(media);
                if (picked && picked.path) {
                    document.getElementById('createSlideMediaInput').value = picked.path;
                    const fileInput = document.getElementById('createSlideImageInput');
                    if (fileInput) fileInput.value = '';

                    const thumbBox = document.getElementById('createThumbBox');
                    const imgTag = document.getElementById('createSlidePreviewImgTag');
                    if (imgTag) imgTag.src = picked.url;
                    if (thumbBox) thumbBox.style.display = 'inline-flex';
                    document.getElementById('createSlidePlaceholder')?.classList.add('d-none');
                    document.getElementById('createSlideClearBtn')?.classList.remove('d-none');

                    showCampaignModalNotice('Image attached from Global Media Library.', 'Image Selected', 'success');
                }
            }
        });
    } else {
        showCampaignModalNotice('Global Media Library is loading, please try again.', 'Notice', 'warning');
    }
}

function handleImagePreview(event, wrapId, clearBtnId, placeholderId) {
    const file = event.target.files[0];
    const clearBtn = document.getElementById(clearBtnId);
    const placeholder = document.getElementById(placeholderId);
    const thumbBox = document.getElementById('createThumbBox');
    const imgTag = document.getElementById('createSlidePreviewImgTag');

    if (file) {
        document.getElementById('createSlideMediaInput').value = '';
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

function clearSelectedCreateImage() {
    const input = document.getElementById('createSlideImageInput');
    const mediaInput = document.getElementById('createSlideMediaInput');
    const thumbBox = document.getElementById('createThumbBox');
    const clearBtn = document.getElementById('createSlideClearBtn');
    const placeholder = document.getElementById('createSlidePlaceholder');
    
    if (input) input.value = '';
    if (mediaInput) mediaInput.value = '';
    if (thumbBox) thumbBox.style.display = 'none';
    if (placeholder) placeholder.classList.remove('d-none');
    if (clearBtn) clearBtn.classList.add('d-none');
}

/* Edit Modal: Media Library Picker */
function chooseOfferSlideEditMedia() {
    if (typeof window.openMediaPicker === 'function') {
        window.openMediaPicker({
            multiple: false,
            onSelect: function(media) {
                const picked = extractMediaItem(media);
                if (picked && picked.path) {
                    document.getElementById('editSlideMediaInput').value = picked.path;
                    const fileInput = document.getElementById('editSlideImageInput');
                    if (fileInput) fileInput.value = '';

                    const imgTag = document.getElementById('editCurrentImgTag');
                    const thumbBox = document.getElementById('editThumbBox');
                    if (imgTag) imgTag.src = picked.url;
                    if (thumbBox) thumbBox.style.display = 'inline-flex';
                    document.getElementById('editSlidePlaceholder')?.classList.add('d-none');
                    document.getElementById('editSlideClearBtn')?.classList.remove('d-none');
                    document.getElementById('editRemoveImageFlag').value = '0';

                    showCampaignModalNotice('Image updated from Global Media Library.', 'Image Selected', 'success');
                }
            }
        });
    } else {
        showCampaignModalNotice('Global Media Library is loading, please try again.', 'Notice', 'warning');
    }
}

function handleEditFileSelection(event) {
    const file = event.target.files[0];
    const imgTag = document.getElementById('editCurrentImgTag');
    const thumbBox = document.getElementById('editThumbBox');
    const clearBtn = document.getElementById('editSlideClearBtn');
    const placeholder = document.getElementById('editSlidePlaceholder');
    const removeFlag = document.getElementById('editRemoveImageFlag');

    if (file) {
        document.getElementById('editSlideMediaInput').value = '';
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

function clearEditSlideImage() {
    const input = document.getElementById('editSlideImageInput');
    const mediaInput = document.getElementById('editSlideMediaInput');
    const thumbBox = document.getElementById('editThumbBox');
    const clearBtn = document.getElementById('editSlideClearBtn');
    const placeholder = document.getElementById('editSlidePlaceholder');
    const removeFlag = document.getElementById('editRemoveImageFlag');

    if (input) input.value = '';
    if (mediaInput) mediaInput.value = '';
    if (thumbBox) thumbBox.style.display = 'none';
    if (placeholder) placeholder.classList.remove('d-none');
    if (clearBtn) clearBtn.classList.add('d-none');
    if (removeFlag) removeFlag.value = '1';
}

function openEditOfferSlideModal(btn) {
    const id = btn.getAttribute('data-id');
    const form = document.getElementById('editSlideForm');
    form.action = "{{ url('/admin/theme-setting/offer-card/update') }}/" + id;

    document.getElementById('editTitle').value = btn.getAttribute('data-title');
    document.getElementById('editSubtitle').value = btn.getAttribute('data-subtitle');
    document.getElementById('editBadgeText').value = btn.getAttribute('data-badge_text');
    document.getElementById('editBadgeBg').value = btn.getAttribute('data-badge_bg');
    document.getElementById('editBtnText').value = btn.getAttribute('data-btn_text');
    document.getElementById('editBtnUrl').value = btn.getAttribute('data-btn_url');
    document.getElementById('editSerial').value = btn.getAttribute('data-serial');
    document.getElementById('editStatusSwitch').checked = (btn.getAttribute('data-status') === '1');
    document.getElementById('editRemoveImageFlag').value = '0';
    document.getElementById('editSlideMediaInput').value = '';

    originalEditImageSrc = btn.getAttribute('data-image');
    document.getElementById('editSlideImageInput').value = '';

    const imgTag = document.getElementById('editCurrentImgTag');
    const thumbBox = document.getElementById('editThumbBox');
    const clearBtn = document.getElementById('editSlideClearBtn');
    const placeholder = document.getElementById('editSlidePlaceholder');

    if (originalEditImageSrc && !originalEditImageSrc.includes('placeholder')) {
        imgTag.src = originalEditImageSrc;
        thumbBox.style.display = 'inline-flex';
        placeholder.classList.add('d-none');
        clearBtn.classList.remove('d-none');
    } else {
        thumbBox.style.display = 'none';
        placeholder.classList.remove('d-none');
        clearBtn.classList.add('d-none');
    }

    const editModal = new bootstrap.Modal(document.getElementById('editOfferSlideModal'));
    editModal.show();
}

function openDeleteSlideModal(actionUrl, title) {
    const form = document.getElementById('confirmedDeleteSlideForm');
    const titleElem = document.getElementById('deleteSlideTitle');
    if (form && titleElem) {
        form.action = actionUrl;
        titleElem.textContent = title;
        const delModal = new bootstrap.Modal(document.getElementById('deleteSlideConfirmModal'));
        delModal.show();
    }
}

// Lightbox Zoom Helpers
function zoomThemeMedia(url, title = 'Slide Image') {
    const titleEl = document.getElementById('slideMediaZoomTitle');
    const imgTag  = document.getElementById('slideMediaZoomImgTag');
    if (url && imgTag) {
        if (titleEl) titleEl.innerText = title;
        imgTag.src = url;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('slideMediaZoomModal')).show();
    }
}

function zoomCreateSlidePreview() {
    const imgTag = document.getElementById('createSlidePreviewImgTag');
    if (imgTag && imgTag.src) {
        zoomThemeMedia(imgTag.src, 'New Slide Preview');
    }
}

function zoomEditSlidePreview() {
    const imgTag = document.getElementById('editCurrentImgTag');
    if (imgTag && imgTag.src) {
        zoomThemeMedia(imgTag.src, 'Slide Preview');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    @if(session('success'))
        showCampaignModalNotice('{{ session('success') }}', 'Success', 'success');
    @endif

    // Product Checklist Live Search & Chips
    const searchInput = document.getElementById('productFilterInput');
    const items = document.querySelectorAll('.product-check-item');
    const chipsWrapper = document.getElementById('selectedChipsWrapper');
    const checkboxes = document.querySelectorAll('.product-checkbox');

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

    function renderChips() {
        chipsWrapper.innerHTML = '';
        let hasSelected = false;

        checkboxes.forEach(cb => {
            if (cb.checked) {
                hasSelected = true;
                const id = cb.value;
                const name = cb.getAttribute('data-name');

                const chip = document.createElement('span');
                chip.className = 'product-chip';
                chip.innerHTML = `
                    <span class="chip-text">${name}</span>
                    <span class="chip-remove" data-id="${id}">&times;</span>
                `;
                chipsWrapper.appendChild(chip);
            }
        });

        if (!hasSelected) {
            chipsWrapper.innerHTML = '<span class="text-muted small my-auto placeholder-text" style="font-size: 11px;">No products selected yet.</span>';
        }
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', renderChips);
    });

    chipsWrapper.addEventListener('click', function (e) {
        const removeBtn = e.target.closest('.chip-remove');
        if (removeBtn) {
            const targetId = removeBtn.getAttribute('data-id');
            const targetCb = document.querySelector(`.product-checkbox[value="${targetId}"]`);
            if (targetCb) {
                targetCb.checked = false;
                renderChips();
            }
        }
    });

    renderChips();
});
</script>
@endsection