@extends('layouts.dashboard')

@php
    // সেন্ট্রাল গ্লোবাল সেটিংস ক্যাশ রেজলভার
    static $trackSettingsCache = null;
    if ($trackSettingsCache === null) {
        $trackSettingsCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
            return \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $trackSettings = $trackSettingsCache ?? [];

    // ডায়নামিক থিম কালার ভ্যারিয়েবল (Existing Default Colors)
    $trackCardBg        = $trackSettings['color_auth_card_bg'] ?? '#ffffff';
    $trackCardBorder    = $trackSettings['color_auth_card_border'] ?? '#e2e8f0';
    $trackBoxBg         = $trackSettings['color_site_bg'] ?? '#f8fafc';
    $trackTitleColor    = $trackSettings['color_page_title'] ?? '#0f172a';
    $trackTextColor     = $trackSettings['color_auth_text'] ?? '#64748b';
    $trackInputBg       = $trackSettings['color_auth_input_bg'] ?? '#ffffff';
    $trackInputBorder   = $trackSettings['color_auth_input_border'] ?? '#cbd5e1';
    $trackInputFocus    = $trackSettings['color_auth_input_focus_border'] ?? ($trackSettings['color_common_btn_bg'] ?? '#2563eb');
    
    $trackPrimaryColor  = $trackSettings['color_common_btn_bg'] ?? ($trackSettings['color_hdr_menu_hover_text'] ?? '#2563eb');
    $trackPrimaryHover  = $trackSettings['color_auth_btn_hover'] ?? '#1d4ed8';
@endphp

@section('title', 'Track Order - ' . config('app.name', 'Shop Now BD'))

@section('dashboard_content')
<style>
    :root {
        --track-card-bg: {{ $trackCardBg }};
        --track-card-border: {{ $trackCardBorder }};
        --track-box-bg: {{ $trackBoxBg }};
        --track-title: {{ $trackTitleColor }};
        --track-text: {{ $trackTextColor }};
        --track-input-bg: {{ $trackInputBg }};
        --track-input-border: {{ $trackInputBorder }};
        --track-input-focus: {{ $trackInputFocus }};
        --track-primary: {{ $trackPrimaryColor }};
        --track-primary-hover: {{ $trackPrimaryHover }};
    }

    /* Universal Strict Link Text Decoration */
    .track-package-root a,
    .track-package-root a:hover {
        text-decoration: none !important;
    }

    /* Main Container Card */
    .track-main-card {
        background-color: var(--track-card-bg) !important;
        border: 1px solid var(--track-card-border) !important;
        border-radius: 16px !important;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04), 0 1px 3px rgba(15, 23, 42, 0.02) !important;
        padding: 24px;
        transition: all 0.25s ease;
    }

    .track-header-title {
        color: var(--track-title) !important;
        font-weight: 700;
        font-size: 1.2rem;
        line-height: 1.35;
    }

    .track-header-subtitle {
        color: var(--track-text) !important;
        font-size: 12.5px;
        line-height: 1.45;
    }

    /* Track Search Form */
    .track-input-field {
        background-color: var(--track-input-bg) !important;
        border: 1px solid var(--track-input-border) !important;
        color: var(--track-title) !important;
        font-size: 13.5px;
        border-radius: 50px !important;
        padding: 10px 20px;
        transition: all 0.2s ease;
    }

    .track-input-field:focus {
        border-color: var(--track-input-focus) !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
        background-color: #ffffff !important;
        outline: none;
    }

    .track-input-field::placeholder {
        color: #94a3b8 !important;
        font-size: 13px;
        opacity: 0.9;
    }

    .btn-track-submit {
        background-color: var(--track-primary) !important;
        border-color: var(--track-primary) !important;
        color: #ffffff !important;
        border-radius: 50px !important;
        padding: 10px 24px !important;
        font-size: 13.5px !important;
        font-weight: 700 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 3px 10px rgba(37, 99, 235, 0.25) !important;
        min-height: 42px;
    }

    .btn-track-submit:hover {
        background-color: var(--track-primary-hover) !important;
        border-color: var(--track-primary-hover) !important;
        color: #ffffff !important;
        transform: translateY(-1px);
        box-shadow: 0 5px 14px rgba(37, 99, 235, 0.35) !important;
    }

    .btn-track-submit:active {
        transform: translateY(0);
    }

    /* Result Panel Box */
    .track-result-box {
        background-color: var(--track-box-bg) !important;
        border: 1px solid var(--track-card-border) !important;
        border-radius: 16px;
        padding: 20px;
    }

    /* 5-Step Stepper Component */
    .track-stepper-container {
        position: relative;
        padding: 24px 5px 15px;
    }

    .track-progress-line {
        position: absolute;
        top: 48px;
        left: 8%;
        right: 8%;
        height: 4px;
        background: #e2e8f0;
        z-index: 1;
        border-radius: 4px;
    }

    .track-progress-fill {
        height: 100%;
        background: var(--track-primary, #2563eb) !important;
        transition: width 0.4s ease;
        border-radius: 4px;
    }

    .step-circle {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        margin: 0 auto 8px;
        position: relative;
        z-index: 2;
        transition: all 0.3s ease;
        border: 3px solid #ffffff;
    }

    .step-circle.active {
        background-color: var(--track-primary, #2563eb) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
    }

    .step-circle.completed {
        background-color: #10b981 !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .step-circle.inactive {
        background-color: #e2e8f0 !important;
        color: #94a3b8 !important;
    }

    .step-title-text {
        font-size: 12px;
        font-weight: 700;
        line-height: 1.3;
    }

    .step-sub-text {
        font-size: 11px;
        color: var(--track-text);
        line-height: 1.25;
    }

    /* Courier Box */
    .courier-info-card {
        background-color: #ffffff !important;
        border: 1px solid var(--track-card-border) !important;
        border-radius: 12px;
        padding: 14px 16px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
    }

    .btn-view-order-link {
        border: 1px solid var(--track-primary) !important;
        color: var(--track-primary) !important;
        background: #ffffff !important;
        border-radius: 50px !important;
        padding: 6px 16px !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.2s ease;
    }

    .btn-view-order-link:hover {
        background: var(--track-primary) !important;
        color: #ffffff !important;
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(37, 99, 235, 0.25) !important;
    }

    /* Bengali digit protection */
    .bn-digit {
        font-weight: 700 !important;
    }

    /* Small Screen Optimization (iPhone SE, 320px–575px) */
    @media (max-width: 575.98px) {
        .track-main-card {
            padding: 16px 14px !important;
            border-radius: 14px !important;
        }

        .track-result-box {
            padding: 16px 12px !important;
            border-radius: 14px !important;
        }

        .track-header-title {
            font-size: 1.1rem !important;
        }

        .track-input-field {
            padding: 8px 16px;
            font-size: 13px;
        }

        .btn-track-submit {
            width: 100% !important;
            min-height: 40px;
        }

        /* Responsive 5-Step Stepper on 320px–375px screens */
        .track-stepper-container {
            padding: 14px 0 8px;
        }

        .track-progress-line {
            top: 34px;
            left: 6%;
            right: 6%;
            height: 3px;
        }

        .step-circle {
            width: 36px;
            height: 36px;
            font-size: 12px;
            margin-bottom: 5px;
            border-width: 2px;
        }

        .step-title-text {
            font-size: 10px !important;
            line-height: 1.15;
            display: block;
        }

        .step-sub-text {
            font-size: 9px !important;
        }
    }
</style>

<div class="track-package-root">
    <div class="track-main-card mb-4">
        
        {{-- Header Bar --}}
        <h5 class="track-header-title mb-1">
            <i class="fas fa-shipping-fast me-2" style="color: var(--track-primary);"></i> Track Your Package
        </h5>
        <p class="track-header-subtitle mb-4">Enter your order Invoice Number to get live dispatch and courier updates</p>

        {{-- Tracking Query Form --}}
        <form action="{{ route('user.track.order') }}" method="GET" class="mb-4">
            <div class="row g-2">
                <div class="col-12 col-md-9">
                    <input type="text" 
                           name="invoice_no" 
                           class="form-control track-input-field bn-digit" 
                           value="{{ request('invoice_no') }}" 
                           placeholder="Enter invoice number (INV-20260904-QQFV)" 
                           required>
                </div>
                <div class="col-12 col-md-3">
                    <button type="submit" class="btn btn-track-submit w-100">
                        <i class="fas fa-search"></i> <span>Track</span>
                    </button>
                </div>
            </div>
        </form>

        {{-- Tracking Result Panel --}}
        @if(isset($trackedOrder) && $trackedOrder)
            @php
                $rawStatus = strtolower(trim($trackedOrder->order_status ?? 'pending'));

                // 5 major milestone steps: 1=Received/Pending, 2=Confirmed, 3=Shipped, 4=Out for Delivery, 5=Delivered
                $currentStep = match($rawStatus) {
                    'confirmed', 'processing' => 2,
                    'shipped', 'dispatched', 'on the way' => 3,
                    'attempt_to_deliver', 'out for delivery' => 4,
                    'delivered', 'completed', 'success' => 5,
                    default => 1, // 'pending', 'received'
                };

                $isCancelled = in_array($rawStatus, ['cancelled', 'canceled', 'failed', 'returned']);

                // Progress bar width calculations for 5 steps (0%, 25%, 50%, 75%, 100%)
                $fillPercentages = [
                    1 => '0%',
                    2 => '25%',
                    3 => '50%',
                    4 => '75%',
                    5 => '100%'
                ];
                $fillWidth = $isCancelled ? '0%' : ($fillPercentages[$currentStep] ?? '0%');

                $statusBadgeClass = match($rawStatus) {
                    'delivered', 'completed', 'success' => 'bg-success text-white',
                    'attempt_to_deliver', 'out for delivery' => 'bg-info text-dark',
                    'shipped', 'dispatched' => 'bg-warning text-dark',
                    'confirmed', 'processing' => 'bg-primary text-white',
                    'cancelled', 'canceled', 'failed', 'returned' => 'bg-danger text-white',
                    default => 'bg-secondary text-white',
                };
            @endphp

            <div class="track-result-box mt-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4 border-bottom pb-3" style="border-color: var(--track-card-border) !important;">
                    <div>
                        <h6 class="fw-bold mb-1" style="color: var(--track-title);">
                            Invoice #{{ $trackedOrder->invoice_no }}
                        </h6>
                        <small style="color: var(--track-text);">
                            @if($currentStep >= 5)
                                <i class="fas fa-check-circle text-success me-1"></i> Package delivered successfully.
                            @elseif($isCancelled)
                                <i class="fas fa-times-circle text-danger me-1"></i> This order has been cancelled or returned.
                            @elseif($currentStep === 4)
                                <i class="fas fa-motorcycle me-1" style="color: var(--track-primary);"></i> Rider is attempting to deliver your package today!
                            @else
                                Estimated Delivery: 2-4 business days
                            @endif
                        </small>
                    </div>
                    <div>
                        <span class="badge {{ $statusBadgeClass }} text-uppercase px-3 py-2 rounded-pill font-monospace" style="font-size: 11px; letter-spacing: 0.5px;">
                            CURRENT STATUS: {{ str_replace('_', ' ', $trackedOrder->order_status ?? 'Pending') }}
                        </span>
                    </div>
                </div>

                @if($isCancelled)
                    <div class="alert alert-danger rounded-4 py-3 mb-0 text-center">
                        <i class="fas fa-exclamation-triangle fa-lg me-2"></i> 
                        This order is currently <strong>{{ strtoupper($rawStatus) }}</strong>. Please reach out via 
                        <a href="{{ route('user.support') }}" class="alert-link text-decoration-underline">Customer Support</a> for questions or assistance.
                    </div>
                @else
                    <!-- 5-Step Visual Stepper -->
                    <div class="track-stepper-container">
                        <div class="track-progress-line">
                            <div class="track-progress-fill" style="width: {{ $fillWidth }};"></div>
                        </div>

                        <div class="row text-center position-relative g-0">
                            <!-- Step 1: Received -->
                            <div class="col" style="flex: 1 0 0%;">
                                <div class="step-circle {{ $currentStep > 1 ? 'completed' : 'active' }}">
                                    <i class="fas {{ $currentStep > 1 ? 'fa-check' : 'fa-receipt' }}"></i>
                                </div>
                                <span class="step-title-text {{ $currentStep >= 1 ? 'fw-bold' : '' }}" style="color: {{ $currentStep >= 1 ? 'var(--track-primary)' : 'var(--track-text)' }};">Received</span>
                                <span class="d-block step-sub-text bn-digit mt-0.5">{{ date('d M, Y', strtotime($trackedOrder->created_at)) }}</span>
                            </div>

                            <!-- Step 2: Confirmed -->
                            <div class="col" style="flex: 1 0 0%;">
                                <div class="step-circle {{ $currentStep > 2 ? 'completed' : ($currentStep === 2 ? 'active' : 'inactive') }}">
                                    <i class="fas {{ $currentStep > 2 ? 'fa-check' : 'fa-clipboard-check' }}"></i>
                                </div>
                                <span class="step-title-text {{ $currentStep >= 2 ? 'fw-bold' : '' }}" style="color: {{ $currentStep >= 2 ? 'var(--track-primary)' : 'var(--track-text)' }};">Confirmed</span>
                                <span class="d-block step-sub-text mt-0.5">Verified</span>
                            </div>

                            <!-- Step 3: Shipped -->
                            <div class="col" style="flex: 1 0 0%;">
                                <div class="step-circle {{ $currentStep > 3 ? 'completed' : ($currentStep === 3 ? 'active' : 'inactive') }}">
                                    <i class="fas {{ $currentStep > 3 ? 'fa-check' : 'fa-truck' }}"></i>
                                </div>
                                <span class="step-title-text {{ $currentStep >= 3 ? 'fw-bold' : '' }}" style="color: {{ $currentStep >= 3 ? 'var(--track-primary)' : 'var(--track-text)' }};">Shipped</span>
                                <span class="d-block step-sub-text mt-0.5">On The Way</span>
                            </div>

                            <!-- Step 4: Attempt to Deliver -->
                            <div class="col" style="flex: 1 0 0%;">
                                <div class="step-circle {{ $currentStep > 4 ? 'completed' : ($currentStep === 4 ? 'active' : 'inactive') }}">
                                    <i class="fas {{ $currentStep > 4 ? 'fa-check' : 'fa-motorcycle' }}"></i>
                                </div>
                                <span class="step-title-text {{ $currentStep >= 4 ? 'fw-bold' : '' }}" style="color: {{ $currentStep >= 4 ? 'var(--track-primary)' : 'var(--track-text)' }};">Out for Delivery</span>
                                <span class="d-block step-sub-text mt-0.5">With Rider</span>
                            </div>

                            <!-- Step 5: Delivered -->
                            <div class="col" style="flex: 1 0 0%;">
                                <div class="step-circle {{ $currentStep >= 5 ? 'completed' : 'inactive' }}">
                                    <i class="fas {{ $currentStep >= 5 ? 'fa-check-double' : 'fa-box-open' }}"></i>
                                </div>
                                <span class="step-title-text {{ $currentStep >= 5 ? 'text-success fw-bold' : '' }}" style="color: {{ $currentStep >= 5 ? '#10b981' : 'var(--track-text)' }};">Delivered</span>
                                <span class="d-block step-sub-text mt-0.5">Completed</span>
                            </div>
                        </div>
                    </div>

                    <!-- Courier Information Box (If Assigned) -->
                    @if(!empty($trackedOrder->courier_name) || !empty($trackedOrder->courier_tracking_id))
                        <div class="courier-info-card mt-3 mt-md-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-warning-subtle text-warning-emphasis d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                                    <i class="fas fa-dolly fa-lg"></i>
                                </div>
                                <div class="min-w-0">
                                    <h6 class="fw-bold mb-0 text-truncate" style="color: var(--track-title);">
                                        Delivery Partner: {{ $trackedOrder->courier_name ?? 'Steadfast Courier' }}
                                    </h6>
                                    <p class="small mb-0 text-truncate" style="color: var(--track-text);">
                                        Tracking ID: <strong class="font-monospace bn-digit" style="color: var(--track-primary);">{{ $trackedOrder->courier_tracking_id ?? 'N/A' }}</strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif

                {{-- Bottom Meta Info & View Order Details Link --}}
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mt-4 pt-3 border-top" style="border-color: var(--track-card-border) !important;">
                    <span class="small" style="color: var(--track-text);">
                        <i class="fas fa-map-marker-alt text-danger me-1"></i> Delivery To: 
                        <strong style="color: var(--track-title);">{{ $trackedOrder->shipping_address ?? ($trackedOrder->shipping_district ?? 'Your Shipping Address') }}</strong>
                    </span>
                    <a href="{{ route('user.orders.details', $trackedOrder->invoice_no) }}" class="btn btn-view-order-link">
                        <i class="fas fa-file-invoice"></i> <span>View Order Details</span>
                    </a>
                </div>
            </div>
        @elseif(request()->filled('invoice_no'))
            <div class="alert alert-warning rounded-4 border-0 py-3 text-center small mt-3">
                <i class="fas fa-exclamation-triangle me-1"></i> 
                No matching order was found for invoice "<strong>{{ request('invoice_no') }}</strong>". Please verify your invoice number.
            </div>
        @endif

    </div>
</div>
@endsection