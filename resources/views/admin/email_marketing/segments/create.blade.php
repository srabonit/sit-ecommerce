@extends('layouts.admin')

@section('title', 'Create Audience Segment - Email Marketing')

@section('admin_content')

<style>
    /* ==========================================
       KAIADMIN CLASSIC — AUDIENCE SEGMENT
    ========================================== */

    #email-marketing-segment-create-root {
        --segment-primary: var(--bs-primary, #1572e8);
        --segment-secondary: #6b7280;
        --segment-success: #16a36a;
        --segment-warning: #f5a623;
        --segment-danger: #f04438;
        --segment-info: #31a6d8;

        --segment-bg: #f8f9fa;
        --segment-card: #ffffff;
        --segment-border: #ebeef2;
        --segment-heading: #344054;
        --segment-text: #667085;
        --segment-muted: #98a2b3;

        --segment-radius: 5px;

        color: var(--segment-text);
        font-size: 13px;
    }

    /* Page Header */

    #email-marketing-segment-create-root .segment-create-header {
        gap: 12px;
    }

    #email-marketing-segment-create-root .page-title {
        color: var(--segment-heading);
        font-size: clamp(17px, 2vw, 21px);
        font-weight: 700;
        letter-spacing: -.35px;
    }

    #email-marketing-segment-create-root .page-description {
        color: var(--segment-muted);
        font-size: 12px;
        line-height: 1.6;
    }

    /* Global Radius */

    #email-marketing-segment-create-root .btn,
    #email-marketing-segment-create-root .badge,
    #email-marketing-segment-create-root .alert,
    #email-marketing-segment-create-root .form-control,
    #email-marketing-segment-create-root .form-select,
    #email-marketing-segment-create-root .input-group-text,
    #email-marketing-segment-create-root .card,
    #email-marketing-segment-create-root .preset-chip,
    #email-marketing-segment-create-root .collapse-toggle-btn {
        border-radius: var(--segment-radius) !important;
    }

    #email-marketing-segment-create-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    #email-marketing-segment-create-root .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Typography */

    #email-marketing-segment-create-root .bn-digit,
    #email-marketing-segment-create-root .font-monospace {
        font-weight: 600;
    }

    /* Buttons */

    #email-marketing-segment-create-root .btn-compact {
        min-height: 36px;
        padding: 7px 13px;
        font-size: 12px;
        line-height: 1.3;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        white-space: nowrap;
        transition: all .18s ease;
    }

    #email-marketing-segment-create-root .btn {
        font-weight: 600;
        transition: all .18s ease;
    }

    #email-marketing-segment-create-root .btn:focus-visible {
        outline: 3px solid rgba(21, 114, 232, .16);
        outline-offset: 2px;
    }

    /* Cards */

    #email-marketing-segment-create-root .card {
        border: 1px solid var(--segment-border) !important;
        background: var(--segment-card);
        box-shadow: 0 1px 3px rgba(15, 23, 42, .035);
        margin-bottom: 16px;
        overflow: visible;
    }

    #email-marketing-segment-create-root .section-header-wrap {
        min-height: 49px;
        padding: 11px 16px;
        background: #fff;
        border-bottom: 1px solid var(--segment-border);
    }

    #email-marketing-segment-create-root .section-title {
        color: var(--segment-heading);
        font-size: 13px;
        font-weight: 700;
    }

    #email-marketing-segment-create-root .section-title i {
        font-size: 13px;
    }

    /* Collapse Buttons */

    #email-marketing-segment-create-root .collapse-toggle-btn {
        width: 29px;
        height: 29px;
        min-width: 29px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid var(--segment-border);
        color: #667085;
        transition: all .2s ease;
    }

    #email-marketing-segment-create-root .collapse-toggle-btn:hover {
        background: #f2f6fc;
        border-color: var(--segment-primary);
        color: var(--segment-primary);
    }

    #email-marketing-segment-create-root .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    #email-marketing-segment-create-root .collapse-icon {
        font-size: 11px;
        transition: transform .25s ease;
    }

    /* Form Controls */

    #email-marketing-segment-create-root .form-label {
        display: block;
        margin-bottom: 6px;
        color: #475467;
        font-size: 12px;
        font-weight: 600;
    }

    #email-marketing-segment-create-root .form-control,
    #email-marketing-segment-create-root .form-select {
        min-height: 39px;
        padding: 8px 11px;
        border: 1px solid #dfe3e8;
        background-color: #fff;
        color: #344054;
        font-size: 12.5px;
        box-shadow: none;
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    #email-marketing-segment-create-root textarea.form-control {
        min-height: 75px;
        resize: vertical;
        line-height: 1.6;
    }

    #email-marketing-segment-create-root .form-control::placeholder {
        color: #adb5c0;
        opacity: 1;
    }

    #email-marketing-segment-create-root .form-select {
        padding-right: 32px;
    }

    #email-marketing-segment-create-root .form-control:focus,
    #email-marketing-segment-create-root .form-select:focus {
        border-color: var(--segment-primary);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .10);
        outline: none;
    }

    #email-marketing-segment-create-root .input-group-text {
        min-height: 39px;
        padding: 7px 12px;
        border-color: #dfe3e8;
        background: #f8fafc;
        color: #667085;
        font-size: 11px;
        font-weight: 600;
    }

    /* Preset Chips */

    #email-marketing-segment-create-root .preset-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 26px;
        padding: 4px 9px;
        border: 1px solid #e4e7ec;
        background: #f8fafc;
        color: #667085;
        font-size: 10.5px;
        font-weight: 600;
        cursor: pointer;
        user-select: none;
        transition: all .18s ease;
    }

    #email-marketing-segment-create-root .preset-chip:hover {
        background: #edf4ff;
        border-color: #b7d2fa;
        color: var(--segment-primary);
    }

    #email-marketing-segment-create-root .preset-chip.text-danger {
        color: #d92d20 !important;
    }

    #email-marketing-segment-create-root .preset-chip.text-danger:hover {
        background: #fff1f0;
        border-color: #fecdca;
    }

    /* Filter Information */

    #email-marketing-segment-create-root .filter-description {
        padding: 10px 12px;
        border: 1px solid #e6efff;
        border-radius: 5px;
        background: #f5f9ff;
        color: #667085;
        font-size: 11.5px;
        line-height: 1.6;
    }

    /* Live Audience Widget */

    #email-marketing-segment-create-root .audience-estimation-card {
        color: #fff;
        border: 1px solid #263e5c !important;
        background: linear-gradient(145deg, #152b46 0%, #0c1e34 100%);
        overflow: hidden;
    }

    #email-marketing-segment-create-root .audience-estimation-card .section-header-wrap {
        background: transparent;
        border-bottom: 1px solid rgba(255,255,255,.12);
    }

    #email-marketing-segment-create-root .audience-estimation-card .section-title {
        color: #fff;
    }

    #email-marketing-segment-create-root .audience-estimation-card .collapse-toggle-btn {
        background: rgba(255,255,255,.07);
        border-color: rgba(255,255,255,.2);
        color: #fff;
    }

    #email-marketing-segment-create-root .audience-estimation-card .collapse-toggle-btn:hover {
        background: rgba(255,255,255,.15);
    }

    #email-marketing-segment-create-root .audience-counter-label {
        color: rgba(255,255,255,.75);
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .7px;
    }

    #email-marketing-segment-create-root #previewCountDisplay {
        display: inline-block;
        color: #ffca45;
        font-size: clamp(32px, 4vw, 42px);
        line-height: 1.2;
        font-weight: 800;
        letter-spacing: -1px;
        overflow-wrap: anywhere;
    }

    #email-marketing-segment-create-root .audience-counter-subtitle {
        color: rgba(255,255,255,.7);
        font-size: 11px;
    }

    #email-marketing-segment-create-root .audience-security-note {
        color: rgba(255,255,255,.75);
        font-size: 11px;
        line-height: 1.6;
    }

    /* Action Panel */

    #email-marketing-segment-create-root .action-panel {
        padding: 16px;
    }

    #email-marketing-segment-create-root .action-panel .btn {
        min-height: 42px;
        font-size: 12px;
    }

    #email-marketing-segment-create-root .action-panel .btn-primary {
        box-shadow: 0 2px 5px rgba(21, 114, 232, .15);
    }

    #email-marketing-segment-create-root .action-panel .btn-light {
        background: #fff;
    }

    /* Guidance Card */

    #email-marketing-segment-create-root .guidance-card {
        padding: 16px;
    }

    #email-marketing-segment-create-root .guidance-title {
        color: var(--segment-heading);
        font-size: 13px;
        font-weight: 700;
    }

    #email-marketing-segment-create-root .guidance-item {
        color: #667085;
        font-size: 11.5px;
        line-height: 1.65;
    }

    /* Validation */

    #email-marketing-segment-create-root .alert-danger {
        border: 1px solid #fecdca !important;
        background: #fff6f5;
        color: #344054;
    }

    #email-marketing-segment-create-root .alert-danger ul {
        font-size: 12px;
        line-height: 1.7;
    }

    /* Responsive */

    @media (min-width: 1200px) {
        #email-marketing-segment-create-root .segment-main-content {
            align-items: flex-start;
        }

        #email-marketing-segment-create-root .segment-sidebar {
            position: sticky;
            top: 15px;
        }
    }

    @media (max-width: 1199.98px) {
        #email-marketing-segment-create-root .segment-sidebar {
            position: static;
        }
    }

    @media (max-width: 767.98px) {
        #email-marketing-segment-create-root {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        #email-marketing-segment-create-root .page-title {
            font-size: 17px;
        }

        #email-marketing-segment-create-root .page-description {
            font-size: 11.5px;
        }

        #email-marketing-segment-create-root .header-actions-wrap {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            gap: 8px;
        }

        #email-marketing-segment-create-root .header-actions-wrap .btn {
            width: 100%;
            min-height: 39px;
            padding: 7px 5px;
            font-size: 11px;
        }

        #email-marketing-segment-create-root .card {
            margin-bottom: 12px;
        }

        #email-marketing-segment-create-root .section-header-wrap {
            padding: 10px 12px;
        }

        #email-marketing-segment-create-root .section-content {
            padding: 14px !important;
        }

        #email-marketing-segment-create-root .form-control,
        #email-marketing-segment-create-root .form-select {
            min-height: 41px;
        }

        #email-marketing-segment-create-root .preset-chip {
            min-height: 29px;
            padding: 5px 10px;
        }

        #email-marketing-segment-create-root .audience-estimation-card #previewCountDisplay {
            font-size: 36px;
        }

        #email-marketing-segment-create-root .guidance-card {
            padding: 14px;
        }
    }

    @media (max-width: 359.98px) {
        #email-marketing-segment-create-root .header-actions-wrap {
            grid-template-columns: 1fr;
        }

        #email-marketing-segment-create-root .section-title {
            font-size: 11.5px;
        }

        #email-marketing-segment-create-root .filter-description {
            font-size: 10.5px;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative"
     id="email-marketing-segment-create-root">

    {{-- 1. Page Header & Navigation --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4 segment-create-header">

        <div>
            <h5 class="fw-bold mb-1 d-flex align-items-center gap-2 page-title">
                <i class="fas fa-filter-circle-dollar text-primary"></i>
                <span>Create Audience Segment</span>
            </h5>

            <small class="d-block page-description">
                Define audience filters for targeted email campaigns.
            </small>
        </div>

        <div class="d-flex gap-2 header-actions-wrap">

            <a href="{{ route('admin.email_marketing.segments.index') }}"
               class="btn btn-sm btn-outline-secondary btn-compact shadow-none">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Segments</span>
            </a>

            <button type="button"
                    class="btn btn-sm btn-primary btn-compact shadow-none"
                    onclick="document.getElementById('segmentCreateForm').submit()">
                <i class="fas fa-save"></i>
                <span>Save Segment</span>
            </button>

        </div>
    </div>

    {{-- 2. Validation Errors --}}
    @if ($errors->any())

        <div class="alert alert-danger alert-dismissible fade show py-3 px-3 mb-3 shadow-none"
             role="alert">

            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="fas fa-triangle-exclamation text-danger fs-5"></i>
                <strong class="text-dark small">
                    Please fix the following errors:
                </strong>
            </div>

            <ul class="mb-0 ps-4 text-dark">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

            <button type="button"
                    class="btn-close shadow-none"
                    data-bs-dismiss="alert"
                    aria-label="Close"></button>
        </div>

    @endif

    <form action="{{ route('admin.email_marketing.segments.store') }}"
          method="POST"
          id="segmentCreateForm">

        @csrf

        <div class="row g-3 segment-main-content">

            {{-- LEFT COLUMN: SEGMENT DETAILS & FILTERS --}}
            <div class="col-12 col-xl-8">

                {{-- 1. Segment Information Card --}}
                <div class="card shadow-sm mb-3">

                    <div class="section-header-wrap d-flex justify-content-between align-items-center">

                        <span class="section-title">
                            <i class="fas fa-id-card text-primary me-2"></i>
                            1. Segment Information
                        </span>

                        <button type="button"
                                class="collapse-toggle-btn no-print"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapseSegmentInfo"
                                aria-expanded="true"
                                aria-controls="collapseSegmentInfo"
                                title="Toggle Section">

                            <i class="fas fa-chevron-down collapse-icon"></i>

                        </button>

                    </div>

                    <div class="collapse show" id="collapseSegmentInfo">

                        <div class="p-3 section-content">

                            <div class="row g-3">

                                <div class="col-12 col-md-8">

                                    <label class="form-label" for="segment_name">
                                        Segment Name <span class="text-danger">*</span>
                                    </label>

                                    <input type="text"
                                           name="name"
                                           id="segment_name"
                                           class="form-control"
                                           placeholder="Inactive High-Spenders, Fashion Lovers"
                                           value="{{ old('name') }}"
                                           required>

                                </div>

                                <div class="col-12 col-md-4">

                                    <label class="form-label" for="segment_type">
                                        Segment Type <span class="text-danger">*</span>
                                    </label>

                                    <select name="type"
                                            id="segment_type"
                                            class="form-select font-monospace shadow-none"
                                            required>

                                        <option value="dynamic" {{ old('type', 'dynamic') === 'dynamic' ? 'selected' : '' }}>
                                            Dynamic (Real-Time Auto-Updating)
                                        </option>

                                        <option value="snapshot" {{ old('type') === 'snapshot' ? 'selected' : '' }}>
                                            Snapshot (Static Point-in-Time)
                                        </option>

                                    </select>

                                </div>

                                <div class="col-12">

                                    <label class="form-label" for="segment_description">
                                        Description (Internal Note)
                                    </label>

                                    <textarea name="description"
                                              id="segment_description"
                                              rows="2"
                                              class="form-control shadow-none"
                                              placeholder="Briefly describe this audience...">{{ old('description') }}</textarea>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                {{-- 2. Audience Filter Conditions --}}
                <div class="card shadow-sm mb-3">

                    <div class="section-header-wrap d-flex justify-content-between align-items-center">

                        <div class="d-flex flex-wrap align-items-center gap-2">

                            <span class="section-title">
                                <i class="fas fa-sliders text-success me-2"></i>
                                2. Audience Filter Conditions
                            </span>

                            <span class="badge bg-light text-secondary border font-monospace px-2 py-1"
                                  style="font-size: 10px;">
                                Live Evaluated
                            </span>

                        </div>

                        <button type="button"
                                class="collapse-toggle-btn no-print"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapseFilterConditions"
                                aria-expanded="true"
                                aria-controls="collapseFilterConditions"
                                title="Toggle Section">

                            <i class="fas fa-chevron-down collapse-icon"></i>

                        </button>

                    </div>

                    <div class="collapse show" id="collapseFilterConditions">

                        <div class="p-3 section-content">

                            <div class="filter-description mb-3">

                                <i class="fas fa-info-circle text-primary me-1"></i>

                                Leave conditions empty if you do not want to restrict by that parameter.

                            </div>

                            <div class="row g-3">

                                {{-- Condition A: Category Purchase --}}
                                <div class="col-12 col-md-6">

                                    <label class="form-label" for="category_filter_select">
                                        Customers Who Bought From Category
                                    </label>

                                    <select name="filter_criteria[purchased_category_id]"
                                            id="category_filter_select"
                                            class="form-select filter-trigger font-monospace shadow-none">

                                        <option value="">-- Any Category --</option>

                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach

                                    </select>

                                </div>

                                {{-- Condition B: Minimum Orders --}}
                                <div class="col-12 col-md-6">

                                    <label class="form-label" for="min_orders_input">
                                        Minimum Completed Orders
                                    </label>

                                    <input type="number"
                                           name="filter_criteria[min_orders]"
                                           id="min_orders_input"
                                           class="form-control filter-trigger font-monospace bn-digit shadow-none"
                                           placeholder="2 for repeat buyers"
                                           min="0">

                                    <div class="d-flex flex-wrap gap-2 mt-2">

                                        <span class="preset-chip"
                                              onclick="setPresetValue('min_orders_input', 1)">
                                            1+ Order
                                        </span>

                                        <span class="preset-chip"
                                              onclick="setPresetValue('min_orders_input', 2)">
                                            2+ Repeat
                                        </span>

                                        <span class="preset-chip"
                                              onclick="setPresetValue('min_orders_input', 5)">
                                            5+ VIP
                                        </span>

                                        <span class="preset-chip text-danger"
                                              onclick="setPresetValue('min_orders_input', '')">
                                            Clear
                                        </span>

                                    </div>

                                </div>

                                {{-- Condition C: Inactivity Window --}}
                                <div class="col-12 col-md-6">

                                    <label class="form-label" for="inactive_days_input">
                                        Inactive Days (Win-Back)
                                    </label>

                                    <div class="input-group">

                                        <input type="number"
                                               name="filter_criteria[inactive_days]"
                                               id="inactive_days_input"
                                               class="form-control filter-trigger font-monospace bn-digit shadow-none"
                                               placeholder="30"
                                               min="0">

                                        <span class="input-group-text">
                                            Days
                                        </span>

                                    </div>

                                    <div class="d-flex flex-wrap gap-2 mt-2">

                                        <span class="preset-chip"
                                              onclick="setPresetValue('inactive_days_input', 30)">
                                            30d
                                        </span>

                                        <span class="preset-chip"
                                              onclick="setPresetValue('inactive_days_input', 60)">
                                            60d
                                        </span>

                                        <span class="preset-chip"
                                              onclick="setPresetValue('inactive_days_input', 90)">
                                            90d
                                        </span>

                                        <span class="preset-chip"
                                              onclick="setPresetValue('inactive_days_input', 180)">
                                            180d
                                        </span>

                                        <span class="preset-chip text-danger"
                                              onclick="setPresetValue('inactive_days_input', '')">
                                            Clear
                                        </span>

                                    </div>

                                </div>

                                {{-- Condition D: Abandoned Cart --}}
                                <div class="col-12 col-md-6">

                                    <label class="form-label" for="abandoned_cart_select">
                                        Cart Abandonment Activity
                                    </label>

                                    <select name="filter_criteria[has_abandoned_cart]"
                                            id="abandoned_cart_select"
                                            class="form-select filter-trigger font-monospace shadow-none">

                                        <option value="">-- No Cart Restriction --</option>

                                        <option value="1">
                                            Has unrecovered abandoned cart
                                        </option>

                                    </select>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            {{-- RIGHT COLUMN: LIVE AUDIENCE & ACTIONS --}}
            <div class="col-12 col-xl-4 segment-sidebar">

                {{-- Live Audience Estimation --}}
                <div class="card audience-estimation-card shadow-sm mb-3">

                    <div class="section-header-wrap d-flex justify-content-between align-items-center">

                        <span class="section-title">
                            <i class="fas fa-users-viewfinder text-warning me-2"></i>
                            Live Audience Reach
                        </span>

                        <button type="button"
                                class="collapse-toggle-btn no-print"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapseLiveEstimation"
                                aria-expanded="true"
                                aria-controls="collapseLiveEstimation"
                                title="Toggle Estimation">

                            <i class="fas fa-chevron-down collapse-icon"></i>

                        </button>

                    </div>

                    <div class="collapse show" id="collapseLiveEstimation">

                        <div class="p-4 text-center">

                            <div class="audience-counter-label text-uppercase font-monospace">
                                Estimated Matching Subscribers
                            </div>

                            <div class="my-3">

                                <span id="previewCountDisplay"
                                      class="fw-bold font-monospace bn-digit">
                                    ...
                                </span>

                                <span class="audience-counter-subtitle d-block mt-1 font-monospace">
                                    Consented Profiles
                                </span>

                            </div>

                            <div class="pt-3 border-top border-white border-opacity-10">

                                <p class="audience-security-note mb-0">

                                    <i class="fas fa-shield-halved me-1 text-success"></i>

                                    Only legally consented active subscribers are calculated.

                                </p>

                            </div>

                        </div>

                    </div>

                </div>

                {{-- Action Panel --}}
                <div class="card shadow-sm mb-3">

                    <div class="action-panel">

                        <button type="submit"
                                class="btn btn-primary w-100 fw-bold shadow-none mb-2">
                            <i class="fas fa-save me-1"></i>
                            Save Audience Segment
                        </button>

                        <a href="{{ route('admin.email_marketing.segments.index') }}"
                           class="btn btn-light border w-100 shadow-none text-secondary">
                            <i class="fas fa-times me-1"></i>
                            Cancel
                        </a>

                    </div>

                </div>

                {{-- Audience Targeting Guidance --}}
                <div class="card guidance-card shadow-sm">

                    <h6 class="guidance-title mb-3 d-flex align-items-center gap-2">

                        <i class="fas fa-lightbulb text-warning"></i>

                        <span>Audience Targeting Tips</span>

                    </h6>

                    <div class="guidance-item mb-3">

                        <div class="fw-semibold text-dark mb-1">
                            <i class="fas fa-sync-alt text-primary me-1"></i>
                            Dynamic Segments
                        </div>

                        Best for automated recurring broadcasts and daily win-back campaigns.

                    </div>

                    <div class="guidance-item">

                        <div class="fw-semibold text-dark mb-1">
                            <i class="fas fa-camera text-success me-1"></i>
                            Snapshots
                        </div>

                        Best for one-time seasonal campaigns where the audience list must remain frozen.

                    </div>

                </div>

            </div>

        </div>

    </form>

</div>

{{-- Real-Time Live Audience Evaluation Script --}}
<script>
    function setPresetValue(inputId, value) {
        const input = document.getElementById(inputId);
        if (input) {
            input.value = value;
            input.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        const triggers = document.querySelectorAll('.filter-trigger');
        const countDisplay = document.getElementById('previewCountDisplay');
        const form = document.getElementById('segmentCreateForm');
        let debounceTimer = null;

        function updateAudiencePreview() {
            countDisplay.innerHTML = '<span class="spinner-border spinner-border-sm text-warning" role="status"></span>';

            const formData = new FormData(form);
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

            fetch("{{ route('admin.email_marketing.segments.preview_count') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    "Accept": "application/json"
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.success) {
                    countDisplay.innerText = Number(data.count).toLocaleString();
                } else {
                    countDisplay.innerText = "0";
                }
            })
            .catch(err => {
                console.error("Preview count error:", err);
                countDisplay.innerText = "0";
            });
        }

        // Trigger on input changes with 300ms debounce
        triggers.forEach(el => {
            el.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(updateAudiencePreview, 300);
            });
            el.addEventListener('change', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(updateAudiencePreview, 150);
            });
        });

        // Initial Calculation on Page Load
        updateAudiencePreview();
    });
</script>

@endsection