@extends('layouts.admin')

@section('title', 'Create Audience Segment - Email Marketing')

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #email-marketing-segment-create-root .btn,
    #email-marketing-segment-create-root .btn-sm,
    #email-marketing-segment-create-root button,
    #email-marketing-segment-create-root .badge,
    #email-marketing-segment-create-root .alert,
    #email-marketing-segment-create-root .form-control,
    #email-marketing-segment-create-root .form-select,
    #email-marketing-segment-create-root .input-group-text,
    #email-marketing-segment-create-root .card,
    #email-marketing-segment-create-root .metric-card,
    #email-marketing-segment-create-root .preset-chip,
    #email-marketing-segment-create-root .collapse-toggle-btn {
        border-radius: 5px !important;
    }

    #email-marketing-segment-create-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #email-marketing-segment-create-root .input-group > .form-control {
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
        font-size: 11px !important;
        padding: 5px 12px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    /* Enterprise Surface Cards */
    #email-marketing-segment-create-root .card {
        border: 1px solid #e2e8f0;
        background-color: #ffffff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 16px;
    }
    .section-header-wrap {
        padding: 10px 14px;
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
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
    #email-marketing-segment-create-root .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #email-marketing-segment-create-root .form-control,
    #email-marketing-segment-create-root .form-select {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #email-marketing-segment-create-root .form-control:focus,
    #email-marketing-segment-create-root .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Quick Preset Filter Chips */
    .preset-chip {
        display: inline-flex;
        align-items: center;
        padding: 2px 7px;
        font-size: 10px;
        font-weight: 600;
        color: #475569;
        background-color: #f1f5f9;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        user-select: none;
        transition: all 0.15s ease;
    }
    .preset-chip:hover {
        background-color: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
    }

    /* Live Audience Estimation Counter Card */
    .audience-estimation-card {
        background: linear-gradient(135deg, #03172e 0%, #0c2d52 100%);
        color: #ffffff;
        border: 1px solid #1e293b;
    }

    /* Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .segment-create-header h5 {
            font-size: 16px !important;
        }
        .header-actions-wrap {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .header-actions-wrap .btn {
            width: 100%;
            padding: 8px 6px !important;
            font-size: 11px !important;
            justify-content: center;
        }
        #email-marketing-segment-create-root .card {
            margin-bottom: 12px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-segment-create-root">

    {{-- 1. Header & Navigation (Original Clean Light Theme) --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 segment-create-header">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-filter-circle-dollar text-primary"></i>
                <span>Create Audience Segment</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Build automated audience filters to target high-converting marketing campaigns.
            </small>
        </div>

        <div class="d-flex gap-2 header-actions-wrap">
            <a href="{{ route('admin.email_marketing.segments.index') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-arrow-left me-1"></i> <span>Back to Segments</span>
            </a>
            <button type="button" class="btn btn-sm btn-primary btn-compact shadow-xs fw-bold" onclick="document.getElementById('segmentCreateForm').submit()">
                <i class="fas fa-save me-1"></i> <span>Save Segment</span>
            </button>
        </div>
    </div>

    {{-- 2. Validation Error Alerts --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 small border-0 shadow-xs mb-3" role="alert">
            <div class="d-flex align-items-center gap-1 mb-1">
                <i class="fas fa-triangle-exclamation text-danger fs-5"></i>
                <strong class="text-dark">Please fix the following errors:</strong>
            </div>
            <ul class="mb-0 ps-3 text-dark">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('admin.email_marketing.segments.store') }}" method="POST" id="segmentCreateForm">
        @csrf

        <div class="row g-3">
            {{-- Left Column: Segment Details & Filter Rules --}}
            <div class="col-12 col-xl-8">

                {{-- 1. Basic Metadata Card (Collapsible) --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
                        <span class="fw-bold text-dark small" style="font-size: 12.5px;">
                            <i class="fas fa-id-card text-primary me-1"></i> 1. Segment Information
                        </span>
                        <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseSegmentInfo" aria-expanded="true" aria-controls="collapseSegmentInfo" title="Toggle Section">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>
                    <div class="collapse show" id="collapseSegmentInfo">
                        <div class="p-3">
                            <div class="row g-2">
                                <div class="col-12 col-md-8">
                                    <label class="form-label" for="segment_name">
                                        Segment Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="name" id="segment_name" class="form-control" placeholder="Inactive High-Spenders, Fashion Lovers" value="{{ old('name') }}" required>
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label" for="segment_type">
                                        Segment Type <span class="text-danger">*</span>
                                    </label>
                                    <select name="type" id="segment_type" class="form-select font-monospace shadow-none" required>
                                        <option value="dynamic" {{ old('type', 'dynamic') === 'dynamic' ? 'selected' : '' }}>Dynamic (Real-Time Auto-Updating)</option>
                                        <option value="snapshot" {{ old('type') === 'snapshot' ? 'selected' : '' }}>Snapshot (Static Point-in-Time)</option>
                                    </select>
                                </div>

                                <div class="col-12 mt-1">
                                    <label class="form-label" for="segment_description">Description (Internal Note)</label>
                                    <textarea name="description" id="segment_description" rows="2" class="form-control shadow-none" placeholder="Briefly describe what this audience represents...">{{ old('description') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. Audience Filter Rules Card (Collapsible) --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
                        <div>
                            <span class="fw-bold text-dark small" style="font-size: 12.5px;">
                                <i class="fas fa-sliders text-success me-1"></i> 2. Audience Filter Conditions
                            </span>
                            <span class="badge bg-light text-secondary border font-monospace ms-1 px-2 py-0" style="font-size: 9.5px;">
                                Live Evaluated
                            </span>
                        </div>
                        <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseFilterConditions" aria-expanded="true" aria-controls="collapseFilterConditions" title="Toggle Section">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>
                    <div class="collapse show" id="collapseFilterConditions">
                        <div class="p-3">
                            <p class="text-muted small mb-3" style="font-size: 11px;">
                                <i class="fas fa-info-circle text-primary me-1"></i> Leave conditions empty if you do not want to restrict by that specific parameter.
                            </p>

                            <div class="row g-2">
                                {{-- Condition A: Category Purchase --}}
                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="category_filter_select">Customers who bought from Category</label>
                                    <select name="filter_criteria[purchased_category_id]" id="category_filter_select" class="form-select filter-trigger font-monospace shadow-none">
                                        <option value="">-- Any Category --</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Condition B: Minimum Orders Placed --}}
                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="min_orders_input">Minimum Completed Orders</label>
                                    <input type="number" name="filter_criteria[min_orders]" id="min_orders_input" class="form-control filter-trigger font-monospace bn-digit shadow-none" placeholder="2 for repeat buyers" min="0">
                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        <span class="preset-chip" onclick="setPresetValue('min_orders_input', 1)">1+ Order</span>
                                        <span class="preset-chip" onclick="setPresetValue('min_orders_input', 2)">2+ Repeat</span>
                                        <span class="preset-chip" onclick="setPresetValue('min_orders_input', 5)">5+ VIP</span>
                                        <span class="preset-chip text-danger" onclick="setPresetValue('min_orders_input', '')">Clear</span>
                                    </div>
                                </div>

                                {{-- Condition C: Inactivity Window --}}
                                <div class="col-12 col-md-6 mt-2">
                                    <label class="form-label" for="inactive_days_input">Inactive Days (Win-Back)</label>
                                    <div class="input-group">
                                        <input type="number" name="filter_criteria[inactive_days]" id="inactive_days_input" class="form-control filter-trigger font-monospace bn-digit shadow-none" placeholder="30" min="0">
                                        <span class="input-group-text bg-light text-muted font-monospace" style="font-size: 11px;">Days</span>
                                    </div>
                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        <span class="preset-chip" onclick="setPresetValue('inactive_days_input', 30)">30d</span>
                                        <span class="preset-chip" onclick="setPresetValue('inactive_days_input', 60)">60d</span>
                                        <span class="preset-chip" onclick="setPresetValue('inactive_days_input', 90)">90d</span>
                                        <span class="preset-chip" onclick="setPresetValue('inactive_days_input', 180)">180d</span>
                                        <span class="preset-chip text-danger" onclick="setPresetValue('inactive_days_input', '')">Clear</span>
                                    </div>
                                </div>

                                {{-- Condition D: Abandoned Cart Filter --}}
                                <div class="col-12 col-md-6 mt-2">
                                    <label class="form-label" for="abandoned_cart_select">Cart Abandonment Activity</label>
                                    <select name="filter_criteria[has_abandoned_cart]" id="abandoned_cart_select" class="form-select filter-trigger font-monospace shadow-none">
                                        <option value="">-- No Cart Restriction --</option>
                                        <option value="1">Has unrecovered abandoned cart</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Right Column: Live Audience Calculation & Submission --}}
            <div class="col-12 col-xl-4">

                {{-- Live Audience Counter Widget (Collapsible) --}}
                <div class="card audience-estimation-card border-0 shadow-sm mb-3">
                    <div class="section-header-wrap d-flex justify-content-between align-items-center bg-transparent border-bottom border-white border-opacity-10">
                        <span class="fw-bold text-white small" style="font-size: 12px;">
                            <i class="fas fa-users-viewfinder text-warning me-1"></i> Live Audience Reach
                        </span>
                        <button type="button" class="collapse-toggle-btn shadow-xs no-print text-white bg-transparent border-white border-opacity-25" data-bs-toggle="collapse" data-bs-target="#collapseLiveEstimation" aria-expanded="true" aria-controls="collapseLiveEstimation" title="Toggle Estimation">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>
                    <div class="collapse show" id="collapseLiveEstimation">
                        <div class="p-3 text-center">
                            <span class="text-white text-opacity-75 small text-uppercase fw-semibold font-monospace" style="font-size: 10px;">
                                Estimated Matching Subscribers
                            </span>
                            <div class="my-2">
                                <span id="previewCountDisplay" class="display-6 fw-bold text-warning bn-digit font-monospace">...</span>
                                <span class="text-white text-opacity-75 d-block small font-monospace" style="font-size: 11px;">Consented Profiles</span>
                            </div>
                            <p class="text-white text-opacity-75 small mb-0 pt-2 border-top border-white border-opacity-10" style="font-size: 10.5px;">
                                <i class="fas fa-shield-halved me-1 text-success"></i> Only legally consented active subscribers are calculated.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Action Panel Card --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="p-3">
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-xs btn-compact mb-2" style="font-size: 12px !important;">
                            <i class="fas fa-save me-1"></i> Save Audience Segment
                        </button>
                        <a href="{{ route('admin.email_marketing.segments.index') }}" class="btn btn-light border w-100 py-2 btn-compact shadow-xs text-secondary">
                            Cancel
                        </a>
                    </div>
                </div>

                {{-- Segmentation Strategy Guidance Card --}}
                <div class="card p-3 shadow-xs">
                    <h6 class="fw-bold text-dark mb-1 small d-flex align-items-center gap-1">
                        <i class="fas fa-lightbulb text-warning"></i> <span>Audience Targeting Tips</span>
                    </h6>
                    <p class="text-muted small mb-1" style="font-size: 11px; line-height: 1.4;">
                        &bull; <strong>Dynamic Segments:</strong> Best for automated recurring broadcasts (daily win-back campaigns).
                    </p>
                    <p class="text-muted small mb-0" style="font-size: 11px; line-height: 1.4;">
                        &bull; <strong>Snapshots:</strong> Best for one-time seasonal blast lists where the list must remain frozen.
                    </p>
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