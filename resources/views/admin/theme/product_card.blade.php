@extends('layouts.admin')

@section('title', 'Product Card & Review Settings - ' . config('app.name'))

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #productCardSettingsRoot .btn,
    #productCardSettingsRoot .btn-sm,
    #productCardSettingsRoot button,
    #productCardSettingsRoot .form-control,
    #productCardSettingsRoot .form-select,
    #productCardSettingsRoot .badge,
    #productCardSettingsRoot .alert,
    #productCardSettingsRoot .card,
    #productCardSettingsRoot .settings-header-card,
    #productCardSettingsRoot .settings-panel-card,
    #productCardSettingsRoot .status-kpi-pill,
    #productCardSettingsRoot .feature-switch-card,
    #productCardSettingsRoot .collapse-toggle-btn,
    .pc-modal-scope .modal-content,
    .pc-modal-scope .btn,
    .pc-modal-scope .badge {
        border-radius: 5px !important;
    }

    #productCardSettingsRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #productCardSettingsRoot .input-group > .form-control {
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

    /* Enterprise Surface Header Card */
    .settings-header-card {
        background: #0f172a;
        color: #ffffff;
        padding: 14px 18px;
        border: 1px solid #1e293b;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
        margin-bottom: 14px;
    }

    /* Section Panel Card */
    .settings-panel-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 14px;
        overflow: hidden;
    }
    .settings-panel-header {
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

    /* Feature Switch Card */
    .feature-switch-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        padding: 10px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        transition: all 0.15s ease;
        height: 100%;
    }
    .feature-switch-card:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
    }

    /* Form Controls */
    .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #productCardSettingsRoot .form-control,
    #productCardSettingsRoot .form-select {
        min-height: 33px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #productCardSettingsRoot .form-control:focus,
    #productCardSettingsRoot .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
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
        .settings-header-card { padding: 12px 14px !important; }
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
        .settings-panel-header { padding: 10px 12px; }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-2" id="productCardSettingsRoot">

    <!-- 1. Header & Quick Controls -->
    <div class="settings-header-card d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 header-top-wrap">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace text-uppercase" style="font-size: 9px;">
                    <i class="fas fa-th-large me-1"></i> STOREFRONT PRODUCT CARD
                </span>
                <span class="badge bg-light text-white-50 border border-secondary font-monospace" style="font-size: 9px;">
                    INTERACTIVE UI
                </span>
            </div>
            <h5 class="fw-bold mb-0 d-flex align-items-center gap-2" style="font-size: 17px;">
                <i class="fas fa-th-large text-primary"></i>
                <span>Product Card &amp; Review Settings</span>
            </h5>
            <small class="text-white-50 d-block mt-1" style="font-size: 11.5px; line-height: 1.35;">
                Configure catalog product cards display, price decimals, rating badges, quick view and review policies.
            </small>
        </div>

        <div class="top-action-btns d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary btn-compact text-white border-white-50 shadow-xs" onclick="toggleAllCardSections()">
                <i class="fas fa-arrows-up-down me-1"></i>
                <span id="expandAllCardBtnText">Collapse All</span>
            </button>
            <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold px-3" onclick="document.getElementById('productCardSettingsForm').submit()">
                <i class="fas fa-check-circle me-1"></i>
                <span>Save Settings</span>
            </button>
        </div>
    </div>

    <!-- 2. Status Summary KPI Row (Symmetric 3-Column Grid) -->
    @php
        $cardActiveCount = 0;
        $cardCheckKeys = ['card_show_price_decimal', 'card_show_review', 'card_show_wishlist', 'card_show_sale_badge', 'card_enable_quick_view', 'card_show_stockout_btn'];
        foreach($cardCheckKeys as $ck) {
            if (($settings[$ck] ?? '1') == '1') $cardActiveCount++;
        }
        $currentHover = $settings['card_hover_style'] ?? 'gallery_flip';
        $hoverLabel = match($currentHover) {
            'none' => 'No Animation',
            'zoom' => 'Smooth Scale Zoom',
            'slide_up' => 'Slide Up Second Image',
            default => 'Gallery Flip (Default)',
        };
        $detailReviewsOn = ($settings['detail_show_reviews'] ?? '1') == '1';
    @endphp

    <div class="row g-2 mb-3">
        <div class="col-12 col-md-4">
            <div class="status-kpi-pill">
                <div class="min-w-0">
                    <small class="text-muted d-block fw-semibold text-truncate" style="font-size: 10.5px;">Card Active Badges</small>
                    <strong class="text-primary d-block font-monospace bn-digit" style="font-size: 13px;">{{ $cardActiveCount }} of 6 Enabled</strong>
                </div>
                <div class="text-primary fs-5"><i class="fas fa-tags"></i></div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="status-kpi-pill">
                <div class="min-w-0">
                    <small class="text-muted d-block fw-semibold text-truncate" style="font-size: 10.5px;">Card Hover Animation</small>
                    <strong class="text-dark d-block text-truncate" style="font-size: 13px;">{{ $hoverLabel }}</strong>
                </div>
                <div class="text-warning fs-5"><i class="fas fa-wand-magic-sparkles"></i></div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="status-kpi-pill">
                <div class="min-w-0">
                    <small class="text-muted d-block fw-semibold text-truncate" style="font-size: 10.5px;">Product Reviews System</small>
                    <strong class="text-{{ $detailReviewsOn ? 'success' : 'secondary' }} d-block" style="font-size: 13px;">
                        {{ $detailReviewsOn ? 'Enabled on Store' : 'Reviews Hidden' }}
                    </strong>
                </div>
                <div class="text-{{ $detailReviewsOn ? 'success' : 'secondary' }} fs-5"><i class="fas fa-star"></i></div>
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

    <form action="{{ route('admin.theme.setting.product_card.update') }}" method="POST" id="productCardSettingsForm">
        @csrf

        <!-- Section 1: Product Card Controls (COLLAPSIBLE) -->
        <div class="settings-panel-card">
            <div class="settings-panel-header d-flex justify-content-between align-items-center">
                <div class="section-title-text">
                    <i class="fas fa-th-large text-primary"></i>
                    <span>Product Card Controls &amp; Badge Visibility</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace text-uppercase" style="font-size: 8.5px;">CARD BADGES</span>
                    <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseCardControls" aria-expanded="true" aria-controls="collapseCardControls" title="Toggle Section">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>
                </div>
            </div>

            <div class="collapse show" id="collapseCardControls">
                <div class="p-3 bg-light bg-opacity-25">
                    <div class="row g-2">
                        <!-- 1. Price Decimal Switch -->
                        <div class="col-12 col-md-6">
                            <div class="feature-switch-card">
                                <div class="pe-2">
                                    <label class="form-check-label d-block fw-bold text-dark" for="sw_price_decimal" style="font-size: 12px; cursor: pointer;">
                                        Show Decimal in Product Price
                                    </label>
                                    <small class="text-muted d-block font-monospace" style="font-size: 10.5px;">
                                        Display exact decimal amounts (e.g. ৳500.00 vs ৳500).
                                    </small>
                                </div>
                                <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                    <input class="form-check-input ms-0" type="checkbox" name="card_show_price_decimal" id="sw_price_decimal" value="1" {{ ($settings['card_show_price_decimal'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Reviews on Card Switch -->
                        <div class="col-12 col-md-6">
                            <div class="feature-switch-card">
                                <div class="pe-2">
                                    <label class="form-check-label d-block fw-bold text-dark" for="sw_card_review" style="font-size: 12px; cursor: pointer;">
                                        Show Star Reviews on Card
                                    </label>
                                    <small class="text-muted d-block font-monospace" style="font-size: 10.5px;">
                                        Renders customer star rating score and review count badge.
                                    </small>
                                </div>
                                <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                    <input class="form-check-input ms-0" type="checkbox" name="card_show_review" id="sw_card_review" value="1" {{ ($settings['card_show_review'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Wishlist Heart Switch -->
                        <div class="col-12 col-md-6">
                            <div class="feature-switch-card">
                                <div class="pe-2">
                                    <label class="form-check-label d-block fw-bold text-dark" for="sw_card_wishlist" style="font-size: 12px; cursor: pointer;">
                                        Wishlist Heart Button
                                    </label>
                                    <small class="text-muted d-block font-monospace" style="font-size: 10.5px;">
                                        Allows shoppers to bookmark products to wishlist collection.
                                    </small>
                                </div>
                                <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                    <input class="form-check-input ms-0" type="checkbox" name="card_show_wishlist" id="sw_card_wishlist" value="1" {{ ($settings['card_show_wishlist'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>

                        <!-- 4. Quick View Modal Switch -->
                        <div class="col-12 col-md-6">
                            <div class="feature-switch-card">
                                <div class="pe-2">
                                    <label class="form-check-label d-block fw-bold text-dark" for="sw_quick_view" style="font-size: 12px; cursor: pointer;">
                                        Quick Detail View Modal
                                    </label>
                                    <small class="text-muted d-block font-monospace" style="font-size: 10.5px;">
                                        Opens floating preview popup right from card without reload.
                                    </small>
                                </div>
                                <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                    <input class="form-check-input ms-0" type="checkbox" name="card_enable_quick_view" id="sw_quick_view" value="1" {{ ($settings['card_enable_quick_view'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>

                        <!-- 5. Stock Out Button Switch -->
                        <div class="col-12 col-md-6">
                            <div class="feature-switch-card">
                                <div class="pe-2">
                                    <label class="form-check-label d-block fw-bold text-dark" for="sw_stockout_btn" style="font-size: 12px; cursor: pointer;">
                                        Stock Out Button Status
                                    </label>
                                    <small class="text-muted d-block font-monospace" style="font-size: 10.5px;">
                                        Replaces active purchase buttons with disabled stock out label.
                                    </small>
                                </div>
                                <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                    <input class="form-check-input ms-0" type="checkbox" name="card_show_stockout_btn" id="sw_stockout_btn" value="1" {{ ($settings['card_show_stockout_btn'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>

                        <!-- 6. Sale Badge Toggle & Custom Text Input -->
                        <div class="col-12 col-md-6">
                            <div class="feature-switch-card flex-column align-items-stretch justify-content-between">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <label class="form-check-label d-block fw-bold text-dark" for="sw_sale_badge" style="font-size: 12px; cursor: pointer;">
                                            Show Discount Sale Badge
                                        </label>
                                        <small class="text-muted font-monospace" style="font-size: 10.5px;">
                                            Highlight promotional items.
                                        </small>
                                    </div>
                                    <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                        <input class="form-check-input ms-0" type="checkbox" name="card_show_sale_badge" id="sw_sale_badge" value="1" {{ ($settings['card_show_sale_badge'] ?? '1') == '1' ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label mb-1" style="font-size: 10.5px;">Custom Sale Badge Label Text</label>
                                    <input type="text" name="card_sale_badge_text" class="form-control" value="{{ $settings['card_sale_badge_text'] ?? 'Sale!' }}" placeholder="Sale!">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Card Hover Effect Animation (COLLAPSIBLE) -->
        <div class="settings-panel-card">
            <div class="settings-panel-header d-flex justify-content-between align-items-center">
                <div class="section-title-text">
                    <i class="fas fa-wand-magic-sparkles text-warning"></i>
                    <span>Product Card Hover Effect Dynamics</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle font-monospace text-uppercase" style="font-size: 8.5px;">ANIMATION</span>
                    <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseHoverAnimation" aria-expanded="true" aria-controls="collapseHoverAnimation" title="Toggle Section">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>
                </div>
            </div>

            <div class="collapse show" id="collapseHoverAnimation">
                <div class="p-3 bg-light bg-opacity-25">
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Hover Animation Preset</label>
                            <select name="card_hover_style" class="form-select font-monospace">
                                <option value="none" {{ ($settings['card_hover_style'] ?? '') == 'none' ? 'selected' : '' }}>No Animation (Static Image)</option>
                                <option value="zoom" {{ ($settings['card_hover_style'] ?? '') == 'zoom' ? 'selected' : '' }}>Smooth Scale Zoom (Subtle Magnify)</option>
                                <option value="gallery_flip" {{ ($settings['card_hover_style'] ?? 'gallery_flip') == 'gallery_flip' ? 'selected' : '' }}>Gallery Flip (Reveal 2nd Image)</option>
                                <option value="slide_up" {{ ($settings['card_hover_style'] ?? '') == 'slide_up' ? 'selected' : '' }}>Slide Up Secondary Photo</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-muted">Behavior Note</label>
                            <p class="text-muted small mb-0 font-monospace" style="font-size: 11px; line-height: 1.4;">
                                Secondary image flip automatically utilizes the product gallery thumbnail when hovered by shoppers.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Detail Page Reviews & Policy (COLLAPSIBLE) -->
        <div class="settings-panel-card">
            <div class="settings-panel-header d-flex justify-content-between align-items-center">
                <div class="section-title-text">
                    <i class="fas fa-star text-warning"></i>
                    <span>Product Detail Reviews &amp; Verification Policy</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace text-uppercase" style="font-size: 8.5px;">REVIEWS ENGINE</span>
                    <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseDetailReviews" aria-expanded="true" aria-controls="collapseDetailReviews" title="Toggle Section">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>
                </div>
            </div>

            <div class="collapse show" id="collapseDetailReviews">
                <div class="p-3 bg-light bg-opacity-25">
                    <div class="row g-2">
                        <!-- 1. Show Reviews on Detail Page -->
                        <div class="col-12 col-md-6">
                            <div class="feature-switch-card">
                                <div class="pe-2">
                                    <label class="form-check-label d-block fw-bold text-dark" for="sw_detail_reviews" style="font-size: 12px; cursor: pointer;">
                                        Show Reviews on Detail Page
                                    </label>
                                    <small class="text-muted d-block font-monospace" style="font-size: 10.5px;">
                                        Renders customer rating tab and testimonials on single product view.
                                    </small>
                                </div>
                                <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                    <input class="form-check-input ms-0" type="checkbox" name="detail_show_reviews" id="sw_detail_reviews" value="1" {{ ($settings['detail_show_reviews'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Verified Buyers Only Review -->
                        <div class="col-12 col-md-6">
                            <div class="feature-switch-card">
                                <div class="pe-2">
                                    <label class="form-check-label d-block fw-bold text-dark" for="sw_allow_review" style="font-size: 12px; cursor: pointer;">
                                        Verified Buyers Only Review Policy
                                    </label>
                                    <small class="text-muted d-block font-monospace" style="font-size: 10.5px;">
                                        Restricts ratings submission strictly to confirmed delivered purchasers.
                                    </small>
                                </div>
                                <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                    <input class="form-check-input ms-0" type="checkbox" name="detail_allow_review" id="sw_allow_review" value="1" {{ ($settings['detail_allow_review'] ?? '1') == '1' ? 'checked' : '' }}>
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
                <i class="fas fa-check-circle me-1"></i> Save Changes
            </button>
        </div>
    </form>
</div>

<!-- Modal: Custom Enterprise Notice Modal -->
<div class="modal fade pc-modal-scope" id="productCardNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 108500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="mb-2" id="productCardNoticeIconWrap">
                    <i class="fas fa-circle-check text-success fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="productCardNoticeTitle" style="font-size: 14px;">Notification</h6>
                <p class="text-muted small mb-3" id="productCardNoticeMessage" style="font-size: 12px; line-height: 1.45;"></p>
                <button type="button" class="btn btn-primary btn-compact px-4" data-bs-dismiss="modal">Acknowledge</button>
            </div>
        </div>
    </div>
</div>

<script>
// Universal Notice Modal
function showProductCardModalNotice(message, title = 'Notice', type = 'info') {
    const titleEl = document.getElementById('productCardNoticeTitle');
    const msgEl   = document.getElementById('productCardNoticeMessage');
    const iconEl  = document.getElementById('productCardNoticeIconWrap');

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

    const modalEl = document.getElementById('productCardNoticeModal');
    if (modalEl && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

// Toggle All Product Card Sections (Expand All / Collapse All)
let allCardSectionsExpanded = true;
function toggleAllCardSections() {
    const collapses = document.querySelectorAll('#productCardSettingsRoot .settings-panel-card .collapse');
    const toggleBtns = document.querySelectorAll('#productCardSettingsRoot .settings-panel-card .collapse-toggle-btn');
    const btnText = document.getElementById('expandAllCardBtnText');

    allCardSectionsExpanded = !allCardSectionsExpanded;

    collapses.forEach(col => {
        if (allCardSectionsExpanded) {
            bootstrap.Collapse.getOrCreateInstance(col, { toggle: false }).show();
        } else {
            bootstrap.Collapse.getOrCreateInstance(col, { toggle: false }).hide();
        }
    });

    toggleBtns.forEach(btn => {
        btn.setAttribute('aria-expanded', allCardSectionsExpanded ? 'true' : 'false');
    });

    if (btnText) {
        btnText.innerText = allCardSectionsExpanded ? 'Collapse All' : 'Expand All';
    }
}
</script>
@endsection