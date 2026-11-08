
@extends('layouts.admin')

@section('title', 'New SMS Campaign')

@section('admin_content')
<div class="container-fluid px-3 px-md-4 py-4 sms-campaign-page">

    {{-- Top Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 sms-page-header">
        <div>
            <h4 class="fw-bold mb-1">Create SMS Campaign</h4>
            <p class="text-muted small mb-0">Broadcast instant or scheduled SMS to targeted, verified audiences.</p>
        </div>
        <a href="{{ route('admin.sms.campaigns.index') }}" class="btn btn-outline-secondary btn-sm px-3 sms-back-btn">
            <i class="fas fa-arrow-left me-1"></i> Back to Campaigns
        </a>
    </div>

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show small mb-4" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i> Please check the form errors below.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show small mb-4" role="alert">
            <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.sms.campaigns.store') }}" method="POST" id="campaignForm">
        @csrf

        <input type="hidden" name="selected_phones" id="selectedPhonesInput" value="">

        <div class="row g-4">

            {{-- Left Column --}}
            <div class="col-12 col-lg-8">

                {{-- Basic Details --}}
                <div class="card sms-card mb-4">
                    <div class="card-header">
                        <h6 class="sms-card-title"><i class="fas fa-circle-info me-2"></i>Campaign Info</h6>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label">Campaign Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Flash Sale Blast" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">SMS Gateway &amp; Sender ID <span class="text-danger">*</span></label>
                                <div class="row g-2">
                                    <div class="col-7">
                                        <select name="provider_id" id="providerSelect" class="form-select" required>
                                            @foreach($providers as $provider)
                                                <option value="{{ $provider->id }}" data-price="{{ $provider->price_per_sms ?? 0.35 }}" data-sender="{{ $provider->sender_id }}" {{ old('provider_id') == $provider->id ? 'selected' : '' }}>
                                                    {{ $provider->name }} (৳{{ number_format((float) ($provider->price_per_sms ?? 0.35), 4) }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-5">
                                        <input type="text" name="sender_id" id="senderIdInput" class="form-control" value="{{ old('sender_id', $providers->first()?->sender_id) }}" placeholder="Sender ID">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Target Audience Selection --}}
                <div class="card sms-card mb-4">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h6 class="sms-card-title"><i class="fas fa-users me-2"></i>Target Audience Segmentation</h6>
                            <span class="text-muted small">Select one audience group or use a custom number list</span>
                        </div>
                        <span class="badge sms-neutral-badge">Validated BD Numbers Only</span>
                    </div>

                    <div class="card-body p-3 p-md-4">
                        <div class="row g-2 mb-4">

                            {{-- 1. All Order Buyers --}}
                            <div class="col-12 col-sm-6 col-md-3">
                                <label class="audience-card h-100 {{ old('target_audience', 'all_order_customers') === 'all_order_customers' ? 'border-primary bg-primary-subtle' : '' }}">
                                    <input type="radio" name="target_audience" value="all_order_customers" class="d-none audience-radio" data-count="{{ $audienceCounts['order_customers'] ?? 0 }}" {{ old('target_audience', 'all_order_customers') === 'all_order_customers' ? 'checked' : '' }}>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="audience-card-title">Order Buyers</span>
                                        <i class="fas fa-shopping-bag text-primary"></i>
                                    </div>
                                    <span class="audience-card-description">All verified order buyers</span>
                                    <span class="audience-card-count text-primary" id="cardCountOrder">{{ number_format($audienceCounts['order_customers'] ?? 0) }}</span>
                                </label>
                            </div>

                            {{-- 2. Recent Buyers --}}
                            <div class="col-12 col-sm-6 col-md-3">
                                <label class="audience-card h-100 {{ old('target_audience') === 'recent_buyers' ? 'border-primary bg-primary-subtle' : '' }}">
                                    <input type="radio" name="target_audience" value="recent_buyers" class="d-none audience-radio" data-count="{{ $audienceCounts['recent_buyers'] ?? 0 }}" {{ old('target_audience') === 'recent_buyers' ? 'checked' : '' }}>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="audience-card-title">Recent Buyers</span>
                                        <i class="fas fa-clock-rotate-left text-success"></i>
                                    </div>
                                    <span class="audience-card-description">Active in last 60 days</span>
                                    <span class="audience-card-count text-success" id="cardCountRecent">{{ number_format($audienceCounts['recent_buyers'] ?? 0) }}</span>
                                </label>
                            </div>

                            {{-- 3. Product-Specific Buyers --}}
                            <div class="col-12 col-sm-6 col-md-3">
                                <label class="audience-card h-100 {{ old('target_audience') === 'product_buyers' ? 'border-primary bg-primary-subtle' : '' }}">
                                    <input type="radio" name="target_audience" value="product_buyers" class="d-none audience-radio" data-count="{{ $audienceCounts['order_customers'] ?? 0 }}" {{ old('target_audience') === 'product_buyers' ? 'checked' : '' }}>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="audience-card-title">Product Buyers</span>
                                        <i class="fas fa-box text-warning"></i>
                                    </div>
                                    <span class="audience-card-description">By purchased product</span>
                                    <span class="audience-card-count" id="productBuyerCount">{{ number_format($audienceCounts['order_customers'] ?? 0) }}</span>
                                </label>
                            </div>

                            {{-- 4. Category-Specific Buyers --}}
                            <div class="col-12 col-sm-6 col-md-3">
                                <label class="audience-card h-100 {{ old('target_audience') === 'category_buyers' ? 'border-primary bg-primary-subtle' : '' }}">
                                    <input type="radio" name="target_audience" value="category_buyers" class="d-none audience-radio" data-count="{{ $audienceCounts['order_customers'] ?? 0 }}" {{ old('target_audience') === 'category_buyers' ? 'checked' : '' }}>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="audience-card-title">Category Buyers</span>
                                        <i class="fas fa-tags text-info"></i>
                                    </div>
                                    <span class="audience-card-description">By product category</span>
                                    <span class="audience-card-count" id="categoryBuyerCount">{{ number_format($audienceCounts['order_customers'] ?? 0) }}</span>
                                </label>
                            </div>

                            {{-- 5. Registered Customers --}}
                            <div class="col-12 col-sm-6 col-md-3">
                                <label class="audience-card h-100 {{ old('target_audience') === 'registered_customers' ? 'border-primary bg-primary-subtle' : '' }}">
                                    <input type="radio" name="target_audience" value="registered_customers" class="d-none audience-radio" data-count="{{ $audienceCounts['registered_customers'] ?? 0 }}" {{ old('target_audience') === 'registered_customers' ? 'checked' : '' }}>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="audience-card-title">Reg. Customers</span>
                                        <i class="fas fa-user-check text-primary"></i>
                                    </div>
                                    <span class="audience-card-description">Registered with phone</span>
                                    <span class="audience-card-count text-primary">{{ number_format($audienceCounts['registered_customers'] ?? 0) }}</span>
                                </label>
                            </div>

                            {{-- 6. Staff & Admin Users --}}
                            <div class="col-12 col-sm-6 col-md-3">
                                <label class="audience-card h-100 {{ old('target_audience') === 'staff_users' ? 'border-primary bg-primary-subtle' : '' }}">
                                    <input type="radio" name="target_audience" value="staff_users" class="d-none audience-radio" data-count="{{ $audienceCounts['staff_users'] ?? 0 }}" {{ old('target_audience') === 'staff_users' ? 'checked' : '' }}>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="audience-card-title">Staff &amp; Admin</span>
                                        <i class="fas fa-user-shield text-danger"></i>
                                    </div>
                                    <span class="audience-card-description">Internal team members</span>
                                    <span class="audience-card-count text-danger">{{ number_format($audienceCounts['staff_users'] ?? 0) }}</span>
                                </label>
                            </div>

                            {{-- 7. All Eligible Combined --}}
                            <div class="col-12 col-sm-6 col-md-3">
                                <label class="audience-card h-100 {{ old('target_audience') === 'all_eligible' ? 'border-primary bg-primary-subtle' : '' }}">
                                    <input type="radio" name="target_audience" value="all_eligible" class="d-none audience-radio" data-count="{{ $audienceCounts['all_eligible'] ?? 0 }}" {{ old('target_audience') === 'all_eligible' ? 'checked' : '' }}>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="audience-card-title">All Eligible Contacts</span>
                                        <i class="fas fa-users-viewfinder text-dark"></i>
                                    </div>
                                    <span class="audience-card-description">Combined unique pool</span>
                                    <span class="audience-card-count">{{ number_format($audienceCounts['all_eligible'] ?? 0) }}</span>
                                </label>
                            </div>

                            {{-- 8. Custom Numbers List --}}
                            <div class="col-12 col-sm-6 col-md-3">
                                <label class="audience-card h-100 {{ old('target_audience') === 'custom_numbers' ? 'border-primary bg-primary-subtle' : '' }}">
                                    <input type="radio" name="target_audience" value="custom_numbers" class="d-none audience-radio" data-count="0" {{ old('target_audience') === 'custom_numbers' ? 'checked' : '' }}>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="audience-card-title">Custom List</span>
                                        <i class="fas fa-keyboard text-secondary"></i>
                                    </div>
                                    <span class="audience-card-description">Manual paste / CSV</span>
                                    <span class="audience-card-count text-secondary" id="customNumbersCount">0</span>
                                </label>
                            </div>
                        </div>

                        {{-- Dynamic Filter Controls --}}
                        <div class="sms-filter-panel mb-3" id="dynamicFilterControls">
                            <div class="row g-3 align-items-end">

                                <div class="col-12 col-md-4 d-none" id="productFilterWrapper">
                                    <label class="form-label">Target Product</label>
                                    <select name="product_id" id="filterProductSelect" class="form-select">
                                        <option value="">All Products (Select to filter)</option>
                                        @foreach($products as $prod)
                                            <option value="{{ $prod->id }}">{{ Str::limit($prod->title, 40) }} ({{ $prod->product_code ?? '#'.$prod->id }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-md-4 d-none" id="categoryFilterWrapper">
                                    <label class="form-label">Target Category</label>
                                    <select name="category_id" id="filterCategorySelect" class="form-select">
                                        <option value="">All Categories (Select to filter)</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-md-4" id="datePresetWrapper">
                                    <label class="form-label">Date / Purchase Timeline</label>
                                    <select name="date_preset" id="filterDatePreset" class="form-select">
                                        <option value="all">All Time History</option>
                                        <option value="today">Today's Orders</option>
                                        <option value="7_days">Last 7 Days</option>
                                        <option value="30_days">Last 30 Days</option>
                                        <option value="60_days">Last 60 Days (Recent)</option>
                                        <option value="90_days">Last 90 Days</option>
                                        <option value="this_month">This Month</option>
                                        <option value="last_month">Last Month</option>
                                        <option value="custom">Custom Date Range</option>
                                    </select>
                                </div>

                                <div class="col-6 col-md-2 d-none" id="customStartDateWrapper">
                                    <label class="form-label">From Date</label>
                                    <input type="date" name="start_date" id="filterStartDate" class="form-control">
                                </div>

                                <div class="col-6 col-md-2 d-none" id="customEndDateWrapper">
                                    <label class="form-label">To Date</label>
                                    <input type="date" name="end_date" id="filterEndDate" class="form-control">
                                </div>

                                <div class="col-12 col-md-4 ms-auto">
                                    <label class="form-label">Search within list</label>
                                    <div class="input-group">
                                        <input type="text" id="recipientSearchInput" class="form-control" placeholder="Search customer name or phone...">
                                        <button type="button" class="btn btn-primary" id="btnRefreshRecipients" title="Refresh list">
                                            <i class="fas fa-sync-alt" id="refreshIcon"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Custom Phone Numbers --}}
                        <div id="customNumbersContainer" class="d-none mb-3">
                            <label class="form-label d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <span>Enter or Paste Mobile Numbers <span class="text-danger">*</span></span>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle" id="customValidBadge">0 Valid Numbers</span>
                            </label>
                            <textarea name="custom_numbers" id="customNumbersTextarea" rows="5" class="form-control font-monospace @error('custom_numbers') is-invalid @enderror" placeholder="Paste numbers separated by comma, space or newline:&#10;01711000000&#10;01811000000&#10;+8801911000000">{{ old('custom_numbers') }}</textarea>
                            <span class="form-text">Validated BD prefixes: 013, 014, 015, 016, 017, 018, 019, 096 (with or without 88 / +88). Duplicates are automatically removed.</span>
                            @error('custom_numbers')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Recipient Table --}}
                        <div class="sms-recipient-panel" id="recipientsTableContainer">
                            <div class="sms-recipient-toolbar">
                                <div class="d-flex flex-wrap align-items-center gap-3">
                                    <div class="form-check m-0">
                                        <input class="form-check-input" type="checkbox" id="selectAllRecipients" checked>
                                        <label class="form-check-label fw-semibold" for="selectAllRecipients">Select All</label>
                                    </div>
                                    <span class="text-muted small"><span id="recipientCountBadge">0</span> matching contacts found</span>
                                </div>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-2" id="selectedCountBadge">0 Selected</span>
                            </div>

                            <div class="table-responsive sms-recipient-table-wrap">
                                <table class="table table-hover table-sm align-middle mb-0" id="recipientsTable">
                                    <thead class="sticky-top">
                                        <tr>
                                            <th class="text-center ps-3" style="width: 48px;">#</th>
                                            <th>Recipient Name</th>
                                            <th>Mobile Number</th>
                                            <th>Segment Source</th>
                                            <th class="text-center">Order Activity</th>
                                            <th class="text-end pe-3">Total Spent</th>
                                        </tr>
                                    </thead>
                                    <tbody id="recipientsTableBody">
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                <div class="spinner-border spinner-border-sm text-primary me-1" role="status"></div>
                                                Loading eligible contacts...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Message Content --}}
                <div class="card sms-card mb-4">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <h6 class="sms-card-title"><i class="fas fa-comment-sms me-2"></i>Message Body</h6>
                        @if($templates->count() > 0)
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted small">Insert Template:</span>
                                <select id="templatePicker" class="form-select form-select-sm sms-template-select">
                                    <option value="">Select template...</option>
                                    @foreach($templates as $tpl)
                                        <option value="{{ $tpl->id }}" data-body="{{ $tpl->body }}">{{ $tpl->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>

                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">SMS Content <span class="text-danger">*</span></label>
                            <span class="badge sms-neutral-badge" id="encodingBadge">GSM-7</span>
                        </div>

                        <textarea name="sms_message" id="campaignMessage" rows="5" class="form-control @error('sms_message') is-invalid @enderror" placeholder="Type marketing message here..." required>{{ old('sms_message') }}</textarea>
                        @error('sms_message')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <div class="sms-counter-bar mt-3">
                            <div>Characters: <strong id="charCount">0</strong></div>
                            <div>
                                Parts: <strong id="partsCount">1</strong>
                                <span class="text-muted ms-1" id="partsInfo">(160 chars/part)</span>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="form-label">Internal Reference Note (Optional)</label>
                            <input type="text" name="description" class="form-control" value="{{ old('description') }}" placeholder="Campaign objective or marketing reference">
                        </div>
                    </div>
                </div>

            </div>

            {{-- Right Column --}}
            <div class="col-12 col-lg-4">

              {{-- Schedule Timing --}}
<div class="card sms-card mb-4">
    <div class="card-header">
        <h6 class="sms-card-title mb-0"><i class="fas fa-calendar-clock me-2"></i>Execution Timing</h6>
    </div>
    <div class="card-body p-3 p-md-4">
        <!-- Option 1: Send Immediately -->
        <div class="form-check custom-radio-box p-3 border rounded-3 mb-3 position-relative cursor-pointer">
            <input class="form-check-input position-absolute top-50 start-0 translate-middle-y ms-3" type="radio" name="schedule_type" id="scheduleImmediate" value="immediate" {{ old('schedule_type', 'immediate') === 'immediate' ? 'checked' : '' }}>
            <label class="form-check-label d-block ps-4 cursor-pointer" for="scheduleImmediate">
                <span class="fw-semibold text-dark d-block">Send Immediately</span>
                <span class="text-muted small">Queues and starts dispatch immediately</span>
            </label>
        </div>

        <!-- Option 2: Schedule for Later -->
        <div class="form-check custom-radio-box p-3 border rounded-3 mb-3 position-relative cursor-pointer">
            <input class="form-check-input position-absolute top-50 start-0 translate-middle-y ms-3" type="radio" name="schedule_type" id="scheduleLater" value="scheduled" {{ old('schedule_type') === 'scheduled' ? 'checked' : '' }}>
            <label class="form-check-label d-block ps-4 cursor-pointer" for="scheduleLater">
                <span class="fw-semibold text-dark d-block">Schedule for Later</span>
                <span class="text-muted small">Dispatches at specified date &amp; time</span>
            </label>
        </div>

        <!-- Date & Time Input Container -->
        <div id="scheduleTimeContainer" class="{{ old('schedule_type') === 'scheduled' ? '' : 'd-none' }} mt-3">
            <label class="form-label fw-semibold" style="font-size: 12.5px;">Scheduled Date &amp; Time <span class="text-danger">*</span></label>
            <input type="datetime-local" name="scheduled_at" id="scheduledAtInput" class="form-control @error('scheduled_at') is-invalid @enderror" value="{{ old('scheduled_at') }}" min="{{ now()->addMinutes(2)->format('Y-m-d\TH:i') }}">
            @error('scheduled_at')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

                {{-- Campaign Summary --}}
                <div class="card sms-card mb-4">
                    <div class="card-header">
                        <h6 class="sms-card-title"><i class="fas fa-chart-pie me-2"></i>Campaign Summary</h6>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <ul class="list-unstyled small mb-4">
                            <li class="sms-summary-row">
                                <span>Target Recipients</span>
                                <strong id="summaryRecipients">0</strong>
                            </li>
                            <li class="sms-summary-row">
                                <span>SMS Parts / Recipient</span>
                                <strong id="summaryParts">1</strong>
                            </li>
                            <li class="sms-summary-row">
                                <span>Total Message Credits</span>
                                <strong id="summaryTotalCredits">0</strong>
                            </li>
                            <li class="sms-summary-row sms-summary-total">
                                <span>Estimated Cost</span>
                                <h5 id="summaryEstCost">৳ 0.00</h5>
                            </li>
                        </ul>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary py-2" id="btnSubmitCampaign">
                                <i class="fas fa-paper-plane me-1"></i> Launch Campaign
                            </button>
                            <a href="{{ route('admin.sms.campaigns.index') }}" class="btn btn-outline-secondary py-2">Cancel</a>
                        </div>
                    </div>
                </div>

                {{-- SMS Preview --}}
                <div class="card sms-card">
                    <div class="card-header">
                        <h6 class="sms-card-title"><i class="fas fa-mobile-screen-button me-2"></i>Live SMS Preview</h6>
                    </div>
                    <div class="card-body p-3 sms-preview-area">
                        <div class="sms-phone-preview">
                            <div class="sms-preview-heading">
                                <span id="previewSender">SHOP NOW BD</span>
                                <span>Preview</span>
                            </div>
                            <div class="sms-preview-message" id="previewBody">Type message to preview...</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

{{-- Direct In-Content JavaScript Engine --}}
<script>
(function() {
    function initSmsCampaignEngine() {
        const campaignForm          = document.getElementById('campaignForm');
        const providerSelect        = document.getElementById('providerSelect');
        const senderIdInput         = document.getElementById('senderIdInput');
        const campaignMessage       = document.getElementById('campaignMessage');
        const templatePicker        = document.getElementById('templatePicker');
        const charCount             = document.getElementById('charCount');
        const partsCount            = document.getElementById('partsCount');
        const partsInfo             = document.getElementById('partsInfo');
        const encodingBadge         = document.getElementById('encodingBadge');

        const audienceRadios        = document.querySelectorAll('.audience-radio');
        const dynamicFilterControls = document.getElementById('dynamicFilterControls');
        const productFilterWrapper  = document.getElementById('productFilterWrapper');
        const filterProductSelect   = document.getElementById('filterProductSelect');
        const categoryFilterWrapper = document.getElementById('categoryFilterWrapper');
        const filterCategorySelect  = document.getElementById('filterCategorySelect');
        const filterDatePreset      = document.getElementById('filterDatePreset');
        const customStartDateWrapper= document.getElementById('customStartDateWrapper');
        const filterStartDate       = document.getElementById('filterStartDate');
        const customEndDateWrapper  = document.getElementById('customEndDateWrapper');
        const filterEndDate         = document.getElementById('filterEndDate');

        const recipientSearchInput  = document.getElementById('recipientSearchInput');
        const btnRefreshRecipients  = document.getElementById('btnRefreshRecipients');
        const refreshIcon           = document.getElementById('refreshIcon');

        const customNumbersContainer= document.getElementById('customNumbersContainer');
        const customNumbersTextarea = document.getElementById('customNumbersTextarea');
        const customNumbersCount    = document.getElementById('customNumbersCount');
        const customValidBadge      = document.getElementById('customValidBadge');

        const recipientsTableContainer = document.getElementById('recipientsTableContainer');
        const recipientsTableBody   = document.getElementById('recipientsTableBody');
        const selectAllRecipients   = document.getElementById('selectAllRecipients');
        const recipientCountBadge   = document.getElementById('recipientCountBadge');
        const selectedCountBadge    = document.getElementById('selectedCountBadge');
        const selectedPhonesInput   = document.getElementById('selectedPhonesInput');

        const scheduleImmediate     = document.getElementById('scheduleImmediate');
        const scheduleLater         = document.getElementById('scheduleLater');
        const scheduleTimeContainer = document.getElementById('scheduleTimeContainer');

        const summaryRecipients     = document.getElementById('summaryRecipients');
        const summaryParts          = document.getElementById('summaryParts');
        const summaryTotalCredits   = document.getElementById('summaryTotalCredits');
        const summaryEstCost        = document.getElementById('summaryEstCost');
        const previewBody           = document.getElementById('previewBody');
        const previewSender         = document.getElementById('previewSender');

        let currentRecipientsList = [];
        let selectedRecipientsMap = new Map();

        function validateBdPhone(raw) {
            if (!raw) return null;
            let clean = raw.replace(/[^0-9]/g, '');
            if (clean.startsWith('00880')) clean = clean.substring(2);
            if (clean.length === 13 && clean.startsWith('880')) {
                let local = clean.substring(2);
                if (/^(01[3-9]\d{8}|096\d{8})$/.test(local)) return '88' + local;
            }
            if (clean.length === 11 && /^(01[3-9]\d{8}|096\d{8})$/.test(clean)) {
                return '88' + clean;
            }
            if (clean.length === 10 && /^(1[3-9]\d{8}|96\d{8})$/.test(clean)) {
                return '880' + clean;
            }
            if (clean.length === 12 && clean.startsWith('88')) {
                let sub = clean.substring(2);
                if (/^(1[3-9]\d{8})$/.test(sub)) return '880' + sub;
            }
            return null;
        }

        function parseCustomNumbersFromTextarea() {
            if (!customNumbersTextarea) return [];
            const text = customNumbersTextarea.value.trim();
            if (!text) return [];
            const lines = text.split(/[\r\n,; ]+/).filter(Boolean);
            const map = new Map();
            lines.forEach(item => {
                const formatted = validateBdPhone(item);
                if (formatted) map.set(formatted, formatted);
            });
            return Array.from(map.values());
        }

        // Provider & Sender updates
        if (providerSelect) {
            providerSelect.addEventListener('change', function () {
                const selected = this.options[this.selectedIndex];
                const defaultSender = selected.getAttribute('data-sender') || '';
                if (senderIdInput) senderIdInput.value = defaultSender;
                if (previewSender) previewSender.textContent = defaultSender || 'SHOP NOW BD';
                calculateEverything();
            });
        }

        if (senderIdInput) {
            senderIdInput.addEventListener('input', function () {
                if (previewSender) previewSender.textContent = this.value.trim() || 'SHOP NOW BD';
            });
        }

        // Audience Selection
        audienceRadios.forEach(radio => {
            radio.addEventListener('change', function () {
                document.querySelectorAll('.audience-card').forEach(c => {
                    c.classList.remove('border-primary', 'bg-primary-subtle');
                });
                this.closest('.audience-card').classList.add('border-primary', 'bg-primary-subtle');

                const val = this.value;

                if (val === 'custom_numbers') {
                    selectedRecipientsMap.clear();
                    currentRecipientsList = [];
                    if (selectedPhonesInput) selectedPhonesInput.value = '';
                    if (selectedCountBadge) selectedCountBadge.textContent = '0 Selected';
                    if (selectAllRecipients) selectAllRecipients.checked = false;
                    document.querySelectorAll('.recipient-row-checkbox').forEach(chk => chk.checked = false);

                    if (customNumbersContainer) customNumbersContainer.classList.remove('d-none');
                    if (dynamicFilterControls) dynamicFilterControls.classList.add('d-none');
                    if (recipientsTableContainer) recipientsTableContainer.classList.add('d-none');

                    calculateEverything();
                } else {
                    if (customNumbersContainer) customNumbersContainer.classList.add('d-none');
                    if (dynamicFilterControls) dynamicFilterControls.classList.remove('d-none');
                    if (recipientsTableContainer) recipientsTableContainer.classList.remove('d-none');

                    if (productFilterWrapper) productFilterWrapper.classList.toggle('d-none', val !== 'product_buyers');
                    if (categoryFilterWrapper) categoryFilterWrapper.classList.toggle('d-none', val !== 'category_buyers');

                    fetchRecipientsViaAjax();
                }
            });
        });

        // Custom numbers live input
        if (customNumbersTextarea) {
            customNumbersTextarea.addEventListener('input', function () {
                const validList = parseCustomNumbersFromTextarea();
                const count = validList.length;
                if (customNumbersCount) customNumbersCount.textContent = count;
                if (customValidBadge) customValidBadge.textContent = `${count} Valid Numbers`;
                calculateEverything();
            });
        }

        // Date Presets & Filter Changes
        if (filterDatePreset) {
            filterDatePreset.addEventListener('change', function () {
                const isCustom = (this.value === 'custom');
                if (customStartDateWrapper) customStartDateWrapper.classList.toggle('d-none', !isCustom);
                if (customEndDateWrapper) customEndDateWrapper.classList.toggle('d-none', !isCustom);
                if (!isCustom) fetchRecipientsViaAjax();
            });
        }

        if (filterStartDate) filterStartDate.addEventListener('change', fetchRecipientsViaAjax);
        if (filterEndDate) filterEndDate.addEventListener('change', fetchRecipientsViaAjax);
        if (filterProductSelect) filterProductSelect.addEventListener('change', fetchRecipientsViaAjax);
        if (filterCategorySelect) filterCategorySelect.addEventListener('change', fetchRecipientsViaAjax);

        let searchDebounceTimer = null;
        if (recipientSearchInput) {
            recipientSearchInput.addEventListener('input', function () {
                clearTimeout(searchDebounceTimer);
                searchDebounceTimer = setTimeout(fetchRecipientsViaAjax, 400);
            });
        }

        if (btnRefreshRecipients) {
            btnRefreshRecipients.addEventListener('click', fetchRecipientsViaAjax);
        }

        // AJAX Recipient Discovery
        function fetchRecipientsViaAjax() {
            const activeAudience = document.querySelector('.audience-radio:checked');
            const audienceVal = activeAudience ? activeAudience.value : 'all_order_customers';

            if (audienceVal === 'custom_numbers') {
                calculateEverything();
                return;
            }

            if (refreshIcon) refreshIcon.classList.add('fa-spin');
            if (recipientsTableBody) {
                recipientsTableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <div class="spinner-border spinner-border-sm text-primary me-1" role="status"></div>
                            Loading eligible contacts...
                        </td>
                    </tr>
                `;
            }

            const payload = {
                audience_type: audienceVal,
                product_id: filterProductSelect ? filterProductSelect.value : '',
                category_id: filterCategorySelect ? filterCategorySelect.value : '',
                date_preset: filterDatePreset ? filterDatePreset.value : 'all',
                start_date: filterStartDate ? filterStartDate.value : '',
                end_date: filterEndDate ? filterEndDate.value : '',
                search: recipientSearchInput ? recipientSearchInput.value.trim() : '',
                _token: '{{ csrf_token() }}'
            };

            fetch("{{ route('admin.sms.campaigns.filter_recipients_ajax') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify(payload)
            })
            .then(async res => {
                const data = await res.json().catch(() => null);
                if (!res.ok || !data) {
                    throw new Error((data && data.message) ? data.message : ("HTTP status " + res.status));
                }
                return data;
            })
            .then(data => {
                if (data.success) {
                    currentRecipientsList = data.recipients || [];
                    selectedRecipientsMap.clear();

                    currentRecipientsList.forEach(rec => {
                        selectedRecipientsMap.set(rec.phone_number, {
                            phone: rec.phone_number,
                            customer_id: rec.customer_id
                        });
                    });

                    renderRecipientsTable(currentRecipientsList);

                    if (recipientCountBadge) recipientCountBadge.textContent = data.total_count;
                    if (selectAllRecipients) selectAllRecipients.checked = currentRecipientsList.length > 0;

                    if (audienceVal === 'product_buyers' && document.getElementById('productBuyerCount')) {
                        document.getElementById('productBuyerCount').textContent = data.total_count;
                    }
                    if (audienceVal === 'category_buyers' && document.getElementById('categoryBuyerCount')) {
                        document.getElementById('categoryBuyerCount').textContent = data.total_count;
                    }
                } else {
                    currentRecipientsList = [];
                    selectedRecipientsMap.clear();
                    if (recipientCountBadge) recipientCountBadge.textContent = '0';
                    if (recipientsTableBody) {
                        recipientsTableBody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-danger">${data.message || 'Failed to load contacts.'}</td></tr>`;
                    }
                }
            })
            .catch(err => {
                console.error(err);
                currentRecipientsList = [];
                selectedRecipientsMap.clear();
                if (recipientCountBadge) recipientCountBadge.textContent = '0';
                if (recipientsTableBody) {
                    recipientsTableBody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle me-1"></i> ${err.message || 'Error loading contacts.'}</td></tr>`;
                }
            })
            .finally(() => {
                if (refreshIcon) refreshIcon.classList.remove('fa-spin');
                syncSelectedState();
                calculateEverything();
            });
        }

        // Render Table Rows
        function renderRecipientsTable(list) {
            if (!recipientsTableBody) return;

            if (list.length === 0) {
                recipientsTableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="fas fa-user-slash fs-4 d-block mb-1 text-secondary opacity-50"></i>
                            No eligible recipients found matching the filter criteria.
                        </td>
                    </tr>
                `;
                return;
            }

            let html = '';
            list.forEach((rec) => {
                const isChecked = selectedRecipientsMap.has(rec.phone_number) ? 'checked' : '';
                const orderCount = rec.orders_count !== undefined ? rec.orders_count : 1;
                const spentFormatted = typeof rec.total_spent === 'number' ? `৳${rec.total_spent.toFixed(2)}` : (rec.total_spent || '—');

                html += `
                    <tr>
                        <td class="text-center ps-2">
                            <input type="checkbox" class="form-check-input recipient-row-checkbox"
                                   data-phone="${rec.phone_number}"
                                   data-customer-id="${rec.customer_id || ''}"
                                   ${isChecked}>
                        </td>
                        <td class="fw-semibold text-dark text-truncate" style="max-width: 160px;" title="${rec.name}">
                            ${rec.name || 'Customer'}
                        </td>
                        <td class="font-monospace text-primary fw-medium">
                            ${rec.phone_number}
                        </td>
                        <td>
                            <span class="badge bg-light text-secondary border px-2 py-1">
                                ${rec.source || 'Order'}
                            </span>
                        </td>
                        <td class="text-center text-muted">${orderCount}</td>
                        <td class="text-end pe-3 font-monospace fw-semibold text-dark">${spentFormatted}</td>
                    </tr>
                `;
            });

            recipientsTableBody.innerHTML = html;

            document.querySelectorAll('.recipient-row-checkbox').forEach(chk => {
                chk.addEventListener('change', function () {
                    const phone = this.getAttribute('data-phone');
                    const customerId = this.getAttribute('data-customer-id');
                    if (this.checked) {
                        selectedRecipientsMap.set(phone, { phone: phone, customer_id: customerId });
                    } else {
                        selectedRecipientsMap.delete(phone);
                    }
                    syncSelectedState();
                    calculateEverything();
                });
            });
        }

        // Select All Checkbox
        if (selectAllRecipients) {
            selectAllRecipients.addEventListener('change', function () {
                const checked = this.checked;
                document.querySelectorAll('.recipient-row-checkbox').forEach(chk => {
                    chk.checked = checked;
                    const phone = chk.getAttribute('data-phone');
                    const customerId = chk.getAttribute('data-customer-id');
                    if (checked) {
                        selectedRecipientsMap.set(phone, { phone: phone, customer_id: customerId });
                    } else {
                        selectedRecipientsMap.delete(phone);
                    }
                });
                syncSelectedState();
                calculateEverything();
            });
        }

        function syncSelectedState() {
            const activeAudience = document.querySelector('.audience-radio:checked');
            const audienceVal = activeAudience ? activeAudience.value : 'all_order_customers';

            if (audienceVal === 'custom_numbers') {
                if (selectedPhonesInput) selectedPhonesInput.value = '';
                return;
            }

            const count = selectedRecipientsMap.size;
            if (selectedCountBadge) selectedCountBadge.textContent = `${count} Selected`;

            if (selectedPhonesInput) {
                const selectedArray = Array.from(selectedRecipientsMap.values());
                selectedPhonesInput.value = JSON.stringify(selectedArray);
            }

            if (selectAllRecipients) {
                const rowCount = document.querySelectorAll('.recipient-row-checkbox').length;
                selectAllRecipients.checked = rowCount > 0 && count === rowCount;
            }
        }

        // Template Insertion
        if (templatePicker) {
            templatePicker.addEventListener('change', function () {
                const selected = this.options[this.selectedIndex];
                const body = selected.getAttribute('data-body') || '';
                if (body && campaignMessage) {
                    campaignMessage.value = body;
                    calculateEverything();
                }
            });
        }

        // Scheduling Toggle
        if (scheduleImmediate) {
            scheduleImmediate.addEventListener('change', function () {
                if (this.checked && scheduleTimeContainer) scheduleTimeContainer.classList.add('d-none');
            });
        }
        if (scheduleLater) {
            scheduleLater.addEventListener('change', function () {
                if (this.checked && scheduleTimeContainer) scheduleTimeContainer.classList.remove('d-none');
            });
        }

        // Message Counter & Cost Calculation
        function calculateEverything() {
            if (!campaignMessage) return;

            const text = campaignMessage.value;
            const length = text.length;
            if (charCount) charCount.textContent = length;

            const isUnicode = /[^\u0000-\u007F]/.test(text);
            let parts = 1;

            if (isUnicode) {
                if (encodingBadge) {
                    encodingBadge.textContent = 'Unicode (Bangla)';
                    encodingBadge.className = 'badge bg-warning-subtle text-warning-emphasis border border-warning-subtle fw-normal';
                }
                if (length <= 70) {
                    parts = 1;
                    if (partsInfo) partsInfo.textContent = '(70 chars/part)';
                } else {
                    parts = Math.ceil(length / 67);
                    if (partsInfo) partsInfo.textContent = '(67 chars/part)';
                }
            } else {
                if (encodingBadge) {
                    encodingBadge.textContent = 'GSM-7 (Standard)';
                    encodingBadge.className = 'badge bg-light text-secondary border fw-normal';
                }
                if (length <= 160) {
                    parts = 1;
                    if (partsInfo) partsInfo.textContent = '(160 chars/part)';
                } else {
                    parts = Math.ceil(length / 153);
                    if (partsInfo) partsInfo.textContent = '(153 chars/part)';
                }
            }

            if (partsCount) partsCount.textContent = parts;
            if (summaryParts) summaryParts.textContent = parts;
            if (previewBody) previewBody.textContent = length > 0 ? text : 'Type message to preview...';

            let recipients = 0;
            const activeAudience = document.querySelector('.audience-radio:checked');
            const audienceVal = activeAudience ? activeAudience.value : 'all_order_customers';

            if (audienceVal === 'custom_numbers') {
                const validCustomList = parseCustomNumbersFromTextarea();
                recipients = validCustomList.length;
            } else {
                recipients = selectedRecipientsMap.size;
            }

            if (summaryRecipients) summaryRecipients.textContent = recipients.toLocaleString();

            const totalCredits = recipients * parts;
            if (summaryTotalCredits) summaryTotalCredits.textContent = totalCredits.toLocaleString();

            const price = parseFloat(providerSelect ? (providerSelect.options[providerSelect.selectedIndex]?.getAttribute('data-price') || 0.35) : 0.35);
            const estimatedCost = (totalCredits * price).toFixed(2);
            if (summaryEstCost) summaryEstCost.textContent = `৳ ${parseFloat(estimatedCost).toLocaleString()}`;
        }

        if (campaignMessage) {
            campaignMessage.addEventListener('input', calculateEverything);
        }

        // Safe Form Submission Check
        if (campaignForm) {
            campaignForm.addEventListener('submit', function (e) {
                const activeAudience = document.querySelector('.audience-radio:checked');
                const audienceVal = activeAudience ? activeAudience.value : 'all_order_customers';

                if (audienceVal === 'custom_numbers') {
                    const validCustomList = parseCustomNumbersFromTextarea();
                    if (validCustomList.length === 0) {
                        e.preventDefault();
                        alert('Please enter at least one valid Bangladeshi mobile number in the custom list box (e.g., 01711000000 or 8801811000000).');
                        if (customNumbersTextarea) customNumbersTextarea.focus();
                        return false;
                    }
                    if (selectedPhonesInput) selectedPhonesInput.value = '';
                } else {
                    if (selectedRecipientsMap.size === 0) {
                        e.preventDefault();
                        alert('No recipients selected! Please select at least one contact checkbox from the list.');
                        return false;
                    }
                }
            });
        }

        // Initial Setup
        const initialChecked = document.querySelector('.audience-radio:checked');
        if (initialChecked && initialChecked.value === 'custom_numbers') {
            if (customNumbersContainer) customNumbersContainer.classList.remove('d-none');
            if (dynamicFilterControls) dynamicFilterControls.classList.add('d-none');
            if (recipientsTableContainer) recipientsTableContainer.classList.add('d-none');
            calculateEverything();
        } else {
            fetchRecipientsViaAjax();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSmsCampaignEngine);
    } else {
        initSmsCampaignEngine();
    }
})();
</script>

<style>
/* =========================================================
   Kaiadmin Lite v1.2.0 - Classic / White Classic
   Scoped styles: SMS Campaign page only
========================================================= */

.sms-campaign-page {
    --sms-primary: #1572e8;
    --sms-primary-dark: #1260c4;
    --sms-text: #2a2f5b;
    --sms-muted: #8d9498;
    --sms-border: #e6e8eb;
    --sms-surface: #ffffff;
    --sms-soft: #f7f9fc;
    --sms-radius: 8px;
    color: var(--sms-text);
    min-width: 0;
}

.sms-campaign-page * {
    box-sizing: border-box;
}

.sms-campaign-page .sms-page-header h4 {
    color: var(--sms-text);
    font-size: clamp(1.15rem, 1.5vw, 1.4rem);
    letter-spacing: -.35px;
}

.sms-campaign-page .sms-page-header p {
    line-height: 1.55;
}

.sms-campaign-page .sms-back-btn {
    min-height: 36px;
    border-radius: 6px;
    font-weight: 600;
}

.sms-campaign-page .sms-card {
    border: 1px solid var(--sms-border) !important;
    border-radius: var(--sms-radius) !important;
    background: var(--sms-surface);
    box-shadow: 0 2px 8px rgba(0, 0, 0, .035) !important;
    overflow: hidden;
}

.sms-campaign-page .sms-card > .card-header {
    min-height: 54px;
    padding: 15px 20px;
    display: flex;
    align-items: center;
    background: #fff;
    border-bottom: 1px solid var(--sms-border);
}

.sms-campaign-page .sms-card-title {
    margin: 0;
    color: var(--sms-text);
    font-size: 14px;
    font-weight: 700;
    letter-spacing: -.15px;
}

.sms-campaign-page .sms-card-title > i {
    color: var(--sms-primary);
    font-size: 13px;
}

.sms-campaign-page .form-label {
    display: block;
    margin-bottom: 7px;
    color: #495057;
    font-size: 12px;
    font-weight: 600;
}

.sms-campaign-page .form-control,
.sms-campaign-page .form-select {
    min-height: 38px;
    border: 1px solid #e3e6ea;
    border-radius: 6px;
    color: #495057;
    background-color: #fff;
    font-size: 13px;
    box-shadow: none;
    transition: border-color .15s ease, box-shadow .15s ease;
}

.sms-campaign-page textarea.form-control {
    min-height: 110px;
    padding: 11px 13px;
    line-height: 1.6;
    resize: vertical;
}

.sms-campaign-page .form-control:focus,
.sms-campaign-page .form-select:focus {
    border-color: rgba(21, 114, 232, .65);
    box-shadow: 0 0 0 3px rgba(21, 114, 232, .10);
}

.sms-campaign-page .form-control::placeholder {
    color: #aab0b7;
}

.sms-campaign-page .form-check-input {
    border-color: #cbd1d8;
    box-shadow: none;
}

.sms-campaign-page .form-check-input:checked {
    border-color: var(--sms-primary);
    background-color: var(--sms-primary);
}

.sms-campaign-page .btn {
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    transition: all .15s ease;
}

.sms-campaign-page .btn-primary {
    background-color: var(--sms-primary);
    border-color: var(--sms-primary);
}

.sms-campaign-page .btn-primary:hover {
    background-color: var(--sms-primary-dark);
    border-color: var(--sms-primary-dark);
}

.sms-campaign-page .btn-outline-secondary {
    border-color: #d9dde3;
    color: #626a73;
}

.sms-campaign-page .sms-neutral-badge {
    padding: 6px 9px;
    color: #68717b;
    background: #f5f6f8;
    border: 1px solid #e7e9ed;
    border-radius: 5px;
    font-size: 10px;
    font-weight: 600;
}

.sms-campaign-page .audience-card {
    display: block;
    height: 100%;
    padding: 13px;
    cursor: pointer;
    background: #fff;
    border: 1px solid var(--sms-border);
    border-radius: 7px !important;
    transition: border-color .15s ease, background-color .15s ease, box-shadow .15s ease;
}

.sms-campaign-page .audience-card:hover {
    border-color: rgba(21, 114, 232, .65) !important;
    box-shadow: 0 3px 10px rgba(21, 114, 232, .07);
}

.sms-campaign-page .audience-card.border-primary {
    border-color: var(--sms-primary) !important;
    background: #f2f7ff !important;
    box-shadow: 0 0 0 1px rgba(21, 114, 232, .08);
}

.sms-campaign-page .audience-card-title {
    color: var(--sms-text);
    font-size: 12px;
    font-weight: 700;
}

.sms-campaign-page .audience-card > div > i {
    font-size: 14px;
    opacity: .8;
}

.sms-campaign-page .audience-card-description {
    display: block;
    margin-top: 7px;
    color: var(--sms-muted);
    font-size: 10px;
    line-height: 1.45;
}

.sms-campaign-page .audience-card-count {
    display: block;
    margin-top: 9px;
    color: var(--sms-text);
    font-size: 17px;
    font-weight: 700;
    line-height: 1.2;
}

.sms-campaign-page .sms-filter-panel {
    padding: 16px;
    background: var(--sms-soft);
    border: 1px solid var(--sms-border);
    border-radius: 7px;
}

.sms-campaign-page .sms-filter-panel .form-label {
    margin-bottom: 6px;
    font-size: 11px;
}

.sms-campaign-page .sms-filter-panel .form-control,
.sms-campaign-page .sms-filter-panel .form-select {
    min-height: 36px;
    font-size: 12px;
}

.sms-campaign-page .sms-filter-panel .input-group .btn {
    min-width: 42px;
}

.sms-campaign-page .sms-recipient-panel {
    border: 1px solid var(--sms-border);
    border-radius: 7px;
    overflow: hidden;
}

.sms-campaign-page .sms-recipient-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    padding: 11px 14px;
    background: #f8f9fb;
    border-bottom: 1px solid var(--sms-border);
}

.sms-campaign-page .sms-recipient-toolbar .form-check-label {
    color: var(--sms-text);
    font-size: 11px;
}

.sms-campaign-page .sms-recipient-table-wrap {
    max-height: 300px;
    overflow: auto;
}

.sms-campaign-page #recipientsTable {
    min-width: 680px;
    font-size: 11px;
}

.sms-campaign-page #recipientsTable thead th {
    padding: 11px 10px;
    color: #69717b;
    background: #f8f9fb;
    border-bottom: 1px solid var(--sms-border);
    font-size: 10px;
    font-weight: 700;
    white-space: nowrap;
    letter-spacing: .2px;
}

.sms-campaign-page #recipientsTable tbody td {
    padding: 10px;
    border-color: #eef0f2;
    vertical-align: middle;
}

.sms-campaign-page #recipientsTable tbody tr:last-child td {
    border-bottom: 0;
}

.sms-campaign-page #recipientsTable .badge {
    font-size: 9px;
    font-weight: 600;
}

.sms-campaign-page .sms-template-select {
    width: 190px;
    max-width: 100%;
}

.sms-campaign-page .sms-counter-bar {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    padding: 10px 12px;
    color: #78818a;
    background: #f8f9fb;
    border: 1px solid #edf0f3;
    border-radius: 6px;
    font-size: 11px;
}

.sms-campaign-page .sms-counter-bar strong {
    color: var(--sms-text);
    font-weight: 700;
}

.sms-campaign-page .sms-schedule-option {
    padding: 12px;
    background: #f8f9fb;
    border: 1px solid #edf0f3;
    border-radius: 7px;
}

.sms-campaign-page .sms-schedule-option .form-check-input {
    margin-top: .18em;
}

.sms-campaign-page .sms-schedule-option .form-check-label {
    color: var(--sms-text);
    font-size: 12px;
}

.sms-campaign-page .sms-summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    padding: 12px 0;
    color: #7d858d;
    border-bottom: 1px solid #edf0f2;
    font-size: 11px;
}

.sms-campaign-page .sms-summary-row:first-child {
    padding-top: 0;
}

.sms-campaign-page .sms-summary-row strong {
    color: var(--sms-text);
    font-size: 12px;
    font-weight: 700;
    text-align: right;
}

.sms-campaign-page .sms-summary-total {
    padding-top: 15px;
    border-bottom: 0;
}

.sms-campaign-page .sms-summary-total span {
    color: var(--sms-text);
    font-weight: 700;
}

.sms-campaign-page .sms-summary-total h5 {
    margin: 0;
    color: var(--sms-primary);
    font-size: 18px;
    font-weight: 700;
}

.sms-campaign-page .sms-preview-area {
    background: #f5f7fa;
}

.sms-campaign-page .sms-phone-preview {
    width: 100%;
    max-width: 285px;
    min-height: 170px;
    margin: 0 auto;
    padding: 15px;
    background: #fff;
    border: 1px solid #e3e7eb;
    border-radius: 12px;
    box-shadow: 0 3px 12px rgba(0, 0, 0, .045);
    text-align: left;
}

.sms-campaign-page .sms-preview-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    padding-bottom: 11px;
    margin-bottom: 11px;
    border-bottom: 1px solid #edf0f2;
}

.sms-campaign-page .sms-preview-heading span:first-child {
    color: var(--sms-text);
    font-size: 11px;
    font-weight: 700;
}

.sms-campaign-page .sms-preview-heading span:last-child {
    color: #9aa1a9;
    font-size: 10px;
}

.sms-campaign-page .sms-preview-message {
    color: #495057;
    font-family: monospace;
    font-size: 12px;
    line-height: 1.65;
    white-space: pre-wrap;
    word-break: break-word;
}

.sms-campaign-page .alert {
    border-radius: 7px;
}

.sms-campaign-page .form-text {
    display: block;
    margin-top: 7px;
    color: #89919a;
    font-size: 10px;
    line-height: 1.55;
}

.sms-campaign-page .cursor-pointer {
    cursor: pointer;
}

/* Tablet */
@media (max-width: 991.98px) {
    .sms-campaign-page {
        padding-top: 20px !important;
    }

    .sms-campaign-page .sms-card > .card-header {
        padding: 13px 16px;
    }

    .sms-campaign-page .sms-card .card-body {
        padding: 16px !important;
    }
}

/* Mobile */
@media (max-width: 575.98px) {
    .sms-campaign-page {
        padding-left: 12px !important;
        padding-right: 12px !important;
        padding-top: 16px !important;
    }

    .sms-campaign-page .sms-page-header {
        align-items: flex-start !important;
    }

    .sms-campaign-page .sms-page-header h4 {
        font-size: 18px;
    }

    .sms-campaign-page .sms-back-btn {
        width: 100%;
        justify-content: center;
    }

    .sms-campaign-page .sms-card {
        margin-bottom: 16px !important;
    }

    .sms-campaign-page .sms-card > .card-header {
        padding: 12px 14px;
    }

    .sms-campaign-page .sms-card .card-body {
        padding: 14px !important;
    }

    .sms-campaign-page .audience-card {
        padding: 11px;
    }

    .sms-campaign-page .audience-card-title {
        font-size: 11px;
    }

    .sms-campaign-page .audience-card-description {
        font-size: 9px;
    }

    .sms-campaign-page .audience-card-count {
        font-size: 16px;
    }

    .sms-campaign-page .sms-filter-panel {
        padding: 12px;
    }

    .sms-campaign-page .sms-recipient-toolbar {
        align-items: flex-start;
    }

    .sms-campaign-page .sms-template-select {
        width: 100%;
    }

    .sms-campaign-page .sms-card-header-template {
        align-items: flex-start;
    }

    .sms-campaign-page .sms-counter-bar {
        font-size: 10px;
    }

    .sms-campaign-page .sms-summary-total h5 {
        font-size: 16px;
    }
}
</style>
@endsection

@section('content')
    @yield('admin_content')
@endsection
